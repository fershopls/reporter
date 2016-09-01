<?php
define("MASTER_DIR", realpath(__DIR__));
require MASTER_DIR . '/vendor/autoload.php';

function dd ($string = '', $return = 0) {echo $string . "\t\t\t\t\t"; if ($return) echo "\r"; else echo "\n";}

use Phine\Path\Path;
use lib\Data\OutputManager;
use lib\Settings\SettingsManager;
use lib\Cache\CacheDriver;
use lib\Data\StringKey;

use lib\PDO\MasterPDO;
use lib\Query\DatabaseQuery;
use lib\PDO\DatabaseInterface;

use lib\Data\DataHandler;
use lib\CSV\CSV;

# Global Libraries

$settings = new SettingsManager(include(Path::join([MASTER_DIR, 'support', 'config.php'])));

$output = new OutputManager($settings->get('DIRS'));
$output->setAlias('/([\\\\\/])/', DIRECTORY_SEPARATOR);
$output->setAlias('/^(\%[\\\\\/]?)/', MASTER_DIR . DIRECTORY_SEPARATOR);

$cache = new CacheDriver($output->get('cache'));



$master = new MasterPDO(array(
    'hosting' => $settings->get('SQLSRV.database'),
    'username' => $settings->get('SQLSRV.username'),
    'password' => $settings->get('SQLSRV.password'),
));

$dbq = new DatabaseQuery();
$dbi = new DatabaseInterface($master, [], $cache);



$dh = new DataHandler();
$csv = new CSV();



# Get Available Databases
$dbs = $cache->fallback('dbs', [$master, $dbq], function(MasterPDO $master, DatabaseQuery $dbq){
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
    dd("DBS: (".count($dbs).") Found. ({$i}) Lost.");
    return $dbs;
}, $settings->get('DEFAULT.cache_store_minutes', 30));
$dbi->setDatabases($dbs);
dd("DBS: (".count($dbs).") Loaded.");



# Methods
$dbi->callback('dic',function ($req, $res) {
    $req['row']['key'] = StringKey::get($req['row'][1]);
    $res[$req['database']][$req['row'][0]] = $req['row'];
    return $res;
});



# DBI Dictionaries

$db_worker_dic = $dbi->set($dbq->getDatabaseWorkerDic())
    ->execute('dic');

$db_period_dic = $dbi->set($dbq->getPeriodDic())
    ->execute('dic');

$db_period_type_dic = $dbi->set($dbq->getPeriodTypeDic())
    ->execute('dic');

$db_regpat_dic = $dbi->set($dbq->getRegPatDic())
    ->execute('dic');

$db_concept_dic = $dbi->set($dbq->getConceptDic())
    ->execute('dic');

$db_key_concept_dic = $dbi->set($dbq->getConceptDic())
    ->execute(function($req, $res){
        $string = $req['row']['descripcion'];
        $res[StringKey::get($string)] = $req['row'];
        return $res;
    });


# Solve Relationships

$used ['databases'] = []; $used ['workers'] = 0;
$used ['workers_dumped'] = 0; $used ['rows'] = 0;
#
$db_worker_concept_dic = [];
$db_concept_ordered = [];
foreach ($db_worker_dic as $db_slug => $workers)
{
    dd("[{$db_slug}]");
    $w = 0; $w_num = count($workers);
    foreach ($workers as $worker)
    {
        $w++; $used['workers']++;
        $params = [
            'worker_id' => $worker['idempleado'],
            'period_begin' => '20160101 00:00',
            'period_end'   => '20160601 00:00',
        ];
        $q = $master->using($db_slug)->prepare($dbq->getWorkerMovement());
        $_percent = round($w*100/$w_num);
        dd ("{$_percent}% [{$w}/$w_num] -> {$worker['idempleado']} Query...", 1);
        $q->execute($params);

        $rows = $q->fetchAll();
        if ($rows) {
            $used['workers_dumped']++;
            $used['databases'][$db_slug] = 1;
        }
        $q = 0; $q_num = count($rows);
        foreach ($rows as $row)
        {
            $q++; $used['rows']++;

            $_cpt_key = $db_concept_dic[$db_slug][$row['idconcepto']]['key'];
            $_cpt_type = $db_key_concept_dic[$_cpt_key]['tipoconcepto'];

            $db_concept_ordered [$_cpt_type][$_cpt_key] = 1;

            $db_worker_concept_dic [$db_slug] [$row['idperiodo']] [$worker['idempleado']] [$_cpt_key] = $row['importetotal'];
            dd ("{$_percent}% [{$w}/$w_num] -> {$q} from {$q_num}", 1);
        }

    }
}


