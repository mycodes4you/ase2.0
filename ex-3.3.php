<?php
// (c) Xavier Nicolay
// Exemple de génération de devis/facture PDF

foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('parciales/metodos-de-pago-3.3.php');

$cfdi = file_get_contents(DIR_DOCS.$axml);

$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido");

// echo 'Lectura de elementos: <br>';
	
$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
$Emisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
$Receptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
$Impuestos = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Impuestos')->item(3);
$Timbre = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);

// --- extracción de valores ---
// --- nodo emisor ---
$emisor_nombre = utf8_decode($Emisor->getAttribute("Nombre"));
$emisor_rfc = utf8_decode($Emisor->getAttribute("Rfc"));
$emisor_regimen = utf8_decode($Emisor->getAttribute("RegimenFiscal"));

// -- nodo receptor --- 
$receptor_nombre = utf8_decode($Receptor->getAttribute("Nombre"));
$receptor_usocfdi = utf8_decode($Receptor->getAttribute("UsoCFDI"));
$receptor_rfc = utf8_decode($Receptor->getAttribute("Rfc"));

// --- nodo comprobante ---
$comprobante_serie = utf8_decode($Comprobante->getAttribute("Serie"));
$comprobante_folio = utf8_decode($Comprobante->getAttribute("Folio"));
$comprobante_fecha = utf8_decode($Comprobante->getAttribute("Fecha"));
$comprobante_f_pago = utf8_decode($Comprobante->getAttribute("FormaPago"));
$comprobante_moneda = utf8_decode($Comprobante->getAttribute("Moneda"));
$comprobante_subtotal = utf8_decode($Comprobante->getAttribute("SubTotal"));
$comprobante_total = utf8_decode($Comprobante->getAttribute("Total"));
$comprobante_tipoc = utf8_decode($Comprobante->getAttribute("TipoDeComprobante"));
$comprobante_metodo = utf8_decode($Comprobante->getAttribute("MetodoPago"));
$comprobante_lexp = utf8_decode($Comprobante->getAttribute("LugarExpedicion"));
$comprobante_desc = utf8_decode($Comprobante->getAttribute("Descuento"));
$comprobante_condpago = utf8_decode($Comprobante->getAttribute("CondicionesDePago"));
$comprobante_nocert = utf8_decode($Comprobante->getAttribute("NoCertificado"));


// echo 'Impuestos: '. $Impuestos->getAttribute("TotalImpuestosTrasladados");



// $dmp = explode(',',$metop);

// --- Extraer inforación del vehículo ---
if($orden_id != '') {
	$veh = datosVehiculo($orden_id, $dbpfx);
} else {
	$veh = datosVehiculo('', $dbpfx, $previa_id);
}

// --- extraer poliza ---
$preg = "SELECT sub_poliza FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '$reporte'";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion de póliza!");
while ($sub = mysql_fetch_array($matr)) {
	if ($sub['sub_poliza'] != '') { $poliza = $sub['sub_poliza']; } 
}

// --- Extraer información del cliente ---
$preg0 = "SELECT c.cliente_nombre, c.cliente_apellidos FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de poliza!");
$clie = mysql_fetch_array($matr0);

if(file_exists('particular/invoice-3.3.php')) {
	require('particular/invoice-3.3.php');
} else {
	require('parciales/invoice-3.3.php');
}

$pdf = new PDF_Invoice( 'P', 'mm', 'A4' );
$pdf->AliasNbPages();
$pdf->AddPage();

if(file_exists('particular/invoice-3.3.php')) {
	require('particular/fmt-fact-3.3.php');
} else {
	require('parciales/fmt-fact-3.3.php');
}

$pdf->Output(DIR_DOCS . $qrnom[0] . '.pdf', 'F');

if($enviacdfi == 1) {
	include('parciales/envia-cfdi.php');
} else {
	$pdf->Output();
}

?>
