<?php 
foreach($_POST as $k => $v){$$k=$v;}  // echo $k.' -> '.$v.'<br>';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';

include('parciales/funciones.php');
if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}
include('idiomas/' . $idioma . '/refacciones.php');

$_SESSION['selector']['grupo'] = $grupo;
$_SESSION['selector']['ref_presel'] = $ref_presel;
$_SESSION['selector']['ongest'] = $ongest;


/*  ----------------  obtener nombres de proveedores   ------------------- */
	
		$consulta = "SELECT prov_id, prov_razon_social, prov_nic, prov_env_ped, prov_representante, prov_email, prov_dde FROM " . $dbpfx . "proveedores WHERE prov_email != '' AND prov_activo = '1' ORDER BY prov_nic";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
		$num_provs = mysql_num_rows($arreglo);
   	$provs = array();
//   	$provs[0] = 'Sin Proveedor';
		while ($prov = mysql_fetch_array($arreglo)) {
			$provs[$prov['prov_id']] = array('nombre' => $prov['prov_razon_social'], 'nic' => $prov['prov_nic'], 'env' => $prov['prov_env_ped'], 'contacto' => $prov['prov_representante'], 'email' => $prov['prov_email'], 'dde' => $prov['prov_dde']);
		}
//		print_r($provs);

/*  ----------------  nombres de aseguradoras   ------------------- */
		$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_nic, prov_def FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		while ($aseg = mysql_fetch_array($arreglo)) {
			define('ASEGURADORA_' . $aseg['aseguradora_id'], $aseg['aseguradora_logo']);
			define('ASEGURADORA_NIC_' . $aseg['aseguradora_id'], $aseg['aseguradora_nic']);
			$asegnic[$aseg['aseguradora_id']] = $aseg['aseguradora_nic'];
			$autosurt[$aseg['aseguradora_id']] = $aseg['autosurtido'];
			$prov_def[$aseg['aseguradora_id']] = $aseg['prov_def'];
		}


if (($accion==='gestiona') || ($accion==='actualizar') || ($accion==='cotpedprod' || $accion==='gestprod') || ($accion==='insertar') || ($accion==='inspcpaq') || ($accion==='actpcpaq') || $accion==='guardacotiza') { 
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}

