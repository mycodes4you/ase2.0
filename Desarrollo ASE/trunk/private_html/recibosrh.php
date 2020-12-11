<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';

include('parciales/funciones.php');
include('idiomas/' . $idioma . '/recibosrh.php');
if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

/*  ----------------  obtener nombres de usuarios   ------------------- */
	
		$consulta = "SELECT usuario, nombre, apellidos, comision FROM " . $dbpfx . "usuarios WHERE acceso = '0' ORDER BY nombre,apellidos";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo selección de usuarios!");
//		$num_provs = mysql_num_rows($arreglo);
   		$usu = array();
//   	$provs[0] = 'Sin Proveedor';
		while ($usua = mysql_fetch_array($arreglo)) {
			$usu[$usua['usuario']] = array('nom' => $usua['nombre'], 'ape' => $usua['apellidos'], 'com' => $usua['comision']);
		}
//		print_r($provs);

/*  ----------------  nombres de aseguradoras   ------------------- */
		$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_nic, prov_def FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		while ($aseg = mysql_fetch_array($arreglo)) {
			define('ASEGURADORA_' . $aseg['aseguradora_id'], $aseg['aseguradora_logo']);
			define('ASEGURADORA_NIC_' . $aseg['aseguradora_id'], $aseg['aseguradora_nic']);
			$autosurt[$aseg['aseguradora_id']] = $aseg['autosurtido'];
			$prov_def[$aseg['aseguradora_id']] = $aseg['prov_def'];
		}


if (($accion==='consultar') || ($accion==='pagar') || ($accion==='procesapago') || ($accion==='listar') || ($accion==='inspcpaq') || ($accion==='actpcpaq') || ($accion==='descuentos')) {
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}

