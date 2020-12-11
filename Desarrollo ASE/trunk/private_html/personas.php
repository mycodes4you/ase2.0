<?php
include('parciales/funciones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);} // echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k = limpiar_cadena($v);} // echo $k.' -> '.$v.' | ';

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

include('idiomas/' . $idioma . '/personas.php');

if (($accion==='insertar') || ($accion==='actualizar')) {
	/* no cargar encabezado */
} else {

}

//  ----------------  obtener nombres de aseguradoras   -------------------

	$consulta = "SELECT aseguradora_id, aseguradora_logo, aseguradora_nic FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
	while ($aseg = mysql_fetch_array($arreglo)) {
		$ase[$aseg['aseguradora_id']][0] = $aseg['aseguradora_logo'];
		$ase[$aseg['aseguradora_id']][1] = $aseg['aseguradora_nic'];
	}
	$ase[0][0] = 'particular/logo-particular.png';
	$ase[0][1] = 'Particulares';

//  ----------------  nombres de aseguradoras   -------------------

if (($accion==="crear") || ($accion==="modificar")) {

	if (validaAcceso('1090000', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol06'] == '1'))) {
		include('parciales/encabezado.php');
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
	} else {
		redirigir('usuarios.php?mensaje=' . $lang['Acceso NO autorizado']);
	}

//	echo 'Tipo Dato -> ' .$tipodato. ' Empresa -> ' . $eid . ' Cliente -> ' . $cliente_id . '<br>';

	if($scid > 0 && $tipodato == '1' && $dato > 0) {
		$preg3 = "UPDATE " . $dbpfx . "clientes SET cliente_empresa_id = '$dato' WHERE cliente_id = '$scid'";
		echo $preg3;
		$matr3 = mysql_query($preg3);
		redirigir('personas.php?accion=consultar&cliente_id=' . $scid);
	}

	if($tipodato == '' && $eid == '' && $cliente_id == '') {
		echo '			<div class="col-lg-8 col-md-8 col-sm-12" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">'."\n";
		echo '			<form action="personas.php?accion=verifica" method="post" enctype="multipart/form-data">'."\n";
		echo '				<div class="row"><div class="col-sm-12"><div class="content-box-header">
					<div class="panel-title">
						<h3>' . $lang['Buscar contacto'] . '</h3>
					</div>
				</div></div></div>'."\n";
		echo '
				<div class="row">
					<div class="alert alert-warning" role="alert">
						<b><big>' . $lang['Instrucciones'] . ':</b></big><br>
							' . $lang['InstBusq'] . '
					</div>
				</div>
				<div class="row">
					<div class="col-sm-2">
						' . $lang['Razón Social'] . '
					</div>
					<div class="col-sm-6">
						<input type="text" name="empresa" style="width: 100%;" maxlength="128" />
					</div>
				</div>
				<div class="row">
					<div class="col-sm-2">
						' . $lang['Nombre'] . '
					</div>
					<div class="col-sm-6">
						<input type="text" name="nombre" style="width: 100%;" maxlength="128" />
					</div>
				</div>
				<div class="row">
					<div class="col-sm-2">
						' . $lang['Apellidos'] . '
					</div>
					<div class="col-sm-6">
						<input type="text" name="apellidos" style="width: 100%;" maxlength="128" />
					</div>
				</div>
				<div class="row">
					<div class="col-sm-2">
						' . $lang['RFC'] . '
					</div>
					<div class="col-sm-6">
						<input type="text" name="rfc" style="width: 100%;" maxlength="13" />
					</div>
				</div>
				<div class="row">
					<div class="col-sm-1">
						<input type="submit" class="btn btn-success btn-md" name="verificar" value="' . $lang['Verificar'] . '" />
						<input type="hidden" name="origen" value="' . $accion . '" />
						<input type="hidden" name="scid" value="' . $scid . '" />
					</div>
				</div>
				</form><br><br>'."\n";
	} else {
		if($tipodato == '2') {
			redirigir('vehiculos.php?accion=crear&cliente_id=' . $dato);
		} elseif($tipodato == '1') {
			$empresa_id = $dato;
		}
		if($accion==="modificar") {
			$pregunta = "SELECT * FROM " . $dbpfx . "clientes WHERE cliente_id = '$cliente_id'";
		   $matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de clientes");
		   $num_cols = mysql_num_rows($matriz);
		   if ($num_cols > 0) {
		   	$cliente = mysql_fetch_array($matriz);
				$empresa_id = $cliente['cliente_empresa_id'];
				$pregunta2 = "SELECT * FROM " . $dbpfx . "empresas WHERE empresa_id = '" . $empresa_id . "'";
				$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección de Empresa!");
				$empresa = mysql_fetch_array($matriz2);
			} else {
				$accion='crear';
			}
		}

		echo '			<form action="personas.php?accion=';
		if ($accion==="modificar") { echo 'actualizar';} else {echo 'insertar';}
		echo '" method="post" enctype="multipart/form-data">'."\n";
		echo '			<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
		echo '				<tr class="cabeza_tabla"><td colspan="2">' . $lang['Datos de contacto'] . '</td></tr>'."\n";
		echo '				<tr><td>' . $lang['Razón Social'] . '</td><td style="text-align:left;"><span class="peque">' . $lang['Nombre de empresa o Nombre completo del cliente'] . '</span><br><input type="text" name="empresa" size="60" maxlength="255" value="';
		echo ($empresa['empresa_razon_social'] != '') ? $empresa['empresa_razon_social'] : $_SESSION['cliente']['empresa'];
		echo '" /></td></tr>'."\n";
		echo '				<tr><td><strong>' . $lang['Nombre'] . ' *</strong></td><td><input type="text" name="nombre" size="60" maxlength="60" value="';
		echo ($cliente['cliente_nombre'] != '') ? $cliente['cliente_nombre'] : $_SESSION['cliente']['nombre'];
		echo'" /></td></tr>'."\n";
		echo '				<tr><td><strong>' . $lang['Apellidos'] . ' *</strong></td><td><input type="text" name="apellidos" size="60" maxlength="60" value="';
		echo ($cliente['cliente_apellidos'] != '') ? $cliente['cliente_apellidos'] : $_SESSION['cliente']['apellidos'] ;
		echo '" /></td></tr>'."\n";
		echo '				<tr><td><strong>' . $lang['Teléfono Trabajo'] . ' *</strong></td><td><input type="text" name="telefono1" size="60" maxlength="40" value="';
		echo ($cliente['cliente_telefono1'] != '') ? $cliente['cliente_telefono1'] : $_SESSION['cliente']['telefono1']; echo '" /></td></tr>'."\n";
		echo '				<tr><td></td><td style="text-align:left;"><input type="checkbox" name="boletin" value="Si" ';
		if($cliente['cliente_boletin'] == 'Si') {
			echo 'checked="checked" ';
		}
		echo '/>' . $lang['Desea recibir e-mail'] . '</td></tr>'."\n";
		echo '				<tr><td><strong>' . $lang['e-Mail'] . ' *</strong></td><td><input type="text" name="email" size="60" maxlength="120" value="';
		echo ($cliente['cliente_email'] != '') ? $cliente['cliente_email'] : $_SESSION['cliente']['email']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['TipoCliente'] . '</td><td style="text-align:left;">' . $lang['CliAseg'] . '<input type="radio" name="tipo" value="1" ';
		if($cliente['cliente_tipo'] == '1' || $_SESSION['cliente']['tipo'] == '1' ) { echo 'checked="checked" '; }
		echo '/> ' . $lang['CliOtro'] . '<input type="radio" name="tipo" value="0" ';
		if($cliente['cliente_tipo'] === '0' || $_SESSION['cliente']['tipo'] === '0' ) { echo 'checked="checked" '; }
		echo ' /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Teléfono Casa'] . '</td><td><input type="text" name="telefono2" size="60" maxlength="40" value="';
		echo ($cliente['cliente_telefono2'] != '') ? $cliente['cliente_telefono2'] : $_SESSION['cliente']['telefono2']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Celular'] . '</td><td><input type="text" name="movil" size="60" maxlength="40" value="';
		echo ($cliente['cliente_movil'] != '') ? $cliente['cliente_movil'] : $_SESSION['cliente']['movil']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Nextel'] . '</td><td><input type="text" name="movil2" size="60" maxlength="40" value="';
		echo ($cliente['cliente_movil2'] != '') ? $cliente['cliente_movil2'] : $_SESSION['cliente']['movil2']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Telegram'] . '</td><td><input type="text" name="telegram_id" size="60" maxlength="40" value="';
		echo ($cliente['cliente_telegram_id'] != '') ? $cliente['cliente_telegram_id'] : $_SESSION['cliente']['telegram_id']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Representante'] . '</td><td><input type="text" name="representante" size="60" maxlength="255" value="';
		echo ($empresa['empresa_representante'] != '') ? $empresa['empresa_representante'] : $_SESSION['cliente']['representante']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Tel del Representante'] . '</td><td><input type="text" name="telefono" size="60" maxlength="40" value="';
		echo ($empresa['empresa_telefono'] != '') ? $empresa['empresa_telefono'] : $_SESSION['cliente']['telefono']; echo '" /></td></tr>'."\n";
		echo '				<tr class="cabeza_tabla"><td colspan="2">' . $lang['Datos fiscales de facturación'] . '</td></tr>'."\n";
//		echo '				<tr><td colspan="2">' . $lang['Desea actualizar Datos fiscales?'] . ' <input type="checkbox" name="fiscales" value="1"';
//		if($_SESSION['cliente']['fiscales'] == '1') { echo ' checked="checked"';}
//		echo ' /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['RFC'] . '</td><td style="text-align:left;"><input type="text" name="rfc" size="60" maxlength="15" value="';
		echo ($empresa['empresa_rfc'] != '') ? $empresa['empresa_rfc'] : $_SESSION['cliente']['rfc'];
		echo '" /><br><span class="peque">' . $lang['Si no requiere factura para deduccion fiscal, colocar RFC genérico XAXX010101000'] . '</span></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Calle'] . '</td><td><input type="text" name="calle" size="60" maxlength="120" value="';
		echo ($empresa['empresa_calle'] != '') ? $empresa['empresa_calle'] : $_SESSION['cliente']['calle']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Número exterior'] . '</td><td style="text-align:left;"><input type="text" name="numext" size="20" maxlength="15" value="';
		echo ($empresa['empresa_ext'] != '') ? $empresa['empresa_ext'] : $_SESSION['cliente']['numext']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Número interior'] . '</td><td style="text-align:left;"><input type="text" name="numint" size="20" maxlength="15" value="';
		echo ($empresa['empresa_int'] != '') ? $empresa['empresa_int'] : $_SESSION['cliente']['numint']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Colonia'] . '</td><td><input type="text" name="colonia" size="60" maxlength="60" value="';
		echo ($empresa['empresa_colonia'] != '') ? $empresa['empresa_colonia'] : $_SESSION['cliente']['colonia']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['C.P.'] . '</td><td style="text-align:left;"><input type="text" name="postal" size="5" maxlength="5" value="';
		echo ($empresa['empresa_cp'] != '') ? $empresa['empresa_cp'] : $_SESSION['cliente']['postal']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Delegación o Municipio'] . '</td><td><input type="text" name="municipio" size="60" maxlength="60" value="';
		echo ($empresa['empresa_municipio'] != '') ? $empresa['empresa_municipio'] : $_SESSION['cliente']['municipio']; echo '" /></td></tr>'."\n";
		echo '				<tr><td>' . $lang['Estado'] . '</td><td style="text-align:left;">
				<select name="estado" size="1">
					<option value="" ';
		if($empresa['empresa_estado']=='') { echo 'selected="1"';}
		echo '>' . $lang['Selecciona Estado'] . '</option>'."\n";
		foreach($estados as $k){
			echo '					<option value="' . $k . '" ';
			if($empresa['empresa_estado'] == $k || $_SESSION['cliente']['estado'] == $k) { echo 'selected="selected" ';}
			echo '>' . $k . '</option>'."\n";
		}
		echo '				</select>
				</td></tr>'."\n";
		echo '				<tr><td>' . $lang['País'] . '</td><td><input type="text" name="pais" size="60" maxlength="40"  value="';
		echo ($_SESSION['cliente']['pais'] != '') ? $_SESSION['cliente']['pais'] : 'México'; echo '" /></td></tr>'."\n";
		echo '				<tr><td colspan="2" style="text-align:left;"><input type="submit" value="' . $lang['Enviar'] . '" />&nbsp;<input type="reset" name="limpiar" value="' . $lang['Borrar'] . '" /></td></tr>'."\n";
		echo '				</tr>
			</table>'."\n";

		if($accion==="modificar") {
			echo '<input type="hidden" name="cliente_id" value="' . $cliente['cliente_id'] . '" />
			<input type="hidden" name="clave" value="' . $cliente['cliente_clave'] . '" />
			<input type="hidden" name="regexp" value="' . $regexp . '" />
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<input type="hidden" name="empresa_id" value="' . $empresa['empresa_id'] . '" />'."\n";
		}

		echo '	</form>'."\n";
	}
}

