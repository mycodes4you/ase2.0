<?php
foreach($_POST as $k => $v){$$k=$v; } // echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k=$v;}  // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/entrega.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

//  ----------------  Obtener datos de aseguradoras   -------------------

		$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_nic, aseguradora_alta, aseguradora_v_email, aseguradora_razon_social, prov_def, prov_dde, aseguradora_saltapres FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		while ($aseg = mysql_fetch_array($arreglo)) {
			$ase[$aseg['aseguradora_id']]['logo'] = $aseg['aseguradora_logo'];
			$ase[$aseg['aseguradora_id']]['nic'] = $aseg['aseguradora_nic'];
			$ase[$aseg['aseguradora_id']]['saltapres'] = $aseg['aseguradora_saltapres'];
			$ase[$aseg['aseguradora_id']]['autosurtido'] = $aseg['autosurtido'];
			$asenoti[$aseg['aseguradora_id']]['alta'] = $aseg['aseguradora_alta'];
			$asenoti[$aseg['aseguradora_id']]['email'] = $aseg['aseguradora_v_email'];
			$asenoti[$aseg['aseguradora_id']]['razon'] = $aseg['aseguradora_razon_social'];
		}
		$ase[0]['logo'] = 'imagenes/logo-particular.png';
		$ase[0]['nic'] = 'Particular';
		$ase[0]['autosurtido'] = 1;
//  ----------------

if (($accion==='procesar') || ($accion==='registrar') || ($accion==='imprimir') || ($accion==='lavado') || ($accion==='liberar') || ($accion==='asigna') || ($accion==='seguimiento') || ($accion==='garantia') || ($accion==='cobros') || ($accion==='procesacobro') || ($accion==='borrafactura') || ($accion==='cierra') || ($accion==='transito') || ($accion==='deducible')) { 
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}

if ($accion==="seguimiento") {
	$funnum = 1030000;
/*	$codigos = explode(' ', $codigo);
	$sub_orden_id = $codigos[1];
	$orden_id = $codigos[0];
*/	
	$sub_orden_id = $codigo;
/*	if($sub_orden_id=='' || $orden_id=='') {
		redirigir('entrega.php?accion=suborden');
	}
*/	
	$pregunta = "SELECT orden_id, sub_descripcion, sub_refacciones_recibidas, sub_estatus, sub_orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	$sub = mysql_fetch_array($matriz);
	$preg0 = "SELECT orden_vehiculo_marca, orden_vehiculo_tipo, orden_vehiculo_color, orden_vehiculo_placas FROM " . $dbpfx . "ordenes WHERE orden_id = '".$sub['orden_id']."'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
	$orden = mysql_fetch_array($matr0);
//	echo 'Estamos en la sección seguimiento';
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';?>
					<div class="row"> <!-box header del título. -->
						<div class="col-md-12">
			  				<div class="content-box-header" style="min-height: 60px;">
								<div class="panel-title">
				  					<span><big><?php echo $lang['Tarea a Evaluar']; ?></big></span>
								</div>
					  		</div>
						</div>
					</div>
					<div class="col-12">
					<!-- Estatus y datos -->
					<div class="col-lg-4 col-md-4 col-sm-6" style="margin: 5px;">
						<div style="box-shadow: 3px 4px 10px 0px #484848; border-top-left-radius: 10px; border-radius: 10px; margin-bottom: 10px;">
							<div class="row" style="margin: 10px;">
								<?php 
									if($mensaje!='') {?>
									<div class="alert alert-danger" role="alert">
										<?php echo $mensaje; ?>
									</div>
								<?php } ?>
								<form action="entrega.php?accion=registrar" method="post" enctype="multipart/form-data">
									<big>Vehiculo <b><?php echo $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color']; ?></b><br>
									<?php echo $lang[' Placas: '] . '<b>' . $orden['orden_vehiculo_placas']; ?></b><br>
									Tarea: <b><?php echo $sub['sub_orden_id']; ?></b> <br>
									De: <b><?php echo $sub['sub_descripcion'];?> </b></big>

									<div class="container" style="text-align: center;">
										<div class="row" style="display: flex">
											<div class="col" style="width: 50%">
												<big><b><?php echo $lang['REPROCESAR']; ?></b></big>
											</div>
											<div class="col" style="width: 50%">
												<big><b><?php echo $lang['APROBAR']; ?></b></big>
											</div>
										</div>
										<div class="row" style="display: flex;">
											<div class="col" style="width: 50%;">
												<input type="image" src="idiomas/<?php echo $idioma; ?>/imagenes/reproceso.png" name="seleccion1" value="1" />
											</div>
											<div class="col" style="width: 50%;">
												<input type="image" src="idiomas/<?php echo $idioma; ?>/imagenes/aprobado.png" name="seleccion2" value="2" />
												<input type="hidden" name="codigo" value="<?php echo $codigo; ?>" />
												<input type="hidden" name="sub_estatus" value="<?php echo $sub['sub_estatus']; ?>">
											</div>
										</div>
									</div>
									<?php 
									if($sub['sub_refacciones_recibidas']==1) { ?>
										<input type="texzt" name="pendientes" value="1">
									<?php } ?> 

								</form>
							</div>
						</div>
					</div>


<?php
}

elseif($accion==="suborden") {
	$funnum = 1030005;
//	echo 'Estamos en la sección suborden';
	echo '	<br><form action="entrega.php?accion=seguimiento" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="650">';
	if($mensaje!='') {
		echo '		<tr class="alerta"><td colspan="2">' . $mensaje . '</td></td></tr>';
	}
	echo '		<tr class="cabeza_tabla"><td colspan="2">'.$lang['Tarea a Evaluar'].'</td></td></tr>
		<tr><td colspan="2" style="text-align:left;">'.$lang['Pasar código de barras de SOT presiona Enter'].'</td></td></tr>
		<tr>
			<td>'.$lang['Código'].'</td>
			<td style="text-align:left;"><input type="text" id="codigo" name="codigo" /></td>
		</tr>
	</table>
	</form>';
}

elseif ($accion==="registrar") {
	$funnum = 1030010;
	$valpres = validaAcceso('1045065', $dbpfx); // Autorización para aprobar presupuestos

//	echo 'Estamos en la sección registrar';
	if (($_SESSION['rol07']=='1' || $_SESSION['rol04']=='1') && $sub_estatus=='111') {
		$mensaje =$lang['Acceso a Supervisor']; }
	elseif(($_SESSION['rol06']=='1') && $sub_estatus=='101') {
		$mensaje =$lang['Acceso a Asesor']; }
	elseif(($_SESSION['rol07']=='1' || $valpres == 1) && $sub_estatus=='127') {
		$mensaje =$lang['Acceso a Jefe de Area']; }
	elseif($_SESSION['rol11']=='1' && $sub_estatus=='121') {
		$mensaje =$lang['Acceso a Calidad']; }
	else {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso no autorizado']);
	}

	$error = 'no'; $mensaje= '';
	$sub_orden_id = $codigo;
	if (isset($seleccion1_x)) { $seleccion = 1; } 
	elseif(isset($seleccion2_x)) { $seleccion = 2; }
	else { $error = "si"; $mensaje = $lang['No hubo selección válida']; }
	$num_cols = 0; 
	if ($sub_orden_id!='') {
		$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de subordenes!");
		$sub = mysql_fetch_array($matriz);
		$reporte = $sub['sub_reporte'];
		$operador = $sub['sub_operador'];
		$num_cols = mysql_num_rows($matriz);
		$orden_id = $sub['orden_id'];
	} else {
		$error = 'si';
		$mensaje .=$lang['no válida la SOT']. $sub_orden_id . '<br>'."\n";
	}

	if($sub['sub_estatus'] == '101' && $sub['sub_aseguradora'] > '0' && $docingreso == '1') {
		$preg0 = "SELECT * FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND doc_archivo LIKE '%-i-%'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Documentos!");
		$fila0 = mysql_num_rows($matr0);
		if($fila0 < '8') { $error = 'si';	$mensaje .=$lang['No hay fotos de ingreso'].'<br>'."\n"; }
		$preg0 = "SELECT * FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND doc_archivo LIKE '%-i-1-%'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Documentos!");
		$fila0 = mysql_num_rows($matr0);
		if($fila0 < '1') { $error = 'si';	$mensaje .=$lang['No hay foto de VIN'].'<br>'."\n"; }
		$preg0 = "SELECT * FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND doc_nombre LIKE '%Orden de Admi%'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Documentos!");
		$fila0 = mysql_num_rows($matr0);
		if($fila0 < '1') { $error = 'si';	$mensaje .=$lang['No agregado Orden de Admisión'].'<br>'."\n"; }
//		$preg0 = "SELECT * FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND doc_nombre LIKE '%Hoja de Da%'";
//		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Documentos!");
//		$fila0 = mysql_num_rows($matr0);
//		if($fila0 < '1') { $error = 'si';	$mensaje .=$lang['No agregado Hoja de Daños'].'<br>'."\n"; }
//		Se elimina la verificación de Hoja de Daños ya que no es emitida en general por todas las aseguradoras
	}

	$preg0 = "SELECT orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Ordenes!");
	$ord = mysql_fetch_array($matr0);

	$npres = 0;
	$preg2 = "SELECT sub_presupuesto FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '" . $reporte . "' AND sub_estatus < '130'";
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Tareas! " . $preg2);
	while($pres = mysql_fetch_array($matr2)) {
		$npres = $npres + $pres['sub_presupuesto'];
	}
	if($sub['sub_estatus'] == 121 && $npres == 0 && $preaut != 1) {
//		$error = 'si';	$mensaje .=$lang['No hay montos de valuación'].'<br>'."\n"; 
	}

	if (($num_cols>0) && ($error == 'no')) {
//		echo $sub['sub_estatus'];
		if ($sub['sub_estatus'] == 111 || $sub['sub_estatus'] == 121 || $sub['sub_estatus'] == 101 || $sub['sub_estatus'] == 127)  {
//	echo $usuario . ' --> ' . $sub_orden_id . ' --> ' .  $orden_id;
			if($seleccion == 1) {
				$reprocesos = $sub['sub_reprocesos'] + 1;
				if($sub['sub_estatus'] == 101) {
					$sql_data_array = array('sub_estatus' => '101');
					bitacora($orden_id, 'Devuelto a Estatus 1 ' . ORDEN_ESTATUS_1, $dbpfx);
				} elseif($sub['sub_estatus'] == 127) {
					$sql_data_array = array('sub_estatus' => '127');
					bitacora($orden_id, 'Devuelto a Estatus 27 ' . ORDEN_ESTATUS_27, $dbpfx);
				} else {
					$sql_data_array = array('sub_estatus' => '107');
					bitacora($orden_id, 'Devuelto a Estatus 7 ' . ORDEN_ESTATUS_7, $dbpfx);
					$sql_data_array['sub_reprocesos'] = $reprocesos;
				}
			}
			if($seleccion == 2) {
				if($pendientes == 1 && $sub['sub_estatus'] == 111) {
					$sql_data_array = array('sub_estatus' => '105');
					bitacora($orden_id, 'Cambio a Estatus 5 ' . ORDEN_ESTATUS_5, $dbpfx);
				} elseif($sub['sub_estatus'] == 111) {
					$sql_data_array = array('sub_estatus' => '121');
				} elseif($sub['sub_estatus'] == 101 && $sub['sub_aseguradora'] < '1' && $particpres != 1) {
					// ------ Si es Particular se cambia el estatus a valuado
					$sql_data_array = array('sub_estatus' => '102');
				} elseif($sub['sub_estatus'] == 101) {
					// ------ Si es de Aseguradora se cambia el estatus según $saltapres
					if($ase[$sub['sub_aseguradora']]['saltapres'] == 1) {
						if($valor['ValComoPartic'][0] == '1') {
							$sql_data_array = array('sub_estatus' => '102');
						} else {
							$sql_data_array = array('sub_estatus' => '129');
						}
					} else {
						$sql_data_array = array('sub_estatus' => '124');
					}
				} elseif($sub['sub_estatus'] == 127) {
					$sql_data_array = array('sub_estatus' => '128',
						'sub_controlista' => $_SESSION['usuario'],
						'sub_fecha_asignacion' => date('Y-m-d H:i:s', time()));
				} else {
					$sql_data_array = array('sub_estatus' => '112');
					if(is_null($sub['sub_fecha_terminado'])) {
						$sql_data_array['sub_fecha_terminado'] = date('Y-m-d H:i:s');
					}
					$sql_data_array['sub_horas_empleadas'] = horasEmpleadas($sub_orden_id, $dbpfx);
					if($metodo != 'c') {
						$pregunta2 = "SELECT usuario, horas_programadas FROM " . $dbpfx . "usuarios WHERE usuario = '$operador'";
						$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección de horas de operador!".$matriz2);
						$usuario = mysql_fetch_array($matriz2); 
						$horas=0;
						$carga= explode (":", $usuario['horas_programadas']);
						$progra = explode (":", $sub['sub_horas_programadas']);
						if ($carga[1] < $progra[1]) { $carga[1] = $carga[1] + 60; $carga[0] = $carga[0] -1; }
						$minutos_c = $carga[1] - $progra[1];
						if($minutos_c == 0) {$minutos_c='00';}
						$horas_c = $carga[0] - $progra[0];
						if($horas_c <= 0) {$horas_c='00';}
						$nueva_carga= array('horas_programadas' => $horas_c . ':' . $minutos_c . ':00');
						$parametros = 'usuario = ' . $operador;
						ejecutar_db($dbpfx . 'usuarios', $nueva_carga, 'actualizar', $parametros);
					}
					if(is_null($sub['sub_fecha_terminado']) && $notifica_tareas[$sub['sub_area']] == 1) {
						include('idiomas/'.$idioma.'/notifica-tareas.php');
						include('parciales/notifica.php');
					}
				}
			}
			$parametros = 'sub_orden_id = ' . $sub_orden_id;
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
			$nse = $sql_data_array['sub_estatus'];
			bitacora($orden_id, 'Cambio de Tarea ' . $sub_orden_id . ' a estatus ' . $nse . ' ' . constant('ORDEN_ESTATUS_'.($nse-100)), $dbpfx);

			if($cierrapres == 1 && $nse == '128' && is_array($areapres)) {
				$preg1 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
				$matr1 = mysql_query($preg1) or die("Falló selección de reporte! " . $preg1);
				$subrep = mysql_fetch_array($matr1);
				$reporte = $subrep['sub_reporte'];
				$preg1 = "SELECT sub_area, sub_estatus FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus >= '124' AND sub_estatus <= '128' AND sub_reporte = '" . $reporte . "'";
				$matr1 = mysql_query($preg1) or die("Falló selección de tareas del reporte! " . $preg1);
				$cerrar = 1;
				while($cpres = mysql_fetch_array($matr1)) {
					foreach($areapres as $k) {
						if($cpres['sub_area'] == $k && $cpres['sub_estatus'] < '128') { $cerrar = 0; }
					}
				}
				if($cerrar == 1) {
					$param = "orden_id = '$orden_id' AND sub_estatus >= '124' AND sub_estatus <= '127' AND sub_reporte = '" . $reporte . "'";
					$sql_cierra = array('sub_estatus' => '128');
					ejecutar_db($dbpfx . 'subordenes', $sql_cierra, 'actualizar', $param);
					bitacora($orden_id, 'Cambio automático de Tarea a estatus 128.', $dbpfx);
					for($i=1; $i <= $num_areas_servicio; $i++) {
						actualiza_suborden ($orden_id, $i, $dbpfx);
					}
				}
			}

			if($qv_activo == 1 && $nse == '128' && $ase[$sub['sub_aseguradora']]['autosurtido'] == '1') {
				// --- Si QV está activo, genera el XML para crear cotizaciones
				$preg1 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE ";
				if($cierrapres == 1) {
					$preg1 .= " sub_estatus = '128' AND orden_id = '$orden_id' AND sub_reporte = '$reporte'";
				} else {
					$preg1 .= " sub_orden_id = '$sub_orden_id'";
				}
				$matr1 = mysql_query($preg1) or die("Falló selección de reporte! " . $preg1);
				$opxml = '';
				while($subref = mysql_fetch_array($matr1)) {
					$pregref = "SELECT o.op_id, o.op_cantidad, o.op_nombre, o.op_codigo, o.op_cotizado_a, o.op_pedido, o.op_doc_id, o.op_recibidos, d.doc_archivo FROM " . $dbpfx . "orden_productos o LEFT JOIN " . $dbpfx . "documentos d ON o.op_doc_id = d.doc_id WHERE o.op_tangible = '1' AND o.op_pedido < '1' AND o.op_ok = '0' AND o.sub_orden_id = '" . $subref['sub_orden_id'] . "'";
					$matref = mysql_query($pregref) or die("ERROR: Fallo selección de refacciones! " . $pregref);
					$fila1 = mysql_num_rows($matref);
					if($fila1 > 0) {
						while($prods = mysql_fetch_array($matref)) {
							$opxml .= '			<Ref op_id="' . $prods['op_id'] . '" op_cantidad="' . $prods['op_cantidad'] . '" op_nombre="' . $prods['op_nombre'] . '" op_codigo="' . $prods['op_codigo'] . '" op_doc_id="' . $prods['op_doc_id'] . '" op_estatus="10" foto_ref="' . $prods['doc_archivo'] . '" />'."\n";
						}
					}
				}
				if($opxml != '') {
					$veh = datosVehiculo($orden_id, $dbpfx);
					$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
					$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
					$xml .= '		<Solicitud tiempo="0">10</Solicitud>'."\n";
					$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'" foto_frontal="' . $veh['foto_frontal'] .'" foto_izquierda="' . $veh['foto_izquierda'] .'" foto_derecha="' . $veh['foto_derecha'] .'" foto_vin="' . $veh['foto_vin'] .'">'."\n";
					$xml .= $opxml;
					$xml .= '		</OT>'."\n";
					$xml .= '	</Comprador>'."\n";
					$mtime = substr(microtime(), (strlen(microtime())-3), 3);
					$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.qv';
					file_put_contents("../qv-salida/".$xmlnom, $xml);
				}
			}

			unset($sql_data_array);
			actualiza_orden($orden_id, $dbpfx);

			// ------ Verifica que todos los trabajos tengan valuación, si no, regresa las tareas al estatus 103 y la OT a 3. 
			$pregunta3 = "SELECT orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
			$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
			$estat = mysql_fetch_array($matriz3);
			if ($estat['orden_estatus'] == 12 && $valor['ValidaValTrab'][0] == 1) {
				$preg4 = "SELECT sub_reporte, sub_presupuesto FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '130'";
				$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de trabajos! " . $preg4);
				$uval = array();
				while ($vsub = mysql_fetch_array($matr4)) {
					if($vsub['sub_reporte'] == '') { $vsub['sub_reporte'] = 0; }
					$uval[$vsub['sub_reporte']] = $uval[$vsub['sub_reporte']] + $vsub['sub_presupuesto'];
				}
				foreach($uval as $k => $v) {
					if($v < 1) {
						$param = "sub_reporte = '" . $k . "' AND orden_id = ''" . $orden_id . "";
						$sql_data = array('sub_estatus' => '103');
						ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $param);
						if($k == 0) { $cual = 'Trabajo Particular'; } else { $cual = 'Siniestro ' . $k; }

						$preg5 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE activo = 1 AND acceso = 0 AND rol02 = 1";
						$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de gerentes! " . $preg5);
						while($ger = mysql_fetch_array($matr5)) {
							bitacora($orden_id, $cual . ' terminado sin valuación.', $dbpfx, 'Para: ' . $ger['nombre'] . ' ' . $ger['apellidos'] . $cual . ' terminado sin valuación.', 3, '', '', $ger['usuario']);
						}
						unset($sql_data);
					}
				}
				unset($uval);
			actualiza_orden($orden_id, $dbpfx);
			}

			$pregunta3 = "SELECT orden_estatus, orden_presupuesto, orden_ref_pendientes, orden_fecha_de_entrega FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
			$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
			$estat = mysql_fetch_array($matriz3);
			if ($estat['orden_estatus'] == 12 && $notifica_cliente == 1 && $estat['orden_ref_pendientes'] == 0 && strtotime($estat['orden_fecha_de_entrega']) < 1000000) {
				include('idiomas/'.$idioma.'/notifica.php');
				include('parciales/notifica.php');
				$parametros = 'orden_id = ' . $orden_id;
				$sql_data['orden_ubicacion'] = 'Zona de Espera';
				ejecutar_db($dbpfx . 'ordenes', $sql_data, 'actualizar', $parametros);
				unset($sql_data);
			}

			redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
		} else {
			redirigir('entrega.php?accion=suborden&mensaje='.$lang['no está esperando evaluación']);
		}
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
}

