<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if (!isset($_SESSION['usuario']) || $_SESSION['codigo'] >= '2000') {
	redirigir('usuarios.php');
}

// ----------------  Obtener nombres de aseguradoras   ------------------- 

		$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_alta, aseguradora_v_email, aseguradora_razon_social, aseguradora_nic, aseguradora_saltapres, calc_pintura, area_ut, preciout FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!" . $consulta);
		while ($aseg = mysql_fetch_array($arreglo)) {
			define('ASEGURADORA_' . $aseg['aseguradora_id'], $aseg['aseguradora_logo']);
			define('ASEGURADORA_NIC_' . $aseg['aseguradora_id'], $aseg['aseguradora_nic']);
			define('AUTOSURTIDO_' . $aseg['aseguradora_id'], $aseg['autosurtido']);
			$asesaltapres[$aseg['aseguradora_id']] = $aseg['aseguradora_saltapres'];
			$ut[$aseg['aseguradora_id']] = $aseg['preciout'];
			$asurt[$aseg['aseguradora_id']] = $aseg['autosurtido'];
			$c_pint[$aseg['aseguradora_id']] = $aseg['calc_pintura'];
			$area_ut[$aseg['aseguradora_id']] = $aseg['area_ut'];
			$asenoti[$aseg['aseguradora_id']]['alta'] = $aseg['aseguradora_alta'];
			$asenoti[$aseg['aseguradora_id']]['email'] = $aseg['aseguradora_v_email'];
			$asenoti[$aseg['aseguradora_id']]['razon'] = $aseg['aseguradora_razon_social'];
		}
		define('ASEGURADORA_0', 'particular/logo-particular.png');
		define('ASEGURADORA_NIC_0', 'Particular');
		define('AUTOSURTIDO_0', '1');
		$ut[0] = $preciout;

//  ----------------  Nombres de usuarios   -------------------

	$pregus = "SELECT * FROM " . $dbpfx . "usuarios WHERE acceso = '0' ORDER BY nombre,apellidos";
	$matrus = mysql_query($pregus) or die("ERROR: Fallo selección de usuarios!");
	while($usu = mysql_fetch_array($matrus)) {
		$usr[$usu['usuario']]['nom'] = $usu['nombre'] . ' ' . $usu['apellidos'];
		if($usu['rol02'] == 1) { $usr[$usu['usuario']]['rol02'] = 1; }
		if($usu['rol04'] == 1) { $usr[$usu['usuario']]['rol04'] = 1; }
		if($usu['rol06'] == 1) { $usr[$usu['usuario']]['rol06'] = 1; }
		if($usu['rol09'] == 1) { $usr[$usu['usuario']]['rol09'] = 1; }
		if($usu['rol10'] == 1) { $usr[$usu['usuario']]['rol10'] = 1; }
		if($usu['activo'] == 1) { $usr[$usu['usuario']]['activo'] = 1; }
	}

if (($accion==='insertar') || ($accion==='actualizar') || ($accion==='avaluo') || ($accion==='mod_avaluo') || ($accion==='confcancelar') || $accion==='termcotizar' || $accion==='imprimepres'  || $accion==='envio' || $accion==='compara' || $accion==='presupuesto' || $accion==='imprimeaut' || $accion==='cppresauth' || $accion==='espera') {
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php'); 
	echo '	<div id="body">'."\n";
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">'."\n";
}

if (($accion==="crear") || ($accion==="complemento") || ($accion==="adicional")) {
	$funnum = 1045000;

	$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
   $matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  	$orden = mysql_fetch_array($matriz);

	echo '
	<br>
	<form action="presupuestos.php?accion=insertar" method="post" enctype="multipart/form-data" name="rapida">
	<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
		<tr><td colspan="2" style="text-align:left;">' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . $lang['Placas'] . $orden['orden_vehiculo_placas'] . ' <strong>OT ' . $orden_id . '</strong></td></tr>';
	echo '<input type="hidden" name="orden_estatus" value="' . $orden['orden_estatus'] . '" />'."\n";
	if (isset($_SESSION['pres']['mensaje'])) {
		echo '		<tr><td colspan="2"><span class="alerta">' . $_SESSION['pres']['mensaje'] . '</span></td></tr>';
		}
 	if ($accion==="complemento") {
		$pregunta2 = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub_orden_id . "'";
//		echo $pregunta2;
	   $matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
  		$sub = mysql_fetch_array($matriz2);
  	}
 	if ($orden['orden_servicio']=="4") {
 		echo '<input type="hidden" name="siniestro" value="1" />'."\n";
 		if($accion==="complemento") {
  			echo '		<tr class="cabeza_tabla"><td colspan="2">Agregar complemento a Presupuesto Solicitado Inicialmente</td></tr>
		<tr><td>' . $lang['NumSin'] . ':</td><td style="text-align:left;">' . $sub['sub_reporte'] . '<input type="hidden" name="reporte" value="' . $sub['sub_reporte'] . '" /><input type="hidden" name="poliza" value="' . $sub['sub_poliza'] . '" /></td></tr>';
			echo '		<tr><td>Aseguradora: </td><td style="text-align:left;"><img src="' . constant('ASEGURADORA_' . $sub['sub_aseguradora']) . '" alt=""><input type="hidden" name="aseguradora" value="' . $sub['sub_aseguradora'] . '" /><input type="hidden" name="nombre_archivo" value="' . $sub['sub_doc_adm'] . '" /></td></tr>';
 		} elseif($accion==="crear") {
			echo '		<tr class="cabeza_tabla"><td colspan="2">Revisión y comparación de orden de admisión contra daño físico</td></tr>'."\n";
			echo '		<tr><td>Aseguradora: </td><td style="text-align:left;">
			<select name="aseguradora" size="1">
				<option value="Seleccione..." >Seleccione...</option>';
			$pregunta3 = "SELECT aseguradora_id, aseguradora_nic, aseguradora_v_email, aseguradora_alta FROM " . $dbpfx . "aseguradoras";
			$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
			while($aseguradora = mysql_fetch_array($matriz3)) {
				echo '				<option value="' . $aseguradora['aseguradora_id'] . '">' . $aseguradora['aseguradora_nic'] . '</option>'."\n";
			}
			echo '			</select><br>';
			echo '		</td></tr>';
//			echo '		<tr class="obscuro"><td>Agregar imagen escaneada en<br>JPG de la orden de admisión</td><td style="text-align:left;"><input type="file" name="orden_adm" size="20" /></td></tr>'."\n";
			echo '		<tr class="claro"><td>' . $lang['NumSin'] . '</td><td style="text-align:left;"><input type="text" name="reporte" size="30" maxlength="30" value="';
			if (isset($_SESSION['pres']['reporte'])) { echo $_SESSION['pres']['reporte']; }
			echo '" /></td></tr>'."\n";
			echo '		<tr class="obscuro"><td>' . $lang['NumPoliza'] . '</td><td style="text-align:left;"><input type="text" name="poliza" size="30" maxlength="30" value="';
			if (isset($_SESSION['pres']['poliza'])) { echo $_SESSION['pres']['poliza']; }
			echo '" /></td></tr>'."\n";
			echo '		<tr class="obscuro"><td>Paga deducible?: </td><td style="text-align:left;">Sí<input type="radio" name="pagadedu" size="30" maxlength="30" value="1"';
			if ($_SESSION['pres']['pagadedu']== '1') { echo ' checked="checked"'; }
			echo '" /> | No<input type="radio" name="pagadedu" size="30" maxlength="30" value="2"';
			if ($_SESSION['pres']['pagadedu']== '2') { echo ' checked="checked"'; }
			echo '" /></td></tr>'."\n";

			if($ajustadores == '1') {
				echo '		<tr><td>Nombre y apellidos de ajustador: </td><td style="text-align:left;"><input type="text" name="nomajus" size=30" maxlength="45" value="';
				if (isset($_SESSION['pres']['nomajus'])) { echo $_SESSION['pres']['nomajus']; }
				echo '" /></td></tr>';
				echo '		<tr><td>Número de ajustador: </td><td style="text-align:left;"><input type="text" name="idajus" size="15" maxlength="30" value="';
				if (isset($_SESSION['pres']['idajus'])) { echo $_SESSION['pres']['idajus']; }
				echo '" /></td></tr>';
			}
 		} elseif($accion==="adicional") {
			echo '		<tr class="cabeza_tabla"><td colspan="2">Tarea Adicional</td></tr>
		<tr><td>' . $lang['NumSinAseg'] . ': </td><td style="text-align:left;">
			<select name="asegrep" size="1">
				<option value="0" >Particular</option>'."\n";
			include('particular/otros_tipos.php');
			$preg3 = "SELECT sub_reporte, sub_aseguradora, sub_poliza FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_aseguradora > '0' AND sub_estatus < '190' GROUP BY sub_reporte";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo seleccion subordenes!");
			while($sub = mysql_fetch_array($matr3)) {
				echo '				<option value="' . $sub['sub_reporte'] . '|' . $sub['sub_aseguradora'] . '|' . $sub['sub_poliza'] . '">' . constant('ASEGURADORA_NIC_' . $sub['sub_aseguradora']) . ': ' . $sub['sub_reporte'] . '</option>'."\n";
			}
			echo '			</select><br>';
			echo '		</td></tr>'."\n";
			echo '<input type="hidden" name="adicional" value="1" />';
		}
	} elseif($orden['orden_servicio']=="2" && !is_null($ConvenioGarantia)) {
		echo '		<tr><td>Tipo de Servicio: </td><td style="text-align:left;"><img src="' . constant('ASEGURADORA_' . $ConvenioGarantia) . '" alt="">
			<input type="hidden" name="aseguradora" value="' . $ConvenioGarantia . '" />
			<input type="hidden" name="siniestro" value="0" />
			<input type="hidden" name="orden_garantia" value="' . $orden['orden_garantia'] . '" />
			<input type="hidden" name="tipo_servicio" value="' . $orden['orden_servicio'] . '" />
			</td></tr>'."\n";
	}
	echo '		<tr><td valign="top">Seleccione el Área de Servicio</td></tr>
		<tr><td>
			<select name="areas" id="areas" size="1" onchange="componerPaquetes(document.rapida.areas[selectedIndex].value)">
				<option value="" >Seleccione...</option>';
	for($i=1;$i<=$num_areas_servicio;$i++) {
		echo '			<option value="' . $i . '"';
		if($sub['sub_area'] == $i) { echo ' selected '; } // Futuro de 20130204
		echo '>' . constant('NOMBRE_AREA_' . $i) . '</option>'."\n";
	}
	echo '			</select>
			</td>
		</tr>'."\n";
	echo '			<tr><td>Seleccionar un Paquete de Servicio</td></tr>
				<tr><td>
					<select name="paquetes" size="1">'."\n";
	echo '				</select>
				</td></tr>'."\n";
	echo '			<tr class="cabeza_tabla"><td>Descripción</td></tr>
				<tr><td><textarea name="descripcion" rows="5" cols="40">';
	if($sub['sub_descripcion'] != '') { echo $sub['sub_descripcion']; }
	elseif(isset($_SESSION['pres']['descripcion'])) { echo $_SESSION['pres']['descripcion']; }  // Futuro de 20130204
	echo '</textarea></td></tr>'."\n";

	echo '
		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="cliente_id" value="' . $orden['orden_cliente_id'] . '" />
			<input type="hidden" name="vehiculo_id" value="' . $orden['orden_vehiculo_id'] . '" />
			<input type="hidden" name="vehiculo" value="' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . '" />
			<input type="hidden" name="color" value="' . $orden['orden_vehiculo_color'] . '" />
			<input type="hidden" name="placas" value="' . $orden['orden_vehiculo_placas'] . '" />
			<input type="hidden" name="expediente" value="' . $orden['orden_fecha_registro_expediente'] . '" />
		</td></tr>'."\n";
	if ($accion==="complemento") {
		echo '<tr><td colspan="2"><input type="hidden" name="agregar" value="1" /></td></tr>'."\n";
	}
	echo '		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
		</tr>
	</table>
	</form>'."\n";
}