elseif (($accion==='insertar') || ($accion==='actualizar')) {

	if (validaAcceso('1090000', $dbpfx) == '1' ||  ($solovalacc !='1' && ($_SESSION['rol06']=='1'))) {
		// Acceso autorizado
	} else {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso NO autorizado']);
	}
	unset($_SESSION['cliente']);
	$_SESSION['cliente'] = array();
	$nombre=preparar_entrada_bd($nombre); $_SESSION['cliente']['nombre'] = $nombre;
	$apellidos=preparar_entrada_bd($apellidos); $_SESSION['cliente']['apellidos'] = $apellidos;
	$email=preparar_entrada_bd($email); $_SESSION['cliente']['email'] = $email;
	$_SESSION['cliente']['tipo'] = $tipo;
	$telefono1=preparar_entrada_bd($telefono1); $_SESSION['cliente']['telefono1'] = $telefono1;
	$telefono2=preparar_entrada_bd($telefono2); $_SESSION['cliente']['telefono2'] = $telefono2;
	$movil=preparar_entrada_bd($movil); $_SESSION['cliente']['movil'] = $movil;
	$movil2=preparar_entrada_bd($movil2); $_SESSION['cliente']['movil2'] = $movil2;
	$telegram_id=preparar_entrada_bd($telegram_id); $_SESSION['cliente']['telegram_id'] = $telegram_id;
	$_SESSION['cliente']['fiscales'] = $fiscales;
	$empresa=preparar_entrada_bd($empresa); $_SESSION['cliente']['empresa'] = $empresa;
	$rfc=preparar_entrada_bd($rfc); $_SESSION['cliente']['rfc'] = $rfc;
	$calle=preparar_entrada_bd($calle); $_SESSION['cliente']['calle'] = $calle;
	$numext=preparar_entrada_bd($numext); $_SESSION['cliente']['numext'] = $numext;
	$numint=preparar_entrada_bd($numint); $_SESSION['cliente']['numint'] = $numint;
	$colonia=preparar_entrada_bd($colonia); $_SESSION['cliente']['colonia'] = $colonia;
	$postal=limpiarNumero($postal); $_SESSION['cliente']['postal'] = $postal;
	$municipio=preparar_entrada_bd($municipio); $_SESSION['cliente']['municipio'] = $municipio;
	$estado=preparar_entrada_bd($estado); $_SESSION['cliente']['estado'] = $estado;
	$pais=preparar_entrada_bd($pais); $_SESSION['cliente']['pais'] = $pais;
	$representante=preparar_entrada_bd($representante); $_SESSION['cliente']['representante'] = $representante;
	$telefono=preparar_entrada_bd($telefono); $_SESSION['cliente']['telefono'] = $telefono;
	$boletin=preparar_entrada_bd($boletin);$_SESSION['cliente']['boletin'] = $boletin;

	$error = 'no';
	$mensaje= '';
//	echo '<br><br>====== ' . $error . ' <<>> ' . $mensaje . ' ======<br><br>';
//	echo strlen($telefono1);

	if (strlen($nombre) < 2) {$error = 'si'; $mensaje .= $lang['nombre corto'] . $nombre . '<br>'."\n";}
	if (strlen($apellidos) < 2) {$error = 'si'; $mensaje .= $lang['apellido corto'] . $apellidos . '<br>'."\n";}
	if (strlen($email) < 6  && $boletin == 'Si') {$error = 'si'; $mensaje .=$lang['correo corta'] . $email . '<br>'."\n";}
	if (strlen($telefono1) < 7) {$error = 'si'; $mensaje .= $lang['teléfono tener lada'] . $telefono1 . '<br>'."\n";}
//	if (strlen($telefono2) < 10) {$error = 'si'; $mensaje .='El número debe tener lada y número local: ' . $telefono2 . '<br>'."\n";}
//	if (strlen($movil) < 10) {$error = 'si'; $mensaje .='El número debe tener lada y número local: ' . $movil . '<br>'."\n";}
//	if (strlen($movil2) < 10) {$error = 'si'; $mensaje .='El número debe tener lada y número local: ' . $movil2 . '<br>'."\n";}

	if($rfc != '') {
		if (strlen($empresa) < 3) {$error = 'si'; $mensaje .=$lang['Razón Social corto'] . $empresa . '<br>'."\n";}
		if (strlen($rfc) < 12) {$error = 'si'; $mensaje .=$lang['RFC corto'].'<br>'."\n";}
		if (strlen($rfc) > 13) {$error = 'si'; $mensaje .=$lang['RFC largo'].'<br>'."\n";}
		if (strlen($calle) < 1) {$error = 'si'; $mensaje .= $lang['calle corto'] . $calle . '<br>'."\n";}
		if (strlen($numext) < 1) {$error = 'si'; $mensaje .=$lang['número exterior corto'] . $numext . '<br>'."\n";}
		if (strlen($colonia) < 2) {$error = 'si'; $mensaje .=$lang['colonia corta'] . $colonia . '<br>'."\n";}
		if (strlen($postal) != 5 || !is_numeric($postal)) {$error = 'si'; $mensaje .= $lang['CP corto'].'<br>'."\n";}
		if (strlen($municipio) < 3) {$error = 'si'; $mensaje .=$lang['municipio delegación corto'] . $municipio . '<br>'."\n";}
		if (strlen($estado) < 6 ) {$error = 'si'; $mensaje .=$lang['Selecciona Estado'].'<br>'."\n";}
//	echo '<br><br>====== ' . $error . ' ======<br><br>';
	}

	if (!isset($empresa) || $empresa == '') {$empresa = $nombre . ' ' . $apellidos;}

/*
	$preg0 = "SELECT * FROM " . $dbpfx . "empresas WHERE empresa_razon_social LIKE '$empresa'";
	if (isset($rcf) && $rfc != '') { $preg0 .= " OR empresa_rfc LIKE '$rfc'"; }
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de empresas!");
	$fila0 = mysql_num_rows($matr0);
	if($fila0 > 0) {
		while ($emp = mysql_fetch_array($matr0)) {
			if ($accion==='insertar' || $empresa_id != $emp['empresa_id']) {
				$error = 'si';
				$mensaje .= $lang['Ya existe empresa'] . $emp['empresa_razon_social'];
				if (isset($rcf) && $rfc != '') {
					$mensaje .= ' ' . $lang['RFC'] . ': ' . $emp['empresa_rfc'];
				}
				$mensaje .= '<br>'."\n";
				break;
			}
		}
	}
*/

	if ($error === 'no') {
		if($accion==='actualizar') { $parametros='empresa_id = ' . $empresa_id; } else { $parametros=''; }
		$sql_data_array = array('empresa_rfc' => $rfc,
			'empresa_calle' => $calle,
			'empresa_ext' => $numext,
			'empresa_int' => $numint,
			'empresa_colonia' => $colonia,
			'empresa_cp' => $postal,
			'empresa_municipio' => $municipio,
			'empresa_estado' => $estado,
			'empresa_pais' => $pais);
		$sql_data_array['empresa_razon_social'] = $empresa;
		$sql_data_array['empresa_representante'] = $representante;
		$sql_data_array['empresa_telefono'] = $telefono;

		if ($accion==='insertar') {
			$empresa_id = ejecutar_db($dbpfx . 'empresas', $sql_data_array, $accion);
			$str = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz123456789";
			$clave = "";
			for($i=0;$i<6;$i++) {$clave .= substr($str,rand(0,56),1);}
		} else {
			ejecutar_db($dbpfx . 'empresas', $sql_data_array, $accion, $parametros);
			$parametros='cliente_id = ' . $cliente_id;
		}
		unset($sql_data_array);
		$sql_data_array = array('cliente_empresa_id' => $empresa_id,
			'cliente_nombre' => $nombre,
			'cliente_apellidos' => $apellidos,
			'cliente_email' => $email,
			'cliente_tipo' => $tipo,
			'cliente_telefono1' => $telefono1,
			'cliente_telefono2' => $telefono2,
			'cliente_movil' => $movil,
			'cliente_movil2' => $movil2,
			'cliente_telegram_id' => $telegram_id,
			'cliente_clave' => $clave,
			'cliente_boletin' => $boletin);

		if ($accion==='insertar') {
			$cliente_id = ejecutar_db($dbpfx . 'clientes', $sql_data_array, $accion);
		} else {
			ejecutar_db($dbpfx . 'clientes', $sql_data_array, $accion, $parametros);
		}
		unset($_SESSION['cliente']);
		if($regexp == 1) {
			redirigir('reg-express.php?accion=express&orden_id=' . $orden_id);
		} else {
			redirigir('personas.php?accion=consultar&cliente_id=' . $cliente_id);
		}
	} else {
		$_SESSION['msjerror'] = $mensaje;
		if ($accion==='insertar') {
			redirigir('personas.php?accion=crear&cliente_id=' . $cliente_id);
		} else {
			redirigir('personas.php?accion=modificar&cliente_id=' . $cliente_id);
		}
	}
}

