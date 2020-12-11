<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;}
include('parciales/funciones.php');

//include('../particular/config.php');
	$pago_id ='';
	$pago_referencia ='';
	$prov_nic ='';
	$pago_banco ='';
	$pago_cuenta ='';
	$pago_monto ='';
	$pago_fecha ='';
	$nombre_agencia ='';
	$excel = unserialize($excel);
//	echo $excel[pago_id];

	// -------------------   Creación de Archivo Excel   ---------------------------
	$celda = 'A1';
	$titulo = 'Pagos grupales a pedidos de: ' . $excel['nombre_agencia'];

	require_once ('Classes/PHPExcel.php');
	$objReader = PHPExcel_IOFactory::createReader('Excel5');
	$objPHPExcel = $objReader->load("parciales/export.xls");
	$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
				->setTitle("PAGOS")
				->setKeywords("AUTOSHOP EASY"); 

	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($celda, $titulo);
				//->setCellValue("A3", $fecha_export);
	// ------ ENCABEZADOS ---
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue("A4", "Pago")
				->setCellValue("B4", "Proveedor")
				->setCellValue("C4", "Pedidos")
				->setCellValue("D4", "Facturas")
				->setCellValue("E4", "Banco")
				->setCellValue("F4", "Cuenta")
				->setCellValue("G4", "Monto")
				->setCellValue("H4", "Tipo de Pago")
				->setCellValue("I4", "Referencia")
				->setCellValue("J4", "Fecha")
				->setCellValue("K4", "Usuario");
	$z = 5;

// --- Seleccionado pagos del proveedor ---
	if ($excel['rango'] == 1) {
		$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE prov_id = $excel[prov_id] AND pago_fecha BETWEEN '$excel[feini]' AND '$excel[fefin]' ORDER BY pago_fecha DESC";
	} else {
		$pregpagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE prov_id = $excel[prov_id] ORDER BY pago_fecha DESC";
	}
//echo $pregpagos . '<br>';
	$matrPagos = mysql_query($pregpagos) or die ("Error al seleccionar Pago! 52 " . $pregpagos) ;

// --- Seleccionando proveedor ---
	$proveedores = "SELECT prov_razon_social, prov_rfc FROM " . $dbpfx . "proveedores WHERE prov_id = $excel[prov_id]";
	$matrProveedores = mysql_query($proveedores);
	$esteproveedor = mysql_fetch_assoc($matrProveedores);

	while ($estepago = mysql_fetch_array($matrPagos)) {
		// --- Celdas a grabar ----
		$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
		$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z; $ke = 'K'.$z;

		$e_pago_id = $estepago['pago_id'];
		$e_pago_referencia = $estepago['pago_referencia'];
		$e_prov_razon_social = $esteproveedor['prov_razon_social'];
		$e_pago_tipo = constant("TIPO_PAGO_".$estepago['pago_tipo']);
		$e_pago_banco = $estepago['pago_banco'];
		$e_pago_monto = $estepago['pago_monto'];
		$e_pago_fecha = date('Y-m-d 00:00:01', strtotime($estepago['pago_fecha']));
		$e_pago_fecha = PHPExcel_Shared_Date::PHPToExcel( strtotime($e_pago_fecha) );
		$e_pago_usuario = $estepago['usuario'];
		$e_pago_cuenta = $estepago['pago_cuenta'];

		$pedidos = "SELECT pedido_id FROM " . $dbpfx . "pagos_facturas WHERE pago_id = $e_pago_id";
		$matrPedidos = mysql_query($pedidos) or die ("Error al selecionar pedidos");
		$cuantospedidos = mysql_num_rows($matrPedidos);
		while ($estepedido = mysql_fetch_array($matrPedidos)) {
			$pp[] = $estepedido['pedido_id'];
		}
		$lista_pedidos = implode(", ", $pp);

		$sqlUsuario = "SELECT * FROM " . $dbpfx . "usuarios WHERE usuario = '$e_pago_usuario'";
		$matrsqlU = mysql_query($sqlUsuario) or die("ERROR: Fallo selección de sql total");
		$datosU = mysql_fetch_assoc($matrsqlU);
		$nombre_usuario = $datosU['nombre'] . ' ' . $datosU['apellidos'];

		$facturas = "SELECT fact_id FROM " . $dbpfx . "pagos_facturas WHERE pago_id = $e_pago_id";
		$matrFacturas = mysql_query($facturas) or die ("Error al selecionar facturas");
		$cuantosfacturas = mysql_num_rows($matrFacturas);
		while ($estafactura = mysql_fetch_array($matrFacturas)) {
			$ppf[] = $estafactura['fact_id'];
		}
		$lista_facturas = implode(", ", $ppf);
		
		if($estepago['pago_banco'] != '' || $$estepago['pago_banco'] != 0){}else{$e_pago_banco='Efectivo';}
		//if($estepago['pago_cuenta'] != '' || $estepago['pago_cuenta'] != 0){}else{$e_pago_cuenta='Efectivo';}
		//if($estepago['pago_referencia'] != '' || $estepago['pago_referencia'] != 0){}else{$e_pago_referencia='Efectivo';}
			
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue($a, $e_pago_id)
			->setCellValue($b, $e_prov_razon_social)
			->setCellValue($c, $lista_pedidos)
			->setCellValue($d, $lista_facturas)
			->setCellValue($e, $e_pago_banco)
			->setCellValue($f, $e_pago_cuenta)
			->setCellValue($g, $e_pago_monto)
			->setCellValue($h, $e_pago_tipo)
			->setCellValue($i, $e_pago_referencia)			
			->setCellValue($j, $e_pago_fecha)													
			->setCellValue($ke, $nombre_usuario);

		$objPHPExcel->getActiveSheet()
						->getStyle($j)
						->getNumberFormat()
						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
		$z++;
		unset($lista_pedidos);
		unset($pp);
		unset($lista_facturas);
		unset($ppf);
	}

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="pagos_grupales.xls"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;