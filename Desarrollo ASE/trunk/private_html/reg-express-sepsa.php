<?php
foreach($_POST as $k => $v){$$k=$v; } //echo $k.' -> '.$v.' | '; }
foreach($_GET as $k => $v){$$k=$v; } //echo $k.' -> '.$v.' | '; }
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/reg-express.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1

header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado para forzar revalidación.

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
		$preg0 = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_placas = '" . $ord['orden_vehiculo_placas'] . "'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de vehículo!");
		$filas = mysql_num_rows($matr0);
		if($filas == 1) {
			$veh = mysql_fetch_array($matr0);
			$vehiculo_id = $veh['vehiculo_id'];
			$existe = 1;
			$preg1 = "SELECT * FROM " . $dbpfx . "clientes WHERE cliente_id = '" . $veh['vehiculo_cliente_id'] . "'";
//			echo $preg1;
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Cliente!");
			$clie = mysql_fetch_array($matr1);
			$cliente_id = $clie['cliente_id'];
			$empresa_id = $clie['cliente_empresa_id'];
			$preg2 = "SELECT * FROM " . $dbpfx . "empresas WHERE empresa_id = '" . $empresa_id . "'";
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
	echo '									<tr><td>' . $lang['Placa'] . '</td><td colspan="2">';
	if($existe == 1) {
		echo '<input type="hidden" name="siexiste" value="1" />';		
		echo $veh['vehiculo_placas'];
	} else {
		echo '<input type="hidden" name="siexiste" value="0" />';
		echo '<input type="text" name="placas" size="10" maxlength="15" value="' . $ord['orden_vehiculo_tipo'] . '" />';
	}
	echo '</td></tr>'."\n";
	echo '									<tr><td>' . $lang['serie'] . '</td><td colspan="2">';
	if($existe == 1) {
			echo $veh['vehiculo_serie'];
	} else {
		echo '<input type="text" name="serie" size="18" maxlength="60" value="' . $_SESSION['exp']['serie'] . '" />';
	}
	echo '</td></tr>'."\n";
	echo '									<tr><td>' . $lang['marca'] . '</td><td colspan="2">';
	if($existe == 1) {
		echo $veh['vehiculo_marca'];
	} else {
		echo '<input type="text" name="marca" size="18" maxlength="60" value="' . $_SESSION['exp']['marca'] . '" />';
	}
	echo '</td></tr>
									<tr><td>' . $lang['tipo'] . '</td><td colspan="2">';
	if($existe == 1) {
		echo $veh['vehiculo_tipo'];
	} else {
		echo '<input type="text" name="tipo" size="18" maxlength="40" value="' . $_SESSION['exp']['tipo'] . '" />';
	}
	echo '</td></tr>
									<tr><td>' . $lang['subtipo'] . '</td><td colspan="2">';
	if($existe == 1) {
		echo $veh['vehiculo_subtipo'];
	} else {
	echo '<input type="text" name="subtipo" size="18" maxlength="40" value="' . $_SESSION['exp']['subtipo'] . '" />';
	}
	echo '</td></tr>
									<tr><td style="text-align:center;">' . $lang['year'] . '</td><td style="text-align:center;">' . $lang['puertas'] . '</td><td  style="text-align:center;">' . $lang['color'] . '</td></tr>'."\n";
	if($existe == 1) {
		echo '									<tr><td style="text-align:center;">' . $veh['vehiculo_modelo'] . '</td><td style="text-align:center;">' . $veh['vehiculo_puertas'] . '</td><td style="text-align:center;">' . $veh['vehiculo_color'] . '</td></tr>'."\n";
	} else {
		echo '									<tr><td style="text-align:center;"><input type="text" name="modelo" size="3" maxlength="4" value="' . $_SESSION['exp']['modelo'] . '" /></td><td style="text-align:center;"><input type="text" name="puertas" size="3" maxlength="6" value="' . $_SESSION['exp']['puertas'] . '" /></td><td style="text-align:center;"><input type="text" name="colores" size="6" maxlength="15" value="' . $_SESSION['exp']['colores'] . '" /></td></tr>'."\n";
	}
	if($regexpext == 1) {
		echo '									<tr><td style="text-align:center;">' . $lang['cilindros'] . '</td><td style="text-align:center;">' . $lang['cilindrada'] . '</td><td  style="text-align:center;">' . $lang['motor'] . '</td></tr>'."\n";
		if($existe == 1) {
			echo '									<tr><td style="text-align:center;">' . $veh['vehiculo_cilindros'] . '</td><td style="text-align:center;">' . $veh['vehiculo_litros'] . '</td><td style="text-align:center;">' . $veh['vehiculo_tipomotor'] . '</td></tr>'."\n";
		} else {
			echo '									<tr><td style="text-align:center;"><input type="text" name="cilindros" size="3" maxlength="4" /></td><td style="text-align:center;"><input type="text" name="litros" size="3" maxlength="6" /></td><td style="text-align:center;"><input type="text" name="tipomotor" size="12" maxlength="32" /></td></tr>'."\n";
		}
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
	echo '									<tr><td>' . $lang['Empresa'] . '</td><td>';
	if($existe == 1) {
		echo $empresa['empresa_razon_social'];
	} else {
		echo '<input type="text" name="razon" size="24" maxlength="120" value="' . $_SESSION['exp']['razon'] . '" />';
	}
	echo '</td></tr>
									<tr><td>' . $lang['Nombre'] . '</td><td>';
	if($existe == 1) {
		echo $clie['cliente_nombre'];
	} else {
		echo '<input type="text" name="nombre" size="24" maxlength="120" value="' . $_SESSION['exp']['nombre'] . '" />';
	}
	echo '</td></tr> 
									<tr><td>' . $lang['Apellidos'] . '</td><td>';
	if($existe == 1) {
		echo $clie['cliente_apellidos'];
	} else {
		echo '<input type="text" name="apellidos" size="24" maxlength="60" value="' . $_SESSION['exp']['apellidos'] . '" />';
	}
	echo '</td></tr>
									<tr><td>' . $lang['conductor'] . '</td><td>';
	if($existe == 1) {
		if($clie['cliente_tipo'] == 1) { echo $lang['clietipo']; } else { echo $lang['tercero']; }
	} else {
		echo $lang['clietipo'] . '<input type="radio" name="clietipo" value="1"';
		if($_SESSION['exp']['clietipo'] == '1' || $_SESSION['exp']['clietipo'] == '' ) { echo ' checked="checked"'; }
		echo ' />' . $lang['tercero'] . '<input type="radio" name="clietipo" value="0"';
		if($_SESSION['exp']['clietipo'] == '0' ) { echo ' checked="checked'; }
		echo ' />';
	}
	echo '</td></tr>
									<tr><td colspan="2" style="text-align:left;">' . $lang['deseaemail'];
	if($existe == 1) {
		echo ' ' . $clie['cliente_boletin'];
	} else {
		echo '<input type="checkbox" name="boletin" value="Si"';
		if($_SESSION['exp']['boletin'] != 'No') {echo ' checked="checked"';}
		echo ' />';
	}
	echo '</td></tr>
									<tr><td>' . $lang['email'] . '</td><td>';
	if($existe == 1) {
		echo $clie['cliente_email'];
	} else {
		echo '<input type="text" name="email" size="24" maxlength="120" value="' . $_SESSION['exp']['email'] . '" />';
	}
	echo '</td></tr>
									<tr><td>' . $lang['Teléfono'] . '</td><td>';
	if($existe == 1) {
		echo $clie['cliente_telefono1'];
	} else {
		echo '<input type="text" name="telefono1" size="24" maxlength="40" value="' . $_SESSION['exp']['telefono1'] . '" />';
	}
	echo '</td></tr>
									<tr><td>' . $lang['Otro'] . '</td><td>';
	if($existe == 1) {
		echo $clie['cliente_telefono2'];
	} else {
		echo '<input type="text" name="telefono2" size="24" maxlength="40" value="' . $_SESSION['exp']['telefono2'] . '" />';
	}
	echo '</td></tr>
									<tr><td>' . $lang['Celular'] . '</td><td>';
	if($existe == 1) {
		echo $clie['cliente_movil'];
	} else {
		echo '<input type="text" name="movil" size="24" maxlength="40" value="' . $_SESSION['exp']['movil'] . '" />';
	}
	echo '</td></tr>
									<tr><td>' . $lang['Nextel'] . '</td><td>';
	if($existe == 1) {
		echo $clie['cliente_movil2'];
	} else {
		echo '<input type="text" name="movil2" size="24" maxlength="40" value="' . $_SESSION['exp']['movil2'] . '" />';
	}
	echo '</td></tr>
								</table>'."\n";
	echo '						</div>'."\n";
/*	if($empresa_id != '') {
		foreach($empresa as $k => $v) {
			echo '<input type="hidden" name="'.$k.'" value="'.$v.'">'."\n";
		}
	}
*/					
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
										<td valign="top">'."\n";
	echo '											<select name="servicio" size="1">
											<option id="ts4" value="4" '; echo ($_SESSION['exp']['servicio'] == '4') ? 'selected="1"' : ''; echo '><label for="ts4" >' . $lang['os4'] . '</label></option>
											<option id="ts1" value="1" '; echo ($_SESSION['exp']['servicio'] == '1') ? 'selected="1"' : ''; echo '><label for="ts1" >' . $lang['os1'] . '</label></option>
											<option id="ts3" value="3" '; echo ($_SESSION['exp']['servicio'] == '3') ? 'selected="1"' : ''; echo '><label for="ts3" >' . $lang['os3'] . '</label></option>
											<option id="ts2" value="2" '; echo ($_SESSION['exp']['servicio'] == '2') ? 'selected="1"' : ''; echo '><label for="ts2" >' . $lang['os2'] . '</label></option>
											<option id="ts5" value="5" '; echo ($_SESSION['exp']['servicio'] == '5') ? 'selected="1"' : ''; echo '><label for="ts5" >' . $lang['os5'] . '</label></option>
											<option id="ts6" value="6" '; echo ($_SESSION['exp']['servicio'] == '6') ? 'selected="1"' : ''; echo '><label for="ts6" >' . $lang['os6'] . '</label></option>
											</select>
										</td>
									</tr>
									<tr><td colspan="2">' . $lang['cualot'] . '<input type="text" name="garantia" size="6" value="' . $_SESSION['exp']['garantia'] . '" /></td></tr>'."\n";
	echo '								</table>'."\n";
	echo '						</div>'."\n";

	echo '						<div class="obscuro espacio">
							<h3>' . $lang['Categoría de Servicio'] . '</h3>
								<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="100%">'."\n";
	echo '									<tr>
										<td colspan="2" style="text-align:left;">
											<select name="categoria" size="1">'."\n";
	for($se=1;$se<=4;$se++){
		echo '											<option value="' . $se . '"';  
		if($_SESSION['exp']['categoria'] !='' && $_SESSION['exp']['categoria'] == $se) { echo ' selected="selected" '; }
		elseif($_SESSION['exp']['categoria'] == '' && $ord['orden_categoria'] == $se) { echo ' selected="selected" '; }
		echo '>' . constant('CATEGORIA_DE_REPARACION_' .$se) . '</option>'."\n";
	}
	echo '											</select>
										</td>
									</tr>'."\n";
	echo '									<tr><td colspan="2">' . $lang['donde'] . '</td></tr>
									<tr><td colspan="2" style="text-align:left;">
				<select name="espacio" size="1">' . "\n";
//	echo '					<option value="" >Seleccionar... </option>' . "\n";
	echo '					<option value="Zona de Espera" '; echo ($_SESSION['exp']['espacio'] == 'Zona de Espera') ? 'selected="1"' : ''; echo '>' . $lang['En Taller'] . '</option>' . "\n";
	echo '					<option value="Transito" '; echo ($_SESSION['exp']['espacio'] == 'Transito') ? 'selected="1"' : ''; echo '>' . $lang['Tránsito'] . '</option>' . "\n";
	echo '				</select></td></tr>'."\n";
	echo '									<tr><td>' . $lang['Torre'] . '</td><td style="text-align:left;"><input type="text" name="torre" size="4" maxlength="30" value="' . $_SESSION['exp']['torre'] . '" /></td></tr>'."\n";
	echo '								</table>
						</div>'."\n";

	echo '					</td>
				</tr>'."\n";
	echo '				<tr><td colspan="3"><input type="submit" name="valida_placas" value="Enviar" onclick="validarExp(rapida);return false;" /></td></tr>';
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

	$_SESSION['exp'] = array();

	if (!isset($razon) || $razon == '') {$razon = $nombre . ' ' . $apellidos;}
	$razon = strtoupper(preparar_entrada_bd($razon)); $_SESSION['exp']['razon'] = $razon;
	$placas = strtoupper(limpiarString($placas)); $_SESSION['exp']['placas'] = $placas;
	$serie = strtoupper(limpiarString($serie)); $_SESSION['exp']['serie'] = $serie;
	$marca = strtoupper(limpiarString($marca)); $_SESSION['exp']['marca'] = $marca;
	$tipo = strtoupper(limpiarString($tipo)); $_SESSION['exp']['tipo'] = $tipo;
	$subtipo = strtoupper(limpiarString($subtipo)); $_SESSION['exp']['subtipo'] = $subtipo;
	$modelo = preparar_entrada_bd($modelo); $_SESSION['exp']['modelo'] = $modelo;
	$colores = strtoupper(limpiarString($colores)); $_SESSION['exp']['colores'] = $colores;
	$puertas = strtoupper(limpiarString($puertas)); $_SESSION['exp']['puertas'] = $puertas;
	$nombre = strtoupper(preparar_entrada_bd($nombre)); $_SESSION['exp']['nombre'] = $nombre;
	$apellidos = strtoupper(preparar_entrada_bd($apellidos)); $_SESSION['exp']['apellidos'] = $apellidos;
	$_SESSION['exp']['clietipo'] = $clietipo;
	$_SESSION['exp']['deseaemail'] = $deseaemail;
	if($boletin == '' || $boletin == 'No') { $_SESSION['exp']['boletin'] = 'No'; } else { $_SESSION['exp']['boletin'] = 'Si'; }
	$email = preparar_entrada_bd($email); $_SESSION['exp']['email'] = $email;
	$telefono1 = preparar_entrada_bd($telefono1); $_SESSION['exp']['telefono1'] = $telefono1;
	$telefono2 = preparar_entrada_bd($telefono2); $_SESSION['exp']['telefono2'] = $telefono2;
	$movil = preparar_entrada_bd($movil); $_SESSION['exp']['movil'] = $movil;
	$movil2 = preparar_entrada_bd($movil2); $_SESSION['exp']['movil2'] = $movil2;
	$asesor = preparar_entrada_bd($asesor); $_SESSION['exp']['asesor'] = $asesor;
	$servicio = preparar_entrada_bd($servicio); $_SESSION['exp']['servicio'] = $servicio;
	$garantia = preparar_entrada_bd($garantia); $_SESSION['exp']['garantia'] = $garantia;
	$categoria = preparar_entrada_bd($categoria); $_SESSION['exp']['categoria'] = $categoria;
	$orden_id = preparar_entrada_bd($orden_id); $_SESSION['exp']['orden_id'] = $orden_id;
	$_SESSION['exp']['espacio'] = $espacio;
//	$odometro = preparar_entrada_bd($odometro); $_SESSION['exp']['odometro'] = $odometro;
	$torre = preparar_entrada_bd($torre); $_SESSION['exp']['$torre'] = $torre;

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

//	echo $existe;

	if($existe != 1) { 
		$parametros='';
		$hacer = 'insertar';
	} else {
		$hacer = 'actualizar';
	}

//  ------------------    Registro Express de datos de facturación   ----------------------

//////////////////        Revisar formulario de captura antes de activar

	$sql_data_ord = array();

	if($existe != 1) { 
	
	
		$preg0 = "SELECT * FROM " . $dbpfx . "empresas WHERE empresa_razon_social LIKE '$razon'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de empresas!");
		$fila0 = mysql_num_rows($matr0);
		if($fila0 > 0) {
			$_SESSION['msjerror'] = $lang['Ya existe empresa'] . ' ' . $emp['empresa_razon_social'] . ' ' . $lang['Elija una'];
//			print_r($_SESSION['exp']);
			redirigir('personas.php?accion=consultar&empresa=' . $razon);
		}

		$sql_data_array = array('empresa_razon_social' => $razon);
	
		ejecutar_db($dbpfx . 'empresas', $sql_data_array, $hacer);
		$empresa_id = mysql_insert_id(); 
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
		$parametros='';      	
		$str = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz1234567890";
		$clave = "";
		for($i=0;$i<6;$i++) {$clave .= substr($str,rand(0,57),1);}
		$sql_data_array['cliente_clave'] = $clave;

		ejecutar_db($dbpfx . 'clientes', $sql_data_array, $hacer);
		unset($sql_data_array);
		$cliente_id = mysql_insert_id(); 
	 	
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
		ejecutar_db($dbpfx . 'vehiculos', $sql_data_array, $hacer);
		unset($sql_data_array);
		$vehiculo_id = mysql_insert_id();
		
		$sql_data_ord['orden_vehiculo_marca'] = $marca;
		$sql_data_ord['orden_vehiculo_tipo'] = $tipo;
		$sql_data_ord['orden_vehiculo_color'] = $colores;
		$sql_data_ord['orden_vehiculo_placas'] = $placas;
	}


	$sql_data_ord['orden_cliente_id'] = $cliente_id;
	$sql_data_ord['orden_vehiculo_id'] = $vehiculo_id;
	$sql_data_ord['orden_asesor_id'] = $asesor;
	$sql_data_ord['orden_servicio'] = $servicio;
	$sql_data_ord['orden_categoria'] = $categoria;
	$sql_data_ord['orden_odometro'] = $odometro;
	$sql_data_ord['orden_ubicacion'] = 'Recepción';
	$sql_data_ord['orden_garantia'] = $garantia;
	$sql_data_ord['orden_paga_deducible'] = $deducible;
	$sql_data_ord['orden_torre'] = $torre;
	$sql_data_ord['orden_alerta'] = '0';
	$sql_data_ord['orden_fecha_ultimo_movimiento'] = date('Y-m-d H:i:s');
	$sql_data_ord['orden_estatus'] = '1';
	if($metrico == 2) {
		$sql_data_ord['orden_odometro'] = $litros;
	}
/*	if($saltapres == '1') {
		$sql_data_ord['orden_estatus'] = '1';
	} else {
		$sql_data_ord['orden_estatus'] = '24';
	}
*/	
	if($confolio == 1) {
		$parametros = "oid = '".$oid."'";
		$sql_data_ord['orden_id'] = $orden_id; 
	} else {
		$parametros = "orden_id = '".$orden_id."'";
	}
	ejecutar_db($dbpfx . 'ordenes', $sql_data_ord, 'actualizar', $parametros);
	bitacora($orden_id, 'Registro Express terminado', $dbpfx);
	unset($_SESSION['exp']);

/*	if($categoria != '2') {	
		bitacora($orden_id, 'Cambio de Categoria de Servicio a categoria ' . constant('CATEGORIA_DE_REPARACION_' . $categoria), $dbpfx, 'Cambio de Categoria de Servicio a categoria ' . constant('CATEGORIA_DE_REPARACION_' . $categoria),0);
	}
*/

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