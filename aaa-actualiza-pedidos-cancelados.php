<?php
// ******************************************************************************************
// ********************* Scrip para actualizar pedidos cancelados ***************************
// ******************************************************************************************

include('parciales/funciones.php');
foreach($_GET as $k => $v){$$k = limpiar_cadena($v);}  // echo $k.' -> '.$v.' | ';

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

function actualiza_pedidos_especial($pedido_id, $dbpfx, $btc) {

// ------ Determinar el estatus del pedido 

		// --- Determinar estatus de items del pedido para determinar el estatus base (0, 5, 7 ó 10) del pedido
		$preg1 = "SELECT op_recibidos, op_cantidad, op_costo FROM " . $dbpfx . "orden_productos WHERE op_pedido='" . $pedido_id . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden_productos!");
		$fila1 = mysql_num_rows($matr1);
		$sql_data = array();
		if($fila1 > 0 ) {
			$recibido = 1;
			$subtotal = 0; $iva = 0;
			while ($prod = mysql_fetch_array($matr1)) {
				if($prod['op_recibidos'] < $prod['op_cantidad']) {
					$recibido = 0;
				}
				$subtotal = $subtotal + round(($prod['op_cantidad'] * $prod['op_costo']), 2);
			}
			$sql_data['subtotal'] = $subtotal;

			$preg_pedido = "SELECT prov_id, orden_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $pedido_id . "'";
			$matr_pedido = mysql_query($preg_pedido) or die("ERROR: Fallo selección de pedido! " . $preg_pedido);
			$pedido = mysql_fetch_array($matr_pedido);
			$orden_id = $pedido['orden_id'];

			$preg_prov = "SELECT prov_iva FROM " . $dbpfx . "proveedores WHERE prov_id = '" . $pedido['prov_id'] . "'";
			$matr_prov = mysql_query($preg_prov) or die("ERROR: Fallo selección de proveedor! " . $preg_prov);
			$prov = mysql_fetch_array($matr_prov);

			$sql_data['impuesto'] = round(($subtotal * $prov['prov_iva']), 2);
			$monto_pedido = $subtotal + $sql_data['impuesto'];

			// --- determinar monto de las facturas del pedido y sus pagos ---
			$preg_facturas = "SELECT f_monto, fact_id FROM " . $dbpfx . "facturas_por_pagar WHERE doc_int_id = '" . $pedido_id . "' AND pagada < '2'";
			$matr_facturas = mysql_query($preg_facturas) or die("ERROR: Fallo selección de facturas! " . $preg_facturas);
			$monto_facturas = 0;
			$monto_pagos = 0;
			while($facturas = mysql_fetch_array($matr_facturas)) {
				$monto_facturas = $monto_facturas + round($facturas['f_monto'],2);
				// --- determinar monto de los pagos de cada factura
				$preg_pagos = "SELECT monto FROM " . $dbpfx . "pagos_facturas WHERE pedido_id = '" . $pedido_id . "' AND fact_id = '" . $facturas['fact_id'] . "'";
				$matr_pagos = mysql_query($preg_pagos) or die("ERROR: Fallo selección de pagos!" . $preg_pagos);
				while($pagos = mysql_fetch_array($matr_pagos)) {
					$monto_pagos = $monto_pagos + round($pagos['monto'],2);
				}
			}

// ------ Determinar monto de anticipos ---
			$preg_adelantos = "SELECT monto FROM " . $dbpfx . "pagos_facturas WHERE pedido_id = '" . $pedido_id . "' AND fact_id < 1";
			$matr_adelantos = mysql_query($preg_adelantos) or die("ERROR: Fallo selección de pagos!" . $preg_adelantos);
			$monto_adelantos = 0;
			while($adelantos = mysql_fetch_array($matr_adelantos)) {
				$monto_adelantos = $monto_adelantos + round($adelantos['monto'],2);
			}

			$pagado = round(($monto_pagos + $monto_adelantos),2);

			if($recibido == 0) {
				// --- Determinar estatus de pedidos no recibidos
				if($pagado > 0) {
					$coment = 'Anticipo, Items por Recibir';
					$sql_data['pedido_estatus'] = '7';
				} else {
					$coment = 'Pedido recibido por proveedor';
					$sql_data['pedido_estatus'] = '5';
				}
			} else {
				// --- Determinar estatus de pedidos recibidos
				if($monto_facturas == '0' && $monto_adelantos == '0') {
					$coment = 'Items Recibidos sin pagos y sin registro de documentos de pago';
					$sql_data['pedido_estatus'] = '10';
				} elseif($monto_pedido > $monto_facturas && $monto_facturas > '0' && $pagado == '0') {
					$coment = 'Registro parcial de Documentos de pago sin pagos registrados';
					$sql_data['pedido_estatus'] = '15';
				} elseif($monto_facturas >= $monto_pedido && $pagado == '0') {
					if($monto_facturas >= $monto_pedido){
						$mensaje = 'Los montos de las facturas de este pedido exceden el monto del pedido';
					}
					$coment = 'Documentos de cobro registrados sin pagos registrados';
					$sql_data['pedido_estatus'] = '20';
				} elseif($pagado < $monto_pedido) {
					$coment = 'Pedido pagado parcialmente, Items recibidos';
					$sql_data['pedido_estatus'] = '25';
				} elseif($pagado >= $monto_pedido && $monto_pedido > $monto_facturas) {
					$coment = 'Pedido pagado sin documentos de pago completos';
					$sql_data['pedido_estatus'] = '30';
				} elseif($pagado >= $monto_pedido) {
					$coment = 'Pedido terminado: recibido y pagado';
					$sql_data['pedido_estatus'] = '99';
				} else {
// ------ Pedido en estatus no identificado
					$sql_data['pedido_estatus'] = '95';
					bitacora($orden_id, 'Pedido en revisión de Soporte Técnico.', $dbpfx, 'Pedido ' . $pedido_id . ' con estatus indeterminado', 3, '', '', 701);
				}
			}
		} else {
			$sql_data['pedido_estatus'] = '90';
			$sql_data['subtotal'] = '0';
			$sql_data['impuesto'] = '0';
			$coment = 'Pedido Cancelado';
		}
		if($btc == 1) {
			bitacora($orden_id, 'Pedido ' . $pedido_id . ': ' . $coment, $dbpfx);
		}
		$parametros = " pedido_id ='" . $pedido_id . "'";
		ejecutar_db($dbpfx . 'pedidos', $sql_data, 'actualizar', $parametros);
		return $mensaje;
	}