elseif ($accion==='insertar') {
//	echo 'Estamos en la sección inserta.<br>';
	if (validaAcceso('1045005', $dbpfx) == '1' || $_SESSION['rol06']=='1') {
		// Acceso autorizado
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	unset($_SESSION['pres']);
	$_SESSION['pres'] = array();
	$siniestro=preparar_entrada_bd($siniestro);
	$_SESSION['pres']['fsin_aa'] = $fsin_aa;
	$_SESSION['pres']['fsin_mm'] = $fsin_mm;
	$_SESSION['pres']['fsin_dd'] = $fsin_dd;
	$fsiniestro = $fsin_dd . '/' . $fsin_mm . '/' . $fsin_aa;
	$fecha_sin = $fsin_aa . '/' . $fsin_mm . '/' . $fsin_dd;
//	print_r($asegrep);
	if($adicional==1) {
		$asedat = explode("|", $asegrep);
		$reporte = $asedat[0];
		$aseguradora = $asedat[1];
		$poliza = $asedat[2];
	}

	if($orden_garantia != '' && $tipo_servicio == '2') {
		$reporte = $orden_garantia;
	}
//	echo $asedat[0] . ' -> ' . $asedat[1] . '<br>';


//	echo 'Adicional ' . $adicional . ' Variable: ' . $asegrep . ' ' . ' Reporte: ' . $reporte . ' Aseguradora: ' . $aseguradora; print_r($asedat);
	$reporte=preparar_entrada_bd($reporte); 
	$poliza=preparar_entrada_bd($poliza); $_SESSION['pres']['poliza'] = $poliza;
	$aseguradora=preparar_entrada_bd($aseguradora); $_SESSION['pres']['aseguradora'] = $aseguradora;
	$descripcion=preparar_entrada_bd($descripcion); $_SESSION['pres']['descripcion'] = $descripcion;
	$_SESSION['pres']['pagadedu'] = $pagadedu;
	$_SESSION['pres']['nomajus'] = $nomajus;
	$_SESSION['pres']['idajus'] = $idajus;
	$error = 'no';
	$mensaje= '';
//	echo $reporte . ' -> ' . $aseguradora . ' Siniestro: ' . $siniestro . 'Adicional: ' . $adicional . '<br>';
	
	if($adicional=='1' && ($reporte=='0' || $reporte=='' || $reporte==$lang['Interno'] || $reporte==$lang['Rines'] || $reporte==$lang['Garantía'])) { $siniestro=0; }
	if($reporte == '') { $reporte = '0'; }
//	echo '<br><br>====== ' . $error . ' <<>> ' . $mensaje . ' ======<br><br>';

	if ($aseguradora == 'Seleccione...') {$error = 'si'; $mensaje .='Debe seleccionar una aseguradora o particular.<br>';}

  	if ($siniestro==="1" && $aseguradora != '0') {
		if ($reporte == '') {$error = 'si'; $mensaje .= $lang['NumSinVacio'] . '<br>';}
		if ($poliza == '') {$error = 'si'; $mensaje .='El número de póliza no puede estar vacío.<br>';}
		if ($pagadedu < '1' && $adicional != '1') {$error = 'si'; $mensaje .='Debe indicar si o no paga deducible.<br>';}
/*		if ($fsin_aa == '' || strlen($fsin_aa) != 4) {$error = 'si'; $mensaje .='Debe indicar el año a 4 dígitos.<br>';}
		if ($fsin_mm == '' || strlen($fsin_mm) != 2) {$error = 'si'; $mensaje .='Debe indicar el mes a 2 dígitos.<br>';}
		if ($fsin_dd == '' || strlen($fsin_dd) != 2) {$error = 'si'; $mensaje .='Debe indicar el día a 2 dígitos.<br>';}
*/		
		if($ajustadores == '1' && $adicional != '1' && $tipo_servicio != '2') {
			if ($nomajus == '') {$error = 'si'; $mensaje .='El nombre del Ajustador no puede estar vacío.<br>';}
			if ($idajus == '') {$error = 'si'; $mensaje .='El número del ajustador no puede estar vacío.<br>';}
		}
/*  		if ($agregar != 1 && $adicional != 1 && $aseguradora != 0) {
			$nombre_archivo = 'orden-' . $orden_id . '-orden-de-admision-' . time() . '.jpg';
   		if (move_uploaded_file($_FILES['orden_adm']['tmp_name'], DIR_DOCS . $nombre_archivo)) {
  				$sql_data_array = array('orden_id' => $orden_id,
  					'doc_nombre' => 'Orden de Admisi&oacute;n',
  					'doc_archivo' => $nombre_archivo);
				ejecutar_db($dbpfx . 'documentos', $sql_data_array, $accion);
  	  		} else {
  				$error = 'si'; $mensaje .= "Ocurrió algún error al subir el archivo de Orden de Admisión. No pudo guardarse.<br>";
  			}
		} */
  	}
  	  	
  	$_SESSION['pres']['reporte'] = $reporte;
//  	echo $reporte;
  	
	if ($descripcion == '' && ($paquetes == 'Seleccione' || $paquetes == '')) {$error = 'si'; $mensaje .='La tarea debe tener descripción o se debe seleccionar un paquete de servicio.<br>';}
//	echo $paquetes;
	if ($areas == '' || $areas == 'Seleccione') {$error = 'si'; $mensaje .='Debe seleccionar una Área de Servicio.<br>';}

   if ($error === 'no') {
		// --- Verifica si esta activa la actualiza_fech_ingreso --
		if($actualiza_fech_ingreso == 1) {
			$pregfi = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "'";
			$matrfi = mysql_query($pregfi) or die("ERROR: Fallo selección de Tareas! 265 " . $pregfi);
			$filafi = mysql_num_rows($matrfi);
			if($filafi < 1) {
				unset($sqlfi);
				$sqlfi = ['orden_fecha_recepcion' => date('Y-m-d H:i:s')];
				$parametros = "orden_id ='" . $orden_id . "'";
				ejecutar_db($dbpfx . 'ordenes', $sqlfi, 'actualizar', $parametros);
			}
		}
	  	if (isset($paquetes) && $paquetes!='Seleccione') {
	  		$preg3 = "SELECT paq_descripcion, paq_nombre FROM " . $dbpfx . "paquetes WHERE paq_id='" . $paquetes . "'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de paq_prods!");
			$desc = mysql_fetch_array($matr3);
			$preg4 = "SELECT pc_area_id FROM " . $dbpfx . "paq_comp WHERE pc_paq_id='" . $paquetes . "' GROUP BY pc_area_id";
			$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de Componentes de Paquete!");
			while($tarea = mysql_fetch_array($matr4)) {
	      		$sql_data_array = [
					'orden_id' => $orden_id,
   	   				'sub_area' => $tarea['pc_area_id'],
      				'sub_descripcion' => '<b>Paquete de servicio</b> ' . $desc['paq_nombre'] . ': ' . $desc['paq_descripcion'],
					'sub_paquete_id' => $paquetes,
				];
				$sub_orden_id = ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
				$preg0 = "SELECT pc_prod_id, pc_prod_cant FROM " . $dbpfx . "paq_comp WHERE pc_paq_id='" . $paquetes . "'AND pc_area_id = '" . $tarea['pc_area_id'] . "'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de paq_prods!");
				$presupuesto = 0;
				$sub_partes = 0;
				$sub_consumibles = 0;
				$sub_mo = 0;
				$tiempo = 0;
				while($paqs = mysql_fetch_array($matr0)) {
					//echo 'paquete<br>';
					$solicitar_cot = 0;
					$preg1 = "SELECT prod_cant_cotizar, prod_marca, prod_prov_id, prod_costo, prod_codigo, prod_nombre, prod_tangible, prod_precio, prod_cantidad_disponible FROM " . $dbpfx . "productos WHERE prod_id='" . $paqs['pc_prod_id'] . "'";
					//	echo $preg1.'<br>';
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de paq_prods!");
					while($prods = mysql_fetch_array($matr1)) {
						$op_subtotal= $paqs['pc_prod_cant'] * $prods['prod_precio'];
						$presupuesto = $presupuesto + $op_subtotal;
						if($prods['prod_tangible']=='1') {
							$sub_partes = $sub_partes + $op_subtotal;
						} elseif($prods['prod_tangible']=='2') {
							$sub_consumibles = $sub_consumibles + $op_subtotal;
						} else {
							$sub_mo = $sub_mo + $op_subtotal;
							$tiempo = $tiempo + $paqs['pc_prod_cant'];
						}
						$prods_pendientes = 0;
						$sql_data_array = [
							'sub_orden_id' => $sub_orden_id,
							'op_area' => $tarea['pc_area_id'],
							'prod_id' => $paqs['pc_prod_id'],
							'op_nombre' => $prods['prod_nombre'],
							'op_codigo' => $prods['prod_codigo'],
							'op_cantidad' => $paqs['pc_prod_cant'],
							'op_tangible' => $prods['prod_tangible'],
							'op_precio' => $prods['prod_precio'],
							'op_costo' => $prods['prod_costo'],
							'op_subtotal' => $op_subtotal
						];
						$surtidos = '';
						if($paqs['pc_prod_cant'] > $prods['prod_cantidad_disponible'] && $prods['prod_tangible'] > '0') {
							//echo 'No hay existencias para surtir<br>';
							// --- Si la cantidad disponible no es suficiente para cubrir el paquete se debe de registrar que hay pendientes por surtir ---
							// --- Calcular y registrar cuantos pendientes de surtir quedan ---
							if($prods['prod_cantidad_disponible'] == 0){
								$cant_pendiente = $paqs['pc_prod_cant'];	
							} else{
								$cant_pendiente = $paqs['pc_prod_cant'] - $prods['prod_cantidad_disponible'];
							}
							$surtidos = $prods['prod_cantidad_disponible'];
							$sql_data_array['op_recibidos'] = $prods['prod_cantidad_disponible'];
							$parme = " prod_id = '" . $paqs['pc_prod_id'] . "' ";
							$sqdat = ['prod_cantidad_disponible' => '0'];
							ejecutar_db($dbpfx . 'productos', $sqdat, 'actualizar', $parme);
							unset($sqdat);
							//$ajus01 = "ACTUALIZAR " . $dbpfx . "productos SET prod_cantidad_disponible = '0' WHERE prod_id = '" . $paqs['pc_prod_id'] . "'";
							//$reajus01 = mysql_query($ajus01) or die("ERROR: Fallo actualización de productos!");
							if($prods['prod_tangible'] == '1') { $refacciones=1; }
							elseif ($prods['prod_tangible'] == '2') { $consumibles = 1; }
							$solicitar_cot = 1;

						} elseif($paqs['pc_prod_cant'] <= $prods['prod_cantidad_disponible'] && $prods['prod_tangible'] > '0') {
							$surtidos = $paqs['pc_prod_cant'];
							$sql_data_array['op_recibidos'] = $paqs['pc_prod_cant'];
							$sql_data_array['op_ok'] = '1';
							$pregup = "SELECT prod_cantidad_disponible FROM " . $dbpfx . "productos WHERE prod_id = '" . $paqs['pc_prod_id'] . "'";
							$matrup = mysql_query($pregup);
							$up = mysql_fetch_array($matrup);
							$disp = $up['prod_cantidad_disponible'] - $paqs['pc_prod_cant'];
							$parme = " prod_id = '" . $paqs['pc_prod_id'] . "' ";
							$sqdat = ['prod_cantidad_disponible' => $disp];
							ejecutar_db($dbpfx . 'productos', $sqdat, 'actualizar', $parme);
							unset($sqdat);
							//$ajus01 = "ACTUALIZAR " . $dbpfx . "productos SET prod_cantidad_disponible = prod_cantidad_disponible - '" . $paqs['pc_prod_cant'] . "' WHERE prod_id = '" . $paqs['pc_prod_id'] . "'";
							//$reajus01 = mysql_query($ajus01) or die("ERROR: Fallo actualización de productos!");

						}
						$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
						
						unset($sql_data_array);
						$sql_data_array = [
							'prod_id' => $paqs['pc_prod_id'],
							'prods_pendiente_requeridos' => $paqs['pc_prod_cant'],
							'prods_pendiente_surtidos' => $surtidos,
							'prods_pendiente_adeudos' => $cant_pendiente,
							'op_id' => $nueva_id,
							'sub_orden_id' => $sub_orden_id,
							'orden_id' => $orden_id,
							'prods_pendiente_fecha' => date('Y-m-d H:i:s'),
							'prods_pendiente_entregados' => 0,
						];
						ejecutar_db($dbpfx . 'prods_pendientes', $sql_data_array, 'insertar');	

						if($solicitar_cot == 1){
							
							//echo 'se solicita cotizacion, al proveedor ' . $prods['prod_prov_id'] . '<br>';
							// --- Revisar si se dbe de mandar a cotizar a quien-vende.com ---
							if($qv_activo == 1){
								// ------ Si QV está activo, genera el encabezado del XML para agregar cotizaciones
								$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
								$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
								$xml .= '		<Solicitud tiempo="' . microtime() . '">10</Solicitud>'."\n";
								$xml .= '		<OT orden_id="Almacen" marca="n/a" tipo="n/a" color="n/a" vin="n/a" modelo="n/a">'."\n";
								$xml .= '			<Ref op_id="' . $nueva_id . '" op_cantidad="' . $prods['prod_cant_cotizar'] . '" op_nombre="' . $prods['prod_nombre'] . '" op_codigo="' . $prods['prod_codigo'] . '" op_estatus="10" />'."\n";
								$xml .= '		</OT>'."\n";
								$xml .= '	</Comprador>'."\n";
								$mtime = substr(microtime(), (strlen(microtime())-3), 3);
								$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.xml';
								file_put_contents("../qv-salida/".$xmlnom, $xml);

							} elseif($prods['prod_prov_id'] != ''){ // --- Cotizar al proveedor seleccionado ---
								
								//echo 'Se busca al proveedor<br>';
								// --- Consultar si el proveedor tiene habilitadas las notificaciones por email ---
								$preg_proveedor = "SELECT prov_email, prov_razon_social FROM " . $dbpfx . "proveedores WHERE prov_id = '" . $prods['prod_prov_id'] . "' AND prov_env_ped = '1' AND prov_activo = '1'";
								$matr_proveedor = mysql_query($preg_proveedor) or die("ERROR: Fallo selección de proveedor! " . $preg_proveedor);
								$encontrado = mysql_num_rows($matr_proveedor);
								
								if($encontrado == 1){ // --- Enviar correo de cotización y crear cotización ---
									
									//echo 'se va a registrar la cotización';
									unset($sql_data);
									$sql_data = [
										'prod_id' => $paqs['pc_prod_id'], 
										'prod_prov_id' => $prods['prod_prov_id'],
										'prod_costo' => NULL,
										'dias_entrega' => NULL,
										'dias_credito' => NULL,
										'fecha_cotizado' => date('Y-m-d H:i:s', time())
									];
									ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'insertar');
									
									$info_prov = mysql_fetch_assoc($matr_proveedor);
									$para = $info_prov['prov_email'];
									$respondera = $_SESSION['email'];
									$concopia = (constant('EMAIL_PROVEEDOR_CC'));
									$asunto = "Cotización de refacciones para " . $nombre_agencia;
									
									// ------ Construir el contenido -----------------
									$contenido = '<!-- BODY -->
									<table class="body-wrap">
										<tr>
											<td></td>
											<td class="container" bgcolor="#F2F2F2">
												<div class="content">
													<h3>Cotización para ' . $nombre_agencia . '</h3>
													<p class="lead">Estimado proveedor: <br>' . $info_prov['prov_razon_social'] . '
													<br><br>
													' . EMAIL_TEXT_COTIZACION . '</p>
												</div>
											</td>
											<td></td>
										</tr>
									</table>
									<table class="body-wrap">
										<tr>
											<td></td>
											<td class="container" bgcolor="#F2F2F2">
												<div class="content">
													<table border=1 cellspacing=0 bgcolor="#AECCF2" width="100%">
														<tr>
															<th align="center">Cantidad</th>
															<th align="center">Nombre</th>
															<th align="center">Código</th>
															<th align="center">Costo</th>
															<th align="center">Promesa de<br>Entrega</th>
														</tr>
														<tr>
															<td style="text-align: center; ">' . $prods['prod_cant_cotizar'] . '</td>
															<td>' . $prods['prod_nombre'] . ' ' . $prods['prod_marca'] . '</td>
															<td>' . $prods['prod_codigo'] . '</td>
															<td></td>
															<td></td>
														</tr>
													</table>
												</div>
											</td>
										</tr>
									</table>
									<table class="body-wrap" >
										<tr>
											<td></td>
											<td class="container" bgcolor="#F2F2F2">
												<div class="content">
													<h5>Atentamente:</h5>'."\n";
												if($_SESSION['email'] != '') {
													$contenido .= '				<p>' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '<br>'."\n";
												} else {
													$contenido .= '				<p>' . JEFE_DE_ALMACEN . '<br>'."\n";
												}
												$contenido .= '				' .$agencia_razon_social. '<br>
													' .$agencia_direccion. '<br>
													Col. ' .$agencia_colonia. ' ' .$agencia_municipio. '<br>
													C.P.: ' .$agencia_cp. ' . ' .$agencia_estado. '<br>'."\n";
												if($_SESSION['email'] != '') {
													$contenido .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' . $_SESSION['email'] . '">' . $_SESSION['email'] . '</a><br>'."\n";
												} else {
													$contenido .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' .EMAIL_DE_ALMACEN. '">' .EMAIL_DE_ALMACEN. '</a><br>'."\n";
												}
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
								
							}
							
						}
						
					}
				}
				$horas = intval($tiempo);
				$minutos = intval(($tiempo - $horas)*60);
				if($minutos==0) {$minutos='00';}
				$programadas = $horas . ':' . $minutos;
				$parametros='sub_orden_id = ' . $sub_orden_id;
			  	$sql_data_array = [
					'sub_presupuesto' => $presupuesto,
	  				'sub_partes' => $sub_partes, 
	  				'sub_consumibles' => $sub_consumibles, 
			  		'sub_mo' => $sub_mo, 
			  		'sub_valuador' => $_SESSION['usuario'],
	  				'sub_deducible' => $deducible,
	  				'sub_fecha_presupuesto' => date('Y-m-d H:i:s'),
		  			'sub_horas_programadas' => $programadas,
			  		'sub_refacciones_recibidas' => $refacciones,
		  			'sub_estatus' => '102'];
		  		if($aseguradora > 0) {
					$sql_data_array['sub_siniestro'] = $siniestro;
					$sql_data_array['sub_reporte'] = $reporte;
					$sql_data_array['sub_aseguradora'] = $aseguradora;
					$sql_data_array['sub_poliza'] = $poliza;
		  		}
			  	ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		  		unset($_SESSION['pres']['sub_orden_id']);
			}
			
		} elseif ($siniestro == '1') {
//			$parametros='vehiculo_id = ' . $vehiculo_id;
//			$sql_data_array = array('vehiculo_poliza' => $poliza);
//			ejecutar_db($dbpfx . 'vehiculos', $sql_data_array, 'actualizar', $parametros);

//	  		$preg = "SELECT aseguradora_v_email, aseguradora_alta FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '$aseguradora'";
//	  		$matr = mysql_query($preg) or die("ERROR: Fallo selección aseguradoras!");
//	  		$aseg = mysql_fetch_array($matr);
	  		$preg2 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND doc_nombre LIKE '%Orden de Admisi%' ORDER BY doc_id DESC LIMIT 1";
	  		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección documento!");
	  		$doc = mysql_fetch_array($matr2);
	  		if(!file_exists(DIR_DOCS . $doc['doc_archivo'])) { baja_archivo($doc['doc_archivo']); }
//			echo '<br>Aseguradora: ' . $aseguradora;
			$sql_data_array = array('orden_id' => $orden_id,
				'sub_area' => $areas,
				'sub_descripcion' => $descripcion,
				'sub_siniestro' => $siniestro,
				'sub_reporte' => $reporte,
				'sub_aseguradora' => $aseguradora,
				'sub_poliza' => $poliza,
				'sub_nomajus' => $nomajus,
				'sub_idajus' => $idajus,
				'sub_paga_deducible' => $pagadedu,
				'sub_doc_adm' => $doc['doc_archivo']);
		} else {
     		$sql_data_array = array('orden_id' => $orden_id,
     			'sub_area' => $areas,
     			'sub_reporte' => $reporte,
     			'sub_descripcion' => $descripcion);
	  	}

// -------------------------- ASIGNAR ESTATUS A LA SUBORDEN -----------------------------
		$quitar_fecha = '0';

		if($orden_estatus == '1') {
			$sql_data_array['sub_estatus'] = '101';
		} elseif(($orden_estatus >= '4' && $orden_estatus <= '16')  || $orden_estatus == '21') {
			if(($particpres == 1 && $aseguradora < 1) || ($asesaltapres[$aseguradora] != 1 && $aseguradora > 0)) { $sql_data_array['sub_estatus'] = '124'; }
			else { $sql_data_array['sub_estatus'] = '102'; }
			$quitar_fecha = '1';
		} elseif(($orden_estatus >= '24' && $orden_estatus <= '29') || $orden_estatus < '4' || $orden_estatus == '20') {
			if($aseguradora < '1') {
				if($particpres == 1) { $sql_data_array['sub_estatus'] = '124'; }
				else { $sql_data_array['sub_estatus'] = '102'; }
			} elseif($asesaltapres[$aseguradora] == '1') {
				if($valor['ValComoPartic'][0] == '1') { $sql_data_array['sub_estatus'] = '102'; }
				else { $sql_data_array['sub_estatus'] = '129'; }
			} else {
				$sql_data_array['sub_estatus'] = '124';
			}
		} else {
			$_SESSION['msjerror'] = "No se puede crear tarea en el estatus actual de la orden";
			redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id);
		}

		$sub_orden_id = ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
// --------------------------------------------------------------------------------------

// ------ Elimina orden_fecha_acordada, orden_fecha_proceso_fin y orden_fecha_notificacion de la OT en caso de que existan
// ------ ya que con la nueva tarea agregada éstas serán diferentes a las que pudieran existir.

		if($quitar_fecha == '1') {
			$paramfae = " orden_id = '" . $orden_id . "'";
			$sql_ajusta = [
				'orden_fecha_acordada' => 'null',
				'orden_fecha_proceso_fin' => 'null',
				'orden_fecha_notificacion' => 'null'
			];
			
			if($valor['mantenerfpe'][0] != 1 ) {
				$sql_ajusta['orden_fecha_promesa_de_entrega'] = 'null';
				$tambienfpe = 'promesa de entrega, ';
			}
			ejecutar_db($dbpfx . 'ordenes', $sql_ajusta, 'actualizar', $paramfae);
			bitacora($orden_id, 'Reinicio de fechas por nueva tarea en OT en reparación o terminada.', $dbpfx, 'Se eliminaron fechas ' . $tambienfpe . 'acordada de entrega, notificación de termino de reparación y fin de proceso debido a que agregué una nueva tarea estando la OT en reparación o terminada.', '0');
			unset($sql_ajusta);
		}
// -------------------------------------------------------------------------------------

		actualiza_suborden($orden_id, $areas, $dbpfx);
		if($areas == '6' && $orden_estatus == '1') {
			if($paquetes == 'Seleccione') {
				$sql_data_array = [
					'orden_id' => $orden_id,
					'sub_area' => '7',
					'sub_descripcion' => 'Pintura.',
					'sub_siniestro' => $siniestro,
					'sub_reporte' => $reporte,
					'sub_poliza' => $poliza,
					'sub_aseguradora' => $aseguradora,
					'sub_paga_deducible' => $pagadedu,
					'sub_doc_adm' => $doc['doc_archivo']
				];
	  			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
				actualiza_suborden($orden_id, '7', $dbpfx);
			}
			if($igualador==1) {
				$sql_data_array = [
					'orden_id' => $orden_id,
					'sub_area' => '5',
					'sub_descripcion' => 'Registro de igualado de pinturas, selladores y tornillos cuando aplique.',
					'sub_siniestro' => $siniestro,
					'sub_reporte' => $reporte,
					'sub_poliza' => $poliza,
					'sub_aseguradora' => $aseguradora,
					'sub_paga_deducible' => $pagadedu,
					'sub_doc_adm' => $doc['doc_archivo']
				];
                                if($pularmado_avanzadas == 1){
                                        $sql_data_array['sub_estatus'] = '104';
                                }
	  			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
				actualiza_suborden($orden_id, '5', $dbpfx);
			}
			if($mecanica==1) {
				$sql_data_array = [
					'orden_id' => $orden_id,
					'sub_area' => '1',
					'sub_descripcion' => $lang['descrip_mecanica'],
					'sub_siniestro' => $siniestro,
					'sub_reporte' => $reporte,
					'sub_poliza' => $poliza,
					'sub_aseguradora' => $aseguradora,
					'sub_paga_deducible' => $pagadedu,
					'sub_doc_adm' => $doc['doc_archivo']
				];
	  			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
				actualiza_suborden($orden_id, '1', $dbpfx);
			}
			if($pularmado==1) {
				$sql_data_array = [
					'orden_id' => $orden_id,
					'sub_area' => '4',
					'sub_descripcion' => 'Desarmado y armado de partes a reparar.',
					'sub_siniestro' => $siniestro,
					'sub_reporte' => $reporte,
					'sub_poliza' => $poliza,
					'sub_aseguradora' => $aseguradora,
					'sub_paga_deducible' => $pagadedu,
					'sub_doc_adm' => $doc['doc_archivo']
				];
				if($pularmado_avanzadas == 1){
					$sql_data_array['sub_estatus'] = '104';
				}
	  			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
				actualiza_suborden($orden_id, '4', $dbpfx);
				$sql_data_array = [
					'orden_id' => $orden_id,
					'sub_area' => '8',
					'sub_descripcion' => 'Pulido de partes reparadas.',
					'sub_siniestro' => $siniestro,
					'sub_reporte' => $reporte,
					'sub_poliza' => $poliza,
					'sub_aseguradora' => $aseguradora,
					'sub_paga_deducible' => $pagadedu,
					'sub_doc_adm' => $doc['doc_archivo']
				];
				if($pularmado_avanzadas == 1){
					$sql_data_array['sub_estatus'] = '104';
				}
	  			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
				actualiza_suborden($orden_id, '8', $dbpfx);
			}
			if($notiase == '1' && $asenoti[$aseguradora]['alta'] == '1') {
				$asunto = 'Ingreso de vehículo con siniestro ' . $reporte . ' a ' . $nombre_agencia;
				$situacion = 'ha ingresado a nuestro Centro de Reparación <strong>' . $nombre_agencia . '</strong>';
				include_once('parciales/notifica_aseguradora.php');
			}
		}
		actualiza_orden ($orden_id, $dbpfx);
		bitacora($orden_id, 'Se creo Descripción de Daños', $dbpfx);
		unset($_SESSION['pres']);
		redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['pres']['mensaje'] = $mensaje;
		if ($agregar != 1 && $adicional != 1) {
			redirigir('presupuestos.php?accion=crear&orden_id=' . $orden_id);
		} elseif($agregar == 1) {
			redirigir('presupuestos.php?accion=complemento&orden_id=' . $orden_id . '&sub_orden_id=' . $sub_orden_id);
		} else {
			redirigir('presupuestos.php?accion=adicional&orden_id=' . $orden_id);
		}
	}
}

elseif ($accion==='actualizar') {

	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06']=='1'))) {
		// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado');
	}

	$error = 'no'; $mensaje = 'No se actualizó la tarea.<br>';
	$nvadescripcion = preparar_entrada_bd($nvadescripcion);
	$nvoreporte = preparar_entrada_bd($nvoreporte);