# Relationships Info
foreach ($used as $str_used => $counter)
{
    $counter = is_array($counter)?count($counter):$counter;
    dd("({$counter}) {$str_used}");
} dd();


# Prepare to Export
dd ("[CSV] Preparing to export");

$concept_type_string = [
    "D" => 'Deducciones',
    "P" => 'Percepciones',
    "O" => 'Obligaciones',
];

$csv_rows = [];
foreach ($db_worker_concept_dic as $db_slug => $_db_period)
{
    foreach ($_db_period as $period_id => $_db_worker)
    {
        foreach ($_db_worker as $worker_id => $_db_concept)
        {
            $_period_type_id = $db_period_dic[$db_slug][$period_id]['idtipoperiodo'];
            $_period_type_key = $db_period_type_dic[$db_slug][$_period_type_id]['key'];

            $csv_id = $period_id . $worker_id;

            $csv_rows[$csv_id][$dh->getConceptId('Database')] = $db_slug;
            $csv_rows[$csv_id][$dh->getConceptId('Worker code')] = $db_worker_dic[$db_slug][$worker_id]['codigoempleado'];
            $csv_rows[$csv_id][$dh->getConceptId('Worker name')] = $db_worker_dic[$db_slug][$worker_id]['nombrelargo'];
            $csv_rows[$csv_id][$dh->getConceptId('Period type')] = $_period_type_key;
            $csv_rows[$csv_id][$dh->getConceptId('Period no.')] = $db_period_dic[$db_slug][$period_id]['numeroperiodo'];
            $csv_rows[$csv_id][$dh->getConceptId('Period begin')] = $db_period_dic[$db_slug][$period_id]['fechainicio'];
            $csv_rows[$csv_id][$dh->getConceptId('Period end')] = $db_period_dic[$db_slug][$period_id]['fechafin'];

            $_concept_type_last = null;
            $_concept_type_total = 0;
            $db_concept_ordered['FINAL'] = [];
            foreach ($db_concept_ordered  as $_concept_type => $_concept_group)
            {
                if ($_concept_type_last && $_concept_type_last != $_concept_type)
                {
                    if ($_concept_type_last != 'N')
                    {
                        $concept_row = $dh->getConceptId("Total ".$concept_type_string[$_concept_type_last]);
                        $csv_rows[$csv_id][$concept_row] = $_concept_type_total;
                    }
                    $_concept_type_total = 0;
                }
                $_concept_type_last = $_concept_type;
                foreach ($_concept_group as $_concept_key => $i)
                {
                    $concept_value = isset($_db_concept[$_concept_key])?$_db_concept[$_concept_key]:0;
                    $concept_row = $dh->getConceptId($db_key_concept_dic[$_concept_key]['descripcion']);
                    $csv_rows[$csv_id][$concept_row] = $concept_value;
                    $_concept_type_total += $concept_value;
                }
            }
        }
    }
}


# Dump

$csv->writerow($dh->getHeaders());
foreach ($csv_rows as $csv_row)
{
    $rows = [];
    foreach ($dh->getHeaders() as $key => $header)
        $rows[] = isset($csv_row[$key])?$csv_row[$key]:0;
    $csv->writerow($rows);
}

$_output = Path::join([$output->get('output'), 'output.csv']);
file_put_contents($_output, $csv->get());
dd ("[CSV] [Done] ".$_output);
