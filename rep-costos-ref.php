<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

$preg = "SELECT orden_id, orden_fecha_recepcion FROM " . $dbpfx . "ordenes WHERE orden_fecha_recepcion >= '$fecha' AND orden_fecha_recepcion <= '$fecha2' AND (orden_estatus < '30' OR orden_estatus = '99')";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
$tnppe = 0; $tcppe = 0; $tvppe = 0; $ocp = 0;
$tnpop = 0; $tcpop = 0; $tvpop = 0;	$occ = 1;
echo '<table border="1">'."\n";
echo '	<tr><td>OT</td><td>Fecha de Ingreso</td><td>Cantidad<br>de Partes</td><td>Costo Total</td><td>Partes<br>con Costo</td><td>Costo Promedio<br>por parte</td></tr>'."\n";
while($ord = mysql_fetch_array($matr)) {
	$preg1 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE sub_estatus < '130' AND orden_id = '" . $ord['orden_id'] . "' AND sub_aseguradora = '1'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion!");
	$fila1 = mysql_num_rows($matr1);
	if($fila1 > 0) {
	$numots++;
	$nppe = 0; $cppe = 0; $vppe = 0; $npm = 0;
	$npop = 0; $cpop = 0; $vpop = 0; $cpm = 0;
	while($sub = mysql_fetch_array($matr1)) {
		$preg3 = "SELECT op_id, op_cantidad, op_costo, op_precio, op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '".$sub['sub_orden_id']."' AND op_tangible = '1' AND op_pres IS NULL AND op_pedido > '0' AND op_codigo NOT LIKE '%PAG%'";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de orden_productos 3!");
		while($op = mysql_fetch_array($matr3)) {
			$preg4 = "SELECT v.prov_nic FROM " . $dbpfx . "pedidos p, " . $dbpfx . "proveedores v WHERE p.pedido_id = '" . $op['op_pedido'] . "' AND p.prov_id = v.prov_id AND v.prov_nic LIKE '%RED%GNP%'";
//			echo $preg4;
			$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de proveedor!");
			$pr = mysql_num_rows($matr4);
//			$datos .= $pr['prov_id'] . ' -> ' . $prov . '<br>';
			if($pr > 0) {
				$nppe = $nppe + $op['op_cantidad'];
				$cppe = $cppe + ($op['op_cantidad'] * $op['op_costo']);
				if($op['op_costo'] > 1) {
					$npm = $npm + $op['op_cantidad'];
					$cpm = $cpm + ($op['op_cantidad'] * $op['op_costo']);
				} else {
					$occ = 0;
				}
			}
		}
	}
	$cppp = ($cpm / $npm);
	echo '	<tr><td>' . $ord['orden_id'] . '</td><td>' . $ord['orden_fecha_recepcion'] . '</td><td>' . $nppe . '</td><td>' . number_format($cppe, 2) . '</td><td>' . $npm . '</td><td>' . number_format($cppp, 2) . '</td></tr>'."\n";
	$tnppe = $tnppe + $nppe; 
	$tcppe = $tcppe + $cppe; 
	$tvppe = $tvppe + $vppe;
	$tnpm = $tnpm + $npm;  
	if($occ > 0 && $cpm > 0) { $ocp++; $tcpm = $tcpm + $cpm; }
	$occ = 1;
	}
}
$tcppp = ($tcpm / $tnpm);
echo '	<tr><td colspan="2">Totales: ' . $numots . ' OTs.</td><td>' . $tnppe . '</td><td>' . number_format($tcppe, 2) . '</td><td>' . $tnpm . '</td><td>' . number_format($tcpm, 2) . '</td></tr>'."\n";
echo '</table>'."\n";
echo '<p>Costo promedio por OT(' . $ocp . '): $' . number_format(($tcpm / $ocp), 2) . '<br>'."\n";
echo 'Costo promedio por parte cotizada: $' . number_format(($tcpm / $tnpm), 2) . '<br>'."\n";
echo ''."\n";
echo ''."\n";
echo '</p>'."\n";
?>
