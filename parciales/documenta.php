<?php
  	$parametros='orden_id = ' . $orden_id;
  	$nombre_archivo = 'orden-' . $orden_id . '-liberacion-' . time() . '.jpg';
	if (move_uploaded_file($_FILES['pres_auto']['tmp_name'], DIR_DOCS . $nombre_archivo)){
		$sql_data_array = array('orden_id' => $orden_id,
			'doc_nombre' => 'Orden de Trabajo Pagada',
			'doc_archivo' => $nombre_archivo);
		ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
	} else {
		$mensaje = "Ocurrió algún error al subir el archivo. No pudo guardarse.<br>";
		$error = 'si';
	}
   if ($error === 'no') {
		$sql_data_array = array('orden_estatus' => '99',
			'orden_alerta' => '0',
			'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'),
			'orden_fecha_de_entrega' => date('Y-m-d H:i:s'));
		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		$pregunta = "SELECT orden_vehiculo_id FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$dato = mysql_fetch_array($matriz);
		$parametros='vehiculo_id = ' . $dato['orden_vehiculo_id'];
		$sql_data = array('vehiculo_proxima' => $proxima);
		ejecutar_db($dbpfx . 'vehiculos', $sql_data, 'actualizar', $parametros);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		include('idiomas/ES-MX/proceso.php');
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		$mensaje .= 'No se cerró la orden de trabajo ' . $orden_id . ', intente de nuevo.';
	}
	echo $mensaje;
	