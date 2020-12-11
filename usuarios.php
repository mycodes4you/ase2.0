<?php
foreach($_POST as $k => $v){$$k=$v;}  //echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');


/*  ----------------  obtener nombres de usuarios   ------------------- */

	$consulta = "SELECT usuario, nombre, apellidos, comision FROM " . $dbpfx . "usuarios WHERE rol09 = '1' AND acceso = '0' AND activo = '1' ORDER BY nombre";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo selección de usuarios!");
//	$num_provs = mysql_num_rows($arreglo);
	$usu = array();
//	$provs[0] = 'Sin Proveedor';
		while ($usua = mysql_fetch_array($arreglo)) {
			$usu[$usua['usuario']] = array('nom' => $usua['nombre'], 'ape' => $usua['apellidos'], 'com' => $usua['comision']);
		}

/*
if (($accion==='insertar') || ($accion==='actualizar') || ($accion==='ingresar') || ($accion==='clave') || ($accion==='ajustar') || ($accion==='terminar')) { 
	// no cargar encabezado
} else {
	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}
*/

if ($accion==="ingresar") {
	session_start();
	$funnum = 1135000;
	$usuario = limpiarNumero($usuario);
	$usuario = intval($usuario);
	$pregunta = "SELECT * FROM " . $dbpfx . "usuarios WHERE usuario = '$usuario' AND activo = '1'";	
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección!");
	$usr = mysql_fetch_array($matriz);
	$num_cols = mysql_num_rows($matriz);
	if($num_cols == 1) {
		$verificar = md5($clave);
		//$verificar = $clave;
		if ($verificar==$usr['clave']) {
			if($usr['codigo'] >= '2000' && $usr['ensesion'] == '1' && $verifica_ses == '1') {
				redirigir('usuarios.php?mensaje=El Usuario ya tiene una sesión activa!');
			}
			$instan = md5(preg_replace('/[^A-Za-z0-9]/', '', time()));
			session_unset();
			session_destroy();
			session_id($instan.$usr['usuario']);
			session_start();
			//$_SESSION[$dbpfx][$usr['usuario']]= '1';
			$_SESSION['usuario']= $usr['usuario'];
			$_SESSION['puesto']= $usr['puesto'];
			$_SESSION['localidad']= $usr['localidad'];
			$_SESSION['acceso']= $usr['acceso'];
			$_SESSION['codigo']= $usr['codigo'];
			$_SESSION['nombre']= $usr['nombre'];
			$_SESSION['apellidos']= $usr['apellidos'];
			$_SESSION['email']= $usr['email'];
			$_SESSION['aseg'] = $usr['aseg'];
			$_SESSION['prov'] = $usr['prov'];
			$_SESSION['rol01'] = $usr['rol01'];
			$_SESSION['rol02'] = $usr['rol02'];
			$_SESSION['rol03'] = $usr['rol03'];
			$_SESSION['rol04'] = $usr['rol04'];
			$_SESSION['rol05'] = $usr['rol05'];
			$_SESSION['rol06'] = $usr['rol06'];
			$_SESSION['rol07'] = $usr['rol07'];
			$_SESSION['rol08'] = $usr['rol08'];
			$_SESSION['rol09'] = $usr['rol09'];
			$_SESSION['rol10'] = $usr['rol10'];
			$_SESSION['rol11'] = $usr['rol11'];
			$_SESSION['rol12'] = $usr['rol12'];
			$_SESSION['rol13'] = $usr['rol13'];
			$_SESSION['rol14'] = $usr['rol14'];
			$_SESSION['rol17'] = $usr['rol17'];
			touch("../tmp/access-" . session_id());
			$parme = "usuario = '" . $usr['usuario'] ."'";
			$sqdat = ['ensesion' => '1'];
			ejecutar_db($dbpfx . 'usuarios', $sqdat, 'actualizar', $parme);
			unset($sqdat);

// ------ Obtener la IP del usuario ------
/*			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				} else {
					$ip = $_SERVER['REMOTE_ADDR'];
				}
			} */
			
			if (isset($_SERVER["HTTP_CLIENT_IP"])) {
				$ip = $_SERVER["HTTP_CLIENT_IP"];
		} elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
				$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
				$ip = $_SERVER["HTTP_X_FORWARDED"];
		} elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
				$ip = $_SERVER["HTTP_FORWARDED_FOR"];
		} elseif (isset($_SERVER["HTTP_FORWARDED"])) {
				$ip = $_SERVER["HTTP_FORWARDED"];
		} else {
				$ip = $_SERVER["REMOTE_ADDR"];
		}
			bitacora('0', 'El usuario ' . $_SESSION['usuario'] . ' se firmó con la IP: ' . $ip, $dbpfx);
			// ------- Función para cambio de password cada cierto tiempo
			if($fechapassword > '0' && $_SESSION['acceso'] == '0') {
				$dias = intval((strtotime("now") - strtotime( $usr['fecha_password'] )) / 86400);
				if($dias > $fechapassword) {
					$_SESSION['cambio_pass'] = 1;
					$_SESSION['tmpusr'] = $_SESSION['usuario'];
					unset($_SESSION['usuario']);
					$_SESSION['msjerror'] = 'Es necesario hacer cambio de contraseña';
					redirigir('usuarios.php');
				} else {
					$_SESSION['cambio_pass'] = 0;
					redirigir('index.php');
				}
			}
			redirigir('index.php');
		}
	$_SESSION['52f99ab3e0d61cb81a']++;
	$_SESSION['msjerror'] = 'Usuario o clave incorrecto o inactivo - después del 3er intento fallido, se bloquerá el acceso desde esta sesión...';
	redirigir('usuarios.php');
	}
	$_SESSION['52f99ab3e0d61cb81a']++;
	$_SESSION['msjerror'] = 'Usuario o clave incorrecto o inactivo - después del 3er intento fallido, se bloquerá el acceso desde esta sesión...';
	redirigir('usuarios.php');
}


