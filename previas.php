<?php 
foreach($_POST as $k => $v){$$k=$v; } // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;}  // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/previas.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

if ($accion==='crear' || $accion==='consultar' || $accion==='presupuesto' || $accion==='cerrar' || $accion==='cancelar') { 
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">'."\n";
}

if ($accion==='crear') {

	$funnum = 1155000;
	$retorno = validaAcceso($funnum, $dbpfx);
	
//	echo 'Estamos en la sección  consulta';
	if ($retorno == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol04']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol06']=='1') {
		// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado.');
	}	
	
	$preg = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_vehiculo_id = '" . $vehiculo_id . "' AND orden_estatus < 90";
	$matr = mysql_query($preg) or die("ERROR: Fallo selección de ordenes!");
	$fila = mysql_num_rows($matr);

	if($fila > 0) {
		$ord = mysql_fetch_array($matr);
		$_SESSION['msjerror'] = $lang['Ya hay una OT activa'];
		redirigir('ordenes.php?accion=consultar&orden_id=' . $ord['orden_id']);
	}

	$preg0 = "SELECT previa_id FROM " . $dbpfx . "previas WHERE previa_vehiculo_id = '" . $vehiculo_id . "' AND previa_estatus < 90";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de previas!");
	$fila0 = mysql_num_rows($matr0);

	if($fila0 > 0) {
		$prev = mysql_fetch_array($matr0);
		$_SESSION['msjerror'] = $lang['Ya existe Presupuesto Previo'] . $vehiculo_id;
		redirigir('previas.php?accion=consultar&previa_id=' . $prev['previa_id']);
	}

	$preg1 = "SELECT c.cliente_id FROM " . $dbpfx . "clientes c, " . $dbpfx . "vehiculos v WHERE v.vehiculo_id = '" . $vehiculo_id . "' AND v.vehiculo_cliente_id = c.cliente_id";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion de Cliente!");
	$clie = mysql_fetch_array($matr1);

	$sql_array = array('previa_estatus' => '10',
		'previa_cliente_id' => $clie['cliente_id'],
		'previa_vehiculo_id' => $vehiculo_id,
		'previa_fecha_recepcion' => date('Y-m-d H:i:s'),
		'previa_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'),
		'previa_asesor_id' => $_SESSION['usuario'],
		'previa_alerta' => '0');

	$previa_id = ejecutar_db($dbpfx . 'previas', $sql_array, 'insertar');
	bitacora(0, 'Creación de nueva Previa para el vehículo ' . $vehiculo_id . ' del Cliente ' . $cliente_id, $dbpfx, '', '', '', $previa_id);
	redirigir('previas.php?accion=consultar&previa_id=' . $previa_id);
}

elseif ($accion==="consultar") {
	
	$funnum = 1155005;
	$retorno = validaAcceso($funnum, $dbpfx);
    
	if ($retorno == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol04']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol06']=='1') {
		 $mensaje = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Cotizador, ingresar Usuario y Clave correcta');
	}
//	echo 'Estamos en la sección imprimir';
	$mensaje = '';
	$error = 'no';

