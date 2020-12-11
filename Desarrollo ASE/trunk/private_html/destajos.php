<?php
include('parciales/funciones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}
include('idiomas/' . $idioma . '/destajos.php');

/*  ----------------  obtener nombres de usuarios   ------------------- */

		$consulta = "SELECT usuario, nombre, apellidos, comision FROM " . $dbpfx . "usuarios WHERE acceso = '0' AND activo = '1' ORDER BY nombre";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo selección de usuarios!");
//		$num_provs = mysql_num_rows($arreglo);
		$usu = array();
//		$provs[0] = 'Sin Proveedor';
		while ($usua = mysql_fetch_array($arreglo)) {
			$usu[$usua['usuario']] = array(
				'nom' => $usua['nombre'],
				'ape' => $usua['apellidos'],
				'com' => $usua['comision']
			);
		}
//		print_r($provs);

if (($accion==='aplica') || ($accion==='actualizar') || ($accion==='recibo') || ($accion==='insertar') || ($accion==='inspcpaq') || ($accion==='actpcpaq') || $accion==='generar') {
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}

if ($accion==="gestionar") {

	if(validaAcceso('1020000', $dbpfx)=='1' || $solovalacc != '1' || ($_SESSION['codigo'] <= '12')) {
		$mensaje = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso para Administradores']);
	}

	if(isset($usuarios) && $orden_ver > 0) {
		unset ($_SESSION['dest']);
		redirigir('destajos.php?accion=cesta&orden_ver='.$orden_ver);
	}

	foreach($seleccionado as $i => $v) {
		$previo = 0;
		$preg4 = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub_orden_id[$i] . "'";
		$matr4 = mysql_query($preg4) or die("Falló seleción de subordenes " . $preg4);
		$msub = mysql_fetch_array($matr4);
		
		foreach($_SESSION['dest']['sub_reporte'] as $ii => $rr) {
			if($sub_orden_id[$i] == $_SESSION['dest']['sub_orden_id'][$ii]) {
				$_SESSION['dest']['mensaje'] .= $lang['El destajo de'];
				$_SESSION['dest']['mensaje'] .= constant('NOMBRE_AREA_' . $msub['sub_area']) . $lang['del reporte'];
				if($rr == '0' || $rr == '') { $_SESSION['dest']['mensaje'] .= $lang['Particular']; }
				else { $_SESSION['dest']['mensaje'] .= $rr; }
				$_SESSION['dest']['mensaje'] .= $lang['en la OT'];
				$_SESSION['dest']['mensaje'] .= $msub['orden_id'];
				$_SESSION['dest']['mensaje'] .= $lang['ya había sido agregado al pre-recibo'].'<br>';
				$previo = 1;
			}
		}
		if($piezas[$i] == '0' && $destpiezas == '1' && $msub['sub_area'] == '7') {
			$_SESSION['dest']['mensaje'] .= $lang['piezas pintadas esta en 0 para la OT'] . $msub['orden_id'] . '<br>';
			$previo = 1;
		}
		if($previo == 0) {
			$porcentaje[$i] = limpiarNumero($porcentaje[$i]);
			$_SESSION['dest']['orden_id'][] = $msub['orden_id'];
			$veh = datosVehiculo($msub['orden_id'], $dbpfx);
			$_SESSION['dest']['vehiculo'][] = $veh['marca'] . ' ' . $veh['tipo'] . ' ' . $veh['color'] . ' ' . $veh['placas'];
			$_SESSION['dest']['sub_reporte'][] = $msub['sub_reporte'];
			$_SESSION['dest']['sub_area'][] = $msub['sub_area'];
			$_SESSION['dest']['sub_partes'][] = $sub_partes[$i];
			$_SESSION['dest']['sub_consumibles'][] = $sub_consumibles[$i];
			$_SESSION['dest']['sub_mo'][] = $sub_mo[$i];
			$_SESSION['dest']['piezas'][] = $piezas[$i];
			$_SESSION['dest']['porcen'][] = $porcentaje[$i];
			$_SESSION['dest']['recibo_id'][] = $msub['recibo_id'];
			$_SESSION['dest']['sub_orden_id'][] = $sub_orden_id[$i];
			$_SESSION['dest']['costcons'][] = $costcons[$i];
			$_SESSION['dest']['operador'][] = $msub['sub_operador'];
			$_SESSION['dest']['decodi'][] = $decodi[$i];
			$_SESSION['dest']['oprnum'][$msub['sub_operador']]++;
			$_SESSION['dest']['comision'][] = 0;
			$_SESSION['dest']['comi_tipo'][] = '';
			$_SESSION['dest']['comision_id'] = '';
		}
	}
		echo '		<form action="destajos.php?accion=cesta" method="post" enctype="multipart/form-data">'."\n";
		echo '		<table border="0"> <tr><td>'. $lang['Agregar la OT'].'<input type="text" name="orden_ver" /></td><td><input type="submit" name="Enviar" value="'. $lang['Enviar'].'" /></td></tr></table>'."\n";
		echo '		</form>';
		echo '		<form action="destajos.php?accion=aplica" method="post" enctype="multipart/form-data">'."\n";
		echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">';
		if (isset($_SESSION['dest']['mensaje'])) {
			echo '		<tr><td colspan="2"><span class="alerta">' . $_SESSION['dest']['mensaje'] . '</span></td></tr>';
			unset($_SESSION['dest']['mensaje']);
		}
		echo '		<tr class="cabeza_tabla"><td colspan="2">'. $lang['Gestionar Pago Destajos'].'</td></tr>
		<tr><td colspan="2" style="text-align:left;">'."\n";
      echo '<table border="1" width="100%" class="izquierda">
				<tr><td align="center">'. $lang['OT'].'</td><td>'. $lang['Vehículo'].'</td><td>'. $lang['Siniestro'].'</td><td>'. $lang['Area'].'</td><td>'. $lang['Destajo'].'</td>';
		if($destoper != '1') {
			echo '<td>'. $lang['Porcentaje'].'</td>';
		}
		echo '<td colspan="3">'. $lang['Recibo y Datos / Seleccionar'].'</td></tr>'."\n";
		$j=0;
		$total = 0;
		foreach ($_SESSION['dest']['orden_id'] as $k => $orden_id) {
			if($_SESSION['dest']['sub_area'][$k] == '6') { $fondo = 'area6'; }
			elseif($_SESSION['dest']['sub_area'][$k] == '7') { $fondo = 'area7'; }
			else { $fondo = 'areaotra';}
			echo '				<tr class="' . $fondo . '">';
			echo '<td><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '" target="_blank">' . $orden_id . '</a></td>';
			echo '<td>' . $_SESSION['dest']['vehiculo'][$k] . '</td>';
			echo '<td>';
			if($_SESSION['dest']['sub_reporte'][$k] == '0' || $_SESSION['dest']['sub_reporte'][$k] == '') { echo 'Particular'; }
			else { echo $_SESSION['dest']['sub_reporte'][$k]; }
			echo '</td>';
			if($_SESSION['dest']['comision'][$k] == 1) {
				echo '<td>' . $_SESSION['dest']['comi_tipo'][$k] . '</td>';
			} else {
				echo '<td>' . constant('NOMBRE_AREA_' . $_SESSION['dest']['sub_area'][$k]) . '</td>';
			}

			include('particular/determina_mo.php');

			$total = $total + $_SESSION['dest']['monto'][$k];
			echo '<td style="text-align:right;">' . number_format($_SESSION['dest']['monto'][$k],2) . '</td>';
			if($_SESSION['dest']['decodi'][$k] == 1) {
				echo '<td>' . $lang['Comisión Directa'] . '</td>';
			} elseif($destoper != '1') {
				echo '<td>' . $_SESSION['dest']['porcen'][$k] . '</td>';
			} else {
				echo '<td>' . $lang['NA'] . '</td>';
			}
			if($_SESSION['dest']['recibo_id'][$k] > '0') {
				echo '<td><a href="recibosrh.php?accion=consultar&recibo_id=' . $_SESSION['dest']['recibo_id'][$k] . '">' . $_SESSION['dest']['recibo_id'][$k] . '</a></td>';
				$preg3 = "SELECT usuario, saldado FROM " . $dbpfx . "destajos WHERE recibo_id = '" . $_SESSION['dest']['recibo_id'][$k] . "'";
				$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de recibo!");
				$rec = mysql_fetch_array($matr3);
				echo '<td>' . $usu[$rec['usuario']]['nom'] . ' ' . $usu[$rec['usuario']]['ape'] . '</td><td>';
				if($rec['saldado'] == '1') { echo 'Pagado'; } else { echo 'Pendiente'; }
				echo '</td></tr>'."\n";
			} else {
				echo '<td colspan="3">Pagar <input type="checkbox" name="seleccionado[' . $k . ']" value="1" checked="checked" /> ' . $usu[$_SESSION['dest']['operador'][$k]]['nom'] . ' ' . $usu[$_SESSION['dest']['operador'][$k]]['ape'] . '</td></tr>'."\n";
			}
		}
		if($destiva == '1') {
			echo '<tr><td colspan="4" style="text-align:right;">'. $lang['SubTotal'].'</td><td colspan="1" style="text-align:right;">' . number_format($total,2) . '</td><td colspan="4"></td></tr>';
			$iva = round(($total * $impuesto_iva), 2);
			echo '<tr><td colspan="4" style="text-align:right;">'. $lang['IVA al 16'].'</td><td colspan="1" style="text-align:right;">' . number_format($iva,2) . '</td><td colspan="4"></td></tr>';
		}
		$gran = $total + $iva;
		echo '<tr><td colspan="4" style="text-align:right;">'. $lang['Total'].'</td><td colspan="1" style="text-align:right;">' . number_format($gran,2) . '</td><td colspan="4"></td></tr>';
		echo '<tr><td colspan="9">'. $lang['Destajos'] . ($k + 1) . '.'. $lang['Promedio por Destajo antes de IVA'] . number_format(($total / ($k + 1)),2) . ' </td></tr>';

		echo '			</table></td></tr>'."\n";
		echo '		<tr><td style="text-align:left;"><input type="submit" value="'. $lang['Enviar'].'" />&nbsp;<button name="limpiar" value="'. $lang['limpiar'].'">'. $lang['Limpiar Pantalla'].'</button>&nbsp;<button name="recalcula" value="recalcula">'. $lang['Recalcular'].'</button></td><td>';
		if(count($_SESSION['dest']['oprnum']) == 1) {
			foreach($_SESSION['dest']['oprnum'] as $l => $w) {$oprdest = $l;}
			echo $lang['Confirme Operador'];
		} elseif(count($_SESSION['dest']['oprnum']) > 1) {
			echo $lang['Diferente Operador'];
		} else {
			echo $lang['Seleccione Operador que se pagará'];
		}

		echo '				<select name="operador" size="1" />
					<option value="'.$lang['Seleccione'].'">'.$lang['Seleccione'].'</option>'."\n";
		foreach($usu as $k => $v) {
			echo '					<option value="' . $k . '|' . $v['nom'] . '|' . $v['ape'] . '|' . $v['com'] . '"';
			if($oprdest == $k) { echo ' selected ';}
			echo ' >' . $v['nom'] . ' ' . $v['ape'] . '</option>'."\n";
		}
		echo '	</td></tr>'."\n";
		echo '	<tr><td class="cabeza_tabla" colspan="2">&nbsp;</td></tr>'."\n";
		echo '	<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="destajos.php?accion=generar"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a Listado'].'" title="'. $lang['Regresar a Listado'].'"></a></div></td></tr>'."\n";
		echo ' </table>
	</form>';
}