elseif ($accion==="asignar") {
	
	$funnum = 1030015;
	
//	echo 'Estamos en la sección asignar';
	$error = 'si'; $num_cols = 0;
	if ($orden_id!='') {
		$pregunta = "SELECT o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas,  o.orden_cliente_id, o.orden_fecha_promesa_de_entrega, c.cliente_nombre, c.cliente_apellidos, c.cliente_telefono1, c.cliente_telefono2, c.cliente_movil, c.cliente_movil2  FROM " . $dbpfx . "ordenes o, " . $dbpfx . "clientes c WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$orden = mysql_fetch_array($matriz);
		$num_cols = mysql_num_rows($matriz);
		$error = 'no';
	}
//	echo $pregunta;
	if ($num_cols>0 && $error=='no') {
		echo '	<br><form action="entrega.php?accion=asigna" method="post" enctype="multipart/form-data">
		<table cellpadding="0" cellspacing="0" border="0" class="agrega">';
		if (isset($_SESSION['entrega']['mensaje'])) {
			echo '		<tr><td colspan="2"><span class="alerta">' . $_SESSION['entrega']['mensaje'] . '</span></td></tr>';
			unset($_SESSION['entrega']['mensaje']);
		}
		echo '		<tr class="cabeza_tabla"><td colspan="2">'.$lang['Preparación de Automóvil para entrega a cliente'].'</td></tr>
		<tr><td>
			<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
				<tr><td>Vehículo</td><!-- <td>'.$lang['Asignar al preparador'].'</td> --></tr>
				';
		echo '				<tr><td>' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] .$lang['Placas'] . $orden['orden_vehiculo_placas'] . '<br>
		'.$lang['Cliente'] . $orden['cliente_nombre'] . ' ' . $orden['cliente_apellidos'] . '<br>'.$lang['Teléfono 1'] . $orden['cliente_telefono1'] . '<br>
		'.$lang['Teléfono 2'] . $orden['cliente_telefono2'] . '<br>
		'.$lang['Movil 1'] . $orden['cliente_movil'] . '<br>
		'.$lang['Movil 2'] . $orden['cliente_movil2'] . '<br>
		</td><td>'.$lang['Seleccione un Lavador'].'<br><select name="operador" size="1">
					<option value="" > '.$lang['Seleccione'].' </option>';
		$pregunta2 = "SELECT usuario, nombre, apellidos, horas_programadas FROM " . $dbpfx . "usuarios WHERE rol10 = '1' AND acceso = '0' AND activo = '1' ORDER BY apellidos";
		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
		while ($usuario = mysql_fetch_array($matriz2)) {
			echo '					<option value="' . $usuario['usuario'] . '">' . $usuario['nombre'] . ' ' . $usuario['apellidos'] . '</option>';
			}
		echo '</select>';
		echo '				</td> </tr>
				';
		echo '<tr><td>'.$lang['Fecha acordada con el Cliente'].'</td>
					<td>';

		require_once("calendar/tc_calendar.php");

		//instantiate class and set properties
		$myCalendar = new tc_calendar("acordada", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
	if ($orden['orden_fecha_promesa_de_entrega']!=NULL) {
		$f_promesa = strtotime($orden['orden_fecha_promesa_de_entrega']);
		$myCalendar->setDate(date("d", $f_promesa), date("m", $f_promesa), date("Y", $f_promesa));
	} else {
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
	}
		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2011, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();	  
		
		echo '					</td></tr>';
		echo '<tr><td>'.$lang['Hora acordada con el Cliente'].'</td>
					<td><select name="h_acor" size="1">
						<option value="" >'.$lang[' Seleccione... '].'</option>
						<option value="09:00" > 09:00 </option>
						<option value="09:30" > 09:30 </option>
						<option value="10:00" > 10:00 </option>
						<option value="10:30" > 10:30 </option>
						<option value="11:00" > 11:00 </option>
						<option value="11:30" > 11:30 </option>
						<option value="12:00" > 12:00 </option>
						<option value="12:30" > 12:30 </option>
						<option value="13:00" > 13:00 </option>
						<option value="13:30" > 13:30 </option>
						<option value="14:00" > 14:00 </option>
						<option value="14:30" > 14:30 </option>
						<option value="15:00" > 15:00 </option>
						<option value="15:30" > 15:30 </option>
						<option value="16:00" > 16:00 </option>
						<option value="16:30" > 16:30 </option>
						<option value="17:00" > 17:00 </option>
						<option value="17:30" > 17:30 </option>
						<option value="18:00" > 18:00 </option>
						<option value="18:30" > 18:30 </option>
					</select>';
		echo '					</td></tr>';
		echo '<tr><td colspan="2">&nbsp;</td></tr>
				<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
			</table>
			</td></tr>
		<tr><td colspan="2" style="text-align:left;">
		<input type="hidden" name="orden_id" value="' . $orden_id . '">
		<input type="submit" value="'.$lang['Enviar'].'" />&nbsp;<input type="reset" name="limpiar" value="' . $lang['Limpiar Selección'] . '" /></td></tr></table></form>
';

	} else {
   	echo '<span class="alerta">'.$lang['No se localizó la OT o El Cliente o el Vehículo indicado'];
   }
}

elseif($accion==='asigna') {
	
	$funnum = 1030020;
	
	if ($_SESSION['rol06']!='1') {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso para Asesor de Servicio']);
	}
	$error = 'no';
	$mensaje = '';
  	if ($operador == '') {$error = 'si'; $mensaje .=$lang['Seleccione un Lavador'].'<br>';}
  	if ($acordada == '' || $h_acor == '') {$error = 'si'; $mensaje .=$lang['Fecha y Hora para la entrega del vehículo'].'<br>';}
   if ($error === 'no' && $acordada != '')  {
  		$fecha_acor = $acordada . ' ' . $h_acor;
  		$parametros = 'orden_id = ' . $orden_id;
  		$pregunta = "SELECT c.cliente_nombre, c.cliente_apellidos, c.cliente_email, o.orden_cliente_id, o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas, o.orden_estatus_1, o.orden_estatus_2, o.orden_estatus_3, o.orden_estatus_4, o.orden_estatus_5, o.orden_estatus_6, o.orden_estatus_7, o.orden_estatus_8, o.orden_estatus_9, o.orden_estatus_10, o.orden_presupuesto FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
//  		echo $pregunta;
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$dato = mysql_fetch_array($matriz);
	   
  		if($notifica_cliente == 1 && $dato['cliente_email'] != '') {
			
			$para = $dato['cliente_email'];
			$diasem = [
				'0' => 'Domingo',
				'1' => 'Lunes',
				'2' => 'Martes',
				'3' => 'Miércoles',
				'4' => 'Jueves',
				'5' => 'Viernes',
				'6' => 'Sábado'
			];
			$nommeses = [
				'1' => 'Enero',
				'2' => 'Febrero',
				'3' => 'Marzo',
				'4' => 'Abril',
				'5' => 'Mayo',
				'6' => 'Junio',
				'7' => 'Julio',
				'8' => 'Agosto',
				'9' => 'Septiembre',
				'10' => 'Octubre',
				'11' => 'Noviembre',
				'12' => 'Diciembre'
			];
			$eldia = strtotime($fecha_acor);
			$f_acor = $diasem[date('w', $eldia)] . ' ' . date('j', $eldia) . ' de ' . $nommeses[date('n', $eldia)] . ' a las ' . date('H:i', $eldia);
			//echo $fecha_acor;
			
			$contenido = '
<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				</br>
				<h3>' . $lang['EMAIL_ENTREGA_SALUDO'] . $dato['cliente_nombre'] . ' ' . $dato['cliente_apellidos'] . '.</h3>
				<p class="lead">' .  $lang['EMAIL_ENTREGA_CONT1'] . $orden_id . ' ' .  $lang['EMAIL_ENTREGA_CONT2'] . ' <strong>' . $dato['orden_vehiculo_marca'] . ' ' . $dato['orden_vehiculo_tipo'] . ' ' . $dato['orden_vehiculo_color'] . ' ' . $dato['orden_vehiculo_placas'] . '</strong>.<br><br> ' .  $lang['EMAIL_ENTREGA_CONT3'] . $f_acor . '</p>	
				<br>
				<p>' . $lang['EMAIL_ENTREGA_CONT5'] . '</p>
		</td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h5>Atentamente:</h5>
				<p>' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '<br>
        		' . $nombre_agencia . '<br>'."\n";
				if($_SESSION['email'] != '') {
					$contenido .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' . $_SESSION['email'] . '">' . $_SESSION['email'] . '</a><br>'."\n";
					$concopia = $_SESSION['email'];
				} else {
					$contenido .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' . $agencia_email . '">' . $agencia_email . '</a><br>'."\n";
				}
				$contenido .= '        		' . $agencia_telefonos . '<br>
				<p style="font-size:9px;font-weight:bold;">Este mensaje fue
        		enviado desde un sistema automático, si desea hacer algúnn
        		comentario respecto a esta notificación o cualquier otro asunto
        		respecto al Centro de Reparación por favor responda a los
        		correos electrónicos o teléfonos incluidos en el cuerpo de este
        		mensaje. De antemano le agradecemos su atención y preferencia.</p>
			</div>							
		</td>
		<td></td>
	</tr>
</table>'."\n";
			
			$para = $dato['cliente_email'];
			$asunto = $lang['EMAIL_ENTREGA_ASUNTO'] ;
			
			include ('parciales/notifica2.php');

  		}
	   
	   
  		$sql_data_array = array('orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'),
  			'orden_estatus' => '14',
			'orden_fecha_notificacion' => date('Y-m-d H:i:s'),
			'orden_fecha_acordada' => $fecha_acor,
	  		'orden_alerta' => '0');
//  		echo $para . EMAIL_ENTREGA_ASUNTO . $mensaje . $encabezados; 
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Cambio a estatus ' . $sql_data_array['orden_estatus'] . ' ' . constant('ORDEN_ESTATUS_14') . ' anterior: 12 ' . constant('ORDEN_ESTATUS_12'), $dbpfx);
		$preg0 = "SELECT sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_aseguradora > '0' AND sub_estatus = '112' GROUP BY sub_aseguradora";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de subordenes!");
		$sub = mysql_fetch_array($matr0);
		$aseguradora = $sub['sub_aseguradora'];
		$reporte = $sub['sub_reporte'];
		if($notiase == '1' && $asenoti[$aseguradora]['alta'] == '1') {
			$asunto = 'Terminado de vehículo con placas ' . $dato['orden_vehiculo_placas'] . ' en ' . $nombre_agencia;
			$situacion = 'se terminó la reparación en <strong>' . $nombre_agencia . '</strong> de ';
			include_once('parciales/notifica_aseguradora.php');
		}
		
		
/*		$sql_data_array = array('orden_id' => $orden_id,
			'sub_area' => '8',
			'sub_descripcion' => 'Lavado y preparación para entrega al cliente.',
			'sub_estatus' => '122', 
			'sub_operador' => $operador);
  		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
		actualiza_suborden($orden_id, '8', $dbpfx);
*/
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		include('idiomas/' . $idioma . '/proceso.php');
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		$mensaje .=$lang['No se liberó el automovil de la OT'] . $orden_id . $lang['para su entrega'];
	}
	echo $mensaje;
}

elseif($accion==='lavado') {
	
	$funnum = 1030025;
	
	if ($_SESSION['rol06']!='1') {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso para Asesor de Servicio']);
	}
	$error = 'no';
	$mensaje = '';
	if ($error === 'no') {
   	$parametros = 'orden_id = ' . $orden_id;
  		$sql_data_array = array('orden_estatus' => '15',
  			'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'));
	  	$sql_data_array['orden_alerta'] = '0';
		// echo $para . EMAIL_ENTREGA_ASUNTO . $mensaje . $encabezados; 
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Cambio a estatus ' . $sql_data_array['orden_estatus'] . ' ' . constant('ORDEN_ESTATUS_15') . ' anterior: 14 ' . constant('ORDEN_ESTATUS_14'), $dbpfx);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		include('idiomas/' . $idioma . '/proceso.php');
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		$mensaje .= $lang['No se liberó el automovil de la OT'] . $orden_id . $lang['para su entrega'];
	}
	echo $mensaje;
}

elseif ($accion==="listado") {
	
	$funnum = 1030030;
//	echo 'Estamos en la sección listar para factura';
	$error = 'si'; $num_cols = 0;
	if ($orden_id!='') {
		$pregunta = "SELECT o.orden_vehiculo_id, o.orden_cliente_id, o.orden_odometro, o.orden_estatus, c.cliente_nombre, c.cliente_apellidos FROM " . $dbpfx . "ordenes o, " . $dbpfx . "clientes c WHERE o.orden_id = '$orden_id' AND c.cliente_id = o.orden_cliente_id";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
		$error = 'no';
	}
	
	if ($error ==='no' && $num_cols > 0 && $dato != '') {
		$rep = explode('|', $dato);
		if($rep[0] == '0' || $rep[0] == '') { $reporte = 'Particular'; } else { $reporte = $rep[0]; }  
		while ($orden = mysql_fetch_array($matriz)) {

			echo '		<table cellpadding="3" cellspacing="0" border="0" width="840" class="izquierda">'."\n";
			echo '			<tr>
			<td style="width:230px;"><img src="particular/logo-agencia.png" alt="' . $agencia_razon_social . '" height="80"></td><br>
		      <td style="width:400px; text-align:center;"><h2>FORMATO DE ENTREGA</h2>
		      	<br><span style="font-size:9px; line-height:6px;">Este documento NO es un recibo de pago o comprobante fiscal.<br>Solo aplica como formato de entrega.</span>.
				</td>
				<td style="width:210px; vertical-align: top; line-height:12px;">' . $agencia_direccion . '<br><br>
				Col. ' . $agencia_colonia . '.<br><br>
				C.P. ' . $agencia_cp . '. '  . $agencia_municipio . '<br><br>' . $agencia_estado . '<br><br>
				Tel. ' . $agencia_telefonos . '</td>
			</tr>'."\n";
			echo '		</table>'."\n";
			echo '		<table cellpadding="3" cellspacing="0" border="0" width="840" class="izquierda">'."\n";
			echo '			<tr><td>Expedido en ' . $agencia_municipio . ', ' . $agencia_estado . ' el ' . date('d') . ' de ' . date('M') . ' del ' . date('Y') . '</td><td style="text-align:right;">Orden de Trabajo: ' . $orden_id . ' Reporte: ' . $reporte . '</td></tr>'."\n";
			echo '		</table>'."\n";

			$veh = datosVehiculo($orden_id, $dbpfx);
			echo '		<table cellpadding="3" cellspacing="0" border="1" width="840" class="izquierda" style="font-size:12px;">'."\n";
			echo '			<tr><td colspan="2">' . $lang['Datos del Cliente'] . ' ' . $orden['cliente_nombre'] . ' '  . $orden['cliente_apellidos'] . '</tr>'."\n";
			echo '			<tr><td>' . $lang['Datos del Vehiculo'] . ': ' . $veh['marca'] . ' '  . $veh['tipo'] . ' ' . $veh['color'] . ' ' . $veh['modelo'] . ' ' . $lang['Placas'] . ' ' . $veh['placas'] . ' ' . $lang['Serie'] . ': ' . $veh['serie'] . ' ' . constant('UNIDAD_'.$metrico) . ': ' . $orden['orden_odometro'] . '</td></tr>'."\n";
			echo '		</table><br>'."\n";
			echo '		<table cellpadding="3" cellspacing="0" border="1" width="840" class="izquierda">'."\n";
			echo '			<tr class="cabeza_tabla"><td width="10%">' . $lang['Cantidad'] . '</td><td width="50%">' . $lang['Descripción'] . '</td><td width="20%">' . $lang['Precio Unitario'] . '</td><td width="20%">' . $lang['Sub Total'] . '</td></tr>'."\n";
   	  	$pregunta2 = "SELECT sub_orden_id, sub_partes, sub_consumibles, sub_mo, sub_area FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '" . $rep[0] . "' AND sub_estatus < '130'";
     		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección de subordenes!".$pregunta2);
	      while ($sub = mysql_fetch_array($matriz2)) {
   	   	$mo[$sub['sub_area']] = $mo[$sub['sub_area']] + round($sub['sub_mo'], 2);
      		$totmo = $totmo + $sub['sub_mo'];
	      }
   	   echo '			<tr><td colspan="4">' . $lang['Mano de Obra'] . '</td></tr>'."\n";
      	foreach($mo as $k => $v){
      		if($v > 0) {
      			echo '			<tr><td style="text-align:center;">1</td><td>' . $lang['Mano de Obra'] . ' de ' . constant('NOMBRE_AREA_'.$k) . '</td><td style="text-align:right;">$ ' . number_format($v,2) . '</td><td style="text-align:right;">$ ' . number_format($v,2) . '</td></tr>'."\n";
	      	}
   	   }
      	echo '			<tr><td colspan="4">' . $lang['Refacciones'] . '</td></tr>'."\n";
			mysql_data_seek($matriz2,0);
			while ($sub = mysql_fetch_array($matriz2)) {
				$pregunta4 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_pres IS NULL AND op_tangible = '1' AND (op_autosurtido = '2' OR op_autosurtido = '3') ORDER BY op_nombre";
				$matriz4 = mysql_query($pregunta4) or die("ERROR: Fallo seleccion!");
				$num_prods = mysql_num_rows($matriz4);
				while ($prods = mysql_fetch_array($matriz4)) {
					if ($prods['op_cantidad'] > 0 && $prods['op_precio'] != 0) {
						echo '<tr><td width="10%" style="text-align:center;">' . $prods['op_cantidad'] . '</td><td style="text-align:left;"  width="50%">' . $prods['op_nombre'] . '</td><td style="text-align:right;" width="20%">$ ' . number_format($prods['op_precio'],2) . '</td><td style="text-align:right;" width="20%">$ ' . number_format(($prods['op_cantidad'] * $prods['op_precio']),2) . '</td></tr>'."\n";
						$totpar = $totpar + round(($prods['op_cantidad'] * $prods['op_precio']), 2);
					}
				}
			}
      	echo '			<tr><td colspan="4">' . $lang['Consumibles'] . '</td></tr>'."\n";
			mysql_data_seek($matriz2,0);
			while ($sub = mysql_fetch_array($matriz2)) {
				$pregunta4 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_pres IS NULL AND op_tangible = '2' ORDER BY op_nombre";
				$matriz4 = mysql_query($pregunta4) or die("ERROR: Fallo selección de consumibles!");
				$num_prods = mysql_num_rows($matriz4);
				while ($prods = mysql_fetch_array($matriz4)) {
					if ($prods['op_cantidad'] > 0 && $prods['op_precio'] > 0) {
						echo '<tr><td width="10%" style="text-align:center;">' . $prods['op_cantidad'] . '</td><td style="text-align:left;"  width="50%">' . $prods['op_nombre'] . '</td><td style="text-align:right;" width="20%">$ ' . number_format($prods['op_precio'],2) . '</td><td style="text-align:right;" width="20%">$ ' . number_format(($prods['op_cantidad'] * $prods['op_precio']),2) . '</td></tr>'."\n";
						$totcons = $totcons + round(($prods['op_cantidad'] * $prods['op_precio']), 2);
					}
				}
			}
			echo '		</table>'."\n";
			$totsub = $totpar + $totmo + $totcons; 
			$totiva = round(($totsub * $impuesto_iva), 2);
			$grantotal = $totsub + $totiva;
			echo '		<table cellpadding="3" cellspacing="0" border="0" width="840" class="izquierda">'."\n";
			echo '			<tr><td width="60%" colspan="2" style="border-bottom:1px solid black;">Observaciones:</td><td style="text-align:right;" width="20%">SubTotal de Refacciones</td><td style="text-align:right; border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black;">$ ' . number_format($totpar,2) . '</td></tr>'."\n";
			echo '			<tr><td colspan="2" style="border-bottom:1px solid black;"></td><td style="text-align:right;">SubTotal de Consumibles</td><td style="text-align:right; border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black;">$ ' . number_format($totcons,2) . '</td></tr>'."\n";
			echo '			<tr><td colspan="2" style="border-bottom:1px solid black;"></td><td style="text-align:right;">SubTotal de Mano de Obra</td><td style="text-align:right; border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black;">$ ' . number_format($totmo,2) . '</td></tr>'."\n";
			echo '			<tr><td colspan="2" style="border-bottom:1px solid black;"></td><td style="text-align:right;">SubTotal</td><td style="text-align:right; border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black;">$ ' . number_format($totsub,2) . '</td></tr>'."\n";
			echo '			<tr><td colspan="2" style="border-bottom:1px solid black;"></td><td style="text-align:right;">IVA al ' . ($impuesto_iva * 100) . '%</td><td style="text-align:right; border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black;">$ ' . number_format($totiva,2) . '</td></tr>'."\n";
			echo '			<tr><td colspan="2" style="border-bottom:1px solid black;"></td><td style="text-align:right;">Total</td><td style="text-align:right; border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black;">$ ' . number_format($grantotal,2) . '</td></tr>'."\n";
			echo '			<tr><td colspan="4" style="font-size:9px; line-height:12px; text-align:right;">Este documento NO es un recibo de pago o comprobante fiscal. Sólo aplica como formato de entrega.</td></tr>'."\n";
			echo '			<tr><td colspan="4"><div class="control">';
			if(($orden['orden_estatus']==15) && ($_SESSION['rol06']=='1')) {
				echo '<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="' . $lang['Imprimir Conceptos de Factura'] . '" title="' . $lang['Imprimir Conceptos de Factura'] . '"></a> ';
			}
			echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a></div></td></tr>'."\n";
			echo '		</table>'."\n";
		}
	} else {
		if(!isset($dato) || $dato=='') {
			$preg0 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '189' GROUP BY sub_reporte";
   	  	$mat0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
     		$num_rep = mysql_num_rows($mat0);
			if ($num_rep > 1) {
				echo '	<form action="entrega.php?accion=listado" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="850">'."\n";
   		  	echo '		<tr><td colspan="2" style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">'. $lang['más de un siniestro o tipo de servicio, elija el reporte'].'</td></tr>' . "\n";
     			echo '		<tr><td colspan="2"><select name="dato" size="1">' . "\n";
				echo '			<option value="" >'.$lang['Seleccione'].'</option>';
		     	while($rep = mysql_fetch_array($mat0)) {
		     		if($rep['sub_reporte'] == '0' || $rep['sub_reporte'] == '') { $reporte = 'Particular'; } else { $reporte = $rep['sub_reporte']; }
   		  		echo '			<option value="' . $rep['sub_reporte'] . '|' . $rep['sub_aseguradora'] . '">' . $reporte . '</option>' . "\n";
				}
				echo '		</select></td></tr>' . "\n";
				echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2"><input type="hidden" name="orden_id" value="' . $orden_id . '" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="'.$lang['Enviar'].'" /></td></tr>
		</tr>
	</table>
	</form>'."\n";
			} else {
				$rep = mysql_fetch_array($mat0);
				$dato = $rep['sub_reporte'] . '|' . $rep['sub_aseguradora'];
				redirigir('entrega.php?accion=listado&orden_id=' . $orden_id . '&dato=' . $dato);
			}
		} else {
			$mensaje .=$lang['No hay registros con esos datos'].'</br>';
			echo '<p>' . $mensaje . '</p>';
		}
	}
}

