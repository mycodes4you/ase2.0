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
				//$sql_array = array('orden_ubicacion' => $ubicaciones[$_SESSION['localidad']]);
				$sql_array = array('orden_ubicacion' => "Zona de Espera");
				ejecutar_db($dbpfx . 'ordenes', $sql_array, 'actualizar', $parametros);
				$_SESSION['msjerror'] .= 'Vehículo reingresado al Taller.<br>';
				
				// --- Actualizar la fecha de ingresó si esta colocada la variable $actualiza_fech_ingreso --
				
//				$actualiza_fech_ingreso = 1;
				if($actualiza_fech_ingreso == 1){
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
					'orden_ubicacion' => 'Recepción',
					'orden_alerta' => '0',
					'orden_estatus' => '17');
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
					bitacora($orden_id, 'Cambio a estatus 17 Creación de nueva OT para vehículo preregistrado desde Vigilancia', $dbpfx, $datoing . ': Creación de nueva Orden de Trabajo desde Vigilancia en ' . $ubicaciones[$_SESSION['localidad']] . ' para vehículo con ' . $kms . ' Kilometros. Obs: ' . $obs, 5);
					unset($_SESSION['ord']);
					unset($vig_array);
				} else {
					bitacora($orden_id, 'Cambio a estatus 17 Creación de nueva OT para vehículo preregistrado', $dbpfx);
				}
			}

//  ----------- Uso futuro cuando todas las tablas "ordenes" incluyan la columna "oid" aun cuando no la utilicen.  -----------------
			/* 
			if($multiorden != 1) {
				$parametros = "oid ='" . $orden_id . "'";
				$sql_data_array = array('orden_id' => $orden_id);
				ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
			}
			*/
			//  --------------------------------------------------------------------------------

//  ---------- Agrega los presupuestos previos como tareas de la nueva OT listos para aceptar y modificar o cancelar  -------------- 

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
							bitacora($orden_id, 'Tarea ' . $sub_orden_id . ' creada desde Presupuesto Previo '  . $prev['previa_id'], $dbpfx, 'Tarea ' . $sub_orden_id . ' creada desde Presupuesto Previo '  . $prev['previa_id'], '0', $sub_orden_id);
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
			//  -----------------------------------------------------------------------------------------------
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

			//  ----------- Uso futuro cuando todas las tablas "ordenes" incluyan la columna "oid" aun cuando no la utilicen.  -----------------
			/* 
			if($multiorden != 1) {
				$parametros = "oid ='" . $orden_id . "'";
				$sql_data_array = array('orden_id' => $orden_id);
				ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
			}
			*/
			//  --------------------------------------------------------------------------------------------------------------------------------

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

elseif($accion==='modificar') {
	// Eliminar por Fuera de Uso -----------
	$orden_id=preparar_entrada_bd($orden_id);
	$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección! " . $pregunta);
	$orden = mysql_fetch_array($matriz);

	echo '<br><form action="ordenes.php?accion=actualizar" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr class="cabeza_tabla"><td colspan="2">Modificar de Orden de Trabajo</td></tr>
		<tr><td colspan="2">' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . $lang['Placas'] . $orden['orden_vehiculo_placas'] . '</td></tr>
		<tr><td>Asesor Asignado: </td><td style="text-align:left;">
			<select name="asesor" size="1">
				<option value="" > Seleccione... </option>';

	$pregunta2 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE codigo=30";
		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección! " . $pregunta2);
		while($usuario = mysql_fetch_array($matriz2)) {
			echo '				<option value="' . $usuario['usuario'] . '" ';
			if ($usuario['usuario'] == $orden['orden_asesor_id']) { echo 'selected="selected"';} 
			echo '>' . $usuario['nombre'] . ' ' . $usuario['apellidos'] . '</option>';
			}
		echo '			</select>
		</td></tr>';
		echo '		<tr>
			<td valign="top">Tipo de Servicio: </td>
			<td style="text-align:left;">
				<input type="radio" name="servicio" value="1" '; 
		if (($orden['orden_servicio']=="1" && !$_SESSION['orden']['servicio']) || ($_SESSION['orden']['servicio']=='1')) { echo 'checked="checked"';	} 
		echo ' />' . ORDEN_SERVICIO_1 . '<br>
				<input type="radio" name="servicio" value="2" ';
		if (($orden['orden_servicio']=="2" && !$_SESSION['orden']['servicio']) || ($_SESSION['orden']['servicio']=='2')) { echo 'checked="checked"'; }
		echo ' />' . ORDEN_SERVICIO_2 .'<br>
				<input type="radio" name="servicio" value="3" ';
		if (($orden['orden_servicio']=="3" && !$_SESSION['orden']['servicio']) || ($_SESSION['orden']['servicio']=='3')) { echo 'checked="checked"'; }
		echo ' />' . ORDEN_SERVICIO_3 . '<br>
				<input type="radio" name="servicio" value="4" ';
		if (($orden['orden_servicio']=="4" && !$_SESSION['orden']['servicio']) || ($_SESSION['orden']['servicio']=='4')) { echo 'checked="checked"'; }
		echo ' />' . ORDEN_SERVICIO_4 . '<br>
			</td>
		</tr>
		<tr>
			<td valign="top">Categoría de Servicio: </td>
			<td style="text-align:left;">
				<select name="categoria" size="1">
					<option value=""> Seleccione </option>
					<option value="1" ';
		if (($orden['orden_categoria']=="1" && !$_SESSION['orden']['categoria']) || ($_SESSION['orden']['categoria']=='1')) { echo 'selected="selected"'; }
		echo '>' . CATEGORIA_DE_REPARACION_1 . '</option>
					<option value="2" ';
		if (($orden['orden_categoria']=="2" && !$_SESSION['orden']['categoria']) || ($_SESSION['orden']['categoria']=='2')) { echo 'selected="selected"'; }
		echo '>' . CATEGORIA_DE_REPARACION_2 . '</option>
					<option value="3" ';
		if (($orden['orden_categoria']=="3" && !$_SESSION['orden']['categoria']) || ($_SESSION['orden']['categoria']=='3')) { echo 'selected="selected"'; }
		echo '>' . CATEGORIA_DE_REPARACION_3 . '</option>
					<option value="4" ';
		if (($orden['orden_categoria']=="4" && !$_SESSION['orden']['categoria']) || ($_SESSION['orden']['categoria']=='4')) { echo 'selected="selected"'; }
		echo '>' . CATEGORIA_DE_REPARACION_4 . '</option>
				</select>
			</td>
		</tr>';
		$pregunta3 = "SELECT * FROM " . $dbpfx . "documentos WHERE orden_id = '" . $orden_id . "'";
		$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo selección! " . $pregunta3);
		$filas = mysql_num_rows($matriz3);
		if($filas == 0) {
			echo '		<tr><td>Agregar fotografía de tablero con odómetro y combustible</td><td style="text-align:left;"><input type="file" name="tablero" size="30" /></td></tr>
		<tr><td>Agregar fotografía de esquina frontal derecha</td><td style="text-align:left;"><input type="file" name="fefder" size="30" /></td></tr>
		<tr><td>Agregar fotografía de esquina posterior izquierda</td><td style="text-align:left;"><input type="file" name="fepizq" size="30" /></td></tr>
		<tr><td>Agregar fotografía de costado derecho</td><td style="text-align:left;"><input type="file" name="fcder" size="30" /></td></tr>
		<tr><td>Agregar fotografía de costado izquierdo</td><td style="text-align:left;"><input type="file" name="fcizq" size="30" /></td></tr>
		<tr><td>Agregar fotografía posterior</td><td style="text-align:left;"><input type="file" name="fpos" size="30" /></td></tr>
		<tr><td>Agregar fotografía frontal</td><td style="text-align:left;"><input type="file" name="ffron" size="30" /></td></tr>';
			echo '<input type="hidden" name="afotos" value="insertar">';
		} else {
			$nombres = array('tablero', 
  				'fefder', 
  				'fepizq', 
	  			'fcder', 
  				'fcizq', 
  				'fpos', 
  				'ffron');
			$i=0;
			while($documento = mysql_fetch_array($matriz3)) {
				echo '		<tr><td>Cambiar fotografía de ' . $documento['doc_nombre'] . ' <img src="' . DIR_DOCS . $documento['doc_archivo'] . '" border="0" width="80"></td><td style="text-align:left;"><input type="file" name="' . $nombres[$i] . '" size="30" /></td></tr>';
			$i++;
			}
			echo '<input type="hidden" name="afotos" value="actualizar">';
		}
		echo '		<tr><td>Odometro: </td><td style="text-align:left;"><input type="text" name="odometro" size="10" maxlength="10" value="';
		echo ($_SESSION['orden']['odometro']) ? $_SESSION['orden']['odometro'] : $orden['orden_odometro'];
		echo '" /></td></tr>
		<tr><td>Número y color de Torre de servicio: </td><td style="text-align:left;"><input type="text" name="torre" size="30" maxlength="30" value="';
		echo ($_SESSION['orden']['torre']) ? $_SESSION['orden']['torre'] : $orden['orden_torre'];
		echo '" /></td></tr>' . "\n";
		//	$preg0 = "SELECT esp_nombre FROM " . $dbpfx . "espacios WHERE orden_id IS NULL ORDER BY esp_nombre";
		//	$mat0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de espacios!");
		//	$num_aseg = mysql_num_rows($mat0);
		//	if($num_aseg > 1) {
		echo '		<tr>
		<td>Selecciona si el auto <strong>se queda "En Taller" o se va en "Tránsito"</strong>:</td>
		<td style="text-align:left;">
			<select name="espacio" size="1">' . "\n";
     	echo '				<option value="" >Seleccionar... </option>' . "\n";
     	echo '				<option value="Transito" '; echo ($_SESSION['orden']['espacio'] == 'Transito') ? 'selected="1"' : ''; echo '>Tránsito</option>' . "\n";
		/*while ($esp = mysql_fetch_array($mat0)) {
     		echo '				<option value="' . $esp['esp_nombre'] . '" '; echo ($_SESSION['orden']['espacio'] == $esp['esp_nombre']) ? 'selected="1"' : ''; echo '>' . $esp['esp_nombre'] . '</option>' . "\n";
     	}
     	*/     
     	echo '				<option value="En Taller" '; echo ($_SESSION['orden']['espacio'] == 'En Taller') ? 'selected="1"' : ''; echo '>En Taller</option>' . "\n";
	//	}
	echo '				</select></td></tr>';
	//	echo '		<tr><td>Si como espacio seleccionaste <strong>Otro</strong>, por favor indica cual: </td><td style="text-align:left;"><input type="text" name="otro" size="30" maxlength="32" /></td></tr>';
	echo '		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
	</table>';
	echo '	<input type="hidden" name="vehiculo_id" value="' . $orden['orden_vehiculo_id'] . '">
	<input type="hidden" name="vehiculo_marca" value="' . $orden['orden_vehiculo_marca'] . '">
	<input type="hidden" name="vehiculo_tipo" value="' . $orden['orden_vehiculo_tipo'] . '">
	<input type="hidden" name="vehiculo_color" value="' . $orden['orden_vehiculo_color'] . '">
	<input type="hidden" name="vehiculo_placas" value="' . $orden['orden_vehiculo_placas'] . '">
	<input type="hidden" name="cliente_id" value="' . $orden['orden_cliente_id'] . '">
	<input type="hidden" name="orden_id" value="' . $orden_id . '">'; 
	echo '</form>';

}

