<?php
		$prega = "SELECT o.orden_fecha_recepcion, c.cliente_nombre, c.cliente_apellidos, c.cliente_telefono1, c.cliente_email FROM " . $dbpfx . "ordenes o, " . $dbpfx . "clientes c WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id LIMIT 1";
		$matra = mysql_query($prega) or die("ERROR: Fallo selección de datode cliente!");
		$cust = mysql_fetch_array($matra);
		$veh = datosVehiculo($orden_id, $dbpfx);
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
			</tr>
			<tr><td>Fecha Ingreso: ' . $cust['orden_fecha_recepcion'] . '</td><td style="text-align: center;">' . $veh['completo'] . ' </td><td style="text-align: right;">No. de Orden de Trabajo: ' . $orden_id . '</td></tr>
			<tr><td colspan="3">Cliente: ' . $cust['cliente_nombre'] . ' ' . $cust['cliente_apellidos'] . ' Tel. ' . $cust['cliente_telefono1'] . ' Email: ' . $cust['cliente_email'];
		echo '. <span style="font-size:14px;"><strong>';
		if($sub_orden_id != '') {
			if($reporte != '0') {
				echo 'Aseguradora ' . constant('ASEGURADORA_NIC_'.$sin['sub_aseguradora']) . ' Siniestro: ' . $reporte;
			} else {
				echo 'Trabajos Particulares.';
			} 
		} else {
			echo 'Incluye todos los trabajos.';
		}
		echo '</strong></span></td></tr>
		</table>'."\n";
			echo '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">' . "\n";
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
				echo '				<tr class="cabeza_tabla"><td colspan="6">Presupuesto de ' . constant('NOMBRE_AREA_'.$j);
				if($controlista[$j] > '0') {
					echo ' terminado por ' . $usr[$controlista[$j]]['nombre'] . ' el ' . date('Y-m-d H:i', strtotime($fpres[$j]));
				} else {
//					echo ' pendiente de terminar.';
				}
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
							$mo_tmp .= '				<tr><td style="padding-left:4px; padding-right:4px;">' . $parte[0] . '</td><td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td><td style="padding-left:4px; padding-right:4px;">' . $parte[2] . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($parte[3],2) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td></tr>'."\n";
							$submo = $submo + $subtotal;
						}
					}
					if($k > '0') {
						foreach($v as $l => $w) {
							$parte = explode('|', $w);
							$subtotal = round(($parte[1] * $parte[3]), 2);
							$recon .= '				<tr><td style="padding-left:4px; padding-right:4px;">' . $parte[0] . '</td><td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td><td style="padding-left:4px; padding-right:4px;">' . $parte[2] . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($parte[3],2) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td></tr>'."\n";
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
			$iva = 0;
			$dias = intval(($horas / 16) + 0.999999);
			if($pciva != '1') {
				$iva = round(($total * 0.16), 2);
			}
			$gtotal = $total + $iva;
			
			echo '				<tr class="cabeza_tabla"><td colspan="4">Observaciones: ';
			if($diasrep == 1) { echo 'Días para reparación: ' . $dias; }
			
			echo '</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Sub Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($total,2) . '</td></tr>'."\n";
			echo '				<tr class="cabeza_tabla"><td colspan="4"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">';
			if($pciva != '1') { echo 'IVA (16%)'; }
			echo '</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">';
			if($pciva != '1') { echo number_format($iva,2); }
			echo '</td></tr>'."\n";
			
			echo '				<tr class="cabeza_tabla"><td colspan="4"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Gran Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($gtotal,2) . '</td></tr>'."\n";
			echo ' </table>'."\n";

			echo '	<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="840">' . "\n";
			echo '		<tr><td colspan="4" style="height:15px;"></td></tr>'."\n";
			echo '		<tr><td colspan="4" style="text-align:left;"><div class="control"><a href="proceso.php?accion=consultar&orden_id=' . $orden_id . '#' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>&nbsp;<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir-presupuesto.png" alt="Imprimir Todas las SOT de la OT" title="Imprimir Todas las SOT de la OT"></a></div></td></tr>'."\n";
			echo '	</table>'."\n";
		
?>
