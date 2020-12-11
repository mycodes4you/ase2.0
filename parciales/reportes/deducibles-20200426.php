<?php

if ($f1125030 == '1' || $_SESSION['rol02']=='1') {

	if($estatusflt >= '5' && $estatusflt <= '7') {
		$preg0 = "SELECT o.orden_id, o.orden_estatus, o.orden_vehiculo_placas, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_id, o.orden_alerta, o.orden_asesor_id, o.orden_fecha_recepcion, o.orden_fecha_de_entrega, o.orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes o";
	} else {
		$preg0 = "SELECT orden_id, orden_estatus, orden_vehiculo_placas, orden_vehiculo_tipo, orden_vehiculo_color, orden_vehiculo_id, orden_alerta, orden_asesor_id, orden_fecha_recepcion, orden_fecha_de_entrega, orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes WHERE ";
	}

	if($estatusflt == '5') {
		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '1' AND f.fact_tipo < '3' AND f.fact_fecha_cobrada >= '" . $feini . "' AND f.fact_fecha_cobrada <= '" . $fefin . "' ";
	} elseif($estatusflt == '6') {
		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '0' AND f.fact_tipo < '3' AND f.fact_fecha_emision >= '" . $feini . "' AND f.fact_fecha_emision <= '" . $fefin . "' ";
	} elseif($estatusflt == '7') {
		$preg0 .= " WHERE NOT EXISTS ( SELECT null FROM " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_tipo < '3' ) AND ((o.orden_estatus >= '12' AND o.orden_estatus <= '16') OR o.orden_estatus = '99') AND o.orden_fecha_ultimo_movimiento >= '" . $feini . "' AND o.orden_fecha_ultimo_movimiento <= '" . $fefin . "' ";
	} elseif($estatusflt == '4') {
		$preg0 .= " orden_fecha_de_entrega > '" . $feini . "' AND orden_fecha_de_entrega < '" . $fefin . "' ";
		$nomrep = 'Entregados Autorizados';
	} elseif($estatusflt == '3') {
		$preg0 .= " orden_fecha_ultimo_movimiento > '" . $feini . "' AND orden_fecha_ultimo_movimiento < '" . $fefin . "'  AND (orden_estatus >= '12' AND orden_estatus <= '15') ";
		$nomrep = 'Terminados';
	} elseif($estatusflt == '2') {
		$preg0 .= " orden_fecha_ultimo_movimiento > '" . $feini . "' AND orden_fecha_ultimo_movimiento < '" . $fefin . "'  AND ((orden_estatus >= '4' AND orden_estatus <= '11') OR orden_estatus = '21') ";
		$nomrep = 'Reparación';
	} elseif($estatusflt == '1') {
		$preg0 .= " orden_fecha_ultimo_movimiento > '" . $feini . "' AND orden_fecha_ultimo_movimiento < '" . $fefin . "'  AND (orden_estatus = '2' OR orden_estatus = '20' OR orden_estatus = '28' OR orden_estatus = '29') ";
		$nomrep = 'Por Autorizar';
	} elseif($estatusflt == '0') {
		$preg0 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'  AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Todos los Recibidos';
	} else {
		$preg0 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'  AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Todos los Recibidos';
	}

