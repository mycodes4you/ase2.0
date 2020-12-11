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
	<div class="row"> <!- box header del título. -->
		<div class="col-sm-12">
	  		<div class="content-box-header">
				<div class="panel-title">
		  			<h2>Consultar Tareas O.T. ' . $orden_id . '</h2>
				</div>
			</div>
		</div>
	</div>
	<br>'."\n";
		while($orden = mysql_fetch_array($matriz)){
			$orden_estatus = $orden['orden_estatus'];
			$vehiculo = datosVehiculo($orden_id, $dbpfx);
			echo '<div style="overflow-x:auto; margin: 10px;">
			<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="100%">
			<tr>
				<td style="text-align:left;" colspan="2">
					<h2>Vehículo: ' . $vehiculo['completo'] . '</h2>
				</td>
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
	  		$mat0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
	  		$num_aseg = mysql_num_rows($mat0);
	  		if($num_aseg > 1) {
	  			echo 'Filtro por cliente: <a href="presupuestos.php?accion=consultar&orden_id=' . $orden_id . '">Todas las SOT</a>&nbsp;';
	  			while($seg = mysql_fetch_array($mat0)) {
	  				echo '<a href="presupuestos.php?accion=consultar&orden_id=' . $orden_id . '&sasg=' . $seg['sub_aseguradora'] . '"><img src="' . constant('ASEGURADORA_' . $seg['sub_aseguradora']) . '" alt=""></a>&nbsp;';
	  			}
	  		}
	  		
			echo '
									<a href="proceso.php?accion=imprimir&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/imprimir-sot.png" alt="Imprimir Todas las SOT de la OT" title="Imprimir Todas las SOT de la OT"></a>
								</div>
							</td>
						</tr>' . "\n";
			
			
			
	  		$pregunta2 = "SELECT sub_orden_id, sub_area, COUNT(*) AS cuenta FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '189' ";
	  		if(isset($sasg) && $sasg != '') {
	  					$pregunta2 .= "AND sub_aseguradora = '" . $sasg . "' ";
			  		}
			$pregunta2 .= "GROUP BY sub_area";
			$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");

			$pregus = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE (rol09 ='1' || rol10='1') AND activo ='1'";
			$matrus = mysql_query($pregus) or die("ERROR: Fallo selección de usuarios!");
			while($usu = mysql_fetch_array($matrus)) {
				$usuario[$usu['usuario']] = $usu['nombre'] . ' ' . $usu['apellidos'];
			}

			while ($area = mysql_fetch_array($matriz2)) {
  				if($area['sub_area'] != 6 && $area['sub_area'] != 7) { $fondo_area = 'areaotra'; }
  				elseif($area['sub_area']== 6) { $fondo_area = 'area6'; }
  				else { $fondo_area = 'area7'; }
				
				echo '		
						<tr>
							<td style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">
								<br><span style="font-weight:bold; font-size:1.5em;">Tareas del área ' . constant('NOMBRE_AREA_' . strtoupper($area['sub_area'])) . '</span>
							</td>
						</tr>
						<tr>
							<td style="text-align:left; vertical-align:top; border-radius: 10px; border-style:solid; border-color:#666; padding:3px;" class="' . $fondo_area . ' shadow-box">';
		
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
						
						echo '			
								<table class="cabeza_sot" cellpading="0" cellspacing="0" border="1" width="100%">
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
							echo '
												Presupuesto: <b><big>$ ' . number_format($tarea['sub_presupuesto'],2) . '</big></b><br>
												Refacciones: <b><big>$ ' . number_format($tarea['sub_partes'],2) . '</big></b><br>
												Consumibles: <b><big>$ ' . number_format($tarea['sub_consumibles'],2) . '</big></b><br>
												MO: <b><big>$ ' . number_format($tarea['sub_mo'],2) . '</big></b><br>';
						}
						echo '
											Valuación Modificada: <b>' . $tarea['sub_fecha_presupuesto'] . '</b><br>
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
							echo '
											<a href="refacciones.php?accion=paquete&paq_id=' . $tarea['sub_paquete_id'] . '" target="_blank">
												<img src="idiomas/' . $idioma . '/imagenes/paquete.png" alt="Tarea de paquete de servicio" title="Tarea de paquete de servicio" width="45" height="45">
											</a>'."\n";	
						}
					
						echo '
											<span style="font-size:1.3em; font-weight:bold;">
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
						if($tarea['sub_aseguradora']>0) {
							echo '								
														<td width="100%">
															' . $lang['NumSin'] . ': <strong>' . $tarea['sub_reporte'] . '</strong><br>
															Documento: ' . $doc_adm . '<br>
															Ajustador: ' . $tarea['sub_nomajus'] . '. ID Ajustador: ' . $tarea['sub_idajus'] . '
														</td>'."\n";
						}
						if($tarea['sub_reporte'] == 'Interno' || $tarea['sub_reporte'] == 'Rines' || $tarea['sub_reporte'] == $lang['Garantía']) {
							echo '								
														<td width="100%">
															<strong>' . $tarea['sub_reporte'] . '</strong>
														</td>'."\n";
						}
						echo '						
													</tr>
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
								echo '					
												<a href="' . $acciones['accion_url'] . $tarea['sub_orden_id'] . '&area=' . $area['sub_area'] . '&sini=' . $tarea['sub_siniestro'] . '&asegura=' . $tarea['sub_aseguradora'] . '"><img src="idiomas/' . $idioma . '/imagenes/' . $acciones['accion_descripcion'] . '</a>'."\n";
							}
						}
						if($metodo=='c') {
//								$codigo_seguimiento = $orden_id . ' ' . $sub_orden_id;
							$codigo_seguimiento = $sub_orden_id;
						} else {
							$codigo_seguimiento = $sub_orden_id;
//								$codigo_seguimiento = $orden_id . ' ' . $sub_orden_id . ' ' . $tarea['sub_operador'];
						}
						if ($tarea['sub_estatus']=='111' || $tarea['sub_estatus']=='101' || $tarea['sub_estatus']=='127') {
							echo '					
												<a href="entrega.php?accion=seguimiento&codigo=' . $codigo_seguimiento . '"><img src="idiomas/' . $idioma . '/imagenes/aprobar-tarea.png" alt="Aprobar Tarea Concluida" title="Revisar Tarea Concluida"></a>'."\n";
						}
						if ($tarea['sub_estatus']=='129' && $tarea['sub_siniestro']=='1') {
							echo '					
												<a href="presupuestos.php?accion=enviar&sub_orden_id=' . $sub_orden_id . '&area=' . $area['sub_area'] . '"><img src="idiomas/' . $idioma . '/imagenes/enviar-presupuesto.png" alt="Subir Presupuesto para Aseguradora" title="Subir Presupuesto para Aseguradora"></a>'."\n";
						} 
						if ($tarea['sub_estatus']=='129' && $tarea['sub_siniestro']!='1') {
							echo '					
												<a href="presupuestos.php?accion=valuar&sub_orden_id=' . $sub_orden_id . '&area=' . $area['sub_area'] . '"><img src="idiomas/' . $idioma . '/imagenes/modificar-presupuesto.png" alt="Confirmar Valuación" title="Confirmar Valuación"></a>'."\n";
						}
						if ($preaut == '1' && $tarea['sub_estatus'] > '103' && $tarea['sub_estatus'] < '111' && $_SESSION['rol05']== '1') {
							echo '					
												<a href="presupuestos.php?accion=enviar&sub_orden_id=' . $sub_orden_id . '&area=' . $area['sub_area'] . '"><img src="idiomas/' . $idioma . '/imagenes/enviar-presupuesto.png" alt="Enviar Presupuesto a Aseguradora" title="Enviar Presupuesto a Aseguradora"></a>'."\n";
						}
						
						if(($_SESSION['rol04']== '1' || $_SESSION['rol07']== '1' || $asignoper == '1') && ($tarea['sub_estatus'] == '104' || $tarea['sub_estatus'] == '108')) {
							if($orden['orden_categoria'] == '4' ) {
								$preg6 = "SELECT sub_orden_id FROM " . $dbpfx . "agenda WHERE sub_orden_id = '" . $tarea['sub_orden_id'] . "'";
								$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de agenda! ".$preg6);
								$fila6 = mysql_num_rows($matr6);
								if($fila6 > 0) {
									echo '
												<a href="proceso.php?accion=reasignar&sub_orden_id=' . $tarea['sub_orden_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/reasignar-operario.png" alt="Cambiar Agenda para la Tarea" title="Cambiar Agenda para la Tarea"></a>';
								} else {
									echo '
												<a href="proceso.php?accion=asignar&sub_orden_id=' . $tarea['sub_orden_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/asignar-agenda.png" alt="Programar tiempo para la Tarea" title="Programar tiempo para la Tarea"></a>';
								}
							} else {
								echo '
												<a href="proceso.php?accion=diagnosticar&sub_orden_id=' . $tarea['sub_orden_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/usuario.png" alt="Asignar Operador" title="Asignar Operador"></a>';
							}
						}
