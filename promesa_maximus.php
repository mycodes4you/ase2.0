<?php
		echo '	<form action="ingreso.php?accion=cartaaxa" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="mediana" width="850">
		<tr><td style="width:150px; text-align:center;"><img src="imagenes/logo-axa.png" alt=""></td><td style="text-align:center; vertical-align:middle;">CARTA FECHA SALIDA</td></tr>
		<tr><td><strong>PRESENTE</strong></td><td><span style="font-size:0.8em">AXA SEGUROS, S.A. de C.V.</span></td></tr></table>'."\n";
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="izquierda mediana" width="850">'."\n";
		echo '		<tr><td colspan="5">';
		echo '			<p style="text-align: center;"><br><br>POR MEDIO DE LA PRESENTE LE NOTIFICAMOS QUE ESTE CENTRO DE REPARACION SE COMPROMETE A ENTREGAR REPARADA LA UNIDAD EN LA FECHA QUE A CONTINUACION SE ESTIMA, EL CAMBIO DE DICHA FECHA SOLAMENTE PODRA HACERSE CON PLENO CONOCIMIENTO Y AUTORIZACION DE AXA SEGUROS, S.A. DE C.V. HACIENDO USO Y EN APEGO A LAS CONDICIONES GENERALES DEL SEGURO.</p></td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td style="width:150px;">ORDEN</td><td style="width:170px; text-align:center; border-style:solid; border-width: 1px;">' . $orden_id . '</td><td style="width:170px;">&nbsp;</td><td style="width:190px;">FECHA DE SALIDA</td><td style="width:170px; text-align:center; border-style:solid; border-width: 1px;">&nbsp;</td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td>SINIESTRO</td><td style="text-align:center; border-style:solid; border-width: 1px;">' . $reporte . '</td><td>&nbsp;</td><td>FECHA DE INGRESO</td><td style="text-align:center; border-style:solid; border-width: 1px;">'.$ord['orden_fecha_recepcion'].'</td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td colspan="5" style="font-size:1.5em; font-weight:bold; text-align:center;">'.$ord['cliente_nombre'].' '.$ord['cliente_apellidos'].'</td></tr>'."\n";
		echo '		<tr><td colspan="5" style="text-align:center;">NOMBRE DEL ASEGURADO O TERCERO</td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td colspan="5" style="font-size:1.3em; font-weight:bold; padding:2px; text-align:center; border-style:solid; border-width: 1px;">MAXIMUS BODY SHOP</td></tr>'."\n";
		echo '		<tr><td colspan="5" style="text-align:center;">NOMBRE DEL CENTRO DE REPARACION</td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td>MARCA</td><td style="text-align:center; border-style:solid; border-width: 1px;">' . $ord['orden_vehiculo_marca'] . '</td><td>&nbsp;</td><td>MODELO</td><td style="text-align:center; border-style:solid; border-width: 1px;">'.$ord['vehiculo_modelo'].'</td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td>TIPO</td><td style="text-align:center; border-style:solid; border-width: 1px;">' . $ord['orden_vehiculo_tipo'] . '</td><td>&nbsp;</td><td>PLACAS</td><td style="text-align:center; border-style:solid; border-width: 1px;">'.$ord['orden_vehiculo_placas'].'</td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td>&nbsp;</td><td colspan="3" style="text-align:center;"><input type="text" name="fecha_promesa" style="text-align:center;" /></td><td>&nbsp;</td></tr>'."\n";
		echo '		<tr><td colspan="5" style="text-align:center;">FECHA PROMESA</td></tr>'."\n";
		echo '		<tr><td colspan="5">&nbsp;</td></tr>'."\n";
		echo '		<tr><td colspan="2" style="text-align:center;"><img src="imagenes/logo-agencia.png" alt=""><hr>SELLO<br>CENTRO DE REPARACION</td><td>&nbsp;</td><td colspan="2" style="text-align:center; vertical-align:bottom"><hr>FIRMA ASEGURADO / TERCERO</td></tr>
		<tr><td colspan="5">
		<input type="hidden" name="orden_id" value="' . $orden_id . '" />
		<input type="hidden" name="dato" value="' . $dato . '" />
		<div class="control"><button name="promesa" type="submit">Registrar Fecha Promesa</button><br><br></div>
		</td></tr></table>
		</form>'."\n";
		
?>
