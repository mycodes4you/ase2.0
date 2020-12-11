<?php
		$codigoqr = $urlpub.'/consulta.php?accion=consultar&orden_id=' . $orden_id . '&arg0=' . $cust['cliente_clave'];
		$imagenqr = 'documentos/qr-orden-' . $orden_id . '.png';
		QRcode::png($codigoqr, $imagenqr, 'L', 4, 2);
		$preg5 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_area = '6' AND sub_estatus < '189' AND sub_siniestro = '1' LIMIT 1";
		$matr5 = mysql_query($preg5) or die("ERROR: Fallo seleccion de aseguradora!");
		$aseg = mysql_fetch_array($matr5);

		echo '		<div >
		<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda" style="line-height:12px; font-size:0.8em;">
			<tr>
				<td style="width:210px;"><img src="particular/logo-agencia.png" alt="' . $agencia_razon_social . '" height="80"></td>
				<td style="width:410px; text-align:center;"><span style="font-size:1.5em; font-weight:bold;">' . $agencia_razon_social . '</span><br>
				<span style="line-height:12px; ">' . $agencia_direccion . ' Colonia ' . $agencia_colonia . '<br>
				Código Postal ' . $agencia_cp . '. '  . $agencia_municipio . ', ' . $agencia_estado . '.<br>
				R.F.C. ' . $agencia_rfc . '<br>
				</span>
				</td>
				<td style="width:220px; vertical-align: top; text-align:right;"><img src="' . $imagenqr . '" alt="QR de seguimiento AutoShop Easy" width="100">
				</td>
			</tr>'."\n";
		echo '				<tr><td style="width:200px;">Fecha de Ingreso: ' . $orden['orden_fecha_recepcion'] . '</td><td style="width:460px;"> Vehículo: ' . $cust['vehiculo_marca'] . ' ' . $cust['vehiculo_tipo'] . ' ' . $cust['vehiculo_subtipo'] . ' Color: ' . $cust['vehiculo_color'] . ' Año: ' . $cust['vehiculo_modelo'] . ' Placas: ' . $cust['vehiculo_placas'] . '</td><td style="width:180px; vertical-align: top; text-align:right;" rowspan="4">Teléfonos: ' . $agencia_telefonos . '<br>
				Correo electrónico del Taller:<br>' . $agencia_email . '<br> 
				Número de Afiliación SIEM: 11446</td></tr>'."\n";

//			echo '			<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda">
		echo '				<tr><td><span style="font-size:12px; font-weight:bold;">Orden de Reparación: ' . $orden_id . '</span></td><td>Cliente: ' . $cust['cliente_nombre'] . ' ' . $cust['cliente_apellidos'] . '. RFC:</td></tr>';
