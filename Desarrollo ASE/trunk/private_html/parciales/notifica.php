<?php 

$pregunta = "SELECT c.cliente_nombre, c.cliente_apellidos, c.cliente_email, c.cliente_telegram_id, o.orden_cliente_id, o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas, o.orden_presupuesto FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!" . $pregunta);
$dato = mysql_fetch_array($matriz);
if($dato['cliente_email'] != '') { 
	$para = $dato['cliente_email'];
	$asunto = $lang['asunto'] ; 
} else {
	$para = $agencia_email;
	$asunto = $lang['Cliente sin email']; 
}

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

if($dato['cliente_telegram_id'] != '' ) {
	$mensajetlgm = $lang['asunto'] . ': ' . $lang['saludo'] . $dato['cliente_nombre'] . ' ' . $dato['cliente_apellidos'] . '. ' . $lang['aviso1'] . ' ' . $dato['orden_vehiculo_marca'] . ' ' . $dato['orden_vehiculo_tipo'] . ' ' . $dato['orden_vehiculo_color'] . ' ' . $lang['placas'] . '. ' . $lang['aviso2'];
	$notitelegram = notificaTelegram($dato['cliente_telegram_id'], $mensajetlgm . ' ' . $motivo);
//	echo $notitelegram;
} else {

$contenido = '<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				</br>
				<h3>' . $lang['saludo'] . $dato['cliente_nombre'] . ' ' . $dato['cliente_apellidos'] . '</h3>
				<p class="lead">' . $lang['aviso1'] . '</p>	
				<table bgcolor="#AECCF2">
					<tr>
						<th align="center">' . $lang['orden'] . '</th>
            			<td>' . $orden_id . '</td>
					</tr>
					<tr>
						<th align="center">' . $lang['vehiculo'] . '</th>
            			<td>' . $dato['orden_vehiculo_marca'] . ' ' . $dato['orden_vehiculo_tipo'] . ' ' . $dato['orden_vehiculo_color'] . '</td>
					</tr>
					<tr>
						<th align="center">' . $lang['placas'] . '</th>
            			<td>' . $dato['orden_vehiculo_placas'] . '</td>
					</tr>
				</table>	
				<p class="lead"><strong>' . $lang['aviso2'] . ' ' . $motivo . '</strong></p>
				<p>' . $lang['despedida'] . '</p>
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
        		' . $nombre_agencia . '<br>'."\n";
			if($_SESSION['email'] != '') {
				$contenido .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' . $_SESSION['email'] . '">' . $_SESSION['email'] . '</a><br>'."\n";
				$concopia = $_SESSION['email'];
			} else {
				$contenido .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' . $agencia_email . '">' . $agencia_email . '</a><br>'."\n";
			}
			$contenido .= '        		' . $agencia_telefonos . '<br>
        		</p>
        	<p style="font-size:9px;font-weight:bold;">Este mensaje fue
        		enviado desde un sistema automático, si desea hacer algúnn
        		comentario respecto a esta notificación o cualquier otro asunto
        		respecto al Centro de Reparación por favor responda a los
        		correos electrónicos o teléfonos incluidos en el cuerpo de este
        		mensaje. De antemano le agradecemos su atención y preferencia.</p>
			</div>
									
		</td>
		<td></td>
	</tr>
</table>'."\n";

include ('parciales/notifica2.php');
}
?>