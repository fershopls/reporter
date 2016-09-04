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
        <div class="container">

            <div class="row">
                <div class="eight columns">
                    <label for="">Registro Patronal</label>
                    <select name="regpat" class="u-full-width">
                        <?php
                        foreach ($REGPAT as $r)
                            echo '<option value="'.$r.'">'.$r.'</option>';
                        ?>
                    </select>
                </div>
                <div class="four columns">
                    <label for="">&nbsp;</label>
                    <input type="submit" class="u-full-width" value="UPDATE" />
                </div>
            </div>

            <div class="row">
                <div class="six columns">
                    <label for="">Ejercicio</label>
                    <input class="u-full-width" type="number" value="2016" />
                </div>
                <div class="six columns">
                    <label for="">Tipo de Periodo</label>
                    <select class="u-full-width" name="" id="">
                        <option value="semanal">SEMANAL</option>
                        <option value="catorcenal">CATORCENAL</option>
                        <option value="quicenal">QUINCENAL</option>
                        <option value="mensual">MENSUAL</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="six columns">
                    <label for="">Fecha Inicial</label>
                    <input class='u-full-width' type="date" value="<?php echo $DEFAULT_DATE ?>">
                </div>
                <div class="six columns">
                    <label for="">Fecha Final</label>
                    <input class='u-full-width' type="date" value="<?php echo $DEFAULT_DATE ?>">
                </div>
            </div>

            <div class="row text-center">
                <div class="six columns">
                    <label for="">
                        <input type="checkbox" />
                        Incluir empleados con neto 0
                    </label>
                </div>
                <div class="six columns">
                    <label for="">
                        <input type="checkbox" />
                        Incluir empleados con baja
                    </label>
                </div>
            </div>

            <div class="row">
                <div class="eight columns">
                    <label for="">Nombre del Reporte</label>
                    <input class="u-full-width" type="text" placeholder="<?php echo $DEFAULT_FILENAME ?>" />
                </div>
                <div class="four columns">
                    <label for="">&nbsp;</label>
                    <input class="u-full-width" type="submit" value="Create"/>
                </div>
            </div>

        </div>
    </div>

    <script src='js/particles.js'></script>
    <script src="js/index.js"></script>

</body>
</html>
