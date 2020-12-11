<?php

	$mensaje = '';
	$error = 'si'; $num_cols = 0;
	include('parciales/phpqrcode/qrlib.php');
	$codigoqr = $urlpub.'/consulta.php?accion=consultar&orden_id=' . $orden_id . '&arg0=' . $cust['cliente_clave'];
	$imagenqr = 'documentos/qr-orden-' . $orden_id . '.png';
	QRcode::png($codigoqr, $imagenqr, 'L', 4, 2);
	$error = 'no';
	$preg0 = "SELECT o.*, c.* FROM " . $dbpfx . "ordenes o, " . $dbpfx . "clientes c WHERE o.orden_id = '$orden_id' AND o.orden_cliente_id = c.cliente_id";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Ordenes de Trabajo!");
	$ord = mysql_fetch_array($matr0);
	$preg1 = "SELECT sub_reporte, sub_poliza, sub_valuador, sub_controlista, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Tareas! ". $preg1);
	while ($sub = mysql_fetch_array($matr1)) {
		$sin[$sub['sub_reporte']] = 1;
		$pol[$sub['sub_poliza']] = 1;
		$valu[$sub['sub_valuador']] = 1;
		$cont[$sub['sub_controlista']] = 1;
        $clie[$sub['sub_aseguradora']] = 1;
	}

	$veh = datosVehiculo($orden_id, $dbpfx);
	$preg2 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE acceso = '0' AND (rol05 = '1' OR rol06 = '1' OR rol07 = '1')";
	$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Tareas! ". $preg2);
	while ($usu = mysql_fetch_array($matr2)) {
		$usr[$usu['usuario']] = $usu['nombre'] . ' ' . $usu['apellidos'];
	}	
