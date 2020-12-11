<?php 
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/ordenes.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);} // echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k = limpiar_cadena($v);} // echo $k.' -> '.$v.' | ';

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

	/*  ----------------  obtener nombres de aseguradoras   ------------------- */
	$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_nic, prov_def, prov_dde FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras! " . $consulta);
	while ($aseg = mysql_fetch_array($arreglo)) {
		$ase[$aseg['aseguradora_id']]['logo'] = $aseg['aseguradora_logo'];
		$ase[$aseg['aseguradora_id']]['nic'] = $aseg['aseguradora_nic'];
	}
	$ase[0]['logo'] = 'particular/logo-particular.png';
	$ase[0]['nic'] = 'Particular';
	/*  ----------------  nombres de aseguradoras   ------------------- */

if (($accion==='insertar') || ($accion==='actualizar') || ($accion==='asignar') || ($accion==='confcancelar') || ($accion==='recepcion') || ($accion==='cambiapol') || $accion==='cancelar') { 
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">' ."\n";
}

if($accion==="recepcion") {

	$funnum = 1040000;
	$mensaje = '';
	$datoing = strtoupper(limpiarString($placas));
	$kms = limpiarNumero($kms);
	$obs = limpiar_cadena($obs);
	$msg = '';
	$error = 'no';
	if($_SESSION['codigo'] == '75') {

	//  ----------------  obtener nombres de usuarios   ------------------- 
		$pregusu = "SELECT nombre, apellidos, usuario, codigo, activo FROM " . $dbpfx . "usuarios";
		$matrusu = mysql_query($pregusu) or die("ERROR: Fallo selección de usuarios! " . $pregusu);
		while ($ases = mysql_fetch_array($matrusu)) {
			$usuario[$ases['usuario']] = $ases['nombre'] . ' ' . $ases['apellidos'];
		}
		$usuario[1] = 'el Cliente';
		$usuario[2] = 'Otra persona';
	//  ----------------  ---------------------------   ------------------- 

		if($conductor == '' || $conductor == '0') { $error = 'si'; $msg .= 'SELECCIONE EL CONDUCTOR.<br>'; }
		if($datoing == '') { $error = 'si'; $msg .= 'FALTARON LAS PLACAS.<br>'; }
		if($kms == '') { $error = 'si'; $msg .= 'NO PUSO LOS KILOMETROS.<br>'; }
		unset($_SESSION['ord']);
		$_SESSION['ord']['placas'] = $datoing;
		$_SESSION['ord']['kms'] = $kms;
		$_SESSION['ord']['obs'] = $obs;
		if($error == 'si') {
			$_SESSION['msjerror'] = $msg;
			redirigir('ordenes-de-trabajo.php');
		}
	}
	
	if(($_SESSION['codigo'] == '75' && $movimiento == '1') || $_SESSION['codigo'] < '75') {
		$preg0 = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_serie = '" . $datoing . "' OR vehiculo_placas = '" . $datoing . "'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección! ".$preg0);
		$veh = mysql_fetch_array($matr0);
		$filas = mysql_num_rows($matr0);

		if($filas == 1) {

			$preg = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_vehiculo_id = '" . $veh['vehiculo_id'] . "' AND orden_estatus < '90'";
			$matr = mysql_query($preg) or die("ERROR: Fallo selección! " . $preg);
			$ord = mysql_fetch_array($matr);
			$fila = mysql_num_rows($matr);
		
			if($fila > 0 && $multiorden != 1) {
				$parametros = "orden_id ='" . $ord['orden_id'] . "'";
				$sql_array = array('orden_sitio_actual' => $_SESSION['localidad']);
				ejecutar_db($dbpfx . 'ordenes', $sql_array, 'actualizar', $parametros);
				$_SESSION['msjerror'] .= 'Vehículo reingresado al Taller.<br>';

				// --- Actualizar la fecha de ingresó si esta colocada la variable $actualiza_fech_ingreso --

				if($actualiza_fech_ingreso == 1) {
					unset($sql_array);
					$sql_array = [
						'orden_fecha_recepcion' => date('Y-m-d H:i:s'),
					];
					$parametros = "orden_id ='" . $ord['orden_id'] . "'";
					ejecutar_db($dbpfx . 'ordenes', $sql_array, 'actualizar', $parametros);
				}

				if($_SESSION['codigo'] == '75') {
					$vig_array = array(
						'orden_id' => $ord['orden_id'],
						'vig_localidad' => $_SESSION['localidad'],
						'vig_tipo' => $movimiento,
						'vig_placas' => $datoing,
						'vig_kms' => $kms,
						'vig_conductor' => $conductor,
						'vig_usuario' => $_SESSION['usuario'],
						'vig_fecha' => date('Y-m-d H:i:s'),
						'vig_obs' => $obs,
					);
					ejecutar_db($dbpfx . 'vigilancia', $vig_array, 'insertar');
					bitacora($ord['orden_id'], 'Reingreso al Taller', $dbpfx, $datoing . ': Vehículo ingresado a ' . $ubicaciones[$_SESSION['localidad']] . ' por ' . $usuario[$conductor] . ' con ' . $kms . ' Kilometros. Obs: ' . $obs, 5);
					unset($_SESSION['ord']);
					unset($vig_array);
					redirigir('ordenes-de-trabajo.php');
				} else {
					bitacora($ord['orden_id'], 'Reingreso al Taller', $dbpfx, 'Reingreso al Taller', 0);
					redirigir('ordenes.php?accion=consultar&orden_id=' . $ord['orden_id']);
				}
			} else {
				$sql_array = array('orden_vehiculo_id' => $veh['vehiculo_id'],
					'orden_vehiculo_marca' => $veh['vehiculo_marca'],
					'orden_vehiculo_tipo' => $veh['vehiculo_tipo'],
					'orden_vehiculo_color' => $veh['vehiculo_color'],
					'orden_vehiculo_placas' => $veh['vehiculo_placas'],
					'orden_cliente_id' => $veh['vehiculo_cliente_id'],
					'orden_fecha_recepcion' => date('Y-m-d H:i:s'),
					'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'),
					'orden_asesor_id' => $_SESSION['usuario'],
					'orden_sitio_ingreso' => $_SESSION['localidad'],
					'orden_sitio_actual' => $_SESSION['localidad'],
					'orden_ubicacion' => 'Recepción',
					'orden_alerta' => '0');
				if($veh['vehiculo_flota'] > 0) {
					$sql_array['orden_estatus'] = '1';
					$sql_array['orden_servicio'] = '4';
					$sql_array['orden_categoria'] = '2';
				} else {
					$sql_array['orden_estatus'] = '17';
				}
			}
			if($metrico > 0) {
				$sql_array['orden_metrico'] = $metrico;
			}
			if($confolio == '1') {
				$msj='No registrar en bitácora';
				$oid = ejecutar_db($dbpfx . 'ordenes', $sql_array, 'insertar');
			} else {
				$orden_id = ejecutar_db($dbpfx . 'ordenes', $sql_array, 'insertar');
				if($_SESSION['codigo'] == '75') {
					$vig_array = array(
						'orden_id' => $orden_id,
						'vig_localidad' => $_SESSION['localidad'],
						'vig_tipo' => $movimiento,
						'vig_placas' => $datoing,
						'vig_kms' => $kms,
						'vig_conductor' => $conductor,
						'vig_usuario' => $_SESSION['usuario'],
						'vig_fecha' => date('Y-m-d H:i:s'),
						'vig_obs' => $obs,
					);
					ejecutar_db($dbpfx . 'vigilancia', $vig_array, 'insertar');
					bitacora($orden_id, 'Cambio a estatus ' . $sql_array['orden_estatus'] . ' Creación de nueva OT para vehículo preregistrado desde Vigilancia', $dbpfx, $datoing . ': Creación de nueva Orden de Trabajo desde Vigilancia en ' . $ubicaciones[$_SESSION['localidad']] . ' para vehículo con ' . $kms . ' Kilometros. Obs: ' . $obs, 5);
					unset($_SESSION['ord']);
					unset($vig_array);
				} else {
					bitacora($orden_id, 'Cambio a estatus ' . $sql_array['orden_estatus'] . ' Creación de nueva OT para vehículo preregistrado', $dbpfx);
				}
			}

// ----------- Uso futuro cuando todas las tablas "ordenes" incluyan la columna "oid" aun cuando no la utilicen.  -----------------
			/* 
			if($multiorden != 1) {
				$parametros = "oid ='" . $orden_id . "'";
				$sql_data_array = array('orden_id' => $orden_id);
				ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
			}
			*/
			//  --------------------------------------------------------------------------------

// ---------- Agrega los presupuestos previos como tareas de la nueva OT listos para aceptar y modificar o cancelar  -------------- 

			if($confolio != '1' && $multiorden != 1) {
				$preg2 = "SELECT previa_id FROM " . $dbpfx . "previas WHERE previa_vehiculo_id = '" . $veh['vehiculo_id'] . "' AND previa_estatus = '99'";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de previas! " . $preg2);
				$fila2 = mysql_num_rows($matr2);
				if($fila2 > 0) {
					while ($prev = mysql_fetch_assoc($matr2)) {
						$actprevia = array();
						$preg3 = "SELECT doc_id FROM " . $dbpfx . "documentos WHERE previa_id = '" . $prev['previa_id'] . "'";
						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de documentos! ". $preg3);
						$param = "previa_id = '" . $prev['previa_id'] . "'";
						$actprevia['orden_id'] = $orden_id;
						ejecutar_db($dbpfx . 'documentos', $actprevia, 'actualizar', $param);

						$preg3 = "SELECT fact_id FROM " . $dbpfx . "facturas_por_cobrar WHERE previa_id = '" . $prev['previa_id'] . "'";
						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de facturas por cobrar! ". $preg3);
						$param = "previa_id = '" . $prev['previa_id'] . "'";
						$actprevia['orden_id'] = $orden_id;
						ejecutar_db($dbpfx . 'facturas_por_cobrar', $actprevia, 'actualizar', $param);

						$preg3 = "SELECT * FROM " . $dbpfx . "subordenes WHERE previa_id = '" . $prev['previa_id'] . "'";
						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de subordenes! " . $preg3);
						while ($sub = mysql_fetch_assoc($matr3)) {
							$subori = $sub['sub_orden_id'];
							$sub['sub_orden_id'] = NULL;
							$sub['previa_id'] = NULL;
							$sub['orden_id'] = $orden_id;
							$sub_orden_id = ejecutar_db($dbpfx . 'subordenes', $sub, 'insertar');
							bitacora($orden_id, 'Tarea ' . $sub_orden_id . ' creada desde Presupuesto Previo ' . $prev['previa_id'], $dbpfx, 'Tarea ' . $sub_orden_id . ' creada desde Presupuesto Previo ' . $prev['previa_id'], '0', $sub_orden_id);
							$preg4 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $subori . "'";
							$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de orden productos! " . $preg4);
							$presupuesto = 0; $sub_partes = 0; $sub_consumibles = 0; $sub_mo = 0; $tiempo = 0;
							while ($op = mysql_fetch_assoc($matr4)) {
								unset($op['op_id']);
								$op['sub_orden_id'] = $sub_orden_id;
								// ------ Determina si los items de las tareas deben copiarse como presupuestos o autorizados ------
								if($particpres != 1) {
									unset($op['op_pres']);
								}
								ejecutar_db($dbpfx . 'orden_productos', $op, 'insertar');
							}
							if($particpres == 1) {
								unset($sub);
								$sub['sub_presupuesto'] = 0;
								$sub['sub_impuesto'] = NULL;
								$sub['sub_partes'] = NULL;
								$sub['sub_consumibles'] = NULL;
								$sub['sub_mo'] = NULL;
								$sub['sub_horas_programadas'] = NULL;
								$param = "sub_orden_id = '" . $sub_orden_id . "'";
								ejecutar_db($dbpfx . 'subordenes', $sub, 'actualizar', $param);
							}
						}
						unset($actprevia);
						$param = "previa_id = '" . $prev['previa_id'] . "'";
						$actprevia['previa_estatus'] = 91;
						ejecutar_db($dbpfx . 'previas', $actprevia, 'actualizar', $param);
					}
					$param = "orden_id = '" . $orden_id . "'";
					unset($sql_array);
					$sql_array = array('orden_servicio' => '1');
					ejecutar_db($dbpfx . 'ordenes', $sql_array, 'actualizar', $param);
					actualiza_orden ($orden_id, $dbpfx);
				}
			}
			// -----------------------------------------------------------------------------------------------
			if($_SESSION['codigo'] == '75') {
				unset($_SESSION['ord']);
				redirigir('ordenes-de-trabajo.php');
			}

			if($confolio != '1') {
				redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
			} else {
				redirigir('ordenes.php?accion=consultar&oid=' . $oid);
			}

		} elseif($filas < 1) {
			$preg = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE (orden_vehiculo_tipo = '" . $datoing . "' OR orden_vehiculo_placas = '" . $datoing . "') AND orden_estatus = '17'";
			$matr = mysql_query($preg) or die("ERROR: Fallo selección! " . $preg);
			$fila = mysql_num_rows($matr);

			if($fila > 0 && $multiorden != 1) {
				$ord = mysql_fetch_array($matr);
				if($_SESSION['codigo'] == '75') {
					$vig_array = array(
						'orden_id' => $ord['orden_id'],
						'vig_localidad' => $_SESSION['localidad'],
						'vig_tipo' => $movimiento,
						'vig_placas' => $datoing,
						'vig_kms' => $kms,
						'vig_conductor' => $conductor,
						'vig_usuario' => $_SESSION['usuario'],
						'vig_fecha' => date('Y-m-d H:i:s'),
						'vig_obs' => $obs,
					);
					ejecutar_db($dbpfx . 'vigilancia', $vig_array, 'insertar');
					bitacora($ord['orden_id'], 'Reingreso al Taller', $dbpfx, $datoing . ': Vehículo reingresado a ' . $ubicaciones[$_SESSION['localidad']] . ' por ' . $usuario[$conductor] . ' con ' . $kms . ' Kilometros. Obs: ' . $obs, 5);
					unset($_SESSION['ord']);
					redirigir('ordenes-de-trabajo.php');
				} else {
					$_SESSION['msjerror'] = 'Ya existe en Recepción un vehículo con el dato ' . $datoing;
					redirigir('ordenes.php?accion=consultar&orden_id=' . $ord['orden_id']);
				}
			}

			$sql_array = array('orden_estatus' => '17',
				'orden_vehiculo_tipo' => $datoing,
				'orden_vehiculo_placas' => $datoing,
				'orden_fecha_recepcion' => date('Y-m-d H:i:s'),
				'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'),
				'orden_asesor_id' => $_SESSION['usuario'],
				'orden_sitio_ingreso' => $_SESSION['localidad'],
				'orden_sitio_actual' => $_SESSION['localidad'],
				'orden_ubicacion' => 'Recepcion',
				'orden_odometro' => $kms,
				'orden_alerta' => '0');
			if($metrico > 0) {
				$sql_array['orden_metrico'] = $metrico;
			}
			if($confolio == '1') {
				$oid = ejecutar_db($dbpfx . 'ordenes', $sql_array, 'insertar');
				$msj='No registrar en bitácora';
			} else {
				$orden_id = ejecutar_db($dbpfx . 'ordenes', $sql_array, 'insertar');
				if($_SESSION['codigo'] == '75') {
					$vig_array = array(
						'orden_id' => $orden_id,
						'vig_localidad' => $_SESSION['localidad'],
						'vig_tipo' => $movimiento,
						'vig_placas' => $datoing,
						'vig_kms' => $kms,
						'vig_conductor' => $conductor,
						'vig_usuario' => $_SESSION['usuario'],
						'vig_fecha' => date('Y-m-d H:i:s'),
						'vig_obs' => $obs,
					);
					ejecutar_db($dbpfx . 'vigilancia', $vig_array, 'insertar');
					bitacora($orden_id, 'Cambio a estatus 17 Creación de nueva OT para nuevo vehículo desde Vigilancia', $dbpfx, $datoing . ': Cambio a estatus 17 Creación de nueva Orden de Trabajo desde Vigilancia en ' . $ubicaciones[$_SESSION['localidad']] . ' para vehículo con ' . $kms . ' Kilometros. Obs: ' . $obs, 5);
					unset($_SESSION['ord']);
					unset($vig_array);
					redirigir('ordenes-de-trabajo.php');
				} else {
					bitacora($orden_id, 'Cambio a estatus 17 Creación de nueva OT para nuevo vehículo', $dbpfx);
				}
			}

			// ----------- Uso futuro cuando todas las tablas "ordenes" incluyan la columna "oid" aun cuando no la utilicen.  -----------------
			/* 
			if($multiorden != 1) {
				$parametros = "oid ='" . $orden_id . "'";
				$sql_data_array = array('orden_id' => $orden_id);
				ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
			}
			*/
			// --------------------------------------------------------------------------------------------------------------------------------

		} else {
			if($_SESSION['codigo'] == '75') {
				$preg = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_vehiculo_id = '" . $veh['vehiculo_id'] . "' AND orden_estatus < '90' DESC LIMIT 1";
				$matr = mysql_query($preg) or die("ERROR: Fallo selección! " . $preg);
				$ord = mysql_fetch_array($matr);
				$vig_array = array(
					'orden_id' => $ord['orden_id'],
					'vig_localidad' => $_SESSION['localidad'],
					'vig_tipo' => $movimiento,
					'vig_placas' => $datoing,
					'vig_kms' => $kms,
					'vig_conductor' => $conductor,
					'vig_usuario' => $_SESSION['usuario'],
					'vig_fecha' => date('Y-m-d H:i:s'),
					'vig_obs' => $obs,
				);
				ejecutar_db($dbpfx . 'vigilancia', $vig_array, 'insertar');
				bitacora($ord['orden_id'], 'Reingreso al Taller', $dbpfx,  $datoing . ': Vehículo ingresado a ' . $ubicaciones[$_SESSION['localidad']] . ' por ' . $usuario[$conductor] . ' con ' . $kms . ' Kilometros. Obs: ' . $obs, 5);
				unset($_SESSION['ord']);
				unset($vig_array);
				redirigir('ordenes-de-trabajo.php');
			} else {
				$motivo = 'Existe más de un vehículo con los mismos datos: ' . $datoing;
				
				$_SESSION['msjerror'] = $motivo . ', ya se envió un reporte a Soporte AutoShop Easy, en breve será atendido, gracias!';
				unset($_SESSION['ord']);
				//redirigir('contacto.php');
				redirigir('comentarios.php?accion=registrar&confirmar=Enviar&visicom=3&msjusr=701&motivo='.$motivo);
			}
		}
		unset($_SESSION['ord']);
		redirigir('index.php');
	} elseif($_SESSION['codigo'] == '75' && $movimiento == '2') {
		$preg0 = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_serie = '" . $datoing . "' OR vehiculo_placas = '" . $datoing . "' LIMIT 1";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!".$preg0);
		$veh = mysql_fetch_array($matr0);
		$filas = mysql_num_rows($matr0);

		$preg = "SELECT orden_id, orden_estatus, orden_fecha_de_entrega FROM " . $dbpfx . "ordenes WHERE orden_vehiculo_id = '" . $veh['vehiculo_id'] . "' ORDER BY orden_id DESC LIMIT 1";
		$matr = mysql_query($preg) or die("ERROR: Fallo selección!".$preg);
		$ord = mysql_fetch_array($matr);
		$orden_id = $ord['orden_id'];
		$param = " orden_id = '$orden_id'";
		if($conductor == '1') {
			if($ord['orden_estatus'] < '4' || $ord['orden_estatus'] == '17' || $ord['orden_estatus'] == '18' || ($ord['orden_estatus'] >= '24' && $ord['orden_estatus'] <= '29') || $ord['orden_estatus'] == '20') {
				$sql_data['orden_ubicacion'] = 'Transito';
			} else {
				$sql_data['orden_ubicacion'] = 'Entrega al Cliente';
				if(is_null($ord['orden_fecha_de_entrega']) || $ord['orden_fecha_de_entrega'] == '0000-00-00 00:00:00' || $ord['orden_fecha_de_entrega'] == '') {
					$sql_data['orden_fecha_de_entrega'] = date('Y-m-d H:i:s', time());
				}
			}
		} else {
			if($ord['orden_estatus'] >= '4' && $ord['orden_estatus'] <= '11') {
				$sql_data['orden_ubicacion'] = 'Salida a Pruebas o Otro Taller';
			} else {
				$sql_data['orden_ubicacion'] = 'Traslado';
			}
		}
