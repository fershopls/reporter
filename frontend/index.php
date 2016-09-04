<!DOCTYPE html>
<html >
  <head>
    <meta charset="UTF-8">
    <title>Reportes TSL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="css/normalize.css">

    <link rel='stylesheet prefetch' href='https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css'>
<link rel='stylesheet prefetch' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css'>

        <link rel="stylesheet" href="css/style.css">

  </head>

  <body>

    <link href='https://fonts.googleapis.com/css?family=Josefin+Sans:400,600,700,700italic,400italic,300' rel='stylesheet' type='text/css'>

<div id="particles-js" class="particles"></div>

<div class="wrap">
  <div class="header">
    Lista de Raya
  </div>
  <div class="container">  
    <div style='max-width:400px;margin:auto;'>
	
	<table>
		<tr>
			<th>
        <div>
          <label>Registro Patronal:</label>
        
		  
		 <select class="u-full-width" type="text" >
			<option value="ip3">IP3</option>
			<option value="tsl">TSL</option>
			<option value="iol">IOL</option>
			<option value="tusolucion">Tu Solucion</option>
		</select>
        </div>
		</th>  
		<th>
        <div>
          <label>Ejercicio:</label>
          <input class="u-full-width" type="number" placeholder="" value="<?php echo date("Y"); ?>"/>
        </div>
		</th>
		</tr>
		<tr>	
		<th>	
		<div>
          <label>Tipo de Periodo:</label>
            <select class="u-full-width" type="text" >
				<option value="semanal">SEMANAL</option>
				<option value="catorcenal">CATORCENAL</option>
				<option value="quicenal">QUINCENAL</option>
			</select>
        </div>
		</th>
		</tr>
		
  
	<tr>
		<th> 
		<div>
          <label>Fecha Inicial:</label>
          <input class="u-full-width" type="date" value="<?php echo date("Y-m-d");?>" />
        </div>
		</th>
		<th>
		<div>
          <label>Fecha Final:</label>
          <input class="u-full-width" type="date" value="<?php echo date("Y-m-d");?>" />
        </div>
		</th>
		</tr>
		<tr>
		<th>
		<div>
          <label>Incluir Empleados con Neto Cero:</label>
          <select class="u-full-width" type="text" >
				<option value="1">Si</option>
				<option value="2">No</option>				
			</select>
        </div>
		</th>
		<th>
		<div>
          <label>Incluir Empleados con baja:</label>
          <select class="u-full-width" type="text" >
				<option value="1">Si</option>
				<option value="2">No</option>				
			</select>
        </div>
		</th>
		</tr>
		</table>  
        <div>
          <input class="primary button u-full-width" type="submit" value="Ejecutar Reporte" />
        </div>
        <div class="text-center">
  
       
          
          
        </div>
    </div>
  </div>
</div>
    <script src='http://vincentgarreau.com/particles.js/assets/_build/js/lib/particles.js'></script>

        <script src="js/index.js"></script>

    
    
    
  </body>
</html>
