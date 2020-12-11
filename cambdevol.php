<?php
include('parciales/funciones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

include('idiomas/' . $idioma . '/cambdevol.php');

/*  ----------------  obtener nombres de proveedores   ------------------- */

$consulta = "SELECT prov_id, prov_nic, prov_dde, prov_iva, prov_razon_social FROM " . $dbpfx . "proveedores ORDER BY prov_nic";
$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
$num_provs = mysql_num_rows($arreglo);
$provs = array();
//$provs[0] = 'Sin Proveedor';
while ($prov = mysql_fetch_array($arreglo)) {
	$provs[$prov['prov_id']] = array('nic' => $prov['prov_nic'], 'dde' => $prov['prov_dde'], 'iva' => $prov['prov_iva'], 'razon' => $prov['prov_razon_social']);
}
//print_r($provs);

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

$pregusu = "SELECT usuario, nombre, apellidos, rol13, activo FROM " . $dbpfx . "usuarios WHERE acceso = '0' ORDER BY nombre";
$matrusu = mysql_query($pregusu) or die("ERROR: Fallo selección de usuarios!");
while ($usr = mysql_fetch_array($matrusu)) {
	$usu[$usr['usuario']] = array('nombre' => $usr['nombre'], 'apellidos' => $usr['apellidos'], 'rol13' => $usr['rol13'], 'activo' => $usr['activo']);
}

if ($accion==='registro' || $accion === 'dictaminar') {
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php');
	echo '	<div id="body">'."\n";
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">'."\n";
	echo '			<div class="page-content">'."\n";
}

if($accion === 'registrar') {
// ------ Captura de requerimiento de devolución o cambio de refacciones.
	if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol13']=='1'))) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccNoAut'];
		redirigir('usuarios.php');
	}

//	if(validaAcceso('1175010', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02']=='1'))) {
// ------ Valida acceso a lista de cambios y devoluciones por Autorizar ------
	include('parciales/cambdevol-menu.php');
//	}

	echo '
				<div class="row"> <!-box header del título. -->
					<div class="col-sm-12">
						<div class="content-box-header">
							<div class="panel-title">
		  						<h2>' . $lang['CambODev'] . '</h2> 
							</div>
					  	</div>
					</div>
				</div>'."\n";

	if($orden_id == '' && $_SESSION['cambdevol']['orden_id'] == '' && $_SESSION['devo']['orden_id'] == '') {
		echo '
				<form action="cambdevol.php?accion=registrar" method="post" enctype="multipart/form-data">
				<div class="row">
					<div class="col-sm-4">
						' . $lang['IngresaOT'] . '
					</div>
					<div class="col-sm-1">
						<input type="text" name="orden_id" required>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-1">
						<input type="submit" value="' . $lang['Enviar'] . '" />
					</div>
				</div>
				</form>'."\n";
	} else {
		$contenido = array();
		if(is_array($_SESSION['cambdevol'])) {
			$orden_id = $_SESSION['cambdevol']['orden_id'];
			$pedido_id = $_SESSION['cambdevol']['pedido_id'];
			$prov_id = $_SESSION['cambdevol']['prov_id'];

			foreach($_SESSION['cambdevol']['op_id'] as $k => $v) {
				$preg1 = "SELECT op_recibidos, op_costo, op_nombre, sub_orden_id, op_tangible FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $v . "'";
				$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos! " . $preg1);
//				echo $preg1;
				$preg2 = "SELECT e.ent_operador FROM " . $dbpfx . "entregas e, " . $dbpfx . "entregas_productos p WHERE p.op_id = '" . $v . "' AND p.ent_id = e.ent_id";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de entregas! " . $preg2);
				while ($prod = mysql_fetch_array($matr1)) {
					$entr = mysql_fetch_assoc($matr2);
					if($_SESSION['devo']['responsable'] == '' && $entr['ent_operador'] != '') {
						$_SESSION['devo']['responsable'] = $entr['ent_operador'];
					} else {
						$preg3 = "SELECT sub_operador FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $prod['sub_orden_id'] . "'";
						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de sub_operador! " . $preg3);
						$entr = mysql_fetch_assoc($matr3);
						$responsable = $entr['sub_operador'];
					}
					if($_SESSION['devo']['tiporeq'][$v] != '') {
						$contenido[$v]['tiporeq'] = $_SESSION['devo']['tiporeq'][$v];
					}
					$contenido[$v]['cantidad'] = $prod['op_recibidos'];
					$contenido[$v]['monto'] = (($prod['op_recibidos'] * $prod['op_costo']) * (1 + $provs[$prov_id]['iva']));
					$contenido[$v]['nombre'] = $prod['op_nombre'];
				}
			}
			unset($_SESSION['cambdevol']);
		} else {
			if($_SESSION['devo']['pedido_id'] != '') {
				$orden_id = $_SESSION['devo']['orden_id'];
				$pedido_id = $_SESSION['devo']['pedido_id'];
				$prov_id = $_SESSION['devo']['prov_id'];
				foreach($_SESSION['devo']['cantidad'] as $k => $v) {
					$contenido[$k]['tiporeq'] = $_SESSION['devo']['tiporeq'][$k];
					$contenido[$k]['cantidad'] = $_SESSION['devo']['cantidad'][$k];
					$contenido[$k]['monto'] = $_SESSION['devo']['monto'][$k];
					$contenido[$k]['nombre'] = $_SESSION['devo']['nombre'][$k];
				}
			} else {
				if($orden_id == '') { $orden_id = $_SESSION['devo']['orden_id']; }
				for($i=1; $i < 4; $i++) {
					$contenido[$i] = array();
					$contenido[$i]['tiporeq'] = $_SESSION['devo']['tiporeq'][$i];
					$contenido[$i]['cantidad'] = $_SESSION['devo']['cantidad'][$i];
					$contenido[$i]['nombre'] = $_SESSION['devo']['nombre'][$i];
				}
			}
		}

// ------ Formulario de captura -------
		$veh = datosVehiculo($orden_id, $dbpfx);
		echo '
				<div class="row">
					<div class="col-sm-12">
						<div class="cabeza_tabla">
							' . $lang['DeOt'] . ' ' . $orden_id . ' ' . $lang['DelVeh'] . ' ' . $veh['completo'] . "\n";
		if($pedido_id != '') {
			echo '							 ' . $lang['DelPed'] . ' ' . $pedido_id . ' ' . $lang['DelProv'] . ' ' . strtoupper($provs[$prov_id]['nic'])."\n";
		}
		echo '						</div>
					</div>
				</div>'."\n";
		echo '
				<form action="cambdevol.php?accion=registro" method="post" enctype="multipart/form-data">'."\n";
		echo '
				<div class="row">'."\n";
		if($pedido_id != "") {
			echo '					<div class="col-sm-1 cen">' . $lang['Devolucion'] . '</div>'."\n";
			$tipoinput = 'hidden';
		} else {
			$tipoinput = 'text';
		}
		echo '					<div class="col-sm-1 cen">' . $lang['Reemplazo'] . '</div>
					<div class="col-sm-1 cen">' . $lang['Cantidad'] . '</div>
					<div class="col-sm-5">' . $lang['NombreItem'] . '</div>'."\n";
		if($pedido_id == '') {
			echo '					<div class="col-sm-1">' . $lang['NombreArea'] . '</div>'."\n";
		}
		echo '				</div>'."\n";
		foreach($contenido as $k => $v) {
			echo '
				<div class="row">'."\n";
			if($pedido_id != "") {
				echo '						<div class="col-sm-1 cen">
						<input type="radio" name="tiporeq[' . $k . ']" required value="1" ';
				if($v['tiporeq'] == 1) { echo 'checked '; }
				echo '>
					</div>'."\n";
			}
			echo '					<div class="col-sm-1 cen">
						<input type="radio" name="tiporeq[' . $k . ']" value="2" ';
			if($v['tiporeq'] == 2) { echo 'checked '; }
			echo '>
					</div>
					<div class="col-sm-1 cen">
						<input type="' . $tipoinput . '" name="cantidad[' . $k . ']" value="' . $v['cantidad'] . '" size="3"/>
						<input type="hidden" name="monto[' . $k . ']" value="' . $v['monto'] . '" />';
			if($pedido_id != "") { echo $v['cantidad']; }
			echo "\n".'					</div>
					<div class="col-sm-5">
						<input type="' . $tipoinput . '" name="nombre[' . $k . ']" value="' . $v['nombre'] . '" size ="45" />';
			if($pedido_id != "") { echo $v['nombre']; }
			echo "\n".'					</div>'."\n";
			if($pedido_id == '') {
				echo '
					<div class="col-sm-1">
						<select name="area[' . $k . ']" />
							<option value="" >' . $lang['Selecciona'] . '</option>'."\n";
				for($i=1; $i <= $num_areas_servicio; $i++) {
					echo '							<option value="' . $i . '"';
					if($_SESSION['devo']['area'] == $i) { echo ' selected'; } 
					echo ' >' . constant('NOMBRE_AREA_' . $i) . '</option>'."\n";
				}
			echo '
						</select>
					</div>'."\n";
			}
			echo '
				</div>'."\n";
		}

		echo '				<div class="row">
					<div class="col-sm-2 der">
						' . $lang['Motivo'] . '
					</div>
					<div class="col-sm-8">
						<input type="text" name="motivo" value="' . $_SESSION['devo']['motivo'] . '" required size ="60" />
					</div>
				</div>
				<div class="row">
					<div class="col-sm-2 der">' . $lang['Responsable'] . '</div>
					<div class="col-sm-4 izq">
						<select name="responsable" required />
							<option value="" >' . $lang['Selecciona'] . '</option>'."\n";
		if($_SESSION['devo']['responsable'] != '') { $reponsable = $_SESSION['devo']['responsable']; }
		foreach($usu as $j => $u) {
			if($usu[$j]['activo'] == 1) {
				echo '							<option value="' . $j . '" ';
				if($reponsable == $j) { echo ' selected '; }
				echo ' >' . $usu[$j]['nombre'] . ' ' . $usu[$j]['apellidos'] . '</option>'."\n";
			}
		}
		echo '
						</select>
						<input type="hidden" name="orden_id" value="' . $orden_id . '" />
						<input type="hidden" name="pedido_id" value="' . $pedido_id . '" />
						<input type="hidden" name="prov_id" value="' . $prov_id . '" />
					</div>
				</div>'."\n";
		echo '
				<div class="row">
				<div class="row">
					<div class="col-sm-12">
						<input type="submit" value="' . $lang['Enviar'] . '" />
					</div>
				</div>'."\n";
		echo '
				</form>'."\n";
		unset($_SESSION['cambdevol']);
		unset($_SESSION['devo']);
	}
}