//		echo $param;
//		print_r($sql_data);
		ejecutar_db($dbpfx . 'ordenes', $sql_data, 'actualizar', $param);
		$vig_array = array(
			'orden_id' => $ord['orden_id'],
			'vig_localidad' => $_SESSION['localidad'],
			'vig_tipo' => $movimiento,
			'vig_placas' => $datoing,
			'vig_kms' => $kms,
			'vig_conductor' => $conductor,
			'vig_usuario' => $_SESSION['usuario'],
			'vig_fecha' => date('Y-m-d H:i:s'),
			'vig_obs' => $obs,
		);
		ejecutar_db($dbpfx . 'vigilancia', $vig_array, 'insertar');
		bitacora($ord['orden_id'], 'Salida del Taller', $dbpfx, $datoing . ': Vehículo ' . ' retirado de ' . $ubicaciones[$_SESSION['localidad']] . ' por ' . $usuario[$conductor] . ' con ' . $kms . ' Kilometros. Obs: ' . $obs, 5);
		unset($_SESSION['ord']);
		unset($vig_array);
		redirigir('ordenes-de-trabajo.php');
	} else {
		$_SESSION['msjerror'] = 'POR FAVOR MARQUE ENTRADA O SALIDA';
		redirigir('ordenes-de-trabajo.php');
	}
}

