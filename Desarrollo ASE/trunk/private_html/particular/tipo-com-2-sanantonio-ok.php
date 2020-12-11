<?php

// --- Parámetros iniciales ---
	$tarifa1 = 130; $tarifa2 = 130; $fijo = 1500;
	$feinicalc = '2015-01-01 00:00:00';

// --- Se crea la pregunta de usuarios con rol de asesor ---
$pregase = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE acceso = '0' AND codigo = '15' ORDER BY nombre, apellidos ";
$matrase = mysql_query($pregase) or die("ERROR: Fallo selección de Asesores! " . $pregase);
echo $pregase;

if($accion === "generar") {
// --- Presentación de candidatos a recibir comisiones de acuerdo a definición del cliente.
// --- Creación de registros de las comisiones a pagar.

// --- Obtener periodo de cálculo ---
	if($fefin == '') {
		$fefin = date('Y-m-t 23:59:59');
	}
	if($feini == '') {
		$feini = (strtotime($fefin) - (30*24*3600));
		$feini = date('Y-m-01 00:00:00', $feini);
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

// --- Obtener OTs 99 del periodo y localizar si tienen comisiones calculadas --
	$preg1 = "SELECT " . $dbpfx . "ordenes.orden_id, comision_tipo, recibo_id FROM " . $dbpfx . "ordenes LEFT JOIN " . $dbpfx . "comisiones ON " . $dbpfx . "ordenes.orden_id = " . $dbpfx . "comisiones.orden_id WHERE orden_fecha_de_entrega < '" . $fefin . "' AND orden_fecha_de_entrega > '" . $feini . "' AND orden_estatus = '99'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de OTs! tipo-1 67 " . $preg1);
//	echo $preg1;

	while ($ases = mysql_fetch_array($matrase)) {
		mysql_data_seek($matr1,0); $cuanots = 0;
		while ($ots = mysql_fetch_array($matr1)) {
			if($ots['comision_tipo'] != $comision) {
				$asesor[$ases['usuario']]['ot'][] = $ots['orden_id'];
				$asesor[$ases['usuario']]['cuantas']++;
				$asesor[$ases['usuario']]['nombre'] = $ases['nombre'] . ' ' . $ases['apellidos'];
			} elseif($ots['comision_tipo'] == $comision) {
				$asesor[$ases['usuario']]['ot'][] = '<span style="background-color:yellow;"> OT ' .  $ots['orden_id'] . ' Pagada con recibo <a href="recibosrh.php?accion=consultar&recibo_id=' . $ots['recibo_id'] . '" target="_blank">' . $ots['recibo_id'] . '</a> </span><br>';
				$asesor[$ases['usuario']]['nombre'] = $ases['nombre'] . ' ' . $ases['apellidos'];
			}
		}
	}

// --- Presentar resultados para seleccionar a quienes se paga ---
	echo '				<form action="comisiones.php?accion=procesar" method="post" enctype="multipart/form-data" name="procesacoms">'."\n";
	$fondo = 'claro';
	foreach($asesor as $ase => $vase) {
		echo '				<div class="row ' . $fondo . '">
					<div class="col-sm-8">'."\n";
		echo '					' . $vase['cuantas'] . ' OTs calificaron para <strong>' . $vase['nombre'] . '</strong>:<br>'."\n";
		echo '					<input type="checkbox" name="fijo[' . $ase . ']" checked value="' . $fijo . '" />Comisión Base '."\n";
		foreach($vase['ot'] as $kot) {
			if(is_numeric($kot)) {
				echo '					<input type="checkbox" name="otcom[' . $kot . ']" checked value="' . $ase . '" /><a href="ordenes.php?accion=consultar&orden_id=' . $kot . '" target="_blank">' . $kot . '</a> '."\n";
			} else {
				echo '					' . $kot . "\n";
			}
		}
		echo '				</div></div>'."\n";
		if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
	}
	echo '				<div class="row"><div class="col-sm-2 izq">
						<input type="submit" class="btn btn-success" value="Procesar OTs seleccionadas" />
						<input type="hidden" name="fecorte" value="' . $fefin . '" />
						<input type="hidden" name="comision" value="' . $comision . '" />
				</div></div></form>'."\n";
}

elseif($accion === "procesar") {

// --- Determina cuantas OTs fueron seleccionadas de cada JT para pago de comisiones ---
	foreach($otcom as $k => $v) {
		$cuantos[$v]++;
	}

// --- Determina la tarifa a utilizar para cada asesor basado en la cantidad de OTs logradas ---
// --- Crea el recibo de pago para cada Asesor ---
	while ($ases = mysql_fetch_array($matrase)) {
		if($cuantos[$ases['usuario']] > 0) {
			if($cuantos[$ases['usuario']] > 20) {
				$tarifa[$ases['usuario']] = $tarifa2;
			} else {
				$tarifa[$ases['usuario']] = $tarifa1;
			}
			$sql_array = [
				'usuario' => $ases['usuario'],
				'usuario_paga' => $_SESSION['usuario']
			];
			$recibo_id[$ases['usuario']] = ejecutar_db($dbpfx . 'destajos', $sql_array, 'insertar');
		}
	}

// --- Agregar comisiones para cada JT ---
	foreach($otcom as $k => $v) {
		unset($sql_com);
		$sql_com = [
			'comision_tipo' => $comision,
			'orden_id' => $k,
			'indicador' => $k,
			'usuario' => $v,
			'monto' => $tarifa[$v],
			'fecha_evento' =>$fecorte,
			'fecha_creacion' => date('Y-m-d H:i:s', time()),
			'recibo_id' => $recibo_id[$v],
			'estatus' => 20,
		];
		ejecutar_db($dbpfx . 'comisiones', $sql_com, 'insertar');
		$bitacora = 'Se creó la comisión del usuario ' . $v . ' por ' . utf8_encode($com['com_nombre']) . '  con un monto de $ ' . number_format($tarifa[$v],2);
		bitacora($k, $bitacora, $dbpfx);

// --- Inserta el regitro en el recibo de destajo ---
		$veh = datosVehiculo($k, $dbpfx);
		$sql_data = [
				'recibo_id' => $recibo_id[$v],
				'orden_id' => $k,
				'monto' => $tarifa[$v],
				'reporte' =>  '---',
				'vehiculo' => $veh['completo'],
				'comision' => utf8_encode($com['com_nombre']),
				'operador' => $v,
		];
		ejecutar_db($dbpfx . 'destajos_elementos', $sql_data, 'insertar');
		$total[$v] = $total[$v] + $tarifa[$v];
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