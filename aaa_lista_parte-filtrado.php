<?php
foreach($_GET as $k => $v) {$$k=$v;}

include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

$prov_id = limpiarNumero($prov_id);

$error = 'no';
$preg0 = "SELECT prov_nic, prov_razon_social FROM " . $dbpfx . "proveedores WHERE prov_id = '$prov_id'";
$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de proveedor! " . $preg0);
$prov = mysql_fetch_array($matr0);

$preg = "SELECT pedido_id, orden_id, fecha_creado FROM " . $dbpfx . "pedidos WHERE pedido_estatus != 90 ";
if(($prov_id) || ($feini) || ($fefin)) {
	if($prov_id != '')  { $preg .= " AND prov_id = '$prov_id'"; }
	if($feini != '') { $preg .= " AND fecha_creado >= '" . date('Y-m-d 00:00:00', strtotime($feini)) . "'"; }
	if($fefin != '') { $preg .= " AND fecha_creado <= '" . date('Y-m-d 23:59:59', strtotime($fefin)) . "'"; }
} else {
	$error = 'si';
}
 echo $preg;
if($error == 'no') {
	$matr = mysql_query($preg) or die("ERROR: Fallo seleccion inicial! " . $preg);
	echo '<table cellpadding = "2" border="1">
	<tr class="cabeza"><td colspan="7">Refacciones pedidas al Proveedor ' . $prov['prov_razon_social'] . ' (' . $prov['prov_nic'] . ')</td></tr>'."\n";
	echo '	<tr><td>OT</td><td>Pedido</td><td>Fecha Pedido</td><td>Parte</td><td>Codigo</td><td>Costo</td><td>Precio de Venta</td></tr>'."\n";
	while ($ped = mysql_fetch_array($matr)) {
		$preg2 = "SELECT op_codigo, op_nombre, op_costo, op_precio, sub_orden_id FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $ped['pedido_id'] . "' AND (op_nombre LIKE '%mangueta%' OR op_nombre LIKE '%maza%' OR op_nombre LIKE '%flecha%' OR op_nombre LIKE '%direcci%' OR op_nombre LIKE '%horquilla%' OR op_nombre LIKE '%brazo%de%direcc%' OR op_nombre LIKE '%tornillo%' OR op_nombre LIKE '%estabilizado%' OR op_nombre LIKE '%puente%del%' OR op_nombre LIKE '%eje%trasero%')";
//		echo $preg2;
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos!");
		$fila2 = mysql_num_rows($matr2);
		if($fila2 > 0) {
			while($prod = mysql_fetch_array($matr2)) {
				echo '	<tr><td>' . $ped['orden_id'] . '</td><td>' . $ped['pedido_id'] . '</td><td>' . $ped['fecha_creado'] . '</td><td>' . $prod['op_nombre'] . '</td><td>' . $prod['op_codigo'] . '</td><td>' . $prod['op_costo'] . '</td><td>' . $prod['op_precio'] . '</td><tr>'."\n";
			}
		}
	}
	echo '</table>'."\n";
} else {
	echo 'faltaron datos de ingreso.';
}

?>
