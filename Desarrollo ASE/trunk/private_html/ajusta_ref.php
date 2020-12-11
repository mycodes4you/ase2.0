<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}
	$preg = "SELECT s.orden_id, o.orden_estatus FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE s.sub_estatus < '190' AND (o.orden_fecha_recepcion = '$fecha' OR o.orden_id = '$orden_id') AND s.orden_id = o.orden_id GROUP BY o.orden_id";
	$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
	while ($ord = mysql_fetch_array($matr)) {
		$orden_id = $ord['orden_id'];
		$orden_estatus = $ord['orden_estatus'];

		$preg2 = "SELECT s.sub_orden_id, s.sub_refacciones_recibidas, s.sub_estatus, s.sub_area, s.sub_consumibles, s.sub_mo FROM " . $dbpfx . "orden_productos o, " . $dbpfx . "subordenes s WHERE o.sub_orden_id = s.sub_orden_id AND s.orden_id = '$orden_id' GROUP BY s.sub_orden_id";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de orden_productos - subordenes!");
		$totref = 1;
			if($sin_autorizadas == 1) {
				while($sub = mysql_fetch_array($matr2)) {
					$preg3 = "SELECT op_id, op_ok, op_estructural FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '".$sub['sub_orden_id']."' AND op_tangible = '1' AND op_autosurtido >='1' AND op_autosurtido <='3'";
					$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de orden_productos 3!");
					$estruc = 1; $completo = 1; 
					while($op = mysql_fetch_array($matr3)) {
						if($op['op_ok'] == '0') {
							$completo = 0;
							$totref = 0;
							if($op['op_estructural'] == '1') {
								$estruc = 0;
							}
						}
					}
					$parametros = "sub_orden_id = '" . $sub['sub_orden_id'] ."'";
					if($completo == 1) {
						$sql_data_array = array('sub_refacciones_recibidas' => '0');
						if($sub['sub_estatus'] == '105') { $sql_data_array['sub_estatus'] = '106';	}
						ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
					} elseif($estruc == 1) {
						$sql_data_array = array('sub_refacciones_recibidas' => '1');
						ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
					} else {
						$sql_data_array = array('sub_refacciones_recibidas' => '2');
						ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
					}
//					echo $sql_data_array['sub_refacciones_recibidas'] . '<br>';
					actualiza_orden($orden_id, $dbpfx);
				}
			} else {
				while($sub = mysql_fetch_array($matr2)) {
					$preg3 = "SELECT op_id, op_ok, op_estructural, op_pedido, op_pres, op_cantidad, op_precio, op_precio_revisado, op_autosurtido, op_item_seg FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '".$sub['sub_orden_id']."' AND op_tangible = '1'";
					$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de orden_productos 3!");
					$estruc = 1; $completo = 1; $op_ref = 0;
					while($op = mysql_fetch_array($matr3)) {
						if($op['op_ok'] == '0') {
							if(($op['op_pres'] == 1 && $op['op_pedido'] > 0) || is_null($op['op_pres'])) {
								$completo = 0;
								$totref = 0;
								if($op['op_estructural'] == '1') {
									$estruc = 0;
								}
							}
						}
						$op_sub = round(($op['op_cantidad'] * $op['op_precio']), 2);
						if($op['op_precio_revisado'] > 0) {
							$param = "op_id = '" . $op['op_id'] . "'";
							$sql_data = array('op_subtotal' => $op_sub);
							ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
						}
						if(is_null($op['op_pres'])) {
							if(!is_null($op['op_item_seg'])) {
								$preg4 = "SELECT op_id, op_autosurtido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_tangible = '1' AND op_id = '" . $op['op_item_seg'] . "'";
								$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de productos asociados.");
								$sit = mysql_fetch_array($matr4);
								if($sit['op_autosurtido'] == '2' || $sit['op_autosurtido'] == '3') {
									$op_ref = $op_ref + $op_sub;
								}
							} elseif($op['op_autosurtido'] == '2' || $op['op_autosurtido'] == '3') {
								$op_ref = $op_ref + $op_sub;
							}
						}
					}
					$parametros = "sub_orden_id = '" . $sub['sub_orden_id'] ."'";
					$sub_pres = $op_ref + $sub['sub_consumibles'] + $sub['sub_mo'];
					$sql_data_array = array('sub_partes' => $op_ref, 'sub_presupuesto' => $sub_pres);
					if($completo == 1) {
						$sql_data_array['sub_refacciones_recibidas'] = '0';
						if($sub['sub_estatus'] == '105') { $sql_data_array['sub_estatus'] = '106';	}
					} elseif($estruc == 1) {
						$sql_data_array['sub_refacciones_recibidas'] = '1';
					} else {
						$sql_data_array['sub_refacciones_recibidas'] = '2';
					}
					ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
					actualiza_orden($orden_id, $dbpfx);
				}
			}
	}
?>