elseif ($accion==="consultar") {

	if($nvavista == 1) {
		$_SESSION['nvavista'] = 1;
	}
	unset($_SESSION['msjerror']);
	unset($_SESSION['msjpass']);

	$funnum = 1040005;
	
	$infomon = validaAcceso('1040065', $dbpfx);  // Valida acceso a mostrar información monetaria.

	$partmon = validaAcceso('1045075', $dbpfx);  // Valida acceso a mostrar información monetaria de tareas particulares.
	
//	echo 'Estamos en la sección  consulta. Orden: ' . $orden_id;
	unset($_SESSION['pres']);
	unset($_SESSION['proceso']);
	$error = 'no'; $num_cols = 0;
	if ($orden_id!='' || $oid!='') {
		if($orden_id!='') { $pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'"; }
		else { $pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE oid = '$oid'"; }
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección!" . $pregunta);
		$num_cols = mysql_num_rows($matriz);
	if ($num_cols>0) {
		$mensaje='';
		$orden = mysql_fetch_array($matriz);
		$orden_id = $orden['orden_id'];
		$oid = $orden['oid'];
		if($orden['orden_metrico']===0) {$metrico='Kilometros';} else {$metrico='Millas';}
		if($confolio == 1 && $orden['orden_estatus'] == 17) {
			$vehiculo = array('tipo' => $orden['orden_vehiculo_tipo'], 'placas' => $orden['orden_vehiculo_placas'], );
		} else {
			$vehiculo = datosVehiculo($orden['orden_id'], $dbpfx);
		}
		$pregunta5 = "SELECT * FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190' ORDER BY sub_area";
		$matriz5 = mysql_query($pregunta5) or die("ERROR: Fallo selección! ".$pregunta5);
		$reporte = array(); $repdet = array(); $cuentasub = 0;
		$repo = mysql_num_rows($matriz5);
		while($am = mysql_fetch_array($matriz5)) {
			$reporte[$am['sub_reporte']]['ase'] = $am['sub_aseguradora'];
			$reporte[$am['sub_reporte']]['sub'] = $am['sub_orden_id'];
			$reporte[$am['sub_reporte']]['pol'] = $am['sub_poliza'];
			$reporte[$am['sub_reporte']]['pres'] = $reporte[$am['sub_reporte']]['pres'] + $am['sub_presupuesto'];
			$reporte[$am['sub_reporte']]['ref'] = $reporte[$am['sub_reporte']]['ref'] + $am['sub_partes'];
			$reporte[$am['sub_reporte']]['pint'] = $reporte[$am['sub_reporte']]['pint'] + $am['sub_consumibles'];
			$reporte[$am['sub_reporte']]['mo'] = $reporte[$am['sub_reporte']]['mo'] + $am['sub_mo'];
			$reporte[$am['sub_reporte']]['fv'] = $am['sub_fecha_valaut'];
			if($am['sub_deducible'] > $reporte[$am['sub_reporte']]['dedu']) {
				$reporte[$am['sub_reporte']]['dedu'] = $am['sub_deducible'];
			}
			if($am['sub_paga_deducible'] == 0 || is_null($am['sub_paga_deducible'])) {
				$reporte[$am['sub_reporte']]['paga'] = 'Indefinido';
			} elseif($am['sub_paga_deducible'] == 1) {
				$reporte[$am['sub_reporte']]['paga'] = 'Sí';
			} elseif($am['sub_paga_deducible'] == 2) {
				$reporte[$am['sub_reporte']]['paga'] = 'No';
			}
			$repdet[$am['sub_reporte']][$am['sub_orden_id']]['descrip'] = $am['sub_descripcion'];
			$repdet[$am['sub_reporte']][$am['sub_orden_id']]['area'] = $am['sub_area'];
			// ---- Estatus administrativo --------------
			$repdet[$am['sub_reporte']][$am['sub_orden_id']]['admin'] = $am['sub_estatus'];
			// ---- Estatus reparación ------------------
			$preg4 = "SELECT * FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" . $am['sub_orden_id'] . "' ORDER BY seg_id DESC LIMIT 1";
			$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de seguimientos! ".$preg4);
			$fila4 = mysql_num_rows($matr4);
			$seg = mysql_fetch_array($matr4);
			$repdet[$am['sub_reporte']][$am['sub_orden_id']]['rep'] = $seg['seg_tipo'];
			// ---- Estatus refacciones -----------------
			$repdet[$am['sub_reporte']][$am['sub_orden_id']]['refpend'] = $am['sub_refacciones_recibidas'];
			if($fila4 > 0) {
				if($seg['seg_tipo'] > 0 && $seg['seg_tipo'] < 7) { $estrep = 1; }
				elseif($seg['seg_tipo'] == 7) { $estrep = 2; }
				else { $estrep = 0; }
				$cuentasub++;
				$promrep = $promrep + $estrep;
			}
			// ---- Estatus monetario -------------------
			if($am['sub_estatus'] < '130') {
				$preg4 = "SELECT p.pedido_pagado, p.pedido_tipo, p.pedido_estatus FROM " . $dbpfx . "pedidos p, " . $dbpfx . "orden_productos o WHERE o.sub_orden_id = '" . $am['sub_orden_id'] . "' AND p.pedido_estatus != '90' AND o.op_pedido = p.pedido_id";
				$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de pedidos! ".$preg4);
				$fila4 = mysql_num_rows($matr4);
				if($fila4 > 0) {
					$pedpag = 0;
					while($ped =  mysql_fetch_array($matr4)) {
						if(($ped['pedido_pagado'] == '0' && $ped['pedido_tipo'] > 1) || ($ped['pedido_estatus'] < '10' && $ped['pedido_tipo'] == 1)) {
							$pedpag = 2;
						}
					}
				} else { $pedpag = 'na'; }
				$repdet[$am['sub_reporte']][$am['sub_orden_id']]['pedidos'] = $pedpag;

				if($am['fact_id'] > 0) {
					$factcobrada = 0;
					$preg4 = "SELECT fact_cobrada FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '" . $am['fact_id'] . "' AND fact_cobrada = '1'";
					$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de facturas! ".$preg4);
					$fila4 = mysql_num_rows($matr4);
					if($fila4 < 1) {
						$factcobrada = 1;
					}
				} elseif($am['sub_presupuesto'] > '0') {
					$factcobrada = 2;
				} else {
					$factcobrada = 'na';
				}
				$repdet[$am['sub_reporte']][$am['sub_orden_id']]['fact_id'] = $factcobrada;

				if($am['recibo_id'] > 0) {
					$destajopag = 1;
					$preg4 = "SELECT recibo_id FROM " . $dbpfx . "destajos WHERE recibo_id = '" . $am['recibo_id'] . "' AND saldado = '1'";
					$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de destajos! ".$preg4);
					$fila4 = mysql_num_rows($matr4);
					if($fila4 == 1) {
						$destajopag = 0;
					}
				} elseif($am['sub_mo'] > '0') {
					$destajopag = 2;
				} else {
					$destajopag = 'na';
				}
				$repdet[$am['sub_reporte']][$am['sub_orden_id']]['recibo_id'] = $destajopag;
			}

			// -------------- Obtener fecha de valuación de cada Siniestro --------------
			if(!is_null($am['sub_fecha_valaut'])) {
				$info_valuacion[$am['sub_reporte']] = $am['sub_fecha_valaut'];
			}
		}
		$asesor_id=$orden['orden_asesor_id'];
		$pregunta2 = "SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '$asesor_id'";
		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección! " . $pregunta2);
		$asesor = mysql_fetch_array($matriz2);

// ------ Inicio de presentacion de informacion ---
		if($error == 'no') {
echo '
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header" style="min-height: 60px;"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '&nvavista=2"><img src="idiomas/' . $idioma . '/imagenes/otra-vista.png" alt="Regresar a la version anterior" title="Regresar a la version anterior"></a>
						<div class="panel-title">

		  					<span><big>Orden de trabajo: <b>' . $orden['orden_id'] . '</b> Cliente: <a href="personas.php?accion=consultar&cliente_id=' . $orden['orden_cliente_id'] . '">' . $orden['orden_cliente_id'] . '</a> Vehículo: <a href="vehiculos.php?accion=consultar&vehiculo_id=' . $orden['orden_vehiculo_id'] . '">' . $orden['orden_vehiculo_id'] . '</a>'."\n";
			
			echo '</big></span>
						</div>
			  		</div>
				</div>
			</div>
			<br>'."\n"; ?>

			<div class="col-12">
				<!-- Estatus y datos -->
				
				<div class="col-lg-4 col-md-4 col-sm-6" style="margin: 5px;">
					<div style="box-shadow: 3px 4px 10px 0px #484848; border-top-left-radius: 10px; border-radius: 10px; background-color: white; margin-bottom: 10px;">
						<?php if ($repo > 0) { ?>
						<div class="row"><?php
					     	// ---------------------- Estatus general de la OT --------------------
							$promrep = ($promrep / $cuentasub);
							echo '<div class="col-lg-2 col-md-2 col-2">' . constant('ALARMA_' . $orden['orden_alerta']) . '</div>';
							echo '<div class="col-lg-3 col-md-3 col-3"><b>' . constant('ORDEN_ESTATUS_' . $orden['orden_estatus']) . '</b></div>';

							//$pres = $pres + $orden['sub_presupuesto'];
							echo '<div class="col-lg-6 col-md-6 col-6"></div>';
							//echo '<br>' . $lang['Reparación'] . ' ';
							//if($promrep == 2) { echo $lang['terminada']; }
							//elseif($promrep > 0) { echo $lang['en progreso']; }
							//else { echo $lang['por iniciar']; }
							if($orden['orden_ref_pendientes'] == '2') { echo '<br>' . $lang['Ref Estructurales'] . ''; } 
							elseif($orden['orden_ref_pendientes'] == '1') { echo '<br>' . $lang['Ref Pend'] . ''; }
							else { echo '<br>' . $lang['Sin Ref Pend']; } ?>
						</div>
					
							<?php
							//echo '<<pre>';
							//print_r($reporte);
							//echo '</pre>';
							
							foreach($reporte as $sin => $sindet) {
								// ------ Verificar si un supervisor de aseguradora puede ver esta OT.
								if($_SESSION['codigo']=='2000') {
									$mostrar = 0;
									$miflota = explode('|', $_SESSION['aseg']);
									foreach($miflota as $k) {
									//						echo $sindet['ase'] . ' ' . ' ' . $alerta['orden_id'] . ' ' . $k;
										if($sindet['ase'] == $k) { $mostrar = 1;}
									}	
								}

								if(($_SESSION['codigo'] >= '2000' && $mostrar == 0) || ($restrictotoper == 1 && ($_SESSION['codigo'] >= '60' && $_SESSION['codigo'] <= '75'))) {
									// Si es usuario de aseguradora no se debe mostrar trabajos particulares
								} else {
									echo '<a name="' . $sin . '"></a>
									<hr style="color: #2c5ba0;height: 15px;background-color: #2c5ba0;margin-top: 10px;margin-bottom: 10px; ">
								<table cellspacing="0" cellpadding="2" border="0" width="100%" style="background-color: ; text-align:right; line-height:12px;">'."\n";
									//				print_r($sindet);




									echo '	<tr>
											<td colspan="3" style="text-align: left; background-color: #fff;"><b>';
									if($sindet['ase'] > 0) {
										echo '# Siniestro: ' . $sin . '<br> # Poliza: ' . $sindet['pol'] . '<br>';
										echo '<br> Paga Deducible? ';
										echo $sindet['paga'];
										echo ' ($' . number_format($sindet['dedu'],2) . ')';
										echo'</td><td style="text-align: left; background-color: #65baec;"><b></b></td><td style="text-align: left; background-color: #5494e8;"><b></b></td><td style="text-align: left; background-color: #3b7dd3;"><b></b></td><td style="text-align: left; background-color: #2c5ba0;"><b></b></td></tr>'."\n";
									} else {
										// Valida acceso a mostrar registro de Anticipo.
										if(validaAcceso('1040085', $dbpfx) == 1) {
											echo '<a class="btn btn-warning btn-sm" href="entrega.php?accion=regcobro&orden_id=' . $orden_id . '&anticipo=1">Recibir Anticipo</a></b>';
										}
										echo '
											</td>
											<td style="text-align: left; background-color: #65baec;" rowspan="2"></td><td style="text-align: left; background-color: #5494e8;" rowspan="2"></td><td style="text-align: left; background-color: #3b7dd3;" rowspan="2"></td><td style="text-align: left; background-color: #2c5ba0;" rowspan="2"></td></tr>';
										echo '							<tr><td colspan="3" style="text-align: left; background-color: #fff;"><b>';
										$pregant = "SELECT c.cobro_monto FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE cf.orden_id = '$orden_id' AND c.cobro_tipo = '5' AND c.cobro_id = cf.cobro_id";
										$matrant = mysql_query($pregant) or die("ERROR: Fallo selección de Cobros!".$pregant);
										$anticipo = 0;
										while($cob = mysql_fetch_array($matrant)) {
											$anticipo = $anticipo + $cob['cobro_monto'];
										}
										echo 'Anticipos: $' . number_format($anticipo,2) . '</b></td></tr>'."\n";		
									}






									echo '							<tr><td style="width:200px; vertical-align:top; text-align:left; background-color: white; height: 50px;" colspan="2"><img src="' . $ase[$sindet['ase']]['logo'] . '" alt="' . $ase[$sindet['ase']]['nic'] . '" title="' . $ase[$sindet['ase']]['nic'] . '" style="max-width: 143px;"></td>
									<td style="width:16px; background-color: white; padding-bottom: 110px;" rowspan="5">';
									// Valida acceso a modificar Siniestro o Trabajo Particular.
									if(validaAcceso('1040075', $dbpfx) == '1' || $_SESSION['rol02'] == '1' || $_SESSION['rol05'] == '1'  || $_SESSION['rol06'] == '1' || $_SESSION['rol12'] == '1') {
										echo '<a href="presupuestos.php?accion=trabmodificar&orden_id=' . $orden_id . '&reporte=' . $sin . '&poliza=' . $poliza . '&pagadedu=' . $deducible . '&pagadedu=' . $sindet['paga'] . '"><img src="idiomas/' . $idioma . '/imagenes/prod-editar.png" alt="' . $lang['Modificar Trabajo'] . '" title="' . $lang['Modificar Trabajo'] . '"></a>';
									}
									$vermon = 0;
									if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $sindet['ase'] < 1)) {
										$vermon = 1;
									echo '</td>';



									echo '<td style="background-color: #65baec; color: white;" rowspan="5" width="44">
											
											<b><div class="txtv">Administrativo</div></b>
												
												</td>';
									echo '<td style="background-color: #5494e8; color: white;" rowspan="5" width="44">
											<b><div class="txtv">Reparación</div></b>
												</td>';
									echo '<td style="background-color: #3b7dd3; color: white;" rowspan="5" width="44">';
									if($_SESSION['codigo'] < '2000') {
										echo '<a href="refacciones.php?accion=gestionar&orden_id=' . $orden['orden_id'] . '" style="text-decoration: none;color: white;">';
									}
									echo '<b><div class="txtv">Refacciones</div></b>';
									if($_SESSION['codigo'] < '2000') { echo '</a>'; }
									echo '</td><td style="background-color: #2c5ba0; color: white;" rowspan="5" width="44">
											
											<b><div class="txtv">Monetario</div></b>
												
												</td></tr>




											<tr>
												<td style="background-color: white;"><b>Refacciones</b></td><td style="background-color: white;">';
									if($vermon == 1) { echo '$' . number_format($sindet['ref'],2); }
									echo '</td></tr>'."\n";
									echo '							<tr><td style="background-color: white;"><b>Mano de Obra</b></td><td style="background-color: white;">';
									if($vermon == 1) { echo '$' . number_format($sindet['mo'],2); }
									echo '</td></tr>'."\n";
									echo '							<tr><td style="background-color: white;"><b>Consumibles</b></td><td style="background-color: white;">';
									if($vermon == 1) { echo '$' . number_format($sindet['pint'],2); }
									echo '</td></tr>'."\n";
									$stotal = $sindet['ref'] + $sindet['pint'] + $sindet['mo'];
									echo '							<tr><td style="background-color: white;"><b>Total</b></td><td style="background-color: white;">';
									if($vermon == 1) { echo '$' . number_format($stotal,2); }
									$pres = $pres + $stotal;
									}

									echo '</tr>
									</table>'."\n";




									echo '						<table cellspacing="0" cellpadding="2" border="0" width="100%" style="">'."\n";
									$fondo = 'claro';
									foreach($repdet[$sin] as $tarea => $val) {
										if(($val['admin'] > 103 && $val['admin'] <= 112) || $val['admin'] == 121) { $tipo_prod =  'proceso'; } else { $tipo_prod = 'presupuestos'; }
										
										echo '							<tr class="' . $fondo . '">'."\n";
										echo '<td width="220" colspan="2">';
										if((validaAcceso('1045010', $dbpfx) == '1' || $_SESSION['rol02'] == '1' || $_SESSION['rol05'] == '1'  || $_SESSION['rol06'] == '1' || $_SESSION['rol12'] == '1') && $_SESSION['codigo'] < '2000') {
											echo '&nbsp;<a href="presupuestos.php?accion=modificar&sub_orden_id=' . $tarea . '"><img src="idiomas/' . $idioma . '/imagenes/prod-editar.png" alt="' . $lang['Modificar Tarea'] . '" title="' . $lang['Modificar Tarea'] . '"></a> ';
										}
										echo ' <i>' . constant('NOMBRE_AREA_' . strtoupper($val['area'])) .', Tarea: <b> ' . $tarea . ' </b></i>';
								
										echo '<br>';
										//echo '' . $tarea . ': ' . $val['descrip'] . '<br>';
										if($_SESSION['codigo'] < '2000') {
											echo '<a href="' . $tipo_prod . '.php?accion=consultar&orden_id=' . $orden_id . '#' . $tarea . '">' . strtoupper(constant('TAREA_ESTATUS_' . $val['admin'])) . '</a>';
										} else {
											echo strtoupper(constant('TAREA_ESTATUS_' . $val['admin']));
										}
										echo '</td>'."\n";
										// ----------- Determinar porcentaje de avance administrativo -----------
										$peradmin = 0;
										if($val['admin'] == 101) { $peradmin = 0.1; }
										elseif($val['admin'] == 124 || $val['admin'] == 127) { $peradmin = 0.2; }
										elseif($val['admin'] == 128) { $peradmin = 0.3; }
										elseif($val['admin'] == 129) { $peradmin = 0.4; }
										elseif($val['admin'] == 120) { $peradmin = 0.5; }
										elseif($val['admin'] == 102) { $peradmin = 0.6; }
										elseif(($val['admin'] >= 104 && $val['admin'] <= 111) || $val['admin'] == 121) { $peradmin = 0.7; }
										elseif($orden['orden_estatus'] == 14 || ($val['admin'] >= 112 && $val['admin'] <= 114)) { $peradmin = 0.8; }
										elseif($orden['orden_estatus'] == 15) { $peradmin = 0.8; }
										elseif($orden['orden_estatus'] == 16) { $peradmin = 0.9; }
										elseif($orden['orden_estatus'] == 99) { $peradmin = 1; }
										echo '								<td width="44">';
										if($_SESSION['codigo'] < '2000') {
											if($peradmin <= 0.7) { $barra = 2; }
											elseif($peradmin <= 0.9) { $barra = 1; }
											else { $barra = 0; }
											echo '<a href="' . $tipo_prod . '.php?accion=consultar&orden_id=' . $orden_id . '#' . $tarea . '"><img src="imagenes/alarma_' . $barra . '.jpg" style="height: 20px; width: ' . ($peradmin * 44) . 'px;" /></a><br><a href="' . $tipo_prod . '.php?accion=consultar&orden_id=' . $orden_id . '#' . $tarea . '">' . round(($peradmin * 100),0) . '%</a>';
										} else {
											echo '<img src="imagenes/alarma_' . $orden['orden_alerta'] . '.jpg" style="height: 20px; width: ' . ($peradmin * 44) . 'px;" /><br>' . round(($peradmin * 100),0) . '%';
										}
										echo '</td>'."\n";
										// ----------- Determinar porcentaje de avance de reparación -----------
										$perrep = 0;
										if($val['rep'] == 1) { $perrep = 0.25; }
										elseif($val['rep'] == 2) { $perrep = 0.5; }
										elseif($val['rep'] == 5) { $perrep = 0.75; }
										elseif($val['rep'] == 7) { $perrep = 1; }
										echo '								<td width="44">';
										if($_SESSION['codigo'] < '2000') {
											if($perrep <= 0.5) { $barra = 2; }
											elseif($perrep <= 0.75) { $barra = 1; }
											else { $barra = 0; }
											echo '<a href="' . $tipo_prod . '.php?accion=consultar&orden_id=' . $orden_id . '#' . $tarea . '"><img src="imagenes/alarma_' . $barra . '.jpg" style="height: 20px; width: ' . ($perrep * 44) . 'px;" /></a><br><a href="' . $tipo_prod . '.php?accion=consultar&orden_id=' . $orden_id . '#' . $tarea . '">' . round(($perrep * 100),0) . '%</a>';
										} else {
											echo '<img src="imagenes/alarma_' . $orden['orden_alerta'] . '.jpg" style="height: 20px; width: ' . ($perrep * 44) . 'px; margin-right: 1px;margin-left: 1px;" /><br>' . round(($perrep * 100),0) . '%';
										}
										echo '</td>'."\n";
										// ----------- Determinar porcentaje de avance de refacciones -----------
										$perref = 0;
										$pregant = "SELECT op_pedido, op_ok, op_costo, op_item_seg, op_pres FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '$tarea' AND op_tangible = '1'";
										$matrant = mysql_query($pregant) or die("ERROR: Fallo selección de Refacciones! ".$pregant);
										$cueref = 0; $refper = array(); $cuerefaut = 0;
										while($op = mysql_fetch_array($matrant)) {
											if($op['op_ok'] == 1 && $op['op_item_seg'] < 1) { $refper[3]++; $cueref++; }
											elseif($op['op_pedido'] > 0) { $refper[2]++; $cueref++; }
											elseif($op['op_costo'] > 0) { $refper[1]++; $cueref++; }
											if($op['op_pres'] < 1) { $cuerefaut++; }
										}
										if($cueref > 0) {
											if($cuerefaut > $cueref) { $cueref = $cuerefaut; }
											$perref = (($refper[3]/$cueref)*1) + (($refper[2]/$cueref)*0.66) + (($refper[1]/$cueref)*0.33);
											if($perref <= 0.75) { $barra = 2; }
											elseif($perref <= 0.98) { $barra = 1; }
											else { $barra = 0; }
											echo '								<td width="44"><a href="refacciones.php?accion=gestionar&orden_id=' . $orden_id . '"><img src="imagenes/alarma_' . $barra . '.jpg" style="height: 20px; width: ' . ($perref * 44) . 'px; margin-right: 1px;margin-left: 1px;" /></a><br><a href="refacciones.php?accion=gestionar&orden_id=' . $orden_id . '">' . round(($perref * 100),0) . '%</a></td>'."\n";
										} else {
											echo '								<td width="44" align="center"><img src="imagenes/na.png" title="No Aplica" width="32px"></td>'."\n";
										}
										// ----------- Determinar porcentaje de avance monetario -----------
										echo '								<td width="44">'."\n";
										if($_SESSION['codigo'] < '2000') {
									
											echo '									<a href="entrega.php?accion=cobros&orden_id=' . $orden_id . '"><img src="imagenes/alarma_mon_' . $val['fact_id'] .  '.png" alt="'. $lang['Cobro a Clientes'] .'" title="'. $lang['Cobro a Clientes'] .'" width="44" ></a><br>'."\n";
											echo '									<a href="pedidos.php?accion=listar&orden_id=' . $orden_id . '"><img src="imagenes/alarma_mon_' . $val['pedidos'] . '.png" alt="'. $lang['Pago de pedidos'] .'" title="'. $lang['Pago de pedidos'] .' " width="44" ></a><br>'."\n";
											echo '									<a href="destajos.php?accion=cesta&orden_ver=' . $orden_id . '"><img src="imagenes/alarma_mon_' . $val['recibo_id'] . '.png" alt="'. $lang['Pago de Destajo'] .'" title="'. $lang['Pago de Destajo'] .'" width="44" ></a>'."\n";
										} else {
											
											echo '									<img src="imagenes/alarma_mon_' . $val['fact_id'] .  '.png" alt="'. $lang['Cobro a Clientes'] .'" title="'. $lang['Cobro a Clientes'] .'" width="44" style="margin-right: 1px;margin-left: 1px;"><br>'."\n";
											echo '									<img src="imagenes/alarma_mon_' . $val['pedidos'] . '.png" alt="'. $lang['Pago de pedidos'] .'" title="'. $lang['Pago de pedidos'] .' " width="44" style="margin-right: 1px;margin-left: 1px;"><br>'."\n";
											echo '									<img src="imagenes/alarma_mon_' . $val['recibo_id'] . '.png" alt="'. $lang['Pago de Destajo'] .'" title="'. $lang['Pago de Destajo'] .'" width="44" style="margin-right: 1px;margin-left: 1px;">'."\n";
										}
										echo '							</td></tr>'."\n";
										if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
										//echo '	<tr class="' . $fondo . '"><td colspan="">' . $tarea . ': ' . $val['descrip'] . '</td></tr>'."\n";

									}
								}
								
								echo '</table>'."\n";
								
								
							}
							echo '<big>Presupuesto Total: <b>$' . number_format($pres,2) . '</b></big>' ;
			    } else
							{ ?>
								
								<div class="col" style="min-height: 100vh; box-sizing: border-box; text-align: center; background-color: #b0b0b0; border-top-left-radius: 10px; border-radius: 10px; display: flex; justify-content: center; align-content: center; flex-direction: column;">
									<div style="padding-top: 20px;">
										<big><b>No hay Tareas para mostrar, <br>Asocia un Vehículo y después agrega descripción de daños.</b></big>
									</div>
								</div>
								
							<?php }
							?>
			    	</div>
			    </div> 
				<div class="col-lg-3 col-md-3 col-sm-5" style="margin: 5px;">
					<div class="row" align="center">
						<div class="row" style="box-shadow: 3px 4px 10px 0px #484848; border-radius: 10px; background-color: white; margin-bottom: 10px;">
							<h2 style="padding-top: 10px;padding-bottom: 10px;"><b><?php 
								echo '' . $vehiculo['marca'] . ' ' . $vehiculo['tipo'] . ' ' . $vehiculo['color'] . ' ' . $vehiculo['modelo'];
								if($vehiculo['flota'] > 0) {
									echo ' ' . $lang['NumEco'];
									if($vehiculo['placas']=='') {
										echo $orden['orden_vehiculo_tipo'];
									} else {
										echo $vehiculo['placas'];
									}
									echo ' ' . $lang['Placas'] . ' ' . $vehiculo['eco'];
								} else {
									echo '<br>' . $lang['Placas'];
									if($vehiculo['placas']=='') {
										echo $orden['orden_vehiculo_tipo'];
									} else {
										echo $vehiculo['placas'];
									}
								}
								?>

								</b></h2>
						</div>
						<div class="row" align="center">
							<div class="image-box" style="box-shadow: 3px 4px 10px 0px #484848; border-radius: 10px; margin-bottom: 10px;">
  								<a href="<?php echo $vehiculo['imagen']; ?>" target="_blank" title="<?php echo $vehiculo['marca'] . ' ' . $vehiculo['tipo'] . '  ' . $vehiculo['modelo'] . '  ' . $vehiculo['color']; ?>"><?php

  								//if (!isset($vehiculo['imagen'])) {
  								if ($vehiculo['imagen'] == '') {
								$imagen = "imagenes/marca_agua.jpg";
								// Imprimes la imagen utilizando la ruta, por ejemplo:
								echo '<img src="' . $imagen . '" height="165"/>';
								} else {
								// Lo que tengas ahora mismo para imprimir la imagen BLOB
								echo '<img src="' . $vehiculo['imagen'] . '" height="165"/>';
								} ?>
								</a>
  							</div>
  						</div>

  						<div class="row" align="center"><?php


							echo '<table style="box-shadow: 3px 4px 10px 0px #484848; border-radius: 10px; width:100%;"><tr><td>';
							echo '<table cellspacing="0" cellpadding="2" border="0" width="100%">'."\n";
							echo '<tr class="claro"><td style="text-align:center;"><img src="imagenes/avatar.png" alt="Asesor de Servicio" title="Asesor de Servicio"></td>
									<td>';
							//if((validaAcceso('1120005', $dbpfx) == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1' || $_SESSION['rol02'] == '1') && $_SESSION['codigo'] < '2000') {
							//		echo '<a href="ordenes.php?accion=otcambiar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/prod-editar.png" alt="' . $lang['Modificar Datos OT'] . '" title="' . $lang['Modificar Datos OT'] . '"></a>&nbsp;';
							//}
								echo '<b>Asesor:</b> ' . $asesor['nombre'] . ' ' . $asesor['apellidos'] . ' <b>#' . $orden['orden_asesor_id'] . '</b></td></tr>'."\n";
								if((validaAcceso('1120005', $dbpfx) == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1' || $_SESSION['rol02'] == '1') && $_SESSION['codigo'] < '2000') {
								echo '<tr class="obscuro"><td style="text-align:center;" colspan="2"><br><a href="ordenes.php?accion=otcambiar&orden_id=' . $orden_id . '"><button class="btn btn-primary"><img src="idiomas/' . $idioma . '/imagenes/prod-editar.png" alt="' . $lang['Modificar Datos OT'] . '" title="' . $lang['Modificar Datos OT'] . '"> Modificar datos generales de la OT</button></a> <br></td></tr>'."\n";
								}
								echo '				</table>'."\n";
								echo '			<table cellspacing="0" cellpadding="2" border="0" width="100%">'."\n";
								echo '				<tr class="claro"><td><b>Servicio: </b>' . constant('ORDEN_SERVICIO_' .$orden['orden_servicio']) . '</td><td><b>' . $lang['Llegó en Grua'] . '</b> ';
								if($orden['orden_grua'] == 1) { echo $lang['Sí']; }
								elseif($orden['orden_grua'] == 2) { echo $lang['No']; }
								else { echo $lang['No identificado']; }
								echo '</td></tr>'."\n";
								if($orden['orden_servicio']=='2') { echo '				<tr><td><b>OT reclamada en Garantía: </b></td><td><a href="ordenes.php?accion=consultar&orden_id=' . $orden['orden_garantia'] . '" target="_blank">' . $orden['orden_garantia'] . '</a></td></tr>'."\n";}
								echo '				<tr class="obscuro"><td><b>' . constant('UNIDAD_' . $orden['orden_metrico']) . ': </b>' . number_format($orden['orden_odometro']) . '</td><td><b>Torre: </b>' . $orden['orden_torre'] . '</td></tr>'."\n";

								echo '				<tr class="claro"><td><b>Categoría de servicio: </b></td><td>';
								if ((validaAcceso('1040080', $dbpfx) || $_SESSION['rol02'] == '1' || $_SESSION['rol06'] == '1') && $confcs == '1' ) {
									echo '					<form action="ordenes.php?accion=catserv" method="post" enctype="multipart/form-data" name="ncat">'."\n";
									echo '					<select name="categoria" size="1" onchange="document.ncat.submit()";>'."\n";
									for($se=1;$se<=4;$se++){
										echo '			<option value="' . $se . '"';
										if($se == $orden['orden_categoria']) { echo ' selected="selected" '; }
										echo '>' . constant('CATEGORIA_DE_REPARACION_' .$se) . '</option>'."\n";
									}
									echo '					</select><input type="hidden" name="orden_id" value="' . $orden_id . '" /></form>'."\n";
								} else {
									echo constant('CATEGORIA_DE_REPARACION_' .$orden['orden_categoria']);
								}
								echo '</td></tr>'."\n"; ?>
									<tr style="background-color: cadetblue;"><td style="vertical-align:top; text-align: center;border-top-width: 3px;border-top-style: solid;border-color: cadetblue;border-right-width: 3px;border-right-style: solid;border-left-width: 3px;border-left-style: solid;" colspan="2"><b><big>UBICACIÓN FISICA</big></b></td></tr>
								<?php 
								echo '				<tr class="obscuro"><td style="vertical-align:top;border-left-width: 3px;border-left-style: solid;border-color: cadetblue;"><b>Recibido en:</b></td><td style="border-right-width: 3px;border-right-style: solid;border-color: cadetblue;"> ' . $orden['orden_sitio_ingreso'] . '</td></tr>'."\n";
								echo '<tr><td style="border-left-width: 3px;border-left-style: solid;border-color: cadetblue;"><b>Ubicación Actual</b></td><td style="border-right-width:3px; border-right-style:solid; border-color:cadetblue; color:red;"><b><big>' . $orden['orden_ubicacion'] . '</big></b></td></tr>'."\n";
								echo '				<tr class="claro"><td style="border-left-width: 3px;border-left-style: solid;border-color: cadetblue;border-bottom-width: 3px;border-bottom-style: solid;"><b>Cambio de Sitio: </b></td><td style="border-bottom-width: 3px;border-bottom-style: solid;border-right-width: 3px;border-right-style: solid;border-color: cadetblue;">'."\n";
								if($cambubic == '1' && $orden['orden_estatus'] <= '39' && $orden['orden_estatus'] != '16') {
									if (validaAcceso('1040055', $dbpfx) == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
										echo '					<form action="ordenes.php?accion=cambiaubic" method="post" enctype="multipart/form-data" name="ubic">'."\n";
										echo'						<select name="nubic" onchange="document.ubic.submit()";>';
										echo '						<option value="">Cambiar Sitio Actual</option>'."\n";
										foreach($ubicaciones as $uk) {
											echo '					<option value="' . $uk . '">' . $uk . '</option>'."\n";
										}
										echo '					</select><input type="hidden" name="orden_id" value="' . $orden_id . '" /></form>'."\n";
									}
								}
								echo '</td></tr>'."\n";
								echo '				</table>'."\n";
								echo '				<table cellspacing="0" cellpadding="2" border="0" width="100%">'."\n";
								echo '				<tr class="obscuro"><td style="width: 42%;"><b>Fecha de Recepción: </b></td><td>' . $orden['orden_fecha_recepcion'] . '</td></tr>'."\n";
								if($fcompcr == 1) {
									// ------------  Habilitar el despliegue de fecha compromiso de Centro de Reparación ( que es diferente de la 
									// ------------  "Fecha Promesa de Entrega" requerida por aseguradoras  
									echo '				<tr class="claro"><td><b>Fecha Compromiso <br>Taller: </b></td><td>';?>
															<table>
																<tr>
																	
																	<td><?php echo $orden['orden_fecha_compromiso_de_taller'];?></td>
																	<td><?php
																		$retorno = 0; $retorno = validaAcceso('1040010', $dbpfx);
																		if ($retorno == '1') {
																			require_once("calendar/tc_calendar.php");
																			$compta = strtotime($orden['orden_fecha_compromiso_de_taller']);
																			echo '					<form action="ordenes.php?accion=comptaller" method="post" enctype="multipart/form-data">'."\n";
																			//instantiate class and set properties
																			$myCalendar = new tc_calendar("fecomp", true);
																			$myCalendar->setPath("calendar/");
																			$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
																			$myCalendar->setDate(date("d", $compta), date("m", $compta), date("Y", $compta));
																			//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
																			$myCalendar->disabledDay("sun");
																			$myCalendar->setYearInterval(2017, 2025);
																			$myCalendar->setAutoHide(true, 5000);

																			//output the calendar
																			$myCalendar->writeScript();
																			echo '<input type="hidden" name="orden_id" value="' . $orden_id . '" /><input class="btn btn-success btn-sm2" type="submit" value="Guardar" /></form>'."\n";
																		} ?>
																	</td>
																</tr>
															</table><?php
									echo '				</td></tr>';
								}
								if ($orden['orden_fecha_promesa_de_entrega']!='' && $orden['orden_estatus'] < 90) {
									$fpe = strtotime($orden['orden_fecha_promesa_de_entrega']);
									if($orden['orden_categoria'] == '2') {$talarma = 86400;} else {$talarma = 3600;}
									$alerta_fecha = 'alarma_preventiva';
									$preventiva = $fpe - $talarma;
									if ($fpe <= time()) {
										$alerta_fecha = 'alarma_critica';
									} elseif (($fpe - $talarma) > time()) {
										$alerta_fecha = 'alarma_normal';
									}
								} else {
									$alerta_fecha = '';
								}
								echo '			 	<tr class="obscuro"><td><b>Promesa de entrega: </b></td><td class="' . $alerta_fecha . '">';?>
														<table>
															<tr>
																<td>
																	<? echo $orden['orden_fecha_promesa_de_entrega']; ?>
																</td>
																<td><?php
																	if($fpromesa == 1) {
																		// ------------  Habilitar el despliegue de fecha compromiso de Centro de Reparación ( que es diferente de la 
																		// ------------  "Fecha Promesa de Entrega" requerida por aseguradoras  
																		$retorno = 0; $retorno = validaAcceso('1040050', $dbpfx);
																		if ($retorno == '1') {
																			require_once("calendar/tc_calendar.php");
																			$fprom1 = strtotime($orden['orden_fecha_promesa_de_entrega']);
																			echo '					<form action="ordenes.php?accion=fpromesa" method="post" enctype="multipart/form-data">'."\n";
																			//instantiate class and set properties
																			$myCalendar = new tc_calendar("fprom", true);
																			$myCalendar->setPath("calendar/");
																			$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
																			$myCalendar->setDate(date("d", $fprom1), date("m", $fprom1), date("Y", $fprom1));
																			//					$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
																			$myCalendar->disabledDay("sun");
																			$myCalendar->setYearInterval(2017, 2025);
																			$myCalendar->setAutoHide(true, 5000);

																			//output the calendar
																			$myCalendar->writeScript();
																			echo '					<input type="hidden" name="orden_id" value="' . $orden_id . '" /><input class="btn btn-success btn-sm2" type="submit" value="Guardar" /></form>'."\n";
																		} 
																	}?>

																</td>
															</tr>
														</table> <?php


													 
								 //FECHA DE VALUACION?>
								<tr class="claro"><td><b>Fecha de valuación:</b> </td><td>
										
									<table> <?php
										if($info_valuacion == '' || $info_valuacion == NULL){
											foreach ($reporte as $siniestro => $fecha) {
												if(validaAcceso('1040130', $dbpfx) == '1'){
													require_once("calendar/tc_calendar.php");
													$myCalendar = '';
													if ($fecha[fv] != 0 || $fecha[fv] != '') {
														$fecha_cal = strtotime($fecha[fv]);
													}
													else {
														$fecha_cal = strtotime(date("Y-m-d H:i:s"));
													}
													$myCalendar = new tc_calendar('feval_' . $fecha[sub], true);
													$myCalendar->setPath("calendar/");
													$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
													$myCalendar->setDate(date("d", $fecha_cal), date("m", $fecha_cal), date("Y", $fecha_cal));
													$myCalendar->disabledDay("sun");
													$myCalendar->setYearInterval(2017, 2025);
													$myCalendar->setAutoHide(true, 5000);
												}
												if ($siniestro != 0 || $siniestro != NULL) { ?>
													<tr>
														<td style="width: 63%;"><b><?php echo $siniestro; ?></b></td>
														<td><?php
															if(validaAcceso('1040130', $dbpfx) == '1'){?>
																<form action="ordenes.php?accion=fecha_valuacion" method="post" enctype="multipart/form-data">
																<?php $myCalendar->writeScript(); ?>
																<input type="hidden" name="siniestro[<?php echo $cont; ?>]" value="<?php echo $siniestro; ?>">
																<input type="hidden" name="orden_id" value="<?php echo $orden_id; ?>">
																<input type="hidden" name="sub_orden_id" value="<?php echo $fecha[sub]; ?>">
																<input class="btn btn-success btn-sm2" type="submit" value="Guardar" />
															</form><?php
															}?>
														</td>
													</tr>
													<tr>
														<td colspan="2"> Sin Fecha</td>
														
													</tr><?php
												}
												else {	?>								
													<tr>
														<td style="width: 63%;"><b>Particular</b></td>
														<td><?php
															if(validaAcceso('1040130', $dbpfx) == '1'){?>
																<form action="ordenes.php?accion=fecha_valuacion" method="post" enctype="multipart/form-data">
																<?php $myCalendar->writeScript(); ?>
																<input type="hidden" name="siniestro[<?php echo $cont; ?>]" value="<?php echo $siniestro; ?>">
																<input type="hidden" name="orden_id" value="<?php echo $orden_id; ?>">
																<input type="hidden" name="sub_orden_id" value="<?php echo $fecha[sub]; ?>">
																<input class="btn btn-success btn-sm2" type="submit" value="Guardar" />
															</form><?php
															}?>
														</td>
													</tr>
													<tr>
														<td colspan="2"> Sin Fecha</td>
														
													</tr><?php
												}
											}
										}
										
										else {
											foreach($reporte as $siniestro => $fecha){
												if(validaAcceso('1040130', $dbpfx) == '1'){
													require_once("calendar/tc_calendar.php");
													$myCalendar = '';
													if ($fecha[fv] != 0 || $fecha[fv] != '') {
														$fecha_cal = strtotime($fecha[fv]);
													}
													else {
														$fecha_cal = strtotime(date("Y-m-d H:i:s"));
													}
												$myCalendar = new tc_calendar('feval_' . $fecha[sub], true);
												$myCalendar->setPath("calendar/");
												$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
												$myCalendar->setDate(date("d", $fecha_cal), date("m", $fecha_cal), date("Y", $fecha_cal));
												$myCalendar->disabledDay("sun");
												$myCalendar->setYearInterval(2017, 2025);
												$myCalendar->setAutoHide(true, 5000);
												
												if($siniestro != 0 || $siniestro != NULL){
													if($fecha['fv'] != 0 || $fecha['fv'] != NULL){ ?>
														<tr>
															<td style="width: 63%;"><b><?php echo $siniestro; ?></b></td>
															<td><form action="ordenes.php?accion=fecha_valuacion" method="post" enctype="multipart/form-data">
																	<?php $myCalendar->writeScript(); ?>
																	<input type="hidden" name="siniestro[<?php echo $cont; ?>]" value="<?php echo $siniestro; ?>">
																	<input type="hidden" name="orden_id" value="<?php echo $orden_id; ?>">
																	<input type="hidden" name="sub_orden_id" value="<?php echo $fecha[sub]; ?>">
																	<input class="btn btn-success btn-sm2" type="submit" value="Guardar" />
																</form>
															</td>
														</tr>
														<tr>
															<td colspan="2"><?php echo $fecha['fv']; ?></td>
																
														</tr><?php
															
													} else { ?>
														<tr>
															<td style="width: 63%;"><b><?php echo $siniestro; ?></b></td>
															<td><?php
																if(validaAcceso('1040130', $dbpfx) == '1'){?>
																	<form action="ordenes.php?accion=fecha_valuacion" method="post" enctype="multipart/form-data">
																		<?php $myCalendar->writeScript();?>
																		<input type="hidden" name="siniestro[<?php echo $cont; ?>]" value="<?php echo $siniestro; ?>">
																		<input type="hidden" name="orden_id" value="<?php echo $orden_id; ?>">
																		<input type="hidden" name="sub_orden_id" value="<?php echo $fecha[sub]; ?>">
																		<input class="btn btn-success btn-sm2" type="submit" value="Guardar" />
																	</form><?php
																}?>
															</td>
														</tr>
														<tr>
															<td colspan="2"> Sin Fecha</td>
																
														</tr> <?php
													}
												} else {
													if($fecha['fv'] != 0 || $fecha['fv'] != NULL){ ?>
														<tr>
															<td style="width: 63%;"><b>Particular</b></td>
															<td><?php
																if(validaAcceso('1040130', $dbpfx) == '1'){?>
																	<form action="ordenes.php?accion=fecha_valuacion" method="post" enctype="multipart/form-data">
																	<?php $myCalendar->writeScript(); ?>
																	<input type="hidden" name="siniestro[<?php echo $cont; ?>]" value="<?php echo $siniestro; ?>">
																	<input type="hidden" name="orden_id" value="<?php echo $orden_id; ?>">
																	<input type="hidden" name="sub_orden_id" value="<?php echo $fecha[sub]; ?>">
																	<input class="btn btn-success btn-sm2" type="submit" value="Guardar" />
																</form><?php
																}?>
															</td>
														</tr>
														<tr>
															<td colspan="2"><?php echo $fecha['fv']; ?></td>
														</tr><?php
													} else { ?>
														<tr>
															<td style="width: 63%;"><b>Particular</b></td>
															<td><?php
																if(validaAcceso('1040130', $dbpfx) == '1'){?>
																	<form action="ordenes.php?accion=fecha_valuacion" method="post" enctype="multipart/form-data">
																	<?php $myCalendar->writeScript(); ?>
																	<input type="hidden" name="siniestro[<?php echo $cont; ?>]" value="<?php echo $siniestro; ?>">
																	<input type="hidden" name="orden_id" value="<?php echo $orden_id; ?>">
																	<input type="hidden" name="sub_orden_id" value="<?php echo $fecha[sub]; ?>">
																	<input class="btn btn-success btn-sm2" type="submit" value="Guardar" />
																</form><?php
																}?>
															</td>
														</tr>
														<tr>
															<td colspan="2"> Sin Fecha</td>
															
														</tr><?php
													}
												}
											} else { ?>
												<?php
												if($siniestro != 0 || $siniestro != NULL){ ?>
													<tr><td style="width: 63%;"><b><?php echo $siniestro; ?></b></td></tr><?php
												} else { ?>
													<td style="width: 63%;"><b>Particular</b></td><?php
												} 
												if($fecha['fv'] != 0 || $fecha['fv'] != NULL){ ?>
													<tr><td><?php echo $fecha['fv']; ?></td></tr>
													<?php
												}else{ ?>
													<tr>
														<td colspan="2"> Sin Fecha</td>
													</tr> <?php
															
												}
											}
												
										}
									}	?>
									</table><?php
								// ------ Prersentación de Fecha de termino de producción ------
									echo '			 	<tr class="obscuro"><td><b>' . $lang['TermProd'] . '</b></td><td>';?>
															<table>
																<tr>
																	<td>
																		<?php echo $orden['orden_fecha_proceso_fin'];?>
																	</td>
																	<td>
																		<?php
																		$ffpro1 = strtotime($orden['orden_fecha_proceso_fin']);
																		if(validaAcceso('1040115', $dbpfx) == '1' || $ffpro1 < 1000 && validaAcceso('1040120', $dbpfx) == '1') {
																			if($ffpro1 == '' || $ffpro1 == 0) {
																				$ffpro1 = time();
																			}
																			require_once("calendar/tc_calendar.php");
																			echo '					<form action="ordenes.php?accion=fpromesa&ffpro1=1" method="post" enctype="multipart/form-data">'."\n";
																			//instantiate class and set properties
																			$myCalendar = new tc_calendar("ffprod", true);
																			$myCalendar->setPath("calendar/");
																			$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
																			$myCalendar->setDate(date("d", $ffpro1), date("m", $ffpro1), date("Y", $ffpro1));
																			//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
																			$myCalendar->disabledDay("sun");
																			$myCalendar->setYearInterval(2017, 2025);
																			$myCalendar->setAutoHide(true, 5000);
																			//output the calendar
																			$myCalendar->writeScript();
																				echo '					<input type="hidden" name="orden_id" value="' . $orden_id . '" /><input class="btn btn-success btn-sm2" type="submit" value="Guardar" /></form>'."\n";
																		}
																		?>
																	</td>
																</tr>
															</table><?php															
									
									echo '</td></tr>'."\n";

								echo '			 	<tr class="claro"><td><b>Ultimo movimiento: </b></td><td>' . $orden['orden_fecha_ultimo_movimiento'] . '</td></tr>'."\n";
								if ($orden['orden_fecha_acordada']!='' && $orden['orden_estatus'] < 90) {
									$fae = strtotime($orden['orden_fecha_acordada']);
									if($orden['orden_categoria'] == '2') {$talarma = 86400;} else {$talarma = 3600;}
									$alerta_acordada = 'alarma_preventiva';
									$preventiva = $fae - $talarma;
									if ($fae <= time()) {
										$alerta_acordada = 'alarma_critica';
									} elseif (($fae - $talarma) > time()) {
										$alerta_acordada = 'alarma_normal';
									}
								} else {
									$alerta_acordada = '';
								}
								echo '		 		<tr class="obscuro"><td><b>Vehículo Lavado?:</b> </td>
														<td>';
														 	if (constant('ALARMA_' . $orden['orden_lavado']) != '') {
											 					echo constant('ALARMA_' . $orden['orden_lavado']);
											 				} else {
											 					echo 'No se ha definido';
											 				}
											 					echo '</td></tr>

							 		<tr class="claro"><td><b>Fecha Acordada de Entrega: </b></td>
							 			<td class="' . $alerta_acordada . '">';
							 				if ($orden['orden_fecha_acordada'] != '') {
							 					echo $orden['orden_fecha_acordada'];
							 				} else {
							 					echo 'No se ha definido';
							 				}
							 					echo '</td></tr>
							 		<tr class="obscuro"><td><b>Fecha Entregado: </b></td><td>';?>
							 				<table>
							 					<tr>
							 						<td>
							 							<?php echo $orden['orden_fecha_de_entrega']; ?>
							 						</td>
							 						<td>
							 							<?php 
							 							$retorno = 0; $retorno = validaAcceso('1040050', $dbpfx);
														if ($retorno == '1') {
															$fent1 = strtotime($orden['orden_fecha_de_entrega']);
															require_once("calendar/tc_calendar.php");
															echo '		 			<form action="ordenes.php?accion=fentrega" method="post" enctype="multipart/form-data">'."\n";
															//instantiate class and set properties
															$myCalendar = new tc_calendar("fent", true);
															$myCalendar->setPath("calendar/");
															$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
															$myCalendar->setDate(date("d", $fent1), date("m", $fent1), date("Y", $fent1));
															//				$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
															$myCalendar->disabledDay("sun");
															$myCalendar->setYearInterval(2017, 2025);
															$myCalendar->setAutoHide(true, 5000);

															//output the calendar
															$myCalendar->writeScript();
															echo '					<input type="hidden" name="orden_id" value="' . $orden_id . '" /><input class="btn btn-success btn-sm2" type="submit" value="Guardar" /></form>'."\n";
														}  ?>
							 						</td>
							 					</tr>
							 				</table> <?php
										 	 
											
							 	echo '</td></tr>'."\n";
								// ----------> Validar acceso a cálculo de Destajos		 	$funnum = 1135050;
							 	$retorno = 0; $retorno = validaAcceso('1135050', $dbpfx);
							 	if($_SESSION['codigo'] <= '12' || $retorno == '1' || $_SESSION['rol12']=='1' || $_SESSION['rol06']=='1') {
							 		echo '<tr class="claro"><td colspan="2" align="center"><a href="destajos.php?accion=cesta&orden_ver=' . $orden_id . '">Ver Pago de Destajos</a></td></tr>'."\n";
							 	}
							 	if($_SESSION['codigo'] <= '12' || $_SESSION['rol12']=='1' || $_SESSION['rol06']=='1') {
									$preg6 = "SELECT * FROM " . $dbpfx . "facturas_por_cobrar WHERE orden_id = '$orden_id' AND fact_cobrada < '2'";
									$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección! " . $preg6);
									//$preg6 = "SELECT f.fact_tipo, f.fact_num, f.fact_monto, f.fact_cobrada FROM " . $dbpfx . "facturas_por_cobrar f, " . $dbpfx . "facturas_pc_ordenes fpco WHERE fpco.orden_id = '$orden_id' AND f.fact_cobrada < '2' AND fpco.fact_id = f.fact_id ";
									//$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección! " . $preg6);
									echo '		 		<tr class="obscuro"><td colspan="2"><table cellspacing="0" cellpadding="2" border="0" class="centrado" width="100%"><tr><td><b>Documento</b></td><td><b>Número</b></td><td><b>Monto</b></td><td><b>Cobrado?</b></td></tr>'."\n";
									while($fact = mysql_fetch_array($matr6)) {
										echo '<tr class="claro"><td>';
										if($fact['fact_tipo']==1) { echo'Factura'; } else { echo 'Remisión';}
										echo '</td><td>';
										echo $fact['fact_num'];
										echo '</td><td>';
										echo money_format('%n', $fact['fact_monto']);
										echo '</td><td>';
										if($fact['fact_cobrada']==1) { echo 'Sí'; } else { echo 'No';}
										echo '</td></tr>'."\n";
									}

									echo '		 				</table>'."\n";
										echo '</td></tr></table>';

							 	}

								echo '		 	</table></td></tr>'."\n";


								?>  															
						</div>		

						</div>
			    	</div>
			
		
				<!-- Menu y mensajes --><?php
				mysql_close();
				mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
				mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
			
				$preg1 = "SELECT val_numerico FROM valores WHERE val_nombre = 'AccionesResponsivas'";
				$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de valores comunes! " . $preg1);
				$acc1 = mysql_fetch_assoc($matr1);
 				//echo $acc1['val_numerico'] . ' <<<<<<<<<<<';
 				$accres = $acc1['val_numerico'];
 				mysql_close();
				mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
				mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);

				if($accres == 0) {

				}else {
				?>
				<div class="menuacciones" align="center">
				<?php } ?>
				<div class="col-lg-1 col-md-1 col-sm-5" style="margin: 5px;">
					
						<div class="row" style="box-shadow: 3px 4px 10px 0px #484848; border-radius: 10px; background-color: white; margin-bottom: 10px;"><?php




				// ------  Acciones ------
							echo '<h3>Acciones</h3>';
							$orden_estatus = $orden['orden_estatus'];
							$pregunta4 = "SELECT * FROM " . $dbpfx . "acciones WHERE accion_estatus = '".$orden['orden_estatus']."'";
							$matriz4 = mysql_query($pregunta4) or die("ERROR: Fallo selección! " . $pregunta4);
							$num_acc = mysql_num_rows($matriz4);
							if ($num_acc>0) {
								while ($acciones = mysql_fetch_array($matriz4)) {
									$rol = $acciones['accion_codigo'];
									if (($acciones['accion_codigo']=='99') || ($_SESSION[$rol]==1)) {
										echo '<a href="' . $acciones['accion_url'];
										if($orden_estatus == 17  && $confolio == '1') {
											echo $orden['oid'];
										} else {
											echo $orden['orden_id'];
										}
										echo '"><img class="acciones" style="margin-left: 10px;margin-right: 10px;" src="idiomas/' . $idioma . '/imagenes/' . $acciones['accion_descripcion'] . '</a>'."\n";
									}
								}
							}
								if ($orden_estatus>=1 && $orden_estatus<=89 && $orden_estatus != 17 && ($_SESSION['rol05']==1 || $_SESSION['rol06']==1)) {
									echo '<a href="presupuestos.php?accion=adicional&orden_id=' . $orden_id . '"><img class="acciones" style="margin-left: 10px;margin-right: 10px;" src="idiomas/' . $idioma . '/imagenes/agregar-tarea.png" alt="Agregar otra tarea" title="Agregar otra tarea"></a>'."\n";
								}
								if(($orden_estatus == 17  && $confolio != '1') || $orden_estatus != 17) {
									echo '<a href="documentos.php?accion=listar&orden_id=' . $orden_id . '"><img class="acciones" style="margin-left: 10px;margin-right: 10px;" src="idiomas/' . $idioma . '/imagenes/documento.png" alt="Mostrar documentos" title="Mostrar documentos"></a>'."\n";
									echo '<a href="comentarios.php?accion=agregar&orden_id=' . $orden_id . '"><img class="acciones" style="margin-left: 10px;margin-right: 10px;" src="idiomas/' . $idioma . '/imagenes/agregar-comentarios.png" alt="Agregar Comentarios" title="Agregar Comentarios"></a>'."\n";
								}

								// ------ Agregar las acciones fijas disponibles en todos los estatus de la OT -----  
								if(file_exists('particular/textos/acciones.php')) {
									include('particular/textos/acciones.php');
								}
								// ------



								?>
						</div>
					</div><?php
				if ($accres == 0) {
					
				} else {
				?>
				</div>
				<?php }


if($accres == 0) { } else {
echo '<div id="mySidenav2" class="sidenav2">'."\n";
echo '<a href="#" class="closebtn2" onclick="closeNav2();">&times;</a>'."\n";
echo '<center><a>Menú de Acciones</a>';
		$orden_estatus40 = $orden['orden_estatus'];
							$pregunta40 = "SELECT * FROM " . $dbpfx . "acciones WHERE accion_estatus = '".$orden['orden_estatus']."'";
							$matriz40 = mysql_query($pregunta40) or die("ERROR: Fallo selección! " . $pregunta40);
							$num_acc40 = mysql_num_rows($matriz40);
							if ($num_acc40>0) {
								while ($acciones40 = mysql_fetch_array($matriz40)) {
									$rol40 = $acciones40['accion_codigo'];
									if (($acciones40['accion_codigo']=='99') || ($_SESSION[$rol]==1)) {
										echo '				<a href="' . $acciones40['accion_url'];
										if($orden_estatus40 == 17  && $confolio40 == '1') {
											echo $orden['oid'];
										} else {
											echo $orden['orden_id'];
										}
										echo '"><img class="acciones" style="margin-left: 10px;margin-right: 10px;" src="idiomas/' . $idioma . '/imagenes/' . $acciones40['accion_descripcion'] . '</a>'."\n";
									}
								}
							}
								if ($orden_estatus40>=1 && $orden_estatus40<=89 && $orden_estatus40 != 17 && ($_SESSION['rol05']==1 || $_SESSION['rol06']==1)) {
									echo '				<a href="presupuestos.php?accion=adicional&orden_id=' . $orden_id . '"><img class="acciones" style="margin-left: 10px;margin-right: 10px;" src="idiomas/' . $idioma . '/imagenes/agregar-tarea.png" alt="Agregar otra tarea" title="Agregar otra tarea"></a>'."\n";
								}
								if(($orden_estatus40 == 17  && $confolio40 != '1') || $orden_estatus40 != 17) {
									echo '				<a href="documentos.php?accion=listar&orden_id=' . $orden_id . '"><img class="acciones" style="margin-left: 10px;margin-right: 10px;" src="idiomas/' . $idioma . '/imagenes/documento.png" alt="Mostrar documentos" title="Mostrar documentos"></a>'."\n";
									echo '				<a href="comentarios.php?accion=agregar&orden_id=' . $orden_id . '"><img class="acciones" style="margin-left: 10px;margin-right: 10px;" src="idiomas/' . $idioma . '/imagenes/agregar-comentarios.png" alt="Agregar Comentarios" title="Agregar Comentarios"></a>'."\n";
								}

								// ------ Agregar las acciones fijas disponibles en todos los estatus de la OT -----  
								if(file_exists('particular/textos/acciones.php')) {
									include('particular/textos/acciones.php');
								}		
echo '</center></div>';

echo '<div class="hamburguesaacciones" onclick="openNav2();" style="font-size: 15px"><span><img src="imagenes/acciones.png" width="24px"><br>Acciones</span></div>'."\n";
echo '<script type="text/javascript">'."\n";

echo '	function openNav2() { '."\n";
echo '  document.getElementById("mySidenav2").style.width = "250px";'."\n";
echo '	}';

echo ' function closeNav2() {'."\n";
echo ' document.getElementById("mySidenav2").style.width = "0";'."\n";
echo '	}'."\n";
echo '</script>'."\n";
}
?>
				<div class="col-lg-3 col-md-2 col-sm-11" style="margin: 5px;">
					<div class="row" align="center" >
						<div class="row"><?php


							if($mensjint == '1') {
							echo '				<form action="comentarios.php?accion=visto" method="post" enctype="multipart/form-data">'."\n";

							// --- sección de boletines ---
							include("parciales/boletines_pendientes.php");

							//echo '</div></div>';

							// ----------------------- sección de comentarios -------------------------
							echo '<div class="row" align="center" style="box-shadow: 3px 4px 10px 0px #484848; margin-bottom: 10px;border-radius: 10px;">
									<div class="row">
										<table width="100%">
											<tr>
												<td><b><big>Tus mensajes no leidos:</big></b><br>'."\n";

								$pregc1 = "SELECT c.orden_id, c.bit_id, c.fecha_com, c.usuario, c.comentario, c.interno, c.recordatorio, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.usuario = u.usuario AND (c.interno = '1' OR c.interno = '3') AND c.para_usuario = '" . $_SESSION['usuario'] . "' AND fecha_visto IS NULL ORDER BY c.bit_id DESC";
								$matrc1 = mysql_query($pregc1) or die("ERROR: Fallo selección de comentarios! " . $pregc1);
								$j=0; $fondo='claro';
								while($comen = mysql_fetch_array($matrc1)) {
									echo '				<p class="' . $fondo . '" style="margin-top:0px; padding-left:3px; padding-right:3px;">'."\n";
									if($comen['orden_id'] == '9999995' ) {
										$provid = explode('|', $comen['comentario']);
										echo '									' . $provid[1] . '<br>'."\n";
										echo '									<input type="hidden" name="prov_id" value="' . $provid[0] . '" />
												<input type="hidden" name="visto" value="' . $comen['bit_id'] . '" />'."\n";
										echo '									<button name="activar" value="1" type="submit">' . $lang['CotAcept'] . '</button>'."\n";
										echo '									<button name="activar" value="0" type="submit">' . $lang['CotPosp'] . '</button>'."\n";
										echo '									<button name="activar" value="2" type="submit">' . $lang['CotBloq'] . '</button>'."\n";
										echo '									<a href="proveedores.php?accion=consultar&prov_id=' . $provid[0] . '" target="_blank"><button type="button">' . $lang['ProvVer'] . '</button></a>'."\n";
									} else {
										echo '				<a href="ordenes.php?accion=consultar&orden_id=' . $comen['orden_id'] . '" target="_blank">Mensaje en la OT ' . $comen['orden_id'] . '</a><br>'."\n";
										echo '				El ' . $comen['fecha_com'] . ' de ' . $comen['nombre'] . ' ' . $comen['apellidos'] . '<br>'."\n";
										echo '				' . $comen['comentario']. '<br>'."\n";
										if($comen['recordatorio'] != 1) {
											echo '				<button class="btn btn-success" name="visto" value="' . $comen['bit_id'] . '" type="submit">' . $lang['Visto'] . '</button>'."\n";
										}
										echo '				<input type="hidden" name="orden_id" value="' . $comen['orden_id'] . '" />
							<input type="hidden" name="pagina" value="ordenes.php" />'."\n";
									}
									echo '				</p>'."\n";				
									if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
								}
								echo '				</td></tr></table></form></div></div>'."\n";
							} ?>

						
			    </div>

			   
			  </div>
			</div>
				<div class="col-lg-11 col-md-11 col-sm-11" style="margin: 5px;">
					<div style="box-shadow: 3px 4px 10px 0px #484848; border-radius: 10px; margin: 5px;"><?php
// ----------------   Comentarios y Bitácora --------------------------------------------			

				$per_generales = validaAcceso(1040100, $dbpfx);
				$per_seguimiento = validaAcceso(1040105, $dbpfx);
				$per_bitacora = validaAcceso(1040110, $dbpfx);
				if(($confolio == '1' && $orden['orden_estatus'] != '17') || ($confolio != '1')) {
					echo '	<table>		<tr>
					<td colspan="3" style="vertical-align:top;">
				<div id="navegador"><a name="com"></a><br>
					<ul>'."\n";
					if($per_generales == 1 || ($_SESSION['codigo'] != '60' && $_SESSION['codigo'] != '75' && $_SESSION['codigo'] != '70')) {
						echo '
						<li'."\n";
						if($com == '1' || $com == ''){ echo ' class="activa"'; }
							echo '><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '&com=1#com">Comentarios Generales</a></li>'."\n";
					}
					if($per_seguimiento == 1 || $_SESSION['rol02']=='1' ||  $_SESSION['rol06']=='1') {
						echo '
						<li'."\n";	
						if($com == '2') { echo ' class="activa"'; }
							echo '><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '&com=2#com">Seguimiento</a></li>'."\n";
					}
					if($per_bitacora || $_SESSION['rol02']=='1') {
						echo '
						<li'."\n";
						if($com == '3') { echo ' class="activa"'; }
							echo '><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '&com=3#com">Bitácora</a></li>'."\n";
					}
					echo '
					</ul>
				</div></td></tr>'."\n";
					echo '			<tr><td colspan="3">';
					if($com == '') {
						$com = '1';
					}	
					if($com == '1' && ($per_generales == 1 || ($_SESSION['codigo'] != '70' && $_SESSION['codigo'] != '75'))) {
						$pregunta3 = "SELECT c.fecha_com, c.fecha_visto, c.usuario, c.comentario, c.interno, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.orden_id = '$orden_id' AND c.usuario = u.usuario AND (c.interno = '0' OR c.interno = '3') ";
						if($_SESSION['codigo'] == '60' || $_SESSION['codigo'] == '2000') {
							$pregunta3 .= " AND (c.usuario = '" . $_SESSION['usuario'] . "' OR c.para_usuario = '" . $_SESSION['usuario'] . "') ";
						}
						$pregunta3 .= " ORDER BY c.bit_id DESC";
						$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion DE GENERALES!" . $pregunta3);
						$j=0; $fondo='claro';
						while($comen = mysql_fetch_array($matriz3)) {
							echo '<p class="' . $fondo . '" style="margin-top:0px; padding-left:3px; padding-right:3px;';
							if($comen['interno'] == '1') { echo ' color:#0000ff;'; }
							elseif($comen['interno'] == '3' && is_null($comen['fecha_visto'])) { echo ' background-color: #FFFF93;'; }
							echo '">' . $comen['fecha_com'] . ' ' . $comen['nombre'] . ' ' . $comen['apellidos'] . ' -> ' . $comen['comentario'] . '</p>'."\n";
							$j++;
							if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
						}
					}
					if($com == '2' && ($per_seguimiento == 1 || $_SESSION['rol02']=='1' ||  $_SESSION['rol06']=='1')) {
						$pregunta3 = "SELECT c.fecha_com, c.fecha_visto, c.usuario, c.comentario, c.interno, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.orden_id = '$orden_id' AND c.usuario = u.usuario AND (c.interno = '2' OR c.interno = '1') ORDER BY c.bit_id DESC";
						$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion DE GENERALES!" . $pregunta3);
						$j=0; $fondo='claro';
						while($comen = mysql_fetch_array($matriz3)) {
							echo '<p class="' . $fondo . '" style="margin-top:0px; padding-left:3px; padding-right:3px;';
							if($comen['interno'] == '1') { echo ' color:#0000ff;'; }
							elseif($comen['interno'] == '3' && is_null($comen['fecha_visto'])) { echo ' background-color: #FFFF93;'; }
							echo '">' . $comen['fecha_com'] . ' ' . $comen['nombre'] . ' ' . $comen['apellidos'] . ' -> ' . $comen['comentario'] . '</p>'."\n";
							$j++;
							if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
						}
					}
					if($com == '3' && ($per_bitacora || $_SESSION['rol02']=='1')) {
						$pregunta3 = "SELECT b.bit_fecha, b.bit_estatus, u.nombre, u.apellidos FROM " . $dbpfx . "bitacora b, " . $dbpfx . "usuarios u WHERE b.orden_id = '$orden_id' AND b.usuario = u.usuario";
						$pregunta3 .= " ORDER BY b.bit_id DESC";
						$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
						$j=0; $fondo='claro';
						while($bit = mysql_fetch_array($matriz3)){
							echo '<p class="' . $fondo . '" style="margin-top:0px; padding-left:3px; padding-right:3px;">' . $bit['bit_fecha'] . ' ' . $bit['nombre'] . ' ' . $bit['apellidos'] . ' -> ' . $bit['bit_estatus'] . '</p>';
							$j++;
							if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
						}
					} 
					echo '</td></tr></table><p>'."\n";
				}
//------------------------------------------------------------------------------------------

?>
			    	</div>
			    </div>
			</div>



			<?php 
		}

		} else {
			$mensaje .='No se encontraron registros con esos datos.</br>';
		}
	} else {
		$mensaje .='Se requiere el número de orden de trabajo a consultar.</br>';
	}
		echo '<p>' . $mensaje . '</p>'."\n";
}
elseif ($accion === "fecha_valuacion"){
	
	$funnum = 1040130;
	$acceso = 0; $retorno = validaAcceso($funnum, $dbpfx);
	$retorno = 1;
	
	if($retorno == '1') {
		$preg0 = "SELECT sub_orden_id, orden_id, sub_siniestro, sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub_orden_id . "'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de suborden! " . $preg0);
		$cont = 0;
		//echo $preg0;
		//while ($siniestro = mysql_fetch_array($matr0)) {
		$siniestro = mysql_fetch_array($matr0);
			//echo $fecha;
			//$fecha = 'feval_' . $cont;
			//echo $fecha;
			//echo '<hr>';
			//echo $_POST['feval_'. $cont]  . ' orden -- ' . $siniestro['sub_orden_id'] . '<br>';
			//echo '<pre>';
			//print_r($siniestro);
			//echo '</pre>';
		
			$fechaval = $_POST['feval_'. $sub_orden_id];
			//echo $fechaval;
			$fecha_valu = date('Y-m-d H:i:s', strtotime($fechaval));
			if($siniestro['2'] === '0' | $siniestro['2'] === ''){
			$parametros=" orden_id = '" . $orden_id . "' AND sub_orden_id = '" . $siniestro['0'] . "'";
			//echo $parametros;
			}
			else{
			$parametros=" orden_id = '" . $orden_id . "' AND sub_reporte = '" . $siniestro['4'] . "'";
			//echo $parametros;
			}
			//echo $parametros;
			$sql_data_array = array('sub_fecha_valaut' => $fecha_valu);
			//print_r($sql_data_array);
			
			if($fecha_valu === ''){	
			
			
			}
			else{
			//echo '<pre>';
			//print_r($sql_data_array);
			//echo '</pre>';
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
			bitacora($orden_id, 'Cambio de fecha a: ' . $fecha_valu . ' de Valuación del siniestro ' . $siniestro['4'], $dbpfx, 'Cambio de fecha de Valuación', '0');
		//	}

			

		}
$cont++;
/*		$cont = 0;
		
		foreach($siniestro as $key){
			
			
			//echo 'key ' . $key . '<br>';
			$fecha = 'feval_' . $cont;
			
			//echo 'Siniestro: ' . $key . '<br> fecha: ' . $$fecha . '<br>';
			$fecha_valu = date('Y-m-d H:i:s', strtotime($$fecha));
			$parametros=" orden_id = '" . $orden_id . "' AND sub_reporte = '" . $siniestro['1'] . "'";
			$sql_data_array = array('sub_fecha_valaut' => $fecha_valu);
			
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
			bitacora($orden_id, 'Cambio de fecha de Valuación del siniestro ' . $key, $dbpfx, 'Cambio de fecha de Valuación', '0');
			
			$cont++;
			echo 'contador = ' . $cont;
		}*/
		
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función no fue autorizado';
	}
	$msjpass = 'Se actualizó la fecha de Valuación a : ' . $fecha_valu;
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id . '&msjpass=' . $msjpass);
	
}
elseif ($accion==="listar") {

	$funnum = 1040015;

//	echo 'Estamos en la sección listar. Cliente: ' . $cliente_id . ' Vehiculo: ' . $vehiculo_id;
	$placas=preparar_entrada_bd($placas);
	$eco=preparar_entrada_bd($eco);
	$torre=preparar_entrada_bd($torre);
	$tipo=preparar_entrada_bd($tipo);
	$cliente_id = preparar_entrada_bd($cliente_id);
	$previa_id = preparar_entrada_bd($previa_id);
	$error = 'si'; $num_cols = 0;
	$mensaje= 'Se necesita al menos un dato para buscar.<br>';
	
	echo '			<table cellspacing="2" cellpadding="2" border="0" class="izquierda">';
	echo '				<tr><td>'."\n";
	echo '					<table cellspacing="2" cellpadding="2" border="0" class="centrado">
						<tr class="cabeza_tabla"><td colspan="3" align="left">Ordenes de Trabajo</td></tr>'."\n";
	if ($siniestro!='') {
		$error = 'no'; $mensaje ='';
		$pregunta = "SELECT s.sub_reporte, o.oid, o.orden_id, o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas, o.orden_estatus, v.vehiculo_flota, v.vehiculo_eco, v.vehiculo_placas FROM " . $dbpfx . "ordenes o, " . $dbpfx . "subordenes s, " . $dbpfx . "vehiculos v WHERE s.sub_reporte LIKE '%$siniestro%' AND o.orden_id = s.orden_id AND o.orden_vehiculo_id = v.vehiculo_id GROUP BY o.orden_id ORDER BY o.orden_id";
	} elseif ($placas != '' || $eco != '' || $torre!='' || $tipo!='' || $cliente_id != '') {
		$error = 'no'; $mensaje ='';
		$pregunta = "SELECT o.orden_id, o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas, o.orden_estatus, v.vehiculo_flota, v.vehiculo_eco, v.vehiculo_placas FROM " . $dbpfx . "ordenes o LEFT JOIN " . $dbpfx . "vehiculos v ON o.orden_vehiculo_id = v.vehiculo_id WHERE ";
		if($placas != '') { $pregunta .= "(v.vehiculo_placas LIKE '%$placas%' OR v.vehiculo_eco LIKE '%$placas%' OR o.orden_vehiculo_tipo LIKE '%$placas%') "; }
		if($placas != '' && $eco != '') { $pregunta .= " OR (v.vehiculo_placas LIKE '%$eco%' OR v.vehiculo_eco LIKE '%$eco%' OR o.orden_vehiculo_tipo LIKE '%$eco%') "; }
		elseif($eco != '') { $pregunta .= " (v.vehiculo_placas LIKE '%$eco%' OR v.vehiculo_eco LIKE '%$eco%') "; }
		elseif($torre != '') { $pregunta .= "o.orden_torre = '$torre' AND o.orden_estatus < '90' ";}
		if ($torre != '' && $tipo != '') { $pregunta .= "AND o.orden_vehiculo_tipo LIKE '%$tipo%' "; }
		elseif ($tipo != '') { $pregunta .= "o.orden_vehiculo_tipo LIKE '%$tipo%' "; }
		if ($cliente_id) { $pregunta .= "o.orden_cliente_id = '$cliente_id' "; }
		$pregunta .= " GROUP BY o.orden_id ORDER BY o.orden_id";
	} else {
		$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '0'";
	}
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección! ". $pregunta);
	$num_cols = mysql_num_rows($matriz);
//		echo $pregunta;
		if ($num_cols > 0) {
			$j=0;
			while ($orden = mysql_fetch_array($matriz)) {
				$preg0 = "SELECT sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden['orden_id'] . "' GROUP BY sub_reporte ORDER BY sub_aseguradora";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de suborden! " . $preg0);
				echo '						<tr class="claro">';
				echo '							<td>Orden ID:</td><td>'."\n";
				echo '								<table width="100%"><tr><td>';
				echo '									<a href="ordenes.php?accion=consultar&';
				if($orden['orden_estatus'] == '17' && $confolio == 1) {
					echo 'oid=' . $orden['oid'] . '">' . $orden['oid'];
				} else {
					echo 'orden_id=' . $orden['orden_id'] . '">' . $orden['orden_id'];
				}
				echo '</a>'."\n";
				echo '									</td><td style="text-align:left;">';
				while ($sub = mysql_fetch_array($matr0)) {
					echo '									<img src="' . $ase[$sub['sub_aseguradora']]['logo'] . '" alt="" height="16" >&nbsp;'."\n";
					if($sub['sub_reporte'] == '' || $sub['sub_reporte'] == '0') { 
						echo 'Particular';
					} else {
						echo $sub['sub_reporte'];
					}
					echo '<br>'."\n";
				}
				echo '									</td></tr></table>';
				echo '							</td><td rowspan="5">
								<a href="ordenes.php?accion=consultar&';
				if($orden['orden_estatus'] == '17' && $confolio == 1) {
					echo 'oid=' . $orden['oid'];
				} else {
					echo 'orden_id=' . $orden['orden_id'];
				}
				echo '"><img src="idiomas/' . $idioma . '/imagenes/ordenes-detalle.png" alt="Detalles Ordenes de Trabajo" title="Detalles Ordenes de Trabajo"></a>&nbsp;'."\n";

// --------- Valida permiso para mostrar botón de Cobro de Anticipos ---------------------

				$retorno0 = 0; $retorno0 = validaAcceso('1040070', $dbpfx);
//				if($retorno0 == 1 || $_SESSION['rol06'] == '1' || $_SESSION['rol12'] == '1') {
				if($retorno0 == 1) {
					echo '								<a href="entrega.php?accion=regcobro&orden_id=' . $orden['orden_id'] . '&tipo=2&anticipo=1"><img src="idiomas/' . $idioma . '/imagenes/cobro-anticipo.png" alt="'.$lang['Registrar cobro de Anticipo'].'" title="'.$lang['Registrar cobro de Anticipo'].'"></a>&nbsp;'."\n";
				}
				echo '							</td></tr>'."\n";
				echo '							<tr class="obscuro"><td>Vehículo</td><td>' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . '</td></tr>
							<tr class="claro"><td>' . $lang['Placa'] . '</td><td>';
				if($orden['vehiculo_flota'] > 0) {
					echo $orden['vehiculo_eco'];
				} else {
					echo $orden['orden_vehiculo_placas'];
				}
				echo '</td></tr>'."\n";
				$fondo = 'obscuro';
				if($orden['vehiculo_flota'] > 0) {
					echo '							<tr class="obscuro"><td>' . $lang['NumEco'] . '</td><td>' . $orden['orden_vehiculo_placas'] . '</td></tr>'."\n";
					$fondo = 'claro';
				}
				echo '							<tr class="' . $fondo . '"><td>Estatus</td><td>' . constant('ORDEN_ESTATUS_' . $orden['orden_estatus']) . '</td></tr>'."\n";
				$j++;
				echo '							<tr class="obscuro"><td colspan="3" style="height:5px;"></td></tr>'."\n";
				if($j==2) {$j=0;}
			}
			
		} else {
			echo '						<tr><td colspan="5">No se encontraron Ordenes de Trabajo con esos datos.</td></tr>'."\n";
		}
		echo '					</table>'."\n";
		echo '				</td><td>'."\n";
		echo '					<table cellspacing="2" cellpadding="2" border="0" class="centrado">
						<tr class="cabeza_tabla"><td colspan="3" align="left">Presupuestos Previos</td></tr>'."\n";

	$pregunta = "SELECT p.* FROM " . $dbpfx . "previas p, " . $dbpfx . "vehiculos v WHERE ";
	if ($placas != '' || $eco != '') {
		if($placas != '') { $pregunta .= "(v.vehiculo_placas LIKE '%$placas%' OR v.vehiculo_eco LIKE '%$placas%') "; }
		if($placas != '' && $eco != '') { $pregunta .= " OR (v.vehiculo_placas LIKE '%$eco%' OR v.vehiculo_eco LIKE '%$eco%') "; }
		elseif($eco != '') { $pregunta .= " (v.vehiculo_placas LIKE '%$eco%' OR v.vehiculo_eco LIKE '%$eco%') "; }
	} elseif($previa_id!='') {
		$pregunta .= "p.previa_id = '$previa_id' ";
	} else {
		$pregunta .= "p.previa_cliente_id = '$cliente_id' ";
	}
	$pregunta .= "AND p.previa_vehiculo_id = v.vehiculo_id";
//		echo $pregunta;
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección! ".$pregunta);
	$fprev = mysql_num_rows($matriz);

//	echo $pregunta;
	if ($fprev > 0) {
		$fondo = 'obscuro';
		while ($prev = mysql_fetch_array($matriz)) {
			echo '							<tr class="claro">';
			echo '								<td>Presupuesto</td><td><a href="previas.php?accion=consultar&previa_id=' . $prev['previa_id'] . '">' . $prev['previa_id'] . '</a></td>'."\n";
			echo '								<td rowspan="4"><a href="previas.php?accion=consultar&previa_id=' . $prev['previa_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/previos-detalle.png" alt="Detalles Presupuesto Previo" title="Detalles Presupuesto Previo"></a></td></tr>'."\n";
			echo '								<tr class="obscuro"><td>Estatus</td><td>' . constant('PREVIA_ESTATUS_' . $prev['previa_estatus']) . '</td></tr>
								<tr class="claro"><td>Vehículo</td><td>' . $prev['vehiculo_marca'] . ' ' . $prev['vehiculo_tipo'] . ' ' . $prev['vehiculo_color'] . ' ' . $prev['vehiculo_modelo'] . '</td></tr>
								<tr class="obscuro"><td>' . $lang['Placa'] . '</td><td>' . $prev['vehiculo_placas'] . '</td></tr>'."\n";
			echo '								<tr class="obscuro"><td colspan ="3" style="height:5px;"></td></tr>'."\n";
		}
	} else {
		echo '						<tr><td colspan="5">No se encontraron presupuestos previos con esos datos.</td></tr>'."\n";
	}
	echo '						</table>'."\n";
	echo '					</td></tr></table>';
}

elseif ($accion==='perdida') {

	if(validaAcceso('1040020', $dbpfx) == 1) {
		// Acceso autorizado
	} elseif($solovalacc != 1 && ($_SESSION['rol05']=='1')) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función es sólo para Valuadores';
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}

	if($reporte == '' || !isset($reporte) || $reporte == 'Seleccione') {
		if(isset($sub_orden_id) && $sub_orden_id != '') {
			$pregunta = "SELECT sub_reporte, orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id' AND sub_reporte != '' AND sub_reporte != '0' AND sub_reporte != '' AND sub_estatus < '190' GROUP BY sub_reporte";
		} else {
			$pregunta = "SELECT sub_reporte, orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte != '' AND sub_reporte != '0' AND sub_estatus < '190' GROUP BY sub_reporte";
		}
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección agrupada! " . $pregunta);
		$filas = mysql_num_rows($matriz);
		echo '		<form action="ordenes.php?accion=perdida" method="post" enctype="multipart/form-data">'."\n";
		echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
		if ($filas > 1) {
			echo '			<tr><td style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">Existe más de un siniestro, elija el adecuado (No aplica para trabajos particulares).</td></tr>' . "\n";
	 		echo '			<tr><td><select name="reporte" size="1">' . "\n";
			echo '				<option value="Seleccione" >Seleccione...</option>';
			while($rep = mysql_fetch_array($matriz)) {
				echo '				<option value="' . $rep['sub_reporte'] . '">' . $rep['sub_reporte'] . '</option>' . "\n";
				$orden_id = $rep['orden_id'];
			}
			echo '			</select></td></tr>' . "\n";
		} elseif ($filas == 1) {
			$rep = mysql_fetch_array($matriz);
			$orden_id = $rep['orden_id'];
			echo '			<tr><td>El cambio de estatus se aplicará al siniestro ' . $rep['sub_reporte'] . '.<input type="hidden" name="reporte" value="' . $rep['sub_reporte'] . '" /></td></tr>'."\n";
		} else {
			echo '			<tr><td>Trabajo particular o no se localizó el siniestro deseado.</td></tr>'."\n";
		}
		echo '			<input type="hidden" name="orden_id" value="' . $orden_id . '" />'."\n";
		echo '			<tr><td colspan="2" style="text-align:left;"><input type="submit" name="confirmar" value="Enviar" />&nbsp;<input type="submit" name="regresar" value="Regresar" /></td></tr>
		</table></form>'."\n";
		} else {
			$preg0 = "SELECT orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de OT! " . $preg0);
			$orden = mysql_fetch_array($matr0);
			$veh = datosVehiculo($orden_id, $dbpfx);
			echo '		 <form action="ordenes.php?accion=pago" method="post" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" border="0" class="agrega">
				<tr class="cabeza_tabla"><td colspan="2">Pérdida total o Pago de daños para la OT: ' . $orden_id . ' y siniestro: ' . $reporte . '</td></tr>
				<tr><td colspan="2" style="text-align:left;">' . $veh['completo'] . '</td></tr>
				<tr><td colspan="2"><hr></td></tr>
				<tr>
					<td>Seleccionar el Nuevo Estátus: </td>
					<td>
						' . ORDEN_ESTATUS_30 . '<input type="radio" name="tipopago" value="130" /><br>
						' . ORDEN_ESTATUS_31 . '<input type="radio" name="tipopago" value="131" /><br>
						' . ORDEN_ESTATUS_32 . '<input type="radio" name="tipopago" value="132" /><br>
						' . ORDEN_ESTATUS_33 . '<input type="radio" name="tipopago" value="133" /><br>'."\n";
		if(defined('ORDEN_ESTATUS_34') && constant('ORDEN_ESTATUS_34') != '') { echo '						' . ORDEN_ESTATUS_34 . '<input type="radio" name="tipopago" value="134" /><br>'; }
		if(defined('ORDEN_ESTATUS_35') && constant('ORDEN_ESTATUS_35') != '') { echo '						' . ORDEN_ESTATUS_35 . '<input type="radio" name="tipopago" value="135" /><br>'; }
		if($orden['orden_estatus'] >= '30' && $orden['orden_estatus'] <= '35' ) {
			echo '						<strong>Regresar OT a Valuación</strong><input type="radio" name="tipopago" value="102" />'."\n";
		}
		echo '						' . $lang['Cambiar a Particular'] . '<input type="radio" name="tipopago" value="90" /><br>'."\n";
		echo '					</td></tr>
				<tr><td colspan="2"><hr></td></tr>
				<tr><td style="text-align:center;" valign="top">Indicar si el comentario es para uso:<br>Interno <input type="radio" name="visicom" value="0" checked="checked" />';
		foreach($usuauthcom as $k) {
			if($_SESSION['usuario'] == $k) {
				echo '&nbsp;Email al Cliente <input type="radio" name="visicom" value="1" />';
			}
		}

		echo '</td><td><textarea name="motivo" cols="40" rows="6"></textarea></td></tr>
				<tr><td colspan="2" style="text-align:left;"><input type="submit" name="confirmar" value="Enviar" />&nbsp;<input type="submit" name="regresar" value="Regresar" /></td></tr>
			</table>
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<input type="hidden" name="sub_reporte" value="' . $reporte . '" />
		</form>'; 
		echo '<p>Esta función sólo aplica para Tareas de Aseguradoras</p>'."\n";
		echo '<div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div>';
	}
}

