<?php
foreach($_POST as $k => $v){$$k=$v; } //echo $k.' -> '.$v.' | '; }
foreach($_GET as $k => $v){$$k=$v; } //echo $k.' -> '.$v.' | '; }
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/reg-express.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}


if (($accion==='insertar') || ($accion==='actualizar') || ($accion==='asignar') || ($accion==='seguro') || ($accion==='express') || ($accion==='seguro')) {
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">' ."\n";
}

if($accion==='express') {
	
	$funnum = 1120000;

	if ($_SESSION['rol06']!='1') {
		redirigir('usuarios.php?mensaje='.$lang['acceso_error']);
	}

	$preg = "SELECT orden_id, orden_cliente_id, orden_vehiculo_id, orden_vehiculo_placas, orden_vehiculo_tipo, orden_categoria FROM " . $dbpfx . "ordenes WHERE ";
	if($orden_id > 0) {
		$preg .= "orden_id = '" . $orden_id . "' ";
	} elseif($oid > 0) {
		$preg .= "oid = '" . $oid . "' ";
	} else {
		$_SESSION['index']['mensaje'] = $lang['no_id'];
		redirigir('index.php');
	}
	$preg .= "AND orden_estatus < '90' ";
	$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
	$ord = mysql_fetch_array($matr);
	$fila = mysql_num_rows($matr);
	$orden_id = $ord['orden_id'];
	if($fila == 1 && $ord['orden_vehiculo_placas']!= '') {
		$preg0 = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_placas = '".$ord['orden_vehiculo_placas']."'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
		$filas = mysql_num_rows($matr0);
		if($filas == 1) {
			$veh = mysql_fetch_array($matr0);
			$vehiculo_id = $veh['vehiculo_id'];
			$existe = 1;
			$preg1 = "SELECT * FROM " . $dbpfx . "clientes WHERE cliente_id = '".$ord['orden_cliente_id']."'";
//			echo $preg1;
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion!");
			$clie = mysql_fetch_array($matr1);
			$cliente_id = $clie['cliente_id'];
			$empresa_id = $clie['cliente_empresa_id'];
			$preg2 = "SELECT * FROM " . $dbpfx . "empresas WHERE empresa_id = '".$empresa_id."'";
//			echo $preg2;
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo seleccion!");
			$empresa = mysql_fetch_array($matr2);
		} elseif($filas > 1) {
			$_SESSION['msjerror'] = 'Existe más de un vehículo con las mismas placas, favor de remover el duplicado.';
			redirigir('vehiculos.php?accion=consultar&placas=' . $ord['orden_vehiculo_placas']);
		}
	}

	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">' ."\n";

		
	// <form action="/cgi-bin/test.cgi" name="myForm" onsubmit="return(validate());">
	echo '			<form action="reg-express.php?accion=insertar" id="rapida" name="rapida" method="post" enctype="multipart/form-data">'."\n";
	echo '			<table cellpadding="0" cellspacing="0" border="0" width="100%">';
	if($_SESSION['exp']['msj_vehiculo'] != '') {
		echo '<tr><td colspan="3"><span class="alerta">' . $_SESSION['exp']['msj_vehiculo'] . '</span></td></tr>';
	}
	unset($_SESSION['exp']);
	$_SESSION['exp'] = array();

	echo '				<tr>
					<td valign="top" width="33%">
						<div class="obscuro espacio">
							<h3>' . $lang['vehiculo'] . '</h3>
								<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="100%">
									<tr><td colspan="3"><span class="alerta">' . $_SESSION['exp']['msj_vehiculo'] . '</span></td></tr>
									<tr class="cabeza_tabla"><td colspan="2">' . $encabezado . '</td></tr>'."\n";
	if($confolio == 1) {
		echo '									<tr><td>Orden de Trabajo</td><td colspan="2"><input type="text" name="ordenid" size="18" maxlength="11" value="' . $orden_id . '" /></td></tr>'."\n";
	}
	echo '									<tr><td>' . $lang['Placa'] . '</td><td colspan="2"><input type="text" name="placas" size="10" maxlength="15" value="';
	echo ($veh['vehiculo_placas']) ? $veh['vehiculo_placas'] : $ord['orden_vehiculo_tipo'];
	echo '" /></td></tr>'."\n";
	echo '									<tr><td>' . $lang['serie'] . '</td><td colspan="2"><input type="text" name="serie" size="18" maxlength="60" value="';
//	echo ($veh['vehiculo_serie']) ? $veh['vehiculo_serie'] : $ord['orden_vehiculo_tipo'];
	echo $veh['vehiculo_serie'];
	echo '" /></td></tr>'."\n";

	if($valor['UsarMarcas'][0] == 1) {
		echo '		<tr><td>' . $lang['marca'] . '</td><td style="text-align:left;">'."\n";
		echo '			<select name="marca" size="1" onchange="document.filtro.submit()";>'."\n";
		echo '				<option value="0">Seleccione Marca</option>'."\n";

// ------ Conectando a ASEBase para obtener datos de Marcas y Modelos de Vehículos ---------------

		mysql_close();
		mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
		mysql_select_db('ASEBase') or die('Falló la seleccion la DB');
		
		$preg1 = "SELECT marca_id, marca_nombre FROM marcas ORDER BY marca_orden";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de marcas! " . $preg1);
		while ($ma = mysql_fetch_array($matr1)) {
			echo '				<option value="' . $ma['marca_nombre'] . '"';
			if(($_SESSION['vehiculo']['marca'] == '' && $vehiculo['vehiculo_marca'] == $ma['marca_nombre']) || ($_SESSION['vehiculo']['marca'] == $ma['marca_nombre'])) { echo ' selected="selected" '; $marca = $ma['marca_id']; }
			echo '>' . $ma['marca_nombre'] . '</option>'."\n";
		}
		echo '			</select>'."\n";
		echo '		</td></tr>'."\n";
		echo '		<tr><td>' . $lang['tipo'] . '</td><td style="text-align:left;">'."\n";
		echo '			<select name="tipo" size="1">'."\n";
		echo '				<option value="0">Seleccione Modelo</option>'."\n";
		$preg2 = "SELECT modelo_id, modelo_nombre FROM modelos WHERE marca_id = '" . $marca . "' ORDER BY modelo_nombre";
//		echo 'MO -> ' . $preg2;
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de modelos! " . $preg2);
		while ($mod = mysql_fetch_array($matr2)) {
			echo '				<option value="' . $mod['modelo_nombre'] . '"';
			if(($_SESSION['vehiculo']['tipo'] == '' && $vehiculo['vehiculo_tipo'] == $mod['modelo_nombre']) || ($_SESSION['vehiculo']['tipo'] == $mod['modelo_nombre'])) { echo ' selected="selected" '; }
			echo '>' . $mod['modelo_nombre'] . '</option>'."\n";
		}
		echo '				<option value="OTRO TIPO">OTRO TIPO</option>'."\n";
		echo '			</select>'."\n";
		mysql_close();
		
// ------ Cierre de ASEBase de datos comunes -----------------------------		
		
		echo '		</td></tr>'."\n";
	} else {
		echo '									<tr><td>' . $lang['marca'] . '</td><td colspan="2"><input type="text" name="marca" size="18" maxlength="60" value="' . $veh['vehiculo_marca'] . '"/></td></tr>
									<tr><td>' . $lang['tipo'] . '</td><td colspan="2"><input type="text" name="tipo" size="18" maxlength="40" value="' . $veh['vehiculo_tipo'] . '" /></td></tr>'."\n";
	}

	echo '									<tr><td>' . $lang['subtipo'] . '</td><td colspan="2"><input type="text" name="subtipo" size="18" maxlength="40" value="' . $veh['vehiculo_subtipo'] . '" /></td></tr>
									<tr><td style="text-align:center;">' . $lang['year'] . '</td><td style="text-align:center;">' . $lang['puertas'] . '</td><td  style="text-align:center;">' . $lang['color'] . '</td></tr>'."\n";
	echo '									<tr><td style="text-align:center;"><input type="text" name="modelo" size="3" maxlength="4" value="' . $veh['vehiculo_modelo'] . '" /></td><td style="text-align:center;"><input type="text" name="puertas" size="3" maxlength="6" value="' . $veh['vehiculo_puertas'] . '" /></td><td style="text-align:center;"><input type="text" name="colores" size="6" maxlength="15" value="' . $veh['vehiculo_color'] . '" /></td></tr>';
	if($regexpext == 1) {
		echo '									<tr><td style="text-align:center;">' . $lang['cilindros'] . '</td><td style="text-align:center;">' . $lang['cilindrada'] . '</td><td  style="text-align:center;">' . $lang['motor'] . '</td></tr>
									<tr><td style="text-align:center;"><input type="text" name="cilindros" size="3" maxlength="4" value="' . $veh['vehiculo_cilindros'] . '" /></td><td style="text-align:center;"><input type="text" name="litros" size="3" maxlength="6" value="' . $veh['vehiculo_litros'] . '" /></td><td style="text-align:center;"><input type="text" name="tipomotor" size="12" maxlength="32" value="' . $veh['vehiculo_tipomotor'] . '" /></td></tr>'."\n";
		
	}
	
	echo '									<tr><td colspan="2">' . $lang['docadmin'] . '<input type="file" name="orden_adm" size="6" />';
	if($adm_docs == '1') {
		echo '<input type="hidden" name="admdocs" value="1" />';  // Verificación de forzar subida de documentos
	} else {
		echo '<input type="hidden" name="admdocs" value="0" />';
	}
	echo '</td></tr>
									<tr><td colspan="2">' . $lang['docrep'] . '<input type="file" name="levante" size="6" /></td></tr>'."\n";
	echo '								</table>
						</div>
					</td>
					<td valign="top" width="33%">
						<div class="obscuro espacio">
							<h3>' . $lang['cliente'] . '</h3>
								<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="100%">'."\n";
	echo '									<tr><td>' . $lang['Nombre'] . '</td><td><input type="text" name="nombre" size="24" maxlength="120" value="' . $clie['cliente_nombre'] . '" /></td></tr> 
									<tr><td>' . $lang['Apellidos'] . '</td><td><input type="text" name="apellidos" size="24" maxlength="60" value="' . $clie['cliente_apellidos'] . '" /></td></tr>
									<tr><td>' . $lang['conductor'] . '</td><td>' . $lang['clietipo'] . '<input type="radio" name="clietipo" value="1" />' . $lang['tercero'] . '<input type="radio" name="clietipo" value="0" /></td></tr>
									<tr></tr>
									<tr><td colspan="2" style="text-align:left;">' . $lang['deseaemail'] . '<input type="checkbox" name="boletin" value="Si" checked="checked" /></td></tr>
									<tr><td>' . $lang['email'] . '</td><td><input type="text" name="email" size="24" maxlength="120" value="' . $clie['cliente_email'] . '" /></td></tr>
									<tr><td>' . $lang['Teléfono'] . '</td><td><input type="text" name="telefono1" size="24" maxlength="40" value="' . $clie['cliente_telefono1'] . '" /></td></tr>
									<tr><td>' . $lang['Otro'] . '</td><td><input type="text" name="telefono2" size="24" maxlength="40" value="' . $clie['cliente_telefono2'] . '" /></td></tr>
									<tr><td>' . $lang['Celular'] . '</td><td><input type="text" name="movil" size="24" maxlength="40" value="' . $clie['cliente_movil'] . '" /></td></tr>
									<tr><td>' . $lang['Nextel'] . '</td><td><input type="text" name="movil2" size="24" maxlength="40" value="' . $clie['cliente_movil2'] . '" /></td></tr>
								</table>'."\n";
	echo '						</div>'."\n";
	if($empresa_id != '') {
		foreach($empresa as $k => $v) {
			echo '<input type="hidden" name="'.$k.'" value="'.$v.'">'."\n";
		}
	}
					
	echo '					</td>
					<td valign="top" width="33%">
						<div class="obscuro espacio">'."\n";
	echo '							<h3>' . $lang['Tipo de Servicio'] . '</h3>
								<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="100%">'."\n";
	echo '									<tr><td colspan="2" style="text-align:left;">
										<select name="asesor" size="1">
											<option value="Seleccione" >' . $lang['Seleccione Asesor'] . '</option>'."\n";
	$pregunta2 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE codigo = '30' AND acceso ='0' AND activo ='1' ";
	$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
	while($usuario = mysql_fetch_array($matriz2)) {
		echo '											<option value="' . $usuario['usuario'] . '" '; echo ($_SESSION['exp']['asesor'] == $usuario['usuario']) ? 'selected="1"' : ''; echo '>' . $usuario['nombre'] . ' ' . $usuario['apellidos'] . '</option>'."\n";
	}
	echo '										</select>
									</td></tr>
									<tr>
										<td valign="top" colspan="2">' . $lang['Tipo de Servicio'] . '</td>
									</tr>
									<tr>
										<td valign="top">' . $lang['Directo'] . '<br><input type="radio" name="servicio" id="ts1" value="1" /><label for="ts1" >' . $lang['os1'] . '</label>
											<input type="radio" name="servicio" id="ts2" value="2" /><label for="ts2" >' . $lang['os2'] . '</label>
											<input type="radio" name="servicio" id="ts3" value="3" /><label for="ts3" >' . $lang['os3'] . '</label></td><td>
											<strong>' . $lang['servicio'] . '</strong><br><input type="radio" name="servicio" id="ts4" value="4" /><label for="ts4" >' . $lang['os4'] . '</label>
										</td>
									</tr>
									<tr><td colspan="2">' . $lang['Garantía'] . '<input type="text" name="garantia" size="6" />' . $lang['cualot'] . '</td></tr>'."\n";
	echo '								</table>'."\n";
	echo '						</div>'."\n";

	echo '						<div class="obscuro espacio">
							<h3>' . $lang['Categoría de Servicio'] . '</h3>
								<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="100%">'."\n";
	echo '									<tr>
										<td colspan="2" style="text-align:left;">
											<select name="categoria" size="1">'."\n";
	for($se=1;$se<=4;$se++){
		echo '			<option value="' . $se . '"';  
		if($_SESSION['exp']['categoria'] !='' && $_SESSION['exp']['categoria'] == $se) { echo ' selected="selected" '; }
		elseif($_SESSION['exp']['categoria'] == '' && $ord['orden_categoria'] == $se) { echo ' selected="selected" '; }
		echo '>' . constant('CATEGORIA_DE_REPARACION_' .$se) . '</option>'."\n";
	}
	echo '											</select>
										</td>
									</tr>'."\n";
	echo '		<tr>
			<td>' . $lang['donde'] . '</td>
			<td style="text-align:left;">
				<select name="espacio" size="1">' . "\n";
//	echo '					<option value="" >Seleccionar... </option>' . "\n";
	echo '					<option value="Zona de Espera" '; echo ($_SESSION['exp']['espacio'] == 'Zona de Espera') ? 'selected="1"' : ''; echo '>' . $lang['En Taller'] . '</option>' . "\n";
	echo '					<option value="Transito" '; echo ($_SESSION['exp']['espacio'] == 'Transito') ? 'selected="1"' : ''; echo '>' . $lang['Tránsito'] . '</option>' . "\n";
	echo '				</select></td></tr>'."\n";
	echo '									<tr><td>' . $lang['Torre'] . '</td><td style="text-align:left;"><input type="text" name="torre" size="4" maxlength="30" /></td></tr>'."\n";
	echo '								</table>
						</div>'."\n";

	echo '					</td>
				</tr>'."\n";
	echo '				<tr><td colspan="3" style="text-align:right;"><input type="submit" name="valida_placas" value="Enviar" onclick="validarExp(rapida);return false;" /></td></tr>';
	echo '				</table>
				<input type="hidden" name="existe" value="'.$existe.'">
				<input type="hidden" name="cliente_id" value="'.$cliente_id.'">
				<input type="hidden" name="vehiculo_id" value="'.$vehiculo_id.'">
				<input type="hidden" name="empresa_id" value="'.$empresa_id.'">
				<input type="hidden" name="orden_id" value="'.$orden_id.'">
				<input type="hidden" name="oid" value="'.$oid.'">
			</form>';
	unset($_SESSION['exp']);
}

