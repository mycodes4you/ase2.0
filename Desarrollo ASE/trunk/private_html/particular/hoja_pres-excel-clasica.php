	<?php

		$prega = "SELECT o.orden_fecha_recepcion, o.orden_torre, o.orden_asesor_id, c.cliente_nombre, c.cliente_apellidos, c.cliente_telefono1, c.cliente_email, c.cliente_empresa_id FROM " . $dbpfx . "ordenes o, " . $dbpfx . "clientes c WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id LIMIT 1";
		$matra = mysql_query($prega) or die("ERROR: Fallo selección de datode cliente!");
		$cust = mysql_fetch_array($matra);
		$veh = datosVehiculo($orden_id, $dbpfx);
		$inipres = cambioEstatus($orden_id, 27, $dbpfx);
		$hacepres = cambioEstatus($orden_id, 27, $dbpfx);
		$inicot = cambioEstatus($orden_id, 28, $dbpfx);
		$finpres = end($inicot);
		$pregem = "SELECT empresa_razon_social FROM " . $dbpfx . "empresas WHERE empresa_id = '" . $cust['cliente_empresa_id'] . "'";
		$matrem = mysql_query($pregem) or die("ERROR: Fallo selección de datode cliente!");
		$emp = mysql_fetch_array($matrem);
		$hoy = date('Y-m-d H:i:s');
		
		