if($accion==="pendientes") {
	
	$funnum = 1115000;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	include('idiomas/' . $idioma . '/refacciones.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	$mensaje = '';
	$error = 'si'; $num_cols = 0;
	echo '	<table cellpadding="3" cellspacing="1" border="1">';
	echo '		<tr class="cabeza_tabla"><td colspan="7">Listado de productos ' . $llave . '</td></tr>
';
	echo '		<tr><td align="center">Cant</td><td>Nombre</td><td>Proveedor</td><td>Estado</td><td>F Promesa</td><td>Pendientes</td><td>Orden ID</td></tr>' . "\n";
//	echo $llave;
	if($llave == 'por solicitar') { $llave2 = "op_fecha_solicitada IS NULL";}
	elseif($llave == 'por recibir') { $llave2 = "op_cantidad > op_recibidos AND op_fecha_solicitada IS NOT NULL";}
	elseif($llave == 'pendientes por proveedor') { $llave2 = "op_prov_ok = '0' ORDER BY op_prov_id";}
	elseif(isset($prov_id) && $prov_id!='') { $llave2 = "op_prov_id = '$prov_id' AND op_prov_ok = '0'";}
	else { $llave2 = "op_prov_ok = '0'"; }
   $pregunta = "SELECT * FROM " . $dbpfx . "orden_productos WHERE op_tangible = '1' AND " . $llave2 ;
//   echo $pregunta;
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	$filas = mysql_num_rows($matriz);
	$j=0;
	$fondo = 'claro';
	while($prods = mysql_fetch_array($matriz)) {
	   $pregunta1 = "SELECT orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id =" . $prods['sub_orden_id'] ;
		$matriz1 = mysql_query($pregunta1) or die("ERROR: Fallo seleccion!");
		$orden = mysql_fetch_array($matriz1);
		$pend = $prods['op_cantidad'] - $prods['op_recibidos'];
		$estado = ($prods['op_prov_id']=='') ? 'por solicitar' : 'por recibir';
      $renglon = '		<tr class="' . $fondo . '">
      	<td align="center">' . $prods['op_cantidad'] . '</td><td>' . $prods['op_nombre'] . '</td><td align="center">' . $provs[$prods['op_prov_id']]['nic'] . '</td><td align="center">' . $estado . '</td><td align="center">' . $prods['op_fecha_promesa'] . '</td><td align="center">' . $pend . '</td><td align="center"><a href="ordenes.php?accion=consultar&orden_id=' . $orden['orden_id'] . '">' . $orden['orden_id'] . '</a></td>
      </tr>' . "\n";
      $contenido .= $renglon;
      echo $renglon;
		$j++;
		if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
	}
	echo '		<tr class="cabeza_tabla"><td colspan="7"><a href="javascript:window.print();"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="Imprimir Listado" title="Imprimir Listado"></a>';
	echo '</td></tr>
	</table>';
}

elseif($accion==="gestionar") {
	
	$funnum = 1115005;
	$funnum = 1115010;
	$funnum = 1115015;
	$funnum = 1115020;
	$funnum = 1115025;
	
/*	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
*/	
	$mensaje = '';
	$error = 'si'; $num_cols = 0;
		$pregunta = "SELECT sub_orden_id, sub_area, sub_estatus, sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$f1 = mysql_num_rows($matriz);
		$error = 'no';
		$pregunta2 = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_color, v.vehiculo_placas, v.vehiculo_serie, v.vehiculo_modelo, o.orden_estatus FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_vehiculo_id = v.vehiculo_id";
		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
		$orden = mysql_fetch_array($matriz2);
		$vehiculo = datosVehiculo($orden_id, $dbpfx); 
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="840">'."\n";
		echo '		<tr><td colspan="3" style="text-align:left;">' . $vehiculo['completo'] . '</td></tr>';
		echo '		<tr><td style="text-align:left;">'."\n";
	
//	echo $pregunta;
	if ($f1>0) {
//		if($pidepres == '1' && $ongest != '1') { $ongest = '1'; }
		if(!isset($grupo) || $grupo =='') { $grupo=1; } 
		if($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol13']=='1') {
			echo '	<form action="refacciones.php?accion=gestionar&orden_id=' . $orden_id . '" method="post" enctype="multipart/form-data" name="grupo">'."\n";
			echo '	Grupo: <select name="grupo" onchange="document.grupo.submit()"; />'."\n";
			echo '			<option value="0"'; if($grupo==0) {echo ' selected ';} echo '>Mano de Obra</option>'."\n";
			echo '			<option value="1"'; if($grupo==1) {echo ' selected ';} echo '>Refacciones</option>'."\n";
			echo '			<option value="2"'; if($grupo==2) {echo ' selected ';} echo '>Consumibles</option></select>'."\n";
			echo '	<input type="hidden" name="ref_presel" value="'.$ref_presel.'" /><input type="hidden" name="ongest" value="'.$ongest.'" />'."\n";
			echo '	</form>';
		}

		echo '			</td><td style="text-align:center;">'."\n";
		echo '<form action="refacciones.php?accion=gestionar&orden_id=' . $orden_id . '" method="post" enctype="multipart/form-data" name="partidas">Todas las partidas<input type="checkbox" name="ref_presel" value="1" ';
		if($ref_presel == '1') { echo 'checked="checked" '; }
		echo 'onchange="document.partidas.submit()"; /><input type="hidden" name="grupo" value="'.$grupo.'" /><input type="hidden" name="ongest" value="'.$ongest.'" /></form>';
		echo '			</td><td style="text-align:right;">'."\n";
		echo '<form action="refacciones.php?accion=gestionar&orden_id=' . $orden_id . '" method="post" enctype="multipart/form-data" name="oculta">Oculta partes no gestionadas<input type="checkbox" name="ongest" value="1" ';
		if($ongest == '1') { echo 'checked="checked" '; }
		echo 'onchange="document.oculta.submit()"; /><input type="hidden" name="ref_presel" value="'.$ref_presel.'" /><input type="hidden" name="grupo" value="'.$grupo.'" /></form>';
		echo '</td></tr></table>'."\n";		

		if($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol07']=='1') {
			echo '	<form action="refacciones.php?accion=gestiona" method="post" enctype="multipart/form-data">'."\n";
		}
		if($soloautorizadas == '1') { $ancho = '70%'; } else { $ancho = '100%';}
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="' . $ancho . '">'."\n";
		if (isset($_SESSION['ref']['mensaje'])) {
			echo '		<tr><td colspan="2"><span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span></td></tr>';
			unset($_SESSION['ref']['mensaje']);
		}
		echo $_SESSION['proceso']['etiqueta'];
		
		echo '		<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div></td></tr>'."\n";
		echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left;">Gestionar los productos requeridos para ejecutar la Orden de Trabajo: ' . $orden_id . '</td></tr>'."\n";

		$jj=0;// echo $_SESSION['rol08'];
		$j=0;
//		$f_promesa = dia_habil ($provdd);
		$tot_costo=0;
		$tot_precio =0;
		$tot_auth = 0;
      while ($sub = mysql_fetch_array($matriz)) {
      	$pregunta1 = "SELECT o.op_id, o.prod_id, op_item, op_item_seg, o.op_cantidad, o.op_nombre, o.op_codigo, o.sub_orden_id, o.op_estructural, o.op_tangible, o.op_surtidos, o.op_reservado, o.op_pedido, o.op_fecha_promesa, o.op_ok, o.op_costo, o.op_precio, o.op_precio_original, o.op_precio_revisado, o.op_doc_id, o.op_autosurtido, o.op_pres";
//      	if($sub['sub_reporte'] == '0') { $pregunta1 .= ", p.prod_cantidad_existente, p.prod_cantidad_disponible";	}
      	$pregunta1 .= " FROM " . $dbpfx . "orden_productos o";
//      	if($sub['sub_reporte'] == '0') {$pregunta1 .= ", " . $dbpfx . "productos p ";}
			$pregunta1 .= " WHERE o.sub_orden_id = '" . $sub['sub_orden_id'] . "' AND o.op_tangible = '$grupo'";
      	if($soloautorizadas == '1') { $pregunta1 .= " AND o.op_pres IS NULL "; }
//      	echo $pregunta1;
			$pregunta1 .= " ORDER BY o.op_nombre";
			$matriz1 = mysql_query($pregunta1) or die("ERROR: Fallo seleccion!");
			$f2 = mysql_num_rows($matriz1);
			if($f2 > 0) {
				$jj++;
				while($prods = mysql_fetch_array($matriz1)) {
//					echo 'Aseg: ' . $sub['sub_aseguradora'] . ' Autosurt: ' . $prods['op_autosurtido'] . '<br>';
					if((($autosurt[$sub['sub_aseguradora']] == '1' && $prods['op_autosurtido'] != '1') || $sub['sub_aseguradora'] == '0' ) && $prods['op_pres'] != '1') {
						$tot_auth = $tot_auth + ($prods['op_cantidad'] * $prods['op_precio']);
					}
					if($prods['op_pres'] == '1') { $tr = 'pre'; $fondo = $tr.$fondo; } else { $tr = 'aut'; $fondo = $tr.$fondo; } 
					$ref[$tr][$j] = '';
					$preg7 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $prods['op_item_seg'] . "'";
					$matr7 = mysql_query($preg7) or die("ERROR: Fallo selección de orden productos");
					$itseg = mysql_fetch_array($matr7);
					if(($prods['op_reservado'] < $prods['op_cantidad'] || $prods['op_tangible'] == '0' ) && $prods['op_pedido'] < '1' && is_null($prods['op_item_seg'])) {
						$muestra = 1;
						if($ongest == '1') { $muestra = 0; } // Si es 0, no mostrará las refacciones no gestionados
						if($pidepres == '1' && $prods['op_pres'] == '1') { $muestra = 1; } // Pero si se pide desde presupuesto, entonces se forza mostrarlo
						if($muestra == '1') {  // Si es 0, no mostrará los refacciones no gestionados de valuación autorizada 
//							echo '				<tr class="' . $fondo . '"><td>'."\n";
							
							$ref[$tr][$j] = '					<table border="1" cellpadding="2" cellspacing="0" width="100%">
						<tr><td style="font-weight:bold; text-align:left;"><a name="' . $prods['op_id'] . '"></a>' . $prods['op_cantidad'] . ' ' . $prods['op_nombre'];
							if($ajustacodigo == 1) {
								$ref[$tr][$j] .= '<br>Código: <input type="text" name="op_codigo[' . $j . ']" value="' . $prods['op_codigo'] . '" size="12" />';
							}
							$ref[$tr][$j] .= '<input type="hidden" name="op_id[' . $j . ']" value="' . $prods['op_id'] . '" />';
							$ref[$tr][$j] .= '<input type="hidden" name="prod_id[' . $j . ']" value="' . $prods['prod_id'] . '" /></td>';

							if($cotizar != '1' && $prods['op_pedido'] < 1 && $prods['op_reservado'] < $prods['op_cantidad'] && $_SESSION['rol08']=='1') {
								$ref[$tr][$j] .= '<td style="text-align:right;">Costo: <input type="text" name="op_costo[' . $j . ']" value="' . $prods['op_costo'] . '" size="4" /></td>';
							} elseif($_SESSION['rol02']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol12']=='1') {
								$ref[$tr][$j] .= '<td>Costo: $' . number_format($prods['op_costo'],2) . '</td>';
							} else {
								$ref[$tr][$j] .= '<td></td>';
							}
							$ref[$tr][$j] .= '<td style="text-align:right;">Tarea: ' . $prods['sub_orden_id'] . ' Item #' . $prods['op_item'] . '</td></tr>'."\n";
							$preg4 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE doc_id = '" . $prods['op_doc_id'] . "'";
//							echo $preg4;
							$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección foto refacción!");
							$resu4 = mysql_fetch_array($matr4);
							$ref[$tr][$j] .= '						<tr>';
							if($_SESSION['rol08']=='1') {
								if($prods['prod_cantidad_disponible'] >= $prods['op_cantidad'] && $prods['op_cantidad'] > '0') {
									$ref[$tr][$j] .= '<td colspan="3">Disponible en Almacén. ¿Reservar? <input type="checkbox" name="reservar[' . $j . ']" value="' . $prods['op_cantidad'] . '" /><input type="hidden" name="nvodisp[' . $j . ']" value="'; $ref[$tr][$j] .= $prods['prod_cantidad_disponible'] - $prods['op_cantidad']; $ref[$tr][$j] .= '" /></td>'."\n";
								} else {
									if($cotizar == '1') {
										$preg3 = "SELECT * FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $prods['op_id'] . "'";
										$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección Prod-Prov!");
										$resu3 = mysql_num_rows($matr3);
										if($resu3 > 0) {
											$ref[$tr][$j] .= '<td colspan="3"><table cellpadding="2" cellspacing="0" border="0">'."\n";
											$ref[$tr][$j] .= '							<tr><td style="text-align:left;">Proveedor</td><td style="text-align:left;">Fecha Cotizado</td><td style="text-align:left;">Costo</td><td style="text-align:left;">Días<br>Crédito</td><td style="text-align:left;">Días de<br>Entrega</td><td style="text-align:center;">Agregar a<br>Pedido</td></tr>'."\n";
											while($prov3 = mysql_fetch_array($matr3)) {
												$f_promesa = dia_habil ($prov3['dias_entrega']);
												$ref[$tr][$j] .= '							<tr><td>' . $provs[$prov3['prod_prov_id']]['nic'] . '</td><td>' . date('d-m-Y', strtotime($prov3['fecha_cotizado'])) . '</td><td>$ ' . number_format($prov3['prod_costo'],2) . '</td><td>' . $prov3['dias_credito'] . '</td><td>' . $prov3['dias_entrega'] . '</td><td style="width:60px;text-align:center;">';
												$ref[$tr][$j] .= '<input type="radio" name="sel_prov[' . $j . ']" value="' . $prov3['prod_prov_id'] . '" /><input type="hidden" name="op_costo[' . $j . '][' . $prov3['prod_prov_id'] . ']" value="' . $prov3['prod_costo'] . '" />';
												$ref[$tr][$j] .= '</td></tr>'."\n";
											}
											$ref[$tr][$j] .= '							<tr><td colspan="2" style="text-align:left;">Seleccionar para pedir cotizaciones adicionales: <input type="checkbox" name="selecionado[' . $j . ']" value="1" /></td><td colspan="2"><a href="refacciones.php?accion=cargacotiza&op_id=' . $prods['op_id'] . '&orden_id=' . $orden_id . '">Cargar datos de cotizaciones</a></td><td colspan="2" style="text-align:left;">Foto: <a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a></td></tr>'."\n";
											$ref[$tr][$j] .= '						</table>'."\n";
										} else {
											if($tr == 'aut' && $soloautorizadas != '1') {
												$ref[$tr][$j] .= '<td style="text-align:left;">';
												$ref[$tr][$j] .= '<input type="text" name="asoc_item[' . $j . ']" size="2"> Asociar con Item de Presupuesto';
												$ref[$tr][$j] .= '</td><td colspan="2">';
											} else {
												$ref[$tr][$j] .= '<td colspan="3">';
											}
											$ref[$tr][$j] .= 'No hay cotizaciones disponibles para este producto<br><a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a> Solicitar cotización a proveedor seleccionado abajo: <input type="checkbox" name="selecionado[' . $j . ']" value="1"';
										if($ref_presel == '1') { $ref[$tr][$j] .= ' checked="checked"'; }
										$ref[$tr][$j] .= ' />';
										$ref[$tr][$j] .= '<br><input type="file" name="fotoref[' . $j . ']" />'."\n";
										}
										$ref[$tr][$j] .= '</td>'."\n";
									} else {
										$ref[$tr][$j] .= '<td colspan="3" style="text-align:left;">';
										if($prods['op_item_seg'] != '') {
												$ref[$tr][$j] .= 'Este producto fue gestionado con el <a href="pedidos.php?accion=consultar&pedido=' . $itseg['op_pedido'] . '">Pedido ' . $itseg['op_pedido'] . '</a> del item ' . $itseg['op_item'] . ' de los productos presupuestados';
												$ref[$tr][$j] .= '<br>¿Desea remover la asociación con el item ' . $itseg['op_item'] . '? <input type="checkbox" name="rem_item[' . $j . ']" value="1" />'."\n";
												$ref[$tr][$j] .= '</td>'."\n";
										} else {
											if($tr == 'aut' && $soloautorizadas != '1') {
												$ref[$tr][$j] .= '<input type="text" name="asoc_item[' . $j . ']" size="2"> Asociar con Item de Presupuesto o ';
											}
											$ref[$tr][$j] .= 'Seleccionar para proveedor: <input type="checkbox" name="selecionado[' . $j . ']" value="1"';
											if($ref_presel == '1') { $ref[$tr][$j] .= ' checked="checked"'; }
											$ref[$tr][$j] .= ' /></td></tr>'."\n";
											$ref[$tr][$j] .= '						<tr><td colspan="3"><a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a> <input type="file" name="fotoref[' . $j . ']" /></td>'."\n";
										}
									}
								}
							} elseif(($_SESSION['rol02'] ==1 || $_SESSION['rol12'] ==1) && $tr == 'aut' && $prods['op_item_seg'] != '') {
								$margen = round(((($prods['op_precio'] - $prods['op_costo'])/$prods['op_precio']) * 100), 2);
								if($prods['op_precio_revisado'] == '0') {
									$ref[$tr][$j] .= '<td>Valuación original: ' . money_format('%n', $prods['op_precio']) . '<br>Precio Revisado:';
									if($prods['op_autosurtido'] == '1') {
										$ref[$tr][$j] .= money_format('%n', $prods['op_precio']);
									} else {
										$ref[$tr][$j] .= ' <input type="text" name="precio[' . $j . ']" size="6" />';
									}
									$ref[$tr][$j] .= ALARMA_1 . '<br>Utilidad:' . $margen . '%</td>'."\n";
								} elseif($prods['op_precio_revisado'] == '2') {
									$ref[$tr][$j] .= '<td>Valuación original: ' . money_format('%n', $prods['op_precio_original']) . '<br>Precio Revisado:';
									if($prods['op_autosurtido'] == '1') {
										$ref[$tr][$j] .= money_format('%n', $prods['op_precio']);
									} else {
										$ref[$tr][$j] .= '<input type="text" name="precio[' . $j . ']" size="6" value="' . money_format('%n', $prods['op_precio']) . '" />';
									}
									$ref[$tr][$j] .= ALARMA_2 . '<br>Utilidad:' . $margen . '%</td>'."\n";
								} else {
									$ref[$tr][$j] .= '<td>Valuación original: ' . money_format('%n', $prods['op_precio_original']) . '<br>Precio Revisado:';
									if($prods['op_autosurtido'] == '1') {
										$ref[$tr][$j] .= money_format('%n', $prods['op_precio']);
									} else {
										$ref[$tr][$j] .= '<input type="text" name="precio[' . $j . ']" size="6" value="' . money_format('%n', $prods['op_precio']) . '" />';
									}
									$ref[$tr][$j] .= ALARMA_0 . '<br>Utilidad:' . $margen . '%</td>'."\n";
								}
								if($prods['op_autosurtido'] == '2' || $prods['op_autosurtido'] == '3') {
									$tot_costo = $tot_costo + ($prods['op_costo'] * $prods['op_cantidad']);
									$tot_precio = $tot_precio + ($prods['op_precio'] * $prods['op_cantidad']);
								}
								$ref[$tr][$j] .= '<td colspan="2" style="vertical-align:top;">Refacción Gestionada con el Item ' . $itseg['op_item'] . ' de presupuestados.';
								$ref[$tr][$j] .= '<br>Foto: <a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a></td>'."\n";
							} elseif($_SESSION['rol05']=='1') {
								if($prods['op_item_seg'] != '') {
									$ref[$tr][$j] .= '<td colspan="3">Refacción Gestionada con el Item ' . $itseg['op_item'] . ' de presupuestados.';
								} else {
									$ref[$tr][$j] .= '<td colspan="3">Refacción no Gestionada aún.';
								}
								$ref[$tr][$j] .= '<br>Foto: <a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a> <input type="file" name="fotoref[' . $j . ']" size="30" /></td>'."\n";
							} elseif($_SESSION['rol07']=='1') {
								if($prods['op_item_seg'] != '') {
									$ref[$tr][$j] .= '<td colspan="3">Refacción Gestionada con el Item ' . $itseg['op_item'] . ' de presupuestados.';
								} else {
									$ref[$tr][$j] .= '<td colspan="3">Refacción no Gestionada aún.';
								}
								$ref[$tr][$j] .= '<br>Foto: <a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a> <input type="file" name="fotoref[' . $j . ']" size="30" /></td>'."\n";
							} else {
								if($prods['op_item_seg'] != '') {
									$ref[$tr][$j] .= '<td colspan="3">Refacción Gestionada con el Item ' . $itseg['op_item'] . ' de presupuestados.';
								} else {
									$ref[$tr][$j] .= '<td colspan="3">Refacción no Gestionada aún.';
								}
								$ref[$tr][$j] .= '<br>Foto: <a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a></td>'."\n";
							}
							$ref[$tr][$j] .= '						</tr>'."\n";
							$ref[$tr][$j] .= '					</table>' . "\n";
						}
					} elseif($prods['op_pedido'] > 0 || !is_null($prods['op_item_seg'])) {
//						echo '				<tr class="' . $fondo . '"><td>'."\n";
						$ref[$tr][$j] .= '					<table border="1" cellpadding="2" cellspacing="0" width="100%">
						<tr><td style="font-weight:bold; text-align:left;">' . $prods['op_cantidad'] . ' ' . $prods['op_nombre'];
						$ref[$tr][$j] .= '<input type="hidden" name="op_id[' . $j . ']" value="' . $prods['op_id'] . '" />';
						$ref[$tr][$j] .= '<input type="hidden" name="prod_id[' . $j . ']" value="' . $prods['prod_id'] . '" /></td>'."\n";
/*						if($prods['op_pedido'] < 1 && $prods['op_reservado'] < $prods['op_cantidad'] && $_SESSION['rol08']=='1') {
							$ref[$tr][$j] .= '				<td><input type="text" name="op_costo[' . $j . ']" size="2" value="' . $prods['op_costo'] . '" size="12" /></td>'."\n";
						} 
*/
						if($_SESSION['rol08'] ==1 || $_SESSION['rol02'] ==1 || $_SESSION['rol12'] ==1 || $_SESSION['rol13'] ==1 || $_SESSION['rol05'] ==1) {
							$ref[$tr][$j] .= '<td style="text-align:right;"><input type="hidden" name="op_costo[' . $j . ']" value="' . $prods['op_costo'] . '"/>$' . number_format($prods['op_costo'],2) . '</td>'."\n";
						} else {
								$ref[$tr][$j] .= '<td></td>';
						}
						$ref[$tr][$j] .= '<td style="text-align:right;">Tarea: ' . $prods['sub_orden_id'] . ' Item #' . $prods['op_item'] . '</td><tr>'."\n";
						$ref[$tr][$j] .= '						<tr>';
						if(($_SESSION['rol02'] ==1 || $_SESSION['rol12'] ==1) && $tr == 'aut') {
							$margen = round(((($prods['op_precio'] - $prods['op_costo'])/$prods['op_precio']) * 100), 2);
							if($prods['op_precio_revisado'] == '0') {
								$ref[$tr][$j] .= '<td>Valuación original: ' . money_format('%n', $prods['op_precio']) . '<br>Precio Revisado:';
								if($prods['op_autosurtido'] == '1') {
									$ref[$tr][$j] .= money_format('%n', $prods['op_precio']);
								} else {
									$ref[$tr][$j] .= ' <input type="text" name="precio[' . $j . ']" size="6" />';
								}
								$ref[$tr][$j] .= ALARMA_1 . '<br>Utilidad:' . $margen . '%</td>'."\n";
							} elseif($prods['op_precio_revisado'] == '2') {
								$ref[$tr][$j] .= '<td>Valuación original: ' . money_format('%n', $prods['op_precio_original']) . '<br>Precio Revisado:';
								if($prods['op_autosurtido'] == '1') {
									$ref[$tr][$j] .= money_format('%n', $prods['op_precio']);
								} else {
									$ref[$tr][$j] .= '<input type="text" name="precio[' . $j . ']" size="6" value="' . money_format('%n', $prods['op_precio']) . '" />';
								}
								$ref[$tr][$j] .= ALARMA_2 . '<br>Utilidad:' . $margen . '%</td>'."\n";
							} else {
								$ref[$tr][$j] .= '<td>Valuación original: ' . money_format('%n', $prods['op_precio_original']) . '<br>Precio Revisado:';
								if($prods['op_autosurtido'] == '1') {
									$ref[$tr][$j] .= money_format('%n', $prods['op_precio']);
								} else {
									$ref[$tr][$j] .= '<input type="text" name="precio[' . $j . ']" size="6" value="' . money_format('%n', $prods['op_precio']) . '" />';
								}
								$ref[$tr][$j] .= ALARMA_0 . '<br>Utilidad:' . $margen . '%</td>'."\n";
							}
							if(($prods['op_autosurtido'] == '2' || $prods['op_autosurtido'] == '3') && $tr == 'aut') {
								$tot_costo = $tot_costo + ($prods['op_costo'] * $prods['op_cantidad']);
								$tot_precio = $tot_precio + ($prods['op_precio'] * $prods['op_cantidad']);
							}
							$ref[$tr][$j] .= '							<td colspan="2">';
						} else {
							$ref[$tr][$j] .= '							<td colspan="3">';
						}
						if($prods['op_item_seg'] != '') {
							$ref[$tr][$j] .= 'Este producto fue gestionado con el ';
							if(($_SESSION['rol02'] ==1 || $_SESSION['rol08'] ==1) && $tr == 'aut') {
								$ref[$tr][$j] .= '<a href="pedidos.php?accion=consultar&pedido=' . $itseg['op_pedido'] . '">Pedido: ' . $itseg['op_pedido'] . '</a> del ';
							}
							$ref[$tr][$j] .= 'item ' . $itseg['op_item'] . ' de los productos presupuestados';
							if(($_SESSION['rol02'] ==1 || $_SESSION['rol08'] ==1) && $tr == 'aut') {
								$ref[$tr][$j] .= '<br>¿Desea remover la asociación con el item ' . $itseg['op_item'] . '? <input type="checkbox" name="rem_item[' . $j . ']" value="1" />'."\n";
							}
						} else {
							$preg4 = "SELECT prov_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $prods['op_pedido'] . "'";
							$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de proveedor!");
							$resu4 = mysql_fetch_array($matr4);
							if($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol13']=='1') { 
								$ref[$tr][$j] .= '<a href="pedidos.php?accion=consultar&pedido=' . $prods['op_pedido'] . '">Pedido: ' . $prods['op_pedido'] . '</a> a '."\n"; 
							}
							$ref[$tr][$j] .= $provs[$resu4['prov_id']]['nic'] . '<br>';
							if($prods['op_ok'] == '1') {
								$ref[$tr][$j] .= 'Recibido OK el: ' . $prods['op_fecha_promesa']; 
							} else { 
								$ref[$tr][$j] .= '<span style="color:red;">Pendiente NO recibido</span> con promesa de entrega el: ' . $prods['op_fecha_promesa']; 
							}
						}
						$ref[$tr][$j] .= '</td>'."\n";
						$ref[$tr][$j] .= '					</tr>'."\n";
						$ref[$tr][$j] .= '				</table>' . "\n";
					} else {
//						$ref[$tr][$j] .= '				<tr class="' . $fondo . '"><td>'."\n";
						$ref[$tr][$j] .= '					<table border="1" cellpadding="2" cellspacing="0" width="100%">
						<tr><td style="font-weight:bold; text-align:left;">' . $prods['op_cantidad'] . ' ' . $prods['op_nombre'] . "\n";
						$ref[$tr][$j] .= '						<input type="hidden" name="op_id[' . $j . ']" value="' . $prods['op_id'] . '" />';
						$ref[$tr][$j] .= '						<input type="hidden" name="prod_id[' . $j . ']" value="' . $prods['prod_id'] . '" /></td>';
						if($prods['op_pedido'] < 1 && $prods['op_reservado'] < $prods['op_cantidad'] && $_SESSION['rol08']=='1') {
							$ref[$tr][$j] .= '<td><input type="text" name="op_costo[' . $j . ']" size="2" value="' . $prods['op_costo'] . '"/></td>';
						} 
						$ref[$tr][$j] .= '<td>Item #' . $prods['op_item'] . '</td></tr>'."\n";
						$ref[$tr][$j] .= '						<tr><td colspan="2">Reservados en Almacén</td></tr>'."\n";
						$ref[$tr][$j] .= '					</table>' . "\n";
					}
					$j++;
					if($fondo == 'preclaro' || $fondo == 'autclaro') {$fondo = 'obscuro';} else  {$fondo = 'claro';}
				}
			}
		}
/*		echo 'presupestados: <br>';
		print_r($ref['pre']);
		echo 'autorizados: <br>';
		print_r($ref['aut']);
		echo '------------- <br>';
*/		
		$fpre = count($ref['pre']); // echo 'presupestados: ' . $fpre . '<br>';
		$faut = count($ref['aut']); // echo 'autorizados: ' . $faut . '<br>';
		echo '		<tr>';
		if($soloautorizadas != '1') {
			echo '<td class="preobscuro" style="text-align:center; font-size:1.2em; font-weight:bold;">Presupuestados</td>';
		} 
		echo '<td colspan="1" class="autobscuro" style="text-align:center; font-size:1.2em; font-weight:bold;">Autorizados</td>';
		echo '</tr>'."\n";
		$fondo = 'claro';
		if($fpre > $faut) { $filas = $fpre; } else { $filas = $faut; }
		echo '		<tr>'."\n";
		if($soloautorizadas != '1') {
			echo '			<td class="pre' . $fondo . '" style="text-align:center; vertical-align:top;">'."\n";
			if(current($ref['pre'])) { echo current($ref['pre']); }
			echo '			</td>'."\n";
		}
		echo '			<td class="aut' . $fondo . '" style="text-align:center; vertical-align:top;">'."\n";
		if(current($ref['aut'])) { echo current($ref['aut']); }
		echo '			</td></tr>'."\n";
		for($i=1; $i < $filas; $i++){
			if($fondo == 'claro') {$fondo = 'obscuro';} else  {$fondo = 'claro';}
			echo '		<tr>'."\n";
			if($soloautorizadas != '1') {
				echo '			<td class="pre' . $fondo . '" style="text-align:center; vertical-align:top;">'."\n";
				if(next($ref['pre'])) { echo current($ref['pre']); }
				echo '			</td>'."\n";
			}
			echo '			<td class="aut' . $fondo . '" style="text-align:center; vertical-align:top;">'."\n";
			if(next($ref['aut'])) { echo current($ref['aut']); }
			echo '			</td></tr>'."\n";
		}
		


//		echo '			</table></td></tr>'."\n";



		if($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol07']=='1') {
			if($_SESSION['rol02']=='1' || $_SESSION['rol12']=='1') {
				$tot_margen = round(((($tot_precio - $tot_costo) / $tot_precio) * 100), 2);
				echo '		<tr class="cabeza_tabla"><td colspan="2">Costo de Gestionadas: $' . number_format($tot_costo, 2) . '. Precio de Gestionadas: $' . number_format($tot_precio, 2) . '. Total Refacciones Autorizadas: $' . number_format($tot_auth, 2) . '. Utilidad: ' . $tot_margen . '%</td></tr>'."\n";
			}
			echo '		<tr><td colspan="2">'."\n";
			echo 'Tipo de Solicitud: <select name="tipo_pedido" /><option value="">Seleccionar...</option>'."\n";
			if($_SESSION['rol08']=='1' && (($orden['orden_estatus'] >= '2' && $orden['orden_estatus'] <= '16') || $orden['orden_estatus'] == '99')) {
				echo '			<option value="1">' . TIPO_PEDIDO_1 . '</option>'."\n";
				echo '			<option value="2">' . TIPO_PEDIDO_2 . '</option>'."\n";
				echo '			<option value="3">' . TIPO_PEDIDO_3 . '</option>'."\n";
			}
			
			if($_SESSION['rol08']=='1' && $preaut == '1' && (($orden['orden_estatus'] >= '24' && $orden['orden_estatus'] <= '29') || $orden['orden_estatus'] == '20')) {
				echo '			<option value="1">' . TIPO_PEDIDO_1 . '</option>'."\n";
				echo '			<option value="2">' . TIPO_PEDIDO_2 . '</option>'."\n";
				echo '			<option value="3">' . TIPO_PEDIDO_3 . '</option>'."\n";
			}
			
			if($_SESSION['rol08']=='1') {
				echo '			<option value="10" selected>' . TIPO_PEDIDO_10 . '</option>'."\n";
				if($cotizataller == '1') { echo '			<option value="11">' . TIPO_PEDIDO_11 . '</option>'."\n"; }
				echo '			<option value="6" selected>' . TIPO_PEDIDO_6 . '</option>'."\n";
				echo '			<option value="5">' . TIPO_PEDIDO_5 . '</option>'."\n";
			}
			if($_SESSION['rol02']=='1' || $_SESSION['rol12']=='1') {
				echo '			<option value="4" selected>' . TIPO_PEDIDO_4 . '</option>'."\n";
			}
			if($_SESSION['rol05']=='1' || $_SESSION['rol07']=='1') {
				echo '			<option value="5" selected>' . TIPO_PEDIDO_5 . '</option>'."\n";
			}
			echo '			</select>&nbsp;'."\n";
			if($_SESSION['rol08']=='1') {
				echo 'Descuento Gloabal a Precio de Compra en seleccionadas: <input type="text" name="descuento" value="" size="4" />';
				echo '			Proveedor: <select name="prov_selec[]" multiple="multiple" size="4"/>'."\n";
				foreach($provs as $k => $v) {
					echo '			<option value="' . $k . ':' .$v['dde'] . '">' . $v['nic'] . '</option>'."\n";
				}
				echo '		</select>';
			}
			if($_SESSION['rol12']=='1' && $grupo == 1) {
				echo 'Descuento Global en Precio de Venta de Refacciones: <input type="text" name="descuento" value="" size="4" />';
			}
			echo '</td></tr>'."\n";
		}
		
			echo '		<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a> &nbsp; <a href="refacciones.php?accion=imprimelista&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="Imprimir Listado" title="Imprimir Listado"></a></div></td></tr>'."\n";
		if($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol07']=='1') {
			echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;<input type="hidden" name="orden_id" value="' . $orden_id . '" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Limpiar selecciones" /></td></tr>'."\n";
		}
		echo '	</table>'."\n";
		if($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol07']=='1') {
	echo '	</form>';
		}
	}
}

elseif($accion==="gestiona") {
	
	$funnum = 1115005;
	$funnum = 1115010;
	$funnum = 1115015;
	$funnum = 1115020;
	$funnum = 1115025;

	if ($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol07']=='1') {
		$mensaje='Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	unset($_SESSION['ref']);
	$_SESSION['ref'] = array();
	$_SESSION['ref']['mensaje']='';
	$mensaje = '';
	$error = 'no';
	
// --------------- Asociar items --------------------

	$it = array();
	foreach($op_id as $i => $v) {
		$pregi = "SELECT op_item FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $v . "'";
		$matri = mysql_query($pregi) or die("ERROR: Fallo selección de productos!");
		$item = mysql_fetch_array($matri);
		$it[$item['op_item']] = $v; 
	}
//	print_r($it);
	foreach($op_id as $i => $v) {
		$asoc_item[$i] = limpiarNumero($asoc_item[$i]);
		if($asoc_item[$i] > 0) {
			$pregi = "SELECT * FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $it[$asoc_item[$i]] . "'";
//			echo '<br>' .$pregi;
			$matri = mysql_query($pregi) or die("ERROR: Fallo selección de productos!");
			$ii = mysql_fetch_array($matri);
			if($ii['op_pedido'] > 0) {
				$preg6 = "UPDATE " . $dbpfx . "orden_productos SET op_item_seg = '" . $it[$asoc_item[$i]] . "' WHERE op_id = '" . $v ."'";
//				echo '<br>' .$preg6;
				$matr6 = mysql_query($preg6) or die("ERROR: Fallo actualización de productos!");
				if($ii['op_ok'] == 1) {
					$preg6 = "UPDATE " . $dbpfx . "orden_productos SET op_recibidos = op_cantidad, op_ok = 1, op_costo = 0, op_subtotal = 0, op_fecha_promesa = '"  . $ii['op_fecha_promesa'] . "', op_autosurtido = '" . $ii['op_autosurtido'] . "' WHERE op_id = '" . $v ."'";
					$matr6 = mysql_query($preg6) or die("ERROR: Fallo actualización de productos!");
					$preg7 = "SELECT op_id, op_ok, op_estructural, op_pedido, op_pres FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '".$ii['sub_orden_id']."' AND op_tangible = '1'";
					if($sin_autorizadas == 1) {
						 $preg7 .= " AND op_autosurtido >='1' AND op_autosurtido <='3'";
					}
					$matr7 = mysql_query($preg7) or die("ERROR: Fallo selección de orden_productos 3!");
					$estruc = 1; $completo = 1; 
					while($opp = mysql_fetch_array($matr7)) {
						if($opp['op_ok'] == '0') {
							if($sin_autorizadas != 1) {
								if(($opp['op_pres'] == 1 && $opp['op_pedido'] > 0) || is_null($opp['op_pres'])) {
									$completo = 0;
									$totref = 0;
									if($opp['op_estructural'] == '1') {
										$estruc = 0;
									}
								}
							} else {
								$completo = 0;
								$totref = 0;
								if($op['op_estructural'] == '1') {
									$estruc = 0;
								}
							}
						}
					}
					$parametros = "sub_orden_id = '" . $ii['sub_orden_id'] ."'";
					if($completo == 1) {
						$sql_data_array = array('sub_refacciones_recibidas' => '0');
						if($ii['sub_estatus'] == '105') { $sql_data_array['sub_estatus'] = '106';	}
					} elseif($estruc == 1) {
						$sql_data_array = array('sub_refacciones_recibidas' => '1');
					} else {
						$sql_data_array = array('sub_refacciones_recibidas' => '2');
					}
					if($ii['sub_refacciones_recibidas'] != $sql_data_array['sub_refacciones_recibidas']) {
						ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
					}
					actualiza_orden($orden_id, $dbpfx);
				}
				$selecionado[$i] = 0;
			} else {
				$mensaje .= 'El Item ' . $asoc_item[$i] . ' no tiene pedido y debe tenerlo para poder asociarlo.<br>'; $error = 'si';
			}
		}
		if($rem_item[$i] == 1) {
			$preg6 = "UPDATE " . $dbpfx . "orden_productos SET op_item_seg = NULL, op_fecha_promesa = NULL, op_recibidos = 0, op_ok = 0, op_autosurtido = 0 WHERE op_id = '" . $v ."'";
			$matr6 = mysql_query($preg6) or die("ERROR: Fallo actualización de productos!");
			$pregi = "SELECT * FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $v . "'";
//			echo '<br>' .$pregi;
			$matri = mysql_query($pregi) or die("ERROR: Fallo selección de productos!");
			$ii = mysql_fetch_array($matri);
			$preg7 = "SELECT op_id, op_ok, op_estructural, op_pedido, op_pres FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '".$ii['sub_orden_id']."' AND op_tangible = '1'";
			if($sin_autorizadas == 1) {
				$preg7 .= " AND op_autosurtido >='1' AND op_autosurtido <='3'";
			} else {
				$preg7 .= " AND op_item_seg IS NULL";
			}
			$matr7 = mysql_query($preg7) or die("ERROR: Fallo selección de orden_productos 3!");
			$estruc = 1; $completo = 1; 
			while($opp = mysql_fetch_array($matr7)) {
				if($opp['op_ok'] == '0') {
					if($sin_autorizadas != 1) {
						if(($opp['op_pres'] == 1 && $opp['op_pedido'] > 0) || is_null($opp['op_pres'])) {
							$completo = 0;
							$totref = 0;
							if($opp['op_estructural'] == '1') {
								$estruc = 0;
							}
						}
					} else {
						$completo = 0;
						$totref = 0;
						if($op['op_estructural'] == '1') {
							$estruc = 0;
						}
					}
				}
			}
			$parametros = "sub_orden_id = '" . $ii['sub_orden_id'] ."'";
			if($completo == 1) {
				$sql_data_array = array('sub_refacciones_recibidas' => '0');
				if($ii['sub_estatus'] == '105') { $sql_data_array['sub_estatus'] = '106';	}
			} elseif($estruc == 1) {
				$sql_data_array = array('sub_refacciones_recibidas' => '1');
			} else {
				$sql_data_array = array('sub_refacciones_recibidas' => '2');
			}
			if($ii['sub_refacciones_recibidas'] != $sql_data_array['sub_refacciones_recibidas']) {
				ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
			}
			actualiza_orden($orden_id, $dbpfx);
		}
	}

// --------------------------------------------------	
	
	$descuento = (limpiarNumero($descuento) / 100 );
	if($tipo_pedido >= '1' && $tipo_pedido <= '3') {
		if($cotizar == '1') { 
			$cuantos_prov = count($sel_prov); 
			if($cuantos_prov < 1) { $mensaje .= 'Seleccione al menos una cotización para crear pedido.<br>'; $error = 'si'; }
			if($tipo_pedido > 1) { 
				foreach($op_id as $i => $v) {
					if(isset($sel_prov[$i]) && ($op_costo[$i][$sel_prov[$i]] == '0' || $op_costo[$i][$sel_prov[$i]] == '')) {
						$mensaje .= 'En pedidos con cargo al Taller el costo del OpID ' . $op_id[$i] . ' debe ser mayor a CERO.<br>'; $error = 'si';
					}
				}
			}
		} else { 
			$cuantos_prov = count($prov_selec);
			if($cuantos_prov > 1) { $mensaje .= 'Seleccione SOLO UN proveedor para pedido.<br>'; $error = 'si'; }
			if($cuantos_prov < 1) { $mensaje .= 'Seleccione al menos UN proveedor para pedido.<br>'; $error = 'si'; } 
		}
		
		
	}
	if($tipo_pedido < 1) { $mensaje .= 'Seleccione el tipo de solicitud.<br>'; $error = 'si'; }
	if(!is_array($selecionado) && ($tipo_pedido < 4 || $tipo_pedido == 10) && $cotizar != '1') { $mensaje .= 'Seleccione al menos una refacción, consumible o mano de obra.<br>'; $error = 'si'; }
	$j=0;

	if($error == 'no') {
		if($orden_id != '') { 	$vehiculo = datosVehiculo($orden_id, $dbpfx);}
		$j=0;
		if($tipo_pedido < 4) {
				foreach($op_id as $i => $v) {
/* ------------ Reservados --------------------*/
					if($reservar[$i] > 0) {
//				echo $reservar[$i] . ' ' . $i . '<br>';
						$param = "op_id = '".$op_id[$i]."'";
//				echo $param;
						$sql_data_array= array('op_reservado' => $reservar[$i], 'op_autosurtido' => $tipo_pedido);
						ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $param);
						$param = "prod_id = '".$prod_id[$i]."'";
						$sql_data_array= array('prod_cantidad_disponible' => $nvodisp[$i]);
						ejecutar_db($dbpfx . 'productos', $sql_data_array, 'actualizar', $param);
/* ------------ Preparar pedidos de refacciones --------------------*/
/* ------------   Modo Cotización ----------------------------------*/
					} elseif($sel_prov[$i] > 0) {
						$descuc = round(($costo[$i] * $descuento), 2);
						$costo[$i] = $costo[$i] - $descuc;
						$descup = round(($op_costo[$i][$sel_prov[$i]] * $descuento), 2);
//						echo 'Hola';
						$op_costo[$i][$sel_prov[$i]] = $op_costo[$i][$sel_prov[$i]] - $descup;
						if($tipo_pedido == '1') $op_costo[$i][$sel_prov[$i]] = 0;
//						echo $op_costo[$i][$sel_prov[$i]] . '<br>';
						$pedprov[$sel_prov[$i]][] = array('op_id' => $op_id[$i]);
						$pedido_tipo[$sel_prov[$i]] = $tipo_pedido;
						$reporte[$sel_prov[$i]] = $sub_reporte[$i];
						$aseguradora[$sel_prov[$i]] = $sub_aseguradora[$i];
						$pedop[$op_id[$i]] = array($sel_prov[$i], $fpromesa[$i], $tipo_pedido, $cantidad[$i], $op_nombre[$i], $op_codigo[$i], $op_costo[$i][$sel_prov[$i]], $precio[$i]);
					} elseif($selecionado[$i] == '1') {
						$descuc = round(($costo[$i] * $descuento), 2);
						$costo[$i] = $costo[$i] - $descuc;
						$descup = round(($op_costo[$i] * $descuento), 2);
						$op_costo[$i] = $op_costo[$i] - $descup;
//						echo $op_nombre[$i] . ' -> ' . $op_costo[$i] . ' ' . $descuento . ' ' . $descu . '<br>'; 
						$prov_sel = explode(':', $prov_selec[0]);
						$fprom = dia_habil($prov_sel[1]);
						$pedprov[$prov_sel[0]][] = array('op_id' => $op_id[$i]);
						$pedido_tipo[$prov_sel[0]] = $tipo_pedido;
						$reporte[$prov_sel[0]] = $sub_reporte[$i];
						$aseguradora[$prov_sel[0]] = $sub_aseguradora[$i];
						$pedop[$op_id[$i]] = array($prov_sel[0], $fprom, $tipo_pedido, $cantidad[$i], $op_nombre[$i], $op_codigo[$i], $op_costo[$i], $precio[$i]);
					}
				}
//		print_r($pedprov);
/* ------------ Crear pedidos de refacciones --------------------*/
			foreach($pedprov as $i => $v) {
				$subtotal = 0; 
				if($pedido_tipo[$i] == '2' || $pedido_tipo[$i] == '3') {
					foreach($pedop as $j => $w) {
						$w[6] = limpiarNumero($w[6]);
						$subtotal = $subtotal + $w[6];
					}
				}
				$iva = round(($subtotal * $impuesto_iva), 2);
				$sql_array = array('prov_id' => $i,
					'orden_id' => $orden_id,
					'pedido_tipo' => $pedido_tipo[$i],
					'subtotal' =>  $subtotal,
					'impuesto' => $iva,
					'usuario_pide' => $_SESSION['usuario']);
				ejecutar_db($dbpfx . 'pedidos', $sql_array, 'insertar');
				$pedido = mysql_insert_id();
				bitacora($orden_id, 'Se creo el pedido ' . $pedido, $dbpfx);
				$fpromped = dia_habil($provs[$i]['dde']);
				$param = "pedido_id = '" . $pedido . "'";
				$sql_data = array('fecha_promesa' => $fpromped);
				if($pedido_tipo[$i] == '1' || $pedido_tipo[$i] == '2' || $pedido_tipo[$i] == '3') {
					$sql_data['pedido_estatus'] = 5;
					$sql_data['fecha_pedido'] = date('Y-m-d H:i:s');
				}
				ejecutar_db($dbpfx . 'pedidos', $sql_data, 'actualizar', $param);
				foreach($pedop as $j => $w) {
					if($w[0] == $i) {
						$uop_id = $j;  // Último número de op_id para obtener aseguradora y reporte más adelante
						$param = "op_id = '" . $j . "'";
						$w[7] = limpiarNumero($w[7]);
						$sql_data = array('op_pedido' => $pedido,
							'op_fecha_promesa' => $fpromped,
							'op_costo' => $w[6],
							'op_autosurtido' => $w[2]);
						if($w[7] > 0 && $w[7] != '') {
							$prop = "SELECT op_precio FROM " . $dbpfx . "orden_productos WHERE op_id = '$j'";
							$maop = mysql_query($prop) or die("ERROR: Fallo selección de orden producto!");
							$elop = mysql_fetch_array($maop);
 
							$sql_data['op_precio_original'] = $elop['op_precio'];
							$sql_data['op_precio_revisado'] = '1'; 
							$sql_data['op_precio'] = $w[7];
							$concepto = 'Precio Revisado en OP:' . $j;
							bitacora($orden_id, $concepto, $dbpfx);
						}
						if($ajustacodigo == 1) {
							$sql_data['op_codigo'] = $w[5];
						}
						ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
						$recalcular = 1;
					}
				}
			}
			if($tipo_pedido == '1') {
				$preg = "SELECT sub_orden_id FROM " . $dbpfx . "orden_productos WHERE op_id = '$uop_id'";
				$matr = mysql_query($preg) or die("ERROR: Fallo selección de productos!");
				$sub2 = mysql_fetch_array($matr);
				$pregs = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub2['sub_orden_id'] . "'";
				$matrs = mysql_query($pregs) or die("ERROR: Fallo selección de suborden!");
				$sub3 = mysql_fetch_array($matrs);
				
				$acargo = 'Cargo a ' . $asegnic[$sub3['sub_aseguradora']] . '.<br>Reporte: ' . $sub3['sub_reporte'];
			} else { 
				$acargo = constant('TIPO_PEDIDO_'.$tipo_pedido); 
			}
//			echo 'Provedor: '.$i.' Aseg: '.$aseguradora[$i].' Rep: '.$reporte[$i];
			$siniestro = $reporte[$i];
			$para = $provs[$i]['email'];
			$enviar_prov = $provs[$i]['env'];
//			echo 'Usuario: ' . $_SESSION['usuario']; 
/*			if($_SESSION['usuario']=='1001') { $para = $provs[$i]['email']; 
				if($email_refaux != '') { $bcc = ', ' . $email_refaux; }
				else { $bcc = '';} 
			} else { $enviar_prov = 0; }
*/
			$asunto = 'Pedido ' . $pedido . ' de ' . $agencia . ' OT ' . $orden_id;
			$texto_t_solicitud = 'Pedido';
			$preg1 = "SELECT op_cantidad, op_nombre, op_codigo, op_doc_id FROM " . $dbpfx . "orden_productos WHERE op_pedido = '$pedido'";
//			echo $preg1;
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");
		} elseif($tipo_pedido == '5') {
//			echo $tipo_pedido;
//			print_r($_FILES['fotoref']);
			foreach($_FILES['fotoref']['name'] as $k => $v) {
//				$findx = key($_FILES['fotoref']['name']);
//				echo '<br>'.$findx.' ' . $v . '<br>';
				$nombre_archivo = basename($_FILES['fotoref']['name'][$k]);
				if($nombre_archivo != '') {
					$nombre_archivo = limpiarstring($nombre_archivo);
					$nombre_archivo = $orden_id . '-p-' . $op_id[$k] . '-' . time() . '-' . $nombre_archivo;
//					echo $nombre_archivo;
					if (move_uploaded_file($_FILES['fotoref']['tmp_name'][$k], DIR_DOCS . $nombre_archivo)) {
						$sql_data_array = array('orden_id' => $orden_id,
						'doc_nombre' => 'Imagen de refacción: ' . $op_id[$k],
						'doc_usuario' => $_SESSION['usuario'],
						'doc_archivo' => $nombre_archivo);
						ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
						$doc_id = mysql_insert_id();
						creaMinis($nombre_archivo);
						$param = "op_id = '" . $op_id[$k] . "'";
						$sql_data = array('op_doc_id' => $doc_id);
						ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
						bitacora($orden_id, 'Imagen de refacción ' . $op_id[$k], $dbpfx);
					} else {
						$_SESSION['msjerror'] .= 'Error, no subió el archivo ' . $_FILES['fotoref']['name'][$k] . '<br>';
					}
				}
			}
			redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id);
		} elseif($tipo_pedido == '10') {
			$para =''; $cuenta = 0;
			foreach($prov_selec as $k) {
				$m = explode(':', $k);
				if($provs[$m[0]]['env'] == '1') {$enviar_prov = 1;}
				foreach($op_id as $j => $w) {
					echo 'OP ID: ' . $w . ' K -> ' . $k . '<br>';
					if($selecionado[$j] == '1') {
						$preg1 = "SELECT op_id FROM " . $dbpfx . "prod_prov WHERE op_id = '$w' AND prod_prov_id = '" . $m[0] . "'";
						$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos a proveedores!");
						$fila1 = mysql_num_rows($matr1);
						$sql_data = array(
							'op_id' => $w, 
							'prod_prov_id' => $m[0],
							'prod_costo' => NULL,
							'dias_entrega' => NULL,
							'dias_credito' => NULL,
							'fecha_cotizado' => date('Y-m-d H:i:s', time())
						);
						if($fila1 > 0) { 
							$param = "op_id = '$w' AND prod_prov_id = '" . $m[0] . "'";
							ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'actualizar', $param);
						} else { 
							ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'insertar');
						}
					}
				}
				if($cuenta > 0) { $cotiza_a .= '|'; }
				$cotiza_a .= $m[0];
				$cuenta++;
			}
			$asunto = 'Cotización de Refacciones de ' . $vehiculo['completo'] . ' para ' . $agencia . ' OT ' . $orden_id;
			if($cotizataller == '1') {
				$acargo = 'A cargo de Aseguradora' ;
			} else {
				$acargo = 'Lista de refacciones.' ;
			}
			$texto_t_solicitud = 'Cotizar';

		} elseif($tipo_pedido == '11') {
			$para =''; $cuenta = 0;
			foreach($prov_selec as $k) {
				$m = explode(':', $k);
				if($provs[$m[0]]['env'] == '1') {$enviar_prov = 1;}
				foreach($op_id as $j => $w) {
					echo 'OP ID: ' . $w . ' K -> ' . $k . '<br>';
					if($selecionado[$j] == '1') {
						$preg1 = "SELECT op_id FROM " . $dbpfx . "prod_prov WHERE op_id = '$w' AND prod_prov_id = '" . $m[0] . "'";
						$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos a proveedores!");
						$fila1 = mysql_num_rows($matr1);
						$sql_data = array(
							'op_id' => $w, 
							'prod_prov_id' => $m[0],
							'prod_costo' => NULL,
							'dias_entrega' => NULL,
							'dias_credito' => NULL,
							'fecha_cotizado' => date('Y-m-d H:i:s', time())
						);
						if($fila1 > 0) { 
							$param = "op_id = '$w' AND prod_prov_id = '" . $m[0] . "'";
							ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'actualizar', $param);
						} else { 
							ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'insertar');
						}
					}
				}
				if($cuenta > 0) { $cotiza_a .= '|';}
				$cotiza_a .= $m[0];
				$cuenta++;
			}
			$asunto = 'Cotización de Refacciones de ' . $vehiculo['completo'] . ' para ' . $agencia . ' OT ' . $orden_id;
			$acargo = 'A cargo de Taller' ;
			$texto_t_solicitud = 'Cotizar';

		} elseif($tipo_pedido == '4') {
			foreach($op_id as $k => $v) {
				$param = "op_id = '" . $v . "'";
				$precio[$k] = limpiarNumero($precio[$k]);
				$sql_data = array();
				$prop = "SELECT op_precio, op_precio_revisado FROM " . $dbpfx . "orden_productos WHERE op_id = '$v'";
				$maop = mysql_query($prop) or die("ERROR: Fallo selección de orden producto!");
				$elop = mysql_fetch_array($maop);
//				echo 'op_id: ' . $v . ' precio: ' . $precio[$k] . '<br>';
				if($precio[$k] != '' && $precio[$k] >= '0' && $precio[$k] != $elop['op_precio']) { 
					if($elop['op_precio_revisado'] < '1') {
						$sql_data['op_precio_original'] = $elop['op_precio']; 
					}
					$sql_data['op_precio_revisado'] = '1'; 
					$sql_data['op_precio'] = $precio[$k];
					$concepto = 'Precio Revisado paso 1 en OP:' . $v;
					bitacora($orden_id, $concepto, $dbpfx);
					$recalcular = 1;
					ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
				} elseif($descuento > 0 && $precio[$k] == '' ) { 
					if($elop['op_precio_revisado'] < '1') {
						$sql_data['op_precio_original'] = $elop['op_precio']; 
					}
					$descpre = round(($elop['op_precio'] * $descuento), 2);
					$precio[$k] = $elop['op_precio'] - $descpre;
					$sql_data['op_precio_revisado'] = '1'; 
					$sql_data['op_precio'] = $precio[$k];
					$concepto = 'Precio Revisado en OP:' . $v;
					bitacora($orden_id, $concepto, $dbpfx);
					$recalcular = 1;
					ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
				}
//				print_r($sql_data);
			}
		} elseif($tipo_pedido == '6') {
			foreach($op_id as $k => $v) {
				$param = "op_id = '" . $v . "'";
				$op_costo[$k] = limpiarNumero($op_costo[$k]);
				$sql_data = array();
				$prop = "SELECT op_costo, op_precio_revisado, op_codigo FROM " . $dbpfx . "orden_productos WHERE op_id = '$v'";
				$maop = mysql_query($prop) or die("ERROR: Fallo selección de orden producto!");
				$elop = mysql_fetch_array($maop);
//				echo 'op_id: ' . $v . ' precio: ' . $precio[$k] . '<br>';
				if($op_costo[$k] >= 0 && $op_costo[$k] != $elop['op_costo']) { 
					if($elop['op_precio_revisado'] == '1') {
						$sql_data['op_precio_revisado'] = '2'; 
					}
					$sql_data['op_costo'] = $op_costo[$k];
					$concepto = 'Costo revisado desde lista en OP:' . $v;
					bitacora($orden_id, $concepto, $dbpfx);
					ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
				}
				if($ajustacodigo == 1) {
					$op_codigo[$k] = preparar_entrada_bd($op_codigo[$k]);
					unset($sql_data);
					if($op_codigo[$k] != $elop['op_codigo']) {
						$sql_data['op_codigo'] = $op_codigo[$k];
						$concepto = 'Codigo cambiado desde lista en OP:' . $v;
						bitacora($orden_id, $concepto, $dbpfx);
						ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
					}
				}
//				print_r($sql_data);
			}
		}
		