elseif($accion === 'registro') {

	if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol13']=='1'))) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccNoAut'];
		redirigir('usuarios.php');
	}

	unset($_SESSION['devo']); $_SESSION['devo'] = array();
	$mensaje = ''; $error = 'no';
	$_SESSION['devo']['orden_id'] = $orden_id;
	$_SESSION['devo']['pedido_id'] = $pedido_id;
	$_SESSION['devo']['prov_id'] = $prov_id;
	unset($tprq);
	foreach ($cantidad as $k => $v) {
		if($v > 0) {
			$_SESSION['devo']['tiporeq'][$k] = $tiporeq[$k];
			$cantidad[$k] = limpiarNumero($cantidad[$k]); $_SESSION['devo']['cantidad'][$k] = $cantidad[$k];
			$monto[$k] = limpiarNumero($monto[$k]); $_SESSION['devo']['monto'][$k] = $monto[$k];
			if($cantidad[$k] <= 0 && $tiporeq[$k] > 0) { $error = 'si'; $mensaje .= $lang['SinCant'] . '<br>';}
			$nombre[$k] = limpiar_cadena($nombre[$k]); $_SESSION['devo']['nombre'][$k] = $nombre[$k];
			if($nombre[$k] == '' && $tiporeq[$k] > 0) { $error = 'si'; $mensaje .= $lang['SinNomb'] . '<br>';}
			$_SESSION['devo']['area'][$k] = $area[$k];
			if($tiporeq[$k] == 2 && $pedido_id != '' && !is_numeric($area[$k]) && $area[$k] < 1) { $error = 'si'; $mensaje .= $lang['SinArea'] . '<br>'; }
			if($tiporeq[$k] < 1 && ($cantidad[$k] > 0 || $nombre[$k] != '')) { $error = 'si'; $mensaje .= $lang['SinTipoReq'] . '<br>';}
			$tprq[$tiporeq[$k]]++;
		}
	}
	$_SESSION['devo']['responsable'] = $responsable;
	$motivo = limpiar_cadena($motivo); $_SESSION['devo']['motivo'] = $motivo;
	if($motivo == '') { $error = 'si'; $mensaje .= $lang['SinMotivo'] . '<br>';}
	if($usudictcd < 100) { $error = 'si'; $mensaje .= $lang['SinUsuDict'] . ' ' . $usudictcd . '<br>';}

	if($error == 'no') {
		foreach($tprq as $k => $v) {
			$sqldata = [
			'orden_id' => $orden_id,
			'pedido_id' => $pedido_id,
			'prov_id' => $prov_id,
			'usu_responsable' => $responsable,
			'motivo' => $motivo,
			'usu_requiere' => $_SESSION['usuario']
			];
			if($k == 1) { $tprq1 = ejecutar_db($dbpfx . 'cambdevol', $sqldata, 'insertar'); $cd_id = $tprq1; }
			else { $tprq2 = ejecutar_db($dbpfx . 'cambdevol', $sqldata, 'insertar'); $cd_id = $tprq2; }
			bitacora($orden_id, $lang['ReqCDCreado'] . ' ' . $cd_id, $dbpfx, 'Para: ' . $usu[$usudictcd]['nombre'] . ' ' . $usu[$usudictcd]['apellidos'] . ' ' . $lang['NotiCambDevo'] . ' <a href="cambdevol.php?accion=pendientes&cd_id=' . $cd_id . '"><button type="button">Requerimiento ' . $cd_id . '</button></a>', 3, '', '', $usudictcd);
			unset($sqldata);
		}

		foreach ($cantidad as $k => $v) {
			if($v > 0 && $tiporeq[$k] == 1) {
				$sqldata = [
					'cd_id' => $tprq1,
					'op_id' => $k,
					'cantidad' => $cantidad[$k],
					'monto' => $monto[$k],
					'nombre' => $nombre[$k],
					'tipo_cd' => $tiporeq[$k]
				];
			} elseif($v > 0 && $tiporeq[$k] == 2) {
				$sqldata = [
					'cd_id' => $tprq2,
					'cantidad' => $cantidad[$k],
					'nombre' => $nombre[$k],
					'tipo_cd' => $tiporeq[$k],
					'area' => $area[$k]
				];
			}
			if(is_array($sqldata)) {
				ejecutar_db($dbpfx . 'cambdevol_elementos', $sqldata, 'insertar');
			}
			unset($sqldata);
		}

		unset($_SESSION['devo']);
		unset($_SESSION['cambdevol']);
		redirigir('gestion.php');
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('cambdevol.php?accion=registrar');
	}
}

