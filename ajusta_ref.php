<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}
	$preg2 = "SELECT orden_id, sub_orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id >= '" . $tarea . "' AND sub_refacciones_recibidas > '0' AND sub_estatus < '130' LIMIT 50";
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de orden_productos - subordenes!");
echo $preg2;
	while ($sub = mysql_fetch_array($matr2)) {
		echo '<br>' . $sub['orden_id'] . ' ';
		$resultado = ajustaTarea($sub['sub_orden_id'], $dbpfx);
		echo $resultado;
	}

?>
