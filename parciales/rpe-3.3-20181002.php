<?php

##########################################################
# PASO1. Crea un CFDi 3.3 cn el Complemento de Pagos
#
# Regresa un texto en la variable $cfdi
##########################################################

	if($_SESSION['rpe']['emitido'] == '1') {
		$mensaje = 'Ya se emitió el REP para este cobro';
		unset($_SESSION['rpe']['emitido']);
		$_SESSION['msjerror'] = $mensaje;
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
	}

# Partimos de un CFDi a medias, conservando declaracion de esquemas
		$cfdi = '<?xml version="1.0" encoding="utf-8"?>
<cfdi:Comprobante xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd http://www.sat.gob.mx/Pagos http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos10.xsd" xmlns:pago10="http://www.sat.gob.mx/Pagos" Version="3.3" Serie="" Folio="" Fecha="" Sello="" NoCertificado="" Certificado="" SubTotal="0" Moneda="XXX" Total="0" TipoDeComprobante="P" LugarExpedicion="" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" >
	<cfdi:Emisor Rfc="" Nombre="" RegimenFiscal="" />
	<cfdi:Receptor Rfc="" Nombre="" UsoCFDI="P01" />
	<cfdi:Conceptos>
		<cfdi:Concepto ClaveProdServ="84111506" Cantidad="1" ClaveUnidad="ACT" Descripcion="Pago" ValorUnitario="0" Importe="0" />
	</cfdi:Conceptos>
	<cfdi:Complemento>
		<pago10:Pagos xmlns:pago10="http://www.sat.gob.mx/Pagos" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="1.0"></pago10:Pagos>
	</cfdi:Complemento>
</cfdi:Comprobante>
';


//echo htmlspecialchars($cfdi);
	   
# Convierte a objeto DOM $xml
$xml = new DOMDocument();
$xml->loadXML($cfdi) or die('No se cargo XML inicial');

# Calcula totales
// --- NODO COMPROBANTE --- 
$c = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
$c->setAttribute('Serie', $repgen['Serie']);
$c->setAttribute('Folio', $repgen['Folio']);
//$c->setAttribute('Total', $total);

$fecha_emision = date('Y-m-d');
$fecha_emision .= 'T';
$fecha_emision .= date('H:i:s');
$c->setAttribute('Fecha', $fecha_emision);

//$c->setAttribute('FormaPago', $metop);
//$c->setAttribute('MetodoPago', $metodo_pago);
//$c->setAttribute('SubTotal', $subtotal);

# Modifica codigos semifijos

// --- NODO RECEPTOR --- 
$xmlreceptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
$xmlreceptor->setAttribute('Nombre', $repgen['ReceptorNombre']);
$xmlreceptor->setAttribute('Rfc', $repgen['ReceptorRfc']);

// --- NODO EMISOR ---
/* 
if(!$numemisorfiscal) { $numemisorfiscal = 1; }
foreach($valarr['RFC_' . $numemisorfiscal] as $kr => $vr) {
	$emisor[$vr[0]] = $vr[1];
}

$xmlemisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
$xmlemisor->setAttribute('Nombre', $emisor[2]);
$xmlemisor->setAttribute('Rfc', $emisor[1]);
$xmlemisor->setAttribute('RegimenFiscal', $emisor[3]);
$c->setAttribute('LugarExpedicion', $emisor[4]);
*/

$xmlemisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
$xmlemisor->setAttribute('Nombre', $agencia_razon_social);
$xmlemisor->setAttribute('Rfc', $agencia_rfc);
$xmlemisor->setAttribute('RegimenFiscal', $agencia_reg33);
$c->setAttribute('LugarExpedicion', $agencia_cp);

# Agrega pagos
$xmlpagos = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/Pagos', 'Pagos')->item(0);