if($accion === 'listar') {

	if($anticipo != '') {
		redirigir('recibosrh.php?accion=pagar&operador=' . $operador . '&feini=' . $feini . '&fefin=' . $fefin);
	}
	if($descuento != '') {
		redirigir('recibosrh.php?accion=descuentos&operador=' . $operador . '&feini=' . $feini . '&fefin=' . $fefin);
	}
	if($ver_pagos != '') {
		redirigir('recibosrh.php?accion=consulta_pag_desc&operador=' . $operador . '&feini=' . $feini . '&fefin=' . $fefin);
	}

	if (validaAcceso('1110000', $dbpfx) == 1 || ($solovalacc != '1' && ($_SESSION['codigo'] <= '12'))) {
		include('parciales/encabezado.php');
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
   } else {
		redirigir('usuarios.php?mensaje='. $lang['Acceso NO autorizado']);
	}
//	echo $anticipo . '<br>' . $descuento . '<br>' . $ver_pagos . '<br>' . $operador;
	
	if(!$feini && !$fefin) {
		$feini = date('Y-m-01 00:00:00');
		$fefin = date('Y-m-t 23:59:59');
	} else {
		$feini = date('Y-m-d 00:00:00', strtotime($feini));
		$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
	}

	echo '			<div class="control">
				<form action="recibosrh.php?accion=listar" method="post" enctype="multipart/form-data">
				<table cellpadding="0" cellspacing="0" border="0" width="80%">
					<tr class="cabeza_tabla">
						<td colspan="2" style="text-align:left; font-size:16px;">' . $lang['Listado de Recibos'] . '</td>
					<tr>
						<td>' . $lang['Por Pagar'] . '<input type="radio" name="estatus" value="" ';
	if(!isset($estatus) || $estatus == '') { echo 'checked="checked" ';}
	echo '/>&nbsp;&nbsp;
							' . $lang['Pagados'] . '<input type="radio" name="estatus" value="1" ';
	if($estatus == '1') { echo 'checked="checked" ';}
	echo '/>&nbsp;&nbsp;
							' . $lang['Cancelados'] . '<input type="radio" name="estatus" value="2" ';
	if($estatus == '2') { echo 'checked="checked" ';}
	echo '/>&nbsp;&nbsp;
							' . $lang['Todos'] . '<input type="radio" name="estatus" value="3" ';
	if($estatus == '3') { echo 'checked="checked" ';}
	echo '/>
						</td>
						<td>' . $lang['Operador'] . '
							<select name="operador">
								<option value="">Seleccione para filtrar</option>'."\n";
	foreach($usu as $k => $v) {
		echo '								<option value="' . $k . '" ';
		if($operador == $k) { echo 'selected ';}
		echo '>' . $v['nom'] . ' ' . $v['ape'] . '</option>'."\n";
	}
	echo '								</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							'. $lang['Seleccione las fechas'].'<br>'."\n";
	require_once("calendar/tc_calendar.php");
	echo '							<table cellpadding="0" cellspacing="0" border="0" width="100%">
								<tr>
									<td>Fecha de Inicio<br>';
		//instantiate class and set properties
	$myCalendar = new tc_calendar("feini", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
	$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
//	$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2015, 2025);
	$myCalendar->setAutoHide(true, 5000);
		//output the calendar
	$myCalendar->writeScript();
	echo '									</td>
									<td>Fecha de Fin<br>';
	//instantiate class and set properties
	$myCalendar = new tc_calendar("fefin", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
	$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
//	$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2011, 2020);
	$myCalendar->setAutoHide(true, 5000);
		//output the calendar
	$myCalendar->writeScript();
	echo '									</td>
									<td>'."\n";
	if($operador > 1) {
		echo '										<input type="hidden" name="adelantado" value="1" />
										<input class="btn btn-success" type="submit" name="anticipo" value="Registrar pago de Anticipo" />
										<br><input class="btn btn-danger" type="submit" name="descuento" value="Descuento por Penalización" />'."\n";
	} else {
		echo '										<strong>' . $lang['Selecciona Operador Ant Desc'] . '</strong>'."\n";
	}
	echo '										<br><input class="btn btn-primary" type="submit" name="ver_pagos" value="Consultar pagos y descuentos" />
									</td>
								</tr>
								<tr>
									<td colspan="3">
										<br>
										<input class="btn btn-danger" type="submit" name="filtro" value="' . $lang['Recibos'] . '"/>
										<input class="btn btn-success" type="submit" name="filtro" value="' . $lang['OrdenesTrabajo'] . '"/>
									</td>
								</tr>
							</table>
							<br>
						</td>
					</tr>
				</table>
				</form>
			</div>'."\n";
	// ---- Termina área de filtros -----

	$preg1 = "SELECT usuario FROM " . $dbpfx . "destajos WHERE fecha_creado > '$feini' AND fecha_creado < '$fefin'";
	if($estatus == 1) {
		$preg1 .= " AND saldado = 1 ";
	} elseif($estatus == 2) {
		$preg1 .= " AND saldado = 2 ";
	} elseif(!isset($estatus) || $estatus == '') {
		$preg1 .= " AND saldado = 0 ";
	}
	if($operador != '') { 
		$preg1 .= " AND usuario = '$operador' "; 
	}
	$preg1 .= " GROUP BY usuario";
//	echo $preg1;
  	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de recibos! " . $preg1);

  	echo '			<table cellpadding="2" cellspacing="2" border="0" width="80%">
				<tr>
					<td colspan="2" style="vertical-align:top; border: 1px solid black;">'."\n";
	while($gusu = mysql_fetch_array($matr1)) {
		$preg3 = "SELECT * FROM " . $dbpfx . "destajos WHERE fecha_creado > '$feini' AND fecha_creado < '$fefin' AND usuario = '" . $gusu['usuario'] . "' ";
		if($estatus == 1) {
			$preg3 .= " AND saldado = 1 ";
		} elseif($estatus == 2) {
			$preg3 .= " AND saldado = 2 ";
		} elseif(!isset($estatus) || $estatus == '') {
			$preg3 .= " AND saldado = 0 ";
		}
		$preg3 .= " ORDER BY recibo_id ";
//		echo $preg3;
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de recibos! " . $preg3);
		$encabezado = 'Destajos del ' . strftime('%e de %B del %Y', strtotime($feini)) . ' al ' . strftime('%e de %B del %Y', strtotime($fefin));
		$encabezado .= ' para ' . $usu[$gusu['usuario']]['nom'] . ' ' . $usu[$gusu['usuario']]['ape'];
		echo '						<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr class="cabeza_tabla">
								<td colspan="11" style="text-align:left;">' . $encabezado . '</td>
							</tr>'."\n";
		if($filtro == $lang['OrdenesTrabajo']) {
			echo '							<tr>
								<th>OT</th>
								<th>Recibo</th>
								<th>Vehículo</th>
								<th>Siniestro</th>
								<th>Área</th>
								<th>Monto del destajo</th>
								<th>Costo de materiales</th>
							</tr>'."\n";
		} else {
			echo '							<tr>
								<th>Recibo</th>
								<th>Destajo</th>
								<th>Fecha Creado</th>
								<th>Monto Pagado</th>
								<th>Fecha Pagado</th>
								<th>Costo de materiales</th>
							</tr>'."\n";
		}
		$total = 0; $pagado = 0; $consumibles = 0; $fondo = 'claro';
		while($rec = mysql_fetch_array($matr3)) {
			if($rec['saldado'] < 2) {
				if($filtro == $lang['OrdenesTrabajo']) {
					$preg2 = "SELECT * FROM " . $dbpfx . "destajos_elementos WHERE recibo_id = '" . $rec['recibo_id'] . "'";
//					if($filtro == $lang['Recibos'] || $filtro == '') { $preg2 .= " LIMIT 1"; }
					$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de elementos de destajo! " . $preg2);
					while($ord = mysql_fetch_array($matr2)) {
						if($ord['reporte'] == '0' || $ord['reporte'] == '') { $ord['reporte'] = 'Particular'; }
						echo '							
							<tr class="' . $fondo . '">'."\n";
						$veh = datosVehiculo($ord['orden_id'], $dbpfx);
						$sbt_dest = round($ord['monto'],2);
						$imp_dest = round(($sbt_dest * $destiva * $impuesto_iva), 2);
						$total_rec = round(($sbt_dest + $imp_dest), 2);
						$cons_dest = round($ord['costcons'],2);
						$imp_cons = round(($cons_dest * $destiva * $impuesto_iva), 2);
						$total_consu = $cons_dest + $imp_cons;
						echo '								
								<td>
									<a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" target="_blank">' . $ord['orden_id'] . '</a>
								</td>
								<td>
									<a href="recibosrh.php?accion=consultar&recibo_id=' . $rec['recibo_id'] . '">' . $rec['recibo_id'] . '</a>
								</td>
								<td style="text-align: left !important;">
									' . $veh['marca'] . ' ' . $veh['tipo'] . ' ' . $veh['color'] . '
								</td>
								<td>
									' . $ord['reporte'] . '
								</td>
								<td>
									' . constant('NOMBRE_AREA_'.$ord['area']) . '
								</td>
								<td style="text-align: right !important;">
									<b>' . number_format($total_rec, 2) . '</b>
								</td>
								<td style="text-align: right !important;">
									<b>' . number_format($total_consu, 2) . '</b>
								</td>
							</tr>'."\n";
						$total = $total + $total_rec;
						$consumibles = $consumibles + $total_consu; 
						if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
					}
				} else {
					$total_rec = round(($rec['monto'] + $rec['impuesto']),2);
					$total_consu = round(($rec['monto_cons'] + $rec['impuesto_cons']),2);
					$prct = ($sbt_dest + $imp_dest) / $total_rec;
					echo '							
							<tr class="' . $fondo . '">
								<td>
									<a href="recibosrh.php?accion=consultar&recibo_id=' . $rec['recibo_id'] . '">' . $rec['recibo_id'] . '</a>
								</td>
								<td style="text-align:right;">
									<b>' . number_format(($total_rec),2) . '</b>
								</td>
								<td>
									' . date('Y-m-d', strtotime($rec['fecha_creado'])) . '
								</td>
								<td style="text-align:right;">
									<b>' . number_format(($rec['pagado']),2) . '</b>
								</td>
								<td>';
					if(strtotime($rec['fecha_pagado']) > 1000) {
						echo									date('Y-m-d', strtotime($rec['fecha_pagado']));
					}
					echo '								</td><td style="text-align:right;">
									<b>' . number_format($total_consu,2) . '</b>
								</td></tr>'."\n";
					$total = $total + $total_rec;
					$pagado = $pagado + round($rec['pagado'],2);
					$consumibles = $consumibles + $total_consu;
					if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
				}
//				echo '				</table>'."\n";
			} else {
				if($filtro != $lang['OrdenesTrabajo']) {
					echo '							<tr class="' . $fondo . '">
								<td>
									<a href="recibosrh.php?accion=consultar&recibo_id=' . $rec['recibo_id'] . '">' . $rec['recibo_id'] . '</a>
								</td><td colspan="9">
									Recibo Cancelado ' . date('Y-m-d', strtotime($rec['fecha_pagado'])) . '
								</td>
							</tr>'."\n";
				}
				if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
			}
		}
		if($filtro == $lang['OrdenesTrabajo']) {
			echo '							<tr>
								<td style="text-align:right;" colspan="5">
									' . $lang['Total Calculado'] . ' para ' . $usu[$gusu['usuario']]['nom'] . ' ' . $usu[$gusu['usuario']]['ape'] . '
								</td>
								<td style="text-align:right;">
									<b>' . number_format($total,2) . '</b>
								</td>
								<td style="text-align:right;">
									<b>' . number_format($consumibles,2) . '</b>
								</td>
							</tr>'."\n";
		} else {
			echo '							<tr>
								<td style="text-align:right;">
									' . $lang['Total Calculado'] . ':
								</td>
								<td style="text-align:right;">
									<b>' . number_format($total,2) . '</b>
								</td>
								<td style="text-align:right;">
									' . $lang['Total Pagado'] . ':
								</td>
								<td style="text-align:right;">
									<b>' . number_format($pagado,2) . '</b>
								</td>
								<td style="text-align:right;">
									' . $lang['TotalCons'] . ':
								</td>
								<td style="text-align:right;">
									<strong>' . number_format($consumibles,2) . '</strong>
								</td>
							</tr>'."\n";
		}
		$tnc = $tnc + $total;
		$tnp = $tnp + $pagado;
		echo '				</table>
			</div>'."\n";
	}
	echo '			</td>
		</tr>'."\n";
	if($filtro == $lang['OrdenesTrabajo']) {
		echo '		<tr>
			<td style="text-align:right;" colspan="2">
				' . $lang['Total Calculado'] . ': <b>' . number_format($tnc,2) . '</b>
			</td>
		</tr>'."\n";
	} else {
		echo '		<tr>
			<td style="text-align:right;">
				' . $lang['Total Calculado'] . ': <b>' . number_format($tnc,2) . '</b>
			</td>
			<td style="text-align:right;">
				' . $lang['Total Pagado'] . ' <b>' . number_format($tnp,2) . '</b>
			</td>
		</tr>'."\n";
	}
	echo '
		<tr>
			<td colspan="2"><hr></td>
		</tr>
		<tr>
			<td class="cabeza_tabla" colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td style="text-align:left;" colspan="2">
				<div class="control">
					<a href="usuarios.php"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a Usuarios'].'" title="'. $lang['Regresar a Usuarios'].'"></a>
				</div>
			</td>
		</tr>
	</table>'."\n";
}

elseif($accion === 'consulta_pag_desc') {

	if(validaAcceso('1110010', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['codigo'] <= '12'))) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['Acceso NO autorizado'];
		redirigir('recibosrh.php?accion=listar&operador=' . $operador);
	}
	
/*	if($operador == "" || $operador == "Seleccione"){
		$_SESSION['msjerror'] = $lang['SelOperador'];
		redirigir("recibosrh.php?accion=listar");
	} */

	if(isset($feini) && $feini != '0000-00-00') {
		$feini = date('Y-m-d 00:00:00', strtotime($feini));
		$prega = " orden_fecha_recepcion > '" . $feini . "'";
		$t_ini = strftime('%e de %B del %Y', strtotime($feini));
		if(!isset($fefin) || $fefin == '' || $fefin == '0000-00-00') {
			$fefin = date('Y-m-d 23:59:59', time());
		}
		$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
		$prega .= " AND orden_fecha_recepcion < '" . $fefin . "' ";
		$t_fin = strftime('%e de %B del %Y', strtotime($fefin));
	} else {
		$feini = date('Y-m-01 00:00:00');
		$fefin = date('Y-m-t 23:59:59');
		$prega = " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "' ";
		$t_ini = strftime('%e de %B del %Y', strtotime($feini));
		$t_fin = strftime('%e de %B del %Y', strtotime($fefin));
	}
	require_once("calendar/tc_calendar.php");

	if($filtro == '' || $filtro == 'Adelantados') {
		$titulo_busqueda = $lang['pagos_adelantados'];
		$filtro = 'Adelantados';
	} else {
		$titulo_busqueda = $lang['Descuentos'];
		$filtro = 'Descuentos';
	}

// ------ Consulta de pagos adelantados y desuentos -----
	echo '		<div class="page-content content-box">
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
					<div class="content-box-header">
						<div class="panel-title">
							<h2>' . $lang['ConsultaPAyDes'];
	if($operador != '') {
		echo $usu[$operador]['nom'] . ' ' . $usu[$operador]['ape'];
	} else {
		echo $lang['Todos'];
	}
	echo '</h2>
						</div>
					</div>
				</div>
			</div>'."\n";
	
// --- Filros de búsqueda ---
	echo '
	<form action="recibosrh.php?accion=consulta_pag_desc&operador=' . $operador . '&filtro=' . $filtro . '" method="post" enctype="multipart/form-data" name="consultar pagos">
		<div class="row">
			<div class="col-md-12 panel-body">
				<div class="col-sm-3 padding-left">
					<STRONG>Fecha de inicio:</STRONG><br>'."\n";
					//instantiate class and set properties
					$myCalendar = new tc_calendar("feini", true);
					$myCalendar->setPath("calendar/");
					$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
					$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
					$myCalendar->setYearInterval(2011, 2020);
					$myCalendar->setAutoHide(true, 5000);
					//output the calendar
					$myCalendar->writeScript();
	echo '		
				</div>
				<div class="col-sm-3 padding-left">
					<STRONG>Fecha de fin:</STRONG><br>'."\n";
					//instantiate class and set properties
					$myCalendar = new tc_calendar("fefin", true);
					$myCalendar->setPath("calendar/");
					$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
					$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
					$myCalendar->setYearInterval(2011, 2020);
					$myCalendar->setAutoHide(true, 5000);
					//output the calendar
					$myCalendar->writeScript();
	echo '				
				</div>
				<div class="col-sm-3 padding-right">
					<STRONG>Tipo de pago:</STRONG><br>
					<select name="tipo_pago" size="1">
						<option value="0"';
						if($tipo_pago == 0 || $tipo_pago == '') { echo ' selected '; }
						echo '>Todos</option>
						<option value="1"';
						if($tipo_pago == 1) { echo ' selected '; }
						echo '>Con saldo por aplicar</option>
						<option value="2"';
						if($tipo_pago == 2) { echo ' selected '; }
						echo '>Sin saldo pendiente</option>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 panel-body">
				<div class="col-sm-3 padding-left">
					<input type="hidden" name="filtro" value="' . $filtro . '" />
					<input type="submit" class="btn btn-success" value="Consultar">
				</div>
			</div>
		</div>
	</form>'."\n";

	echo '
		<div class="row">
			<div class="col-md-12">
	  			<div id="navegador">
					<a name="com"></a><br>
					<ul>'."\n";
	echo '						<li';
	if($filtro == 'Adelantados') { echo ' class="activa"'; }
	echo '><a href="recibosrh.php?accion=consulta_pag_desc&operador=' . $operador . '&feini=' . $feini . '&fefin=' . $fefin . '&filtro=Adelantados&tipo_pago=' . $tipo_pago . '">' . $lang['pagos'] . '</a></li>'."\n";
	echo '						<li';
	if($filtro == 'Descuentos'){ echo ' class="activa"'; }
	echo '><a href="recibosrh.php?accion=consulta_pag_desc&operador=' . $operador . '&feini=' . $feini . '&fefin=' . $fefin . '&filtro=Descuentos&tipo_pago=' . $tipo_pago . '">' . $lang['Descuentos'] . '</a></li>'."\n";
	echo '					</ul>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 ">
				<div class="col-md-12">
					<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr>'."\n";

	if($filtro == 'Descuentos') {
		echo '								<th>' . $lang['Descuento'] . '</th>
								<th>' . $lang['Recibo'] . '</th>
								<th>' . $lang['Fecha del descuento'] . '</th>'."\n";
		if($operador == '') {
			echo '								<th>' . $lang['Nombre'] . '</th>'."\n";
		}
		echo '								<th>' . $lang['Motivo'] . '</th>
								<th>' . $lang['Monto'] . '</th>
								<th>' . $lang['Monto Asignado'] . '</th>
								<th>' . $lang['Monto Sin Aplicar'] . '</th>
								'."\n";
	} else {	
		echo '
								<th>' . $lang['Pago'] . '</th>
								<th>' . $lang['Recibo'] . '</th>'."\n";
		if($operador == '') {
			echo '								<th>' . $lang['Nombre'] . '</th>'."\n";
		}
		echo '
								<th>' . $lang['Fecha de Pago'] . '</th>
								<th>' . $lang['Forma de pago']. '</th>
								<th>' . $lang['Banco'] . '</th>
								<th>' . $lang['Cuenta'] . '</th>
								<th>' . $lang['Referencia'] . '</th>
								<th>' . $lang['Documento'] . '</th>
								<th>' . $lang['Monto'] . '</th>
								<th>' . $lang['Monto Asignado'] . '</th>
								<th>' . $lang['Monto Sin Aplicar'] . '</th>
								'."\n";
	}
	
	// ---------- Pagos ------------
	$preg_pagos = "SELECT * FROM " . $dbpfx . "destajos_pagos WHERE pago_fecha >= '" . $feini . "' AND pago_fecha <= '" . $fefin . "' ";
	if($operador != '') {
		$preg_pagos .= " AND usuario_pago_recibido = '" . $operador . "'";
	}

	// ---------- Filtros de tipo de pago ---------------
	// ----- $tipo_pago = 1 Con saldo por aplicar -----
	// ----- $tipo_pago = 2 Sin saldo pendiente -----
	// ----- $tipo_pago == '' || $tipo_pago == 0 Buscar todos los pagos -----
	
	if($filtro == 'Descuentos') {
		$preg_pagos .= " AND descuento = '1' ";
	} else {
		$preg_pagos .= " AND descuento IS NULL ";
	}
	
	if($pago_id != '') {
		$preg_pagos = "SELECT * FROM " . $dbpfx . "destajos_pagos WHERE pago_id = '" . $pago_id . "'";
	}
	
	$matr_pagos = mysql_query($preg_pagos) or die("ERROR: Fallo selección de pagos adelantados! " . $preg_pagos);
	$matr_pag_actual = mysql_query($preg_pagos);
	
	$clase = 'claro';
	$total_pag = 0;
	$total_asignado_global = 0;
	while($pagos = mysql_fetch_array($matr_pag_actual)) {
		$pinta = '';
		
		if($pagos['recibo_id'] > 0) {
			$link = '<a href="recibosrh.php?accion=consultar&recibo_id=' . $pagos['recibo_id'] . '">' . $pagos['recibo_id'] . '</a>';
			
			if($tipo_pago == 0 || $tipo_pago == '') { // ----- Buscar todos los pagos -----
				$pinta = 'Si';
			}
			elseif($tipo_pago == 2){// ----- Sin saldo pendiente -----
				$pinta = 'Si';	
			}
			$total_asignado = $pagos['pago_monto_origen'];
			
		} else {
			$link = '<a href="recibosrh.php?accion=historial_pagos&pago_id=' . $pagos['pago_id'] . '&operador=' . $operador . '">Detalle</a>';
			
			// ---- consultar si el pago tiene Hijos ----
			// --- extraer array de la herencia ---
			$array_herencia = explode("|", $pagos['herencia_pagos_id']);
			
			$total_asignado = 0;
			foreach($array_herencia as $key => $val){
				// ---- consultar pago hijo ----
				$preg_pago_h = "SELECT pago_monto, recibo_id, pago_referencia FROM " . $dbpfx . "destajos_pagos WHERE pago_id = '" . $val . "'";
				$matr_pago_h = mysql_query($preg_pago_h) or die("ERROR: Fallo selección de pago!");
				$info_pago_h = mysql_fetch_array($matr_pago_h);
				$total_asignado = $total_asignado + $info_pago_h['pago_monto'];
			}
			$resta = $pagos['pago_monto_origen'] - $total_asignado;
			
			if($tipo_pago == 0 || $tipo_pago == ''){ // ----- Buscar todos los pagos -----
				$pinta = 'Si';
			}
			elseif($tipo_pago == 1 && $resta > 0){// ----- Con saldo pendiente -----
				$pinta = 'Si';
			}
			elseif($tipo_pago == 2 && $resta == 0){// ----- Sin saldo pendiente -----
				$pinta = 'Si';
			}
		
		}
	
		if($filtro == 'Descuentos') {
			if($pinta == 'Si'){
				echo '
							<tr class="' . $clase . '">
								<td>
									<big>' . $pagos['pago_id'] . '</big>
								</td>
								<td>
									<big>' . $link . '</big>
								</td>
								<td>
									<big>' . date('Y-m-d', strtotime($pagos['pago_fecha'])) . '</big>
								</td>
								<td style="text-align: left !important;">
									<big><a href="recibosrh.php?accion=consulta_pag_desc&operador=' . $pagos['usuario_pago_recibido'] . '&filtro=' . $filtro . '&feini=' . $feini . '&fefin=' . $fefin . '">' . $usu[$pagos['usuario_pago_recibido']]['nom'] . ' ' . $usu[$pagos['usuario_pago_recibido']]['ape'] . '</a></big>
								</td>
								<td style="text-align: left !important;">
									<big>' . $pagos['motivo'] . '</big>
								</td>
								<td style="text-align: right !important;">
									<big>' . number_format($pagos['pago_monto_origen'],2) . '</big>
								</td>
								<td style="text-align: right !important;">
									<big><b>' . number_format($total_asignado,2) . '</b></big>
								</td>
								<td style="text-align: right !important;">
									<big><b>' . number_format($resta,2) . '</b></big>
								</td>
							</tr>'."\n";
			}
		} else {
			if($pinta == 'Si'){
				echo '
							<tr class="' . $clase . '">
								<td>
									<big>' . $pagos['pago_id'] . '</big>
								</td>
								<td>
									<big>' . $link . '</big>
								</td>
								<td style="text-align: left !important;">
									<big><a href="recibosrh.php?accion=consulta_pag_desc&operador=' . $pagos['usuario_pago_recibido'] . '&filtro=' . $filtro . '&feini=' . $feini . '&fefin=' . $fefin . '">' . $usu[$pagos['usuario_pago_recibido']]['nom'] . ' ' . $usu[$pagos['usuario_pago_recibido']]['ape'] . '</a></big>
								</td>
								<td>
									<big>' . date('Y-m-d', strtotime($pagos['pago_fecha'])) . '</big>
								</td>
								<td style="text-align: left !important;">
									<big>' . constant('TIPO_PAGO_' . $pagos['pago_tipo']) . '</big>
								</td>
								<td>
									<big>' . $pagos['pago_banco'] . '</big>
								</td>
								<td>
									<big>' . $pagos['pago_cuenta'] . '</big>
								</td>
								<td>
									<big>' . $pagos['pago_referencia'] . '</big>
								</td>
								<td>'."\n";
				if(file_exists(DIR_DOCS . $pagos['pago_documento']) && $pagos['pago_documento'] != '') {
					echo '
									<a href="' . DIR_DOCS . $pagos['pago_documento'] . '" target="_blank"><img src="';
					if(file_exists(DIR_DOCS . 'minis/' . $pagos['pago_documento'])) {
						echo  DIR_DOCS . 'minis/' . $pagos['pago_documento'];
					} else {
						echo  DIR_DOCS . 'documento.png';
					}
					echo '" width="16" border="0"></a>'."\n";
				}
				echo '
								</td>
								<td style="text-align: right !important;">
									<big><b>' . number_format($pagos['pago_monto_origen'],2) . '</b></big>
								</td>
								<td style="text-align: right !important;">
									<big><b>' . number_format($total_asignado,2) . '</b></big>
								</td>
								<td style="text-align: right !important;">
									<big><b>' . number_format($resta,2) . '</b></big>
								</td>
							</tr>'."\n";
			}
		}
		
		if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
		if($pinta == 'Si'){
			$total_pag = $total_pag + $pagos['pago_monto_origen'];
			$total_asignado_global = $total_asignado_global + $total_asignado;
			$resta_global = $resta_global + $resta;
		}
		
		$resta = 0;
		$total_asignado = 0;
	}

	if($filtro == 'Descuentos') { $colspan = 4; } else { $colspan = 8; }
	echo '							
							<tr class="' . $clase . '">
								<td colspan="' . $colspan . '"></td>
								<td style="text-align: right !important;">
									<big><b>' . $lang['Totales'] . '</b></big>
								</td >
								<td style="text-align: right !important;">
									<big><b>' . number_format($total_pag,2) . '</b></big>
								</td>
								<td style="text-align: right !important;">
									<big><b>' . number_format($total_asignado_global,2) . '</b></big>
								</td>
								<td style="text-align: right !important;">
									<big><b>' . number_format($resta_global,2) . '</b></big>
								</td>
							</tr>
						</table>
						<div>
							<a href="recibosrh.php?accion=listar&operador=' . $operador . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar al listado'].'" title="'. $lang['Regresar al listado'].'"></a>
						</div>
					</div>
				</div>
			</div>
		</div>
		'."\n";
	
	echo '
	</div>'."\n";
	
}

