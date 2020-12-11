<?php
foreach($_POST as $k => $v){$$k=$v;} //  echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';

include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

    if(isset($feini) && $feini != '0000-00-00') {
		$feini = date('Y-m-d 00:00:00', strtotime($feini));
		$prega = " orden_fecha_recepcion > '" . $feini . "'";
		$t_ini = strftime('%e de %B del %Y', strtotime($feini));
		if(!isset($fefin) || $fefin == '' || $fefin == '0000-00-00') {
			$fefin = date('Y-m-d 23:59:59', time());
		}
		$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
		$prega .= " AND orden_fecha_recepcion < '" . $fefin . "' ";
		$t_fin = strftime('%e de %B del %Y', strtotime($fefin));
	} else {
		$feini = date('Y-m-01 00:00:00');
		$fefin = date('Y-m-t 23:59:59');
		$prega = " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "' ";
		$t_ini = strftime('%e de %B del %Y', strtotime($feini));
		$t_fin = strftime('%e de %B del %Y', strtotime($fefin));
	}

    //  ----------------  obtener nombres de aseguradoras	------------------- 

	$consulta = "SELECT aseguradora_id, aseguradora_logo, aseguradora_nic, aseguradora_razon_social FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_nic";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
	while ($aseg = mysql_fetch_array($arreglo)) {
		$ase[$aseg['aseguradora_id']][0] = $aseg['aseguradora_logo'];
		$ase[$aseg['aseguradora_id']][1] = $aseg['aseguradora_nic'];
		$ase[$aseg['aseguradora_id']][2] = $aseg['aseguradora_razon_social'];
	}
	$ase[0][0] = 'particular/logo-particular.png';
	$ase[0][1] = 'Particular';
	$ase[0][2] = 'Particular';
    

