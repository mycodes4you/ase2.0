<?php include_once('funciones.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>';
echo $agencia . ' - ' . $titulo ;
echo '</title>
<meta name="keywords" content="' . $keywords . '">
<meta name="description" content="' . $pag_desc . '">
<!-- <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8"> -->
<meta name="generator" content="Bluefish 2.2.5" >'."\n";
if(basename($_SERVER['PHP_SELF'])=='index.php' || basename($_SERVER['PHP_SELF'])=='monitoreo.php' || basename($_SERVER['PHP_SELF'])=='produccion.php') { 
	echo '<meta http-equiv="refresh" content="300">'."\n"; 
}
echo '<meta name="author" content="Agustín Díaz" >
<meta name="copyright" content="Agustin Diaz">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">'."\n";
if(basename($_SERVER['PHP_SELF'])=='reg-express.php' || basename($_SERVER['PHP_SELF'])=='reportes.php') {
	echo '<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">'."\n";
}
echo '<link href="css/estilos.css" type="text/css" rel="stylesheet" />
<link href="css/imprimir.css" type="text/css" rel="stylesheet" media="print" />'."\n";
if($simulador == 1) { 
	echo '<link href="css/simulador.css" type="text/css" rel="stylesheet" />'."\n"; 
}

echo '<script type="text/javascript" src="parciales/dinamico.js"></script>'."\n";

if ((basename($_SERVER['PHP_SELF'])=='seguimiento.php') || (basename($_SERVER['PHP_SELF'])=='usuarios.php') || (basename($_SERVER['PHP_SELF'])=='refacciones.php') || (basename($_SERVER['PHP_SELF'])=='entrega.php')) {

	echo '	<script type="text/javascript">
		function setFocus() {
			document.getElementById("codigo").focus();
		}
	</script>'."\n";
}

if ((basename($_SERVER['PHP_SELF'])=='ordenes-de-trabajo.php')) {

	echo '	<script type="text/javascript">
		function setFocus() {
			document.getElementById("orden_id").focus();
		}
	</script>'."\n";
}

if (basename($_SERVER['PHP_SELF'])=='reg-express.php' || basename($_SERVER['PHP_SELF'])=='presupuestos.php') {
	echo '	<script type="text/javascript">
		function cargarPaquetes(xArea) {
			var o
			document.forms.rapida.paquetes.disabled=true;'."\n";
	$pregunta = "SELECT paq_id, paq_nombre, paq_area FROM " . $dbpfx . "paquetes WHERE paq_activo = '1' ORDER BY paq_nombre";
	$matriz = mysql_query($pregunta);
		echo "			o = document.createElement('OPTION');
				o.text = 'Seleccione';
				document.forms.rapida.paquetes.options.add (o);" . "\n";
	while ($paquetes = mysql_fetch_array($matriz)) {
		echo "			if (xArea == " . $paquetes['paq_area'] . ") {
				o = document.createElement('OPTION');
				o.text = '" . $paquetes['paq_nombre'] . "';
				o.value = " . $paquetes["paq_id"] . ";
				document.forms.rapida.paquetes.options.add (o);
			}" . "\n";
	}
	echo '			document.forms.rapida.paquetes.disabled=false;
		}
	</script>'."\n";
} 

echo '</head>'."\n";

if ((basename($_SERVER['PHP_SELF'])=='seguimiento.php') || (basename($_SERVER['PHP_SELF'])=='usuarios.php') || (basename($_SERVER['PHP_SELF'])=='reg-express.php') || (basename($_SERVER['PHP_SELF'])=='refacciones.php') || (basename($_SERVER['PHP_SELF'])=='entrega.php') || (basename($_SERVER['PHP_SELF'])=='ordenes-de-trabajo.php')) {
	echo '<body onload="setFocus()">'."\n";
} else {
	echo '<body>'."\n";
}
echo '	<div id="container">
		<h1>' . $pagina_actual . '</h1>'."\n";

/* Encabezado general */