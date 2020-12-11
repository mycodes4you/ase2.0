<?php

if($accion==='valuar') {

	$pregunta2 = "SELECT orden_vehiculo_marca, orden_vehiculo_tipo, orden_vehiculo_color, orden_vehiculo_placas FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $sub_orden['orden_id'] . "'";
	$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
	$orden = mysql_fetch_array($matriz2);

	// --- ELEGIR ENCABEZADO ---
	if($sub_orden['sub_area'] == '7') {
		$encabezado = "Pintura, Consumibles y Mano de Obra.";
	} else {
		$encabezado = "Refacciones, Consumibles y Mano de Obra.";
	}

	echo '
				<form action="presupuestos.php?accion=avaluo" method="post" enctype="multipart/form-data">
					<table cellpadding="0" cellspacing="0" border="0" class="agrega">
						<tr>
							<td colspan="2" style="text-align:left !important;">
								<div class="col-md-12">
									<div class="content-box-header">
										<h2>Valuación autorizada, ' . $encabezado . ' tarea: ' . $sub_orden_id . '</h2>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align:left;">
								<big>
									<br><b>Vehículo:</b> 
									' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . $lang['Placas'] . $orden['orden_vehiculo_placas'] .'
								</big>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align:left;">
								<big>
									<b>Area:</b>' . constant('NOMBRE_AREA_' . $sub_orden['sub_area']) . '. Descripción de tarea: ' . $sub_orden['sub_descripcion'] . '
								</big>
							</td>
						</tr>'."\n";

	if($sub_orden['sub_siniestro']==='1') {
		
		echo '		
						<tr>
							<td colspan="2" style="text-align:left;"><br>
								<big>
									<b>Aseguradora:</b> <img src="' . constant('ASEGURADORA_' . $sub_orden['sub_aseguradora']) . '" alt="">
									<b>Reporte:</b> ' . $sub_orden['sub_reporte'] . '<br>
									<a href="' . DIR_DOCS . $sub_orden['sub_doc_adm'] . '" target="_blank">Orden de Admisión</a>
									<br>
								<big>	
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align:left;">
								<br><b>Monto del Deducible:</b>&nbsp;<input type="text" name="deducible" value="" />
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align:left;">
								<b>Fecha de Valuación Autorizada:</b>&nbsp;<input type="text" name="fecha_aut_val" value="" />
							</td>
						</tr>'."\n";
	}

	if($sub_orden['sub_area'] <='6' || $sub_orden['sub_area'] >='8') {
		echo '
						<tr>
							<td>
								<div class="row">
									<div class="col-md-12 panel-body" style="text-align: left !important;">
										<legend class="legend"><b>REFACCIONES:</b></legend>
										<b>instrucciones:</b>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align:left;">';

		if($sub_orden['sub_siniestro']==='1') {
			echo '
								Copia y pega desde la valuación autorizada las <span style="color:#f00; font-weight:bold;">PIEZAS SUSTITUIDAS</span><br>O agrega directamente las refacciones, una por renglón NO OLVIDE PONER EL SIMBOLO DE PESOS $ antes del precio.: 
								<br><big><span style="color:#f00; font-weight:bold;">Descripción y el Precio de Venta (si no sabe el precio colocar $0.00)</span></big><br>
								EJEMPLO:<br>
								<b>JGO 4 GUIAS FASCIA TRASERA KL12345678 $1,250.50</b>';
		} else {
			echo '
								Agrega las Refacciones, una por renglón: <br>
								<span style="color:#f00; font-weight:bold;"><big>Cantidad, Descripción, Codigo de parte si es posible y el Precio Unitario de Venta (si no sabe el precio colocar un # en lugar del precio)</span></big><br>
								EJEMPLO:<br>
								<b>1 FARO COMPLETO FC64665466 $2,650.20</b>
								<input type="hidden" name="particular" value="1" />';
		}
		echo '
							</td>
						</tr>
						<tr>
							<td colspan="2" valign="top" style="text-align:left;">
								<textarea name="audasust" cols="70" rows="13" style="background-color:#ADFFA5;" />' . $_SESSION['pres']['audasust'] . '</textarea><br>
								<!-- <img src="imagenes/piezas-sustituidas.png" alt="" /> -->
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align:left;"></td>
						</tr>'."\n";
		echo '
						<tr>
							<td>
								<div class="row">
									<div class="col-md-12 panel-body" style="text-align: left !important;">
										<legend class="legend">
											<b>CONSUMIBLES:</b>
											<small>Materiales utilizados a granel como lijas, estopas, etc.</small>
										</legend>
										<b>Instrucciones:</b>
									</div>
								</div>
							</td>
						</tr>'."\n";
			echo '
						<tr>
							<td colspan="2" style="text-align:left;">
								Agrega los consumibles, uno por renglón: <br><span style="color:#f00; font-weight:bold;"><big>Cantidad, Descripción, Código de parte si es posible y Precio Unitario  de Venta (si no sabe el precio colocar $0.00)</big></span><br>
								EJEMPLO:<br>
								<b>4 litros de liquido regrigerante KL12345678 $100.90</b>
						</td>
					</tr>
					<tr>
						<td colspan="2" valign="top" style="text-align:left;">
							<textarea name="audacons" cols="70" rows="13" style="background-color:#ADFFA5;" />' . $_SESSION['pres']['audacons'] . '</textarea>
							<!-- <br><img src="imagenes/consumibles.png" alt="" /> -->
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:left;"></td>
					</tr>'."\n";

	} elseif($sub_orden['sub_area']=='7') {
		if($sub_orden['sub_siniestro'] != '1') {
			echo '	<input type="hidden" name="particular" value="1" />';
		}
			echo '
					<tr>
						<td colspan="2" style="text-align:left;">
							<br>
							Copia y pega desde la Valuación Autorizada del apartado <span style="color:#f00; font-weight:bold;">COSTOS ADICIONALES</span> o teclea directamente lo siguiente:<br>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:left;">
							<b>Importe de Costos:</b>
							<input type="text" name="pint_precio[0]" />
							<input type="hidden" name="pint_nombre[0]" value="Pintura y otros productos" />
							<input type="hidden" name="pint_cantidad[0]" value="1" />
						</td>
					</tr>
<!--					<tr>
						<td colspan="2" style="text-align:left;">
							<b>Precio de Constante Material:</b>
							<input type="text" name="pint_precio[1]" />
							<input type="hidden" name="pint_nombre[1]" value="Constante Materiales" />
							<input type="hidden" name="pint_cantidad[1]" value="1" />
						</td>
					</tr> -->'."\n";
		echo '
					<tr>
						<td>
							<div class="row">
								<div class="col-md-12 panel-body" style="text-align: left !important;">
									<legend class="legend">
										<b>CONSUMIBLES:</b>
										<small>Materiales utilizados a granel como litros de thiner, estopas, etc.</small>
									</legend>
									<b>Instrucciones:</b>
								</div>
							</div>
						</td>
					</tr>'."\n";
		echo '		<tr>
						<td colspan="2" style="text-align:left;">
							Agrega los materiales y consumibles, uno por renglón: <br>
							<span style="color:#f00; font-weight:bold;"><big>Cantidad, Descripción, Código si es posible y Precio Unitario de Venta en caso que se vayan a facturar al cliente aparte del Importe de Costos (si no sabe el precio colocar $0.00)</big></span><br>
							EJEMPLO:<br>
							<b>0.25 Litros de thinner TH8875432 $12.50</b>
						</td>
					</tr>
					<tr>
						<td colspan="2" valign="top" style="text-align:left;">
							<textarea name="audacons" cols="70" rows="13" style="background-color:#ADFFA5;" />' . $_SESSION['pres']['audacons'] . '</textarea>
							<br><img src="imagenes/consumibles.png" alt="" />
						</td>
					</tr>'."\n";
	}

	if($sub_orden['sub_area'] <='6' || $sub_orden['sub_area'] >='8') {
		echo '
					<tr>
						<td>
							<div class="row">
								<div class="col-md-12 panel-body" style="text-align: left !important;">
									<legend class="legend"><b>MANO DE OBRA:</b></legend>
									<b>Instrucciones:</b>
								</div>
							</div>
						</td>
					</tr>'."\n";
		echo '
					<tr>
						<td colspan="2" style="text-align:left;">'."\n";
		if($sub_orden['sub_siniestro'] != '1') {
			echo '							<input type="hidden" name="particular" value="1" />'."ņ";
		}
		echo '							Copia y pega desde la valuación autorizada los <span style="color:#f00; font-weight:bold;">CONCEPTOS DE MANO DE OBRA</span> del área correspondiente o Manualmente coloca la Descripción y Precio de la Mano de Obra:<br>
							EJEMPLO:<br>
							<b>Montar y desmontar pieza $500.59</b>
							<br>Precio de Hora de Trabajo:
							<input type="text" name="preciounidad" value="';
			if($areaut[($sub_orden['sub_area'] - 1)] > 0) {
				echo $areaut[($sub_orden['sub_area'] - 1)];
			} elseif($sub_orden['sub_siniestro'] != '1') {
				echo $preciout;
			} else {
				echo $ut[$sub_orden['sub_aseguradora']];
			}
			echo '" />
						</td>
					</tr>
					<tr>
						<td colspan="2" valign="top" style="text-align:left;">
							<textarea name="audamo" cols="70" rows="13" style="background-color:#ADFFA5;" />' . $_SESSION['pres']['audamo'] . '</textarea><br>
						<img src="imagenes/desglose-mo.png" alt="" />
					</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:left;"><hr></td>
				</tr>'."\n";
	} elseif ($sub_orden['sub_area'] == '7') {
		if($sub_orden['sub_siniestro'] != '1') {
			echo '<input type="hidden" name="particular" value="1" />'."\n";
		}
			echo '
					<tr>
						<td>
							<div class="row">
								<div class="col-md-12 panel-body" style="text-align: left !important;">
									<legend class="legend"><b>MANO DE OBRA:</b></legend>
									<b>instrucciones:</b>
								</div>
							</div>
						</td>
					</tr>'."\n";
			echo '		
					<tr>
						<td colspan="2" style="text-align:left;">
							
							Agrega aquí la Mano de Obra requerida (un concepto por renglon):<br>
							<span style="color:#f00; font-weight:bold;">
								<big>Descripción Precio</big>
							</span><br>
							<b>Pintar puerta delantera derecha $500.50</b>
							
							<br>Precio de Hora de Trabajo:
							<input type="text" name="preciounidad" value="';
			if($areaut[($sub_orden['sub_area'] - 1)] > 0) {
				echo $areaut[($sub_orden['sub_area'] - 1)];
			} elseif($sub_orden['sub_siniestro'] != '1') {
				echo $preciout;
			} else {
				echo $ut[$sub_orden['sub_aseguradora']];
			}
			echo '" />
						</td>
					</tr>'."\n";
			echo '
					<tr>
						<td colspan="2" valign="top" style="text-align:left;">
							<textarea name="audamo" cols="70" rows="13" style="background-color:#ADFFA5;" />' . $_SESSION['pres']['audamo'] . '</textarea>
							<br>
							<img src="imagenes/desglose-mo.png" alt="" />
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:left;"><hr></td>
					</tr>'."\n";
	}
	$preg0 = "SELECT op_id, op_item_seg, op_codigo, op_nombre, op_cantidad, op_precio, op_tangible, prod_id, op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden['sub_orden_id'] . "' AND op_pres IS NULL AND op_tangible < '3' ORDER BY op_tangible, op_nombre";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de requeridos!");
	echo '					<tr class="cabeza_tabla">
						<td colspan="2" style="text-align:left; font-size:16px;">
							<big>Refacciones, Consumibles y Mano de Obra</big>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:left;">
							<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
								<tr>
									<td><b>Tipo</b></td>
									<td><b>Cantidad</b></td>
									<td><b>Nombre</b></td>
									<td><b>Código</b></td>
									<td><b>Precio Unitario</b></td>
									<td><b>Borrar?</b></td>
								</tr>'."\n";
	$cuenta = 0;
	while($op = mysql_fetch_array($matr0)) {
		if($op['op_tangible'] == '0') { $tipo = $lang['MO'];}
		elseif($op['op_tangible'] == '1') { $tipo = $lang['Refacción'];}
		elseif($op['op_tangible'] == '2') { $tipo = $lang['Consumible'];}
		echo '								<tr>
									<td>' . $tipo . '</td>
									<td style="text-align:center;">';
		if($sub_orden['fact_id'] > 0 || $op['op_pedido'] > 0 || ($op['op_tangible'] == '0' && $sub_orden['recibo_id'] > 0)) {
			echo $op['op_cantidad'];
			echo '<input type="hidden" name="cantp[' . $cuenta . ']" value="' . $op['op_cantidad'] . '" />';
		} else {
			echo '<input class="valua" style="text-align:right;" type="text" name="cantp[' . $cuenta . ']" value="' . $op['op_cantidad'] . '" />';
		}
		echo '</td>
									<td>' . $op['op_nombre'] . '</td>
									<td>' . $op['op_codigo'] . '</td>
									<td>';
		if($sub_orden['fact_id'] > 0 || ($op['op_tangible'] == '0' && $sub_orden['recibo_id'] > 0)) {
			echo number_format($op['op_precio'],2);
			echo '<input type="hidden" name="precio[' . $cuenta . ']" value="' . $op['op_precio'] . '" />'."\n";
		} else {
			echo '<input class="valua" style="text-align:right;" type="text" name="precio[' . $cuenta . ']" value="' . number_format($op['op_precio'],2) . '" size="6" />'."\n";
		}
		echo '										<input type="hidden" name="op_pedido[' . $cuenta . ']" value="' . $op['op_pedido'] . '" />
										<input type="hidden" name="op_id[' . $cuenta . ']" value="' . $op['op_id'] . '" /></td>
									<td>';
		if($op['op_pedido'] > 0 ) {
			echo '<a href="pedidos.php?accion=consultar&pedido="' . $op['op_pedido'] . '" target="_blank">P ' . $op['op_pedido'] . '</a>';
		} elseif($op['op_item_seg'] > 0 ) {
			echo '<a href="refacciones.php?accion=gestionar&orden_id=' . $sub_orden['orden_id'] . '#' . $op['op_id'] . '" target="_blank">E ' . $op['op_item_seg'] . '</a>';
		} elseif($op['op_tangible'] == 0 && $op['recibo_id'] > 0 ) {
			echo '<a href="recibosrh.php?accion=consultar&recibo_id=' . $op['recibo_id'] . '" target="_blank">RD ' . $op['recibo_id'] . '</a>';
		} else {
			echo '<input type="checkbox" name="borrar[' . $cuenta . ']" value="1" />';
		}
		echo '</td>
								</tr>'."\n";
		$cuenta++;
	}
	echo '							</table>
						</td>
					</tr>'."\n";

	$preg1 = "SELECT paq_id, paq_nombre FROM " . $dbpfx . "paquetes WHERE paq_area ='" . $sub_orden['sub_area'] . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Seleccionar un Paquete de Servicio</td></tr>
		<tr><td colspan="2" style="text-align:left;">
			<select name="paquete" size="1">
				<option value="">Seleccione...</option>'."\n";
	while($paqs = mysql_fetch_array($matr1)) {
		echo '				<option value="' . $paqs['paq_id'] . '">' . $paqs['paq_nombre'] . '</option>'."\n";
	}
	echo '			</select>
		</td></tr>'."\n";
// ------ Sección suspendida temporalmente -------
/*
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left; font-size:16px;"><a href="presupuestos.php?accion=cesta&sub_orden_id=' . $sub_orden_id;
	if($preaut === '1') { echo '&preaut=1'; }
	echo '"><img src="idiomas/' . $idioma . '/imagenes/refacciones.png" alt="Asignar Refacciones desde Almacén" title="Asignar Refacciones desde Almacén"> Asignar Refacciones y Mano de Obra desde Catálogo</a></td></tr>'."\n"; 
*/
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">'."\n";
		
	if($preaut === '1') { echo '<input type="hidden" name="preaut" value="1" />'."\n"; }
	echo '			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="orden_id" value="' . $sub_orden['orden_id'] . '" />
			<input type="hidden" name="aseguradora_id" value="' . $sub_orden['sub_aseguradora'] . '" />
			<input type="hidden" name="area" value="' . $sub_orden['sub_area'] . '" />
			<input type="hidden" name="sub_estatus" value="' . $sub_orden['sub_estatus'] . '" />
		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>'."\n";
	echo '	</table>
	</form>'."\n";

}

elseif(($accion==='avaluo') || ($accion==='mod_avaluo')) {
	$sub_orden_id = preparar_entrada_bd($sub_orden_id);
	$area = preparar_entrada_bd($area);
	$deducible = limpiarNumero($deducible);
	$preciout = limpiarNumero($preciounidad);
	//if($particular!='1') {$preciout = $preciounidad;}
	if((isset($audamo) && $audamo != '') || (isset($audapint) && $audapint != '')) {
		if(($preciout == '0' || $preciout == '') && $desdecesta == '' ) {
			$_SESSION['msjerror']= 'Por favor indica el precio de la de la Hora de Mano de Obra.<br>';
			redirigir('presupuestos.php?accion=valuar&sub_orden_id=' . $sub_orden_id);
		}
	}


// ---- Procesar Bloques de Texto ----

	if($audasust != '') {
		$caja['audaprod'] = $audasust;
	}
	if($audacons != '') {
		$caja['audaconsumible'] = $audacons;
	}
	if($audamo != '') {
		$caja['audaobr'] = $audamo;
	}
	
	

// ------ Nos conectamos a ASEBase para determinar si las partes y MO pertenecen a esta área o a otra ---
	mysql_close(); 
	mysql_connect("localhost", $dbusuario, $dbclave) or die ('Falló la conexion a la DB. 470');
	mysql_select_db("ASEBase") or die ("Base de datos no encontrada.");

	foreach($caja as $bloque => $texto) {
		$audasust2 = preg_split("/[\n]+/", $texto);
		$pesos = 0; $descripcion = ''; $precioar = ''; $cantidad = '';
//		echo $bloque;
		foreach ($audasust2 as $i => $v) {
			for($j = 0; $j < strlen($v); $j++) {
				if($v[$j] == '$') {
					// --- Localizar precio de parte para determinar el fin de línea.
					$pesos = 1;
					if($j > 0) {
						$descripcion .= substr($v, 0, $j-1);
						$descripcion = $descripcion . ' ';
					}
				} elseif((is_numeric($v[$j]) || ord($v[$j]) == 44 || ord($v[$j]) == 46) && $pesos == 1) {
					// --- Después de localizar $, si el siguiente es número, se guarda como precio.
					if(ord($v[$j]) != 44) {
						$precioar = $precioar . $v[$j];
					}
				} elseif($pesos == 1 && $precioar != '') {
					// --- Después de guardar precio, al siguiente caracter que ya no es parte del precio, se da por terminada la línea
					if(($particular == 1 && $bloque == 'audaprod') || $bloque == 'audaconsumible') {
						// --- Si se debe obtener cantidades, debe ir por aca ---
						for($n = 0; $n < strlen($descripcion); $n++) {
							if(is_numeric($descripcion[$n]) || $descripcion[$n] == '.') {
								$cantidad .= $descripcion[$n];
							} elseif($cantidad != '' && !is_numeric($descripcion[$n]) && $descripcion[$n] != '.') {
								// --- limpia números del principio de la descripción ---
								$describe = substr($descripcion, $n);
								break;
							}
						}
					} else {
						// --- Limpia números del principio de la descripción ---
						for($n = 0; $n < strlen($descripcion); $n++) {
							if(!is_numeric($descripcion[$n]) && $descripcion[$n] != '.' && ord($descripcion[$n]) != 32 && ord($descripcion[$n]) != 13) {
								$describe = substr($descripcion, $n);
								break;
							}
						}
					}
					// --- Guarda la descripción obtenida de las concatenaciones y el precio, creando así un nuevo renglón ---
					if($bloque == 'audaprod') {
						$audaprod[] = ['0' => trim($describe), '1' => $precioar];
						if($valor['determina_area'][0] != 1 && ($area == 1 || $area == 6)) {
							// --- Si $determina_area es diferente de 1 y el area en curso es 1 o 6 , se aplica el algoritmo para determinar a que área pertenece ---
							$audaprod[$i][5] = determina_area(trim($descripcion));
						}
					} elseif($bloque == 'audaobr') {
						$audaobr[] = ['0' => trim($describe), '1' => $precioar];
						if($valor['determina_area'][0] != 1 && ($area == 1 || $area == 6)) {
							// --- Si $determina_area es diferente de 1 y el area en curso es 1 o 6 , se aplica el algoritmo para determinar a que área pertenece ---
							$audaobr[$i][5] = determina_area(trim($descripcion));
						}
					} else {
						$audaconsumible[] = ['0' => trim($describe), '1' => $precioar, '4' => $cantidad];
					}
					$descripcion = '';
					$precioar = '';
					$cantidad = '';
					break;
				} elseif(!is_numeric($v[$j]) && $pesos == 1) {
					// --- Si encuentra $ pero luego de éste no hay números, se deduce que no era del precio y se continua con la búsqueda del precio ---
					$pesos = 0;
				}
			}
			if($pesos == 0) {
				// --- Si no se encontró el precio en esta línea, concatenar con la siguiente linea para formar la descripción ---
				$descripcion .= $v;
				$descripcion = $descripcion . ' ';
			} else {
				// --- Se reinicia la busqueda del fin de la siguiente línea.
				$pesos = 0;
			}
		}
	}

// ------ Restablecemos conexión a la Base de Datos de la instancia
	mysql_close();
	mysql_connect($servidor,$dbusuario,$dbclave)  or die ('Falló la conexion a la DB. 681');
	mysql_select_db($dbnombre) or die('Falló la seleccion la DB');

	$error = 'no';
	$mensaje= '';
	$parametros='sub_orden_id = ' . $sub_orden_id;

	if(is_array($audaconsumible) && $audaconsumible[0][0] != '') {
		for($i=0;$i<=count($audaconsumible);$i++) {
			if($audaconsumible[$i][4] == '0') {
				$error = 'si';
				$mensaje .= 'No se agregó cantidad en uno de los consumibles.<br>';
			}
		}
	}

	if(is_array($audaprod) && $audaprod[0][0] != ''){
		for($i=0;$i<=count($audaprod);$i++){
			if($particular == '1' && $audaprod[$i][4] == '0'){
				$error = 'si';
				$mensaje .= 'No se agregó cantidad en una de las refacciones en trabajo particular.<br>';
			}
		}
	}

	if(is_array($audaobr) && $audaobr[0][0] != '') {
		$vermo = 0;
		for($i=0;$i<=count($audaobr);$i++) {
			$vermo = $vermo + $audaobr[$i][1];
		}
		if($vermo == '0') {
			$error = 'si';
			$mensaje .= 'No se agregó precio en mano de obra.<br>';
		}
	}
	// ---- Termina de verificación de errores ----

	// ---- procesamiento de arrays generados

	if($error === 'no' && (is_array($prod_id) || isset($paquete) || is_array($_SESSION['prods']['id']) || is_array($audaprod) || is_array($audaobr) || is_array($audap) || is_array($op_id) || is_array($audaconsumible) )) {

		if (is_array($op_id)) {
			foreach($op_id as $i => $ii) {
				if($precio[$i] != '' || $cantp[$i] != '') { // --- Actualizar cantidad y/o precio del producto ----
					unset($sql_data_array);
					$cantidad_limpia = limpiarNumero($cantp[$i]);
					$precio_limpio = limpiarNumero($precio[$i]);
					if($precio_limpio > 0) { $sql_data_array['op_precio'] = $precio_limpio; }
					if($cantidad_limpia > 0) { $sql_data_array['op_cantidad'] = $cantidad_limpia; }
					$param = " op_id = '" . $op_id[$i] . "'";
					ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $param);
					unset($sql_data_array);
					$utilped[$op_pedido[$i]] = 1;
				}
				if(isset($borrar[$i]) && $borrar[$i]=='1') {
					$pregp = "SELECT op_id, prod_id, op_cantidad, op_nombre FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op_id[$i] . "' AND op_pedido < '1' AND op_surtidos < '0.0001'";
					$matrp = mysql_query($pregp);
					$regr = mysql_fetch_array($matrp);
					if($regr['prod_id'] > 0) {
						$suma = "UPDATE " . $dbpfx . "productos SET prod_cantidad_disponible = prod_cantidad_disponible + " . $regr['op_cantidad'] . " WHERE prod_id = '" . $regr['prod_id'] . "'";
						$resultado = mysql_query($suma) or die("ERROR: no se actualizaron los productos!");
						$archivo = '../logs/' . time() . '-base.ase';
						$myfile = file_put_contents($archivo, $suma . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
					}
					$parme = " op_id = '" . $op_id[$i] . "' AND op_pedido < '1' ";
					ejecutar_db($dbpfx . 'orden_productos', $sqdat, 'eliminar', $parme);
					if($qv_activo == 1 && $regr['op_id'] == $op_id[$i]) {
						$xmlitem .= '			<Ref op_id="' . $op_id[$i] . '" op_estatus="90" />'."\n";
					}
					bitacora($orden_id, 'Se eliminó de la valuación la parte ' . $op_id[$i] . ' ' . $regr['op_nombre'], $dbpfx);
				}
			}
		}

// ------ Actualizar utilidad de pedido ------
		foreach($utilped as $uk => $uv) {
			$actutilped = recalcUtilPed($uk, $dbpfx);
		}

// ------ BÚSQUEDA DE REFACCIONES Y MANO DE OBRA DE LA SUBORDEN ---
		$refacciones=0;
		$preg1 = "SELECT prod_id, op_id, op_nombre, op_cantidad, op_precio, op_descuento, op_tangible, op_estructural, op_recibidos, op_autosurtido, op_pres FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "' AND op_tangible < '3' AND op_pres IS NULL";
		//echo $preg1;
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden_productos!");

		$autosurtido = $asurt[$aseguradora_id];

		$sub_partes = 0; $sub_consumibles = 0; $sub_mo = 0; $presupuesto = 0;
		while($op = mysql_fetch_array($matr1)) {
			$op_subtotal = round(($op['op_cantidad'] * ($op['op_precio'] - $op['op_descuento'])), 2);
			$sql_data_array['op_subtotal'] = $op_subtotal;
			$param = " op_id = '" . $op['op_id'] . "'";
			ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $param);
			if($op['op_tangible']=='1' && ($autosurtido == 1 || $particular == 1 || $op['op_autosurtido']=='2'|| $op['op_autosurtido']=='3')) {
				$sub_partes = $sub_partes + $op_subtotal;
				$presupuesto = $presupuesto + $op_subtotal;
			} elseif($op['op_tangible']=='2') {
				$sub_consumibles = $sub_consumibles + $op_subtotal;
				$presupuesto = $presupuesto + $op_subtotal;
			} elseif($op['op_tangible']=='0') {
				$sub_mo = $sub_mo + $op_subtotal;
				$presupuesto = $presupuesto + $op_subtotal;
				$tiempo = $tiempo + $op['op_cantidad'];
			}
			//echo $op_subtotal . '<br>';
			
			if($op['op_cantidad'] > $op['op_recibidos'] && $op['op_tangible']=='1' && $op['op_pres']!='1') { 
				if($op['op_estructural']==1) { $refacciones=2;}
				elseif($refacciones==0) { $refacciones=1; } 
			}
		}
		unset($sql_data_array);

// ------ DETERMINACIÓN DE NÚMERO DE ITEM ---

		$preg6 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id'";
		$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de subordenes items!");
		$item = 1;
		while($dato6 = mysql_fetch_array($matr6)) {
			$preg5 = "SELECT op_item FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $dato6['sub_orden_id'] . "' ORDER BY op_item DESC LIMIT 1";
			$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden_productos!");
			$dato5 = mysql_fetch_array($matr5);
			if($dato5['op_item'] >= $item) {$item = $dato5['op_item'] + 1;}
		}

//--------------  Fin de determinación de número de Item	--------------------

		if (is_array($prod_id)) {
			for($i=0;$i<count($prod_id);$i++) {
				if($prod_cantidad[$i]!='') {
					//$preg1 = "SELECT prod_cantidad_disponible FROM " . $dbpfx . "productos WHERE prod_id = '" . $prod_id[$i] . "'";
					//$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de op_prods!");
					//$op = mysql_fetch_array($matr1);
  					if($prod_cantidad[$i] > $prod_disponible[$i]) { $refacciones=1; }
					if($prod_tangible[$i]=='1') {
						$prod_cantidad[$i] = intval($prod_cantidad[$i]);
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_partes = $sub_partes + $op_subtotal;
					} elseif($prod_tangible[$i]=='2') {
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_consumibles = $sub_consumibles + $op_subtotal;
					} else {
						if($sub_aseguradora > 1) {
							$op_subtotal= $prod_cantidad[$i] * $ut[$sub_aseguradora];
						} else {
							$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						}
						$sub_mo = $sub_mo + $op_subtotal;
						$tiempo = $tiempo + $prod_cantidad[$i];
					}
					$presupuesto = $presupuesto + $op_subtotal;
					$sql_data_array = array('sub_orden_id' => $sub_orden_id,
						'op_area' => $area,
						'op_item' => $item,
						'prod_id' => $prod_id[$i],
						'op_nombre' => $prod_nombre[$i],
						'op_codigo' => $prod_codigo[$i],
						'op_tangible' => $prod_tangible[$i],
						'op_precio' => $prod_precio[$i],
						'op_costo' => $prod_costo[$i],
						'op_subtotal' => $op_subtotal);
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
					$item++;
					if($prod_disponible[$i] >= $prod_cantidad[$i]) {
						$param = "op_id = '$nueva_id'";
						$sql_data_array = array('op_reservado' => $prod_cantidad[$i],
							'op_cantidad' => $prod_cantidad[$i],
							'op_recibidos' => $prod_cantidad[$i],
							'op_ok' => '1',
							'op_autosurtido' => '2',
							'op_fecha_promesa' => date('Y-m-d H:i:s', time()));
						ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $param);
					} elseif($refacciones == 1 && $prod_disponible[$i] == 0) {
						$param = "op_id = '$nueva_id'";
						$sql_data_array = array('op_cantidad' => $prod_cantidad[$i],
							'op_autosurtido' => '2');
						ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $param);
					} else {
						$param = "op_id = '$nueva_id'";
						$sql_data_array = array('op_reservado' => $prod_disponible[$i],
							'op_cantidad' => $prod_disponible[$i],
							'op_recibidos' => $prod_disponible[$i],
							'op_ok' => '1',
							'op_autosurtido' => '2',
							'op_fecha_promesa' => date('Y-m-d H:i:s', time()));
						ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $param);
						$nvacant = $prod_cantidad[$i] - $prod_disponible[$i];
						$sql_data_array = array('sub_orden_id' => $sub_orden_id,
							'op_area' => $area,
							'op_item' => $item,
							'prod_id' => $prod_id[$i],
							'op_nombre' => $prod_nombre[$i],
							'op_cantidad' => $nvacant,
							'op_codigo' => $prod_codigo[$i],
							'op_tangible' => $prod_tangible[$i],
							'op_precio' => $prod_precio[$i],
							'op_autosurtido' => '2',
							'op_subtotal' => $op_subtotal);
						$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
						$item++;
					}

					if($prod_tangible[$i] > 0) {
						$pregup = "SELECT prod_cantidad_disponible FROM " . $dbpfx . "productos WHERE prod_id = '" . $prod_id[$i] . "'";
						$matrup = mysql_query($pregup);
						$up = mysql_fetch_array($matrup);
						$disp = $up['prod_cantidad_disponible'] - $prod_cantidad[$i];
						$parme = " prod_id = '" . $prod_id[$i] . "'";
						$sqdat = ['prod_cantidad_disponible' => $disp];
						ejecutar_db($dbpfx . 'productos', $sqdat, 'actualizar', $parme);
						unset($sqdat);
						
//						$resta = "ACTUALIZAR " . $dbpfx . "productos SET prod_cantidad_disponible = prod_cantidad_disponible - " . $prod_cantidad[$i] . " WHERE prod_id = '" . $prod_id[$i] . "'";
//						$resultado = mysql_query($resta) or die("ERROR: no se actualizaron los productos!");
					}
				}
			}
		}

		if (isset($paquete) && $paquete!='') {
			$preg3 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE sub_orden_id='" . $sub_orden_id . "' AND sub_estatus < '112'";
			$matr3 = mysql_query($preg3) or die($preg3);
			$reporte = mysql_fetch_array($matr3);
			$preg4 = "SELECT sub_orden_id, sub_area FROM " . $dbpfx . "subordenes WHERE sub_reporte='" . $reporte['sub_reporte'] . "' AND sub_estatus < '112'";
			$matr4 = mysql_query($preg4) or die($preg4);
			while($tarea = mysql_fetch_array($matr4)) {
				$preg0 = "SELECT pc_prod_id, pc_prod_cant, pc_area_id FROM " . $dbpfx . "paq_comp WHERE pc_paq_id='" . $paquete . "' AND pc_activo = '1' AND pc_area_id = '" . $tarea['sub_area'] . "'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de paq_prods!");
				while($paqs = mysql_fetch_array($matr0)) {
					$preg1 = "SELECT prod_codigo, prod_nombre, prod_tangible, prod_precio FROM " . $dbpfx . "productos WHERE prod_id='" . $paqs['pc_prod_id'] . "'";
//			echo $preg1.'<br>';
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
							'op_subtotal' => $op_subtotal);
						$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
						$item++;
					}
				}
			}
		}

		if (is_array($audaprod)) {
			$op_subtotal = 0;
			for($i=0;$i<=count($audaprod);$i++) {
				if(($audaprod[$i][0] != '') && ($audaprod[$i][1] != '')) {
					if($particular == '1') {
						$cant1 = $audaprod[$i][4];
					} else {
						$cant1 = '1'; 
					}

					if($audaprod[$i][2]==1) {
						$refacciones = 2;
					} elseif($refacciones == 0) {
						$refacciones = 1;
					}

					$op_subtotal = $cant1 * ($audaprod[$i][1] - $descuento);
					if($audaprod[$i][5] > 0 && $audaprod[$i][5] != $area) {
						// --- Este Item se agregara en otra tarea ---
						$prods_faltantes[] = [
							'op_area' => $audaprod[$i][5],
							'op_nombre' => $audaprod[$i][0],
							'op_codigo' => $audaprod[$i][3],
							'op_cantidad' => $cant1,
							'op_precio' => $audaprod[$i][1],
							'op_subtotal' => $op_subtotal,
							'op_tangible' => 1,
							'op_estructural' => $audaprod[$i][2]
						];
					} else {
						// --- Se continua insertando en esta Tarea ---
						$sql_data_array3 = array(
							'sub_orden_id' => $sub_orden_id,
							'op_area' => $audaprod[$i][5],
							'op_item' => $item,
							'op_nombre' => $audaprod[$i][0],
							'op_codigo' => $audaprod[$i][3],
							'op_cantidad' => $cant1,
							'op_precio' => $audaprod[$i][1],
							'op_subtotal' => $op_subtotal,
							'op_tangible' => 1,
							'op_estructural' => $audaprod[$i][2]
						);
						if(($autosurtido=='1' || $particular == '1') && $bloqueaprecio == '0') {
							$sub_partes = $sub_partes + $op_subtotal;
							$presupuesto = $presupuesto + $op_subtotal;
						}
						$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array3, 'insertar');
						$item++;
						if($qv_activo == 1) {
							$xmlitem .= '			<Ref op_id="' . $nueva_id . '" op_cantidad="' . $cant1 . '" op_nombre="' . $audaprod[$i][0] . '" op_codigo="' . $audaprod[$i][3] . '" op_estatus="10" />'."\n";
						}
						if($audaprod[$i][5] == 0){ // --- Si el área fue 0 se agrega a la lista de refacciones no identificadas ---
							mysql_close(); // --- cerramos conexion actual --- 
							mysql_connect("localhost", $dbusuario, $dbclave) or die ('Falló la conexion a la DB. 1065');
							mysql_select_db("ASEBase") or die ("Base de datos no encontrada.");
							$consulta = "INSERT INTO no_identificados (`op_id`,`instancia`,`concepto`) VALUES ('" . $nueva_id . "','" . $nombre_agencia . "','" . $audaprod[$i][0] . "')";
							$resultado = mysql_query($consulta) or die("Falló insersión a ASEBase: " . $consulta);
							mysql_close(); // --- cerramos conexion a ASE BASE --- 
							// ---- REABRIMOS CONEXIONA LA BASE DE DATOS DE LA INSTANCIA ---- 
							mysql_connect($servidor,$dbusuario,$dbclave)  or die ('Falló la conexion a la DB. 1076');
							mysql_select_db($dbnombre) or die('Falló la seleccion la DB');
						}
						
					}
				}
			}
		}

		if (is_array($audaconsumible)) {
			$op_subtotal = 0;
			for($i=0;$i<=count($audaconsumible);$i++) {
				if(($audaconsumible[$i][0] != '') && ($audaconsumible[$i][1] != '')) {
					$cant1 = $audaconsumible[$i][4];
					$op_subtotal = round(($audaconsumible[$i][1] * $cant1), 6);
					$sql_data_array3 = array('sub_orden_id' => $sub_orden_id,
						'op_area' => $area,
						'op_item' => $item,
						'op_nombre' => $audaconsumible[$i][0],
						'op_cantidad' => $cant1,
						'op_precio' => $audaconsumible[$i][1],
						'op_subtotal' => $op_subtotal,
						'op_tangible' => '2');
					if(($autosurtido=='1' || $particular == '1') && $bloqueaprecio == '0') {
						$sub_consumibles = $sub_consumibles + $op_subtotal;
						$presupuesto = $presupuesto + $op_subtotal;
					}
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array3, 'insertar');
					$item++;
				}
			}
		}

		if (is_array($audaobr)) {
			for($i=0;$i<=count($audaobr);$i++) {
				if(($audaobr[$i][0]!='') && ($audaobr[$i][1]!='')) {
					$cant1 = round(($audaobr[$i][1] / $preciout), 6);
					if($audaobr[$i][5] != $area && $audaobr[$i][5] > 0) {
						// --- Este Item se agregara en otra tarea ---
						if($cant1 < 0) { $preciout = $preciout * -1; }
						$prods_faltantes[] = [
							'op_area' => $audaobr[$i][5],
							'op_nombre' => $audaobr[$i][0],
							'op_tangible' => 0,
							'op_cantidad' => $cant1,
						];
						if($bloqueaprecio == '0') {
							$op_subtotal= round(($cant1 * $preciout), 2);
							$sub_mo = $sub_mo + $op_subtotal;
							$presupuesto = $presupuesto + $op_subtotal;
							$prods_faltantes[$i]['op_precio'] = $preciout;
							$prods_faltantes[$i]['op_subtotal'] = $op_subtotal;
						}
					} else {
						$tiempo = $tiempo + $cant1;
						if($cant1 < 0) { $preciout = $preciout * -1; }
						$sql_data_array4 = array(
							'sub_orden_id' => $sub_orden_id,
							'op_area' => $audaobr[$i][5],
							'op_item' => $item,
							'op_nombre' => $audaobr[$i][0],
							'op_tangible' => 0,
							'op_cantidad' => $cant1);
						if($bloqueaprecio == '0') {
							$op_subtotal= round(($cant1 * $preciout), 2);
							$sub_mo = $sub_mo + $op_subtotal;
							$presupuesto = $presupuesto + $op_subtotal;
							$sql_data_array4['op_precio'] = $preciout;
							$sql_data_array4['op_subtotal'] = $op_subtotal;
						}
						$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array4, 'insertar');
						$item++;
						if(isset($audaobr[$i][5]) && $audaobr[$i][5] == 0) {
							// --- Si el área fue 0 se agrega a la lista de refacciones no identificadas ---
							mysql_close(); // --- cerramos conexion actual --- 
							mysql_connect("localhost", $dbusuario, $dbclave) or die ('Falló la conexion a la DB. 1189');
							mysql_select_db("ASEBase") or die ("Base de datos no encontrada.");
							$consulta = "INSERT INTO no_identificados (`op_id`,`instancia`,`concepto`) VALUES ('" . $nueva_id . "','" . $nombre_agencia . "','" . $audaobr[$i][0] . "')";
							$resultado = mysql_query($consulta) or die("Falló insersión a ASEBase: " . $consulta);
							mysql_close(); // --- cerramos conexion a ASE BASE --- 
							// ---- REABRIMOS CONEXIONA LA BASE DE DATOS DE LA INSTANCIA ---- 
							mysql_connect($servidor,$dbusuario,$dbclave)  or die ('Falló la conexion a la DB. 1200');
							mysql_select_db($dbnombre) or die('Falló la seleccion la DB. 1201');
						}
					}
				}
			}
		}

		if (is_array($pint_nombre)) {
			for($i=0;$i<count($pint_nombre);$i++) {
				if ($pint_precio[$i]!='') {
					$sql_data_array = array('sub_orden_id' => $sub_orden_id,
						'op_area' => $area,
						'op_item' => $item,
						'op_nombre' => $pint_nombre[$i],
						'op_cantidad' => $pint_cantidad[$i],
						'op_tangible' => 2);
					if($bloqueaprecio == '0') {
						$pint_precio[$i] = limpiarNumero($pint_precio[$i]);
						$op_subtotal= $pint_cantidad[$i] * $pint_precio[$i];
						$presupuesto = $presupuesto + $op_subtotal;
						$sub_consumibles = $sub_consumibles + $op_subtotal;
						$sql_data_array['op_precio'] = $pint_precio[$i];
						$sql_data_array['op_subtotal'] = $op_subtotal;
					}
//					if($i == 0) { $sql_data_array['prod_id'] = '11'; } else { $sql_data_array['prod_id'] = '12'; }
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
					$item++;
				}
			}
		}

		if (is_array($audap)) {
			for($i=0;$i<=count($audap);$i++) {
				if(($audap[$i][0]!='') && ($audap[$i][1]!='')) {
					if($c_pint[$aseguradora_id] == '1') { 
						$cantmo = $audap[$i][1] / $preciout; 
						$tiempo = $tiempo + ($audap[$i][1] / 100);
					} else {
						$cantmo = $audap[$i][1] / 10;
						$tiempo = $tiempo + ($audap[$i][1] / 10);
						if($preciout < 0) { $cantmo = $cantmo * -1; }
					}
					if($preciout < 0) { $preciout = $preciout * -1; }
					$sql_data_array4 = array('sub_orden_id' => $sub_orden_id,
//						'prod_id' => '7',
						'op_area' => $area,
						'op_item' => $item,
						'op_nombre' => $audap[$i][0],
						'op_tangible' => 0,
						'op_cantidad' => $cantmo);
					if($bloqueaprecio == '0') {
						$op_subtotal= $cantmo * $preciout;
						$sub_mo = $sub_mo + $op_subtotal;
						$presupuesto = $presupuesto + $op_subtotal;
						$sql_data_array4['op_precio'] = $preciout;
						$sql_data_array4['op_subtotal'] = $op_subtotal;
					}
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array4, 'insertar');
					$item++;
				}
			}
		}

		if($qv_activo == 1 && isset($xmlitem)) {
			// ------ Si QV está activo, genera el encabezado del XML para agregar cotizaciones
			$veh = datosVehiculo($orden_id, $dbpfx);
			$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
			$xml .= '		<Solicitud tiempo="' . microtime() . '">10</Solicitud>'."\n";
			$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'">'."\n";
			$xml .= $xmlitem;
			$xml .= '		</OT>'."\n";
			$xml .= '	</Comprador>'."\n";
			$mtime = substr(microtime(), (strlen(microtime())-3), 3);
			$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.xml';
			file_put_contents("../qv-salida/".$xmlnom, $xml);
			unset($xmlitem);
		}

		$horas = intval($tiempo);
		$minutos = round((($tiempo - $horas)*60), 2);
		if($minutos==0) {$minutos='00';}
		$programadas = $horas . ':' . $minutos;
		$sql_data_array = array('sub_presupuesto' => $presupuesto,
			'sub_partes' => $sub_partes,
			'sub_consumibles' => $sub_consumibles,
			'sub_mo' => $sub_mo,
			'sub_valuador' => $_SESSION['usuario'],
			'sub_deducible' => $deducible,
			'sub_fecha_valaut' => $fecha_aut_val,
			'sub_fecha_presupuesto' => date('Y-m-d H:i:s'),
			'sub_horas_programadas' => $programadas);
		if($pidepres != '1') { $sql_data_array['sub_refacciones_recibidas'] = $refacciones; }
		if($sub_estatus < '104' || $sub_estatus == '120' || $sub_estatus == '128' || $sub_estatus == '129') {
			$sql_data_array['sub_estatus'] = '102';
		}
		$parametros='sub_orden_id = ' . $sub_orden_id;
		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		if($deducible > '0' && $siniestro != '0') {
			$parametros="orden_id = '" . $orden_id . "' AND sub_reporte = '" . $siniestro . "'";
			$sql_data_array = array('sub_deducible' => $deducible);
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		}
		unset($sql_data_array);
		
		bitacora($orden_id, 'Se actualizó la valuación de la Tarea ' . $sub_orden_id, $dbpfx);
		unset($_SESSION['pres']['sub_orden_id']);

		actualiza_orden ($orden_id, $dbpfx);
		unset($_SESSION['pres']);

		// ################## Procesar el array de faltantes ####################
		if($determina_area != 1 && is_array($prods_faltantes) && ($area == 1 || $area == 6)) {
			// --- Procesando productos de otras areas ---
			$preg_suborden = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub_orden_id . "'";
			$matr_suborden = mysql_query($preg_suborden) or die('Fallo: ' . $preg_suborden);
			$tarea_actual = mysql_fetch_assoc($matr_suborden);
			// --- Definir a qué área se agregaran los faltantes
			if($area == 1) {
				$area = 6;
			} else{
				$area = 1;
			}
			// --- consultar si hay tareas disponibles (SIN DESTAJOS PAGADOS, NI FACTURADAS, NI DESCUENTOS) para agregar faltantes ---
			$preg3 = "SELECT sub_orden_id, sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE sub_area = '" . $area . "' AND sub_estatus < '130' AND orden_id = '" . $tarea_actual['orden_id'] . "' AND sub_reporte = '" . $tarea_actual['sub_reporte'] . "' AND sub_aseguradora = '" . $tarea_actual['sub_aseguradora'] . "' AND fact_id IS NULL AND recibo_id IS NULL AND sub_descuento IS NULL LIMIT 1";
			$matr3 = mysql_query($preg3) or die ('ERROR: no se logro abrir subordenes! ' . $preg3);
			$fila3 = mysql_num_rows($matr3);
			$sub_partes = 0; $sub_consumibles = 0; $sub_mo = 0; $presupuesto = 0;
			if($fila3 == 1) {
				// --- SI SE ENCUENTRA TAREA DISPONIBLE, AQUÍ SE AGREGAN LOS FALTANTES ---
				$tarea_candidata = mysql_fetch_assoc($matr3);
				// ---- BÚSQUEDA DE REFACCIONES Y MANO DE OBRA DE LA SUBORDEN ----
				$refacciones=0;
				$preg1 = "SELECT prod_id, op_nombre, op_cantidad, op_precio, op_descuento, op_tangible, op_estructural, op_recibidos, op_autosurtido, op_pres FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $tarea_candidata['sub_orden_id'] . "' AND op_tangible < '3' AND op_pres IS NULL";
				//echo $preg1;
				$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden_productos!");
				while($op = mysql_fetch_array($matr1)) {
					$op_subtotal = round(($op['op_cantidad'] * ($op['op_precio'] - $op['op_descuento'])), 2);
					if($op['op_tangible']=='1' && ($autosurtido == 1 || $particular == 1 || $op['op_autosurtido']=='2'|| $op['op_autosurtido']=='3')) {
						$sub_partes = $sub_partes + $op_subtotal;
						$presupuesto = $presupuesto + $op_subtotal;
					} elseif($op['op_tangible']=='2') {
						$sub_consumibles = $sub_consumibles + $op_subtotal;
						$presupuesto = $presupuesto + $op_subtotal;
					} elseif($op['op_tangible']=='0') {
						$sub_mo = $sub_mo + $op_subtotal;
						$presupuesto = $presupuesto + $op_subtotal;
						$tiempo = $tiempo + $op['op_cantidad'];
					}
					//echo $op_subtotal . '<br>';
					if($op['op_cantidad'] > $op['op_recibidos'] && $op['op_tangible']=='1' && $op['op_pres']!='1') { 
						if($op['op_estructural']==1) { $refacciones=2;}
						elseif($refacciones==0) { $refacciones=1; } 
					}
				}
				$sub_orden_id = $tarea_candidata['sub_orden_id'];
			} else {
				// --- Se crea una tarea nueva que herede de la tarea actual ---
				$sql_data_array = [
					'orden_id' => $tarea_actual['orden_id'],
					'sub_area' => $area,
					'sub_descripcion' => $tarea_actual['sub_descripcion'],
					'sub_estatus' => '102',
					'sub_siniestro' => $tarea_actual['sub_siniestro'],
					'sub_reporte' => $tarea_actual['sub_reporte'],
					'sub_poliza' => $tarea_actual['sub_poliza'],
					'sub_aseguradora' => $tarea_actual['sub_aseguradora'],
					'sub_paga_deducible' => $tarea_actual['sub_paga_deducible'],
					'sub_deducible' => $tarea_actual['sub_deducible'],
					'sub_dedu_cobrado' => $tarea_actual['sub_dedu_cobrado'],
					'sub_dedu_fecha' => $tarea_actual['sub_dedu_fecha'],
				];
				$sub_orden_id = ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
			}

			foreach($prods_faltantes as $key => $val) {
				$sql_data_array3 = [
						'sub_orden_id' => $sub_orden_id,
						'op_area' => $val['op_area'],
						'op_item' => $item,
						'op_nombre' => $val['op_nombre'],
						'op_codigo' => $val['op_codigo'],
						'op_cantidad' => $val['op_cantidad'],
						'op_precio' => $val['op_precio'],
						'op_subtotal' => $val['op_subtotal'],
						'op_tangible' => $val['op_tangible'],
						'op_estructural' => $val['op_estructural']
				];
				if(($autosurtido=='1' || $particular == '1') && $bloqueaprecio == '0') {
					if($val['op_tangible'] == '1') {
						$sub_partes = $sub_partes + $val['op_subtotal'];
					} else {
						$sub_mo = $sub_mo + $val['op_subtotal'];
					}
					$presupuesto = $presupuesto + $val['op_subtotal'];
				}
				$item++;
				$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array3, 'insertar');
				if($val['op_tangible'] == 1 && $qv_activo == 1) {
					// --- Cotizar refacciones ---
					$xmlitem .= '			<Ref op_id="' . $nueva_id . '" op_cantidad="' . $val['op_cantidad'] . '" op_nombre="' . $val['op_nombre'] . '" op_codigo="' . $val['op_codigo'] . '" op_estatus="10" />'."\n";
				}
			}

			if($qv_activo == 1 && isset($xmlitem)) {
				// ------ Si QV está activo, genera el encabezado del XML para agregar cotizaciones
				$veh = datosVehiculo($orden_id, $dbpfx);
				$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
				$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
				$xml .= '		<Solicitud tiempo="' . microtime() . '">10</Solicitud>'."\n";
				$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'">'."\n";
				$xml .= $xmlitem;
				$xml .= '		</OT>'."\n";
				$xml .= '	</Comprador>'."\n";
				$mtime = substr(microtime(), (strlen(microtime())-3), 3);
				$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.xml';
				file_put_contents("../qv-salida/".$xmlnom, $xml);
				unset($xmlitem);
			}

			$horas = intval($tiempo);
			$minutos = round((($tiempo - $horas)*60), 2);
			if($minutos==0) {$minutos='00';}
			$programadas = $horas . ':' . $minutos;
			$sql_data_array = [
				'sub_presupuesto' => $presupuesto,
				'sub_partes' => $sub_partes,
				'sub_consumibles' => $sub_consumibles,
				'sub_mo' => $sub_mo,
				'sub_valuador' => $_SESSION['usuario'],
				'sub_fecha_presupuesto' => date('Y-m-d H:i:s'),
				'sub_horas_programadas' => $programadas
			];
			if($pidepres != '1') { $sql_data_array['sub_refacciones_recibidas'] = $refacciones; }
			if($sub_estatus < '104' || $sub_estatus == '120' || $sub_estatus == '128' || $sub_estatus == '129') {
				$sql_data_array['sub_estatus'] = '102';
			}
			$parametros='sub_orden_id = ' . $sub_orden_id;
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
			unset($sql_data_array);
			bitacora($orden_id, 'Se actualizó la valuación de la Tarea ' . $sub_orden_id, $dbpfx);
			unset($_SESSION['pres']['sub_orden_id']);
			actualiza_suborden ($orden_id, $area, $dbpfx);
			actualiza_orden ($orden_id, $dbpfx);
			unset($_SESSION['pres']);
		}
		redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror']= $mensaje;
		redirigir('presupuestos.php?accion=valuar&sub_orden_id=' . $sub_orden_id);
	}	
}

