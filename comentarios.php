<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/comentarios.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

if ($accion==='registrar' || $accion==='mostrar' || $accion==='visto') {
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}

if ($accion==='agregar') {
	$funnum = 1015000;
	$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  	$orden = mysql_fetch_array($matriz);
	echo '		 <form action="comentarios.php?accion=registrar" method="post" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" border="0" class="agrega">
				<tr><td colspan="2"><span class="alerta">' . $_SESSION['coment']['mensaje'] . '</span></td></tr>
				<tr class="cabeza_tabla"><td colspan="2">Agregar comentarios a la OT para uso interno y para cliente final.</td></tr>
				<tr><td colspan="2" style="text-align:left;">' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['orden_vehiculo_color'] . ' Placas: ' . $orden['orden_vehiculo_placas'] . '</td></tr>
				<tr><td colspan="2"><hr></td></tr>
				<tr><td style="text-align:center;" valign="top">Indicar si el comentario es para uso:'."\n";
	echo '				<br>' . $lang['com_tipo_0'] . '<input type="radio" name="visicom" value="0"';
	if($_SESSION['coment']['visicom'] == '0') { echo ' checked="checked"';}
	echo ' />'."\n";
	foreach($usuauthcom as $k) {
		if($_SESSION['usuario'] == $k) {
// ---------------------- Comentarios a clientes --------------------------
//			$funnum = 1015005;
			echo '					<br>' . $lang['com_tipo_1'] . '<input type="radio" name="visicom" value="1"';
			if($_SESSION['coment']['visicom'] == '1') { echo ' checked="checked"';}
			echo ' />'."\n";
		}
	}
	if($_SESSION['codigo'] == '30' || $_SESSION['rol06'] == '1') {
		echo '					<br>' . $lang['com_tipo_2'] . '<input type="radio" name="visicom" value="2"';
		if($_SESSION['coment']['visicom'] == '2') { echo ' checked="checked"';}
		echo ' />'."\n";
	}
	if($mensjint == '1') {
		echo '					<br>' . $lang['com_tipo_3'] . '<input type="radio" name="visicom" value="3"';
		if($_SESSION['coment']['visicom'] == '3') { echo ' checked="checked"';}
		echo ' />'."\n";
		echo '					<br><select name="msjusr[]" multiple="multiple">'."\n";
		echo '						<option value="701" style="background-color: #FFFF93;">Soporte Técnico AutoShop Easy</option>'."\n";

		$preg1 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE activo = '1' AND acceso = '0' ORDER BY nombre";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Usuarios !" . $preg1);
		while($usu = mysql_fetch_array($matr1)) {
			echo '						<option value="' . $usu['usuario'] . '">' . $usu['nombre'] . ' ' . $usu['apellidos'] . '</option>'."\n";
		}
		echo '					</select>'."\n";
	}
	echo '</td><td><textarea name="motivo" cols="40" rows="6">' . $_SESSION['coment']['motivo'] . '</textarea></td></tr>
				<tr><td colspan="2" style="text-align:left;"><input type="submit" name="confirmar" value="Enviar" />&nbsp;<input type="submit" name="regresar" value="Regresar" /></td></tr>
			</table>
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />
			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="estatus" value="' . $orden['orden_estatus'] . '" />
		</form>';
}

