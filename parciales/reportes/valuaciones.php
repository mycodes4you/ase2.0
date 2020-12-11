<?php
/*************************************************************************************
*   Script de "reporte de Ingreso de Vehiculos"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/

if ($f1125095 == '1' || $_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol05'] == '1') {

	if($export == 1){ // ---- Hoja de calculo ----

		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'COMPARAR VALUACIONES: ' . $nombre_agencia;
			
		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("Comparar Valuaciones")
					->setKeywords("AUTOSHOP EASY");

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($celda, $titulo)
					->setCellValue("A3", $fecha_export);

		// ------ ENCABEZADOS ---
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A4", "OT")
					->setCellValue("B4", "Cliente")
					->setCellValue("C4", "Vehículo")
					->setCellValue("D4", $lang['Placa'])
					->setCellValue("E4", "Estatus")
					->setCellValue("F4", "Siniestro")
					->setCellValue("G4", "Valuación Solicitada")
					->setCellValue("H4", "Valuación Autorizada")
					->setCellValue("I4", "Diferencia")
					->setCellValue("J4", "Fecha de Solicitud")
					->setCellValue("K4", "Fecha de autorización")
					->setCellValue("L4", "Días para autorización");
	
		$z= 5;

	}
	else{ // ---- HTML ----
	
	
		$preg0 = "SELECT orden_cliente_id, orden_id, orden_estatus, orden_fecha_registro_expediente, orden_fecha_presupuesto, orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes WHERE ";
		$preg0 .= $prega ;
		$matr0 = mysql_query($preg0);
		
		if($nivel > 0) {
			$encabezado .= ' con monto de alerta de $' . number_format($nivel,0);
		}

		echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-12">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2>' . $filas . $encabezado . '</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 ">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">			
					<tr>
						<th>
							<big>OT</big>
						</th>
						<th>
							<big>Cliente</big>
						</th>
						<th>
							<big>Vehículo</big>
						</th>
						<th>
							<big>' . $lang['Placa'] . '</big>
						</th>
						<th>
							<big>Estatus</big>
						</th>
						<th>
							<big>Siniestro</big>
						</th>
						<th>
							<big>Valuación Solicitada</big>
						</th>
						<th>
							<big>Valuación Autorizada</big>
						</th>
						<th>
							<big>Diferencia</big>
						</th>
						<th>
							<big>Fecha de Solicitud</big>
						</th>
						<th>
							<big>Fecha de autorización</big>
						</th>
						<th>
							<big>Días para autorización</big>
						</th>
					</tr>'."\n";

	}
	
	$fondo = 'claro';
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	$numots = mysql_num_rows($matr0);
	$cproms = 0; $cproma = 0;
		
	while($ord = mysql_fetch_array($matr0)) {
		
		$vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);
		$preg2 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' GROUP BY sub_reporte";
		$matr2 = mysql_query($preg2);
		
		$preg1 = "SELECT sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' GROUP BY sub_reporte";
		$matr1 = mysql_query($preg1) or die($preg1);
		$ima = array();
				
				
		while($aseico = mysql_fetch_assoc($matr1)) {
			$ima[$aseico['sub_aseguradora']] = [
				$ase[$aseico['sub_aseguradora']][0], 
				$ase[$aseico['sub_aseguradora']][1]
			];
		}
		
		while($gsub = mysql_fetch_array($matr2)) {
			
			if($export == 1){ // ---- Hoja de calculo ----
				
				// --- Celdas a grabar ----
				$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
				$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
				$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z; $n = 'N'.$z;
				
				$info_vehiculo = strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']);
				
				if($gsub['sub_reporte'] == '0'){ 
					$reporte = 'Particular'; 
				} else{ 
					$reporte = $gsub['sub_reporte'];
				}
				
				$cliente = '';
				foreach($ima as $k => $v) {
				
					$cliente .= $v[1] . ', ';
				}
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($a, $ord['orden_id'])
							->setCellValue($b, $cliente)
							->setCellValue($c, $info_vehiculo)
							->setCellValue($d, $vehiculo['placas'])
							->setCellValue($e, constant('ORDEN_ESTATUS_' . $ord['orden_estatus']))
							->setCellValue($f, $reporte);
				
			}
			else{ // ---- HTML ----
			
				echo '				
					<tr class="' . $fondo . '">
						<td style="padding-left:10px; padding-right:10px; text-align:center;">
							<a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" style=" display:block;">' . $ord['orden_id'] . '</a>
						</td>
						<td>';
							foreach($ima as $k => $v) {
								echo  '<img src="' . $v[0] . '" alt="" height="20" > ' . $v[1] . '<br>';
							}
				echo '
						</td>
						<td style="padding-left:10px; padding-right:10px;">
							' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']) . '
						</td>
						<td style="padding-left:10px; padding-right:10px;">
							' . $vehiculo['placas'] . '
						</td>
						<td style="padding-left:10px; padding-right:10px;">
							' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '
						</td>
						<td>'."\n";

				if($gsub['sub_reporte'] == '0'){ 
					echo '
							Particular'; 
				} else{ 
					echo ' 
							' . $gsub['sub_reporte'];
				}
			
				echo '
						</td>'."\n";
			}
				
			$preg1 = "SELECT sub_orden_id, sub_area FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $gsub['sub_reporte'] . "' AND orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' ";
			$matr1 = mysql_query($preg1) or die("Falló selección de subordenes");
			//echo '<table cellpadding="2" cellspacing="0" border="1" width="100%">';
			$partess = 0; $conss = 0; $mos = 0; $press = 0;$partesa = 0; $consa = 0; $moa = 0; $presa = 0;
			while($sub = mysql_fetch_array($matr1)) {
				$preg4 = "SELECT op_precio, op_cantidad, op_tangible, op_pres FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
				$matr4 = mysql_query($preg4) or die("Falló selección de Productos");
				while($op = mysql_fetch_array($matr4)) {
					$subtotal = ($op['op_cantidad'] * $op['op_precio'] );
					if($op['op_tangible'] == '2' && $op['op_pres'] == 1) {
						$conss = $conss + $subtotal;
					} elseif($op['op_tangible'] == '2') {
						$consa = $consa + $subtotal;
					} elseif($op['op_tangible'] == '1' && $op['op_pres'] == 1) {
						$partess = $partess + $subtotal;
					} elseif($op['op_tangible'] == '1') {
						$partesa = $partesa + $subtotal;
					} elseif($op['op_tangible'] == '0' && $op['op_pres'] == 1) {
						$mos = $mos + $subtotal;
					} elseif($op['op_tangible'] == '0') {
						$moa = $moa + $subtotal;
					}
				}
			}
			$press = $partess + $conss + $mos;
			$presa = $partesa + $consa + $moa;
			if($nivel > 0) {
				if($press > ($nivel * 1.05)) {$fondosol = 'alarma_critica';}
				elseif($press == 0) {$fondosol = '';}
				elseif($press <= $nivel) {$fondosol = 'alarma_normal';}
				else {$fondosol = 'alarma_preventiva';}
				if($presa > ($nivel * 1.05)) {$fondoaut = 'alarma_critica';}
				elseif($presa == 0) {$fondoaut = '';}
				elseif($presa <= $nivel) {$fondoaut = 'alarma_normal';}
				else {$fondoaut = 'alarma_preventiva';}
			} else {
				if($press > $presa || $press < $presa) {$fondosol = 'alarma_critica'; $fondosaut = 'alarma_critica';} else {$fondosol = '';}
				if($press == $presa) {$fondosol = 'alarma_normal'; $fondoaut = 'alarma_normal';} else {$fondoaut = '';}
				if($press == '0' || $presa == '0') {$fondosol = 'alarma_preventiva'; $fondoaut = 'alarma_preventiva';}
			}
			
			if($export == 1){ // ---- Hoja de calculo ----
				
				$valuacion_solicitada = 'Partes: $' . number_format($partess, 2) . ', MO: $' . number_format($mos, 2) . ', Cons: $' . number_format($conss, 2) . ', Total: $' . number_format($press, 2);
				
				$valuacion_autorizada = 'Partes: $' . number_format($partesa, 2) . ', MO: $' . number_format($moa, 2) . ', Cons: $' . number_format($consa, 2) . ', Total: $' . number_format($presa, 2);
				
				$diferencia = number_format(($presa - $press), 2);
				
				$preg_fechas = "SELECT orden_fecha_registro_expediente, orden_fecha_presupuesto FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $ord['orden_id'] . "'";
				$matr_fechas = mysql_query($preg_fechas) or die("Falló selección de fechas");
				$info_fe = mysql_fetch_assoc($matr_fechas);
				
				if($info_fe['orden_fecha_presupuesto'] != ''){
					$fecha_presupuesto = date('Y-m-d', strtotime($info_fe['orden_fecha_presupuesto']));
					$fecha_presupuesto = PHPExcel_Shared_Date::PHPToExcel( strtotime($fecha_presupuesto) );
						
					// --- cambiar el formato de la celda tipo fecha/date ---
					$objPHPExcel->getActiveSheet()
								->getStyle($kkk)
								->getNumberFormat()
								->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);	
				} else{
					$fecha_presupuesto = '';
				}
				
				if($info_fe['orden_fecha_registro_expediente'] != ''){
					$fecha_registro =  date('Y-m-d', $info_fe['orden_fecha_registro_expediente']);
					$fecha_presupuesto = PHPExcel_Shared_Date::PHPToExcel( strtotime($fecha_presupuesto) );
						
					// --- cambiar el formato de la celda tipo fecha/date ---
					$objPHPExcel->getActiveSheet()
								->getStyle($j)
								->getNumberFormat()
								->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				} else{
					$fecha_registro = '';
				}
				
				$ddif = intval(((strtotime($info_fe['orden_fecha_presupuesto']) - strtotime($info_fe['orden_fecha_registro_expediente'])) / 86400));
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($g, $valuacion_solicitada)
							->setCellValue($h, $valuacion_autorizada)
							->setCellValue($i, $diferencia)
							->setCellValue($j, $fecha_registro)
							->setCellValue($kkk, $fecha_presupuesto)
							->setCellValue($l, $ddif);

			}
			else{ // ---- HTML ----
			
				echo '
						<td class="' . $fondosol . '">
							Partes: $' . number_format($partess, 2) . '<br>MO: $' . number_format($mos, 2) . '<br>Cons: $' . number_format($conss, 2) . '<br>Total: $' . number_format($press, 2) . '
						</td>
						<td class="' . $fondoaut . '">Partes: $' . number_format($partesa, 2) . '<br>MO: $' . number_format($moa, 2) . '<br>Cons: $' . number_format($consa, 2) . '<br>Total: $' . number_format($presa, 2) . '
						</td>
						<td class="' . $fondosol . $fondoaut . '" style="text-align:right;">
							$' . number_format(($presa - $press), 2) . '
						</td>
						<td>
							' . $ord['orden_fecha_registro_expediente'] . '
						</td>
						<td>
							' . $ord['orden_fecha_presupuesto'] . '
						</td>'."\n";
			}

			$ddif = intval(((strtotime($ord['orden_fecha_presupuesto']) - strtotime($ord['orden_fecha_registro_expediente'])) / 86400));
			
			if($export == 1){ // ---- Hoja de calculo ----
				
				$z++;
			}
			else{ // ---- HTML ----
				
				echo '
						<td>
							' . $ddif . '
						</td>
					</tr>'."\n";
			}

			$tpress = $tpress + $press;
			if($press > 0) { $cproms++; }
			$tpresa = $tpresa + $presa;
			if($presa > 0) { $cproma++; }
			if($fondo == 'obscuro') {$fondo = 'claro';} else { $fondo = 'obscuro';}

		}
		
	}
	
	if($export == 1){ // ---- Hoja de calculo ----
		
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="comparacion_de_valuaciones.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;

	}
	else{ // ---- HTML ----

		echo '
					<tr>
						<th colspan="6" style="text-align:right;">
							<big>Promedios</big>
						</th>
						<th style="text-align:right;">
							<big>$' . number_format(($tpress/$cproms),2) . '</big>
						</th>
						<th style="text-align:right;">
							<big>$' . number_format(($tpresa/$cproma), 2) . '</big>
						</th>
						<th style="text-align:right;">
						</th><th colspan="2">
						</th>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>'."\n";
	}

} else{
	
	if($export == 1){ // ---- Hoja de calculo ----

	}
	else{ // ---- HTML ----
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}


?>