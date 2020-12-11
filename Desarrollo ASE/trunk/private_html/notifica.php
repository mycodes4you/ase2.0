<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/notifica.php');

if (($accion==='procesar') || ($accion==='registrar') || ($accion==='imprimir') || ($accion==='asigna')) { 
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}

if($accion==='asigna') {
	
	$funnum = 1080000;
	
	if ($_SESSION['rol06']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Asesor de Servicio, ingresar Usuario y Clave correcta');
	}
	$error = 'no';
	$mensaje = '';
   if (($error === 'no') && ($operador != '') && ($acordada != ''))  {
  		$fecha_acor = $acordada . ' ' . $h_acor;
  		$parametros = 'orden_id = ' . $orden_id;
  		$pregunta = "SELECT c.cliente_nombre, c.cliente_apellidos, c.cliente_email, o.orden_cliente_id, o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas, o.orden_estatus_01, o.orden_estatus_02, o.orden_estatus_03, o.orden_estatus_04, o.orden_estatus_05, o.orden_estatus_06, o.orden_estatus_07, o.orden_estatus_08, o.orden_estatus_09, o.orden_estatus_10, o.orden_presupuesto FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$dato = mysql_fetch_array($matriz);
		$para = $dato['cliente_email'];
		$f_acor = strftime('%A %e de %B del %Y a las %l:%M %p', strtotime($acordada));
//		echo $fecha_acor;
		$contenido = EMAIL_ENTREGA_SALUDO . $dato['cliente_nombre'] . ' ' . $dato['cliente_apellidos'] . '.<br>
<br>
' . EMAIL_ENTREGA_CONT1 . $orden_id . ' ' . EMAIL_ENTREGA_CONT2 . ' <strong>' . $dato['orden_vehiculo_marca'] . ' ' . $dato['orden_vehiculo_tipo'] . ' ' . $dato['orden_vehiculo_color'] . ' ' . $dato['orden_vehiculo_placas'] . '</strong>.<br><br> ' . EMAIL_ENTREGA_CONT3 . $f_acor . EMAIL_ENTREGA_CONT4 . money_format('%n', $dato['orden_presupuesto']) . EMAIL_ENTREGA_MONEDA . ' <br>
<br>
' . EMAIL_ENTREGA_CONT5 . '<br>
';
		$mensaje = '<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8">
		<title>' . EMAIL_ENTREGA_ASUNTO . '</title>
	</head>
	<body>
		' . $contenido . '
	</body>
</html>
';
		$headers = 'MIME-Version: 1.0
';
		$headers .= 'Content-type: text/html
';
		mail($para, 'Notificacion Automatica de E-Taller', $mensaje, $headers);
  		$sql_data_array = array('orden_estatus' => '13',
  			'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'));
		$sql_data_array['orden_fecha_notificacion'] = date('Y-m-d H:i:s');
		$sql_data_array['orden_fecha_acordada'] = $fecha_acor;
		$sql_data_array['orden_fecha_ultimo_movimiento'] = date('Y-m-d H:i:s');
	  	$sql_data_array['orden_alerta'] = '0';
  		echo $para . EMAIL_ENTREGA_ASUNTO . $mensaje . $encabezados; 
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		include('idiomas/' . $idioma . '/proceso.php');
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		$mensaje .= 'No se liberó el automovil de la Orden de Trabajo ' . $orden_id . '  para su entrega.';
	}
	echo $mensaje;
}

?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>