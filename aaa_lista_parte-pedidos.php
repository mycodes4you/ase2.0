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

		$consulta = "SELECT prov_id, prov_nic, prov_dde, prov_iva FROM " . $dbpfx . "proveedores ORDER BY prov_nic";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
		$num_provs = mysql_num_rows($arreglo);
		$provs = array();
//		$provs[0] = 'Sin Proveedor';
		while ($prov = mysql_fetch_array($arreglo)) {
			$provs[$prov['prov_id']] = array('nic' => $prov['prov_nic'], 'dde' => $prov['prov_dde'], 'iva' => $prov['prov_iva']);
		}
		$tcomp = ['1' => 'Aseguradora', '2' => 'Credito', '3' => 'Contado'];
//		print_r($provs);


$preg = "SELECT pedido_id, orden_id, fecha_creado, prov_id FROM " . $dbpfx . "pedidos WHERE pedido_estatus != 90 ";

if(($prov_id) || ($feini) || ($fefin) || ($pedido)) {
	if($pedido != '')  { $preg .= " AND pedido_id >= '$pedido'"; }
	if($prov_id != '')  { $preg .= " AND prov_id = '$prov_id'"; }
	if($feini != '') { $preg .= " AND fecha_creado >= '" . date('Y-m-d 00:00:00', strtotime($feini)) . "'"; }
	if($fefin != '') { $preg .= " AND fecha_creado <= '" . date('Y-m-d 23:59:59', strtotime($fefin)) . "'"; }
} else {
	$error = 'si';
}
$preg .= " LIMIT 500";

 echo $preg;

if($error == 'no') {
	$matr = mysql_query($preg) or die("ERROR: Fallo seleccion inicial! " . $preg);
	echo '<table cellpadding = "2" border="1">';
//	echo '	<tr class="cabeza"><td colspan="7">Refacciones pedidas al Proveedor ' . $prov['prov_razon_social'] . ' (' . $prov['prov_nic'] . ')</td></tr>'."\n";
	echo '	<tr><td>Pedido</td><td>OT</td><td>Proveedor</td><td>Fecha Pedido</td><td>Fecha Promesa</td><td>Fecha Recibido</td><td>Tipo de Compra</td><td>Parte</td><td>Cantidad</td><td>Costo</td><td>Precio de Venta</td></tr>'."\n";
	while ($ped = mysql_fetch_array($matr)) {
		$preg2 = "SELECT op_id, op_nombre, op_cantidad, op_costo, op_precio, sub_orden_id, op_fecha_promesa, op_ok, op_autosurtido FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $ped['pedido_id'] . "' ";
//		echo $preg2;
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos!");
		$fila2 = mysql_num_rows($matr2);
		if($fila2 > 0) {
			while($prod = mysql_fetch_array($matr2)) {
				$preg3 = "SELECT dias_entrega FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $prod['op_id'] . "' AND prod_prov_id = '" . $ped['prov_id'] . "'";
				$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de FPE!");
//				echo $preg3;
				$diasfpe = 0;
				while($den = mysql_fetch_array($matr3)) {
					$diasfpe = ($den['dias_entrega'] * 24 * 3600);
				}
				echo '	<tr><td>' . $ped['pedido_id'] . '</td><td>' . $ped['orden_id'] . '</td><td>' . $provs[$ped['prov_id']]['nic'] . '</td><td>' . date('Y-m-d', strtotime($ped['fecha_creado'])) . '</td><td>';
				$fpe = (strtotime($ped['fecha_creado']) + $diasfpe);
				echo date('Y-m-d', $fpe);
				echo '</td><td>';
				if($prod['op_ok'] == 1) {
					echo date('Y-m-d', strtotime($prod['op_fecha_promesa']));
				}
				echo '</td><td>' . $tcomp[$prod['op_autosurtido']] . '</td><td>' . $prod['op_nombre'] . '</td><td>' . $prod['op_cantidad'] . '</td><td>' . $prod['op_costo'] . '</td><td>' . $prod['op_precio'] . '</td><tr>'."\n";
			} 
		}
	}
	echo '</table>'."\n";
} else {
	echo 'faltaron datos de ingreso.';
}

?>
