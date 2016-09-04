<?php

require realpath(__DIR__) . '/../bootstrap.php';

use Phine\Path\Path;
use lib\Data\OutputManager;
use lib\Settings\SettingsManager;
use lib\Cache\CacheDriver;
use lib\Data\StringKey;

use lib\PDO\MasterPDO;
use lib\Query\DatabaseQuery;
use lib\PDO\DatabaseInterface;

# Global Libraries

$settings = new SettingsManager(include(Path::join([MASTER_DIR, 'support', 'config.php'])));

$output = new OutputManager($settings->get('DIRS'));
$output->setAlias('/^(\%[\\\\\/]?)/', MASTER_DIR . DIRECTORY_SEPARATOR);

$cache = new CacheDriver($output->get('cache'));


$master = new MasterPDO(array(
    'hosting' => $settings->get('SQLSRV.database'),
    'username' => $settings->get('SQLSRV.username'),
    'password' => $settings->get('SQLSRV.password'),
));

$dbq = new DatabaseQuery();
$dbi = new DatabaseInterface($master, [], $cache);

# Get Available Databases

$dbs = $cache->fetch('dbs');

# Script
$listener_object = $cache->fetch($settings->get('LISTENER'));

$info = array(
    'TIME_START' => date('Ymd\THis',time()),
    'WAS_STOPED' => (!$listener_object || !is_array($listener_object))?"True":"False",
    'RUNNING' => (!$listener_object || !is_array($listener_object))?"False":"True",
    'COMMANDS' => $listener_object,
    'RPS_PREV' => count($cache->fetch('RPS')),
);

$filename = Path::join([$output->get('logs'),'BOT_'.$info['TIME_START'].'.log']);
file_put_contents($filename, print_r($info, 1));
if ((!$listener_object || !is_array($listener_object))) die();

unset($listener_object[$instruction]);
$cache->save($settings->get('LISTENER'), []);
foreach ($listener_object as $instruction)
{
    if ($instruction == 'update')
    {
        echo "UPDATE....";
        $rps = [];
        $i = 0; $_i = count($dbs);
        foreach ($dbs as $db_slug) {
            $i++;
            $q = $master->using($db_slug)->query($dbq->getRegPatDic());
            $q->execute();
            $rows = $q->fetchAll();
            echo round($i/$_i*100)."% [{$i}/{$_i}] [{$db_slug}] = RPS(".count($rps).")\t\t\t\t\r";

            foreach ($rows as $row) {
                $rps[$row['cregistroimss']][] = $db_slug;
            }
        }
        echo "SAVING....";
        $cache->save("RPS", $rps);
        echo "DONE....";
    }
}

$info['RPS_FOUND'] = count($rps);
$info['TIME_END'] = date('Ymd\THis',time());
$info['RUNNING'] = count($rps);

file_put_contents($filename, print_r($info, 1));