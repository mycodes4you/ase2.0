<?php
/*************************************************************************************
*   Script de "reporte de Vehiculos Entregados"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/

if ($f1125030 == '1' || $_SESSION['rol02']=='1') {

	if($estatusflt >= '5' && $estatusflt <= '7') {
		$preg0 = "SELECT o.orden_id, o.orden_estatus, o.orden_vehiculo_placas, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_id, o.orden_alerta, o.orden_asesor_id, o.orden_fecha_recepcion, o.orden_fecha_de_entrega, o.orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes o";
	} else {
		$preg0 = "SELECT orden_id, orden_estatus, orden_vehiculo_placas, orden_vehiculo_tipo, orden_vehiculo_color, orden_vehiculo_id, orden_alerta, orden_asesor_id, orden_fecha_recepcion, orden_fecha_de_entrega, orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes WHERE ";
	}

	if($estatusflt == '5') {
		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '1' AND f.fact_tipo < '3' AND f.fact_fecha_cobrada >= '" . $feini . "' AND f.fact_fecha_cobrada <= '" . $fefin . "' AND (o.orden_estatus < '30' OR o.orden_estatus = '99') ";
	} elseif($estatusflt == '6') {

//		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '0' AND f.fact_tipo < '3' AND f.fact_fecha_emision >= '" . $feini . "' AND f.fact_fecha_emision <= '" . $fefin . "' AND (o.orden_estatus < '30' OR o.orden_estatus = '99') ";
			$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '0' AND f.fact_tipo < '3' AND f.fact_fecha_emision >= '" . $feini . "' AND f.fact_fecha_emision <= '" . $fefin . "' ";
			$rangofe = $lang['Fecha de Facturas'];
		} elseif($estatusflt == '7') {
			$preg0 .= " WHERE NOT EXISTS ( SELECT null FROM " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_tipo < '3' ) AND ((o.orden_estatus >= '12' AND o.orden_estatus <= '16') OR o.orden_estatus = '99') AND o.orden_fecha_ultimo_movimiento >= '" . $feini . "' AND o.orden_fecha_ultimo_movimiento <= '" . $fefin . "' ";
			$rangofe = $lang['Fecha de Ultimo Movimiento'];
		} elseif($estatusflt == '4') {
			$preg0 .= " orden_fecha_de_entrega > '" . $feini . "' AND orden_fecha_de_entrega < '" . $fefin . "' AND (orden_estatus < '30' OR orden_estatus = '99') ";
			$nomrep = 'Entregados Autorizados';
			$rangofe = $lang['Fecha de Entrega'];
		} elseif($estatusflt == '3') {
			$preg0 .= " orden_fecha_ultimo_movimiento > '" . $feini . "' AND orden_fecha_ultimo_movimiento < '" . $fefin . "'  AND (orden_estatus >= '12' AND orden_estatus <= '15') ";
			$nomrep = 'Terminados';
			$rangofe = $lang['Fecha de Ultimo Movimiento'];
		} elseif($estatusflt == '2') {
// ------ Se eliminan rangos de fechas para incluir todos los trabajos de este tipo en virtud de que no deben existir demasiados
			$preg0 .= " ((orden_estatus >= '4' AND orden_estatus <= '11') OR orden_estatus = '21') ";
			$nomrep = 'Reparación';
			$rangofe = $lang['Fecha No Aplica'];
		} elseif($estatusflt == '1') {
			$preg0 .= " orden_estatus <= '2' OR orden_estatus = '17' OR orden_estatus = '20' OR (orden_estatus >= '24' AND orden_estatus <= '29') ";
			$nomrep = 'Por Autorizar';
			$rangofe = $lang['Fecha No Aplica'];
		} elseif($estatusflt == '0') {
			$preg0 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'  AND (orden_estatus < '30' OR orden_estatus = '99') ";
			$nomrep = 'Todos los Recibidos';
			$rangofe = $lang['Fecha de Recepción'];
		} else {
			$preg0 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'  AND (orden_estatus < '30' OR orden_estatus = '99') ";
			$nomrep = 'Todos los Recibidos';
			$rangofe = $lang['Fecha de Recepción'];
		}

//	echo $preg0;

	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de lapso! ".$preg0);
	$filas = mysql_num_rows($matr0);
	
	
	if($export == 1){ // ---- Hoja de calculo ----
	
		$encabezado = ' OTs ' . $nomrep . ' del ' . $t_ini . ' al ' . $t_fin . '' . $rangofe;

		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'FINANZAS: ' . $nombre_agencia;
	
		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("REPORTE DE FINANZAS")
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
					->setCellValue("F4", "Siniestro")
					->setCellValue("G4", "Venta de Ref")
					->setCellValue("H4", "Venta de Materiales")
					->setCellValue("I4", "Venta de MO")
					->setCellValue("J4", "Monto de Venta")
					->setCellValue("K4", "Costo Aseguradora")
					->setCellValue("L4", "Fact/Rem")
					->setCellValue("M4", "Emisión")
					->setCellValue("N4", "Cobrada?")
					->setCellValue("O4", "Costo Ref")
					->setCellValue("P4", "Costo Mats")
					->setCellValue("Q4", "Costo TOT")
					->setCellValue("R4", "Costo Destajo")
					->setCellValue("S4", "Costo Total")
					->setCellValue("T4", "% Utilidad");
		$z= 5;
		
	}
	else{ // ---- HTML ----
	
		$encabezado = ' OTs ' . $nomrep . ' del ' . $t_ini . ' al ' . $t_fin . '<br><span style="font-weight:bold; color: red;">' . $rangofe . '</span>';
		echo '			
			<table cellspacing="1" cellpadding="2" border="0">
				<tr class="cabeza_tabla">
					<td colspan="20" style="text-align: right;">';
		
		if($asegflt != ""){
			echo '
						<img src="' . $ase[$asegflt][0] . '"/>';
		}
	
		echo			$encabezado . '
					</td>
				</tr>' . "\n";

		echo '				
				<tr>
					<td>OT</td>
					<td>Vehículo</td>
					<td>' . $lang['Placa'] . '</td>
					<td>Estatus</td>
					<td>Cliente</td>
					<td>Siniestro</td>
					<td class="area6">Venta de<br>Ref</td>
					<td class="area6">Venta de<br>Materiales</td>
					<td class="area6">Venta de<br>MO</td>
					<td class="area6">Monto de<br>Venta</td>
					<td class="areaotra">Costo<br>Aseguradora</td>
					<td class="area6">Fact/Rem</td>
					<td class="area6" style="text-align:center;">Emisión</td>
					<td class="area6">Cobrada?</td>
					<td class="area7">Costo Ref</td>
					<td class="area7" style="text-align:center;">Costo<br>Mats</td>
					<td class="area7" style="text-align:center;">Costo<br>TOT</td>
					<td class="area7" style="text-align:center;">Costo<br>Destajo</td>
					<td class="area7" style="text-align:center;">Costo<br>Total</td>
					<td class="areaotra" style="text-align:center;">% Utilidad</td>
				</tr>'."\n";
	$fondo = 'claro';
		
	}

	$j = 0;
	$totpres = 0; $totpart = 0; $totsin = 0; $numpart = 0; $numsin = 0; $pvppart = 0; $pvpsin = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	$total_x_busqueda = 0;
	
	while($ord = mysql_fetch_array($matr0)) {
		
		$preg2 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
		if($asegflt != '') {
			$preg2 .= " AND sub_aseguradora = '" . $asegflt . "' ";
		}
		$preg2 .= " GROUP BY sub_reporte";
//		echo '<br>' . $preg2;
		$matr2 = mysql_query($preg2);
		while($gsub = mysql_fetch_array($matr2)) {
			
//			if($estatusflt == '5') {
			$total_x_busqueda = $total_x_busqueda + 1;
			
			if($export == 1){ // ---- Hoja de calculo ----
				
				// --- Celdas a grabar ----
				$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
				$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $jota = 'J'.$z;
				$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z; $n = 'N'.$z; $o = 'O'.$z;
				$p = 'P'.$z; $q = 'Q'.$z; $r = 'R'.$z; $s = 'S'.$z; $t = 'T'.$z;
				
				$datos_vehi = strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']);
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($a, $ord['orden_id'])
							->setCellValue($b, $datos_vehi)
							->setCellValue($c, $ord['orden_vehiculo_placas'])
							->setCellValue($d, constant('ORDEN_ESTATUS_' . $ord['orden_estatus']))
							->setCellValue($e, $ase[$gsub['sub_aseguradora']][1]);
				
			}
			else{ // ---- HTML ----
			
				echo '				
				<tr class="' . $fondo . '">
					<td style="padding-left:10px; padding-right:10px; text-align:center;">
						<a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '">' . $ord['orden_id'] . '</a>
					</td>
					<td style="padding-left:10px; padding-right:10px;">
						' . strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']) . '
					</td>
					<td style="padding-left:10px; padding-right:10px;">
						' . $ord['orden_vehiculo_placas'] . '
					</td>
					<td style="padding-left:10px; padding-right:10px;">
						' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '
					</td>
					<td>
						' . $ase[$gsub['sub_aseguradora']][1] . '
					</td>
					<td>';
			}
			
			if($gsub['sub_reporte'] == '0' || $gsub['sub_reporte'] == '') {
				
				if($export == 1){ // ---- Hoja de calculo ----

					$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($f, 'Particular');
					
				}
				else{ // ---- HTML ----
					echo 'Particular';
				}
				
				$numpart++;
				$gsub['sub_reporte'] = '0';
				
			} else {
				
				if($export == 1){ // ---- Hoja de calculo ----
					
					$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($f, $gsub['sub_reporte']);
					
				}
				else{ // ---- HTML ----
					echo $gsub['sub_reporte'];
				}
				$numsin++;
				
			}
			
			if($export == 1){ // ---- Hoja de calculo ----

			}
			else{ // ---- HTML ----
				echo '
					</td>';
			}
			
			$preg3 = "SELECT sub_orden_id, sub_deducible, sub_presupuesto, sub_partes, sub_consumibles, sub_mo FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $gsub['sub_reporte'] . "' AND orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
//			echo '<br>' . $preg3;
			$matr3 = mysql_query($preg3) or die("Falló selección de subordenes");
			$pres = 0; $partes = 0; $cons = 0; $mo = 0;
			$nppe = 0; $cppe = 0; $cpm = 0; $npm = 0;
			$dedu = 0; $fila5 = 0; $opped = 0; $occ = 1;
			$costcons = 0; $costtot = 0; $costref = 0; $costdest = 0;
			while($sub = mysql_fetch_array($matr3)) {
				$pres = $pres + $sub['sub_presupuesto'];
				$partes = $partes  + $sub['sub_partes'];
				$cons = $cons  + $sub['sub_consumibles'];
				$mo = $mo  + $sub['sub_mo'];
				if($sub['sub_deducible'] > $dedu) { $dedu = $sub['sub_deducible']; }
				$preg5 = "SELECT op_id, op_cantidad, op_costo, op_precio, op_pedido, op_autosurtido, op_pres, op_item_seg, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '".$sub['sub_orden_id']."' ";
// ------ Filtrado de refacciones por código de producto ---------------
				if($fltprodcod == '1') { $preg5 .= " AND (op_codigo IS NULL OR op_codigo NOT LIKE '%PAG%')"; }
// ------
				$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden_productos 3!");
//				echo '<br>' . $preg5;
				$fila6 = mysql_num_rows($matr5);
				$fila5 = $fila5 + $fila6;
				if(mysql_num_rows($matr5) > 0) {
					while($op = mysql_fetch_array($matr5)) {
						if($op['op_tangible'] == '1') {
							$cppe = $cppe + ($op['op_cantidad'] * $op['op_costo']);
							if($op['op_pedido'] < 1 && is_null($op['op_item_seg'])) {
								$opped = 1;
							}
							if($op['op_autosurtido'] == '1' && $op['op_pedido'] > 0) {
								$nppe++;
								if($op['op_costo'] > 1) {
									$npm = $npm + $op['op_cantidad'];
									$cpm = $cpm + ($op['op_cantidad'] * $op['op_costo']);
								} elseif($op['op_autosurtido'] == '1') {
									$occ = 0;
								}
							}
							if($op['op_autosurtido'] != '1' && $op['op_pedido'] > '0') {
								$costref = $costref + ($op['op_cantidad'] * $op['op_costo']);
							}
						} elseif($op['op_tangible'] == '2') {
							$costcons = $costcons + ($op['op_cantidad'] * $op['op_costo']);
						} elseif($op['op_tangible'] == '0') {
							$costtot = $costtot + ($op['op_cantidad'] * $op['op_costo']);
						}
					}
				}
			}

// Determinar costos de MO por Destajos
			$preg6 = "SELECT monto FROM " . $dbpfx . "destajos_elementos WHERE orden_id = '" . $ord['orden_id'] . "' AND reporte = '" . $gsub['sub_reporte'] . "'";
			$matr6 = mysql_query($preg6) or die("Falló selección de destajos. ".$preg6);
			while($dest = mysql_fetch_array($matr6)) {
				$costdest = $costdest + $dest['monto'];
			}

			$cppp = ($cpm / $npm);
			$nvomo = $cons + $mo;
			$asecc = 'black';
			if ($occ > 0 && $cpm > 0) {
				$ocp++; $tcpm = $tcpm + $cpm; $asecc = 'black';
			} elseif($nppe == 0 && $cppe > 0 ) {
				$asecc = 'black';
			} else {
				$asecc = 'red';
			}

			if( $opped == 1 ) {
				$asecc = 'red';
			}
//			$mirada = '<!-- occ-' . $occ . ' cpm-' . $cpm . ' nppe-' . $nppe . ' cppe-' . $cppe . ' opped-' . $opped . ' -->';
			$occ = 1;

			if($export == 1){ // ---- Hoja de calculo ----
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($g, $partes)
							->setCellValue($h, $cons)
							->setCellValue($i, $mo)
							->setCellValue($jota, $pres);
				
			}
			else{ // ---- HTML ----
				echo '
					<td class="area6" style="text-align:right;">
						$' .  number_format($partes, 2) . '</td>
					<td class="area6" style="text-align:right;">
						$' .  number_format($cons, 2) . '
					</td>
					<td class="area6" style="text-align:right;">
						$' .  number_format($mo, 2) . '
					</td>
					<td class="area6" style="text-align:right;">
						$' .  number_format($pres, 2) . '
					</td>
					<td class="area6" style="text-align:right; color:' . $asecc . ';">';
			}
			
			if($opped == 1 || $nppe > 0 || ($gsub['sub_reporte'] != '0' && $partes > '0' && ($fila5 > 0 && $cppe == 0 ))) {
				
				if($export == 1){ // ---- Hoja de calculo ----
					
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($kkk, $cpm);
					
				}
				else{ // ---- HTML ----
					echo '$' .  number_format($cpm, 2) . $mirada;
				}
				
			}
			
			if($export == 1){ // ---- Hoja de calculo ----
			}
			else{ // ---- HTML ----
				echo '
					</td>';
			}
			
			$totpres = $totpres + $pres;
			$totpartes = $totpartes + $partes;
			$totcons = $totcons + $cons;
			$totmo = $totmo + $mo;
			$totcostref = $totcostref + $costref;
			$totcostcon = $totcostcon + $costcons;
			$totcosttot = $totcosttot + $costtot;
			$totcostdes = $totcostdes + $costdest;
			if($gsub['sub_reporte'] == '0' || $gsub['sub_reporte'] == '') {
				$totpartespart = $totpartespart + $partes;
				$totconspart = $totconspart + $cons;
				$totmopart = $totmopart + $mo;
				$totpart = $totpart + $pres;
				$totcostconpart = $totcostconpart + $costcons;
				$totcosttotpart = $totcosttotpart + $costtot;
				$totcostdespart = $totcostdespart + $costdest;
				$totcostrefpart = $totcostrefpart + $costref;
				if($pres > 0) { $pvppart++; }
			} else {
				$totpartessin = $totpartessin + $partes;
				$totconssin = $totconssin + $cons;
				$totmosin = $totmosin + $mo;
				$totsin = $totsin + $pres;
				$totcostconsin = $totcostconsin + $costcons;
				$totcosttotsin = $totcosttotsin + $costtot;
				$totcostdessin = $totcostdessin + $costdest;
				$totcostrefsin = $totcostrefsin + $costref;
				if($pres > 0) { $pvpsin++; }
			}
			$preg1 = "SELECT fact_num, fact_fecha_emision, fact_tipo, fact_fecha_cobrada, fact_cobrada FROM " . $dbpfx . "facturas_por_cobrar WHERE orden_id = '" . $ord['orden_id'] . "' AND fact_tipo < '4' AND reporte = '" . $gsub['sub_reporte'] . "' AND fact_cobrada < 2 ";
//			if($ofc == 1) { $preg1 .= "AND fact_cobrada = '1' "; }
//			elseif($ofsc == 1) { $preg1 .= "AND fact_cobrada = '0' "; }
//			elseif($osf == 1) { $preg1 .= "AND fact_cobrada = '1' "; }
//			echo $preg1;
			$matr1 = mysql_query($preg1) or die("Falló selección de subordenes");
//		echo '<table cellpadding="2" cellspacing="0" border="1" width="100%">';
			$cobrada = 0; $fact_cob = 0; $dedu_cob = 0; $dedu_no = 0; $fact_num = ''; $fact_fech = ''; $dedu_num = ''; $fech_cob = ''; $fech_deducob = '';
			while($fact = mysql_fetch_array($matr1)) {
				if($fact['fact_tipo'] < '3') {
					$fact_num = $fact['fact_num'] . ' ';
					if($fact['fact_fecha_emision'] != '0000-00-00 00:00:00') {
						$fact_fech = date('Y-m-d', strtotime($fact['fact_fecha_emision'])) . ' ';
					}
					if(!is_null($fact['fact_fecha_cobrada']) && $fact['fact_fecha_cobrada'] != '0000-00-00 00:00:00') {
						
						$fech_cob = date('Y-m-d', strtotime($fact['fact_fecha_cobrada'])) . ' ';
						
					}
					if($fact['fact_cobrada'] == '1') {
						$fact_cob = 1;
					} else {
						$cobrada = 2;
					}
				} else {
					$dedu_num = $fact['fact_num'] . ' ';
//					$dedu_fech = $fact['fact_fecha_emision'] . ' ';
					if($fact['fact_fecha_cobrada'] != '0000-00-00 00:00:00') {
						$fech_deducob = date('Y-m-d', strtotime($fact['fact_fecha_cobrada'])) . ' ';
					}
					if($fact['fact_cobrada'] == '1') {
						$dedu_cob = 1;
					} else {
						$dedu_no = 2;
					}
				}
			}
			if($fact_cob == '1' && $cobrada == '0') {
				$cobrada = $fech_cob;
			} else {
				$cobrada = 'No';
			}
			if($dedu_cob == '1' && $dedu_no == '0') {
				$dedu_cob = 1;
			} else {
				$dedu_cob = 0;
			}

			$totcosts = $costref+$costcons+$costtot+$costdest;

			if($pres == 0 && $totcosts > 0) {
				$util = -100;
			} else {
				$util = round(((($pres - $totcosts) / $pres) * 100),2);
			}
			if($margutil == '') { $margutil = 0;}

			if($export == 1){ // ---- Hoja de calculo ----
				
				if($fact_fech == ''){
					
				} else{
					$fact_fech = PHPExcel_Shared_Date::PHPToExcel( strtotime($fact_fech) );
					
					// --- cambiar el formato de la celda tipo fecha/date ---
					$objPHPExcel->getActiveSheet()
    						->getStyle($m)
    						->getNumberFormat()
    						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}
				
				
				if($cobrada == 'No'){
					
				} else{
					$cobrada = PHPExcel_Shared_Date::PHPToExcel( strtotime($cobrada) );
					
					// --- cambiar el formato de la celda tipo fecha/date ---
					$objPHPExcel->getActiveSheet()
    						->getStyle($n)
    						->getNumberFormat()
    						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				}
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($l, $fact_num)
							->setCellValue($m, $fact_fech)
							->setCellValue($n, $cobrada)
							->setCellValue($o, $costref)
							->setCellValue($p, $costcons)
							->setCellValue($q, $costtot)
							->setCellValue($r, $costdest)
							->setCellValue($s, $totcosts);
				
			}
			else{ // ---- HTML ----
				echo '
					<td class="area6">
						<a href="entrega.php?accion=cobros&orden_id=' . $ord['orden_id'] . '" target="_blank">' . $fact_num . '</a>
					</td>
					<td class="area6">
						' . $fact_fech . '
					</td>
					<td class="area6">
						' . $cobrada . '
					</td>
					<td class="area7" style="text-align:right;">
						$' . number_format($costref, 2) . '
					</td>
					<td class="area7">
						$' . number_format($costcons, 2) . '
					</td>
					<td class="area7">
						$' . number_format($costtot, 2) . '
					</td>
					<td class="area7">
						$' . number_format($costdest, 2) . '
					</td>
					<td class="area7">
						' . number_format($totcosts, 2) . '
					</td>
					<td ';
			
			}
			
			if($util < $margutil ) {
// ------ Si el margen de utilidad es menor a $margutil cambia el color del fondo de la celda
				if($export == 1){ // ---- Hoja de calculo ----
				}
				else{ // ---- HTML ----
					echo ' style="background-color: #ff6666; text-align: center;"';
				}
					
			} else {
				
				if($export == 1){ // ---- Hoja de calculo ----
				}
				else{ // ---- HTML ----
					echo ' class="areaotra"';
				}
			}
			
			if($export == 1){ // ---- Hoja de calculo ----
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($t, $util);
				$z++;
			}
			else{ // ---- HTML ----
				echo '">' . $util . '%</td>';
				echo '
				</tr>'."\n";
				$j++;
				if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
			}
		}
	}
	$tnvomo = $totcons + $totmo;
	$prcpartes = number_format((($totpartes / $totpres) * 100), 2);
	$prcnvomo = number_format((($tnvomo /$totpres) * 100), 2);
	$prcpartespart = number_format((($totpartespart / $totpart) * 100), 2);
	$prcnvomopart = number_format(((($totconspart + $totmopart) / $totpart) * 100), 2);
	$prcpartessin = number_format((($totpartessin / $totsin) * 100), 2);
	$prcnvomosin = number_format(((($totconssin + $totmosin) / $totsin) * 100), 2);
	$pvpp = number_format(round(($totpart / $pvppart), 2),2);
	$pvps = number_format(round(($totsin / $pvpsin), 2),2);
	$totutilsin = round(((($totsin - ($totcostrefsin+$totcostconsin+$totcosttotsin+$totcostdessin)) / $totsin) * 100),2);
	$cps = number_format(round((($totcostrefsin+$totcostconsin+$totcosttotsin+$totcostdessin) / $pvpsin), 2),2);
	$totutilpart = round(((($totpart - ($totcostrefpart+$totcostconpart+$totcosttotpart+$totcostdespart)) / $totpart) * 100),2);
	$cptp = number_format(round((($totcostrefpart+$totcostconpart+$totcosttotpart+$totcostdespart) / $pvppart), 2),2);
	$totutil = round(((($totpres - ($totcostref+$totcostcon+$totcosttot+$totcostdes)) / $totpres) * 100),2);

	if($export == 1){ // ---- Hoja de calculo ----
		
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="finanzas.xls"');
		header('Cache-Control: max-age=0');


		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
		
	}
	else{ // ---- HTML ----
	echo '				
				<tr class="claro">
					<td colspan="6" style="text-align:center; font-size: x-large;">
						' . $total_x_busqueda . ' Ventas Encontradas<br>(Montos Sin Impuestos)
					</td>
					<td style="vertical-align:bottom;">
						Venta Total<br>Refacciones
					</td>
					<td style="vertical-align:bottom;">
						Venta Total<br>Materiales</td>
					<td style="vertical-align:bottom;">
						Venta Total<br>MO
					</td>
					<td style="vertical-align:bottom;">
						Monto Total<br>de Venta
					</td>
					<td>
						Venta Promedio
					</td>
					<td colspan="2">
					</td>
					<td style="text-align:right;">
						Costo Promedio
					</td>
					<td style="vertical-align:bottom;">
						Costo<br>Total Ref
					</td>
					<td style="vertical-align:bottom;">
						Costo<br>Total<br>Mats
					</td>
					<td style="vertical-align:bottom;">
						Costo<br>Total<br>TOT
					</td>
					<td style="vertical-align:bottom;">
						Costo<br>Total<br>Destajo
					</td>
					<td style="vertical-align:bottom;">
						Costo<br>Total
					</td>
					<td style="vertical-align:bottom;">
						Utilidad<br>Total
					</td>
					</tr>
					<tr class="obscuro">
					<td colspan="6" style="text-align:right;">
						Total Aseguradoras ('. $pvpsin .')
					</td>
					<td style="text-align:right;">
					' . number_format($totpartessin,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totconssin,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totmosin,2) . '
					</td><td style="text-align:right;">
						' . number_format($totsin,2) . '
					</td>
					<td style="text-align:right;">
						' . $pvps . '
					</td>
					<td colspan="2"></td><td style="text-align:right;">
						' . $cps . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcostrefsin,2) . '
					</td><td style="text-align:right;">
						' . number_format($totcostconsin,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcosttotsin,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcostdessin,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format(($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin),2) . '
					</td>
					<td style="text-align:right;">
						' . $totutilsin . '%
					</td>
				</tr>
				<tr class="obscuro">
					<td colspan="6" style="text-align:right;">
						Indices Aseguradoras
					</td>
					<td style="text-align:right;">
						' . round((($totpartessin / $totsin) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . round((($totconssin / $totsin) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . round((($totmosin / $totsin) * 100), 2) . '%
					</td>
					<td colspan="5">
					</td>
					<td style="text-align:right;">
						' . round((($totcostrefsin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . round((($totcostconsin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . round((($totcosttotsin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . round((($totcostdessin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%
					</td>
					<td colspan="2">
					</td>
				</tr>
				<tr class="claro">
					<td colspan="6" style="text-align:right;">
						Total Particulares ('. $pvppart .')
					</td>
					<td style="text-align:right;">
						' . number_format($totpartespart,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totconspart,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totmopart,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totpart,2) . '
					</td>
					<td style="text-align:right;">
						' . $pvpp . '
					</td>
					<td colspan="2">
					</td>
					<td style="text-align:right;">
						' . $cptp . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcostrefpart,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcostconpart,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcosttotpart,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcostdespart,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format(($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart),2) . '
					</td>
					<td style="text-align:right;">
						' . $totutilpart . '%
					</td>
				</tr>
				<tr class="claro">
					<td colspan="6" style="text-align:right;">
						Indices Particulares
					</td>
					<td style="text-align:right;">
					' . round((($totpartespart / $totpart) * 100),2) . '%
					</td>
					<td style="text-align:right;">
						' . round((($totconspart / $totpart) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . round((($totmopart / $totpart) * 100), 2) . '%
					</td>
					<td colspan="5">
					</td>
					<td style="text-align:right;">
						' . round((($totcostrefpart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . round((($totcostconpart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . round((($totcosttotpart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . round((($totcostdespart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%
					</td>
					<td colspan="2">
					</td>
					</tr>
					<tr class="obscuro">
					<td colspan="6" style="text-align:right;">Total</td><td style="text-align:right;">
						' . number_format($totpartes, 2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcons, 2) . '
					</td>
					<td style="text-align:right;">' . number_format($totmo, 2) . '
					</td>
					<td style="text-align:right;">' . number_format($totpres, 2) . '
					</td>
					<td colspan="4">
					</td>
					<td style="text-align:right;">
						' . number_format($totcostref,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcostcon,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcosttot,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format($totcostdes,2) . '
					</td>
					<td style="text-align:right;">
						' . number_format(($totcostref + $totcostcon + $totcosttot + $totcostdes),2) . '
					</td>
					<td style="text-align:right;">
						' . $totutil . '%
					</td>
				</tr>
				<tr class="obscuro">
					<td colspan="6" style="text-align:right;">
						Utilidad Total
					</td>
					<td style="text-align:right;">
						' . number_format(((($totpartes - $totcostref) / $totpartes) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . number_format(((($totcons - $totcostcon) / $totcons) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . number_format(((($totmo - ($totcosttot + $totcostdes)) / $totmo) * 100), 2) . '%
					</td>
					<td style="text-align:right;">
						' . number_format(((($totpres - ($totcostref + $totcostcon + $totcosttot + $totcostdes)) / $totpres) * 100), 2) . '%
					</td>
					<td colspan="10">
					</td>
				</tr>
				<tr class="claro">
					<td colspan="6" style="text-align:right;">
						Costo de Refacciones surtidas<br>por Aseguradoras
					</td>
					<td style="text-align:right;">
						' . number_format($tcpm,2) . '
					</td>
					<td colspan="2">
						Costo Promedio de<br>Refacciones surtidas<br>por Aseguradoras (' . $ocp . ')
					</td>
					<td style="text-align:right;">
						' . number_format(($tcpm / $ocp),2) . '
					</td>
					<td colspan="7" style="color:red;">
						Las cantidades en ROJO son costos incompletos<br>de refacciones surtidas por Aseguradoras
					</td>
				</tr>
			</table>';
	}
	} else {
		 
		echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