//	echo 'Reporte: ' . $nvoreporte . ' Aseguradora: ' . $nvaaseguradora . '<br>';

	if (!isset($nvadescripcion) || $nvadescripcion == '') { $error = 'si'; $mensaje .= $lang['DescVacia'] . '<br>';}
	if($nvaaseguradora == 0) { $nvoreporte = 0; }
	elseif($nvoreporte == '0' || $nvoreporte == '') { $error = 'si'; $mensaje .= $lang['NumSinVacio'] . '<br>'; }

	if ($error === 'no') {
		$mensaje = '';
		if($_FILES['orden_adm']['name'] != '') {
			$resultado = agrega_documento($orden_id, $_FILES['orden_adm'], $lang['nomdocadmin'], $dbpfx);
			if ($resultado['error'] == 'si') {
				$_SESSION['msjerror'] .= "Ocurrió algún error al subir el archivo de " . $lang['nomdocadmin'] . ". No pudo guardarse.<br>";
			} else {
				$nombre_archivo = $resultado['nombre'];
				$comentario .= ' ' . $lang['nomdocadmin'];
			}
		}
		if($_FILES['levante']['name'] != '') {
			$resultado = agrega_documento($orden_id, $_FILES['levante'], $lang['nomdocrep'], $dbpfx);
			if ($resultado['error'] == 'si') {
				$_SESSION['msjerror'] .= "Ocurrió algún error al subir el archivo de " . $lang['nomdocrep'] . ". No pudo guardarse.<br>";
			} else {
				$nombre_levante = $resultado['nombre'];
				$comentario .= ' ' . $lang['nomdocrep'];
			}
		}

		$sql_data_array = [
			'sub_descripcion' => $nvadescripcion,
			'sub_reporte' => $nvoreporte,
		];
		
		if($nvadescripcion != $descripcion) {
			if(isset($comentario)) { $comentario .= ', '; }
			$comentario .= ' descripción de: ' . $descripcion . ' a: ' . $nvadescripcion;
		}
		if($nvoreporte != $reporte) {
			if(isset($comentario)) { $comentario .= ', '; }
			$comentario .= ' reporte de ' . $reporte . ' a ' . $nvoreporte;
		}

		if($estatus == '112') {
			$preg = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub_orden_id . "' AND sub_estatus < '130' AND fact_id IS NULL AND recibo_id IS NULL";
			$matr = mysql_query($preg) or die("ERROR: Fallo selección de tareas! 737 " . $preg);
			$fila = mysql_num_rows($matr);
			if($fila > 0) {
				$sql_data_array['sub_estatus'] = $nvoestatus;
			} else {
				$mensaje .= 'Tarea ' . $sub_orden_id . ' Terminada ya facturada o con recibo de destajo. No se cambió estatus.<br>';
			}
		} else {
			$sql_data_array['sub_estatus'] = $nvoestatus;
		}
		if($nvoestatus != $estatus) {
			if(isset($comentario)) { $comentario .= ', '; }
			$comentario .= ' del estatus ' . constant('TAREA_ESTATUS_' . $estatus) . ' al estatus ' . constant('TAREA_ESTATUS_' . $nvoestatus);
		}


		if($nvooper != $operador) {
			if(isset($comentario)) { $comentario .= ' y '; }
			$comentario .= ' del operador ' . $usr[$operador]['nom'] . ' al operador ' . $usr[$nvooper]['nom'];
		}
		$sql_data_array['sub_operador'] = $nvooper;

		if($flota < 1) {
			$sql_data_array['sub_aseguradora'] = $nvaaseguradora;
			if($nvaaseguradora > '0') {
				if($ConvenioGarantia == $nvaaseguradora) {
					$sql_orden['orden_servicio'] = '2';
					$sql_orden['orden_garantia'] = $nvoreporte;
				} else {
					$sql_orden['orden_servicio'] = '4';
				}
				$sql_data_array['sub_siniestro'] = '1';
			} else {
				$preg0 = "SELECT sub_siniestro FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_orden_id != '" . $sub_orden_id . "' AND sub_siniestro = '1'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de subordenes! ".$preg0);
				$fila0 = mysql_num_rows($matr0);
				if($fila0 > 0) {
					$sql_orden['orden_servicio'] = '4';
				} else {
					$sql_orden['orden_servicio'] = '3';
				}
				$sql_data_array['sub_siniestro'] = '0';
			}
		}

		$parametros='sub_orden_id = ' . $sub_orden_id;
		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		unset($sql_data_array);
		if(count($sql_orden) > 0) {
			$parametros='orden_id = ' . $orden_id;
			ejecutar_db($dbpfx . 'ordenes', $sql_orden, 'actualizar', $parametros);
			unset($sql_orden);
		}
		bitacora($orden_id, 'Cambio de ' . $comentario . 'para la Tarea ' . $sub_orden_id, $dbpfx, 'Cambié ' . $comentario . ' para la Tarea ' . $sub_orden_id . ' de ' . constant('NOMBRE_AREA_' . $area), 0, $sub_orden_id);
		actualiza_orden($orden_id, $dbpfx);
		redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('presupuestos.php?accion=modificar&sub_orden_id=' . $sub_orden_id);
	}
}

elseif ($accion==="consultar") {

	$funnum = 1045015;

	$infomon = validaAcceso('1045070', $dbpfx);  // Valida acceso a mostrar información monetaria.
	$partmon = validaAcceso('1045075', $dbpfx);  // Valida acceso a mostrar información monetaria de tareas particulares.
	$ajuprecpres = validaAcceso('1115100', $dbpfx);  // Valida la activación de modificación normal de presupuestos.

	include("parciales/consulta-tareas.php");

}

elseif ($accion === 'mover') {

	$actsub[$sub_orden_id] = 1; // --- Arreglo para identificar las tareas participantes y actualizar al final su estatus --
	foreach($opm as $key => $val) {
		$rep = explode('|', $val);
		$actsub[$rep[0]] = 1;
		$sql_data_array = [
			'sub_orden_id' => $rep[0],
			'op_tangible' => $optang[$key],
		];
		if($origtang[$key] == '0' && $optang[$key] != '0') {
			$sql_data_array['op_cantidad'] = 1;
			$sql_data_array['op_precio_pres'] = round(($origcant[$key] * $origppres[$key]),2);
			$sql_data_array['op_precio'] = round(($origcant[$key] * $origpauth[$key]),2);
		} elseif($origtang[$key] > '0' && $optang[$key] == '0') {
			if($origpauth[$key] > '0') {
				$nvacant = round(($origpauth[$key] / $ut[$rep[1]]),6);
				$sql_data_array['op_cantidad'] = $nvacant;
				$sql_data_array['op_precio'] = $ut[$rep[1]];
				$sql_data_array['op_precio_pres'] = round(($origppres[$key] / $nvacant),2);
			} elseif($origppres[$key] > '0') {
				$nvacant = round(($origppres[$key] / $ut[$rep[1]]),6);
				$sql_data_array['op_cantidad'] = $nvacant;
				$sql_data_array['op_precio'] = 0;
				$sql_data_array['op_precio_pres'] = $ut[$rep[1]];;
			}
		}
		$parametro = " op_id='" . $key . "' ";
		ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $parametro);
		bitacora($orden_id, 'Se movió OP ' . $key . ' de la tarea ' . $sub_orden_id . ' a la ' . $val . ' y al tipo ' . $optang[$key], $dbpfx);
	}
	// --- Actualizar tareas ---
	foreach($actsub as $key => $val) {
		ajustaTarea ($key, $dbpfx);
	}
//	$_SESSION['msjerror'] = 'Items movidos exítosamente!!';
	redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id . '#' . $sub_orden_id);
}

elseif ($accion==='modificar') {

	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol05'] == '1' || $_SESSION['rol06'] == '1' || $_SESSION['rol07'] == '1'))) {
		// Acceso autorizado
	} else {
		 redirigir('usuarios.php?mensaje=Su usuario no tiene acceso a esta operación.');
	}

	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion! 1150 " . $pregunta);
	$sub_orden = mysql_fetch_array($matriz);
	$pregunta2 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $sub_orden['orden_id'] . "'";
	$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion! 1153" . $pregunta2);
	$orden = mysql_fetch_array($matriz2);

	$veh = datosVehiculo($sub_orden['orden_id'], $dbpfx);
	if($sub_orden['sub_aseguradora'] == '') { $sub_orden['sub_aseguradora'] = 0; }

	echo '	<form action="presupuestos.php?accion=actualizar" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr>
			<td style="text-align:left; vertical-align:top;">
				<table cellpadding="0" cellspacing="0" border="0" class="agrega">
					<tr><td style="text-align:left;">' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . $lang['Placas'] . $orden['orden_vehiculo_placas'] .'</td>';
	echo '					<tr><td colspan="2" style="text-align:left;">Cliente: <img src="' . constant('ASEGURADORA_' . $sub_orden['sub_aseguradora']) . '" alt=""> Reporte: ' . $sub_orden['sub_reporte'] . "\n";
	echo '					<tr class="cabeza_tabla"><td colspan="2">Modificar la descripción de las tareas de esta Tarea:</td></tr>'."\n";
	if($veh['flota'] < 1 && $orden['orden_estatus'] < 30) {
		echo '					<tr><td>Cliente: </td><td style="text-align:left;">
						<select name="nvaaseguradora" size="1">
							<option value="0" ';
		if($sub_orden['sub_aseguradora'] == '0') { echo 'selected ';}
		echo '>Particular</option>'."\n";
			$pregunta3 = "SELECT aseguradora_id, aseguradora_nic FROM " . $dbpfx . "aseguradoras";
			$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
		while($aseguradora = mysql_fetch_array($matriz3)) {
			echo '							<option value="' . $aseguradora['aseguradora_id'] . '" ';
			if($aseguradora['aseguradora_id'] == $sub_orden['sub_aseguradora']) { echo 'selected ';}
			echo '>' . $aseguradora['aseguradora_nic'] . '</option>'."\n";
		}
		echo '						</select>
						</td></tr>'."\n";
	} else {
		echo '					<tr><td colspan="2" style="text-align:left;"><span style="color:red;"><strong>En OTs terminadas o con estatus de Pérdida, no se permite el cambio de aseguradora o convenio</strong></span><input type="hidden" name="nvaaseguradora" value="' . $sub_orden['sub_aseguradora'] . '"></td></tr>'."\n";
	}
	echo '					<tr><td>Número de reporte o siniestro o garantía: </td><td style="text-align:left;"><input type="text" name="nvoreporte" value="' . $sub_orden['sub_reporte'] . '" /></td></tr>'."\n";
	echo '					<tr><td>Descripción de tarea: </td><td style="text-align:left;"><textarea name="nvadescripcion" cols="60" rows="3" >' . $sub_orden['sub_descripcion'] . '</textarea></td></tr>'."\n";

	// --- Cambiar operario en una tarea --
	if(validaAcceso('1070050', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol04'] == '1'))) {
		echo '					<tr><td>Operador asignado a la tarea: </td><td style="text-align:left;">'."\n";
		echo '						<select name="nvooper" size="1">'."\n";
		foreach($usr as $ku => $vu) {
			if($usr[$ku]['rol09'] == 1 && $usr[$ku]['activo'] == 1) {
				echo '							<option value="' . $ku . '"';
				if($ku == $sub_orden['sub_operador']) { echo ' selected '; }
				echo '>' . $vu['nom'] . '</option>'."\n";
			}
		}
		echo '						</select>
					</td></tr>'."\n";
	} else {
		echo '					<tr><td>Operador asignado a la tarea: </td><td style="text-align:left;"><span style="color:red;"><strong>Puede cambiar: Jefe de Taller o Gerente</strong></span><br>' . $usr[$sub_orden['sub_operador']] . '<input type="hidden" name="nvooper" value="' . $sub_orden['sub_operador'] . '"></td></tr>'."\n";
	}

	// --- Cancelar una tarea --
	if(validaAcceso('1045045', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol02'] == '1' || $_SESSION['rol04'] == '1'))) {
		echo '					<tr><td>Cancelar la tarea: </td><td style="text-align:left;">'."\n";
		echo '						<a href="presupuestos.php?accion=cancelar&sub_orden_id=' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/cancelar.png" alt="Cancelar Orden de Trabajo" title="Cancelar Orden de Trabajo"></a>'."\n";
		echo '					</td></tr>'."\n";
	} else {
		echo '					<tr><td>Cancelar la tarea: </td><td style="text-align:left;"><span style="color:red;"><strong>Puede Cancelar: Jefe de Taller o Gerente</strong></span></td></tr>'."\n";
	}

	// ------ Determina quien tiene permiso de cambiar estatus de las tareas ---
	unset($nvoe);
	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && $_SESSION['rol07'] == '1')) {
		$acceso[40] = 1;
	}
	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && $_SESSION['rol06'] == '1')) {
		$acceso[30] = 1;
	}
	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && $_SESSION['rol05'] == '1')) {
		$acceso[20] = 1;
	}
	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && $_SESSION['rol04'] == '1')) {
		$acceso[15] = 1;
	}
	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && $_SESSION['rol02'] == '1')) {
		$acceso[10] = 1;
	}

	// --- Estatus permitidos para cambiar -----
	if($sub_orden['sub_estatus'] == 124) {
		$derecho = 'Asesor de Servicio';
		if($acceso[30] == 1) { $nvoe = [101]; }
	} elseif($sub_orden['sub_estatus'] == 128) {
		$derecho = 'Presupuestador';
		if($acceso[40] == 1) { $nvoe = [124,101]; }
	} elseif($sub_orden['sub_estatus'] == 129) {
		$derecho = 'Valuador';
		if($acceso[20] == 1) { $nvoe = [128,124,101]; }
	} elseif($sub_orden['sub_estatus'] == 120) {
		$derecho = 'Valuador';
		if($acceso[20] == 1) { $nvoe = [129,128,124,101]; }
	} elseif($sub_orden['sub_estatus'] == 102) {
		$derecho = 'Valuador';
		if($acceso[20] == 1) { $nvoe = [120,129,128,124,101]; }
	} elseif($sub_orden['sub_estatus'] >= 104 && $sub_orden['sub_estatus'] <= 108) {
		$derecho = 'Jefe de Taller y Gerente';
		if($acceso[15] == 1 || $acceso[10] == 1) { $nvoe = [104,102,120,129,128,124,101]; }
	} elseif($sub_orden['sub_estatus'] == 111) {
		$derecho = 'Jefe de Taller y Gerente';
		if($acceso[15] == 1 || $acceso[10] == 1) { $nvoe = [104,102,120,129,128,124,101]; }
	} elseif($sub_orden['sub_estatus'] == 121) {
		$derecho = 'Jefe de Taller y Gerente';
		if($acceso[15] == 1 || $acceso[10] == 1) { $nvoe = [111,104,102,120,129,128,124,101]; }
	} elseif($sub_orden['sub_estatus'] == 112) {
		$derecho = 'Gerente';
		if($acceso[10] == 1) { $nvoe = [121,111,104,102,120,129,128,124,101]; }
	} else {
		$derecho = 'No está permitido el cambio directo en este estatus';
	}

	if($orden['orden_estatus'] < '90') {
		echo '					<tr><td>Nuevo Estatus</td><td style="text-align:left;">'."\n";
		if(count($nvoe) >= 1) {
			echo '						<select name="nvoestatus" >'."\n";
			echo '							<option value="' . $sub_orden['sub_estatus'] . '" selected >';
			if(is_array($nvoe)) {
				echo constant('TAREA_ESTATUS_'. $sub_orden['sub_estatus']);
			} else {
				echo $nvoe;
			}
			echo '							</option>'."\n";
			if(is_array($nvoe)) {
				foreach($nvoe as $k) {
					if($k != $sub_orden['sub_estatus']) {
						echo '							<option value="' . $k . '">' . constant('TAREA_ESTATUS_'. $k) . '</option>'."\n";
					}
				}
			}
			echo '						</select>'."\n";
		} elseif($sub_orden['sub_estatus'] >= '130') {
			echo 'Este estatus se puede cambiar desde la acción "Pago Perdida", por favor intente desde esa acción.';
			echo '<input type="hidden" name="nvoestatus" value="' . $sub_orden['sub_estatus'] . '" />';
		} else {
			echo constant('TAREA_ESTATUS_'. $sub_orden['sub_estatus']) . '<br><span style="color:red;">Puede cambiar este estatus: <strong>' . $derecho . '</strong></span>';
			echo '<input type="hidden" name="nvoestatus" value="' . $sub_orden['sub_estatus'] . '" />';
		}
		echo '					</td></tr>'."\n";
	} else {
		echo '					<tr><td><span style="color:red;"><strong>No está permitido el cambio en OTs Cerradas</strong></span>
						<input type="hidden" name="nvoestatus" value="' . $sub_orden['sub_estatus'] . '" />
					</td></tr>'."\n";
	}
// ------------------------------------------------
	echo '					<tr><td colspan="2" style="text-align:left;"><tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr></a></td></tr>'."\n";
	echo '					<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
					<tr><td colspan="2" style="text-align:left;">
						<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
						<input type="hidden" name="orden_id" value="' . $sub_orden['orden_id'] . '" />
						<input type="hidden" name="flota" value="' . $veh['flota'] . '" />
						<input type="hidden" name="aseguradora" value="' . $sub_orden['sub_aseguradora'] . '" />
						<input type="hidden" name="reporte" value="' . $sub_orden['sub_reporte'] . '" />
						<input type="hidden" name="descripcion" value="' . $sub_orden['sub_descripcion'] . '" />
						<input type="hidden" name="operador" value="' . $sub_orden['sub_operador'] . '" />
						<input type="hidden" name="estatus" value="' . $sub_orden['sub_estatus'] . '" />
						<input type="hidden" name="area" value="' . $sub_orden['sub_area'] . '" />
						<a href="ordenes.php?accion=consultar&orden_id=' . $sub_orden['orden_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo" border="1" >
					</td></tr>'."\n";
	echo '				</table></td></tr>'."\n";
	echo '			</table></form>'."\n";
}