foreach ($pagos as $k => $v) {
	$salto = $xml->createTextNode("\n\t\t\t");
	$salto = $xmlpagos->appendChild($salto);
	$xmlpago = $xml->createElement("pago10:Pago");
// --- Agregar atributos del pago ---
	$xmlpago->setAttribute('FechaPago', $v['FechaPago']);
	$xmlpago->setAttribute('FormaDePagoP', $v['FormaDePagoP']);
	$xmlpago->setAttribute('MonedaP', "MXN");
	$xmlpago->setAttribute('Monto', $v['Monto']);
	if($v['Banco'] == '') { $v['Banco'] = 'Efectivo'; }
	$xmlpago->setAttribute('NomBancoOrdExt', $v['Banco']);
	if($v['NumOperacion'] == '') { $v['NumOperacion'] = 'XX'; }
	$xmlpago->setAttribute('NumOperacion', $v['NumOperacion']);
//	$xmlpago->setAttribute('TipoCambioP', "1");
// --- Append del pago ----------
	$xmlpago = $xmlpagos->appendChild($xmlpago);
	$salto = $xml->createTextNode("\n\t\t\t\t");
	$salto = $xmlpago->appendChild($salto);
// --- Crear nodo documento relacionado *************
	$xmldocrel = $xml->createElement("pago10:DoctoRelacionado");
// --- Agregar atributos del documento relacionado ---
	$xmldocrel->setAttribute('IdDocumento', $v['IdDocumento']);
	$xmldocrel->setAttribute('Serie', $v['Serie']);
	$xmldocrel->setAttribute('Folio', $v['Folio']);
	$xmldocrel->setAttribute('ImpPagado', $v['ImpPagado']);
	$xmldocrel->setAttribute('ImpSaldoAnt', $v['ImpSaldoAnt']);
	$xmldocrel->setAttribute('ImpSaldoInsoluto', $v['ImpSaldoInsoluto']);
	$xmldocrel->setAttribute('MetodoDePagoDR', $v['MetodoDePagoDR']);
	$xmldocrel->setAttribute('MonedaDR', "MXN");
	$xmldocrel->setAttribute('NumParcialidad', $v['NumParcialidad']);
// --- Append del nodo documento relacionado----
	$xmldocrel = $xmlpago->appendChild($xmldocrel);
	$salto = $xml->createTextNode("\n\t\t\t");
	$salto = $xmlpago->appendChild($salto);
}
$salto = $xml->createTextNode("\n\t\t");
$salto = $xmlpagos->appendChild($salto);

unset($c);

# Reconvierte a texto
$cfdi = $xml->saveXML();
unset($xml);
	   
// echo $cfdi . '<br>';
// echo htmlspecialchars($cfdi);
// file_put_contents(DIR_DOCS . 'rpe-temporal.xml', $cfdi);

###############################################################
# PASO2. Firma el comprobante que esta en $cfdi en modo texto
#
# Regresa el comprobante firmado en la misma variable $cfdi
###############################################################

# Convierte a modelo DOM
$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido Paso 2");

# Extrae cadena original
$xslt = new XSLTProcessor();
$XSL = new DOMDocument();

//$cadena_ori = file_get_contents("http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos10.xslt");
$XSL->load('cadenaoriginal_3_3.xslt', LIBXML_NOCDATA);
error_reporting(0); # Se deshabilitan los errores pues el xsl de la cadena esta en version 2 y eso genera algunos warnings
$xslt->importStylesheet( $XSL );
error_reporting(E_ALL); # Se habilitan de nuevo los errores (se asume que originalmente estaban habilitados)

$c = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);

# A continuacion se incluye el certificado que se usará para firma.

include('../certificados/'.$agencia_rfc.'-certificado.php');

// include('/usr/share/php/Math/BigInteger.php');
include('parciales/BigInteger.php');

// echo 'BigInteger';
# Extrae el número de certificado
# Para su correcto funcionamiento esta seccion requiere el plugin o modulo GMPlib
$cert509 = openssl_x509_read($cert) or die("\nNo se puede leer el certificado\n");
$data = openssl_x509_parse($cert509);
# En $data hay mucha informacion relevante del certificado. Si se desea explorar se puede usar la funcion print_r. Las codificaciones son... interesantes, sobre todo ésta y las fechas

$serial1 = $data['serialNumber'];
// echo $serial1;
$serial2 = new Math_BigInteger($serial1);
$serial2 = $serial2->toHex();
// $serial2 = gmp_strval($serial1, 16);
$serial3 = explode("\n", chunk_split($serial2, 2, "\n"));
$serial = "";
foreach ($serial3 as $serialt) {
	if (2 == strlen($serialt))
		$serial .= chr('0x' . $serialt);
}
$noCertificado = $serial;

unset($serial1, $serial2, $serial3, $serial, $serialt, $data, $cert509);

$c->setAttribute('NoCertificado', $noCertificado);

$cadena = $xslt->transformToXML( $c );
unset($xslt, $XSL);

