<?php
foreach($_GET as $k => $v){$$k=$v;}  // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
//include('idiomas/' . $idioma . '/presupuestos.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

//  ----------------  obtener nombres de usuarios   ------------------- 

        $consulta = "SELECT nombre, apellidos, usuario FROM " . $dbpfx . "usuarios WHERE acceso = '0'";
        $arreglo = mysql_query($consulta) or die("ERROR: Fallo selección de asesores!");
        while ($ases = mysql_fetch_array($arreglo)) {
                $usuario[$ases['usuario']] = $ases['nombre'] . ' ' . $ases['apellidos'];
        }

//  ----------------  nombres de asesores   ------------------- 

$preg = "SELECT orden_id, orden_vehiculo_id, orden_asesor_id, orden_grua, orden_servicio, orden_estatus, orden_fecha_recepcion, orden_fecha_de_entrega FROM " . $dbpfx . "ordenes WHERE orden_id >= '$orden' LIMIT 500";
$matr = mysql_query($preg) or die("ERROR: Fallo selección de OTs! " . $preg);
// echo $preg;

echo '<table cellpadding = "2" border="1">'."\n";
echo '<tr><td>OT</td><td>SINIESTRO</td><td>PLACAS</td><td>MARCA</td><td>SUB MARCA</td><td>MODELO</td><td>GRUA</td><td>FECHA DE INGRESO</td><td>ESTATUS</td><td>ASESOR DE SERVICIO</td><td>FECHA DE VALUACION</td><td>FECHA ENTREGADO</td><td>DEDUCIBLE</td><td>MONTO DE REPARACION</td><td>MANO DE OBRA HyM</td><td>MANO DE OBRA PINTURA</td><td>MATERIALES PINTURA</td><td>REFACCIONES RED</td><td>REFACCIONES OTROS</td><td>NUM FACTURA</td><td>FECHA FACTURA</td></tr>'."\n";

while ($ord = mysql_fetch_array($matr)) {
	$preg0 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_aseguradora > 0 AND sub_estatus < '190' GROUP BY sub_reporte";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de siniestros! " . $preg0);
//	echo $preg0 . '<br>';
	while ($gsub = mysql_fetch_array($matr0)) {
		$dedu = 0; $fevalaut = 0; $mo7 = 0; $mochm = 0; $partes = 0; $consumibles = 0; $paraseg = 0; $fe_emi = ''; $fact_num = '';
		unset($mo, $cons, $part, $ccase);
		$preg1 = "SELECT sub_orden_id, sub_area, sub_fecha_valaut, sub_deducible FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $gsub['sub_reporte'] . "' AND orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '130'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de deducible! " . $preg1);
//		echo $preg1 . '<br>';
		while ($sub = mysql_fetch_array($matr1)) {
			if($sub['sub_deducible'] > $dedu) { $dedu = $sub['sub_deducible']; }
			if(strtotime($sub['sub_fecha_valaut']) > $fevalaut) { $fevalaut = strtotime($sub['sub_fecha_valaut']); }
			$preg2 = "SELECT op_cantidad, op_precio, op_tangible, op_autosurtido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_pres IS NULL";
//			echo $preg2 . '<br>';
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos! " . $preg2);
			while ($op = mysql_fetch_array($matr2)) {
				if($op['op_tangible'] == 2) {
					$cons[$sub['sub_area']] = $cons[$sub['sub_area']] + round(($op['op_cantidad'] * $op['op_precio']),2);
				} elseif($op['op_tangible'] == 1) {
					if($op['op_autosurtido'] == 1) {
						$ccase[$sub['sub_area']] = $ccase[$sub['sub_area']] + round(($op['op_cantidad'] * $op['op_precio']),2);
					} else {
						$part[$sub['sub_area']] = $part[$sub['sub_area']] + round(($op['op_cantidad'] * $op['op_precio']),2);
					}
				} else {
					$mo[$sub['sub_area']] = $mo[$sub['sub_area']] + round(($op['op_cantidad'] * $op['op_precio']),2);
				}
			}
		}

		if($fevalaut > 1000000) { $fevalaut = date('Y-m-d', $fevalaut); } else { $fevalaut = '--'; }
		$fent = strtotime($ord['orden_fecha_de_entrega']);
		if($fent > 1000000) { $fent = date('Y-m-d', $fent); } else { $fent = '--'; }

		foreach($mo as $area => $monto) {
			if($area == '7') { $mo7 = $mo7 + $monto; }
			else { $mochm = $mochm + $monto; }
		}
		foreach($cons as $area => $monto) {
			$consumibles = $consumibles + $monto;
		}
		foreach($part as $area => $monto) {
			$partes = $partes + $monto; 
		}
		foreach($ccase as $area => $monto) {
			$paraseg = $paraseg + $monto; 
		}
		$preg3 = "SELECT fact_num, fact_fecha_emision, fact_cobrada FROM " . $dbpfx . "facturas_por_cobrar WHERE orden_id = '" . $ord['orden_id'] . "' AND reporte = '" . $gsub['sub_reporte'] ."' AND fact_cobrada < '2' AND fact_tipo < '2'";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de facturas! " . $preg3);
		$fact_num = '';
		while ($fact = mysql_fetch_array($matr3)) {
			if($fact_num != '') { $fact_num .= ', '; }
			$fact_num .= $fact['fact_num'];
			if($fe_emi != '') { $fe_emi .= ', '; }
			$fe_emi .= date('Y-m-d', strtotime($fact['fact_fecha_emision']));
		}
		$veh = datosVehiculo($ord['orden_id'], $dbpfx);
		echo '	<tr><td>' . $ord['orden_id'] . '</td><td>' . $gsub['sub_reporte'] . '</td><td>' . $veh['placas'] . '</td><td>' . $veh['marca'] . '</td><td>' . $veh['tipo'] . '</td><td>' . $veh['modelo'] . '</td><td>';
		if($ord['orden_grua'] == 1) { echo 'Sí'; }
		elseif($ord['orden_grua'] == 2) { echo 'No'; }
		else { echo '--'; }
		echo '</td><td>' . date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])) . '</td><td>' . constant('ORDEN_ESTATUS_'.$ord['orden_estatus']) . '</td><td>' . $usuario[$ord['orden_asesor_id']] . '</td><td>' . $fevalaut . '</td><td>' . $fent . '</td><td>' . $dedu . '</td><td>' . ($mo7 + $mochm + $consumibles + $partes) . '</td><td>' . $mochm . '</td><td>' . $mo7 . '</td><td>' . $consumibles . '</td><td>' . $paraseg . '</td><td>' . $partes . '</td><td>' . $fact_num . '</td><td>' . $fe_emi . '</td></tr>'."\n";
	}
}
echo '</table>';
?>