elseif ($accion==='registrar') {
	$funnum = 1015010;
	$error = 'no';
	$mensaje= '';
	unset($_SESSION['coment']);
	$_SESSION['coment'] = array();
	$motivo = str_replace("'","", $motivo);
	$_SESSION['coment']['motivo'] = $motivo;
	$_SESSION['coment']['visicom'] = $visicom;
	if($visicom == 3 && count($msjusr) < 1) { $error = 'si'; $mensaje .= 'Por favor selecciona al destinatario.<br>'; }
	if($visicom == '' || !isset($visicom)) { $error = 'si'; $mensaje .= 'Por favor selecciona el tipo de comentario.'; }

// ------ Determina la etapa en la que se encuentra la OT ** Usar los mismos criterios que en recordatorio.php
	if($estatus == 17 || $estatus <= 3 || ($estatus >= 24 && $estatus <= 29) || $estatus == 20) {
		$etapa_com = 10;
	} elseif($estatus == 4 || ($estatus >= 30 && $estatus <= 35)) {
		$etapa_com = 20;
	} elseif($estatus >= 7 && $estatus <= 11) {
		$etapa_com = 30;
	} elseif($estatus == 5 || $estatus == 6 || ($estatus >= 12 && $estatus <= 16) || $estatus == 21 || ($estatus > 90 && $estatus <= 99)) {
		$etapa_com = 40;
	}
// ------

	if ($confirmar=="Enviar" && $error == 'no') {
		$motivo = preg_replace('/\r\n/', '<br>', $motivo);
		if ($visicom == 1) {
/*			define('EMAIL_AVISO_ASUNTO', 'Informe sobre Su Automóvil en ' . $agencia);
			define('EMAIL_AVISO_SALUDO', 'Estimad@ ');
			define('EMAIL_AVISO_CONT1', 'Se ha agregado el siguiente comentario a la Orden de Trabajo ');
			define('EMAIL_AVISO_CONT2', 'en nuestro Sistema de Administración y Seguimiento, relativo a su vehículo');
			define('EMAIL_AVISO_CONT3', $motivo);
			define('EMAIL_AVISO_CONT4', 'Reciba un cordial saludo.');
			define('EMAIL_AVISO_CONT5', $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '<br>' . $_SESSION['puesto'] . '<br>');
*/			include('parciales/notifica.php');
			bitacora($orden_id, 'Comentario ' . $lang['com_tipo_' . $visicom], $dbpfx, $motivo, $visicom, $sub_orden_id, '', '', $etapa_com);
//			echo $mensaje;
		} elseif($visicom == 3) {
			$destino = '';
//			print_r($msjusr);
			$dd = 0;
			foreach($msjusr as $k => $v) {
				$preg1 = "SELECT nombre, apellidos, email FROM " . $dbpfx . "usuarios WHERE usuario = '" . $v . "'";
				$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Usuarios !" . $preg1);
				$usu = mysql_fetch_array($matr1);
				if($usu['email'] != '') {
					$ma = explode(',', $usu['email']);
					foreach($ma as $kk) {
						if($dd > 0) { $destino .= ', ';}
						$destino .= $kk;
						$dd++;
					}
				}

            bitacora($orden_id, 'Comentario ' . $lang['com_tipo_' . $visicom], $dbpfx, 'Para: ' . $usu['nombre'] . ' ' . $usu['apellidos'] . ' ' . $motivo, $visicom, $sub_orden_id, '', $v);
			}
			if($destino != '') {
				require ('parciales/PHPMailerAutoload.php');

				$mail = new PHPMailer;

				$mail->CharSet = 'UTF-8';
				$mail->isSMTP();                                      // Set mailer to use SMTP
//				$mail->Host = $smtphost;  // Specify main and backup SMTP servers
				$mail->Host = 'mail.controldeservicio.com';  // Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
//				$mail->Username = $smtpusuario;                 // SMTP username
				$mail->Username = 'notifica@controldeservicio.com';                 // SMTP username
//				$mail->Password = $smtpclave;                           // SMTP password
				$mail->Password = 'De34x.t7';                           // SMTP password
//				$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
//				$mail->Port       = $smtppuerto;
				$mail->Port       = '587';

//				$mail->From = constant('EMAIL_PROVEEDOR_FROM');
				$mail->From = 'notifica@controldeservicio.com';
				$mail->FromName = 'Notificaciones de ' . $nombre_agencia;

				$ma = explode(',', $destino);
				foreach($ma as $k) {
					$mail->addAddress($k);     // Add a recipient
				}

			   if($_SESSION['email'] != '' ) {
			   	$mail->addReplyTo($_SESSION['email']);
		  		}
				$mail->addBCC('monitoreo@controldeservicio.com');
				$mail->isHTML(true);                                  // Set email format to HTML
				$mail->Subject = 'Tienes un mensaje en la OT ' . $orden_id;

				$email_order = '<!DOCTYPE HTML PUBLIC \'-//W3C//DTD HTML 4.0 Transitional//EN\'><html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" /></head><body style="font-family:Arial;">';
				$email_order .= '<p>Hola buen día.</p>';
				$email_order .= $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . ' te envió el siguiente comentario en la OT ' . $orden_id . ':';
				$email_order .= '<p>' . $motivo . '</p>';
				$email_order .= '<p style="font-size:9px;font-weight:bold;">Este mensaje fue enviado desde un sistema automático, si desea hacer algún comentario respecto a esta notificación o cualquier otro asunto respecto a la reparación o al Centro de Reparación por favor NO Responda o de Reply a este mensaje ya que este buzón no es revisado. En cambio, le pedimos nos contacte mediante los teléfonos o correos electrónicos incluidos en el cuerpo de este mensaje.  De antemano le agradecemos su atención y preferencia.</p>';
				$email_order .= '</body></html>';

				$mail->Body    = $email_order;
				$mail->AltBody = 'Hola, buen día.' . "\n\n" . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . ' te envió el siguiente comentario en la OT ' . $orden_id . ':' . "\n\n" . $motivo . "\n\n\n\n" . 'Este mensaje fue enviado desde un sistema automático, si desea hacer algún comentario respecto a esta notificación o cualquier otro asunto respecto a la reparación o al Centro de Reparación por favor NO Responda o de Reply a este mensaje ya que este buzón no es revisado. En cambio, le pedimos nos contacte mediante los teléfonos o correos electrónicos incluidos en el cuerpo de este mensaje.  De antemano le agradecemos su atención y preferencia.';
				if(!$mail->send()) {
					$mensaje = 'Errores en notificación automática: ';
					$mensaje .=  $mail->ErrorInfo;
	   				$_SESSION['msjerror'] = $mensaje;
				}

			}
		} else {
			if ($visicom == 2) {
				$preg_recordatorios = "SELECT bit_id FROM " . $dbpfx . "comentarios WHERE orden_id = '" . $orden_id . "' AND recordatorio = '1' AND para_usuario = '" . $_SESSION['usuario'] . "' ";
				$matr_recordatorios = mysql_query($preg_recordatorios) or die("ERROR: Fallo selección de recordatorios! " . $preg_recordatorios);
				$fila = mysql_num_rows($matr_recordatorios);
				if($fila > 0) {
					while($usu = mysql_fetch_array($matr_recordatorios)) {
						$sql_data_array = [
							'fecha_visto' => date('Y-m-d H:i:s', time()),
							'recordatorio' => 0,
						];
						$parametros = "bit_id = '" . $usu['bit_id'] . "'";
						ejecutar_db($dbpfx . 'comentarios', $sql_data_array, 'actualizar', $parametros);
					}
				}
			}
			bitacora($orden_id, 'Comentario ' . $lang['com_tipo_' . $visicom], $dbpfx, $motivo, $visicom, $sub_orden_id, '', '', $etapa_com);
		}
		unset($_SESSION['coment']);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('comentarios.php?accion=agregar&orden_id=' . $orden_id);
	}
}

