<?php

require (realpath(__DIR__).'/../bootstrap.php');

$DEFAULT_DATE = date("Y-m-d", time());
$DEFAULT_FILENAME = date("Ymd\THis", time());
$REGPAT = array_keys($cache->fetch('RPS'));
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reportes TSL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/normalize.css">
    <link rel='stylesheet prefetch' href='css/skeleton.min.css'>
    <link rel='stylesheet prefetch' href='css/font-awesome.min.css'>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Josefin+Sans:400,600,700,700italic,400italic,300' rel='stylesheet' type='text/css'>

</head>

<body>

    <div id="particles-js" class="particles"></div>

    <div class="wrap">
        <div class="header">
            Manifest UI
        </div>
        <form action="process.php" method="POST">
        <div class="container">

            <div class="row">
                <div class="eight columns">
                    <label for="regpat">Registro Patronal</label>
                    <select name="regpat" class="u-full-width">
                        <?php
                        foreach ($REGPAT as $r)
                            echo '<option value="'.$r.'">'.$r.'</option>';
                        ?>
                    </select>
                </div>
                <div class="four columns">
                    <label for="">&nbsp;</label>
                    <input type="submit" class="u-full-width" name="SUBMIT" value="UPDATE_REGPAT" />
                </div>
            </div>

            <div class="row">
                <div class="six columns">
                    <label for="exercise">Ejercicio</label>
                    <input name="exercise" class="u-full-width" type="number" value="2016" />
                </div>
                <div class="six columns">
                    <label for="period_type">Tipo de Periodo</label>
                    <select class="u-full-width" name="period_type" id="">
                        <option value="semanal">SEMANAL</option>
                        <option value="catorcenal">CATORCENAL</option>
                        <option value="quicenal">QUINCENAL</option>
                        <option value="mensual">MENSUAL</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="six columns">
                    <label for="date_begin">Fecha Inicial</label>
                    <input name="date_begin" class='u-full-width' type="date" value="<?php echo $DEFAULT_DATE ?>">
                </div>
                <div class="six columns">
                    <label for="date_end">Fecha Final</label>
                    <input name="date_end" class='u-full-width' type="date" value="<?php echo $DEFAULT_DATE ?>">
                </div>
            </div>

            <div class="row text-center">
                <div class="six columns">
                    <label for="options[workers_net]">
                        <input name="options[workers_net]" type="checkbox" />
                        Incluir empleados con neto 0
                    </label>
                </div>
                <div class="six columns">
                    <label for="options[worker_down]">
                        <input name="options[worker_down]" type="checkbox" />
                        Incluir empleados con baja
                    </label>
                </div>
            </div>

            <div class="row">
                <div class="eight columns">
                    <label for="filename">Nombre del Reporte</label>
                    <input name="filename" class="u-full-width" type="text" placeholder="<?php echo $DEFAULT_FILENAME ?>" />
                </div>
                <div class="four columns">
                    <label for="">&nbsp;</label>
                    <input class="u-full-width" type="submit" name="submit" value="CREATE_REPORT"/>
                </div>
            </div>

        </div>
        </form>
    </div>

    <script src='js/particles.js'></script>
    <script src="js/index.js"></script>

</body>
</html>
