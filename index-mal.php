<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

if($_SESSION['codigo'] == '75') {
	redirigir('ordenes-de-trabajo.php');
}
/*
$j=0;
for($i=1;$i<10;$i++) {
	$rol = 'rol0' . $i;
	if($_SESSION[$rol]==1) {
		$j++;
	}
	if ($j>1) {
		redirigir('monitoreo.php');
	}
}
*/
include('idiomas/' . $idioma . '/index.php');
include('parciales/encabezado.php');
echo '	<div id="body">';
include('parciales/menu_inicio.php'); 
echo '			<div id="principal">'."\n";


//	print_r($etapa);
	
/*  ----------------  obtener nombres de aseguradoras   ------------------- */
	
		$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_nic, prov_def, prov_dde FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		$ase[0] = "Particular";
		$aselogo[0] = "imagenes/particular.jpg";
		while ($aseg = mysql_fetch_array($arreglo)) {
			$ase[$aseg['aseguradora_id']] = $aseg['aseguradora_nic'];
			$aselogo[$aseg['aseguradora_id']] = $aseg['aseguradora_logo'];
//			define('ASEGURADORA_NIC_' . $aseg['aseguradora_id'], $aseg['aseguradora_nic']);
			$autosurt[$aseg['aseguradora_id']] = $aseg['autosurtido'];
			$prov_def[$aseg['aseguradora_id']] = $aseg['prov_def'];
			$prov_dde[$aseg['aseguradora_id']] = $aseg['prov_dde'];
		}
/*  ----------------  nombres de aseguradoras   ------------------- */

if(validaAcceso('1055000', $dbpfx) == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol04']=='1') {
	$treintadias = (strtotime(date('Y-m-d')) - 2592000); 
	$estemes = date('n'); $year = date('Y');
	for($j=0;$j<4;$j++) {
		$elmes = $estemes - $j;
		if($elmes < 1) {$elmes = $elmes + 12; $year = $year -1;}
		if($elmes == 1) { $etiqmes[$j] = 'ENE'; }
		if($elmes == 2) { $etiqmes[$j] = 'FEB'; }
		if($elmes == 3) { $etiqmes[$j] = 'MAR'; }
		if($elmes == 4) { $etiqmes[$j] = 'ABR'; }
		if($elmes == 5) { $etiqmes[$j] = 'MAY'; }
		if($elmes == 6) { $etiqmes[$j] = 'JUN'; }
		if($elmes == 7) { $etiqmes[$j] = 'JUL'; }
		if($elmes == 8) { $etiqmes[$j] = 'AGO'; }
		if($elmes == 9) { $etiqmes[$j] = 'SEP'; }
		if($elmes == 10) { $etiqmes[$j] = 'OCT'; }
		if($elmes == 11) { $etiqmes[$j] = 'NOV'; }
		if($elmes == 12) { $etiqmes[$j] = 'DIC'; }
		$fini[$j] = $year.'-'.$elmes.'-01 00:00:00';
		$ffin[$j] = date('Y-m-t 23:59:59', strtotime($fini[$j]));
		if($j == '3') { $feini = $year.'-'.$elmes.'-01 00:00:00'; }
		$fefin = date('Y-m-t 23:59:59', time());
	}

	$preg4 = "SELECT f.orden_id, f.fact_cobrada, o.orden_fecha_de_entrega, o.orden_estatus FROM " . $dbpfx . "facturas_por_cobrar f, " . $dbpfx . "ordenes o WHERE f.fact_cobrada < 2 AND f.fact_tipo < 3 AND f.orden_id = o.orden_id AND o.orden_fecha_de_entrega >= '$feini'";
	$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de facturas! ".$preg4);
//	echo $preg4;
	while($fact = mysql_fetch_array($matr4)) {
		$mes = quemes($fact['orden_fecha_de_entrega']);
		if($fact['fact_cobrada'] == '1') {
			$ofc[$mes]++;
		} else {
			$ofsc[$mes]++;
		}
	}
		 
	$pregunta = "SELECT orden_id, orden_estatus, orden_ref_pendientes, orden_ubicacion, orden_fecha_recepcion, orden_fecha_promesa_de_entrega, orden_fecha_de_entrega, orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		
	$ubics = count($ubicaciones);
	while($ord = mysql_fetch_array($matriz)) {
		$ds = dias($ord['orden_fecha_recepcion']);
		$mes = quemes($ord['orden_fecha_recepcion']);
		
		if($ord['orden_ubicacion']=='Transito' && $ord['orden_estatus'] < 90) {
			if($ord['orden_estatus'] == '4') {
				$otrit[$ds]++;
			} else {
				$ot[$ord['orden_estatus']][$ds]++;
			}
			$otmf[$mes]++;
			/*echo $sem . ' -> ' . $ord['orden_id'] . ' | ';*/ 
		} elseif(is_array($ubicaciones) && $cambubic == 1) {
			$ot[$ord['orden_estatus']][$ds]++;
			$positivo = 0; // -- Variable utilizada para almacenar si el vehiculo está en alguna de las ubicaciones alternas del taller 
			for($i=1; $i < $ubics; $i++) {
				if($ord['orden_ubicacion'] == $ubicaciones[$i] && $ord['orden_estatus'] < 90) {
					$otubc[$i][$mes]++;
					$positivo = 1;
				}
			}
			if($positivo == 0 && $ord['orden_estatus'] < 90) {
				$otm[$mes]++;
			}
		} elseif($ord['orden_estatus'] < 90) {
			$ot[$ord['orden_estatus']][$ds]++;
			$otm[$mes]++;
		}

		if($ord['orden_estatus'] != 90) {
			$to[$mes]++;
		}

		if($ord['orden_estatus'] == 90) {
			$ote[$ord['orden_estatus']][$mes]++;
		}

		if($ord['orden_estatus'] >= 30 && $ord['orden_estatus'] <= 39) {
			$otsr[$ord['orden_estatus']][$mes]++;
		}

		if($ord['orden_ref_pendientes'] > '0') {
			$otrp[$ds]++;
		}

/*		for($i=1;$i<=$num_areas_servicio;$i++) {
			if($ord['orden_ubicacion'] == constant('NOMBRE_AREA_'.$i)) {
				$tr[$i][$ds]++;
			}
		}
*/

		$pregunta2 = "SELECT sub_estatus, sub_area FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección! " . $pregunta2);
		while($sub = mysql_fetch_array($matriz2)) {
			if($sub['sub_estatus'] < '112' && $sub['sub_estatus'] > '106') {
				$tr[$sub['sub_area']][$ds]++;
//				echo 'Orden: ' . $ord['orden_id'] . ' -> Subestatus: ' . $sub['sub_estatus'] . '<br>';
			}
/*			if($sub['sub_refacciones_recibidas'] > 0) {
				$rp[$sub['sub_area']][$sem]++;
			}
*/		}

//		if($mes > 2) {$mes=3;}

		// ---- Recorrer las tareas de la orden en curso ----
		$preg3 = "SELECT s.sub_estatus, s.sub_aseguradora, s.sub_reporte FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE o.orden_id = '" . $ord['orden_id'] . "' AND s.sub_estatus < '190' AND o.orden_id = s.orden_id GROUP BY s.sub_reporte";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo seleccion! " . $preg3);
		while ($sub2 = mysql_fetch_array($matr3)) {
			
			$mes = quemes($ord['orden_fecha_recepcion']);
			$asegu[$sub2['sub_aseguradora']][$mes]++; 
			$mese = quemes($ord['orden_fecha_de_entrega']);
			
			if($ord['orden_fecha_de_entrega'] != '' && ($ord['orden_estatus'] == 99 || $ord['orden_estatus'] <= 29)){
				$aseen[$sub2['sub_aseguradora']][$mese]++;
			}
			

			// ---- Recorrer las facturas y recibos dentro de las tareas ----
			$preg4 = "SELECT fact_id, recibo_id, sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus >= '112' AND sub_estatus <= '115' AND sub_reporte = '" . $sub2['sub_reporte'] . "' AND sub_presupuesto > '0' ";
			$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de tareas sin facturar o sin destajos! " . $preg4);
			while ($finan = mysql_fetch_array($matr4)) {
				
				if(is_null($finan['fact_id'])) {

					// ---- Almacenar el reporte o trabajo particular de la orden ----
					$trab_no_fact[$ord['orden_id'].$sub2['sub_reporte']] = 1;
					//echo 'orden : ' . $ord['orden_id'] . ', suborden por facturar ' . $finan['sub_orden_id'] . ' del mes ' . $mese . '<br>';	
					
				}
				if(is_null($finan['recibo_id'])) {
					$osrd[$mese]++;
				}
			}
			
			// ---- Sumar los trabajos al mes actual ----
			$osf[$mese] = $osf[$mese] + count($trab_no_fact);
			unset($trab_no_fact);

		}

	}