elseif ($accion==='pago') {
	
	if(validaAcceso('1040020', $dbpfx) == 1) {
		// Acceso autorizado
	} elseif($solovalacc != 1 && ($_SESSION['rol05']=='1')) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función es sólo para Valuadores';
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
	unset($_SESSION['orden']);
	$_SESSION['orden'] = array();
	$error = 'no';
	$mensaje= '';
	$pago = limpiarNumero($pago); $_SESSION['orden']['pago'] = $pago;
	$_SESSION['orden']['tipopago'] = $tipopago;
	if ((!$tipopago) || ($tipopago == '')) {$error = 'si'; $mensaje .= 'Seleccione el Cambio de Estatus. <br>';}
//	if ((!$pago) || ($pago == '') && $tipopago == '130') {$error = 'si'; $mensaje .= 'Agregar el importe del Pago de Daños. <br>';}

// ------ Validación si hay refacciones recibidas antes de permitir cambiar estatus ------
	$preg0 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE sub_reporte = '$sub_reporte' AND orden_id = '$orden_id' AND sub_estatus < 190";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de tareas!".$preg0);
	$fila0 = mysql_num_rows($matr0);
	$refrec = 0;
	unset($pedido_id);
	if($fila0 > 0) {
		while($sub = mysql_fetch_array($matr0)) {
			$preg1 = "SELECT op_ok, op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_pedido >'0' AND op_tangible < 3 ";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!".$preg1);
			$fila1 = mysql_num_rows($matr0);
			if($fila1 > 0) {
				while($op = mysql_fetch_array($matr1)) {
					$pedido_id[$op['op_pedido']] = 1;
					if($op['op_ok'] == '1') { $refrec++; }
				}
			}
// ------ Cancelar pedidos no recibidos
			unset($sql_data_array);
			$parametros="sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_tangible < '3' AND op_pedido < 1";
			$sql_data_array['op_pedido'] = '';
			$sql_data_array['op_item_seg'] = 'null';
			$sql_data_array['op_fecha_promesa'] = 'null';
			$sql_data_array['op_recibidos'] = '0';
			$sql_data_array['op_ok'] = '0';
			$sql_data_array['op_autosurtido'] = '0';
			$sql_data_array['op_costo'] = '0';
			ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $parametros);
		}
	} else {
		$error = 'si'; $mensaje .= $lang['No se encontro'] . '<br>';
	}
	
	if($tipopago == 132 && $conservpedsperd == 1) {
// ------ No eliminar pedidos ya que no es posible devolverlos todos y se desea conservar montos de costos y precios 
	} elseif($refrec > 0 && $tipopago > 101 ) {
		$error = 'si'; $mensaje .= $lang['Devolver refacciones'] . '<br>';
	}

	
	if ($confirmar=="Enviar" && $error == 'no') {
		$parametros="sub_reporte = '$sub_reporte' AND orden_id = '$orden_id'";
		if($tipopago > 101) {
			$sql_data_array = array('sub_estatus' => $tipopago,
				'sub_refacciones_recibidas' => '0',
				'sub_deducible' => 'null');
		} else {
			$sql_data_array = array('sub_siniestro' => '0',
				'sub_reporte' => '0',
				'sub_aseguradora' => '0',
				'sub_deducible' => 'null',
				'sub_estatus' => '101');
		}
		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);

		foreach($pedido_id as $k => $v) {
/*			
			$preg3 = "SELECT op_id FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $k . "' AND op_tangible < '3' ";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de items! " . $preg3);
			$fila3 = mysql_num_rows($result);
			if($fila3 < 1) {
				$param = " pedido_id ='" . $k . "'";
				$sql_data = [
					'pedido_estatus' => '90',
					'subtotal' => '0',
					'impuesto' => '0',
				];
				ejecutar_db($dbpfx . 'pedidos', $sql_data, 'actualizar', $param);
			}
*/
			actualiza_pedido($k, $dbpfx);
		}

		unset($_SESSION['orden']);
		actualiza_orden ($orden_id, $dbpfx);
		unset($sql_data_array);
		$preg2 = "SELECT sub_siniestro FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_siniestro = '1' AND sub_estatus < '190'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de subordenes! ".$preg2);
		$fila2 = mysql_num_rows($matr2);
		$parametros="orden_id = '" . $orden_id . "'";
		$sql_orden = array('orden_ubicacion' => constant('ZONA_DE_ESPERA'));
		if($fila2 > 0) {
			$sql_orden['orden_servicio'] = '4';
		} else {
			$sql_orden['orden_servicio'] = '3';
		}
		ejecutar_db($dbpfx . 'ordenes', $sql_orden, 'actualizar', $parametros);
		$tipopago = $tipopago -100;
		bitacora($orden_id, 'Cambio a estatus ' . $tipopago, $dbpfx, $motivo, $visicom);
		unset($sql_orden);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('ordenes.php?accion=perdida&orden_id=' . $orden_id . '&reporte=' . $sub_reporte);
	}
}