elseif ($accion==="entregar") {

	$funnum = 1030095;
	
//	echo 'Estamos en la sección enviar';
	echo '	<form action="entrega.php?accion=liberar" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	echo '		<tr><td>'. $lang['confirme la entrega del vehículo'].'</td><td style="text-align:left;"><input type="checkbox" name="confirmado" value="1" /></td></tr>'."\n";
	echo '		<tr><td>'. $lang['agregue un comentario'].'</td><td><textarea name="motivo" cols="40" rows="6"></textarea></td></tr>
		<tr><td>'. $lang['Fecha llamada al cliente'].'</td><td style="text-align:left;">';

		require_once("calendar/tc_calendar.php");

		//instantiate class and set properties
		$myCalendar = new tc_calendar("proxima", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2012, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();	  
		
		
		echo '</td></tr>';

	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="' . $lang['Enviar'] . '" /></td></tr>
		</tr>
	</table>
	</form>';
}

elseif($accion==="liberar") {
	
	$funnum = 1030100;
	
	if ($_SESSION['rol06']!='1') {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso para Asesor de Servicio']);
	}
	$error = 'no';
	$mensaje = '';
   if (isset($confirmado) && $confirmado == '1' && $error === 'no') {
		$sql_data_array = array('orden_alerta' => '0',
			'orden_ubicacion' => 'Entregado por Documentar',
			'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'),
			'orden_estatus' => '16');
		$preg = "SELECT orden_vehiculo_id, orden_fecha_de_entrega FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de Ordenes de Trabajo!");
		$ord = mysql_fetch_array($matr);
		if(is_null($ord['orden_fecha_de_entrega']) || $ord['orden_fecha_de_entrega'] == '0000-00-00 00:00:00' || $ord['orden_fecha_de_entrega'] == '') {
			$sql_data_array['orden_fecha_de_entrega'] = date('Y-m-d H:i:s');
		}
		$parametros='orden_id = ' . $orden_id;
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Cambio a estatus ' . $sql_data_array['orden_estatus'] . ' ' . constant('ORDEN_ESTATUS_16') . ' anterior: 15 ' . constant('ORDEN_ESTATUS_15'), $dbpfx);
		if($motivo != '') {
			$parametros = " vehiculo_id = '" . $ord['orden_vehiculo_id'] . "'";
			$sql_data = array('vehiculo_proxima' => $proxima,
				'vehiculo_motivo' => $motivo);
			ejecutar_db($dbpfx . 'vehiculos', $sql_data, 'actualizar', $parametros);
			bitacora($orden_id, 'Comentario de entrega a Cliente', $dbpfx, $motivo, '2');
		}
		unset($sql_data_array);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$mensaje .= $resultado['mensaje'];
		$mensaje .= $lang['No se marcó como entregado el vehículo de la OT'] . $orden_id . $lang['intente de nuevo'];
		$_SESSION['msjerror'] = $mensaje;
		redirigir('entrega.php?accion=entregar&orden_id=' . $orden_id);
	}
}

elseif ($accion==="salida") {
	
	$funnum = 1030045;
			
//	echo 'Estamos en la sección enviar';
	echo '	<form action="entrega.php?accion=transito" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">';
//	echo '		<tr><td>Agregar imagen escaneada en JPG Salida con Pendiente por Refacciones</td><td style="text-align:left;"><input type="file" name="imagen" size="30" /></td></tr>'."\n";
	echo '		<tr><td>'. $lang['Comentarios'].'</td><td><textarea name="motivo" cols="40" rows="6"></textarea></td></tr>';
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2"><input type="hidden" name="orden_id" value="' . $orden_id . '" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="'. $lang['Enviar'].'" /></td></tr>
		</tr>
	</table>
	</form>';
}

elseif($accion==="transito") {
	
	$funnum = 1030050;
	
	if ($_SESSION['rol06']!='1') {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso para Asesor de Servicio']);
	}
	$error = 'no';
	$mensaje = '';
	// $resultado = agrega_documento($orden_id, $_FILES['imagen'], 'ot-transito', $dbpfx);
 	if($resultado['error'] == 'no') { $mensaje .= $lang['No se cerró la orden de trabajo'] . $orden_id . $lang['intente de nuevo']; }
   if ($error === 'no') {
		$sql_data_array = array('orden_alerta' => '0', 
			'orden_ubicacion' => 'Transito', 
			'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'));
		$parametros='orden_id = ' . $orden_id;
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, constant('ORDEN_ESTATUS_' . $sql_data_array['orden_estatus']), $dbpfx, $motivo);
		unset($sql_data_array);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
}

elseif ($accion==="garantia") {
	
	$funnum = 1030055;
	
//	echo 'Estamos en la sección listar para factura';
	$error = 'si'; $num_cols = 0;
	if ($orden_id!='') {
		$pregunta = "SELECT o.orden_vehiculo_marca, o.orden_vehiculo_tipo, v.vehiculo_modelo, v.vehiculo_serie, o.orden_vehiculo_placas, o.orden_odometro, o.orden_cliente_id FROM " . $dbpfx . "ordenes o, " . $dbpfx . "vehiculos v WHERE o.orden_id = '$orden_id' AND o.orden_vehiculo_id = v.vehiculo_id";
		$error = 'no';
	}
	if ($error ==='no') {
//		echo $pregunta;
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
	}
	if ($num_cols>0 && $dato!='') {
		$rep = explode('|', $dato);
		if($rep[0] != '0'){
			$reporte = $rep[0];
			// --- Buscar la aseguradora a la que pertenece ese reporte ---
			$preg_convenio = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND sub_reporte = '" . $reporte . "' LIMIT 1";
			$matriz_convenio = mysql_query($preg_convenio) or die("ERROR: Fallo seleccion de convenio!" . $preg_convenio);
			$info_convenio = mysql_fetch_assoc($matriz_convenio);
			$convenio = $info_convenio['sub_aseguradora'];
			
			// --- Consultar nick del convenio ---
			$preg_nick_c = "SELECT aseguradora_nic FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $convenio . "' LIMIT 1";
			$matriz_nick_c = mysql_query($preg_nick_c) or die("ERROR: Fallo seleccion de NICK!" . $preg_nick_c);
			$info_nick_c = mysql_fetch_assoc($matriz_nick_c);
			
			if($info_nick_c['aseguradora_nic'] == 'AXA' || $info_nick_c['aseguradora_nic'] == 'Axa' || $info_nick_c['aseguradora_nic'] == 'AXA-UBER' || $info_nick_c['aseguradora_nic'] == 'axa'){
				$es_axa = 1;
			} else{
				$es_axa = 0;
			}
		} else{
			$reporte = 'Particular';
			$es_axa = 0;
		}  
		$orden = mysql_fetch_array($matriz);
		$orden_estatus = $orden['orden_estatus'];
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		include ('particular/garantia.php');
		echo '</td></tr>'."\n";
		echo '		<tr><td><div class="control">';
		if($_SESSION['rol06']=='1') {
			echo '<a href="javascript:window.print()">'. $lang['Imprimir Garantía'].'</a> | ';
		}
		echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '">'. $lang['Regresar a la OT'].'</a></div></td></tr>';
		echo '</table>';
	} else {
		if(!isset($dato) || $dato=='') {
			$preg0 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '189' GROUP BY sub_reporte";
   	  	$mat0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
     		$num_rep = mysql_num_rows($mat0);
			if ($num_rep > 1) {
				include('parciales/encabezado.php'); 
				echo '	<div id="body">';
				include('parciales/menu_inicio.php');
				echo '		<div id="principal">';
				echo '	<form action="entrega.php?accion=garantia" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="850">'."\n";
   		  	echo '		<tr><td colspan="2" style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">'. $lang['más de un siniestro o tipo de servicio, elija el reporte'].'</td></tr>' . "\n";
     			echo '		<tr><td colspan="2"><select name="dato" size="1">' . "\n";
				echo '			<option value="" >'.$lang['Seleccione'].'</option>';
		     	while($rep = mysql_fetch_array($mat0)) {
   		  		echo '			<option value="' . $rep['sub_reporte'] . '|' . $rep['sub_aseguradora'] . '">' . $rep['sub_reporte'] . '</option>' . "\n";
				}
				echo '		</select></td></tr>' . "\n";
				echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2"><input type="hidden" name="orden_id" value="' . $orden_id . '" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="'.$lang['Enviar'].'" /></td></tr>
		</tr>
	</table>
	</form>'."\n";
			} else {
				$rep = mysql_fetch_array($mat0);
				$dato = $rep['sub_reporte'] . '|' . $rep['sub_aseguradora'];
				redirigir('entrega.php?accion=garantia&orden_id=' . $orden_id . '&dato=' . $dato);
			}
		} else {
			$mensaje .= $lang['No hay registros con esos datos'];
			echo '<p>' . $mensaje . '</p>';
		}
	}
}

