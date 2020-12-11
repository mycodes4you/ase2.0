<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
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

$codigos = array('10' => array('GERENCIA','rol02'),
	'12' => array('ASISTENTE DE GERENCIA','rol03'),
	'15' => array('JEFE DE TALLER','rol04'),
	'20' => array('VALUADORES','rol05'),
	'30' => array('ASESORES','rol06'),
	'40' => array('JEFES DE AREA','rol07'),
	'50' => array('ALMACEN','rol08'),
	'60' => array('OPERADORES','rol09'),
	'70' => array('AYUDANTES','rol10'),
	'80' => array('CALIDAD','rol11'),
	'90' => array('VENTAS','rol12'),
	'100' => array('PAGO PROVEEDORES','rol13'),
	'2000' => array('ASEGURADORA','rol14'));
	

//	print_r($etapa);
	
	$funnum = 1055000;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);

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

if($_SESSION['rol02']=='1' || $_SESSION['rol04']=='1' || $retorno == '1') {
	$treintadias = (strtotime(date('Y-m-d')) - 2592000); 
	$estemes = date('n'); $year = date('Y');
	for($j=0;$j<4;$j++) {
		$elmes = $estemes - $j;
		if($elmes < 1) {$elmes = $elmes + 12; $year = $year -1;}
		$etiqmes[$j] = strtoupper(strftime('%b', mktime(0,0,0,$elmes)));
		if($j == '3') { $feini = $year.'-'.$elmes.'-01 00:00:00'; }
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
		$ds = dias($ord['orden_fecha_ultimo_movimiento']);
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
			$positivo = 0;
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
		
		if($ord['orden_estatus'] >= 30 && $ord['orden_estatus'] <= 39) {
			$otsr[$ord['orden_estatus']][$mes]++;
		}
		
		if($ord['orden_ref_pendientes'] > '0') {
			$otrp[$ds]++;
		}
		
		for($i=1;$i<=10;$i++) {
			if($ord['orden_ubicacion'] == constant('NOMBRE_AREA_'.$i)) {
				$tr[$i][$ds]++;
			}
		}

/*		$pregunta2 = "SELECT sub_estatus, sub_area, sub_reporte, sub_aseguradora, sub_refacciones_recibidas FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "'";
		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
		while($sub = mysql_fetch_array($matriz2)) {
			if($sub['sub_estatus'] < '111' && $sub['sub_estatus'] > '106') {
				$tr[$sub['sub_area']][$sem]++;
			}
			if($sub['sub_refacciones_recibidas'] > 0) {
				$rp[$sub['sub_area']][$sem]++;
			}
		}
*/		

//		if($mes > 2) {$mes=3;}
		$preg3 = "SELECT s.sub_estatus, s.sub_aseguradora FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE o.orden_id = '" . $ord['orden_id'] . "' AND s.sub_estatus < '190' AND o.orden_id = s.orden_id GROUP BY s.sub_reporte";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo seleccion!");
		while ($sub2 = mysql_fetch_array($matr3)) {
			$mes = quemes($ord['orden_fecha_recepcion']);
			$asegu[$sub2['sub_aseguradora']][$mes]++; 
//			echo $ord['orden_id'] . ' fecha: ' . $ord['orden_fecha_recepcion'] . 'mes: ' .$mes . ' Aseg: ' . $sub2['sub_aseguradora'] . '<br>';
			if($ord['orden_estatus'] == '99' || $ord['orden_estatus'] == '16') {
				$mes = quemes($ord['orden_fecha_de_entrega']);
				$aseen[$sub2['sub_aseguradora']][$mes]++; 
			}
		}

		$mes = quemes($ord['orden_fecha_de_entrega']);
		if($ord['orden_estatus'] > 90 || $ord['orden_estatus'] == 16) {
			$ote[$ord['orden_estatus']][$mes]++;
		}

	}
	
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
		if($ot[$v][3]!='' || $ot[$v][4]!='') { $ot[$v][3] = $ot[$v][3] + $ot[$v][4]; }
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id='.$v.'&n=t">' . constant('ORDEN_ESTATUS_'.$v) . '</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=0">'. $ot[$v][0] .'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=1">'.$ot[$v][1].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=2">'.$ot[$v][2].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=3">'.$ot[$v][3].'</a></td></tr>'."\n";
	}

	echo '						</table>';
	echo '			</div>'."\n";

	
	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;" rowspan="2">' . PANEL_CONTROL_2 . '</td>
								<td width="10%" colspan="4">Días</td>
							</tr>
							<tr class="cabeza_tabla">
								<td width="10%">1</td>
								<td width="10%">2 a 3</td>
								<td width="10%">4 a 6</td>
								<td width="10%">7 o +</td>
							</tr>'."\n";

		if($otrit[3]!='' || $otrit[4]!='') { $otrit[3] = $otrit[3] + $otrit[4]; }
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&rit=1&n=t">'. $lang['OTs en Transito estado 4'].'</a></td><td><a href="reportes.php?accion=tabla&rit=1&n=0">'.$otrit[0].'</a></td><td><a href="reportes.php?accion=tabla&rit=1&n=1">'.$otrit[1].'</a></td><td><a href="reportes.php?accion=tabla&rit=1&n=2">'.$otrit[2].'</a></td><td><a href="reportes.php?accion=tabla&rit=1&n=3">'.$otrit[3].'</a></td></tr>'."\n";
	foreach($etapa[2] as $k => $v) {
		if($ot[$v][3]!='' || $ot[$v][4]!='') { $ot[$v][3] = $ot[$v][3] + $ot[$v][4]; }
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id='.$v.'&n=t">' . constant('ORDEN_ESTATUS_'.$v) . '</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=0">'. $ot[$v][0] .'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=1">'.$ot[$v][1].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=2">'.$ot[$v][2].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=3">'.$ot[$v][3].'</a></td></tr>'."\n";
	}
