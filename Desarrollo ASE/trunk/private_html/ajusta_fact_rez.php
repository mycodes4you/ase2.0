<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}


if ($_SESSION['usuario'] != '701' || !isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}


// Comentario de descripción de función.

$preg = "SELECT o.orden_id, o.orden_cliente_id, o.orden_presupuesto, o.orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes o WHERE o.orden_id <= '" . $orden . "' AND NOT EXISTS (SELECT f.fact_id FROM " . $dbpfx . "facturas_por_cobrar f WHERE f.orden_id = o.orden_id)";
$matr = mysql_query($preg) or die("ERROR: Fallo selección de OTs y facturas! " . $preg);
// echo $preg . '<br>';
echo 'Encontradas ' . mysql_num_rows($matr) . '<br>';
$factnum = 1000001;
while ($ord = mysql_fetch_array($matr)) {
	$preg0 = "SELECT sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE sub_estatus < '130' AND orden_id = '" . $ord['orden_id'] . "' AND fact_id IS NULL GROUP BY sub_reporte";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Tareas! " . $preg0);
	while ($gsub = mysql_fetch_array($matr0)) {
		echo 'Procesando ' . $ord['orden_id'] . ' Reporte ' . $gsub['sub_reporte'] . '<br>';
		$monto = 0;
		$preg1 = "SELECT sub_presupuesto FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_reporte = '" . $gsub['sub_reporte'] . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Tareas! " . $preg1);
		while ($sub = mysql_fetch_array($matr1)) {
			$monto = $monto + $sub['sub_presupuesto'];
		}
		$sqldata = array(
			'orden_id' => $ord['orden_id'],
			'reporte' => $gsub['sub_reporte'],
			'aseguradora_id' => $gsub['sub_aseguradora'],
			'fact_num' => 'FIC'.$factnum,
			'fact_fecha_emision' => $ord['orden_fecha_ultimo_movimiento'],
			'fact_tipo' => '1', 
			'fact_monto' => $monto,
			'fact_descripcion' => 'Factura ficticia por depuracion',
			'fact_cobrada' => '1',
			'fact_fecha_cobrada' => $ord['orden_fecha_ultimo_movimiento'],
			'usuario' => '701'
		);
		if($gsub['sub_aseguradora'] == '0') { $sqldata['cliente_id'] = $ord['orden_cliente_id']; }
		ejecutar_db($dbpfx . 'facturas_por_cobrar', $sqldata, 'insertar');
		$fact_id = mysql_insert_id();
		unset($sqldata);
		$param = "orden_id = '" . $ord['orden_id'] . "' AND sub_reporte = '" . $gsub['sub_reporte'] . "'";
		$sqldata = array('fact_id' => $fact_id);
		ejecutar_db($dbpfx . 'subordenes', $sqldata, 'actualizar', $param);
		unset($sqldata);
		$factnum++;
	}
}






?>