elseif($accion === 'historial_pagos') {
	
	$funnum = 1110010;

	// ----------> Validar acceso a recibos de Destajos
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	
	if($_SESSION['codigo'] <= '12' || $retorno == 1) {
		
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso sólo Gerente']);
	}
	
	// ---- consultar pago original ----
	$preg_pago = "SELECT * FROM " . $dbpfx . "destajos_pagos WHERE pago_id = '" . $pago_id . "'";
	$matr_pago = mysql_query($preg_pago) or die("ERROR: Fallo selección de pago!");
	$info_pago = mysql_fetch_array($matr_pago);
	if($info_pago['descuento'] == 1) {
		$tipomov = $lang['Descuento'];
	} else {
		$tipomov = $lang['Pago'];
	}
	
	
	
	echo '
	<div class="page-content content-box">'."\n";
	// ---- consulta de pagos y desuentos -----
	echo '
		<div class="row"> <!-box header del título. -->
			<div class="col-md-12">
	  			<div class="content-box-header">
					<div class="panel-title">
		  				<h2>' . $lang['Historial'] . ' ' . $tipomov . ' ' . $pago_id . '</h2>
					</div>
					<button type="button" class="btn btn-primary">' .$usu[$operador]['nom'] . ' ' . $usu[$operador]['ape'] . '</button>
			  	</div>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-6">
				<div id="content-tabla">
					<table cellspacing="0" class="table-new">
						<tr>
							<th>' . $lang['Recibo'] . '</th>
							<th>' . $lang['Referencia'] . '</th>
							<th>' . $lang['Monto'] . '</th>
						</tr>'."\n";
	
	// --- extraer array de la herencia ---
	$array_herencia = explode("|", $info_pago['herencia_pagos_id']);
	$clase = 'claro';
	$total_asignado = 0;
	foreach($array_herencia as $key => $val){
		// ---- consultar pago hijo ----
		$preg_pago_h = "SELECT pago_monto, recibo_id, pago_referencia FROM " . $dbpfx . "destajos_pagos WHERE pago_id = '" . $val . "'";
		$matr_pago_h = mysql_query($preg_pago_h) or die("ERROR: Fallo selección de pago!");
		$info_pago_h = mysql_fetch_array($matr_pago_h);
		
		echo '
						<tr class="' . $clase . '">
							<td>
								<big><a href="recibosrh.php?accion=consultar&recibo_id=' . $info_pago_h['recibo_id'] . '">' . $info_pago_h['recibo_id'] . '</a></big>
							</td>
							<td style="text-align: left !important;">
								<big>' . $info_pago_h['pago_referencia'] . '</big>
							</td>
							<td style="text-align: right !important;">
								<big><b> ' . number_format($info_pago_h['pago_monto'],2) . '</big></b>
							</td>
						</tr>'."\n";
		
		if($clase == 'claro'){
			$clase = 'obscuro';
		} else{
			$clase = 'claro';
		}
		$total_asignado = $total_asignado + $info_pago_h['pago_monto'];
	}	
	$resta = $info_pago['pago_monto_origen'] - $total_asignado;
	
	echo '
						<tr class="' . $clase . '">
							<td colspan="1"></td>
							<td style="text-align: right !important;">
								<big><b>' . $lang['TotalAsig'] . ':</b></big>
							</td>
							<td style="text-align: right !important;">
								<big><b> ' . number_format($total_asignado,2) . '</b></big>
							</td>
						</tr>
						<tr class="' . $clase . '">
							<td colspan="1"></td>
							<td style="text-align: right !important;">
								<big><b>' . $tipomov . ' ' . $lang['total'] . ':</b></big>
							</td>
							<td style="text-align: right !important;">
								<big><b> ' . number_format($info_pago['pago_monto_origen'],2) . '</b></big>
							</td>
						</tr>
						<tr class="' . $clase . '">
							<td colspan="1"></td>
							<td style="text-align: right !important;">
								<big><b>' . $lang['RestaPA'] . ':</b></big>
							</td>
							<td style="text-align: right !important;">
								<big><b> ' . number_format($resta,2) . '</b></big>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-4 panel-body">
					<a href="recibosrh.php?accion=consulta_pag_desc&operador=' . $operador . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Gestionar boletines" title="Gestionar boletines"></a>
				</div>
			</div>
		</div>'."\n";
		
		
	
}

