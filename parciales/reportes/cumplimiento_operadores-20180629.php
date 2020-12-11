<?php
/*************************************************************************************
*   Script de "reporte cumplimiento de operadores"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
*
**************************************************************************************/

	if($f1125120 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol04']=='1') {

		//echo 'fini' . $feini . ' ffin ' . $fefin . ' operador ' . $operador . ' estatus_tareas ' . $estatus_tareas . '<br>';		
		
		// --- Realizar consulta de subordenes ---
		$preg_tareas = " SELECT * FROM " . $dbpfx . "subordenes WHERE sub_fecha_inicio >= '" . $feini . "' AND sub_fecha_inicio <= '" . $fefin . "' ";
		// --- Aplicar filtros a la consulta ---
		if($operador != 0){
			$preg_tareas .= " AND sub_operador = '" . $operador . "' ";
		}
		if($estatus_tareas == 1){ // --- Tareas sin terminar ---
			$preg_tareas .= " AND ( sub_fecha_terminado = '' OR sub_fecha_terminado IS NULL ) ";
		}
		elseif($estatus_tareas == 2){ // --- Tareas terminadas ---
			$preg_tareas .= " AND ( sub_fecha_terminado != '' OR sub_fecha_terminado != '0000-00-00 00:00:00' OR sub_fecha_terminado IS NOT NULL ) ";
		}
		//echo $preg_tareas . '<br>';
		$matr_tareas = mysql_query($preg_tareas) or die("ERROR: Fallo selección de tareas! " . $preg_tareas);
		$num_tareas = mysql_num_rows($matr_tareas);
		
		if($operador > 0) { $nombre_usuario = $usuario[$operador]; } else { $nombre_usuario = $lang['Todos Operarios']; }
		$encabezado = 'Reporte de rendimiento: ' . $nombre_usuario;
			
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
						->setCellValue("B4", $lang['Tarea'])
						->setCellValue("C4", $lang['Area'])
						->setCellValue("D4", $lang['OT'])
						->setCellValue("E4", $lang['Horas estimadas'])
						->setCellValue("F4", $lang['Horas utilizadas'])
						->setCellValue("G4", $lang['Déficit / Superative'])
						->setCellValue("H4", $lang['% Pérdida / Ganancia'])
						->setCellValue("I4", $lang['Reprocesos'])
						->setCellValue("J4", $lang['Tiempo de reproceso'])
						->setCellValue("K4", $lang['Historial de comentarios'])
						->setCellValue("L4", $lang['Evaluada?']);
			$z= 5;
				
		}
		else{ // ---- HTML ----	
				echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-10">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2>' . $encabezado . '</h2>
				</div>
			</div>
		</div>
	</div>		
	<div class="row">
		<div class="col-md-10">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
						<tr>
							<th>
								' . $lang['Operario'] . '
							</th>
							<th>
								' . $lang['Tarea'] . '
							</th>
							<th>
								<a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=area">' . $lang['Area'] . '</a>
							</th>
							<th>
								<a href="reportes.php?accion=carga_operador&tipo_busq=detalle&opeflt=' . $opeflt . '&colflt=ordenid">' . $lang['OT'] . '</a>
							</th>
							<th>
								' . $lang['Horas estimadas'] . '
							</th>
							<th>
								' . $lang['Horas utilizadas'] . '
							</th>
							<th>
								
								<a onclick="muestraAbajo01()" class="ayuda" >' . $lang['Pérdida / Ganancia'] . '</a>
								<div id="AyudaItem01" class="muestra-contenido">
									' . $lang['ayuda_perdida_ganancia'] . '
								</div>
								<script>
									function muestraAbajo01() {
    									document.getElementById("AyudaItem01").classList.toggle("mostrar");
									}
								</script>
								
							</th>
							<th>
								' . $lang['% Pérdida / Ganancia'] . '
							</th>
							<th>
								' . $lang['Reprocesos'] . '
							</th>
							<th>
								' . $lang['Tiempo de reproceso'] . '
							</th>
							<th>
								' . $lang['Historial de comentarios'] . '
							</th>
							<th>
								' . $lang['Evaluada?'] . '
							</th>
						</tr>'."\n";
		}
		
			$fondo = "claro";
			$total_horas_rep = 0;
			$total_deficit = 0;
			$total_horas_aseg = 0;
			$total_horas_utilizadas = 0;
			while($tmp = mysql_fetch_array($matr_tareas)) {
				
				// ************* CALCULOS *****************
				$class_repro = '';
				if($tmp['sub_reprocesos'] > 0){ // ---- Si hay reprocesos calcular el tiempo que tardaron esos reprocesos ----
					
					$class_repro = 'rojo_tenue';
					// --- Colsultar fecha en que la orden se envió a reproceso ---
					$preg_reproceso = "SELECT bit_fecha FROM " . $dbpfx . "bitacora WHERE bit_estatus like 'Cambio de Tarea " . $tmp['sub_orden_id'] . " a estatus 107%' LIMIT 1";
					$matr_reproceso = mysql_query($preg_reproceso) or die("ERROR: Fallo seleccion! " . $preg_reproceso);
					$fecha_repro = mysql_fetch_assoc($matr_reproceso);
					//echo $fecha_repro['bit_fecha'];
					// --- Buscar el seguimiento de la tarea la fecdha que sea mayor a la fecha de reproceso ---
					$preg_seg_rep = "SELECT seg_tipo, seg_hora_registro FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" . $tmp['sub_orden_id'] . "' AND seg_hora_registro > '" . $fecha_repro['bit_fecha'] . "'";
					//echo $preg_seg_rep;
					$matr_seg_rep = mysql_query($preg_seg_rep) or die("ERROR: Fallo seleccion! " . $preg_seg_rep);
					// --- Sumar tiempo de reproceso ---
					$horas = 0;
					while($seguimiento = mysql_fetch_array($matr_seg_rep)){
						// --- seg_tipo (5 = continua, 2 = pausa, 7 = terminado) ---
						//echo 'seg_tipo ' . $seguimiento['seg_tipo'] . ' seg_hora_registro ' . $seguimiento['seg_hora_registro'] . '<br>';
						if($seguimiento['seg_tipo'] == 5){ // --- continua ---
							$restar = strtotime($seguimiento['seg_hora_registro']);
							//echo 'inicia ' . $seguimiento['seg_hora_registro'] . '<br> segundos = ' . $restar . '<br>';
						}
						elseif($seguimiento['seg_tipo'] == 2 || $seguimiento['seg_tipo'] == 7){ // ---- pausa o termino ---
							$pausa = strtotime($seguimiento['seg_hora_registro']);
							//echo 'Pausa ' . $seguimiento['seg_hora_registro'] . '<br> segundos = ' . $pausa . '<br>';
							// --- Obtener diferencia de horas ---
							$diferencia = $pausa - $restar;
							$horas = $horas + $diferencia;
						}
					}
					$total_horas_rep = $total_horas_rep + $horas;
					$horas_reproceso = conv_seg_hora($horas);
					
				}else{
					$total_horas_rep = $total_horas_rep + 0;
					$class_repro = '';
					$horas_reproceso = '-';
					$tmp['sub_reprocesos'] = 0;
				}
				
				// --- Consultar comentarios ---
				$pregcom = "SELECT * FROM " . $dbpfx . "comentarios WHERE orden_id = '" . $tmp['orden_id'] . "' AND sub_orden_id = '" . $tmp['sub_orden_id'] . "'";
				$matrcom = mysql_query($pregcom) or die("ERROR: Fallo seleccion!");
				$j=0; $fondo_comentario='claro';
				$comentarios = '';
				while($comen = mysql_fetch_array($matrcom)) {
					if($export == 1){ // ---- Hoja de calculo ----
						$comentarios .= $comen['fecha_com'] . ' -> Usuario: ' . $comen['usuario'] . '. ' . $comen['comentario'] . ' | ';
					}
					else{ // --- HTML ---
						$comentarios .= '
						<p class="' . $fondo_comentario . '" style="margin-top:0px; padding-left:3px; padding-right:3px;">
							' . $comen['fecha_com'] . ' -> Usuario: ' . $comen['usuario'] . '. ' . $comen['comentario'] . '
						</p>';
					$j++;
					if ($j == 1) { $fondo_comentario = 'obscuro'; } else { $fondo_comentario = 'claro'; $j = 0; }
					}
				}
				if($comentarios == ''){
					if($export == 1){ // ---- Hoja de calculo ----
						$comentarios = 'SIN COMENTARIOS';
					}
					else{ // --- HTML ---
						$comentarios = 'SIN COMENTARIOS';
					}
				}
					
				// --- Calcular deficit ---
				$estilo_deficit = '';
				$programadas = conv_segundos($tmp['sub_horas_programadas']);
				$empleadas = conv_segundos($tmp['sub_horas_empleadas']);
				
				// --- $porcen_ganancia = conv_seg_hora($porcen_ganancia);
				
				$total_horas_aseg = $total_horas_aseg + $programadas;
				$total_horas_utilizadas = $total_horas_utilizadas + $empleadas;
				if($programadas == 0){
					if($export == 1){ // ---- Hoja de calculo ----
						$deficit = 'No se puede calcular';
					}
					else{ // --- HTML ---
						$deficit = '<small>No se puede calcular</small>';
					}
					$ganancia = '0';
					$total_deficit = $total_deficit + 0;
					$estilo_deficit = 'rojo_tenue';
					$class_porc = '';
					$class_margen = '';
					
				} else{
					
					/*
					$porcen_ganancia = $programadas * $margen_alarma; // --- $margen_alarma ---- Definido en config.php ---
					if($empleadas < $porcen_ganancia){ // --- Si las horas utilizadas son menores al margen de ganacia saltar alarma ---
						$class_margen = 'rojo_tenue';
					}else{
						$class_margen = '';
					}
					*/
					
					$class_porc = '';
					$ganancia = ($empleadas / $programadas) * 100;
					$ganancia = round($ganancia, 2);
					if($ganancia > 100){ // --- Pérdida ---
						$ganancia = $ganancia - 100;
						$ganancia = $ganancia * - 1;
						
						if($ganancia < $limite_minimo_ganancia){
							$class_porc = 'rojo_tenue';
						}
						
					} else{ // --- Ganancia ---
						$ganancia = 100 - $ganancia;
						
						if($ganancia > $limite_maximo_ganancia){
							$class_porc = 'rojo_tenue';
						}
					}
					
					$deficit = $programadas - $empleadas;
					$total_deficit = $total_deficit + $deficit;
					$deficit = conv_seg_hora($deficit);
					$encuentra = substr($deficit,0,1);
					if($encuentra === '-'){
						$estilo_deficit = 'rojo_tenue';
					} else{
						$estilo_deficit = 'verde_tenue';
					}
				}
				// --- Definir si fue evaluada ---
				if($tmp['sub_estatus'] == 112){
					$evaluada = 'SI';
				} else{
					$evaluada = 'NO';
				}
				
				
				if($export == 1){ // ---- Hoja de calculo ----
						
					// --- Celdas a grabar ----
					$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
					$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $jota = 'J'.$z;
					$kkk = 'K'.$z; $l = 'L'.$z;
			
					$area = constant('NOMBRE_AREA_'.$tmp['sub_area']);
					
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($a, $usuario[$tmp['sub_operador']])
								->setCellValue($b, $tmp['sub_orden_id'])
								->setCellValue($c, $area)
								->setCellValue($d, $tmp['orden_id'])
								->setCellValue($e, $tmp['sub_horas_programadas'])
								->setCellValue($f, $tmp['sub_horas_empleadas'])
								->setCellValue($g, $deficit)
								->setCellValue($h, $ganancia)
								->setCellValue($i, $tmp['sub_reprocesos'])
								->setCellValue($jota, $horas_reproceso)
								->setCellValue($kkk, $comentarios)
								->setCellValue($l, $evaluada);
			
					$z++;
						
				}
				else{ // ---- HTML ----
									
					echo '				
						<tr class="' . $fondo . '">
							<td>
								' . $usuario[$tmp['sub_operador']] . '
							</td>
							<td>
								<a href="proceso.php?accion=consultar&orden_id=' . $tmp['orden_id'] . '#' . $tmp['sub_orden_id'] . '" target="_blank">' . $tmp['sub_orden_id'] . '</a>
							</td>
							<td style="text-align: center;" class="area' . $tmp['area'] . '">
								' . constant('NOMBRE_AREA_'.$tmp['sub_area']) . '
							</td>
							<td style="text-align: center;">
								<a href="ordenes.php?accion=consultar&orden_id=' . $tmp['orden_id'] . '" target="_blank">' . $tmp['orden_id'] . '</a>
							</td>
							<td style="text-align: center;">
								' . $tmp['sub_horas_programadas'] . '
							</td>
							<td class="' . $class_margen . '" style="text-align: center;">
								' . $tmp['sub_horas_empleadas'] . '
							</td>
							<td class="' . $estilo_deficit . '">
								' . $deficit . '
							</td>
							<td class="' . $class_porc . '" style="text-align: center;">
								' . $ganancia . ' %
							</td>
							<td class="' . $class_repro . '">
								' . $tmp['sub_reprocesos'] . '
							</td>
							<td>
								' . $horas_reproceso . '
							</td>'."\n";
							
					if($comentarios == 'SIN COMENTARIOS'){
						echo '
							<td>
								<b>' . $comentarios . '</b>
							</td>'."\n";
					} elseif($comentarios != 'SIN COMENTARIOS'){
						echo '
							<td>
								<a onclick="muestraAbajo' . $tmp['sub_orden_id'] . '()" class="ayuda" >Ver comentarios</a>
								<div id="AyudaItem' . $tmp['sub_orden_id'] . '" class="muestra-contenido">
									' . $comentarios . '
								</div>
								<script>
									function muestraAbajo' . $tmp['sub_orden_id'] . '() {
    									document.getElementById("AyudaItem' . $tmp['sub_orden_id'] . '").classList.toggle("mostrar");
									}
								</script>
							</td>'."\n";
						
					}
					
					echo '
							<td>
								<b>' . $evaluada . '</b>
							</td>
						</tr>'."\n";
					
					if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
					if($tmp['usuario'] > 10 || $opeflt == 10) { $total = $total + 1; }
					
				}
			}
			
			if($export == 1){ // ---- Hoja de calculo ----

				//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="cumplimiento-operadores.xls"');
				header('Cache-Control: max-age=0');


				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				$objWriter->save('php://output');
				exit;
			}
			else{ // ---- HTML ----
				echo '	
						<tr>
							<td colspan="4" style="text-align: right;">
								<b>TOTALES</b>
							</td>
							<td colspan="1">
								<b>' . conv_seg_hora($total_horas_aseg) . '</b>
							</td>
							<td colspan="1">
								<b>' . conv_seg_hora($total_horas_utilizadas) . '</b>
							</td>
							<td colspan="1">
								<b>' . conv_seg_hora($total_deficit) . '</b>
							</td>
							<td colspan="2">
							</td>
							<td colspan="1">
								<b>' . conv_seg_hora($total_horas_rep) . '</b>
							</td>
						</tr>
						<tr>
							<td colspan="12" style="text-align: left;">
								<H2>' . $num_tareas . ' Tarea(s) </H2>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>'."\n";
			}
		
		
	}
?>
