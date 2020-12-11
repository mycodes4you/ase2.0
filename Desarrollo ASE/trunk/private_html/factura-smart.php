<?php
include('parciales/funciones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}


//			"token"=>"T2lYQ0t4L0RHVkR4dHZ5Nkk1VHNEakZ3Y0J4Nk9GODZuRyt4cE1wVm5tbXB3YVZxTHdOdHAwVXY2NTdJb1hkREtXTzE3dk9pMmdMdkFDR2xFWFVPUXpTUm9mTG1ySXdZbFNja3FRa0RlYURqbzdzdlI2UUx1WGJiKzViUWY2dnZGbFloUDJ6RjhFTGF4M1BySnJ4cHF0YjUvbmRyWWpjTkVLN3ppd3RxL0dJPQ.T2lYQ0t4L0RHVkR4dHZ5Nkk1VHNEakZ3Y0J4Nk9GODZuRyt4cE1wVm5tbFlVcU92YUJTZWlHU3pER1kySnlXRTF4alNUS0ZWcUlVS0NhelhqaXdnWTRncklVSWVvZlFZMWNyUjVxYUFxMWFxcStUL1IzdGpHRTJqdS9Zakw2UGRiMTFPRlV3a2kyOWI5WUZHWk85ODJtU0M2UlJEUkFTVXhYTDNKZVdhOXIySE1tUVlFdm1jN3kvRStBQlpLRi9NeWJrd0R3clhpYWJrVUMwV0Mwd3FhUXdpUFF5NW5PN3J5cklMb0FETHlxVFRtRW16UW5ZVjAwUjdCa2g0Yk1iTExCeXJkVDRhMGMxOUZ1YWlIUWRRVC8yalFTNUczZXdvWlF0cSt2UW0waFZKY2gyaW5jeElydXN3clNPUDNvU1J2dm9weHBTSlZYNU9aaGsvalpQMUxzMTZPMjRFWmFBbFpuOVVLKzU1VWpuQnJsekErY2JrYUc2Yk0rVloxU1ZQR1dnbjhJU21YOEJZWHloNWwyYnNYTXkxZkVLNmpFVEtlZjZtUEdsUllydjNocTBlRy9vRzROTHZBdUZuVU1IRnRLSHExTVNtOVJmTG1qcVNmNFBScFlDL0xzU0JRby9qb0VBazFIUVp2TkU9._n-3PjNzktVuA8iN2a51nILrm9s3LmI5_Zb69nMn1_g"


	require_once "vendor/autoload.php";
	use SWServices\Stamp\StampService as StampService;

	 try{
		header("Content-type: application/json");
		$params = array(
			"url"=>"http://services.test.sw.com.mx",
			"user"=>"demo",
			"password"=>"123456789"
			);
		$cfdi = file_get_contents("documentos/" . $xmltemporal);
//		echo $xml;
		$stamp = StampService::Set($params);
		$result = $stamp::StampV1($cfdi);
		$timbre = $result->data->tfd;
//		file_put_contents('documentos/temporal.xml', $result);
//		echo $result;
//		var_dump(get_object_vars($result));

	}
	catch(Exception $e){
		header("Content-type: text/plain");
		echo "Caught exception: ",  $e->getMessage();
	} 
	
###############################################################
# PASO5. Integra el timbre recibido en $timbre en el $cfdi
#
# Regresa el $cfdi ya integrado con el timbre
###############################################################
// echo "\n\nPASO5. Integra el timbre recibido en \$timbre en el \$cfdi\n";
# Convierte a modelo DOM

// echo 'Timbre: ' . $timbre;



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
$xmltimbre->schemaValidate('http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd') or die("\n\n\nError de validacion\n$return");
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
unset($timbre, $xml, $sobretimbre, $xmltimbre, $paso, $complemento, $t);

if(isset($addenda) && $addenda != '') {
	include('parciales/' . $addenda);
}

// echo htmlspecialchars($cfdi);
$nombre_cfdi = $valor['factserie'][1] . $fact_num . '-' . $res_uuid;

file_put_contents(DIR_DOCS.$nombre_cfdi.'.xml', $cfdi);


		include('parciales/phpqrcode/qrlib.php');
		$ftotal = number_format($total, 6, '.', '');
		$qrtotal = strval($ftotal);
		$qrtotal = sprintf('%017s', $qrtotal);
		$codigoqr = '?re=' . $agencia_rfc . '&rr=' . $clienterfc . '&tt=' . $qrtotal . '&id=' . $res_uuid;
		$imagenqr = DIR_DOCS . $nombre_cfdi . '.png';
		QRcode::png($codigoqr, $imagenqr, 'L', 4, 2);

	$doc_nombre = 'Factura XML ' . $valor['factserie'][1] . $fact_num;
	$sql_data_array = array('doc_nombre' => $doc_nombre,
		'doc_clasificado' => 1,
		'doc_usuario' => $_SESSION['usuario'],
		'doc_archivo' => $nombre_cfdi . '.xml');
	if($orden_id != '') {
		$sql_data_array['orden_id'] = $orden_id;
	} else {
		$sql_data_array['previa_id'] = $previa_id;
	}
	ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');

	$pregtim = "UPDATE " . $dbpfx . "valores SET val_numerico = (val_numerico - 1) WHERE val_nombre = 'timbres'";
	$matrtim = mysql_query($pregtim) or die("ERROR: Fallo actualización de timbres! ".$pregtim);
	$archivo = '../logs/' . date('Ymd-i') . '-base.ase';
	$myfile = file_put_contents($archivo, $pregtim . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
   
	$doc_nombre = 'Factura PDF ' . $valor['factserie'][1] . $fact_num;
	$sql_data_array = array('doc_nombre' => $doc_nombre,
		'doc_clasificado' => 1,
		'doc_usuario' => $_SESSION['usuario'],
		'doc_archivo' => $nombre_cfdi . '.pdf');
	if($orden_id != '') {
		$sql_data_array['orden_id'] = $orden_id;
	} else {
		$sql_data_array['previa_id'] = $previa_id;
	}
	ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
	if($orden_id != '') {
		bitacora($orden_id, $doc_nombre, $dbpfx);
	} else {
		bitacora('0', $doc_nombre, $dbpfx, '', '', '', $previa_id);
	}
   
  	$sql_data = array('fact_serie' => $valor['factserie'][1],
  		'fact_num' => $fact_num,
  		'orden_id' => $orden_id,
  		'previa_id' => $previa_id,
  		'reporte' => $reporte,
  		'cliente_id' => $clienteid, 
  		'fact_rfc' => $clienterfc,
  		'fact_sub' => $suma,
  		'fact_iva' => $iva,
  		'fact_total' => $total,
  		'fact_fecha' => date('Y-m-d'),
  		'usuario' => $_SESSION['usuario']);
  	ejecutar_db($dbpfx . 'facturas', $sql_data, 'insertar');
  
  	$factcomp = $valor['factserie'][1] . $fact_num;
   
  	$sql_data = array(
  		'orden_id' => $orden_id,
  		'previa_id' => $previa_id,
  		'reporte' => $reporte,
  		'cliente_id' => $cliid,
  		'aseguradora_id' => $aseid, 
  		'fact_num' => $factcomp,
  		'fact_uuid' => $res_uuid,
  		'fact_fecha_emision' => date('Y-m-d H:i:s', time()),
  		'fact_tipo' => '1',
  		'fact_monto' => $total,
  		'usuario' => $_SESSION['usuario']);
  	ejecutar_db($dbpfx . 'facturas_por_cobrar', $sql_data, 'insertar');
  	$fact_id = mysql_insert_id();
  
  	$sql_data = array('fact_id' => $fact_id);
	foreach($tarea as $tar) {
		$param = "sub_orden_id = '" . $tar . "'";
		ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $param);
		$pregtar = "UPDATE " . $dbpfx . "subordenes SET sub_impuesto = (sub_presupuesto * " . $impuesto_iva . ") WHERE sub_orden_id = '" . $tar . "'";
		$matrtar = mysql_query($pregtar) or die("ERROR: Fallo actualización de impuesto! " . $pregtar);
		$archivo = '../logs/' . date('Ymd-i') . '-base.ase';
		$myfile = file_put_contents($archivo, $pregtar . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);

		if($orden_id != '') {
			bitacora($orden_id, 'Tarea ' . $tar . ' facturada con la factura ' . $fact_id, $dbpfx);
		} else {
			bitacora('0', 'Tarea ' . $tar . ' facturada con la factura ' . $fact_id, $dbpfx, '', '', '', $previa_id);
		}
	}

// ------ Insertar descuentos como Ajustes Administrativos ---------

	if($descuento > 0) {
		$sql_data = array(
			'fact_id' => $fact_id,
			'orden_id' => $orden_id,
			'reporte' => $reporte,
			'motivo' => 'Descuento: ' . $motivo,
			'monto' => ($descuento + ($descuento * $impuesto_iva)),
			'usuario' => $_SESSION['usuario'],
			'fecha_ajuste' => date('Y-m-d H:i:s')
		);
		ejecutar_db($dbpfx . 'ajusadmin', $sql_data, 'insertar');
		bitacora($orden_id, 'Registro de Ajuste Administrativo por descuento incluido en la factura ' . $fact_id, $dbpfx);
	}


// ------------- Redirigir a ex.php  -------------------------   

	$_SESSION['fact']['facturada'] = 1;
   if($orden_id != '') {
		redirigir('ex.php?axml=' . $nombre_cfdi . '.xml&orden_id=' . $orden_id . '&reporte=' . $reporte . '&obsad=' . $obsad);
	} else {
		redirigir('ex.php?axml=' . $nombre_cfdi . '.xml&previa_id=' . $previa_id . '&reporte=' . $reporte . '&obsad=' . $obsad);
	}   
*/
?>
