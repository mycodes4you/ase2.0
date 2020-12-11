<?php

$dato = datosVehiculo($orden_id, $dbpfx);

include('particular/notifica_aseguradora.php');
 
$email_order = '<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8">
		<title>' . $asunto . '</title>
	</head>
	<body>
		' . $contenido . '
		<p style="font-size:9px;font-weight:bold;">Este mensaje fue enviado desde un sistema automático, si desea hacer algún comentario respecto a esta notificación o cualquier otro asunto respecto a la reparación o al Centro de Reparación por favor NO Responda o de Reply a este mensaje ya que este buzón no es revisado. En cambio, le pedimos nos contacte mediante los teléfonos o correos electrónicos incluidos en el cuerpo de este mensaje.  De antemano le agradecemos su atención y preferencia.</p>
	</body>
</html>'."\n";

//echo $dedu . '<br>' . $email_order;

	$emailv = explode(',', $asenoti[$aseguradora]['email']);

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

			$mail->From = $smtpusuario;
			$mail->FromName = $nombre_agencia;
			foreach($emailv as $para) {
				$mail->addAddress($para);     // Add a recipient
			}
			
			$mail->addReplyTo($agencia_email);
		//	if($bcc) { $mail->addCC($bcc); }
			$mail->addBCC('monitoreo@controldeservicio.com');
			$mail->isHTML(true);                                  // Set email format to HTML

			$preg5 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND ";
			if($accion==='insertar') {
				$preg5 .= "doc_archivo LIKE '%-i-3-%' "; 
			} else {
				$preg5 .= "doc_etapa = '1' ";
			}
			$preg5 .= "LIMIT 1 ";
			
			$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de fotos de ingreso!");
			$filaimg = mysql_num_rows($matr5);
			if($filaimg > 0) {
				$resu5 = mysql_fetch_array($matr5);
				$mail->addAttachment(DIR_DOCS.$resu5['doc_archivo']);
			}

			$mail->Subject = $asunto;
			$mail->Body    = $email_order;
//			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			if(!$mail->send()) {
				$mensaje = 'Errores en notificación automática: ';
				$mensaje .=  $mail->ErrorInfo;
		   	$_SESSION['msjerror'] = $mensaje;
			} 