elseif (($accion==="crear") || ($accion==="modificar")) {
	
	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}

	if (validaAcceso('1135005', $dbpfx) == '1') {
		$mensaje = '';
	} else {
		$_SESSION['msjerror'] = 'Acceso sólo para Administradores de Recursos Humanos';
		redirigir('usuarios.php');
	}

	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';


	if($accion==="modificar") {
		$pregunta = "SELECT * FROM " . $dbpfx . "usuarios WHERE usuario = '$usuario'";
	   $matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	   $num_cols = mysql_num_rows($matriz);
	   if ($num_cols > 0) {
	   	$_SESSION['pers'] = mysql_fetch_array($matriz);
		} else {
			$accion='crear';
		}
	}

	if ($accion==="modificar") { $tipo = 'actualizar';}
	else {
		$tipo = 'insertar';
		$_SESSION['pers']['nombre'] = (isset($_SESSION['pers']['nombre'])) ? $_SESSION['pers']['nombre'] : '';
		$_SESSION['pers']['apellidos'] = (isset($_SESSION['pers']['apellidos'])) ? $_SESSION['pers']['apellidos'] : '';
		$_SESSION['pers']['puesto'] = (isset($_SESSION['pers']['puesto'])) ? $_SESSION['pers']['puesto'] : '';
		$_SESSION['pers']['codigo'] = (isset($_SESSION['pers']['codigo'])) ? $_SESSION['pers']['codigo'] : '';
		$_SESSION['pers']['areas'] = (isset($_SESSION['pers']['areas'])) ? $_SESSION['pers']['areas'] : '';
		$_SESSION['pers']['aseg'] = (isset($_SESSION['pers']['aseg'])) ? $_SESSION['pers']['aseg'] : '';
		$_SESSION['pers']['prov'] = (isset($_SESSION['pers']['prov'])) ? $_SESSION['pers']['prov'] : '';
		$_SESSION['pers']['rol01'] = (isset($_SESSION['pers']['rol01'])) ? $_SESSION['pers']['rol01'] : '';
		$_SESSION['pers']['rol02'] = (isset($_SESSION['pers']['rol02'])) ? $_SESSION['pers']['rol02'] : '';
		$_SESSION['pers']['rol03'] = (isset($_SESSION['pers']['rol03'])) ? $_SESSION['pers']['rol03'] : '';
		$_SESSION['pers']['rol04'] = (isset($_SESSION['pers']['rol04'])) ? $_SESSION['pers']['rol04'] : '';
		$_SESSION['pers']['rol05'] = (isset($_SESSION['pers']['rol05'])) ? $_SESSION['pers']['rol05'] : '';
		$_SESSION['pers']['rol06'] = (isset($_SESSION['pers']['rol06'])) ? $_SESSION['pers']['rol06'] : '';
		$_SESSION['pers']['rol07'] = (isset($_SESSION['pers']['rol07'])) ? $_SESSION['pers']['rol07'] : '';
		$_SESSION['pers']['rol08'] = (isset($_SESSION['pers']['rol08'])) ? $_SESSION['pers']['rol08'] : '';
		$_SESSION['pers']['rol09'] = (isset($_SESSION['pers']['rol09'])) ? $_SESSION['pers']['rol09'] : '';
		$_SESSION['pers']['rol10'] = (isset($_SESSION['pers']['rol10'])) ? $_SESSION['pers']['rol10'] : '';
		$_SESSION['pers']['rol11'] = (isset($_SESSION['pers']['rol11'])) ? $_SESSION['pers']['rol11'] : '';
		$_SESSION['pers']['rol12'] = (isset($_SESSION['pers']['rol12'])) ? $_SESSION['pers']['rol12'] : '';
		$_SESSION['pers']['rol13'] = (isset($_SESSION['pers']['rol13'])) ? $_SESSION['pers']['rol13'] : '';
		$_SESSION['pers']['rol14'] = (isset($_SESSION['pers']['rol14'])) ? $_SESSION['pers']['rol14'] : '';
		$_SESSION['pers']['rol15'] = (isset($_SESSION['pers']['rol15'])) ? $_SESSION['pers']['rol15'] : '';
		$_SESSION['pers']['rol16'] = (isset($_SESSION['pers']['rol16'])) ? $_SESSION['pers']['rol16'] : '';
		$_SESSION['pers']['rol17'] = (isset($_SESSION['pers']['rol17'])) ? $_SESSION['pers']['rol17'] : '';
		$_SESSION['pers']['activo'] = (isset($_SESSION['pers']['activo'])) ? $_SESSION['pers']['activo'] : '';
		$_SESSION['pers']['ensesion'] = (isset($_SESSION['pers']['ensesion'])) ? $_SESSION['pers']['ensesion'] : '';
		$_SESSION['pers']['usuario'] = (isset($_SESSION['pers']['usuario'])) ? $_SESSION['pers']['usuario'] : '';
		$_SESSION['pers']['calle_numero'] = (isset($_SESSION['pers']['calle_numero'])) ? $_SESSION['pers']['calle_numero'] : '';
		$_SESSION['pers']['municipio'] = (isset($_SESSION['pers']['municipio'])) ? $_SESSION['pers']['municipio'] : '';
		$_SESSION['pers']['colonia'] = (isset($_SESSION['pers']['colonia'])) ? $_SESSION['pers']['colonia'] : '';
		$_SESSION['pers']['estado'] = (isset($_SESSION['pers']['estado'])) ? $_SESSION['pers']['estado'] : '';
		$_SESSION['pers']['telefono'] = (isset($_SESSION['pers']['telefono'])) ? $_SESSION['pers']['telefono'] : '';
		$_SESSION['pers']['movil'] = (isset($_SESSION['pers']['movil'])) ? $_SESSION['pers']['movil'] : '';
		$_SESSION['pers']['email'] = (isset($_SESSION['pers']['email'])) ? $_SESSION['pers']['email'] : '';
		$_SESSION['pers']['email_personal'] = (isset($_SESSION['pers']['email_laboral'])) ? $_SESSION['pers']['email_personal'] : '';
		$_SESSION['pers']['rfc'] = (isset($_SESSION['pers']['rfc'])) ? $_SESSION['pers']['rfc'] : '';
		$_SESSION['pers']['contrato'] = (isset($_SESSION['pers']['contrato'])) ? $_SESSION['pers']['contrato'] : '';
		$_SESSION['pers']['comision'] = (isset($_SESSION['pers']['comision'])) ? $_SESSION['pers']['comision'] : '';
	}
	echo '	<br>
	<form action="usuarios.php?accion=' . $tipo . '" method="post" enctype="multipart/form-data" autocomplete="off">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td colspan="3"><span class="alerta">' . $_SESSION['pers']['mensaje'] . '</span></td></tr>
		<tr class="cabeza_tabla"><td colspan="3">Datos de Operativos de Usuario</td></tr>
		<tr><td>Nombre</td><td colspan="2" style="text-align:left;"><input type="text" name="nombre" size="60" maxlength="60" value="' . $_SESSION['pers']['nombre'] . '" /></td></tr>
		<tr><td>Apellidos</td><td colspan="2" style="text-align:left;"><input type="text" name="apellidos" size="60" maxlength="60" value="' . $_SESSION['pers']['apellidos'] . '" /></td></tr>
		<tr><td>Puesto</td><td colspan="2" style="text-align:left;"><input type="text" name="puesto" size="60" maxlength="120" value="' . $_SESSION['pers']['puesto'] . '" /></td></tr>
		<tr><td>E-mail laboral</td><td colspan="2" style="text-align:left;"><input type="text" name="email" size="60" maxlength="120" value="' . $_SESSION['pers']['email'] . '" /></td></tr>
		<tr><td>Teléfono laboral</td><td colspan="2" style="text-align:left;"><input type="text" name="telefono_laboral" size="60" maxlength="120" value="' . $_SESSION['pers']['telefono_laboral'] . '" /></td></tr>
		'."\n";
	echo '		<tr><td>Código de Puesto</td><td style="text-align:left;" colspan="2">
			<select name="codigo" size="1">
				<option value=""'; if ($_SESSION['pers']['codigo']=='') {echo ' selected="1"';} echo '>Seleccione...</option>
				<option value="10"'; if ($_SESSION['pers']['codigo']==10) {echo ' selected="1"';} echo '>' . $lang['GERENTE'] . '</option>
				<option value="12"'; if ($_SESSION['pers']['codigo']==12) {echo ' selected="1"';} echo '>' . $lang['ASISTENTE'] . '</option>
				<option value="15"'; if ($_SESSION['pers']['codigo']==15) {echo ' selected="1"';} echo '>' . $lang['JEFE DE TALLER'] . '</option>
				<option value="20"'; if ($_SESSION['pers']['codigo']==20) {echo ' selected="1"';} echo '>' . $lang['VALUADOR'] . '</option>
				<option value="30"'; if ($_SESSION['pers']['codigo']==30) {echo ' selected="1"';} echo '>' . $lang['ASESOR'] . '</option>
				<option value="40"'; if ($_SESSION['pers']['codigo']==40) {echo ' selected="1"';} echo '>' . $lang['JEFE DE AREA'] . '</option>
				<option value="50"'; if ($_SESSION['pers']['codigo']==50) {echo ' selected="1"';} echo '>' . $lang['ALMACEN'] . '</option>
				<option value="60"'; if ($_SESSION['pers']['codigo']==60) {echo ' selected="1"';} echo '>' . $lang['OPERADOR'] . '</option>
				<option value="70"'; if ($_SESSION['pers']['codigo']==70) {echo ' selected="1"';} echo '>' . $lang['AUXILIAR'] . '</option>
				<option value="75"'; if ($_SESSION['pers']['codigo']==75) {echo ' selected="1"';} echo '>' . $lang['VIGILANCIA'] . '</option>
				<option value="80"'; if ($_SESSION['pers']['codigo']==80) {echo ' selected="1"';} echo '>' . $lang['CALIDAD'] . '</option>
				<option value="90"'; if ($_SESSION['pers']['codigo']==90) {echo ' selected="1"';} echo '>' . $lang['COBRANZA'] . '</option>
				<option value="100"'; if ($_SESSION['pers']['codigo']==100) {echo ' selected="1"';} echo '>' . $lang['PAGOS'] . '</option>
				<option value="2000"'; if ($_SESSION['pers']['codigo']==2000) {echo ' selected="1"';} echo '>' . $lang['ASEGURADORA'] . '</option>
			</select></td></tr>'."\n";
	echo '		<tr><td>Roles adicionales para este usuario</td><td style="text-align:left;">';
	echo '		<input type="checkbox" name="rol02" value="1"'; if($_SESSION['pers']['rol02']==1) { echo ' checked="checked"'; } echo ' />' . $lang['GERENTE'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol03" value="1"'; if($_SESSION['pers']['rol03']==1) { echo ' checked="checked"'; } echo ' />' . $lang['ASISTENTE'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol04" value="1"'; if($_SESSION['pers']['rol04']==1) { echo ' checked="checked"'; } echo ' />' . $lang['JEFE DE TALLER'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol05" value="1"'; if($_SESSION['pers']['rol05']==1) { echo ' checked="checked"'; } echo ' />' . $lang['VALUADOR'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol06" value="1"'; if($_SESSION['pers']['rol06']==1) { echo ' checked="checked"'; } echo ' />' . $lang['ASESOR'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol07" value="1"'; if($_SESSION['pers']['rol07']==1) { echo ' checked="checked"'; } echo ' />' . $lang['JEFE DE AREA'] . "\n";
	echo '</td><td style="text-align:left;">'."\n";
	echo '		<input type="checkbox" name="rol08" value="1"'; if($_SESSION['pers']['rol08']==1) { echo ' checked="checked"'; } echo ' />' . $lang['ALMACEN'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol11" value="1"'; if($_SESSION['pers']['rol11']==1) { echo ' checked="checked"'; } echo ' />' . $lang['CALIDAD'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol12" value="1"'; if($_SESSION['pers']['rol12']==1) { echo ' checked="checked"'; } echo ' />' . $lang['COBRANZA'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol13" value="1"'; if($_SESSION['pers']['rol13']==1) { echo ' checked="checked"'; } echo ' />' . $lang['PAGOS'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol09" value="1"'; if($_SESSION['pers']['rol09']==1) { echo ' checked="checked"'; } echo ' />' . $lang['OPERADOR'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol10" value="1"'; if($_SESSION['pers']['rol10']==1) { echo ' checked="checked"'; } echo ' />' . $lang['AUXILIAR'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol15" value="1"'; if($_SESSION['pers']['rol15']==1) { echo ' checked="checked"'; } echo ' />' . $lang['VIGILANCIA'] . '<br>'."\n";




	echo '		<tr><td>Áreas activas para<br>' . SUPERVISOR . ' y ' . OPERADOR . '</td><td style="text-align:left;" colspan="2">'."\n";

	$uarea = explode('|', $_SESSION['pers']['areas']);
	for($i=1;$i<=$num_areas_servicio;$i++){
		echo '		<input type="checkbox" name="area['.$i.']" value="'.$i.'"';
		foreach($uarea as $k) {
			if($k == $i) {
				echo ' checked="checked"';
			}
		}
		echo ' />' . constant('NOMBRE_AREA_'.$i) . '&nbsp;&nbsp;'."\n";
	}
	echo '</td></tr>'."\n";

	echo '</td></tr>'."\n";
	if ($accion==="modificar") {
		echo '		<tr style="background-color:#ADFFA5;"><td>Usuario Activo</td><td style="text-align:left;" colspan="2"><input type="checkbox" name="activo"'; if ($_SESSION['pers']['activo']==1) {echo ' checked="checked"';} echo ' /></td></tr>'."\n";
		echo '		<tr style="background-color:#ADFFA5;"><td>Usuario Conectado</td><td style="text-align:left;" colspan="2"><input type="checkbox" name="ensesion"'; if ($_SESSION['pers']['ensesion']==1) {echo ' checked="checked"';} echo ' /></td></tr>'."\n";
		echo '		<tr style="background-color:#ADFFA5;"><td>Nueva Clave</td><td style="text-align:left;" colspan="2"><input autocomplete="new-password" type="password" name="clavesu01"/><input type="hidden" name="usuario" size="60" maxlength="15" value="' . $usuario . '"/></td></tr>'."\n";
	}

	echo '		<tr class="cabeza_tabla"><td colspan="3">Datos Particulares de Usuario</td></tr>
		<tr><td>Calle y número de Domicilio</td><td colspan="2" style="text-align:left;"><input type="text" name="calle_numero" size="60" maxlength="40" value="' . $_SESSION['pers']['calle_numero'] . '" /></td></tr>
		<tr><td>Colonia</td><td colspan="2" style="text-align:left;"><input type="text" name="colonia" size="60" maxlength="60" value="' . $_SESSION['pers']['colonia'] . '" /></td></tr>
		<tr><td>Delegación o<br>Municipio</td><td colspan="2" style="text-align:left;"><input type="text" name="municipio" size="60" maxlength="60" value="' . $_SESSION['pers']['municipio'] . '" /></td></tr>
		<tr><td>Estado</td><td colspan="2" style="text-align:left;"><input type="text" name="estado" size="60" maxlength="60" value="' . $_SESSION['pers']['estado'] . '" /></td></tr>
		<tr><td>Teléfono</td><td colspan="2" style="text-align:left;"><input type="text" name="telefono" size="60" maxlength="40" value="' . $_SESSION['pers']['telefono'] . '" /></td></tr>
		<tr><td>Celular</td><td colspan="2" style="text-align:left;"><input type="text" name="movil" size="60" maxlength="40" value="' . $_SESSION['pers']['movil'] . '" /></td></tr>
		<tr><td>E-mail personal</td><td colspan="2" style="text-align:left;"><input type="text" name="email_personal" size="60" maxlength="120" value="' . $_SESSION['pers']['email_personal'] . '" /></td></tr>';
	echo '		<tr class="cabeza_tabla"><td colspan="3">Datos fiscales</td></tr>
		<tr><td>RFC</td><td colspan="2" style="text-align:left;"><input type="text" name="rfc" size="60" maxlength="15" value="' . $_SESSION['pers']['rfc'] . '"/></td></tr>
		<tr><td>Contrato</td><td colspan="2" style="text-align:left;"><input type="text" name="contrato" size="60" maxlength="15" value="' . $_SESSION['pers']['contrato'] . '"/></td></tr>
		<tr><td>Comisión</td><td colspan="2" style="text-align:left;"><input type="text" name="comision" size="60" maxlength="15" value="' . $_SESSION['pers']['comision'] . '"/></td></tr>';
	echo '		<tr><td colspan="3" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
	</table>';
echo '</form>';
}

elseif (($accion==='insertar') || ($accion==='actualizar')) {

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}

	if (validaAcceso('1135005', $dbpfx) == '1') {
		$mensaje = '';
		unset($_SESSION['pers']);
		$_SESSION['pers'] = array();
	} else {
		$_SESSION['msjerror'] = 'Acceso sólo para Administradores de Recursos Humanos';
		redirigir('usuarios.php');
	}

	$mensaje= '';
	if(isset($activo)) { $activo = 1;}
	if($codigo == 1) {$rol01 = 1; }
	if($codigo == 10) {$rol02 = 1; }
	if($codigo == 12) {$rol03 = 1; }
	if($codigo == 15) {$rol04 = 1; }
	if($codigo == 20) {$rol05 = 1; }
	if($codigo == 30) {$rol06 = 1; }
	if($codigo == 40) {$rol07 = 1; }
	if($codigo == 50) {$rol08 = 1; }
	if($codigo == 60) {$rol09 = 1; }
	if($codigo == 70) {$rol10 = 1; }
	if($codigo == 75) {$rol15 = 1; }
	if($codigo == 80) {$rol11 = 1; }
	if($codigo == 90) {$rol12 = 1; }
	if($codigo == 100) {$rol13 = 1; }
	if($codigo == 2000) {$rol14 = 1; }

	$cuenta = 0;
	foreach($area as $k => $v) {
		if($cuenta > 0) { $areas .= '|'; }
		$areas .= $v;
		$cuenta++;
	}
	$_SESSION['pers']['areas'] = $areas;
	$_SESSION['pers']['rol01'] = $rol01;
	$_SESSION['pers']['rol02'] = $rol02;
	$_SESSION['pers']['rol03'] = $rol03;
	$_SESSION['pers']['rol04'] = $rol04;
	$_SESSION['pers']['rol05'] = $rol05;
	$_SESSION['pers']['rol06'] = $rol06;
	$_SESSION['pers']['rol07'] = $rol07;
	$_SESSION['pers']['rol08'] = $rol08;
	$_SESSION['pers']['rol09'] = $rol09;
	$_SESSION['pers']['rol010'] = $rol10;
	$_SESSION['pers']['rol011'] = $rol11;
	$_SESSION['pers']['rol012'] = $rol12;
	$_SESSION['pers']['rol013'] = $rol13;
	$_SESSION['pers']['rol014'] = $rol14;
	$_SESSION['pers']['rol015'] = $rol15;
	$_SESSION['pers']['rol016'] = $rol16;
	$_SESSION['pers']['rol017'] = $rol17;
	$_SESSION['pers']['activo'] = $activo;
	$_SESSION['pers']['ensesion'] = $ensesion;
	$_SESSION['pers']['usuario'] = $usuario;
	$_SESSION['pers']['calle_numero'] = $calle_numero;
	$_SESSION['pers']['municipio'] = $municipio;
	$_SESSION['pers']['colonia'] = $colonia;
	$_SESSION['pers']['estado'] = $estado;
	$_SESSION['pers']['telefono'] = $telefono;
	$_SESSION['pers']['movil'] = $movil;
	$_SESSION['pers']['email'] = $email;
	$_SESSION['pers']['rfc'] = $rfc;
	$_SESSION['pers']['contrato'] = $contrato;
	$_SESSION['pers']['comision'] = $comision;
	$nombre = preparar_entrada_bd($nombre); $_SESSION['pers']['nombre'] = $nombre;
	$apellidos = preparar_entrada_bd($apellidos); $_SESSION['pers']['apellidos'] = $apellidos;
	$puesto = preparar_entrada_bd($puesto); $_SESSION['pers']['puesto'] = $puesto;
	$codigo = preparar_entrada_bd($codigo); $_SESSION['pers']['codigo'] = $codigo;
	$aseg = preparar_entrada_bd($aseg); $_SESSION['pers']['aseg'] = $aseg;
	$prov = preparar_entrada_bd($prov); $_SESSION['pers']['prov'] = $prov;
	$calle_numero = preparar_entrada_bd($calle_numero); $_SESSION['pers']['calle_numero'] = $calle_numero;
	$municipio = preparar_entrada_bd($municipio); $_SESSION['pers']['municipio'] = $municipio;
	$colonia = preparar_entrada_bd($colonia); $_SESSION['pers']['colonia'] = $colonia;
	$estado = preparar_entrada_bd($estado); $_SESSION['pers']['estado'] = $estado;
	$telefono = limpiarNumero($telefono); $_SESSION['pers']['telefono'] = $telefono;
	$movil = limpiarNumero($movil); $_SESSION['pers']['movil'] = $movil;
	$email = preparar_entrada_bd($email); $_SESSION['pers']['email'] = $email;
	$rfc = preparar_entrada_bd($rfc); $_SESSION['pers']['rfc'] = $rfc;
	$contrato = preparar_entrada_bd($contrato); $_SESSION['pers']['contrato'] = $contrato;
	$comision = preparar_entrada_bd($comision); $_SESSION['pers']['comision'] = $comision;
	$email_personal = preparar_entrada_bd($email_personal); $_SESSION['pers']['email_personal'] = $email_personal;
	$telefono_laboral = preparar_entrada_bd($telefono_laboral); $_SESSION['pers']['telefono_laboral'] = $telefono_laboral;
	$error = 'no';
	$mensaje= '';
//	echo '<br><br>====== ' . $error . ' <<>> ' . $mensaje . ' ======<br><br>';
//	echo strlen($telefono1);

	if (strlen($nombre) < 3) {$error = 'si'; $mensaje .='El nombre es muy corto: ' . $nombre . '<br>';}
	if (strlen($apellidos) < 3) {$error = 'si'; $mensaje .='El apellido es muy corto: ' . $apellidos . '<br>';}
	if ($codigo < 10) {$error = 'si'; $mensaje .='Por favor seleccione el código de puesto para el usuario: ' . $nombre . ' ' . $apellidos . '<br>';}
//	if (strlen($email) < 7) {$error = 'si'; $mensaje .='La dirección de la cuenta de correo es muy corta: ' . $email . '<br>';}
//	if (strlen($telefono) < 10) {$error = 'si'; $mensaje .='El número debe tener lada y número local: ' . $telefono . '<br>';}
//	if (strlen($rfc) != 13) {$error = 'si'; $mensaje .='El RFC es de 13 posiciones para personas.<br>';}
//	if (strlen($colonia) < 3) {$error = 'si'; $mensaje .='La colonia es muy corta: ' . $colonia . '<br>';}
//	if (strlen($municipio) < 3) {$error = 'si'; $mensaje .='El municipio o delegación es muy corto: ' . $municipio . '<br>';} 

//	echo '<br><br>====== ' . $error . ' ======<br><br>';
	if ($error === 'no') {
		$sql_data_array = array('nombre' => $nombre,
			'apellidos' => $apellidos,
			'puesto' => $puesto,
			'codigo' => $codigo,
//			'aseg' => $aseg,
//			'prov' => $prov,
			'areas' => $areas,
			'calle_numero' => $calle_numero,
			'colonia' => $colonia,
			'municipio' => $municipio,
			'estado' => $estado,
			'telefono' => $telefono,
			'telefono_laboral' => $telefono_laboral,
			'movil' => $movil,
			'email' => $email,
			'email_personal' => $email_personal,
			'rfc' => $rfc,
			'contrato' => $contrato,
			'rol01' => $rol01,
			'rol02' => $rol02,
			'rol03' => $rol03,
			'rol04' => $rol04,
			'rol05' => $rol05,
			'rol06' => $rol06,
			'rol07' => $rol07,
			'rol08' => $rol08,
			'rol09' => $rol09,
			'rol10' => $rol10,
			'rol11' => $rol11,
			'rol12' => $rol12,
			'rol13' => $rol13,
			'rol14' => $rol14,
			'rol15' => $rol15,
			'rol16' => $rol16,
			'rol17' => $rol17,
			'comision' => $comision);
      if ($accion==='insertar') {
      	$parametros='';
	   	$str = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz1234567890";
   		$clave = "";
   		for($i=0;$i<8;$i++) {$clave .= substr($str,rand(0,59),1);}
   		$nueva_clave = md5($clave);
   		$sql_data_array[clave] = $nueva_clave;
			$pregunta = mysql_query( "SHOW TABLE STATUS LIKE '" . $dbpfx . "usuarios'" );
			$res = mysql_fetch_assoc($pregunta);
			$usuario = $res['Auto_increment'] + $basenumusuarios;
			$sql_data_array['usuario'] = $usuario;
      }  else {
      	if ($clavesu01 != '') {$nueva_clave = md5($clavesu01); $sql_data_array['clave'] = $nueva_clave;}
			$sql_data_array['activo'] = $activo;
			$sql_data_array['ensesion'] = $ensesion;
      	$parametros = 'usuario = ' . $usuario;
      }
      ejecutar_db($dbpfx . 'usuarios', $sql_data_array, $accion, $parametros);
      bitacora('0', 'Se acaba de ' . $accion . ' el usuario ' . $usuario, $dbpfx);
		unset($_SESSION['pers']);
		if ($accion==='insertar') {
			$mensaje = 'El nuevo usuario ' . $usuario . ' tiene la clave: ' . $clave;
		} else {
			$mensaje = 'Usuario actualizado.';
		}
		$_SESSION['msjerror'] = $mensaje;
		redirigir('usuarios.php');
	} else {
		$_SESSION['msjerror'] = $mensaje;
		if ($accion==='insertar') {
			redirigir('usuarios.php?accion=crear');
		} else {
			redirigir('usuarios.php?accion=modificar&usuario=' . $usuario);
		}
//     	echo $mensaje;
//     	print_r ($_SESSION['pers']);
	}
}

elseif ($accion==='terminar') {
	
	$funnum = 1135015;
	
	$parme = "usuario = '" . $usr['usuario'] ."'";
	$sqdat = ['ensesion' => '0'];
	ejecutar_db($dbpfx . 'usuarios', $sqdat, 'actualizar', $parme);
	unset($sqdat);

	unlink ("../tmp/access-" . session_id());
	session_unset();
	redirigir('usuarios.php');
}

elseif ($accion==="consultar") {

	$funnum = 1135020;

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}

	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	if ($_SESSION['codigo'] < 60) {
		$mensaje = '';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}

	$nombre=preparar_entrada_bd($nombre);
	$apellidos=preparar_entrada_bd($apellidos);
	$error = 'si'; $num_cols = 0;
	$mensaje= 'Se necesita al menos un dato para buscar.<br>';
	if (($usuario!='') || ($nombre!='') || ($apellidos!='')) {
		$error = 'no'; $mensaje ='No se encontró ningún usuario con los datos proporcionados';
		$pregunta = "SELECT * FROM " . $dbpfx . "usuarios WHERE ";
		if ($usuario) {$pregunta .= "usuario = '$usuario' ";}
		if (($usuario) && ($nombre)) {$pregunta .= "AND nombre LIKE '%" . $nombre . "%' ";}
			elseif ($nombre) {$pregunta .= "nombre LIKE '%" . $nombre . "%' ";}
		if (($nombre) && ($apellidos)) {$pregunta .= "AND apellidos LIKE '%" . $apellidos . "%' ";} 
			elseif ($apellidos) {$pregunta .= "apellidos LIKE '%" . $apellidos . "%' ";}
   	$matriz = mysql_query($pregunta) or die($pregunta);
   	$num_cols = mysql_num_rows($matriz);
   }
   if ($num_cols > 0) {
   	$mensaje ='';
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
		while ($pers = mysql_fetch_array($matriz)) {
			echo '		<tr class="cabeza_tabla"><td colspan="2">Datos de Operativos de Usuario</td></tr>
		<tr><td>Nombre</td><td>' . $pers['nombre'] . '</td></tr>
		<tr><td>Apellidos</td><td>' . $pers['apellidos'] . '</td></tr>
		<tr><td>Número de Usuario</td><td>' . $pers['usuario'] . '</td></tr>
		<tr><td>Puesto</td><td>' . $pers['puesto'] . '</td></tr>
		<tr><td>Código de Puesto</td><td>' . $pers['codigo'] . '</td></tr>
		<tr><td>Activo</td><td>' . $pers['activo'] . '</td></tr>
		<tr><td>Carga programada</td><td>' . $pers['horas_programadas'] . '</td></tr>
		<tr class="cabeza_tabla"><td colspan="2">Datos Particulares de Usuario</td></tr>
		<tr><td>Calle y número de Domicilio</td><td>' . $pers['calle_numero'] . '</td></tr>
		<tr><td>Colonia</td><td>' . $pers['colonia'] . '</td></tr>
		<tr><td>Delegación o<br>Municipio</td><td>' . $pers['municipio'] . '</td></tr>
		<tr><td>Estado</td><td>' . $pers['estado'] . '</td></tr>
		<tr><td>Teléfono</td><td>' . $pers['telefono'] . '</td></tr>
		<tr><td>Celular</td><td>' . $pers['movil'] . '</td></tr>
		<tr><td>E-mail</td><td>' . $pers['email'] . '</td></tr>
		<tr class="cabeza_tabla"><td colspan="2">Datos fiscales</td></tr>
		<tr><td>RFC</td><td>' . $pers['rfc'] . '</td></tr>
		<tr><td>Contrato</td><td>' . $pers['contrato'] . '</td></tr>
		<tr><td>Comisión</td><td>' . $pers['comision'] . '</td></tr>
		<tr><td>Fecha de ingreso</td><td>' . $pers['fecha_alta'] . '</td></tr>
		<tr><td>Fecha de baja</td><td>' . $pers['fecha_baja'] . '</td></tr>'."\n";
		}
		echo '	</table>'."\n";
	}
	echo $mensaje;
}

elseif ($accion==="clave") {

	$funnum = 1135025;

	if (!isset($usuario)) {
		redirigir('usuarios.php');
	}

	if($clave1!=$clave2) {
		$_SESSION['msjerror'] .= 'La nueva clave y su repetición no coinciden - Intenta de nuevo.<br>';
	}
	if($rto3ye === $clave1) {
		$_SESSION['msjerror'] .= 'La nueva clave y la clave actual no pueden ser iguales - Intenta de nuevo.<br>';
	}
	if(strlen($clave1) < 8) {
		$_SESSION['msjerror'] .= 'La clave debe tener al menos 8 caracteres.<br>';
	}
	if (!preg_match('`[a-z]`',$clave1)) {
		$_SESSION['msjerror'] .= 'La clave debe tener al menos una letra minúscula - Intenta de nuevo.<br>';
	}
	if (!preg_match('`[A-Z]`',$clave1)) {
		$_SESSION['msjerror'] .= 'La clave debe tener al menos una letra mayúscula - Intenta de nuevo.<br>';
	}
	if (!preg_match('`[0-9]`',$clave1)) {
		$_SESSION['msjerror'] .= 'La clave debe tener al menos un número - Intenta de nuevo.<br>';
	}
	if($_SESSION['msjerror'] != '') {
		redirigir('usuarios.php');
	}

	$pregunta = "SELECT usuario, clave FROM " . $dbpfx . "usuarios WHERE usuario = '$usuario'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	$pers = mysql_fetch_array($matriz);
	$num_cols = mysql_num_rows($matriz);
	if($num_cols > 0) {
		$verificar = md5($rto3ye);
		//$verificar = $clave;
		$nueva_clave = md5($clave1);
		if($verificar==$pers['clave']) {
			$sql_data_array = array(
				'clave' => $nueva_clave,
				'fecha_password' => date('Y-m-d H:i:s')
			);
			$parametros = 'usuario = ' . $usuario;
			ejecutar_db($dbpfx . 'usuarios', $sql_data_array, 'actualizar', $parametros);
			bitacora('0', 'El usuario ' . $usuario . ' acaba de cambiar su clave de acceso.', $dbpfx);
			session_unset();
			$_SESSION['msjerror'] = 'Clave actualizada - Por favor ingresa con tu nueva clave.';
			redirigir('usuarios.php');
		}
		$_SESSION['msjerror'] = 'La clave actual o el usuario NO es correcto - Intenta de nuevo.';
		redirigir('usuarios.php');
	}
	$_SESSION['msjerror'] = 'El usuario o la clave actual NO es correcta - Intenta de nuevo.';
	redirigir('usuarios.php');
}

elseif ($accion==="alertas") {
	
	$funnum = 1135030;

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}
	
	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	if ($_SESSION['codigo'] <= 15) {
		$mensaje = '';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}

	$pregunta = "SELECT * FROM " . $dbpfx . "alertas ORDER BY al_categoria, al_sort";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	echo '	<br>
	<form action="usuarios.php?accion=ajustar" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="3" border="0">
		<tr class="cabeza_tabla"><td colspan="4">Tiempos de alerta <span style="color: #f00;">en HORAS</span> para cada Estatus del Proceso</td></tr>
		<tr><td class="der">Estatus</td><td class="cen">Alerta Preventiva</td><td class="cen">Alerta Crítica</td><td class="cen">Categoría</td></tr>
		';
	$j=0;
	while($alerta = mysql_fetch_array($matriz)) {
		echo '		<tr>
			<td class="der"><input type="hidden" name="al_id[' . $j . ']" value="' . $alerta['al_id'] . '" />' . constant('ORDEN_ESTATUS_' . $alerta['al_estatus']) . '</td>
			<td class="cen"><input type="text" name="preventivo[' . $j . ']" value="' . $alerta['al_preventivo'] . '" size="6" /></td>
			<td class="cen"><input type="text" name="critico[' . $j . ']" value="' . $alerta['al_critico'] . '" size="6" /></td>
			<td class="cen">' . constant('CATEGORIA_DE_REPARACION_' . $alerta['al_categoria']) . '</td>
		</tr>'."\n";
		$j++;
	}
	echo '<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
	</table>';
echo '</form>';	
}

