<?php
/*************************************************************************************
*   Script de "reporte cobros y pagos"
*   este script esta dividido en dos:
*   1. exportar consulta del reporte en formato hoja de calculo
*   2. consulta mostrada  en el front-html
*
**************************************************************************************/



if ($f1125115 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1') {

	if($flujoflt != '2' && $provflt == '') {
		$preg1 = "SELECT fc.cliente_id, fc.aseguradora_id, fc.fact_num, cf.monto, cf.orden_id, cf.usuario, cf.fact_id, c.cobro_id, c.cobro_banco, c.cobro_fecha, c.cobro_cuenta, c.cobro_metodo, c.cobro_tipo, c.cobro_referencia, c.rpe_id FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c, " . $dbpfx . "facturas_por_cobrar fc WHERE c.cobro_fecha >= '" . $feini . "' AND c.cobro_fecha <= '" . $fefin . "' AND cf.fact_id = fc.fact_id AND c.cobro_id = cf.cobro_id";
		
		//$preg1 = "SELECT c.*, f.*, p.* FROM " . $dbpfx . "cobros c, " . $dbpfx . "facturas_por_cobrar f, " . $dbpfx . "cobros_facturas p WHERE c.cobro_fecha >= '" . $feini . "' AND c.cobro_fecha <= '" . $fefin . "' AND p.fact_id = f.fact_id AND c.cobro_id = p.cobro_id";

		$preg4 = "SELECT cf.monto, cf.orden_id, cf.usuario, c.cobro_id, c.cobro_banco, c.cobro_cuenta, c.cobro_metodo, c.cobro_tipo, c.cobro_referencia, c.cobro_fecha, cf.aseguradora_id FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE c.cobro_fecha >= '" . $feini . "' AND c.cobro_fecha <= '" . $fefin . "' AND c.cobro_id = cf.cobro_id AND (cf.fact_id IS NULL OR cf.fact_id = '0')";
		
		//$preg4 = "SELECT c.*, p.* FROM " . $dbpfx . "cobros c, " . $dbpfx . "cobros_facturas p WHERE c.cobro_fecha >= '" . $feini . "' AND c.cobro_fecha <= '" . $fefin . "' AND p.fact_id < '1' AND c.cobro_id = p.cobro_id";

		$preg5 = "SELECT a.*, cf.fecha, fc.aseguradora_id FROM " . $dbpfx . "ajusadmin a, " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "facturas_por_cobrar fc WHERE a.fecha_ajuste >= '" . $feini . "' AND a.fecha_ajuste <= '" . $fefin . "' AND cf.fact_id = a.fact_id AND fc.fact_id = a.fact_id";

		if($asegflt === '0' || $asegflt > '0') {
			$preg1 .= " AND fc.aseguradora_id = '" . $asegflt . "'";
			$preg5 .= " AND fc.aseguradora_id = '" . $asegflt . "'";
		}

		$matrcob = mysql_query($preg1) or die("ERROR: Fallo selección de cobros! " . $preg1);
		$filacob = mysql_num_rows($matrcob);

		$matrantcob = mysql_query($preg4) or die("ERROR: Fallo selección de cobros! " . $preg4);
		$filaantcob = mysql_num_rows($matrantcob);

		$matrajus = mysql_query($preg5) or die("ERROR: Fallo selección de ajustes administrativos! " . $preg5);
		$filaajus = mysql_num_rows($matrajus);
	}

	if($flujoflt != '1' && $asegflt == '') {
//		$preg2 = "SELECT pp.*, f.*, p.prov_id FROM " . $dbpfx . "pedidos_pagos pp, " . $dbpfx . "pedidos p," . $dbpfx . "facturas_por_pagar f WHERE pp.pago_fecha >= '" . $feini . "' AND pp.pago_fecha <= '" . $fefin . "' AND pp.fact_id = f.fact_id AND p.pedido_id = pp.pedido_id";

		$preg2 = "SELECT pf.pago_id, pf.pedido_id, pf.monto, pf.proveedor_id, pf.fact_id, pf.usuario, f.fact_num, f.orden_id, pp.pago_referencia, pp.pago_tipo, pp.pago_fecha, pp.pago_banco, pp.pago_cuenta FROM " . $dbpfx . "pagos_facturas pf, " . $dbpfx . "pedidos_pagos pp," . $dbpfx . "facturas_por_pagar f WHERE pp.pago_fecha >= '" . $feini . "' AND pp.pago_fecha <= '" . $fefin . "' AND pf.fact_id = f.fact_id AND pf.pago_id = pp.pago_id";

//		$preg3 = "SELECT pp.*, p.prov_id FROM " . $dbpfx . "pedidos_pagos pp, " . $dbpfx . "pedidos p WHERE pp.pago_fecha >= '" . $feini . "' AND pp.pago_fecha <= '" . $fefin . "' AND p.pedido_id = pp.pedido_id AND pp.fact_id < '1'";

		$preg3 = "SELECT pf.*, pp.pago_referencia FROM " . $dbpfx . "pagos_facturas pf, " . $dbpfx . "pedidos_pagos pp  WHERE pp.pago_fecha >= '" . $feini . "' AND pp.pago_fecha <= '" . $fefin . "' AND pp.pago_id = pf.pago_id AND pf.fact_id < '1'";

		$preg6 = "SELECT dp.* FROM " . $dbpfx . "destajos_pagos dp WHERE dp.pago_fecha >= '" . $feini . "' AND dp.pago_fecha <= '" . $fefin . "' AND descuento IS NULL ";

		if($provflt > '0') { $preg2 .= " AND pf.proveedor_id = '" . $provflt . "'"; $preg3 .= " AND pf.proveedor_id = '" . $provflt . "'"; $preg6 .= " AND dp.usuario_pago_recibido = '" . $provflt . "'"; }

		$matrpag = mysql_query($preg2) or die("ERROR: Fallo selección de pagos a proveedores! " . $preg2);
		$filapag = mysql_num_rows($matrpag);

		$matrantpag = mysql_query($preg3) or die("ERROR: Fallo selección de pagos anticipados a proveedores! " . $preg3);
		$filaantpag = mysql_num_rows($matrantpag);

		$matrdestpag = mysql_query($preg6) or die("ERROR: Fallo selección de pagos de destajos! " . $preg6);
		$filadestpag = mysql_num_rows($matrdestpag);

	}

	//echo 'resultados= ' . $filacob . '<br>';
	//echo 'resultados= ' . $filapag . '<br>';

	// ------ Función ordenar por fecha
	function ordenar( $a, $b ) {
		return strtotime($a['fecha']) - strtotime($b['fecha']);
	}

	$padre = array();

	// ------ Definimos los tipos de documento
	$tipo_doc = [
	1 => "Factura",
	2 => "Remisión",
	3 => "Deducible",
	4 => "Nota de Crédito",
	5 => "Anticipo"
	];

	$sum_cobros = 0; $sum_ajuscob = 0;

// ------ Guardamos consulta de cobros en un arreglo
	while($cobros = mysql_fetch_array($matrcob)){
		if($export == 1) { // ---- Hoja de calculo ----
			$factnum = $tipo_doc[$cobros['cobro_tipo']] . ' ' . $cobros['fact_num'];
			$ordeti = $cobros['orden_id'];
		} else { // ---- HTML ----
			$factnum = $tipo_doc[$cobros['cobro_tipo']] . ' <a href="entrega.php?accion=cobros&orden_id=' . $cobros['orden_id'] . '" target="_blank">' . $cobros['fact_num'] . '</a>';
			$ordeti = '<a href="ordenes.php?accion=consultar&orden_id=' . $cobros['orden_id'] . '" target="_blank"> ' . $cobros['orden_id'] . '</a>';
		}

		$nombre_cuenta = $cuent[$cobros['cobro_cuenta']][0] . ' ' . $cuent[$cobros['cobro_cuenta']][1];
		if($cobros['cobro_tipo'] == '4') {
			$cobros['monto'] = $cobros['monto'] * -1;
			if($export == 1) { // ---- Hoja de calculo ----
				$monto = $cobros['monto'];
			} else { // ---- HTML ----
				$monto = '<span style="color:red;">' . number_format($cobros['monto'], 2) . '</span>';
			}
			$sum_ajuscob = $sum_ajuscob + $cobros['monto'];
			$cobros['monto'] = 0;
		} else {
			if($export == 1) { // ---- Hoja de calculo ----
				$monto = $cobros['monto'];
			} else { // ---- HTML ----
				$monto = number_format($cobros['monto'], 2);
			}
		}
		$sum_cobros = $sum_cobros + $cobros['monto'];

		if($cobros['aseguradora_id'] == ''){
			$cobros['aseguradora_id'] = 0;
		}

		if($export == 1) { // ---- Hoja de calculo ----
			$cobro_referencia = 'Cobro: ' . $cobros['cobro_id'] . ' - ' . $cobros['cobro_referencia'];
		} else { // ---- HTML ----

			$cobro_referencia = 'Cobro: <STRONG>' . $cobros['cobro_id'] . '</STRONG> - ' . $cobros['cobro_referencia'];
		}

		$rpe = '';
		if($cobros['rpe_id'] != ''){ // --- Buscar el uuid ---
			$preg_rpe = " SELECT fact_serie, fact_uuid, fact_num FROM " . $dbpfx . "facturas WHERE fact_id = '" . $cobros['rpe_id'] . "'";
			$matr_rpe = mysql_query($preg_rpe) or die("Falló selección de RPE! " . $preg_rpe);
			$info_rpe = mysql_fetch_assoc($matr_rpe);

			if($export == 1) { // ---- Hoja de calculo ----
				$rpe = $info_rpe['fact_serie'] . $info_rpe['fact_num'];
			} else { // ---- HTML ----
				$rpe = '
				<a href="documentos/' . $info_rpe['fact_serie'] . $info_rpe['fact_num'] . '-' . $info_rpe['fact_uuid'] . '.pdf">
					' . $info_rpe['fact_serie'] . $info_rpe['fact_num'] . '
				</a>';
			}

		}

		$padre[] = array(
			'orden_id' => $ordeti,
			'aseguradora_id' => $ase[$cobros['aseguradora_id']][1],
			'fecha' => $cobros['cobro_fecha'],
			'cobro_monto' => $monto,
			'pago_monto' => '',
			'cobro_cuenta' => $nombre_cuenta,
			'cobro_tipo' => constant('TIPO_PAGO_' . $cobros['cobro_metodo']),
			'fac_num' => $factnum,
			'cobro_referencia' => $cobro_referencia,
			'usuario' => $usuario[$cobros['usuario']],
			'rpe' => $rpe,
		);
	}

	while($cobant = mysql_fetch_array($matrantcob)){
		if($export == 1) { // ---- Hoja de calculo ----
			$factnum = 'Anticipo';
			$ordeti = $cobant['orden_id'];	
			$cobro_referencia = 'Cobro: ' . $cobant['cobro_id'] . ' - ' .  $cobant['cobro_referencia'];
			$monto = $cobant['monto'];
		} else { // ---- HTML ----
			$factnum = '<a href="entrega.php?accion=cobros&orden_id=' . $cobant['orden_id'] . '" target="_blank">Anticipo</a>';
			$ordeti = '<a href="ordenes.php?accion=consultar&orden_id=' . $cobant['orden_id'] . '" target="_blank"> ' . $cobant['orden_id'] . '</a>';
			$cobro_referencia = 'Cobro: <STRONG>' . $cobant['cobro_id'] . '</STRONG> - ' .  $cobant['cobro_referencia'];
			$monto = number_format($cobant['monto'], 2);
		}

		$nombre_cuenta = $cuent[$cobant['cobro_cuenta']][0] . ' ' . $cuent[$cobant['cobro_cuenta']][1];
		
		if($cobant['aseguradora_id'] != ''){
			$aseg = $ase[$cobant['aseguradora_id']][1];
		} else{
			$aseg = $ase[0][1];
		}
		
		$sum_cobros = $sum_cobros + $cobant['monto'];
		$padre[] = array(
			'orden_id' => $ordeti,
			'aseguradora_id' => $aseg,
			'fecha' => $cobant['cobro_fecha'],
			'cobro_monto' => $monto,
			'pago_monto' => '',
			'cobro_cuenta' => $nombre_cuenta,
			'cobro_tipo' => constant('TIPO_PAGO_' . $cobant['cobro_metodo']),
			'fac_num' => $factnum,
			'cobro_referencia' => $cobro_referencia,
			'usuario' => $usuario[$cobant['usuario']],
			'rpe' => '',
		);
	}

	while($ajustes = mysql_fetch_array($matrajus)){
		if($export == 1) { // ---- Hoja de calculo ----
			$factnum = 'Ajuste Administrativo';
			$ordeti = $ajustes['orden_id'];
			$monto = $ajustes['monto'];
		} else { // ---- HTML ----
			$factnum = '<a href="entrega.php?accion=cobros&orden_id=' . $ajustes['orden_id'] . '" target="_blank">Ajuste Administrativo</a>';
			$ordeti = '<a href="ordenes.php?accion=consultar&orden_id=' . $ajustes['orden_id'] . '" target="_blank"> ' . $ajustes['orden_id'] . '</a>';
			$monto = '<span style="color:red;">' . number_format($ajustes['monto'], 2) . '</span>';
		}

		$nombre_cuenta = 'N/A';

		$sum_ajuscob = $sum_ajuscob + $ajustes['monto'];
		$padre[] = array(
			'orden_id' => $ordeti,
			'aseguradora_id' => $ase[$ajustes['aseguradora_id']][1],
			'fecha' => $ajustes['fecha_ajuste'],
			'cobro_monto' => $monto,
			'pago_monto' => '',
			'cobro_cuenta' => $nombre_cuenta,
			'cobro_tipo' => 'N/A',
			'fac_num' => $factnum,
			'cobro_referencia' => $ajustes['motivo'],
			'usuario' => $usuario[$ajustes['usuario']],
			'rpe' => '',
		);
	}

	$sum_pagos = 0; $sum_ajuspag = 0;

// ------ Continuamos guardando en el arreglo, ahora los pagos
	while($pagos = mysql_fetch_array($matrpag)){
		if($export == 1) { // ---- Hoja de calculo ----
			$factnum = $tipo_doc[$pagos['tipo']] . ' ' . $pagos['fact_num'];
			$cobro_referencia = 'Pago: ' . $pagos['pago_id'] . ' - ' . $pagos['pago_referencia'];
		} else { // ---- HTML ----
			$factnum = $tipo_doc[$pagos['tipo']] . ' <a href="pedidos.php?accion=consultar&pedido=' . $pagos['pedido_id'] . '" target="_blank">' . $pagos['fact_num'] . '</a>';
			$cobro_referencia = 'Pago: <STRONG>' . $pagos['pago_id'] . '</STRONG> - ' . $pagos['pago_referencia'];
		}

		if($pagos['orden_id'] == '999999997') {
			$ordeti = 'Bodega'; 
		} else {
			if($export == 1) { // ---- Hoja de calculo ----
				$ordeti = $pagos['orden_id'];
			} else { // ---- HTML ----
				$ordeti = '<a href="ordenes.php?accion=consultar&orden_id=' . $pagos['orden_id'] . '" target="_blank"> ' . $pagos['orden_id'] . '</a>';
			}
		}

		if($pagos['pago_tipo'] == '4') {
			if($export == 1) { // ---- Hoja de calculo ----
				$monto = ($pagos['monto'] * -1);
			} else { // ---- HTML ----
				$monto = '<span style="color:red;">' . number_format($pagos['monto'], 2) . '</span>';
			}
			$sum_ajuspag = $sum_ajuspag + $pagos['monto'];
			$pagos['monto'] = 0;
		} else {
			if($export == 1) { // ---- Hoja de calculo ----
				$monto = $pagos['monto'];
			} else { // ---- HTML ----
				$monto = number_format($pagos['monto'], 2);
			}
		}

		$sum_pagos = $sum_pagos + $pagos['monto'];
		$padre[] = array(
			'orden_id' => $ordeti,
			'aseguradora_id' => $provs[$pagos['proveedor_id']],
			'fecha' => $pagos['pago_fecha'],
			'cobro_monto' => '',
			'pago_monto' => $monto,
			'cobro_cuenta' => $pagos['pago_banco'] . ' ' . $pagos['pago_cuenta'],
			'cobro_tipo' => constant('TIPO_PAGO_' . $pagos['pago_tipo']),
			'fac_num' => $factnum,
			'cobro_referencia' => $cobro_referencia,
			'usuario' => $usuario[$pagos['usuario']],
			'rpe' => '',
		);
	}

	while($pagant = mysql_fetch_array($matrantpag)) {
		if($export == 1) { // ---- Hoja de calculo ----
			$factnum = 'Anticipo';
			$cobro_referencia = 'Pago: ' . $pagant['pago_id'] . ' - ' . $pagant['pago_referencia'];
		} else { // ---- HTML ----
			$factnum = '<a href="pedidos.php?accion=consultar&pedido=' . $pagant['pedido_id'] . '" target="_blank">Anticipo</a>';
			$cobro_referencia = 'Pago: <STRONG>' . $pagant['pago_id'] . '</STRONG> - ' . $pagant['pago_referencia'];
		}

		if($pagant['orden_id'] == '999999997') {
			$ordeti = 'Bodega'; 
		} else {
			if($export == 1) { // ---- Hoja de calculo ----
				$ordeti = $pagant['orden_id'];
			} else { // ---- HTML ----
				$ordeti = '<a href="ordenes.php?accion=consultar&orden_id=' . $pagant['orden_id'] . '" target="_blank"> ' . $pagant['orden_id'] . '</a>';
			}
		}

		if($pagant['pago_tipo'] == '4') {
			if($export == 1) { // ---- Hoja de calculo ----
				$monto = ($pagant['pago_monto'] * -1);
			} else { // ---- HTML ----
				$monto = '<span style="color:red;">' . number_format($pagant['pago_monto'], 2) . '</span>';
			}
			$pagant['pago_monto'] = 0;
		} else {
			if($export == 1) { // ---- Hoja de calculo ----
				$monto = $pagant['pago_monto'];
			} else { // ---- HTML ----
				$monto = number_format($pagant['pago_monto'], 2);
			}
		}

		$sum_pagos = $sum_pagos + $pagant['pago_monto'];
		$padre[] = array(
			'orden_id' => $ordeti,
			'aseguradora_id' => $provs[$pagant['prov_id']],
			'fecha' => $pagant['pago_fecha'],
			'cobro_monto' => '',
			'pago_monto' => $monto,
			'cobro_cuenta' => $pagant['pago_banco'] . ' ' . $pagant['pago_cuenta'],
			'cobro_tipo' => constant('TIPO_PAGO_' . $pagant['pago_tipo']),
			'fac_num' => $factnum,
			'cobro_referencia' => $cobro_referencia,
			'usuario' => $usuario[$pagant['usuario']],
			'rpe' => '',
		);
	}

// ------ Agregamos los pagos a Operadores ----------
	while($dest = mysql_fetch_array($matrdestpag)) {
		if($export == 1) { // ---- Hoja de calculo ----
			if($dest['recibo_id'] > 0) {
				$factnum = 'Recibo ' . $dest['recibo_id'];
			} else {
				$factnum = 'Pago No Asignado';
			}
			$cobro_referencia = 'Pago: ' . $dest['pago_id'] . ' - ' . $dest['pago_referencia'];
			$ordeti = 'N/A';
			$monto = $dest['pago_monto'];
		} else { // ---- HTML ----
			if($dest['recibo_id'] > 0) {
				$factnum = '<a href="recibosrh.php?accion=consultar&recibo_id=' . $dest['recibo_id'] . '" target="_blank">Recibo ' . $dest['recibo_id'] . '</a>';
			} else {
				$factnum = '<a href="recibosrh.php?accion=consulta_pag_desc&operador=' . $dest['usuario_pago_recibido'] . '&pago_id=' . $dest['pago_id'] . '" target="_blank">Pago No Asignado</a>';
			}
			$cobro_referencia = 'Pago: <STRONG>' . $dest['pago_id'] . '</STRONG> - ' . $dest['pago_referencia'];
			$ordeti = 'N/A';
			$monto = number_format($dest['pago_monto'], 2);
		}

		$sum_pagos = $sum_pagos + $dest['pago_monto'];
		$padre[] = array(
			'orden_id' => $ordeti,
			'aseguradora_id' => $usuario[$dest['usuario_pago_recibido']],
			'fecha' => $dest['pago_fecha'],
			'cobro_monto' => '',
			'pago_monto' => $monto,
			'cobro_cuenta' => $dest['pago_banco'] . ' ' . $dest['pago_cuenta'],
			'cobro_tipo' => constant('TIPO_PAGO_' . $dest['pago_tipo']),
			'fac_num' => $factnum,
			'cobro_referencia' => $cobro_referencia,
			'usuario' => $usuario[$dest['usuario']],
			'rpe' => '',
		);
	}

// ------ Ordenamos el arreglo por fecha con la función "ordenar"
	usort($padre, 'ordenar');
	//print_r($padre);

// ------ Definimos encabezado de la tabla
	$encabezado = "Reporte de flujo de cobros y pagos del " . date('Y-m-d', strtotime($feini)) . " al " . date('Y-m-d', strtotime($fefin));

// -------------------   Creación de Archivo Excel   --------------------
	if($export == 1) { // ---- Hoja de calculo ----
		$fecha_export = date('Y-m-d H:i:s');
		$celda = 'A1';
		$titulo = 'REPORTE DE COBROS Y PAGOS: ' . $nombre_agencia;

		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("REPORTE DE COBROS Y PAGOS")
					->setKeywords("AUTOSHOP EASY");

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($celda, $titulo)
					->setCellValue("A3", $fecha_export);

// ------ ENCABEZADOS ---
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A4", "OT")
					->setCellValue("B4", "Cliente o Proveedor")
					->setCellValue("C4", "Monto Cobrado a Clientes")
					->setCellValue("D4", "Monto Pagado a Proveedores")
					->setCellValue("E4", "Cuenta Afectada")
					->setCellValue("F4", "Fecha de Movimento")
					->setCellValue("G4", "Método del Cobro o Pago")
					->setCellValue("H4", "Documento")
					->setCellValue("I4", "Referencia")
					->setCellValue("J4", "RPE")
					->setCellValue("K4", "Usuario que Cobró o Pagó");
		$z= 5;
	} else { // ---- HTML ----

		// ------ Front del Rporte ------
		echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-12">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2>' . $encabezado . '</h2>
				</div>
			</div>
		</div>
	</div>		
	<div class="row">
		<div class="col-md-12 ">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
					<tr>
						<td class="area6" style="text-align: center;"><big><b>OT<b></big></td>
						<td class="area7" style="text-align: center; width: 15%;"><big><b>Cliente o Proveedor<b></big></td>
						<td class="area6" style="text-align: center;"><big><b>Monto Cobrado a Clientes<b></big></td>
						<td class="area7" style="text-align: center;"><big><b>Monto Pagado a Proveedores<b></big></td>
						<td class="area6" style="text-align: center; width: 10%;"><big><b>Cuenta Afectada<b></big></td>
						<td class="area7" style="text-align: center;"><big><b>Fecha de Movimento<b></big></td>
						<td class="area6" style="text-align: center; width: 10%;"><big><b>Método del Cobro o Pago<b></big></td>
						<td class="area7" style="text-align: center;"><big><b>Documento<b></big></td>
						<td class="area6" style="text-align: center; width: 12%;"><big><b>Referencia<b></big></td>
						<td class="area6" style="text-align: center; width: 12%;"><big><b>RPE<b></big></td>
						<td class="area7" style="text-align: center; width: 15%;"><big><b>Usuario que Cobró o Pagó<b></big></td>
					</tr>'."\n";
		$fondo = 'claro';
	}

	// ------ Recorrer el arreglo padre
	foreach($padre as $k => $v){
		if($export == 1) { // ---- Hoja de calculo ----

// ------ Celdas a grabar ----
			$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
			$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $jota = 'J'.$z;
			$kkk = 'K'.$z;

// ------ Cambiar el formato de la celda tipo fecha/date ---
			$fecha_a = date('Y-m-d', strtotime($v['fecha']));
			$fecha_a = PHPExcel_Shared_Date::PHPToExcel( strtotime($fecha_a) );
			$objPHPExcel->getActiveSheet()
    					->getStyle($f)
    					->getNumberFormat()
    					->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);

			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($a, $v['orden_id'])
						->setCellValue($b, $v['aseguradora_id'])
						->setCellValue($c, $v['cobro_monto'])
						->setCellValue($d, $v['pago_monto'])
						->setCellValue($e, $v['cobro_cuenta'])
						->setCellValue($f, $fecha_a)
						->setCellValue($g, $v['cobro_tipo'])
						->setCellValue($h, $v['fac_num'])
						->setCellValue($i, $v['cobro_referencia'])
						->setCellValue($jota, $v['rpe'])
						->setCellValue($kkk, $v['usuario']);
			$z++;
		} else { // ---- HTML ----
			echo '
					<tr class="' . $fondo . '">
						<td style="text-align: center !important;">
							<big>' . $v['orden_id'] . '</big>
						</td>
						<td style="text-align: left !important;">
							<big>' . $v['aseguradora_id'] . '</big>
						</td>
						<td style="text-align: right !important;">
							<big>' . $v['cobro_monto'] . '</big>
						</td>
						<td style="text-align: right !important;">
							<big>' . $v['pago_monto'] . '</big>
						</td>
						<td>
							<big>' . $v['cobro_cuenta'] . '</big>
						</td>
						<td style="text-align: center !important ;">
							<big>' . date('Y-m-d', strtotime($v['fecha'])) . '</big>
						</td>
						<td>
							<big>' . $v['cobro_tipo'] . '</big>
						</td>
						<td style="text-align: center !important;">
							<big>' . $v['fac_num'] . '</big>
						</td>
						<td style="text-align: left !important;">
							<big>' . $v['cobro_referencia'] . '</big>
						</td>
						<td style="text-align: left !important;">
							<big>' . $v['rpe'] . '</big>
						</td>
						<td style="text-align: left !important;">
							<big>' . $v['usuario'] . '</big>
						</td>
					</tr>'."\n";

// ------ Cambiamos el fondo del renglón
			if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
	}

	if($export == 1) { // ---- Hoja de calculo ----

// ------ Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="cobros-pagos.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	} else { // ---- HTML ----

// ------ Calcular el promedio de pagos y cobros
		$promedio_cob = $sum_cobros / $filacob;
		$promedio_pag = $sum_pagos / $filapag;
		echo '
					<tr class="' . $fondo . '">
						<td colspan="2" style="text-align: right;">
							<big><b>Total:<b></big>
						</td>
						<td style="text-align: right;">
							<big><b>' . number_format($sum_cobros, 2) . '<b></big>
						</td>
						<td style="text-align: right;">
							<big><b>' . number_format($sum_pagos, 2) . '<b></big>
						</td>
						<td colspan="7">
					</tr>	
					<tr class="' . $fondo . '">
						<td colspan="2" style="text-align: right;">
							<big><b>Monto promedio por movimiento:<b></big>
						</td>
						<td style="text-align: right;">
							<big><b>' . number_format($promedio_cob, 2) . '<b></big>
						</td>
						<td style="text-align: right;">
							<big><b>' . number_format($promedio_pag, 2) . '<b></big>
						</td>
						<td colspan="7"></td>
					</tr>
					<tr class="' . $fondo . '">
						<td colspan="2" style="text-align: right;">
							<big><b>Total Descuentos y NC:<b></big>
						</td>
						<td style="text-align: right;">
							<big><b>' . number_format($sum_ajuscob, 2) . '<b></big>
						</td>
						<td></td>
						<td colspan="7"></td>
					</tr>			
					<tr class="' . $fondo . '">
						<td colspan="2" style="text-align: right;">
							<big><b>Total Acreditaciones:<b></big>
						</td>
						<td></td>
						<td style="text-align: right;">
							<big><b>' . number_format($sum_ajupag, 2) . '<b></big>
						</td>
						<td colspan="7"></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>'."\n";
	}
	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
?>