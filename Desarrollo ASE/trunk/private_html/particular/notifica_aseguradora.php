<?php

$pregcli = "SELECT c.cliente_nombre, c.cliente_apellidos, c.cliente_email, c.cliente_telefono1 FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE c.cliente_id = o.orden_cliente_id AND o.orden_id = '" . $orden_id . "'";
$matrcli = mysql_query($pregcli) or die("ERROR: Fallo selección de datos de cliente!");
$clie = mysql_fetch_array($matrcli);

$contenido = '<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				</br>
				<h3>Estimados Clientes de ' . strtoupper($asenoti[$aseguradora]['razon']) . '</h3>
				<p class="lead">Les informamos que hoy ' . date('d-m-Y', time()) . ' ' . $situacion . ' el siguiente vehículo:</p>	
				<table bgcolor="#AECCF2">
					<tr>
						<th align="center">' . $lang['vehiculo'] . ':</th>
            			<td>' . $dato['marca'] . ' ' . $dato['tipo'] . ' ' . $dato['color'] . ' ' . $dato['modelo'] . '</td>
					</tr>
					<tr>
						<th align="center">' . $lang['placas'] . '</th>
            			<td>' . $dato['placas'] . '</td>
					</tr>
				</table>	
				<br>
				<p class="lead">Este vehículo ingresó con el siniestro ' . $reporte . ' que atendemos con la Orden de Trabajo ' . $orden_id . '.</p>	
			</div>
		</td>
		<td></td>
	</tr>
</table>'."\n";

$contenido .= '<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h4>Datos del Cliente:</h4>
				<table bgcolor="#AECCF2">
					<tr>
						<th align="center">Nombre:</th>
            		<td>' . $clie['cliente_nombre'] . ' ' . $clie['cliente_apellidos'] . '</td>
					</tr>
					<tr>
						<th align="center">Teléfono: </th>
            		<td>' . $clie['cliente_telefono1'] . '</td>
					</tr>
					<tr>
						<th align="center">Correo (E-mail): </th>
           			<td>' . $clie['cliente_email'] . '</td>
					</tr>
				</table>
				</br>
			</div>
		</td>
		<td></td>
	</tr>
</table>';

$contenido .= '<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h5>Atentamente:</h5>
				<p>' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '<br>
				' . $nombre_agencia . '<br>
				' . $agencia_telefonos . '<br>
				' . $agencia_email . '<br>
				</p>
				<p style="font-size:9px;font-weight:bold;">Este mensaje fue 
				enviado desde un sistema automático, si desea hacer algún 
				comentario respecto a esta notificación o cualquier otro asunto
				respecto al Centro de Reparación por favor responda a los
				correos electrónicos o teléfonos incluidos en el cuerpo de este
				mensaje. De antemano le agradecemos su atención y preferencia.</p>
			</div>
		</td>
		<td></td>
	</tr>
</table>'."\n";

?>