//	print_r($otrp);
		if($otrp[3]!='' || $otrp[4]!='') { $otrp[3] = $otrp[3] + $otrp[4]; }
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&pp=1&n=t">'. $lang['Ordenes con Refacc Pend'].'</a></td><td><a href="reportes.php?accion=tabla&pp=1&n=0">'.$otrp[0].'</a></td><td><a href="reportes.php?accion=tabla&pp=1&n=1">'.$otrp[1].'</a></td><td><a href="reportes.php?accion=tabla&pp=1&n=2">'.$otrp[2].'</a></td><td><a href="reportes.php?accion=tabla&pp=1&n=3">'.$otrp[3].'</a></td></tr>'."\n";
	echo '						</table>';
	echo '			</div>'."\n";

	echo '			<div style="clear: both;"></div><div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;" rowspan="2">' . PANEL_CONTROL_3 . '</td>
								<td width="10%" colspan="4">'. $lang['Días'].'</td>
							</tr>
							<tr class="cabeza_tabla">
								<td width="10%">'. $lang['1'].'</td>
								<td width="10%">'. $lang['2 a 3'].'</td>
								<td width="10%">'. $lang['4 a 6'].'</td>
								<td width="10%">'. $lang['7 o +'].'</td>
							</tr>'."\n";
							
	foreach($tr as $k => $v) {
		if($tr[$k][3]!='' || $tr[$k][4]!='') { $tr[$k][3] = $tr[$k][3] + $tr[$k][4]; }
		$refp = $rp[$k][0] + $rp[$k][1] + $rp[$k][2] + $rp[$k][3] + $rp[$k][4]; 
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&area='.$k.'&n=t">' . constant('NOMBRE_AREA_'.$k) . '</a></td><td><a href="reportes.php?accion=tabla&area='.$k.'&n=0">'.$tr[$k][0].'</a></td><td><a href="reportes.php?accion=tabla&area='.$k.'&n=1">'.$tr[$k][1].'</a></td><td><a href="reportes.php?accion=tabla&area='.$k.'&n=2">'.$tr[$k][2].'</a></td><td><a href="reportes.php?accion=tabla&area='.$k.'&n=3">'.$tr[$k][3].'</a></td></tr>'."\n";
	}

	echo '						</table>';
	echo '			</div>'."\n";

	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;" rowspan="2">'. $lang['Ordenes en Etapa de Entrega'].'</td>
								<td width="10%" colspan="4">Días</td>
							</tr>
							<tr class="cabeza_tabla">
								<td width="10%">1</td>
								<td width="10%">2 a 3</td>
								<td width="10%">4 a 6</td>
								<td width="10%">7 o +</td>
							</tr>'."\n";
	foreach($etapa[3] as $k => $v) {
		if($ot[$v][3]!='' || $ot[$v][4]!='') { $ot[$v][3] = $ot[$v][3] + $ot[$v][4]; }
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id='.$v.'&n=t">' . constant('ORDEN_ESTATUS_'.$v) . '</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=0">'. $ot[$v][0] .'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=1">'.$ot[$v][1].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=2">'.$ot[$v][2].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&n=3">'.$ot[$v][3].'</a></td></tr>'."\n";
	}

	echo '						</table>';
	echo '			</div>'."\n";

	echo '			<div style="clear: both;"></div><div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">'. $lang['Entregas Facturables por Mes'].'</td>
								<td width="10%">'.$etiqmes[0].'</td>
								<td width="10%">'.$etiqmes[1].'</td>
								<td width="10%">'.$etiqmes[2].'</td>
								<td width="10%">'.$etiqmes[3].'</td>
							</tr>'."\n";