elseif($accion === 'pendientes') {
// ------ Captura de requerimiento de devolución o cambio de refacciones.
	if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol13']=='1'))) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccNoAut'];
		redirigir('usuarios.php');
	}

	include('parciales/cambdevol-menu.php');
	echo '
				<div class="row"> <!-box header del título. -->
					<div class="col-sm-12">
						<div class="content-box-header">
							<div class="panel-title">
		  						<h2>' . $lang['ReqPendDict'] . '</h2> 
							</div>
					  	</div>
					</div>
				</div>'."\n";

	$preg1 = "SELECT * FROM " . $dbpfx . "cambdevol WHERE cd_estatus < 10";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de requerimientos de cambio o devolución! " . $preg1);
	echo '
				<form action="cambdevol.php?accion=dictaminar" method="post" enctype="multipart/form-data">
				<div class="row cabeza_tabla">
					<div class="col-sm-1 cen">
						Req ID / OT
					</div>
					<div class="col-sm-2">
						Vehículo
					</div>
					<div class="col-sm-2">
						' . $lang['DetalleSol'] . '
					</div>
					<div class="col-sm-1 cen">
						' . $lang['Solicitante'] . '
					</div>
					<div class="col-sm-3">
						Motivo
					</div>
					<div class="col-sm-1 cen">
						' . $lang['Devolucion'] . '
					</div>
					<div class="col-sm-1 cen">
						' . $lang['Reemplazo'] . '
					</div>
					<div class="col-sm-1 cen">
						' . $lang['Rechazar'] . '
					</div>
				</div>'."\n";

	$fondo = "claro";
	while($cds = mysql_fetch_array($matr1)) {
		$orden_id = $cds['orden_id'];
		$veh = datosVehiculo($orden_id,$dbpfx);
		$preg2 = "SELECT * FROM " . $dbpfx . "cambdevol_elementos WHERE cd_id = '" . $cds['cd_id'] . "' AND dictamen = '0'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de elementos de cambio o devolución! " . $preg2);
		while($ele = mysql_fetch_array($matr2)) {
			if($ele['tipo_cd'] == 1 ) { $tipo_cd = $lang['Devolucion']; }
			else { $tipo_cd = $lang['Reemplazo']; }
			echo '
				<div class="row ' . $fondo . '">
					<div class="col-sm-1 cen">
						' . $cds['cd_id'] . ' / <a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '" target="_blank">' . $orden_id . '</a>
						<input type="hidden" name="orden_id[' . $ele['elem_id'] . ']" value="' . $orden_id . '" />
						<input type="hidden" name="req_id[' . $ele['elem_id'] . ']" value="' . $cds['cd_id'] . '" />
						<input type="hidden" name="pedido_id[' . $ele['elem_id'] . ']" value="' . $cds['pedido_id'] . '" />
						<input type="hidden" name="prov_id[' . $ele['elem_id'] . ']" value="' . $cds['prov_id'] . '" />
					</div>
					<div class="col-sm-2">
						' . $veh['completo'] . '
					</div>
					<div class="col-sm-2">
						' . $tipo_cd . ' de ' . $ele['cantidad'] . ' ' . $ele['nombre'] . '
					</div>
					<div class="col-sm-1 cen">
						' . $usu[$cds['usu_responsable']]['nombre'] . ' ' . $usu[$cds['usu_responsable']]['apellidos'] . '
					</div>
					<div class="col-sm-3">
						' . $cds['motivo'] . '
					</div>
					<div class="col-sm-1 cen">
						<input type="radio" name="dictamen[' . $ele['elem_id'] . ']" value="1" />
					</div>
					<div class="col-sm-1 cen">
						<input type="radio" name="dictamen[' . $ele['elem_id'] . ']" value="2" />
					</div>
					<div class="col-sm-1 cen">
						<input type="radio" name="dictamen[' . $ele['elem_id'] . ']" value="3" />
					</div>
				</div>'."\n";
			if($fondo == 'claro') { $fondo = "obscuro"; } else { $fondo = "claro"; }
		}
	}
	echo '
				<div class="row">
					<div class="col-sm-12">
						<input type="submit" value="' . $lang['Enviar'] . '" />
					</div>
				</div>'."\n";
	echo '
				</form>'."\n";
}

