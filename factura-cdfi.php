<?php 
foreach($_POST as $k => $v){$$k=$v; } // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;}  // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('parciales/numeros-a-letras.php');
include('idiomas/' . $idioma . '/factura.php');

if ($accion==="consultar") {
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '	<div id="principal">';
//	echo 'Estamos en la sección generar factura';
	$error = 'si'; $num_cols = 0; $mensaje = 'No es válido el número de OT indicado';
	if ($orden_id!='') {
		$preg0 = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '189' GROUP BY sub_reporte";
     	$mat0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
     	$num_rep = mysql_num_rows($mat0);
		$error = 'no';
		$mensaje = '';
     	$preg1 = "SELECT * FROM " . $dbpfx . "facturas WHERE fact_serie = '" . $agencia_serie . "' ORDER BY fact_num DESC LIMIT 1";
     	$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion!");
     	$facturas = mysql_num_rows($matr1);
     	if($facturas > 0) {
     		$fact = mysql_fetch_array($matr1);
     		$fact_num = $fact['fact_num'] + 1;
     		if($agencia_folio_final >= $fact_num) {
     			$fact_libres = $agencia_folio_final - $fact['fact_num'];
     			if($fact_libres < 10) {
     				$alerta = 'Quedan ' . $fact_libres . ' facturas disponibles: solicitar nuevos folios a la brevedad!';
     			}
     		} else {
     			$error = 'si';
     			$mensaje = 'Folios agotados, no se pueden emitir más facturas de la serie ' . $agencia_serie;
     		}
     	} else {
     		$fact_num = $agencia_folio_inicial;
     	}
	} else {
		$error = 'si';
	}

	if ($num_rep > 0 && $error ==='no') {
		echo '	<form action="factura.php?accion=confirma" method="post" enctype="multipart/form-data" name="imprime">'  . "\n";
     	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td><span class="alerta">' . $_SESSION['fact']['mensaje'] . '<br>' . $alerta . '</span></td></tr>
'  . "\n";
		unset($_SESSION['fact']);
     	echo '		<tr class="cabeza_tabla"><td>Facturas para la Orden de Trabajo: ' . $orden_id . '</td></tr>';
		$hoy = date('Y-m-dTH:i:s');
		echo '		<tr><td>Fecha de emisión de la factura (día-mes-año): <input type="text" name="fecha" value="' . $hoy . '" size="9" /></td></tr>';
		echo '		<input type="hidden" name="orden_id" value="' . $orden_id . '" />';
		echo '		<tr><td>Se va a utilizar la Serie: <strong>' . $agencia_serie . '</strong>  y el número de Factura: <input type="text" name="fact_num" value="' . $fact_num . '" size="8" /></td></tr>';
		if ($num_rep > 1) {
   	  	echo '		<tr><td style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">
			Existe más de un servicio que se puede facturar, elija el siniestro adecuado o 0 (cero) para trabajo particular:
		</td></tr>' . "\n";
     		echo '		<tr><td><select name="dato" size="1">' . "\n";
			echo '			<option value="" >Seleccione...</option>';
	     	while($rep = mysql_fetch_array($mat0)) {
   	  		echo '			<option value="' . $rep['sub_reporte'] . '|' . $rep['sub_aseguradora'] . '">' . $rep['sub_reporte'] . '</option>' . "\n";
			}
			echo '		</select></td></tr>' . "\n";
		} else {
			$rep = mysql_fetch_array($mat0);
			echo '		<input type="hidden" name="dato" value="' . $rep['sub_reporte'] . '|' . $rep['sub_aseguradora'] . '" />';
		}
//		echo $rep['sub_reporte'];
		echo '		<tr><td><input type="submit" value="Enviar" /></td></tr>';
		echo '		</table></form>';
	} else {
		echo '<p>' . $mensaje . '</p>';
	}
}

