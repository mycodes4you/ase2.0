<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');


if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

$preg = "SELECT cliente_id FROM " . $dbpfx . "clientes WHERE cliente_clave = ''";
$matr = mysql_query($preg) or die("ERROR: Fallo selección de clientes!");
while ($cli = mysql_fetch_array($matr)) {
	$parametros="cliente_id ='" . $cli['cliente_id'] . "'";      	
	$str = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz1234567890";
	$clave = "";
	for($i=0;$i<8;$i++) {$clave .= substr($str,rand(0,59),1);}
	$sql_data_array['cliente_clave'] = $clave;
	ejecutar_db($dbpfx . 'clientes', $sql_data_array, 'actualizar', $parametros);
}

?>
