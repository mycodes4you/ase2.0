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
	
	if (validaAcceso('1120000', $dbpfx) == 1 || ($solovalacc != 1 && $_SESSION['rol06']=='1')) {
		// Acceso autotizado 
	} else {
		$_SESSION['msjerror'] = $lang['acceso_error'];
		if($confolio == 1) {
			$donde = 'ordenes.php?accion=consultar&oid=' . $oid;
		} else {
			$donde = 'ordenes.php?accion=consultar&orden_id=' . $orden_id;
		}
		redirigir($donde);
	}

	$preg = "SELECT orden_id, orden_cliente_id, orden_vehiculo_id, orden_vehiculo_placas, orden_vehiculo_tipo, orden_categoria FROM " . $dbpfx . "ordenes WHERE ";
	if($orden_id > 0) {
		$preg .= "orden_id = '" . $orden_id . "' ";
	} elseif($oid > 0) {
		$preg .= "oid = '" . $oid . "' ";
	} else {
		$_SESSION['msjerror'] = $lang['no_id'];
		redirigir('index.php');
	}
	$preg .= "AND orden_estatus < '90' ";
	$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
	$ord = mysql_fetch_array($matr);
	$fila = mysql_num_rows($matr);
	$existe = 0;
	$orden_id = $ord['orden_id'];
	if($fila == 1 && $ord['orden_vehiculo_placas']!= '') {
		$preg0 = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_placas = '".$ord['orden_vehiculo_placas']."'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
		$filas = mysql_num_rows($matr0);
		if($filas == 1) {
			$veh = mysql_fetch_array($matr0);
			$vehiculo_id = $veh['vehiculo_id'];
			$existe = 1;
			$preg1 = "SELECT * FROM " . $dbpfx . "clientes WHERE cliente_id = '" . $veh['vehiculo_cliente_id']."'";
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

	echo '		<div class="row"><div class="col-12"><form action="reg-express.php?accion=insertar" id="filtro" name="filtro" method="post" enctype="multipart/form-data">'."\n";
	echo '			<div class="col-lg-4 col-md-6 col-sm-3">
						<div class="obscuro espacio">
							<h3>' . $lang['vehiculo'];
	if($existe == 1) {
		echo ' <a href="vehiculos.php?accion=modificar&vehiculo_id=' . $vehiculo_id . '&orden_id=' . $orden_id . '&oid=' . $oid . '&regexp=1"><img src="idiomas/' . $idioma . '/imagenes/prod-editar.png" width="45" height="45" alt="' . $lang['EditarDatos'] . '" title="' . $lang['EditarDatos'] . '" /></a>';
	}
	echo '</h3>
								<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="100%">
									<tr class="cabeza_tabla"><td colspan="2">' . $encabezado . '</td></tr>'."\n";
	if($confolio == 1) {
		echo '									<tr><td>Orden de Trabajo</td><td colspan="2"><input type="text" name="ordenid" size="15" maxlength="11" value="' . $orden_id . '" /></td></tr>'."\n";
	}

	if($existe == 1) {
		echo '									<tr><td colspan="3">' . $lang['Placa'] . ': <strong>' . $veh['vehiculo_placas'] . '</strong></td></tr>'."\n";
		echo '									<tr><td colspan="3">' . $lang['serie'] . ': <strong>' .  $veh['vehiculo_serie']  .'</strong></td></tr>'."\n";
		echo '									<tr><td colspan="3">' . $lang['marca'] . ': <strong>' . $veh['vehiculo_marca'] . '</strong></td></tr>'."\n";
		echo '									<tr><td colspan="3">' . $lang['tipo'] . ': <strong>' . $veh['vehiculo_tipo'] . '</strong></td></tr>'."\n";
		echo '									<tr><td colspan="3">' . $lang['subtipo'] . ': <strong>' . $veh['vehiculo_subtipo'] . '</strong></td></tr>'."\n";
		echo '									<tr><td colspan="3">' . $lang['year'] . ': <strong>' . $veh['vehiculo_modelo'] . '</strong></td></tr>'."\n";
		echo '									<tr><td colspan="3">' . $lang['color'] . ': <strong>' . $veh['vehiculo_color'] . '</strong></td></tr>'."\n";
		echo '									<tr><td colspan="3">' . $lang['puertas'] . ': <strong>' . $veh['vehiculo_puertas'] . '</strong></td></tr>'."\n";
	} else {
		echo '									<tr><td><strong>' . $lang['Placa'] . ' *</strong></td><td colspan="2"><input type="text" name="placas" size="10" maxlength="15" value="';
		echo ($_SESSION['exp']['placas']) ? $_SESSION['exp']['placas'] : $ord['orden_vehiculo_tipo'];
		echo '" /></td></tr>'."\n";
		echo '									<tr><td><strong>' . $lang['serie'] . ' *</strong></td><td colspan="2"><input type="text" name="serie" size="15" maxlength="60" value="' . $_SESSION['exp']['serie'] . '" /></td></tr>'."\n";

// ------ Utilizar Marcas y Modelos ------
		if($valor['UsarMarcas'][0] == 1) {
			echo '									<tr><td><strong>' . $lang['marca'] . ' *</strong></td><td style="text-align:left;">'."\n";
			echo '										<select name="marca" size="1" onchange="document.filtro.submit()";>'."\n";
			echo '											<option value="">Seleccione Marca</option>'."\n";

// ------ Conectando a ASEBase para obtener datos de Marcas y Modelos de Vehículos ---------------

			mysql_close();
			mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
			mysql_select_db('ASEBase') or die('Falló la seleccion la DB');
		
			$preg1 = "SELECT marca_id, marca_nombre FROM marcas ORDER BY marca_orden";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de marcas! " . $preg1);
			while ($ma = mysql_fetch_array($matr1)) {
				echo '				<option value="' . $ma['marca_nombre'] . '"';
				if($_SESSION['exp']['marca'] == $ma['marca_nombre']) { echo ' selected="selected" '; $marca_id = $ma['marca_id']; }
				echo '>' . $ma['marca_nombre'] . '</option>'."\n";
			}
			echo '				<option value="OTRA MARCA"';
			if($_SESSION['exp']['marca'] == 'OTRA MARCA') { echo ' selected="selected" '; $marca_id = 99999; }
			echo '>OTRA MARCA</option>'."\n";
			echo '			</select>'."\n";
			echo '		</td></tr>'."\n";
			echo '		<tr><td><strong>' . $lang['tipo'] . ' *</strong></td><td style="text-align:left;">'."\n";
			echo '			<select name="tipo" size="1">'."\n";
			echo '				<option value="">Seleccione Modelo</option>'."\n";
			if($marca_id != 99999) {
				$preg2 = "SELECT modelo_id, modelo_nombre FROM modelos WHERE marca_id = '" . $marca_id . "' ORDER BY modelo_nombre";
//				echo 'MO -> ' . $preg2;
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de modelos! " . $preg2);
				while ($mod = mysql_fetch_array($matr2)) {
					echo '				<option value="' . $mod['modelo_nombre'] . '"';
					if($_SESSION['exp']['tipo'] == $mod['modelo_nombre']) { echo ' selected="selected" '; }
					echo '>' . $mod['modelo_nombre'] . '</option>'."\n";
				}
			}
			echo '				<option value="OTRO TIPO">OTRO TIPO</option>'."\n";
			echo '			</select>'."\n";
			mysql_close();
			mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
			mysql_select_db($dbnombre) or die('Falló la seleccion la DB');

// ------ Cierre de ASEBase de datos comunes -----------------------------		

			echo '		</td></tr>'."\n";
		} else {
			echo '									<tr><td><strong>' . $lang['marca'] . ' *</strong></td><td colspan="2"><input type="text" name="marca" size="15" maxlength="60" value="' . $_SESSION['exp']['marca'] . '"/></td></tr>
									<tr><td><strong>' . $lang['tipo'] . ' *</strong></td><td colspan="2"><input type="text" name="tipo" size="15" maxlength="40" value="' . $_SESSION['exp']['tipo'] . '" /></td></tr>'."\n";
		}

		echo '									<tr><td>' . $lang['subtipo'] . '</td><td colspan="2"><input type="text" name="subtipo" size="15" maxlength="40" value="' . $_SESSION['exp']['subtipo'] . '" /></td></tr>
									<tr><td style="text-align:center;"><strong>' . $lang['year'] . ' *</strong></td><td style="text-align:center;">' . $lang['puertas'] . '</td><td  style="text-align:center;"><strong>' . $lang['color'] . ' *</strong></td></tr>'."\n";
		echo '									<tr><td style="text-align:center;"><input type="text" name="modelo" size="3" maxlength="4" value="' . $_SESSION['exp']['modelo'] . '" /></td><td style="text-align:center;"><input type="text" name="puertas" size="3" maxlength="6" value="' . $_SESSION['exp']['puertas'] . '" /></td><td style="text-align:center;"><input type="text" name="colores" size="6" maxlength="15" value="' . $_SESSION['exp']['colores'] . '" /></td></tr>';
		if($regexpext == 1) {
			echo '									<tr><td style="text-align:center;">' . $lang['cilindros'] . '</td><td style="text-align:center;">' . $lang['cilindrada'] . '</td><td  style="text-align:center;">' . $lang['motor'] . '</td></tr>
									<tr><td style="text-align:center;"><input type="text" name="cilindros" size="3" maxlength="4" value="' . $_SESSION['exp']['cilindros'] . '" /></td><td style="text-align:center;"><input type="text" name="litros" size="3" maxlength="6" value="' . $_SESSION['exp']['litros'] . '" /></td><td style="text-align:center;"><input type="text" name="tipomotor" size="6" maxlength="32" value="' . $_SESSION['exp']['tipomotor'] . '" /></td></tr>'."\n";

		}
	}

	if($adm_docs == '1' || $docingreso == '1') {
		echo '									<tr><td colspan="2"><strong>' . $lang['docadmin'] . ' * </strong><input type="file" name="orden_adm" size="6" />';
	}
	echo '</td></tr>'."\n";
	if($adm_docid == '1') {
		echo '								<tr><td colspan="2"><strong>' . $lang['docrep'] . ' * </strong><input type="file" name="levante" size="6" /></td></tr>'."\n";
	}
	echo '								</table>
						</div></div>
					<div class="col-lg-4 col-md-6 col-sm-3">
						<div class="obscuro espacio">
							<h3>' . $lang['cliente'];
	if($existe == 1) {
		echo ' <a href="personas.php?accion=modificar&cliente_id=' . $cliente_id . '&orden_id=' . $orden_id . '&oid=' . $oid . '&regexp=1"><img src="idiomas/' . $idioma . '/imagenes/prod-editar.png" width="45" height="45" alt="' . $lang['EditarDatos'] . '" title="' . $lang['EditarDatos'] . '" /></a>';
	}
	echo '</h3>
								<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="100%">'."\n";
	if($existe == 1) {
		echo '									<tr><td>' . $lang['Empresa'] . ': <strong>' . $empresa['empresa_razon_social'] . '</strong></td></tr>'."\n";
		echo '									<tr><td>' . $lang['Nombre'] . ': <strong>' . $clie['cliente_nombre'] . '</strong></td></tr> 
									<tr><td>' . $lang['Apellidos'] . ': <strong>' . $clie['cliente_apellidos'] . '</strong></td></tr>
									<tr><td>' . $lang['conductor'] . ': <strong>';
		if($clie['cliente_tipo'] == 1) { echo $lang['clietipo']; } else { echo $lang['tercero']; }
		echo '</strong></td></tr>
									<tr><td>' . $lang['deseaemail'] . ': <strong>' . $clie['cliente_boletin'] . '</strong></td></tr>
									<tr><td>' . $lang['email'] . ': <strong>' . $clie['cliente_email'] . '</strong></td></tr>
									<tr><td>' . $lang['Teléfono'] . ': <strong>' . $clie['cliente_telefono1'] . '</strong></td></tr>
									<tr><td>' . $lang['Otro'] . ': <strong>' . $clie['cliente_telefono2'] . '</strong></td></tr>
									<tr><td>' . $lang['Celular'] . ': <strong>' . $clie['cliente_movil'] . '</strong></td></tr>
									<tr><td>' . $lang['Nextel'] . ': <strong>' . $clie['cliente_movil2'] . '</strong></td></tr>'."\n";
	} else {
//$_SESSION['exp']['razon_social']
		echo '									<tr><td>' . $lang['Empresa'] . '</td><td><input type="text" name="razon_social" size="24" maxlength="120" value="' . $_SESSION['exp']['razon_social'] . '" /></td></tr>
									<tr><td><strong>' . $lang['Nombre'] . ' *</strong></td><td><input type="text" name="nombre" size="24" maxlength="120" value="' . $_SESSION['exp']['nombre'] . '" /></td></tr>
									<tr><td><strong>' . $lang['Apellidos'] . ' *</strong></td><td><input type="text" name="apellidos" size="24" maxlength="60" value="' . $_SESSION['exp']['apellidos'] . '" /></td></tr>
									<tr><td><strong>' . $lang['conductor'] . ' *</strong></td><td>' . $lang['clietipo'] . '<input type="radio" name="clietipo" value="1"';
		if($_SESSION['exp']['clietipo'] == 1) { echo ' checked '; }
		echo ' />' . $lang['tercero'] . '<input type="radio" name="clietipo" value="0"';
		if($_SESSION['exp']['clietipo'] == 0) { echo ' checked '; }
		echo ' /></td></tr>
									<tr></tr>
									<tr><td colspan="2" style="text-align:left;"><strong>' . $lang['deseaemail'] . ' *</strong><input type="checkbox" name="boletin" value="Si"';
		if($_SESSION['exp']['boletin'] == 'Si') { echo ' checked '; }
		echo ' /></td></tr>
									<tr><td><strong>' . $lang['email'] . ' *</strong></td><td><input type="text" name="email" size="24" maxlength="120" value="' . $_SESSION['exp']['email'] . '" /></td></tr>
									<tr><td><strong>' . $lang['Teléfono'] . ' *</strong></td><td><input type="text" name="telefono1" size="24" maxlength="40" value="' . $_SESSION['exp']['telefono1'] . '" /></td></tr>
									<tr><td>' . $lang['Otro'] . '</td><td><input type="text" name="telefono2" size="24" maxlength="40" value="' . $_SESSION['exp']['telefono2'] . '" /></td></tr>
									<tr><td>' . $lang['Celular'] . '</td><td><input type="text" name="movil" size="24" maxlength="40" value="' . $_SESSION['exp']['movil'] . '" /></td></tr>
									<tr><td>' . $lang['Nextel'] . '</td><td><input type="text" name="movil2" size="24" maxlength="40" value="' . $_SESSION['exp']['movil2'] . '" /></td></tr>'."\n";
	}
	echo '								</table>'."\n";
	echo '						</div></div>'."\n";
	if($empresa_id != '') {
		foreach($empresa as $k => $v) {
			echo '<input type="hidden" name="'.$k.'" value="'.$v.'">'."\n";
		}
	}

	echo '				<div class="col-lg-4 col-md-6 col-sm-3">	
						<div class="obscuro espacio">'."\n";
	echo '							<h3>' . $lang['Tipo de Servicio'] . '</h3>
								<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="100%">'."\n";
	echo '									<tr><td colspan="2" style="text-align:left;">
										<select name="asesor" size="1">
											<option value="Seleccione" >' . $lang['Seleccione Asesor'] . '</option>'."\n";
	$pregunta2 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE rol06 = '1' AND acceso ='0' AND activo ='1' ";
	$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección! " . $pregunta2);
	while($usuario = mysql_fetch_array($matriz2)) {
		echo '											<option value="' . $usuario['usuario'] . '"';
		if($_SESSION['exp']['asesor'] == $usuario['usuario']) { echo ' selected '; }
		echo '>' . $usuario['nombre'] . ' ' . $usuario['apellidos'] . '</option>'."\n";
	}
	echo '										</select>
									</td></tr>
									<tr>
										<td valign="top" colspan="2"><strong>' . $lang['Tipo de Servicio'] . ' *</strong></td>
									</tr>
									<tr>
										<td valign="top">' . $lang['Directo'] . '<br>
											<input type="radio" name="servicio" id="ts1" value="1"';
	if($_SESSION['exp']['servicio'] == 1) { echo ' checked '; }
	echo ' /><label for="ts1" >' . $lang['os1'] . '</label><br>
											<input type="radio" name="servicio" id="ts3" value="3"';
	if($_SESSION['exp']['servicio'] == 3) { echo ' checked '; }
	echo ' /><label for="ts3" >' . $lang['os3'] . '</label><br>
											<input type="radio" name="servicio" id="ts2" value="2"';
	if($_SESSION['exp']['servicio'] == 2) { echo ' checked '; }
	echo ' /><label for="ts2" >' . $lang['os2'] . '</label>
										</td><td>
											<strong>' . $lang['servicio'] . '</strong><br>
											<input type="radio" name="servicio" id="ts4" value="4"';
	if($_SESSION['exp']['servicio'] == 4) { echo ' checked '; }
	echo ' /><label for="ts4" >' . $lang['os4'] . '</label>
										</td>
									</tr>
									<tr><td colspan="2">' . $lang['Garantía'] . '<input type="text" name="garantia" value="' . $_SESSION['exp']['garantia'] . '" size="6" />' . $lang['cualot'] . '</td></tr>'."\n";
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
	echo '					<option value="Zona de Espera" '; echo ($_SESSION['exp']['espacio'] == 'Zona de Espera') ? 'selected="1"' : ''; echo '>' . $lang['En Taller'] . '</option>' . "\n";
	echo '					<option value="Transito" '; echo ($_SESSION['exp']['espacio'] == 'Transito') ? 'selected="1"' : ''; echo '>' . $lang['Tránsito'] . '</option>' . "\n";
	echo '				</select></td></tr>'."\n";
	echo '									<tr><td>' . $lang['Torre'] . '</td><td style="text-align:left;"><input type="text" name="torre" size="4" value="' . $_SESSION['exp']['torre'] . '" maxlength="30" /></td></tr>'."\n";
	if($grua_reg == '1') {
		echo '<input type="hidden" name="gruareg" value="1" />';  // Verificación de forzar registro de llegada en Grua.
	} else {
		echo '<input type="hidden" name="gruareg" value="0" />';
	}
	echo '									<tr><td colspan="2" style="text-align:left;">' . $lang['Grua'] . '<br>Sí<input type="radio" name="grua" value="1" ';
	if($_SESSION['exp']['grua'] == 1) { echo 'checked'; }
	echo '/> | No<input type="radio" name="grua" value="2" ';
	if($_SESSION['exp']['grua'] == 2) { echo 'checked'; }
	echo '/></td></tr>'."\n";

	echo '								</table>
						</div></div>'."\n";

	echo '					'."\n";
	echo '				<input type="submit" name="valida_placas" value="Enviar" onclick="validarExp(filtro);return false;" />
				<input type="hidden" name="existe" value="'.$existe.'">
				<input type="hidden" name="cliente_id" value="'.$cliente_id.'">
				<input type="hidden" name="vehiculo_id" value="'.$vehiculo_id.'">
				<input type="hidden" name="empresa_id" value="'.$empresa_id.'">
				<input type="hidden" name="orden_id" value="'.$orden_id.'">
				<input type="hidden" name="oid" value="'.$oid.'">
			</form></div></div>';
	unset($_SESSION['exp']);
}

elseif($accion==='insertar') {

	if (validaAcceso('1120000', $dbpfx) == 1 || ($solovalacc != 1 && $_SESSION['rol06']=='1')) {
		// Acceso autotizado 
	} else {
		$_SESSION['msjerror'] = $lang['acceso_error'];
		if($confolio == 1) {
			$donde = 'ordenes.php?accion=consultar&oid=' . $oid;
		} else {
			$donde = 'ordenes.php?accion=consultar&orden_id=' . $orden_id;
		}
		redirigir($donde);
	}

	if($confolio == 1) {
		$orden_id = limpiarNumero($ordenid);
		$preg01 = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matr01 = mysql_query($preg01) or die("ERROR: Fallo búsqueda de ordenes!");
		$fila = mysql_num_rows($matr01);
		if($fila > 0) {
			$_SESSION['msjerror'] ='Ya existe otra Orden de Trabajo con el número ' . $orden_id . '.<br>';
			redirigir('reg-express.php?accion=express&oid=' . $oid);
		}
	}

	unset($_SESSION['exp']);
	$_SESSION['exp'] = array();
	$error = 'no';

	$razon_social=strtoupper(preparar_entrada_bd($razon_social)); $_SESSION['exp']['razon_social'] = $razon_social;
	$nombre=strtoupper(preparar_entrada_bd($nombre)); $_SESSION['exp']['nombre'] = $nombre;
	$apellidos=strtoupper(preparar_entrada_bd($apellidos)); $_SESSION['exp']['apellidos'] = $apellidos;
	$email=preparar_entrada_bd($email); $_SESSION['exp']['email'] = $email;
	$telefono1=preparar_entrada_bd($telefono1); $_SESSION['exp']['telefono1'] = $telefono1;
	$telefono2=preparar_entrada_bd($telefono2); $_SESSION['exp']['telefono2'] = $telefono2;
	$movil=preparar_entrada_bd($movil); $_SESSION['exp']['movil'] = $movil;
	$movil2=preparar_entrada_bd($movil2); $_SESSION['exp']['movil2'] = $movil2;
	$placas = strtoupper(limpiarString($placas)); $_SESSION['exp']['placas'] = $placas;
	$serie=strtoupper(limpiarString($serie)); $_SESSION['exp']['serie'] = $serie;
	$marca=preparar_entrada_bd($marca); $_SESSION['exp']['marca'] = $marca;
	$tipo=preparar_entrada_bd($tipo); $_SESSION['exp']['tipo'] = $tipo;
	$subtipo=strtoupper(preparar_entrada_bd($subtipo)); $_SESSION['exp']['subtipo'] = $subtipo;
	$modelo=limpiarNumero($modelo); $_SESSION['exp']['modelo'] = $modelo;
	$colores=strtoupper(preparar_entrada_bd($colores)); $_SESSION['exp']['colores'] = $colores;
	$puertas=limpiarNumero($puertas); $_SESSION['exp']['puertas'] = $puertas;
	$asesor=preparar_entrada_bd($asesor); $_SESSION['exp']['asesor'] = $asesor;
	$servicio=preparar_entrada_bd($servicio); $_SESSION['exp']['servicio'] = $servicio;
	$categoria=preparar_entrada_bd($categoria); $_SESSION['exp']['categoria'] = $categoria;
	$orden_id=preparar_entrada_bd($orden_id); $_SESSION['exp']['orden_id'] = $orden_id;
	$torre=preparar_entrada_bd($torre); $_SESSION['exp']['torre'] = $torre;
	$grua=preparar_entrada_bd($grua); $_SESSION['exp']['grua'] = $grua;
	$transito=preparar_entrada_bd($transito); $_SESSION['exp']['transito'] = $transito;
	$_SESSION['exp']['boletin'] = $boletin;
	$_SESSION['exp']['clietipo'] = $clietipo;
	$_SESSION['exp']['garantia'] = $garantia;
	$espacio=preparar_entrada_bd($espacio); $_SESSION['exp']['espacio'] = $espacio;

	if($asesor == '' || $servicio == '' || $categoria == '' || ($servicio == '2' && ($garantia == '' || !is_numeric($garantia)))) {
		$_SESSION['msjerror'] = $lang['Requeridos'] . '<br>';
		if($confolio == 1) {
			redirigir('reg-express.php?accion=express&oid=' . $oid);
		} else {
			redirigir('reg-express.php?accion=express&orden_id=' . $orden_id);
		}
	}

	if($existe == 1) {
		$preg0 = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_id = '" . $vehiculo_id . "'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
		$veh = mysql_fetch_array($matr0);
		$placas = $veh['vehiculo_placas'];
		$marca = $veh['vehiculo_marca'];
		$tipo = $veh['vehiculo_tipo'];
		$colores = $veh['vehiculo_color'];
		$puertas = $veh['vehiculo_puertas'];
	} elseif($existe != 1 && $error == 'no') {
		if($nombre == '' || $apellidos == '' || $telefono1 == '' || $placas == '' || $serie == '' || $marca == '' || $tipo == '' || $modelo == '' || $colores == '' ) {
			$_SESSION['msjerror'] = $lang['Requeridos'] . '<br>';
			if($confolio == 1) {
				redirigir('reg-express.php?accion=express&oid=' . $oid);
			} else {
				redirigir('reg-express.php?accion=express&orden_id=' . $orden_id);
			}
		}

		$preg = "SELECT vehiculo_id FROM " . $dbpfx . "vehiculos WHERE vehiculo_placas = '$placas'";
		if($noverifserie != '1') { $preg .= " OR vehiculo_serie ='$serie'"; }
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de Vehículos!");
		$fila = mysql_num_rows($matr);
		if($fila > 0) {
			$_SESSION['msjerror'] ='Ya existe otro vehículo con el mismo número de placas o VIN.<br>';
			if($confolio == 1) {
				redirigir('reg-express.php?accion=express&oid=' . $oid);
			} else {
				redirigir('reg-express.php?accion=express&orden_id=' . $orden_id);
			}
		}

		if($razon_social != '') {
			$sql_data_array = array('empresa_razon_social' => $razon_social);
		} else {
			$sql_data_array = array('empresa_razon_social' => $nombre . ' ' . $apellidos);
		}
		$empresa_id = ejecutar_db($dbpfx . 'empresas', $sql_data_array, 'insertar');
		unset($sql_data_array);

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
		$str = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz1234567890";
		$clave = "";
		for($i=0;$i<6;$i++) {$clave .= substr($str,rand(0,57),1);}
		$sql_data_array['cliente_clave'] = $clave;
		$cliente_id = ejecutar_db($dbpfx . 'clientes', $sql_data_array, 'insertar'); 
		unset($sql_data_array);

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
		$vehiculo_id = ejecutar_db($dbpfx . 'vehiculos', $sql_data_array, 'insertar');
		unset($sql_data_array);
	}

	$sql_data_array = array('orden_cliente_id' => $cliente_id,
		'orden_vehiculo_id' => $vehiculo_id,
		'orden_vehiculo_marca' => $marca,
		'orden_vehiculo_tipo' => $tipo,
		'orden_vehiculo_color' => $colores,
		'orden_vehiculo_placas' => $placas,
		'orden_asesor_id' => $asesor,
		'orden_servicio' => $servicio,
		'orden_categoria' => $categoria,
		'orden_ubicacion' => $espacio,
		'orden_grua' => $grua,
		'orden_torre' => $torre,
		'orden_alerta' => '0',
		'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'));
	$sql_data_array['orden_estatus'] = '1';
	if($metrico == 2) {
		$sql_data_array['orden_odometro'] = $litros;
	}
	if($servicio == 2) {
		$sql_data_array['orden_garantia'] = $garantia;
	}

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

	if($valor['UsarMarcas'][0] == 1 && ($marca == 'OTRA MARCA' || $tipo == 'OTRO TIPO')) {
		bitacora($orden_id, 'Marca o Tipo de Vehículo no encontrada', $dbpfx, 'Se seleccionó la Marca: ' . $marca . ' y el Tipo: ' . $tipo . ' para el vehículo ' . $vehiculo_id, 3, '', '', 701);
	}

	if(isset($_FILES['orden_adm'])) {

		$resultado = agrega_documento($orden_id, $_FILES['orden_adm'], $lang['nomdocadmin'], $dbpfx);
		if ($resultado['error'] == 'si') {
   		$_SESSION['orden']['mensaje'] .= "Ocurrió algún error al subir el archivo de " . $lang['nomdocadmin'] . ". No pudo guardarse.<br>";
	   }
	}

	if(isset($_FILES['levante'])) {

		$resultado = agrega_documento($orden_id, $_FILES['levante'], $lang['nomdocrep'], $dbpfx);
		if ($resultado['error'] == 'si') {
   		$_SESSION['orden']['mensaje'] .= "Ocurrió algún error al subir el archivo de " . $lang['nomdocrep'] . ". No pudo guardarse.<br>";
	   }
   }

	// ---- Envio de Bienvenida ---
	if($notifica_bienvenida == '1') {
		include('particular/notifica_bienvenida.php');
		if($msjerror == 1) {
			$comentario = 'Ocurrió un error en el envió de la bienvenida';
			$para_usuario = $_SESSION['usuario'];
			$etapa_com = '';
			$interno = 3;
		} else {
			if($para == $agencia_email ) {
				$comentario = 'Bienvenida no enviada: el cliente no tiene correo propio capturado.';
			} else{
				$comentario = 'Correo de bienvenida enviado a ' . $dato['cliente_email'];
			}
			$para_usuario = '';
			$interno = 2;
			$etapa_com = 10;
		}
		bitacora($orden_id, $comentario, $dbpfx, $comentario, $interno, '', '', $para_usuario, $etapa_com);
	}

	unset($_SESSION['exp']);
	redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
}

echo '		</div>
	</div>';
include('parciales/pie.php');
/* Archivo index.php */
