<?php
		$prega = "SELECT o.orden_fecha_recepcion, c.cliente_nombre, c.cliente_apellidos, c.cliente_telefono1, c.cliente_email FROM " . $dbpfx . "ordenes o, " . $dbpfx . "clientes c WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id LIMIT 1";
		$matra = mysql_query($prega) or die("ERROR: Fallo selección de datode cliente!");
		$cust = mysql_fetch_array($matra);
		$veh = datosVehiculo($orden_id, $dbpfx, '');
		
		if($envio == '1') {
			$contenido .= '		<br>
		<table>
			<tr>
				<td></td>
				<td>
					<div class="contenedor80">'."\n";
		}
		$contenido .= '						<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda body-wrap">
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
							</tr>
							<tr><td style="font-size:11px;">Fecha Ingreso: ' . $cust['orden_fecha_recepcion'] . '</td><td style="text-align: center; font-size:11px;">' . $veh['completo'] . ' </td><td style="text-align: right; font-size:11px;">No. de Orden de Trabajo: ' . $orden_id . '</td></tr>
							<tr><td colspan="3" style="font-size:11px;">Cliente: ' . $cust['cliente_nombre'] . ' ' . $cust['cliente_apellidos'] . ' Tel. ' . $cust['cliente_telefono1'] . ' Email: ' . $cust['cliente_email'];
		$contenido .= '. <span style="font-size:12px;"><strong>';
		if($sub_orden_id != '') {
			if($reporte != '0') {
				$contenido .= 'Aseguradora ' . constant('ASEGURADORA_NIC_'.$aseguradora) . ' Siniestro: ' . $reporte;
			} else {
				$contenido .= 'Trabajos Particulares.';
			} 
		} else {
			$contenido .= 'Incluye todos los trabajos.';
		}
		$contenido .= '</strong></span></td></tr>
						</table>'."\n";
			$contenido .= '							<table cellpadding="0" cellspacing="0" border="1" class="izquierda body-wrap" width="840">' . "\n";
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
			$moarr = array();
			$paarr = array();
			foreach($items as $j => $u) {
				$contenido .= '								<tr class="cabeza_tabla"><td colspan="6" >Presupuesto de ' . constant('NOMBRE_AREA_'.$j);
				if($controlista[$j] > '0') {
					$contenido .= ' terminado por ' . $usr[$controlista[$j]]['nombre'] . ' el ' . date('Y-m-d H:i', strtotime($fpres[$j]));
				} else {
//					echo ' pendiente de terminar.';
				}
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
							$mo_tmp .= '								<tr><td style="padding-left:4px; padding-right:4px;">' . $parte[0] . '</td><td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td><td style="padding-left:4px; padding-right:4px;">' . $parte[2] . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($parte[3],2) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td></tr>'."\n";
							$moarr[$j][] = array($parte[0], $parte[1], $parte[2], $subtotal);
							$submo = $submo + $subtotal;
						}
					}
					if($k > '0') {
						foreach($v as $l => $w) {
							$parte = explode('|', $w);
							$subtotal = round(($parte[1] * $parte[3]), 2);
							$recon .= '								<tr><td style="padding-left:4px; padding-right:4px;">' . $parte[0] . '</td><td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td><td style="padding-left:4px; padding-right:4px;">' . $parte[2] . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($parte[3],2) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td></tr>'."\n";
							$paarr[$j][] = array($parte[0], $parte[1], $parte[2], $parte[3]);
							$subarea = $subarea + $subtotal;
						}
					}
				}
				$contenido .= '								<tr><td colspan="3" style="vertical-align: top;">'."\n";
				$contenido .= '									<table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:10px;" class="body-wrap">'."\n";
				$contenido .= '										<tr><td colspan="5">Mano de Obra</td></tr>'."\n";
				$contenido .= '										<tr style="text-align:center;"><td>Item</td><td>Cantidad</td><td>Nombre</td><td style="text-align:right;">Precio Unitario</td><td style="text-align:right;">Subtotal</td></tr>'."\n";
				$contenido .= $mo_tmp;
				$contenido .= '										<tr><td colspan="4" style="text-align:right;">Subtotal</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($submo,2) . '</td></tr>'."\n";
				$contenido .= '									</table>'."\n";
				$contenido .= '								</td><td colspan="3" style="vertical-align: top;">'."\n";
				$contenido .= '									<table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:10px;">'."\n";
				$contenido .= '										<tr><td colspan="5">';
				if($k == '1') { $contenido .= 'Refacciones'; } elseif($k == '2') { $contenido .= 'Consumibles'; } else { $contenido .= 'Sin refacciones o consumibles'; }
				$contenido .= '</td></tr>'."\n";
				$contenido .= '										<tr style="text-align:center;"><td>Item</td><td>Cantidad</td><td>Nombre</td><td style="text-align:right;">Precio Unitario</td><td style="text-align:right;">Subtotal</td></tr>'."\n";
				$contenido .= $recon;
				$contenido .= '										<tr><td colspan="4" style="text-align:right;">Subtotal</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
				$contenido .= '									</table>'."\n";
				$contenido .= '								</td></tr>'."\n";
				$subarea = $subarea + $submo;

				$contenido .= '								<tr><td colspan="4" style="text-align:center; vertical-align:bottom; padding-left:4px; padding-right:4px; height:60px;">Nombre y Firma de Responsable de ' . constant('NOMBRE_AREA_'.$j) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">Sub total de ' . constant('NOMBRE_AREA_'.$j) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
		
		
				$total = $total + $subarea;
			}
			$iva = 0;
			$dias = intval(($horas / 16) + 0.999999);
			if($pciva != '1') {
				$iva = round(($total * 0.16), 2);
			}
			$gtotal = $total + $iva;
			
			$contenido .= '							<tr class="cabeza_tabla"><td colspan="4">Observaciones: ';
			if($diasrep == 1) { $contenido .= 'Días para reparación: ' . $dias; }
			
			$contenido .= '</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Sub Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($total,2) . '</td></tr>'."\n";
			$contenido .= '							<tr class="cabeza_tabla"><td colspan="4"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">';
			if($pciva != '1') { $contenido .= 'IVA (16%)'; }
			$contenido .= '</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">';
			if($pciva != '1') { $contenido .= number_format($iva,2); }
			$contenido .= '</td></tr>'."\n";
			
			$contenido .= '							<tr class="cabeza_tabla"><td colspan="4"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Gran Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($gtotal,2) . '</td></tr>'."\n";
			$contenido .= '						</table>'."\n";

			if($envio != '1') {
				echo $contenido;
			} else {
				$contenido .= '						<br>
						<h5>Atentamente:</h5>
						<p>' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '<br>
						' . $nombre_agencia . '<br>
						Teléfonos:' . $agencia_telefonos . '<br>'."\n";			
				if($_SESSION['email'] != '') {
					$contenido .= '						E-mail: ' . $_SESSION['email'] . '<br>';
				} else {
					$contenido .= '						E-mail: ' . $agencia_email . '<br>';
				}
        		$contenido .= '						</p>
						<p style="font-size:9px;font-weight:bold;">Este mensaje fue
						enviado desde un sistema automático, si desea hacer algún
						comentario respecto a esta notificación o cualquier otro asunto
						respecto al Centro de Reparación por favor responda a los
						correos electrónicos o teléfonos incluidos en el cuerpo de este
						mensaje. De antemano le agradecemos su atención y preferencia.</p>
					</div>
				</td>
			</tr>
		</table>'."\n";
			
				if($accion === 'imprimeaut') {
					$asunto_inicio = 'Valuación autorizada '; 
				} else { 
					$asunto_inicio = 'Presupuesto para '; 
				}
				$asunto = $asunto_inicio . $veh['marca'] . ' ' . $veh['tipo'] . ' ' . $veh['color'] . ' ' . $veh['modelo'] . ' Placas: ' . $veh['placas'];
				$para = $cust['cliente_email'];
				$respondera = $_SESSION['email'];
				include('parciales/notifica2.php');
				if($_SESSION['msjerror'] == '') {
					$_SESSION['msjerror'] = 'Se envió el correo a ' . $para;
				}
				redirigir('ordenes.php?accion=consultar&orden_id='.$orden_id);
			}

			$nom_excel = $orden_id . '-' . $veh['placas'] . '-presupuesto-';
			if($reporteAseguradora != '0' && $reporte != '') { $nom_excel .= $reporte; } else { $nom_excel .= 'Particular'; }
			$nom_excel .=  '.xlsx';
			if (file_exists(DIR_DOCS . $nom_excel)) { unlink (DIR_DOCS . $nom_excel); }

			echo '		<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="840">' . "\n";
			echo '			<tr><td colspan="4" style="height:15px;"></td></tr>'."\n";
			echo '			<tr><td colspan="4" style="text-align:left;"><div class="control"><a href="presupuestos.php?accion=consultar&orden_id=' . $orden_id . '#' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>&nbsp;<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir-presupuesto.png" alt="Imprimir Todas las SOT de la OT" title="Imprimir Todas las SOT de la OT"></a>
				<a href="' . DIR_DOCS . $nom_excel . '"><img src="idiomas/' . $idioma . '/imagenes/partidas-para-aseguradora.png" alt="Descargar Datos crudos de Presupuesto" title="Descargar Datos crudos de Presupuesto"></a>
				<a href="presupuestos.php?accion=imprimepres&orden_id=' . $orden_id . '&sub_orden_id= ' . $sub_orden_id . '&area=' . $area . '&sin=' . $sin . '&envio=1&accion=' . $accion . '"><img src="idiomas/' . $idioma . '/imagenes/enviar_correo.png" alt="envíar por correo" title="envíar por correo"></a>
				</div></td></tr>'."\n";
			echo '		</table>'."\n";

