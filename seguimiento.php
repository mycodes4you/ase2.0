<?php 
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/seguimiento.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if($seguimiento == 1 && !isset($_SESSION['usuario'])) {
	$_SESSION['msjerror'] = 'Para registrar avances de reparación, el Operario debe ingresar al sistema con su usuario y clave.';
	redirigir('usuarios.php');
}

if ($accion==="seguimiento") {

	$funnum = 1130000;

	//$mensaje = isset($mensaje) ? $mensaje : '';

	if($agencia == 'PROFEREAUTO') {
		$codigos = explode(' ', $codigo);
		//$operador = $codigos[2];
		if($codigos[1] != '') {
			$sub_orden_id = $codigos[1];
			$orden_id = $codigos[0];
		} else {
		$sub_orden_id = $codigo;
		}
	} else {
		$sub_orden_id = $codigo;
	}
	if($numero!='') {
		$operador = $numero;
	} else {
		redirigir('seguimiento.php?accion=operador&codigo=' . $codigo);
	}
	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	$sub = mysql_fetch_array($matriz);
	if ($sub['sub_estatus'] == 111 || $sub['sub_estatus'] == 121 || $sub['sub_estatus'] == 127)  {
		if (!isset($_SESSION['usuario'])) {
			$_SESSION['msjerror'] = '';
			redirigir('usuarios.php?mensaje='. $lang['Tarea no esta en proceso']);
		} else {
			redirigir('entrega.php?accion=seguimiento&codigo=' . $codigo);
		}
	}
	$pregunta2 = "SELECT usuario, sub_orden_id, estatus FROM " . $dbpfx . "usuarios WHERE rol09 = '1' AND activo ='1' AND usuario = '" . $operador . "'";
	$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
	$opr = mysql_fetch_array($matriz2);
	$filas = mysql_num_rows($matriz2);
	if($filas == 0) {
		redirigir('seguimiento.php?accion=operador&codigo=' . $codigo . '&mensaje='. $lang['Operador no existe o no tiene el código de puesto']);
	}
	if($metodo != 'c' && $sub['sub_operador'] != $operador) {
		
		redirigir('seguimiento.php?accion=directo&mensaje='. $lang['Operador ingresado no concuerda con la tarea']);
		
	}
	if ($sub['sub_estatus'] >= 104 && $sub['sub_estatus'] <= 110) {
		$msj = $lang['Acceso autorizado'];
	} elseif($preaut == '1' && ($sub['sub_estatus'] < '104' || $sub['sub_estatus'] == '120' || ($sub['sub_estatus'] > '121' && $sub['sub_estatus'] < '130'))) {
		$msj =$lang['Acceso autorizado'];
	} else {
		redirigir('seguimiento.php?accion=directo&mensaje='. $lang['Tarea marcada'].' <strong>'. $lang['No se puede reparar'].'</strong>'. $lang['consulta con Jefe de Taller']);
	}
	
	$preg0 = "SELECT * FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '$sub_orden_id' AND (seg_opr_apoyo IS NULL OR seg_opr_apoyo < 1) ORDER BY seg_id DESC LIMIT 1";
	//echo $preg0 . '<br>';
	$matr0 = mysql_query($preg0);
	$numseg = mysql_num_rows($matr0);
	$seg = mysql_fetch_array($matr0);
	if($seg['seg_tipo'] == '7') {
		//redirigir('seguimiento.php?accion=directo&mensaje=La tarea ya esta terminada, no se pueden registrar nuevas acciones');
	}
	
	include('parciales/encabezado.php'); 
	echo '	<div id="body">'."\n";
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">'."\n";
	echo '	<br><form action="seguimiento.php?accion=registrar" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="3" border="0" class="agrega" width="650">'."\n";
	if($mensaje!='') {
		echo '		<tr class="alerta"><td colspan="2">' . $mensaje . '</td></td></tr>'."\n";
	}
	if($opr['estatus'] == '1' && $opr['sub_orden_id'] != $sub_orden_id) {
	
		$pregunta3 = "SELECT o.orden_id, o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas FROM " . $dbpfx . "ordenes o, " . $dbpfx . "subordenes s WHERE s.sub_orden_id = '" . $opr['sub_orden_id'] . "' AND s.orden_id = o.orden_id";
		$matriz3 = mysql_query($pregunta3);
		$asig = mysql_fetch_array($matriz3);
		$vehiculo = $asig['orden_vehiculo_marca'] . ' ' . $asig['orden_vehiculo_tipo'] . ' ' . $asig['orden_vehiculo_color'] . ' ' . $asig['orden_vehiculo_placas'];
		$pausar = 1;
		//redirigir('seguimiento.php?accion=directo&mensaje='. $lang['El Operador'] . $operador . $lang['OCUPADO en el vehiculo'] . $vehiculo . $lang['tarea'] . $opr['sub_orden_id'] . $lang ['NO puede trabajar al mismo tiempo más de una SOT'].'<br><br>'. $lang['coloca el número correcto' ]. $opr['sub_orden_id'] . $lang['pasa tu credencial y pones en pausa o terminar la tarea'] . $opr['sub_orden_id'] . $lang['estás ocupado']);
		echo '		<tr>
					<td colspan="2" style="text-align:left;"> 
						<span style="font-size:18px; font-weight:bold;">
							' . $lang['El Operador'] . $operador . $lang['OCUPADO en el vehiculo'] . $vehiculo . $lang['tarea'] . $opr['sub_orden_id'] . '
							 , Si deseas iniciar o continuar esta tarea, se pausará la tarea ' . $opr['sub_orden_id']. ' si no hay un ayudante que la cubra.
						</span>
					</td>
				</tr>'."\n";
		 
	} 
	
	echo '
				<tr class="cabeza_tabla"><td colspan="2"><span style="font-size:18px; font-weight:bold;">'. $lang['Selecciona acción para la Tarea'] . constant('NOMBRE_AREA_' . $sub['sub_area']) . ': ' . $sub['sub_descripcion'] . '</span></td></tr>'."\n";
	
	if ($numseg < '1')  {
		//if($sub['sub_estatus'] == '104') {
		echo '		<tr>
			<td style="text-align:center;" colspan="2">'. $lang['INICIAR'].'<br><input type="image" src="idiomas/' . $idioma . '/imagenes/iniciar.png" name="seleccion1" value="1" /></td></tr>'."\n";
	} elseif ($sub['sub_estatus'] >= '105' && $sub['sub_estatus'] <= '107' ) {

		echo '		<tr>
			<td style="text-align:center;">'. $lang['CONTINUAR'].'<br><input type="image" src="idiomas/' . $idioma . '/imagenes/continuar.png" name="seleccion5" value="5" /></td></tr>'."\n";
	
	} elseif (($seg['seg_tipo'] == '1' || $seg['seg_tipo'] == '5') && $sub['sub_operador'] == $operador)  {

		echo '		<tr>
			<td style="text-align:center;">'. $lang['PAUSAR'].'<br><input type="image" src="idiomas/' . $idioma . '/imagenes/pausar.png" name="seleccion2" value="2" /></td>
			<td style="text-align:center;">'. $lang['TERMINAR'].'<br><input type="image" src="idiomas/' . $idioma . '/imagenes/terminar.png" name="seleccion7" value="7" /></td></tr>'."\n";
			
	} elseif (($seg['seg_tipo'] == '1' || $seg['seg_tipo'] == '5') && $sub['sub_operador'] != $operador) {
		echo '		<tr>
			<td colspan="2" style="text-align:center;"><h1 style=" color:#f00; font-weight:bold;">'. $lang['TRABAJO CONJUNTO AL OPERADOR'] . $sub['sub_operador'] . '</h1></td></tr>
		<tr>
			<td style="text-align:center;">'. $lang['INICIAR APOYO'].'<br><input type="image" src="idiomas/' . $idioma . '/imagenes/multiop-inicia.png" name="seleccion5" value="5" /></td>
			<td style="text-align:center;">'. $lang['TERMINAR APOYO'].'<br><input type="image" src="idiomas/' . $idioma . '/imagenes/multiop-termina.png" name="seleccion2" value="2" /><input type="hidden" name="multi" value="1"></td>
		</tr>'."\n";

	} elseif ($seg['seg_tipo'] == '2')  {
		echo '		<tr>
			<td style="text-align:center;">'. $lang['CONTINUAR'].'<br><input type="image" src="idiomas/' . $idioma . '/imagenes/continuar.png" name="seleccion5" value="5" /></td></tr>'."\n";
		
		/*} elseif ($seg['seg_tipo'] == '2' && $sub['sub_operador'] != $operador )  {
		echo '		<tr>
			<td colspan="2" style="text-align:center;"><h1 style=" color:#f00; font-weight:bold;">'. $lang['TRABAJO CONJUNTO AL OPERADOR'] . $sub['sub_operador'] . '</h1></td></tr>
		<tr>
			<td style="text-align:center;" colspan="2">'. $lang['TERMINAR APOYO'].'<br><input type="image" src="idiomas/' . $idioma . '/imagenes/multiop-termina.png" name="seleccion2" value="2" /><input type="hidden" name="multi" value="1"></td>
		</tr>'."\n";
		*/
	} elseif ($seg['seg_tipo'] == '7')  {
		echo '		<tr>
			<td style="text-align:center;" colspan="2">'. $lang['TERMINAR'].'<br><input type="image" src="idiomas/' . $idioma . '/imagenes/terminar.png" name="seleccion7" value="7" /></td></tr>'."\n";
			
	}
	
		// ------ Mejora para subir imágenes de avance de reparación.... 
		/*	if($img_avances == '1' && $seguimiento == 1) {
		echo '		<tr><td colspan="2"><hr></td></tr>'."\n";
		echo '		<tr class="cabeza_tabla"><td colspan="2"><span style="font-size:18px; font-weight:bold;">'. $lang['ImagenTareaTerminada'] . ' para ' .constant('NOMBRE_AREA_' . $sub['sub_area']) . ': ' . $sub['sub_descripcion'] . '</span></td></tr>'."\n";
		echo '		<tr><td colspan="2" style="text-align:left;"><input type="file" name="imagen[]" size="30" /></td></tr>'."\n";
		echo '		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>'."\n";
	}		
*/
	if($pausar == 1){

		echo '
			<input type="hidden" name="pausa_suborden" value="' . $opr['sub_orden_id'] . '">'."\n";
	}
	echo '
	<input type="hidden" name="operador" value="' . $operador . '">
	<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '">
	<input type="hidden" name="sub_estatus" value="' . $sub['sub_estatus'] . '">
	<input type="hidden" name="orden_id" value="' . $sub['orden_id'] . '">
	<input type="hidden" name="fecha_terminado" value="' . $sub['sub_fecha_terminado'] . '">
	</table>
	</form>'."\n";
}

