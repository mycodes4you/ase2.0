<?php
	if($_SESSION['cambio_pass'] == 1) {
		echo '			<div id="menu">
				<a href="contacto.php"><img src="idiomas/' . $idioma . '/imagenes/contacto.png" alt="Contacto, Soporte y Ayuda" title="Contacto, Soporte y Ayuda"></a>
				<a href="usuarios.php?accion=terminar"><img src="idiomas/' . $idioma . '/imagenes/terminar-sesion.png" alt="Terminar Sesión" title="Terminar Sesión"></a>
			</div>'."\n";
	} else {
		echo '			<div id="menu">
				<a href="index.php"><img src="idiomas/' . $idioma . '/imagenes/inicio.png" alt="Inicio" title="Inicio"></a>'."\n";
		if (validaAcceso('1040040', $dbpfx) == '1' || ($_SESSION['codigo'] > '0' && $_SESSION['codigo'] != '70')) {
			echo '				<a href="ordenes-de-trabajo.php"><img src="idiomas/' . $idioma . '/imagenes/orden-de-trabajo.png" alt="Ordenes de Trabajo" title="Ordenes de Trabajo"></a>'."\n";
		}
		if (validaAcceso('1135000', $dbpfx) == '1' || ($_SESSION['codigo'] > '0' && $_SESSION['codigo'] != '60' && $_SESSION['codigo'] != '70' && $_SESSION['codigo'] < '2000')) {
			echo '				<a href="usuarios.php"><img src="idiomas/' . $idioma . '/imagenes/usuarios.png" alt="Usuarios" title="Usuarios"></a>'."\n";
		}
		if (validaAcceso('1010000', $dbpfx) == '1' || $_SESSION['codigo'] > '0' && $_SESSION['codigo'] <= '30') {
			echo '				<a href="clientes.php"><img src="idiomas/' . $idioma . '/imagenes/clientes.png" alt="Clientes" title="Clientes"></a>'."\n";
		}

		if (validaAcceso('1000000', $dbpfx) == '1' || $_SESSION['codigo'] > '0' && ($_SESSION['codigo'] <= '50' || $_SESSION['rol13'] == '1')) {
			echo '				<a href="gestion.php"><img src="idiomas/' . $idioma . '/imagenes/refacciones.png" alt="Almacén" title="Almacén"></a>'."\n";
			echo '				<a href="monitoreo.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/buscar.png" alt="Seguimiento" title="Monitoreo"></a>'."\n";
		}

		if ($_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
			echo '				<a href="produccion.php"><img src="idiomas/' . $idioma . '/imagenes/produccion.png" alt="Express" title="Express"></a>'."\n";
		}
		$resultado1060 = validaAcceso('1060000', $dbpfx);
		if ($resultado1060 == '1' || $_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1' || $_SESSION['rol13'] == '1') {
			echo '				<a href="informes.php"><img src="idiomas/' . $idioma . '/imagenes/reportes.png" alt="Reportes" title="Reportes"></a>'."\n";
		}

		$resultado = validaAcceso('1150000', $dbpfx);
		if ($asientos == '1' && $resultado == '1') {
			echo '				<a href="contabilidad.php"><img src="idiomas/' . $idioma . '/imagenes/contabilidad.png" alt="Módulo de Contabilidad" title="Módulo de Contabilidad"></a>'."\n";
		}
		if ($_SESSION['codigo'] != '70' && $_SESSION['codigo'] < '2000' ) {
			echo '				<a href="seguimiento.php?accion=directo"><img src="idiomas/' . $idioma . '/imagenes/seguimiento.png" alt="Seguimiento" title="Seguimiento"></a>'."\n";
		}
		echo '				<a href="contacto.php"><img src="idiomas/' . $idioma . '/imagenes/contacto.png" alt="Contacto, Soporte y Ayuda" title="Contacto, Soporte y Ayuda"></a>'."\n";
		if (isset($_SESSION['usuario'])) {
			echo '				<a href="usuarios.php?accion=terminar"><img src="idiomas/' . $idioma . '/imagenes/terminar-sesion.png" alt="Terminar Sesión" title="Terminar Sesión"></a>'."\n";
		}
		if (isset($_SESSION['usuario'])) {
//			echo '				<span style="padding-left: 10px;">Usuario: ' . $_SESSION['puesto'] . ' ' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '</span>'."\n";
			echo '				<span style="padding-left: 10px;">Usuario: ' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '</span>'."\n";
			$pregc1 = "SELECT c.orden_id, c.bit_id, c.fecha_com, c.usuario, c.comentario, c.interno, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.usuario = u.usuario AND c.interno = '3' AND c.para_usuario = '" . $_SESSION['usuario'] . "' AND fecha_visto IS NULL ORDER BY c.bit_id DESC";
			$matrc1 = mysql_query($pregc1) or die("ERROR: Fallo selección de comentarios! " . $pregc1);
			$filac1 = mysql_num_rows($matrc1);
			if($filac1 > 0) {
				echo '<a href="index.php"><img src="imagenes/comentarios-pendientes.png" alt="Comentarios Por Resolver" title="Comentarios Por Resolver"></a> ' . $filac1 . "\n";
			}
		}
		echo '			</div>'."\n";
	}
	echo '			<div style="clear: both;">'."\n";
	echo '			<span class="alerta"';
	if($_SESSION['codigo'] == '75') { echo ' style="font-size:28px; line-height: 120%;">'; } else { echo '>';} 
	echo$_SESSION['msjerror'] . '</span>'."\n";
	echo '			</div>'."\n";
		unset($_SESSION['msjerror']);
?>