include('parciales/encabezado.php');
echo '	<div id="body">';
include('parciales/menu_inicio.php');
echo '	
            <div id="principal">';

    echo '		<form action="aaa-finanzas-extendido.php" method="post" enctype="multipart/form-data" name="filtrorep">
		<table cellpadding="0" cellspacing="0" border="0" width="80%" style="clear:left;">'."\n";

	require_once("calendar/tc_calendar.php");
	echo '					<tr><td style="vertical-align:top;">Fecha de Inicio<br>';
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
	echo '</td><td style="vertical-align:top;">Fecha de Fin<br>';
		//instantiate class and set properties
	$myCalendar = new tc_calendar("fefin", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar.gif");
	$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
//	$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2011, 2020);
	$myCalendar->setAutoHide(true, 5000);

		//output the calendar
	$myCalendar->writeScript();
	echo '</td><td style="vertical-align:top;">'."\n";

    echo 'Filtra por Estatus<br>';
		echo '							<select name="estatusflt" size="1">'."\n";
		echo '								<option value="0"';
		if($estatusflt == '0') { echo ' selected '; }
		echo '>Todos los Recibidos</option>'."\n";
		echo '								<option value="1"';
		if($estatusflt == '1') { echo ' selected '; }
		echo '>En documentación</option>'."\n";
		echo '								<option value="2"';
		if($estatusflt == '2') { echo ' selected '; }
		echo '>En reparación</option>'."\n";
		echo '								<option value="3"';
		if($estatusflt == '3') { echo ' selected '; }
		echo '>Terminados</option>'."\n";
		echo '								<option value="4"';
		if($estatusflt == '4') { echo ' selected '; }
		echo '>Entregados</option>'."\n";
		echo '								<option value="5"';
		if($estatusflt == '5') { echo ' selected '; }
		echo '>Cobrados</option>'."\n";
		echo '								<option value="6"';
		if($estatusflt == '6') { echo ' selected '; }
		echo '>Facturados No cobrados</option>'."\n";
		echo '								<option value="7"';
		if($estatusflt == '7') { echo ' selected '; }
		echo '>Cobrables No Facturados</option>'."\n";
		echo '							</select>'."\n";

        echo '<br>Filtra por Cliente';
		echo '							<select name="asegflt" size="1" onchange="document.filtrorep.submit()";>'."\n";
		echo '								<option value=""';
		if($asegflt == '') { echo ' selected '; }
		echo '>Todos los Clientes</option>'."\n";
		foreach($ase as $ka => $va) {
			echo '								<option value="' . $ka . '"';
			if($asegflt == '' . $ka . '') { echo ' selected '; }
			echo '>' . $va[1] . '</option>'."\n";
		}
		echo '							</select><br>'."\n";

        	echo '						</td></tr>
				<tr><td colspan="3">
					<input type="hidden" name="accion" value="' . $accion . '" />
					<input type="hidden" name="nomrep" value="' . $nomrep . '" />
					<input type="hidden" name="ordenar" value="' . $ordenar . '" />
					<input type="submit" value="Enviar" />
				</td></tr></table></form>'."\n";


    echo '<br>' . $estatusflt;
    echo '<br>' . $asegflt;
    echo '<br>' . $feini;
    echo '<br>' . $fefin;
     

    // --- Logica ---
    if($estatusflt >= '5' && $estatusflt <= '7') {
		$preg0 = "SELECT o.orden_id, o.orden_estatus, o.orden_vehiculo_placas, o.orden_vehiculo_tipo, o.orden_vehiculo_marca, o.orden_vehiculo_color, o.orden_vehiculo_id, o.orden_alerta, o.orden_asesor_id, o.orden_fecha_recepcion, o.orden_fecha_de_entrega, o.orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes o";
	} else {
		$preg0 = "SELECT orden_id, orden_estatus, orden_vehiculo_placas, orden_vehiculo_tipo, orden_vehiculo_marca, orden_vehiculo_color, orden_vehiculo_id, orden_alerta, orden_asesor_id, orden_fecha_recepcion, orden_fecha_de_entrega, orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes WHERE ";
	}

	if($estatusflt == '5') {
		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '1' AND f.fact_tipo < '3' AND f.fact_fecha_cobrada >= '" . $feini . "' AND f.fact_fecha_cobrada <= '" . $fefin . "' AND (o.orden_estatus < '30' OR o.orden_estatus = '99') "; 
	} elseif($estatusflt == '6') {
        
//		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '0' AND f.fact_tipo < '3' AND f.fact_fecha_emision >= '" . $feini . "' AND f.fact_fecha_emision <= '" . $fefin . "' AND (o.orden_estatus < '30' OR o.orden_estatus = '99') ";
		$preg0 .= ", " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_cobrada = '0' AND f.fact_tipo < '3' ";
        
	} elseif($estatusflt == '7') {
		$preg0 .= " WHERE NOT EXISTS ( SELECT null FROM " . $dbpfx . "facturas_por_cobrar f WHERE o.orden_id = f.orden_id AND f.fact_tipo < '3' ) AND ((o.orden_estatus >= '12' AND o.orden_estatus <= '16') OR o.orden_estatus = '99') AND o.orden_fecha_ultimo_movimiento >= '" . $feini . "' AND o.orden_fecha_ultimo_movimiento <= '" . $fefin . "' "; 
	} elseif($estatusflt == '4') { 
		$preg0 .= " orden_fecha_de_entrega > '" . $feini . "' AND orden_fecha_de_entrega < '" . $fefin . "' AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Entregados Autorizados'; 
	} elseif($estatusflt == '3') { 
		$preg0 .= " orden_fecha_ultimo_movimiento > '" . $feini . "' AND orden_fecha_ultimo_movimiento < '" . $fefin . "'  AND (orden_estatus >= '12' AND orden_estatus <= '15') ";
		$nomrep = 'Terminados';
	} elseif($estatusflt == '2') { 
		$preg0 .= " orden_fecha_ultimo_movimiento > '" . $feini . "' AND orden_fecha_ultimo_movimiento < '" . $fefin . "'  AND ((orden_estatus >= '4' AND orden_estatus <= '11') OR orden_estatus = '21') ";
		$nomrep = 'Reparación'; 
	} elseif($estatusflt == '1') { 
		$preg0 .= " orden_fecha_ultimo_movimiento > '" . $feini . "' AND orden_fecha_ultimo_movimiento < '" . $fefin . "'  AND (orden_estatus = '2' OR orden_estatus = '20' OR orden_estatus = '28' OR orden_estatus = '29') ";
		$nomrep = 'Por Autorizar'; 
	} elseif($estatusflt == '0') { 
		$preg0 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'  AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Todos los Recibidos'; 
	} else {
		$preg0 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "'  AND (orden_estatus < '30' OR orden_estatus = '99') ";
		$nomrep = 'Todos los Recibidos'; 
	}

//	echo $preg0;
	
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de lapso!".$preg0);
	$filas = mysql_num_rows($matr0);	
	$encabezado = ' OTs ' . $nomrep . ' del ' . $t_ini . ' al ' . $t_fin;

	echo '			<table cellspacing="1" cellpadding="2" border="0">' . "\n";
	echo '				<tr class="cabeza_tabla"><td colspan="19" style="text-align: right;">';
	if($asegflt != ""){
		echo '<img src="' . $ase[$asegflt][0] . '"/>';
	}
	echo	$encabezado . '</td></tr>' . "\n";
	echo '				
        <tr>
            <td>OT</td>
            <td>Siniestro</td>
            <td>Vehículo</td>
            <td>Modelo</td>
            <td>Marca</td>
            <td>Placas</td>
            <td>Estatus</td>
            <td>Fecha Recepción</td>
            <td>Fecha Entrega</td>
            <td>Fecha Termino</td>
            <td>Total Valuación</td>
            <td>Venta Refacciones</td>
            <td>MO Hojalateria</td>
            <td>MO Mecánica</td>
            <td>MO Pintura</td>
            <td>Materiales</td>
            <td>Constante Materiales</td>
            <td>Costo Refacciones</td>
            <td>Costo RED GNP</td>
            <td>Costo Sajiro</td>
            <td>Costo Materiales</td>
            <td>Costo Constante Materiales</td>
            <td>Destajo Hojalatería</td>
            <td>Destajo Pintura</td>
            <td>Valor Factura antes IVA</td>
            <td>Fecha de Pago</td>
        </tr>'."\n";

	$fondo = 'claro';
	$j = 0;
	$totpres = 0; $totpart = 0; $totsin = 0; $numpart = 0; $numsin = 0; $pvppart = 0; $pvpsin = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	$total_x_busqueda = 0;
	while($ord = mysql_fetch_array($matr0)) {
		$preg2 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
		if($asegflt != '') {
			$preg2 .= " AND sub_aseguradora = '" . $asegflt . "' ";
		}
		$preg2 .= " GROUP BY sub_reporte";
//		echo '<br>' . $preg2;
		$matr2 = mysql_query($preg2);
		$veh = datosVehiculo($ord['orden_id'], $dbpfx);
		while($gsub = mysql_fetch_array($matr2)) {
			$total_x_busqueda = $total_x_busqueda + 1;
			echo '				
            <tr class="' . $fondo . '">
					<td style="padding-left:10px; padding-right:10px; text-align:center;">' . $ord['orden_id'] . '</td><td>';

			if($gsub['sub_reporte'] == '0' || $gsub['sub_reporte'] == '') {
				echo 'Particular'; 
				$numpart++; 
				$gsub['sub_reporte'] = '0';
			} else { 
				echo $gsub['sub_reporte']; 
				$numsin++;
			}
			echo '</td>';

         echo '
                    <td>' . strtoupper($ord['orden_vehiculo_tipo']) . '</td><td>' . strtoupper($veh['modelo']) . '</td><td>' . strtoupper($ord['orden_vehiculo_marca']) . '</td><td>' . $ord['orden_vehiculo_placas'] . '</td><td style="padding-left:10px; padding-right:10px;">' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '</td>';
			echo '<td>' . $ord['orden_fecha_recepcion'] . '</td><td>' . $ord['orden_fecha_de_entrega'] . '</td>';

			$preg3 = "SELECT sub_orden_id, sub_area, sub_deducible, sub_presupuesto, sub_partes, sub_consumibles, sub_mo, sub_fecha_terminado FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $gsub['sub_reporte'] . "' AND orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
//			echo '<br>' . $preg3;
			$matr3 = mysql_query($preg3) or die("Falló selección de subordenes");
			$pres = 0; $partes = 0; $cons = 0; $mo = 0; $constmat = 0; $precmat = 0;
			$nppe = 0; $cppe = 0; $cpm = 0; $npm = 0;
			$dedu = 0; $fila5 = 0; $opped = 0; $occ = 1; $fe_term = '';
			$costcons = 0; $costtot = 0; $costref = 0; $costdest = 0;
			unset($mo);
			while($sub = mysql_fetch_array($matr3)) {
				$pres = $pres + $sub['sub_presupuesto'];
				$partes = $partes  + $sub['sub_partes'];
				$cons = $cons  + $sub['sub_consumibles'];
				$mo[$sub['sub_area']] = $mo[$sub['sub_area']]  + $sub['sub_mo'];
				if(strtotime($sub['sub_fecha_terminado']) > strtotime($fe_term)) { $fe_term = $sub['sub_fecha_terminado']; }
				if($sub['sub_deducible'] > $dedu) { $dedu = $sub['sub_deducible']; } 
				$preg5 = "SELECT op_id, op_cantidad, op_costo, op_precio, op_nombre, op_pedido, op_autosurtido, op_pres, op_item_seg, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '".$sub['sub_orden_id']."' ";
// ------ Filtrado de refacciones por código de producto ---------------
				if($fltprodcod == '1') { $preg5 .= " AND (op_codigo IS NULL OR op_codigo NOT LIKE '%PAG%')"; }
// ------
				$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden_productos 3! " . $preg5);
//				echo '<br>' . $preg5;
				$fila6 = mysql_num_rows($matr5);
				$fila5 = $fila5 + $fila6;
				if(mysql_num_rows($matr5) > 0) {
					while($op = mysql_fetch_array($matr5)) {
						if($op['op_tangible'] == '1') {
/*							$cppe = $cppe + ($op['op_cantidad'] * $op['op_costo']);
							if($op['op_pedido'] < 1 && is_null($op['op_item_seg'])) {
								$opped = 1;
							}
							if($op['op_autosurtido'] == '1' && $op['op_pedido'] > 0) {
								$nppe++;
								if($op['op_costo'] > 1) {
									$npm = $npm + $op['op_cantidad'];
									$cpm = $cpm + ($op['op_cantidad'] * $op['op_costo']);
								} elseif($op['op_autosurtido'] == '1') {
									$occ = 0;
								}
							} */
							if(($op['op_autosurtido'] != '1' && $op['op_pedido'] > '0') || $op['op_item_seg'] > 0) {
								$costref = $costref + ($op['op_cantidad'] * $op['op_costo']);
							} elseif($op['op_autosurtido'] == '1' && $op['op_pedido'] > 0) {
								$cpm = $cpm + ($op['op_cantidad'] * $op['op_costo']);
							}
						} elseif($op['op_tangible'] == '2') {
							$costcons = $costcons + ($op['op_cantidad'] * $op['op_costo']);
							if(strpos($op['op_nombre'], 'onstant') !== false) {
								$constmat = $constmat + ($op['op_cantidad'] * $op['op_precio']);
							}
						} elseif($op['op_tangible'] == '0') {
							$costtot = $costtot + ($op['op_cantidad'] * $op['op_costo']);
						}
					}
				}
			}
			$precmat = $cons - $constmat;

// Determinar costos de MO por Destajos
			unset($costdest);
			$preg6 = "SELECT monto, area FROM " . $dbpfx . "destajos_elementos WHERE orden_id = '" . $ord['orden_id'] . "' AND reporte = '" . $gsub['sub_reporte'] . "'";
			$matr6 = mysql_query($preg6) or die("Falló selección de destajos. ".$preg6);
			while($dest = mysql_fetch_array($matr6)) {
				$costdest[$dest['area']] = $costdest[$dest['area']] + $dest['monto'];
			}

/*			$cppp = ($cpm / $npm);
			$nvomo = $cons + $mo;
			$asecc = 'black';
			if ($occ > 0 && $cpm > 0) {
				$ocp++; $tcpm = $tcpm + $cpm; $asecc = 'black';
			} elseif($nppe == 0 && $cppe > 0 ) {
				$asecc = 'black';
			} else { 
				$asecc = 'red'; 
			}

			if( $opped == 1 ) {
				$asecc = 'red';
			}
//			$mirada = '<!-- occ-' . $occ . ' cpm-' . $cpm . ' nppe-' . $nppe . ' cppe-' . $cppe . ' opped-' . $opped . ' -->';
			$occ = 1;
*/

			echo '<td>' . $fe_term . '</td>';
			echo '<td>' .  number_format($pres, 2) . '</td>';
			echo '<td>' .  number_format($partes, 2) . '</td>';
			echo '<td>' .  number_format($mo[6], 2) . '</td>';
			echo '<td>' .  number_format($mo[1], 2) . '</td>';
			echo '<td>' .  number_format($mo[7], 2) . '</td>';
			echo '<td>' .  number_format($precmat, 2) . '</td>';
			echo '<td>' .  number_format($constmat, 2) . '</td>';
			echo '<td>' .  number_format($costref, 2) . '</td>';
			echo '<td>' .  number_format($cpm, 2) . '</td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td>' .  number_format($costdest[6], 2) . '</td>';
			echo '<td>' .  number_format($costdest[7], 2) . '</td>';

/*			$totpres = $totpres + $pres;
			$totpartes = $totpartes + $partes;
			$totcons = $totcons + $cons;
			$totmo = $totmo + $mo;
			$totcostref = $totcostref + $costref;
			$totcostcon = $totcostcon + $costcons;
			$totcosttot = $totcosttot + $costtot;
			$totcostdes = $totcostdes + $costdest;
			if($gsub['sub_reporte'] == '0' || $gsub['sub_reporte'] == '') {
				$totpartespart = $totpartespart + $partes;
				$totconspart = $totconspart + $cons;
				$totmopart = $totmopart + $mo;
				$totpart = $totpart + $pres;
				$totcostconpart = $totcostconpart + $costcons;
				$totcosttotpart = $totcosttotpart + $costtot;
				$totcostdespart = $totcostdespart + $costdest;
				$totcostrefpart = $totcostrefpart + $costref;
				if($pres > 0) { $pvppart++; }
			} else { 
				$totpartessin = $totpartessin + $partes;
				$totconssin = $totconssin + $cons;
				$totmosin = $totmosin + $mo;
				$totsin = $totsin + $pres;
				$totcostconsin = $totcostconsin + $costcons;
				$totcosttotsin = $totcosttotsin + $costtot;
				$totcostdessin = $totcostdessin + $costdest;
				$totcostrefsin = $totcostrefsin + $costref;
				if($pres > 0) { $pvpsin++; }
			}
*/

			$preg1 = "SELECT fact_fecha_cobrada, fact_monto, fact_impuesto FROM " . $dbpfx . "facturas_por_cobrar WHERE orden_id = '" . $ord['orden_id'] . "' AND fact_tipo < '3' AND reporte = '" . $gsub['sub_reporte'] . "' AND fact_cobrada < 2 ";
//			if($ofc == 1) { $preg1 .= "AND fact_cobrada = '1' "; } 
//			elseif($ofsc == 1) { $preg1 .= "AND fact_cobrada = '0' "; }
//			elseif($osf == 1) { $preg1 .= "AND fact_cobrada = '1' "; }
//			echo $preg1;
			$matr1 = mysql_query($preg1) or die("Falló selección de facturas " . $preg1);
//		echo '<table cellpadding="2" cellspacing="0" border="1" width="100%">';
			$factmonto = 0; $fact_cob = 0; $dedu_cob = 0; $dedu_no = 0; $fact_num = ''; $fact_fech = ''; $dedu_num = ''; $fech_cob = ''; $fech_deducob = '';
			while($fact = mysql_fetch_array($matr1)) {
					if(!is_null($fact['fact_fecha_cobrada']) && $fact['fact_fecha_cobrada'] != '0000-00-00 00:00:00') {
						$fech_cob = date('Y-m-d', strtotime($fact['fact_fecha_cobrada'])) . ' ';
					} else {
						$fech_cob = '';
					}
					$factmonto = $factmonto + round(($fact['fact_monto'] - $fact['fact_impuesto']),2);
			}

			echo '<td>' . $factmonto . '</td><td>' . $fech_cob . '</td>';
			echo '</tr>'."\n";
			if($fondo == 'obscuro') { $fondo = 'claro'; } else { $fondo = 'obscuro'; }
		}
	}


/*	$tnvomo = $totcons + $totmo;
	$prcpartes = number_format((($totpartes / $totpres) * 100), 2);
	$prcnvomo = number_format((($tnvomo /$totpres) * 100), 2);
	$prcpartespart = number_format((($totpartespart / $totpart) * 100), 2);
	$prcnvomopart = number_format(((($totconspart + $totmopart) / $totpart) * 100), 2);
	$prcpartessin = number_format((($totpartessin / $totsin) * 100), 2);
	$prcnvomosin = number_format(((($totconssin + $totmosin) / $totsin) * 100), 2);
	$pvpp = number_format(round(($totpart / $pvppart), 2),2);
	$pvps = number_format(round(($totsin / $pvpsin), 2),2);
	$totutilsin = round(((($totsin - ($totcostrefsin+$totcostconsin+$totcosttotsin+$totcostdessin)) / $totsin) * 100),2);
	$cps = number_format(round((($totcostrefsin+$totcostconsin+$totcosttotsin+$totcostdessin) / $pvpsin), 2),2);
	$totutilpart = round(((($totpart - ($totcostrefpart+$totcostconpart+$totcosttotpart+$totcostdespart)) / $totpart) * 100),2);
	$cptp = number_format(round((($totcostrefpart+$totcostconpart+$totcosttotpart+$totcostdespart) / $pvppart), 2),2);
	$totutil = round(((($totpres - ($totcostref+$totcostcon+$totcosttot+$totcostdes)) / $totpres) * 100),2);

	echo '				<tr class="claro"><td colspan="5" style="text-align:center; font-size: x-large;">' . $total_x_busqueda . ' Ventas Encontradas<br>(Montos Sin Impuestos)</td><td style="vertical-align:bottom;">Venta Total<br>Refacciones</td><td style="vertical-align:bottom;">Venta Total<br>Materiales</td><td style="vertical-align:bottom;">Venta Total<br>MO</td><td style="vertical-align:bottom;">Monto Total<br>de Venta</td><td>Venta Promedio</td><td colspan="2"></td><td style="text-align:right;">Costo Promedio</td><td style="vertical-align:bottom;">Costo<br>Total Ref</td><td style="vertical-align:bottom;">Costo<br>Total<br>Mats</td><td style="vertical-align:bottom;">Costo<br>Total<br>TOT</td><td style="vertical-align:bottom;">Costo<br>Total<br>Destajo</td><td style="vertical-align:bottom;">Costo<br>Total</td><td style="vertical-align:bottom;">Utilidad<br>Total</td></tr>'."\n";	

	echo '				<tr class="obscuro"><td colspan="5" style="text-align:right;">Total Aseguradoras ('. $pvpsin .')</td><td style="text-align:right;">' . number_format($totpartessin,2) . '</td><td style="text-align:right;">' . number_format($totconssin,2) . '</td><td style="text-align:right;">' . number_format($totmosin,2) . '</td><td style="text-align:right;">' . number_format($totsin,2) . '</td><td style="text-align:right;">' . $pvps . '</td><td colspan="2"></td><td style="text-align:right;">' . $cps . '</td><td style="text-align:right;">' . number_format($totcostrefsin,2) . '</td><td style="text-align:right;">' . number_format($totcostconsin,2) . '</td><td style="text-align:right;">' . number_format($totcosttotsin,2) . '</td><td style="text-align:right;">' . number_format($totcostdessin,2) . '</td><td style="text-align:right;">' . number_format(($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin),2) . '</td><td style="text-align:right;">' . $totutilsin . '%</td></tr>'."\n";

	echo '				<tr class="obscuro"><td colspan="5" style="text-align:right;">Indices Aseguradoras</td><td style="text-align:right;">' . round((($totpartessin / $totsin) * 100), 2) . '%</td><td style="text-align:right;">' . round((($totconssin / $totsin) * 100), 2) . '%</td><td style="text-align:right;">' . round((($totmosin / $totsin) * 100), 2) . '%</td><td colspan="5"></td><td style="text-align:right;">' . round((($totcostrefsin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%</td><td style="text-align:right;">' . round((($totcostconsin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%</td><td style="text-align:right;">' . round((($totcosttotsin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%</td><td style="text-align:right;">' . round((($totcostdessin / ($totcostrefsin + $totcostconsin + $totcosttotsin + $totcostdessin)) * 100), 2) . '%</td><td colspan="2"></td></tr>'."\n";

	echo '				<tr class="claro"><td colspan="5" style="text-align:right;">Total Particulares ('. $pvppart .')</td><td style="text-align:right;">' . number_format($totpartespart,2) . '</td><td style="text-align:right;">' . number_format($totconspart,2) . '</td><td style="text-align:right;">' . number_format($totmopart,2) . '</td><td style="text-align:right;">' . number_format($totpart,2) . '</td><td style="text-align:right;">' . $pvpp . '</td><td colspan="2"></td><td style="text-align:right;">' . $cptp . '</td><td style="text-align:right;">' . number_format($totcostrefpart,2) . '</td><td style="text-align:right;">' . number_format($totcostconpart,2) . '</td><td style="text-align:right;">' . number_format($totcosttotpart,2) . '</td><td style="text-align:right;">' . number_format($totcostdespart,2) . '</td><td style="text-align:right;">' . number_format(($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart),2) . '</td><td style="text-align:right;">' . $totutilpart . '%</td></tr>'."\n";

	echo '				<tr class="claro"><td colspan="5" style="text-align:right;">Indices Particulares</td><td style="text-align:right;">' . round((($totpartespart / $totpart) * 100),2) . '%</td><td style="text-align:right;">' . round((($totconspart / $totpart) * 100), 2) . '%</td><td style="text-align:right;">' . round((($totmopart / $totpart) * 100), 2) . '%</td><td colspan="5"></td><td style="text-align:right;">' . round((($totcostrefpart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%</td><td style="text-align:right;">' . round((($totcostconpart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%</td><td style="text-align:right;">' . round((($totcosttotpart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%</td><td style="text-align:right;">' . round((($totcostdespart / ($totcostrefpart + $totcostconpart + $totcosttotpart + $totcostdespart)) * 100), 2) . '%</td><td colspan="2"></td></tr>'."\n";

	echo '				<tr class="obscuro"><td colspan="5" style="text-align:right;">Total</td><td style="text-align:right;">' . number_format($totpartes, 2) . '</td><td style="text-align:right;">' . number_format($totcons, 2) . '</td><td style="text-align:right;">' . number_format($totmo, 2) . '</td><td style="text-align:right;">' . number_format($totpres, 2) . '</td><td colspan="4"></td><td style="text-align:right;">' . number_format($totcostref,2) . '</td><td style="text-align:right;">' . number_format($totcostcon,2) . '</td><td style="text-align:right;">' . number_format($totcosttot,2) . '</td><td style="text-align:right;">' . number_format($totcostdes,2) . '</td><td style="text-align:right;">' . number_format(($totcostref + $totcostcon + $totcosttot + $totcostdes),2) . '</td><td style="text-align:right;">' . $totutil . '%</td></tr>'."\n";

	echo '				<tr class="obscuro"><td colspan="5" style="text-align:right;">Utilidad Total</td><td style="text-align:right;">' . number_format(((($totpartes - $totcostref) / $totpartes) * 100), 2) . '%</td><td style="text-align:right;">' . number_format(((($totcons - $totcostcon) / $totcons) * 100), 2) . '%</td><td style="text-align:right;">' . number_format(((($totmo - ($totcosttot + $totcostdes)) / $totmo) * 100), 2) . '%</td><td style="text-align:right;">' . number_format(((($totpres - ($totcostref + $totcostcon + $totcosttot + $totcostdes)) / $totpres) * 100), 2) . '%</td><td colspan="10"></td></tr>'."\n";

	echo '				<tr class="claro"><td colspan="5" style="text-align:right;">Costo de Refacciones surtidas<br>por Aseguradoras</td><td style="text-align:right;">' . number_format($tcpm,2) . '</td><td colspan="2">Costo Promedio de<br>Refacciones surtidas<br>por Aseguradoras (' . $ocp . ')</td><td style="text-align:right;">' . number_format(($tcpm / $ocp),2) . '</td><td colspan="7" style="color:red;">Las cantidades en ROJO son costos incompletos<br>de refacciones surtidas por Aseguradoras</td></tr>'."\n";
*/
	echo '			</table>';


        
echo'	
            </div>
	</div>'."\n";
include('parciales/pie.php'); 

?>