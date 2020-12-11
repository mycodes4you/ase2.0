<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');


if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}

//----  crear la relación de cada uno de los pagos en la tabla pagos_facturas ----

	$preg = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE pago_id >= '" . $_GET['pago'] . "' LIMIT 500";
	$matr = mysql_query($preg) or die("ERROR: Fallo selección de pagos!");
	$total = mysql_num_rows($matr);

	echo '<br><strong>Total pagos: ' . $total . '</strong>';

	$procesados = 0; $provs = 0;
	while ($pag = mysql_fetch_array($matr)) {
		$preg_prov = "SELECT prov_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $pag['pedido_id'] .  "'";
		$matr_preg_prov = mysql_query($preg_prov) or die("ERROR: Fallo seleccion de pedido!" . $preg_prov);
		$prov = mysql_fetch_assoc($matr_preg_prov);

		$fila1 = 0;
		$preg1 = "SELECT pago_id FROM " . $dbpfx . "pagos_facturas WHERE pago_id = '" . $pag['pago_id'] . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Falló selección de pagos! " . $preg1);
		$fila1 = mysql_num_rows($matr1);
		if($fila1 == 0) {
			unset($sql_data_array);
			$sql_data_array = [
				'pago_id' => $pag['pago_id'],
				'fact_id' => $pag['fact_id'],
				'monto' => $pag['pago_monto'],
				'pedido_id' => $pag['pedido_id'],
				'proveedor_id' => $prov['prov_id'],
				'orden_id' => $pag['orden_id'],
				'usuario' => $pag['usuario'],
				'fecha' => $pag['fecha_registro'],
			];
			ejecutar_db($dbpfx . 'pagos_facturas', $sql_data_array);
			$procesados = $procesados + 1;
			$pagproc = $pag['pago_id'];
		}
		$sql_prov = [
			'prov_id' => $prov['prov_id']
		];
		$param = "pago_id = '" . $pag['pago_id'] . "'";
		ejecutar_db($dbpfx . 'pedidos_pagos', $sql_prov, 'actualizar', $param);
		$provs++;
		$provpag = $pag['pago_id'];
	}
	
	echo '<br><br><strong>Total pagos ajustados: ' . $procesados . '</strong><br>Último: ' . $pagproc . '<br>';
	echo '<br><br><strong>Total proveedores ajustados: ' . $provs . '</strong><br>Último: ' . $provpag . '<br>';