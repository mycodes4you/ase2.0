<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}

$preg = "SELECT pedido_id, orden_id, pedido_tipo FROM " . $dbpfx . "pedidos WHERE prov_id = '57' AND pedido_tipo != '1'";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
while ($ped = mysql_fetch_array($matr)) {
	$preg1 = "UPDATE " . $dbpfx . "pedidos SET pedido_tipo = '1' WHERE pedido_id = '" . $ped['pedido_id'] . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección! " . $preg1);

	$preg2 = "UPDATE " . $dbpfx . "orden_productos SET op_autosurtido = '1' WHERE op_pedido = '" . $ped['pedido_id'] . "'";
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección! " . $preg2);
	
	bitacora($ped['orden_id'], 'Pedido ' . $ped['pedido_id'] . ' cambiado de tipo ' . $ped['pedido_tipo'] . ' a tipo 1.', $dbpfx);
}
?>