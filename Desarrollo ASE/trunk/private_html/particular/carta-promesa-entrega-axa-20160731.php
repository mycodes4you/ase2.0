<?php
		echo '	<form action="ingreso.php?accion=cartaaxa" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="mediana" width="840">
		<tr><td style="width:70%; text-align:center;"><img src="particular/logo-axa.png" alt=""></td><td style="width:70%;text-align:center; vertical-align:middle;">&nbsp;</td></tr>
		<tr><td><br><br><br><span style="font-size:1.5em; font-weight:bold;">Tiempo de entrega garantizado</span></td><td><span style="font-size:0.8em"></span></td></tr></table>'."\n";
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="izquierda mediana" width="840">'."\n";
		echo '		<tr><td style="width:70%; text-align:justify;">';
		echo '			<p style="text-align:justify;"><br>
		<br>
		Estimado(a) asegurado(a):<br>
		<br>
		En AXA nos comprometemos contigo y te brindamos la fecha de entrega de tu auto:<br>
		<br>
		Taller: ' . $nombre_agencia . '<br>
		Siniestro: ' . $reporte . '<br>
		Póliza: ' . $poliza . '<br> 
		Nombre del Asegurado: ' . $ord['cliente_nombre'] . ' ' . $ord['cliente_apellidos'] . '<br>
		Marca: ' . $ord['vehiculo_marca'] . '<br>
		Modelo: ' . $ord['vehiculo_modelo'] . '<br>
		Tipo: ' . $ord['vehiculo_tipo'] . '<br>
		Placas: ' . $ord['orden_vehiculo_placas'] . '<br>
		Fecha compromiso de entrega: ' . date('Y-m-d 17:00:00', strtotime($ord['orden_fecha_promesa_de_entrega'])) . '<br>
		<br>
		Ahora que tu auto entra al taller, no olvides revisar tu carátula de póliza pues puedes obtener un auto en renta gratis si cuentas con la cobertura Auto Consentido.<br>
		<br>
		<br>
		<span style="font-weight: bold;">Recuerda que:<br>
		<br>
		Te garantizamos la entrega de tu auto en la fecha promesa pactada. &nbsp; Si no cumplimos con ésta tenemos una compensación para ti, llámanos al 01 800 911 2014 para conocerla.</span><br>
		<br>
		Aplica en talleres TAC, con un máximo de 30 días de retraso. No aplica para pólizas de flotillas, pólizas a nombre de personas morales, ni para reparaciones en agencias automotrices.<br>
		<br>
		<br>
		<br>
		Atentamente.<br>
		AXA Seguros, S.A. de C.V.<br>
		Tels. 5169 1000 | 01 800 900 1292 | axa.mx
		</p>
		</td><td>&nbsp;
		</td></tr>
		</table>
		</form>'."\n";
		
?>
