<?php

	$mensaje = '';
	$error = 'si'; $num_cols = 0;
	include('parciales/phpqrcode/qrlib.php');
	if ($sub_orden_id!='') {
		$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id' AND sub_estatus >= '104' AND sub_estatus <= '110'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
		$ord = mysql_fetch_array($matriz);
		$orden_id = $ord['orden_id'];
		mysql_data_seek($matriz,0);
		$error = 'no';
	} else {
		$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
		$error = 'no';
	}
	$preg0 = "SELECT orden_fecha_promesa_de_entrega FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Ordenes de Trabajo!");
	$ord = mysql_fetch_array($matr0);
	
//	echo $pregunta;
	if ($num_cols>0) {
		
		// --- CONSULTAR ID DEL CLIENTE ---
		$preg_cliente_id = "SELECT orden_cliente_id FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matr_cliente_id = mysql_query($preg_cliente_id) or die("ERROR: Fallo selección de Orden de Trabajo! " . $preg_cliente_id);
		$cliente_id = mysql_fetch_assoc($matr_cliente_id);
		
		// --- CONCULTAR DATOS DEL CLIENTE ---
		$preg_info_cliente = "SELECT cliente_empresa_id, cliente_nombre, cliente_apellidos, cliente_telefono1 FROM " . $dbpfx . "clientes  WHERE cliente_id = '" . $cliente_id['orden_cliente_id'] . "'";
		$matr_info_cliente = mysql_query($preg_info_cliente) or die("ERROR: Fallo selección de Orden de Trabajo! " . $preg_info_cliente);
		$cliente_info = mysql_fetch_assoc($matr_info_cliente);
		// --- CONCULTAR DATOS DE LA EMPRESA ---
		$preg_empresa = "SELECT * FROM " . $dbpfx . "empresas WHERE empresa_id = '" . $cliente_info['cliente_empresa_id'] . "'";
		$matr_empresa = mysql_query($preg_empresa) or die("ERROR: Fallo seleccion! " . $preg_empresa);
		$empresa = mysql_fetch_assoc($matr_empresa);
		
//			echo $orden_id;
		$pveh = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_color, v.vehiculo_modelo, v.vehiculo_placas FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "ordenes o WHERE o.orden_id = '" . $orden_id . "' AND o.orden_vehiculo_id = v.vehiculo_id";
		$mveh = mysql_query($pveh) or die("ERROR: Fallo selección de vehículo!" . $pregunta);
		$veh = mysql_fetch_array($mveh);
		$vehiculo = array('marca' => $veh['vehiculo_marca'],
			'tipo' => $veh['vehiculo_tipo'],
			'color' => $veh['vehiculo_color'],
			'modelo' => $veh['vehiculo_modelo'],
			'placas' => $veh['vehiculo_placas']);
//			$vehiculo = datosVehiculo($orden_id, $dbpfx);
		echo '			<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="840">		
							<br>
							<tr>
								<td style="text-align:left; font-size:16px;">TALLER: <b>' . $empresa["empresa_razon_social"] . '</b></td>
							</tr>
							<tr>
								<td style="text-align:left; font-size:16px;">
								CALLE: <b>' . $empresa["empresa_calle"] . ' #' . $empresa["empresa_ext"] . ' int. ' . $empresa["empresa_int"] . '</b> COLONIA: <b>' . $empresa["empresa_colonia"] . '</b>  MUNICIPIO: <b>' . $empresa["empresa_municipio"] . '</b>
								<br>
								C.P. <b>' . $empresa["empresa_cp"] . '</b> ESTADO:  <b>' . $empresa["empresa_estado"] . '</b>
								<td>
							</tr>
							<tr>
								<td style="text-align:left; font-size:16px;"><br>CONTACTO: <b>' . $cliente_info["cliente_nombre"] . ' ' . $cliente_info["cliente_apellidos"] . '</b> <br>TEL: <b>' . $cliente_info["cliente_telefono1"] . '</b></td>
							</tr>
							<tr>
								<td style="text-align:left; font-size:22px; font-weight:bold; line-height:30px;">' . $vehiculo['marca'] . ' ' . $vehiculo['tipo'] . ' ' . $vehiculo['color'] . ' ' . $vehiculo['modelo'] . $lang['Placas'] . $vehiculo['placas'] . '<br>Orden de Trabajo: ' . $orden_id . '</td>
								<td style="text-align:right;"></td>
							</tr>'."\n";
		echo '			</table>'."\n";
		while($sub = mysql_fetch_array($matriz)) {
/*			if ($sub_orden_id!='') {
				$vehiculo = datosVehiculo($sub['orden_id'], $dbpfx);
				echo '		<tr><td style="text-align:left; font-size:22px;" colspan="4">Vehículo: ' . $vehiculo['marca'] . ' ' . $vehiculo['tipo'] . ' ' . $vehiculo['color'] . ' ' . $vehiculo['modelo'] . $lang['Placas'] . $vehiculo['placas'] . '</td></tr>'."\n";
			}
*/			if($metodo=='c') {
				$sub_orden_id = $sub['sub_orden_id'];
				$orden_id = $sub['orden_id'];
//				$codigo = $orden_id . ' ' . $sub_orden_id;
				$codigo = $sub_orden_id;
			} else {
				$orden_id = $sub['orden_id'];
//				$codigo = $orden_id . ' ' . $sub['sub_orden_id'] . ' ' . $sub['sub_operador'];
				$codigo = $sub['sub_orden_id'];
				$preg0 = "SELECT esp_nombre FROM " . $dbpfx . "espacios WHERE orden_id = '" . $sub['orden_id'] . "'";
		   		$mat0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de espacios!");
				$lugar = mysql_fetch_array($mat0);
			}
			$pregunta3 = "SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '" . $sub['sub_operador'] . "'";
			$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
			$usuario = mysql_fetch_array($matriz3);
			$codigoqr = 'https://' . $_SERVER['SERVER_NAME'] . '/seguimiento.php?accion=seguimiento&codigo=' . $codigo;
			$imagenseg = DIR_DOCS.'qr-seguimiento-' . $codigo . '.png';
			QRcode::png($codigoqr, $imagenseg, 'L', 4, 2);
			$filamo = ''; $filat = '';
			
			// ---- Consultar Nombre del convenio ---
			$consulta_nic = "SELECT aseguradora_nic FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $sub['sub_aseguradora'] . "'";
			$mtr_nick = mysql_query($consulta_nic) or die("ERROR: Fallo aseguradora!");
			$nick_convenio = mysql_fetch_assoc($mtr_nick);
			
			echo '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">'."\n";
			echo '				<tr><td style="text-align:left; font-size:22px; line-height:30px; width:748px;">
										<b><strong>Tarea ' . $sub['sub_orden_id'] . ': ' . constant('NOMBRE_AREA_' . strtoupper($sub['sub_area'])) . ' ' . $sub['sub_descripcion'] . '</strong></b><br><b><small>CONVENIO:</b> ' . $nick_convenio['aseguradora_nic'] . ' <b>SINIESTRO:</b> ' . $sub['sub_reporte'] . '</small></td><td valign="top" style="width:100px;" ><img src="' . $imagenseg . '" alt="Código QR registro de seguimiento de reparación" width="100"></td></tr></table>'."\n";
			echo '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">'."\n";
			echo '				<tr><td style="vertical-align:top; width:50%;">';
			if($sub['sub_area']=='7') { echo $lang['Consumibles']; } else { echo $lang['Partes']; }
			echo '</td><td style="vertical-align:top; width:50%;">' . $lang['MO'] . '</td></tr>'."\n";
			if($sotsindesc != '1') {
				$pregunta2 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '$sub_orden_id'";
//				if($sub['sub_area']=='7') { $pregunta2 .= " AND op_tangible = '0'"; } 
//				elseif($soloref=='1') { $pregunta2 .= " AND op_tangible > '0'"; }
				$pregunta2 .= " ORDER BY op_tangible,op_item";
   		   $matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
      		$num_prods = mysql_num_rows($matriz2);
	      	if ($num_prods>0) {
	      		while ($prods = mysql_fetch_array($matriz2)) {
		      		if($prods['op_tangible'] == '0') {
   		   			$filat .= '						<tr><td>' . $prods['op_cantidad'] . ' ' . $prods['op_nombre'] . '</td></tr>'."\n";
   		   		} else {
   	   				$filamo .= '						<tr><td>' . $prods['op_cantidad'] . ' ' . $prods['op_nombre'] . '</td></tr>'."\n";
   	   			}
   	   		}
	   	   }
			}
			echo '				<tr><td style="vertical-align:top; width:50%;">';
			echo '					<table cellpadding="3" cellspacing="0" border="0" class="izquierda" width="100%">'."\n";
			echo $filamo;
			echo '					</table>'."\n";			
			echo '						</td><td>';
			echo '					<table cellpadding="3" cellspacing="0" border="0" class="izquierda" width="100%">'."\n";
			echo $filat;
			echo '					</table>'."\n";			
			echo '				</td></tr></table>'."\n";
			
		}
		echo '			<table cellpadding="0" cellspacing="2" border="0" class="izquierda" width="840">'."\n";
		echo '				<tr><td style="text-align:left;"><div class="control"><a href="proceso.php?accion=consultar&orden_id=' . $orden_id . '#' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>&nbsp;<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir-sot.png" alt="Imprimir Todas las SOT de la OT" title="Imprimir Todas las SOT de la OT"></a></div></td></tr></table>'."\n";
	}

?>