elseif ($accion==="cobros") {
	if (validaAcceso('1030060', $dbpfx) == 1 || $_SESSION['codigo'] <= '12' || ($solovalacc != '1' && ($_SESSION['rol06'] == '1' || $_SESSION['rol12'] == '1'))) {
		 $msg = $lang['Acceso autorizado'];
	} else {
		redirigir('usuarios.php?mensaje='. $lang['Acceso no autorizado']);
	}

	$preg = "SELECT orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
	$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
	$dato = mysql_fetch_array($matr);
	if($dato['orden_estatus']==99 || ($dato['orden_estatus'] > 0 && $dato['orden_estatus'] < 30)) {
	$preg0 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190' GROUP BY sub_reporte ORDER BY sub_reporte";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Tareas! " . $preg0);
	$fila0 = mysql_num_rows($matr0);
	if($fila0 > 0) {
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
		require_once("calendar/tc_calendar.php");
		$cf = ['9292FF','FFFF93','90EE90','ADD8E6','FFC0CB','FFA500','EBD5F9','#FE0000'];
		$vehiculo = datosVehiculo($orden_id, $dbpfx);
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="izquierda">'."\n";
		echo '		<tr><td colspan="4">OT: ' . $orden_id . ' ' . $vehiculo['marca'] . ' ' . $vehiculo['tipo'] . ' ' . $vehiculo['color'] . ' ' . $vehiculo['modelo'] . $lang['Placas'] . $vehiculo['placas'] . '</td></tr>'."\n";
		echo '		<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a></div></td></tr>'."\n";
		echo '	</table>'."\n";
		while($gru = mysql_fetch_array($matr0)) {
			if($gru['sub_reporte'] == '') { $gru['sub_reporte'] = '0'; } 
// ------ Recolección de datos para modificar presentación -------------------

			$preg2 = "SELECT * FROM " . $dbpfx . "facturas_por_cobrar WHERE orden_id = '$orden_id' AND reporte = '" . $gru['sub_reporte'] . "'";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Facturas por Cobrar!");
//			mysql_data_seek($matr2, 0);
			$facturas = array();
			$cobdoc = '';
			$j = 0;
			$totdoccob = 0;
			while($fact = mysql_fetch_array($matr2)) {
				if($fact['fact_tipo'] == '1') { $tipo_nom = $lang['Factura']; } 
				elseif($fact['fact_tipo'] == '2') { $tipo_nom = $lang['Comprobante Simplificado']; } 
//				$fact_total = round(($fact['fact_monto'] * 0.16), 2) + $fact['fact_monto'];
				$fact_total = $fact['fact_monto'];
				$fact_id[$fact['fact_id']]['numero'] = $fact['fact_num'];
				$fact_id[$fact['fact_id']]['fondo'] = $cf[$j];
				$fact_id[$fact['fact_id']]['monto'] = $fact['fact_monto'];
				$tpf = 0;
				$tareas = '';
				$preg5 = "SELECT sub_orden_id, sub_presupuesto, sub_impuesto FROM " . $dbpfx . "subordenes WHERE fact_id = '" . $fact['fact_id'] . "' AND sub_estatus < '190'";
				$matr5 = mysql_query($preg5) or die('ERROR: Falló seleción de tareas con factura!' . $preg5);
				while($tpfa = mysql_fetch_array($matr5)) {
					$tpf = $tpf + $tpfa['sub_presupuesto'] + $tpfa['sub_impuesto'];
					$tareas .= $tpfa['sub_orden_id'] . ', ';
				}

// ------ Obtención de datos de documentos de cobros (desasociar cobros) -------

				if($fact['fact_tipo'] < '3' && !is_null($fact['fact_fecha_recibida']) && $fact['fact_cobrada'] < '2') {
					$cobdoc .= '			<form action="entrega.php?accion=regcobro" method="post" enctype="multipart/form-data">'."\n";
					$cobdoc .= '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="100%" style="background-color: #'.$cf[$j].';">'."\n";
					$cobdoc .= '				<tr class="cabeza_tabla"><td colspan="9">' . $j . ' ' . $lang['Registro de Cobros'] . $tareas . $lang[' por un monto total de $'] . number_format($tpf,2) . $lang[' con los siguientes documentos:'] . '</td></tr>'."\n";
					$cobdoc .= '				<tr><td colspan="9"><strong>' . $tipo_nom . ' ' . $fact['fact_num'] . '</strong>' . $lang['programada para cobro'];
					$emision_fecha = date('d-m-Y', strtotime($fact['fact_fecha_programada']));
					$totdoccob = $totdoccob + $fact_total;
					$cobdoc .= $emision_fecha . $lang['por un total de'] . number_format($fact_total, 2) . '</td></tr>'."\n";
					$cobdoc .= '				<tr><td><div style="position: relative; display: inline-block;"><a onclick="AyudaQuitar()" class="ayuda">' . $lang['Quitar'] . '</a><div id="AyudaQuitar" class="muestra-contenido">' . $ayuda['AyudaQuitar'] . '</div></div></td><td>'. $lang['Num Cobro'] . '</td><td>' . $lang['Fecha'] . '</td><td>'. $lang['Forma de cobro']. '</td><td>' . $lang['Banco Origen'] . '</td><td>'. $lang['Referencia'].'</td><td>'. $lang['Cuenta de Cobro'].'</td><td>'. $lang['Documento'].'</td><td>'. $lang['Monto Cobrado'].'</td></tr>'."\n";

					echo '					<script>
						function AyudaQuitar() {
							document.getElementById("AyudaQuitar").classList.toggle("mostrar");
						}
					</script>'."\n";

					$preg3 = "SELECT c.*, cf.monto FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE cf.fact_id = '" . $fact['fact_id'] . "' AND cf.cobro_id = c.cobro_id";
					$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de Cobros!");
					$cobros = 0;
					while($cob = mysql_fetch_array($matr3)) {
						$cobros = $cobros + $cob['monto'];
						$cobdoc .= '				<tr><td><a href="entrega.php?accion=desasoc_cobros&cobro_id=' . $cob['cobro_id'] . '&orden_id=' . $orden_id . '&fact=' . $fact['fact_id'] . '&fact_num=' . $fact['fact_num'] . '"><img src="idiomas/' . $idioma . '/imagenes/go-bottom-4.png" alt="Desasociar" title="Desasociar"></a></td><td>' . $cob['cobro_id'] . '</td><td>';
						$cobro_fecha = strtotime($cob['cobro_fecha']);
						$cobro_fecha = date('d-m-Y', $cobro_fecha);
						$cobdoc .= $cobro_fecha . '</td><td>' . constant('TIPO_PAGO_'.$cob['cobro_metodo']) . '</td><td>' . $cob['cobro_banco'] . '</td><td>' . $cob['cobro_referencia'] . '</td><td>' . $cob['cobro_cuenta'] . '</td><td>';
						if($cob['cobro_documento'] != '') {
							$cobdoc .= '<a href="' . DIR_DOCS . $cob['cobro_documento'] . '" target="_blank"><img src="' . DIR_DOCS . 'documento.png"></a>';
						}
						$cobdoc .= '</td><td style="text-align:right;">$ ' . number_format($cob['monto'], 2) . '</td></tr>'."\n";
					}
					$mont_pc = round($fact_total, 2) - round($cobros, 2);
					$cobdoc .= '				<tr><td colspan="7">
					<input type="hidden" name="fact_total" value="' . $fact_total . '" />
					<input type="hidden" name="tipo" value="' . $fact['fact_tipo'] . '" />
					<input type="hidden" name="fact_id" value="' . $fact['fact_id'] . '" />
					<input type="hidden" name="reporte" value="' . $gru['sub_reporte'] . '" />
					<input type="hidden" name="numero" value="' . $fact['fact_num'] . '" />
					<input type="hidden" name="orden_id" value="' . $orden_id . '" />
					<input type="hidden" name="mont_pc" value="' . $mont_pc . '" />
					<input type="hidden" name="aseguradora" value="' . $gru['sub_aseguradora'] . '" />'."\n";
					// -------------------------
					if($valor['timbres'][0] > 0) {
						$cobdoc .= '<a class="btn" href="nota-de-credito.php?accion=consultar&orden_id=' . $orden_id . '&reporte=' . $gru['sub_reporte'] . '&numero=' . $fact['fact_num'] . '&fact_id=' . $fact['fact_id'] . '&mont_pc=' . $mont_pc .'">' . $lang['Crear Nota de Crédito'] . '</a>';
					} else {
						$cobdoc .= $lang['TimbresAgot'];
					}
					$cobdoc .= '&nbsp;';
					if($mont_pc > 0) {
						$cobdoc .= '<input type="submit" name="cobrar" value="'. $lang['Registrar cobro'] . ' ' . $tipo_nom . '" />';
					} else {
						$cobdoc .= '&nbsp;';
					}
					$cobdoc .= '</td><td>'. $lang['Por cobrar'].'</td><td style="text-align:right;">$ ' . number_format($mont_pc,2) . '</td></tr>'."\n";
					$facturas[] = $fact['fact_num'] . '|' . $mont_pc . '|' . $fact['fact_id'];

// ------ Presentar ajustes administrativos como descuentos y otro ajustes
					$preg6 = "SELECT * FROM " . $dbpfx . "ajusadmin WHERE fact_id = '" . $fact['fact_id'] . "'";
					$matr6 = mysql_query($preg6) or die("Error: falló selección de ajustes administrativos. " . $preg6);
					$taju = 0;
					while($aja = mysql_fetch_array($matr6)) {
						$cobdoc .= '				<tr><td colspan="8">' . $aja['motivo'] . ' Usuario:' . $aja['usuario'] . ' el ' . $aja['fecha_ajuste'];
						$cobdoc .= '</td><td style="text-align:right;">$ ' . number_format($aja['monto'],2) . '</td></tr>'."\n";
						$taju = $taju +  $aja['monto'];
					}
					$fact_id[$fact['fact_id']]['monto'] = $fact_id[$fact['fact_id']]['monto'] + $taju;
					if($tpf > $fact_id[$fact['fact_id']]['monto']) {
						$cobdoc .= '				<tr><td colspan="8" style="text-align:right;"><a href="entrega.php?accion=ajusadm&orden_id=' . $orden_id . '&reporte=' . $gru['sub_reporte'] . '&monto=' . ($tpf - $fact_id[$fact['fact_id']]['monto']) . '&doc=' . $fact['fact_id'] . '"><button type="button">'. $lang['Diferencia por Ajustar'] . '</button></a></td><td style="text-align:right;">$' . number_format(($tpf - $fact_id[$fact['fact_id']]['monto']), 2) . '</td></tr>'."\n";
					}
					$cobdoc .= '			</table></form>'."\n";
//					if($cobros == '0') {
						$cobdoc .= '			<form action="entrega.php?accion=borrafactura" method="post" enctype="multipart/form-data">'."\n";
						$cobdoc .= '			<table cellpadding="2" cellspacing="2" border="0" class="agrega" width="100%" style="background-color: #'.$cf[$j].';">'."\n";
						$cobdoc .= '				<tr><td colspan="8">' . $lang['Borrar Factura'] . $tipo_nom . $lang['PorFavorMarq'] . '<input type="checkbox" name="borrafact" />
						 <input type="hidden" name="fact_id" value="' . $fact['fact_id'] . '" />
						 <input type="hidden" name="orden_id" value="' . $orden_id . '" />
						 <input type="hidden" name="reporte" value="' . $gru['sub_reporte'] . '" />
						 &nbsp;<input type="submit" name="Borrar" value="'. $lang['Borrar'] . ' ' . $tipo_nom . '" />'."\n";
						 $cobdoc .= '			</td></tr></table></form>'."\n";
//					}
				} elseif($fact['fact_tipo'] < '3' && is_null($fact['fact_fecha_recibida']) && $fact['fact_cobrada'] < 2) {
					$cobdoc .= '			<form action="entrega.php?accion=regfactrec" method="post" enctype="multipart/form-data">'."\n";
					$cobdoc .= '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="100%" style="background-color: #'.$cf[$j].';">'."\n";
					$cobdoc .= '				<tr class="cabeza_tabla"><td colspan="8">'. $lang['Registro de Recepción'] . $tipo_nom . ' ' . $fact['fact_num'] . $lang['Emitida el'];
					$emision_fecha = strtotime($fact['fact_fecha_emision']);
					$emision_fecha = date('d-m-Y', $emision_fecha);
					$cobdoc .= $emision_fecha . $lang['por un total de'] . number_format($fact_total, 2) . '</td></tr>'."\n";
					$cobdoc .= '				<tr><td colspan="8">
					<input type="hidden" name="tipo" value="' . $fact['fact_tipo'] . '" />
					<input type="hidden" name="fact_id" value="' . $fact['fact_id'] . '" />
					<input type="hidden" name="reporte" value="' . $gru['sub_reporte'] . '" />
					<input type="hidden" name="numero" value="' . $fact['fact_num'] . '" />
					<input type="hidden" name="orden_id" value="' . $orden_id . '" />'."\n";
					$cobdoc .= '<input type="submit" name="cobrar" value="'. $lang['Registra recibo y fecha de pago de Factura'].'" />';
					$cobdoc .= '</td></tr>'."\n";
					$cobdoc .= '			</table></form>'."\n";
					$cobdoc .= '			<form action="entrega.php?accion=borrafactura" method="post" enctype="multipart/form-data">'."\n";
					$cobdoc .= '			<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="100%" style="background-color: #'.$cf[$j].';">'."\n";
					$cobdoc .= '				<tr><td colspan="8">' . $lang['Borrar Factura'] . $tipo_nom . $lang['PorFavorMarq'] . '<input type="checkbox" name="borrafact" />
						<input type="hidden" name="fact_id" value="' . $fact['fact_id'] . '" />
						<input type="hidden" name="orden_id" value="' . $orden_id . '" />
						<input type="hidden" name="reporte" value="' . $gru['sub_reporte'] . '" />
						&nbsp;<input type="submit" name="Borrar" value="'. $lang['Borrar'] . ' ' . $tipo_nom . '" />'."\n";
					$cobdoc .= '			</td></tr></table></form>'."\n";
				}
				$j++;
			}

// ----------- Selección de cobros huerfanos ----------------------------------------------
			if($gru['sub_aseguradora'] == 0) {
				$preg_clie = "SELECT orden_cliente_id FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $orden_id . "'";
				$matr_clie = mysql_query($preg_clie) or die("ERROR: Fallo selección de orden");
				$cliente = mysql_fetch_array($matr_clie);
				$cliente_id = $cliente['orden_cliente_id'];
				$preg3 = "SELECT c.cobro_id, c.cobro_id, c.cobro_fecha, c.cobro_metodo, c. cobro_banco, c.cobro_cuenta, c.cobro_referencia, c.cobro_documento, cf.monto, cf.cf_id FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE cf.cliente_id = '" . $cliente_id . "' AND (cf.fact_id < 1 OR cf.fact_id IS NULL) AND c.cobro_id = cf.cobro_id";
			} else{
				$preg3 = "SELECT c.cobro_id, c.cobro_id, c.cobro_fecha, c.cobro_metodo, c. cobro_banco, c.cobro_cuenta, c.cobro_referencia, c.cobro_documento, cf.monto, cf.cf_id FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE cf.aseguradora_id = '" . $gru['sub_aseguradora'] . "' AND (cf.fact_id < 1 OR cf.fact_id IS NULL) AND c.cobro_id = cf.cobro_id";
			} 

//  ------- Pregunta original -----------			
//			$preg3 = "SELECT c.* FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE cf.orden_id = '$orden_id' AND (cf.fact_id < 1 OR cf.fact_id IS NULL) AND c.cobro_id = cf.cobro_id";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de Cobros!".$preg3);
//			echo $preg3;
			$fila3 = mysql_num_rows($matr3);
			if($fila3 > 0) {
				$cobdoc .= '		<form action="entrega.php?accion=asociarant" method="post" enctype="multipart/form-data">'."\n";
				$cobdoc .= '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="100%">'."\n";
				$cobdoc .= '				<tr class="cabeza_tabla"><td colspan="10">Asociar <div style="position: relative; display: inline-block;"><a onclick="Huerfanos()" class="ayuda">' . $lang['cobros huerfanos'] . '</a><div id="Huerfanos" class="muestra-contenido">' . $ayuda['Huerfanos'] . '</div></div> a Facturas por Cobrar</td></tr>'."\n";

				echo '					<script>
						function Huerfanos() {
							document.getElementById("Huerfanos").classList.toggle("mostrar");
						}
					</script>'."\n";

				$cobdoc .= '			<tr><td><div style="position: relative; display: inline-block;"><a onclick="Eliminar()" class="ayuda">' . $lang['Eliminar'] . '</a><div id="Eliminar" class="muestra-contenido">' . $ayuda['Eliminar'] . '</div></div></td><td>' . $lang['Cobro'] . '</td><td>' . $lang['Fecha'] . '</td><td>' . $lang['Forma de pago'] . '</td><td>' . $lang['Banco'] . '</td><td>' . $lang['Cuenta'] . '</td><td>' . $lang['Referencia'] . '</td><td>' . $lang['Documento'] . '</td><td>' . $lang['Monto'] . '</td><td>' . $lang['Asignar a FID'] . '</td></tr>'."\n";

				echo '					<script>
						function Eliminar() {
							document.getElementById("Eliminar").classList.toggle("mostrar");
						}
					</script>'."\n";

				$cobrado = 0;
				$fondo = 'claro';
				// ------ Cobros ---------------------
  				while($cob = mysql_fetch_array($matr3)) {
  					$cobdoc .= '			<tr><td><a href="entrega.php?accion=eliminar_cobro&cobro_id=' . $cob['cobro_id'] . '&orden_id=' . $orden_id . '&monto=' . $cob['monto'] . '&cuenta=' . $cob['cobro_cuenta'] . '&banco=' . $cob['cobro_banco'] . '&referencia=' . $cob['cobro_referencia'] . '&monto_fact=' . $fact_total . '"><img src="idiomas/' . $idioma . '/imagenes/edit-delete-6.png" alt="Eliminar pago adelantado" title="Eliminar cobro"></a></td><td>' . $cob['cobro_id'] . '</td><td>' . date('d/m/Y', strtotime($cob['cobro_fecha'])) . '</td><td>' . constant('TIPO_PAGO_' . $cob['cobro_metodo']) . '</td><td>' . $cob['cobro_banco'] . '</td><td>' . $cob['cobro_cuenta'] . '</td><td>' . $cob['cobro_referencia'] . '</td><td>';
					if($cob['cobro_documento'] != '') {
						$cobdoc .= '<a href="' . DIR_DOCS . $cob['cobro_documento'] . '" target="_blank"><img src="' . DIR_DOCS . 'documento.png" width="48" border="0"></a>'; 
					} 
					$cobdoc .= '</td><td style="text-align: right;">$ ' . number_format($cob['monto'], 2) . '</td>'."\n";
					$cobdoc .= '					<td><select name="fid[]" /><option value="0|0|0">' . $lang['Selecciona Factura'] . '</option>'."\n";
					foreach($facturas as $k => $v) {
						$ff = explode('|', $v);
						$cobdoc .= '						<option value="' . $v . '">' . $ff[0] . '</option>'."\n";
					}
					$cobdoc .= '					</select><input type="hidden" name="cf_id[]" value="' . $cob['cf_id'] . '" /><input type="hidden" name="cobro_monto[]" value="' . $cob['monto'] . '" /><input type="hidden" name="cobro_id[]" value="' . $cob['cobro_id'] . '"></td></tr>'."\n";
				}
				if(count($facturas) > 0) {
					$cobdoc .= '				<tr><td colspan="10"><input type="submit" name="pagar" value="' . $lang['Asociar cobros con facturas'] . '" />';
					$cobdoc .= '<input type="hidden" name="orden_id" value="' . $orden_id . '">';
					$cobdoc .= '</td></tr>'."\n";
				}
				$cobdoc .= '			</table></form>'."\n";
			}

// ------ Obtener información de Tareas y ajustes administrativos para cada Trabajo.

			$cobpres = '';
			$cobpres .= '			<form action="entrega.php?accion=cobros" id="adtar' . $gru['sub_reporte'] . '" name="adtar' . $gru['sub_reporte'] . '" method="post" enctype="multipart/form-data">'."\n";
			$cobpres .= '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="100%">
				<tr class="cabeza_tabla"><td colspan="5">'. $lang['Resumen de Tareas por Cobrar'].'<input type="hidden" name="orden_id" value="' . $orden_id . '" /></td></tr>'."\n";
			$preg1 = "SELECT sub_orden_id, sub_reporte, sub_aseguradora, sub_area, sub_presupuesto, sub_impuesto, sub_descripcion, sub_deducible, fact_id FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '" . $gru['sub_reporte'] . "' AND sub_estatus < '190' AND sub_presupuesto != '0' ORDER BY sub_area";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Tareas! " . $preg1);
			$pres = 0;
			$iva = 0;
			$dedu = 0;
			$total_sub = 0;
			$prsnt = 0;
			$cobpres .= '				<tr><td>' . $lang['Tarea'] . '</td><td>' . $lang['Área'] . '</td><td>' . $lang['Descripción'] . '</td><td>' . $lang['Agregar'] . '<br>' . $lang['a Cobro'] . '</td><td>' . $lang['Montos'] . '</td></tr>'."\n";
			while($sub = mysql_fetch_array($matr1)) {
				$cobpres .= '				<tr';
				if($fact_id[$sub['fact_id']]['fondo'] != '') {
					$cobpres .= ' style="background-color:#' . $fact_id[$sub['fact_id']]['fondo'] .';"';
				}
				$cobpres .= '><td>' . $sub['sub_orden_id'] . '</td><td>' . constant('NOMBRE_AREA_'.$sub['sub_area']) . '</td><td>' . $sub['sub_descripcion'] . '</td>';
				if($gru['sub_reporte']== '0' && $pciva == '1') {
					$subitem = round(($sub['sub_presupuesto'] / (1 + $impuesto_iva)), 2);
				} else {
					$subitem = $sub['sub_presupuesto']; 
				}
				if($sub['fact_id'] < '1' || is_null($sub['fact_id'])) {
					$cobpres .= '<td><input type="checkbox" name="tarea[' . $gru['sub_reporte'] . '][' . $sub['sub_orden_id'] . ']" value="'.$subitem.'" ';
					if($tarea[$gru['sub_reporte']][$sub['sub_orden_id']] == $subitem) { $cobpres .= 'checked="checked" '; }
					$cobpres .= ' onchange="document.adtar' . $gru['sub_reporte'] . '.submit()" /></td>';
				} else {
					$cobpres .= '<td>' . $fact_id[$sub['fact_id']]['numero'] . '</td>';
				}
				$cobpres .= '<td style="text-align:right;">$' . number_format($subitem, 2) . '</td></tr>'."\n";
				$aseguradora = $sub['sub_aseguradora'];
				if($sub['sub_deducible'] > $dedu) { $dedu = $sub['sub_deducible']; }
				$subtotal = $subtotal + $subitem;
				$impuestos = $impuestos + $sub['sub_impuesto'];
				$fact_id[$sub['fact_id']]['subtotal'] = $fact_id[$sub['fact_id']]['subtotal'] + $subitem + $sub['sub_impuesto'];
			}
			$tarfact = '';
			foreach($tarea[$gru['sub_reporte']] as $k => $v) {
					$pres = $pres + $v;
					$tarfact .= '				<input type="hidden" name="tarfact[]" value="' . $k . '" />'."\n";
			}
			$cobpres .= '				<tr><td colspan="4" style="text-align:right;">' . $lang['Subtotal'].'</td><td style="text-align:right;">$' . number_format($pres, 2) . '</td></tr>'."\n";
			$cobpres .= '				<tr><td colspan="5" style="text-align:right;">' . $lang['Tipo de Cobro: '] . $lang['Factura'] . '<input type="radio" name="fact_tipo[' . $gru['sub_reporte'] . ']" value="1"';
			if($fact_tipo[$gru['sub_reporte']] != '2') { $cobpres .= ' checked="checked" '; }
			$cobpres .= ' onchange="document.adtar' . $gru['sub_reporte'] . '.submit()"/> | '.$lang['Comprobante Simplificado'].'<input type="radio" name="fact_tipo[' . $gru['sub_reporte'] . ']" value="2"';
			if($fact_tipo[$gru['sub_reporte']] == '2') { $cobpres .= ' checked="checked" '; }
			$cobpres .= ' onchange="document.adtar' . $gru['sub_reporte'] . '.submit()"/></td></tr>'."\n";
			if($fact_tipo[$gru['sub_reporte']] != '2') { 
				$coniva[$gru['sub_reporte']] = $impuesto_iva; 
			} else {
				$coniva[$gru['sub_reporte']] = '0';
			}
			$iva = round(($pres * $coniva[$gru['sub_reporte']]), 2);
			$cobpres .= '				<tr><td colspan="4" style="text-align:right;">' . $lang['IVA'] . ' ' . ($coniva[$gru['sub_reporte']] * 100) . '%</td><td style="text-align:right;">$' . number_format($iva, 2) . '</td></tr>'."\n";
			$total_sub = $pres + $iva;
			$cobpres .= '				<tr><td colspan="4" style="text-align:right;"><input type="submit" name="Recalcular" value="'. $lang['Recalcular'].'">'. $lang['Total'].'</td><td style="text-align:right;">$' . number_format($total_sub, 2) . '</td></tr>'."\n";

// ------ Calculando si el total requiere un ajuste.
/*			
			$totconimp = $subtotal + $impuestos;
			$cobpres .= '				<tr class="cabeza_tabla"><td colspan="5" style="text-align:right;">' . $lang['Total Neto a Cobrar'] . ' $' . number_format($totconimp,2) . '</td></tr>'."\n";
			if($totconimp > $totdoccob) {
				foreach($fact_id as $fk => $fv) {
//					print_r($fv);
					if($fv['monto'] < $fv['subtotal']) {
						$cobpres .= '				<tr><td colspan="4" style="text-align:right;"><a href="entrega.php?accion=ajusadm&orden_id=' . $orden_id . '&reporte=' . $gru['sub_reporte'] . '&monto=' . ($totconimp - $totdoccob) . '&doc=' . $fk . '"><button type="button">'. $lang['Diferencia por Ajustar'] . ' ' . $fv['numero'] . ' por:</button></a></td><td style="text-align:right;">$' . number_format(($totconimp - $totdoccob), 2) . '</td></tr>'."\n";
					}
				}
			}
*/
			$cobpres .= '			</table></form>'."\n";

			$revisa = 0;
			
//			$preg3 = "SELECT * FROM " . $dbpfx . "cobros WHERE orden_id = '$orden_id' AND reporte = '" . $gru['sub_reporte'] . "'";
//			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de Cobros!");
/*			while($fact = mysql_fetch_array($matr2)) {
				$preg4 = "SELECT * FROM " . $dbpfx . "cobros WHERE fact_id = '" . $fact['fact_id'] . "' AND cobro_tipo < '3'";
				$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de Cobros!");
				while($cob = mysql_fetch_array($matr4)) {
					if($cob['fact_id'] == $fact['fact_id'] ) {
						$revisa = $revisa + $cob['cobro_monto']; 
					}
//						$cobpres .= $cob['fact_id'] . ' ==> ' . $fact['fact_id'] . '<br>';
				}
				
				if($fact['fact_cobrada'] < 2 && $fact['fact_tipo'] < 3 ) {
					$revisa = $revisa + $fact['fact_monto'];
				}
				
			}
//			$revisa = $revisa - 0.01;
			$cobpres .= $revisa . ' - ' . $total_sub;
*/
			if($total_sub > 0) {
//				$por_facturar = ($total_sub - $revisa);
				$cobpres .= '			<form action="entrega.php?accion=regfact" method="post" enctype="multipart/form-data">'."\n";
				$cobpres .= '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="100%">'."\n";
				$cobpres .= '				<tr class="cabeza_tabla"><td colspan="2">'. $lang['Factura externa XML CFDi'].'.<br>' . $lang['Monto por facturar: $'] . number_format($total_sub,2) . ' <input type="text" name="fact_monto" value="' . number_format($total_sub,2) . '" size="8" style="text-align:right;"></td></tr>
				<tr><td style="text-align:right;">'. $lang['Archivo XML'].'</td><td><input type="file" name="cfdi" size="12" /></td></tr>'."\n";
				$cobpres .= '				<tr><td style="text-align:right;">' . $lang['Número de Documento'] . '</td><td><input type="text" name="fact_num" size="12" /></td></tr>
				<tr><td style="text-align:right;">' . $lang['Fecha de emisión'] . '</td><td>';

				//instantiate class and set properties
/*				require_once("calendar/tc_calendar.php");
				$ahora = date('Y-m-d', time());
//				$cobpres .= $ahora;
				$myCalendar = new tc_calendar("emision", true);
				$myCalendar->setPath("calendar/");
				$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
				$myCalendar->setDate(date("d"), date("m"), date("Y"));
//				$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
				$myCalendar->disabledDay("sun");
				$myCalendar->setYearInterval(2012, 2020);
				$myCalendar->setAutoHide(true, 5000);

//				output the calendar
				$cobpres .= $myCalendar->writeScript();
*/
				$cobpres .= '					<input type="text" name="emyear" value="' . date("Y") . '" size="2"> <input type="text" name="emmes" value="' . date("m") . '" size="2"> <input type="text" name="emdia" value="' . date("d") . '" size="2">'."\n";
				$cobpres .= '				</td></tr>'."\n";
				$cobpres .= '				<tr><td colspan="2" style="text-align:right;"><input type="submit" name="registro" value="'. $lang['Registrar Factura'].'" />
				<input type="hidden" name="orden_id" value="' . $orden_id . '" />
				<input type="hidden" name="nombre" value="' . $nombre . '" />
				<input type="hidden" name="reporte" value="' . $gru['sub_reporte'] . '" />
				<input type="hidden" name="aseguradora" value="' . $gru['sub_aseguradora'] . '" />
				<input type="hidden" name="total_sub" value="' . $total_sub . '" />
				<input type="hidden" name="fact_tipo" value="' . $fact_tipo[$gru['sub_reporte']] . '" />
				<input type="hidden" name="por_facturar" value="' . $total_sub . '" />'."\n"; 
				$cobpres .= $tarfact;
				$cobpres .= '				</td></tr>'."\n";
				$cobpres .= '			</table></form>'."\n";
			}


// ------ Obtener datos de Deducibles --------------------
			$cobdedu = '';
			if($dedu > 0) {
				$cobdedu .= '		<tr><td colspan="1" style="vertical-align:top;">'."\n";
				$cobdedu .= '			<form action="entrega.php?accion=regfact" method="post" enctype="multipart/form-data">'."\n";
				$cobdedu .= '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda">'."\n";
				$cobdedu .= '				<tr class="cabeza_tabla"><td colspan="2">'. $lang['número de recibo de Deducible'].'</td></tr>
				<tr><td style="text-align:right;">'. $lang['Monto Deducible'].'</td><td><input type="hidden" name="fact_monto" value="' . $dedu . '" />$' . number_format($dedu, 2) . '</td></tr>';
				$revisa = 0;
				mysql_data_seek($matr2, 0);
				while($fact = mysql_fetch_array($matr2)) {
					$preg4 = "SELECT c.cobro_monto FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE cf.fact_id = '" . $fact['fact_id'] . "' AND cf.fact_id = c.fact_id";
					$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de Cobros!");
					while($cob = mysql_fetch_array($matr4)) {
						$revisa = $revisa + $cob['cobro_monto']; 
//						$cobdedu .= $cob['fact_id'] . ' ==> ' . $fact['fact_id'] . '<br>';
					}
				}
//				$revisa = $revisa - 0.01;
//				$cobdedu .= $revisa . ' - ' . $total_sub;
				if($revisa < $dedu) { 
					$cobdedu .= '				<tr><td style="text-align:right;">'. $lang['Número de Recibo'].'</td><td><input type="text" name="fact_num" size="12" /><input type="hidden" name="fact_tipo" value="3" /><input type="hidden" name="por_facturar" value="' . ($dedu - $revisa) . '" /></td></tr>
				<tr><td style="text-align:right;">'. $lang['Fecha de emisión'].'</td><td>'."\n";

//				instantiate class and set properties
					$ahora = date('Y-m-d', time());
//					$cobdedu .= $ahora;
/*					$myCalendar = new tc_calendar("emision", true);
					$myCalendar->setPath("calendar/");
					$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
					$myCalendar->setDate(date("d"), date("m"), date("Y"));
//					$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
					$myCalendar->disabledDay("sun");
					$myCalendar->setYearInterval(2012, 2020);
					$myCalendar->setAutoHide(true, 5000);

//					output the calendar
					$myCalendar->writeScript();
*/

					$cobdedu .= '					<input type="text" name="emyear" value="' . date("Y") . '" size="2"> <input type="text" name="emmes" value="' . date("m") . '" size="2"> <input type="text" name="emdia" value="' . date("d") . '" size="2">'."\n";
					$cobdedu .= '				</td></tr>'."\n";
					$cobdedu .= '				<tr><td colspan="2" style="text-align:right;">
					<input type="submit" name="registro" value="'. $lang['Registrar recibo de Deducible'].'" />
					<input type="hidden" name="orden_id" value="' . $orden_id . '" />
					<input type="hidden" name="nombre" value="' . $nombre . '" />
					<input type="hidden" name="reporte" value="' . $gru['sub_reporte'] . '" />
					<input type="hidden" name="aseguradora" value="' . $gru['sub_aseguradora'] . '" />
					</td></tr>'."\n";
				}
				$cobdedu .= '			</table></form>'."\n";
				$cobdedu .= '		</td><td style="vertical-align:top;">'."\n";
				mysql_data_seek($matr2, 0);
				while($fact = mysql_fetch_array($matr2)) {
					if($fact['fact_tipo'] == '3' && $fact['fact_cobrada'] < 2) {
						$fact_total =$dedu;
						$tipo_nom = 'Deducible';
						$cobdedu .= '			<form action="entrega.php?accion=regcobro" method="post" enctype="multipart/form-data">'."\n";
						$cobdedu .= '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda">'."\n";
						$cobdedu .= '				<tr class="cabeza_tabla"><td colspan="8">'. $lang['Registro de Cobros'] . $tipo_nom . ' ' . $fact['fact_num'] . $lang['Emitida el'];
						$emision_fecha = strtotime($fact['fact_fecha_emision']);
						$emision_fecha = date('d-m-Y', $emision_fecha);
						$cobdedu .= $emision_fecha . $lang['por un total de'] . number_format($fact_total, 2) . '</td></tr>'."\n";
						$cobdedu .= '				<tr><td>'. $lang['Num Cobro'].'</td><td>'. $lang['Fecha'].'</td><td>'. $lang['Forma de cobro'].'</td><td>'. $lang['Banco Origen'].'</td><td>'. $lang['Referencia'].'</td><td>'. $lang['Cuenta de Cobro'].'</td><td>'. $lang['Documento'].'</td><td>'. $lang['Monto Cobrado'].'</td></tr>'."\n";
						$preg3 = "SELECT c.*, cf.monto FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE cf.fact_id = '" . $fact['fact_id'] . "' AND cf.cobro_id = c.cobro_id";
						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de Cobros!");
						$cobros = 0;
						while($cob = mysql_fetch_array($matr3)) {
								$cobros = $cobros + $cob['monto'];
								$cobdedu .= '				<tr><td>' . $cob['cobro_id'] . '</td><td>';
								$cobro_fecha = strtotime($cob['cobro_fecha']);
								$cobro_fecha = date('d-m-Y', $cobro_fecha);
								$cobdedu .= $cobro_fecha . '</td><td>' . constant('TIPO_PAGO_'.$cob['cobro_metodo']) . '</td><td>' . $cob['cobro_banco'] . '</td><td>' . $cob['cobro_referencia'] . '</td><td>' . $cob['cobro_cuenta'] . '</td><td>' . $cob['cobro_documento'] . '</td><td style="text-align:right;">$ ' . number_format($cob['monto'],2) . '</td></tr>'."\n";
						}
						$mont_pc = $fact_total - $cobros;
						$cobdedu .= '				<tr><td colspan="6">
						<input type="hidden" name="tipo" value="' . $fact['fact_tipo'] . '" />
						<input type="hidden" name="fact_id" value="' . $fact['fact_id'] . '" />
						<input type="hidden" name="reporte" value="' . $gru['sub_reporte'] . '" />
						<input type="hidden" name="numero" value="' . $fact['fact_num'] . '" />
						<input type="hidden" name="orden_id" value="' . $orden_id . '" />
						<input type="hidden" name="mont_pc" value="' . $mont_pc . '" />'."\n";
						if($mont_pc > 0) {
							$cobdedu .= '<input type="submit" name="cobrar" value="' . $lang['Registrar cobro'] . ' ' . $tipo_nom . '" />';
						} else {
							$cobdedu .= '<a href="entrega.php?accion=deducible&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/carta-deducible.png" alt="Carta de Deducible" title="Carta de Deducible"></a>';
						}
						$cobdedu .= '</td><td>'. $lang['Por cobrar'].'</td><td style="text-align:right;">$ ' . number_format($mont_pc,2) . '</td></tr>'."\n";
						$cobdedu .= '			</table></form>'."\n";
						if($cobros == '0') {
							$cobdedu .= '			<form action="entrega.php?accion=borrafactura" method="post" enctype="multipart/form-data">'."\n";
							$cobdedu .= $lang['Borrar Factura'] . $tipo_nom . $lang['PorFavorMarq'] . '<input type="checkbox" name="borrafact" />
							<input type="hidden" name="fact_id" value="' . $fact['fact_id'] . '" />
							<input type="hidden" name="orden_id" value="' . $orden_id . '" />
							<input type="hidden" name="reporte" value="' . $gru['sub_reporte'] . '" />
							&nbsp;<input type="submit" name="Borrar" value="'. $lang['Borrar'] . ' ' . $tipo_nom . '" />'."\n";
							$cobdedu .= '			</form>'."\n";
						}
					}
				}
			}
//			echo '		<tr><td colspan="2">&nbsp;</td></tr>'."\n";

// ------ Presentación de Datos de Cobros	---------
			$nombre = $ase[$gru['sub_aseguradora']]['nic'];
			echo '	<table cellpadding="0" cellspacing="0" border="1" class="agrega">';
			echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:center; font-size:16px;">'. $lang['Conceptos a cobrar'] . $nombre;
			if($gru['sub_reporte'] != '0') { echo $lang['del Siniestro'] . $gru['sub_reporte']; }
			echo '</td></tr>'."\n";
			echo '		<tr><td style="vertical-align:top;">' . $cobpres . '</td><td style="vertical-align:top;">' . $cobdoc . '</td></tr>'."\n";
//			echo '		<tr></tr>'."\n";
			echo '		<tr><td colspan="2" style="vertical-align:top;">' . $cobdedu . '</td></tr>'."\n";
			echo '	</table><hr>';
		}
		echo '		<div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a></div>'."\n";

		} else {
			$_SESSION['msjerror'] = $lang['no tiene tareas no registra cobros'];
			redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
		}
	} else {
		$_SESSION['msjerror'] =  $lang['OT no terminada no registrar cobros'];
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
}

elseif ($accion === "eliminar_cobro") {

	if (validaAcceso('1030115', $dbpfx) == 1) {
		// --- Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccesoPermiso'];
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id );
	}

	if($eliminar == 1) {
			$bitacora = 'Eliminé el cobro ' . $cobro_id . ', banco ' . $banco . ', cuenta ' . $cuenta . ', referencia ' . $referencia . ' de la orden ' . $orden_id . ' monto de ' . $monto;
			bitacora($orden_id, $bitacora, $dbpfx);
			$parametros = " cobro_id ='" . $cobro_id . "'";
			ejecutar_db($dbpfx . 'cobros', '', 'eliminar', $parametros);
			ejecutar_db($dbpfx . 'cobros_facturas', '', 'eliminar', $parametros);
			redirigir("entrega.php?accion=cobros&orden_id=" . $orden_id);
	} else {
		$preg_asoc = "SELECT cobro_id, fact_id, orden_id FROM " . $dbpfx . "cobros_facturas WHERE cobro_id = '" . $cobro_id . "' AND fact_id > '0'";
		$matr_asoc = mysql_query($preg_asoc) or die("ERROR: Fallo seleccion de cobros asociados! " . $preg_asoc);
		$num_asoc = mysql_num_rows($matr_asoc);
//		echo $num_asoc . ' ' . $preg_asoc;
		if($num_asoc > 0){
			$mensaje =  'EL cobro ' . $cobro_id . ' también se encuentra asociado a';
			while($cobros = mysql_fetch_array($matr_asoc)){
				$preg_info = "SELECT fact_num, orden_id FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '" . $cobros['fact_id'] . "'";
				$matr_info = mysql_query($preg_info) or die("ERROR: Fallo seleccion de informacion! " . $preg_info);
				//echo $preg_info;
				$info = mysql_fetch_array($matr_info);
				$mensaje .= ' la factura ' . $info['fact_num'] . ' en la orden ' . $cobros['orden_id'] . ',';
			}
			$_SESSION['msjerror'] = $mensaje;
			redirigir("entrega.php?accion=cobros&orden_id=" . $orden_id);
		} else {
			echo '
			<h2>¿Estás seguro que quieres eliminar el cobro ' . $cobro_id . '?</h2>'."\n";
			echo '
			<table>
				<tr>
					<td><a href="entrega.php?accion=eliminar_cobro&cobro_id=' . $cobro_id . '&eliminar=1&orden_id=' . $orden_id . '&monto=' . $monto . '&banco=' . $banco . '&cuenta=' . $cuenta . '&referencia=' . $referencia . '"><button type="button" class="btn btn-success">SI, eliminar cobro</button></a></td>
					<td><a href="entrega.php?accion=cobros&orden_id=' . $orden_id . '"><button type="button" class="btn btn-danger">NO, regresar</button></a></td>
				</tr>
			</table>'."\n";
		}
	}
}

