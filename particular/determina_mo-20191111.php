<?php
// Eliminar comentarios para habilitar pago de destajo a tarifa fija para OT de Aseguradora  

if($accion==='cesta' || $accion==='generar') {

	$mo = 0;
	if($sub['sub_estatus'] == '112' || ($destsinterm == 1 && (($sub['sub_estatus'] >= '104' && $sub['sub_estatus'] <= '111') || $sub['sub_estatus'] == '121'))) {
		$pregmo = "SELECT op_cantidad, op_precio FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_tangible = '0' AND op_pedido < '1' AND op_pres IS NULL";
		$matrmo = mysql_query($pregmo) or die("ERROR: Fallo selección de Mano de Obra! " . $pregmo);
		while($opdes = mysql_fetch_array($matrmo)) {
			$mo = $mo + ($opdes['op_cantidad'] * $opdes['op_precio']);
		}
	}
	$mo = round($mo,2);
}

if($accion==='gestionar') {
	if($_SESSION['dest']['comision'][$k] == 1) {

	} elseif($_SESSION['dest']['decodi'][$k] == '1') {
		$_SESSION['dest']['monto'][$k] = round($_SESSION['dest']['porcen'][$k], 2);
	} elseif($moycons == '1' && $destoper != '1') {
//		if($_SESSION['dest']['sub_area'][$k] == '7') { $factorHP = 0.064; } else { $factorHP = 0.108; }
//		$parades = $_SESSION['dest']['sub_mo'][$k];
//		$_SESSION['dest']['monto'][$k] = ($parades * $factorHP) + $_SESSION['dest']['porcen'][$k];
	} elseif($destpiezas == '1' && $_SESSION['dest']['sub_area'][$k] == '7') {
		$_SESSION['dest']['monto'][$k] = round(($_SESSION['dest']['piezas'][$k] * $_SESSION['dest']['porcen'][$k]), 2);
	} elseif($destoper != '1') {
		if($_SESSION['dest']['sub_reporte'][$k] == '0' || $_SESSION['dest']['sub_reporte'][$k] == '') {
			// --- Tareas Particulares ---
			if($_SESSION['dest']['sub_area'][$k] == '7') { $factorHP = 0.10; } else { $factorHP = 0.10; }
		} else {
			// --- Tareas de Aseguradora ---
			if($_SESSION['dest']['sub_area'][$k] == '7') { $factorHP = 0.255; } else { $factorHP = 0.14; }
		}
                $parades = $_SESSION['dest']['sub_mo'][$k];
                $_SESSION['dest']['monto'][$k] = ($parades * $factorHP) + $_SESSION['dest']['porcen'][$k];

	} else {
		$_SESSION['dest']['monto'][$k] = round($_SESSION['dest']['porcen'][$k], 2);
	}
}

?>
