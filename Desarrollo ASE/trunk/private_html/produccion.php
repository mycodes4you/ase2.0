<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
} else {
	include('idiomas/' . $idioma . '/produccion.php');
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php'); 
	echo '			<div id="principal"><br>
';
}
	$funnum = 1100000;
	
	echo '			<table cellspacing="0" cellpadding="0" border="0" class="izquierda"><tr><td>';
	$pregunta = "SELECT usuario, nombre, apellidos, inicio, fin, comida FROM " . $dbpfx . "usuarios WHERE acceso = '0' AND rol09 = '1' AND activo = '1' ORDER BY nombre,apellidos";
//	echo $pregunta;
   $matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion de usuarios!");
	$num_cols = mysql_num_rows($matriz);
//	echo $num_cols;
	$hoy = date('w'); 
	if($hoy==6) { 
		$ma = mktime(0, 0, 0, date("m")  , date("d")+2, date("Y")); 
	} else { 
		$ma = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")); 
	}
	$hora = date('H') - 7; $minuto = date('i');
	$tick = ($hora * 60) + $minuto;
//	$prueba = round(9.46); echo $prueba;
//	echo $tick . '-' . $hora . '-' . $minuto . '<br>'; 
	$hoy = date('Y-m-d'); $ma = date('Y-m-d', $ma);
	if ($num_cols>0) {
		echo '			<table cellspacing="0" cellpadding="0" border="1" class="centrado">
				<tr><td colspan="29">Control de Proceso Express del día ' . $hoy . '</td></tr>'."\n";
		echo '				<tr><td colspan="2">Operador</td><td colspan="2">7</td><td colspan="2">8</td><td colspan="2">9</td><td colspan="2">10</td><td colspan="2">11</td><td colspan="2">12</td><td colspan="2">13</td><td colspan="2">14</td><td colspan="2">15</td><td colspan="2">16</td><td colspan="2">17</td><td colspan="2">18</td><td colspan="2">19</td><td>Ext</td></tr>'."\n";
		while($usr = mysql_fetch_array($matriz)) {
			echo '				<tr><td colspan="29" style="height:10px;"></td></tr>'."\n";
			echo '				<tr class="claro"><td rowspan="2" style="text-align:left;">' . $usr['nombre'] . ' ' . $usr['apellidos'] . '</td><td>HOY:</td>';
			$preg0 = "SELECT * FROM " . $dbpfx . "agenda WHERE operador = '" . $usr['usuario'] . "' AND fecha_inicio = '" . $hoy . "' ORDER BY seg_inicio";
//			echo $preg0;
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de subordenes!");
			$num_tareas = mysql_num_rows($matr0);

/*       ===================== Posibilidad de mostrar únicamente los horarios laborales de cada Operador   ==================  

			if($usr['inicio'] > 1) {
				echo '<td colspan="'; echo $usr['inicio'] - 1; echo '" style="width:'; echo ($usr['inicio'] -1) * 25; echo 'px;"></td>';
			}
			$segmentos = $usr['inicio'];
*/
			$segmentos = 1;
			if($num_tareas > 0) {
				while($tarea = mysql_fetch_array($matr0)) {
					$preg2 = "SELECT sub_estatus, sub_refacciones_recibidas FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $tarea['sub_orden_id'] . "'";
					$matr2 = mysql_query($preg2) or die("ERROR: Fallo seleccion de suborden!");
					$estat = mysql_fetch_array($matr2);
					$dura = $tarea['seg_inicio'] + $tarea['segmentos'];
					$tarea_ini = ($tarea['seg_inicio'] - 1) * 30; 
					$duramin = $tarea['segmentos'] * 30;
					$dura1de4 = round($duramin * 0.25);
					$duramedia = round($duramin / 2);
					$dura3de4 = round($duramin * 0.75);
//					echo 'Tick: ' . $tick . ' Inicio: ' . $tarea_ini . ' Duración: ' . $duramin . ' Dura 1 de 4: ' . $dura1de4 . ' Duración media: ' . $duramedia . ' Duración 3 de 4: ' . $dura3de4 . '<br>';
					if($estat[0]=='104' && $estat['1'] > 0) { $alarma = 'alarma_refacciones';}
					elseif(($estat[0]=='104' || $estat[0]=='122') && $tick >= $tarea_ini && $tick < ($tarea_ini + $dura1de4)) { $alarma = 'alarma_preventiva';}
					elseif(($estat[0]=='104' || $estat[0]=='122') && $tick >= ($tarea_ini + $dura1de4)) { $alarma = 'alarma_critica';}
					elseif($estat[0]=='105' && $tick < $tarea_ini) { $alarma = 'alarma_refacciones'; }
					elseif($estat[0]=='105' && $tick >= $tarea_ini) { $alarma = 'alarma_refacciones'; }
					elseif($estat[0]=='106' && $tick >= $tarea_ini && $tick < ($tarea_ini + $dura1de4)) { $alarma = 'alarma_preventiva'; }
					elseif($estat[0]=='106' && $tick >= ($tarea_ini + $dura1de4)) { $alarma = 'alarma_critica';}
					elseif($estat[0]=='107') { $alarma = 'alarma_critica'; }
					elseif(($estat[0]=='108' || $estat[0]=='124') && $tick >= $tarea_ini && $tick < ($tarea_ini + $duramedia)) { $alarma = 'alarma_preventiva'; }
					elseif(($estat[0]=='108' || $estat[0]=='124') && $tick >= ($tarea_ini + $duramedia)) { $alarma = 'alarma_critica';}
					elseif(($estat[0]=='109' || $estat[0]=='110' || $estat[0]=='125' || $estat[0]=='126') && $tick < $tarea_ini) { $alarma = 'alarma_normal'; }
					elseif(($estat[0]=='109' || $estat[0]=='110' || $estat[0]=='125' || $estat[0]=='126') && $tick >= $tarea_ini && $tick < ($tarea_ini + $dura3de4)) { $alarma = 'alarma_normal'; }
					elseif(($estat[0]=='109' || $estat[0]=='110' || $estat[0]=='125' || $estat[0]=='126') && $tick >= ($tarea_ini + $dura3de4) && $tick < ($tarea_ini + $duramin)) { $alarma = 'alarma_preventiva';}
					elseif(($estat[0]=='109' || $estat[0]=='110' || $estat[0]=='125' || $estat[0]=='126') && $tick >= ($tarea_ini + $duramin)) { $alarma = 'alarma_critica';}
					
					elseif(($estat[0]=='111' || $estat[0]=='127') && $tick < ($tarea_ini + $dura3de4)) { $alarma = 'alarma_concluido'; }
					elseif(($estat[0]=='111' || $estat[0]=='127') && $tick >= ($tarea_ini + $dura3de4) && $tick < ($tarea_ini + $duramin)) { $alarma = 'alarma_preventiva';}
					elseif(($estat[0]=='111' || $estat[0]=='127') && $tick >= ($tarea_ini + $duramin)) { $alarma = 'alarma_critica';}
					elseif($estat[0]=='111') { $alarma = 'alarma_concluido'; }
					elseif($estat[0]=='112' || $estat[0]=='128') { $alarma = 'alarma_terminado'; }
					else { $alarma = 'alarma_neutral'; }
/*					if(($segmentos + $dura) > 27) {
						echo '<td colspan="'; echo 27 - $segmentos; echo '" class="' . $alarma . '">Más Tareas<br>para un<br>día</td>';
						break;
					}
*/					if($tarea['seg_inicio'] > $segmentos) {
						for($i=$segmentos;$tarea['seg_inicio'] > $i;$i++) {
							echo '<td style="width:25px;"></td>';
							$segmentos++;
						}
					}
					
					if($tarea['seg_inicio'] < $usr['comida'] && $dura > $usr['comida']) {
						echo '<td colspan="'; echo $tarea['segmentos'] + 2; echo '" class="' . $alarma . '">'; $segmentos = $segmentos + 2;}
					elseif($tarea['seg_inicio'] == $usr['comida']) {
						echo '<td colspan="2" style="width:50px;"></td>'; echo '<td colspan="'; echo $tarea['segmentos']; echo '" class="' . $alarma . '">'; $segmentos = $segmentos + 2;}
					elseif($tarea['seg_inicio'] == ($usr['comida'] + 1)) {
						echo '<td style="width:25px;"></td>'; echo '<td colspan="'; echo $tarea['segmentos']; echo '" class="' . $alarma . '">'; $segmentos = $segmentos + 1;}
					else { echo '<td colspan="'; echo $tarea['segmentos']; echo '" class="' . $alarma . '">';}
					
					$preg1 = "SELECT orden_vehiculo_placas, orden_vehiculo_tipo, orden_vehiculo_color, orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $tarea['orden_id'] . "'";
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion de orden!");
					$alerta = mysql_fetch_array($matr1);
					echo '<table cellspacing="0" cellpadding="0" border="0">';
					echo '<tr><td style="text-align:left;font-size:9px;"><a href="proceso.php?accion=consultar&orden_id=' . $tarea['orden_id'] . '#' . $tarea['sub_orden_id'] . '" style=" display:block;">' . $alerta['orden_vehiculo_placas'] . '</a></td></tr>';
					echo '<tr><td style="text-align:left;font-size:9px;">' . strtoupper($alerta['orden_vehiculo_tipo']) . ' ' . strtoupper($alerta['orden_vehiculo_color']) . '</td></tr><!-- <tr><td colspan="2" style="text-align:center; font-size:9px;">' . strtoupper(constant('ORDEN_ESTATUS_' . $alerta['orden_estatus'])) . '</td></tr> --></table>';
					echo '</td>';
					$segmentos = $segmentos + $tarea['segmentos'];
				}
				if($segmentos < 27) {
					for($i=$segmentos; 27 > $i; $i++) {
						if($usr['comida']==$i) { 
							echo '<td colspan="2" style="width:50px;"></td>';
							$i++; 
						} else {
							echo '<td style="width:25px;"></td>';
						}
					}
				}
			} else {
/*    ========== Posibilidad de mostrar  únicamente los horarios laborales de cada Operador   ==========
				for($i=$usr['inicio']; $usr['fin'] >= $i; $i++) {
*/
				for($i=1; 26 >= $i; $i++) {
					if($usr['comida']==$i) { 
						echo '<td colspan="2" style="width:50px;"></td>';
						$i++; 
					} else {
						echo '<td style="width:25px;"></td>';
					}
				}

/*    ========== Posibilidad de mostrar  únicamente los horarios laborales de cada Operador   ==========
				if($usr['fin'] < 28) {
						echo '<td colspan="'; echo 28 - $usr['fin']; echo '" style="width:'; echo (28 - $usr['fin']) * 25; echo 'px;"></td>';
				}
*/
			}
			echo '</tr>'."\n";
			echo '				<tr class="obscuro"><td>MAÑANA:</td>';
			$preg0 = "SELECT * FROM " . $dbpfx . "agenda WHERE operador = '" . $usr['usuario'] . "' AND fecha_inicio = '" . $ma . "' ORDER BY seg_inicio";
//			echo $preg0;
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de agenda mañana!");
			$num_tareas = mysql_num_rows($matr0);
/*       ===================== Posibilidad de mostrar únicamente los horarios laborales de cada Operador   ==================  

			if($usr['inicio'] > 1) {
				echo '<td colspan="'; echo $usr['inicio'] - 1; echo '" style="width:'; echo ($usr['inicio'] -1) * 25; echo 'px;"></td>';
			}
			$segmentos = $usr['inicio'];
*/
			$segmentos = 1;
			if($num_tareas > 0) {
				while($tarea = mysql_fetch_array($matr0)) {
//					echo $tarea['sub_horas_programadas'];
					$alarma = 'alarma_neutral';
					$dura = $tarea['seg_inicio'] + $tarea['segmentos'];
					$preg2 = "SELECT sub_estatus FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $tarea['sub_orden_id'] . "'";
					$matr2 = mysql_query($preg2) or die("ERROR: Fallo seleccion de suborden!");
					$estat = mysql_fetch_array($matr2);
					if($estat[0]=='105') { $alarma = 'alarma_refacciones'; }
					if(($segmentos + $tarea['segmentos']) > 27) {
						echo '<td colspan="'; echo 27 - $segmentos; echo '" class="' . $alarma . '">Más Tareas<br>para un<br>día</td>';
						break;
					}
					if($tarea['seg_inicio'] > $segmentos) {
						for($i=$segmentos;$tarea['seg_inicio'] > $i;$i++) {
							echo '<td style="width:25px;"></td>';
							$segmentos++;
						}
					}
					if($tarea['seg_inicio'] < $usr['comida'] && $dura > $usr['comida']) {
						echo '<td colspan="'; echo $tarea['segmentos'] + 2; echo '" class="' . $alarma . '">'; $segmentos = $segmentos + 2;}
					elseif($tarea['seg_inicio'] == $usr['comida']) {
						echo '<td colspan="2" style="width:50px;"></td>'; echo '<td colspan="'; echo $tarea['segmentos']; echo '" class="' . $alarma . '">'; $segmentos = $segmentos + 2;}
					elseif($tarea['seg_inicio'] == ($usr['comida'] + 1)) {
						echo '<td style="width:25px;"></td>'; echo '<td colspan="'; echo $tarea['segmentos']; echo '" class="' . $alarma . '">'; $segmentos = $segmentos + 1;}
					else { echo '<td colspan="'; echo $tarea['segmentos']; echo '" class="' . $alarma . '">';}
					$preg1 = "SELECT orden_vehiculo_placas, orden_vehiculo_tipo, orden_vehiculo_color, orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $tarea['orden_id'] . "'";
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion de orden!");
					$alerta = mysql_fetch_array($matr1);
					echo '<table cellspacing="0" cellpadding="0" border="0">';
					echo '<tr><td style="text-align:left; font-size:9px;"><a href="proceso.php?accion=consultar&orden_id=' . $tarea['orden_id'] . '#' . $tarea['sub_orden_id'] . '" style=" display:block;">' . $alerta['orden_vehiculo_placas'] . '</a></td></tr>';
					echo '<tr><td style="text-align:left; font-size:9px;">' . strtoupper($alerta['orden_vehiculo_tipo']) . ' ' . strtoupper($alerta['orden_vehiculo_color']) . '</td></tr><!-- <tr><td colspan="2" style="text-align:left; font-size:9px;">' . strtoupper(constant('ORDEN_ESTATUS_' . $alerta['orden_estatus'])) . '</td></tr> --></table>';
					echo '</td>';
					$segmentos = $segmentos + $tarea['segmentos'];
				}
				if($segmentos < 27) {
					for($i=$segmentos; 27 > $i; $i++) {
						if($usr['comida']==$i) { 
							echo '<td colspan="2" style="width:50px;"></td>';
							$i++; 
						} else {
							echo '<td style="width:25px;"></td>';
						}
					}
				}
			} else {
/*    ========== Posibilidad de mostrar  únicamente los horarios laborales de cada Operador   ==========
				for($i=$usr['inicio']; $usr['fin'] >= $i; $i++) {
*/
				for($i=1; 26 >= $i; $i++) {
					if($usr['comida']==$i) { 
						echo '<td colspan="2" style="width:50px;"></td>';
						$i++; 
					} else {
						echo '<td style="width:25px;"></td>';
					}
				}

/*    ========== Posibilidad de mostrar  únicamente los horarios laborales de cada Operador   ==========
				if($usr['fin'] < 28) {
						echo '<td colspan="'; echo 28 - $usr['fin']; echo '" style="width:'; echo (28 - $usr['fin']) * 25; echo 'px;"></td>';
				}
*/
			}
			echo '				</tr>'."\n";
		}
		echo '				</table>'."\n";
	}
	echo '			</td><td>';
	echo '				<table cellspacing="0" cellpadding="0" border="0" class="izquierda">
					<tr><td>Color, Estatus y Tiempos</td></tr>
					<tr><td class="alarma_neutral">Por iniciar sin Contratiempos</td></tr>
					<tr><td class="alarma_normal">Ejecutandose sin Contratiempos</td></tr>
					<tr><td class="alarma_preventiva">Sin terminar Rezagada</td></tr>
					<tr><td class="alarma_refacciones">Esperando Refacciones</td></tr>
					<tr><td class="alarma_critica">Sin terminar Tiempo Vencido</td></tr>
					<tr><td class="alarma_concluido">Concluida esperando aprobación</td></tr>
					<tr><td class="alarma_terminado">Tarea terminada y aprobada</td></tr>
					';
	echo '				</table>'."\n";
	echo '			</td></tr></table>';
?>
			</div>
		</div>
<?php include('parciales/pie.php'); 
/* Archivo index.php */
/* AutoShop Easy */