elseif ($accion==="consultar") {

	if (validaAcceso('1090000', $dbpfx) == '1' || validaAcceso('1090010', $dbpfx) == '1' || ($_SESSION['codigo'] < '60' || $_SESSION['codigo'] > '75')) {
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
	} else {
		redirigir('usuarios.php?mensaje='.$lang['Acceso NO autorizado']);
	}
//	echo 'Estamos en la sección  consulta';
	$error = 'si'; $num_cols = 0;
	if ($cliente_id!='') {
		$pregunta = "SELECT * FROM " . $dbpfx . "clientes WHERE cliente_id = '$cliente_id'";
		$error = 'no';
	} else {
		$nombre=preparar_entrada_bd($nombre);
		$apellidos=preparar_entrada_bd($apellidos);
		$email=preparar_entrada_bd($email);
		$telefono1=preparar_entrada_bd($telefono1);
		$mensaje= $lang['un dato para buscar'].'<br>';
		if ( $empresa != '' || ($nombre!='') || ($apellidos!='') || ($email!='') || ($telefono1!='')) {
			$error = 'no'; $mensaje ='';
			$pregunta = "SELECT c.*, e.empresa_razon_social FROM " . $dbpfx . "clientes c, " . $dbpfx . "empresas e WHERE c.cliente_empresa_id = e.empresa_id AND ";
			if ($nombre) {$pregunta .= "c.cliente_nombre like '%$nombre%' ";}
			if (($nombre) && ($apellidos)) {$pregunta .= "AND c.cliente_apellidos like '%$apellidos%' ";}
			elseif ($apellidos) {$pregunta .= "c.cliente_apellidos like '%$apellidos%' ";}
			if ((($nombre) || ($apellidos)) && ($email)) {$pregunta .= "AND c.cliente_email like '%$email%' ";}
			elseif ($email) {$pregunta .= "c.cliente_email like '%$email%' ";}
			if ((($email) || ($nombre) || ($apellidos)) && ($telefono1)) {$pregunta .= "AND c.cliente_telefono1 like '%$telefono1%'";}
			elseif ($telefono1) {$pregunta .= "cliente_telefono1 like '%$telefono1%'";}
			if (($email || $nombre || $apellidos || $telefono1) && $empresa) {$pregunta .= "AND e.empresa_razon_social like '%$empresa%'";}
			elseif ($empresa) {$pregunta .= "e.empresa_razon_social like '%$empresa%'";}
		}
	}
	if($_SESSION['codigo'] == '2000') {
//		$pregunta .= " AND cliente_aseguradora = '" . $_SESSION['aseg'] . "' ";
	}
	if ($error ==='no') {
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!" . $pregunta);
		$num_cols = mysql_num_rows($matriz);
	}
	if ($num_cols == 1) {
		echo '	<table cellspacing="2" cellpadding="2" border="0" style="font-size:1.2em;">' . "\n";
		while ($cliente = mysql_fetch_array($matriz)) {
			$empresa_id = $cliente['cliente_empresa_id'];
			$pregunta2 = "SELECT * FROM " . $dbpfx . "empresas WHERE empresa_id = '$empresa_id'";
			$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
			$empresa = mysql_fetch_array($matriz2);
			echo '		<tr class="cabeza_tabla"><td colspan="2" align="left">' . $lang['Cliente'] . ': ' . $empresa['empresa_razon_social'] . '</td></tr>'."\n";
			if ($retorno == '1' || $_SESSION['rol06'] == '1' || $_SESSION['rol12'] == '1' ) {
				echo '		<tr class="cabeza_tabla"><td colspan="2" align="left">'.$lang['Datos facturación'].'</td></tr>'."\n";
				echo '		<tr><td>' . $empresa['empresa_razon_social'] . '<br>'.$lang['Calle'].': ' . $empresa['empresa_calle'] . ' #' . $empresa['empresa_ext'] . ' ' . $empresa['empresa_int'] . '<br>'.$lang['Colonia'].':' . $empresa['empresa_colonia'] . ', '.$lang['Municipio'].': ' . $empresa['empresa_municipio'] . '<br>'.$lang['C.P.'].': ' . $empresa['empresa_cp'] . '. '.$lang['Estado'].': ' . $empresa['empresa_estado'] . '. '.$lang['País'].': ' . $empresa['empresa_pais'] . '.<br>'.$lang['RFC'].': ' . $empresa['empresa_rfc'] . '</td>'."\n";
				echo '			<td></td>'."\n";
				echo '		</tr>'."\n";
			}
			echo '		<tr class="cabeza_tabla"><td colspan="2" align="left">' . $lang['Contactos'] . '</td></tr>'."\n";
			echo '		<tr><td colspan="2" align="left"><strong>(';
			if($cliente['cliente_tipo'] == 1 ) {
				echo $lang['CliAseg'];
			} else {
				echo $lang['CliOtro'];
			}
			echo ') ' . $cliente['cliente_nombre'] . ' ' . $cliente['cliente_apellidos'] . '</strong></td></tr>
		<tr class="obscuro"><td>'.$lang['e-Mail'].': <span style="font-weight:bold;">' . $cliente['cliente_email'] . '</span></td><td>'.$lang['Número de cliente'].': <span style="font-weight:bold;">' . $cliente['cliente_id'] . '</span></td></tr>
		<tr class="claro"><td>'.$lang['Notificaciones por e-mail?'].' <span style="font-weight:bold;">';
		 	if($cliente['cliente_boletin'] != 'Si') { echo 'No'; } else { echo 'Sí'; }
		 	echo '</span></td><td>'.$lang['Clave de Cliente'].': <span style="font-weight:bold;">' . $cliente['cliente_clave'] . '</span></td></tr>
		<tr class="obscuro"><td>'.$lang['Teléfono Trabajo'].': <span style="font-weight:bold;">' . $cliente['cliente_telefono1'] . '</span></td><td>'.$lang['Teléfono Casa'].': <span style="font-weight:bold;">' . $cliente['cliente_telefono2'] . '</span></td></tr>
		<tr class="claro"><td>'.$lang['Celular'].': <span style="font-weight:bold;">' . $cliente['cliente_movil'] . '</span></td><td>'.$lang['Nextel'].': <span style="font-weight:bold;">' . $cliente['cliente_movil2'] . '</span></td></tr>'."\n";
			echo '				<tr class="claro"><td>'.$lang['Telegram'].': <span style="font-weight:bold;">' . $cliente['cliente_telegram_id'] . '</span></td><td></td></tr>'."\n";
			echo '		<tr class="cabeza_tabla"><td colspan="2" align="left">Acciones</td></tr>
		<tr><td colspan="2">
			<a href="ordenes.php?accion=listar&cliente_id=' . $cliente['cliente_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/ordenes-listar.png" alt="'.$lang['Ver OT'].'" title="'.$lang['Ver OT'].'"></a>&nbsp;
			<a href="previas.php?accion=consultar&cliente_id=' . $cliente['cliente_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/previos-listar.png" alt="'.$lang['Ver Previa'].'" title="'.$lang['Ver Previa'].'"></a>&nbsp;
			<a href="vehiculos.php?accion=listar&cliente_id=' . $cliente['cliente_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/listar-autos.png" alt="'.$lang['Ver Vehículos'].'" title="'.$lang['Ver Vehículos'].'"></a>&nbsp;';
			if (validaAcceso('1090000', $dbpfx) == '1' || validaAcceso('1090010', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol06'] == '1'))) {
				echo	'		 <a href="vehiculos.php?accion=crear&cliente_id=' . $cliente['cliente_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/agregar-auto.png" alt="'.$lang['Agregar Vehículos'].'" title="'.$lang['Agregar Vehículos'].'"></a>&nbsp;
			<a href="personas.php?accion=modificar&cliente_id=' . $cliente['cliente_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/cambiar-datos.png" alt="'.$lang['Modificar'].'" title="'.$lang['Modificar'].'"></a>&nbsp;
			<a href="documentos.php?accion=listar&cliente_id=' . $cliente['cliente_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/documento.png" alt="'.$lang['Ver Documentos'].'" title="'.$lang['Ver Documentos'].'"></a>&nbsp;'."\n";
			}
			echo '</td></tr>';
		}
		echo '	</table>';
	} elseif($num_cols > 1) {

		echo '			<table cellspacing="2" cellpadding="2" border="1">
				<tr class="cabeza_tabla"><td colspan="5" align="left">' . $lang['Datos de contacto'] . '</td></tr>'."\n";
		echo '				<tr><td>' . $lang['Clave de Cliente'] . '</td><td>' . $lang['Empresa'] . '</td><td>' . $lang['Nombre'] . '</td><td>' . $lang['Teléfono Trabajo'] . '</td><td>' . $lang['e-Mail'] . '</td></tr>'; $fondo = 'obscuro';
		while ($cliente = mysql_fetch_array($matriz)) {
			echo '		<tr class="' . $fondo . '">
				<td style="text-align:center;"><a href="personas.php?accion=consultar&cliente_id=' . $cliente['cliente_id'] . '">' . $cliente['cliente_id'] . '</a></td>
				<td><a href="personas.php?accion=consultar&cliente_id=' . $cliente['cliente_id'] . '">' . $cliente['empresa_razon_social'] . '</a></td>
				<td><a href="personas.php?accion=consultar&cliente_id=' . $cliente['cliente_id'] . '">' . $cliente['cliente_nombre'] . ' ' . $cliente['cliente_apellidos'] . '</a></td>
				<td>' . $cliente['cliente_telefono1'] . '</td>
				<td>' . $cliente['cliente_email'] . '</td>
			</tr>';
			if($fondo == 'obscuro') { $fondo = 'claro'; } else { $fondo = 'obscuro'; }
		}
		echo '	</table>';

	} else {
		$mensaje .=$lang['No hay datos'].'</br>';
		echo '<p>' . $mensaje . '</p>';
	}
}

elseif($accion==="verifica") {

	if (($nombre!='') || ($empresa!='') || ($apellidos!='') || ($rfc!='')) {
		if(isset($nombre) && $nombre != '') {
			$nombres = explode(' ', $nombre);
		}
		if(isset($empresa) && $empresa != '') {
			$empresas = explode(' ', $empresa);
		}
		if(isset($apellidos) && $apellidos!='') {
			$apellido = explode(' ', $apellidos);
		}
		$error = 'no'; $mensaje ='';
		$patterns = '/[áéíóúÁÉÍÓÚñÑäëïöüÄËÏÖÜ]/';
		$replace = '%';


//		$preg0 = "SELECT * FROM " . $dbpfx . "clientes WHERE ";
		$preg0 = "SELECT e.empresa_id, e.empresa_razon_social, e.empresa_rfc, c.* FROM " . $dbpfx . "empresas e, " . $dbpfx . "clientes c WHERE e.empresa_id = c.cliente_empresa_id AND ";
		if(count($nombres) > 0 || $rfc != '') {
			$preg0 .= "(";
			if(count($nombres) > 0) {
				$cuantos = count($nombres);
//				echo $cuantos;
				foreach($nombres as $i => $noms) {
					$noms = preg_replace($patterns, $replace, $noms);
					if($i == 0) {
						$preg0 .= "(";
					}
					$preg0 .= "c.cliente_nombre LIKE '%$noms%' ";
					if(($cuantos - 1) > $i) {
						$preg0 .= " OR ";
					} else {
						$preg0 .= ") ";
					}
				}
/*				$preg0 .= " OR ";
				foreach($nombres as $i => $noms) {
					$noms = preg_replace($patterns, $replace, $noms);
//					echo 'Indice: ' . $i . '<br>';
					if($i == 0) {
						$preg0 .= "(";
					}
					$preg0 .= "e.empresa_razon_social LIKE '%$noms%' ";
					if(($cuantos - 1) > $i) {
						$preg0 .= " OR ";
					} else {
						$preg0 .= ")";
					}
				} */
			}
			if(count($nombres) > 0 && $rfc != '') {
					$preg0 .= " OR ";
			}
			if($rfc != '') {
				$preg0 .= " e.empresa_rfc LIKE '%$rfc%'";
			}
			$preg0 .= ")";
		}

		if (($cuantos > 0 || $rfc != '' ) && count($apellido) > 0) {$preg0 .= " AND ";}
		if (is_array($apellido)) {
			$cuantos = count($apellido);
			$preg0 .= "(";
			foreach($apellido as $i => $apes) {
				$apes = preg_replace($patterns, $replace, $apes);
//				echo 'Indice: ' . $i . '<br>';
				if($i == 0) {
					$preg0 .= "(";
				}
				$preg0 .= "c.cliente_apellidos LIKE '%$apes%' ";
				if(($cuantos - 1) > $i) {
					$preg0 .= " OR ";
				} else {
					$preg0 .= ") ";
				}
			}
			$preg0 .= ")";
		}

		if (($cuantos > 0 || $rfc != '' ) && count($empresas) > 0) {$preg0 .= " AND ";}
		if (is_array($empresas)) {
			$cuantos = count($empresas);
			$preg0 .= "(";
			foreach($empresas as $i => $emps) {
				$emps = preg_replace($patterns, $replace, $emps);
//				echo 'Indice: ' . $i . '<br>';
				if($i == 0) {
					$preg0 .= "(";
				}
				$preg0 .= "e.empresa_razon_social LIKE '%$emps%' ";
				if(($cuantos - 1) > $i) {
					$preg0 .= " OR ";
				} else {
					$preg0 .= ") ";
				}
			}
			$preg0 .= ")";
		}
//		echo $preg0;
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de clientes! " . $preg0);
		$fila0 = mysql_num_rows($matr0);
		if($fila0 > 0) {
			while ($clie = mysql_fetch_array($matr0)) {
				$emprs[] = array($clie['cliente_empresa_id'], $clie['empresa_razon_social'], $clie['empresa_rfc'], $clie['cliente_nombre'], $clie['cliente_apellidos'], $clie['cliente_telefono1'], $clie['cliente_email'], $clie['cliente_id']);
			}
		}
	}

	$resultados = count($emprs);
	$renglones = 25;
	$paginas = (round(($resultados / $renglones) + 0.49999999) - 1);

// ------ Si no existe $pagina la página se coloca en 0
	if(!isset($pagina)) { $pagina = 0;}

// ------ Calculamos los resultados que deben de ser consultados ---
	$inicial = $pagina * $renglones;
	$final = $inicial + $renglones;
	$indicador = $inicial + 1;


	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	echo '			<div class="page-content">'."\n";

	unset($_SESSION['cliente']);

	if($express == 1) {
		echo '			<form action="reg-express.php?accion=express" method="post" enctype="multipart/form-data">'."\n";
		echo '				<input type="hidden" name="orden_id" value="' . $orden_id . '" />
				<input type="hidden" name="oid" value="' . $oid . '" />
				<input type="hidden" name="placas" value="' . $placas . '" />'."\n";
	} else {
		echo '			<form action="personas.php?accion=' . $origen . '" method="post" enctype="multipart/form-data">'."\n";
	}
	echo '			<table cellpadding="2" cellspacing="2" border="1">'."\n";
	if($scid == '') { $cols = 8; } else { $cols = 5; }
	echo '				<tr><td colspan="'.$cols.'">
						<h3>Descripción</h3>
							Antes de agregar un nueva Empresa (Cliente) o un Contacto de un Cliente (Persona responsable del vehículo) existente, por favor revisa si ya existe en tu base de datos.
						<h3>Instrucciones:</h3>
							1.- Por favor localice en el listado la empresa o la persona que coincida con los datos buscados.<br>
							2.- Si encontro la persona, por favor seleccione la persona con el selector a la izquierda del nombre de la persona.<br>
							3.- Ahora presione el botón "Nuevo Vehículo para este Contacto" y cambiará a la pantalla de captura de datos del vehículo.<br>Una vez que termine, se mostrará la pantalla del Resumen de la Orden de Trabajo recien creada.<br><br>
							4.- Si no encontró a la persona pero sí encotró a la Empresa,  por favor seleccione la empresa con el selector a la izquierda del nombre de la empresa.<br>
							5.- Ahora presione el botón "Nuevo Contacto para esta Empresa" y cambiará a la pantalla de captura de datos de la nueva Persona.<br>
							Cuando termine de capturar los datos de la persona, en esa misma pantalla presione el botón "Agregar Vehículo" y se le mostrará la pantalla de captura de datos del vehículo.<br>
							Al terminar de capturar los datos del vehículo se mostrará la pantalla del Resumen de la Orden de Trabajo recien creada.
							</td></tr>'."\n";
	echo '
									<tr class="cabeza_tabla">
										<td colspan="'.$cols.'" style="text-align:right;">';
	echo '													Mostrando la página ' . ($pagina + 1) . ' de ' . ($paginas + 1) . ' páginas de Resultados.&nbsp; | &nbsp;
															<a href="personas.php?accion=verifica&pagina=0&nombre=' . $nombre . '&apellidos=' . $apellidos . '&rfc=' . $rfc . '&origen=' . $origen . '&scid=' . $scid . '">' . $lang['Primera'] . ' | </a>'."\n";
	if($pagina > 0) {
		echo '											<a href="personas.php?accion=verifica&pagina=' . ($pagina - 1) . '&nombre=' . $nombre . '&apellidos=' . $apellidos . '&rfc=' . $rfc . '&origen=' . $origen . '&scid=' . $scid . '">' . $lang['Anterior'] . ' | </a>'."\n";
	}
	if(($pagina + 1) < $paginas){
		echo '											<a href="personas.php?accion=verifica&pagina=' . ($pagina + 1) . '&nombre=' . $nombre . '&apellidos=' . $apellidos . '&rfc=' . $rfc . '&origen=' . $origen . '&scid=' . $scid . '">' . $lang['Siguiente'] . ' | </a>'."\n";
	}
	echo '											<a href="personas.php?accion=verifica&pagina=' . $paginas . '&nombre=' . $nombre . '&apellidos=' . $apellidos . '&rfc=' . $rfc . '&origen=' . $origen . '&scid=' . $scid . '">' . $lang['Ultima'] . '</a>'."\n";
	echo '
										</td>
									</tr>'."\n";

	echo '				<tr><td colspan="3" class="area6" style="text-align: center;">Selecciona esta Empresa</td>';
	if($scid == '') {
		echo '<td colspan="5" class="area7" style="text-align: center;">Selecciona este Contacto</td>';
	}
	echo '</tr>'."\n";
	echo '				<tr><td class="area6" style="text-align:center;">Selección</td><td>Nombre de  Empresa</td><td>RFC</td>';
	if($scid == '') {
		echo '<td class="area7" style="text-align: center;">Selección</td><td>Nombre</td><td>Apellidos</td><td>Teléfono</td><td>Email</td>';
	}
	echo '</tr>'."\n";
	$fondo = 'claro';
	if(count($emprs) > 0) {
		for($i = $inicial; $i < ($inicial + $renglones) ; $i++) {
			if(count($emprs) <= $i) { break; }
			echo '				<tr class="' . $fondo . '"><td class="area6" style="text-align:center;"><input type="radio" name="dato" value="' . $emprs[$i][0] . '" /></td><td>(' . $emprs[$i][0] . ') ' . $emprs[$i][1] . '</td><td>' . $emprs[$i][2] . '</td>';
			if($scid == '') {
				echo '<td class="area7" style="text-align:center;"><input type="radio" name="dato" value="' . $emprs[$i][7] . '" /></td><td>(' . $emprs[$i][7] . ') ' . $emprs[$i][3] . '</td><td>' . $emprs[$i][4] . '</td><td>' . $emprs[$i][5] . '</td><td>' . $emprs[$i][6] . '</td>';
			}
			echo '</tr>'."\n";
				if($fondo == 'claro') { $fondo = 'obscuro';} else { $fondo = 'claro'; }
		}

		echo '				<tr><td colspan="3" class="area6" style="text-align:left;"><button type="submit" name="tipodato" value="1">Nuevo Contacto para esta Empresa</button><input type="hidden" name="scid" value="' . $scid . '"></td>';
		if($scid == '') {
			echo '<td colspan="5" class="area7" style="text-align:left;"><button type="submit" name="tipodato" value="2">Nuevo Vehículo para este Contacto</button></td>';
		}
		echo '</tr>'."\n";
		echo '									<tr class="cabeza_tabla">
										<td colspan="'.$cols.'" style="text-align:right;">';
		echo '													Mostrando la página ' . ($pagina + 1) . ' de ' . ($paginas + 1) . ' páginas de Resultados.&nbsp; | &nbsp;
															<a href="personas.php?accion=verifica&pagina=0&nombre=' . $nombre . '&apellidos=' . $apellidos . '&rfc=' . $rfc . '&origen=' . $origen . '&scid=' . $scid . '">' . $lang['Primera'] . ' | </a>'."\n";
		if($pagina > 0) {
			echo '											<a href="personas.php?accion=verifica&pagina=' . ($pagina - 1) . '&nombre=' . $nombre . '&apellidos=' . $apellidos . '&rfc=' . $rfc . '&origen=' . $origen . '&scid=' . $scid . '">' . $lang['Anterior'] . ' | </a>'."\n";
		}
		if(($pagina + 1) < $paginas){
			echo '											<a href="personas.php?accion=verifica&pagina=' . ($pagina + 1) . '&nombre=' . $nombre . '&apellidos=' . $apellidos . '&rfc=' . $rfc . '&origen=' . $origen . '&scid=' . $scid . '">' . $lang['Siguiente'] . ' | </a>'."\n";
		}
		echo '											<a href="personas.php?accion=verifica&pagina=' . $paginas . '&nombre=' . $nombre . '&apellidos=' . $apellidos . '&rfc=' . $rfc . '&origen=' . $origen . '&scid=' . $scid . '">' . $lang['Ultima'] . '</a>'."\n";
		echo '
										</td>
									</tr>'."\n";

	}
	if($scid == '') {
		echo '				<tr><td colspan="'.$cols.'">7.- Si no localiza la Empresa o Contacto, antes de crear un nuevo registro, por favor busque de nuevo con menos datos, es decir, sólo el nombre o sólo el apellido, o sólo el nombre de la empresa o sólo el RFC. También puede escribir su busqueda con una fracción del dato, por ejemplo: Si busca un contacto con el apellido -- Rodríguez -- puede utilizar -- Rod -- y si existen Contactos con apellidos Rodríguez o Rodriguez o Rodrigues o Rodeo, etc...<br>
		8.- Si en definitiva no existe la Empresa, por favor presione el botón del fondo: "Nueva Empresa, Contacto y Vehículo", entonces el sistema cambiará a la pantalla de captura de datos de la nueva Empresa y Contacto. Cuando termine de capturar los datos de la persona, en esa misma pantalla presione el botón "Agregar Vehículo" y se le mostrará la pantalla de captura de datos del vehículo.</td></tr>'."\n";
		echo '				<tr><td colspan="'.$cols.'" class="areaotra" style="text-align:left;"><button type="submit" name="eid" value="nva">Nueva Empresa, Contacto y Vehículo</button></td></tr>'."\n";
	}
	echo '			</table></form>'."\n";
}

elseif ($accion==="cuentasxcobrar") {

	$funnum = 1125035;

	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1') {
		$mensaje = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	$consulta = "SELECT aseguradora_id, aseguradora_nic FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_nic";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
	while ($aseg = mysql_fetch_array($arreglo)) {
		$ases[$aseg['aseguradora_id']][1] = $aseg['aseguradora_nic'];
	}

	$fondo = 'claro';
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	if($asegid == '0') {
		$preg2 = "SELECT f.* FROM " . $dbpfx . "facturas_por_cobrar f, " . $dbpfx . "clientes c, " . $dbpfx . "empresas e WHERE f.fact_cobrada < '2' AND f.fact_tipo < 3 AND f.cliente_id = c.cliente_id AND c.cliente_empresa_id = e.empresa_id ORDER BY ";
		if($sort == 1) {
			$preg2 .= " f.fact_num ";
		} elseif($sort == 2) {
			$preg2 .= " f.fact_num DESC";
		} elseif($sort == 3) {
			$preg2 .= " e.empresa_razon_social, c.cliente_nombre ";
		} elseif($sort == 4) {
			$preg2 .= " e.empresa_razon_social DESC, c.cliente_nombre DESC";
		} else {
			$preg2 .= " e.empresa_razon_social,c.cliente_nombre";
		}
		$asesort = 'asegid=0';
	} else {
		$preg2 = "SELECT * FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_cobrada < '2' AND fact_tipo < 3 AND ";
		if(isset($asegid) && $asegid != 'aseg') {
			$preg2 .= "aseguradora_id = '$asegid' ";
		} else {
			$preg2 .= "aseguradora_id > '0' ";
		}
		$preg2 .= "ORDER BY ";
		if($sort == 1) {
			$preg2 .= " fact_num ";
		} elseif($sort == 2) {
			$preg2 .= " fact_num DESC";
		} elseif($sort == 3) {
			$preg2 .= " aseguradora_id, fact_fecha_emision ";
		} elseif($sort == 4) {
			$preg2 .= " aseguradora_id DESC, fact_fecha_emision";
		} else {
			$preg2 .= " aseguradora_id,fact_fecha_emision";
		}
		$asesort = 'asegid=aseg';
	}
	//echo $preg2;
	$matr2 = mysql_query($preg2);
	$cobrada = 0; $fact_cob = 0; $dedu_cob = 0; $dedu_no = 0; $fact_num = ''; $fact_fech = ''; $dedu_num = ''; $fech_cob = ''; $fech_deducob = ''; $txc = 0; $tyc = 0; $tve = 0;

	if($export == 1){ // ---- Hoja de calculo ----

		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'CUENTAS POR COBRAR: ' . $nombre_agencia;

		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("VEHÍCULOS ENTREGADOS")
					->setKeywords("AUTOSHOP EASY");

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($celda, $titulo);

		// ------ ENCABEZADOS ---
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A4", "Aseguradora o Cliente")
					->setCellValue("B4", "Factura o Remisión")
					->setCellValue("C4", "# de Factura")
					->setCellValue("D4", "Fecha de Emisión de Factura")
					->setCellValue("E4", "Fecha Contrarecibo")
					->setCellValue("F4", "Días de Crédito")
					->setCellValue("G4", "Días para Cobro")
					->setCellValue("H4", "Fecha Vence")
					->setCellValue("I4", "Total")
					->setCellValue("J4", "Cobrado")
					->setCellValue("K4", "Fecha de Cobro")
					->setCellValue("L4", "Por Cobrar")
					->setCellValue("M4", "Importe Vencido");
		$z= 5;


	}
	else{ // ---- HTML ----
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';

		if($asegid == ''){ $asegid = 'aseg'; $cliente = 'TODOS LOS CONVENIOS';}

		if($asegid != 'aseg'){
			$cliente = $ases[$asegid][1];
		}

		if($asegid == '0'){
			$cliente = 'PARTICULARES';
		}

		echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-sm-12">
			<div class="content-box-header">
				<div class="panel-title">
  					<h2>CUENTAS POR COBRAR ' . $cliente . '</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-3">
			<a href="personas.php?accion=cuentasxcobrar&export=1&asegid=' . $asegid . '&sort=' . $sort . '">
				<img src="idiomas/' . $idioma . '/imagenes/hoja-calculo.png" alt="Exportar" border="0">
			</a>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
					<tr>
						<form action="personas.php?accion=cuentasxcobrar" method="post" enctype="multipart/form-data" name="año">
						<th colspan="5">
							<big>Desglose: </big>
							<select name="asegid" size="1" onchange="document.año.submit()";>'."\n";

		if($asegid == ''){
			$selected = 'selected';
		}

		echo '
								<option value="" ' . $selected . '>
									CONVENIO
								</option>'."\n";

		foreach($ases as $key => $val){

			echo '
								<option value="' .  $key . '">
									' . $val['1'] . '
								</option>'."\n";
		}

		echo '
							</select>
							<a href="personas.php?accion=cuentasxcobrar&asegid=aseg"><button type="button" class="btn btn-primary">Aseguradoras</button></a> <a href="personas.php?accion=cuentasxcobrar&asegid=0"><button type="button" class="btn btn-success">Particulares</button></a> <a href="personas.php?accion=cxcglobal"><button type="button" class="btn btn-danger">Resumen Global</button></a>
						</th>
						</form>
						<th colspan="8">
							<big><b>Cuentas por Cobrar al ' . date('Y-m-d') . '</b></big>
						</th>
					</tr>
					<tr>
						<th>
							<big>
								Aseguradora o Cliente<br>
								<a href="personas.php?accion=cuentasxcobrar&' . $asesort . '&sort=3"><img src="imagenes/ordenar-asc.png" alt="Ordenar Ascendente" title="Ordenar Ascendente"></a>
								<a href="personas.php?accion=cuentasxcobrar&' . $asesort . '&sort=4"><img src="imagenes/ordenar-desc.png" alt="Ordenar Decendente" title="Ordenar Decendente"></a>
							</big>
						</th>
						<th>
							<big>
								Factura o<br>Remisión
							</big>
						</th>
						<th>
							<big>
								# de Factura<br>
								<a href="personas.php?accion=cuentasxcobrar&' . $asesort . '&sort=1"><img src="imagenes/ordenar-asc.png" alt="Ordenar Ascendente" title="Ordenar Ascendente"></a>
								<a href="personas.php?accion=cuentasxcobrar&' . $asesort . '&sort=2"><img src="imagenes/ordenar-desc.png" alt="Ordenar Decendente" title="Ordenar Decendente"></a>
							</big>
						</th>
						<th>
							<big>Fecha de<br>Emisión<br>de Factura</big>
						</th>
						<th>
							<big>Fecha Contrarecibo</big>
						</th>
						<th>
							<big>Días de<br>Crédito</big>
						</th>
						<th>
							<big>Días para<br>Cobro</big>
						</th>
						<th>
							<big>Fecha Vence</big>
						</th>
						<th>
							<big>Total</big>
						</th>
						<th>
							<big>Cobrado</big>
						</th>
						<th>
							<big>Fecha de Cobro</big>
						</th>
						<th>
							<big>Por Cobrar</big>
						</th>
						<th>
							<big>Importe Vencido</big>
						</th>
					</tr>'."\n";
	}

		while($fact = mysql_fetch_array($matr2)) {
			if($fact['aseguradora_id'] > 0) {
				$nombre = $ase[$fact['aseguradora_id']][1];
			} elseif($fact['fact_tipo'] == '1') {
				$preg3 = "SELECT e.empresa_razon_social FROM " . $dbpfx . "clientes c, " . $dbpfx . "empresas e WHERE c.cliente_id = '" . $fact['cliente_id'] . "' AND e.empresa_id = c.cliente_empresa_id";
				$matr3 = mysql_query($preg3);
				$cliente = mysql_fetch_array($matr3);
				$nombre = $cliente['empresa_razon_social'];
			} else {
				$preg3 = "SELECT cliente_nombre, cliente_apellidos FROM " . $dbpfx . "clientes WHERE cliente_id = '" . $fact['cliente_id'] . "'";
				$matr3 = mysql_query($preg3);
				$cliente = mysql_fetch_array($matr3);
				$nombre = $cliente['cliente_nombre'] . ' ' . $cliente['cliente_apellidos'];
			}
			unset($fa);
			$monto_cobrado = 0;
			$tfech_rec = 0;
			$tfech_prog = 0;
			if($fact['fact_tipo'] == 1) { $tipo = 'Factura'; } else { $tipo = 'Remisión'; }
			$fa['num'] = $fact['fact_num'];
			if(!is_null($fact['fact_fecha_emision'])) {
				$fa['fech'] = date('Y-m-d', strtotime($fact['fact_fecha_emision']));
				$tfech_emi = strtotime($fa['fech']);
			}
			if(!is_null($fact['fact_fecha_recibida'])) {
				$fa['rec'] = date('Y-m-d', strtotime($fact['fact_fecha_recibida']));
				$tfech_rec = strtotime($fa['rec']);
			}
			if(!is_null($fact['fact_fecha_programada'])) {
				$fa['prog'] = date('Y-m-d', strtotime($fact['fact_fecha_programada']));
				$tfech_prog = strtotime($fa['prog']);
			}
			$dias_credito = intval(($tfech_prog - $tfech_emi)/86400);
			$dias_cobro = intval(($tfech_prog - $hoy)/86400);

			if($fact['fact_cobrada'] == 0) {
				$preg4 = "SELECT cf.monto, c.cobro_fecha FROM " . $dbpfx . "cobros c, " . $dbpfx . "cobros_facturas cf WHERE cf.fact_id = '" . $fact['fact_id'] . "' AND c.cobro_id = cf.cobro_id ORDER BY c.cobro_fecha";
				$matr4 = mysql_query($preg4);

				while($cob = mysql_fetch_array($matr4)) {
					$monto_cobrado = $monto_cobrado + $cob['monto'];
					$fa['cob'] = date('Y-m-d', strtotime($cob['cobro_fecha']));
				}
				$mpc = $fact['fact_monto'] - $monto_cobrado;
				$txc = $txc + $fact['fact_monto'];
				$tyc = $tyc + $monto_cobrado;
				$tpc = $tpc + $mpc;

				if($export == 1){ // ---- Hoja de calculo ----

					$fa_prog = '';
					$fa_cob = '';
					$fa_rec = '';

					// --- Celdas a grabar ----
					$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
					$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
					$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z;

					$vencido = 0;
					if(strtotime($fact['fact_fecha_programada']) < $hoy) {
						$vencido = $mpc;
						$tve = $tve + $vencido;
					}

					$fa_fech = date('Y-m-d', strtotime($fa['fech']));
					$fa_fech = PHPExcel_Shared_Date::PHPToExcel( strtotime($fa_fech) );

					// --- cambiar el formato de la celda tipo fecha/date ---
					$objPHPExcel->getActiveSheet()
    							->getStyle($d)
    							->getNumberFormat()
    							->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);

					if($fa['prog'] != ''){

						$fa_prog = date('Y-m-d', strtotime($fa['prog']));
						$fa_prog = PHPExcel_Shared_Date::PHPToExcel( strtotime($fa_prog) );

						// --- cambiar el formato de la celda tipo fecha/date ---
						$objPHPExcel->getActiveSheet()
    								->getStyle($h)
    								->getNumberFormat()
    								->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
					} else{
						$fa_prog = $fa['prog'];
					}

					if($fa['cob'] != ''){

						$fa_cob = date('Y-m-d', strtotime($fa['cob']));
						$fa_cob = PHPExcel_Shared_Date::PHPToExcel( strtotime($fa_cob) );

						// --- cambiar el formato de la celda tipo fecha/date ---
						$objPHPExcel->getActiveSheet()
									->getStyle($kkk)
    								->getNumberFormat()
    								->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
					} else{
						$fa_cob = $fa['cob'];
					}

					if($fa['rec'] != ''){

						$fa_rec = date('Y-m-d', strtotime($fa['rec']));
						$fa_rec = PHPExcel_Shared_Date::PHPToExcel( strtotime($fa_rec) );

						// --- cambiar el formato de la celda tipo fecha/date ---
						$objPHPExcel->getActiveSheet()
									->getStyle($e)
    								->getNumberFormat()
    								->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
					} else{
						$fa_rec = $fa['rec'];
					}

					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($a, $nombre)
								->setCellValue($b, $tipo)
								->setCellValue($c, $fa['num'])
								->setCellValue($d, $fa_fech)
								->setCellValue($e, $fa_rec)
								->setCellValue($f, $dias_credito)
								->setCellValue($g, $dias_cobro)
								->setCellValue($h, $fa_prog)
								->setCellValue($i, $fact['fact_monto'])
								->setCellValue($j, $monto_cobrado)
								->setCellValue($kkk, $fa_cob)
								->setCellValue($l, $mpc)
								->setCellValue($m, $vencido);

					$z++;

				}
				else{ // ---- HTML ----

					if($fact['aseguradora_id'] > 0) {
						// echo 'asegid=' . $fact['aseguradora_id'];
						$regcob = 'aseguradoras.php?accion=regcobro&aseguradora_id=' . $fact['aseguradora_id'];
					} else {
						// echo 'clieid=' . $fact['cliente_id'];
						$regcob = 'entrega.php?accion=cobros&orden_id=' . $fact['orden_id'];
					}
					echo '
					<tr class="' . $fondo . '">
						<td style="text-align: left !important;">
							<a href="' . $regcob . '">' . $nombre . '</a>
						</td>
						<td>
							' . $tipo . '
						</td>
						<td>
							<a href="entrega.php?accion=cobros&orden_id=' . $fact['orden_id'] . '">' . $fa['num'] . '</a>
						</td>
						<td>
							' . $fa['fech'] . '
						</td>
						<td>
							' . $fa['rec'] . '
						</td>
						<td>
							' . $dias_credito . '
						</td>
						<td>
							' . $dias_cobro . '
						</td>
						<td>
							' . $fa['prog'] . '
						</td>
						<td style="text-align:right;">
							<b>$ ' . number_format($fact['fact_monto'],2) . '</b>
						</td>
						<td class="aut' . $fondo . '" style="text-align:right;">
							<b>$ ' . number_format($monto_cobrado,2) . '</b>
						</td>
						<td>
							' . $fa['cob'] . '
						</td>
						<td class="pre' . $fondo . '" style="text-align:right;">
							<b>$ ' . number_format($mpc,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>$ ';

					$vencido = 0;
					if(strtotime($fact['fact_fecha_programada']) < $hoy) {
						$vencido = $mpc;
						$tve = $tve + $vencido;
					}
					echo number_format($vencido,2);
					echo '
							</b>
						</td>
					</tr>'."\n";
					if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro';  }
				}

			}

			// ------ Buscar y presentar diferencias -----------------
			$preg6 = "SELECT sub_orden_id, sub_impuesto, sub_presupuesto FROM " . $dbpfx . "subordenes WHERE fact_id = '" . $fact['fact_id'] . "' AND sub_estatus < '190'";
			$matr6 = mysql_query($preg6) or die("Error: falló selección de tareas con factura. " . $preg6);
			$total_facturar = 0;
			while($dif = mysql_fetch_array($matr6)) {
				$total_facturar = $total_facturar + $dif['sub_presupuesto'] + $dif['sub_impuesto'];
			}
			$preg6 = "SELECT * FROM " . $dbpfx . "ajusadmin WHERE fact_id = '" . $fact['fact_id'] . "'";
			$matr6 = mysql_query($preg6) or die("Error: falló selección de ajustes admin. " . $preg6);
			$total_justificado = 0;
			while($dif = mysql_fetch_array($matr6)) {
				$total_justificado = $total_justificado + $dif['monto'];
			}
			$diferencia = round(($total_facturar - ($total_justificado + $fact['fact_monto'])),2);
			if($diferencia > 0) {
				$txc = $txc + $diferencia;
				$tpc = $tpc + $diferencia;

				if($export == 1){ // ---- Hoja de calculo ----

					// --- Celdas a grabar ----
					$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
					$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
					$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z;

					$fa_fech = date('Y-m-d', strtotime($fa['fech']));
					$fa_fech = PHPExcel_Shared_Date::PHPToExcel( strtotime($fa_fech) );

					// --- cambiar el formato de la celda tipo fecha/date ---
					$objPHPExcel->getActiveSheet()
    							->getStyle($d)
    							->getNumberFormat()
    							->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);

					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($a, $nombre)
								->setCellValue($b, "Diferencia")
								->setCellValue($c, $fa['num'])
								->setCellValue($d, $fa_fech)
								->setCellValue($e, "NA")
								->setCellValue($f, "NA")
								->setCellValue($g, "NA")
								->setCellValue($h, "NA")
								->setCellValue($i, $diferencia)
								->setCellValue($j, "-")
								->setCellValue($kkk, "NA")
								->setCellValue($l, $diferencia)
								->setCellValue($m, "NA");

					$z++;


				}
				else{ // ---- HTML ----
					echo '
					<tr class="' . $fondo . '">
						<td style="text-align: left !important;">
							<a href="' . $regcob . '">' . $nombre . '</a>
						</td>
						<td>
							Diferencia
						</td>
						<td>
							<a href="entrega.php?accion=cobros&orden_id=' . $fact['orden_id'] . '">' . $fa['num'] . '</a>
						</td>
						<td>
							' . $fa['fech'] . '
						</td>
						<td>
							NA
						</td>
						<td>
							NA
						</td>
						<td>
							NA
						</td>
						<td>
							NA
						</td>
						<td style="text-align:right;">
							<b>$ ' . number_format($diferencia,2) . '</b>
						</td>
						<td class="aut' . $fondo . '" style="text-align:right;">
						</td>
						<td>
							NA
						</td>
						<td class="pre' . $fondo . '" style="text-align:right;">
							<b>$ ' . number_format($diferencia,2) . '</b>
						</td>
						<td style="text-align:right;">
							NA
						</td>
					</tr>'."\n";

					if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro';  }
				}

			}
		}

		if($export == 1){ // ---- Hoja de calculo ----

			//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="cuentas-por-cobrar.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;

		}
		else{ // ---- HTML ----

			echo '
					<tr>
						<td colspan="7">
							<a href="personas.php?accion=cxcglobal"><button type="button" class="btn btn-danger">Resumen Global</button></a>';
			if($asegid != '' || $clieid != '') {
				echo ' <a href="personas.php?accion=cuentasxcobrar"><button type="button" class="btn btn-danger">Regresar a Desglose</button></a>';
			}
			echo '
						</td>
						<td colspan="2" style="text-align:right;">
							<big><b>$ ' . number_format($txc,2) . '</b></big>
						</td>
						<td style="text-align:right;">
							<big><b>$ ' . number_format($tyc,2) . '</b></big>
						</td>
						<td colspan="2" style="text-align:right;">
							<big><b>$ ' . number_format($tpc,2) . '</b></big>
						</td>
						<td style="text-align:right;">
							<big><b>$ ' . number_format($tve,2) . '</b></big>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>'."\n";
		}


}

