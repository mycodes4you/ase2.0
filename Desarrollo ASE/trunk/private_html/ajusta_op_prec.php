<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}

// ----------- Verificación de datos de aseguradora 

$consulta = "SELECT aseguradora_id, autosurtido FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
while ($aseg = mysql_fetch_array($arreglo)) {
	$asurt[$aseg['aseguradora_id']] = $aseg['autosurtido'];
}

$error = 'no';
// ---- Selección de una Tarea específica -------------->
if(isset($misub) && is_numeric($misub)) {
	$preg = "SELECT sub_orden_id, sub_area, sub_estatus, sub_descuento, sub_refacciones_recibidas, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_estatus < '130' AND sub_orden_id = '".$misub."'";
} elseif(isset($inisub) && is_numeric($inisub)) {
// ---- Selección de todas las OTs -------------->
	$preg = "SELECT sub_orden_id, sub_area, sub_estatus, sub_descuento, sub_refacciones_recibidas, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_estatus < '130' AND sub_orden_id >= '" . $inisub . "' LIMIT 300";
} elseif(isset($feutl)) {
// ---- Selección de todas las OTs -------------->
	$preg = "SELECT s.sub_orden_id, s.sub_aseguradora, s.sub_reporte FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE s.orden_id = o.orden_id AND s.sub_estatus < '130' AND o.orden_fecha_ultimo_movimiento >= '" . date('Y-m-d 00:00:00', strtotime($feutl)) . "' LIMIT 1000";
} elseif(isset($pedido)) {
// ---- Selección de todas las OTs -------------->
	$preg = "SELECT s.sub_orden_id, s.sub_aseguradora, s.sub_reporte FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o, " . $dbpfx . "pedidos p WHERE s.orden_id = o.orden_id AND o.orden_id = p.orden_id AND s.sub_estatus < '130' AND p.pedido_id = '" . $pedido . "'";
} else {
	$error = 'si';
}
// echo $preg;
if($error == 'no') {
	$matr = mysql_query($preg) or die("ERROR: Fallo selección! " . $preg);
	$fila = mysql_num_rows($matr); $numi = 1;
	while ($sub = mysql_fetch_array($matr)) {
		echo 'Procesando ' . $numi . ' de ' . $fila . ' sub: ' . $sub['sub_orden_id'] . ' ' . microtime(true) . ' <br>'."\n";
		$v = $sub['sub_orden_id'];
		$ase = $sub['sub_aseguradora'];
		if($sub['sub_reporte'] == '0' || $sub['sub_reporte'] == '') { $particular = 1; }
		$preg2 = "SELECT op_id, op_ok, op_estructural, op_pedido, op_pres, op_cantidad, op_costo, op_precio, op_precio_revisado, op_tangible, op_autosurtido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '$v'";
//	echo $preg2;
//	echo $asurt[$ase];
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos para recálculo!");
		$estruc = 1; $completo = 1; $op_ref = 0; $op_cons = 0; $op_mo = 0;
		while($op = mysql_fetch_array($matr2)) {
			// --- Obtiene monto para descuento en caso de reemplazo ---
			if($sub['sub_descuento'] > 0) {
				$descxremp = $descxremp + ($op['op_cantidad'] * $op['op_costo']);
			}
			// --- Determina si están completas las refacciones relacionadas a la Tarea ---
			if($op['op_ok'] == '0' && $op['op_tangible'] == '1') {
				if(($op['op_pres'] == 1 && $op['op_pedido'] > 0) || is_null($op['op_pres'])) {
					$completo = 0;
					$totref = 0;
					if($op['op_estructural'] == '1') {
						$estruc = 0;
					}
				}
			}
			// --- Determina si debe agregar o no el precio de venta a resumen cobrable de la Tarea ---
			if($op['op_autosurtido']=='1') {
				$op_sub = 0;
			} else {
				$op_sub = $op['op_cantidad'] * $op['op_precio'];
			}
			// --- En caso de revisión de precio de venta, reconfirma el subtotal actual ---
			if($op['op_precio_revisado'] > 0) {
				$paramjup = "op_id = '" . $op['op_id'] . "'";
				$sqldajup = array('op_subtotal' => $op_sub);
				ejecutar_db($dbpfx . 'orden_productos', $sqldajup, 'actualizar', $paramjup);
			}
			// --- Actualiza los montos de venta para resumen cobrable de la Tarea ---
			if($op['op_tangible']== 1 && $op['op_pres'] < 1 && (($asurt[$ase] == '1' && $op['op_autosurtido'] != '1') || $particular == 1 || $op['op_autosurtido']=='2'|| $op['op_autosurtido']=='3')) {
				$op_ref = $op_ref + $op_sub;
			} elseif($op['op_tangible']== 2 && $op['op_pres'] < 1) {
				$op_cons = $op_cons + $op_sub;
			} elseif($op['op_tangible']== 0 && $op['op_pres'] < 1) {
				$op_mo = $op_mo + $op_sub;
			}
		}
		if($completo == 1) {
			$sql_data_array = array('sub_refacciones_recibidas' => '0');
			if($sub['sub_estatus'] == '105') {
				$sql_data_array['sub_estatus'] = '106';
			}
		} elseif($estruc == 1) {
			$sql_data_array = array('sub_refacciones_recibidas' => '1');
		} else {
			$sql_data_array = array('sub_refacciones_recibidas' => '2');
		}
		$nvo_pres = $op_ref + $op_cons + $op_mo;
		$sql_data_array['sub_presupuesto'] = $nvo_pres;
		$sql_data_array['sub_partes'] = $op_ref;
		$sql_data_array['sub_consumibles'] = $op_cons;
		$sql_data_array['sub_mo'] = $op_mo;
//		print_r($sql_data);
		$param = "sub_orden_id = '" . $v . "'";
		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $param);
		unset($sql_data_array);
		if($sub['sub_descuento'] > 0) {
			$parametros = "pago_id = '" . $sub['sub_descuento'] ."'";
// ------ Sumar el porcentaje que determine el taller a los costos de refacciones de remplazo
			$descxremp = round(($descxremp * (1 + ($prcxremp / 100))), 2);
			$sql_data_array['pago_monto_origen'] = $descxremp;
			ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'actualizar', $parametros);
			bitacora($orden_id, 'Guardando descuento de ' . $descxremp . ' en PagoID ' . $sub['sub_descuento'], $dbpfx);
		}
		$numi++;
	}
} else {
	echo 'Revisar el numero de Tarea.';
}

?>
