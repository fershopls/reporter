<?php
define("MASTER_DIR", realpath(__DIR__));
require MASTER_DIR . '/vendor/autoload.php';

function dd ($string = '', $return = 0) {
    echo $string . "\t\t\t\t\t";
    if ($return) echo "\r";
    else echo "\n";
}

use Phine\Path\Path;
use lib\Cache\Cache;
use lib\Config\ConfigManager;
use lib\Data\StringKey;
use lib\Data\DataHandler;
use lib\Data\DirectoryManager;
use lib\PDO\MasterPDO;
use lib\PDO\DatabaseInterface;
use lib\Query\DatabaseQuery;

$settings = new ConfigManager(include(Path::join([MASTER_DIR, 'support', 'config.php'])));
$dir_stack = new DirectoryManager($settings->get('DIRS'));

foreach ($settings->get('DIRS') as $slug => $route)
{
    $route = preg_replace('/^(\%[\\\\\/]?)/', MASTER_DIR.DIRECTORY_SEPARATOR, $route);
    if (!file_exists($route))
        mkdir($route, 0777, true);
}

$settings_cache_store_minutes = $settings->get('DEFAULT.cache_store_minutes');

$cacheDriver = new Cache(Path::join([MASTER_DIR, $settings->get('DIRS.cache')]));

$masterPdo = new MasterPDO(array(
    'hosting' => $settings->get('SQLSRV.database'),
    'username' => $settings->get('SQLSRV.username'),
    'password' => $settings->get('SQLSRV.password'),
));

$dbq = new DatabaseQuery();
$dbi = new DatabaseInterface($masterPdo, [], $cacheDriver);
$dh = new DataHandler();
$csv = new lib\CSV\CSV();


# Get Available Databases
$dbs = $cacheDriver->fallback('dbs', [$masterPdo], function($master){
    $dbs = [];
    $i = 0;
    $rows = $master->using('nomGenerales')
        ->query("SELECT [RutaEmpresa] FROM [nomGenerales].[dbo].[NOM10000] GROUP BY [RutaEmpresa]")
        ->fetchAll();
    foreach ($rows as $row)
    {
        if (isset($row[0]) && $row[0] && $master->testConnection($row[0]))
            $dbs[] = $master->using($row[0])?$row[0]:null;
        else
            $i++;
    }
    echo "DBS: (".count($dbs).") Found. ({$i}) Lost." . PHP_EOL;
    return $dbs;
}, $settings_cache_store_minutes);
$dbi->setDatabases($dbs);
echo "DBS: (".count($dbs).") Loaded." . PHP_EOL;



# Methods
$dbi->callback('dic',function ($req, $res) {
    $req['row']['key'] = StringKey::get($req['row'][1]);
    $res[$req['database']][$req['row'][0]] = $req['row'];
    return $res;
});


# Dictionaries


$db_worker_dic = $dbi->set($dbq->getDatabaseWorkerDic())
    ->cache('wdic')
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

$used ['databases'] = [];
$used ['workers'] = 0;
$used ['workers_dumped'] = 0;
$used ['rows'] = 0;
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
            'period_begin' => 18,
            'period_end' => 20,
        ];
        $q = $masterPdo->using($db_slug)->prepare($dbq->getWorkerMovement());
        dd ("[{$w}/$w_num] -> {$worker['idempleado']} query..", 1);
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
            dd ("[{$w}/$w_num] -> {$q} from {$q_num}", 1);
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

$csv_rows = [];
foreach ($db_worker_concept_dic as $db_slug => $_db_period)
{
    foreach ($_db_period as $period_id => $_db_worker)
    {
        foreach ($_db_worker as $worker_id => $_db_concept)
        {
            $_period_type_id = $db_period_dic[$db_slug][$period_id]['idtipoperiodo'];
            $_period_type_key = $db_period_type_dic[$db_slug][$_period_type_id]['key'];

            $csv_rows[$worker_id][$dh->getConceptId('Database')] = $db_slug;
            $csv_rows[$worker_id][$dh->getConceptId('Worker name')] = $db_worker_dic[$db_slug][$worker_id]['nombrelargo'];
            $csv_rows[$worker_id][$dh->getConceptId('Period type')] = $_period_type_key;
            $csv_rows[$worker_id][$dh->getConceptId('Period no.')] = $db_period_dic[$db_slug][$period_id]['numeroperiodo'];
            $csv_rows[$worker_id][$dh->getConceptId('Period begin')] = $db_period_dic[$db_slug][$period_id]['fechainicio'];
            $csv_rows[$worker_id][$dh->getConceptId('Period end')] = $db_period_dic[$db_slug][$period_id]['fechafin'];

            $_concept_type_last = null;
            $_concept_type_total = 0;
            $db_concept_ordered['FINAL'] = [];
            foreach ($db_concept_ordered  as $_concept_type => $_concept_group)
            {
                if ($_concept_type_last && $_concept_type_last != $_concept_type && $_concept_type_last != 'N')
                {
                    $concept_row = $dh->getConceptId("({$_concept_type_last}) Total");
                    $csv_rows[$worker_id][$concept_row] = $_concept_type_total;
                    $_concept_type_total = 0;
                }
                $_concept_type_last = $_concept_type;
                foreach ($_concept_group as $_concept_key => $i)
                {
                    $concept_value = isset($_db_concept[$_concept_key])?$_db_concept[$_concept_key]:0;
                    $concept_row = $dh->getConceptId($db_key_concept_dic[$_concept_key]['descripcion']);
                    $csv_rows[$worker_id][$concept_row] = $concept_value;
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

file_put_contents(Path::join([MASTER_DIR, 'support', 'output.csv']), $csv->get());
dd ("[CSV] [Done]");