//	if ($num_cols > 0) {
		
	$prega = "SELECT p.previa_id, p.previa_estatus, p.previa_asesor_id, p.previa_fecha_ultimo_movimiento, p.previa_vehiculo_id, c.cliente_id, c.cliente_nombre, c.cliente_apellidos, c.cliente_telefono1, c.cliente_email FROM " . $dbpfx . "previas p, " . $dbpfx . "clientes c WHERE p.previa_cliente_id = c.cliente_id AND (p.previa_id = '$previa_id' OR p.previa_vehiculo_id = '$vehiculo_id' OR p.previa_cliente_id = '$cliente_id')";
	$matra = mysql_query($prega) or die("ERROR: Fallo selección de datos de cliente!");
	$cust = mysql_fetch_array($matra);
	$previa_id = $cust['previa_id'];
	$filaa = mysql_num_rows($matra);
		
	if($filaa == 1) {

		$num_cols = 0;
	
		$preg = "SELECT s.sub_area, s.sub_orden_id, o.op_id FROM " . $dbpfx . "subordenes s, " . $dbpfx . "orden_productos o WHERE s.previa_id = '$previa_id' AND s.sub_estatus < '130' AND s.sub_orden_id = o.sub_orden_id AND o.op_pres = '1'";
		$preg .= " GROUP BY s.sub_area ORDER BY s.sub_area ";
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de grupo de subordenes!");
		$num_cols = mysql_num_rows($matr);
		
		$pregv = "SELECT vehiculo_marca, vehiculo_tipo, vehiculo_color, vehiculo_modelo, vehiculo_placas FROM " . $dbpfx . "vehiculos WHERE vehiculo_id = '" . $cust['previa_vehiculo_id'] . "'";
		$matrv = mysql_query($pregv) or die("ERROR: Fallo selección de vehículo!" . $pregv);
		$veh = mysql_fetch_array($matrv);

		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
		
		$contenido = '			<table cellpadding="2" cellspacing="2" border="0" width="100%">
				<tr>
					<td></td>
					<td>
						<div class="contenedor80">
							<table cellpadding="0" cellspacing="0" border="0" width="840" class="izquierda body-wrap">
								<tr>
									<td style="width:230px;"><img src="particular/logo-agencia.png" alt="' . $agencia_razon_social . '" height="80"></td>
									<td style="width:400px; text-align:center;"><h2>PRESUPUESTO</h2></td>
									<td style="width:210px; vertical-align: top; line-height:14px; font-size:  11px">' . $agencia_direccion . '<br>
									Col. ' . $agencia_colonia . '. ' . $agencia_municipio . '<br>
									C.P. ' . $agencia_cp . '. ' . $agencia_estado . '<br>
									Tel. ' . $agencia_telefonos . '</td>
								</tr>
								<tr><td>Fecha: ' . $cust['previa_fecha_ultimo_movimiento'] . '</td><td style="text-align: center;">' . $veh['vehiculo_marca'] . ' ' . $veh['vehiculo_tipo'] . ' ' . $veh['vehiculo_color'] . ' ' . $veh['vehiculo_modelo'] . ' Placas:' . $veh['vehiculo_placas'] . '</td><td style="text-align: right;">No. de Presupuesto: ' . $previa_id . '</td></tr>
								<tr><td colspan="3">Cliente: ' . $cust['cliente_nombre'] . ' ' . $cust['cliente_apellidos'] . ' Tel. ' . $cust['cliente_telefono1'] . ' Email: ' . $cust['cliente_email'] . '</td></tr>
							</table>'."\n";
				
		$contenido .= '							<table cellpadding="0" cellspacing="0" border="1" class="izquierda body-wrap" width="840">' . "\n";
		while($gsub = mysql_fetch_array($matr)) {
			$preg0 = "SELECT s.sub_orden_id, s.sub_descripcion FROM " . $dbpfx . "subordenes s, " . $dbpfx . "orden_productos o WHERE s.previa_id = '$previa_id' AND s.sub_estatus < '130' AND s.sub_area = '" . $gsub['sub_area'] . "' AND s.sub_orden_id = o.sub_orden_id AND o.op_pres = '1'";
//			if($sub_orden_id != '') { $preg0 .= " AND s.sub_reporte = '$reporte'"; }
			$preg0 .= " GROUP BY s.sub_orden_id ORDER BY s.sub_area,s.sub_orden_id  ";
			$matr0 = mysql_query($preg0) or die("ERROR: ".$preg0);
			$num_grp = mysql_num_rows($matr0);
//			echo $num_grp;
			if ($num_grp > 0) {
				while($sub = mysql_fetch_array($matr0)) {
					$preg1 = "SELECT op_cantidad, op_nombre, op_precio, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_pres = '1' ORDER BY op_tangible,op_nombre ";
					$matr1 = mysql_query($preg1) or die("ERROR: ".$preg1);
					$num_op = mysql_num_rows($matr1);
					if ($num_op > 0) {
						$encab = 0;
						while($op = mysql_fetch_array($matr1)) {
							if($op['op_tangible'] == '1') {
								$items[$gsub['sub_area']][1][] = $op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
							}
							if($op['op_tangible'] == '2') {
								$items[$gsub['sub_area']][2][] = $op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
							}
							if($op['op_tangible'] == '0') {
								$items[$gsub['sub_area']][0][] = $op['op_cantidad'].'|'.$op['op_nombre'].'|'.$op['op_precio'];
							}
						}
					}
					$tareas[$gsub['sub_area']][] = $sub['sub_orden_id'];
					$descrip[$gsub['sub_area']][] = $sub['sub_descripcion'];
				}
			}
		}
		$total = 0;
		foreach($items as $j => $u) {
			$contenido .= '								<tr class="cabeza_tabla"><td colspan="6">Presupuesto de ' . constant('NOMBRE_AREA_'.$j);
			if($cust['previa_estatus'] == '10') {
				foreach($tareas[$j] as $sot) {
					$contenido .= ' <a href="previas.php?accion=agregar&previa_id=' . $previa_id . '&sub_orden_id=' . $sot . '" class="control">Modificar Presupuesto</a>';
					
				}
			}
			$contenido .= '<br>';
			foreach($descrip[$j] as $sot) {
				$contenido .= $sot . '. ';
			}
			$contenido .= '</td></tr>'."\n";
			$subarea = 0;
			$submo = 0;
			$mo_tmp = '';
			$recon = '';
			foreach($u as $k => $v) {
				$subtotal = 0;
				if($k == '0') {
					foreach($v as $l => $w) {
						$parte = explode('|', $w);
						$subtotal = round(($parte[0] * $parte[2]), 2);
						if($cant_mano_obra == 1) {
							$mo_tmp .= '										<tr><td style="padding-left:4px; padding-right:4px;">1</td><td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td></tr>'."\n";
						} else {
                            
                            $mo_tmp .= '										
                                <tr>'."\n";
                            
                            if($omite_info_presu == 1){ // omitir infomacion de los presupuestos
                               $mo_tmp .= '										
                                    <td>1</td>'."\n";
                            } else{
                                $mo_tmp .= '										
                                    <td style="padding-left:4px; padding-right:4px;">' . $parte[0] . '</td>'."\n";
                            }
                            
                            $mo_tmp .= '
                                    <td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td>'."\n";
                            
                            if($omite_info_presu == 1){ // omitir infomacion de los presupuestos
                                
                                 $mo_tmp .= '										
                                    <td></td>'."\n";
                                
                            } else{
                                
                                $mo_tmp .= '
                                    <td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($parte[2],2) . '</td>'."\n";
                                
                            }
                            $mo_tmp .= '
                                    <td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td>
                                </tr>'."\n";
						}
						$submo = $submo + $subtotal;
					}
				}
				if($k > '0') {
					foreach($v as $l => $w) {
						$parte = explode('|', $w);
						$subtotal = round(($parte[0] * $parte[2]), 2);
						
                        $recon .= '										
                            <tr>'."\n";
                        
                        if($omite_info_presu == 1){ // omitir infomacion de los presupuestos
                            
                            $recon .= '
                                <td style="padding-left:4px; padding-right:4px;">1</td>'."\n";
                        
                        } else{
                           
                            $recon .= '
                                <td style="padding-left:4px; padding-right:4px;">' . $parte[0] . '</td>'."\n";
                            
                        }
                        
                        $recon .= '        
                                <td style="padding-left:4px; padding-right:4px;">' . $parte[1] . '</td>'."\n";
                        
                        if($omite_info_presu == 1){ // omitir infomacion de los presupuestos
                            
                            $recon .= '
                                <td></td>'."\n";
                            
                        } else {
                            
                            $recon .= '
                                <td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($parte[2],2) . '</td>'."\n";
                            
                        }
                        
                        $recon .= '
                                <td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subtotal,2) . '</td>
                            </tr>'."\n";
						$subarea = $subarea + $subtotal;
					}
				}
			}

			$contenido .= '								<tr><td colspan="3">'."\n";
			$contenido .= '									
                <table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:10px;">
                    <tr>
                        <td colspan="4">Mano de Obra</td>
                    </tr>'."\n";
			$contenido .= '										
                    <tr style="text-align:center;">
                        <td>Cantidad</td>
                        <td>Nombre</td>'."\n";
            
            if($omite_info_presu == 1){ // omitir infomacion de los presupuestos
                $contenido .= '
                        <td></td>'."\n";
            } else{
                $contenido .= '
                        <td style="text-align:right;">Precio Unitario</td>'."\n";
            }
            
            $contenido .= '
                        <td style="text-align:right;">Subtotal</td>
                    </tr>'."\n";
            
            
			$contenido .= $mo_tmp;
//			echo $mo_tmp;
			$contenido .= '										<tr><td colspan="3" style="text-align:right;">Subtotal</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($submo,2) . '</td></tr>'."\n";
			$contenido .= '									</table>'."\n";
			$contenido .= '								</td><td colspan="3">'."\n";
			$contenido .= '									<table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:10px;">'."\n";
			$contenido .= '										<tr><td colspan="4">';
			if($k == '1') { $contenido .= 'Refacciones'; } elseif($k == '2') { $contenido .= 'Consumibles'; } else { $contenido .= 'Sin refacciones o consumibles'; }
			$contenido .= '</td></tr>'."\n";
			$contenido .= '										
                <tr style="text-align:center;">
                    <td>Cantidad</td>
                    <td>Nombre</td>'."\n";
            if($omite_info_presu == 1){ // omitir infomacion de los presupuestos
                
                $contenido .= '
                    <td></td>'."\n";
            } else{
                
                $contenido .= '
                    <td style="text-align:right;">Precio Unitario</td>'."\n";
                
            }
            $contenido .= '
                    <td style="text-align:right;">Subtotal</td>
                </tr>'."\n";
            
			$contenido .= $recon;
			$contenido .= '										<tr><td colspan="3" style="text-align:right;">Subtotal</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
			$contenido .= '									</table>'."\n";
			$contenido .= '								</td></tr>'."\n";
			$subarea = $subarea + $submo;

			$contenido .= '								<tr><td colspan="4" style="text-align:center; vertical-align:bottom; padding-left:4px; padding-right:4px; height:60px;">Nombre y Firma de Responsable de ' . constant('NOMBRE_AREA_'.$j) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">Sub total de ' . constant('NOMBRE_AREA_'.$j) . '</td><td style="text-align:right; padding-left:4px; padding-right:4px;">' . number_format($subarea,2) . '</td></tr>'."\n";
			$total = $total + $subarea;
		}
		if($pciva != 1) {
			$iva = round(($total * 0.16), 2);
			$gtotal = $total + $iva;
		} else {
			$gtotal = $total;
		}
		$contenido .= '								<tr class="cabeza_tabla"><td colspan="4">Observaciones:</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Sub Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($total,2) . '</td></tr>'."\n";
		$contenido .= '								<tr class="cabeza_tabla"><td colspan="4"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">';
		if($pciva != 1) { $contenido .= 'IVA (16%)'; } else { $contenido .= '&nbsp;';}
		$contenido .= '</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">';
		if($pciva != 1) { $contenido .= number_format($iva, 2); } else { $contenido .= '&nbsp;';}
		$contenido .= '</td></tr>'."\n";
		$contenido .= '								<tr class="cabeza_tabla"><td colspan="4"></td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">Gran Total</td><td style="text-align:right; font-size:14px; font-weight:bold; padding-left:4px; padding-right:4px;">' . number_format($gtotal, 2) . '</td></tr>'."\n";
		$preg2 = "SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '" . $cust['previa_asesor_id'] . "'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de usuario!" . $preg2);
		$usu = mysql_fetch_array($matr2);
		$contenido .= '								<tr><td colspan="6" style="text-align:right;">Asesor: ' . $usu['nombre'] . ' ' . $usu['apellidos'] . '</td></tr>'."\n";
		$contenido .= '						</table>'."\n";
		$contenido .= '					</td>
					<td></td>
				</tr>
			</table>'."\n";

		echo $contenido;
		
		if($envio == '1') {
			$contenido .= '			<table cellpadding="2" cellspacing="2" border="0" width="100%">
				<tr>
					<td></td>
					<td>
						<div class="contenedor80">'."\n";
			$contenido .= '						<br>
					<h5>Atentamente:</h5>
					<p>' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '<br>
        			' . $nombre_agencia . '<br>
        			Teléfonos:' . $agencia_telefonos . '<br>'."\n";
			if($_SESSION['email'] != ''){
				$contenido .= '						E-mail: ' . $_SESSION['email'] . '<br>'."\n";
			} else {
				$contenido .= '						E-mail: ' . $agencia_email . '<br>'."\n";
			}
			$contenido .= '						</p>
						<p style="font-size:9px;font-weight:bold;">Este mensaje fue
						enviado desde un sistema automático, si desea hacer algún
						comentario respecto a esta notificación o cualquier otro asunto
						respecto al Centro de Reparación por favor responda a los
						correos electrónicos o teléfonos incluidos en el cuerpo de este
						mensaje. De antemano le agradecemos su atención y preferencia.</p>'."\n";
			$contenido .= '					</td>
					<td></td>
				</tr>
			</table>'."\n";

			
			$asunto = 'Presupuesto para ' . $veh['vehiculo_marca'] . ' ' . $veh['vehiculo_tipo'] . ' ' . $veh['vehiculo_color'] . ' ' . $veh['vehiculo_modelo'] . ' Placas:' . $veh['vehiculo_placas'];
			$para = $cust['cliente_email'];
			$respondera = $_SESSION['email'];

			include('parciales/notifica2.php');
		}

		echo '			<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="840">' . "\n";
		echo '				<tr><td colspan="4" style="height:15px;"></td></tr>'."\n";
		echo '				<tr><td colspan="4" style="text-align:left;">
				<div class="control">'."\n";
		echo '					<a href="personas.php?accion=consultar&cliente_id=' . $cust['cliente_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/datos-de-clientes.png" alt="Regresar a Datos del Cliente" title="Regresar a Datos del Cliente"></a> '."\n";
		if($vehiculo_id != '') {
			echo '					<a href="vehiculos.php?accion=consultar&vehiculo_id=' . $vehiculo_id . '"><img src="idiomas/' . $idioma . '/imagenes/listar-autos.png" alt="Regresar a Datos del Vehículo" title="Regresar a Datos del Vehículo"></a> '."\n";
		}
		if($cust['previa_estatus'] <= '10') {
			echo '					<a href="previas.php?accion=agregar&previa_id=' . $previa_id . '"><img src="idiomas/' . $idioma . '/imagenes/agregar-tarea.png" alt="Agregar Tarea" title="Agregar Tarea"></a> '."\n";
			echo '					<a href="previas.php?accion=cerrar&previa_id=' . $previa_id . '"><img src="idiomas/' . $idioma . '/imagenes/archivar.png" alt="Cerrar Presupuesto Previo" title="Cerrar Presupuesto Previo"></a> '."\n";
			echo '					<a href="previas.php?accion=cancelar&previa_id=' . $previa_id . '"><img src="idiomas/' . $idioma . '/imagenes/cancelar.png" alt="Cerrar Presupuesto Previo" title="Cerrar Presupuesto Previo"></a> '."\n";
		}
		if($cust['previa_estatus'] == '99') {
			echo '					<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir-presupuesto.png" alt="Imprimir Todas las SOT de la OT" title="Imprimir Todas las SOT de la OT"></a>'."\n";
			$retorno0 = validaAcceso('1095000', $dbpfx);
			if($retorno0 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1' || $_SESSION['rol12']=='1') {
				echo '					<a href="factura.php?accion=consultar&previa_id=' . $previa_id . '"><img src="idiomas/' . $idioma . '/imagenes/facturas.png" alt="Facturar Presupuesto" title="Facturar Presupuesto"></a>
					<a href="previas.php?accion=consultar&previa_id=' . $previa_id . '&envio=1"><img src="idiomas/' . $idioma . '/imagenes/enviar_correo.png" alt="envíar por correo" title="envíar por correo"></a>'."\n";
			}
		}
		if($cust['previa_estatus'] >= '90') {
			echo '					<a href="previas.php?accion=crear&vehiculo_id=' . $cust['previa_vehiculo_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/nuevo-previo.png" alt="Crear Nuevo Presupuesto" title="Crear Nuevo Presupuesto"></a>'."\n";
		}
		echo '					<a href="documentos.php?accion=listar&previa_id=' . $previa_id . '"><img src="idiomas/' . $idioma . '/imagenes/documento.png" alt="Mostrar Documentos" title="Mostrar Documentos"></a>'."\n";
		echo '				</div></td></tr>'."\n";
		echo '			</table>'."\n";
	} elseif($filaa > 1) {
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		echo '			<table cellspacing="2" cellpadding="2" border="1" width="840">
				<tr class="cabeza_tabla"><td colspan="3" align="left">' . $lang['Listado de Presupuestos Previos'];
		if($vehiculo_id != '') {
			echo 'Vehículo: ' . $vehiculo_id;
		} else {
			echo 'Cliente: ' . $cliente_id;
		}
		echo '</td></tr>'."\n";
		echo '				<tr><td>' . $lang['Número de Presupuesto'] . '</td><td>' . $lang['Descripción de Tareas'] . '</td><td>' . $lang['Estatus del Presupuesto'] . '</td></tr>'; $fondo = 'obscuro';
		mysql_data_seek($matra, 0);
		while ($prev = mysql_fetch_array($matra)) {
			$preg0 = "SELECT sub_descripcion, sub_area FROM " . $dbpfx . "subordenes WHERE previa_id = '" . $prev['previa_id'] . "' AND sub_estatus < '130'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de datos de previas!");
			$fila0 = mysql_num_rows($matr0);
			if($fila0 > 0) {
				$asunto = '';
				while($sub = mysql_fetch_array($matr0)) {
					$asunto .= constant('NOMBRE_AREA_' . $sub['sub_area']) . ': ' . $sub['sub_descripcion'] . '.<br>'; 
				}
			} else {
				$asunto = $lang['No se han creado tareas'];
			}
			echo '		<tr class="' . $fondo . '">
				<td style="text-align:center;"><a href="previas.php?accion=consultar&previa_id=' . $prev['previa_id'] . '">' . $prev['previa_id'] . '</a></td>
				<td>' . $asunto . '</td>
				<td>' . constant('PREVIA_ESTATUS_' . $prev['previa_estatus']) . '</td>
			</tr>';
			if($fondo == 'obscuro') { $fondo = 'claro'; } else { $fondo = 'obscuro'; }
		}
		echo '	</table>';
	} else {
		$mensaje = 'No se encontró el Presupuesto Previo ' . $previa_id;
		if($previa_id == '' && $vehiculo_id != '') {
			redirigir('previas.php?accion=crear&vehiculo_id='.$vehiculo_id);
		} elseif($previa_id == '' && $cliente_id != '') {
			$mensaje .= ' para el Cliente ' . $cliente_id;
			$_SESSION['msjerror'] = $mensaje;
			redirigir('personas.php?accion=consultar&cliente_id=' . $cliente_id);
		}
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		echo '<p>' . $mensaje . '</p>'."\n";
		
	}
}

