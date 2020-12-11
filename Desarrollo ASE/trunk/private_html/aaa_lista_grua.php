<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

$error = 'no';
$preg0 = "SELECT orden_id, orden_vehiculo_marca, orden_vehiculo_tipo, orden_vehiculo_color, orden_vehiculo_placas, orden_grua, orden_estatus, orden_fecha_recepcion, orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes WHERE orden_id >= '21600'";
$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de ordenes! " . $preg0);
$ord = mysql_fetch_array($matr0);

if($error == 'no') {
	echo '<table cellpadding = "2" border="1">'."\n";
	echo '	<tr><td>OT</td><td>Vehiculo</td><td>Placas</td><td>Reporte</td><td>Grua</td><td>Estatus</td><td>Fecha Recibido</td><td>Ultimo Movimiento</td></tr>'."\n";
	while ($ord = mysql_fetch_array($matr0)) {
		$preg1 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' GROUP BY sub_reporte";
//		echo $preg2;
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de tareas! " . $preg1);
		while($sub = mysql_fetch_array($matr1)) {
			echo '	<tr><td>' . $ord['orden_id'] . '</td><td>' . $ord['orden_vehiculo_marca'] . ' ' . $ord['orden_vehiculo_tipo'] . ' ' . $ord['orden_vehiculo_color'] . '</td><td>' . $ord['orden_vehiculo_placas'] . '</td><td>';
			if($sub['sub_reporte'] != '' && $sub['sub_reporte'] != '0') { echo $sub['sub_reporte']; }
			else { echo 'Particular'; }
			echo '</td><td>';
			if($ord['orden_grua'] == 1) { echo 'Sí'; }
			elseif($ord['orden_grua'] == 2) { echo 'No'; }
			else { echo '--'; }
			echo '</td><td>' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '</td><td>' . date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])) . '</td><td>' . date('Y-m-d', strtotime($ord['orden_fecha_ultimo_movimiento'])) . '</td><tr>'."\n";
		}
	}
	echo '</table>'."\n";
} else {
	echo 'faltaron datos de ingreso.';
}

?>