//						if ($tarea['sub_estatus']>='104' && $tarea['sub_estatus']<='126' && $tarea['sub_estatus']!='111' && $tarea['sub_estatus']!='112' && $tarea['sub_estatus']!='120' && ($_SESSION['rol04'] == 1 || validaAcceso('1070070', $dbpfx) == 1)) {
						if ($tarea['sub_estatus']>='104' && $tarea['sub_estatus']<='126' && $tarea['sub_estatus']!='111' && $tarea['sub_estatus']!='112' && $tarea['sub_estatus']!='120') {
							echo '
												<a href="seguimiento.php?accion=seguimiento&codigo=' . $codigo_seguimiento . '"><img src="idiomas/' . $idioma . '/imagenes/registrar-seguimiento.png" alt="Actualizar actividad en tarea" title="Actualizar actividad en tarea"></a>';
						}
						if (($tarea['sub_estatus'] >= '104') && ($tarea['sub_estatus'] < '112') && ($_SESSION['rol08']=='1')) {
							echo '
												<a href="proceso.php?accion=refacciones&sub_orden_id=' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/surtir-refacciones.png" alt="Surtir Refacciones" title="Surtir Refacciones"></a>';
						}
						if ($tarea['sub_estatus']=='111' || $tarea['sub_estatus']=='101' || $tarea['sub_estatus']=='127') {
							echo '
												<a href="entrega.php?accion=seguimiento&codigo=' . $codigo_seguimiento . '"><img src="idiomas/' . $idioma . '/imagenes/aprobar-tarea.png" alt="Revisar Tarea Concluida" title="Revisar Tarea Concluida"></a>';
						}
						if ($tarea['sub_estatus']=='129' && $tarea['sub_siniestro']=='1') {
							echo '
												<a href="presupuestos.php?accion=enviar&sub_orden_id=' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/enviar-presupuesto.png" alt="Enviar Presupuesto a Aseguradora" title="Enviar Presupuesto a Aseguradora"></a>';
						} 
						if ($tarea['sub_estatus']=='129' && $tarea['sub_siniestro']!='1') {
							echo '
												<a href="presupuestos.php?accion=valuar&sub_orden_id=' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/realizar-presupuesto.png" alt="Presupuesto Autorizado" title="Presupuesto Autorizado"></a>';
						}
	
						// ------ Agregar las acciones fijas disponibles en todas los estatus de la Tarea -----  
						if(file_exists('particular/textos/acciones-tareas.php')) {
							include('particular/textos/acciones-tareas.php');
						}
						// ------
						echo '					
												<a href="comentarios.php?accion=agregar&orden_id=' . $orden_id . '&sub_orden_id=' . $sub_orden_id . '&area=' . $area['sub_area'] . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/agregar-comentarios.png" alt="Agregar Comentarios" title="Agregar Comentarios"></a>
												<hr>Operador Asignado a la Tarea:  ' . $tarea['sub_operador'] . ' -> ' . $usuario[$tarea['sub_operador']] . '<br>'."\n";