elseif($accion==='confirma') {
	if ($_SESSION['rol04']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta para este Rol');
	}
	unset($_SESSION['fact']);
	$_SESSION['fact'] = array();

	$error = 'no'; $mensaje= '';
	$datos = explode('|', $dato);
	$reporte = $datos[0];
	$aseguradora = $datos[1];

	if($orden_id!='' && !is_null($reporte)) {
		$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '$reporte'";
   	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
   	$filas = mysql_num_rows($matriz);
   } else {
   	$error = 'si';
		$mensaje= 'No se encontraron registros con los datos indicados.<br>';
	}
	
	if($fecha=='' || !isset($fecha)) { 
		$error = 'si';
		$mensaje .= 'Por favor corrija la fecha de la factura.<br>';
	}
	
	if($filas > 0) {
		$total_ref = 0; $total_con = 0; $total_mo = 0; 
		while ($sub = mysql_fetch_array($matriz)) {
			$total_ref = $total_ref + $sub['sub_partes'];
			$total_con = $total_con + $sub['sub_consumibles'];
			$total_mo = $total_mo + $sub['sub_mo'];
			if($sub['sub_refacciones_recibidas']=='1' && $factsinpend == '1') { 
				$_SESSION['fact']['mensaje'] = 'No se puede facturar, aún hay refacciones por recibir.';
				redirigir('factura.php?accion=consultar&orden_id=' . $orden_id); 
			}
			if($sub['sub_estatus'] < '112' && $factsinpend == '1') { 
				$_SESSION['fact']['mensaje'] = 'No se puede facturar, aún hay tareas por terminar.';
				redirigir('factura.php?accion=consultar&orden_id=' . $orden_id); 
			}
		}
		$subtotal = $total_ref + $total_con + $total_mo;
	
		if($subtotal == 0) { 
			$error = 'si';
			$mensaje .= 'No se puede facturar, el importe no puede ser 0 cero.<br>';
		}
	}
	
   if($filas > 0 && $error == 'no') {
	  	$pregunta4 = "SELECT v.vehiculo_poliza, v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_modelo, v.vehiculo_placas FROM " . $dbpfx . "ordenes o, " . $dbpfx . "vehiculos v WHERE o.orden_id = '$orden_id' AND o.orden_vehiculo_id = v.vehiculo_id";
  		$matriz4 = mysql_query($pregunta4) or die("ERROR: Fallo seleccion!");
	  	$_SESSION['fact']['datosv']  = mysql_fetch_array($matriz4);
//	  	unset($datosv);
		if($aseguradora == 0) {
			$pregunta2 = "SELECT e.empresa_razon_social, e.empresa_rfc, e.empresa_calle, e.empresa_colonia, e.empresa_cp, e.empresa_municipio, e.empresa_estado, e.empresa_pais FROM " . $dbpfx . "ordenes o, " . $dbpfx . "empresas e, " . $dbpfx . "clientes c  WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id AND c.cliente_empresa_id = e.empresa_id";
   		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
   		$cli = mysql_fetch_array($matriz2);
   		$cliente = array(
   			'nombre' => strtoupper($cli['empresa_razon_social']), 
   			'calle' =>  strtoupper($cli['empresa_calle']), 
   			'colonia' =>  strtoupper($cli['empresa_colonia']), 
   			'cp' =>  $cli['empresa_cp'], 
   			'municipio' =>  strtoupper($cli['empresa_municipio']), 
   			'estado' =>  strtoupper($cli['empresa_estado']), 
   			'pais' =>  strtoupper($cli['empresa_pais']), 
   			'descuento' =>  $cli['empresa_descuento'], 
   			'rfc' =>  strtoupper($cli['empresa_rfc'])
   		);
		} else {
			$pregunta2 = "SELECT * FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $aseguradora . "'";
   		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
   		$ase = mysql_fetch_array($matriz2);
   		$cliente = array(
   			'nombre' => $ase['aseguradora_razon_social'], 
   			'calle' =>  $ase['aseguradora_calle'], 
   			'colonia' =>  $ase['aseguradora_colonia'], 
   			'cp' =>  $ase['aseguradora_cp'], 
   			'municipio' =>  $ase['aseguradora_municipio'], 
   			'estado' =>  $ase['aseguradora_estado'], 
   			'pais' =>  $ase['aseguradora_pais'], 
   			'descuento' =>  $ase['aseguradora_descuento'], 
   			'rfc' =>  $ase['aseguradora_rfc']
   		);
   	}
   	$_SESSION['fact']['cliente'] = $cliente;
  		$_SESSION['fact']['subtotal'] = round($subtotal, 2);
  		$_SESSION['fact']['descuento'] = round(($subtotal * ($cliente['descuento']/100)), 2);
  		$_SESSION['fact']['suma'] = $subtotal - $_SESSION['fact']['descuento'];
  		$_SESSION['fact']['iva'] = round(($_SESSION['fact']['suma'] * 0.16), 2);
  		$_SESSION['fact']['total'] = $_SESSION['fact']['suma'] + $_SESSION['fact']['iva'];
  		$_SESSION['fact']['letra'] = strtoupper(letras2($total));
   	
   	
   	include('parciales/encabezado.php'); 
		echo '	<div id="body">' . "\n";
			include('parciales/menu_inicio.php');
		echo '	<div id="principal">' . "\n";
		echo '		<div >
		<form action="factura.php?accion=imprime" method="post" enctype="multipart/form-data" name="confirma">
		<table cellpadding="0" cellspacing="0" border="0" width="840px" class="izquierda">
			<tr>
				<td style="width:210px;"><img src="imagenes/logo-agencia.png" alt="' . $agencia_razon_social . '"></td>
				<td style="width:290px;"><strong>' . $agencia_razon_social . '</strong><br>
				' . $agencia_direccion . '<br>
				' . $agencia_colonia . ', '  . $agencia_municipio . '.<br>
				' . $agencia_cp . '. '  . $agencia_estado . '. México.<br>
				' . $agencia_telefonos . '<br>
				</td>
			</tr>
			<tr><td colspan="2"><hr></td></tr>
			<tr><td>Factura</td><td>' . $agencia_serie . $fact_num . '</td></tr>
			<tr><td>Lugar de Expedición:</td><td>'  . $agencia_lugar_emision . '</td></tr>
			<tr><td>Fecha de Emisión:</td><td>' . $fecha . '</td></tr>
			<tr><td colspan="2"><hr></td></tr>
			<tr><td>Cliente: </td><td>' . $_SESSION['fact']['cliente']['nombre'] . '</td></tr>
			<tr><td>Dirección: </td><td>' . $_SESSION['fact']['cliente']['calle'] . '</td></tr>
			<tr><td>Colonia y Municipio: </td><td>' . $_SESSION['fact']['cliente']['colonia'] . ', ' . $_SESSION['fact']['cliente']['municipio'] . '</td></tr>
			<tr><td>CP y Entidad: </td><td>' . $_SESSION['fact']['cliente']['cp'] . '. ' . $_SESSION['fact']['cliente']['estado'] . ', ' . $_SESSION['fact']['cliente']['pais'] . '</td></tr>
			<tr><td>RFC: </td><td>' . $_SESSION['fact']['cliente']['rfc'] . '</td></tr>
			<tr><td colspan="2"><hr></td></tr>
			<tr><td colspan="2">Conceptos a facturar</td></tr>'."\n";
		mysql_data_seek($matriz,0);
		if($fact_resumen == 1) {
			$j=0;
			while ($sub = mysql_fetch_array($matriz)) {
//				print_r($sub); echo '<br><br>';
				$area_tot = 0;
				$_SESSION['fact']['concep'][$j]['cantidad'] = 1;
				$_SESSION['fact']['concep'][$j]['unidad'] = 'Lote';
				
//				$frase = '1 Lote de ';
				if($sub['sub_partes'] > 0 ) {
					$_SESSION['fact']['concep'][$j]['descripcion'] .= 'Refacciones';
					$area_tot = $area_tot + $sub['sub_partes'];
				}
				if($sub['sub_consumibles'] > 0 ) {
					if($area_tot > 0) { $_SESSION['fact']['concep'][$j]['descripcion'] .= ', '; }
					$_SESSION['fact']['concep'][$j]['descripcion'] .= 'Materiales';
					$area_tot = $area_tot + $sub['sub_consumibles'];
				}
				if($sub['sub_mo'] > 0 ) {
					if($area_tot > 0) { $_SESSION['fact']['concep'][$j]['descripcion'] .= ' y '; }
					$_SESSION['fact']['concep'][$j]['descripcion'] .= 'Mano de Obra';
					$area_tot = $area_tot + $sub['sub_mo'];
				}
				$_SESSION['fact']['concep'][$j]['descripcion'] .= ' para el Servicio de ' . constant('NOMBRE_AREA_'.$sub['sub_area']);
				echo '			<tr><td>' . $_SESSION['fact']['concep'][$j]['descripcion'] . ': </td><td><input type="text" name="concepto[' . $j . ']" value="' . money_format('%n', $area_tot) . '" /></td></tr>'."\n";
				if($area_tot > 0) { $j++;}
			}
		}
		echo '			<tr><td colspan="2"><hr></td></tr>'."\n";
		if($cliente['descuento'] > 0) {
			echo '			<tr><td>Descuento Aplicable</td><td>' . $cliente['descuento'] . '</td></tr>'."\n";
		}
		echo '			<tr><td>Sub Total Global</td><td>' . money_format('%n', $_SESSION['fact']['subtotal']) . '</td></tr>
			<tr><td>Descuento Global</td><td>' . money_format('%n', $_SESSION['fact']['descuento']) . '</td></tr>
			<tr><td>Sub Total Neto</td><td>' . money_format('%n', $_SESSION['fact']['suma']) . '</td></tr>
			<tr><td>IVA al 16%</td><td>' . money_format('%n', $_SESSION['fact']['iva']) . '</td></tr>
			<tr><td>Total Neto</td><td>' . money_format('%n', $_SESSION['fact']['total']) . '</td></tr>
			<tr><td>Total en letra</td><td>' . $_SESSION['fact']['letra'] . '</td></tr>'."\n";
		echo '			<tr><td>Forma de Pago</td><td><input type="text" name="formapago" value="Pago en una sola exhibición" ></td></tr>
			<tr><td>Método de Pago</td><td><input type="text" name="metodopago" value="No definido" ></td></tr>
			<tr><td>Número de cuenta de Pago</td><td><input type="text" name="cuentapago" value="No definido" ></td></tr>'."\n";

		if($aseguradora > 0) {
			echo '			<tr><td>SINIESTRO</td><td>' . $reporte . '</td></tr>
			<tr><td>POLIZA</td><td>' . $_SESSION['fact']['datosv']['vehiculo_poliza'] . '</td></tr>'."\n";
		}

		echo '			<tr><td>MARCA</td><td>' . $_SESSION['fact']['datosv']['vehiculo_marca'] . '</td></tr>
			<tr><td>AUTO</td><td>' . $_SESSION['fact']['datosv']['vehiculo_tipo'] . '</td></tr>
			<tr><td>AÑO</td><td>' . $_SESSION['fact']['datosv']['vehiculo_modelo'] . '</td></tr>
			<tr><td>PLACAS</td><td>' . $_SESSION['fact']['datosv']['vehiculo_placas'] . '</td></tr>
			<tr><td colspan="2"><hr></td></tr>
			<tr><td colspan="2"><button name="Confirmar" value="Confirmar" type="submit">Confirmar Datos</button></td></tr>
		</table>
		</div>';
		echo '			<input type="hidden" name="fact_num" value="' . $fact_num . '" />
			<input type="hidden" name="reporte" value="' . $reporte . '" />
			<input type="hidden" name="aseguradora" value="' . $aseguradora . '" />
			<input type="hidden" name="fecha" value="' . $fecha . '" />
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />'."\n";
		echo '		<div class="control">';
		if($_SESSION['rol04']=='1' && $accion==='imprime') {
			echo '<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="Imprimir Factura" title="Imprimir Factura"></a> | ';
		}
		echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div>
		</form>';
		unset($_SESSION['fact']);
	} else {
		$_SESSION['fact']['mensaje'] = $mensaje;
		redirigir('factura.php?accion=consultar&orden_id=' . $orden_id);
	}
}