elseif($accion==='insertar') {
	
	$funnum = 1120005;
	
//	echo 'Estamos en la sección inserta.<br>';
	if ($_SESSION['rol06']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	if($confolio == 1) {
		$orden_id = limpiarNumero($ordenid);
		$preg01 = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matr01 = mysql_query($preg01) or die("ERROR: Fallo búsqueda de ordenes!");
		$fila = mysql_num_rows($matr01);
		if($fila > 0) {
			$_SESSION['exp']['msj_vehiculo'] ='Ya existe otra Orden de Trabajo con el número ' . $orden_id . '.<br>';
			redirigir('reg-express.php?accion=express&oid=' . $oid);
		}
	}

	if($existe != 1) { 
		$preg = "SELECT vehiculo_id FROM " . $dbpfx . "vehiculos WHERE vehiculo_placas = '$placas' OR vehiculo_serie ='$serie'";
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de Vehículos!");
		$fila = mysql_num_rows($matr);
		if($fila > 0) {
			$_SESSION['exp']['msj_vehiculo'] ='Ya existe otro vehículo con el mismo número de placas o VIN.<br>';
			if($confolio == 1) {
				redirigir('reg-express.php?accion=express&oid=' . $oid);
			} else {
				redirigir('reg-express.php?accion=express&orden_id=' . $orden_id);
			}
		}
	}

	unset($_SESSION['exp']);
	$_SESSION['exp'] = array();
//	echo $existe;

	if($existe != 1) { 
		$parametros='';
		$hacer = 'insertar';
	} else {
		$hacer = 'actualizar';
	}

//  ------------------    Registro Express de datos de facturación   ----------------------

//////////////////        Revisar formulario de captura antes de activar

	$empresa_razon_social=strtoupper(preparar_entrada_bd($empresa_razon_social));
	$empresa_rfc=strtoupper(limpiarString($empresa_rfc));
	$empresa_calle=preparar_entrada_bd($empresa_calle);
	$empresa_ext=preparar_entrada_bd($empresa_ext);
	$empresa_int=preparar_entrada_bd($empresa_int);
	$empresa_colonia=preparar_entrada_bd($empresa_colonia);
	$empresa_cp=preparar_entrada_bd($empresa_cp);
	$empresa_municipio=preparar_entrada_bd($empresa_municipio);
	$empresa_estado=preparar_entrada_bd($empresa_estado);
	
	$sql_data_array = array('empresa_razon_social' => $empresa_razon_social,
		'empresa_rfc' => $empresa_rfc,
		'empresa_calle' => $empresa_calle,
		'empresa_ext' => $empresa_ext,
		'empresa_int' => $empresa_int,
		'empresa_colonia' => $empresa_colonia,
		'empresa_cp' => $empresa_cp,
		'empresa_municipio' => $empresa_municipio,
		'empresa_estado' => $empresa_estado,
		'empresa_pais' => 'México');
		
	if($existe == 1) { $parametros="empresa_id ='" . $empresa_id . "'"; }

	ejecutar_db($dbpfx . 'empresas', $sql_data_array, $hacer, $parametros);
	if($existe != 1) { $empresa_id = mysql_insert_id(); } 
	unset($sql_data_array);

	$nombre=strtoupper(preparar_entrada_bd($nombre));
	$apellidos=strtoupper(preparar_entrada_bd($apellidos));
	$email=preparar_entrada_bd($email);
	$telefono1=preparar_entrada_bd($telefono1);
	$telefono2=preparar_entrada_bd($telefono2);
	$movil=preparar_entrada_bd($movil);
	$movil2=preparar_entrada_bd($movil2);
	$sql_data_array = array('cliente_nombre' => $nombre,
		'cliente_apellidos' => $apellidos,
		'cliente_tipo' => $clietipo,
		'cliente_empresa_id' => $empresa_id,
		'cliente_email' => $email,
		'cliente_telefono1' => $telefono1,
		'cliente_telefono2' => $telefono2,
		'cliente_movil' => $movil,
		'cliente_movil2' => $movil2,
		'cliente_boletin' => $boletin);
	if($existe != 1) {
		$parametros='';      	
		$str = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz1234567890";
		$clave = "";
		for($i=0;$i<8;$i++) {$clave .= substr($str,rand(0,58),1);}
		$sql_data_array['cliente_clave'] = $clave;
	} else {
		$parametros="cliente_id ='" . $cliente_id . "'";
	}
	ejecutar_db($dbpfx . 'clientes', $sql_data_array, $hacer, $parametros);

	if($existe != 1) { 
		$cliente_id = mysql_insert_id(); 
		$parametros='';
	} else {
		$parametros="vehiculo_id ='" . $vehiculo_id . "'";
	}
	 	
	unset($sql_data_array);

	$placas = strtoupper(limpiarString($placas));
	$serie=strtoupper(limpiarString($serie));
	$marca=strtoupper(limpiarString($marca));
	$tipo=strtoupper(limpiarString($tipo));
	$subtipo=strtoupper(limpiarString($subtipo));
	$modelo=preparar_entrada_bd($modelo);
	$colores=strtoupper(limpiarString($colores));
	$puertas=strtoupper(limpiarString($puertas));
	$sql_data_array = array('vehiculo_placas' => $placas,
		'vehiculo_serie' => $serie,
		'vehiculo_cilindros' => $cilindros,
		'vehiculo_litros' => $litros,
		'vehiculo_tipomotor' => $tipomotor,
		'vehiculo_marca' => $marca,
		'vehiculo_tipo' => $tipo,
		'vehiculo_subtipo' => $subtipo,
		'vehiculo_modelo' => $modelo,
		'vehiculo_puertas' => $puertas,
		'vehiculo_color' => $colores,
		'vehiculo_cliente_id' => $cliente_id);
	ejecutar_db($dbpfx . 'vehiculos', $sql_data_array, $hacer, $parametros);
	unset($sql_data_array);
	if($existe != 1) { $vehiculo_id = mysql_insert_id(); }

	$asesor=preparar_entrada_bd($asesor);
	$servicio=preparar_entrada_bd($servicio);
	$categoria=preparar_entrada_bd($categoria);
	$orden_id=preparar_entrada_bd($orden_id);
	$odometro=preparar_entrada_bd($odometro);
	$torre=preparar_entrada_bd($torre);

	$sql_data_array = array('orden_cliente_id' => $cliente_id,
		'orden_vehiculo_id' => $vehiculo_id,
		'orden_vehiculo_marca' => $marca,
		'orden_vehiculo_tipo' => $tipo,
		'orden_vehiculo_color' => $colores,
		'orden_vehiculo_placas' => $placas,
		'orden_asesor_id' => $asesor,
		'orden_servicio' => $servicio,
		'orden_categoria' => $categoria,
		'orden_odometro' => $odometro,
		'orden_ubicacion' => $espacio,
		'orden_garantia' => $garantia,
		'orden_paga_deducible' => $deducible,
		'orden_torre' => $torre,
		'orden_alerta' => '0',
		'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'));
		$sql_data_array['orden_estatus'] = '1';
		if($metrico == 2) {
			$sql_data_array['orden_odometro'] = $litros;
		}
/*	if($saltapres == '1') {
		$sql_data_array['orden_estatus'] = '1';
	} else {
		$sql_data_array['orden_estatus'] = '24';
	}
*/	
	if($confolio == 1) {
		$parametros = "oid = '".$oid."'";
		$sql_data_array['orden_id'] = $orden_id; 
	} else {
		$parametros = "orden_id = '".$orden_id."'";
	}
	ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
	bitacora($orden_id, 'Registro Express terminado', $dbpfx);
	if($categoria != '2') {	
		bitacora($orden_id, 'Cambio de Categoria de Servicio a categoria ' . constant('CATEGORIA_DE_REPARACION_' . $categoria), $dbpfx, 'Cambio de Categoria de Servicio a categoria ' . constant('CATEGORIA_DE_REPARACION_' . $categoria),0);
	}


	if(isset($_FILES['orden_adm'])) {

		$resultado = agrega_documento($orden_id, $_FILES['orden_adm'], 'Orden de Admisión', $dbpfx);
		if ($resultado['error'] == 'si') {
   		$_SESSION['orden']['mensaje'] .= "Ocurrió algún error al subir el archivo de Orden de Admisión. No pudo guardarse.<br>";
	   }
	}
	   
	if(isset($_FILES['levante'])) {
		
		$resultado = agrega_documento($orden_id, $_FILES['levante'], 'Hoja de Daños', $dbpfx);
		if ($resultado['error'] == 'si') {
   		$_SESSION['orden']['mensaje'] .= "Ocurrió algún error al subir el archivo de Hoja de Daños. No pudo guardarse.<br>";
	   }
   }
/*	
	$resultado = agrega_documento($orden_id, $_FILES['inventario'], 'Inventario de Ingreso', $dbpfx);
	if ($resultado['error'] == 'si') {
   	$_SESSION['orden']['mensaje'] .= "Ocurrió algún error al subir el archivo del Inventario de Ingreso. No pudo guardarse.<br>";
   }
*/
/*	if($servicio=='4') {
		redirigir('reg-express.php?accion=siniestro&orden_id=' . $orden_id . '&area=' . $areas . '&sub_orden_id=' . $sub_orden_id);
	}
*/
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
}

echo '		</div>
	</div>';
include('parciales/pie.php');
/* Archivo index.php */
