<?php
include('parciales/funciones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}
include('parciales/numeros-a-letras.php');
include('idiomas/' . $idioma . '/factura.php');
include('parciales/metodos-de-pago.php');

if ($accion==="consultar") {
	
	if (validaAcceso('1095000', $dbpfx) == '1') {
		$mensaje = 'Acceso autorizado';
	} elseif ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1' || $_SESSION['rol12']=='1')) {
		$mensaje = 'Acceso autorizado';
	} else {
		$_SESSION['msjerror'] = 'Acceso NO autorizado';
		 redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
	
	include('parciales/encabezado.php'); 
	echo '	<div id="body">'."\n";
	include('parciales/menu_inicio.php');
	echo '	<div id="principal">'."\n";

	$error = 'no'; $num_cols = 0; $mensaje = '';
	$preg0 = "SELECT * FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '" . $fact_id . "'";
//	echo $preg0;
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de factura! " . $preg0);
	$fact = mysql_fetch_array($matr0);

	$preg1 = "SELECT * FROM " . $dbpfx . "facturas WHERE fact_serie = '" . $valor['ncserie'][1] . "' ORDER BY fact_num DESC LIMIT 1";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección!");
	$facturas = mysql_num_rows($matr1);
	if($facturas > 0) {
		$factura = mysql_fetch_array($matr1);
		$fact_num = $factura['fact_num'] + 1;
		if($valor['timbres'][0] < 15 && $valor['timbres'][0] > 0) {
			$alerta = 'Quedan ' . $valor['timbres'][0] . ' folios disponibles: solicitar nuevos folios a la brevedad!';
		} elseif($valor['timbres'][0] <= 0) {
     		$error = 'si';
     		$mensaje .= 'Folios agotados, no se pueden emitir más comprobantes fiscales.<br>';
     	}
	} else {
		$fact_num = $valor['ncinicial'][0];
	}

	if ($error ==='no') {
		$mensaje = '';
		$prctmax = round((($mont_pc / $fact['fact_monto']) * 100),6);
			echo '			<form action="nota-de-credito.php?accion=confirma" method="post" enctype="multipart/form-data" name="imprime">'  . "\n";
   	  	echo '			<table cellpadding="0" cellspacing="0" border="0" class="agrega">
				<tr><td><span class="alerta">' . $_SESSION['notac']['mensaje'] . '<br>' . $alerta . '</span></td></tr>'."\n";
			echo '				<tr class="cabeza_tabla"><td colspan="2">' . $lang['NCPara'] . ' ' . $numero . ' ' . $lang['ConMonto'] . ' ' . number_format($fact['fact_monto'],2) . "\n";
			echo '</td></tr>'."\n";
			echo '				<tr><td colspan="2">' . $lang['SeVaUtilizar'] . ' <strong>' . $valor['ncserie'][1] . '</strong> ' . $lang['NumNC'] . ' <input type="text" name="fact_num" value="' . $fact_num . '" size="8" /></td></tr>'."\n";
			echo '				<tr><td colspan="2">' . $lang['PorcentajeNC'] . ' ' . $prctmax . '% <input type="text" name="porcentaje" value="' . $_SESSION['notac']['porcentaje'] . '" size="8" /></td></tr>'."\n";
			echo '				<tr><td colspan="2">' . $lang['MontoNC'] . ' $' . number_format(round($mont_pc,6),6) . ' <input type="text" name="monetario" value="' . $_SESSION['notac']['monetario'] . '" size="8" /></td></tr>'."\n";
			echo '				<tr><td colspan="2">' . $lang['MotivoNC'] . ': <input type="text" name="motivo" value="' . $_SESSION['notac']['motivo'] . '" size="50" maxlength="240"/></td></tr>'."\n";
			echo '				<tr class="obscuro"><td style="vertical-align: middle;">' . $lang['UsoNCPreg'] . '</td><td style="text-align:left;">'."\n";
			echo '					<input type="radio" name="usonc" value="1" ';
			if($_SESSION['notac']['usonc'] == 1) { echo 'checked="checked" '; } echo '/>' . $lang['UsoNCTotal'] . '<br>'."\n";
			echo '					<input type="radio" name="usonc" value="2" ';
			if($_SESSION['notac']['usonc'] == 2) { echo 'checked="checked" '; } echo '/>' . $lang['UsoNCParcial'] . '<br>'."\n";
			echo '					<input type="radio" name="usonc" value="3" ';
			if($_SESSION['notac']['usonc'] == 3) { echo 'checked="checked" '; } echo '/>' . $lang['UsoNCDescuento'] . ' '."\n";
			echo '				</td></tr>'."\n";
			echo '				<tr><td colspan="2"><input type="hidden" name="orden_id" value="' . $orden_id . '" />
						<input type="hidden" name="fact_id" value="' . $fact_id . '" />
						<input type="hidden" name="reporte" value="' . $reporte . '" />
						<input type="hidden" name="numero" value="' . $numero . '" />
						<input type="hidden" name="fact_monto" value="' . $fact['fact_monto'] . '" />
						<input type="hidden" name="mont_pc" value="' . $mont_pc . '" />
						<input type="hidden" name="prctmax" value="' . $prctmax . '" />
				</td></tr>'."\n";
			echo '				<tr><td colspan="2"><input type="submit" value="Enviar" /></td></tr>'."\n";
			echo '			</table></form>'."\n";
	} else {
//		$mensaje = 'No hay conceptos por facturar.';
		echo '<p>' . $mensaje . '</p>';
	}
	echo '		<div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div>'."\n";
	unset($_SESSION['notac']);
}

elseif($accion==='confirma') {

	if (validaAcceso('1095000', $dbpfx) == '1') {
		$mensaje = 'Acceso autorizado';
	} elseif ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1' || $_SESSION['rol12']=='1')) {
		$mensaje = 'Acceso autorizado';
	} else {
		$_SESSION['msjerror'] = 'Acceso NO autorizado';
		 redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}

	$error = 'no'; $mensaje = '';
	$porcentaje = limpiarNumero($porcentaje); $_SESSION['notac']['porcentaje'] = $porcentaje;
	$monetario = limpiarNumero($monetario); $_SESSION['notac']['monetario'] = $monetario;
	$motivo = preparar_entrada_bd($motivo); $_SESSION['notac']['motivo'] = $motivo;
	$_SESSION['notac']['usonc'] = $usonc;

	$preg0 = "SELECT * FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '" . $fact_id . "'";
//	echo $preg0;
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de factura! " . $preg0);
	$fact = mysql_fetch_array($matr0);
	$cfdi = file_get_contents(DIR_DOCS . $numero . '-' . $fact['fact_uuid'] . '.xml');
	$xml = new DOMDocument();
	if(!$xml->loadXML($cfdi)) { $error = 'si'; $mensaje .= $lang['NoXml'] . '<br>'; }
	if(($porcentaje > 0 && $monetario > 0) || ($porcentaje == 0 && $monetario == 0) || $porcentaje < 0 || $monetario < 0) { $error = 'si'; $mensaje .= $lang['PrctOMonet'] . '<br>'; }
	if($porcentaje > 0 && $porcentaje > $prctmax) { $error = 'si'; $mensaje .= $lang['PrctExced'] . '<br>'; }
	if($monetario > 0 && $monetario > $mont_pc) { $error = 'si'; $mensaje .= $lang['MontoExced'] . '<br>'; }
	if($motivo == '') { $error = 'si'; $mensaje .= $lang['NoMotNC'] . '<br>'; }
	if($usonc == 1 && (($porcentaje > 0 && $porcentaje < 100) || ($monetario > 0 && $monetario < $fact_monto))) {
		$error = 'si'; $mensaje .= $lang['NoRefacturaCompleta'] . '<br>'; 
	}
	if($usonc == '' || !$usonc) { $error = 'si'; $mensaje .= $lang['DebeSelUsoNC'] . '<br>'; }

// echo 'Hola Mundo!';

   if($error == 'no') {
   	unset($_SESSION['notac']);
   	if($porcentaje > 0) {
   		$total = round((($porcentaje / 100) * $fact_monto),2);
   	} else {
   		$total = round($monetario,2);
   	}
   	$subtotal = round(($total / 1.16),2);
   	$iva = $total - $subtotal;
//		echo 'Total: ' . $total; 
		$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
		$Emisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
		$Receptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
//		$Impuestos = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Impuestos')->item(3);
		$Timbre = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);

// ------ Verificación de version de CFDi
		if($Comprobante->getAttribute("version") == '3.2') {
			$EmisorRfc = $Emisor->getAttribute("Rfc");
			$receptor_nombre = utf8_encode($Receptor->getAttribute("nombre"));
			$receptor_rfc = utf8_encode($Receptor->getAttribute("rfc"));
			$metodoDePago = utf8_decode($Comprobante->getAttribute("metodoDePago"));
			if($metodoDePago == 'NA' || $metodoDePago == '99') {
				$comprobante_f_pago = '99';
				$comprobante_m_pago = 'PPD';
			} else {
				$comprobante_f_pago = $metodoDePago;
				$comprobante_m_pago = 'PUE';
			}
		} elseif($Comprobante->getAttribute("Version") == '3.3') {
			$EmisorRfc = $Emisor->getAttribute("Rfc");
			$receptor_nombre = utf8_encode($Receptor->getAttribute("Nombre"));
			$receptor_rfc = utf8_encode($Receptor->getAttribute("Rfc"));
			$comprobante_f_pago = utf8_decode($Comprobante->getAttribute("FormaPago"));
			$comprobante_m_pago = utf8_decode($Comprobante->getAttribute("MetodoPago"));
		}

		// --- Si hay RFCs alternos, localiza el utilizado en la factura ---
		if($RfcAlternos > 0) {
			foreach($Rfcs as $rfck => $rfcv) {
				if($EmisorRfc == $rfcv[0]) {
					$agencia_rfc = $rfcv[0];
					$agencia_razon_social = $rfcv[1];
					$agencia_reg33 = $rfcv[2];
					$agencia_cp = $rfcv[3];
				}
			}
		}
		
##########################################################
# PASO1. Crea un CFDi 3.3 de factura con un par de conceptos
#
# Regresa un texto en la variable $cfdi
##########################################################
//echo $Timbre->getAttribute("UUID");

//echo 'Nombre: ' . utf8_encode($receptor_nombre) . 'Sin encode: ' .  $receptor_nombre;
# Partimos de un CFDi a medias, conservando declaracion de esquemas
		$cfdi = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd" Version="3.3" Sello="" NoCertificado="" Certificado="" LugarExpedicion="" Fecha="" Serie="" Folio="" TipoDeComprobante="E" FormaPago="' . $comprobante_f_pago . '" MetodoPago="' . $comprobante_m_pago . '" Moneda="MXN" TipoCambio="1" SubTotal="' . $subtotal . '" Total="' . $total . '" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<cfdi:CfdiRelacionados TipoRelacion="01">
		<cfdi:CfdiRelacionado UUID="' . $Timbre->getAttribute("UUID") . '" />
	</cfdi:CfdiRelacionados>
	<cfdi:Emisor Rfc="" Nombre="' . $agencia_razon_social . '" RegimenFiscal="' . $agencia_reg33 . '"/>
	<cfdi:Receptor Rfc="" Nombre="' . $receptor_nombre . '" UsoCFDI="G02" />
	<cfdi:Conceptos>
		<cfdi:Concepto Cantidad="1" ClaveProdServ="84111506" Descripcion="' . $motivo;
		if($reporte != '' && $reporte != '0') {
			$cfdi .= ' Siniestro: ' . $reporte;
		}
		$cfdi .='" ClaveUnidad="ACT" Unidad="ACT" ValorUnitario="' . $subtotal . '" Importe="' . $subtotal . '">
			<cfdi:Impuestos>
				<cfdi:Traslados>
					<cfdi:Traslado Base="' . $subtotal . '" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="' . $iva . '" />
				</cfdi:Traslados>
			</cfdi:Impuestos>
		</cfdi:Concepto>
	</cfdi:Conceptos>
	<cfdi:Impuestos TotalImpuestosTrasladados="' . $iva . '">
		<cfdi:Traslados>
			<cfdi:Traslado Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="' . $iva . '" />
		</cfdi:Traslados>
	</cfdi:Impuestos>
	<cfdi:Complemento>
	</cfdi:Complemento>
</cfdi:Comprobante>
';

file_put_contents(DIR_DOCS . 'nc-temporal.xml', $cfdi);
// echo 'Crudo: ' . $cfdi;
// echo htmlspecialchars($cfdi);

# Convierte a objeto DOM $xml
$xml = new DOMDocument();
$xml->loadXML($cfdi) or die ('Archivo no válido');

$letra = strtoupper(letras2($total));


$fecha_emision = date('Y-m-d');
$fecha_emision .= 'T';
$fecha_emision .= date('H:i:s');

# Calcula totales

// --- NODO RECEPTOR --- 
$xmlreceptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
$xmlreceptor->setAttribute('Rfc', $receptor_rfc);
	   
// --- NODO EMISOR --- 
$xmlemisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
$xmlemisor->setAttribute('Rfc', $agencia_rfc);

// --- NODO COMPROBANTE --- 
$c = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
$c->setAttribute('Serie', $valor['ncserie'][1]);
$c->setAttribute('Folio', $fact_num);
$c->setAttribute('LugarExpedicion', $agencia_cp);
$c->setAttribute('Fecha', $fecha_emision);

# Reconvierte a texto
$cfdi = $xml->saveXML();

unset($c);
unset($xml);
	   
// echo $cfdi . '<br>';
// echo htmlspecialchars($cfdi);
// file_put_contents(DIR_DOCS . 'temporal.xml', $cfdi);

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

// $cadena_ori = file_get_contents("http://www.sat.gob.mx/sitio_internet/cfd/3/cadenaoriginal_3_2/cadenaoriginal_3_2.xslt");
$XSL->load('cadenaoriginal_3_3.xslt', LIBXML_NOCDATA);
error_reporting(0); # Se deshabilitan los errores pues el xssl de la cadena esta en version 2 y eso genera algunos warnings
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
// echo htmlspecialchars($cfdi);
 file_put_contents(DIR_DOCS . 'nc-temporal.xml', $cfdi);

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

//echo htmlspecialchars($cfdi);

# Valida contra esquema
// $xml->schemaValidate('cfdv33.xsd') or die("\n\nNo es un CFDi valido para esquema cfdv33.xsd");

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

// echo '<br>Cadena: ' . $cadena . '<br>';

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

file_put_contents(DIR_DOCS . 'nc-temporal.xml', $cfdi);

unset($xml, $Comprobante, $cert2, $cert, $pkey, $crypttext, $cadena);


###############################################################
# PASO4. Timbra el CFDI en la variable $cfdi con TimbreFiscal
#
#        4.1) Ensobreta
#        4.2) Envía a TimbreFiscal
#        4.3) Recibe un timbre (o procesa un error)
# Regresa el $cfdi intacto y $timbre
###############################################################

$cfdiversion = 3.3;

include_once('parciales/'.$pac_prov);

file_put_contents(DIR_DOCS . 'nc-timbre-temporal.xml', $timbre);

###############################################################
# PASO5. Integra el timbre recibido en $timbre en el $cfdi
#
# Regresa el $cfdi ya integrado con el timbre
###############################################################
// echo "\n\nPASO5. Integra el timbre recibido en \$timbre en el \$cfdi\n";
# Convierte a modelo DOM

$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido paso 5");
//$xml->schemaValidate('cfdv33.xsd') or die("\n\n\nCFDi no valido paso 5 validación cfdv33.xsd");

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
//$xmltimbre->schemaValidate('http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd') or die("\n\n\nError de validación Timbre Fiscal.\n\n$return");

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

$nombre_cfdi = $valor['ncserie'][1] . $fact_num . '-' . $res_uuid;
file_put_contents(DIR_DOCS.$nombre_cfdi.'.xml', $cfdi);

// ------ Obtener los datos que se imprimen en el archivo PDF y guardarlos como parte de la adsenda AutoShopEasy

		$preg3 = "SELECT * FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '$reporte' AND sub_estatus < '190'";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de Subordenes! " . $preg3);
		while ($datsub = mysql_fetch_array($matr3)) {
			if($datsub['sub_poliza'] != '') { $poliza = $datsub['sub_poliza']; }
			if($datsub['sub_deducible'] > $dedu) { $dedu = round($datsub['sub_deducible'], 2); }
		}
		$datveh = datosVehiculo($orden_id, $dbpfx);
		$datvehiculo = $datveh['marca'] . ' ' . $datveh['tipo'] . ' ' . $datveh['modelo'];
		$datplacas =  $datveh['placas'];
		$datvin = $datveh['serie'];
		$preg3 = "SELECT c.cliente_nombre, c.cliente_apellidos FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de Clientes! " . $preg3);
		$datcli =  mysql_fetch_array($matr3);
		$datcliente = $datcli['cliente_nombre'] . ' ' . $datcli['cliente_apellidos'];

