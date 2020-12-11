<?php
		echo '	<form action="entrega.php?accion=garantia" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="centrado mediana" width="850">
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr><td colspan="4">&nbsp;</td><td colspan="3">GARANTIA DE REPARACION</td></tr>
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr style="background-color:black; color:white; font-weight:bold;"><td style="width:50px;">&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>NO. SINIESTRO</td><td>&nbsp;</td><td>FECHA SALIDA</td><td style="width:100px;">&nbsp;</td></tr>
		<tr><td>&nbsp;</td><td>' . $orden_id . '</td><td>&nbsp;</td><td>' . $reporte . '</td><td>&nbsp;</td><td>' . date('d/m/Y') . '</td><td>&nbsp;</td></tr>
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr style="background-color:black; color:white; font-weight:bold;"><td>&nbsp;</td><td>MARCA</td><td>TIPO</td><td>MODELO</td><td>' . $lang['PLACAS'] . '</td><td>KILOMETRAJE</td><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td><td>' . $orden['orden_vehiculo_marca'] . '</td><td>' . $orden['orden_vehiculo_tipo'] . '</td><td>' . $orden['vehiculo_modelo'] . '</td><td>' . $orden['orden_vehiculo_placas'] . '</td><td><input type="text" name="odometro" value="' . $odometro . '" size="8" /></td><td>&nbsp;</td></tr>
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr><td colspan="7">&nbsp;</td></tr>'."\n";
		echo '	</table>'."\n";
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="izquierda mediana" width="850">'."\n";
		echo '		<tr><td>';
		echo '			<p><i>' . $agencia_razon_social . ' garantiza los trabajos y reparaciones realizados a consecuencia del siniestro o trabajos particulares autorizados por el cliente, según sea EL CASO:</i></p>
			<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="850">
				<tr><td colspan="7">&nbsp;</td></tr>
				<tr><td colspan="7">&nbsp;</td></tr>
				<tr><td style="width:150px;">&nbsp;</td><td>&nbsp;</td><td>1.- Laminado</td><td style="width:100px;">&nbsp;</td><td>150 días naturales.</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td><td>2.- Pintura</td><td>&nbsp;</td><td>150 días naturales.</td><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td><td colsapn="3">3.- Mecánica</td><td>&nbsp;</td><td>90 días naturales.</td></tr>
				<tr><td colspan="7">&nbsp;</td></tr>
				<tr><td colspan="7">&nbsp;</td></tr>
			</table><br>
			<div style="border-style:solid; border-width: 2px; padding-left:3px;"><p></p><strong>Exclusiones:</strong><br>
			<ul>
				<li>a. <b>NO</b> ampara daños, rayones, tallones o cualquier daño a la pintura por agentes externos a la misma después de la reparación por el <b>Cliente.</b></li>
				<li>b. <b>NO</b> ampara daños por uso, desgaste natural en las piezas de mecánica del vehículo.</li>
				<li>c. <b>NO</b> ampara daños en piezas eléctricas del vehículo.</li>
				<li>d. <b>NO</b> ampara golpes o daños posteriores a la recepción por el Cliente.</li>
			</ul>
			</div><br><br><br>
			<p>En caso de requerirse algún servicio bajo los términos de esta garantía, El cliente deberá llamar al teléfono de atención a clientes al <b>' . $agencia_telefonos . '</b><br>
			Las reparaciones por garantía serán efectuadas sin costo y para hacerse válida deberá presentar éste documento.<br><br><br><br>
Atte.</p>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr><td style="text-align:center;"><br>Gerencia<br>' . $agencia . '</td><td style="text-align:center;"><br><br>FIRMA ENTERADO CLIENTE</td></tr>
				<tr><td colspan="2" style="height:20px;"></td></tr>
				<tr><td colspan="2">El cliente firma de enterado y de conformidad respecto a las condiciones mecánicas y generales del auto, así como de sus contenidos y accesorios.</td></tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr><td colspan="2" style="text-align:center;">' . $agencia_direccion . ' Col. ' . $agencia_colonia . ', ' . $agencia_municipio . ', ' . $agencia_estado . ' C.P. ' . $agencia_cp . '<br>'.$agencia_email.'</td></tr>
			</table>
		<input type="hidden" name="orden_id" value="' . $orden_id . '" />
		<input type="hidden" name="dato" value="' . $dato . '" />
		<div class="control"><button name="Actualizar Kilometraje" type="submit">Actualizar Kilometraje</button></div>
		</form>'."\n";
		
?>