//		$fincot = cambioEstatus($orden_id, 29, $dbpfx);
//		$fincot = end($fincot);
//		print_r($veh);
		/*
		echo '		<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda">
			<tr>
				<td style="width:230px;"><img src="particular/logo-agencia.png" alt="' . $agencia_razon_social . '" height="80"></td>
				<td style="width:400px; text-align:center;"><h2>';
		if($accion === 'imprimeaut') { echo 'VALUACION<br>AUTORIZADA'; } else { echo 'PRESUPUESTO'; }
		echo '</h2>
				</td>
				<td style="width:210px; vertical-align: top; line-height:12px;">' . $agencia_direccion . '<br>
				Col. ' . $agencia_colonia . '. ' . $agencia_municipio . '<br>
				C.P. ' . $agencia_cp . '. ' . $agencia_estado . '<br>
				Tel. ' . $agencia_telefonos . '</td>
			</tr>'."\n";
		echo '			<tr><td colspan="3">'."\n";
		echo '<span style="font-size:14px;"><strong>';
		if($reporte != '0' && $reporte != '') {
			echo 'Aseguradora ' . constant('ASEGURADORA_NIC_'.$aseguradora);
		} else {
			echo 'Trabajos Particulares.';
		} 
		echo '</strong></span></td></tr>'."\n";		
		echo '			<tr><td colspan="2">Contacto: ' . $cust['cliente_nombre'] . ' ' . $cust['cliente_apellidos'] . ' Tel. ' . $cust['cliente_telefono1'] . '</td><td>';
		if($reporte != '0' && $reporte != '') { echo 'Siniestro: ' . $reporte; } else { echo 'Particular'; } 
		echo '</td></tr>'."\n";
		echo '			<tr><td colspan="2">Email: ' . $cust['cliente_email'] . '</td><td>';
		if($reporte != '0' && $reporte != '') { echo 'Póliza: ' . $poliza; } else { echo ''; } 		
		echo '</td></tr>'."\n";
		echo '			<tr><td colspan="2">Fecha de Ingreso: ' . $cust['orden_fecha_recepcion'] . '</td><td>Torre: ' . $cust['orden_torre'];
		echo '</td></tr>'."\n";
		echo '			<tr><td colspan="2">Inicio de Presupuesto: ';
		if($inipres[0]['fecha'] != '' && $inipres[0]['fecha'] != '0000-00-00 00:00:00')  { $fechainipres = date('Y-m-d H:i', strtotime($inipres[0]['fecha'])); echo $fechainipres;}
		echo ' por ' . $usr[$inipres[0]['usuario']]['nombre'] . '</td><td>OT: ' . $orden_id . '</td></tr>'."\n";
//		echo '			<tr><td colspan="3">Presupuesto terminaldo el : ' . date('Y-m-d H:i', strtotime($inipres[0]['fecha'])) . '</td></tr>'."\n";
		echo '			<tr><td colspan="3">Presupuesto Terminado: ';
		if($finpres['fecha'] != '' && $finpres['fecha'] != '0000-00-00 00:00:00') { $fechafinpres = date('Y-m-d H:i', strtotime($finpres['fecha'])); echo $fechafinpres;}
		echo ' por ' . $usr[$finpres['usuario']]['nombre'] . '</td></tr>'."\n";
		echo '			<tr><td colspan="3">Propiedad de : ' . $emp['empresa_razon_social'] . '</td></tr>'."\n";
		echo '			<tr><td colspan="3"><span><strong>Unidad: ' . $veh['tipo'] . ' Marca: ' . $veh['marca'] . ' Color: ' . $veh['color'] . ' Año: ' . $veh['modelo'] . ' PLACAS: ' . $veh['placas'] . ' Subtipo: ' . $veh['subtipo'] . ' VIN: ' . $veh['serie'] . '</strong></span></td></tr>'."\n";
		echo '		</table>'."\n";
		$diasrep = 1;
			echo '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">' . "\n";
			while($gsub = mysql_fetch_array($matr)) {
				$preg0 = "SELECT s.sub_orden_id, s.sub_controlista, s.sub_fecha_asignacion FROM " . $dbpfx . "subordenes s, " . $dbpfx . "orden_productos o WHERE s.orden_id = '$orden_id' AND s.sub_estatus < '130' AND s.sub_area = '" . $gsub['sub_area'] . "' AND s.sub_orden_id = o.sub_orden_id AND ";
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
						$preg1 .= " ORDER BY op_tangible,op_item ";
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
			$moarr = array();
			$paarr = array();
			foreach($items as $j => $u) {
				echo '				<tr class="cabeza_tabla"><td colspan="5">Presupuesto de ' . constant('NOMBRE_AREA_'.$j);
				echo '</td></tr>'."\n";
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
							$mo_tmp .= '				<tr><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:center; padding-left:4px; padding-right:4px;">' . $parte[0] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:center; padding-left:4px; padding-right:4px;">' . $parte[1] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:left; padding-left:4px; padding-right:4px;">' . $parte[2] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:right; padding-left:4px; padding-right:4px;">' . number_format($parte[3],2) . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td></tr>'."\n";
							$moarr[$j][] = array($parte[0], $parte[1], $parte[2], $parte[3]);
							$submo = $submo + $subtotal;
						}
					}
					if($k > '0') {
						foreach($v as $l => $w) {
							$parte = explode('|', $w);
							$subtotal = round(($parte[1] * $parte[3]), 2);
							$recon .= '				<tr><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:center; padding-left:4px; padding-right:4px;">' . $parte[0] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:center; padding-left:4px; padding-right:4px;">' . $parte[1] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:left; padding-left:4px; padding-right:4px;">' . $parte[2] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:right; padding-left:4px; padding-right:4px;">' . number_format($parte[3],2) . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td></tr>'."\n";
							$paarr[$j][] = array($parte[0], $parte[1], $parte[2], $parte[3]);
							$subarea = $subarea + $subtotal;
						}
					}
				}
				echo '				<tr><td colspan="5">'."\n";
				echo '					<table cellpadding="0" cellspacing="0" border="0" width="100%">'."\n";
				echo '						<tr><td colspan="5">';
				if($k == '1') { echo 'Refacciones'; } elseif($k == '2') { echo 'Consumibles'; } else { echo 'Sin refacciones o consumibles'; }
				echo '</td></tr>'."\n";
				echo '						<tr style="text-align:center;"><td>Partida</td><td>Cantidad</td><td>Nombre</td><td style="text-align:right;">Precio Unitario</td><td style="text-align:right;">Subtotal</td></tr>'."\n";
				echo $recon;
				echo '						<tr><td colspan="4" style="text-align:right;">Subtotal de ';
				if($k == '1') { echo 'Refacciones'; } elseif($k == '2') { echo 'Consumibles'; } else { echo 'Sin refacciones o consumibles'; }
				echo '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
				echo '						<tr><td colspan="5">Mano de Obra</td></tr>'."\n";
				echo '						<tr style="text-align:center;"><td>Partida</td><td>Cantidad</td><td>Nombre</td><td style="text-align:right;">Precio Unitario</td><td style="text-align:right;">Subtotal</td></tr>'."\n";
				echo $mo_tmp;
				echo '						<tr><td colspan="4" style="text-align:right;">Subtotal de Mano de Obra</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($submo,2) . '</td></tr>'."\n";
				$subarea = $subarea + $submo;
				echo '						<tr><td colspan="3"></td><td style="text-align:right; padding-left:4px; padding-right:4px;">Sub total de ' . constant('NOMBRE_AREA_'.$j) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
				echo '					</table>'."\n";
				echo '				</td></tr>'."\n";

				$total = $total + $subarea;
			}
			$dias = intval(($horas / 16) + 0.999999);
			$iva = round(($total * 0.16), 2);
			$gtotal = $total + $iva;
			
			echo '				<tr class="cabeza_tabla"><td colspan="3">Observaciones: ';
			if($diasrep == 1) { echo 'Días para reparación: ' . $dias; }
			echo '</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Sub Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($total,2) . '</td></tr>'."\n";
			echo '				<tr class="cabeza_tabla"><td colspan="3"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">IVA (16%)</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($iva,2) . '</td></tr>'."\n";
			echo '				<tr class="cabeza_tabla"><td colspan="3"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Gran Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($gtotal,2) . '</td></tr>'."\n";
			echo ' </table>'."\n";
			*/

		//echo = $area;
		//echo = $sub_orden_id;

		$contenido = '
		<br>
		<tr>
		<td></td>
		<td>
		<div class="contenedor80">
		<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda body-wrap">
			<tr>
				<td style="width:230px;"><img src="particular/logo-agencia.png" alt="' . $agencia_razon_social . '" height="80"></td>
				<td style="width:400px; text-align:center;"><h2>';
		if($accion === 'imprimeaut') { $contenido .= 'VALUACION<br>AUTORIZADA'; } else { $contenido .= 'PRESUPUESTO'; }
		$contenido .= '</h2>
				</td>
				<td style="width:210px; vertical-align: top; line-height:12px; font-size:11px;">' . $agencia_direccion . '<br>
				Col. ' . $agencia_colonia . '. ' . $agencia_municipio . '<br>
				C.P. ' . $agencia_cp . '. ' . $agencia_estado . '<br>
				Tel. ' . $agencia_telefonos . '</td>
			</tr>'."\n";
		$contenido .= '			<tr><td colspan="3">'."\n";
		$contenido .= '<span style="font-size:12px;"><strong>';
		if($reporte != '0' && $reporte != '') {
			$contenido .= 'Aseguradora ' . constant('ASEGURADORA_NIC_'.$aseguradora);
		} else {
			$contenido .= 'Trabajos Particulares.';
		} 
		$contenido .= '</strong></span></td></tr>'."\n";		
		$contenido .= '			<tr><td colspan="2" style="font-size:11px;">Contacto: ' . $cust['cliente_nombre'] . ' ' . $cust['cliente_apellidos'] . ' Tel. ' . $cust['cliente_telefono1'] . '</td><td>';
		if($reporte != '0' && $reporte != '') { $contenido .= 'Siniestro: ' . $reporte; } else { $contenido .= 'Particular'; } 
		$contenido .= '</td></tr>'."\n";
		$contenido .= '			<tr><td colspan="2" style="font-size:11px;">Email: ' . $cust['cliente_email'] . '</td><td>';
		if($reporte != '0' && $reporte != '') { $contenido .= 'Póliza: ' . $poliza; } else { $contenido .= ''; } 		
		$contenido .= '</td></tr>'."\n";
		$contenido .= '			<tr><td colspan="2" style="font-size:11px;">Fecha de Ingreso: ' . $cust['orden_fecha_recepcion'] . '</td><td>Torre: ' . $cust['orden_torre'];
		$contenido .= '</td></tr>'."\n";
		$contenido .= '			<tr><td colspan="2" style="font-size:11px;">Inicio de Presupuesto: ';
		if($inipres[0]['fecha'] != '' && $inipres[0]['fecha'] != '0000-00-00 00:00:00')  { $fechainipres = date('Y-m-d H:i', strtotime($inipres[0]['fecha'])); $contenido .= $fechainipres;}
		$contenido .= ' por ' . $usr[$inipres[0]['usuario']]['nombre'] . '</td><td>OT: ' . $orden_id . '</td></tr>'."\n";
