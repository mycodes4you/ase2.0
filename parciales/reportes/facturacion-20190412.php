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
					->setCellValue("H4", "Fecha de Cobro")
					->setCellValue("I4", "RPE")
					->setCellValue("J4", "Nota de Crédito");
		$z = 5;

	}
	else{ // ---- HTML ----

		echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-12">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2>' . $filas . $encabezado . '</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 ">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
					<tr>
						<th><big>Factura</big></th>
						<th><big>OT</big></th>
						<th><big>Cliente</big></th>
						<th><big>Monto Total</big></th>
						<th><big>Monto Cobrado</big></th>
						<th><big>Monto Pendiente</big></th>
						<th><big>Fecha Emitida</big></th>
						<th><big>Fecha de Cobro</big></th>
						<th><big>RPE</big></th>
					</tr>
					<tr class="claro">
						<td colspan="9">
							<form action="reportes.php?accion=facturacion" method="post" enctype="multipart/form-data">
						</td>
					</tr>
					<tr class="claro">
						<td>
							<input class="form-control" placeholder="Factura" type="text" name="factura" size="4" >
						</td>
						<td>
							<input class="form-control" placeholder="OT" type="text" name="ot" size="4" >
						</td>
						<td>
							<input class="form-control" placeholder="Cliente" type="text" name="cliente" size="12" >
						</td>
						<td>
							<input class="form-control" placeholder="Monto" type="text" name="fact_monto" size="4" >
						</td>
						<td>
							<input class="btn btn-success" class="form-control" type="submit" value="Enviar" />
							</form>
						</td>
						<td colspan="4">
						</td>
					</tr>'."\n";
	}

	$fondo = 'claro';
	$totfact = 0; $totcob = 0;
	while($fact = mysql_fetch_array($matr0)) {

		// ---- consultar RPE (recibos de pago electronicos) ----
		$preg_cobros = "SELECT cobro_id FROM " . $dbpfx . "cobros_facturas WHERE fact_id = '" . $fact['fact_id'] . "' ";
		$matr_cobros = mysql_query($preg_cobros) or die("ERROR: Fallo selección de cobros!");
		$filas_cobros = mysql_num_rows($matr_cobros);

		if($filas_cobros > 0){ // --- se procede con la búsqueda de RPE ---

			while($cobros = mysql_fetch_array($matr_cobros)){

				// --- Consultar si el cobro tiene un rpe asociado ---
				$preg_rpe = "SELECT rpe_id FROM " . $dbpfx . "cobros WHERE rpe_id != '' AND cobro_id = '" . $cobros['cobro_id'] . "'";
				$matr_rpe = mysql_query($preg_rpe) or die("ERROR: Fallo selección de rpe!");
				$filas_rpe = mysql_num_rows($matr_rpe);

				if($filas_rpe > 0){ // --- se consultan las facturas para construir Links ---

					$rpe = '';
					while($con_rpe = mysql_fetch_array($matr_rpe)){

						// --- Consultar en la tabla facturas para construir links ---
						$preg_doc = "SELECT fact_serie, fact_num, fact_uuid FROM " . $dbpfx . "facturas WHERE fact_id = '" . $con_rpe['rpe_id'] . "'";
						$matr_doc = mysql_query($preg_doc) or die("ERROR: Fallo selección de factura!");
						$doc = mysql_fetch_assoc($matr_doc);

						if($export == 1) { // ---- Hoja de calculo ----
							$rpe .= ' ' . $doc['fact_serie'] . $doc['fact_num'];
						} else { // ---- HTML ----
							$link_xml = '<small> <a href="documentos/' . $doc['fact_serie'] . $doc['fact_num'] . '-' . $doc['fact_uuid'] . '.xml" target="_blank">' . $doc['fact_serie'] . $doc['fact_num'] . ' xml</a> </small>';
							$link_pdf = '<small> <a href="documentos/' . $doc['fact_serie'] . $doc['fact_num'] . '-' . $doc['fact_uuid'] . '.pdf" target="_blank">' . $doc['fact_serie'] . $doc['fact_num'] . ' pdf</a> </small>';
							$rpe .= ' ' . $link_pdf . ' ' . $link_xml;
						}
					}

				} else{ // --- Los cobros no tiene RPR ---
					$rpe = '---';
				}

			}

		} else{ // --- No hay cobros ---
			$rpe = '---';
		}


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

		$preg2 = "SELECT monto, cobro_id FROM " . $dbpfx . "cobros_facturas WHERE fact_id = '" . $fact['fact_id'] . "'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cobros " . $preg2);
		$fila2 = mysql_num_rows($matr2);
		$cobrado = 0;
		$nota_cre = '';
		while ($cob = mysql_fetch_array($matr2)) {
			// --- Consultar si el cobro pertenece a una nota de crédito ---
			$preg_nota_cre = "SELECT cobro_tipo FROM " . $dbpfx . "cobros WHERE cobro_id = '" . $cob['cobro_id'] . "'";
			$matr_nota_cre = mysql_query($preg_nota_cre) or die("ERROR: " . $preg_nota_cre);
			$cobro_tipo = mysql_fetch_assoc($matr_nota_cre);
			if($cobro_tipo['cobro_tipo'] == 4){ // --- Pertenece a una nota de crédito --
				$nota_cre = '<a href="entrega.php?accion=cobros&orden_id=' . $fact['orden_id'] . '">Nota de Crédito</a>';
			} else { // se suma a los montos cobrados ---
				$cobrado = $cobrado + $cob['monto'];
			}
		}
		$porcobrar = $fact['fact_monto'] - $cobrado;

		if($export == 1){ // ---- Hoja de calculo ----

			// --- Celdas a grabar ----
			$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
			$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;


			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($a, $fact['fact_num'])
						->setCellValue($b, $fact['orden_id'])
						->setCellValue($c, $clie);

		}
		else{ // ---- HTML ----

			echo '
					<tr class="' . $fondo . '">
						<td>
							<big>' . $fact['fact_num'] . '</big>
						</td>
						<td>
							<big><a href="ordenes.php?accion=consultar&orden_id=' . $fact['orden_id'] . '">' . $fact['orden_id'] . '</a></big>
						</td>
						<td style="text-align: left !important;">
							<big>' . $clie . '</big>
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
							<big>$' . number_format($fact['fact_monto'], 2) . '</big>
						</td>
						<td style="text-align:right;">
							<big>';
			}

		}

		if($fact['fact_cobrada'] < 2) {

			if($export == 1){ // ---- Hoja de calculo ----
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($e, $cobrado);
			}
			else{ // ---- HTML ----
				if($nota_cre != ''){
					echo $nota_cre . ' ';
				}
				echo '$' . number_format($cobrado, 2);
			}

		} else{

			if($export == 1){ // ---- Hoja de calculo ----
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($e, 'Cancelada');
			}
			else{ // ---- HTML ----
				echo '<big>Cancelada</big>';
			}

		}

		if($export == 1){ // ---- Hoja de calculo ----

		}
		else{ // ---- HTML ----

			echo '
							</big>
						</td>
						<td style="text-align:right;">
							<big>';
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

			$fecha_emision = date('d-m-Y 00:00:01', strtotime($fact['fact_fecha_emision']));
			$fecha_emision = PHPExcel_Shared_Date::PHPToExcel( strtotime($fecha_emision) );

			$contenido = '';
			if($nota_cre != ''){
				$contenido = 'Si';
			}

			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($g, $fecha_emision)
						->setCellValue($h, $fcobrada)
						->setCellValue($i, $rpe)
						->setCellValue($j, $contenido);

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
							<big>
						</td>
						<td>
							<big>' . date('d-m-Y', strtotime($fact['fact_fecha_emision'])) . '</big>
						</td>
						<td>
							<big>' . $fcobrada . '</big>
						</td>
						<td>
							<big>' . $rpe . '</big>
						</td>
					</tr>'."\n";

			if($fact['fact_cobrada'] < 2) {
				$totfact = $totfact + $fact['fact_monto'];
				$totcob = $totcob + $cobrado;
				$totpc = $totpc + $porcobrar;
			}

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
							<big><b>Totales</b></big>
						</td>
						<td style="text-align:right;">
							<big><b>$' . number_format($totfact, 2) . '</b></big>
							</td>
						<td style="text-align:right;">
							<big><b>$' . number_format($totcob, 2) . '</b></big>
						</td>
						<td style="text-align:right;">
							<big><b>$' . number_format($totpc, 2) . '</b></big>
						</td>
						<td colspan="2"></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>'."\n";

	}

} else{
	echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
}
