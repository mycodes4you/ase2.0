<?php
//   Script de "reporte de Facturación"
//   este script esta dividido en dos:
//   1. exportar consulta del reporte en formato hoja de calculo
//   2. consulta mostrada  en el front-html


if ($f1125130 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol13']=='1') {

	$encabezado = ' Facturas';
	$preg0 = "SELECT * FROM " . $dbpfx . "facturas_por_pagar WHERE pagada < '2' AND ";
	if($tipo_fecha < 1) {
		$preg0 .= " f_rec >= '" . $feini . "' AND f_rec <= '" . $fefin . "'";
		$encabezado .= ' recibidas del ' . $t_ini . ' al ' . $t_fin;
		$preg_orden = " ORDER BY f_rec, fact_id ASC";
	}
	elseif($tipo_fecha == 1) {
		$preg0 .= " f_prog >= '" . $feini . "' AND f_prog <= '" . $fefin . "'";
		$encabezado .= ' programadas para pago del ' . $t_ini . ' al ' . $t_fin;
		$preg_orden = " ORDER BY f_prog, fact_id ASC";
	}
	elseif($tipo_fecha == 2) {
		$preg0 .= " f_pago >= '" . $feini . "' AND f_pago <= '" . $fefin . "'";
		$encabezado .= ' pagadas del ' . $t_ini . ' al ' . $t_fin;
		$preg_orden = " ORDER BY f_pago, fact_id ASC";
	}
	else {
		$preg0 .= " f_creada >= '" . $feini . "' AND f_creada <= '" . $fefin . "'";
		$encabezado .= ' emitidas del ' . $t_ini . ' al ' . $t_fin;
		$preg_orden = " ORDER BY f_creada, fact_id ASC";
	}

	if($fmontofactura != '') {
		$preg0 .= " AND f_monto = '" . limpiarNumero($fmontofactura) . "'";
		$encabecola .= ' con monto de ' . number_format(limpiarNumero($fmontofactura),2);
	}
	if($fot != '') {
		$preg0 .= " AND orden_id = '" . $fot . "'";
		$encabecola .= ' de la OT ' . $fot;
	}
	if($ffactura != '') {
		$preg0 .= " AND fact_num LIKE '%" . $ffactura . "%'";
		$encabecola .= ' de la factura ' . $ffactura;
	}
	if($fpedido != '') {
		$preg0 .= " AND doc_int_id = '" . $fpedido . "'";
		$encabecola .= ' del pedido ' . $fpedido;
	}
	if($fproveedor > 0) {
		$preg0 .= " AND tercero_id = '" . $fproveedor . "'";
		$encabecola .= ' del proveedor ' . $provs[$fproveedor];
	}

	$encabezado = $encabezado . $encabecola;

	$preg0 .= $preg_orden;
	//echo 'Query: ' . $preg0;
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de lapso! 101 " . $preg0);
	$filas = mysql_num_rows($matr0);
	//echo $filas;

	if($export == 1) {
		// -------------------   Creación de Archivo CSV   ----------------------------------
		$titulo = 'Facturas-' . $nombre_agencia . '-' . date('Ymd', time()) . '.csv';
		$columna = ['Factura', 'Factura UUID', 'Pedido', 'OT', 'ProvID', 'Proveedor Nick', 'Fecha Creada', 'Fecha Recibida', 'Fecha Programada', 'Monto Total', 'Monto Pagado', 'Monto Pendiente', 'Fecha de Pago', 'Banco', 'Cuenta', 'Monto Global', 'Referencia', 'RPE UUID'];
		$fp = fopen('php://output', 'w');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $titulo . '"');
		header('Pragma: no-cache');
		header('Expires: 0');
		fputcsv($fp, $columna);
	} 
	else { // ---- HTML ----

		echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-12">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2>' . $filas . $encabezado . '</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 ">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
					<tr>
						<th>Factura</th>
						<th>Pedido</th>
						<th>OT</th>
						<th>Proveedor</th>
						<th>Fecha Creada</th>
						<th>Fecha Recibida</th>
						<th>Fecha Programada</th>
						<th>Monto Total</th>
						<th>Monto Pagado</th>
						<th>Monto Pendiente</th>
						<th>Fecha de Pago</th>
						<th>Banco</th>
						<th>Cuenta</th>
						<th>Monto Global</th>
						<th>Referencia</th>
						<th>RPE</th>
					</tr>
					<tr class="claro">
						<td colspan="16"><form action="reportes.php?accion=facturacion" method="post" enctype="multipart/form-data"></td>
					</tr>'."\n";
	}

	$fondo = 'obscuro';
	$totfact = 0; $totcob = 0;

	while($fact = mysql_fetch_array($matr0)) {
		unset($f_creada, $exf_creada, $f_rec, $exf_rec, $f_prog, $exf_prog, $f_pago, $exf_pago);
		// --- Obtener información de pagos --
		$porcobrar = $fact['fact_monto'] - $cobrado;

		if(strtotime($fact['f_creada']) > 1000000) {
			$f_creada = date('Y-m-d', strtotime($fact['f_creada']));
			$exf_creada = $fact['f_creada'];
		}
		if(strtotime($fact['f_rec']) > 1000000) {
			$f_rec = date('Y-m-d', strtotime($fact['f_rec']));
			$exf_rec = $fact['f_rec'];
		}
		if(strtotime($fact['f_prog']) > 1000000) {
			$f_prog = date('Y-m-d', strtotime($fact['f_prog']));
			$exf_prog = $fact['f_prog'];
		}

		if($export != 1) {
			echo '
					<tr class="' . $fondo . '">
						<td>' . $fact['fact_num'] . '</td>
						<td>' . $fact['doc_int_id'] . '</td>
						<td><a href="ordenes.php?accion=consultar&orden_id=' . $fact['orden_id'] . '">' . $fact['orden_id'] . '</a></td>
						<td style="text-align: left !important;">' . $provs[$fact['tercero_id']] . '</td>
						<td>' . $f_creada . '</td>
						<td>' . $f_rec . '</td>
						<td>' . $f_prog . '</td>
						<td style="text-align: right;">' . number_format($fact['f_monto'],2) . '</td>'."\n";
		}

		$preg1 = "SELECT p.pago_id, p.adelanto, p.monto, p.fecha, pp.pago_fecha, pp.pago_monto, pp.pago_banco, pp.pago_cuenta, pp.pago_referencia, pp.rpe_doc_id FROM " . $dbpfx . "pagos_facturas p LEFT JOIN " . $dbpfx . "pedidos_pagos pp ON p.pago_id = pp.pago_id WHERE p.fact_id = '" . $fact['fact_id'] . "' ";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de lapso! 215 " . $preg1);
		$fila1 = mysql_num_rows($matr1);
		unset($monto_pagado, $pago, $monto_pendiente, $f_pago);
		if($fila1 > 0) {
			while($pags = mysql_fetch_array($matr1)) {
				$monto_pagado = $monto_pagado + $pags['monto'];
				$pago[$pags['pago_id']] = [
					'pgfecha' => $pags['pago_fecha'],
					'pgmonto' => $pags['pago_monto'],
					'pgbanco' => $pags['pago_banco'],
					'pgcuenta' => $pags['pago_cuenta'],
					'pgreferencia' => $pags['pago_referencia'],
					'pgrpe' => $pags['rpe_doc_id'],
					'pgrpeuuid' => $pags['rpe_uuid'],
				];
				
			}
		}
		$monto_pendiente = $fact['f_monto'] - $monto_pagado;
		$totfact = $totfact + $fact['f_monto'];
		$totpag = $totpag + $monto_pagado;
		$totpp = $totpp + $monto_pendiente;

		if($export != 1) {
			echo '						<td style="text-align:right;">' . number_format($monto_pagado, 2) . '</td>
						<td style="text-align:right;">' . number_format($monto_pendiente, 2) . '</td>'."\n";
		}

		unset($lafecha, $elbanco, $elcuenta, $elmonto, $elreferencia, $elrpe, $exf_pago, $exbanco, $excuenta, $exmonto, $exreferencia, $exrpe);
		if(count($pago) > 0) {
			foreach($pago as $kpag => $vpag) {
				if($lafecha != '') { $lafecha .= '<br>'; $exf_pago .= ' ';}
				if(strtotime($vpag['pgfecha']) > 1000000) {
					$lafecha .= date('Y-m-d', strtotime($vpag['pgfecha']));
				}
				$exf_pago .= $vpag['pgfecha'];
				if($elbanco != '') { $elbanco .= '<br>'; $exbanco .= ' ';}
				$elbanco .= $vpag['pgbanco'];
				$exbanco .= $vpag['pgbanco'];
				if($elcuenta != '') { $elcuenta .= '<br>'; $excuenta .= ' '; }
				$elcuenta .= $vpag['pgcuenta'];
				$excuenta .= $vpag['pgcuenta'];
				if($elmonto != '') { $elmonto .= '<br>'; $exmonto .= ' '; }
				$elmonto .= number_format($vpag['pgmonto'],2);
				$exmonto .= $vpag['pgmonto'];
				if($elreferencia != '') { $elreferencia .= '<br>'; $exreferencia .= ' '; }
				$elreferencia .= $vpag['pgreferencia'];
				$exreferencia .= $vpag['pgreferencia'];
				if($elrpe != '') { $elrpe .= '<br>'; $exrpe .= ' '; }
				$elrpe .= $vpag['pgrpe'];
				$exrpe .= $exrpe;
			}
			if($export != 1) {
				echo '						<td>' . $lafecha . '</td>
						<td>' . $elbanco . '</td>
						<td>' . $elcuenta . '</td>
						<td>' . $elmonto . '</td>
						<td>' . $elreferencia . '</td>
						<td>' . $elrpe . '</td>'."\n";
			}
		}
		else {
			if($export != 1) {
				echo '						<td></td><td></td><td></td><td></td><td></td><td></td>'."\n";
			}
		}
		if($fondo == 'claro') { $fondo= 'obscuro';} else { $fondo = 'claro'; }

		if($export == 1) {
			$campos = [$fact['fact_num'], $fact['f_uuid'], $fact['doc_int_id'], $fact['orden_id'], $fact['tercero_id'], $provs[$fact['tercero_id']], $exf_creada, $exf_rec, $exf_prog, $fact['f_monto'], $monto_pagado, $monto_pendiente, $exf_pago, $exbanco, $excuenta, $exmonto, $exreferencia, $exrpe];
			fputcsv($fp, array_values($campos));
		}
	}


	if($export != 1) {
		echo '
					<tr>
						<td colspan="7" style="text-align:right;">
							<big><b>Totales</b></big>
						</td>
						<td style="text-align:right;">
							<big><b>$' . number_format($totfact, 2) . '</b></big>
							</td>
						<td style="text-align:right;">
							<big><b>$' . number_format($totpag, 2) . '</b></big>
						</td>
						<td style="text-align:right;">
							<big><b>$' . number_format($totpp, 2) . '</b></big>
						</td>
						<td colspan="2"></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>'."\n";

	} else {
		exit;
	}

} else{
	echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
}