// ------ BUSQUEDA DE OPERADORES TRABAJANDO EN LA SUB ORDEN ---------

						$preg_ayudantes ="SELECT usuario FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" . $sub_orden_id . "' GROUP BY usuario ORDER BY usuario";
						$matr_ayudantes = mysql_query($preg_ayudantes) or die("ERROR: Fallo seleccion de ayudantes! " . $preg_ayudantes);
						while($consulta_operadores = mysql_fetch_array($matr_ayudantes)){
							if($consulta_operadores['usuario'] != $tarea['sub_operador']) {
								echo '
												Ayudante: ' . $consulta_operadores['usuario'] . ' -> ' . $usuario[$consulta_operadores['usuario']] . '<br>'."\n";
							}
						}
						echo '
												<hr>Comentarios:<br>'."\n";
						$pregcom = "SELECT * FROM " . $dbpfx . "comentarios WHERE orden_id = '$orden_id' AND sub_orden_id = '$sub_orden_id'";
						$matrcom = mysql_query($pregcom) or die("ERROR: Fallo seleccion!");
						$j=0; $fondo='claro';
						while($comen = mysql_fetch_array($matrcom)) {
							echo '
												<p class="' . $fondo . '" style="margin-top:0px; padding-left:3px; padding-right:3px;">
													' . $comen['fecha_com'] . ' -> Usuario: ' . $comen['usuario'] . '. ' . $comen['comentario'] . '
												</p>'."\n";
							$j++;
							if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
						}
						echo '
											</div>
										</td>
									</tr>
								</table>'."\n";
			
