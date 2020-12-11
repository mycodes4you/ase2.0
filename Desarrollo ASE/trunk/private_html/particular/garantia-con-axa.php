<?php

	// --- CONSULTAR CLIENTE ---
	$pregunta_cli = "SELECT cliente_nombre, cliente_apellidos FROM " . $dbpfx . "clientes WHERE cliente_id = ' " . $orden['orden_cliente_id'] . "'";
	$mtr_cliente = mysql_query($pregunta_cli) or die("ERROR: Fallo seleccion!" . $pregunta_cli);
	$info_cliente = mysql_fetch_assoc($mtr_cliente);

		echo '	<form action="entrega.php?accion=garantia" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="centrado mediana" width="850">
		<tr>
			<td colspan="7">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="7">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="4"><img src="particular/logo-agencia.png" alt="AGENCIA" title="AGENCIA"></td>
			<td colspan="3"><h2>CARTA GARANTÍA</h2></td>
		</tr>
		<tr>
			<td colspan="7">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="7">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="7">&nbsp;</td>
		</tr>
		<tr style="background-color:black; color:white; font-weight:bold;">
			<td style="width:50px;">NOMBRE</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>OT</td>
			<td>SINIESTRO</td>
			<td>FECHA</td>
		</tr>
		<tr>
			<td colspan="4">' . $info_cliente['cliente_nombre'] . ' ' . $info_cliente['cliente_apellidos'] . '</td>
			<td>' . $orden_id . '</td>
			<td>' . $reporte . '</td>
			<td>&nbsp;' . date('d/m/Y') . '</td>
		</tr>
		<tr>
			<td colspan="7">&nbsp;</td>
		</tr>
		<tr style="background-color:black; color:white; font-weight:bold;">
			<td>MARCA</td>
			<td></td>
			<td>MODELO</td>
			<td>AÑO</td>
			<td>' . $lang['PLACAS'] . '</td>
			<td>KILOMETRAJE</td>
			<td>VIN</td>
		</tr>
		<tr>
			<td>' . $orden['orden_vehiculo_marca'] . '</td>
			<td>&nbsp;</td>
			<td>' . $orden['orden_vehiculo_tipo'] . '</td>
			<td>' . $orden['vehiculo_modelo'] . '</td>
			<td>' . $orden['orden_vehiculo_placas'] . '</td>
			<td><input type="text" name="odometro" value="' . $odometro . '" size="8" />
			</td><td>' . $orden['vehiculo_serie'] . '</td>
		</tr>
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr>
			<td colspan="7">
				<h3>¡Gracias por su confianza!</h3>
			</td>
		</tr>'."\n";
		echo '	</table>'."\n";
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="izquierda mediana" width="850">'."\n";
		echo '		<tr><td>';
		echo '			<p><i>Uno de nuestros valores primordiales es ofrecerle la mejor calidad en servicio, mano de obra y en los materiales que utilizamos, y por ello le ofrecemos...</i></p>
			<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="850">
				<tr>
					<td colspan="7">
						<h3>
							<small>
							GARANTÍA de 2 años en servicios de hojalatería.<br>
							GARANTÍA En servicios de Mecánica por 5,000 km. o 1 año (lo que ocurra primero).<br>
							GARANTÍA en refacciones 1 año y en refacciones electrónicas 1 MES. 
							</small>
						</h3>
					</td>
				</tr>
				<tr>
					<td colspan="7">ESTA GARANTÍA CUBRE:</td>
				</tr>
				<tr>
					<td style="width:150px;">&nbsp;</td>
					<td>&nbsp;</td>
					<td><b>Pintura:</b> aparición de burbujas, obscurecimiento o pérdida excesiva de la pigmentación, cuarteamiento (excepto por colisión).</td>
				</tr>
				<tr>
					<td style="width:150px;">&nbsp;</td>
					<td>&nbsp;</td>
					<td><b>Hojalatería:</b> puntos soldadura, cuadraturas, armado.</td>
				</tr>
				<tr>
					<td style="width:150px;">&nbsp;</td>
					<td>&nbsp;</td>
					<td><b>Mecánica:</b> todas las reparaciones mecánicas relacionadas con el daño original de la colisión.</td>
				</tr>
				<tr>
					<td style="width:150px;">&nbsp;</td>
					<td>&nbsp;</td>
					<td><b>Refacciones:</b> sobre defectos de las piezas nuevas mecánicas y de colisión (aplica garantía del fabricante).</td>
				</tr>
			</table><br>
			<div style="border-style:solid; border-width: 2px; padding-left:3px;"><p></p><strong>PARA HACER VÁLIDA LA GARANTÍA ES INDISPENSABLE:</strong><br>
			<ul>
				<li>Mismo propietario del auto.</li>
				<li>Mantenimiento de la pintura anual.</li>
				<li>Identificación Oficial.</li>
				<li>Presentación de esta carta garantía.</li>
			</ul>
			</div>
			<table cellpadding="0" cellspacing="0" border="0" class="centrado" width="850">
				<tr>
					<td colspan="7">
						<h3>LA GARANTÍA PIERDE VALIDEZ SI:</h3>
					</td>
				</tr>
			</table>
			<ul>
				<li>El auto recibe una reparación con un proveedor diferente en el período comprendido en la fecha de este certificado y la fecha en la que se quiera hacer válida la garantía.</li>
				<li>Por daños no imputables a defectos de mano de obra o reparaciones realizadas al amparo de la orden de trabajo.</li>
				<li>Cambio del propietario del auto.</li>
			</ul>
			<p>Agradecemos su preferencia y esperamos poder servirle en un futuro. Ponemos a su disposición nuestros datos para cualquier duda o aclaración 65-5036-06 ó contacto@sarsan.com.mx</p>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td style="text-align:center;"><br>SELLO DE AUTORIZACIÓN</td>
					<td style="text-align:center;"><br>NOMBRE Y FIRMA DEL CLIENTE</td>
				</tr>
			</table>
		<input type="hidden" name="orden_id" value="' . $orden_id . '" />
		<input type="hidden" name="dato" value="' . $dato . '" />
	</table>	
	<br>
	<div class="control"><button name="Actualizar Kilometraje" type="submit">Actualizar Kilometraje</button></div>
	</form>
	<br>'."\n";

