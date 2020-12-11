<?php

// --- Parámetros iniciales ---
	$tarifa1 = 0.07; $tarifa2 = 0.7; $fijo = 0;
	$feinicalc = '2015-01-01 00:00:00';

// --- Se crea la pregunta de usuarios con rol de asesor ---
$pregase = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE acceso = '0' AND rol06 = '1' ORDER BY nombre, apellidos ";
$matrase = mysql_query($pregase) or die("ERROR: Fallo selección de Asesores! " . $pregase);
//echo $pregase;

if($accion === "generar") {
// --- Presentación de candidatos a recibir comisiones de acuerdo a definición del cliente.
// --- Creación de registros de las comisiones a pagar.

// --- Obtener periodo de cálculo ---
	if($fefin == '') {
		$fefin = date('Y-m-d 23:59:59');
	}
	if($feini == '') {
		$feini = (strtotime($fefin) - (7*24*3600));
		$feini = date('Y-m-d 00:00:00', $feini);
	}

// --- Presentar selector de periodo ---
	require_once("calendar/tc_calendar.php");
	echo '				<form action="comisiones.php?accion=generar" method="post" enctype="multipart/form-data" name="selperiodo">'."\n";
	echo '				<div class="row"><div class="col-sm-3 izq"><strong><big>Fecha de Inicio</big></strong><br>';
		//instantiate class and set properties
		$myCalendar = new tc_calendar("feini", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
		//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		//$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2013, 2023);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();
	echo '				</div><div class="col-sm-3 izq"><strong><big>Fecha de Fin</big></b><br>';
		//instantiate class and set properties
		$myCalendar = new tc_calendar("fefin", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
		//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		//$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2013, 2023);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();

// --- Ajustar horas de fechas seleccionadas --
	$feini = date('Y-m-d 00:00:00', strtotime($feini));
	$fefin = date('Y-m-d 23:59:59', strtotime($fefin));

	echo '				</div>
					<div class="row"><div class="col-sm-2 izq">
						<input type="submit" class="btn btn-success" value="Aplicar Fechas" />
						<input type="hidden" name="comision" value="' . $comision . '" />
					</div>
				</div></form>'."\n";

	$preg3 = "SELECT f.fact_id, f.orden_id, f.fact_num, f.fact_monto, f.fact_fecha_cobrada FROM " . $dbpfx . "facturas_por_cobrar f, " . $dbpfx . "ordenes o WHERE o.orden_fecha_de_entrega < '" . $fefin . "' AND o.orden_fecha_de_entrega > '" . $feini . "' AND (f.aseguradora_id = '0' OR f.aseguradora_id = '4' ) AND f.fact_cobrada = '1' AND f.orden_id = o.orden_id ORDER BY aseguradora_id,fact_id ";
	$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de Facturas cobradas a particulares o de venta adicional! tipo-3 67 " . $preg3);
//		echo $preg3;
	$idx = 0;
	while ($fact = mysql_fetch_array($matr3)) {
// --- Obtener OTs 99 del periodo y localizar si tienen comisiones calculadas --
		$preg1 = "SELECT " . $dbpfx . "ordenes.orden_id, orden_asesor_id, comision_tipo, recibo_id FROM " . $dbpfx . "ordenes LEFT JOIN " . $dbpfx . "comisiones ON " . $dbpfx . "ordenes.orden_id = " . $dbpfx . "comisiones.orden_id WHERE " . $dbpfx . "ordenes.orden_id = '" . $fact['orden_id'] . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de OTs! tipo-3 72 " . $preg1);
//	echo mysql_num_rows($matr1);
		mysql_data_seek($matrase,0);
		while ($ases = mysql_fetch_array($matrase)) {
			mysql_data_seek($matr1,0);
			while ($ots = mysql_fetch_array($matr1)) {
//				echo 'Asesor: ' . $ases['usuario'] . ' ASE OT: ' . $ots['orden_asesor_id'] . '<br>';
				if($ases['usuario'] == $ots['orden_asesor_id'] && $ots['comision_tipo'] != $comision) {
					$asesor[$ases['usuario']][$idx]['ot'] = $ots['orden_id'];
					$asesor[$ases['usuario']][$idx]['fact_id'] = $fact['fact_id'];
					$asesor[$ases['usuario']][$idx]['monto'] = $fact['fact_monto'];
					$asesor[$ases['usuario']][$idx]['numero'] = $fact['fact_num'];
					$nomase[$ases['usuario']]['nombre'] = $ases['nombre'] . ' ' . $ases['apellidos'];
				} elseif($ases['usuario'] == $ots['usuario'] && $ots['comision_tipo'] == $comision) {
					$asesor[$ases['usuario']][$idx]['ot'] = '<span style="background-color:yellow;"> Factura ' . $fact['fact_id'] . ' de la OT ' .  $ots['orden_id'] . ' pagada con recibo <a href="recibosrh.php?accion=consultar&recibo_id=' . $ots['recibo_id'] . '" target="_blank">' . $ots['recibo_id'] . '</a> </span><br>';
					$nomase[$ases['usuario']]['nombre'] = $ases['nombre'] . ' ' . $ases['apellidos'];
				}
				$idx++;
			}
		}
	}

// --- Presentar resultados para seleccionar a quienes se paga ---
	echo '				<form action="comisiones.php?accion=procesar" method="post" enctype="multipart/form-data" name="procesacoms">'."\n";
	$fondo = 'claro';
	foreach($asesor as $kf => $xf) {
		echo '				<div class="row">
					<div class="col-sm-8">
						<strong>' . $nomase[$kf]['nombre'] . ' (' . $kf . ')</strong>:<br>
						<table cellpadding="2" class="table">
							<thead><tr>
								<th class="cen">OT</th>
								<th>Factura</th>
								<th>Monto facturado</th>
								<th>Comisión calculada</th>
							</tr></thead>
							<tbody>'."\n";
		foreach($xf as $kx => $vf) {
			if(is_numeric($vf['ot'])) {
				$montcom = round((($vf['monto'] / (1 + $impuesto_iva)) * $tarifa1),2);
				echo '							<tr class="' . $fondo . '">
								<td><input type="checkbox" name="otcom[' . $vf['ot'] . ']" checked value="' . $kf . '|' . $vf['fact_id'] . '|' . $montcom . '|' . $vf['numero'] . '" /><a href="entrega.php?accion=cobros&orden_id=' . $vf['ot'] . '" target="_blank">' . $vf['ot'] . '</a></td>
								<td>' . $vf['numero'] . '</td>
								<td class="der">$' . number_format($vf['monto'],2) . '</td>
								<td class="der">$' . number_format($montcom,2) . '</td>
							</tr>' . "\n";
			} else {
				echo '							<tr class="' . $fondo . '">
								<td colspan="4">' . $vf['ot'] . '</td>
							</tr>'."\n";
			}
			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
		echo '							<tbody></table>'."\n";
		echo '				</div></div>'."\n";
	}
	echo '				<div class="row"><div class="col-sm-2 izq">
						<input type="submit" class="btn btn-success" value="Procesar OTs seleccionadas" />
						<input type="hidden" name="fecorte" value="' . $fefin . '" />
						<input type="hidden" name="comision" value="' . $comision . '" />
				</div></div></form>'."\n";
}

elseif($accion === "procesar") {

// --- Determina cuantas OTs fueron seleccionadas de cada Asesor para pago de comisiones ---
	foreach($otcom as $k => $v) {
		$datos = explode('|', $v);
		$item[$k]['asesor'] = $datos['0'];
		$item[$k]['factura'] = $datos['1'];
		$item[$k]['montcom'] = $datos['2'];
		$item[$k]['numero'] = $datos['3'];
		$cuantos[$k]++;
	}

// --- Determina la tarifa a utilizar para cada asesor basado en la cantidad de OTs logradas ---
// --- Crea el recibo de pago para cada Asesor ---
/*	while ($ases = mysql_fetch_array($matrase)) {
		if($cuantos[$ases['usuario']] > 0 || count($cbfija) > 0) {
			if($cuantos[$ases['usuario']] > 20) {
				$tarifa[$ases['usuario']] = $tarifa2;
			} else {
				$tarifa[$ases['usuario']] = $tarifa1;
			}
			if($recibo_id[$ases['usuario']] < 1) {
				$sql_array = [
					'usuario' => $ases['usuario'],
					'usuario_paga' => $_SESSION['usuario']
				];
				$recibo_id[$ases['usuario']] = ejecutar_db($dbpfx . 'destajos', $sql_array, 'insertar');
			}
		}
	} */

// --- Agregar comisiones para cada Asesor ---
	foreach($item as $k => $v) {
		if($recibo_id[$v['asesor']] < 1) {
			$sql_array = [
				'usuario' => $v['asesor'],
				'usuario_paga' => $_SESSION['usuario']
			];
			$recibo_id[$v['asesor']] = ejecutar_db($dbpfx . 'destajos', $sql_array, 'insertar');
		}
		unset($sql_com);
		$sql_com = [
			'comision_tipo' => $comision,
			'orden_id' => $k,
			'indicador' => 'Venta en factura ' . $v['numero'],
			'usuario' => $v['asesor'],
			'monto' => $v['montcom'],
			'fecha_evento' =>$fecorte,
			'fecha_creacion' => date('Y-m-d H:i:s', time()),
			'recibo_id' => $recibo_id[$v['asesor']],
			'estatus' => 20,
		];
		ejecutar_db($dbpfx . 'comisiones', $sql_com, 'insertar');
		$bitacora = 'Se creó la comisión del usuario ' . $v['asesor'] . ' por ' . utf8_encode($com['com_nombre']) . ' dela factura ' . $v['factura'] . ' con un monto de $' . number_format($v['montcom'],2);
		bitacora($k, $bitacora, $dbpfx);

// --- Inserta el regitro en el recibo de destajo ---
		$veh = datosVehiculo($k, $dbpfx);
		$sql_data = [
				'recibo_id' => $recibo_id[$v['asesor']],
				'orden_id' => $k,
				'monto' => $v['montcom'],
				'reporte' =>  'Factura: ' . $v['numero'],
				'vehiculo' => $veh['completo'],
				'comision' => utf8_encode($com['com_nombre']),
				'operador' => $v['asesor'],
		];
		ejecutar_db($dbpfx . 'destajos_elementos', $sql_data, 'insertar');
		$total[$v['asesor']] = $total[$v['asesor']] + $v['montcom'];
	}

// --- Actualiza los totales de cada recibo de destajo creado ---
	foreach($recibo_id as $k => $v) {
		if($destiva == 1) {
			$iva = round(($total[$k] * $impuesto_iva),2);
		}
		$param = "recibo_id = '" . $v . "'";
		$sql_data = [
			'monto' => $total[$k],
			'impuesto' => $iva,
		];
		ejecutar_db($dbpfx . 'destajos', $sql_data, 'actualizar', $param);
	}
}

?>