elseif ($accion==='cancelar') {
	
	$funnum = 1040030;
	$error = 0;
	if($oid == '' && $id != 17) {
		$preg0 = "SELECT * FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección! " . $preg0);
		$mensaje_pedidos = '';
		$mensaje_paquetes = '';
		
		while($sub_orden = mysql_fetch_array($matr0)) {
			
			// --- Revisar pedidos en los productos de la suborden en curso ---
			$preg1 = "SELECT op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden['sub_orden_id'] . "' AND op_pedido > '0' AND op_tangible < '3'";
			$matr1 = mysql_query($preg1) or die("ERROR: Falló selección de refacciones en pedido!");
			$fila1 = mysql_num_rows($matr1);
			
			if($fila1 > 0) {

				$error = 1;
				$pedido_array = [];
				while($peds = mysql_fetch_array($matr1)) {
					
					if($pedido_array[$peds['op_pedido']] == 1){
						// --- No agregar ---
					} else{
						$pedido_array[$peds['op_pedido']] = 1;
						$pedidos .= $peds['op_pedido'] . ',';	
					}
				
				}
				
				$mensaje_pedidos .= 'La Tarea ' . $sub_orden['sub_orden_id'] . ' tiene los pedido(s) ' . $pedidos . ' mismos que debe cancelar para poder eliminar esta Tarea.<br>';
			}
			
			// --- Consultar si los productos no pertenecen a un paquete y ya fueron surtidos ----
			$preg_paq = "SELECT prod_id, op_surtidos, op_nombre FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden['sub_orden_id'] . "' AND prod_id > 0 AND op_surtidos > '0.0001'";
			$matr_paq = mysql_query($preg_paq) or die("ERROR: Fallo selección de refacciones de paquete de servicio! " . $preg_paq);
			$fila_paq = mysql_num_rows($matr_paq);
			
	
			if($fila_paq > 0){

				$refs_paquete = '';
				while($paq_ser = mysql_fetch_array($matr_paq)){
					$refs_paquete .= 'Se entregó al operador ' . $paq_ser['op_surtidos'] . ' Pieza(s) de: ' . $paq_ser['op_nombre'] . ',';
				}
		
				$error = 1;
				$mensaje_paquetes .= 'La Tarea ' . $sub_orden['sub_orden_id'] . ' tiene refacciones entregadas a operadores: ' . $refs_paquete . ', mismos que debe(n) de ser devueltos para poder eliminar esta Tarea.<br>';

			}
			
			if($sub_orden['recibo_id'] > 0) { 
				$error = 1; 
				$mensaje .= 'La Orden de Trabajo tiene Recibos de Destajo, no se puede cancelar esta OT.<br>';
			}
			if($sub_orden['fact_id'] > 0) {
				$error = 1;
				$mensaje .= 'La Orden de Trabajo tiene al menos una Tarea Facturada, no se puede cancelar esta OT.<br>';
			}
		}
	}
	
	if($error == '0'){
		
		$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE ";
		if($confolio == 1 && $oid != '') {
			$pregunta .= "oid = '$oid'";
		} else {
			$pregunta .= "orden_id = '$orden_id'";
		}

		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion! " . $pregunta);
		$orden = mysql_fetch_array($matriz);
		include('parciales/encabezado.php');
		echo '	
			<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		
				<div id="principal">
					<form action="ordenes.php?accion=confcancelar" method="post" enctype="multipart/form-data">
						<table cellpadding="0" cellspacing="0" border="0" class="agrega">
							<tr class="cabeza_tabla">
								<td colspan="2">Confirmar o Rechazar la cancelación de la Orden de Trabajo de vehículo:</td>
							</tr>
							<tr>
								<td colspan="2" style="text-align:left;">
									' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . $lang['Placas'] . $orden['orden_vehiculo_placas'] . '
								</td>
							</tr>
							<tr>
								<td>
									Indicar el motivo de la cancelación:<br>Mínimo 50 caracteres.
								</td>
								<td>
									<textarea name="motivo" cols="40" rows="6">'."\n";

		if($_SESSION['ord']['motivo'] != '') { echo $_SESSION['ord']['motivo']; unset($_SESSION['ord']); }
			echo '
									</textarea>
								</td>
							</tr>
							<tr>
								<td>
									<input type="submit" name="confirmar" value="Cancelar" /><label>Confirmar Cancelación</label>
								</td>
								<td>
									<a href="ordenes.php?accion=consultar&';

		if($confolio == 1 && $id == 17 && $oid != '') {
			echo 'oid=' . $oid;
		} else {
			echo 'orden_id=' . $orden_id;
		}
		echo '">
									<button type="button">Rechazar Cancelación</button></a>
								</td>
							</tr>
						</table>
						<input type="hidden" name="orden_id" value="' . $orden_id . '" />
						<input type="hidden" name="oid" value="' . $oid . '" />
					</form>'."\n";
	
	} else {
		$_SESSION['msjerror'] = $mensaje . '<br>' . $mensaje_pedidos . '<br>' . $mensaje_paquetes;
		redirigir('ordenes.php?accion=consultar&orden_id='.$orden_id);
	}
}

