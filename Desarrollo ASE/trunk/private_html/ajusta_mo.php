<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}

// --- Variables: $aseg = Num aseguradora; $subid = Tarea inicial; $hora = nuevo precio de hora de Mano de Obra ---

	$preg = "SELECT sub_orden_id, orden_id FROM " . $dbpfx . "subordenes WHERE sub_estatus < '190' AND sub_aseguradora = '" . $aseg . "' AND sub_orden_id >= '" . $subid . "' LIMIT 100";
	$matr = mysql_query($preg) or die("ERROR: Fallo seleccion! " . $preg);
//	echo $preg . '<br><br>';
	while($sub = mysql_fetch_array($matr)) {
		echo 'Procesando tarea: ' . $sub['sub_orden_id'] . ' de la OT ' . $sub['orden_id'] . '<br>';
		$preg3 = "SELECT op_id, op_subtotal FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_tangible = '0' AND op_pres IS NULL";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de orden_productos 3! " . $preg3);
//		echo $preg3 . '<br>';
		$estimadas = 0;
		while($op = mysql_fetch_array($matr3)) {
			$cantidad = round(($op['op_subtotal'] / $hora), 6);
			$estimadas = $estimadas + $cantidad;
			$param = "op_id = '" . $op['op_id'] . "'";
			$sql_data = array('op_cantidad' => $cantidad, 'op_precio' => $hora);
			ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
		}
		$horas = intval($estimadas);
		$minutos = round((($estimadas - $horas)*60), 2);
		if($minutos==0) {$minutos='00';}
		$sql_data = array('sub_horas_programadas' => $horas . ':' . $minutos);
		$param = "sub_orden_id = '" . $sub['sub_orden_id'] . "'";
		ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $param);
	}
?>
