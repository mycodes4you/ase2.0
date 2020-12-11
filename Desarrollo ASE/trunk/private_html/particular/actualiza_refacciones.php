<?php

$ahora = time();
$domingo = date('w'); 
if($domingo > 0) {
	include('config.php');
	mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
	mysql_select_db($dbnombre) or die('Falló la seleccion la DB');
	include('comun.php');
/*  ----------------  obtener nombres de aseguradoras   ------------------- */
	
		$consulta = "SELECT aseguradora_id, aseguradora_nic FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_nic";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
   	$ase = array();
		while ($aa = mysql_fetch_array($arreglo)) {
			$ase[$aa['aseguradora_id']] = $aa['aseguradora_nic'];
		}
		$ase[0] = 'Particular';
//		print_r($ase);
/*  ----------------  nombres de aseguradoras   ------------------- */

	$pregunta = "SELECT  prov_id, prov_razon_social, prov_representante, prov_email FROM " . $dbpfx . "proveedores WHERE prov_activo = '1' AND prov_env_ped = '1'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion de proveedores!");
//$venc = mysql_fetch_array($matriz);
// echo $provs . '<br>';
//print_r($venc);
	while($prov = mysql_fetch_array($matriz)) {
		$filas = 0;
		$envprov = 0;
		$preg2 = "SELECT pedido_id, fecha_promesa FROM " . $dbpfx . "pedidos WHERE prov_id = '" . $prov['prov_id'] . "' AND pedido_estatus < '10'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de pedidos!");
		$contenido = '<!-- BODY -->
<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h3>' . EMAIL_PARTES_ASUNTO . ' para ' . $nombre_agencia . '</h3>
				<p class="lead">' . EMAIL_PARTES_SALUDO . ' ' . $prov['prov_representante'] . '<br><br>'."\n";
		$contenido .= '				<p>' . EMAIL_PARTES_CONT1 . '</p>'."\n";
		$contenido .= '
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
				<table border=1 cellspacing=0 bgcolor="#AECCF2" width="100%">'."\n";
		$contenido .= "					<tr><td>OT</td><td>Pedido</td><td>Siniestro</td><td>Nombre y referencia</td><td align=center>Cantidad pendiente</td><td align=center>Fecha de vencimiento</td></tr>\n";
		$envprov = 0;
		while($ped = mysql_fetch_array($matr2)) {
			$preg3 = "SELECT op_id, sub_orden_id, op_nombre, op_cantidad, op_recibidos, op_fecha_promesa FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $ped['pedido_id'] . "' AND op_ok = 0 AND op_tangible = '1'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de refacciones!");
			while($op = mysql_fetch_array($matr3)) {
				$pendiente = $op['op_cantidad'] - $op['op_recibidos'];
				if(strtotime($op['op_fecha_promesa']) < $ahora && $pendiente > 0) {
					$envprov = 1;
					$pregunta3 = "SELECT orden_id, sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $op['sub_orden_id'] . "'";
					$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
					while($aseg = mysql_fetch_array($matriz3)) {
						$filas++;
						if($aseg['sub_reporte'] == '0') { $aseg['sub_reporte'] = 'Particular'; }
						$contenido .= "					<tr><td>" . $aseg['orden_id'] . "</td><td>" . $ped['pedido_id'] . "</td><td>" . $aseg['sub_reporte'] . "</td><td>" . $op['op_nombre'] . "</td><td align=center>" . $pendiente . "</td><td>" . date('Y-m-d', strtotime($op['op_fecha_promesa'])) . "</td></tr>\n";
					}
				}
			}
		}

		if($envprov == 1) {
			$asunto = constant('EMAIL_PARTES_ASUNTO');
			$para = $prov['prov_email'];
			$respondera = (constant('EMAIL_PROVEEDOR_RESPONDER'));
			$concopia = (constant('EMAIL_PROVEEDOR_CC'));
			$contenido .= '				</table>
			</div>
		</td>
		<td></td>
	</tr>'."\n";
			$contenido .= '</table>
<table class="body-wrap" >
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h5>Atentamente:</h5>'."\n";
			$contenido .= '				<p>' . JEFE_DE_ALMACEN . '<br>'."\n";
			$contenido .= '				' .$nombre_agencia. '<br>
				' .$agencia_direccion. '<br>
				Col. ' .$agencia_colonia. ' ' .$agencia_municipio. '<br>
				C.P.: ' .$agencia_cp. ' . ' .$agencia_estado. '<br>'."\n";
			$contenido .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' .EMAIL_DE_ALMACEN. '">' .EMAIL_DE_ALMACEN. '</a><br>'."\n";
			$contenido .= '				Tels: ' .$agencia_telefonos. '<br>
				' . TELEFONOS_ALMACEN . '</p>
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
</table>
<!-- /BODY -->'."\n";
//			echo $contenido;


if(file_exists('logo-base64.php')) {
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

			require_once ('../parciales/PHPMailerAutoload.php');

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
			
/*			if($bcc) {
				$ma = explode(',', $bcc);
				foreach($ma as $k) {
					$mail->addBCC($k);     // Add a recipient
				}
			}
*/
			if($_SESSION['email'] != '') { $mail->addBCC($_SESSION['email']); }

			$mail->addBCC('monitoreo@controldeservicio.com');
			$mail->isHTML(true);                                  // Set email format to HTML

			$mail->Subject = $asunto;

			$mail->Body    = $email_order;
//			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

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
		}
	}
}

//echo $mensaje;

?>