elseif ($accion==='confcancelar') {
	
	if (validaAcceso('1040030', $dbpfx) == 1 || $_SESSION['rol06']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol04']=='1') {
		//	Acceso permitido.		
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	
	$error = 'no';
	if($motivo == '' || !isset($motivo) || strlen($motivo) < 20) {
		$error = 'si'; $_SESSION['msjerror'] = 'Debe indicar el motivo de la cancelación y el texto debe tener al menos 50 caracteres de longitud.';
		$_SESSION['ord']['motivo'] = $motivo;
	}
	
	if ($confirmar=="Cancelar" && $error == 'no') {
		if($confolio == 1 && $oid != '') {
			$parametros='oid = ' . $oid;
		} else {
			$parametros='orden_id = ' . $orden_id;
	      $sql_data_array = array('sub_estatus' => '190', 'sub_refacciones_recibidas' => '0');
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		}
		$sql_data_array = array('orden_estatus' => '90', 'orden_alerta' => '0', 'orden_ref_pendientes' => '0');
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		unset($sql_data_array);

		$preg4 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE activo = 1 AND acceso = 0 AND rol02 = 1";
		$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de gerentes! " . $preg4);
		while($ger = mysql_fetch_array($matr4)) {
			if($confolio == 1 && $oid != '') {
				bitacora('0', 'Se canceló el ingreso de la Orden Provisional ' . $oid, $dbpfx, 'Para: ' . $ger['nombre'] . ' ' . $ger['apellidos'] . '. Orden de Trabajo Provisional ' . $oid . ' cancelada: ' . $motivo, 3, '', '', $ger['usuario']);
			} else {
				bitacora($orden_id, 'Orden de Trabajo Cancelada.', $dbpfx, 'Para: ' . $ger['nombre'] . ' ' . $ger['apellidos'] . '. Orden de Trabajo Cancelada: ' . $motivo, 3, '', '', $ger['usuario']);
			}
		}
	} else {
		if($confolio == 1 && $oid != '') {
			redirigir('ordenes.php?accion=cancelar&oid=' . $oid);
		} else {
			redirigir('ordenes.php?accion=cancelar&orden_id=' . $orden_id);
		}
	}
	if($confolio == 1 && $oid != '') {
		redirigir('ordenes.php?accion=consultar&oid=' . $oid);
	} else {
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
}

elseif ($accion==='comptaller') {
	
	$funnum = 1040010;

	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1') {
		$fecomp = date('Y-m-d 18:00:00', strtotime($fecomp));
		$parametros="orden_id = '" . $orden_id . "'";
		$sql_data_array = array('orden_fecha_compromiso_de_taller' => $fecomp);
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Cambio de fecha Compromiso de Taller', $dbpfx, 'Cambio de fecha Compromiso de Taller', '0');
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función no fue autorizado';
	}
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
}

elseif ($accion==='fentrega') {
	
	$funnum = 1040060;

	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1') {
		$fent = date('Y-m-d 18:00:00', strtotime($fent));
		$parametros="orden_id = '" . $orden_id . "'";
		$sql_data_array = array('orden_fecha_de_entrega' => $fent);
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Cambio de fecha de Entrega de Vehículo', $dbpfx, 'Cambio de fecha de Entrega de Vehículo', '0');
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función no fue autorizado';
	}
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);

}