elseif ($accion==='ajustar') {
	
	$funnum = 1135030;
	
//	echo 'Estamos en la sección inserta.<br>';

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}	

	if (($_SESSION['rol04']==1) || ($_SESSION['codigo'] <= 15)) {
		$mensaje = 'Continuar';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}
	$error = 'no';
   if (($error === 'no') && (is_array($al_id))) {
		for($i=0;$i<count($al_id);$i++) {
			$sql_data_array = array('al_preventivo' => $preventivo[$i],
				'al_critico' => $critico[$i]);
			$parametros = 'al_id = ' . $al_id[$i];
			ejecutar_db($dbpfx . 'alertas', $sql_data_array, 'actualizar', $parametros);
		}
		redirigir('usuarios.php?mensaje=Tiempo de alertas ajustado');
	} else {
		include('idiomas/' . $idioma . '/personas.php');
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		echo $mensaje;
	}
}

elseif ($accion==='permisos') {

//permisos y funciones INICIO
	
	$funnum = 1135005;
	
//	echo 'Estamos en la sección inserta.<br>';

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}	

	if (($_SESSION['rol04']==1) || ($_SESSION['codigo'] <= 15)) {
//	Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}

	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
   
	$usuario = limpiarNumero($usuario);
	$usuario = intval($usuario);
   $pregunta = "SELECT * FROM " . $dbpfx . "usuarios WHERE usuario ='$usuario'";
  	$matriz = mysql_query($pregunta) or die($pregunta);
  	$num_cols = mysql_num_rows($matriz);

   if ($num_cols > 0) {
   	$mensaje ='';
	
		echo '		<form action="permisos.php" method="POST">' . "\n";
		echo '		<table cellpadding="0" cellspacing="0" border="0" class="izq">' . "\n";
		while ($pers = mysql_fetch_array($matriz)) {
			echo '			<tr class="cabeza_tabla"><td colspan="3">Permisos Adicionales para: ';
			echo $pers['nombre'] . ' ' . $pers['apellidos'] .'<b> ('. $pers['puesto'] .')</b></td></tr>' . "\n";

			$user=$usuario;
			$getPermisos = mysql_query("SELECT * FROM ". $dbpfx ."usr_permisos WHERE usuario = '$user' AND activo = '1'");
			while($row = mysql_fetch_array($getPermisos)) {
				$perms[$row['num_funcion']] = 1;
			
			}

			foreach($funciones as $padre => $hijos) {
				echo '				<tr class="cabeza_tabla"><td colspan="3">' . $lang[$padre] . '</td></tr>' . "\n";
				$cuantos = count($hijos);
				$filas = intval($cuantos / 3);
				$col = 0; $fila = 0;
				foreach($hijos as $funcion => $descx) {
					if($col == 0) { echo '				<tr>' . "\n"; }
					echo '					<td><input type="checkbox" id="' . $funcion . '" name="fun[' . $funcion . ']" value="1"'; if ($perms[$funcion] == 1) { echo ' checked="checked" '; } echo '><label for="' . $funcion . '">' . $descx . '</td>' . "\n";
					$col++; $cuantos--;
					if($col == 3) {
						$col = 0;
						echo '				</tr>' . "\n";
					}
					if($cuantos == 0 && $col > 0) {
						echo '					<td colspan="' . (3 - $col) . '"></td></tr>' . "\n";
					}
				}

			}


		echo '				<tr><td colspan="3" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>' . "\n";
		echo '			</table>' . "\n";	
		echo '			</form>' . "\n";

		}
	}
}
//permisos y funciones FIN




