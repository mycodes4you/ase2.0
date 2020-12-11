<?php

$pdf->addSociete($emisor_nombre,"RFC Emisor: " . $Emisor->getAttribute("rfc")."\n" . $df_calle ." #". $df_numext ." - ". $df_numint ."\n" . $df_colonia . ", " . $df_municipio . ".\n" . $df_cp . " " . $df_estado . ".\n" . $regimen . ".\nTipo de comprobante: " . $tipocomprobante . ".\nFecha de Emisión: " . $Comprobante->getAttribute("fecha") . "\nLugar de Emisión: " . $comprobante_lexp . ".\nNo de Serie CSD: " . $Comprobante->getAttribute("noCertificado") . ".\nFolio Fiscal: " . $Timbre->getAttribute("UUID"));

$pdf->fact_dev( $nomcomprobante, $factura );

$pdf->fact_dev( $nomcomprobante, $factura );

$pdf->addClientAdresse("RECEPTOR\n" . $receptor_nombre . "\n" . "RFC Receptor: " . $receptor_rfc . "\n" . $receptor_calle . " #" . $receptor_numext . " - " . $receptor_numint . "\n" . $receptor_colonia . ", " . $receptor_municipio . ".\n" . $receptor_cp . " " . $receptor_estado . ".");

$qrnom = explode('.', $axml); 

$pdf->Image(DIR_DOCS . $qrnom[0] . '.png', 175, 50, 26, 26, 'PNG');

$pdf->Image('particular/logo-agencia.png', 155, 16, 'PNG');

$cols=array( "Cantidad"    => 18,
             "Descripción"  => 94,
             "Unidad"     => 22,
             "Precio Unitario"      => 26,
             "Subtotal" => 30 );
$pdf->addCols( $cols);
$cols=array( "Cantidad"    => "C",
             "Descripción"  => "L",
             "Unidad"     => "L",
             "Precio Unitario"      => "R",
             "Subtotal" => "R" );
$pdf->addLineFormat( $cols);
$pdf->addLineFormat($cols);

$y    = 90;

	$conceptos = $xml->getElementsByTagName('Concepto');
	foreach($conceptos as $concepto)
		{
			$line = array( 
				"Cantidad"    => $concepto->getAttribute("cantidad"),
				"Descripción"  => utf8_decode($concepto->getAttribute("descripcion")),
				"Unidad"     => utf8_decode($concepto->getAttribute("unidad")));
			$unitario = number_format(floatval($concepto->getAttribute("valorUnitario")), 2, '.', ',');
			$importe = number_format(floatval($concepto->getAttribute("importe")), 2, '.', ',');
			$line['Precio Unitario'] = $unitario;
			$line['Subtotal'] = $importe;
			$size = $pdf->addLine( $y, $line );
			$y += $size + 2;
			if($y > 270) { 
				$pdf->AddPage();
				$y = 6; 
			}
		}

	if($y > 250) { 
		$pdf->AddPage();
		$y = 12; 
	}
	
$pdf->Line( 10, $y-2, 200, $y-2);

$postot = $y;

$descuento = $Comprobante->getAttribute("descuento");
$subtotal = $Comprobante->getAttribute("subTotal");
$nuevosub = (floatval($subtotal) - floatval($descuento)); 
$iva = $Impuestos->getAttribute("totalImpuestosTrasladados");
$total = $Comprobante->getAttribute("total");

$descuento = number_format(floatval($descuento), 2, '.', ',');
$subtotal = number_format(floatval($subtotal), 2, '.', ',');
$nuevosub = number_format(floatval($nuevosub), 2, '.', ',');
$iva = number_format(floatval($iva), 2, '.', ',');
$total = number_format(floatval($total), 2, '.', ',');
$pdf->addTotales($subtotal, $descuento, $nuevosub, $iva, $total, $postot);

include('parciales/numeros-a-letras.php');
$letra = strtoupper(letras2($Comprobante->getAttribute("total")));
$pdf->addConletra($letra, $y);
$y = $y + 4;

$pdf->addFormaPago(utf8_decode($Comprobante->getAttribute("formaDePago")), $y);
$y = $y + 4;

foreach($dmp as $jj) {
	$pdf->addMetodoPago($jj . ' ' . utf8_decode($metodossat[$jj]), $y);
	$y = $y + 4;
}

$pdf->addCuentaPago($Comprobante->getAttribute("NumCtaPago"), $y);
$y = $y + 4;

$pdf->addCondicionesPago(utf8_decode($Comprobante->getAttribute("condicionesDePago")), $y);

$y = $y + 4;
$motdesc = $Comprobante->getAttribute("motivoDescuento");
if($motdesc != '') {
	$pdf->addMotivo($motdesc, $y);	
}

if($y > 260) { 
	$pdf->AddPage();
	$y = 12;
}

$preg1 = "SELECT a.omite_datos_pdf FROM " . $dbpfx . "subordenes s, " . $dbpfx . "aseguradoras a WHERE s.orden_id = '$orden_id' AND s.sub_reporte = '$reporte' AND a.aseguradora_id = s.sub_aseguradora AND s.sub_estatus < '190'";
$matr1 = mysql_query($preg1) or die("ERROR: Fallo selecciÃ³n de aseguradora! " . $preg1);
$asegu = mysql_fetch_array($matr1);

if($asegu['omite_datos_pdf'] != '1') {
	if($reporte != '' && $reporte != '0') {
		$datos_veh = 'POLIZA: ' . $poliza . '. SINIESTRO: ' . $reporte . "\n" . 'CLIENTE: ' . utf8_decode($clie['cliente_nombre']) . ' ' . utf8_decode($clie['cliente_apellidos']) . "\n";
	}
	$datos_veh .= 'VEHICULO : ' . $veh['marca'] . ' ' . $veh['tipo'] . ' ' . $veh['modelo'] . ' PLACAS: ' . $veh['placas'] . '. VIN: ' . $veh['serie'] . "\n";
}

if($obsad != '') {
	$datos_veh .= 'Observaciones: ' . utf8_decode($obsad) . "\n";
}

$y = $y + 8; 
$pdf->addVehDatos($datos_veh, $y);

$y =$y + 16;
if($y > 260) { 
	$pdf->AddPage();
	$y = 12;
}

$pdf->addSello($Comprobante->getAttribute("sello"), $y);

$y =$y + 16;
if($y > 260) { 
	$pdf->AddPage();
	$y = 12;
}

$pdf->addSelloSat($Timbre->getAttribute("selloSAT"), $y);

$cadena_sat = '||' . $Timbre->getAttribute("version") . '|' . $Timbre->getAttribute("UUID") . '|' . $Timbre->getAttribute("FechaTimbrado") . '|' . $Comprobante->getAttribute("sello") . '|' . $Timbre->getAttribute("noCertificadoSAT") . '||';

$y =$y + 16;
if($y > 260) { 
	$pdf->AddPage();
	$y = 12;
}

if($prueba != '') {
	$pdf->temporaire($prueba);
}

$pdf->addCadenaSat($Timbre->getAttribute("version"), $Timbre->getAttribute("UUID"), $Timbre->getAttribute("FechaTimbrado"), $Comprobante->getAttribute("sello"), $Timbre->getAttribute("noCertificadoSAT"), $y);


?>