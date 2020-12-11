<?php

$pregunta = "SELECT c.cliente_nombre, c.cliente_apellidos, c.cliente_email, c.cliente_telegram_id, o.orden_cliente_id, o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas, o.orden_asesor_id, o.orden_presupuesto FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!" . $pregunta);
$dato = mysql_fetch_array($matriz);
if($dato['cliente_email'] != '') {
	$para = $dato['cliente_email'];
	$asunto = 'Actualización de estatus de su Automóvil.';
} else {
	$para = $agencia_email;
	$asunto = 'Cliente sin email capturado.' ;
}

$texto1 = 'Le informamos que la Aseguradora ' . constant('ASEGURADORA_NIC_'.$aseguradora) . ' ha autorizado la reparación de su vehículo con relación al siniestro: ' . $reporte . ' que ';
$texto1 .= 'atendemos con la Orden de Trabajo ' . $orden_id . '.';

if($dedu > 0) {
	$texto2 = ' Por otra parte, le informamos que su aseguradora determinó que tiene un deducible por pagar para el siniestro: '. $reporte . ', ';
	$texto2 .= 'el monto y la cuenta en donde tendrá que depositarlo le será informado por su asesor de servicio. Le recordamos que nosotros por ningún ';
	$texto2 .= 'motivo aceptamos efectivo, por lo que le solicitamos presente el voucher original del pago al momento de recoger el vehículo. Gracias.';
}

$motivo = $asunto . ' ' . $texto1 . ' ' . $texto2;

if($dato['cliente_telegram_id'] != '' ) {
        $notitelegram = notificaTelegram($dato['cliente_telegram_id'], $motivo);
//      echo $notitelegram;

}


$cons0 = "SELECT nombre, apellidos, email FROM " . $dbpfx . "usuarios WHERE usuario = '" . $dato['orden_asesor_id'] . "'";
$arre0 = mysql_query($cons0) or die("ERROR: Fallo selección de usuarios!");
$usu0 = mysql_fetch_array($arre0);

if($usu0['email'] != '') { $concopia = $usu0['email']; }

$contenido = '		<table class="body-wrap">
			<tr>
				<td></td>
				<td class="container" bgcolor="#F2F2F2">
					<div class="content">
						</br>
						<h3>Estimado(a): ' . $dato['cliente_nombre'] . ' ' . $dato['cliente_apellidos'] . '</h3>
						<p class="lead">Le informamos que la Aseguradora ' . constant('ASEGURADORA_NIC_'.$aseguradora) . ' ha autorizado la reparación de su vehículo con relación al siniestro: ' . $reporte . ' que atendemos con la Orden de Trabajo ' . $orden_id . '.</p>	
						<h5>Su vehiculo:</h5>		
						<table bgcolor="#AECCF2">
							<tr><th align="center">Marca:</th><td>' . $dato['orden_vehiculo_marca'] . '</td></tr>
							<tr><th align="center">Tipo:</th><td> ' . $dato['orden_vehiculo_tipo'] . '</td></tr>
							<tr><th align="center">Color:</th><td> ' . $dato['orden_vehiculo_color'] . '</td></tr>
							<tr><th align="center">Placas: </th><td>' . $dato['orden_vehiculo_placas'] . '</td></tr>
						</table>
					</div>							
				</td>
				<td></td>
			</tr>
		</table>'."\n";

if($dedu > 0) { 
	$contenido .= '		<table class="body-wrap">
			<tr>
				<td></td>
				<td class="container" bgcolor="#F2F2F2">
					<div class="content">
						<p class="lead">Le informamos que su aseguradora determinó que tiene un deducible por pagar para el siniestro: '. $reporte . ', el monto y la cuenta en donde tendrá que depositarlo le será informado por su asesor de servicio. Le recordamos que nosotros por ningún motivo aceptamos efectivo, por lo que le solicitamos presente el voucher original del pago al momento de recoger el vehículo. Gracias.</p>
					</div>
				</td>
				<td></td>
			</tr>'."\n";
	$contenido .= '		</table>'."\n";
}

$contenido .= '		<br>
		<table class="body-wrap">
			<tr>
				<td></td>
				<td class="container" bgcolor="#F2F2F2">
					<div class="content">
						<h5>Atentamente:</h5>
						<p>' . $usu0['nombre'] . ' ' . $usu0['apellidos'] . '<br>
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

	include('parciales/notifica2.php');

?>
