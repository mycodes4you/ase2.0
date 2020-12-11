<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}

if($cuantos < 1) { $cuantos = 500; }

$error = 'no';
$preg0 = "SELECT orden_id, orden_ubicacion FROM " . $dbpfx . "ordenes WHERE orden_id >= '$orden' AND orden_fecha_de_entrega > orden_fecha_recepcion LIMIT " . $cuantos;
$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de ordenes! " . $preg0);

if($error == 'no') {
	$pregus = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios";
	$matrus = mysql_query($pregus) or die("ERROR: Fallo selección de usuarios! " . $pregus);
	while($usu = mysql_fetch_array($matrus)) {
		$usuario[$usu['usuario']] = $usu['nombre'] . ' ' . $usu['apellidos'];
	}
	$oprst = array(1 => 'Inició', 2 => 'Pausó', 5 => 'Continuó', 7 => 'Terminó');

echo '
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
</head>
<body>'."\n";

	echo '<table cellpadding = "2" border="1">'."\n";
	echo '	<tr><td>OT</td><td>Usuario</td><td>Fecha Inicio</td><td>Fecha Fin</td><td>Cambio</td><td>Estuvo en Tránsito?</td><td>Lapso formato H:m:s</td><td>Lapso en Segundos</td></tr>'."\n";
	while ($ord = mysql_fetch_array($matr0)) {
		$tiempo = 0;
		$preg1 = "SELECT * FROM " . $dbpfx . "bitacora WHERE orden_id = '" . $ord['orden_id'] . "' AND (`bit_estatus` LIKE 'Creaci%n de nueva OT%' OR `bit_estatus` LIKE 'Registro Express terminado' OR `bit_estatus` LIKE 'Se creo Descripci%n de Da%os' OR `bit_estatus` LIKE 'Cambio a estatus%' OR `bit_estatus` LIKE 'Cambio de Ubicaci%n%' OR `bit_estatus` LIKE 'Reingreso al Taller')";
//		echo $preg2;
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de tareas! " . $preg1);
		$Entransito = '';
		while($bit = mysql_fetch_array($matr1)) {
			$marca = strtotime($bit['bit_fecha']);
			if($tiempo > 0) {
				$segundos = $marca - $tiempo;
				$horas = intval($segundos / 3600);
				$lapso = date('i:s', $segundos);
				$lapso = $horas . ':' . $lapso;				
				$tiempo = $marca;
				$fefin = $bit['bit_fecha'];
			} else {
				$feini = $bit['bit_fecha'];
				$fefin = $bit['bit_fecha'];
				$segundos = 0;
				$lapso = '0:00:00';
				$tiempo = $marca;
			}
			$estatus = explode('anterior:', $bit['bit_estatus']);
			if(!$estatus[1]) { $estatus[1] = $bit['bit_estatus']; }
			if(preg_match( '/^Cambio de Ubicaci.*.Taller/', $bit['bit_estatus']) || $bit['bit_estatus'] == 'Reingreso al Taller') { $Entransito = 'Reingresó'; }
			if(preg_match( '/^Cambio de Ubicaci.*.Transito/', $bit['bit_estatus'])) { $Entransito = 'Tránsito'; }
			echo '	<tr><td>' . $ord['orden_id'] . '</td><td>' . $usuario[$bit['usuario']] . '</td><td>' . $feini . '</td><td>' . $fefin . '</td><td>' . $estatus[1] . '</td><td>' . $Entransito . '</td><td>' . $lapso . '</td><td>' . $segundos . '</td><tr>'."\n";
			$feini = $bit['bit_fecha'];
			unset($estatus[1]);
		}
		$preg2 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de tareas! " . $preg2);
		while($sub = mysql_fetch_array($matr2)) {
			$preg3 = "SELECT usuario, sub_area, seg_tipo, seg_hora_registro FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de tareas! " . $preg3);
			$tiempo = 0; $fefin = ''; $reg = 0; $contenido = array();
			while($seg = mysql_fetch_array($matr3)) {
				$marca = strtotime($seg['seg_hora_registro']);
				if($tiempo > 0) {
					$segundos = $marca - $tiempo;
					$horas = intval($segundos / 3600);
					$lapso = date('i:s', $segundos);
					$contenido[($reg - 1)][3] = $horas . ':' . $lapso;
					$contenido[($reg - 1)][4] = $segundos;
					$tiempo = $marca;
					$feini = $seg['seg_hora_registro'];
					$contenido[($reg - 1)][1] = $seg['seg_hora_registro'];
				} else {
					$feini = $seg['seg_hora_registro'];
					$tiempo = $marca;
				}
				$contenido[$reg][0] = '	<tr><td>' . $ord['orden_id'] . '</td><td>' . $usuario[$seg['usuario']] . '</td><td>' . $feini . '</td><td>';
				$contenido[$reg][2] = '</td><td>' . $oprst[$seg['seg_tipo']] . ' ' . constant('NOMBRE_AREA_' . $seg['sub_area']) . '</td><td></td>';
				$reg++;
			}
			foreach($contenido as $k) {
				echo $k[0] . $k[1] . $k[2] . '<td>' .$k[3] . '</td><td>' . $k[4] . '</td><tr>'."\n";
			}
		}
	}
	echo '</table>'."\n";
} else {
	echo 'faltaron datos de ingreso.';
}
echo '</body></html>';

?>
