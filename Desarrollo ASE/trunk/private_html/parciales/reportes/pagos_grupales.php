<?php
if ($f1125100 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1') {

	$funnum = 1105030;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if(isset($feini) == ''){
		$feini = date('Y-m-01');
		$fefin = date('Y-m-t');
	}
	if ($retorno == '1') {
		$msg = $lang['Acceso autorizado'];
	} else {
		redirigir('usuarios.php?mensaje='. $lang['acceso_error']);
	}

						$pregPagos = "SELECT SQL_CALC_FOUND_ROWS * FROM " . $dbpfx . "pedidos_pagos 
						INNER JOIN " . $dbpfx . "proveedores ON " . $dbpfx . "pedidos_pagos.prov_id = " . $dbpfx . "proveedores.prov_id ";

							if($cliente != ''){
								$pregase = "SELECT * FROM " . $dbpfx . "proveedores WHERE prov_razon_social LIKE '%$cliente%'";
								$matrase = mysql_query($pregase) or die("ERROR: Fallo selección de lapso!");
								$asenm = mysql_fetch_array($matrase);
								//$numpagos2 = mysql_num_rows($matrase);
								$clienteID = $asenm['prov_id'];
								$pregPagos .= " WHERE ". $dbpfx . "proveedores.prov_id = '$clienteID'";
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Pedidos Pagados");
								$numpagos2 = mysql_num_rows($matrPagos);
								$encabezado = ' resultado(s) del proveedor '.$asenm['prov_razon_social'].' ';
								//--- Datos Excel
								$datos = 'uncliente';
								$nombreprov = $asenm['prov_razon_social'];
								$link = '&datos='. $datos.'&clienteID='.$clienteID.'&nombreprov='.$nombreprov;


							} elseif($pedido != ''){
								$pregPagos2 = "SELECT * FROM " . $dbpfx . "pagos_facturas WHERE `pedido_id` = $pedido";
								$matrPagos2 = mysql_query($pregPagos2) or die("ERROR: Fallo selección de Pedidos Pagados");
								$pID = mysql_fetch_assoc($matrPagos2);
								$pagoID = $pID['pago_id'];
								$provid = $pID['proveedor_id'];

								$pregPagos .= " WHERE ". $dbpfx . "pedidos_pagos.pago_id = '$pagoID' LIMIT 1";
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Pedidos Pagados");
								$numpagos2 = mysql_num_rows($matrPagos);
								$encabezado = ' resultado(s) de Pedido: '.$pedido.' ';

								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Pedidos Pagados"); 
								//--- Datos Excel
								$datos = 'unpedido';
								$link = '&datos='. $datos.'&pedido='.$pedido.'&provid='.$provid;

							
							} elseif($factura != ''){
								$pregPagos2 = "SELECT * FROM " . $dbpfx . "pagos_facturas WHERE `fact_id` = $factura";
								$matrPagos2 = mysql_query($pregPagos2) or die("ERROR: Fallo selección de factura");
								$pID = mysql_fetch_assoc($matrPagos2);
								$pagoID = $pID['pago_id'];
								$provid = $pID['proveedor_id'];
								$pregPagos .= " WHERE ". $dbpfx . "pedidos_pagos.pago_id = '$pagoID'";
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de factura Pagados");
								$numpagos2 = mysql_num_rows($matrPagos);
								$encabezado = ' resultado(s) de factura: '.$factura.' ';
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de factura Pagados"); 
								//--- Datos Excel
								$datos = 'unafactura';
								$link = '&datos='. $datos.'&factura='.$factura.'&provid='.$provid;

							} elseif($referencia != ''){
								
								$pregPagos .= " WHERE ". $dbpfx . "pedidos_pagos.pago_referencia = '$referencia'";
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Pedidos Pagados");
								$numpagos2 = mysql_num_rows($matrPagos);
								$encabezado = ' resultado(s) de Referencia: '.$referencia.' ';

								$datos = 'unareferencia';
								$link = '&datos='. $datos.'&referencia='.$referencia;

							} 
							elseif($tipo_pago != ''){
								$pregase = "SELECT * FROM " . $dbpfx . "proveedores WHERE prov_razon_social LIKE '%$cliente%'";
								$matrase = mysql_query($pregase) or die("ERROR: Fallo selección de lapso!");
								$asenm = mysql_fetch_array($matrase);
								//$numpagos2 = mysql_num_rows($matrase);
								$clienteID = $asenm['prov_id'];
								$pregPagos .= " WHERE ". $dbpfx . "pedidos_pagos.pago_tipo = '$tipo_pago'";
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Pedidos Pagados");
								
								$numpagos2 = mysql_num_rows($matrPagos);
								$encabezado = ' resultado(s) del Tipo de Pago: '.constant("TIPO_PAGO_".$tipo_pago).' ';

								$datos = 'tipopago';
								$link = '&datos='. $datos.'&tipopago='.$tipo_pago;
							}
							elseif($cuentas != ''){
								
								$pregPagos .= " WHERE ". $dbpfx . "pedidos_pagos.pago_cuenta = '$cuentas'";
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Pedidos Pagados");
								$numpagos2 = mysql_num_rows($matrPagos);

								$pregCuenta = "SELECT * FROM " .$dbpfx."cont_cuentas WHERE ban_cuenta = '$cuentas' AND ban_activo = '1'";
								$matrCuenta = mysql_query($pregCuenta) or die ("ERROR: Fallo al obtener #Cuenta");
								$nombre_C = mysql_fetch_assoc($matrCuenta);
								$nombreC = $nombre_C['ban_nombre'];
								$encabezado = ' resultado(s) de Cuenta: '.$nombreC.' - ' .$cuentas;


								$datos = 'unacuenta';
								$link = '&datos='. $datos.'&cuenta='.$cuentas;

						
							}	elseif($banco != ''){
								
								$pregPagos .= " WHERE ". $dbpfx . "pedidos_pagos.pago_banco LIKE '%$banco%'";
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Pedidos Pagados");
								$numpagos2 = mysql_num_rows($matrPagos);
								$encabezado = ' resultado(s) del Banco: '.$banco.' ';

								$datos = 'unbanco';
								$link = '&datos='. $datos.'&banco='.$banco;

						
							}	elseif($monto == 'DESC'){
								$pregPagos .= " ORDER BY `" . $dbpfx . "pedidos_pagos`.`pago_monto` DESC ";
								//echo $pregPagos;
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Ragno");
								$numpagos = mysql_num_rows($matrPagos);						
								$numpagos2 = mysql_num_rows($matrPagos);
								$encabezado = ' resultado(s) por Monto de Mayor a Menor ';

								$datos = 'MontoDESC';
								$link = '&datos='. $datos;
							}
							elseif($monto == 'ASC'){
								$pregPagos .= " ORDER BY `" . $dbpfx . "pedidos_pagos`.`pago_monto` ASC";
								//echo $pregPagos;
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Ragno");
								$numpagos = mysql_num_rows($matrPagos);						
								$numpagos2 = mysql_num_rows($matrPagos);
								$encabezado = ' resultado(s) por Monto de Menor a Mayor';

								$datos = 'MontoACS';
								$link = '&datos='. $datos;
							}
								elseif($rangoF == ''){
								/*PAGINACION*/
								// maximo por pagina
								$limit = 30;
								// pagina pedida
								$pag = (int) $pag;
								if ($pag < 1){$pag = 1;}
								$offset = ($pag-1) * $limit;
								/*PAGINACION*/
								////SQL PAGINACION
								//echo $pregPagos.'<br>';
								if($pagoID != ''){
									$pregPagos .= " WHERE ". $dbpfx . "pedidos_pagos.pago_id = '$pagoID'";
									$numpagos2 = mysql_num_rows($matrPagos);
									$encabezado = ' resultado(s) de Pago: '.$pagoID.' ';
								}								
								else{
									$encabezado = " resultado(s) de Pagos grupales a pedidos de todos los proveedores";
								}
								$pregPagos .= " ORDER BY `" . $dbpfx . "pedidos_pagos`.`pago_id` DESC LIMIT $offset, $limit";
								$totalPagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos";
								$matrTotPagos = mysql_query($totalPagos) or die ("ERROR: fallo contar pagos");
								$numpagos2 = mysql_num_rows($matrTotPagos);
								//echo $pregPagos.'<br>';
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Pedidos Pagados");
								//$numpagos2 = mysql_num_rows($matrPagos). ' de --- ';
								$datos = 'todos';
								$link = '&datos='. $datos;

								$sqlTotal = "SELECT FOUND_ROWS() as total";
								$matrsqlT = mysql_query($sqlTotal) or die("ERROR: Fallo selección de sql total");
								$rowTotal = mysql_fetch_assoc($matrsqlT);
								// Total de registros sin limit
								$total = $rowTotal["total"];
								///SQL NUMERO DE PAGOS TOTALES
								$pregPagos2 = "SELECT * FROM " . $dbpfx . "pedidos_pagos";
								
								$matrPagos2 = mysql_query($pregPagos2) or die("ERROR: Fallo selección de Pedidos Pagados"); 
								//$numpagos2 = mysql_num_rows($matrPagos2);
							}
							elseif($rangoF == 1 || $rangoF == 'on'){
								$pregPagos .= " WHERE pago_fecha BETWEEN '$feini' AND '$fefin' ORDER BY `" . $dbpfx . "pedidos_pagos`.`pago_id` DESC";
								//echo $pregPagos;
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Ragno");
								$numpagos = mysql_num_rows($matrPagos);						
								$numpagos2 = mysql_num_rows($matrPagos);
								
								$datos = 'rangoF';
								$link = '&datos='. $datos.'&feini='.$feini.'&fefin='.$fefin;
							}

							if($export == ''){
						

	echo '			
					<a href="reportes.php?accion=pagos_grupales&export=1' .$link . '"><img src="idiomas/es_MX/imagenes/hoja-calculo.png"></a>
					</td>
				</tr>
                <tr><td colspan="3">
                    	<input type="hidden" name="accion" value="' . $accion . '" />
                    	<input type="hidden" name="nomrep" value="' . $nomrep . '" />
                   		<input type="hidden" name="ordenar" value="' . $ordenar . '" />
                    	<input type="submit" class="btn btn-success" value="Enviar" />
                    	
                    </td>
                    
				</tr>
			</table>';
				
				
			echo'	</form>'."\n";

            

            echo '		<div style="clear: both;"></div>

								
								
										
									

							<div class="row">
								<div class="col-md-12">
									<div class="content-box-header" style="min-height: 60px;">
										<div class="panel-title">';
										if($rangoF == ''){
											echo '
											<span><h3>Mostrando (' . $numpagos2 . ') '.$encabezado.' </b></h3></span>';
										}
										elseif ($rangoF == 1 || $rangoF == 'on') {
											echo '
											<span><h3>Mostrando (' . $numpagos2 . ') Pagos grupales a pedidos del ' . $feini . ' al ' . $fefin . '</b></h3></span>';
										}

										echo '
										</div>
									</div>
								</div>
							</div>';
							
							
							echo'
							<div class="col-md-12 t-responsiva">
									<table class="pagostabla" style="margin-top: 10px;">
										<thead style="background-color: white;">
											<tr style="background-color: white;">
												<th>Pago</th>
												<th>Proveedor</th>
												<th>Pedidos</th>
												<th>Facturas</th>
												<th>Monto</th>
												<th>Banco</th>
												<th>Cuenta</th>
												<th>Tipo de Pago</th>
												<th>Referencia</th>
												<th>Fecha</th>
												<th>Usuario</th>													
												<th></th>
											<tr>
										</thead>
										<tbody>';
										echo '

										<tr class="claro">
						<td colspan="12">
							<form action="reportes.php?accion=pagos_grupales" method="get" enctype="multipart/form-data">
						</td>
					</tr>
					<tr class="claro">
						<td>
							<!--<input class="form-control" placeholder="Pedido" type="text" name="pagoID" size="4" >-->
						</td>
						<td>
							<input class="form-control" placeholder="Proveedor" type="text" name="cliente" size="12" >
						</td>
						<td>
							<input class="form-control" placeholder="Pedido" type="text" name="pedido" size="4" >
						</td>
						<td>
							<input class="form-control" placeholder="Factura" type="text" name="factura" size="4" >
						</td>
						<td><center>';
							if($monto == 'DESC'){
								echo '<a href="reportes.php?accion=pagos_grupales&monto=DESC" class="btn btn-sm" style="background-color: #00c3ff; color: blue; padding-left: 5px;padding-right: 5px;"><img src="imagenes/down.png"></a>  ';
							}
							else{
								echo '<a href="reportes.php?accion=pagos_grupales&monto=DESC" class="btn btn-sm" style="background-color: white; color: blue; padding-left: 5px;padding-right: 5px;"><img src="imagenes/down.png"></a>  ';
							}
							if($monto == 'ASC'){
								echo '<a href="reportes.php?accion=pagos_grupales&monto=ASC" class="btn btn-sm" style="background-color: #00c3ff; color: blue; padding-left: 5px;padding-right: 5px;"><img src="imagenes/up.png"></a>  ';
							}else{
								echo '<a href="reportes.php?accion=pagos_grupales&monto=ASC" class="btn btn-sm" style="background-color: white; color: blue; padding-left: 5px;padding-right: 5px;"><img src="imagenes/up.png"></a>  ';
							} echo' </center>
						</td>
						<td>
							<input class="form-control" placeholder="Banco" type="text" name="banco" size="4" >
						</td>
						<td>
							<select name="cuentas" size="1">
								<option value="">Seleccionar</option>'."\n";
									$preg0 = "SELECT * FROM " . $dbpfx . "cont_cuentas WHERE ban_activo = '1'";
									$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuentas");
									while ($ban = mysql_fetch_array($matr0)) {
										echo '<option value="' . $ban['ban_cuenta'] . '">' . $ban['ban_nombre'] . ' - ' . $ban['ban_cuenta'] . '</option>'."\n";
									}
									echo '

							</select>
						</td>
						<td>
							<select name="tipo_pago" size="1">
								<option value="">Seleccionar</option>'."\n";
									for($i=1;$i<=$opcpago;$i++) {
										echo '<option value="' . $i . '">' . constant('TIPO_PAGO_'.$i) . '</option>'."\n";
									}
									echo '
							</select>
						</td>
						<td><input class="form-control" placeholder="Referencia" type="text" name="referencia" size="4" ></td>
						<td>';
						if ($accion === "pagos_grupales") {
							echo '<label class="container" style="padding-left: 0px;margin-left: 30px;margin-bottom: 24px;">
							<input type="checkbox" ';
									 if($rangoF == 'on'){ echo 'checked="checked"'; } 
								echo 'name="rangoF" id="rangoF" style="display: none;">
								<span class="slider round" style="box-shadow: 1px 3px 0px 0px #000;"></span>
								</label>Utilizar Rango de Fechas
								'; ?>
								<script type="text/javascript">
								    $(document).ready(function(){
								    	var data2;
								    	var data;
								        $(":checkbox#rangoF").change(function(){
								        	if ($(":checkbox#rangoF:checked").length == 0)
								        		data2 = 0;
								        	if ($(":checkbox#rangoF:checked").length)
								        		data2 = 'on';
								        });
								   	});
								</script><?php
						}
					echo'		
							<input type="hidden" name="feini" value="' . $feini . '">
							<input type="hidden" name="fefin" value="' . $fefin . '">
							<input type="hidden" name="accion" value="pagos_grupales"></td>
						<td><input class="btn btn-success" class="form-control" type="submit" value="Enviar" /></form></td>
						<td></td>

					</tr>'."\n";

											$number=0;
											while ($pagosm = mysql_fetch_assoc($matrPagos)) {
												
												$number++;

												$pregPagos3 = "SELECT * FROM " . $dbpfx . "pagos_facturas WHERE `pago_id` = $pagosm[pago_id] ORDER BY fecha DESC";
												$matrPagos3 = mysql_query($pregPagos3) or die("ERROR: Fallo selección de Pedidos Pagados");
												$pregPagos4 = "SELECT * FROM " . $dbpfx . "pagos_facturas WHERE `pago_id` = $pagosm[pago_id] ORDER BY fecha DESC";
												$matrPagos4 = mysql_query($pregPagos4) or die("ERROR: Fallo selección de Pedidos Pagados");
												
													
													echo '
													<tr>
														<td>' . $pagosm['pago_id'] . '</td>
														<td>' . $pagosm['prov_nic'] . '</td>
														';
															//echo '# ++: '.$number;
															$rowss = mysql_num_rows($matrPagos3);
															if ($rowss == 0){echo '<td><b>No hay Pedidos relacionados</b></td>';
															}else {
																echo '<td>';
																while ($estepago = mysql_fetch_array($matrPagos3)) {
																	//echo '<br>'.$pregPagos3.'<br>';
																	
																	if($pedido == $estepago['pedido_id']){
																		echo '<a class="btn btn-sm" style="color: white; background-color: #02678e;padding-top: 2px;padding-bottom: 2px;padding-left: 2px;padding-right: 2px; border-color: #022a39;text-decoration:none; " href="pedidos.php?accion=consultar&pedido=' . $estepago['pedido_id'] . '" target="_blank"><b>' . $estepago['pedido_id'] . '</b></a> ';
																	}else{
																		echo '<a class="btn btn-sm" style="color: black; background-color: #00c3ff;padding-top: 2px;padding-bottom: 2px;padding-left: 2px;padding-right: 2px; border-color: #015a75;text-decoration:none; " href="pedidos.php?accion=consultar&pedido=' . $estepago['pedido_id'] . '" target="_blank"><b>' . $estepago['pedido_id'] . '</b></a> ';
																	}
																	
																} echo '</td>';
															}
																$rowss2 = mysql_num_rows($matrPagos4);
															if ($rowss2 == 0){echo '<td><b>No hay Facturas relacionados</b></td>';
															}else {
																echo '<td>';			
																while ($estepago2 = mysql_fetch_array($matrPagos4)) {
																										
														
																
																	if($factura == $estepago2['fact_id']){
																		echo '<a class="btn btn-sm" style="color: white; background-color: #047310;padding-top: 2px;padding-bottom: 2px;padding-left: 2px;padding-right: 2px; border-color: #011c04;text-decoration:none; " href="pedidos.php?accion=consultar&pedido=' . $estepago2['pedido_id'] . '" target="_blank"><b>' . $estepago2['fact_id'] . '</b></a> ';
																	}else{
																		echo '<a class="btn btn-sm" style="color: black; background-color: #05e11d;padding-top: 2px;padding-bottom: 2px;padding-left: 2px;padding-right: 2px; border-color: #026c0e;text-decoration:none; " href="pedidos.php?accion=consultar&pedido=' . $estepago2['pedido_id'] . '" target="_blank"><b>' . $estepago2['fact_id'] . '</b></a> ';
																	}
																
															}echo '</td>';
														}
																echo '
														
														<td style="text-align: right;"><b>$' . number_format($pagosm['pago_monto'], 2) . '</b></td>
														<td>';
														if($pagosm['pago_banco'] != ''){echo $pagosm['pago_banco'];}else{echo 'Efectivo';}
															echo '
														</td>														
														<td>';
														if($pagosm['pago_cuenta'] == '' || $pagosm['pago_cuenta'] == 0){echo 'Efectivo';}else{echo $pagosm['pago_cuenta'];}
															echo '
														
														<td>' . constant("TIPO_PAGO_".$pagosm['pago_tipo']) . '</td>
														<td>';
														echo $pagosm['pago_referencia'];
															echo '
														</td>			
														<td>' . $pagosm['pago_fecha'] . '</td>';
														$sqlUsuario = "SELECT * FROM " . $dbpfx . "usuarios WHERE usuario = '$pagosm[usuario]'";
														$matrsqlU = mysql_query($sqlUsuario) or die("ERROR: Fallo selección de sql total");
														$datosU = mysql_fetch_assoc($matrsqlU);
															echo'
														<td>' . $datosU['nombre'] . ' ' . $datosU['apellidos'] . '</td>
														<td><a href="proveedores.php?accion=ver_detalle_pago&pagoid=' . $pagosm['pago_id'] . '" class="btn btn-info btn-sm" target="_blank"> Ver</a></td>
													</tr>';
												}
												echo '
											</tbody>
											<tfoot>
												<tr>
													<td colspan="12">
														<div style="text-align: center;">';
															$totalPag = ceil($total/$limit);
															$links = array();
															for( $i=1; $i<=$totalPag ; $i++) {
																if($i == $pag ) {
																	$links[] = '<a style="background-color: #2c5ba0; color: white; border-color: white;" class="btn btn-md" href="reportes.php?accion=pagos_grupales&prov_id=' . $prov_id . '&pag=' . $i . '">' . $i . '</a>';
																} else {
																	$links[] = '<a style="background-color: white; color: #2c5ba0; border-color: #2c5ba0;" class="btn btn-md" href="reportes.php?accion=pagos_grupales&prov_id=' . $prov_id . '&pag=' . $i . '">' . $i . '</a>';
																}
															}
															echo implode(" ", $links);
															echo '
														</div>
													</td>
												</tr>
											</tfoot>
										</table>
									
								</div>
								<div class="col-md-4">
								</div>';
								
								}

								if($export == 1){
									foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
									foreach($_GET as $k => $v){$$k=$v;}
										
										// -------------------   Creación de Archivo Excel   ---------------------------
											$celda = 'A1';
											if($datos == 'todos' || $datos == 'MontoACS' || $datos == 'MontoDESC'){
												$titulo = 'Pagos grupales a pedidos de TODOS los Proveedores';
											}
											elseif($datos == 'uncliente'){
												$titulo = 'Pagos grupales a pedidos de del proveedor: '.$nombreprov;
											}
											elseif($datos == 'unpedido'){
												$titulo = 'Pagos grupales al pedido: '.$pedido;
											}
											elseif($datos == 'unafactura'){
												$titulo = 'Pagos grupales a la factura: '.$factura;
											}
											elseif($datos == 'unbanco'){
												$titulo = 'Pagos grupales al banco: '.$banco;
											}
											elseif($datos == 'unacuenta'){
												$titulo = 'Pagos grupales a la cuenta: '.$cuenta;
											}
											elseif($datos == 'tipopago'){
												$titulo = 'Pagos grupales del tipo de pago: '.$tipopago;
											}
											elseif($datos == 'referencia'){
												$titulo = 'Pagos grupales de la referencia: '.$referencia;
											}
											elseif($datos == 'rangoF'){
												$titulo = 'Pagos grupales a pedidos de TODOS los Proveedores del: '.$feini.' al: '.$fefin;
											}

											

											require_once ('Classes/PHPExcel.php');
											$objReader = PHPExcel_IOFactory::createReader('Excel5');
											$objPHPExcel = $objReader->load("parciales/export.xls");
											$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
														->setTitle("PAGOS")
														->setKeywords("AUTOSHOP EASY"); 

											$objPHPExcel->setActiveSheetIndex(0)
														->setCellValue($celda, $titulo);
														//->setCellValue("A3", $fecha_export);
											// ------ ENCABEZADOS ---
											$objPHPExcel->setActiveSheetIndex(0)
														->setCellValue("A4", "Pago")
														->setCellValue("B4", "Proveedor")
														->setCellValue("C4", "Pedidos")
														->setCellValue("D4", "Facturas")
														->setCellValue("E4", "Banco")
														->setCellValue("F4", "Cuenta")
														->setCellValue("G4", "Monto")
														->setCellValue("H4", "Tipo de Pago")
														->setCellValue("I4", "Referencia")
														->setCellValue("J4", "Fecha")
														->setCellValue("K4", "Usuario");
											$z = 5;

										// --- Seleccionado pagos del proveedor ---
											if($datos == 'todos'){
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos ORDER BY pago_fecha DESC";
											}
											elseif($datos == 'MontoACS'){
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos ORDER BY pago_monto ASC";
											}
											elseif($datos == 'MontoDESC'){
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos ORDER BY pago_monto DESC";
											}
											elseif($datos == 'uncliente'){
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE prov_id = '$clienteID' ORDER BY pago_fecha DESC";
											}
											elseif($datos == 'unpedido'){
												$pregpagos1 = "SELECT * FROM " . $dbpfx . "pagos_facturas WHERE proveedor_id = '$provid' AND pedido_id = '$pedido' ORDER BY fecha DESC";
												$matrPagos1 = mysql_query($pregpagos1) or die ("Error al seleccionar Pago! 52 " . $pregpagos) ;
												$estepago1 = mysql_fetch_assoc($matrPagos1);
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE pago_id = '$estepago1[pago_id]' ORDER BY pago_fecha DESC";
											}
											elseif($datos == 'unafactura'){
												$pregpagos1 = "SELECT * FROM " . $dbpfx . "pagos_facturas WHERE proveedor_id = '$provid' AND fact_id = '$factura' ORDER BY fecha DESC";
												$matrPagos1 = mysql_query($pregpagos1) or die ("Error al seleccionar Pago! 52 " . $pregpagos) ;
												$estepago1 = mysql_fetch_assoc($matrPagos1);
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE pago_id = '$estepago1[pago_id]' ORDER BY pago_fecha DESC";
											}
											elseif($datos == 'unbanco'){
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE pago_banco LIKE '%$banco%' ORDER BY pago_fecha DESC";
											}
											elseif($datos == 'unacuenta'){
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE pago_cuenta = '$cuenta' ORDER BY pago_fecha DESC";
											}
											elseif($datos == 'tipopago'){
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE pago_tipo = '$tipopago' ORDER BY pago_fecha DESC";
											}
											elseif($datos == 'unareferencia'){
												
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE pago_referencia = '$referencia' ORDER BY pago_fecha DESC";
											}



											if ($datos == 'rangoF') {
												$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE pago_fecha BETWEEN '$feini' AND '$fefin' ORDER BY pago_fecha DESC";
											} else {
												
											}
										
											$matrPagos = mysql_query($pregpagos) or die ("Error al seleccionar Pago! 5233 " . $pregpagos) ;
											//echo $pregpagos . '<br>';

										// --- Seleccionando proveedor ---
											

											while ($estepago = mysql_fetch_array($matrPagos)) {
												if($datos == 'unpedido' || $datos == 'unafactura' ){
													$proveedores = "SELECT prov_razon_social, prov_rfc FROM " . $dbpfx . "proveedores WHERE prov_id = '$provid'";
												}
												else{
													$proveedores = "SELECT prov_razon_social, prov_rfc FROM " . $dbpfx . "proveedores WHERE prov_id = '$estepago[prov_id]'";
												}
											$matrProveedores = mysql_query($proveedores);
											$esteproveedor = mysql_fetch_assoc($matrProveedores);

												// --- Celdas a grabar ----
												$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
												$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z; $ke = 'K'.$z;

												$e_pago_id = $estepago['pago_id'];
												$e_pago_referencia = $estepago['pago_referencia'];
												$e_prov_razon_social = $esteproveedor['prov_razon_social'];
												$e_pago_tipo = constant("TIPO_PAGO_".$estepago['pago_tipo']);
												$e_pago_banco = $estepago['pago_banco'];
												$e_pago_monto = $estepago['pago_monto'];
												$e_pago_fecha = date('Y-m-d 00:00:01', strtotime($estepago['pago_fecha']));
												$e_pago_fecha = PHPExcel_Shared_Date::PHPToExcel( strtotime($e_pago_fecha) );
												$e_pago_usuario = $estepago['usuario'];
												$e_pago_cuenta = $estepago['pago_cuenta'];

												$pedidos = "SELECT pedido_id FROM " . $dbpfx . "pagos_facturas WHERE pago_id = $e_pago_id";
												$matrPedidos = mysql_query($pedidos) or die ("Error al selecionar pedidos");
												$cuantospedidos = mysql_num_rows($matrPedidos);
												while ($estepedido = mysql_fetch_array($matrPedidos)) {
													$pp[] = $estepedido['pedido_id'];
												}
												$lista_pedidos = implode(", ", $pp);

												$facturas = "SELECT fact_id FROM " . $dbpfx . "pagos_facturas WHERE pago_id = $e_pago_id";
												$matrFacturas = mysql_query($facturas) or die ("Error al selecionar facturas");
												$cuantosfacturas = mysql_num_rows($matrFacturas);
												while ($estafactura = mysql_fetch_array($matrFacturas)) {
													$ppf[] = $estafactura['fact_id'];
												}
												$lista_facturas = implode(", ", $ppf);

												$sqlUsuario = "SELECT * FROM " . $dbpfx . "usuarios WHERE usuario = '$e_pago_usuario'";
												$matrsqlU = mysql_query($sqlUsuario) or die("ERROR: Fallo selección de sql total");
												$datosU = mysql_fetch_assoc($matrsqlU);
												$nombre_usuario = $datosU['nombre'] . ' ' . $datosU['apellidos'];
												
												if($estepago['pago_banco'] != '' || $$estepago['pago_banco'] != 0){}else{$e_pago_banco='Efectivo';}
												//if($estepago['pago_cuenta'] != '' || $estepago['pago_cuenta'] != 0){}else{$e_pago_cuenta='Efectivo';}
												//if($estepago['pago_referencia'] != '' || $estepago['pago_referencia'] != 0){}else{$e_pago_referencia='Efectivo';}
													
												$objPHPExcel->setActiveSheetIndex(0)
													->setCellValue($a, $e_pago_id)
													->setCellValue($b, $e_prov_razon_social)
													->setCellValue($c, $lista_pedidos)
													->setCellValue($d, $lista_facturas)
													->setCellValue($e, $e_pago_banco)
													->setCellValue($f, $e_pago_cuenta)
													->setCellValue($g, $e_pago_monto)
													->setCellValue($h, $e_pago_tipo)
													->setCellValue($i, $e_pago_referencia)			
													->setCellValue($j, $e_pago_fecha)													
													->setCellValue($ke, $nombre_usuario);

												$objPHPExcel->getActiveSheet()
																->getStyle($j)
																->getNumberFormat()
																->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
												$z++;
												unset($lista_pedidos);
												unset($pp);
												unset($lista_facturas);
												unset($ppf);
											}

											header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
											header('Content-Disposition: attachment;filename="pagos_grupales.xls"');
											header('Cache-Control: max-age=0');

											$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
											$objWriter->save('php://output');
											exit;
								}

	

} else{
	echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
}