elseif ($accion==='cambiaubic') {
	
	if (validaAcceso('1040055', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1'))) {
		if($nubic != '') {
			$parametros="orden_id = '" . $orden_id . "'";
			$sql_data_array = array('orden_sitio_actual' => $nubic);
			ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
			bitacora($orden_id, 'Cambio de Ubicación a ' . $nubic, $dbpfx, 'Cambio de Ubicación a ' . $nubic, '0');
		}
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función no fue autorizado';
	}
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
}

elseif ($accion==='fpromesa') {
	if(validaAcceso('1040050', $dbpfx) == '1' && strtotime($fprom) > 1000000) {
		$fprom = date('Y-m-d 18:00:00', strtotime($fprom));
		$parametros="orden_id = '" . $orden_id . "'";
		$sql_data_array = array('orden_fecha_promesa_de_entrega' => $fprom);
		$quefecha = $lang['CambFeProm'];
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, $quefecha . $fprom, $dbpfx, $quefecha . $fprom, '0');
	} elseif((validaAcceso('1040115', $dbpfx) == '1' || validaAcceso('1040120', $dbpfx) == '1') && strtotime($ffprod) > 1000000) {
		$ffprod = date('Y-m-d 18:00:00', strtotime($ffprod));
		$sql_data_array = array('orden_fecha_proceso_fin' => $ffprod);
		$quefecha = $lang['CambTermProd'];
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, $quefecha . $ffprod, $dbpfx, $quefecha . $ffprod, '0');
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función no fue autorizado';
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
	$msjpass = 'Se actualizo la fecha de Promesa de Entrega a: ' . $ffprod . $fprom;
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id . '&msjpass=' . $msjpass);
}

