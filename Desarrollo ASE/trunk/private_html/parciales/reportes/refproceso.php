<?php
/*************************************************************************************
*   Script de "reporte de Refacciones en proceso"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/


if ($f1125105 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol08'] == '1') {

		$pregpag = "SELECT pedido_id, prov_id, orden_id, subtotal, impuesto, pedido_tipo FROM " . $dbpfx . "pedidos WHERE fecha_creado > '" . $feini . "' AND fecha_creado < '" . $fefin . "' AND pedido_estatus != 90 AND orden_id != '999999997'";
		//echo $preg1 . '<br>';
		$matrpag = mysql_query($pregpag) or die("ERROR: Fallo selección de pedidos! " . $pregpag);
		$filapag = mysql_num_rows($matrpag);

		if($export == 1){ // ---- Hoja de calculo ----
		
			$preg1 = $pregpag;	
		}
		else{ // ---- HTML ----
			
			$renglones = 20;
			$paginas = (round(($filapag / $renglones) + 0.49999999) - 1);
			if(!isset($pagina)) { $pagina = 0;}
			$inicial = $pagina * $renglones;

			$preg1 = "SELECT pedido_id, prov_id, orden_id, subtotal, impuesto, pedido_tipo FROM " . $dbpfx . "pedidos WHERE fecha_creado > '" . $feini . "' AND fecha_creado < '" . $fefin . "' AND pedido_estatus != 90 AND orden_id != '999999997' LIMIT " . $inicial . ", " . $renglones;
			//echo $preg1 . '<br>';
			
		}
		
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de pedidos! " . $preg1);
		$fila1 = mysql_num_rows($matr1);

		if($export == 1){ // ---- Hoja de calculo ----
		
			// -------------------   Creación de Archivo Excel   ---------------------------
			$celda = 'A1';
			$titulo = 'REFACCIONES EN PROCESO: ' . $nombre_agencia;
	
			require_once ('Classes/PHPExcel.php');
			$objReader = PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load("parciales/export.xls");
			$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
						->setTitle("REFACCIONES EN PROCESO")
						->setKeywords("AUTOSHOP EASY");

			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($celda, $titulo);

			// ------ ENCABEZADOS ---
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue("A4", $lang['Pedido'])
						->setCellValue("B4", $lang['Proveedor'])
						->setCellValue("C4", $lang['OT'])
						->setCellValue("D4", $lang['Vehículo'])
						->setCellValue("E4", $lang['Item'])
						->setCellValue("F4", $lang['Tipo Item'])
						->setCellValue("G4", $lang['Estatus'])
						->setCellValue("H4", $lang['Costo Item'])
						->setCellValue("I4", $lang['Monto Pagado'])
						->setCellValue("J4", $lang['Monto por Pagar'])
						->setCellValue("K4", $lang['Estatus de Cobro']);
			$z= 5;
			/*
			//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="refacciones-en-proceso.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			*/
		}
		else{ // ---- HTML ----
			
			// ------ Imprimimos el encabezado de la tabla
		echo '			
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-12">
			<div class="content-box-header">
				<div class="panel-title">
  					<h2>' . $encabezado . '</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 ">
			<div id="content-tabla">
				<form action="reportes.php?accion=refproceso" method="post" enctype="multipart/form-data" name="fltrefnom">
					<table cellspacing="0" class="table-new">'."\n";
		echo '				<tr style="text-align: right;">
								<td colspan="9" class="claro" style="text-align: left;"></td>
								<td colspan="2">
									<a href="reportes.php?accion=refproceso&pagina=0&feini=' . $feini . '&fefin=' . $fefin . '&fltrefnom1=' . $fltrefnom1 . '&tipo_producto=' . $tipo_producto . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '
									<a href="reportes.php?accion=refproceso&pagina=' . $url . '&feini=' . $feini . '&fefin=' . $fefin . '&fltrefnom1=' . $fltrefnom1 . '&tipo_producto=' . $tipo_producto . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '
									<a href="reportes.php?accion=refproceso&pagina=' . $url . '&feini=' . $feini . '&fefin=' . $fefin . '&fltrefnom1=' . $fltrefnom1 . '&tipo_producto=' . $tipo_producto . '">Siguiente</a>&nbsp;';
		}
		echo '
									<a href="reportes.php?accion=refproceso&pagina=' . $paginas . '&feini=' . $feini . '&fefin=' . $fefin . '&fltrefnom1=' . $fltrefnom1 . '&tipo_producto=' . $tipo_producto . '">Última</a>
								</td>
							</tr>
							<tr>
								<th>
									<big>' . $lang['Pedido'] . '</big>
								</th>
								<th>
									<big>' . $lang['Proveedor'] . '</big>
								</th>
								<th>
									<big>' . $lang['OT'] . '</big>
								</th>
								<th>
									<big>' . $lang['Vehículo'] . '</big>
								</th>
								<th>
									<big>' . $lang['Item'] . '</big><br>
									<input type="text" name="fltrefnom1" placeholder="' . $lang['Filtrar Partes'] . '" onchange="document.fltrefnom.submit()";>
								</th>
								<th>
									<big>' . $lang['Tipo Item'] . '</big>
								</th>
								<th>
									<big><a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['Estatus'] . '&base=reportes.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['Estatus'] . '</a></big>
								</th>
								<th>
									<big><a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['Costo Item'] . '&base=reportes.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['Costo Item'] . '</a></big>
								</th>
								<th>
									<big>' . $lang['Monto Pagado'] . '</big>
								</th>
								<th>
									<big>' . $lang['Monto por Pagar'] . '</big>
								</th>
								<th>
									<big><a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['Estatus de Cobro'] . '&base=reportes.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['Estatus de Cobro'] . '</a></big>
								</th>
							</tr>'."\n";
	
		}
	
	
		// ------ Definimos encabezado de la tabla
		$encabezado = $lang['Reporte de Refacciones en Proceso'] . ' del ' . date('Y-m-d', strtotime($feini)) . " al " . date('Y-m-d', strtotime($fefin));

		
		$fondo = 'claro';
		$totcosto = 0; $totpagado = 0; $totpp = 0;
		while ($ped = mysql_fetch_array($matr1)) {
			$monto = round(($ped['subtotal'] + $ped['impuesto']),2);
			$preg3 = "SELECT pago_monto FROM " . $dbpfx . "pedidos_pagos WHERE pedido_id = '" . $ped['pedido_id'] . "'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de pagos! " . $preg3);
			$pagado = 0;
			while ($pag = mysql_fetch_array($matr3)) {
				$pagado = $pagado + $pag['pago_monto'];
			}
			$pagado = round($pagado, 2);
			$factor = ($pagado / $monto);
			$veh = datosVehiculo($ped['orden_id'], $dbpfx);
			$preg2 = "SELECT op_id, op_cantidad, op_nombre, op_costo, op_precio, op_tangible, op_ok, op_recibidos, sub_orden_id FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $ped['pedido_id'] . "' AND op_tangible < 3";
			if($fltrefnom1 != '') {
				$preg2 .= " AND op_nombre LIKE '%" . $fltrefnom1 . "%'";
			}
			if($tipo_producto === '0' || $tipo_producto === '1' || $tipo_producto === '2') {
				$preg2 .= " AND op_tangible = '" . $tipo_producto . "'";
			}
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos! " . $preg2);
			$fila2 = mysql_num_rows($matr2);