if($inicio == '' || $fin == ''){ // No ejecutamos script
	
	echo 'No se puede ejecutar el script sin el rango de los pedidos<br>';
	
} else{ // Ejecutamos script

	
	$preg0 = "SELECT pedido_id, pedido_estatus, orden_id FROM " . $dbpfx . "pedidos WHERE pedido_id >= '" . $inicio . "' AND pedido_id <= '" . $fin . "' LIMIT 50";
	
	echo "<br>Query a ejecutar " . $preg0 . "<br><br>Pedidos a procesar del <b>" . $inicio . "</b> al <b>" . $fin . "</b><br>";
	
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de pedidos!" . $preg0);
	$filas = mysql_num_rows($matr0);
	
	if($filas > 0){
		
		echo 'Procesando...<br>';
		// dormir durante 10 segundos
		sleep(1);
		$fp = fopen("particular/pedidos_procesados.txt","a");
		// comienza ejecución
		fwrite($fp, date('h:i:s') . PHP_EOL);
		
		while($pedidos = mysql_fetch_array($matr0)){
		
			$texto = 'Pedido ' . $pedidos['pedido_id'] . ' , estatus = ' .  $pedidos['pedido_estatus'] . '';
		
			// --- Ejecutar actualiza apedido ----
			$mensaje = actualiza_pedidos_especial($pedidos['pedido_id'], $dbpfx, '');
		
			if($mensaje != ''){
				$texto .= ' Mensaje '	. $mensaje;
			}
			// --- Consultar el nuevo estatus del pedido ---
			$preg_estatus = "SELECT pedido_estatus FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $pedidos['pedido_id'] . "'";
			$matr_estatus = mysql_query($preg_estatus) or die("ERROR: Fallo selección de pedidos!" . $preg_estatus);
			$estatus = mysql_fetch_assoc($matr_estatus);
		
			if($pedidos['pedido_estatus'] == $estatus['pedido_estatus']){
				$texto .= ' -- No se actualizo el pedido';
			} else{
				$texto .= ' -- Se actualizo el pedido al estatus ' . $estatus['pedido_estatus'] . '';
			}
			// --- Guardar texto en el archivo ---
			fwrite($fp, $texto . PHP_EOL);
			
		}
		// --- Termina ejecucion ---
		fwrite($fp, date('h:i:s') . PHP_EOL);
		fclose($fp);
	
		// --- Redirigir aumentado el rango en 50 ---
		$nuevo_inicio = $fin + 1;
		$nuevo_fin = $fin + 50;
		redirigir('aaa-actualiza-pedidos-cancelados.php?inicio=' . $nuevo_inicio . '&fin=' . $nuevo_fin . '');
		
	} else{
		echo '<br> TERMINÓ LA EJECUCIÓN<br>';
	}
}



 	
 	
	  


