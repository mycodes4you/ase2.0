<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';

include('config.php');

mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
mysql_select_db($dbnombre) or die('Falló la seleccion la DB');

function datosVehiculo($orden_id, $dbpfx) {
		$pregunta = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_subtipo, v.vehiculo_color, v.vehiculo_modelo, v.vehiculo_placas, v.vehiculo_serie FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "ordenes o WHERE o.orden_vehiculo_id = v.vehiculo_id AND o.orden_id = '" . $orden_id . "'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de vehículo!" . $pregunta);
		$veh = mysql_fetch_array($matriz);
		$vehiculo = array('marca' => $veh['vehiculo_marca'],
			'tipo' => $veh['vehiculo_tipo'],
			'color' => $veh['vehiculo_color'],
			'serie' => $veh['vehiculo_serie'],
			'modelo' => $veh['vehiculo_modelo']);
		return $vehiculo;
}

if($accion==="porcotizar") {
	
	$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	$xml .= '	<Cotizar instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
	
	$preg0 = "SELECT o.orden_id FROM " . $dbpfx . "orden_productos p, " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE p.op_tangible = '1' AND p.op_ok = '0' AND s.sub_orden_id = p.sub_orden_id AND s.orden_id = o.orden_id AND o.orden_estatus < '30' GROUP BY o.orden_id ";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de refacciones! " . $preg0);
	while($ord = mysql_fetch_array($matr0)) {
		$orden_id = $ord['orden_id'];
		$preg1 = "SELECT p.op_id, p.op_cantidad, p.op_nombre, p.op_codigo, p.op_cotizado_a, p.op_pedido, p.op_doc_id, p.op_recibidos FROM " . $dbpfx . "orden_productos p, " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE p.op_tangible = '1' AND p.op_ok = '0' AND s.sub_orden_id = p.sub_orden_id AND s.orden_id = o.orden_id AND o.orden_estatus < '30' AND o.orden_id = '$orden_id'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de refacciones! " . $preg1);
		$fila1 = mysql_num_rows($matr1);
		if($fila1 > 0) {
			$veh = datosVehiculo($orden_id, $dbpfx);
			$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'">'."\n";
			while($prods = mysql_fetch_array($matr1)) {
				$pend = $prods['op_cantidad'] - $prods['op_recibidos'];
				if($pend > 0) {
					$xml .= '			<Ref op_id="' . $prods['op_id'] . '" op_cantidad="' . $prods['op_cantidad'] . '" op_nombre="' . $prods['op_nombre'] . '" op_codigo="' . $prods['op_codigo'] . '" op_doc_id="' . $prods['op_doc_id'] . '"';
					if((is_null($prods['op_cotizado_a']) || $prods['op_cotizado_a'] == '') && $prods['op_pedido'] < 1) {
						$xml .= ' op_estatus="10"';
					} elseif(!is_null($prods['op_cotizado_a']) && $prods['op_cotizado_a'] != '' && $prods['op_pedido'] < 1 ) {
						$xml .= ' op_estatus="20"';
					} elseif( $prods['op_pedido'] > 0 ) {
						$preg2 = "SELECT p.prov_id, pv.prov_qv_id FROM " . $dbpfx . "pedidos p, " . $dbpfx . "proveedores pv WHERE p.pedido_id = '" . $prods['op_pedido'] . "' AND p.prov_id = pv.prov_id";
						$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de refacciones! " . $preg2);
						$prov = mysql_fetch_array($matr2);
						$xml .= ' op_pedido="' . $prods['op_pedido'] . '" prov_qv_id="' . $prov['prov_qv_id'] . '" op_estatus="30"';
					}
					$xml .= ' />'."\n";
				}
			}
			$xml .= '		</OT>'."\n";
		}
	}
	$xml .= '	</Cotizar>'."\n";
	$xmlnom = date('Ymd') . '-' . $instancia . '.xml';
	echo $xmlnom;
	if(file_exists("../". DIR_DOCS . $xmlnom)) {
		rename("../". DIR_DOCS . $xmlnom, "../" . DIR_DOCS . date('Ymd') . '-' . $instancia . '-' . time() . '.xml');
	}
	file_put_contents("../". DIR_DOCS . $xmlnom, $xml);
}


?>