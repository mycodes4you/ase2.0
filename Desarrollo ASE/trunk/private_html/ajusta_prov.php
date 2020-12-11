<?php 
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/factura.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if ($_SESSION['usuario'] == '701' || $_SESSION['usuario'] == '1000') {
	// Acceso permitido
} else {
	redirigir('usuarios.php');
}

$preg1 = "SELECT op_id FROM " . $dbpfx . "prod_prov WHERE sub_orden_id IS NULL AND fecha_cotizado > '" . $fecha . "' GROUP BY op_id LIMIT 100";
$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Prod_Prov! " . $preg1);
$fila1 = mysql_num_rows($matr1);
while($op = mysql_fetch_array($matr1)) {
	$preg2 = "SELECT sub_orden_id FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op['op_id'] . "'";
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Prod_Prov! " . $preg2);
	$sub = mysql_fetch_array($matr2);
	$param = "op_id = '" . $op['op_id'] . "'";
	$sqlop['sub_orden_id'] = $sub['sub_orden_id'];
	ejecutar_db($dbpfx . 'prod_prov', $sqlop, 'actualizar', $param);
}
echo 'Se procesaron ' . $fila1 . ' OPs<br>';


?>