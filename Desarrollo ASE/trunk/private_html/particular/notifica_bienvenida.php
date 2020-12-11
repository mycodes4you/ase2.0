<?php
include ("idiomas/es_MX/notifica_bienvenida.php");

$pregunta = "SELECT c.cliente_nombre, c.cliente_apellidos, c.cliente_email, c.cliente_clave, o.orden_cliente_id, o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas, o.orden_asesor_id, o.orden_presupuesto FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!" . $pregunta);
$dato = mysql_fetch_array($matriz);
if($dato['cliente_email'] != '' && $dato['cliente_email'] != $agencia_email) {
	$para = $dato['cliente_email'];
	$asunto = 'Bienvenido a ' . $nombre_agencia; 
} else {
	$para = $agencia_email;
	$asunto = $lang['asunto'];
//	$bcc = '';
}

include ("logo-base64.php");


$cons0 = "SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '" . $dato['orden_asesor_id'] . "'";
$arre0 = mysql_query($cons0) or die("ERROR: Fallo selección de usuarios!");
$usu0 = mysql_fetch_array($arre0);

$url_directa = $urlpub . '/consulta.php?accion=consultar&orden_id=' . $orden_id . '&arg0=' . $dato['cliente_clave'];

$contenido = '<!-- BODY -->
<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">

			<div class="content">
			</br>
						<h3>Estimado(a): ' . $dato['cliente_nombre'] . ' ' . $dato['cliente_apellidos'] . '</h3>

						<p class="lead">Le damos la más cordial bienvenida a nuestro Centro de Reparación Automotriz ' . $nombre_agencia . '. Una empresa líder en el ramo Automotriz con más de 8 años de experiencia. Agradecemos su confianza en nosotros para la reparación de su vehículo.</p>
			<h5>Su vehículo:</h5>
			<table bgcolor="#AECCF2">
				<tr>
						<th align="center">Marca:</th>
            			<td>' . $dato['orden_vehiculo_marca'] . '</td>
				</tr>
				<tr>
						<th align="center">Tipo:</th>
            			<td>' . $dato['orden_vehiculo_tipo'] . '</td>
				</tr>
				<tr>
						<th align="center">Color:</th>
            			<td>' . $dato['orden_vehiculo_color'] . ' </td>
				</tr>
				<tr>
						<th align="center">Placas: </th>
            			<td>' . $dato['orden_vehiculo_placas'] . '</td>
				</tr>
				<tr>
						<th align="center">Clave de usuario: </th>
            			<td>' . $dato['cliente_clave'] . '</td>
				</tr>
				<tr>
						<th align="center">Orden de trabajo: </th>
            			<td>' . $orden_id . '</td>
				</tr>

			</table>
						<br>
			         <h3 align="center">CONOZCA EL AVANCE DE SU REPARACIÓN</h3>

                  <p class="lead">Puede consultar el avance de la reparación de su vehículo utilizando las siguientes opciones: <br><br>1. Por medio del siguiente <a href="' . $url_directa . '">enlace.</a><br>2. Escaneando con su celular el código QR de su hoja de ingreso.<br>3. Ó mediante estos pasos:</p>
						<br>
						<p class="lead">1. Identifique su clave de cliente (6 dígitos) dentro de su hoja de ingreso o dentro de este correo.<br>2. Entre a: ' . $urlpub . '<br>3. Escriba la orden de trabajo o las placas de su vehículo o número de siniestro.<br>4. Digite la clave de cliente que ya ha identificado anteriormente.</p>
			<h5>Atentamente:</h5>
				<p>' . $usu0['nombre'] . ' ' . $usu0['apellidos'] . '<br>
        		' . $nombre_agencia . '<br>
        		Teléfonos:' . $agencia_telefonos . '<br>
        		E-mail: ' . $agencia_email. '<br>
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
</table><!-- /BODY -->'."\n";

include('parciales/notifica2.php');