elseif($accion === 'dictaminar') {

	if(validaAcceso('1175005', $dbpfx) == '1' || $_SESSION['usuario'] == $usudictcd || ($solovalacc != 1 && ($_SESSION['rol02']=='1') && $dictcdunico != '1')) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccNoAut'];
		redirigir('gestion.php');
	}
	$error = 'no';

	if($error == 'no') {
		foreach($dictamen as $k => $v) {

//-------------- 	DETERMINACIÓN DE NÚMERO DE ITEM	--------------------
			$preg6 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id[$k] . "'";
			$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de subordenes items! " . $preg6);
			$item = 1;
			while($dato6 = mysql_fetch_array($matr6)) {
				$preg5 = "SELECT op_item FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $dato6['sub_orden_id'] . "' ORDER BY op_item DESC LIMIT 1";
				$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden_productos! " . $preg5);
				$dato5 = mysql_fetch_array($matr5);
				if($dato5['op_item'] >= $item) {$item = $dato5['op_item'] + 1;}
			}
// ------
			$preg1 = "SELECT cde.*, cd.* FROM " . $dbpfx . "cambdevol_elementos cde, " . $dbpfx . "cambdevol cd WHERE cde.elem_id = '" . $k . "' AND cde.cd_id = cd.cd_id";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de elementos de cambio o devolución! " . $preg1);
			$ele = mysql_fetch_assoc($matr1);

			if($v == 1) {
// ------ Devolución autorizada. Inician los siguientes procesos:
// ------ 1.- Copia op_id original y lo deja libre para remover-cotizar-pedir;
// ------ 2.- Cambia op_tangible a 8 en op_id original;
// ------ 3.- Envía mensaje interno a usu_requiere para que sepa fue autorizada la devolución y esté preparado;
// ------ 4.- Notifica a Proveedor el requerimiento de Devolución y que pase por su refacción;
// ------ 5.- Guarda registros para emitir contrarecibo de entrega de devolución;
// ------ 6.- Determina pagos y documentos asociados al pedido origen y genera huerfanos y NC como sea apropiado.
// ------ 7.- Si la refacción devuelta era autorizada o estaba relacionada con una autorizada, marca la tarea como pendiente de refacciones.

				$preg2 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $ele['op_id'] . "'";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de orden productos! " . $preg2);
				$op = mysql_fetch_assoc($matr2);

				// --- Remueve las relaciones de autorizados a presupuestados y marca la tarea con refacciones pendientes
				if($op['op_pres'] != '1') {
					$param = "sub_orden_id = '" . $ele['sub_orden_id'] . "'";
					$sqlref = ['sub_refacciones_recibidas' => 1];
					ejecutar_db($dbpfx . 'subordenes', $sqlref, 'actualizar', $param);
				} else {
					$pregref = "SELECT sub_orden_id FROM " . $dbpfx . "orden_productos WHERE op_item_seg = '" . $ele['op_id'] . "'";
					$matrref = mysql_query($pregref) or die("ERROR: Fallo selección de orden productos! " . $pregref);
					$ref = mysql_fetch_assoc($matrref);
					$param = "sub_orden_id = '" . $ref['sub_orden_id'] . "'";
					$sqlref = ['sub_refacciones_recibidas' => 1];
					ejecutar_db($dbpfx . 'subordenes', $sqlref, 'actualizar', $param);
				}
				$param = "op_item_seg = '" . $ele['op_id'] . "'";
				$sqldata = ['op_item_seg' => 'null'];
				ejecutar_db($dbpfx . 'orden_productos', $sqldata, 'actualizar', $param);

				// --- Crea una copia del item original para la devolucion y lo deja en el pedido
				$op['op_id'] = NULL;
				$op['op_item'] = $item;
				$op['op_tangible'] = 8;
				$nvoopid = ejecutar_db($dbpfx . 'orden_productos', $op, 'insertar');
				bitacora($ele['orden_id'], $lang['NuevoItem'] . ' ' . $op['sub_orden_id'], $dbpfx, 'Para: ' . $usu[$ele['usu_requiere']]['nombre'] . ' ' . $usu[$ele['usu_requiere']]['apellidos'] . ' ' . $lang['DevoAuto'] . ' ' . $ele['nombre'] . ' ' . $lang['DevoAutoComp'], 3, '', '', $ele['usu_requiere']);
				$item++;

				// --- Remueve el item original del pedido y lo deja listo para reprocesar
				$param = "op_id = '" . $ele['op_id'] . "'";
				$sqldata = array();
				$sqldata['op_pedido'] = 0;
				$sqldata['op_fecha_promesa'] = 'null';
				$sqldata['op_recibidos'] = 0;
				$sqldata['op_ok'] = 0;
				$sqldata['op_autosurtido'] = 0;
				ejecutar_db($dbpfx . 'orden_productos', $sqldata, 'actualizar', $param);
				if($qv_activo == 1) {
					$opxml .= '                     <Ref op_id="' .  $ele['op_id'] . '" op_estatus="57" />'."\n";
				}
				// --- Ajusta el número de op_id con la nueva copia para continuar la devolución.
				$param = "elem_id = '" . $ele['elem_id'] . "'";
				$sqldata = [
					'op_id' => $nvoopid,
					'dictamen' => 10,
					'usu_dictamina' => $_SESSION['usuario'],
					'fecha_dictamen' => date('Y-m-d H:i:s')];
				ejecutar_db($dbpfx . 'cambdevol_elementos', $sqldata, 'actualizar', $param);

// ------ Almacena devoluciones para su posterior notificación
				$devols[$ele['pedido_id']][] = [
					'op_id' => $nvoopid,
					'cantidad' => $ele['cantidad'],
					'nombre' => $ele['nombre'],
					'causa' => $ele['motivo'],
				];

// ------ Localizar los mensajes de aviso al dictaminador y cerrarlos en automático ----
				$paracm = "orden_id = '" . $ele['orden_id'] . "' AND comentario LIKE '%cd_id=" . $ele['cd_id'] . '"%' . "'";
				unset($sqldata);
				$sqldata = ['fecha_visto' => date('Y-m-d H:i:s')];
				ejecutar_db($dbpfx . 'comentarios', $sqldata, 'actualizar', $paracm);

			} elseif($v == 2) {
// ------ Reemplazo autorizado. Inician los siguientes procesos:
// ------ 1.- Genera nueva tarea para alojar la refacción y en su caso la MO correspondiente.
// ------ 2.- Genera la OP a la nueva tarea para cotizar-pedir asociado a la descripción del remplazo.
// ------ 3.- Envía mensaje interno a usu_requiere para que sepa fue autorizado el reemplazo y esté preparado;
// ------ 4.- Si fue señalado, agrega un porcentaje a el costo de la tarea recien creada como descuento en destajo o comisiones al solicitante.

				if($sub_orden_id == '' || $req_id != $ele['cd_id']) {

// ------ Creando descuento sin monto, asociado al responsable para que conforme sea actualizada la tarea, el descuento se actualice.
					$sql_data_array = [
						'pago_monto' => 0,
						'pago_fecha' => date('Y-m-d H:i:m'),
						'usuario' => $_SESSION['usuario'],
						'usuario_pago_recibido' => $ele['usu_responsable'],
						'motivo' => $lang['ReemDe'] . ' ' . $ele['nombre'] . ' ' . $ele['motivo'] . ' ' . $lang['DeOt'] . ' <a href="ordenes.php?accion=consultar&orden_id=' . $ele['orden_id'] . '" target="_blank">' . $ele['orden_id'] . '</a>',
						'descuento' => 1,
						'pago_monto_origen' => 0
					];
					$sub_descuento = ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'insertar');
					unset($sql_data_array);

					$req_id = $ele['cd_id'];

// ------ Creando nueva tarea para seguimiento de trabajos y cotizaciones
					$sub['sub_refacciones_recibidas'] = 1;
					$sub['sub_descripcion'] = 'INTERNO: Reemplazo de ' . $ele['nombre'];
					$sub['sub_siniestro'] = 0;
					$sub['sub_reporte'] = 'Interno';
					$sub['sub_aseguradora'] = 0;
					$sub['sub_estatus'] = 102;
					$sub['sub_area'] = $ele['area'];
					$sub['orden_id'] = $ele['orden_id'];
					$sub['sub_operador'] = $ele['usu_responsable'];
					$sub['sub_descuento'] = $sub_descuento;
					$sub_orden_id = ejecutar_db($dbpfx . 'subordenes', $sub, 'insertar');
				}

// ------ Creando cada uno de los items a la Tarea
				$op['sub_orden_id'] = $sub_orden_id;
				$op['op_item'] = $item;
				$op['op_cantidad'] = $ele['cantidad'];
				$op['op_nombre'] = $ele['nombre'];
				$op['op_tangible'] = 1;
				$op['op_area'] = $ele['area'];
				$nvo_opid = ejecutar_db($dbpfx . 'orden_productos', $op, 'insertar');
				bitacora($ele['orden_id'], $lang['NuevoItem'] . ' ' . $op['sub_orden_id'], $dbpfx, 'Para: ' . $usu[$ele['usu_requiere']]['nombre'] . ' ' . $usu[$ele['usu_requiere']]['apellidos'] . ' ' . $lang['ReemAuto'] . ' ' . $ele['nombre'] . ' ' . $lang['ReemAutoComp'], 3, '', '', $ele['usu_requiere']);
				$item++;
				$param = "elem_id = '" . $ele['elem_id'] . "'";
				$sqldata = [
					'dictamen' => 20,
					'op_id' => $nvo_opid,
					'usu_dictamina' => $_SESSION['usuario'],
					'fecha_dictamen' => date('Y-m-d H:i:s')];
				ejecutar_db($dbpfx . 'cambdevol_elementos', $sqldata, 'actualizar', $param);

// ------ Localizar los mensajes de aviso al dictaminador y cerrarlos en automático ----
				$paracm = "orden_id = '" . $ele['orden_id'] . "' AND comentario LIKE '%cd_id=" . $ele['cd_id'] . '"%' . "'";
				unset($sqldata);
				$sqldata = ['fecha_visto' => date('Y-m-d H:i:s')];
				ejecutar_db($dbpfx . 'comentarios', $sqldata, 'actualizar', $paracm);

// ------ Localizar los mensajes de aviso al dictaminador y cerrarlos en automático ----
				$paracm = "orden_id = '" . $ele['orden_id'] . "' AND comentario LIKE '%cd_id=" . $ele['cd_id'] . '"%' . "'";
				unset($sqldata);
				$sqldata = ['fecha_visto' => date('Y-m-d H:i:s')];
				ejecutar_db($dbpfx . 'comentarios', $sqldata, 'actualizar', $paracm);

			} elseif($v == 3) {
// ------ Requerimiento rechazado, no hay devolución ni reemplazo.
// ------ 1.- Ajusta la partida a cancelado.
// ------ 2.- Envía mensaje a usuario que gestionó para avisar el rechazo.
// ------ 3.- Se capturan los motivos del rechazo.

				// --- Ajusta la partida a cancelado y envía mensaje al solicitante.
				$param = "elem_id = '" . $ele['elem_id'] . "'";
				$sqldata = [
					'dictamen' => 90,
					'usu_dictamina' => $_SESSION['usuario'],
					'fecha_dictamen' => date('Y-m-d H:i:s')];
				ejecutar_db($dbpfx . 'cambdevol_elementos', $sqldata, 'actualizar', $param);
				if($ele['tipo_cd'] == 1) { $tiporeq = $lang['Devolucion']; } else { $tiporeq = $lang['Reemplazo']; }
				bitacora($ele['orden_id'], $lang['SuReq'] . ' ' . $tiporeq . ' de ' . $ele['nombre'] . ' ' . $lang['FueRechazado'] . $lang['OT'] . ' ' . $ele['orden_id'], $dbpfx, 'Para: ' . $usu[$ele['usu_requiere']]['nombre'] . ' ' . $usu[$ele['usu_requiere']]['apellidos'] . ' ' . $lang['SuReq'] . ' ' . $tiporeq . ' de ' . $ele['nombre'] . ' ' . $lang['FueRechazado'] . $lang['OT'] . ' ' . $ele['orden_id'], 3, '', '', $ele['usu_requiere']);

// ------ Localizar los mensajes de aviso al dictaminador y cerrarlos en automático ----
				$paracm = "orden_id = '" . $ele['orden_id'] . "' AND comentario LIKE '%cd_id=" . $ele['cd_id'] . '"%' . "'";
				unset($sqldata);
				$sqldata = ['fecha_visto' => date('Y-m-d H:i:s')];
				ejecutar_db($dbpfx . 'comentarios', $sqldata, 'actualizar', $paracm);

			}
			if($qv_activo == 1 && $opxml != '') {
				$veh = datosVehiculo($ele['orden_id'], $dbpfx);
				$mtime = substr(microtime(), (strlen(microtime())-3), 3);
				$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
				$xml .= '       <Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
				$xml .= '               <Solicitud tiempo="0">57</Solicitud>'."\n";
				$xml .= '               <OT orden_id="' . $ele['orden_id'] . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'" foto_frontal="' . $veh['foto_frontal'] .'" foto_izquierda="' . $veh['foto_izquierda'] .'" foto_derecha="' . $veh['foto_derecha'] .'" foto_vin="' . $veh['foto_vin'] .'">'."\n";
				$xml .= $opxml;
				$xml .= '               </OT>'."\n";
				$xml .= '       </Comprador>'."\n";
				$xmlnom = $nick . '-' . $ele['orden_id'] . '-' . date('YmdHis') . $mtime . '.xml';
				file_put_contents("../qv-salida/".$xmlnom, $xml);
			}
			unset($opxml, $xml);
			actualiza_orden($ele['orden_id'], $dbpfx);
		}