elseif($accion === 'consultar') {
	
	$funnum = 1110005;

// ----------> Validar acceso a recibos de Destajos
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	
	if ($_SESSION['codigo'] <= '12' || $retorno == '1') {
		include('idiomas/' . $idioma . '/recibosrh.php');
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso no autorizado']);
	}
	if($recibo_id!='') {
		$preg0 = "SELECT * FROM " . $dbpfx . "destajos WHERE recibo_id='" . $recibo_id . "'";
		//	echo $preg0 . '<br>';
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de recibo!");
		$fila0 = mysql_num_rows($matr0);
	   	$rec = mysql_fetch_array($matr0);
	}

	if($fila0 > 0) {
		$codigo = $recibo_id;
		$ope = explode('|', $operador);
		echo '		
			<table cellpadding="0" cellspacing="0" border="0" class="pedidos" width="100%">
				<tr>
					<td rowspan="6" style="width:60%; vertical-align:top;">
						<img src="particular/logo-agencia.png" alt="' . $agencia_razon_social . '"><br clear="all"><br>
						<h2>'. $lang['HOJA DE PAGO DE DESTAJOS'].'</h2>'."\n";

		if($_SESSION['codigo'] <='12' || $retorno == 1) {
			echo '
						<img src="parciales/barcode.php?barcode=' . $codigo . '&width=300&height=60">';
		}
		echo '				
					</td>
					<td style="width:40%; vertical-align:top;">
						<strong>' . $agencia_razon_social . '</strong><br>
						' . $agencia_direccion . '.<br>
						' . $agencia_colonia . ', '  . $agencia_municipio . '.<br>
						' . $agencia_cp . '. '  . $agencia_estado . '. México.<br>
						' . $agencia_telefonos . '.<br>
					</td>
				</tr>
				<tr>
					<td>
						<strong>'. $lang['Recibo:'] . $recibo_id . '</strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong>'. $lang['Nombre'] . $usu[$rec['usuario']]['nom'] . ' ' . $usu[$rec['usuario']]['ape'] . '</strong>
					</td>
				</tr>
				<tr>
					<td>
						'. $lang['Operador:'].'<a href="recibosrh.php?accion=listar&operador=' . $rec['usuario'] . '">' . $rec['usuario'] . '</a>
					</td>
				</tr>
			</table>
			<form action="recibosrh.php?accion=pagar" method="post" enctype="multipart/form-data">
			<div class="control">
				<table cellpadding="3" cellspacing="0" border="0" class="izquierda">
					<tr>
						<td colspan="2"><span class="alerta">
							' . $_SESSION['ped']['mensaje'] . '</span>
						</td>
					</tr>
				</table>
			</div>
			<hr>
			<table cellpadding="0" cellspacing="0" border="0" >
				<tr>
					<td style="vertical-align:top;"><input type="hidden" name="operador" value="' . $rec['usuario'] . '" />
						<h3>' . $lang['ELEMENTOS DEL RECIBO'] . '</h3>'."\n";
		
		$fondo = 'claro';
		echo '			
						<table cellpadding="2" cellspacing="0" border="1" class="izquierda chica">
							<tr>
								<td>'. $lang['OT'].'</td>
								<td>'. $lang['Vehículo'].'</td>
								<td>'. $lang['Costo de'].'<br>'. $lang['Materiales'].'</td><td>'. $lang['Siniestro'].'</td>
								<td>'. $lang['Área'].'</td>
								<td style="width:90px;">'. $lang['Destajo'].'</td>
							</tr>'."\n";
		
		$preg1 = "SELECT * FROM " . $dbpfx . "destajos_elementos WHERE recibo_id='" . $recibo_id . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden_productos!");
		$numots = 0;
		$promots = 0;
		$fondo = 'claro';
		$pago = 0; $mats = 0;
		while ($elem = mysql_fetch_array($matr1)) {
			if($elem['reporte'] == '0' || $elem['reporte'] == '') { $elem['reporte'] = 'Particular'; }
			echo '				
							<tr class="' . $fondo . '">
								<td style="text-align:center;">
									<a href="ordenes.php?accion=consultar&orden_id=' . $elem['orden_id'] . '">' . $elem['orden_id'] . '</a>
								</td>
								<td>
									' . $elem['vehiculo'] . '
								</td>
								<td style="text-align:right;">
									' . number_format($elem['costcons'],2) . '
								</td>
								<td>
									' . $elem['reporte'] . '
								</td>
								<td>';
			
			if($elem['comision'] != ''){
				echo 
									$elem['comision'];
			} else{
				echo  
									constant('NOMBRE_AREA_'.$elem['area']);	
			}
			echo '
								</td>
								<td style="text-align:right;">
									' . number_format($elem['monto'],2) . '
								</td>
							</tr>'."\n";
			
			$pago = $pago + $elem['monto'];
			$mats = $mats + $elem['costcons'];
			if($fondo == 'claro') {$fondo = 'obscuro';} else {$fondo = 'claro';}
			$numots++;
		}
		if($destiva == 1) {
			echo '				
							<tr>
								<td colspan="2" style="text-align:right;">
									'. $lang['Subtotal Materiales'].'
								</td>
								<td style="text-align:right;">
									' . number_format($mats,2) . '
								</td>
								<td colspan="2" style="text-align:right;">
									'. $lang['Subtotal Destajo'].'
								</td>
								<td style="text-align:right;">
									' . number_format($pago,2) . '
								</td>
							</tr>
							<tr>
								<td colspan="2" style="text-align:right;">
									'. $lang['IVA 16'].'
								</td>
								<td style="text-align:right;">
									' . number_format($rec['impuesto_cons'],2) . '
								</td>
								<td colspan="2" style="text-align:right;">
									'. $lang['IVA 16'].'
								</td>
								<td style="text-align:right;">
									' . number_format($rec['impuesto'],2) . '
								</td>
							</tr>'; 
		}
		$total = round(($rec['monto'] + $rec['impuesto']),2);
		$matstotal = $rec['monto_cons'] + $rec['impuesto_cons'];
		echo '				
							<tr>
								<td colspan="2" style="text-align:right; font-weight:bold;">
									'. $lang['Total Materiales'].'
								</td>
								<td style="text-align:right; font-weight:bold;">
									' . number_format($matstotal,2) . '
								</td>
								<td colspan="2" style="text-align:right; font-weight:bold;">
									'. $lang['Total Destajo'].'
								</td>
								<td style="text-align:right; font-weight:bold;">
									' . number_format($total,2) . '
								</td>
							</tr>';
		
		$promots = round(($pago / $numots), 2);
		echo '				
							<tr class="control">
								<td colspan="6" style="text-align:left;">
									'. $lang['Total OTs'].'<strong>' . $numots . '</strong> '. $lang['Promedio antes de IVA'].'<strong>' . number_format($promots,2) . '</strong>
								</td>
							</tr>
							<input type="hidden" name="recibo_id" value="' . $recibo_id . '" />
							<tr class="control">
								<td colspan="4" style="text-align:left;">';
		
		$preg_total = "SELECT pago_monto FROM " . $dbpfx . "destajos_pagos WHERE recibo_id = '" . $recibo_id . "' AND  descuento IS NULL";
		$matr_total = mysql_query($preg_total) or die("ERROR: Fallo selección de pagos!");
  		$pagado = 0;
		while($consulta_total = mysql_fetch_array($matr_total)) {
			$pagado = $consulta_total['pago_monto'] + $pagado;
		}
		$preg_desc = "SELECT pago_monto FROM " . $dbpfx . "destajos_pagos WHERE recibo_id = '" . $recibo_id . "' AND descuento = '1'";
		$matr_desc = mysql_query($preg_desc) or die("ERROR: Fallo selección de descuentos!");
		$descuentos = 0;
		while($con_descuentos = mysql_fetch_array($matr_desc)) {
			$descuentos = $con_descuentos['pago_monto'] + $descuentos;
		}
		$por_pagar = round(($total - $pagado - $descuentos),2);
		
		if($rec['pagado'] < $total && $rec['saldado'] < 2) {
			
			echo '					
									<input type="hidden" name="pago_recibo" value="1" />
									<input type="submit" name="pago" value="'. $lang['Registrar Pago a Operador'].'" />
									<a href="recibosrh.php?accion=descuentos&recibo_id=' . $recibo_id . '&operador=' . $rec['usuario'] . '&por_pagar=' . $por_pagar . '"><button type="button">'. $lang['Registrar Descuento a Operador'].'</button></a>';
			
		} elseif($rec['saldado'] == 2) {
			echo '
									<span style="font-weight:bold; color: #fd0000;">RECIBO CANCELADO</span>';	
		} else {
			echo '
									<span style="font-weight:bold; background-color: green; color: white;">'. $lang['DESTAJO PAGADO'] . $rec['fecha_pagado'] . '</span>';
		}
		echo '
								</td>
								<td colspan="2">
									<a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['ayuda_recibos'] . '&base=recibosrh.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['¿CÓMO ELIMINAR RECIBOS?'] . '</a><br>
									<a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['ayuda_descuentos'] . '&base=recibosrh.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['DESCUENTOS A OPERADOR'] . '</a>
								</td>
							</tr>
						</table>'."\n";
		
		echo '			
						<input type="hidden" name="por_pagar" value="' . $por_pagar . '" />
			</form>
					</td>
					<td style="width:20px;"></td>
					<td style="vertical-align:top;">
						<div class="control">'."\n";
		
		if($rec['saldado'] < 2) {
// --------------- Tabla para Administración de Pagos a Operadores ---------------------- //  	
			$preg2 = "SELECT * FROM " . $dbpfx . "destajos_pagos WHERE recibo_id = '" . $recibo_id . "' AND descuento IS NULL";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de pagos!");
//			echo $preg2 . '<br>';
			$pagado = 0;
			$fondo = 'claro';
			echo '		
							<h3>' . $lang['PAGOS DEL RECIBO'] . '</h3>
							<table cellpadding="2" cellspacing="0" border="1" class="izquierda">'."\n";
			$contenido = '';
  			while($pag = mysql_fetch_array($matr2)) {
  				$pagado = $pag['pago_monto'] + $pagado;
	  			$contenido .= '				
								<tr>
									<td>
										<a href="recibosrh.php?accion=desaso_pag&pago_id=' . $pag['pago_id'] . '&recibo_id=' . $recibo_id . '&monto=' . $pag['pago_monto'] . '"><img src="idiomas/' . $idioma . '/imagenes/edit-delete-6.png" alt="Desasociar" title="Desasociar"></a>
									</td>
									<td>
										' . $pag['pago_id'] . '
									</td>
									<td>
										' . date('Y-m-d', strtotime($pag['pago_fecha'])) . '
									</td>
									<td>
										' . constant('TIPO_PAGO_' . $pag['pago_tipo']) . '
									</td>
									<td>
										' . $pag['pago_banco'] . '
									</td>
									<td>
										' . $pag['pago_cuenta'] . '
									</td>
									<td>
										' . $pag['pago_referencia'] . '
									</td>
									<td>';
				
				if(file_exists(DIR_DOCS . $pag['pago_documento']) && $pag['pago_documento'] != '') {
					$contenido .= '<a href="' . DIR_DOCS . $pag['pago_documento'] . '" target="_blank"><img src="';
					if(file_exists(DIR_DOCS . 'minis/' . $pag['pago_documento'])) {
						$contenido .= DIR_DOCS . 'minis/' . $pag['pago_documento'];
					} else {
						$contenido .= DIR_DOCS . 'documento.png';
					}
					$contenido .= '" width="48" border="0"></a>'; 
				}
				$contenido .= '
									<td style="text-align: right;">
										-' . number_format($pag['pago_monto'],2) . '
									</td>
								</tr>'."\n";
	  		}

// --------------------- Selección de descuentos ----------------
			$preg_desc = "SELECT * FROM " . $dbpfx . "destajos_pagos WHERE recibo_id = '" . $recibo_id . "' AND descuento = '1'";
			$matr_desc = mysql_query($preg_desc) or die("ERROR: Fallo selección de descuentos!");
			$descuentos = 0;
			$num_desc = mysql_num_rows($matr_desc);
			if($num_desc != 0) {
				while($con_desc = mysql_fetch_array($matr_desc)) {
					$contenido .= '				
								<tr>
									<td>
										<a href="recibosrh.php?accion=desaso_pag&pago_id=' . $con_desc['pago_id'] . '&recibo_id=' . $recibo_id . '&monto=' . $con_desc['pago_monto'] . '"><img src="idiomas/' . $idioma . '/imagenes/edit-delete-6.png" alt="Desasociar" title="Desasociar"></a>
									</td>
									<td>
										'. $con_desc['pago_id'] . '</td>
									<td>
										' . date('Y-m-d', strtotime($con_desc['pago_fecha'])) . '
									</td>
									<td colspan="5">
										' . $lang['DESCUENTO'] . $con_desc['motivo'] . ', ' . $con_desc['pago_referencia'] . '
									</td>
									<td style="text-align: right;">
										-' . number_format($con_desc['pago_monto'],2) . '
									</td>
								</tr>'."\n";
					$descuentos = $descuentos + $con_desc['pago_monto'];
				}
			}
			$por_pagar = round(($total - $pagado - $descuentos),2);

// ------ Muestra elementos de Pago
			echo '				
								<tr>
									<td>
										<a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['desasoc_pagos'] . '&base=recibosrh.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">Quitar</a>
									</td>
									<td>
										' . $lang['Pago'] . '
									</td>
									<td>
										' . $lang['Fecha'] . '
									</td>
									<td>
										'. $lang['Forma de pago'].'
									</td>
									<td>
										'. $lang['Banco'].'
									</td>
									<td>
										'. $lang['Cuenta'].'
									</td>
									<td>
										'. 	$lang['Referencia'].'
									</td>
									<td>
										'. $lang['Documento'].'
									</td>
									<td>
										'. 	$lang['Monto'].'
									</td>
								</tr>
								<tr>
									<td colspan="8" style="text-align:right;">
										'. $lang['Total Destajo'].'
									</td>
									<td colspan="1" style="text-align: right;">
										<strong>' . number_format($total,2) . '</strong>
									</td>
								</tr>'."\n";
			echo $contenido;
			echo '				
								<tr>
									<td colspan="8" style="text-align:right;">
										'. $lang['Total por pagar'].'
									</td>
									<td colspan="1" style="text-align: right;">
										<strong>' . number_format($por_pagar,2) . '</strong>
									</td>
								</tr>
							</table>'."\n";
			$total_dscuentos = mysql_num_rows($matr_desc);
			$total_pagos = mysql_num_rows($matr2);
			if($total_dscuentos == 0 && $total_pagos == 0) {
				echo '			
							<a href="recibosrh.php?accion=eliminar_recibo&recibo_id=' . $recibo_id . '"><button type="button">'. $lang['Eliminar recibo'] . '</button></a>'."\n";
			} else {
				echo '			
							<a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['desasoc_pagos'] . '&base=recibosrh.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['¿CÓMO DESASOCIAR PAGOS?'] . '</a>'."\n";	
			}
			echo '			
							<hr>'."\n";
		
// ---------- Pagos Sin Asociar ------------
			$preg_pag_ade = "SELECT * FROM " . $dbpfx . "destajos_pagos WHERE usuario_pago_recibido = '" . $rec['usuario'] . "' AND recibo_id = '0' AND descuento IS NULL AND pago_monto > '0'";
			$matr_pag_ade = mysql_query($preg_pag_ade) or die("ERROR: Fallo selección de pagos adelantados!");
//			echo $preg_pag_ade;
			echo '			
							<h3><a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['pagos_adelantados'] . '&base=recibosrh.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['PAGOS ADELANTADOS'] . '</a> A ' .$usu[$rec['usuario']]['nom'] . ' ' . $usu[$rec['usuario']]['ape'] . '</h3>'."\n";
			if( mysql_num_rows($matr_pag_ade) == '0') {
				echo '			
							<p><strong>'. $lang['No se encontraron pagos adelantados'] . '</strong></p>'."\n";
			} else {
				echo '			
							<table cellpadding="2" cellspacing="0" border="1" class="izquierda control">
								<tr>
									<td>
										<a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['asociar'] . '&base=recibosrh.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['Asociar'] . '</a>
									</td>
									<td>
										<a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['eliminar'] . '&base=recibosrh.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['Eliminar_'] . '</a>
									</td>
									<td>'. $lang['Pago'] . '</td>
									<td>'. $lang['Fecha'].'</td>
									<td>'. $lang['Forma de pago'].'</td>
									<td>'. $lang['Banco'].'</td>
									<td>'. $lang['Cuenta'].'</td>
									<td>'. $lang['Referencia'].'</td>
									<td>Documento</td>
									<td>Monto</td>
								</tr>'."\n";
				$total_pag = 0;
				while($con_pag = mysql_fetch_array($matr_pag_ade)) {
					
					if($con_pag['pago_monto_origen'] == 0){
						
					} else{
						$Monto_origen = ' <small>Monto original <b>' .  number_format($con_pag['pago_monto_origen'],2) . '</b></small>';
					}
					
					echo '				
								<tr>
									<td>
										<a href="recibosrh.php?accion=asoci_pag_ade&pago_id=' . $con_pag['pago_id'] . '&recibo_id=' . $recibo_id . '&monto=' . $con_pag['pago_monto'] . '&por_pagar=' . $por_pagar . '"><img src="idiomas/' . $idioma . '/imagenes/list-add-5.png" alt="Asociar Pago" title="Asociar Pago"></a>
									</td>
									<td>
										<a href="recibosrh.php?accion=eliminar_pago&pago_id=' . $con_pag['pago_id'] . '&recibo_id=' . $recibo_id . '&monto=' . $con_pag['pago_monto'] . '&de_usuario=' . $rec['usuario'] . '"><img src="idiomas/' . $idioma . '/imagenes/edit-delete-6.png" alt="Eliminar Pago" title="Eliminar Pago"></a>
									</td>
									<td>
										' . $con_pag['pago_id'] . '
									</td>
									<td>
										' . date('Y-m-d', strtotime($con_pag['pago_fecha'])) . '
									</td>
									<td>
										' . constant('TIPO_PAGO_' . $con_pag['pago_tipo']) . '
									</td>
									<td>
										' . $con_pag['pago_banco'] . '
									</td>
									<td>
										' . $con_pag['pago_cuenta'] . '
									</td>
									<td>
										' . $con_pag['pago_referencia'] . $Monto_origen . '
									</td>
									<td>';
					if(file_exists(DIR_DOCS . $con_pag['pago_documento']) && $con_pag['pago_documento'] != '') {
						echo '
										<a href="' . DIR_DOCS . $con_pag['pago_documento'] . '" target="_blank"><img src="';
						if(file_exists(DIR_DOCS . 'minis/' . $con_pag['pago_documento'])) {
							echo DIR_DOCS . 'minis/' . $con_pag['pago_documento'];
						} else {
							echo DIR_DOCS . 'documento.png';
						}
						echo '" width="48" border="0"></a>'; 
					}
					echo '
									<td>
										<strong>' . number_format($con_pag['pago_monto'],2) . '</strong>
									</td>
								</tr>'."\n";
					$total_pag = $total_pag + $con_pag['pago_monto'];
				}
				echo '				
								<tr>
									<td colspan="9" style="text-align:right;">
										' . $lang['Total Pagado sin registrar:'] . '
									</td>
									<td>
										<strong>' .  number_format($total_pag,2) . '</strong>
									</td>
								</tr>
							</table>'."\n";
			}

// ------ Descuentos sin asociar ---
			$preg_descuentos = "SELECT * FROM " . $dbpfx . "destajos_pagos WHERE usuario_pago_recibido = '" . $rec['usuario'] . "' AND recibo_id = '0' AND descuento = '1' AND pago_monto > '0'";
			$matr_descuentos = mysql_query($preg_descuentos) or die("ERROR: Fallo selección de pagos adelantados!");
			echo '			
							<h3>
								<a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['Descuentos_sin_asociar'] . '&base=recibosrh.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['DESCUENTOS SIN ASOCIAR'] . '</a> DE ' .$usu[$rec['usuario']]['nom'] . ' ' . $usu[$rec['usuario']]['ape'] . '</h3>'."\n";
			if(mysql_num_rows($matr_descuentos) == '0') {
				echo '			
							<p><strong>' . $lang['No se encontraron descuentos'] . '</strong></p>'."\n";
			} else {
				echo '			
							<table cellpadding="2" cellspacing="0" border="1" class="izquierda control">
								<tr>
									<td>' . $lang['Asociar'] .  '</td>
									<td>' . $lang['Eliminar'] . '</td>
									<td>' . $lang['Pago'] . '</td>
									<td>' . $lang['Fecha'] . '</td>
									<td>'. $lang['Motivo']. '</td>
									<td>' . $lang['Monto']. '</td>
								</tr>'."\n";
				$total_pag = 0;
				while($con_desc = mysql_fetch_array($matr_descuentos)) {
					echo '				
								<tr>
									<td>
										<a href="recibosrh.php?accion=asoci_pag_ade&pago_id=' . $con_desc['pago_id'] . '&recibo_id=' . $recibo_id . '&monto=' . $con_desc['pago_monto'] . '&por_pagar=' . $por_pagar . '&descuento=1"><img src="idiomas/' . $idioma . '/imagenes/list-add-5.png" alt="Asociar Pago" title="Asociar Pago"></a>
									</td>
									<td>
										<a href="recibosrh.php?accion=eliminar_pago&pago_id=' . $con_desc['pago_id'] . '&recibo_id=' . $recibo_id . '&monto=' . $con_desc['pago_monto'] . '&de_usuario=' . $rec['usuario'] . '"><img src="idiomas/' . $idioma . '/imagenes/edit-delete-6.png" alt="desasociar" title="desasociar"></a>
									</td>
									<td>
										' . $con_desc['pago_id'] . '
									</td>
									<td>
										' . date('Y-m-d', strtotime($con_desc['pago_fecha'])) . '
									</td>
									<td>
										' . $con_desc['motivo'] . '
									</td>
									<td>
										<strong>' . number_format($con_desc['pago_monto'],2) . '</strong>
									</td>
								</tr>'."\n";
					$total_pag = $total_pag + $con_desc['pago_monto'];
				}
				echo '				
								<tr>
									<td colspan="5" style="text-align:right;">
										' . $lang['Total descuentos sin registrar:'] . '
									</td>
									<td>
										<strong>' .  number_format($total_pag,2) . '</strong>
									</td>
								</tr>
							</table>'."\n";	
			}
		}
		echo '			</div>
					</td>
				</tr>
				<tr>
					<td class="cabeza_tabla" colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:left;">
						<div class="control">
							<a href="recibosrh.php?accion=listar&operador=' . $rec['usuario'] . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a Usuarios'].'" title="'. $lang['Regresar a Usuarios'].'"></a>
						</div>
					</td>
				</tr>
			</table>
			<div id="encabezado">
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td colspan="7">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="7" class="cen">
							'. $lang['ADJUNTAR HOJA A SU FACTURA DE AUTORIZADO'].'
						</td>
					</tr>
				</table>
			</div>'."\n";
	} else {
		echo $lang['No se encontró recibo'];
	}
}

