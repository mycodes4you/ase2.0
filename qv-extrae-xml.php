<?php
foreach($_POST as $k => $v){$$k=$v; } //echo $k.' -> '.$v.' | '; }
foreach($_GET as $k => $v){$$k=$v; } //echo $k.' -> '.$v.' | '; }
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/proveedores.php');

$ruta="../qv-entrada/";
$directorio=dir($ruta);
//	echo "Directorio " . $ruta . ":<br><br>";
$reg = '/\w*+.+xml$/';
$archivos = array();
while ($archivo = $directorio->read()) {
	if(preg_match($reg, $archivo) && !is_dir($archivo)){
//		echo $archivo."<br>";
		$archivos[] = $archivo;
	}
}

$directorio->close();
$cots = array();

foreach($archivos as $archxml) {
//	echo $archxml."<br>";
	$arc = file_get_contents($ruta.$archxml);
//	echo $ruta.$archxml;
	$xml = new DOMDocument();
	if(!$xml->loadXML($arc)) {
		rename($ruta.$archxml, $ruta.'fallidos/'.$archxml);
	} else {
		$proveedor = $xml->getElementsByTagName('Proveedor')->item(0);
		$prov_rfc = $proveedor->getAttribute("prov_rfc");
		$prov_qv_id = $proveedor->getAttribute("prov_id");
		$preg1 = "SELECT * FROM " . $dbpfx . "proveedores WHERE prov_qv_id = '" . $prov_qv_id . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion de proveedores! " . $preg1);
		$prov = mysql_fetch_array($matr1);
		$fila1 = mysql_num_rows($matr1);
		$prov_id = $prov['prov_id'];
		if($prov['prov_activo'] < '2' && $fila1 == '1') {
			// --- Actualiza datos de proveedor en CDR desde QV ---
			unset($sqlprov);
//			if($prov['prov_nic'] != $proveedor->getAttribute("prov_nick") && $proveedor->getAttribute("prov_nick") != '') { $sqlprov['prov_nic'] = $proveedor->getAttribute("prov_nick"); }
			if($prov['prov_razon_social'] != $proveedor->getAttribute("prov_razon") && $proveedor->getAttribute("prov_razon") != '') { $sqlprov['prov_razon_social'] = $proveedor->getAttribute("prov_razon"); }
			if($prov['prov_rfc'] != $proveedor->getAttribute("prov_rfc") && $proveedor->getAttribute("prov_rfc") != '') { $sqlprov['prov_rfc'] = $proveedor->getAttribute("prov_rfc"); }
			if($prov['prov_cp'] != $proveedor->getAttribute("prov_cp") && $proveedor->getAttribute("prov_cp") != '') { $sqlprov['prov_cp'] = $proveedor->getAttribute("prov_cp"); }
			if($prov['prov_representante'] != $proveedor->getAttribute("prov_nombre") && $proveedor->getAttribute("prov_nombre") != '') { $sqlprov['prov_representante'] = $proveedor->getAttribute("prov_nombre"); }
			if($prov['prov_telefono1'] != $proveedor->getAttribute("prov_tel") && $proveedor->getAttribute("prov_tel") != '') { $sqlprov['prov_telefono1'] = $proveedor->getAttribute("prov_tel"); }
//			if($prov['prov_email'] != $proveedor->getAttribute("prov_email") && $proveedor->getAttribute("prov_email") != '') { $sqlprov['prov_email'] = $proveedor->getAttribute("prov_email"); }
			if(count($sqlprov) > 0) {
				$parprov = "prov_qv_id = '" . $prov_qv_id . "'";
				ejecutar_db($dbpfx.'proveedores', $sqlprov, 'actualizar', $parprov);
			}
			unset($sqlprov);

			$cotizacion = $xml->getElementsByTagName('Cotizacion');
			$pedido = $xml->getElementsByTagName('Pedido');
			$i = 0;
			foreach($cotizacion as $k) {
// ------ Obteniendo generales de cotización
				$costo_envio = $cotizacion->item($i)->getAttribute('costo_envio');
				$mensaje = $cotizacion->item($i)->getAttribute('mensaje');
				$vencimiento = $cotizacion->item($i)->getAttribute('vencimiento');
				$dias_credito = $cotizacion->item($i)->getAttribute('dias_credito');
				$ref = $cotizacion->item($i)->getElementsByTagName('Ref');
				$j = 0;
				foreach($ref as $p) {
					$op_id = $ref->item($j)->getAttribute('op_id');
					$disponibilidad = $ref->item($j)->getAttribute('disponibilidad');
					$condicion = $ref->item($j)->getAttribute('condiciones');
					$origen = $ref->item($j)->getAttribute('origen');
					$cantidad = $ref->item($j)->getAttribute('cantidad');
					$costo = $ref->item($j)->getAttribute('precio');
					$op_estatus = $ref->item($j)->getAttribute('op_estatus');
					$dias_entrega = $ref->item($j)->getAttribute('dias_entrega');
					$foto_ref = $ref->item($j)->getAttribute('foto_ref');
					$preg2 = "SELECT * FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $op_id . "' AND prod_prov_id = '" . $prov_id . "'";
					$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección Prod-Prov! " . $preg2);
					$fila2 = mysql_num_rows($matr2);
					if($fila2 >= 1) { $hacer = 'actualizar'; } else { $hacer = 'insertar'; }
					$cot = mysql_fetch_array($matr2);
					$preg3 = "SELECT s.orden_id, o.sub_orden_id, o.op_cotizado_a FROM " . $dbpfx . "subordenes s, " . $dbpfx . "orden_productos o WHERE o.op_id = '" . $op_id . "' AND o.sub_orden_id = s.sub_orden_id";
					$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección Prod-Prov! " . $preg3);
					$ord = mysql_fetch_array($matr3);
					$orden_id = $ord['orden_id'];
// ------ Dependiendo del Estatus, tomar la acción correspondiente ---------------------
					$param = " op_id = '" . $op_id . "' AND prod_prov_id ='" . $prov_id . "'";
					if($op_estatus == '90' || $costo == '0') {
						ejecutar_db($dbpfx . 'prod_prov', '', 'eliminar', $param);
						bitacora($orden_id, 'Se eliminó cotización para OP ' . $op_id . ' desde QV del Prov: ' . $prov_id . ' Costo: ' . $cot['prod_costo'], $dbpfx, '', '0', '', '', '', '', '', '708');
					} elseif($op_estatus == '20') {
						$sql_data = [
							'prod_costo' => $costo,
							'dias_entrega' => $dias_entrega,
							'dias_credito' => $dias_credito,
							'fecha_cotizado' => date('Y-m-d H:i:s'),
							'cotqv' => 1,
							'prod_mensaje' => $mensaje,
							'prod_vencimiento' => date('Y-m-d 23:59:59', strtotime($vencimiento)),
							'prod_origen' => $origen,
							'prod_condicion' => $condicion,
							'prod_costo_envio' => $costo_envio,
							'prod_disponibilidad' => $disponibilidad,
							'prod_foto_prov' => $foto_ref,
						];
						if($hacer == 'insertar') {
							$sql_data['op_id'] = $op_id;
							$sql_data['prod_prov_id'] = $prov_id;
							$sql_data['sub_orden_id'] = $ord['sub_orden_id'];
						}
						ejecutar_db($dbpfx . 'prod_prov', $sql_data, $hacer, $param);
						bitacora($orden_id, $hacer . ' cotización para OP ' . $op_id . ' desde QV. Antes ' . $cot['prod_costo'] . ' Ahora ' . $costo, $dbpfx, '', '0', '', '', '', '', '', '708');
// ------ Actualiza el registro op_cotizado_a con la respuesta del proveedor --
						if($ord['op_cotizado_a'] != '') {
							$cotiza = $prov_id . '|' . $ord['op_cotizado_a'];
						} else {
							$cotiza = $prov_id;
						}
						$param = "op_id = '$op_id'";
						$sql_data = array('op_cotizado_a' => $cotiza);
						ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);

// --- Actualiza mejor cotización --
						$preg4 = "SELECT pp_id, prod_prov_id, prod_costo, dias_entrega, dias_credito, fecha_cotizado FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $op_id . "'";
						$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección Prod-Prov! 122 " . $preg4);
						unset($mejcot, $puntos);
						$menorcost = $costo; $provmencost = $prov_id; $provgan = time(); $maspuntos = 0;
						if(!isset($McotValCost)) { $McotValDde = 80; }
						if(!isset($McotValDde)) { $McotValDde = 15; }
						if(!isset($McotValDdc)) { $McotValDdc = 0; }
						if(!isset($McotValRep)) { $McotValRep = 5; }
						while($mcot = mysql_fetch_array($matr4)) {
							if($mcot['prod_costo'] > ($menorcost / 10)) {  // --- Omite cotizaciones en 0 o muy bajas --
								$mejcot[$mcot['prod_prov_id']]['costo'] = $mcot['prod_costo'];
								if($mcot['dias_entrega'] > 5) { $mcot['dias_entrega'] = 5; }
								$mejcot[$mcot['prod_prov_id']]['dde'] = round(($McotValDde * ((5 - $mcot['dias_entrega']) / 5)),6);
								if($mcot['dias_credito'] > 30) { $mcot['dias_credito'] = 30; }
								$mejcot[$mcot['prod_prov_id']]['ddc'] = round(($McotValDdc * ($mcot['dias_credito'] / 30)),6);
								if($prov['prov_reputacion'] > 100) { $prov['prov_reputacion'] = 100; }
								$mejcot[$mcot['prod_prov_id']]['rep'] = round(($McotValRep * ($prov['prov_reputacion'] / 100)),6);
								if($mcot['prod_costo'] < $menorcost) {
									$provmencost = $mcot['prod_prov_id'];
									$menorcost = $mcot['prod_costo'];
								}
//								echo $menorcost . '<br>';
							}
						}
						mysql_data_seek($matr4,0);
						while($mcot = mysql_fetch_array($matr4)) {
							if($mcot['prod_costo'] > ($mejcot[$provmencost]['costo'] / 5)) {
								$costcot = round(($McotValCost / ($mcot['prod_costo'] / $mejcot[$provmencost]['costo'])),6);
								if($costcot > $McotValCost) { $costcot = $McotValCost; }
//								echo $mcot['pp_id'];
//								echo ' Costo: ' . $costcot;
//								echo ' DDE: ' . $mejcot[$mcot['prod_prov_id']]['dde'];
//								echo ' DDC: ' . $mejcot[$mcot['prod_prov_id']]['ddc'];
//								echo ' Rep: ' . $mejcot[$mcot['prod_prov_id']]['rep'];
								$mejcot[$mcot['prod_prov_id']]['valcot'] = $costcot + $mejcot[$mcot['prod_prov_id']]['dde'] + $mejcot[$mcot['prod_prov_id']]['ddc'] + $mejcot[$mcot['prod_prov_id']]['rep'];
							}
//							echo ' Suma: ' . $mejcot[$mcot['prod_prov_id']]['valcot'] . '<br>';
							$puntos[$mcot['pp_id']] = $mejcot[$mcot['prod_prov_id']]['valcot'];
//							echo 'Mas puntos ' . $maspuntos . '<br>';
							if($mejcot[$mcot['prod_prov_id']]['valcot'] > $maspuntos) {
								$maspuntos = $mejcot[$mcot['prod_prov_id']]['valcot'];
								unset($gano,$ganador);
								$gano[$mcot['pp_id']] = 1;
								$ganador = $mcot['pp_id'];
								$cuanto[$ganador] = $mejcot[$mcot['prod_prov_id']]['valcot'];
							} elseif($mejcot[$mcot['prod_prov_id']]['valcot'] == $maspuntos) {
								$gano[$mcot['pp_id']] = 1;
							}
						}
//						print_r($gano);
						if(count($gano) > 1) {
							mysql_data_seek($matr4,0);
							while($mcot = mysql_fetch_array($matr4)) {
								if(strtotime($mcot['fecha_cotizado']) < $provgan && $gano[$mcot['pp_id']] == 1) {
									$provgan = strtotime($mcot['fecha_cotizado']);
									$ganador = $mcot['pp_id'];
									$cuanto[$ganador] = $mejcot[$mcot['prod_prov_id']]['valcot'];
								}
							}
						}
						$sqldata = [
							'puntos' => $cuanto[$ganador],
							'ganador' => 1,
						];
						$param = "pp_id = '" . $ganador . "'";
						ejecutar_db($dbpfx . 'prod_prov', $sqldata, 'actualizar', $param);
						unset($puntos[$ganador]); // --- Removemos los puntos del ganador --
						arsort($puntos); // --- ordenamos las cotizaciones de mayor a menor puntaje --
						$gix = 2;
						foreach($puntos as $kpp => $vpu) {
							$sqldata = [
								'puntos' => $vpu,
								'ganador' => $gix,
							];
							$param = "pp_id = '" . $kpp . "'";
							ejecutar_db($dbpfx . 'prod_prov', $sqldata, 'actualizar', $param);
							$gix++;
						}
					}
					$j++;
				}
				$i++;
			}

			$i = 0; unset($docsfpc);
			foreach($pedido as $k) {
// ------ Obteniendo generales de pedido
				$mensaje = $pedido->item($i)->getAttribute('mensaje');
				$estatus = $pedido->item($i)->getAttribute('estatus');
				$pedido_id = $pedido->item($i)->getAttribute('pedido_id');
				$doc = $pedido->item($i)->getElementsByTagName('Documento');
				$preg1 = "SELECT orden_id, pedido_estatus, subtotal, impuesto, dias_credito, usuario_pide, observaciones FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $pedido_id . "'";
				$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección Pedidos! " . $preg1);
				$pedori = mysql_fetch_array($matr1);
				$orden_id = $pedori['orden_id'];
				$monto_pedido = round(($pedori['subtotal'] + $pedori['impuesto']),2);
				$fechaprog = dia_habil($pedori['dias_credito'], $DiaRevFact);
				if($estatus == '40') {
					$param = "pedido_id = '" . $pedido_id . "'";
					$sql_data = ['observaciones' => $pedori['observaciones'] . "\n<br>" . date('Y-m-d H:i') . ': ' . $mensaje];
					ejecutar_db($dbpfx . 'pedidos', $sql_data, 'actualizar', $param);
					bitacora($orden_id, 'Pedido ' . $pedido_id . ' aceptado por Proveedor ' . $prov['prov_nic'], $dbpfx, '', '0', '', '', '', '', '', '708');
				} elseif($estatus == '35' && $pedori['pedido_estatus'] < '10') {
					$param = "pedido_id = '" . $pedido_id . "'";
					$sql_data = ['pedido_estatus' => '93', 'observaciones' => $pedori['observaciones'] . "\n<br>" . date('Y-m-d H:i') . ': ' . $mensaje];
					ejecutar_db($dbpfx . 'pedidos', $sql_data, 'actualizar', $param);
					$param = "op_pedido = '" . $pedido_id . "' AND op_tangible < '3'";
					unset($sql_data);
					$sql_data = ['op_pedido' => '0'];
					ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
					bitacora($orden_id, 'Pedido ' . $pedido_id . ' cancelado por Proveedor ' . $prov['prov_nic'], $dbpfx, 'Pedido ' . $pedido_id . ' rechazado por Proveedor ' . $prov['prov_nic'], '3', '', '', $prov['usuario_pide'], '', '', '708');
					unset($sql_data);
				}
				$j = 0;
				$factpc_id = '';
				foreach($doc as $p) {
					$docnom = $doc->item($j)->getAttribute('nombre');
					$nombre_archivo = $doc->item($j)->getAttribute('url');
					$doctipo = $doc->item($j)->getAttribute('tipo');
					$fact_num = $doc->item($j)->getAttribute('fact_num');
					$fact_monto = $doc->item($j)->getAttribute('fact_monto');
					$rfc_receptor = $doc->item($j)->getAttribute('rfc_receptor');
					$fact_emision = $doc->item($j)->getAttribute('fact_emision');
					$sql_data_array = [
						'orden_id' => $orden_id,
						'doc_nombre' => $docnom . ' del pedido ' . $pedido_id,
						'doc_usuario' => '708',
						'doc_archivo' => $nombre_archivo,
						'doc_clasificado' => '1',
					];
					$doc_id = ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
					// --- Guarda el nombre del archivo del documento para el tipo procesado para después usar el array para
					// --- enlazar los diferentes documentos a la factura recién subida.
					$docsfpc[$i][$docnom] = $nombre_archivo;
					// --- Agrega el archivo a la lista de archivos a extraer desde wget --
					$archivo = '../qv-entrada/documentos.qv';
					$myfile = file_put_contents($archivo, $instancia . '/' . $nombre_archivo . PHP_EOL , FILE_APPEND | LOCK_EX);
					if($doctipo == 'Factura' && $docnom == 'Factura-xml') {
						$facturado = 0;
						$preg6 = "SELECT f_monto FROM " . $dbpfx . "facturas_por_pagar WHERE doc_int_id = '" . $pedido_id . "' AND pagada < '2'";
						$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección facturas por pagar! 201 " . $preg6);
						while($fpag = mysql_fetch_array($matr6)) {
							$facturado = $facturado + $fpag['f_monto'];
						}
						$factot = $fact_monto + $facturado;
						if(($monto_pedido + 0.1) > $factot && $rfc_receptor == $agencia_rfc) {
							$sql_data_array = [
								'orden_id' => $orden_id,
								'tipo' => '1',
								'doc_int_id' => $pedido_id,
								'tercero_id' => $prov_id,
								'fact_num' => $fact_num,
								'f_monto' => $fact_monto,
								'f_creada' => $fact_emision,
								'f_rec' => date('Y-m-d H:i:s'),
								'f_prog' => $fechaprog,
								'f_doc_xml' => $nombre_archivo,
								'f_uuid' => $uuid,
								'usuario' => '708',
							];
							$fid[$i] = ejecutar_db($dbpfx . 'facturas_por_pagar', $sql_data_array, 'insertar');
							bitacora($orden_id, 'Registro de factura por pagar: '.$fact_num, $dbpfx);
							$xmlitem = '			<Documento uuid="' . $uuid . '" tipo="Factura-xml" aceptado="SI" motivo="Factura recibida y programada para pago." f_rec="' . date('Y-m-d H:i:s') . '" f_prog="' . $fechaprog . '" />'."\n";
							actualiza_pedido($pedido_id, $dbpfx);
						} else {
							$motivo = '';
							if(($monto_pedido + 0.1) < $factot && $facturado > 0) {
								$motivo = 'La factura ' . $fact_num . ' fue rechazada porque junto con la(s) factura(s) previamente registrada(s) excede el monto total a facturar en el pedido. ';
							} elseif(($monto_pedido + 0.1) < $factot) {
								$motivo = 'La factura ' . $fact_num . ' fue rechazada porque excede el monto total a facturar en el pedido. ';
							}
							if($rfc_receptor != $agencia_rfc) {
								$motivo .= 'La factura ' . $fact_num . ' fue rechazada porque el RFC del receptor de la factura no corresponde con el RFC del Comprador.';
							}
							$param = "pedido_id = '" . $pedido_id . "'";
							$sql_data = ['observaciones' => $pedori['observaciones'] . "\n<br>" . date('Y-m-d H:i') . ': ' . $motivo];
							ejecutar_db($dbpfx . 'pedidos', $sql_data, 'actualizar', $param);
							bitacora($orden_id, 'Factura ' . $fact_num . ' del proveedor ' . $prov_id . ' ' . $prov['prov_nic'] . ' ' . $motivo . ' ' . $pedido_id, $dbpfx, '', '0', '', '', '', '', '', '708');
							$xmlitem = '			<Documento uuid="' . $uuid . '" tipo="Factura-xml" aceptado="NO" motivo="' . $motivo . '" />'."\n";
						}
						// --- Se genera el XML de respuesta del procesamiento de la factura --
						$xmlped = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
						$xmlped .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" prov_id="' . $prov_qv_id . '" >'."\n";
						$xmlped .= '		<Solicitud tiempo="' . microtime() . '">60</Solicitud>'."\n";
						$xmlped .= '		<Pedido pedido_id="' . $pedido_id . '">'."\n";
						$xmlped .= $xmlitem;
						$xmlped .= '		</Pedido>'."\n";
						$xmlped .= '	</Comprador>'."\n";
						$mtime = substr(microtime(), (strlen(microtime())-3), 3);
						$xmlnom = $nick . '-60-' . date('YmdHis') . $mtime . '.xml';
						file_put_contents("../qv-salida/".$xmlnom, $xmlped);
						unset($xmlitem); unset($xmlped);
					}
					$j++;
				}
				// -- Registra en el pedido el Contrarrecibo correspondiente --
				if($docsfpc[$i]['Contrarrecibo'] != '') {
					$param = "pedido_id = '" . $pedido_id . "'";
					$sqlpeddoc['pedido_doc_cr'] = $docsfpc[$i]['Contrarrecibo'];
					ejecutar_db($dbpfx . 'pedidos', $sqlpeddoc, 'actualizar', $param);
				}
				$i++;
			}
			// --- Registra en cada factura el nombre del PDF correspondiente --
			foreach($fid as $kf => $vf) {
				$param = "fact_id = '" . $vf . "'";
				$sqlfactdoc['f_doc_pdf'] = $docsfpc[$kf]['Factura-pdf'];
				ejecutar_db($dbpfx . 'facturas_por_pagar', $sqlfactdoc, 'actualizar', $param);
			}
//			echo $ruta . 'procesados/' . time() . '-' .$archxml;
			rename($ruta.$archxml, $ruta . 'procesados/' . time() . '-' .$archxml);
		} else {
			if($fila1 == 0 || $prov['prov_activo'] == '0') {
// ------ Avisar a Usuarios autorizados que un proveedor nuevo o inactivo desea cotizar sus requerimientos de refacciones ---
				if($fila1 == 0) {
					// --- Si no existe, lo insertamos como inactivo ---
					$sqlprov = [
						'prov_nic' => $proveedor->getAttribute("prov_nick"),
						'prov_razon_social' => $proveedor->getAttribute("prov_razon"),
						'prov_qv_id' => $prov_qv_id,
						'prov_rfc' => $prov_rfc,
						'prov_cp' => $proveedor->getAttribute("prov_cp"),
						'prov_representante' => $proveedor->getAttribute("prov_nombre"),
						'prov_telefono1' => $proveedor->getAttribute("prov_tel"),
						'prov_email' => $proveedor->getAttribute("prov_email"),
						'prov_activo' => 0,
					];
					$prov_id = ejecutar_db($dbpfx.'proveedores', $sqlprov, 'insertar');
				}
//				echo 'Prov: ' . $prov_id . '<br>';
				// --- Construimos contenido de botones ---
				$preg_usr_auto = "SELECT usuario FROM " . $dbpfx . "usr_permisos WHERE activo = '1' AND num_funcion = '1105000'";
				$matr_usr_auto = mysql_query($preg_usr_auto) or die("ERROR: no se logró conectar con usr_permisos! " . $preg_usr_auto);
//				echo $preg_usr_auto;
				while($man = mysql_fetch_array($matr_usr_auto)) {
					bitacora('9999995', $lang['ProvNvo'] . ' ' . $lang['ProvDesea'], $dbpfx, $prov_id .  '|' . $proveedor->getAttribute("prov_razon") . ' ' . $lang['ProvDesea'], 3, '', '', $man['usuario'], '', '', '708');
				}
			} else {
				bitacora('9999991', $lang['ProvNvo'] . ' no procesado', $dbpfx, $prov_id . '|' . $proveedor->getAttribute("prov_razon") . ' no procesado', 3, '', '', 701, '', '', '708');
				rename($ruta.$archxml, $ruta . 'procesados/' . time() . '-' .$archxml. '.bloqueado');
				// --- Avisar al proveedor que el comprador rechaza sus cotizaciones y que no insista ---
				$mtime = substr(microtime(), (strlen(microtime())-3), 3);
				$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
				$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
				$xml .= '		<Solicitud tiempo="' . date('YmdHis') . $mtime . '">80</Solicitud>'."\n";
				$xml .= '		<Proveedor vendedor="' . $prov_qv_id . '" mensaje="Proveedor bloqueado por comprador, contactar para remover bloqueo." />'."\n";
				$xml .= '	</Comprador>'."\n";
				$xmlnom = $nick . '-Bloqueo-' . date('YmdHis') . $mtime . '.xml';
				file_put_contents("../qv-salida/".$xmlnom, $xml);
			}
		}
	}
}
?>
