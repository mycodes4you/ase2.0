<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

//  ----------------  nombres de aseguradoras   
		$consulta = "SELECT aseguradora_id, aseguradora_logo, aseguradora_nic FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		while ($aseg = mysql_fetch_array($arreglo)) {
			$ase[$aseg['aseguradora_id']]['logo'] = $aseg['aseguradora_logo'];
			$ase[$aseg['aseguradora_id']]['nic'] = $aseg['aseguradora_nic'];
			$ase[$aseg['aseguradora_id']]['auto'] = $aseg['autosurtido'];
		}

//  ----------------  obtener nombres de usuarios   ------------------- 

	$consulta = "SELECT nombre, apellidos, usuario FROM " . $dbpfx . "usuarios WHERE activo = '1'";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo selección de usuarios!");
	while ($ases = mysql_fetch_array($arreglo)) {
		$usuario[$ases['usuario']] = $ases['nombre'] . ' ' . $ases['apellidos'];
	}

//  ----------------  nombres de asesores   ------------------- 

/*
Por hacer: agregar un campo en la base de datos para colocar el nombre del archivo de plantilla que cada aseguradora utiliza para generar la hoja de cálculo con su correspondiente Logo.
*/


if ($accion==='genera') {

	// creación de excel para aseguradora Mapfre.
	$preg1 = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id' LIMIT 1";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección SubOrden!");
	$sub = mysql_fetch_array($matr1);
	$preg2 = "SELECT v.*, o.orden_asesor_id, o.orden_fecha_recepcion FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "ordenes o WHERE o.orden_id = '" . $sub['orden_id'] . "' AND v.vehiculo_id = o.orden_vehiculo_id";
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección Vehículo y OT! " . $preg2);
	$veh = mysql_fetch_array($matr2);
	$nom_excel = $sub['orden_id'] . '-cotizaex-' . $veh['vehiculo_placas'];
	if($sub['sub_aseguradora'] == '0') {
		$nom_excel .= '-particular'; 
	} else {
		$nom_excel .= '-aseguradora';
	}
	$nom_excel .=  '.xlsx';
	if (file_exists(DIR_DOCS . $nom_excel)) { unlink (DIR_DOCS . $nom_excel); }
	$hoy = date('Y-m-d', time());
	$recepcion = date('Y-m-d', strtotime($veh['orden_fecha_recepcion']));	
	if($veh['vehiculo_transmision'] == '1') { $vehiculo_transmision = 'Estándar'; }
	if($veh['vehiculo_transmision'] == '2') { $vehiculo_transmision = 'Automática'; } 
	if($veh['vehiculo_elevadores'] == '1') { $vehiculo_elevadores = 'Manual'; }
	if($veh['vehiculo_elevadores'] == '2') { $vehiculo_elevadores = 'Eléctricos'; }
	if($veh['vehiculo_aa'] == '1') { $vehiculo_aa = 'Sí'; }
	if($veh['vehiculo_aa'] == '2') { $vehiculo_aa = 'No'; }

	// -------------------   Creación de Archivo Excel   ----------------------------------		
	require_once ('Classes/PHPExcel.php');
	$objReader = PHPExcel_IOFactory::createReader('Excel5');
	if($ase[$sub['sub_aseguradora']]['nic'] == 'MAPFRE') {
		$objPHPExcel = $objReader->load(DIR_DOCS . "plantilla-cotizacion-mapfre.xls");
	} else {
		$objPHPExcel = $objReader->load(DIR_DOCS . "plantilla-cotizacion-taller.xls");
	}
	$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
		->setTitle("Cotización de Refacciones")
		->setKeywords("AUTOSHOP EASY");

	if($ase[$sub['sub_aseguradora']]['nic'] == 'MAPFRE') {
		$objPHPExcel->setActiveSheetIndex(0)
            		->setCellValue('C5', 'A CARGO DE LA ASEGURADORA');
	} else {
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('C5', 'A CARGO DEL TALLER');
	}

	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('C7', ' ' . $sub['sub_poliza'])
				->setCellValue('C8', ' ' . $sub['sub_reporte'])
				->setCellValue('C6', $nombre_agencia)
				->setCellValue('C10', $veh['vehiculo_serie'])
				->setCellValue('C11', $veh['vehiculo_placas'])
				->setCellValue('F5', $veh['vehiculo_marca'])
				->setCellValue('F6', $veh['vehiculo_tipo'])
				->setCellValue('F7', $veh['vehiculo_subtipo'])
				->setCellValue('F8', $veh['vehiculo_modelo'])
				->setCellValue('F9', $veh['vehiculo_color'])
				->setCellValue('F10', $usuario[$veh['orden_asesor_id']])
				->setCellValue('F11', $veh['vehiculo_cilindros'])
				->setCellValue('M5', $vehiculo_transmision)
				->setCellValue('M6', $vehiculo_elevadores)
				->setCellValue('M7', $veh['vehiculo_puertas'])
				->setCellValue('M8', $vehiculo_aa)
				->setCellValue('M9', $usuario[$_SESSION['usuario']])
				->setCellValue('M10', $recepcion)
				->setCellValue('M11', $hoy);


	$preg3 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $sub['orden_id'] . "' AND ";
	if($sub['sub_reporte'] != '0') {
		$preg3 .= "sub_reporte != '0' ";
	} else {
		$preg3 .= "sub_reporte = '0' ";
	}

	$preg3 .= " AND sub_estatus < '190'";
	$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección Suborden!");

	if($cotextcmo == 1) {
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('C16', 'REFACCIONES Y MANO DE OBRA');
	}
	$j = 17;
	while($rsub = mysql_fetch_array($matr3)) {
		$preg4 = "SELECT op_id, op_cantidad, op_nombre, op_precio FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $rsub['sub_orden_id'] . "' AND op_pres = '1'";

		// ------ Si la variable $cotextcmo esta en 1 se vacía a la hoja de cálculo todo lo presupuestado
		if($cotextcmo != 1) {
			$preg4 .= " AND op_tangible = '1' ";
		}
		$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección Suborden!");
		while($op = mysql_fetch_array($matr4)) {
			// --- Consultar las cotizaciones de la refación ---
			$b = 'B'.$j; $c = 'C'.$j; 
			$de = 'D'.$j; $e = 'E'.$j; $f = 'F'.$j; $g = 'G'.$j; $d = 'H'.$j; $ci = 'I'.$j;
			
			$array_cols = [$de, $f, $d];
			$colorig = [$e, $g, $ci];
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($b, $op['op_cantidad'])
						->setCellValue($c, $op['op_nombre']);
			//echo $op['op_cantidad'] . '-' . $op['op_nombre'] . '-' . $op['op_id'] . '<br>'; 

			if($MargMapf > 0) {
				// --- Se incluye el cálculo sugerido de precio de venta ---
				$preg_cot = "SELECT prod_costo, pp_id, prod_origen FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $op['op_id'] . "' ORDER BY prod_costo DESC LIMIT 3";
				//echo $preg_cot . '<br>';
				$matr_cot = mysql_query($preg_cot) or die("ERROR:" . $preg_cot);
				$array_costos = [];
				while($cot = mysql_fetch_array($matr_cot)) {
					if($cot['prod_costo'] > 0) {
						$array_costos[$cot['pp_id']]['cost'] = $cot['prod_costo'] * (1 + ($MargMapf / 100));
						$array_costos[$cot['pp_id']]['orig'] = strtoupper(substr($cot['prod_origen'],0,1));
					} else {
						$array_costos[$cot['pp_id']] = 0;
					}
				}
				// --- Se ordenan por número de registro de cotización ---
				ksort($array_costos);
				$cont = 0;
				foreach($array_costos as $key => $val) {
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($array_cols[$cont], $val['cost'])
								->setCellValue($colorig[$cont], $val['orig']);
					$cont++;
				}
			}
			$j++;
		}
	}

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(DIR_DOCS . $nom_excel);
	//-----------------  Fin de Creación de Archivo Excel   -----------------------			

	$sql_data_array = [
		'orden_id' => $sub['orden_id'],
		'doc_usuario' => $_SESSION['usuario'],
		'doc_archivo' => $nom_excel,
		'doc_clasificado' => 0,
	];
	if($sub['sub_aseguradora'] == '0') {
		$sql_data_array['doc_nombre'] = 'Cotización de Refacciones Particular'; 
	} else {
		$sql_data_array['doc_nombre'] = 'Cotización de Refacciones Aseguradora';
	}

	ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
	bitacora($sub['orden_id'], $sql_data_array['doc_nombre'], $dbpfx);
	redirigir('documentos.php?accion=listar&orden_id=' . $sub['orden_id']);
}

?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>