//			echo $cust['cliente_calle'] . ' ' . $cust['cliente_numext'] . ' ' . $cust['cliente_numint'] . ' Colonia ' . $cust['cliente_colonia'] . '. Municipio ' . $cust['cliente_municipio'] . ', ' . $cust['cliente_estado'];
		echo '			<tr><td colspan="2">Domicilio:</td></tr>'."\n";
		echo '			<tr><td>Teléfono: ' . $cust['cliente_telefono1'] . '</td><td colspan="2"> Correo eléctronico: ' . $cust['cliente_email'] . '</td></tr>
		</table>'."\n";

		echo '		<table cellpadding="1" cellspacing="3" border="0" width="840" class="invenc" style="font-size:0.8em;">
			<tr><td style="border-width: 1px; border-style: solid; width: 415px;">
				<table cellpadding="1" cellspacing="0" border="0" width="100%" class="izquierda">
					<tr><td style="width: 42%;">Número de Serie: ' . $cust['vehiculo_serie'] . '</td><td style="width: 34%;">Número de Motor: ' . $cust ['vehiculo_motor'] . '</td><td style="width: 24%;">Kilometraje:' . $orden['orden_odometro'] . '</td></tr>
					<tr><td colspan="2">Tanque de Gasolina: Vacio ';
		if($inv['inv_int'][28] == '0') { echo ' <strike>Reserva </strike>';} else { echo 'Reserva ';}
		if($inv['inv_int'][28] == '1') { echo '<strike>1/8 </strike>';} else { echo '1/8 ';}
		if($inv['inv_int'][28] == '2') { echo '<strike>1/4 </strike>';} else { echo '1/4 ';}
		if($inv['inv_int'][28] == '3') { echo '<strike>3/8 </strike>';} else { echo '3/8 ';}
		if($inv['inv_int'][28] == '4') { echo '<strike>1/2 </strike>';} else { echo '1/2 ';}
		if($inv['inv_int'][28] == '5') { echo '<strike>5/8 </strike>';} else { echo '5/8 ';}
		if($inv['inv_int'][28] == '6') { echo '<strike>3/4 </strike>';} else { echo '3/4 ';}
		if($inv['inv_int'][28] == '7') { echo '<strike>7/8 </strike>';} else { echo '7/8 ';}
		if($inv['inv_int'][28] == '8') { echo '<strike>Lleno</strike>';} else { echo 'Lleno';}
		echo '</td><td>Puertas: ' . $cust ['vehiculo_puertas'] . '</td></tr>
				</table>
			</td><td style="border-width: 1px; border-style: solid; width: 295px;">
				<table cellpadding="1" cellspacing="0" border="0" width="100%" class="invenc">
					<tr><td style="width: 50%;">Aseguradora: ' . $asegu[$aseg['sub_aseguradora']]['nic'] . '</td><td style="width: 50%;">Siniestro: ' . $aseg['sub_reporte'] . '</td></tr>
					<tr><td>Clave de Cliente: ' . $cust['cliente_clave'] . '</td></tr>
				</table>
			</td></tr>
		</table>
		</div>';
			$orden_estatus = $orden['orden_estatus'];
			echo '		<table cellpadding="2" cellspacing="0" border="0" class="izq" width="840">
			<tr>
				<td style="width:417px; vertical-align: top; padding-right:3px; font-size:10px;">'."\n";
//					print_r($cust);
			echo '						Interiores<br>'."\n";
			echo '					<table border="0" cellpadding="1" cellspacing="1" width="100%" class="inventario">'."\n"; $k=0;
			for($i=0; $i < $invint; $i++) {
				if ($k==0) { echo '					<tr><td>';} else { echo '<td>';}
				echo '					' . constant('ETIQUETA_INV_INTERIORES_'.$i) . ': '; 
				if($inv['inv_int'][$i]==0) {echo '';} 
				elseif($inv['inv_int'][$i]==1) {echo 'Sí';} 
				elseif($inv['inv_int'][$i]==2) {echo 'No';}
				elseif($inv['inv_int'][$i]==3) {echo 'Daño';}
				echo '</td>';
				$k++;
				if ($k==3) {$k=0; echo '</tr>';}
			}
			if ($k==2) {echo '</tr>';}
			echo '					</table>'."\n";
			
			echo '						Lado Izquierdo<br>'."\n";
			echo '					<table border="0" cellpadding="1" cellspacing="1" width="100%" class="inventario">'."\n"; $k=0;
			for($i=0; $i < $invlatizq; $i++) {
				if ($k==0) { echo '					<tr><td>';} else { echo '<td>';}
				echo '					' . constant('ETIQUETA_INV_LATIZQ_'.$i) . ': '; 
				if($inv['inv_latizq'][$i]==0) {echo '';} 
				elseif($inv['inv_latizq'][$i]==1) {echo 'Sí';} 
				elseif($inv['inv_latizq'][$i]==2) {echo 'No';}
				elseif($inv['inv_latizq'][$i]==3) {echo 'Daño';}
				echo '</td>';
				$k++;
				if ($k==3) {$k=0; echo '</tr>';}
			}
			if ($k==2) {echo '</tr>';}
			echo '					</table>'."\n";

			echo '					Frontal<br>'."\n";
			echo '					<table border="0" cellpadding="1" cellspacing="1" width="100%" class="inventario">'."\n"; $k=0;
			for($i=0; $i < $invfrontal; $i++) {
				if ($k==0) { echo '					<tr><td>';} else { echo '<td>';}
				echo '					' . constant('ETIQUETA_INV_FRONTAL_'.$i) . ': '; 
				if($inv['inv_frontal'][$i]==0) {echo '';} 
				elseif($inv['inv_frontal'][$i]==1) {echo 'Sí';} 
				elseif($inv['inv_frontal'][$i]==2) {echo 'No';}
				elseif($inv['inv_frontal'][$i]==3) {echo 'Daño';}
				echo '</td>';
				$k++;
				if ($k==3) {$k=0; echo '</tr>';}
			}
			if ($k==2) {echo '</tr>';}
			echo '					</table>'."\n";
			
			echo '					Motor<br>'."\n";
			echo '					<table border="0" cellpadding="1" cellspacing="1" width="100%" class="inventario">'."\n"; $k=0;
			for($i=0; $i < $invmotor; $i++) {
				if ($k==0) { echo '					<tr><td>';} else { echo '<td>';}
				echo '					' . constant('ETIQUETA_INV_MOTOR_'.$i) . ': '; 
				if($inv['inv_motor'][$i]==0) {echo '';} 
				elseif($inv['inv_motor'][$i]==1) {echo 'Sí';} 
				elseif($inv['inv_motor'][$i]==2) {echo 'No';}
				elseif($inv['inv_motor'][$i]==3) {echo 'Daño';}
				echo '</td>';
				$k++;
				if ($k==3) {$k=0; echo '</tr>';}
			}
			if ($k==2) {echo '</tr>';}
			echo '					</table>'."\n";
			
			echo '					Lado Derecho<br>'."\n";
			echo '					<table border="0" cellpadding="1" cellspacing="1" width="100%" class="inventario">'."\n"; $k=0;
			for($i=0; $i < $invlatder; $i++) {
				if ($k==0) { echo '					<tr><td>';} else { echo '<td>';}
				echo '					' . constant('ETIQUETA_INV_LATDER_'.$i) . ': '; 
				if($inv['inv_latder'][$i]==0) {echo '';} 
				elseif($inv['inv_latder'][$i]==1) {echo 'Sí';} 
				elseif($inv['inv_latder'][$i]==2) {echo 'No';}
				elseif($inv['inv_latder'][$i]==3) {echo 'Daño';}
				echo '</td>';
				$k++;
				if ($k==3) {$k=0; echo '</tr>';}
			}
			if ($k==2) {echo '</tr>';}
			echo '					</table>'."\n";
