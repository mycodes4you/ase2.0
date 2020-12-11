<?php

if($accion==='cesta' || $accion==='generar') {

	// --- Obtiene las horas por pagar, excluyendo TOTs ---
	$pregmo = "SELECT op_cantidad, op_precio FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_tangible = '0' AND op_pedido < '1' AND op_pres IS NULL";
	$matrmo = mysql_query($pregmo) or die("ERROR: Fallo selección de Mano de Obra! " . $pregmo);
	$mo = 0;
	while($modes = mysql_fetch_array($matrmo)) {
		$mo = $mo + ($modes['op_cantidad'] * $modes['op_precio']);
	}
	$cons = $sub['sub_consumibles'];
}

if($accion==='gestionar') {

			if($_SESSION['dest']['comision'][$k] == 1){
				
			} elseif($_SESSION['dest']['decodi'][$k] == '1') {
				$_SESSION['dest']['monto'][$k] = round($_SESSION['dest']['porcen'][$k], 2);
			} elseif($moycons == '1' && $destoper != '1') {
				$parades = $_SESSION['dest']['sub_mo'][$k] + $_SESSION['dest']['sub_consumibles'][$k];
				$_SESSION['dest']['monto'][$k] = round((($parades * $_SESSION['dest']['porcen'][$k]) / 100), 2) - $_SESSION['dest']['costcons'][$k];
			} elseif($destpiezas == '1' && $_SESSION['dest']['sub_area'][$k] == '7') {
				$_SESSION['dest']['monto'][$k] = round(($_SESSION['dest']['piezas'][$k] * $_SESSION['dest']['porcen'][$k]), 2);
			} elseif($destoper != '1') {
				$_SESSION['dest']['monto'][$k] = round(((($_SESSION['dest']['sub_mo'][$k] * $_SESSION['dest']['porcen'][$k]) / 100) - ($_SESSION['dest']['costcons'][$k] * 0.5)), 2);
			} else {
				$_SESSION['dest']['monto'][$k] = round($_SESSION['dest']['porcen'][$k], 2);
			}

}

?>
