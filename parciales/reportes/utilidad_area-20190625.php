<?php
/*************************************************************************************
*   Script de "reporte de Finanzas"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/

if ($f1125030 == '1' || $_SESSION['rol02']=='1') {
	
	//echo 'area ' . $area_filtro . '<br>';

	if($estatusflt >= '5' && $estatusflt <= '7') { // --- Cobrados y Cobrables No Facturados ---
		
		$preg0 = "SELECT DISTINCT o.orden_id, o.orden_estatus, o.orden_vehiculo_placas, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_id, o.orden_alerta, o.orden_asesor_id, o.orden_fecha_recepcion, o.orden_fecha_de_entrega, o.orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes o";
		
	} else { // --- Facturados No cobrados, Todos los Recibidos, En documentación, En reparación, Terminados, Entregados ---
		
		$preg0 = "SELECT orden_id, orden_estatus, orden_vehiculo_placas, orden_vehiculo_tipo, orden_vehiculo_color, orden_vehiculo_id, orden_alerta, orden_asesor_id, orden_fecha_recepcion, orden_fecha_de_entrega, orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes WHERE ";
		
	}

	if($estatusflt == '5') { // --- Cobrados ---
		
		// --- Consultamos las facturas por cobrar, que ya hallan sido cobradas ---
		
		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '1' AND f.fact_tipo < '3' AND o.orden_fecha_de_entrega >= '" . $feini . "' AND o.orden_fecha_de_entrega <= '" . $fefin . "' AND (o.orden_estatus < '30' OR o.orden_estatus = '99') ";
		
		
	} elseif($estatusflt == '6') { // --- Facturados No cobrados ---
		
		// --- Consultamos las facturas por cobrar, que no hallan sido pagadas ---
		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '0' AND f.fact_tipo < '3' AND o.orden_fecha_de_entrega >= '" . $feini . "' AND o.orden_fecha_de_entrega <= '" . $fefin . "' ";
		$rangofe = $lang['Fecha de Facturas'];
		
	} elseif($estatusflt == '7') { // --- Cobrables No Facturados ---
		
		// --- Se deben de buscar todos los siniestros y agruparlos, posterirmente se debe de localizar los que tienen pendiente el cobro, es decir que no están facturados.
		//$preg0 .= " WHERE NOT EXISTS ( SELECT null FROM " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_tipo < '3' ) AND ((o.orden_estatus >= '12' AND o.orden_estatus <= '16') OR o.orden_estatus = '99') AND o.orden_fecha_ultimo_movimiento >= '" . $feini . "' AND o.orden_fecha_ultimo_movimiento <= '" . $fefin . "' ";
		
		$preg0 .= " WHERE o.orden_fecha_de_entrega >= '" . $feini . "' AND o.orden_fecha_de_entrega <= '" . $fefin . "' AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$rangofe = $lang['Fecha de Ultimo Movimiento'];
		
	} elseif($estatusflt == '4') { // --- Entregados ---
		
		$preg0 .= " orden_fecha_de_entrega > '" . $feini . "' AND orden_fecha_de_entrega < '" . $fefin . "' AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Entregados Autorizados';
		$rangofe = $lang['Fecha de Entrega'];
		
	} elseif($estatusflt == '3') { // --- Terminados ---
		
		$preg0 .= " orden_fecha_ultimo_movimiento > '" . $feini . "' AND orden_fecha_ultimo_movimiento < '" . $fefin . "'  AND (orden_estatus >= '12' AND orden_estatus <= '15') ";
		$nomrep = 'Terminados';
		$rangofe = $lang['Fecha de Ultimo Movimiento'];
		
	} elseif($estatusflt == '2') { // --- En reparación ---
		
		// ------ Se eliminan rangos de fechas para incluir todos los trabajos de este tipo en virtud de que no deben existir demasiados
		$preg0 .= " ((orden_estatus >= '4' AND orden_estatus <= '11') OR orden_estatus = '21') ";
		$nomrep = 'Reparación';
		$rangofe = $lang['Fecha No Aplica'];
		
	} elseif($estatusflt == '1') { // --- En documentación ---
		
		$preg0 .= " orden_estatus <= '2' OR orden_estatus = '17' OR orden_estatus = '20' OR (orden_estatus >= '24' AND orden_estatus <= '29') ";
		$nomrep = 'Por Autorizar';
		$rangofe = $lang['Fecha No Aplica'];
		
	} elseif($estatusflt == '0') { // --- Todos los Recibidos ---
		
		$preg0 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'  AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Todos los Recibidos';
		$rangofe = $lang['Fecha de Recepción'];
		
	} else { // --- Default ---
		
		$preg0 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'  AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Todos los Recibidos';
		$rangofe = $lang['Fecha de Recepción'];
		
	}
	
	//echo '<big>' . $preg0 . '</big>';
	//echo 'Filtro ' . $estatusflt . '<br>';
	
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de lapso! ".$preg0);
	$filas = mysql_num_rows($matr0);

	if($export == 1){ // ---- Hoja de calculo ----

		$encabezado = 'Reporte de Utilidad por área, OTs recibidas del ' . $t_ini . ' al ' . $t_fin . ' ' . $rangofe;

		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'REPORTE DE UTILIDAD X ÁREA: ' . $nombre_agencia;
	
		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("REPORTE DE UTILIDAD X ÁREA")
					->setKeywords("AUTOSHOP EASY");

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($celda, $titulo)
					->setCellValue("A3", $fecha_export);

		// ------ ENCABEZADOS ---
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A4", "OT")
					->setCellValue("B4", "Vehículo")
					->setCellValue("C4", $lang['Placa'])
					->setCellValue("D4", "Estatus")
					->setCellValue("E4", "Cliente")
					->setCellValue("F4", $lang['Siniestro'])
					->setCellValue("G4", "Venta de Ref Mec.")
					->setCellValue("H4", "Costo de Ref Mec.")
					->setCellValue("I4", "Util. de Ref Mec.")
					->setCellValue("J4", "Venta de MO Mec.")
					->setCellValue("K4", "Costo de MO Mec.")
					->setCellValue("L4", "Util. de MO Mec.")
					->setCellValue("M4", "Venta de Ref Hoj.")
					->setCellValue("N4", "Costo de Ref Hoj.")
					->setCellValue("O4", "Util. de Ref Hoj.")
					->setCellValue("P4", "Venta de MO Hojal.")
					->setCellValue("Q4", "Costo de MO Hojal.")
					->setCellValue("R4", "Util. de MO Hojal.")
					->setCellValue("S4", "Venta de Consu. Pint.")
					->setCellValue("T4", "Costo de Consu. Pint.")
					->setCellValue("U4", "Util. de Consu. Pint.")
					->setCellValue("V4", "Venta de MO Pint.")
					->setCellValue("W4", "Costo de MO Pint.")
					->setCellValue("X4", "Util. de MO Pint.");
		$z= 5;
		
	}
	else { // ---- HTML ----
	
		
		$encabezado = ' Reporte de Utilidad por área, OTs recibidas del ' . $t_ini . ' al ' . $t_fin . ' <small><span style="font-weight:bold; color: red;">' . $rangofe . '</span></small>';
		echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-12 panel-title">'."\n";
		
		if($asegflt != ""){
			echo '
					<img src="' . $ase[$asegflt][0] . '"/>';
		}
		
		echo '
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2><small>' . $encabezado . '</small></h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 ">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">' . "\n";

		echo '			
					<tr>
						<td colspan="6"></td>
						<td colspan="6" class="areaotra"><b>Mécanica</b></td>
						<td colspan="6" class="area6"><b>Hojalateria</b></td>
						<td colspan="6" class="area7"><b>Pintura</b></td>
					</tr>
					<tr>
						<th>OT</th>
						<th>Vehículo</th>
						<th>' . $lang['Placa'] . '</th>
						<th>Estatus</th>
						<th>Cliente</th>
						<th>' . $lang['Siniestro'] . '</th>
						<td class="areaotra"><b>Venta de<br>Ref</b></td>
						<td class="areaotra"><b>Costo de<br>Ref</b></td>
						<td class="areaotra"><b>Utili. de<br>Ref</b></td>
						<td class="areaotra"><b>Venta de<br>MO</b></td>
						<td class="areaotra"><b>Costo de<br>MO</b></td>
						<td class="areaotra"><b>Utili. de<br>MO</b></td>
						<td class="area6"><b>Venta de<br>Ref</b></td>
						<td class="area6"><b>Costo de<br>Ref</b></td>
						<td class="area6"><b>Utili. de<br>Ref</b></td>
						<td class="area6"><b>Venta de<br>MO</b></td>
						<td class="area6"><b>Costo de<br>MO</b></td>
						<td class="area6"><b>Utili. de<br>MO</b></td>
						<td class="area7"><b>Venta de<br>Consu.</b></td>
						<td class="area7"><b>Costo de<br>Consu.</b></td>
						<td class="area7"><b>Utili. de<br>Consu.</b></td>
						<td class="area7"><b>Venta de<br>MO</b></td>
						<td class="area7"><b>Costo de<br>MO</b></td>
						<td class="area7"><b>Utili. de<br>MO</b></td>		
					</tr>'."\n";
	$fondo = 'claro';
		
	}

	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	$ventas = 0;
	
	while($ord = mysql_fetch_array($matr0)) {
		
		// ---- Consultar los siniestros de la orden ----
		$preg_siniestros = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
		
		if($estatusflt == '7') { // --- Cobrables No Facturados ---
			$preg_siniestros .= " AND sub_presupuesto > '0' ";
		}
		
		if($asegflt != '') {
			$preg_siniestros .= " AND sub_aseguradora = '" . $asegflt . "' ";
		}
		
		$preg_siniestros .= " GROUP BY sub_reporte";
		
		$matr_siniestros = mysql_query($preg_siniestros) or die ('Falló: ' . $preg_siniestros);
		
		while($sin = mysql_fetch_array($matr_siniestros)){
			
			//echo 'orden ' . $ord['orden_id'] . ', Siniestro ' . $sin['sub_reporte'] . '<br>';
			// --- Recorrer las tareas del siniestro ----
			$preg_tareas = "SELECT DISTINCT sub_partes, sub_mo, sub_consumibles, sub_orden_id, sub_reporte, sub_aseguradora, fact_id, sub_area, recibo_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' AND sub_reporte = '" . $sin['sub_reporte'] . "'";

			$matr_tareas = mysql_query($preg_tareas) or die ('Falló: ' . $preg_tareas);
			//echo $preg_tareas . '<br>';

			$tot_venta_ref_mecanica = 0;
			$costo_refs_mecanica = 0;
			$tot_venta_mo_mecanica = 0;
			$costo_mo_mecanica = 0;
			
			$tot_venta_ref_hojal = 0;
			$costo_refs_hojal = 0;
			$tot_venta_mo_hojal = 0;
			$costo_mo_hojal = 0;
			
			$tot_venta_mat_pintura = 0;
			$costo_mat_pintura = 0;
			$tot_venta_mo_pintura = 0;
			$costo_mo_pintura = 0;
			
			// --- Consultar los recibos de destajo del siniestro ---
			$preg_destajos = "SELECT area, monto FROM " . $dbpfx . "destajos_elementos WHERE orden_id = '" . $ord['orden_id'] . "' AND reporte = '" . $sin['sub_reporte'] . "'";
			$matr_destajos = mysql_query($preg_destajos) or die ('Falló: ' . $preg_destajos);
			
			while($destajos = mysql_fetch_array($matr_destajos)){
				
				if($destajos['area'] == 1){ // --- área de mecánica --
					$costo_mo_mecanica = $costo_mo_mecanica + $destajos['monto'];
				}
				elseif($destajos['area'] == 6){ // --- área de hojalatería --
					$costo_mo_hojal = $costo_mo_hojal + $destajos['monto'];
				}
				elseif($destajos['area'] == 7){ // --- área de pintura --
					$costo_mo_pintura = $costo_mo_pintura + $destajos['monto'];
				}
			}
			
			// ---- Recorrer tareas del reporte ----
			//echo 'recorrer tareas de la orden ' . $ord['orden_id'] . '<br>';

			while($tareas = mysql_fetch_array($matr_tareas)){

				//echo 'Tarea: ' . $tareas['sub_orden_id'] . '<br>';
				if($tareas['sub_area'] == 1){ // --- área de mecánica --

					// --- Sumar precio de venta de las refacciones ---
					$tot_venta_ref_mecanica = $tot_venta_ref_mecanica + $tareas['sub_partes'];
					// --- Sumar precio de venta de la mano de obra ---
					$tot_venta_mo_mecanica = $tot_venta_mo_mecanica + $tareas['sub_mo'];
					// ---- Consultar el costo de las refacciones contemplando las refacciones de pedidos y de paquetes de servicio ---
					$preg_refs = "SELECT op_cantidad, op_costo, op_precio, op_pedido, op_autosurtido, op_pres, op_item_seg, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $tareas['sub_orden_id'] . "' AND op_autosurtido != '1' AND ( op_surtidos = op_cantidad OR op_pedido > '0' ) AND op_tangible = '1' ";
					$matr_refs = mysql_query($preg_refs);
					//echo 'ops ' . $preg_refs . '<br>';
					while($refs = mysql_fetch_array($matr_refs)){
						$costo_refs_mecanica = $costo_refs_mecanica + ( $refs['op_cantidad'] * $refs['op_costo'] );
					}
					// ---- Consultar costos de mano de obra (T.O.T.) ----
					$preg_mano_obra = "SELECT op_cantidad, op_costo FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $tareas['sub_orden_id'] . "' AND op_pedido > '0' AND op_tangible = '0' ";
					$matr_mo = mysql_query($preg_mano_obra);
					
					while($mo = mysql_fetch_array($matr_mo)){
						$costo_mo_mecanica = $costo_mo_mecanica + ( $mo['op_cantidad'] * $mo['op_costo'] );
					}
					
				}
				elseif($tareas['sub_area'] == 6){ // --- área de hojalatería ---
					
					// --- Sumar precio de venta de las refacciones ---
					$tot_venta_ref_hojal = $tot_venta_ref_hojal + $tareas['sub_partes'];
					// --- Sumar precio de venta de la mano de obra ---
					$tot_venta_mo_hojal = $tot_venta_mo_hojal + $tareas['sub_mo'];
					// ---- Consultar el costo de las refacciones contemplando las refacciones de pedidos y de paquetes de servicio ---
					$preg_refs = "SELECT op_cantidad, op_costo, op_precio, op_pedido, op_autosurtido, op_pres, op_item_seg, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $tareas['sub_orden_id'] . "' AND op_autosurtido != '1' AND ( op_surtidos = op_cantidad OR op_pedido > '0' ) AND op_tangible = '1' ";
					$matr_refs = mysql_query($preg_refs);
					//echo 'ops ' . $preg_refs . '<br>';
					while($refs = mysql_fetch_array($matr_refs)){
						$costo_refs_hojal = $costo_refs_hojal + ( $refs['op_cantidad'] * $refs['op_costo'] );
					}
					// ---- Consultar costos de mano de obra (T.O.T.) ----
					$preg_mano_obra = "SELECT op_cantidad, op_costo FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $tareas['sub_orden_id'] . "' AND op_pedido > '0' AND op_tangible = '0' ";
					$matr_mo = mysql_query($preg_mano_obra);
					
					while($mo = mysql_fetch_array($matr_mo)){
						$costo_mo_hojal = $costo_mo_hojal + ( $mo['op_cantidad'] * $mo['op_costo'] );
					}
					
				}
				elseif($tareas['sub_area'] == 7){ // --- área de pintura ---

					// --- Sumar precio de materiales de pintura ---
					$tot_venta_mat_pintura = $tot_venta_mat_pintura + $tareas['sub_consumibles'];
					// --- Sumar precio de venta de la mano de obra ---
					$tot_venta_mo_pintura = $tot_venta_mo_pintura + $tareas['sub_mo'];
					// ---- Consultar el costo de los consumibles contemplando los de pedidos y de paquetes de servicio ---
					$preg_refs = "SELECT op_cantidad, op_costo, op_precio, op_pedido, op_autosurtido, op_pres, op_item_seg, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $tareas['sub_orden_id'] . "' AND op_autosurtido != '1' AND ( op_surtidos = op_cantidad OR op_pedido > '0' ) AND op_tangible = '2' ";
					$matr_refs = mysql_query($preg_refs);
					//echo 'ops ' . $preg_refs . '<br>';
					while($refs = mysql_fetch_array($matr_refs)){
						$costo_mat_pintura = $costo_mat_pintura + ( $refs['op_cantidad'] * $refs['op_costo'] );
					}
					// ---- Consultar costos de mano de obra (T.O.T.) ----
					$preg_mano_obra = "SELECT op_cantidad, op_costo FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $tareas['sub_orden_id'] . "' AND op_pedido > '0' AND op_tangible = '0' ";
					$matr_mo = mysql_query($preg_mano_obra);
					
					while($mo = mysql_fetch_array($matr_mo)){
						$costo_mo_pintura = $costo_mo_pintura + ( $mo['op_cantidad'] * $mo['op_costo'] );
					}
					
				}

			}
			
			// --- Calcular Utilidad ---
			$util_ref_mecanica = round(((($tot_venta_ref_mecanica - $costo_refs_mecanica) / $tot_venta_ref_mecanica) * 100),2);
			$util_mo_mecanica = round(((($tot_venta_mo_mecanica - $costo_mo_mecanica) / $tot_venta_mo_mecanica) * 100),2);
			
			$util_ref_hojal = round(((($tot_venta_ref_hojal - $costo_refs_hojal) / $tot_venta_ref_hojal) * 100),2);
			$util_mo_hojal = round(((($tot_venta_mo_hojal - $costo_mo_hojal) / $tot_venta_mo_hojal) * 100),2);
			
			$util_consum_pintura = round(((($tot_venta_mat_pintura - $costo_mat_pintura) / $tot_venta_mat_pintura) * 100),2);
			$util_mo_pintura = round(((($tot_venta_mo_pintura - $costo_mo_pintura) / $tot_venta_mo_pintura) * 100),2);
			
			
			if($export == 1){ // ---- Hoja de calculo ----
				
				// --- Celdas a grabar ----
				$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
				$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $jota = 'J'.$z;
				$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z; $n = 'N'.$z; $o = 'O'.$z;
				$p = 'P'.$z; $q = 'Q'.$z; $r = 'R'.$z; $s = 'S'.$z; $t = 'T'.$z;
				$u = 'U'.$z; $v = 'V'.$z; $w = 'W'.$z; $x = 'X'.$z; $y = 'Y'.$z;
				
				$datos_vehi = strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']);
					
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($a, $ord['orden_id'])
							->setCellValue($b, $datos_vehi)
							->setCellValue($c, $ord['orden_vehiculo_placas'])
							->setCellValue($d, constant('ORDEN_ESTATUS_' . $ord['orden_estatus']))
							->setCellValue($e, $ase[$sin['sub_aseguradora']][1]);
				
			}
			else{ // ---- HTML ----
				
				echo '				
					<tr class="' . $fondo . '">
						<td>
							<a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '">' . $ord['orden_id'] . '</a>
						</td>
						<td style="text-align: left !important;">
							' . strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']) . '
						</td>
						<td>
							' . $ord['orden_vehiculo_placas'] . '
						</td>
						<td>
							' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '
						</td>
						<td>
							 ' . $ase[$sin['sub_aseguradora']][1] . '
						</td>
						<td>';
			}
			
			if($gsub['sub_reporte'] == '0' || $gsub['sub_reporte'] == '') {
				
				if($export == 1){ // ---- Hoja de calculo ----

					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($f, 'Particular');
					
				}
				else{ // ---- HTML ----
					echo 'Particular';
				}
				
				$numpart++;
				$gsub['sub_reporte'] = '0';
				
			}
			else{
				
				if($export == 1){ // ---- Hoja de calculo ----
					
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($f, $gsub['sub_reporte']);
					
				}
				else{ // ---- HTML ----
					echo $gsub['sub_reporte'];
				}
				$numsin++;
				
			}
			
			if($export == 1){ // ---- Hoja de calculo ----

			}
			else{ // ---- HTML ----
				echo '
						</td>';
			}
			
			if($export == 1){ // ---- Hoja de calculo ----
					
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($g, number_format($tot_venta_ref_mecanica, 2))
							->setCellValue($h, number_format($costo_refs_mecanica, 2))
							->setCellValue($i, round($util_ref_mecanica, 2))
							->setCellValue($jota, number_format($tot_venta_mo_mecanica, 2))		
							->setCellValue($kkk, number_format($costo_mo_mecanica, 2))
							->setCellValue($l, round($util_mo_mecanica, 2))
							->setCellValue($m, number_format($tot_venta_ref_hojal, 2))
							->setCellValue($n, number_format($costo_refs_hojal, 2))
							->setCellValue($o, round($util_ref_hojal, 2))
							->setCellValue($p, number_format($tot_venta_mo_hojal, 2))
							->setCellValue($q, number_format($costo_mo_hojal, 2))
							->setCellValue($r, round($util_mo_hojal, 2))
							->setCellValue($s, number_format($tot_venta_mat_pintura, 2))
							->setCellValue($t, number_format($costo_mat_pintura, 2))
							->setCellValue($u, round($util_consum_pintura, 2))
							->setCellValue($v, number_format($tot_venta_mo_pintura, 2))
							->setCellValue($w, number_format($costo_mo_pintura, 2))
							->setCellValue($x, round($util_mo_pintura, 2));
				
				$z++;
			}
			else{ // ---- HTML ----
				
				if($margutil == '') { $margutil = 0;}
				
				// --- Marcar utilidades ---
				if($util_ref_mecanica < $margutil ) {
					// ------ Si el margen de utilidad es menor a $margutil cambia el color del fondo de la celda
					$u_ref_mec = ' style="background-color: #ff6666; text-align: center;"';
					
				} else {
					$u_ref_mec = ' class="areaotra"';
				}
				
				if($util_mo_mecanica < $margutil ) {
					// ------ Si el margen de utilidad es menor a $margutil cambia el color del fondo de la celda
					$u_mo_mec = ' style="background-color: #ff6666; text-align: center;"';
					
				} else {
					$u_mo_mec = ' class="areaotra"';
				}
				
				if($util_ref_hojal < $margutil ) {
					// ------ Si el margen de utilidad es menor a $margutil cambia el color del fondo de la celda
					$u_ref_hoj = ' style="background-color: #ff6666; text-align: center;"';
					
				} else {
					$u_ref_hoj = ' class="area6"';
				}
				
				if($util_mo_hojal < $margutil ) {
					// ------ Si el margen de utilidad es menor a $margutil cambia el color del fondo de la celda
					$u_mo_hoj = ' style="background-color: #ff6666; text-align: center;"';
					
				} else {
					$u_mo_hoj = ' class="area6"';
				}
				
				if($util_consum_pintura < $margutil ) {
					// ------ Si el margen de utilidad es menor a $margutil cambia el color del fondo de la celda
					$u_ref_pint = ' style="background-color: #ff6666; text-align: center;"';
					
				} else {
					$u_ref_pint = ' class="area7"';
				}
				
				if($util_mo_pintura < $margutil ) {
					// ------ Si el margen de utilidad es menor a $margutil cambia el color del fondo de la celda
					$u_mo_pint = ' style="background-color: #ff6666; text-align: center;"';
					
				} else {
					$u_mo_pint = ' class="area7"';
				}

				echo '
						<td>
							$ ' . number_format($tot_venta_ref_mecanica, 2) . '
						</td>
						<td>
							$ ' . number_format($costo_refs_mecanica, 2) . '
						</td>
						<td ' . $u_ref_mec . '>
							' . round($util_ref_mecanica, 2) . ' %
						</td>
						<td>
							$ ' . number_format($tot_venta_mo_mecanica, 2) . '
						</td>
						<td>
							$ ' . number_format($costo_mo_mecanica, 2) . '
						</td>
						<td ' . $u_mo_mec . '>
							' . round($util_mo_mecanica, 2) . ' %
						</td>
						<td>
							$ ' . number_format($tot_venta_ref_hojal, 2) . '
						</td>
						<td>
							$ ' . number_format($costo_refs_hojal, 2) . '
						</td>
						<td ' . $u_ref_hoj . '>
							' . round($util_ref_hojal, 2) . ' %
						</td>
						<td>
							$ ' . number_format($tot_venta_mo_hojal, 2) . '
						</td>
						<td>
							$ ' . number_format($costo_mo_hojal, 2) . '
						</td>
						<td ' . $u_mo_hoj . '>
							' . round($util_mo_hojal, 2) . ' %
						</td>
						<td>
							$ ' . number_format($tot_venta_mat_pintura, 2) . '
						</td>
						<td>
							$ ' . number_format($costo_mat_pintura, 2) . '
						</td>
						<td ' . $u_ref_pint . '>
							' . round($util_consum_pintura, 2) . ' %
						</td>
						<td>
							$ ' . number_format($tot_venta_mo_pintura, 2) . '
						</td>
						<td>
							$ ' . number_format($costo_mo_pintura, 2) . '
						</td>
						<td ' . $u_mo_pint . '>
							' . round($util_mo_pintura, 2) . ' %
						</td>
					<tr>';
				$ventas++;

			}	
			/*
			echo 'total de venta de refacciones área mecánica: ' . $tot_venta_ref_mecanica . '<br>';
			echo 'total costo de refacciones área mecánica: ' . $costo_refs_mecanica . '<br>';
			echo '% de utilidad de refacciones área mecánica: ' . $util_ref_mecanica . '<br><br>';
			
			echo 'total de venta MO de mécanica: ' . $tot_venta_mo_mecanica . '<br>';
			echo 'total costo de MO de mécanica: ' . $costo_mo_mecanica . '<br>';
			echo '% de utilidad de MO área mecánica: ' . $util_mo_mecanica . '<br><br>';
			
			echo 'total de venta de refacciones área hojalatería: ' . $tot_venta_ref_hojal . '<br>';
			echo 'total costo de refacciones área hojalatería: ' . $costo_refs_hojal . '<br>';
			echo '% de utilidad de refacciones área hojalatería: ' . $util_ref_hojal . '<br><br>';
			
			echo 'total de venta MO de hojalatería: ' . $tot_venta_mo_hojal . '<br>';
			echo 'total costo de MO de hojalatería: ' . $costo_mo_hojal . '<br>';
			echo '% de utilidad de MO área hojalatería: ' . $util_mo_hojal . '<br><br>';
			
			echo 'total de venta de refacciones área pintura: ' . $tot_venta_mat_pintura . '<br>';
			echo 'total costo de refacciones área pintura: ' . $costo_mat_pintura . '<br>';
			echo '% de utilidad de refacciones área pintura: ' . $util_consum_pintura . '<br><br>';
			
			echo 'total de venta MO de pintura: ' . $tot_venta_mo_pintura . '<br>';
			echo 'total costo de MO de pintura: ' . $costo_mo_pintura . '<br>';
			echo '% de utilidad de MO área pintura: ' . $util_mo_pintura . '<br><br>';
			*/
		}		
		
	}

	if($export == 1){ // ---- Hoja de calculo ----
		
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="utilidad_x_area.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
		
	}
	else{ // ---- HTML ----
	echo '				
					<tr class="claro">
						<td colspan="6" style="text-align:center; font-size: x-large;">
							<b>' . $ventas . ' Ventas Encontradas<br>(Montos Sin Impuestos)</b>
						</td>
						<td colspan="18"></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>'."\n";
	}
	} else {
		 
		echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}