// -------------- Agregar números de proveedores a quienes se cotizó la refacción

		if($tipo_pedido == '10' || $tipo_pedido == '11') {
			foreach($op_id as $k => $v) {
				if($selecionado[$k] == '1') {
					$preg5 = "SELECT op_cotizado_a FROM " . $dbpfx . "orden_productos WHERE op_id = '$v'";
					$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden producto!");
					$cot = mysql_fetch_array($matr5);
					if($cot['op_cotizado_a'] != '') {
						$cotiza = $cotiza_a . '|' . $cot['op_cotizado_a'];
					} else {
						$cotiza = $cotiza_a;
					}
					$param = "op_id = '$v'";
					$sql_data = array('op_cotizado_a' => $cotiza);
					ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
				}
			}
		}
		
		
//		echo $recalcular;
// 			Recalcular subtotal de refacciones y presupuesto
		if($recalcular == 1) {
			$preg3 = "SELECT sub_orden_id, sub_consumibles, sub_mo FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '130'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de subordenes!");
			while($sub1 = mysql_fetch_array($matr3)) {
				$preg2 = "SELECT op_id, op_cantidad, op_precio, op_precio_revisado, op_autosurtido, op_recibidos, op_estructural, op_pres, op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub1['sub_orden_id'] . "' AND op_tangible = '1'";
//				echo $preg2;
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos para recálculo!");
				$op_ref = 0; $refacciones = 0;
				while($rec = mysql_fetch_array($matr2)) {
					$op_sub = round(($rec['op_cantidad'] * $rec['op_precio']), 2);
					if(is_null($rec['op_pres'])) {
						if($rec['op_precio_revisado'] > 0) {
							$param = "op_id = '" . $rec['op_id'] . "'";
							$sql_data = array('op_subtotal' => $op_sub);
							ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
						}
						if($rec['op_autosurtido'] == '2' || $rec['op_autosurtido'] == '3') {
							$op_ref = $op_ref + $op_sub;
						}
					}
					if($rec['op_cantidad'] > $rec['op_recibidos']) {
						if(($rec['op_pres'] == 1 && $rec['op_pedido'] > 0) || is_null($rec['op_pres'])) {
							if($rec['op_estructural']==1) { $refacciones=2;}
							else { $refacciones=1; } 
						} 
					}
				}
//					echo $op_ref;
				$nvo_pres = $op_ref + $sub1['sub_consumibles'] + $sub1['sub_mo'];
				$sql_data = array('sub_presupuesto' => $nvo_pres, 'sub_partes' => $op_ref, 'sub_refacciones_recibidas' => $refacciones);
				$param = "sub_orden_id = '" . $sub1['sub_orden_id'] . "'";
				ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $param);
			}
		} 

		