elseif($accion==='presupuestar') {
	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  	$sub_orden = mysql_fetch_array($matriz);

//	echo 'Estamos en la sección valuar';

	echo '	<form action="presupuestos.php?accion=presupuesto" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	$veh = datosVehiculo($sub_orden['orden_id'], $dbpfx);
	echo '		<tr><td colspan="2" style="text-align:left;">' . $veh['completo'] . '	</td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left;">Productos, Materiales y Mano de Obra a presupuestar para la Tarea: <span style="font-size:16px; font-weight:bold;">' . constant('NOMBRE_AREA_' . $sub_orden['sub_area']) . '</span></td></tr>'."\n";
	if($sub_orden['sub_siniestro']==='1') {
		echo '		<tr><td colspan="2" style="text-align:left;">Aseguradora: <img src="' . constant('ASEGURADORA_' . $sub_orden['sub_aseguradora']) . '" alt=""><br>Reporte: ' . $sub_orden['sub_reporte'] . '</td></tr>';
	}
	if($sub_orden['sub_siniestro'] !='1') { echo '<input type="hidden" name="particular" value="1" />'; }
	echo '		<tr><td colspan="2" style="text-align:left;">Descripción de tarea: <span style="font-size:16px; font-weight:bold;">' . $sub_orden['sub_descripcion'] . '</span><br><br></td></tr>'."\n";

// ------ Captura de Refacciones ------
	echo '						<tr><td>
								<div class="row">
									<div class="col-md-12 panel-body" style="text-align: left !important;">
										<legend class="legend"><span style="font-weight:bold;">' . $lang['REFACCIONES'] . ':</span></legend>
									</div>
								</div>
							</td></tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;"><span style="font-weight:bold;">' . $lang['Instrucciones'] . '</span> ' . $lang['AgregaREFACCION'] . '<br><span style="color:#f00; font-weight:bold;">' . $lang['InstRef'] . '</span>:</td></tr>'."\n";
	echo '		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audasust" cols="70" rows="13" style="background-color:#FFFFB0;" /></textarea></td></tr>
		<tr><td colspan="2" style="text-align:left;"></td></tr>'."\n";

// ------ Captura de Consumibles ------
	echo '						<tr><td>
								<div class="row">
									<div class="col-md-12 panel-body" style="text-align: left !important;">
										<legend class="legend"><span style="font-weight:bold;">' . $lang['CONSUMIBLES'] . ':</span></legend>
									</div>
								</div>
							</td></tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;">';
	echo '<span style="font-weight:bold;">' . $lang['Instrucciones'] . '</span> ' . $lang['AgregaCONSUMIBLE'] . '<br><span style="color:#f00; font-weight:bold;">' . $lang['InstCons'] . '</span>:';
	echo '</td></tr>
		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audacons" cols="70" rows="13" style="background-color:#FFFFB0;" /></textarea></td></tr>
		<tr><td colspan="2" style="text-align:left;"></td></tr>'."\n";


// ------ Captura de Mano de Obra ------
	echo '						<tr><td>
								<div class="row">
									<div class="col-md-12 panel-body" style="text-align: left !important;">
										<legend class="legend"><span style="font-weight:bold;">' . $lang['MANOOBRA'] . ':</span></legend>
									</div>
								</div>
							</td></tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;">';
	echo '<span style="font-weight:bold;">' . $lang['Instrucciones'] . '</span> ' . $lang['AgregaMO'] . '<br><span style="color:#f00; font-weight:bold;">' . $lang['InstMO'] . '</span><br>' . $lang['PrecioUT'] . ':<input type="text" name="preciout" value="' . $ut[$sub_orden['sub_aseguradora']] . '" /></td></tr>
		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audamo" cols="70" rows="13" style="background-color:#FFFFB0;" /></textarea></td></tr>
		<tr><td colspan="2" style="text-align:left;"></td></tr>'."\n";

	$preg0 = "SELECT op_id, op_codigo, op_nombre, op_cantidad, op_precio, prod_id, op_tangible, op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden['sub_orden_id'] . "' AND op_pres = '1' ORDER BY op_tangible,op_item";
  	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de requeridos!");
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Refacciones, Materiales y Mano de Obra ya presupuestado:';
	echo '</td></tr>
			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td>Tipo</td><td>Cantidad</td><td>Nombre</td><td>Código</td><td>Precio<br>Unitario</td><td>Borrar?</td></tr>'."\n";
	$cuenta = 0;
	while($op = mysql_fetch_array($matr0)) {
		if($op['op_tangible'] == '1') { $tipo = 'Refacción';}
		elseif($op['op_tangible'] == '2') { $tipo = 'Consumible';}
		else {$tipo = 'MO';}
		echo '					<tr><td style="text-align:center;">' . $tipo . '</td><td style="text-align:center;">' . $op['op_cantidad'];

		echo '</td><td>' . $op['op_nombre'] . '</td><td>' . $op['op_codigo'] . '</td><td style="text-align:right;">' . number_format($op['op_precio'],2);
		echo '</td><td>';
		if($op['op_pedido'] > 0) {
			echo 'En Pedido';
		} else {
			echo '<input type="checkbox" name="borrar[' . $cuenta . ']" value="1" /><input type="hidden" name="op_id[' . $cuenta . ']" value="' . $op['op_id'] . '" />';
		}
		echo '</td></tr>'."\n";
		$cuenta++;
	}
	echo '				</table>';
	echo '			</td>
		</tr>'."\n";
	$preg1 = "SELECT paq_id, paq_nombre FROM " . $dbpfx . "paquetes WHERE paq_area ='" . $sub_orden['sub_area'] . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Seleccionar un Paquete de Servicio</td></tr>
		<tr><td colspan="2" style="text-align:left;">
			<select name="paquete" size="1">
				<option value="">Seleccione...</option>'."\n";
	while($paqs = mysql_fetch_array($matr1)) {
		echo '				<option value="' . $paqs['paq_id'] . '">' . $paqs['paq_nombre'] . '</option>'."\n";
	}
	echo '			</select>
		</td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";

	echo '		<tr><td colspan="2" style="text-align:left; font-size:16px;"><a href="presupuestos.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&cpres=1';
	if($preaut === '1') { echo '&preaut=1'; }
	echo '"><img src="idiomas/' . $idioma . '/imagenes/refacciones.png" alt="Asignar Refacciones desde Almacén" title="Asignar Refacciones desde Almacén"> Asignar Refacciones y Mano de Obra desde Catálogo</a></td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">'."\n";

	echo '			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="orden_id" value="' . $sub_orden['orden_id'] . '" />
			<input type="hidden" name="aseguradora_id" value="' . $sub_orden['sub_aseguradora'] . '" />
			<input type="hidden" name="area" value="' . $sub_orden['sub_area'] . '" />
		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>'."\n";
	echo '	</table>
	</form>'."\n";
}

