<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/vehiculos.php');
    
if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

if (($accion==='insertar') || ($accion==='actualizar')) { 
	/* no cargar encabezado */
} else {

}

if (($accion==="crear" ) || ($accion==="modificar")) {
	
	$funnum = 1145000;
	
	$retorno = validaAcceso($funnum, $dbpfx);
	
	if ($retorno == '1' || $_SESSION['rol06'] == '1' ) {	
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
	} else {
		redirigir('usuarios.php?mensaje='. $lang['Acceso NO autorizado']);
	}	
	$error='no';
	if($accion==="crear") {
		if ($cliente_id == 0 || is_numeric($cliente_id)!= 1) {
			$error = 'si';
			$mensaje = $lang['alta vehiculo desde perfil cliente'];
		} else {
			$preg0 = "SELECT * FROM " . $dbpfx . "clientes WHERE cliente_id ='$cliente_id'";
			$matr0 = mysql_query($preg0);
			$num_clie = mysql_num_rows($matr0);
			if($num_clie > 0) {
				$clie = mysql_fetch_array($matr0);
				$encabezado = $lang['Agregando vehiculo'] . $clie['cliente_nombre'] . ' ' . $clie['cliente_apellidos'];
			} else {
				$error = 'si';
				$mensaje = $lang['No encontró cliente'] . $cliente_id;
			}
		}
	}
	if($accion==="modificar") {
		if (($placas!='') || ($vehiculo_id!='')) {
			$error = 'no'; $mensaje ='';
			$pregunta = "SELECT * FROM " . $dbpfx . "vehiculos WHERE ";
			if ($placas) {$pregunta .= "vehiculo_placas = '$placas' ";}
			if (($placas) && ($vehiculo_id)) {$pregunta .= "AND vehiculo_id = '$vehiculo_id' ";}
			elseif ($vehiculo_id) {$pregunta .= "vehiculo_id = '$vehiculo_id' ";}
		}
	   $matriz = mysql_query($pregunta);
	   $num_cols = mysql_num_rows($matriz);
	   if ($num_cols == 1) {
	   	$vehiculo = mysql_fetch_array($matriz);
	   	$encabezado = $lang['Modificando vehículo'];
		} elseif($num_cols > 1) {
			$error='si'; $mensaje=$lang['datos iguales de vehiculo'];
		} else {
			$error='si'; $mensaje=$lang['No encontró vehículo'];
		}
	}
			
//	echo 'Estamos en la sección crear';
	if($error=='no') {
		if($_SESSION['exp']['placas'] != '') {
			unset($_SESSION['vehiculo']);
			$_SESSION['vehiculo'] = array();
			$_SESSION['vehiculo']['placas'] = $_SESSION['exp']['placas'];
			$_SESSION['vehiculo']['serie'] = $_SESSION['exp']['serie'];
			$_SESSION['vehiculo']['marca'] = $_SESSION['exp']['marca'];
			$_SESSION['vehiculo']['tipo'] = $_SESSION['exp']['tipo'];
			$_SESSION['vehiculo']['subtipo'] = $_SESSION['exp']['subtipo'];
			$_SESSION['vehiculo']['modelo'] = $_SESSION['exp']['modelo'];
			$_SESSION['vehiculo']['puertas'] = $_SESSION['exp']['puertas'];
			$_SESSION['vehiculo']['color'] = $_SESSION['exp']['colores'];
			unset($_SESSION['exp']);
		}


		echo '	<br>
		<form action="vehiculos.php?accion='; 
		if ($accion==="modificar") 
			{ echo 'actualizar';} 
		else {echo 'insertar';} 
		echo '" method="post" enctype="multipart/form-data" name="filtro">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td colspan="2"><span class="alerta">' . $_SESSION['vehiculo']['mensaje'] . '</span></td></tr>
		<tr class="cabeza_tabla"><td colspan="2">' . $encabezado . '</td></tr>
		<tr><td><strong>* ' . $lang['Placas'] . '</strong></td><td><input type="text" name="placas" size="60" maxlength="60" value="'; 
		echo ($vehiculo['vehiculo_placas'] != '') ? $vehiculo['vehiculo_placas'] : $_SESSION['vehiculo']['placas']; 
		echo '" /></td></tr>'."\n";
		echo '		<tr><td><strong>* ' . $lang['Serie'] . '</strong></td><td><input type="text" name="serie" size="60" maxlength="60" value="';
		echo ($vehiculo['vehiculo_serie'] != '') ? $vehiculo['vehiculo_serie'] : $_SESSION['vehiculo']['serie']; 
		echo '" /></td></tr>'."\n";

		if($valor['UsarMarcas'][0] == 1) {
			echo '		<tr><td><strong>* ' . FABRICANTE . '</strong>' . $_SESSION['vehiculo']['marca'] . '/' .  $vehiculo['vehiculo_marca'] . '</td><td style="text-align:left;">'."\n";
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
				if($_SESSION['vehiculo']['marca'] == '' && $vehiculo['vehiculo_marca'] == $ma['marca_nombre']) {
					echo ' selected="selected" ';
					$marca = $ma['marca_id'];
				}
                                if($_SESSION['vehiculo']['marca'] == $ma['marca_nombre']) {
					echo ' selected="selected" ';
					$marca = $ma['marca_id'];
					$_SESSION['vehiculo']['tipo'] = '';
					$vehiculo['vehiculo_tipo'] = '';
				}

				echo '>' . $ma['marca_nombre'] . '</option>'."\n";
			}
			echo '			</select>'."\n";
			echo '		</td></tr>'."\n";
			echo '		<tr><td><strong>* ' . TIPO . '</strong>' . $_SESSION['vehiculo']['tipo'] . '/' .  $vehiculo['vehiculo_tipo'] . '</td><td style="text-align:left;">'."\n";
			echo '			<select name="tipo" size="1">'."\n";
			echo '				<option value="0">Seleccione Modelo</option>'."\n";
			$preg2 = "SELECT modelo_id, modelo_nombre FROM modelos WHERE marca_id = '" . $marca . "' ORDER BY modelo_nombre";
//			echo 'MO -> ' . $preg2;
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de modelos! " . $preg2);
			while ($mod = mysql_fetch_array($matr2)) {
				echo '				<option value="' . $mod['modelo_nombre'] . '"';
				if(($_SESSION['vehiculo']['tipo'] == '' && $vehiculo['vehiculo_tipo'] == $mod['modelo_nombre']) || ($_SESSION['vehiculo']['tipo'] == $mod['modelo_nombre'])) { echo ' selected="selected" '; }
				echo '>' . $mod['modelo_nombre'] . '</option>'."\n";
			}
			echo '				<option value="OTRO TIPO">OTRO TIPO</option>'."\n";
			echo '			</select>'."\n";
// ------ Cierre de ASEBase de datos comunes -----------------------------		
			mysql_close();
			mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
			mysql_select_db($dbnombre) or die('Falló la seleccion la DB');
			echo '		</td></tr>'."\n";
		} else {
			echo '		<tr><td><strong>* ' . FABRICANTE . '</strong></td><td><input type="text" name="marca" size="60" maxlength="120" value="';
			echo ($vehiculo['vehiculo_marca'] != '') ? $vehiculo['vehiculo_marca'] : $_SESSION['vehiculo']['marca']; 
			echo '" /></td></tr>'."\n";
			echo '		<tr><td><strong>* ' . TIPO . '</strong></td><td><input type="text" name="tipo" size="60" maxlength="40" value="';
			echo ($vehiculo['vehiculo_tipo'] != '') ? $vehiculo['vehiculo_tipo'] : $_SESSION['vehiculo']['tipo'];
			echo '" /></td></tr>'."\n";
		}
		echo '		<tr><td>' . SUBTIPO . '</td><td><input type="text" name="subtipo" size="60" maxlength="40" value="';
		echo ($vehiculo['vehiculo_subtipo'] != '') ? $vehiculo['vehiculo_subtipo'] : $_SESSION['vehiculo']['subtipo'];
		echo '" /></td></tr>
		<tr><td><strong>* ' . YEAR . '</strong></td><td><input type="text" name="modelo" size="60" maxlength="40" value="';
		echo ($vehiculo['vehiculo_modelo'] != '') ? $vehiculo['vehiculo_modelo'] : $_SESSION['vehiculo']['modelo'];
		echo '" /></td></tr>
		<tr><td>';
		if($docingreso == 1) {
			echo '<strong>* ' . COLOR . '</strong>';
		} else {
			echo COLOR;
		}			
		echo '</td><td><input type="text" name="color" size="60" maxlength="40" value="';
		echo ($vehiculo['vehiculo_color'] != '') ? $vehiculo['vehiculo_color'] : $_SESSION['vehiculo']['color'];
		echo '" /></td></tr>'."\n";
		echo '		<tr><td>' . PUERTAS . '</td><td><input type="text" name="puertas" size="60" maxlength="40" value="';
		echo ($vehiculo['vehiculo_puertas'] != '') ? $vehiculo['vehiculo_puertas'] : $_SESSION['vehiculo']['puertas'];
		echo '" /></td></tr>'."\n";
		echo '		<tr><td>' . NUM_MOTOR . '</td><td><input type="text" name="motor" size="60" maxlength="120" value="';
		echo ($vehiculo['vehiculo_motor'] != '') ? $vehiculo['vehiculo_motor'] : $_SESSION['vehiculo']['motor']; 
		echo '" /></td></tr>
		<tr><td>' . TIPO_MOTOR . '</td><td><input type="text" name="tipomotor" size="60" maxlength="120" value="';
		echo ($vehiculo['vehiculo_tipomotor'] != '') ? $vehiculo['vehiculo_tipomotor'] : $_SESSION['vehiculo']['tipomotor']; 
		echo '" /></td></tr>
		<tr><td>' . CILINDROS . '</td><td><input type="text" name="cilindros" size="60" maxlength="120" value="';
		echo ($vehiculo['vehiculo_cilindros'] != '') ? $vehiculo['vehiculo_cilindros'] : $_SESSION['vehiculo']['cilindros']; 
		echo '" /></td></tr>
		<tr><td>' . LITROS . '</td><td><input type="text" name="litros" size="60" maxlength="120" value="';
		echo ($vehiculo['vehiculo_litros'] != '') ? $vehiculo['vehiculo_litros'] : $_SESSION['vehiculo']['litros']; 
		echo '" /></td></tr>'."\n";
	//caracteristicas
		if($vehad == '1') {
			echo '		<tr><td>' . TIPO_TRANSMISION	 . '</td><td style="text-align:left;">&nbsp;&nbsp;<label>'.$lang['Estándar'].'</label><input type="radio" name="transmision" value="1"';
			if($vehiculo['vehiculo_transmision'] == '1' || $_SESSION['vehiculo']['transmision'] == '1') { echo ' checked="checked"'; } 
			echo ' />'.$lang['Automático'].'<input  type="radio" name="transmision" value="2"';
			if($vehiculo['vehiculo_transmision'] == '2' || $_SESSION['vehiculo']['transmision'] == '2') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . SEGUROS_PTAS . '</td><td style="text-align:left;">&nbsp;&nbsp;'.$lang['Manual'].'<input type="radio" name="seguros" value="1"';
			if($vehiculo['vehiculo_seguros'] == '1' || $_SESSION['vehiculo']['seguros'] == '1') { echo ' checked="checked" '; } 
			echo ' />'.$lang['Eléctricos'].'<input  type="radio" name="seguros" value="2"';
			if($vehiculo['vehiculo_seguros'] == '2' || $_SESSION['vehiculo']['seguros'] == '2') { echo ' checked="checked" '; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . AIRE_ACONDICINADO . '</td><td style="text-align:left;">&nbsp;&nbsp;'.$lang['Sí'].'<input type="radio" name="aa" value="1"';
			if($vehiculo['vehiculo_aa'] == '1' || $_SESSION['vehiculo']['aa'] == '1') { echo ' checked="checked" '; } 
			echo ' />'.$lang['No'].'<input  type="radio" name="aa" value="2"';
			if($vehiculo['vehiculo_aa'] == '2' || $_SESSION['vehiculo']['aa'] == '2') { echo ' checked="checked" '; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . ELEVADORES . '</td><td style="text-align:left;">&nbsp;&nbsp;'.$lang['Manual'].'<input type="radio" name="elevadores" value="1"';
			if($vehiculo['vehiculo_elevadores'] == '1' || $_SESSION['vehiculo']['elevadores'] == '1') { echo ' checked="checked" '; } 
			echo ' />'.$lang['Eléctricos'].'<input  type="radio" name="elevadores" value="2"';
			if($vehiculo['vehiculo_elevadores'] == '2' || $_SESSION['vehiculo']['elevadores'] == '2') { echo ' checked="checked" '; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . ESPEJOS . '</td><td style="text-align:left;">&nbsp;&nbsp;'.$lang['Manual'].'<input type="radio" name="espejos" value="1"';
			if($vehiculo['vehiculo_espejos'] == '1' || $_SESSION['vehiculo']['espejos'] == '1') { echo ' checked="checked" '; } 
			echo ' />'.$lang['Eléctricos'].'<input  type="radio" name="espejos" value="2"';
			if($vehiculo['vehiculo_espejos'] == '2' || $_SESSION['vehiculo']['espejos'] == '2') { echo ' checked="checked" '; }
			echo ' />'.$lang['Térmicos'].'<input  type="radio" name="espejos" value="3"';
			if($vehiculo['vehiculo_espejos'] == '3' || $_SESSION['vehiculo']['espejos'] == '3') { echo ' checked="checked" '; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . VIDRIOS . '</td><td style="text-align:left;">&nbsp;&nbsp;'.$lang['Tintado'].'<input type="radio" name="vidrios" value="1"';
			if($vehiculo['vehiculo_vidrios'] == '1' || $_SESSION['vehiculo']['vidrios'] == '1') { echo ' checked="checked" '; } 
			echo ' />'.$lang['Sombreado'].'<input  type="radio" name="vidrios" value="2"';
			if($vehiculo['vehiculo_vidrios'] == '2' || $_SESSION['vehiculo']['vidrios'] == '2') { echo ' checked="checked" '; }
			echo ' /> Claro<input  type="radio" name="vidrios" value="3"';
			if($vehiculo['vehiculo_vidrios'] == '3' || $_SESSION['vehiculo']['vidrios'] == '3') { echo ' checked="checked" '; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . MEDALLON . '</td><td style="text-align:left;">&nbsp;&nbsp;'.$lang['con defroster'].'<input type="radio" name="medallon" value="1"';
			if($vehiculo['vehiculo_medallon'] == '1' || $_SESSION['vehiculo']['medallon'] == '1') { echo ' checked="checked"'; } 
			echo ' /> Sin defroster<input  type="radio" name="medallon" value="2"';
			if($vehiculo['vehiculo_medallon'] == '2' || $_SESSION['vehiculo']['medallon'] == '2') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . PARABRISAS . '</td><td style="text-align:left;">&nbsp;&nbsp;Sombreado<input type="radio" name="parabrisas" value="1"';
			if($vehiculo['vehiculo_parabrisas'] == '1' || $_SESSION['vehiculo']['parabrisas'] == '1') { echo ' checked="checked"'; } 
			echo ' /> Claro<input  type="radio" name="parabrisas" value="2"';
			if($vehiculo['vehiculo_parabrisas'] == '2' || $_SESSION['vehiculo']['parabrisas'] == '2') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . QUEMACOCOS . '</td><td style="text-align:left;">&nbsp;&nbsp;Manual<input type="radio" name="quemacocos" value="1"';
			if($vehiculo['vehiculo_quemacocos'] == '1' || $_SESSION['vehiculo']['quemacocos'] == '1') { echo ' checked="checked"'; } 
			echo ' /> Eléctrico<input  type="radio" name="quemacocos" value="2"';
			if($vehiculo['vehiculo_quemacocos'] == '2' || $_SESSION['vehiculo']['quemacocos'] == '2') { echo ' checked="checked"'; }
			echo ' /> No<input  type="radio" name="quemacocos" value="3"';
			if($vehiculo['vehiculo_quemacocos'] == '3' || $_SESSION['vehiculo']['quemacocos'] == '3') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . DIRECCION . '</td><td style="text-align:left;">&nbsp;&nbsp;Estándar<input type="radio" name="direccion" value="1"';
			if($vehiculo['vehiculo_direccion'] == '1' || $_SESSION['vehiculo']['direccion'] == '1') { echo ' checked="checked"'; } 
			echo ' /> Hidráulica<input type="radio" name="direccion" value="2"';
			if($vehiculo['vehiculo_direccion'] == '2' || $_SESSION['vehiculo']['direccion'] == '2') { echo ' checked="checked"'; }
			echo ' /> Electrónica<input type="radio" name="direccion" value="3"';
			if($vehiculo['vehiculo_direccion'] == '3' || $_SESSION['vehiculo']['direccion'] == '3') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . BOLSA_AIRE . '</td><td style="text-align:left;">&nbsp;&nbsp;No<input type="radio" name="bolsa_aire" value="0"';
			if($vehiculo['vehiculo_bolsa_aire'] == '0' || $_SESSION['vehiculo']['bolsa_aire'] == '0') { echo ' checked="checked"'; } 
			echo ' />&nbsp;&nbsp; 1<input  type="radio" name="bolsa_aire" value="1"';
			if($vehiculo['vehiculo_bolsa_aire'] == '1' || $_SESSION['vehiculo']['bolsa_aire'] == '1') { echo ' checked="checked"'; }
			echo ' />&nbsp;&nbsp; 2<input  type="radio" name="bolsa_aire" value="2"';
			if($vehiculo['vehiculo_bolsa_aire'] == '2' || $_SESSION['vehiculo']['bolsa_aire'] == '2') { echo ' checked="checked"'; }
			echo ' />&nbsp;&nbsp; 4<input  type="radio" name="bolsa_aire" value="4"';
			if($vehiculo['vehiculo_bolsa_aire'] == '4' || $_SESSION['vehiculo']['bolsa_aire'] == '4') { echo ' checked="checked"'; }
			echo ' />&nbsp;&nbsp; 6<input  type="radio" name="bolsa_aire" value="6"';
			if($vehiculo['vehiculo_bolsa_aire'] == '6' || $_SESSION['vehiculo']['bolsa_aire'] == '6') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . TIPO_FRENOS . '</td><td style="text-align:left;">&nbsp;&nbsp;Normal<input type="radio" name="frenos" value="1"';
			if($vehiculo['vehiculo_frenos'] == '1' || $_SESSION['vehiculo']['frenos'] == '1') { echo ' checked="checked"'; } 
			echo ' /> ABS<input type="radio" name="frenos" value="2"';
			if($vehiculo['vehiculo_frenos'] == '2' || $_SESSION['vehiculo']['frenos'] == '2') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . TIPO_SUSPENSION . '</td><td style="text-align:left;">&nbsp;&nbsp;Normal<input type="radio" name="suspension" value="1"';
			if($vehiculo['vehiculo_suspension'] == '1' || $_SESSION['vehiculo']['suspension'] == '1') { echo ' checked="checked"'; } 
			echo ' /> Deportiva<input type="radio" name="suspension" value="2"';
			if($vehiculo['vehiculo_suspension'] == '2' || $_SESSION['vehiculo']['suspension'] == '2') { echo ' checked="checked"'; }
			echo ' /> Electrónica<input type="radio" name="suspension" value="3"';
			if($vehiculo['vehiculo_suspension'] == '3' || $_SESSION['vehiculo']['suspension'] == '3') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . TIPO_RINES . '</td><td style="text-align:left;">&nbsp;&nbsp;Placacero<input type="radio" name="tring" value="1"';
			if($vehiculo['vehiculo_tipo_ring'] == '1' || $_SESSION['vehiculo']['tring'] == '1') { echo ' checked="checked"'; } 
			echo ' /> Aluminio<input type="radio" name="tring" value="2"';
			if($vehiculo['vehiculo_tipo_ring'] == '2' || $_SESSION['vehiculo']['tring'] == '2') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . RINES_ORIGINALES . '</td><td style="text-align:left;">&nbsp;&nbsp;No<input type="radio" name="oring" value="1"';
			if($vehiculo['vehiculo_ring_original'] == '1' || $_SESSION['vehiculo']['oring'] == '1') { echo ' checked="checked"'; } 
			echo ' /> Sí<input type="radio" name="oring" value="2"';
			if($vehiculo['vehiculo_ring_original'] == '2' || $_SESSION['vehiculo']['oring'] == '2') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . VESTIDURAS . '</td><td style="text-align:left;">&nbsp;&nbsp;De Piel<input type="radio" name="vestiduras" value="1"';
			if($vehiculo['vehiculo_vestiduras'] == '1' || $_SESSION['vehiculo']['vestiduras'] == '1') { echo ' checked="checked"'; } 
			echo ' /> Tela<input type="radio" name="vestiduras" value="2"';
			if($vehiculo['vehiculo_vestiduras'] == '2' || $_SESSION['vehiculo']['vestiduras'] == '2') { echo ' checked="checked"'; }
			echo ' /> Vinipiel<input type="radio" name="vestiduras" value="3"';
			if($vehiculo['vehiculo_vestiduras'] == '3' || $_SESSION['vehiculo']['vestiduras'] == '3') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . TIPO_DE_FAROS . '</td><td style="text-align:left;">&nbsp;&nbsp;Lisos<input type="radio" name="faros" value="1"';
			if($vehiculo['vehiculo_faros'] == '1' || $_SESSION['vehiculo']['faros'] == '1') { echo ' checked="checked"'; } 
			echo ' /> Rayados<input type="radio" name="faros" value="2"';
			if($vehiculo['vehiculo_faros'] == '2' || $_SESSION['vehiculo']['faros'] == '2') { echo ' checked="checked"'; }
			echo ' /> Doble Parábola<input type="radio" name="faros" value="3"';
			if($vehiculo['vehiculo_faros'] == '3' || $_SESSION['vehiculo']['faros'] == '3') { echo ' checked="checked"'; }
			echo ' /> Con Lupa<input type="radio" name="faros" value="4"';
			if($vehiculo['vehiculo_faros'] == '2' || $_SESSION['vehiculo']['faros'] == '2') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
			echo '		<tr><td>' . NUM_VEL . '</td><td style="text-align:left;">&nbsp;&nbsp; 4<input type="radio" name="velocidad" value="1"';
			if($vehiculo['vehiculo_velocidad'] == '1' || $_SESSION['vehiculo']['velocidad'] == '1') { echo ' checked="checked"'; } 
			echo ' /> 5<input type="radio" name="velocidad" value="2"';
			if($vehiculo['vehiculo_velocidad'] == '2' || $_SESSION['vehiculo']['velocidad'] == '2') { echo ' checked="checked"'; }
			echo ' /> 6<input type="radio" name="velocidad" value="3"';
			if($vehiculo['vehiculo_velocidad'] == '3' || $_SESSION['vehiculo']['velocidad'] == '3') { echo ' checked="checked"'; }
			echo ' /> Variables<input type="radio" name="velocidad" value="4"';
			if($vehiculo['vehiculo_velocidad'] == '4' || $_SESSION['vehiculo']['velocidad'] == '4') { echo ' checked="checked"'; }
			echo ' /></td></tr>'."\n";
		}
		
		if ($accion==="modificar") {
			echo'		<tr><td>' . $lang['Activo'] . '</td><td style="text-align:left;"><input type="checkbox" name="status" value="1"';
			if ($vehiculo['vehiculo_status']=="1") { echo ' checked="checked"'; }
			echo ' /></td></tr>';
			echo '	<input type="hidden" name="vehiculo_id" value="' . $vehiculo['vehiculo_id'] . '" />
			<input type="hidden" name="regexp" value="' . $regexp . '" />
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<input type="hidden" name="cliente_id" value="' . $vehiculo['vehiculo_cliente_id'] . '" />';
		} else {
			echo'		<input type="hidden" name="status" value="1" /><input type="hidden" name="cliente_id" value="' . $cliente_id . '" />'; 
		}
		echo '		<tr><td colspan="2" style="text-align:left;"><strong>* ' . $lang['Datos mínimos'] . '</strong></td></tr>'."\n";
		echo '		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
	</table>
	</form>';
	} else {
		echo $mensaje;
	}
}

elseif (($accion==='insertar') || ($accion==='actualizar')) {
	
	$funnum = 1145000;
	$retorno = validaAcceso($funnum, $dbpfx);
	
//	echo 'Estamos en la sección inserta.<br>';
	if ($retorno == '1' || $_SESSION['rol06']=='1') {
		$msj = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	unset($_SESSION['vehiculo']);
	$_SESSION['vehiculo'] = array();
	$placas = strtoupper(limpiarString($placas)); $_SESSION['vehiculo']['placas'] = $placas;
	$serie=strtoupper(preparar_entrada_bd($serie)); $_SESSION['vehiculo']['serie'] = $serie;
	$motor=strtoupper(preparar_entrada_bd($motor)); $_SESSION['vehiculo']['motor'] = $motor;
	$tipomotor=strtoupper(limpiarString($tipomotor)); $_SESSION['vehiculo']['tipomotor'] = $tipomotor;
	$cilindros=strtoupper(limpiarString($cilindros)); $_SESSION['vehiculo']['cilindros'] = $cilindros;
	$litros=strtoupper(limpiarString($litros)); $_SESSION['vehiculo']['litros'] = $litros;
	$marca=strtoupper($marca); $_SESSION['vehiculo']['marca'] = $marca;
	$tipo=strtoupper($tipo); $_SESSION['vehiculo']['tipo'] = $tipo;
	$subtipo=strtoupper($subtipo); $_SESSION['vehiculo']['subtipo'] = $subtipo;
	$modelo=limpiarNumero($modelo); $_SESSION['vehiculo']['modelo'] = $modelo;
	$puertas=limpiarNumero($puertas); $_SESSION['vehiculo']['puertas'] = $puertas;
	$color=strtoupper(limpiarString($color)); $_SESSION['vehiculo']['color'] = $color;
	$_SESSION['vehiculo']['transmision'] = $transmision;
	$_SESSION['vehiculo']['seguros'] = $seguros;
	$_SESSION['vehiculo']['aa'] = $aa;
	$_SESSION['vehiculo']['elevadores'] = $elevadores;
	$_SESSION['vehiculo']['espejos'] = $espejos;
	$_SESSION['vehiculo']['vidrios'] = $vidrios;
	$_SESSION['vehiculo']['medallon'] = $medallon;
	$_SESSION['vehiculo']['parabrisas'] = $parabrisas;
	$_SESSION['vehiculo']['quemacocos'] = $quemacocos;
	$_SESSION['vehiculo']['direccion'] = $direccion;
	$_SESSION['vehiculo']['bolsa_aire'] = $bolsa_aire;
	$_SESSION['vehiculo']['frenos'] = $frenos;
	$_SESSION['vehiculo']['suspension'] = $suspension;
	$_SESSION['vehiculo']['tring'] = $tring;
	$_SESSION['vehiculo']['oring'] = $oring;
	$_SESSION['vehiculo']['vestiduras'] = $vestiduras;
	$_SESSION['vehiculo']['faros'] = $faros;
   $_SESSION['vehiculo']['velocidad']=$velocidad;

	$aseguradora=strtoupper($aseguradora); $_SESSION['vehiculo']['aseguradora'] = $aseguradora;
	$poliza=preparar_entrada_bd($poliza); $_SESSION['vehiculo']['poliza'] = $poliza;
//	$cliente_id=preparar_entrada_bd($cliente_id);
//	$status=preparar_entrada_bd($status);

	$error = 'no';
	$mensaje= '';
//	echo '<br><br>====== ' . $error . ' <<>> ' . $mensaje . ' ======<br><br>';
//	echo strlen($telefono1);

	if (strlen($placas) < 4) {$error = 'si'; $mensaje .='El número de placas es muy corto: ' . $placas . '<br>';}
	if (strlen($serie) < 4) {$error = 'si'; $mensaje .='El número de serie o VIN es muy corto: ' . $serie . '<br>';}
	if (strlen($marca) < 2) {$error = 'si'; $mensaje .='El nombre de la Marca (fabricante) es muy corto: ' . $marca . '<br>';}
	if (strlen($tipo) < 2) {$error = 'si'; $mensaje .='El nombre del Tipo (submarca) es muy corto: ' . $tipo . '<br>';}
	if (strlen($color) < 3) {$error = 'si'; $mensaje .='El nombre del color es muy corto: ' . $color . '<br>';}
	if (strlen($modelo) < 4 || strlen($modelo) > 4) {$error = 'si'; $mensaje .='El año debe tener 4 números (por ejemplo 2012): ' . $modelo . '<br>';}
	if($vehad=='1') {
/*
		if($transmision < '1' || $transmision > '2' ) {$error = 'si'; $mensaje .='Indique el tipo de transmisión. <br>';}
		if($seguros < '1' || $seguros > '2' ) {$error = 'si'; $mensaje .='Indique el tipo de Seguros de puertas. <br>';}
		if($aa < '1' || $aa > '2' ) {$error = 'si'; $mensaje .='Indique si tiene Aire Acondicionado. <br>';}
		if($elevadores < '1' || $elevadores > '2' ) {$error = 'si'; $mensaje .='Indique el tipo de Elevadores de Cristales. <br>';}
		if($espejos < '1' || $espejos > '3' ) {$error = 'si'; $mensaje .='Indique el tipo de Espejos laterales. <br>';}
		if($vidrios < '1' || $vidrios > '3' ) {$error = 'si'; $mensaje .='Indique el color de cristales. <br>';}
		if($medallon < '1' || $medallon > '2' ) {$error = 'si'; $mensaje .='Indique si el Medallón tiene defroster. <br>';}
		if($parabrisas < '1' || $parabrisas > '2' ) {$error = 'si'; $mensaje .='Indique el tipo de parabrisas. <br>';}
		if($quemacocos < '1' || $quemacocos > '3' ) {$error = 'si'; $mensaje .='Indique el tipo de quemacocos. <br>';}
		if($direccion < '1' || $direccion > '3' ) {$error = 'si'; $mensaje .='Indique el tipo de dirección. <br>';}
		if($bolsa_aire < '1' || $bolsa_aire > '2' ) {$error = 'si'; $mensaje .='Indique si hay bolsa de aire . <br>';}
		if($frenos < '1' || $frenos > '2' ) {$error = 'si'; $mensaje .='Indique el tipo de frenos. <br>';}
		if($suspension < '1' || $suspension > '3' ) {$error = 'si'; $mensaje .='Indique el tipo de suspensión. <br>';}
*/
	}
	
	if($accion==='insertar') {
		$preg = "SELECT vehiculo_id FROM " . $dbpfx . "vehiculos WHERE vehiculo_placas = '$placas'";
		if($serie != '' && $noverifserie != '1') { $preg .= " OR vehiculo_serie ='$serie'"; }
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de Ordenes! L169" . $preg);
		$fila = mysql_num_rows($matr);
		if($fila > 0) {$error = 'si'; $mensaje .='Ya existe otro vehículo con el mismo número de placas o VIN.<br>';}
	}

//	echo '<br><br>====== ' . $error . ' ======<br><br>';
   if ($error === 'no') {
   	if($accion==='actualizar') {$parametros='vehiculo_id = ' . $vehiculo_id;} else {$parametros='';}
   	if($serie == '') { $serie = $placas; }
		$sql_data_array = array('vehiculo_placas' => $placas,
			'vehiculo_serie' => $serie,
			'vehiculo_motor' => $motor,
			'vehiculo_tipomotor' => $tipomotor,
			'vehiculo_cilindros' => $cilindros,
			'vehiculo_litros' => $litros,
			'vehiculo_marca' => $marca,
			'vehiculo_tipo' => $tipo,
			'vehiculo_subtipo' => $subtipo,
			'vehiculo_modelo' => $modelo,
			'vehiculo_puertas' => $puertas,
			'vehiculo_color' => $color,
			'vehiculo_aseguradora' => $aseguradora,
			'vehiculo_poliza' => $poliza,
			'vehiculo_cliente_id' => $cliente_id,
			'vehiculo_status' => $status);
		if($vehad=='1') {
			$sql_data_array['vehiculo_transmision'] = $transmision;
			$sql_data_array['vehiculo_seguros'] = $seguros;
			$sql_data_array['vehiculo_aa'] = $aa;
			$sql_data_array['vehiculo_elevadores'] = $elevadores;
			$sql_data_array['vehiculo_espejos'] = $espejos;
			$sql_data_array['vehiculo_vidrios'] = $vidrios;
			$sql_data_array['vehiculo_medallon'] = $medallon;
			$sql_data_array['vehiculo_parabrisas'] = $parabrisas;
			$sql_data_array['vehiculo_quemacocos'] = $quemacocos;
			$sql_data_array['vehiculo_direccion'] = $direccion;
			$sql_data_array['vehiculo_bolsa_aire'] = $bolsa_aire;
			$sql_data_array['vehiculo_frenos'] = $frenos;
			$sql_data_array['vehiculo_suspension'] = $suspension;
			$sql_data_array['vehiculo_tipo_ring'] = $tring;
			$sql_data_array['vehiculo_ring_original'] = $oring;
			$sql_data_array['vehiculo_vestiduras'] = $vestiduras;
			$sql_data_array['vehiculo_faros'] = $faros;
			$sql_data_array['vehiculo_velocidad']=$velocidad;
		}
//		print_r($sql_data_array);
	if ($accion==='insertar') {
		$vehiculo_id = ejecutar_db($dbpfx . 'vehiculos', $sql_data_array, $accion, $parametros);
	} else {
		ejecutar_db($dbpfx . 'vehiculos', $sql_data_array, $accion, $parametros);
	}

     	if($accion==='actualizar') {
     		$preg0 = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_vehiculo_id = '$vehiculo_id' AND orden_id > '0'";
     		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Ordenes! L199" . $preg0);
     		$fila0 = mysql_num_rows($matr0);
     		if($fila0 > 0) {
     			while($ord = mysql_fetch_array($matr0)) {
     				$parametros = "orden_id ='" . $ord['orden_id'] . "'";
     				$sql_data_array = array('orden_vehiculo_marca' => $marca,
     					'orden_vehiculo_tipo' => $tipo,
     					'orden_vehiculo_color' => $color,
     					'orden_vehiculo_placas' => $placas);
     				ejecutar_db($dbpfx . 'ordenes', $sql_data_array, $accion, $parametros);
     			}
     		}
     	}
     	unset($_SESSION['vehiculo']);
     	$cliente_id = '';
     	if($regexp == 1) {
			redirigir('reg-express.php?accion=express&orden_id=' . $orden_id);
     	} else {
			redirigir('vehiculos.php?accion=consultar&vehiculo_id=' . $vehiculo_id);
     	}
	} else {
		$_SESSION['vehiculo']['mensaje'] = $mensaje;
		if ($accion==='insertar') {
			redirigir('vehiculos.php?accion=crear&cliente_id=' . $cliente_id);
		} else {
			redirigir('vehiculos.php?accion=modificar&vehiculo_id=' . $vehiculo_id);
		}
	}
}

elseif ($accion==="consultar") {
	
	$funnum = 1145010;
	$retorno = validaAcceso($funnum, $dbpfx);
	
//	echo 'Estamos en la sección  consulta';
	if ($retorno == '1' || $_SESSION['codigo'] < '60'  || $_SESSION['codigo'] > '70' ) {	
// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado.');
	}	
	$error = 'si'; $num_cols = 0;
	if ($vehiculo_id!='') {
		$pregunta = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_id = '$vehiculo_id'";
		$error = 'no';
	} else {
		$placas=preparar_entrada_bd($placas);
		$cliente_id=preparar_entrada_bd($cliente_id);
		$mensaje= 'Se necesita al menos un dato para buscar.<br>';
		if (($placas!='') || ($cliente_id!='')) {
			$error = 'no'; $mensaje ='';
			$pregunta = "SELECT * FROM " . $dbpfx . "vehiculos WHERE ";
			if ($placas) {$pregunta .= "vehiculo_placas like '%$placas%' ";}
			if (($placas) && ($cliente_id)) {$pregunta .= "AND vehiculo_cliente_id = '$cliente_id' ";} 
				elseif ($cliente_id) {$pregunta .= "vehiculo_cliente_id = '$cliente_id' ";}
			$pregunta .= " AND vehiculo_status = '1'";
		}
	}
//		echo $pregunta;
	if($error == 'no') {
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
	}

	if ($num_cols == 1 && $error == 'no') {
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		echo '	<table cellspacing="2" cellpadding="2" border="1" style="font-size:1.2em;">'."\n";
		while ($vehiculo = mysql_fetch_array($matriz)) {
			if($cliente_id == '') { $cliente_id = $vehiculo['vehiculo_cliente_id']; }
			$preg0 = "SELECT cliente_nombre, cliente_apellidos, cliente_email, cliente_telefono1, cliente_movil FROM " . $dbpfx . "clientes WHERE cliente_id = '" . $cliente_id . "'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de Cliente!");
			$cli =  mysql_fetch_array($matr0);
			
			echo '		<tr class="cabeza_tabla"><td colspan="2" align="left">' . $lang['Datos del vehículo'] . ': ' . $vehiculo['vehiculo_marca'] . ' ' . $vehiculo['vehiculo_tipo'] . ' ' . $vehiculo['vehiculo_color'] . ' ' . $vehiculo['vehiculo_modelo'] . ' ' . $lang['Placas'] . ': ' . $vehiculo['vehiculo_placas'] . '</td></tr>
		<tr><td colspan="3">' . $lang['Propiedad del Cliente'] . ': ' . $cli['cliente_nombre'] . ' ' . $cli['cliente_apellidos'] . '. Tel: ' . $cli['cliente_telefono1'] . '. Movil: ' . $cli['cliente_movil'] . '</td></tr>'."\n";

			$retorno = validaAcceso('1145000', $dbpfx);
			if ($retorno == '1' || $_SESSION['rol06']==='1') {
				echo '		<tr><td colspan="2">
			<a href="vehiculos.php?accion=modificar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/cambiar-datos.png" alt="Modificar" title="Modificar"></a> 
			<a href="personas.php?accion=consultar&cliente_id=' . $vehiculo['vehiculo_cliente_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/datos-de-clientes.png" alt="Ver Datos del Cliente" title="Ver Datos del Cliente"></a> 
			<a href="previas.php?accion=consultar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/presupuesto-previo.png" alt="Ver o Crear Presupuesto Previo" title="Ver o Crear Presupuesto Previo"></a> 
			<a href="ordenes.php?accion=listar&placas=' . $vehiculo['vehiculo_placas'] . '"><img src="idiomas/' . $idioma . '/imagenes/ordenes-listar.png" alt="Ver Ordenes de Trabajo" title="Ver Ordenes de Trabajo"></a> '."\n";
				echo '			<a href="documentos.php?accion=listar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/documento.png" alt="Ver Documentos" title="Ver Documentos"></a>'."\n";
				echo '		</td></tr>'."\n";
			}
		
			echo '		<tr><td>' . $lang['Número de vehículo'] . '</td><td>' . $vehiculo['vehiculo_id'] . '</td></tr>
		<tr><td>' . $lang['Activo'] . '?</td><td>';
			if($vehiculo['vehiculo_status'] == 1) { echo $lang['Sí']; } else { echo $lang['No']; }
			echo '</td></tr>
		<tr><td>' . $lang['Serie'] . '</td><td>' . $vehiculo['vehiculo_serie'] . '</td></tr>
		<tr><td>' . $lang['Num Motor'] . '</td><td>' . $vehiculo['vehiculo_motor'] . '</td></tr>
		<tr><td>' . $lang['Subtipo'] . '</td><td>' . $vehiculo['vehiculo_subtipo'] . '</td></tr>
		<tr><td>' . $lang['Puertas'] . '</td><td>' . $vehiculo['vehiculo_puertas'] . '</td></tr>
		<tr><td>' . $lang['Tipo de Motor'] . '</td><td>' . $vehiculo['vehiculo_tipomotor'] . '</td></tr>
		<tr><td>' . $lang['Cilindros'] . '</td><td>' . $vehiculo['vehiculo_cilindros'] . '</td></tr>
		<tr><td>' . $lang['Cilindrada'] . '</td><td>' . $vehiculo['vehiculo_litros'] . '</td></tr>'."\n";

			if($vehad == '1') {
				echo '		<tr><td>' . TIPO_TRANSMISION . '</td><td>';
				if($vehiculo['vehiculo_transmision'] == '1') { echo 'Estándar'; } elseif($vehiculo['vehiculo_transmision'] == '2') { echo 'Automática'; }
				echo '</td></tr>'."\n";
				//numero de velocidades
				echo '		<tr><td>' . NUM_VEL . '</td><td>';
				if($vehiculo['vehiculo_velocidad'] == '1') { echo '4'; } elseif($vehiculo['vehiculo_velocidad'] == '2') { echo '5'; } elseif($vehiculo['vehiculo_velocidad'] == '3') { echo '6'; } elseif($vehiculo['vehiculo_velocidad'] == '4') { echo 'Variables'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . DIRECCION . '</td><td>';
				if($vehiculo['vehiculo_direccion'] == '1') { echo 'Estándar'; } elseif($vehiculo['vehiculo_direccion'] == '2') { echo 'Hidráulica'; } elseif($vehiculo['vehiculo_direccion'] == '3') { echo 'Electrónica'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . BOLSA_AIRE . '</td><td>';
				if($vehiculo['vehiculo_bolsa_aire'] == '0') { echo 'No'; } 
				elseif($vehiculo['vehiculo_bolsa_aire'] > '0') { echo $vehiculo['vehiculo_bolsa_aire']; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . AIRE_ACONDICINADO . '</td><td>';
				if($vehiculo['vehiculo_aa'] == '1') { echo 'Sí'; } elseif($vehiculo['vehiculo_aa'] == '2') { echo 'No'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . VESTIDURAS . '</td><td>';
				if($vehiculo['vehiculo_vestiduras'] == '1') { echo 'De Piel'; } elseif($vehiculo['vehiculo_vestiduras'] == '2') { echo 'Tela'; } elseif($vehiculo['vehiculo_vestiduras'] == '3') { echo 'Vinipiel'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . SEGUROS_PTAS . '</td><td>';
				if($vehiculo['vehiculo_seguros'] == '1') { echo 'Manual'; } elseif($vehiculo['vehiculo_seguros'] == '2') { echo 'Eléctricos'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . ELEVADORES . '</td><td>';
				if($vehiculo['vehiculo_elevadores'] == '1') { echo 'Manual'; } elseif($vehiculo['vehiculo_elevadores'] == '2') { echo 'Eléctricos'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . ESPEJOS . '</td><td>';
				if($vehiculo['vehiculo_espejos'] == '1') { echo 'Manual'; } elseif($vehiculo['vehiculo_espejos'] == '2') { echo 'Eléctricos'; } elseif($vehiculo['vehiculo_espejos'] == '3') { echo 'Térmicos'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . VIDRIOS . '</td><td>';
				if($vehiculo['vehiculo_vidrios'] == '1') { echo 'Tintado'; } elseif($vehiculo['vehiculo_vidrios'] == '2') { echo 'Sombreado'; } elseif($vehiculo['vehiculo_vidrios'] == '3') { echo 'Claro'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . MEDALLON . '</td><td>';
				if($vehiculo['vehiculo_medallon'] == '1') { echo 'Con defroster'; } elseif($vehiculo['vehiculo_medallon'] == '2') { echo 'Sin defroster'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . PARABRISAS . '</td><td>';
				if($vehiculo['vehiculo_parabrisas'] == '1') { echo 'Sombreado'; } elseif($vehiculo['vehiculo_parabrisas'] == '2') { echo 'Claro'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . QUEMACOCOS . '</td><td>';
				if($vehiculo['vehiculo_quemacocos'] == '1') { echo 'Manual'; } elseif($vehiculo['vehiculo_quemacocos'] == '2') { echo 'Eléctrico'; } elseif($vehiculo['vehiculo_quemacocos'] == '3') { echo 'No'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . TIPO_FRENOS . '</td><td>';
				if($vehiculo['vehiculo_frenos'] == '1') { echo 'Normal'; } elseif($vehiculo['vehiculo_frenos'] == '2') { echo 'ABS'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . TIPO_SUSPENSION . '</td><td>';
				if($vehiculo['vehiculo_suspension'] == '1') { echo 'Normal'; } elseif($vehiculo['vehiculo_suspension'] == '2') { echo 'Deportiva'; } elseif($vehiculo['vehiculo_suspension'] == '3') { echo 'Electrónica'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . TIPO_RINES . '</td><td>';
				if($vehiculo['vehiculo_tipo_ring'] == '1') { echo 'Placacero'; } elseif($vehiculo['vehiculo_tipo_ring'] == '2') { echo 'Aluminio'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . RINES_ORIGINALES . '</td><td>';
				if($vehiculo['vehiculo_ring_original'] == '1') { echo 'No'; } elseif($vehiculo['vehiculo_ring_original'] == '2') { echo 'Sí'; }
				echo '</td></tr>'."\n";
				echo '		<tr><td>' . TIPO_DE_FAROS . '</td><td>';
				if($vehiculo['vehiculo_faros'] == '1') { echo 'Lisos'; } elseif($vehiculo['vehiculo_faros'] == '2') { echo 'Rayados'; } elseif($vehiculo['vehiculo_faros'] == '3') { echo 'Doble Parábola'; } elseif($vehiculo['vehiculo_faros'] == '4') { echo 'Con Lupa'; }
				echo '</td></tr>'."\n";
			}

			$retorno = validaAcceso('1145000', $dbpfx);
			if ($retorno == '1' || $_SESSION['rol06']==='1') {
				echo '		<tr><td colspan="2">
			<a href="vehiculos.php?accion=modificar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/cambiar-datos.png" alt="Modificar" title="Modificar"></a> 
			<a href="personas.php?accion=consultar&cliente_id=' . $vehiculo['vehiculo_cliente_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/datos-de-clientes.png" alt="Ver Datos del Cliente" title="Ver Datos del Cliente"></a> 
			<a href="previas.php?accion=consultar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/presupuesto-previo.png" alt="Ver o Crear Presupuesto Previo" title="Ver o Crear Presupuesto Previo"></a> 
			<a href="ordenes.php?accion=listar&placas=' . $vehiculo['vehiculo_placas'] . '"><img src="idiomas/' . $idioma . '/imagenes/ordenes-listar.png" alt="Ver Ordenes de Trabajo" title="Ver Ordenes de Trabajo"></a> '."\n";
				echo '			<a href="documentos.php?accion=listar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/documento.png" alt="Ver Documentos" title="Ver Documentos"></a>'."\n";
				echo '		</td></tr>'."\n";
			}
		}
		echo '	</table>';
		echo '	<a href="personas.php?accion=consultar&cliente_id=' . $cliente_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Datos del Cliente" title="Regresar a la Datos del Cliente"></a>'."\n";
	} elseif($num_cols > 1) {
		$vehiculo = mysql_fetch_array($matriz);
		redirigir('vehiculos.php?accion=listar&cliente_id=' . $vehiculo['vehiculo_cliente_id']);
	} else {
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		$mensaje .='No se encontraron registros con esos datos.</br>';
		echo '<p>' . $mensaje . '</p>';
	}
}

elseif ($accion==="listar") {
	
	$funnum = 1145015;
	$retorno = validaAcceso($funnum, $dbpfx);
//	echo 'Estamos en la sección listar';
	if ($retorno == '1' || $_SESSION['codigo'] < '60'  || $_SESSION['codigo'] > '70' ) {
		
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado.');
	}	
	$cliente_id=preparar_entrada_bd($cliente_id);
	$error = 'si'; $num_cols = 0;
	$mensaje = $lang['Un dato para buscar'] ;
	if ($placas!='' || $cliente_id!='' || $serie != '') {
		$error = 'no'; $mensaje ='';
		$pregunta = "SELECT * FROM " . $dbpfx . "vehiculos WHERE ";
		if ($placas) {$pregunta .= "vehiculo_placas LIKE '%$placas%' ";}
		if (($placas) && ($cliente_id)) {$pregunta .= "AND vehiculo_cliente_id = '$cliente_id' ";} 
		elseif ($cliente_id) {$pregunta .= "vehiculo_cliente_id = '$cliente_id' ";}
		if (($placas || $cliente_id) && $serie ) { $pregunta .= "AND vehiculo_serie LIKE '%$serie%' "; }
		elseif ($serie) { $pregunta .= "vehiculo_serie LIKE '%$serie%' ";} 
		$pregunta .= " AND vehiculo_status = '1'";
	} elseif($vehiculo_id != '') {
		$pregunta = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_id = '$vehiculo_id' ";
	}
//	echo $pregunta;
   $matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	$num_cols = mysql_num_rows($matriz);
	if ($num_cols > 1) {
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		echo '	<table cellspacing="2" cellpadding="2" border="1">
	<tr><td colspan="7" align="left">' . $lang['Datos del vehículo'] . ':</td></tr>
	<tr><td>' . $lang['Vehículo'] . '</td><td>' . $lang['Placas'] . '</td><td>' . $lang['Serie'] . '</td><td>' . $lang['Marca'] . '</td><td>' . $lang['Tipo'] . '</td><td>' . $lang['Año'] . '</td><td colspan="2">' . $lang['Acciones'] . '</td></tr>';
		while ($vehiculo = mysql_fetch_array($matriz)) {
			echo '		<tr>
				<td>' . $vehiculo['vehiculo_id'] . '</td>
				<td>' . $vehiculo['vehiculo_placas'] . '</td>
				<td>' . $vehiculo['vehiculo_serie'] . '</td>
				<td>' . $vehiculo['vehiculo_marca'] . '</td>
				<td>' . $vehiculo['vehiculo_tipo'] . '</td>
				<td>' . $vehiculo['vehiculo_modelo'] . '</td>
				<td>
					<table cellspacing="2" cellpadding="2" border="0">
						<tr>
							<td align="center"><a href="vehiculos.php?accion=consultar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/consultar.png" alt="Detalles" title="Detalles"></a></td>'."\n";
			if ($_SESSION['rol06']==='1') {
				echo '							<td align="center"><a href="ordenes.php?accion=listar&placas=' . $vehiculo['vehiculo_placas'] . '"><img src="idiomas/' . $idioma . '/imagenes/ordenes-listar.png" alt="Listar Ordenes de Trabajo" title="Listar Ordenes de Trabajo"></a></td>';
				echo '							<td align="center"><a href="documentos.php?accion=listar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/documento.png" alt="Ver Documentos" title="Ver Documentos"></a></td>'."\n";
			}
			echo '						</tr>
					</table>
				</td>
			</tr>';
			if($cliente_id == '') { $cliente_id = $vehiculo['vehiculo_cliente_id']; }
		}
		echo '	</table>';
		unset($_SESSION['vehiculos']);
	} elseif($num_cols == 1) {
		$vehiculo = mysql_fetch_array($matriz);
		redirigir('vehiculos.php?accion=consultar&vehiculo_id=' . $vehiculo['vehiculo_id']);
	} else {
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		$mensaje .= $lang['No encontró vehículo'] . '</br>';
		echo '<p>' . $mensaje . '</p>';
	}
	echo '	<a href="personas.php?accion=consultar&cliente_id=' . $cliente_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="' . $lang['Regresar al Cliente'] . '" title="' . $lang['Regresar al Cliente'] . '"></a>'."\n";
}

?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
