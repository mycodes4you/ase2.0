<?php
include('parciales/funciones.php');
//include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}  // echo $k.' -> '.$v.' | ';

if ($_SESSION['usuario'] == '701' || $_SESSION['usuario'] == '1000') {
	// Aceptado
} else {
	redirigir('usuarios.php');
}


$preg0 = "SELECT pedido_id, orden_id, fecha_recibido, pedido_estatus FROM " . $dbpfx . "pedidos  WHERE pedido_id >= '" . $pedini . "' AND pedido_id <= '" . $pedfin . "' AND pedido_estatus < '90' AND pedido_pagado = 0 LIMIT 100";
$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de pedidos! " . $preg0);
while ($ped = mysql_fetch_array($matr0)) {
	if($ped['pedido_estatus'] >= '10') {
		$preg1 = "SELECT fact_id, f_rec, f_prog FROM " . $dbpfx . "facturas_por_pagar WHERE doc_int_id = '" . $ped['pedido_id'] . "' AND pagada = 0";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de facturas! " . $preg1);
		while ($fact = mysql_fetch_array($matr1)) {
			if(strtotime($fact['f_prog']) > 1000000) {
				$sqlpag = ['f_pago' => $fact['f_prog']];
			} else {
				$sqlpag = ['f_pago' => $fact['f_rec']];
			}
			$sqlpag = ['pagada' => '1', 'usuario' => '701'];
			$param = "fact_id = '" . $fact['fact_id'] . "'";
			ejecutar_db($dbpfx.'facturas_por_pagar', $sqlpag, 'actualizar', $param);
			bitacora($ped['orden_id'], 'Marcado masivo de facturas como pagadas, factura ' . $fact['fact_id'], $dbpfx);
			echo 'Factura ' . $fact['fact_id'] . ' marcada como pagada. <br>';
		}
		if($sqlpag['f_pago'] != '') { $fepag = $sqlpag['f_pago']; } else { $fepag = $ped['fecha_recibido']; }
		$sqlped = ['pedido_pagado' => '1', 'pedido_fecha_de_pago' => $fepag, 'pedido_estatus' => '99'];
		$param = "pedido_id = '" . $ped['pedido_id'] . "'";
//		print_r($sqlped);
		ejecutar_db($dbpfx.'pedidos', $sqlped, 'actualizar', $param);
		bitacora($ped['orden_id'], 'Cierre masivo de pedido ' . $ped['pedido_id'], $dbpfx);
	} else {
		$sqlped = ['pedido_pagado' => '2', 'pedido_estatus' => '90'];
		$param = "pedido_id = '" . $ped['pedido_id'] . "'";
//		print_r($sqlped);
		ejecutar_db($dbpfx.'pedidos', $sqlped, 'actualizar', $param);
		bitacora($ped['orden_id'], 'Cancelación masiva de pedidos no recibidos, pedido ' . $ped['pedido_id'], $dbpfx);
	}
	echo 'Pedido: ' . $ped['pedido_id'] . ' Estatus: ' . $sqlped['pedido_estatus'] . '<br>';
	unset($sqlpag, $sqlped);
}


/*
		$preg1 = "SELECT o.*, p.orden_id FROM " . $dbpfx . "orden_productos o, " . $dbpfx . "pedidos p WHERE o.op_pedido='" . $pedido_id . "' AND o.op_pedido = p.pedido_id";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden_productos! " . $preg1);
		$fila1 = mysql_num_rows($matr1);
		$param = "pedido_id = '" . $pedido_id . "'";
		$sql_data = array();
		if($fila1 > 0 ) {
			$completo = 1;
			$subtotal = 0; $iva = 0;
			while ($prod = mysql_fetch_array($matr1)) {
				$orden_id = $prod['orden_id'];
				if($prod['op_recibidos'] < $prod['op_cantidad']) {
					$completo = 0;
					echo $prod['op_nombre'] . ' no se ha recibido.<br>';
				} else {
					if($qv_activo == 1) {
						$opxml .= '			<Ref op_id="' . $prod['op_id'] . '" op_estatus="50" fecha_recibido="' . $prod['op_fecha_promesa'] . '" />'."\n";
					}
				}
			}
			if($qv_activo == 1 && $opxml != '') {
				$veh = datosVehiculo($orden_id, $dbpfx);
				$mtime = substr(microtime(), (strlen(microtime())-3), 3);
				$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
				$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
				$xml .= '		<Solicitud tiempo="' . time() . $mtime . '">50</Solicitud>'."\n";
				$xml .= '               <OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" col
or="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'" foto_frontal="' . $veh['foto_frontal'] .'" foto_izquierda="' . $
veh['foto_izquierda'] .'" foto_derecha="' . $veh['foto_derecha'] .'" foto_vin="' . $veh['foto_vin'] .'">'."\n";
				$xml .= $opxml;
				$xml .= '		</OT>'."\n";
				$xml .= '	</Comprador>'."\n";
				$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.xml';
				file_put_contents("../qv-salida/".$xmlnom, $xml);
			}
		} else {
			echo 'No hubo productos';
		}
*/

?>