elseif ($accion==='agregar') {
	
	$funnum = 1155010;
	$retorno = validaAcceso($funnum, $dbpfx);
	
	if ($retorno == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol04']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol06']=='1') {
		$msj = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	
	echo '	<form action="previas.php?accion=presupuesto" method="post" enctype="multipart/form-data" name="rapida">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	$pregv = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_color, v.vehiculo_modelo, v.vehiculo_placas FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "previas p WHERE v.vehiculo_id = p.previa_vehiculo_id AND p.previa_id = '$previa_id'";
	$matrv = mysql_query($pregv) or die("ERROR: Fallo selección de vehículo!" . $pregv);
	$veh = mysql_fetch_array($matrv);
	echo '		<tr><td colspan="2" style="text-align:left;">' . $veh['vehiculo_marca'] . ' ' . $veh['vehiculo_tipo'] . ' ' . $veh['vehiculo_color'] . ' ' . $veh['vehiculo_modelo'] . ' Placas:' . $veh['vehiculo_placas'] . '</td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left;">Agregar Refacciones y Mano de Obra desde Catálogo al Presupuesto</td></tr>'."\n";	
	echo '		<tr><td colspan="2" style="text-align:left; font-size:16px;"><a href="previas.php?accion=cesta&previa_id=' . $previa_id . '&sub_orden_id='.$sub_orden_id;
	if($preaut === '1') { echo '&preaut=1'; }
	echo '"><img src="idiomas/' . $idioma . '/imagenes/refacciones.png" alt="Asignar Refacciones desde Almacén" title="Asignar Refacciones desde Almacén"> Agregar Refacciones y Mano de Obra desde Catálogo al Presupuesto</a></td></tr>'."\n";	
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left;">Productos, Materiales y Mano de Obra a presupuestar para la Tarea:</td></tr>'."\n";
	echo '<input type="hidden" name="particular" value="1" />';
	
	if($sub_orden_id != '') {
		$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
   	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	  	$sub_orden = mysql_fetch_array($matriz);
		echo '		<tr><td colspan="2" style="text-align:left;"><br>Area: <span style="font-size:16px; font-weight:bold;">' . constant('NOMBRE_AREA_' . $sub_orden['sub_area']) . '</span>.</td></tr>'."\n";
	} else {
		echo '		<tr><td colspan="2" style="text-align:left;">Seleccione el Área del Presupuesto:<br>
			<select name="areas" id="areas" size="1" onchange="componerPaquetes(document.rapida.areas[selectedIndex].value)">
				<option value="" >Seleccione...</option>';
		for($i=1;$i<=$num_areas_servicio;$i++) {
			echo '			<option value="' . $i . '"';
			if($i == $_SESSION['prev']['areas']) { echo ' selected="selected" '; }
			
			echo '>' . constant('NOMBRE_AREA_' . $i) . '</option>'."\n";
		}
	echo '			</select>
			</td>
		</tr>'."\n";
	echo '			<tr><td colspan="2" style="text-align:left;">Seleccionar un Paquete de Servicio</td></tr>
				<tr><td colspan="2" style="text-align:left;">
					<select name="paquetes" size="1">'."\n";
	echo '				</select>
				</td></tr>'."\n";
	}
	
	echo '		<tr><td colspan="2" style="text-align:left;">Descripción de tarea:<br><textarea name="descripcion" rows="5" cols="70" >';
	if($_SESSION['prev']['descrip'] != '') { echo $_SESSION['prev']['descrip']; }
	else { echo $sub_orden['sub_descripcion']; } 
	echo '</textarea></td></tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;">';
	echo 'Agrega una REFACCION o MATERIAL DE PINTURA por renglón:<br><span style="color:#f00; font-weight:bold;">Cantidad, Descripción y Precio al Público:</span>';
	echo '</td></tr>
		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audasust" cols="70" rows="13" style="background-color:#FFFFB0;" /></textarea></td></tr>
		<tr><td colspan="2" style="text-align:left;"><hr></td></tr>'."\n";
	
	echo '		<tr><td colspan="2" style="text-align:left;">';
	echo 'Agrega la MANO DE OBRA, un trabajo por renglón con la<br><span style="color:#f00; font-weight:bold;">Descripción y Precio al Público de cada trabajo:</span></td></tr>
		<tr><td colspan="2" valign="top" style="text-align:left;"><textarea name="audamo" cols="70" rows="13" style="background-color:#FFFFB0;" /></textarea></td></tr>
		<tr><td colspan="2" style="text-align:left;"><hr></td></tr>'."\n";

	$preg0 = "SELECT op_id, op_codigo, op_nombre, op_cantidad, op_precio, prod_id, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden['sub_orden_id'] . "' AND op_pres = '1' AND op_pedido < 1 ORDER BY op_tangible,op_nombre";
  	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de requeridos!");
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Refacciones, Materiales y Mano de Obra ya presupuestado:';
	echo '</td></tr>
			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td>Tipo</td><td>Cantidad</td><td>Nombre</td><td>Código</td><td>Precio</td><td>Borrar?</td></tr>'."\n";
	$cuenta = 0;
	while($op = mysql_fetch_array($matr0)) {
		if($op['op_tangible'] == '1') { $tipo = 'Refacción';}
		elseif($op['op_tangible'] == '2') { $tipo = 'Consumible';}
		else {$tipo = 'MO';}
		echo '					<tr><td style="text-align:center;">' . $tipo . '</td><td style="text-align:center;">' . $op['op_cantidad'] . '</td><td>' . $op['op_nombre'] . '</td><td>' . $op['op_codigo'] . '</td><td style="text-align:right;">' . money_format('%n', $op['op_precio']) . '</td><td><input type="checkbox" name="borrar[' . $cuenta . ']" value="1" /><input type="hidden" name="op_id[' . $cuenta . ']" value="' . $op['op_id'] . '" /></td></tr>'."\n";
		$cuenta++;
	}
	echo '				</table>';
	echo '			</td>
		</tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">'."\n";
	echo '			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="previa_id" value="' . $previa_id . '" />'."\n";
	if($sub_orden_id != '') {
		echo '			<input type="hidden" name="areas" value="' . $sub_orden['sub_area'] . '" />'."\n";
	}
	echo '		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>'."\n";
	echo '	</table>
	</form>'."\n";
}

