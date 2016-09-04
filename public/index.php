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
                    <select name="" class="u-full-width">
                        <option value=""></option>
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
                        <option value=""></option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="six columns">
                    <label for="">Fecha Inicial</label>
                    <select name="" id="" class="u-full-width">
                        <option value=""></option>
                    </select>
                </div>
                <div class="six columns">
                    <label for="">Fecha Final</label>
                    <select name="" id="" class="u-full-width">
                        <option value=""></option>
                    </select>
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
                    <input class="u-full-width" type="text" placeholder="20160913T143508" />
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
