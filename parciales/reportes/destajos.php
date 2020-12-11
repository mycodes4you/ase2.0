<?php
/*************************************************************************************
*   Script de "reporte de Destajos"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
**************************************************************************************/

if ($f1125055 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1') {
	
	
	$encabezado = 'Reporte de Semanal de Destajo por Operadores del ' . $t_ini . ' al ' . $t_fin;
	$mes_este = date('n');

	if($desdiacorte == '') { $desdiacorte = 4; }

	$dini = date('w', strtotime($feini));
	if($dini > $desdiacorte) { $nvafeini = strtotime($feini) + ((7 - ($dini - $desdiacorte)) * 86400); }
	elseif($dini < $desdiacorte) { $nvafeini = strtotime($feini) + (($desdiacorte - $dini) * 86400); }
	else { $nvafeini = strtotime($feini); }

	$dfin = date('w', strtotime($fefin));
	if($dfin > $desdiacorte) { $nvafefin = strtotime($fefin) + ((7 - ($dfin - $desdiacorte)) * 86400); }
	elseif($dfin < $desdiacorte) { $nvafefin = strtotime($fefin) + (($desdiacorte - $dfin) * 86400); }
	else { $nvafefin = strtotime($fefin); }

	$sems = intval(((($nvafefin - $nvafeini) / 86400) / 7) + 1);

	$cols = $sems + 3;

	if($export == 1){ // ---- Hoja de calculo ----

	}
 	else{ // ---- HTML ----
	
		echo '				
<div class="page-content">
	<br>
	<div class="row"> <!-box header del título. -->
		<div class="col-md-12">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2>' . $encabezado . ' ' . $lang['Día de corte'] . ' ' . $nombredia[$desdiacorte] . '</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 ">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">' . "\n";
		$fondo = 'claro';
	}
	
	
	$j = 0;

	$preg0 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE acceso = 0 AND rol09 = 1 AND activo = 1 ORDER BY nombre,apellidos";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de operadores");
	$filas = mysql_num_rows($matr0);

	if($export == 1){ // ---- Hoja de calculo ----
            
		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'DESTAJOS: ' . $nombre_agencia;
	
		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("Listado de Destajos")
					->setKeywords("AUTOSHOP EASY");

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($celda, $titulo)
					->setCellValue("A3", $fecha_export);

		// ------ ENCABEZADOS ---
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A4", "Usuario")
					->setCellValue("B4", "Nombre y Apellidos");
		
		for($i = 0; $i < $sems; $i++) {
			
			if($i === 0){
				$letra = 'C';
			} 
			elseif($i === 1){
				$letra = 'D';
			}
			elseif($i === 2){
				$letra = 'E';
			}
			elseif($i === 3){
				$letra = 'F';
			}
			elseif($i === 4){
				$letra = 'G';
			}
			elseif($i === 5){
				$letra = 'H';
			}
			elseif($i === 6){
				$letra = 'I';
			}
			elseif($i === 7){
				$letra = 'J';
			}
			elseif($i === 8){
				$letra = 'K';
			}
			elseif($i === 9){
				$letra = 'L';
			}
			elseif($i === 10){
				$letra = 'M';
			}
			
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($letra . "4", date('Y-m-d', ($nvafeini + ($j*7*86400))));
			
			$j++;
			if($j == 18) { break; }
		}
		
		$z= 5;
            
	}
 	else{ // ---- HTML ----
	
		echo '				
					<tr>
						<th><big>Usuario</big></th>
						<th><big>Nombre y Apellidos</big></th>';
		$j=0;
		for($i = 0; $i < $sems; $i++) {
			echo '
						<th><big>' . date('Y-m-d', ($nvafeini + ($j*7*86400))) . '</big></th>';
			$j++;
			if($j == 18) { break; }
		}
		echo '
					</tr>'."\n";
		
	}
	
	//echo 'Nva ini: ' . date('Y-m-d H:i:s', $nvafeini) . ' Nva Fin: ' . date('Y-m-d H:i:s', $nvafefin) . '<br>';
	$opr = array();
	while($usr = mysql_fetch_array($matr0)) {

		$preg1 = "SELECT usuario, fecha_creado, monto, impuesto FROM " . $dbpfx . "destajos WHERE usuario = '" . $usr['usuario'] . "' AND ";
		$prega = "fecha_creado > '" . date('Y-m-d 00:00:00', ($nvafeini - (6*86400))) . "' AND fecha_creado < '" . date('Y-m-d 23:59:59', $nvafefin) . "' ";
		$preg1 .= $prega;
		//echo $preg1 . '<br>';
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de destajos!" . $preg1);
		$jj = 0;
		while($des = mysql_fetch_array($matr1)) {
			$sem = intval(((strtotime($des['fecha_creado']) - ($nvafeini - (6*86400))) / 86400) / 7);
//				echo $des['usuario'] . '->' . $des['fecha_creado'] . ' -> ' . $sem . ' ' . $des['monto'] . ' ' . $des['impuesto'] . '<br>-----<br>';
			$opr[$usr['usuario']][$sem] = $opr[$usr['usuario']][$sem] + $des['monto'] + $des['impuesto'];
//				echo $sem . '(' . $des['fecha_creado'] . '): ' . $cob . ' -> ' . $opr[$usr['usuario']][$sem] . '<br>';
		}
		
		if($export == 1){ // ---- Hoja de calculo ----
			// --- Celdas a grabar ----
			$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
			$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
			$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z;
			
			
			
			
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($a, $usr['usuario'])
						->setCellValue($b, $usr['nombre'] . ' ' . $usr['apellidos']);
			
			for($i = 0; $i < $sems; $i++) {
				
				if($i === 0){
					$letra = $c;
				} 
				elseif($i === 1){
					$letra = $d;
				}
				elseif($i === 2){
					$letra = $e;
				}
				elseif($i === 3){
					$letra = $f;
				}
				elseif($i === 4){
					$letra = $g;
				}
				elseif($i === 5){
					$letra = $h;
				}
				elseif($i === 6){
					$letra = $i;
				}
				elseif($i === 7){
					$letra = $j;
				}
				elseif($i === 8){
					$letra = $kkk;
				}
				elseif($i === 9){
					$letra = $l;
				}
				elseif($i === 10){
					$letra = $m;
				}
				
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($letra, $opr[$usr['usuario']][$i]);
				$jj++;
				if($jj == 18) { break; }
			}
			
			
			
		}
 		else{ // ---- HTML ----
		
			echo '				
					<tr class="' . $fondo . '">
						<td>
							<big><a href="recibosrh.php?accion=listar&operador=' . $usr['usuario'] . '&feini=' . date('Y-m-d 00:00:00', ($nvafeini - (6*86400))) . '&fefin=' . date('Y-m-d 23:59:59', $nvafefin) . '" target="_blank">' . $usr['usuario'] . '</a></big>
						</td>
						<td style="text-align: left !important;">
							<big>' . $usr['nombre'] . ' ' . $usr['apellidos'] . '</big>
						</td>';
			
			for($i = 0; $i < $sems; $i++) {
				echo '
						<td>
							<big>$' . number_format($opr[$usr['usuario']][$i], 2) . '</big>
						</td>';
				$jj++;
				if($jj == 18) { break; }
			}
			echo '
					</tr>'."\n";
			if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
		
		if($export == 1){ // ---- Hoja de calculo ----
			$z++;    
		}
	}
		
	if($export == 1){ // ---- Hoja de calculo ----    
	
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="destajos.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}
	else{ // ---- HTML ----
		echo '		
				</table>
			</div>
		</div>
	</div>
</div>'."\n";
	}
	
} else {
	echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
}

?>