elseif($accion === 'asoci_pag_ade') {

	$funnum = 1110015;
	
// ----------> Validar acceso a recibos de Destajos ----
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);	
	
	if($confirma == '1') {
		
		//echo 'recibo: ' . $recibo_id . ' pago_id: ' . $pago_id . ' monto: ' . $monto . ' por pagar: ' . $por_pagar . '<br>';
		
		$monto = limpiarNumero($monto);
		// --- verificar que el pago no exceda el monto pendiente ---
		if($monto > $por_pagar){
			$mensaje .= 'No se puede aplicar el pago, el monto asignado supera el monto pendiente por pagar.<br>';
			$_SESSION['msjerror'] = $mensaje;
			redirigir('recibosrh.php?accion=asoci_pag_ade&pago_id=' . $pago_id . '&recibo_id=' . $recibo_id . '&por_pagar=' . $por_pagar);
		} else{
			
			if($por_pagar > 0){
				// ---- consultar pago original ----
				$preg_pago = "SELECT * FROM " . $dbpfx . "destajos_pagos WHERE pago_id = '" . $pago_id . "'";
				$matr_pago = mysql_query($preg_pago) or die("ERROR: Fallo selección de pago!");
				$info_pago = mysql_fetch_array($matr_pago);
				$restante = $info_pago['pago_monto'] - $monto;
				
				if($restante == 0 && $info_pago['herencia_pagos_id'] == ''){ // si el pago se aplicará completo y no tiene hijos, se asocia al recibo
					// --- Asignar el pago o descuento al recibo ---
					//echo 'Se puede easociar el pago, no tiene hijos<br>';
					unset($sql_data_array);
					$sql_data_array = [
						'recibo_id' => $recibo_id,
					];
					$parametros = " pago_id ='" . $pago_id . "'";
					ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'actualizar', $parametros);
					$mensaje .= 'Se asoció el ' . $pago_id . ' al recibo.<br>';
					$_SESSION['msjerror'] = $mensaje;
					
				} elseif($restante == 0 && $info_pago['herencia_pagos_id'] != ''){ // El monto es exacto pero tiene hijos, se crea un nuevo pago y se deja en 90 el pago padre
					//echo 'El monto es exacto pero tiene hijos<br>';
					if($descuento == 1){
						$referencia = 'asignado del descuento ' . $pago_id;
					} else{
						$referencia = 'asignado del pago ' . $pago_id;
					}
					$sql_data_array = [
						'pago_monto' => $monto,
						'pago_tipo' => $info_pago['pago_tipo'],
						'pago_banco' => $info_pago['pago_banco'],
						'pago_cuenta' => $info_pago['pago_cuenta'],
						'pago_documento' => $info_pago['pago_documento'],
						'pago_referencia' => $referencia,
						'pago_fecha' => date('Y-m-d H:i:s', time()),
						'recibo_id' => $recibo_id,
						'usuario' => $_SESSION['usuario'],
						'origen_pago_id' => $info_pago['pago_id'],
						'usuario_pago_recibido' => $info_pago['usuario_pago_recibido']
					];
					if($descuento == 1){
						$sql_data_array['descuento'] = $info_pago['descuento'];
						$sql_data_array['motivo'] = $info_pago['motivo'];
					}
					$hijo_id = ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array);
					// ---- Actualizar pago original con el monto 0 ------
					unset($sql_data_array);
					$sql_data_array = [
						'pago_monto' => $restante,
					];
					if($info_pago['herencia_pagos_id'] != ''){
						$sql_data_array['herencia_pagos_id'] = $info_pago['herencia_pagos_id'] . '|' . $hijo_id;
					}
					$parametros = " pago_id ='" . $pago_id . "'";
					ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'actualizar', $parametros);
					$mensaje .= 'Se asoció la cantidad seleccionada al recibo.<br>';
					$_SESSION['msjerror'] = $mensaje;
					
				} else{
					//echo 'crear pago hijo<br>';
					// ---- crear nuevo pago con el monto elegido por el usuario y asignar al recibo
					if($descuento == 1){
						$referencia = 'asignado del descuento ' . $pago_id;
					} else{
						$referencia = 'asignado del pago ' . $pago_id;
					}
					$sql_data_array = [
						'pago_monto' => $monto,
						'pago_tipo' => $info_pago['pago_tipo'],
						'pago_banco' => $info_pago['pago_banco'],
						'pago_cuenta' => $info_pago['pago_cuenta'],
						'pago_documento' => $info_pago['pago_documento'],
						'pago_referencia' => $referencia,
						'pago_fecha' => date('Y-m-d H:i:s', time()),
						'recibo_id' => $recibo_id,
						'usuario' => $_SESSION['usuario'],
						'origen_pago_id' => $info_pago['pago_id'],
						'usuario_pago_recibido' => $info_pago['usuario_pago_recibido']
					];
					if($descuento == 1){
						$sql_data_array['descuento'] = $info_pago['descuento'];
						$sql_data_array['motivo'] = $info_pago['motivo'];
					}
					$hijo_id = ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array);
					// ---- Actualizar pago original con el monto sobrante ------
					unset($sql_data_array);
					$sql_data_array = [
						'pago_monto' => $restante,
					];
					if($info_pago['herencia_pagos_id'] != ''){
						$sql_data_array['herencia_pagos_id'] = $info_pago['herencia_pagos_id'] . '|' . $hijo_id;
					}
					// --- revisar si ya tiene hijos el pago ---
					if($info_pago['herencia_pagos_id'] == ''){
						$sql_data_array['herencia_pagos_id'] = $hijo_id;
					}
					$parametros = " pago_id ='" . $pago_id . "'";
					ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'actualizar', $parametros);
					$mensaje .= 'Se fraccionó el pago ' . $pago_id . ' y se asoció la cantidad seleccionada al recibo.<br>';
					$_SESSION['msjerror'] = $mensaje;
				}
				
				// ----- Actualizar el recibo de destajo -----
				$preg_recibo = "SELECT pagado, monto FROM " . $dbpfx . "destajos WHERE recibo_id = '" . $recibo_id . "'";
				$matr_recibo = mysql_query($preg_recibo) or die("ERROR: Fallo selección de recibo!");
				$info_recibo = mysql_fetch_array($matr_recibo);
				unset($sql_data_array);
				// ----- sumar el monto del pago al total pagado del recibo ---
				
				$pagado_rec = $info_recibo['pagado'] + $monto;
				$sql_data_array['pagado'] = $pagado_rec;
				
				if($pagado_rec == $info_recibo['monto']){// marcar saldado el recibo
					$sql_data_array['saldado'] = 1;
					$sql_data_array['fecha_pagado'] = date('Y-m-d H:i:s', time());	
				} else{
					$sql_data_array['saldado'] = 0;
					$sql_data_array['fecha_pagado'] = '';
				}
				$parametros = " recibo_id = '" . $recibo_id . "'";	
				ejecutar_db($dbpfx . 'destajos', $sql_data_array, 'actualizar', $parametros);
				
				redirigir('recibosrh.php?accion=consultar&recibo_id=' . $recibo_id);
				
			} else{
				$mensaje .= 'No hay monto pendiente por pagar.<br>';
				$_SESSION['msjerror'] = $mensaje;
				redirigir('recibosrh.php?accion=consultar&recibo_id=' . $recibo_id);
			}
		}
		
	} else{
		
		// ---- consulta info del pago adelantado ----
		$preg0 = "SELECT * FROM " . $dbpfx . "destajos_pagos WHERE pago_id='" . $pago_id . "'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de pago!");
		$pago = mysql_fetch_array($matr0);
		
		if($descuento == 1){
			$texto = 'El descuento tiene un monto de $' . number_format($pago['pago_monto'],2) . ' y queda pendiente de pagar del recibo $' . number_format($por_pagar,2);
		} else{
			$texto = 'El pago adelantado tiene un monto de $' . number_format($pago['pago_monto'],2) . ' y queda pendiente de pagar del recibo $' . number_format($por_pagar,2);
		}
		
		echo '
			<h2>
				' . $texto . '<br><br>
				¿cuánto desea agregar al recibo?
			</h2>
			<form action="recibosrh.php?accion=asoci_pag_ade" method="post" enctype="multipart/form-data">
			<input type="text" class="form-control" name="monto" placeholder="Monto" required value="' . $pago['pago_monto'] . '"><br>
			<table>
				<tr>
					<td>
					<input type="hidden" name="por_pagar" value="' . $por_pagar . '">
					<input type="hidden" name="pago_id" value="' . $pago_id . '">
					<input type="hidden" name="recibo_id" value="' . $recibo_id . '">
					<input type="hidden" name="descuento" value="' . $descuento . '">
					<input type="hidden" name="confirma" value="1">
					<input type="submit" class="btn btn-success" value="Agregar pago"></td>
					<td><a href="recibosrh.php?accion=consultar&recibo_id=' . $recibo_id . '"><button type="button" class="btn btn-danger">NO, regresar</button></a></td>
				</tr>
			</table>'."\n";
		echo '					</form>'."\n";
	}	
}