// echo "Cadena original: " . $cadena . "\n";
// echo "Numero de certificado = [$noCertificado]\n";

# Extrae valores relevantes
# Extrae el certificado, sin enters para anexarlo al cfdi

// echo $cert."\n";

preg_match('/-----BEGIN CERTIFICATE-----(.+)-----END CERTIFICATE-----/msi', $cert, $matches) or die("No certificado\n");
$algo = $matches[1];
$algo = preg_replace('/\n/', '', $algo);
$certificado = preg_replace('/\r/', '', $algo);
// echo "Certificado = [$certificado]\n";

# Extrae la llave privada, en formato openssl
$key = openssl_pkey_get_private($cert) or die("No llave privada\n");

# Firma la cadena original con la llave privada y codifica en base64 el resultado
$crypttext = "";

openssl_sign($cadena, $crypttext, $key, OPENSSL_ALGO_SHA256);
$sello = base64_encode($crypttext);
// echo "sello = [$sello]\n";

# Incorpora los dos elementos al cfdi
$c->setAttribute('Certificado', $certificado);
$c->setAttribute('Sello', $sello);

# regresa el resultado
$cfdi = $xml->saveXML();
unset($c, $xml, $cert, $certificado, $cadena, $crypttext, $key);
//echo htmlspecialchars($cfdi);
file_put_contents(DIR_DOCS . 'rpe-temporal.xml', $cfdi);

###############################################################
# PASO3. Verifica el CFDI en la variable $cfdi
#
# Se interrumpe si hay error
###############################################################

$cadena="";
# Valida UTF8
mb_check_encoding($cfdi, "UTF-8") or die("El string no esta en UTF8\n");

# Convierte a modelo DOM
$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido Paso 3");

# Valida contra esquema
// $Pagos10xsd = file_get_contents("http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos10.xsd");

$xml->schemaValidate('cfdv33.xsd') or die("\n\nNo es un CFDi valido para esquema cfdv33.xsd");

//echo htmlspecialchars($cfdi);

# Verifica la firma
$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
# Extrae cadena original
$xslt = new XSLTProcessor();
$XSL = new DOMDocument();
$XSL->load('cadenaoriginal_3_3.xslt', LIBXML_NOCDATA);
error_reporting(0);
$xslt->importStylesheet( $XSL );
error_reporting(E_ALL);
$cadena = $xslt->transformToXML( $Comprobante );
unset($xslt, $XSL);

// echo '<br>Cadena Extraida: ' . $cadena . '<br>';

# Extrae el certificado y lo pone en formato que las funciones puedan leer
$cert2 = $Comprobante->getAttribute("Certificado");
$cert  = "-----BEGIN CERTIFICATE-----\n";
$cert .= chunk_split($cert2, 64, "\n");
$cert .= "-----END CERTIFICATE-----\n";

if (!($pkey = openssl_pkey_get_public($cert))) {
	echo "\n\n\nNo es posible extraer llave publica\n";
	die;
}

# Extrae sello
$crypttext = base64_decode($Comprobante->getAttribute("Sello"));

// echo "Sello decodificado:<br><br>".$crypttext;

// echo htmlspecialchars($cfdi);


if (openssl_verify($cadena, $crypttext, $pkey, OPENSSL_ALGO_SHA256)) {
//	echo  "El firmado es correcto\n";
} else {
	die("\nError en el firmado!!!\n");
}

file_put_contents(DIR_DOCS . 'rpe-temporal.xml', $cfdi);

unset($xml, $Comprobante, $cert2, $cert, $pkey, $crypttext, $cadena);



###############################################################
# PASO4. Timbra el CFDI en la variable $cfdi con TimbreFiscal
#
#        4.1) Ensobreta
#        4.2) Envía a TimbreFiscal
#        4.3) Recibe un timbre (o procesa un error)
# Regresa el $cfdi intacto y $timbre
###############################################################

include_once('parciales/'.$pac_prov);

###############################################################
# PASO5. Integra el timbre recibido en $timbre en el $cfdi
#
# Regresa el $cfdi ya integrado con el timbre
###############################################################
// echo "\n\nPASO5. Integra el timbre recibido en \$timbre en el \$cfdi\n";
# Convierte a modelo DOM