elseif ($accion==='valuar') {
	
	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  	$sub_orden = mysql_fetch_array($matriz);
	
	if (validaAcceso('1045025', $dbpfx) == '1' || $_SESSION['rol05']=='1' || (($_SESSION['rol04']=='1' || $_SESSION['rol06']=='1' || $_SESSION['rol07']=='1') && $sub_orden['sub_aseguradora'] == '0')) {
		$msj = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

// ------ Obtenemos en array el tipo de unidad UT o $ para cada área de esta Aseguradora
	$areaut = explode('|', $area_ut[$sub_orden['sub_aseguradora']] );
// ------ Localizamos el archivo con el algoritmo a utilizar para esta aseguradora
	$pregaseg = "SELECT a.algoritmo_autorizados FROM " . $dbpfx . "aseguradoras a, " . $dbpfx . "subordenes s WHERE s.sub_orden_id = '$sub_orden_id' AND s.sub_aseguradora = a.aseguradora_id";
	$matraseg = mysql_query($pregaseg) or die("ERROR: No se localizó la aseguradora de esta tarea! " . $pregaseg);
	$filaaseg = mysql_num_rows($matraseg);
	if($filaaseg == 0) {
		if(is_array($valor['AlgoritmoDefault'])) {
			$algoaseg['algoritmo_autorizados'] = $valor['AlgoritmoDefault'][1];
		} else {
			$algoaseg['algoritmo_autorizados'] = 'parciales/auda-gold.php';
		}
	} else {
		$algoaseg = mysql_fetch_array($matraseg);
	}
	if(file_exists($algoaseg['algoritmo_autorizados'])) {
		include($algoaseg['algoritmo_autorizados']);
	} else {
		$_SESSION['msjerror'] = 'No se localizó el archivo de algoritmo de conversión, favor de reportar a Soporte Técnico. Gracias!';
		redirigir('presupuestos.php?accion=consultar&sub_orden_id=' . $sub_orden_id);
	}
}

elseif ($accion==='cesta') {
	
	$funnum = 1045030;
	
	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  	$sub_orden = mysql_fetch_array($matriz);
  	$pregunta2 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $sub_orden['orden_id'] . "'";
	$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
  	$orden = mysql_fetch_array($matriz2);
	
	echo '	<form action="presupuestos.php?accion=cesta" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td colspan="2" style="text-align:left;">' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . $lang['Placas'] . $orden['orden_vehiculo_placas'] .'</td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Seleccionar del Almacén</td></tr>'."\n";
	if($preaut==1) { echo '<input type="hidden" name="preaut" value="1" />'; }
	echo '		<tr class="obscuro espacio"><td style="text-align:left; width:50%;">Filtrar por Almacén: 
			<select name="almacen" size="1">
				<option value="">Seleccionar...</option>'."\n";
	for($i=1;$i<=$num_almacenes;$i++) {
		echo '				<option value="' . $i . '"';
		if($almacen == $i) { echo ' selected '; }
		echo '>' . $nom_almacen[$i] . '</option>'."\n";
	}											
	echo '			</select>
			<input name="Enviar" value="Enviar" type="submit">';
	echo '		</td><td style="text-align:left;">Buscar por nombre: <input type="text" name="nombre" value="' . $nombre . '" size="15">
			<input name="Enviar" value="Enviar" type="submit"><input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '">
			<input type="hidden" name="cpres" value="' . $cpres . '" />
			</td></tr></table></form>'."\n";
	if((isset($almacen) && $almacen!='') || (isset($nombre) && $nombre!='')) { 
		if(isset($almacen) && $almacen!='') { 
			$preg .= "AND prod_almacen='" . $almacen . "' ";
			if(isset($nombre) && $nombre!='') {
				$nomped = explode(' ', $nombre);
				if(count($nomped) > 0) {
					foreach($nomped as $kc => $vc){
						$preg .= "AND prod_nombre LIKE '%" . $vc . "%' ";
					}
				} 
			}
		}
		elseif(isset($nombre) && $nombre!='') { 
			$nomped = explode(' ', $nombre);
			if(count($nomped) > 0) {
				foreach($nomped as $kc => $vc){
					$preg .= "AND prod_nombre LIKE '%" . $vc . "%' ";
				}
			} 
		}
	}
	
	$preg2 = "SELECT prod_id FROM " . $dbpfx . "productos WHERE prod_activo='1' AND prod_tangible < '3' ";
	$preg2 = $preg2 . $preg;
//	echo $preg2;
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos!");
	$filas = mysql_num_rows($matr2);

	$renglones = 100;
	$paginas = (round(($filas / $renglones) + 0.49999999) - 1);
	if(!isset($pagina)) { $pagina = 0;}
	$inicial = $pagina * $renglones;
//	echo $paginas;
	$preg3 = "SELECT prod_id, prod_codigo, prod_marca, prod_nombre, prod_cantidad_disponible, prod_precio, prod_precioint, prod_tangible, prod_almacen FROM " . $dbpfx . "productos WHERE prod_activo='1' AND prod_tangible < '3' ";
	$preg3 = $preg3 . $preg;
	$preg3 .= "ORDER BY prod_almacen,prod_nombre LIMIT " . $inicial . ", " . $renglones;
	$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de productos!");
	echo '	<form action="presupuestos.php?accion=';
	if($cpres == '1') { echo 'presupuesto'; }
	else { echo 'avaluo'; }
	echo '" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		';
//	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Seleccionar del Almacén</td></tr>'."\n";
	echo '			<tr><td colspan="2"><a href="presupuestos.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=0&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
	if($pagina > 0) {
		$url = $pagina - 1;
		echo '<a href="presupuestos.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $url . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
	}
	if($pagina < $paginas) {
		$url = $pagina + 1;
		echo '<a href="presupuestos.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $url . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
	}
	echo '<a href="presupuestos.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $paginas . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>';
	echo '</td></tr>'."\n";
	echo '			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td style="width:30px;text-align:center;">Almacén</td><td style="width:350px;">Nombre</td><td style="width:0px;">Código</td><td>Marca</td><td style="width:30px;">Disponibles</td><td>Precio Público</td><td>Precio Interno</td><td>Cantidad</td></tr>'."\n";
	$cue = 0;
	while($prods = mysql_fetch_array($matr3)) {
		echo '					<tr><td>' . constant('NOMBRE_ALMACEN_'.$prods['prod_almacen']) . '</td><td>' . $prods['prod_nombre'] . '<input type="hidden" name="prod_nombre[' . $cue . ']" value="' . $prods['prod_nombre'] . '" /></td><td>' . $prods['prod_codigo'] . '<input type="hidden" name="prod_codigo[' . $cue . ']" value="' . $prods['prod_codigo'] . '" /><td>' . $prods['prod_marca'] . '</td><td style="text-align:right;">' . $prods['prod_cantidad_disponible'] . '<input type="hidden" name="prod_disponible[' . $cue . ']" value="' . $prods['prod_cantidad_disponible'] . '" /></td><td style="text-align:right;">' . money_format('%n', $prods['prod_precio']) . '</td><td style="text-align:right;">' . money_format('%n', $prods['prod_precioint']) . '</td><td><input type="text" name="prod_cantidad[' . $cue . ']" size="4" /><input type="hidden" name="prod_id[' . $cue . ']" value="' . $prods['prod_id'] . '" /><input type="hidden" name="prod_precio[' . $cue . ']" value="' . $prods['prod_precio'] . '" /><input type="hidden" name="prod_costo[' . $cue . ']" value="' . $prods['prod_precioint'] . '" /><input type="hidden" name="prod_tangible[' . $cue . ']" value="' . $prods['prod_tangible'] . '" /></td></tr>'."\n";
		$cue++;
	}
	echo '				</table>'."\n";
	echo '			</td>
		</tr>'."\n";
	echo '			<tr><td colspan="2"><a href="presupuestos.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=0&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
	if($pagina > 0) {
		$url = $pagina - 1;
		echo '<a href="presupuestos.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $url . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
	}
	if($pagina < $paginas) {
		$url = $pagina + 1;
		echo '<a href="presupuestos.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $url . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
	}
	echo '<a href="presupuestos.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $paginas . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>';
	echo '</td></tr>'."\n";
	echo '		<tr><td colspan="2"><hr></td></tr>'."\n";
	if($preaut==1) { echo '<input type="hidden" name="preaut" value="1" />'; }
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">
			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="sub_aseguradora" value="' . $sub_orden['sub_aseguradora'] . '" />
			<input type="hidden" name="orden_id" value="' . $sub_orden['orden_id'] . '" />
			<input type="hidden" name="area" value="' . $sub_orden['sub_area'] . '" />
			<input type="hidden" name="cpres" value="' . $cpres . '" />
			<input type="hidden" name="desdecesta" value="1" />
		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>'."\n";
	echo '	</table>
	</form>'."\n";
}

elseif ($accion==="modificar_prod") {
	
	$funnum = 1045035;

// Posible desuso de esta función 20140629
	
	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  	$sub_orden = mysql_fetch_array($matriz);
  	$pregunta2 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $sub_orden['orden_id'] . "'";
	$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
  	$orden = mysql_fetch_array($matriz2);
			
//	echo 'Estamos en la sección valuar';

	echo '	<form action="presupuestos.php?accion=mod_avaluo" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td colspan="2" style="text-align:left;">' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . $lang['Placas'] . $orden['orden_vehiculo_placas'] .'</td></tr>';
	echo '		<tr class="cabeza_tabla"><td colspan="2">Modificar o agregar los productos necesarios para ejecutar esta suborden:</td></tr>
		<tr><td colspan="2" style="text-align:left;">Area: ' . constant('NOMBRE_AREA_' . strtoupper($area)) . '. Descripción de tarea: ' . $sub_orden['sub_descripcion'] . '</td></tr>
		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2" style="text-align:left;">Monto del Deducible:&nbsp;<input type="text" name="deducible" value="' . money_format('%n', $orden['orden_deducible']) . '" /></td></tr>'."\n";
	$area = $sub_orden['sub_area'];
  	$pregunta3 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "' AND op_tangible = '1'";
	$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
	$c=0;
	echo '		<tr><td colspan="2" valign="top" style="text-align:left;">Productos y Materiales</td></tr>
		<tr><td colspan="2" style="text-align:left;">
			<table width="100%" border="0" class="izquierda">
				<tr><td>Nombre</td><td>Referencia</td><td>Cantidad</td><td>Precio Unitario</td><td>Días en que estará<br>disponible</td><td>Quitar</td></tr>';
	while($prods = mysql_fetch_array($matriz3)) {
		echo '<tr>
					<td><input type="text" name="prod_nombre[' . $c . ']" value="' . $prods['op_nombre'] . '" size="30"></td>
					<td><input type="text" name="prod_referencia[' . $c . ']" value="' . $prods['op_referencia'] . '" size="30"></td>
					<td><input type="text" name="prod_cantidad[' . $c . ']" value="' . $prods['op_cantidad'] . '" size="4" style="text-align:center;"></td>
					<td><input type="text" name="prod_precio[' . $c . ']" value="' . $prods['op_precio'] . '" size="12" style="text-align:right;"></td>
					<td><input type="text" name="prod_dd[' . $c . ']" value="' . $prods['op_dias_disponible'] . '" size="12" style="text-align:right;"><input type="hidden" name="prov_id[' . $c . ']" value="' . $prods['op_prov_id'] . '" ></td>
					<td><input type="checkbox" name="prod_quitar[' . $c . ']" style="text-align:left;"></td></tr>
		';
		$c++;
	}
	echo '			</table></td></tr>';
  	$pregunta4 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "' AND op_tangible = '0'";
	$matriz4 = mysql_query($pregunta4) or die("ERROR: Fallo seleccion!");
	$d=0;
		echo '		<tr><td valign="top" colspan="2" valign="top" style="text-align:left;">Mano de Obra</td></tr>
		<tr><td colspan="2" style="text-align:left;">
			<table width="50%" border="0" class="izquierda">
				<tr><td>Descripción del Trabajo</td><td>Cantidad</td><td>Precio Unitario</td><td>Quitar</td></tr>';
		while($obra = mysql_fetch_array($matriz4)) {
			echo '<tr>
					<td><input type="text" name="obra_desc[' . $d . ']" value="' . $obra['op_nombre'] . '" size="30"></td>
					<td><input type="text" name="obra_cantidad[' . $d . ']" value="' . $obra['op_cantidad'] . '" size="4" style="text-align:center;"></td>
					<td><input type="text" name="obra_precio[' . $d . ']" value="' . $obra['op_precio'] . '" size="12" style="text-align:right;"></td>
					<td><input type="checkbox" name="obra_quitar[' . $d . ']" style="text-align:left;"></td></tr>
		';
		$d++;
		}
	echo '			</table></td></tr>';
	echo '		<tr class="cabeza_tabla"><td colspan="2">Nuevos Productos y Materiales</td></tr>';
	echo '		<tr>
			<td>
				<table id="tablaTareas" width="100%" class="izquierda">
					<tr>
						<td width="40%" valign="top">Nombre</td>
						<td>Referencia</td>
						<td>Cantidad</td>
						<td>Precio</td>
						<td>En cuantos días disponible?</td>
						<td>Acciones</td>
					</tr>
				</table>
			</td>
			<td valign="bottom"><input type="button" onClick="agregarProducto()" value="Nuevo Producto" ></td>
		</tr>
		<tr class="cabeza_tabla"><td colspan="2">Mano de Obra Adicional</td></tr>
		<tr>
			<td>
				<table id="tablaObra" width="100%" class="izquierda">
					<tr>
						<td>Descripción del trabajo</td>
						<td>Cantidad</td>
						<td>Precio</td>
						<td>Acciones</td>
					</tr>
				</table>
			</td>
			<td valign="bottom"><input type="button" onClick="agregarObra()" value="Nueva Mano de Obra" ></td>
		</tr>';

echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">
			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="orden_id" value="' . $sub_orden['orden_id'] . '" />
			<input type="hidden" name="area" value="' . $area . '" />
		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
		</tr>
	</table>
	</form>';
}

elseif (($accion==='avaluo') || ($accion==='mod_avaluo')) {
	
	$pregacc = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matracc = mysql_query($pregacc) or die("ERROR: Fallo seleccion!");
  	$subacc = mysql_fetch_array($matracc);
	
	if (validaAcceso('1045040', $dbpfx) == '1' || $_SESSION['rol05']=='1' || (($_SESSION['rol04']=='1' || $_SESSION['rol06']=='1' || $_SESSION['rol07']=='1') && $subacc['sub_aseguradora'] == '0')) {
		$msj = 'Acceso autorizado';
	} else {
		$_SESSION['msjerror'] = $lang['Acceso No Autorizado'];
		redirigir('presupuestos.php?accion=consultar&sub_orden_id=' . $sub_orden_id);
	}

// ------ Obtenemos en array el tipo de unidad UT o $ para cada área de esta Aseguradora
	$areaut = explode('|', $area_ut[$subacc['sub_aseguradora']] );
// ------ Localizamos el archivo con el algoritmo a utilizar para esta aseguradora
	$pregaseg = "SELECT a.algoritmo_autorizados FROM " . $dbpfx . "aseguradoras a, " . $dbpfx . "subordenes s WHERE s.sub_orden_id = '$sub_orden_id' AND s.sub_aseguradora = a.aseguradora_id";
	$matraseg = mysql_query($pregaseg) or die("ERROR: No se localizó la aseguradora de esta tarea! " . $pregaseg);
	$filaaseg = mysql_num_rows($matraseg);
	if($filaaseg == 0) {
		if(is_array($valor['AlgoritmoDefault'])) {
			$algoaseg['algoritmo_autorizados'] = $valor['AlgoritmoDefault'][1];
		} else {
			$algoaseg['algoritmo_autorizados'] = 'parciales/auda-gold.php';
		}
	} else {
		$algoaseg = mysql_fetch_array($matraseg);
	}
	if(file_exists($algoaseg['algoritmo_autorizados'])) {
		include($algoaseg['algoritmo_autorizados']);
	} else {
		$_SESSION['msjerror'] = 'No se localizó el archivo de algoritmo de conversión, favor de reportar a Soporte Técnico. Gracias!';
		redirigir('presupuestos.php?accion=consultar&sub_orden_id=' . $sub_orden_id);
	}
}

elseif ($accion==='cancelar') {
	
	$funnum = 1045045;
	
	$error = 0;
	$preg1 = "SELECT op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '$sub_orden_id' AND op_pedido > '0' AND op_tangible < '3'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de refacciones en pedido!");
	$fila1 = mysql_num_rows($matr1);
	$pedidos = '';
	while($peds = mysql_fetch_array($matr1)) {
		$pedidos .= $peds['op_pedido'] . ' ';
	}
	if($fila1 > 0) {
		$error = 1;
		echo '				<p class="alerta">La Tarea tiene los pedidos ' . $pedidos . ', mismos que debe cancelar para poder eliminar esta Tarea.</p>'."\n";
	}

	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección!");
  	$sub_orden = mysql_fetch_array($matriz);

	if($sub_orden['recibo_id'] > 0) {
		$error = 1;
		echo '				<p class="alerta">La Tarea tiene Recibos de Destajo, no se puede eliminar esta Tarea.</p>'."\n";
	}
	if($sub_orden['fact_id'] > 0) {
		$error = 1;
		echo '				<p class="alerta">La Tarea fue Facturada, no se puede eliminar esta Tarea.</p>'."\n";
	}

	if($error == '0') {
	echo '		 <form action="presupuestos.php?accion=confcancelar" method="post" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" border="0" class="agrega">
				<tr class="cabeza_tabla"><td colspan="2">Confirmar o Rechazar la cancelación de la Sub Orden de Trabajo:</td></tr>
				<tr><td colspan="2" style="text-align:left;"><strong>Área: ' . $sub_orden['sub_area'] . '. Descripción de la Tarea: ' . $sub_orden['sub_descripcion'] . '</strong></td></tr>
				<tr><td><input type="submit" name="confirmar" value="Confirmar" /><label>Confirmar Cancelación</label></td><td><input type="submit" name="rechazar" value="Rechazar" /><label>Rechazar Cancelación</label></td></tr>
			</table>
			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="orden_id" value="' . $sub_orden['orden_id'] . '" />
			<input type="hidden" name="sub_area" value="' . $sub_orden['sub_area'] . '" />
			<input type="hidden" name="sub_aseguradora" value="' . $sub_orden['sub_aseguradora'] . '" />
			<input type="hidden" name="sub_presupuesto" value="' . $sub_orden['sub_presupuesto'] . '" />
		</form>';
	} else {
		echo '			<div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $sub_orden['orden_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div>'."\n";
	}
}