//		echo '			<tr><td colspan="3">Presupuesto terminaldo el : ' . date('Y-m-d H:i', strtotime($inipres[0]['fecha'])) . '</td></tr>'."\n";
		$contenido .= '			<tr><td colspan="3" style="font-size:11px;">Presupuesto Terminado: ';
		if($finpres['fecha'] != '' && $finpres['fecha'] != '0000-00-00 00:00:00') { $fechafinpres = date('Y-m-d H:i', strtotime($finpres['fecha'])); $contenido .= $fechafinpres;}
		$contenido .= ' por ' . $usr[$finpres['usuario']]['nombre'] . '</td></tr>'."\n";
		$contenido .= '			<tr><td colspan="3" style="font-size:11px;">Propiedad de : ' . $emp['empresa_razon_social'] . '</td></tr>'."\n";
		$contenido .= '			<tr><td colspan="3" style="font-size:11px;"><span><strong>Unidad: ' . $veh['tipo'] . ' Marca: ' . $veh['marca'] . ' Color: ' . $veh['color'] . ' Año: ' . $veh['modelo'] . ' PLACAS: ' . $veh['placas'] . ' Subtipo: ' . $veh['subtipo'] . ' VIN: ' . $veh['serie'] . '</strong></span></td></tr>'."\n";
		$contenido .= '		</table>'."\n";
		$diasrep = 1;
			$contenido .= '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda body-wrap" width="840">' . "\n";
			while($gsub = mysql_fetch_array($matr)) {
				$preg0 = "SELECT s.sub_orden_id, s.sub_controlista, s.sub_fecha_asignacion FROM " . $dbpfx . "subordenes s, " . $dbpfx . "orden_productos o WHERE s.orden_id = '$orden_id' AND s.sub_estatus < '130' AND s.sub_area = '" . $gsub['sub_area'] . "' AND s.sub_orden_id = o.sub_orden_id AND ";
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
						$preg1 .= " ORDER BY op_tangible,op_item ";
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
			$moarr = array();
			$paarr = array();
			foreach($items as $j => $u) {
				$contenido .= '				<tr class="cabeza_tabla"><td colspan="5">Presupuesto de ' . constant('NOMBRE_AREA_'.$j);
				$contenido .= '</td></tr>'."\n";
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
							$mo_tmp .= '				<tr><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:center; padding-left:4px; padding-right:4px;">' . $parte[0] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:center; padding-left:4px; padding-right:4px;">' . $parte[1] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:left; padding-left:4px; padding-right:4px;">' . $parte[2] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:right; padding-left:4px; padding-right:4px;">' . number_format($parte[3],2) . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td></tr>'."\n";
							$moarr[$j][] = array($parte[0], $parte[1], $parte[2], $parte[3]);
							$submo = $submo + $subtotal;
						}
					}
					if($k > '0') {
						foreach($v as $l => $w) {
							$parte = explode('|', $w);
							$subtotal = round(($parte[1] * $parte[3]), 2);
							$recon .= '				<tr><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:center; padding-left:4px; padding-right:4px;">' . $parte[0] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:center; padding-left:4px; padding-right:4px;">' . $parte[1] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:left; padding-left:4px; padding-right:4px;">' . $parte[2] . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:right; padding-left:4px; padding-right:4px;">' . number_format($parte[3],2) . '</td><td style="border-bottom-width:1px; border-bottom-style:solid; text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td></tr>'."\n";
							$paarr[$j][] = array($parte[0], $parte[1], $parte[2], $parte[3]);
							$subarea = $subarea + $subtotal;
						}
					}
				}
				$contenido .= '				<tr><td colspan="5">'."\n";
				$contenido .= '					<table cellpadding="0" cellspacing="0" border="0" width="100%">'."\n";
				$contenido .= '						<tr><td colspan="5">';
				if($k == '1') { $contenido .= 'Refacciones'; } elseif($k == '2') { $contenido .= 'Consumibles'; } else { $contenido .= 'Sin refacciones o consumibles'; }
				$contenido .= '</td></tr>'."\n";
				$contenido .= '						<tr style="text-align:center;"><td>Partida</td><td>Cantidad</td><td>Nombre</td><td style="text-align:right;">Precio Unitario</td><td style="text-align:right;">Subtotal</td></tr>'."\n";
				$contenido .= $recon;
				$contenido .= '						<tr><td colspan="4" style="text-align:right;">Subtotal de ';
				if($k == '1') { $contenido .= 'Refacciones'; } elseif($k == '2') { $contenido .= 'Consumibles'; } else { $contenido .= 'Sin refacciones o consumibles'; }
				$contenido .= '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
				$contenido .= '						<tr><td colspan="5">Mano de Obra</td></tr>'."\n";
				$contenido .= '						<tr style="text-align:center;"><td>Partida</td><td>Cantidad</td><td>Nombre</td><td style="text-align:right;">Precio Unitario</td><td style="text-align:right;">Subtotal</td></tr>'."\n";
				$contenido .= $mo_tmp;
				$contenido .= '						<tr><td colspan="4" style="text-align:right;">Subtotal de Mano de Obra</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($submo,2) . '</td></tr>'."\n";
				$subarea = $subarea + $submo;
				$contenido .= '						<tr><td colspan="3"></td><td style="text-align:right; padding-left:4px; padding-right:4px;">Sub total de ' . constant('NOMBRE_AREA_'.$j) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
				$contenido .= '					</table>'."\n";
				$contenido .= '				</td></tr>'."\n";

				$total = $total + $subarea;
			}
			$dias = intval(($horas / 16) + 0.999999);
			$iva = round(($total * 0.16), 2);
			$gtotal = $total + $iva;
			
			$contenido .= '				<tr class="cabeza_tabla"><td colspan="3">Observaciones: ';
			if($diasrep == 1) { $contenido .= 'Días para reparación: ' . $dias; }
			$contenido .= '</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Sub Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($total,2) . '</td></tr>'."\n";
			$contenido .= '				<tr class="cabeza_tabla"><td colspan="3"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">IVA (16%)</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($iva,2) . '</td></tr>'."\n";
			$contenido .= '				<tr class="cabeza_tabla"><td colspan="3"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Gran Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($gtotal,2) . '</td></tr>'."\n";
			$contenido .= ' </table>'."\n";
			
			if($envio == '1'){
				
				echo '';
			} else{
				
				echo $contenido;
			}
			
			if($envio == '1'){
			
			$contenido .= '
			<br>
			<h5>Atentamente:</h5>
				<p>' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '<br>
        		' . $nombre_agencia . '<br>
        		Teléfonos:' . $agencia_telefonos . '<br>'."\n";
			
			if($_SESSION['email'] != ''){
				
				$contenido .= 'E-mail: ' . $_SESSION['email'] . '<br>';
					
			} else {
				
				$contenido .= 'E-mail: ' . $agencia_email . '<br>';
			}
		
        	$contenido .= '
				</p>
        	<p style="font-size:9px;font-weight:bold;">Este mensaje fue
        		enviado desde un sistema automático, si desea hacer algún
        		comentario respecto a esta notificación o cualquier otro asunto
        		respecto al Centro de Reparación por favor responda a los
        		correos electrónicos o teléfonos incluidos en el cuerpo de este
        		mensaje. De antemano le agradecemos su atención y preferencia.</p>
			</div>
			</div>
			</td>
			</tr>'."\n";
				
			if($accion === 'imprimeaut'){ 
				$asunto_inicio = 'Valuación autorizada '; 
			} else{ 
				$asunto_inicio = 'Presupuesto para '; 
			}
				
			$asunto = $asunto_inicio . $veh['marca'] . ' ' . $veh['tipo'] . ' ' . $veh['color'] . ' ' . $veh['modelo'] . ' Placas: ' . $veh['placas'];
			
			$para = $cust['cliente_email'];
			
			$respondera = $_SESSION['email'];
			
			include('parciales/notifica2.php');
			
			if($_SESSION['msjerror'] == ''){
				
				$_SESSION['msjerror'] = 'Se envió el correo a ' . $para;
			}
				
			redirigir('ordenes.php?accion=consultar&orden_id='.$orden_id);
		}
		

			include('parciales/numeros-a-letras.php');
			$letra = strtoupper(letras2($gtotal));

			echo '	<table cellpadding="0" cellspacing="0" border="0" class="izquierda body-wrap" width="840">' . "\n";
