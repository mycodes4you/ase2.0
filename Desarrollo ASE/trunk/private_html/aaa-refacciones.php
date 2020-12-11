<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

$tcomp = ['1' => 'Aseguradora', '2' => 'Credito', '3' => 'Contado'];

// ---- Calcular refacciones gestionadas en el mes de Abril 2019 ---
$preg_refs = "SELECT r.sub_orden_id, r.op_id, r.op_costo, r.op_precio, r.op_autosurtido, r.op_cantidad, r.op_nombre, r.op_costo, r.op_pedido, p.fecha_creado FROM " . $dbpfx . "orden_productos r, " . $dbpfx . "pedidos p WHERE r.op_pedido = p.pedido_id AND (p.fecha_creado >= '2019-06-01 00:00:00' AND p.fecha_creado <= '2019-06-06 23:59:59') ORDER BY p.pedido_id";

echo $preg_refs . '<br><br>';

$matr_refs = mysql_query($preg_refs) or die("ERROR: Fallo selección de REFACCIONES! " . $preg_refs);
$total_refs_gestionadas = mysql_num_rows($matr_refs);

echo '<table cellpadding = "2" border="1">';
echo '	<tr><td>Pedido</td><td>Orden</td><td>Fecha pedido</td><td>Cantidad</td><td>Refacción</td><td>Costo</td><td>Tipo de Compra</td></tr>'."\n";

while($refs = mysql_fetch_array($matr_refs)){
	
	$preg_orden = "SELECT orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $refs['sub_orden_id'] . "'";
	$mtr_orden = mysql_query($preg_orden);
	$ord = mysql_fetch_assoc($mtr_orden);
	
	echo '
		<tr>
			<td>' . $refs['op_pedido'] . '</td>
			<td>' . $ord['orden_id'] . '</td>
			<td>' . date('Y-m-d', strtotime($refs['fecha_creado'])) . '</td>
			<td>' . $refs['op_cantidad'] . '</td>
			<td>' . $refs['op_nombre'] . '</td>
			<td>$' .  number_format($refs['op_costo'], 2) . '</td>
			<td>' . $tcomp[$refs['op_autosurtido']] . '</td>
		</tr>'."\n";
	
}


// --- Calcular las refacciones gestionadas con cargo a la aseguradora ---
//$preg_refs_aseg = "SELECT r.op_nombre, r.op_costo, r.op_pedido, p.fecha_creado, r.op_autosurtido FROM " . $dbpfx . "orden_productos r, " . $dbpfx . "pedidos p WHERE r.op_pedido = p.pedido_id AND (p.fecha_creado >= '2019-04-01 00:00:00' AND p.fecha_creado <= '2019-04-30 23:59:59') AND r.op_autosurtido = '1' ORDER BY p.pedido_id";

//echo $preg_refs_aseg . '<br><br>';

//$matr_refs_aseg = mysql_query($preg_refs_aseg) or die("ERROR: Fallo selección de REFACCIONES de Aseguradora! " . $preg_refs_aseg);
//$total_refs_aseg = mysql_num_rows($matr_refs_aseg);

// --- total de refacciones Gestionadas a cargo del taller ---
//$preg_refs_taller = "SELECT r.op_nombre, r.op_costo, r.op_pedido, p.fecha_creado, r.op_autosurtido FROM " . $dbpfx . "orden_productos r, " . $dbpfx . "pedidos p WHERE r.op_pedido = p.pedido_id AND (p.fecha_creado >= '2019-04-01 00:00:00' AND p.fecha_creado <= '2019-04-30 23:59:59') AND (r.op_autosurtido = '2' OR r.op_autosurtido = '3') ORDER BY p.pedido_id";

//echo $preg_refs_taller . '<br><br>';

//$matr_refs_taller = mysql_query($preg_refs_taller) or die("ERROR: Fallo selección de REFACCIONES de Aseguradora! " . //$preg_refs_taller);
//$total_refs_taller = mysql_num_rows($matr_refs_taller);

// ---- calcular el costo total de las refacciones gestionadas a cargo del taller ----
//$total_costo = 0;
//while($refs = mysql_fetch_array($matr_refs_taller)){
	//$total_costo = $total_costo + $refs['op_costo'];
//}


// --- Calcular porcentaje de $total_refs_aseg ---
//$porcentaje_aseg = ($total_refs_aseg * 100) / $total_refs_gestionadas;
//$porcentaje_aseg = round($porcentaje_aseg, 2);

//$porcentaje_taller = ($total_refs_taller * 100) / $total_refs_gestionadas;
//$porcentaje_taller = round($porcentaje_taller, 2);


//echo '<br><br>El total de refacciones gestionadas en el mes de Abril 2019 fue de: ' . $total_refs_gestionadas . '<br>';
//echo '<br><br>El total de refacciones gestionadas en el mes de Abril 2019 con cargo a la aseguradora fue de: ' . $total_refs_aseg . '<br>';
//echo '<br><br>El total de refacciones gestionadas en el mes de Abril 2019 a cargo del taller fue de: ' . $total_refs_taller . '<br>';
//echo '<br><br>El costo total de refacciones gestionadas en el mes de Abril 2019 a cargo del taller fue de: $' . number_format($total_costo, 2) . '<br>';
//echo '<br><br>El procentaje de refacciones gestionadas a cargo de la aseguradora en el mes de Abril 2019 es de : ' . $porcentaje_aseg . '%<br>';
//echo '<br><br>El procentaje de refacciones gestionadas a cargo del taller en el mes de Abril 2019 es de : ' . $porcentaje_taller . '%<br>';





?>