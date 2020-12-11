<?php 
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/factura.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

	if($feini == '') {
		$feini = date('Y-m-01 00:00:00');
		$fefin = date('Y-m-t 23:59:59');
	} else {
		$feini = date('Y-m-01 00:00:00', strtotime($feini));
		$fefin = date('Y-m-t 23:59:59', strtotime($feini));
	}

	$num_rep = 0; $mensaje = 'No hay conceptos por facturar.';	
	$preg0 = "SELECT orden_id, orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_fecha_recepcion >= '" . $feini . "' AND orden_fecha_recepcion <= '" . $fefin . "'";
//	echo $preg0;
	$mat0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!".$preg0);
	$num_rep = mysql_num_rows($mat0);

	if ($num_rep > 0) {
		
		while($ord = mysql_fetch_array($mat0)) {
			if($ordenini == '') { $ordenini = $ord['orden_id']; }
			$ordenfin = $ord['orden_id'];
			if($ord['orden_estatus'] < 30 || $ord['orden_estatus'] == 99) {
				$ordcob++;
			} else {
				$excluidas++;
			}
		}
		$periodo = date('j', strtotime($feini)) . ' al ' . date('t', strtotime($fefin)) . ' de ' . $mes[date('m', strtotime($fefin))] . ' del ' . date('Y', strtotime($fefin));  
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xml .= '	<Prefactura periodo="' . $periodo . '" rfc="' . $agencia_rfc . '" cliente="' . $agencia_razon_social . '" calle="' . $agencia_calle . '" numext="' . $agencia_numext . '" numint="' . $agencia_numint . '" colonia="' . $agencia_colonia . '" municipio="' . $agencia_municipio . '" codigopostal="' . $agencia_cp . '" estado="' . $agencia_estado . '" pais="' . $agencia_pais . '" ordenini="' . $ordenini . '" ordenfin="' . $ordenfin . '" ordcob="' . $ordcob . '" excluidas="' . $excluidas . '" >'."\n";
		$xml .= '	</Prefactura>'."\n";
//		echo $xml;
		$instancia = strtoupper(substr($dbpfx, 0, -1));
		$xmlnom = date('Ymd', strtotime($feini)) . '-' . $instancia . '.xml';
//		echo $xmlnom;
		if(file_exists(DIR_DOCS . $xmlnom)) {
			rename(DIR_DOCS . $xmlnom, DIR_DOCS . $xmlnom . time());
		}
		file_put_contents(DIR_DOCS . $xmlnom, $xml);
//		redirigir(DIR_DOCS . $xmlnom);
	} else {
		echo 'No hay OT en este periodo.';
	}
?>