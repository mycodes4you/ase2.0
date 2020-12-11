<?php
// Eliminar comentarios para habilitar pago de destajo a tarifa fija para OT de Aseguradora  

if($accion==='cesta') {
	$mo = $sub['sub_mo'];
	$cons = $sub['sub_consumibles'];			
 /*
			if($sub['sub_reporte'] == '0') {
				$mo = $mo_part;
			}
		
			$tiempo = explode(':', $sub['sub_horas_programadas']);
			$minutos = $tiempo[1]/60;
			$horas = $tiempo[0] + $minutos;
			if($sub['sub_reporte'] == '0') {
				$mo = $sub['sub_mo'];
				$cons = $sub['sub_consumibles'];
			} else {
				$mo = $utlocal * $horas;
				$cons = $sub['sub_consumibles'] * 0.8;
			}
*/
}			

if($accion==='generar') {
	$mo = $sub['sub_mo'];
	$cons = $sub['sub_consumibles'];			
 /*
			if($sub['sub_reporte'] == '0') {
				$mo = $mo_part;
			}
		
			$tiempo = explode(':', $sub['sub_horas_programadas']);
			$minutos = $tiempo[1]/60;
			$horas = $tiempo[0] + $minutos;
			if($sub['sub_reporte'] == '0') {
				$mo = $sub['sub_mo'];
				$cons = $sub['sub_consumibles'];
			} else {
				$mo = $utlocal * $horas;
				$cons = $sub['sub_consumibles'] * 0.8;
			}
*/
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
			
//	$pregdetmo = "SELECT sub";			
			
}


?>