elseif ($accion==='presupuesto') {
	
	$funnum = 1155015;
	$retorno = validaAcceso($funnum, $dbpfx);
	
	if ($retorno == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol04']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol06']=='1') {
		$msj = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado, Se requiere permiso para esta función');
	}
	
	echo 'Areas: ' . $areas . 'Desc: ' . $descripcion .'<br>';
	
	$error = 'no';
	$area = preparar_entrada_bd($areas); $_SESSION['prev']['areas'] = $area;
	$descripcion = preparar_entrada_bd($descripcion); $_SESSION['prev']['descrip'] = $descripcion;
//	echo 'Area: ' . $areas . ' Paquetes: ' . $paquetes . '<br>';

	if($sub_orden_id == '') {
		if($areas == '' || $areas == '0') { 
			$error = 'si';
			$msj .= 'Por favor selecciona una área de trabajo.<br>';
		}
		if($descripcion == '' && $paquetes == '') {
			$error = 'si';
			$msj .= 'Por favor indica la descripción de el trabajo a realizar.<br>';
		} 

		if($error == 'si') {
			$_SESSION['msjerror'] = $msj;
			redirigir('previas.php?accion=agregar&previa_id=' . $previa_id);
		}

		$sql_data_array = array('previa_id' => $previa_id,
				'sub_area' => $areas,
				'sub_descripcion' => $descripcion,
				'sub_reporte' => '0',
				'sub_aseguradora' => '0');
		$sub_orden_id = ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
		unset($_SESSION['prev']);
	}
	
	$sub_orden_id=preparar_entrada_bd($sub_orden_id);
	
	$autosurtido = '0';  // Presupuesto de Taller por Autorizar....
	
	$audasust2 = preg_split("/[\n]+/", $audasust);
