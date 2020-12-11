<?php
/*
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}

$preg = "SELECT pedido_id FROM " . $dbpfx . "pedidos WHERE pedido_id >= '" . $pedido . "' LIMIT 200";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion! " . $preg);
while ($ped = mysql_fetch_array($matr)) {
	$datos = recalcUtilPed($ped['pedido_id'], $dbpfx);
	$mm++;
	$numped = $ped['pedido_id'];
//	echo '<br>Pedido: ' . $ped['pedido_id'] . ' ';
}

echo 'Fueron ' . $mm . ' Ultimo: ' . $numped;

*/

include('/home/autoshop/domains/tronco/parciales/funciones.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}  // echo $k.' -> '.$v.' | ';

$preg = "SELECT pedido_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $pedido . "'";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion! " . $preg);
while ($ped = mysql_fetch_array($matr)) {
        $datos = recalcUtilPed($ped['pedido_id'], $dbpfx);
}

?>