elseif ($accion==='confcancelar') {
	
	$funnum = 1045050;
	
	if ($_SESSION['rol06']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	if ($confirmar=="Confirmar") {
		
		// --- Consultar si los productos no pertenecen a un paquete y ya fueron surtidos ----
		$preg_paq = "SELECT prod_id, op_surtidos, op_nombre, op_recibidos FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '$sub_orden_id' AND prod_id > 0";
		$matr_paq = mysql_query($preg_paq) or die("ERROR: Fallo selección de refacciones de paquete de servicio! " . $preg_paq);
		$fila_paq = mysql_num_rows($matr_paq);
	
		//echo 'PRODS EN PAQUETE RECIBIDOS ' . $fila_paq . '<br>';
		
		if($fila_paq > 0) { // --- Regresar los disponibles al almacen ---
			
			while($paq_ser = mysql_fetch_array($matr_paq)) {
				
				$suma = "UPDATE " . $dbpfx . "productos SET prod_cantidad_disponible = prod_cantidad_disponible + " . $paq_ser['op_recibidos'] . " WHERE prod_id = '" . $paq_ser['prod_id'] . "'";
				//echo $suma . '<br>';
				$resultado = mysql_query($suma) or die("ERROR: no se actualizaron los productos!");
				$archivo = '../logs/' . time() . '-base.ase';
				$myfile = file_put_contents($archivo, $suma . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				
				// ---- Consultar si hay pendientes para cancelarlos -----
				$preg_pendientes = "SELECT * FROM " . $dbpfx . "prods_pendientes WHERE prod_id = '" . $paq_ser['prod_id'] . "' AND sub_orden_id = '" . $sub_orden_id . "'";
				$matr_pendientes = mysql_query($preg_pendientes) or die("ERROR: Fallo selección de prods_pendientes! " . $preg_pendientes);
				$num_pendientes = mysql_num_rows($matr_pendientes);
				
				if($num_pendientes > 0){ // --- Cancelar los pendientes ---
					while($pendientes = mysql_fetch_array($matr_pendientes)){
						$parme = " prods_pendiente_id = '" . $pendientes['prods_pendiente_id'] . "' ";
						ejecutar_db($dbpfx . 'prods_pendientes', $sqdat, 'eliminar', $parme);	
					}
				}
				
			}
		
		}
		
		$parme = " sub_orden_id = '" . $sub_orden_id . "' ";
		ejecutar_db($dbpfx . 'orden_productos', '', 'eliminar', $parme);

//		$preg1 = "ELIMINAR FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "'";
//		$resultado = mysql_query($preg1);
		
		$parametros='sub_orden_id = ' . $sub_orden_id;
		$sql_data_array = array('sub_estatus' => '198',
			'sub_refacciones_recibidas' => '0');
		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		actualiza_suborden ($orden_id, $sub_area, $dbpfx);

		$preg2 = "SELECT orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo determinación de OT!");
	  	$ord = mysql_fetch_array($matr2);

  		if($noticantarea == 1 && $sub_aseguradora < 1) {
			$preg4 = "SELECT usuario FROM " . $dbpfx . "usuarios WHERE activo = 1 AND acceso = 0 AND rol02 = 1";
			$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de gerentes! " . $preg4);
			while($ger = mysql_fetch_array($matr4)) {
		  		bitacora($ord['orden_id'], 'Tarea ' . $sub_orden_id . ' Cancelada y sus refacciones eliminadas.', $dbpfx, 'Cancelé la Tarea de ' . constant('NOMBRE_AREA_' . $sub_area) . ' ' . $sub_orden_id . ' ' . constant('ASEGURADORA_NIC_' . $sub_aseguradora) . ' por $' . number_format($sub_presupuesto,2), 3, $sub_orden_id, $p2, $ger['usuario']);
			}
	  	} else {
	  		bitacora($ord['orden_id'], 'Tarea ' . $sub_orden_id . ' Cancelada y sus refacciones eliminadas.', $dbpfx, 'Cancelé la Tarea de ' . constant('NOMBRE_AREA_' . $sub_area) . ' ' . $sub_orden_id . ' ' . constant('ASEGURADORA_NIC_' . $sub_aseguradora) . ' por $' . number_format($sub_presupuesto,2), 0, $sub_orden_id);
	  	}
	  	
		$preg3 = "SELECT sub_estatus FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '130'";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de subordenes activas!");
	  	$fila3 = mysql_num_rows($matr3);
	  	
	  	if($fila3 > 0) {
			actualiza_orden ($orden_id, $dbpfx);
		} else {
			$parametros='orden_id = ' . $ord['orden_id'];
			$sql_can = array();
			for($i=1; $i <= $num_areas_servicio; $i++) {
				$sql_can['orden_estatus_'.$i] = 'NULL';
			}
			$sql_can['orden_estatus'] = '1';
			$sql_can['orden_ref_pendientes'] = '0';
			$sql_can['orden_fecha_ultimo_movimiento'] = date('Y-m-d H:i:s');
			$sql_can['orden_alerta'] = '0';
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
			bitacora($ord['orden_id'], 'OT sin Tareas, se regresa a Captura de Daños(1).', $dbpfx, 'Todas las Tareas Canceladas, se regresa a Captura de Daños.', 0);
		}
	} 
	redirigir('presupuestos.php?accion=consultar&orden_id=' . $_SESSION['orden_id']);
}

elseif ($accion==='enviar') {
	
	$funnum = 1045055;
	
//	echo 'Estamos en la sección enviar';

	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  	$sub_orden = mysql_fetch_array($matriz);
  	$pregunta2 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $sub_orden['orden_id'] . "'";
	$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
  	$orden = mysql_fetch_array($matriz2);

//	echo $preg;
	echo '	<form action="presupuestos.php?accion=envio" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">';
	echo '		<tr><td style="text-align:left;" colspan="2">' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . $lang['Placas'] . $orden['orden_vehiculo_placas'] . '</td></tr>
		<tr class="cabeza_tabla"><td colspan="2">Enviar Presupuesto a Aseguradora: <img src="' . constant('ASEGURADORA_'.$sub_orden['sub_aseguradora']) . '" alt=""></td></tr>';
	echo '		<tr><td>Agregar archivo PDF del presupuesto enviado a Aseguradora: </td><td style="text-align:left;"><input type="file" name="pres_auto" size="30" /></td></tr>'."\n";
	if($presolnop != '1') {
		echo '		<tr><td>¿Desea enviar más tarde el archivo de Presupuesto Solicitado?</td><td style="text-align:left;"><input type="checkbox" name="opcion" value="1" /></td></tr>'."\n";
	}
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">'."\n";
		
	if($preaut === '1') { echo '<input type="hidden" name="preaut" value="1" />'."\n"; }
	echo '			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="orden_id" value="' . $sub_orden['orden_id'] . '" />
			<input type="hidden" name="aseguradora_id" value="' . $sub_orden['sub_aseguradora'] . '" />
			<input type="hidden" name="reporte" value="' . $sub_orden['sub_reporte'] . '" />
			<input type="hidden" name="aseguradora" value="' . $sub_orden['sub_aseguradora'] . '" />
			<input type="hidden" name="area" value="' . $sub_orden['sub_area'] . '" />
			<input type="hidden" name="sub_estatus" value="' . $sub_orden['sub_estatus'] . '" />
		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>'."\n";
	echo '	</table>
	</form>'."\n";
}

elseif ($accion==='envio') {
	
	$funnum = 1045060;
	
	$retorno = validaAcceso('1045060', $dbpfx);
	
	if ($retorno == '1' || $_SESSION['rol05']=='1') {
		$msj = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Valuadores, ingresar Usuario y Clave correcta');
	}
	$error = 'no';
	$mensaje = '';
  	$doc_nombre = 'Presupuesto Solicitado Reporte: ' . $reporte;
  	$nombre_archivo = basename($_FILES['pres_auto']['name']);
  	$nombre_archivo = limpiarstring($nombre_archivo);
  	$nombre_archivo = $orden_id . '-' . time() . '-' . $nombre_archivo;
//  	echo $opcion;
	if($bloqueaprecio == '1' || isset($opcion)) {
		$msg='Dispensar subida de archivo de presupuesto';
	} else {
		if (move_uploaded_file($_FILES['pres_auto']['tmp_name'], DIR_DOCS . $nombre_archivo)) {
			$sql_data_array = array('orden_id' => $orden_id,
				'doc_nombre' => $doc_nombre,
				'doc_clasificado' => '1',
				'doc_archivo' => $nombre_archivo);
			ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
			sube_archivo($nombre_archivo);
		} else {
			$mensaje = "No seleccionó el archivo a subir o no pudo guardarse.<br>";
			$error = 'si';
		}
	}

	if ($error === 'no') {
	  	$parametros = "sub_reporte = '" . $reporte . "' AND sub_aseguradora = '$aseguradora' AND (sub_estatus = '120' OR sub_estatus < '103' OR sub_estatus > '121') AND sub_estatus < '190' AND orden_id = '$orden_id'";
	  	unset($sql_data_array);
		$sql_data_array['sub_estatus'] = '120'; 
		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, 'Valuación solicitada registrada como enviada', $dbpfx);

		actualiza_orden($orden_id, $dbpfx);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('presupuestos.php?accion=enviar&sub_orden_id=' . $sub_orden_id);
	}
}

elseif ($accion==='presupuestar') {

	if (validaAcceso('1045065', $dbpfx) == '1' || $_SESSION['rol07']=='1') {
		$msj = 'Acceso autorizado';
	} else {
		$_SESSION['msjerror'] = $lang['Acceso No Autorizado'];
		redirigir('presupuestos.php?accion=consultar&sub_orden_id=' . $sub_orden_id);
	}

// ------ Localizamos el archivo con el algoritmo a utilizar para esta aseguradora
	$pregaseg = "SELECT a.algoritmo_pres FROM " . $dbpfx . "aseguradoras a, " . $dbpfx . "subordenes s WHERE s.sub_orden_id = '$sub_orden_id' AND s.sub_aseguradora = a.aseguradora_id";
	$matraseg = mysql_query($pregaseg) or die("ERROR: No se localizó la aseguradora de esta tarea! " . $pregaseg);
	$filaaseg = mysql_num_rows($matraseg);
	if($filaaseg == 0) {
		if(is_array($valor['AlgoritmoDefault'])) {
			$algoaseg['algoritmo_pres'] = $valor['AlgoritmoDefault'][1];
		} else {
			$algoaseg['algoritmo_pres'] = 'parciales/aseref.php';
		}
	} else {
		$algoaseg = mysql_fetch_array($matraseg);
	}
	if(file_exists($algoaseg['algoritmo_pres'])) {
		include($algoaseg['algoritmo_pres']);
	} else {
		$_SESSION['msjerror'] = 'No se localizó el archivo de algoritmo de conversión, favor de reportar a Soporte Técnico. Gracias!';
		redirigir('presupuestos.php?accion=consultar&sub_orden_id=' . $sub_orden_id);
	}
}

elseif ($accion==='presupuesto') {

	if (validaAcceso('1045065', $dbpfx) == '1' || $_SESSION['rol07']=='1') {
		$msj = 'Acceso autorizado';
	} else {
		$_SESSION['msjerror'] = $lang['Acceso No Autorizado'];
		redirigir('presupuestos.php?accion=consultar&sub_orden_id=' . $sub_orden_id);
	}

// ------ Localizamos el archivo con el algoritmo a utilizar para esta aseguradora
	$pregaseg = "SELECT a.algoritmo_pres FROM " . $dbpfx . "aseguradoras a, " . $dbpfx . "subordenes s WHERE s.sub_orden_id = '$sub_orden_id' AND s.sub_aseguradora = a.aseguradora_id";
	$matraseg = mysql_query($pregaseg) or die("ERROR: No se localizó la aseguradora de esta tarea! " . $pregaseg);
	$filaaseg = mysql_num_rows($matraseg);
	if($filaaseg == 0) {
		if(is_array($valor['AlgoritmoDefault'])) {
			$algoaseg['algoritmo_pres'] = $valor['AlgoritmoDefault'][1];
		} else {
			$algoaseg['algoritmo_pres'] = 'parciales/auda-gold.php';
		}
	} else {
		$algoaseg = mysql_fetch_array($matraseg);
	}
	if(file_exists($algoaseg['algoritmo_pres'])) {
		include($algoaseg['algoritmo_pres']);
	} else {
		$_SESSION['msjerror'] = 'No se localizó el archivo de algoritmo de conversión, favor de reportar a Soporte Técnico. Gracias!';
		redirigir('presupuestos.php?accion=consultar&sub_orden_id=' . $sub_orden_id);
	}
}

elseif ($accion==="imprimepres") {

	if (validaAcceso('1045070', $dbpfx) == '1' || $_SESSION['rol04']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol06']=='1') {
		 $mensaje = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Cotizador, ingresar Usuario y Clave correcta');
	}
//	echo 'Estamos en la sección imprimir';
	$mensaje = '';
	$num_cols = 0;

	if($sin == '' || !isset($sin) || $sin == 'Seleccione') {
		include('parciales/encabezado.php'); 
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
		if(isset($sub_orden_id) && $sub_orden_id != '') {
			$pregunta = "SELECT sub_reporte, orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id' AND sub_estatus < 130 GROUP BY sub_reporte";
		} else {
			$pregunta = "SELECT sub_reporte, orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < 130 GROUP BY sub_reporte";
		}
//		echo $pregunta;
			$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección agrupada!");
  		$filas = mysql_num_rows($matriz);
		echo '		<form action="presupuestos.php?accion=imprimepres" method="post" enctype="multipart/form-data">'."\n";
		echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
		if ($filas > 1) {
		  	echo '			<tr><td style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">Existe más de un siniestro o trabajo particular.</td></tr>' . "\n";
	  		echo '			<tr><td><select name="sin" size="1">' . "\n";
			echo '				<option value="Seleccione" >Seleccione...</option>';
		  	while($rep = mysql_fetch_array($matriz)) {
		  		if($rep['sub_reporte'] == '' || $rep['sub_reporte'] == '0') {
		  			echo '				<option value="0">Particular</option>' . "\n"; 
		  		} else {
		  			echo '				<option value="' . $rep['sub_reporte'] . '">' . $rep['sub_reporte'] . '</option>' . "\n";
		  		}
		  		$orden_id = $rep['orden_id'];
			}
			echo '			</select></td></tr>' . "\n";
		} elseif ($filas == 1) {
			$rep = mysql_fetch_array($matriz);
			$orden_id = $rep['orden_id'];
			echo '			<tr><td>Se imprimirá el presupuesto para ';
			if($rep['sub_reporte'] == '' || $rep['sub_reporte'] == '0') {
				echo 'el Trabajo Particular.';
			} else {
				echo 'el siniestro ' . $rep['sub_reporte'] . '.';
			}
			echo '<input type="hidden" name="sin" value="' . $rep['sub_reporte'] . '" />
				  <input type="hidden" name="area" value="' . $area . '" />
				  <input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
				  </td></tr>'."\n";
		} else {
			echo '			<tr><td>No se encontraron datos.</td></tr>'."\n";
		}
		echo '			<tr><td colspan="2" style="text-align:left;"><input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="submit" name="confirmar" value="Enviar" />&nbsp;<input type="submit" name="regresar" value="Regresar" /></td></tr>
		</table></form>'."\n";
  	} else {
		$preg0 = "SELECT sub_aseguradora, sub_poliza FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '$sin' LIMIT 1";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de grupo de subordenes!");
		$rep = mysql_fetch_array($matr0);
		$reporte = $sin;
		$poliza = $rep['sub_poliza'];
		$aseguradora = $rep['sub_aseguradora'];

		$pregu = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE activo = '1'";
		$matru = mysql_query($pregu) or die("ERROR: Fallo selección de usuarios!");
		while ($usu = mysql_fetch_array($matru)) {
			$usr[$usu['usuario']]['nombre'] = $usu['nombre'] . ' ' . $usu['apellidos'];
		}

		$preg = "SELECT s.sub_area, s.sub_orden_id, o.op_id FROM " . $dbpfx . "subordenes s, " . $dbpfx . "orden_productos o WHERE s.orden_id = '$orden_id' AND s.sub_estatus < '130' AND s.sub_orden_id = o.sub_orden_id AND o.op_pres = '1' ";
//		if($aseguradora > '0') { $preg .= "o.op_pres = '1'"; } else { $preg .= "o.op_pres IS NULL"; }
		$preg .= " AND s.sub_reporte = '" . $reporte . "'";
		$preg .= " GROUP BY s.sub_area ORDER BY s.sub_area ";
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de grupo de subordenes!");
		$num_cols = mysql_num_rows($matr);
		$error = 'no';

		if ($num_cols > 0) {
			if($envio != '1') {
				include('parciales/encabezado.php'); 
				echo '	<div id="body">'."\n";
				include('parciales/menu_inicio.php');
				echo '		<div id="principal">'."\n";
			}
			if (file_exists('particular/hoja_pres.php')) {
			// ------ El fichero hoja_pres existe en particular
				include('particular/hoja_pres.php');
			} else {
			// ------ El fichero hoja_pres no existe en particular
				include('parciales/hoja_pres.php');
			}
		} else {
			$_SESSION['msjerror'] = 'No se ha creado Presupuesto para el siniestro ' . $reporte . ' de la OT ' . $orden_id;
			redirigir('presupuestos.php?accion=consultar&orden_id='.$orden_id);
		}
	}
	//echo $sub_orden_id . '<br>' . $area . '<br>' . $sin;
}

elseif ($accion==="imprimeaut") {
		
	if (validaAcceso('1045070', $dbpfx) == '1' || $_SESSION['rol04']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol06']=='1') {
		 $mensaje = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Cotizador, ingresar Usuario y Clave correcta');
	}
//	echo 'Estamos en la sección imprimir';
	$mensaje = '';
	$num_cols = 0;

	if($sin == '' || !isset($sin) || $sin == 'Seleccione') {
		include('parciales/encabezado.php'); 
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
		if(isset($sub_orden_id) && $sub_orden_id != '') {
			$pregunta = "SELECT sub_reporte, orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id' AND sub_estatus < 130 GROUP BY sub_reporte";
		} else {
			$pregunta = "SELECT sub_reporte, orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < 130 GROUP BY sub_reporte";
		}
//		echo $pregunta;
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección agrupada!");
  		$filas = mysql_num_rows($matriz);
		echo '		<form action="presupuestos.php?accion=imprimeaut" method="post" enctype="multipart/form-data">'."\n";
		echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
		if ($filas > 1) {
		  	echo '			<tr><td style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">Existe más de un siniestro o trabajo particular.</td></tr>' . "\n";
	  		echo '			<tr><td><select name="sin" size="1">' . "\n";
			echo '				<option value="Seleccione" >Seleccione...</option>';
		  	while($rep = mysql_fetch_array($matriz)) {
		  		if($rep['sub_reporte'] == '' || $rep['sub_reporte'] == '0') {
		  			echo '				<option value="0">Particular</option>' . "\n"; 
		  		} else {
		  			echo '				<option value="' . $rep['sub_reporte'] . '">' . $rep['sub_reporte'] . '</option>' . "\n";
		  		}
		  		$orden_id = $rep['orden_id'];
			}
			echo '			</select></td></tr>' . "\n";
		} elseif ($filas == 1) {
			$rep = mysql_fetch_array($matriz);
			$orden_id = $rep['orden_id'];
			echo '			<tr><td>Se imprimirá el presupuesto para ';
			if($rep['sub_reporte'] == '' || $rep['sub_reporte'] == '0') {
				echo 'el Trabajo Particular.';
			} else {
				echo 'el siniestro ' . $rep['sub_reporte'] . '.';
			}
			echo '<input type="hidden" name="sin" value="' . $rep['sub_reporte'] . '" /></td></tr>'."\n";
		} else {
			echo '			<tr><td>No se encontraron datos.</td></tr>'."\n";
		}
		echo '			<tr><td colspan="2" style="text-align:left;"><input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="submit" name="confirmar" value="Enviar" />&nbsp;<input type="submit" name="regresar" value="Regresar" /></td></tr>
		</table></form>'."\n";
  	} else {
		$preg0 = "SELECT sub_aseguradora, sub_poliza FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '$sin' LIMIT 1";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de grupo de subordenes!");
		$rep = mysql_fetch_array($matr0);
		$reporte = $sin;
		$poliza = $rep['sub_poliza'];
		$aseguradora = $rep['sub_aseguradora'];
		
		$pregu = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE activo = '1'";
		$matru = mysql_query($pregu) or die("ERROR: Fallo selección de usuarios!");
		while ($usu = mysql_fetch_array($matru)) {
			$usr[$usu['usuario']]['nombre'] = $usu['nombre'] . ' ' . $usu['apellidos'];
		}
		
		$preg = "SELECT s.sub_area, s.sub_orden_id, o.op_id FROM " . $dbpfx . "subordenes s, " . $dbpfx . "orden_productos o WHERE s.orden_id = '$orden_id' AND s.sub_estatus < '130' AND s.sub_orden_id = o.sub_orden_id AND o.op_pres IS NULL ";
//		if($aseguradora > '0') { $preg .= "o.op_pres = '1'"; } else { $preg .= "o.op_pres IS NULL"; }
		$preg .= " AND s.sub_reporte = '" . $reporte . "'";
		$preg .= " GROUP BY s.sub_area ORDER BY s.sub_area ";
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de grupo de subordenes!");
		$num_cols = mysql_num_rows($matr);
		$error = 'no';

		if ($num_cols > 0) {
			include('parciales/encabezado.php'); 
			echo '	<div id="body">'."\n";
			include('parciales/menu_inicio.php');
			echo '		<div id="principal">'."\n";
			
			
			

			if (file_exists('particular/hoja_pres.php')) {
				
	 			//echo "El fichero hoja_pres existe en particular";
				include('particular/hoja_pres.php');
				
			} else {
				
	 			//echo "El fichero hoja_pres no existe en particular";
				include('parciales/hoja_pres.php');
				
			}
		} else {
			$_SESSION['msjerror'] = 'No se ha creado Valuación para el siniestro ' . $reporte . ' de la OT ' . $orden_id;
			redirigir('presupuestos.php?accion=consultar&orden_id='.$orden_id);
		}
	}
}