// --------- Bloque de documentación ------------------
	
	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;" rowspan="2">' . PANEL_CONTROL_1 . '</td>
								<td width="10%" colspan="4">'. $lang['Días'].'</td>
							</tr>
							<tr class="cabeza_tabla">
								<td width="10%">1</td>
								<td width="10%">2 a 3</td>
								<td width="10%">4 a 6</td>
								<td width="10%">7 o +</td>
							</tr>'."\n";
	
	foreach($etapa[1] as $k => $v) {
//		if($ot[$v][3]!='' || $ot[$v][4]!='') { $ot[$v][3] = $ot[$v][3] + $ot[$v][4]; }
		$ot[$v][3] = $ot[$v][3] + $ot[$v][4] + $ot[$v][5];
		if($ot[$v][3] == '0') { $ot[$v][3] = '';}
		echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id='.$v.'&n=t">' . constant('ORDEN_ESTATUS_'.$v) . '</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=0">'. $ot[$v][0] .'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=1">'.$ot[$v][1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=2">'.$ot[$v][2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=d">'.$ot[$v][3].'</a>
								</td>
							</tr>'."\n";
	}

	echo '						
						</table>
					</div>'."\n";

// --------- Bloque de Reparación ------------------
	
	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;" rowspan="2">' . PANEL_CONTROL_2 . '</td>
								<td width="10%" colspan="4">' . $lang['Días'] . '</td>
							</tr>
							<tr class="cabeza_tabla">
								<td width="10%">1 a 6</td>
								<td width="10%">7 a 12</td>
								<td width="10%">13 a 19</td>
								<td width="10%">20 o +</td>
							</tr>'."\n";

//		if($otrit[3]!='' || $otrit[4]!='') { $otrit[3] = $otrit[3] + $otrit[4]; }
		$otrit[2] = $otrit[0] + $otrit[1] + $otrit[2];
		if($otrit[2] == '0') { $otrit[2] = ''; }
		echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&rit=1&n=t">'. $lang['OTs en Transito estado 4'].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&rit=1&n=a">'.$otrit[2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&rit=1&n=3">'.$otrit[3].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&rit=1&n=4">'.$otrit[4].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&rit=1&n=5">'.$otrit[5].'</a>
								</td>
							</tr>'."\n";

	foreach($etapa[2] as $k => $v) {
//		if($ot[$v][3]!='' || $ot[$v][4]!='') { $ot[$v][3] = $ot[$v][3] + $ot[$v][4]; }
		$ot[$v][2] = $ot[$v][0] + $ot[$v][1] + $ot[$v][2];
		if($ot[$v][2] == '0') { $ot[$v][2] = '';}
		echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id='.$v.'&n=t">' . constant('ORDEN_ESTATUS_'.$v) . '</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=a">'. $ot[$v][2] .'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=3">'.$ot[$v][3].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=4">'.$ot[$v][4].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=5">'.$ot[$v][5].'</a>
								</td>
							</tr>'."\n";

	}