elseif ($accion==="consultar") {

	if($nvavista == 2) { $_SESSION['nvavista'] = 0; }
	if($_SESSION['nvavista'] == 1) {
		redirigir('ordenes-1.2.php?accion=consultar&orden_id=' . $orden_id . '&oid=' . $oid);
	}

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
			$pregunta5 = "SELECT sub_orden_id, sub_area, sub_presupuesto, sub_estatus, sub_refacciones_recibidas, sub_reporte, sub_aseguradora, sub_poliza, sub_paga_deducible, sub_deducible, sub_fecha_valaut FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190'";
			$matriz5 = mysql_query($pregunta5) or die("ERROR: Fallo selección! " . $pregunta5);
			$currest = 0; $currarea = 0; $reporte = array(); $reporte[0] = ''; $rd =1; $rc = 0; $reciente = 0; $ase2k = array();
			while($am = mysql_fetch_array($matriz5)) {
				//if($am['sub_estatus'] > $currest && $am['sub_estatus'] < 112 && $am['sub_estatus'] > 104 ) { 
				if($am['sub_estatus'] < 112 && $am['sub_estatus'] > 104 ) {
					$preg4 = "SELECT * FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" .$am['sub_orden_id'] . "' ORDER BY seg_id DESC LIMIT 1";
					$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de seguimientos! " . $preg4);
					$seg = mysql_fetch_array($matr4);
					//print_r($seg);
					//echo ' tiempo ' . strtotime($seg['seg_hora_registro']) . '<br>'."\n";
					if(strtotime($seg['seg_hora_registro']) > $reciente) {
						$currarea = $seg['sub_area']; 
						$reciente = strtotime($seg['seg_hora_registro']);
						//echo $reciente . ' -> ' . $currarea . '<br>'."\n";
					}
					$currest = $am['sub_estatus'];
				}
				$pres = $pres + $am['sub_presupuesto'];
				$tipo[$am['sub_aseguradora']] = $tipo[$am['sub_aseguradora']] + $am['sub_presupuesto'];
				foreach($reporte as $rs => $rn) {
					//echo  ' rc=' . $rc . ' rd='  . $rd . ' rs=' . $rs . ' rn=' . $rn . ' rep=' . $am['sub_reporte'];
					if($rn == $am['sub_reporte']) { $rd = 0; }
				}
				// ------ Obtener los números de aseguradora para verificar si un supervisor de aseguradora puede ver esta OT.
				$ase2k[$am['sub_aseguradora']] = 1;
				// ------
				if($rd == 1) {
					$reporte[$rc] = $am['sub_reporte'];
					$poliza[$rc] = $am['sub_poliza'];
					$rc++;
					//echo ' nvo ';
				}
				//echo '<br>'."\n"; 
				$rd = 1;
				// -------------- Obtener fecha de valuación de cada Siniestro --------------
				if(!is_null($am['sub_fecha_valaut'])) {
					$info_valuacion[$am['sub_reporte']] = $am['sub_fecha_valaut'];
				}
			}

			// ------ Verificar si un supervisor de aseguradora puede ver esta OT.
			if($_SESSION['codigo'] >= '2000' && $ase2k[$_SESSION['aseg']] != 1) {
				$error = 'si';  $mensaje = 'Acceso no permitido!<br>';
			}
			//$mensaje .= 'Mi aseguradora es: ' . $_SESSION['aseg'] . '<br>';
			//print_r($ase2k);

			if($error == 'no') {
				echo '	<table cellspacing="2" cellpadding="2" border="0">'."\n";
				echo '		<tr><td colspan="3">'."\n";
				echo '			<table cellspacing="2" cellpadding="2" border="0">'."\n";
				echo '				<tr><td rowspan="2"><a href="ordenes-1.2.php?accion=consultar&orden_id=' . $orden_id . '&nvavista=1"><img src="idiomas/' . $idioma . '/imagenes/otra-vista.png" alt="Prueba Otra Vista" title="Prueba Nueva Vista"></a></td><td align="left" colspan="3"><span style="font-weight:bold; font-size:1.2em;">Orden de trabajo: ' . $orden['orden_id'] . ' Cliente: <a href="personas.php?accion=consultar&cliente_id=' . $orden['orden_cliente_id'] . '">' . $orden['orden_cliente_id'] . '</a> Vehículo: <a href="vehiculos.php?accion=consultar&vehiculo_id=' . $orden['orden_vehiculo_id'] . '">' . $orden['orden_vehiculo_id'] . '</a></span></td></tr>'."\n";
				echo '		<tr><td colspan="3" style="font-size:1.2em;">' . $vehiculo['marca'] . ' ' . $vehiculo['tipo'] . ' ' . $vehiculo['color'] . ' ' . $vehiculo['modelo'] . ' ' . $lang['Placas'];
				if($vehiculo['placas']=='') {
					echo $orden['orden_vehiculo_tipo'];
				} else {
					echo $vehiculo['placas'];
				}
				echo '				</td></tr>'."\n";
				echo '			</table></td></tr>'."\n";
				echo '		<tr><td valign="top">'."\n";
				echo '			<table cellspacing="0" cellpadding="0" border="0"><tr><td style="vertical-align:top;">'."\n";
				echo '			<table cellspacing="0" cellpadding="2" border="1">'."\n";
				echo '				<tr><td>Estatus: </td><td colspan="2"><span style="font-weight:bold;">' . constant('ORDEN_ESTATUS_' . $orden['orden_estatus']);
				if($currarea > 0) {
					echo '<br>Vehículo en ' . constant('NOMBRE_AREA_' . $currarea); 
				} elseif($orden['orden_ubicacion'] == 'Transito') {
					echo '<br>Vehículo en Tránsito'; 
				}
				if ($orden['orden_ref_pendientes']==2) {
					echo '<br>' . REFACCIONES_ESTRUCTURALES ;
				} elseif($orden['orden_ref_pendientes']==1) {
					echo '<br>' . REFACCIONES_PENDIENTES ;
				}
				echo '</span></td><td>' . constant('ALARMA_' . $orden['orden_alerta']) . '</td></tr>'."\n";
				$asesor_id=$orden['orden_asesor_id'];
				$pregunta2 = "SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '$asesor_id'";
				$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección! " . $pregunta2);
				$asesor = mysql_fetch_array($matriz2);
				echo '			<tr><td>Asesor: </td><td colspan="3">' . $orden['orden_asesor_id'] . ' ' . $asesor['nombre'] . ' ' . $asesor['apellidos'] . ' </td></tr>'."\n";
// ------ Se valida acceso super restringido para Operarios ------
			if($_SESSION['codigo'] < '60' || $_SESSION['codigo'] > '75' || ($restrictotoper != 1 && ($_SESSION['codigo'] >= '60' && $_SESSION['codigo'] <= '75'))) {
				foreach($tipo as $ak => $av) {
					if($_SESSION['codigo'] >= '2000' && $ak < 1) {
					// Si es usuario de aseguradora no se debe mostrar trabajos particulares
					} else {
						echo '			<tr><td colspan="';
						if($ak < 1) { echo '3';	} else { echo '4'; }
						echo '"><img src="' . $ase[$ak]['logo'] . '" alt="" align="left"> ';
						if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $ak < 1)) { echo '$'. number_format($av,2);}
						if($ak < 1 && $pciva == 1) {
							echo $lang['IVAIncluido'];
						} elseif($ak < 1) {
							echo $lang['MasIVA'];
						}
						echo ' </td>';
// ------ Valida acceso a mostrar registro de Anticipo.
						if($ak < 1 && validaAcceso('1040085', $dbpfx) == 1 ) {
							echo '<td><a href="entrega.php?accion=regcobro&orden_id=' . $orden_id . '&anticipo=1&cliente_id=' . $orden['orden_cliente_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/cobro-anticipo.png" alt="" title=""></a></td>';
						}
					}
					echo '</tr>'."\n";
				}
				if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1') {
					echo '			<tr><td colspan="4"><span style="font-weight:bold;">';
					if ($orden['orden_estatus']=='30' || $orden['orden_estatus']=='31' || $orden['orden_estatus']=='32' || $orden['orden_estatus']=='33') {
						echo constant('IMPORTE_DE_PAGO_' . $orden['orden_estatus']);
					} else {
						echo IMPORTE_DE_REPARACION;
					}
					echo ': ' . money_format('%n', $pres) . '</span></td></tr>'."\n";
				}
				if($orden['orden_servicio'] == '4') {
					echo '			<tr><td colspan="2">' . $lang['NumSin'] . '</td><td style="text-align:center;">¿Paga Deducible?</td><td colspan="2" style="text-align:right;">Monto</td></tr>'."\n";
					$pdedu = array();
					mysql_data_seek($matriz5,0);
					while($pd = mysql_fetch_array($matriz5)) {
						if($pd['sub_paga_deducible'] > 0) {
							if($pdedu[$pd['sub_reporte']]['paga'] < '1' || $pdedu[$pd['sub_reporte']]['paga'] == '2') {
								$pdedu[$pd['sub_reporte']]['paga'] = $pd['sub_paga_deducible'];
							}
						} elseif($pd['sub_reporte'] != '' && $pd['sub_reporte'] != '0' && $pdedu[$pd['sub_reporte']]['paga'] < '1') {
							$pdedu[$pd['sub_reporte']]['paga'] = '0';
						}
						if($pd['sub_reporte'] != '' && $pd['sub_reporte'] != '0' && $pd['sub_deducible'] > $pdedu[$pd['sub_reporte']]['monto']) {
							$pdedu[$pd['sub_reporte']]['monto'] = $pd['sub_deducible'];
						}
					}
					foreach($pdedu as $pk => $pv) {
						echo '			<tr><td colspan="2">' . $pk . '</td><td style="font-weight:bold; text-align:center;">';
						if($pdedu[$pk]['paga'] == '1') { echo SI; }
						elseif($pdedu[$pk]['paga'] == '2') { echo NO; }
						else { echo NO_DEFINIDO;}
						echo '</td><td style="font-weight:bold; text-align:right;">';
						echo money_format('%n', $pv['monto']) . '</td></tr>'."\n";
						$dedutot = $dedutot + $pv['monto'];
					}
					echo '			<tr><td colspan="4" style="font-weight:bold;text-align:right;">Monto total de Deducible: ' . money_format('%n', $dedutot) . '</td></tr>'."\n";
				}
			
				echo '			<tr><td>Area</td><td>Presupuesto</td><td>Estatus</td><td>Refacciones</td></tr>';
				mysql_data_seek($matriz5,0);
				while($estat = mysql_fetch_array($matriz5)) {
					if(($estat['sub_estatus'] > 103 && $estat['sub_estatus'] <= 112) || $estat['sub_estatus'] == 121) { $tipo_prod =  'proceso'; } else { $tipo_prod = 'presupuestos'; }
					if($_SESSION['codigo'] < '2000') {
						echo '			<tr><td>' . constant('NOMBRE_AREA_' . strtoupper($estat['sub_area'])) . '</td>';
						if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $estat['sub_aseguradora'] < 1)) {
							echo '<td style="text-align:right;">' . money_format('%n', $estat['sub_presupuesto']) . '</td>';
						} else {
							echo '<td>&nbsp;</td>';
						}
						echo '<td>';
						echo '<a href="' . $tipo_prod . '.php?accion=consultar&orden_id=' . $orden_id . '#' . $estat['sub_orden_id'] . '">' . constant('SUBORDEN_ESTATUS_' . $estat['sub_estatus']) . '</a>';
						echo '</td><td>';
						//if($orden['orden_estatus'] < 2 || $orden['orden_estatus'] == 17 || $orden['orden_estatus'] == 22 || $orden['orden_estatus'] == 24 || $orden['orden_estatus'] == 97 || $orden['orden_estatus'] == 98 || $_SESSION['codigo'] >= '2000') {
							//echo '<img src="idiomas/' . $idioma . '/imagenes/sin-tareas.png" alt="" border="">';
						//} else {
						echo '<a href="refacciones.php?accion=gestionar&orden_id=' . $orden_id . '">' . constant('ALARMA_' . $estat['sub_refacciones_recibidas']) . '</a>';
						//}
						echo '</td></tr>'."\n";
					} else {
						//$usuaseg = $_SESSION['aseg'];
						//echo $estat['sub_orden_id'] . ' -> ' . $usuaseg . '<br>'."\n";
						if($_SESSION['aseg'] == $estat['sub_aseguradora']) {
							echo '			<tr><td>' . constant('NOMBRE_AREA_' . strtoupper($estat['sub_area'])) . '</td>';
							if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $estat['sub_aseguradora'] < 1)) {
								echo '<td style="text-align:right;">' . money_format('%n', $estat['sub_presupuesto']) . '</td>';
							} else {
								echo '<td>&nbsp;</td>';
							}
							echo '<td>';
							echo constant('SUBORDEN_ESTATUS_' . $estat['sub_estatus']);
							echo '</td><td>';
							echo constant('ALARMA_' . $estat['sub_refacciones_recibidas']);
							echo '</td></tr>'."\n";
						}
					}
				}
			}
			echo '			</table>
		</td><td style="width:5px;"></td><td style="vertical-align:top;">
			<table cellspacing="0" cellpadding="2" border="1" width="100%">
				<tr><td colspan="3" style="text-align:center;">' . $vehiculo['frontal'] . '</td></tr>'."\n";

