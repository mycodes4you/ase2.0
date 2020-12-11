<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}


		$preg1 = "SELECT orden_torre, orden_fecha_recepcion, orden_cliente_id FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion de orden de trabajo!" . $preg1);
		$ord = mysql_fetch_array($matr1);
		$veh = datosVehiculo($orden_id, $dbpfx);
		$preg2 = "SELECT sub_reporte, sub_aseguradora, sub_poliza, sub_deducible FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_area = '6' AND sub_estatus < '189' AND sub_siniestro = '1' LIMIT 1";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo seleccion de siniestro!" . $preg2);
		$sin = mysql_fetch_array($matr2);
		$preg3 = "SELECT aseguradora_nic FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $sin['sub_aseguradora'] . "'";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo aseguradoras!" . $preg3);
		$aseg = mysql_fetch_array($matr3);
		$preg4 = "SELECT cliente_empresa_id, cliente_nombre, cliente_apellidos, cliente_telefono1, cliente_clave, cliente_email FROM " . $dbpfx . "clientes WHERE cliente_id = '" . $ord['orden_cliente_id'] . "'";
		$matr4 = mysql_query($preg4) or die("ERROR: Fallo seleccion de cliente!" . $preg4);
		$cli = mysql_fetch_array($matr4);
		$preg5 = "SELECT empresa_calle, empresa_ext, empresa_int FROM " . $dbpfx . "empresas WHERE empresa_id = '" . $cli['cliente_empresa_id'] . "'";
		$matr5 = mysql_query($preg5) or die("ERROR: Fallo seleccion de dirección!" . $preg5);
		$emp = mysql_fetch_array($matr5);
		require('fpdf.php');
		include('parciales/phpqrcode/qrlib.php');
		include('parciales/php-barcode.php');
		$urlpub = 'www.sarsan-amores.autoshop-easy.com';
		$codigoqr = $urlpub.'/consulta.php?accion=consultar&orden_id=' . $orden_id . '&arg0=' . $cli['cliente_clave'];
		$imagenqr = DIR_DOCS.'qr-orden-' . $orden_id . '.png';
		QRcode::png($codigoqr, $imagenqr, 'L', 4, 2);

  $fontSize = 10;
  $marge    = 10;   // between barcode and hri in pixel
  $x        = 50;  // barcode center
  $y        = 80;  // barcode center
  $height   = 10;   // barcode height in 1D ; module size in 2D
  $width    = 1;    // barcode height in 1D ; not use in 2D
  $angle    = 0;   // rotation in degrees
  
  $code     = '1234567'; // barcode, of course ;)
  $type     = 'code128';
  $black    = '000000'; // color in hexa

		$pdf = new FPDF('P', 'pt', 'Letter');
		$pdf->SetMargins(0,0);
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',8);
		$pdf->SetXY(5,16);
		$data = Barcode::fpdf($pdf, $black, 65, 16, 0, $type, '1234567', 1.2, 15);
//		$pdf->Cell(0,0,'ordentorre1');
		$pdf->SetXY(76,16);
//		$pdf->Cell(0,0,'orden_torre2');
		$pdf->SetXY(147,16);
		$pdf->Cell(0,0,'orden_torre3');
		$pdf->SetXY(5,41.4);
		$pdf->Cell(0,0,'orden_torre4');
		$pdf->SetXY(76,41.4);
		$pdf->Cell(0,0,'orden_torre5');
		$pdf->SetXY(147,41.4);




		$pdf->SetXY(106,29);
		$pdf->Cell(40,10,date('d / m / Y', strtotime($ord['orden_fecha_recepcion'])));
		$pdf->SetXY(20,36);
		$pdf->Cell(40,10,$sin['sub_poliza']);
		$pdf->SetXY(27,42);
		$pdf->Cell(40,10,$aseg['aseguradora_nic']);
		$pdf->SetXY(26,48);
		$pdf->Cell(40,10,number_format($sin['sub_deducible'],2));
		$pdf->SetXY(59,48);
		$pdf->Cell(40,10,$sin['sub_reporte']);
		$pdf->SetXY(100,35);
		$pdf->Cell(40,10,utf8_decode($cli['cliente_nombre']) . ' ' . utf8_decode($cli['cliente_apellidos']));
		$pdf->SetXY(102,40);
		$pdf->Cell(40,10,utf8_decode($emp['empresa_calle']) . ' ' . utf8_decode($emp['empresa_ext']) . ' ' . utf8_decode($emp['empresa_int']));
		$pdf->SetXY(96,46);
		$pdf->Cell(40,10,$cli['cliente_telefono1']);
		$pdf->SetXY(136,46);
		$pdf->Cell(40,10,$cli['cliente_email']);
		$pdf->SetXY(20,55);
		$pdf->Cell(40,10,utf8_decode($veh['marca']));
		$pdf->SetXY(66,55);
		$pdf->Cell(40,10,utf8_decode($veh['tipo']));
		$pdf->SetXY(106,55);
		$pdf->Cell(40,10,$veh['modelo']);
		$pdf->SetXY(144,55);
		$pdf->Cell(40,10,utf8_decode($veh['subtipo']));
		$pdf->SetXY(20,61);
		$pdf->Cell(40,10,utf8_decode($veh['color']));
		$pdf->SetXY(57,61);
		$pdf->Cell(40,10,$veh['placas']);
		$pdf->Image($imagenqr,126,175,23);
		$pdf->SetFont('Arial','B',10);
		$pdf->SetXY(126,194);
		$pdf->Cell(40,10,$urlpub);
		$pdf->SetXY(126,198);
		$pdf->Cell(40,10,'Clave de Cliente: ' . $cli['cliente_clave']);
		$pdf->Output();
		