// ------ Inserción de anotaciones de Ordenes de Trabajo, Siniestros, Clientes, Datos del Vehículo y otros que relacionan el XML con los datos de AutoShop-Easy
// $res_uuid = 'uuid-de-pruebas-8862522424';
$Compro = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
$salto = $xml->createTextNode("\t");
$salto = $Compro->appendChild($salto);
$AddenD = $xml->createElementNS('http://www.sat.gob.mx/cfd/3', 'Addenda');
$AddenD = $Compro->appendChild($AddenD);
$salto = $xml->createTextNode("\n\t\t");
$salto = $AddenD->appendChild($salto);
$Ase = $xml->createElement("AutoShopEasy");	
$Ase->setAttribute("version", "1.0");
$Ase = $AddenD->appendChild($Ase);
$salto = $xml->createTextNode("\n\t\t\t");
$salto = $Ase->appendChild($salto);
$Anotaciones = $xml->createElement("Anotaciones");

// ------ Identificadores impresos en PDF ------
$preg3 = "SELECT a.omite_datos_pdf FROM " . $dbpfx . "subordenes s, " . $dbpfx . "aseguradoras a WHERE s.orden_id = '$orden_id' AND s.sub_reporte = '$reporte' AND a.aseguradora_id = s.sub_aseguradora AND s.sub_estatus < '190' LIMIT 1";
$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de aseguradora! " . $preg3);
$asegu = mysql_fetch_array($matr1);
if($asegu['omite_datos_pdf'] != '1') {
	$Anotaciones->setAttribute("POLIZA", $poliza);
	$Anotaciones->setAttribute("SINIESTRO", $reporte);
	$Anotaciones->setAttribute("CLIENTE", $datcliente);
	$Anotaciones->setAttribute("VEHICULO", $datvehiculo);
	$Anotaciones->setAttribute("PLACAS", $datplacas);
	$Anotaciones->setAttribute("VIN", $datvin);
}

