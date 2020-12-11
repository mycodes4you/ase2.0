<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('particular/perf-config.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

$funnum = 1085000;

/*	$retorno = 0; $retorno = validaAcceso('101101', $dbpfx);
	if ($retorno == '1') {
		$mensaje = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}	
*/	

/*  ----------------  obtener nombres de aseguradoras   ------------------- */
	
		$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_nic, prov_def, prov_dde FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		$ase[0] = "Particular";
		$ase_logo[0] = "logo-agencia.png";
		while ($aseg = mysql_fetch_array($arreglo)) {
			$ase[$aseg['aseguradora_id']] = $aseg['aseguradora_nic'];
			$ase_logo[$aseg['aseguradora_id']] = $aseg['aseguradora_logo'];
		}
/*  ----------------  nombres de aseguradoras   ------------------- */
	

include('idiomas/' . $idioma . '/performance-talleres.php');
include('parciales/encabezado.php');
echo '	<div id="body">';
include('parciales/menu_inicio.php'); 
echo '			<div id="principal">'."\n";

//	echo 'Fecha de Inicio: ' . $feini . ' Fecha de fin: ' . $fefin . '<br>';

	if(!isset($feini)) { $feini = date('Y-m-01 00:00:00'); } 
	else { $feini = date('Y-m-d 00:00:00', strtotime($feini)); }
	if(!isset($fefin)) { $fefin = date('Y-m-t 23:59:59'); }
	else { $fefin = date('Y-m-d 23:59:59', strtotime($fefin)); }
	 
	echo '		<form action="performance-talleres-nvo.php" method="post" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" border="0" width="80%">
				<td colspan="6">Seleccione las fechas que desea consultar<br>'.$feini. ' ' . $fefin ."\n";
				
	require_once("calendar/tc_calendar.php");
	echo '					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<tr><td>Fecha de Inicio<br>';
		//instantiate class and set properties
	$myCalendar = new tc_calendar("feini", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar.gif");
	$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
//	$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2011, 2020);
	$myCalendar->setAutoHide(true, 5000);

		//output the calendar
	$myCalendar->writeScript();
	echo '</td><td>Fecha de Fin<br>';	  
		//instantiate class and set properties
	$myCalendar = new tc_calendar("fefin", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar.gif");
	$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
//	$myCalendar->setDate(date("d"), date("m"), date("Y"));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
//	$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2011, 2020);
	$myCalendar->setAutoHide(true, 5000);

		//output the calendar
	$myCalendar->writeScript();
	echo '</td><td>Aseguradora</td><td>';
	echo '							<select name="aseguradora" size="1">'."\n";
	foreach ($ase as $k => $v) {
		echo '								<option value="' . $k . '"';
		if($aseguradora == $k) { echo ' selected="selected"'; }
		echo ' >'.$v.'</option>'."\n";
	}
	echo '							</select></td></tr>'."\n";
	echo '						<tr><td colspan="3"><input type="hidden" name="accion" value="' . $accion . '" /><input type="hidden" name="nomrep" value="' . $nomrep . '" /><input type="submit" value="Enviar" /></td></tr></table>';
	echo '				</td></tr>
			</table></form>'."\n";

	if(!isset($aseguradora)) { $aseguradora = 1; }
//	echo date('Y-m-t');
	$preg0 = "SELECT orden_id, orden_estatus, orden_fecha_recepcion, orden_fecha_promesa_de_entrega, orden_fecha_de_entrega, orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes WHERE ";
	$prega = " orden_fecha_de_entrega >= '" . $feini . "' AND orden_fecha_de_entrega <= '" . $fefin . "' ";
	$preg0 .= $prega ;
//	$preg0 .= " AND orden_fecha_de_entrega IS NOT NULL AND orden_fecha_promesa_de_entrega IS NOT NULL ORDER BY orden_id";
	$preg0 .= " AND orden_fecha_de_entrega IS NOT NULL ORDER BY orden_id";
//	echo $preg0;
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Ordenes!");
	$filas = mysql_num_rows($matr0);	
//	echo 'Filas: ' .$filas.'<br>';
	$mo = array(); $partes = array(); $ot = array(); $day = array(); 
	$dprom = array(); $dv = array(); $dp = array(); $promesa = array();
	$costmm30 = 0; $elementos = 0;
	$costmmay30 = 0; $elemenmay = 0;
	$costmtot = 0; $elementot = 0;
	while($ord = mysql_fetch_array($matr0)) {
		$preg1 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_aseguradora = '$aseguradora' AND sub_estatus < '130' GROUP BY sub_reporte";
//		echo $preg1;
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de subordenes! " . $preg1);
		while($gsub = mysql_fetch_array($matr1)) {
			$preg2 = "SELECT sub_mo, sub_partes, sub_consumibles FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_aseguradora = '$aseguradora' AND sub_reporte = '" . $gsub['sub_reporte'] . "'";
//		echo $preg1;
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de subordenes! " . $preg2);
			$filas = mysql_num_rows($matr2);
			if($filas > 0) {
				$m = 0; $p = 0; $c = 0;
				while($sub = mysql_fetch_array($matr2)) {
					$m = $m + $sub['sub_mo'];
					$p = $p + $sub['sub_partes'];
					$c = $c + $sub['sub_consumibles'];
				}
				$s = $p + $c;
				$t = $m + $s;
//				echo '<br>' . $m . ' -> ' . $p . ' -> ' . $c . ' -> ' . $s . ' -> ' . $t . ' <br> ';
				if($t < $rango1) { $mo[1] = $mo[1] + $m; $partes[1] = $partes[1] + $s; $ot[1]++; $costmm30 = $costmm30 + $t; $elementos++; }
				elseif($t <= $rango2) { $mo[2] = $mo[2] + $m; $partes[2] = $partes[2] + $s; $ot[2]++; $costmm30 = $costmm30 + $t; $elementos++; }
				elseif($t <= $rango3) { $mo[3] = $mo[3] + $m; $partes[3] = $partes[3] + $s; $ot[3]++; $costmm30 = $costmm30 + $t; $elementos++; }
				elseif($t <= $rango4) { $mo[4] = $mo[4] + $m; $partes[4] = $partes[4] + $s; $ot[4]++; $costmm30 = $costmm30 + $t; $elementos++; }
				elseif($t <= $rango5) { $mo[5] = $mo[5] + $m; $partes[5] = $partes[5] + $s; $ot[5]++; $costmm30 = $costmm30 + $t; $elementos++; }
				elseif($t <= $rango6) { $mo[6] = $mo[6] + $m; $partes[6] = $partes[6] + $s; $ot[6]++; $costmm30 = $costmm30 + $t; $elementos++; }
				elseif($t <= $rango7) { $mo[7] = $mo[7] + $m; $partes[7] = $partes[7] + $s; $ot[7]++; $costmmay30 = $costmmay30 + $t; $elemenmay++; }
				elseif($t <= $rango8) { $mo[8] = $mo[8] + $m; $partes[8] = $partes[8] + $s; $ot[8]++; $costmmay30 = $costmmay30 + $t; $elemenmay++; }
				elseif($t <= $rango9) { $mo[9] = $mo[9] + $m; $partes[9] = $partes[9] + $s; $ot[9]++; $costmmay30 = $costmmay30 + $t; $elemenmay++; }
				elseif($t > $rango9) { $mo[10] = $mo[10] + $m; $partes[10] = $partes[10] + $s; $ot[10]++; $costmmay30 = $costmmay30 + $t; $elemenmay++; }
				$costmtot = $costmtot + $t; $elementot++;
//				print_r($ot);
				$ini = strtotime($ord['orden_fecha_recepcion']);
				$fin = strtotime($ord['orden_fecha_de_entrega']);
				$prom = strtotime($ord['orden_fecha_promesa_de_entrega']);
				$dias = intval(($fin - $ini) / 86400);

				if($dias <= $dias1) { $day[1]++; $dprom[1] = $dprom[1] + $dias; }
				elseif($dias <= $dias2) { $day[2]++; $dprom[2] = $dprom[2] + $dias;  }
				elseif($dias <= $dias3) { $day[3]++; $dprom[3] = $dprom[3] + $dias;  }
				elseif($dias <= $dias4) { $day[4]++; $dprom[4] = $dprom[4] + $dias;  }
				elseif($dias > $dias4) { $day[5]++; $dprom[5] = $dprom[5] + $dias;  }
		
				if($prom > $ini) {
					if($fin > $prom) {
						$promesa[0]++;
					} else {
						$promesa[1]++;
					}
				}
			}
		}
	}
	
	echo '			<table cellspacing="1" cellpadding="2" border="0" width="100%">' . "\n";
	echo '				<tr class="cabeza_tabla"><td colspan="18" style="text-align: left;">Seguimiento al Costo para el Cliente: ' . $ase[$aseguradora] . ' del ' . $feini . ' al ' . $fefin . '</td></tr>' . "\n";
	echo '				<tr class="encabezados"  style="text-align: center;"><td style="text-align: left;">Rasgos de Costo del Siniestro</td><td>' . $eti_rango1 . '</td><td>' . $eti_rango2 . '</td><td>' . $eti_rango3 . '</td><td>' . $eti_rango4 . '</td><td>' . $eti_rango5 . '</td><td>' . $eti_rango6 . '</td><td>' . $eti_rango7 . '</td><td>' . $eti_rango8 . '</td><td>' . $eti_rango9 . '</td><td>' . $eti_rango10 . '</td></tr>'."\n";

	echo '				<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Número de siniestros</td>';
	for($i=1;$i < 11;$i++) {
	$cmedio[$i] = ($mo[$i] + $partes[$i]) / $ot[$i];
	echo '<td>';
		if($cmedio[$i] > 0) { echo $ot[$i]; }
	echo '</td>'; 
	}
	echo '</tr>'."\n";
	
	echo '				<tr class="claro" style="text-align: center;"><td style="text-align: left;">Frecuencia (%)</td>';
	for($i=1;$i < 11;$i++) {
		if($cmedio[$i] > 0) { 
			$ots = $ots + $ot[$i];
		} 
	}
	for($i=1;$i < 11;$i++) {
		echo '<td>'; 
		$cfrec[$i] = round((($ot[$i]/$ots) * 100), 2);
		if($cmedio[$i] > 0) { echo $cfrec[$i]; } else { echo '0'; }
		echo '%</td>'; 
	}
	echo '</tr>'."\n";

	echo '				<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Costo Medio</td>';
	for($i=1;$i < 11;$i++) { 
	echo '<td>' . number_format($cmedio[$i],2) . '</td>'; 
	}
	echo '</tr>'."\n";

	echo '				<tr class="claro" style="text-align: center;"><td style="text-align: left;">% Mano de Obra</td>';
	for($i=1;$i < 11;$i++) { 
		
		echo '<td>' . round((($mo[$i] / ($mo[$i] + $partes[$i])) * 100), 2) . '%</td>'; 
	}
	echo '</tr>'."\n";

	echo '				<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">% Refacciones</td>';
	for($i=1;$i < 11;$i++) { 
		
		echo '<td>' . round((($partes[$i] / ($mo[$i] + $partes[$i])) * 100), 2) . '%</td>'; 
	}
	echo '</tr>'."\n";
	
	echo '<tr><td colspan="11" style="height:40px;"></td></tr>'."\n";

	echo '				<tr><td colspan="3">';
	echo '					<table width="100%">'."\n";
	echo '						<tr class="encabezados"  style="text-align: center;"><td style="text-align: left;">Indicadores</td><td>Medidas</td><td>Semáforo</tr>'."\n";

	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Costo Medio (Menores a 30 mil)</td>';
/*	for($i=1;$i < 6;$i++) {
		$costmm30 = $costmm30 + $cmedio[$i];
		if(isset($cmedio[$i]) && $cmedio[$i] != '') { $elementos++; } 
	}	
*/	$costmm30 = round(($costmm30/$elementos), 2);
	echo '<td>' . number_format($costmm30,2) . '</td><td>';
	if($costmm30 <= $costoobjm30m) { echo '<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Dentro del objetivo" height="20">'; } else { echo '<img src="idiomas/' . $idioma . '/imagenes/alerta-critica.png" alt="Fuera del objetivo" height="18">'; }
	echo '</td>'; 
	echo '</tr>'."\n";

	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Costo Objetivo (Menores a 30 mil)</td>';
	echo '<td>' . number_format($costoobjm30m,2) . '</td><td></td>'; 
	echo '</tr>'."\n";

	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Diferencia en pesos (Meta - Real)</td>';
	echo '<td>' . number_format(($costmm30 - $costoobjm30m),2) . '</td><td></td>'; 
	echo '</tr>'."\n";

	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Distancia Porcentual</td>';
	$distporc = round(((($costmm30 - $costoobjm30m) / $costmm30) * 100), 2);
	echo '<td>' . $distporc . '%</td><td></td>'; 
	echo '</tr>'."\n";

	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Costo Medio (Mayores a 30 mil)</td>';
/*	for($i=7;$i < 11;$i++) {
		$costmmay30 = $costmmay30 + $cmedio[$i];
		if(isset($cmedio[$i]) && $cmedio[$i] != '') { $elemenmay++; } 
	}	
*/	$costmmay30 = round(($costmmay30/$elemenmay), 2);
	echo '<td>' . number_format($costmmay30,2) . '</td><td>';
	echo '</td>'; 
	echo '</tr>'."\n";

	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Costo Medio Total</td>';
/*	for($i=1;$i < 11;$i++) {
		$costmtot = $costmtot + $cmedio[$i];
		if(isset($cmedio[$i]) && $cmedio[$i] != '') { $elementot++; } 
	}	
*/	$costmtot = round(($costmtot/$elementot), 2);
	echo '<td>' . number_format($costmtot,2) . '</td><td>';
	echo '</td>'; 
	echo '</tr>'."\n";
	
	echo '<tr><td colspan="3"></td></tr>'."\n";

	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">% de valuaciones mayores a 30 mil</td>';
	$valmay = 0; $valmen = 0; $elval = 0;
	for($i=1;$i < 11;$i++) {
		if($i < 7 && $ot[$i] > 0) { $valmen = $valmen + $ot[$i]; $elval++; } 
		if($i >= 7 && $ot[$i] > 0) { $valmay = $valmay + $ot[$i]; $elval++; } 
	}	
	
	echo '<td>' . round((($valmay / ($valmay + $valmen)) * 100), 2) . '%</td><td></td>'; 
	echo '</tr>'."\n";

	echo '					</table></td><td colspan="1"></td><td colspan="7" style="vertical-align:top;">'."\n";
	echo '					<table width="100%">'."\n";
	
	# PHPlot Example: Pie/text-data-single
	require_once ('parciales/phplot.php');

	$datav = array(
		array('', 0,'',''),
		array($eti_rango1, 1, $cfrec[1],22),
		array($eti_rango2, 2, $cfrec[2],30),
		array($eti_rango3, 3, $cfrec[3],17),
		array($eti_rango4, 4, $cfrec[4],11),
		array($eti_rango5, 5, $cfrec[5],7),
		array($eti_rango6, 6, $cfrec[6],2),
		array($eti_rango7, 7, $cfrec[7],2),
		array($eti_rango8, 8, $cfrec[8],1),
		array($eti_rango9, 9, $cfrec[9],1),
		array($eti_rango10, 10, $cfrec[10],2),
		array('', 11,'',''),
	);

/*	$datav = array(
		array('', 0,'',''),
		array($eti_rango1, 1, $cfrec[1],22),
		array($eti_rango2, 2, $cfrec[2],30),
		array($eti_rango3, 3, $cfrec[3],17),
		array($eti_rango4, 4, $cfrec[4],11),
		array($eti_rango5, 5, $cfrec[5],7),
		array($eti_rango6, 6, $cfrec[6],2),
		array($eti_rango7, 7, $cfrec[7],2),
		array($eti_rango8, 8, $cfrec[8],1),
		array($eti_rango9, 9, $cfrec[9],1),
		array($eti_rango10, 10, $cfrec[10],2),
		array('', 11,'',''),
	);
*/
/*	$datai = array(
		array('', 0,'',''),
		array($eti_rango1, 1, 22),
		array($eti_rango2, 2, 30),
		array($eti_rango3, 3, 17),
		array($eti_rango4, 4, 11),
		array($eti_rango5, 5, 7),
		array($eti_rango6, 6, 2),
		array($eti_rango7, 7, 2),
		array($eti_rango8, 8, 1),
		array($eti_rango9, 9, 1),
		array($eti_rango10, 10, 2),
		array('', 11,'',''),
	);
*/
	
	$t99_glob = new PHPlot(650,250);
// Volumen
	$t99_glob->SetFailureImage(False); // No error images
	$t99_glob->SetPrintImage(False); // No automatic output
	$t99_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$t99_glob->SetPlotType('linepoints');
	$t99_glob->SetDataType('data-data');
	$t99_glob->SetDataValues($datav);
	$t99_glob->SetTitle("Frecuencia de Reparaciones e Importes valuados");
	$t99_glob->SetYTitle(utf8_decode('Volumen de reparaciones %'));
	$t99_glob->SetXTitle(utf8_decode('Monto de reparaciones $'));
	$t99_glob->SetYDataLabelPos('plotin');
//	$t99_glob->SetYTickLabelPos('none');
//	$t99_glob->SetYTickPos('none');
	$t99_glob->SetDrawYGrid(False);
	$t99_glob->SetXTickPos('none');
	$t99_glob->SetXTickLabelPos('none');

	$t99_glob->SetLegend(array('Volumen', 'Ideal'));
//	$t99_glob->SetPieLabelType(array('label','value'), 'custom', 'dinero');
# Place the legend in the upper left corner:
//	$t99_glob->SetLegendPixels(5,5);
	$t99_glob->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$t99_glob->DrawGraph();

	
	echo '						<tr><td><img src="' . $t99_glob->EncodeImage() . '" alt="gráficas"></td></tr>'."\n";
	
	echo '					</table></td></tr>'."\n";
	echo '				<tr><td colspan="11" style="height:25px;"></td></tr>'."\n";
	echo '			</table>'."\n";

	echo '			<table cellspacing="1" cellpadding="2" border="0" width="100%">' . "\n";
	echo '				<tr class="cabeza_tabla"><td colspan="18" style="text-align: left;">Seguimiento al Servicio para el Cliente: ' . $ase[$aseguradora] . ' del ' . $feini . ' al ' . $fefin . '</td></tr>' . "\n";
	echo '				<tr class="encabezados"  style="text-align: center;"><td style="text-align: center;">Terminados</td><td>Número de Vehículos</td><td>Distribución de<br>Vehículos</td><td>Días Promedio</td><td>Distribución<br>Objetivo</td><td>Días Prom<br>Objetivo</td><td>Diferencia<br>Frecuencia</td><td>Diferencia<br>Días Prom</td></tr>'."\n";
	$totveh = $day[1] + $day[2] + $day[3] + $day[4] + $day[5]; 
	$totpro = $dprom[1] + $dprom[2] + $dprom[3] + $dprom[4] + $dprom[5]; 

	for($i=1;$i < 6;$i++) {
		echo '				<tr class="obscuro" style="text-align: center;"><td style="text-align: center;">' . $term[$i] . '</td>';
		$dv[$i] = round((($day[$i] / $totveh) * 100 ), 2);
		$dp[$i] = round(($dprom[$i] / $day[$i]), 1);
		echo '<td>' . $day[$i] . '</td><td>' . $dv[$i] . '%</td><td>' . $dp[$i] . '</td><td>' . $do[$i] . '%</td><td>' . $dpo[$i] . '</td><td>' . ($do[$i] - $dv[$i]) . '</td><td>' . ($dpo[$i] - $dp[$i]) . '</td>';
		echo '</tr>'."\n";
	}
	echo '				<tr class="obscuro" style="text-align: center;"><td style="text-align: center;">Total</td>';
	$dv[$i] = round((($day[$i] / $totveh) * 100 ), 2);
	$dp[$i] = round(($dprom[$i] / $day[$i]), 1);
	$tdp = round(($totpro / $totveh), 1);
	echo '<td>' . $totveh . '</td><td>100%</td><td>' . $tdp . '</td><td>100%</td><td>' . $dpo[6] . '</td><td>0%</td><td>' . ($dpo[6] - $tdp) . '</td>';
	echo '</tr>'."\n";
	echo '			</table>'."\n";

	echo '			<table cellspacing="1" cellpadding="2" border="0" width="100%">' . "\n";
	echo '				<tr><td colspan="11" style="height:25px;"></td></tr>'."\n";

	echo '				<tr><td colspan="3" width="40%">';
	echo '					<table width="100%">'."\n";
	echo '						<tr class="encabezados"  style="text-align: center;"><td style="text-align: left;">Indicadores</td><td>Medidas</td><td>Semáforo</tr>'."\n";

//	$costmm30 = 0; $elementos = 0;
	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Días de reparación promedio</td>';
	
	echo '<td>' . $tdp . '</td><td>';
	if($tdp <= $dpo[6]) { 
		echo '<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Dentro del objetivo" height="20">'; 
	} else { 
		echo '<img src="idiomas/' . $idioma . '/imagenes/alerta-critica.png" alt="Fuera del objetivo" height="18">'; 
	}
	echo '</td>'; 
	echo '</tr>'."\n";

	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Días de reparación objetivo</td>';
	echo '<td>' . $dpo[6] . '</td><td></td>'; 
	echo '</tr>'."\n";

	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Diferencia en Días (Meta - Real)</td>';
	echo '<td>' . ($dpo[6] - $tdp) . '</td><td></td>'; 
	echo '</tr>'."\n";

	echo '						<tr class="obscuro" style="text-align: center;"><td style="text-align: left;">Distancia porcentual</td>';
	$dstper = round(((($dpo[6] - $tdp) * 100) / $tdp), 2);
	echo '<td>' . $dstper . '%</td><td>';
	echo '</td>'; 
	echo '</tr>'."\n";
	
	if($promesa[1] > '0') {
		$cumfp = round((($promesa[1] * 100) / $totveh), 2);
	} else {
		$cumfp = 0;
	}

	echo '<tr><td colspan="3">PPP: ' . $promesa[0] . '</td></tr>'."\n";

	echo '						<tr class="obscuro" style="height:40px;"><td colspan="3" style="text-align: center; font-size:20px; font-weight:bold;">' . $cumfp . '% en Cumplimiento de Fecha Promesa</td>';
	echo '</tr>'."\n";

	echo '					</table>'."\n";
	echo '				</td><td colspan="1"></td><td colspan="7" style="vertical-align:top;" width="60%">'."\n";
	echo '					<table width="100%">'."\n";
	echo '						<tr class="encabezados" style="text-align: center;"><td colspan="7"></td></tr>'."\n";
	$dvrr = array(
		array(utf8_decode('- 4 días'),$dv[1], $do[1]),
		array(utf8_decode('4 a 6 días'),$dv[2], $do[2]),
		array(utf8_decode('7 a 10 días'),$dv[3], $do[3]),
		array(utf8_decode('11 a 12 días'),$dv[4], $do[4]),
		array(utf8_decode('+ 12 días'),$dv[5], $do[5]),
	);

	
	$ssdv = new PHPlot(600,250);
	$ssdv->SetFailureImage(False); // No error images
	$ssdv->SetPrintImage(False); // No automatic output
	$ssdv->SetImageBorderType('plain'); // Improves presentation in the manual
	$ssdv->SetPlotType('bars');
	$ssdv->SetDataType('text-data');
	$ssdv->SetDataValues($dvrr);
	$ssdv->SetTitle(utf8_decode('Distribución de Vehículos por Rango de Reparación'));
	$ssdv->SetLegend(array('Terminados', 'Objetivo'));
//	$ssdv->SetYTitle(utf8_decode('Dist. de reparaciones %'));
//	$ssdv->SetXTitle(utf8_decode('Rangos de reparación'));
//	$ssdv->SetYDataLabelPos('plotin');
//	$ssdv->SetYTickLabelPos('none');
	$ssdv->SetYTickPos('none');
	$ssdv->SetDrawYGrid(False);
	$ssdv->SetXTickPos('none');
	$ssdv->SetXTickLabelPos('none');

//	$ssdv->SetPieLabelType(array('label','value'), 'custom', 'dinero');
# Place the legend in the upper left corner:
	$ssdv->SetLegendPixels(30,25);
	$ssdv->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$ssdv->DrawGraph();
	
	echo '						<tr><td><img src="' . $ssdv->EncodeImage() . '" alt="gráficas"></td></tr>'."\n";
	echo '					</table></td></tr>'."\n";
	echo '				<tr><td colspan="11" style="height:25px;"></td></tr>'."\n";


	echo '			</table>'."\n";

	$fondo = 'claro';
	$j = 0;

?>
			</div>
		</div>

<?php include('parciales/pie.php'); 
/* Archivo index.php */
/* AutoShop Easy */