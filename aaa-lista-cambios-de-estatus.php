<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if($_SESSION['usuario'] == '701' || $_SESSION['usuario'] == '1000' ) {
        // Acceso autorizado
} else {
        redirigir('usuarios.php');
}

if($cuantos < 1) { $cuantos = 500; }

$feini = date('Y-m-d 00:00:00', strtotime($feini));
$fefin = date('Y-m-d 23:59:59', strtotime($fefin));


$error = 'no';
//$preg0 = "SELECT orden_id, orden_ubicacion FROM " . $dbpfx . "ordenes WHERE orden_id >= $orden AND orden_fecha_de_entrega >= '$feent' AND (orden_estatus < '30' OR orden_estatus = '99') ORDER BY orden_id LIMIT " . $cuantos;
$preg0 = "SELECT orden_id, orden_ubicacion, orden_servicio, orden_categoria, orden_asesor_id FROM " . $dbpfx . "ordenes WHERE ";
if($orden != '') {
	$preg0 .= " orden_id >= '" . $orden . "'";
} else {
	$preg0 .= " orden_fecha_recepcion >= '$feini' AND orden_fecha_recepcion <= '$fefin'";
}
$preg0 .= " AND orden_estatus <= '99' ORDER BY orden_id LIMIT " . $cuantos;
$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de ordenes! " . $preg0);
//echo $preg0;

if($error == 'no') {
	$pregus = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios";
	$matrus = mysql_query($pregus) or die("ERROR: Fallo selección de usuarios! " . $pregus);
	while($usu = mysql_fetch_array($matrus)) {
		$usuario[$usu['usuario']] = $usu['nombre'] . ' ' . $usu['apellidos'];
	}
	$oprst = array(1 => 'Inició', 2 => 'Pausó', 5 => 'Continuó', 7 => 'Terminó');

	$secuen = [17, 1, 24, 27, 28, 29, 20, 2, 4, 9, 8, 10, 11, 21, 12, 14, 15, 16, 99, 5, 6, 7, 30, 31, 32, 33, 34, 35, 90, 98, 97, 95];

	if($export == 1) {
		$titulo = 'Estatus-por-OT-' . $nombre_agencia . '-' . date('Ymd', time()) . '.csv';
		$columna = array('OT', 'Tipo Servicio', 'Categoria Daño', 'Asesor');
		foreach($secuen as $ko) {
			$columna[] = 'Estatus';
			$columna[] = 'Usuario';
			$columna[] = 'Fecha Inicial';
			$columna[] = 'Fecha Último';
			$columna[] = 'Lapso H:m:s';
			$columna[] = 'Lapso Segs';
		}

		$fp = fopen('php://output', 'w');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $titulo . '"');
		header('Pragma: no-cache');
		header('Expires: 0');
		fputcsv($fp, $columna);
	} else {
		echo '
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
</head>
<body>'."\n";

		echo '<table cellpadding = "2" border="1">'."\n";
		echo '	<tr>'."\n";
		echo '		<td colspan="4">Datos generales de las OT en ' . $agencia . '</td>'."\n";
		foreach($secuen as $ko) {
			echo '		<td colspan="6">' . constant('ORDEN_ESTATUS_' . $ko) . '</td>'."\n";
		}
		echo '	</tr>'."\n";
		echo '	<tr>'."\n";
		echo '		<td>OT</td>
		<td>Tipo Servicio</td>
		<td>Categoria Daño</td>
		<td>Asesor</td>'."\n";
		foreach($secuen as $ko) {
			echo '		<td>Estatus</td>
		<td>Usuario</td>
		<td>Fecha Inicial</td>
		<td>Fecha Último</td>
		<td>Lapso H:m:s</td>
		<td>Lapso Segs</td>';
		}
		echo '	</tr>'."\n";
	}

