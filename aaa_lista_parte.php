<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

//$prov_id = limpiarNumero($prov_id);

$error = 'no';

/*  ----------------  obtener nombres de proveedores   ------------------- */

		$consulta = "SELECT prov_id, prov_nic FROM " . $dbpfx . "proveedores";
		$arreglo = mysql_query($consulta) or die("ERROR: Falló selección de proveedores!" . $consulta);
		$num_provs = mysql_num_rows($arreglo);
//		$aseg = array();
		$provs[0] = 'Sin Proveedor';
		while ($pro = mysql_fetch_array($arreglo)) {
			$provs[$pro['prov_id']]['nic'] = $pro['prov_nic'];
		}
//		$aseg[0] = 'Particular';
		$tcomp = ['1' => 'Aseguradora', '2' => 'Credito', '3' => 'Contado'];
//		print_r($provs);

$preg = "SELECT pedido_id, orden_id, fecha_creado, fecha_recibido, prov_id FROM " . $dbpfx . "pedidos WHERE pedido_estatus != 90 AND fecha_creado >= '" . date('Y-m-d 00:00:00', strtotime($feini)) . "' AND fecha_creado <= '" . date('Y-m-d 23:59:59', strtotime($fefin)) . "'";
//$preg = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE (orden_estatus < '90' OR orden_estatus = '99') AND orden_fecha_recepcion >= '" . $feini . "' AND orden_fecha_recepcion <= '" . $fefin . "'";

// AND orden_id >= '" . $orden . "'";

/*
if(($prov_id) || ($feini) || ($fefin) || ($pedido)) {
	if($pedido != '')  { $preg .= " AND pedido_id >= '$pedido'"; }
	if($prov_id != '')  { $preg .= " AND prov_id = '$prov_id'"; }
	if($feini != '') { $preg .= " AND fecha_creado >= '" . date('Y-m-d 00:00:00', strtotime($feini)) . "'"; }
	if($fefin != '') { $preg .= " AND fecha_creado <= '" . date('Y-m-d 23:59:59', strtotime($fefin)) . "'"; }
} else {
	$error = 'si';
}
*/

//$preg .= " LIMIT 500";

//echo $preg;

if($error == 'no') {
	$matr = mysql_query($preg) or die("ERROR: Fallo seleccion inicial! " . $preg);
	echo '<table cellpadding = "0" cellspacing = "0" border="1">';
//	echo '	<tr class="cabeza"><td colspan="7">Refacciones pedidas al Proveedor ' . $prov['prov_razon_social'] . ' (' . $prov['prov_nic'] . ')</td></tr>'."\n";
	echo '	<tr><td>Pedido</td><td>F Creado</td><td>F Recibido</td><td>ProvID</td><td>Proveedor</td><td>Parte</td><td>Costo</td><td>OT</td><td>Vehículo</td></tr>'."\n";
	while ($ped = mysql_fetch_array($matr)) {
		$veh = datosVehiculo($ped['orden_id'], $dbpfx);
//		$preg1 = "SELECT sub_orden_id, sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE sub_estatus < '130' AND orden_id = '" . $ord['orden_id'] . "'";
//		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de tareas! " . $preg1);
//		while($ord = mysql_fetch_array($matr1)) {
			$preg2 = "SELECT op_id, op_item_seg, op_nombre, op_cantidad, op_costo, op_precio, op_autosurtido FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $ped['pedido_id'] . "' AND op_tangible = '1'";
//		echo $preg2;
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos!");
//			$fila2 = mysql_num_rows($matr2);
//			if($fila2 > 0) {
				while($prod = mysql_fetch_array($matr2)) {
//					if($prod['op_precio'] == 0 || $prod['op_precio'] == '' || is_null($prod['op_precio'])) {
//						$preg3 = "SELECT op_precio FROM " . $dbpfx . "orden_productos WHERE op_item_seg = '" . $prod['op_id'] . "'";
//						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de productos!" . $preg3);
//						$seg = mysql_fetch_assoc($matr3);
//						$precio = round(($prod['op_cantidad'] * $seg['op_precio']), 2);
//					} else {
						$precio = round(($prod['op_cantidad'] * $prod['op_precio']), 2);
//					}
					$costo = round(($prod['op_cantidad'] * $prod['op_costo']), 2);
					$utilidad = round((($precio - $costo) / $precio),2);
					if(strtotime($ped['fecha_recibido']) > 1000000) {
						$ferec = date('Y-m-d', strtotime($ped['fecha_recibido']));
					} else {
						$ferec = '';
					}
					echo '	<tr><td>' . $ped['pedido_id'] . '</td><td>' . date('Y-m-d', strtotime($ped['fecha_creado'])) . '</td><td>' . $ferec . '</td><td>' . $ped['prov_id'] . '</td><td>' . $provs[$ped['prov_id']]['nic'] . '</td><td>' . $prod['op_nombre'] . '</td><td style="text-align:right;">' . number_format($costo,2) . '</td><td>' . $ped['orden_id'] . '</td><td>' . $veh['completo'] . '</td><tr>'."\n";
				}
//			}
//		}
	}
	echo '</table>'."\n";
} else {
	echo 'faltaron datos de ingreso.';
}

?>
