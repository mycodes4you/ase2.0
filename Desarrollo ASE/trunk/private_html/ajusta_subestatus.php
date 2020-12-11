<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}

$preg = "SELECT orden_id FROM " . $dbpfx . "ordenes";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
while ($ord = mysql_fetch_array($matr)) {
	$preg0 = "SELECT sub_estatus, sub_area FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
	while ($sub = mysql_fetch_array($matr0)) {
		if($sub['sub_estatus'] == 112) { $sub12 = 1; }
		elseif(($sub['sub_estatus'] == 127 || $sub['sub_estatus'] == 128 || $sub['sub_estatus'] == 120 || $sub['sub_estatus'] == 102) && ($sub['sub_area'] == 4 || $sub['sub_area'] == 8)) { $rezago = 1; 	}
	}
	if($sub12 == 1 && $rezago == 1) {
		echo $ord['orden_id'] . '<br>';
	}
	$sub12 = 0; $rezago = 0;
}






?>