//	echo '	<tr><td>OT</td><td>Usuario</td><td>Fecha Inicio</td><td>Fecha Fin</td><td>Cambio</td><td>Estuvo en Tránsito?</td><td>Lapso formato H:m:s</td><td>Lapso en Segundos</td></tr>'."\n";
	while ($ord = mysql_fetch_array($matr0)) {
		//echo 'Hola!';
		$tiempo = 0;
		unset($fila);
		$preg1 = "SELECT * FROM " . $dbpfx . "bitacora WHERE orden_id = '" . $ord['orden_id'] . "' AND (`bit_estatus` LIKE 'Creaci%n de nueva OT%' OR `bit_estatus` LIKE 'Registro Express terminado' OR `bit_estatus` LIKE 'Se creo Descripci%n de Da%os' OR `bit_estatus` LIKE 'Cambio a estatus%' OR `bit_estatus` LIKE 'Cambio de Ubicaci%n%' OR `bit_estatus` LIKE 'Reingreso al Taller' OR `bit_estatus` LIKE 'Orden de Trabajo Cance%')";
		//echo $preg1;
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de tareas! " . $preg1);
		while($bit = mysql_fetch_array($matr1)) {
			$marca = strtotime($bit['bit_fecha']);
			if($tiempo > 0) {
				$segundos = $marca - $tiempo;
//				$horas = intval($segundos / 3600);
//				$lapso = date('i:s', $segundos);
//				$lapso = $horas . ':' . $lapso;
				$tiempo = $marca;
				$fefin = $bit['bit_fecha'];
			} else {
				$feini = $bit['bit_fecha'];
				$fefin = $bit['bit_fecha'];
				$segundos = 0;
				$lapso = '0:00:00';
				$tiempo = $marca;
			}

			if(preg_match( '/^Cambio de Ubicaci.*.Taller/', $bit['bit_estatus']) || $bit['bit_estatus'] == 'Reingreso al Taller') {
				$fila[$numest]['transito'] = 'Reingresó';
			}
			if(preg_match( '/^Cambio de Ubicaci.*.Transito/', $bit['bit_estatus'])) {
				$fila[$numest]['transito'] = 'Tránsito';
			}
			$numest = '';
			if(preg_match( '/^Creaci.*.n de nueva OT para nuevo/', $bit['bit_estatus'])) { $numest = 17; }
			elseif(preg_match( '/^Registro Express terminado/', $bit['bit_estatus'])) { $numest = 17; }
			elseif(preg_match( '/^Se creo Descripci.*.n de Da.*.os/', $bit['bit_estatus'])) { $numest = 1; }
			elseif(preg_match( '/^Orden de Trabajo Cance.*/', $bit['bit_estatus'])) { $numest = 90; }
			else {
				$estatus = explode('anterior:', $bit['bit_estatus']);
				for($j = 0; $j < 10; $j++) {
					if(is_numeric($estatus[1][$j])) {
						$numest = $numest . $estatus[1][$j];
					} elseif(($estatus[1][$j]==' ' || ord($estatus[1][$j]) == '9') && $numest != '') {
						break;
					}
				}
			}

			$fila[$numest]['usuario'] = $usuario[$bit['usuario']];
			if($fila[$numest]['feini'] == '') {
				$fila[$numest]['feini'] = $feini;
			}
			if($numest == 16 && strtotime($fila['99']['feini']) < strtotime($fefin)) {
				$fila['99']['feini'] = $fefin;
			}
			if($numest == 30 && strtotime($fila['98']['feini']) < strtotime($fefin)) {
				$fila['98']['feini'] = $fefin;
			}
			if($numest == 31 && strtotime($fila['97']['feini']) < strtotime($fefin)) {
				$fila['97']['feini'] = $fefin;
			}
			if(($numest >= 32 && $numest <= 35) && strtotime($fila['95']['feini']) < strtotime($fefin)) {
				$fila['95']['feini'] = $fefin;
			}
			if($numest == 90 && strtotime($fila['90']['feini']) < strtotime($fefin)) {
				$fila['90']['feini'] = $fefin;
			}

			if(strtotime($fila[$numest]['fefin']) < strtotime($fefin)) {
				$fila[$numest]['fefin'] = $fefin;
			}

			$fila[$numest]['segundos'] = $fila[$numest]['segundos'] + $segundos;

//			echo '	<tr><td>' . $ord['orden_id'] . '</td><td>' . $usuario[$bit['usuario']] . '</td><td>' . $feini . '</td><td>' . $fefin . '</td><td>' . $estatus[1] . '</td><td>' . $Entransito . '</td><td>' . $lapso . '</td><td>' . $segundos . '</td><tr>'."\n";
			$feini = $bit['bit_fecha'];
		}
		if($ord['orden_servicio'] == 1 || $ord['orden_servicio'] == 3) {
			$tipserv = 'Particular';
		} elseif($ord['orden_servicio'] == 2) {
			$tipserv = 'Garantía';
		} else {
			$tipserv = 'Siniestro';
		}

		if($export == 1) {
			$campos = array($ord['orden_id'], $tipserv, constant('CATEGORIA_DE_REPARACION_' . $ord['orden_categoria']), $usuario[$ord['orden_asesor_id']]);
			foreach($secuen as $ko) {
				$lapso = '';
				$horas = intval($fila[$ko]['segundos'] / 3600);
				$minutos = intval(($fila[$ko]['segundos'] - ($horas * 3600)) / 60);
				$segundos = intval($fila[$ko]['segundos'] - (($horas * 3600) + ($minutos * 60)));
				if($minutos < 10) { $minutos = '0' . $minutos; }
				if($segundos < 10) { $segundos = '0' . $segundos; }
				$lapso = $horas . ':' . $minutos . ':' . $segundos;
				$campos[] = $ko;
				$campos[] = $fila[$ko]['usuario'];
				$campos[] = $fila[$ko]['feini'];
				$campos[] = $fila[$ko]['fefin'];
				$campos[] = $lapso;
				$campos[] = $fila[$ko]['segundos'];
			}
			fputcsv($fp, array_values($campos));
		} else {
			echo '	<tr>
		<td>' . $ord['orden_id'] . '</td>
		<td>' . $tipserv . '</td>
		<td>' . constant('CATEGORIA_DE_REPARACION_' . $ord['orden_categoria']) . '</td>
		<td>' . $usuario[$ord['orden_asesor_id']] . '</td>'."\n";
			foreach($secuen as $ko) {
				$lapso = '';
				$horas = intval($fila[$ko]['segundos'] / 3600);
				$minutos = intval(($fila[$ko]['segundos'] - ($horas * 3600)) / 60);
				$segundos = intval($fila[$ko]['segundos'] - (($horas * 3600) + ($minutos * 60)));
				if($minutos < 10) { $minutos = '0' . $minutos; }
				if($segundos < 10) { $segundos = '0' . $segundos; }
				$lapso = $horas . ':' . $minutos . ':' . $segundos;
				echo '		<td>' . $ko . '</td>
		<td>' . $fila[$ko]['usuario'] . '</td>
		<td>' . $fila[$ko]['feini'] . '</td>
		<td>' . $fila[$ko]['fefin'] . '</td>
		<td>' . $lapso . '</td>
		<td>' . $fila[$ko]['segundos'] . '</td>';
			}
			echo '	</tr>'."\n";
		}

	}
	if($export == 1) {
		exit;
	} else {
		echo '</table>'."\n";
		echo '</body></html>';
	}
} else {
	echo 'faltaron datos de ingreso.';
}

?>
