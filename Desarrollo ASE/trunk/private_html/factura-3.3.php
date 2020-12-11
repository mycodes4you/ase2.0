<?php 

include('parciales/funciones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

include('parciales/numeros-a-letras.php');
include('idiomas/' . $idioma . '/factura.php');
include('parciales/metodos-de-pago-3.3.php');
$agencia_regimen = $agencia_reg33;

if ($accion==="consultar") {

	if(validaAcceso('1095000', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1'))) {
//		$mensaje = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=' . $lang['AccesoNegado']);
	}

	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '	<div id="principal">';
	$error = 'no'; $num_cols = 0; $mensaje = '';
	$preg0 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE ";
	if($reporte != '') {
		$preg0 .= "sub_reporte = '$reporte' AND ";
	}
	if($orden_id!='') {
		$preg0 .= "orden_id = '$orden_id' AND ";
	} elseif($previa_id!='') {
		$preg0 .= "previa_id = '$previa_id' AND ";
	} else {
		$error = 'si';
		$mensaje .= $lang['RefNoValida'] . '<br>';
	}
	$preg0 .= "sub_estatus < '190'";
//	$preg0 .= " AND (fact_id IS NULL OR fact_id < '1')";
	$preg0 .= " GROUP BY sub_reporte";
	$mat0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!".$preg0);
	$num_rep = mysql_num_rows($mat0);

	$preg1 = "SELECT fact_num FROM " . $dbpfx . "facturas WHERE fact_serie = '" . $valor['factserie'][1] . "' ORDER BY fact_num DESC LIMIT 1";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección!");
	$facturas = mysql_num_rows($matr1);
	$alerta = '';
	if($facturas > 0) {
		$fact = mysql_fetch_array($matr1);
		$fact_num = $fact['fact_num'] + 1;
		if($valor['timbres'][0] > 0) {
			$alerta = $lang['Quedan'] . ' ' . $valor['timbres'][0] . ' ' . $lang['TimbresDisp'];
		} elseif($valor['timbres'][0] <= 0) {
			$error = 'si';
			$mensaje .= $lang['TimbresAgot'] . '<br>';
		}
	} else {
		$fact_num = $valor['factinicial'][0];
	}

	if ($num_rep > 0 && $error ==='no') {
		$mensaje = '';
//		unset($_SESSION['fact']);
		if ($num_rep > 1) {
			echo '	<form action="factura-3.3.php?accion=consultar" method="post" enctype="multipart/form-data" name="imprime">'  . "\n";
   	  	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
   	  	echo '		<tr><td style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">' . $lang['ExisVarServ'] . '</td></tr>' . "\n";
     		echo '		<tr><td><select name="reporte" size="1">' . "\n";
			echo '			<option value="" >' . $lang['Seleccione...'] . '</option>';
	     	while($rep = mysql_fetch_array($mat0)) {
	     		if($rep['sub_reporte'] == '') { $rep['sub_reporte'] = '0'; }
   	  		echo '			<option value="' . $rep['sub_reporte'] . '">';
   	  		if($rep['sub_reporte'] == '0') { echo $lang['Particular']; } else { echo $rep['sub_reporte']; } 
   	  		echo '</option>' . "\n";
			}
			echo '		</select></td></tr>' . "\n";
			echo '		<input type="hidden" name="orden_id" value="' . $orden_id . '" />';
			echo '		<input type="hidden" name="previa_id" value="' . $previa_id . '" />';
			echo '		<tr><td><input type="submit" value="' . $lang['Enviar'] . '" /></td></tr>'."\n";
			echo '		</table></form>'."\n";
		} else {
			$rep = mysql_fetch_array($mat0);
			echo '	<form action="factura-3.3.php?accion=confirma" method="post" enctype="multipart/form-data" name="imprime">'  . "\n";
			echo '	<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">'."\n";
			if($alerta != '') {
				echo '		<tr><td><span class="alerta">' . $alerta . '</span></td></tr>'."\n";
			}
			echo '		<tr class="cabeza_tabla"><td>' . $lang['FacturaPara'] . ' '."\n";
			if($orden_id != '') {
				echo $lang['LaOT'] . ' ' . $orden_id;
			} else {
				echo $lang['ElPP'] . ' ' .$previa_id;
			}
			echo '</td></tr>'."\n";
			echo '		<input type="hidden" name="orden_id" value="' . $orden_id . '" />';
			echo '		<input type="hidden" name="previa_id" value="' . $previa_id . '" />';
			echo '		<tr><td>' . $lang['UtilSer0'] . ': ' . $valor['factserie'][1] . $lang['UtilSer1'] . ': <input type="text" name="fact_num" value="' . $fact_num . '" size="8" /></td></tr>'."\n";
			echo '		<tr class="cabeza_tabla"><td>' . $lang['EstilosFacturar'] . '</td></tr>'."\n";
			echo '		<tr><td><input type="radio" name="desglose" value="0">' . $lang['Desglose0'] . '</td></tr>'."\n";
			echo '		<tr><td><input type="radio" name="desglose" value="2" checked="checked">' . $lang['Desglose2'] . '</td></tr>'."\n";
			echo '		<tr><td><input type="radio" name="desglose" value="1">' . $lang['Desglose1'] . '</td></tr>'."\n";
			echo '		<tr><td><input type="radio" name="desglose" value="3">' . $lang['Desglose3'] . '</td></tr>'."\n";
			echo '		<tr><td><input type="radio" name="desglose" value="4">' . $lang['Desglose4'] . '</td></tr>'."\n";
			echo '		<tr><td><input type="radio" name="desglose" value="5">' . $lang['Desglose5'] . '</td></tr>'."\n";
						
			echo '		<tr class="cabeza_tabla"><td>' . $lang['SelTareaFact'];
			if($rep['sub_reporte'] == '0') {
				echo ' ' . $lang['TrabPart'];
			} else {
				echo ' ' . $lang['Siniestro'] . ' ' .$rep['sub_reporte']; 
			} 
			echo '</td></tr>'."\n";
			$preg2 = "SELECT sub_orden_id, sub_descripcion, sub_area, sub_estatus, sub_presupuesto FROM " . $dbpfx . "subordenes WHERE ";
			if ($orden_id!='') {
				$preg2 .= "orden_id = '$orden_id' ";
			} else {
				$preg2 .= "previa_id = '$previa_id' ";
			}
			$preg2 .= "AND sub_reporte = '" . $rep['sub_reporte'] . "' AND sub_estatus < '130' AND (fact_id IS NULL OR fact_id < '1') AND sub_presupuesto != '0'";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección! " . $preg2);
//			echo $preg2;
			echo '		<tr><td>'."\n";
			echo '			<table cellpadding="0" cellspacing="0" border="1" class="agrega" width="100%">';
			echo '				<tr><td>Tarea</td><td>Area</td><td>Descripción</td><td>Estatus</td><td>Monto</td><td>Facturar?</td></tr>'."\n";
			while($sub = mysql_fetch_array($matr2)) {
				echo '				<tr><td>' . $sub['sub_orden_id'] . '</td><td>' . constant('NOMBRE_AREA_' . $sub['sub_area']) . '</td><td>' . $sub['sub_descripcion'] . '</td><td>';
				if($sub['sub_estatus'] < 112 || $sub['sub_estatus'] > 115) {
					echo 'Sin terminar';
				} else {
					echo 'Terminada';
				}
				echo '</td><td>$' . number_format($sub['sub_presupuesto'],2) . '</td><td><input type="checkbox" name="tarea[]" value="' . $sub['sub_orden_id'] . '" checked="checked" /></td></tr>'."\n";
			}
			echo '			</table>'."\n";
			echo '		<input type="hidden" name="reporte" value="' . $rep['sub_reporte'] . '" />';
			echo '		<input type="hidden" name="aseguradora" value="' . $rep['sub_aseguradora'] . '" />';
			echo '		<input type="hidden" name="orden_id" value="' . $orden_id . '" />';
			echo '		<input type="hidden" name="previa_id" value="' . $previa_id . '" />';
			echo '		</td></tr>'."\n";
			echo '		<tr><td><input type="submit" value="Enviar" /></td></tr>'."\n";
			echo '		</table></form>'."\n";
//			$rep = mysql_fetch_array($mat0);
//			echo '		<input type="hidden" name="dato" value="' . $rep['sub_reporte'] . '|' . $rep['sub_aseguradora'] . '" />';
		}
//		echo $rep['sub_reporte'];
	} else {
//		$mensaje = 'No hay conceptos por facturar.';
		echo '<p>' . $mensaje . '</p>';
	}
	echo '		<div class="control">';
	if($orden_id != '') {
		echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>';
	} else {
		echo '<a href="previas.php?accion=consultar&previa_id=' . $previa_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar al Presupuesto" title="Regresar al Presupuesto"></a>';
	}
	echo '		</div>'."\n";
}

elseif($accion==='confirma') {

	if(validaAcceso('1095005', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1'))) {
		$mensaje = 'Acceso autorizado';
	} else {
		$_SESSION['msjerror'] = $lang['AccesoNegado'] ;
		if($orden_id != '') {
			redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id);
		} else {
			redirigir('previas.php?accion=consultar&previa_id=' . $previa_id);
		}
	}

	$error = 'no'; $mensaje= '';
	$sumdto = 0;
	$maxmp = count($metodossat);
	$fact_num = limpiarNumero($fact_num);

	if($fact_num == '' || !isset($fact_num)) {
		$error = 'si';
		$mensaje .= $lang['FaltaFolio'] . '<br>';
	} else {
		$_SESSION['fact']['fact_num'] = $fact_num;
	}

	if($Recalcula == 'Recalcula') {
		if($metop == '') {
			$error = 'si'; $mensaje .= $lang['FaltaMP'] . '<br>'; $Recalcula = '';
		}
		foreach($dtoext as $sdto) {
			$sumdto = $sumdto + $sdto;
		}
		if($motivo == '' && $sumdto > 0) {
			$error = 'si'; $mensaje .= $lang['DesSiMotNo'] . '<br>'; $Recalcula = '';
		}
		if($RfcAlternos > 0 && $cualrfc == '') {
			$error = 'si'; $mensaje .= $lang['NoRfc'] . '<br>'; $Recalcula = '';
		}
	}

	$total_ref = 0; $total_con = 0; $total_mo = 0; $dedu = 0;
	foreach($tarea as $v) {
		$preg0 = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$v'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Subordenes! " . $preg0);
		$sub = mysql_fetch_array($matr0);
		$total_ref = $total_ref + $sub['sub_partes'];
		$total_con = $total_con + $sub['sub_consumibles'];
		$total_mo = $total_mo + $sub['sub_mo'];
		if($sub['sub_refacciones_recibidas']=='1' && $factsinpend == '1') {
			$mensaje .= $lang['RefPorReci'] . '<br>';
			$error = 'si';
		}
		if($sub['sub_estatus'] < '112' && $factsinpend == '1' && $orden_id != '') {
			$mensaje .= $lang['TareasPorTerm'] . '<br>';
			$error = 'si'; 
		}
		if($sub['sub_poliza'] != '') { $poliza = $sub['sub_poliza']; }
		if($sub['sub_deducible'] > $dedu) { $dedu = round($sub['sub_deducible'], 2); }
	}
	$dedu = number_format($dedu, 2, '.', '');
	$subtotal = $total_ref + $total_con + $total_mo;

	if($subtotal <= '0') {
		$error = 'si';
		$mensaje .= $lang['ValCero'] . '<br>';
	}

//	  	unset($datosv);
	if($aseguradora < 1) {
			if($orden_id != '') {
				$pregunta2 = "SELECT e.empresa_razon_social, e.empresa_rfc, e.empresa_calle, e.empresa_ext, e.empresa_int, e.empresa_colonia, e.empresa_cp, e.empresa_municipio, e.empresa_estado, e.empresa_pais, e.empresa_descuento, c.cliente_id FROM " . $dbpfx . "ordenes o, " . $dbpfx . "empresas e, " . $dbpfx . "clientes c  WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id AND c.cliente_empresa_id = e.empresa_id";
			} else {
				$pregunta2 = "SELECT e.empresa_razon_social, e.empresa_rfc, e.empresa_calle, e.empresa_ext, e.empresa_int, e.empresa_colonia, e.empresa_cp, e.empresa_municipio, e.empresa_estado, e.empresa_pais, e.empresa_descuento, c.cliente_id FROM " . $dbpfx . "previas o, " . $dbpfx . "empresas e, " . $dbpfx . "clientes c  WHERE o.previa_id = '$previa_id' AND o.previa_cliente_id = c.cliente_id AND c.cliente_empresa_id = e.empresa_id";
			}
   		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!".$pregunta2);
   		$cli = mysql_fetch_assoc($matriz2);
			foreach($cli as $rfi => $rfd) {
				if($rfd == '' && $rfi != 'empresa_int') {
					$error = 'si';
					$_SESSION['msjerror'] = $lang['DatosReceptor'] . ' ' . $lang['Incompletos'] . ': ' . $rfi;
					redirigir('personas.php?accion=consultar&cliente_id=' . $cli['cliente_id']);
				}
			}
   		$cliente = array(
   			'nombre' => strtoupper($cli['empresa_razon_social']), 
   			'calle' =>  strtoupper($cli['empresa_calle']), 
   			'numext' =>  strtoupper($cli['empresa_ext']), 
   			'numint' =>  strtoupper($cli['empresa_int']), 
   			'colonia' =>  strtoupper($cli['empresa_colonia']), 
   			'cp' =>  $cli['empresa_cp'], 
   			'municipio' =>  strtoupper($cli['empresa_municipio']), 
   			'estado' =>  strtoupper($cli['empresa_estado']), 
   			'pais' =>  strtoupper($cli['empresa_pais']), 
   			'descuento' =>  $cli['empresa_descuento'], 
   			'rfc' =>  strtoupper($cli['empresa_rfc'])
   		);

// ------ Ajusta datos para venta de mostrador ------
			if($desglose == 4) {
				$cliente['nombre'] = $lang['VtaMosNom'];
				$cliente['rfc'] = $lang['VtaMosRfc'];
			}

	} else {
			$pregunta2 = "SELECT aseguradora_razon_social, aseguradora_calle, aseguradora_ext, aseguradora_int, aseguradora_colonia, aseguradora_cp, aseguradora_municipio, aseguradora_estado, aseguradora_pais, aseguradora_descuento, aseguradora_rfc, aseguradora_addenda FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $aseguradora . "'";
   		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion! " . $pregunta2);
   		$ase = mysql_fetch_assoc($matriz2);
			foreach($ase as $rfi => $rfd) {
				if($rfd == '' && $rfi != 'aseguradora_int' && $rfi != 'aseguradora_addenda') {
					$error = 'si';
					$_SESSION['msjerror'] = $lang['DatosReceptor'] . ' ' . $lang['Incompletos'] . ': ' . $rfi;
					redirigir('aseguradoras.php?accion=consultar&aseguradora_id=' . $aseguradora);
				}
			}
   		$cliente = array(
   			'nombre' => $ase['aseguradora_razon_social'], 
   			'calle' =>  $ase['aseguradora_calle'], 
   			'numext' =>  $ase['aseguradora_ext'], 
   			'numint' =>  $ase['aseguradora_int'], 
   			'colonia' =>  $ase['aseguradora_colonia'], 
   			'cp' =>  $ase['aseguradora_cp'], 
   			'municipio' =>  $ase['aseguradora_municipio'],
   			'estado' =>  $ase['aseguradora_estado'],
   			'pais' =>  $ase['aseguradora_pais'],
   			'descuento' =>  $ase['aseguradora_descuento'],
   			'rfc' =>  $ase['aseguradora_rfc']
   		);
  			$preg3 = "SELECT c.cliente_nombre, c.cliente_apellidos FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo seleccion de poliza!");
			$asegurado = mysql_fetch_array($matr3);
	}

	if($error == 'no') {
		if($orden_id != '') {
			$datosv = datosVehiculo($orden_id, $dbpfx);
		} else {
		$datosv = datosVehiculo('', $dbpfx, $previa_id);
		}

		include('parciales/encabezado.php'); 
		echo '	<div id="body">' . "\n";
			include('parciales/menu_inicio.php');
		echo '	<div id="principal">' . "\n";
		echo '		<div >'."\n";
		if($Recalcula == 'Recalcula') {
			echo '		<form action="factura-3.3.php?accion=imprime" method="post" enctype="multipart/form-data" name="confirma" target="_blank">'."\n";
		} else {
			echo '		<form action="factura-3.3.php?accion=confirma" method="post" enctype="multipart/form-data" name="confirma" >'."\n";
			unset($_SESSION['fact']['facturada']);
		}
		echo '		<table cellpadding="0" cellspacing="0" border="0" width="840px" class="izquierda">
			<tr><td><strong><big>' . $lang['DatosEmisor'] . '</big></strong></td></tr>'."\n";
// ------ Busca si hay más de un RFC y si es así, presenta opciones
		if($Recalcula == '') {
			if($RfcAlternos > 0) {
				echo '			<tr><td style="width:210px;">' . $lang['SelEmisor'] . '</td><td><select name="cualrfc">'."\n";
				echo '				<option value="">' . $lang['Seleccione...'] . '</option>'."\n";
				foreach($Rfcs as $rfck => $rfcv) {
					echo '				<option value="' . $rfcv[0] . '|' . $rfcv[1] . '|' . $rfcv[2] . '|' . $rfcv[3] . '">' . $rfcv[1] . '</option>'."\n";
				}
				echo '			</select></td></tr>'."\n";
			} else {
				echo '			<tr><td style="width:210px;"><img src="imagenes/logo-agencia.png" alt="' . $agencia_razon_social . '"></td><td><strong>' . $agencia_razon_social . '</strong><input type="hidden" name="cualrfc" value="' . $agencia_rfc . '|' . $agencia_razon_social . '|' . $agencia_regimen . '|' . $agencia_cp . '" /></td></tr>
			<tr><td>' . $lang['RFC'] . ': </td><td>' . $agencia_rfc . '</td></tr>
			<tr><td>' . $lang['Regimen'] . ': </td><td>' . $agencia_regimen . '</td></tr>
			<tr><td>' . $lang['LugarEmision'] . ': </td><td>' . $agencia_cp . '</td></tr>'."\n";
			}
		} else {
			$rfcu = explode('|', $cualrfc);
			echo '			<tr><td style="width:210px;"><img src="imagenes/logo-agencia.png" alt="' . $rfcu[1] . '"></td><td><strong>' . $rfcu[1] . '</strong><input type="hidden" name="cualrfc" value="' . $cualrfc . '" /></td></tr>
			<tr><td>' . $lang['RFC'] . ': </td><td>' . $rfcu[0] . '</td></tr>
			<tr><td>' . $lang['Regimen'] . ': </td><td>' . $rfcu[2] . '</td></tr>
			<tr><td>' . $lang['LugarEmision'] . ': </td><td>' . $rfcu[3] . '</td></tr>'."\n";
		}
		echo '			<tr><td colspan="2"><hr></td><tr>
			<tr><td><strong><big>' . $lang['DatosReceptor'] . '</big></strong></td></tr>
			<tr><td>' . $lang['Cliente'] . ': </td><td>' . $cliente['nombre'] . '</td></tr>
			<tr><td>' . $lang['RFC'] . ': </td><td>' . $cliente['rfc'] . '</td></tr>
			<tr><td>' . $lang['UsoCFDi'] . ': <a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['usoCFDI'] . '&base=factura.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=700,height=250,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;"><small>' . $lang['¿Qué es esto?'] .'</small></a></td><td>'."\n";
		if($Recalcula != 'Recalcula' && $desglose == 4) {
			echo '					<input type="hidden" name="usocfdi" value="P01" />P01 ' . $usosdecfdi['P01'] . "\n";
		} elseif($Recalcula != 'Recalcula') {
			echo '					<select name="usocfdi" required/>'."\n";
			echo '						<option value="">' . $lang['Seleccione...'] . '</option>'."\n";
			foreach($usosdecfdi as $idx => $tex) {
				echo '						<option value="' . $idx . '"';
				if($usocfdi == $idx) { echo ' selected '; }
				echo ' >' . $idx . ' ' . $tex . '</option>'."\n";
			}
			echo '					</select>'."\n";
		} else {
			echo '					<input type="hidden" name="usocfdi" value="' . $usocfdi . '" />' . $usocfdi . ' ' . $usosdecfdi[$usocfdi] . "\n";
		}
		echo '				</td></tr>'."\n";
		echo '			<tr><td colspan="2"><hr></td>
			<tr><td><strong><big>' . $lang['DatosComprobante'] . '</big></strong></td></tr>
			<tr><td>' . $lang['Factura'] . '</td><td>' . $valor['factserie'][1] . $fact_num . '</td></tr>'."\n";
		if($aseguradora > 0 && $ase['aseguradora_addenda'] != '' ) {
			if(file_exists('particular/textos/' . $ase['aseguradora_addenda'])) {
				include_once('particular/textos/' .$ase['aseguradora_addenda']);
			}
		}

		echo '			<tr>
				<td>' . $lang['MetPag'] . ' <a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['metodopago'] . '&base=factura.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=700,height=250,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;"><small>' . $lang['¿Qué es esto?'] .'</small></a></td><td>'."\n";
		if($Recalcula != 'Recalcula') {
		   echo '					<select name="metodo_pago">'."\n";
		   foreach($metodosdepago as $mets => $dets) {
				echo '						<option value="' . $mets . '"';
				if($mets == $metodo_pago) { echo ' selected="selected" '; } 
				echo '>' . $mets . ' ' . $dets . '</option>'."\n";
			}
			echo '					</select>'."\n";
		} else {
			echo '<input type="hidden" name="metodo_pago" value="' . $metodo_pago . '" />' . $metodo_pago . ' ' . $metodosdepago[$metodo_pago] . "\n";
		}
	   echo '				</td>
			</tr>'."\n";

		echo '			<tr><td>' . $lang['FormaDePago'] . ' ' . $cmp . ': </td><td>'."\n";
		if($Recalcula != 'Recalcula') {
			echo '				<select name="metop" required/>'."\n";
			echo '					<option value="">' . $lang['SelFormPago'] . '</option>'."\n";
			foreach($metodossat as $mets => $dets) {
				echo '					<option value="' . $mets . '"';
				if($mets == $metop) { echo ' selected="selected" '; } 
				echo '>' . $mets . ' ' . $dets . '</option>'."\n";
			}
			echo '				</select>'."\n";
		} else {
			if($metodo_pago == 'PPD') { $metop = '99'; }
			echo '				<input type="hidden" name="metop" value="' . $metop . '" />'. $metop . ' ' . $metodossat[$metop] . "\n";
		}
		echo '			</td></tr>'."\n";

		echo '			<tr>
				<td>' . $lang['CondPago'] . ': <a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['condiciones_pago'] . '&base=factura.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=700,height=250,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;"><small>' . $lang['¿Qué es esto?'] .'</small></a></td><td>';
		if($Recalcula != 'Recalcula') {
			echo ' <input type="text" name="condpago" value="' . $condpago . '" />';
		} else {
			echo ' <input type="hidden" name="condpago" value="' . $condpago . '" />' . $condpago;
		}
		echo '</td></tr>'."\n";
	   
		echo '			<tr><td></td><td></td></tr>
			<tr><td colspan="2"><hr></td></tr>
			<tr><td colspan="2"><strong><big>' . $lang['ConceptosFacturar'] . '</big></strong></td></tr>
		</table>'."\n";
		echo '		<table cellpadding="3" cellspacing="0" border="1" width="840px" class="izquierda">'."\n";
		echo '			<tr>
				<td>Cantidad</td><td>Clave Prod. o Serv. <a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['Clave'] . '&base=factura.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=700,height=250,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;"><small>' . $lang['info'] .'</small></a></td>
				<td>Descripción</td>
				<td>Clave Unidad <a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['ClaveUnidad'] . '&base=factura.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=700,height=250,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;"><small>' . $lang['info'] .'</small></a></td>
				<td>Unidad</td>
				<td>Precio Unitario</td>
				<td>Descuento</td>
				<td>Subtotal</td></tr>'."\n";

switch($desglose) {
	case 5: //DESGLOSE5 DESCRIPCION DE TAREAS
			$j = 0;
			foreach($tarea as $v) {
				$preg5 = "SELECT sub_orden_id, sub_descripcion, sub_area, sub_estatus, sub_presupuesto FROM " . $dbpfx . "subordenes WHERE orden_id = $orden_id  AND sub_orden_id = $v AND sub_estatus < '130' AND sub_reporte = '$reporte' AND (fact_id IS NULL OR fact_id < '1') AND sub_presupuesto != '0' "; 
				$matr2 = mysql_query($preg5) or die("ERROR: Fallo selección! " . $preg5);
				
			while($sub = mysql_fetch_array($matr2)) {
				$precio_unitario = $sub['sub_presupuesto'];
				$suma = $suma + round($sub['sub_presupuesto'], 2);

				if($Recalcula != 'Recalcula') {
					echo '<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" size="4" value="1" size="2" />1</td>';
				}	else 	{
					echo '<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" size="4" value="1" size="2" />1</td>';
				}
				if($Recalcula != 'Recalcula') {
					echo '<td><input type="text" name="ClaveProdServ[' . $j . ']" value="78181501" size="6" required/></td>';
				}	else 	{
					echo '<td><input type="hidden" name="ClaveProdServ[' . $j . ']" value="' . $ClaveProdServ[$j] . '" size="6" required/> ' . $ClaveProdServ[$j] . ' </td>';
				}
				if($Recalcula != 'Recalcula') {
					echo '<td><textarea name="descext[' . $j . ']" style="margin: 0px; height: 130px; width: 430px;">' . constant('NOMBRE_AREA_' . $sub['sub_area']) . ' - ' . $sub['sub_descripcion'] . '</textarea></td>';
				}	else 	{	
					echo '<td><input type="hidden" name="descext[' . $j . ']" value="' . $descext[$j] . '"/>' . $descext[$j] . '</td>';
				}
				if($Recalcula != 'Recalcula') {
					echo '<td><input type="text" name="ClaveUnidad[' . $j . ']" value="E48" size="4"/> </td>';
				}	else 	{
					echo '<td><input type="hidden" name="ClaveUnidad[' . $j . ']" value="' . $ClaveUnidad[$j] .'" size="4"/> ' . $ClaveUnidad[$j] .'</td>';
				}
				if($Recalcula != 'Recalcula') {
					echo '<td><input type="text" name="uniext[' . $j . ']" value="Servicio" size="6" required /></td>';
				}	else 	{
					echo '<td><input type="hidden" name="uniext[' . $j . ']" value="' . $uniext[$j] . '" size="6" required /> ' . $uniext[$j] . '</td>';
				}
				if($Recalcula != 'Recalcula') {
					echo '<td style="text-align:right;">' . number_format($precio_unitario,2) . '<input type="hidden" name="precext[' . $j . ']" value="' . $precio_unitario . '" /></td>';
				}	else 	{
					echo '<td style="text-align:right;">' . number_format($precio_unitario,2) . '<input type="hidden" name="precext[' . $j . ']" value="' . $precio_unitario . '" /> </td>';
				}
				if($Recalcula != 'Recalcula') {
					if($cliente['descuento'] != '') { $dtoext[$j] = $cliente['descuento']; } else { $dtoext[$j] = 0; }
					echo '<td style="text-align:right;"><input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="'. $dtoext[$j] .'" size="2" /> %</td>';
				}	else 	{
					echo '<td style="text-align:right;"><input style="text-align:right;" type="hidden" name="dtoext[' . $j . ']" value="'. $dtoext[$j] .'" size="2" /> '. $dtoext[$j] .' %</td>';
                }
						$cnt = 1;
						$subtotal = round(($precio_unitario * $cnt), 2);
						$subtotal = $subtotal - round(($subtotal * ($dtoext[$j] / 100)), 2);

					echo '<td style="text-align:right;">$' . number_format($subtotal,2) . '</td></tr>'."\n";
			

						$gdto = $gdto + ($precio_unitario - $subtotal);
						$gsubt = $gsubt + $subtotal;
						$giva = $giva + round(($subtotal * $impuesto_iva), 6);
						$j++;
						

				}
			}
				
		
			break;
	case 2: //DESGLOSE2 AUDAGOLD
			$j=0; $dtopartes=0; $dtocons=0; $dtomo=0; $giva = 0;
			foreach($tarea as $v) {
				$preg0 = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$v'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!" . $preg0);
				$sub = mysql_fetch_array($matr0);
//				print_r($sub); echo '<br><br>';
//				$area_tot = 0;
				if($sub['sub_partes'] != 0 ) {
					$partes = $partes + round($sub['sub_partes'], 2);
				}
				if($sub['sub_consumibles'] != 0 ) {
					$cons = $cons + round($sub['sub_consumibles'], 2);
				}
				if($sub['sub_mo'] != 0 ) {
//					if($area_tot > 0) { $frase .= ' y '; }
					$motot = $motot + round($sub['sub_mo'], 2);
					$mo[$sub['sub_area']] = $mo[$sub['sub_area']] + round($sub['sub_mo'], 2);
				}

				if($cliente['descuento'] > 0) {
					$dtopartes = $cliente['descuento'];
					$dtocons = $cliente['descuento'];
					$dtomo = $cliente['descuento'];
				} else {
//  -----------  20150124: Integración futura de descuentos por Tarea  -----------------
					if($sub['sub_dto_partes'] > $dtopartes) { $dtopartes = $sub['sub_dto_partes']; }
					if($sub['sub_dto_cons'] > $dtocons) { $dtocons = $sub['sub_dto_cons']; }
					if($sub['sub_dto_mo'] > $dtomo) { $dtomo = $sub['sub_dto_mo']; }
				}
			}
				$mo16 = $mo['1'] + $mo['6'];
				if($mo16 > 0) {
					echo '			<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" value="1" />1</td>';
					echo '<td>';
					//--- ClaveUnidad ---
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="ClaveProdServ[' . $j . ']" value="' . $lang['E48ClaPro'] . '" size="6" required/>';
					} else {
						echo '<input type="hidden" name="ClaveProdServ[' . $j . ']" value="' . $ClaveProdServ[$j] . '" />' . $ClaveProdServ[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="descext[' . $j . ']" value="' . $lang['MOChaMec'] . '" />';
					} else {
						echo '<input type="hidden" name="descext[' . $j . ']" value="' . $descext[$j] . '" />' . $descext[$j];
					}
					echo '</td><td>'; 
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="ClaveUnidad[' . $j . ']" value="' . $lang['E48ClaUni'] . '" size="4" required/>';
					} else {
						echo '<input type="hidden" name="ClaveUnidad[' . $j . ']" value="' . $ClaveUnidad[$j] . '" />'. $ClaveUnidad[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="uniext[' . $j . ']" value="' . $lang['UniSer'] . '" size="6" />';
					} else {
						echo '<input type="hidden" name="uniext[' . $j . ']" value="' . $uniext[$j] .'" />' . $uniext[$j];
					}
					echo '</td><td style="text-align:right;">';
					echo '<input type="hidden" name="precext[' . $j . ']" value="' . $mo16 . '" size="8" />' . number_format($mo16,2) . '</td>'; 
					echo '<td style="text-align:right;">';
					if($Recalcula != 'Recalcula') {
						echo '<input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="0" size="2" />';
					} else {
						echo '<input type="hidden" name="dtoext[' . $j . ']" value="' . $dtoext[$j] . '" />' . $dtoext[$j];
					}
					echo ' %</td>';
					$subtotal = $mo16 - round(($mo16 * ($dtoext[$j] / 100)), 2);
					echo '<td style="text-align:right;">$' . number_format($subtotal, 2) . '</td>';
					echo '</tr>'."\n";
					$gdto = $gdto + ($mo16 - $subtotal);
					$gsubt = $gsubt + $subtotal;
					$giva = $giva + round(($subtotal * $impuesto_iva), 6);
					$j++;
				}
				if($partes > 0) {
//					echo '			<tr><td><input type="checkbox" name="omitir[' . $j . ']" value="1"></td>';
					echo '			<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" value="1" />1</td>';
					echo '<td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="ClaveProdServ[' . $j . ']" value="25191700" size="6" required/>';
					} else {
						echo '<input type="hidden" name="ClaveProdServ[' . $j . ']" value="' . $ClaveProdServ[$j] . '" />' . $ClaveProdServ[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="descext[' . $j . ']" value="' . $lang['LotRef'] . '" />';
					} else {
						echo '<input type="hidden" name="descext[' . $j . ']" value="' . $descext[$j] . '" />' . $descext[$j];
					}
					echo '</td><td>';
					//--- ClaveUnidad ---
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="ClaveUnidad[' . $j . ']" value="EA" size="4" required/>';
					} else {
						echo '<input type="hidden" name="ClaveUnidad[' . $j . ']" value="' . $ClaveUnidad[$j] . '" />' . $ClaveUnidad[$j]; 
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="uniext[' . $j . ']" value="Pieza" size="6" />';
					} else {
						echo '<input type="hidden" name="uniext[' . $j . ']" value="' . $uniext[$j] . '" />' . $uniext[$j];
					}
					echo '</td>'; 
					echo '<td style="text-align:right;"><input type="hidden" name="precext[' . $j . ']" value="' . $partes . '" size="8" />' . number_format($partes,2) . '</td>';
					echo '<td style="text-align:right;">';
					if($Recalcula != 'Recalcula') {
						echo '<input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="0" size="2" />';
					} else {
						echo '<input type="hidden" name="dtoext[' . $j . ']" value="' . $dtoext[$j] . '" />' . $dtoext[$j];
					}
					echo ' %</td>';
					$subtotal = $partes - round(($partes * ($dtoext[$j] / 100)), 2);
					echo '<td style="text-align:right;">$' . number_format($subtotal, 2) . '</td>';
					echo '</tr>'."\n";
					$gdto = $gdto + ($partes - $subtotal);
					$gsubt = $gsubt + $subtotal;
					$giva = $giva + round(($subtotal * $impuesto_iva), 6);
					$j++;
				}
				if($cons > 0 || $mo['7'] > 0) {
					$cons = $cons + $mo['7'];
//					echo '			<tr><td><input type="checkbox" name="omitir[' . $j . ']" value="1"></td>';
					echo '			<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" value="1" />1</td>';
					echo '<td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="ClaveProdServ[' . $j . ']" value="78181501" size="6" required/>';
					} else {
						echo '<input type="hidden" name="ClaveProdServ[' . $j . ']" value="' . $ClaveProdServ[$j] . '" />'. $ClaveProdServ[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="descext[' . $j . ']" value="Materiales y M.O. de Pintura" />';
					} else {
						echo '<input type="hidden" name="descext[' . $j . ']" value="' . $descext[$j] . '" />' . $descext[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="ClaveUnidad[' . $j . ']" value="ZZ" size="4" />';
					} else {
						echo '<input type="hidden" name="ClaveUnidad[' . $j . ']" value="' . $ClaveUnidad[$j] . '" />' . $ClaveUnidad[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="uniext[' . $j . ']" value="N/A" size="6" required/>';
					} else {
						echo '<input type="hidden" name="uniext[' . $j . ']" value="' . $uniext[$j] . '" />' . $uniext[$j];
					}
					echo '</td>'; 
					echo '<td style="text-align:right;"><input type="hidden" name="precext[' . $j . ']" value="' . $cons . '" size="8" />' . number_format($cons,2) . '</td><td style="text-align:right;">';
					if($Recalcula != 'Recalcula') {
						echo '<input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="0" size="2" />';
					} else {
						echo '<input type="hidden" name="dtoext[' . $j . ']" value="' . $dtoext[$j] . '" />' . $dtoext[$j];
					}
					echo ' %</td>';
					$subtotal = $cons - round(($cons * ($dtoext[$j] / 100)), 2);
					echo '<td style="text-align:right;">$' . number_format($subtotal, 2) . '</td>';
					echo '</tr>'."\n";
					$gdto = $gdto + ($cons - $subtotal);
					$gsubt = $gsubt + $subtotal;
					$giva = $giva + round(($subtotal * $impuesto_iva), 6);
					$j++;
				}
		break;
	case 1://DESGLOSE1 DESGLOSADO
	case 3://DESGLOSE3 DESGLOSADO MO
//		mysql_data_seek($matriz,0);
		$j=0; $dtopartes=0; $dtocons=0; $dtomo=0; $giva = 0;
		//if($desglose == '1' || $desglose == '3') {
			foreach($tarea as $v) {
				$preg4 = "SELECT op_id, op_nombre, op_codigo, op_cantidad, op_precio, op_tangible, op_autosurtido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $v . "' ";
				if($orden_id!='') {
					$preg4 .= " AND op_pres IS NULL ";
				} else {
					$preg4 .= " AND op_pres = '1' ";
				}
				$preg4 .= " AND op_cantidad > '0' AND op_precio > '0' ORDER BY op_tangible,op_nombre ";
				$matr4 = mysql_query($preg4) or die("ERROR: Fallo seleccion de items!");
			while ($op = mysql_fetch_array($matr4)) {
					$subtotal = 0;
				if($op['op_tangible'] == '0' || $op['op_tangible'] == '2' || ($op['op_tangible'] == '1' && ($op['op_autosurtido'] == '2' || $op['op_autosurtido'] == '3'))) {
						echo '			<tr>';
						echo '<td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" size="4" value="';
				if($cantext[$j] != '') {
							$cnt = $cantext[$j];
						} elseif($desglose == '3' && $op['op_tangible'] == '0') {
							$cnttmp = $op['op_cantidad'];
							$cnt = 1;
						} else {
							$cnt = $op['op_cantidad'];
						}
						echo $cnt . '" size="2" />' . $cnt . '</td><td>';
				if($Recalcula != 'Recalcula') {
							echo '<input type="text" name="ClaveProdServ[' . $j . ']" value="';
						if($op['op_tangible'] == '0') { echo '78181501'; }
							elseif($op['op_tangible'] == '1') { echo '25191700'; }
							else { echo '73181104'; }
							echo '" size="6" required/>';
						} else {
							echo '<input type="hidden" name="ClaveProdServ[' . $j . ']" value="' . $ClaveProdServ[$j] . '" />' . $ClaveProdServ[$j];
						} 
						echo '</td><td>'; 
						if($Recalcula != 'Recalcula') {
							echo '<input type="text" name="descext[' . $j . ']" value="' . $op['op_nombre']; 
							if($ajustacodigo == 1) {
								echo ' ' . $op['op_codigo']; 
							}
							echo '" />';
						} else {
							echo '<input type="hidden" name="descext[' . $j . ']" value="' . $descext[$j] . '" />' . $descext[$j];
						}
						echo '</td><td>';
						if($Recalcula != 'Recalcula') {
							echo '<input type="text" name="ClaveUnidad[' . $j . ']" value="';
							if($op['op_tangible'] == '0') { echo 'E48'; }
							elseif($op['op_tangible'] == '1') { echo 'EA'; }
							else { echo 'ZZ'; }
							echo '" size="3" />';
						} else {
							echo '<input type="hidden" name="ClaveUnidad[' . $j . ']" value="' . $ClaveUnidad[$j] . '" />' . $uniext[$j];
						}
						echo '</td><td>';
						if($Recalcula != 'Recalcula') {
							echo '<input type="text" name="uniext[' . $j . ']" value="';
							if($op['op_tangible'] == '0') { echo 'Servicio'; }
							elseif($op['op_tangible'] == '1') { echo 'Pieza'; }
							else { echo 'N/A'; }
							echo '" size="4" required/>';
						} else {
							echo '<input type="hidden" name="uniext[' . $j . ']" value="' . $uniext[$j] . '" />' . $uniext[$j];
						}
						echo '</td><td style="text-align:right;">';
						//---------------------
						if($precext[$j] != '') {
							$fv = $precext[$j];
						} elseif($desglose == '3' && $op['op_tangible'] == '0') {
							$fv = $op['op_precio'] * $cnttmp;
						} else {
							$fv = $op['op_precio'];
						}
						$fv = round(limpiarNumero($fv),2);
						echo '<input type="hidden" name="precext[' . $j . ']" value="' . $fv . '" />' . number_format($fv,2) . '</td>';
						echo '<td style="text-align:right;">';
						if($Recalcula != 'Recalcula') {
							if($cliente['descuento'] != '') { $dtoext[$j] = $cliente['descuento']; } else { $dtoext[$j] = 0; }
							echo '<input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="' . $dtoext[$j] . '" size="2" />';
						} else {
							echo '<input style="text-align:right;" type="hidden" name="dtoext[' . $j . ']" value="' . $dtoext[$j] . '" size="2" />' . $dtoext[$j];
						}
						echo ' %</td>';
						$subtotal = round(($fv * $cnt), 2);
						$subtotal = $subtotal - round(($subtotal * ($dtoext[$j] / 100)), 2);
						echo '<td style="text-align:right;">' . number_format($subtotal, 2) . '</td>';
					}
					echo '</tr>'."\n";
					$gdto = $gdto + ($fv - $subtotal);
					$gsubt = $gsubt + $subtotal;
					$giva = $giva + round(($subtotal * $impuesto_iva), 6);
					$j++;
					$dto = 0;
				}
			}
		break;
				
	case 0://DESGLOSE0 RESUMEN DE TAREAS
	case 4://DESGLOSE4 VENTA MOSTTRADOR
	$j=0; $dtopartes=0; $dtocons=0; $dtomo=0; $giva = 0;
			foreach($tarea as $v) {
				$preg0 = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$v'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!" . $preg0);
				$sub = mysql_fetch_array($matr0);
//				print_r($sub); echo '<br><br>';
//				$area_tot = 0;
				if($sub['sub_partes'] != 0 ) {
					$partes = $partes + round($sub['sub_partes'], 2);
				}
				if($sub['sub_consumibles'] != 0 ) {
					$cons = $cons + round($sub['sub_consumibles'], 2);
				}
				if($sub['sub_mo'] != 0 ) {
//					if($area_tot > 0) { $frase .= ' y '; }
					$motot = $motot + round($sub['sub_mo'], 2);
					$mo[$sub['sub_area']] = $mo[$sub['sub_area']] + round($sub['sub_mo'], 2);
				}

				if($cliente['descuento'] > 0) {
					$dtopartes = $cliente['descuento'];
					$dtocons = $cliente['descuento'];
					$dtomo = $cliente['descuento'];
				} else {
//  -----------  20150124: Integración futura de descuentos por Tarea  -----------------
					if($sub['sub_dto_partes'] > $dtopartes) { $dtopartes = $sub['sub_dto_partes']; }
					if($sub['sub_dto_cons'] > $dtocons) { $dtocons = $sub['sub_dto_cons']; }
					if($sub['sub_dto_mo'] > $dtomo) { $dtomo = $sub['sub_dto_mo']; }
				}
			}
				foreach($mo as $fk => $fv) {
					if($fv > 0) {
//						echo '			<tr><td><input type="checkbox" name="omitir[' . $j . ']" value="1"></td>';
						echo '			<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" value="1" />1</td>';
						echo '<td>';
						if($Recalcula != 'Recalcula') {
							echo '<input type="text" name="ClaveProdServ[' . $j . ']" value="78181501" size="6" required/>';
						} else {
							echo '<input type="hidden" name="ClaveProdServ[' . $j . ']" value="' . $ClaveProdServ[$j] . '" />' . $ClaveProdServ[$j];
						}
						echo '</td><td>';
						if($Recalcula != 'Recalcula') {
							echo '<input type="text" name="descext[' . $j . ']" value="Mano de Obra de ' . constant('NOMBRE_AREA_' . $fk) . '" />';
						} else {
							echo '<input type="hidden" name="descext[' . $j . ']" value="' . $descext[$j] . '" />' . $descext[$j];
						}
						echo '</td><td>';
						if($Recalcula != 'Recalcula') {
							echo '<input type="text" name="ClaveUnidad[' . $j . ']" value="E48" size="4" required/>';
						} else {
							echo '<input type="hidden" name="ClaveUnidad[' . $j . ']" value="' . $ClaveUnidad[$j] . '" />' . $ClaveUnidad[$j];
						}
						echo '</td><td>';
						if($Recalcula != 'Recalcula') {
							echo '<input type="text" name="uniext[' . $j . ']" value="Servicio" size="6" />';
						} else {
							echo '<input type="hidden" name="uniext[' . $j . ']" value="' . $uniext[$j] . '" />' . $uniext[$j];
						}
						echo '</td><td style="text-align:right;"><input type="hidden" name="precext[' . $j . ']" value="' . $fv . '" size="8" />' . number_format($fv,2);
						echo '</td><td style="text-align:right;">';
						if($Recalcula != 'Recalcula') {
							echo '<input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="0" size="2" />';
						} else {
							echo '<input style="text-align:right;" type="hidden" name="dtoext[' . $j . ']" value="' . $dtoext[$j] . '" />' . $dtoext[$j];
						}
						echo ' %</td>';
							$subtotal = $fv - round(($fv * ($dtoext[$j] / 100)), 2);
							echo '<td style="text-align:right;">$' . number_format($subtotal, 2) . '</td>';
							echo '</tr>'."\n";
							$gdto = $gdto + ($fv - $subtotal);
							$gsubt = $gsubt + $subtotal;
							$giva = $giva + round(($subtotal * $impuesto_iva), 6);
							$j++;
					}
				}


				if($partes > 0) {
//					echo '			<tr><td><input type="checkbox" name="omitir[' . $j . ']" value="1"></td>';
					echo '			<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" value="1" />1</td>';
					echo '<td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="ClaveProdServ[' . $j . ']" value="25191700" size="6" required/>';
					} else {
						echo '<input type="hidden" name="ClaveProdServ[' . $j . ']" value="' . $ClaveProdServ[$j] . '" />' . $ClaveProdServ[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="descext[' . $j . ']" value="Lote de Refacciones" />';
					} else {
						echo '<input type="hidden" name="descext[' . $j . ']" value="' . $descext[$j] . '" />' . $descext[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="ClaveUnidad[' . $j . ']" value="EA" size="4" required/>';
					} else {
						echo '<input type="hidden" name="ClaveUnidad[' . $j . ']" value="' . $ClaveUnidad[$j] . '" />' . $ClaveUnidad[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="uniext[' . $j . ']" value="Pieza" size="6" />';
					} else {
						echo '<input type="hidden" name="uniext[' . $j . ']" value="' . $uniext[$j] . '" />' . $uniext[$j];
					}
					echo '</td><td style="text-align:right;"><input type="hidden" name="precext[' . $j . ']" value="' . $partes . '" size="8" />' . number_format($partes,2) . '</td><td style="text-align:right;">';
					if($Recalcula != 'Recalcula') {
						echo '<input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="0" size="2" />';
					} else {
						echo '<input style="text-align:right;" type="hidden" name="dtoext[' . $j . ']" value="' . $dtoext[$j] . '" />' . $dtoext[$j];
					}
					echo ' %</td>';
					$subtotal = $partes - round(($partes * ($dtoext[$j] / 100)), 2);
					echo '<td style="text-align:right;">$' . number_format($subtotal, 2) . '</td>';
					echo '</tr>'."\n";
					$gdto = $gdto + ($partes - $subtotal);
					$gsubt = $gsubt + $subtotal;
					$giva = $giva + round(($subtotal * $impuesto_iva), 6);
					$j++;
				}
				if($cons > 0) {
//					echo '			<tr><td><input type="checkbox" name="omitir[' . $j . ']" value="1"></td>';
					echo '			<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" value="1" />1</td>';
					echo '<td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="ClaveProdServ[' . $j . ']" value="73181104" size="6" required/>';
					} else {
						echo '<input type="hidden" name="ClaveProdServ[' . $j . ']" value="' . $ClaveProdServ[$j] . '" />' . $ClaveProdServ[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="descext[' . $j . ']" value="Lote de Materiales" />';
					} else {
						echo '<input type="hidden" name="descext[' . $j . ']" value="' . $descext[$j] . '" />' . $descext[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="ClaveUnidad[' . $j . ']" value="ZZ" size="4" required/>';
					} else {
						echo '<input type="hidden" name="ClaveUnidad[' . $j . ']" value="' . $ClaveUnidad[$j] . '" />' . $ClaveUnidad[$j];
					}
					echo '</td><td>';
					if($Recalcula != 'Recalcula') {
						echo '<input type="text" name="uniext[' . $j . ']" value="N/A" size="6" />';
					} else {
						echo '<input type="hidden" name="uniext[' . $j . ']" value="'. $uniext[$j] . '" />' . $uniext[$j];
					}
					echo '</td><td style="text-align:right;"><input type="hidden" name="precext[' . $j . ']" value="' . $cons . '" size="8" />' . number_format($cons,2) . '</td>';
					echo '<td style="text-align:right;">';
					if($Recalcula != 'Recalcula') {
						echo '<input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="0" size="2" />';
					} else {
						echo '<input type="hidden" name="dtoext[' . $j . ']" value="' . $dtoext[$j] . '" />' . $dtoext[$j];
					}
					echo ' %</td>';
					$subtotal = $cons - round(($cons * ($dtoext[$j] / 100)), 2);
					echo '<td style="text-align:right;">$' . number_format($subtotal, 2) . '</td>';
					echo '</tr>'."\n";
					$gdto = $gdto + ($cons - $subtotal);
					$gsubt = $gsubt + $subtotal;
					$giva = $giva + round(($subtotal * $impuesto_iva), 6);
					$j++;
				}
			break;
		}
// ------ Renglones adicionales para agregar conceptos a la factura ---------
		for($i=$j;($j+0) > $i; $i++) {
			if($Recalcula != 'Recalcula') {
				echo '			<tr>'."\n";
				echo '				<td style="text-align:center;"><input style="text-align:center;" type="text" name="cantext[' . $i . ']" size="1" value="" /></td>
				<td><input type="text" name="ClaveProdServ[' . $i . ']" size="6" value="" /></td>
				<td><input type="text" name="descext[' . $i . ']" size="20" value="" /></td>
				<td><input type="text" name="ClaveUnidad[' . $i . ']" value="" size="4" /></td>
				<td><input type="text" name="uniext[' . $i . ']" size="3" value="" /></td>
				<td style="text-align:right;"><input style="text-align:right;" type="text" name="precext[' . $i . ']" value="" size="6" /></td>
				<td style="text-align:right;"><input style="text-align:right;" type="text" name="dtoext[' . $i . ']" value="" size="2" /> %</td>
				<td style="text-align:right;"></td>
			</tr>'."\n";
			} else {
				$cantext[$i] = round(limpiarNumero($cantext[$i]),2);
				if($cantext[$i] > 0) {
					$precext[$i] = round(limpiarNumero($precext[$i]),2);
					echo '			<tr>'."\n";
					echo '				<td style="text-align:center;"><input type="hidden" name="cantext[' . $i . ']" value="' . $cantext[$i] . '" />' . $cantext[$i] . '</td>
				<td><input type="hidden" name="ClaveProdServ[' . $i . ']" value="' . $ClaveProdServ[$i] . '" />' . $ClaveProdServ[$i] . '</td>
				<td><input type="hidden" name="descext[' . $i . ']" value="' . $descext[$i] . '" />' . $descext[$i] . '</td>
				<td><input type="hidden" name="ClaveUnidad[' . $i . ']" value="' . $ClaveUnidad[$i] . '" />' . $ClaveUnidad[$i] . '</td>
				<td><input type="hidden" name="uniext[' . $i . ']" value="' . $uniext[$i] . '" />' . $uniext[$i] . '</td>
				<td style="text-align:right;"><input style="text-align:right;" type="hidden" name="precext[' . $i . ']" value="' . $precext[$i] . '" />' . number_format($precext[$i],2) . '</td>
				<td style="text-align:right;"><input style="text-align:right;" type="hidden" name="dtoext[' . $i . ']" value="' . $dtoext[$i] . '" size="2" />' . $dtoext[$i] . ' %</td>';
					$subtotal = $cantext[$i] * ($precext[$i] - round(($precext[$i] * ($dtoext[$i] / 100)), 2));
					echo '<td style="text-align:right;">$' . number_format($subtotal, 2) . '</td>';
					echo '</tr>'."\n";
					$gdto = $gdto + (($cantext[$i] * $precext[$i]) - $subtotal);
					$gsubt = $gsubt + $subtotal;
					$giva = $giva + round(($subtotal * $impuesto_iva), 6);
				}
			}
		}

		echo '			<tr><td colspan="7" style="text-align:right;">Subtotal:</td><td style="text-align:right;">$' . number_format($gsubt, 2) . '</td></tr>'."\n";
		$iva = $giva; 
		echo '			<tr><td colspan="7" style="text-align:right;">IVA al 16%:</td><td style="text-align:right;">$' . number_format($iva, 2) . '</td></tr>'."\n";
		$suma = $gsubt + $iva;
		echo '			<tr><td colspan="7" style="text-align:right;">Total:</td><td style="text-align:right;">$' . number_format($suma, 2) . '</td></tr>'."\n";
		echo '		</table>'."\n";

		echo '		<table cellpadding="0" cellspacing="0" border="0" width="840px" class="izquierda">'."\n";

/*		if($cliente['descuento'] > 0) {
			echo '			<tr><td style="width:210px;">Descuento General Aplicable antes de IVA</td><td><input style="text-align:right;" type="text" name="perdesc" value="' . $cliente['descuento'] . '" size="3" />%</td></tr>'."\n";
		}
*/
		if($Recalcula != 'Recalcula') {
			echo '			<tr><td>Motivo del Descuento (si hay descuento)</td><td><input type="text" name="motivo" size="20" /></td></tr>'."\n";
		} elseif($sumdto > 0) {
			echo '			<tr><td>Motivo del Descuento: </td><td><input type="hidden" name="motivo" value="'.$motivo.'" />'.$motivo.'</td></tr>'."\n";
		}

		if($aseguradora > 0) {
			echo '			<tr><td>CLIENTE</td><td>' . $asegurado['cliente_nombre'] . ' ' . $asegurado['cliente_apellidos'] . '</td></tr>'."\n";
			if($siniestro == '') { $siniestro = $reporte; } 
			echo '			<tr><td>SINIESTRO</td><td>' . $reporte . '</td></tr>
			<tr><td>POLIZA</td><td>' . $poliza . '</td></tr>
			<tr><td>DEDUCIBLE</td><td>' . $dedu . '</td></tr>'."\n";
		}

		echo '			<tr><td>MARCA</td><td><input type="text" name="marca" value="' . $datosv['marca'] . '" /></td></tr>
			<tr><td>AUTO</td><td><input type="text" name="tipo" value="' . $datosv['tipo'] . '" /></td></tr>
			<tr><td>AÑO</td><td><input type="text" name="modelo" value="' . $datosv['modelo'] . '" /></td></tr>
			<tr><td>COLOR</td><td><input type="text" name="color" value="' . $datosv['color'] . '" /></td></tr>
			<tr><td>PLACAS</td><td><input type="text" name="placas" value="' . $datosv['placas'] . '" /></td></tr>
			<tr><td>VIN</td><td><input type="text" name="vin" value="' . $datosv['serie'] . '" /></td></tr>'."\n";
		echo '			<tr><td>Observaciones adicionales:</td><td><input type="text" name="obsad" size="60" maxlength="80" value="' . $obsad . '" /></td></tr>'."\n";

// --------------- Inclusión de addenda en caso de que exista  -------------------------

		if($aseguradora > 0 && $ase['aseguradora_addenda'] != '' ) {
			include('parciales/' . $ase['aseguradora_addenda']);
			echo '			<tr><td colspan="2"><input type="hidden" name="addenda" value="' . $ase['aseguradora_addenda'] . '" /></td></tr>'."\n";
		}

		echo '			<tr><td colspan="2"><hr></td></tr>'."\n";
		if($Recalcula == 'Recalcula' && $noconfirmar != '1') {
			echo '			<tr><td colspan="2"><button name="Confirmar" value="Confirmar" type="submit">Confirmar Datos</button></td></tr>'."\n";
		} elseif($noconfirmar == '1') {
			echo '			<tr><td colspan="2"><span style="background-color: yellow; color: red; font-weight: bold;">Addenda Incompleta - VERIFICAR</span></td></tr>'."\n";
		} else {
			echo '			<tr><td colspan="2"><button name="Recalcula" value="Recalcula" type="submit">Recalcular Datos</button></td></tr>'."\n";
		}
		echo '		</table>
		</div>'."\n";

		echo '			<input type="hidden" name="fact_num" value="' . $fact_num . '" />
			<input type="hidden" name="reporte" value="' . $reporte . '" />
			<input type="hidden" name="poliza" value="' . $poliza . '" />
			<input type="hidden" name="deducible" value="' . $dedu . '" />
			<input type="hidden" name="dato" value="' . $dato . '" />
			<input type="hidden" name="aseguradora" value="' . $aseguradora . '" />
			<input type="hidden" name="desglose" value="' . $desglose . '" />
			<input type="hidden" name="sumdto" value="' . $sumdto . '" />
			<input type="hidden" name="previa_id" value="' . $previa_id . '" />
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />'."\n";
		foreach($tarea as $k => $v) {
			echo '			<input type="hidden" name="tarea[]" value="' . $v . '" />'."\n";
		}

		echo '		<div class="control">';
/*		if($_SESSION['rol04']=='1' && $accion==='imprime') {
			echo '<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="Imprimir Factura" title="Imprimir Factura"></a> | ';
		}
*/
		if($orden_id != '') {
			echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>'."\n";
		} else {
			echo '<a href="previas.php?accion=consultar&previa_id=' . $previa_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar al Presupuesto" title="Regresar al Presupuesto"></a>'."\n";
		}
		echo '		</div></form>'."\n";
	} else {
		$_SESSION['msjerror'] = $mensaje;
//		echo $mensaje;
		if($orden_id != '') {
			redirigir('factura-3.3.php?accion=consultar&orden_id=' . $orden_id);
		} else {
			redirigir('factura-3.3.php?accion=consultar&previa_id=' . $previa_id);
		}
	}
}

elseif($accion==='imprime') {

	if(validaAcceso('1095010', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1'))) {
//		$mensaje = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=' . $lang['AccesoNegado']);
	}

	$error = 'no'; $mensaje= '';

	$subtotal=0; $descuento = 0;
/*	foreach ($cantext as $k => $v) {
		$descuento = $descuento + round((($v * $precext[$k]) * ($dtoext[$k] / 100)), 6);
		$impext[$k] = round(($cantext[$k] * $precext[$k]), 2);
		$subtotal = $subtotal + $impext[$k];
	}
*/
	$motivo = limpiar_cadena($motivo);

// ------ Habilitacíon de múltiples RFCs para facturar --
	$rfcu = explode('|', $cualrfc);
	$agencia_rfc = $rfcu[0];
	$agencia_razon_social = $rfcu[1];
	$agencia_regimen = $rfcu[2];
	$agencia_cp = $rfcu[3];
// ------

// ----------------- Si hay descuento debe haber motivo ------------------------
	if($_SESSION['fact']['facturada'] == 1)  { $error = 'si'; $mensaje .= 'La factura ya fue emitida<br>'; }
	if($metop == '') { $error = 'si'; $mensaje .= 'Seleccione un método de pago.<br>'; $Recalcula = ''; }

	if($error == 'no') {
   	if($orden_id != '') {
			$pregunta4 = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_modelo, v.vehiculo_placas, v.vehiculo_serie FROM " . $dbpfx . "ordenes o, " . $dbpfx . "vehiculos v WHERE o.orden_id = '$orden_id' AND o.orden_vehiculo_id = v.vehiculo_id";
		} else {
			$pregunta4 = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_modelo, v.vehiculo_placas, v.vehiculo_serie FROM " . $dbpfx . "previas o, " . $dbpfx . "vehiculos v WHERE o.previa_id = '$previa_id' AND o.previa_vehiculo_id = v.vehiculo_id";
		}
  		$matriz4 = mysql_query($pregunta4) or die("ERROR: Fallo seleccion!".$pregunta4);
	  	$datosv  = mysql_fetch_array($matriz4);
//	  	unset($datosv);
		if($aseguradora == 0) {
			if($orden_id != '') {
				$pregunta2 = "SELECT e.empresa_razon_social, e.empresa_rfc, e.empresa_calle, e.empresa_ext, e.empresa_int, e.empresa_colonia, e.empresa_cp, e.empresa_municipio, e.empresa_estado, e.empresa_pais, e.empresa_descuento, c.cliente_id FROM " . $dbpfx . "ordenes o, " . $dbpfx . "empresas e, " . $dbpfx . "clientes c  WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id AND c.cliente_empresa_id = e.empresa_id";
			} else {
				$pregunta2 = "SELECT e.empresa_razon_social, e.empresa_rfc, e.empresa_calle, e.empresa_ext, e.empresa_int, e.empresa_colonia, e.empresa_cp, e.empresa_municipio, e.empresa_estado, e.empresa_pais, e.empresa_descuento, c.cliente_id FROM " . $dbpfx . "previas o, " . $dbpfx . "empresas e, " . $dbpfx . "clientes c  WHERE o.previa_id = '$previa_id' AND o.previa_cliente_id = c.cliente_id AND c.cliente_empresa_id = e.empresa_id";
			}
   		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!".$pregunta2);
   		$cli = mysql_fetch_array($matriz2);
   		$cliente = array(
   			'id' => $cli['cliente_id'],
   			'nombre' => strtoupper($cli['empresa_razon_social']), 
   			'calle' =>  strtoupper($cli['empresa_calle']), 
   			'numext' =>  strtoupper($cli['empresa_ext']), 
   			'numint' =>  strtoupper($cli['empresa_int']), 
   			'colonia' =>  strtoupper($cli['empresa_colonia']), 
   			'cp' => $cli['empresa_cp'], 
   			'municipio' =>  strtoupper($cli['empresa_municipio']), 
   			'estado' =>  strtoupper($cli['empresa_estado']), 
   			'pais' =>  strtoupper($cli['empresa_pais']), 
   			'descuento' =>  $cli['empresa_descuento'], 
   			'rfc' =>  $cli['empresa_rfc'],
   		);
		} else {
			$pregunta2 = "SELECT * FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $aseguradora . "'";
   		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!".$pregunta2);
   		$ase = mysql_fetch_array($matriz2);
   		$cliente = array(
   			'id' => $ase['aseguradora_id'],
   			'nombre' => $ase['aseguradora_razon_social'], 
   			'calle' =>  $ase['aseguradora_calle'], 
   			'numext' =>  $ase['aseguradora_ext'], 
   			'numint' =>  $ase['aseguradora_int'], 
   			'colonia' =>  $ase['aseguradora_colonia'], 
   			'cp' =>  $ase['aseguradora_cp'], 
   			'municipio' =>  $ase['aseguradora_municipio'], 
   			'estado' =>  $ase['aseguradora_estado'], 
   			'pais' =>  $ase['aseguradora_pais'], 
   			'descuento' =>  $ase['aseguradora_descuento'], 
   			'rfc' =>  $ase['aseguradora_rfc']
   		);
   	}

##########################################################
# PASO1. Crea un CFDi 3.3 de factura con un par de conceptos
#
# Regresa un texto en la variable $cfdi
##########################################################

# Partimos de un CFDi a medias, conservando declaracion de esquemas
		$cfdi = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd" Version="3.3" Sello="" NoCertificado="" Certificado="" LugarExpedicion="" Fecha="" Serie="" Folio="" TipoDeComprobante="I" FormaPago="" MetodoPago="" Moneda="MXN" TipoCambio="1" SubTotal="" Total="" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<cfdi:Emisor Rfc="" Nombre="" RegimenFiscal=""/>
	<cfdi:Receptor Rfc="" Nombre="" UsoCFDI="" />
	<cfdi:Conceptos>
	</cfdi:Conceptos>
	<cfdi:Impuestos TotalImpuestosTrasladados="">
		<cfdi:Traslados>
			<cfdi:Traslado Impuesto="" TipoFactor="" TasaOCuota="" Importe="" />
		</cfdi:Traslados>
	</cfdi:Impuestos>
	<cfdi:Complemento>
	</cfdi:Complemento>
</cfdi:Comprobante>
';

# El emisor no cambia. Si cambiara el codigo sería semejante al de receptor
		$emisor = [
			"nombre" => $agencia_razon_social,
			"rfc" => $agencia_rfc,
			"Regimen" => $agencia_regimen,
			"Domicilio" => [
				"codigoPostal" => $agencia_cp,
				"pais" => "MEXICO",
				"estado" => $agencia_estado,
				"municipio" => $agencia_municipio,
				"colonia" => $agencia_colonia,
				"noExterior" => $agencia_numext,
				"referencia" => $agencia_referencia,
				"calle" => $agencia_calle
			]
		];

		$receptor = [
			"nombre" => $cliente['nombre'],
			"rfc" => $cliente['rfc'],
			"UsoCFDI" => $usocfdi,
			"Domicilio" => [
				"codigoPostal" => $cliente['cp'],
				"pais" => "MEXICO",
				"estado" => $cliente['estado'],
				"municipio" => $cliente['municipio'],
				"colonia" => $cliente['colonia'],
				"noExterior" => $cliente['numext'],
				"calle" => $cliente['calle'],
			]
		];

		if ($cliente['numint'] != '') {
			$receptor['Domicilio']['noInterior'] = $cliente['numint']; 
		}

# Convierte a objeto DOM $xml
$xml = new DOMDocument();
$xml->loadXML($cfdi);

# Modifica codigos semifijos

// --- NODO RECEPTOR --- 
$xmlreceptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
$xmlreceptor->setAttribute('Nombre', $receptor['nombre']);
$xmlreceptor->setAttribute('Rfc', $receptor['rfc']);
$xmlreceptor->setAttribute('UsoCFDI', $receptor['UsoCFDI']);

// --- NODO EMISOR --- 
$xmlemisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
$xmlemisor->setAttribute('Nombre', $emisor['nombre']);
$xmlemisor->setAttribute('Rfc', $emisor['rfc']);
$xmlemisor->setAttribute('RegimenFiscal', $emisor['Regimen']);

# Agrega conceptos
	$total_iva = 0;

	$xmlconceptos = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Conceptos')->item(0);

	foreach ($cantext as $k => $v) {

		if($v > 0) {
			// *************** crear nodo concepto *****************************
			$xmlconcepto = $xml->createElement("cfdi:Concepto");

			// --- Agregar atributos del concepto ---
			$xmlconcepto->setAttribute('Cantidad', $v);
			$xmlconcepto->setAttribute('ClaveProdServ', $ClaveProdServ[$k]);
			$xmlconcepto->setAttribute('Descripcion', $descext[$k]);
			$xmlconcepto->setAttribute('ClaveUnidad', $ClaveUnidad[$k]);
			$xmlconcepto->setAttribute('Unidad', $uniext[$k]);
			$xmlconcepto->setAttribute('ValorUnitario', $precext[$k]);
			$importe = round(($v * $precext[$k]), 2);
			if($dtoext[$k] > 0) {
				$desc_concpto = round(($importe * ($dtoext[$k] / 100)), 2);
				$desc_concptofmt = number_format($desc_concpto, 6, '.', '');
				$xmlconcepto->setAttribute('Descuento', $desc_concptofmt);
			} else {
				$desc_concpto = 0;
			}
			// --- calcular importe del concepto ---
			$importefmt = number_format($importe, 6, '.', '');
			$xmlconcepto->setAttribute('Importe', $importefmt);
			// --- Append de los conceptos ----------
			$xmlconcepto = $xmlconceptos->appendChild($xmlconcepto);
			// --- Crear nodo impuestos del concepto *************
			$xmlimpuestosc = $xml->createElement("cfdi:Impuestos");
    		// --- Append del nodo ----
			$xmlimpuestosc = $xmlconcepto->appendChild($xmlimpuestosc);
			// --- Crear nodo de traslados
			$xmltrasladosc = $xml->createElement("cfdi:Traslados");
			// --- Append del nodo ----
			$xmltrasladosc = $xmlimpuestosc->appendChild($xmltrasladosc);
			// --- Crear nodo de traslado
			$xmltrasladoc = $xml->createElement("cfdi:Traslado");
			// --- Agregar atributos del traslado ---
			$baseimpuesto = $importe - $desc_concpto;
			$xmltrasladoc->setAttribute('Base', $baseimpuesto);
			$xmltrasladoc->setAttribute('Impuesto', '002');
			$xmltrasladoc->setAttribute('TipoFactor', 'Tasa');
			$xmltrasladoc->setAttribute('TasaOCuota', '0.160000');
			$importe_impuesto = round(($baseimpuesto * 0.160000), 6);
			$xmltrasladoc->setAttribute('Importe', number_format($importe_impuesto, 6, '.', ''));
			// --- Append del nodo ----
			$xmltrasladoc = $xmltrasladosc->appendChild($xmltrasladoc);
			// --- calculos de impuestos ---
			$total_iva = $total_iva + $importe_impuesto;
			$total_descuento = $total_descuento + $desc_concpto;
			$subtotal = $subtotal + $importe;
		}
	}

	$descuento = $total_descuento;
	$suma = $subtotal - $descuento;
	$iva = round($total_iva,2);
	$total = $suma + $iva;
	$subtotal = number_format($subtotal, 2, '.', '');
	$descuento = number_format($descuento, 2, '.', '');
	$suma = number_format($suma, 2, '.', '');
	$iva = number_format($iva, 2, '.', '');
	$total = number_format($total, 2, '.', '');
	$letra = strtoupper(letras2($total));


$fecha_emision = date('Y-m-d');
$fecha_emision .= 'T';
$fecha_emision .= date('H:i:s');

# Calcula totales
// --- NODO COMPROBANTE --- 
$c = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
$c->setAttribute('Serie', $valor['factserie'][1]);
$c->setAttribute('Folio', $fact_num);
$c->setAttribute('FormaPago', $metop);
$c->setAttribute('MetodoPago', $metodo_pago);
$c->setAttribute('SubTotal', $subtotal);

if($descuento > '0') {
	$c->setAttribute('Descuento', $descuento);
}
if($condpago != '') {
	$c->setAttribute('CondicionesDePago', $condpago);
}

$c->setAttribute('Total', $total);
$c->setAttribute('LugarExpedicion', $agencia_cp);
$c->setAttribute('Fecha', $fecha_emision);

$c->getElementsByTagName('Impuestos')->item(0)->setAttribute('TotalImpuestosTrasladados', $iva);
$c->getElementsByTagName('Impuestos')->item(0)->getElementsByTagName('Traslados')->item(0)->getElementsByTagName('Traslado')->item(0)->setAttribute('Impuesto', '002');
$c->getElementsByTagName('Impuestos')->item(0)->getElementsByTagName('Traslados')->item(0)->getElementsByTagName('Traslado')->item(0)->setAttribute('TipoFactor', 'Tasa');
$c->getElementsByTagName('Impuestos')->item(0)->getElementsByTagName('Traslados')->item(0)->getElementsByTagName('Traslado')->item(0)->setAttribute('TasaOCuota', '0.160000');
$c->getElementsByTagName('Impuestos')->item(0)->getElementsByTagName('Traslados')->item(0)->getElementsByTagName('Traslado')->item(0)->setAttribute('Importe', $iva);

unset($c);

# Reconvierte a texto
$cfdi = $xml->saveXML();
unset($xml);
	   
// echo $cfdi . '<br>';
// echo htmlspecialchars($cfdi);
// file_put_contents(DIR_DOCS . 'temporal.xml', $cfdi);

###############################################################
# PASO2. Firma el comprobante que esta en $cfdi en modo texto
#
# Regresa el comprobante firmado en la misma variable $cfdi
###############################################################

# Convierte a modelo DOM
$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido Paso 2");

# Extrae cadena original
$xslt = new XSLTProcessor();
$XSL = new DOMDocument();

// $cadena_ori = file_get_contents("http://www.sat.gob.mx/sitio_internet/cfd/3/cadenaoriginal_3_2/cadenaoriginal_3_2.xslt");
$XSL->load('cadenaoriginal_3_3.xslt', LIBXML_NOCDATA);
error_reporting(0); # Se deshabilitan los errores pues el xssl de la cadena esta en version 2 y eso genera algunos warnings
$xslt->importStylesheet( $XSL );
error_reporting(E_ALL); # Se habilitan de nuevo los errores (se asume que originalmente estaban habilitados)

$c = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);

# A continuacion se incluye el certificado que se usará para firma.

include('../certificados/'.$agencia_rfc.'-certificado.php');

// include('/usr/share/php/Math/BigInteger.php');
include('parciales/BigInteger.php');

// echo 'BigInteger';
# Extrae el número de certificado
# Para su correcto funcionamiento esta seccion requiere el plugin o modulo GMPlib
$cert509 = openssl_x509_read($cert) or die("\nNo se puede leer el certificado\n");
$data = openssl_x509_parse($cert509);
# En $data hay mucha informacion relevante del certificado. Si se desea explorar se puede usar la funcion print_r. Las codificaciones son... interesantes, sobre todo ésta y las fechas

$serial1 = $data['serialNumber'];
// echo $serial1;
$serial2 = new Math_BigInteger($serial1);
$serial2 = $serial2->toHex();
// $serial2 = gmp_strval($serial1, 16);
$serial3 = explode("\n", chunk_split($serial2, 2, "\n"));
$serial = "";
foreach ($serial3 as $serialt) {
	if (2 == strlen($serialt))
		$serial .= chr('0x' . $serialt);
}
$noCertificado = $serial;

unset($serial1, $serial2, $serial3, $serial, $serialt, $data, $cert509);

$c->setAttribute('NoCertificado', $noCertificado);

$cadena = $xslt->transformToXML( $c );
unset($xslt, $XSL);

// echo "Cadena original: " . $cadena . "\n";
// echo "Numero de certificado = [$noCertificado]\n";

# Extrae valores relevantes
# Extrae el certificado, sin enters para anexarlo al cfdi

// echo $cert."\n";

preg_match('/-----BEGIN CERTIFICATE-----(.+)-----END CERTIFICATE-----/msi', $cert, $matches) or die("No certificado\n");
$algo = $matches[1];
$algo = preg_replace('/\n/', '', $algo);
$certificado = preg_replace('/\r/', '', $algo);
// echo "Certificado = [$certificado]\n";

# Extrae la llave privada, en formato openssl
$key = openssl_pkey_get_private($cert) or die("No llave privada\n");

# Firma la cadena original con la llave privada y codifica en base64 el resultado
$crypttext = "";

openssl_sign($cadena, $crypttext, $key, OPENSSL_ALGO_SHA256);
$sello = base64_encode($crypttext);
// echo "sello = [$sello]\n";

# Incorpora los dos elementos al cfdi
$c->setAttribute('Certificado', $certificado);
$c->setAttribute('Sello', $sello);

# regresa el resultado
$cfdi = $xml->saveXML();
unset($c, $xml, $cert, $certificado, $cadena, $crypttext, $key);
// echo htmlspecialchars($cfdi);
// file_put_contents(DIR_DOCS . 'temporal.xml', $cfdi);

###############################################################
# PASO3. Verifica el CFDI en la variable $cfdi
#
# Se interrumpe si hay error
###############################################################

$cadena="";
# Valida UTF8
mb_check_encoding($cfdi, "UTF-8") or die("El string no esta en UTF8\n");

# Convierte a modelo DOM
libxml_use_internal_errors(true);

$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido Paso 3");

//echo htmlspecialchars($cfdi);

# Valida contra esquema
//$xml->schemaValidate('cfdv33.xsd') or die("\n\nNo es un CFDi valido para esquema cfdv33.xsd");

if(!$xml->schemaValidate('cfdv33.xsd')) {
	echo "<br>No es un CFDi valido para esquema cfdv33.xsd<br>";
	echo htmlspecialchars($cfdi);
	echo '<br><br>Errores de validación de esquema:<br>';
	$errors = libxml_get_errors();
	echo '<pre>';
	print_r($errors);
	echo '</pre>';
	libxml_clear_errors();
	echo '<br>.......';
}


//echo htmlspecialchars($cfdi);

# Verifica la firma
$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
# Extrae cadena original
$xslt = new XSLTProcessor();
$XSL = new DOMDocument();
$XSL->load('cadenaoriginal_3_3.xslt', LIBXML_NOCDATA);
error_reporting(0);
$xslt->importStylesheet( $XSL );
error_reporting(E_ALL);
$cadena = $xslt->transformToXML( $Comprobante );
unset($xslt, $XSL);

// echo '<br>Cadena: ' . $cadena . '<br>';

# Extrae el certificado y lo pone en formato que las funciones puedan leer
$cert2 = $Comprobante->getAttribute("Certificado");
$cert  = "-----BEGIN CERTIFICATE-----\n";
$cert .= chunk_split($cert2, 64, "\n");
$cert .= "-----END CERTIFICATE-----\n";

if (!($pkey = openssl_pkey_get_public($cert))) {
	echo "\n\n\nNo es posible extraer llave publica\n";
	die;
}

# Extrae sello
$crypttext = base64_decode($Comprobante->getAttribute("Sello"));

// echo "Sello decodificado:<br><br>".$crypttext;

// echo htmlspecialchars($cfdi);


if (openssl_verify($cadena, $crypttext, $pkey, OPENSSL_ALGO_SHA256)) {
//	echo  "El firmado es correcto\n";
} else {
	die("\nError en el firmado!!!\n");
}

file_put_contents(DIR_DOCS . 'temporal.xml', $cfdi);

unset($xml, $Comprobante, $cert2, $cert, $pkey, $crypttext, $cadena);


###############################################################
# PASO4. Timbra el CFDI en la variable $cfdi con TimbreFiscal
#
#        4.1) Ensobreta
#        4.2) Envía a TimbreFiscal
#        4.3) Recibe un timbre (o procesa un error)
# Regresa el $cfdi intacto y $timbre
###############################################################

//$cfdiversion = 3.3;

include_once('parciales/'.$pac_prov);


###############################################################
# PASO5. Integra el timbre recibido en $timbre en el $cfdi
#
# Regresa el $cfdi ya integrado con el timbre
###############################################################
// echo "\n\nPASO5. Integra el timbre recibido en \$timbre en el \$cfdi\n";
# Convierte a modelo DOM

$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido paso 5");
$xml->schemaValidate('cfdv33.xsd') or die("\n\n\nCFDi no valido paso 5 validación cfdv33.xsd");
# Valida que realmente haya regresado un timbre
$sobretimbre = new DOMDocument();
if($sobretimbre->loadXML($timbre)) {
        // Respuesta XML válido para Timbre
} else {
        echo 'Codigo de respuesta del PAC: ' . $res_codigo . '<br>';
        echo 'Mensaje de respuesta del PAC: ' . $res_mensaje . '<br>';
}
$sobretimbre->loadXML($timbre) or die("\n\n\nXML de respuesta timbrado no valido\n");
# Extrae el timbre (si existe)
$xmltimbre = new DOMDocument('1.0', 'UTF-8');
# Extrae el nodo
$paso = $sobretimbre->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);
$res_uuid = $paso->getAttribute("UUID");
$paso = $xmltimbre->importNode($paso, true);
$xmltimbre->appendChild($paso);
unset($paso);
# Valida
# $xmltimbre->schemaValidate('TimbreFiscalDigitalv11.xsd') or die("\n\n\nError de validación Timbre Fiscal.\n\n$return");
# Incorpora el timbre en el nodo complemento. Si no existe dicho nodo, lo crea
$complemento = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Complemento')->item(0);
if (!$complemento) {
	$complemento = $xml->createElementNS('http://www.sat.gob.mx/cfd/3', 'Complemento');
	$xml->appendChild($complemento);
}
$t = $xmltimbre->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);
$t = $xml->importNode($t, true);
$complemento->appendChild($t);
$cfdi = $xml->saveXML();

if(isset($addenda) && $addenda != '') {
	$Compro = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
	$salto = $xml->createTextNode("\t");
	$salto = $Compro->appendChild($salto);
	$AddenD = $xml->createElementNS('http://www.sat.gob.mx/cfd/3', 'Addenda');
	$AddenD = $Compro->appendChild($AddenD);
	$salto = $xml->createTextNode("\n");
	$salto = $Compro->appendChild($salto);
	$cfdi = $xml->saveXML();
	include('parciales/' . $addenda);
//	echo htmlspecialchars($cfdi);
}

unset($timbre, $xml, $sobretimbre, $xmltimbre, $paso, $complemento, $t);

$nombre_cfdi = $valor['factserie'][1] . $fact_num . '-' . $res_uuid;

file_put_contents(DIR_DOCS.$nombre_cfdi.'.xml', $cfdi);

		include('parciales/phpqrcode/qrlib.php');
		$fe = substr($sello, -8);
		$ftotal = number_format($total, 6, '.', '');
		$qrtotal = strval($ftotal);
		$qrtotal = sprintf('%017s', $qrtotal);
		$codigoqr = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=' . $res_uuid . '&re=' . $agencia_rfc . '&rr=' . $cliente['rfc'] . '&tt=' . $qrtotal . '&fe=' . $fe;
		$imagenqr = DIR_DOCS . $nombre_cfdi . '.png';
		QRcode::png($codigoqr, $imagenqr, 'L', 4, 2);

	$doc_nombre = 'Factura XML ' . $valor['factserie'][1] . $fact_num;
	$sql_data_array = array('doc_nombre' => $doc_nombre,
		'doc_clasificado' => 1,
		'doc_usuario' => $_SESSION['usuario'],
		'doc_archivo' => $nombre_cfdi . '.xml');
	if($orden_id != '') {
		$sql_data_array['orden_id'] = $orden_id;
	} else {
		$sql_data_array['previa_id'] = $previa_id;
	}
	ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
	// --- Copia el archivo al servidor de respaldo ---
	if($servrespaldo != '') {
		$conecta = ssh2_connect($servrespaldo, 2922);
		ssh2_scp_send($conecta, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_cfdi . '.xml', '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_cfdi . '.xml', 0644);
	}

	$pregtim = "UPDATE " . $dbpfx . "valores SET val_numerico = (val_numerico - 1) WHERE val_nombre = 'timbres'";
	$matrtim = mysql_query($pregtim) or die("ERROR: Fallo actualización de timbres! ".$pregtim);
	$archivo = '../logs/' . time() . '.ase';
	$myfile = file_put_contents($archivo, $pregtim . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
   
	$doc_nombre = 'Factura PDF ' . $valor['factserie'][1] . $fact_num;
	$sql_data_array = array('doc_nombre' => $doc_nombre,
		'doc_clasificado' => 1,
		'doc_usuario' => $_SESSION['usuario'],
		'doc_archivo' => $nombre_cfdi . '.pdf');
	if($orden_id != '') {
		$sql_data_array['orden_id'] = $orden_id;
	} else {
		$sql_data_array['previa_id'] = $previa_id;
	}
	ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
	if($orden_id != '') {
		bitacora($orden_id, $doc_nombre, $dbpfx);
	} else {
		bitacora('0', $doc_nombre, $dbpfx, '', '', '', $previa_id);
	}
   
  	$sql_data = array('fact_serie' => $valor['factserie'][1],
  		'fact_num' => $fact_num,
  		'orden_id' => $orden_id,
  		'previa_id' => $previa_id,
  		'reporte' => $reporte,
  		'cliente_id' => $cliente['id'], 
  		'fact_rfc' => $cliente['rfc'],
  		'fact_sub' => $suma,
  		'fact_iva' => $iva,
  		'fact_total' => $total,
  		'fact_fecha' => date('Y-m-d'),
  		'usuario' => $_SESSION['usuario']);
  	ejecutar_db($dbpfx . 'facturas', $sql_data, 'insertar');
  
  	$factcomp = $valor['factserie'][1] . $fact_num;
   
  	$sql_data = array(
  		'orden_id' => $orden_id,
  		'previa_id' => $previa_id,
  		'reporte' => $reporte,
  		'cliente_id' => $cli['cliente_id'],
  		'aseguradora_id' => $ase['aseguradora_id'], 
  		'fact_num' => $factcomp,
		'fact_rfc_emisor' => $emisor['rfc'],
		'fact_rfc_receptor' => $receptor['rfc'],
  		'fact_uuid' => $res_uuid,
  		'fact_fecha_emision' => date('Y-m-d H:i:s', time()),
  		'fact_tipo' => '1',
  		'fact_monto' => $total,
  		'usuario' => $_SESSION['usuario']);
  	$fact_id = ejecutar_db($dbpfx . 'facturas_por_cobrar', $sql_data, 'insertar');
  
	$sql_data = array('fact_id' => $fact_id);
/*	if(is_array($tareas) && count($tareas) > 0) {
		$tarea = explode('|', $tareas);
	}
*/	foreach($tarea as $tar) {
		$param = "sub_orden_id = '" . $tar . "'";
		ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $param);
		$pregtar = "UPDATE " . $dbpfx . "subordenes SET sub_impuesto = (sub_presupuesto * " . $impuesto_iva . ") WHERE sub_orden_id = '" . $tar . "'";
		$matrtar = mysql_query($pregtar) or die("ERROR: Fallo actualización de impuesto! " . $pregtar);
		$archivo = '../logs/' . time() . '-base.ase';
		$myfile = file_put_contents($archivo, $pregtar . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);

		if($orden_id != '') {
			bitacora($orden_id, 'Tarea ' . $tar . ' facturada con la factura ' . $fact_id, $dbpfx);
		} else {
			bitacora('0', 'Tarea ' . $tar . ' facturada con la factura ' . $fact_id, $dbpfx, '', '', '', $previa_id);
		}
	}

// ------ Insertar descuentos como Ajustes Administrativos ---------

	if($descuento > 0) {
		$sql_data = array(
			'fact_id' => $fact_id,
			'orden_id' => $orden_id,
			'reporte' => $reporte,
			'motivo' => 'Descuento: ' . $motivo,
			'monto' => ($descuento + ($descuento * $impuesto_iva)),
			'usuario' => $_SESSION['usuario'],
			'fecha_ajuste' => date('Y-m-d H:i:s')
		);
		ejecutar_db($dbpfx . 'ajusadmin', $sql_data, 'insertar');
		bitacora($orden_id, 'Registro de Ajuste Administrativo por descuento incluido en la factura ' . $fact_id, $dbpfx);
	}


// ------------- Redirigir a ex-3.3.php  -------------------------   

	$_SESSION['fact']['facturada'] = 1;
   if($orden_id != '') {
		redirigir('ex-3.3.php?axml=' . $nombre_cfdi . '.xml&orden_id=' . $orden_id . '&reporte=' . $reporte . '&obsad=' . $obsad);
	} else {
		redirigir('ex-3.3.php?axml=' . $nombre_cfdi . '.xml&previa_id=' . $previa_id . '&reporte=' . $reporte . '&obsad=' . $obsad);
	}   

	} else {
		$_SESSION['msjerror'] = $mensaje;
//		echo $mensaje;
		if($orden_id != '') {
			redirigir('factura-3.3.php?accion=consultar&orden_id=' . $orden_id);
		} else {
			redirigir('factura-3.3.php?accion=consultar&previa_id=' . $previa_id);
		}
	}
}
echo '		</div>'."\n";
include('parciales/pie.php');

?>