// ------ Se valida acceso super restringido para Operarios ------
			if($_SESSION['codigo'] < '60' || $_SESSION['codigo'] > '75' || ($restrictotoper != 1 && ($_SESSION['codigo'] >= '60' && $_SESSION['codigo'] <= '75'))) {
				if($orden['orden_servicio']=='4' || $NumSisExt == 1) {
					echo '				<tr><td>' . $lang['NumSin'] . '</td><td>' . $lang['NumPoliza'] . '</td><td>Editar?</td></tr>'."\n";
					foreach($reporte as $rs => $rn) {
						if(($rn != '0' && $rn != '') || $NumSisExt == 1) {
							echo '				<tr><td>' . $rn . '</td><td>' . $poliza[$rs] . '</td><td>';
							if(validaAcceso('1040075', $dbpfx) || ($solovalacc != 1 && ($_SESSION['rol03'] == '1' || $_SESSION['rol05'] == '1' || $_SESSION['rol12'] == '1'))) {
								echo '<a href="ordenes.php?accion=cambiarep&orden_id=' . $orden_id . '&reporte=' . $rn . '&poliza=' . $poliza[$rs] . '&deducible=' . $pdedu[$rn]['monto'] . '"><img src="idiomas/' . $idioma . '/imagenes/prod-editar.png" alt="' . $lang['Cambio de números'] . '" title="' . $lang['Cambio de números'] . '" width="23" height="23"></a>';
							}
							echo '</td></tr>'."\n";
						}
					}
				}
				echo '			</table>'."\n";
				echo '			<table cellspacing="0" cellpadding="2" border="1">'."\n";
				echo '				<tr><td>Tipo de servicio: </td><td>' . constant('ORDEN_SERVICIO_' .$orden['orden_servicio']) . '</td></tr>'."\n";
				if($orden['orden_servicio']=='2') { echo '				<tr><td>OT reclamada en Garantía: </td><td><a href="ordenes.php?accion=consultar&orden_id=' . $orden['orden_garantia'] . '" target="_blank">' . $orden['orden_garantia'] . '</a></td></tr>'."\n";}
				echo '				<tr><td>Categoría de servicio: </td><td>';
				// $funnum = 1040080;
				$retorno = 0; $retorno = validaAcceso('1040080', $dbpfx);
				if (($retorno == '1' || ($solovalacc != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol06'] == '1'))) && $confcs == '1' ) {
					echo '					<form action="ordenes.php?accion=catserv" method="post" enctype="multipart/form-data">'."\n";
					echo '					<select name="categoria" size="1">'."\n";
					for($se = 1; $se <= $numcatsservicio; $se++) {
						echo '			<option value="' . $se . '"';
						if($se == $orden['orden_categoria']) { echo ' selected="selected" '; }
						echo '>' . constant('CATEGORIA_DE_REPARACION_' .$se) . '</option>'."\n";
					}
					echo '					</select><input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="submit" value="Enviar" /></form>'."\n";
				} else {
					echo constant('CATEGORIA_DE_REPARACION_' .$orden['orden_categoria']);
				}
				echo '</td></tr>
				<tr><td>' . constant('UNIDAD_' . $orden['orden_metrico']) . '</td><td>' . $orden['orden_odometro'] . '</td></tr>
				<tr><td>Ubicación: </td><td>';
				if($currarea > 0) {
					echo $orden['orden_ubicacion'] . ' - ' . constant('NOMBRE_AREA_' . $currarea);
				} else {
					echo $orden['orden_ubicacion'];
				}
				if($cambubic == '1' && $orden['orden_estatus'] <= '39' && $orden['orden_estatus'] != '16') {
					if (validaAcceso('1040055', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1'))) {
						echo '					<form action="ordenes.php?accion=cambiaubic" method="post" enctype="multipart/form-data" name="ubic">'."\n";
						if(count($ubicaciones) == '1') {
							echo '						<input type="hidden" name="nubic" value="' . $ubicaciones[0] . '" />'."\n";
							echo '						<input type="submit" value="Cambiar a ' . $ubicaciones[0] . '" />'."\n";
						} else {
							echo '						<select name="nubic" onchange="document.ubic.submit()";>'."\n";
							foreach($ubicaciones as $uk => $uv) {
								echo '							<option value="' . $uv . '" ';
								if($orden['orden_ubicacion'] == $uv) { echo ' selected '; }
								echo '>' . $uv . '</option>'."\n";
							}
							echo '						</select>'."\n";
						}
						echo '					<input type="hidden" name="orden_id" value="' . $orden_id . '" /></form>'."\n";
					}
				}
			
				echo '</td></tr>'."\n";
				echo '				<tr><td>Torre: </td><td>' . $orden['orden_torre'] . '</td></tr>'."\n";
				echo '				<tr><td>' . $lang['Llegó en Grua'] . '</td><td>';
				if($orden['orden_grua'] == 1) { echo $lang['Sí']; }
				elseif($orden['orden_grua'] == 2) { echo $lang['No']; }
				else { echo $lang['No identificado']; }
				echo '</td></tr>'."\n";
				echo '				<tr><td>Fecha de Recepción: </td><td>' . $orden['orden_fecha_recepcion'] . '</td></tr>'."\n";
				if($fcompcr == 1) {
					// ------------  Habilitar el despliegue de fecha compromiso de Centro de Reparación ( que es diferente de la 
					// ------------  "Fecha Promesa de Entrega" requerida por aseguradoras  
					echo '				<tr><td>Fecha Compromiso Taller: </td><td>'."\n";
					echo $orden['orden_fecha_compromiso_de_taller'];
					$retorno = 0; $retorno = validaAcceso('1040010', $dbpfx);
					if ($retorno == '1') {
						require_once("calendar/tc_calendar.php");
						$compta = strtotime($orden['orden_fecha_compromiso_de_taller']);
						echo '					<form action="ordenes.php?accion=comptaller" method="post" enctype="multipart/form-data">'."\n";
						//instantiate class and set properties
						$myCalendar = new tc_calendar("fecomp", true);
						$myCalendar->setPath("calendar/");
						$myCalendar->setIcon("calendar/images/iconCalendar.gif");
						$myCalendar->setDate(date("d", $compta), date("m", $compta), date("Y", $compta));
						//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
						$myCalendar->disabledDay("sun");
						$myCalendar->setYearInterval(2013, 2020);
						$myCalendar->setAutoHide(true, 5000);

						//output the calendar
						$myCalendar->writeScript();
						echo '					<input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="submit" value="Enviar" /></form>'."\n";
					}
					echo '</td></tr>'."\n";
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
					$fpe = $orden['orden_fecha_promesa_de_entrega'];
				} elseif($orden['orden_fecha_promesa_de_entrega']!='') {
					$fpe = $orden['orden_fecha_promesa_de_entrega'];
				} else {
					$alerta_fecha = '';
					$fpe = '<span style="color:red; font-weight:bold;">Sin Fecha</span>';
				}
				echo '			 	<tr><td>Promesa de entrega: </td><td class="' . $alerta_fecha . '">' . $fpe;
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
						$myCalendar->setIcon("calendar/images/iconCalendar.gif");
						$myCalendar->setDate(date("d", $fprom1), date("m", $fprom1), date("Y", $fprom1));
						//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
						$myCalendar->disabledDay("sun");
						$myCalendar->setYearInterval(1999, 2020);
						$myCalendar->setAutoHide(true, 5000);

						//output the calendar
						$myCalendar->writeScript();
						echo '					<input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="submit" value="Enviar" /></form>'."\n";
					} 
				}
				echo '</td></tr>'."\n";
				if($valautcap == 1) {
					echo '			 	<tr><td>Fecha de valuación: </td><td>';
					$cont = 0;
					if(validaAcceso('1040130', $dbpfx) == '1'){
						echo '<form action="ordenes.php?accion=fecha_valuacion" method="post" enctype="multipart/form-data">'."\n";
					}

					foreach( $info_valuacion as $siniestro => $fecha){
						if($fecha == '' || $fecha == '0000-00-00 00:00:00'){
							echo '<span style="color:red; font-weight:bold;">Sin Fecha</span>';
						} else{	
							//output the calendar
							if(validaAcceso('1040130', $dbpfx) == '1'){
								require_once("calendar/tc_calendar.php");
								$myCalendar = '';
								$fecha_cal = strtotime($fecha);
								//instantiate class and set properties
								$myCalendar = new tc_calendar('fecha_valuacion_' . $cont, true);
								$myCalendar->setPath("calendar/");
								$myCalendar->setIcon("calendar/images/iconCalendar.gif");
								$myCalendar->setDate(date("d", $fecha_cal), date("m", $fecha_cal), date("Y", $fecha_cal));
								$myCalendar->disabledDay("sun");
								$myCalendar->setYearInterval(1999, 2020);
								$myCalendar->setAutoHide(true, 5000);
								if($cont > 0){ echo '<br><br>'; }
								echo '
								Sin: <b>' . $siniestro .  '</b> - ' . $fecha . '<br>
								<input type="hidden" name="siniestro[' . $cont . ']" value="' . $siniestro . '">
								<input type="hidden" name="orden_id" value="' . $orden_id . '">'."\n";
								$myCalendar->writeScript();
								$cont++;
							} else{
								echo '
								Sin: <b>' . $siniestro .  '</b> - ' . $fecha . '<br>'."\n";
							}
								
							}
						}
						if(validaAcceso('1040130', $dbpfx) == '1'){
							echo '
								&nbsp
								<input class="btn btn-success" type="submit" value="Enviar" />
							</form>'."\n";
						}
					
				}

				// ------ Prersentación de Fecha de termino de producción ------
				echo '			 	<tr><td>' . $lang['TermProd'] . '</td><td>' . $orden['orden_fecha_proceso_fin'];
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
					$myCalendar->setIcon("calendar/images/iconCalendar.gif");
					$myCalendar->setDate(date("d", $ffpro1), date("m", $ffpro1), date("Y", $ffpro1));
					//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
					$myCalendar->disabledDay("sun");
					$myCalendar->setYearInterval(2013, 2022);
					$myCalendar->setAutoHide(true, 5000);
					//output the calendar
					$myCalendar->writeScript();
						echo '					<input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="submit" value="Enviar" /></form>'."\n";
				}
				echo '</td></tr>'."\n";
				echo '			 	<tr><td>Ultimo movimiento: </td><td>' . $orden['orden_fecha_ultimo_movimiento'] . '</td></tr>'."\n";
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
				echo '		 		<tr><td>Vehículo Lavado?: </td><td style="text-align:center;">' . constant('ALARMA_' . $orden['orden_lavado']) . '</td></tr>
		 		<tr><td>Fecha Acordada de Entrega: </td><td class="' . $alerta_acordada . '">' . $orden['orden_fecha_acordada'] . '</td></tr>
		 		<tr><td>Fecha Entregado: </td><td>'."\n";
			 	echo $orden['orden_fecha_de_entrega'] . '<br>';
				$retorno = 0; $retorno = validaAcceso('1040060', $dbpfx);
				if ($retorno == '1') {
					$fent1 = strtotime($orden['orden_fecha_de_entrega']);
					require_once("calendar/tc_calendar.php");
					echo '		 			<form action="ordenes.php?accion=fentrega" method="post" enctype="multipart/form-data">'."\n";
					//instantiate class and set properties
					$myCalendar = new tc_calendar("fent", true);
					$myCalendar->setPath("calendar/");
					$myCalendar->setIcon("calendar/images/iconCalendar.gif");
					$myCalendar->setDate(date("d", $fent1), date("m", $fent1), date("Y", $fent1));
					//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
					$myCalendar->disabledDay("sun");
					$myCalendar->setYearInterval(2013, 2020);
					$myCalendar->setAutoHide(true, 5000);

					//output the calendar
					$myCalendar->writeScript();
					echo '					<input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="submit" value="Enviar" /></form>'."\n";
				} 
		 		echo '</td></tr>'."\n";
				// ----------> Validar acceso a cálculo de Destajos		 	$funnum = 1135050;
			 	$retorno = 0; $retorno = validaAcceso('1135050', $dbpfx);
			 	if($_SESSION['codigo'] <= '12' || $retorno == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1'))) {
			 		echo '<tr><td colspan="2" align="center"><a href="destajos.php?accion=cesta&orden_ver=' . $orden_id . '">Ver Pago de Destajos</a></td></tr>'."\n";
		 		}
			 	if(validaAcceso('1040125', $dbpfx) == 1 || $infomon == 1 || $_SESSION['codigo'] <= '12' || ($solovalacc != 1 && ($_SESSION['rol12']=='1'))) {
					$preg6 = "SELECT * FROM " . $dbpfx . "facturas_por_cobrar WHERE orden_id = '$orden_id' AND fact_cobrada < '2'";
					$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección! " . $preg6);
					echo '		 		<tr><td colspan="2"><table cellspacing="0" cellpadding="2" border="1" class="centrado" width="100%"><tr><td>Documento</td><td>Número</td><td>Monto</td><td>Cobrado?</td></tr>'."\n";
					while($fact = mysql_fetch_array($matr6)) {
						echo '<tr><td>';
						if($fact['fact_tipo']==1) { echo'Factura'; } else { echo 'Remisión';}
						echo '</td><td>';
						echo $fact['fact_num'];
						echo '</td><td>';
						if($infomon == 1 || $_SESSION['codigo'] <= '12' || $_SESSION['rol12']=='1') {
							echo number_format($fact['fact_monto'], 2);
						}
						echo '</td><td>';
						if($fact['fact_cobrada']==1) { echo 'Sí'; } else { echo 'No';}
						echo '</td></tr>'."\n";
					}
				
					echo '		 				</table></td></tr>'."\n";
				}
			}
			echo '		 	</table></td></tr>'."\n";
				// ----------------   Comentarios y Bitácora --------------------------------------------
			
				$per_generales = validaAcceso(1040100, $dbpfx);
				$per_seguimiento = validaAcceso(1040105, $dbpfx);
				$per_bitacora = validaAcceso(1040110, $dbpfx);
				if(($confolio == '1' && $orden['orden_estatus'] != '17') || ($confolio != '1')) {
					echo '			<tr><td colspan="3" style="vertical-align:top;">
				<div id="navegador"><a name="com"></a><br>
					<ul>'."\n";
					if($per_generales == 1 || ($_SESSION['codigo'] < '2000' && $_SESSION['codigo'] != '60' && $_SESSION['codigo'] != '75' && $_SESSION['codigo'] != '70')) {
						echo '
						<li'."\n";
						if($com == '1' || $com == ''){ echo ' class="activa"'; }
							echo '><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '&com=1#com">Comentarios Generales</a></li>'."\n";
					}
					if($per_seguimiento == 1 || $_SESSION['rol02']=='1' ||  $_SESSION['rol06']=='1'){ 
						echo '
						<li'."\n";	
						if($com == '2') { echo ' class="activa"'; }
							echo '><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '&com=2#com">Seguimiento</a></li>'."\n";
					}
					if($per_bitacora || $_SESSION['rol02']=='1'){ 
						echo '
						<li'."\n";
						if($com == '3') { echo ' class="activa"'; }
							echo '><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '&com=3#com">Bitácora</a></li>'."\n";
					}
					echo '
					</ul>
				</div></td></tr>'."\n";
					echo '			<tr><td colspan="3" style="border-width:1px; border-style:solid; width:650px;">';
					if($com == '') {
						$com = '1';
					}	
					if($com == '1' && ($per_generales == 1 || ($_SESSION['codigo'] < '2000' && $_SESSION['codigo'] != '70' && $_SESSION['codigo'] != '75'))) {
						$pregunta3 = "SELECT c.fecha_com, c.fecha_visto, c.usuario, c.comentario, c.interno, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.orden_id = '$orden_id' AND c.usuario = u.usuario AND (c.interno = '0' OR c.interno = '3') ";
						if($_SESSION['codigo'] == '60') {
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
					echo '</td></tr>'."\n";
				}
//------------------------------------------------------------------------------------------

// ------  Acciones ------
				echo '			</table>'."\n";
				echo '		</td><td valign="top"><div align="center">
			<h3>Acciones</h3>'."\n";
				$orden_estatus = $orden['orden_estatus'];
				$pregunta4 = "SELECT * FROM " . $dbpfx . "acciones WHERE accion_estatus = '".$orden['orden_estatus']."'";
				$matriz4 = mysql_query($pregunta4) or die("ERROR: Fallo selección! " . $pregunta4);
				$num_acc = mysql_num_rows($matriz4);
				if ($num_acc>0) {
					while ($acciones = mysql_fetch_array($matriz4)) {
						$rol = $acciones['accion_codigo'];
						if (($acciones['accion_codigo']=='99') || ($_SESSION[$rol]==1)) {
							echo '				<a href="' . $acciones['accion_url'];
							if($orden_estatus == 17  && $confolio == '1') {
								echo $orden['oid'];
							} else {
								echo $orden['orden_id'];
							}
							echo '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/' . $acciones['accion_descripcion'] . '</a><br>'."\n";
						}
					}
				}
				if ($orden_estatus>=1 && $orden_estatus<=89 && $orden_estatus != 17 && ($_SESSION['rol05']==1 || $_SESSION['rol06']==1)) {
					echo '				<a href="presupuestos.php?accion=adicional&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/agregar-tarea.png" alt="Agregar otra tarea" title="Agregar otra tarea"></a><br>'."\n";
				}
				if(($orden_estatus == 17  && $confolio != '1') || $orden_estatus != 17) {
					echo '				<a href="documentos.php?accion=listar&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/documento.png" alt="Mostrar documentos" title="Mostrar documentos"></a><br>'."\n";
					if($_SESSION['codigo'] < '2000') {
						echo '				<a href="comentarios.php?accion=agregar&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/agregar-comentarios.png" alt="Agregar Comentarios" title="Agregar Comentarios"></a><br>'."\n";
						//echo '				<a href="documentos.php?accion=agregar&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/agregar-documento.png" alt="Agregar nuevo documento" title="Agregar nuevo documento"></a><br>'."\n";
					}
				}

				// ------ Agregar las acciones fijas disponibles en todos los estatus de la OT -----  
				if(file_exists('particular/textos/acciones.php')) {
					include('particular/textos/acciones.php');
				}
				// ------

				echo '	</div></td>'."\n";
				echo '	<td style="vertical-align:top; text-align:left; width:300px;">'."\n";
				if($mensjint == '1') {
      			echo '				<form action="comentarios.php?accion=visto" method="post" enctype="multipart/form-data">'."\n";

				// --- sección de boletines ---
      			include("parciales/boletines_pendientes.php");

				// ----------------------- Sección de mensajes intenos -------------------------
      			echo '
							<table width="100%">
								<tr>
									<td style="border-width:1px; border-style:solid; text-align:left;">Tus mensajes no leidos:<br>'."\n";
					
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
								echo '				<button name="visto" value="' . $comen['bit_id'] . '" type="submit">' . $lang['Visto'] . '</button>'."\n";
							}
							echo '				<input type="hidden" name="orden_id" value="' . $comen['orden_id'] . '" />
				<input type="hidden" name="pagina" value="ordenes.php" />'."\n";
						}
						echo '				</p>'."\n";				
						if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
					}
					echo '				</td></tr></table></form>'."\n";
				}
				echo '			</td></tr>'."\n";
				echo '</table>'."\n";
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
		
		$cont = 0;
		foreach($siniestro as $key){
			
			echo 'key ' . $key . '<br>';
			$fecha = 'fecha_valuacion_' . $cont;
			
			echo 'Siniestro: ' . $key . '<br> fecha: ' . $$fecha . '<br>';
			$fecha_valu = date('Y-m-d H:i:s', strtotime($$fecha));
			$parametros=" orden_id = '" . $orden_id . "' AND sub_reporte = '" . $key . "'";
			$sql_data_array = array('sub_fecha_valaut' => $fecha_valu);
			
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
			bitacora($orden_id, 'Cambio de fecha de Valuación del siniestro ' . $key, $dbpfx, 'Cambio de fecha de Valuación', '0');
			
			$cont++;
		}
		
	} else {
		$_SESSION['msjerror'] = 'El acceso a esta función no fue autorizado';
	}
	$_SESSION['msjerror'] = 'Se actualizó la fecha de Valuación';
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	
}