elseif ($accion==="listar") {
	
	$funnum = 1135020;
//	echo 'Estamos en la sección listar. Cliente: ' . $cliente_id . ' Vehiculo: ' . $vehiculo_id;

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}

	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	if ($_SESSION['codigo'] < 60) {
		$mensaje = '';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}
	
	$error = 'no'; $mensaje ='';
	$pregunta = "SELECT usuario, nombre, apellidos, puesto, codigo FROM " . $dbpfx . "usuarios WHERE acceso = '0' AND activo = '1' ORDER BY nombre";
   $matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	$num_cols = mysql_num_rows($matriz);
	if ($num_cols>0) {
		echo '	<table cellspacing="2" cellpadding="2" border="0" class="izquierda">
		<tr class="cabeza_tabla"><td colspan="4" align="left">Lista de Usuarios</td></tr>
		<tr><td>Usuario</td><td>Nombre</td><td>Puesto</td><td>Grupo</td></tr>';
		$j=0;
		while ($usr = mysql_fetch_array($matriz)) {
			echo '		<tr'; if($j==0) { echo ' class="claro" >';} else { echo ' class="obscuro" >';}
			echo '			<td><a href="usuarios.php?accion=modificar&usuario=' . $usr['usuario'] . '">' . $usr['usuario'] . '</a></td>
			<td>' . $usr['nombre'] . ' ' . $usr['apellidos'] . '</td>
			<td>' . $usr['puesto'] . '</td>
			<td>' . $codigos[$usr['codigo']] . '</td>
		</tr>';
			$j++;
			if($j==2) {$j=0;}
		}
		echo '	</table>';
	} else {
		$mensaje ='No se encontraron usuarios con esos datos.</br>';
	}
}