elseif($accion === 'desaso_pag'){
	
	if(validaAcceso('1110015', $dbpfx)=='1') {
		$mensaje = $lang['Acceso autorizado'];
	} elseif ($solovalacc != '1' && ($_SESSION['codigo'] <= '12')) {
		$mensaje = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso para Administradores']);
	}

	// --- consultar info del pago ---
	$preg_pago = "SELECT origen_pago_id, pago_monto FROM " . $dbpfx . "destajos_pagos WHERE pago_id = '" . $pago_id . "'";
	$matr_pago = mysql_query($preg_pago) or die("ERROR: Fallo selección de pago!");
	$info_pago = mysql_fetch_array($matr_pago);
	
	if($info_pago['origen_pago_id'] != ''){ // Eliminar pago hijo y regresar monto al pago padre 
		//echo 'Es un pago hijo<br>';
		// --- consultar pago padre ---
		$preg_pago_p = "SELECT pago_id, pago_monto, herencia_pagos_id FROM " . $dbpfx . "destajos_pagos WHERE pago_id = '" . $info_pago['origen_pago_id'] . "'";
		$matr_pago_p = mysql_query($preg_pago_p) or die("ERROR: Fallo selección de pago!");
		$info_pago_p = mysql_fetch_array($matr_pago_p);
		
		// --- actualizar el pago padre ---
		$nuevo_monto = $info_pago_p['pago_monto'] + $info_pago['pago_monto'];
		//echo 'Padre ' . $info_pago['origen_pago_id'] . '<br>';
		//echo 'Monto padre ' . $info_pago_p['pago_monto'] . '<br>';
		
		// --- Quitar el pago hijo de la herencia ---
		$array_herencia = explode("|", $info_pago_p['herencia_pagos_id']);
		print_r($array_herencia);
		$nueva_herencia = '';
		foreach($array_herencia as $key => $val){
			// --- rearmar la cadena de herencia ---
			$omite = 0;
			if($val == $pago_id){
				$omite = 1;
			}
			if($nueva_herencia == ''){
				if($omite == 0){
					$nueva_herencia = $val;
				} 
			} else{
				if($omite == 0){
					$nueva_herencia = $nueva_herencia . '|' . $val;
				} 
			}
		}	
		$sql_data_array = [
			'pago_monto' => $nuevo_monto,
			'herencia_pagos_id' => $nueva_herencia,
		];
		$parametros = " pago_id ='" . $info_pago_p['pago_id'] . "'";
		ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'actualizar', $parametros);
		
		// --- eliminar el pago hijo ---
		$parametros = " pago_id ='" . $pago_id . "'";
		ejecutar_db($dbpfx . 'destajos_pagos', '', 'eliminar', $parametros);
		
	} else{ // se desasocia el pago del recibo
		//echo 'Es un pago padre<br>';
		$sql_data_array['recibo_id'] = 0;
		$parametros = " pago_id ='" . $pago_id . "'";
		ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'actualizar', $parametros);
	}
	$consulta = "UPDATE " . $dbpfx . "destajos SET pagado = pagado - '" . $monto . "', saldado = '0', fecha_pagado = NULL WHERE recibo_id = '" . $recibo_id . "'";
	$aplica = mysql_query($consulta) or die("Falló el ajuste del monto pagado del destajo! " . $consulta);
	$archivo = '../logs/' . time() . '-base.ase';
	$myfile = file_put_contents($archivo, $coloca . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
	redirigir('recibosrh.php?accion=consultar&recibo_id=' . $recibo_id);
}

