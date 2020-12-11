<?php
include('config.php');
mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
mysql_select_db($dbnombre) or die('Falló la seleccion la DB');

$ahora = date('Y-m-d H:i:s');
$domingo = date('w'); 
if($domingo > 0) {
	include('comun.php');
/*  ----------------  obtener nombres de aseguradoras   ------------------- */
	
		$consulta = "SELECT aseguradora_id, aseguradora_nic FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_nic";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
   	$ase = array();
		while ($aa = mysql_fetch_array($arreglo)) {
			$ase[$aa['aseguradora_id']] = $aa['aseguradora_nic'];
		}
		$ase[0] = 'Particular';
//		print_r($ase);
/*  ----------------  nombres de aseguradoras   ------------------- */

	$pregunta = "SELECT  prov_id, prov_razon_social, prov_representante, prov_email FROM " . $dbpfx . "proveedores WHERE prov_activo = '1'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion de proveedores!");
//$venc = mysql_fetch_array($matriz);
// echo $provs . '<br>';
//print_r($venc);
	while($prov = mysql_fetch_array($matriz)) {
		$filas = 0;
		$envprov = 0;
		$preg2 = "SELECT pedido_id, fecha_promesa FROM " . $dbpfx . "pedidos WHERE prov_id = '" . $prov['prov_id'] . "' AND pedido_estatus < '10'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de pedidos!");
		$contenido = "Proveedor: " . $prov['prov_razon_social'] . "<br><br>";
		$contenido .= EMAIL_PARTES_SALUDO . ' ' . $prov['prov_representante'] . "<br><br>";
		$contenido .= EMAIL_PARTES_CONT1 . "<br><br>";
		$contenido .= "	<table cellpadding='3' cellspacing='0' border='1'>\n";
		$contenido .= "		<tr><td>OT</td><td>Pedido</td><td>Siniestro</td><td>Nombre y referencia</td><td align=center>Cantidad<br>pendiente</td><td align=center>Fecha de<br>vencimiento</td></tr>\n";
		while($ped = mysql_fetch_array($matr2)) {
			$preg3 = "SELECT op_id, sub_orden_id, op_nombre, op_cantidad, op_recibidos, op_fecha_promesa FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $ped['pedido_id'] . "' AND op_ok = 0 AND op_tangible = '1'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de refacciones!");
			while($op = mysql_fetch_array($matr3)) {
				$pregunta3 = "SELECT orden_id, sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $op['sub_orden_id'] . "'";
				$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
				while($aseg = mysql_fetch_array($matriz3)) {
					$pendiente = $op['op_cantidad'] - $op['op_recibidos'];
					$filas++;
					if($aseg['sub_reporte'] == '0') { $aseg['sub_reporte'] = 'Particular'; }
					$contenido .= "		<tr><td>" . $aseg['orden_id'] . "</td><td>" . $ped['pedido_id'] . "</td><td>" . $aseg['sub_reporte'] . "</td><td>" . $op['op_nombre'] . "</td><td>" . $pendiente . "</td><td>" . date('Y-m-d', strtotime($op['op_fecha_promesa'])) . "</td></tr>\n";
				}
			}
			if($ped['fecha_promesa'] < $ahora) {
				$envprov = 1;
			}
		}
		$contenido .= "	</table><br><br>";
		$contenido .= EMAIL_PARTES_CONT2;
		$contenido .= EMAIL_PARTES_CONT3;
		$mensaje = "<html>
	<head>
		<meta http-equiv='content-type' content='text/html; charset=UTF-8'>
		<meta http-equiv='content-type' content='application/xhtml+xml; charset=UTF-8'>
		<title>" . EMAIL_PARTES_ASUNTO . "</title>
	</head>
	<body>
		" . $contenido . "
	</body>
</html>
";
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html\r\n";
		$headers .= "From: " . EMAIL_PROVEEDOR_RESPONDER . "\r\n";
		if($envprov > 0) {
			// ----------- Notificar al Proveedor -------------
			$para =  $prov['prov_email'];
//			$para =  'agustin.diaz@controldeservicio.com';
			$headers .= "Cc: $ref_pend_email\r\n";
		} else {
			// ----------- Notificar sólo internamente -------------
			$para =  $ref_pend_email;			
		}
		$headers .= "Reply-To: " . EMAIL_PROVEEDOR_RESPONDER .  "\r\n" . "X-Mailer: PHP";
		
		if($filas > 0) {
//			mail($para, "Notificacion Automatica de $agencia", $mensaje, $headers);
			echo $para.'<br>Notificacion Automatica de '.$agencia.'<br>'.$headers.'<br><br>'.$mensaje;
		}
	}

// ::::::::::::::::::::::::::  Notificando las refacciones no gestionadas :::::::::::::::::::::::::::

		$contenido = "	<table cellpadding='3' cellspacing='0' border='1'>\n";
		$contenido .= "		<tr><td>OT</td><td>Aseguradora</td><td>Siniestro</td><td>Nombre y referencia</td><td align=center>Cantidad<br>pendiente</td></tr>\n";
		$preg3 = "SELECT op_id, sub_orden_id, op_nombre, op_cantidad, op_recibidos, op_fecha_promesa FROM " . $dbpfx . "orden_productos WHERE op_pedido = '0' AND op_ok = 0 AND op_tangible = '1'";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo seleccion!");
		while($op = mysql_fetch_array($matr3)) {
			$pendiente = $op['op_cantidad'] - $op['op_recibidos'];
			$pregunta3 = "SELECT orden_id, sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $op['sub_orden_id'] . "'";
			$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
			while($aseg = mysql_fetch_array($matriz3)) {
				if($aseg['sub_reporte'] == '0') { $aseg['sub_reporte'] = 'Particular'; }
				$contenido .= "		<tr><td>" . $aseg['orden_id'] . "</td><td>" . $ase[$aseg['sub_aseguradora']] . "</td><td>" . $aseg['sub_reporte'] . "</td><td>" . $op['op_nombre'] . "</td><td>" . $pendiente . "</td></tr>\n";
			}
		}
		$contenido .= "	</table><br><br>";
		$mensaje = "<html>
	<head>
		<meta http-equiv='content-type' content='text/html; charset=UTF-8'>
		<meta http-equiv='content-type' content='application/xhtml+xml; charset=UTF-8'>
		<title>Refaciones NO pedidas</title>
	</head>
	<body>
		" . $contenido . "
	</body>
</html>
";
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html\r\n";
		$headers .= "From: " . EMAIL_PROVEEDOR_RESPONDER . "\r\n";
		$headers .= "Reply-To: " . EMAIL_PROVEEDOR_RESPONDER .  "\r\n" . "X-Mailer: PHP";
		$para =  $ref_pend_email;			
		
//		mail($para, "Refacciones NO pedidas de $agencia", $mensaje, $headers);
		echo $para.'<br>Refacciones NO pedidas de '.$agencia.'<br>'.$headers.'<br><br>'.$mensaje;

}

//echo $mensaje;

?>