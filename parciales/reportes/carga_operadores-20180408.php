<?php
/*************************************************************************************
*   Script de "reporte cobros y pagos"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
*
**************************************************************************************/

	if($export == 1){ // ---- Hoja de calculo ----
	
	}
	else{ // ---- HTML ----
		$href = 'reportes.php?accion=carga_operador&export=1';
		
		if($tipo_busq == 'detalle'){
			$href .= '&tipo_busq=detalle&opeflt=' . $opeflt;
		}
		
		echo '	
		<a href="' . $href . '">
			<img src="idiomas/' . $idioma . '/imagenes/hoja-calculo.png" alt="'. $lang['exportar pedidos'].'" border="0">
		</a>'."\n";
	}

	if($f1125120 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol04']=='1') {

		// ------ Consulta de ordenes activas
		$preg1 = "SELECT orden_id, orden_estatus, orden_fecha_promesa_de_entrega, orden_categoria, orden_servicio, orden_ubicacion FROM " . $dbpfx . "ordenes WHERE orden_estatus < '12' OR (orden_estatus > '17' AND orden_estatus < '30') AND orden_estatus != '21'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de ordenes! " . $preg1);
		$fila1 = mysql_num_rows($matr1);
		while($ord = mysql_fetch_array($matr1)) {
			$preg2 = "SELECT sub_orden_id, sub_area, sub_estatus, sub_presupuesto, sub_consumibles, sub_mo, recibo_id, sub_reporte, sub_aseguradora, sub_operador, sub_fecha_inicio, sub_horas_programadas, sub_reprocesos FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '130'";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de ordenes! " . $preg2);
			while($sub = mysql_fetch_array($matr2)) {
				if($sub['sub_operador'] > 0) {
					$preg3 = "SELECT seg_tipo FROM " . $dbpfx . "seguimiento WHERE usuario = '" . $sub['sub_operador'] . "' AND sub_orden_id = '" . $sub['sub_orden_id'] . "' ORDER BY seg_id DESC LIMIT 1";
					$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de seguimientos! " . $preg3);
					$seg = mysql_fetch_array($matr3);
				} elseif($sub['sub_presupuesto'] > 0) {
					$sub['sub_operador'] = 10;
					$usuario[10] = $lang['MO AUTORIZADA SIN OPERARIO']; $usr_cod[10] = 60; $usr_activo[10] = 1;
				}
				$refporcen = refaccionesCompletas($sub['sub_orden_id'], $dbpfx);
				if($ord['orden_ubicacion'] != 'Transito') { $ubic = 'En Taller'; } else { $ubic = 'En Tránsito'; }
				if($sub['sub_operador'] > 0) {
					$oper[$sub['sub_operador']][] = [
						'orden_id' => $ord['orden_id'],
						'sub_orden_id' => $sub['sub_orden_id'],
						'orden_estatus' => $ubic,
						'orden_servicio' => $ord['orden_servicio'],
						'sub_area' => $sub['sub_area'],
						'sub_estatus' => $sub['sub_estatus'],
						'sub_cons' => $sub['sub_consumibles'],
						'sub_mo' => $sub['sub_mo'],
						'reporte' => $sub['sub_reporte'],
						'aseg' => $sub['sub_aseguradora'],
						'horas' => $sub['sub_horas_programadas'],
						'reprocesos' => $sub['sub_reprocesos'],
						'sfi' => strtotime($sub['sub_fecha_inicio']),
						'fpe' => strtotime($ord['orden_fecha_promesa_de_entrega']),
						'repest' => $seg['seg_tipo'],
						'ordcat' => $ord['orden_categoria'],
						'refporcen' => $refporcen
					];
				}
			}
		}

		if($tipo_busq == 'resumen' || $tipo_busq == '') {
			unset($mo_autorizada);
			$fondo = 'claro';
			foreach($usuario as $key => $nombre_usuario) {
				if($usr_cod[$key] == 60 && $usr_activo[$key] == 1) {
					$fecha = '';
					$minutos = 0;
					foreach($oper[$key] as $datos) {
						$mo_autorizada[$key] = $mo_autorizada[$key] + $datos['sub_mo'];
						/*$preg_destajos = "SELECT monto, costcons FROM " . $dbpfx . "destajos_elementos WHERE operador = '" . $key . "' AND recibo_id = '" . $datos['recibo'] . "'";
						$matr_destajos = mysql_query($preg_destajos) or die("ERROR: Fallo selección de destajos! " . $preg_destajos);
						while($destajos = mysql_fetch_array($matr_destajos)){
							$mo_pagada[$key] = $mo_pagada[$key] + $destajos['monto'];
						}*/
						$veh[$key][$datos['orden_id']]++; // Vehículos asignados a este operario
						if($key > 10) { $vehasig[$datos['orden_id']]++; } // Cuenta de OTs asiganas en general
						$mins = explode(':', $datos['horas']);
						$minutos = $minutos + ($mins[0] * 60) + $mins[1];
					}
				}
				$horas = intval($minutos / 60);
				$mins = intval($minutos - ($horas * 60));
				if($mins < 10) { $mins = '0'.$mins; }
				$tiempo[$key] = $horas . ':' . $mins;
				$tot_mo = $tot_mo + $mo_autorizada[$key];
				$tot_mo_pag = $tot_mo_pag + $mo_pagada[$key];
				$vehiculos[$key] = count($veh[$key]);
				$totasig = $totasig + count($veh[$key]);
			}

			
			if($export == 1){ // ---- Hoja de calculo ----
	
				$fecha_export = date('Y-m-d H:i:s');
		
				// -------------------   Creación de Archivo Excel   --------------------
				$celda = 'A1';
				$titulo = 'REPORTE DE CARGA DE TRABAJO: ' . $nombre_agencia;
	
				require_once ('Classes/PHPExcel.php');
				$objReader = PHPExcel_IOFactory::createReader('Excel5');
				$objPHPExcel = $objReader->load("parciales/export.xls");
				$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
							->setTitle("REPORTE DE CARGA DE TRABAJO")
							->setKeywords("AUTOSHOP EASY");

				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($celda, $titulo)
							->setCellValue("A3", $fecha_export);

				// ------ ENCABEZADOS ---
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue("A4", "TODOS LOS OPERADORES")
							->setCellValue("B4", "VEHÍCULOS")
							->setCellValue("C4", "CARGA EN HORAS")
							->setCellValue("D4", "MANO DE OBRA AUTORIZADA");
				$z= 5;
		
			}
			else{ // ---- HTML ----
				echo '			
				<table cellspacing="2" cellpadding="2" border="0" class="izquierda">
					<tr class="cabeza_tabla" style="text-align: right;">
						<td colspan="4" style="text-align: left;">Carga de Trabajo Asignada a Operarios</td>
					</tr>
					<tr>
						<td style="text-align: left;"><STRONG><a href="reportes.php?accion=carga_operador&tipo_busq=detalle">TODOS LOS OPERADORES</STRONG></a></td>
						<td style="text-align: center;"><STRONG>VEHÍCULOS</STRONG></td>
						<td style="text-align: center;"><STRONG>CARGA EN HORAS</STRONG></td>
						<td style="text-align: center;"><STRONG>MANO DE OBRA AUTORIZADA</STRONG></td>
					</tr>'."\n";

				$fondo = 'claro';
			}	
			
			foreach($usuario as $key => $nombre_usuario) {
				if($usr_cod[$key] == 60 && $usr_activo[$key] == 1) {
							
					if($export == 1){ // ---- Hoja de calculo ----
						
						// --- Celdas a grabar ----
						$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
			
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($a, $nombre_usuario)
									->setCellValue($b, $vehiculos[$key])
									->setCellValue($c, $tiempo[$key])
									->setCellValue($d, number_format($mo_autorizada[$key], 2));
			
						$z++;
					
						
					}
					else{ // ---- HTML ----
				
						$enlace = '<a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $key . '">' . $nombre_usuario . '</a>';
						echo '				
						<tr class="' . $fondo . '">
							<td>' . $enlace . 	'</td>
							<td style="text-align: center;">' . $vehiculos[$key] . '</td>
							<td style="text-align: right;">' . $tiempo[$key] . '</td>
							<td style="text-align: right; ' .  $rojo_mo_aut . '">$ ' .  number_format($mo_autorizada[$key], 2) . 	'</td>
						</tr>'."\n";
						if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
						
					}
				}
			}
			
			if($export == 1){ // ---- Hoja de calculo ----
				
				//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="carga-de-trabajo.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				$objWriter->save('php://output');
				exit;
			}
			else{ // ---- HTML ----	
				echo '				
						<tr>
							<td colspan="7" style="text-align: left;">
								<H2>' . count($vehasig) . ' Vehículos Asignados a Operarios de ' . $fila1 . ' Pendientes de Terminar su Reparación.</H2>
							</td>
						</tr>
					</table>'."\n";
			}
		}

		
		if($tipo_busq == 'detalle') {
			
			if($opeflt > 0) { $nombre_usuario = $usuario[$opeflt]; } else { $nombre_usuario = $lang['Todos Operarios']; }
			$encabezado = 'Reporte de carga de trabajo de ' . $nombre_usuario;
			
			if($export == 1){ // ---- Hoja de calculo ----
			
				$fecha_export = date('Y-m-d H:i:s');
		
				// -------------------   Creación de Archivo Excel   --------------------
				$celda = 'A1';
				$titulo = $encabezado . ' ' . $nombre_agencia;
	
				require_once ('Classes/PHPExcel.php');
				$objReader = PHPExcel_IOFactory::createReader('Excel5');
				$objPHPExcel = $objReader->load("parciales/export.xls");
				$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
							->setTitle($encabezado)
							->setKeywords("AUTOSHOP EASY");

				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($celda, $titulo)
							->setCellValue("A3", $fecha_export);

				// ------ ENCABEZADOS ---
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue("A4", $lang['Operario'])
							->setCellValue("B4", $lang['OT'])
							->setCellValue("C4", $lang['Vehículo'])
							->setCellValue("D4", $lang['Carga en Horas'])
							->setCellValue("E4", $lang['Area'])
							->setCellValue("F4", $lang['Tipo Trabajo'])
							->setCellValue("G4", $lang['Mano de Obra Autorizada'])
							->setCellValue("H4", $lang['Estatus de Reparación'])
							->setCellValue("I4", $lang['Fecha de Inicio'])
							->setCellValue("j4", $lang['Refacciones Recibidas'])
							->setCellValue("k4", $lang['FechaPromesa'])
							->setCellValue("l4", $lang['EstatusOT']);
				$z= 5;
				
			}
			else{ // ---- HTML ----	
				echo '			
					<table cellspacing="2" cellpadding="2" border="0" class="izquierda">
						<tr class="cabeza_tabla" style="text-align: right;">
							<td colspan="12" style="text-align: left;">' . $encabezado . '</td>
						</tr>
						<tr>
							<td class="centnegri"><a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=operario">' . $lang['Operario'] . '</a></td>
							<td class="centnegri"><a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=ordenid">' . $lang['OT'] . '</a></td>
							<td class="centnegri">' . $lang['Vehículo'] . '</td>
							<td class="centnegri">' . $lang['Carga en Horas'] . '</td>
							<td class="centnegri"><a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=area">' . $lang['Area'] . '</a></td>
							<td class="centnegri">' . $lang['Tipo Trabajo'] . '</td>
							<td class="centnegri">' . $lang['Mano de Obra Autorizada'] . '</td>
							<td class="centnegri"><a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=estrep">' . $lang['Estatus de Reparación'] . '</a></td>
							<td class="centnegri">' . $lang['Fecha de Inicio'] . '</td>
							<td class="centnegri">' . $lang['Refacciones Recibidas'] . ' <a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=refreca"><img src="imagenes/ordenar-asc.png" alt="Ordenar Ascendente" title="Ordenar Ascendente"></a> <a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=refrecd"><img src="imagenes/ordenar-desc.png" alt="Ordenar Decendente" title="Ordenar Decendente"></a></td>
							<td class="centnegri">' . $lang['FechaPromesa'] . ' <a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=fpea"><img src="imagenes/ordenar-asc.png" alt="Ordenar Ascendente" title="Ordenar Ascendente"></a> <a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=fped"><img src="imagenes/ordenar-desc.png" alt="Ordenar Decendente" title="Ordenar Decendente"></a></td>
							<td class="centnegri">' . $lang['EstatusOT'] . ' <a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=ubica"><img src="imagenes/ordenar-asc.png" alt="Ordenar Ascendente" title="Ordenar Ascendente"></a> <a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=ubicd"><img src="imagenes/ordenar-desc.png" alt="Ordenar Decendente" title="Ordenar Decendente"></a></td>
						</tr>'."\n";
			}

			$define_temporal = "CREATE TEMPORARY TABLE " . $dbpfx . "carga_operador" . $_SESSION['usuario'] . " (reg_id int(11), usuario int(11), operario varchar(128), orden_id int(11), sub_orden_id int(11), veh varchar(128), carga varchar(32), area int(11), tipotrab varchar(64), moa double, estrep varchar(40), feini varchar(10), refrec double DEFAULT NULL, fpe varchar(10), estatus varchar(60), ordcat int(3))";
			$crea_temporal = mysql_query($define_temporal) or die("ERROR: Fallo creación de temporal! " . $define_temporal);
			$index_temporal = "ALTER TABLE " . $dbpfx . "carga_operador" . $_SESSION['usuario'] . " ADD PRIMARY KEY (`reg_id`)";
			$crea_temporal = mysql_query($index_temporal) or die("ERROR: Fallo index de temporal! " . $index_temporal);
			$auto_temporal = "ALTER TABLE " . $dbpfx . "carga_operador" . $_SESSION['usuario'] . " MODIFY reg_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1";
			$crea_temporal = mysql_query($auto_temporal) or die("ERROR: Fallo autoinc de temporal! " . $auto_temporal);
			foreach($usuario as $key => $nombre_usuario) {
				if($usr_cod[$key] == 60 && $usr_activo[$key] == 1 && (($opeflt > 0 && $opeflt == $key) || $opeflt == '')) {
					unset($estrep);  // Estatus de Reparación Terminado.
					foreach($oper[$key] as $k => $datos) {
// ------ Obtenemos el número de OT, La fecha promesa de entrega y su estatus
						$orden_id[$key][$datos['sub_orden_id']] = $datos['orden_id'];
						$ordcat[$key][$datos['sub_orden_id']] = $datos['ordcat'];
						$ords[$key][$datos['sub_orden_id']] = $datos['orden_estatus'];
						$fpe[$key][$datos['sub_orden_id']] = $datos['fpe'];
						$area[$key][$datos['sub_orden_id']] = $datos['sub_area'];
						$refcomp[$key][$datos['sub_orden_id']] = $datos['refporcen'];
// ------ Sumamos los minutos de las diferentes tareas asignadas a este usuario en la OT actual.
						$mins = explode(':', $datos['horas']);
						$minutos[$key][$datos['sub_orden_id']] = $minutos[$key][$datos['sub_orden_id']] + ($mins[0] * 60) + $mins[1];
// ------ Verificamos que la fecha de inicio es válida y si es válida la guardada en $tini
						if($datos['sfi'] > 1000000) {
							$tini[$key][$datos['sub_orden_id']] = $datos['sfi'];
						} else {
							$tini[$key][$datos['sub_orden_id']] = 0;
						}
// ------ Obtenemos MO Autorizada de las diferentes tareas asignadas a este usuario en la OT actual.
						$mo_autorizada[$key][$datos['sub_orden_id']] = $mo_autorizada[$key][$datos['sub_orden_id']] + $datos['sub_mo'];
// ------ Obtenemos los estatus de reparación de las tareas de esta OT para el Operario y guardamos el menor
						if(!isset($estrep[$key][$datos['sub_orden_id']]) && $key > 10) { $estrep[$key][$datos['sub_orden_id']] = 7; }
						if($datos['repest'] < $estrep[$key][$datos['sub_orden_id']] || $datos['repest'] == '') {
							$estrep[$key][$datos['sub_orden_id']] = $datos['repest'];
						}
// ------ Se determina el tipo de trabajo: Normal, Garantía, Interno o Reproceso
						$tipotrab[$key][$datos['sub_orden_id']] = '';
						if($datos['reprocesos'] > 0) { $tipotrab[$key][$datos['sub_orden_id']] .= $datos['reprocesos'] . ' ' . $lang['Reprocesos']; }
						if($datos['orden_servicio'] == '2') { $tipotrab[$key][$datos['sub_orden_id']] .= ' ' . constant('ORDEN_SERVICIO_2'); }
						if($datos['reporte'] == 'Interno') { $tipotrab[$key][$datos['sub_orden_id']] .= ' ' . $lang['Interno']; }
						if($tipotrab[$key][$datos['sub_orden_id']] == '') { $tipotrab[$key][$datos['sub_orden_id']] = $lang['Normal']; }
// ------ Cuenta de OTs asignadas en general
						if($key > 10) {
							$vehasig[$datos['orden_id']]++;
						}
					}

					foreach($ords[$key] as $k => $estatus) {
						$veh = datosVehiculo($orden_id[$key][$k], $dbpfx);
						$vehiculo = $veh['marca'] . ' ' . $veh['modelo'] . ' ' . $veh['color'] . ' ' . $veh['placas'];
						$horas = intval($minutos[$key][$k] / 60);
						$mins = intval($minutos[$key][$k] - ($horas * 60));
						if($mins < 10) { $mins = '0'.$mins; }
						$carga = $horas . ':' . $mins;
						if($tini[$key][$k] > 1000000) { $ftini = date('Y-m-d', $tini[$key][$k]); } else { $ftini = '---'; }
						$refrecper = $refcomp[$key][$k];
/*						if($refcomp[$key][$k] == '200') {
							$refrecper = '';
						} else {
							$refrecper = $refcomp[$key][$k];
						}
*/
						
						$temporal = "INSERT INTO " . $dbpfx . "carga_operador" . $_SESSION['usuario'] . " (usuario, operario, orden_id, sub_orden_id, veh, carga, area, tipotrab, moa, estrep, feini, refrec, fpe, estatus, ordcat) VALUES ('" . $key . "', '" . $usuario[$key] . "', '" . $orden_id[$key][$k] . "', '" . $k . "', '" . $vehiculo . "', '" . $carga . "', '" . $area[$key][$k] . "', '" . $tipotrab[$key][$k] . "', '" . $mo_autorizada[$key][$k] . "', '" . $estrep[$key][$k] . "', '" . $tini[$key][$k] . "', '" . $refrecper . "', '" . $fpe[$key][$k] . "', '" . $estatus . "', '" . $ordcat[$key][$k] . "')";
						$inserta_temporal = mysql_query($temporal) or die("ERROR: Fallo inserción de temporal! " . $temporal);
					}
				}
			}

			$preg4 = "SELECT * FROM " . $dbpfx . "carga_operador" . $_SESSION['usuario'] . " ORDER BY ";
			if($colflt == 'operario') { $preg4 .= " operario,fpe"; }
			elseif($colflt == 'ordenid') { $preg4 .= " orden_id"; }
			elseif($colflt == 'area') { $preg4 .= " area,fpe"; }
			elseif($colflt == 'estrep') { $preg4 .= " estrep,fpe"; }
			elseif($colflt == 'refreca') { $preg4 .= " refrec ASC"; }
			elseif($colflt == 'refrecd') { $preg4 .= " refrec DESC"; }
			elseif($colflt == 'fpea') { $preg4 .= " fpe ASC"; }
			elseif($colflt == 'fped') { $preg4 .= " fpe DESC"; }
			elseif($colflt == 'ubica') { $preg4 .= " estatus ASC"; }
			elseif($colflt == 'ubicd') { $preg4 .= " estatus DESC"; }
			else { $preg4 .= " fpe,usuario"; }
//			echo $preg4;
			$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de temporal! " . $preg4);
			$fondo = 'claro';
			while($tmp = mysql_fetch_array($matr4)) {
				
				if($export == 1){ // ---- Hoja de calculo ----
						
					// --- Celdas a grabar ----
					$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
					$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $jota = 'J'.$z;
					$k = 'K'.$z; $l = 'L'.$z;
					
					if($tmp['feini'] > 1000000){
						
						$ftini = date('Y-m-d', $tmp['feini']);
						
						$ftini = PHPExcel_Shared_Date::PHPToExcel( strtotime($ftini) );
						// --- cambiar el formato de la celda tipo fecha/date ---
						$objPHPExcel->getActiveSheet()
    								->getStyle($i)
    								->getNumberFormat()
    								->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
					} else{
						$ftini = '---';
					}
					
					if($tmp['fpe'] > 1000000) {
						
						$fpef = date('Y-m-d', $tmp['fpe']);
						
						$fpef = PHPExcel_Shared_Date::PHPToExcel( strtotime($fpef) );
						// --- cambiar el formato de la celda tipo fecha/date ---
						$objPHPExcel->getActiveSheet()
    								->getStyle($k)
    								->getNumberFormat()
    								->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
						
					} else {
						$fpef = 'Sin Fecha';
					}
					
					if($tmp['refrec'] <= 100) {
						$refacciones = $tmp['refrec'] . '%';
					} else {
						$refacciones = $lang['No Aplica'];
					}
			
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($a, $usuario[$tmp['usuario']])
								->setCellValue($b, $tmp['orden_id'])
								->setCellValue($c, $tmp['veh'])
								->setCellValue($d, $tmp['carga'])
								->setCellValue($e, constant('NOMBRE_AREA_'.$tmp['area']))
								->setCellValue($f, $tmp['tipotrab'])
								->setCellValue($g, number_format($tmp['moa'], 2))
								->setCellValue($h, $lang['EstaRep'. $tmp['estrep']])
								->setCellValue($i, $ftini)
								->setCellValue($jota, $refacciones)
								->setCellValue($k, $fpef)
								->setCellValue($l, $tmp['estatus']);
			
					$z++;
						
				}
				else{ // ---- HTML ----
				
					echo '				
						<tr class="' . $fondo . '">
							<td>' . $usuario[$tmp['usuario']] . '</td>
							<td style="text-align: center;">
								<a href="proceso.php?accion=consultar&orden_id=' . $tmp['orden_id'] . '#' . $tmp['sub_orden_id'] . '" target="_blank">' . $tmp['orden_id'] . '</a>
							</td>
							<td>' . $tmp['veh'] . '</td>
							<td style="text-align: center;">' . $tmp['carga'] . '</td>
							<td style="text-align: center;" class="area' . $tmp['area'] . '">
								' . constant('NOMBRE_AREA_'.$tmp['area']) . '
							</td>'."\n";
					
						if($tmp['tipotrab'] != $lang['Normal']) { $tipoaler = 'alarma_critica';} else { $tipoaler = ''; }
					
						echo '					
							<td class="' . $tipoaler . '" style="text-align: center;">
								' . $tmp['tipotrab'] . '
							</td>
							<td style="text-align: right;">
								$ ' .  number_format($tmp['moa'], 2) . 	'
							</td>
							<td style="text-align: center;">
								' . $lang['EstaRep'. $tmp['estrep']] . '
							</td>'."\n";
					
						if($tmp['feini'] > 1000000) { $ftini = date('Y-m-d', $tmp['feini']); } else { $ftini = '---'; }
						echo '					
							<td style="text-align: center;">' . $ftini . '</td>
							<td style="text-align: center;">';
						if($tmp['refrec'] <= 100) {
							echo '
								<a href="refacciones.php?accion=gestionar&orden_id=' . $tmp['orden_id'] . '&subflt=' . $tmp['sub_orden_id'] . '" target="_blank">' . $tmp['refrec'] . '%</a>';
						} else {
							echo $lang['No Aplica'];
						}
						echo '
							</td>'."\n";
						if($tmp['fpe'] > 1000000) {
							if($tmp['ordcat'] == '2') {$talarma = 172800;} else {$talarma = 7200;}
								$alerta = 'alarma_preventiva';
							if ($tmp['fpe'] <= time()) {
								$alerta = 'alarma_critica';
							} elseif (($tmp['fpe'] - $talarma) > time()) {
								$alerta = '';
							}
							$fpef = date('Y-m-d', $tmp['fpe']);
						} else {
							$fpef = '<span style="background-color: red; color: white; font-weight: bold;">Sin Fecha</span>';
							$alerta = '';
						}
						echo '					
							<td class="' . $alerta . '" style="text-align: center;">' . $fpef . '</td>
							<td><STRONG>' . $tmp['estatus'] . '</STRONG></td>
						</tr>'."\n";
						if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
						if($tmp['usuario'] > 10 || $opeflt == 10) { $total = $total + 1; }
				}
			}
			
			if($export == 1){ // ---- Hoja de calculo ----

				//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="carga_operador.xls"');
				header('Cache-Control: max-age=0');


				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				$objWriter->save('php://output');
				exit;
			}
			else{ // ---- HTML ----
				echo '				
						<tr>
							<td colspan="12" style="text-align: left;">
								<H2>' . $total . ' Tareas en ' . count($vehasig) . ' Vehículos Asignados</H2>
							</td>
						</tr>
					</table>'."\n";
			}
		}
		
	} else {
		echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}