elseif ($accion==="cesta") {

	if(validaAcceso('1020005', $dbpfx)=='1' || ($solovalacc != '1' && ($_SESSION['codigo'] <= '12'))) {
		$mensaje = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso para Administradores']);
	}

	$pregunta = "SELECT s.orden_id, s.sub_orden_id, s.sub_area, s.sub_estatus, s.sub_partes, s.sub_reporte, s.sub_consumibles, s.sub_mo, s.recibo_id, s.sub_fecha_terminado, s.sub_aseguradora, s.sub_horas_programadas, s.sub_operador, o.orden_estatus FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE s.orden_id = '$orden_ver' AND s.sub_estatus < '130' AND o.orden_id = s.orden_id ";
	if($destcomdir != 1) {
		$pregunta .= " AND s.sub_mo > 0 ";
	}
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de Tareas!");
	$f1 = mysql_num_rows($matriz);

	if($f1 > 0) {
		echo '	<form action="destajos.php?accion=gestionar" method="post" enctype="multipart/form-data">'."\n";
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">';
		echo '		<tr><td colspan="2"><span class="alerta">' . $_SESSION['dest']['mensaje'] . '</span></td></tr>';
		echo '		<tr class="cabeza_tabla"><td colspan="2">'. $lang['Seleccion Tareas para Pago de Destajos'].'</td></tr>
		<tr><td colspan="2" style="text-align:left;">'."\n";
		echo '			<table border="1" width="100%" class="izquierda">
				<tr><td align="center">'. $lang['OT'].'</td><td>'. $lang['Vehículo'].'</td><td>'. $lang['Siniestro'].'</td><td>'. $lang['Fecha Terminado'].'</td><td>'. $lang['Area'].'</td><td>'. $lang['Costo'].'<br>'. $lang['Pintura'].'</td><td>'. $lang['Consumibles'].'</td><td>'. $lang['MO'].'</td><td>'. $lang['Horas'].'</td>';
		if($destpiezas == '1') {echo '<td>'. $lang['Piezas'].'</td>';}
		if($destoper != '1' && $destpiezas == '1') { echo '<td>'. $lang['Destajo %'].'<br>'. $lang['Pago por Pieza'].'</td>';}
		elseif($destoper != '1') {echo '<td>'. $lang['Destajo %'].'</td>';}
		else { echo '<td>' . $lang['Monto'] . '</td>'; }
		echo '<td colspan="3">'. $lang['Recibo y Datos / Seleccionar'].'</td></tr>'."\n";
		$mo_part = 0; $desterm = 0;
		while($mopart = mysql_fetch_array($matriz)) {
			if(($mopart['sub_reporte'] == '0' || $mopart['sub_reporte'] == '') && ($mopart['sub_area']== '6' || $mopart['sub_area']== '7')) {
				$mo_part = $mo_part + $mopart['sub_mo'];
			}
			if($mopart['orden_estatus'] == '210' || $mopart['orden_estatus'] < '12' || ($mopart['orden_estatus'] > '16' && $mopart['orden_estatus'] < '99')) { $desterm = 1; }
		}
		mysql_data_seek($matriz, 0);
		$j=0;
		while($sub = mysql_fetch_array($matriz)) {
//			if($sub['sub_reporte'] != 'Interno') {
				$costcons =0;
				if($sub['sub_area']== '6') {
					$fondo = 'area6';
				} elseif($sub['sub_area']== '7') {
					$fondo = 'area7';
					$preg4 = "SELECT op_costo, op_cantidad FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_tangible = '2'";
					$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de consumibles!");
					while($cc = mysql_fetch_array($matr4)) {
						$costcons = $costcons + ($cc['op_costo'] * $cc['op_cantidad']);
					}
					echo '<input type="hidden" name="costcons[' . $j . ']" value="' . $costcons . '" />';
				} else {
					$fondo = 'areaotra';
				}
				include('particular/determina_mo.php');
				$pregunta2 = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_color, v.vehiculo_modelo, v.vehiculo_placas FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "ordenes o WHERE o.orden_id = '" . $orden_ver . "' AND o.orden_vehiculo_id = v.vehiculo_id";
				$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección de vehículo!");
//		echo $pregunta2;
				$veh = mysql_fetch_array($matriz2);
				$vehiculo = $veh['vehiculo_marca'] . ' ' . $veh['vehiculo_tipo'] . ' ' . $veh['vehiculo_color'] . ' ' . $veh['vehiculo_modelo'] . ' ' .$lang['Placas'] . $veh['vehiculo_placas'];

				echo '<input type="hidden" name="sub_orden_id[' . $j . ']" value="' . $sub['sub_orden_id'] . '" /><input type="hidden" name="sub_partes[' . $j . ']" value="' . $sub['sub_partes'] . '" /><input type="hidden" name="sub_consumibles[' . $j . ']" value="' . $cons . '" /><input type="hidden" name="sub_mo[' . $j . ']" value="' . $mo . '" />';
				echo '				<tr class="' . $fondo . '">';
				echo '<td><a href="ordenes.php?accion=consultar&orden_id=' . $sub['orden_id'] . '" target="_blank">' . $sub['orden_id'] . '</a></td>';
				echo '<td>' . $vehiculo . '</td>';
				if($sub['sub_reporte'] == '0' || $sub['sub_reporte'] == '') { $sub['sub_reporte'] = 'Particular'; }
				echo '<td>' . $sub['sub_reporte'] . '</td>';
				echo '<td>' . $sub['sub_fecha_terminado'] . '</td>';
				echo '<td>' . constant('NOMBRE_AREA_' . $sub['sub_area']) . '</td>';
				echo '<td>$' . number_format($costcons,2) . '</td>';
				echo '<td>$' . number_format($cons,2) . '</td>';
				if($destoper != '1') {
					if($sub['sub_reporte'] == 'Particular') {
						$mo_porcen = $destpart[$sub['sub_area']] * 100;
					} else {
						$mo_porcen = $destajo[$sub['sub_area']] * 100;
					}
				}
				if($mo == 0 && $destcomdir == 1) {
					echo '<td colspan="2">' . $lang['Comisión Directa'] . '<input type="hidden" name="decodi[' . $j . ']" value="1" /></td>';
				} else {
					echo '<td>$' . number_format($mo,2) . '</td>';
					echo '<td>' . $sub['sub_horas_programadas'] . '</td>';
				}
				if($destpiezas == '1' && $sub['sub_area']== '7') {
					echo '<td><input type="text" name="piezas[' . $j . ']" value="0" size="3" /></td>';
				} elseif($destpiezas == '1') {
					echo '<td>'. $lang['NA'].' </td>';
				}
				if($destoper != '1') {
					echo '<td><input type="text" name="porcentaje[' . $j . ']" value="' . $mo_porcen . '" size="3" /></td>';
				} else {
					echo '<td><input type="text" name="porcentaje[' . $j . ']" size="5" /></td>';
				}

				if($sub['recibo_id'] > '0') {
					echo '<td><a href="recibosrh.php?accion=consultar&recibo_id=' . $sub['recibo_id'] . '">' . $sub['recibo_id'] . '</a></td>';
					$preg3 = "SELECT usuario, saldado FROM " . $dbpfx . "destajos WHERE recibo_id = '" . $sub['recibo_id'] . "'";
					$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de recibo!");
					$rec = mysql_fetch_array($matr3);
					echo '<td>' . $usu[$rec['usuario']]['nom'] . ' ' . $usu[$rec['usuario']]['ape'] . '</td><td>';
					if($rec['saldado'] == '1') { echo 'Pagado'; } else { echo 'Pendiente'; }
					echo '</td></tr>'."\n";
				} elseif($desterm == '1' && $DestSubTerm == '1') {
					echo '<td colspan="3">'. $lang['TareasSinTerminar'].'</td></tr>'."\n";
				} elseif($sub['sub_area']== '7' && $costcons == '0' && $moycons == '1') {
					echo '<td colspan="3">'. $lang['No se ha capturado el Costo de Pintura'].'</td></tr>'."\n";
				} elseif($CostoEnH == '1' && $costcons == '0' && $moycons == '1') {
					// -- Esta variable $CostoEnH se coloca en parciales/determina_mo.php
					echo '<td colspan="3">'. $lang['No se ha capturado el Costo de Pintura'].'</td></tr>'."\n";
				} elseif($destsinterm != '1' && ($sub['sub_estatus'] < '112' || $sub['sub_estatus'] > '116')) {
					echo '<td colspan="3">'. $lang['No se ha Terminado la Tarea'].'</td></tr>'."\n";
				} elseif($sub['sub_estatus'] <= '103' || ($sub['sub_estatus'] > '112' && $sub['sub_estatus'] != '121')) {
					echo '<td colspan="3">'. $lang['No en reparación'].'</td></tr>'."\n";
				} elseif($destsinterm == 1 && ($destoper == 1 || $destcomdir == 1)) {
					echo '<td colspan="3"><input type="checkbox" name="seleccionado[' . $j . ']" value="1" />' . $usu[$sub['sub_operador']]['nom'] . ' ' . $usu[$sub['sub_operador']]['ape'];
					echo '</td></tr>'."\n";
				} elseif($destsinterm == '1' && ($sub['sub_estatus'] == '104' || ($sub['sub_estatus'] >= '107' && $sub['sub_estatus'] <= '110'))) {
					echo '<td colspan="3">'. $lang['No se ha Terminado la Tarea'].'</td></tr>'."\n";
				} else {
					echo '<td colspan="3"><input type="checkbox" name="seleccionado[' . $j . ']" value="1" />' . $usu[$sub['sub_operador']]['nom'] . ' ' . $usu[$sub['sub_operador']]['ape'];
					echo '</td></tr>'."\n";
				}
				$j++;
//			}
		}
		echo '			</table></td></tr>'."\n";
		echo '		<tr><td style="text-align:left;" colspan="2"><input type="submit" value="'. $lang['Enviar'].'" />&nbsp;<input type="reset" name="limpiar" value="'. $lang['Limpiar selecciones'].'" /></td></tr>'."\n";
		echo '		<tr><td class="cabeza_tabla" colspan="2">&nbsp;</td></tr>'."\n";
		echo '	<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="destajos.php?accion=gestionar"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a Destajos'].'" title="'. $lang['Regresar a Destajos'].'"></a></div></td></tr>'."\n";
		echo '	</table>
	</form>';
	} else {
		echo '<span class="alerta">En la OT ' . $orden_ver . $lang['no se ha presupuestado MO'].'</span>';
	}
}