elseif($accion==='presupuesto') {
	
	$sub_orden_id=preparar_entrada_bd($sub_orden_id);
	$area=preparar_entrada_bd($area);
	
	$autosurtido = '0';  // Presupuesto de Taller por Autorizar....

	$audasust2 = preg_split("/[\n]+/", $audasust);
//	print_r($audasust2);
	foreach ($audasust2 as $i => $v) {
//		print_r($v);
//		echo '<br>';
		$precioar = '';
		unset($estructural);
		$codigo =''; $texto = '';
		$descripcion = ''; 
		$des = '';
		$cant = '';
// Identificar partes estructurales con un & al final de la línea de partes a sustituir.
		for($j = strlen($v); $j >= 0; $j--) {
			if(ord($v[$j]) == 38) {
				$estructural = 1;
				break;
			} elseif(ord($v[$j]) == 32 || ord($v[$j]) == 9) {
				break;
			}
		}
// Obtener precio de parte
		for($j = strlen($v); $j >= 0; $j--) {
//			echo $j . ' ' . $v[$j] . ' -> ' . ord($v[$j]) . '<br>'; 
			if($v[$j]=='#') {
				$des = substr($v, 0, $j);
				$precioar = 0;
				break;
			}
			elseif((is_numeric($v[$j]) || $v[$j]=='.' || $v[$j]==',') && $texto == '') {
				if($v[$j]!=',') {
					$precioar = $v[$j] . $precioar;
				}
			}
			elseif((is_numeric($v[$j]) || $v[$j]=='.') && $texto != '') {
				$texto = $v[$j] . $texto;
			}
			elseif(ord($v[$j]) >= '65') {
				$texto = $v[$j] . $texto;
			}
			elseif(($v[$j]==' ' || ord($v[$j]) == '9' || ord($v[$j]) == 36) && $precioar != '') {
				$des = substr($v, 0, $j);
				break;
			}
			elseif(!is_numeric($v[$j]) && ($precioar != '' || $texto != '')) {
				if($precioar == '') { $texto = ' ' . $texto; }
				$des = substr($v, 0, $j) . $precioar . $texto;
				$precioar = 0;
				break;
			}
		}

		for($j = 0; strlen($des) >= $j; $j++) {
			if(is_numeric($des[$j]) || $des[$j]=='.' ) {
				$cant = $cant . $des[$j];
			}
			elseif($des[$j]==' ' && $cant!='') {
				$descripcion = substr($des, $j);
				break;
			} else {
				$descripcion = $des;
				break;
			}
		}

		if($cant == '' && strlen($des) > 0) {
			$cant = 1;
			$descripcion = $des;
		}

		$audaprod[$i][0] = trim($descripcion);
		$audaprod[$i][1] = trim($precioar);
		$audaprod[$i][2] = trim($estructural);
//		$audaprod[$i][3] = trim($codigo);
		$audaprod[$i][4] = trim($cant);
	}
//	print_r($audaprod);
//	echo '<br><br>';
	unset($audasust, $audasust2);


// ---- Procesar consumibles ----
	$audacons2 = preg_split("/[\n]+/", $audacons);
print_r($audacons2);
echo '<br>';
	
	foreach ($audacons2 as $i => $v) {
		$precioar = '';
		$descripcion = '';
		$cant = '';
		$res = '';

		// Obtener precio del consumible
		for($j = strlen($v); $j >= 0; $j--) {
			if($v[$j]=='#') {
				$res = substr($v, 0, $j);
				$precioar = 0;
				break;
			}
			elseif(is_numeric($v[$j]) || $v[$j]=='.' || $v[$j]=='-') {
				if($v[$j]==',') { $v[$j]='.'; }
				$precioar = $v[$j] . $precioar;
			}
			elseif(($v[$j]==' ' || ord($v[$j]) == '9') && $precioar!='') {
				$res = substr($v, 0, $j);
			break;
			}
		}

		if($precioar == '') { $precioar = '0'; }

		for($j = 0; strlen($res) >= $j; $j++) {
			if(is_numeric($res[$j]) || $res[$j]=='.' ){
				$cant = $cant . $res[$j];
			} elseif($res[$j]==' ' && $cant!=''){
				$descripcion = substr($res, $j);
				break;
			}
		}

		if($cant == '') { $cant = '0'; }

		$audaconsumible[$i][0] = trim($descripcion);
		$audaconsumible[$i][1] = trim($precioar);
		$audaconsumible[$i][4] = trim($cant);
	}
	print_r($audaconsumible);
	//echo '<br>';
	unset($audacons, $audacons2);
// ---- Termina Procesamiento de consumibles ----


//	print_r($audamo); echo '<br>';

	$audamo2 = preg_split("/[\n]+/", $audamo);
	foreach ($audamo2 as $i => $v) {
		$precioar = '';
		$cant = 0;
		$texto = '';
//		$descripcion = $v;

		for($j = strlen($v); $j >= 0; $j--) {
//			echo $j . ' ' . $v[$j] . ' -> ' . ord($v[$j]) . '<br>'; 
			if($v[$j]=='#') {
				$des = substr($v, 0, $j);
				$precioar = 0;
				break;
			}
			elseif((is_numeric($v[$j]) || $v[$j]=='.' || $v[$j]==',') && $texto == '') {
				if($v[$j]!=',') {
					$precioar = $v[$j] . $precioar;
				}
			}
			elseif((is_numeric($v[$j]) || $v[$j]=='.') && $texto != '') {
				$texto = $v[$j] . $texto;
			}
			elseif(ord($v[$j]) >= '65') {
				$texto = $v[$j] . $texto;
			}
			elseif(($v[$j]==' ' || ord($v[$j]) == 9 || ord($v[$j]) == 36) && $precioar != '') {
				$des = substr($v, 0, $j);
				break;
			}
			elseif(!is_numeric($v[$j]) && ($precioar != '' || $texto != '')) {
				if($precioar == '') { $texto = ' ' . $texto; }
				$des = substr($v, 0, $j) . $precioar . $texto;
				$precioar = 0;
				break;
			}
		}
		$audaobr[$i][0] = trim($des);
		$audaobr[$i][1] = trim($precioar);
	}
//	print_r($audaobr);
//	echo '<br><br>';
	unset($audamo2, $audamo);

	$error = 'no';
	$mensaje= '';
	$parametros='sub_orden_id = ' . $sub_orden_id;

	if (($error === 'no') && (isset($paquete) || is_array($audaobr) || is_array($audaprod) || is_array($audaconsumible) || is_array($op_id) )) {
//		print_r($op_id);
		if (is_array($op_id)) {
			foreach($op_id as $i => $oi) {
				if(isset($borrar[$i]) && $borrar[$i]=='1') {
					$parme = " op_id = '" . $op_id[$i] . "' AND op_pedido < '1' ";
					ejecutar_db($dbpfx . 'orden_productos', '', 'eliminar', $parme);

//					$pregunta="ELIMINAR FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op_id[$i] . "' AND op_pedido < '1'";
//					$resultado = mysql_query($pregunta);
				} else {
					$preunit[$i] = limpiarNumero($preunit[$i]);
					$cantp[$i] = limpiarNumero($cantp[$i]);
//					echo 'Cant: ' .$cantp[$i] . ' PU: ' . $preunit[$i] . '<br>';
					$opsub = $cantp[$i] * $preunit[$i];
					$param = "op_id = '" . $op_id[$i] . "' AND op_pedido < '1'";
					$sqldata = array('op_cantidad' => $cantp[$i], 'op_precio' => $preunit[$i], 'op_subtotal' => $opsub);
					ejecutar_db($dbpfx . 'orden_productos', $sqldata, 'actualizar', $param);
				}
			}
		}
		unset($sqldata);
		bitacora($orden_id, 'Precios de Presupuesto actualizados para Tarea ' . $sub_orden_id, $dbpfx);
		$refacciones=0;
		$preg1 = "SELECT prod_id, op_cantidad, op_precio, op_descuento, op_tangible, op_estructural, op_recibidos FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "' AND op_tangible < '3'";
//		echo $preg1;
  		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden_productos!");
  		while($op = mysql_fetch_array($matr1)) {
			$op_subtotal = $op['op_cantidad'] * ($op['op_precio'] - $op['op_descuento']);
			if($op['op_tangible']=='1') {
				$sub_partes = $sub_partes + $op_subtotal;
			} elseif($op['op_tangible']=='2') {
				$sub_consumibles = $sub_consumibles + $op_subtotal;
			} else {
				$sub_mo = $sub_mo + $op_subtotal;
				$tiempo = $tiempo + $op['op_cantidad'];
			}
//			echo $op_subtotal . '<br>';
			$presupuesto = $presupuesto + $op_subtotal;
  		}

//--------------  Determinación de número de Item	--------------------

		$preg6 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < 130";
		$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de subordenes items!");
		$item = 1;
		while($dato6 = mysql_fetch_array($matr6)) {
			$preg5 = "SELECT op_item FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $dato6['sub_orden_id'] . "' ORDER BY op_item DESC LIMIT 1";
  			$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden_productos!");
  			$dato5 = mysql_fetch_array($matr5);
			if($dato5['op_item'] >= $item) {$item = $dato5['op_item'] + 1;}
		}

//--------------  Fin de determinación de número de Item	--------------------

		if (is_array($prod_id)) {
			for($i=0;$i<count($prod_id);$i++) {
				if($prod_cantidad[$i]!='') {
//						$preg1 = "SELECT prod_cantidad_disponible FROM " . $dbpfx . "productos WHERE prod_id = '" . $prod_id[$i] . "'";
//  					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de op_prods!");
//  					$op = mysql_fetch_array($matr1);
  					if($prod_cantidad[$i] > $prod_disponible[$i]) { $refacciones=1; }
					if($prod_tangible[$i]=='1') {
						$prod_cantidad[$i] = intval($prod_cantidad[$i]);
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_partes = $sub_partes + $op_subtotal;
					} elseif($prod_tangible[$i]=='2') {
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_consumibles = $sub_consumibles + $op_subtotal;
					} else {
						if($sub_aseguradora > 0) { $prod_precio[$i] = $ut[$sub_aseguradora]; }
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_mo = $sub_mo + $op_subtotal;
						$tiempo = $tiempo + $prod_cantidad[$i];
					}
					$presupuesto = $presupuesto + $op_subtotal;
					$sql_data_array = array('sub_orden_id' => $sub_orden_id,
						'op_area' => $area,
						'op_item' => $item,
						'prod_id' => $prod_id[$i],
						'op_cantidad' => $prod_cantidad[$i], 
						'op_nombre' => $prod_nombre[$i],
						'op_codigo' => $prod_codigo[$i],
						'op_tangible' => $prod_tangible[$i],
						'op_precio' => $prod_precio[$i],
						'op_costo' => $prod_costo[$i],
						'op_pres' => '1',
						'op_subtotal' => $op_subtotal);
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
					$item++;
				}
			}
		}

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
//			echo $preg1.'<br>';
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
							'op_pres' => '1',
							'op_subtotal' => $op_subtotal);
						$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
						$item++;
					}
				}
			}
		}

		if (is_array($audaprod)) {
			$op_subtotal = 0;
			for($i=0;$i<=count($audaprod);$i++) {
				if(($audaprod[$i][0]!='') && ($audaprod[$i][1]!='')) {
					$cant1 = $audaprod[$i][4];
					if($area=='7') { $tang = 2; } else { $tang = 1; }
					$op_subtotal = round(($cant1 * ($audaprod[$i][1] - $descuento)),6);
					$sql_data_array3 = array('sub_orden_id' => $sub_orden_id,
						'prod_id' => $prod_id,
						'op_area' => $area,
						'op_item' => $item,
						'op_nombre' => $audaprod[$i][0],
						'op_cantidad' => $cant1,
						'op_precio' => $audaprod[$i][1],
						'op_subtotal' => $op_subtotal,
						'op_autosurtido' => $autosurtido,
						'op_pres' => '1',
						'op_tangible' => $tang,
						'op_estructural' => $audaprod[$i][2]);
					if($bloqueaprecio != '1') {
						
						if($tang == '1') {
							$sub_partes = $sub_partes + $op_subtotal;
						} else {
							$sub_consumibles = $sub_consumibles + $op_subtotal;
						}
						$presupuesto = $presupuesto + $op_subtotal;
					}
					ejecutar_db($dbpfx . 'orden_productos', $sql_data_array3, 'insertar');
					$item++;
				}
			}
		}

		if (is_array($audaconsumible)) {
			$op_subtotal = 0;
			for($i=0;$i<=count($audaconsumible);$i++) {
				if(($audaconsumible[$i][0]!='') && ($audaconsumible[$i][1]!='')) {
					$cant1 = $audaconsumible[$i][4];
//					$op_precio = round(($audaconsumible[$i][1] / $cant1),6);
					$op_subtotal = round(($audaconsumible[$i][1] * $cant1),6);
					$sql_data_array3 = array('sub_orden_id' => $sub_orden_id,
						'prod_id' => $prod_id,
						'op_area' => $area,
						'op_item' => $item,
						'op_nombre' => $audaconsumible[$i][0],
						'op_cantidad' => $cant1,
						'op_precio' => $audaconsumible[$i][1],
						'op_subtotal' => $op_subtotal,
						'op_autosurtido' => $autosurtido,
						'op_pres' => '1',
						'op_tangible' => '2');
					if($bloqueaprecio != '1') {
						$sub_consumibles = $sub_consumibles + $op_subtotal;
						$presupuesto = $presupuesto + $op_subtotal;
					}
					ejecutar_db($dbpfx . 'orden_productos', $sql_data_array3, 'insertar');
					$item++;
				}
			}
		}

		if (is_array($audaobr)) {
			for($i=0;$i<=count($audaobr);$i++) {
				if(($audaobr[$i][0]!='') && ($audaobr[$i][1]!='')) {
//					echo 'Desc: ' . $audaobr[$i][0] . ' Total: '. $audaobr[$i][1] . ' Cantidad: ' . $cant1 . ' Precio UT: ' . $preciout . '<br>';
					$sbtq = $audaobr[$i][1] / $preciout;
					$cant1 = round($sbtq, 6);
//					echo 'Desc: ' . $audaobr[$i][0] . ' Total: '. $audaobr[$i][1] . ' Cantidad: ' . $cant1 . ' Precio UT: ' . $sbtq . '<br>';
					$tiempo = $tiempo + $cant1;
					if($cant1 < 0) { $preciout = $preciout * -1; }
					$sql_data_array4 = array('sub_orden_id' => $sub_orden_id,
//						'prod_id' => '6',
						'op_area' => $area,
						'op_item' => $item,
						'op_nombre' => $audaobr[$i][0],
						'op_tangible' => 0,
						'op_autosurtido' => $autosurtido,
						'op_pres' => '1',
						'op_cantidad' => $cant1);
					if($bloqueaprecio != '1') {
						$op_subtotal= round(($cant1 * $preciout), 6);
						$sub_mo = $sub_mo + $op_subtotal;
						$presupuesto = $presupuesto + $op_subtotal;
						$sql_data_array4['op_precio'] = $preciout;
						$sql_data_array4['op_subtotal'] = $op_subtotal;
					}
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array4, 'insertar');
					$item++;
				}
			}
		}
		$sql_data_array = array('sub_estatus' => '127');
		$parametros='sub_orden_id = ' . $sub_orden_id;
		if($_SESSION['usuario'] != '701') {
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		}
 		bitacora($orden_id, 'Presupuesto Creado o Modificado para Tarea ' . $sub_orden_id, $dbpfx);
		unset($_SESSION['pres']['sub_orden_id']);
		actualiza_suborden ($orden_id, $area, $dbpfx);
		actualiza_orden ($orden_id, $dbpfx);
		
		redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['msgerror'] = 'No se recibieron datos';
		redirigir('presupuestos.php?accion=consultar&orden_id=' . $orden_id);
	}
}

?>