//			echo '		</td>'."\n";
//			echo '		<td style="width:277px; vertical-align: top; padding-right:3px; font-size:10px;">'."\n";

			echo '					Posterior<br>'."\n";
			echo '					<table border="0" cellpadding="1" cellspacing="1" width="100%" class="inventario">'."\n"; $k=0;
			for($i=0; $i < $invpost; $i++) {
				if ($k==0) { echo '					<tr><td>';} else { echo '<td>';}
				echo '					' . constant('ETIQUETA_INV_POST_'.$i) . ': '; 
				if($inv['inv_post'][$i]==0) {echo '';} 
				elseif($inv['inv_post'][$i]==1) {echo 'Sí';} 
				elseif($inv['inv_post'][$i]==2) {echo 'No';}
				elseif($inv['inv_post'][$i]==3) {echo 'Daño';}
				echo '</td>';
				$k++;
				if ($k==3) {$k=0; echo '</tr>';}
			}
			if ($k==2) {echo '</tr>';}
			echo '					</table>'."\n";
			
			echo '					Cajuela<br>'."\n";
			echo '					<table border="0" cellpadding="1" cellspacing="1" width="100%" class="inventario">'."\n"; $k=0;
			for($i=0; $i < $invcajuela; $i++) {
				if ($k==0) { echo '					<tr><td>';} else { echo '<td>';}
				echo '					' . constant('ETIQUETA_INV_CAJ_'.$i) . ': '; 
				if($inv['inv_cajuela'][$i]==0) {echo '';} 
				elseif($inv['inv_cajuela'][$i]==1) {echo 'Sí';} 
				elseif($inv['inv_cajuela'][$i]==2) {echo 'No';}
				elseif($inv['inv_cajuela'][$i]==3) {echo 'Daño';}
				echo '</td>';
				$k++;
				if ($k==3) {$k=0; echo '</tr>';}
			}
			if ($k==2) {echo '</tr>';}
			echo '					</table>'."\n";
			
			echo '					Otros<br>'."\n";
			echo '					<table border="0" cellpadding="1" cellspacing="1" width="100%" class="inventario">'."\n"; $k=0;
			for($i=0; $i < $invotros; $i++) {
				if ($k==0) { echo '					<tr><td>';} else { echo '<td>';}
				echo '					' . constant('ETIQUETA_INV_OTROS_'.$i) . ': '; 
				if($inv['inv_otros'][$i]==0) {echo '';} 
				elseif($inv['inv_otros'][$i]==1) {echo 'Sí';} 
				elseif($inv['inv_otros'][$i]==2) {echo 'No';}
				elseif($inv['inv_otros'][$i]==3) {echo 'Daño';}
				echo '</td>';
				$k++;
				if ($k==3) {$k=0; echo '</tr>';}
			}
			if ($k==2) {echo '</tr>';}
			echo '					</table>'."\n";
			
			echo '					Toldo<br>'."\n";
			echo '					<table border="0" cellpadding="1" cellspacing="1" width="100%" class="inventario">'."\n"; $k=0;
			for($i=0; $i < $invtoldo; $i++) {
				if ($k==0) { echo '					<tr><td>';} else { echo '<td>';}
				echo '					' . constant('ETIQUETA_INV_TOLDO_'.$i) . ': '; 
				if($inv['inv_toldo'][$i]==0) {echo '';} 
				elseif($inv['inv_toldo'][$i]==1) {echo 'Sí';} 
				elseif($inv['inv_toldo'][$i]==2) {echo 'No';}
				elseif($inv['inv_toldo'][$i]==3) {echo 'Daño';}
				echo '</td>';
				$k++;
				if ($k==3) {$k=0; echo '</tr>';}
			}
			if ($k==2) {echo '</tr>';}
			echo '					</table>'."\n";
			
			echo '		</td><td style="vertical-align: top;">'."\n";
			echo '			<table cellpadding="2" cellspacing="0" border="0" class="izq" width="100%">'."\n";
			if($preexistentes=='') {$preexistentes = 'imagenes/dibujos-de-autos.png';}
			echo '				<tr><td><img src="' . $preexistentes . '" alt="Registro gráfico" width="400" style="margin-left:0px;margin-right:0px;"></td></tr>'."\n";
			echo '			</table>'."\n";
			echo '			<table cellpadding="1" cellspacing="1" border="0" class="inventario" width="100%" style="font-size:0.8em;">'."\n";
			echo '				<tr><td style="width:100px;">Llantas</td><td>Marca, tamaño y desgaste</td></tr>'."\n";
			echo '				<tr><td>Delantera Izquierda</td><td></td></tr>'."\n";
			echo '				<tr><td>Delantera Derecha</td><td></td></tr>'."\n";
			echo '				<tr><td>Trasera Izquierda</td><td></td></tr>'."\n";
			echo '				<tr><td>Trasera Derecha</td><td></td></tr>'."\n";
			echo '				<tr><td>Refacción</td><td></td></tr>'."\n";
			
			if($inv['inv_lizq_lld']!='') {echo 'Del Izq: ' . $inv['inv_lizq_lld'];}
			if($inv['inv_lizq_llt']!='') {echo ', Tras Izq: ' . $inv['inv_lizq_llt'];} 
			if($inv['inv_lder_lld']!='') {echo ', Del Der: ' . $inv['inv_lder_lld'];}
			if($inv['inv_lder_llt']!='') {echo ', Tras Der: ' . $inv['inv_lder_llt'];}
			
			echo '			</table>'."\n";
			echo '			<table cellpadding="1" cellspacing="0" border="0" width="100%" class="diagnostico">'."\n";
			echo '				<tr><td colspan="4" style="border-top-width:1px; border-right-width:1px; border-top-style:solid; border-right-style:solid;text-align:center;font-weight:bold;">DIAGNOSTICO</td></tr>'."\n";
			echo '				<tr><td style="width:53px;">Cantidad</td><td colspan="2" style="width:80px;">Descripción</td><td class="cierradiag" style="width:80px;">Precio</td></tr>'."\n";
			for($i=0;$i<13;$i++) {
				echo '				<tr><td></td><td colspan="2"></td><td class="cierradiag"></td></tr>'."\n";
			}
			echo '				<tr><td colspan="2" rowspan="3" style="width:252px;">¿El consumidor suministra las partes, refacciones o materiales para la prestación del servicio de reparación del vehículo?: Sí( ) No( )</td><td style="text-align:right;">Subtotal:</td><td class="cierradiag"></td></tr>'."\n";
			echo '				<tr><td style="text-align:right;">IVA:</td><td class="cierradiag"></td></tr>'."\n";
			echo '				<tr><td style="text-align:right;">Total:</td><td class="cierradiag"></td></tr>'."\n";
			echo '			</table>'."\n";
			echo '			<table cellpadding="0" cellspacing="0" border="0" class="izq" width="100%" style="font-size:0.8em;">'."\n";
			echo '				<tr style="height:3px;"><td></td></tr>'."\n";
			echo '				<tr><td style="width:100%; line-height:12px;">¿El consumidor sí( ) no( ) acepta que el proveedor ceda o transmita a terceros, con fines mercadotécnicos o publicitarios, la información proporcionada por él, con motivo del presente contrato? y ¿Si( ) No( ) acepta que el proveedor le envíe publicidad sobre bienes y servicios?</td></tr>'."\n";
			echo '				<tr style="height:2px;"><td></td></tr>'."\n";
			echo '				<tr><td style="border-style:solid; border-width:1px; vertical-align:top; height:120px;">Observaciones y comentarios: ';
			if($inv['inv_int_obs']!=''){echo 'Interior: ' . $inv['inv_int_obs'] . ' <> ';}
			if($inv['inv_latizq_obs']!='') {echo 'Lado Izquierdo: ' . $inv['inv_latizq_obs'] . ' <> ';}
			if($inv['inv_frontal_obs']!='') {echo 'Frontal: ' . $inv['inv_frontal_obs'] . ' <> ';}
			if($inv['inv_motor_obs']!='') {echo 'Motor: ' . $inv['inv_motor_obs'] . ' <> ';}
			if($inv['inv_latder_obs']!='') {echo 'Lado Derecho: ' . $inv['inv_latder_obs'] . ' <> ';}
			if($inv['inv_post_obs']!='') {echo 'Posterior: ' . $inv['inv_post_obs'] . ' <> ';}
			if($inv['inv_cajuela_obs']!='') {echo 'Cajuela: ' . $inv['inv_cajuela_obs'] . ' <> ';}
			if($inv['inv_toldo_obs']!='') {echo 'Toldo: ' . $inv['inv_toldo_obs'] . ' <> ';}
			if($inv['inv_otros_obs']!='') {echo 'Otros: ' . $inv['inv_otros_obs'];}
			echo '				</td></tr>
			</table>'."\n";
			echo '		</td></tr>'."\n";
			echo '	</table>'."\n";
			echo '			<table cellpadding="2" cellspacing="0" border="1" width="840">
				<tr><td colspan="2" style="text-align:center;">RECEPCION</td><td colspan="2" style="text-align:left;">Fecha y Hora de Entrega:</td></tr>
				<tr style="height:72px;"><td style="text-align:center; width:25%; vertical-align:bottom;">Nombre y firma del cliente</td>
					<td style="text-align:center; width:25%; vertical-align:bottom;"><span style="font-size:0.8em;">Asesor de Servicio<br>' . $usr['nombre'] . ' ' . $usr['apellidos'] . '</span></td>
					<td style="text-align:center; width:25%; vertical-align:bottom;">Nombre y firma del cliente</td>
					<td style="text-align:center; width:25%; vertical-align:bottom;"><span style="font-size:0.8em;">Asesor de Servicio<br>' . $usr['nombre'] . ' ' . $usr['apellidos'] . '</span></td>
				</tr>
				</table>'."\n";
			echo '			<table cellpadding="0" cellspacing="0" border="0" width="840">
				<tr style="height:5px;"><td></td></tr>
				<tr><td style="text-align:left;"><div class="control">';
			$_SESSION['orden_id'] = $orden_id;
			if($_SESSION['rol06']=='1') {
				echo'<a href="javascript:window.print();bloquea(' . $dbpfx . 'subordenes,' . $orden_id . ')"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="Imprimir Hoja de Ingreso" title="Imprimir Hoja de Ingreso"></a> ';
			}
			echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div>
			</td></tr>
		</table>'."\n";
