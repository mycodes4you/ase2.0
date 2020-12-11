<?php
/*************************************************************************************
*   Script de "OTS por aseguradora"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/

	if ($f1125070 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
	
		if($export == 1){ // ---- Hoja de calculo ----
		
			// -------------------   Creación de Archivo Excel   ---------------------------
			$celda = 'A1';
			$titulo = 'OTS POR ASEGURADORA: ' . $nombre_agencia;
	
			require_once ('Classes/PHPExcel.php');
			$objReader = PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load("parciales/export.xls");
			$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
						->setTitle("OTS POR ASEGURADORA")
						->setKeywords("AUTOSHOP EASY");

			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($celda, $titulo);

			// ------ ENCABEZADOS ---
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue("A4", $lang['OT'])
						->setCellValue("B4", $lang['Vehículo'])
						->setCellValue("C4", $lang['Placa'])
						->setCellValue("D4", $lang['Asesor'])
						->setCellValue("E4", $lang['Siniestro'])
						->setCellValue("F4", $lang['Estatus'])
						->setCellValue("G4", $lang['Ubicación'])
						->setCellValue("H4", $lang['Partes'])
						->setCellValue("I4", $lang['Consumibles'])
						->setCellValue("J4", $lang['MO'])
						->setCellValue("K4", $lang['FechaR'])
						->setCellValue("L4", $lang['FechaE'])
						->setCellValue("M4", $lang['FechaUM'])
						->setCellValue("N4", $lang['DiasP'])
						->setCellValue("O4", $lang['Cliente']);
			$z = 5;

	}
		else{ // ---- HTML ----
		
		echo '			
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-sm-12 panel-title">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2><small>' . $encabezado . '</small></h2>
				</div>
			</div>
		</div>
	</div>'."\n";
	
	}
	
		// --- Si el filtro de cliente en el reporte de aseguradora viene colocado ---
		if($a != ''){ $ase = $ase_filtro;}
		
		$hoy_1 = strtotime(date('Y-m-d 23:59:59'));
		
		if($tipo_reparacion == ''){
			//echo 'No se evalua, se muestran todos los resultados';
			$evalua = 'No';
		} elseif($tipo_reparacion == 'reparados'){
			//echo 'Se evalua, se muestran solo los reparados';
			$evalua = 'reparados';
		} elseif($tipo_reparacion == 'no_reparados'){
			//echo 'Se evalua, se muestran solo los no reparados';
			$evalua = 'no_reparados';
		}
		
		//echo 'feini ' . $feini . '<br>';
		//echo 'fefin ' . $fefin . '<br>';
		//echo '$tipo_reparacion ' . $tipo_reparacion . '<br>'; 
		//echo '$estatusflt ' . $estatusflt . '<br>'; 
		
		foreach($ase as $k => $v) {

			$fondo = 'claro';
			$j = 0;
			$pregpo = "SELECT s.orden_id, o.orden_fecha_de_entrega FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o  WHERE s.sub_aseguradora = '" . $k . "' AND o.orden_id = s.orden_id AND ";
			
			
			if($tipo_reparacion == 'reparados'){
				$pregpo .= " s.sub_estatus < '190' AND o.orden_fecha_de_entrega IS NOT NULL AND";
			} elseif($tipo_reparacion == 'no_reparados'){
				$pregpo .= " ((o.orden_estatus >= '30' AND o.orden_estatus <= '98') OR o.orden_estatus = '210')  AND ";
			}
			
			
			if($tipo_reparacion == ''){

				if($estatusflt == 1) {
					$pregpo .= " o.orden_fecha_de_entrega >= '" . $feini . "' AND o.orden_fecha_de_entrega <= '" . $fefin . "' GROUP BY s.orden_id ORDER BY 	o.orden_fecha_de_entrega";
				} else {
					$pregpo .= " o.orden_fecha_recepcion >= '" . $feini . "' AND o.orden_fecha_recepcion <= '" . $fefin . "' GROUP BY s.orden_id ORDER BY s.orden_id";
				}
				
			}
			else{
				$pregpo .= " o.orden_fecha_de_entrega >= '" . $feini . "' AND o.orden_fecha_de_entrega <= '" . $fefin . "' GROUP BY s.orden_id ORDER BY 	o.orden_fecha_de_entrega";
			}
			
			//$pregpo .= " GROUP BY s.orden_id ORDER BY s.orden_id";
			$matrpo = mysql_query($pregpo) or die("ERROR: Fallo selección de subórdenes! " . $pregpo);
			$filapo = mysql_num_rows($matrpo);
			//echo $filapo;
			//echo $pregpo . '<br>';
			
			unset($tcp); unset($tcc); unset($tcm); unset($contenido); unset($renglon);
			while($gpo = mysql_fetch_array($matrpo)) {
				$preg3 = "SELECT s.sub_aseguradora, s.sub_orden_id, s.sub_reporte, o.orden_id, o.orden_vehiculo_color, o.orden_vehiculo_tipo, o.orden_vehiculo_placas, o.orden_ubicacion, o.orden_estatus, o.orden_asesor_id, o.orden_fecha_recepcion, o.orden_fecha_promesa_de_entrega, o.orden_servicio, o.orden_fecha_de_entrega, o.orden_fecha_ultimo_movimiento FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o  WHERE s.sub_aseguradora = '" . $k . "' AND o.orden_id = s.orden_id AND s.sub_estatus < '190' AND s.orden_id = '" . $gpo['orden_id'] . "' GROUP BY s.sub_reporte";
				$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de subórdenes!");
				//echo $v[1] . ' -> ' . $preg3 . '<br>';
				while ($ord = mysql_fetch_array($matr3)) {
					
					// --- Se evalua si la variable lo indica ---
					$pinta = 'no';
					if($evalua == 'reparados'){
						//echo 'reparados ase ' . $k . '<br>';
						if($ord['orden_fecha_de_entrega'] != '' && ($ord['orden_estatus'] == 99 || $ord['orden_estatus'] <= 29)){
							$pinta = 'si';
						}
						
					} elseif($evalua == 'no_reparados'){
						
						if($ord['orden_estatus'] == 210 || ($ord['orden_estatus'] >= 30 || $ord['orden_estatus'] <= 98)){
							$pinta = 'si';
						}
					
					} else{
						
						$pinta = 'si';
						
					}
					
					if($pinta == 'si'){
					
					//echo 'orden ' . $ord['orden_id'] . '<br>';
					if(count($contenido[$ord['orden_servicio']]) == 0) {
						
						if($export == 1){ // ---- Hoja de calculo ----
						}
						else{ // ---- HTML ----
						
							$contenido[$ord['orden_servicio']] .= '				
							<tr>
								<th colspan="13" style="text-align: left !important;">
									' . $lang['Trabajos para'] . ' ';
							if($ord['sub_aseguradora'] > 0) {
								$contenido[$ord['orden_servicio']] .= $v[1] . '&nbsp;<img src="' . $v[0] . '" alt="" />';
							} elseif($ord['orden_servicio'] == '4' ) {
								$contenido[$ord['orden_servicio']] .= $lang['Ventas Adicionales'];
							} elseif($ord['orden_servicio'] == '2' ) {
								$contenido[$ord['orden_servicio']] .= $lang['Garantía'];
							} else {
								$contenido[$ord['orden_servicio']] .= $v[1] . ' ' . constant('ORDEN_SERVICIO_' . $ord['orden_servicio']) . '&nbsp;<img src="' . $v[0] . '" alt="" />';
							}
							
							$contenido[$ord['orden_servicio']] .= '
								</th>
							</tr>' . "\n";
							$contenido[$ord['orden_servicio']] .= '				
							<tr>
								<th>' . $lang['OT'] . '</th>
								<th>' . $lang['Vehículo'] . '</th>
								<th>' . $lang['Placa'] . '</th>
								<th>' . $lang['Asesor'] . '</th>
								<th>' . $lang['Siniestro'] . '</th>
								<th>' . $lang['Estatus'] . '</th>
								<th>' . $lang['Ubicación'] . '</th>
								<th>' . $lang['Partes'] . '</th>
								<th>' . $lang['Consumibles'] . '</th>
								<th>' . $lang['MO'] . '</th>
								<th>' . $lang['FechaR'] . '</th>
								<th>' . $lang['FechaE'] . '</th>
								<th>' . $lang['FechaUM'] . '</th>
								<th>' . $lang['DiasP'] . '</th>
								<th>' . $lang['Cliente'] . '</th>
							</tr>'."\n";
						}
						
					}

					$fentre = strtotime($ord['orden_fecha_de_entrega']);
					if($fentre > strtotime('2012-01-01 00:00:00')) {
						$dias = intval(($fentre - strtotime($ord['orden_fecha_recepcion'])) / 86400);
					} else {
						$dias = intval(($hoy_1 - strtotime($ord['orden_fecha_recepcion'])) / 86400);
					}
					$preg4 = "SELECT sub_partes, sub_consumibles, sub_mo FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_reporte = '" . $ord['sub_reporte'] . "' ";
					$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de costos!");
					//$fcost = mysql_num_rows($matr4);
					$cost_part = 0; $cost_cons = 0; $cost_mo = 0;
					while ($cost = mysql_fetch_array($matr4)) {
						$cost_part = $cost_part + $cost['sub_partes'];
						$cost_cons = $cost_cons + $cost['sub_consumibles'];
						$cost_mo = $cost_mo + $cost['sub_mo'];
					}
					
					if($export == 1){ // ---- Hoja de calculo ----
								
						// --- Celdas a grabar ----
						$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
						$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
						$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z; $n = 'N'.$z; $o = 'O'.$z;
				
						$datos_vehi = strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']);
				
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($a, $ord['orden_id'])
									->setCellValue($b, $datos_vehi)
									->setCellValue($c, $ord['orden_vehiculo_placas'])
									->setCellValue($d, $usuario[$ord['orden_asesor_id']]);
						
						$z++;
					}
					else{ // ---- HTML ----
					
					
						$renglon[$ord['orden_servicio']][$j] .= '				
							<tr class="' . $fondo . '">
								<td style="padding-left:2px; padding-right:2px; text-align:center;">
									<a href="';
						if($ord['orden_estatus'] > 3) { $renglon[$ord['orden_servicio']][$j] .= 'presupuestos.php'; } else  { $renglon[$ord['orden_servicio']][$j] .= 'proceso.php'; }
						$renglon[$ord['orden_servicio']][$j] .= '?accion=consultar&orden_id=' . $ord['orden_id'] . '#' . $ord['sub_orden_id'] . '">' . $ord['orden_id'] . '</a>	
								</td>
									<td style="text-align:left;">
										' . strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']) . '
									</td>
									<td style="padding-left:2px; padding-right:2px;">
										' . $ord['orden_vehiculo_placas'] . '
									</td>
									<td style="text-align:left;">
										' . $usuario[$ord['orden_asesor_id']] . '
									</td>
									<td>';
					}
					
					if($export == 1){ // ---- Hoja de calculo ----
						
						if($ord['sub_reporte'] != '0') {
							
							$objPHPExcel->setActiveSheetIndex(0)
										->setCellValue($e, $ord['sub_reporte']);
							
						} elseif($ord['orden_servicio'] == '4') {
							
							$objPHPExcel->setActiveSheetIndex(0)
										->setCellValue($e, $lang['Ventas Adicionales']);
							
						} elseif($ord['orden_servicio'] == '2') {
							
							$objPHPExcel->setActiveSheetIndex(0)
										->setCellValue($e, $lang['Garantía']);
							
						} else {
							
							$descrip = $lang['Particular'] . ' ' . constant('ORDEN_SERVICIO_' . $ord['orden_servicio']);
							$objPHPExcel->setActiveSheetIndex(0)
										->setCellValue($e, $descrip);
							
						}
						
						$frec = date('Y-m-d', strtotime($ord['orden_fecha_recepcion']));
						$frec = PHPExcel_Shared_Date::PHPToExcel( strtotime($frec) );
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($f, constant('ORDEN_ESTATUS_' . $ord['orden_estatus']))
									->setCellValue($g, $ord['orden_ubicacion'])
									->setCellValue($h, number_format($cost_part,2))
									->setCellValue($i, number_format($cost_cons,2))
									->setCellValue($j, number_format($cost_mo,2))
									->setCellValue($kkk, $frec);
						
						// --- cambiar el formato de la celda tipo fecha/date ---
						$objPHPExcel->getActiveSheet()
    								->getStyle($kkk)
    								->getNumberFormat()
    								->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
						
					}
					else{ // ---- HTML ----	
					
						if($ord['sub_reporte'] != '0') {
							$renglon[$ord['orden_servicio']][$j] .= $ord['sub_reporte'];
						} elseif($ord['orden_servicio'] == '4') {
							$renglon[$ord['orden_servicio']][$j] .= $lang['Ventas Adicionales'];
						} elseif($ord['orden_servicio'] == '2') {
							$renglon[$ord['orden_servicio']][$j] .= $lang['Garantía'];
						} else {
							$renglon[$ord['orden_servicio']][$j] .= $lang['Particular'] . ' ' . constant('ORDEN_SERVICIO_' . $ord['orden_servicio']);
						}
					
						$renglon[$ord['orden_servicio']][$j] .= '
									</td>
									<td style="text-align:left;">
										' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '
									</td>
									<td style="text-align:left;">
										' . $ord['orden_ubicacion'] . '
									</td>
									<td style="text-align:right;">
										<b>' . number_format($cost_part,2) . '</b>
									</td>
									<td style="text-align:right;">
										<b>' . number_format($cost_cons,2) . '</b>
									</td>
									<td style="text-align:right;">
										<b>' . number_format($cost_mo,2) . '</b>
									</td>
									<td style="padding-left:2px; padding-right:2px;">
										' . date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])) . '
									</td>
									<td style="padding-left:2px; padding-right:2px;">';
					}
					
					if($export == 1){ // ---- Hoja de calculo ----
						
						if(strtotime($ord['orden_fecha_de_entrega']) > strtotime('2012-01-01 00:00:00')) {
							
							$fentre = date('Y-m-d', strtotime($ord['orden_fecha_de_entrega']));
							$fentre = PHPExcel_Shared_Date::PHPToExcel( strtotime($fentre) );
								
							// --- cambiar el formato de la celda tipo fecha/date ---
							$objPHPExcel->getActiveSheet()
    									->getStyle($l)
    									->getNumberFormat()
    									->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
							
						} else {
							
							$fentre = 'Sin Fecha';
							
						}
						
						$fumov = date('Y-m-d', strtotime($ord['orden_fecha_ultimo_movimiento']));
						$fumov = PHPExcel_Shared_Date::PHPToExcel( strtotime($fumov) );
						
						// --- cambiar el formato de la celda tipo fecha/date ---
						$objPHPExcel->getActiveSheet()
    								->getStyle($m)
    								->getNumberFormat()
    								->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($l, $fentre)
									->setCellValue($m, $fumov)
									->setCellValue($n, $dias)
									->setCellValue($o, $v[1]);
						
						
					}
					else{ // ---- HTML ----
					
						if(strtotime($ord['orden_fecha_de_entrega']) > strtotime('2012-01-01 00:00:00')) {
							$renglon[$ord['orden_servicio']][$j] .= date('Y-m-d', strtotime($ord['orden_fecha_de_entrega']));
						} else {
							$renglon[$ord['orden_servicio']][$j] .= $lang['Sin Fecha'];
						}
					
						$renglon[$ord['orden_servicio']][$j] .= '
									</td>
									<td style="padding-left:2px; padding-right:2px;">
										' . date('Y-m-d', strtotime($ord['orden_fecha_ultimo_movimiento'])) . '
									</td>
									<td align="center">
										'.$dias.'
									</td>
									<td>
										' . $v[1] . '
									</td>
								</tr>'."\n";
					}
					
					$j++;
					if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
					$tcp[$ord['orden_servicio']] = $tcp[$ord['orden_servicio']] + $cost_part;
					$tcc[$ord['orden_servicio']] = $tcc[$ord['orden_servicio']] + $cost_cons;
					$tcm[$ord['orden_servicio']] = $tcm[$ord['orden_servicio']] + $cost_mo;
						
					}
					
				}
			}

			if($export == 1){ // ---- Hoja de calculo ----
			}
			else{ // ---- HTML ----
			
				foreach($contenido as $contk => $contv) {
					//echo 'OS -> ' . $contk . '<br>';
					echo '
			<div class="row">
				<div class="col-md-12 ">
					<div id="content-tabla">
						<table cellspacing="0" class="table-new">'."\n";
				
					$venta = $tcp[$contk] + $tcc[$contk] + $tcm[$contk];
					echo $contv;
				
					foreach($renglon[$contk] as $reglk => $reglv) {
						echo $reglv;
					}
				
					echo '				
							<tr>
								<td colspan="7">
									<b>' . $lang['Se encontraron'] . ' ' .count($renglon[$contk]) . ' ' . $lang['TrabajosCliente'] . '.</b>
								</td>
								<td style="text-align:right;">
									<b>' . number_format($tcp[$contk],2) . '</b>
								</td>
								<td style="text-align:right;">
									<b>' . number_format($tcc[$contk],2) . '</b>
								</td>
								<td style="text-align:right;">
									<b>' . number_format($tcm[$contk],2) . '</b>
								</td>
								<td style="text-align:right;">
									<b>' . $lang['VentaTotal'] . number_format($venta,2) . '</b>
								</td>
								<td colspan="5">
									&nbsp;
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>' . "\n";
				}
			}
			
		}
		
		if($export == 1){ // ---- Hoja de calculo ----
			
			//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="ots-por-aseguradora.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}
		
		echo '		
</div>'."\n";
	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}