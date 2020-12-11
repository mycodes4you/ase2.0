<?php
foreach($_GET as $k => $v){$$k=$v;}  // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}

if($ord != '') {
	$preg1 = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_id = '$ord' AND orden_estatus < '90' LIMIT 1";
} elseif($orden_ini != '' && $orden_fin != '') {
	$preg1 = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_id >= '$orden_ini' AND orden_id <= '$orden_fin' AND orden_estatus < '90' LIMIT 100";
}
$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de OT! " . $preg1);
$fila1 = mysql_num_rows($matr1);
echo 'Encontradas: ' . $fila1 . '<br>';

if($fila1 > 0) {
	while ($ord = mysql_fetch_array($matr1)) {
		$orden = $ord['orden_id'];
		echo 'OT: ' . $orden . '<br>';
		$preg = "SELECT sub_orden_id, sub_estatus FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden' AND sub_estatus < '190' AND sub_estatus != '112'";
		$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");

		while ($sub = mysql_fetch_array($matr)) {
/*			if($sub['sub_estatus'] < '130') {
				$preg0 = "SELECT o.op_pedido FROM " . $dbpfx . "orden_productos o, " . $dbpfx . "pedidos p WHERE o.sub_orden_id = '" . $sub['sub_orden_id'] . "' AND o.op_pedido > '0' AND p.pedido_estatus < '10' AND o.op_pedido = p.pedido_id";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de pedidos !" . $preg0);
				while ($ped = mysql_fetch_array($matr0)) {
					$res = "UPDATE " . $dbpfx . "pedidos SET pedido_estatus = '90', pedido_pagado = '0', observaciones = 'Cierre masivo " . date('Ymd') . "' WHERE pedido_id = '" . $ped['op_pedido'] . "'";
					$aplica = mysql_query($res) or die("ERROR: Fallo actualización de pedido!" . $res);
					$res = "UPDATE " . $dbpfx . "orden_productos SET op_ok = '1', op_pedido = '0' WHERE op_pedido = '" . $ped['op_pedido'] . "'";
					$aplica = mysql_query($res) or die("ERROR: Fallo actualización de productos!" . $res);
				}
				$res = "UPDATE " . $dbpfx . "orden_productos SET op_ok = '1' WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_tangible = '1' AND op_pedido < '1'";
				$aplica = mysql_query($res) or die("ERROR: Fallo actualización de productos!" . $res);
				$res = "UPDATE " . $dbpfx . "subordenes SET sub_refacciones_recibidas = '0', sub_estatus = '112' WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
				$aplica = mysql_query($res) or die("ERROR: Fallo actualización de tarea!" . $res);
			} else {
*/				$res = "UPDATE " . $dbpfx . "subordenes SET sub_refacciones_recibidas = '0' WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
				$aplica = mysql_query($res) or die("ERROR: Fallo actualización de tarea!" . $res);
//			}
		}

		$res = "UPDATE " . $dbpfx . "ordenes SET orden_estatus = '210', orden_fecha_de_entrega = orden_fecha_ultimo_movimiento, orden_alerta = '0', orden_ref_pendientes = '0', orden_ubicacion = 'Cerrado - En Revisión' WHERE orden_id = '$orden' ";
		$aplica = mysql_query($res) or die("ERROR: Fallo actualización de OT!" . $res);
		bitacora($orden, 'Cierre masivo ' . date('Ymd') . ' Depuración', $dbpfx, 'Cierre masivo ' . date('Ymd') . ' Depuración', '0');
	}
}

?>
