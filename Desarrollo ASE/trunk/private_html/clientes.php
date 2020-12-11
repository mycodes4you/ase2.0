<?php 
include('parciales/funciones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

if (validaAcceso('1010000', $dbpfx) == '1' || $solovalacc != 1 || $_SESSION['codigo'] < '60' || $_SESSION['codigo'] == '2000') {
	$msg=$lang['Acceso Autorizado'];
} else {
	redirigir('usuarios.php?mensaje='.$lang['Acceso NO autorizado']);
}

include('idiomas/' . $idioma . '/clientes.php');


include('parciales/encabezado.php'); 
echo '<div id="body"><br>'."\n";
  include('parciales/menu_inicio.php'); 
  echo '<div id="principal">'."\n";
  echo '	<div class="row">
  				<div class="col-lg-9 col-md-12 col-md-12">
  					<div class="col-lg-4 col-md-4 col-sm-6">';
  
	echo '		
					<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
						<h3>'. $lang['Clientes'].'</h3>'."\n";
	if ($_SESSION['codigo'] < '60') { 
		echo '<a href="personas.php?accion=crear"><img src="idiomas/' . $idioma . '/imagenes/cliente-agregar.png" alt="'. $lang['Agregar nuevo cliente'].'" title="'. $lang['Agregar nuevo cliente'].'" border="0"></a> '."\n";
	}
	if (validaAcceso('1125035', $dbpfx) == '1' || ($solovalacc != 1)) {
		echo '<a href="personas.php?accion=cuentasxcobrar"><img src="idiomas/' . $idioma . '/imagenes/cuentasxcobrar.png" alt="'. $lang['Cuentas por Cobrar'].'" title="'. $lang['Cuentas por Cobrar'].'" border="0"></a>'."\n";
	}
// ----- Reporte de clientes en formato excel
	if (validaAcceso('1090015', $dbpfx) == '1') {
		echo '<a href="personas.php?accion=exportarclientes"><img src="idiomas/' . $idioma . '/imagenes/reporte-clientes.png" alt="'. $lang['ListaClientes'].'" title="'. $lang['ListaClientes'].'" border="0"></a>'."\n";
	}
	echo'					</div>
					<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
						<h3>'. $lang['Consultar Cliente'].'<br><span style="font-size:smaller;">'. $lang['los siguientes campos'].'</span></h3>
						<form action="personas.php?accion=consultar" method="post">
							<table cellpadding="0" cellspacing="0" border="0">
								<tr><td>'. $lang['Número'].'</td><td><input type="text" name="cliente_id" size="24" maxlength="60" /></td></tr>
								<tr><td>'. $lang['Empresa'].'</td><td><input type="text" name="empresa" size="24" maxlength="60" /></td></tr>
								<tr><td>'. $lang['Nombre'].'</td><td><input type="text" name="nombre" size="24" maxlength="60" /></td></tr>
								<tr><td>'. $lang['Apellido'].'</td><td><input type="text" name="apellidos" size="24" maxlength="60" /></td></tr>
								<tr><td>'. $lang['e-Mail'].'</td><td><input type="text" name="email" size="24" maxlength="120" /></td></tr>
								<tr><td>'. $lang['Teléfono Trabajo'].'</td><td><input type="text" name="telefono1" size="24" maxlength="40" /></td></tr>
								<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md"value="'. $lang['Enviar'].'" />&nbsp;<input type="reset" class="btn btn-danger btn-md" name="limpiar" value="'. $lang['Borrar'].'" /></td></tr>
							</table>
						</form>
						<p>&nbsp;</p>
					</div>
					<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
						<h3>'. $lang['Modificar Cliente'].'</h3>
						<form action="personas.php?accion=modificar" method="post">
							<table cellpadding="0" cellspacing="0" border="0">
								<tr><td>'. $lang['Número de Cliente'].'</td><td><input type="text" name="cliente_id" size="10" maxlength="11" /></td></tr>
								<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md"value="'. $lang['Enviar'].'" />&nbsp;<input type="reset" class="btn btn-danger btn-md" name="limpiar" value="'. $lang['Borrar'].'" /></td></tr>
							</table>
						</form>
					</div></div>
				'."\n";
	if (validaAcceso('1135030', $dbpfx) == 1 || $_SESSION['codigo'] <= 15 || ($solovalacc != 1 && ($_SESSION['rol01'] == 1 || $_SESSION['rol02'] == 1 || $_SESSION['rol03'] == 1 || $_SESSION['rol04'] == 1))) {
		echo '	<div class="col-lg-4 col-md-4 col-sm-6">				
					<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
						<h3>Aseguradoras</h3>
						<a href="aseguradoras.php?accion=crear"><img src="idiomas/' . $idioma . '/imagenes/aseguradora-nueva.png" alt="Agregar nueva aseguradora" title="Agregar nueva aseguradora" border="0"></a></div>
					<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
						<h3>Consultar Aseguradora</h3>
						<form action="aseguradoras.php?accion=consultar" method="post">
							<table cellpadding="0" cellspacing="0" border="0">
								<tr><td>Razón Social </td><td><input type="text" name="nombre" size="24" maxlength="60" /></td></tr>
								<tr><td>Apodo NIC </td><td><input type="text" name="nic" size="24" maxlength="40" /></td></tr>
								<tr><td>e-Mail </td><td><input type="text" name="email" size="24" maxlength="120" /></td></tr>
								<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md"value="Enviar" />&nbsp;<input type="reset" class="btn btn-danger btn-md" name="limpiar" value="Borrar" /></td></tr>
							</table>
						</form>
					</div>
					<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
						<h3>Modificar Aseguradora</h3>
						<form action="aseguradoras.php?accion=modificar" method="post">
							<table cellpadding="0" cellspacing="0" border="0">
								<tr><td>Número de Aseguradora: </td><td><input type="text" name="aseguradora_id" size="10" maxlength="11" /></td></tr>
								<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md"value="Enviar" />&nbsp;<input type="reset" class="btn btn-danger btn-md" name="limpiar" value="Borrar" /></td></tr>
							</table>
						</form>
					</div></div>
				'."\n";
	}
	echo '			<div class="col-lg-4 col-md-4 col-sm-6">	
					<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
						<h3>'. $lang['Nuevo Vehículo'].'</h3>
							'. $lang['vehículos agregan desde el cliente'].'
					</div>
					<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
						<h3>'. $lang['Consultar Vehículo'].'</h3>
						<form action="vehiculos.php?accion=listar" method="post">
							<table cellpadding="0" cellspacing="0" border="0">
								<tr><td>'. $lang['Placas'].'</td><td><input type="text" name="placas" size="24" maxlength="60" /></td></tr>
								<tr><td>'. $lang['Serie'].'</td><td><input type="text" name="serie" size="24" maxlength="60" /></td></tr>
								<tr><td>'. $lang['Cliente ID'].'</td><td><input type="text" name="cliente_id" size="24" maxlength="120" /></td></tr>
								<tr><td>'. $lang['Vehículo ID'].'</td><td><input type="text" name="vehiculo_id" size="24" maxlength="120" /></td></tr>
								<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md"value="'. $lang['Enviar'].'" />&nbsp;<input type="reset" class="btn btn-danger btn-md" name="limpiar" value="'. $lang['Borrar'].'" /></td></tr>
							</table>
						</form>
						<p>&nbsp;</p>
					</div></div>
				'."\n";
	echo '		</div></div>	
		</div>
	</div>';
	include('parciales/pie.php');
 /* Archivo index.php */
/* e-Taller */
