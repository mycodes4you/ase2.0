<?php
		echo '	<form action="entrega.php?accion=garantia" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="centrado mediana" width="850">
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr><td colspan="7">&nbsp;</td></tr>
		<tr><td colspan="4">&nbsp;</td><td colspan="1"><b>GARANTIA DE REPARACION</b></td></tr>
		<tr><td colspan="4">&nbsp;</td><td colspan="3"><br>Númeo de siniestro:_________________</td></tr>
		<tr><td colspan="7">&nbsp;</td></tr>'."\n";
		echo '	</table>'."\n";
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="izquierda mediana" width="850">'."\n";
		echo '		<tr><td>';
		echo '			
				<p ALIGN="justify">
					<b>' . $nombre_agencia . '</b> en lo subsecuente LA AGENCIA, hacemos constar la reparación realizada dentro de nuestras instalaciones bajo la orden de trabajo No. <b>' . $orden_id . '</b>, respecto del vehículo Marca <b>' . $orden['orden_vehiculo_marca'] . '</b>, Tipo <b>' . $orden['orden_vehiculo_tipo'] . '</b>, Modelo <b>' . $orden['vehiculo_modelo'] . '</b>, placas de circulación <b>' . $orden['orden_vehiculo_placas'] . '</b> , a nombre de _______________________________________________________________ (propietario).
				</p>
				<p ALIGN="justify">
					La reparación antes referida se hizo en apego a las mejores prácticas de trabajo y en uso de la mejor tecnología disponible para tal efecto por ende extendemos a favor del propietario el vehículo antes referido y a partir de la fecha de la presente constancia, las siguientes garantías:
				</p>
			<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="850">
				<tr>
					<td></td>
					<td><b>I. GARNTÍA DE 1 AÑO</b></td>
					<td>
						Sobre defectos en la mano de obra que incluyen soldadura y cambio de piezas nuevas; pintura cubriendo principalmente aparición de burbujas, oscurecimiento o pérdida excesiva de la pigmentación.
					</td>
				</tr>
				<tr>
					<td><br></td>
				</tr>
				<tr>
					<td></td>
					<td><b>II. GARANTÍA DE 3 MESES</b></td>
					<td>
						En todas las reparaciones mecánicas relacionadas directamente con el daño original de la colisión, en suspensión, aire acondicionado o componentes eléctricos.
					</td>
				</tr>
				<tr>
					<td><br></td>
				</tr>
				<tr>
					<td></td>
					<td><b>III. GARANTÍA DE CALIDAD. LA-AGENCIA</b></td>
					<td>
						Garantiza que las piezas y los materiales usados son de primera calidad, y nos comprometemos a responder de ello y en su caso asistirlo para demandar garantías de rigen proporcionadas por el cliente.
					</td>
				</tr>
			</table><br>
			<p ALIGN="justify">
				Las garantías antes referidas se entienden para efectos de sustitución, reparación y o detallado en su caso, sobre los trabajos realizados al amparo de la presente orden de trabajo, las cuales serán realizadas por LA AGENCIA a solicitud del cliente, por lo que bajo ninguna circunstancia incluyenlaoligaión del reembolso alguno o remuneración económica por pérdida consecuencial de ninguna especie. 
			</p>
			<p ALIGN="justify">
				Las garantías de referencia no tendrán aplicación y se consideran excluidas sin responsabilidad para LA AGENCIA cuando ocurrancualesquiera de los siguientes eventos: 
			</p>
			<div style="border-style:solid; border-width: 2px; padding-left:3px;"><p></p><strong>Exclusiones:</strong><br>
			<ul>
				<li><b>a)</b> Daños no imputables a defectos en mano de obra o relaraciones realizadas al amparo de la presente orden de trabajo.</li>
				<li><b>b)</b> Cambio en la titularidad de la póliza con cargo a la cual se afecta el presente siniestro.</li>
				<li><b>c)</b> Reparacion por proveedor distinto a LA AGENCIA entre el período comprendido de la fecha de este certifcado al momento en que se presenta hacer válida la garantía</li>
			</ul>
			</div><br>
			<p ALIGN="justify">
				ESTA GARANTÍA POR ESCRITO NO DEBERÁ SER VARIADA, COMPLEMENTADA, CALIFICADA O INTERPRETADA POR CUALQUIER INSTANCIA PREVIA A LA NEGOCIACIÓN DIRECTA CON LA AGENCIA Y DEBERÁ CONSIDERARSE NULAY SIN EFECTO CUANDO EXISTA ALTERACIÓN DE LAS REPARACIONES NO AUTORIZADAS PREVIAMENTE POR NOSOTROS. LA PRESENTE GARANTÍA NO ES TRANSFERIBLE. NOS RESERVAMOS EL DERECHO DE EVALUAR FÍSICAMENTE CALQUIER PROBLEMA QUE PUEDA SURGIR COMO RESULTADO DIRECTO DE LA REPARACIÓN ORIGINAL. 
			</p>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr><td style="text-align:center;"><br>_________________________</td>
				<td style="text-align:center;"><br>_________________________</td></tr>
				<tr><td style="text-align:center;"><b>FIRMA DEL ASEGURADO</b></td>
				<td style="text-align:center;"><b>SELLO Y FIRMA CDR</b></td></tr>
				<tr>
					<td><br></td>
				</tr>
				<tr><td colspan="2" style="text-align:center;">' . $agencia_direccion . ' Col. ' . $agencia_colonia . ', ' . $agencia_municipio . ', ' . $agencia_estado . ' C.P. ' . $agencia_cp . '<br>'.$agencia_email.'</td></tr>
			</table>
			
		<input type="hidden" name="orden_id" value="' . $orden_id . '" />
		<input type="hidden" name="dato" value="' . $dato . '" />
		<div class="control"><button name="Actualizar Kilometraje" type="submit">Actualizar Kilometraje</button></div>
		</form>'."\n";


?>
