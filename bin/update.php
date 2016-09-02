<?php
define("MASTER_DIR", realpath(__DIR__).'/..');
require MASTER_DIR . '/vendor/autoload.php';


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

$dbs = [];
$i = 0;
$rows = $master->using('nomGenerales')
    ->query($dbq->getDatabaseDic())
    ->fetchAll();
foreach ($rows as $row)
{
    if (isset($row[0]) && $row[0] && $master->testConnection($row[0]))
        $dbs[] = $master->using($row[0])?$row[0]:null;
    else
        $i++;
}
$cache->save('dbs', $dbs, 10);

# Script
$listener_object = $cache->fetch($settings->get('LISTENER'));

if (!$listener_object || !is_array($listener_object)) die();

foreach ($listener_object as $instruction)
{
    if ($instruction == 'update')
    {
        $rps = [];
        foreach ($dbs as $db_slug) {
            $q = $master->using($db_slug)->query($dbq->getRegPatDic());
            $q->execute();
            $rows = $q->fetchAll();

            foreach ($rows as $row) {
                $rps[$row['cregistroimss']][] = $db_slug;
            }
        }
        $cache->save("RPS", $rps);
        unset($listener_object[$instruction]);
        $cache->save($settings->get('LISTENER'), array_values($listener_object), 0);
    }
}