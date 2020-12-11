<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/ingreso.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}
		
/*  ----------------  obtener nombres de aseguradoras   ------------------- */
	
		$consulta = "SELECT aseguradora_razon_social, aseguradora_id, aseguradora_logo, aseguradora_nic, autosurtido FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		$asegu=array();
		while ($aseg = mysql_fetch_array($arreglo)) {
			$asegu[$aseg['aseguradora_id']]['logo'] = $aseg['aseguradora_logo'];
			$asegu[$aseg['aseguradora_id']]['nic'] = $aseg['aseguradora_nic'];
			$asegu[$aseg['aseguradora_id']]['auto'] = $aseg['autosurtido'];
			$agegu[$aseg['aseguradora_id']]['razon'] = $aseg['aseguradora_razon_social'];
// Identificando a AXA para carta de promesa de entrega
//			if($aseg['aseguradora_nic'] == 'AXA') {
//				$axa = $aseg['aseguradora_id'];
//			}
		}
/*  ----------------  nombres de aseguradoras   ------------------- */

if (($accion==='insertar') || ($accion==='avaluo') || ($accion==='mod_avaluo') || ($accion==='confcancelar') || ($accion==='cartaaxa') || ($accion==='env_correo') ) { 
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">'."\n";
}

if ($accion==="consultar") {
	
	$funnum = 1065000;
	
//	echo 'Estamos en la sección  consulta';
	$error = 'si'; $num_cols = 0; $total_pres = '';
	if ($orden_id!='') {
		$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
		$orden = mysql_fetch_array($matriz);
		$error = 'no';
		$preg1 = "SELECT * FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "clientes c WHERE v.vehiculo_id = '" . $orden['orden_vehiculo_id'] . "' AND c.cliente_id = '" . $orden['orden_cliente_id'] . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de vehículo!");
		$cust = mysql_fetch_array($matr1);
		$preg2 = "SELECT * FROM " . $dbpfx . "empresas WHERE empresa_id = '" . $cust['cliente_empresa_id'] . "'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cliente!");
		$emp = mysql_fetch_array($matr2);
		$preg3 = "SELECT nombre, apellidos, telefono_laboral, email FROM " . $dbpfx . "usuarios WHERE usuario = '" . $orden['orden_asesor_id'] . "'";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de cliente!");
		$usr = mysql_fetch_array($matr3);
		$preg4 = "SELECT * FROM " . $dbpfx . "inventario WHERE orden_id = '$orden_id'";
		$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de cliente!");
		$inv = mysql_fetch_array($matr4);
	}

	if ($num_cols>0 && $error ==='no') {
		include('parciales/phpqrcode/qrlib.php');
		include($hoja_ingreso); 
	} else {
		$_SESSION['msjerror'] = $lang['No hay registros con datos'].'</br>';
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
}

elseif ($accion==="cartaaxa") {
	
	$funnum = 1065005;
/*	
Este reporte sólo funciona para la aseguradora AXA, si se requiere para otra aseguradora se debe ajustar la sección Obtener nombres de aseguradoras y la pregunta para obtener el dato....
*/
	$error = 'si'; $num_cols = 0; $total_pres = '';
	if ($orden_id!='') {
		$pregunta = "SELECT o.orden_vehiculo_placas, o.orden_fecha_recepcion, o.orden_fecha_promesa_de_entrega, v.vehiculo_modelo, v.vehiculo_marca, v.vehiculo_tipo, c.cliente_nombre, c.cliente_apellidos FROM " . $dbpfx . "ordenes o, " . $dbpfx . "clientes c, " . $dbpfx . "vehiculos v WHERE o.orden_id = '$orden_id' AND o.orden_vehiculo_id = v.vehiculo_id AND o.orden_cliente_id = c.cliente_id";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
	}
	if ($num_cols>0 && $dato!='') {
		$dato = explode('|', $dato);

		if($dato[0] != '0') { 
			$reporte = $dato[0];
		} else { 
			$reporte = 'Particular'; 
		}  

		$poliza = $dato[1];
		$ord = mysql_fetch_array($matriz);
		if($ord['orden_fecha_promesa_de_entrega'] == '' || is_null($ord['orden_fecha_promesa_de_entrega']) || $ord['orden_fecha_promesa_de_entrega'] == '0000-00-00 00:00:00'){
			$_SESSION['msjerror'] = 'No has asignado una fecha promesa de entrega';
			redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
		}
		$orden_estatus = $ord['orden_estatus'];
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		include ('particular/carta-promesa-entrega-axa.php');
		echo '<table cellpadding="0" cellspacing="0" border="0" class="mediana" width="850">'."\n";
		echo '		<tr><td><div class="control">';
		echo '<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="Imprimir Carta" title="Imprimir Carta"></a>&nbsp;';
		echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la Orden de Trabajo'].'" title="'. $lang['Regresar a la Orden de Trabajo'].'"></a>';
                if(file_exists('particular/notifica-carta-promesa.php')) {
                        echo '&nbsp;<a href="ingreso.php?accion=env_correo&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/enviar_correo.png" alt="envíar por correo" title="envíar por correo"></a>';
                }
		echo '</div></td></tr>'."\n";
		echo '</table>'."\n";
/*		if(isset($fecha_promesa) && $fecha_promesa != '' ) {
			$prom = strtotime($fecha_promesa);
			$nprom = date('Y-m-d', $prom);
			echo $nprom;
			$sql = array('orden_fecha_promesa_de_entrega' => $nprom);
			$param = "orden_id = '" . $orden_id . "'";
			ejecutar_db($dbpfx . 'ordenes', $sql, 'actualizar', $param);
		}
*/	} else {
		if(!isset($dato) || $dato=='') {
//		$preg0 = "SELECT sub_reporte, sub_poliza FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '189' AND sub_aseguradora = '$axa' GROUP BY sub_reporte";
		$preg0 = "SELECT s.sub_reporte, s.sub_poliza FROM " . $dbpfx . "subordenes s, " . $dbpfx . "aseguradoras a WHERE s.orden_id = '$orden_id' AND s.sub_estatus < '189' AND a.aseguradora_nic LIKE '%AXA%' AND s.sub_aseguradora = a.aseguradora_id GROUP BY s.sub_reporte";
   	  	$mat0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
     		$num_rep = mysql_num_rows($mat0);
			if ($num_rep > 1) {
				include('parciales/encabezado.php'); 
				echo '	<div id="body">';
				include('parciales/menu_inicio.php');
				echo '		<div id="principal">';
				echo '	<form action="ingreso.php?accion=cartaaxa" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="850">'."\n";
   		  	echo '		<tr><td colspan="2" style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">'. $lang['Existe más de un siniestro o servicio por entregar'].'</td></tr>' . "\n";
     			echo '		<tr><td colspan="2"><select name="dato" size="1">' . "\n";
			echo '			<option value="" >'. $lang['Seleccione'].'</option>'."\n";
		     	while($rep = mysql_fetch_array($mat0)) {
   		  		echo '			<option value="' . $rep['sub_reporte'] . '|' . $rep['sub_poliza'] . '">' . $rep['sub_reporte'] . '</option>' . "\n";
			}
			echo '		</select></td></tr>' . "\n";
			echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2"><input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="hidden" name="poliza" value="' . $rep['sub_poliza'] . '" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="'. $lang['Enviar'].'" /></td></tr>
		</tr>
	</table>
	</form>'."\n";
			} elseif($num_rep == 1) {
				$rep = mysql_fetch_array($mat0);
				redirigir('ingreso.php?accion=cartaaxa&orden_id=' . $orden_id . '&dato=' . $rep['sub_reporte'] . '|' . $rep['sub_poliza']);
			} else {
				$_SESSION['orden']['mensaje'] =$lang['OT'] . $orden_id . $lang['no tiene tareas para AXA!'];
				redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
			}
		} else {
			$mensaje .=$lang['No hay registros con datos'];
			echo '<p>' . $mensaje . '</p>';
		}
	}
}