elseif ($accion==="aplica") {

	if(validaAcceso('1020010', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['codigo'] <= '12'))) {
		$mensaje = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso para Administradores']);
	}

	$_SESSION['dest']['mensaje']='';
	$oper = explode('|', $operador);
	$mensaje = '';
	$error = 'no';
//	echo $operador . ': ' . $oper[0] . ' -> ' . $oper[1] . ' -> ' . $oper[2] .  ' -> ' . $oper[3] . '<br>' ;
	if(isset($limpiar) && $limpiar=='limpiar') {
		unset ($_SESSION['dest']);
		redirigir('destajos.php?accion=gestionar');
	}
	if(isset($recalcula) && $recalcula=='recalcula') {
		foreach($_SESSION['dest']['sub_reporte'] as $k => $m) {
			if($seleccionado[$k] != '1') {
				unset($_SESSION['dest']['orden_id'][$k]);
				unset($_SESSION['dest']['vehiculo'][$k]);
				unset($_SESSION['dest']['sub_reporte'][$k]);
				unset($_SESSION['dest']['sub_area'][$k]);
				unset($_SESSION['dest']['sub_partes'][$k]);
				unset($_SESSION['dest']['sub_consumibles'][$k]);
				unset($_SESSION['dest']['sub_mo'][$k]);
				unset($_SESSION['dest']['porcen'][$k]);
				unset($_SESSION['dest']['recibo_id'][$k]);
				unset($_SESSION['dest']['sub_orden_id'][$k]);
				unset($_SESSION['dest']['costcons'][$k]);
				unset($_SESSION['dest']['operador'][$k]);
				unset($_SESSION['dest']['decodi'][$k]);
				unset($_SESSION['dest']['oprnum']);
			}
		}
		redirigir('destajos.php?accion=gestionar');
	}
	if(!is_array($seleccionado)) { $mensaje .= $lang[ 'Seleccion Tareas para Pago de Destajos'].'<br>'; $error = 'si'; }
	if($oper[0] == '0' || $oper[0] == '' || !is_numeric($oper[0])) { $mensaje .= $lang[ 'Seleccione Operador que se pagará Destajo'].'<br>'; $error = 'si'; }
	if($destoper == '1') {
//		if($oper[3] == '0' || $oper[3] == '' || !is_numeric($oper[3])) { $mensaje .=  $lang['Operador no tiene comisión asignada'].'<br>'; $error = 'si'; }
	} else {
		foreach($_SESSION['dest']['porcen'] as $k => $n) {
			if($destpiezas == '1') {
				if($_SESSION['dest']['sub_area'][$k] != '7') {
					if(($n > 100 || $n < 0) ) {
						$mensaje .=  $lang['El destajo asignado es mayor al 100% o menor al 0%'].'<br>'; $error = 'si';
					}
				}
			} elseif($_SESSION['dest']['decodi'][$k] == '1') {
				// No afectar -- Comisión directa
			} else {
				if(($n > 100 || $n < 0) ) {
					$mensaje .=  $lang['El destajo asignado es mayor al 100% o menor al 0%'].'<br>'; $error = 'si';
				}
			}
		}
	}
	$j=0;

	if($error == 'no') {
		$sql_array = [
			'usuario' => $oper[0],
			'usuario_paga' => $_SESSION['usuario']
		];
		$recibo_id = ejecutar_db($dbpfx . 'destajos', $sql_array, 'insertar');
		$total = 0; $total_cons = 0;
		foreach($seleccionado as $i => $v) {
			if($destoper == '1') {
//				$_SESSION['dest']['porcen'][$i] = $oper[3];
			}
			
			$_SESSION['dest']['monto'][$i] = round($_SESSION['dest']['monto'][$i],2);
			$_SESSION['dest']['costcons'][$i] = round($_SESSION['dest']['costcons'][$i],2);

			$total = $total + $_SESSION['dest']['monto'][$i];
			$total_cons = $total_cons + $_SESSION['dest']['costcons'][$i];

			$sql_data = [
				'recibo_id' => $recibo_id,
				'orden_id' => $_SESSION['dest']['orden_id'][$i],
				'area' => $_SESSION['dest']['sub_area'][$i],
				'monto' => $_SESSION['dest']['monto'][$i],
				'costcons' => $_SESSION['dest']['costcons'][$i],
				'porcentaje' => $_SESSION['dest']['porcen'][$i],
				'reporte' =>  $_SESSION['dest']['sub_reporte'][$i],
				'vehiculo' => $_SESSION['dest']['vehiculo'][$i],
				'operador' => $oper[0]
			];
			if($_SESSION['dest']['comision'][$i] == 1){
				$sql_data['comision'] = $_SESSION['dest']['comi_tipo'][$i];
			} else{
				$sql_data['comision'] = null;
			}
//			print_r($sql_data);
			if($destoper == '1' || $_SESSION['dest']['decodi'][$i] == '1') { $sql_data['porcentaje'] = 0; }
			ejecutar_db($dbpfx . 'destajos_elementos', $sql_data, 'insertar');
			$param = "sub_orden_id = '" . $_SESSION['dest']['sub_orden_id'][$i] . "'";
			$sql_data = array('recibo_id' => $recibo_id);
			ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $param);
			bitacora($_SESSION['dest']['orden_id'][$i], $lang['Recibo de destajo'] . $recibo_id . $lang[ 'generado para Operador'] . $oper[0], $dbpfx);
			if($_SESSION['dest']['comision'][$i] == 1){
				$sql_data = [
					'recibo_id' => $recibo_id,
					'estatus' => 20,
				];
				$parametros = "com_id = '" . $_SESSION['dest']['comision_id'][$i] . "'";
				ejecutar_db($dbpfx . 'comisiones', $sql_data, 'actualizar', $parametros);
			}
		}
		if($destiva == 1) {
			$iva = round(($total * $impuesto_iva),2);
			$iva_cons = round(($total_cons * $impuesto_iva),2);
		}
		$param = "recibo_id = '" . $recibo_id . "'";
		$sql_data = [
			'monto' => $total,
			'impuesto' => $iva,
			'monto_cons' => $total_cons,
			'impuesto_cons' => $iva_cons
		];
		ejecutar_db($dbpfx . 'destajos', $sql_data, 'actualizar', $param);
		unset ($_SESSION['dest']);
		redirigir('recibosrh.php?accion=consultar&recibo_id=' . $recibo_id);
	} else {
//		echo $mensaje;
   	$_SESSION['dest']['mensaje'] = $mensaje;
   	redirigir('destajos.php?accion=gestionar');
   }
}