elseif($accion === "pagar") {
	
	$funnum = 1110010;

// ----------> Validar acceso a recibos de Destajos
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	
	if ($_SESSION['codigo'] <= '12' || $retorno == 1) {
		include('idiomas/' . $idioma . '/recibosrh.php');
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso sólo Gerente']);
	}
	
	if($operador == "" || $operador == "Seleccione") {
		if($recibo_id != '') {
			$_SESSION['msjerror'] = $lang['SelOperador'];
			redirigir("recibosrh.php?accion=consultar&recibo_id=" . $recibo_id);
		} elseif($adelanto == '') {
			$_SESSION['msjerror'] = $lang['SelOperador'];
			redirigir('recibosrh.php?accion=listar&feini=' . $feini . '&fefin=' . $fefin . '&operador=' . $operador);
		}
	}
	
	echo '	
		<form action="recibosrh.php?accion=procesapago" method="post" enctype="multipart/form-data">
		<table cellpadding="0" cellspacing="0" border="0" class="agrega">
			<tr>
				<td colspan="2"><span class="alerta">
					' . $_SESSION['rec']['mensaje'] . '</span>
				</td>
			</tr>'."\n";
	// --- Seleccionamos si es un recibo o un pago adelantado ---
	if($pago_recibo == 1) {
		echo '
			<tr class="cabeza_tabla">
				<td colspan="2">
					'. $lang['Recibo:'] . $recibo_id . '
				</td>
			</tr>';	
	} else {
		$adelanto = 1;
		echo '
			<tr class="cabeza_tabla">
				<td colspan="2">
					Registrar Pago adelantado a ' . $usu[$operador]['nom'] . ' ' . $usu[$operador]['ape'] . '
				</td>
			</tr>
			<input type="hidden" name="adelanto" value="' . $adelanto . '" />'."\n";
	}
	
	unset($_SESSION['rec']['mensaje']);
	if(!isset($por_pagar) || $por_pagar == '') { $por_pagar = $_SESSION['rec']['por_pagar']; } 
	$por_pagar = round($por_pagar, 2);
	echo '		
			<tr>
				<td>'. $lang['Monto de pago'].'</td>
				<td style="text-align:left;"><input type="text" name="pagar" value="' . $por_pagar . '" size="10" style="text-align:right;"/></td>
			</tr>
			<tr>
				<td>'. $lang['Fecha del pago'].'</td>
				<td style="text-align:left;">';

	require_once("calendar/tc_calendar.php");

		//instantiate class and set properties
		$myCalendar = new tc_calendar("fechapago", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2011, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();	  

	echo '
				</td>
			</tr>
			<tr>
				<td>'. $lang['Método de Pago'].'</td>
				<td style="text-align:left;">
					<select name="forma_pago" size="1">
						<option value="" > '. $lang['Seleccione'].'</option>
						<option value="1" >' . TIPO_PAGO_1 . '</option>
						<option value="2" >' . TIPO_PAGO_2 . '</option>
						<option value="3" >' . TIPO_PAGO_3 . '</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>'. $lang['Banco'].'</td>
				<td style="text-align:left;"><input type="text" name="banco" size="30" value="' . REC_RH_BANCO . '" /></td>
			</tr>
			<tr>
				<td>'. $lang['Cuenta'].'</td>
				<td style="text-align:left;"><input type="text" name="cuenta" size="30" value="' . REC_RH_CUENTA . '" /></td>
			</tr>
			<tr>
				<td>'. $lang['Num cheque o transferencia'].'</td>
				<td style="text-align:left;"><input type="text" name="referencia" size="15" /></td>
			</tr>
			<tr>
				<td>'. $lang['comprobante de pago'].'</td>
				<td style="text-align:left;"><input type="file" name="comprobante" size="30" /></td>
			</tr>'."\n";	
	if($recibo_id == '') {
		$retorno = 'recibosrh.php?accion=listar&feini=' . $feini . '&fefin=' . $fefin . '&operador=' . $operador;
	} else {
		$retorno = 'recibosrh.php?accion=consultar&recibo_id=' . $recibo_id;	
	}
	
	echo '		
			<tr>
				<td colspan="2" style="text-align:left;"><div class="control"><a href="' . $retorno . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar al Pedido'].'" title="'. $lang['Regresar al Recibo'].'"></a></div></td>
			</tr>
			<tr class="cabeza_tabla">
				<td colspan="2"><input type="hidden" name="operador" value="' . $operador . '" /></td>
			</tr>
			<tr>
				<td colspan="2"><input type="hidden" name="recibo_id" value="' . $recibo_id . '" /><input type="hidden" name="por_pagar" value="' . $por_pagar . '" /></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:left;"><input type="submit" value="'. $lang['Enviar'].'" />&nbsp;<input type="reset" name="limpiar" value="'. $lang['Borrar datos'].'" /></td>
			</tr>
	</table>
	</form>';
}

elseif($accion === 'descuentos') {
	
	if(validaAcceso('1110015', $dbpfx)=='1' || ($solovalacc != '1' && ($_SESSION['codigo'] <= '12'))) {
		$mensaje = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso para Administradores']);
	}

	if($operador == "" || $operador == "Seleccione") {
		if($recibo_id != '') {
			$_SESSION['msjerror'] = $lang['SelOperador'];
			redirigir("recibosrh.php?accion=consultar&recibo_id=" . $recibo_id);
		} else {
			$_SESSION['msjerror'] = $lang['SelOperador'];
			redirigir("recibosrh.php?accion=listar");
		}
	}

	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		
				<div id="principal">
				<form action="recibosrh.php?accion=procesa_desc" method="post" enctype="multipart/form-data">
				<table cellpadding="0" cellspacing="0" border="0" class="agrega">
					<tr>
						<td colspan="2"><span class="alerta">' . $_SESSION['rec']['mensaje'] . '</span></td>
					</tr>
					<tr class="cabeza_tabla">
						<td colspan="2">
							' . $lang['Registrar Descuento a '] . $usu[$operador]['nom'] . ' ' . $usu[$operador]['ape'] . '<input type="hidden" name="operador" value="' . $operador . '" /><input type="hidden" name="recibo_id" value="' . $recibo_id . '" />
						</td>
					</tr>'."\n";
	unset($_SESSION['rec']['mensaje']);
	echo '			
					<tr>
						<td>' . $lang['Monto del descuento'] . '</td>
						<td style="text-align:left;"><input type="text" name="monto_descuento" value="' . $por_pagar . '" size="10" style="text-align:right;"/></td>
					</tr>
					<tr>
						<td>' . $lang['Fecha del descuento'] . '</td>
						<td style="text-align:left;">';

	require_once("calendar/tc_calendar.php");

		//instantiate class and set properties
		$myCalendar = new tc_calendar("fecha_desc", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2017, 2025);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();	  
	
	echo '
						</td>
					</tr>
					<tr>
						<td>' . $lang['Motivo del descuento'] . '</td>
						<td style="text-align:left;"><textarea name="motivo" rows="4" cols="40" placeholder="Explicación del descuento"></textarea></td>
					</tr>'."\n";
	
	if($recibo_id == '' || $recibo_id == 0) {
		$retorno = 'recibosrh.php?accion=listar&feini=' . $feini . '&fefin=' . $fefin . '&operador=' . $operador;
	} else {
		$retorno = 'recibosrh.php?accion=consultar&recibo_id=' . $recibo_id;	
	}
	echo '			
					<tr>
						<td colspan="2" style="text-align:left;">
							<div class="control"><a href="'. $retorno . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar al Pedido'].'" title="'. $lang['Regresar al Recibo'].'"></a></div>
						</td>
					</tr>
					<tr class="cabeza_tabla">
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><input type="hidden" name="por_pagar" value="' . $por_pagar . '" /></td>
						<td colspan="2"><input type="hidden" name="adelantado" value="' . $adelantado . '" /></td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:left;"><input type="submit" value="'. $lang['Enviar'].'" /></td>
					</tr>
				</table>
				</form>'."\n";
}

elseif($accion === 'procesa_desc') {
	
	if(validaAcceso('1110015', $dbpfx)=='1') {
		$mensaje = $lang['Acceso autorizado'];
	} elseif ($solovalacc != '1' && ($_SESSION['codigo'] <= '12')) {
		$mensaje = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso para Administradores']);
	}
	
	$mensaje = '';
	$error = 'no'; 
	$monto_descuento = limpiarNumero($monto_descuento); 
	$motivo = preparar_entrada_bd($motivo); 

	if($adelantado == '1'){
		if($monto_descuento > $por_pagar) {$error = 'si'; $mensaje .= 'El monto no puede ser mayor al total del recibo.<br>';}	
	}
	if($monto_descuento < 0) {$error = 'si'; $mensaje .= 'El monto no puede ser negativo.<br>';}
	if($monto_descuento == 0 || $monto_descuento == '') {$error = 'si'; $mensaje .= 'El monto no puede ser cero.<br>';}
	if($motivo == '') {$error = 'si'; $mensaje .= 'Debe registrar un motivo para el descuento.<br>';}
	if($recibo_id == '') { $recibo_id = 0; }
	
	if($error == 'no') {
		$sql_data_array = [
			'recibo_id' => $recibo_id,
			'pago_monto' => $monto_descuento,
			'pago_fecha' => $fecha_desc,
			'usuario' => $_SESSION['usuario'],
			'usuario_pago_recibido' => $operador,
			'motivo' => $motivo,
			'descuento' => 1,
			'pago_monto_origen' => $monto_descuento
		];
		ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array);
		$mensaje = 'Registro de descuento exitoso al operario ' . $usu[$operador]['nom'] . ' ' . $usu[$operador]['ape'] . ' en el recibo: ' . $recibo_id;
		$_SESSION['msjerror'] = $mensaje;
		
		if($recibo_id == 0) {
			redirigir('recibosrh.php?accion=consulta_pag_desc&feini=' . $feini . '&fefin=' . $fefin . '&operador=' . $operador);
		} else {
			// --- si hay recibo se actualiza el monto pagado ---
			$preg_recibo = "SELECT pagado FROM " . $dbpfx . "destajos WHERE recibo_id = '" . $recibo_id . "'";
			$matr_recibo = mysql_query($preg_recibo) or die("ERROR: Fallo selección de RECIBO! " . $preg_recibo);
			$info_recibo = mysql_fetch_assoc($matr_recibo);
			$nuevo_pagado = $info_recibo['pagado'] + $monto_descuento;
			unset($sql_data_array);
			$sql_data_array = [
				'pagado' => $nuevo_pagado,
			];
			$para = " recibo_id = '" . $recibo_id ."'";
			ejecutar_db($dbpfx . 'destajos', $sql_data_array, 'actualizar' , $para);
			redirigir('recibosrh.php?accion=consultar&recibo_id=' . $recibo_id);
		}
	} else {
		$_SESSION['rec']['mensaje'] = $mensaje;
		redirigir('recibosrh.php?accion=descuentos&recibo_id=' . $recibo_id . '&feini=' . $feini . '&fefin=' . $fefin . '&por_pagar=' . $por_pagar . '&operador=' . $operador .'&adelantado=' . $adelantado);
	}
}

