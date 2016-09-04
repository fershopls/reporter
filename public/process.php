<?php

require realpath(__DIR__) . '/../bootstrap.php';

use Phine\Path\Path;

/*
 * What are we going to receive?
 * - [regpat]           Indica el registro patronal
 * - [update_regpat]    En caso de que exista este parametro se debe programar una tarea para actualizar los registros patronales
 * - [exercise]         Indica el ejercicio
 * - [period_type]      Referenciado en $PERIOD_TYPE
 * - [date_begin]       Date en formato YYYY-MM-DD
 * - [date_end]         Date en formato YYYY-MM-DD
 * - [filename]         Nombre del reporte
 * - [options]          Opciones Extras
 *   > [workers_net]    Existe y contiene `on` en caso de ser activado
 *   > [worker_down]    Existe y contiene `on` en caso de ser activado
 *
 * */

if (isset($_POST['update_regpat']))
{
    $cache->save($settings->get('LISTENER'), ['update']);
} else {
    $filename = date('YmdHis', time());
    $filepath = Path::join([$output->get('request'), $filename . '.json']);
    file_put_contents($filepath, json_encode($_POST));
}


?>
If your browser does not redirect you automatically click <a href="index.php">here</a>
<script>window.location = 'index.php';</script>
