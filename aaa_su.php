<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if ($_SESSION['usuario'] != '1000') {
//	redirigir('usuarios.php');
}

$preg = "SELECT doc_id, doc_archivo FROM " . $dbpfx . "documentos WHERE doc_id >= '" . $doc . "' AND doc_archivo NOT LIKE '%.zip' AND doc_archivo NOT LIKE 'qr-%'";
$matr = mysql_query($preg) or die("ERROR: Falló selección de documentos! " . $preg);
$archivo = '../documentos-faltantes.txt';
while ($arch = mysql_fetch_array($matr)) {
	$nombre_archivo = $arch['doc_archivo'];
	if(!file_exists(DIR_DOCS . $nombre_archivo)) {
		// --- Guardar en un log los archivos que se deben restaturar --
		$myfile = file_put_contents($archivo, $nombre_archivo . PHP_EOL , FILE_APPEND | LOCK_EX);
	}
	$myfile = file_put_contents('../documentos-contador.txt', $arch['doc_id'] . PHP_EOL , FILE_APPEND | LOCK_EX);
}

?>

