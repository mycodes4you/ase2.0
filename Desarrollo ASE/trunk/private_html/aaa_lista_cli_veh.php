<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

include('parciales/encabezado.php');
echo '	<div id="body">';
include('parciales/menu_inicio.php');
echo '		<div id="principal">';


$error = 'no';
$preg0 = "SELECT c.cliente_id, c.cliente_nombre, c.cliente_apellidos, c.cliente_tipo, c.cliente_email, c.cliente_telefono1, c.cliente_telefono2, c.cliente_movil, c.cliente_movil2, v.vehiculo_placas, v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_subtipo, v.vehiculo_modelo, v.vehiculo_color FROM " . $dbpfx . "clientes c, " . $dbpfx . "vehiculos v WHERE v.vehiculo_cliente_id = c.cliente_id ORDER BY c.cliente_nombre";
$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de clientes y vehículos! " . $preg0);

if($error == 'no') {
	echo '<table cellpadding = "2" border="1">'."\n";
	echo '	<tr><td>Cliente ID</td><td>Nombre</td><td>Apellidos</td><td>Tipo de Contacto</td><td>E Mail</td><td>Teléfonos</td><td>Marca</td><td>Tipo</td><td>Sub Tipo</td><td>Año</td><td>Color</td><td>Placas</td></tr>'."\n";
	while ($cli = mysql_fetch_array($matr0)) {
		echo '	<tr><td>' . $cli['cliente_id'] . '</td><td>' . $cli['cliente_nombre'] . '</td><td>' . $cli['cliente_apellidos'] . '</td><td>';
		if($cli['cliente_tipo'] == 1) { echo 'Asegurado'; } else { echo 'Tercero'; }
		echo '</td><td>' . $cli['cliente_email'] . '</td><td>' . $cli['cliente_telefono1'] . ' ' . $cli['cliente_telefono2'] . ' ' . $cli['cliente_movil'] . ' ' . $cli['cliente_movil2'] . '</td><td>' . $cli['vehiculo_marca'] . '</td><td>' . $cli['vehiculo_tipo'] . '</td><td>' . $cli['vehiculo_subtipo'] . '</td><td>' . $cli['vehiculo_modelo'] . '</td><td>' . $cli['vehiculo_color'] . '</td><td>' . $cli['vehiculo_placas'] . '</td></tr>'."\n";
	}
	echo '</table>'."\n";
} else {
	echo 'faltaron datos de ingreso.';
}

?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>