<?php 


// Fuera de uso 20140629

foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
if (($accion==='insertar') || ($accion==='actualizar')) { 
	/* no cargar encabezado */
	include('parciales/funciones.php');
} else {
	include('idiomas/' . $idioma . '/siniestros.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}

if ($accion==="crear") {
	$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
   $matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  	$orden = mysql_fetch_array($matriz);
			
//	echo 'Estamos en la sección crear';
?>
	<br>
	<form action="siniestros.php?accion=insertar" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr class="cabeza_tabla"><td colspan="2">Revisión y comparación de orden de admisión contra daño físico</td></tr>
		<tr><td><br>Agregar imagen escaneada de orden de admisión</td><td style="text-align:left;"><input type="file" name="orden_adm" size="30" /><br></td></tr>
		<tr class="cabeza_tabla"><td colspan="2">Tareas incluidas en la orden de admisión</td></tr>
		<tr><td>Mecánica</td><td style="text-align:left;"><textarea cols="60" rows="3" name="mecanica"></textarea></td></tr>
		<tr><td>Eléctrica</td><td style="text-align:left;"><textarea cols="60" rows="3" name="electrica"></textarea></td></tr>
		<tr><td>Hojalatería</td><td style="text-align:left;"><textarea cols="60" rows="3" name="hojalateria"></textarea></td></tr>
		<tr><td>Pintura</td><td style="text-align:left;"><textarea cols="60" rows="3" name="pintura"></textarea></td></tr>
		<tr><td>Accesorios</td><td style="text-align:left;"><textarea cols="60" rows="3" name="accesorios"></textarea></td></tr>
		<tr><td>¿El cliente requiere tareas adicionales<br>a las autorizadas por la aseguradora?</td><td style="text-align:left;">No <input type="radio" name="diferencias" value="No" checked="checked" /> Sí <input type="radio" name="diferencias" value="Si" /></td></tr>
		<tr><td valign="bottom"><input type="button" onClick="agregarTarea()" value="Añadir Tarea" ></td>
			<td>
				<table id="tablaTareas" width="100%" class="izquierda">
					<tr>
						<td width="20%" valign="top">Area</td>
						<td width="60%">Descripción</td>
						<td width="20%">Acciones</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2"><input type="hidden" name="orden_id" value="<?php echo $orden_id; ?>" />
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
		</tr>
	</table>
	</form>
<?php
}

elseif (($accion==='insertar') || ($accion==='actualizar')) {
//	echo 'Estamos en la sección inserta.<br>';

	$placas=preparar_entrada_bd($placas);
	$serie=preparar_entrada_bd($serie);
	$marca=preparar_entrada_bd($marca);
	$tipo=preparar_entrada_bd($tipo);
	$subtipo=preparar_entrada_bd($subtipo);
	$modelo=preparar_entrada_bd($modelo);
	$puertas=preparar_entrada_bd($puertas);
	$color=preparar_entrada_bd($color);
	$aseguradora=preparar_entrada_bd($aseguradora);
	$poliza=preparar_entrada_bd($poliza);
	$cliente_id=preparar_entrada_bd($cliente_id);
	$status=preparar_entrada_bd($status);

	$error = 'no';
	$mensaje= '';
//	echo '<br><br>====== ' . $error . ' <<>> ' . $mensaje . ' ======<br><br>';
//	echo strlen($telefono1);

	if (strlen($placas) < 4) {$error = 'si'; $mensaje .='El número de placas es muy corto: ' . $placas . '<br>';}
	if (strlen($modelo) < 4) {$error = 'si'; $mensaje .='El modelo debe tener 4 números (2011): ' . $modelo . '<br>';}

//	echo '<br><br>====== ' . $error . ' ======<br><br>';
   if ($error === 'no') {
   	if($accion==='actualizar') {$parametros='vehiculo_id = ' . $vehiculo_id;} else {$parametros='';}
		$sql_data_array = array('vehiculo_placas' => $placas,
										'vehiculo_serie' => $serie,
										'vehiculo_marca' => $marca,
										'vehiculo_tipo' => $tipo,
										'vehiculo_subtipo' => $subtipo,
										'vehiculo_modelo' => $modelo,
										'vehiculo_puertas' => $puertas,
										'vehiculo_color' => $color,
										'vehiculo_aseguradora' => $aseguradora,
										'vehiculo_poliza' => $poliza,
										'vehiculo_cliente_id' => $cliente_id,
										'vehiculo_status' => $status);
      ejecutar_db($dbpfx . 'vehiculos', $sql_data_array, $accion, $parametros);
      if ($accion==='insertar') {
   		$vehiculo_id = mysql_insert_id();
     	} 
      redirigir('vehiculos.php?accion=consultar&vehiculo_id=' . $vehiculo_id);
	} else {
		include('idiomas/' . $idioma . '/vehiculos.php');
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		echo $mensaje;
	}
	
}

elseif ($accion==="consultar") {
//	echo 'Estamos en la sección  consulta';
	$error = 'si'; $num_cols = 0;
	if ($vehiculo_id!='') {
		$pregunta = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_id = '$vehiculo_id'";
		$error = 'no';
		} else {
		$placas=preparar_entrada_bd($placas);
		$cliente_id=preparar_entrada_bd($cliente_id);
		$mensaje= 'Se necesita al menos un dato para buscar.<br>';
		if (($placas!='') || ($cliente_id!='')) {
			$error = 'no'; $mensaje ='';
			$pregunta = "SELECT * FROM " . $dbpfx . "vehiculos WHERE ";
			if ($placas) {$pregunta .= "vehiculo_placas like '%$placas%' ";}
			if (($placas) && ($cliente_id)) {$pregunta .= "AND vehiculo_cliente_id = '$cliente_id' ";} 
				elseif ($cliente_id) {$pregunta .= "vehiculo_cliente_id = '$cliente_id' ";}
		}
	}
	if ($error ==='no') {
//		echo $pregunta;
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
	}
	if ($num_cols>0) {
?>
	<table cellspacing="2" cellpadding="2" border="0">
	<TH colspan="2" align="left">Datos de los vehículos:</TH>
<?php 
		while ($vehiculo = mysql_fetch_array($matriz)) {
			echo '		<tr><td>Número de vehículo</td><td>' . $vehiculo['vehiculo_id'] . '</td></tr>
		<tr><td>Placas</td><td>' . $vehiculo['vehiculo_placas'] . '</td></tr>
		<tr><td>Serie</td><td>' . $vehiculo['vehiculo_serie'] . '</td></tr>
		<tr><td>Marca</td><td>' . $vehiculo['vehiculo_marca'] . '</td></tr>
		<tr><td>Tipo</td><td>' . $vehiculo['vehiculo_tipo'] . '</td></tr>
		<tr><td>Subtipo</td><td>' . $vehiculo['vehiculo_subtipo'] . '</td></tr>
		<tr><td>Modelo</td><td>' . $vehiculo['vehiculo_modelo'] . '</td></tr>
		<tr><td>Color</td><td>' . $vehiculo['vehiculo_color'] . '</td></tr>
		<tr><td>Puertas</td><td>' . $vehiculo['vehiculo_puertas'] . '</td></tr>
		<tr><td>Aseguradora</td><td>' . $vehiculo['vehiculo_aseguradora'] . '</td></tr>
		<tr><td>Póliza</td><td>' . $vehiculo['vehiculo_poliza'] . '</td></tr>
		<tr><td>Cliente ID</td><td>' . $vehiculo['vehiculo_cliente_id'] . '</td></tr>
		<tr><td>Activo?</td><td>' . $vehiculo['vehiculo_status'] . '</td></tr>
		<tr><td colspan="2"><a href="vehiculos.php?accion=modificar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '">Modificar</a> | <a href="personas.php?accion=consultar&cliente_id=' . $vehiculo['vehiculo_cliente_id'] . '">Ver Datos del Cliente</a> | <a href="ordenes.php?accion=listar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '">Ver Ordenes de Trabajo</a> | <a href="ordenes.php?accion=crear&vehiculo_id=' . $vehiculo['vehiculo_id'] . '">Nueva Orden de Trabajo</a> | <a href="documentos.php?accion=listar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '">Documentos Relacionados</a></td></tr> 
		<tr><td colspan="2">-----------------------------------</td></tr>';
		}
		echo '	</table>';
	} else {
		$mensaje .='No se encontraron registros con esos datos.</br>';
		echo '<p>' . $mensaje . '</p>';
	}
}

elseif ($accion==="listar") {
//	echo 'Estamos en la sección listar';
	$cliente_id=preparar_entrada_bd($cliente_id);
	$error = 'si'; $num_cols = 0;
	$mensaje= 'Se necesita al menos un dato para buscar.<br>';
	$pregunta = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_cliente_id = '$cliente_id'";
   $matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	$num_cols = mysql_num_rows($matriz);
	if ($num_cols>0) {
		echo '	<table cellspacing="2" cellpadding="2" border="0">
	<TH colspan="2" align="left">Datos de los vehiculos:</TH>
	<tr><td>Vehículo</td><td>Placas</td><td>Marca</td><td>Tipo</td><td>Modelo</td><td colspan="2">Acciones</td></tr>';
		while ($vehiculo = mysql_fetch_array($matriz)) {
			echo '		<tr>
				<td>' . $vehiculo['vehiculo_id'] . '</td>
				<td>' . $vehiculo['vehiculo_placas'] . '</td>
				<td>' . $vehiculo['vehiculo_marca'] . '</td>
				<td>' . $vehiculo['vehiculo_tipo'] . '</td>
				<td>' . $vehiculo['vehiculo_modelo'] . '</td>
				<td><a href="vehiculos.php?accion=consultar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '">Detalles</a></td>
				<td><a href="ordenes.php?accion=listar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '">Listar Ordenes de Trabajo</a></td>
				<td><a href="documentos.php?accion=listar&vehiculo_id=' . $vehiculo['vehiculo_id'] . '">Documentos Relacionados</a></td>
			</tr>';
		}
		echo '	</table>';
	} else {
		$mensaje .='No se encontraron registros con esos datos.</br>';
		echo '<p>' . $mensaje . '</p>';
	}
}
?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>