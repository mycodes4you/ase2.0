<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');


// echo "<script language=\\"javascript\\">window.open(\\"http://www.example.com\\",\\"_blank\\");</script>";

		include('parciales/phpqrcode/qrlib.php');
		$urlpub = 'www.sarsan-amores.autoshop-easy.com';
		$orden_id = 52;
		$cust['cliente_clave'] = 'YYYttt34';
		$codigoqr = $urlpub.'/consulta.php?accion=consultar&orden_id=' . $orden_id . '&arg0=' . $cust['cliente_clave'];
		$imagenqr = '../documentos/qr-orden-' . $orden_id . '.png';
		QRcode::png($codigoqr, $imagenqr, 'L', 4, 2);
		$preg5 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_area = '6' AND sub_estatus < '189' AND sub_siniestro = '1' LIMIT 1";
		$matr5 = mysql_query($preg5) or die("ERROR: Fallo seleccion de aseguradora!");
		$aseg = mysql_fetch_array($matr5);
		require('fpdf.php');

		$pdf = new FPDF('P', 'mm', 'Letter');
		$pdf->AddPage();
		$pdf->SetFont('Times','',8);
		$pdf->SetXY(30,31);
		$pdf->Cell(40,10,'TORRE');
		$pdf->SetXY(65,31);
		$pdf->Cell(40,10,'COLOR');
		$pdf->SetXY(112,30);
		$pdf->Cell(40,10,date('Y/m/d'));
		$pdf->SetXY(26,37);
		$pdf->Cell(40,10,'POLIZAPOLIZA');
		$pdf->SetXY(33,43);
		$pdf->Cell(40,10,'ASEGURADORA');
		$pdf->SetXY(32,49);
		$pdf->Cell(40,10,'DEDUCIBLE');
		$pdf->SetXY(65,49);
		$pdf->Cell(40,10,'SINIESTRO');
		$pdf->SetXY(106,36);
		$pdf->Cell(40,10,'NOMBRE Y APELLIDOPAT APELLIDOMAT');
		$pdf->SetXY(108,41);
		$pdf->Cell(40,10,'DOMICILIO DEL CLIENTE CALLE Y NUMERO');
		$pdf->SetXY(102,47);
		$pdf->Cell(40,10,'TELEFONO 000');
		$pdf->SetXY(142,47);
		$pdf->Cell(40,10,'CORREO@ELECTRONICO.COM');
		$pdf->SetXY(26,56);
		$pdf->Cell(40,10,'MARCA MARCA');
		$pdf->SetXY(72,56);
		$pdf->Cell(40,10,'TIPO TIPO');
		$pdf->SetXY(112,56);
		$pdf->Cell(40,10,'MODELO MODELO');
		$pdf->SetXY(150,56);
		$pdf->Cell(40,10,'VERSION');
		$pdf->SetXY(26,62);
		$pdf->Cell(40,10,'COLOR');
		$pdf->SetXY(63,62);
		$pdf->Cell(40,10,'PLACAS');
		$pdf->Image($imagenqr,132,176,23);
		$pdf->SetFont('Times','',10);
		$pdf->SetXY(132,195);
		$pdf->Cell(40,10,$urlpub);
		$pdf->SetXY(132,198);
		$pdf->Cell(40,10,'Clave de Cliente: ');
		$pdf->Output();
		