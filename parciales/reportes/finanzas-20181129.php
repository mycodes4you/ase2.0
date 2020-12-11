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
	
	if($area_filtro > 0){ // --- Si el filtro de área está activado se consultan las tareas de esa área
		$encabezado_area = ' del área de ' . constant('NOMBRE_AREA_' . $area_filtro);
	} else{
		$encabezado_area = '';
	}

	if($export == 1){ // ---- Hoja de calculo ----

		$encabezado = ' OTs ' . $nomrep . ' del ' . $t_ini . ' al ' . $t_fin . ' ' . $encabezado_area . ' ' . $rangofe;

		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'FINANZAS: ' . $nombre_agencia;
	
		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("REPORTE DE FINANZAS")
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
					->setCellValue("F4", "Siniestro")
					->setCellValue("G4", "Venta de Ref")
					->setCellValue("H4", "Venta de Materiales")
					->setCellValue("I4", "Venta de MO")
					->setCellValue("J4", "Monto de Venta")
					->setCellValue("K4", "Costo Aseguradora")
					->setCellValue("L4", "Fact/Rem")
					->setCellValue("M4", "Monto facturado")
					->setCellValue("N4", "Monto por facturar")
					->setCellValue("O4", "Cobrada")
					->setCellValue("P4", "Descuentos")
					->setCellValue("Q4", "Costo Ref")
					->setCellValue("R4", "Costo Mats")
					->setCellValue("S4", "Costo TOT")
					->setCellValue("T4", "Costo Destajo")
					->setCellValue("U4", "Costo Total")
					->setCellValue("V4", "% Utilidad");
		$z= 5;
		
	}
	else { // ---- HTML ----
	
		
		$encabezado = ' OTs ' . $nomrep . ' del ' . $t_ini . ' al ' . $t_fin . ' ' . $encabezado_area . ' <small><span style="font-weight:bold; color: red;">' . $rangofe . '</span></small>';
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
						<th>OT</th>
						<th>Vehículo</th>
						<th>' . $lang['Placa'] . '</th>
						<th>Estatus</th>
						<th>Cliente</th>
						<th>Siniestro</th>
						<td class="area6"><b>Venta de<br>Ref</b></td>
						<td class="area6"><b>Venta de<br>Materiales</b></td>
						<td class="area6"><b>Venta de<br>MO</b></td>
						<td class="area6"><b>Monto de<br>Venta</b></td>
						<td class="areaotra"><b>Costo<br>Aseguradora</b></td>
						<td class="area6"><b>Fact/Rem</b></td>
						<td class="area6" style="text-align:center;"><b>Monto<br>facturado</b></td>
						<td class="area6" style="text-align:center;"><b>Monto por<br>facturar</b></td>
						<td class="area6"><b>Cobrada</b></td>
						<td class="area7" style="text-align:center;"><b>Descuentos</b></td>
						<td class="area7"><b>Costo Ref</b></td>
						<td class="area7" style="text-align:center;"><b>Costo<br>Mats</b></td>
						<td class="area7" style="text-align:center;"><b>Costo<br>TOT</b></td>
						<td class="area7" style="text-align:center;"><b>Costo<br>Destajo</b></td>
						<td class="area7" style="text-align:center;"><b>Costo<br>Total</b></td>
						<td class="areaotra" style="text-align:center;"><b>% Utilidad</b></td>
					</tr>'."\n";
	$fondo = 'claro';
		
	}

	$j = 0;
	$totpres = 0; $totpart = 0; $totsin = 0; $numpart = 0; $numsin = 0; $pvppart = 0; $pvpsin = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	$total_x_busqueda = 0;
	
	while($ord = mysql_fetch_array($matr0)) {
		
		//echo 'orden '. $ord['orden_id'] .  '<br>';
		
		$preg2 = "SELECT DISTINCT sub_reporte, sub_aseguradora, fact_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
		
		if($estatusflt == '7') { // --- Cobrables No Facturados ---
			$preg2 .= " AND sub_presupuesto > '0' ";
		}
		
		if($asegflt != '') {
			$preg2 .= " AND sub_aseguradora = '" . $asegflt . "' ";
		}
		
		if($area_filtro > 0){ // --- Si el filtro de área está activado se consultan las tareas de esa área
			$preg2 .= " AND sub_area = '" . $area_filtro . "' ";
		}
		
		$preg2 .= " GROUP BY sub_reporte";
		//echo '<br>' . $preg2 . '<br>';
		$matr2 = mysql_query($preg2);
		
		while($gsub = mysql_fetch_array($matr2)) {
			
			if($estatusflt == '7'){
				// ---- Recorrer las facturas y recibos dentro de las tareas ----
				$preg4 = "SELECT fact_id, recibo_id, sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus >= '112' AND sub_estatus <= '115' AND sub_reporte = '" . $gsub['sub_reporte'] . "' AND sub_presupuesto > '0' ";
				$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de tareas sin facturar o sin destajos! " . $preg4);
				while ($finan = mysql_fetch_array($matr4)) {
				
					if(is_null($finan['fact_id'])) {
						// ---- Almacenar el reporte o trabajo particular de la orden ----
						$oculta = 'no';
						//echo 'orden : ' . $ord['orden_id'] . ', suborden por facturar ' . $finan['sub_orden_id'] . '<br>';
					
					} else{
						$oculta = 'si';
					}
				}
			} else{
				$oculta = 'no';
			}
		
			
			if($estatusflt == '5' || $estatusflt == '6'){
				
				if(is_null($gsub['fact_id']) && $gsub['fact_tipo'] < 3){
					$oculta = 'si';
				} else{
					
					// ---  Revisar que la factura este pagada ---
					$preg_fact = "SELECT orden_id, fact_cobrada FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '" . $gsub['fact_id'] . "'";
					$matr_fact = mysql_query($preg_fact) or die("ERROR: Fallo selección de facturas! " . $preg_fact);
					$info_fact = mysql_fetch_assoc($matr_fact);
					
					//echo $info_fact['fact_cobrada'] . '<br>';
					
					if($estatusflt == '5'){ // --- Cobradas ---
						//echo 'cobradas<br>';
						if($info_fact['fact_cobrada'] != 1){	
							$oculta = 'si';
						}
					}
					
					if($estatusflt == '6'){ // --- NO obradas ---
						//echo 'no cobradas<br>';
						if($info_fact['fact_cobrada'] != 0){
							$oculta = 'si';
						}
					}
					
				}
				
			}

			if($oculta == 'no'){
				
				//if($estatusflt == '5') {
				$total_x_busqueda = $total_x_busqueda + 1;
			
				if($export == 1){ // ---- Hoja de calculo ----
				
					// --- Celdas a grabar ----
					$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
					$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $jota = 'J'.$z;
					$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z; $n = 'N'.$z; $o = 'O'.$z;
					$p = 'P'.$z; $q = 'Q'.$z; $r = 'R'.$z; $s = 'S'.$z; $t = 'T'.$z;
					$u = 'U'.$z; $v = 'V'.$z;
				
					$datos_vehi = strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']);
				
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($a, $ord['orden_id'])
								->setCellValue($b, $datos_vehi)
								->setCellValue($c, $ord['orden_vehiculo_placas'])
								->setCellValue($d, constant('ORDEN_ESTATUS_' . $ord['orden_estatus']))
								->setCellValue($e, $ase[$gsub['sub_aseguradora']][1]);
				
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
							' . $ase[$gsub['sub_aseguradora']][1] . '
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
			
				$preg3 = "SELECT sub_orden_id, sub_deducible, sub_presupuesto, sub_partes, sub_consumibles, sub_mo, fact_id FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $gsub['sub_reporte'] . "' AND orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
				
				if($area_filtro > 0){ // --- Si el filtro de área está activado se consultan las tareas de esa área
					$preg3 .= " AND sub_area = '" . $area_filtro . "' ";
				}
				//echo '<br>' . $preg3;
				
				$matr3 = mysql_query($preg3) or die("Falló selección de subordenes");
				$pres = 0; $partes = 0; $cons = 0; $mo = 0;
				$nppe = 0; $cppe = 0; $cpm = 0; $npm = 0;
				$dedu = 0; $fila5 = 0; $opped = 0; $occ = 1;
				$costcons = 0; $costtot = 0; $costref = 0; $costdest = 0;
				$monto_no_fact = 0;
				
				while($sub = mysql_fetch_array($matr3)) {

					
					// --- Calcular montos no facturados ---
					if(is_null($sub['fact_id'])){
						$monto_no_fact = $monto_no_fact + $sub['sub_presupuesto'];
					}
					
					$pres = $pres + $sub['sub_presupuesto'];
					$partes = $partes  + $sub['sub_partes'];
					$cons = $cons  + $sub['sub_consumibles'];
					$mo = $mo  + $sub['sub_mo'];
					if($sub['sub_deducible'] > $dedu) { $dedu = $sub['sub_deducible']; }
					$preg5 = "SELECT op_id, op_cantidad, op_costo, op_precio, op_pedido, op_autosurtido, op_pres, op_item_seg, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '".$sub['sub_orden_id']."' ";
					
					// ------ Filtrado de refacciones por código de producto ---------------
					if($fltprodcod == '1') { $preg5 .= " AND (op_codigo IS NULL OR op_codigo NOT LIKE '%PAG%')"; }

					$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden_productos 3!");
					//echo '<br>' . $preg5;
					$fila6 = mysql_num_rows($matr5);
					$fila5 = $fila5 + $fila6;
					
					if(mysql_num_rows($matr5) > 0) {
						while($op = mysql_fetch_array($matr5)) {
							if($op['op_tangible'] == '1') {
								$cppe = $cppe + ($op['op_cantidad'] * $op['op_costo']);
								if($op['op_pedido'] < 1 && is_null($op['op_item_seg'])) {
									$opped = 1;
								}
								if($op['op_autosurtido'] == '1' && $op['op_pedido'] > 0) {
									$nppe++;
									if($op['op_costo'] > 1) {
										$npm = $npm + $op['op_cantidad'];
										$cpm = $cpm + ($op['op_cantidad'] * $op['op_costo']);
									} elseif($op['op_autosurtido'] == '1') {
										$occ = 0;
									}
								}
								if($op['op_autosurtido'] != '1' && $op['op_pedido'] > '0') {
									$costref = $costref + ($op['op_cantidad'] * $op['op_costo']);
								}
							} elseif($op['op_tangible'] == '2') {
								$costcons = $costcons + ($op['op_cantidad'] * $op['op_costo']);
							} elseif($op['op_tangible'] == '0') {
								$costtot = $costtot + ($op['op_cantidad'] * $op['op_costo']);
							}
						}
					}
				}

				// --- Determinar costos de MO por Destajos ---
				$preg6 = "SELECT monto FROM " . $dbpfx . "destajos_elementos WHERE orden_id = '" . $ord['orden_id'] . "' AND reporte = '" . $gsub['sub_reporte'] . "'";
				$matr6 = mysql_query($preg6) or die("Falló selección de destajos. ".$preg6);
				while($dest = mysql_fetch_array($matr6)) {
					$costdest = $costdest + $dest['monto'];
				}

				$cppp = ($cpm / $npm);
				$nvomo = $cons + $mo;
				$asecc = 'black';
				if ($occ > 0 && $cpm > 0) {
					$ocp++; $tcpm = $tcpm + $cpm; $asecc = 'black';
				} elseif($nppe == 0 && $cppe > 0 ) {
					$asecc = 'black';
				} else {
					$asecc = 'red';
				}

				if( $opped == 1 ) {
					$asecc = 'red';
				}
				
				//$mirada = '<!-- occ-' . $occ . ' cpm-' . $cpm . ' nppe-' . $nppe . ' cppe-' . $cppe . ' opped-' . $opped . ' -->';
				$occ = 1;

				if($export == 1){ // ---- Hoja de calculo ----
				
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($g, $partes)
								->setCellValue($h, $cons)
								->setCellValue($i, $mo)
								->setCellValue($jota, $pres);
				
				}
				else{ // ---- HTML ----
					echo '
						<td class="area6" style="text-align:right;">
							$' .  number_format($partes, 2) . '</td>
						<td class="area6" style="text-align:right;">
							$' .  number_format($cons, 2) . '
						</td>
						<td class="area6" style="text-align:right;">
							$' .  number_format($mo, 2) . '
						</td>
						<td class="area6" style="text-align:right;">
							$' .  number_format($pres, 2) . '
						</td>
						<td class="areaotra" style="text-align:right; color:' . $asecc . ';">';
				}
			
				if($opped == 1 || $nppe > 0 || ($gsub['sub_reporte'] != '0' && $partes > '0' && ($fila5 > 0 && $cppe == 0 ))) {
				
					if($export == 1){ // ---- Hoja de calculo ----
					
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($kkk, $cpm);
					
					}
					else{ // ---- HTML ----
						echo '$' .  number_format($cpm, 2) . $mirada;
					}
				
				}
			
				if($export == 1){ // ---- Hoja de calculo ----
				}
				else{ // ---- HTML ----
					echo '
						</td>';
				}
			
				$totpres = $totpres + $pres;
				$totpartes = $totpartes + $partes;
				$totcons = $totcons + $cons;
				$totmo = $totmo + $mo;
				$totcostref = $totcostref + $costref;
				$totcostcon = $totcostcon + $costcons;
				$totcosttot = $totcosttot + $costtot;
				$totcostdes = $totcostdes + $costdest;
				if($gsub['sub_reporte'] == '0' || $gsub['sub_reporte'] == '') {
					$totpartespart = $totpartespart + $partes;
					$totconspart = $totconspart + $cons;
					$totmopart = $totmopart + $mo;
					$totpart = $totpart + $pres;
					$totcostconpart = $totcostconpart + $costcons;
					$totcosttotpart = $totcosttotpart + $costtot;
					$totcostdespart = $totcostdespart + $costdest;
					$totcostrefpart = $totcostrefpart + $costref;
					if($pres > 0) { $pvppart++; }
				} else {
					$totpartessin = $totpartessin + $partes;
					$totconssin = $totconssin + $cons;
					$totmosin = $totmosin + $mo;
					$totsin = $totsin + $pres;
					$totcostconsin = $totcostconsin + $costcons;
					$totcosttotsin = $totcosttotsin + $costtot;
					$totcostdessin = $totcostdessin + $costdest;
					$totcostrefsin = $totcostrefsin + $costref;
					if($pres > 0) { $pvpsin++; }
				}
				
				
				$preg1 = "SELECT fact_num, fact_fecha_emision, fact_tipo, fact_fecha_cobrada, fact_cobrada, fact_monto, fact_id FROM " . $dbpfx . "facturas_por_cobrar WHERE orden_id = '" . $ord['orden_id'] . "' AND fact_tipo < '4' AND reporte = '" . $gsub['sub_reporte'] . "' AND fact_cobrada < 2 ";
				$matr1 = mysql_query($preg1) or die("Falló selección de subordenes");
			
				$cobrada = 0; $fact_cob = 0; $dedu_cob = 0; $dedu_no = 0; $fact_num = ''; $fact_fech = ''; $dedu_num = ''; $fech_cob = ''; $fech_deducob = '';
				$total_facts_sin_iva = 0; $total_press_sin_iva = 0; $aju_sin_iva = 0; $imp_reducir = 1 + $impuesto_iva;
				
				// ---- Consultar los montos de las tareas y buscar ajustes  para compararlo con lo facturado--	
				
				while($fact = mysql_fetch_array($matr1)) {
					if($fact['fact_tipo'] < '3') {

						// --- Quitar iva a la factura ---
						$fact_sin_iva = ($fact['fact_monto']) / $imp_reducir;
						
						// --- Consultar ajuste administrativo (descuento) ---
						$preg_ajuste = "SELECT monto FROM " . $dbpfx . "ajusadmin WHERE orden_id = '" . $ord['orden_id'] . "' AND fact_id = '" . $fact['fact_id'] . "' ";
						$matr_ajuste = mysql_query($preg_ajuste) or die("Falló selección de ajustes");
						$hay_ajuste = mysql_num_rows($matr_ajuste);
						
						if($hay_ajuste == 1) { // --- si hay ajuste se procesa ---
							
							$ajuste = mysql_fetch_assoc($matr_ajuste);
							// --- Quitar iva al descuento ---
							$aju_sin_iva = $aju_sin_iva + ($ajuste['monto'] / $imp_reducir);
						}
						
						//echo 'ajuste de la factura ' . $fact['fact_num'] . ' ' . $aju_sin_iva . '<br>';
						
						// --- Consultar montos des las tareas facturadas ---
						$preg_tareas = "SELECT sub_presupuesto FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND fact_id = '" . $fact['fact_id'] . "' ";
						$matr_tareas = mysql_query($preg_tareas) or die("Falló selección de tareas");
						
						$presu_total = 0;
						while($tareas = mysql_fetch_array($matr_tareas)){
							
							$presu_total = $presu_total + $tareas['sub_presupuesto'];
						}
						
						if($hay_ajuste == 1){ // --- si hay ajuste se resta al presupuesto ---
							
							$presu_total = $presu_total - $aju_sin_iva;
						}
						
						//echo 'presupuesto de la factura ' . $fact['fact_num'] . ' ' . $presu_total . '<br>'; 
						
						
						// --- sumamos globales del siniestro ---
						$total_facts_sin_iva = $total_facts_sin_iva + $fact_sin_iva;
						$total_press_sin_iva = $total_press_sin_iva + $presu_total;
						
						
						$fact_num .= $fact['fact_num'] . ' ';
						if($fact['fact_fecha_emision'] != '0000-00-00 00:00:00') {
							$fact_fech = date('Y-m-d', strtotime($fact['fact_fecha_emision'])) . ' ';
						}
						if(!is_null($fact['fact_fecha_cobrada']) && $fact['fact_fecha_cobrada'] != '0000-00-00 00:00:00') {
						
							$fech_cob = date('Y-m-d', strtotime($fact['fact_fecha_cobrada'])) . ' ';
						
						}
						if($fact['fact_cobrada'] == '1') {
							$fact_cob = 1;
						} else {
							$cobrada = 2;
						}
						
					} else {
						$dedu_num = $fact['fact_num'] . ' ';
						//$dedu_fech = $fact['fact_fecha_emision'] . ' ';
						if($fact['fact_fecha_cobrada'] != '0000-00-00 00:00:00') {
							$fech_deducob = date('Y-m-d', strtotime($fact['fact_fecha_cobrada'])) . ' ';
						}
						if($fact['fact_cobrada'] == '1') {
							$dedu_cob = 1;
						} else {
							$dedu_no = 2;
						}
					}
				}
				
				// --- Redondear totales ---
				$total_facts_sin_iva = round($total_facts_sin_iva, 2);
				$total_press_sin_iva = round($total_press_sin_iva, 2);
				// --- Evaluar si lo facturado contra el presupuesto ---
				//echo 'Facturado = ' . $total_facts_sin_iva . '<br> presupuesto = ' . $total_press_sin_iva . '<br>';
				
				
				if($total_facts_sin_iva == $total_press_sin_iva){ // --- Montos facturados corresponden al presupuesto --
					$facturado = ' class="area6"';
				}
				elseif($total_facts_sin_iva < $total_press_sin_iva){ // --- Montos facturados son menores al presupuesto ---
					$facturado = ' style="background-color: #ff6666; text-align: center;"';
				}
				elseif($total_facts_sin_iva > $total_press_sin_iva){ // --- Montos facturados son mayores al presupuesto ---
					$facturado = ' style="background-color: #ff6666; text-align: center;"';
				}
				
				if($fact_cob == '1' && $cobrada == '0') {
					$cobrada = 'Si';
				} else {
					$cobrada = 'No';
				}
				if($dedu_cob == '1' && $dedu_no == '0') {
					$dedu_cob = 1;
				} else {
					$dedu_cob = 0;
				}

				$totcosts = $costref+$costcons+$costtot+$costdest+$aju_sin_iva;

				if($pres == 0 && $totcosts > 0) {
					$util = -100;
				} else {
					$util = round(((($pres - $totcosts) / $pres) * 100),2);
				}
			
				if($margutil == '') { $margutil = 0;}

				if($export == 1){ // ---- Hoja de calculo ----
				
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($l, $fact_num)
								->setCellValue($m, $total_facts_sin_iva)
								->setCellValue($n, $monto_no_fact)
								->setCellValue($o, $cobrada)
								->setCellValue($p, $aju_sin_iva)
								->setCellValue($q, $costref)
								->setCellValue($r, $costcons)
								->setCellValue($s, $costtot)
								->setCellValue($t, $costdest)
								->setCellValue($u, $totcosts);
				
				}
				else{ // ---- HTML ----
					echo '
						<td class="area6">
							<a href="entrega.php?accion=cobros&orden_id=' . $ord['orden_id'] . '" target="_blank">' . $fact_num . '</a>
						</td>
						<td ' . $facturado . '>
							$' . number_format($total_facts_sin_iva, 2) . '
						</td>
						<td class="area6">
							$' . number_format($monto_no_fact, 2) . '
						</td>
						<td class="area6">
							' . $cobrada . '
						</td>
						<td class="area7" style="text-align:right;">
							$' . number_format($aju_sin_iva, 2) . '
						</td>
						<td class="area7" style="text-align:right;">
							$' . number_format($costref, 2) . '
						</td>
						<td class="area7" style="text-align:right;">
							$' . number_format($costcons, 2) . '
						</td>
						<td class="area7" style="text-align:right;">
							$' . number_format($costtot, 2) . '
						</td>
						<td class="area7" style="text-align:right;">
							$' . number_format($costdest, 2) . '
						</td>
						<td class="area7" style="text-align:right;">
							$' . number_format($totcosts, 2) . '
						</td>
						<td ';
			
				}
			
				if($util < $margutil ) {
					// ------ Si el margen de utilidad es menor a $margutil cambia el color del fondo de la celda
					if($export == 1){ // ---- Hoja de calculo ----
					}
					else{ // ---- HTML ----
						echo ' style="background-color: #ff6666; text-align: center;"';
					}
					
				} else {
				
					if($export == 1){ // ---- Hoja de calculo ----
					}
					else{ // ---- HTML ----
						echo ' class="areaotra"';
					}
				}
			
				if($export == 1){ // ---- Hoja de calculo ----
				
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($v, $util);
					$z++;
				}
				else{ // ---- HTML ----
					echo '"><b><big>' . $util . '%</b></big></td>';
					echo '
					</tr>'."\n";
					$j++;
					if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
				}
			}
		}
	}
	$tnvomo = $totcons + $totmo;
	$prcpartes = number_format((($totpartes / $totpres) * 100), 2);
	$prcnvomo = number_format((($tnvomo /$totpres) * 100), 2);
	$prcpartespart = number_format((($totpartespart / $totpart) * 100), 2);
	$prcnvomopart = number_format(((($totconspart + $totmopart) / $totpart) * 100), 2);
	$prcpartessin = number_format((($totpartessin / $totsin) * 100), 2);
	$prcnvomosin = number_format(((($totconssin + $totmosin) / $totsin) * 100), 2);
	$pvpp = number_format(round(($totpart / $pvppart), 2),2);
	$pvps = number_format(round(($totsin / $pvpsin), 2),2);
	$totutilsin = round(((($totsin - ($totcostrefsin+$totcostconsin+$totcosttotsin+$totcostdessin)) / $totsin) * 100),2);
	$cps = number_format(round((($totcostrefsin+$totcostconsin+$totcosttotsin+$totcostdessin) / $pvpsin), 2),2);
	$totutilpart = round(((($totpart - ($totcostrefpart+$totcostconpart+$totcosttotpart+$totcostdespart)) / $totpart) * 100),2);
	$cptp = number_format(round((($totcostrefpart+$totcostconpart+$totcosttotpart+$totcostdespart) / $pvppart), 2),2);
	$totutil = round(((($totpres - ($totcostref+$totcostcon+$totcosttot+$totcostdes)) / $totpres) * 100),2);

	if($export == 1){ // ---- Hoja de calculo ----
		
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="finanzas.xls"');
		header('Cache-Control: max-age=0');


		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
		
	}
	else{ // ---- HTML ----
	echo '				
					<tr class="claro">
						<td colspan="6" style="text-align:center; font-size: x-large;">
							<b>' . $total_x_busqueda . ' Ventas Encontradas<br>(Montos Sin Impuestos)</b>
						</td>
						<td style="vertical-align:bottom;">
							Venta Total<br>Refacciones
						</td>
						<td style="vertical-align:bottom;">
							Venta Total<br>Materiales</td>
						<td style="vertical-align:bottom;">
							Venta Total<br>MO
						</td>
						<td style="vertical-align:bottom;">
							Monto Total<br>de Venta
						</td>
						<td>
							Venta Promedio
						</td>
						<td colspan="4">
						</td>
						<td style="text-align:right;">
							Costo Promedio
						</td>
						<td style="vertical-align:bottom;">
							Costo<br>Total Ref
						</td>
						<td style="vertical-align:bottom;">
							Costo<br>Total<br>Mats
						</td>
						<td style="vertical-align:bottom;">
							Costo<br>Total<br>TOT
						</td>
						<td style="vertical-align:bottom;">
							Costo<br>Total<br>Destajo
						</td>
						<td style="vertical-align:bottom;">
						Costo<br>Total
						</td>
						<td style="vertical-align:bottom;">
							Utilidad<br>Total
						</td>
					</tr>
					<tr class="obscuro">
						<td colspan="6" style="text-align:right;">
							Total Aseguradoras ('. $pvpsin .')
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totpartessin,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totconssin,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totmosin,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totsin,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . $pvps . '</b>
						</td>
						<td colspan="4">
						</td>
						<td style="text-align:right;">
							<b>' . $cps . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcostrefsin,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcostconsin,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcosttotsin,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcostdessin,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format(($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin),2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . $totutilsin . '%</b>
						</td>
					</tr>
					<tr class="obscuro">
						<td colspan="6" style="text-align:right;">
							Indices Aseguradoras
						</td>
						<td style="text-align:right;">
							<b>' . round((($totpartessin / $totsin) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . round((($totconssin / $totsin) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . round((($totmosin / $totsin) * 100), 2) . '%</b>
						</td>
						<td colspan="7">
						</td>
						<td style="text-align:right;">
							<b>' . round((($totcostrefsin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . round((($totcostconsin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . round((($totcosttotsin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . round((($totcostdessin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%</b>
						</td>
						<td colspan="2">
						</td>
					</tr>
					<tr class="claro">
						<td colspan="6" style="text-align:right;">
							Total Particulares ('. $pvppart .')
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totpartespart,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totconspart,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totmopart,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totpart,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . $pvpp . '</b>
						</td>
						<td colspan="4">
						</td>
						<td style="text-align:right;">
							<b>' . $cptp . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcostrefpart,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcostconpart,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcosttotpart,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcostdespart,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format(($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart),2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . $totutilpart . '%</b>
						</td>
					</tr>
					<tr class="claro">
						<td colspan="6" style="text-align:right;">
							Indices Particulares
						</td>
						<td style="text-align:right;">
							<b>' . round((($totpartespart / $totpart) * 100),2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . round((($totconspart / $totpart) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . round((($totmopart / $totpart) * 100), 2) . '%</b>
						</td>
						<td colspan="7">
						</td>
						<td style="text-align:right;">
							<b>' . round((($totcostrefpart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . round((($totcostconpart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . round((($totcosttotpart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . round((($totcostdespart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%</b>
						</td>
						<td colspan="2">
						</td>
					</tr>
					<tr class="obscuro">
						<td colspan="6" style="text-align:right;">Total</td><td style="text-align:right;">
							<b>' . number_format($totpartes, 2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcons, 2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totmo, 2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totpres, 2) . '</b>
						</td>
						<td colspan="6">
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcostref,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcostcon,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcosttot,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format($totcostdes,2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format(($totcostref + $totcostcon + $totcosttot + $totcostdes),2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>' . $totutil . '%</b>
						</td>
					</tr>
					<tr class="obscuro">
						<td colspan="6" style="text-align:right;">
							Utilidad Total
						</td>
						<td style="text-align:right;">
							<b>' . number_format(((($totpartes - $totcostref) / $totpartes) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format(((($totcons - $totcostcon) / $totcons) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format(((($totmo - ($totcosttot + $totcostdes)) / $totmo) * 100), 2) . '%</b>
						</td>
						<td style="text-align:right;">
							<b>' . number_format(((($totpres - ($totcostref + $totcostcon + $totcosttot + $totcostdes)) / $totpres) * 100), 2) . '%</b>
						</td>
						<td colspan="12">
						</td>
					</tr>
					<tr class="claro">
						<td colspan="6" style="text-align:right;">
							Costo de Refacciones surtidas<br>por Aseguradoras
						</td>
						<td style="text-align:right;">
							<b>' . number_format($tcpm,2) . '</b>
						</td>
						<td colspan="2">
							Costo Promedio de<br>Refacciones surtidas<br>por Aseguradoras (' . $ocp . ')
						</td>
						<td style="text-align:right;">
							<b>' . number_format(($tcpm / $ocp),2) . '</b>
						</td>
						<td colspan="12" style="color:red;">
							<b>Las cantidades en ROJO son costos incompletos<br>de refacciones surtidas por Aseguradoras</b>
						</td>
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