/* ------------------- Crear archivo ZIP con imágenes de ingreso para envío a proveedores en Cotización -----------------*/		
/*		$pregimg = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE `orden_id` = $orden_id AND doc_archivo LIKE '%-i-%'";
		$matrimg = mysql_query($pregimg) or die("ERROR: Fallo selección de imágenes!");
		$filaimg = mysql_num_rows($matrimg);
		if($filaimg > 0) {
			$archivo = $orden_id.'-refacciones.zip';
			if (file_exists(DIR_DOCS . $archivo)) { unlink (DIR_DOCS . $archivo); }
			$zip = new ZipArchive();
			 if ($zip->open(DIR_DOCS . $archivo, ZIPARCHIVE::CREATE )!==TRUE) {
				exit("No se puede abrir <$archivo>\n");
			} 
			while($img = mysql_fetch_array($matrimg)) {
				 $zip->addFile(DIR_DOCS . $img['doc_archivo']);
			}
			$zip->close();
		}	
*/
/* ------------ Enviar por e-mail pedidos a Proveedores --------------------*/
//		echo ' POr enviar correo!';
		if($enviar_prov == '1') {
						
			require ('parciales/PHPMailerAutoload.php');

			$mail = new PHPMailer;

			$mail->CharSet = 'UTF-8';
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $smtphost;  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = $smtpusuario;                 // SMTP username
			$mail->Password = $smtpclave;                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
			$mail->Port       = $smtppuerto; 

			$mail->From = constant('EMAIL_PROVEEDOR_FROM');
			$mail->FromName = 'Refacciones de ' . $nombre_agencia;
			
			if($tipo_pedido < '5') {
				$ma = explode(',', $provs[$i]['email']);
				foreach($ma as $k) {
					$mail->addAddress($k);     // Add a recipient
				}
			} else {
				foreach($prov_selec as $k) {
					$m = explode(':', $k);
					$ma = explode(',', $provs[$m[0]]['email']); 
					foreach($ma as $j) {
						$mail->addAddress($j);     // Add a recipient
					}
				}
			} 
//			$mail->addAddress($para);     // Add a recipient

			$pregusu = "SELECT email FROM " . $dbpfx . "usuarios WHERE usuario = '" . $_SESSION['usuario'] . "'";
			$matrusu = mysql_query($pregusu) or die("ERROR: Fallo selección de usuario!");
			$eusr = mysql_fetch_array($matrusu);
		   if($eusr['email'] != '' ) {
		   	$mail->addReplyTo($eusr['email']);
		   	$mail->addCC($eusr['email']);
		  	} else {
				$mail->addReplyTo(constant('EMAIL_PROVEEDOR_RESPONDER'));
				$ma = explode(',', constant('EMAIL_PROVEEDOR_CC'));
				foreach($ma as $k) {
					$mail->addCC($k);     // Add a recipient
				}
			}

//			$mail->addCC(constant('EMAIL_PROVEEDOR_CC'));

			if($bcc) { $mail->addCC($bcc);}
			$mail->addBCC('monitoreo@controldeservicio.com');

			$email_order = '<!DOCTYPE HTML PUBLIC \'-//W3C//DTD HTML 4.0 Transitional//EN\'><html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" /></head><body style="font-family:Arial;">';
			if($tipo_pedido == 10 || $tipo_pedido == 11) { $email_order .= '<p>' . EMAIL_TEXT_COTIZACION . '</p>'."\n"; } 
			else { $email_order .= '<p>' . EMAIL_TEXT_DESCRIPCION . '</p>'."\n"; }
			$email_order .= '<table cellpadding="2" cellspacing="0" border="1" width="800"><tr><td colspan="2">' . $nombre_agencia . '</td><td><strong>' . $texto_t_solicitud . ': </strong>' . $pedido . '<br>OT ' . $orden_id . '</td><td><strong>';
			$email_order .= $acargo;
			$email_order .= '</strong></td><td>Fecha: ' . date('Y-m-d') . '</td></tr>'."\n";
			$email_order .= '<tr><td colspan="5"><strong>DETALLE DE REFACCIONES PARA:<br>' . $vehiculo['refacciones'] . '</strong></td></tr></table>'."\n";
			$email_order .= '<table cellpadding="2" cellspacing="0" border="1" width="800"><tr><td>Cantidad</td><td>Nombre</td><td>Código</td></tr>';
			if($tipo_pedido == 10 || $tipo_pedido == 11) {
				foreach($op_id as $k => $v) {
					if($selecionado[$k] == '1') {
						$preg5 = "SELECT op_cantidad, op_nombre, op_codigo, op_doc_id FROM " . $dbpfx . "orden_productos WHERE op_id = '$v'";
						$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden producto!");
						$procot = mysql_fetch_array($matr5);
						$email_order .= '<tr><td style="text-align:center;">' . $procot['op_cantidad'] . '</td><td>' . $procot['op_nombre'] . '</td><td>' . $procot['op_codigo'] . '</td></tr>'."\n";
						if($envfotoref == '1') {
							$preg4 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE doc_id = '" . $procot['op_doc_id'] . "'";
							$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección foto refacción!");
							$resu4 = mysql_fetch_array($matr4);
							$filaimg = mysql_num_rows($matr4);
							if($filaimg > 0) {
								$mail->addAttachment(DIR_DOCS.$resu4['doc_archivo']);
							}
						}
					}
				}
				if($envcotex == '1') {
					$preg5 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE doc_archivo LIKE '" . $orden_id . "-cotizaex%";
					if($tipo_pedido == 10) { $preg5 .= "-aseguradora.xlsx'"; }
					elseif($tipo_pedido == 11) { $preg5 .= "-particular.xlsx'"; }
					
					$preg5 .= " GROUP BY doc_archivo";
					$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección cotización Excel!" . $preg5);
					$filaimg = mysql_num_rows($matr5);
					while ($resu5 = mysql_fetch_array($matr5)) {
//						echo 'Siniestro: ' . $siniestro . ' Archivo: ' . $resu5['doc_archivo'] . '<br>';
						if($filaimg > 0) {
							$mail->addAttachment(DIR_DOCS.$resu5['doc_archivo']);
						}
					}
					$preg5 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND (doc_archivo LIKE '%-i-1-%' OR doc_archivo LIKE '%-i-2-%' OR doc_archivo LIKE '%-i-3-%' OR doc_archivo LIKE '%-i-6-%')";
					$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de fotos de ingreso!");
					$filaimg = mysql_num_rows($matr5);
					if($filaimg > 0) {
						while($resu5 = mysql_fetch_array($matr5)) {
							$mail->addAttachment(DIR_DOCS.$resu5['doc_archivo']);
						}
					}
				}
			} else {
				while ($prod = mysql_fetch_array($matr1)) {
					$email_order .= '<tr><td style="text-align:center;">' . $prod['op_cantidad'] . '</td><td>' . $prod['op_nombre'] . '</td><td>' . $prod['op_codigo'] . '</td></tr>'."\n";
					if($envfotoref == '1') {
						$preg4 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE doc_id = '" . $prod['op_doc_id'] . "'";
						$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección foto refacción!");
						$resu4 = mysql_fetch_array($matr4);
						$filaimg = mysql_num_rows($matr4);
						if($filaimg > 0) {
							$mail->addAttachment(DIR_DOCS.$resu4['doc_archivo']);
						}
					}
				}
				if($envcotex == '1') {
					$preg5 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND (doc_archivo LIKE '%-i-1-%' OR doc_archivo LIKE '%-i-2-%' OR doc_archivo LIKE '%-i-3-%' OR doc_archivo LIKE '%-i-5-%')";
					$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de fotos de ingreso!");
					$filaimg = mysql_num_rows($matr5);
					if($filaimg > 0) {
						while($resu5 = mysql_fetch_array($matr5)) {
							$mail->addAttachment(DIR_DOCS.$resu5['doc_archivo']);
						}
					}
				}
			}
			$email_order .= '</table>'."\n";
			$email_order .= '<p>Atentamente.<br><br>' . JEFE_DE_ALMACEN . '<br>'.$agencia_razon_social."<br>".$agencia_direccion."<br>Col. ".$agencia_colonia.", ".$agencia_municipio."<br>C.P.: ".$agencia_cp.". ".$agencia_estado."<br>E-mail: ".EMAIL_DE_ALMACEN."<br>Tels: ".$agencia_telefonos.'<br>' . TELEFONOS_ALMACEN . '</p>';
			$email_order .= '</body></html>';

			$mail->isHTML(true);                                  // Set email format to HTML

			$mail->Subject = $asunto;
			$mail->Body    = $email_order;
//			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			if(!$mail->send()) {
				$mensaje = 'Errores en notificación automática: ';
				$mensaje .=  $mail->ErrorInfo;
	   	$_SESSION['msjerror'] = $mensaje;
   		redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id);
			} 
		} 
		if($tipo_pedido < 4) {
			redirigir('pedidos.php?accion=consultar&pedido=' . $pedido);
		}
		redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id);
	} else {
   	$_SESSION['ref']['mensaje'] = $mensaje;
   	redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id);
   }
}

elseif($accion==='listar') {
	
	$funnum = 1115030;

	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}

	if(isset($paquete) && $paquete > 0) { $_SESSION['ref']['paquete'] = $paquete;}
	$paquete = $_SESSION['ref']['paquete'];
	if($limpiar == 'Restablecer Filtros') { $codigo=''; $nombre=''; $almacen=''; }
	echo '	<form action="refacciones.php?accion=listar" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td colspan="2"><span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span></td></tr>'."\n";
	unset($_SESSION['ref']['mensaje']);
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Refacciones, Consumibles y Mano de Obra por Almacén</td></tr>'."\n";
	
	
	echo '		<tr class="obscuro"><td style="text-align:left; width:50%;">Buscar por codigo: <input type="text" name="codigo" id="codigo" size="15" value="' . $codigo . '">
			<input name="Enviar" value="Enviar" type="submit"></td><td style="width:50%;">Buscar por nombre: <input type="text" name="nombre" size="15" value="' . $nombre . '">
			<input name="Enviar" value="Enviar" type="submit"></td></tr>
		<tr class="obscuro"><td style="text-align:left;">Filtrar por Almacén: 
			<select name="almacen" size="1">
				<option value="">Seleccionar...</option>'."\n";
	foreach($nom_almacen as $k => $v) {
		echo '				<option value="' . $k . '"';
		if($almacen == $k) { echo ' selected="selected" ';}
		echo '>' . $v . '</option>'."\n";
	}											
	echo '			</select>
			<input name="Enviar" value="Enviar" type="submit"><br><input type="submit" name="limpiar" value="Restablecer Filtros">'."\n";
	echo '		</td><td><a href="refacciones.php?accion=item&nuevo=1"><img src="idiomas/' . $idioma . '/imagenes/agregar.png" alt="Nuevo Producto" title="Nuevo Producto"></a>&nbsp;<a href="refacciones.php?accion=cotpedprod"><img src="idiomas/' . $idioma . '/imagenes/carro-de-compra.png" alt="Pedidos y Cotizaciones" title="Pedidos y Cotizaciones"></a>'."\n";
	if($pedpend == 1) { $_SESSION['ref']['pedpend'] = 1; }
	if($cotpend == 1) { $_SESSION['ref']['cotpend'] = 1; }
	if($pedpend == 2)  { unset($_SESSION['ref']['pedpend']); unset($_SESSION['ref']['cotpend']); }
	if($_SESSION['ref']['pedpend'] == 1 || $_SESSION['ref']['cotpend'] == 1) {
		echo '&nbsp;<a href="refacciones.php?accion=listar&pedpend=2"><img src="idiomas/' . $idioma . '/imagenes/flag-black.png" alt="Todo" title="Todo"></a>';
	} else {
		echo '&nbsp;<a href="refacciones.php?accion=listar&pedpend=1"><img src="idiomas/' . $idioma . '/imagenes/flag-yellow.png" alt="Sólo Items Pendientes por Recibir" title="Sólo Items Pendientes por Recibir"></a>';
		echo '&nbsp;<a href="refacciones.php?accion=listar&cotpend=1"><img src="idiomas/' . $idioma . '/imagenes/cotizacion-por-recibir.png" alt="Sólo Cotizaciones Pendientes por Recibir" title="Sólo Cotizaciones Pendientes por Recibir"></a>';
	}
	echo '&nbsp;<a href="refacciones.php?accion=generacb"><img src="idiomas/' . $idioma . '/imagenes/barcode_scanner.png" alt="Generar Etiquetas de Código de Barras" title="Generar Etiquetas de Código de Barras"></a>';
	echo '</td></tr></table></form>'."\n";
	if((isset($almacen) && $almacen!='') || (isset($nombre) && $nombre!='') || (isset($codigo) && $codigo!='')) { 
		if(isset($almacen) && $almacen!='') { $preg .= "AND prod_almacen='" . $almacen . "' "; }
		if(isset($nombre) && $nombre!='') {
			$nomped = explode(' ', $nombre);
			if(count($nomped) > 0) {
				foreach($nomped as $kc => $vc){
					$preg .= "AND prod_nombre LIKE '%" . $vc . "%' ";
				}
			} 
		}
		if(isset($codigo) && $codigo!='') { $preg .= "AND prod_codigo = '" . $codigo . "' "; }
	}
	if($_SESSION['ref']['pedpend'] == 1) { $preg .= "AND prod_cantidad_pedida > '0' "; }
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	$preg0 = "SELECT prod_id FROM " . $dbpfx . "productos WHERE prod_activo='1' ";
	$preg0 = $preg0 . $preg;
