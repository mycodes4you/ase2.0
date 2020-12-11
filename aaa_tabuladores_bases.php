<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

$preg = "SELECT o.orden_vehiculo_marca, o.orden_vehiculo_tipo, s.sub_orden_id FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE s.sub_descripcion LIKE 'golpe%frontal' AND o.orden_id = s.orden_id AND s.sub_orden_id > '" . $tarea . "' LIMIT 500";

$matr = mysql_query($preg) or die("ERROR: Fallo seleccion de subordenes! " . $preg);
echo '<table cellpadding = "2" border="1">';
echo '	<tr><td>Cantidad</td><td>Nombre</td><td>Código</td><td>Costo</td><td>Precio</td><td>Tangible</td><td>Marca</td><td>Tipo</td></tr>'."\n";
while ($ord = mysql_fetch_array($matr)) {
	$preg2 = "SELECT op_cantidad, op_nombre, op_codigo, op_costo, op_precio, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $ord['sub_orden_id'] . "'";
//		echo $preg2;
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos!");
	while($prod = mysql_fetch_array($matr2)) {
		echo '	<tr><td>' . $prod['op_cantidad'] . '</td><td>' . $prod['op_nombre'] . '</td><td>' . $prod['op_codigo'] . '</td><td>' . $costo . '</td><td>' . $precio . '</td><td>' . $prod['op_tangible'] . '</td><td>' . $ord['orden_vehiculo_marca'] . '</td><td>' . $ord['orden_vehiculo_tipo'] . '</td><tr>'."\n";
	}
	$tarea = $ord['sub_orden_id'];
}
echo '</table>'."\n";
echo $tarea;

?>