//			$factor = (1 / $fila2);
			$preg6 = "SELECT fact_id FROM " . $dbpfx . "facturas_por_pagar WHERE doc_int_id = '" . $ped['pedido_id'] . "'";
			$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de facturas por pagar! " . $preg6);
			$fila6 = mysql_num_rows($matr6);

			while ($op = mysql_fetch_array($matr2)) {
				
				if($export == 1){ // ---- Hoja de calculo ----
					
					
					// --- Celdas a grabar ----
					$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
					$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
					$kkk = 'K'.$z;
				
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($a, $ped['pedido_id'])
								->setCellValue($b, $provs[$ped['prov_id']]);
					
					if($ped['orden_id'] > '9999999') {
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($c, $lang['Bodega'])
									->setCellValue($d, $lang['Pedido Directo']);
						
					} else {
						
						$datos_vehi = $veh['marca'] . ' ' . $veh['tipo'] . ' ' . $veh['color'] . ' ' . $veh['placas'];
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($c, $ped['orden_id'])
									->setCellValue($d, $datos_vehi);

					}

					$cant_nom = $op['op_cantidad'] . ' ' . $op['op_nombre'];
					
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($e, $cant_nom)
								->setCellValue($f, constant('TIPO_PRODUCTO_'. $op['op_tangible']));

					
				}
				else{ // ---- HTML ----
					
					echo '				
							<tr class="' . $fondo . '">
								<td>
									<a href="pedidos.php?accion=consultar&pedido=' . $ped['pedido_id'] . '">' . $ped['pedido_id'] . '</a>
								</td>
								<td style="text-align: left;">
									' . $provs[$ped['prov_id']] . '
								</td>'."\n";
					if($ped['orden_id'] > '9999999') {
						echo '					
								<td>
									' . $lang['Bodega'] . '
								</td>
								<td>
									' . $lang['Pedido Directo'] . '
								</td>'."\n";
					} else {
						echo '					
								<td>
									<a href="ordenes.php?accion=consultar&orden_id=' . $ped['orden_id'] . '">' . $ped['orden_id'] . '</a>
								</td>
								<td style="text-align: left;">
									' . $veh['marca'] . ' ' . $veh['tipo'] . ' ' . $veh['color'] . ' ' . $veh['placas'] . '
								</td>'."\n";
					}
					echo '					
								<td style="text-align: left;">
									' . $op['op_cantidad'] . ' ' . $op['op_nombre'] . '
								</td>
								<td>
									' . constant('TIPO_PRODUCTO_'. $op['op_tangible']) . '
								</td>
								<td style="text-align: left;">
									' . $tipo_estatus . ''."\n";
					
				}
				
				
				if($op['op_ok'] == 0) {
					
					if($export == 1){ // ---- Hoja de calculo ----
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($g, $tipo_estatus . ' ' . $lang['Pendiente']);
						
					}
					else{ // ---- HTML ----
						echo $lang['Pendiente'];
					}
					
				} else {
					
					$preg4 = "SELECT orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $ped['orden_id'] . "'";
					$matr4 = mysql_query($preg4) or die("ERROR: Falló selección de ordenes! " . $preg4);
					$ord1 = mysql_fetch_array($matr4);
					$ordest = $ord1['orden_estatus'];
					$preg5 = "SELECT e.ent_operador FROM " . $dbpfx . "entregas e, " . $dbpfx . "entregas_productos ep WHERE ep.op_id = '" . $op['op_id'] . "' AND e.ent_id = ep.ent_id";
					$matr5 = mysql_query($preg5) or die("ERROR: Falló selección de operadores! " . $preg5);
					$oper = mysql_fetch_array($matr5);
					if($oper['ent_operador'] > 0) {
						$entoper = $usuario[$oper['ent_operador']];
					} else {
						$entoper = $lang['Sin Operador'];
					}
					
					if($ordest == '16' || $ordest == '99') {
						
						if($export == 1){ // ---- Hoja de calculo ----
							
							$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($g, $tipo_estatus . ' ' . $lang['Entregado'] . ' ' . $lang['Instaló'] . ' ' . $entoper);
							
						}
						else{ // ---- HTML ----
						
							echo $lang['Entregado'] . ' ' . $lang['Instaló'] . ' ' . $entoper;
						}
						
					} elseif(($ordest >= '12' && $ordest <= '15') || $ordest == '21') {
						
						if($export == 1){ // ---- Hoja de calculo ----
							
							$objPHPExcel->setActiveSheetIndex(0)
										->setCellValue($g, $tipo_estatus . ' ' . $lang['Por Entregar'] . ' ' . $lang['por'] . ' ' . $entoper);
							
						}
						else{ // ---- HTML ----
						
							echo $lang['Por Entregar'] . ' ' . $lang['por'] . ' ' . $entoper;
						}
						
					} elseif(($ordest >= '30' && $ordest <= '40') || ($ordest >= '90' && $ordest <= '98')) {
						if($entoper == $lang['Sin Operador']) {
							
							if($export == 1){ // ---- Hoja de calculo ----
								
								$objPHPExcel->setActiveSheetIndex(0)
											->setCellValue($g, $tipo_estatus . ' ' . $lang['Por Devolver'] . ' ' . $lang['En Almacén']);
								
							}
							else{ // ---- HTML ----
							
								echo $lang['Por Devolver'] . ' ' . $lang['En Almacén'];
							}
							
						} else {
							
							if($export == 1){ // ---- Hoja de calculo ----
								
								$objPHPExcel->setActiveSheetIndex(0)
											->setCellValue($g, $tipo_estatus . ' ' . $lang['Por Devolver'] . ' ' . $lang['con'] . ' ' . $entoper);
								
							}
							else{ // ---- HTML ----
								echo $lang['Por Devolver'] . ' ' . $lang['con'] . ' ' . $entoper;
							}
							
						}
					} else {
						if($entoper == $lang['Sin Operador']) {
							
							if($export == 1){ // ---- Hoja de calculo ----
								
								$objPHPExcel->setActiveSheetIndex(0)
											->setCellValue($g, $tipo_estatus . ' ' . $lang['Recibido sin entregar']);
								
							}
							else{ // ---- HTML ----
								echo $lang['Recibido sin entregar'];
							}
							
						} else {
							
							if($export == 1){ // ---- Hoja de calculo ----
								
								$objPHPExcel->setActiveSheetIndex(0)
											->setCellValue($g, $tipo_estatus . ' ' . $lang['Entregado a'] . ' ' . $entoper);
								
							}
							else{ // ---- HTML ----
								echo $lang['Entregado a'] . ' ' . $entoper;
							}
								
						}
					}
				}
				
				$costo_item = 0; $costo_pagado = 0;
				
				if($export == 1){ // ---- Hoja de calculo ----
		
				}
				else{ // ---- HTML ----
					echo '
								</td>
								<td style="text-align: right;">'."\n";
				}
					
				if($ped['pedido_tipo'] == 1) {
					
					if($export == 1){ // ---- Hoja de calculo ----
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($h, $lang['A Cargo Aseguradora']);
						
					}
					else{ // ---- HTML ----
					
						echo $lang['A Cargo Aseguradora'];
					}
					
				} elseif($ped['pedido_tipo'] == 2 || $ped['pedido_tipo'] == 3 ) {
					
					$costo_item = round((($op['op_cantidad'] * $op['op_costo']) * (1 + $provs_iva[$ped['prov_id']])), 2);
					$costo_pagado = round(($costo_item * $factor),2);
					if($costo_item != 0) {
					
						if($export == 1){ // ---- Hoja de calculo ----
							
							$objPHPExcel->setActiveSheetIndex(0)
										->setCellValue($h, $costo_item);
							
						}
						else{ // ---- HTML ----
						
							echo number_format($costo_item, 2);
							
						}
						
					}
					$totcosto = $totcosto + $costo_item;
					$totpagado = $totpagado + $costo_pagado;
					$totpp = $totpp + ($costo_item - $costo_pagado);
				}

				if($export == 1){ // ---- Hoja de calculo ----
		
				}
				else{ // ---- HTML ----
					echo '
								</td>
								<td style="text-align: right;">'."\n";
				}
				
				if($costo_pagado != 0) {
					
					if($export == 1){ // ---- Hoja de calculo ----
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($i, $costo_pagado);
						
					}
					else{ // ---- HTML ----
						echo number_format($costo_pagado, 2);
					}
				}
				
					if($export == 1){ // ---- Hoja de calculo ----
		
					}
					else{ // ---- HTML ----
						echo '
								</td>
								<td style="text-align: right;">'."\n";
					}
				
				if($fila6 > 0) {
					
					if($export == 1){ // ---- Hoja de calculo ----
				
						$costo_fin = $costo_item - $costo_pagado;
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($j, $costo_fin);
						
					}
					else{ // ---- HTML ----
						echo number_format(($costo_item - $costo_fin), 2);
					}
					
				} elseif($ped['pedido_tipo'] == 1) {
					
					if($export == 1){ // ---- Hoja de calculo ----
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($j, $lang['A Cargo Aseguradora']);
						
					}
					else{ // ---- HTML ----
						echo $lang['A Cargo Aseguradora'];
					}
					
				} else {
					
					if($export == 1){ // ---- Hoja de calculo ----
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($j, $lang['Sin Factura']);
						
					}
					else{ // ---- HTML ----
						echo $lang['Sin Factura'];
					}
					
				}
				
				if($export == 1){ // ---- Hoja de calculo ----
		
				}
				else{ // ---- HTML ----
					echo '
								</td>
								<td>'."\n";
				}
				
				$preg7 = "SELECT f.fact_id, f.fact_cobrada FROM " . $dbpfx . "facturas_por_cobrar f, " . $dbpfx . "subordenes s WHERE f.fact_id = s.fact_id AND f.orden_id = '" . $ped['orden_id'] . "' ORDER BY f.fact_id DESC LIMIT 1";
				$matr7 = mysql_query($preg7) or die("ERROR: Fallo selección de facturas por pagar! " . $preg7);
				$fila7 = mysql_num_rows($matr7);
//				echo 'fila7: ' . $fila7;
				if($fila7 > 0) {
					$cob = mysql_fetch_array($matr7);
					if($cob['fact_cobrada'] == '1') {
						
						if($export == 1){ // ---- Hoja de calculo ----
							$objPHPExcel->setActiveSheetIndex(0)
										->setCellValue($kkk, $lang['OT Cobrada']);
						}
						else{ // ---- HTML ----
							echo $lang['OT Cobrada'];
						}
						
					} elseif($cob['fact_cobrada'] == '2') {
						
						if($export == 1){ // ---- Hoja de calculo ----
							$objPHPExcel->setActiveSheetIndex(0)
										->setCellValue($kkk, $lang['OT Cancelada']);
						}
						else{ // ---- HTML ----
							echo $lang['OT Cancelada'];
						}
						
					} elseif($cob['fact_cobrada'] === '0') {
						
						if($export == 1){ // ---- Hoja de calculo ----
							$objPHPExcel->setActiveSheetIndex(0)
										->setCellValue($kkk, $lang['OT Por Cobrar']);
						}
						else{ // ---- HTML ----
							echo $lang['OT Por Cobrar'];
						}
					}
				} else {
					
					if($export == 1){ // ---- Hoja de calculo ----
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($kkk, $lang['OT Sin Facturar']);
					}
					else{ // ---- HTML ----
						echo $lang['OT Sin Facturar'];
					}
					
				}
				
				if($export == 1){ // ---- Hoja de calculo ----
					
					$z++;
					
				}
				else{ // ---- HTML ----
					echo '
								</td>
							</tr>'."\n";
					if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
				}
				
			}
			
		}
	
		if($export == 1){ // ---- Hoja de calculo ----
		
			//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="refacciones-en-proceso.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			
		}
		else{ // ---- HTML ----
			echo '				
							<tr class="' . $fondo . '">
								<td colspan="7"></td>
								<td><b><big>' . $lang['Total Refacciones'] . '</big></b></td>
								<td><b><big>' . $lang['Total Pagado'] . '</big></b></td>
								<td><b><big>' . $lang['Total Por Pagar'] . '</big></b></td>
								<td></td>
							</tr>
							<tr class="' . $fondo . '">
								<td colspan="7"></td>
								<td style="text-align: right;"><b><big>' . number_format($totcosto, 2) . '</big></b></td>
								<td style="text-align: right;"><b><big>' . number_format($totpagado, 2) . '</big></b></td>
								<td style="text-align: right;"><b><big>' . number_format($totpp, 2) . '</big></b></td>
								<td><input type="hidden" name="feini" value="' . $feini . '" /><input type="hidden" name="fefin" value="' . $fefin . '" /><input type="hidden" name="tipo_producto" value="' . $tipo_producto . '" /><input type="hidden" name="tipo_estatus" value="' . $tipo_estatus . '" /></td>
							</tr>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>'."\n";
		}
	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}

?>