//	echo $preg0 . '<br>';
   $matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
   $filas = mysql_num_rows($matr0);
   if($filas == 0 && $codigo!='') {
   	echo '		<tr><td colspan="2">No se encontró ningún producto con el código ' . $codigo . ',<br>¿Desea agregarlo como nuevo a la lista de productos? <a href="refacciones.php?accion=item&nuevo=1&codigo=' . $codigo . '">Agregar</a>&nbsp;</td></tr>';
   } else {
	   $renglones = 50;
	   $paginas = (round(($filas / $renglones) + 0.49999999) - 1);
   	if(!isset($pagina)) { $pagina = 0;}
	   $inicial = $pagina * $renglones;
//	echo $paginas;
		if($_SESSION['ref']['cotpend'] == 1) {
			$preg1 = "SELECT p.prod_id, p.prod_marca, p.prod_codigo, p.prod_nombre, p.prod_cantidad_pedida, p.prod_cantidad_existente, p.prod_cantidad_disponible, p.prod_precio, p.prod_almacen, p.prod_tangible FROM " . $dbpfx . "productos p, " . $dbpfx . "prod_prov pp WHERE p.prod_activo = '1' AND pp.prod_id = p.prod_id AND pp.prod_costo = '0'";
//			$preg1 = $preg1 . $preg;
			$preg1 .= " GROUP BY p.prod_id ORDER BY p.prod_almacen, p.prod_id LIMIT " . $inicial . ", " . $renglones;
		} else {
			$preg1 = "SELECT prod_id, prod_marca, prod_codigo, prod_nombre, prod_cantidad_pedida, prod_cantidad_existente, prod_cantidad_disponible, prod_precio, prod_almacen, prod_tangible FROM " . $dbpfx . "productos WHERE prod_activo = '1' ";
			$preg1 = $preg1 . $preg;
			$preg1 .= " GROUP BY prod_id ORDER BY prod_almacen, prod_id LIMIT " . $inicial . ", " . $renglones;
		}
//	echo $preg1;
   	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");

		echo '			<tr><td colspan="2"><a href="refacciones.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="refacciones.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>';
		echo '</td></tr>'."\n";
		echo '			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td style="width:120px;">Almacén</td><td style="width:150px;">Nombre</td><td style="width:80px;">Marca</td><td style="width:0px;">Código</td><td style="width:30px;">Existencia</td><td style="width:30px;">Disponible</td><td style="width:100px;">Precio Unitario<br>de Venta</td><td>Acciones</td></tr>'."\n";
		$cue = 0;
		while($prods = mysql_fetch_array($matr1)) {
			echo '					<tr><td>';
			echo $nom_almacen[$prods['prod_almacen']]; 
			echo '</td><td>' . $prods['prod_nombre'] . '</td><td>' . $prods['prod_marca'] . '</td><td>' . $prods['prod_codigo'] . '</td><td style="text-align:right;">' . $prods['prod_cantidad_existente'] . '</td><td style="text-align:right;">' . $prods['prod_cantidad_disponible'] . '</td><td style="text-align:right;">' . money_format('%n', $prods['prod_precio']) . '</td><td>';
			if(isset($paquete) && $paquete > 0) {
				echo '<a href="refacciones.php?accion=inspcpaq&paquete=' . $paquete . '&prod_id=' . $prods['prod_id'] . '">Agregar</a></td></tr>'."\n";
			} else {
				echo '<a href="refacciones.php?accion=item&prod_id=' . $prods['prod_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/prod-editar.png" alt="Detalles" title="Detalles"></a> / <a href="refacciones.php?accion=cotpedprod&prod_id=' . $prods['prod_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/carro-de-compra.png" height="16" alt="Agregar a Pedido o Cotización" title="Agregar a Pedido o Cotización"></a>';
				if($prods['prod_cantidad_pedida'] > 0 && $prods['prod_tangible'] > 0) { 
					echo '<img src="idiomas/' . $idioma . '/imagenes/flag-yellow.png" height="16" alt="Pendiente por Recibir" title="Pendiente por Recibir">';
				}
				$preg2 = "SELECT prod_id FROM " . $dbpfx . "prod_prov WHERE prod_id = '" . $prods['prod_id'] . "' AND prod_costo = '0' ";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cotizaciones!");
				$fila2 = mysql_num_rows($matr2);
				if($fila2 > 0) { 
					echo '<img src="idiomas/' . $idioma . '/imagenes/cotizacion-por-recibir.png" height="16" alt="Cotización por Recibir" title="Cotización por Recibir">';
				}
				echo '</td></tr>'."\n";
			}
			$cue++;
		}
		echo '				</table>'."\n";
		echo '			</td>
		</tr>'."\n";
		echo '			<tr><td colspan="2"><a href="refacciones.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="refacciones.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>';
		echo '</td></tr>'."\n";
	}
	echo '		<tr><td colspan="2"><hr></td></tr>'."\n";
	echo '	</table>'."\n";
}

elseif($accion==='item') {
	
	$funnum = 1115035;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	
	if($prod_id!='') {
		$preg0 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id='" . $prod_id . "'";
//	echo $preg0 . '<br>';
   	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
	   $prod = mysql_fetch_array($matr0);
	}
	
	echo '	<form action="refacciones.php?accion=';
	if($nuevo=='1') { echo 'insertar';} else { echo 'actualizar';}
	echo '" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">
		<tr><td colspan="3"><span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span></td></tr>'."\n";
	echo '		<tr><td>Nombre</td><td><input type="text" name="nombre" size="40" maxlength="255" value="'; 
	echo ($_SESSION['ref']['nombre']) ? $_SESSION['ref']['nombre']:$prod['prod_nombre']; echo '" /></td>';
	echo '<td rowspan="13">Histórico de 5 últimos movimientos:<br>';
	$preg4 = "SELECT * FROM " . $dbpfx . "prod_bitacora WHERE prod_id='" . $prod_id . "' ORDER BY bit_id DESC LIMIT 5";
//	echo $preg4 . '<br>';
   $matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de bitácoras!".$preg4);
   $fila4 = mysql_num_rows($matr4);
   if($fila4 > 0) {
   	echo '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda">'."\n";
	   while($hist = mysql_fetch_array($matr4)) {
	   	$preg5 = "SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '" . $hist['usuario'] . "'";
			$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de Usuario!".$preg5);
			$usu = mysql_fetch_array($matr5);
   		echo '				<tr><td>' . $hist['fecha_evento'] . '</td><td>' . $usu['nombre'] . ' ' . $usu['apellido'] . '</td></tr>'."\n".'<tr><td colspan="2">' . $hist['evento'] . '</td></tr>'."\n";
   		if($hist['motivo'] != '') { echo '				<tr><td colspan="2">' . $hist['motivo'] . '</td></tr>'."\n";}
   	}
   	echo '			</table>'."\n";
   }

	echo '</td></tr>'."\n";
	echo '		<tr><td>Marca</td><td><input type="text" name="marca" size="40" maxlength="32" value="'; 
	echo ($_SESSION['ref']['marca']) ? $_SESSION['ref']['marca']:$prod['prod_marca']; echo '" /></td></tr>'."\n";
	echo '		<tr><td>Código</td><td><input type="text" name="codigo" size="20" maxlength="20" value="';
	if($nuevo=='1' && $codigo!='') {
		echo $codigo;
	} else {
		echo ($_SESSION['ref']['codigo']) ? $_SESSION['ref']['codigo']:$prod['prod_codigo'];
	} 
	echo '" />Código de Barras del producto.</td></tr>'."\n";
	
	echo '		<tr><td>Unidad</td><td><select name="uniprod" size="1">
			<option value="">Seleccionar...</option>'."\n";
	foreach ($unidad as $k) {
		echo '			<option value="'.$k.'" '; if($prod['prod_unidad']==$k) {echo 'selected="selected" ';} echo '>'.$k.'</option>'."\n";
	}
	echo '		</select></td></tr>'."\n";
	$tangible = array(0 => "Mano de Obra", 1 => "Refacción", 2 => "Consumible");
	echo '		<tr><td>Tipo</td><td><select name="tangible" size="1">
			<option value="9">Seleccionar...</option>'."\n"; 
	foreach ($tangible as $k => $v) {
		echo '			<option value="' . $k . '" '; if($prod['prod_tangible']==$k) {echo 'selected="selected" ';} echo '>'.$v.'</option>'."\n";
	}
	echo '		</select></td></tr>'."\n";

	if(isset($nuevo) && $nuevo=='1') {
		echo '		<tr><td colspan="2"><span class="alerta">Una vez dado de alta el producto, la cantidad y su costo se podrán ajustar desde recibo de productos.</span></td><td></tr>';
	} else {
		echo '		<tr><td>Existencias</td><td>' . $prod['prod_cantidad_existente'] . '<input type="hidden" name="existencia" value="' . $prod['prod_cantidad_existente'] . '" /></td></tr>'."\n";
		echo '		<tr><td style="vertical-align:bottom;">Ajustar<br>existencias a</td><td><input type="text" name="nvaexist" size="8" maxlength="10" value="' . $prod['prod_cantidad_existente'] . '" /> Motivo: <textarea name="motivoajuste" rows="3" cols="30">' . $_SESSION['ref']['motivoajuste'] . '</textarea></td></tr>'."\n";
		echo '		<tr><td>Pedidos</td><td>' . $prod['prod_cantidad_pedida'] . '</td></tr>'."\n";
		echo '		<tr><td>Disponibles</td><td>' . $prod['prod_cantidad_disponible'] . '<input type="hidden" name="disponible" value="' . $prod['prod_cantidad_disponible'] . '" /></td></tr>'."\n";
	}
	echo '		<tr><td>Precio de Venta Público</td><td><input type="text" name="precio" size="11" maxlength="20" value="'; 
	echo $prod['prod_precio'] . '" style="text-align:right;" /></td></tr>'."\n";
	echo '		<tr><td>Precio de Venta Interno</td><td><input type="text" name="precioint" size="11" maxlength="20" value="'; 
	echo $prod['prod_precioint'] . '" style="text-align:right;" /></td></tr>'."\n";
	echo '		<tr><td>Precio de Compra</td><td><input type="text" name="prodcosto" size="11" maxlength="20" value="'; 
	echo $prod['prod_costo'] . '" style="text-align:right;" /></td></tr>'."\n";
	$margen = (($prod['prod_precio'] - $prod['prod_costo']) / $prod['prod_precio']);
	echo '		<tr><td>Margen de Utilidad</td><td>' . ($margen * 100) . '% </td></tr>'."\n";
	echo '		<input type="hidden" name="margen" size="4" maxlength="6" value="' . $margen . '" />'."\n";
	echo '		<tr><td>Almacén</td><td><select name="almacen" size="1">
			<option value="">Seleccionar...</option>'."\n";
	foreach($nom_almacen as $k => $v) {
		echo '			<option value="'.$k.'" '; if($prod['prod_almacen']==$k) {echo 'selected="selected" ';} echo '>' . $v . '</option>'."\n";
	} 
	echo '		</select></td></tr>'."\n";
	echo '		<tr><td>Resurtir</td><td colspan="2"><input type="text" name="resurtir" size="12" value="'; 
	echo ($_SESSION['ref']['resurtir']) ? $_SESSION['ref']['resutir']:$prod['prod_resurtir']; echo '" /> Solicitar pedido cuando quede esta cantidad</td></tr>'."\n";
	echo '		<tr><td>Ubicación</td><td colspan="2"><input type="text" name="local" size="20" maxlength="32" value="'; 
	echo ($_SESSION['ref']['local']) ? $_SESSION['ref']['local']:$prod['prod_local']; echo '" /> Lugar dentro del almacén en donde está ubicado el producto.</td></tr>'."\n";

	if(!isset($nuevo) || $nuevo=='') {
		$preg2 = "SELECT pp.op_cantidad, pp.op_costo, pp.op_pedido, pp.op_recibidos, p.prov_id, pp.op_fecha_promesa FROM " . $dbpfx . "orden_productos pp, " . $dbpfx . "pedidos p WHERE pp.prod_id='" . $prod_id . "' AND pp.op_pedido = p.pedido_id";
//		echo $preg2 . '<br>';
  		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de proveedores!");
	  	$pedidos = mysql_num_rows($matr2);
	
  		echo '		<tr><td>Pedidos</td><td colspan="2">'."\n";
		echo '		<table cellpadding="3" cellspacing="0" border="1" class="centrado">'; 
		echo '			<tr><td>Proveedor</td><td>Pedido</td><td>Cantidad</td><td>Costo</td><td>Fecha Promesa<br>de Entrega</td><td>Recibidas</td><td>Pendientes</td></tr>'."\n";
		if($pedidos > 0) {
			while($ped = mysql_fetch_array($matr2)) {
				$pendientes = $ped['op_cantidad'] - $ped['op_recibidos'];
				echo '			<tr><td><a href="proveedores.php?accion=consultar&prov_id=' . $ped['prov_id'] . '" target="_blank" >' . $provs[$ped['prov_id']]['nic'] . '</a></td><td><a href="pedidos.php?accion=consultar&pedido=' . $ped['op_pedido'] . '" target="_blank">' . $ped['op_pedido'] . '</a></td><td>' . $ped['op_cantidad'] . '</td><td style="text-align:right;">' . money_format('%n', $ped['op_costo']) . '</td><td>' . $ped['op_fecha_promesa'] . '</td><td>' . $ped['op_recibidos'] . '</td><td>' . $pendientes . '</td>'."\n";
				echo '			</tr>'."\n";
			}
		} else {
			echo '<tr><td colspan="7">No se encontró ningún pedido.</td></tr>'."\n";
		}
		echo '		</table></td></tr>'."\n";
//		echo '		<tr><td>Nuevo Pedido?</td><td  style="text-align:left;"><input type="checkbox" name="nvopedido" value="1"';
//		echo ' /> Actualiza y después le lleva a la página para hacer Pedidos o pedir Cotizaciones.</td></tr>'; 
		
		$preg3 = "SELECT * FROM " . $dbpfx . "prod_prov  WHERE prod_id='" . $prod_id . "'";
//		echo $preg3 . '<br>';
	   $matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de proveedores!");
  		$cotiza = mysql_num_rows($matr3);
   	
	  	echo '		<tr><td>Cotizaciones</td><td colspan="2">'."\n";
		echo '		<table cellpadding="3" cellspacing="0" border="1" class="centrado">'; 
		echo '			<tr><td>Proveedor</td><td>Costo<br>Unitario</td><td>Días de Entrega</td><td>Días de crédito</td><td>Solicitada</td><td>Remover Cotización</td><td>Hacer Pedido</td></tr>'."\n";
		if($cotiza > 0) {
			$j=0;
			while($cot = mysql_fetch_array($matr3)) {
				echo '<input type="hidden" name="cot_prov_id['.$j.']" value="'.$cot['prod_prov_id'].'" />';
				echo '			<tr><td>' . $provs[$cot['prod_prov_id']]['nic'] . '</td><td><input type="text" name="cot_costo['.$j.']" value="' . money_format('%n', $cot['prod_costo']) . '" size="10"/></td><td><input type="text" name="cot_entrega['.$j.']" value="' . $cot['dias_entrega'] . '" size="3"/></td><td><input type="text" name="cot_credito['.$j.']" value="' . $cot['dias_credito'] . '" size="3"/></td><td>'.$cot['fecha_cotizado'].'</td><td><input type="checkbox" name="quitacot['.$j.']" value="1" /></td><td><input type="radio" name="hazped" value="' . $prod_id . ':' . $cot['prod_prov_id'] . '" /></td>'."\n";
				echo '			</tr>'."\n";
				$j++;
			}
		} else {
			echo '<tr><td colspan="4">No se encontró ninguna cotización.</td></tr>'."\n";
		}
		echo '		</table></td></tr>'."\n";
	
  		echo '		<tr><td>Nuevo Proveedor</td><td colspan="2">'."\n";
	  	echo '			<table cellpadding="3" cellspacing="0" border ="1" class="centrado"><tr><td><select name="prov_id">
			  	<option  value="0">...Seleccione</option>'."\n";
  		foreach($provs as $i => $j) {
  			echo '			  	<option  value="'.$i.'">'.$j['nic'].'</option>'."\n";
	  	}
		echo '			</select></td><td>Costo: <input type="text" name="costo" size="10"/></td><td>Días de entrega: <input type="text" name="entrega" size="3"/></td><td>Días de crédito: <input type="text" name="credito" size="3"/></td></tr></table></td></tr>'."\n";
	}
		
	echo '		<tr><td>Borrar?</td><td colspan="2" style="text-align:left;">';
	if($prod['prod_cantidad_existente'] > 0) {
		echo 'Para poder desactivar un producto, primero debe quedar sin existencias';
	} else {
		echo '<input type="checkbox" name="borrar" value="1" />';
	}
	echo '</td></tr>'; 
	echo '		<input type="hidden" name="prod_id" value="' . $prod['prod_id'] . '" /><input type="hidden" name="nuevo" value="' . $nuevo . '" />';
	echo '		<tr><td colspan="3" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>'."\n";
	echo '		<tr><td colspan="3" style="height:24px;"></td></tr>'."\n";
	echo '		<tr><td colspan="3" style="text-align:left;"><a href="refacciones.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Lista de Partes" title="Regresar a la Lista de Partes"></a></td></tr>'."\n";
	echo '	</table>
	</form>'."\n";
}

elseif($accion==="actualizar" || $accion==="insertar") {
	
	$funnum = 1115035;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Rol Almacén, ingresar Usuario y Clave correcta.');
	}
	unset($_SESSION['ref']);
	$_SESSION['ref'] = array();
	$_SESSION['ref']['mensaje']='';
	$mensaje = '';
	$error = 'no'; 
	
	$nombre = preparar_entrada_bd($nombre); $_SESSION['ref']['nombre']=$nombre;
	$marca = preparar_entrada_bd($marca); $_SESSION['ref']['marca']=$marca;
	$codigo = preparar_entrada_bd($codigo); $_SESSION['ref']['codigo']=$codigo;
	$motivoajuste = preparar_entrada_bd($motivoajuste); $_SESSION['ref']['motivoajuste'] = $motivoajuste;
	$nvaexist = limpiarNumero($nvaexist);

	
	$precio = limpiarNumero($precio); $_SESSION['ref']['precio']=$precio;
	$precioint = limpiarNumero($precioint); $_SESSION['ref']['precioint']=$precioint;
	$prodcosto = limpiarNumero($prodcosto); $_SESSION['ref']['prodcosto']=$prodcosto;
	$resurtir = limpiarNumero($resurtir); $_SESSION['ref']['resurtir']=$resurtir;
	$local = preparar_entrada_bd($local); $_SESSION['ref']['local']=$local;

/*	if($prod_id!='') {
		$preg0 = "SELECT prod_codigo FROM " . $dbpfx . "productos WHERE prod_codigo='" . $codigo . "'";
//	echo $preg0 . '<br>';
   	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
		$filas = mysql_num_rows($matr0);
		$prod = mysql_fetch_array($matr0);
		if($filas > 0 && ($accion==="insertar" || $nuevo=='1')) { 
//			$_SESSION['ref']['mensaje'] = 'Ya existe un producto en este código de identificación.';
//			redirigir('refacciones.php?accion=listar&codigo=' . $codigo); 
		}
	} else {
		$codigo = time();
	}
*/
	
//	echo 'Nombre: ' . $nombre . ' Código: ' . $codigo . '<br>'; 
	
	if($nuevo != '1') {
		if($nvaexist < 0) { $error = 1; $msj .= 'Las existencias no pueden ser menores a cero.<br>'; }
		if($nvaexist != $existencia) {
			$reservados = $existencia - $disponible;
			if($nvaexist >= $reservados) {
				$neok = 1;
				$nvadispo = $nvaexist - $reservados;
			} else {
				$error = 1; $msj .= 'La nueva existencia no puede ser menor a los reservados (existencia - disponibles).<br>';
			}
		} else {
			$nvadispo = $disponible;
		}
		if($neok == 1 && $motivoajuste == '') { $error = 1; $msj .= 'Se debe indicar el motivo del ajuste de existencias.<br>'; }
		
//		if($precio=='' && $margen=='') { $error = 1; $msj .= 'Por favor indique el precio de venta o el margen de venta.<br>'; }
//		if($precio < $costo && ($margen=='' || $margen <= 0 )) { $error = 1; $msj .= 'El precio de venta no puede ser menor al costo.<br>'; }
//		if(($precio < $costo || $precio == '') && $margen > 0) { $precio = round(((($costo * $margen)/100) + $costo), 2) ; }
//		if($precio >= $costo && $costo > 0) { $margen = round(((($precio * 100) / $costo) - 100), 2) ; }
//		if($precio == $costo && $costo == 0) { $margen = $defmarg; }
	} else {
//		$margen = $defmarg;
	}
	if(isset($borrar) && $borrar == '1') {$activo = 0;} else {$activo = 1;}
	if($nombre=='') { $error = 1; $msj .= 'Se requiere el nombre del producto.<br>'; }
//	if($marca=='') { $error = 1; $msj .= 'Se requiere la Marca del producto.<br>'; }
	if($almacen=='') { $error = 1; $msj .= 'Debe seleccionar un almacén.<br>'; }	
	if($tangible=='9') { $error = 1; $msj .= 'Debe seleccionar un tipo de Producto.<br>'; }	
//	if() { $error = 1; $msj .= '<br>'; }

//	if($costo < 0) { $error = 1; $msj .= 'El costo no puede ser menor que cero.<br>'; }

	if($error === 'no') {

		$preg1 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id='" . $prod_id . "'";
//	echo $preg0 . '<br>';
   	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");
		$fila1 = mysql_num_rows($matr1);
		$pact = mysql_fetch_array($matr1);
		if(($pact['prod_nombre'] != $nombre || $pact['prod_marca'] != $marca || $pact['prod_unidad'] != $uniprod || $pact['prod_tangible'] != $tangible) && $nuevo != '1') {
			$sql_data_array = array('prod_id' => $prod_id,
				'tipo' => 10, // Comentarios de cambio de identidad de producto
				'evento' => 'Cambio de ' . $pact['prod_nombre'] . ' ' . $pact['prod_marca'] . ' ' . $pact['prod_unidad'] . ' ' . $pact['prod_tangible'],
				'motivo' => 'A: ' . $nombre . ' ' . $marca . ' ' . $uniprod . ' ' . $tangible,
				'usuario' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');
		}
		if(($pact['prod_precio'] != $precio || $pact['prod_precioint'] != $precioint || $pact['prod_costo'] != $prodcosto) && $nuevo != '1') {
			$sql_data_array = array('prod_id' => $prod_id,
				'tipo' => 20, // Comentarios de cambio de precios de producto
				'evento' => 'Cambio de precios: Venta Público de ' . $pact['prod_precio'] . ' a ' . $precio . ', Venta Interno de ' . $pact['prod_precioint'] . ' a ' . $precioint . ' y Compra de ' . $pact['prod_costo'] . ' a ' . $prodcosto,
				'motivo' => 'Ajuste manual de precios',
				'usuario' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');
		}
		if(($pact['prod_almacen'] != $almacen || $pact['prod_resurtir'] != $resurtir || $pact['prod_local'] != $local) && $nuevo != '1') {
			$sql_data_array = array('prod_id' => $prod_id,
				'tipo' => 30, // Comentarios de cambio de clasificación y ubicación de producto
				'evento' => 'Cambio de ' . $nom_almacen[$pact['prod_almacen']] . ' ' . $pact['prod_resurtir'] . ' ' . $pact['prod_local'],
				'motivo' => 'a ' . $nom_almacen[$almacen] . ' ' . $resurtir . ' ' . $local,
				'usuario' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');
		}
		
		if($accion=='insertar') { $parametros = ''; } else { $parametros = 'prod_id = ' . $prod_id;}
		$sql_data_array = array('prod_codigo' => $codigo,
			'prod_nombre' => $nombre,
			'prod_marca' => $marca,
			'prod_unidad' => $uniprod,
			'prod_tangible' => $tangible,
			'prod_cantidad_existente' => $nvaexist,
			'prod_cantidad_disponible' => $nvadispo,
			'prod_precio' => $precio,
			'prod_precioint' => $precioint,
			'prod_costo' => $prodcosto,
			'prod_almacen' => $almacen,
			'prod_resurtir' => $resurtir,
			'prod_local' => $local,
			'prod_activo' => $activo);
		ejecutar_db($dbpfx . 'productos', $sql_data_array, $accion, $parametros);
		if($accion=='insertar') { $prod_id = mysql_insert_id(); } 
		
		if($neok == 1) {
			$sql_data_array = array('prod_id' => $prod_id,
				'tipo' => 0, // Comentarios de ajuste manual de existencias
				'evento' => 'Ajuste manual de existencias de ' . $existencia . ' a ' . $nvaexist . '.',
				'motivo' => $motivoajuste,
				'usuario' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');
		}
		
		if(isset($cot_prov_id) && $cot_prov_id != '') {
			$duplicado = 0;
			for($i=0;$i < count($cot_prov_id);$i++) {
				if($quitacot[$i] != '1') {
					$parametros = "prod_id = '" . $prod_id. "' AND prod_prov_id = '".$cot_prov_id[$i]."' ";
					$sql_data_array = array('prod_costo' => limpiarNumero($cot_costo[$i]),
						'dias_entrega' => $cot_entrega[$i],
						'dias_credito' => $cot_credito[$i]);
					ejecutar_db($dbpfx . 'prod_prov', $sql_data_array, 'actualizar', $parametros);
					if($cot_prov_id[$i] == $prov_id) { $duplicado = 1;}
				} else {
					$preg1 = "DELETE FROM " . $dbpfx . "prod_prov WHERE prod_id = '" . $prod_id. "' AND prod_prov_id = '" . $cot_prov_id[$i] . "'";
					$resultado = mysql_query($preg1);
				}
			}
		}
		
		if($duplicado == 0 && $prov_id > '0') {
			$parametros = '';
			$sql_data_array = array('prod_prov_id' => $prov_id,
				'prod_costo' => limpiarNumero($costo),
				'dias_entrega' => $entrega,
				'dias_credito' => $credito,
				'prod_id' => $prod_id,
				'fecha_cotizado' => date('Y-m-d H:i:s', time()));
			ejecutar_db($dbpfx . 'prod_prov', $sql_data_array, 'insertar');
		}
		
/*		if($accion=='insertar') {
			$prod_id = mysql_insert_id();
			$concepto = 'Producto ' . $prod_id . ' agregado'; 
		} else {
			$concepto = 'Producto ' . $prod_id . ' modificado';
		}
		bitacora('999999997', $concepto, $dbpfx);
*/

		unset($sql_data_array);
		unset($_SESSION['ref']);
		if($hazped != '') {
			$nvoped = explode(':', $hazped);
			redirigir('refacciones.php?accion=cotpedprod&prod_id=' . $nvoped[0] .'&nvoped1=' . $nvoped[1]);
		}
		redirigir('refacciones.php?accion=listar&prod_id=' . $prod_id);
	} else {
   	$_SESSION['ref']['mensaje'] = $msj;
   	redirigir('refacciones.php?accion=item&prod_id=' . $prod_id . '&nuevo=' . $nuevo);
   }
   
}

elseif($accion==='cotpedprod') {
	
	$funnum = 1115045;
	
//	unset($_SESSION['recibo']);	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	if(isset($prod_id) && $prod_id != '') {

		$preg0 = "SELECT prod_id, prod_marca, prod_nombre, prod_codigo, prod_costo, prod_tangible, prod_cantidad_existente, prod_unidad FROM " . $dbpfx . "productos WHERE prod_activo = '1' AND prod_id = '" . $prod_id . "'";
//		echo $preg0 . '<br>';
	   $matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
   	$filas = mysql_num_rows($matr0);
		$prods = mysql_fetch_array($matr0);
		if($filas > '0') {
			$_SESSION['cotped']['prod_id'][] = $prods['prod_id']; 
			$_SESSION['cotped']['prod_marca'][] = $prods['prod_marca']; 
			$_SESSION['cotped']['prod_nombre'][] = $prods['prod_nombre']; 
			$_SESSION['cotped']['prod_codigo'][] = $prods['prod_codigo'];
			$_SESSION['cotped']['prod_tangible'][] = $prods['prod_tangible']; 
			$_SESSION['cotped']['prod_unidad'][] = $prods['prod_unidad']; 
			$_SESSION['cotped']['prod_cantidad_existente'][] = $prods['prod_cantidad_existente']; 
		}
		if($nvoped1 != '') {
			$preg1 = "SELECT prod_costo FROM " . $dbpfx . "prod_prov WHERE prod_prov_id = '$nvoped1' AND prod_id = '" . $prod_id . "'";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de cotizaciones de productos!".$preg1);
			$cotprov = mysql_fetch_array($matr1);
			$_SESSION['cotped']['prod_costo'][] = $cotprov['prod_costo'];
		} else {
			$_SESSION['cotped']['prod_costo'][] = $prods['prod_costo'];
		}
	}

	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	
	echo '	<form action="refacciones.php?accion=gestprod" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Cotizaciones y Pedidos de Refacciones y Consumibles</td></tr>'."\n";
	echo '			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td style="width:300px;">Nombre</td><td style="width:120px;">Marca</td><td style="width:100px;">Código</td><td style="width:20px;">Existencias</td><td style="width:20px;">Unidad</td><td style="width:20px;">Cantidad</td><td style="width:75px;">Costo Unitario</td><td>Acciones</td></tr>'."\n";
	$cue = 0;
	foreach($_SESSION['cotped']['prod_id'] as $k => $v) {
		echo '					<tr>
						<td><a href="refacciones.php?accion=item&prod_id=' . $v . '">' . $_SESSION['cotped']['prod_nombre'][$k] . '</a><input type="hidden" name="nombre[' . $k . ']" value="' . $_SESSION['cotped']['prod_nombre'][$k] . '" /></td>
						<td>' . $_SESSION['cotped']['prod_marca'][$k] . '<input type="hidden" name="prod_id[' . $k . ']" value="' . $v . '" /></td>
						<td>' . $_SESSION['cotped']['prod_codigo'][$k] . '<input type="hidden" name="codigo[' . $k . ']" value="' . $_SESSION['cotped']['prod_codigo'][$k] . '" /></td>
						<td style="text-align:right;"><input type="hidden" name="existente[' . $k . ']" value="' . $_SESSION['cotped']['prod_cantidad_existente'][$k] . '"><input type="hidden" name="tangible[' . $k . ']" value="' . $_SESSION['cotped']['prod_tangible'][$k] . '" />' . $_SESSION['cotped']['prod_cantidad_existente'][$k] . '</td>
						<td style="text-align:right;">' . $_SESSION['cotped']['prod_unidad'][$k] . '</td>
						<td><input type="text" name="cantidad[' . $k . ']" size="4" value="' . $_SESSION['cotped']['cantidad'][$k] . '" style="text-align:right;"></td>
						<td style="text-align:right;"><input type="text" name="costo[' . $k . ']" size="11" value="' . money_format('%n', $_SESSION['cotped']['prod_costo'][$k]) . '" style="text-align:right;"></td>
						<td>Quitar<input type="checkbox" name="quitar[' . $k . ']" value="1"></td></tr>'."\n";
		$cue++;
	}
	echo '				</table>'."\n";
	echo '			</td>
		</tr>'."\n";

	echo '		<tr><td colspan="2" style="text-align:left;"><a href="refacciones.php?accion=listar">Agregar más productos</a></td></tr>'."\n";
	echo '		<tr><td style="text-align:left; width:150px;">Proveedor</td><td style="text-align:left; width:85%;"><select name="prov_selec[]" multiple="multiple" size="4"/>'."\n";
	foreach($provs as $k => $v) {
		echo '			<option value="' . $k . '"';
		if($k == $nvoped1) { echo ' selected="selected" '; }  // haciendo pedido desde item
		echo '>' . $v['nic'] . '</option>'."\n";
	}
	echo '		</select></td></tr>'."\n";
	echo '		<tr><td style="text-align:left; width:150px;">Tipo de Solicitud</td><td style="text-align:left; width:85%;">';
	echo '<select name="tipo_pedido" /><option value="">Seleccionar...</option>'."\n";
	echo '			<option value="2">' . TIPO_PEDIDO_2 . '</option>'."\n";
	echo '			<option value="3">' . TIPO_PEDIDO_3 . '</option>'."\n";
	echo '			<option value="10">' . TIPO_PEDIDO_10 . '</option>'."\n";
	echo '		</select></td></tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;"><input type="hidden" name="bodega" value="1" /><input name="enviar" value="Enviar" type="submit">&nbsp;<button name="limpiar" value="limpiar">Eliminar Partidas</button></td></tr>'."\n";
	echo '		<tr><td colspan="2"><hr></td></tr>'."\n";
	echo '	</table></form>'."\n";

}

elseif($accion==="gestprod") {
	
	$funnum = 1115005;
	$funnum = 1115010;
	$funnum = 1115015;
	$funnum = 1115020;
	$funnum = 1115025;

	if ($_SESSION['rol08']=='1') {
		$mensaje = '';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	
	if(isset($limpiar) && $limpiar=='limpiar') { 
		unset ($_SESSION['cotped']);
		redirigir('refacciones.php?accion=cotpedprod');
	}
	
	$error = 'no';

	if($tipo_pedido >= '2' && $tipo_pedido <= '3') {
		$cuantos_prov = count($prov_selec);
		if($cuantos_prov > 1) { $mensaje .= 'Seleccione SOLO UN proveedor para pedido.<br>'; $error = 'si'; }
		if($cuantos_prov < 1) { $mensaje .= 'Seleccione al menos UN proveedor para pedido.<br>'; $error = 'si'; }
	}
	if($tipo_pedido < 1) { $mensaje .= 'Seleccione el tipo de solicitud.<br>'; $error = 'si'; }

	foreach($prod_id as $j => $w) {
		$cantidad[$j] = limpiarNumero($cantidad[$j]);
		if($cantidad[$j] <= 0 && $quitar[$j] != '1') { 
			$mensaje .= 'La cantidad del producto ' . $nombre[$j] . ' es menor o igual a CERO.<br>'; $error = 'si';
		}
	}
	
	
	$j=0;

	if($error == 'no') {
		$j=0;
		if($tipo_pedido < 4) {
/* ------------ Crear pedidos de almacén --------------------*/
			$prov_id = $prov_selec[0];
			$subtotal = 0; 
			foreach($prod_id as $j => $w) {
				$cantidad[$j] = limpiarNumero($cantidad[$j]);
				$subtotal = $subtotal + $cantidad[$j];
			}
			$iva = round(($subtotal * $impuesto_iva), 2);
			$sql_array = array('prov_id' => $prov_id,
				'orden_id' => '999999997',
				'pedido_tipo' => $tipo_pedido,
				'subtotal' =>  $subtotal,
				'impuesto' => $iva,
				'usuario_pide' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'pedidos', $sql_array, 'insertar');
			$pedido = mysql_insert_id();
			$fpromped = dia_habil($provs[$prov_id]['dde']);
			$param = "pedido_id = '" . $pedido . "'";
			$sql_data = array('fecha_promesa' => $fpromped);
			$sql_data['pedido_estatus'] = 5;
			$sql_data['fecha_pedido'] = date('Y-m-d H:i:s');
			ejecutar_db($dbpfx . 'pedidos', $sql_data, 'actualizar', $param);
			foreach($prod_id as $j => $w) {
				if($quitar[$j] != '1') {
					$costoprod = limpiarNumero($costo[$j]);
					$cantprod = limpiarNumero($cantidad[$j]);
					$sql_data = array('prod_id' => $w,
						'op_codigo' => $codigo[$j],
						'op_nombre' => $nombre[$j],
						'sub_orden_id' => '999999997',
						'op_pedido' => $pedido,
						'op_cantidad' => $cantprod,
						'op_costo' => $costoprod,
						'op_tangible' => $tangible[$j],
						'op_autosurtido' => $tipo_pedido);
					ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'insertar');
					$preg1 = "UPDATE " . $dbpfx . "productos SET prod_cantidad_pedida = prod_cantidad_pedida + '" . $cantprod . "' WHERE prod_id = '$w'"."\n";
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo actualización de productos!".$preg1);
				}
			}
			$acargo = constant('TIPO_PEDIDO_'.$tipo_pedido);
			$empresa = $provs[$prov_id]['nombre'];
			$contacto = $provs[$prov_id]['contacto'];
			$para = $provs[$prov_id]['email'];
			$enviar_prov = $provs[$prov_id]['env'];
			$asunto = 'Pedido ' . $pedido . ' de ' . $agencia;
			$texto_t_solicitud = 'Pedido';
		} elseif($tipo_pedido == '10') {
			$para =''; $cuenta = 0;
			foreach($prov_selec as $k) {
				if($provs[$k]['env'] == '1') {$enviar_prov = 1;}
				foreach($prod_id as $j => $w) {
					$preg1 = "SELECT prod_id FROM " . $dbpfx . "prod_prov WHERE prod_id = '$w' AND prod_prov_id = '$k'";
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos a proveedores!");
					$fila1 = mysql_num_rows($matr1);
					$sql_data = array(
						'prod_id' => $w, 
						'prod_prov_id' => $k,
						'prod_costo' => NULL,
						'dias_entrega' => NULL,
						'dias_credito' => NULL,
						'fecha_cotizado' => date('Y-m-d H:i:s', time())
					);
					if($fila1 > 0) { 
						$param = "prod_id = '$w' AND prod_prov_id = '$k'";
						ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'actualizar', $param);
					} else { 
						ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'insertar');
					}
				}
			}
			$asunto = 'Cotización para ' . $agencia;
			$texto_t_solicitud = 'Cotizar';
		}
		
/* ------------ Enviar por e-mail pedidos a Proveedores --------------------*/
//		echo ' Por enviar correo!';
		if($enviar_prov == '1') {
						
			require ('parciales/PHPMailerAutoload.php');

			$mail = new PHPMailer;

			$mail->CharSet = 'UTF-8';
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $smtphost;  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = $smtpusuario;                 // SMTP username
			$mail->Password = $smtpclave;                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
			$mail->Port       = $smtppuerto; 

			$mail->From = constant('EMAIL_PROVEEDOR_FROM');
			$mail->FromName = 'Refacciones de ' . $nombre_agencia;
			
			if($tipo_pedido < '4') {
				$ma = explode(',', $provs[$prov_id]['email']);
				foreach($ma as $k) {
					$mail->addAddress($k);     // Add a recipient
				}
			} else {
				foreach($prov_selec as $k) {
					$ma = explode(',', $provs[$k]['email']); 
					foreach($ma as $j) {
						$mail->addAddress($j);     // Add a recipient
					}
				}
			} 
//			$mail->addAddress($para);     // Add a recipient

			$mail->addReplyTo(constant('EMAIL_PROVEEDOR_RESPONDER'));

			$ma = explode(',', constant('EMAIL_PROVEEDOR_CC'));
			foreach($ma as $k) {
				$mail->addCC($k);     // Add a recipient
			}
//			$mail->addCC(constant('EMAIL_PROVEEDOR_CC'));

			if($bcc) { $mail->addCC($bcc);}
			$mail->addBCC('monitoreo@controldeservicio.com');

			$email_order = '<!DOCTYPE HTML PUBLIC \'-//W3C//DTD HTML 4.0 Transitional//EN\'><html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" /></head><body style="font-family:Arial;">';
			if($tipo_pedido < 4) {
				$email_order .= '<p>Estimad@ ' . $contacto . '<br>' . $empresa . '</p>'."\n";
				$email_order .= '<p>' . EMAIL_TEXT_DESCRIPCION . '</p>'."\n";
			} else {
				$email_order .= '<p>Estimados Proveedores.</p>'."\n";
				$email_order .= '<p>' . EMAIL_TEXT_COTIZACION . '</p>'."\n";
			}

			$email_order .= '<table cellpadding="2" cellspacing="0" border="1" width="800"><tr><td colspan="2">' . $nombre_agencia . '</td><td><strong>' . $texto_t_solicitud . ': </strong>' . $pedido . '</td><td><strong>';
			$email_order .= $acargo;
			$email_order .= '</strong></td><td>Fecha: ' . date('Y-m-d') . '</td></tr>'."\n";
			$email_order .= '</table>'."\n";
			$email_order .= '<table cellpadding="2" cellspacing="0" border="1" width="800"><tr><td>Cantidad</td><td>Nombre</td><td>Código</td></tr>';
			foreach($prod_id as $j => $w) {
				if($quitar[$j] != '1') {
					$cantprod = limpiarNumero($cantidad[$j]);
					$email_order .= '<tr><td>' . $cantprod . '</td><td>' . $nombre[$j] . '</td><td>' . $codigo[$j] . '</td></tr>'."\n";
				}
			}

			$email_order .= '</table>'."\n";
			$email_order .= '<p>Atentamente.<br><br>' . JEFE_DE_ALMACEN . '<br>'.$agencia_razon_social."<br>".$agencia_direccion."<br>Col. ".$agencia_colonia.", ".$agencia_municipio."<br>C.P.: ".$agencia_cp.". ".$agencia_estado."<br>E-mail: ".EMAIL_DE_ALMACEN."<br>Tels: ".$agencia_telefonos.'<br>' . TELEFONOS_ALMACEN . '</p>';
			$email_order .= '</body></html>';

			$mail->isHTML(true);                                  // Set email format to HTML

			$mail->Subject = $asunto;
			$mail->Body    = $email_order;
//			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			if(!$mail->send()) {
				$mensaje = 'Errores en notificación automática: ';
				$mensaje .=  $mail->ErrorInfo;
	   		$_SESSION['msjerror'] = $mensaje;
			} 
		}
//		echo $email_order;
		
		unset($_SESSION['cotped']); 
		if($tipo_pedido < 4) {
			redirigir('pedidos.php?accion=consultar&pedido=' . $pedido);
		}
		redirigir('refacciones.php?accion=cotpedprod');
	} else {
   	$_SESSION['msjerror'] = $mensaje;
   	redirigir('refacciones.php?accion=cotpedprod');
   }
}