//	print_r($audasust2);
	foreach ($audasust2 as $i => $v) {
		$precioar = '';
		unset($estructural);
		$codigo ='';
		$descripcion = '';
		$cant = '';
// Identificar partes estructurales con un & al final de la línea de partes a sustituir.		
		for($j = strlen($v); $j >= 0; $j--) {
			if(ord($v[$j]) == 38) {
				$estructural = 1;
				break;
			} elseif(ord($v[$j]) != 32) {
				break;
			}
		}
// Obtener precio de parte
		for($j = strlen($v); $j >= 0; $j--) {
			if($v[$j]=='#') {
				$des = substr($v, 0, $j);
				$precioar = 0;
				break;
			}
			elseif(is_numeric($v[$j]) || $v[$j]=='.' ) {
				if($v[$j]==',') { $v[$j]='.'; }
				$precioar = $v[$j] . $precioar;
			}
			elseif(($v[$j]==' ' || ord($v[$j]) == '9') && $precioar!='') {
				$des = substr($v, 0, $j);
			break;
			}
		}

		for($j = 0; strlen($des) >= $j; $j++) {
			if(is_numeric($des[$j]) || $des[$j]=='.' ) {
				$cant = $cant . $des[$j];
			}
			elseif($des[$j]==' ' && $cant!='') {
				$descripcion = substr($des, $j);
			break;
			}
		}

		$audaprod[$i][0] = trim($descripcion);
		$audaprod[$i][1] = trim($precioar);
		$audaprod[$i][2] = trim($estructural);
//		$audaprod[$i][3] = trim($codigo);
		$audaprod[$i][4] = trim($cant);
	}
//	print_r($audaprod);
//	echo '<br><br>';
	unset($audasust, $audasust2);


//	print_r($audamo); echo '<br>';

	$audamo2 = preg_split("/[\n]+/", $audamo);
		$cant = 0;
	foreach ($audamo2 as $i => $v) {
		$precioar = '';
		$descripcion = $v;
		
		for($j = strlen($v); $j >= 0; $j--) {
			if($v[$j]=='#') {
				$descripcion = substr($v, 0, $j);
				$precioar = 0;
				break;
			}
			elseif(is_numeric($v[$j]) || $v[$j]=='.' ) {
				$precioar = $v[$j] . $precioar;
			}
			elseif($v[$j]==' ' && $precioar!='') {
				$descripcion = substr($v, 0, $j);
				break;
			}
		}
		$audaobr[$i][0] = trim($descripcion);
		$audaobr[$i][1] = trim($precioar);
	}
