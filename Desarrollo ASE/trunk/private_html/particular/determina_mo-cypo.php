<?php
		$utbase = 0;
		if($_SESSION['dest']['sub_area'][$k] == '4' || $sub['sub_area'] == '4' ) {
				$utbase = 50;
		} elseif($_SESSION['dest']['sub_area'][$k] == '6' || $sub['sub_area'] == '6') {
				$utbase = 70;
		} elseif($_SESSION['dest']['sub_area'][$k] == '7' || $sub['sub_area'] == '7') {
				$utbase = 70;
		} elseif($_SESSION['dest']['sub_area'][$k] == '8' || $sub['sub_area'] == '8') {
				$utbase = 50;
		}

if($accion==='cesta' || $accion==='generar') {

		$pregpar = "SELECT sub_orden_id, sub_area, sub_reporte, sub_estatus, recibo_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $sub['orden_id'] . "' AND sub_estatus = '112' ";
		if($sub['sub_area'] == '6' || $sub['sub_area'] == '7') {
			// --- Si la tarea que se está revisando es 6 o 7, el query se amarra al número de la tarea, así sólo se llama esta ---
			$pregpar .= " AND sub_orden_id = '" . $sub['sub_orden_id'] . "' ";
		} elseif($sub['sub_area'] == '4' || $sub['sub_area'] == '8') {
			// --- Si la tarea es 4 o 8, que pueden o no tener tienen MO, entonces se incluyen todas las del mismo reporte para obtener 
			// --- sus relacionadas 6 para 4 y 7 para 8.
			$pregpar .= " AND sub_reporte = '" . $sub['sub_reporte'] . "' ";
			if($sub['sub_area'] == '4') {
				// --- Si la tarea que se está revisando es 4, se incluyen las de su área y las de 6 para obenter todo lo valuado de ambas áreas ---
				$pregpar .= " AND (sub_area = '4' OR sub_area = '6') ";
			} elseif($sub['sub_area'] == '8') {
				// --- Si la tarea que se está revisando es 8, se incluyen las de su área y las de 7 para obenter todo lo valuado de ambas áreas ---
				$pregpar .= " AND (sub_area = '8' OR sub_area = '7') ";
			}
		}
		$matrpar = mysql_query($pregpar) or die("ERROR: Fallo selección de Mano de Obra! " . $pregpar);
		$horas = 0; $hpar = 0; $hcalc = 0;
		while($regpar = mysql_fetch_array($matrpar)) {
			// --- Obtiene las horas por pagar, sólo de las valuadas, excluyendo TOTs, que son las que tienen pedido ---
			$pregmo = "SELECT op_cantidad, op_precio FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $regpar['sub_orden_id'] . "' AND op_tangible = '0' AND op_pedido < '1' AND op_pres IS NULL";
			$matrmo = mysql_query($pregmo) or die("ERROR: Fallo selección de Mano de Obra! " . $pregmo);
			while($modes = mysql_fetch_array($matrmo)) {
				if($sub['sub_area'] == '6' || $sub['sub_area'] == '7') {
					// --- Si la tarea que se está revisando es 6 o 7, simplemente se suman las horas y termina la búsqueda ---
					// --- Si es 4 u 8, de todas maneras se obtienen para determinar cuantas corresponden a este trabajo ---
					$horas = $horas + $modes['op_cantidad'];
				} elseif($sub['sub_area'] == '4' || $sub['sub_area'] == '8') {
					// --- Si la tarea tiene recibo de pago, se deben descontar del total obtenido en las tareas relacionadas ---
					if($regpar['recibo_id'] > 0) {
						$pregrecid = "SELECT monto, porcentaje FROM " . $dbpfx . "destajos_elementos WHERE recibo_id = '" . $regpar['recibo_id'] . "' AND area = '" . $regpar['sub_area'] . "' AND reporte = '" . $regpar['sub_reporte'] . "'";
						$matrrecid = mysql_query($pregrecid) or die("ERROR: Fallo selección de Mano de Obra! " . $pregrecid);
						$dstpag = mysql_fetch_array($matrrecid);
						$hcalc = $hcalc + (($dstpag['monto'] + $dstpag['porcentaje']) / $utbase);
					} else {
						// --- No se espera que tengan MO, pero de todas manerar se revisa y si llegan a tener, todos los demás cálculos ---
						// --- ya no se usan y sólo se toma en cuenta esta variable ---
						$hpar = $hpar + $modes['op_cantidad'];
					}
				}
			}
		}

		if($hpar > 0) {
			$mo = ($hpar * $utbase);
		} elseif($hcalc > 0) {
			$mo = ($horas - $hcalc) * $utbase;
		} else {
			$mo = ($horas * $utbase);
		}
		$cons = $sub['sub_consumibles'];
}

if($accion==='gestionar') {

			if($_SESSION['dest']['sub_mo'][$k] == 0) {
				$_SESSION['dest']['monto'][$k] = 0;
				$_SESSION['msjerror'] = 'El destajo debe estar basado en horas autorizadas, no sólo en Variación!<br>';
			} elseif($_SESSION['dest']['comision'][$k] == 1) {
			// --- No aplica pago ---
			} elseif($_SESSION['dest']['decodi'][$k] == '1') {
				$_SESSION['dest']['monto'][$k] = round($_SESSION['dest']['porcen'][$k], 2);
			} elseif($moycons == '1') {
				$parades = $_SESSION['dest']['sub_mo'][$k] + $_SESSION['dest']['sub_consumibles'][$k];
				$_SESSION['dest']['monto'][$k] = round((($parades * $_SESSION['dest']['porcen'][$k]) / 100), 2) - $_SESSION['dest']['costcons'][$k];
			} elseif($destpiezas == '1' && $_SESSION['dest']['sub_area'][$k] == '7') {
				$_SESSION['dest']['monto'][$k] = round(($_SESSION['dest']['piezas'][$k] * $_SESSION['dest']['porcen'][$k]), 2);
			} elseif($destoper != '1') {
				$_SESSION['dest']['monto'][$k] = $_SESSION['dest']['sub_mo'][$k] + $_SESSION['dest']['porcen'][$k];
			} else {
				$_SESSION['dest']['monto'][$k] = round(((($_SESSION['dest']['sub_mo'][$k] * $_SESSION['dest']['porcen'][$k]) / 100) - ($_SESSION['dest']['costcons'][$k] * 0.5)), 2);
			}
}

?>
