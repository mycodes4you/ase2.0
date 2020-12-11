<?php
// (c) Xavier Nicolay
// Exemple de génération de devis/facture PDF

foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('parciales/metodos-de-pago.php');

$cfdi = file_get_contents(DIR_DOCS.$axml);

$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido");

$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
$Emisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
$Receptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
$DF = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'DomicilioFiscal')->item(0);
$Domicilio = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Domicilio')->item(0);
$Regimen = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'RegimenFiscal')->item(0);
$Impuestos = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Impuestos')->item(0);
$Timbre = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);
$regimen = utf8_decode($Regimen->getAttribute("Regimen"));
$emisor_nombre = utf8_decode($Emisor->getAttribute("nombre"));
$df_calle = utf8_decode($DF->getAttribute("calle"));
$df_numext = utf8_decode($DF->getAttribute("noExterior"));
$df_numint = utf8_decode($DF->getAttribute("noInterior"));
$df_colonia = utf8_decode($DF->getAttribute("colonia"));
$df_municipio = utf8_decode($DF->getAttribute("municipio"));
$df_cp = utf8_decode($DF->getAttribute("codigoPostal"));
$df_estado = utf8_decode($DF->getAttribute("estado"));
$receptor_nombre = utf8_decode($Receptor->getAttribute("nombre"));
$receptor_rfc = utf8_decode($Receptor->getAttribute("rfc"));
$receptor_calle = utf8_decode($Domicilio->getAttribute("calle"));
$receptor_numext = utf8_decode($Domicilio->getAttribute("noExterior"));
$receptor_numint = utf8_decode($Domicilio->getAttribute("noInterior"));
$receptor_colonia = utf8_decode($Domicilio->getAttribute("colonia"));
$receptor_municipio = utf8_decode($Domicilio->getAttribute("municipio"));
$receptor_cp = utf8_decode($Domicilio->getAttribute("codigoPostal"));
$receptor_estado = utf8_decode($Domicilio->getAttribute("estado"));
$comprobante_lexp = utf8_decode($Comprobante->getAttribute("LugarExpedicion"));
$tipocomprobante = utf8_decode($Comprobante->getAttribute("tipoDeComprobante"));
$metop = utf8_decode($Comprobante->getAttribute("metodoDePago"));
$dmp = explode(',',$metop);

if($orden_id != '') {
	$veh = datosVehiculo($orden_id, $dbpfx);
} else {
	$veh = datosVehiculo('', $dbpfx, $previa_id);
}

$preg = "SELECT sub_poliza FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '$reporte'";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion de póliza!");
while ($sub = mysql_fetch_array($matr)) {
	if ($sub['sub_poliza'] != '') { $poliza = $sub['sub_poliza']; } 
}

$preg0 = "SELECT c.cliente_nombre, c.cliente_apellidos FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de poliza!");
$clie = mysql_fetch_array($matr0);


$factura = $Comprobante->getAttribute("serie") . $Comprobante->getAttribute("folio");
if($tipocomprobante == 'egreso') { $nomcomprobante = 'Nota de Credito'; }
else { $nomcomprobante = 'Factura'; }

if(file_exists('particular/invoice.php')) {
	require('particular/invoice.php');
} else {
	require('parciales/invoice.php');
}

$pdf = new PDF_Invoice( 'P', 'mm', 'A4' );
$pdf->AliasNbPages();
$pdf->AddPage();

if(file_exists('particular/invoice.php')) {
	require('particular/fmt-fact.php');
} else {
	require('parciales/fmt-fact.php');
}

$pdf->Output(DIR_DOCS . $qrnom[0] . '.pdf', 'F');

if($enviacdfi == 1) {
	include('parciales/envia-cfdi.php');
} else {
	$pdf->Output();
}
?>