elseif($accion==="directo") {

	$funnum = 1130005;

//	$mensaje = isset($mensaje) ? $mensaje : '';

	include('parciales/encabezado.php'); 
	echo '	<div id="body">'."\n";
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">'."\n";
	if($_SESSION['codigo'] < '2000') {
		echo '			<br><form action="seguimiento.php?accion=seguimiento" method="post" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="3" border="0" class="agrega" width="650">'."\n";
		if($mensaje!='') {
			echo '				<tr class="alerta"><td colspan="2"><h2>' . $mensaje . '</h2></td></tr>'."\n";
		}
		echo '				<tr class="cabeza_tabla"><td colspan="2"><h2>'. $lang['Registro de Avances'].'</h2></td></tr>
				<tr><td colspan="2" style="text-align:left;"><h2>'. $lang['Pasa lectora en ta tarea y presiona ENTER'].'</h2></td></tr>
				<tr>
					<td colspan="2" style="text-align:center;">Código: <input type="text" id="codigo" name="codigo" size="10" maxlength="11" /></td>
				</tr>
			</table>
			</form>'."\n";
	}

}

elseif ($accion==="registrar") {

	$funnum = 1130010;

//	echo 'Estamos en la sección registrar';
	$error = 'no'; $mensaje= ''; 
	if ($_SESSION['usuario'] != $operador && $seguimiento == 1) {
		$_SESSION['msjerror'] = $lang['Operador diferente en el código de seguimiento'] . $codigo;
		redirigir('seguimiento.php?accion=seguimiento');
	}

	// --- Pausar tarea ---
	if($pausa_suborden != ''){
		
		// --- Revisar si hay un ayudante trabajando en la tarea ---
		$preg_ayuda = " SELECT usuario FROM " . $dbpfx . "usuarios WHERE sub_orden_id = '" . $pausa_suborden . "' AND usuario != '" . $operador . "' LIMIT 1"; 
		$matriz_ayuda = mysql_query($preg_ayuda) or die("ERROR: Fallo seleccion! " . $preg_ayuda);
		$hay_ayudante = mysql_num_rows($matriz_ayuda);
		
		if($hay_ayudante == 1){ // --- El ayudante se asignara como titular de la tarea ---
			
			$ayudante = mysql_fetch_assoc($matriz_ayuda);
			$sql_data = [
				'sub_operador' => $ayudante['usuario'],
			];
			$param = " sub_orden_id = '" . $pausa_suborden . "' ";
			ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $param);
			
		} else{ // --- Se pausa la taea ---
		
			$sql_data_array = [
				'sub_estatus' => '108',
			];
			$param = " sub_orden_id = '" . $pausa_suborden . "'";
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $param);

			// --- Consultar el área de la tarea que se va pausar ---
			$preg_area = "SELECT sub_area FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $pausa_suborden . "'";
			$matr_area = mysql_query($preg_area) or die("ERROR: Fallo! " . $preg_area);
			$area = mysql_fetch_assoc($matr_area);

		}
		
		// --- Registrar Pausa en seguimiento ---
		$sql_data_array = [
			'usuario' => $operador,
			'sub_orden_id' => $pausa_suborden,
			'seg_tipo' => '2', // --- Pausa ---
			'sub_area' => $area['sub_area']
		];
		ejecutar_db($dbpfx . 'seguimiento', $sql_data_array, 'insertar');
		
	}
	
	if (isset($seleccion1_x)) {
		$usr_estat = array('estatus' => '1'); $seleccion = 1;
	} elseif (isset($seleccion2_x)) {
		$usr_estat = array('estatus' => '0'); $seleccion = 2;
	} elseif (isset($seleccion5_x)) {
		$usr_estat = array('estatus' => '1'); $seleccion = 5;
	} elseif (isset($seleccion7_x)) {
		$usr_estat = array('estatus' => '0'); $seleccion = 7;
	} else {
		$error = "si"; $mensaje = $lang['No hubo selección válida'];
	}

	if($multi != 1) { $multi = 'null'; }
	$estatus = '';

	$num_cols = 0;
	if ($sub_orden_id!='') {
		$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$sub = mysql_fetch_array($matriz);
		$num_cols = mysql_num_rows($matriz);
/*		if ($sub['orden_id'] != $orden_id) {
			$mensaje .= '<p class="alerta">No se encontró o no es válida la orden de trabajo ' . $orden_id . '.</p>'."\n";
			$error = 'si';
		}
*/		$usr_estat['sub_orden_id'] = $sub_orden_id;
	} else {
		$error = "si"; $mensaje =$lang['No se indicó la Tarea'];
	}
	if ($num_cols > 0 && $error == 'no') {
		$mensaje = '';

// ------ Al cerrar o pausar Tareas se deben cerrar las de los ayudantes ------------------

		if(($seleccion == 2 && $multi != 1) || $seleccion == 7) {
			$preg6 = "SELECT usuario FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" . $sub_orden_id . "' GROUP BY usuario";
			$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de operarios para la tarea " . $sub_orden_id . "! " . $preg6);
			while ($oper = mysql_fetch_array($matr6)) {
				$preg5 = "SELECT seg_tipo, seg_opr_apoyo FROM " . $dbpfx . "seguimiento WHERE usuario = '" . $oper['usuario'] . "' AND sub_orden_id = '" . $sub_orden_id . "' ORDER BY seg_id DESC LIMIT 1";
				$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de operarios! " . $preg5);
//				echo $preg5;
				$stipo = mysql_fetch_array($matr5);
				if($stipo['seg_opr_apoyo'] != 1) { $stipo['seg_opr_apoyo'] = 'null'; }
				if($stipo['seg_tipo'] == 1 || $stipo['seg_tipo'] == 5) {
					$sql_data_array = array('usuario' => $oper['usuario'],
						'sub_orden_id' => $sub_orden_id,
						'seg_opr_apoyo' => $stipo['seg_opr_apoyo'],
						'sub_area' => $sub['sub_area']);
					if($stipo['seg_opr_apoyo'] == 1) {
						$sql_data_array['seg_tipo'] = '2';
					} else {
						$sql_data_array['seg_tipo'] = $seleccion;
					}
					ejecutar_db($dbpfx . 'seguimiento', $sql_data_array);
				}
				unset($sql_data_array);
				$parametros = 'usuario = ' . $oper['usuario'];
				ejecutar_db($dbpfx . 'usuarios', $usr_estat, 'actualizar', $parametros);
			}
			if($sub['sub_estatus'] >= '104' && $sub['sub_estatus'] <= '110') {
				if($seleccion == 2) {
					$estatus = 108;
				} else {
					$estatus = 111;
				}
			}
		} else {
			$sql_data_array = array('usuario' => $operador,
				'sub_orden_id' => $sub_orden_id,
				'seg_tipo' => $seleccion,
				'seg_opr_apoyo' => $multi,
				'sub_area' => $sub['sub_area']);
			ejecutar_db($dbpfx . 'seguimiento', $sql_data_array);
			unset($sql_data_array);
			$parametros = 'usuario = ' . $operador;
			ejecutar_db($dbpfx . 'usuarios', $usr_estat, 'actualizar', $parametros);
			if($multi != 1 && $sub['sub_estatus'] >= '104' && $sub['sub_estatus'] <= '110') {
				if($seleccion == 1) { $estatus = 109; }
				if($seleccion == 5) { $estatus = 110; $sql_data_array['sub_operador'] = $operador; }
			}
		}

		$sql_data = array();
		//$sql_data_array = array('sub_operador' => $operador);
		if($seleccion == '1') {
			$sql_data_array['sub_fecha_inicio'] = date('Y-m-d H:i:s');
			$sql_data_array['sub_operador'] = $operador;
			$pregunta2 = "SELECT orden_fecha_proceso_inicio FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
			$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
			$dato = mysql_fetch_array($matriz2);
			if (is_null($dato['orden_fecha_proceso_inicio'])) {
				$sql_data['orden_fecha_proceso_inicio'] = date('Y-m-d H:i:s');
			}
		}

// ------ Calculo de tiempo utilizado en la reparación
		$sql_data_array['sub_horas_empleadas'] = horasEmpleadas($sub_orden_id, $dbpfx);

		if($estatus == '111') {
			$sql_data['orden_ubicacion'] = constant('ZONA_DE_ESPERA');
		} else {
			$sql_data['orden_ubicacion'] = constant('NOMBRE_AREA_' . $sub['sub_area']);
		}

		$parametros = 'orden_id = ' . $orden_id;
		ejecutar_db($dbpfx . 'ordenes', $sql_data, 'actualizar', $parametros);

		if($estatus != '') {
			$sql_data_array['sub_estatus'] = $estatus;
		}

		if(count($sql_data_array) > 0) {
			$parametros = 'sub_orden_id = ' . $sub_orden_id;
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		}

		actualiza_orden($orden_id, $dbpfx);
		redirigir('seguimiento.php?accion=directo');
	} else {
		if($error=='no') {
			redirigir('seguimiento.php?accion=directo&mensaje='. $lang['No se encontró o no es válida la suborden de trabajo '] . $sub_orden_id);
		}
	}
}

