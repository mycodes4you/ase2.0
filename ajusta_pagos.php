<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/pedidos.php');


if ($_SESSION['usuario'] != '1000') {
	redirigir('usuarios.php');
}

/*  ----------------  obtener nombres de proveedores   ------------------- */

		$consulta = "SELECT prov_id, prov_nic, prov_qv_id, prov_dde, prov_iva FROM " . $dbpfx . "proveedores ORDER BY prov_nic";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
		$num_provs = mysql_num_rows($arreglo);
		$provs = array();
//		$provs[0] = 'Sin Proveedor';
		while ($prov = mysql_fetch_array($arreglo)) {
			$provs[$prov['prov_id']] = array('nic' => $prov['prov_nic'], 'qvid' => $prov['prov_qv_id'], 'dde' => $prov['prov_dde'], 'iva' => $prov['prov_iva']);
		}
//		print_r($provs);

/*  ----------------  nombres de aseguradoras   ------------------- */
		$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_nic, prov_def FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		while ($aseg = mysql_fetch_array($arreglo)) {
			define('ASEGURADORA_' . $aseg['aseguradora_id'], $aseg['aseguradora_logo']);
			define('ASEGURADORA_NIC_' . $aseg['aseguradora_id'], $aseg['aseguradora_nic']);
			$autosurt[$aseg['aseguradora_id']] = $aseg['autosurtido'];
			$prov_def[$aseg['aseguradora_id']] = $aseg['prov_def'];
		}
//  ----------------  obtener nombres de usuarios	-------------------

		$pregusu = "SELECT usuario, nombre, apellidos, email, rol09 FROM " . $dbpfx . "usuarios ORDER BY nombre";
		$matrusu = mysql_query($pregusu) or die("ERROR: Fallo selección de usuarios!");
		while ($usr = mysql_fetch_array($matrusu)) {
			$usu[$usr['usuario']] = array('nombre' => $usr['nombre'], 'apellidos' => $usr['apellidos'], 'email' => $usr['email'], 'rol09' => $usr['rol09']);
		}

