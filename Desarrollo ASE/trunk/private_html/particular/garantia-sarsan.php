<?php

	echo '</br>
		<table cellpadding="0" cellspacing="0" border="0" class="izquierda mediana" width="840">'."\n";
// faltó -------------------------------
		echo '		<tr><td style="text-align:center;"><img src="particular/logo_agencia.png" alt=""></td></tr>'."\n";
// -------------------------------
		echo '		<tr><td>'."\n";
		echo '			<p ALIGN=center><b>CARTA DE GARANTÍA</b></p>'."\n";
// faltó -------------------------------
		echo '				</td></tr>'."\n";
// -------------------------------
		echo '			</table><br>'."\n";

		$preg1 = "SELECT cliente_nombre, cliente_apellidos FROM " . $dbpfx . "clientes WHERE cliente_id = '" . $orden['orden_cliente_id'] . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección! " . $preg1);
		$clien = mysql_fetch_array($matr1);
		
		echo '
	<table cellpadding="0" cellspacing="0" border="1" class="centrado mediana" width="840">

<tr>
  <td style="background-color:#E5E5E5; font-weight:bold; width:80px;"><strong>Nombre:</strong></td>
  <td colspan="5">' . $clien['cliente_nombre'] . ' ' . $clien['cliente_apellidos'] . '</td>
</tr>

<tr>
  <td style="background-color:#E5E5E5; font-weight:bold; width:80px;">Marca:</td>
  <td colspan="1">' . $orden['orden_vehiculo_marca'] . '</td>
  <td style="background-color:#E5E5E5; font-weight:bold; width:80px;">Modelo:</td>
  <td colspan="1">' . $orden['orden_vehiculo_tipo'] . '</td>
  <td style="background-color:#E5E5E5; font-weight:bold; width:80px;">Año:</td>
  <td colspan="1">' . $orden['vehiculo_modelo'] . '</td>
</tr>

<tr>
  <td style="background-color:#E5E5E5; font-weight:bold; width:80px;">Siniestro:</td>
  <td colspan="1">' . $reporte . '</td>
  <td style="background-color:#E5E5E5; font-weight:bold; width:80px;">Vin:</td>
  <td colspan="1">' . $orden['vehiculo_serie'] . '</td>
  <td style="background-color:#E5E5E5; font-weight:bold; width:80px;">Fecha:</td>
  <td colspan="1">' . date('d-m-Y') . '</td>
</tr>
</table>
'."\n";

echo '	
<table cellpadding="0" cellspacing="0" border="0" class="centrado mediana" width="840"></br>

<tr>
	<td ALIGN=center><b>¡Gracias Por su confianza!</b></td>
</tr>

<tr>
	<td>&nbsp;</td>
</tr>


<tr>
	<td>Uno de nuestros valores primordiales es ofrecerle la mejor calidad de servicio, mano de obra y en los materiales que utilizamos, y por ello le ofrecemos...</td>
</tr>

<tr>
	<td>&nbsp;</td>
</tr>

<tr>
	<td ALIGN=center><b>GARANTÍA DE POR VIDA en servicios de hojalateria y pintura y</b></td>
</tr>

<tr>
	<td>&nbsp;</td>
</tr>

<tr>
	<td ALIGN=center><b>En servicios de Mecánica, Garantía por 5,000 km o un año (lo que ocurra primero)</td>
</tr>
</table>'."\n";

echo '	
		</br>
		<table cellpadding="0" cellspacing="0" border="0" class="izquierda mediana" width="840">'."\n";
		echo '		<tr><td>'."\n";
		echo '			<p ALIGN=left><b>ESTA GARANTÍA CUBRE:</b></p>
						<UL TYPE=disk>
						<LI> <b>Pintura:</b> aparición de burbujas, obscurecimiento o pérdida excesiva de la pigmentación, cuarteamiento (exepto por colisión). 
						<LI> <b>Hojalatería:</b> puntos soldadura, cuadraturas, armado.
						<LI> <b>Mecánica:</b> todas las reparaciones mecanicas relacionadas con el daño original de la colisión.
						<LI> <b>Refacciones:</b> sobre defectos de las piezas nuevas mecánicas y de colisión (aplica garantía del fabricante).
						</UL>
						<p ALIGN=left><b>PARA HACER VALIDA ESTA GARANTÍA ES INDISPENSABLE:</b></p>
						<UL TYPE=disk>
						<LI> Mismo propietario del auto y póliza (reparación inicial y presentación de garantía).
						<LI> Póliza AXA activa.
						<LI> Mantenimiento de la pintura anual con costo especial por ser cliente AXA (pulida anual).
						<LI> Identificación oficial.
						<LI> Presentación de esta carta garantía.
						</UL>
						<p ALIGN=left><b>LA GARANTÍA PIERDE VALIDEZ SÍ:</b></p>
						<UL TYPE=disk>
						<LI> El auto recibe una reparación con un proveedor diferente en el periodo comprendido entre la fecha de este certificado y la fecha en que se quiera hacer valida la garantía.
						<LI> Por daños no imputables o defectos de mano de obra o reparaciones realizadas al amparo de la orden de trabajo.
						<LI> Cambio de la titularidad de la póliza y de la propiedad del auto.
						</UL>
						<p>Agradecemos su preferencia y esperamos poder srvirle en un futuro. Ponemos a su disposición nuestros datos para 					cualquier duda o aclaración: <b>65-50-36-06</b> ó <b>contacto@sarsan.com.mx</b>  </p><br/><br/>'."\n";
// faltó -------------------------------
		echo '		</td></tr>
		</table>'."\n";
// -------------------------------
		echo '	<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr><td style="text-align:center;"><br><b>SELLO DE AUTORIZACIÓN</b><br></td><td style="text-align:center;"><br><br><b>NOMBRE Y FIRMA CLIENTE</b></td></tr>


			</table><br>
'."\n";

?>