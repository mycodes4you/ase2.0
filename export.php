<?php 

foreach($_POST as $k => $v) { $$k=$v; } //  echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v) { $$k=$v; } // echo $k.' -> '.$v.' | ';

include('parciales/funciones.php');
include('idiomas/' . $idioma . '/reportes.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

	// ----- Fecha INI - FIN -------
	$prega = '';
	if(isset($feini) && $feini != '0000-00-00') {
		$feini = date('Y-m-d 00:00:00', strtotime($feini));
		$prega = " orden_fecha_recepcion > '" . $feini . "'";
		$t_ini = strftime('%e de %B del %Y', strtotime($feini));
		if(!isset($fefin) || $fefin == '' || $fefin == '0000-00-00') {
			$fefin = date('Y-m-d 23:59:59', time());
		}
		$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
		$prega .= " AND orden_fecha_recepcion < '" . $fefin . "' ";
		$t_fin = strftime('%e de %B del %Y', strtotime($fefin));
	} else {
		$feini = date('Y-m-01 00:00:00');
		$fefin = date('Y-m-t 23:59:59');
		$prega = " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "' ";
		$t_ini = strftime('%e de %B del %Y', strtotime($feini));
		$t_fin = strftime('%e de %B del %Y', strtotime($fefin));
	}

	//  ----------------  obtener nombres de aseguradoras	------------------- 

	$consulta = "SELECT aseguradora_id, aseguradora_logo, aseguradora_nic, aseguradora_razon_social FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_nic";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
	while ($aseg = mysql_fetch_array($arreglo)) {
		$ase[$aseg['aseguradora_id']][0] = $aseg['aseguradora_logo'];
		$ase[$aseg['aseguradora_id']][1] = $aseg['aseguradora_nic'];
		$ase[$aseg['aseguradora_id']][2] = $aseg['aseguradora_razon_social'];
	}
	$ase[0][0] = 'particular/logo-particular.png';
	$ase[0][1] = 'Particular';
	$ase[0][2] = 'Particular';

//  ------------------------------------------------------------------- 

//  ----------------  nombres de asesores ------------------- 


	$pregusu = "SELECT nombre, apellidos, usuario, codigo, activo, rol09 FROM " . $dbpfx . "usuarios WHERE acceso = '0' ORDER BY nombre, apellidos";
	$matrusu = mysql_query($pregusu) or die("ERROR: Fallo selección de usuarios!");
	while ($ases = mysql_fetch_array($matrusu)) {
		$usuario[$ases['usuario']] = $ases['nombre'] . ' ' . $ases['apellidos'];
		$usu_rol09[$ases['usuario']] = $ases['rol09'];
		$usr_cod[$ases['usuario']] = $ases['codigo'];
		$usr_activo[$ases['usuario']] = $ases['activo'];
	}



	$fecha_export = "Fecha de exportación: " .  date('Y-m-d');


if($reporte == "ingresos"){
	
	$preg0 = "SELECT orden_id, orden_cliente_id, orden_estatus, orden_categoria, orden_servicio, orden_vehiculo_placas, orden_vehiculo_tipo, orden_vehiculo_color, orden_vehiculo_id, orden_alerta, orden_asesor_id, orden_fecha_recepcion, orden_fecha_de_entrega, orden_fecha_promesa_de_entrega, orden_fecha_ultimo_movimiento, orden_grua FROM " . $dbpfx . "ordenes WHERE ";
	
	$preg0 .= $prega ;

	if($servflt != '') {
		$preg0 .= " AND orden_servicio = '" . $servflt . "' ";
	}

//	echo $preg0 . "<br>"."\n";
	
	// -------------------   Creación de Archivo Excel   ----------------------------------	

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
		
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de lapso!");
	$filas = mysql_num_rows($matr0);
	
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	
	
	while($ord = mysql_fetch_array($matr0)) {
		
		// --- Celdas a grabar ----
		$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
        $f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
		$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z;
		
		$oculta = 'no';
		$preg2 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' ";
		
		if($asegflt != '') {
			$preg2 .= " AND sub_aseguradora = '" . $asegflt . "' ";
		}
		
		$preg2 .= " GROUP BY sub_reporte";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de subordenes!".$preg2);
		$fila2 = mysql_num_rows($matr2);
		
		if($asegflt != '' && $fila2 == 0 && $ord['orden_estatus'] != '17' && $ord['orden_estatus'] != '1' && $ord['orden_estatus'] != '90') {
			$oculta = 'si';
		}
			
		if($oculta == 'no') {
			if(isset($of) || isset($osf)) {
				$ocu = 0;
				mysql_data_seek($matr4, 0);
				while($fact = mysql_fetch_array($matr4)) {
					
					if($ord['orden_id'] == $fact['orden_id']) {
						
						if($fact['fact_cobrada'] == '1' && $of == '1') {
							
							$ordfac[$ord['orden_id']][] = array(
								'fact_id' => $fact['fact_id'],
								'fact_num' => $fact['fact_num']);
									$ocu = 1;
							
						} elseif($fact['fact_cobrada'] == '0' && $of == '0') {
							
							$ordfac[$ord['orden_id']][] = [
								'fact_id' => $fact['fact_id'],
								'fact_num' => $fact['fact_num'],
							];
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
				
				
			$vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);
				
			if($confolio == 1 && $id == 17) {

				$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue($a, $ord['oid']);
			} else {

				$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue($a, $ord['orden_id']);
			}
				
			if($confolio == 1 && $id == 17) {
				
				$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue($b, strtoupper($ord['orden_vehiculo_tipo']))
            				->setCellValue($c, strtoupper($ord['orden_vehiculo_placas']));
				
			} else {
				
				$vehi_text = strtoupper($vehiculo['marca']) . ' ' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']);
			
				$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue($b, $vehi_text)
							->setCellValue($c, strtoupper($vehiculo['placas']));
			}
			
			$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue($d, constant('CATEGORIA_DE_REPARACION_' . $ord['orden_categoria']))
							->setCellValue($e, constant('ORDEN_SERVICIO_' . $ord['orden_servicio']));
			
			$preg1 = "SELECT sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' GROUP BY sub_reporte";
				
			$matr1 = mysql_query($preg1) or die($preg1);
			$reporte = array(); $ima = array();
				
			while($aseico = mysql_fetch_assoc($matr1)) {
				$ima[$aseico['sub_aseguradora']] = [$ase[$aseico['sub_aseguradora']][0], $ase[$aseico['sub_aseguradora']][1]];
				$reporte[$aseico['sub_reporte']] = 1; 
			}
			
			foreach($ima as $k => $v) {
				
				$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue($f, $v[1]);
			}
			
			foreach($reporte as $k => $v) {
				
				if($k != '' && $k != "0") { 

					$objPHPExcel->setActiveSheetIndex(0)
            					->setCellValue($g, $k);
				}
				
			}
				
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
			
			if(isset($of) || isset($osf)) {
				foreach($ordfac as $k => $v) {
						
					foreach($v as $w) {
						//echo '<a href="entrega.php?accion=cobros&orden_id=' . $k . '">' . $w['fact_num'] . '</a> ';
					}
				}
			} else {
					
				$asesor_id[$ord['orden_asesor_id']]++;
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($j, $usuario[$ord['orden_asesor_id']]);
				
				
			}

			$fpe = strtotime($ord['orden_fecha_promesa_de_entrega']);
			
			if($fpe > strtotime('2012-01-01')) {
				
				$fpe =  date('Y-m-d', $fpe);
			} else {
				
				$fpe = "sin fecha";
			}
			
			$f_recep = date('Y-m-d', strtotime($ord['orden_fecha_recepcion']));
				
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($kkk, $f_recep)
						->setCellValue($l, $fpe)
						->setCellValue($m, $dias);
			
		}
		
		$z++;
	}
		
	$nom_rep = "Reporte-pedidos";
	
}




 // Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $nom_rep . '.xls"');
    header('Cache-Control: max-age=0');


    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;

// -------------------   Creación de Archivo Excel   ---------------------------------- 