// ------ Identificadores propios de la Nota de Creédito
$Anotaciones->setAttribute("MotivoNC", $motivo);
if($usonc == 1) { $UsoNC = $lang['UsoNCTotal']; }
elseif($usonc == 2) { $UsoNC = $lang['UsoNCParcial']; }
else { $UsoNC = $lang['UsoNCDescuento']; }
$Anotaciones->setAttribute("UsoNC", $UsoNC);
// ------ Fin de anotaciones ------
$Anotaciones = $Ase->appendChild($Anotaciones);
$salto = $xml->createTextNode("\n\t\t");
$salto = $Ase->appendChild($salto);
$salto = $xml->createTextNode("\n\t");
$salto = $AddenD->appendChild($salto);
$salto = $xml->createTextNode("\n");
$salto = $Compro->appendChild($salto);

$cfdi = $xml->saveXML();
file_put_contents(DIR_DOCS.$nombre_cfdi.'.xml', $cfdi);
unset($timbre, $xml, $sobretimbre, $xmltimbre, $paso, $complemento, $t);

		include('parciales/phpqrcode/qrlib.php');
		$fe = substr($sello, -8);
		$ftotal = number_format($total, 6, '.', '');
		$qrtotal = strval($ftotal);
		$qrtotal = sprintf('%017s', $qrtotal);
		$codigoqr = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=' . $res_uuid . '&re=' . $agencia_rfc . '&rr=' . $receptor_rfc . '&tt=' . $qrtotal . '&fe=' . $fe;
		$imagenqr = DIR_DOCS . $nombre_cfdi . '.png';
		QRcode::png($codigoqr, $imagenqr, 'L', 4, 2);

		$doc_nombre = 'Nota de Crédito XML ' . $valor['ncserie'][1] . $fact_num;
		$sql_data_array = array('doc_nombre' => $doc_nombre,
			'doc_clasificado' => 1,
			'doc_usuario' => $_SESSION['usuario'],
			'doc_archivo' => $nombre_cfdi . '.xml');
			$sql_data_array['orden_id'] = $orden_id;
		ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
		sube_archivo($nombre_cfdi . '.xml');

		$pregtim = "UPDATE " . $dbpfx . "valores SET val_numerico = (val_numerico - 1) WHERE val_nombre = 'timbres'";
		$matrtim = mysql_query($pregtim) or die("ERROR: Fallo actualización de timbres! ".$pregtim);
		$archivo = '../logs/' . time() . '-base.ase';
		$myfile = file_put_contents($archivo, $pregtim . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
   
		$doc_nombre = 'Nota de Crédito PDF ' . $valor['ncserie'][1] . $fact_num;
		$sql_data_array = array('doc_nombre' => $doc_nombre,
			'doc_clasificado' => 1,
			'doc_usuario' => $_SESSION['usuario'],
			'doc_archivo' => $nombre_cfdi . '.pdf');
			$sql_data_array['orden_id'] = $orden_id;
		ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
		sube_archivo($nombre_cfdi . '.pdf');
		bitacora($orden_id, $doc_nombre, $dbpfx);
   
	  	$sql_data = array('fact_serie' => $valor['ncserie'][1],
  			'fact_num' => $fact_num,
  			'orden_id' => $orden_id,
  			'reporte' => $reporte,
	  		'fact_rfc' => $receptor_rfc,
  			'fact_sub' => $subtotal,
  			'fact_iva' => $iva,
	  		'fact_total' => $total,
  			'fact_fecha' => date('Y-m-d'),
  			'usuario' => $_SESSION['usuario']);
	  	ejecutar_db($dbpfx . 'facturas', $sql_data, 'insertar');

  		$factcomp = $valor['ncserie'][1] . $fact_num;

// ------ Se registra la NC como cobro de la factura ya que esta se mantiene vigente ------
		$sql_data_array = array(
			'orden_id' => $orden_id,
			'reporte' => $reporte,
			'cobro_tipo' => '4', // ------ El cobro_tipo = 4 se ocupa para identificar como Nota de Crédito.
			'cobro_monto' => $total,
			'cobro_metodo' => $comprobante_f_pago,
			'cobro_referencia' => $lang['NotaCredito'] . ' ' . $factcomp,
			'cobro_fecha' => date('Y-m-d H:i:s', time()),
			'cobro_documento' => $nombre_cfdi . '.pdf',
			'usuario' => $_SESSION['usuario']);
		$cobro_id = ejecutar_db($dbpfx . 'cobros', $sql_data_array);
		bitacora($orden_id, $lang['NotaCredito'] . ' ' . $lang['ImpTotFac'] . ' ' . $fact_id, $dbpfx);

		$sql_data_array = array(
			'cobro_id' => $cobro_id,
			'fact_id' => $fact_id,
			'monto' => $total,
			'orden_id' => $orden_id,
			'usuario' => $_SESSION['usuario'],
			'fecha' => date('Y-m-d H:i:s', time()));
		ejecutar_db($dbpfx . 'cobros_facturas', $sql_data_array);
		bitacora($orden_id, 'Cobro de Factura ID ' . $fact_id . ' con el cobro id ' . $cobro_id, $dbpfx);
// ------ Se liberan las tareas para que se pueda emitir una nueva factura 
		if($usonc == 1) {
			$pregunta = "UPDATE " . $dbpfx . "subordenes SET fact_id = NULL, sub_impuesto = NULL WHERE fact_id = '" . $fact_id . "'";
			$resultado = mysql_query($pregunta);
			$archivo = '../logs/' . time() . '-base.ase';
			$myfile = file_put_contents($archivo, $pregunta . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
		} elseif($usonc == 2) {
			$preg2 = "SELECT * FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND sub_reporte = '" . $reporte . "' AND fact_id = '" . $fact_id . "' AND sub_estatus < '190' LIMIT 1";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección aseguradoras! " . $preg2);
			$aseg2 = mysql_fetch_array($matr2);
			$sql_data_array = array('orden_id' => $orden_id,
				'sub_area' => 50,
				'sub_presupuesto' => $subtotal,
				'sub_impuesto' => $iva,
				'sub_mo' => $subtotal,
				'sub_descripcion' => $motivo,
				'sub_siniestro' => $aseg2['sub_siniestro'],
				'sub_reporte' => $reporte,
				'sub_aseguradora' => $aseg2['sub_aseguradora'],
				'sub_poliza' => $aseg2['sub_poliza'],
				'sub_estatus' => '112');
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
		}

// ------------- Redirigir a ex-3.3.php  -------------------------   	

		redirigir('ex-3.3.php?axml=' . $nombre_cfdi . '.xml&orden_id=' . $orden_id . '&reporte=' . $reporte);

	} else {
		$_SESSION['msjerror'] = $mensaje;
//		echo $mensaje;
		redirigir('nota-de-credito.php?accion=consultar&orden_id=' . $orden_id . '&reporte=' . $reporte . '&numero=' . $numero . '&fact_id=' . $fact_id . '&mont_pc=' . $mont_pc);
	}
}

?>
		</div>
	</div>
<p class="footer">Derechos Reservados 2009 - 2014</p>
</div>
</body>
</html>
