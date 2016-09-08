<?php
require realpath(__DIR__) . '/../bootstrap.php';

use lib\Log\Log;

use Phine\Path\Path;
use lib\Data\StringKey;

use lib\PDO\MasterPDO;
use lib\Query\DatabaseQuery;
use lib\PDO\DatabaseInterface;

use lib\Data\DataHandler;
use lib\CSV\CSV;

# Logger instance
$log = new Log($output->get('logs'));

# User Input
$_parameters = array(
    'regpat' => '',
    'exercise' => '',
    'period_type' => '',
    'date_begin'  => '20160101 00:00',
    'date_end'    => '20160701 00:00',
);

$log->dd(['debug'], "Start to scan dir `".$output->get('request')."`");
$requests_dir = scandir($output->get('request'));
array_shift($requests_dir);array_shift($requests_dir);
$requests_dir = array_values($requests_dir);

$log->dd(['debug'], "Getting first request and unserializing it.");
$path = Path::join([$output->get('request'), $requests_dir[0]]);
$request_content = file_get_contents($path);
$_parameters = \lib\Cache\Serializer::unserialize($request_content, true);

$log->dd(['debug'], "Parameters.", $_parameters);
$regex = '/^\d{4}\-\d{2}\-\d{2}$/i';
if (!preg_match($regex, $_parameters['date_begin']) || !preg_match($regex, $_parameters['date_end']))
    die("Dates aren't in html5 format: YYYY-MM-DD");
$_parameters['date_begin'] = str_replace('-', '', $_parameters['date_begin']).' 00:00';
$_parameters['date_end'] = str_replace('-', '', $_parameters['date_end']).' 00:00';

$log->dd(['debug'], "Unlinking `{$path}`");
#unlink($path);


function dd ($string = '', $return = 0) { echo $string . "\t\t\t\t\t"; if ($return) echo "\r"; else echo "\n";}

$master = new MasterPDO(array(
    'hosting' => $settings->get('SQLSRV.database'),
    'username' => $settings->get('SQLSRV.username'),
    'password' => $settings->get('SQLSRV.password'),
));

$log->dd(['debug'], "Creating Databases Querys and Interfaces");
$dbq = new DatabaseQuery();
$dbi = new DatabaseInterface($master, [], $cache);


$log->dd(['debug'], "Creating Data Handlers and CSV Interface");
$dh = new DataHandler();
$csv = new CSV();


$log->dd(['dbs','debug'], "Getting available databases in cache");
$dbs = $cache->fallback('dbs', [$master, $dbq], function(MasterPDO $master, DatabaseQuery $dbq) use ($log) {
    $log->dd(['dbs','alert'], "Cache was not found. Researching again on `nomGenerales.db`");
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
    return $dbs;
}, $settings->get('DEFAULT.cache_store_minutes', 30));
$dbi->setDatabases($dbs);
$log->dd(['dbs','debug'], "Databases loaded.", ['dbs_loaded'=>count($dbs)]);

if (count($requests_dir) == 0)
{
    $log->dd(['debug'], "Program skiped because there is not requests.");
    die();
}


# Methods
$dbi->callback('dic',function ($req, $res) {
    $req['row']['key'] = StringKey::get($req['row'][1]);
    $res[$req['database']][$req['row'][0]] = $req['row'];
    return $res;
});



# DBI Dictionaries
$log->dd(['debug'], "Fetching databases tables and dictionaries.");
$log->dd(['query','debug'], "Executing DB_STRING_DIC", ['query'=>$dbq->getDatabaseStringDic()]);
$db_string_dic = $dbi->set($dbq->getDatabaseStringDic())
    ->execute(function($req, $res)
    {
        $res[$req['database']] = $req['row']['nombrecorto'];
        return $res;
    });

$log->dd(['query','debug'], "[1/6] Executing DB_WORKER_DIC", ['query'=>$dbq->getDatabaseWorkerDic()]);
$db_worker_dic = $dbi->set($dbq->getDatabaseWorkerDic())
    ->execute('dic');

$log->dd(['query','debug'], "[2/6] Executing DB_PERIOD_DIC", ['query'=>$dbq->getPeriodDic()]);
$db_period_dic = $dbi->set($dbq->getPeriodDic())
    ->execute('dic');

$log->dd(['query','debug'], "[3/6] Executing DB_PERIOD_TYPE_DIC", ['query'=>$dbq->getPeriodTypeDic()]);
$db_period_type_dic = $dbi->set($dbq->getPeriodTypeDic())
    ->execute('dic');

$log->dd(['query','debug'], "[4/6] Executing DB_REGPAT_DIC", ['query'=>$dbq->getRegPatDic()]);
$db_regpat_dic = $dbi->set($dbq->getRegPatDic())
    ->execute('dic');