elseif($accion==='imprime') {
	if ($_SESSION['rol04']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
function satxmls_cargaAtt(&$nodo, $attr) {
	foreach ($attr as $key => $val) {
		$val = preg_replace('/\s\s+/', ' ', $val);   // Regla 5a y 5c
		$val = trim($val);                           // Regla 5b
		if (strlen($val)>0) {   // Regla 6
			$val = utf8_encode(str_replace("|","/",$val)); // Regla 1
			$nodo->setAttribute($key,$val);
		}
	}
}

$cadena_original = '||';
$cadena_original .= '3.2|';
$cadena_original .= $fecha.'|';
$cadena_original .= 'ingreso|';
$cadena_original .= $formapago.'|';
$cadena_original .= $_SESSION['fact']['suma'].'|';
$cadena_original .= $_SESSION['fact']['descuento'].'|';
$cadena_original .= $_SESSION['fact']['total'].'|';
$cadena_original .= $metodopago.'|';
$cadena_original .= $agencia_municipio . ', ' . $agencia_estado.'|';
$cadena_original .= $agencia_rfc.'|';
$cadena_original .= $agencia_razon_social.'|';
$cadena_original .= $agencia_calle.'|';
$cadena_original .= $agencia_num_ext.'|';
$cadena_original .= $agencia_colonia.'|';
$cadena_original .= $agencia_municipio.'|';
$cadena_original .= $agencia_estado.'|';
$cadena_original .= $agencia_pais.'|';
$cadena_original .= $agencia_cp.'|';
$cadena_original .= $agencia_regimen.'|';
$cadena_original .= $_SESSION['fact']['cliente']['rfc'].'|';
$cadena_original .= $_SESSION['fact']['cliente']['nombre'].'|';
$cadena_original .= $_SESSION['fact']['cliente']['calle'].'|';
$cadena_original .= $_SESSION['fact']['cliente']['colonia'].'|';
$cadena_original .= $_SESSION['fact']['cliente']['municipio'].'|';
$cadena_original .= $_SESSION['fact']['cliente']['estado'].'|';
$cadena_original .= $_SESSION['fact']['cliente']['pais'].'|';
$cadena_original .= $_SESSION['fact']['cliente']['cp'].'|';
foreach($_SESSION['fact']['concep'] as $k => $v) {
	$cadena_original .= '|';
}
$cadena_original .= '|';
$cadena_original .= '|';
$cadena_original .= '|';
$cadena_original .= '|';
$cadena_original .= '|';
$cadena_original .= '|';
$cadena_original .= '|';
$cadena_original .= '|';
$cadena_original .= '|';
$cadena_original .= '|';
$cadena_original .= '|';


$xml = new DOMdocument("1.0","UTF-8");
$root = $xml->createElement("cfdi:Comprobante");
$root = $xml->appendChild($root);

satxmls_cargaAtt($root, array("version"=>"3.2",
                      "serie"=>$agencia_serie,
                      "folio"=>$fact_num,
                      "fecha"=>$fecha,
                      "formaDePago"=>$formapago,
                      "subTotal"=>$_SESSION['fact']['suma'],
                      "total"=>$_SESSION['fact']['total'],
                      "metodoDePago"=>$metodopago,
                      "NumCtaPago"=>$cuentapago,
                      "LugarExpedicion"=>$agencia_municipio . ', ' . $agencia_estado,
                      "tipoDeComprobante"=>'ingreso',
                      "xmlns:cfdi"=>'http://www.sat.gob.mx/cfd/3" xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd',
                      "noCertificado"=>$numcertificado,
                      "sello"=>$sello,
                      "certificado"=>$certificado
                   )
                );

   	
   	$preg0 = "SELECT fact_id FROM " . $dbpfx . "facturas WHERE fact_serie = '" . $agencia_serie . "' AND fact_num = '" . $fact_num . "'";
   	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de facturas!");
   	$fact_rep = mysql_num_rows($matr0);
   	if($fact_rep == 0) {
	   	$sql_data = array('fact_serie' => $agencia_serie,
   			'fact_num' => $fact_num,
   			'orden_id' => $orden_id,
   			'reporte' => $reporte,
   			'cliente_id' => $cliente['id'], 
   			'fact_rfc' => $cliente['rfc'],
   			'fact_sub' => $suma,
	   		'fact_iva' => $iva,
   			'fact_total' => $total,
   			'fact_fecha' => $fecha,
   			'usuario' => $_SESSION['usuario']);
	   	ejecutar_db($dbpfx . 'facturas', $sql_data, 'insertar');
   	}
   	include('parciales/encabezado.php'); 
		echo '	<div id="body">' . "\n";
			include('parciales/menu_inicio.php');
		echo '	<div id="principal">' . "\n";
		echo '		<div >
		<table cellpadding="0" cellspacing="0" border="0" width="840px" class="izquierda" style=" border-width: 1px; border-style: solid;">
			<tr>
				<td style="width:210px;"><img src="imagenes/logo-agencia.png" alt="' . $agencia_razon_social . '"></td>
				<td style="width:390px;"><strong>' . $agencia_razon_social . '</strong><br>
				' . $agencia_direccion . '<br>
				' . $agencia_colonia . ', '  . $agencia_municipio . '.<br>
				' . $agencia_cp . '. '  . $agencia_estado . '. México.<br>
				' . $agencia_telefonos . '<br>
				<strong>RFC: ' . $agencia_rfc . '</strong><br>
				' . $agencia_regimen . '</td>
				<td style="width:230px; border-left-width: 1px; border-left-style: solid;">
					<h2>Factura ' . $agencia_serie . $fact_num . '</h2>
					<hr>
					Lugar de expedición:<br>'  . $agencia_lugar_emision . '<br>
					Fecha: ' . $fecha . '	</td>
			</tr>
			<tr><td colspan="3"><hr></td></tr>
			<tr><td>Cliente: </td><td colspan="2">' . $cliente['nombre'] . '</td></tr>
			<tr><td>Dirección: </td><td colspan="2">' . $cliente['calle'] . '</td></tr>
			<tr><td>Colonia y Municipio: </td><td colspan="2">' . $cliente['colonia'] . ', ' . $cliente['municipio'] . '</td></tr>
			<tr><td>CP y Entidad: </td><td colspan="2">' . $cliente['cp'] . '. ' . $cliente['estado'] . ', ' . $cliente['pais'] . '</td></tr>
			<tr><td><strong>RFC: </strong></td><td colspan="2"><strong>' . $cliente['rfc'] . '</strong></td></tr>
		</table><br>'."\n";
		echo '		<table cellpadding="0" cellspacing="0" border="1" width="840px" class="izquierda">
			<tr><td>Cantidad</td><td>Unidad</td><td>Descripción</td><td>P Unitario</td><td>Importe</td></tr>'."\n";
		mysql_data_seek($matriz,0);
		if($fact_resumen == 1) {
			while ($sub = mysql_fetch_array($matriz)) {
				if($sub['sub_partes'] > 0 ) {
					echo '			<tr><td style="text-align:center">1</td><td style="text-align:center">N/A</td><td>Lote de Partes y Refacciones para el Servicio de ' . constant('NOMBRE_AREA_'.$sub['sub_area']) . '</td><td style="text-align:right">' . money_format('%n', $sub['sub_partes']) . '</td><td style="text-align:right">' . money_format('%n', $sub['sub_partes']) . '</td></tr>'."\n";
				}
				if($sub['sub_consumibles'] > 0 ) {
					echo '			<tr><td style="text-align:center">1</td><td style="text-align:center">N/A</td><td>Lote de Consumibles y Materiales para el Servicio de ' . constant('NOMBRE_AREA_'.$sub['sub_area']) . '</td><td style="text-align:right">' . money_format('%n', $sub['sub_consumibles']) . '</td><td style="text-align:right">' . money_format('%n', $sub['sub_consumibles']) . '</td></tr>'."\n";
				}
				if($sub['sub_mo'] > 0 ) {
					echo '			<tr><td style="text-align:center">1</td><td style="text-align:center">N/A</td><td>Paquete de Mano de Obra para el Servicio de ' . constant('NOMBRE_AREA_'.$sub['sub_area']) . '</td><td style="text-align:right">' . money_format('%n', $sub['sub_mo']) . '</td><td style="text-align:right">' . money_format('%n', $sub['sub_mo']) . '</td></tr>'."\n";
				}
			}
		}
		if($cliente['descuento'] > 0) {
			echo '			<tr><td>1</td><td>N/A</td><td>Descuento</td><td style="text-align:right">' . money_format('%n', ($descuento * -1)) . '</td><td style="text-align:right">' . money_format('%n', $descuento) . '</td></tr>'."\n";
		}
		echo '			<tr><td colspan="3" rowspan="3">
				Observaciones: ';
		if($aseguradora > 0) {
			echo 'POLIZA: ' . $datosv['vehiculo_poliza'] . '. SINIESTRO: ' . $reporte . '.';
		}
		echo ' VEHICULO MARCA: ' . $datosv['vehiculo_marca'] . ' MODELO: ' . $datosv['vehiculo_tipo'] . ' AÑO: ' . $datosv['vehiculo_modelo'] . ' PLACAS: ' . $datosv['vehiculo_placas'] . '<br><br>Total en letra: ' . $letra . ''."\n";
		echo '			</td><td>SubTotal</td><td style="text-align:right">' . money_format('%n', $suma) . '</td></tr>
			<tr><td>IVA al 16%</td><td style="text-align:right">' . money_format('%n', $iva) . '</td></tr>
			<tr><td>Total</td><td style="text-align:right">' . money_format('%n', $total) . '</td></tr>
		</table><br>'."\n";

		echo '		<table cellpadding="3" cellspacing="0" border="1" width="840px">
			<tr><td rowspan="3"><img src="' . $agencia_cbb . '" alt="" style="padding:3px;"></td><td>Método de Pago: </td><td>Cuenta: </td></tr>
			<tr><td colspan="2">Tipo de Pago: ' . $agencia_tipo_pago . '</td></tr>
			<tr><td colspan="2" style="vertical-align:top;">
				Número de Aprobación SICOFI: <strong>' . $agencia_sicofi . '.</strong><br>
				La reproducción apócrifa de este comprobante constituye un delito en los términos de las disposiciones fiscales.<br> 
   			Este comprobante tendrá una vigencia de dos años contados a partir de la fecha de aprobación de la asignación de folios, la cual es: ' . $agencia_fecha_aprobacion . '
			</td></tr>'."\n";

		echo '					</table>
		</div>';
		echo '			<input type="hidden" name="fact_num" value="' . $fact_num . '" />
			<input type="hidden" name="reporte" value="' . $reporte . '" />
			<input type="hidden" name="aseguradora" value="' . $aseguradora . '" />
			<input type="hidden" name="fecha" value="' . $fecha . '" />
			<input type="hidden" name="orden_id" value="' . $orden_id . '" />'."\n";
		echo '		<div class="control">';
		if($_SESSION['rol04']=='1' && $accion==='imprime') {
			echo '<a href="javascript:window.print()">Imprimir Factura</a> | ';
		}
		echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '">Regresar a la Orden de Trabajo</a></div>
		</form>';
		unset($_SESSION['fact']);
	} else {
		$_SESSION['fact']['mensaje'] = $mensaje;
		redirigir('factura.php?accion=consultar&orden_id=' . $orden_id);
	}
}



?>
		</div>
	</div>
<p class="footer">Derechos Reservados 2009 - 2013</p>
</div>
</body>
</html>