elseif ($accion==="generar") {
// ----------> Generar Listado de Calculo de Destajos
	if(validaAcceso('1020005', $dbpfx) == '1' || validaAcceso('1020015', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['codigo'] <= '12'))) {
		$mensaje = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso para Administradores']);
	}

	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	echo '		<form action="destajos.php?accion=generar" method="post" enctype="multipart/form-data">'."\n";
	echo '			<table cellpadding="0" cellspacing="0" border="0" width="80%">'."\n";
	echo '				<tr class="cabeza_tabla"><td colspan="2">Listado de Destajos'."\n";
	echo '				<tr><td>'. $lang['Destajos'] . ' ' . $lang['Por Calcular'] . '<input type="radio" name="estatus" value="" ';
	if(!isset($estatus) || $estatus == '') { echo 'checked="checked" ';}
	echo '/>&nbsp;&nbsp;' . $lang['Calculados'] . '<input type="radio" name="estatus" value="1" ';
	if($estatus == '1') { echo 'checked="checked" ';}
	echo '/>&nbsp;&nbsp;' . $lang['Todos'] .'<input type="radio" name="estatus" value="2" ';
	if($estatus == '2') { echo 'checked="checked" ';}
	echo '/></td><td>' . $lang['Operador'];
	echo '					<select name="oprdest">
						<option value="">Seleccione para filtrar</option>'."\n";
	foreach($usu as $k => $v){
		echo '						<option value="' . $k . '" ';
		if($oprdest == $k) { echo 'selected ';}
		echo '>' . $v['nom'] . ' ' . $v['ape'] . '</option>'."\n";
	}
	echo '					</select>';
	echo '</td></tr>'."\n";

	if(isset($feini) && $feini != '0000-00-00') {
		$feini = date('Y-m-d 00:00:00', strtotime($feini));
		if(!isset($fefin) || $fefin == '' || $fefin == '0000-00-00') {
			$fefin = date('Y-m-t 23:59:59', time());
		}
		$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
	} else {
		$feini = date('Y-m-01 00:00:00');
		$fefin = date('Y-m-t 23:59:59');
	}

	echo '				<tr><td colspan="2">'. $lang['Seleccione las fechas'].'<br>'."\n";

	require_once("calendar/tc_calendar.php");
	echo '					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<tr><td>Fecha de Inicio<br>';
		//instantiate class and set properties
	$myCalendar = new tc_calendar("feini", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
	$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
//	$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
//	$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2011, 2020);
	$myCalendar->setAutoHide(true, 5000);

		//output the calendar
	$myCalendar->writeScript();
	echo '</td><td>Fecha de Fin<br>';
		//instantiate class and set properties
	$myCalendar = new tc_calendar("fefin", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
	$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
//	$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
//	$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2011, 2020);
	$myCalendar->setAutoHide(true, 5000);

		//output the calendar
	$myCalendar->writeScript();
	echo '</td></tr><tr><td colspan="2"><input type="submit" value="Enviar" /></td></tr></table>
				</td></tr>
			</table></form>';

/*	if(!$feini && !$fefin) {
		$diasem = date('w'); $hoy = strtotime(date('Y-m-d 00:00:00')); $diasem++;
		$feini = date('Y-m-d 00:00:00', ($hoy - ($diasem * 86400)));
		$fefin = date('Y-m-d 00:00:00');
	}

	$fefin = date('Y-m-d H:i:s', (strtotime($fefin) + 86000));
*/

	$encabezado = 'Destajos del ' . strftime('%e de %B del %Y', strtotime($feini)) . ' al ' . strftime('%e de %B del %Y', strtotime($fefin));

	$encabezado = $feini . ' al ' . $fefin;

	if($oprdest != '') { $encabezado .= ' para ' . $usu[$oprdest]['nom'] . ' ' . $usu[$oprdest]['ape'];}


	$preg1 = "SELECT s.sub_orden_id, s.sub_mo, s.sub_consumibles, s.sub_reporte, s.sub_area, o.orden_estatus, s.orden_id, s.sub_fecha_terminado, s.sub_aseguradora, s.recibo_id, s.sub_horas_programadas, s.sub_operador FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE s.sub_fecha_terminado > '" . $feini . "' AND s.sub_fecha_terminado < '" . $fefin . "' AND o.orden_id = s.orden_id ";
	if($destsinterm == '1') {
		$preg1 .= " AND (s.sub_estatus = '105' OR s.sub_estatus = '106' OR s.sub_estatus = '111' OR s.sub_estatus = '112' OR s.sub_estatus = '121') ";
	} else {
		$preg1 .= " AND s.sub_estatus = '112' ";
	}
	if($destcomdir != 1) {
		$preg1 .= " AND s.sub_mo > 0 ";
	}
	if(!isset($estatus) || $estatus == '') { $preg1 .= " AND s.recibo_id IS NULL "; }
	elseif($estatus == 1) { $preg1 .= " AND s.recibo_id > 0 "; }
	elseif($estatus == 2) { $preg1 .= " AND (s.recibo_id > 0 OR s.recibo_id IS NULL) "; }
	if($oprdest != '') { $preg1 .= " AND s.sub_operador = '$oprdest' "; }
	$preg1 .= " ORDER BY s.sub_operador,s.sub_fecha_terminado";
	$matr1 = mysql_query($preg1);

	echo '			<form action="destajos.php?accion=gestionar" method="post" enctype="multipart/form-data">'."\n";
	echo '			<table cellspacing="1" cellpadding="2" border="0">' . "\n";
	if($destpiezas == '1') {$cols = 14;} else {$cols = 13;}
	echo '				<tr class="cabeza_tabla"><td colspan="'.$cols.'" style="text-align: right;">' . $encabezado . '</td></tr>' . "\n";
	echo '				<tr><td>'.$lang['OT'].'</td><td>'.$lang['Vehículo'].'</td><td>Siniestro</td><td>Fecha de<br>Terminado</td><td>Área</td><td>Costo de <br>Consumibles</td><td>Venta de<br>Consumibles</td><td>MO</td><td>Horas</td>';
	if($destpiezas == '1') {
		echo '<td>Piezas</td><td>% Destajo /<br>Por pieza</td>';
	} elseif($destoper != '1') {
		echo '<td>' . $lang['Destajo %'] . '</td>';
	} else {
		echo '<td>' . $lang['Monto'] . '</td>';
	}
	echo '<td>Operador</td><td colspan="2">Seleccionar o<br>Recibo Destajo</td></tr>'."\n";
	unset($mopartic); unset($desterm);
	while($mopart = mysql_fetch_array($matr1)) {
		if(($mopart['sub_reporte'] == '0' || $mopart['sub_reporte'] == '') && ($mopart['sub_area']== '6' || $mopart['sub_area']== '7')) {
			$mopartic[$mopart['orden_id']] = $mopartic[$mopart['orden_id']] + $mopart['sub_mo'];
		}
		if($mopart['orden_estatus'] == '210' || $mopart['orden_estatus'] < '12' || ($mopart['orden_estatus'] > '16' && $mopart['orden_estatus'] < '99')) { $desterm[$mopart['orden_id']] = 1; }
	}
	mysql_data_seek($matr1, 0);
	$fondo = 'claro';
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	while($sub = mysql_fetch_array($matr1)) {
		if($sub['sub_area']== '6') {
			$fondo = 'area6';
		} elseif($sub['sub_area']== '7') {
			$fondo = 'area7';
		} else {
			$fondo = 'areaotra';
		}

		$mo_part = $mopartic[$sub['orden_id']];

		include('particular/determina_mo.php');
		$veh = datosVehiculo($sub['orden_id'], $dbpfx);
		$vehiculo = $veh['marca'] . ' ' . $veh['tipo'] . ' ' . $veh['color'] . ' ' . $veh['placas'];
		$preg2 = "SELECT op_costo, op_cantidad FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_tangible = '2'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de consumibles!".$preg2);
		$costcons = 0;
		while($cc = mysql_fetch_array($matr2)) {
			$costcons = $costcons + ($cc['op_costo'] * $cc['op_cantidad']);
		}

		if($destoper != '1') {
			if($sub['sub_reporte'] == '0' || $sub['sub_reporte'] == '') {
				$mo_porcen = $destpart[$sub['sub_area']] * 100;
			} else {
				$mo_porcen = $destajo[$sub['sub_area']] * 100;
			}
		}

		echo '				<tr class="' . $fondo . '"><td><a href="ordenes.php?accion=consultar&orden_id=' . $sub['orden_id'] . '" target="_blank">' . $sub['orden_id'] . '</a></td><td>' . strtoupper($vehiculo) . '</td><td>';
		if($sub['sub_reporte'] == '0' || $sub['sub_reporte'] == '') { echo 'Particular'; } else { echo $sub['sub_reporte']; }
		echo '</td><td>' . date('d/M/Y', strtotime($sub['sub_fecha_terminado'])) . '</td><td>' . constant('NOMBRE_AREA_'.$sub['sub_area']) . '</td>';
		if($mo == 0 && $destcomdir == 1) {
			echo '<td colspan="4" style="text-align:right;">' . $lang['Comisión Directa'] . '<input type="hidden" name="decodi[' . $j . ']" value="1" /></td>';
		} else {
			echo '<td style="text-align:right;">' . number_format($costcons,2) . '</td><td style="text-align:right;">' .  number_format($cons,2) . '</td><td style="text-align:right;">' .  number_format($mo,2) . '</td><td>' . $sub['sub_horas_programadas'] . '</td>';
		}
		if($destpiezas == '1' && is_null($sub['recibo_id'])) {
			echo '<td>';
			if($sub['sub_area']== '7') {
				echo '<input type="text" name="piezas[' . $j . ']" value="0" size="3" />';
			} else {
				echo $lang['NA'];
			}
			echo '</td>';
		} elseif($destpiezas == '1' ) {
			echo '<td></td>';
		}
		echo '<td>';
		if(is_null($sub['recibo_id'])) {
			echo '<input type="text" name="porcentaje[' . $j . ']" value="' . $mo_porcen . '" size="3" />';
		}
		echo '</td><td>' . $usu[$sub['sub_operador']]['nom'] . ' ' . $usu[$sub['sub_operador']]['ape'] . '</td>';
		if($desterm[$sub['orden_id']] == '1' && $DestSubTerm == '1') {
			echo '<td colspan="2">'. $lang['TareasSinTerminar'];
		} elseif($sub['sub_area']== '7' && $costcons == '0' && $moycons == '1') {
			echo '<td colspan="2">'. $lang['No se ha capturado el Costo de Pintura'];
		} elseif($CostoEnH == 1 && $costcons == '0' && $moycons == '1') {
			// -- Esta variable $CostoEnH se coloca en parciales/determina_mo.php
			echo '<td colspan="2">'. $lang['No se ha capturado el Costo de Pintura'];
		} elseif(is_null($sub['recibo_id'])) {
			echo '<td style="text-align:center;" colspan="2"><input type="checkbox" name="seleccionado[' . $j . ']" value="1" />';
		} else {
			echo '<td style="text-align:center;"><a href="recibosrh.php?accion=consultar&recibo_id=' . $sub['recibo_id'] . '" target="_blank">' . $sub['recibo_id'] . '</a></td><td style="text-align:center;">';
			$preg3 = "SELECT fecha_pagado, saldado FROM " . $dbpfx . "destajos WHERE recibo_id = '" . $sub['recibo_id'] . "'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de destajos!".$preg3);
			$rdest = mysql_fetch_array($matr3);
			if($rdest['saldado'] < 1) {
				echo 'Por Pagar';
			} else {
				echo date('d/M/Y', strtotime($rdest['fecha_pagado']));
			}
		}
		echo '		<input type="hidden" name="sub_orden_id[' . $j . ']" value="' . $sub['sub_orden_id'] . '" />
		<input type="hidden" name="costcons[' . $j . ']" value="' . $costcons . '" />
		<input type="hidden" name="sub_consumibles[' . $j . ']" value="' . $cons . '" />
		<input type="hidden" name="sub_mo[' . $j . ']" value="' . $mo . '" />'."\n";
		echo '</td></tr>'."\n";
		$j++;
	}
	echo '	<tr><td colspan="'.$cols.'"><input type="submit" value="'. $lang['Enviar a Pre-recibo'].'" /></td></tr>'."\n";
	echo '	<tr><td colspan="'.$cols.'">&nbsp;</td></tr>'."\n";
	echo '	<tr><td colspan="'.$cols.'" style="text-align:left;"><div class="control"><a href="usuarios.php"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a Usuarios'].'" title="'. $lang['Regresar a Usuarios'].'"></a></div></td></tr>'."\n";
	echo '			</table>'."\n";
	echo '			</form>'."\n";
}

?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