elseif ($accion === "desasoc_cobros") {

	if (validaAcceso('1030115', $dbpfx) == 1) {
// --- Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccesoPermiso'];
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id );
	}

	if($eliminar == 1) {
		
		if($grupal == 1){ // --- Se debe de desasociar de todas las facturas a las que este ligado ---
			
			// --- Consultar los cobros ---
			$preg_asoc = "SELECT cobro_id, fact_id, orden_id FROM " . $dbpfx . "cobros_facturas WHERE cobro_id = '" . $cobro_id . "' AND fact_id > '0'";
			$matr_asoc = mysql_query($preg_asoc) or die("ERROR: Fallo seleccion de cobros asociados! " . $preg_asoc);
			$num_asoc = mysql_num_rows($matr_asoc);
			
			$mensaje = "Se desasociaron los cobros en: ";
			
			while($cobros = mysql_fetch_array($matr_asoc)){
				
				// --- Seleccionar la factura ---
				$preg_info = "SELECT fact_num, orden_id, fact_id FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '" . $cobros['fact_id'] . "'";
				$matr_info = mysql_query($preg_info) or die("ERROR: Fallo seleccion de informacion! " . $preg_info);
				$info = mysql_fetch_array($matr_info);
				
				// --- desasociar cobros de "cobros y cobros facturas" ------ 
				$sql_data_array = [
					'fact_id' => 'null',
					//'orden_id' => 'null',
				];
				$parametros = " cobro_id ='" . $cobros['cobro_id'] . "' AND fact_id = '" . $info['fact_id'] . "'";
				ejecutar_db($dbpfx . 'cobros_facturas', $sql_data_array, 'actualizar', $parametros);
				
				// --- desasociar cobros de "facturas por cobrar" ------
				unset($sql_data_array);
				$sql_data_array = [
					'fact_cobrada' => 0,
					'fact_fecha_cobrada' => 'null',
				];
				$parametros = " fact_id ='" . $info['fact_id'] . "'";
				ejecutar_db($dbpfx . 'facturas_por_cobrar', $sql_data_array, 'actualizar', $parametros);
				bitacora($orden_id, 'Quité el cobro ' . $cobros['cobro_id'] . ' de la factura ' . $info['fact_id'], $dbpfx);
				
				$mensaje .= ' la factura ' . $info['fact_num'] . ' en la orden ' . $cobros['orden_id'] . ',';
			}
			 
			$_SESSION['msjerror'] = $mensaje;
			redirigir("entrega.php?accion=cobros&orden_id=" . $orden_id);
			
		} else{
			// --- desasociar cobros de "cobros y cobros facturas" ------ 
			$sql_data_array = [
				'fact_id' => 'null',
				//'orden_id' => 'null',
			];
			$parametros = " cobro_id ='" . $cobro_id . "' AND fact_id = '" . $fact . "'";
			ejecutar_db($dbpfx . 'cobros_facturas', $sql_data_array, 'actualizar', $parametros);
			// --- desasociar cobros de "facturas por cobrar" ------
			unset($sql_data_array);
			$sql_data_array = [
				'fact_cobrada' => 0,
				'fact_fecha_cobrada' => 'null',
			];
			$parametros = " fact_id ='" . $fact . "'";
			ejecutar_db($dbpfx . 'facturas_por_cobrar', $sql_data_array, 'actualizar', $parametros);
			bitacora($orden_id, 'Quité el cobro ' . $cobro_id . ' de la factura ' . $fact, $dbpfx);
			redirigir("entrega.php?accion=cobros&orden_id=" . $orden_id);
		}
		
	} else {
		
		echo '
		<h2>¿Quieres desasociar el cobro ' . $cobro_id . ' de la factura ' . $fact_num . '?</h2>
		<table>
			<tr>
				<td><a href="entrega.php?accion=desasoc_cobros&cobro_id=' . $cobro_id . '&eliminar=1&fact=' . $fact . '&orden_id=' . $orden_id . '"><button type="button" class="btn btn-success">Sí, desacociar cobro</button></a></td>
				<td><a href="entrega.php?accion=cobros&orden_id=' . $orden_id . '"><button type="button" class="btn btn-danger">No, regresar</button></a></td>
			</tr>
		</table>'."\n";
		
		// --- Consultar si es un cobro multiple ---
		$preg_asoc = "SELECT cobro_id, fact_id, orden_id FROM " . $dbpfx . "cobros_facturas WHERE cobro_id = '" . $cobro_id . "' AND fact_id > '0'";
		$matr_asoc = mysql_query($preg_asoc) or die("ERROR: Fallo seleccion de cobros asociados! " . $preg_asoc);
		$num_asoc = mysql_num_rows($matr_asoc);
		//echo $num_asoc . ' ' . $preg_asoc;
		if($num_asoc > 1){
			
			$mensaje =  'EL cobro ' . $cobro_id . ' es multiple y se encuentra asociado a';
			
			while($cobros = mysql_fetch_array($matr_asoc)){
				$preg_info = "SELECT fact_num, orden_id FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '" . $cobros['fact_id'] . "'";
				$matr_info = mysql_query($preg_info) or die("ERROR: Fallo seleccion de informacion! " . $preg_info);
				//echo $preg_info;
				$info = mysql_fetch_array($matr_info);
				$mensaje .= ' la factura ' . $info['fact_num'] . ' en la orden ' . $cobros['orden_id'] . ',';
			}
			echo '
			<h2>¿Quieres desasociarlo de todas las facturas?</h2>
			<h4>' . $mensaje . '</h4>
				<table>
					<tr>
						<td>
							<a href="entrega.php?accion=desasoc_cobros&cobro_id=' . $cobro_id . '&grupal=1&eliminar=1&fact=' . $fact . '&orden_id=' . $orden_id . '"><button type="button" class="btn btn-success">Sí, desacociar cobro de todas las facturas</button></a>
						</td>
					</tr>
				</table>'."\n";
			
		}
	}
}

elseif($accion==="asociarant") {

	if (validaAcceso('1030115', $dbpfx) == 1) {
// --- Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccesoPermiso'];
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id );
	}

	$error = 'no';
	$mensaje = '';
	$cobs = array();
	foreach($fid as $k => $v) {
		if($v != '0|0|0') {
//			print_r($v); echo '<br>'; 
//			$facturas[] = $fact['fact_num'] . '|' . $mont_pc . '|' . $fact['fact_id'];
			$ff = explode('|', $v);
			if($ff[1] < $cobro_monto[$k]) { $error = 'si'; $mensaje .= 'El monto de un cobro es mayor a lo que resta por cobrar de la factura.<br>';}
			$cobs[$ff[2]] = $cobs[$ff[2]] + $cobro_monto[$k];
			if($ff[1] < $cobs[$ff[2]]) { $error = 'si'; $mensaje .= 'El monto de la suma de los cobros asignados es mayor a lo que resta por cobrar de la factura.<br>';}
		}
	}

	if ($error === 'no') {
		foreach($fid as $k => $v) {
			if($v != '0|0|0') {
				$ff = explode('|', $v);
				$param = " cf_id = '" . $cf_id[$k] . "' ";
				$sql_data_array = [
					'fact_id' => $ff[2],
					'orden_id' => $orden_id,
				];
				ejecutar_db($dbpfx . 'cobros_facturas', $sql_data_array, 'actualizar', $param);
				bitacora($orden_id, 'Agregué el cobro huérfano '.$cobro_id[$k].' a la factura '.$ff[2], $dbpfx);
				// -- Marcar pagada la factura ---
//				if($ff[1] == $cobro_monto[$k]){
				if($ff[1] == $cobs[$ff[2]]){
					unset($sql_data_array);
					$sql_data_array = [
						'fact_cobrada' => 1,
						'fact_fecha_cobrada' => date('Y-m-d H:i:s', time()),
					];
					$param = " fact_id = '" . $ff[2] . "' ";
					ejecutar_db($dbpfx . 'facturas_por_cobrar', $sql_data_array, 'actualizar', $param);
				}
			}
		}
		unset($sql_data_array);
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
	}
}

elseif($accion==="regfact") {

	if (validaAcceso('1030065', $dbpfx) == 1 || $_SESSION['codigo'] <= '12' || ($solovalacc != '1' && ($_SESSION['rol12'] == '1'))) {
		$msg = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso para Asesor de Servicio']);
	}

	$error = 'no';
	$mensaje = '';
	if($reporte == '') {$reporte = '0';}

	if($_FILES['cfdi']['name']) {
		$cfdi = file_get_contents($_FILES['cfdi']['tmp_name']);

		$xml = new DOMDocument();
		$xml->loadXML($cfdi) or die("\n\n\nXML no valido");
//		$resulta = agrega_documento($orden_id, $_FILES['cfdi'], 'CFDi - XML a cliente', $dbpfx);

		$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
		$Emisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
		$Receptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
		$Impuestos = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Impuestos')->item(0);
		$Timbre = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);
		$conceptos = $xml->getElementsByTagName('Concepto');

// ------ Verificación de version de CFDi
		if($Comprobante->getAttribute("version") == '3.2') {
			$rfc = $Emisor->getAttribute("rfc");
			$receptor_nombre = utf8_decode($Receptor->getAttribute("nombre"));
			$receptor_rfc = utf8_decode($Receptor->getAttribute("rfc"));
			$emision = $Comprobante->getAttribute("fecha");
			$iva = $Impuestos->getAttribute("totalImpuestosTrasladados");
			$uuid = $Timbre->getAttribute("UUID");
			$concep = 0;
			$descripcion = '';
			foreach($conceptos as $concepto)	{
				if($concep > '0') { $descripcion .= ', ';} 
				$descripcion .= utf8_decode($concepto->getAttribute("descripcion"));
				$concep++;
			}
			$descripcion .= '.';
			$fact_monto = $Comprobante->getAttribute("total");
			$fact_num = $Comprobante->getAttribute("serie") . $Comprobante->getAttribute("folio");
		} elseif($Comprobante->getAttribute("Version") == '3.3') {
			$rfc = $Emisor->getAttribute("Rfc");
			$receptor_nombre = utf8_decode($Receptor->getAttribute("Nombre"));
			$receptor_rfc = utf8_decode($Receptor->getAttribute("Rfc"));
			$emision = $Comprobante->getAttribute("Fecha");
			foreach($xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Impuestos') as $Impues) {
				if($Impues->getAttribute("TotalImpuestosTrasladados") != '') {
					$iva = $Impues->getAttribute("TotalImpuestosTrasladados");
					break;
				}
			}
			$uuid = $Timbre->getAttribute("UUID");
			$concep = 0;
			$descripcion = '';
			foreach($conceptos as $concepto)	{
				if($concep > '0') { $descripcion .= ', ';} 
				$descripcion .= utf8_decode($concepto->getAttribute("descripcion"));
				$concep++;
			}
			$descripcion .= '.';
			$fact_monto = $Comprobante->getAttribute("Total");
			$fact_num = $Comprobante->getAttribute("Serie") . $Comprobante->getAttribute("Folio");
			// --- Agrega el XML al sistema
//			agrega_documento ($orden_id, $_FILES['cfdi'], basename($_FILES['cfdi']['name']) . '.xml', $dbpfx, '', 1);
			if(move_uploaded_file($_FILES['cfdi']['tmp_name'], DIR_DOCS . basename($_FILES['cfdi']['name']))) {
				$doc_nombre = 'Factura XML ' . $fact_num;
				$nombre_archivo = basename($_FILES['cfdi']['name']);
				$sql_fact = array('orden_id' => $orden_id,
					'doc_nombre' => $doc_nombre,
					'doc_clasificado' => 1,
					'doc_usuario' => $_SESSION['usuario'],
					'doc_archivo' => $nombre_archivo);
				ejecutar_db($dbpfx . 'documentos', $sql_fact, 'insertar');
				// --- Copia el archivo al servidor de respaldo ---
				if($servrespaldo != '') {
					$conecta = ssh2_connect($servrespaldo, 2922);
					ssh2_scp_send($conecta, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, 0644);
				}
			}
			
		} else {
			$error = 'si'; $mensaje .= 'La versión del documento no coincide con los esperados, por favor revise que sea el documento correcto. ' . '<br>';
		}

		if($receptor_rfc == 'XAXX010101000' || $receptor_rfc == 'XEXX010101000') { 
			$tipo_nom = 'Factura Generica';
			$fact_tipo = '1';
		} else {
			$tipo_nom = 'Factura';
			$fact_tipo = '1';
		} 

//		if($total_sub != $total) { $error = 'si'; $mensaje .= $lang['impor tot de fact no coinc con tot a cobr'];}

	} else {
		$fact_monto = limpiarNumero($fact_monto);
//		$por_facturar = limpiarNumero($por_facturar);
		if(!isset($fact_num) || $fact_num =='') { $error = 'si'; $mensaje .= 'Indique el número del documento para ' . $nombre . '<br>';  }
		if(!isset($fact_tipo)) { $error = 'si'; $mensaje .= 'Seleccione Factura o Comprobante Simplificado para ' . $nombre . '<br>';  }
		if($fact_monto <= '0' || $fact_monto =='') { $error = 'si'; $mensaje .= 'Indique el monto del cobro para ' . $nombre . '<br>';  }
		$emision = $emyear . '-' . $emmes . '-' . $emdia;
		if($emision == '2017-00-00') { $error = 'si'; $mensaje .= 'Indique la fecha de emisión del documento para ' . $nombre . '<br>';  }
	}

