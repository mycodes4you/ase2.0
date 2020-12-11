<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}
include('parciales/numeros-a-letras.php');
include('idiomas/' . $idioma . '/factura.php');

if ($accion==="consultar") {
	
	$funnum = 1095000;
	
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '	<div id="principal">';
//	echo 'Estamos en la sección generar factura';
	$error = 'no'; $num_cols = 0; $mensaje = '';
//	echo $reporte;
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
		$mensaje .= 'No es válido el número de Orden de Trabajo o Presupuesto Previo indicado.<br>';
	}
	$preg0 .= "sub_estatus < '190'";
//	$preg0 .= " AND (fact_id IS NULL OR fact_id < '1')";
	$preg0 .= " GROUP BY sub_reporte";
	$mat0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!".$preg0);
	$num_rep = mysql_num_rows($mat0);

	$preg1 = "SELECT * FROM " . $dbpfx . "facturas WHERE fact_serie = '" . $agencia_serie . "' ORDER BY fact_num DESC LIMIT 1";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección!");
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
     		$mensaje .= 'Folios agotados, no se pueden emitir más facturas de la serie ' . $agencia_serie . '<br>';
     	}
	} else {
		$fact_num = $agencia_folio_inicial;
	}

	if ($num_rep > 0 && $error ==='no') {
		$mensaje = '';
		unset($_SESSION['fact']);
		if ($num_rep > 1) {
			echo '	<form action="factura.php?accion=consultar" method="post" enctype="multipart/form-data" name="imprime">'  . "\n";
   	  	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
   	  	echo '		<tr><td style="text-align:left; vertical-align:top; font-weight:bold; width:100%;">
			Existe más de un servicio que se puede facturar, elija el siniestro adecuado o 0 (cero) para trabajo particular:
		</td></tr>' . "\n";
     		echo '		<tr><td><select name="reporte" size="1">' . "\n";
			echo '			<option value="" >Seleccione...</option>';
	     	while($rep = mysql_fetch_array($mat0)) {
	     		if($rep['sub_reporte'] == '') { $rep['sub_reporte'] = '0'; }
   	  		echo '			<option value="' . $rep['sub_reporte'] . '">';
   	  		if($rep['sub_reporte'] == '0') { echo 'Particular'; } else { echo $rep['sub_reporte']; } 
   	  		echo '</option>' . "\n";
			}
			echo '		</select></td></tr>' . "\n";
			echo '		<input type="hidden" name="orden_id" value="' . $orden_id . '" />';
			echo '		<input type="hidden" name="previa_id" value="' . $previa_id . '" />';		
			echo '		<tr><td><input type="submit" value="Enviar" /></td></tr>'."\n";
			echo '		</table></form>'."\n";
		} else {
			$rep = mysql_fetch_array($mat0);
			echo '	<form action="factura.php?accion=confirma" method="post" enctype="multipart/form-data" name="imprime">'  . "\n";
   	  	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td><span class="alerta">' . $_SESSION['fact']['mensaje'] . '<br>' . $alerta . '</span></td></tr>'."\n";
			echo '		<tr class="cabeza_tabla"><td>Facturas para '."\n";
			if($orden_id != '') {
				echo 'la Orden de Trabajo: ' . $orden_id;
			} else {
				echo 'el Presupuesto Previo: ' . $previa_id;
			}
			echo '</td></tr>'."\n";
			$hoy = date('j-m-Y');
			echo '		<tr><td>Fecha de emisión de la factura (día-mes-año): <input type="text" name="fecha" value="' . $hoy . '" size="9" /></td></tr>'."\n";
			echo '		<input type="hidden" name="orden_id" value="' . $orden_id . '" />';
			echo '		<input type="hidden" name="previa_id" value="' . $previa_id . '" />';		
			echo '		<tr><td>Se va a utilizar la Serie: <strong>' . $agencia_serie . '</strong>  y el número de Factura: <input type="text" name="fact_num" value="' . $fact_num . '" size="8" /></td></tr>'."\n";
			echo '		<tr><td>Seleccione si desea Resumen de Conceptos(Default)<input type="radio" name="desglose" value="0" checked="checked"> o Todos los items a factura desglosados<input type="radio" name="desglose" value="1"></td></tr>'."\n";
			echo '		<tr style="height:15px;"><td>&nbsp;</td></tr>'."\n";
			echo '		<tr class="cabeza_tabla"><td>Seleccione las Tareas a facturar para el ';
			if($rep['sub_reporte'] == '0') { 
				echo 'Trabajo Particular'; 
			} else {
				echo 'Siniestro ' . $rep['sub_reporte']; 
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
		echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div>'."\n";
	} else {
		echo '<a href="previas.php?accion=consultar&previa_id=' . $previa_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar al Presupuesto" title="Regresar al Presupuesto"></a></div>'."\n";
	}
}

elseif($accion==='confirma') {
	
	$funnum = 1095005;
	
	if ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1') {
		$mensaje = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta para este Rol');
	}

//	echo $orden_id . ' ' . $dato;

	$error = 'no'; $mensaje= 'No hay Tareas por facturar.';
//	$datos = explode('|', $dato);
//	$reporte = $datos[0];
//	$aseguradora = $datos[1];

/*	if($orden_id!='' && !is_null($reporte)) {
		$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_reporte = '$reporte' AND sub_estatus < '189' AND (fact_id IS NULL OR fact_id < '1') AND sub_presupuesto != '0'";
   } elseif($previa_id !=''){
   	$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE previa_id = '$previa_id' AND sub_reporte = '$reporte' AND sub_estatus < '189' AND (fact_id IS NULL OR fact_id < '1') AND sub_presupuesto != '0'";
   } else {
   	$error = 'si';
		$mensaje= 'No se encontraron registros con los datos indicados.<br>';
	}
  	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!".$pregunta);
  	$filas = mysql_num_rows($matriz);
*/		
	if($_SESSION['fact']['fecha'] != '') {
		$fecha = $_SESSION['fact']['fecha'];
		$fact_num = $_SESSION['fact']['fact_num'];
	} elseif($fecha=='' || !isset($fecha)) { 
		$error = 'si';
		$mensaje .= 'Por favor corrija la fecha de la factura.<br>';
	} else {
		$_SESSION['fact']['fecha'] = $fecha;
		$_SESSION['fact']['fact_num'] = $fact_num;
	}
	
	if($Recalcula == 'Recalcula' && $cuenp != '' && !is_numeric($cuenp)) {
		$error = 'si';
		$mensaje .= 'La Cuenta de Pago deben ser los últimos 4 numeros o dejar vacio.<br>';
		$Recalcula = '';
	}
	
//	if($filas > 0) {
		$total_ref = 0; $total_con = 0; $total_mo = 0; $dedu = 0;
		foreach($tarea as $v) {
			$preg0 = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$v'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!" . $preg0);
			$sub = mysql_fetch_array($matr0);
			$total_ref = $total_ref + $sub['sub_partes'];
			$total_con = $total_con + $sub['sub_consumibles'];
			$total_mo = $total_mo + $sub['sub_mo'];
			if($sub['sub_refacciones_recibidas']=='1' && $factsinpend == '1') { 
				$_SESSION['fact']['mensaje'] = 'No se puede facturar, aún hay refacciones por recibir.';
				redirigir('factura.php?accion=consultar&orden_id=' . $orden_id); 
			}
			if($sub['sub_estatus'] < '112' && $factsinpend == '1' && $orden_id != '') {
				$_SESSION['fact']['mensaje'] = 'No se puede facturar, aún hay tareas por terminar.';
				redirigir('factura.php?accion=consultar&orden_id=' . $orden_id); 
			}
			if($sub['sub_poliza'] != '') { $poliza = $sub['sub_poliza']; }
			if($sub['sub_deducible'] > $dedu) { $dedu = round($sub['sub_deducible'], 2); }
		}
		$dedu = number_format($dedu, 2, '.', '');
		$subtotal = $total_ref + $total_con + $total_mo;
	
		if($subtotal <= 0) { 
			$error = 'si';
			$mensaje .= 'No se puede facturar, el importe no puede ser menor o igual a 0 cero.<br>';
		}
//	}
//	echo $filas . ' ' . $error;
   if($error == 'no') {
   	if($orden_id != '') {
	  		$pregunta4 = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_modelo, v.vehiculo_color, v.vehiculo_placas, v.vehiculo_serie FROM " . $dbpfx . "ordenes o, " . $dbpfx . "vehiculos v WHERE o.orden_id = '$orden_id' AND o.orden_vehiculo_id = v.vehiculo_id";
	  	} else {
	  		$pregunta4 = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_modelo, v.vehiculo_color, v.vehiculo_placas, v.vehiculo_serie FROM " . $dbpfx . "previas p, " . $dbpfx . "vehiculos v WHERE p.previa_id = '$previa_id' AND p.previa_vehiculo_id = v.vehiculo_id";
	  	}
  		$matriz4 = mysql_query($pregunta4) or die("ERROR: Fallo selección datos vehículo!");
	  	$datosv  = mysql_fetch_array($matriz4);
//	  	unset($datosv);
		if($aseguradora == 0) {
			if($orden_id != '') {
				$pregunta2 = "SELECT e.empresa_razon_social, e.empresa_rfc, e.empresa_calle, e.empresa_ext, e.empresa_int, e.empresa_colonia, e.empresa_cp, e.empresa_municipio, e.empresa_estado, e.empresa_pais, e.empresa_descuento FROM " . $dbpfx . "ordenes o, " . $dbpfx . "empresas e, " . $dbpfx . "clientes c  WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id AND c.cliente_empresa_id = e.empresa_id";
			} else {
				$pregunta2 = "SELECT e.empresa_razon_social, e.empresa_rfc, e.empresa_calle, e.empresa_ext, e.empresa_int, e.empresa_colonia, e.empresa_cp, e.empresa_municipio, e.empresa_estado, e.empresa_pais, e.empresa_descuento FROM " . $dbpfx . "previas o, " . $dbpfx . "empresas e, " . $dbpfx . "clientes c  WHERE o.previa_id = '$previa_id' AND o.previa_cliente_id = c.cliente_id AND c.cliente_empresa_id = e.empresa_id";
			}
   		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!".$pregunta2);
   		$cli = mysql_fetch_array($matriz2);
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
		} else {
			$pregunta2 = "SELECT * FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $aseguradora . "'";
   		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!".$pregunta2);
   		$ase = mysql_fetch_array($matriz2);
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
   	
   	include('parciales/encabezado.php'); 
		echo '	<div id="body">' . "\n";
			include('parciales/menu_inicio.php');
		echo '	<div id="principal">' . "\n";
		echo '		<div >'."\n";
		if($Recalcula == 'Recalcula') {
			echo '		<form action="factura.php?accion=imprime" method="post" enctype="multipart/form-data" name="confirma" target="_blank">'."\n";
		} else {
			echo '		<form action="factura.php?accion=confirma" method="post" enctype="multipart/form-data" name="confirma" >'."\n";
			unset($_SESSION['fact']['facturada']);
		}
		echo '		<table cellpadding="0" cellspacing="0" border="0" width="840px" class="izquierda">
			<tr>
				<td style="width:210px;"><img src="imagenes/logo-agencia.png" alt="' . $agencia_razon_social . '"></td>
				<td><strong>' . $agencia_razon_social . '</strong><br>
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
			<tr><td>Cliente: </td><td>' . $cliente['nombre'] . '</td></tr>
			<tr><td>Dirección: </td><td>' . $cliente['calle'] . ' #' . $cliente['numext'] . ' Int.' . $cliente['numint'] . '</td></tr>
			<tr><td>Colonia y Municipio: </td><td>' . $cliente['colonia'] . ', ' . $cliente['municipio'] . '</td></tr>
			<tr><td>CP y Entidad: </td><td>' . $cliente['cp'] . '. ' . $cliente['estado'] . ', ' . $cliente['pais'] . '</td></tr>
			<tr><td>RFC: </td><td>' . $cliente['rfc'] . '</td></tr>'."\n";
		
		if($aseguradora > 0 && $ase['aseguradora_addenda'] != '' ) {
			if(file_exists('particular/textos/' . $ase['aseguradora_addenda'])) {
				include_once('particular/textos/' .$ase['aseguradora_addenda']);
			}			
		}

		echo '			<tr><td>Método de Pago: </td><td><input type="text" name="metop" value="' . $metop . '" /></td></tr>
			<tr><td>Cuenta: </td><td><input type="text" name="cuenp" value="' . $cuenp . '" size="10"/></td></tr>
			<tr><td>Condiciones de Pago: </td><td><input type="text" name="condp" value="' . $condp . '" /></td></tr>'."\n";

		echo '			<tr><td></td><td></td></tr>
			<tr><td colspan="2"><hr></td></tr>
			<tr><td colspan="2">Conceptos a facturar</td></tr>
		</table>'."\n";
		echo '		<table cellpadding="3" cellspacing="0" border="1" width="840px" class="izquierda">'."\n";
		echo '			<tr><td>Cantidad</td><td>Descripción</td><td>Unidad</td><td>Precio Unitario</td><td>Descuento</td><td>Subtotal</td></tr>'."\n";

//		mysql_data_seek($matriz,0);
		$j=0;$dtopartes=0;$dtocons=0;$dtomo=0;
		if($desglose == '1') {
			foreach($tarea as $v) {
				$preg4 = "SELECT op_id, op_nombre, op_codigo, op_cantidad, op_precio, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $v . "' AND op_pres IS NULL AND op_cantidad > '0' AND op_precio > '0' ORDER BY op_tangible,op_nombre";
				$matr4 = mysql_query($preg4) or die("ERROR: Fallo seleccion de items!");
				while ($op = mysql_fetch_array($matr4)) {
					echo '			<tr>';
					echo '<td style="text-align:center;"><input type="text" name="cantext[' . $j . ']" size="4" value="';
					if($cantext[$j] != '') { 
						$cnt = $cantext[$j];
					} else {
						$cnt = $op['op_cantidad'];
					}
					echo $cnt . '" size="2" /></td>';
					echo '<td><input type="text" name="descext[' . $j . ']" value="';
					if($descext[$j] != '') { 
						echo $descext[$j]; 
					} else { 
						echo $op['op_nombre']; 
						if($ajustacodigo == 1) { 
							echo ' ' . $op['op_codigo']; 
						}
					}
					echo '" /></td>';
					echo '<td><input type="text" name="uniext[' . $j . ']" value="';
					if($uniext[$j] != '') {
						echo $uniext[$j];
					} else {
						if($op['op_tangible'] == '0') { echo 'Servicio'; }
						elseif($op['op_tangible'] == '1') { echo 'Pieza'; }
						else { echo 'No Aplica'; }						
					}
					echo '" size="10" /></td>';
					if($precext[$j] != '') { $fv = number_format($precext[$j], 2, '.', ''); }
					else { $fv = $op['op_precio'];}
					echo '<td><input style="text-align:right;" type="text" name="precext[' . $j . ']" value="' . $fv . '" size="8" /></td>';
					if($dtoext[$j] != '') { $dto = $dtoext[$j]; } else { $dto = $cliente['descuento']; }
					echo '<td><input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="' . $dto . '" size="2" /></td>';
					$subtotal = round(($fv * $cnt), 2);
					$subtotal = $subtotal - round(($subtotal * ($dto / 100)), 2);
					echo '<td style="text-align:right;">$' . number_format($subtotal, 2, '.', '') . '</td>';
					echo '</tr>'."\n";
					$gdto = $gdto + ($fv - $subtotal);
					$gsubt = $gsubt + $subtotal;
					$j++;
					$dto = 0;
				}
			}
		} else {
			foreach($tarea as $v) {
				$preg0 = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$v'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!" . $preg0);
				$sub = mysql_fetch_array($matr0);
//				print_r($sub); echo '<br><br>';
//				$area_tot = 0;
				if($sub['sub_partes'] != 0 ) {
					$partes = round(($partes + $sub['sub_partes']), 2);
				}
				if($sub['sub_consumibles'] != 0 ) {
					$cons = round(($cons + $sub['sub_consumibles']), 2);
				}
				if($sub['sub_mo'] != 0 ) {
//					if($area_tot > 0) { $frase .= ' y '; }
					$motot = round(($motot + $sub['sub_mo']), 2);
					$mo[$sub['sub_area']] = round(($mo[$sub['sub_area']] + $sub['sub_mo']), 2);
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
			if($factcondensada == '1') {
				echo '			<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" size="4" value="1" size="4" />1</td>';
				echo '<td><input type="text" name="descext[' . $j . ']" value="';
				if($descext[$j] != '') { 
					echo $descext[$j]; 
				} else { 
					echo 'Mano de Obra de Hojalateria y Pintura';
				}
				echo '" /></td>';
				echo '<td><input type="text" name="uniext[' . $j . ']" value="';
				if($uniext[$j] != '') {
					echo $uniext[$j];
				} else { 
					echo 'Servicio';
				}
				echo '" size="10" /></td>';
				$mocons = $motot + $cons; // el total de mano de obra + el total de consumibles 
				$cons = 0; // se vacia la variable de consumibles para no repetir montos
				if($precext[$j] != '') { $mocons = number_format($precext[$j], 2, '.', ''); }
				echo '<td><input style="text-align:right;" type="text" name="precext[' . $j . ']" value="' . $mocons . '" size="8" /></td>';
				if($dtoext[$j] != '') { $dtomo = $dtoext[$j]; } 
				echo '<td><input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="' . $dtomo . '" size="2" /></td>';
				$subtotal = $mocons - round(($mocons * ($dtomo / 100)), 2);
				echo '<td style="text-align:right;">$' . number_format($subtotal, 2, '.', '') . '</td>';
				echo '</tr>'."\n";
				$gdto = $gdto + ($mocons - $subtotal);
				$gsubt = $gsubt + $subtotal;
				$j++;
			} else {
				foreach($mo as $fk => $fv) {
					if($fv > 0) {
//						echo '			<tr><td><input type="checkbox" name="omitir[' . $j . ']" value="1"></td>';
						echo '			<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" size="4" value="1" size="4" />1</td>';
						echo '<td><input type="text" name="descext[' . $j . ']" value="';
						if($descext[$j] != '') { 
							echo $descext[$j]; 
						} else { 
							echo 'Mano de Obra de ' . constant('NOMBRE_AREA_' . $fk);
						}
						echo '" /></td>';
						echo '<td><input type="text" name="uniext[' . $j . ']" value="';
						if($uniext[$j] != '') {
							echo $uniext[$j];
						} else { 
							echo 'Servicio';
						}
						echo '" size="10" /></td>';
						if($precext[$j] != '') {	$fv = number_format($precext[$j], 2, '.', ''); }
						echo '<td><input style="text-align:right;" type="text" name="precext[' . $j . ']" value="' . $fv . '" size="8" /></td>';
						if($dtoext[$j] != '') { $dtomo = $dtoext[$j]; } 
						echo '<td><input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="' . $dtomo . '" size="2" /></td>';
						$subtotal = $fv - round(($fv * ($dtomo / 100)), 2);
						echo '<td style="text-align:right;">$' . number_format($subtotal, 2, '.', '') . '</td>';
						echo '</tr>'."\n";
						$gdto = $gdto + ($fv - $subtotal);
						$gsubt = $gsubt + $subtotal;
						$j++;
					}
				}
			}
			if($partes > 0) {
//				echo '			<tr><td><input type="checkbox" name="omitir[' . $j . ']" value="1"></td>';
				echo '			<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" size="4" value="1" size="4" />1</td>';
				echo '<td><input type="text" name="descext[' . $j . ']" value="';
				if($descext[$j] != '') {
					echo $descext[$j]; 
				} else { 
					echo 'Lote de Refacciones';
				}
				echo '" /></td>';
				echo '<td><input type="text" name="uniext[' . $j . ']" value="';
				if($uniext[$j] != '') {
					echo $uniext[$j];
				} else { 
					echo 'Piezas';
				}
				echo '" size="10" /></td>';
				if($precext[$j] != '') { $partes = number_format($precext[$j], 2, '.', ''); }
				echo '<td><input style="text-align:right;" type="text" name="precext[' . $j . ']" value="' . $partes . '" size="8" /></td>';
				if($dtoext[$j] != '') { $dtopartes = $dtoext[$j]; } 
				echo '<td><input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="' . $dtopartes . '" size="2" /></td>';
				$subtotal = $partes - round(($partes * ($dtopartes / 100)), 2);
				echo '<td style="text-align:right;">$' . number_format($subtotal, 2, '.', '') . '</td>';
				echo '</tr>'."\n";
				$gdto = $gdto + ($partes - $subtotal);
				$gsubt = $gsubt + $subtotal;
				$j++;
			}
			if($cons > 0) {
//				echo '			<tr><td><input type="checkbox" name="omitir[' . $j . ']" value="1"></td>';
				echo '			<tr><td style="text-align:center;"><input type="hidden" name="cantext[' . $j . ']" size="4" value="1" size="4" />1</td>';
				echo '<td><input type="text" name="descext[' . $j . ']" value="';
				if($descext[$j] != '') { 
					echo $descext[$j]; 
				} else { 
					echo 'Lote de Materiales';
				}
				echo '" /></td>';
				echo '<td><input type="text" name="uniext[' . $j . ']" value="';
				if($uniext[$j] != '') {
					echo $uniext[$j];
				} else { 
					echo 'No Aplica';
				}
				echo '" size="10" /></td>';
				if($precext[$j] != '') {	$cons = number_format($precext[$j], 2, '.', ''); }
				echo '<td><input style="text-align:right;" type="text" name="precext[' . $j . ']" value="' . $cons . '" size="8" /></td>';
				if($dtoext[$j] != '') { $dtocons = $dtoext[$j]; } 
				echo '<td><input style="text-align:right;" type="text" name="dtoext[' . $j . ']" value="' . $dtocons . '" size="2" /></td>';
				$subtotal = $cons - round(($cons * ($dtocons / 100)), 2);
				echo '<td style="text-align:right;">$' . number_format($subtotal, 2, '.', '') . '</td>';
				echo '</tr>'."\n";
				$gdto = $gdto + ($cons - $subtotal);
				$gsubt = $gsubt + $subtotal;
				$j++;
			}
		}
			
			for($i=$j;($j+15) > $i; $i++) {
				echo '			<tr>';
				echo '<td><input type="text" name="cantext[' . $i . ']" size="4" value="' . $cantext[$i] . '" /></td>
				<td><input type="text" name="descext[' . $i . ']" size="20" value="' . $descext[$i] . '" /></td>
				<td><input type="text" name="uniext[' . $i . ']" size="4" value="' . $uniext[$i] . '" /></td>
				<td><input style="text-align:right;" type="text" name="precext[' . $i . ']" value="' . $precext[$i] . '" size="8" /></td>
				<td><input style="text-align:right;" type="text" name="dtoext[' . $i . ']" value="' . $dtoext[$i] . '" size="2" /></td>';
				$subtotal = $cantext[$i] * ($precext[$i] - round(($precext[$i] * ($dtoext[$i] / 100)), 2));
				echo '<td style="text-align:right;">$' . number_format($subtotal, 2) . '</td>';
				echo '</tr>'."\n";
				$gdto = $gdto + (($cantext[$i] * $precext[$i]) - $subtotal);
				$gsubt = $gsubt + $subtotal;
			}
		echo '			<tr><td colspan="5" style="text-align:right;">Subtotal:</td>
		<td style="text-align:right;">$' . number_format($gsubt, 2, '.', '') . '</td></tr>'."\n";
		$iva = round(($gsubt * 0.16), 2); 
		echo '			<tr><td colspan="5" style="text-align:right;">IVA al 16%:</td>
		<td style="text-align:right;">$' . number_format($iva, 2, '.', '') . '</td></tr>'."\n";
		$suma = $gsubt + $iva;
		echo '			<tr><td colspan="5" style="text-align:right;">Total:</td>
		<td style="text-align:right;">$' . number_format($suma, 2, '.', '') . '</td></tr></table>'."\n";

		echo '		<table cellpadding="0" cellspacing="0" border="0" width="840px" class="izquierda">'."\n";

/*		if($cliente['descuento'] > 0) {
			echo '			<tr><td style="width:210px;">Descuento General Aplicable antes de IVA</td><td><input style="text-align:right;" type="text" name="perdesc" value="' . $cliente['descuento'] . '" size="3" />%</td></tr>'."\n";
		}
*/		echo '			<tr><td>Motivo del Descuento (si hay descuento)</td><td><input type="text" name="motivo" size="20" value="'.$motivo.'" /></td></tr>'."\n";
		if($aseguradora > 0) {
			echo '			<tr><td>CLIENTE</td><td>' . $asegurado['cliente_nombre'] . ' ' . $asegurado['cliente_apellidos'] . '</td></tr>'."\n";
			if($siniestro == '') { $siniestro = $reporte; } 
			echo '			<tr><td>SINIESTRO</td><td>' . $reporte . '</td></tr>
			<tr><td>POLIZA</td><td>' . $poliza . '</td></tr>
			<tr><td>DEDUCIBLE</td><td>' . $dedu . '</td></tr>'."\n";
		}

		echo '			<tr><td>MARCA</td><td><input type="text" name="marca" value="' . $datosv['vehiculo_marca'] . '" /></td></tr>
			<tr><td>AUTO</td><td><input type="text" name="tipo" value="' . $datosv['vehiculo_tipo'] . '" /></td></tr>
			<tr><td>AÑO</td><td><input type="text" name="modelo" value="' . $datosv['vehiculo_modelo'] . '" /></td></tr>
			<tr><td>COLOR</td><td><input type="text" name="color" value="' . $datosv['vehiculo_color'] . '" /></td></tr>
			<tr><td>PLACAS</td><td><input type="text" name="placas" value="' . $datosv['vehiculo_placas'] . '" /></td></tr>
			<tr><td>VIN</td><td><input type="text" name="vin" value="' . $datosv['vehiculo_serie'] . '" /></td></tr>'."\n";
		echo '			<tr><td>Observaciones adicionales:</td><td><input type="text" name="obsad" size="60" maxlength="80" value="' . $obsad . '" /></td></tr>'."\n";

// --------------- Inclusión de addenda en caso de que exista  -------------------------

		if($aseguradora > 0 && $ase['aseguradora_addenda'] != '' ) {
			include('parciales/' . $ase['aseguradora_addenda']);
			echo '<input type="hidden" name="addenda" value="' . $ase['aseguradora_addenda'] . '" />';
		}

		echo '			<tr><td colspan="2"><hr></td></tr>'."\n";
		if($Recalcula == 'Recalcula') {
			echo '			<tr><td colspan="2"><button name="Confirmar" value="Confirmar" type="submit">Confirmar Datos</button></td></tr>'."\n";
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
			<input type="hidden" name="fecha" value="' . $fecha . '" />
			<input type="hidden" name="desglose" value="' . $desglose . '" />
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
		if($orden_id != '') {
			redirigir('factura.php?accion=consultar&orden_id=' . $orden_id);
		} else {
			redirigir('factura.php?accion=consultar&previa_id=' . $previa_id);
		}
	}
}

elseif($accion==='imprime') {
	
	$funnum = 1095010;
	
	if ($_SESSION['rol02']=='1' || $_SESSION['rol03']=='1') {
		$mensaje = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

/*
   	include('parciales/encabezado.php'); 
		echo '	<div id="body">' . "\n";
			include('parciales/menu_inicio.php');
		echo '	<div id="principal">' . "\n";
		echo '		<div >'."\n";
		echo '<div style="text-align:center;"><h2>Por favor espere a que se genere, se valide y se timbre el la factura electrónica.<br>Por favor no recargue la página o presione Enter ya que se duplicará la generación de la factura</h2><img src="imagenes/espera.gif" alt="Espera..."></div>'."\n";

		echo '		</div>
	</div>
<p class="footer">Derechos Reservados 2009 - 2012</p>'."\n";
*/

	$error = 'no'; $mensaje= '';

	$subtotal=0; $descuento = 0;
	foreach ($cantext as $k => $v) {
		$precext[$k] = limpiarNumero($precext[$k]);
		$cantext[$k] = limpiarNumero($cantext[$k]);
		$dtoext[$k] = limpiarNumero($dtoext[$k]);
		$descuento = $descuento + round(($precext[$k] * ($dtoext[$k] / 100)), 2);
		$impext[$k] = round(($cantext[$k] * $precext[$k]), 2);
		$impext[$k] = number_format($impext[$k], 2, '.', '');
		$subtotal = $subtotal + $impext[$k];
	}
	$motivo = limpiar_cadena($motivo);

// ----------------- Si hay descuento debe haber motivo ------------------------
	if($descuento > 0 && $motivo == '') { $error = 'si'; $mensaje .= 'Por favor indique el motivo del descuento.<br>'; }
	if($_SESSION['fact']['facturada'] == 1)  { $error = 'si'; $mensaje .= 'La factura ya fue emitida'; }
	$descuento = number_format($descuento, 2, '.', '');
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
   			'rfc' =>  strtoupper($cli['empresa_rfc'])
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
  		$subtotal = round($subtotal, 2);
  		$subtotal = number_format($subtotal, 2, '.', '');
  		$suma = $subtotal - $descuento;
  		$suma = number_format($suma, 2, '.', '');
  		$iva = round(($suma * 0.16), 2);
  		$iva = number_format($iva, 2, '.', '');
  		$total = $suma + $iva;
  		$total = number_format($total, 2, '.', '');
  		$letra = strtoupper(letras2($total));

##########################################################
# PASO1. Crea un CFDi de factura con un par de conceptos
#
# Regresa un texto en la variable $cfdi
##########################################################

date_default_timezone_set('America/Mexico_City'); 
// echo "PASO 1.- Crea un CFDi de factura con un par de conceptos\n";
# Partimos de un CFDi a medias, conservando declaracion de esquemas
$cfdi = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd" version="3.2" fecha="" formaDePago="EN UNA SOLA EXHIBICION" subTotal="" total="" LugarExpedicion="" tipoDeComprobante="ingreso" metodoDePago="" serie="" folio="" noCertificado="" sello="">
  <cfdi:Emisor nombre="" rfc="">
    <cfdi:DomicilioFiscal calle="" noExterior="" colonia="" referencia="" municipio="" estado="" pais="" codigoPostal=""/>
    <cfdi:RegimenFiscal Regimen=""/>
  </cfdi:Emisor>
  <cfdi:Receptor nombre="" rfc="">
    <cfdi:Domicilio codigoPostal="" pais="" estado="" municipio="" noExterior="" calle="" colonia=""/>
  </cfdi:Receptor>
  <cfdi:Conceptos>
  </cfdi:Conceptos>
  <cfdi:Impuestos totalImpuestosTrasladados="">
    <cfdi:Traslados>
      <cfdi:Traslado importe="" tasa="16.00" impuesto="IVA"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
  <cfdi:Complemento> 
  </cfdi:Complemento>
';
if(isset($addenda) && $addenda != '') {
	$cfdi .= '	<cfdi:Addenda>
	</cfdi:Addenda>
';
}
$cfdi .= '</cfdi:Comprobante>
';

# El emisor no cambia. Si cambiara el codigo sería semejante al de receptor
$emisor = array ("nombre" => $agencia_razon_social,
		"rfc" => $agencia_rfc,
		"Regimen" => $agencia_regimen,
		"Domicilio" => array (
			"codigoPostal" => $agencia_cp,
			"pais" => "MEXICO",
			"estado" => $agencia_estado,
			"municipio" => $agencia_municipio,
			"colonia" => $agencia_colonia,
			"noExterior" => $agencia_numext,
			"referencia" => $agencia_referencia,
			"calle" => $agencia_calle
			)
		);

$receptor = array ("nombre" => $cliente['nombre'],
		"rfc" => $cliente['rfc'],
		"Domicilio" => array (
			"codigoPostal" => $cliente['cp'],
			"pais" => "MEXICO",
			"estado" => $cliente['estado'],
			"municipio" => $cliente['municipio'],
			"colonia" => $cliente['colonia'],
			"noExterior" => $cliente['numext'],
			"calle" => $cliente['calle']
			)
		);
		
if ($cliente['numint'] != '') {
	$receptor['Domicilio']['noInterior'] = $cliente['numint']; 
	}

# Convierte a objeto DOM $xml
$xml = new DOMDocument();
$xml->loadXML($cfdi);

# Modifica codigos semifijos
$xmlreceptor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Receptor')->item(0);
$xmlreceptor->setAttribute('nombre', $receptor['nombre']);
$xmlreceptor->setAttribute('rfc', $receptor['rfc']);
$xmlreceptordomicilio = $xmlreceptor->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Domicilio')->item(0);
foreach($receptor["Domicilio"] as $key => $value) {
	$xmlreceptordomicilio->setAttribute($key, $value);
}
unset($xmlreceptor);
unset($xmlreceptordomicilio);

$xmlemisor = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Emisor')->item(0);
$xmlemisor->setAttribute('nombre', $emisor['nombre']);
$xmlemisor->setAttribute('rfc', $emisor['rfc']);
$xmlemisorregimen = $xmlemisor->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'RegimenFiscal')->item(0);
$xmlemisorregimen->setAttribute('Regimen', $emisor['Regimen']);
$xmlemisordomicilio = $xmlemisor->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'DomicilioFiscal')->item(0);
foreach($emisor["Domicilio"] as $key => $value) {
	$xmlemisordomicilio->setAttribute($key, $value);
}
unset($xmlemisor);
unset($xmlemisordomicilio);

# Agrega conceptos
if($fact_resumen == 1) {
	$xmlconceptos = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Conceptos')->item(0);
	foreach ($cantext as $k => $v) {
		if($v > 0) {
			$xmlconcepto = $xml->createElementNS('http://www.sat.gob.mx/cfd/3', 'Concepto');
			$xmlconcepto->setAttribute('cantidad', $v);
			$xmlconcepto->setAttribute('unidad', $uniext[$k]);
			$xmlconcepto->setAttribute('descripcion', $descext[$k]);
			$xmlconcepto->setAttribute('valorUnitario', $precext[$k]);
			$xmlconcepto->setAttribute('importe', $impext[$k]);
			$xmlconceptos->appendChild($xmlconcepto);
			unset($xmlconcepto);
			unset($importe);
		}
	}
	unset($xmlconceptos);
}

$fecha_emision = date('Y-m-d');
$fecha_emision .= 'T';
$fecha_emision .= date('H:i:s');

# Calcula totales
$c = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
$c->setAttribute('serie', $agencia_serie);
$c->setAttribute('folio', $fact_num);
$c->setAttribute('subTotal', $subtotal);
if($descuento > '0') {
	$c->setAttribute('descuento', $descuento);
	$c->setAttribute('motivoDescuento', $motivo);
}
if($metop != '') {
	$c->setAttribute('metodoDePago', $metop);
} else {
	$c->setAttribute('metodoDePago', 'NO IDENTIFICADO');
}
if($condp != '') {
	$c->setAttribute('condicionesDePago', $condp);
}
if($cuenp != '') {
	$c->setAttribute('NumCtaPago', $cuenp);
}
$c->setAttribute('total', $total);
$c->setAttribute('LugarExpedicion', $agencia_lugar_emision);
$c->setAttribute('fecha', $fecha_emision);
$c->getElementsByTagName('Impuestos')->item(0)->setAttribute('totalImpuestosTrasladados', $iva);
$c->getElementsByTagName('Impuestos')->item(0)->getElementsByTagName('Traslados')->item(0)->getElementsByTagName('Traslado')->item(0)->setAttribute('importe', $iva);
unset($c);

# Reconvierte a texto
$cfdi = $xml->saveXML();
unset($xml);
// echo htmlspecialchars($cfdi);

###############################################################
# PASO2. Firma el comprobante que esta en $cfdi en modo texto
#
# Regresa el comprobante firmado en la misma variable $cfdi
###############################################################
// echo "\n\nPASO2. Firma el comprobante que esta en \$cfdi en modo texto\n";
# Convierte a modelo DOM
$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido Paso 2");

# Extrae cadena original
$xslt = new XSLTProcessor();
$XSL = new DOMDocument();
$XSL->load( 'cadenaoriginal_3_2.xslt', LIBXML_NOCDATA);
error_reporting(0); # Se deshabilitan los errores pues el xssl de la cadena esta en version 2 y eso genera algunos warnings
$xslt->importStylesheet( $XSL );
error_reporting(E_ALL); # Se habilitan de nuevo los errores (se asume que originalmente estaban habilitados)
$c = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
$cadena = $xslt->transformToXML( $c );
unset($xslt, $XSL);
// echo "Cadena original = [$cadena]\n";

# A continuacion se incluye el certificado que se usará para firma.

include('../certificado.php');

// echo 'Certificado';

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

openssl_sign($cadena, $crypttext, $key);
$sello = base64_encode($crypttext);
// echo "sello = [$sello]\n";

# Incorpora los tres elementos al cfdi
$c->setAttribute('certificado', $certificado);
$c->setAttribute('sello', $sello);
$c->setAttribute('noCertificado', $noCertificado);

# regresa el resultado
$cfdi = $xml->saveXML();
unset($c, $xml, $cert, $certificado, $cadena, $crypttext, $key);
// echo htmlspecialchars($cfdi);

file_put_contents(DIR_DOCS.'temporal.xml', $cfdi);


###############################################################
# PASO3. Verifica el CFDI en la variable $cfdi
#
# Se interrumpe si hay error
###############################################################
// echo "\n\nPASO3. Verifica el CFDI en la variable \$cfdi\n";
$cadena="";
# Valida UTF8
mb_check_encoding($cfdi, "UTF-8") or die("El string no esta en UTF8\n");

# Convierte a modelo DOM
$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido Paso 3");

// echo htmlspecialchars($cfdi);



# Valida contra esquema
$xml->schemaValidate('cfdv32.xsd') or die("\n\nNo es un CFDi valido");

# Verifica la firma
$Comprobante = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
# Extrae cadena original
$xslt = new XSLTProcessor();
$XSL = new DOMDocument();
$XSL->load( 'cadenaoriginal_3_2.xslt', LIBXML_NOCDATA);
error_reporting(0);
$xslt->importStylesheet( $XSL );
error_reporting(E_ALL);
$cadena = $xslt->transformToXML( $Comprobante );
unset($xslt, $XSL);

# Extrae el certificado y lo pone en formato que las funciones puedan leer
$cert2 = $Comprobante->getAttribute("certificado");
$cert  = "-----BEGIN CERTIFICATE-----\n";
$cert .= chunk_split($cert2, 64, "\n");
$cert .= "-----END CERTIFICATE-----\n";

if (!($pkey = openssl_pkey_get_public($cert))) {
	echo "\n\n\nNo es posible extraer llave publica\n";
	die;
}

# Extrae sello
$crypttext = base64_decode($Comprobante->getAttribute("sello"));

// echo "Sello decodificado:<br><br>".$$crypttext;
if (openssl_verify($cadena, $crypttext, $pkey)) {
//	echo  "El firmado es correcto\n";
} else {
	die("\nError en el firmado!!!\n");
}	
unset($xml, $Comprobante, $cert2, $cert, $pkey, $crypttext, $cadena);


###############################################################
# PASO4. Timbra el CFDI en la variable $cfdi con TimbreFiscal
#
#        4.1) Ensobreta
#        4.2) Envía a TimbreFiscal
#        4.3) Recibe un timbre (o procesa un error)
# Regresa el $cfdi intacto y $timbre
###############################################################

// echo 'PASO 4  probando timbrado...';

require_once('nusoap/nusoap.php');


$fname = DIR_DOCS.'temporal.xml';
if(!file_exists($fname)){
 die(PHP_EOL . "File not found" . PHP_EOL . PHP_EOL);
}
 
$handle = fopen($fname, "r");
$sData = '';
$usuario = "testing@solucionfactible.com";
$password = "timbrado.SF.16672";
 
while(!feof($handle))
    $sData .= fread($handle, filesize($fname));
fclose($handle);
$b64 = base64_encode($sData);
 
$response = '';
/*
Porfavor note: 
    Este ejemplo está basado en el WSDL De timbrado
    cada WSDL tiene su propia estructura y deberá modificarse la petición 
    acorde al webservice que se esté conectando.*/
try {
        $client = new SoapClient("https://testing.solucionfactible.com/ws/services/Timbrado?wsdl");
        $params = array('usuario' => $usuario, 'password' => $password, 'cfdiBase64'=>$b64, 'zip'=>False);
        $response = $client->__soapCall('timbrarBase64', array('parameters' => $params));
} catch (SoapFault $fault) { 
        echo "SOAPFault: ".$fault->faultcode."-".$fault->faultstring."\n";
}
 
$ret = $response->return;
 
print_r("Estatus request: " . $ret->status . PHP_EOL);
print_r("Mensjae request: " . $ret->mensaje . PHP_EOL);
 
if($ret->status == 200) {
        print_r("Contenido resultados: " . PHP_EOL);
        print_r($ret->resultados);
}


/*
//Se asigna el servicio
$servicio=$pac_url; //url del servicio
//parametros de la llamada
$parametros=array();

//Se preparan los parametros con los valores adecuados
$parametros['usuario']=$pac_usuario; //String
$parametros['contrasena']=$pac_clave;//String
$parametros['cfdi']=$cfdi;//String

// echo "revisando parametros<br>";
// print_r($parametros);


//Se crea el cliente del servicio
$client = new SoapClient( $servicio, $parametros);

//Se hace el metodo que vamos a probar
$result = $client->timbrar($parametros);

// print_r($parametros);

//Para observar el Dump de lo que regresa, es puramente de debug
// echo "Valor dump del servicio:";
// echo "<BR>";
// var_dump($result);
// echo "<BR>";
// echo "<BR>";

//Aislar cada valor de lo que regresa, y poder manipularlo como sea
foreach($result as $key => $value) {
	
//    echo "Código:";
//    echo "<BR>";
    $res_codigo = $value->codigo; 
    $res_uuid = $value->uuid;
//    var_dump($value->codigo);
//	echo $res_codigo;
//    echo "<BR>";
//    echo "<BR>";
//    echo "Mensaje:";
//    echo "<BR>";
//    var_dump($value->mensaje);
//    echo "<BR>";
//    echo "<BR>";
//    echo "Timbre:";
//    echo "<BR>";
//	file_put_contents('regresa.xml', $value->timbre);
//    var_dump($value->timbre);
//	echo "UUID";
//    echo "<BR>";
//    var_dump($value->uuid);

$timbre = $value->timbre;

}

*/


// echo 'Timbre: <br>' . $timbre;


###############################################################
# PASO5. Integra el timbre recibido en $timbre en el $cfdi
#
# Regresa el $cfdi ya integrado con el timbre
###############################################################
// echo "\n\nPASO5. Integra el timbre recibido en \$timbre en el \$cfdi\n";
# Convierte a modelo DOM
$xml = new DOMDocument();
$xml->loadXML($cfdi) or die("\n\n\nXML no valido paso 5");
$xml->schemaValidate('cfdv32.xsd') or die("\n\n\nCFDi no valido");
# Valida que realmente haya regresado un timbre
$sobretimbre = new DOMDocument();
$sobretimbre->loadXML($timbre) or die("\n\n\nXML de respuesta no valido\n");
# Extrae el timbre (si existe)
$xmltimbre = new DOMDocument('1.0', 'UTF-8');
# Extrae el nodo
$paso = $sobretimbre->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);
$fecha_sat = $paso->getAttribute("FechaTimbrado");
$serie_sat = $paso->getAttribute("noCertificadoSAT");
$sello_sat = $paso->getAttribute("selloSAT");
$version_sat = $paso->getAttribute("version");
$paso = $xmltimbre->importNode($paso, true);
$xmltimbre->appendChild($paso);
unset($paso);
# Valida
$xmltimbre->schemaValidate('TimbreFiscalDigital.xsd') or die("\n\n\nError de validacion\n$return");
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
unset($timbre, $xml, $sobretimbre, $xmltimbre, $paso, $complemento, $t);

if(isset($addenda) && $addenda != '') {
	include('parciales/' . $addenda);
}

// echo htmlspecialchars($cfdi);
$nombre_cfdi = $agencia_serie . $fact_num . '-' . $res_uuid;

file_put_contents(DIR_DOCS.$nombre_cfdi.'.xml', $cfdi);

		include('parciales/phpqrcode/qrlib.php');
		$ftotal = number_format($total, 6, '.', '');
		$qrtotal = strval($ftotal);
		$qrtotal = sprintf('%017s', $qrtotal);
		$codigoqr = '?re=' . $agencia_rfc . '&rr=' . $cliente['rfc'] . '&tt=' . $qrtotal . '&id=' . $res_uuid;
		$imagenqr = DIR_DOCS . $nombre_cfdi . '.png';
		QRcode::png($codigoqr, $imagenqr, 'L', 4, 2);


	$doc_nombre = 'Factura XML ' . $agencia_serie . $fact_num;
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
   	
	$doc_nombre = 'Factura PDF ' . $agencia_serie . $fact_num;
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
   	
  	$sql_data = array('fact_serie' => $agencia_serie,
  		'fact_num' => $fact_num,
  		'orden_id' => $orden_id,
  		'previa_id' => $previa_id,
  		'reporte' => $reporte,
  		'cliente_id' => $cliente['id'], 
  		'fact_rfc' => $cliente['rfc'],
  		'fact_sub' => $suma,
  		'fact_iva' => $iva,
  		'fact_total' => $total,
  		'fact_fecha' => $fecha,
  		'usuario' => $_SESSION['usuario']);
  	ejecutar_db($dbpfx . 'facturas', $sql_data, 'insertar');
  	
  	$factcomp = $agencia_serie . $fact_num;
   	
  	$sql_data = array(
  		'orden_id' => $orden_id,
  		'previa_id' => $previa_id,
  		'reporte' => $reporte,
  		'cliente_id' => $cli['cliente_id'],
  		'aseguradora_id' => $ase['aseguradora_id'], 
  		'fact_num' => $factcomp,
  		'fact_fecha_emision' => date('Y-m-d H:i:s', time()),
  		'fact_tipo' => '1',
  		'fact_monto' => $total,
  		'usuario' => $_SESSION['usuario']);
  	ejecutar_db($dbpfx . 'facturas_por_cobrar', $sql_data, 'insertar');
  	$fact_id = mysql_insert_id();
  	
  	$sql_data = array('fact_id' => $fact_id);
	foreach($tarea as $tar) {
		$param = "sub_orden_id = '" . $tar . "'";
		ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $param);
		if($orden_id != '') {
			bitacora($orden_id, 'Tarea ' . $tar . ' facturada con la factura ' . $fact_id, $dbpfx);
		} else {
			bitacora('0', 'Tarea ' . $tar . ' facturada con la factura ' . $fact_id, $dbpfx, '', '', '', $previa_id);
		}
	}

// ------------- Redirigir a ex.php  -------------------------   	

	$_SESSION['fact']['facturada'] = 1;
   if($orden_id != '') {	
		redirigir('ex.php?axml=' . $nombre_cfdi . '.xml&orden_id=' . $orden_id . '&reporte=' . $reporte . '&obsad=' . $obsad);
	} else {
		redirigir('ex.php?axml=' . $nombre_cfdi . '.xml&previa_id=' . $previa_id . '&reporte=' . $reporte . '&obsad=' . $obsad);
	}   	
   	
	} else {
		$_SESSION['fact']['mensaje'] = $mensaje;
		if($orden_id != '') {
			redirigir('factura.php?accion=consultar&orden_id=' . $orden_id);
		} else {
			redirigir('factura.php?accion=consultar&previa_id=' . $previa_id);
		}
	}
}


?>
		</div>
	</div>
<p class="footer">Derechos Reservados 2009 - 2014</p>
</div>
</body>
</html>