else {

	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">'."\n";
	include('parciales/menu_inicio.php');
	
	echo '		<div id="principal">'."\n";

	$funnum = 1135045;

	if ($_SESSION['codigo'] >= 1) {
		if (isset($mensaje)) { echo '			<p class="alerta">' . $mensaje .  '</p>'."\n"; }
		if($_SESSION['cambio_pass'] == 1) {
			echo '			<table cellpadding="5" cellspacing="0" border="0" width="80%">
				<tr>
					<td valign="top" width="33%">
						<div class="obscuro espacio">
							<h3>Cambiar Clave, ya han pasado más de ' . $fechapassword . ' días desde que se renovó la clave.</h3>
							<p>La clave debe de contener:</p>
							<ul>
								<li><strong>Un mínimo de ocho caracteres.</strong></li>
								<li><strong>Una minúscula.</strong></li>
								<li><strong>Una mayúscula.</strong></li>
								<li><strong>Un número.</strong></li>
								<li><strong>Opcionalmente alguno de estos: . - + & _ $ % </strong></li>
							</ul>
							<form action="usuarios.php?accion=clave" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Clave actual: </td><td><input type="password" autocomplete="cualquier-cosa" name="rto3ye" size="10" maxlength="20" /></td></tr>
									<tr><td>Clave nueva: </td><td><input type="password" name="clave1" size="10" maxlength="20" /></td></tr>
									<tr><td>Repetir clave nueva: </td><td><input type="password" name="clave2" size="10" maxlength="20" />
									<input type="hidden" name="usuario" value="' . $_SESSION['tmpusr'] . '" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>
								</table>
							</form>
						</div>
					</td>
				</tr>
			</table>'."\n";
		} else {
			echo '			<table cellpadding="5" cellspacing="0" border="0" width="80%">
				<tr>
					<td valign="top" width="33%">
						<div class="obscuro espacio">
							<h3>Cambiar Clave</h3>
							<p>La clave debe de contener:</p>
							<ul>
								<li><strong>Un mínimo de ocho caracteres.</strong></li>
								<li><strong>Una minúscula.</strong></li>
								<li><strong>Una mayúscula.</strong></li>
								<li><strong>Un número.</strong></li>
								<li><strong>Opcionalmente alguno de estos: . - + & _ $ % </strong></li>
							</ul>
							<form action="usuarios.php?accion=clave" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Clave actual: </td><td><input type="password" name="rto3ye" autocomplete="cualquier-cosa" size="10" maxlength="20" /></td></tr>
									<tr><td>Clave nueva: </td><td><input type="password" name="clave1" size="10" maxlength="20" /></td></tr>
									<tr><td>Repetir clave nueva: </td><td><input type="password" name="clave2" size="10" maxlength="20" />
									<input type="hidden" name="usuario" value="' . $_SESSION['usuario'] . '" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>
								</table>
							</form>
						</div>'."\n";
	
		$funnum = 1135050;
// ----------> Validar acceso a cálculo de Destajos		 	$funnum = 1135050;
		$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);

		if ($_SESSION['codigo'] <= 12 || $retorno == '1' || $_SESSION['rol01'] == 1 || $_SESSION['rol02'] == 1 || $_SESSION['rol03'] == 1) {
			echo '						<div class="obscuro espacio">
							<h3>Destajos</h3>
							<form action="destajos.php?accion=gestionar" method="post">
								Por OT especifica: <input type="text" name="orden_ver" size="6" />&nbsp;<input type="submit" name="usuarios" value="Enviar" />
							</form><br>
							<a href="destajos.php?accion=generar"><img src="idiomas/' . $idioma . '/imagenes/destajos-listar.png" alt="Listado de Destajos por Calcular" title="Listado de Destajos por Calcular" ></a>
							<a href="comisiones.php?accion=consultar"><img src="idiomas/' . $idioma . '/imagenes/comisiones_h.png" alt="Listado de Comisiones" title="Listado de Comisiones" ></a>
							<a href="comisiones.php?accion=generar"><img src="idiomas/' . $idioma . '/imagenes/comisionesXcalcular_h.png" alt="Generar recibos de Comisiones" title="Generar recibos de Comisiones" ></a>
						</div>'."\n";
			echo '						<div class="obscuro espacio">
							<h3>Recibos de pago</h3>
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Operador: </td><td>
										<form action="recibosrh.php?accion=listar" method="post">
											<select name="operador">
												<option value="Seleccione">Seleccione</option>'."\n";
			foreach($usu as $n => $v) {
				echo '												<option value="' . $n . '">' . $v['nom'] . ' ' . $v['ape'] . '</option>'."\n";
			}
			echo '											</select><br>
											<input type="submit" value="Enviar" />
										</form>
									</td></tr>
									<tr><td>Listado de<br>Pendientes: </td><td>
										<form action="recibosrh.php?accion=listar" method="post">
											<input type="submit" value="Enviar" />
										</form>
									</td></tr>
									<tr><td>Recibo: </td><td>
										<form action="recibosrh.php?accion=consultar" method="post">
											<input type="text" name="recibo_id" size="6" />&nbsp;<input type="submit" value="Enviar" />
										</form>
									</td></tr>
								</table>
						</div>'."\n";
		}		
		echo '					</td>';

		$funnum = 1135005;
		$retorno = validaAcceso($funnum, $dbpfx);
		if ($retorno == 1 || $_SESSION['codigo'] <= 15 || $_SESSION['rol01'] == 1 || $_SESSION['rol02'] == 1 || $_SESSION['rol03'] == 1 || $_SESSION['rol04'] == 1) {
			echo '					<td valign="top" width="34%">
						<div class="obscuro espacio">
						<table>
						<tr>
						<th width="50%" align="center"><h3>Crear Nuevo Usuario</h3></th>
						<th width="50%" align="center"><h3>Listar <br>Usuarios</h3></th>
						</tr>
						<tr>
						<td width="50%" align="center"><a href="usuarios.php?accion=crear"><img src="idiomas/' . $idioma . '/imagenes/nuevo-usuario.png" alt="Agregar Nuevo usuario" title="Agregar Nuevo usuario"></a></td>
						<td width="50%" align="center"><a href="usuarios.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/consultar.png" alt="Listar usuarios" title="Listar usuarios"></a></td>
						</tr>
						</table>
						</div>

<!--
						<div class="obscuro espacio">
							<h3>Modificar Permisos de Usuario</h3>
							<form action="usuarios.php?accion=permisos" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Número de Usuario: </td><td><input type="text" name="usuario" size="10" maxlength="11" /></td></tr>
								<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>
								</table>
							</form>
						</div>
-->

						<div class="obscuro espacio">
							<h3>Consultar Datos de Usuario</h3>
							<form action="usuarios.php?accion=consultar" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Número de Usuario: </td><td><input type="text" name="usuario" size="10" maxlength="11" /></td></tr>
									<tr><td>Nombre: </td><td><input type="text" name="nombre" size="15" maxlength="20" /></td></tr>
									<tr><td>Apellidos: </td><td><input type="text" name="apellidos" size="15" maxlength="30" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>
								</table>
							</form>
						</div>
						<div class="obscuro espacio">
							<h3>Modificar Datos de Usuario</h3>
							<form action="usuarios.php?accion=modificar" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Número de Usuario: </td><td><input type="text" name="usuario" size="10" maxlength="11" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>
								</table>
							</form>
						</div>
					</td>'."\n";
			}


			echo '					<td valign="top" width="33%">'."\n";
			if (validaAcceso('1135030', $dbpfx) == 1 || $_SESSION['codigo'] <= 15 || $_SESSION['rol01'] == 1 || $_SESSION['rol02'] == 1 || $_SESSION['rol03'] == 1 || $_SESSION['rol04'] == 1) {
				echo '						<div class="obscuro espacio">
							<h3>Ajustar Tiempos de Alertas</h3>
							<a href="usuarios.php?accion=alertas"><img src="idiomas/' . $idioma . '/imagenes/tiempos.png" alt="Modificar Tiempos de Alertas" title="Modificar Tiempos de Alertas"></a></div>'."\n";
			}

// ------ Codificación de Acceso a Sitio Web de Capacitación.

			if($_SESSION['codigo'] < '2000' && $_SESSION['codigo'] != 60 && $_SESSION['codigo'] != 70 && $_SESSION['codigo'] != 75) {
				$pregunta = "SELECT clave FROM " . $dbpfx . "usuarios WHERE usuario = '" . $_SESSION['usuario'] . "' AND activo = '1'";
				$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección! " . $pregunta);
  				$usr = mysql_fetch_array($matriz);

				$principio = substr($usr['clave'], 0, '13');
				$medio = substr($usr['clave'], 13, '4');
				$final = substr($usr['clave'], 17);
				$cfx = $final . $medio. $principio;
				$usuver =  md5($dbpfx . $_SESSION['usuario']);
				$principio = substr($usuver, 0, '13');
				$medio = substr($usuver, 13, '4');
				$final = substr($usuver, 17);
				$usuver = $final . $medio. $principio;
				$cfx = $usuver . $cfx;

/*			$tens = strlen($cfx);
			$prin2 = substr($cfx, -13);
			$med2 = substr($cfx, -17, '4');
			$fin2 = substr($cfx, 32, 15);
			$ensamblada = $prin2 . $med2 . $fin2; */

				echo '						<div class="obscuro espacio">'."\n";
//			echo '		Clave: ' . $usr['clave'] . '<br>Tamaño: ' . $tens . '<br>Principio:  ' . $principio . '<br>Medio:  ' . $medio . '<br>y Final:  ' . $final . '<br>Cfx:  ' . $cfx . '<br>Princio 2:  ' . $prin2 . '<br>Medio 2:  ' . $med2 . '<br>Filan 2:  ' . $fin2 . '<br>Resamble:  ' . $ensamblada . '<br>'."\n";
				echo '							<h3>Capacitación de Usuarios</h3>
							<form action="https://vaicop.com/index.php" method="post" target="_blank">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td colspan="2" ><img src="idiomas/' . $idioma . '/imagenes/video-tutoriales_h.png" alt="Video Tutoriales" title="Video Tutoriales"></td></tr>
									<tr><td colspan="2" style="text-align:left;">
										<input type="hidden" name="inspfxrk" value="' . $dbpfx . '" />
										<input type="hidden" name="prn3rhy2" value="' . $cfx . '" />
										<input type="submit" value="Video Tutoriales" />
									</td></tr>
								</table>
							</form>
						</div>'."\n";
			}
// ------ Módulo de boletines --------
			echo '						
						<div class="obscuro espacio">
							<h3>Boletín Interno</h3>'."\n";
			
			// ------ Acceso a gestión de boletines ------
			if (validaAcceso('1170100', $dbpfx) == 1){
				echo '						
							<a href="boletines.php?accion=gestionar"><button type="button" class="btn btn-primary">GESTIONAR</button></a>
							<br>'."\n";
			}
			echo '
							<br>
							<a href="boletines.php?accion=listar"><button type="button" class="btn btn-primary">MIS BOLETINES</button></a>
							</div>
						'."\n";
			echo '					</td>'."\n";
		echo '				</tr></table>'."\n";
		}
	} else {
		
		$funnum = 1135060;
		
		echo '			<table cellpadding="0" cellspacing="0" border="0" width="80%">
				<tr>
					<td>
						<div>
							<h3>Ingresar</h3>'."\n";
		if (isset($mensaje)) { echo '							<p class="alerta">' . $mensaje .  '</p>'."\n"; } else { echo '							<p>Accede con tu usuario y clave</p>'."\n"; }
		echo '							<form action="usuarios.php?accion=ingresar" method="post">
								<table cellpadding="0" cellspacing="0" border="0">'."\n";
		if($_SESSION['52f99ab3e0d61cb81a'] >= 3) {
			echo '									<tr><td colspan="2"><h3>Acceso Suspendido</h3></td></tr>'."\n";
		} else {
			echo '									<tr><td>Usuario: </td><td><input type="text" id="codigo" name="usuario" size="10" maxlength="11" /></td></tr>
									<tr><td>Clave: </td><td><input type="password" name="clave" size="20" maxlength="20" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>'."\n";
		}
		echo '								</table>
							</form>
						</div>
					</td>
				</tr>
			</table>'."\n";
	}
}
			
echo '		</div>
	</div>'."\n";



include('parciales/pie.php');
/* Archivo usuarios.php */
/* AutoShop-Easy.com */
