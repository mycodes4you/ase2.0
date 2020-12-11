<?php 
include('parciales/funciones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

include('idiomas/' . $idioma . '/aseguradoras.php');

/*  ----------------  obtener nombres de aseguradoras   ------------------- */
	
		$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_nic, aseguradora_alta, aseguradora_v_email, aseguradora_razon_social, prov_def, prov_dde FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		while ($aseg = mysql_fetch_array($arreglo)) {
			$ase[$aseg['aseguradora_id']]['logo'] = $aseg['aseguradora_logo'];
			$ase[$aseg['aseguradora_id']]['nic'] = $aseg['aseguradora_nic'];
			$asenoti[$aseg['aseguradora_id']]['alta'] = $aseg['aseguradora_alta'];
			$asenoti[$aseg['aseguradora_id']]['email'] = $aseg['aseguradora_v_email'];
			$asenoti[$aseg['aseguradora_id']]['razon'] = $aseg['aseguradora_razon_social'];
		}
		$ase[0]['logo'] = 'imagenes/logo-particular.png';
		$ase[0]['nic'] = 'Particular';
/*  ----------------  nombres de aseguradoras   ------------------- */



if (($accion==="crear") || ($accion==="modificar")) {

	if(validaAcceso('1005000', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1'))) {
		$mensaje = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje=' . $lang['Acceso NO autorizado ingresar Usuario y Clave correcta']);
	}

	if($accion==="modificar") {
		$pregunta = "SELECT * FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '$aseguradora_id'";
	   $matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de Aseguradoras!");
	   $filas = mysql_num_rows($matriz);
	   if ($filas > 0) {
	   	$aseg = mysql_fetch_array($matriz);
		} else {
			$accion='crear';
		}
	}

	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	echo '		<form action="aseguradoras.php?accion='; if ($accion==="modificar") { echo 'actualizar';} else {echo 'insertar';} echo '" method="post" enctype="multipart/form-data">
		<table cellpad;ding="0" cellspacing="0" border="0" class="agrega">';
	if($_SESSION['aseg']['mensaje'] != '') {
		echo '		<tr><td colspan="2"><span class="alerta">' . $_SESSION['aseg']['mensaje'] . '</span></td></tr>';
	}
	echo '		<tr class="cabeza_tabla"><td colspan="2">' .$lang['Datos de la Aseguradora'].'</td></tr>
		<tr><td>' .$lang['Razón Social'].'</td><td><input type="text" name="nombre" size="60" maxlength="255" value="'; echo ($aseg['aseguradora_razon_social'] != '') ? $aseg['aseguradora_razon_social'] : $_SESSION['aseg']['nombre']; echo '" /></td></tr>
		<tr><td>' .$lang['NIC'].'</td><td style="text-align: left;"><input type="text" name="nic" size="32" maxlength="32" value="'; echo ($aseg['aseguradora_nic'] != '') ? $aseg['aseguradora_nic'] : $_SESSION['aseg']['nic']; echo '" /></td></tr>
		<tr><td>' .$lang['Calle'].'</td><td><input type="text" name="calle" size="60" maxlength="120" value="'; echo ($aseg['aseguradora_calle'] != '') ? $aseg['aseguradora_calle'] : $_SESSION['aseg']['calle']; echo '" /></td></tr>
		<tr><td>' .$lang['Num Ext'].'</td><td style="text-align:left;"><input type="text" name="numext" size="15" maxlength=15" value="'; echo ($aseg['aseguradora_ext'] != '') ? $aseg['aseguradora_ext'] : $_SESSION['aseg']['numext']; echo '" /></td></tr>
		<tr><td>' .$lang['Num Int'].'</td><td style="text-align:left;"><input type="text" name="numint" size="15" maxlength="15" value="'; echo ($aseg['aseguradora_int'] != '') ? $aseg['aseguradora_int'] : $_SESSION['aseg']['numint']; echo '" /></td></tr>
		<tr><td>' .$lang['Colonia'].'</td><td><input type="text" name="colonia" size="60" maxlength="60" value="'; echo ($aseg['aseguradora_colonia'] != '') ? $aseg['aseguradora_colonia'] : $_SESSION['aseg']['colonia']; echo '" /></td></tr>
		<tr><td>' .$lang['CP'].'</td><td style="text-align:left;"><input type="text" name="cp" size="7" maxlength="5" value="'; echo ($aseg['aseguradora_cp'] != '') ? $aseg['aseguradora_cp'] : $_SESSION['aseg']['cp']; echo '" /></td></tr>
		<tr><td>' .$lang['Municipio Delegación'].'</td><td><input type="text" name="municipio" size="60" maxlength="60" value="'; echo ($aseg['aseguradora_municipio'] != '') ? $aseg['aseguradora_municipio'] : $_SESSION['aseg']['municipio']; echo '" /></td></tr>
		<tr><td>' .$lang['Estado'].'</td><td style="text-align:left;">
			<select name="estado" size="1">
				<option value="" '; if ($aseg['aseguradora_estado']=='') {echo 'selected="1"';} echo '>' .$lang['Seleccione...'].'</option>
				<option value="Aguascalientes" '; if ($aseg['aseguradora_estado']=='Aguascalientes' || $_SESSION['aseg']['estado']=='Aguascalientes') {echo 'selected="1"';} echo '>Aguascalientes</option>
				<option value="Baja California Norte" '; if ($aseg['aseguradora_estado']=='Baja California Norte' || $_SESSION['aseg']['estado']=='Baja California Norte') {echo 'selected="1"';} echo '>Baja California Norte</option>
				<option value="Baja California Sur" '; if ($aseg['aseguradora_estado']=='Baja California Sur' || $_SESSION['aseg']['estado']=='Baja California Sur') {echo 'selected="1"';} echo '>Baja California Sur</option>
				<option value="Campeche" '; if ($aseg['aseguradora_estado']=='Campeche' || $_SESSION['aseg']['estado']=='Campeche') {echo 'selected="1"';} echo '>Campeche</option>
				<option value="Chiapas" '; if ($aseg['aseguradora_estado']=='Chiapas' || $_SESSION['aseg']['estado']=='Chiapas') {echo 'selected="1"';} echo '>Chiapas</option>
				<option value="Chihuahua" '; if ($aseg['aseguradora_estado']=='Chihuahua' || $_SESSION['aseg']['estado']=='Chihuahua') {echo 'selected="1"';} echo '>Chihuahua</option>
				<option value="Coahuila" '; if ($aseg['aseguradora_estado']=='Coahuila' || $_SESSION['aseg']['estado']=='Coahuila') {echo 'selected="1"';} echo '>Coahuila</option>
				<option value="Colima" '; if ($aseg['aseguradora_estado']=='Colima' || $_SESSION['aseg']['estado']=='Colima') {echo 'selected="1"';} echo '>Colima</option>
				<option value="Ciudad de México" '; if ($aseg['aseguradora_estado']=='Ciudad de México' || $_SESSION['aseg']['estado']=='Ciudad de México') {echo 'selected="1"';} echo '>Ciudad de México</option>
				<option value="Durango" '; if ($aseg['aseguradora_estado']=='Durango' || $_SESSION['aseg']['estado']=='Durango') {echo 'selected="1"';} echo '>Durango</option>
				<option value="Estado de México" '; if ($aseg['aseguradora_estado']=='Estado de México' || $_SESSION['aseg']['estado']=='Estado de México') {echo 'selected="1"';} echo '>Estado de México</option>
				<option value="Guerrero" '; if ($aseg['aseguradora_estado']=='Guerrero' || $_SESSION['aseg']['estado']=='Guerrero') {echo 'selected="1"';} echo '>Guerrero</option>
				<option value="Guanajuato" '; if ($aseg['aseguradora_estado']=='Guanajuato' || $_SESSION['aseg']['estado']=='Guanajuato') {echo 'selected="1"';} echo '>Guanajuato</option>
				<option value="Hidalgo" '; if ($aseg['aseguradora_estado']=='Hidalgo' || $_SESSION['aseg']['estado']=='Hidalgo') {echo 'selected="1"';} echo '>Hidalgo</option>
				<option value="Jalisco" '; if ($aseg['aseguradora_estado']=='Jalisco' || $_SESSION['aseg']['estado']=='Jalisco') {echo 'selected="1"';} echo '>Jalisco</option>
				<option value="Michoacán" '; if ($aseg['aseguradora_estado']=='Michoacán' || $_SESSION['aseg']['estado']=='Michoacán') {echo 'selected="1"';} echo '>Michoacán</option>
				<option value="Morelos" '; if ($aseg['aseguradora_estado']=='Morelos' || $_SESSION['aseg']['estado']=='Morelos') {echo 'selected="1"';} echo '>Morelos</option>
				<option value="Nayarit" '; if ($aseg['aseguradora_estado']=='Nayarit' || $_SESSION['aseg']['estado']=='Nayarit') {echo 'selected="1"';} echo '>Nayarit</option>
				<option value="Nuevo León" '; if ($aseg['aseguradora_estado']=='Nuevo León' || $_SESSION['aseg']['estado']=='Nuevo León') {echo 'selected="1"';} echo '>Nuevo León</option>
				<option value="Oaxaca" '; if ($aseg['aseguradora_estado']=='Oaxaca' || $_SESSION['aseg']['estado']=='Oaxaca') {echo 'selected="1"';} echo '>Oaxaca</option>
				<option value="Puebla" '; if ($aseg['aseguradora_estado']=='Puebla' || $_SESSION['aseg']['estado']=='Puebla') {echo 'selected="1"';} echo '>Puebla</option>
				<option value="Querétaro" '; if ($aseg['aseguradora_estado']=='Querétaro' || $_SESSION['aseg']['estado']=='Querétaro') {echo 'selected="1"';} echo '>Querétaro</option>
				<option value="Quintana Roo" '; if ($aseg['aseguradora_estado']=='Quintana Roo' || $_SESSION['aseg']['estado']=='Quintana Roo') {echo 'selected="1"';} echo '>Quintana Roo</option>
				<option value="San Luís Potosí" '; if ($aseg['aseguradora_estado']=='San Luís Potosí' || $_SESSION['aseg']['estado']=='San Luís Potosí') {echo 'selected="1"';} echo '>San Luís Potosí</option>
				<option value="Sinaloa" '; if ($aseg['aseguradora_estado']=='Sinaloa' || $_SESSION['aseg']['estado']=='Sinaloa') {echo 'selected="1"';} echo '>Sinaloa</option>
				<option value="Sonora" '; if ($aseg['aseguradora_estado']=='Sonora' || $_SESSION['aseg']['estado']=='Sonora') {echo 'selected="1"';} echo '>Sonora</option>
				<option value="Tabasco" '; if ($aseg['aseguradora_estado']=='Tabasco' || $_SESSION['aseg']['estado']=='Tabasco') {echo 'selected="1"';} echo '>Tabasco</option>
				<option value="Tamaulipas" '; if ($aseg['aseguradora_estado']=='Tamaulipas' || $_SESSION['aseg']['estado']=='Tamaulipas') {echo 'selected="1"';} echo '>Tamaulipas</option>
				<option value="Tlaxcala" '; if ($aseg['aseguradora_estado']=='Tlaxcala' || $_SESSION['aseg']['estado']=='Tlaxcala') {echo 'selected="1"';} echo '>Tlaxcala</option>
				<option value="Veracruz" '; if ($aseg['aseguradora_estado']=='Veracruz' || $_SESSION['aseg']['estado']=='Veracruz') {echo 'selected="1"';} echo '>Veracruz</option>
				<option value="Yucatán" '; if ($aseg['aseguradora_estado']=='Yucatán' || $_SESSION['aseg']['estado']=='Yucatán') {echo 'selected="1"';} echo '>Yucatán</option>
				<option value="Zacatecas" '; if ($aseg['aseguradora_estado']=='Zacatecas' || $_SESSION['aseg']['estado']=='Zacatecas') {echo 'selected="1"';} echo '>Zacatecas</option>
			</select>
		</td></tr>
		<tr><td>País</td><td><input type="text" name="pais" size="60" maxlength="40"  value="'; echo ($aseg['aseguradora_pais'] != 'México') ? 'México' : $_SESSION['aseg']['pais']; echo '" /></td></tr>
		<tr><td>' .$lang['RFC'].'</td><td><input type="text" name="rfc" size="60" maxlength="13" value="'; echo ($aseg['aseguradora_rfc'] != '') ? $aseg['aseguradora_rfc'] : $_SESSION['aseg']['rfc']; echo '" /></td></tr>
		<tr><td>' .$lang['Contacto'].'</td><td><input type="text" name="representante" size="60" maxlength="120" value="'; echo ($aseg['aseguradora_representante'] != '') ? $aseg['aseguradora_representante'] : $_SESSION['aseg']['representante']; echo '" /></td></tr>
		<tr><td>' .$lang['Teléfono'].'</td><td><input type="text" name="telefono" size="60" maxlength="40" value="'; echo ($aseg['aseguradora_telefono'] != '') ? $aseg['aseguradora_telefono'] : $_SESSION['aseg']['telefono']; echo '" /></td></tr>
		<tr><td>' .$lang['E mail'].'</td><td><input type="text" name="email" size="60" maxlength="128" value="'; echo ($aseg['aseguradora_email'] != '') ? $aseg['aseguradora_email'] : $_SESSION['aseg']['email']; echo '" /></td></tr>
		<tr><td>&nbsp;</td><td style="text-align:left;">' .$lang['Habilitar envío de Altas?'].' <input type="checkbox" name="alta"'; if($aseg['aseguradora_alta'] == '1') { echo 'checked="checked"'; } echo '" /> | ' .$lang['Autosurtido'].' <input type="checkbox" name="autosurtido"'; if($aseg['autosurtido'] == '1') { echo 'checked="checked"';} echo '" /> | ' .$lang['Precio de Unidad de Trabajo'].' <input type="text" name="preciomo" size="6" maxlength="10" value="'; echo ($aseg['preciout'] != '') ? $aseg['preciout'] : $_SESSION['aseg']['preciout']; echo '" /></td></tr>
		<tr><td>' .$lang['Emails para altas'].'</td><td><input type="text" name="v_email" size="60" maxlength="128" value="'; echo ($aseg['aseguradora_v_email'] != '') ? $aseg['aseguradora_v_email'] : $_SESSION['aseg']['v_email']; echo '" /></td></tr>
		<tr><td>' .$lang['Proveedor Default'].'</td><td>
			<select name="prov_id" size="1">
			<option value="">' . $lang['Seleccione...'] . '</option>'."\n";
	$preg0 = "SELECT prov_id, prov_nic FROM " . $dbpfx . "proveedores WHERE prov_activo = '1'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Proveedores!");
	$filas = mysql_num_rows($matr0);
	while($prov = mysql_fetch_array($matr0)) {
		echo '			<option value="' . $prov['prov_id'] . '" '; if ($aseg['prov_def'] == $prov['prov_id'] || $_SESSION['aseg']['prov_id'] == $prov['prov_id']) {echo 'selected="1"';} echo '>' . $prov['prov_nic'] . '</option>'."\n";
	}
	echo '			</select>
		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="' . $lang['Enviar'].'" />&nbsp;<input type="reset" name="limpiar" value="' . $lang['Borrar'].'" /></td></tr>
	</table>';
	if($accion==="modificar") {
		echo '<input type="hidden" name="aseguradora_id" value="' . $aseg['aseguradora_id'] . '" />';
	} 

echo '	</form>';
	
}

elseif (($accion==='insertar') || ($accion==='actualizar')) {

	if(validaAcceso('1005000', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1'))) {
		$mensaje = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje=' . $lang['Acceso NO autorizado ingresar Usuario y Clave correcta']);
	}

	unset($_SESSION['aseg']);
	$_SESSION['aseg'] = array();
	$nombre=preparar_entrada_bd($nombre); $_SESSION['aseg']['nombre'] = $nombre;
	$nic=preparar_entrada_bd($nic); $_SESSION['aseg']['nic'] = $nic;
	$rfc=preparar_entrada_bd($rfc); $_SESSION['aseg']['rfc'] = $rfc;
	$calle=preparar_entrada_bd($calle); $_SESSION['aseg']['calle'] = $calle;
	$numext=preparar_entrada_bd($numext); $_SESSION['aseg']['numext'] = $numext;
	$numint=preparar_entrada_bd($numint); $_SESSION['aseg']['numint'] = $numint;
	$colonia=preparar_entrada_bd($colonia); $_SESSION['aseg']['colonia'] = $colonia;
	$municipio=preparar_entrada_bd($municipio); $_SESSION['aseg']['municipio'] = $municipio;
	$cp=preparar_entrada_bd($cp); $_SESSION['aseg']['cp'] = $cp;
	$estado=preparar_entrada_bd($estado); $_SESSION['aseg']['estado'] = $estado;
	$pais=preparar_entrada_bd($pais); $_SESSION['aseg']['pais'] = $pais;
	$representante=preparar_entrada_bd($representante); $_SESSION['aseg']['representante'] = $representante;
	$telefono=preparar_entrada_bd($telefono); $_SESSION['aseg']['telefono'] = $telefono;
	$v_email=preparar_entrada_bd($v_email); $_SESSION['aseg']['v_email'] = $v_email;
	$email=preparar_entrada_bd($email); $_SESSION['aseg']['email'] = $email;
	$preciomo=preparar_entrada_bd($preciomo); $_SESSION['aseg']['preciout'] = $preciomo;
//	echo $nombre;
//	print_r($_SESSION['aseg']);

	$error = 'no';
	$mensaje= '';
//	echo '<br><br>====== ' . $error . ' <<>> ' . $mensaje . ' ======<br><br>';
//	echo strlen($telefono1);

	if (strlen($nombre) < 3) {$error = 'si'; $mensaje .= $lang['Razón Social corto'] . $nombre . '<br>';}
	if (strlen($nic) < 2) {$error = 'si'; $mensaje .= $lang['NIC corto'] . $nic . '<br>';}
	if (strlen($email) < 8) {$error = 'si'; $mensaje .= $lang['correo corto'] . $email . '<br>';}
	if (strlen($telefono) < 8) {$error = 'si'; $mensaje .= $lang['teléfono debe tener lada y número local'] . $telefono . '<br>';}
	if (strlen($rfc) < 12) {$error = 'si'; $mensaje .= $lang['RFC corto: 12 posiciones para provs, 13 para personas'].'.<br>';}
	if (strlen($rfc) > 13) {$error = 'si'; $mensaje .= $lang['RFC largo: 12 posiciones para provs, 13 para personas'].'.<br>';}
	if (strlen($calle) < 5) {$error = 'si'; $mensaje .= $lang['calle y número corto'] . $calle . '<br>';}
	if (strlen($colonia) < 3) {$error = 'si'; $mensaje .= $lang['colonia corta'] . $colonia . '<br>';}
	if (strlen($municipio) < 4) {$error = 'si'; $mensaje .= $lang['El municipio o delegación es muy corto: '] . $municipio . '<br>';}
	if (strlen($cp) != 5) {$error = 'si'; $mensaje .= $lang['CP de 5 dígitos'] . $cp . '<br>';}
	
//	echo '<br><br>====== ' . $error . ' ======<br><br>';
	if ($error === 'no') {
		if($accion==='actualizar') {$parametros='aseguradora_id = ' . $aseguradora_id;} else {$parametros='';}
			$sql_data_array = array('aseguradora_razon_social' => $nombre,
			'aseguradora_nic' => $nic,	
			'aseguradora_rfc' => $rfc,
			'aseguradora_calle' => $calle,
			'aseguradora_ext' => $numext,
			'aseguradora_int' => $numint,
			'aseguradora_colonia' => $colonia,
			'aseguradora_cp' => $cp,
			'aseguradora_municipio' => $municipio,
			'aseguradora_estado' => $estado,
			'aseguradora_pais' => $pais,
			'aseguradora_representante' => $representante,
			'aseguradora_telefono' => $telefono,
			'aseguradora_v_email' => $v_email,
			'preciout' => $preciomo,
			'prov_def' => $prov_id,
			'aseguradora_email' => $email);
		if(isset($alta)) { $sql_data_array['aseguradora_alta'] = 1; } else { $sql_data_array['aseguradora_alta'] = 0; }
		if(isset($autosurtido)) { $sql_data_array['autosurtido'] = 1; } else { $sql_data_array['autosurtido'] = 0; }
		if ($accion==='insertar') {
			$aseguradora_id = ejecutar_db($dbpfx . 'aseguradoras', $sql_data_array, $accion, $parametros);
		} else {
			ejecutar_db($dbpfx . 'aseguradoras', $sql_data_array, $accion, $parametros);
		}
		unset($_SESSION['aseg']);
		redirigir('aseguradoras.php?accion=consultar&aseguradora_id=' . $aseguradora_id);
	} else {
		$_SESSION['aseg']['mensaje'] = $mensaje;
		if ($accion==='insertar') {
			redirigir('aseguradoras.php?accion=crear');
		} else {
			redirigir('aseguradoras.php?accion=modificar&aseguradora_id=' . $aseguradora_id);
		}
	}
}

elseif ($accion==="consultar") {
	//$funnum = '1005010';
	//$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
//	echo 'Estamos en la sección  consulta';
	$error = 'si'; $num_cols = 0;
	if ($aseguradora_id!='') {
		$pregunta = "SELECT * FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '$aseguradora_id'";
		$error = 'no';
	} else {
		$nombre=preparar_entrada_bd($nombre);
		$email=preparar_entrada_bd($email);
		$nic=preparar_entrada_bd($nic);
		$mensaje= $lang['Se necesita al menos un dato para buscar'].'<br>';
		if (($nombre!='') || ($email!='') || ($nic!='')) {
			$error = 'no'; $mensaje ='';
			$pregunta = "SELECT * FROM " . $dbpfx . "aseguradoras WHERE ";
			if ($nombre) {$pregunta .= "aseguradora_razon_social like '%$nombre%' ";}
		if (($nombre) && ($email)) {$pregunta .= "AND aseguradora_email like '%$email%' ";} 
			elseif ($email) {$pregunta .= "aseguradora_email like '%$email%' ";}
		if ((($email) || ($nombre)) && ($nic)) {$pregunta .= "AND aseguradora_nic like '%$nic%'";}
			elseif ($nic) {$pregunta .= "aseguradora_nic like '%$nic%'";} 
		}
	}
	if ($error ==='no') {
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
	}

	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	if ($num_cols == 1) {
		echo '		<table cellspacing="2" cellpadding="2" border="0" style="font-size:1.2em;">' . "\n";
		while ($aseg = mysql_fetch_array($matriz)) {
			echo '			<tr class="cabeza_tabla"><td colspan="2" align="left">'.$lang['Datos de la Aseguradora'].' ' . $aseg['aseguradora_razon_social'] . '</td></tr>'."\n";
		
			echo '			<tr class="obscuro"><td>' . $lang['NIC'] . ': ' . $aseg['aseguradora_nic'] . '</td><td>' . $lang['Número de Aseguradora'] . ': ' . $aseg['aseguradora_id'] . '</td></tr>
			<tr class="claro"><td>'.$lang['Representante'].': ' . $aseg['aseguradora_representante'] . '</td><td>'.$lang['Teléfono'].': ' . $aseg['aseguradora_telefono1'] . '</td></tr>
			<tr class="obscuro"><td colspan="2">'.$lang['E mail'].': ' . $aseg['aseguradora_email'] . '</td></tr>
			<tr class="obscuro"><td>' . $lang['Precio de MO'].': $' . number_format($aseg['preciout'], 2) . '</td><td>' . $lang['Proveedor Default'] . ': ' . $aseg['prov_def'] . '</td></tr>
			<tr class="claro"><td>' . $lang['Enviar Altas de Ingresos'] . ': '; 
			echo ($aseg['aseguradora_alta'] == '1') ? 'Sí' : 'No'; 
			echo '</td><td>' . $lang['Autosurtido'] . ': '; 
			echo ($aseg['autosurtido'] == '1') ? 'Sí' : 'No'; 
			echo '</td></tr>
			<tr class="obscuro"><td colspan="2">'.$lang['Emails para altas'].': ' . $aseg['aseguradora_v_email'] . '</td></tr>'."\n";

			//$reto1 = 0; $reto1 = validaAcceso('1005000', $dbpfx);
			//if ($reto1 == '1' || $_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol12'] == '1' ) {
			if (validaAcceso('1005000', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol03'] == '1' || $_SESSION['rol12'] == '1'))) {
				echo '			<tr class="cabeza_tabla"><td colspan="2" align="left">' . $lang['Datos fiscales'] . '</td></tr>'."\n";
				echo '					<tr><td colspan="2">' . $aseg['aseguradora_razon_social'] . '</td></tr>'."\n";
				echo '					<tr><td colspan="2">' . $aseg['aseguradora_calle'] . ' #' . $aseg['aseguradora_ext'] . ' Int.' . $aseg['aseguradora_int'] . '</td></tr>
			<tr><td colspan="2">' . $aseg['aseguradora_colonia'] . ', ' . $aseg['aseguradora_municipio'] . '</td></tr>
			<tr><td colspan="2">' . $lang['CP'] . ': ' . $aseg['aseguradora_cp'] . '. ' . $aseg['aseguradora_estado'] . '. ' . $aseg['aseguradora_pais'] . '.</td></tr>
			<tr><td colspan="2">' . $lang['RFC'] . ': ' . $aseg['aseguradora_rfc'] . '</td></tr>'."\n";
			}
		
			echo '		<tr class="cabeza_tabla"><td colspan="2" align="left">Acciones</td></tr>'."\n";
			echo '					<tr><td colspan="2">
				<a href="aseguradoras.php?accion=modificar&aseguradora_id=' . $aseg['aseguradora_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/cambiar-datos.png" alt="'.$lang['Modificar'].'" title="'.$lang['Modificar'].'"></a>
				<a href="aseguradoras.php?accion=regcobro&aseguradora_id=' . $aseg['aseguradora_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/cobro-registrar.png" alt="'.$lang['Registrar Cobros'].'" title="'.$lang['Registrar Cobros'].'"></a>
			</td></tr>'."\n";
		}
		echo '		</table>'."\n";
	} elseif($num_cols > 1) {
		
		echo '			<table cellspacing="2" cellpadding="2" border="1">
				<tr><td colspan="4" align="left">' . $lang['Datos de la Aseguradora'] . ':</td></tr>'."\n";
		echo '				<tr><td>#</td><td>' . $lang['NIC'] . '</td><td>' . $lang['Razón Social'] . '</td><td>' . $lang['Acciones'] . '</td></tr>'; $fondo = 'obscuro';
		while ($aseg = mysql_fetch_array($matriz)) {
			echo '		<tr class="' . $fondo . '">
				<td style="text-align:center;"><a href="aseguradoras.php?accion=consultar&aseguradora_id=' . $aseg['aseguradora_id'] . '">' . $aseg['aseguradora_id'] . '</a></td>
				<td><a href="aseguradoras.php?accion=consultar&aseguradora_id=' . $aseg['aseguradora_id'] . '">' . $aseg['aseguradora_nic'] . '</a></td>
				<td>' . $aseg['aseguradora_razon_social'] . '</td>
				<td>
					<a href="aseguradoras.php?accion=modificar&aseguradora_id=' . $aseg['aseguradora_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/cambiar-datos.png" alt="'.$lang['Modificar'].'" title="'.$lang['Modificar'].'"></a>&nbsp;
					<a href="aseguradoras.php?accion=regcobro&aseguradora_id=' . $aseg['aseguradora_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/cobro-registrar.png" alt="'.$lang['Registrar Cobros'].'" title="'.$lang['Registrar Cobros'].'"></a>&nbsp;
			</td>
			</tr>';
			if($fondo == 'obscuro') { $fondo = 'claro'; } else { $fondo = 'obscuro'; }
		}
		echo '	</table>';
		
	} else {
		$mensaje .= $lang['No se encontraron registros con esos datos'].'</br>';
		echo '<p>' . $mensaje . '</p>';
	}
}

elseif($accion==="regcobro") {
	
	$funnum = 1005015;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	
	if ($retorno == '1') {
		$msg = $lang['Acceso autorizado']; 
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso NO autorizado ingresar Usuario y Clave correcta']);
	}
// ------ Reinicia la protección contra doble envío ------
	unset($_SESSION['microtime']);

	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';



	echo '		<form action="aseguradoras.php?accion=regcobro" method="post" name="regcobro" enctype="multipart/form-data">'."\n";
	if(isset($feini) && $feini != '0000-00-00') {
		$feini = date('Y-m-d 00:00:00', strtotime($feini));
		$prega = " fact_fecha_emision > '" . $feini . "'";
		$t_ini = strftime('%e de %B del %Y', strtotime($feini));
		if(!isset($fefin) || $fefin == '' || $fefin == '0000-00-00') {
			$fefin = date('Y-m-d 23:59:59', time());
		}
		$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
		$prega .= " AND fact_fecha_emision < '" . $fefin . "' ";
		$t_fin = strftime('%e de %B del %Y', strtotime($fefin));
	} else {
		$feini = date('Y-m-01 00:00:00');
		$fefin = date('Y-m-t 23:59:59');
		$prega = " fact_fecha_emision > '" . $feini . "' AND fact_fecha_emision < '" . $fefin . "' ";
	}
	echo '		<table cellpadding="2" cellspacing="0" border="0" class="izquierda" width="100%">'."\n";
	echo '			<tr class="cabeza_tabla"><td colspan="3" style="text-align:left;">'. $lang['Registrar cobro'] . ' ' . $lang['de la Aseguradora'] . ' ' . $asenoti[$aseguradora_id]['razon'] . '</td></tr>'."\n";
	echo '			<tr><td width="30%"><b><big>' . $lang['FechaIniEmi'] . '</big></b><br>';
	//instantiate class and set properties
	require_once("calendar/tc_calendar.php");
	$myCalendar = new tc_calendar("feini", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
	$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
	//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
	//$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2013, 2025);
	$myCalendar->setAutoHide(true, 5000);

	//output the calendar
	$myCalendar->writeScript();
	echo '</td>
				<td width="30%"><b><big>' . $lang['FechaFinEmi'] . '</big></b><br>';
	//instantiate class and set properties
	$myCalendar = new tc_calendar("fefin", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
	$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
	//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
	//$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2013, 2025);
	$myCalendar->setAutoHide(true, 5000);

	//output the calendar
	$myCalendar->writeScript();
	echo '				</td><td width="40%">'."\n";
	if($RfcAlternos > 0) {
		$rfcu = explode('|', $cualrfc);
		echo 					$lang['SelEmisor'] . '<br><select name="cualrfc">'."\n";
		echo '					<option value="">' . $lang['Seleccione...'] . '</option>'."\n";
		foreach($Rfcs as $rfck => $rfcv) {
			echo '					<option value="' . $rfcv[0] . '|' . $rfcv[1] . '|' . $rfcv[2] . '|' . $rfcv[3] . '" ';
			if($rfcu[0] == $rfcv[0]) { echo 'SELECTED '; }
			echo '/>' . $rfcv[1] . '</option>'."\n";
		}
		echo '					</select>'."\n";
	} else {
		echo '					<input type="hidden" name="cualrfc" value="' . $agencia_rfc . '|' . $agencia_razon_social . '|' . $agencia_reg33 . '|' . $agencia_cp . '" />'."\n";
	}

	echo '</td>
			</tr>
			<tr>
				<td colspan="3"><input type="submit" value="Enviar" /><input type="hidden" name="aseguradora_id" value="' . $aseguradora_id . '" /></td>
			</tr>
		</table></form>'."\n";

	echo '		<form action="aseguradoras.php?accion=procesacobro" method="post" name="procesacobro" enctype="multipart/form-data">
		<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	echo '					<tr><td>
				<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="100%">'."\n";
	$tipo = '1';
	$tipo_nom = $lang['Factura']; 
	echo '					<tr class="cabeza_tabla"><td colspan="2" style="text-align:left;">'. $lang['DatosPago'] . '</td></tr>' . "\n";
	if(!isset($mont_pc) || $mont_pc == '') { $mont_pc = $_SESSION['aseg']['cobro']; }
	$mont_pc = limpiarNumero($mont_pc);
	echo '					<tr><td>'. $lang['Monto de este cobro'].'</td><td style="text-align:left;"><input type="text" name="cobro" value="' . number_format($mont_pc, 2) . '" size="10"  style="text-align:right;"/></td></tr>'."\n";
	echo '					<tr><td>'. $lang['Fecha del cobro'].'</td><td style="text-align:left;">';

	if(isset($_SESSION['aseg']['fechacobro'])) {
		$dia = date("d", strtotime($_SESSION['aseg']['fechacobro']));
		$mes = date("m", strtotime($_SESSION['aseg']['fechacobro']));
		$year = date("Y", strtotime($_SESSION['aseg']['fechacobro']));
	} else {
		$dia = date("d");
		$mes = date("m");
		$year = date("Y");
	}

		//instantiate class and set properties
	$myCalendar = new tc_calendar("fechacobro", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
	$myCalendar->setDate($dia, $mes, $year);
//	$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
	$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2011, 2020);
	$myCalendar->setAutoHide(true, 5000);

// output the calendar
	$myCalendar->writeScript();

	echo '</td></tr>';
	
	echo '					<tr><td>'. $lang['Método de Cobro'].'</td><td style="text-align:left;">'."\n";
	echo '						<select name="forma_cobro" size="1">
							<option value="" >'. $lang['Seleccione'].'</option>'."\n";
	for($i=1;$i<=$opcpago;$i++) {
		echo '							<option value="' . $i . '"';
		if ($_SESSION['aseg']['forma_cobro'] == $i) { echo ' selected="selected"'; }
		echo ' >' . constant('TIPO_PAGO_'.$i) . '</option>'."\n";
	}
	echo '						</select>'."\n";
	echo '					</td></tr>'."\n";
	echo '					<tr><td>'. $lang['Banco Origen'].'</td><td style="text-align:left;"><input type="text" name="banco" value="';
	if($_SESSION['aseg']['banco'] != '') {
		echo $_SESSION['aseg']['banco'];
	} else {
		echo REC_CLI_BANCO;
	} 
	echo '" size="30" /></td></tr>'."\n";
	echo '					<tr><td>'. $lang['Num cheque o transferencia'].'</td><td style="text-align:left;">
						<input type="hidden" name="aseguradora" value="' . $aseguradora_id . '">
						<input type="text" name="referencia" value="' . $_SESSION['aseg']['referencia'] . '" size="15" ';
	echo '/></td></tr>'."\n";
	echo '					<tr><td>'. $lang['Cuenta de Cobro'].'</td><td style="text-align:left;">
						<select name="cuenta" size="1">
							<option value="" >'. $lang['Seleccione'].'</option>'."\n";

	$preg0 = "SELECT * FROM " . $dbpfx . "cont_cuentas WHERE ban_activo = '1'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuentas");
	while ($ban = mysql_fetch_array($matr0)) {
		echo '							<option value="' . $ban['ban_id'] . '"';
		if ($_SESSION['aseg']['cuenta'] == $ban['ban_id']) { echo ' selected="selected"'; }
		echo ' >' . $ban['ban_nombre'] . ' - ' . $ban['ban_cuenta'] . '</option>'."\n";
	}
	echo '						</select>';
	echo '					</td></tr>'."\n";
	echo '					<tr><td>'. $lang['Imagen de comprobante de cobro'].'</td><td style="text-align:left;"><input type="file" name="comprobante" size="30" /><input type="hidden" name="aseguradora_id" value="' . $aseguradora_id . '" /></td></tr>'."\n";
	echo '		<tr><td>' . $lang['CrearRPE'] . '</td><td style="text-align:left;">' . $lang['Sí'] . '<input type="radio" name="crearpe" value="1" ';
	if($_SESSION['aseg']['crearpe'] == '1') { echo 'checked="checked" '; }
	echo '/>&nbsp;&nbsp;' . $lang['No'] . '<input type="radio" name="crearpe" value="2" ';
	if($_SESSION['aseg']['crearpe'] == '2') { echo 'checked="checked" '; }
	echo '/></td></tr>'."\n";
	echo '		<tr><td>' . $lang['SerieDeRPE'] . '</td><td style="text-align:left;"><input type="hidden" name="rpe_serie" value="' . $factserie . '" />' . $factserie . '</td></tr>'."\n";
	echo '		<tr><td>' . $lang['Número de RPE'] . '</td><td style="text-align:left;"><input type="text" name="rpe_num" value="';
	if($_SESSION['aseg']['rpe_num'] > 0) { echo $_SESSION['aseg']['rpe_num']; } else { echo $fnum['fact_num'] + 1; }
	echo '" size="2" /><input type="hidden" name="fact_num" value="' . $fnum['fact_num'] . '" /></td></tr>'."\n";
	echo '		<tr><td>' . $lang['Moneda'] . '</td><td style="text-align:left;"><input type="hidden" name="moneda" value="MXN" />MXN</td></tr>'."\n";
	echo '				</table>'."\n";

// ------------------- Seleccionar facturas por aplicar cobros -----------------------------------	

	$preg1 = "SELECT * FROM " . $dbpfx . "facturas_por_cobrar WHERE aseguradora_id = '" . $aseguradora_id . "' AND fact_cobrada = 0  AND fact_tipo = 1 AND " . $prega;
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de facturas".$preg1);
	$fila1 = mysql_num_rows($matr1);
	if($cualrfc == '') {
		$cualrfc = $agencia_rfc . '|' . $agencia_razon_social . '|' . $agencia_reg33 . '|' . $agencia_cp;
	}
	$rfcu = explode('|', $cualrfc);

	echo '				<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="100%">'."\n";
	$acobrar = 0;
	if($fila1 > 0) {
		echo '					<tr class="cabeza_tabla" style="text-align:left;"><td colspan="9" style="text-align:left;">Facturas pendientes de cobro</td></tr>'."\n";
		echo '					<tr><td>Factura</td><td>Fecha</td><td>Importe Total</td><td>Importe Cobrado</td><td>Importe Por Cobrar</td><td>Seleccionar</td><td>Importe de pago</td><td>' . $lang['NumeroParcialidad'] . '</td><td>' . $lang['UUID'] . '</td></tr>'."\n";
		$tpc = 0;
// ------ Obtener la serie a utilizar para crear el RPE si aplica ------
		if($valor['SerieRPE'][1] != '') {
			$factserie = $valor['SerieRPE'][1]; 
		} else {
			$factserie = 'RPE';
		}
		$preg3 = "SELECT fact_num FROM " . $dbpfx . "facturas WHERE fact_serie = '" . $factserie . "' ORDER BY fact_num DESC LIMIT 1";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de Recibo de Pago Electrónico! " . $preg3);
		$fnum = mysql_fetch_array($matr3);

		while ($fact = mysql_fetch_array($matr1)) {
			$ponrenglon = 1; $activacheck = 0;
			$preg2 = "SELECT cf.monto, c.cobro_tipo FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE cf.fact_id = '" . $fact['fact_id'] . "' AND cf.cobro_id = c.cobro_id";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cobros " . $preg2);
			$cobrado = 0; $numcobs = 0;
			while ($cob = mysql_fetch_array($matr2)) {
				$cobrado = $cobrado + round($cob['monto'], 2);
				if($cob['cobro_tipo'] != 4) { $numcobs++; }
			}
			$fact['fact_monto'] = round($fact['fact_monto'], 2);
			$porcobrar = $fact['fact_monto'] - $cobrado;
			$renglon = '';
			if($porcobrar > 0) {
				$acobrar++;
				$renglon = '					<tr><td>' . $fact['fact_num'] . '</td>
						<td>' . $fact['fact_fecha_emision'] . '</td>
						<td style="text-align:right;">$' . number_format($fact['fact_monto'], 2) . '</td>
						<td style="text-align:right;">$' . number_format($cobrado, 2) . '</td>
						<td style="text-align:right;">$' . number_format($porcobrar, 2) . '</td>';
// ------ Recopila datos para agregar a REP -------
				if(file_exists(DIR_DOCS . $fact['fact_num'] . '-' . $fact['fact_uuid'] . '.xml')) {
					$cfdi = file_get_contents(DIR_DOCS . $fact['fact_num'] . '-' . $fact['fact_uuid'] . '.xml');
					$xml = new DOMDocument();
					if($xml->loadXML($cfdi)) {
						$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
						$Emisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
						$Receptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
						if($Comprobante->getAttribute("Version") == '3.3') {
							if($Comprobante->getAttribute("MetodoPago") == 'PIP' || $Comprobante->getAttribute("MetodoPago") == 'PPD') {
								// --- Verifica que la fecha de cobro sea mayor a la fecha de la factura --
								$fecobseg = strtotime($fechacobro); $fecobseg = $fecobseg + 86399; 
								if($fecobseg >= strtotime($Comprobante->getAttribute("Fecha"))) {
									if($valor['timbres'][0] > 0) {
										$renglon .= '						<td style="text-align:right;"><input style="text-align:center;" type="text" name="num_parcialidad[' . $fact['fact_id'] . ']" value="';
										if($_SESSION['aseg']['num_parcialidad'][$fact['fact_id']] != '') { $renglon .= $_SESSION['aseg']['num_parcialidad'][$fact['fact_id']]; } else { $renglon .= ($numcobs + 1); }
										$renglon .= '" size="2" /><input type="hidden" name="num_par[' . $fact['fact_id'] . ']" value="' . $numcobs . '" /></td>
						<td style="text-align:left;">' . $fact['fact_uuid'] . '
							<input type="hidden" name="SerieRel[' . $fact['fact_id'] . ']" value="' . $Comprobante->getAttribute("Serie") . '" />
							<input type="hidden" name="FolioRel[' . $fact['fact_id'] . ']" value="' . $Comprobante->getAttribute("Folio") . '" />
							<input type="hidden" name="MetodoRel[' . $fact['fact_id'] . ']" value="' . $Comprobante->getAttribute("MetodoPago") . '" />
							<input type="hidden" name="ReceptorNombre" value="' . $Receptor->getAttribute("Nombre") . '" />
							<input type="hidden" name="ReceptorRfc" value="' . $Receptor->getAttribute("Rfc") . '" />
							<input type="hidden" name="doc_ot" value="' . $fact['orden_id'] . '" />
						</td>'."\n";
										// --- Creamos $_SESSION PARA GUARDAR EL RFC ---
										$_SESSION['RPE']['RfcReceptor'] = $Receptor->getAttribute("Rfc");
										$rfcem = $Emisor->getAttribute("Rfc");
										$habcrrpe = 1;
									}
								} else {
									$renglon .= '						<td style="text-align:right;" colspan="2">' . $lang['FeCobMenor'] . ' No se genera REP</td>'."\n";
								}
							} else {
								$renglon .= '						<td style="text-align:right;" colspan="2">' . $lang['TipoPUE'] . ' No se genera REP</td>'."\n";
							}
						} else {
							$renglon .= '						<td colspan="2">' . $lang['CFDIVersionNo33'] . '</td>'."\n";
						}
					} else {
						$renglon .= '						<td colspan="2">' . $lang['XMLNoValido'] . '</td>'."\n";
					}
				} else {
					$renglon .= '						<td colspan="2">' . $lang['NoHayCFDi'] . '</td>'."\n";
				}
/*				$renglon .= '						<td style="text-align:center;">';
				if(($crearpe == 1 && $fecobseg >= strtotime($Comprobante->getAttribute("Fecha"))) || $crearpe == 2) {
					$renglon .= '<input type="checkbox" name="selfact[' . $fact['fact_id'] . ']" value="1" ';
					if($_SESSION['aseg']['selfact'][$fact['fact_id']] == '1') {
						$renglon .= ' checked="checked" /></td>
								<td style="text-align:right;"><input type="text" name="fact_porcob[' . $fact['fact_id'] . ']" value="';
						if($_SESSION['aseg']['porcobrar'][$fact['fact_id']] > 0) {
							$porcobrar = $_SESSION['aseg']['porcobrar'][$fact['fact_id']];
						}
						$renglon .= number_format($porcobrar, 2);
						$renglon .= '" size="8" style="text-align:right;" /><input type="hidden" name="saldo_anterior[' . $fact['fact_id'] . ']" value="' . ($fact['fact_monto'] - $cobrado) . '" /><input type="hidden" name="fact_monto[' . $fact['fact_id'] . ']" value="' . $fact['fact_monto'] . '"/></td>'."\n";
					} else {
						$renglon .= ' /></td>
					<td></td>'."\n";
					}
				} else {
					$renglon .= '</td>
					<td></td>'."\n";
				} */
				$renglon .= '					</tr>'."\n";
				if($rfcem != $rfcu[0] && $RfcAlternos > 0) { $ponrenglon = 0; }
				if($ponrenglon == 1) {
					if($_SESSION['aseg']['selfact'][$fact['fact_id']] == '1') {
						$tpc = $tpc + $porcobrar;
					}
					echo $renglon;
				}
			}
		}
		$tpc = limpiarNumero($tpc);
		echo '					<tr><td colspan="6" style="text-align:right;">Importe Total a Registrar</td><td style="text-align:right;">$' . number_format($tpc, 2) . '<input type="hidden" name="tpc" value="' . $tpc . '" /></td><td colspan="2"></td></tr>'."\n";
		if($habcrrpe == 1) {
			echo '					<tr><td colspan="3">' . $lang['CrearRPE'] . ' ' . $lang['Sí'] . '<input type="radio" name="crearpe" value="1" ';
			if($_SESSION['aseg']['crearpe'] == '1') { echo 'checked="checked" '; }  
			echo '/>&nbsp;&nbsp;' . $lang['No'] . '<input type="radio" name="crearpe" value="2" ';
			if($_SESSION['aseg']['crearpe'] == '2') { echo 'checked="checked" '; }
			echo '/></td></tr>'."\n";
			echo '					<tr><td>' . $lang['SerieDeRPE'] . '</td><td style="text-align:left;" colspan="2"><input type="hidden" name="rpe_serie" value="' . $factserie . '" />' . $factserie . '</td></tr>'."\n";
			echo '					<tr><td>' . $lang['Número de RPE'] . '</td><td style="text-align:left;" colspan="2"><input type="text" name="rpe_num" value="';
			if($_SESSION['aseg']['rpe_num'] > 0) { echo $_SESSION['aseg']['rpe_num']; } else { echo $fnum['fact_num'] + 1; }
			echo '" size="2" /><input type="hidden" name="fact_num" value="' . $fnum['fact_num'] . '" /></td></tr>'."\n";
			echo '					<tr><td>' . $lang['Moneda'] . '</td><td style="text-align:left;" colspan="2"><input type="hidden" name="moneda" value="MXN" />MXN</td></tr>'."\n";
		}
		if($acobrar == 0) {
			echo '					<tr><td colspan="7" style="text-align:left;"><span class="alerta">Hay facturas no cobradas pero no hay montos pendientes por cobrar para ' . $asenoti[$aseguradora_id]['razon'] . ', por favor contacte a Soporte AutoShop Easy.</span></td></tr>'."\n";
		}
		echo '					<tr><td colspan="7" style="text-align:left;"><input type="submit" name="recalcular" value="Recalcular" />
						<input type="hidden" name="aseguradora_id" value="' . $aseguradora_id . '" />
						<input type="hidden" name="cualrfc" value="' . $cualrfc . '" />
						<input type="hidden" name="microtime" value="' . microtime() . '" />';
		if($tpc == $mont_pc && $mont_pc > 0) {
			echo '<input type="submit" name="enviar" value="'. $lang['Aplicar'].'" />';
		} else {
			echo $lang['No coinciden montos'];
		}
		echo '</td></tr>'."\n";
	} else {
		echo '					<tr><td colspan="9" style="text-align:left;"><span class="alerta">No se encontraron facturas por registrar cobros para ' . $asenoti[$aseguradora_id]['razon'] . '</span></td></tr>'."\n";
	}
	echo '				</table>'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="100%">'."\n";
	echo '					<tr class="cabeza_tabla"><td colspan="2">
							<input type="hidden" name="feini" value="' . $feini . '" />
							<input type="hidden" name="fefin" value="' . $fefin . '" />
						</td></tr>'."\n";
	echo '					<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="aseguradoras.php?accion=consultar&aseguradora_id=' . $aseguradora_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a aseguradora'].'" title="'. $lang['Regresar a aseguradora'].'"></a></div></td></tr>'."\n";
	echo '				</table>'."\n";
	echo '			</td>
			</tr>
		</table>
		</form>';
}

elseif($accion==="procesacobro") {

	$funnum = 1005015;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);

	if ($retorno == '1') {
		$msg = $lang['Acceso autorizado']; 
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso NO autorizado ingresar Usuario y Clave correcta']);
	}
	unset($_SESSION['aseg']);
	$_SESSION['aseg'] = array();

	$mensaje = '';
	$error = 'no'; 
	foreach($selfact as $k => $v) {
		if($v == '1') {
			$_SESSION['aseg']['selfact'][$k] = $v;
			$fact_porcob[$k] =  limpiarNumero($fact_porcob[$k]); $_SESSION['aseg']['porcobrar'][$k] = $fact_porcob[$k];
			$_SESSION['aseg']['num_parcialidad'][$k] = $num_parcialidad[$k];
		}
	}
	$cobro = limpiarNumero($cobro); $_SESSION['aseg']['cobro'] = $cobro;
	$rpe_num = limpiarNumero($rpe_num); $_SESSION['aseg']['rpe_num'] = $rpe_num;
	$_SESSION['aseg']['fechacobro'] = $fechacobro;
	$_SESSION['aseg']['forma_cobro'] = $forma_cobro;
	$banco = preparar_entrada_bd($banco); $_SESSION['aseg']['banco'] = $banco;	
	$referencia = preparar_entrada_bd($referencia); $_SESSION['aseg']['referencia'] = $referencia;
	$cuenta = preparar_entrada_bd($cuenta); $_SESSION['aseg']['cuenta'] = $cuenta;	

	if($crearpe != '1' && $crearpe != '2') { $error = 'si'; $mensaje .= $lang['ValidarCrearRPE'].'<br>';}
	if($recalcular == 'Recalcular') {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('aseguradoras.php?accion=regcobro&aseguradora_id=' . $aseguradora_id . '&feini=' . $feini . '&fefin=' . $fefin . '&cualrfc=' . $cualrfc);
	}

//	$_SESSION['aseg']['por_cobrar'] = $por_cobrar;
	$tipo_nom = 'Factura'; 

//	echo $forma_pago;
	$por_cobrar = 0;
	foreach($fact_porcob as $k => $v) {
		$por_cobrar = $por_cobrar + $v;
	}
	$por_cobrar = limpiarNumero($por_cobrar);

	if($cobro <= 0 || $cobro == '') {$error = 'si'; $mensaje .= $lang['monto del cobro no puede ser cero'].'<br>';}
	if($forma_cobro == '' || !isset($forma_cobro)) {$error = 'si'; $mensaje .= $lang['Selecc forma de pago'].'<br>';}
	if(!isset($cuenta) || $cuenta == '') {$error = 'si'; $mensaje .= $lang['Banco y Cuenta de cobro'].'<br>';}
//	if($forma_cobro > 1 && $referencia == '') {$error = 'si'; $mensaje .=$lang['Num cheque o transferencia'].'<br>';}
	if($cobro != $por_cobrar) {$error = 'si'; $mensaje .= $lang['Monto del cobro no debe ser diferente'].'<br>';}
	if(!is_array($fact_monto) || count($fact_monto) < 1) {$error = 'si'; $mensaje .=$lang['Datos incompletos'].'<br>';}

	if($crearpe == 1) {
		if($rpe_num == '' || !is_numeric($rpe_num) || ($rpe_num <= $fact_num)) { $error = 'si'; $mensaje .= $lang['ValidarRPENum'].'<br>';}
		foreach($fact_porcob as $k => $v) {
			if($num_parcialidad[$k] == '' || !is_numeric($num_parcialidad[$k]) || ($num_parcialidad[$k] <= $num_par[$k])) { $error = 'si'; $mensaje .= $lang['ValidarParNum'].'<br>';}
		}

// ------ Traducción de forma de cobro para efectos fiscales ------
		if($forma_cobro == 1) { $fcobro = '01'; }
		elseif($forma_cobro == 2) { $fcobro = '02'; }
		elseif($forma_cobro == 3) { $fcobro = '03'; }
		elseif($forma_cobro == 6) { $fcobro = '04'; }
		elseif($forma_cobro == 7) { $fcobro = '28'; }
		else { $error = 'si'; $mensaje .= $lang['SeleccFormaFiscal'].'<br>'; }
	}

	if($tpc != $por_cobrar) {
		$_SESSION['msjerror'] = 'El monto Total por Registrar era diferente a la suma de las asignaciones por factura. Se recalculó.<br>';
		redirigir('aseguradoras.php?accion=regcobro&aseguradora_id=' . $aseguradora_id . '&feini=' . $feini . '&fefin=' . $fefin);
	}

	if($error === 'no') {
		$preg0 = "SELECT * FROM " . $dbpfx . "cont_cuentas WHERE ban_id = '$cuenta'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuenta bancaria");
		$ban = mysql_fetch_array($matr0);
//		print_r($subir);
//		echo 'Resultado de subir<br>';

		$sql_data_array = array(
			'cobro_tipo' => $fact['fact_tipo'],
			'cobro_monto' => $cobro,
			'cobro_metodo' => $forma_cobro,
			'cobro_banco' => $banco,
			'cobro_cuenta' => $cuenta,
			'cobro_referencia' => $referencia,
			'cobro_fecha' => $fechacobro,
			'aseguradora_id' => $aseguradora,
			'usuario' => $_SESSION['usuario']);
		$cobro_id = ejecutar_db($dbpfx . 'cobros', $sql_data_array);
		unset($sql_data_array);

		foreach($fact_porcob as $k => $v) {
			if($v > 0) {
				$preg1 = "SELECT * FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '$k'";
				$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de facturas ".$preg1);
				$fact = mysql_fetch_array($matr1);
				$orden_id = $fact['orden_id'];
				if($_FILES['comprobante']['name'] != '') {
					$subir = agrega_documento($orden_id, $_FILES['comprobante'], $lang['Imagen de comprobante de cobro'] . ' ' . $cobro_id, $dbpfx, '', '1');
				}

				$sql_data_array = array(
					'cobro_id' => $cobro_id,
					'fact_id' => $k,
					'monto' => $v,
					'aseguradora_id' => $aseguradora,
					'orden_id' => $orden_id,
					'usuario' => $_SESSION['usuario'],
					'fecha' => date('Y-m-d H:i:s', time()));
				ejecutar_db($dbpfx . 'cobros_facturas', $sql_data_array);
				bitacora($orden_id, 'Cobro de Factura ID ' . $k . ' con el cobro id ' . $cobro_id . ' por un monto de ' . $v, $dbpfx);
				unset($sql_data_array);

				$preg2 = "SELECT monto FROM " . $dbpfx . "cobros_facturas WHERE fact_id = '$k'";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cobros - facturas ".$preg2);
				$mcob = 0;
				while($cf = mysql_fetch_array($matr2)) {
					$mcob = $mcob + $cf['monto'];
				}

				if($fact_monto[$k] == $mcob) {
					$coloca = "UPDATE " . $dbpfx . "facturas_por_cobrar SET fact_cobrada = '1', fact_fecha_cobrada = '" . $fechacobro . "' WHERE fact_id = '$k'";
					$graba = mysql_query($coloca) or die("ERROR: Fallo actualización de facturas por cobrar! " . $coloca);
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $coloca . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
					bitacora($orden_id, $lang['Cobro Total'] . $tipo_nom . ' id ' . $k, $dbpfx);
				}

// ----------- Asientos contables ----------------->
		
				if($asientos == 1) {
					$poliza = regPoliza('2', $lang['Registro en cuenta'] . $ban['ban_id'] .$lang['cobro de la factura'] . $fact['fact_num'] . $lang['OT'] . $orden_id, $fact_num);
		
					$resultado = regAsiento('5', '0', '2', $poliza['ciclo'], $poliza['polnum'], $ban['ban_id'],$lang['Registro en cuenta'] . $ban['ban_id'] . $lang['cobro de la factura'] . $fact['fact_num'] .$lang['OT'] . $orden_id, $v, $orden_id, $fact['fact_num']);
		
					$iva = round(($v - ($v / 1.16)), 2);
					$resultado = regAsiento('0', '0', '2', $poliza['ciclo'], $poliza['polnum'], '2000010', $lang['IVA trasladado por cobrar de la factura'] . $fact['fact_num'] .$lang['OT'] . $orden_id, $iva, $orden_id, $fact['fact_num']);

					$resultado = regAsiento('0', '1', '2', $poliza['ciclo'], $poliza['polnum'], '2000015', $lang['IVA cobrado de la factura'] . $fact['fact_num'] . $lang['OT'] . $orden_id, $iva, $orden_id, $fact['fact_num']);

					$preg2 = "SELECT reporte, cliente_id, aseguradora_id FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '$k' AND orden_id = '$orden_id'";
					$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de factura por cobrar");
					$fxc = mysql_fetch_array($matr2);
					if($fact['reporte'] == '0') {
						$resultado = regAsiento('4', '1', '2', $poliza['ciclo'], $poliza['polnum'], $fact['cliente_id'], $lang['Cobro de factura'] . $fact['fact_num'] . $lang['cliente'] . $fact['cliente_id'] . $lang['OT'] . $orden_id, $v, $orden_id, $fact['fact_num']);
					} else {
						$resultado = regAsiento('3', '1', '2', $poliza['ciclo'], $poliza['polnum'], $fact['aseguradora_id'], $lang['Cobro de factura'] . $fact['fact_num'] . $lang['aseguradora'] . $fact['aseguradora_id'] .$lang['OT'] . $orden_id, $v, $orden_id, $fact['fact_num']);
					}
				}
// ------ Si se eligió crear RPE guardar datos de facturas relacionadas ------
				if($crearpe == 1) {
					$docrel[] = [
						'IdDocumento' => $fact['fact_uuid'],
						'Serie' => $SerieRel[$k],
						'Folio' => $FolioRel[$k],
						'MetodoDePagoDR' => $MetodoRel[$k],
						'ImpPagado' => $v,
						'ImpSaldoAnt' => $saldo_anterior[$k],
						'ImpSaldoInsoluto' => round(($saldo_anterior[$k] - $v),2),
						'NumParcialidad' => $num_parcialidad[$k],
						'doc_ot' => $doc_ot[$k],
					];
				}
			}
		}
		// --- Actualiza el nombre del archivo comprobante de cobro en el cobro ---
		if($subir['nombre'] != '') {
			$param = "cobro_id = '" . $cobro_id . "'";
			$sqldata = ['cobro_documento' => $subir['nombre']];
			ejecutar_db($dbpfx . 'cobros', $sqldata, 'actualizar');
			unset($sqldata);
		}
// ------ Si se eligió crear RPE guardar datos generales del Complemento ------
		if($crearpe == 1) {
			$repgen = [
				'Serie' => $rpe_serie,
				'Folio' => $rpe_num,
				'ReceptorRfc' => $_SESSION['RPE']['RfcReceptor'],
				'ReceptorNombre' => $ReceptorNombre,
			];
			unset($_SESSION['RPE']);
			if($referencia == '') { $referencia = '01'; }
			$pagos[] = [
				'FechaPago' => $fechacobro . 'T23:59:59',
				'FormaDePagoP' => $fcobro,
				'Monto' => $cobro,
				'Banco' => $banco,
				'NumOperacion' => $referencia,
			];

			$rfcv = explode('|', $cualrfc);
			$agencia_rfc = $rfcv[0];
			$agencia_razon_social = $rfcv[1];
			$agencia_regimen = $rfcv[2];
			$agencia_cp = $rfcv[3];

			include('parciales/rpe-3.3.php');
		}
		unset($_SESSION['aseg']);
		redirigir('reportes.php?accion=facturacion&aseguradora_id=' . $aseguradora_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('aseguradoras.php?accion=regcobro&aseguradora_id=' . $aseguradora_id . '&feini=' . $feini . '&fefin=' . $fefin);
	}
}

?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