elseif($accion==='listpaqs') {
	
	$funnum = 1115055;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	
	echo '	<form action="refacciones.php?accion=listpaqs" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td colspan="2"><span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span></td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Paquetes de Servicio</td></tr>'."\n";
	echo '		<tr class="obscuro"><td style="text-align:left; width:50%;">Buscar por codigo: <input type="text" name="codigo" id="codigo" size="15">
			<input name="Enviar" value="Enviar" type="submit"></td><td style="width:50%;">Buscar por nombre: <input type="text" name="nombre" size="15">
			<input name="Enviar" value="Enviar" type="submit"></td></tr>
		<tr class="obscuro"><td style="text-align:left;">Filtrar por Área de Servicio: 
			<select name="area" size="1">
				<option value="">Seleccionar...</option>'."\n";
	for($i=1;$i<=$num_areas_servicio;$i++) {
		echo '				<option value="' . $i . '">' . constant('NOMBRE_AREA_' . $i) . '</option>'."\n";
	}											
	echo '			</select>
			<input name="Enviar" value="Enviar" type="submit">';
	echo '		</td><td><a href="refacciones.php?accion=paquete&nuevo=1">Nuevo Paquete</a></td></tr></table></form>'."\n";
	if((isset($area) && $area!='') || (isset($nombre) && $nombre!='') || (isset($codigo) && $codigo!='')) { 
		if(isset($area) && $area!='') { $preg .= "AND paq_area='" . $area . "' "; }
		if(isset($nombre) && $nombre!='') { $preg .= "AND paq_nombre LIKE '%" . $nombre . "%' "; }
		if(isset($codigo) && $codigo!='') { $preg .= "AND paq_nic LIKE '%" . $codigo . "%' "; }
	}
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	$preg0 = "SELECT paq_id FROM " . $dbpfx . "paquetes WHERE paq_activo='1' ";
	$preg0 = $preg0 . $preg;
//	echo $preg0 . '<br>';
   $matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
   $filas = mysql_num_rows($matr0);
	   $renglones = 20;
	   $paginas = (round(($filas / $renglones) + 0.49999999) - 1);
   	if(!isset($pagina)) { $pagina = 0;}
	   $inicial = $pagina * $renglones;
//	echo $paginas;
		$preg1 = "SELECT * FROM " . $dbpfx . "paquetes WHERE paq_activo = '1' ";
		$preg1 = $preg1 . $preg;
		$preg1 .= "ORDER BY paq_area, paq_nombre LIMIT " . $inicial . ", " . $renglones;
//	echo $preg1;
   	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de paquetes!");

		echo '			<tr><td colspan="2"><a href="refacciones.php?accion=listpaqs&pagina=0">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="refacciones.php?accion=listpaqs&pagina=' . $url . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="refacciones.php?accion=listpaqs&pagina=' . $url . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="refacciones.php?accion=listpaqs&pagina=' . $paginas . '">Ultima</a>';
		echo '</td></tr>'."\n";
		echo '			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td style="width:120px;">Área</td><td style="width:250px;">Nombre</td><td style="width:40px;">Nic</td><td>Acciones</td></tr>'."\n";
		$cue = 0;
		while($paq = mysql_fetch_array($matr1)) {
			echo '					<tr><td>' . constant('NOMBRE_AREA_'.$paq['paq_area']) . '</td><td>' . $paq['paq_nombre'] . '</td><td>' . $paq['paq_nic'] . '</td><td><a href="refacciones.php?accion=paquete&paq_id=' . $paq['paq_id'] . '">Listar</a> / <a href="refacciones.php?accion=paquete&paq_id=' . $paq['paq_id'] . '&quitar=1">Remover</a></td></tr>'."\n";
			$cue++;
		}
		echo '				</table>'."\n";
		echo '			</td>
		</tr>'."\n";
		echo '			<tr><td colspan="2"><a href="refacciones.php?accion=listpaqs&pagina=0">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="refacciones.php?accion=listpaqs&pagina=' . $url . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="refacciones.php?accion=listpaqs&pagina=' . $url . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="refacciones.php?accion=listpaqs&pagina=' . $paginas . '">Ultima</a>';
		echo '</td></tr>'."\n";
	echo '		<tr><td colspan="2"><hr></td></tr>'."\n";
	echo '	</table>'."\n";
}