//	if($fact_monto != $por_facturar) { $error = 'si'; $mensaje .= 'El monto '.$fact_monto.' de la factura '.$fact_num.' no puede ser diferente al monto por facturar '.$por_facturar.' para ' . $nombre . '<br>';  }
	
//	echo $receptor_rfc;
 
   if ($error === 'no') {
		$sql_data_array = array('orden_id' => $orden_id,
			'reporte' => $reporte,
			'fact_num' => $fact_num,
			'fact_uuid' => $uuid,
			'fact_fecha_emision' => $emision,
			'fact_tipo' => $fact_tipo,
			'fact_monto' => $fact_monto,
			'fact_impuesto' => $iva,
			'fact_rfc' => $receptor_rfc,
			'fact_descripcion' => $descripcion,
			'usuario' => $_SESSION['usuario']);
		if($reporte=='0' || $reporte=='') {
			$preg0 = "SELECT orden_cliente_id FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
			$matr0 = mysql_query($preg0);
			$ord = mysql_fetch_array($matr0);
			$cli_id = $ord['orden_cliente_id'];
			$sql_data_array['cliente_id'] = $ord['orden_cliente_id']; 
		} else {
			$sql_data_array['aseguradora_id'] = $aseguradora;
		}
		if($regfefact != '1') {
			$sql_data_array['fact_fecha_recibida'] = date('Y-m-d H:i:s', time());
			$sql_data_array['fact_fecha_programada'] = date('Y-m-d H:i:s', time());
		}
		$fact_id = ejecutar_db($dbpfx . 'facturas_por_cobrar', $sql_data_array, 'insertar');
		bitacora($orden_id, $lang['Registro de'] . $tipo_nom . ': '.$fact_num, $dbpfx);
//		print_r($sql_data_array);
		unset($sql_data_array);
		
		$preg1 = "SELECT sub_orden_id, sub_partes, sub_consumibles, sub_mo, sub_area FROM " . $dbpfx . "subordenes WHERE sub_reporte = '$reporte' AND orden_id = '$orden_id' AND sub_estatus < '130' AND fact_id IS NULL ";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de subordenes!");
		$cons=0; $part=0; $mo7=0; $mo=0;
		if($pciva == '1' && $reporte == '0') { $factor = (1 + $impuesto_iva); } else { $factor = 1; }
		while($sub = mysql_fetch_array($matr1)) {
			if($sub['sub_area'] == '7') {
				$cons = $cons + round(($sub['sub_consumibles'] / $factor), 2);
				$mo7 = $mo7 + round(($sub['sub_mo'] / $factor), 2);
			} else {
				$part = $part + round(($sub['sub_partes'] / $factor), 2);
				$mo = $mo + round(($sub['sub_mo'] / $factor), 2);
			}
//			$saf[] = $sub['sub_orden_id'];
		}

		// ----------- Asientos contables ----------------->

		if(isset($fact_tipo) && $fact_tipo < '3' && $asientos == '1') {
			if($aseguradora > '0') {
				$poliza = regPoliza('1', $lang['Emisión de factura'] . $fact_num . $lang['aseguradora'] . $ase[$aseguradora]['nic'] . $lang['siniestro'] . $reporte . $lang['OT'] . $orden_id, $fact_num);
		
				$resultado = regAsiento('3', '0', '1', $poliza['ciclo'], $poliza['polnum'], $aseguradora, $lang['Emisión de factura'] . $fact_num . $lang['aseguradora'] . $ase[$aseguradora]['nic'] . $lang['siniestro'] . $reporte .  $lang['OT'] . $orden_id, $fact_monto, $orden_id, $fact_num);

			} elseif($aseguradora == '0' && $fact_tipo == '1') {
				$poliza = regPoliza('1', $lang['Emisión de factura'] . $fact_num . $lang['cliente'] . $cli_id . $lang['OT'] . $orden_id, $fact_num);
		
				$resultado = regAsiento('4', '0', '1', $poliza['ciclo'], $poliza['polnum'], $cli_id, $lang['Emisión de factura'] . $fact_num . $lang['cliente'] . $cli_id . $lang['OT'] . $orden_id, $fact_monto, $orden_id, $fact_num);

			} else {
				$poliza = regPoliza('1', $lang['Emisión del Comprobante Simplificado'] . $fact_num . $lang['OT'] . $orden_id, $fact_num);

				$resultado = regAsiento('0', '0', '1', $poliza['ciclo'], $poliza['polnum'], '2005000', $lang['Emisión del Comprobante Simplificado'] . $fact_num . $lang ['OT'] . $orden_id, $fact_monto, $orden_id, $fact_num);
			}
			
			$resultado = regAsiento('0', '1', '1', $poliza['ciclo'], $poliza['polnum'], '2000010', $lang['IVA Trasladado por cobrar de factura'] . $fact_num . $lang['OT'] . $orden_id, $iva, $orden_id, $fact_num);

			if($mo > 0) {
				$resultado = regAsiento('0', '1', '1', $poliza['ciclo'], $poliza['polnum'], '2005005', $lang['Ingreso por servic de Hojal, Mec de la factura'] . $fact_num . $lang['siniestro'] . $reporte . $lang['OT'] . $orden_id, $mo, $orden_id, $fact_num);
			}
			if($mo7 > 0) {
				$resultado = regAsiento('0', '1', '1', $poliza['ciclo'], $poliza['polnum'], '2005010', $lang['Ingreso por servic de Pintu de la factura'] . $fact_num . $lang['siniestro'] . $reporte . $lang['OT'] . $orden_id, $mo7, $orden_id, $fact_num);
			}
			if($cons > 0) {
				$resultado = regAsiento('0', '1', '1', $poliza['ciclo'], $poliza['polnum'], '2005015', $lang['Ingreso por Materi de la factura'] . $fact_num . $lang['siniestro'] . $reporte . $lang['OT'] . $orden_id, $cons, $orden_id, $fact_num);
			}
			if($part > 0) {
				$resultado = regAsiento('0', '1', '1', $poliza['ciclo'], $poliza['polnum'], '2005020', $lang['Ingreso por Refacc de la factura'] . $fact_num . $lang['siniestro'] . $reporte . $lang['OT'] . $orden_id, $part, $orden_id, $fact_num);
			}
		}
// -------------------------------------  Fin de Asientos Contables ------------------------------

		foreach($tarfact as $k => $v) {
			if($fact_tipo < 3) {
				$param = " sub_orden_id = '" . $v . "'";
				$sql_data_array = array('fact_id' => $fact_id);
				ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $param);
				bitacora($orden_id, 'Tarea ' . $v . ' registrada como facturada con la factura id: '.$fact_id, $dbpfx);
				if($fact_tipo == '1') {
					$pregtar = "UPDATE " . $dbpfx . "subordenes SET sub_impuesto = (sub_presupuesto * " . $impuesto_iva . ") WHERE sub_orden_id = '" . $v . "'";
					$matrtar = mysql_query($pregtar) or die("ERROR: Fallo actualización de impuesto! " . $pregtar);
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $pregtar . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				}
			}
		}

		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
		
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
	}
}

elseif($accion==="regcobro") {

	if (validaAcceso('1030070', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol12'] == '1'))) {
		$msg = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso para Asesor de Servicio']);
	}

// ------ Reinicia la protección contra doble envío ------
	unset($_SESSION['microtime']);
	unset($_SESSION['rpe']['emitido']);

	echo '	<form action="entrega.php?accion=procesacobro" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0">'."\n";
	if($tipo == '1') { $tipo_nom = $lang['Factura']; }
	elseif($tipo == '2') { $tipo_nom = $lang['Comprobante Simplificado']; }
	elseif($tipo == '3') { $tipo_nom = $lang['Deducible']; }
	elseif($anticipo == 1) { $tipo_nom = $lang['Anticipo']; $tipo = '5';}
	echo '		<tr class="cabeza_tabla"><td colspan="4">'. $lang['Registrar cobro'] . $tipo_nom . ' ' . $numero . $lang['OT'] . $orden_id . '</td></tr>'."\n";
	echo '		<tr><td colspan="4">&nbsp;</td></tr>'."\n";
	echo '		<tr><td colspan="2" style="vertical-aling:top; width: 50%;">'."\n";
	echo '			<table cellpadding="0" cellspacing="0" border="1" class="agrega">'."\n";
// ------------ Mostrar Cobros de anticipos registrados -----------------------
	if($anticipo == 1) {
		$preg1 = "SELECT c.* FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE cf.orden_id = '$orden_id' AND c.cobro_tipo = '5' AND c.cobro_id = cf.cobro_id";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Cobros!".$preg1);
//		echo $preg1;
		$fila1 = mysql_num_rows($matr1);
		if($fila1 > 0) {
			echo '				<tr class="cabeza_tabla"><td colspan="2">Anticipos Previamente Registrados</td></tr>'."\n";
			echo '				<tr><td colspan="2">'."\n";
			echo '					<table cellspacing="0" cellpadding="2" border="1">'."\n";
			echo '						<tr><td>Cobro</td><td>Fecha</td><td>Forma de pago</td><td>Tipo</td><td>Cuenta</td><td>Referencia</td><td>Documento</td><td>Monto</td><td>Registrado por Usuario</td></tr>'."\n";
			$fondo = 'claro';
			while($cob = mysql_fetch_array($matr1)) {
				echo '						<tr><td>' . $cob['cobro_id'] . '</td><td>' . date('d/m/Y', strtotime($cob['cobro_fecha'])) . '</td><td>' . constant('TIPO_PAGO_' . $cob['cobro_tipo']) . '</td><td>' . $cob['cobro_banco'] . '</td><td>' . $cob['cobro_cuenta'] . '</td><td>' . $cob['cobro_referencia'] . '</td><td>';
				if($cob['cobro_documento'] != '') {
					echo '<a href="' . DIR_DOCS . $cob['cobro_documento'] . '" target="_blank"><img src="' . DIR_DOCS . 'documento.png" width="48" border="0"></a>'; 
				}
				echo '</td><td style="text-align: right;">$ ' . number_format($cob['cobro_monto'], 2) . '</td><td>' . $cob['usuario'] . '</td></tr>'."\n";
			}
			echo '					</table>'."\n";
			echo '				</td></tr>'."\n";
			echo '				<tr><td colspan="2">&nbsp;</td></tr>'."\n";
		} else {
			echo '				<tr class="cabeza_tabla"><td colspan="2">No hay Anticipos Previamente Registrados</td></tr>'."\n";
		}
	}
// -----------------------------------
//	echo 'cliente: ' . $cliente_id;
	echo '				<tr class="cabeza_tabla"><td colspan="2" style="text-align: center;">Datos Generales del Cobro</td></tr>'."\n";
	$mont_pc = round($mont_pc,2);
	echo '		<tr><td>'. $lang['Monto de este cobro'].'</td><td style="text-align:left;"><input type="text" name="cobro" value="';
	if($_SESSION['ent']['cobro'] != '') { echo number_format(($_SESSION['ent']['cobro']),2); } else { echo number_format($mont_pc,2); }
	echo '" size="10"  style="text-align:right;" required/></td></tr>'."\n";
	echo '		<tr><td>'. $lang['Fecha del cobro'].'</td><td style="text-align:left;">';
		require_once("calendar/tc_calendar.php");

		//instantiate class and set properties
		$myCalendar = new tc_calendar("fechacobro", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2011, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();

	echo '</td></tr>';

	echo '		<tr><td>'. $lang['Método de Cobro'].'</td><td style="text-align:left;">'."\n";
	echo '			<select name="forma_cobro" size="1" required/>
				<option value="" >'. $lang['Seleccione'].'</option>'."\n";
	for($i=1;$i<=$opcpago;$i++) {
		echo '				<option value="' . $i . '"';
		if($i == $_SESSION['ent']['forma_cobro']) { echo ' selected="selected" '; }
		echo '>' . constant('TIPO_PAGO_'.$i) . '</option>'."\n";
	}

// ------ Código puesto a la ligera, sin analizar el resto.... ------
/*	foreach($metodossat as $mets => $dets) {
		if($mets != '99') {
			echo '					<option value="' . $mets . '"';
			if($mets == $_SESSION['ent']['forma_cobro']) { echo ' selected="selected" '; } 
			echo '>' . $mets . ' ' . $dets . '</option>'."\n";
		}
	}
*/

	echo '			</select>'."\n";
	echo '		</td></tr>'."\n";
	echo '		<tr><td>'. $lang['Banco Origen'].'</td><td style="text-align:left;"><input type="text" name="banco" value="';
	if($_SESSION['ent']['banco'] != '') { echo $_SESSION['ent']['banco']; } else { echo REC_CLI_BANCO; }
	echo '" size="30" /></td></tr>'."\n";
	echo '		<tr><td>'. $lang['Num cheque o transferencia'].'</td><td style="text-align:left;"><input type="text" name="referencia" size="15" value="';
	if($_SESSION['ent']['referencia'] != '') { echo $_SESSION['ent']['referencia']; }
	elseif($anticipo == 1) { echo 'Anticipo'; }
	echo '" /></td></tr>'."\n";
	echo '		<tr><td>'. $lang['Cuenta de Cobro'].'</td><td style="text-align:left;">
			<select name="cuenta" size="1" required />
				<option value="" >'. $lang['Seleccione'].'</option>'."\n";

		$preg0 = "SELECT * FROM " . $dbpfx . "cont_cuentas WHERE ban_activo = '1'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuentas");
		while ($ban = mysql_fetch_array($matr0)) {
			echo '				<option value="' . $ban['ban_id'] . '"';
			if($_SESSION['ent']['cuenta'] == $ban['ban_id']) { echo ' selected="selected"';}
			echo ' />' . $ban['ban_nombre'] . ' - ' . $ban['ban_cuenta'] . '</option>'."\n";
		}
		echo '			</select>';
		echo '		</td></tr>'."\n";
	echo '		<tr><td>'. $lang['Imagen de comprobante de cobro'].'</td><td style="text-align:left;"><input type="file" name="comprobante" size="30" /></td></tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="entrega.php?accion=cobros&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a cobros'].'" title="'. $lang['Regresar a cobros'].'"></a></div></td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '			</table></td>'."\n";

// ------ Si hay CFDi de la factura y es de versión 3.3, ofrecer creación de Complemento de Pago ------
	echo '			<td style="vertical-align:top; width: 50%;">'."\n";
	echo '			<table cellpadding="0" cellspacing="0" border="1" class="agrega">'."\n";
	echo '				<tr class="cabeza_tabla"><td colspan="2" style="text-align: center;">Recibo de Pago Electrónico</td></tr>'."\n";
	$preg1 = "SELECT cf.* FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE cf.orden_id = '$orden_id' AND cf.fact_id = '" . $fact_id . "' AND c.cobro_tipo != '4' AND cf.cobro_id = c.cobro_id";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Cobros!" . $preg1);
	$fila1 = mysql_num_rows($matr1);
	if($fila1 == 0 && $mont_pc < $fact_total) {
		$saldant = $fact_total;
	} else {
		$saldant = $mont_pc;
	}

	$preg2 = "SELECT * FROM	" . $dbpfx . "facturas_por_cobrar WHERE fact_id = '" . $fact_id . "'";
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de factura por cobrar! " . $preg2);
	$fact = mysql_fetch_array($matr2);

	if(file_exists(DIR_DOCS . $fact['fact_num'] . '-' . $fact['fact_uuid'] . '.xml')) {
		$cfdi = file_get_contents(DIR_DOCS . $fact['fact_num'] . '-' . $fact['fact_uuid'] . '.xml');
		$xml = new DOMDocument();
		if($xml->loadXML($cfdi)) {
			$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
			$Emisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
			$Receptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
			if($Comprobante->getAttribute("Version") == '3.3') {
				if($Comprobante->getAttribute("MetodoPago") == 'PIP' || $Comprobante->getAttribute("MetodoPago") == 'PPD') {
					if($valor['timbres'][0] > 0) {
						if($valor['SerieRPE'][1] != '') {
							$factserie = $valor['SerieRPE'][1]; 
						} else {
							$factserie = 'RPE';
						}
						$preg3 = "SELECT fact_num FROM " . $dbpfx . "facturas WHERE fact_serie = '" . $factserie . "' ORDER BY fact_num DESC LIMIT 1";
						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de Recibo de Pago Electrónico! " . $preg3);
						$fnum = mysql_fetch_array($matr3);
						
						echo '		<tr><td>' . $lang['CrearRPE'] . '</td><td style="text-align:left;">' . $lang['Sí'] . '<input type="radio" name="crearpe" value="1" ';
						if($_SESSION['ent']['crearpe'] == '1') { echo 'checked="checked" '; }  
						echo '/>&nbsp;&nbsp;' . $lang['No'] . '<input type="radio" name="crearpe" value="2" ';
						if($_SESSION['ent']['crearpe'] == '2') { echo 'checked="checked" '; }
						echo '/></td></tr>'."\n";
						echo '		<tr><td>' . $lang['SerieDeRPE'] . '</td><td style="text-align:left;"><input type="hidden" name="rpe_serie" value="' . $factserie . '" />' . $factserie . ' del RFC: ' . $Emisor->getAttribute("Rfc") . '</td></tr>'."\n";
						echo '		<tr><td>' . $lang['Número de RPE'] . '</td><td style="text-align:left;"><input type="text" name="rpe_num" value="';
						if($_SESSION['ent']['rpe_num'] != '') { echo $_SESSION['ent']['rpe_num']; } else { echo $fnum['fact_num'] + 1; }
						echo '" /><input type="hidden" name="fact_num" value="' . $fnum['fact_num'] . '" /></td></tr>'."\n";
						echo '		<tr><td>' . $lang['Moneda'] . '</td><td style="text-align:left;"><input type="hidden" name="moneda" value="MXN" />MXN</td></tr>'."\n";
						echo '		<tr><td>' . $lang['UUID'] . '</td><td style="text-align:left;"><input type="hidden" name="uuid" value="' . $fact['fact_uuid'] . '" />' . $fact['fact_uuid'] . '</td></tr>'."\n";
						echo '		<tr><td>' . $lang['NumeroParcialidad'] . '</td><td style="text-align:left;"><input type="text" name="num_parcialidad" value="';
						if($_SESSION['ent']['num_parcialidad'] != '') { echo $_SESSION['ent']['num_parcialidad']; } else { echo ($fila1 + 1); }
						echo '" /><input type="hidden" name="num_par" value="' . $fila1 . '" /></td></tr>'."\n";
						echo '		<tr><td>' . $lang['SaldoAnterior'] . '</td><td style="text-align:left;">$ ' . number_format($mont_pc,2);
						echo '<input type="hidden" name="saldo_anterior" value="' . $saldant . '" />';
						echo '</td></tr>'."\n";
					} else {
						echo '		<tr><td colspan="2">' . $lang['NoHayTimbres'] . '<input type="hidden" name="crearpe" value="2" /></td></tr>'."\n";
					}
				} else {
					echo '		<tr><td colspan="2">' . $lang['TipoPUE'] . '<input type="hidden" name="crearpe" value="2" /></td></tr>'."\n";
				}
			} else {
				echo '		<tr><td colspan="2">' . $lang['CFDIVersionNo33'] . '<input type="hidden" name="crearpe" value="2" /></td></tr>'."\n";
			}
		} else {
			echo '		<tr><td colspan="2">' . $lang['XMLNoValido'] . '<input type="hidden" name="crearpe" value="2" /></td></tr>'."\n";
		}
	} else {
		echo '		<tr><td colspan="2">' . $lang['NoHayCFDi'] . '<input type="hidden" name="crearpe" value="2" /></td></tr>'."\n";
	}
// --------------------------------

	echo '			</table></td>'."\n";
	echo '		<tr><td colspan="2">
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<input type="hidden" name="microtime" value="' . microtime() . '" />
			<input type="hidden" name="fact_id" value="' . $fact_id . '" />
			<input type="hidden" name="fact_total" value="' . $fact_total . '" />
			<input type="hidden" name="tipo" value="' . $tipo . '" />
			<input type="hidden" name="por_cobrar" value="' . $mont_pc . '" />
			<input type="hidden" name="numero" value="' . $numero . '" />
			<input type="hidden" name="reporte" value="' . $reporte . '" />
			<input type="hidden" name="aseguradora" value="' . $aseguradora . '" />'."\n";
	if(file_exists(DIR_DOCS . $fact['fact_num'] . '-' . $fact['fact_uuid'] . '.xml')) {
		echo '			<input type="hidden" name="SerieRel" value="' . $Comprobante->getAttribute("Serie") . '" />
			<input type="hidden" name="FolioRel" value="' . $Comprobante->getAttribute("Folio") . '" />
			<input type="hidden" name="MetodoRel" value="' . $Comprobante->getAttribute("MetodoPago") . '" />
			<input type="hidden" name="ReceptorNombre" value="' . $Receptor->getAttribute("Nombre") . '" />
			<input type="hidden" name="ReceptorRfc" value="' . $Receptor->getAttribute("Rfc") . '" />
			<input type="hidden" name="EmisorRfc" value="' . $Emisor->getAttribute("Rfc") . '" />'."\n";
	}

	echo '			<input type="hidden" name="cliente_id" value="' . $cliente_id . '" />'."\n";
	if($anticipo == 1) { echo '			<input type="hidden" name="anticipo" value="1" />'."\n"; }
	echo '		</td></tr>
		<tr>
			<td colspan="2" style="text-align:left;">
				<input type="submit" value="'. $lang['Enviar'].'" />
			</td>
		</tr>
	</table>
	</form>'."\n";
}