elseif ($accion==="cxcglobal") {

	$funnum = 1125035;

	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1') {
		$mensaje = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	//  ----------------  obtener nombres de aseguradoras y id's	-------------------
	$consulta = "SELECT aseguradora_id, aseguradora_logo, aseguradora_nic, aseguradora_razon_social FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_nic";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
	while ($aseg = mysql_fetch_array($arreglo)) {
		$ase[$aseg['aseguradora_id']][0] = $aseg['aseguradora_logo'];
		$ase[$aseg['aseguradora_id']][1] = $aseg['aseguradora_nic'];
	}
	$ase[0][0] = 'particular/logo-particular.png';
	$ase[0][1] = 'Particular';
	$ase[0][2] = '0';

	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	$fondo = 'claro';
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));

	echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-sm-6">
			<div class="content-box-header">
				<div class="panel-title">
  					<h2>Cuentas por Cobrar Global (CONVENIOS Y PARTICULARES)</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
					<tr class="cabeza_tabla">
						<th colspan="3">
							<big>Cuentas por Cobrar al ' . date('Y-m-d') . '</big>
						</th>
					</tr>
					<tr>
						<th><big>Aseguradora</big></th>
						<th><big>Total por Cobrar</big></th>
						<th><big>Importe Vencido</big></th>
					</tr>'."\n";

	$total_tve = 0;
	$total_tpc = 0;

	foreach($ase as $key => $val){
		//echo 'key ' . $key . ' val ' . $val . '<br>';
		$asegid = $key;

		if($asegid == '0') {
			$preg2 = "SELECT f.* FROM " . $dbpfx . "facturas_por_cobrar f, " . $dbpfx . "clientes c, " . $dbpfx . "empresas e WHERE f.fact_cobrada < '2' AND f.fact_tipo < 3 AND f.cliente_id = c.cliente_id AND c.cliente_empresa_id = e.empresa_id ORDER BY ";
			if($sort == 1) {
				$preg2 .= " f.fact_num ";
			} elseif($sort == 2) {
				$preg2 .= " f.fact_num DESC";
			} elseif($sort == 3) {
				$preg2 .= " e.empresa_razon_social, c.cliente_nombre ";
			} elseif($sort == 4) {
				$preg2 .= " e.empresa_razon_social DESC, c.cliente_nombre DESC";
			} else {
				$preg2 .= " e.empresa_razon_social,c.cliente_nombre";
			}
			$asesort = 'asegid=0';
		} else {
				$preg2 = "SELECT * FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_cobrada < '2' AND fact_tipo < 3 AND ";
			if(isset($asegid) && $asegid != 'aseg') {
				$preg2 .= "aseguradora_id = '$asegid' ";
			} else {
				$preg2 .= "aseguradora_id > '0' ";
			}
			$preg2 .= "ORDER BY ";
			if($sort == 1) {
				$preg2 .= " fact_num ";
			} elseif($sort == 2) {
				$preg2 .= " fact_num DESC";
			} elseif($sort == 3) {
				$preg2 .= " aseguradora_id, fact_fecha_emision ";
			} elseif($sort == 4) {
				$preg2 .= " aseguradora_id DESC, fact_fecha_emision";
			} else {
				$preg2 .= " aseguradora_id,fact_fecha_emision";
			}
			$asesort = 'asegid=aseg';
		}
		//echo $preg2 . '<br>';
		$matr2 = mysql_query($preg2);
		$cobrada = 0; $fact_cob = 0; $dedu_cob = 0; $dedu_no = 0; $fact_num = ''; $fact_fech = ''; $dedu_num = ''; $fech_cob = ''; $fech_deducob = ''; $txc = 0; $tyc = 0; $tve = 0; $tpc = 0;

		while($fact = mysql_fetch_array($matr2)) {

			if($fact['aseguradora_id'] > 0) {
				$nombre = $ase[$fact['aseguradora_id']][1];
			} elseif($fact['fact_tipo'] == '1') {
				$preg3 = "SELECT e.empresa_razon_social FROM " . $dbpfx . "clientes c, " . $dbpfx . "empresas e WHERE c.cliente_id = '" . $fact['cliente_id'] . "' AND e.empresa_id = c.cliente_empresa_id";
				$matr3 = mysql_query($preg3);
				$cliente = mysql_fetch_array($matr3);
				$nombre = $cliente['empresa_razon_social'];
			} else {
				$preg3 = "SELECT cliente_nombre, cliente_apellidos FROM " . $dbpfx . "clientes WHERE cliente_id = '" . $fact['cliente_id'] . "'";
				$matr3 = mysql_query($preg3);
				$cliente = mysql_fetch_array($matr3);
				$nombre = $cliente['cliente_nombre'] . ' ' . $cliente['cliente_apellidos'];
			}
			unset($fa);
			$monto_cobrado = 0;
			$tfech_rec = 0;
			$tfech_prog = 0;
			if($fact['fact_tipo'] == 1) { $tipo = 'Factura'; } else { $tipo = 'Remisión'; }
			$fa['num'] = $fact['fact_num'];
			if(!is_null($fact['fact_fecha_emision'])) {
				$fa['fech'] = date('Y-m-d', strtotime($fact['fact_fecha_emision']));
				$tfech_emi = strtotime($fa['fech']);
			}
			if(!is_null($fact['fact_fecha_recibida'])) {
				$fa['rec'] = date('Y-m-d', strtotime($fact['fact_fecha_recibida']));
				$tfech_rec = strtotime($fa['rec']);
			}
			if(!is_null($fact['fact_fecha_programada'])) {
				$fa['prog'] = date('Y-m-d', strtotime($fact['fact_fecha_programada']));
				$tfech_prog = strtotime($fa['prog']);
			}
			$dias_credito = intval(($tfech_prog - $tfech_emi)/86400);
			$dias_cobro = intval(($tfech_prog - $hoy)/86400);

			if($fact['fact_cobrada'] == 0) {
				$preg4 = "SELECT cf.monto, c.cobro_fecha FROM " . $dbpfx . "cobros c, " . $dbpfx . "cobros_facturas cf WHERE cf.fact_id = '" . $fact['fact_id'] . "' AND c.cobro_id = cf.cobro_id ORDER BY c.cobro_fecha";
				$matr4 = mysql_query($preg4);

				while($cob = mysql_fetch_array($matr4)) {
					$monto_cobrado = $monto_cobrado + $cob['monto'];
					$fa['cob'] = date('Y-m-d', strtotime($cob['cobro_fecha']));
				}
				$mpc = $fact['fact_monto'] - $monto_cobrado;
				$txc = $txc + $fact['fact_monto'];
				$tyc = $tyc + $monto_cobrado;
				$tpc = $tpc + $mpc;

				$vencido = 0;
				if(strtotime($fact['fact_fecha_programada']) < $hoy) {
					$vencido = $mpc;
					$tve = $tve + $vencido;
				}

			}

			// ------ Buscar y presentar diferencias -----------------
			$preg6 = "SELECT sub_orden_id, sub_impuesto, sub_presupuesto FROM " . $dbpfx . "subordenes WHERE fact_id = '" . $fact['fact_id'] . "' AND sub_estatus < '190'";
			$matr6 = mysql_query($preg6) or die("Error: falló selección de tareas con factura. " . $preg6);
			$total_facturar = 0;
			while($dif = mysql_fetch_array($matr6)) {
				$total_facturar = $total_facturar + $dif['sub_presupuesto'] + $dif['sub_impuesto'];
			}
			$preg6 = "SELECT * FROM " . $dbpfx . "ajusadmin WHERE fact_id = '" . $fact['fact_id'] . "'";
			$matr6 = mysql_query($preg6) or die("Error: falló selección de ajustes admin. " . $preg6);
			$total_justificado = 0;
			while($dif = mysql_fetch_array($matr6)) {
				$total_justificado = $total_justificado + $dif['monto'];
			}
			$diferencia = round(($total_facturar - ($total_justificado + $fact['fact_monto'])),2);
			if($diferencia > 0) {
				$txc = $txc + $diferencia;
				$tpc = $tpc + $diferencia;
			}


		}

		echo '
					<tr class="' . $fondo . '">
						<td style="text-align: left !important;">
							<big>
								<a href="personas.php?accion=cuentasxcobrar&asegid=' . $key . '">' . $val['1'] . '</a>
								<img src="' . $val['0'] . '" alt="" height="20" >
							</big>
						</td>
						<td style="text-align:right;">
							<big><b>$' . number_format($tpc,2) . '</b></big>
						</td><td style="text-align:right;">
							<big><b>$' . number_format($tve,2) . '</b></big>
						</td>
					</tr>'."\n";
		if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }

		$total_tpc = $total_tpc + $tpc;
		$total_tve = $total_tve + $tve;

	}

		echo '
					<tr>
						<td style="text-align: left !important;">
							<big><a href="personas.php?accion=cuentasxcobrar">Ir a Desglose General</a></big>
						</td>
						<td style="text-align:right;">
							<big><b>$' . number_format($total_tpc,2) . '</b></big>
						</td><td style="text-align:right;">
							<big><b>$' . number_format($total_tve,2) . '</b></big>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<br>
