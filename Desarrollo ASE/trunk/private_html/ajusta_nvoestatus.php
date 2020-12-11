<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if ($_SESSION['usuario'] == '701' || $_SESSION['usuario'] == '1000') {
	// --- Acceso permitido ---
} else {
	redirigir('usuarios.php');
}

$preg = "SELECT sub_orden_id, sub_estatus FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190'";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
while ($sub = mysql_fetch_array($matr)) {
	$preg1 = "UPDATE " . $dbpfx . "subordenes SET sub_estatus = '" . ($estatus + 100) . "' WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo actualización! " . $preg1);
}
actualiza_orden ($orden_id, $dbpfx);

?>