elseif($accion==='mostrar') {
	$funnum = 1015015;
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	echo '		<div id="principal"> ';
//	echo $orden_id;

	$pregunta3 = "SELECT c.fecha_com, c.usuario, c.comentario, c.interno, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.orden_id = '$orden_id' AND c.usuario = u.usuario";
	if($tiposeg == '2') {
		$pregunta3 .= " AND interno = '2' ORDER BY c.fecha_com DESC";
		$titulo = ' de Seguimineto';
	}
	$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo selección! " . $pregunta3);
	echo '			<table cellspacing="0" cellpadding="2" border="1" width="100%">
				<tr><td style="border-width:1px; border-style:solid;"><span style="font-size:1.2em; font-weight:bold;">Comentarios' . $titulo . ' de la OT: ' . $orden_id . '</span><br>';
	$j=0; $fondo='claro';
	while($comen = mysql_fetch_array($matriz3)) {
		echo '<p class="' . $fondo . '" style="margin-top:0px; padding-left:3px; padding-right:3px;';
		if($comen['interno'] == '1') { echo ' color:#0000FF;'; }
		echo '">' . $comen['fecha_com'] . ' ' . $comen['nombre'] . ' ' . $comen['apellidos'] . ' -> ' . $comen['comentario'] . '</p>';
		if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
	}
	echo '</td></tr></table>';
}