elseif($accion==="procesacobro") {
	
	if (validaAcceso('1030070', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1' || $_SESSION['rol12']=='1'))) {
		$msg = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso no autorizado']);
	}
	unset($_SESSION['ent']);
	$_SESSION['ent'] = array();
	$mensaje = '';
	$error = 'no'; 
	$cobro = limpiarNumero($cobro); $_SESSION['ent']['cobro'] = $cobro;
	$banco = preparar_entrada_bd($banco); $_SESSION['ent']['banco'] = $banco;	
	$cuenta = preparar_entrada_bd($cuenta); $_SESSION['ent']['cuenta'] = $cuenta;	
	$referencia = preparar_entrada_bd($referencia); $_SESSION['ent']['referencia'] = $referencia;
	$_SESSION['ent']['por_cobrar'] = $por_cobrar;
	$_SESSION['ent']['forma_cobro'] = $forma_cobro;
	$_SESSION['ent']['crearpe'] = $crearpe;
	$_SESSION['ent']['rpe_serie'] = $rpe_serie;
	$rpe_num = intval($rpe_num); $_SESSION['ent']['rpe_num'] = $rpe_num;
	$_SESSION['ent']['moneda'] = $moneda;
	$_SESSION['ent']['uuid'] = $uuid;
	$num_parcialidad = intval($num_parcialidad); $_SESSION['ent']['num_parcialidad'] = $num_parcialidad;
	if($tipo == '1') { $tipo_nom = $lang['Factura']; } 
	elseif($tipo == '2') { $tipo_nom = $lang['Comprobante Simplificado']; } 
	elseif($tipo == '3') { $tipo_nom = $lang['Deducible']; }
	elseif($tipo == '4') { $tipo_nom = $lang['Nota de crédito']; }
	elseif($tipo == '5') { $tipo_nom = $lang['Anticipo']; }
	if($reporte == '') { $reporte = 0; }
	if($anticipo == 1 && $referencia == '') { $referencia = 'Anticipo'; }
	if($anticipo == 1) { $tipo = 5; }
	
	if($cobro <= 0 || $cobro == '') {$error = 'si'; $mensaje .= $lang['monto del cobro no puede ser cero'].'<br>';}
	if($forma_cobro == '' || !isset($forma_cobro)) {$error = 'si'; $mensaje .= $lang['Selecc forma de pago'].'<br>';}
	if($forma_cobro > 1 && ($banco == '' || $cuenta == '')) {$error = 'si'; $mensaje .= $lang['Banco y Cuenta de cobro'].'<br>';}
	if($forma_cobro > 1 && $referencia == '') {$error = 'si'; $mensaje .=$lang['Num cheque o transferencia'].'<br>';}

	if($anticipo != 1) {
		if($cobro > $por_cobrar) {$error = 'si'; $mensaje .= $lang['Monto del cobro no debe ser superior'].'<br>';}
		if(!isset($fact_id) || $fact_id == '') {$error = 'si'; $mensaje .=$lang['Datos incompletos'].'<br>';}
	}
	if($valor['timbres'][0] > 0 && $crearpe != '1' && $crearpe != '2') { $error = 'si'; $mensaje .= $lang['ValidarCrearRPE'].'<br>';}
	if($crearpe == 1) {
		if($rpe_num == '' || !is_numeric($rpe_num) || ($rpe_num <= $fact_num)) { $error = 'si'; $mensaje .= $lang['ValidarRPENum'].'<br>';}
		if($num_parcialidad == '' || !is_numeric($num_parcialidad) || ($num_parcialidad <= $num_par)) { $error = 'si'; $mensaje .= $lang['ValidarParNum'].'<br>';}

// ------ Traducción de forma de cobro para efectos fiscales ------
		if($forma_cobro == 1) { $fcobro = '01'; }
		elseif($forma_cobro == 2) { $fcobro = '02'; }
		elseif($forma_cobro == 3) { $fcobro = '03'; }
		elseif($forma_cobro == 6) { $fcobro = '04'; }
		elseif($forma_cobro == 7) { $fcobro = '28'; }
		else { $error = 'si'; $mensaje .= $lang['SeleccFormaFiscal'].'<br>'; }
	}

// ------ Evitar doble registro del mismo cobro ------
	if($_SESSION['microtime'] == $microtime) {$error = 'si'; $mensaje .= $lang['RegCobEnviado'].'<br>';}

	if($error === 'no') {
		//--- Al procesar el cobro, se coloca a la variable $_SESSION['microtime'] el valor $microtime con lo que...
		//--- si se vuelve a enviar el formulario será detectado como doble envío...
		$_SESSION['microtime'] = $microtime;
		$preg0 = "SELECT * FROM " . $dbpfx . "cont_cuentas WHERE ban_id = '$cuenta'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuenta bancaria");
		$ban = mysql_fetch_array($matr0);
		if($_FILES['comprobante']['name'] != '') {
			$subir = agrega_documento($orden_id, $_FILES['comprobante'], $lang['Comprob Cobro de factu'].$numero, $dbpfx);
		} 
//		print_r($subir);
//		echo 'Resultado de subir<br>';
		//--- determinar cliente del cobro --------
		$sql_data_array = array(
//			'fact_id' => $fact_id,
			'orden_id' => $orden_id,
			'reporte' => $reporte,
			'cobro_tipo' => $tipo,
			'cobro_monto' => $cobro,
			'cobro_metodo' => $forma_cobro,
			'cobro_banco' => $banco,
			'cobro_cuenta' => $cuenta,
			'cobro_referencia' => $referencia,
			'cobro_fecha' => $fechacobro,
			'cobro_documento' => $subir['nombre'],
			'usuario' => $_SESSION['usuario']);
		if($aseguradora == 0) {
			$preg_clie = "SELECT orden_cliente_id FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $orden_id . "'";
			$matr_clie = mysql_query($preg_clie) or die("ERROR: Fallo selección de orden");
			$cliente = mysql_fetch_array($matr_clie);
			$cliente_id = $cliente['orden_cliente_id'];
			$sql_data_array['cliente_id'] = $cliente_id;
		} else{
			$sql_data_array['aseguradora_id'] = $aseguradora;
		}
		$cobro_id = ejecutar_db($dbpfx . 'cobros', $sql_data_array);
//		bitacora($orden_id, 'Cobro de ' . $tipo_nom . ' ' . $numero, $dbpfx);
		unset($sql_data_array);
		unset($_SESSION['ent']);

		$sql_data_array = array(
			'cobro_id' => $cobro_id,
			'fact_id' => $fact_id,
			'monto' => $cobro,
			'orden_id' => $orden_id,
			'usuario' => $_SESSION['usuario'],
			'fecha' => date('Y-m-d H:i:s', time()));
		if($aseguradora == 0) {
			$sql_data_array['cliente_id'] = $cliente_id;
		} else{
			$sql_data_array['aseguradora_id'] = $aseguradora;
		}
		if($anticipo == '1'){
			$sql_data_array['fact_id'] = 'null';
		}
		ejecutar_db($dbpfx . 'cobros_facturas', $sql_data_array);
		bitacora($orden_id, 'Cobro de Factura ID ' . $fact_id . ' con el cobro id ' . $cobro_id, $dbpfx);

		if($cobro == $por_cobrar && $anticipo != 1) {
				$coloca = "UPDATE " . $dbpfx . "facturas_por_cobrar SET fact_cobrada = '1', fact_fecha_cobrada = '" . $fechacobro . "' WHERE fact_id = '$fact_id'";
				$graba = mysql_query($coloca) or die("ERROR: Fallo actualización de facturas por cobrar!".$coloca);
				$archivo = '../logs/' . time() . '-base.ase';
				$myfile = file_put_contents($archivo, $coloca . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				bitacora($orden_id, $lang['Cobro Total'] . $tipo_nom . ' ' . $numero, $dbpfx);
		}

		// ----------- Asientos contables ----------------->
		
		if($asientos == 1) {
			$poliza = regPoliza('2', $lang['Registro en cuenta'] . $ban['ban_id'] .$lang['cobro de la factura'] . $numero . $lang['OT'] . $orden_id, $fact_num);
		
			$resultado = regAsiento('5', '0', '2', $poliza['ciclo'], $poliza['polnum'], $ban['ban_id'],$lang['Registro en cuenta'] . $ban['ban_id'] . $lang['cobro de la factura'] . $numero .$lang['OT'] . $orden_id, $cobro, $orden_id, $numero);
		
			$iva = round(($cobro - ($cobro / ($impuesto_iva + 1))), 2);
			$resultado = regAsiento('0', '0', '2', $poliza['ciclo'], $poliza['polnum'], '2000010', $lang['IVA trasladado por cobrar de la factura'] . $numero .$lang['OT'] . $orden_id, $iva, $orden_id, $numero);

			$resultado = regAsiento('0', '1', '2', $poliza['ciclo'], $poliza['polnum'], '2000015', $lang['IVA cobrado de la factura'] . $numero . $lang['OT'] . $orden_id, $iva, $orden_id, $numero);

			$preg1 = "SELECT reporte, cliente_id, aseguradora_id FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '$fact_id' AND orden_id = '$orden_id'";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de factura por cobrar");
			$fxc = mysql_fetch_array($matr1);
			if($fxc['reporte'] == '0') {
				$resultado = regAsiento('4', '1', '2', $poliza['ciclo'], $poliza['polnum'], $fxc['cliente_id'], $lang['Cobro de factura'] . $numero . $lang['cliente'] . $fxc['cliente_id'] . $lang['OT'] . $orden_id, $cobro, $orden_id, $numero);
			} else {
				$resultado = regAsiento('3', '1', '2', $poliza['ciclo'], $poliza['polnum'], $fxc['aseguradora_id'], $lang['Cobro de factura'] . $numero . $lang['aseguradora'] . $fxc['aseguradora_id'] .$lang['OT'] . $orden_id, $cobro, $orden_id, $numero);
			}
		}
		
// ------ Si se eligió crear RPE enviar a generar comprobante ------
		if($crearpe == 1) {
			$repgen = [
				'Serie' => $rpe_serie,
				'Folio' => $rpe_num,
				'ReceptorRfc' => $ReceptorRfc,
				'ReceptorNombre' => $ReceptorNombre,
			];
			$pagos[] = [
				'FechaPago' => $fechacobro . 'T23:59:59',
				'FormaDePagoP' => $fcobro,
				'Monto' => $cobro,
				'Banco' => $banco,
				'NumOperacion' => $referencia,
			];
			$docrel[] = [
				'IdDocumento' => $uuid,
				'Serie' => $SerieRel,
				'Folio' => $FolioRel,
				'MetodoDePagoDR' => $MetodoRel,
				'NumParcialidad' => $num_parcialidad,
				'ImpSaldoAnt' => $saldo_anterior,
				'ImpPagado' => $cobro,
				'ImpSaldoInsoluto' => round(($saldo_anterior -$cobro),2),
				'doc_ot' => $orden_id,
			];
			// --- Si hay RFCs alternos, localiza el utilizado en la factura ---
			if($RfcAlternos > 0) {
				foreach($Rfcs as $rfck => $rfcv) {
					if($EmisorRfc == $rfcv[0]) {
						$agencia_rfc = $rfcv[0];
						$agencia_razon_social = $rfcv[1];
						$agencia_regimen = $rfcv[2];
						$agencia_cp = $rfcv[3];
					}
				}
			}
			include('parciales/rpe-3.3.php');
		}
// -----------------------------------------------------------------
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
	} else {
//		echo 'Cobro: ' . $cobro . '. En session: ' . $_SESSION['ent']['cobro'] . '<br>';
   	$_SESSION['msjerror'] = $mensaje;
   	redirigir('entrega.php?accion=regcobro&orden_id=' . $orden_id . '&tipo=' . $tipo . '&fact_id=' . $fact_id . '&reporte=' . $reporte . '&numero=' . $numero . '&mont_pc=' . $por_cobrar . '&aseguradora=' . $aseguradora);
	}
}

elseif($accion==="asociarant") {

	$funnum = '';

	if ($_SESSION['rol12']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Pago a Proveedores, ingresar Usuario y Clave correcta');
	}
	$error = 'no';
	$mensaje = '';
	$cobs = array();
	foreach($fid as $k => $v) {
		if($v != '0|0|0') {
//			print_r($v); echo '<br>';
			$ff = explode('|', $v);
			if($ff[1] < $cobro_monto[$k]) { $error = 'si'; $mensaje .= 'El monto de un cobro es mayor a lo que resta por cobrar de la factura.<br>';}
			$cobs[$ff[2]] = $cobs[$ff[2]] + $cobro_monto[$k];
		}
	}

	foreach($fid as $k => $v) {
		if($v != '0|0|0') {
			$ff = explode('|', $v);
			if($ff[1] < $cobs[$ff[2]]) { $error = 'si'; $mensaje .= 'El monto de la suma de los cobros asignados es mayor a lo que resta por cobrar de la factura.<br>';}
		}
	}

 
   if ($error === 'no') {
   	foreach($fid as $k => $v) {
   		if($v != '0|0|0') {
	  			$ff = explode('|', $v);
				$param = " cobro_id = '" . $cobro_id[$k] . "' ";
				$sql_data_array = array('fact_id' => $ff[2]);
				ejecutar_db($dbpfx . 'cobros_facturas', $sql_data_array, 'actualizar', $param);
				bitacora($orden_id, 'Anticipo '.$cobro_id[$k].' asociado a la factura '.$ff[2], $dbpfx);
			}
		}
		unset($sql_data_array);
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
	}
}

elseif($accion==="borrafactura") {

	if (validaAcceso('1030080', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol02']=='1'))) {
		// Acceso autorizado
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso para Gerentes']);
	}
	if(isset($borrafact)) {
		unset($_SESSION['ent']);
		$error = 'no';
		$preg1 = "SELECT fact_uuid, fact_num, fact_cobrada FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '" . $fact_id . "'";
		$matr1 = mysql_query($preg1) or die('Falló selección de factura! ' . $preg1);
		$fact = mysql_fetch_array($matr1);
// Si la factura es del mes actual y no se ha cobrado, la cancelación procede. Si es anterior o está cobrada, se pregunta 
// si se genera una Nota de Crédito por el total de la factura o se procede a cancelar de todas formas.
		if($cancelacfdi == '1' && $fact['fact_uuid'] != '' && $borradirecto != '1') {
			include('parciales/encabezado.php');
			echo '	<div id="body">';
			include('parciales/menu_inicio.php');
			echo '		<div id="principal">';
			$cfdi = file_get_contents(DIR_DOCS . $fact['fact_num'] . '-' . $fact['fact_uuid'] . '.xml');
			$xml = new DOMDocument();
			$xml->loadXML($cfdi) or die("\n\n\nXML no valido");
			$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
			$fechaem = explode('-', $Comprobante->getAttribute("fecha"));
			$subtotal = $Comprobante->getAttribute("subTotal");
			$descrip = 'Nota de crédito por la factura ' . $fact['fact_num'] . ' del ' . $Comprobante->getAttribute("fecha");
			if($fechaem[0] < date('Y') || $fechaem[1] != date('m') || $fact['fact_cobrada'] == '1') {
				echo '<a href="entrega.php?accion=regnc&orden_id=' . $orden_id . '&reporte=' . $reporte . '&subtotal=' . $subtotal . '&descrip=' .  $descrip . '&numero=' . $fact['fact_num'] . '" ><button type="button">Factura de Mes anterior o Cobrada. Crear Nota de Crédito?</button></a> ó ';
			}
			echo '<a href="entrega.php?accion=borrafactura&orden_id=' . $orden_id . '&reporte=' . $reporte . '&fact_id=' . $fact_id . '&borrafact=1&borradirecto=1"><button type="button">Cancelar Factura</button></a>';
		} else {
			if($fact['fact_uuid'] != '' && $cancelacfdi == '1') {
				require_once('nusoap/nusoap.php');
				$options = array('trace' => 1, 'exceptions' => true);
				$urlAutentica = $pac_autentica;
				$urlCancelacion = $pac_cancelacion;
				try {
					$autentica = new SoapClient($urlAutentica, $options) or die('No logro autenticar');
					$cancelacion = new SoapClient($urlCancelacion, $options) or die('No logro url de cancelacion');
					$credentials = array('usuario' => $pac_usuario, 'password' => $pac_clave);
					$token = $autentica->AutenticarBasico($credentials)->AutenticarBasicoResult;
					$cer = file_get_contents('../certificados/'.$agencia_rfc.'.cer.pem');
					$key = file_get_contents('../certificados/'.$agencia_rfc.'.key.pem');
					$cerB64 = base64_encode($cer);
					$keyB64 = base64_encode($key);
					$uuids = (array($fact['fact_uuid']));
					$cancelacionData = array(
						'PEMCer' => $cerB64,
						'PEMKey' => $keyB64,
						'RFCEmisor' => $agencia_rfc,
						'UUIDs' => $uuids,
						'tokenAutenticacion' => $token);
					$respuesta = $cancelacion->CancelarPEM($cancelacionData)->CancelarPEMResult;
					$xml = simplexml_load_string($respuesta) or die("\n\n\nXML no valido");
					$uuidr = $xml->Folios->UUID;
					$euidr = $xml->Folios->EstatusUUID;
					if($euidr == '201' || $euidr == '202') {
						$msj = 'Factura Cancelada';
						$nr = 'CANCELADA';
					} else {
						$_SESSION['msjerror'] = 'La factura no fue cancelada, error ' . $euidr . '<br>';
						$nr = 'ERROR-'.$euidr;
						$error = 'si';
					}
					$nombre_acuse = $fact['fact_num'] . '-'.$nr.'-' . $fact['fact_uuid'];
					$xml->asXml(DIR_DOCS.$nombre_acuse.'.xml');
					$doc_nombre = 'Acuse XML ' . $nr . ' ' . $fact['fact_num'];
					$sql_data_array = array('doc_nombre' => $doc_nombre,
						'doc_clasificado' => 1,
						'doc_usuario' => $_SESSION['usuario'],
						'orden_id' => $orden_id,
						'doc_archivo' => $nombre_acuse . '.xml');
					ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
				}
				catch(SoapFault $e) {
					$_SESSION['msjerror'] = 'El SAT no procesó la cancelación, reportar a Soporte junto con el siguiente texto:<br>'; 
					$_SESSION['msjerror'] .= $e->faultstring;
//					echo $e->faultstring;
					$error = 'si';
				}
			}
			if($error == 'no') {
				$pregunta = "UPDATE " . $dbpfx . "facturas_por_cobrar SET fact_cobrada = '2' WHERE fact_id = '" . $fact_id . "'";
				$resultado = mysql_query($pregunta);
				$archivo = '../logs/' . time() . '-base.ase';
				$myfile = file_put_contents($archivo, $pregunta . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				$pregunta = "UPDATE " . $dbpfx . "cobros_facturas SET fact_id = '0' WHERE fact_id = '" . $fact_id . "'";
				$resultado = mysql_query($pregunta);
				$archivo = '../logs/' . time() . '-base.ase';
				$myfile = file_put_contents($archivo, $pregunta . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				$pregunta = "UPDATE " . $dbpfx . "subordenes SET fact_id = NULL, sub_impuesto = NULL WHERE fact_id = '" . $fact_id . "'";
				$resultado = mysql_query($pregunta);
				$archivo = '../logs/' . time() . '-base.ase';
				$myfile = file_put_contents($archivo, $pregunta . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				$pregunta = "DELETE FROM " . $dbpfx . "ajusadmin WHERE fact_id = '" . $fact_id . "'";
				$resultado = mysql_query($pregunta);
				$archivo = '../logs/' . time() . '-base.ase';
				$myfile = file_put_contents($archivo, $pregunta . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				bitacora($orden_id, $lang['factura por cobrar eliminada'] . $fact_id . '.', $dbpfx);
				$_SESSION['msjerror'] .= $lang['La factura'] . $fact_id . $lang['fue eliminada'];
			}
			redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
		}
	}
}

elseif($accion==="regnc") {
	
	$funnum = 1030065;
	if($_SESSION['ent']['descripcion'] == '') { $_SESSION['ent']['descripcion'] = $descrip;}
	if($_SESSION['ent']['importe'] == '') { $_SESSION['ent']['importe'] = $subtotal;}

	echo '		<form action="entrega.php?accion=genregnc" method="post" enctype="multipart/form-data">
		<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	echo '			<tr class="cabeza_tabla"><td colspan="2">'. $lang['Captura de NC Gen'] . ' a la factura ' . $numero . ' ' . $lang['OT'] . $orden_id . '</td></tr>'."\n";
/*	echo '			<tr><td>'. $lang['Seleccione Area'] . '</td><td style="text-align:left;">';
	echo '				<select name="area" size="1">
					<option value="" >Seleccione...</option>'."\n";
	for($i=1;$i<=$num_areas_servicio;$i++) {
		echo '					<option value="' . $i . '"';
		if($_SESSION['ent']['area'] == $i) { echo ' selected '; }
		echo '>' . constant('NOMBRE_AREA_' . $i) . '</option>'."\n";
	}
	echo '				</select>'."\n";
	echo '			</td></tr>'."\n";
*/	echo '			<tr><td>'. $lang['Concepto de la NC'] . '</td><td style="text-align:left;"><input type="text" name="descripcion" value="' . $_SESSION['ent']['descripcion'] . '" /></td></tr>'."\n";
	echo '			<tr><td>'. $lang['Importe de la NC'] . '</td><td style="text-align:left;"><input type="text" name="importe" value="' . $_SESSION['ent']['importe'] . '" /></td></tr>'."\n";
	echo '			<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="entrega.php?accion=cobros&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a cobros'].'"'. $lang[' title="Regresar a cobros'].'"></a></div></td></tr>'."\n";
	unset($_SESSION['ent']);
	echo '			<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2">
				<input type="hidden" name="orden_id" value="' . $orden_id . '" />
				<input type="hidden" name="reporte" value="' . $reporte . '" />
			</td></tr>
			<tr><td colspan="2" style="text-align:left;"><input type="submit" value="'. $lang['Enviar'].'" /></td></tr>
		</table>
		</form>';
}

elseif($accion==="genregnc") {

	if (validaAcceso('1030065', $dbpfx) == 1 || ($solovalacc !='1' && ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1' || $_SESSION['rol12']=='1'))) {
		$msg =$lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso no autorizado']);
	}
	unset($_SESSION['ent']);
	$_SESSION['ent'] = array();
	$mensaje = '';
	$error = 'no';
	$importe = limpiarNumero($importe); $_SESSION['ent']['importe'] = $importe;
	$descripcion =preparar_entrada_bd($descripcion);  $_SESSION['ent']['descripcion'] = $descripcion;
	if($importe <= 0) { $error = 'si'; $mensaje .= $lang['Importe NC no menor'] . '<br>'; }
	if($descripcion == '' || !isset($descripcion)) { $error = 'si'; $mensaje .= $lang['Concepto vacio'] . '<br>'; }

	if($error === 'no') {
		$preg = "SELECT * FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '$reporte' AND sub_estatus < '130' LIMIT 1";
		$matr = mysql_query($preg);
		$sub = mysql_fetch_array($matr);
		$importe = ($importe * -1);
		$sql_data_array = array('orden_id' => $orden_id,
				'sub_area' => '50',
				'sub_descripcion' => $descripcion,
				'sub_estatus' => '112',
				'sub_presupuesto' => $importe,
				'sub_siniestro' => $sub['sub_siniestro'],
				'sub_reporte' => $reporte,
				'sub_aseguradora' => $sub['sub_aseguradora'],
				'sub_poliza' => $sub['sub_poliza'],
				'sub_paga_deducible' => $sub['sub_paga_deducible'],
				'recibo_id' => '-1');
		$tarea = ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
		bitacora($orden_id, $lang['Tarea para NC'], $dbpfx, $lang['Tarea para NC'], 0, $tarea);
		unset($sql_data_array);
		unset($_SESSION['ent']);
		redirigir('nota-de-credito.php?accion=consultar&orden_id=' . $orden_id . '&reporte=' . $reporte);
	} else {
	   	$_SESSION['msjerror'] = $mensaje;
   		redirigir('entrega.php?accion=regnc&orden_id=' . $orden_id);
	}
}

elseif($accion==="regfactrec") {
	
	$funnum = 1030085;

	echo '	<form action="entrega.php?accion=procesafactrec" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	if($tipo == '1') { $tipo_nom =$lang['Factura']; } 
	elseif($tipo == '2') { $tipo_nom =$lang['Comprobante Simplificado']; } 
	elseif($tipo == '3') { $tipo_nom = $lang['Deducible']; }
	echo '		<tr class="cabeza_tabla"><td colspan="2">'. $lang['Regist fecha programada de pago'] . $tipo_nom . ' ' . $numero . $lang['OT'] . $orden_id . '</td></tr>'."\n";
	echo '		<tr><td>'. $lang['Fecha Cliente recibió'] . $tipo_nom . '</td><td style="text-align:left;">';
		require_once("calendar/tc_calendar.php");

		//instantiate class and set properties
		$myCalendar = new tc_calendar("fecharec", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2011, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();	  

	echo '</td></tr>';
	
	echo '		<tr><td>'. $lang['Fecha programada de pago'] . $tipo_nom . '</td><td style="text-align:left;">';
		require_once("calendar/tc_calendar.php");

		//instantiate class and set properties
		$myCalendar = new tc_calendar("fechaprog", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2011, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();	  

	echo '</td></tr>';

	echo '		<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="entrega.php?accion=cobros&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a cobros'].'"'. $lang[' title="Regresar a cobros'].'"></a></div></td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2"><input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="hidden" name="fact_id" value="' . $fact_id . '" /><input type="hidden" name="tipo" value="' . $tipo . '" /><input type="hidden" name="numero" value="' . $numero . '" /><input type="hidden" name="reporte" value="' . $reporte . '" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="'. $lang['Enviar'].'" /></td></tr>
		</tr>
	</table>
	</form>';
}

elseif($accion==="procesafactrec") {
	
	if (validaAcceso('1030090', $dbpfx) == 1 || ($solovalacc !='1' && ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1' || $_SESSION['rol12']=='1'))) {
		$msg =$lang['Acceso autorizado']; 
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso no autorizado']);
	}
	unset($_SESSION['ent']);
	$_SESSION['ent'] = array();
	$mensaje = '';
	$error = 'no'; 
	if($tipo == '1') { $tipo_nom = $lang['Factura']; } 
	elseif($tipo == '2') { $tipo_nom = $lang['Comprobante Simplificado']; } 
	elseif($tipo == '3') { $tipo_nom = $lang['Deducible']; }

	if($error === 'no') {
		$param = " fact_id = '" . $fact_id . "'";
		$sql_data_array = array('fact_fecha_recibida' => $fecharec,
			'fact_fecha_programada' => $fechaprog);
		ejecutar_db($dbpfx . 'facturas_por_cobrar', $sql_data_array, 'actualizar', $param);
		bitacora($orden_id, $lang['Registro de recepción'] . $tipo_nom . ' ' . $numero, $dbpfx);
		unset($sql_data_array);
		unset($_SESSION['ent']);
		redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
	} else {
   	$_SESSION['msjerror'] = $mensaje;
   	redirigir('entrega.php?accion=cobros&orden_id=' . $orden_id);
   }
}

elseif ($accion==="cerrar") {

	$funnum = 1030035;
	
//	echo 'Estamos en la sección enviar';
	echo '	<form action="entrega.php?accion=cierra" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	$preg = "SELECT orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
	$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
	$dato = mysql_fetch_array($matr);
	echo '		<tr class="obscuro"><td>'. $lang['Archivo comprobante de entrega'].'</td><td style="text-align:left;"><input type="file" name="imagen" size="30" /></td></tr>'."\n";
	if($arciase == '1') {
		echo '		<tr class="claro"><td>' . $lang['Enviar a Aseguradora'] . '</td><td style="text-align:left;"><input type="checkbox" name="envaseg" value="1" checked="checked"></td></tr>'."\n";
	}
	echo '		<tr><td colspan="2">
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<input type="hidden" name="estatus" value="' . $dato['orden_estatus'] . '"/>
		</td></tr>
		<tr><td colspan="2" style="text-align:left;" class="obscuro"><input type="submit" value="'. $lang['Enviar'].'" /></td></tr>
	</table>
	</form>';
}

elseif($accion==="cierra") {
	
	$funnum = 1030040;
	
	if ($_SESSION['rol06']!='1') {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso para Asesor de Servicio']);
	}
	$error = 'no';
	$mensaje = '';
	$resulta = agrega_documento($orden_id, $_FILES['imagen'], $lang['No se agregó imagen de comprobante de entrega'], $dbpfx);
	if($resulta['error'] != 'no') {  $error = 'si'; $mensaje .= $lang['No agregó imagen de comprobante de entrega'].'<br>';  }
   if ($error === 'no') {
		$preg = "SELECT orden_estatus, orden_vehiculo_id, orden_vehiculo_placas, orden_fecha_de_entrega FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
		$dato = mysql_fetch_array($matr);
		$sql_data_array = array('orden_alerta' => '0',
			'orden_ubicacion' => $lang['Entregado - Cerrado'],
			'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'));
		if($dato['orden_estatus'] >= '30' && $dato['orden_estatus'] <= '35') {
			if(is_null($dato['orden_fecha_de_entrega']) || $dato['orden_fecha_de_entrega'] == '0000-00-00 00:00:00' || $dato['orden_fecha_de_entrega'] == '') {
				$sql_data_array['orden_fecha_de_entrega'] = date('Y-m-d H:i:s', time());
			}
			$sql_data_array['orden_ref_pendientes'] = '0';
		}
		if($dato['orden_estatus']=='30') { $sql_data_array['orden_estatus'] = '98'; }
		elseif($dato['orden_estatus']=='31') { $sql_data_array['orden_estatus'] = '97'; }
		elseif($dato['orden_estatus']=='32') { $sql_data_array['orden_estatus'] = '96'; }
		elseif($dato['orden_estatus']=='33' || $dato['orden_estatus']=='34' || $dato['orden_estatus']=='35') { $sql_data_array['orden_estatus'] = '95'; }
		else { $sql_data_array['orden_estatus'] = '99'; }
/*		if($sql_data_array['orden_estatus']>='95' && $sql_data_array['orden_estatus']<='98') {
			$preg1 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id'";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion!");
			while($sub = mysql_fetch_array($matr1)) {
				$preg2 = "DELETE FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
				$matr2 = mysql_query($preg2);
				$sql_data = array('sub_estatus' => '190');
				$parametros='sub_orden_id = ' . $sub['sub_orden_id'];
				ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $parametros);
				unset($sql_data);
			}
			$sql_data_array['orden_presupuesto'] = NULL;
		}
*/		$parametros='orden_id = ' . $orden_id;
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Cambio a estatus ' . $sql_data_array['orden_estatus'] . ' ' . constant('ORDEN_ESTATUS_' . $sql_data_array['orden_estatus']) . ' anterior: ' . $dato['orden_estatus'] . ' ' . constant('ORDEN_ESTATUS_' . $dato['orden_estatus']), $dbpfx, $motivo);
		$parametros='vehiculo_id = ' . $dato['orden_vehiculo_id'];
		$sql_data = array('vehiculo_proxima' => $proxima);
		ejecutar_db($dbpfx . 'vehiculos', $sql_data, 'actualizar', $parametros);
		unset($sql_data_array);
		$preg0 = "SELECT sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_aseguradora > '0' AND sub_estatus = '112' GROUP BY sub_aseguradora";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de subordenes!");
		$sub = mysql_fetch_array($matr0);
		$aseguradora = $sub['sub_aseguradora'];
		$reporte = $sub['sub_reporte'];
//		echo 'Conf: ' . $arciase . ' Bandera: ' . $envaseg . ' Asegnoti: ' . $asenoti[$aseguradora]['alta'] . ' Aseg: ' . $aseguradora;
		if($arciase == '1' && $asenoti[$aseguradora]['alta'] == '1' && $envaseg == '1') {
			$asunto = 'Encuesta de satisfaccion para vehiculo con placas ' . $dato['orden_vehiculo_placas'] . ' en ' . $nombre_agencia;
			$situacion = 'recabamos y enviamos anexa la <strong>Encuesta de Satisfaccion del Cliente</strong> sobre los trabajos que realizamos en';
			include_once('parciales/notifica_aseguradora.php');
		}
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$mensaje .= $resultado['mensaje'];
		$mensaje .= $lang['No se cerró la orden de trabajo'] . $orden_id . $lang['intente de nuevo'];
		$_SESSION['msjerror'] = $mensaje;
		redirigir('entrega.php?accion=cerrar&orden_id=' . $orden_id);
	}
}

elseif ($accion==="deducible") {
	
	if (validaAcceso('1030105', $dbpfx) == 1 || $_SESSION['codigo'] <= '12' || ($solovalacc != '1' && ($_SESSION['rol12'] == '1'))) {
		$msg = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso para Asesor de Servicio']);
	}
	
//	echo 'Estamos en la sección carta de deducibles';
	$error = 'si'; $num_cols = 0;
	if ($orden_id!='') {
		$pregunta = "SELECT o.orden_vehiculo_marca, o.orden_vehiculo_tipo, v.vehiculo_modelo, o.orden_vehiculo_placas, o.orden_odometro FROM " . $dbpfx . "ordenes o, " . $dbpfx . "vehiculos v WHERE o.orden_id = '$orden_id' AND o.orden_vehiculo_id = v.vehiculo_id";
		$error = 'no';
	} else {
		
	}
	if ($error ==='no') {
//		echo $pregunta;
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de OT!");
		$num_cols = mysql_num_rows($matriz);
	}
	if ($num_cols>0 && $dato!='') {
		$veh = datosVehiculo($orden_id, $dbpfx);
		$rep = explode('|', $dato);
		$preg0 = "SELECT * FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $rep[1] . "'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo aseguradoras!");
		$aseg =  mysql_fetch_array($matr0);
		$preg1 = "SELECT * FROM " . $dbpfx . "facturas_por_cobrar WHERE aseguradora_id = '" . $rep[1] . "' AND reporte = '" . $rep[0] . "' AND fact_tipo = '3' AND fact_cobrada < '2'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo facturas por cobrar!");
		$fact =  mysql_fetch_array($matr1);
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		include ('particular/carta-deducible.php');
		echo '</td></tr>'."\n";
		echo '		<tr><td><div class="control">';
		echo '<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="' . $lang['Imprimir Deducible'] . '" title="' . $lang['Imprimir Deducible'] . '" /></a> ';
		echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="' . $lang['Regresar a la OT'] . '" title="' . $lang['Regresar a la OT'] . '" /></a></div></td></tr>';
		echo '</table>';
	} else {
		if(!isset($dato) || $dato=='') {
			$preg0 = "SELECT sub_reporte, sub_aseguradora, sub_poliza FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '189' AND sub_reporte != '0' AND sub_reporte != '' GROUP BY sub_reporte";
   	  	$mat0 = mysql_query($preg0) or die("ERROR: Fallo selección!");
     		$num_rep = mysql_num_rows($mat0);
			if ($num_rep > 1) {
				include('parciales/encabezado.php'); 
				echo '	<div id="body">';
				include('parciales/menu_inicio.php');
				echo '		<div id="principal">';
				echo '	<form action="entrega.php?accion=deducible" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="850">'."\n";
   		  	echo '		<tr><td colspan="2" style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">'. $lang['más de un siniestro o tipo de servicio, elija el reporte'].'</td></tr>' . "\n";
     			echo '		<tr><td colspan="2"><select name="dato" size="1">' . "\n";
				echo '			<option value="" >'.$lang['Seleccione'].'</option>';
		     	while($rep = mysql_fetch_array($mat0)) {
   		  		echo '			<option value="' . $rep['sub_reporte'] . '|' . $rep['sub_aseguradora'] . '|' . $rep['sub_poliza'] . '">' . $rep['sub_reporte'] . '</option>' . "\n";
				}
				echo '		</select></td></tr>' . "\n";
				echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2"><input type="hidden" name="orden_id" value="' . $orden_id . '" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="'.$lang['Enviar'].'" /></td></tr>
		</tr>
	</table>
	</form>'."\n";
			} elseif($num_rep== 1) {
				$rep = mysql_fetch_array($mat0);
				$dato = $rep['sub_reporte'] . '|' . $rep['sub_aseguradora'] . '|' . $rep['sub_poliza'];
				redirigir('entrega.php?accion=deducible&orden_id=' . $orden_id . '&dato=' . $dato);
			} else {
				$_SESSION['msjerror'] = 'No se capturó el número de póliza o no es siniestro.';
				redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
			}
		} 
	}
}

elseif ($accion==="ajusadm") {
	
	unset($_SESSION['microtime']);

	if (validaAcceso('1030110', $dbpfx) == 1 || ($solovalacc != '1' && ($_SESSION['rol12']=='1'))) {
		// Acceso Autorizado
	} else {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Rol Cobranza.');
	}
	echo '	<form action="entrega.php?accion=regajuadm" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr class="cabeza_tabla"><td colspan="2">Registro de Ajuste administrativo en la OT ';
	echo $orden_id;
	if($reporte != '') {
		echo ' para el reporte ';
		if($reporte == '0') { echo 'Particular'; } else { echo $reporte; } 
	}
	if($doc != '') { ' en la factura ID ' . $doc; }
	if($pedido_id != '') { ' del pedido ' . $pedido_id; }
	elseif($recibo_id != '') { ' del recibo de destajo ' . $recibo_id; }
	echo ' por un monto de ' . number_format($monto,2);
	echo '</td></tr>'."\n";
	echo '		<tr><td>Monto a ajustar</td><td style="text-align:left;"><input type="text" name="ajuste" value="' . $monto . '" size="10"  style="text-align:right;"/></td></tr>'."\n";
	echo '		<tr><td>Motivo del ajuste:</td><td style="text-align:left;"><input type="text" name="motivo" size="40" /></td></tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="entrega.php?accion=cobros&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar al Registro de Cobros" title="Regresar al Registro de Cobros"></a></div></td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">
			<input type="hidden" name="microtime" value="' . microtime() . '" />
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<input type="hidden" name="pedido_id" value="' . $pedido_id . '" />
			<input type="hidden" name="recibo_id" value="' . $recibo_id . '" />
			<input type="hidden" name="doc_id" value="' . $doc . '" />
			<input type="hidden" name="monto" value="' . $monto . '" />
			<input type="hidden" name="reporte" value="' . $reporte . '" />
			<input type="hidden" name="pagina" value="' . basename($_SERVER['PHP_SELF']) . '" />
		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>
	</table>
	</form>';
}

elseif($accion==="regajuadm") {
	
	if (validaAcceso('1030110', $dbpfx) == 1 || ($solovalacc != '1' && ($_SESSION['rol12']=='1'))) {
		// Acceso Autorizado
	} else {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Rol Cobranza.');
	}

	$error = 'no';
	$mensaje = '';

 	if($_SESSION['microtime'] == $microtime) { $error = 'si'; $_SESSION['msjerror'] .= 'La tecla enter fue presionada más de una vez, sólo se procesó el primer envío.<br>';}
 	else { $_SESSION['microtime'] = $microtime; }

	if($ajuste > $monto && $doc_id != '') { $error = 'si'; $mensaje .= 'El monto del ajuste no puede ser mayor al monto por ajustar.<br>';  }
	if($motivo == '' || strlen($motivo) < '15') { $error = 'si'; $mensaje .= 'El motivo debe contener al menos 15 caracteres.<br>';  }
 
   if ($error === 'no') {
		$sql_data_array = array('orden_id' => $orden_id,
			'recibo_id' => $recibo_id,
			'pedido_id' => $pedido_id,
			'reporte' => $reporte,
			'fact_id' => $doc_id,
			'monto' => $ajuste,
			'motivo' => $motivo,
			'fecha_ajuste' => date('Y-m-d H:i:s'),
			'usuario' => $_SESSION['usuario']);
		ejecutar_db($dbpfx . 'ajusadmin', $sql_data_array, 'insertar');
		bitacora($orden_id, 'Registro de ajuste administrativo por ' . $ajuste . ' debido a ' . $motivo, $dbpfx);
//		print_r($sql_data_array);
		unset($sql_data_array);
		redirigir($pagina . '?accion=cobros&orden_id=' . $orden_id);

	} else {
		$_SESSION['msjerror'] .= $mensaje;
		redirigir($pagina . '?accion=cobros&orden_id=' . $orden_id);
	}
}

?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