elseif($accion==='paquete') {
	
	$funnum = 1115060;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
		
	if($paq_id!='') {
		$preg0 = "SELECT * FROM " . $dbpfx . "paquetes WHERE paq_id='" . $paq_id . "'";
//	echo $preg0 . '<br>';
   	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
	   $paq = mysql_fetch_array($matr0);
	}
	
	echo '	<form action="refacciones.php?accion=';
	if($nuevo=='1') { echo 'inspaq';} else { echo 'actpaq';}
	echo '" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="izquierda">
		<tr><td colspan="2"><span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span></td></tr>'."\n";
	echo '		<tr><td>Nombre</td><td><input type="text" name="nombre" size="40" maxlength="255" value="'; 
	echo ($_SESSION['ref']['nombre']) ? $_SESSION['ref']['nombre']:$paq['paq_nombre']; echo '" /></td></tr>'."\n";
	echo '		<tr><td>Descripción</td><td><input type="text" name="descripcion" size="40" maxlength="255" value="'; 
	echo ($_SESSION['ref']['descripcion']) ? $_SESSION['ref']['descripcion']:$paq['paq_descripcion']; echo '" /></td></tr>'."\n";
	echo '		<tr><td>Nic</td><td><input type="text" name="nic" size="40" maxlength="32" value="'; 
	echo ($_SESSION['ref']['nic']) ? $_SESSION['ref']['nic']:$paq['paq_nic']; echo '" /></td></tr>'."\n";
	echo '		<tr><td>Area Padre:</td><td><select name="areapadre" size="1">
					<option value="">Seleccionar...</option>'."\n";
				for($i=1;$i<=$num_areas_servicio;$i++) {
					echo '					<option value="'.$i.'" '; if($paq['paq_area']==$i) {echo 'selected="selected" ';} echo '>' . constant('NOMBRE_AREA_'.$i) . '</option>'."\n";
				} 
				echo '		</select></td></tr>'."\n";
	if($nuevo!='1') {
		echo '		<tr><td style="text-align:right;">Productos de<br>este Paquete<br><br><a href="refacciones.php?accion=listar&paquete=' . $paq_id . '">Agregar Nuevo<br>Producto</a></td><td>'."\n";
		echo '				<table cellpadding="2" cellspacing="0" border="1" class="izquierda">'."\n";
		echo '					<tr><td colspan="7">Primero agregue cada una de las refacciones, consumibles y mano de obra que integraran el paquete y<br>después indique a que área serán asignadas así como su cantidad.</td></tr>'."\n";
		echo '					<tr><td>Area</td><td>Nombre</td><td>Marca</td><td>Código</td><td>Cant</td><td style="text-align:right;">Precio</td><td>Acciones</td></tr>'."\n";
		$preg1 = "SELECT pc_id, pc_prod_id, pc_prod_cant, pc_area_id FROM " . $dbpfx . "paq_comp WHERE pc_paq_id='" . $paq_id . "'";
//	echo $preg1 . '<br>';
  		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");
   	while($pc = mysql_fetch_array($matr1)) {
			$preg2 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id='" . $pc['pc_prod_id'] . "'";
//			echo $preg2 . '<br>';
  			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos!");
  			while($prod = mysql_fetch_array($matr2)) {
  				echo '					<tr><td><select name="area['.$pc['pc_id'].']" size="1">
					<option value="">Seleccionar...</option>'."\n";
				for($i=1;$i<=$num_areas_servicio;$i++) {
					echo '					<option value="'.$i.'" '; if($pc['pc_area_id']==$i) {echo 'selected="selected" ';} echo '>' . constant('NOMBRE_AREA_'.$i) . '</option>'."\n";
				} 
				echo '		</select></td><td>'.$prod['prod_nombre'].'</td><td>'.$prod['prod_marca'].'</td><td>'.$prod['prod_codigo'].'</td><td style="text-align:center;"><input type="text" name="cantidad['.$pc['pc_id'].']" size="1" value="'.$pc['pc_prod_cant'].'"></td><td style="text-align:right;">'. money_format('%n', $prod['prod_precio']) .'</td><td>Quitar <input type="checkbox" name="quitar['.$pc['pc_id'].']" value="1"></td></tr>'."\n";
	  		}
   	}
		echo '				</table></td></tr>'."\n";
	
		echo '		<tr><td>Borrar Paquete?</td><td  style="text-align:left;"><input type="checkbox" name="borrar" value="1"';
		echo ' /></td></tr>';
	} 
	echo '		<input type="hidden" name="paq_id" value="' . $paq['paq_id'] . '" /><input type="hidden" name="nuevo" value="' . $nuevo . '" />';
	echo '		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>
	</table>
	</form>';
}

elseif($accion==="inspcpaq") {
	
	$funnum = 1115065;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Rol Almacén, ingresar Usuario y Clave correcta.');
	}
	$sql_data_array = array('pc_paq_id' => $paquete,
		'pc_prod_id' => $prod_id,
		'pc_prod_cant' => '1');
	ejecutar_db($dbpfx . 'paq_comp', $sql_data_array, 'insertar');
	bitacora('999997', 'Nuevo producto '.$prod_id.' agregado al paquete '.$paquete, $dbpfx);
	unset($sql_data_array);
	unset($_SESSION['ref']);
	redirigir('refacciones.php?accion=paquete&paq_id=' . $paquete);
}

elseif($accion==="inspaq" || $accion==="actpaq") {
	
	$funnum = 1115065;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Rol Almacén, ingresar Usuario y Clave correcta.');
	}
	unset($_SESSION['ref']);
	$_SESSION['ref'] = array();
	$mensaje = '';
	$error = 'no'; 

	$nombre = preparar_entrada_bd($nombre); $_SESSION['ref']['nombre']=$nombre;
	$descripcion = preparar_entrada_bd($descripcion); $_SESSION['ref']['nombre']=$descripcion;
	$nic = preparar_entrada_bd($nic); $_SESSION['ref']['nic']=$nic;
//	$area = preparar_entrada_bd($area); $_SESSION['ref']['area']=$area;
	
	if(isset($borrar) && $borrar == '1') {$activo = 0;} else {$activo = 1;}
	if($nombre=='') { $error = 1; $msj .= 'Se requiere el nombre del paquete.<br>'; }
	if($descripcion=='') { $error = 1; $msj .= 'Se requiere la descripción del paquete.<br>'; }
	if($nic=='') { $error = 1; $msj .= 'Se requiere asignar un nombre corto o nic.<br>'; }
//	if($area=='') { $error = 1; $msj .= 'Debe seleccionar una área de servicio en que se utilizará este paquete.<br>'; }
		
	
	if($error === 'no') {
		if($accion=='inspaq') { $parametros = ''; $modifica = 'insertar'; } else { $parametros = 'paq_id = ' . $paq_id; $modifica = 'actualizar';} 
		$sql_data_array = array('paq_nombre' => $nombre,
			'paq_descripcion' => $descripcion,
			'paq_area' => $areapadre,
			'paq_nic' => $nic,
			'paq_activo' => $activo);
		ejecutar_db($dbpfx . 'paquetes', $sql_data_array, $modifica, $parametros);
		unset($sql_data_array);
		if($accion=='inspaq') {
			$paq_id = mysql_insert_id();
			$concepto = 'Paquete ' . $paq_id . ' agregado'; 
		} else {
			foreach($cantidad as $k => $v) {
				$preg0 = "UPDATE " . $dbpfx . "paq_comp SET pc_prod_cant = '" . $v . "', pc_area_id = '" . $area[$k] . "' WHERE pc_id = '" . $k . "'";
//				echo $preg0 . '<br>';
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo actualización de productos!");
			}
			foreach($quitar as $k => $v) {
				if($v == '1') { 
					$preg0 = "DELETE FROM " . $dbpfx . "paq_comp WHERE pc_id = '" . $k . "'";
//						echo $preg0 . '<br>';
					$matr0 = mysql_query($preg0) or die("ERROR: Fallo remoción de productos!");
				}
			} 
			$concepto = 'Paquete ' . $paq_id . ' modificado';
		}
		bitacora('999998', $concepto, $dbpfx);
		unset($_SESSION['ref']);
		redirigir('refacciones.php?accion=listpaqs');
	} else {
   	$_SESSION['ref']['mensaje'] = $msj;
   	redirigir('refacciones.php?accion=paquete&prod_id=' . $paq_id . '&nuevo=' . $nuevo);
   }
   
}

elseif($accion==='retorno') {
	
	$funnum = 1115075;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	
	echo '	<form action="refacciones.php?accion=recibo" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td colspan="2"><span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span></td></tr>'."\n";
	unset($_SESSION['ref']['mensaje']);
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Retorno de Refacciones y Consumibles al Almacén</td></tr>'."\n";
	echo '			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td style="width:120px;">Marca</td><td style="width:100px;">Código</td><td style="width:300px;">Nombre</td><td style="width:30px;">Existencias</td><td style="width:75px;">Costo</td><td style="width:30px;">Recibir</td><td>Acciones</td></tr>'."\n";
	$cue = 0;
	foreach($_SESSION['recibo']['prod_id'] as $k => $v) {
		echo '					<tr>
						<td>' . $_SESSION['recibo']['prod_marca'][$k] . '<input type="hidden" name="prod_id[' . $k . ']" value="' . $v . '" /></td>
						<td>' . $_SESSION['recibo']['prod_codigo'][$k] . '</td>
						<td>' . $_SESSION['recibo']['prod_nombre'][$k] . '</td>
						<td style="text-align:right;"><input type="hidden" name="existente[' . $k . ']" value="' . $_SESSION['recibo']['prod_cantidad_existente'][$k] . '">' . $_SESSION['recibo']['prod_cantidad_existente'][$k] . '</td>
						<td style="text-align:right;"><input type="text" name="costo[' . $k . ']" size="11" value="' . money_format('%n', $_SESSION['recibo']['prod_costo'][$k]) . '" style="text-align:right;"></td>
						<td><input type="text" name="recibir[' . $k . ']" size="4" style="text-align:right;"></td>
						<td>Quitar<input type="checkbox" name="quitar[' . $k . ']" value="1"></td></tr>'."\n";
		$cue++;
	}
	echo '				</table>'."\n";
	echo '			</td>
		</tr>'."\n";

	$preg1 = "SELECT prov_id, prov_nic FROM " . $dbpfx . "proveedores WHERE prov_activo='1'";
//	echo $preg0 . '<br>';
   $matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de proveedores!");

	echo '		<tr><td colspan="2" style="text-align:left;"><a href="refacciones.php?accion=listar">Agregar más productos</a></td></tr>'."\n";
	echo '		<tr><td style="text-align:left; width:150px;">Proveedor</td><td style="text-align:left; width:85%;"><select name="prov_id" size="1">
			<option value="">Seleccionar...</option>'."\n";
	while($prov = mysql_fetch_array($matr1)) {
		echo '			<option value="'.$prov['prov_id'].'" '; if($prod['prod_prov_id']==$prov['prov_id']) {echo 'selected="selected" ';} echo '>'.$prov['prov_nic'].'</option>'."\n";
	}
	echo '		</select></td></tr>'."\n";
	echo '		<tr><td style="text-align:left; width:150px;">Número de factura: </td><td style="text-align:left;"><input type="text" name="factura" size="20"></td></tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;"><input name="enviar" value="Recibir" type="submit"></td></tr>'."\n";
	echo '		<tr><td colspan="2"><hr></td></tr>'."\n";
	echo '	</table></form>'."\n";

}