$log->dd(['query','debug'], "[5/6] Executing DB_CONCEPT_DIC", ['query'=>$dbq->getConceptDic()]);
$db_concept_dic = $dbi->set($dbq->getConceptDic())
    ->execute('dic');

$log->dd(['query','debug'], "[6/6] Executing DB_KEY_CONCEPT_DIC", ['query'=>$dbq->getConceptDic()]);
$db_key_concept_dic = $dbi->set($dbq->getConceptDic())
    ->execute(function($req, $res){
        $string = $req['row']['descripcion'];
        $res[StringKey::get($string)] = $req['row'];
        return $res;
    });


# Solve Relationships
$log->dd(['debug'], "Preparing to preparing to walk through every available database.");
$used ['databases'] = []; $used ['workers'] = 0;
$used ['workers_dumped'] = 0; $used ['rows'] = 0;
#
$db_worker_concept_dic = [];
$db_concept_ordered = [
    'P' => [],
    'D' => [],
    'N' => [],
    'O' => [],
];
foreach ($db_worker_dic as $db_slug => $workers)
{
    $has_reg_pat = array_filter($db_regpat_dic[$db_slug], function ($ob) use ($_parameters){
        return ($ob[1]==$_parameters['regpat'])?true:false;
    });
    if (count($has_reg_pat) == 0)
        continue;
    dd("[{$db_slug}]");
    $w = 0; $w_num = count($workers);
    foreach ($workers as $worker)
    {
        $w++; $used['workers']++;
        if ($_parameters['period_type']=='')
            $period_type = array_filter($db_period_type_dic[$db_slug], function ($ob) use ($_parameters) {
                return ($ob['key']==StringKey::get($_parameters['period_type']))?true:false;
            });
        $period_type = isset($period_type)?$period_type:[];
        if (count($period_type) == 0 && $_parameters['period_type']!='')
            continue;
        $params = [
            'worker_id' => $worker['idempleado'],
            'date_begin' => $_parameters['date_begin'],
            'date_end'   => $_parameters['date_end'],
            'exercise'   => $_parameters['exercise'],
        ];
        if ($_parameters['period_type']!='')
            $params['period_type'] = array_values($period_type)[0]['idtipoperiodo'];
        $q = $master->using($db_slug)->prepare($dbq->getWorkerMovement(':worker_id', ':date_begin', ':date_end', ':exercise', ($_parameters['period_type']==false?false:null)));
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
$log->dd(['debug'], "Database walk through finished.");


# Relationships Info
$log->dd(['debug'], "DBWTH Info", $used);
foreach ($used as $str_used => $counter)
{
    $counter = is_array($counter)?count($counter):$counter;
    dd("({$counter}) {$str_used}");
} dd();
if (count($used['databases']) == 0)
{
    dd("[NULL] Databases. aborting.");
    die();
}


# Prepare to Export
$log->dd(['debug'],"Preparing rows to export");

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

            $db_name = isset($db_string_dic[$db_slug])?$db_string_dic[$db_slug]:$db_slug;
            $csv_rows[$csv_id][$dh->getConceptId('Factura')] = '';
            $csv_rows[$csv_id][$dh->getConceptId('Empresa')] = $db_name;
            $csv_rows[$csv_id][$dh->getConceptId('Codigo de Empleado')] = $db_worker_dic[$db_slug][$worker_id]['codigoempleado'];
            $csv_rows[$csv_id][$dh->getConceptId('Nombre de Empleado')] = $db_worker_dic[$db_slug][$worker_id]['nombrelargo'];
            $csv_rows[$csv_id][$dh->getConceptId('Tipo de Periodo')] = $_period_type_key;
            $csv_rows[$csv_id][$dh->getConceptId('No. de Periodo')] = $db_period_dic[$db_slug][$period_id]['numeroperiodo'];
            $csv_rows[$csv_id][$dh->getConceptId('Fecha Inicio')] = $db_period_dic[$db_slug][$period_id]['fechainicio'];
            $csv_rows[$csv_id][$dh->getConceptId('Fecha Fin')] = $db_period_dic[$db_slug][$period_id]['fechafin'];

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
$log->dd(['CSV','debug'], "Ordering csv rows");
$csv->writerow($dh->getHeaders());
foreach ($csv_rows as $csv_row)
{
    $rows = [];
    foreach ($dh->getHeaders() as $key => $header)
        $rows[] = isset($csv_row[$key])?$csv_row[$key]:0;
    $csv->writerow($rows);
}

$log->dd(['debug'], "Starting to write CSV file");
date_default_timezone_set("America/Mexico_City");
$_output = Path::join([$output->get('output'), date("YmdTHis",time()).'_'.StringKey::get($_parameters['filename']).'.csv']);
file_put_contents($_output, $csv->get());
$log->dd (['CSV','done'],$_output);