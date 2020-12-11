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
	'40' => array('SUPERVISORES','rol07'),
	'50' => array('ALMACEN','rol08'),
	'60' => array('OPERADORES','rol09'),
	'70' => array('AYUDANTES','rol10'),
	'80' => array('CALIDAD','rol11'),
	'90' => array('VENTAS','rol12'),
	'100' => array('PAGO PROVEEDORES','rol13'),
	'2000' => array('ASEGURADORA','rol14'));
	
$etapas = array("" => array(17,0,1,20,2),
	"Refacciones Pendientes" => array(4,5,6,8,9,10,11),
	"Etapa de Reparación" => array(4,5,6,8,9,10,11),
	"Etapa de Entrega" => array(12,13,14,15,30,31),
	"Facturación" => array(99),
	"Aseguradora" => array(97,98));

//	print_r($etapas);


if($_SESSION['rol02']=='1' || $_SESSION['rol04']=='1') {
	$treintadias = (strtotime(date('Y-m-d')) - 2592000); $feini = date('Y-m-d 00:00:00', $treintadias);
	$estemes = date('n'); $year = date('Y');
	for($j=0;$j<4;$j++) {
		$elmes = $estemes - $j;
		if($elmes < 1) {$elmes = $elmes + 12;}
		$etiqmes[$j] = strtoupper(strftime('%b', mktime(0,0,0,$elmes)));
	}
	$fefin = date('Y-m-d 23:59:59');
	 
	$pregunta = "SELECT orden_id, orden_estatus, orden_ref_pendientes, orden_ubicacion, orden_fecha_recepcion, orden_fecha_promesa_de_entrega, orden_fecha_de_entrega FROM " . $dbpfx . "ordenes";
//	 WHERE orden_fecha_recepcion > '$feini' AND orden_fecha_recepcion < '$fefin'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	
/*  ----------------  obtener nombres de aseguradoras   ------------------- */
	
		$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_nic, prov_def, prov_dde FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		$ase[0] = "Particular";
		while ($aseg = mysql_fetch_array($arreglo)) {
			$ase[$aseg['aseguradora_id']] = $aseg['aseguradora_nic'];
//			define('ASEGURADORA_NIC_' . $aseg['aseguradora_id'], $aseg['aseguradora_nic']);
			$autosurt[$aseg['aseguradora_id']] = $aseg['autosurtido'];
			$prov_def[$aseg['aseguradora_id']] = $aseg['prov_def'];
			$prov_dde[$aseg['aseguradora_id']] = $aseg['prov_dde'];
		}
/*  ----------------  nombres de aseguradoras   ------------------- */
	
	while($ord = mysql_fetch_array($matriz)) {
		$sem = semana($ord['orden_fecha_recepcion']);
		if($ord['orden_estatus'] < 2 || $ord['orden_estatus']==17 || $ord['orden_estatus']==27) { 
			if($ord['orden_ubicacion']=='Transito') { $int[$sem]++; /*echo $sem . ' -> ' . $ord['orden_id'] . ' | ';*/ } else { $in[$sem]++; /*echo $sem . ' -> ' . $ord['orden_id'] . ' | ';*/}
		}
		elseif($ord['orden_estatus']==20 || $ord['orden_estatus']==28) { 
			if($ord['orden_ubicacion']=='Transito') { $ast[$sem]++; } else { $as[$sem]++; }
		}
		elseif($ord['orden_estatus'] > 1 && $ord['orden_estatus'] < 5 && $ord['orden_ubicacion']=='Transito') {
			if($ord['orden_ref_pendientes']==2) { $afe[$sem]++; } 
			elseif($ord['orden_ref_pendientes']==1) { $afp[$sem]++; }
			else { $afc[$sem]++; }
		}
		elseif($ord['orden_estatus'] > 1 && $ord['orden_estatus'] < 5) { 
			if($ord['orden_ref_pendientes']==2) { $ate[$sem]++; } 
			elseif($ord['orden_ref_pendientes']==1) { $atp[$sem]++; }
			else { $atc[$sem]++; }
		}

		if(($ord['orden_estatus'] > 10 && $ord['orden_estatus'] < 17) || ($ord['orden_estatus'] > 21 && $ord['orden_estatus'] < 24)) { $ec[$sem]++; }
		if($ord['orden_estatus'] == 7 ) { 
			if($ord['orden_ubicacion']=='Transito') { $epf[$sem]++; } else { $ep[$sem]++; }
		}

		if($ord['orden_estatus'] == 30 ) { $epd[$sem]++; }
		if($ord['orden_estatus'] == 31 ) { $ept[$sem]++; }
		if($ord['orden_estatus'] == 32 ) { $epp[$sem]++; }
		if($ord['orden_estatus'] == 33 || $ord['orden_estatus'] == 34 || $ord['orden_estatus'] == 35) { $enr[$sem]++; }

		if($ord['orden_estatus'] > 89) {
			$mes = quemes($ord['orden_fecha_de_entrega']);
//			$mes = quemes($ord['orden_fecha_recepcion']);
			if($ord['orden_estatus'] == 99 ) { $ce[$mes]++; }
			if($ord['orden_estatus'] == 98 ) { $cpd[$mes]++; }
			if($ord['orden_estatus'] == 97 ) { $cpt[$mes]++; }
			if($ord['orden_estatus'] == 96 ) { $cpp[$mes]++; }
			if($ord['orden_estatus'] == 95 || $ord['orden_estatus'] == 94 || $ord['orden_estatus'] == 93 ) { $cnr[$mes]++; }
		} else {
			$mes = quemes($ord['orden_fecha_recepcion']);
			if($ord['orden_ubicacion']=='Transito') { $upf[$mes]++; } else { $upt[$mes]++; }
		}
		
		if($ord['orden_estatus'] != 90) {
			$mes = quemes($ord['orden_fecha_recepcion']);
			$to[$mes]++;
		}
		
		$pregunta2 = "SELECT sub_estatus, sub_area, sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "'";
		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
		while($sub = mysql_fetch_array($matriz2)) {
			if($sub['sub_estatus'] < '111' && $sub['sub_estatus'] > '104') {
				if($sub['sub_area']=='1') {$rm1[$sub['sub_estatus']][$sem]++;}
				elseif($sub['sub_area']=='5') {$re1[$sub['sub_estatus']][$sem]++;}
				elseif($sub['sub_area']=='6') {$rh1[$sub['sub_estatus']][$sem]++;}
				elseif($sub['sub_area']=='7') {$rp1[$sub['sub_estatus']][$sem]++;}
				elseif($sub['sub_area']=='2') {$ra1[$sub['sub_estatus']][$sem]++;}
			}
		}
		$mes = quemes($ord['orden_fecha_recepcion']);
		if($mes > 2) {$mes=3;}
		$preg3 = "SELECT s.sub_estatus, s.sub_aseguradora FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE o.orden_id = '" . $ord['orden_id'] . "' AND s.sub_estatus < '190' AND o.orden_id = s.orden_id GROUP BY s.sub_reporte";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo seleccion!");
		while ($sub2 = mysql_fetch_array($matr3)) {
			$asegu[$sub2['sub_aseguradora']][$mes]++; 
			if($ord['orden_estatus'] == 99) {
				$mes = quemes($ord['orden_fecha_de_entrega']);
				$aseen[$sub2['sub_aseguradora']][$mes]++; 
			}
//			echo $ord['orden_id'] . ' mes: ' .$mes . ' Aseg: ' . $sub2['sub_aseguradora'] . '<br>';
		}
	}

//	print_r($rp1);
	$rmp1=0; $rep1=0; $rhp1=0; $rpp1=0; $rap1=0; 
	for($i=0;$i<5;$i++) {
		$rm[$i]=$rm1[109][$i] + $rm1[110][$i]; $rmp[$i]=$rm1[106][$i] + $rm1[107][$i] + $rm1[108][$i]; if($rm[$i]==0) {$rm[$i]='';}
		$re[$i]=$re1[109][$i] + $re1[110][$i]; $rep[$i]=$re1[106][$i] + $re1[107][$i] + $re1[108][$i]; if($re[$i]==0) {$re[$i]='';}
		$rh[$i]=$rh1[109][$i] + $rh1[110][$i]; $rhp[$i]=$rh1[106][$i] + $rh1[107][$i] + $rh1[108][$i]; if($rh[$i]==0) {$rh[$i]='';}
		$rp[$i]=$rp1[109][$i] + $rp1[110][$i]; $rpp[$i]=$rp1[106][$i] + $rp1[107][$i] + $rp1[108][$i]; if($rp[$i]==0) {$rp[$i]='';}
		$ra[$i]=$ra1[109][$i] + $ra1[110][$i]; $rap[$i]=$ra1[106][$i] + $ra1[107][$i] + $ra1[108][$i]; if($ra[$i]==0) {$ra[$i]='';}
		$rpa[$i] = $rmp[$i] + $rep[$i] + $rhp[$i] + $rpp[$i] + $rap[$i] ; if($rpa[$i]==0) {$rpa[$i]='';}
		$rmp1 = $rmp1 + $rmp[$i]; $rep1 = $rep1 + $rep[$i]; $rhp1 = $rhp1 + $rhp[$i]; $rpp1 = $rpp1 + $rpp[$i]; $rap1 = $rap1 + $rap[$i];
		$rmrp = $rmrp + $rm1[105][$i];  
		$rerp = $rerp + $re1[105][$i];  
		$rhrp = $rhrp + $rh1[105][$i];  
		$rprp = $rprp + $rp1[105][$i];  
		$rarp = $rarp + $ra1[105][$i]; 
		$up[$i] = $upf[$i] + $upt[$i];
//		$to[$i] = $ce[$i] + $cpd[$i] + $cpp[$i] + $cpt[$i] + $up[$i];
	}
		if($rmp1==0) {$rmp1='';} 
		if($rep1==0) {$rep1='';} 
		if($rhp1==0) {$rhp1='';} 
		if($rpp1==0) {$rpp1='';} 
		if($rap1==0) {$rap1='';}
		if($rmrp==0) {$rmrp='';}
		if($rerp==0) {$rerp='';}
		if($rhrp==0) {$rhrp='';}
		if($rprp==0) {$rprp='';}
		if($rarp==0) {$rarp='';}

//	print_r($rpp);
	
	
	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">Ordenes en Etapa de Autorización<br>Semanas de ingreso:</td>
								<td width="10%">- de 3 días</td>
								<td width="10%">1</td>
								<td width="10%">2</td>
								<td width="10%">3 o más</td>
							</tr>'."\n";
	if($in[3]!='' || $in[4]!='') { $in[3] = $in[3] + $in[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oi&t=0">Ingreso</a></td><td><a href="reportes.php?accion=tabla&id=oi0&t=0">'.$in[0].'</a></td><td><a href="reportes.php?accion=tabla&id=oi1&t=0">'.$in[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oi2&t=0">'.$in[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oi3&t=0">'.$in[3].'</a></td></tr>'."\n";
	if($int[3]!='' || $int[4]!='') { $int[3] = $int[3] + $int[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oi&t=1">Ingreso en Tránsito</a></td><td><a href="reportes.php?accion=tabla&id=oi0&t=1">'.$int[0].'</a></td><td><a href="reportes.php?accion=tabla&id=oi1&t=1">'.$int[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oi2&t=1">'.$int[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oi3&t=1">'.$int[3].'</a></td></tr>'."\n";
	if($as[3]!='' || $as[4]!='') { $as[3] = $as[3] + $as[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oa&t=0">Autorización Solicitada</a></td><td><a href="reportes.php?accion=tabla&id=oa0&t=0">'.$as[0].'</a></td><td><a href="reportes.php?accion=tabla&id=oa1&t=0">'.$as[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oa2&t=0">'.$as[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oa3&t=0">'.$as[3].'</a></td></tr>'."\n";
	if($ast[3]!='' || $ast[4]!='') { $ast[3] = $ast[3] + $ast[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oa&t=1">Autorización Solicitada en Tránsito</a></td><td><a href="reportes.php?accion=tabla&id=oa0&t=1">'.$ast[0].'</a></td><td><a href="reportes.php?accion=tabla&id=oa1&t=1">'.$ast[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oa2&t=1">'.$ast[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oa3&t=1">'.$ast[3].'</a></td></tr>'."\n";
	echo '						</table>';
	echo '			</div>'."\n";

	
	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">Ordenes Autorizadas por Iniciar<br>Semanas de ingreso:</td>
								<td width="10%">- de 3 días</td>
								<td width="10%">1</td>
								<td width="10%">2</td>
								<td width="10%">3 o más</td>
							</tr>'."\n";
	if($afe[3]!='' || $afe[4]!='') { $afe[3] = $afe[3] + $afe[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oae&t=1">En Tránsito Estructurales Pendientes</a></td><td><a href="reportes.php?accion=tabla&id=oae0&t=1">'.$afe[0].'</a></td><td><a href="reportes.php?accion=tabla&id=oae1&t=1">'.$afe[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oae2&t=1">'.$afe[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oae3&t=1">'.$afe[3].'</a></td></tr>'."\n";
	if($afp[3]!='' || $afp[4]!='') { $afp[3] = $afp[3] + $afp[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oap&t=1">En Tránsito Estructurales Completas</a></td><td><a href="reportes.php?accion=tabla&id=oap0&t=1">'.$afp[0].'</a></td><td><a href="reportes.php?accion=tabla&id=oap1&t=1">'.$afp[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oap2&t=1">'.$afp[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oap3&t=1">'.$afp[3].'</a></td></tr>'."\n";
	if($afc[3]!='' || $afc[4]!='') { $afc[3] = $afc[3] + $afc[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oac&t=1">En Tránsito Refacciones Completas</a></td><td><a href="reportes.php?accion=tabla&id=oac0&t=1">'.$afc[0].'</a></td><td><a href="reportes.php?accion=tabla&id=oac1&t=1">'.$afc[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oac2&t=1">'.$afc[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oac3&t=1">'.$afc[3].'</a></td></tr>'."\n";
	if($ate[3]!='' || $ate[4]!='') { $ate[3] = $ate[3] + $ate[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oae&t=0">En Taller Estructurales Pendientes</a></td><td><a href="reportes.php?accion=tabla&id=oae0&t=0">'.$ate[0].'</a></td><td><a href="reportes.php?accion=tabla&id=oae1&t=0">'.$ate[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oae2&t=0">'.$ate[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oae3&t=0">'.$ate[3].'</a></td></tr>'."\n";
	if($atp[3]!='' || $atp[4]!='') { $atp[3] = $atp[3] + $atp[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oap&t=0">En Taller Estructurales Completas</a></td><td><a href="reportes.php?accion=tabla&id=oap0&t=0">'.$atp[0].'</a></td><td><a href="reportes.php?accion=tabla&id=oap1&t=0">'.$atp[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oap2&t=0">'.$atp[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oap3&t=0">'.$atp[3].'</a></td></tr>'."\n";
	if($atc[3]!='' || $atc[4]!='') { $atc[3] = $atc[3] + $atc[4]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oac&t=0">En Taller Refacciones Completas</a></td><td><a href="reportes.php?accion=tabla&id=oac0&t=0">'.$atc[0].'</a></td><td><a href="reportes.php?accion=tabla&id=oac1&t=0">'.$atc[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oac2&t=0">'.$atc[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oac3&t=0">'.$atc[3].'</a></td></tr>'."\n";
	echo '						</table>';
	echo '			</div>'."\n";

	echo '			<div style="clear: both;"></div><div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="40%" style="text-align:left;">SubOrdenes en Proceso de Reparación<br>Semanas de ingreso:</td>
								<td width="10%">1</td>
								<td width="10%">2</td>
								<td width="10%">3</td>
								<td width="10%">4 o más</td>
								<td width="10%">Pausa</td>
								<td width="10%">RP</td>
							</tr>'."\n";
	if($rm[0]!='' || $rm[1]!='') { $rm[1] = $rm[0] + $rm[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=orm">Mecánica</a></td><td><a href="reportes.php?accion=tabla&id=orm1">'.$rm[1].'</a></td><td><a href="reportes.php?accion=tabla&id=orm2">'.$rm[2].'</a></td><td><a href="reportes.php?accion=tabla&id=orm3">'.$rm[3].'</a></td><td><a href="reportes.php?accion=tabla&id=orm4">'.$rm[4].'</a></td><td><a href="reportes.php?accion=tabla&id=ormp">'.$rmp1.'</a></td><td><a href="reportes.php?accion=tabla&id=ormrp">'.$rmrp.'</a></td></tr>'."\n";
	if($rh[0]!='' || $rh[1]!='') { $rh[1] = $rh[0] + $rh[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=orh">Hojalatería</a></td><td><a href="reportes.php?accion=tabla&id=orh1">'.$rh[1].'</a></td><td><a href="reportes.php?accion=tabla&id=orh2">'.$rh[2].'</a></td><td><a href="reportes.php?accion=tabla&id=orh3">'.$rh[3].'</a></td><td><a href="reportes.php?accion=tabla&id=orh4">'.$rh[4].'</a></td><td><a href="reportes.php?accion=tabla&id=orhp">'.$rhp1.'</a></td><td><a href="reportes.php?accion=tabla&id=orhrp">'.$rhrp.'</a></td></tr>'."\n";
	if($rp[0]!='' || $rp[1]!='') { $rp[1] = $rp[0] + $rp[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=orp">Pintura</a></td><td><a href="reportes.php?accion=tabla&id=orp1">'.$rp[1].'</a></td><td><a href="reportes.php?accion=tabla&id=orp2">'.$rp[2].'</a></td><td><a href="reportes.php?accion=tabla&id=orp3">'.$rp[3].'</a></td><td><a href="reportes.php?accion=tabla&id=orp4">'.$rp[4].'</a></td><td><a href="reportes.php?accion=tabla&id=orpp">'.$rpp1.'</a></td><td><a href="reportes.php?accion=tabla&id=orprp">'.$rprp.'</a></td></tr>'."\n";
	if($re[0]!='' || $re[1]!='') { $re[1] = $re[0] + $re[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=ore">Eléctrica</a></td><td><a href="reportes.php?accion=tabla&id=ore1">'.$re[1].'</a></td><td><a href="reportes.php?accion=tabla&id=ore2">'.$re[2].'</a></td><td><a href="reportes.php?accion=tabla&id=ore3">'.$re[3].'</a></td><td><a href="reportes.php?accion=tabla&id=ore4">'.$re[4].'</a></td><td><a href="reportes.php?accion=tabla&id=orep">'.$rep1.'</a></td><td><a href="reportes.php?accion=tabla&id=orerp">'.$rerp.'</a></td></tr>'."\n";
	if($ra[0]!='' || $ra[1]!='') { $ra[1] = $ra[0] + $ra[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=ora">Accesorios</a></td><td><a href="reportes.php?accion=tabla&id=ora1">'.$ra[1].'</a></td><td><a href="reportes.php?accion=tabla&id=ora2">'.$ra[2].'</a></td><td><a href="reportes.php?accion=tabla&id=ora3">'.$ra[3].'</a></td><td><a href="reportes.php?accion=tabla&id=ora4">'.$ra[4].'</a></td><td><a href="reportes.php?accion=tabla&id=orap">'.$rap1.'</a></td><td><a href="reportes.php?accion=tabla&id=orarp">'.$rarp.'</a></td></tr>'."\n";
/*	if($rpa[0]!='' || $rpa[1]!='') { $rpa[1] = $rpa[0] + $rpa[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=orpa">Pausadas</a></td><td><a href="reportes.php?accion=tabla&id=orpa1">'.$rpa[1].'</a></td><td><a href="reportes.php?accion=tabla&id=orpa2">'.$rpa[2].'</a></td><td><a href="reportes.php?accion=tabla&id=orpa3">'.$rpa[3].'</a></td><td><a href="reportes.php?accion=tabla&id=orpa4">'.$rpa[4].'</a></td><td>&nbsp;</td><td>&nbsp;</td></tr>'."\n";
*/
	echo '						</table>';
	echo '			</div>'."\n";

	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">Ordenes por Entregar<br>Semanas de ingreso:</td>
								<td width="10%">1</td>
								<td width="10%">2</td>
								<td width="10%">3</td>
								<td width="10%">4 o más</td>
							</tr>'."\n";
	if($ec[0]!='' || $ec[1]!='') { $ec[1] = $ec[0] + $ec[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oec">Por Entregar </a></td><td><a href="reportes.php?accion=tabla&id=oec1">'.$ec[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oec2">'.$ec[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oec3">'.$ec[3].'</a></td><td><a href="reportes.php?accion=tabla&id=oec4">'.$ec[4].'</a></td></tr>'."\n";
	if($ep[0]!='' || $ep[1]!='') { $ep[1] = $ep[0] + $ep[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oep">Terminado en Taller RP</a></td><td><a href="reportes.php?accion=tabla&id=oep1">'.$ep[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oep2">'.$ep[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oep3">'.$ep[3].'</a></td><td><a href="reportes.php?accion=tabla&id=oep4">'.$ep[4].'</a></td></tr>'."\n";
	if($epf[0]!='' || $epf[1]!='') { $epf[1] = $epf[0] + $epf[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oep&t=1">Terminado en Tránsito RP</a></td><td><a href="reportes.php?accion=tabla&id=oep1&t=1">'.$epf[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oep2&t=1">'.$epf[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oep3&t=1">'.$epf[3].'</a></td><td><a href="reportes.php?accion=tabla&id=oep4&t=1">'.$epf[4].'</a></td></tr>'."\n";
	if($epd[0]!='' || $epd[1]!='') { $epd[1] = $epd[0] + $epd[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oepd">Por Entregar Pago de Daños</a></td><td><a href="reportes.php?accion=tabla&id=oepd1">'.$epd[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oepd2">'.$epd[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oepd3">'.$epd[3].'</a></td><td><a href="reportes.php?accion=tabla&id=oepd4">'.$epd[4].'</a></td></tr>'."\n";
	if($epp[0]!='' || $epp[1]!='') { $epp[1] = $epp[0] + $epp[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oepp">Por Entregar Pago Plus</a></td><td><a href="reportes.php?accion=tabla&id=oepp1">'.$epp[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oepp2">'.$epp[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oepp3">'.$epp[3].'</a></td><td><a href="reportes.php?accion=tabla&id=oepp4">'.$epp[4].'</a></td></tr>'."\n";
	if($ept[0]!='' || $ept[1]!='') { $ept[1] = $ept[0] + $ept[1]; }
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&id=oept">Por Entregar Pérdida Total</a></td><td><a href="reportes.php?accion=tabla&id=oept1">'.$ept[1].'</a></td><td><a href="reportes.php?accion=tabla&id=oept2">'.$ept[2].'</a></td><td><a href="reportes.php?accion=tabla&id=oept3">'.$ept[3].'</a></td><td><a href="reportes.php?accion=tabla&id=oept4">'.$ept[4].'</a></td></tr>'."\n";
	echo '						</table>';
	echo '			</div>'."\n";

	echo '			<div style="clear: both;"></div><div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">Totales recibidos por Mes<br>Mes de ingreso:</td>
								<td width="10%">'.$etiqmes[0].'</td>
								<td width="10%">'.$etiqmes[1].'</td>
								<td width="10%">'.$etiqmes[2].'</td>
								<td width="10%">'.$etiqmes[3].'</td>
							</tr>'."\n";

	$uptot = $up[0] + $up[1] + $up[2] + $up[3];
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;">En proceso: '.$uptot.'</a></td><td><a>'.$up[0].'</a></td><td><a>'.$up[1].'</a></td><td><a>'.$up[2].'</a></td><td><a>'.$up[3].'</a></td></tr>'."\n";
	$toto = $to[0] + $to[1] + $to[2] + $to[3];
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;">Totales: '.$toto.'</a></td><td><a>'.$to[0].'</a></td><td><a>'.$to[1].'</a></td><td><a>'.$to[2].'</a></td><td><a>'.$to[3].'</a></td></tr>'."\n";
	echo '						</table>';
	echo '			</div>'."\n";

	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">Recibidos por Tipo de Cliente<br>Mes de ingreso:</td>
								<td width="10%">'.$etiqmes[0].'</td>
								<td width="10%">'.$etiqmes[1].'</td>
								<td width="10%">'.$etiqmes[2].'</td>
								<td width="10%">'.$etiqmes[3].'</td>
							</tr>'."\n";
	foreach($ase as $k => $v) {
		$asegut = $asegu[$k][0] + $asegu[$k][1] + $asegu[$k][2] + $asegu[$k][3];
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=aseguradora&im=asegu&a='.$k.'">' . $v . ': '.$asegut.'</a></td><td><a href="reportes.php?accion=aseguradora&im=asegu0&a='.$k.'">'.$asegu[$k][0].'</a></td><td><a href="reportes.php?accion=aseguradora&im=asegu1&a='.$k.'">'.$asegu[$k][1].'</a></td><td><a href="reportes.php?accion=aseguradora&im=asegu2&a='.$k.'">'.$asegu[$k][2].'</a></td><td><a href="reportes.php?accion=aseguradora&im=asegu3&a='.$k.'">'.$asegu[$k][3].'</a></td></tr>'."\n";
//		echo '							<tr class="bloque_fila"><td style="text-align:left;">' . $v . ': '.$asegut.'</td><td>'.$asegu[$k][0].'</td><td>'.$asegu[$k][1].'</td><td>'.$asegu[$k][2].'</td><td>'.$asegu[$k][3].'</td></tr>'."\n";
	}
	echo '						</table>';
	echo '			</div>'."\n";

	echo '			<div style="clear: both;"></div><div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">Entregas Totales por Mes<br>Mes de entrega:</td>
								<td width="10%">'.$etiqmes[0].'</td>
								<td width="10%">'.$etiqmes[1].'</td>
								<td width="10%">'.$etiqmes[2].'</td>
								<td width="10%">'.$etiqmes[3].'</td>
							</tr>'."\n";
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" >Entregados</a></td><td><a>'.$ce[0].'</a></td><td><a>'.$ce[1].'</a></td><td><a>'.$ce[2].'</a></td><td><a href="reportes.php?accion=tabla&im=oce3">'.$ce[3].'</a></td></tr>'."\n";
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" >Pago de daños</a></td><td><a href="reportes.php?accion=tabla&im=ocpd0">'.$cpd[0].'</a></td><td><a >'.$cpd[1].'</a></td><td><a >'.$cpd[2].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpd3">'.$cpd[3].'</a></td></tr>'."\n";
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" >Pago Plus</a></td><td><a href="reportes.php?accion=tabla&im=ocpp0">'.$ocpp[0].'</a></td><td><a >'.$ocpp[1].'</a></td><td><a >'.$ocpp[2].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpp3">'.$cpp[3].'</a></td></tr>'."\n";
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" >Pérdida total</a></td><td><a href="reportes.php?accion=tabla&im=ocpt0">'.$cpt[0].'</a></td><td><a >'.$cpt[1].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpt2">'.$cpt[2].'</a></td><td><a >'.$cpt[3].'</a></td></tr>'."\n";
/*
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&im=oce">Entregados</a></td><td><a href="reportes.php?accion=tabla&im=oce0">'.$ce[0].'</a></td><td><a href="reportes.php?accion=tabla&im=oce1">'.$ce[1].'</a></td><td><a href="reportes.php?accion=tabla&im=oce2">'.$ce[2].'</a></td><td><a href="reportes.php?accion=tabla&im=oce3">'.$ce[3].'</a></td></tr>'."\n";
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&im=ocpd">Pago de daños</a></td><td><a href="reportes.php?accion=tabla&im=ocpd0">'.$cpd[0].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpd1">'.$cpd[1].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpd2">'.$cpd[2].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpd3">'.$cpd[3].'</a></td></tr>'."\n";
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&im=ocpp">Pago Plus</a></td><td><a href="reportes.php?accion=tabla&im=ocpp0">'.$ocpp[0].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpp1">'.$ocpp[1].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpp2">'.$ocpp[2].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpp3">'.$cpp[3].'</a></td></tr>'."\n";
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&im=ocpt">Pérdida total</a></td><td><a href="reportes.php?accion=tabla&im=ocpt0">'.$cpt[0].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpt1">'.$cpt[1].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpt2">'.$cpt[2].'</a></td><td><a href="reportes.php?accion=tabla&im=ocpt3">'.$cpt[3].'</a></td></tr>'."\n";
*/
	echo '						</table>';
	echo '			</div>'."\n";

	echo '			<div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">Entregados por Tipo de Cliente<br>Mes de:</td>
								<td width="10%">'.$etiqmes[0].'</td>
								<td width="10%">'.$etiqmes[1].'</td>
								<td width="10%">'.$etiqmes[2].'</td>
								<td width="10%">'.$etiqmes[3].'</td>
							</tr>'."\n";
	foreach($ase as $k => $v) {
		$aseent = $aseen[$k][0] + $aseen[$k][1] + $aseen[$k][2] + $aseen[$k][3];
		echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=aseguradora&im=aseen&a='.$k.'">' . $v . ': '.$aseent.'</a></td><td><a href="reportes.php?accion=aseguradora&im=aseen0&a='.$k.'">'.$aseen[$k][0].'</a></td><td><a href="reportes.php?accion=aseguradora&im=aseen1&a='.$k.'">'.$aseen[$k][1].'</a></td><td><a href="reportes.php?accion=aseguradora&im=aseen2&a='.$k.'">'.$aseen[$k][2].'</a></td><td><a href="reportes.php?accion=aseguradora&im=aseen3&a='.$k.'">'.$aseen[$k][3].'</a></td></tr>'."\n";
//		echo '							<tr class="bloque_fila"><td style="text-align:left;">' . $v . ': '.$aseent.'</td><td>'.$aseen[$k][0].'</td><td>'.$aseen[$k][1].'</td><td>'.$aseen[$k][2].'</td><td>'.$aseen[$k][3].'</td></tr>'."\n";
	}
	echo '						</table>';
	echo '			</div>'."\n";

	echo '			<div style="clear: both;"></div><div class="bloque_1">'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="1" width="100%" class="bloque_fila centrado">
							<tr class="cabeza_tabla">
								<td width="60%" style="text-align:left;">Ubicación de Autos<br>Mes de ingreso:</td>
								<td width="10%">Este<br>mes</td>
								<td width="10%">1<br>mes</td>
								<td width="10%">2<br>meses</td>
								<td width="10%">3 o<br>más</td>
							</tr>'."\n";
	$upft = $upf[0] + $upf[1] + $upf[2] + $upf[3];
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&im=oupft&t=1">En Tránsito: '.$upft.'</a></td><td><a href="reportes.php?accion=tabla&im=oupft0">'.$upf[0].'</a></td><td><a href="reportes.php?accion=tabla&im=oupft1">'.$upf[1].'</a></td><td><a href="reportes.php?accion=tabla&im=oupft2">'.$upf[2].'</a></td><td><a href="reportes.php?accion=tabla&im=oupft3">'.$upf[3].'</a></td></tr>'."\n";
	$uptt = $upt[0] + $upt[1] + $upt[2] + $upt[3];
	echo '							<tr class="bloque_fila"><td style="text-align:left;"><a style="font-size: 1.0em; text-decoration: underline;" href="reportes.php?accion=tabla&im=ouptt">En Taller: '.$uptt.'</a></td><td><a href="reportes.php?accion=tabla&im=ouptt0">'.$upt[0].'</a></td><td><a href="reportes.php?accion=tabla&im=ouptt1">'.$upt[1].'</a></td><td><a href="reportes.php?accion=tabla&im=ouptt2">'.$upt[2].'</a></td><td><a href="reportes.php?accion=tabla&im=ouptt3">'.$upt[3].'</a></td></tr>'."\n";
	echo '						</table>';
	echo '			</div>'."\n";

//	print_r($v);
	
} 


if ($_SESSION['codigo']!='70' && $_SESSION['codigo'] < '2000') {

// página default 

//	echo $num_cols;
		echo '				<div style="clear: both;"></div><table cellspacing="0" cellpadding="2" border="1" class="avisos" >'."\n";
		echo '					<tr>
						<td colspan="2" valign="top">';
		echo '							<form action="index.php?accion=selecciona" method="post" enctype="multipart/form-data" name="filtro"> 
								Por grupo: <select name="rol" size="0" onchange="document.filtro.submit()";>
											<option value="">Seleccione ...</option>
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
										</select>&nbsp;&nbsp;
								Por estatus: <select name="estatus" size="0" onchange="document.filtro.submit()";>
											<option value="">Seleccione ...</option>
											<option value="17">' . ORDEN_ESTATUS_17 . '</option>
											<option value="0">' . ORDEN_ESTATUS_0 . '</option>
											<option value="1">' . ORDEN_ESTATUS_1 . '</option>
											<option value="24">' . ORDEN_ESTATUS_24 . '</option>
											<option value="25">' . ORDEN_ESTATUS_25 . '</option>
											<option value="26">' . ORDEN_ESTATUS_26 . '</option>
											<option value="27">' . ORDEN_ESTATUS_27 . '</option>
											<option value="28">' . ORDEN_ESTATUS_28 . '</option>
											<option value="20">' . ORDEN_ESTATUS_20 . '</option>
											<option value="2">' . ORDEN_ESTATUS_2 . '</option>
											<option value="3">' . ORDEN_ESTATUS_3 . '</option>
											<option value="4">' . ORDEN_ESTATUS_4 . '</option>
											<option value="5">' . ORDEN_ESTATUS_5 . '</option>
											<option value="6">' . ORDEN_ESTATUS_6 . '</option>
											<option value="7">' . ORDEN_ESTATUS_7 . '</option>
											<option value="8">' . ORDEN_ESTATUS_8 . '</option>
											<option value="9">' . ORDEN_ESTATUS_9 . '</option>
											<option value="10">' . ORDEN_ESTATUS_10 . '</option>
											<option value="11">' . ORDEN_ESTATUS_11 . '</option>
											<option value="21">' . ORDEN_ESTATUS_21 . '</option>
											<option value="12">' . ORDEN_ESTATUS_12 . '</option>
											<option value="22">' . ORDEN_ESTATUS_22 . '</option>
											<option value="23">' . ORDEN_ESTATUS_23 . '</option>
											<option value="13">' . ORDEN_ESTATUS_13 . '</option>
											<option value="14">' . ORDEN_ESTATUS_14 . '</option>
											<option value="15">' . ORDEN_ESTATUS_15 . '</option>
											<option value="16">' . ORDEN_ESTATUS_16 . '</option>
											<option value="30">' . ORDEN_ESTATUS_30 . '</option>
											<option value="31">' . ORDEN_ESTATUS_31 . '</option>
											<option value="90">' . ORDEN_ESTATUS_90 . '</option>
											<option value="97">' . ORDEN_ESTATUS_97 . '</option>
											<option value="98">' . ORDEN_ESTATUS_98 . '</option>
											<option value="99">' . ORDEN_ESTATUS_99 . '</option>
										</select>&nbsp;&nbsp;' . "\n";
		echo '								<input type="submit" name="enviar" value="Enviar" />';
		echo '							</form>
						</td>
					</tr>' . "\n";
		if(isset($rol) && $rol!='') {
			echo '					<tr><td colspan="2">Ordenes de Trabajo del Grupo ' . $codigos[$rol][0] . '</td></tr>' . "\n";
			$pregunta = "SELECT a.al_codigo, a.al_preventivo, a.al_critico, a.al_vista, a.al_categoria, o.* FROM " . $dbpfx . "alertas a, " . $dbpfx . "ordenes o WHERE o.orden_estatus = a.al_estatus AND o.orden_categoria = a.al_categoria AND ";
			if($rol == '50') { 
				$pregunta .= "o.orden_ref_pendientes > '0' ";
			} else {
				$pregunta .= "al_codigo ='" . $codigos[$rol][1] . "' ";
			}
			$pregunta .= "ORDER BY o.orden_id DESC";
		}
		elseif(isset($estatus) && $estatus!='') {
			echo '					<tr><td colspan="2">Ordenes de Trabajo en Estatus ' . constant('ORDEN_ESTATUS_' . $estatus) . '</td></tr>' . "\n";
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
				$pregunta .= "o.orden_ref_pendientes > '0' ";
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
					if($_SESSION['codigo']=='30' && $alerta['orden_asesor_id']!=$_SESSION['usuario'] && $alerta['orden_estatus']!='17' && $alerta['orden_estatus']!='0' && $rol!='' && $estatus!='') {
					} else {
						if($c==0) { echo '								<tr class="' . $fondo . '">'; }
						echo "\n" . '								<td><table><tr>';
						echo '<td rowspan ="2"><a href="ordenes.php?accion=consultar&'."\n";
						if($alerta['orden_estatus']=='17' && ($multiorden == 1 || $confolio == 1)) {
							echo 'oid=' . $alerta['oid'];
						} else {
							echo 'orden_id=' . $alerta['orden_id'];
						}
						echo '">' . constant('ALARMA_' . $alerta['orden_alerta']) . '</a></td>';
						echo '<td class="bloque"><a href="ordenes.php?accion=consultar&'."\n";
						if($alerta['orden_estatus']=='17' && ($multiorden == 1 || $confolio == 1)) {
							echo 'oid=' . $alerta['oid'];
						} else {
							echo 'orden_id=' . $alerta['orden_id'];
						}
						echo '">' . $alerta['orden_vehiculo_tipo'] . '&nbsp;' . $alerta['orden_vehiculo_color'] . '&nbsp;' . $alerta['orden_vehiculo_placas'] . '</a></td></tr>' . "\n";
						echo '<tr><td><strong>OT: ' . $alerta['orden_id'] . '</strong>&nbsp;' . constant('ORDEN_ESTATUS_' . $alerta['orden_estatus']);
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
						echo "\n" . '								<td><table><tr>';
						echo '<td rowspan ="2"><a href="ordenes.php?accion=consultar&orden_id=' . $alerta['orden_id'] . '">' . constant('ALARMA_' . $alerta['orden_alerta']) . '</a></td>';
						echo '<td class="bloque"><a href="ordenes.php?accion=consultar&orden_id=' . $alerta['orden_id'] . '">' . $alerta['orden_vehiculo_tipo'] . '&nbsp;' . $alerta['orden_vehiculo_color'] . '&nbsp;' . $alerta['orden_vehiculo_placas'] . '</a></td></tr>' . "\n";
						echo '<tr><td><strong>OT: ' . $alerta['orden_id'] . '</strong>&nbsp;' . constant('ORDEN_ESTATUS_' . $alerta['orden_estatus']);
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