$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido paso 5");
$xml->schemaValidate('cfdv33.xsd') or die("\n\n\nCFDi no valido paso 5 validación cfdv33.xsd");
# Valida que realmente haya regresado un timbre
$sobretimbre = new DOMDocument();
$sobretimbre->loadXML($timbre) or die("\n\n\nXML de respuesta timbrado no valido\n");
# Extrae el timbre (si existe)
$xmltimbre = new DOMDocument('1.0', 'UTF-8');
# Extrae el nodo
$paso = $sobretimbre->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);
$res_uuid = $paso->getAttribute("UUID");
$paso = $xmltimbre->importNode($paso, true);
$xmltimbre->appendChild($paso);
unset($paso);
# Valida
$xmltimbre->schemaValidate('http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd') or die("\n\n\nError de validación Timbre Fiscal\n\n$return");
# Incorpora el timbre en el nodo complemento. Si no existe dicho nodo, lo crea
$complemento = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Complemento')->item(0);
if (!$complemento) {
	$complemento = $xml->createElementNS('http://www.sat.gob.mx/cfd/3', 'Complemento');
	$xml->appendChild($complemento);
}
$t = $xmltimbre->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);
$t = $xml->importNode($t, true);
$complemento->appendChild($t);
$cfdi = $xml->saveXML();

$nombre_cfdi = $rpe_serie . $rpe_num . '-' . $res_uuid;

file_put_contents(DIR_DOCS.$nombre_cfdi.'.xml', $cfdi);
unset($timbre, $xml, $sobretimbre, $xmltimbre, $paso, $complemento, $t);

	include('parciales/phpqrcode/qrlib.php');
	$fe = substr($sello, -8);
	$ftotal = number_format($total, 6, '.', '');
	$qrtotal = strval($ftotal);
	$qrtotal = sprintf('%017s', $qrtotal);
	$codigoqr = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=' . $res_uuid . '&re=' . $agencia_rfc . '&rr=' . $repgen['ReceptorRfc'] . '&tt=' . $qrtotal . '&fe=' . $fe;
	$imagenqr = DIR_DOCS . $nombre_cfdi . '.png';
	QRcode::png($codigoqr, $imagenqr, 'L', 4, 2);

	$doc_nombre = 'Recibo Pago Electrónico XML ' . $rpe_serie . $rpe_num;
	$sql_data_array = array('doc_nombre' => $doc_nombre,
		'doc_clasificado' => 1,
		'doc_usuario' => $_SESSION['usuario'],
		'doc_archivo' => $nombre_cfdi . '.xml');
		$sql_data_array['orden_id'] = $orden_id;
	ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');

	$pregtim = "UPDATE " . $dbpfx . "valores SET val_numerico = (val_numerico - 1) WHERE val_nombre = 'timbres'";
	$matrtim = mysql_query($pregtim) or die("ERROR: Fallo actualización de timbres! ".$pregtim);
	$archivo = '../logs/' . date('Ymd-i') . '-base.ase';
	$myfile = file_put_contents($archivo, $pregtim . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
   
	$doc_nombre = 'Recibo Pago Electrónico PDF ' . $rpe_serie . $rpe_num;
	$sql_data_array = array('doc_nombre' => $doc_nombre,
		'doc_clasificado' => 1,
		'doc_usuario' => $_SESSION['usuario'],
		'doc_archivo' => $nombre_cfdi . '.pdf');
		$sql_data_array['orden_id'] = $orden_id;
	ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
	bitacora($orden_id, $doc_nombre, $dbpfx);

	$sql_data = array('fact_serie' => $rpe_serie,
		'fact_num' => $rpe_num,
		'orden_id' => $orden_id,
		'fact_uuid' => $res_uuid,
		'reporte' => $reporte,
		'fact_fecha' => date('Y-m-d'),
		'usuario' => $_SESSION['usuario']);
	ejecutar_db($dbpfx . 'facturas', $sql_data, 'insertar');
	$rpe_id = mysql_insert_id();
	unset($sql_data);

	$paramrpe = "cobro_id = '" . $cobro_id . "'";
	$sql_data = array('rpe_id' => $rpe_id);
	ejecutar_db($dbpfx . 'cobros', $sql_data, 'actualizar', $paramrpe);

	unset($_SESSION['ent']['rpe_num']);

	$_SESSION['rpe']['emitido'] = 1;
	redirigir('rpe-3.3-pdf.php?axml=' . $nombre_cfdi . '.xml');

?>