elseif ($accion==='termcotizar') {

	$funnum = '1045080';
	$retorno = validaAcceso($funnum, $dbpfx);

	if ($retorno == '1' || $_SESSION['rol08']=='1') {
		// Acceso autorizado
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	if($siniestro == '') {
		include('parciales/encabezado.php');
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";

		if($sub_orden_id == '') {
			$pregunta = "SELECT sub_reporte, orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus = 128 GROUP BY sub_reporte";
		} else {
			$pregunta = "SELECT sub_reporte, orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub_orden_id . "' AND sub_estatus = 128 GROUP BY sub_reporte";
		}

//		echo $pregunta;
			$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección agrupada!");
  		$filas = mysql_num_rows($matriz);
		echo '		<form action="presupuestos.php?accion=termcotizar" method="post" enctype="multipart/form-data">'."\n";
		echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";

		if ($filas > 1) {
			  	echo '			<tr>
				<td style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">Existe más de un siniestro o trabajo particular.</td></tr>' . "\n";
	  			echo '			<tr>
				<td><select name="siniestro" size="1">' . "\n";
			echo '					<option value="Seleccione" >Seleccione...</option>';
			  	while($rep = mysql_fetch_array($matriz)) {
			  		if($rep['sub_reporte'] == '' || $rep['sub_reporte'] == '0') {
		  				echo '					<option value="0">Particular</option>' . "\n"; 
		  			} else {
		  				echo '					<option value="' . $rep['sub_reporte'] . '">' . $rep['sub_reporte'] . '</option>' . "\n";
			  		}
				$orden_id = $rep['orden_id'];
			}
			echo '					</select>
				</td>
			</tr>' . "\n";
		} elseif ($filas == 1) {

			$rep = mysql_fetch_array($matriz);

			echo ''."\n";
			$orden_id = $rep['orden_id'];
			echo '			<tr>
				<td>Se terminará la cotización para ';
			if($rep['sub_reporte'] == '' || $rep['sub_reporte'] == '0') {
				echo 'el Trabajo Particular.';
				$rep['sub_reporte'] = '0';
			} else {
				echo 'el siniestro ' . $rep['sub_reporte'] . '.';
			}
			echo '<input type="hidden" name="siniestro" value="' . $rep['sub_reporte'] . '" />
				</td>
			</tr>'."\n";
		} else {
			echo '			<tr>
				<td>No se encontraron datos.</td></tr>'."\n";
		}
		echo '			<tr><td colspan="2" style="text-align:left;">
						<input type="hidden" name="orden_id" value="' . $orden_id . '" />
						<input type="submit" name="confirmar" value="Enviar" />&nbsp;<input type="submit" name="regresar" value="Regresar" /></td></tr>
		</table></form>'."\n";

	} else {
		$parametros = "orden_id = '" . $orden_id . "' AND (sub_estatus = '128' OR  sub_estatus = '127' OR  sub_estatus = '124') ";
		if($siniestro == '0') {
			$parametros .= " AND (sub_reporte = '' OR sub_reporte = '0') ";
		} else {
			$parametros .= " AND sub_reporte = '" . $siniestro . "' ";
		}

		$sql_data_array['sub_estatus'] = '129';
		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		actualiza_orden ($orden_id, $dbpfx);
		unset($sql_data_array);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
}

elseif ($accion==='ajuprecpres') {

	if($accmover == 'Mover') {
		if(!isset($p_mover)) {
			$_SESSION['msjerror'] = 'Debes seleccionar mínimo un Item para mover!!';
			redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id . '');
		}
		echo '		<div class="page-content">
				<div class="row"><div class="col-sm-12"><div class="content-box-header"><div class="panel-title">
								<h2>Mover Items de tarea:</h2>
				</div></div></div></div>'."\n";
		echo '			<div class="row"><div class="col-sd-12">
							<form action="presupuestos.php?accion=mover" method="post" enctype="multipart/form-data" name="mueveitems">
							<div class="col-sm-8 padding"><div id="content-tabla">
								<table cellspacing="0" class="table-new izquierda">
									<tr>
										<th><big>Concepto</big></th>
										<th><big>Mover a la Tarea</big></th>
										<th><big>Cambiar tipo de Concepto</big></th>
									</tr>'."\n";
			
		// ---- Consultar Info de la Tareas de la OT ----
		$preg_suborden = "SELECT sub_orden_id, sub_reporte, sub_aseguradora, sub_area, sub_descripcion FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND sub_estatus < '130'";
		$matr_suborden = mysql_query($preg_suborden) or die('Fallo: consulta de Tareas! 862 ' . $preg_suborden);
		if(mysql_num_rows($matr_suborden) > 1) {
			while($tarea = mysql_fetch_assoc($matr_suborden)) {
				$tareas[$tarea['sub_orden_id']] = $tarea['sub_orden_id'] . ' ' . constant('NOMBRE_AREA_' . $tarea['sub_area']) . ' ' . constant('ASEGURADORA_NIC_' . $tarea['sub_aseguradora']) . ' ' . $tarea['sub_reporte'] . ' ' . substr($tarea['sub_descripcion'], 0, 30);
				$aseg_id[$tarea['sub_orden_id']] = $tarea['sub_aseguradora'];
			}
			// ---- Listar los items seleccionados ----
			$fondo = 'obscuro';
			foreach($p_mover as $pk => $pv) {
				// --- Guardar la Tarea de origen --
				$sub_orden_id = $op[$pk]['sub'];
				echo '									<tr class="' . $fondo . '">
										<td>(' . $op[$pk]['sub'] . ') ' . $op[$pk]['cantidad'] . ' ' . $op[$pk]['nombre'] . '</td>
										<td><select name="opm['. $pk .']" >'."\n";
				foreach($tareas as $tk => $tv) {
					echo '											<option value="' . $tk . '|' . $aseg_id[$tk] . '"';
					if($tk == $op[$pk]['sub']) { echo ' selected ';}
					echo '>' . $tv . '</option>'."\n";
				}
				echo '										</select></td>
										<td><select name="optang['. $pk .']" >'."\n";
				for($tang=0; $tang < 3; $tang++) {
					//echo 'Tangible de iterm: ' . $op[$pk]['tangible'] . '<br>';
					echo '											<option value="' . $tang . '"';
					if($tang == $op[$pk]['tangible']) { echo ' selected ';}
					echo '>' . $prod_tipo[$tang] . '</option>'."\n";
//					echo '>' . $valarr['prod_tipo'][$tang] . '</option>'."\n";
				}
				echo '										</select>
											<input type="hidden" name="origtang[' . $pk . ']" value="' . $op[$pk]['tangible'] . '" >
											<input type="hidden" name="origcant[' . $pk . ']" value="' . $op[$pk]['cantidad'] . '" >
											<input type="hidden" name="origppres[' . $pk . ']" value="' . $op[$pk]['precio_pres'] . '" >
											<input type="hidden" name="origpauth[' . $pk . ']" value="' . $op[$pk]['precio'] . '" >
										</td>
									</tr>'."\n";
				if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
			}
			echo '									</table>
								</div>
								<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
								<input type="hidden" name="orden_id" value="' . $orden_id . '" />
								<input type="hidden" name="aseg_id" value="' . $orden_id . '" />
								<input type="submit" class="btn btn-success" value="Mover" />
							</div>
							<div class="col-sm-8 padding">
								<a href="presupuestos.php?accion=consultar&orden_id=' . $orden_id . '#' . $sub_orden_id . '">
									<img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo">
								</a>
							</div>
							</form>
						</div>
					</div>
				</div>'."\n";
		} else{ // --- NO Hay tareas disponibles para mover Items ---
			$_SESSION['msjerror'] = 'No hay tareas disponibles para mover los items, debes crear una nueva tarea para mover los item(s).';
			redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id . '#' . $sub_orden_id);
		}
	} else {
	// ------ Actualizar items de presupuestos y valuaciones ------
		foreach($op as $k => $v) {
			if($borraop[$k] == 1 || $v['precio_pres'] != $ajpprecio[$k] || $v['precio'] != $ajpval[$k]) {
				$param = " op_id = '" . $k . "'";
				unset($sqlajp);
				if($borraop[$k] == 1) {
					$hacer = 'eliminar';
					$combit = 'Eliminé a OP ' . $k . ' ' . $v['cantidad'] . ' ' . $v['nombre'] . ' en la tarea ' . $ajptarea;
					$comentario = 'Eliminé ' . $v['cantidad'] . ' ' . $v['nombre'] . ' con presupuesto ' . number_format($v['precio_pres'],2) . ' y autorizado ' . number_format($v['precio'],2);
				} else {
					$presunit = '0'; $valcant = '0'; $valunit = '0';
					if($v['tangible'] > 0) {
						$valcant = $v['cantidad'];
						$valunit = round((limpiarNumero($ajpval[$k]) / $v['cantidad']),2);
						$presunit = round((limpiarNumero($ajpprecio[$k]) / $v['cantidad']),2);
					} else {
						$valunit = $ut[$v['aseg']];
						$valcant = round((limpiarNumero($ajpval[$k]) / $valunit),6);
						if($valcant > 0) {
							$presunit = round((limpiarNumero($ajpprecio[$k]) / $valcant),6);
						} else {
							$presunit = $ut[$v['aseg']];
							$valcant = round((limpiarNumero($ajpprecio[$k]) / $presunit),6);
							$valunit = '0';
						}
					}
					$sqlajp = [
						'op_cantidad' => $valcant,
						'op_precio_pres' => $presunit,
						'op_subtotal_pres' => $ajpprecio[$k],
						'op_precio' => $valunit,
						'op_subtotal' => $ajpval[$k],
					];
					$hacer = 'actualizar';
					$combit = 'Actualicé a OP ' . $k . ' ' . $v['cantidad'] . ' ' . $v['nombre'] . ' en la tarea ' . $ajptarea;
					$comentario = 'Actualicé ' . $valcant . ' ' . $v['nombre'] . ' presupuesto de ' . number_format($v['precio_pres'],2) . ' a ' . $ajpprecio[$k] . ' y autorizado de ' . number_format($v['precio'],2) . ' a ' . $ajpval[$k];
				}
		//		echo 'Parametro: ' . $param . '<br>';
		//		print_r($sqlajp);
		//		echo '<br>';
				ejecutar_db($dbpfx . 'orden_productos', $sqlajp, $hacer, $param);
				bitacora($orden_id, $combit, $dbpfx, $comentario);
			}
		}
		ajustaTarea ($ajptarea, $dbpfx);
		unset($ajpprecio, $ajpcant, $opsub, $sqlajp, $param);
		redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id . '#' . $ajptarea);
	}
}

elseif ($accion==='comparar') {

	$funnum = 1045055;
	
//	echo 'Estamos en la sección enviar';

	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  	$sub_orden = mysql_fetch_array($matriz);
  	$pregunta2 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $sub_orden['orden_id'] . "'";
	$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
  	$orden = mysql_fetch_array($matriz2);

//	echo $preg;
	echo '	<form action="presupuestos.php?accion=compara" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="izquierda">';
	echo '		<tr><td style="text-align:left;" colspan="2">' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . $lang['Placas'] . $orden['orden_vehiculo_placas'] . '</td></tr>
		<tr class="cabeza_tabla"><td colspan="2" style="font-size:16px; color:red;">Cargar detalle de valuación solicitada</td></tr>'."\n";

	echo '		<tr><td colspan="2">&nbsp;</td></tr>'."\n";

	if($compara == '1') {

		echo '		<tr class="cabeza_tabla"><td colspan="2">Productos, Materiales y Mano de Obra a presupuestar para la Tarea:</td></tr>
		<tr><td colspan="2" style="text-align:left;">Area: ' . constant('NOMBRE_AREA_' . $sub_orden['sub_area']) . '. Descripción de tarea: ' . $sub_orden['sub_descripcion'] . '</td></tr>'."\n";
		if($sub_orden['sub_siniestro']==='1') {
			echo '		<tr><td colspan="2" style="text-align:left;">Aseguradora: <img src="' . constant('ASEGURADORA_' . $sub_orden['sub_aseguradora']) . '" alt=""> Reporte: ' . $sub_orden['sub_reporte'] . '<br><a href="' . DIR_DOCS . $sub_orden['sub_doc_adm'] . '" target="_blank">Orden de Admisión</a></td></tr>'."\n";
		}
		if($sub_orden['sub_area'] <='6' || $sub_orden['sub_area'] >='8') {
			echo '		<tr><td colspan="2" style="text-align:left;">';
			if($sub_orden['sub_siniestro']==='1') {
//			echo 'Copia y Pega desde AUDATEX las <span style="color:#f00; font-weight:bold;">PIEZAS SUSTITUIDAS que tienen precio</span>:';
				echo 'Copia y Pega desde AUDATEX las <span style="color:#f00; font-weight:bold;">PIEZAS SUSTITUIDAS</span><br>O agrega directamente las refacciones, una por renglón: <span style="color:#f00; font-weight:bold;">Descripción, Código (al menos 8 letras o números sin espacio) Precio Público (si no sabe el precio colocar un # en lugar del precio)</span>:';
			} else {
				echo 'Agrega las Refacciones, una por renglón: <span style="color:#f00; font-weight:bold;">Cantidad Descripción Código (al menos 8 letras o números sin espacio) Precio Público (si no sabe el precio colocar un # en lugar del precio)</span>:<input type="hidden" name="particular" value="1" />';
			}
			echo '</td></tr>
		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audasust" cols="70" rows="13" style="background-color:#FFFFB0;" />' . $_SESSION['pres']['audasust'] . '</textarea>
			<img src="imagenes/piezas-sustituidas.jpg" alt="" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><hr></td></tr>'."\n";
		} elseif($sub_orden['sub_area']=='7') {
			if($sub_orden['sub_siniestro']==='1') {
				echo '		<tr><td colspan="2" style="text-align:left;">';
				echo'Copia y Pega desde AUDATEX del <span style="color:#f00; font-weight:bold;">RESUMEN MATERIALES PINTURA</span> o directamente si no lo haces desde AUDATEX lo siguiente:<br>';
				echo '</td></tr>
		<tr><td colspan="2" style="text-align:left;">Precio de Materiales: <input type="text" name="pint_precio[0]" /><input type="hidden" name="pint_nombre[0]" value="Pintura y otros productos" /><input type="hidden" name="pint_cantidad[0]" value="1" /></td></tr>
		<tr><td colspan="2" style="text-align:left;">Precio de Constante Material: <input type="text" name="pint_precio[1]" /><input type="hidden" name="pint_nombre[1]" value="Constante Materiales" /><input type="hidden" name="pint_cantidad[1]" value="1" /></td></tr>'."\n";
			} else {
				echo '		<tr><td colspan="2" style="text-align:left;">';
				echo'Agrega las Pinturas y materiales, uno por renglón: <span style="color:#f00; font-weight:bold;">Cantidad Descripción Código (al menos 8 letras o números sin espacio) Precio Público (si no sabe el precio colocar un # en lugar del precio)</span>:<input type="hidden" name="particular" value="1" />';
				echo '</td></tr>
		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audasust" cols="70" rows="13" style="background-color:#FFFFB0;" />' . $_SESSION['pres']['audasust'] . '</textarea><input type="hidden" name="consumibles" value="1" />
			</td></tr>
		<tr><td colspan="2" style="text-align:left;"><hr></td></tr>'."\n";
			}
		}
		if($sub_orden['sub_area'] <='6' || $sub_orden['sub_area'] >='8') {
			if($sub_orden['sub_siniestro']==='1') {
				echo '		<tr><td colspan="2" style="text-align:left;">Copia y Pega desde AUDATEX el <span style="color:#f00; font-weight:bold;">DESGLOSE MANO DE OBRA</span> del área correspondiente o Manualmente coloca la Descripción y Precio de la Mano de Obra:<br>Precio de Hora de Trabajo:<input type="text" name="preciounidad" value="' . $preciout . '" /></td></tr>
		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audamo" cols="70" rows="13" style="background-color:#FFFFB0;" /></textarea>
			<img src="imagenes/desglose-mo.jpg" alt="" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><hr></td></tr>'."\n";
			} else {
				echo '		<tr><td colspan="2" style="text-align:left;">Agrega aquí o selecciona desde el Almacén la Mano de Obra requerida: <span style="color:#f00; font-weight:bold;">Descripción Precio</span>:<input type="hidden" name="particular" value="1" /><br>Precio de Hora de Trabajo:<input type="text" name="preciounidad" value="' . $preciout . '" /></td></tr>'."\n";

				echo '		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audamo" cols="70" rows="13" style="background-color:#FFFFB0;" />' . $_SESSION['pres']['audamo'] . '</textarea>
			</td></tr>
		<tr><td colspan="2" style="text-align:left;"><hr></td></tr>'."\n";
			}
		} elseif ($sub_orden['sub_area'] == '7') {
			if($sub_orden['sub_siniestro']==='1') {
				echo '		<tr><td colspan="2" style="text-align:left;">Copia y Pega desde AUDATEX <span style="color:#f00; font-weight:bold;">HOJA SECCION DE PINTURA</span> las Descripción de los trabajos de Pintura:<br>O coloca manualmante cada labor en un renglón con la Descripción y las Unidades de trabajo (1 hora = 10 Unidades de Trabajo).<br><br>Precio de Hora de Trabajo:<input type="text" name="preciounidad" value="' . $preciout . '" /></td></tr>
		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audapint" cols="70" rows="13" style="background-color:#FFFFB0;" /></textarea></td></tr>
		<tr><td colspan="2" style="text-align:left;"><hr></td></tr>';
			} else {
				echo '		<tr><td colspan="2" style="text-align:left;">Agrega aquí o selecciona desde el Almacén la Mano de Obra requerida: <span style="color:#f00; font-weight:bold;">Descripción Precio</span>:<input type="hidden" name="particular" value="1" /><br>Precio de Hora de Trabajo:<input type="text" name="preciounidad" value="' . $preciout . '" /></td></tr>'."\n";

				echo '		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audamo" cols="70" rows="13" style="background-color:#FFFFB0;" />' . $_SESSION['pres']['audamo'] . '</textarea>
			</td></tr>
		<tr><td colspan="2" style="text-align:left;"><hr></td></tr>'."\n";
			}
		}
		$preg0 = "SELECT op_id, op_codigo, op_nombre, op_cantidad, op_precio, op_tangible, prod_id FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden['sub_orden_id'] . "' AND op_pres = '1' AND op_pedido < 1 ORDER BY op_tangible,op_item";
  		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de requeridos!");
		echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Refacciones, Consumibles y Mano de Obra';

		echo '.</td></tr>
			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td>Tipo</td><td>Cantidad</td><td>Nombre</td><td>Código</td><td>Precio</td><td>Borrar?</td></tr>'."\n";
		$cuenta = 0;
		while($op = mysql_fetch_array($matr0)) {
			if($op['op_tangible'] == '1') { $tipo = 'Refacción';}
			elseif($op['op_tangible'] == '2') { $tipo = 'Consumible';}
			else {$tipo = 'MO';}
			echo '					<tr><td>' . $tipo . '</td><td style="text-align:center;">' . $op['op_cantidad'] . '</td><td>' . $op['op_nombre'] . '</td><td>' . $op['op_codigo'] . '</td><td style="text-align:right;">' . money_format('%n', $op['op_precio']) . '</td><td><input type="checkbox" name="borrar[' . $cuenta . ']" value="1" /><input type="hidden" name="op_id[' . $cuenta . ']" value="' . $op['op_id'] . '" /></td></tr>'."\n";
			$cuenta++;
		}
		if($preaut === '1') { echo '<input type="hidden" name="preaut" value="1" />'."\n"; }
		echo '			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="orden_id" value="' . $sub_orden['orden_id'] . '" />
			<input type="hidden" name="aseguradora_id" value="' . $sub_orden['sub_aseguradora'] . '" />
			<input type="hidden" name="reporte" value="' . $sub_orden['sub_reporte'] . '" />
			<input type="hidden" name="aseguradora" value="' . $sub_orden['sub_aseguradora'] . '" />
			<input type="hidden" name="area" value="' . $sub_orden['sub_area'] . '" />
			<input type="hidden" name="sub_estatus" value="' . $sub_orden['sub_estatus'] . '" />'."\n";
		echo '			</td></tr></table>'."\n";
	}	
	echo '					<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>'."\n";
	echo '			</table>
			</form>'."\n";
}

