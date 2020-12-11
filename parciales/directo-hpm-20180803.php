<?php

if ($accion==='presupuestar' || $accion==='valuar') {
	
  	if($accion==='presupuestar') {
		$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  		$sub_orden = mysql_fetch_array($matriz);
  		$oppres = 1;
  	}

//	echo 'Estamos en la sección valuar';

	echo '		<form action="presupuestos.php?accion=';
	if($oppres == 1) { echo 'presupuesto'; } else { echo 'avaluo'; }
	echo '" method="post" enctype="multipart/form-data">'."\n";
	echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">
			<tr><td colspan="2"><span class="alerta">' . $_SESSION['pres']['mensaje'] . '</span></td></tr>'."\n";
	$veh = datosVehiculo($sub_orden['orden_id'], $dbpfx);
	echo '			<tr><td colspan="2" style="text-align:left; font-size:1.5em; font-weight:bold;">' . $veh['completo'] . '	</td></tr>'."\n";
	unset($_SESSION['pres']['mensaje']);
	echo '			<tr class="cabeza_tabla"><td colspan="2" style="text-align:left;">Refacciones, Productos, Materiales y Mano de Obra a presupuestar para la Reparación:</td></tr>'."\n";
	echo '			<tr><td colspan="2" style="text-align:left;"><img src="' . constant('ASEGURADORA_' . $sub_orden['sub_aseguradora']) . '" alt="">';
	if($sub_orden['sub_reporte'] == '0' || $sub_orden['sub_reporte'] == '') {
		$reporte = 'Particular';
	} else {
		$reporte = $sub_orden['sub_reporte'];
	}
	echo ' Reporte: ' . $reporte;
	echo '</td></tr>'."\n";
	echo '			<tr><td colspan="2" style="text-align:left;"><br>Area: <span style="font-size:16px; font-weight:bold;">' . constant('NOMBRE_AREA_' . $sub_orden['sub_area']) . '</span>.<br>Descripción de tarea: <span style="font-size:16px; font-weight:bold;">' . $sub_orden['sub_descripcion'] . '</span><br><br></td></tr>'."\n";
	echo '			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">
					<tr class="cabeza_tabla"><td colspan="6">Modificar o Eliminar Refacciones y Mano de Obra ya agregada</td></t></tr>
					<tr><td>Tipo</td><td>Area</td><td>Cantidad</td><td>Nombre</td><td>Precio<br>Unitario</td><td>Borrar?</td></tr>'."\n";
	$pregsub = "SELECT sub_orden_id, sub_area, fact_id, recibo_id, sub_descuento FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $sub_orden['sub_reporte'] . "' AND orden_id = '" . $sub_orden['orden_id'] . "' AND (sub_orden_id = '" . $sub_orden_id . "' OR sub_area = '";
	if($sub_orden['sub_area'] != '7') { $pregsub .= "7"; } else { $pregsub .= "6"; } 
	$pregsub .= "') AND sub_estatus < 130";
//	echo $pregsub;
	$matrsub = mysql_query($pregsub) or die("ERROR: Falló selección de tareas del reporte! " . $pregsub);
	$cuenta = 0;
	while($subrep = mysql_fetch_array($matrsub)) {
// ------ Localiza todos los conceptos relacionados al reporte ------
		$preg0 = "SELECT op_id, op_nombre, op_cantidad, op_precio, op_tangible, prod_id, op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $subrep['sub_orden_id'] . "' AND ";
//		$preg0 = "SELECT op_id, op_nombre, op_cantidad, op_precio, op_tangible, prod_id, op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "' AND ";
		if($oppres == 1) {
			$preg0 .= " op_pres = '1' ";
		} else {
			$preg0 .= " op_pres IS NULL ";
		}
		$preg0 .= " AND op_tangible < '3' ORDER BY op_tangible,op_item";
//		echo $preg0 . '<br>';
		$matr0 = mysql_query($preg0) or die("ERROR: Falló selección de conceptos requeridos! " . $preg0);
		while($op = mysql_fetch_array($matr0)) {
			if($op['op_tangible'] == '1') { $tipo = 'Refacción';}
			elseif($op['op_tangible'] == '2') { $tipo = 'Consumible';}
			else {$tipo = 'MO';}
			echo '					<tr><td style="text-align:center;">' . $tipo . '</td>'."\n";
			echo '						<td style="text-align:center;">' . constant('NOMBRE_AREA_' . $subrep['sub_area']) . '</td>'."\n";
			echo '						<td style="text-align:center;">';
			if(($op['op_tangible'] > '0' && ($op['op_pedido'] > 0 || $subrep['fact_id'] > 0)) || ($op['op_tangible'] == '0' && ($subrep['recibo_id'] > 0 || $subrep['fact_id'] > 0))) {
				echo $op['op_cantidad'];
			} else {
				echo '<input style="text-align:right;" type="text" name="cantp[' . $cuenta . ']" value="' . $op['op_cantidad'] . '" size="2" />';
			}
			echo '</td>'."\n";
			echo '						<td>' . $op['op_nombre'] . '</td>'."\n";
			echo '						<td style="text-align:right;">';
			if(($op['op_tangible'] > '0' && $subrep['fact_id'] > 0) || ($op['op_tangible'] == '0' && ($subrep['recibo_id'] > 0 || $subrep['fact_id'] > 0))) {
				echo number_format($op['op_precio'],2);
			} else {
				echo '<input style="text-align:right;" type="text" name="preunit[' . $cuenta . ']" value="' . number_format($op['op_precio'],2) . '" size="8" /><input type="hidden" name="pedido[' . $cuenta . ']" value="' . $op['pedido_id'] . '" />';
			}
			echo '</td>'."\n";
			echo '						<td>';
			if(($op['op_tangible'] > '0' && ($op['op_pedido'] > 0 || $subrep['fact_id'] > 0)) || ($op['op_tangible'] == '0' && ($subrep['recibo_id'] > 0 || $subrep['fact_id'] > 0))) {
				if($subrep['fact_id'] > 0) {
					echo $lang['Factura'] . ' ' . $subrep['fact_id'];
				} elseif($op['op_tangible'] > '0' && $op['op_pedido'] > 0) {
					echo $lang['Pedido'] . ' ' . $op['op_pedido'];
				} elseif($op['op_tangible'] == '0' && $subrep['recibo_id'] > 0) {
					echo $lang['Recibo'] . ' ' . $subrep['recibo_id'];
				}
			} else {
				echo '<input type="checkbox" name="borrar[' . $cuenta . ']" value="1" /><input type="hidden" name="op_id[' . $cuenta . ']" value="' . $op['op_id'] . '" />';
			}
			echo '</td></tr>'."\n";
			$cuenta++;
		}
	}
	echo '						<tr class="cabeza_tabla"><td colspan="6">&nbsp;</td></tr>'."\n";
	echo '						<tr><td colspan="6" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>'."\n";
	echo '					</table>'."\n";
	echo '				</td>
			</tr>'."\n";
	$preg1 = "SELECT paq_id, paq_nombre FROM " . $dbpfx . "paquetes WHERE paq_area ='" . $sub_orden['sub_area'] . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");
	echo '	 		<tr><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '			<tr class="cabeza_tabla"><td colspan="2" style="text-align:left;">Seleccionar un Paquete de Servicio</td></tr>
			<tr><td colspan="2" style="text-align:left;">
				<select name="paquete" size="1">
					<option value="">Seleccione...</option>'."\n";
	while($paqs = mysql_fetch_array($matr1)) {
		echo '					<option value="' . $paqs['paq_id'] . '">' . $paqs['paq_nombre'] . '</option>'."\n";
	}
	echo '				</select>
		</td></tr>'."\n";
	echo '	 		<tr><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '		</table>'."\n";

// ------ Nuevo método de captura, todo en uno ------

// ------ Número de renglones para captura de conceptos; es variable ya que pueden ser precargados en 
// ------ valuación particular desde lo presupuestado y por lo tanto ser más de el default de 10

// ------ Si el reporte es Particular y la acción corresponde a Autorizados y es la primera vez que se muestra la pantalla,
// ------ localizar presupuestados y presentarlos precargados en SESSION['pres']
/*	if(!isset($_SESSION['pres']) && $reporte == 'Particular' && $accion==='valuar') {
		$pregsub = "SELECT sub_orden_id, sub_area FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $sub_orden['sub_reporte'] . "' AND orden_id = '" . $sub_orden['orden_id'] . "' AND (sub_orden_id = '" . $sub_orden_id . "' OR sub_area = '";
		if($sub_orden['sub_area'] != '7') { $pregsub .= "7"; } else { $pregsub .= "6"; } 
		$pregsub .= "') AND sub_estatus < 130";
		$matrsub = mysql_query($pregsub) or die("ERROR: Falló selección de tareas del reporte! " . $pregsub);
		$cuenta = 0;
		while($subrep = mysql_fetch_array($matrsub)) {
// ------ Localiza todos los conceptos relacionados al reporte ------
			$preg0 = "SELECT op_id, op_nombre, op_cantidad, op_precio, op_tangible, prod_id, op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $subrep['sub_orden_id'] . "' AND op_pres IS NULL AND op_tangible < '3' ORDER BY op_nombre";
//		echo $preg0 . '<br>';
			$matr0 = mysql_query($preg0) or die("ERROR: Falló selección de conceptos requeridos! " . $preg0);
			while($op = mysql_fetch_array($matr0)) {
// ------ Recrear la matriz SESSION['pres']!!!!
			}
		} 
	} */

// ------ Encabezado de tabla de captura de conceptos ------
	if($area == 6 || $area ==7) { $carea = $area; } else { $carea = 'otra'; }
	echo '		<table cellpadding="2" cellspacing="0" border="1" class="agrega" width="840">'."\n";
	echo '			<tr class="cabeza_tabla"><td colspan="7" style="text-align:left;">' . $lang['EncabezadoDirectoHPM'] . '</td></tr>'."\n";
	echo '			<tr><td style="text-align:left;">' . $lang['Concepto'] . '</td>
				<td style="text-align:center;">' . $lang['Cantidad'] . '</td>
				<td style="text-align:center;">' . $lang['MO Reparar'] . ' ';
	if($area ==7) { $areafic = 6;} else { $areafic = $area; }
	if($areaut[($areafic-1)] > 0 && $accion==='valuar') {
		echo $lang['EnUTs'];
		echo '<input style="text-align:right;" type="text" name="preut[' . $areafic . ']" value="';
		if($_SESSION['pres']['preut'][$areafic] > '0') { echo number_format($_SESSION['pres']['preut'][$areafic],2); }
		else { echo number_format($areaut[($areafic-1)], 2); }
	} else {
		echo $lang['EnPesos'];
		echo '<input style="text-align:right;" type="text" name="preut[' . $areafic . ']" value="';
		if($_SESSION['pres']['preut'][$areafic] > '0') { echo number_format($_SESSION['pres']['preut'][$areafic],2); }
		else { echo number_format($ut[$sub_orden['sub_aseguradora']], 2); }		
	}
	echo '" size="4" />';
	echo '</td>'."\n";
	if($area != 7) {
		echo '				<td style="text-align:center;" class="area' . $carea . '">' . $lang['MO Cambiar'] . ' ';
/*		if($areaut[($area-1)] > 0 && $accion==='valuar') {
			echo $lang['EnUTs'];
			echo '<input style="text-align:right;" type="text" name="preut[' . $area . ']" value="';
			if($_SESSION['pres']['preut'][$area] != '') { echo number_format($_SESSION['pres']['preut'][$area],2); }
			else { echo number_format($areaut[($area-1)], 2); }
		} else {
			echo $lang['EnPesos'];
			echo '<input style="text-align:right;" type="text" name="preut[' . $area . ']" value="';
			if($_SESSION['pres']['preut'][$area] != '') { echo number_format($_SESSION['pres']['preut'][$area],2); }
			else { echo number_format($preciout, 2); }
		}
		echo '" size="4" />';
*/		echo '</td>'."\n";
		echo '				<td style="text-align:center;" class="area' . $carea . '">' . constant('NOMBRE_AREA_'.$area) . ' Precio Unitario de Venta</td>'."\n";
	}
	echo '				<td style="text-align:center;">';
	if($areaut[(7-1)] > 0 && $accion==='valuar' && $sub_orden['sub_aseguradora'] > 0) {
		echo $lang['MO Pintar'] . ' ' . $lang['EnUTs'];
		echo '<input style="text-align:right;" type="text" name="preut[7]" value="';
		if($_SESSION['pres']['preut'][7] > '0') { echo number_format($_SESSION['pres']['preut'][7],2); }
		else { echo number_format($areaut[(7-1)], 2); }
	} elseif($accion==='valuar' && $sub_orden['sub_aseguradora'] > 0 && $valor['ValComoPartic'][0] != '1') {
		echo $lang['MO Pintar'] . ' ' . $lang['EnPesos'];
		echo '<input style="text-align:right;" type="text" name="preut[7]" value="';
		if($_SESSION['pres']['preut'][7] > '0') { echo number_format($_SESSION['pres']['preut'][7],2); }
		else { echo number_format($ut[$sub_orden['sub_aseguradora']], 2); }
	} else {
		echo $lang['Precio Pintar'] . ' ' . $lang['PorcenMatPint'];
		echo '<input type="hidden" name="preut[7]" value="' . $preciout .'" />';
		echo '<input style="text-align:right;" type="text" name="prepint" value="';
		if($_SESSION['pres']['prepint'] != '') { echo number_format($_SESSION['pres']['prepint'],2); }
		else { echo '25'; }
	}
	echo '" size="4" />';
	echo '%</td></tr>'."\n";
// ------ Fin de encabezado de tabla de captura de conceptos ------

// ------ Creación de renglones para captura de conceptos ------

	$renglones = 10;
	if($valor['AlmacenValuacion'][0] > 0) {
		$pregval = "SELECT prod_nombre FROM " . $dbpfx . "productos WHERE prod_almacen = '" . $valor['AlmacenValuacion'][0] . "' AND prod_activo = '1' ORDER BY prod_nombre LIMIT 50";
		$matrval = mysql_query($pregval) or die("ERROR: Falló selección de productos de valuación! " . $pregval);
		while($prods = mysql_fetch_assoc($matrval)) {
			$prod[] = $prods['prod_nombre'];
		}
	}

	for($i = 1; $i <= $renglones; $i++) {
		echo '			<tr><td>';
		if($valor['AlmacenValuacion'][0] > 0 && $i < 6) {
			echo '<select name="concepto[' . $i . ']">'."\n";
			echo '					<option value="">&nbsp</option>'."\n";
			foreach($prod as $k => $v) {
				echo '					<option value="' . $v . '"';
				if($_SESSION['pres']['concepto'][$i] == $v) { echo ' selected '; }
				echo '>' . $v . '</option>'."\n";
			}
			echo '					</select></td>'."\n";
		} else {
			echo '<input type="text" name="concepto[' . $i . ']" value="';
			if($_SESSION['pres']['concepto'][$i] != '') { echo $_SESSION['pres']['concepto'][$i]; }
			echo '" size="40"/></td>'."\n";
		}
		echo '				<td style="text-align:center;"><input style="text-align:center;" type="text" name="cantidad[' . $i . ']" value="';
		if($_SESSION['pres']['cantidad'][$i] != '') { echo $_SESSION['pres']['cantidad'][$i]; } else { echo '1';}
		echo '" size="2" /></td>'."\n";
		echo '				<td><input style="text-align:right;" type="text" name="morep[' . $i . ']" value="';
		if($_SESSION['pres']['morep'][$i] != '') { echo $_SESSION['pres']['morep'][$i]; }
		echo '" size="4" /></td>'."\n";
		if($area != 7) {
			echo '				<td class="area' . $carea . '"><input style="text-align:right;" type="text" name="mocamb[' . $i . ']" value="';
			if($_SESSION['pres']['mocamb'][$i] != '') { echo $_SESSION['pres']['mocamb'][$i]; }
			echo '" size="4" /></td>'."\n";
			echo '				<td class="area' . $carea . '"><input style="text-align:right;" type="text" name="precio[' . $i . ']" value="';
			if($_SESSION['pres']['precio'][$i] != '') { echo $_SESSION['pres']['precio'][$i]; }
			echo '" size="4" /></td>'."\n";
		}
		echo '				<td><input style="text-align:right;" type="text" name="mo7[' . $i . ']" value="';
		if($_SESSION['pres']['mo7'][$i] != '') { echo $_SESSION['pres']['mo7'][$i]; }
		echo '" size="4" /></td></tr>'."\n";
	}
	if($accion==='valuar' && $sub_orden['sub_aseguradora'] > 0 && $valor['ValComoPartic'][0] != '1' ) {
		echo '			<tr><td style="text-align:left;" colspan="2">';
		echo 'Materiales de Pintura: <input type="text" name="matpint" value="';
		if($_SESSION['pres']['matpint'] != '') { echo $_SESSION['pres']['matpint']; }
		echo '" size="1"/>';
		echo '</td><td style="text-align:right;" colspan="5"></td></tr>'."\n";
	}
	echo '			<tr class="cabeza_tabla"><td colspan="7">&nbsp;</td></tr>
			<tr><td colspan="7">'."\n";
	echo '				<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
				<input type="hidden" name="orden_id" value="' . $sub_orden['orden_id'] . '" />
				<input type="hidden" name="aseguradora_id" value="' . $sub_orden['sub_aseguradora'] . '" />
				<input type="hidden" name="reporte" value="' . $sub_orden['sub_reporte'] . '" />
				<input type="hidden" name="area" value="' . $area . '" />
				<input type="hidden" name="estatus" value="' . $sub_orden['sub_estatus'] . '" />
			</td></tr>
			<tr><td colspan="7" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>'."\n";
	echo '		</table>
		</form>'."\n";
}

