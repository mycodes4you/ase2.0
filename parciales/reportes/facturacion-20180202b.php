<?php
/*************************************************************************************
*   Script de "reporte de Facturación"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/


if ($f1125100 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1') {

	if($cliente != '') {
		
		$pregase = "SELECT aseguradora_id FROM " . $dbpfx . "aseguradoras WHERE aseguradora_razon_social LIKE '%$cliente%' LIMIT 1";
		$matrase = mysql_query($pregase) or die("ERROR: Fallo selección de lapso!");
		$asenm = mysql_fetch_array($matrase);
		$filaase = mysql_num_rows($matrase);
		
		if($filaase > 0) {
			
			$aseguradora_id = $asenm['aseguradora_id'];
			
		} else {
			
			$pregcli = "SELECT c.cliente_id, e.empresa_razon_social FROM " . $dbpfx . "clientes c, " . $dbpfx . "empresas e WHERE e.empresa_razon_social LIKE '%$cliente%' AND e.empresa_id = c.cliente_empresa_id LIMIT 1";
			$matrcli = mysql_query($pregcli) or die("ERROR: Fallo selección de lapso!");
			$clinm = mysql_fetch_array($matrcli);
			$filacli = mysql_num_rows($matrcli);
			if($filacli > 0) {
				$fltcli = $clinm['cliente_id'];
				$empresa = $clinm['empresa_razon_social'];
				
			}
		}
	}

	$preg0 = "SELECT fact_id, orden_id, reporte, cliente_id, aseguradora_id, fact_rfc, fact_num, fact_fecha_emision, fact_tipo, fact_monto, fact_cobrada, fact_fecha_cobrada, usuario FROM " . $dbpfx . "facturas_por_cobrar WHERE (fact_tipo < '3' OR fact_tipo = '4') ";

	$encabezado = ' Documentos de cobro: ';
	
	if($fact_tipo == 1 || $fact_tipo == 2 || $fact_tipo == 3){
		
		if($fact_tipo == 1){
			
			$preg0 .= " AND fact_tipo = '1'";
			$encabezado .= 'facturas ';
			
		}
		if($fact_tipo == 2){
			
			$preg0 .= " AND fact_tipo = '2'";
			$encabezado .= 'remisiones ';
			
		}
		if($fact_tipo == 3){
			
			$preg0 .= " AND fact_tipo = '3'";
			$encabezado .= 'deducibles ';
			
		}
		
		$preg0 .= " AND fact_fecha_emision > '" . $feini . "' AND fact_fecha_emision < '" . $fefin . "'";
			$encabezado .= ' emitidas del ' . $t_ini . ' al ' . $t_fin;
			$encabecola = '';
		
		
	} else{
		
		if($aseguradora_id != '') {
			
			$preg0 .= " AND aseguradora_id = '$aseguradora_id'";
			$encabecola = ' para ' . $ase[$aseguradora_id][2];
			
		} elseif($factura != '') {
			
			$preg0 .= " AND fact_num LIKE '%$factura%'";
			$encabezado .= $factura;
			
		} elseif($ot != '') {
			
			$preg0 .= " AND orden_id = '$ot'";
			$encabezado .= ' de la OT ' . $ot;
			
		} elseif($fltcli != '') {
			
			$preg0 .= " AND cliente_id = '$fltcli'";
			$encabecola = ' del Cliente ' . $empresa;
			
		} elseif($fact_monto != '') {

			$preg0 .= " AND fact_monto = '$fact_monto'";
			$encabezado .= ' con un monto de ' . $fact_monto;
			
		} else {
			
			$preg0 .= " AND fact_fecha_emision > '" . $feini . "' AND fact_fecha_emision < '" . $fefin . "'";
			$encabezado .= ' emitidas del ' . $t_ini . ' al ' . $t_fin;
			$encabecola = '';
			
		}
	}
	

	$encabezado = $encabezado . $encabecola;

	$preg0 .= " ORDER BY fact_id DESC";
	//echo $preg0;

	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de lapso!");
	$filas = mysql_num_rows($matr0);

	if($export == 1){ // ---- Hoja de calculo ----
            
		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'FACTURACIÓN: ' . $nombre_agencia;
	
		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("FACTURACIÓN")
					->setKeywords("AUTOSHOP EASY");

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($celda, $titulo)
					->setCellValue("A3", $fecha_export);

		// ------ ENCABEZADOS ---
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A4", "Factura")
					->setCellValue("B4", "OT")
					->setCellValue("C4", "Cliente")
					->setCellValue("D4", "Monto Total")
					->setCellValue("E4", "Monto Cobrado")
					->setCellValue("F4", "Monto Pendiente")
					->setCellValue("G4", "Fecha Emitida")
					->setCellValue("H4", "Fecha de Cobro");
		$z = 5;
            
	}
	else{ // ---- HTML ----
		
		echo '			
		<table cellspacing="1" cellpadding="2" border="0">
			<tr class="cabeza_tabla">
				<td colspan="18" style="text-align: right;">
					' . $filas . $encabezado . '
				</td>
			</tr>
			<tr>
				<td>Factura</td>
				<td>OT</td>
				<td>Cliente</td>
				<td>Monto Total</td>
				<td>Monto Cobrado</td>
				<td>Monto Pendiente</td>
				<td>Fecha Emitida</td>
				<td>Fecha de Cobro</td>
			</tr>
			<tr>
				<td colspan="8">
					<form action="reportes.php?accion=facturacion" method="post" enctype="multipart/form-data">
				</td>
			</tr>
			<tr>
				<td>
					<input type="text" name="factura" size="4" >
				</td>
				<td>
					<input type="text" name="ot" size="4" >
				</td>
				<td>
					<input type="text" name="cliente" size="12" >
				</td>
				<td>
					<input type="text" name="fact_monto" size="4" >
				</td>
				<td>
					<input type="submit" value="Enviar" />
					</form>
				</td>
				<td colspan="3">
				</td>
			</tr>'."\n";
	}

	$fondo = 'claro';
	$totfact = 0; $totcob = 0;
	while($fact = mysql_fetch_array($matr0)) {
		
		if($fact['fact_fecha_cobrada'] != '' && $fact['fact_fecha_cobrada'] != NULL){
			
			if($export == 1){ // ---- Hoja de calculo ----
				$fcobrada = PHPExcel_Shared_Date::PHPToExcel( strtotime($fact['fact_fecha_cobrada']) );
			}
			else{ // ---- HTML ----
				$fcobrada = date('d-m-Y', strtotime($fact['fact_fecha_cobrada']));
			}
			
		} else { 
			$fcobrada = ''; 
		}
		
		if($fact['aseguradora_id'] >= '1') {
			$clie = $ase[$fact['aseguradora_id']][2];
		} else {
			$preg1 = "SELECT e.empresa_razon_social, c.cliente_nombre, c.cliente_apellidos FROM " . $dbpfx . "empresas e, " . $dbpfx . "clientes c WHERE c.cliente_id = '" . $fact['cliente_id'] . "' AND e.empresa_id = c.cliente_empresa_id";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de empresa!");
			$emp = mysql_fetch_array($matr1);
			if($emp['empresa_razon_social'] == ""){
				$clie = $emp['cliente_nombre'] . " " . $emp['cliente_apellidos'];
			} else {
				$clie = $emp['empresa_razon_social'];
			}
		}
		
		$preg2 = "SELECT monto FROM " . $dbpfx . "cobros_facturas WHERE fact_id = '" . $fact['fact_id'] . "'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cobros " . $preg2);
		$fila2 = mysql_num_rows($matr2);
		$cobrado = 0;
		while ($cob = mysql_fetch_array($matr2)) {
			$cobrado = $cobrado + $cob['monto'];
		}
		$porcobrar = $fact['fact_monto'] - $cobrado;
		
		if($export == 1){ // ---- Hoja de calculo ----    
	
			// --- Celdas a grabar ----
			$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
			$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z;
				
				
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($a, $fact['fact_num'])
						->setCellValue($b, $fact['orden_id'])
						->setCellValue($c, $clie);
				
		}
		else{ // ---- HTML ----
		
			echo '				
			<tr class="' . $fondo . '">
					<td>
						' . $fact['fact_num'] . '
					</td>
					<td>
						<a href="ordenes.php?accion=consultar&orden_id=' . $fact['orden_id'] . '">' . $fact['orden_id'] . '</a>
					</td>
					<td>
						' . $clie . '
					</td>'."\n";
		}
		
		
		if($fact['fact_cobrada'] == 2) {
			$fact['fact_monto'] = 0;
			
			if($export == 1){ // ---- Hoja de calculo ----    
			
			}
			else{ // ---- HTML ----
				echo '		
					<td></td>
					<td style="text-align:right;">';
			}
			
		} else{
			
			if($export == 1){ // ---- Hoja de calculo ----    
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($d, $fact['fact_monto']);
			}
			else{ // ---- HTML ----
				echo '		
					<td style="text-align:right;">
						$' . number_format($fact['fact_monto'], 2) . '
					</td>
					<td style="text-align:right;">';
			}
			
		}
		
		if($fact['fact_cobrada'] < 2) {
			
			if($export == 1){ // ---- Hoja de calculo ----    
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($e, $cobrado);
			}
			else{ // ---- HTML ----
				echo '$' . number_format($cobrado, 2);
			}
			
		} else{
			
			if($export == 1){ // ---- Hoja de calculo ----    
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($e, 'Cancelada');
			}
			else{ // ---- HTML ----
				echo 'Cancelada';
			}
			
		}
		
		if($export == 1){ // ---- Hoja de calculo ----    

		}
		else{ // ---- HTML ----
		
			echo '
					</td>
					<td style="text-align:right;">';
		}
			
		if($fact['fact_cobrada'] < 2) {
			
			if($export == 1){ // ---- Hoja de calculo ----    
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($f, $porcobrar);
			}
			else{ // ---- HTML ----
				echo '$' . number_format($porcobrar, 2);
			}
			
		} else {
			
			if($export == 1){ // ---- Hoja de calculo ----   
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($f, 'Cancelada');
			}
			else{ // ---- HTML ----
				echo 'Cancelada';
			}
			
		}
		
		if($export == 1){ // ---- Hoja de calculo ----
			
			$fecha_emision = PHPExcel_Shared_Date::PHPToExcel( strtotime($fact['fact_fecha_emision']) );
			
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($g, $fecha_emision)
						->setCellValue($h, $fcobrada);
			
			// --- cambiar el formato de la celda tipo fecha/date ---
			$objPHPExcel->getActiveSheet()
    					->getStyle($g)
    					->getNumberFormat()
    					->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			
			if($fcobrada == ''){
				
			} else{
				$objPHPExcel->getActiveSheet()
    						->getStyle($h)
    						->getNumberFormat()
    						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			}
			
			
			$z++;
			
		}
		else{ // ---- HTML ----
			
			echo '
					</td>
					<td>
						' . date('d-m-Y', strtotime($fact['fact_fecha_emision'])) . '
					</td>
					<td>
						' . $fcobrada . '
					</td>
				</tr>'."\n";
			
			$totfact = $totfact + $fact['fact_monto'];
			$totcob = $totcob + $cobrado;
			$totpc = $totpc + $porcobrar;
			if($fondo == 'claro') { $fondo= 'obscuro';} else { $fondo = 'claro'; }
		}
		
		
	}
	
	if($export == 1){ // ---- Hoja de calculo ----
	
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="facturacion.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
		
	}
	else{ // ---- HTML ----
		echo '				
			<tr>
				<td colspan="3" style="text-align:right;">
					Totales
				</td>
				<td style="text-align:right;">
					$' . number_format($totfact, 2) . '
				</td>
				<td style="text-align:right;">
					$' . number_format($totcob, 2) . '
				</td>
				<td style="text-align:right;">
					$' . number_format($totpc, 2) . '
				</td>
				<td colspan="2"></td>
			</tr>
		</table>';
	}

} else{
	echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
}