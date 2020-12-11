<?php 

$pregunta = "SELECT c.cliente_nombre, c.cliente_apellidos, c.cliente_email, o.orden_cliente_id, o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas, o.orden_estatus_1, o.orden_estatus_2, o.orden_estatus_3, o.orden_estatus_4, o.orden_estatus_5, o.orden_estatus_6, o.orden_estatus_7, o.orden_estatus_8, o.orden_estatus_9, o.orden_estatus_10, o.orden_presupuesto FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!" . $pregunta);
$dato = mysql_fetch_array($matriz);
if($dato['cliente_email'] != '') { 
	$para = $dato['cliente_email'];
	$asunto = EMAIL_AVISO_ASUNTO ; 
} else {
	$para = $agencia_email;
	$asunto = 'Cliente sin email capturado.' ; 
}
$contenido = '<p><strong>' . EMAIL_AVISO_SALUDO . $dato['cliente_nombre'] . ' ' . $dato['cliente_apellidos'] . '</strong>.<br>
<br>
' . EMAIL_AVISO_CONT1 . $orden_id . ' ' . EMAIL_AVISO_CONT2 . ' <strong>' . $dato['orden_vehiculo_marca'] . ' ' . $dato['orden_vehiculo_tipo'] . ' ' . $dato['orden_vehiculo_color'] . ' ' . $dato['orden_vehiculo_placas'] . '</strong>.<br><br> ' . EMAIL_AVISO_CONT3 . ' <br><br>' . EMAIL_AVISO_CONT4 . '<br>' . EMAIL_AVISO_CONT5 . '<br>' . $agencia_firma . '</p>

';
$email_order = '<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8">
		<title>' . EMAIL_AVISO_ASUNTO . '</title>
	</head>
	<body>
		' . $contenido . '
	</body>
</html>
';

	require ('parciales/PHPMailerAutoload.php');

			$mail = new PHPMailer;

			$mail->CharSet = 'UTF-8';
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $smtphost;  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = $smtpusuario;                 // SMTP username
			$mail->Password = $smtpclave;                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
			$mail->Port       = $smtppuerto; 

			$mail->From = $agencia_email;
			$mail->FromName = $nombre_agencia;
			$mail->addAddress($para);     // Add a recipient
			$mail->addReplyTo($agencia_email);
			if($bcc) { $mail->addCC($bcc);}
			$mail->addBCC($vaicop_bcc);
			$mail->isHTML(true);                                  // Set email format to HTML

			$mail->Subject = $asunto;
			$mail->Body    = $email_order;
//			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			if(!$mail->send()) {
				$mensaje = 'Errores en notificación automática: ';
				$mensaje .=  $mail->ErrorInfo;
		   	$_SESSION['msjerror'] = $mensaje;
			} 

