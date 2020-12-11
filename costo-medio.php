<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/costo-medio.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

if ($accion === 'entrada') {

	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	echo '			<form enctype="multipart/form-data" action="costo-medio.php?accion=genera" method="POST">'."\n";
	echo '			<table>
				<tr class="encabezado"><td>' . $lang['Seleccione su archivo Excel a procesar'] . '</td></tr>
				<tr><td><input name="excel" type="file" /></td></tr>
				<tr><td><input type="submit" value="Enviar Archivo" /></td></tr>
			</table></form>'."\n";
}

elseif ($accion==='genera') {

// --- Creación de excel para con Costo Medio para GNP

	$archivo = basename($_FILES['excel']['name']);
	if (move_uploaded_file($_FILES['excel']['tmp_name'], DIR_DOCS . $archivo)) {
		require_once ('Classes/PHPExcel.php');
		require_once ('Classes/PHPExcel/Reader/Excel2007.php');
		include_once ('Classes/PHPExcel/IOFactory.php');
		$objPHPExcel = PHPExcel_IOFactory::load(DIR_DOCS . $archivo);
		$objPHPExcel->setActiveSheetIndex(0);
		$numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
//		echo $archivo;
		// --- Leemos los siniestros de la relación entregada por GNP y los guardamos en un arreglo
//		echo $numRows;
		$siniestro = array();
		for ($i = 3; $i <= $numRows; $i++) {
			$sin = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
			if($sin != '') { $siniestro[$sin] = $i; }
		}
//			echo 'Fila ' . $i . ' Siniestro: ' . $sin . '.<br>';
		$objPHPExcel = new PHPExcel();
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objPHPExcel = $objReader->load(DIR_DOCS . $archivo);
		$objPHPExcel->setActiveSheetIndex(0);
		foreach($siniestro as $sin => $fila) {
			$preg1 = "SELECT sub_orden_id, orden_id, sub_estatus FROM " . $dbpfx . "subordenes WHERE sub_reporte = '$sin' AND sub_estatus < 190";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección SubOrden! " . $preg1);
			$est = '190'; $partes = 0; $mo = 0; $partes1 = 0; $orden_id = '';
			while($sub = mysql_fetch_array($matr1)) {
				$preg2 = "SELECT op_precio, op_precio_original, op_tangible, op_autosurtido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_pres IS NULL";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección Items! " . $preg2);
				while($op = mysql_fetch_array($matr2)) {
					if($op['op_precio_original'] > 0) {
						$precio = $op['op_precio_original'];
					} else {
						$precio = $op['op_precio'];
					}
					if($op['op_autosurtido'] == '1') {
						if($op['op_tangible'] == '1') {
							$partes1 = $partes1 + $precio;
						}
					}
					if($op['op_tangible'] == '1') {
						$partes = $partes + $precio;
					} elseif($op['op_tangible'] == '2' || $op['op_tangible'] == '0') {
						$mo = $mo + $precio;
					}
				}
				$orden_id = $sub['orden_id'];
				if($sub['sub_estatus'] < $est) { $est = $sub['sub_estatus']; }
			}
			if($est < '120' && $est > '101') { $estatus = 'REPARACION'; }
			elseif($est == '101' || $est == '120' || ($est >= '124' && $est < '130' )) { $estatus = 'PENDIENTE DE AUTORIZAR'; }
			elseif($est == '130') { $estatus = 'PD'; }
			elseif($est == '131') { $estatus = 'PT'; }
			elseif($est == '132') { $estatus = 'GLOBAL'; }
			elseif($est == '133') { $estatus = 'IMPROCEDENTE EN INVESTIGACION GNP'; }
			elseif($est == '134') { $estatus = 'DAÑO MENOR AL DEDUCIBLE'; }
			elseif($est == '135') { $estatus = 'DESISTIMIENTO'; }
			else { $estatus = 'NO REGISTRO INGRESO'; }
			if($partes1 == '0') { $partes1 = ''; }

// -------------------   Guardar datos a Archivo Excel   ----------------------------------			
			
			$objPHPExcel->getActiveSheet()
				->setCellValue('P'.$fila, $orden_id)
				->setCellValue('Q'.$fila, $estatus)
				->setCellValue('S'.$fila, $mo)
				->setCellValue('T'.$fila, $partes)
				->setCellValue('U'.$fila, ($mo + $partes))
				->setCellValue('X'.$fila, $partes1);
            
//		echo 'P'.$fila.' ' . $orden_id . ' Q'.$fila.' ' . $estatus . ' S'.$fila.' ' . $mo . ' $Ref ' . $partes . ' Total ' . ($mo + $partes) . '  Otros ' . $partes1 . '<br>';
//		echo 'Orden ID: ' . $orden_id . '<br>';
		}
		
		// --- Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Costo-Medio-AutoShop-Easy.xlsx"');
		header('Cache-Control: max-age=0');
//		exit;

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
 
//		$objWriter->save(DIR_DOCS . 'AAA-Prueba.xlsx');
		
	} else {
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		echo '			<h1>No se logró recibir el archivo.</h1>'."\n";
	}
}

?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