elseif ($accion==='otcambiar') {

	if((validaAcceso('1120005', $dbpfx) == '1' || validaAcceso('1040125', $dbpfx) == 1) || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol04']=='1' || $_SESSION['rol06']=='1'))) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['Acceso no autorizado']; 
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}

		$preg1 = "SELECT orden_vehiculo_placas, orden_asesor_id, orden_servicio, orden_categoria, orden_sitio_ingreso, orden_grua, orden_garantia, orden_torre, orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matr1 = mysql_query($preg1) or die('Error en selección de Ordenes! '.$preg1);
		$ord = mysql_fetch_array($matr1);
		if($ord['orden_servicio'] == '4') {
			$preg3 = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190'";
			$matr3 = mysql_query($preg3) or die('Error en selección de Tareas! ' . $preg3);
			$otaseg = 0;
			while ($sub = mysql_fetch_array($matr3)) {
				if($sub['sub_aseguradora'] > 0) { $otaseg = 1; }
			}
		} ?>
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
		  			<div class="content-box-header">
						<div class="panel-title">
							
			  				<h2>Modificar datos generales de la OT: <?php echo $orden_id;?></h2>
			  				
						</div>
				  	</div>
				</div>
			</div><br>

			<div class="col-md-8" style="box-shadow: 3px 4px 10px 0px #484848; border-radius: 10px; margin: 10px;">
				<div class="col-md-12 panel-body">
					
					<br> <?php
						if($otaseg == 1) { ?>
		            <div class="alert alert-warning" role="alert">
						Una Orden que tiene trabajos de Aseguradora o Convenio no se puede cambiar directamente a Particular o Garantía.
						<b>Tiene dos opciones:</b><br>
						1.- Por favor vaya a la <b>"<a href="ordenes.php?accion=consultar&orden_id=<?php echo $orden_id; ?>" class="alert-link">Vista principal</a>"</b> y cambie cada uno de los siniestros a <b>"Trabajo Particular"</b>.<br>
						2.- Use la acción <b>"<a href="ordenes.php?accion=perdida&orden_id=<?php echo $orden_id; ?>" class="alert-link">Pago Perida</a>"</b> y seleccione la opción: <b>"Cambiar a Particular"</b>. <br>
						<b>Gracias!</b>
					</div>
						<?php } ?>
						<form action="ordenes.php?accion=otcambiadat" method="post" enctype="multipart/form-data">
				<div class="col-md-3 col-lg-4">
					
						<?php 
						if($ord['orden_estatus'] == '17') { ?>
							<div class="form-group" style="margin-bottom: 1rem;">
								<label style="font-size: initial;" for="">Cambio de Placas</label>
								<input class="form-control" type="text" name="placas" value="<?php echo $ord['orden_vehiculo_placas']; ?>"/>
							</div>
						<?php 
						} ?>

						
						<div class="form-group" style="margin-bottom: 1rem;">
							<label style="font-size: initial;" for="asesor">Seleccionar Asesor de Servicio</label><br>
							<select name="asesor"> <?php 
								$preg2 = "SELECT usuario, nombre, apellidos, localidad FROM " . $dbpfx . "usuarios WHERE acceso = '0' AND rol06 = '1' AND activo = '1'";
								if($_SESSION['acceso'] == '0' && $_SESSION['codigo'] >= '20') {
									$preg2 .= " AND localidad = '" . $_SESSION['localidad'] . "'";
								}
								$preg2 .= " ORDER BY nombre";
								$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Asesores! ".$preg2);
								while ($usu = mysql_fetch_array($matr2)) {
									echo '<option value="' . $usu['usuario'] . '"';
									if($usu['usuario'] == $ord['orden_asesor_id']) { echo ' selected '; }
									echo '>' . $usu['usuario'] . ' - ' . $usu['nombre'] . ' ' . $usu['apellidos'] . '</option>'."\n";
								} ?>
							</select> 
						</div>

						<div class="form-group" style="margin-bottom: 1rem;">
							<label style="font-size: initial;" for="tipo">Tipo de Servicio</label><br>
						<select name="tipo">
							<?php
							if($otaseg == 1) { $tsi = 4; } else { $tsi = 1; }
							for($i=$tsi;$i<5;$i++) {
								echo '<option value="' . $i . '"';
								if($i == $ord['orden_servicio']) { echo ' selected '; }
								echo '>' . $lang['os'.$i] . '</option>'."\n";
							} ?>
						</select>
						</div>

						<div class="form-group" style="margin-bottom: 1rem;">
							<label style="font-size: initial;" for="">Garantia</label>
							<input class="form-control" type="text" name="garantia" value="<?php echo $ord['orden_garantia']; ?>" />
						</div>
				</div>
				<div class="col-md-3 col-lg-4">

						<div class="form-group" style="margin-bottom: 1rem;">
							<label style="font-size: initial;" for="categoria">Categoría de Servicio</label><br>
							<select name="categoria"> <?php 
								for($se=1;$se<=4;$se++) {
									echo '<option value="' . $se . '"';  
									if($ord['orden_categoria'] == $se) { echo ' selected="selected" '; }
									echo '>' . constant('CATEGORIA_DE_REPARACION_' .$se) . '</option>'."\n";
								} ?>
							</select>
						</div>

						<div class="form-group" style="margin-bottom: 1rem;">
							<label style="font-size: initial;" for="grua">Grua</label><br>
							<select name="grua"> <?php
								$grua = array('No definido','Sí','No');
								for($i=0;$i<3;$i++){
									echo '<option value="' . $i . '"';  
									if($ord['orden_grua'] == $i) { echo ' selected="selected" '; }
									echo '>' . $grua[$i] . '</option>'."\n";
								} ?>
							</select>
						</div>

						<div class="form-group" style="margin-bottom: 1rem;">
							<label style="font-size: initial;" for="">Torre</label>
							<input class="form-control" type="text" name="torre" value="<?php echo $ord['orden_torre']; ?>" />
						</div>

						<div class="form-group" style="margin-bottom: 1rem;">
							<label style="font-size: initial;" for="">Recibido en</label><br>
							<select name="sitio"> <?php
								foreach($ubicaciones as $uk => $uv) {
									echo '<option value="' . $uk . '" ';
									if($ord['orden_sitio_ingreso'] == $uk) { echo ' selected '; }
									echo '>' . $uv . '</option>'."\n";
								} ?>
							</select>
						</div>

						<input type="hidden" name="orden_id" value="<?php echo $orden_id; ?>" />
						<input type="submit" class="btn btn-success" value="Enviar" />
					
				</div>
				</form>
				
					<div class="form-group col-md-3 col-lg-12" style="background-color: white; background-color: white;margin-top: 10px;margin-bottom: 10px;">
						<center><h2>&nbsp;</h2></center>
					</div>
				<div class="col-md-3 col-lg-8">
					<div class="form-group" style="margin-bottom: 1rem;">
						<a href="ordenes.php?accion=consultar&orden_id=<?php echo $orden_id; ?>"><img src="idiomas/es_MX/imagenes/regresar-h.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo" border="0" ></a>
						<a href="ingreso.php?accion=consultar&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/hoja-ingreso-h.png" alt="Hoja de Ingreso" title="Hoja de Ingreso"></a>
						<a href="ingreso.php?accion=inventario&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/inventario-h.png" alt="Inventario de Ingreso" title="Inventario de Ingreso"></a>
						<a href="ingreso.php?accion=cartaaxa&orden_id=<?php echo $orden_id; ?>"><img src="idiomas/es_MX/imagenes/carta-axa-h.png" alt="" /></a>
						<a href="presupuestos.php?accion=crear&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/crear-presupuesto-h.png" alt="Agregar otra tarea" title="Agregar otra tarea"></a>
						<a href="entrega.php?accion=salida&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/salida-con-rp-h.png" alt="Entrega con Vale de Refacciones" title="Entrega con Vale de Refacciones"></a>
						<a href="ordenes.php?accion=perdida&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/pago-h.png" alt="Pérdida Total o Pago de daños" title="Pérdida Total o Pago de daños"></a>
						<a href="ordenes.php?accion=cancelar&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/cancelar-h.png" alt="Cancelar Orden de Trabajo" title="Cancelar Orden de Trabajo"></a><br>
						<a href="proceso.php?accion=imprimir&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/imprimir-sot-h.png" alt="Imprimir Codigos de Barras para Operadores" title="Imprimir Codigos de Barras para Operadores"></a>
						<a href="documentos.php?accion=avances&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/avance-reparacion-h.png" alt="Subir imágenes de Avance de Reparación" title="Subir imágenes de Avance de Reparación"></a>
						<a href="entrega.php?accion=listado&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/imprimir-recibo-h.png" alt="Lista de entrega para facturacion" title="Lista de entrega para facturacion"></a>
						<a href="entrega.php?accion=garantia&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/formato-de-entrega-h.png" alt="Formato de Entrega" title="Formato de Entrega"></a>
						<a href="entrega.php?accion=cobros&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/cobro-registrar-h.png" alt="Registrar Cobros" title="Registrar Cobros"></a>
						<a href="factura-3.3.php?accion=consultar&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/facturas-3.3.png" alt="Crear Factura" title="Crear Factura"></a>
						<a href="nota-de-credito.php?accion=consultar&orden_id=<?php echo $orden_id; ?>"><img class="acciones" src="idiomas/es_MX/imagenes/nota-de-credito-h.png" alt="Crear Nota de Credito" title="Crear Nota de credito"></a>
					</div>
				</div>
			</div>


<?php

}

elseif ($accion==='otcambiadat') {

	if((validaAcceso('1120005', $dbpfx) == '1' || validaAcceso('1040125', $dbpfx) == 1) || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol04']=='1' || $_SESSION['rol06']=='1'))) {
		// Acceso permitido
	} else {
		$_SESSION['msjerror'] = $lang['Acceso no autorizado']; 
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
	$error = 'no'; $msj = '';

	if($error == 'no') {
		$garantia = intval(limpiarNumero($garantia));
		$torre = preparar_entrada_bd($torre);
		$parametros="orden_id = '" . $orden_id . "'";
		$sql_data_array = array(
			'orden_asesor_id' => $asesor,
			'orden_servicio' => $tipo,
			'orden_garantia' => $garantia,
			'orden_categoria' => $categoria,
			'orden_grua' => $grua,
			'orden_torre' => $torre,
			'orden_sitio_ingreso' => $sitio);

		if($placas != '') {
			$placas = strtoupper(limpiarString($placas));
			$preg0 = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_placas LIKE '%$placas%' OR vehiculo_eco LIKE '%$placas%'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección! ". $preg0);
			$fila0 = mysql_num_rows($matr0);
			if($fila0 > 1) {
				$_SESSION['msjerror'] = 'El número de placas ' . $placas . ' coincide con más de un vehiculo, por favor revise su dato.<br>';
				redirigir('ordenes.php?accion=otcambiar&orden_id=' . $orden_id);
			}
			if($fila0 == 1) {
				$veh = mysql_fetch_array($matr0);
				$sql_data_array['orden_vehiculo_id'] = $veh['vehiculo_id'];
				$sql_data_array['orden_cliente_id'] = $veh['vehiculo_cliente_id'];
				$sql_data_array['orden_vehiculo_marca'] = $veh['vehiculo_marca'];
				$sql_data_array['orden_vehiculo_tipo'] = $veh['vehiculo_tipo'];
				$sql_data_array['orden_vehiculo_color'] = $veh['vehiculo_color'];
				$sql_data_array['orden_vehiculo_placas'] = $veh['vehiculo_placas'];
			} else {
				$sql_data_array['orden_vehiculo_id'] = 0;
				$sql_data_array['orden_cliente_id'] = 0;
				$sql_data_array['orden_vehiculo_marca'] = '';
				$sql_data_array['orden_vehiculo_tipo'] = $placas;
				$sql_data_array['orden_vehiculo_color'] = '';
				$sql_data_array['orden_vehiculo_placas'] = $placas;
			}
		}

		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		$cambio = 'Cambio Gral Asesor ' . $asesor . ', Tipo de servicio: ' . $tipo . ', OT Ref Garantía: ' . $garantia . ', Categoría: ' . $categoria . ', Grua: ' . $grua;
		if($placas != '') {
			$cambio .= ', Ingreso a ' . $sitio . ' y Placas a ' . $placas;
		} else {
			$cambio .= ' e Ingreso a ' . $sitio;
		}
		bitacora($orden_id, $cambio, $dbpfx);
		$msjpass = 'Se actualizarón los datos Generales de la OT';
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id . '&msjpass=' . $msjpass);
	} else {
		$_SESSION['msjerror'] = $msj;
		redirigir('ordenes.php?accion=otcambiar&orden_id=' . $orden_id);
	}
}

elseif ($accion==='cambiarep') {
	
	$funnum = 1040075;

	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol05'] == '1' || $_SESSION['rol12'] == '1') {
		echo '			<form action="ordenes.php?accion=cambiapol" method="post" enctype="multipart/form-data">'."\n";
		echo '			<table cellpadding="0" cellspacing="0" border="0" class="agrega">
				<tr class="cabeza_tabla"><td colspan="2">' . $lang['Cambio de números'] . '</td></tr>'."\n";
		echo '				<tr><td>' . $lang['Cambiar número de Siniestro de'] . ' ' . $reporte . ' a:</td><td><input type="text" name="nvoreporte" value="' . $reporte . '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Cambiar número de Póliza de'] . ' ' . $poliza . ' a:</td><td><input type="text" name="nvapoliza" value="' . $poliza . '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Cambiar monto de Deducible de'] . ' $' . number_format($deducible, 2) . ' a:</td><td><input type="text" name="nvodeducible" value="' . $deducible . '" /></td></tr>'."\n";
		echo '				<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /><input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="hidden" name="reporte" value="' . $reporte . '" /><input type="hidden" name="poliza" value="' . $reporte . '" /><input type="hidden" name="deducible" value="' . $reporte . '" /></td></tr>'."\n";
		echo '			</table>'."\n";
		echo '			</form>'."\n";
		echo '			<div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div>'."\n";
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función no fue autorizado';
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
}

elseif ($accion==='cambiapol') {

	$error = 'no';

	if (validaAcceso('1040075', $dbpfx) == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol05'] == '1' || $_SESSION['rol12'] == '1') {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función no fue autorizado.<br>';
		$error = 'si';
	}

	$nvodeducible = limpiarNumero($nvodeducible);
	$nvoreporte = preparar_entrada_bd($nvoreporte);
	$nvapoliza = preparar_entrada_bd($nvapoliza);

	if ($nvoreporte == '') {$error = 'si'; $_SESSION['msjerror'] .= 'El número de reporte no puede estar vacío. Si es Particular coloque 0.<br>';}

	if ($error == 'no') {
		$parametros="orden_id = '" . $orden_id . "' AND sub_reporte = '" . $reporte . "'";
// ------ Ajustando los números de reporte o siniestro en Tareas de la OT
		$sql_data_array = array(
			'sub_reporte' => $nvoreporte,
			'sub_deducible' => $nvodeducible,
			'sub_poliza' => $nvapoliza);
		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		unset($sql_data_array);
// ------ Ajustando los números de reporte o siniestro en facturas previamiente emitidas.
		$parametros="orden_id = '" . $orden_id . "' AND reporte = '" . $reporte . "'";
		$sql_data_array = array('reporte' => $nvoreporte);
		ejecutar_db($dbpfx . 'facturas_por_cobrar', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, $lang['CambDeSiniestroDe'] . ' ' . $reporte . ' a ' . $nvoreporte . ', ' . $lang['DePoliza'] . ' ' . $poliza . ' a ' . $nvapoliza . ' y deducible de ' . $deducible . ' a ' . $nvodeducible . '.', $dbpfx);
		unset($_SESSION['msjerror']);
	}
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
}

elseif ($accion==='catserv') {
	
	$funnum = 1040080;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || $_SESSION['rol02'] == '1' || $_SESSION['rol05'] == '1') {
		$parametros="orden_id = '" . $orden_id . "'";
		$sql_data_array = array('orden_categoria' => $categoria);
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Cambio de Categoria de Servicio a categoria ' . constant('CATEGORIA_DE_REPARACION_' .$categoria), $dbpfx, 'Cambio de Categoria de Servicio a categoria ' . constant('CATEGORIA_DE_REPARACION_' . $categoria),0);
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función no fue autorizado';
	}
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
}

elseif ($accion==='abreot') {

	if(validaAcceso('1040095', $dbpfx) == '1' || ($solovalacc != '1' && $abreotforz != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol05'] == '1'))) {
// ------ Colocar $abreotforz en 1 para forzar que sólo se puedan reabrir OTs cerradas con permiso de usuario
		$mensaje = $lang['Acceso autorizado'];
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función no fue autorizado';
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
	
// ------ Verifica que sólo OTs con estatus 99 se puedan regresar
	$preg1 = "SELECT orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de ordenes! " . $preg1);
	$ord = mysql_fetch_array($matr1);
	if($ord['orden_estatus'] == '99') {
		$parametros="orden_id = '" . $orden_id . "'";
		$sql_data_array = array('orden_estatus' => '15');
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Reapertura de OT 99 a estatus 15 para agregar complementos ', $dbpfx, 'Reapertura OT para agregar complementos',0);
	} else {
		$_SESSION['msjerror'] = $lang['ReAperturaNO'];
	}
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
}

?>

		</div>
	</div>
<?php include('parciales/pie.php'); ?>