elseif($accion==="inventario") {
	
	$funnum = 1065010;
	
	echo '			
		<form action="ingreso.php?accion=insertar" id="ingreso" name="ingreso" method="post" enctype="multipart/form-data">'."\n";

	$preg0 = "SELECT * FROM " . $dbpfx . "inventario WHERE orden_id = '$orden_id'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de inventario!");
	$inv = mysql_fetch_array($matr0);
	$preg2 = "SELECT doc_id, doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND doc_archivo like '%-i-%'";
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de inventario!");
	while($doc = mysql_fetch_array($matr2)) {
		$imgnom = explode('-', $doc['doc_archivo']);
		$img[$imgnom[2]] = array('id' => $doc['doc_id'],
			'arch' => $doc['doc_archivo']);
	}
	
	echo '
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<div class="obscuro espacio">
				<h3>'. $lang['Generales de Ingreso'].'</h3>
				<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="380">'."\n";
				//  --------------- Captura de inventario de ingreso desde tablet  
	echo'									
					<tr>
						<td width=100%>'."\n";

	if($inv_detalle == '1') {
		echo '									
							<table cellpadding="0" cellspacing="0" border="0" width="100%">
								<tr>
									<td>
										<a name="int"></a>
										<strong>'. $lang['Interiores'].'</strong>
									</td>
									<td>'. $lang['Sí'].'</td>
									<td>'. $lang['No'].'</td>
									<td>'. $lang['C/D'].'</td>
								</tr>'."\n";

		for($i=0; $i < $invint; $i++) {
			echo '									
								<tr class="' . $fondo . ' inv">
									<td>' . constant('ETIQUETA_INV_INTERIORES_'.$i) . '</td>
									<td><input type="radio" name="int[' . $i . ']" value="1" '; 
									if($inv['inv_int'][$i]=='1') { echo 'checked="checked"';}
									elseif($llena_inv) { echo 'checked="checked"';}
									echo ' /></td>
									<td><input type="radio" name="int[' . $i . ']" value="2" '; if($inv['inv_int'][$i]=='2') { echo 'checked="checked"';} echo ' />
									<td><input type="radio" name="int[' . $i . ']" value="3" '; if($inv['inv_int'][$i]=='3') { echo 'checked="checked"';} echo ' /></td>
								</tr>'."\n";

			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
		echo '									
							</table>'."\n";
	}

	if($inv_detalle == '1' || $inv_gas == '1') {
		echo '									
							<table cellpadding="0" cellspacing="0" border="0" width="100%">
								<tr class="obscuro inv">
									<td colspan="9">' . ETIQUETA_INV_INTERIORES_28 . '</td>
								</tr>
								<tr class="claro inv">
									<td>'. $lang['R'].'</td>
									<td>'. $lang['1/8'].'</td>
									<td>'. $lang['1/4'].'</td>';

									echo '
									<td>'. $lang['3/8'].'</td>
									<td>'. $lang['1/2'].'</td>
									<td>'. $lang['5/8'].'</td>
									<td>'. $lang['3/4'].'</td>
									<td>'. $lang['7/8'].'</td>
									<td>'. $lang['LL'].'</td>
								</tr>
								<tr class="obscuro inv">
									<td><input type="radio" name="intgas" value="0" /></td>
									<td><input type="radio" name="intgas" value="1" '; if($inv['inv_int'][28]=='1') { echo 'checked="checked"';} echo ' /></td>
									<td><input type="radio" name="intgas" value="2" '; if($inv['inv_int'][28]=='2') { echo 'checked="checked"';} echo ' /></td>
									<td><input type="radio" name="intgas" value="3" '; if($inv['inv_int'][28]=='3') { echo 'checked="checked"';} echo ' /></td>
									';

									echo '		<td><input type="radio" name="intgas" value="4" '; if($inv['inv_int'][28]=='4') { echo 'checked="checked"';} echo ' /></td>
									<td><input type="radio" name="intgas" value="5" '; if($inv['inv_int'][28]=='5') { echo 'checked="checked"';} echo ' /></td>
									<td><input type="radio" name="intgas" value="6" '; if($inv['inv_int'][28]=='6') { echo 'checked="checked"';} echo ' /></td>
									<td><input type="radio" name="intgas" value="7" '; if($inv['inv_int'][28]=='7') { echo 'checked="checked"';} echo ' /></td>
									<td><input type="radio" name="intgas" value="8" '; if($inv['inv_int'][28]=='8') { echo 'checked="checked"';} echo ' /></td>
								</tr>
							</table>'."\n";
	}
	echo '									
							<table cellpadding="0" cellspacing="0" border="0" width="100%">
								<tr>
									<td colspan="4">'. $lang['Interiores'].'</td>
								</tr>'."\n";

	if($inv_detalle == '1') {
		echo '									
								<tr class="claro inv">
									<td colspan="2">' . ETIQUETA_INV_INTERIORES_29 . ': </td>
									<td colspan="2">'. $lang['Automática'].'<input type="radio" name="transmi" value="1" '; if($inv['inv_int'][29]=='1') { echo 'checked="checked"';} echo ' />&nbsp;'. $lang['Estándar'].'<input type="radio" name="transmi" value="2" '; if($inv['inv_int'][29]=='2') { echo 'checked="checked"';} echo ' /></td>
								</tr>'."\n";
	}
	$preg1 = "SELECT orden_odometro FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de inventario!");
	$odo = mysql_fetch_array($matr1);
	echo '									
								<tr class="claro inv">
									<td colspan="2">'. $lang['Kilometraje'].'</td>
									<td colspan="2"><input type="text" name="odometro" value="' . $odo['orden_odometro'] . '" size="7" /></td>
								</tr>'."\n";

	echo '									
								<tr class="obscuro inv">
									<td>'. $lang['Foto Indicadores de Tablero'].'</td>
									<td>'."\n";

	if($img[0]['arch'] != '') {
		echo '
										<a href="' . DIR_DOCS . $img[0]['arch'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $img[0]['arch'] . '" alt="" /></a><input type="hidden" name="doc_id[tablero]" value="' . $img[0]['id'] . '" />
									</td>';
	} else {
		echo '
										&nbsp;
									</td>';
	}
	echo '
									<td style="text-align:left;" colspan="2"><input type="file" name="tablero" size="3" /></td>
								</tr>
								<tr class="obscuro inv">
									<td colspan="4">'. $lang['Observaciones: '].'<textarea name="obs_int" cols="25" rows="3" />' . $inv['inv_int_obs'] . '</textarea></td>
								</tr>
								<tr>
									<td colspan="4"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="sint" value="'. $lang['Guardar'].'" /></td>
								</tr>
							</table>
							<table cellpadding="0" cellspacing="0" border="0" width="100%">
								<tr>
									<td colspan="4"><h3>'. $lang['Lateral Izquierdo'].'</h3></td>
								</tr>'."\n";

	//  --------------- Captura de inventario de ingreso desde tablet  
	if($inv_detalle == '1') {

		echo '									
								<tr>
									<td><a name="lizq"></a><strong>'. $lang['Lateral Izquierdo'].'</strong></td><td>'. $lang['Sí'].'</td>
									<td>'. $lang['No'].'</td>
									<td>'. $lang['C/D'].'</td>
								</tr>'."\n";

		for($i=0; $i < $invlatizq; $i++) {
			echo '									
								<tr class="' . $fondo . ' inv">
									<td>' . constant('ETIQUETA_INV_LATIZQ_'.$i) . '</td>
									<td><input type="radio" name="lizq[' . $i . ']" value="1" '; 
									if($inv['inv_latizq'][$i]=='1') { echo 'checked="checked"';}
									elseif($llena_inv) { echo 'checked="checked"';}
									echo ' /></td>
									<td><input type="radio" name="lizq[' . $i . ']" value="2" '; if($inv['inv_latizq'][$i]=='2') { echo 'checked="checked"';} echo ' /></td>
									<td><input type="radio" name="lizq[' . $i . ']" value="3" '; if($inv['inv_latizq'][$i]=='3') { echo 'checked="checked"';} echo ' /></td>
								</tr>'."\n";

			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
		echo '									
								<tr class="claro inv">
									<td colspan="4">' . ETIQUETA_INV_LATIZQ_12 . ': <input type="text" name="lizqlltras" value="' . $inv['inv_lizq_llt'] . '" size="12" />' . $inv['inv_lizq_llt'] . '</td>
								</tr>
								<tr class="obscuro inv">
									<td colspan="4">' . ETIQUETA_INV_LATIZQ_13 . ': <input type="text" name="lizqlldel" value="' . $inv['inv_lizq_lld'] . '" size="12" />' . $inv['inv_lizq_lld'] . '</td>
								</tr>'."\n";
	}
	echo '									
								<tr class="claro inv">
									<td colspan="4">
										'. $lang['Foto VIN'] ."\n";
	if($img[1]['arch'] != '') {
		echo '
										<a href="' . DIR_DOCS . $img[1]['arch'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $img[1]['arch'] . '" alt="" /></a><input type="hidden" name="doc_id[vin]" value="' . $img[1]['id'] . '" />'."\n";
	}
	echo '
										&nbsp;<input type="file" name="vin" size="3" />
									</td>
								</tr>'."\n";

	echo '									
								<tr class="obscuro inv">
										<td colspan="4">'. $lang['Foto Lateral Izquierda'];
	if($img[2]['arch'] != '') {
		echo '
										<a href="' . DIR_DOCS . $img[2]['arch'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $img[2]['arch'] . '" alt="" /></a><input type="hidden" name="doc_id[fcizq]" value="' . $img[2]['id'] . '" />';
	}
	echo '
										&nbsp; <input type="file" name="fcizq" size="3" />
									</td>
								</tr>'."\n";
	echo '									
								<tr class="claro inv">
									<td colspan="4">'. $lang['Observaciones'] . ' ' . $lang['Lateral Izquierdo'] . '<textarea name="obs_lizq" cols="25" rows="3" />' . $inv['inv_latizq_obs'] . '</textarea></td>
								</tr>'."\n";
	echo '
								<tr>
									<td colspan="4"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="slizq" value="'. $lang['Guardar'].'" /></td>
								</tr>';
	echo '									
							</table>'."\n";

	echo '									
							<table cellpadding="0" cellspacing="0" border="0" width="100%">'."\n";

	echo '									
								<tr>
									<td colspan="4"><h3>'. $lang['Frontal']. '</h3></td>
								</tr>'."\n";

	if($inv_detalle == '1') {
		echo '									
								<tr>
									<td><a name="frontal"></a><strong>'. $lang['Frontal'].'</strong></td><td>'. $lang['Sí'].'</td><td>'. $lang['No'].'</td><td>'. $lang['C/D'].'</td>
								</tr>'."\n";
		for($i=0; $i < $invfrontal; $i++) {
			echo '									
								<tr class="' . $fondo . ' inv">
									<td>' . constant('ETIQUETA_INV_FRONTAL_'.$i) . '</td>
									<td><input type="radio" name="frontal[' . $i . ']" value="1" '; 
									if($inv['inv_frontal'][$i]=='1') { echo 'checked="checked"';}
									elseif($llena_inv) { echo 'checked="checked"';}
									echo ' /></td>
									<td><input type="radio" name="frontal[' . $i . ']" value="2" '; if($inv['inv_frontal'][$i]=='2') { echo 'checked="checked"';} echo ' /></td>
									<td><input type="radio" name="frontal[' . $i . ']" value="3" '; if($inv['inv_frontal'][$i]=='3') { echo 'checked="checked"';} echo ' /></td>
								</tr>'."\n";
			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
	}
	echo '									
							<tr class="claro inv">
								<td colspan="4">'. $lang['Foto Frontal'];
	if($img[3]['arch'] != '') {
		echo '
									<a href="' . DIR_DOCS . $img[3]['arch'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $img[3]['arch'] . '" alt="" /></a><input type="hidden" name="doc_id[ffron]" value="' . $img[3]['id'] . '" />&nbsp;';
	}
	echo '
									<input type="file" name="ffron" size="3" />
								</td>
							</tr>'."\n";
	echo '									
							<tr class="obscuro inv">
								<td colspan="4">'. $lang['Foto Esquina Frontal Derecha'];
	if($img[4]['arch'] != '') {
		echo '
									<a href="' . DIR_DOCS . $img[4]['arch'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $img[4]['arch'] . '" alt="" /></a><input type="hidden" name="doc_id[fefder]" value="' . $img[4]['id'] . '" />&nbsp;';
	}
	echo '
									<input type="file" name="fefder" size="3" />
								</td>
							</tr>'."\n";

	echo '									
							<tr class="claro inv">
								<td colspan="4">'. $lang['Observaciones'].'<textarea name="obs_frontal" cols="25" rows="3" />' . $inv['inv_frontal_obs'] . '</textarea></td>
							</tr>'."\n";
	echo '
							<tr>
								<td colspan="4"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="sfrontal" value="'. $lang['Guardar'].'" /></td>
							</tr>';
	echo '									
						</table>'."\n";

	echo '									
						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<tr>
								<td colspan="4"><h3>'. $lang['Motor'].'</h3></td>
							</tr>'."\n";
	if($inv_detalle == '1') {
		echo '									
							<tr>
								<td><a name="motor"></a><strong>'. $lang['Motor'].'</strong></td>
								<td>'. $lang['Sí'].'</td>
								<td>'. $lang['No'].'</td><td>'. $lang['C/D'].'</td>
							</tr>'."\n";
		for($i=0; $i < $invmotor; $i++) {
			echo '									
							<tr class="' . $fondo . ' inv">
								<td>' . constant('ETIQUETA_INV_MOTOR_'.$i) . '</td>
								<td><input type="radio" name="motor[' . $i . ']" value="1" '; 
								if($inv['inv_motor'][$i]=='1') { echo 'checked="checked"';}
								elseif($llena_inv) { echo 'checked="checked"';} 
								echo ' /></td>
								<td><input type="radio" name="motor[' . $i . ']" value="2" '; if($inv['inv_motor'][$i]=='2') { echo 'checked="checked"';} echo ' /></td>
								<td><input type="radio" name="motor[' . $i . ']" value="3" '; if($inv['inv_motor'][$i]=='3') { echo 'checked="checked"';} echo ' /></td>
							</tr>'."\n";

			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
	}
	echo '									
							<tr class="claro inv">
								<td colspan="4">'. $lang['Foto Motor'];
	if($img[8]['arch'] != '') {
		echo '
									<a href="' . DIR_DOCS . $img[8]['arch'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $img[8]['arch'] . '" alt="" /></a><input type="hidden" name="doc_id[motor]" value="' . $img[8]['id'] . '" />&nbsp;';
	}
	echo '
									<input type="file" name="motor" size="3" />
								</td>
							</tr>'."\n";
	echo '									
							<tr class="claro inv">
								<td colspan="4">'. $lang['Observaciones'].'<textarea name="obs_motor" cols="25" rows="3" />' . $inv['inv_motor_obs'] . '</textarea></td>
							</tr>'."\n";
	echo '
							<tr>
								<td colspan="4"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="smotor" value="'. $lang['Guardar'].'" />
							</td>
						</tr>
					</table>'."\n";

	echo '									
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<tr>
							<td colspan="4"><h3>'. $lang['Lateral Derecho'].'</h3></td>
						</tr>'."\n";

	if($inv_detalle == '1') {
		echo '									
						<tr>
							<td><a name="lder"></a><strong>'. $lang['Lateral Derecho'].'</strong></td>
							<td>'. $lang['Sí'].'</td>
							<td>'. $lang['No'].'</td>
							<td>'. $lang['C/D'].'</td>
						</tr>'."\n";

		for($i=0; $i < $invlatder; $i++) {
			echo '									
						<tr class="' . $fondo . ' inv">
							<td>' . constant('ETIQUETA_INV_LATDER_'.$i) . '</td>
							<td><input type="radio" name="lder[' . $i . ']" value="1" '; 
							if($inv['inv_latder'][$i]=='1') { echo 'checked="checked"';} 
							elseif($llena_inv) { echo 'checked="checked"';}
							echo ' /></td>
							<td><input type="radio" name="lder[' . $i . ']" value="2" '; if($inv['inv_latder'][$i]=='2') { echo 'checked="checked"';} echo ' /></td>
							<td><input type="radio" name="lder[' . $i . ']" value="3" '; if($inv['inv_latder'][$i]=='3') { echo 'checked="checked"';} echo ' /></td>
						</tr>'."\n";

			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
		echo '									
						<tr class="claro inv">
							<td colspan="4">' . ETIQUETA_INV_LATDER_12 . ': <input type="text" name="lderlltras" value="' . $inv['inv_lder_llt'] . '" size="12" />' . $inv['inv_lder_llt'] . '</td>
						</tr>'."\n";
		echo '									
						<tr class="obscuro inv">
							<td colspan="4">' . ETIQUETA_INV_LATDER_13 . ': <input type="text" name="lderlldel" value="' . $inv['inv_lder_lld'] . '" size="12" />' . $inv['inv_lder_lld'] . '</td>
						</tr>'."\n";
	}
	echo '									
						<tr class="claro inv">
							<td colspan="4">'. $lang['Foto Lateral Derecha'];

	if($img[5]['arch'] != '') {
		echo '
								<a href="' . DIR_DOCS . $img[5]['arch'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $img[5]['arch'] . '" alt="" /></a><input type="hidden" name="doc_id[fcder]" value="' . $img[5]['id'] . '" />&nbsp;';
	}
	echo '
								<input type="file" name="fcder" size="3" />
							</td>
						</tr>
						<tr class="claro inv">
							<td colspan="4">'. $lang['Observaciones'].'<textarea name="obs_lder" cols="25" rows="3" />' . $inv['inv_latder_obs'] . '</textarea></td>
						</tr>
						<tr>
							<td colspan="4"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="' . $lang['Regresar a la OT'] . '" title="'. $lang['Regresar a la OT'] . '"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="slder" value="'. $lang['Guardar'].'" />
						</td>
					</tr>
				</table>
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td colspan="4"><h3>'. $lang['Posterior'].'</h3></td>
					</tr>'."\n";

	if($inv_detalle == '1') {
		echo '									
					<tr>
						<td><a name="post"></a><strong>'. $lang['Posterior'].'</strong></td>
						<td>'. $lang['Sí'].'</td><td>'. $lang['No'].'</td>
						<td>'. $lang['C/D'].'</td>
					</tr>'."\n";

		for($i=0; $i < $invpost; $i++) {
			echo '									
					<tr class="' . $fondo . ' inv">
						<td>' . constant('ETIQUETA_INV_POST_'.$i) . '</td>
						<td><input type="radio" name="post[' . $i . ']" value="1" '; 
						if($inv['inv_post'][$i]=='1') { echo 'checked="checked"';} 
						elseif($llena_inv) { echo 'checked="checked"';}
						echo ' /></td>
						<td><input type="radio" name="post[' . $i . ']" value="2" '; if($inv['inv_post'][$i]=='2') { echo 'checked="checked"';} echo ' /></td>
						<td><input type="radio" name="post[' . $i . ']" value="3" '; if($inv['inv_post'][$i]=='3') { echo 'checked="checked"';} echo ' /></td>
					</tr>'."\n";

			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
	}
	echo '									
					<tr class="obscuro inv">
						<td colspan="4">'. $lang['Foto Posterior'];

	if($img[6]['arch'] != '') {
		echo '
							<a href="' . DIR_DOCS . $img[6]['arch'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $img[6]['arch'] . '" alt="" /></a><input type="hidden" name="doc_id[fpos]" value="' . $img[6]['id'] . '" />&nbsp;';
	}
	echo '
							<input type="file" name="fpos" size="3" />
						</td>
					</tr>
					<tr class="claro inv">
						<td colspan="4">'. $lang['Foto Esquina Posterior Izquierda'];

	if($img[7]['arch'] != '') {
		echo '
							<a href="' . DIR_DOCS . $img[7]['arch'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $img[7]['arch'] . '" alt="" /></a><input type="hidden" name="doc_id[fepizq]" value="' . $img[7]['id'] . '" />&nbsp;';
	}
	echo '	
							<input type="file" name="fepizq" size="3" />
						</td>
					</tr>
					<tr class="claro inv">
						<td colspan="4">'. $lang['Observaciones'].'<textarea name="obs_post" cols="25" rows="3" />' . $inv['inv_post_obs'] . '</textarea></td>
					</tr>
					<tr>
						<td colspan="4"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="spost" value="'. $lang['Guardar'].'" /></td>
					</tr>
				</table>
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td colspan="4"><h3>'. $lang['Cajuela'].'</h3></td>
					</tr>'."\n";

	if($inv_detalle == '1') {
		echo '									
					<tr>
						<td><a name="caj"></a><strong>'. $lang['Cajuela'].'</strong></td>
						<td>'. $lang['Sí'].'</td><td>'. $lang['No'].'</td>
						<td>C/D</td>
					</tr>'."\n";

		for($i=0; $i < $invcajuela; $i++) {
			echo '
					<tr class="' . $fondo . ' inv">
						<td>' . constant('ETIQUETA_INV_CAJ_'.$i) . '</td>
						<td><input type="radio" name="caj[' . $i . ']" value="1" '; 
						if($inv['inv_cajuela'][$i]=='1') { echo 'checked="checked"';}
						elseif($llena_inv) { echo 'checked="checked"';}
						echo ' /></td>
						<td><input type="radio" name="caj[' . $i . ']" value="2" '; if($inv['inv_cajuela'][$i]=='2') { echo 'checked="checked"';} echo ' /></td>
						<td><input type="radio" name="caj[' . $i . ']" value="3" '; if($inv['inv_cajuela'][$i]=='3') { echo 'checked="checked"';} echo ' /></td>
					</tr>'."\n";

			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
	}
	echo '									
					<tr class="claro inv">
						<td colspan="4">'. $lang['Observaciones'].'<textarea name="obs_cajuela" cols="25" rows="3" />' . $inv['inv_cajuela_obs'] . '</textarea></td>
					</tr>
					<tr>
						<td colspan="4"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="scaj" value="'. $lang['Guardar'].'" /></td>
					</tr>
				</table>
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td colspan="4"><h3>'. $lang['Otros'].'</h3></td>
					</tr>'."\n";

	if($inv_detalle == '1') {
		echo '									
					<tr>
						<td><a name="otros"></a><strong>'. $lang['Otros'].'</strong></td>
						<td>'. $lang['Sí'].'</td>
						<td>'. $lang['No'].'</td>
						<td>'. $lang['C/D'].'</td>
					</tr>'."\n";

		for($i=0; $i < $invotros; $i++) {
			echo '									
					<tr class="' . $fondo . ' inv">
						<td>' . constant('ETIQUETA_INV_OTROS_'.$i) . '</td>
						<td><input type="radio" name="otros[' . $i . ']" value="1" '; 
						if($inv['inv_otros'][$i]=='1') { echo 'checked="checked"';}
						elseif($llena_inv) { echo 'checked="checked"';}
						echo ' /></td>
						<td><input type="radio" name="otros[' . $i . ']" value="2" '; if($inv['inv_otros'][$i]=='2') { echo 'checked="checked"';} echo ' /></td>
						<td><input type="radio" name="otros[' . $i . ']" value="3" '; if($inv['inv_otros'][$i]=='3') { echo 'checked="checked"';} echo ' /></td>
					</tr>'."\n";
			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
	}
	echo '									
					<tr class="obscuro inv">
						<td colspan="4">'. $lang['Otras observaciones'].'<textarea name="obs_otros" cols="35" rows="4" />' . $inv['inv_otros_obs'] . '</textarea></td>
					</tr>
					<tr>
						<td colspan="4"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="'. $lang['Regresar a la OT'].'"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="sotros" value="'. $lang['Guardar'].'" /></td>
					</tr>
				</table>
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td colspan="4"><h3>'. $lang['Toldo'].'</h3></td>
					</tr>'."\n";

	if($inv_detalle == '1') {
		echo '									
					<tr>
						<td><a name="toldo"></a><strong>'. $lang['Toldo'].'</strong></td>
						<td>'. $lang['Sí'].'</td>
						<td>'. $lang['No'].'</td>
						<td>'. $lang['C/D'].'</td>
					</tr>'."\n";

		for($i=0; $i < $invtoldo; $i++) {
			echo '									
					<tr class="' . $fondo . ' inv">
						<td>' . constant('ETIQUETA_INV_TOLDO_'.$i) . '</td>
						<td><input type="radio" name="toldo[' . $i . ']" value="1" '; 
						if($inv['inv_toldo'][$i]=='1') { echo 'checked="checked"';} 
						elseif($llena_inv) { echo 'checked="checked"';}
						echo ' /></td>
						<td><input type="radio" name="toldo[' . $i . ']" value="2" '; if($inv['inv_toldo'][$i]=='2') { echo 'checked="checked"';} echo ' /></td>
						<td><input type="radio" name="toldo[' . $i . ']" value="3" '; if($inv['inv_toldo'][$i]=='3') { echo 'checked="checked"';} echo ' /></td>
					</tr>'."\n";

			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
	}
		echo '									
					<tr class="claro inv">
						<td colspan="4">'. $lang['Observaciones'].'<textarea name="obs_toldo" cols="25" rows="3" />' . $inv['inv_toldo_obs'] . '</textarea></td>
					</tr>
					<tr>
						<td colspan="4"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="stoldo" value="'. $lang['Guardar'].'" /></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>	
	</div>
	</form>';
}

elseif($accion==="insertar") {

	$funnum = 1065015;

	for($i=0; $i < $invint; $i++) {
		if(!isset($int[$i])) {$int[$i] = 0;}
		$wint .= $int[$i];
	}
//	echo $wint;
	$wint .= $intgas;
	$wint .= $transmi;
	for($i=0; $i < $invlatizq; $i++) {
		if(!isset($lizq[$i])) {$lizq[$i] = 0;}
		$wlizq .= $lizq[$i];
	}
	for($i=0; $i < $invfrontal; $i++) {
		if(!isset($frontal[$i])) {$frontal[$i] = 0;}
		$wfrontal .= $frontal[$i];
	}
	for($i=0; $i < $invmotor; $i++) {
		if(!isset($motor[$i])) {$motor[$i] = 0;}
		$wmotor .= $motor[$i];
	}
	for($i=0; $i < $invlatder; $i++) {
		if(!isset($lder[$i])) {$lder[$i] = 0;}
		$wlder .= $lder[$i];
	}
	for($i=0; $i < $invpost; $i++) {
		if(!isset($post[$i])) {$post[$i] = 0;}
		$wpost .= $post[$i];
	}
	for($i=0; $i < $invcajuela; $i++) {
		if(!isset($caj[$i])) {$caj[$i] = 0;}
		$wcaj .= $caj[$i];
	}
	for($i=0; $i < $invotros; $i++) {
		if(!isset($otros[$i])) {$otros[$i] = 0;}
		$wotros .= $otros[$i];
	}
	for($i=0; $i < $invtoldo; $i++) {
		if(!isset($toldo[$i])) {$toldo[$i] = 0;}
		$wtoldo .= $toldo[$i];
	}
	
	$sql_array = array('orden_id' => $orden_id,
		'inv_int' => $wint,
		'inv_int_obs' => $obs_int,
		'inv_latizq' => $wlizq,
		'inv_latizq_obs' => $obs_lizq,
		'inv_frontal' => $wfrontal,
		'inv_frontal_obs' => $obs_frontal,
		'inv_motor' => $wmotor,
		'inv_motor_obs' => $obs_motor,
		'inv_latder' => $wlder,
		'inv_latder_obs' => $obs_lder,
		'inv_post' => $wpost,
		'inv_post_obs' => $obs_post,
		'inv_cajuela' => $wcaj,
		'inv_cajuela_obs' => $obs_cajuela,
		'inv_otros' => $wotros,
		'inv_otros_obs' => $obs_otros,
		'inv_toldo' => $wtoldo,
		'inv_toldo_obs' => $obs_toldo,
		'inv_lizq_lld' => $lizqlldel,		
		'inv_lizq_llt' => $lizqlltras,		
		'inv_lder_lld' => $lderlldel,		
		'inv_lder_llt' => $lderlltras);
		
	$preg0 = "SELECT orden_id FROM " . $dbpfx . "inventario WHERE orden_id = '$orden_id'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de inventario!");
	$filas = mysql_num_rows($matr0);

	if($filas > 0) {
		$parametros = "orden_id ='" . $orden_id . "'";
		$hacer = 'actualizar'; 
	} else {
		$hacer = 'insertar'; 
		$parametros = '';
	}
	ejecutar_db($dbpfx . 'inventario', $sql_array, $hacer, $parametros);
	unset($sql_array);
	unset($_SESSION['inv']);
	$_SESSION['inv']['odometro'] = $odometro;
	$sql_ord = array('orden_odometro' => $odometro);
	$parametros = "orden_id ='$orden_id'";
	ejecutar_db($dbpfx . 'ordenes', $sql_ord, 'actualizar', $parametros);
	unset($sql_ord);
	
	// ---- evaluar si es jpg o jpge ---
	$cont = 0;
	foreach($_FILES as $key => $val){
		//echo 'key: ' . $key . '<br>' . 'val: ' . $val . '<br>';
		
		if(strtolower($val['type']) == 'image/jpeg' || strtolower($val['type']) == 'image/JPGE') { // --- se procesa la imagen ---
			//echo 'CUMPLE CON EL FORMATO<br>';
			$file[$cont] = [
				'imagen' => $val['tmp_name'],
				'procesa' => 'si',
			];
			
			if($key == 'tablero'){ $file[$cont]['nombres'] = 'Tablero';  $file[$cont]['orden'] = '0'; $file[$cont]['etiqueta'] = 'tablero';}
			elseif($key == 'vin'){ $file[$cont]['nombres'] = 'VIN'; $file[$cont]['orden'] = '1'; $file[$cont]['etiqueta'] = 'vin';}
			elseif($key == 'fcizq'){ $file[$cont]['nombres'] = 'Costado izquierdo'; $file[$cont]['orden'] = '2'; $file[$cont]['etiqueta'] = 'fcizq';}
			elseif($key == 'ffron'){ $file[$cont]['nombres'] = 'Frontal'; $file[$cont]['orden'] = '3'; $file[$cont]['etiqueta'] = 'ffron';}
			elseif($key == 'fefder'){ $file[$cont]['nombres'] = 'Esquina frontal derecha'; $file[$cont]['orden'] = '4'; $file[$cont]['etiqueta'] = 'fefder';}
			elseif($key == 'motor'){ $file[$cont]['nombres'] = 'Motor'; $file[$cont]['orden'] = '8'; $file[$cont]['etiqueta'] = 'motor';}
			elseif($key == 'fcder'){ $file[$cont]['nombres'] = 'Costado derecho'; $file[$cont]['orden'] = '5'; $file[$cont]['etiqueta'] = 'fcder';}
			elseif($key == 'fpos'){ $file[$cont]['nombres'] = 'Posterior'; $file[$cont]['orden'] = '6'; $file[$cont]['etiqueta'] = 'fpos';}
			elseif($key == 'fepizq'){ $file[$cont]['nombres'] = 'Esquina posterior izquierda'; $file[$cont]['orden'] = '7'; $file[$cont]['etiqueta'] = 'fepizq';}
			
		} else {
			//echo 'NO CUMPLE CON EL FORMATO<br>';
			if($val['tmp_name'] != ''){
				$file[$cont]['procesa'] = 'no';
				if($key == 'tablero'){ $file[$cont]['nombres'] = 'Tablero';}
				elseif($key == 'vin'){ $file[$cont]['nombres'] = 'VIN';}
				elseif($key == 'fcizq'){ $file[$cont]['nombres'] = 'Costado izquierdo';}
				elseif($key == 'ffron'){ $file[$cont]['nombres'] = 'Frontal';}
				elseif($key == 'fefder'){ $file[$cont]['nombres'] = 'Esquina frontal derecha';}
				elseif($key == 'motor'){ $file[$cont]['nombres'] = 'Motor';}
				elseif($key == 'fcder'){ $file[$cont]['nombres'] = 'Costado derecho';}
				elseif($key == 'fpos'){ $file[$cont]['nombres'] = 'Posterior';}
				elseif($key == 'fepizq'){ $file[$cont]['nombres'] = 'Esquina posterior izquierda';}
			}
		}
		
		$cont++;
	}
	
	$mensaje_imagen = '';
	
	for($i=0;$i<9;$i++) {
		
		if($file[$i]['procesa'] == 'si'){
			//echo 'se procesa archivo<br>';
			//echo 'temporal ' . $file[$i]['imagen'] . '<br>';
			$nombre_archivo = $orden_id . '-i-' . $file[$i]['orden'] . '-' . time() . '.jpg';

			if(move_uploaded_file($file[$i]['imagen'], DIR_DOCS . $nombre_archivo)) {
				if($doc_id[$file[$i]['etiqueta']] != ''){
					$parametros=" doc_id = '" . $doc_id[$file[$i]['etiqueta']] ."'" ;
					$hacer = 'actualizar';
				} else {
					$parametros = '';
					$hacer = 'insertar';
				}
				$sql_data_array = [
					'orden_id' => $orden_id,
					'doc_nombre' => $file[$i]['nombres'],
					'doc_archivo' => $nombre_archivo
				];
				ejecutar_db($dbpfx . 'documentos', $sql_data_array, $hacer, $parametros);
				// --- Copia el archivo al servidor de respaldo ---
				if($servrespaldo != '') {
					$conecta = ssh2_connect($servrespaldo, 2922);
					ssh2_scp_send($conecta, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, 0644);
				}
				creaMinis($nombre_archivo);
				bitacora($orden_id, 'Documentos Ingreso Agregados', $dbpfx);
			}
		unset($sql_data_array);
		} elseif($file[$i]['procesa'] == 'no'){
			$mensaje_imagen .= 'No se subio el archivo foto ' . $file[$i]['nombres'] . ' no tiene un formato de imagen valido.<br>';
		}
	}

	if($sint == 'Guardar') {$etiq = 'int';}
	elseif($slizq == 'Guardar') {$etiq = 'lizq';}
	elseif($sfrontal == 'Guardar') {$etiq = 'frontal';}
	elseif($smotor == 'Guardar') {$etiq = 'motor';}
	elseif($slder == 'Guardar') {$etiq = 'lder';}
	elseif($spost == 'Guardar') {$etiq = 'post';}
	elseif($scaj == 'Guardar') {$etiq = 'caj';}
	elseif($sotros == 'Guardar') {$etiq = 'otros';}
	elseif($stoldo == 'Guardar') {$etiq = 'toldo';}

	$_SESSION['msjerror'] = $mensaje_imagen;
	redirigir('ingreso.php?accion=inventario&orden_id=' . $orden_id . '#' . $etiq);
}

elseif($accion==="caratula") {
	include ('particular/caratula.php');
}

elseif($accion==="env_correo") {

	if(file_exists('particular/notifica-carta-promesa.php')) {
		include('particular/notifica-carta-promesa.php');
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id . '&mensaje=se envío correo al cliente');
	} else {
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	}
}


?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