//	print_r($audaobr);
//	echo '<br><br>';
	unset($audamo2, $audamo);

	$error = 'no';
	$mensaje= '';
	$parametros='sub_orden_id = ' . $sub_orden_id;

   if (($error === 'no') && (isset($paquetes) || is_array($audaobr) || is_array($audaprod) || is_array($op_id) || is_array($prod_id))) {
		if (is_array($op_id)) {
			for($i=0;$i<count($op_id);$i++) {
				if(isset($borrar[$i]) && $borrar[$i]=='1') {
					$pregunta="DELETE FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op_id[$i] . "' AND op_pedido = '0'";
					$resultado = mysql_query($pregunta);
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $pregunta . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				}
			}
		}

//-------------- 	DETERMINACIÓN DE NÚMERO DE ITEM	--------------------

		$preg6 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE previa_id = '$previa_id'";
		$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de subordenes items!");
		$item = 1;
		while($dato6 = mysql_fetch_array($matr6)) {
			$preg5 = "SELECT op_item FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $dato6['sub_orden_id'] . "' ORDER BY op_item DESC LIMIT 1";
  			$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden_productos!");
  			$dato5 = mysql_fetch_array($matr5);
			if($dato5['op_item'] >= $item) {$item = $dato5['op_item'] + 1;}
		}

//--------------  Fin de determinación de número de Item	--------------------

		$refacciones=0;
		$preg1 = "SELECT prod_id, op_cantidad, op_precio, op_descuento, op_tangible, op_estructural, op_recibidos FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "'";
//		echo $preg1;
  		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden_productos!");
  		while($op = mysql_fetch_array($matr1)) {
			$op_subtotal = $op['op_cantidad'] * ($op['op_precio'] - $op['op_descuento']);
			if($op['op_tangible']=='1') {
				$sub_partes = $sub_partes + $op_subtotal;
			} elseif($op['op_tangible']=='2') {
				$sub_consumibles = $sub_consumibles + $op_subtotal;
			} else {
				$sub_mo = $sub_mo + $op_subtotal;
				$tiempo = $tiempo + $op['op_cantidad'];
			}
//			echo $op_subtotal . '<br>';
			$presupuesto = $presupuesto + $op_subtotal;
  		}

		if (is_array($prod_id)) {
			for($i=0;$i<count($prod_id);$i++) {
				if($prod_cantidad[$i]!='') {
//						$preg1 = "SELECT prod_cantidad_disponible FROM " . $dbpfx . "productos WHERE prod_id = '" . $prod_id[$i] . "'";
//  					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de op_prods!");
//  					$op = mysql_fetch_array($matr1);
  					if($prod_cantidad[$i] > $prod_disponible[$i]) { $refacciones=1; }
					if($prod_tangible[$i]=='1') {
						$prod_cantidad[$i] = intval($prod_cantidad[$i]);
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_partes = $sub_partes + $op_subtotal;
					} elseif($prod_tangible[$i]=='2') {
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_consumibles = $sub_consumibles + $op_subtotal;
					} else {
						if($sub_aseguradora > 0) { $prod_precio[$i] = $ut[$sub_aseguradora]; }
						$op_subtotal= $prod_cantidad[$i] * $prod_precio[$i];
						$sub_mo = $sub_mo + $op_subtotal;
						$tiempo = $tiempo + $prod_cantidad[$i];
					}
					$presupuesto = $presupuesto + $op_subtotal;
					$sql_data_array = array('sub_orden_id' => $sub_orden_id,
						'op_area' => $area,
						'op_item' => $item,
						'prod_id' => $prod_id[$i],
						'op_cantidad' => $prod_cantidad[$i], 
						'op_nombre' => $prod_nombre[$i],
						'op_codigo' => $prod_codigo[$i],
						'op_tangible' => $prod_tangible[$i],
						'op_precio' => $prod_precio[$i],
						'op_costo' => $prod_costo[$i],
						'op_pres' => '1',
						'op_subtotal' => $op_subtotal);
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
					$item++;
				}
			}
		}

		if(isset($paquetes) && $paquetes != '') {
	  		$preg3 = "SELECT paq_descripcion, paq_nombre FROM " . $dbpfx . "paquetes WHERE paq_id='" . $paquetes . "'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de paq_prods! ".$preg3);
			$desc = mysql_fetch_array($matr3);
			$preg4 = "SELECT pc_area_id FROM " . $dbpfx . "paq_comp WHERE pc_paq_id='" . $paquetes . "' GROUP BY pc_area_id";
//			echo $preg3;
			$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de Componentes de Paquete!");
			while($tarea = mysql_fetch_array($matr4)) {
/*	      	$sql_data_array = array('previa_id' => $previa_id,
   	   		'sub_area' => $tarea['pc_area_id'],
      			'sub_descripcion' => $desc['paq_nombre'] . ': ' . $desc['paq_descripcion']);
			$sub_orden_id = ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
*/
				$preg0 = "SELECT pc_prod_id, pc_prod_cant FROM " . $dbpfx . "paq_comp WHERE pc_paq_id='" . $paquetes . "'AND pc_area_id = '" . $tarea['pc_area_id'] . "'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de paq_prods!");
				echo $preg0;
				$presupuesto = 0;
				$sub_partes = 0;
				$sub_consumibles = 0;
				$sub_mo = 0;
				$tiempo = 0;
				while($paqs = mysql_fetch_array($matr0)) {
					$preg1 = "SELECT prod_codigo, prod_nombre, prod_tangible, prod_precio, prod_cantidad_disponible FROM " . $dbpfx . "productos WHERE prod_id='" . $paqs['pc_prod_id'] . "'";
//	  				echo $preg1.'<br>';
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de paq_prods!");
					while($prods = mysql_fetch_array($matr1)) {
						$op_subtotal= $paqs['pc_prod_cant'] * $prods['prod_precio'];
						$presupuesto = $presupuesto + $op_subtotal;
						if($prods['prod_tangible']=='1') {
							$sub_partes = $sub_partes + $op_subtotal;
						} elseif($prods['prod_tangible']=='2') {
							$sub_consumibles = $sub_consumibles + $op_subtotal;
						} else {
							$sub_mo = $sub_mo + $op_subtotal;
							$tiempo = $tiempo + $paqs['pc_prod_cant'];
						}
						$sql_data_array = array('sub_orden_id' => $sub_orden_id,
							'op_area' => $tarea['pc_area_id'],
							'op_item' => $item,
							'prod_id' => $paqs['pc_prod_id'],
							'op_nombre' => $prods['prod_nombre'],
							'op_codigo' => $prods['prod_codigo'],
							'op_cantidad' => $paqs['pc_prod_cant'],
							'op_tangible' => $prods['prod_tangible'],
							'op_pres' => '1',
							'op_precio' => $prods['prod_precio'],
							'op_subtotal' => $op_subtotal);
/*						if($paqs['pc_prod_cant'] > $prods['prod_cantidad_disponible'] && $prods['prod_tangible'] > '0') {
							$sql_data_array['op_recibidos'] = $prods['prod_cantidad_disponible'];
							$ajus01 = "UPDATE " . $dbpfx . "productos SET prod_cantidad_disponible = '0' WHERE prod_id = '" . $paqs['pc_prod_id'] . "'";
							$reajus01 = mysql_query($ajus01) or die("ERROR: Fallo actualización de productos!");
							if($prods['prod_tangible'] == '1') { $refacciones=1; }
							elseif ($prods['prod_tangible'] == '2') { $consumibles = 1; }
						} elseif($paqs['pc_prod_cant'] <= $prods['prod_cantidad_disponible'] && $prods['prod_tangible'] > '0') {
							$sql_data_array['op_recibidos'] = $paqs['pc_prod_cant'];
							$ajus01 = "UPDATE " . $dbpfx . "productos SET prod_cantidad_disponible = prod_cantidad_disponible - '" . $paqs['pc_prod_cant'] . "' WHERE prod_id = '" . $paqs['pc_prod_id'] . "'";
							$reajus01 = mysql_query($ajus01) or die("ERROR: Fallo actualización de productos!");
						}
*/
						$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'insertar');
						$item++;
					}
				}
				$horas = intval($tiempo);
				$minutos = intval(($tiempo - $horas)*60);
				if($minutos==0) {$minutos='00';}
				$programadas = $horas . ':' . $minutos;
				$parametros='sub_orden_id = ' . $sub_orden_id;
			  	$sql_data_array = array('sub_presupuesto' => $presupuesto,
	  				'sub_partes' => $sub_partes, 
	  				'sub_consumibles' => $sub_consumibles, 
			  		'sub_mo' => $sub_mo,
			  		'sub_descripcion' => $desc['paq_nombre'] . ': ' . $desc['paq_descripcion'],
			  		'sub_valuador' => $_SESSION['usuario'],
	  				'sub_deducible' => $deducible,
	  				'sub_fecha_presupuesto' => date('Y-m-d H:i:s'),
		  			'sub_horas_programadas' => $programadas,
			  		'sub_refacciones_recibidas' => $refacciones);
			  	ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		  		unset($_SESSION['pres']['sub_orden_id']);
		  	}
		}

		if (is_array($audaprod)) {
			$op_subtotal = 0;
			for($i=0;$i<=count($audaprod);$i++) {
				if(($audaprod[$i][0]!='') && ($audaprod[$i][1]!='')) {
					$cant1 = $audaprod[$i][4];
					if($area=='7') { $tang = 2; } else { $tang = 1; }
					$op_subtotal = round(($cant1 * ($audaprod[$i][1] - $descuento)),6);
					$sql_data_array3 = array('sub_orden_id' => $sub_orden_id,
						'prod_id' => '0',
						'op_area' => $area,
						'op_item' => $item,
						'op_nombre' => $audaprod[$i][0],
						'op_cantidad' => $cant1,
						'op_precio' => $audaprod[$i][1],
						'op_subtotal' => $op_subtotal,
						'op_autosurtido' => $autosurtido,
						'op_pres' => '1',
						'op_tangible' => $tang,
						'op_estructural' => $audaprod[$i][2]);
					if($bloqueaprecio != '1') {
						if($tang == '1') {
							$sub_partes = $sub_partes + $op_subtotal;
						} else {
							$sub_consumibles = $sub_consumibles + $op_subtotal;
						}
						$presupuesto = $presupuesto + $op_subtotal;
					}
					ejecutar_db($dbpfx . 'orden_productos', $sql_data_array3, 'insertar');
					$item++;
				}
			}
		}

		if (is_array($audaobr)) {
			for($i=0;$i<=count($audaobr);$i++) {
				if(($audaobr[$i][0]!='') && ($audaobr[$i][1]!='')) {
					$cant1 = round(($audaobr[$i][1] / $preciout), 6);
					$tiempo = $tiempo + $cant1;
					if($cant1 < 0) { $preciout = $preciout * -1; }
					$sql_data_array4 = array('sub_orden_id' => $sub_orden_id,
						'prod_id' => '0',
						'op_area' => $area,
						'op_item' => $item,
						'op_nombre' => $audaobr[$i][0],
						'op_tangible' => 0,
						'op_autosurtido' => $autosurtido,
						'op_pres' => '1',
						'op_cantidad' => $cant1);
					if($bloqueaprecio != '1') {
						$op_subtotal= round(($cant1 * $preciout), 6);
						$sub_mo = $sub_mo + $op_subtotal;
						$presupuesto = $presupuesto + $op_subtotal;
						$sql_data_array4['op_precio'] = $preciout;
						$sql_data_array4['op_subtotal'] = $op_subtotal;
					}
					$nueva_id = ejecutar_db($dbpfx . 'orden_productos', $sql_data_array4, 'insertar');
					$item++;
				}
			}
		}

		$horas = intval($tiempo);
		$minutos = round((($tiempo - $horas)*60), 2);
		if($minutos==0) {$minutos='00';}
		$programadas = $horas . ':' . $minutos;
	  	$sql_data_array = array('sub_fecha_presupuesto' => date('Y-m-d H:i:s'),
	  		'sub_presupuesto' => $presupuesto,
	  		'sub_partes' => $sub_partes,
	  		'sub_consumibles' => $sub_consumibles,
	  		'sub_mo' => $sub_mo,
	  		'sub_horas_programadas' => $programadas);
	  	if($desdecesta == '1') {
	  		$sql_data_array['sub_area'] = $areas;
	  		$sql_data_array['sub_descripcion'] = $descrip;
	  	}
	  	$parametros='sub_orden_id = ' . $sub_orden_id;