// -------------------   Creación de Archivo Excel   ----------------------------------

	require_once ('Classes/PHPExcel.php');
			$objReader = PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load("parciales/formato-pres.xls");
			$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
						->setTitle("Items de Presupuesto")
						->setKeywords("AUTOSHOP EASY");

			$objPHPExcel->setActiveSheetIndex(0)
            			->setCellValue('E3', $nombre_agencia)
						->setCellValue('B10', constant('ASEGURADORA_NIC_'.$aseguradora))
						->setCellValue('A6', $cust['cliente_nombre'] . ' ' . $cust['cliente_apellidos'])
						->setCellValue('F6', ' ' . $cust['cliente_telefono1'])
            			->setCellValue('A10', ' ' . $reporte)
						->setCellValue('A14', ' ' . $poliza)
            			->setCellValue('G14', $dias) // 'Días de Reparación'
						->setCellValue('D10', $veh['marca'])
						->setCellValue('H10', $veh['color'])
						->setCellValue('D12', $veh['tipo'] . ' ' . $veh['subtipo'])
						->setCellValue('D14', $veh['serie'])
						->setCellValue('B14', $veh['modelo'])
						->setCellValue('H6', $cust['orden_fecha_recepcion'])
						->setCellValue('H8', $orden_id);

	$col = 19;
	foreach($items as $j => $u) {
		
		// --- Agregar el encabezado Refacciones de la tarea ---
		if($paarr[$j] == ''){
			
		} else{
			$b = 'B'.$col;
			
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($b, 'Refacciones de ' . constant('NOMBRE_AREA_'.$j));
			$col++;
			
		}
		
		
		// --- Agregar refacciones de la tarea ---
		foreach($paarr[$j] as $l => $w) {		
			
					$a= 'A'.$col; $b = 'B'.$col; $h = 'H'.$col;
					
					if($w[3] == '' || $w[3] == 0){
						$precio = 0;
					} else{
						$precio = $w[3];
					}
			
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($a, $w[1])
								->setCellValue($b, $w[2])
								->setCellValue($h, $precio);
					$col++;
					
		}
		
		// --- Agregar el encabezado Mano de Obra de la tarea ---
		if($moarr[$j] == ''){
			
		} else{
		
			$b = 'B'.$col;
			
			$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($b, 'Mano de Obra de ' . constant('NOMBRE_AREA_'.$j));
			$col++;
		}
			
		// --- Agregar conceptos de Mano de obra de la tarea ---
		foreach($moarr[$j] as $l => $w) {

					$a= 'A'.$col; $b = 'B'.$col; $h = 'H'.$col;
			
					if($w[3] == '' || $w[3] == 0){
						$precio = 0;
					} else{
						$precio = $w[3];
					}
			
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($a, $w[1])
								->setCellValue($b, $w[2])
								->setCellValue($h, $precio);
					$col++;
		}
		$col++;
		
	}

	// --- Imprimir totales ---
		
		$b = 'B'.$col; $h = 'H'.$col;
		
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($b, "SUBTOTAL")
					->setCellValue($h, $total);
		$col++;
		
		$b = 'B'.$col; $h = 'H'.$col;
		
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($b, "IVA")
					->setCellValue($h, $iva);
		$col++;
		
		$b = 'B'.$col; $h = 'H'.$col;
		
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($b, "TOTAL")
					->setCellValue($h, $gtotal);
	           
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
