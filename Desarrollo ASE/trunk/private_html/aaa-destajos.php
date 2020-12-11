<?php
include('parciales/funciones.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

/*  ----------------  obtener nombres de usuarios   ------------------- */
	
		$consulta = "SELECT usuario, nombre, apellidos, comision FROM " . $dbpfx . "usuarios WHERE acceso = '0' ORDER BY nombre,apellidos";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo selección de usuarios!");
//		$num_provs = mysql_num_rows($arreglo);
   		$usu = array();
//   	$provs[0] = 'Sin Proveedor';
		while ($usua = mysql_fetch_array($arreglo)) {
			$usu[$usua['usuario']] = array('nom' => $usua['nombre'], 'ape' => $usua['apellidos'], 'com' => $usua['comision']);
		}

$feini = '2017-01-01';
$fefin = '2017-07-27';


$preg3 = "SELECT * FROM " . $dbpfx . "destajos WHERE fecha_creado > '$feini' AND fecha_creado < '$fefin'";
$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de recibos! " . $preg3);

$encabezado = 'Destajos del ' . strftime('%e de %B del %Y', strtotime($feini)) . ' al ' . strftime('%e de %B del %Y', strtotime($fefin));

echo '<h1>Reporte de destajos del 1 de enero de 2017 al 27 de julio de 2017';

echo '<table cellpadding = "2" border="1">'."\n";
echo '
	<tr>
		<td>Recibo</td>
		<td>operador</td>
		<td>OT</td>
		<td>Vehículo</td>
		<td>Siniestro</td>
		<td>Área</td>
		<td>Fecha Creado</td>
		<td>Destajo</td>
		<td>Fecha Pagado</td>
		<td>Monto Pagado</td>
		<td>Costo de materiales</td>
	</tr>'."\n";
	

		while($rec = mysql_fetch_array($matr3)) {
			if($rec['saldado'] < 2) {
				$preg2 = "SELECT * FROM " . $dbpfx . "destajos_elementos WHERE recibo_id = '" . $rec['recibo_id'] . "'";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de elementos!".$preg2);
				while($ord = mysql_fetch_array($matr2)) {
					$total_rec = round(($rec['monto'] + $rec['impuesto']),2);
					$sbt_dest = round($ord['monto'],2);
					$imp_dest = round(($sbt_dest * $destiva * $impuesto_iva),2);
					$prct = ($sbt_dest + $imp_dest) / $total_rec;
					$veh = datosVehiculo($ord['orden_id'], $dbpfx);
					if($ord['reporte'] == '0' || $ord['reporte'] == '') { $ord['reporte'] = 'Particular'; }
					echo '									<tr class="' . $fondo . '"><td><a href="recibosrh.php?accion=consultar&recibo_id=' . $rec['recibo_id'] . '">' . $rec['recibo_id'] . '</a></td><td>' . $usu[$rec['usuario']]['nom'] . ' ' . $usu[$rec['usuario']]['ape'] . '</td><td><a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" target="_blank">' . $ord['orden_id'] . '</a></td><td> ' . $veh['marca'] . ' ' . $veh['tipo'] . ' ' . $veh['color'] . '</td><td>' . $ord['reporte'] . '</td><td>' . constant('NOMBRE_AREA_'.$ord['area']) . '</td><td>' . date('d/M/Y', strtotime($rec['fecha_creado'])) . '</td><td style="text-align:right;">' . round(($ord['monto'] + (round(($ord['monto'] * $destiva * $impuesto_iva),2))),2) . '</td><td>';
					if(strtotime($rec['fecha_pagado']) > 1000) {
						echo date('d/M/Y', strtotime($rec['fecha_pagado']));
					}
					echo '</td><td style="text-align:right;">' . number_format(($rec['pagado'] * $prct),2) . '</td><td style="text-align:right;">' . number_format($ord['costcons'],2) . '</td>';
					echo '</tr>'."\n";
					if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
				}
//				echo '				</table>'."\n";
				$total = $total + round(($rec['monto'] + $rec['impuesto']),2);
				$pagado = $pagado + round($rec['pagado'],2);
			} else {
				echo '									<tr class="' . $fondo . '"><td><a href="recibosrh.php?accion=consultar&recibo_id=' . $rec['recibo_id'] . '">' . $rec['recibo_id'] . '</a></td><td colspan="9">Recibo Cancelado</td></tr>'."\n";
				if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
			}
		}


echo '</table>';


?>