elseif($accion === 'desasociar_desc') {
/*	
	$funnum = 1110015;
	
// ----------> Validar acceso a recibos de Destajos
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	
	if ($_SESSION['codigo'] <= '12' || $retorno == 1) {
		
	} else {
		redirigir('usuarios.php?mensaje='. $lang['Acceso sólo Gerente']);
	}
	
	if($eliminar == 1) {
			$sql_data_array = [
				'recibo_id' => 'null',
			];
			$parametros = " pago_id ='" . $pago_id . "'";
			ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'actualizar', $parametros);
			redirigir("recibosrh.php?accion=consultar&recibo_id=" . $recibo_id);
			
	} else {
		echo '
		<h2>' . $lang['¿Estás seguro que quieres desasociar el descuento '] . $pago_id . $lang['del recibo '] . $recibo_id . '?</h2>
		<table>
			<tr>
				<td><a href="recibosrh.php?accion=desasociar_desc&pago_id=' . $pago_id . '&eliminar=1&recibo_id=' . $recibo_id . '&monto=' . $monto . '&de_usuario=' . $de_usuario . '"><button type="button">' . $lang['SI, desacociar pago'] . '</button></a></td>
				<td><a href="recibosrh.php?accion=consultar&recibo_id=' . $recibo_id . '"><button type="button">' . $lang['NO, regresar'] . '</button></a></td>
			</tr>
		</table>'."\n";
	}
*/
}

elseif($accion === 'eliminar_pago') {
	
	$funnum = 1110015;
// ----------> Validar acceso a recibos de Destajos
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($_SESSION['codigo'] <= '12' || $retorno == '1') {
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso sólo Gerente']);
	}
	
	if($eliminar == 1) {
			$parametros = " pago_id ='" . $pago_id . "'";
			ejecutar_db($dbpfx . 'destajos_pagos', '', 'eliminar', $parametros);
			redirigir("recibosrh.php?accion=consultar&recibo_id=" . $recibo_id);
	} else {
		echo '
		<h2>¿Estás seguro que quieres eliminar el pago ' . $pago_id . '?</h2>
		<table>
			<tr>
				<td>
				<a href="recibosrh.php?accion=eliminar_pago&pago_id=' . $pago_id . '&eliminar=1&recibo_id=' . $recibo_id . '&monto=' . $monto . '&de_usuario=' . $de_usuario . '"><button type="button" class="btn btn-success">SI, eliminar pago</button></a></td>
				<td><a href="recibosrh.php?accion=consultar&recibo_id=' . $recibo_id . '"><button type="button" class="btn btn-danger">NO, regresar</button></a></td>
			</tr>
		</table>'."\n";
	}
}

elseif($accion === 'eliminar_recibo') {

// ----------> Validar acceso a recibos de Destajos
	if (validaAcceso('1110020', $dbpfx) == 1) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['Acceso NO autorizado'];
		redirigir('recibosrh.php?accion=consultar&recibo_id=' . $recibo_id);
	}

	if($eliminar == 1) {
// ------ Liberar tareas de recibo ---
			unset($sql_data_array);
			$sql_data_array['recibo_id'] = 'null';
			$parametros = " recibo_id ='" . $recibo_id . "'";
			ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
// ------ Limpiar recibo ---
			unset($sql_data_array);
			$sql_data_array = [
				'monto' => 0,
				'impuesto' => 0,
				'monto_cons' => 0,
				'impuesto_cons' => 0,
				'pagado' => 0,
				'fecha_pagado' => date('Y-m-d H:i:s', time()),
				'saldado' => 2 // -- cancelado --
			];
			$parametros = " recibo_id ='" . $recibo_id . "'";
			ejecutar_db($dbpfx . 'destajos', $sql_data_array, 'actualizar', $parametros);
			unset($sql_data_array);
// ------ Eliminar elementos del destajo --
			$preg_elementos = "SELECT * FROM " . $dbpfx . "destajos_elementos WHERE recibo_id = '" . $recibo_id . "'";
			$matr_elementos = mysql_query($preg_elementos) or die("ERROR: Fallo selección de elementos!");
			while($con_elem = mysql_fetch_array($matr_elementos)){
//				$bitacora = 'El usuario ' . $_SESSION['usuario'] . ' eliminó el destajo ' . $con_elem['destajo_id'] . ' de la orden ' . $con_elem['orden_id'] . ' con reporte ' . $con_elem['reporte'] . ' del área ' . $con_elem['area'] . ' del operario ' . $con_elem['operador'] . ' con un monto de ' . $con_elem['monto'];
				$bitcom = 'Eliminé el destajo ' . $con_elem['destajo_id'] . ' con un monto de ' . $con_elem['monto'] . ' del reporte ';
				if($con_elem['reporte'] == 0 || $con_elem['reporte'] == '') { $bitcom .= 'Particular'; }
				else { $bitcom .= $con_elem['reporte']; }
				$bitcom .= ' del área ' . constant('NOMBRE_AREA_' . $con_elem['area']) . ' para el operario ' . $con_elem['operador'];
				bitacora($con_elem['orden_id'], $bitcom, $dbpfx);
				$parametros = " destajo_id ='" . $con_elem['destajo_id'] . "'";
				ejecutar_db($dbpfx . 'destajos_elementos', '', 'eliminar', $parametros);
				$operador = $con_elem['operador'];
			}
// ----- Eliminar comisiones ---
			$parametros = " recibo_id ='" . $recibo_id . "'";
			ejecutar_db($dbpfx . 'comisiones', '', 'eliminar', $parametros);
			bitacora('0', 'Se canceló el recibo ' . $recibo_id, $dbpfx);

			$_SESSION['msjerror'] = 'Se eliminó el recibo ' . $recibo_id;
			redirigir("recibosrh.php?accion=listar&operador=" . $operador);
	} else {
		echo '
			<h2>¿Estás seguro que quieres eliminar el recibo ' . $recibo_id . '?</h2>
			<table>
				<tr>
					<td>
					<a href="recibosrh.php?accion=eliminar_recibo&eliminar=1&recibo_id=' . $recibo_id . '"><button type="button" class="btn btn-success">SI, eliminar recibo</button></a></td>
					<td><a href="recibosrh.php?accion=consultar&recibo_id=' . $recibo_id . '"><button type="button" class="btn btn-danger">NO, regresar</button></a></td>
				</tr>
			</table>'."\n";
	}
}

elseif ($accion === "procesapago") {
	
	$funnum = 1110010;
	
// ----------> Validar acceso a recibos de Destajos
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	
	if ($_SESSION['codigo'] <= '12' || $retorno == 1) {
		include('idiomas/' . $idioma . '/recibosrh.php');
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso sólo Gerente']);
	}
	
	unset($_SESSION['rec']);
	$_SESSION['rec'] = array();
	$_SESSION['rec']['mensaje']='';
	$mensaje = '';
	$error = 'no'; 
	$pagar = limpiarNumero($pagar); $_SESSION['rec']['pagar'] = $pagar;
	$banco = preparar_entrada_bd($banco); $_SESSION['rec']['banco'] = $banco;	
	$cuenta = preparar_entrada_bd($cuenta); $_SESSION['rec']['cuenta'] = $cuenta;	
	$referencia = preparar_entrada_bd($referencia); $_SESSION['rec']['referencia'] = $referencia;
	$_SESSION['rec']['por_pagar'] = $por_pagar;
	
//	echo $forma_pago;
	
	if($pagar <= 0 || $pagar == '') {$error = 'si'; $mensaje .=$lang['monto del pago no puede ser cero'];}
	if($adelanto != 1 && $pagar > $por_pagar) {$error = 'si'; $mensaje .=$lang['monto del pago no puede ser superior al monto pendiente por pagar'];}
	if($forma_pago == '' || !isset($forma_pago)) {$error = 'si'; $mensaje .= $lang['forma de pago'];}
	if($forma_pago > 1 && ($banco == '' || $cuenta == '')) {$error = 'si'; $mensaje .= $lang['indicar Banco y Cuenta para la forma de pago'];}
	if($forma_pago > 1 && $referencia == '') {$error = 'si'; $mensaje .=$lang['Num cheque o transferencia'];}
	if($recibo_id == '') { $recibo_id = 0; }

	if($error === 'no') {
		$sql_data_array = [
			'recibo_id' => $recibo_id,
			'pago_monto' => $pagar,
			'pago_tipo' => $forma_pago,
			'pago_banco' => $banco,
			'pago_cuenta' => $cuenta,
			'pago_referencia' => $referencia,
			'pago_fecha' => $fechapago,
			'usuario' => $_SESSION['usuario'],
			'usuario_pago_recibido' => $operador,
			'pago_monto_origen' => $pagar,
		];
		$pago_id = ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array);
		if($adelanto != 1) {
			$msgbit = 'Comprobante de pago de destajo recibo ' . $recibo_id;
		} else {
			$msgbit = 'Comprobante de pago adelantado  ' . $pago_id;
			$mensaje .= 'Registro de pago adelantado exitoso al operario ' . $usu[$operador]	['nom'] . ' ' . $usu[$operador]['ape'];
		}
		$nombre = $_FILES['comprobante']['name'];
		unset($sql_data_array);
		$subir = agrega_documento($orden_id, $_FILES['comprobante'], $msgbit, $dbpfx, $pago_id); 
		$sql_data_array['pago_documento'] = $subir['nombre'];
		$parametros = " pago_id ='" . $pago_id . "'";
		ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'actualizar', $parametros);
		bitacora($orden_id, $msgbit, $dbpfx);
		unset($sql_data_array);
		unset($_SESSION['rec']);
		if($adelanto != '1') {
			$coloca = "UPDATE " . $dbpfx . "destajos SET pagado = pagado + '$pagar'";
			if($pagar == $por_pagar) {
				// ----- CONSULTAR Y SALDAR COMISONES ------
				$preg_comisiones = "SELECT com_id, recibo_id, usuario, monto, orden_id FROM " . $dbpfx . "comisiones WHERE recibo_id ='" . $recibo_id . "'";
				$matr_comisones = mysql_query($preg_comisiones) or die("ERROR: Fallo selección de comisiones! " . $preg_comisiones);
				$total_comisones = mysql_num_rows($matr_comisones);
				if($total_comisones > 0){
					while($comisiones = mysql_fetch_array($matr_comisones)){
						$sql_data = [
						'estatus' => 30,
						];
						$parametros = "com_id = '" . $comisiones['com_id'] . "'";
						ejecutar_db($dbpfx . 'comisiones', $sql_data, 'actualizar', $parametros);
						// --- Reg. en Bitacora ---
						$bitacora = 'Se pago la comisión ' . $comisiones['com_id'] . ' del usuario ' . $comisiones['usuario'] . ' en el recibo de destajo ' . $comisiones['recibo_id'] . ' con un monto de $' . number_format($comisiones['monto'],2);
						bitacora($comisiones['orden_id'], $bitacora, $dbpfx);
					}
				}
				$coloca .= ", fecha_pagado = '" . date('Y-m-d H:i:s', time()) . "', saldado = 1";
				bitacora($orden_id, 'Pago Total de Destajo en Recibo '.$recibo_id, $dbpfx);
			}
			$coloca .= " WHERE recibo_id = $recibo_id";
			$graba = mysql_query($coloca) or die("ERROR: Fallo actualización de destajo! " . $graba);
			$archivo = '../logs/' . time() . '-base.ase';
			$myfile = file_put_contents($archivo, $coloca . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
			redirigir('recibosrh.php?accion=consultar&recibo_id=' . $recibo_id);
		} else {
			redirigir('recibosrh.php?accion=consulta_pag_desc&operador=' . $operador);
		}		
	} else {
		$_SESSION['msjerror'] = $mensaje;
		if($adelanto == 1){
			redirigir('recibosrh.php?accion=pagar&adelanto=1&operador=' . $operador);
		} else{
			redirigir('recibosrh.php?accion=pagar&recibo_id=' . $recibo_id . '&operador=' . $operador . '&pago_recibo=1');	
		}
	}
}

?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