elseif ($accion==='presupuesto' || $accion==='avaluo' || $accion==='mod_avaluo') {

	if($accion==='presupuesto') { $oppres = 1; } else { $oppres = 'null'; }
	unset($_SESSION['pres']);

	$_SESSION['pres']['concepto'] = $concepto;
	$_SESSION['pres']['cantidad'] = $cantidad;
	$_SESSION['pres']['morep'] = $morep;
	$_SESSION['pres']['mocamb'] = $mocamb;
	$_SESSION['pres']['precio'] = $precio;
	$_SESSION['pres']['mo7'] = $mo7;
	if($matpint == '') { $matpint = 0; }
	$_SESSION['pres']['matpint'] = limpiarNumero($matpint);
	for($f = 1;$f < 11;$f++) {
		$preut[$f] = limpiarNumero($preut[$f]); if($preut[$f] > 0) { $_SESSION['pres']['preut'][$f] = $preut[$f]; }
//		echo $_SESSION['pres']['preut'][$f] . '<br>';
	}
	$prepint = limpiarNumero($prepint); $_SESSION['pres']['prepint'] = $prepint;
	$autosurtido = '0';  // Presupuesto de Taller por Autorizar....
	$error = 'no';
	$mensaje= '';
	$opxml = '';

	if (($error === 'no') && (isset($paquete) || is_array($concepto) || is_array($op_id) )) {
//		print_r($op_id);
		if (is_array($op_id)) {
			foreach($op_id as $i => $oi) {
				$param = " op_id = '" . $op_id[$i] . "'";
//				echo $param;
				if(isset($borrar[$i]) && $borrar[$i]=='1') {
					$pregp = "SELECT op_id, prod_id, op_cantidad, op_nombre, op_tangible FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op_id[$i] . "' AND op_pedido < '1' AND op_surtidos < '0.0001'";
					$matrp = mysql_query($pregp);
					$regr = mysql_fetch_array($matrp);
					if($regr['prod_id'] > 0) {
						$suma = "UPDATE " . $dbpfx . "productos SET prod_cantidad_disponible = prod_cantidad_disponible + " . $regr['op_cantidad'] . " WHERE prod_id = '" . $regr['prod_id'] . "'";
						$resultado = mysql_query($suma) or die("ERROR: no se actualizaron los productos!");
						$archivo = '../logs/' . date('Ymd-i') . '-base.ase';
						$myfile = file_put_contents($archivo, $suma . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
					}
					ejecutar_db($dbpfx . 'orden_productos', '', 'eliminar', $param);
					if($qv_activo == 1 && $regr['op_tangible'] == 1) {
						$opxml .= '			<Ref op_id="' . $op_id[$i] . '" op_estatus="90" />'."\n";
					}
				} else {
					$preunit[$i] = limpiarNumero($preunit[$i]);
					$cantp[$i] = limpiarNumero($cantp[$i]);
					$opsub = $cantp[$i] * $preunit[$i];
					$sqldata = array('op_cantidad' => $cantp[$i], 'op_precio' => $preunit[$i], 'op_subtotal' => $opsub);
					ejecutar_db($dbpfx . 'orden_productos', $sqldata, 'actualizar', $param);
					bitacora($orden_id, 'Precios de Presupuesto actualizados para Tarea ' . $sub_orden_id, $dbpfx);
					$utilped[$pedido[$i]] = 1;
				}
			}
		}
		unset($sqldata);

// ------ Actualizar utilidad de pedido ------
		foreach($utilped as $uk => $uv) {
			$actutilped = recalcUtilPed($uk, $dbpfx);
		}

//--------------  Determinación de número de Item   --------------------

		$preg6 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < 130";
		$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de subordenes items!");
		$item = 1;
		while($dato6 = mysql_fetch_array($matr6)) {
			$preg5 = "SELECT op_item FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $dato6['sub_orden_id'] . "' ORDER BY op_item DESC LIMIT 1";
  			$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden_productos!");
  			$dato5 = mysql_fetch_array($matr5);
			if($dato5['op_item'] >= $item) {$item = $dato5['op_item'] + 1;}
		}

// ------ Insertando conceptos de paquete
		if (isset($paquete) && $paquete!='') {
			$preg3 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE sub_orden_id='" . $sub_orden_id . "' AND sub_estatus >= '124' AND sub_estatus <= '127'";
			$matr3 = mysql_query($preg3) or die($preg3);
			$reporte = mysql_fetch_array($matr3);
			$preg4 = "SELECT sub_orden_id, sub_area FROM " . $dbpfx . "subordenes WHERE sub_reporte='" . $reporte['sub_reporte'] . "' AND sub_estatus >= '124' AND sub_estatus <= '127'";
			$matr4 = mysql_query($preg4) or die($preg4);
			while($tarea = mysql_fetch_array($matr4)) {
				$preg0 = "SELECT pc_prod_id, pc_prod_cant, pc_area_id FROM " . $dbpfx . "paq_comp WHERE pc_paq_id='" . $paquete . "' AND pc_activo = '1' AND pc_area_id = '" . $tarea['sub_area'] . "'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de paq_prods!");
				while($paqs = mysql_fetch_array($matr0)) {
					$preg1 = "SELECT prod_codigo, prod_nombre, prod_tangible, prod_precio FROM " . $dbpfx . "productos WHERE prod_id='" . $paqs['pc_prod_id'] . "'";
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
							'op_item' => $item,
							'prod_id' => $paqs['pc_prod_id'],
							'op_nombre' => $prods['prod_nombre'],
							'op_codigo' => $prods['prod_codigo'],
							'op_cantidad' => $paqs['pc_prod_cant'],
							'op_tangible' => $prods['prod_tangible'],
							'op_precio' => $prods['prod_precio'],
							'op_autosurtido' => $autosurtido,
							'op_pres' => $oppres,
							'op_subtotal' => $op_subtotal);
						ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
						$nueva_id = mysql_insert_id();
						$item++;
					}
				}
			}
		}
		
