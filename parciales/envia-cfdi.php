<?php
include('idiomas/' . $idioma . '/factura.php');
$seenvia = 'si';

include('parciales/encabezado.php');
echo '	<div id="body">';
include('parciales/menu_inicio.php');
echo '		<div id="principal">';


if($datos == '') {
	
	$preg0 = "SELECT * FROM " . $dbpfx . "aseguradoras WHERE aseguradora_rfc = '" . $receptor_rfc . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de RFC de Aseguradoras! " . $preg0);
	$fila0 = mysql_num_rows($matr0);
	if($fila0 == 1) {
		$aseg = mysql_fetch_array($matr0);
		$razon = $aseg['aseguradora_razon_social'];
		$para = $aseg['aseguradora_email'];
	} elseif($fila0 > 1) {
		$seenvia = 'no';
		echo '			<form action="' . basename($_SERVER['PHP_SELF']) . '" method="post" enctype="multipart/form-data">'."\n";
		echo '			<table cellpadding="0" cellspacing="0" border="0">'."\n";
		echo '				<tr class="cabeza_tabla"><td colspan="2">' . $lang['RFCCompartido'] . '</td></tr>'."\n";
		echo '				<tr><td colspan="2"><select name="datos">'."\n";
		while($aseg = mysql_fetch_array($matr0)) {
			echo '					<option value="' . $aseg['aseguradora_razon_social'] . '|' . $aseg['aseguradora_email'] . '">' . $aseg['aseguradora_nic'] . '</option>'."\n";
		}
		echo '					</select></td></tr>'."\n";
		echo '				<tr class="cabeza_tabla"><td colspan="2">'."\n";
		echo '					<input type="hidden" name="axml" value="' . $axml . '"/>'."\n";
		echo '					<input type="hidden" name="orden_id" value="' . $orden_id . '"/>'."\n";
		echo '					<input type="hidden" name="reporte" value="' . $reporte . '"/>'."\n";
		echo '					<input type="hidden" name="obsad" value="' . $obsad . '"/>'."\n";
		echo '				</td></tr>'."\n";
		echo '				<tr><td colspan="2" style="text-align:left;"><div class="control"><input type="submit" value="Enviar" /></div></td></tr>'."\n";
		echo '				<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
		echo '			</table></form>'."\n";
	}
	if($fila0 < 1) {
		$preg1 = "SELECT e.empresa_razon_social, c.cliente_nombre, c.cliente_apellidos, c.cliente_email FROM " . $dbpfx . "empresas e, " . $dbpfx . "clientes c WHERE e.empresa_rfc = '" . $receptor_rfc . "' AND c.cliente_email != '' AND c.cliente_email != '" . $agencia_email . "' AND e.empresa_id = c.cliente_empresa_id";
//		echo $preg1;
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de RFC de Cliente! " . $preg1);
		$fila1 = mysql_num_rows($matr1);
		if($fila1 == 1) {
			$clie = mysql_fetch_array($matr1);
			$razon = $clie['empresa_razon_social'];
			$para = $clie['cliente_email'];
		} elseif($fila1 > 1) {
			$seenvia = 'no';
			echo '			<form action="' . basename($_SERVER['PHP_SELF']) . '" method="post" enctype="multipart/form-data">'."\n";
			echo '			<table cellpadding="0" cellspacing="0" border="0">'."\n";
			echo '				<tr class="cabeza_tabla"><td colspan="2">' . $lang['RFCCompartido'] . '</td></tr>'."\n";
			echo '				<tr><td colspan="2"><select name="datos">'."\n";
			while($clie = mysql_fetch_array($matr1)) {
				echo '					<option value="' . $clie['empresa_razon_social'] . '|' . $clie['cliente_email'] . '">' . $clie['cliente_nombre'] . ' ' . $clie['cliente_apellidos'] . '</option>'."\n";
			}
			echo '					</select></td></tr>'."\n";
			echo '				<tr class="cabeza_tabla"><td colspan="2">'."\n";
			echo '					<input type="hidden" name="axml" value="' . $axml . '"/>'."\n";
			echo '					<input type="hidden" name="orden_id" value="' . $orden_id . '"/>'."\n";
			echo '					<input type="hidden" name="reporte" value="' . $reporte . '"/>'."\n";
			echo '					<input type="hidden" name="obsad" value="' . $obsad . '"/>'."\n";
			echo '				</td></tr>'."\n";
			echo '				<tr><td colspan="2" style="text-align:left;"><div class="control"><input type="submit" value="Enviar" /></div></td></tr>'."\n";
			echo '				<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
			echo '			</table></form>'."\n";
		} else {
			$seenvia = 'no';
		}
	}

} else {
	$dato = explode('|', $datos);
	$razon = $dato[0];
	$para = $dato[1];
}

//echo $razon;
//echo $para;
//echo $seenvia;


if($seenvia == 'si') {
	$asunto = $asunto_factura_email . $nombre_agencia . ' OT ' . $orden_id;
	$fotos[] = DIR_DOCS . $axml;
	$fotos[] = DIR_DOCS . $qrnom[0] . '.pdf';
	
	$contenido = '<!-- BODY -->
<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h3>Estimado(a) cliente de ' . $razon . '</h3>
				<p class="lead">Anexos encontrará los archivos correspondientes a nuestra factura ' . $comprobante_serie . $comprobante_folio . ' del Centro de Reparación Automotriz ' . $nombre_agencia . ', que corresponden a los trabajos realizados a la Orden de Trabajo ' . $orden_id;
	if($reporte != '' && $reporte != '0') {
		$contenido .= ' del reporte o siniestro ' . $reporte;
		$asunto .= ' del reporte o siniestro ' . $reporte;
	}
	$contenido .= '.</p>
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
</table><!-- /BODY -->'."\n";
	include('parciales/notifica2.php');
	unset($_SESSION['msjerror']);
	echo '			<table cellpadding="0" cellspacing="0" border="0">'."\n";
	echo '				<tr class="cabeza_tabla"><td colspan="2">' . $mensaje . '</td></tr>'."\n";
	echo '				<tr><td colspan="2"><h2>';
	if($msjerror == 1) { echo $lang['FacturaNoEnv']; } else { echo $lang['FacturaEnviada']; }
	echo '</h2></td></tr>'."\n";
	echo '				<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '				<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['RegresarOT'].'" title="'. $lang['RegresarOT'].'"></a></div></td></tr>'."\n";
	echo '				<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '			</table>'."\n";
} else {
	echo '			<table cellpadding="0" cellspacing="0" border="0">'."\n";
	echo '				<tr><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '				<tr class="cabeza_tabla"><td colspan="2">' . $lang['SinEmail'] . '</td></tr>'."\n";
	echo '				<tr><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '				<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['RegresarOT'].'" title="'. $lang['RegresarOT'].'"></a></div></td></tr>'."\n";
	echo '				<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '			</table>'."\n";
}

echo '		</div>
	</div>'."\n";
include('parciales/pie.php');

?>
