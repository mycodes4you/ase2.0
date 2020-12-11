<?php
// Eliminar comentarios para habilitar pago de destajo a tarifa fija para OT de Aseguradora  

if($accion==='cesta' || $accion==='generar') {
	// --- Obtiene base de destajo por aseguradora ---
	$pregbasdes = "SELECT sub_orden_id, sub_consumibles FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $sub['sub_reporte'] . "' AND orden_id = '" . $sub['orden_id'] . "' ";
	if($destsinterm == 1) {
		$pregbasdes .= " AND ((sub_estatus >= '105' AND sub_estatus <= '112') OR sub_estatus = '121') ";
	} else {
		$pregbasdes .= " AND sub_estatus = '112' ";
	}
	$pregbasdes .= " AND (sub_area = '6' OR sub_area = '7')";
	$matrbasdes = mysql_query($pregbasdes) or die("ERROR: Fallo selección de base de destajo! " . $pregbasdes);
//	echo $pregbasdes;
	$ValAuthMO = 0; $cons = 0; $ToT = 0; $costcons = 0;
	while ($basdes = mysql_fetch_array($matrbasdes)) {
		$pregmo = "SELECT op_cantidad, op_precio, op_costo, op_pedido, op_pres, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $basdes['sub_orden_id'] . "' AND (op_tangible = '0' OR op_tangible = '2')";
		$matrmo = mysql_query($pregmo) or die("ERROR: Fallo selección de Mano de Obra! " . $pregmo);
		while($modes = mysql_fetch_array($matrmo)) {
			// --- Obtiene los montos autorizados y montos asignados a TOTs ---
			if($modes['op_tangible'] == '0' && (is_null($modes['op_pres']) || $modes['op_pres'] < 1)) {
				$ValAuthMO = $ValAuthMO + round(($modes['op_cantidad'] * $modes['op_precio']),2);
			}
			if($modes['op_tangible'] == '0' && $modes['op_pedido'] > 0) {
				$ToT = $ToT + round(($modes['op_cantidad'] * $modes['op_precio']),2);
			}
			if($modes['op_tangible'] == '2') {
				$costcons = $costcons + round(($modes['op_cantidad'] * $modes['op_costo']),2);
			}
		}
		$cons = $cons + $basdes['sub_consumibles'];
	}
	$mo = round(($ValAuthMO - $ToT),2);
	$cons = round($cons,2);
	// --- Esta variable forza a considerar el costo de pintura en todas las áreas
	// --- $CostoEnH = 1;
}

if($accion==='gestionar') {
	if($_SESSION['dest']['comision'][$k] == 1) {
		
	} elseif($_SESSION['dest']['decodi'][$k] == '1') {
		$_SESSION['dest']['monto'][$k] = round($_SESSION['dest']['porcen'][$k], 2);
	} elseif($moycons == '1' && $destoper != '1') {
		if($_SESSION['dest']['sub_area'][$k] == '7') { $factorHP = 0.0468; } else { $factorHP = 0.0702; }
		$parades = $_SESSION['dest']['sub_mo'][$k];
		$_SESSION['dest']['monto'][$k] = ($parades * $factorHP) - $_SESSION['dest']['porcen'][$k];
	} elseif($destpiezas == '1' && $_SESSION['dest']['sub_area'][$k] == '7') {
		$_SESSION['dest']['monto'][$k] = round(($_SESSION['dest']['piezas'][$k] * $_SESSION['dest']['porcen'][$k]), 2);
	} elseif($destoper != '1') {
//		$_SESSION['dest']['monto'][$k] = round(((($_SESSION['dest']['sub_mo'][$k] * $_SESSION['dest']['porcen'][$k]) / 100) - ($_SESSION['dest']['costcons'][$k] * 0.5)), 2);
                if($_SESSION['dest']['sub_area'][$k] == '7') { $factorHP = 0.0468; } else { $factorHP = 0.0702; }
                $parades = $_SESSION['dest']['sub_mo'][$k];
                $_SESSION['dest']['monto'][$k] = ($parades * $factorHP) - $_SESSION['dest']['porcen'][$k];

	} else {
		$_SESSION['dest']['monto'][$k] = round($_SESSION['dest']['porcen'][$k], 2);
	}
}

?>
