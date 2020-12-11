<?php
/*************************************************************************************
*   Script de "Mano de obra"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/


if ($f1125060 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1') {

	if($export == 1){ // ---- Hoja de calculo ----
		
		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'MANO DE OBRA: ' . $nombre_agencia;
	
		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("MANO DE OBRA")
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
					->setCellValue("E4", "Siniestro")
					->setCellValue("F4", "Tiempo MO Hojalatería")
					->setCellValue("G4", "Monto MO Hojalatería")
					->setCellValue("H4", "Tiempo MO Pintura")
					->setCellValue("I4", "Monto MO Pintura")
					->setCellValue("J4", "Tiempo MO Otros")
					->setCellValue("K4", "Monto MO Otros");
		$z = 5;

	}
	else{ // ---- HTML ----
		
		echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-12 panel-title">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2><small>' . $encabezado . '</small></h2>
				</div>
			</div>
		</div>
	</div>' . "\n";
		
	}
	
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	
	foreach($ase as $k => $v) {
		
		$preg0 = "SELECT o.orden_id, o.orden_estatus, o.orden_vehiculo_placas, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_id, o.orden_alerta, o.orden_asesor_id, o.orden_fecha_recepcion, o.orden_fecha_de_entrega, o.orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes o, " . $dbpfx . "subordenes s WHERE ";
		$preg0 .= $prega ;
		$preg0 .= " AND s.sub_aseguradora = '$k' AND s.sub_estatus < '130' AND o.orden_id = s.orden_id GROUP BY o.orden_id ";
		$preg0 .= "ORDER BY o.orden_id";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de lapso!");
		$filas = mysql_num_rows($matr0);
		
		if($export == 1){ // ---- Hoja de calculo ----
			
		}
		else{ // ---- HTML ----}
			
			echo '
		<div class="row">
			<div class="col-md-12 ">
				<div id="content-tabla">
					<table cellspacing="0" class="table-new">
						<tr>
							<th colspan="13" style="text-align: left !important;">
									' . $filas . ' Ordenes de Trabajo para ' . $v[1] . '&nbsp;<img src="' . $v[0] . '" alt="" />
							</th>
						</tr>
						<tr>
							<th>
								OT
							</th>
							<th>
								Vehículo
							</th>
							<th>
								' . $lang['Placa'] . '
							</th>
							<th>
								Estatus
							</th>
							<th>
								Siniestro
							</th>
							<td class="area6" colspan="2">
								<b>MO Hojalatería</b>
							</td>
							<td class="area7" colspan="2">
								<b>MO Pintura</b>
							</td>
							<td class="areaotra" colspan="2">
								<b>MO Otros</b>
							</td>
						</tr>
						<tr>
							<td colspan="5">&nbsp;</td>
							<td class="area6" style="text-align:center;">
								<b>Tiempo</b>
							</td>
							<td class="area6" style="text-align:center;">
								<b>Monto</b>
							</td>
							<td class="area7" style="text-align:center;">
								<b>Tiempo</b>
							</td>
							<td class="area7" style="text-align:center;">
								<b>Monto</b>
							</td>
							<td class="areaotra" style="text-align:center;">
								<b>Tiempo</b>
							</td>
							<td class="areaotra" style="text-align:center;">
								<b>Monto</b>
							</td>
						</tr>'."\n";
						$fondo = 'claro';
		}

		$mo6dt = 0;
		$mo6pt = 0;
		$mo7dt = 0;
		$mo7pt = 0;
		$mo1dt = 0;
		$mo1pt = 0;

		while($ord = mysql_fetch_array($matr0)){
		
			$preg2 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '130' AND sub_aseguradora = '$k' GROUP BY sub_reporte";
			$matr2 = mysql_query($preg2);
			$filas2 = mysql_num_rows($matr2);
			
			while($gsub = mysql_fetch_array($matr2)) {
			
				if($export == 1){ // ---- Hoja de calculo ----
					
					// --- Celdas a grabar ----
					$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
					$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $jota = 'J'.$z;
					$kk = 'K'.$z;
				
					$vehiculo = strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']);
					
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($a, $ord['orden_id'])
								->setCellValue($b, $vehiculo)
								->setCellValue($c, $ord['orden_vehiculo_placas'])
								->setCellValue($d, constant('ORDEN_ESTATUS_' . $ord['orden_estatus']));
					
				}
				else{ // ---- HTML ----
				
					//echo $ord['orden_id'] . '<br>';
					
					echo '				
						<tr class="' . $fondo . '">
							<td>
								<a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" style=" display:block;">' . $ord['orden_id'] . '</a>
							</td>
							<td style="text-align: left !important;">
								' . strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']) . '
							</td>
							<td style="padding-left:10px; padding-right:10px;">
								' . $ord['orden_vehiculo_placas'] . '
							</td>
							<td style="padding-left:10px; padding-right:10px;">
								' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '
							</td>
							<td>';

				}
			
				if($export == 1){ // ---- Hoja de calculo ----
					
					if($gsub['sub_reporte'] == '0'){ 
						$rep = 'Particular'; 
					} else{ 
						$rep = $gsub['sub_reporte']; 
					}
					
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($e, $rep);
					
				}
				else{ // ---- HTML ----
				
					if($gsub['sub_reporte'] == '0'){ 
						echo 'Particular'; 
					} else{ 
						echo $gsub['sub_reporte']; 
					}
				
					echo '
							</td>';
				}
				
				$preg1 = "SELECT sub_orden_id, sub_area FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '130' AND sub_reporte = '" . $gsub['sub_reporte'] . "'";
				//echo $preg1;
				$matr1 = mysql_query($preg1) or die("Falló selección de subordenes");
				//echo '<table cellpadding="2" cellspacing="0" border="1" width="100%">';
				unset($mo_dec);
				unset($mo_pre);
				
				while($sub = mysql_fetch_array($matr1)) {
					$preg4 = "SELECT p.op_cantidad, p.op_precio FROM " . $dbpfx . "orden_productos p, " . $dbpfx . "subordenes s WHERE  p.sub_orden_id = s.sub_orden_id AND p.op_tangible = '0' AND s.sub_orden_id = '" . $sub['sub_orden_id'] . "'";
					$matr4 = mysql_query($preg4) or die("Falló selección de Orden Productos".$preg4);
					
						while($seg = mysql_fetch_array($matr4)) {
							$mo_dec[$sub['sub_area']] = $mo_dec[$sub['sub_area']] + $seg['op_cantidad'];
							$mo_pre[$sub['sub_area']] = $mo_pre[$sub['sub_area']] + ($seg['op_cantidad'] * $seg['op_precio']);
						}
				}
			
				$mo_dec[1] = $mo_dec[1] + $mo_dec[2] + $mo_dec[3] + $mo_dec[4] + $mo_dec[5] + $mo_dec[8] + $mo_dec[9] + $mo_dec[10];
				$mo_pre[1] = $mo_pre[1] + $mo_pre[2] + $mo_pre[3] + $mo_pre[4] + $mo_pre[5] + $mo_pre[8] + $mo_pre[9] + $mo_pre[10];
			
				if($export == 1){ // ---- Hoja de calculo ----
					
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($f, round($mo_dec[6], 2))
								->setCellValue($g,  round($mo_pre[6], 2))
								->setCellValue($h,  round($mo_dec[7], 2))
								->setCellValue($i, round($mo_pre[7], 2))
								->setCellValue($jota, round($mo_dec[1], 2))
								->setCellValue($kk, round($mo_pre[1], 2));

					$z++;
					
				}
				else{ // ---- HTML ----
			
					echo '
							<td class="area6" style="text-align: right !important;">
								<b>' . round($mo_dec[6], 2) . '</b>
							</td>
							<td class="area6" style="text-align: right !important;">
								<b>' . money_format("%n", $mo_pre[6]) . '</b>
							</td>
							<td class="area7" style="text-align: right !important;">
								<b>' . round($mo_dec[7], 2) . '</b>
							</td>
							<td class="area7" style="text-align: right !important;">
								<b>' . money_format("%n", $mo_pre[7]) . '</b>
							</td>
							<td class="areaotra" style="text-align: right !important;">
								<b>' . round($mo_dec[1], 2) . '</b>
							</td>
							<td class="areaotra" style="text-align: right !important;">
								<b>' . money_format("%n", $mo_pre[1]) . '</b>
							</td>
						</tr>'."\n";
				}
			
				$mo6dt = $mo6dt + round($mo_dec[6], 2);
				$mo6pt = $mo6pt + $mo_pre[6];
				$mo7dt = $mo7dt + round($mo_dec[7], 2);
				$mo7pt = $mo7pt + $mo_pre[7];
				$mo1dt = $mo1dt + round($mo_dec[1], 2);
				$mo1pt = $mo1pt + $mo_pre[1];
				$j++;
				if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
			}
			
		}
		
		if($export == 1){ // ---- Hoja de calculo ----
		
		}
		else{ // ---- HTML ----
			echo '				
						<tr class="cabeza_tabla">
							<td colspan="5" style="text-align:right;">
								<big>Totales:</big>
							</td>
							<td class="area6" style="text-align: right !important;">
								<big>' . $mo6dt . '</big>
							</td>
							<td class="area6" style="text-align: right !important;">
								<big>' . money_format("%n", $mo6pt) . '</big>
							</td>
							<td class="area7" style="text-align: right !important;">
								<big>' . $mo7dt . '</big>
							</td>
							<td class="area7" style="text-align: right !important;">
								<big>' . money_format("%n", $mo7pt) . '</big>
							</td>
							<td class="areaotra" style="text-align: right !important;">
								<big>' . $mo1dt . '</big>
							</td>
							<td class="areaotra" style="text-align: right !important;">
								<big>' . money_format("%n", $mo1pt) . '</big>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>'."\n";
		}
		
	}
		if($export == 1){ // ---- Hoja de calculo ----
			
			//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="mano-de-obra.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}
		else{ // ---- HTML ----
			echo '			
</div>'."\n";
		}

	} else{
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}






















?>