$preg0 = "SELECT * FROM " . $dbpfx . "temp_pagos";
$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de pagos temporales " . $preg0);
while($reg = mysql_fetch_array($matr0)) {
	$error = 'no'; $provid = 0; $msg = '';

	foreach($provs as $kp => $vp) {
		if($reg['prov_nom'] == $vp['nic']) {
			$provid = $kp;
			break;
		}
	}
	if($provid < 1) { $error = 'si'; $msg .= 'Proveedor no identificado, verificar registro ' . $reg['reg-id'] . '<br>'."\n"; }

	if($reg['pago_tipo'] == 1) { $forma_pago = '01'; }
	elseif($reg['pago_tipo'] == 2) { $forma_pago = '02'; }
	elseif($reg['pago_tipo'] == 3) { $forma_pago = '03'; }
	elseif($reg['pago_tipo'] == 4) { $error = 'si'; $msg .= 'Pago con NC, verificar el pago ' . $reg['reg-id'] . '<br>'."\n"; }
	elseif($reg['pago_tipo'] == 5) { $error = 'si'; $msg .= 'Pago Adelantado, verificar el pago ' . $reg['reg-id'] . '<br>'."\n"; }
	elseif($reg['pago_tipo'] == 6) { $forma_pago = '04'; }
	elseif($reg['pago_tipo'] == 7) { $forma_pago = '28'; }
	else { $error = 'si'; $msg .= 'Pago desconocido, verificar el pago ' . $reg['reg-id'] . '<br>'."\n"; }

	$ban = explode('-', $reg['pago_banco']);

	$preg1 = "SELECT fact_id, f_uuid, f_monto, f_rec FROM " . $dbpfx . "facturas_por_pagar WHERE doc_int_id = '" . $reg['pedido_id'] . "' AND fact_num = '" . $reg['fact_num'] . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de factura por pagar " . $preg1);
	$factura = mysql_fetch_array($matr1);
	$fact_id = $factura['fact_id'];
	$uuid = $factura['f_uuid'];
	$por_pagar = $factura['f_monto'];
	$f_rec = $factura['f_rec'];
	if($fact_id < 1) { $error = 'si'; $msg .= 'Factura no identificada, verificar registro ' . $reg['reg-id'] . '<br>'."\n"; }

	if($error == 'no') {
//		print_r($subir);
//		echo 'Resultado de subir<br>';

		if(strtotime($reg['pago_fecha']) < strtotime('2020-01-01 23:59:59')) { $reg['pago_fecha'] = date('Y-m-d 23:59:59', (strtotime($f_rec) + 2592000)); }
		$sql_data_array = [
			'pago_monto' => $reg['pago_monto'],
			'pago_tipo' => $forma_pago,
			'pago_banco' => $ban[0],
			'pago_cuenta' => $ban[1],
			'pago_referencia' => $reg['pago_referencia'],
			'pago_fecha' => date('Y-m-d 23:59:59', strtotime($reg['pago_fecha'])),
			'usuario' => $reg['usuario'],
			'prov_id' => $provid,
		];
		$pago_id = ejecutar_db($dbpfx . 'pedidos_pagos', $sql_data_array, 'insertar');
		if($adelanto == 1) {
			$comentario = 'Registro de pago adelantado por  ' . $reg['pago_monto'] . ' del Pedido ' . $reg['pedido_id'];
		} else {
			$comentario = 'Pago de Factura ' . $fact_id . ' del Pedido '. $reg['pedido_id'];
		}
		bitacora($orden_id, $comentario, $dbpfx);

		unset($sql_data_array);
		$sql_data_array = [
			'pago_id' => $pago_id,
			'fact_id' => $fact_id,
			'monto' => $reg['pago_monto'],
			'pedido_id' => $reg['pedido_id'],
			'proveedor_id' => $provid,
			'usuario' => $reg['usuario'],
			'fecha' => date('Y-m-d H:i:s', time()),
		];
		if($adelanto == 1) {
			$sql_data_array['adelanto'] = $reg['pedido_id'];
		}
		ejecutar_db($dbpfx . 'pagos_facturas', $sql_data_array);

		unset($sql_data_array);
		unset($_SESSION['ped']);

		// ---- Marcamos como pagada la factura si el pago cumple con el monto por pagar
		$preg2 = "SELECT monto FROM " . $dbpfx . "pagos_facturas WHERE fact_id = '" . $fact_id . "'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de factura por pagar " . $preg2);
		$pagado = 0;
		while($pagos = mysql_fetch_array($matr2)) {
			$pagado = $pagado + $pagos['monto'];
		}
		if($pagado >= ($por_pagar - 0.50) && $fact_id > 0) {
			$param = "fact_id = '" . $fact_id . "'";
			$sql_data_array = ['pagada' => '1',
			'f_pago' => date('Y-m-d H:i:s', time())];
			ejecutar_db($dbpfx . 'facturas_por_pagar', $sql_data_array, 'actualizar', $param);
			unset($sql_data_array);
		}

		// --- Crear el informe XML para Quien-Vende.com --
		if($qv_activo == 1) {
			$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" prov_id="' . $provs[$provid]['qvid'] . '" >'."\n";
			$xml .= '		<Solicitud tiempo="0">70</Solicitud>'."\n";
			$xml .= '		<Factura uuid="' . $uuid . '" pedido_id="' . $reg['pedido_id'] . '">'."\n";
			$xml .= '			<Documento tipo_pago="' . $forma_pago . '" monto="' . $reg['pago_monto'] . '" f_pago="' . date('Y-m-d 23:59:59', strtotime($reg['pago_fecha'])) . '" banco="' . $ban[0] . '" cuenta="' . $ban[1] . '" referencia="' . $reg['pago_referencia'] . '" f_registro="' . date('Y-m-d H:i:s', time()) . '"/>'."\n";
			$xml .= '		</Factura>'."\n";
			$xml .= '	</Comprador>'."\n";
			$mtime = substr(microtime(), (strlen(microtime())-3), 3);
			$xmlnom = $nick . '-' . $reg['pedido_id'] . '-70-' . date('YmdHis') . $mtime . '.xml';
			file_put_contents("../qv-salida/".$xmlnom, $xml);
		}
		actualiza_pedido($reg['pedido_id'], $dbpfx);
		echo 'Registro ' . $reg['reg-id'] . ' OK<br>'."\n"; 
	} else {
		echo $msg;
	}
}

?>
