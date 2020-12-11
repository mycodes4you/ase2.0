<?php
echo 'Hola Mundo!';

include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if ($_SESSION['usuario'] != '701' || !isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

// Comentario de descripción de función.

$preg = "SELECT fact_id, fact_num, fact_uuid FROM " . $dbpfx . "facturas_por_cobrar WHERE ";

if($factura != '') {
	$preg .= "fact_id = '" . $factura . "'";
} else {
	$preg .= "fact_id > '0'";
}
$matr = mysql_query($preg) or die("ERROR: Fallo selección de facturas! " . $preg);
// echo $preg . '<br>';
echo 'Encontradas ' . mysql_num_rows($matr) . '<br>';

while ($fact = mysql_fetch_array($matr)) {
	if(file_exists(DIR_DOCS . $fact['fact_num'] . '-' . $fact['fact_uuid'] . '.xml')) {
		$cfdi = file_get_contents(DIR_DOCS . $fact['fact_num'] . '-' . $fact['fact_uuid'] . '.xml');
		$xml = new DOMDocument();
		$xml->loadXML($cfdi) or die("\n\n\nXML no valido");
//		echo 'Lectura de elementos: <br>';
		$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
		$Emisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
		$emisor_rfc = utf8_decode($Emisor->getAttribute("Rfc"));
		$Receptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
		$receptor_rfc = utf8_decode($Receptor->getAttribute("Rfc"));
		$sqldata = array(
			'fact_rfc_emisor' => $emisor_rfc,
			'fact_rfc_receptor' => $receptor_rfc,
		);
		$param = "fact_id = '" . $fact['fact_id'] . "'";
		ejecutar_db($dbpfx . 'facturas_por_cobrar', $sqldata, 'actualizar', $param);
		unset($xml);
		$ultimo = $fact['fact_id'];
	}
}

echo 'Ultimo: ' . $ultimo;
?>
