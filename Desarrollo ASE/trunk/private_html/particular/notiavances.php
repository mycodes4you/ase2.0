<?php
			$pregn = "SELECT c.cliente_nombre, c.cliente_apellidos, c.cliente_email, c.cliente_clave, o.orden_cliente_id, o.orden_presupuesto FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
			$matrn = mysql_query($pregn) or die("ERROR: Fallo selección! " . $pregn);
			$dato = mysql_fetch_array($matrn);
			$veh = datosVehiculo($orden_id, $dbpfx);
			if($dato['cliente_email'] != '') {
				$para = $dato['cliente_email'];
				$asunto = $lang['asunto'];
			} else {
				$para = $agencia_email;
				$asunto = $lang['Cliente sin email'];
			}
// ------ Agregar un posible destinatario adicional para esta notificación.
//			$concopia = 'monitoreo@controldeservicio.com'; // Remplazar con el correo adicional

if( $notisupase == 1 ) {
	$pregs = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < 130 GROUP BY sub_aseguradora";
	$matrs = mysql_query($pregs) or die("ERROR: Fallo selección!" . $pregs);
	while ($sup = mysql_fetch_array($matrs)) {
		if( $sup['sub_aseguradora'] > 0 ) {
			$pregv = "SELECT aseguradora_v_email FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $sup['sub_aseguradora'] . "'";
			$matrv = mysql_query($pregv) or die("ERROR: Fallo selección!" . $pregv);
			$vmail = mysql_fetch_array($matrv);
			if($bcc != '') {
				$bcc .= ', ' . $vmail['aseguradora_v_email'];
			} else {
				$bcc = $vmail['aseguradora_v_email'];
			}
		}
	}
}

$contenido = '<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
			</br>
						<h3>Estimad@ ' . $dato['cliente_nombre'] . ' ' . $dato['cliente_apellidos'] . '</h3>
						<p class="lead">Le informamos que hemos agregado una nueva fotografía de avance de reparación para su vehículo que atendemos con la Orden de Trabajo: ' . $orden_id . '.</p>	
			<table bgcolor="#AECCF2">
				<tr>
						<th align="center">Marca:</th>
            			<td>' . $veh['marca'] . ' </td>
				</tr>
				<tr>
						<th align="center">Tipo: </th>
            			<td>' . $veh['tipo'] . '</td>
				</tr>
				<tr>
						<th align="center">Color: </th>
            			<td>' . $veh['color'] . ' </td>
				</tr>
				<tr>
						<th align="center">Modelo: </th>
            			<td>' . $veh['modelo'] . '  </td>
				</tr>
				<tr>
						<th align="center">Placas: </th>
            			<td>' . $veh['placas'] . '</td>
				</tr>
			</table>	
						<br>
			         <h3 align="center">CONOZCA EL AVANCE DE SU REPARACIÓN</h3>
                  <p class="lead">Puede consultar el avance de la reparación de su vehículo utilizando las siguientes opciones:<br>
                  <br>1. Por medio del siguiente <a href="' . $urlpub . '/consulta.php?accion=consultar&orden_id=' . $orden_id . '&arg0=' . $dato['cliente_clave'] . '">enlace.</a>
                  <br>2. Escaneando con su celular el código QR en la Hoja de Ingreso que le entregó el Centro de Reparación.
                  <br>3. Ó mediante estos pasos:</p>
						<br>
						<p class="lead">a. Entre a: ' . $urlpub . '
						<br>b. Escriba la orden de trabajo o las placas de su vehículo o número de siniestro.
						<br>c. Digite su clave de cliente: ' . $dato['cliente_clave'] . ' Por favor escríbala tal como aparece, minúsculas, mayúsculas y números.</p>
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
</table>'."\n";

	include('parciales/notifica2.php');
?>