// ---- CARTA FINIQUITO ----
if($es_axa == 1){ // ---- comienza carta Finiquito Axa ----

	echo '
	<div class="saltopagina"></div>'."\n";
	echo '
	<table cellpadding="0" cellspacing="0" border="0" class="centrado mediana" width="850">
		<tr>
			<td colspan="2" style="width:570px; text-align:left;">
				<img src="idiomas/' . $idioma . '/imagenes/encabezado-carta-axa.png" alt="">
			</td>
			<td style="text-align:left; vertical-align:top;">
				&nbsp;
			</td>
		</tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" class="centrado mediana" width="850">
		<tr>
			<td style="width:595; text-align:justify;">
				<h3>
					<small>Siniestro: ' . $reporte . '</small><br><br>
					Finiquito de aseguradora o tercero.
				</h3>
				<br>
				<p style="text-align:justify;">
					Con referencia a la reclamación por falta de respuesta y/o atraso en la reparación que realicé a AXA Seguros, con fecha ___ de _________ de _____, derivada del accidente en el cual fué implicado el automóvil de mi propiedad que se encontraba asegurado bajo la póliza número ____________, me es grato manifestar que dicha reclamaciónha sido debidamente atendida y terminada a mi entera satisfacción.
					<br><br>
					En términos de la cláusula de Sumas Aseguradas, incluida en las condiciones generales de la póliza de seguro, los costos de reparación disminuyen en igual cantidad el límite de responsabilidad de AXA por este siniestro.
					<br><br>
					Relevo a AXA Seguros o a sus representantes de cualquier responsabilidad posterior con motivo de la citada reclamación.
					<br><br>
					<br><br>
					Atentamente:
					<br><br>
					____________________________
					<br>
					Nombre y firma del cliente
					<br><br>
					<br>
					<b><small>Nota: Recuerda que cuentas con 30 días para hacer efectiva tu compensación. Cualquier duda, llámanos al 01 800 911 2014.</small></b>
					<br>
					<br>
					<small>AXA Seguros, SA. De CV. Félix Cuevas 366, Piso 6, Col. Tlacoquemécatl, Del. Benito Juárez, 03200, México, DF. Tels: 5169 1000 01 800 900 1292 axa.mx</small>
				</p>
			</td>
		</tr>
	</table>'."\n";
	
 } else{ // ---- comienza carta Finiquito Default ----
	
	echo '
	<table cellpadding="0" cellspacing="0" border="0" class="centrado mediana" width="850">
		<tr>
			<td colspan="4"><img src="particular/logo-agencia.png" alt="AGENCIA" title="AGENCIA"></td>
			<td colspan="3"><h2>CARTA FINIQUITO</h2></td>
		</tr>
	</table>
	<br>
	<table cellpadding="0" cellspacing="0" border="0" class="centrado mediana" width="850">
		<tr>
			<td style="width:595; text-align:justify;">
				<h3>
					Finiquito de aseguradora o tercero.
				</h3>
				<br>
				<p style="text-align:justify;">
					Con referencia a los servicios que me fueron  proporcionador por <b>' . $agencia . '</b>
					referentes a mi vehículo: <b>' . $orden['orden_vehiculo_marca'] . ' ' . $orden['orden_vehiculo_tipo'] . ' ' . $orden['vehiculo_modelo'] . ' Placas: ' . $orden['orden_vehiculo_placas'] . '</b> establazco  mi completa conformidad y satisfacción. Ya que me fue entregado en tiempo y forma en la fecha ___ de _________ de _____.
					<br><br>
					<br><br>
					Atentamente:
					<br><br>
					____________________________
					<br>
					Nombre y firma del cliente
					<br><br>
					<br>
				</p>
			</td>
		</tr>
	</table>'."\n";
}




		
?>