//	echo $pregunta;
//	echo $orden_id;
       
		echo '		<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda">
			<tr>';
        echo '
				<td style="width:230px;"><img src="particular/logo-agencia.png" alt="' . $agencia_razon_social . '" height="80"></td>
				<td></td>
				<td></td>
				<td style="width:210px; text-align:center;"><!--<h2>' . $orden_id . '</h2>--></td>
				
			</tr>
			<tr>
				<td colspan="4" style="font-size:30px; text-align:center;"><br><STRONG>SEGUIMIENTO Y CONTROL DE REPARACIONES</STRONG></td> 
			</tr>
			<tr>
				<td colspan="4"><hr></td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:18px;"><br><STRONG>ASEGURADORA: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br></STRONG>'."\n";
			
		$a= 0;
        foreach($clie as $k => $v){
            if($a > 0) { echo ', '; }
            
            if($k == 0){
				echo 'Particular';
			}
            else{
				echo $agegu[$k]['razon'] . '<br>' . '<img src="' . $asegu[$k]['logo'] . '"  height="60">';
            }
            $a++;
        }

		echo '		
				</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>SINIESTRO(S): </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br></STRONG>'."\n";
		$j = 0;
		foreach($sin as $k => $v) {
			if($j > 0) { echo ', '; }
			echo $k;
			$j++;
		}

		echo '
				</td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:18px;"><br><STRONG>FECHA RECEPCIÓN: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $ord['orden_fecha_recepcion'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>NÚMERO POLIZA: </STRONG></td>
				<td colspan="2" style="font-size:18px;"><br>'."\n";

		$j = 0;
		foreach($pol as $k => $v) {
			if($j > 0) { echo ', '; }
			echo $k;
			$j++;
		}	

		echo '
				</td>
            </tr>
			<tr>
				<td colspan="1" style="font-size:18px;"><br><STRONG>MARCA VEHÍCULO: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $veh['marca'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>ORDEN:</STRONG></td>
				<td colspan="1" style="font-size:30px;"><br><STRONG>' . $orden_id . '</STRONG></td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:18px;"><br><STRONG>TIPO: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $veh['tipo'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>PLACAS: </STRONG></td>
				<td colspan="1" style="font-size:30px;"><br>' . $veh['placas'] . '</td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:18px;"><br><STRONG>COLOR: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $veh['color'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>KILOMETROS: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $ord['orden_odometro'] . '</td>
			<tr>
				<td colspan="1" style="font-size:18px;"><br><STRONG>MODELO: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $veh['modelo'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>NÚMERO SERIE: </STRONG></td>
				<td colspan="2" style="font-size:18px;"><br>' . $veh['serie'] . '</td>
			</tr>
		</table>
				'."\n";
			/*
			<td colspan="1" style="font-size:18px;"><br><STRONG>VENTA ADICIONAL: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>_______________________</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>FECHA DE ENTREGA:  </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>
				
			if($ord['orden_fecha_de_entrega'] != ''){
				
				echo $ord['orden_fecha_de_entrega']; 
			} else {
				echo 'N/A';
			}

		echo '
			<tr>
				<td colspan="1" style="font-size:18px;"><br><STRONG>FECHA RECEPCIÓN: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $ord['orden_fecha_recepcion'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>ORDEN:</STRONG></td>
				<td colspan="1" style="font-size:30px;"><br><STRONG>' . $orden_id . '</STRONG></td>
			</tr>
			</table>
			<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda">
			<tr>
				<td colspan="1" style="font-size:18px;"><br><STRONG>UNIDAD MARCA: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $veh['marca'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>TIPO: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $veh['tipo'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>MODELO: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $veh['modelo'] . '</td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:18px;"><br><STRONG>NÚMERO POLIZA: </STRONG></td>
				<td colspan="2" style="font-size:18px;"><br>'."\n";

		$j = 0;
		foreach($pol as $k => $v) {
			if($j > 0) { echo ', '; }
			echo $k;
			$j++;
		}	
		echo '
				</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>NÚMERO SERIE: </STRONG></td>
				<td colspan="2" style="font-size:18px;"><br>' . $veh['serie'] . '</td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:18px;"><br><STRONG>CATEGORÍA: </STRONG>' . constant('CATEGORIA_DE_REPARACION_' . $ord['orden_categoria']) . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>PLACAS: </STRONG>' . $veh['placas'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>COLOR: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $veh['color'] . '</td>
				<td colspan="2" style="font-size:18px;"><br><STRONG>KILOMETROS: </STRONG>' . $ord['orden_odometro'] . '</td>
			</tr>
			</table>'."\n";
			*/


		echo '
			<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda">
			<tr>
				<td colspan="6" style="font-size:30px; text-align:center;"><br><br><STRONG>DATOS CLIENTE-ASEGURADO-TERCERO </STRONG></td>
			</tr>
			<tr>
				<td colspan="6"><hr></td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:18px;"><br><br><STRONG>PROPIETARIO:</STRONG></td>
				<td colspan="5" style="font-size:25px;"><br><br><STRONG>' . $ord['cliente_nombre'] . ' ' . $ord['cliente_apellidos'] . '</STRONG>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="font-size:18px;"><br><STRONG>TÉLEFONO PARTICULAR: </STRONG></td>
				<td colspan="2" style="font-size:18px;"><br>' . $ord['cliente_telefono1'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>CELULAR: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>' . $ord['cliente_movil'] . '</td>
			</tr>
			<tr>
				<td colspan="2" style="font-size:18px;"><br><STRONG>CORREO ELECTRÓNICO: </STRONG></td>
				<td colspan="2" style="font-size:18px;"><br>' . $ord['cliente_email'] . '</td>
				<td colspan="1" style="font-size:18px;"><br><STRONG>HORARIO PREFERIBLE: </STRONG></td>
				<td colspan="1" style="font-size:18px;"><br>______________</td>
			</tr>
			<tr>
				<td colspan="6" style="font-size:30px; text-align:center;"><br><br><STRONG>REVISIÓN DE DOCUMENTACIÓN COMPLETA</STRONG></td>
			</tr>
			<tr>
				<td colspan="6"><hr></td>
			</tr>
			</table>
			<br>
			<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda">
			<tr>
				<td colspan="1" style="font-size:15px;">
					<b>Volante de admisión: </b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
				<td colspan="1" style="font-size:15px;">
					<b>Inventario de unidad: </b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:15px;">
					<b>INE o Identificación Oficial: </b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
				<td colspan="1" style="font-size:15px;">
					<STRONG>Hoja de calidad firmada: </STRONG>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:15px;">
					<b>
						<small>Valuación Autorizada (AXA)<br> 
						o Presupuesto firmado (Particular):</small>
					</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
				<td colspan="1" style="font-size:15px;">
					<b>Hoja de autoasignación firmada:</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
			</tr>	
			<tr>
				<td colspan="1" style="font-size:15px;">
					<b>
						<small>Voucher deducible (AXA) o/y Pago (Particular): </small>
					</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
				<td colspan="1" style="font-size:15px;">
					<b>Hoja viajera:</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:15px;">
					<b>
						Finiquito firmado:
					</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
				<td colspan="1" style="font-size:15px;">
					<b>¿Hubo venta adicional?:</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:15px;">
					<b>
						Encuesta de servicio: 
					</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
				<td colspan="1" style="font-size:15px;">
					<b>Vale de refacciones:</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
						<tr>
							<td colspan="1"><b>SI</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1">&nbsp;&nbsp;&nbsp;</td>
							<td colspan="1"><b>NO</b>&nbsp;</td>
							<td colspan="1" style="border:2px solid black; ">&nbsp;&nbsp;&nbsp;</td>
						<tr>
					</table>
				</td>
			</tr>
			</table>
			<br>
			<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda">
			<tr>
				<td colspan="1" style="font-size:15px;">
					<b>
						Fecha y Firma del cliente: 
					</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					
				</td>
				<td colspan="1" style="font-size:15px;">
					<b>Responsable expediente en la entrega:</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					
				</td>
			</tr>
			<tr>
				<td colspan="1" style="font-size:15px;">
					<br><b>_________________________________</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					
				</td>
				<td colspan="1" style="font-size:15px;">
					<br><b>_________________________________</b>
				</td>
				<td colspan="1" style="font-size:15px;">
					
				</td>
			</tr>
			</table>
			'."\n";
        
        

		echo '		<table cellpadding="0" cellspacing="2" border="0" class="izquierda" width="840">'."\n";
		echo '			<tr><td style="text-align:left;"><div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>&nbsp;<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="Imprimir Carátula" title="Imprimir Carátula"></a></div></td></tr></table>'."\n";

?>