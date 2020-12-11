 <?php

//	echo 'Estamos en la sección  consulta';
	$error = 'si'; $num_cols = 0; $total_pres = '';

	if($sub_orden_id != '') {
		$pregunta = "SELECT orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$nor = mysql_fetch_array($matriz);
		$orden_id = $nor['orden_id'];
	}
	if ($orden_id != '') {
		$pregunta = "SELECT orden_estatus, orden_categoria FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
		$error = 'no';
	}

	if ($num_cols>0 && $error === 'no') {
		echo '
<div class="page-content">
	<div class="row"><div class="col-sm-12"><div class="content-box-header"><div class="panel-title">
					<h2>Consultar Tareas O.T. ' . $orden_id . '</h2>
	</div></div></div></div>
	<br>'."\n";
		while($orden = mysql_fetch_array($matriz)){
			$orden_estatus = $orden['orden_estatus'];
			$vehiculo = datosVehiculo($orden_id, $dbpfx);
			echo '			<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="100%">
			<tr>
				<td style="text-align:left;" colspan="2"><h2>Vehículo: ' . $vehiculo['completo'] . '</h2></td>
			</tr>
			<tr>
				<td colspan="2" style="height:5px;"></td>
			</tr>
			<tr>
				<td width="75%" valign="top">
					<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="100%">
						<tr>
							<td style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">
								<div class="control">
									<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '">
										<img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo">
									</a>';
			$preg0 = "SELECT sub_aseguradora, COUNT(sub_orden_id) FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '189' GROUP BY sub_aseguradora";
			$mat0 = mysql_query($preg0) or die("ERROR: Fallo selección de tareas! 47 " . $preg0);
			$num_aseg = mysql_num_rows($mat0);
			if($num_aseg > 1) {
				echo 'Filtro por cliente: <a href="presupuestos.php?accion=consultar&orden_id=' . $orden_id . '">Todas las Tareas</a>&nbsp;';
				while($seg = mysql_fetch_array($mat0)) {
					echo '									<a href="presupuestos.php?accion=consultar&orden_id=' . $orden_id . '&sasg=' . $seg['sub_aseguradora'] . '"><img src="' . constant('ASEGURADORA_' . $seg['sub_aseguradora']) . '" alt=""></a>&nbsp;';
				}
			}
			echo '									<a href="proceso.php?accion=imprimir&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/imprimir-sot.png" alt="Imprimir Todas las SOT de la OT" title="Imprimir Todas las SOT de la OT"></a>
								</div>
							</td>
						</tr>' . "\n";
			$pregunta2 = "SELECT sub_orden_id, sub_area, COUNT(*) AS cuenta FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '189' ";
			if(isset($sasg) && $sasg != '') {
						$pregunta2 .= "AND sub_aseguradora = '" . $sasg . "' ";
					}
			$pregunta2 .= "GROUP BY sub_area";
			$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");

			while ($area = mysql_fetch_array($matriz2)) {
				if($area['sub_area'] != 6 && $area['sub_area'] != 7) { $fondo_area = 'areaotra'; }
				elseif($area['sub_area']== 6) { $fondo_area = 'area6'; }
				else { $fondo_area = 'area7'; }

				echo '						<tr>
							<td style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">
								<br><span style="font-weight:bold; font-size:1.5em;">Tareas del área ' . constant('NOMBRE_AREA_' . strtoupper($area['sub_area'])) . '</span>
							</td>
						</tr>
						<tr>
							<td style="text-align:left; vertical-align:top; border-style:solid; border-color:#666; padding:3px;" class="' . $fondo_area . ' shadow-box">';
				$pregunta3 = "SELECT * FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_area = '" . $area['sub_area'] . "' AND sub_estatus < '189' ";
				if(isset($sasg) && $sasg != '') {
					$pregunta3 .= "AND sub_aseguradora = '" . $sasg . "' ";
				}
				$pregunta3 .= "ORDER BY sub_orden_id";
				$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
				while ($tarea = mysql_fetch_array($matriz3)) {
					if ($tarea['sub_doc_adm'] != '') {
						$doc_adm='<a href="' . DIR_DOCS . $tarea['sub_doc_adm'] . '" target="_blank">Orden de Admisión</a>';
					} else {
						$doc_adm='';
					}
					$sub_orden_id= $tarea['sub_orden_id'];
					// --- Definir si se pueden mover items de la tarea ---
					$mover_refac = 'no';
					$mover_mo = 'no';
					if($_SESSION['rol05'] == 1 || $_SESSION['rol02'] == 1 || $_SESSION['rol12'] == 1){
						$mover_refac = 'si';
						$mover_mo = 'si';
					}
					if($tarea['fact_id'] != '' || $tarea['sub_descuento'] != ''){
						$mover_refac = 'no';
					}
					if($tarea['fact_id'] != '' || $tarea['sub_descuento'] != '' || $tarea['recibo_id'] != ''){
						$mover_mo = 'no';
					}
					echo '								<table class="cabeza_sot" cellpading="0" cellspacing="0" border="1" width="100%">
									<tr>
										<td style="width:30%;">
											<div class="control">
												<a name="' . $tarea['sub_orden_id'] . '"></a> 
												<span style="font-weight:bold; font-size:1.3em;">
													' . constant('NOMBRE_AREA_' . strtoupper($area['sub_area'])) . ', Tarea: ' . $sub_orden_id . '
												</span>
												<hr><b><big>' . constant('SUBORDEN_ESTATUS_' . $tarea['sub_estatus']) . '</big></b><hr>
											</div>';
					if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) {
						echo '												Presupuesto: <b><big>$ ' . number_format($tarea['sub_presupuesto'],2) . '</big></b><br>
												Refacciones: <b><big>$ ' . number_format($tarea['sub_partes'],2) . '</big></b><br>
												Consumibles: <b><big>$ ' . number_format($tarea['sub_consumibles'],2) . '</big></b><br>
												MO: <b><big>$ ' . number_format($tarea['sub_mo'],2) . '</big></b><br>';
					}
					echo '											Valuación Modificada: <b>' . $tarea['sub_fecha_presupuesto'] . '</b><br>
											Valuador: <b>' . $tarea['sub_valuador'] . '</b>
											<div class="control"><hr>
												Inicio: <b>' . $tarea['sub_fecha_inicio'] . '</b><br>
												Fin: <b>' . $tarea['sub_fecha_terminado'] . '</b><br>
												Horas estimadas: <b>' . $tarea['sub_horas_programadas'] . '</b><br>
												Horas utilizadas: <b>' . $tarea['sub_horas_empleadas'] . '</b><br>
												Reprocesos: <b>' . $tarea['sub_reprocesos'] . '</b>
											</div>
										</td>
										<td style="width:70%;">'."\n";
					if($tarea['sub_paquete_id'] != ''){
						echo '											<a href="refacciones.php?accion=paquete&paq_id=' . $tarea['sub_paquete_id'] . '" target="_blank">
												<img src="idiomas/' . $idioma . '/imagenes/paquete.png" alt="Tarea de paquete de servicio" title="Tarea de paquete de servicio" width="45" height="45">
											</a>'."\n";
					}
					echo '											<span style="font-size:1.3em; font-weight:bold;">
												Descripción de la Tarea:
											</span><br>
											<span style="font-size:1.3em;">
												' . $tarea['sub_descripcion'] . '
											</span>
											<div class="control">
												<hr style="border-style:dotted;">
												<table class="izquierda" cellpading="0" cellspacing="0" border="0" width="100%">
													<tr>
														<td>
															<img src="' . constant('ASEGURADORA_' . $tarea['sub_aseguradora']) . '" alt="" border="0">
														</td>'."\n";
					if($tarea['sub_aseguradora'] > 0) {
						echo '														<td width="100%">
															' . $lang['NumSin'] . ': <strong>' . $tarea['sub_reporte'] . '</strong><br>
															Documento: ' . $doc_adm . '<br>
															Ajustador: ' . $tarea['sub_nomajus'] . '. ID Ajustador: ' . $tarea['sub_idajus'] . '
														</td>'."\n";
					}
					if($tarea['sub_reporte'] == 'Interno' || $tarea['sub_reporte'] == 'Rines' || $tarea['sub_reporte'] == $lang['Garantía']) {
						echo '														<td width="100%">
															<strong>' . $tarea['sub_reporte'] . '</strong>
														</td>'."\n";
					}
					echo '													</tr>
												</table>
												<hr style="border-style:dotted;">
												Acciones: <br>'."\n";
					if($metodo=='c') {
						$pregunta5 = "SELECT * FROM " . $dbpfx . "acciones WHERE accion_estatus = '" . $tarea['sub_estatus'] . "' ORDER BY accion_estatus";
					} else {
						$pregunta5 = "SELECT * FROM " . $dbpfx . "acciones WHERE accion_estatus = '" . $tarea['sub_estatus'] . "' ORDER BY accion_estatus";
					}
					$matriz5 = mysql_query($pregunta5) or die("ERROR: Fallo seleccion!");
					while ($acciones = mysql_fetch_array($matriz5)) {
						$rol = $acciones['accion_codigo'];
						if ($_SESSION[$rol]=='1') {
							echo '												<a href="' . $acciones['accion_url'] . $tarea['sub_orden_id'] . '&area=' . $area['sub_area'] . '"><img src="idiomas/' . $idioma . '/imagenes/' . $acciones['accion_descripcion'] . '</a>'."\n";
						}
					}
					if($metodo=='c') {
//							$codigo_seguimiento = $orden_id . ' ' . $sub_orden_id;
						$codigo_seguimiento = $sub_orden_id;
					} else {
						$codigo_seguimiento = $sub_orden_id;
//							$codigo_seguimiento = $orden_id . ' ' . $sub_orden_id . ' ' . $tarea['sub_operador'];
					}
					if ($tarea['sub_estatus']=='111' || $tarea['sub_estatus']=='101' || $tarea['sub_estatus']=='127') {
						echo '												<a href="entrega.php?accion=seguimiento&codigo=' . $codigo_seguimiento . '"><img src="idiomas/' . $idioma . '/imagenes/aprobar-tarea.png" alt="Aprobar Tarea Concluida" title="Revisar Tarea Concluida"></a>'."\n";
					}
					if ($tarea['sub_estatus']=='129' && $tarea['sub_siniestro']=='1') {
						echo '												<a href="presupuestos.php?accion=enviar&sub_orden_id=' . $sub_orden_id . '&area=' . $area['sub_area'] . '"><img src="idiomas/' . $idioma . '/imagenes/enviar-presupuesto.png" alt="Subir Presupuesto para Aseguradora" title="Subir Presupuesto para Aseguradora"></a>'."\n";
					}
					if ($tarea['sub_estatus']=='129' && $tarea['sub_siniestro']!='1') {
						echo '												<a href="presupuestos.php?accion=valuar&sub_orden_id=' . $sub_orden_id . '&area=' . $area['sub_area'] . '"><img src="idiomas/' . $idioma . '/imagenes/modificar-presupuesto.png" alt="Confirmar Valuación" title="Confirmar Valuación"></a>'."\n";
					}
					if ($preaut == '1' && $tarea['sub_estatus'] > '103' && $tarea['sub_estatus'] < '111' && $_SESSION['rol05']== '1') {
						echo '												<a href="presupuestos.php?accion=enviar&sub_orden_id=' . $sub_orden_id . '&area=' . $area['sub_area'] . '"><img src="idiomas/' . $idioma . '/imagenes/enviar-presupuesto.png" alt="Enviar Presupuesto a Aseguradora" title="Enviar Presupuesto a Aseguradora"></a>'."\n";
					}
					if(($_SESSION['rol04']== '1' || $_SESSION['rol07']== '1' || $asignoper == '1') && ($tarea['sub_estatus'] == '104' || $tarea['sub_estatus'] == '108')) {
						if($orden['orden_categoria'] == '4' ) {
							$preg6 = "SELECT sub_orden_id FROM " . $dbpfx . "agenda WHERE sub_orden_id = '" . $tarea['sub_orden_id'] . "'";
							$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de agenda! ".$preg6);
							$fila6 = mysql_num_rows($matr6);
							if($fila6 > 0) {
								echo '												<a href="proceso.php?accion=reasignar&sub_orden_id=' . $tarea['sub_orden_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/reasignar-operario.png" alt="Cambiar Agenda para la Tarea" title="Cambiar Agenda para la Tarea"></a>';
							} else {
								echo '												<a href="proceso.php?accion=asignar&sub_orden_id=' . $tarea['sub_orden_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/asignar-agenda.png" alt="Programar tiempo para la Tarea" title="Programar tiempo para la Tarea"></a>';
							}
						} else {
							echo '												<a href="proceso.php?accion=diagnosticar&sub_orden_id=' . $tarea['sub_orden_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/usuario.png" alt="Asignar Operador" title="Asignar Operador"></a>';
						}
					}
//					if ($tarea['sub_estatus']>='104' && $tarea['sub_estatus']<='126' && $tarea['sub_estatus']!='111' && $tarea['sub_estatus']!='112' && $tarea['sub_estatus']!='120' && ($_SESSION['rol04'] == 1 || validaAcceso('1070070', $dbpfx) == 1)) {
					if ($tarea['sub_estatus']>='104' && $tarea['sub_estatus']<='126' && $tarea['sub_estatus']!='111' && $tarea['sub_estatus']!='112' && $tarea['sub_estatus']!='120') {
						echo '												<a href="seguimiento.php?accion=seguimiento&codigo=' . $codigo_seguimiento . '"><img src="idiomas/' . $idioma . '/imagenes/registrar-seguimiento.png" alt="Actualizar actividad en tarea" title="Actualizar actividad en tarea"></a>';
					}
					if (($tarea['sub_estatus'] >= '104') && ($tarea['sub_estatus'] < '112') && ($_SESSION['rol08']=='1')) {
						echo '												<a href="proceso.php?accion=refacciones&sub_orden_id=' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/surtir-refacciones.png" alt="Surtir Refacciones" title="Surtir Refacciones"></a>';
					}
					if ($tarea['sub_estatus']=='111' || $tarea['sub_estatus']=='101' || $tarea['sub_estatus']=='127') {
						echo '												<a href="entrega.php?accion=seguimiento&codigo=' . $codigo_seguimiento . '"><img src="idiomas/' . $idioma . '/imagenes/aprobar-tarea.png" alt="Revisar Tarea Concluida" title="Revisar Tarea Concluida"></a>';
					}
					if ($tarea['sub_estatus']=='129' && $tarea['sub_siniestro']=='1') {
						echo '												<a href="presupuestos.php?accion=enviar&sub_orden_id=' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/enviar-presupuesto.png" alt="Enviar Presupuesto a Aseguradora" title="Enviar Presupuesto a Aseguradora"></a>';
					}
					if ($tarea['sub_estatus']=='129' && $tarea['sub_siniestro']!='1') {
						echo '												<a href="presupuestos.php?accion=valuar&sub_orden_id=' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/realizar-presupuesto.png" alt="Presupuesto Autorizado" title="Presupuesto Autorizado"></a>';
					}
					echo '												<a href="comentarios.php?accion=agregar&orden_id=' . $orden_id . '&sub_orden_id=' . $sub_orden_id . '&area=' . $area['sub_area'] . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/agregar-comentarios.png" alt="Agregar Comentarios" title="Agregar Comentarios"></a>
												<hr>Operador Asignado a la Tarea:  ' . $tarea['sub_operador'] . ' -> ' . $usr[$tarea['sub_operador']]['nom'] . '<br>'."\n";
					echo '											</div>
										</td>
									</tr>
								</table>'."\n";

// Tablas de refaciones, materiales y MO Presupuestadas del Taller y Autorizadas para Reparación

					$total_pres = $total_pres + $tarea['sub_presupuesto'];
					$pregunta4 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "' AND op_tangible < '3' ORDER BY op_tangible,op_item";
					$matriz4 = mysql_query($pregunta4) or die("ERROR: Fallo seleccion de orden_productos!");
					$num_prods = mysql_num_rows($matriz4);
					if ($num_prods > 0) {
//						echo '<div class="control">'."\n";
						echo '												<br><span style="font-weight:bold; font-size:1.2em;">Refacciones y Consumibles de la Tarea:</span><br>
												<form action="presupuestos.php?accion=ajuprecpres&orden_id=' . $orden_id . '&ajptarea=' . $tarea['sub_orden_id'] . '" method="post" enctype="multipart/form-data" name="ajuprecpres">
													<table class="izquierda ' . $fondo_area . '" cellspacing="0" cellpadding="2" border="1"width="100%">
														<tr>
															<td colspan="1">Descripción</td>
															<td colspan="3">Solicitar</td>
															<td colspan="2">Presupuesto Solicitado</td>
															<td colspan="3">Valuación Autorizada</td>
														</tr>
														<tr>
														<td><b>Nombre</b></td>
														<td style="width:5%"><b>Min</b></td>
														<td style="width:5%"><b>Prom</b></td>
														<td style="width:5%"><b>Max</b></td>
														<td style="background-color:#FFFFB0;text-align:right; width:10%;"><b>Precio Unitario</b></td>
														<td style="background-color:#FFFFB0;text-align:right; width:10%;"><b>Subtotal</b></td>'."\n";
						echo '														<td style="background-color:#ADFFA5; text-align:right; width:10%;"><b>Precio Unitario</b></td>
														<td style="background-color:#ADFFA5; text-align:right; width:10%;"><b>Subtotal</b></td>
														<td style="text-align:left; width:5%;">
															<div style="position: relative; display: inline-block;">
																<big><a onclick="RefCons' . $tarea['sub_orden_id'] . '()" class="ayuda" >P</a></big>
																<div id="Tarea' . $tarea['sub_orden_id'] . '" class="muestra-contenido">
																	' . $ayuda['Tipo_p'] . '
																</div>
															</div>
														</td>
														<script>
															function RefCons' . $tarea['sub_orden_id'] . '() {
															document.getElementById("Tarea' . $tarea['sub_orden_id'] . '").classList.toggle("mostrar");
															}
														</script>
														</tr>'."\n";
						$ppartes_pres = 0; $precio_subtotal_pres = 0; $ppartes = 0; $precio_subtotal = 0;
						$preg5 = "SELECT op_id, prod_costo, ganador FROM " . $dbpfx . "prod_prov WHERE sub_orden_id = '" . $sub_orden_id . "'";
						$matr5 = mysql_query($preg5) or die("ERROR: Fallo seleccion de orden_productos! 301 " . $preg5);
						while($prods = mysql_fetch_array($matriz4)) {
//							echo $prods['op_tangible'];
							if($prods['op_tangible'] > 0) {
								// --- Calculando precio a solicitar del mínimo al máximo --
								$pmin = 0; $pmax = 0; $cprom = 0; $promedio = 0; $lamax = 0;
								mysql_data_seek($matr5,0);
								while($cots = mysql_fetch_array($matr5)) {
									if($cots['op_id'] == $prods['op_id']) {
										//echo 'CotOP: ' . $cots['op_id'] . ' OP: ' . $prods['op_id'] . ' Ganador: ' . $cots['ganador'] . ' Costo: ' . $cots['prod_costo'] . '<br>';
										if($cots['ganador'] == '1') {
											$pmin = round(($cots['prod_costo'] / (1 - ($utilcompras / 100))), 2);
										}
										if($cots['prod_costo'] > $lamax) { $lamax = $cots['prod_costo']; }
										$promedio = $promedio + $cots['prod_costo'];
										$cprom++;
									}
								}
								$pprom = round((($promedio / $cprom) / (1 - ($utilcompras / 100))), 2);
								$pmax = round(($lamax / (1 - ($utilcompras / 100))), 2);
								$precio_subtotal_pres = round(($prods['op_cantidad'] * $prods['op_precio_pres']), 2);
								$precio_subtotal = round(($prods['op_cantidad'] * $prods['op_precio']), 2);
								echo '														<tr>
															<td>' . $prods['op_cantidad'] . ' ' . $prods['op_nombre'] . '<br>
																<input type="hidden" name="op[' . $prods['op_id'] . '][cantidad]" value="' . $prods['op_cantidad'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][nombre]" value="' . $prods['op_nombre'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][tangible]" value="' . $prods['op_tangible'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][precio_pres]" value="' . $prods['op_precio_pres'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][precio]" value="' . $prods['op_precio'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][aseg]" value="' . $tarea['sub_aseguradora'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][sub]" value="' . $tarea['sub_orden_id'] . '" />
																Item: ' . $prods['op_item'] . ' ';
								if($mover_refac == 'si') {
									echo 'Mover: <input type="checkbox" name="p_mover[' . $prods['op_id'] . ']" value="1">';
								}
								echo '</td>'."\n";
								// --- Casillas para mínimos y máximos a solicitar --
								echo '															<td style="text-align:right;">' . number_format($pmin,2) . '</td>
															<td style="text-align:right;">' . number_format($pprom,2) . '</td>
															<td style="text-align:right;">' . number_format($pmax,2) . '</td>'."\n";
								if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) {
									echo '															<td style="text-align:right; background-color:#FFFFB0;">' . number_format($prods['op_precio_pres'],2) . '</td>
															<td style="text-align:right; background-color:#FFFFB0;">';
									if((validaAcceso('1115105', $dbpfx) == 1 || ($solovaacc != 1 && ($ajuprecpres == 1 && $_SESSION['rol05'] == 1))) && (($tarea['sub_estatus'] > 121 && $tarea['sub_estatus'] < 130) || $tarea['sub_estatus'] == 120)) {
										echo '<input style="text-align:right;" type="text" name="ajpprecio[' . $prods['op_id'] . ']" value="' . number_format($precio_subtotal_pres,2) . '" size="6" />';
									} else {
										echo number_format($precio_subtotal_pres,2);
										echo '<input type="hidden" name="ajpprecio[' . $prods['op_id'] . ']" value="' . $precio_subtotal_pres . '" />';
									}
									echo '</td>'."\n";
								} else {
									echo '															<td style="background-color:#FFFFB0; text-align:right;"></td>
															<td style="background-color:#FFFFB0; text-align:right;"></td>'."\n";
								}
								if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) {
									echo '															<td style="background-color:#ADFFA5; text-align:right;">' . number_format($prods['op_precio'],2) . '</td>
															<td style="background-color:#ADFFA5; text-align:right;">';
									if((validaAcceso('1115105', $dbpfx) == 1 || ($solovaacc != 1 && ($ajuprecpres == 1 && $_SESSION['rol05'] == 1))) && (($tarea['sub_estatus'] >= 102 && $tarea['sub_estatus'] <= 112) || $tarea['sub_estatus'] == 121) && $tarea['fact_id'] < 1) {
										echo '<input style="text-align:right;" type="text" name="ajpval[' . $prods['op_id'] . ']" value="' . number_format($precio_subtotal,2) . '" size="6" />';
									} else {
										echo number_format($precio_subtotal,2);
										echo '<input type="hidden" name="ajpval[' . $prods['op_id'] . ']" value="' . $precio_subtotal . '" />';
									}
									echo '</td>'."\n";
								} else {
									echo '															<td style="background-color:#ADFFA5; text-align:right;"></td>
															<td style="background-color:#ADFFA5; text-align:right;"></td>'."\n";
								}
								echo '															<td style="text-align:right;">';
								if($prods['op_autosurtido'] > 0) {
									echo '<a href="pedidos.php?accion=consultar&pedido=' . $prods['op_pedido'] . '" target="_blank"><img src="idiomas/' . $idioma . '/imagenes/tipopedido-' . $prods['op_autosurtido'] . '.png" border="0" width="16" height="16"></a>';
								}
								if($prods['op_ok'] == '1') {
									echo '<img src="idiomas/' . $idioma . '/imagenes/ok.png" border="0" width="16" height="16">';
								} else {
									echo '<img src="idiomas/' . $idioma . '/imagenes/no-16.png" border="0" width="16" height="16">';
								}
								if((validaAcceso('1115105', $dbpfx) == 1 || ($solovaacc != 1 && ($ajuprecpres == 1 && $_SESSION['rol05'] == 1))) && $tarea['fact_id'] < 1 && $tarea['recibo_id'] < 1 && $prods['op_pedido'] < 1) {
									echo '<img src="idiomas/' . $idioma . '/imagenes/eliminar-16x16.png" border="0" width="16" height="16" alt="Eliminar Item" title="Eliminar Item"><input type="checkbox" name="borraop[' . $prods['op_id'] . ']" value="1" />';
								}
								if($tarea['fact_id'] > 0) {
									echo '<img src="idiomas/' . $idioma . '/imagenes/facturado.png" border="0" width="16" height="16" alt="Item Facturado" title="Item Facturado">';
								}
								echo '</td>
														</tr>'."\n";
								$ppartes_pres = $ppartes_pres + $precio_subtotal_pres;
								$ppartes = $ppartes + $precio_subtotal;
							}
						}

						echo '														<tr>
															<td style="text-align:left;" colspan="4">';
						if($mover_refac == 'si') {
							echo '<input type="submit" name="accmover" value="Mover" class="btn btn-sm btn-primary" /> ';
						}
						if(validaAcceso('1115105', $dbpfx) == 1 || ($solovalacc != 1 && ($ajuprecpres == 1 && $_SESSION['rol05'] == 1 && $tarea['sub_estatus'] < 130))) {
							echo '<input type="submit" name="accajustapres" value="' . $lang['AjusPrecPres'] . '" class="btn btn-sm btn-primary" />';
						}
						echo '</td>
															<td style="text-align:right; font-weight:bold; font-size:1.2em;" colspan="2">';
						if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) { 
							echo number_format($ppartes_pres,2);
						} 
						echo '</td>
															<td style="text-align:right; font-weight:bold; font-size:1.2em;" colspan="2">'."\n";
						if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) { 
							echo number_format($ppartes,2);
						} 
						echo '</td>
															<td>
																<input type="hidden" name="orden_id" value="' . $tarea['orden_id'] . '" />
															</td>
														</tr>
													</table>
												</form>'."\n";
// --- Presentación de Mano de Obra ---
						mysql_data_seek($matriz4, 0);
						echo '												<br><span style="font-weight:bold; font-size:1.2em;">Mano de Obra de la Tarea:</span><br>
												<form action="presupuestos.php?accion=ajuprecpres&orden_id=' . $orden_id . '&ajptarea=' . $tarea['sub_orden_id'] . '" method="post" enctype="multipart/form-data" name="ajuprecpres">
													<table class="izquierda ' . $fondo_area . '" cellspacing="0" cellpadding="2" border="1"width="100%">
														<tr>
															<td style="width:50%" colspan="1">Descripción</td>
															<td colspan="2">Presupuesto Solicitado</td>
															<td colspan="3">Valuación Autorizada</td>
														</tr>
														<tr>
														<td><b>Nombre</b></td>
														<td style="background-color:#FFFFB0;text-align:right; width:10%;"><b>Precio Unitario</b></td>
														<td style="background-color:#FFFFB0;text-align:right; width:10%;"><b>Subtotal</b></td>'."\n";
						echo '														<td style="background-color:#ADFFA5; text-align:right; width:10%;"><b>Precio Unitario</b></td>
														<td style="background-color:#ADFFA5; text-align:right; width:10%;"><b>Subtotal</b></td>
														<td style="text-align:left; width:5%;">
															<div style="position: relative; display: inline-block;">
																<big><a onclick="mO' . $tarea['sub_orden_id'] . '()" class="ayuda" >P</a></big>
																<div id="Ayudapedido2' . $tarea['sub_orden_id'] . '" class="muestra-contenido">' . $ayuda['Tipo_p'] . '</div>
															</div>
														</td>
														<script>
															function mO' . $tarea['sub_orden_id'] . '() {
															document.getElementById("Ayudapedido2' . $tarea['sub_orden_id'] . '").classList.toggle("mostrar");
															}
														</script>
														</tr>'."\n";
						$ppartes_pres = 0; $precio_subtotal_pres = 0; $ppartes = 0; $precio_subtotal = 0;
						$preg5 = "SELECT op_id, prod_costo, ganador FROM " . $dbpfx . "prod_prov WHERE sub_orden_id = '" . $sub_orden_id . "'";
						$matr5 = mysql_query($preg5) or die("ERROR: Fallo seleccion de orden_productos! 301 " . $preg5);
						while($prods = mysql_fetch_array($matriz4)) {
//							echo $prods['op_tangible'];
							if($prods['op_tangible'] == 0) {
								$precio_subtotal_pres = round(($prods['op_cantidad'] * $prods['op_precio_pres']), 2);
								$precio_subtotal = round(($prods['op_cantidad'] * $prods['op_precio']), 2);
								echo '														<tr>
															<td>' . $prods['op_nombre'] . '<br>
																<input type="hidden" name="op[' . $prods['op_id'] . '][cantidad]" value="' . $prods['op_cantidad'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][nombre]" value="' . $prods['op_nombre'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][tangible]" value="' . $prods['op_tangible'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][precio_pres]" value="' . $prods['op_precio_pres'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][precio]" value="' . $prods['op_precio'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][aseg]" value="' . $tarea['sub_aseguradora'] . '" />
																<input type="hidden" name="op[' . $prods['op_id'] . '][sub]" value="' . $tarea['sub_orden_id'] . '" />
																Item: ' . $prods['op_item'] . ' ';
								if($mover_refac == 'si') {
									echo 'Mover: <input type="checkbox" name="p_mover[' . $prods['op_id'] . ']" value="1">';
								}
								echo '</td>'."\n";
								// --- Casillas para mínimos y máximos a solicitar --
								if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) {
									echo '															<td style="text-align:right; background-color:#FFFFB0;">' . number_format($prods['op_precio_pres'],2) . '</td>
															<td style="text-align:right; background-color:#FFFFB0;">';
									if((validaAcceso('1115105', $dbpfx) == 1 || ($solovaacc != 1 && ($ajuprecpres == 1 && $_SESSION['rol05'] == 1))) && (($tarea['sub_estatus'] > 121 && $tarea['sub_estatus'] < 130) || $tarea['sub_estatus'] == 120)) {
										echo '<input style="text-align:right;" type="text" name="ajpprecio[' . $prods['op_id'] . ']" value="' . number_format($precio_subtotal_pres,2) . '" size="6" />';
									} else {
										echo number_format($precio_subtotal_pres,2);
										echo '<input type="hidden" name="ajpprecio[' . $prods['op_id'] . ']" value="' . $precio_subtotal_pres . '" />';
									}
									echo '</td>'."\n";
								} else {
									echo '															<td style="text-align:right;"></td>
															<td style="text-align:right;"></td>'."\n";
								}
								if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) {
									echo '															<td style="background-color:#ADFFA5; text-align:right;">' . number_format($prods['op_precio'],2) . '</td>
															<td style="background-color:#ADFFA5; text-align:right;">';
									if((validaAcceso('1115105', $dbpfx) == 1 || ($solovaacc != 1 && ($ajuprecpres == 1 && $_SESSION['rol05'] == 1))) && (($tarea['sub_estatus'] >= 104 && $tarea['sub_estatus'] <= 112) || $tarea['sub_estatus'] == 121) && $tarea['recibo_id'] < 1 && $tarea['fact_id'] < 1) {
										echo '<input style="text-align:right;" type="text" name="ajpval[' . $prods['op_id'] . ']" value="' . number_format($precio_subtotal,2) . '" size="6" />';
									} else {
										echo number_format($precio_subtotal,2);
										echo '<input type="hidden" name="ajpval[' . $prods['op_id'] . ']" value="' . $precio_subtotal . '" />';
									}
									echo '</td>'."\n";
								} else {
									echo '															<td style="text-align:right;"></td>
															<td style="text-align:right;"></td>'."\n";
								}
								echo '															<td style="text-align:right;">';
								if($prods['op_autosurtido'] > 0) {
									echo '<a href="pedidos.php?accion=consultar&pedido=' . $prods['op_pedido'] . '" target="_blank"><img src="idiomas/' . $idioma . '/imagenes/tipopedido-' . $prods['op_autosurtido'] . '.png" border="0" width="16" height="16"></a>';
									if($prods['op_ok'] == '1') {
										echo '<img src="idiomas/' . $idioma . '/imagenes/ok.png" border="0" width="16" height="16">';
									} else {
										echo '<img src="idiomas/' . $idioma . '/imagenes/no-16.png" border="0" width="16" height="16">';
									}
								}
								if((validaAcceso('1115105', $dbpfx) == 1 || ($solovaacc != 1 && ($ajuprecpres == 1 && $_SESSION['rol05'] == 1))) && $tarea['fact_id'] < 1 && $tarea['recibo_id'] < 1 && $prods['op_pedido'] < 1) {
									echo '<img src="idiomas/' . $idioma . '/imagenes/eliminar-16x16.png" border="0" width="16" height="16" alt="Eliminar Item" title="Eliminar Item"><input type="checkbox" name="borraop[' . $prods['op_id'] . ']" value="1" />';
								}
								if($tarea['fact_id'] > 0) {
									echo '<img src="idiomas/' . $idioma . '/imagenes/facturado.png" border="0" width="16" height="16" alt="Item Facturado" title="Item Facturado">';
								}
								if($tarea['recibo_id'] > 0) {
									echo '<img src="idiomas/' . $idioma . '/imagenes/destajo-pagado.png" border="0" width="16" height="16" alt="Destajo calculado" title="Destajo calculado">';
								}
								echo '</td>
														</tr>'."\n";
								$ppartes_pres = $ppartes_pres + $precio_subtotal_pres;
								$ppartes = $ppartes + $precio_subtotal;
							}
						}

						echo '														<tr>
															<td style="text-align:left;" colspan="1">';
						if($mover_refac == 'si') {
							echo '<input type="submit" name="accmover" value="Mover" class="btn btn-sm btn-primary" /> ';
						}
						if(validaAcceso('1115105', $dbpfx) == 1 || ($solovalacc != 1 && ($ajuprecpres == 1 && $_SESSION['rol05'] == 1 && $tarea['sub_estatus'] < 130))) {
							echo '<input type="submit" name="accajustapres" value="' . $lang['AjusPrecPres'] . '"  class="btn btn-sm btn-primary" />';
						}
						echo '</td>
															<td style="text-align:right; font-weight:bold; font-size:1.2em;" colspan="2">';
						if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) { 
							echo number_format($ppartes_pres,2);
						} 
						echo '</td>
															<td style="text-align:right; font-weight:bold; font-size:1.2em;" colspan="2">'."\n";
						if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) { 
							echo number_format($ppartes,2);
						} 
						echo '</td>
															<td>
																<input type="hidden" name="orden_id" value="' . $tarea['orden_id'] . '" />
															</td>
														</tr>
													</table>
												</form>'."\n";
					}
					echo '								<br>'."\n";
					$suborden_estatus = $tarea['sub_estatus'];
				}
				echo '
							</td>
						</tr>'."\n";
			}
			echo '
					</table>
				</td>
				<td class="control" style="vertical-align:top; text-align:left;">'."\n";

			if($mensjint == '1') {
				echo '					<form action="comentarios.php?accion=visto" method="post" enctype="multipart/form-data">
						<table cellspacing="0" cellpadding="2" border="1" width="100%">
							<tr>
								<td style="border-width:1px; border-style:solid; text-align:left;">
									Tus mensajes no leidos:<br>'."\n";
				$pregc1 = "SELECT c.orden_id, c.bit_id, c.fecha_com, c.usuario, c.comentario, c.interno, c.recordatorio, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.usuario = u.usuario AND c.interno = '3' AND c.para_usuario = '" . $_SESSION['usuario'] . "' AND fecha_visto IS NULL ORDER BY c.bit_id DESC";
				$matrc1 = mysql_query($pregc1) or die("ERROR: Fallo selección de comentarios! " . $pregc1);
				$j=0; $fondo='claro';
				while($comen = mysql_fetch_array($matrc1)) {
					echo '
									<p class="' . $fondo . '" style="margin-top:0px; padding-left:3px; padding-right:3px;"><a href="ordenes.php?accion=consultar&orden_id=' . $comen['orden_id'] . '" target="_blank">Mensaje en la OT ' . $comen['orden_id'] . '</a><br>
									El ' . $comen['fecha_com'] . ' de ' . $comen['nombre'] . ' ' . $comen['apellidos'] . '<br>
										' . $comen['comentario']. '<br>'."\n";
					if($comen['recordatorio'] != 1) {
						echo '									<button name="visto" value="' . $comen['bit_id'] . '" type="submit">' . $lang['Visto'] . '</button>'."\n";
					}
					echo '										<input type="hidden" name="orden_id" value="' . $comen['orden_id'] . '" />
										<input type="hidden" name="pagina" value="presupuestos.php" />
									</p>'."\n";
					$j++;
					if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
				}
				echo '
								</td>
							</tr>
						</table>
					</form>'."\n";
			}
			echo '				</td>
			</tr>'."\n";
			}
			echo'			<tr>
				<td style="text-align:left;" colspan="2">'."\n";
			if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1') {
				echo '					<br><b><big>Presupuesto Total: <big>$' . number_format($total_pres,2) . '</big></big><b><br>';
			}
			echo '					<div class="control">
						<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>
					</div>
				</td>
			</tr>'."\n";
			$_SESSION['orden_id'] = $orden_id;
			echo '			<tr>
				<td style="text-align:left;" colspan="2">
					<div class="autoriza">
						<table cellpadding="0" cellspacing="0" border="1" width="100%">
							<tr>
								<td style="text-align:center;" valign="top">
									Acepto el presente presupuesto:<br>
									Fecha, nombre y firma del cliente.<br><br><br><br>
								</td>
								<td style="text-align:center;" valign="top">
									Nombre y firma del Asesor de servicio
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>'."\n";
	} else {
		$mensaje .='No se encontraron registros con esos datos.</br>';
		echo '<p>' . $mensaje . '</p>';
	}

?>
