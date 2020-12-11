<?php
		echo '		<form action="ingreso.php?accion=cartaaxa" method="post" enctype="multipart/form-data">
		<table cellpadding="0" cellspacing="0" border="0" class="mediana" width="840">
			<tr><td style="width:570px; text-align:left;"><img src="idiomas/' . $idioma . '/imagenes/encabezado-carta-axa-2.png" alt=""></td><td style="text-align:left; vertical-align:top;">&nbsp;</td></tr>
		</table>'."\n";
		echo '		<table cellpadding="0" cellspacing="0" border="0" class="izquierda mediana" width="840">'."\n";
		echo '			<tr><td style="width:595; text-align:justify;">';
		echo '				
		<br>
		<br>
		<p style="text-align:justify;">
		Estimado(a) asegurado(a):<br>
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
		Fecha compromiso de entrega: ' . date('Y-m-d', strtotime($ord['orden_fecha_promesa_de_entrega'])) . '<br>
		<br>
		Ahora que tu auto entra al taller, no olvides revisar tu carátula de póliza ya que puedes obtener un auto en renta gratis si cuentas con la cobertura Auto Consentido.<br>
		<br>
		En caso de tener contratada la cobertura Auto Consentido y desees hacer uso de la misma, favor de comunicarte con algunos de los siguientes proveedores para ubicar la oficina más cercana:
		<br>
		<ul>
		
			<li type="disc">EUROPCAR	01800 - 2012084, Opción 5</li>
			<li type="disc">HERTZ		01800 - 7095000</li>

		</ul>
		Nota: Aplican restricciones de acuerdo a las condiciones de la cobertura.
		<br>
		<strong>Recuerda que:
		<br><br>
		Te garantizamos la entrega de tu auto en la fecha promesa pactada.
		Si no cumplimos con esta, puedes obtener un descuento del 20% en deducible por cada día de atraso. Aplican términos y condiciones de uso.
		<br>
		<br>
		Aplica en talleres TAC, y en donde por siniestro se determinó el pago de deducible. Limitado hasta el 100% del deducible. No aplica para reparaciones en agencias automotrices.</strong>
		<br>
		<br>
		<small>Atentamente.<br>
		AXA Seguros, S.A. de C.V.<br>
		</small>
		</p>
		</td></tr>
		</table>
		<br>
		<br>
		<table cellpadding="0" cellspacing="0" border="0" class="mediana" width="840">
			<tr><td style="width:570px; text-align:left;"><img src="idiomas/' . $idioma . '/imagenes/pie-carta-axa-2.png" alt=""></td><td style="text-align:left; vertical-align:top;">&nbsp;</td></tr>
		</table>
		</form>'."\n";

		//------------- GENERAR PDF ------------------------------------

		require('fpdf.php');
		class PDF extends FPDF
		{
		

		}
		
		//--------- variables de contenido de la carta -----------------

		$saludo = 'Estimado(a) asegurado(a):';
		$inicio_linea1 = 'En AXA nos comprometemos contigo y te brindamos la fecha de entrega de tu ';
		$inicio_linea2 = 'auto:';
		$taller = utf8_decode('Taller: ' . $nombre_agencia . '');
		$siniestro = 'Siniestro: ' . $reporte . '';
		$poliza = utf8_decode('Póliza: ' . $poliza . '');
		$asegurado = utf8_decode('Nombre del Asegurado: ' . $ord['cliente_nombre'] . ' ' . $ord['cliente_apellidos'] . '');
		$marca = utf8_decode('Marca: ' . $ord['vehiculo_marca'] . '');
		$modelo = utf8_decode('Modelo: ' . $ord['vehiculo_modelo'] . '');
		$tipo = utf8_decode('Tipo: ' . $ord['vehiculo_tipo'] . '');
		$placas = 'Placas: ' . $ord['orden_vehiculo_placas'] . '';
		$fecha_entrega = 'Fecha compromiso de entrega: ' . date('Y-m-d', strtotime($ord['orden_fecha_promesa_de_entrega'])) . '';
		$renglon1 = utf8_decode('Ahora que tu auto entra al taller, no olvides revisar tu carátula de póliza ya que puedes');
		$renglon2 = utf8_decode('obtener un auto en renta gratis si cuentas con la cobertura Auto Consentido.');
		$renglon3 = utf8_decode('En caso de tener contratada la cobertura Auto Consentido y desees hacer uso de la');
		$renglon4 =utf8_decode('misma, favor de comunicarte con algunos de los siguientes proveedores para ubicar');
		$renglon5 =utf8_decode('la oficina más cercana:');
		$renglon6 =utf8_decode('EUROPCAR   01800 - 2012084 Opción 5');
		$renglon7 =utf8_decode('HERTZ      01800 - 7095000');
		$renglon9 =utf8_decode('Nota: Aplican restricciones de acuerdo a las condiciones de la cobertura.');
		$renglon10 =utf8_decode('Recuerda que:');
		$renglon11 =utf8_decode('Te garantizamos la entrega de tu auto en la fecha promesa pactada. Si no');
		$renglon12 =utf8_decode('cumplimos con ésta, puedes obtener un descuento del 20% en
		deducible por');
		$renglon13 =utf8_decode('cada día de atraso, Aplican términos y condiciones de uso.');
		$renglon15 =utf8_decode('Aplica en talleres TAC, y en donde por siniestro se determinó el pago de');
		$renglon16 =utf8_decode('Adeducible. Limitado hasta el 100% del deducible. No aplica para reparaciones');
		$renglon17 =utf8_decode('en agencias automotrices.');

		$recuerda = ' ';
		$aviso_linea1 = utf8_decode(' ');
		$aviso_linea2 = utf8_decode(' ');
		$aviso_linea3 = utf8_decode(' ');
		$aviso_linea4 = utf8_decode(' ');
		$aviso_linea5 = utf8_decode(' ');
		$aviso_linea6 = utf8_decode(' ');
		$atentamente = 'Atentamente.';
		$axa = 'AXA Seguros, S.A. de C.V.';

		//----------- Creación del documento PDF ---------------
		$pdf=new PDF();
		$pdf->AddPage();

		//----------- Comienza el cuerpo del documento ---------
		$pdf->Image('idiomas/' . $idioma . '/imagenes/encabezado-carta-axa-2.png',10,8,120);

		$pdf->SetFont('Arial','',12);
		$pdf->SetXY( 10, 35 );
		$pdf->Cell( 10, 2, $saludo);

		$pdf->SetXY( 10, 50 );
		$pdf->Cell( 10, 2, $inicio_linea1);

		$pdf->SetXY( 10, 55 );
		$pdf->Cell( 10, 2, $inicio_linea2);
	
		$pdf->SetXY( 10, 70 );
		$pdf->Cell( 10, 2, $taller);

		$pdf->SetXY( 10, 75 );
		$pdf->Cell( 10, 2, $siniestro);

		$pdf->SetXY( 10, 80 );
		$pdf->Cell( 10, 2, $poliza);

		$pdf->SetXY( 10, 85 );
		$pdf->Cell( 10, 2, $asegurado);

		$pdf->SetXY( 10, 90 );
		$pdf->Cell( 10, 2, $marca);

		$pdf->SetXY( 10, 95 );
		$pdf->Cell( 10, 2, $modelo);

		$pdf->SetXY( 10, 100 );
		$pdf->Cell( 10, 2, $tipo);

		$pdf->SetXY( 10, 105 );
		$pdf->Cell( 10, 2, $placas);

		$pdf->SetXY( 10, 110 );
		$pdf->Cell( 10, 2, $fecha_entrega);


		$pdf->SetXY( 10, 130 );
		$pdf->Cell( 10, 2, $renglon1);

		$pdf->SetXY( 10, 135 );
		$pdf->Cell( 10, 2, $renglon2);

		$pdf->SetXY( 10, 140 );
		$pdf->Cell( 10, 2, $renglon3);

		$pdf->SetXY( 10, 145 );
		$pdf->Cell( 10, 2, $renglon4);

		$pdf->SetXY( 10, 150 );
		$pdf->Cell( 10, 2, $renglon5);

		$pdf->SetXY( 17, 165);
		$pdf->Cell( 10, 2, $renglon6);

		$pdf->SetXY( 17, 170 );
		$pdf->Cell( 10, 2, $renglon7);

		$pdf->SetXY( 10, 185 );
		$pdf->Cell( 10, 2, $renglon9);

		$pdf->SetFont('Times','b',12);
		$pdf->SetXY( 10, 200 );
		$pdf->Cell( 10, 2, $renglon10);

		$pdf->SetFont('Times','b',12);
		$pdf->SetXY( 10, 205 );
		$pdf->Cell( 10, 2, $renglon11);
		
		$pdf->SetFont('Times','b',12);
		$pdf->SetXY( 10, 210 );
		$pdf->Cell( 10, 2, $renglon12);

		$pdf->SetFont('Times','b',12);
		$pdf->SetXY( 10, 215 );
		$pdf->Cell( 10, 2, $renglon13);

		$pdf->SetFont('Times','',12);
		$pdf->SetXY( 10, 230 );
		$pdf->Cell( 10, 2, $renglon15);	

		$pdf->SetXY( 10, 250 );
		$pdf->Cell( 10, 2, $atentamente);

		$pdf->SetXY( 10, 255 );
		$pdf->Cell( 10, 2, $axa);

		$pdf->Image('idiomas/' . $idioma . '/imagenes/pie-carta-axa-2.png',10,274,120);

		$nombre_pdf = $orden_id . '-carta-fecha-promesa-axa-' . time() . '.pdf';



		$nombre_y_ruta = DIR_DOCS . $nombre_pdf;

		$pdf->Output($nombre_y_ruta, 'F');

		//---------- guardamos la ruta del documento en la base de datos --------------
		$sql_data_array = array(
				'doc_nombre' => 'Carta AXA',
				'doc_usuario' => $_SESSION['usuario'],
				'doc_archivo' => $nombre_pdf,
				'orden_id' => $orden_id,
		);

		ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
		bitacora($orden_id, $sql_data_array['doc_nombre'], $dbpfx);
?>
