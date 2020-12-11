<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');


if ($_SESSION['usuario'] != 701) {
	redirigir('usuarios.php');
}

$preg = "SELECT pago_id, recibo_id FROM " . $dbpfx . "destajos_pagos WHERE usuario_pago_recibido IS NULL OR usuario_pago_recibido < '1'";
$matr = mysql_query($preg) or die("ERROR: Fallo selección de pagos! " . $preg);
while ($pago = mysql_fetch_array($matr)) {
	$preg0 = "SELECT usuario FROM " . $dbpfx . "destajos WHERE recibo_id = '" . $pago['recibo_id'] . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de recibos! " . $preg0);
	while ($rec = mysql_fetch_array($matr0)) {
		$parametros = 'pago_id = ' . $pago['pago_id'];
	  	$sql_data_array = array('usuario_pago_recibido' => $rec['usuario']);
//		print_r($sql_data_array);
		ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'actualizar', $parametros);
		unset($sql_data_array);
	}
}






?>