elseif ($accion==="listar") {
	
	$funnum = 1040015;
	
//	echo 'Estamos en la sección listar. Cliente: ' . $cliente_id . ' Vehiculo: ' . $vehiculo_id;
	$placas=preparar_entrada_bd($placas);
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
	if (($placas!='') || ($torre!='') || ($tipo!='') || ($cliente_id!='')) {
		$error = 'no'; $mensaje ='';
		$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE ";
		if ($placas) {$pregunta .= "orden_vehiculo_placas LIKE '%$placas%' ";}
		if (($placas) && ($torre)) {$pregunta .= "AND orden_torre = '$torre' AND orden_estatus < '90' ";}
			elseif ($torre) {$pregunta .= "orden_torre = '$torre' AND orden_estatus < '90' ";}
		if (($torre) && ($tipo)) {$pregunta .= "AND orden_vehiculo_tipo LIKE '%$tipo%' ";} 
			elseif ($tipo) {$pregunta .= "orden_vehiculo_tipo LIKE '%$tipo%' ";}
		if ($cliente_id) { $pregunta .= "orden_cliente_id = '$cliente_id' "; }
	} elseif ($siniestro!='') {
		$pregunta = "SELECT s.sub_reporte, o.* FROM " . $dbpfx . "ordenes o, " . $dbpfx . "subordenes s WHERE s.sub_reporte LIKE '%$siniestro%' AND o.orden_id = s.orden_id GROUP BY o.orden_id ORDER BY o.orden_id";
	} else {
		$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '0'";
	}
   $matriz = mysql_query($pregunta) or die("ERROR: Fallo selección! ". $pregunta);
	$num_cols = mysql_num_rows($matriz);
//		echo $pregunta;
		if ($num_cols>0) {
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
				echo '							</td><td rowspan="4">
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
							<tr class="claro"><td>' . $lang['Placa'] . '</td><td>' . $orden['orden_vehiculo_placas'] . '</td></tr>
							<tr class="obscuro"><td>Estatus</td><td>' . constant('ORDEN_ESTATUS_' . $orden['orden_estatus']) . '</td></tr>'."\n";
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
	if (($placas!='') || ($tipo!='') || ($cliente_id!='') || ($previa_id!='')) {
		$error = 'no';
		$pregunta = "SELECT * FROM " . $dbpfx . "previas p, " . $dbpfx . "vehiculos v WHERE ";
		if (($placas!='') || ($tipo!='')) {
			if ($placas) {$pregunta .= "v.vehiculo_placas LIKE '%$placas%' ";}
			if (($placas) && ($tipo)) {$pregunta .= "AND v.vehiculo_tipo LIKE '%$tipo%' ";} 
			elseif ($tipo) {$pregunta .= "v.vehiculo_tipo LIKE '%$tipo%' ";}
		} elseif($previa_id!='') {
			$pregunta .= "p.previa_id = '$previa_id' ";
		} else {
			$pregunta .= "p.previa_cliente_id = '$cliente_id' ";
		}
		$pregunta .= "AND p.previa_vehiculo_id = v.vehiculo_id";
//		if ($cliente_id) { $pregunta .= "p.previa_cliente_id = '$cliente_id' "; }
//		echo $pregunta;
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección! ".$pregunta);
		$fprev = mysql_num_rows($matriz);
	} 
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
	
	if(validaAcceso('1040020', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol05']=='1'))) {
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
	
	if(validaAcceso('1040020', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol05']=='1'))) {
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

// ------ Validación si hay refacciones recibidas antes de permitir cambiar estatus   ------
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

	if($oid == '' && $id != 17){

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
	
	if (validaAcceso('1040030', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol06']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol04']=='1'))) {
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
			$sql_data_array = [
				'sub_estatus' => '190',
				'sub_refacciones_recibidas' => '0'
			];
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
		$parametros="orden_id = '" . $orden_id . "'";
		$sql_data_array = array('orden_ubicacion' => $nubic);
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Cambio de Ubicación a ' . $nubic, $dbpfx, 'Cambio de Ubicación a ' . $nubic, '0');
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
	}
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
}

elseif ($accion==='otcambiar') {
	
		$preg1 = "SELECT orden_vehiculo_placas, orden_asesor_id, orden_servicio, orden_categoria, orden_sitio_ingreso, orden_grua, orden_garantia, orden_torre, orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matr1 = mysql_query($preg1) or die('Error en selección de Ordenes! '.$preg1);
		$ord = mysql_fetch_array($matr1);
		if($ord['orden_servicio'] == '4') {
			$preg3 = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190'";
			$matr3 = mysql_query($preg3) or die('Error en selección de Tareas! '.$preg3);
			$otaseg = 0;
			while ($sub = mysql_fetch_array($matr3)) {
				if($sub['sub_aseguradora'] > 0) { $otaseg = 1; }
			}			
		}
		
		echo '			<form action="ordenes.php?accion=otcambiadat" method="post" enctype="multipart/form-data">'."\n";
		echo '			<table cellpadding="0" cellspacing="0" border="0" class="agrega">
				<tr class="cabeza_tabla"><td colspan="2">' . $lang['Modificar Datos OT'] . '</td></tr>'."\n";
		if($ord['orden_estatus'] == '17') {
			echo '				<tr><td>' . $lang['Cambio de Placas'] . '</td><td style="text-align:left;"><input type="text" name="placas" value="' . $ord['orden_vehiculo_placas'] . '" size="6" /></td></tr>'."\n";
		}
		echo '				<tr><td>' . $lang['Seleccionar Asesor de Servicio'] . '</td><td style="text-align:left;"><select name="asesor">';
		$preg2 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE acceso = '0' AND codigo = '30' AND activo = '1' ORDER BY nombre";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Asesores! ".$preg2);
		while ($usu = mysql_fetch_array($matr2)) {
			echo '					<option value="' . $usu['usuario'] . '"';
			if($usu['usuario'] == $ord['orden_asesor_id']) { echo ' selected '; }
			echo '>' . $usu['nombre'] . ' ' . $usu['apellidos'] . '</option>'."\n";
		}
		echo '</select></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Tipo de Servicio'] . '</td><td style="text-align:left;"><select name="tipo">'."\n";
		if($otaseg == 1) { $tsi = 4; } else { $tsi = 1; }
		for($i=$tsi;$i<5;$i++) {
			echo '					<option value="' . $i . '"';
			if($i == $ord['orden_servicio']) { echo ' selected '; }
			echo '>' . $lang['os'.$i] . '</option>'."\n";
		}
		echo '</select>';
		if($otaseg == 1) {
			echo '<span style="background-color:yellow;"><br>Una Orden que tiene trabajos de Aseguradora o Convenio<br>no se puede cambiar directamente a Particular o Garantía,<br>por favor vaya a la vista principal y cambie cada uno de<br>los siniestros a Trabajo Particular.<br>Gracias!</span>';
		}
		echo '</td></tr>'."\n";
		echo '				<tr><td>' . $lang['Garantía'] . '</td><td style="text-align:left;"><input type="text" name="garantia" value="' . $ord['orden_garantia'] . '" size="6" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Categoría de Servicio'] . '</td><td style="text-align:left;"><select name="categoria">'."\n";
		for($se=1;$se<=4;$se++){
			echo '					<option value="' . $se . '"';  
			if($ord['orden_categoria'] == $se) { echo ' selected="selected" '; }
			echo '>' . constant('CATEGORIA_DE_REPARACION_' .$se) . '</option>'."\n";
		}
		echo '				</select></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Grua'] . '</td><td style="text-align:left;"><select name="grua">'."\n";
		$grua = array('No definido','Sí','No');
		for($i=0;$i<3;$i++){
			echo '					<option value="' . $i . '"';  
			if($ord['orden_grua'] == $i) { echo ' selected="selected" '; }
			echo '>' . $grua[$i] . '</option>'."\n";
		}
		echo '				</select></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Torre'] . '</td><td style="text-align:left;"><input type="text" name="torre" value="' . $ord['orden_torre'] . '" size="10" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Recibido en'] . '</td><td style="text-align:left;"><select name="sitio">'."\n";
		foreach($ubicaciones as $uk => $uv) {
			echo '					<option value="' . $uv . '" ';
			if($orden['orden_sitio_ingreso'] == $uv) { echo 'selected '; }
			echo '>' . $uv . '</option>'."\n";
		}
		echo '				</select></td></tr>'."\n";
		echo '				<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /><input type="hidden" name="orden_id" value="' . $orden_id . '" /></td></tr>'."\n";
		echo '				<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
		echo '			</table>'."\n";
		echo '			</form>'."\n";
		echo '			<div class="control" style="vertical-align:top;">'."\n";
		echo '				<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar-h.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo" border="1" ></a>'."\n";
		echo '				<a href="ingreso.php?accion=consultar&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/hoja-ingreso-h.png" alt="Hoja de Ingreso" title="Hoja de Ingreso"></a>'."\n";
		echo '				<a href="ingreso.php?accion=inventario&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/inventario-h.png" alt="Inventario de Ingreso" title="Inventario de Ingreso"></a>'."\n";
		echo '<a href="ingreso.php?accion=cartaaxa&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/carta-axa-h.png" alt="" /></a>';
		echo '				<a href="presupuestos.php?accion=crear&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/crear-presupuesto-h.png" alt="Agregar otra tarea" title="Agregar otra tarea"></a>'."\n";