// ------ Notifica a proveedores las devoluciones de partes y ajusta estatus del pedido ------
		foreach($devols as $ped_id => $partes) {
			$pregprov = "SELECT p.prov_nic, p.prov_razon_social, p.prov_representante, p.prov_email, ped.orden_id, ped.fecha_pedido FROM " . $dbpfx . "proveedores p, " . $dbpfx . "pedidos ped WHERE p.prov_id = ped.prov_id AND ped.pedido_id = '" . $ped_id . "'";
			$matrprov = mysql_query($pregprov) or die("ERROR: Fallo selección de proveedor para notificación! " . $pregprov);
			$prov = mysql_fetch_array($matrprov);
			// --- Ajusta estatus del pedido ---
			//$param = "pedido_id = '" . $ped_id . "'";
			//$sqlped = ['pedido_estatus' => '50'];
			//ejecutar_db($dbpfx . 'pedidos', $sqlped, 'actualizar', $param);
			bitacora($prov['orden_id'], $lang['PedidoDevolver'] . ' ' . $ped_id, $dbpfx);
			// --- Envía notificación de devolución al proveedor.
			$asunto = $lang['DevoluDe'] . ' ' . $nombre_agencia;
			$para = $prov['prov_email'];
			$respondera = (constant('EMAIL_PROVEEDOR_RESPONDER'));
			$concopia = (constant('EMAIL_PROVEEDOR_CC'));

// ------ Actualiza utilidades del pedido ----------------
			$nvautil = recalcUtilPed($ped_id, $dbpfx);

// ------ Construir el contenido -----------------
			$contenido = '<!-- BODY -->
<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h3>' . $lang['DevoluDe'] . ' ' . $nombre_agencia . '</h3>
				<p class="lead">' . $lang['Estimado'];
			if($prov['prov_representante'] != '') {
				$contenido .= ' ' . $prov['prov_representante'];
			} else {
				$contenido .= ' ' . $lang['Proveedor'];
			}
			$contenido .= "\n";
			$contenido .= '<br>' . $prov['prov_razon_social'] . "\n";
			$contenido .= '<br><br>'."\n";
			$contenido .= $lang['TextoDevol1'] . ' ' . $ped_id . ', ' . $lang['TextoDevol2'] . $partes[0]['causa'];
			$contenido .= '</p>
			</div>
		</td>
		<td></td>
	</tr>
</table>'."\n";
			$veh = datosVehiculo($prov['orden_id'], $dbpfx);
			$contenido .= '<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<table  bgcolor="#AECCF2">
					<tr><th>Solicitud:</th><td>' . $lang['Devolucion'] . '</td></tr>
               <tr><th>Detalles:</th><td>Nuestro Pedido: ' . $ped_id . '.  de fecha: ' . date('Y-m-d', strtotime($prov['fecha_pedido'])) . '</td></tr>
					<tr><th>Detalle de Refacciones para:</th><td>' . $veh['refacciones'] . '</td></tr>
				</table>
			</div>
		</td>
		<td></td>
	</tr>'."\n";
			$contenido .= '	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<table border=1 cellspacing=0 bgcolor="#AECCF2" width="100%">
					<tr>
						<th align="center">Cantidad</th>
						<th align="center">Nombre</th>
					</tr>'."\n";
			foreach($partes as $k => $v) {
				$contenido .= '					<tr>
						<td style="text-align: center; ">' . $v['cantidad'] . '</td>
            		<td>' . $v['nombre'] . '</td>
					</tr>'."\n";
			}
			$contenido .= '</table>
<table class="body-wrap" >
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h5>Atentamente:</h5>'."\n";
			$contenido .= '				<p>' . JEFE_DE_ALMACEN . '<br>'."\n";
			$contenido .= '				' .$agencia_razon_social. '<br>
				' .$agencia_direccion. '<br>
				Col. ' .$agencia_colonia. ' ' .$agencia_municipio. '<br>
				C.P.: ' .$agencia_cp. ' . ' .$agencia_estado. '<br>'."\n";
			$contenido .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' .EMAIL_DE_ALMACEN. '">' .EMAIL_DE_ALMACEN. '</a><br>'."\n";
			$contenido .= '				Tels: ' .$agencia_telefonos. '<br>
				' . TELEFONOS_ALMACEN . '<br>
				</p>
				<p style="font-size:9px;font-weight:bold;">Este mensaje fue
        		enviado desde un sistema automático, si desea hacer algún
        		comentario respecto a esta notificación o cualquier otro asunto
        		respecto al Centro de Reparación por favor responda a los
        		correos electrónicos o teléfonos incluidos en el cuerpo de este
        		mensaje. De antemano le agradecemos su atención y preferencia.</p>
			</div>
		</td>
		<td></td>
	</tr>
</table>
<!-- /BODY -->'."\n";
			include('parciales/notifica2.php');
		}
		redirigir('gestion.php');
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('cambdevol.php?accion=registrar');
	}
}