// ------ Determinamos las tareas en las que se aplicarán los cambios ------
		$tarea[$area] = $sub_orden_id;
		if($aseguradora_id > 0) { $siniestro = 1; } else { $siniestro = 0; }
		$preg = "SELECT sub_orden_id, sub_area, sub_aseguradora, sub_estatus, sub_siniestro, sub_poliza FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND sub_reporte = '" . $reporte . "' AND ";
		if($oppres == 1) {
			$preg .= " (sub_estatus = '124' OR sub_estatus = '127') ";
		} else {
			$preg .= " ((sub_estatus >= '102' AND sub_estatus <= '111') OR sub_estatus = '128' OR sub_estatus = '129' OR sub_estatus = '120')";				
		}
		$preg .= " AND recibo_id IS NULL AND fact_id IS NULL ";
		if($area == 7) { $pararea = 6; } else { $pararea = 7; }
		$preg .= " AND sub_area = '" . $pararea . "'";
//			echo $preg .'<br>';
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de tareas! " . $preg);
		while($rep = mysql_fetch_array($matr)) {
			$tarestat[$rep['sub_orden_id']] = $rep['sub_estatus'];
			$tarea[$pararea] = $rep['sub_orden_id'];
			if($rep['sub_poliza'] != '') { $poliza = $rep['sub_poliza']; }
			if($rep['sub_deducible'] != '') { $deducible = $rep['sub_deducible']; }
		}

