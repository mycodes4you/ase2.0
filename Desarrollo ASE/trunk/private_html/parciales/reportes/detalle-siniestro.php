<?php
/*************************************************************************************
*   Script de "Detalle de siniestros"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/

if ($f1125030 == '1' || $_SESSION['rol02']=='1') {

	
	$preg0 .= " AND orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'";
	$nomrep = 'Todos los Recibidos';
	$rangofe = $lang['Fecha de Recepción'];

	//echo '<big>' . $preg0 . '</big>';
	//echo 'Filtro ' . $estatusflt . '<br>';
	
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de lapso! ".$preg0);
	$filas = mysql_num_rows($matr0);

	$encabezado = ' OTs ' . $nomrep . ' del ' . $t_ini . ' al ' . $t_fin . ' <small><span style="font-weight:bold; color: red;">' . $rangofe . '</span></small>';
		
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
						<th>Cliente</th>
						<th>Siniestro</th>
						<th>Marca</th>
						<th>Tipo</th>
						<th>Modelo</th>
						<th>' . $lang['Placa'] . '</th>	
						<th>Estatus</th>
						<th>Mes aut. val. </th>
						<th>Val. Ini.</th>
						<th>Val. Fin.</th>
						<th>Piezas Sust.</th>
						<th>Piezas Sust. $</th>
						<td class="area6" style="text-align:center;"><b>Piezas Red</b></td>
						<td class="area6" style="text-align:center;"><b>Piezas Red $</b></td>
						<td class="areaotra" style="text-align:center;"><b>Piezas Taller</b></td>
						<td class="areaotra" style="text-align:center;"><b>Piezas Taller $</b></td>
						<th>Mat. Plast. $</th>
						<th>Total Ref.</th>
						<td class="area6" style="text-align:center;"><b>Piezas Rep.</b></td>
						<td class="area6" style="text-align:center;"><b>Piezas Rep. $</b></td>
						<th>Mano Obra</th>
						<td class="area7" style="text-align:center;"><b>Fech. fin. Rep.</b></td>
						<td class="area7" style="text-align:center;"><b>Fech. Entrega</b></td>
						<td class="area6" style="text-align:center;"><b>Factura</b></td>
						<td class="area6" style="text-align:center;"><b>Monto</b></td>
					</tr>'."\n";
	
	
	$fondo = 'claro';
	
	

	$j = 0;
	$totpres = 0; $totpart = 0; $totsin = 0; $numpart = 0; $numsin = 0; $pvppart = 0; $pvpsin = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	$total_x_busqueda = 0;
	
	while($ord = mysql_fetch_array($matr0)) {
		
		//echo 'orden '. $ord['orden_id'] .  '<br>';
		//echo '--------------------------------<br><br>';
		
		$preg2 = "SELECT DISTINCT sub_reporte, sub_aseguradora, fact_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' AND sub_aseguradora = '1'";
		
		if($asegflt != '') {
			$preg2 .= " AND sub_aseguradora = '" . $asegflt . "' ";
		}
		
		$preg2 .= " GROUP BY sub_reporte";
		//echo '<br>' . $preg2 . '<br>';
		$matr2 = mysql_query($preg2);
		
		// --- Consultar Info Vehículo ---
		$preg_vehiculo = "SELECT vehiculo_marca, vehiculo_tipo, vehiculo_modelo FROM " . $dbpfx . "vehiculos WHERE vehiculo_id = '" . $ord['orden_vehiculo_id'] . "'";
		$matr_vehiculo = mysql_query($preg_vehiculo) or die ("Falló ");
		$info_vehi = mysql_fetch_assoc($matr_vehiculo);
		
		while($gsub = mysql_fetch_array($matr2)) {
			
			$oculta = 'no';

			if($oculta == 'no'){
				
				$total_x_busqueda = $total_x_busqueda + 1;

				if($gsub['sub_reporte'] == '0' || $gsub['sub_reporte'] == '') {
					$gsub['sub_reporte'] = '0';
				}
				
					echo '				
					<tr class="' . $fondo . '">
						<td>
							<a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '">' . $ord['orden_id'] . '</a>
						</td>
						<td>
							' . $ase[$gsub['sub_aseguradora']][1] . '
						</td>
						<td style="text-align: left !important;">
							' . $gsub['sub_reporte'] . '
						</td>
						<td>
							' . $info_vehi['vehiculo_marca'] . '
						</td>
						<td>
							' . strtoupper($info_vehi['vehiculo_tipo']) . '
						</td>
						<td>
							' . strtoupper($info_vehi['vehiculo_modelo']) . '
						</td>
						<td>
							' . $ord['orden_vehiculo_placas'] . '
						</td>
						<td>
							' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '
						</td>'."\n";
			
			
				$preg3 = "SELECT sub_orden_id, sub_area, sub_deducible, sub_presupuesto, sub_partes, sub_consumibles, sub_mo, fact_id, sub_fecha_valaut FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $gsub['sub_reporte'] . "' AND orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
				//echo '<br>' . $preg3;
				$matr3 = mysql_query($preg3) or die("Falló selección de subordenes");
				
				$valuacion = 0;
				$presupuesto = 0;
				$num_refacciones = 0;
				$tot_piez_sust = 0;
				$num_surt_aseg = 0;
				$total_surt_aseg = 0;
				$num_surt_taller = 0;
				$total_surt_taller = 0;
				$tot_mat_plast = 0;
				$tot_piez_sin_mat_plast = 0;
				$fecha_val_aut = '';
				$mano_obra_sum = 0;
				$reparacion_sum = 0;
				$total_repardas = 0;
				$total_mo_sin_rep = 0;
				
				while($sub = mysql_fetch_array($matr3)) {

					// --- Fecha de autorización de valuación ---
					if(strtotime($sub['sub_fecha_valaut']) > 100000){
						$fecha_val_aut = date('Y-m', strtotime($sub['sub_fecha_valaut']));	
					} else{
						$fecha_val_aut = '';
					}

					$valuacion = $valuacion + $sub['sub_presupuesto'];
					
					$preg5 = "SELECT op_autosurtido, op_id, op_cantidad, op_costo, op_precio, op_pedido, op_autosurtido, op_pres, op_item_seg, op_tangible, op_nombre FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '".$sub['sub_orden_id']."' ";
					$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden_productos 3!");
					
					//echo '<br>' . $preg5;
					$fila6 = mysql_num_rows($matr5);
					$fila5 = $fila5 + $fila6;
					
					//echo 'orden ' . $ord['orden_id'] . ' tarea ' . $sub['sub_orden_id'] . '<br>';
					
					if(mysql_num_rows($matr5) > 0) {
						
						while($op = mysql_fetch_array($matr5)) {
							
							// --- calcular monto de presupuesto ---
							if($op['op_pres'] == '1'){
								
								$precio_op = $op['op_cantidad'] * $op['op_precio'];
								$presupuesto = $presupuesto + $precio_op;
							}
							
							// --- Calcular total piezas sustituidas ---
							if($op['op_pres'] == '' && $op['op_tangible'] == '1'){ // --- Pieza de valuación --- 
								
								// --- mat plast ---
								if(preg_match("/MAT.PLASTICOS/i", $op['op_nombre'])){ // --- materiales plasticos ---
									
									// --- Total sin Materiales Plasticos ---
									$precio_mat_past = $op['op_cantidad'] * $op['op_precio'];
									$tot_mat_plast = $tot_mat_plast + $precio_mat_past;
									
								} else{ // --- Refacciones ---
									$num_refacciones++;
									// --- Total con Materiales Plasticos ---
									$precio_op_sin_mat = $op['op_cantidad'] * $op['op_precio'];
									$tot_piez_sin_mat_plast = $tot_piez_sin_mat_plast + $precio_op_sin_mat;
									
									// --- Piezas surtidas por la aseguradora ---
								
									if($op['op_autosurtido'] == 1){ // --- Aseguradora ---
										$num_surt_aseg++;
										$precio_op_aseg = $op['op_cantidad'] * $op['op_precio'];
										$total_surt_aseg = $total_surt_aseg + $precio_op_aseg;
									} else{ // --- Taller ---
										$num_surt_taller++;
										$precio_op_taller = $op['op_cantidad'] * $op['op_precio'];
										$total_surt_taller = $total_surt_taller + $precio_op_taller;
									}
									
								}
								
								// --- Total con Materiales Plasticos ---
								$precio_op_val = $op['op_cantidad'] * $op['op_precio'];
								$tot_piez_sust = $tot_piez_sust + $precio_op_val;
								
								
							}
							
							// --- Buscar piezas reparadas ---
							if($op['op_pres'] == '' && $op['op_tangible'] == '0'){ // --- piezas reparadas --- 
							
								// --- Buscar si el op_producto corresponde a una reparación ---
								//echo ' ' . $op['op_nombre'] . '<br>';
								// --- Si los conceptos son diferentes a montar y desmontar ---
								if(preg_match("/DES.MON/i", $op['op_nombre']) || preg_match("/SUSTITUIR/i", $op['op_nombre']) || preg_match("/D.M/i", $op['op_nombre']) || preg_match("/DES-MONTAR/i", $op['op_nombre']) || preg_match("/SUST/i", $op['op_nombre']) || preg_match("/DESMONTA/i", $op['op_nombre']) || preg_match("/CAMBIAR/i", $op['op_nombre'])){
									// --- Mano de obra ---
									//echo 'MANO OBRA ' . $op['op_nombre'] . '<br>';
									$precio_op_mo = $op['op_cantidad'] * $op['op_precio'];
									$total_mo_sin_rep = $total_mo_sin_rep + $precio_op_mo;
									

								} else{
									if($sub['sub_area'] == 6){ // --- si pertenece a hojalatería se considera como reparación ---
										// --- reparación ---
										$reparacion_sum++;
										//echo 'REPARACIÓN ' . $op['op_nombre'] . '<br>';
										
										// --- Total reparadas ---
										$precio_op_rep = $op['op_cantidad'] * $op['op_precio'];
										$total_repardas = $total_repardas + $precio_op_rep;
									}
								}
								
							}
						}
					}
				}

				//echo 'Sum mano de obra ' . $mano_obra_sum . '<br>';
				//echo 'Sum reparacion ' . $reparacion_sum . '<br>';
				echo '
						<td>
							' . $fecha_val_aut . '
						</td>
						<td style="text-align:right;">
							$' .  number_format($presupuesto, 2) . '
						</td>
						<td style="text-align:right;">
							$' .  number_format($valuacion, 2) . '
						</td>
						<td style="text-align:right;">
							' .  $num_refacciones . '
						</td>
						<td style="text-align:right;">
							$' .  number_format($tot_piez_sin_mat_plast, 2) . '
						</td>
						<td class="area6" style="text-align:right;">
							' . $num_surt_aseg . '
						</td>
						<td class="area6" style="text-align:right;">
							$' .  number_format($total_surt_aseg, 2) . '
						</td>
						<td class="area6" style="text-align:right;">
							' . $num_surt_taller . '
						</td>
						<td class="area6" style="text-align:right;">
							$' .  number_format($total_surt_taller, 2) . '
						</td>
						<td class="area6" style="text-align:right;">
							$' .  number_format($tot_mat_plast, 2) . '
						</td>
						<td class="area6" style="text-align:right;">
							$' .  number_format($tot_piez_sust, 2) . '
						</td>'."\n";
				
				
				$preg1 = "SELECT fact_num, fact_fecha_emision, fact_tipo, fact_fecha_cobrada, fact_cobrada, fact_monto, fact_id FROM " . $dbpfx . "facturas_por_cobrar WHERE orden_id = '" . $ord['orden_id'] . "' AND fact_tipo < '4' AND reporte = '" . $gsub['sub_reporte'] . "' AND fact_cobrada < 2 ";
				$matr1 = mysql_query($preg1) or die("Falló selección de subordenes");
				
				$facts_siniestro = '';
				$monto_facturado = 0;
				
				// ---- Consultar los montos de las tareas y buscar ajustes  para compararlo con lo facturado--	
				while($fact = mysql_fetch_array($matr1)) {
					
					if($fact['fact_tipo'] < '3') {
						
						$facts_siniestro = $facts_siniestro . $fact['fact_num'] . ' ';
						$monto_facturado = $monto_facturado + $fact['fact_monto'];
						
					}
				}
				
				if($ord['orden_fecha_de_entrega'] == ''){
					$fecha_entrega = '';
				} else{
					$fecha_entrega = date('Y-m-d', strtotime($ord['orden_fecha_de_entrega']));
				}
				
				if($ord['orden_fecha_proceso_inicio'] == ''){
					$fecha_ini_proceso = '';
				} else{
					$fecha_ini_proceso = date('Y-m-d', strtotime($ord['orden_fecha_proceso_inicio']));
				}
				
				echo '
						<td>
							' . $reparacion_sum . '
						</td>
						<td>
							$' .  number_format($total_repardas, 2) . '
						</td>
						<td>
							$' .  number_format($total_mo_sin_rep, 2) . '
						</td>
						<td>
							' . $fecha_ini_proceso . '
						</td>
						<td>
							' . $fecha_entrega . '
						</td>
						<td>
							' . $facts_siniestro . '
						</td>
						<td>
							$' . number_format($monto_facturado, 2) . '
						</td>'."\n";

				echo '
					</tr>'."\n";
				$j++;
				if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
			}
		}
	}
	

	echo '
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>'."\n";
	} else {
		 
		echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}