// Tablas de refaciones, materiales y MO Presupuestadas del Taller y Autorizadas para Reaparción			
			
						$total_pres = $total_pres + $tarea['sub_presupuesto'];
						$pregunta4 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '$sub_orden_id' AND op_tangible < '3' ORDER BY op_tangible,op_item";
						$matriz4 = mysql_query($pregunta4) or die("ERROR: Fallo seleccion de orden_productos!");
						$num_prods = mysql_num_rows($matriz4);
						if ($num_prods>0) {
//							echo '<div class="control">'."\n";
							echo '			<table border="1" width="100%" class="izquierda">
												<tr>
													<td width="50%" style="background-color:#FFFFB0;">
														Presupuesto del Taller:<br>
															<form action="presupuestos.php?accion=ajuprecpres&orden_id=' . $orden_id . '&ajptarea=' . $tarea['sub_orden_id'] . '" method="post" enctype="multipart/form-data" name="ajuprecpres">
																<table border="0" width="100%" class="izquierda">
																	<tr>
																		<td style="width:5%"><b>Item</b></td>
																		<td style="width:5%"><b>Cant</b></td>
																		<td style="width:50%"><b>Nombre</b></td>
																		<td style="text-align:right; width:20%;"><b>Precio</b></td>
																		<td style="text-align:right; width:20%;"><b>P</b></td>
																		<td style="text-align:right; width:20%;"><b>Subtotal</b></td>
																	</tr>'."\n";
							$ppartes = 0; $precio_subtotal = 0;
							while ($prods = mysql_fetch_array($matriz4)) {
//							echo $prods['op_tangible'];
								if($prods['op_tangible'] > 0 && $prods['op_pres'] == '1') {
									$precio_subtotal = round(($prods['op_cantidad'] * $prods['op_precio']), 6);
									echo '						
																	<tr>
																		<td align="center">' . $prods['op_item'] . '</td>
																		<td align="center">'."\n";
									if(($ajuprecpres == 1 && $_SESSION['rol05'] == 1 && $tarea['sub_estatus'] > 120 && $tarea['sub_estatus'] < 130) || validaAcceso('1115105', $dbpfx) == 1) {
										echo '
																			<input style="text-align:right;" type="text" name="ajpcant[' . $prods['op_id'] . ']" value="' . $prods['op_cantidad'] . '" size="1" />';
									} else {
										echo $prods['op_cantidad'];
									}
									echo '
																		</td>
																		<td>' . $prods['op_nombre'] . '</td>'."\n";
									if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) {
										echo '
																		<td style="text-align:right;">'."\n";
										if(($ajuprecpres == 1 && $_SESSION['rol05'] == 1 && $tarea['sub_estatus'] > 120 && $tarea['sub_estatus'] < 130) || validaAcceso('1115105', $dbpfx) == 1) {
											echo '
																			<input style="text-align:right;" type="text" name="ajpprecio[' . $prods['op_id'] . ']" value="' . $prods['op_precio'] . '" size="6" />'."\n";
										} else {
											echo 
																				number_format($prods['op_precio'],2);
										}
										echo '
																		</td>
																		<td style="text-align:right;">'."\n";
										
										if($prods['op_autosurtido'] > 0){
											
											echo '
																			<img src="idiomas/' . $idioma . '/imagenes/tipopedido-' . $prods['op_autosurtido'] . '.png" border="0" width="18" height="18">'."\n";
										}	
										
										echo '
																		</td>
																		<td style="text-align:right;">
																			' . number_format($precio_subtotal,2) . '
																		</td>'."\n";
									} else {
										echo '
																		<td style="text-align:right;"></td>
																		<td style="text-align:right;"></td>'."\n";
									}
									echo '
																	</tr>'."\n";
									$ppartes = $ppartes + $precio_subtotal;
								}
							}
							$ppartes = round($ppartes, 6);
							echo '
																	<tr>
																		<td style="text-align:right;" colspan="3">
																			<b>'."\n";
							if($tarea['sub_area']=='7') {
								echo '
																			Total Consumibles Presupuestados';
							} else {
								echo '
																			Total Refacciones Presupuestadas';
							}
							echo '
																			</b>
																		</td>
																		<td style="text-align:right;">
																			<b><big>$ '."\n";
							if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) { 
								echo '
																		' .	number_format($ppartes,2) . ' '."\n";
							} 
							echo '
																			</big></b>
																		</td>
																		<td style="text-align:right;">'."\n";
							if(($ajuprecpres == 1 && $_SESSION['rol05'] == 1 && $tarea['sub_estatus'] > 120 && $tarea['sub_estatus'] < 130) || validaAcceso('1115105', $dbpfx) == 1) {
								echo '							
																			<input type="submit" value="' . $lang['AjusPrecPres'] . '" />'."\n";
							}
							echo '										</td>
																	</tr>
																</table>
															</form>
														</td>
														<td width="50%" style="background-color:#ADFFA5;">
															Valuación Autorizada:<br>
															<form action="presupuestos.php?accion=mover_items&suborden_id=' . $sub_orden_id . '&tipo=refacciones&orden_id=' . $orden_id . '" method="post" enctype="multipart/form-data" name="ajuprecpres">
															<table border="0" width="100%" class="izquierda">
																<tr>
																	<td style="width:5%">
																		<div style="position: relative; display: inline-block;">
																			<big><a onclick="muestraAbajo' . $tarea['sub_orden_id'] . '()" class="ayuda" >Item</a></big>
																			<div id="AyudaItem' . $tarea['sub_orden_id'] . '" class="muestra-contenido">
																				' . $ayuda['Item'] . '
																			</div>
																		</div>
																	</td>
														<script>
															function muestraAbajo' . $tarea['sub_orden_id'] . '() {
    															document.getElementById("AyudaItem' . $tarea['sub_orden_id'] . '").classList.toggle("mostrar");
															}
														</script>'."\n";
						
							
							
							if($mover_refac == 'si'){
								echo '
																	<td style="width:5%">
																		<div style="position: relative; display: inline-block;">
																			<big><a onclick="muestraMover' . $tarea['sub_orden_id'] . '()" class="ayuda" >Mover</a></big>
																			<div id="AyudaMover' . $tarea['sub_orden_id'] . '" class="muestra-contenido">
																				' . $ayuda['Mover'] . '
																			</div>
																		</div>
																	</td>
														<script>
															function muestraMover' . $tarea['sub_orden_id'] . '() {
    															document.getElementById("AyudaMover' . $tarea['sub_orden_id'] . '").classList.toggle("mostrar");
															}
														</script>'."\n";	
							}
							
							echo '
																	<td style="width:5%"><b>Cant</b></td>
																	<td style="width:50%"><b>Nombre</b></td>
																	<td style="text-align:right; width:20%;"><b>Precio Unitario</b></td>
																	<td style="text-align:right; width:20%;">
																		<div style="position: relative; display: inline-block;">
																			<big><a onclick="muestrapedido' . $tarea['sub_orden_id'] . '()" class="ayuda" >P</a></big>
																			<div id="Ayudapedido' . $tarea['sub_orden_id'] . '" class="muestra-contenido">
																				' . $ayuda['Tipo_p'] . '
																			</div>
																		</div>
																	</td>
														<script>
															function muestrapedido' . $tarea['sub_orden_id'] . '() {
    															document.getElementById("Ayudapedido' . $tarea['sub_orden_id'] . '").classList.toggle("mostrar");
															}
														</script>
																	<td style="text-align:right; width:20%;"><b>Subtotal</b></td>
																</tr>'."\n";
							mysql_data_seek($matriz4, 0);
							$ppartes = 0; $precio_subtotal = 0;
							while ($prods = mysql_fetch_array($matriz4)) {
//							echo $prods['op_tangible'];
								if($prods['op_tangible'] > 0 && $prods['op_pres'] != '1') {
									$precio_subtotal = round(($prods['op_cantidad'] * $prods['op_precio']), 6);
									$class = '';
									if($prods['op_area'] == 0){
										$class = 'rojo_tenue';
									}
									echo '						
																<tr>
																	<td align="center" class="' . $class . '">' . $prods['op_item'] . '</td>'."\n";
									if($mover_refac == 'si'){
										echo '
																	<td align="center" class="' . $class . '"><input type="checkbox" name="p_mover[' . $prods['op_id'] . ']" value="1"></td>'."\n";
									}
									
									echo '
																	<td align="center">' . $prods['op_cantidad'] . '</td>
																	<td>' . $prods['op_nombre'] . '</td>
																	<td style="text-align:right;">'."\n";
									if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) {
										echo '
																		' .	number_format($prods['op_precio'],2) . '
																	</td>
																	<td style="text-align:right;">'."\n";
																	
										if($prods['op_autosurtido'] > 0){
											
											echo '
																			<img src="idiomas/' . $idioma . '/imagenes/tipopedido-' . $prods['op_autosurtido'] . '.png" border="0" width="18" height="18">'."\n";
										}
										
										echo '
																	</td>
																	<td style="text-align:right;">
																		' . number_format($precio_subtotal,2) . ' '."\n"; 
									} else {
										echo '
																	</td>
																	<td>'."\n";
									} 
									echo '
																	</td>
																</tr>'."\n";
									$ppartes = $ppartes + $precio_subtotal;
								}
							}
							$ppartes = round($ppartes, 6);
							echo '
																<tr>'."\n";
							if($mover_refac == 'si'){
									echo '
																	<td style="text-align:right;" colspan="2">
																		<input type="submit" value="Mover" class="btn btn-sm btn-primary"></input>
																	</td>'."\n";
							}
									echo '
																	<td style="text-align:right;" colspan="3">
																		<b>'."\n";
							if($tarea['sub_area']=='7') {
								echo '
																		Total Consumibles Autorizados';
							} else {
								echo '
																		Total Refacciones Autorizadas';
							}
							echo '
																		</b>
																	</td>
																	<td style="text-align:right;">
																		<b><big>$ '."\n";
							if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) { 
								echo '
																		' . number_format($ppartes,2) . ' '."\n";
							}
							echo '
																		</big></b>
																	</td>
																</tr>
															</table>
															</form>
														</td>
													</tr>
												</table>'."\n";
							mysql_data_seek($matriz4, 0);