// ------ Insertar nuevos conceptos en las tareas correspondientes ------
		$ercon = 0;
		if (is_array($concepto)) {
// ------ Determina si hay materiales de pintura y MO de pintura para saber si hay algún dato faltante
			$mopint = 0;
			foreach($mo7 as $k => $v) {
				$mopint = $mopint + $v;
			}
			if(($accion==='avaluo' || $accion==='mod_avaluo') && $aseguradora_id > 0 && $valor['ValComoPartic'][0] != '1') {
				if(is_numeric($matpint) && $mopint > 0 && $matpint <= 0) {
					$ercon = 1; $msg = 'Hay MO de pintura capturada, debe capturar Materiales de Pintura con precio mayor a 0.<br>'; 
				}
				if(is_numeric($matpint) && $mopint <= 0 && $matpint > 0) {
					$ercon = 1; $msg = 'Hay Materiales de Pintura capturados, debe capturar el precio de MO de Pintura.<br>';
				}
			} else {
				if($area == 7 && $mopint <= 0) {
					$ercon = 1; $msg = 'Está en el área de Pintura pero no capturó Precio por Pintar.<br>';
				}
				if($prepint <= 0 || $prepint >= 100) {
					$ercon = 1; $msg = 'El porcentaje de precio de Materiales de Pintura debe ser mayor al 0% y menor del 100% del Precio por Pintar.<br>';
				}
				$matpint = round(($mopint * ($prepint / 100)),2);
			}

// ------ Determina el área del concepto para decidir si se agrega a tarea actual ------
			foreach($concepto as $k => $nombre) {
				$nombre = limpiar_cadena($nombre);
				$concepto[$k] = limpiar_cadena($concepto[$k]);
				$cantidad[$k] = limpiarNumero($cantidad[$k]);
				$morep[$k] = limpiarNumero($morep[$k]);
				$mocamb[$k] = limpiarNumero($mocamb[$k]);
				$mo7[$k] = limpiarNumero($mo7[$k]);

				$ercon = 0;
				if($concepto[$k] == '' && ($morep[$k] > 0 || $mocamb[$k] > 0 || $mo7[$k] > 0)) { $ercon = 1; $msg = 'No hay nombre de Concepto en el renglón ' . $k . ', por favor colóquelo.<br>'; }
				if(($cantidad[$k] == '' || $cantidad[$k] == '0') && ($morep[$k] > 0 || $mocamb[$k] > 0 || $mo7[$k] > 0)) { $ercon = 1; $msg = $nombre . ' requiere cantidad, por favor colóquelo.<br>'; }
				if($morep[$k] > 0 && $mocamb[$k] > 0) { $ercon = 1; $msg = $nombre . ' no puede tener MO de reparación y cambio, coloque cantidad sólo en una casilla.<br>'; }
				if(($precio[$k] > 0 || $precio[$k] == '#') && $mocamb[$k] <= 0) { $ercon = 1; $msg = $nombre . ' requiere mano de obra para cambio.<br>'; }
				if($concepto[$k] != '' && $precio[$k] <= 0 && $precio[$k] != '#' && $mocamb[$k] <= 0 && $morep[$k] <= 0 && $mo7[$k] <= 0) { $ercon = 1; $msg = $nombre . ' requiere MO de reparación o precio de refacción y mano de obra para cambio o MO de pintura.<br>'; }
				if($morep[$k] > 0 && $precio[$k] > 0) { $ercon = 1; $msg = $nombre . ' tiene MO de reparación, no puede tener precio de refacciones.<br>'; }
				if($mocamb[$k] > 0 && $precio[$k] <= 0 && $precio[$k] != '#') { $ercon = 1; $msg = $nombre . ' tiene MO de cambio, debe tener un precio de refacciones.<br>'; }
				if($ercon == 0) {
// ------ Siendo entrada válida, procesar y después de asignar a Tarea, remover del Array y de SESSION['pres'] ------
					$op_subtotal = 0;
					for($m = 1; $m < 5; $m++) {
						if($m == 1 && ($precio[$k] > 0 || $precio[$k] == '#')) {
							if($precio[$k] == '#') {$op_precio = 0;} else {$op_precio = $precio[$k];}
							$op_subtotal = round(($cantidad[$k] * $op_precio), 2);
							$cant = $cantidad[$k];
							$tang = 1;
							$areanu = $area;
						} elseif($m == 2 && ($mocamb[$k] > 0 || $morep[$k] > 0)) {
							if($area == 7) { $areanu = 6; } else { $areanu = $area; }
							if($mocamb[$k] > 0) {
								if($areaut[$areanu] > 0) {
									$cant = round($mocamb[$k], 6);
									$op_precio = $areaut[$areanu];
								} else {
									$cant = round(($mocamb[$k] / $preut[$areanu]), 6);
									$op_precio = $preut[$areanu];
								}
								$op_subtotal = round(($cant * $op_precio), 2);
								$nombre = 'Cambio de ' . $concepto[$k];
							} else {
								if($areaut[$areanu] > 0) {
									$cant = round($morep[$k], 6);
									$op_precio = $areaut[$areanu];
								} else {
									$cant = round(($morep[$k] / $preut[$areanu]), 6);
									$op_precio = $preut[$areanu];
								}
								$op_subtotal = round(($cant * $op_precio), 2);
								$nombre = 'Reparación de ' . $concepto[$k];
							}
							$tang = 0;
							$tiempo[$areanu] = $tiempo[$areanu] + $cant;
						} elseif($m == 3 && is_numeric($matpint) && $matpint > 0) {
							$op_subtotal = $matpint;
							$op_precio = $matpint;
							$cant = 1;
							$nombre = 'Materiales de Pintura';
							$tang = 2;
							$areanu = 7;
						} elseif($m == 4 && $mo7[$k] > 0) {
//							echo 'MO antes: ' . $mo7[$k] . '<br>';
							$mo7[$k] = round(($mo7[$k] - ($mo7[$k] * ($prepint / 100))),2);
//							echo 'MO después: ' . $mo7[$k] . '<br>';
							if($areaut[(7-1)] > 0) {
								$cant = round($mo7[$k], 6);
								$op_precio = $areaut[(7-1)];
							} else {
								$cant = round(($mo7[$k] / $preut[7]), 6);
								$op_precio = $preut[7];
							}
//							echo 'MO Pint: ' . $cant . ' ' . $op_precio , '<br>';
							$op_subtotal = round(($cant * $op_precio), 2);
//							echo 'Sub total: ' . $op_subtotal . '<br>';
							$nombre = 'Pintado de ' . $concepto[$k];
							$tang = 0;
							$tiempo[7] = $tiempo[7] + $cant;
							$areanu = 7;
						}
						if($tarea[$areanu] == '' && $op_subtotal > 0) {
// ------ Si no existe una Tarea del área requerida, se crea -------
							$descripcion = 'Trabajos a realizar para ' . constant('NOMBRE_AREA_' . $nuarea); 
							$sql_data_array = array('orden_id' => $orden_id,
								'sub_area' => $areanu,
								'sub_descripcion' => $descripcion,
								'sub_siniestro' => $siniestro,
								'sub_estatus' => $estatus,
								'sub_reporte' => $reporte,
								'sub_aseguradora' => $aseguradora_id);
							ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
							$tarea[$areanu] = mysql_insert_id();
						}
						if($op_subtotal > 0 || ($precio[$k] == '#' && $m == 1)) {
							$sql_data_array3 = array('sub_orden_id' => $tarea[$areanu],
								'op_area' => $areanu,
								'op_item' => $item,
								'op_nombre' => $nombre,
								'op_cantidad' => $cant,
								'op_precio' => $op_precio,
								'op_subtotal' => $op_subtotal,
								'op_autosurtido' => '0',
								'op_pres' => $oppres,
								'op_tangible' => $tang);
							ejecutar_db($dbpfx . 'orden_productos', $sql_data_array3, 'insertar');
							$nueva_id = mysql_insert_id();
							$item++;
							if($qv_activo == 1 && $oppres != 1 && $m == 1) {
								$opxml .= '			<Ref op_id="' . $nueva_id . '" op_cantidad="' . $cant . '" op_nombre="' . $nombre . '" op_codigo="' . $codigo . '" op_estatus="10" />'."\n";
							}
							$op_subtotal = 0;
							$_SESSION['pres']['concepto'][$k] = '';
							$_SESSION['pres']['cantidad'][$k] = '';
							$_SESSION['pres']['morep'][$k] = '';
							$_SESSION['pres']['mocamb'][$k] = '';
							$_SESSION['pres']['precio'][$k] = '';
							$_SESSION['pres']['mo7'][$k] = '';
							if($m == 3 && is_numeric($matpint) && $matpint > 0) {
								$matpint = 'Procesado'; $_SESSION['pres']['matpint'] = '';
							}
							$parametros='sub_orden_id = ' . $tarea[$areanu];
							if($oppres == 1) {
								$sql_data_array = array('sub_estatus' => '127');
								ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
							} elseif($tarestat[$tarea[$areanu]] >= '120') {
								$sql_data_array = array('sub_estatus' => '102');
								ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
							}
						}
					}
				} else {
					$_SESSION['msjerror'] = $msg;
					if($accion==='presupuesto') { $hacer = 'presupuestar'; }
					else { $hacer = 'valuar'; }
					redirigir('presupuestos.php?accion=' . $hacer . '&sub_orden_id=' . $sub_orden_id);
				}
			}
		}

		if($accion==='presupuesto') {
			$sql_data_array = array('sub_estatus' => '127');
			$parametros = 'sub_orden_id = ' . $sub_orden_id;
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		}
		
		if($oppres != 1) {
			foreach($tarea as $k) {
				ajustaTarea($k, $dbpfx);
			}
		}
		if($qv_activo == 1 && $opxml != '') {
			$veh = datosVehiculo($orden_id, $dbpfx);
			$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
			$xml .= '		<Solicitud tiempo="' . time() . '">10</Solicitud>'."\n";
			$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'">'."\n";
			$xml .= $opxml;
			$xml .= '		</OT>'."\n";
			$xml .= '	</Comprador>'."\n";
			$mtime = substr(microtime(), (strlen(microtime())-3), 3);
			$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.qv';
//			echo $xmlnom;
//			echo '<br>' . $xml;
			file_put_contents("../qv-salida/".$xmlnom, $xml);
		}
	  	unset($_SESSION['pres']);
		actualiza_orden ($orden_id, $dbpfx);
	  	redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror'] = 'No se recibieron datos';
		redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id);
	}
}

?>