//	  	print_r($sql_data_array);
	  	ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		bitacora(0, 'Presupuesto Creado o Modificado para Tarea ' . $sub_orden_id, $dbpfx, '', '', '', $previa_id);
		unset($sql_data_array, $_SESSION['prev']);
	  	redirigir('previas.php?accion=consultar&previa_id=' . $previa_id);
	} else {
		$_SESSION['msjerror'] = 'No se recibieron datos';
		redirigir('previas.php?accion=agrear&previa_id=' . $previa_id . '&sub_orden_id=' . $sub_orden_id);
	}
}

elseif ($accion==='cerrar' || $accion==='cancelar') {

	$funnum = 1155020;
	$retorno = validaAcceso($funnum, $dbpfx);
	
//	echo 'Estamos en la sección  consulta';
	if ($retorno == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol04']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol06']=='1') {
		// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado.');
	}	
	
	$sql_array = array('previa_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'));
	if($accion==='cerrar') {
		$sql_array['previa_estatus'] = 99; 
		$registro = 'Presupuesto Previo ' . $previa_id . ' Concluido';
	} elseif($accion==='cancelar') {
		$sql_array['previa_estatus'] = 90;
		$registro = 'Presupuesto Previo ' . $previa_id . ' Cancelado';
	}
	$param ="previa_id = '$previa_id'";

	ejecutar_db($dbpfx . 'previas', $sql_array, 'actualizar', $param);
	bitacora(0, $registro, $dbpfx, '', '', '', $previa_id);
	if($accion==='cerrar') {
		redirigir('previas.php?accion=consultar&previa_id=' . $previa_id);
	} else {
		redirigir('index.php');
	}
}