elseif ($accion==='compara') {

	$funnum = 1045060;
	
	$retorno = validaAcceso('1045060', $dbpfx);
	
	if ($retorno == '1' || $_SESSION['rol05']=='1') {
		$msj = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Valuadores, ingresar Usuario y Clave correcta');
	}

	$error = 'no';
	$mensaje = '';		
	$sub_orden_id=preparar_entrada_bd($sub_orden_id);
	$area=preparar_entrada_bd($area);
	$preciout = limpiarNumero($preciounidad);
//	if($particular!='1') {$preciout = $preciounidad;}
	if((isset($audamo) && $audamo != '') || (isset($audapint) && $audapint != '')) { 
		if(($preciout == '0' || $preciout == '') && $desdecesta == '' ) {
			$_SESSION['pres']['mensaje']= 'Por favor indica el precio de la de la Hora de Mano de Obra.<br>';
			redirigir('presupuestos.php?accion=valuar&sub_orden_id=' . $sub_orden_id);
		}
	}

	$audasust2 = preg_split("/[\n]+/", $audasust);
//	print_r($audasust2);
	foreach ($audasust2 as $i => $v) {
		$precioar = '';
		unset($estructural);
		$codigo ='';
		$descripcion = '';
		$cant = '';
		$des = '';
// Identificar partes estructurales con un & al final de la línea de partes a sustituir.		
		for($j = strlen($v); $j >= 0; $j--) {
			if(ord($v[$j]) == 38) {
				$estructural = 1;
				break;
			}
			if(ord($v[$j]) == 32) {
				break;
			}
		}
// Obtener precio de parte
		for($j = strlen($v); $j >= 0; $j--) {
			if($v[$j]=='#') {
				$des = substr($v, 0, $j);
				$precioar = 0;
				break;
			}
			elseif(is_numeric($v[$j]) || $v[$j]=='.' ) {
				if($v[$j]==',') { $v[$j]='.'; }
				$precioar = $v[$j] . $precioar;
			}
			elseif(($v[$j]==' ' || ord($v[$j]) == '9') && $precioar!='') {
				$des = substr($v, 0, $j);
			break;
			}
		}
		
		if($precioar == '') { $precioar = '0'; }
		 
		if($particular == '1') {
			$res = $des;  //  intercambio de variables por desuso de descuentos
			for($j = 0; strlen($res) >= $j; $j++) {
				if(is_numeric($res[$j]) || $res[$j]=='.' ) {
					$cant = $cant . $res[$j];
				}
				elseif($res[$j]==' ' && $cant!='') {
					$des = substr($res, $j);
				break;
				}
			}
			if($cant == '') { $cant = '0'; }
		}

		$res = $des;  //  Obtención del código de la refacción
		for($j = strlen($res); $j >= 0; $j--) {
			if($nonumpart == '1') {
				$codigo = 'XXXXXXXX';
				$descripcion = $res;
				break;
			} else {
				if($res[$j] != ' ' && ord($res[$j]) != '9') {
					$codigo = $res[$j] . $codigo;
				}
				elseif(($res[$j]==' ' || ord($res[$j]) == '9') && strlen($codigo) > 4) {
					$descripcion = substr($res, 0, $j);
				break;
				}
			}
		}
		$audaprod[$i][0] = trim($descripcion);
		$audaprod[$i][1] = trim($precioar);
		$audaprod[$i][2] = trim($estructural);
		$audaprod[$i][3] = trim($codigo);
		$audaprod[$i][4] = trim($cant);
	}
//	print_r($audaprod);
//	echo '<br><br>';
	unset($audasust, $audasust2);

	$audamo2 = preg_split("/[\n]+/", $audamo);
		$precioar = '';
		$descripcion = '';
		$cant = '';
		$des = '';
	foreach ($audamo2 as $i => $v) {
		$precioar = '';
		for($j = strlen($v); $j >= 0; $j--) {
			if(is_numeric($v[$j]) || $v[$j]=='.' ) {
				$precioar = $v[$j] . $precioar;
			}
			elseif($v[$j]==' ' && $precioar!='') {
				$descripcion = substr($v, 0, $j);
				break;
			}
		}
		if($precioar == '') { $precioar = '0';}
		$audaobr[$i][0] = trim($descripcion);
		if($particular == '1') {$precioar * 10;}
		$audaobr[$i][1] = trim($precioar);
	}
//	print_r($audaobr);
//	echo '<br><br>';
	unset($audamo2, $audamo);

	$audapi = preg_split("/[\n]+/", $audapint);
//	print_r($audapi);
	foreach ($audapi as $i => $v) {
		$precioar = '';
		$descripcion = '';
		for($j = strlen($v); $j >= 0; $j--) {
			if(is_numeric($v[$j]) || $v[$j]==',' || $v[$j]=='.') {
				if($v[$j]==',') { $v[$j]='.'; }
				$precioar = $v[$j] . $precioar;
			}
			elseif($v[$j]==' ' && $precioar != '') {
				$descripcion = substr($v, 0, $j);
				break;
			}
		}
		if($precioar == '') { $precioar = '0';}
		$audap[$i][0] = trim($descripcion);
		$audap[$i][1] = trim($precioar);
	}
	
//	print_r($audap);
//	echo '<br><br>';
	unset($audapint, $audapi);

	$parametros='sub_orden_id = ' . $sub_orden_id;
	
	if(is_array($audaprod) && $audaprod[0][0] != '') {
		for($i=0;$i<=count($audaprod);$i++) {
			if($particular == '1' && $audaprod[$i][4] == '0') {
				$error = 'si';
				$mensaje .= 'No se agregó cantidad en una de las refacciones en trabajo particular.<br>';
			}
		}
	}
	
	if(is_array($audap) && $audap[0][0] != '') {
		$vermo = 0;
		for($i=0;$i<=count($audap);$i++) {
			$vermo = $vermo + $audap[$i][1];
		}
		if($vermo == '0') {
			$error = 'si';
			$mensaje .= 'No se agregó precio en mano de obra.<br>';
		}
	}

	if(is_array($audaobr) && $audaobr[0][0] != '') {
		$vermo = 0;
		for($i=0;$i<=count($audaobr);$i++) {
			$vermo = $vermo + $audaobr[$i][1];
		}
		if($vermo == '0') {
			$error = 'si';
			$mensaje .= 'No se agregó precio en mano de obra.<br>';
		}
	}


	if ($error === 'no' && (is_array($prod_id) || isset($paquete) || is_array($_SESSION['prods']['id']) || is_array($audaobr) || is_array($audap) || is_array($op_id))) {
		if (is_array($op_id)) {
			for($i=0;$i<count($op_id);$i++) {
				if(isset($borrar[$i]) && $borrar[$i]=='1') {
					$pregp = "SELECT prod_id, op_cantidad FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op_id[$i] . "' AND op_pedido < '1' AND op_surtidos < '0.0001'";
					$matrp = mysql_query($pregp);
					$regr = mysql_fetch_array($matrp);
					if($regr['prod_id'] > 0) {
						$pregup = "SELECT prod_cantidad_disponible FROM " . $dbpfx . "productos WHERE prod_id = '" . $regr['prod_id'] . "'";
						$matrup = mysql_query($pregup);
						$up = mysql_fetch_array($matrup);
						$disp = $up['prod_cantidad_disponible'] + $regr['op_cantidad'];
						$parme = " prod_id = '" . $regr['prod_id'] . "' ";
						$sqdat = ['prod_cantidad_disponible' => $disp];
						ejecutar_db($dbpfx . 'productos', $sqdat, 'actualizar', $parme);
						unset($sqdat);
						
//						$suma = "ACTUALIZAR " . $dbpfx . "productos SET prod_cantidad_disponible = prod_cantidad_disponible + " . $regr['op_cantidad'] . " WHERE prod_id = '" . $regr['prod_id'] . "'";
//						$resultado = mysql_query($suma) or die("ERROR: no se actualizaron los productos!");
					}
					$parme = " op_id = '" . $op_id[$i] . "' AND op_pedido < '1' AND op_surtidos < '0.0001' ";
					ejecutar_db($dbpfx . 'orden_productos', '', 'eliminar', $parme);

//					$pregunta="ELIMINAR FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op_id[$i] . "' AND op_pedido < '1' AND op_surtidos < '0.0001'";
//					$resultado = mysql_query($pregunta);
					
				}
			}
		}
		$preg1 = "SELECT prod_id, op_nombre, op_cantidad, op_precio, op_descuento, op_tangible, op_estructural, op_recibidos, op_autosurtido, op_pres FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "' AND op_tangible < '3'";
//		echo $preg1;
  		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden_productos!");
  		$preg2 = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub_orden_id . "'";
		$matr2 = mysql_query($preg2);
		$regr = mysql_fetch_array($matr2);
		if($regr['sub_aseguradora'] > 0) {
			$autosurtido = $asurt[$regr['sub_aseguradora']];
		} else {
			$particular = 1;
		}
  		
  		$sub_partes = 0; $sub_consumibles = 0; $sub_mo = 0;
  		while($op = mysql_fetch_array($matr1)) {
			$op_subtotal = round(($op['op_cantidad'] * ($op['op_precio'] - $op['op_descuento'])), 2);
			if($op['op_tangible']=='1' && ($autosurtido == 1 || $particular == 1 || $op['op_autosurtido']=='2'|| $op['op_autosurtido']=='3')) {
				$sub_partes = $sub_partes + $op_subtotal;
				$presupuesto = $presupuesto + $op_subtotal;
			} elseif($op['op_tangible']=='2') {
				$sub_consumibles = $sub_consumibles + $op_subtotal;
				$presupuesto = $presupuesto + $op_subtotal;
			} elseif($op['op_tangible']=='0') {
				$sub_mo = $sub_mo + $op_subtotal;
				$presupuesto = $presupuesto + $op_subtotal;
			}
//			echo $op_subtotal . '<br>';
  		}

		if (is_array($prod_id)) {
			for($i=0;$i<count($prod_id);$i++) {
				if($prod_cantidad[$i]!='') {
//						$preg1 = "SELECT prod_cantidad_disponible FROM " . $dbpfx . "productos WHERE prod_id = '" . $prod_id[$i] . "'";
//  					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de op_prods!");
//  					$op = mysql_fetch_array($matr1);
  					if($prod_cantidad[$i] > $prod_disponible[$i]) { $refacciones=1; }
					if($prod_tangible[$i]=='1') {
						$prod_cantidad[$i] = intval($prod_cantidad[$i]);
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_partes = $sub_partes + $op_subtotal;
					} elseif($prod_tangible[$i]=='2') {
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_consumibles = $sub_consumibles + $op_subtotal;
					} else {
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_mo = $sub_mo + $op_subtotal;
						$tiempo = $tiempo + $prod_cantidad[$i];
					}
					$presupuesto = $presupuesto + $op_subtotal;
					$sql_data_array = array('sub_orden_id' => $sub_orden_id,
						'op_area' => $area,
						'prod_id' => $prod_id[$i],
						'op_nombre' => $prod_nombre[$i],
						'op_codigo' => $prod_codigo[$i],
						'op_tangible' => $prod_tangible[$i],
						'op_precio' => $prod_precio[$i],
						'op_costo' => $prod_costo[$i],
						'op_pres' => '1',
						'op_subtotal' => $op_subtotal);
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
				}
			}
		}

		if (isset($paquete) && $paquete!='') {
			$preg3 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE sub_orden_id='" . $sub_orden_id . "' AND sub_estatus < '112'";
			$matr3 = mysql_query($preg3) or die($preg3);
			$reporte = mysql_fetch_array($matr3);
			$preg4 = "SELECT sub_orden_id, sub_area FROM " . $dbpfx . "subordenes WHERE sub_reporte='" . $reporte['sub_reporte'] . "' AND sub_estatus < '112'";
			$matr4 = mysql_query($preg4) or die($preg4);
			while($tarea = mysql_fetch_array($matr4)) {
				$preg0 = "SELECT pc_prod_id, pc_prod_cant, pc_area_id FROM " . $dbpfx . "paq_comp WHERE pc_paq_id='" . $paquete . "' AND pc_activo = '1' AND pc_area_id = '" . $tarea['sub_area'] . "'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de paq_prods!");
			
				while($paqs = mysql_fetch_array($matr0)) {
					$preg1 = "SELECT prod_codigo, prod_nombre, prod_tangible, prod_precio FROM " . $dbpfx . "productos WHERE prod_id='" . $paqs['pc_prod_id'] . "'";
//			echo $preg1.'<br>';
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de paq_prods!");
					while($prods = mysql_fetch_array($matr1)) {
						$op_subtotal= $paqs['pc_prod_cant'] * $prods['prod_precio'];
						$presupuesto = $presupuesto + $op_subtotal;
						$preg2 = "SELECT op.prod_id, op.op_cantidad, p.prod_cantidad_existente FROM " . $dbpfx . "orden_productos op, " . $dbpfx . "productos p WHERE p.prod_id = '" . $prod_id[$i] . "' AND op.prod_id = p.prod_id";
  						$matr2 = mysql_query($preg1) or die("ERROR: Fallo selección de paq_prods!");
  						$op = mysql_fetch_array($matr2);
	  					if($op['op_cantidad'] > $op['prod_cantidad_existente']) { $refacciones=1; }
						if($prods['prod_tangible']=='1') {
							$sub_partes = $sub_partes + $op_subtotal;
						} elseif($prods['prod_tangible']=='2') {
							$sub_consumibles = $sub_consumibles + $op_subtotal;
						} else {
							$sub_mo = $sub_mo + $op_subtotal;
							$tiempo = $tiempo + $paqs['pc_prod_cant'];
						}
						$sql_data_array = array('sub_orden_id' => $tarea['sub_orden_id'],
							'op_area' => $area,
							'prod_id' => $paqs['pc_prod_id'],
							'op_nombre' => $prods['prod_nombre'],
							'op_codigo' => $prods['prod_codigo'],
							'op_cantidad' => $paqs['pc_prod_cant'],
							'op_tangible' => $prods['prod_tangible'],
							'op_precio' => $prods['prod_precio'],
							'op_pres' => '1',
							'op_autosurtido' => $autosurtido,
							'op_subtotal' => $op_subtotal);
						$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
					}
				}
			}
		}
		
		if (is_array($audaprod)) {
			$op_subtotal = 0;
			for($i=0;$i<=count($audaprod);$i++) {
				if(($audaprod[$i][0]!='') && ($audaprod[$i][1]!='')) {
					if($particular == '1') {
						$cant1 = $audaprod[$i][4];
					} else {
						$cant1 = '1'; 
					}
					if($consumibles=='1') { $tang = 2; } 
					else { $tang = 1; }
//					$f_promesa = dia_habil($audadd);
//					$descuento = round(($audaprod[$i][1]*($audaprod[$i][2]/100)), 2);
					$op_subtotal = $cant1 * ($audaprod[$i][1] - $descuento);
					$sql_data_array3 = array('sub_orden_id' => $sub_orden_id,
//						'prod_id' => $prod_id,
						'op_area' => $area,
						'op_nombre' => $audaprod[$i][0],
						'op_codigo' => $audaprod[$i][3],
						'op_cantidad' => $cant1,
						'op_precio' => $audaprod[$i][1],
						'op_subtotal' => $op_subtotal,
//						'op_descuento' => $descuento,
						'op_tangible' => $tang,
						'op_pres' => '1',
						'op_estructural' => $audaprod[$i][2]);

					if(($autosurtido=='1' || $particular == '1') && $bloqueaprecio == '0') {
						if($tang == '1') {
							$sub_partes = $sub_partes + $op_subtotal;
						} else {
							$sub_consumibles = $sub_consumibles + $op_subtotal;
						}
						$presupuesto = $presupuesto + $op_subtotal;
					}
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array3, 'insertar');
				}
			}
		}
		
		if (is_array($audaobr)) {
			for($i=0;$i<=count($audaobr);$i++) {
				if(($audaobr[$i][0]!='') && ($audaobr[$i][1]!='')) {
					$cant1 = round(($audaobr[$i][1] / $preciout), 4);
					$tiempo = $tiempo + $cant1;
					if($cant1 < 0) { $preciout = $preciout * -1; }
					$sql_data_array4 = array('sub_orden_id' => $sub_orden_id,
//						'prod_id' => '6',
						'op_area' => $area,
						'op_nombre' => $audaobr[$i][0],
						'op_tangible' => 0,
						'op_pres' => '1',
						'op_cantidad' => $cant1);
					if($bloqueaprecio == '0') {
						$op_subtotal= round(($cant1 * $preciout), 2);
						$sub_mo = $sub_mo + $op_subtotal;
						$presupuesto = $presupuesto + $op_subtotal;
						$sql_data_array4['op_precio'] = $preciout;
						$sql_data_array4['op_subtotal'] = $op_subtotal;
					}
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array4, 'insertar');
				}
			}
		}
		
		if (is_array($pint_nombre)) {
			for($i=0;$i<count($pint_nombre);$i++) {
				if ($pint_precio[$i]!='') {
					$sql_data_array = array('sub_orden_id' => $sub_orden_id,
						'op_area' => $area,
						'op_nombre' => $pint_nombre[$i],
						'op_cantidad' => $pint_cantidad[$i],
						'op_pres' => '1',
						'op_tangible' => 2);
					if($bloqueaprecio == '0') {
						$pint_precio[$i] = limpiarNumero($pint_precio[$i]);
						$op_subtotal= $pint_cantidad[$i] * $pint_precio[$i];
						$presupuesto = $presupuesto + $op_subtotal;
						$sub_consumibles = $sub_consumibles + $op_subtotal;
						$sql_data_array['op_precio'] = $pint_precio[$i];
						$sql_data_array['op_subtotal'] = $op_subtotal;
					}
//					if($i == 0) { $sql_data_array['prod_id'] = '11'; } else { $sql_data_array['prod_id'] = '12'; }
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
				}
			}
		}

		if (is_array($audap)) {
			for($i=0;$i<=count($audap);$i++) {
				if(($audap[$i][0]!='') && ($audap[$i][1]!='')) {
					if($c_pint[$aseguradora_id] == '1') { 
						$cantmo = $audap[$i][1] / $preciout; 
						$tiempo = $tiempo + ($audap[$i][1] / 100);
					} else {
						$cantmo = $audap[$i][1] / 10;
						$tiempo = $tiempo + ($audap[$i][1] / 10);
					}
					if($preciout < 0) { $preciout = $preciout * -1; $cantmo = $cantmo * -1; }
					$sql_data_array4 = array('sub_orden_id' => $sub_orden_id,
//						'prod_id' => '7',
						'op_area' => $area,
						'op_nombre' => $audap[$i][0],
						'op_tangible' => 0,
						'op_pres' => '1',
						'op_cantidad' => $cantmo);
					if($bloqueaprecio == '0') {
						$op_subtotal= $cantmo * $preciout;
						$sub_mo = $sub_mo + $op_subtotal;
						$presupuesto = $presupuesto + $op_subtotal;
						$sql_data_array4['op_precio'] = $preciout;
						$sql_data_array4['op_subtotal'] = $op_subtotal;
					}
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array4, 'insertar');
				}
			}
		}
		redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id . '#' . $sub_orden_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('presupuestos.php?accion=comparar&sub_orden_id=' . $sub_orden_id);
	}
}