elseif($accion === 'autorizados') {
// ------ Seguimiento a devolución o cambio de refacciones.
	if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol13']=='1'))) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccNoAut'];
		redirigir('usuarios.php');
	}

	if(isset($feini) && $feini != '0000-00-00') {
		$feini = date('Y-m-d 00:00:00', strtotime($feini));
		if(!isset($fefin) || $fefin == '' || $fefin == '0000-00-00') {
			$fefin = date('Y-m-t 23:59:59', strtotime($feini));
		} else {
			$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
		}
	} else {
		$feini = date('Y-m-01 00:00:00');
		$fefin = date('Y-m-t 23:59:59');
	}
	$prega = " fecha_dictamen >= '" . $feini . "' AND fecha_dictamen <= '" . $fefin . "' ";	

	echo 'Proveedor: ' . $prov_id;

	if($prov_id > 0) {
		$prega .= " AND prov_id = '" . $prov_id . "' ";
	}

	include('parciales/cambdevol-menu.php');
	echo '
				<div class="row"> <!-box header del título. -->
					<div class="col-sm-12">
						<div class="content-box-header">
							<div class="panel-title">
		  						<h2>' . $lang['ReqDict'] . '</h2> 
							</div>
					  	</div>
					</div>
				</div>'."\n";

	require_once("calendar/tc_calendar.php");
	echo '				<div class="row">
					<form action="cambdevol.php?accion=autorizados&prov_id=' . $prov_id . '" method="post" enctype="multipart/form-data" name="filtrorep">
					<div class="col-sm-3">
						' . $lang['FeIni'] . '<br>'."\n";
		//instantiate class and set properties
		$myCalendar = new tc_calendar("feini", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
		//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		//$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2011, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();
	echo '						<input type="submit" value="' . $lang['Enviar'] . '" />
					</div>
					<div class="col-sm-3">
						' . $lang['FeFin'] . '<br>'."\n";
		//instantiate class and set properties
		$myCalendar = new tc_calendar("fefin", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
		//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		//$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2011, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();
	echo '					</div>'."\n";
/*	echo '					<div class="col-sm-2">
						' . $lang['Proveedor'] . '<br>
						<select name="prov_id" size="1" onchange="document.filtrorep.submit()";>
							<option value="">' . $lang['TodosProv'] . '</option>'."\n";
	foreach($provs as $k => $v) {
		echo '							<option value="' . $k . '" ';
		if($prov_id == $k) { echo 'selected '; }
		echo '>' . $v['nic'] . '</option>'."\n";
	}
	echo '						</select>
					</div>
					<div class="col-sm-2">
						' . $lang['TipoReq'] . '<br>
						<select name="req_tipo" size="1" onchange="document.filtrorep.submit()";>
							<option value="">' . $lang['TodosTipos'] . '</option>'."\n";
	
	echo '						</select>
					</div>
					<div class="col-sm-2">
						' . $lang['Estatus'] . '<br>
						<select name="req_estatus" size="1" onchange="document.filtrorep.submit()";>
							<option value="">' . $lang['TodosEstatus'] . '</option>'."\n";
	
	echo '						</select>
					</div>'."\n"; */
	echo '					</form>
				</div>'."\n";

	echo '
				<form action="cambdevol.php?accion=devolver" method="post" enctype="multipart/form-data">
				<div class="row cabeza_tabla">
					<div class="col-sm-1 cen">Req ID / OT</div>
					<div class="col-sm-2">' . $lang['Proveedor'] . '</div>
					<div class="col-sm-3">' . $lang['Vehículo'] . '</div>
					<div class="col-sm-2">' . $lang['Solicitante'] . '</div>
					<div class="col-sm-4">' . $lang['Motivo'] . '</div>
				</div>'."\n";

	if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol08'] == '1'))) {
		echo '				<div class="row">
					<div class="col-sm-2">' . $lang['PersonaRecibe'] . '</div>
					<div class="col-sm-7 izq"><input type="text" name="recibe" size="45" required /></div>
				</div>'."\n";
	}

	$preg1 = "SELECT c.* FROM " . $dbpfx . "cambdevol_elementos ce, " . $dbpfx . "cambdevol c WHERE ce.cd_id = c.cd_id AND ce.dictamen >= '10' AND ";
	$preg1 .= $prega;
	$preg1 .= " GROUP BY c.cd_id";
//	echo $preg1;
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de elementos de cambio o devolución! " . $preg1);
	$fondo = "claro"; $idx = 0;
	while($cds = mysql_fetch_array($matr1)) {
		$orden_id = $cds['orden_id'];
		$veh = datosVehiculo($orden_id,$dbpfx);
		echo '				<div class="row ' . $fondo . '">
					<div class="col-sm-1 cen">
						' . $cds['cd_id'] . ' / ' . $orden_id . '
					</div>
					<div class="col-sm-2">
						' . $provs[$cds['prov_id']]['nic'] . '
					</div>
					<div class="col-sm-3">
						' . $veh['completo'] . '
					</div>
					<div class="col-sm-2">
						' . $usu[$cds['usu_responsable']]['nombre'] . ' ' . $usu[$cds['usu_responsable']]['apellidos'] . '
					</div>
					<div class="col-sm-4">
						' . $cds['motivo'] . '
					</div>
				</div>'."\n";
		$preg2 = "SELECT * FROM " . $dbpfx . "cambdevol_elementos WHERE cd_id = '" . $cds['cd_id'] . "' AND dictamen >= '10'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de elementos de cambio o devolución! " . $preg2);
		while($ele = mysql_fetch_array($matr2)) {
			echo '				<div class="row ' . $fondo . '">
					<div class="col-sm-1">'."\n";
// ------ Vacio para mejorar presentación	-----------
			echo '					</div>
					<div class="col-sm-8">'."\n";
			echo '						';
			if($ele['dictamen'] < 90) {
				if($ele['tipo_cd'] == 1 ) {
					$tipo_cd = $lang['Devolucion'];
					if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol08'] == '1'))) {
						echo '<input type="checkbox" name="item[' . $idx . ']" value="1" />
						<input type="hidden" name="descrip[' . $idx . ']" value="' . $ele['cantidad'] . ' ' . $tipo_cd . ' de ' . $ele['nombre'] . '" />
						<input type="hidden" name="elemento[' . $idx . ']" value="' . $ele['elem_id'] . '" />
						<input type="hidden" name="monto[' . $idx . ']" value="' . $ele['monto'] . '" />
						<input type="hidden" name="orden_id[' . $idx . ']" value="' . $orden_id . '" />
						<input type="hidden" name="req_id[' . $idx . ']" value="' . $cds['cd_id'] . '" />
						<input type="hidden" name="pedido_id[' . $idx . ']" value="' . $cds['pedido_id'] . '" />
						<input type="hidden" name="prov_id[' . $idx . ']" value="' . $cds['prov_id'] . '" />'."\n";
						echo '						';
						$idx++;
					}
				} else {
					$tipo_cd = $lang['Reemplazo'];
				}
			} elseif($ele['dictamen'] == 99) {
				if($ele['tipo_cd'] == 1 ) {
					$tipo_cd = $lang['DevolucionTer'];
					echo '<a href="cambdevol.php?accion=contrarec&dev_id=' . $ele['dev_id'] . '">' . $lang['Devolucion'] . ' ' . $ele['dev_id'] . '</a> ';
				} else {
					$tipo_cd = $lang['ReemplazoTer'];
				}
			} else {
				if($ele['tipo_cd'] == 1 ) {
					$tipo_cd = $lang['DevolucionCan'];
				} else {
					$tipo_cd = $lang['ReemplazoCan'];
				}
			}
			
			echo $ele['cantidad'] . ' ' . $tipo_cd . ' de ' . $ele['nombre'] . '
					</div>
				</div>'."\n";
		}
		if($fondo == 'claro') { $fondo = "obscuro"; } else { $fondo = "claro"; }
	}
	if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol08'] == '1'))) {
		echo '
				<div class="row">
					<div class="col-sm-12">
						<input type="submit" value="' . $lang['Enviar'] . '" />
					</div>
				</div>'."\n";
		echo '
				</form>'."\n";
	}
}

elseif($accion === 'confirmar') {

	if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol08']=='1'))) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccNoAut'];
		redirigir('usuarios.php');
	}