elseif ($accion==='cesta') {
	
	$funnum = 1045030;
	
	if($sub_orden_id != '') {
		$pregunta = "SELECT * FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '$sub_orden_id'";
   	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección! ".$pregunta);
	  	$sub_orden = mysql_fetch_array($matriz);
	} else {
		$sql_data_array = array('previa_id' => $previa_id,
				'sub_reporte' => '0',
				'sub_aseguradora' => '0');
		$sub_orden_id = ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'insertar');
		if(isset($areas)) { $_SESSION['prev']['areas'] = $areas; }
		if(isset($descrip)) { $_SESSION['prev']['descrip'] = $descrip; }		
	}
  	$pregunta2 = "SELECT * FROM " . $dbpfx . "previas WHERE previa_id = '" . $previa_id . "'";
   $matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo selección! ".$pregunta2);
  	$previa = mysql_fetch_array($matriz2);
  	$preg3 = "SELECT * FROM " . $dbpfx . "vehiculos WHERE vehiculo_id = '" . $previa['previa_vehiculo_id'] . "'";
  	$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección! ".$preg3);
  	$veh = mysql_fetch_array($matr3);
	
	echo '	<form action="previas.php?accion=cesta" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td colspan="2" style="text-align:left;">Vehículo: ' . $veh['vehiculo_marca'] . ' ' . $veh['vehiculo_tipo'] . ' ' . $veh['vehiculo_color'] . $lang['Placas'] . $veh['vehiculo_placas'] .'</td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Seleccionar del Almacén</td></tr>'."\n";
	if($preaut==1) { echo '<input type="hidden" name="preaut" value="1" />'; }
	echo '		<tr class="obscuro espacio"><td style="text-align:left; width:50%;">Filtrar por Almacén: 
			<select name="almacen" size="1">
				<option value="">Seleccionar...</option>'."\n";
	for($i=1;$i<=$num_almacenes;$i++) {
		echo '				<option value="' . $i . '"';
		if($almacen == $i) { echo ' selected '; }
		echo '>' . $nom_almacen[$i] . '</option>'."\n";
	}											
	echo '			</select>
			<input name="Enviar" value="Enviar" type="submit">';
	echo '		</td><td style="text-align:left;">Buscar por nombre: <input type="text" name="nombre" value="' . $nombre . '" size="15">
			<input name="Enviar" value="Enviar" type="submit"><input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '"><input type="hidden" name="previa_id" value="' . $previa_id . '">
			<input type="hidden" name="cpres" value="' . $cpres . '" />
			</td></tr></table></form>'."\n";
	if((isset($almacen) && $almacen!='') || (isset($nombre) && $nombre!='')) { 
		if(isset($almacen) && $almacen!='') { 
			$preg .= "AND prod_almacen='" . $almacen . "' ";
			if(isset($nombre) && $nombre!='') {
				$nomped = explode(' ', $nombre);
				if(count($nomped) > 0) {
					foreach($nomped as $kc => $vc){
						$preg .= "AND prod_nombre LIKE '%" . $vc . "%' ";
					}
				} 
			}
		}
		elseif(isset($nombre) && $nombre!='') { 
			$nomped = explode(' ', $nombre);
			if(count($nomped) > 0) {
				foreach($nomped as $kc => $vc){
					$preg .= "AND prod_nombre LIKE '%" . $vc . "%' ";
				}
			} 
		}
	}
	
	$preg2 = "SELECT prod_id FROM " . $dbpfx . "productos WHERE prod_activo='1' AND prod_tangible < '3' ";
	$preg2 = $preg2 . $preg;
//	echo $preg2;
   $matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos!");
   $filas = mysql_num_rows($matr2);

   $renglones = 100;
   $paginas = (round(($filas / $renglones) + 0.49999999) - 1);
   if(!isset($pagina)) { $pagina = 0;}
   $inicial = $pagina * $renglones;
//	echo $paginas;
	$preg3 = "SELECT prod_id, prod_codigo, prod_marca, prod_nombre, prod_cantidad_disponible, prod_precio, prod_precioint, prod_tangible, prod_almacen FROM " . $dbpfx . "productos WHERE prod_activo='1' AND prod_tangible < '3' ";
	$preg3 = $preg3 . $preg;
	$preg3 .= "ORDER BY prod_almacen,prod_nombre LIMIT " . $inicial . ", " . $renglones;
   $matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de productos!");
	echo '	<form action="previas.php?accion=presupuesto" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;">Seleccione el Área del Presupuesto:<br>
			<select name="areas">
				<option value="" >Seleccione...</option>';
	for($i=1;$i<=$num_areas_servicio;$i++) {
		echo '			<option value="' . $i . '"';
		if($i == $_SESSION['prev']['areas']) { echo ' selected="selected" '; } 
		elseif($sub_orden['sub_area'] == $i) { echo ' selected="selected" '; }
		echo '>' . constant('NOMBRE_AREA_' . $i) . '</option>'."\n";
	}
	echo '			</select>
			</td>
		</tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;">Descripción de Tarea:<br><textarea name="descrip" rows="5" cols="70" >';
	if($_SESSION['prev']['descrip'] != '') { echo $_SESSION['prev']['descrip']; }
	elseif($sub_orden['sub_descripcion'] != '') { echo $sub_orden['sub_descripcion']; } 
	echo '</textarea></td></tr>'."\n";

//	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Seleccionar del Almacén</td></tr>'."\n";
	echo '			<tr><td colspan="2"><a href="previas.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=0&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
	if($pagina > 0) {
		$url = $pagina - 1;
		echo '<a href="previas.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $url . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
	}
	if($pagina < $paginas) {
		$url = $pagina + 1;
		echo '<a href="previas.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $url . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
	}
	echo '<a href="previas.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $paginas . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>';
	echo '</td></tr>'."\n";
	echo '			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td style="width:30px;text-align:center;">Almacén</td><td style="width:350px;">Nombre</td><td style="width:0px;">Código</td><td>Marca</td><td style="width:30px;">Disponibles</td><td>Precio Público</td><td>Precio Interno</td><td>Cantidad</td></tr>'."\n";
	$cue = 0;
	while($prods = mysql_fetch_array($matr3)) {
		echo '					<tr><td>' . constant('NOMBRE_ALMACEN_'.$prods['prod_almacen']) . '</td><td>' . $prods['prod_nombre'] . '<input type="hidden" name="prod_nombre[' . $cue . ']" value="' . $prods['prod_nombre'] . '" /></td><td>' . $prods['prod_codigo'] . '<input type="hidden" name="prod_codigo[' . $cue . ']" value="' . $prods['prod_codigo'] . '" /><td>' . $prods['prod_marca'] . '</td><td style="text-align:right;">' . $prods['prod_cantidad_disponible'] . '<input type="hidden" name="prod_disponible[' . $cue . ']" value="' . $prods['prod_cantidad_disponible'] . '" /></td><td style="text-align:right;">' . money_format('%n', $prods['prod_precio']) . '</td><td style="text-align:right;">' . money_format('%n', $prods['prod_precioint']) . '</td><td><input type="text" name="prod_cantidad[' . $cue . ']" size="4" /><input type="hidden" name="prod_id[' . $cue . ']" value="' . $prods['prod_id'] . '" /><input type="hidden" name="prod_precio[' . $cue . ']" value="' . $prods['prod_precio'] . '" /><input type="hidden" name="prod_costo[' . $cue . ']" value="' . $prods['prod_precioint'] . '" /><input type="hidden" name="prod_tangible[' . $cue . ']" value="' . $prods['prod_tangible'] . '" /></td></tr>'."\n";
		$cue++;
	}
	echo '				</table>'."\n";
	echo '			</td>
		</tr>'."\n";
	echo '			<tr><td colspan="2"><a href="previas.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=0&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
	if($pagina > 0) {
		$url = $pagina - 1;
		echo '<a href="presvias.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $url . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
	}
	if($pagina < $paginas) {
		$url = $pagina + 1;
		echo '<a href="previas.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $url . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
	}
	echo '<a href="previas.php?accion=cesta&sub_orden_id=' . $sub_orden_id . '&pagina=' . $paginas . '&cpres=' . $cpres .'&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>';
	echo '</td></tr>'."\n";
	echo '		<tr><td colspan="2"><hr></td></tr>'."\n";
	if($preaut==1) { echo '<input type="hidden" name="preaut" value="1" />'; }
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">
			<input type="hidden" name="sub_orden_id" value="' . $sub_orden_id . '" />
			<input type="hidden" name="previa_id" value="' . $previa_id . '" />
			<input type="hidden" name="desdecesta" value="1" />
		</td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>'."\n";
	echo '	</table>
	</form>'."\n";
}

?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
