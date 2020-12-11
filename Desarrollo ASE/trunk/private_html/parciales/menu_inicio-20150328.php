<?php

	echo '			<div id="menu">
				<a href="index.php"><img src="idiomas/' . $idioma . '/imagenes/inicio.png" alt="Inicio" title="Inicio"></a>'."\n";
	if ($_SESSION['codigo'] > '0' && $_SESSION['codigo'] != '60' && $_SESSION['codigo'] != '70') {
		echo '				<a href="ordenes-de-trabajo.php"><img src="idiomas/' . $idioma . '/imagenes/orden-de-trabajo.png" alt="Ordenes de Trabajo" title="Ordenes de Trabajo"></a>'."\n";
	}
	echo '				<a href="usuarios.php"><img src="idiomas/' . $idioma . '/imagenes/usuarios.png" alt="Usuarios" title="Usuarios"></a>'."\n";
	if ($_SESSION['codigo'] > '0' && $_SESSION['codigo'] <= '30') {
		echo '				<a href="clientes.php"><img src="idiomas/' . $idioma . '/imagenes/clientes.png" alt="Clientes" title="Clientes"></a>'."\n";
	}
	if ($_SESSION['codigo'] > '0' && ($_SESSION['codigo'] <= '50' || $_SESSION['rol13'] == '1')) {
		echo '				<a href="almacen.php"><img src="idiomas/' . $idioma . '/imagenes/refacciones.png" alt="Almacén" title="Almacén"></a>'."\n";
		echo '				<a href="monitoreo.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/buscar.png" alt="Seguimiento" title="Monitoreo"></a>'."\n";
	}

	if ($_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
		echo '				<a href="produccion.php"><img src="idiomas/' . $idioma . '/imagenes/produccion.png" alt="Producción" title="Producción"></a>'."\n";
		
	}
	$resultado1125 = validaAcceso('1125105', $dbpfx);
	if ($resultado1125 == '1' || $_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
		echo '				<a href="informes.php"><img src="idiomas/' . $idioma . '/imagenes/reportes.png" alt="Reportes" title="Reportes"></a>'."\n";
	}
	if ($extrae_partes == '1' && $_SESSION['rol12'] == '1') {
		echo '				<a href="extrae_partes.php"><img src="idiomas/' . $idioma . '/imagenes/busca-partes.png" alt="Busca Precios de Partes" title="Busca Precios de Partes"></a>'."\n";
	}
	
	$resultado = validaAcceso('1150000', $dbpfx);
	if ($asientos == '1' && $resultado == '1') {
		echo '				<a href="contabilidad.php"><img src="idiomas/' . $idioma . '/imagenes/contabilidad.png" alt="Módulo de Contabilidad" title="Módulo de Contabilidad"></a>'."\n";
	}

	echo '				<a href="seguimiento.php?accion=directo"><img src="idiomas/' . $idioma . '/imagenes/seguimiento.png" alt="Seguimiento" title="Seguimiento"></a>'."\n";
	echo '				<a href="contacto.php"><img src="idiomas/' . $idioma . '/imagenes/contacto.png" alt="Contacto, Soporte y Ayuda" title="Contacto, Soporte y Ayuda"></a>'."\n";			
	if (isset($_SESSION['usuario'])) {
//	echo '				<span style="padding-left: 10px;">Usuario: ' . $_SESSION['puesto'] . ' ' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '</span>'."\n";
		echo '				<span style="padding-left: 10px;">Usuario: ' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '</span>'."\n";
	}
	echo '			</div>
		<div style="clear: both;">'."\n";
		echo '<span class="alerta">' . $_SESSION['msjerror'] . '</span>'."\n";
		unset($_SESSION['msjerror']);
?>