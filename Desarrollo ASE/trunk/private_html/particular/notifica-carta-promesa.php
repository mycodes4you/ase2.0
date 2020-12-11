<?php
			$pregn = "SELECT c.cliente_nombre, c.cliente_apellidos, c.cliente_email, c.cliente_clave, o.orden_cliente_id, o.orden_presupuesto FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
			$matrn = mysql_query($pregn) or die("ERROR: Fallo selección! " . $pregn);
			$dato = mysql_fetch_array($matrn);
			if($dato['cliente_email'] != '') {
				$para = $dato['cliente_email'];
				$asunto = 'Carta fecha promesa de entrega';
			} else {
				$para = $agencia_email;
				$asunto = 'Cliente sin email';
			}

			$preg_carta = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND doc_archivo LIKE '" . $orden_id . "-carta-fecha-promesa-axa-%' ORDER BY doc_id DESC LIMIT 1";
			$matr_carta =mysql_query($preg_carta) or die("ERROR: Fallo selección! " . $preg_carta);
			$consulta_carta = mysql_fetch_array($matr_carta);

			$fotos[] = DIR_DOCS . $consulta_carta['doc_archivo'];
			
			

$contenido = '<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
			</br>
						<h3>Estimad@ ' . $dato['cliente_nombre'] . ' ' . $dato['cliente_apellidos'] . '</h3>
						<p class="lead">Le hacemos envío de la carta promesa de entrega de sua aseguradora AXA en relación a la reparación para su vehículo que atendemos con la Orden de Trabajo: ' . $orden_id . '.</p>	
				
						<br>
			         
			<h5>Atentamente:</h5>
				<p>' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '<br>
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