//			echo '		<tr><td colspan="4" style="height:15px;"></td></tr>'."\n";
			echo '		<tr><td colspan="4" style="text-align:center; font-weight:bold;"><p>((' . $letra . '))</p></td></tr>'."\n";

			$nom_excel = $orden_id . '-' . $veh['placas'] . '-presupuesto-';
			if($reporte != '0' && $reporte != '') { $nom_excel .= $reporte; } else { $nom_excel .= 'Particular'; }
			$nom_excel .=  '.xlsx';
			if (file_exists(DIR_DOCS . $nom_excel)) { unlink (DIR_DOCS . $nom_excel); }

			echo '		<tr><td colspan="4" style="text-align:left;"><div class="control"><a href="proceso.php?accion=consultar&orden_id=' . $orden_id . '#' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>&nbsp;<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir-presupuesto.png" alt="Imprimir Todas las SOT de la OT" title="Imprimir Todas las SOT de la OT"></a>&nbsp;<a href="' . DIR_DOCS . $nom_excel . '"><img src="idiomas/' . $idioma . '/imagenes/partidas-para-aseguradora.png" alt="Descargar Datos crudos de Presupuesto" title="Descargar Datos crudos de Presupuesto"></a><a href="presupuestos.php?accion=imprimepres&orden_id=' . $orden_id . '&sub_orden_id= ' . $sub_orden_id . '&area=' . $area . '&sin=' . $sin . '&envio=1&accion=' . $accion . '"><img src="idiomas/' . $idioma . '/imagenes/enviar_correo.png" alt="envíar por correo" title="envíar por correo"></a></div></td></tr>'."\n";
			echo '	</table>'."\n";

