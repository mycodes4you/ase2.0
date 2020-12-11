<?php 
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/almacen.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

if (validaAcceso('1000000', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol02']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol13']=='1'))) {
	$msj='Acceso autorizado';
} else {
	 redirigir('usuarios.php?mensaje='.$lang['no_autorizado']);
}

	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	echo '			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td style="width:33%; vertical-align:top;">
						<div class="obscuro espacio" style="position:relative;">
							<h3>' . $lang['Proveedores'] . '</h3>
							<table cellpadding="0" cellspacing="0" border="0" width="100%">
								<tr>
									<td><a href="proveedores.php?accion=crear"><img src="idiomas/' . $idioma . '/imagenes/proveedores-nuevo.png" alt="Agregar nuevo proveedor" title="Agregar nuevo proveedor" border="0"></a></td>
									<td><a href="proveedores.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/proveedores-listar.png" alt="Listar proveedores" title="Listar proveedores"></a></td>
									<td><a href="proveedores.php?accion=pagos"><img src="idiomas/' . $idioma . '/imagenes/proveedores-pagos.png" alt="Listar Pagos a Proveedores" title="Listar Pagos a Proveedores"></a></td>
									<td><a href="proveedores.php?accion=cuentasxpagar"><img src="idiomas/' . $idioma . '/imagenes/cuentasxpagar.png" alt="Cuentas por Pagar" title="Cuentas por Pagar"></a></td>
									<td><a href="extrae_partes.php"><img src="idiomas/' . $idioma . '/imagenes/consulta_ref.png" alt="Consulta refacciones" title="Consulta Partes" border="0"></a></td>
								</tr>
							</table>
						</div>
						<div class="obscuro espacio">
							<h3>' .$lang['Consultar Proveedor'].'</h3>
							<form action="proveedores.php?accion=consultar" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>' .$lang['Razón Social'].'</td><td><input type="text" name="nombre" size="24" maxlength="60" /></td></tr>
									<tr><td>' .$lang['Apodo NIC'].' </td><td><input type="text" name="nic" size="24" maxlength="40" /></td></tr>
									<tr><td>' .$lang['e-Mail'].' </td><td><input type="text" name="email" size="24" maxlength="120" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="' .$lang['Enviar'].'" />&nbsp;<input type="reset" name="limpiar" value="' .$lang['Borrar'].'" /></td></tr>
								</table>
							</form>
						</div>
						<div class="obscuro espacio">
							<h3>' .$lang['Modificar Proveedor'].'</h3>
							<form action="proveedores.php?accion=modificar" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>' .$lang['Número de Proveedor:'].' </td><td><input type="text" name="prov_id" size="10" maxlength="11" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="' .$lang['Enviar'].'" />&nbsp;<input type="reset" name="limpiar" value="' .$lang['Borrar'].'" /></td></tr>
								</table>
							</form>

						</div>
					</td>
					<td style="width:33%; vertical-align:top;">
						<div class="obscuro espacio">
							<h3>' .$lang['Almacén de Refacciones'] . '</h3>
							<table cellpadding="0" cellspacing="0" border="0">
								<tr><td><a href="refacciones.php?accion=listar">'.$lang['Listado de Refacciones'].'</a></td></tr>
								<tr><td><a href="refacciones.php?accion=listpaqs">' .$lang['Paquetes de Servicio'].'</a></td></tr>
								<tr><td>' .$lang['Reportes de Refacciones'].'</td></tr>
								<tr><td colspan="2" style="text-align:left;"></td></tr>
							</table>
						</div>
						<div class="obscuro espacio">
							<h3>' .$lang['Pedidos de Refacciones'].'</h3>
							<form action="pedidos.php?accion=consultar" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td colspan="2"><a href="refacciones.php?accion=pendientes">' .$lang['Listado de Pendientes'].'</a></td></tr>
									<tr><td colspan="2"><a href="cambdevol.php?accion=registrar">' .$lang['CambDevol'].'</a></td></tr>
									<tr><td colspan="2"><a href="pedidos.php?accion=listar">' .$lang['Listado de Pedidos'].'</a></td></tr>
									<tr><td>' .$lang['# Pedido'].'</td><td><input type="text" name="pedido" size="8" maxlength="11" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="' .$lang['Enviar'].'" />&nbsp;<input type="reset" name="limpiar" value="' .$lang['Borrar'].'" /></td></tr>
								</table>
							</form>
						</div>
						<div class="obscuro espacio">
							<h3>' .$lang['Venta de Refacciones'] . '</h3>
							<form action="salidas.php?accion=venta" method="post">
							<table cellpadding="0" cellspacing="0" border="0">
								<tr><td><a href="salidas.php?accion=listar">'.$lang['Listado de Refacciones'].'</a></td></tr>
								<tr><td>' .$lang['Ventas por Cobrar'] . '</td></tr>
								<tr><td>' .$lang['Ventas Cobradas'] . '</td></tr>
								<tr><td>' .$lang['# Venta'].' <input type="text" name="venta" size="8" maxlength="11" /></td></tr>
								<tr><td colspan="2" style="text-align:left;"><input type="submit" value="' .$lang['Enviar'].'" /></td></tr>
							</table>
							</form>
						</div>
					</td>
					<td style="width:33%; vertical-align:top;">
						<div class="obscuro espacio">
							<h3>' .$lang['Autorización y Seguimiento de Refacciones'].'</h3>
							<form action="refacciones.php?accion=gestionar" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>' .$lang['# Orden de Trabajo'].'</td><td><input type="text" name="orden_id" size="8" maxlength="11" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="' .$lang['Enviar'].'" />&nbsp;<input type="reset" name="limpiar" value="'.$lang['Borrar'].'" /></td></tr></table></form></div><div class="obscuro espacio"></div></td></tr></table>';
?>			
		</div>
	</div>
<?php include('parciales/pie.php');
/* Archivo index.php */
/* e-Taller */