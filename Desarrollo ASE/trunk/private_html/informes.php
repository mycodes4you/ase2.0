<?php 
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/informes.php');
include('parciales/encabezado.php'); 

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

$funnum = 1060000;

echo '	<div id="body">' . "\n";
include('parciales/menu_inicio.php');
echo '		<div id="principal">
			<table cellpadding="0" cellspacing="0" border="0" width="80%">
				<tr>
					<td valign="top" width="50%">
						<div class="obscuro espacio">
							<form action="reportes.php?periodo=mespasado" method="post">
								<table cellpadding="0" cellspacing="0" border="0">';
if ($_SESSION['rol02'] == '1') {
	echo '									<tr><td><h3>Gráficas</h3></td></tr>
									<tr><td><input type="image" src="imagenes/graficas.png" name="graficas" value="graficas" /></td></tr>
									<tr><td>&nbsp;</td></tr>'."\n";
}
if ($_SESSION['rol02'] == '1' && $perfcr == '1') {
	echo '									<tr><td><h3>Performance</h3></td></tr>
									<tr><td><a href="performance-talleres.php">Desempeño del Centro de Reparación</td></tr>'."\n";
}
echo '									<tr><td><h3>Reportes</h3></td></tr>
									<tr><td><input type="image" src="imagenes/reportes.png" name="reportes" value="reportes" /></td></tr>'."\n";
echo '								</table>
							</form>

						</div>
					</td>
					<td valign="top" width="50%">
						<div>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>';
include('parciales/pie.php');
/* Archivo index.php */
/* e-Taller */