//	print_r($otrp);
//		if($otrp[3]!='' || $otrp[4]!='') { $otrp[3] = $otrp[3] + $otrp[4]; }
		$otrp[2] = $otrp[0] + $otrp[1] + $otrp[2];
		if($otrp[2] == '0') { $otrp[2] = ''; }
		echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&pp=1&n=t">'. $lang['Ordenes con Refacc Pend'].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&pp=1&n=a">'.$otrp[2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&pp=1&n=3">'.$otrp[3].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&pp=1&n=4">'.$otrp[4].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&pp=1&n=5">'.$otrp[5].'</a>
								</td>
							</tr>
						</table>
					</div>'."\n";

// --------- Bloque de Mensajes Internos ------------------
	
	if($mensjint == '1') {
	echo '			<div style="float:left; width:240px; background-color: #c9c9c9; margin-top: 3px; margin-right: 3px; margin-bottom: 4px;">'."\n";
// ---------------  Mensajes Internos -----------------
      		echo '				<form action="comentarios.php?accion=visto" method="post" enctype="multipart/form-data">'."\n";
      		echo '				<table cellspacing="0" cellpadding="2" border="1" width="100%">
				<tr><td style="text-align:left;">Tus mensajes no leidos:<br>'."\n";
				$pregc1 = "SELECT c.orden_id, c.bit_id, c.fecha_com, c.usuario, c.comentario, c.interno, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.usuario = u.usuario AND c.interno = '3' AND c.para_usuario = '" . $_SESSION['usuario'] . "' AND fecha_visto IS NULL ORDER BY c.bit_id DESC";
				$matrc1 = mysql_query($pregc1) or die("ERROR: Fallo selección de comentarios! " . $pregc1);
				$j=0; $fondo='claro';
				while($comen = mysql_fetch_array($matrc1)) {
					echo '				<p class="' . $fondo . '" style="margin-top:0px; padding-left:3px; padding-right:3px;">'."\n";
					echo '				<a href="ordenes.php?accion=consultar&orden_id=' . $comen['orden_id'] . '" target="_blank">Mensaje en la OT ' . $comen['orden_id'] . '</a><br>'."\n";
					echo '				El ' . $comen['fecha_com'] . ' de ' . $comen['nombre'] . ' ' . $comen['apellidos'] . '<br>'."\n";
					echo '				' . $comen['comentario']. '<br>'."\n";
					echo '				<button name="visto" value="' . $comen['bit_id'] . '" type="submit">Visto</button>'."\n";
					echo '				<input type="hidden" name="orden_id" value="' . $comen['orden_id'] . '" />
				<input type="hidden" name="pagina" value="index.php" />'."\n";
					echo '				</p>'."\n";				
					$j++;
					if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
				}
				echo '				</td></tr></table></form>'."\n";
	echo '			</div>'."\n";
	}

// --------- Bloque de Vehículos en Proceso ------------------
	
	echo '			<div style="clear: both;"></div><div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;" rowspan="2">' . PANEL_CONTROL_3 . '</td>
								<td width="10%" colspan="4">'. $lang['Días'].'</td>
							</tr>
							<tr class="cabeza_tabla">
								<td width="10%">1 a 6</td>
								<td width="10%">7 a 12</td>
								<td width="10%">13 a 19</td>
								<td width="10%">20 o +</td>
							</tr>'."\n";
	foreach($tr as $k => $v) {
		$tr[$k][2] = $tr[$k][0] + $tr[$k][1] + $tr[$k][2];
		if($tr[$k][2] == '0') { $tr[$k][2] = ''; }
		echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&area='.$k.'&n=t">' . constant('NOMBRE_AREA_'.$k) . '</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&area='.$k.'&n=a">'.$tr[$k][2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&area='.$k.'&n=3">'.$tr[$k][3].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&area='.$k.'&n=4">'.$tr[$k][4].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&area='.$k.'&n=5">'.$tr[$k][5].'</a>
								</td>
							</tr>'."\n";

	}
	echo '						
						</table>
					</div>'."\n";

// --------- Bloque de Terminados ------------------
	
	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;" rowspan="2">'. $lang['Ordenes en Etapa de Entrega'].'</td>
								<td width="10%" colspan="4">'. $lang['Días'].'</td>
							</tr>
							<tr class="cabeza_tabla">
								<td width="10%">1 a 6</td>
								<td width="10%">7 a 12</td>
								<td width="10%">13 a 19</td>
								<td width="10%">20 o +</td>
							</tr>'."\n";
	foreach($etapa[3] as $k => $v) {
//		if($ot[$v][3]!='' || $ot[$v][4]!='') { $ot[$v][3] = $ot[$v][3] + $ot[$v][4]; }
		$ot[$v][2] = $ot[$v][0] + $ot[$v][1] + $ot[$v][2];
		if($ot[$v][2] == '0') { $ot[$v][2] = ''; }
		echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id='.$v.'&n=t">' . constant('ORDEN_ESTATUS_'.$v) . '</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=a">'. $ot[$v][2] .'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=3">'.$ot[$v][3].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=4">'.$ot[$v][4].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$v.'&n=5">'.$ot[$v][5].'</a>
								</td>
							</tr>'."\n";
	}

	echo '						
						</table>
					</div>'."\n";

// --------- Bloque de Facturables ------------------
	
	echo '			<div style="clear: both;"></div><div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">'. $lang['Entregas Facturables por Mes'].'</td>
								<td width="10%">'.$etiqmes[0].'</td>
								<td width="10%">'.$etiqmes[1].'</td>
								<td width="10%">'.$etiqmes[2].'</td>
								<td width="10%">'. $lang['Meses Pas'].'</td>
							</tr>'."\n";
