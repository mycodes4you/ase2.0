<?php
/*************************************************************************************
*   Script de "reporte de Vehiculos Entregados"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/

if ($f1125090 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {

	$preg1 = "SELECT o.orden_id, o.orden_grua, o.orden_vehiculo_color, o.orden_vehiculo_tipo, o.orden_vehiculo_placas, o.orden_estatus, o.orden_servicio, o.orden_fecha_recepcion, o.orden_fecha_de_entrega, o.orden_fecha_promesa_de_entrega FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o  WHERE o.orden_id = s.orden_id AND s.sub_estatus < '190' AND o.orden_fecha_de_entrega >= '" . $feini . "' AND o.orden_fecha_de_entrega <= '" . $fefin . "' ";
	if($servflt != '') {
		$preg1 .= " AND o.orden_servicio = '" . $servflt . "' ";
	}
	$preg1 .= " GROUP BY o.orden_id ORDER BY o.orden_id";
//	echo $preg1;
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de lapso de Ordenes!");
	$filas_ord = mysql_num_rows($matr1);
	
	if($export == 1){ // ---- Hoja de calculo ----
            
		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'VEHÍCULOS ENTREGADOS: ' . $nombre_agencia;
	
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
					->setCellValue("C4", "Placas")
					->setCellValue("D4", "Tipo")
					->setCellValue("E4", "Clientes")
					->setCellValue("F4", "Estatus")
					->setCellValue("G4", "Comentario de Seguimiento")
					->setCellValue("H4", "Fecha Recibido")
					->setCellValue("I4", "Fecha Entrega")
					->setCellValue("J4", "Días en Taller")
					->setCellValue("K4", "Fecha Promesa")
					->setCellValue("L4", "Días de Demora");
		$z= 5;
            
	}
	else{ // ---- HTML ----
		
		echo '	<form action="comisiones.php?accion=generar" method="post" enctype="multipart/form-data">'."\n";
		echo '		
					<table cellspacing="1" cellpadding="2" border="0">
						<tr class="cabeza_tabla">
							<td colspan="17" style="text-align: right;">Reporte de Vehículos Entregados: ' . $filas_ord . $encabezado . '</td>
						</tr>
						<tr>
							<td>OT</td>
							<td>Vehículo</td>
							<td>' . $lang['Placa'] . '</td>
							<td>Tipo</td>
							<td>Clientes</td>
							<td>Estatus</td>
							<td>Comentario de Seguimiento</td>
							<td>Fecha Recibido</td>
							<td>Fecha Entrega</td>
							<td>Días en Taller</td>
							<td>Fecha Promesa</td>
							<td>Días de Demora</td>';
		/*
		//	echo '
							<td style="text-align:center;">
								<a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['Comisiones'] . '&base=reportes.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">COMISIONES</a>
								<input  type="submit" value="Pagar" class="btn btn btn-success">
							</td>';
		*/
		echo '
						</tr>' . "\n";
		$fondo = 'claro';
		
	}

	$hoy = strtotime(date('Y-m-d 23:59:59'));
	$aat = 0; $adm = 0;
	
	while($ord = mysql_fetch_array($matr1)) {
		
		$preg3 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' ";
		if($asegflt != '') {
			$preg3 .= " AND sub_aseguradora = '" . $asegflt . "' ";
		}
		$preg3 .= " GROUP BY sub_reporte LIMIT 1";

		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de subordenes!".$preg3);
		$fila3 = mysql_num_rows($matr3);
		
		while($sub = mysql_fetch_array($matr3)) {
			
			$dias = intval((strtotime($ord['orden_fecha_de_entrega']) - strtotime($ord['orden_fecha_recepcion'])) / 86400);
			
			if(!isset($estanciamax) || $estanciamax == '') { $estanciamax = '20'; }
			
			if($dias > $estanciamax){
				
				if($export == 1){ // ---- Hoja de calculo ---- 
				
				}
				else{ // ---- HTML ----
				
					$dias = '<span style="font-weight:bold; color:red; background-color:yellow;">'.$dias.'</span>'; 
				}
			}
			
			if($export == 1){ // ---- Hoja de calculo ----    
	
				// --- Celdas a grabar ----
				$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
				$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
				$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z;
				
				$datos_vehi = strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']);
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($a, $ord['orden_id'])
							->setCellValue($b, $datos_vehi)
							->setCellValue($c, $ord['orden_vehiculo_placas'])
							->setCellValue($d, constant('ORDEN_SERVICIO_' . $ord['orden_servicio']));
				
			}
			else{ // ---- HTML ----
			
				//$vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);
				echo '				
						<tr class="' . $fondo . '">
							<td style="padding-left:2px; padding-right:2px; text-align:center;">
								<a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" target="_blank">' . $ord['orden_id'] . '</a>
							</td>
							<td style="padding-left:2px; padding-right:2px;">' . strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']) . '
							</td>
							<td style="padding-left:2px; padding-right:2px;">' . $ord['orden_vehiculo_placas'] . '
							</td>
							<td>' . constant('ORDEN_SERVICIO_' . $ord['orden_servicio']) . '</td>';
				
			}

			$pregas = "SELECT sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' GROUP BY sub_reporte";
			$matras = mysql_query($pregas) or die($pregas);
			$reporte = array(); $ima = array();
			while($aseico = mysql_fetch_assoc($matras)) {
				$ima[$aseico['sub_aseguradora']] = [$ase[$aseico['sub_aseguradora']][0], $ase[$aseico['sub_aseguradora']][1]];
				$reporte[$aseico['sub_reporte']] = 1;
				$cont_aseg[$aseico['sub_aseguradora']]++;
			}
			
			if($resumen_ing_egre == 1){
				// ******** RESUMENES DEL REPORTE ********

				// --- Agrupar por asesor ---
				$asesor_cont[$ord['orden_asesor_id']]++;

				// ---- Almacenar el tipo de servicio ----
				$tipo_serv[$ord['orden_servicio']]++;

				// --- Vehiculos en grua ---
				if($ord['orden_grua'] == 1){
					$grua_si++;
				}
				elseif($ord['orden_grua'] == 2){
					$grua_no++;
				}
				else{
					$grua_no_def++;
				}

			}
			
			if($export == 1){ // ---- Hoja de calculo ----
				
			}
			else{ // ---- HTML ----
				
				echo '
							<td>';
				
			}
			
			if($export == 1){ // ---- Hoja de calculo ----
				
				foreach($ima as $k => $v) {
					
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($e, $v[1]);
				}
				
			}
			else{ // ---- HTML ----
				
				foreach($ima as $k => $v) {
				
					echo $v[1] . ' <img src="' . $v[0] . '" alt="" height="16" > ';
				}
				
			}
			
			if($export == 1){ // ---- Hoja de calculo ----
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($f, constant('ORDEN_ESTATUS_'.$ord['orden_estatus']));
			}
			else{ // ---- HTML ----
				
				echo '
							</td>
							<td>' . constant('ORDEN_ESTATUS_'.$ord['orden_estatus']) . ' </td>
							<td>';
			}
			
			$preg2 = "SELECT * FROM " . $dbpfx . "comentarios WHERE orden_id = '" . $ord['orden_id'] . "' AND interno = '2' ";
			$preg2 .= "ORDER BY bit_id DESC LIMIT 1";
			//echo $preg2;
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Comentario!");
			$fila2 = mysql_num_rows($matr2);
			
			if($fila2 > 0) {
				
				$com = mysql_fetch_assoc($matr2);
				
				if($export == 1){ // ---- Hoja de calculo ----
					
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($g, $usuario[$com['usuario']] . ' - ' . $com['comentario']);
				}
				else{ // ---- HTML ----
				
					echo '<strong>' . $usuario[$com['usuario']] . ':</strong> ' . $com['comentario'];
				}
				
			}
			
			if($export == 1){ // ---- Hoja de calculo ----
			
			}
			else{ // ---- HTML ----
			
				echo '
							</td>';
			}
			
			
			$tent = intval(strtotime($ord['orden_fecha_de_entrega']));
			$tprom = intval(strtotime($ord['orden_fecha_promesa_de_entrega']));
			$fondodemora = '';
			if ($tprom > 1000000) {
				$tdemora = intval(($tent - $tprom) / 86400);
				if($tdemora <= '0') {
					$aat++;
					$fondodemora = 'area7';
				} else {
					$adm++;
				}
				$fpe = date('Y-m-d', $tprom);
			} else {
				
				if($export == 1){ // ---- Hoja de calculo ----
					$fpe = "SIN Fecha";
				}
				else{ // ---- HTML ----
					$fpe = $lang['Sin Fecha'];
					$tdemora = 'N/A';
				}
			}
			
			if($export == 1){ // ---- Hoja de calculo ----
			
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($h, date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])))
							->setCellValue($i, date('Y-m-d', $tent))
							->setCellValue($j, $dias)
							->setCellValue($kkk, $fpe)
							->setCellValue($l, $tdemora);
				
				$z++;
			}
			else{ // ---- HTML ----
				
				echo '
							<td style="padding-left:2px; padding-right:2px;">
								' . date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])) . '
							</td>
							<td style="padding-left:2px; padding-right:2px;">
								' . date('Y-m-d', $tent) . '
							</td>
							<td align="center">
								'. $dias .'
							</td>
							<td style="padding-left:2px; padding-right:2px;">
								' . $fpe . '
							</td>
							<td class="' . $fondodemora . '" style="text-align:center;">
								' . $tdemora . '
							</td>';
				/*
				echo '
							<td align="center">
								' . $aat . '
							</td>
							<td align="center">
								' . round((($aat/$atot) * 100), 2) . '%
							</td>
							<td align="center">
								' . $adm . '
							</td>
							<td align="center">
								' . round((($adm/$atot) * 100), 2) . '%
							</td>';
				// -------------------------- Pago de comisión -----------------------------
				//echo '
							<td align="center">
								<input type="checkbox" name="pagar[]" value="1|' . $ord['orden_id'] . '|' . $ord['orden_fecha_de_entrega'] . '" /><input type="hidden" name="feini" value="' . $feini . '" /><input type="hidden" name="fefin" value="' . $fefin . '" />
							</td>';
			
				*/
				echo '
						</tr>'."\n";
				if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
				
			}
		}
	}
	
	
	if($export == 1){ // ---- Hoja de calculo ----
	
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="vehiculos-entregados.xls"');
		header('Cache-Control: max-age=0');


		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
		
	}
	else{ // ---- HTML ----
	
		$atot = $aat + $adm;
	
		echo '				
						<tr>
							<td colspan="6"></td>
							<td colspan="1" style="text-align:right; font-weight:bold; font-size:1.2em; background-color: #33ff33;">
								' . $aat . ' Vehículos entregados SIN demora: ' . round((($aat/$atot) * 100), 2) . '%
							</td>
							<td colspan="6" style="text-align:right; font-weight:bold; font-size:1.2em; color:white; background-color: #ff3333;">
								' . $adm . ' Vehículos entregados DEMORADOS: ' . round((($adm/$atot) * 100), 2) . '%
							</td>
						</tr>'."\n";
		
		if($resumen_ing_egre == 1){
			echo '
						<tr>
							<td colspan="3" style="vertical-align: top;">
								<h2>Total Ordenes: <big><b>' . $filas_ord . '</big></h2>
							<td colspan="3" style="text-align:right; vertical-align: top; font-size:1.2em;">
								<table>
									<tr>
										<td colspan="3"><b><small>Siniestros y clientes registrados:</small></b></td>
									</tr>
								'."\n";

			// --- imprimir valores finales de aseguradoras y particular ---
			foreach($ase as $key => $val){

				if($cont_aseg[$key] == 0){
					$valor = 0;
					$style = 'style="color:white; background-color: #ff3333;"';
				}else{
					$valor = $cont_aseg[$key];
					$style = 'style="background-color: #33ff33"';
				}

				echo '
									<tr>
										<td><b><small>' . $ase[$key][1] . ':</small></b></td>
										<td></td>
										<td ' . $style . '><b>' . $valor . ' </b></small></td>
									</tr>'."\n";
			}


			echo '					</table>
							</td>
							<td colspan="1" style="text-align:right; vertical-align: top; font-size:1.2em;">
								<table>
									<tr>
										<td colspan="3"><small><b>Ingresos por Asesor:</b></small></td>
									</tr>'."\n";
			// --- imprimir total por asesor ---
			foreach($asesor_cont as $key => $val){

				echo '
									<tr>
										<td><small>' . $usuario[$key] . '</small></td>
										<td></td>
										<td><b>' . $val . ' </b></small></td>
									</tr>'."\n";
			}

			echo '
								</table>
							</td>'."\n";

			echo '
						<td colspan="2" style="text-align:right; vertical-align: top; font-size:1.2em;">
							<table>
								<tr>
									<td colspan="1"><small><b>Grúa:</b></small></td>
								</tr>
								<tr>
									<td><small>Ingresos en Grúa</small></td>
									<td></td>
									<td style="text-align:right;"><b>' . $grua_si . ' </b></td>
								</tr>
								<tr>
									<td><small>Ingresos sin Grúa</small></td>
									<td></td>
									<td style="text-align:right;"><b>' . $grua_no . ' </b></td>
								</tr>
								<tr>
									<td><small>Ingresos sin definir</small></td>
									<td></td>
									<td style="text-align:right;"><b>' . $grua_no_def . ' </b></td>
								</tr>
							</table>
						</td>
						<td colspan="2" style="text-align:right; vertical-align: top; font-size:1.2em;">
							<table>
								<tr>
									<td colspan="3"><small><b>Tipos de Ingreso:</b></small></td>
								</tr>'."\n";
			
			// --- recorrer los tipos de servicio de comun.php ---
			$count = 0;
			while($count <= $num_tipos){
				
				echo '
								<tr>'."\n";
				
				if($tipo_serv[$count] == ''){
					$tipo_serv[$count] = 0;
					$style = 'style="color:white; background-color: #ff3333;"';
				}else{
					$style = 'style="background-color: #33ff33"';
				}
				
				if($count == 0){
					
					echo '		
									<td>Canceladas</td>
									<td></td>
									<td ' . $style . '><b>' . $tipo_serv[$count] . '<b></td>';
					
				} else{
					echo '
									<td>' . constant('ORDEN_SERVICIO_'. $count) . '</td>
									<td></td>
									<td ' . $style . '><b>' . $tipo_serv[$count] . "<b></td>";
				
				}
				$count++;
				
				echo '
								</tr>'."\n";
				
			}


			echo '
								
							</table>
						</td>
					</tr>'."\n";
		}
	
		
		echo '			
				</table>
				</form>'."\n";
	}
		

} else {
		echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
}
    


?>