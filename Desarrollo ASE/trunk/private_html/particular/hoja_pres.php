<?php
	// ---- MODELO PARA TOP DA ----

	include('parciales/numeros-a-letras.php');
	$prega = "SELECT o.orden_fecha_recepcion, c.cliente_nombre, c.cliente_apellidos, c.cliente_telefono1, c.cliente_email FROM " . $dbpfx . "ordenes o, " . $dbpfx . "clientes c WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id LIMIT 1";
	$matra = mysql_query($prega) or die("ERROR: Fallo selección de datode cliente!");
	$cust = mysql_fetch_array($matra);
	$veh = datosVehiculo($orden_id, $dbpfx, '');

	$fecha_cambio = cambioEstatus($orden_id, '4', $dbpfx);

	
	if($taller == ''){ // --- Default ---
		
		$cont_lote = 1;

		if($envio == '1') {
			$contenido .= '		<br>
		<table>
			<tr>
				<td></td>
				<td>
					<div class="contenedor80">'."\n";
		}
		$contenido .= '	
						<form action="presupuestos.php?accion=' . $accion . '" method="post" enctype="multipart/form-data">
						<input type="hidden" name="sin" value="' . $reporte . '" />
						<input type="hidden" name="orden_id" value="' . $orden_id . '" />
						
						<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda body-wrap">
							<tr>
								<td style="width:230px;"><img src="particular/logo-agencia.png" alt="' . $agencia_razon_social . '" height="80"></td>
								<td>';
		$contenido .= '
								</td>
								<td style="width:210px; vertical-align: top; line-height:12px; font-size:11px;">' . $agencia_direccion . '<br>
								Col. ' . $agencia_colonia . '. ' . $agencia_municipio . '<br>
								C.P. ' . $agencia_cp . '. ' . $agencia_estado . '<br>
								Tel. ' . $agencia_telefonos . '</td>
							</tr>
							
							<tr>
								<td style="text-align: right;" colspan="3">
									<div class="control">' . date('Y-m-d', strtotime($fecha_cambio[0]['fecha'])) . '</div>
									Villahermosa, Tab a ';
									if($fecha != ''){
										$contenido .= $fecha . ''."\n";
									} else{
										$contenido .= '<input type="text" name="fecha" size="6"/>'."\n";
									}
		$contenido .= '
								</td>
							</tr>
							<tr>
								<td colspan="3">
									'."\n";

		
		if($reporte != '0') {
				
				$preg_aseg = "SELECT aseguradora_razon_social, aseguradora_calle, aseguradora_ext, aseguradora_int, aseguradora_colonia, aseguradora_municipio, aseguradora_estado, aseguradora_pais, aseguradora_cp, aseguradora_rfc FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $aseguradora . "'";
				$matr_aseg = mysql_query($preg_aseg) or die("ERROR: Fallo selección de aseguradora! " . $preg_aseg);
				$aseg = mysql_fetch_assoc($matr_aseg);
				
				
				$contenido .= '
									' . $aseg['aseguradora_razon_social'] . '<br>
									' . $aseg['aseguradora_calle'] . ' ' . $aseg['aseguradora_ext'] . ' ' . $aseg['aseguradora_int'] . ' ' . $aseg['aseguradora_colonia'] . ' C.P ' . $aseg['aseguradora_cp'] . '<br>
									' . $aseg['aseguradora_municipio'] . ', ' . $aseg['aseguradora_estado'] . ', ' . $aseg['aseguradora_pais'] . '';
			} else {
				$contenido .= 'Trabajos Particulares.';
		} 
		
		$contenido .= '
								</td>
							</tr>
							<tr>
								<td style="width:400px; text-align:center;" colspan="3">
									<h2>';
		if($accion === 'imprimeaut') { $contenido .= 'VALUACION<br>AUTORIZADA'; } else { $contenido .= 'P R E S U P U E S T O'; }
		$contenido .= '
									</h2>
								</td>
							</tr>
							<tr>
								<td>
									Datos de la unidad:<br>
									' . $veh['completo'] . '
								</td>
							</tr>
						</table>
						<table cellpadding="0" cellspacing="0" border="1" class="izquierda body-wrap" width="840">
								<tr class="cabeza_tabla">
									<td>LOTE</td>
									<td>Cantidad</td>
									<td>UNIDAD</td>
									<td>CONCEPTO</td>
									<td style="text-align:right;">UNITARIO</td>
									<td style="text-align:right;">COSTO</td>
								</tr>'."\n";

			while($gsub = mysql_fetch_array($matr)) {
				$preg0 = "SELECT s.sub_orden_id, s.sub_descripcion, s.sub_controlista, s.sub_fecha_asignacion FROM " . $dbpfx . "subordenes s, " . $dbpfx . "orden_productos o WHERE s.orden_id = '$orden_id' AND s.sub_estatus < '130' AND s.sub_area = '" . $gsub['sub_area'] . "' AND s.sub_orden_id = o.sub_orden_id AND ";
				if($accion === 'imprimeaut') { $preg0 .= " o.op_pres IS NULL"; } else { $preg0 .= " o.op_pres = '1' "; }
				$preg0 .= " AND s.sub_reporte = '" . $reporte . "'";
				$preg0 .= " GROUP BY s.sub_orden_id ORDER BY s.sub_area,s.sub_orden_id  ";
				$matr0 = mysql_query($preg0) or die("ERROR: ".$preg0);
				$num_grp = mysql_num_rows($matr0);
//				echo $num_grp;
				if ($num_grp > 0) {
					while($sub = mysql_fetch_array($matr0)) {
						$controlista[$gsub['sub_area']] = $sub['sub_controlista'];
						$fpres[$gsub['sub_area']] = $sub['sub_fecha_asignacion'];
						$preg1 = "SELECT op_cantidad, op_nombre, op_precio, op_tangible, op_item FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND ";
						if($accion === 'imprimeaut') { $preg1 .= " op_pres IS NULL"; } else { $preg1 .= " op_pres = '1' "; }
						$preg1 .= " ORDER BY op_cantidad DESC ";
						$matr1 = mysql_query($preg1) or die("ERROR: ".$preg1);
						//echo $preg1 . '<br>';
						$num_op = mysql_num_rows($matr1);
						if ($num_op > 0) {
							$encab = 0;
							while($op = mysql_fetch_array($matr1)) {
								if($op['op_tangible'] == '1') {
									$items[$gsub['sub_area']][1][] = $op['op_item'].'|'.$op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
								}
								if($op['op_tangible'] == '2') {
									$items[$gsub['sub_area']][2][] = $op['op_item'].'|'.$op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
								}
								if($op['op_tangible'] == '0') {
									$items[$gsub['sub_area']][0][] = $op['op_item'].'|'.$op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
								}
							}
						}
					}
				}
			}
			$total = 0;
			$horas = 0;
			$moarr = array();
			$paarr = array();
			foreach($items as $j => $u) {
				
				$subarea = 0;
				$submo = 0;
				$mo_tmp = '';
				$recon = '';
				foreach($u as $k => $v) {
					$subtotal = 0;
					if($k == '0') {
						foreach($v as $l => $w) {
							$parte = explode('|', $w);
							$subtotal = round(($parte[1] * $parte[3]), 2);
							$horas = $horas + $parte[1];
							$mo_tmp .= '								
								<tr>
									<td style="padding-left:4px; padding-right:4px;">' . $cont_lote . '</td>
									<td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td>
									<td style="padding-left:4px; padding-right:4px;">SERVICIO</td>
									<td style="padding-left:4px; padding-right:4px;">' . $parte[2] . '</td>
									<td style="text-align:right; padding-left:4px; padding-right:4px;">
										' . number_format($parte[3],2) . '
									</td>
									<td style="text-align:right; padding-left:4px; padding-right:4px;">
										' . number_format($subtotal,2) . '
									</td>
								</tr>'."\n";
							$moarr[$j][] = array($parte[0], $parte[1], $parte[2], $subtotal);
							$submo = $submo + $subtotal;
							$cont_lote++;
						}
					}
					if($k > '0') {
						foreach($v as $l => $w) {
							$parte = explode('|', $w);
							$subtotal = round(($parte[1] * $parte[3]), 2);
							$recon .= '								
								<tr>
									<td style="padding-left:4px; padding-right:4px;">' . $cont_lote . '</td>
									<td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td>
									<td style="padding-left:4px; padding-right:4px;">PZA</td>
									<td style="padding-left:4px; padding-right:4px;">' . $parte[2] . '</td>
									<td style="text-align:right; padding-left:4px; padding-right:4px;">
										' . number_format($parte[3],2) . '
									</td>
									<td style="text-align:right; padding-left:4px; padding-right:4px;">
										' . number_format($subtotal,2) . '
									</td>
								</tr>'."\n";
							$paarr[$j][] = array($parte[0], $parte[1], $parte[2], $parte[3]);
							$subarea = $subarea + $subtotal;
							$cont_lote++;
						}
					}
				}
			
				$contenido .= $mo_tmp;
				
				$contenido .= $recon;
				
				$subarea = $subarea + $submo;
		
				$total = $total + $subarea;
			}
			$iva = 0;
			$dias = intval(($horas / 16) + 0.999999);
			if($pciva != '1') {
				$iva = round(($total * 0.16), 2);
			}
			$gtotal = $total + $iva;
			
			$contenido .= '							
								<tr class="cabeza_tabla">
									<td colspan="4">Observaciones: ';
			if($diasrep == 1) { $contenido .= 'Días para reparación: ' . $dias; }
			
			$contenido .= '
									</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">
										Sub Total
									</td>
									<td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">
										' . number_format($total,2) . '
									</td>
								</tr>
								<tr class="cabeza_tabla">
									<td colspan="4"></td>
									<td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">';

			if($pciva != '1') { $contenido .= 'IVA (16%)'; }
			$contenido .= '
									</td>
									<td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">';

			if($pciva != '1') { $contenido .= number_format($iva,2); }
			$contenido .= '
									</td>
								</tr>'."\n";

			$letra = strtoupper(letras2($gtotal));
			
			$contenido .= '							
								<tr class="cabeza_tabla">
									<td colspan="4">
										' . $letra . '
									</td>
									<td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">
										TOTAL
									</td>
									<td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">
										' . number_format($gtotal,2) . '
									</td>
								</tr>
							</table>
							<br><br><br>
							<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda body-wrap">
								<tr>
									<td colspan="6">
										CONDICIONES GENERALES
									</td>
								</tr>
								<tr>
									<td colspan="2">
										1-ENTREGA: ';
										if($entrega != ''){
											$contenido .= $entrega . ''."\n";
										} else{
											$contenido .= '<input type="text" name="entrega" size="6"/>'."\n";
										}
			$contenido .= '
									</td>
									<td colspan="2">
										2-PAGO: ';
										if($pago != ''){
											$contenido .= $pago . ''."\n";
										} else{
											$contenido .= '<input type="text" name="pago" size="6"/>'."\n";
										}
			$contenido .= '
									</td>
									<td colspan="2">
										3-VIGENCIA DE PRESUPUESTO: ';
										if($vigencia != ''){
											$contenido .= $vigencia . ''."\n";
										} else{
											$contenido .= '<input type="text" name="vigencia" size="6"/>'."\n";
										}
			$contenido .= '
									</td>
								</tr>
								<tr>
									<td colspan="2">
										4-GARANTÍA: ';
										if($garantia != ''){
											$contenido .= $garantia . ''."\n";
										} else{
											$contenido .= '<input type="text" name="garantia" size="6"/>'."\n";
										}
			$contenido .= '
									</td>
									<td colspan="2">
										5-ENTREGA EN: ';
										if($entrega_en != ''){
											$contenido .= $entrega_en . ''."\n";
										} else{
											$contenido .= '<input type="text" name="entrega_en" size="6"/>'."\n";
										}
			
		
			$contenido .= '
									</td>
								</tr>
							</table>'."\n";
		
			$contenido .= '
							<table>
								<tr>
									<td>
										<td colspan="2">
										NÚMERO DE INVENARIO: ';
										if($num_inventario != ''){
											$contenido .= $num_inventario . ''."\n";
										} else{
											$contenido .= '<input type="text" name="num_inventario" size="6"/>'."\n";
										}
		
			$contenido .= '
									</td>
								</tr>
								<tr>
									<td>
										<td colspan="2">
										NÚMERO ECONÓMICO: ';
										if($num_economico != ''){
											$contenido .= $num_economico . ''."\n";
										} else{
											$contenido .= '<input type="text" name="num_economico" size="6"/>'."\n";
										}
		
			$contenido .= '
									</td>
								</tr>
							
								<tr>
									<td colspan="2"></td>
									<td style="width:400px; text-align:center;" colspan="2">
										<br><br>
										<h2>
											CARLOS HECHEM MARTÍNEZ DE ESCOBAR
										</h2>
									</td>
								</tr>
								<tr>
									<td colspan="2"></td>
									<td style="width:400px; text-align:center;" colspan="2">
										<br>
										<br>
									</td>
								</tr>
								<tr>
									<td colspan="2"></td>
									<td style="width:400px; text-align:center;" colspan="2">
										<h2>
											E N C A R G A D O
										</h2>
									</td>
								</tr>
							</table>
							'."\n";

			
			echo $contenido;	
	}

	elseif($taller == 2){
		
		echo '
		
		<form action="presupuestos.php?accion=' . $accion . '" method="post" enctype="multipart/form-data">
			<input type="hidden" name="sin" value="' . $reporte . '" />
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<input type="hidden" name="taller" value="' . $taller . '" />
			<div class="control">
				<b>% de incremento en presupuesto:</b><br>
				<input type="number" name="incremento"/>
			</div>
		'."\n";
		
		echo '		
		<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda">
			<tr>
				<td style="width:230px;"></td>
				<td style="width:400px; text-align:center;"><h2>PRESUPUESTO</h2>
				</td>
				<td style="width:210px; vertical-align: top; line-height:12px;"></td>
			</tr>
			<tr>
				<td><br>
					Fecha: ';
						if($fecha != ''){
							echo $fecha . ''."\n";
						} else{
							echo '<input type="text" name="fecha" size="6"/>'."\n";
						}
		echo '
				</td>
				<td style="text-align: center;">' . $veh['completo'] . ' </td>
			</tr>
		</table>'."\n";
		
			echo '			
			
			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">' . "\n";
		
			while($gsub = mysql_fetch_array($matr)) {
				$preg0 = "SELECT s.sub_orden_id, s.sub_descripcion, s.sub_controlista, s.sub_fecha_asignacion FROM " . $dbpfx . "subordenes s, " . $dbpfx . "orden_productos o WHERE s.orden_id = '$orden_id' AND s.sub_estatus < '130' AND s.sub_area = '" . $gsub['sub_area'] . "' AND s.sub_orden_id = o.sub_orden_id AND ";
				if($accion === 'imprimeaut') { $preg0 .= " o.op_pres IS NULL"; } else { $preg0 .= " o.op_pres = '1' "; }
				$preg0 .= " AND s.sub_reporte = '" . $reporte . "'";
				$preg0 .= " GROUP BY s.sub_orden_id ORDER BY s.sub_area,s.sub_orden_id  ";
				$matr0 = mysql_query($preg0) or die("ERROR: ".$preg0);
				$num_grp = mysql_num_rows($matr0);
//				echo $num_grp;
				if ($num_grp > 0) {
					while($sub = mysql_fetch_array($matr0)) {
						$controlista[$gsub['sub_area']] = $sub['sub_controlista'];
						$fpres[$gsub['sub_area']] = $sub['sub_fecha_asignacion'];
						$preg1 = "SELECT op_cantidad, op_nombre, op_precio, op_tangible, op_item FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND ";
						if($accion === 'imprimeaut') { $preg1 .= " op_pres IS NULL"; } else { $preg1 .= " op_pres = '1' "; }
						$preg1 .= " ORDER BY op_precio ";
						$matr1 = mysql_query($preg1) or die("ERROR: ".$preg1);
						$num_op = mysql_num_rows($matr1);
						if ($num_op > 0) {
							$encab = 0;
							while($op = mysql_fetch_array($matr1)) {
								if($op['op_tangible'] == '1') {
									$items[$gsub['sub_area']][1][] = $op['op_item'].'|'.$op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
								}
								if($op['op_tangible'] == '2') {
									$items[$gsub['sub_area']][2][] = $op['op_item'].'|'.$op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
								}
								if($op['op_tangible'] == '0') {
									$items[$gsub['sub_area']][0][] = $op['op_item'].'|'.$op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
								}
							}
						}
					}
				}
			}
			$total = 0;
			$horas = 0;
			foreach($items as $j => $u) {

				$subarea = 0;
				$submo = 0;
				$mo_tmp = '';
				$recon = '';
				foreach($u as $k => $v) {
					$subtotal = 0;
					if($k == '0') {
						foreach($v as $l => $w) {
							$parte = explode('|', $w);
							
							if($incremento != ''){
								// --- calcular precios ---
								$cant = "1." . $incremento;
								$unitario = $parte[3] * $cant;
								
							} else{
								$unitario = $parte[3];
							}
							
							$subtotal = round(($parte[1] * $unitario), 2);
							
							$horas = $horas + $parte[1];
							
							$mo_tmp .= '				
							<tr>
								<td style="padding-left:4px; padding-right:4px;">' . $parte[0] . '</td>
								<td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td>
								<td style="padding-left:4px; padding-right:4px;">' . $parte[2] . '</td>
								<td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($unitario , 2) . '</td>
								<td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td>
							</tr>'."\n";
							$submo = $submo + $subtotal;
						}
					}
					if($k > '0') {
						foreach($v as $l => $w) {
							$parte = explode('|', $w);
							
							if($incremento != ''){
								// --- calcular precios ---
								$cant = "1." . $incremento;
								$unitario = $parte[3] * $cant;
								
							} else{
								$unitario = $parte[3];
							}
							
							$subtotal = round(($parte[1] * $unitario), 2);
							
							$recon .= '				
								<tr>
									<td style="padding-left:4px; padding-right:4px;">' . $parte[0] . '</td>
									<td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td>
									<td style="padding-left:4px; padding-right:4px;">' . $parte[2] . '</td>
									<td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($unitario,2) . '</td>
									<td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td>
								</tr>'."\n";
							$subarea = $subarea + $subtotal;
						}
					}
				}
				echo '				<tr><td colspan="3">'."\n";
				echo '					<table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:10px;">'."\n";
				echo '						<tr><td colspan="5">Mano de Obra</td></tr>'."\n";
				echo '						<tr style="text-align:center;"><td>Item</td><td>Cantidad</td><td>Nombre</td><td style="text-align:right;">Precio Unitario</td><td style="text-align:right;">Subtotal</td></tr>'."\n";
				echo $mo_tmp;
				echo '						<tr><td colspan="4" style="text-align:right;">Subtotal</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($submo,2) . '</td></tr>'."\n";
				echo '					</table>'."\n";
				echo '				</td><td colspan="3">'."\n";
				echo '					<table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:10px;">'."\n";
				echo '						<tr><td colspan="5">';
				if($k == '1') { echo 'Refacciones'; } elseif($k == '2') { echo 'Consumibles'; } else { echo 'Sin refacciones o consumibles'; }
				echo '</td></tr>'."\n";
				echo '						<tr style="text-align:center;"><td>Item</td><td>Cantidad</td><td>Nombre</td><td style="text-align:right;">Precio Unitario</td><td style="text-align:right;">Subtotal</td></tr>'."\n";
				echo $recon;
				echo '						<tr><td colspan="4" style="text-align:right;">Subtotal</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
				echo '					</table>'."\n";
				echo '				</td></tr>'."\n";
				$subarea = $subarea + $submo;

				echo '				<tr><td colspan="4" style="text-align:center; vertical-align:bottom; padding-left:4px; padding-right:4px; height:60px;">Nombre y Firma de Responsable de ' . constant('NOMBRE_AREA_'.$j) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">Sub total de ' . constant('NOMBRE_AREA_'.$j) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
				$total = $total + $subarea;
			}
			$dias = intval(($horas / 16) + 0.999999);
			$iva = round(($total * 0.16), 2);
			$gtotal = $total + $iva;
			
			echo '				<tr class="cabeza_tabla"><td colspan="4">Observaciones: ';
			if($diasrep == 1) { echo 'Días para reparación: ' . $dias; }
			echo '</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Sub Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($total,2) . '</td></tr>'."\n";
			echo '				<tr class="cabeza_tabla"><td colspan="4"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">IVA (16%)</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($iva,2) . '</td></tr>'."\n";
			echo '				<tr class="cabeza_tabla"><td colspan="4"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Gran Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($gtotal,2) . '</td></tr>'."\n";
			echo ' </table>'."\n";
		
			echo '
							<table>
								<tr>
									<td>
										<td colspan="2">
										Num. inventario:';
										if($num_inventario != ''){
											echo $num_inventario . ''."\n";
										} else{
											echo '<input type="text" name="num_inventario" size="6"/>'."\n";
										}
		
			echo '
									</td>
									<td>
										<td colspan="2">
										Num economico: ';
										if($num_economico != ''){
											echo $num_economico . ''."\n";
										} else{
											echo '<input type="text" name="num_economico" size="6"/>'."\n";
										}
		
		
			echo '
									</td>
								</tr>
							</table>
			<br>
							<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda body-wrap">
								<tr>
									<td colspan="6">
										CONDICIONES:
									</td>
								</tr>
								<tr>'."\n";
			echo '
									</td>
									<td colspan="2">
										Pago= ';
										if($pago != ''){
											echo $pago . ''."\n";
										} else{
											echo '<input type="text" name="pago" size="6"/>'."\n";
										}
			echo '			
									</td>
									<td colspan="2">
										Entrega= ';
										if($entrega != ''){
											echo  $entrega . ''."\n";
										} else{
											echo '<input type="text" name="entrega" size="6"/>'."\n";
										}
			echo '
									<td colspan="2">
										Vigencia= ';
										if($vigencia != ''){
											echo $vigencia . ''."\n";
										} else{
											echo '<input type="text" name="vigencia" size="6"/>'."\n";
										}
			echo '
									</td>
								</tr>
								<tr>'."\n";
			echo '
									<td colspan="2">
										Lugar Entrega= ';
										if($entrega_en != ''){
											echo $entrega_en . ''."\n";
										} else{
											echo '<input type="text" name="entrega_en" size="6"/>'."\n";
										}
			echo '
									</td>
									<td colspan="2">
										Garantía= ';
										if($garantia != ''){
											echo $garantia . ''."\n";
										} else{
											echo '<input type="text" name="garantia" size="6"/>'."\n";
										}
			echo '
									</td>
								</tr>
								<tr>
									<td style="width:400px; text-align:center;" colspan="6">
										<br><br>'."\n";
									if($nombre_resp != ''){
										echo '
										<h2>
											' . $nombre_resp . '
										</h2>'."\n";
									} else{
										echo '<input type="text" name="nombre_resp" size="24"/>'."\n";
									}
			echo '
									</td>
								</tr>
								<tr>
									<td style="width:400px; text-align:center;" colspan="6">
										<br>
										<br>
									</td>
								</tr>
								<tr>
									<td style="width:400px; text-align:center;" colspan="6">
										<h2>
											E N C A R G A D O
										</h2>
									</td>
								</tr>
							</table>
							'."\n";
		
	}

	elseif($taller == 3){
		
		echo '
		
		<form action="presupuestos.php?accion=' . $accion . '" method="post" enctype="multipart/form-data">
			<input type="hidden" name="sin" value="' . $reporte . '" />
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<input type="hidden" name="taller" value="' . $taller . '" />
			
			<div class="control">
				<b>% de incremento en presupuesto:</b><br>
				<input type="number" name="incremento"/>
			</div>
		'."\n";
		
		echo '		
		<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda">
			<tr>
				<td style="width:230px;"></td>
				<td style="width:400px; text-align:center;"><h2>P R E S U P U E S T O</h2>
				</td>
				<td style="width:210px; vertical-align: top; line-height:12px;"></td>
			</tr>
			<tr>
				<td><br>
					Fecha: ';
						if($fecha != ''){
							echo $fecha . ''."\n";
						} else{
							echo '<input type="text" name="fecha" size="6"/>'."\n";
						}
				echo '
				</td>
				<td style="text-align: center;"></td>
			</tr>
		</table>'."\n";
		
			echo '			
			
			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">' . "\n";
		
			while($gsub = mysql_fetch_array($matr)) {
				$preg0 = "SELECT s.sub_orden_id, s.sub_descripcion, s.sub_controlista, s.sub_fecha_asignacion FROM " . $dbpfx . "subordenes s, " . $dbpfx . "orden_productos o WHERE s.orden_id = '$orden_id' AND s.sub_estatus < '130' AND s.sub_area = '" . $gsub['sub_area'] . "' AND s.sub_orden_id = o.sub_orden_id AND ";
				if($accion === 'imprimeaut') { $preg0 .= " o.op_pres IS NULL"; } else { $preg0 .= " o.op_pres = '1' "; }
				$preg0 .= " AND s.sub_reporte = '" . $reporte . "'";
				$preg0 .= " GROUP BY s.sub_orden_id ORDER BY s.sub_area,s.sub_orden_id  ";
				$matr0 = mysql_query($preg0) or die("ERROR: ".$preg0);
				$num_grp = mysql_num_rows($matr0);
//				echo $num_grp;
				if ($num_grp > 0) {
					while($sub = mysql_fetch_array($matr0)) {
						$controlista[$gsub['sub_area']] = $sub['sub_controlista'];
						$fpres[$gsub['sub_area']] = $sub['sub_fecha_asignacion'];
						$preg1 = "SELECT op_cantidad, op_nombre, op_precio, op_tangible, op_item FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND ";
						if($accion === 'imprimeaut') { $preg1 .= " op_pres IS NULL"; } else { $preg1 .= " op_pres = '1' "; }
						$preg1 .= " ORDER BY op_tangible,op_nombre ";
						$matr1 = mysql_query($preg1) or die("ERROR: ".$preg1);
						$num_op = mysql_num_rows($matr1);
						if ($num_op > 0) {
							$encab = 0;
							while($op = mysql_fetch_array($matr1)) {
								if($op['op_tangible'] == '1') {
									$items[$gsub['sub_area']][1][] = $op['op_item'].'|'.$op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
								}
								if($op['op_tangible'] == '2') {
									$items[$gsub['sub_area']][2][] = $op['op_item'].'|'.$op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
								}
								if($op['op_tangible'] == '0') {
									$items[$gsub['sub_area']][0][] = $op['op_item'].'|'.$op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
								}
							}
						}
					}
				}
			}
			$total = 0;
			$horas = 0;
			foreach($items as $j => $u) {

				$subarea = 0;
				$submo = 0;
				$mo_tmp = '';
				$recon = '';
				foreach($u as $k => $v) {
					$subtotal = 0;
					if($k == '0') {
						foreach($v as $l => $w) {
							$parte = explode('|', $w);
							
							if($incremento != ''){
								// --- calcular precios ---
								$cant = "1." . $incremento;
								$unitario = $parte[3] * $cant;
								
							} else{
								$unitario = $parte[3];
							}
							
							$subtotal = round(($parte[1] * $unitario), 2);
							
							$horas = $horas + $parte[1];
							
							$mo_tmp .= '				
							<tr>
								<td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td>
								<td style="padding-left:4px; padding-right:4px;">' . $parte[2] . '</td>
								<td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($unitario , 2) . '</td>
								<td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td>
							</tr>'."\n";
							$submo = $submo + $subtotal;
						}
					}
					if($k > '0') {
						foreach($v as $l => $w) {
							$parte = explode('|', $w);
							
							if($incremento != ''){
								// --- calcular precios ---
								$cant = "1." . $incremento;
								$unitario = $parte[3] * $cant;
								
							} else{
								$unitario = $parte[3];
							}
							
							$subtotal = round(($parte[1] * $unitario), 2);
							
							$recon .= '				
								<tr>
									<td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td>
									<td style="padding-left:4px; padding-right:4px;">' . $parte[2] . '</td>
									<td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($unitario,2) . '</td>
									<td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td>
								</tr>'."\n";
							$subarea = $subarea + $subtotal;
						}
					}
				}
				echo '				<tr><td colspan="3">'."\n";
				echo '					<table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:10px;">'."\n";
				echo '						<tr><td colspan="5">Mano de obra</td></tr>'."\n";
				echo '						<tr style="text-align:center;"><td>unidades</td><td>concepto</td><td style="text-align:right;">unitario</td><td style="text-align:right;">subtotal</td></tr>'."\n";
				echo $mo_tmp;
				echo '						<tr><td colspan="4" style="text-align:right;">Subtotal</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($submo,2) . '</td></tr>'."\n";
				echo '					</table>'."\n";
				echo '				</td><td colspan="3">'."\n";
				echo '					<table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:10px;">'."\n";
				echo '						<tr><td colspan="5">';
				if($k == '1') { echo 'Refacciones'; } elseif($k == '2') { echo 'Consumibles'; } else { echo 'Sin refacciones o consumibles'; }
				echo '</td></tr>'."\n";
				echo '						<tr style="text-align:center;"><td>unidades</td><td>refacción</td><td style="text-align:right;">unitario</td><td style="text-align:right;">subtotal</td></tr>'."\n";
				echo $recon;
				echo '						<tr><td colspan="4" style="text-align:right;">Subtotal</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
				echo '					</table>'."\n";
				echo '				</td></tr>'."\n";
				$subarea = $subarea + $submo;

				echo '				<tr><td colspan="4" style="text-align:center; vertical-align:bottom; padding-left:4px; padding-right:4px; height:60px;"></td><td style="text-align:right; padding-left:4px; padding-right:4px;">Sub total de ' . constant('NOMBRE_AREA_'.$j) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
				$total = $total + $subarea;
			}
			$dias = intval(($horas / 16) + 0.999999);
			$iva = round(($total * 0.16), 2);
			$gtotal = $total + $iva;
			
			echo '				<tr><td colspan="4">';
			if($diasrep == 1) { echo 'Días para reparación: ' . $dias; }
			echo '</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">SUBTOTAL:</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($total,2) . '</td></tr>'."\n";
			echo '				<tr><td colspan="4"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">(16%) IVA:</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($iva,2) . '</td></tr>'."\n";
			echo '				<tr><td colspan="4"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">TOTAL:</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($gtotal,2) . '</td></tr>'."\n";
			echo ' </table>'."\n";
		
			echo '
							<table>
								<tr>
									<td>
										<td colspan="2">
										Numero economico ';
										if($num_economico != ''){
											echo '<b>' . $num_economico . '</b>'."\n";
										} else{
											echo '<input type="text" name="num_economico" size="6"/>'."\n";
										}
		
			echo '
									</td>
									<td>
										<td colspan="2">
										Numero inventario ';
										if($num_inventario != ''){
											echo '<b>' . $num_inventario . '</b>'."\n";
										} else{
											echo '<input type="text" name="num_inventario" size="6"/>'."\n";
										}
		
			echo '
									</td>
								</tr>
							</table>
							<br>
							<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda body-wrap">
								<tr>
									<td colspan="6">
										Condiciones
									</td>
								</tr>
								<tr>'."\n";
			echo '
									</td>
									<td colspan="2">
										pago $ ';
										if($pago != ''){
											echo $pago . ''."\n";
										} else{
											echo '<input type="text" name="pago" size="6"/>'."\n";
										}
			echo '			
									</td>
									<td colspan="2">
										entrega ';
										if($entrega != ''){
											echo  $entrega . ''."\n";
										} else{
											echo '<input type="text" name="entrega" size="6"/>'."\n";
										}
			echo '
									<td colspan="2">
										vigencia ';
										if($vigencia != ''){
											echo $vigencia . ''."\n";
										} else{
											echo '<input type="text" name="vigencia" size="6"/>'."\n";
										}
			echo '
									</td>
								</tr>
								<tr>'."\n";
			echo '
									<td colspan="2">
										lugar de entrega ';
										if($entrega_en != ''){
											echo $entrega_en . ''."\n";
										} else{
											echo '<input type="text" name="entrega_en" size="6"/>'."\n";
										}
			echo '
									</td>
									<td colspan="2">
										garantía ';
										if($garantia != ''){
											echo $garantia . ''."\n";
										} else{
											echo '<input type="text" name="garantia" size="6"/>'."\n";
										}
			echo '
									</td>
								</tr>
								<tr>
									<td style="width:400px; text-align:center;" colspan="6">
										<br><br>'."\n";
									if($nombre_resp != ''){
										echo '
										<h2>
											' . $nombre_resp . '
										</h2>'."\n";
									} else{
										echo '<input type="text" name="nombre_resp" size="24"/>'."\n";
									}
			echo '
									</td>
								</tr>
								<tr>
									<td style="width:400px; text-align:center;" colspan="6">
										<br>
										<br>
									</td>
								</tr>
								<tr>
									<td style="width:400px; text-align:center;" colspan="6">
										<h2>
											RESPONSABLE
										</h2>
									</td>
								</tr>
							</table>
							'."\n";
		
	}

		
		

			
			
			echo '		<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="840">
							<tr>
								<td><div class="control"><input type="submit" name="confirmar" value="Confirmar Datos" /></div></td>
								<td colspan="3" style="height:15px;"></td>
							</tr>
							<tr>
								<td colspan="4" style="text-align:left;">
									<div class="control">
										<a href="presupuestos.php?accion=consultar&orden_id=' . $orden_id . '#' . $sub_orden_id . '">
											<img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo">
										</a>&nbsp;
										<a href="javascript:window.print()">
											<img src="idiomas/' . $idioma . '/imagenes/imprimir-presupuesto.png" alt="Imprimir Todas las SOT de la OT" title="Imprimir Todas las SOT de la OT">
										</a>&nbsp;
										<a href="presupuestos.php?accion=' . $accion . '&sin=' . $reporte . '&orden_id=' . $orden_id . '" ><big>TALLER TOP</big></a>&nbsp;
										<a href="presupuestos.php?accion=' . $accion . '&taller=2&sin=' . $reporte . '&orden_id=' . $orden_id . '" ><big>TALLER 2</big></a>&nbsp;
										<a href="presupuestos.php?accion=' . $accion . '&taller=3&sin=' . $reporte . '&orden_id=' . $orden_id . '" ><big>TALLER 3</big></a>
									</div>
								</td>
							</tr>
						</table>
						</form>
						'."\n";



?>