//							echo '<div class="control">'."\n";
							echo '			
												<table border="1" width="100%" class="izquierda">
													<tr>
														<td width="50%" style="background-color:#FFFFB0;">Mano de Obra Presupuesto del Taller:<br>
															<form action="presupuestos.php?accion=ajuprecpres&orden_id=' . $orden_id . '&ajptarea=' . $tarea['sub_orden_id'] . '" method="post" enctype="multipart/form-data" name="ajumprecpres">
																<table border="0" width="100%" class="izquierda">
																	<tr>
																		<td style="width:5%"><b>Item</b></td>
																		<td style="width:5%"><b>Cant</b></td>
																		<td style="width:50%"><b>Nombre</b></td>
																		<td style="text-align:right; width:20%;"><b>Precio</b></td>
																		<td style="text-align:right; width:20%;"><b>Subtotal</b></td>
																	</tr>'."\n";
							$pmo = 0;$precio_subtotal = 0;
							while ($prods = mysql_fetch_array($matriz4)) {
								if($prods['op_tangible']==0 && $prods['op_pres'] == '1') {
									$precio_subtotal = round(($prods['op_cantidad'] * $prods['op_precio']), 2);
									echo '						
																	<tr>
																		<td align="center">
																			' . $prods['op_item'] . '
																		</td>
																		<td align="center">'."\n";
									if(($ajuprecpres == 1 && $_SESSION['rol05'] == 1 && $tarea['sub_estatus'] > 120 && $tarea['sub_estatus'] < 130) || validaAcceso('1115105', $dbpfx) == 1) {
										echo '
																			<input style="text-align:right;" type="text" name="ajpcant[' . $prods['op_id'] . ']" value="' . $prods['op_cantidad'] . '" size="1" />'."\n";
									} else {
										echo '
																			' . $prods['op_cantidad'] . ' '."\n";
									}
									echo '
																		</td>
																		<td>' . $prods['op_nombre'] . '</td>'."\n";
									if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) {
										echo '
																		<td style="text-align:right;">'."\n";
										if(($ajuprecpres == 1 && $_SESSION['rol05'] == 1 && $tarea['sub_estatus'] > 120 && $tarea['sub_estatus'] < 130) || validaAcceso('1115105', $dbpfx) == 1) {
											echo '
																			<input style="text-align:right;" type="text" name="ajpprecio[' . $prods['op_id'] . ']" value="' . $prods['op_precio'] . '" size="6" />'."\n";
										} else {
											echo '
																			' . number_format($prods['op_precio'],2) . ' '."\n";
										}
										echo '
																		</td>
																		<td style="text-align:right;">
																			' . number_format($precio_subtotal,2) . '
																		</td>'."\n";
									} else {
										echo '
																		<td></td>
																		<td></td>'."\n";
									}
									echo '
																	</tr>'."\n";
									$pmo = $pmo + $precio_subtotal;
								}
							}
							$pmo = round($pmo, 6);
							echo '						
																	<tr>
																	<td colspan="3" style="text-align:right;">
																		Total Presupuestado MO
																	</td>
																	<td style="text-align:right;">'."\n";
							if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) { 
								echo number_format($pmo,2); 
							}
							echo '
																	</td>
																	<td style="text-align:right;">'."\n";
							if(($ajuprecpres == 1 && $_SESSION['rol05'] == 1 && $tarea['sub_estatus'] > 120 && $tarea['sub_estatus'] < 130) || validaAcceso('1115105', $dbpfx) == 1) {
								echo '							
																		<input type="submit" value="' . $lang['AjusPrecPres'] . '" />'."\n";
							}
							echo '						
																	</td>
																</tr>
															</table>
														</form>
													</td>
													<td width="50%" style="background-color:#ADFFA5;">
														Mano de Obra Autorizada:<br>
														<form action="presupuestos.php?accion=mover_items&suborden_id=' . $sub_orden_id . '&tipo=mo&orden_id=' . $orden_id . '" method="post" enctype="multipart/form-data" name="ajuprecpres">
														<table border="0" width="100%" class="izquierda">
															<tr>
																<td style="width:5%"><b>Item</b></td>'."\n";
							
							if($mover_mo == 'si'){
								echo '
																<td style="width:5%"><b>Mover</b></td>'."\n";
							}
							
							echo '
																<td style="width:5%"><b>Cant</b></td>
																<td style="width:50%"><b>Nombre</b></td>
																<td style="text-align:right; width:20%;"><b>Precio Unitario</b></td>
																<td style="text-align:right; width:20%;"><b>Subtotal</b></td>
															</tr>'."\n";
							mysql_data_seek($matriz4, 0);
							$pmo = 0;$precio_subtotal = 0;
							while ($prods = mysql_fetch_array($matriz4)) {
								if($prods['op_tangible']==0 && $prods['op_pres'] != '1') {
									$precio_subtotal = round(($prods['op_cantidad'] * $prods['op_precio']), 2);
									$class = '';
									if($prods['op_area'] == 0){
										$class = 'rojo_tenue';
									}
									echo '						
															<tr>
																<td align="center" class="' . $class . '">' . $prods['op_item'] . '</td>'."\n";
									if($mover_mo == 'si'){
										echo '
																<td align="center" class="' . $class . '"><input type="checkbox" name="p_mover[' . $prods['op_id'] . ']" value="1"></td>'."\n";
									}
									
									echo '
																<td align="center">' . $prods['op_cantidad'] . '</td>
																<td>' . $prods['op_nombre'] . '</td>
																<td style="text-align:right;">'."\n";
									if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) {
										echo '
																	' . number_format($prods['op_precio'],2) . '
																</td>
																<td style="text-align:right;">
																	' . number_format($precio_subtotal,2) . ''."\n";
									} else {
										echo '
																</td>
																<td>'."\n";
									}
									echo '
																</td>
															</tr>'."\n";
									$pmo = $pmo + $precio_subtotal;
								}
							}
							$pmo = round($pmo, 6);
							echo '						
															<tr>'."\n";
							
							if($mover_mo == 'si'){
								echo '
																<td colspan="2" style="text-align:right;">
																	<input type="submit" value="Mover" class="btn btn-sm btn-primary"></input>
																</td>'."\n";
							}
							
							echo '
																<td colspan="3" style="text-align:right;">
																	<b>Total Autorizado MO</b>
																</td>
																<td style="text-align:right;">
																	<b><big>$ '."\n";
							if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1' || ($partmon == '1' && $tarea['sub_aseguradora'] < 1)) { 
								echo '
																	' . number_format($pmo,2) . ''."\n"; 
							}
							echo '
																	</big></b>
																</td>
															</tr>
														</table>
														</form>
													</td>
												</tr>
											</table>'."\n";
						}
						echo '
											<br>'."\n";
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
				echo '				
								<form action="comentarios.php?accion=visto" method="post" enctype="multipart/form-data">
									<table cellspacing="0" cellpadding="2" border="1" width="100%">
										<tr>
											<td style="border-width:1px; border-style:solid; text-align:left;">
												Tus mensajes no leidos:<br>'."\n";
				$pregc1 = "SELECT c.orden_id, c.bit_id, c.fecha_com, c.usuario, c.comentario, c.interno, c.recordatorio, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.usuario = u.usuario AND c.interno = '3' AND c.para_usuario = '" . $_SESSION['usuario'] . "' AND fecha_visto IS NULL ORDER BY c.bit_id DESC";
				$matrc1 = mysql_query($pregc1) or die("ERROR: Fallo selección de comentarios! " . $pregc1);
				$j=0; $fondo='claro';
				while($comen = mysql_fetch_array($matrc1)) {
					echo '						
												<p class="' . $fondo . '" style="margin-top:0px; padding-left:3px; padding-right:3px;">
													<a href="ordenes.php?accion=consultar&orden_id=' . $comen['orden_id'] . '" target="_blank">Mensaje en la OT ' . $comen['orden_id'] . '</a><br>
													El ' . $comen['fecha_com'] . ' de ' . $comen['nombre'] . ' ' . $comen['apellidos'] . '<br>
													' . $comen['comentario']. '<br>'."\n";

					if($comen['recordatorio'] == 1){
						
					} else {
						echo '				
													<button name="visto" value="' . $comen['bit_id'] . '" type="submit">' . $lang['Visto'] . '</button>'."\n";
					}
					echo '				
													<input type="hidden" name="orden_id" value="' . $comen['orden_id'] . '" />
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
				echo '		</td>
						</tr>'."\n";
			}
			echo'			
						<tr>
							<td style="text-align:left;" colspan="2">';
			if(($bloqueaprecio < '1' && $_SESSION['codigo'] <= $codigomon) || $infomon == '1') {
				echo '
								<br><b><big>Presupuesto Total: <big>$' . number_format($total_pres,2) . '</big></big><b>';
			}
			echo '				<br>
								<div class="control">
									<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>
								</div>
							</td>
						</tr>'."\n";
			$_SESSION['orden_id'] = $orden_id;
			echo '	
						<tr>
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
					</table></div>'."\n";
	} else {
		$mensaje .='No se encontraron registros con esos datos.</br>';
		echo '<p>' . $mensaje . '</p>';
	}

?>