//	echo $preg0;

	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de lapso!".$preg0);
	$filas = mysql_num_rows($matr0);
	$encabezado = ' OTs ' . $nomrep . ' del ' . $t_ini . ' al ' . $t_fin;

	if($export == 1) { // ---- Hoja de calculo ----
		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'DEDUCIBLES: ' . $nombre_agencia;

		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("Listado de Clientes")
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
					->setCellValue("E4", $lang['Siniestro'])
					->setCellValue("F4", "Deducible")
					->setCellValue("H4", "Cobrado?")
					->setCellValue("I4", "F Cobrado");
	
		$z= 5;

	} else { // ---- HTML ----
		echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-10">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2>' . $filas . $encabezado . '</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-10">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
					<tr>
						<th><big>OT</big></th>
						<th><big>Vehículo</big></th>
						<th><big>' . $lang['Placa'] . '</big></th>
						<th><big>Estatus</big></th>
						<th><big>' . $lang['Siniestro'] . '</big></th>
						<th><big>Deducible</big></th>
						<th><big>Cobrado?</big></th>
						<th><big>F Cobrado</big></th>
					</tr>'."\n";
		$fondo = 'claro';
	}

	$j = 0;
	$totpres = 0; $totpart = 0; $totsin = 0; $numpart = 0; $numsin = 0; $pvppart = 0; $pvpsin = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));

	while($ord = mysql_fetch_array($matr0)) {
		$preg2 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_aseguradora > '0' AND sub_aseguradora != '" . $ConvenioGarantia . "' AND sub_paga_deducible < '2' AND sub_estatus < '190' GROUP BY sub_reporte";
		$matr2 = mysql_query($preg2);
		while($gsub = mysql_fetch_array($matr2)) {
			if($export == 1) { // ---- Hoja de calculo ----
				// --- Celdas a grabar ----
				$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
				$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z;
				$datos_vehi = strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']);
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($a, $ord['orden_id'])
							->setCellValue($b, $datos_vehi)
							->setCellValue($c, $ord['orden_vehiculo_placas'])
							->setCellValue($d, constant('ORDEN_ESTATUS_' . $ord['orden_estatus']));
			} else { // ---- HTML ----
				echo '
					<tr class="' . $fondo . '">
						<td style="padding-left:10px; padding-right:10px; text-align:center;">
							<big><a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" style=" display:block;">' . $ord['orden_id'] . '</a></big>
						</td>
						<td style="text-align: left !important;">
							<big>' . strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']) . '</big>
						</td>
						<td style="padding-left:10px; padding-right:10px;">
							<big>' . $ord['orden_vehiculo_placas'] . '</big>
						</td>
						<td style="text-align: left !important;">
							<big>' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '</big>
						</td>
						<td><big>';
			}
			if($export == 1){ // ---- Hoja de calculo ----
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($e, $gsub['sub_reporte']);
					$numsin++;
			} else{ // ---- HTML ----
					echo $gsub['sub_reporte'];
					$numsin++;
			}
			if($export != 1) {  // ---- HTML ----
				echo '
						</big></td>';
			}

			$preg3 = "SELECT sub_orden_id, sub_deducible, sub_presupuesto, sub_partes, sub_consumibles, sub_mo FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $gsub['sub_reporte'] . "' AND orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
//			echo $preg1;
			$matr3 = mysql_query($preg3) or die("Falló selección de subordenes");
			$dedu = 0;
			while($sub = mysql_fetch_array($matr3)) {
				if($sub['sub_deducible'] > $dedu) { $dedu = $sub['sub_deducible']; }
			}
			$preg1 = "SELECT fact_num, fact_fecha_emision, fact_tipo, fact_fecha_cobrada, fact_cobrada FROM " . $dbpfx . "facturas_por_cobrar WHERE orden_id = '" . $ord['orden_id'] . "' AND fact_tipo = '3' AND reporte = '" . $gsub['sub_reporte'] . "' AND fact_cobrada < 2 ";
			$matr1 = mysql_query($preg1) or die("Falló selección de subordenes");
			$cobrada = ''; $fact_cob = 0; $dedu_cob = 0; $dedu_no = 0; $fact_num = ''; $fact_fech = ''; $dedu_num = ''; $fech_cob = ''; $fech_deducob = '';
			while($fact = mysql_fetch_array($matr1)) {
				$fact_num = $fact['fact_num'] . ' ';
				if(!is_null($fact['fact_fecha_cobrada']) && $fact['fact_fecha_cobrada'] != '0000-00-00 00:00:00') {
					$fech_cob = date('Y-m-d', strtotime($fact['fact_fecha_cobrada'])) . ' ';
				}
				if($fact['fact_cobrada'] == '1') {
					$cobrada = 'Sí';
				} else {
					$cobrada = 'No';
				}
			}
			if($export == 1) { // ---- Hoja de calculo ----
				if($fech_cob != ''){
					$fech_cob = PHPExcel_Shared_Date::PHPToExcel( strtotime($fech_cob) );
					// --- cambiar el formato de la celda tipo fecha/date ---
					$objPHPExcel->getActiveSheet()
								->getStyle($h)
								->getNumberFormat()
								->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($f, $dedu)
							->setCellValue($g, $cobrada)
							->setCellValue($h, $fech_cob);
				$z++;
			} else { // ---- HTML ----
				if($cobrada == 'Sí') {
					$cobrada = '<span class="success">Sí</span>';
				} else {
					$cobrada = '<span class="danger">No</span>';
				}
				echo '
						<td class="area7" style="text-align:right;">
							<big><a href="entrega.php?accion=cobros&orden_id=' . $ord['orden_id'] . '" target="_blank">' . number_format($dedu, 2) . '</a></big>
						</td>
						<td><big>' . $cobrada . '</big></td>
						<td class="area7"><big>' . $fech_cob . '</big></td>
					</tr>'."\n";
				$j++;
				if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
			}
		}
	}

	if($export == 1) { // ---- Hoja de calculo ----
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="ingreso-vehiculos.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	} else { // ---- HTML ----
		echo '
				</table>
			</div>
		</div>
	</div>
</div>';

	}

} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
}