elseif($accion==="operador") {

	$funnum = 1130015;

	$pregunta = "SELECT sub_estatus FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$codigo'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección!");
	$sub = mysql_fetch_array($matriz);
	if($sub['sub_estatus']=='111' || $sub['sub_estatus'] == '121' || $sub['sub_estatus'] == '127') {
		if (!isset($_SESSION['usuario'])) {
			redirigir('usuarios.php?mensaje='. $lang['aprobacion de Jefe de Taller']);
		} else {
			redirigir('entrega.php?accion=seguimiento&codigo='.$codigo);
		}
	} elseif($preaut == '1' && ($sub['sub_estatus'] == '112' || $sub['sub_estatus'] >= '130')) {
			$_SESSION['msjerror'] = '';
			redirigir('usuarios.php?mensaje='. $lang['Tarea no esta en proceso']);
	} elseif($preaut != '1' && ($sub['sub_estatus'] < '104' || $sub['sub_estatus'] > '110')) {
			$_SESSION['msjerror'] = '';
			redirigir('usuarios.php?mensaje='. $lang['Tarea no esta en proceso']);
	} // elseif($sub['sub_estatus'] > '111' && $sub['sub_estatus'] < '118') {
//			redirigir('usuarios.php?mensaje='. $lang['Tarea no esta en proceso']);
//	}
	include('parciales/encabezado.php');
	echo '	<div id="body">'."\n";
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">'."\n";
	echo '			<br><form action="seguimiento.php?accion=seguimiento" method="post" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="3" border="0" class="agrega" width="650">'."\n";
	if($mensaje!='') {
		echo '				<tr class="alerta"><td colspan="2">' . $mensaje . '</td></tr>'."\n";
	}
	echo '				<tr class="cabeza_tabla"><td colspan="2"><h2>'. $lang['Número de Operador'].'</h2></td></tr>
				<tr><td colspan="2" style="text-align:left;"><h2>'. $lang['Pasa lectora sobre credencial de Operador'].'</h2></td></tr>
				<tr>
					<td colspan="2" style="text-align:center;">'. $lang['Número: '].'<input type="text" id="codigo" name="numero" size="10" maxlength="11" /><input type="hidden" name="codigo" value="' . $codigo . '" /></td>
				</tr>
			</table>
			</form>'."\n";
}

?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