// ------ Verificando que es la devolución para un solo proveedor

	$error = 'no';
	foreach($prov_id as $k => $v) {
		if($item[$k] == 1) {
			$provedores[$v] = 1;
			$elprov = $v;
		}
	}
	if(count($provedores) > 1 || count($provedores) < 1) {
		$error = 'si';
	}

	if($error == 'no') {
		echo '
				<div class="row"> <!-box header del título. -->
					<div class="col-sm-12">
						<div class="content-box-header">
							<div class="panel-title">
		  						<h2>' . $lang['PartesDev'] . ' ' . $provs[$elprov]['razon'] . '</h2>
							</div>
					  	</div>
					</div>
				</div>
				<div class="row">
				</div>'."\n";

		echo '				<div class="row control">
					<div class="col-sm-1 cen">' . $lang['Req'] . '</div>
					<div class="col-sm-1 cen">OT</div>
					<div class="col-sm-1 cen">' . $lang['Pedido'] . '</div>
					<div class="col-sm-7">' . $lang['Parte'] . '</div>
				</div>' . "\n";
		foreach($item as $k => $v) {
			echo '				<div class="row control">
					<div class="col-sm-1 cen">' . $req_id[$k] . '</div>
					<div class="col-sm-1 cen">' . $orden_id[$k] . '</div>
					<div class="col-sm-1 cen">' . $pedido_id[$k] . '</div>
					<div class="col-sm-7">' . $descrip[$k] . '</div>
				</div>'."\n";
		}
		echo '				<form action="cambdevol.php?accion=regdevol" method="post" enctype="multipart/form-data">
				<div class="row">
					<div class="col-sm-2">' . $lang['PersonaEntrega'] . '</div>
					<div class="col-sm-7 izq"><strong>' . $usu[$_SESSION['usuario']]['nombre'] . ' ' . $usu[$_SESSION['usuario']]['apellidos'] . '</strong></div>
				</div>
				<div class="row">
					<div class="col-sm-2">' . $lang['PersonaRecibe'] . '</div>
					<div class="col-sm-7 izq"><input type="text" name="recibe" size="45" required /></div>
				</div>'."\n";
		if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol08'] == '1'))) {
			echo '
				<div class="row">
					<div class="col-sm-12">
						<input type="submit" value="' . $lang['Enviar'] . '" />
						<input type="hidden" name="item" value="' . $item . '" />
						<input type="hidden" name="descrip" value="' . $descrip . '" />
						<input type="hidden" name="elemento" value="' . $elemento . '" />
						<input type="hidden" name="orden_id" value="' . $orden_id . '" />
						<input type="hidden" name="req_id" value="' . $req_id . '" />
						<input type="hidden" name="pedido_id" value="' . $pedido_id . '" />
						<input type="hidden" name="prov_id" value="' . $prov_id . '" />
					</div>
				</div>'."\n";
			echo '
				</form>'."\n";
		}
	} else {
		echo $lang['ProveedorMas'];
	}
}

elseif($accion === 'devolver') {

	if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol08']=='1'))) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccNoAut'];
		redirigir('usuarios.php');
	}

// ------ Verificando que es la devolución para un solo proveedor

	$error = 'no';
	foreach($prov_id as $k => $v) {
		if($item[$k] == 1) {
			$provedores[$v] = 1;
			$elprov = $v;
		}
	}
	if(count($provedores) > 1 || count($provedores) < 1) {
		$error = 'si'; $mensaje .= $lang['ProveedorMas'] . '<br>';
	}
	$recibe = preparar_entrada_bd($recibe);
	if($recibe == '' || strlen($recibe) <= 5) {
		$error = 'si'; $mensaje .= 'El nombre de quien recibe es muy corto o está vacio.<br>';
	}

	if($error == 'no') {
		$sqldata = [
			'dev_usuario_id' => $_SESSION['usuario'],
			'prov_id' => $elprov,
			'dev_persona_recibio' => $recibe
		];
		$dev_id = ejecutar_db($dbpfx.'cambdevol_devoluciones', $sqldata, 'insertar');
		unset($sqldata);

		$sqldata = [
			'dictamen' => '99',
			'dev_id' => $dev_id,
			'fecha_devuelto' => date('Y-m-d H:i:s')
		];

		$pedidos = array();
		foreach($item as $k => $v) {
			$param = "elem_id = '" . $elemento[$k] . "'";
			ejecutar_db($dbpfx.'cambdevol_elementos', $sqldata, 'actualizar', $param);
			foreach($usu as $uk => $uv) {
				if($uv['rol13'] == '1' && $uv['activo'] == '1') {
					bitacora($orden_id[$k], $lang['ItemDevuelto'] . ' ' . $descrip[$k] . ' ' . $lang['Pedido'] . ' ' . $pedido_id[$k], $dbpfx, 'Para: ' . $uv['nombre'] . ' ' . $uv['apellidos'] . ' ' . $lang['ItemDevuelto'] . ' ' . $descrip[$k] . ' ' . $lang['Pedido'] . ' ' . $pedido_id[$k], 3, '', '', $uk);
				}
			}
			$pedidos[$pedido_id[$k]] = $pedidos[$pedido_id[$k]] + $monto[$k];
		}
		unset($sqldata);
		foreach($pedidos as $k => $v) {
			$preg1 = "SELECT pago_id, fact_id FROM " . $dbpfx . "pagos_facturas WHERE pedido_id = '" . $k . "'";
			$matr1 = mysql_query($preg1) or die("Error: no se conecto con pagos_facturas! " . $preg1);
			$fila1 = 0;
			while($pags = mysql_fetch_array($matr1)) {
				if($pags['fact_id'] > 0) {
					$sqldata = ['fact_id' => 'null'];
					$param = " pago_id ='" . $pags['pago_id'] . "' AND fact_id = '" . $pags['fact_id'] . "'";
					ejecutar_db($dbpfx . 'pagos_facturas', $sqldata, 'actualizar', $param);
					// --- marcar factura como no pagada "facturas por cobrar" ------
					unset($sqldata);
					$sqldata = ['pagada' => 0, 'f_pago' => 'null'];
					$param = " fact_id ='" . $pags['fact_id'] . "'";
					ejecutar_db($dbpfx . 'facturas_por_pagar', $sqldata, 'actualizar', $param);
					unset($sqldata);
					$fila1++;
				}
			}
			if($fila1 > 0) {
				$sqldata = ['pedido_estatus' => 55];
				$param = "pedido_id ='" . $k . "'";
				ejecutar_db($dbpfx . 'pedidos', $sqldata, 'actualizar', $param);
				unset($sqldata);
			} else {
				$preg2 = "SELECT op_id FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $k . "'";
				$matr2 = mysql_query($preg2) or die("Error: no se conecto con orden_productos! " . $preg2);
				$fila2 = mysql_num_rows($matr2);
				if($fila2 == 0) {
					mysql_data_seek($matr1,0);
					while($pags = mysql_fetch_array($matr1)) {
						$sqldata = ['pedido_id' => 'null', 'fact_id' => 'null'];
						$param = " pago_id ='" . $pags['pago_id'] . "'";
						ejecutar_db($dbpfx . 'pagos_facturas', $sqldata, 'actualizar', $param);
					}
					unset($sqldata);
					$sqldata = ['pedido_estatus' => 92];
					$param = "pedido_id ='" . $k . "'";
					ejecutar_db($dbpfx . 'pedidos', $sqldata, 'actualizar', $param);
					unset($sqldata);
				}
			}
		}
	} else {
		$_SESSION['msjerror'] = $mensaje;
	}
	redirigir('cambdevol.php?accion=autorizados');
}

