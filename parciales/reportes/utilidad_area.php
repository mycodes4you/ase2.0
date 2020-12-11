<?php

if ($f1125030 == '1' || $_SESSION['rol02']=='1') {

	if($estatusflt >= '5' && $estatusflt <= '7') { // --- Cobrados y Cobrables No Facturados ---

		$preg0 = "SELECT DISTINCT o.orden_id, o.orden_estatus, o.orden_vehiculo_placas, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_id, o.orden_alerta, o.orden_asesor_id, o.orden_fecha_recepcion, o.orden_fecha_de_entrega, o.orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes o";

	} else { // --- Facturados No cobrados, Todos los Recibidos, En documentación, En reparación, Terminados, Entregados ---

		$preg0 = "SELECT orden_id, orden_estatus, orden_vehiculo_placas, orden_vehiculo_tipo, orden_vehiculo_color, orden_vehiculo_id, orden_alerta, orden_asesor_id, orden_fecha_recepcion, orden_fecha_de_entrega, orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes WHERE ";

	}

	if($estatusflt == '5') { // --- Cobrados ---

		// --- Consultamos las facturas por cobrar, que ya hallan sido cobradas ---

		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '1' AND f.fact_tipo < '3' AND o.orden_fecha_de_entrega >= '" . $feini . "' AND o.orden_fecha_de_entrega <= '" . $fefin . "' AND (o.orden_estatus < '30' OR o.orden_estatus = '99') ";


	} elseif($estatusflt == '6') { // --- Facturados No cobrados ---

		// --- Consultamos las facturas por cobrar, que no hallan sido pagadas ---
		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '0' AND f.fact_tipo < '3' AND o.orden_fecha_de_entrega >= '" . $feini . "' AND o.orden_fecha_de_entrega <= '" . $fefin . "' ";
		$rangofe = $lang['Fecha de Facturas'];

	} elseif($estatusflt == '7') { // --- Cobrables No Facturados ---

		// --- Se deben de buscar todos los siniestros y agruparlos, posterirmente se debe de localizar los que tienen pendiente el cobro, es decir que no están facturados.
		//$preg0 .= " WHERE NOT EXISTS ( SELECT null FROM " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_tipo < '3' ) AND ((o.orden_estatus >= '12' AND o.orden_estatus <= '16') OR o.orden_estatus = '99') AND o.orden_fecha_ultimo_movimiento >= '" . $feini . "' AND o.orden_fecha_ultimo_movimiento <= '" . $fefin . "' ";

		$preg0 .= " WHERE o.orden_fecha_de_entrega >= '" . $feini . "' AND o.orden_fecha_de_entrega <= '" . $fefin . "' AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$rangofe = $lang['Fecha de Ultimo Movimiento'];

	} elseif($estatusflt == '4') { // --- Entregados ---

		$preg0 .= " orden_fecha_de_entrega > '" . $feini . "' AND orden_fecha_de_entrega < '" . $fefin . "' AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Entregados Autorizados';
		$rangofe = $lang['Fecha de Entrega'];

	} elseif($estatusflt == '3') { // --- Terminados ---

		$preg0 .= " orden_fecha_ultimo_movimiento > '" . $feini . "' AND orden_fecha_ultimo_movimiento < '" . $fefin . "'  AND (orden_estatus >= '12' AND orden_estatus <= '15') ";
		$nomrep = 'Terminados';
		$rangofe = $lang['Fecha de Ultimo Movimiento'];

	} elseif($estatusflt == '2') { // --- En reparación ---

		// ------ Se eliminan rangos de fechas para incluir todos los trabajos de este tipo en virtud de que no deben existir demasiados
		$preg0 .= " ((orden_estatus >= '4' AND orden_estatus <= '11') OR orden_estatus = '21') ";
		$nomrep = 'Reparación';
		$rangofe = $lang['Fecha No Aplica'];

	} elseif($estatusflt == '1') { // --- En documentación ---

		$preg0 .= " orden_estatus <= '2' OR orden_estatus = '17' OR orden_estatus = '20' OR (orden_estatus >= '24' AND orden_estatus <= '29') ";
		$nomrep = 'Por Autorizar';
		$rangofe = $lang['Fecha No Aplica'];

	} elseif($estatusflt == '0') { // --- Todos los Recibidos ---

		$preg0 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'  AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Todos los Recibidos';
		$rangofe = $lang['Fecha de Recepción'];

	} else { // --- Default ---

		$preg0 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'  AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Todos los Recibidos';
		$rangofe = $lang['Fecha de Recepción'];

	}

	//echo '<big>' . $preg0 . '</big>';
	//echo 'Filtro ' . $estatusflt . '<br>';

	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de lapso! ".$preg0);
	$filas = mysql_num_rows($matr0);

	if($export == 1) {
		$encabezado = 'Reporte de Utilidad por área, OTs recibidas del ' . $t_ini . ' al ' . $t_fin . ' ' . $rangofe;

		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'REPORTE DE UTILIDAD X ÁREA: ' . $nombre_agencia;

		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("REPORTE DE UTILIDAD X ÁREA")
					->setKeywords("AUTOSHOP EASY");

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($celda, $titulo)
					->setCellValue("A3", $fecha_export);

		// ------ ENCABEZADOS ---
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A4", "OT")
					->setCellValue("B4", "Vehículo")
					->setCellValue("C4", $lang['Placa'])
					->setCellValue("D4", "Estatus")
					->setCellValue("E4", "Cliente")
					->setCellValue("F4", $lang['Siniestro'])
					->setCellValue("G4", "Fecha Ingreso")
					->setCellValue("H4", "Fecha Entrega")
					->setCellValue("I4", "Facturas")
					->setCellValue("J4", "Fecha Factura")
					->setCellValue("K4", "Venta de Ref Mec.")
					->setCellValue("L4", "Costo de Ref Mec.")
					->setCellValue("M4", "Util. de Ref Mec.")
					->setCellValue("N4", "Venta de MO Mec.")
					->setCellValue("O4", "Costo de MO Mec.")
					->setCellValue("P4", "Util. de MO Mec.")
					->setCellValue("Q4", "Venta de Ref Hoj.")
					->setCellValue("R4", "Costo de Ref Hoj.")
					->setCellValue("S4", "Util. de Ref Hoj.")
					->setCellValue("T4", "Venta de MO Hojal.")
					->setCellValue("U4", "Costo de MO Hojal.")
					->setCellValue("V4", "Util. de MO Hojal.")
					->setCellValue("W4", "Venta de Consu. Pint.")
					->setCellValue("X4", "Costo de Consu. Pint.")
					->setCellValue("Y4", "Util. de Consu. Pint.")
					->setCellValue("Z4", "Venta de MO Pint.")
					->setCellValue("AA4", "Costo de MO Pint.")
					->setCellValue("AB4", "Util. de MO Pint.")
					->setCellValue("AC4", "Venta de Ref Otros")
					->setCellValue("AD4", "Costo de Ref Otros")
					->setCellValue("AE4", "Util. de Ref Otros")
					->setCellValue("AF4", "Venta de MO Otros")
					->setCellValue("AG4", "Costo de MO Otros")
					->setCellValue("AH4", "Util. de MO Otros");
		$z= 5;
	} else {
		$encabezado = ' Reporte de Utilidad por área, OTs recibidas del ' . $t_ini . ' al ' . $t_fin . ' <small><span style="font-weight:bold; color: red;">' . $rangofe . '</span></small>';
		echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-12 panel-title">'."\n";
		if($asegflt != ""){
			echo '					<img src="' . $ase[$asegflt][0] . '"/>';
		}
		echo '
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2><small>' . $encabezado . '</small></h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 ">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
					<tr>
						<td colspan="10"></td>
						<td colspan="6" class="areaotra"><b>Mécanica</b></td>
						<td colspan="6" class="area6"><b>Hojalateria</b></td>
						<td colspan="6" class="area7"><b>Pintura</b></td>
						<td colspan="6" class="areaotra"><b>Otras Áreas</b></td>
					</tr>
					<tr>
						<th>OT</th>
						<th>Vehículo</th>
						<th>' . $lang['Placa'] . '</th>
						<th>Estatus</th>
						<th>Cliente</th>
						<th>' . $lang['Siniestro'] . '</th>
						<th>Fecha Ingreso</th>
						<th>Fecha Entrega</th>
						<th>Facturas</th>
						<th>Fecha Facturas</th>
						<td class="areaotra"><b>Venta de<br>Ref</b></td>
						<td class="areaotra"><b>Costo de<br>Ref</b></td>
						<td class="areaotra"><b>Utili. de<br>Ref</b></td>
						<td class="areaotra"><b>Venta de<br>MO</b></td>
						<td class="areaotra"><b>Costo de<br>MO</b></td>
						<td class="areaotra"><b>Utili. de<br>MO</b></td>
						<td class="area6"><b>Venta de<br>Ref</b></td>
						<td class="area6"><b>Costo de<br>Ref</b></td>
						<td class="area6"><b>Utili. de<br>Ref</b></td>
						<td class="area6"><b>Venta de<br>MO</b></td>
						<td class="area6"><b>Costo de<br>MO</b></td>
						<td class="area6"><b>Utili. de<br>MO</b></td>
						<td class="area7"><b>Venta de<br>Consu.</b></td>
						<td class="area7"><b>Costo de<br>Consu.</b></td>
						<td class="area7"><b>Utili. de<br>Consu.</b></td>
						<td class="area7"><b>Venta de<br>MO</b></td>
						<td class="area7"><b>Costo de<br>MO</b></td>
						<td class="area7"><b>Utili. de<br>MO</b></td>
						<td class="areaotra"><b>Venta de<br>Ref</b></td>
						<td class="areaotra"><b>Costo de<br>Ref</b></td>
						<td class="areaotra"><b>Utili. de<br>Ref</b></td>
						<td class="areaotra"><b>Venta de<br>MO</b></td>
						<td class="areaotra"><b>Costo de<br>MO</b></td>
						<td class="areaotra"><b>Utili. de<br>MO</b></td>
					</tr>'."\n";
		$fondo = 'claro';
	}

	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	$ventas = 0;

	while($ord = mysql_fetch_array($matr0)) {
		// ---- Consultar los siniestros de la orden ----
		$preg_siniestros = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";

		if($estatusflt == '7') { // --- Cobrables No Facturados ---
			$preg_siniestros .= " AND sub_presupuesto > '0' ";
		}

		if($asegflt != '') {
			$preg_siniestros .= " AND sub_aseguradora = '" . $asegflt . "' ";
		}

		$preg_siniestros .= " GROUP BY sub_reporte";

		$matr_siniestros = mysql_query($preg_siniestros) or die ('Falló: ' . $preg_siniestros);

		while($sin = mysql_fetch_array($matr_siniestros)) {
			// --- Recorrer las tareas del siniestro ----
			$preg_tareas = "SELECT DISTINCT sub_partes, sub_mo, sub_consumibles, sub_orden_id, sub_reporte, sub_aseguradora, fact_id, sub_area, recibo_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' AND sub_reporte = '" . $sin['sub_reporte'] . "'";
			$matr_tareas = mysql_query($preg_tareas) or die ('Falló: ' . $preg_tareas);
			unset($tot_venta_ref);
			unset($tot_venta_mo);
			unset($tot_venta_cons);
			unset($costo_refs);
			unset($costo_mo);
			unset($costo_cons);
			// --- Consultar los recibos de destajo del siniestro ---
			$preg_destajos = "SELECT area, monto FROM " . $dbpfx . "destajos_elementos WHERE orden_id = '" . $ord['orden_id'] . "' AND reporte = '" . $sin['sub_reporte'] . "'";
			$matr_destajos = mysql_query($preg_destajos) or die ('Falló: ' . $preg_destajos);
			while($destajos = mysql_fetch_array($matr_destajos)) {
				$costo_mo[$destajos['area']] = $costo_mo[$destajos['area']] + $destajos['monto'];
			}
			// ---- Recorrer tareas del reporte ----
			while($tareas = mysql_fetch_array($matr_tareas)) {
				$tot_venta_ref[$tareas['sub_area']] = $tot_venta_ref[$tareas['sub_area']] + $tareas['sub_partes'];
				$tot_venta_mo[$tareas['sub_area']] = $tot_venta_mo[$tareas['sub_area']] + $tareas['sub_mo'];
				$tot_venta_cons[$tareas['sub_area']] = $tot_venta_cons[$tareas['sub_area']] + $tareas['sub_consumibles'];
				// ---- Consultar el costo de las refacciones contemplando las refacciones de pedidos y de paquetes de servicio ---
				$preg_refs = "SELECT op_cantidad, op_area, op_costo, op_pedido, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $tareas['sub_orden_id'] . "' AND op_autosurtido != '1' AND (op_surtidos = op_cantidad OR op_pedido > '0') AND op_tangible < '3'";
				$matr_refs = mysql_query($preg_refs);
				while($refs = mysql_fetch_array($matr_refs)) {
					if($refs['op_tangible'] == '0' && $refs['op_pedido'] > 0) {
						$costo_mo[$refs['op_area']] = $costo_mo[$refs['op_area']] + ($refs['op_costo'] * $refs['op_cantidad']);
					} elseif($refs['op_tangible'] == '1') {
						$costo_refs[$refs['op_area']] = $costo_refs[$refs['op_area']] + ($refs['op_costo'] * $refs['op_cantidad']);
					} elseif($refs['op_tangible'] == '2') {
						$costo_cons[$refs['op_area']] = $costo_cons[$refs['op_area']] + ($refs['op_costo'] * $refs['op_cantidad']);
					}
				}
			}
			$totros_venta_ref = 0;
			$totros_venta_mo = 0;
			$totros_venta_cons = 0;
			$totros_costo_ref = 0;
			$totros_costo_mo = 0;
			$totros_costo_cons = 0;

			// --- Calcular Utilidad  y sumar otras áreas ---
			if($margutil == '') { $margutil = 30;}
			for($art = 1; $art <= $num_areas_servicio; $art++) {
				$u_ref[$art] = ' style="background-color: #C6D9B6;"';
				$u_mo[$art] = 'style="background-color: #C6D9B6;"';
				$u_cons[$art] = 'style="background-color: #C6D9B6;"';
				if($tot_venta_ref[$art] == 0 && $costo_refs[$art] > 0) {
					$util_ref[$art] = '-100';
					$u_ref[$art] = ' style="background-color: #ff6666; text-align: center;"';
				} elseif($tot_venta_ref[$art] > 0) {
					$util_ref[$art] = round(((($tot_venta_ref[$art] - $costo_refs[$art]) / $tot_venta_ref[$art]) * 100),2);
					if($util_ref[$art] < $margutil) {
						// --- Si el margen de utilidad es menor a $margutil, cambia el color del fondo de la celda --
						$u_ref[$art] = ' style="background-color: #ff6666; text-align: center;"';
					}
				} else {
					$util_ref[$art] = '--';
				}
				if($tot_venta_mo[$art] == 0 && $costo_mo[$art] > 0) {
					$util_mo[$art] = '-100';
					$u_mo[$art] = ' style="background-color: #ff6666; text-align: center;"';
				} elseif($tot_venta_mo[$art] > 0) {
					$util_mo[$art] = round(((($tot_venta_mo[$art] - $costo_mo[$art]) / $tot_venta_mo[$art]) * 100),2);
					if($util_mo[$art] < $margutil) {
						// --- Si el margen de utilidad es menor a $margutil, cambia el color del fondo de la celda --
						$u_mo[$art] = ' style="background-color: #ff6666; text-align: center;"';
					}
				} else {
					$util_mo[$art] = '--';
				}
				if($tot_venta_cons[$art] == 0 && $costo_cons[$art] > 0) {
					$util_cons[$art] = '-100';
					$u_cons[$art] = ' style="background-color: #ff6666; text-align: center;"';
				} elseif($tot_venta_cons[$art] > 0) {
					$util_cons[$art] = round(((($tot_venta_cons[$art] - $costo_cons[$art]) / $tot_venta_cons[$art]) * 100),2);
					if($util_cons[$art] < $margutil) {
						// --- Si el margen de utilidad es menor a $margutil, cambia el color del fondo de la celda --
						$u_cons[$art] = ' style="background-color: #ff6666; text-align: center;"';
					}
				} else {
					$util_cons[$art] = '--';
				}

				if($art != 1 && $art != 6 && $art != 7) {
					$totros_venta_ref = $totros_venta_ref + $tot_venta_ref[$art];
					$totros_costo_ref = $totros_costo_ref + $costo_refs[$art];
					$totros_venta_mo = $totros_venta_mo + $tot_venta_mo[$art];
					$totros_costo_mo = $totros_costo_mo + $costo_mo[$art];
					$totros_venta_cons = $totros_venta_cons + $tot_venta_cons[$art];
					$totros_costo_cons = $totros_costo_cons + $costo_cons[$art];
				}
			}
			$u_otr_ref = ' style="background-color: #C6D9B6;"';
			$u_otr_mo = ' style="background-color: #C6D9B6;"';
			$u_otr_cons = ' style="background-color: #C6D9B6;"';
			if($totros_venta_ref == 0 && $totros_costo_ref > 0) {
				$util_otr_ref = '-100';
				$u_otr_ref = ' style="background-color: #ff6666; text-align: center;"';
			} elseif($totros_venta_ref > 0) {
				$util_otr_ref = round(((($totros_venta_ref - $totros_costo_ref) / $totros_venta_ref) * 100),2);
				if($util_otr_ref < $margutil) {
					// --- Si el margen de utilidad es menor a $margutil, cambia el color del fondo de la celda --
					$u_otr_ref = ' style="background-color: #ff6666; text-align: center;"';
				}
			} else {
				$util_otr_ref = '--';
			}
			if($totros_venta_mo == 0 && $totros_costo_mo > 0) {
				$util_otr_mo = '-100';
				$u_otr_mo = ' style="background-color: #ff6666; text-align: center;"';
			} elseif($totros_venta_mo > 0) {
				$util_otr_mo = round(((($totros_venta_mo - $totros_costo_mo) / $totros_venta_mo) * 100),2);
				if($util_otr_mo < $margutil) {
					// --- Si el margen de utilidad es menor a $margutil, cambia el color del fondo de la celda --
					$u_otr_mo = ' style="background-color: #ff6666; text-align: center;"';
				}
			} else {
				$util_otr_mo = '--';
			}
			if($totros_venta_cons == 0 && $totros_costo_cons > 0) {
				$util_otr_cons = '-100';
				$u_otr_cons = ' style="background-color: #ff6666; text-align: center;"';
			} elseif($totros_venta_cons > 0) {
				$util_otr_cons = round(((($totros_venta_cons - $totros_costo_cons) / $totros_venta_cons) * 100),2);
				if($util_otr_cons < $margutil) {
					// --- Si el margen de utilidad es menor a $margutil, cambia el color del fondo de la celda --
					$u_otr_cons = ' style="background-color: #ff6666; text-align: center;"';
				}
			} else {
				$util_otr_cons = '--';
			}

			if($export == 1) {
				// --- Celdas a grabar ----
				$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
				$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $J = 'J'.$z;
				$K = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z; $n = 'N'.$z; $o = 'O'.$z;
				$p = 'P'.$z; $q = 'Q'.$z; $r = 'R'.$z; $s = 'S'.$z; $t = 'T'.$z;
				$u = 'U'.$z; $v = 'V'.$z; $w = 'W'.$z; $x = 'X'.$z; $y = 'Y'.$z;
				$Z = 'Z'.$z; $AA = 'AA'.$z; $AB = 'AB'.$z; $AC = 'AC'.$z; $AD = 'AD'.$z;
				$AE = 'AE'.$z; $AF = 'AF'.$z; $AG = 'AG'.$z; $AH = 'AH'.$z; $AI = 'AI'.$z;

				$datos_vehi = strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']);
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($a, $ord['orden_id'])
							->setCellValue($b, $datos_vehi)
							->setCellValue($c, $ord['orden_vehiculo_placas'])
							->setCellValue($d, constant('ORDEN_ESTATUS_' . $ord['orden_estatus']))
							->setCellValue($e, $ase[$sin['sub_aseguradora']][1]);
			} else {
				echo '
					<tr class="' . $fondo . '">
						<td><a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '">' . $ord['orden_id'] . '</a></td>
						<td style="text-align: left !important;">' . strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']) . '</td>
						<td>' . $ord['orden_vehiculo_placas'] . '</td>
						<td>' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '</td>
						<td>' . $ase[$sin['sub_aseguradora']][1] . '</td>
						<td>';
			}
			if($sin['sub_reporte'] == '0' || $sin['sub_reporte'] == '') {
				if($export == 1) {
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($f, 'Particular');
				} else {
					echo 'Particular';
				}
				$numpart++;
				$sin['sub_reporte'] = '0';
			} else {
				if($export == 1){
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($f, $sin['sub_reporte']);
				} else {
					echo $sin['sub_reporte'];
				}
				$numsin++;
			}
			if($export != 1) {
				echo '</td>'."\n";
			}
			$timefeent = strtotime($ord['orden_fecha_de_entrega']);
			if($export == 1) {
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($g, date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])))
							->setCellValue($h, date('Y-m-d', $timefeent));
			} else {
				echo '						<td>' . date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])) . '</td>
						<td>';
				if($timefeent > 1000000) { echo date('Y-m-d', $timefeent); }
				else { echo ' -- '; }
				echo '</td>'."\n";
			}
			$pregfact = "SELECT fact_num, fact_fecha_emision FROM " . $dbpfx . "facturas_por_cobrar WHERE orden_id = '" . $ord['orden_id'] . "' AND fact_cobrada < '2'";
			$matrfact = mysql_query($pregfact) or die ('ERROR: Falló selección de facturas! 361 ' . $pregfact);
			unset($lafact);
			while($fact = mysql_fetch_array($matrfact)) {
				$lafact[$fact['fact_num']] = date('Y-m-d H:i', strtotime($fact['fact_fecha_emision']));
			}
			$facts = ''; $fefacts = '';

			foreach($lafact as $fk => $fv) {
				$facts .= $fk . ' ';
				$fefacts .= $fv . ' ';
			}

			if($export == 1) {
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($i, $facts)
							->setCellValue($J, $fefacts)
							->setCellValue($K, number_format($tot_venta_ref[1], 2))
							->setCellValue($l, number_format($costo_refs[1], 2))
							->setCellValue($m, round($util_ref[1], 2))
							->setCellValue($n, number_format($tot_venta_mo[1], 2))
							->setCellValue($o, number_format($costo_mo[1], 2))
							->setCellValue($p, round($util_mo[1], 2))
							->setCellValue($q, number_format($tot_venta_ref[6], 2))
							->setCellValue($r, number_format($costo_refs[6], 2))
							->setCellValue($s, round($util_ref[6], 2))
							->setCellValue($t, number_format($tot_venta_mo[6], 2))
							->setCellValue($u, number_format($costo_mo[6], 2))
							->setCellValue($v, round($util_mo[6], 2))
							->setCellValue($w, number_format($tot_venta_cons[7], 2))
							->setCellValue($x, number_format($costo_cons[7], 2))
							->setCellValue($y, round($util_cons[7], 2))
							->setCellValue($Z, number_format($tot_venta_mo[7], 2))
							->setCellValue($AA, number_format($costo_mo[7], 2))
							->setCellValue($AB, round($util_mo[7], 2))
							->setCellValue($AC, number_format($totros_venta_ref, 2))
							->setCellValue($AD, number_format($totros_costo_ref, 2))
							->setCellValue($AE, round($util_otr_ref, 2))
							->setCellValue($AF, number_format($totros_venta_mo, 2))
							->setCellValue($AG, number_format($totros_costo_mo, 2))
							->setCellValue($AH, round($util_otr_mo, 2));
				$z++;
			} else {
				echo '						<td>' . $facts . '</td>
						<td>' . $fefacts . '</td>
						<td>' . number_format($tot_venta_ref['1'], 2) . '</td>
						<td>' . number_format($costo_refs['1'], 2) . '</td>
						<td ' . $u_ref['1'] . '>' . round($util_ref['1'], 2) . '</td>
						<td>' . number_format($tot_venta_mo['1'], 2) . '</td>
						<td>' . number_format($costo_mo['1'], 2) . '</td>
						<td ' . $u_mo['1'] . '>' . round($util_mo['1'], 2) . '</td>
						<td>' . number_format($tot_venta_ref['6'], 2) . '</td>
						<td>' . number_format($costo_refs['6'], 2) . '</td>
						<td ' . $u_ref['6'] . '>' . round($util_ref['6'], 2) . '</td>
						<td>' . number_format($tot_venta_mo['6'], 2) . '</td>
						<td>' . number_format($costo_mo['6'], 2) . '</td>
						<td ' . $u_mo['6'] . '>' . round($util_mo['6'], 2) . '</td>
						<td>' . number_format($tot_venta_cons['7'], 2) . '</td>
						<td>' . number_format($costo_cons['7'], 2) . '</td>
						<td ' . $u_cons['7'] . '>' . round($util_cons['7'], 2) . '</td>
						<td>' . number_format($tot_venta_mo['7'], 2) . '</td>
						<td>' . number_format($costo_mo['7'], 2) . '</td>
						<td ' . $u_mo['7'] . '>' . round($util_mo['7'], 2) . '</td>
						<td>' . number_format($totros_venta_ref, 2) . '</td>
						<td>' . number_format($totros_costo_ref, 2) . '</td>
						<td ' . $u_otr_ref . '>' . round($util_otr_ref, 2) . '</td>
						<td>' . number_format($totros_venta_mo, 2) . '</td>
						<td>' . number_format($totros_costo_mo, 2) . '</td>
						<td ' . $u_otr_mo . '>' . round($util_otr_mo, 2) . '</td>
					<tr>';
				$ventas++;
				if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
			}
		}
	}

	if($export == 1) { // ---- Hoja de calculo ----
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="utilidad_x_area.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	} else {
		echo '
					<tr class="claro">
						<td colspan="6" style="text-align:center; font-size: x-large;">
							<b>' . $ventas . ' Ventas Encontradas<br>(Montos Sin Impuestos)</b>
						</td>
						<td colspan="18"></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>'."\n";
	}

} else {
		echo '<p class="alerta">Acceso no autorizado....</p>';
}
