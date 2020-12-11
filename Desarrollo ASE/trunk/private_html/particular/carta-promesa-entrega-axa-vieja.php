<?php
		echo '		<form action="ingreso.php?accion=cartaaxa" method="post" enctype="multipart/form-data">
		<table cellpadding="0" cellspacing="0" border="0" class="mediana" width="840">
			<tr><td style="width:570px; text-align:left;"><img src="idiomas/' . $idioma . '/imagenes/encabezado-carta-axa.png" alt=""></td><td style="text-align:left; vertical-align:top;">&nbsp;</td></tr>
		</table>'."\n";
		echo '		<table cellpadding="0" cellspacing="0" border="0" class="izquierda mediana" width="840">'."\n";
		echo '			<tr><td style="width:595; text-align:justify;">';
		echo '				<p style="text-align:justify;">Estimado(a) asegurado(a):<br>
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
		Fecha compromiso de entrega: ' . date('Y-m-d', strtotime($ord['orden_fecha_promesa_de_entrega'])) . '<br>
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
		</td><td><img src="idiomas/' . $idioma . '/imagenes/lateral-carta-axa.png" alt="">
		</td></tr>
		</table>
		</form>'."\n";

		//------------- GENERAR PDF ------------------------------------

		require('fpdf.php');
		class PDF extends FPDF
		{
		

		}
		
		//--------- variables de contenido de la carta -----------------

		$saludo = 'Estimado(a) asegurado(a):';
		$inicio_linea1 = 'En  AXA  nos  comprometemos  contigo  y  te  brindamos  la  fecha  de  entrega  de  tu  ';
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
		$renglon1 = utf8_decode('Ahora que tu auto entra al taller, no olvides revisar tu carátula de póliza pues puedes');
		$renglon2 = utf8_decode('obtener un auto en renta gratis si cuentas con la cobertura Auto Consentido.');
		$recuerda = 'Recuerda que:';
		$aviso_linea1 = utf8_decode('Te  garantizamos  la  entrega  de  tu  auto  en  la  fecha  promesa pactada. Si no');
		$aviso_linea2 = utf8_decode('cumplimos  con  ésta  tenemos  una  compensación  para  ti,  llámanos  al ');
		$aviso_linea3 = utf8_decode('01 800 911 2014 para conocerla.');
		$aviso_linea4 = utf8_decode('Aplica en talleres TAC, con un máximo de 30 días de retraso. No aplica para pólizas');
		$aviso_linea5 = utf8_decode('de  flotillas ,  pólizas  a  nombre  de  personas  morales ,  ni  para  reparaciones  en ');
		$aviso_linea6 = utf8_decode('agencias automotrices.');
		$atentamente = 'Atentamente.';
		$axa = 'AXA Seguros, S.A. de C.V.';
		$tel = 'Tels. 5169 1000 | 01 800 900 1292 | axa.mx ';

		//----------- Creación del documento PDF ---------------
		$pdf=new PDF();
		$pdf->AddPage();

		//----------- Comienza el cuerpo del documento ---------
		$pdf->Image('idiomas/' . $idioma . '/imagenes/encabezado-carta-axa.png',10,8,120);
		$pdf->Image('idiomas/' . $idioma . '/imagenes/lateral-carta-axa.png' , 150 ,60, 50 , 43);

		$pdf->SetFont('Times','',12);
		$pdf->SetXY( 10, 65 );
		$pdf->Cell( 10, 2, $saludo);

		$pdf->SetXY( 10, 76 );
		$pdf->Cell( 10, 2, $inicio_linea1);

		$pdf->SetXY( 10, 81 );
		$pdf->Cell( 10, 2, $inicio_linea2);
	
		$pdf->SetXY( 10, 94 );
		$pdf->Cell( 10, 2, $taller);

		$pdf->SetXY( 10, 99 );
		$pdf->Cell( 10, 2, $siniestro);

		$pdf->SetXY( 10, 104 );
		$pdf->Cell( 10, 2, $poliza);

		$pdf->SetXY( 10, 109 );
		$pdf->Cell( 10, 2, $asegurado);

		$pdf->SetXY( 10, 114 );
		$pdf->Cell( 10, 2, $marca);

		$pdf->SetXY( 10, 119 );
		$pdf->Cell( 10, 2, $modelo);

		$pdf->SetXY( 10, 124 );
		$pdf->Cell( 10, 2, $tipo);

		$pdf->SetXY( 10, 129 );
		$pdf->Cell( 10, 2, $placas);

		$pdf->SetXY( 10, 134 );
		$pdf->Cell( 10, 2, $fecha_entrega);

	
		$pdf->SetXY( 10, 144 );
		$pdf->Cell( 10, 2, $renglon1);
	
		$pdf->SetXY( 10, 149 );
		$pdf->Cell( 10, 2, $renglon2);
	
		$pdf->SetFont('Times','b',12);
		$pdf->SetXY( 10, 164 );
		$pdf->Cell( 10, 2, $recuerda);
	
		$pdf->SetFont('Times','b',12);
		$pdf->SetXY( 10, 179 );
		$pdf->Cell( 10, 2, $aviso_linea1);
	
		$pdf->SetXY( 10, 184 );
		$pdf->Cell( 10, 2, $aviso_linea2);

		$pdf->SetXY( 10, 189 );
		$pdf->Cell( 10, 2, $aviso_linea3);
	
		$pdf->SetFont('Times','',12);
		$pdf->SetXY( 10, 204 );
		$pdf->Cell( 10, 2, $aviso_linea4);

		$pdf->SetXY( 10, 209 );
		$pdf->Cell( 10, 2, $aviso_linea5);

		$pdf->SetXY( 10, 214 );
		$pdf->Cell( 10, 2, $aviso_linea6);

		$pdf->SetXY( 10, 264 );
		$pdf->Cell( 10, 2, $atentamente);

		$pdf->SetXY( 10, 269 );
		$pdf->Cell( 10, 2, $axa);

		$pdf->SetXY( 10, 274 );
		$pdf->Cell( 10, 2, $tel);
		
		
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
