<?php

require realpath(__DIR__) . '/../bootstrap.php';

use Phine\Path\Path;

use lib\PDO\MasterPDO;
use lib\Query\DatabaseQuery;
use lib\PDO\DatabaseInterface;
use lib\Log\Log;

# Logger instance
$log = new Log($output->get('logs'), 'U');

# Global Libraries

$master = new MasterPDO(array(
    'hosting' => $settings->get('SQLSRV.database'),
    'username' => $settings->get('SQLSRV.username'),
    'password' => $settings->get('SQLSRV.password'),
));

$dbq = new DatabaseQuery();
$dbi = new DatabaseInterface($master, [], $cache, $log);


# Script
$listener_object = $cache->fetch($settings->get('LISTENER'));
$listener_object = is_array($listener_object)?$listener_object:[];

$info = array(
    'TIME_START' => date('Ymd\THis',time()),
    'WAS_STOPED' => (!$listener_object || !is_array($listener_object))?"True":"False",
    'RUNNING' => (!$listener_object || !is_array($listener_object))?"False":"True",
    'COMMANDS' => $listener_object,
    'RPS_PREV' => count($cache->fetch('RPS')),
);

$filename = Path::join([$output->get('logs'),'BOT_'.$info['TIME_START'].'.log']);
# file_put_contents($filename, print_r($info, 1));
if ((!$listener_object || !is_array($listener_object))){
    $log->dd(['alert'], 'Program stopped', $info);
    die();
}


# Get Available Databases

$log->dd(['dbs','debug'], "Starting to check available databases.");
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
$log->dd(['dbs','debug'], "Databases.", ['db_found'=>count($dbs), 'db_lost' => $i]);


$cache->save($settings->get('LISTENER'), []);
$rps = [];
$log->dd(['debug'], 'Starting to walk through $listener_object.', $listener_object);
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
$log->dd(['debug'], "Done. Program finished with", $info);

#file_put_contents($filename, print_r($info, 1));