//	if($ofc[3]!='' || $ofc[4]!='') { $ofc[3] = $ofc[3] + $ofc[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&of=1&mfe=4">' . $lang['Facturas cobradas'] . '</a></td><td><a href="reportes.php?accion=tabla&of=1&mfe=0">'.$ofc[0].'</a></td><td><a href="reportes.php?accion=tabla&of=1&mfe=1">'.$ofc[1].'</a></td><td><a href="reportes.php?accion=tabla&of=1&mfe=2">'.$ofc[2].'</a></td><td><a href="reportes.php?accion=tabla&of=1&mfe=3">'.$ofc[3].'</a></td></tr>'."\n";
//	if($ofsc[3]!='' || $ofsc[4]!='') { $ofsc[3] = $ofsc[3] + $ofsc[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&of=0&mfe=4">' . $lang['Facturas sin cobrar'] . '</a></td><td><a href="reportes.php?accion=tabla&of=0&mfe=0">'.$ofsc[0].'</a></td><td><a href="reportes.php?accion=tabla&of=0&mfe=1">'.$ofsc[1].'</a></td><td><a href="reportes.php?accion=tabla&of=0&mfe=2">'.$ofsc[2].'</a></td><td><a href="reportes.php?accion=tabla&of=0&mfe=3">'.$ofsc[3].'</a></td></tr>'."\n";

	$osf[0] = ($ote[16][0]+$ote[99][0]) - ($ofc[0]+$ofsc[0]);
	$osf[1] = ($ote[16][1]+$ote[99][1]) - ($ofc[1]+$ofsc[1]);
	$osf[2] = ($ote[16][2]+$ote[99][2]) - ($ofc[2]+$ofsc[2]);
	$osf[3] = ($ote[16][3]+$ote[99][3]) - ($ofc[3]+$ofsc[3]);
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&osf=1&mfe=4">' . $lang['OTs sin facturar'] . '</a></td><td><a href="reportes.php?accion=tabla&osf=1&mfe=0">'.$osf[0].'</a></td><td><a href="reportes.php?accion=tabla&osf=1&mfe=1">'.$osf[1].'</a></td><td><a href="reportes.php?accion=tabla&osf=1&mfe=2">'.$osf[2].'</a></td><td><a href="reportes.php?accion=tabla&osf=1&mfe=3">'.$osf[3].'</a></td></tr>'."\n";
	foreach($etapa[4] as $k => $v) {
//		if($ote[$v][3]!='' || $ote[$v][4]!='') { $ote[$v][3] = $ote[$v][3] + $ote[$v][4]; }
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id='.$v.'&mfe=4">' . constant('ORDEN_ESTATUS_'.$v) . '</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&mfe=0">'.$ote[$v][0].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&mfe=1">'.$ote[$v][1].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&mfe=2">'.$ote[$v][2].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&mfe=3">'.$ote[$v][3].'</a></td></tr>'."\n";
	}
	

	echo '						</table>';
	echo '			</div>'."\n";

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
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&otsr=1&mfr=3">'. $lang['Total Sin Reparar por Entregar'].'</a></td><td><a href="reportes.php?accion=tabla&otsr=1&mfr=0">'.$ottsr[0].'</a></td><td><a href="reportes.php?accion=tabla&otsr=1&mfr=1">'.$ottsr[1].'</a></td><td><a href="reportes.php?accion=tabla&otsr=1&mfr=2">'.$ottsr[2].'</a></td><td><a href="reportes.php?accion=tabla&otsr=1&mfr=4">'.$ottsr[3].'</a></td></tr>'."\n";
	foreach($etapa[6] as $k => $v) {
//		if($ote[$v][3]!='' || $ote[$v][4]!='') { $ote[$v][3] = $ote[$v][3] + $ote[$v][4]; }
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id='.$v.'&mes=t">' . constant('ORDEN_ESTATUS_'.$v) . '</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&mes=0">'.$ote[$v][0].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&mes=1">'.$ote[$v][1].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&mes=2">'.$ote[$v][2].'</a></td><td><a href="reportes.php?accion=tabla&id='.$v.'&mes=3">'.$ote[$v][3].'</a></td></tr>'."\n";
	}

	echo '						</table>';
	echo '			</div>'."\n";

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
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=aseguradora&mfr=4&a='.$k.'">' . $v . ': '.$asegut.'</a></td><td><a href="reportes.php?accion=aseguradora&mfr=0&a='.$k.'">'.$asegu[$k][0].'</a></td><td><a href="reportes.php?accion=aseguradora&mfr=1&a='.$k.'">'.$asegu[$k][1].'</a></td><td><a href="reportes.php?accion=aseguradora&mfr=2&a='.$k.'">'.$asegu[$k][2].'</a></td><td><a href="reportes.php?accion=aseguradora&mfr=3&a='.$k.'">'.$asegu[$k][3].'</a></td></tr>'."\n";
	}
	echo '						</table>';
	echo '			</div>'."\n";

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
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=aseguradora&mfe=4&a='.$k.'">' . $v . ': '.$aseent.'</a></td><td><a href="reportes.php?accion=aseguradora&mfe=0&a='.$k.'">'.$aseen[$k][0].'</a></td><td><a href="reportes.php?accion=aseguradora&mfe=1&a='.$k.'">'.$aseen[$k][1].'</a></td><td><a href="reportes.php?accion=aseguradora&mfe=2&a='.$k.'">'.$aseen[$k][2].'</a></td><td><a href="reportes.php?accion=aseguradora&mfe=3&a='.$k.'">'.$aseen[$k][3].'</a></td></tr>'."\n";
//		echo '							<tr class="bloque_fila"><td style="text-align:left;">' . $v . ': '.$aseent.'</td><td>'.$aseen[$k][0].'</td><td>'.$aseen[$k][1].'</td><td>'.$aseen[$k][2].'</td><td>'.$aseen[$k][3].'</td></tr>'."\n";
	}
	echo '						</table>';
	echo '			</div>'."\n";

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

	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&otmf=1&mfr=3">'. $lang['En Tránsito'].$ottft.'</a></td><td><a href="reportes.php?accion=tabla&otmf=1&mfr=0">'.$ottf[0].'</a></td><td><a href="reportes.php?accion=tabla&otmf=1&mfr=1">'.$ottf[1].'</a></td><td><a href="reportes.php?accion=tabla&otmf=1&mfr=2">'.$ottf[2].'</a></td><td><a href="reportes.php?accion=tabla&otmf=1&mfr=4">'.$ottf[3].'</a></td></tr>'."\n";
	
	foreach($otm as $k => $v) {
			if($k < '4') { 
				$ottm[$k] = $ottm[$k] + $otm[$k];
			} else {
				$ottm[3] = $ottm[3] + $otm[$k];
			}
	}

	$ottmt = $ottm[0] + $ottm[1] + $ottm[2] + $ottm[3];
	

	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&otm=1&mfr=3">'. $lang['En Taller'].$ottmt.'</a></td><td><a href="reportes.php?accion=tabla&otm=1&mfr=0">'.$ottm[0].'</a></td><td><a href="reportes.php?accion=tabla&otm=1&mfr=1">'.$ottm[1].'</a></td><td><a href="reportes.php?accion=tabla&otm=1&mfr=2">'.$ottm[2].'</a></td><td><a href="reportes.php?accion=tabla&otm=1&mfr=4">'.$ottm[3].'</a></td></tr>'."\n";
	
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
				echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&ubc='.$i.'&mfr=3">'.$ubicaciones[$i].': '.$ottubt[$i].'</a></td><td><a href="reportes.php?accion=tabla&ubc='.$i.'&mfr=0">'.$ottubc[$i][0].'</a></td><td><a href="reportes.php?accion=tabla&ubc='.$i.'&mfr=1">'.$ottubc[$i][1].'</a></td><td><a href="reportes.php?accion=tabla&ubc='.$i.'&mfr=2">'.$ottubc[$i][2].'</a></td><td><a href="reportes.php?accion=tabla&ubc='.$i.'&mfr=4">'.$ottubc[$i][3].'</a></td></tr>'."\n";
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
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&ottp=1&mfr=3">Totales: '.$toto.'</a></td><td><a href="reportes.php?accion=tabla&ottp=1&mfr=0">'.$otp[0].'</a></td><td><a href="reportes.php?accion=tabla&ottp=1&mfr=1">'.$otp[1].'</a></td><td><a href="reportes.php?accion=tabla&ottp=1&mfr=2">'.$otp[2].'</a></td><td><a href="reportes.php?accion=tabla&ottp=1&mfr=4">'.$otp[3].'</a></td></tr>'."\n";

	echo '						</table>';
	echo '			</div>'."\n";

