<?php
/*************************************************************************************
*   Script de "reporte de Ingreso de Vehiculos"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/


    if ($f1125005 == '1' || $f1125010 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {

        
        if($export == 1){ // ---- Hoja de calculo ----
            
            // -------------------   Creación de Archivo Excel   ---------------------------
            $celda = 'A1';
            $titulo = 'INGRESOS: ' . $nombre_agencia;
	
            require_once ('Classes/PHPExcel.php');
            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load("parciales/export.xls");
            $objPHPExcel->getProperties()->setCreator("AutoShop Easy")
                        ->setTitle("Listado de Clientes")
                        ->setKeywords("AUTOSHOP EASY");

            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($celda, $titulo)
                        ->setCellValue("A3", $fecha_export);

            // ------ ENCABEZADOS ---
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue("A4", "OT")
                        ->setCellValue("B4", "Vehículo")
                        ->setCellValue("C4", "Placas")
                        ->setCellValue("D4", "Categoría de Servicio")
                        ->setCellValue("E4", "Tipo de Servicio")
                        ->setCellValue("F4", "Cliente")
                        ->setCellValue("G4", "Siniestro")
                        ->setCellValue("H4", "Grúa")
                        ->setCellValue("I4", "Estatus")
                        ->setCellValue("J4", "Asesor")
                        ->setCellValue("K4", "Fecha Recibido")
                        ->setCellValue("L4", "Fecha Promesa de Entrega")
                        ->setCellValue("M4", "Días en proceso");
	
            $z= 5;
            
        }
        else{ // ---- HTML ----
            
            echo '			
                <table cellspacing="1" cellpadding="2" border="0">
                    <tr class="cabeza_tabla">
                        <td colspan="13" style="text-align: right;">' . $filas . $encabezado . '</td>
                    </tr>' . "\n";
            
			echo '				
                    <tr>
                        <td>
                            <a href="reportes.php?accion=reportes&ordenar=ot&feini='.$feini.'&fefin='.$fefin.'">OT</a>
                        </td>
                        <td>
                            Vehículo
                        </td>
                        <td>
                            ' . $lang['Placa'] . '
                        </td>
                        <td>
                            Categoría de Servicio
                        </td>
                        <td>
                            Tipo de Servicio
                        </td>
                        <td>
                            Cliente
                        </td>
                        <td>
                            Siniestro
                        </td>
                        <td>
                            Grúa
                        </td>
                        <td>
                            <a href="reportes.php?accion=reportes&ordenar=estatus&feini='.$feini.'&fefin='.$fefin.'">Estatus</a>
                        </td>
                        <td>
                            <a href="reportes.php?accion=reportes&ordenar=asesor&feini='.$feini.'&fefin='.$fefin.'">Asesor</a>
                        </td>
                        <td>
                            Fecha Recibido
                        </td>
                        <td>
                            ' . $lang['FechaPromesa'] . '
                        </td>
                        <td>
                            Días en proceso
                        </td>
                    </tr>
                ' . "\n";
        }
         
        
        $fondo = 'claro';
        $j = 0;
        $hoy = strtotime(date('Y-m-d 23:59:59'));
        $grua_si = 0;
		$grua_no = 0;
        $grua_no_def  = 0;
		
        while($ord = mysql_fetch_array($matr0)) {
			
            
            $oculta = 'no';
            $preg2 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" .    $ord['orden_id'] . "' AND sub_estatus < '190' ";
            if($asegflt != '') {
                $preg2 .= " AND sub_aseguradora = '" . $asegflt . "' ";
            }
            $preg2 .= " GROUP BY sub_reporte";
            $matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de subordenes!".$preg2);
            $fila2 = mysql_num_rows($matr2);
            if($asegflt != '' && $fila2 == 0 && $ord['orden_estatus'] != '17' && $ord['orden_estatus'] != '1' && $ord['orden_estatus'] != '90') {
                $oculta = 'si';
            }
			
			if($resumen_ing_egre == 1){
				// ******** RESUMENES DEL REPORTE ********

				// --- almacenar ingresos de aseguradora y particular ---
				while($fila2 = mysql_fetch_array($matr2)){
				
					$cont_aseg[$fila2['sub_aseguradora']]++;
					 
				}
			
				// --- Vehiculos en grua ---
				if($ord['orden_grua'] == 1){
					$grua_si++;
				}
				elseif($ord['orden_grua'] == 2){
					$grua_no++;
				}
				else{
					$grua_no_def++;
				}
			
				// ---- Almacenar el tipo de servicio ----
				$tipo_serv[$ord['orden_servicio']]++;
			
				// --- Agrupar por asesor ---
				$asesor_cont[$ord['orden_asesor_id']]++;
			}
			
            if($oculta == 'no') {
                if(isset($of) || isset($osf)) {
                    $ocu = 0;
                    mysql_data_seek($matr4, 0);
                    while($datosVehiculofact = mysql_fetch_array($matr4)) {
                        if($ord['orden_id'] == $fact['orden_id']) {
                            if($fact['fact_cobrada'] == '1' && $of == '1') {
                                $ordfac[$ord['orden_id']][] = array(
                                    'fact_id' => $fact['fact_id'],
                                    'fact_num' => $fact['fact_num']);
                                    $ocu = 1;
                            } elseif($fact['fact_cobrada'] == '0' && $of == '0') {
                                $ordfac[$ord['orden_id']][] = array(
                                    'fact_id' => $fact['fact_id'],
                                    'fact_num' => $fact['fact_num']);
                                $ocu = 1;
                            }
                        } elseif($osf == '1') {
                            $ocu = 1;
                        }
                    }
                    if($ocu == '0') {
                        continue ;
                    }
                }

                $fde = strtotime($ord['orden_fecha_de_entrega']);
                if($fde > strtotime('2012-01-01')) {
                    $dias = intval(($fde - strtotime($ord['orden_fecha_recepcion'])) / 86400) + 1;
                } elseif($ord['orden_estatus'] == '90') {
                    $dias = intval((strtotime($ord['orden_fecha_ultimo_movimiento']) - strtotime($ord['orden_fecha_recepcion'])) / 86400);
                } else {
                    $dias = intval(($hoy - strtotime($ord['orden_fecha_recepcion'])) / 86400) + 1;
                }
                if(!isset($estanciamax) || $estanciamax == '') { $estanciamax = '20'; }
                if($dias > $estanciamax) {
                    
                    if($export == 1){ // ---- Hoja de calculo ----
                        
                    }
                    else{ // ---- HTML ----
                        $dias = '<span style="font-weight:bold; color:red; background-color:yellow;">'.$dias.'</span>';     
                    }
                    
                }
                $vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);

                
                if($export == 1){ // ---- Hoja de calculo ----    
	
                    // --- Celdas a grabar ----
                    $a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
                    $f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
                    $kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z;
                    
                    if($confolio == 1 && $id == 17) {

                        $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue($a, $ord['oid']);   
                        
                    } else {
                        
                        $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue($a, $ord['orden_id']);
                    }
                    
                }
                else{ // ---- HTML ----
                   
                    echo '				
                    <tr class="' . $fondo . '">
                        <td style="padding-left:10px; padding-right:10px; text-align:center;"><a href="ordenes.php?accion=consultar&';
                    if($confolio == 1 && $id == 17) {
                        echo 'oid=' . $ord['oid'];
                    } else {
                        echo 'orden_id=' . $ord['orden_id'];
                    }
                    echo '">';
                    if($confolio == 1 && $id == 17) {
                        echo $ord['oid'];
                    } else {
                        echo $ord['orden_id'];
                    }
                    echo '</a>
                        </td>
                        <td style="padding-left:10px; padding-right:10px;">';
                    
                }
                 
                
                if($confolio == 1 && $id == 17) {
                    
                    if($export == 1){ // ---- Hoja de calculo ----
                        
                        $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue($b, strtoupper($ord['orden_vehiculo_tipo']))
                                    ->setCellValue($c, strtoupper($ord['orden_vehiculo_placas']));
                    }
                    else{ // ---- HTML ----
                        
                        echo strtoupper($ord['orden_vehiculo_tipo']) . '
                        </td>
                        <td style="padding-left:10px; padding-right:10px;">
                            ' . $ord['orden_vehiculo_placas'] . '
                        </td>';
                        
                    }
                    
                    
                } else {
                    
                    if($export == 1){ // ---- Hoja de calculo ----
                        
                        $vehi_text = strtoupper($vehiculo['marca']) . ' ' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']);
			
                        $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue($b, $vehi_text)
                                    ->setCellValue($c, strtoupper($vehiculo['placas']));
                        
                    }
                    else{ // ---- HTML ----
                        
                        echo strtoupper($vehiculo['marca']) . ' ' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']) . '
                        </td>
                        <td style="padding-left:10px; padding-right:10px;">
                            ' . $vehiculo['placas'] . '
                        </td>';
                        
                    }
    
                }
                
                if($export == 1){ // ---- Hoja de calculo ----
                    
                    $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue($d, constant('CATEGORIA_DE_REPARACION_' . $ord['orden_categoria']))
                                ->setCellValue($e, constant('ORDEN_SERVICIO_' . $ord['orden_servicio']));
                    
                }
                else{ // ---- HTML ----
                    
                    echo '
                        <td>' . constant('CATEGORIA_DE_REPARACION_' . $ord['orden_categoria']) . '
                        </td>
                        <td>
                            ' . constant('ORDEN_SERVICIO_' . $ord['orden_servicio']) . '
                        </td>';
                    
                }
                
                
                $preg1 = "SELECT sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' GROUP BY sub_reporte";
                $matr1 = mysql_query($preg1) or die($preg1);
                $reporte = array(); $ima = array();
                
                
                while($aseico = mysql_fetch_assoc($matr1)) {
                    $ima[$aseico['sub_aseguradora']] = [$ase[$aseico['sub_aseguradora']][0], $ase[$aseico['sub_aseguradora']][1]];
                    $reporte[$aseico['sub_reporte']] = 1;
                }
                 
                
                if($export == 1){ // ---- Hoja de calculo ----
                    
                    foreach($ima as $k => $v) {
				
                        $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue($f, $v[1]);
                    }
                    
                }
                else{ // ---- HTML ----
                    
                    echo '
                        <td>';
                    foreach($ima as $k => $v) {
                        echo $v[1] . ' <img src="' . $v[0] . '" alt="" height="16" > ';
                    }
                    echo '
                        </td>';
                    
                }
                
                if($export == 1){ // ---- Hoja de calculo ----
                    
                    
                     foreach($reporte as $k => $v) {
                        if($k != '' && $k != "0") { 
                            $objPHPExcel->setActiveSheetIndex(0)
                                        ->setCellValue($g, $k);
                        }
                    }
                    
                }
                else{ // ---- HTML ----
                    
                    echo '
                        <td>';
                    foreach($reporte as $k => $v) {
                        if($k != '' && $k != "0") { echo $k . ' '; }
                    }
                    echo '
                        </td>';
                }
                
                
                if($export == 1){ // ---- Hoja de calculo ----
                     
                    $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue($h, $lang_grua[$ord['orden_grua']]);
                    
                    $status_ord =  constant('ORDEN_ESTATUS_' . $ord['orden_estatus']);
			
                    if(($t==1 && $est_trans == 1) || $ord['orden_ubicacion'] == 'Transito') { 

                        $status_ord .= ' en Tránsito.';
			
                    }
				
                    if($ord['orden_ref_pendientes'] == '2') {
			
                        $status_ord .= ' - ' . REFACCIONES_ESTRUCTURALES;
			
                    } elseif($ord['orden_ref_pendientes'] == '1') {
				
                        $status_ord .= ' - ' . REFACCIONES_PENDIENTES;
				
                    } else {
				
                        $status_ord .= ' Refacciones Completas';
				
                    }
				
                    $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue($i, $status_ord);
                    
                    
                }
                else{ // ---- HTML ----
                    
                    echo '
                        <td style="text-align:center;">
                            ' . $lang_grua[$ord['orden_grua']] .'
                        </td>';
                    echo '
                        <td style="padding-left:10px; padding-right:10px;">
                        ' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']);
                    
                    if(($t==1 && $est_trans == 1) || $ord['orden_ubicacion'] == 'Transito') {
                        //echo constant('ORDEN_ESTATUS_T_' . $ord['orden_estatus']) ;
                        echo ' en Tránsito.';
                    }
                    if($ord['orden_ref_pendientes'] == '2') {
                        echo '<br>' . REFACCIONES_ESTRUCTURALES;
                    } elseif($ord['orden_ref_pendientes'] == '1') {
                        echo '<br>' . REFACCIONES_PENDIENTES;
                    } else {
                        //echo '<br>Refacciones Completas';
                    }
                    echo '
                        </td>
                        <td>';
                    
                }
                
                if(isset($of) || isset($osf)) {
                    foreach($ordfac as $k => $v) {
                        foreach($v as $w) {
                            
                            if($export == 1){ // ---- Hoja de calculo ----
                            
                            }
                            else{ // ---- HTML ----
                                echo '<a href="entrega.php?accion=cobros&orden_id=' . $k . '">' . $w['fact_num'] . '</a> ';    
                            }
                            
                        }
                    }
                } else {
                    
                    
                    $asesor_id[$ord['orden_asesor_id']]++;
                    
                    if($export == 1){ // ---- Hoja de calculo ----
                        $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue($j, $usuario[$ord['orden_asesor_id']]);        
                    }
                    else{ // ---- HTML ----
                        echo $usuario[$ord['orden_asesor_id']];    
                    }
                        
                    
                    if($ordenar == 'asesor'){ 
                        
                        if($export == 1){ // ---- Hoja de calculo ----
                        
                        }
                        else{ // ---- HTML ----
                            echo ' +' . $asesor_id[$ord['orden_asesor_id']];    
                        }
                        
                    }
                    
                }

                
                
                $fpe = strtotime($ord['orden_fecha_promesa_de_entrega']);
                if($fpe > strtotime('2012-01-01')) {
                    $fpe =  date('Y-m-d', $fpe);
                } else {
                    
                    if($export == 1){ // ---- Hoja de calculo ----
                        $fpe = 'Sin Fecha';
                    }
                    else{ // ---- HTML ----
                        $fpe = $lang['Sin Fecha'];    
                    }
                    
                }
                
                if($export == 1){ // ---- Hoja de calculo ----
                        
                    $f_recep = date('Y-m-d', strtotime($ord['orden_fecha_recepcion']));
				
                    $objPHPExcel->setActiveSheetIndex(0)
				                ->setCellValue($kkk, $f_recep)
                                ->setCellValue($l, $fpe)
                                ->setCellValue($m, $dias);
                    
                }
                else{ // ---- HTML ----
                    
                    echo '
                        </td>
                        <td style="padding-left:10px; padding-right:10px;">
                            ' . date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])) . '
                        </td>
                        <td style="padding-left:10px; padding-right:10px;">
                            ' . $fpe . '
                        </td>
                        <td align="center">
                            '. $dias .'
                        </td>
                    </tr>';
                    if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
                    
                }
             
                  
            }
            if($export == 1){ // ---- Hoja de calculo ----
                $z++;    
            }
            
        }
        
        if($export == 1){ // ---- Hoja de calculo ----
            
            //  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="ingreso-vehiculos.xls"');
            header('Cache-Control: max-age=0');


            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
            
        }
        else{ // ---- HTML ----
		
		if($resumen_ing_egre == 1){
			echo '		
					<tr>
						<td colspan="2" style="vertical-align: top;">
							<h2>Total Ordenes: <big><b>' . $filas . '</big></h2>
						</td>
						<td colspan="3" style="text-align:right; vertical-align: top; font-size:1.2em;">
							<table>
								<tr>
									<td colspan="3"><b><small>Siniestros y clientes registrados:</small></b></td>
								</tr>
								'."\n";
			
			// --- imprimir valores finales de aseguradoras y particular ---
			foreach($ase as $key => $val){
				
				if($cont_aseg[$key] == 0){
					$valor = 0;
					$style = 'style="color:white; background-color: #ff3333;"';
				}else{
					$valor = $cont_aseg[$key];
					$style = 'style="background-color: #33ff33"';
				}
			
				echo '
								<tr>
									<td><b><small>' . $ase[$key][1] . ':</small></b></td>
									<td></td>
									<td ' . $style . '><b>' . $valor . ' </b></small></td>
								</tr>'."\n";
			}			
									
			echo '
							</table>
						</td>
						<td colspan="2" style="text-align:right; vertical-align: top; font-size:1.2em;">
							<table>
								<tr>
									<td colspan="1"><small><b>Grúa:</b></small></td>
								</tr>
								<tr>
									<td><small>Ingresos en Grúa</small></td>
									<td></td>
									<td style="text-align:right;"><b>' . $grua_si . ' </b></td>
								</tr>
								<tr>
									<td><small>Ingresos sin Grúa</small></td>
									<td></td>
									<td style="text-align:right;"><b>' . $grua_no . ' </b></td>
								</tr>
								<tr>
									<td><small>Ingresos sin definir</small></td>
									<td></td>
									<td style="text-align:right;"><b>' . $grua_no_def . ' </b></td>
								</tr>
							</table>
						</td>
						<td colspan="2" style="text-align:right; vertical-align: top; font-size:1.2em;">
							<table>
								<tr>
									<td colspan="3"><small><b>Tipos de Ingreso:</b></small></td>
								</tr>'."\n";
			
			// --- recorrer los tipos de servicio de comun.php ---
			$count = 0;
			while($count <= $num_tipos){
				
				echo '
								<tr>'."\n";
				
				if($tipo_serv[$count] == ''){
					$tipo_serv[$count] = 0;
					$style = 'style="color:white; background-color: #ff3333;"';
				}else{
					$style = 'style="background-color: #33ff33"';
				}
				
				if($count == 0){
					
					echo '		
									<td>Canceladas</td>
									<td></td>
									<td ' . $style . '><b>' . $tipo_serv[$count] . '<b></td>';
					
				} else{
					echo '
									<td>' . constant('ORDEN_SERVICIO_'. $count) . '</td>
									<td></td>
									<td ' . $style . '><b>' . $tipo_serv[$count] . "<b></td>";
				
				}
				$count++;
				
				echo '
								</tr>'."\n";
				
			}
		
			echo '			
							</table>
						</td>
						<td colspan="4" style="text-align:right; vertical-align: top; font-size:1.2em;">
							<table>
								<tr>
									<td colspan="3"><small><b>Ingresos por Asesor:</b></small></td>
								</tr>'."\n";
			
			// --- imprimir total por asesor ---
			foreach($asesor_cont as $key => $val){
				
				echo '
								<tr>
									<td><small>' . $usuario[$key] . '</small></td>
									<td></td>
									<td><b>' . $val . ' </b></small></td>
								</tr>'."\n";
			}	
			
			
			
			echo '		
							</table>
						<td>
					</tr>'."\n";
		}
			
			
            echo '			</table>';    
			
        }
        

	} else {
        //echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta. Reportes o Tablas.</p>'; 
	}




?>