//		echo '				<a href="entrega.php?accion=salida&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/salida-con-rp-h.png" alt="Entrega con Vale de Refacciones" title="Entrega con Vale de Refacciones"></a>'."\n";
		echo '				<a href="ordenes.php?accion=perdida&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/pago-h.png" alt="Pérdida Total o Pago de daños" title="Pérdida Total o Pago de daños"></a>'."\n";
		echo '				<a href="ordenes.php?accion=cancelar&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/cancelar-h.png" alt="Cancelar Orden de Trabajo" title="Cancelar Orden de Trabajo"></a><br>'."\n";
		echo '				<a href="proceso.php?accion=imprimir&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/imprimir-sot-h.png" alt="Imprimir Codigos de Barras para Operadores" title="Imprimir Codigos de Barras para Operadores"></a>'."\n";
		echo '				<a href="documentos.php?accion=avances&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/avance-reparacion-h.png" alt="Subir imágenes de Avance de Reparación" title="Subir imágenes de Avance de Reparación"></a>'."\n";
		echo '				<a href="entrega.php?accion=listado&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/imprimir-recibo-h.png" alt="Lista de entrega para facturacion" title="Lista de entrega para facturacion"></a>'."\n";
		echo '				<a href="entrega.php?accion=garantia&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/formato-de-entrega-h.png" alt="Formato de Entrega" title="Formato de Entrega"></a>'."\n";
		echo '				<a href="entrega.php?accion=cobros&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/cobro-registrar-h.png" alt="Registrar Cobros" title="Registrar Cobros"></a>'."\n";
		echo '				<a href="factura.php?accion=consultar&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/facturas-h.png" alt="Crear Factura" title="Crear Factura"></a>'."\n";
		echo '				<a href="nota-de-credito.php?accion=consultar&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/es_MX/imagenes/nota-de-credito-h.png" alt="Crear Nota de Credito" title="Crear Nota de credito"></a>'."\n";
		echo '			</div>'."\n";
}