elseif ($accion==='visto') {
	$funnum = 1015010;
	unset($_SESSION['coment']);
	$_SESSION['coment'] = array();
	$error = 'no';
	$mensaje= '';

	$activar = explode('|', $visto);
	$preg0 = "UPDATE " . $dbpfx . "comentarios SET fecha_visto = '" . date('Y-m-d H:i:s') . "' WHERE bit_id = '" . $activar[0] . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo actualización de comentario !" . $preg0);
	$archivo = '../logs/' . time() . '-base.ase';
	$myfile = file_put_contents($archivo, $preg0 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);

	$preg1 = "SELECT u.nombre, u.apellidos, u.email, c.comentario, c.orden_id FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.usuario = u.usuario AND c.bit_id = '" . $activar[0] . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Usuarios !" . $preg1);
	$usu = mysql_fetch_array($matr1);

	if($usu['orden_id'] == '9999995') {
		redirigir('proveedores.php?accion=actstatus&prov_id=' . $activar[1] . '&activar=' . $activar[2] . '&orden_id=' . $usu['orden_id']);
	}
			if($usu['email'] != '') {

				$preg2 = "SELECT c.fecha_visto, u.nombre, u.apellidos FROM " . $dbpfx . "comentarios c, " . $dbpfx . "usuarios u WHERE c.para_usuario = u.usuario AND c.bit_id = '" . $activar[0] . "'";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Usuarios !" . $preg2);
				$para = mysql_fetch_array($matr2);

				require ('parciales/PHPMailerAutoload.php');

				$mail = new PHPMailer;

				$mail->CharSet = 'UTF-8';
				$mail->isSMTP();                                      // Set mailer to use SMTP
//				$mail->Host = $smtphost;  // Specify main and backup SMTP servers
				$mail->Host = 'mail.controldeservicio.com';  // Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
//				$mail->Username = $smtpusuario;                 // SMTP username
				$mail->Username = 'notifica@controldeservicio.com';                 // SMTP username
//				$mail->Password = $smtpclave;                           // SMTP password
				$mail->Password = 'De34x.t7';                           // SMTP password
//				$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
//				$mail->Port       = $smtppuerto;
				$mail->Port       = '587';

				$mail->From = 'notifica@controldeservicio.com';
				$mail->FromName = 'Notificaciones de ' . $nombre_agencia;

				$ma = explode(',', $usu['email']);
				foreach($ma as $k) {
					$mail->addAddress($k);     // Add a recipient
				}

				$mail->addBCC('monitoreo@controldeservicio.com');
				$mail->isHTML(true);                                  // Set email format to HTML
				$mail->Subject = 'Tu comentario en la OT ' . $usu['orden_id'] . ' fue leido.';

				$email_order = '<!DOCTYPE HTML PUBLIC \'-//W3C//DTD HTML 4.0 Transitional//EN\'><html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" /></head><body style="font-family:Arial;">';
				$email_order .= '<p>Hola ' . $usu['nombre'] . ' ' . $usu['apellidos'] . '.</p>';
				$email_order .= 'Tu comentario <i>"' . $usu['comentario'] . '"</i> en la OT ' . $usu['orden_id'] . ' para ' . $para['nombre'] . ' ' . $para['apellidos'] . ' fue leido en la siguiente fecha: ' . $para['fecha_visto'];
				$email_order .= '<p style="font-size:9px;font-weight:bold;">Este mensaje fue enviado desde un sistema automático, si desea hacer algún comentario respecto a esta notificación o cualquier otro asunto respecto a la reparación o al Centro de Reparación por favor NO Responda o de Reply a este mensaje ya que este buzón no es revisado. En cambio, le pedimos nos contacte mediante los teléfonos o correos electrónicos incluidos en el cuerpo de este mensaje.  De antemano le agradecemos su atención y preferencia.</p>';
				$email_order .= '</body></html>';

				$mail->Body    = $email_order;
				$mail->AltBody = 'Hola ' . $usu['nombre'] . ' ' . $usu['apellidos'] . '.' . "\n\n" . 'Tu comentario "' . $usu['comentario'] . '" en la OT ' . $usu['orden_id'] . ' para ' . $para['nombre'] . ' ' . $para['apellidos'] . ' fue leido en la siguiente fecha: ' . $para['fecha_visto'] . "\n\n\n\n" . 'Este mensaje fue enviado desde un sistema automático, si desea hacer algún comentario respecto a esta notificación o cualquier otro asunto respecto a la reparación o al Centro de Reparación por favor NO Responda o de Reply a este mensaje ya que este buzón no es revisado. En cambio, le pedimos nos contacte mediante los teléfonos o correos electrónicos incluidos en el cuerpo de este mensaje.  De antemano le agradecemos su atención y preferencia.';
				if(!$mail->send()) {
					$mensaje = 'Errores en notificación automática: ';
					$mensaje .=  $mail->ErrorInfo;
	   			$_SESSION['msjerror'] = $mensaje;
				}
			}
//		redirigir($pagina . '?accion=consultar&orden_id=' . $orden_id);
		redirigir('ordenes.php?accion=consultar&orden_id=' . $usu['orden_id']);
}

?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
