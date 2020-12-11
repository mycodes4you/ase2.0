<?php

// ------ Tipo de CFDi, folio y serie
$factura = $Comprobante->getAttribute("Serie") . $Comprobante->getAttribute("Folio");
$nomcomprobante = 'Recibo de Pagos'; $tipo = utf8_decode('Recibo Electrónico de Pagos');

// -- datos del emisor ---
$adresse = "RFC Emisor: " . $emisor_rfc . "\n" . utf8_decode('Régimen Fiscal: ') . $emisor_regimen . ' ' . utf8_decode($nomregimen[$emisor_regimen]) . "\n" . 'UUID: ' . $Timbre->getAttribute("UUID") . "\n" . 'No. Certificado: ' . $comprobante_nocert . "\n" . utf8_decode('Lugar de Expedición: ') . $comprobante_lexp . "\n" . utf8_decode('Fecha: ') . $comprobante_fecha . "\n" . utf8_decode('Tipo de Comprobante: ') . $tipo . "\n" . utf8_decode('Versión CFDi: 3.3');

$pdf->addSociete(utf8_decode('Emisor: ' . $emisor_nombre . "\n") , $adresse);
$pdf->fact_dev(utf8_decode($nomcomprobante), $factura );

// ------ Tipo de comprobante ---
// $pdf->addPageNumber(utf8_decode($tipo));

// ------ Fecha de expedición
// $pdf->addDate($comprobante_fecha);


// --- datos del receptor --- 
$receptor = "Receptor: " . $receptor_nombre . "\n" . "RFC Receptor: " . $receptor_rfc . "\n" . "Uso CFDI: " . $receptor_usocfdi . ' ' . utf8_decode($usosdecfdi[$receptor_usocfdi]);

$pdf->addClientAdresse($receptor);

// --- QR e info fiscal --- 
$qrnom = explode('.', $axml); // -- pendiente QR ---
$imagenqr = DIR_DOCS . $qrnom[0] . '.png';
$pdf->Image($imagenqr, 166, 26, 33, 33, 'PNG');

// ------ Agrega concepto Único ------
$cols=array( "Cant"    => 18,
             "ClaveProdServ" => 20,
             "ClaveUnidad" => 20,
             utf8_decode("Descripción")  => 50,
             "Precio Unitario"      => 22,
             "Importe" => 22 );
$pdf->addCols( $cols);
$cols=array( "Cant"    => "C",
             "ClaveProdServ"     => "L",
             "ClaveUnidad"     => "L",
             utf8_decode("Descripción")  => "L",
             "Precio Unitario"      => "R",
             "Importe" => "R" );
$pdf->addLineFormat( $cols);
$pdf->addLineFormat($cols);

$y = 87;

	$conceptos = $xml->getElementsByTagName('Concepto');
	foreach($conceptos as $concepto)
		{
			$line = array(
				"Cant"    => $concepto->getAttribute("Cantidad"),
				"ClaveProdServ" => utf8_decode($concepto->getAttribute("ClaveProdServ")),
				"ClaveUnidad" => utf8_decode($concepto->getAttribute("ClaveUnidad")) . " " . utf8_decode($concepto->getAttribute("Unidad")), 
				utf8_decode("Descripción")  => utf8_decode($concepto->getAttribute("Descripcion")));
			$unitario = number_format(floatval($concepto->getAttribute("ValorUnitario")), 2, '.', ',');
			$importe = number_format(floatval($concepto->getAttribute("Importe")), 2, '.', ',');
			$line['Precio Unitario'] = $unitario;
			$line['Importe'] = $importe;
			$line['Descuento'] = number_format(floatval($concepto->getAttribute("Descuento")), 2, '.', ',');
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

// ------ Agrega encabezado de tabla y cada uno de los Pagos del Comprobante ------
$y = $y + 4;
$cols=array( "UUID" => 55,
             "Serie y Folio" => 15,
             utf8_decode("Método") => 15,
             "Saldo Anterior" => 20,
             "Saldo Pendiente" => 20,
             "Monto Pagado" => 25);
$pdf->addRelacionados( $cols);
$cols=array( "UUID" => "C",
             "Serie y Folio" => "C",
             utf8_decode("Método") => "C",
             "Saldo Anterior" => "C",
             "Saldo Pendiente" => "C",
             "Monto Pagado" => "C");
$pdf->addLineFormat( $cols);
$pdf->addLineFormat($cols);

$y = $y + 10;

	$totalcobrado = 0;
	$doctos = $xml->getElementsByTagName('DoctoRelacionado');
	foreach($doctos as $docto)
		{
			$line = array(
				"UUID" => $docto->getAttribute("IdDocumento"),
				"Serie y Folio" => $docto->getAttribute("Serie") . $docto->getAttribute("Folio"),
				utf8_decode("Método") => $docto->getAttribute("MetodoDePagoDR")
			);
			$line['Saldo Anterior'] = number_format(floatval($docto->getAttribute("ImpSaldoAnt")), 2, '.', ',');
			$line['Saldo Pendiente'] = number_format(floatval($docto->getAttribute("ImpSaldoInsoluto")), 2, '.', ',');
			$line['Monto Pagado'] = number_format(floatval($docto->getAttribute("ImpPagado")), 2, '.', ',');
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

$montopago = $Pago->getAttribute("Monto");
include('parciales/numeros-a-letras.php');
$letra = strtoupper(letras2($montopago));

$TotalPago = "Total Pagado: $" . number_format($montopago, 2) . " Total con letra: " . $letra;
$FormaPago = $Pago->getAttribute("FormaDePagoP") . ' ' . utf8_decode($metodossat[$Pago->getAttribute("FormaDePagoP")]);
$TerceraLinea = "Fecha de Pago: " . $Pago->getAttribute("FechaPago") . ". Banco: " . utf8_decode($Pago->getAttribute("NomBancoOrdExt")) . " " . utf8_decode("Número de Operación: ") . $Pago->getAttribute("NumOperacion");

$pdf->addConletra($TotalPago, $y);
$y = $y + 4;

$pdf->addFormaPago($FormaPago, $y);
$y = $y + 4;

$pdf->addMetodoPago($TerceraLinea, $y);
$y = $y + 4;

if($y > 260) { 
	$pdf->AddPage();
	$y = 12;
}

$y =$y + 4;
if($y > 260) { 
	$pdf->AddPage();
	$y = 12;
}

$pdf->addSello($Comprobante->getAttribute("Sello"), $y);

$y =$y + 16;
if($y > 260) { 
	$pdf->AddPage();
	$y = 12;
}

$pdf->addSelloSat($Timbre->getAttribute("SelloSAT"), $y);

$cadena_sat = '||' . $Timbre->getAttribute("version") . '|' . $Timbre->getAttribute("UUID") . '|' . $Timbre->getAttribute("FechaTimbrado") . '|' . $Comprobante->getAttribute("sello") . '|' . $Timbre->getAttribute("noCertificadoSAT") . '||';

$y =$y + 16;
if($y > 250) { 
	$pdf->AddPage();
	$y = 12;
}

$pdf->addCadenaSat($Timbre->getAttribute("Version"), $Timbre->getAttribute("UUID"), $Timbre->getAttribute("FechaTimbrado"), $Timbre->getAttribute("RfcProvCertif"), $Comprobante->getAttribute("Sello"), $Timbre->getAttribute("NoCertificadoSAT"), $y);

if($prueba != '') {
	$pdf->temporaire($prueba);
}


?>