elseif ($accion==='cppresauth') {

	if (validaAcceso('1045090', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol05']=='1'))) {
		// Acceso autorizado
	} else {
		redirigir('presupuestos.php?accion=consultar&sub_orden_id=' . $sub_orden_id . '&area=' . $area);
	}

// ------ DETERMINACIÓN DE NÚMERO DE ITEM ------
	$preg0 = "SELECT orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub_orden_id . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de subordenes items! " . $preg0);
	$ord = mysql_fetch_array($matr0);
	$orden_id = $ord['orden_id'];
	echo 'OT: '. $orden_id;
	$preg1 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de subordenes items! " . $preg1);
	$item = 1;
	while($dato1 = mysql_fetch_array($matr1)) {
		$preg2 = "SELECT op_item FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $dato1['sub_orden_id'] . "' ORDER BY op_item DESC LIMIT 1";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de orden_productos! " . $preg2);
		$dato2 = mysql_fetch_array($matr2);
		if($dato2['op_item'] >= $item) {$item = $dato2['op_item'] + 1;}
	}

	// --- Copia items de presupuesto sólo en tareas con estatus 129: Valuando.
	$preg1 = "SELECT op_id FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "' AND op_pres IS NULL";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden productos! " . $preg1);
	if(mysql_num_rows($matr1) < 1) {
		$preg2 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de orden productos! " . $preg2);
		while($op = mysql_fetch_assoc($matr2)) {
			// --- Crea una copia de los datos escenciales del item original presupuestado ---
			$sql_op = [
				'op_item' => $item,
				'op_codigo' => $op['op_codigo'],
				'op_nombre' => $op['op_nombre'],
				'sub_orden_id' => $op['sub_orden_id'],
				'op_area' => $op['op_area'],
				'op_cantidad' => $op['op_cantidad'],
				'op_tangible' => $op['op_tangible'],
			];
			$nvoopid = ejecutar_db($dbpfx . 'orden_productos', $sql_op, 'insertar');
			$item++;
		}
		bitacora($orden_id, 'Se copiaron items presupuestados a autorizados el la Tarea de ' . constant('NOMBRE_AREA_' . $dato1['sub_area']) . ': ' . $dato1['sub_orden_id'], $dbpfx);
		redirigir('presupuestos.php?accion=valuar&sub_orden_id=' . $sub_orden_id . '&area=' . $area);
	} else {
		$_SESSION['msjerror'] = 'Hay items en valuación, no se pueden copiar aquí los presupuestados mientras haya item en valuación';
		redirigir('presupuestos.php?accion=consultar&sub_orden_id=' . $sub_orden_id . '&area=' . $area);
	}
}

elseif ($accion==='esperar') {

	$funnum = 1045095;

	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección! 2445 " . $pregunta);
	$sub_orden = mysql_fetch_array($matriz);
	$pregunta2 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $sub_orden['orden_id'] . "'";
	$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección! 2448 " . $pregunta2);
	$orden = mysql_fetch_array($matriz2);

//	echo $preg;
	echo '	<form action="presupuestos.php?accion=espera" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">';
	echo '		<tr><td style="text-align:left;" colspan="2">' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . $lang['Placas'] . $orden['orden_vehiculo_placas'] . '</td></tr>
		<tr class="cabeza_tabla"><td colspan="2">' . $lang['GestAseg'] . ' <img src="' . constant('ASEGURADORA_' . $sub_orden['sub_aseguradora']) . '" alt="' . constant('ASEGURADORA_NIC_' . $sub_orden['sub_aseguradora']) . '" title="' . constant('ASEGURADORA_NIC_' . $sub_orden['sub_aseguradora']) . '"></td></tr>';
	echo '		<tr><td colspan="2" style="text-align:left;">' . $lang['MotivoEspera'] . '</td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left;"><textarea name="motivo" cols="46" rows="6">';
		if($_SESSION['pres']['motivo'] != '') { echo $_SESSION['pres']['motivo']; unset($_SESSION['pres']); }
			echo '</textarea></td></tr>
		<tr><td colspan="2">'."\n";
		if($preaut === '1') { echo '<input type="hidden" name="preaut" value="1" />'."\n"; }
	echo '			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="orden_id" value="' . $sub_orden['orden_id'] . '" />
			<input type="hidden" name="aseguradora_id" value="' . $sub_orden['sub_aseguradora'] . '" />
			<input type="hidden" name="reporte" value="' . $sub_orden['sub_reporte'] . '" />
			<input type="hidden" name="aseguradora" value="' . $sub_orden['sub_aseguradora'] . '" />
			<input type="hidden" name="area" value="' . $sub_orden['sub_area'] . '" />
			<input type="hidden" name="sub_estatus" value="' . $sub_orden['sub_estatus'] . '" />
		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>'."\n";
	echo '	</table>
	</form>'."\n";
}

elseif ($accion==='espera') {

	if (validaAcceso('1045100', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol02']=='1' || $_SESSION['rol05']=='1'))) {
		$msj = 'Acceso autorizado';
	} else {
		$_SESSION['msjerror'] = $lang['Acceso No Autorizado'];
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
	$error = 'no';
	$mensaje = '';
	if ($error === 'no') {
		$parametros = "sub_reporte = '" . $reporte . "' AND sub_aseguradora = '$aseguradora' AND sub_estatus < '130' AND orden_id = '$orden_id'";
		unset($sql_data_array);
		$sql_data_array['sub_estatus'] = '122';
		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, $lang['SinPuestoEspe'], $dbpfx, $lang['SinPuestoEspe'] . ' ' . $lang['DebidoA'] . ' ' . $motivo, 0);
		actualiza_orden($orden_id, $dbpfx);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['pres']['motivo'] = $motivo;
		$_SESSION['msjerror'] = $mensaje;
		redirigir('presupuestos.php?accion=esperar&sub_orden_id=' . $sub_orden_id);
	}
}

elseif ($accion==="trabmodificar") {

	if(validaAcceso('1040075', $dbpfx) == '1' || $_SESSION['rol02'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol05'] == '1'  || $_SESSION['rol06'] == '1' || $_SESSION['rol07'] == '1') {
		// Acceso autorizado
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado');
	}

	$trabestat = [
		'101' => '10',
		'124' => '20',
		'127' => '30',
		'128' => '40',
		'129' => '50',
		'122' => '60',
		'120' => '70',
		'102' => '80',
		'103' => '90',
		'104' => '100',
		'105' => '110',
		'106' => '120',
		'107' => '130',
		'108' => '140',
		'109' => '150',
		'110' => '160',
		'111' => '170',
		'121' => '180',
		'112' => '190',
	];

	$pregunta = "SELECT s.*, o.orden_estatus FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND s.sub_reporte = '$reporte' AND s.sub_estatus < '130' AND o.orden_id = s.orden_id";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de Tareas! 2948 ".$pregunta);
	$repestat = 0;
//	echo $pregunta;
	while($sub_orden = mysql_fetch_array($matriz)) {
		if($sub_orden['sub_poliza'] != '') { $poliza = $sub_orden['sub_poliza']; }
//		if($sub_orden['sub_deducible'] > $deducible) { $deducible = $sub_orden['sub_deducible']; }
		$aseguradora = $sub_orden['sub_aseguradora'];
		$sub_orden_id = $sub_orden['sub_orden_id'];
		$ordestatus = $sub_orden['orden_estatus'];
		if($trabestat[$sub_orden['sub_estatus']] > $repestat) { $repestat = $trabestat[$sub_orden['sub_estatus']]; }
	}
//	echo 'Valor del Trabajo: ' . $repestat . '<br>';
	
	if($repestat > 0) {
		foreach($trabestat as $ktr => $vtr) {
//			echo 'Indice: ' . $ktr . ' Valor: ' . $vtr . '<br>';
			if($vtr == $repestat) {
				$repestat = $ktr;
				break;
			}
		}
	}
	
	$veh = datosVehiculo($orden_id, $dbpfx);

	echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">
			<tr><td style="text-align:left; vertical-align:top;">'."\n";
	echo '				<form action="presupuestos.php?accion=trabmodifica" method="post" enctype="multipart/form-data">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="0" class="agrega">
					<tr><td colspan="2" style="text-align:left; font-size:1.2em;">' . $veh['marca'] . ' ' . $veh['tipo'] . ' ' . $veh['color'] . ' ' . $lang['Placas'] . $veh['placas'] .'</td></tr>'. "\n";
	echo '					<tr><td colspan="2" style="text-align:left;">Cliente: <img src="' . constant('ASEGURADORA_' . $aseguradora) . '" alt="' . constant('ASEGURADORA_NIC_' . $aseguradora) . '" title="' . constant('ASEGURADORA_NIC_' . $aseguradora) . '"> Reporte: ' . $reporte . "\n";
	echo '					<tr class="cabeza_tabla"><td colspan="2">Modificación general al Siniestro o Trabajo Particular</td></tr>'."\n";
	if($veh['flota'] < 1 && $ordestatus < 30) {
		echo '					<tr><td>Cliente: </td><td style="text-align:left;">
						<select name="nvaaseguradora" size="1">
							<option value="0" ';
		if($aseguradora == '0') { echo 'selected ';}
		echo '>Particular</option>'."\n";
//		$pregunta3 = "SELECT aseguradora_id, aseguradora_nic FROM " . $dbpfx . "aseguradoras";
//		$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
//		while($aseg3 = mysql_fetch_array($matriz3)) {
		foreach($area_ut as $kas => $vas) {
			echo '							<option value="' . $kas . '" ';
			if($kas == $aseguradora) { echo 'selected ';}
			echo '>' . constant('ASEGURADORA_NIC_' . $kas) . '</option>'."\n";
		}
		echo '						</select>
					</td></tr>'."\n";
	} else {
		echo '					<tr><td colspan="2"><span style="color:red;font-weight:bold;">En OTs terminadas o con estatus de Pérdida, no se permite el cambio de aseguradora o convenio</span><input type="hidden" name="nvaaseguradora" value="' . $aseguradora . '" /></td></tr>'."\n";
	}
	echo '				<tr><td>Imagen escaneada en JPG de la orden de admisión</td><td style="text-align:left;"><input type="file" name="orden_adm" size="30" /></td></tr>
				<tr><td>Imagen escaneada en JPG de la hoja de daños</td><td style="text-align:left;"><input type="file" name="levante" size="30" /></td></tr>
				<tr><td>Número de reporte, siniestro o garantía: </td><td style="text-align:left;"><input type="text" name="nvoreporte" size="20" maxlength="40" value="' . $reporte . '" /></td></tr>'."\n";

	// ------ Determina quien tiene permiso de cambiar estatus de las tareas ---
	unset($nvoe);
	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && $_SESSION['rol07'] == '1')) {
		$acceso[40] = 1;
	}
	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && $_SESSION['rol06'] == '1')) {
		$acceso[30] = 1;
	}
	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && $_SESSION['rol05'] == '1')) {
		$acceso[20] = 1;
	}
	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && $_SESSION['rol04'] == '1')) {
		$acceso[15] = 1;
	}
	if(validaAcceso('1045010', $dbpfx) == '1' || ($solovalacc != 1 && $_SESSION['rol02'] == '1')) {
		$acceso[10] = 1;
	}

	// --- Estatus permitidos para cambiar -----
	if($repestat == 124) {
		$derecho = 'Asesor de Servicio';
		if($acceso[30] == 1) { $nvoe = [101]; }
	} elseif($repestat == 128) {
		$derecho = 'Presupuestador';
		if($acceso[40] == 1) { $nvoe = [124]; }
	} elseif($repestat == 129) {
		$derecho = 'Valuador';
		if($acceso[20] == 1) { $nvoe = [128,124,101]; }
	} elseif($repestat == 122) {
		$derecho = 'Valuador';
		if($acceso[20] == 1) { $nvoe = [129,128,124,101]; }
	} elseif($repestat == 120) {
		$derecho = 'Valuador';
		if($acceso[20] == 1) { $nvoe = [122,129,128,124,101]; }
	} elseif($repestat == 102) {
		$derecho = 'Valuador';
		if($acceso[20] == 1) { $nvoe = [120,122,129,128,124,101]; }
	} elseif($repestat >= 104 && $repestat <= 108) {
		$derecho = 'Jefe de Taller y Gerente';
		if($acceso[10] == 1) {
			$nvoe = [104,102,120,129,128,124,101];
		} elseif($acceso[15] == 1) {
			//$nvoe = [104,102];
			$nvoe = [104,102,120,129,128,124,101];
		}
	} elseif($repestat == 111) {
		$derecho = 'Jefe de Taller y Gerente';
		if($acceso[10] == 1) { $nvoe = [104,102,120,129,128,124,101]; }
		elseif($acceso[15] == 1) {
			//$nvoe = [104,102];
			$nvoe = [104,102,120,129,128,124,101];
		}
	} elseif($repestat == 121) {
		$derecho = 'Jefe de Taller y Gerente';
		if($acceso[10] == 1) { $nvoe = [111,104,102,120,129,128,124,101]; }
		elseif($acceso[15] == 1) {
			//$nvoe = [111,104,102];
			$nvoe = [111,104,102,120,129,128,124,101];
		}
	} elseif($repestat == 112) {
		$derecho = 'Gerente';
		if($acceso[10] == 1) { $nvoe = [121,111,104,102,120,129,128,124,101]; }
	} else {
		$derecho = 'No está permitido el cambio directo en este estatus';
	}

	if(count($nvoe) >= 1 && $ordestatus < 30) {
		echo '					<tr><td>Nuevo Estatus</td><td style="text-align:left;">'."\n";
		echo '						<select name="nvoestatus" >'."\n";
		echo '							<option value="' . $repestat . '" selected >' . constant('TAREA_ESTATUS_'. $repestat) . '</option>'."\n";
		foreach($nvoe as $k) {
			if($k != $repestat) {
				echo '							<option value="' . $k . '">' . constant('TAREA_ESTATUS_'. $k) . '</option>'."\n";
			}
		}
		echo '						</select>'."\n";
	} elseif($repestat > '0' && $ordestatus < 30) {
		echo '					<tr><td colspan="2">Estatus: ' . constant('TAREA_ESTATUS_'. $repestat) . '<br><span style="color:red;">Este estatus lo puede cambiar un : <strong>' . $derecho . '</strong></span>
						<input type="hidden" name="nvoestatus" value="' . $repestat . '" />'."\n";
	} else {
		echo '					<tr><td colspan="2"><span style="color:red;"><strong>No está permitido el cambio de estatus de Tareas en OTs Cerradas o con estatus de Pérdida</strong></span>
						<input type="hidden" name="nvoestatus" value="' . $repestat . '" />'."\n";
	}
	echo '					</td></tr>'."\n";

	echo '				<tr><td colspan="2">
						<input type="hidden" name="reporte" value="' . $reporte . '" />
						<input type="hidden" name="estatus" value="' . $repestat . '" />
						<input type="hidden" name="orden_id" value="' . $orden_id . '" />
					</td></tr>
				<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>'."\n";
	echo '				<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>';
	echo '				<tr><td colspan="2" style="text-align:left;">';
	echo '				<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo" border="1" ></a>'."\n";
	echo '</td></tr>'."\n";
	echo '			</table></form></td></tr>'."\n";
	echo '		</table>'."\n";
}

elseif ($accion==='trabmodifica') {

	if(validaAcceso('1040075', $dbpfx) == '1' || $_SESSION['rol02'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol05'] == '1'  || $_SESSION['rol06'] == '1' || $_SESSION['rol07'] == '1') {
		// Acceso autorizado
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado.');
	}

	$error = 'no';
	$mensaje = '';
	$nvoreporte = preparar_entrada_bd($nvoreporte);

	if ($error === 'no') {
		if($_FILES['orden_adm']['name'] != '') {
			$resultado = agrega_documento($orden_id, $_FILES['orden_adm'], 'Orden de Admisión', $dbpfx);
			if ($resultado['error'] == 'si') {
				$_SESSION['msjerror'] .= "Ocurrió algún error al subir el archivo de Orden de Admisión. No pudo guardarse.<br>";
			}
			$nombre_archivo = $resultado['nombre'];
		}

		if($_FILES['levante']['name'] != '') {
			$resultado = agrega_documento($orden_id, $_FILES['levante'], 'Hoja de Daños', $dbpfx);
			if ($resultado['error'] == 'si') {
				$_SESSION['msjerror'] .= "Ocurrió algún error al subir el archivo de Hoja de Daños. No pudo guardarse.<br>";
			}
		}

		$sql_data_array = array();
		if($reporte != '0' && $reporte != '') {
			$previo = 'Siniestro ' . $reporte;
		} else {
			$previo = 'Trabajo Particular';
		}

	$trabestat = [
		'101' => '10',
		'124' => '20',
		'127' => '30',
		'128' => '40',
		'129' => '50',
		'122' => '60',
		'120' => '70',
		'102' => '80',
		'103' => '90',
		'104' => '100',
		'105' => '110',
		'106' => '120',
		'107' => '130',
		'108' => '140',
		'109' => '150',
		'110' => '160',
		'111' => '170',
		'121' => '180',
		'112' => '190',
	];

		$preg = "SELECT s.sub_orden_id, s.sub_estatus, s.fact_id, s.recibo_id FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o  WHERE s.orden_id = '" . $orden_id . "' AND s.sub_reporte = '" . $reporte . "' AND s.sub_estatus < '130' AND o.orden_estatus < '30' AND o.orden_id = s.orden_id";
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de tareas! 3159 " . $preg);
//		echo $preg;
//		echo $nvoestatus;
		while($sub = mysql_fetch_assoc($matr)) {
			$hacer = 'si';
			$mensaje = 'La Tarea ' . $sub['sub_orden_id'];
			if($trabestat[$sub['sub_estatus']] > $trabestat[$nvoestatus]) {
				if($sub['fact_id'] > 0) {
					$mensaje .= ' ya está facturada';
					$hacer = 'no';
				}
				if($sub['recibo_id'] > 0) {
					$mensaje .= ' ya tiene recibo de destajo';
					$hacer = 'no';
				}
			} elseif($trabestat[$sub['sub_estatus']] < $trabestat[$nvoestatus]) {
				$mensaje .= ' no se puede adelantar de estatus, sólo se puede regresar';
				$hacer = 'no';
			}
			if($hacer == 'si') {
				$sqlestat['sub_estatus'] = $nvoestatus;
				$parametros = " sub_orden_id = '" . $sub['sub_orden_id'] . "'";
				ejecutar_db($dbpfx . 'subordenes', $sqlestat, 'actualizar', $parametros);
				bitacora($orden_id, 'Cambio de estatus de la tarea ' . $sub['sub_orden_id'] . ' de ' . constant('TAREA_ESTATUS_' . $sub['sub_estatus']) . ' a ' . constant('TAREA_ESTATUS_' . $nvoestatus),  $dbpfx, 'Cambio de estatus de la tarea ' . $sub['sub_orden_id'] . ' de ' . constant('TAREA_ESTATUS_' . $sub['sub_estatus']) . ' a ' . constant('TAREA_ESTATUS_' . $nvoestatus), 0, $sub['sub_orden_id']);
				unset($sqlestat);
				$mensaje = '';
			} else {
				$mensaje .= ', no se actualizó el estatus de esta Tarea.<br>';
				$_SESSION['msjerror'] .= $mensaje;
			}
		}

		if($flota < 1) {
			$sql_data_array['sub_aseguradora'] = $nvaaseguradora;
			$sql_data_array['sub_reporte'] = $nvoreporte;
			if($nvaaseguradora > '0') {
				if($ConvenioGarantia == $nvaaseguradora) {
					$sql_orden['orden_servicio'] = '2';
					$sql_orden['orden_garantia'] = $nvoreporte;
				} else {
					$sql_orden['orden_servicio'] = '4';
				}
				$sql_data_array['sub_doc_adm'] = $nombre_archivo;
				$sql_data_array['sub_siniestro'] = '1';
			} else {
				$sql_orden['orden_servicio'] = '3';
				$sql_data_array['sub_siniestro'] = '0';
			}

			$parametros = " sub_reporte = '" . $reporte . "' AND orden_id = '" . $orden_id . "'";
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
			if($sql_orden['orden_servicio'] == '2') {
				$motivo = ' al servicio de Garantía';
			} elseif($sql_orden['orden_servicio'] == '4') {
				$motivo = ' al siniestro';
			} else {
				$motivo = ' al trabajo Particular';
			}
			$motivo .= ' del convenio ' . $aseguradora . ' a ' . $nvaaseguradora . ' y del reporte ' . $reporte . ' al reporte ' . $nvoreporte;
			bitacora($orden_id, 'Ajustes generales ' . $motivo,  $dbpfx);
			unset($sql_data_array);
			$parametros='orden_id = ' . $orden_id;
			ejecutar_db($dbpfx . 'ordenes', $sql_orden, 'actualizar', $parametros);
			unset($sql_orden);
			actualiza_orden ($orden_id, $dbpfx);
		}
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id . '#' . $regresa);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id . '#' . $regresa);
	}	
}

?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