</div>'."\n";

}

elseif ($accion==="exportarclientes") {


    $preg = "SELECT * FROM " . $dbpfx . "clientes";
	$matr = mysql_query($preg) or die("ERROR: Fallo selección SubOrden!");
    $celda = 'C1';
    $titulo = 'Clientes ' . $nombre_agencia;



	// -------------------   Creación de Archivo Excel   ----------------------------------
    require_once ('Classes/PHPExcel.php');
    $objReader = PHPExcel_IOFactory::createReader('Excel5');
    $objPHPExcel = $objReader->load("parciales/export.xls");
    $objPHPExcel->getProperties()->setCreator("AutoShop Easy")
				->setTitle("Listado de Clientes")
				->setKeywords("AUTOSHOP EASY");

    $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($celda, $titulo);

	// ------ ENCABEZADOS ---
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue("A4", "ID")
				->setCellValue("B4", "NOMBRE")
				->setCellValue("C4", "APELLIDOS")
				->setCellValue("D4", "TIPO")
				->setCellValue("E4", "EMAIL")
				->setCellValue("F4", "TELÉFONO")
				->setCellValue("G4", "TELÉFONO 2")
				->setCellValue("H4", "MÓVIL")
				->setCellValue("I4", "CLAVE")
				->setCellValue("J4", "ASEGURADORA");

    $z= 5;
    while($op = mysql_fetch_array($matr)){

        $a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
        $f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($a, $op['cliente_id'])
            ->setCellValue($b, $op['cliente_nombre'])
            ->setCellValue($c, $op['cliente_apellidos'])
            ->setCellValue($d, $op['cliente_tipo'])
            ->setCellValue($e, $op['cliente_email'])
            ->setCellValue($f, $op['cliente_telefono1'])
            ->setCellValue($g, $op['cliente_telefono2'])
            ->setCellValue($h, $op['cliente_movil'])
            ->setCellValue($i, $op['cliente_clave'])
            ->setCellValue($j, $op['cliente_aseguradora']);
			$z++;

    }


    // Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Reporte-Clientes.xls"');
    header('Cache-Control: max-age=0');


    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
	// -------------------   Creación de Archivo Excel   ----------------------------------


}

?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