//	if($ofc[3]!='' || $ofc[4]!='') { $ofc[3] = $ofc[3] + $ofc[4]; }
	foreach($ofc as $kk => $vv) {
			if($kk < '4') {
				$ofc0[$kk] = $ofc0[$kk] + $vv;
			} else {
				$ofc0[3] = $ofc0[3] + $vv;
			}
	}
	echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=finanzas&estatusflt=4&feini='.$feini.'&fefin='.$fefin.'&ofc=1">' . $lang['Facturas cobradas'] . '</a>
								</td>
								<td>
									<a href="reportes.php?accion=finanzas&estatusflt=4&feini='.$fini[0].'&fefin='.$ffin[0].'&ofc=1">'.$ofc0[0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=finanzas&estatusflt=4&feini='.$fini[1].'&fefin='.$ffin[1].'&ofc=1">'.$ofc0[1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=finanzas&estatusflt=4&feini='.$fini[2].'&fefin='.$ffin[2].'&ofc=1">'.$ofc0[2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=finanzas&estatusflt=4&feini='.$fini[3].'&fefin='.$ffin[3].'&ofc=1">'.$ofc0[3].'</a>
								</td>
							</tr>'."\n";


	foreach($ofsc as $kk => $vv) {
		if($kk < '4') {
			$ofsc0[$kk] = $ofsc0[$kk] + $vv;
		} else {
			$ofsc0[3] = $ofsc0[3] + $vv;
		}
	}
	echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=finanzas&estatusflt=4&feini='.$feini.'&fefin='.$fefin.'&ofsc=1">' . $lang['Facturas sin cobrar'] . '</a>
								</td>
								<td>
									<a href="reportes.php?accion=finanzas&estatusflt=4&feini='.$fini[0].'&fefin='.$ffin[0].'&ofsc=1">'.$ofsc0[0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=finanzas&estatusflt=4&feini='.$fini[1].'&fefin='.$ffin[1].'&ofsc=1">'.$ofsc0[1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=finanzas&estatusflt=4&feini='.$fini[2].'&fefin='.$ffin[2].'&ofsc=1">'.$ofsc0[2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=finanzas&estatusflt=4&feini='.$fini[3].'&fefin='.$ffin[3].'&ofsc=1">'.$ofsc0[3].'</a>
								</td>
							</tr>'."\n";

	for($i=0;$i<4;$i++) {
		$aseent = 0;
		foreach($ase as $k => $v) {
			$aseent = $aseent + $aseen[$k][$i];
		}
		$osf[$i] = $aseent - ($ofc[$i]+$ofsc[$i]);
	}
	// ---- Pintar los trabajos pendientes de facturar ----
	echo '							
						<tr class="bloque_fila">
							<td style="text-align:left;">
								<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=finanzas&estatusflt=4&feini='.$feini.'&fefin='.$fefin.'">' . $lang['Tareas sin facturar'] . '</a>
							</td>
							<td>
								<a href="reportes.php?accion=finanzas&feini='.$fini[0].'&fefin='.$ffin[0].'&estatusflt=7">'.$osf0[0].'</a>
							</td>
							<td>
								<a href="reportes.php?accion=finanzas&feini='.$fini[1].'&fefin='.$ffin[1].'&estatusflt=7">'.$osf0[1].'</a>
							</td>
							<td>
								<a href="reportes.php?accion=finanzas&feini='.$fini[2].'&fefin='.$ffin[2].'&estatusflt=7">'.$osf0[2].'</a>
							</td>
							<td>
								<a href="reportes.php?accion=finanzas&feini='.$fini[3].'&fefin='.$ffin[3].'&estatusflt=7">'.$osf0[3].'</a>
							</td>
						</tr>'."\n";

	foreach($etapa[4] as $l => $w) {
//		if($ote[$v][3]!='' || $ote[$v][4]!='') { $ote[$v][3] = $ote[$v][3] + $ote[$v][4]; }
		foreach($ote as $k => $v) {
			if($k == $w) {
				foreach($v as $kk => $vv) {
					if($kk < '4') {
						$ote0[$w][$kk] = $ote0[$w][$kk] + $vv;
					} else {
						$ote0[$w][3] = $ote0[$w][3] + $vv;
					}
				}
			}
		}
		echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id='.$w.'&mes=t">' . constant('ORDEN_ESTATUS_'.$w) . '</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$w.'&mes=0">'.$ote0[$w][0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$w.'&mes=1">'.$ote0[$w][1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$w.'&mes=2">'.$ote0[$w][2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$w.'&mes=3">'.$ote0[$w][3].'</a>
								</td>
							</tr>'."\n";
	}

	echo '						
						</table>
					</div>'."\n";

// --------- Bloque de No Reparados ------------------
	
	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">' . PANEL_CONTROL_6 . '</td>
								<td width="10%">'.$etiqmes[0].'</td>
								<td width="10%">'.$etiqmes[1].'</td>
								<td width="10%">'.$etiqmes[2].'</td>
								<td width="10%">'. $lang['Meses Pas'].'</td>
							</tr>'."\n";
	foreach($otsr as $k => $v) {
		foreach($v as $kk => $vv) {
			if($kk < '4') { 
				$ottsr[$kk] = $ottsr[$kk] + $vv;
			} else {
				$ottsr[3] = $ottsr[3] + $vv;
			}
		}
	}
//	if($ottsr[3]!='' || $ottsr[4]!='') { $ottsr[3] = $ottsr[3] + $ottsr[4]; }
	echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&otsr=1&mfr=t">'. $lang['Total Sin Reparar por Entregar'].'</a>}
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otsr=1&mfr=0">'.$ottsr[0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otsr=1&mfr=1">'.$ottsr[1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otsr=1&mfr=2">'.$ottsr[2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otsr=1&mfr=3">'.$ottsr[3].'</a>
								</td>
							</tr>'."\n";

//	print_r($ote);
	foreach($etapa[6] as $l => $w) {
		foreach($ote as $k => $v) {
			if($k == $w) {
				foreach($v as $kk => $vv) {
					if($kk < '4') {
						$ote1[$w][$kk] = $ote1[$w][$kk] + $vv;
					} else {
						$ote1[$w][3] = $ote1[$w][3] + $vv;
					}
				}
			}
		}
		echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id='.$w.'&mfe=t">' . constant('ORDEN_ESTATUS_'.$w) . '</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$w.'&mfe=0">'.$ote1[$w][0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$w.'&mfe=1">'.$ote1[$w][1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$w.'&mfe=2">'.$ote1[$w][2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&id='.$w.'&mfe=3">'.$ote1[$w][3].'</a>
								</td>
							</tr>'."\n";

	}

	echo '						
						</table>
					</div>'."\n";

// --------- Bloque de Recibidos por Tipo ------------------
	
	echo '			<div style="clear: both;"></div><div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">' . PANEL_CONTROL_7 . '</td>
								<td width="10%">'.$etiqmes[0].'</td>
								<td width="10%">'.$etiqmes[1].'</td>
								<td width="10%">'.$etiqmes[2].'</td>
								<td width="10%">'.$etiqmes[3].'</td>
							</tr>'."\n";
//	print_r($asegu);
	foreach($ase as $k => $v) {
		$asegut = $asegu[$k][0] + $asegu[$k][1] + $asegu[$k][2] + $asegu[$k][3];
		echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=aseguradora&mfr=4&a='.$k.'">' . $v . ': '.$asegut.'</a>
								</td>
								<td>
									<a href="reportes.php?accion=aseguradora&mfr=0&a='.$k.'">'.$asegu[$k][0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=aseguradora&mfr=1&a='.$k.'">'.$asegu[$k][1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=aseguradora&mfr=2&a='.$k.'">'.$asegu[$k][2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=aseguradora&mfr=3&a='.$k.'">'.$asegu[$k][3].'</a>
								</td>
							</tr>'."\n";
	}
	echo '						
						</table>
					</div>'."\n";

// --------- Bloque de Entregados por Tipo ------------------
	
	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">' . PANEL_CONTROL_8 . '</td>
								<td width="10%">'.$etiqmes[0].'</td>
								<td width="10%">'.$etiqmes[1].'</td>
								<td width="10%">'.$etiqmes[2].'</td>
								<td width="10%">'.$etiqmes[3].'</td>
							</tr>'."\n";
	foreach($ase as $k => $v) {
//		print_r($aseen[$k]); echo '<br>';
		$aseent = $aseen[$k][0] + $aseen[$k][1] + $aseen[$k][2] + $aseen[$k][3];
		echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=cliente&mfe=4&a='.$k.'&tipo_reparacion=reparados">' . $v . ': '.$aseent.'</a>
								</td>
								<td>
									<a href="reportes.php?accion=cliente&mfe=0&a='.$k.'&tipo_reparacion=reparados">'.$aseen[$k][0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=cliente&mfe=1&a='.$k.'&tipo_reparacion=reparados">'.$aseen[$k][1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=cliente&mfe=2&a='.$k.'&tipo_reparacion=reparados">'.$aseen[$k][2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=cliente&mfe=3&a='.$k.'&tipo_reparacion=reparados">'.$aseen[$k][3].'</a>
								</td>
							</tr>'."\n";
//		echo '							<tr class="bloque_fila"><td style="text-align:left;">' . $v . ': '.$aseent.'</td><td>'.$aseen[$k][0].'</td><td>'.$aseen[$k][1].'</td><td>'.$aseen[$k][2].'</td><td>'.$aseen[$k][3].'</td></tr>'."\n";
	}
	echo '						
						</table>
					</div>'."\n";

// --------- Bloque de Ubicación ------------------
	
	echo '			<div style="clear: both;"></div><div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">' . PANEL_CONTROL_9 . '</td>
								<td width="10%">'.$etiqmes[0].'</td>
								<td width="10%">'.$etiqmes[1].'</td>
								<td width="10%">'.$etiqmes[2].'</td>
								<td width="10%">'. $lang['Meses Pas'].'</td>
							</tr>'."\n";
	
	foreach($otmf as $kk => $vv) {
		if($kk < '4') { 
			$ottf[$kk] = $ottf[$kk] + $otmf[$kk];
		} else {
			$ottf[3] = $ottf[3] + $otmf[$kk];
		}
	}

	$ottft = $ottf[0] + $ottf[1] + $ottf[2] + $ottf[3];

	echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&otmf=1&mfr=t">'. $lang['En Tránsito'].$ottft.'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otmf=1&mfr=0">'.$ottf[0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otmf=1&mfr=1">'.$ottf[1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otmf=1&mfr=2">'.$ottf[2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otmf=1&mfr=3">'.$ottf[3].'</a>
								</td>
							</tr>'."\n";
	
	foreach($otm as $k => $v) {
			if($k < '4') { 
				$ottm[$k] = $ottm[$k] + $otm[$k];
			} else {
				$ottm[3] = $ottm[3] + $otm[$k];
			}
	}

	$ottmt = $ottm[0] + $ottm[1] + $ottm[2] + $ottm[3];
	

	echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&otm=1&mfr=t">'. $lang['En Taller'].$ottmt.'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otm=1&mfr=0">'.$ottm[0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otm=1&mfr=1">'.$ottm[1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otm=1&mfr=2">'.$ottm[2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&otm=1&mfr=3">'.$ottm[3].'</a>
								</td>
							</tr>'."\n";
	
	if($cambubic == 1) {
			
		foreach($otubc as $k => $v) {
			foreach($v as $l => $w) {
				if($l < '4') { 
					$ottubc[$k][$l] = $ottubc[$k][$l] + $w;
				} else {
					$ottubc[$k][3] = $ottubc[$k][3] + $w;
				}
			}
			
			$ottubt[$k] = $ottubc[$k][0] + $ottubc[$k][1] + $ottubc[$k][2] + $ottubc[$k][3];
		}

		for($i=1; $i < $ubics; $i++) {
			if($ubicaciones[$i] != 'Transito') {
				echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&ubc='.$i.'&mfr=t">'.$ubicaciones[$i].': '.$ottubt[$i].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&ubc='.$i.'&mfr=0">'.$ottubc[$i][0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&ubc='.$i.'&mfr=1">'.$ottubc[$i][1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&ubc='.$i.'&mfr=2">'.$ottubc[$i][2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&ubc='.$i.'&mfr=3">'.$ottubc[$i][3].'</a>
								</td>
							</tr>'."\n";
			}
		}
	}
	
	for($i=0;$i<4;$i++) {
		$otp[$i] = $ottm[$i] + $ottf[$i];
	}
	if($cambubic == 1) {
		for($i=1; $i < $ubics; $i++) {
			for($j=0;$j<4;$j++) {
				$otp[$j] = $otp[$j] + $ottubc[$i][$j];
			}
		}
	}

	$toto = $otp[0] + $otp[1] + $otp[2] + $otp[3];
	echo '							
							<tr class="bloque_fila">
								<td style="text-align:left;">
									<a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&ottp=1&mfr=t">Totales: '.$toto.'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&ottp=1&mfr=0">'.$otp[0].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&ottp=1&mfr=1">'.$otp[1].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&ottp=1&mfr=2">'.$otp[2].'</a>
								</td>
								<td>
									<a href="reportes.php?accion=tabla&ottp=1&mfr=3">'.$otp[3].'</a>
								</td>
							</tr>'."\n";

	echo '						
						</table>
					</div>'."\n";

//	print_r($v);
	
} 

	$funnum = 1055005;

if ($_SESSION['codigo']!='70' && $_SESSION['codigo'] < '2000') {

// página default 

//	echo $num_cols;
		echo '				<div style="clear: both;"></div><table cellspacing="0" cellpadding="2" border="1" class="avisos" >'."\n";
		echo '					<tr>
						<td colspan="2" valign="top">';
		echo '							<form action="index.php?accion=selecciona" method="post" enctype="multipart/form-data" name="filtro"> 
								Por grupo: <select name="rol" size="0" onchange="document.filtro.submit()";>
											<option value="">'. $lang['Seleccione'].'</option>
											<option value="10">' . $codigos['10'][0] . '</option>
											<option value="12">' . $codigos['12'][0] . '</option>
											<option value="15">' . $codigos['15'][0] . '</option>
											<option value="20">' . $codigos['20'][0] . '</option>
											<option value="30">' . $codigos['30'][0] . '</option>
											<option value="40">' . $codigos['40'][0] . '</option>
											<option value="50">' . $codigos['50'][0] . '</option>
											<option value="60">' . $codigos['60'][0] . '</option>
											<option value="70">' . $codigos['70'][0] . '</option>
											<option value="80">' . $codigos['80'][0] . '</option>
										</select>&nbsp;&nbsp;'."\n";
		$preg0 = "SELECT al_estatus FROM " . $dbpfx . "alertas WHERE al_categoria = '4' AND al_sort < '999' ORDER BY al_sort ";
		$matr0 = mysql_query($preg0) or die($preg0);
		echo $lang['Por estatus'].'<select name="estatus" size="0" onchange="document.filtro.submit()";>
											<option value="">'. $lang['Seleccione'].'</option>'."\n";
		while($alest = mysql_fetch_array($matr0)) {
			echo '											<option value="' . $alest['al_estatus'] . '">' . constant('ORDEN_ESTATUS_'.$alest['al_estatus']) . '</option>'."\n";
		}
		echo '										</select>&nbsp;&nbsp;' . "\n";
		echo '								<input type="submit" name="enviar" value="'. $lang['Enviar'].'" />';
		echo '							</form>
						</td>'."\n";
				echo '					<td class="control" rowspan="5" style="vertical-align:top;">'."\n";

// ---------------  Mensajes Internos sin Panel de Control -----------------

			if(validaAcceso('1055000', $dbpfx) != '1' && $_SESSION['rol02'] !='1' && $_SESSION['rol04'] !='1' && $mensjint == '1') {
      		echo '				<form action="comentarios.php?accion=visto" method="post" enctype="multipart/form-data">'."\n";
      		echo '				<table cellspacing="0" cellpadding="2" border="0" width="100%">
				<tr><td style="text-align:left;">Tus mensajes no leidos:<br>'."\n";
				$pregc1 = "SELECT c.orden_id, c.bit_id, c.fecha_com, c.usuario, c.comentario, c.interno, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.usuario = u.usuario AND c.interno = '3' AND c.para_usuario = '" . $_SESSION['usuario'] . "' AND fecha_visto IS NULL ORDER BY c.bit_id DESC";
				$matrc1 = mysql_query($pregc1) or die("ERROR: Fallo selección de comentarios! " . $pregc1);
				$j=0; $fondo='claro';
				while($comen = mysql_fetch_array($matrc1)) {
					echo '				<p class="' . $fondo . '" style="margin-top:0px; padding-left:3px; padding-right:3px;">'."\n";
					echo '				<a href="ordenes.php?accion=consultar&orden_id=' . $comen['orden_id'] . '" target="_blank">Mensaje en la OT ' . $comen['orden_id'] . '</a><br>'."\n";
					echo '				El ' . $comen['fecha_com'] . ' de ' . $comen['nombre'] . ' ' . $comen['apellidos'] . '<br>'."\n";
					echo '				' . $comen['comentario']. '<br>'."\n";
					echo '				<button name="visto" value="' . $comen['bit_id'] . '" type="submit">' . $lang['Visto'] . '</button>'."\n";
					echo '				<input type="hidden" name="orden_id" value="' . $comen['orden_id'] . '" />
				<input type="hidden" name="pagina" value="index.php" />'."\n";
					echo '				</p>'."\n";				
					$j++;
					if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
				}
				echo '				</td></tr></table></form>'."\n";
			}
// -----------------------------------------------------				
			echo '					</td>'."\n";
		echo '					</tr>' . "\n";
		if(isset($rol) && $rol!='') {
			echo '					<tr><td colspan="2">'. $lang['OT del Grupo '] . $codigos[$rol][0] . '</td></tr>' . "\n";
			$pregunta = "SELECT a.al_codigo, a.al_preventivo, a.al_critico, a.al_vista, a.al_categoria, o.* FROM " . $dbpfx . "alertas a, " . $dbpfx . "ordenes o WHERE o.orden_estatus = a.al_estatus AND o.orden_categoria = a.al_categoria AND ";
			if($rol == '50') { // --- Muestra OTs con refacciones pendientes --- 
				$pregunta .= "(al_codigo ='" . $codigos[$rol][1] . "' OR o.orden_ref_pendientes > '0' ";
				if($saltapres == '1') { $pregunta .= ") "; }
				else { $pregunta .= "OR o.orden_estatus = '28') "; }
			} elseif($rol == '30') { 
				$pregunta .= "(al_codigo ='" . $codigos[$rol][1] . "' OR (o.orden_ubicacion = 'Transito' AND o.orden_estatus = '4')) ";
			} else {
				$pregunta .= "al_codigo ='" . $codigos[$rol][1] . "' ";
			}
			$pregunta .= "ORDER BY o.orden_id DESC";
		}
		elseif(isset($estatus) && $estatus!='') {
			echo '					<tr><td colspan="2">'. $lang['OT en Estatus '] . constant('ORDEN_ESTATUS_' . $estatus) . '</td></tr>' . "\n";
			$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE ";
			if($estatus == '5') {
				$pregunta .= "orden_ref_pendientes > '0' ";
			} else {
				$pregunta .= "orden_estatus = '" . $estatus . "' ";
			}
			$pregunta .= "ORDER BY orden_id DESC";
		} else {
			echo '					<tr class="cabeza_tabla"><td colspan="2">' . TABLA_AVISOS_ENC . '</td></tr>' . "\n";
			$pregunta = "SELECT a.al_codigo, a.al_preventivo, a.al_critico, a.al_vista, a.al_categoria, o.* FROM " . $dbpfx . "alertas a, " . $dbpfx . "ordenes o WHERE o.orden_estatus = a.al_estatus AND o.orden_categoria = a.al_categoria AND ";
			if($_SESSION['codigo']=='50') {
				$pregunta .= "(a.al_codigo ='" . $codigos[$_SESSION['codigo']][1] . "' OR a.al_vista ='" . $codigos[$_SESSION['codigo']][1] . "' OR o.orden_ref_pendientes > '0' ";
				if($saltapres == '1') { $pregunta .= ") "; }
				else { $pregunta .= "OR o.orden_estatus = '28') "; }
			} elseif($_SESSION['codigo']=='30' && $asesorpi != '1') {
				$pregunta .= "(o.orden_asesor_id = '" . $_SESSION['usuario'] . "' AND (a.al_codigo ='" . $codigos[$_SESSION['codigo']][1] . "' OR a.al_vista ='" . $codigos[$_SESSION['codigo']][1] . "' OR (o.orden_estatus = '4' AND o.orden_ubicacion = 'Transito')) OR o.orden_estatus = '17') ";
			} elseif($_SESSION['codigo']=='30') {
				$pregunta .= "(a.al_codigo ='" . $codigos[$_SESSION['codigo']][1] . "' OR a.al_vista ='" . $codigos[$_SESSION['codigo']][1] . "' OR (o.orden_estatus = '4' AND o.orden_ubicacion = 'Transito')) ";
			} elseif($_SESSION['codigo']=='20' && $preaut == '1') {
				$pregunta .= "(al_codigo ='" . $codigos[$_SESSION['codigo']][1] . "' OR al_vista ='" . $codigos[$_SESSION['codigo']][1] . "' OR (o.orden_presupuesto < '1' AND o.orden_estatus > '3' AND o.orden_estatus < '17')) ";
			} else {
				$pregunta .= "(a.al_codigo ='" . $codigos[$_SESSION['codigo']][1] . "' OR a.al_vista ='" . $codigos[$_SESSION['codigo']][1] . "') ";
			}
			$pregunta .= "ORDER BY o.orden_id DESC";
		}
		echo '					<tr><td valign="top" colspan="2">' . "\n";
//		echo $pregunta;
   	$matriz = mysql_query($pregunta) or die($pregunta);
		$num_cols = mysql_num_rows($matriz);
		if ($num_cols>0) {
			echo '							<table cellspacing="1" cellpadding="2" border="0">' . "\n";
			$fondo = 'claro'; $j = 0; $c = 0;
			while($alerta = mysql_fetch_array($matriz)) {
				if(($alerta['al_codigo'] == $codigos[$_SESSION['codigo']][1]) || $rol!='' || $estatus!='' || ($alerta['orden_ubicacion'] == 'Transito' && $_SESSION['codigo']=='30') || $_SESSION['codigo']=='50' || $_SESSION['codigo']=='90') {
					if($_SESSION['codigo']=='40' && $alerta['orden_estatus']>='24' && $alerta['orden_estatus']<='27') {
						$mostrar = 0;
						$preg1 = "SELECT areas FROM " . $dbpfx . "usuarios WHERE usuario = '" . $_SESSION['usuario'] . "'";
						$matr1 = mysql_query($preg1) or die($preg1);
						$usu07 = mysql_fetch_array($matr1);
						$are07 = explode('|', $usu07['areas']);
						$preg2 = "SELECT sub_area FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $alerta['orden_id'] . "' AND (sub_estatus = '124' OR sub_estatus = '127')";
						$matr2 = mysql_query($preg2) or die($preg2);
						while($sub07 = mysql_fetch_array($matr2)) {
							foreach($are07 as $k) {
								if($sub07['sub_area'] == $k) { $mostrar = 1;}
							}	
						}
					}
					$preg3 = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $alerta['orden_id'] . "' AND sub_estatus < '190' GROUP BY sub_aseguradora";
					$matr3 = mysql_query($preg3) or die($preg3);
					if($_SESSION['codigo']=='40' && $alerta['orden_estatus']>='24' && $alerta['orden_estatus']<='27' && $mostrar == '0') {
						// No mostrar este resultado
					} else {
						if($c==0) { echo '								<tr class="' . $fondo . '">'; }
						echo "\n" . '								<td><table width="100%"><tr>';
						echo '<td rowspan ="2"><a href="ordenes.php?accion=consultar&'."\n";
						if($alerta['orden_estatus']=='17' && $confolio == '1') {
							echo 'oid=' . $alerta['oid'];
						} else {
							echo 'orden_id=' . $alerta['orden_id'];
						}
						echo '">' . constant('ALARMA_' . $alerta['orden_alerta']) . '</a></td>';
						echo '<td class="bloque"><a href="ordenes.php?accion=consultar&'."\n";
						if($alerta['orden_estatus']=='17' && $confolio == 1) {
							echo 'oid=' . $alerta['oid'];
						} else {
							echo 'orden_id=' . $alerta['orden_id'];
						}
						echo '">' . $alerta['orden_vehiculo_tipo'] . '&nbsp;' . $alerta['orden_vehiculo_color'] . '&nbsp;' . $alerta['orden_vehiculo_placas'] . '</a></td>';
						echo '<td rowspan ="2" style="vertical-align:top; text-align:right;">';
						while($aseico = mysql_fetch_assoc($matr3)) {
							echo '<img src="' . $aselogo[$aseico['sub_aseguradora']] . '" alt="' . $ase[$aseico['sub_aseguradora']] . '" height="16" ><br>';
						}
						echo '</td></tr>' . "\n";
						echo '<tr><td><strong>'. $lang['OT'] . $alerta['orden_id'] . '</strong>&nbsp;' . constant('ORDEN_ESTATUS_' . $alerta['orden_estatus']);
						if ($alerta['orden_ref_pendientes'] == '2') { 
							echo '<br>' . REFACCIONES_ESTRUCTURALES ;
						} elseif ($alerta['orden_ref_pendientes'] == '1') { 
							echo '<br>' . REFACCIONES_PENDIENTES ;
						}
						if ($alerta['orden_ubicacion'] == 'Transito') { echo '<br><span style="color: white; background-color: black; font-weight: bold;">En Tránsito</span>'; }
						echo '</td></tr>';
						echo '</table></td>';
						$c++;
						if ($c == 3) { echo "\n" . '								</tr>' . "\n"; $c = 0; $j++; }
						if ($j < 2) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
					}
				}
			}
			if ($c == 1 || $c == 2) {
				echo "\n" . '								</tr>' . "\n";
			}
			echo '							</table>' . "\n";
			echo '						</td>' . "\n";
			echo '					</tr>'."\n";
			if(!isset($estaus) && !isset($rol)) {
				echo '					<tr class="cabeza_tabla"><td colspan="2">' . TABLA_AVISOS_SUB . '</td></tr>' . "\n";
				mysql_data_seek($matriz, 0);
				echo '					<tr><td valign="top" colspan="2">' . "\n";
				echo '							<table cellspacing="1" cellpadding="2" border="0">' . "\n";
				$fondo = 'claro'; $j = 0; $c = 0; $valmsj = 'VA Pendiente'; $alertaval = 0;
				while($alerta = mysql_fetch_array($matriz)) {
					if($_SESSION['codigo'] == '20' && $alerta['orden_presupuesto'] < '1' && $alerta['orden_estatus'] > '3' && $alerta['orden_estatus'] < '17' ) { $alertaval = 1; }
					if($alerta['al_vista'] == $codigos[$_SESSION['codigo']][1] || $alertaval == 1 || ($alerta['orden_ubicacion'] == 'Transito' && $_SESSION['codigo']=='30')) {
						if($c==0) { echo '								<tr class="' . $fondo . '">'; }
						echo "\n" . '								<td><table width="100%"><tr>';
						echo '<td rowspan ="2"><a href="ordenes.php?accion=consultar&orden_id=' . $alerta['orden_id'] . '">' . constant('ALARMA_' . $alerta['orden_alerta']) . '</a></td>';
						echo '<td class="bloque"><a href="ordenes.php?accion=consultar&orden_id=' . $alerta['orden_id'] . '">' . $alerta['orden_vehiculo_tipo'] . '&nbsp;' . $alerta['orden_vehiculo_color'] . '&nbsp;' . $alerta['orden_vehiculo_placas'] . '</a></td>';
						echo '<td rowspan ="2" style="vertical-align:top; text-align:right;">';
						$preg3 = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $alerta['orden_id'] . "' AND sub_estatus <  '190' GROUP BY sub_aseguradora";
						$matr3 = mysql_query($preg3) or die($preg3);
//						mysql_data_seek($matr3, 0);
						while($aseico = mysql_fetch_assoc($matr3)) {
							echo '<img src="' . $aselogo[$aseico['sub_aseguradora']] . '" alt="' . $ase[$aseico['sub_aseguradora']] . '" height="16" ><br>';
						}
						echo '</td></tr>' . "\n";
						echo '<tr><td><strong>'. $lang['OT'] . $alerta['orden_id'] . '</strong>&nbsp;' . constant('ORDEN_ESTATUS_' . $alerta['orden_estatus']);
						if ($alerta['orden_ref_pendientes'] == '2') { 
							echo '<br>' . REFACCIONES_ESTRUCTURALES ;
						} elseif ($alerta['orden_ref_pendientes'] == '1') { 
							echo '<br>' . REFACCIONES_PENDIENTES ;
						}
						if ($alerta['orden_ubicacion'] == 'Transito') { echo '<br><span style="color: white; background-color: black; font-weight: bold;">En Tránsito</span>'; }
						if ($alertaval == 1) {
							 echo '<br>' . $valmsj ;
						}
						echo '</td></tr>';
						echo '</table></td>';
						$c++;
						if ($c == 3) { echo "\n" . '								</tr>' . "\n"; $c = 0; $j++; }
						if ($j < 2) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
					}
					$alertaval = 0;
				}
				if ($c == 1 || $c == 2) {
					echo "\n" . '								</tr>' . "\n";
				}
				echo '							</table>' . "\n";
				echo '					</td>'."\n";
				echo '				</tr>'."\n";
			}
		}
		echo '				</table>' . "\n";
}


?>
			</div>
		</div>

<?php include('parciales/pie.php'); 
/* Archivo index.php */
/* AutoShop Easy */