elseif($accion==="pventa") {
	
	$funnum = 1115080;
	
	if ($_SESSION['rol02']=='1' || $_SESSION['rol12']=='1') {
		$msj='Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	unset($_SESSION['ref']);
	$_SESSION['ref'] = array();
	$_SESSION['ref']['mensaje']='';
	$mensaje = '';
	$error = 'no';

	if($error == 'no') {
		$pv_rev = 1;
		foreach($op_id as $j => $w) {
			$precio[$j] = limpiarNumero($precio[$j]);
			$param = "op_id = '" . $w . "'";
			if($op_precio_revisado[$j] < '1') {
				$sql_data['op_precio_original'] = $op_precio_original[$j]; 
			}
			if($precio[$j] > 0) { 
				$sql_data['op_precio_revisado'] = '1'; 
				$sql_data['op_precio'] = $precio[$j];
				ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
				$concepto = 'Precio Revisado en OP:' . $w;
				bitacora($orden_id, $concepto, $dbpfx);
			}
		}
		
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id); 
	} else {
   	$_SESSION['ref']['mensaje'] = $mensaje;
   	redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id);
   }
}

elseif($accion==="imprimelista") {
	
	$funnum = 1115085;
	
	$preg0 = "SELECT sub_orden_id, sub_area, sub_estatus, sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
	$preg3 = "SELECT orden_fecha_promesa_de_entrega, orden_asesor_id FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
	$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de Ordenes de Trabajo!");
	$ord = mysql_fetch_array($matr3);
	$preg4 = "SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '" . $ord['orden_asesor_id'] . "'";
	$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de Asesor!");
	$usr = mysql_fetch_array($matr4);
	$veh = datosVehiculo($orden_id, $dbpfx);
	echo '	<table cellpadding="0" cellspacing="0" border="0" width ="800">'."\n";
	echo '	<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div></td></tr>'."\n";
	echo '	<tr><td colspan="2">'."\n";	
	echo '	<table cellpadding="2" cellspacing="1" border="1" width ="800">';
	echo '		<tr class="cabeza_tabla"><td colspan="5">Listado de refacciones para la Orden de Trabajo: ' . $orden_id . ' Vehículo: ' . $veh['completo'] . '<br>Fecha Promesa de Entrega: ' . $ord['orden_fecha_promesa_de_entrega'] . '. Asesor: ' . $usr['nombre'] . ' ' . $usr['apellidos'] . '.</td></tr>'."\n";
	echo '		<tr><td align="center">Cant</td><td>Nombre</td><td>Código</td><td>Proveedor</td><td>Firma de recibido y otros comentarios</td></tr>'."\n";
	while ($sub = mysql_fetch_array($matr0)) {
		$encabezaref = '		<tr><td colspan="5" style="text-align:center;">Refacciones de ' . constant('NOMBRE_AREA_' . $sub['sub_area'] ) . ' Tarea: ' . $sub['sub_orden_id'] . ' para ';
		if($sub['sub_aseguradora'] > '0') { $encabezaref .= 'aseguradora ' . $asegnic[$sub['sub_aseguradora']] . '.'; }
		else { $encabezaref .= 'cliente particular.'; }
		$encabezaref .= '</td></tr>'."\n";
		$preg1 = "SELECT op_id, op_cantidad, op_nombre, op_codigo, op_pedido, op_pres FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_tangible = '1'";
     	// echo $preg1;
		if($pidepres == '1') {
			$preg1 .= " AND (op_pres = '1' OR op_pedido > '0')"; 
		}
		$preg1 .= " ORDER BY op_nombre";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");
		$f2 = mysql_num_rows($matr1);
		if($f2 > 0) {
			echo $encabezaref;
			$fondo = 'claro';
			while($prods = mysql_fetch_array($matr1)) {
				$preg2 = "SELECT prov_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $prods['op_pedido'] . "' LIMIT 1";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de pedido!");
				$prov = mysql_fetch_array($matr2);
				echo '		<tr class=' . $fondo . '><td align="center">' . $prods['op_cantidad'] . '</td><td>' . $prods['op_nombre'] . '</td><td>' . $prods['op_codigo'] . '</td><td>';
				if($prov['prov_id'] > '0') {
					echo $provs[$prov['prov_id']]['nic']; 
				}
				echo '</td><td>&nbsp;</td></tr>'."\n";
				if($fondo == 'claro') {$fondo = 'obscuro';} else  {$fondo = 'claro';}
			}
		}
		$encabezaref = '';
	}
	echo '	</table></td></tr>'."\n";
	echo '		<tr><td colspan="2"><div class="control"><a href="javascript:window.print();"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="Imprimir Listado" title="Imprimir Listado"></a> &nbsp; <a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div></td></tr>'."\n";
	echo '	</table>'."\n";
}

elseif($accion==="cargacotiza") {

	$funnum = 1115090;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || ($soloautorizadas != '1' && ($solovalacc != '1' && ($_SESSION['rol02']=='1' || $_SESSION['rol08']=='1')))) {
		$msj='Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	$veh = datosVehiculo($orden_id, $dbpfx);
	$preg0 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op_id . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección OP!".$preg0);
	$op = mysql_fetch_array($matr0);
	$preg1 = "SELECT * FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $op_id . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección Prod-Prov!");
	$resu1 = mysql_num_rows($matr1);
	if($resu1 > 0) {
		echo '		<form action="refacciones.php?accion=guardacotiza" method="post" enctype="multipart/form-data">'."\n";
		echo '		<table cellpadding="2" cellspacing="0" border="1">'."\n";
		echo '		'."\n";
		echo '			<tr class="cabeza_tabla"><td colspan="7">Captura de Cotizaciones para Cantidad: ' . $op['op_cantidad'] . '. Descripción: ' . $op['op_nombre'] . '.<br>del vehículo ' . $veh['completo'] . ' de la OT ' . $orden_id . '</td></tr>'."\n";
		echo '			<tr><td style="text-align:left;">Proveedor</td><td style="text-align:left;">Fecha de<br>Cotización</td><td style="text-align:left;">Costo Unitario</td><td style="text-align:left;">Costo Total</td><td style="text-align:left;">Días<br>Crédito</td><td style="text-align:left;">Días de<br>Entrega</td><td style="text-align:center;">Cancelar Cotización</td></tr>'."\n";
		$j = 0;
		while($prov3 = mysql_fetch_array($matr1)) {
			echo '			<tr><td>' . $provs[$prov3['prod_prov_id']]['nic'] . '
			<input type="hidden" name="op_id[' . $j . ']" value="' . $op_id . '" />
			<input type="hidden" name="prov_id[' . $j . ']" value="' . $prov3['prod_prov_id'] . '" />
			</td>';
			$sbt = $op['op_cantidad'] * $prov3['prod_costo'];
			echo '<td>' . date('d-m-Y', strtotime($prov3['fecha_cotizado'])) . '</td>';
			echo '<td><input style="text-align:right;" type="text" name="costo[' . $j . ']" value="' . $prov3['prod_costo'] . '" size="4" /></td>';
			echo '<td><input style="text-align:right;" type="text" name="sbt[' . $j . ']" value="' . $sbt . '" size="4" /></td>';
			echo '<td><input style="text-align:right;" type="text" name="credito[' . $j . ']" value="' . $prov3['dias_credito'] . '" size="4" /></td>';
			echo '<td><input style="text-align:right;" type="text" name="entrega[' . $j . ']" value="' . $prov3['dias_entrega'] . '" size="4" /></td>';
			echo '<td style="width:60px;text-align:center;"><input type="radio" name="cancela[' . $j . ']" value="1" /></td></tr>'."\n";
			$j++;
		}
		echo '			<tr><td colspan="7"><input type="submit" name="enviar" value="Aplicar">
				<input type="hidden" name="orden_id" value="' . $orden_id . '" />
				<input type="hidden" name="op_cantidad" value="' . $op['op_cantidad'] . '" />
			</td></tr>'."\n";
		echo '		</table></form>'."\n";
	} else {
		echo 'No hay cotizaciones disponibles para este producto';
	}
}

elseif($accion==="guardacotiza") {

	$funnum = 1115090;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || ($solovalacc != '1' && ($_SESSION['rol02']=='1' || $_SESSION['rol08']=='1'))) {
		$msj='Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

//	print_r($op_id);

	foreach($op_id as $k => $v) {
		$costo[$k] = limpiarNumero($costo[$k]);
		$sbt[$k] = limpiarNumero($sbt[$k]);
		$credito[$k] = intval(limpiarNumero($credito[$k]));
		$entrega[$k] = intval(limpiarNumero($entrega[$k]));
		$preg1 = "SELECT * FROM " . $dbpfx . "prod_prov WHERE op_id = '$v' AND prod_prov_id = '" . $prov_id[$k] . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección Prod-Prov!");
		$cot = mysql_fetch_array($matr1);
		if($sbt[$k] != ($op_cantidad * $costo[$k]) && $sbt[$k] != 0) {
			$costo[$k] = round(($sbt[$k] / $op_cantidad), 6);
		}
		$dato = ' Prov: ' . $cot['prod_prov_id'] . ' Costo: ' . $cot['prod_costo']; 
		if($cancela[$k] == '1') {
			$consulta = "DELETE FROM " . $dbpfx . "prod_prov WHERE op_id = '$v' AND prod_prov_id = '" . $prov_id[$k] . "'";
			$resultado = mysql_query($consulta) or die("ERROR: Fallo borrado de cotizaciones".$consulta);
			bitacora($orden_id, 'Se eliminó cotización para OP ' . $v . $dato, $dbpfx);
		} elseif($cot['prod_costo'] != $costo[$k] || $cot['dias_entrega'] != $entrega[$k] || $cot['dias_credito'] != $credito[$k]) {
			$sql_data = array('prod_costo' => $costo[$k], 'dias_entrega' => $entrega[$k], 'dias_credito' => $credito[$k]);
			$param = "op_id = '" . $v . "' AND prod_prov_id = '" . $prov_id[$k] . "'";
			ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'actualizar', $param);
			bitacora($orden_id, 'Se actualizó cotización para OP ' . $v . ' Antes ' . $dato, $dbpfx);
		}
		$name = $v;
	}
	redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id . '#' . $name);
}

elseif($accion==='generacb') {
	
	$funnum = 1115095;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || ($solovalacc != '1' && ($_SESSION['rol08']=='1'))) {
		$mensaje='';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}

	if($limpiar == 'Restablecer Filtros') { $codigo=''; $nombre=''; $almacen=''; }
	echo '		<form action="refacciones.php?accion=generacb" method="post" enctype="multipart/form-data">'."\n";
	echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">
			<tr><td colspan="2"><span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span></td></tr>'."\n";
	unset($_SESSION['ref']['mensaje']);
	echo '			<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Generación de Códigos de Barras para Productos del Almacén</td></tr>'."\n";
	echo '			<tr class="obscuro"><td style="text-align:left; width:50%;">Buscar por codigo: <input type="text" name="codigo" id="codigo" size="15" value="' . $codigo . '">
				<input name="Enviar" value="Enviar" type="submit"></td><td style="width:50%;">Buscar por nombre: <input type="text" name="nombre" size="15" value="' . $nombre . '">
				<input name="Enviar" value="Enviar" type="submit"></td></tr>
			<tr class="obscuro"><td style="text-align:left;" colspan="2">Filtrar por Almacén: 
				<select name="almacen" size="1">
					<option value="">Seleccionar...</option>'."\n";
	foreach($nom_almacen as $k => $v) {
		echo '					<option value="' . $k . '"';
		if($almacen == $k) { echo ' selected="selected" ';}
		echo '>' . $v . '</option>'."\n";
	}											
	echo '				</select>
				<input name="Enviar" value="Enviar" type="submit"><br><input type="submit" name="limpiar" value="Restablecer Filtros">'."\n";
	echo '			</td>'."\n";
	echo '		</tr></table></form>'."\n";
	if((isset($almacen) && $almacen!='') || (isset($nombre) && $nombre!='') || (isset($codigo) && $codigo!='')) { 
		if(isset($almacen) && $almacen!='') { $preg .= "AND prod_almacen='" . $almacen . "' "; }
		if(isset($nombre) && $nombre!='') {
			$nomped = explode(' ', $nombre);
			if(count($nomped) > 0) {
				foreach($nomped as $kc => $vc){
					$preg .= "AND prod_nombre LIKE '%" . $vc . "%' ";
				}
			} 
		}
		if(isset($codigo) && $codigo!='') { $preg .= "AND prod_codigo = '" . $codigo . "' "; }
	}
	if($_SESSION['ref']['pedpend'] == 1) { $preg .= "AND prod_cantidad_pedida > '0' "; }
	
	echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	$preg0 = "SELECT prod_id FROM " . $dbpfx . "productos WHERE prod_activo='1' ";
	$preg0 = $preg0 . $preg;
//	echo $preg0 . '<br>';
   $matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
   $filas = mysql_num_rows($matr0);
   if($filas == 0 && $codigo!='') {
   	echo '		<tr><td colspan="2">No se encontró ningún producto con el código ' . $codigo . ',<br>¿Desea agregarlo como nuevo a la lista de productos? <a href="refacciones.php?accion=item&nuevo=1&codigo=' . $codigo . '">Agregar</a>&nbsp;</td></tr>';
   } else {
	   $renglones = 100;
	   $paginas = (round(($filas / $renglones) + 0.49999999) - 1);
   	if(!isset($pagina)) { $pagina = 0;}
	   $inicial = $pagina * $renglones;
//	echo $paginas;
			$preg1 = "SELECT prod_id, prod_marca, prod_codigo, prod_nombre, prod_cantidad_pedida, prod_cantidad_existente, prod_cantidad_disponible, prod_precio, prod_almacen, prod_tangible FROM " . $dbpfx . "productos WHERE prod_activo = '1' ";
			$preg1 = $preg1 . $preg;
			$preg1 .= " GROUP BY prod_id ORDER BY prod_almacen, prod_nombre LIMIT " . $inicial . ", " . $renglones;
//	echo $preg1;
   	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");

		echo '			<tr><td colspan="2"><a href="refacciones.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="refacciones.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>';
		echo '</td></tr>'."\n";
		echo '			<tr><td colspan="2" style="text-align:left;">'."\n";
		echo '				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td style="width:120px;">Almacén</td><td style="width:150px;">Nombre</td><td style="width:80px;">Marca</td><td style="width:0px;">Código</td><td style="width:30px;">Existencia</td><td style="width:100px;">Precio Unitario<br>de Venta</td><td style="text-align:center;">Cuantos?<br>';
		echo '<form action="refacciones.php?accion=generacb&almacen=' . $almacen . '&nombre=' . $nombre . '&codigo=' . $codigo . '" method="post" enctype="multipart/form-data" name="partidas"><input type="checkbox" name="presel" value="1" ';
				if($presel == '1') { echo 'checked="checked" '; }
				echo 'onchange="document.partidas.submit()"; /></form>';
		echo '				<form action="refacciones.php?accion=imprimecb" method="post" enctype="multipart/form-data" name="items">'."\n";
		echo '</td></tr>'."\n";
		$cue = 0;
		while($prods = mysql_fetch_array($matr1)) {
			echo '					<tr><td>';
			echo $nom_almacen[$prods['prod_almacen']]; 
			echo '</td><td>' . $prods['prod_nombre'] . '</td><td>' . $prods['prod_marca'] . '</td><td>' . $prods['prod_codigo'] . '</td><td style="text-align:right;">' . $prods['prod_cantidad_existente'] . '</td><td style="text-align:right;">$' . number_format($prods['prod_precio'],2) . '</td><td>';
			echo '<input type="hidden" name="imprimecb[' . $cue . ']" value="' . $prods['prod_codigo'] . '|' . $prods['prod_nombre'] . '|' . $prods['prod_marca'] . '" />';
			echo '<input type="text" name="cuantoscb[' . $cue . ']" size="2" style="text-align:center;" ';
			if($presel == '1') { 
				echo ' value="1">';
			} else {
				echo ' value="">';
			}
			echo '</td></tr>'."\n";
			$cue++;
		}
		echo '				</table>'."\n";
		echo '			</td></tr>'."\n";
		echo '			<tr><td colspan="2"><a href="refacciones.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="refacciones.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>';
		echo '</td></tr>'."\n";
	}
	echo '			<tr><td colspan="2" style="text-align: left;"><input type="submit" name="Imprimir" value="Imprimir"></td></tr>'."\n";
	echo '			<tr><td colspan="2"><hr>
					<input type="hidden" name="nombre" value="' . $nombre . '">
					<input type="hidden" name="almacen" value="' . $almacen . '">
					<input type="hidden" name="codigo" value="' . $codigo . '">
				</td></tr>'."\n";
	echo '		</table>'."\n";
}

elseif($accion==='imprimecb') {
	
	$funnum = 1115095;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || ($solovalacc != '1' && ($_SESSION['rol08']=='1'))) {
		$mensaje='';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
//	print_r($imprimecb);
	echo '		<table cellspacing="15" cellpadding="5" border="1" width=840>'."\n";
	$sp = 1;
	foreach($cuantoscb as $k => $v) {
//		$v = limpiaNumero($v);
		$eti = explode("|", $imprimecb[$k]);
		if($j==0) { echo '			<tr>'."\n";}
		for($i=1; $i<=$v; $i++) {
			if($eti[0] != '' && $eti[0] != ' ') {
				echo '				<td valign="top" style="text-align:center;">' . $eti[1] . ' ' . $eti[2] . '<br><img src="parciales/barcode.php?barcode=' . $eti[0] . '&width=380&height=110"><br>' . $eti[0] . '</td>' . "\n";
				$j++; $sp++;
				if($j==2) { echo '			</tr>'."\n"; $j=0;}
				if($sp == 13) {
					echo '		</table>'."\n";
					echo '		<div class="saltopagina"></div> '."\n";
					echo '		<table cellspacing="15" cellpadding="5" border="1" width=840>'."\n";
					$sp = 1;
				}
			}
		}
	}
	echo '			<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="refacciones.php?accion=generacb&almacen=' . $almacen . '&nombre=' . $nombre . '&codigo=' . $codigo . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a Selección de Productos" title="Regresar a Selección de Productos"></a></div></td></tr>'."\n";
	echo '		</table>'."\n";

}

?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