elseif($accion === 'contrarec') {

	if(validaAcceso('1175005', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol13']=='1'))) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccNoAut'];
		redirigir('usuarios.php');
	}

	$preg1 = "SELECT * FROM " . $dbpfx . "cambdevol_devoluciones WHERE dev_id = '" . $dev_id . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de devoluciones! " . $preg1);
	$dev = mysql_fetch_array($matr1);

	echo '					<table cellpadding="2" cellspacing="0" border="0" width="840">'."\n";
	echo '						<tr class="cabeza_tabla">
							<td colspan="2"><h2>' . $lang['DevoluDe'] . ' ' . $dev_id . '<br>' . $lang['ParaProv'] . ' ' . $provs[$dev['prov_id']]['razon'] . '</h2></td>
						</tr>'."\n";
	echo '						<tr><td><strong>' . $agencia_razon_social . '</strong><br>
							' . $agencia_direccion . '.<br>
							' . $agencia_colonia . ', '  . $agencia_municipio . '.<br>
							' . $agencia_cp . '. '  . $agencia_estado . '. México.<br>
							' . $agencia_telefonos . '</td>
							<td style="text-align:right;"><img src="particular/logo-agencia.png" alt="' . $agencia_razon_social . '"></td>
						</tr>
					</table>'."\n";
	if($_SESSION['rol08']=='1') {
// ------ Colecta las OTs involucradas para colocar una copia del contrarecibo de devolución ------
		echo '					<form action="cambdevol.php?accion=subecr" method="post" enctype="multipart/form-data">
					<input type="hidden" name="dev_id" value="' . $dev_id . '" />'."\n";
	}
	echo '					<table cellpadding="2" cellspacing="0" border="1" width="840">'."\n";
	echo '						<tr class="cabeza_tabla">'."\n";
	echo '							<td>' . $lang['OT'] . '</td>'."\n";
	echo '							<td>' . $lang['Pedido'] . '</td>'."\n";
	echo '							<td>' . $lang['Cantidad'] . '</td>'."\n";
	echo '							<td>' . $lang['NombreItem'] . '</td>'."\n";
	echo '							<td>' . $lang['FecRec'] . '</td>'."\n";
	echo '							<td>' . $lang['Costo'] . '</td>'."\n";
	echo '						<tr>'."\n";
	$preg2 = "SELECT d.*, o.op_costo, o.op_fecha_promesa FROM " . $dbpfx . "cambdevol_elementos d, " . $dbpfx . "orden_productos o WHERE d.dev_id = '" . $dev_id . "' AND o.op_id = d.op_id";
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de devoluciones! " . $preg2);
	$costotot = 0;
	while ($elem = mysql_fetch_array($matr2)) {
		$preg3 = "SELECT * FROM " . $dbpfx . "cambdevol WHERE cd_id = '" . $elem['cd_id'] . "'";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de devoluciones! " . $preg3);
		$cdid = mysql_fetch_array($matr3);
		echo '						<tr class="' . $fondo . '">'."\n";
		echo '							<td style="text-align: center;"><a href="ordenes.php?accion=consultar&orden_id=' . $cdid['orden_id'] . '" target="_blank">' . $cdid['orden_id'] . '<input type="hidden" name="ords[' . $cdid['orden_id'] . ']" value="1" /></td>'."\n";
		echo '							<td style="text-align: center;"><a href="pedidos.php?accion=consultar&pedido=' . $cdid['pedido_id'] . '" target="_blank">' . $cdid['pedido_id'] . '</td>'."\n";
		echo '							<td style="text-align: center;">' . $elem['cantidad'] . '</td>'."\n";
		echo '							<td>' . $elem['nombre'] . '<br>' . $lang['Motivo'] . ': ' . $cdid['motivo'] . '</td>'."\n";
		echo '							<td style="text-align: center;">' . date('Y-m-d', strtotime($elem['op_fecha_promesa'])) . '</td>'."\n";
		echo '							<td style="text-align: right;">$' . number_format($elem['op_costo'], 2) . '</td>'."\n";
		echo '						<tr>'."\n";
		$costotot = $costotot + $elem['op_costo'];
		if($fondo == 'obscuro') { $fondo = 'claro'; } else { $fondo = 'obscuro'; }
	}
	$iva = round(($costotot * $provs[$dev['prov_id']]['iva']), 2);
	$total = $costotot + $iva;
	echo '						<tr>'."\n";
	echo '							<td colspan="5" style="text-align: right;">' . $lang['SubTot'] . '</td>'."\n";
	echo '							<td style="text-align: right;">$' . number_format($costotot, 2) . '</td>'."\n";
	echo '						</tr>'."\n";
	echo '						<tr>'."\n";
	echo '							<td colspan="5" style="text-align: right;">' . $lang['Impuestos'] . '</td>'."\n";
	echo '							<td style="text-align: right;">$' . number_format($iva, 2) . '</td>'."\n";
	echo '						</tr>'."\n";
	echo '						<tr>'."\n";
	echo '							<td colspan="5" style="text-align: right;">' . $lang['Total'] . '</td>'."\n";
	echo '							<td style="text-align: right;">$' . number_format($total, 2) . '</td>'."\n";
	echo '						</tr>'."\n";
	echo '					</table>'."\n";
	echo '					<br>'."\n";
	echo '					<table cellpadding="2" cellspacing="0" border="1" width="840">'."\n";
	echo '						<tr class="cabeza_tabla">'."\n";
	echo '							<td style="text-align: center;">' . $lang['PersonaEntrega'] . '</td>'."\n";
	echo '							<td style="text-align: center;">' . $lang['PersonaRecibe'] . '</td>'."\n";
	echo '						</tr>'."\n";
	echo '						<tr>'."\n";
	echo '							<td style="text-align: center;"><br><br><br><br>' . $usu[$dev['dev_usuario_id']]['nombre'] . ' ' . $usu[$dev['dev_usuario_id']]['apellidos'] . '</td>'."\n";
	echo '							<td style="text-align: center;"><br><br><br><br>' . $dev['dev_persona_recibio'] . '</td>'."\n";
	echo '						</tr>'."\n";
	echo '					</table>'."\n";
	echo '					<table cellpadding="2" cellspacing="0" border="0" width="840">'."\n";
	echo '						<tr><td class="control" colspan="2" style="text-align:left;">
										' . $lang['SubirCR'] . ' <input type="file" name="imagencr" /> <input type="submit" value="' . $lang['Enviar'] . '" />
									</td></tr>'."\n";
	echo '						<tr><td colspan="2" style="text-align:left;">
								<div class="control"><br>
									<a href="cambdevol.php?accion=autorizados"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="' . $lang['ListaDic'] . '" title="' . $lang['ListaDic'] . '"></a>&nbsp;<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="' . $lang['ImprimirCR'] . '" title="' . $lang['ImprimirCR'] . '"></a>'."\n";
	if($dev['dev_doc_id'] != '') {
		echo '									<a href="' . DIR_DOCS . $dev['dev_doc_id'] . '" target="_blank"><img src="' . DIR_DOCS . 'documento.png" alt="' . $lang['ContraRec'] . '" title="' . $lang['ContraRec'] . '"></a>'."\n";
	}
	echo '						</div></td></tr>'."\n";
	echo '					</table>'."\n";
	echo '					</form>'."\n";
	echo '				</div>'."\n";
}

elseif($accion === 'subecr') {
	foreach($ords as $k => $v) {
		$resultado = agrega_documento($k, $_FILES['imagencr'], $lang['ContraRec'] . ' ' . $dev_id, $dbpfx, '', '1');
		bitacora($k, $lang['DocAgregado'], $dbpfx);
	}
	$sqldata['dev_doc_id'] = $resultado['nombre'];
	$param = "dev_id = '" . $dev_id . "'";
	ejecutar_db($dbpfx . 'cambdevol_devoluciones', $sqldata, 'actualizar', $param);
	redirigir('cambdevol.php?accion=contrarec&dev_id=' . $dev_id);
}

echo '			</div>
		</div>
	</div>'."\n";
include('parciales/pie.php');
