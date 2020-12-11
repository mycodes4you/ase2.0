<?php
	echo '<div id="mySidenav" class="sidenav">'."\n";
	echo '<a href="#" class="closebtn" onclick="closeNav();">&times;</a>'."\n";
	echo '<a href="index.php"><img src="idiomas/es_MX/imagenes/inicio.png" alt="Inicio" title="Inicio" class="imgnav"> Inicio</a>'."\n";
  		if (validaAcceso('1040040', $dbpfx) == '1' || ($_SESSION['codigo'] > '0' && $_SESSION['codigo'] != '70')) { 
  	echo '<a href="ordenes-de-trabajo.php"><img src="idiomas/es_MX/imagenes/orden-de-trabajo.png" alt="Ordenes de Trabajo" title="Ordenes de Trabajo" class="imgnav"> Ordenes de Trabajo</a>';
  		}
		if ($_SESSION['codigo'] > '0' && $_SESSION['codigo'] != '60' && $_SESSION['codigo'] != '70' && $_SESSION['codigo'] < '2000') {
	echo '<a href="usuarios.php"><img src="idiomas/es_MX/imagenes/usuarios.png" alt="Usuarios" title="Usuarios" class="imgnav"> Usuarios</a>';
		}
		if (validaAcceso('1010000', $dbpfx) == '1' || ($_SESSION['codigo'] > '0' && $_SESSION['codigo'] <= '30')) {
	echo '<a href="clientes.php"><img src="idiomas/es_MX/imagenes/clientes.png" alt="Clientes" title="Clientes" class="imgnav"> Clientes</a>';
		}
		if (validaAcceso('1000000', $dbpfx) == '1' || ($_SESSION['codigo'] > '0' && ($_SESSION['codigo'] <= '50' || ($solovalacc != 1 && ($_SESSION['rol13'] == '1'))))) {
	echo '<a href="almacen.php"><img src="idiomas/es_MX/imagenes/refacciones.png" alt="Almacén" title="Almacén" class="imgnav"> Almacén</a>
			<a href="monitoreo.php?accion=listar"><img src="idiomas/es_MX/imagenes/buscar.png" alt="Monitoreo" title="Monitoreo" class="imgnav">Monitoreo</a>';
		}
		if(validaAcceso('1100000', $dbpfx) == '1') {
	echo '<a href="produccion.php"><img src="idiomas/es_MX/imagenes/produccion.png" alt="Produccion" title="Produccion" class="imgnav"> Produccion</a>';
		} 
		elseif($solovalacc != '' && ($_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1')) {
	echo '<a href="produccion.php"><img src="idiomas/es_MX/imagenes/produccion.png" alt="Produccion" title="Produccion" class="imgnav"> Produccion</a>';
		}
		if (validaAcceso('1060000', $dbpfx) == '1' || ($_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1' || $_SESSION['rol12'] == '1')) {
  	echo '<a href="informes.php"><img src="idiomas/es_MX/imagenes/reportes.png" alt="Reportes" title="Reportes" class="imgnav"> Reportes</a>';
  		}
		if ($asientos == '1' && validaAcceso('1150000', $dbpfx) == '1') {
	echo '<a href="contabilidad.php"><img src="idiomas/es_MX/imagenes/contabilidad.png" alt="Contabilidad" title="Contabilidad" class="imgnav"> Contabilidad</a>';
		}
		if($seguimiento == 1 && isset($_SESSION['usuario'])) {
  	echo '<a href="seguimiento.php?accion=directo"><img src="idiomas/es_MX/imagenes/seguimiento.png" alt="Seguimiento" title="Seguimiento" class="imgnav"> Seguimiento</a>';
  		} 
  		elseif($seguimiento != 1 && $_SESSION['codigo'] != '70' && $_SESSION['codigo'] < '2000' ) {
  	echo '<a href="seguimiento.php?accion=directo"><img src="idiomas/es_MX/imagenes/seguimiento.png" alt="Seguimiento" title="Seguimiento" class="imgnav"> Seguimiento</a>';
  		}		
  	echo '<a href="contacto.php"><img src="idiomas/es_MX/imagenes/contacto.png" alt="Contacto" title="Contacto" class="imgnav"> Contacto</a>';
  		if (isset($_SESSION['usuario'])) {
	echo '<a href="usuarios.php?accion=terminar"><img src="idiomas/' . $idioma . '/imagenes/terminar-sesion.png" alt="Terminar Sesión" title="Terminar Sesión" class="imgnav"> Terminar Sesión</a>';
		}
  		
echo '</div>'."\n";
echo '<div class="hamburguesa" onclick="openNav();" style="font-size: 30px">&#9776;</div>'."\n";

echo '<script type="text/javascript">'."\n";
echo '	function openNav() { '."\n";
echo '  document.getElementById("mySidenav").style.width = "250px";'."\n";
echo '	}';

echo ' function closeNav() {'."\n";
echo ' document.getElementById("mySidenav").style.width = "0";'."\n";
echo '	}'."\n";
echo '</script>'."\n";
?>
