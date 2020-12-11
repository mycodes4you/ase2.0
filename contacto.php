<?php 
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/contacto.php');
include('parciales/encabezado.php'); 
?>
	<div id="body">
<?php include('parciales/menu_inicio.php'); ?>
		<div id="principal">
			<table cellpadding="0" cellspacing="0" border="0" width="50%">
				<tr>
					<td>
						<div>
							<h3>Soporte y ayuda</h3>
							<p>Por favor utilice nuestro sistema de Tickets en Línea para registrar sus requerimientos de Ayuda, Cambios de Estatus de Ordenes de Trabajo o reportes de fallas.</p>
							<a href="http://controldeservicio.com/open.php" target="_blank"><img src="idiomas/es_MX/imagenes/agregar-un-ticket.png" alt="Agregar un Ticket" /></a>&nbsp;
							<a href="http://controldeservicio.com/view.php" target="_blank"><img src="idiomas/es_MX/imagenes/ver-un-ticket.png" alt="Agregar un Ticket" /></a><br clear="all">
							<h3>También nos puede enviar un mensaje al correo: soporte@controldeservicio.com</h3>
							
							<p>Mediante los siguientes enlaces podrás contactar a nuestros asesores de servicio utilizando la aplicación Skype. Si tienes instalado un micrófono en tu PC, podrás hacer una llamada de voz; si no tienes micrófono, por favor utiliza el botón de Chat.</p>
							<a href="skype:autoshopeasy?call"><img src="idiomas/es_MX/imagenes/llamame.a.skype.png" width="250" height="52" alt="Llamar por Skype" /></a>
							<a href="skype:autoshopeasy?chat"><img src="idiomas/es_MX/imagenes/chat-por-skype.png" alt="Chat por Skype" /></a><br clear="all">
							<?php if($Arr43p87=='1') {
								echo '<h3>Alternativamente nos puede llamar al Teléfono: '; 
/*								if($zona == 'MTY') {echo '(81) 8421-0145';}
								elseif($zona == 'GDL') {echo '(33) 8421-0912';}
								else {echo '(55) 8421-3307'; }
*/
								echo '(55) 8421-3307';
								echo '</h3>';
							} ?>
							<h3>Capacitación y Entrenamiento</h3>
							<a href="imagenes/SoporteAutoshop.exe" target="_blank">Software de Control Remoto UVNC</a> | <a href="imagenes/autoshopeasy_seg.apk" target="_blank">APP ASE para subir Fotos</a>
						<div>
					</td>
				</tr>
			</table>
		</div>
	</div>
<?php include('parciales/pie.php');
/* Archivo index.php */
/* e-Taller */
