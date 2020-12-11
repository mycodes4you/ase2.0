<?php 

if(file_exists('particular/logo-base64.php')) {
	include ('particular/logo-base64.php');
} elseif(file_exists('logo-base64.php')) {
	include ('logo-base64.php');
}

$email_order = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>' . $asunto . '</title>
	<style type="text/css">
* { margin:0; padding:0; }
* { font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; }
img { max-width: 100%; }
.collapse { margin:0; padding:0; }
body { -webkit-font-smoothing:antialiased; -webkit-text-size-adjust:none; width: 100%!important; height: 100%; }
a { color: #2BA6CB;}
table.head-wrap { width: 100%;}
.header.container table td.logo { padding: 15px; }
.header.container table td.label { padding: 15px; padding-left:0px;}
table.body-wrap { width: 100%;}
table.footer-wrap { width: 100%;	clear:both!important;}
.footer-wrap .container td.content p { border-top: 1px solid rgb(215,215,215); padding-top:15px;}
.footer-wrap .container td.content p { font-size:10px; font-weight: bold; }
h1,h2,h3,h4,h5,h6 { font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; line-height: 1.1; margin-bottom:15px; color:#000; }
h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }
h1 { font-weight:200; font-size: 44px;}
h2 { font-weight:200; font-size: 37px;}
h3 { font-weight:500; font-size: 27px;}
h4 { font-weight:500; font-size: 23px;}
h5 { font-weight:900; font-size: 17px;}
h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}
.collapse { margin:0!important; color: #ffffff;}
p, ul { 
	margin-bottom: 10px; 
	font-weight: normal; 
	font-size:14px; 
	line-height:1.6;
	text-align: justify;
}
p.lead { font-size:17px; }
p.last { margin-bottom:0px;}
ul li {
	margin-left:5px;
	list-style-position: inside;
}
/* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
.container {
	display:block!important;
	max-width:600px!important;
	margin:0 auto!important; /* makes it centered */
	clear:both!important;
}

.contenedor80 {
	display:block!important;
	max-width:80%!important;
	margin:0 auto!important; /* makes it centered */
	clear:both!important;
}

/* This should also be a block element, so that it will fill 100% of the .container */
.content {
	padding:15px;
	max-width:600px;
	margin:0 auto;
	display:block; 
}
.content table { width: 100%; }
/* Odds and ends */
.column {
	width: 300px;
	float:left;
}
.column tr td { padding: 15px; }
.column-wrap { 
	padding:0!important; 
	margin:0 auto; 
	max-width:600px!important;
}
.column table { width:100%;}
/* Be sure to place a .clear element after each set of columns, just to be safe */
.clear { display: block; clear: both; }
/* ------------------------------------------- 
		PHONE
		For clients that support media queries.
		Nothing fancy. 
-------------------------------------------- */
@media only screen and (max-width: 600px) {
	a[class="btn"] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}
	div[class="column"] { width: auto!important; float:none!important;}
}
	</style>
	</head>
	<body bgcolor="#FFFFFF">
	<!-- HEADER -->
	<table class="head-wrap" bgcolor="#395259">
		<tr>
			<td></td>
				<td class="header container">
					<div class="content">
						<table bgcolor="#395259">
							<tr>
								<td><img src="' . $logobase64 . '"/></td>
								<td align="right"><h4 class="collapse">' . $agencia .'</h4></td>
							</tr>
						</table>
					</div>
				</td>
			<td></td>
		</tr>
	</table>
	<!-- /HEADER -->
		' . $contenido . '
	<!-- FOOTER -->
<table class="footer-wrap">
	<tr>
		<td></td>
		<td class="container">
				<!-- content -->
				<div class="content">
				<table>
				<tr>
					<td align="center">
						<p>
							<a>Producido por:</a> |
							<a>AutoShop-Easy.com</a>
						</p>
					</td>
				</tr>
			</table>
				</div><!-- /content -->
		</td>
		<td></td>
	</tr>
</table>
<!-- /FOOTER -->
	</body>
</html>';


	require ('parciales/PHPMailerAutoload.php');

			$mail = new PHPMailer;

			$mail->CharSet = 'UTF-8';
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $smtphost;  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = $smtpusuario;                 // SMTP username
			$mail->Password = $smtpclave;                           // SMTP password
//			$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
			$mail->Port       = $smtppuerto; 

			$mail->From = $smtpusuario;
			$mail->FromName = $nombre_agencia;

			$pa = explode(',', $para);
			foreach($pa as $k) {
				$mail->addAddress($k);     // Add a recipient
			}

			if($respondera != '') {
				$ma = explode(',', $respondera);
				foreach($ma as $k) {
					$mail->addReplyTo($k);
				}
			} else {
				$mail->addReplyTo($agencia_email);
			}
			
			if($concopia != '') {
				$ma = explode(',', $concopia);
				foreach($ma as $k) {
					$mail->addCC($k);
				}
			} else {
				$mail->addCC($agencia_email);
			}
			
			if($vaicop_bcc) { $mail->addBCC($vaicop_bcc); }
			if($bcc) {
				$ma = explode(',', $bcc);
				foreach($ma as $k) {
					$mail->addBCC($k);     // Add a recipient
				}
			}

			if($_SESSION['email'] != '') { $mail->addCC($_SESSION['email']); }

			$mail->addBCC('monitoreo@controldeservicio.com');
			$mail->isHTML(true);                                  // Set email format to HTML

			$mail->Subject = $asunto;

			foreach($fotos as $pic) {
				$mail->addAttachment($pic);
			}

			$mail->Body    = $email_order;
//			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			if($mailcodificacion != '') {
				$mail->Encoding = $mailcodificacion;
			}

			if(!$mail->send()) {
				$mensaje = 'Errores en notificación automática: ';
				$mensaje .=  $mail->ErrorInfo;
				$msjerror = 1;
			} else {
				$mensaje = 'Se envió el correo a ' . $para;
				$msjerror = 0;
			}
			$_SESSION['msjerror'] = $mensaje;
			unset($email_aviso);
			unset($fotos);
?>