//	print_r($v);
	
} 

	$funnum = 1055005;

if ($_SESSION['codigo']!='70' && $_SESSION['codigo'] < '2000') {

// página default 

//	echo $num_cols;
		echo '				<div style="clear: both;"></div><table cellspacing="0" cellpadding="2" border="1" class="avisos" >'."\n";
		echo '					<tr><td colspan="2"><span class="alerta">' . $_SESSION['index']['mensaje'] . '</span></td></tr>
					<tr>
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
						</td>
					</tr>' . "\n";
		unset($_SESSION['index']['mensaje']);
		if(isset($rol) && $rol!='') {
			echo '					<tr><td colspan="2">'. $lang['OT del Grupo '] . $codigos[$rol][0] . '</td></tr>' . "\n";
			$pregunta = "SELECT a.al_codigo, a.al_preventivo, a.al_critico, a.al_vista, a.al_categoria, o.* FROM " . $dbpfx . "alertas a, " . $dbpfx . "ordenes o WHERE o.orden_estatus = a.al_estatus AND o.orden_categoria = a.al_categoria AND ";
			if($rol == '50') { 
				$pregunta .= "(o.orden_ref_pendientes > '0' ";
				if($saltapres == '1') { $pregunta .= ") "; }
				else { $pregunta .= "OR o.orden_estatus = '28') "; }
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
				$pregunta .= "(o.orden_ref_pendientes > '0' ";
				if($saltapres == '1') { $pregunta .= ") "; }
				else { $pregunta .= "OR o.orden_estatus = '28') "; }
			} elseif($_SESSION['codigo']=='30') {
				$pregunta .= "(o.orden_asesor_id = '" . $_SESSION['usuario'] . "') ";
			} else {
				$pregunta .= "(al_codigo ='" . $codigos[$_SESSION['codigo']][1] . "' OR al_vista ='" . $codigos[$_SESSION['codigo']][1] . "') ";
			}
			$pregunta .= "ORDER BY o.orden_id DESC";
		}
		echo '					<tr><td valign="top">' . "\n";
//		echo $pregunta;
   	$matriz = mysql_query($pregunta) or die($pregunta);
		$num_cols = mysql_num_rows($matriz);
		if ($num_cols>0) {
			echo '							<table cellspacing="1" cellpadding="2" border="0">' . "\n";
			$fondo = 'claro'; $j = 0; $c = 0;
			while($alerta = mysql_fetch_array($matriz)) {
				if(($alerta['al_codigo'] == $codigos[$_SESSION['codigo']][1]) || $rol!='' || $estatus!='' || $_SESSION['codigo']=='50' || $_SESSION['codigo']=='90') {
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
					$preg3 = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $alerta['orden_id'] . "' GROUP BY sub_aseguradora";
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
				$fondo = 'claro'; $j = 0; $c = 0;
				while($alerta = mysql_fetch_array($matriz)) {
					if($alerta['al_vista'] == $codigos[$_SESSION['codigo']][1]) {
						if($c==0) { echo '								<tr class="' . $fondo . '">'; }
						echo "\n" . '								<td><table width="100%"><tr>';
						echo '<td rowspan ="2"><a href="ordenes.php?accion=consultar&orden_id=' . $alerta['orden_id'] . '">' . constant('ALARMA_' . $alerta['orden_alerta']) . '</a></td>';
						echo '<td class="bloque"><a href="ordenes.php?accion=consultar&orden_id=' . $alerta['orden_id'] . '">' . $alerta['orden_vehiculo_tipo'] . '&nbsp;' . $alerta['orden_vehiculo_color'] . '&nbsp;' . $alerta['orden_vehiculo_placas'] . '</a></td>';
						echo '<td rowspan ="2" style="vertical-align:top; text-align:right;">';
						$preg3 = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $alerta['orden_id'] . "' GROUP BY sub_aseguradora";
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
						echo '</td></tr>';
						echo '</table></td>';
						$c++;
						if ($c == 3) { echo "\n" . '								</tr>' . "\n"; $c = 0; $j++; }
						if ($j < 2) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
					}
				}
				if ($c == 1 || $c == 2) {
					echo "\n" . '								</tr>' . "\n";
				}
				echo '							</table>' . "\n";
				echo '					</td></tr>'."\n";
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