// -------------------   Creación de Archivo Excel   ----------------------------------

	require_once ('Classes/PHPExcel.php');
			$objReader = PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load(DIR_DOCS . "base.xls");
			$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
				->setTitle("Items de Presupuesto")
				->setKeywords("AUTOSHOP EASY");

			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', 'Centro de Reparación')
            ->setCellValue('B1', $nombre_agencia)
				->setCellValue('A2', 'Cliente')
            ->setCellValue('B2', constant('ASEGURADORA_NIC_'.$aseguradora))
				->setCellValue('A3', 'Empresa')
				->setCellValue('B3', $emp['empresa_razon_social'])
				->setCellValue('A4', 'Contacto')
				->setCellValue('B4', $cust['cliente_nombre'])
				->setCellValue('A5', 'Teléfono')
				->setCellValue('B5', ' ' . $cust['cliente_telefono1'])
				->setCellValue('A6', 'Email')
				->setCellValue('B6', $cust['cliente_email'])
				->setCellValue('A7', 'Siniestro')
            ->setCellValue('B7', ' ' . $reporte)
				->setCellValue('A8', 'Póliza')
				->setCellValue('B8', ' ' . $poliza)
				->setCellValue('A9', 'Fecha Inicio Pres')
				->setCellValue('B9', $fechainipres)
				->setCellValue('A10', 'Pres Iniciado por')
            ->setCellValue('B10', $usr[$inipres[0]['usuario']]['nombre'])
				->setCellValue('A11', 'Fecha Fin Pres')
				->setCellValue('B11', $fechafinpres)
				->setCellValue('A12', 'Pres Terminado por')
            ->setCellValue('B12', $usr[$finpres['usuario']]['nombre'])
				->setCellValue('A13', 'Días de Reparación')
            ->setCellValue('B13', $dias)
				->setCellValue('A14', 'Placas')
            ->setCellValue('B14', $veh['placas'])
				->setCellValue('A15', 'Serie')
            ->setCellValue('B15', $veh['serie'])
				->setCellValue('A16', 'Marca')
            ->setCellValue('B16', $veh['marca'])
				->setCellValue('A17', 'Tipo')
            ->setCellValue('B17', $veh['tipo'])
				->setCellValue('A18', 'Subtipo')
            ->setCellValue('B18', $veh['subtipo'])
				->setCellValue('A19', 'Año')
            ->setCellValue('B19', $veh['modelo'])
				->setCellValue('A20', 'Color')
            ->setCellValue('B20', $veh['color'])
				->setCellValue('A21', 'Asesor')
            ->setCellValue('B21', $usr[$cust['orden_asesor_id']]['nombre'])
            ->setCellValue('A22', 'Archivo generado por') 
            ->setCellValue('B22', $usr[$_SESSION['usuario']]['nombre'])
            ->setCellValue('A23', 'Fecha de este archivo')
            ->setCellValue('B23', $hoy);

	$col = 25;
	foreach($items as $j => $u) {
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$col, 'Presupuesto de')
			->setCellValue('B'.$col, constant('NOMBRE_AREA_'.$j));
		$col++;
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$col, 'Partida')
            ->setCellValue('B'.$col, 'Cantidad')
            ->setCellValue('C'.$col, 'Descripción')
            ->setCellValue('D'.$col, 'Precio');
		$col++;
		foreach($paarr[$j] as $l => $w) {
					$a= 'A'.$col; $b = 'B'.$col; $c = 'C'.$col; $d = 'D'.$col;
					$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($a, $w[0])
						->setCellValue($b, $w[1])
						->setCellValue($c, $w[2])
						->setCellValue($d, $w[3]);
					$col++;
					$submo = $submo + $subtotal;
		}
		foreach($moarr[$j] as $l => $w) {
					$a= 'A'.$col; $b = 'B'.$col; $c = 'C'.$col; $d = 'D'.$col;
					$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($a, $w[0])
						->setCellValue($b, $w[1])
						->setCellValue($c, $w[2])
						->setCellValue($d, $w[3]);
					$col++;
		}
		$col++;
	}
	           
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(DIR_DOCS . $nom_excel);

//-----------------  Fin de Creación de Archivo Excel   -----------------------			
			
			$sql_data_array = array('orden_id' => $orden_id,
				'doc_usuario' => $_SESSION['usuario'],
				'doc_archivo' => $nom_excel);
				$sql_data_array['doc_nombre'] = 'Hoja de Presupuesto'; 
			ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
			bitacora($orden_id, $sql_data_array['doc_nombre'], $dbpfx);

			
?>
