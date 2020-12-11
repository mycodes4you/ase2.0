<?php
include('menu_responsivo.php');
	if($_SESSION['cambio_pass'] == 1) {
		echo '		<div id="menu2" class="col-lg-12 col-md-8 col-sm-6" style="background-position-x: right; background-position-y: center;">
				<a href="contacto.php"><img src="idiomas/' . $idioma . '/imagenes/contacto.png" alt="Contacto, Soporte y Ayuda" title="Contacto, Soporte y Ayuda"></a>
				<a href="usuarios.php?accion=terminar"><img src="idiomas/' . $idioma . '/imagenes/terminar-sesion.png" alt="Terminar Sesión" title="Terminar Sesión"></a>
			</div>'."\n";
	} else {
		echo '		<div class="row" style="background-color: white; margin-bottom: 10px;">
			<div class="menugrande">
			<div class="col-lg-6 col-md-12 col-sm-12" style="height:120px; display:flex; justify-content:left; align-items:center;">
				<div id="menu2">
					<center><a href="index.php"><img src="idiomas/' . $idioma . '/imagenes/inicio.png" alt="Inicio" title="Inicio"></a>'."\n";
		if (validaAcceso('1040040', $dbpfx) == '1' || ($_SESSION['codigo'] > '0' && $_SESSION['codigo'] != '70')) {
			echo '					<a href="ordenes-de-trabajo.php"><img src="idiomas/' . $idioma . '/imagenes/orden-de-trabajo.png" alt="Ordenes de Trabajo" title="Ordenes de Trabajo"></a>'."\n";
		}
		if (validaAcceso('1135000', $dbpfx) == '1' || ($_SESSION['codigo'] > '0' && $_SESSION['codigo'] != '60' && $_SESSION['codigo'] != '70' && $_SESSION['codigo'] < '2000')) {
			echo '					<a href="usuarios.php"><img src="idiomas/' . $idioma . '/imagenes/usuarios.png" alt="Usuarios" title="Usuarios"></a>'."\n";
		}
		if (validaAcceso('1010000', $dbpfx) == '1' || ($_SESSION['codigo'] > '0' && $_SESSION['codigo'] <= '30')) {
			echo '					<a href="clientes.php"><img src="idiomas/' . $idioma . '/imagenes/clientes.png" alt="Clientes" title="Clientes"></a>'."\n";
		}
		if (validaAcceso('1000000', $dbpfx) == '1' || ($_SESSION['codigo'] > '0' && ($_SESSION['codigo'] <= '50' || ($solovalacc != 1 && ($_SESSION['rol13'] == '1'))))) {
			echo '					<a href="almacen.php"><img src="idiomas/' . $idioma . '/imagenes/refacciones.png" alt="Almacén" title="Almacén"></a><a href="monitoreo.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/buscar.png" alt="Monitoreo" title="Monitoreo"></a>'."\n";
		}
		if(validaAcceso('1100000', $dbpfx) == '1') {
			echo '					<a href="produccion.php"><img src="idiomas/' . $idioma . '/imagenes/produccion.png" alt="Producción" title="Producción"></a>'."\n";
		} elseif($solovalacc != '' && ($_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1')) {
			echo '					<a href="produccion.php"><img src="idiomas/' . $idioma . '/imagenes/produccion.png" alt="Producción" title="Producción"></a>'."\n";
		}
		if (validaAcceso('1060000', $dbpfx) == '1' || ($_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1' || $_SESSION['rol12'] == '1')) {
			echo '					<a href="informes.php"><img src="idiomas/' . $idioma . '/imagenes/reportes.png" alt="Reportes" title="Reportes"></a>'."\n";
		}
		if ($asientos == '1' && validaAcceso('1150000', $dbpfx) == '1') {
			echo '					<a href="contabilidad.php"><img src="idiomas/' . $idioma . '/imagenes/contabilidad.png" alt="Módulo de Contabilidad" title="Módulo de Contabilidad"></a>'."\n";
		}
		if($seguimiento == 1 && isset($_SESSION['usuario'])) {
			echo '					<a href="seguimiento.php?accion=directo"><img src="idiomas/' . $idioma . '/imagenes/seguimiento.png" alt="Seguimiento" title="Seguimiento"></a>'."\n";
		} elseif($seguimiento != 1 && $_SESSION['codigo'] != '70' && $_SESSION['codigo'] < '2000' ) {
			echo '					<a href="seguimiento.php?accion=directo"><img src="idiomas/' . $idioma . '/imagenes/seguimiento.png" alt="Seguimiento" title="Seguimiento"></a>'."\n";
		}
		echo '					<a href="contacto.php"><img src="idiomas/' . $idioma . '/imagenes/contacto.png" alt="Contacto, Soporte y Ayuda" title="Contacto, Soporte y Ayuda"></a>'."\n";
		if (isset($_SESSION['usuario'])) {
			echo '						<a href="usuarios.php?accion=terminar"><img src="idiomas/' . $idioma . '/imagenes/terminar-sesion.png" alt="Terminar Sesión" title="Terminar Sesión"></a>
					</center>
				</div>
			</div>
			</div>
			<div class="col-lg-4 col-md-12 col-sm-12" style="height: 65px;display: flex;justify-content: center;align-items: center;">
				<center>
					<br><span style="padding-center: 10px;"><b><big>Usuario: </big>' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '</b></span>'."\n";
			$pregc1 = "SELECT c.orden_id, c.bit_id, c.fecha_com, c.usuario, c.comentario, c.interno, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.usuario = u.usuario AND c.interno = '3' AND c.para_usuario = '" . $_SESSION['usuario'] . "' AND fecha_visto IS NULL ORDER BY c.bit_id DESC";
			$matrc1 = mysql_query($pregc1) or die("ERROR: Fallo selección de comentarios! " . $pregc1);
			$filac1 = mysql_num_rows($matrc1);
			if($filac1 > 0) {
				echo '					<br><a href="index.php"><img src="imagenes/comentarios-pendientes2.png" alt="Comentarios Por Resolver" title="Comentarios Por Resolver"></a>
					<span style="position: relative;top: -15px;left: -9px;z-index: 0;background-color: red;color: white;border-radius: 100%;padding-right: 5px;padding-left: 5px;padding-top: 1px;padding-bottom: 1px;"><b>' . $filac1 . '</b>
					</span>'."\n";
			}
			echo '				</center>
			</div>
			<div class="col-lg-2 col-md-12 col-sm-12 logoinstancia"> <img src="particular/logo-agencia.png"> </div>
		</div>'."\n";
		}	else {
echo '			</center>
				</div>
			</div>
			</div>
			
			<div class="col-lg-3 col-md-12 col-sm-12 logoinstancia"> <img src="particular/logo-agencia.png"></div>	
	</div>'."\n";
		}
	}
	echo '			<div style="clear: both;">'."\n";
	if($_SESSION['msjerror'] != '') {
		echo '<div ';
		if($_SESSION['codigo'] == '75') { echo 'class="alert alert-danger" role="alert">'; } else { echo 'class="alert alert-danger" role="alert">';}
		echo $_SESSION['msjerror'] . '</div>'."\n";
	}
	if($_GET['msjpass'] != '') {
		echo '<div class="alert alert-success" role="alert">';
		echo '<div>' . $_GET['msjpass'] . '</div>'."\n";
	}

//	if(isset($_SESSION['microtime'])) { echo ' Micro tiempo: ' . $_SESSION['microtime']; }
	echo '		</div>'."\n";
	if($_SESSION['nvavista'] != 1) {
		unset($_SESSION['msjerror']);
		unset($_SESSION['msjpass']);
	}
	?>