elseif ($accion==='otcambiadat') {
	
	if (validaAcceso('1120005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1' || $_SESSION['rol02'] == '1'))) {
		// Acceso permitido
	} else {
		$_SESSION['msjerror'] = $lang['Acceso no autorizado']; 
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
	$error = 'no'; $msj = '';
	if($placas != '') {
		$placas = strtoupper(limpiarString($placas));
		$preg0 = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_placas LIKE '%$placas%'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección! ". $preg0);
		$fila0 = mysql_num_rows($matr0);
		if($fila0 < 1) {
			$error = 'si'; $msj .= 'No se encontró ningún vehiculo con las placas ' . $placas . '<br>';
		}
	}

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
		if($fila0 > 0) {
			$veh = mysql_fetch_array($matr0);
			$sql_data_array['orden_vehiculo_id'] = $veh['vehiculo_id'];
			$sql_data_array['orden_cliente_id'] = $veh['vehiculo_cliente_id'];
			$sql_data_array['orden_vehiculo_marca'] = $veh['vehiculo_marca'];
			$sql_data_array['orden_vehiculo_tipo'] = $veh['vehiculo_tipo'];
			$sql_data_array['orden_vehiculo_color'] = $veh['vehiculo_color'];
			$sql_data_array['orden_vehiculo_placas'] = $veh['vehiculo_placas'];
		}
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Cambio Gral Asesor ' . $asesor . ', Tipo de servicio: ' . $tipo . ', OT Ref Garantía: ' . $garantia . ', Categoría: ' . $categoria . ' Grua: ' . $grua . ' e Ingreso a ' . $sitio . '.', $dbpfx);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror'] = $msj;
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
}

elseif ($accion==='cambiarep') {
	
	$funnum = 1040075;

	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || ($solovalacc != 1 && ($_SESSION['rol03'] == '1' || $_SESSION['rol05'] == '1' || $_SESSION['rol12'] == '1'))) {
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

	if (validaAcceso('1040075', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol03'] == '1' || $_SESSION['rol05'] == '1' || $_SESSION['rol12'] == '1'))) {
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
	if ($retorno == '1' || ($solovalacc != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol05'] == '1'))) {
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
