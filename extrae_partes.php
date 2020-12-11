<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/extrae_partes.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
} 
$funnum = 1035000;
$resultado = validaAcceso($funnum, $dbpfx);
if ($resultado == '1' || $_SESSION['rol12'] == '1') {
	// acceso concedido
} else {
	redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta para esta función');
} 

//  ----------------  obtener nombres de proveedores   ------------------- 
	$consulta = "SELECT prov_id, prov_nic FROM " . $dbpfx . "proveedores ORDER BY prov_nic";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
	$num_provs = mysql_num_rows($arreglo);
	$provs = array();
	//$provs[0] = 'Sin Proveedor';
	while($prov = mysql_fetch_array($arreglo)){
		$provs[$prov['prov_id']] = $prov['prov_nic'];
	}
	//echo '<pre>';
	//print_r($provs);
	//echo '</pre>';
//  ----------------  nombres de proveedores   ------------------- 

if($export == 1){ // ---- Hoja de calculo ----

}
else{ // ---- HTML ---

	include('idiomas/' . $idioma . '/extrae-partes.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">' . "\n";
	include('parciales/menu_inicio.php');
	echo '		<div id="principal"><br>'."\n";
}

if($accion==='buscar') {

	$error = 'si';

	if (($tipo!='') || ($vin!='')) {
		$vin = substr($vin, 0, 11);
		// --- Construir consulta ---
		$preg = "SELECT o.orden_id, v.vehiculo_serie, v.vehiculo_id, v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_subtipo, v.vehiculo_modelo FROM " . $dbpfx . "ordenes o, " . $dbpfx . "vehiculos v WHERE ";
		
		if($tipo){
			$preg .= "v.vehiculo_tipo LIKE '%$tipo%' ";
		}
		if(($tipo) && ($year)){
			$preg .= "AND v.vehiculo_modelo LIKE '%$year%' ";
		}
		elseif($year){
			$preg .= "v.vehiculo_modelo LIKE '%$year%' ";
		}
		if((($year) || ($tipo)) && ($vin)){
			$preg .= "AND v.vehiculo_serie LIKE '%$vin%' ";
		} 
		elseif($vin){
			$preg .= "v.vehiculo_serie LIKE '%$vin%' ";
		}
		
		$preg .= " AND o.orden_vehiculo_id = v.vehiculo_id ORDER BY v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_modelo";
		//echo $preg . '<br>';
		
		$matr = mysql_query($preg) or die("ERROR: Fallo seleccion! $preg");
		$filas = mysql_num_rows($matr);
		if($filas > 0) {
			//echo 'resultados ' . $filas . '<br>';
			$error = 'no'; 
		}
	}	

	if($error == 'no') {
		
		if($export == 1){ // ---- Hoja de calculo ----
			// -------------------   Creación de Archivo CSV   ----------------------------------
			$columna = [
				'Marca', 
				'Tipo', 
				'VIN', 
				'Año', 
				'Código de Parte', 
				'Descripción', 
				'Costo', 
				'Precio', 
				'Proverdor',
				'Pedido',
			];
			$fp = fopen('php://output', 'w');
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="Búsqueda de refacciones"');
			header('Pragma: no-cache');
			header('Expires: 0');
			fputcsv($fp, $columna);
		}
		else{ // ---- HTML ---
			// --- Imprimir resultados ---
			echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-sm-12 panel-title">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2>Mostrando resultados para ';
			if($tipo != ''){ echo 'Tipo: ' . $tipo . ' '; }
			if($year != ''){ echo 'Año: ' . $year . ' '; }
			if($vin != ''){ echo 'VIN: ' . $vin . ' '; }
			if($parte != ''){ echo 'Refacción: ' . $parte . ' '; }
			if($id_prov > 0){ echo 'Proveedor: ' . $provs[$id_prov] . ' '; }
			echo '
					</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
					<tr>
						<th>Marca</th>
						<th>Tipo</th>
						<th>VIN</th>
						<th>Año</th>
						<th>Código de Parte</th>
						<th>Descripción</th>
						<th>Costo</th>
						<th>Precio</th>
						<th>Proverdor</th>
						<th>Pedido</th>
					</tr>'."\n";
		}
		
		$fondo = 'claro';
		$renglones = 50;
		$pnx = 0;
		
		while ($orden = mysql_fetch_array($matr)){
			//$vin = substr($orden['vehiculo_serie'], 0, 11);
			$preg2 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden['orden_id'] . "' AND sub_estatus < '190'";
	  		$matr2 = mysql_query($preg2) or die('falló ' . $preg2);
			while ($sub = mysql_fetch_array($matr2)) {
				$preg3 = "SELECT op_id, op_codigo, op_nombre, op_costo, op_precio, op_pedido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND op_tangible = '1' AND op_pedido > 0 AND op_ok = '1'";
				if($parte != '') {
					$preg3 .= " AND op_nombre LIKE '%$parte%'";
				}
		  		$matr3 = mysql_query($preg3) or die('falló ' . $preg3);
  				while ($op = mysql_fetch_array($matr3)){
					
					if($id_prov == 0){ // --- si no hay proveedor se agrega al array
						// --- Paginar resultados ---
						$opr[$pnx][] = $op['op_id'];
						$inx++;
						if($inx == $renglones) { $pnx++; $inx = 0; }	
					} else{ // --- si hay proveedor , se evalua para mostrar solo los op que tengan pedido con este proveedor ---
						
						$preg_ped = "SELECT prov_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $op['op_pedido'] . "'";
						$matr_ped = mysql_query($preg_ped) or die('falló ' . $preg_ped);
						$cons_pedido = mysql_fetch_assoc($matr_ped);
						
						if($cons_pedido['prov_id'] == $id_prov){
							// --- Paginar resultados ---
							$opr[$pnx][] = $op['op_id'];
							$inx++;
							if($inx == $renglones) { $pnx++; $inx = 0; }
						}
					}
					
		  		}
			}
		}
		
		$itemsref = ($pnx * $renglones) + $inx;
		$paginas = $pnx;
		if(!isset($pagina)) { $pagina = 0;}
		$inicial = $pagina * $renglones;
		//echo '<pre>';
		//echo 'Pagina ' . $pagina . '<br>';
		//print_r($opr);
		//echo '</pre>';
		
		
		
		if($export == 1){ // ---- Hoja de calculo ----
			
			// --- Recorrer elementos de la página en curso ---
			foreach($opr as $key => $val){
				//echo '$key ' . $key . ' $val ' . $val . '<br>';
				foreach($val as $kk => $op_id){
					//echo ' $kk ' . $kk . ' $op_id ' . $op_id . '<br>';
					
					//echo 'op_id ' . $op_id . '<br>';
					// --- Consultar el op ---
					$preg_op = "SELECT sub_orden_id, op_codigo, op_nombre, op_costo, op_precio, op_pedido FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op_id . "'";
					$matr_op = mysql_query($preg_op) or die('falló ' . $preg_op);
					$op_info = mysql_fetch_assoc($matr_op);

					// --- Consulatar suborden ---
					$preg_sub = "SELECT orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $op_info['sub_orden_id'] . "'";
					$matr_sub = mysql_query($preg_sub) or die('falló ' . $preg_sub);
					$sub_info = mysql_fetch_assoc($matr_sub);

					// --- consultar info de la orden y vehículo ---
					$preg_ord = "SELECT o.orden_id, v.vehiculo_serie, v.vehiculo_id, v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_subtipo, v.vehiculo_modelo FROM " . $dbpfx . "ordenes o, " . $dbpfx . "vehiculos v WHERE o.orden_id = '" . $sub_info['orden_id'] . "' AND o.orden_vehiculo_id = v.vehiculo_id";
					$matr_ord = mysql_query($preg_ord) or die('falló ' . $preg_ord);
					$ord_info = mysql_fetch_assoc($matr_ord);

					// --- Consultar info del pedido y provedor ---
					$preg_pedido = "SELECT prov_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $op_info['op_pedido'] . "'";
					$matr_ped = mysql_query($preg_pedido) or die("falló " . $preg_pedido);
					$info_ped = mysql_fetch_assoc($matr_ped);
					
					$op_info['op_costo'] = number_format($op_info['op_costo'], 2);
					$op_info['op_precio'] = number_format($op_info['op_precio'], 2);
				
					// --- Array a insertar en la fila ---
					$insert = [
						$ord_info['vehiculo_marca'],
						$ord_info['vehiculo_tipo'],
						$ord_info['vehiculo_serie'],
						$ord_info['vehiculo_modelo'],
						$op_info['op_codigo'],
						$op_info['op_nombre'],
						$op_info['op_costo'],
						$op_info['op_precio'],
						$provs[$info_ped['prov_id']],
						$op_info['op_pedido'],
					];
					fputcsv($fp, array_values($insert));
				}
			}
		}
		else{ // ---- HTML ---
		
		
			// --- Recorrer elementos de la página en curso ---
			foreach($opr[$pagina] as $op_id){
			
				//echo 'op_id ' . $op_id . '<br>';
				// --- Consultar el op ---
				$preg_op = "SELECT sub_orden_id, op_codigo, op_nombre, op_costo, op_precio, op_pedido FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op_id . "'";
				$matr_op = mysql_query($preg_op) or die('falló ' . $preg_op);
				$op_info = mysql_fetch_assoc($matr_op);
			
				// --- Consulatar suborden ---
				$preg_sub = "SELECT orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $op_info['sub_orden_id'] . "'";
	  			$matr_sub = mysql_query($preg_sub) or die('falló ' . $preg_sub);
				$sub_info = mysql_fetch_assoc($matr_sub);
			
				// --- consultar info de la orden y vehículo ---
				$preg_ord = "SELECT o.orden_id, v.vehiculo_serie, v.vehiculo_id, v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_subtipo, v.vehiculo_modelo FROM " . $dbpfx . "ordenes o, " . $dbpfx . "vehiculos v WHERE o.orden_id = '" . $sub_info['orden_id'] . "' AND o.orden_vehiculo_id = v.vehiculo_id";
				$matr_ord = mysql_query($preg_ord) or die('falló ' . $preg_ord);
				$ord_info = mysql_fetch_assoc($matr_ord);
			
				// --- Consultar info del pedido y provedor ---
				$preg_pedido = "SELECT prov_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $op_info['op_pedido'] . "'";
				$matr_ped = mysql_query($preg_pedido) or die("falló " . $preg_pedido);
				$info_ped = mysql_fetch_assoc($matr_ped);
			
				echo '	
					<tr class="' . $fondo . '">
						<td style="text-align:left;">' . $ord_info['vehiculo_marca'] . '</td>
						<td style="text-align:left;">' . $ord_info['vehiculo_tipo'] . '</td>
						<td style="text-align:left;">' . $ord_info['vehiculo_serie'] . '</td>
						<td style="text-align:left;">' . $ord_info['vehiculo_modelo'] . '</td>
						<td style="text-align:left;">' . $op_info['op_codigo'] . '</td>
						<td style="text-align:left;">' . $op_info['op_nombre'] . ' </td>
						<td style="text-align:right;">
							<b>$ ' . number_format($op_info['op_costo'], 2) . '</b>
						</td>
						<td style="text-align:right;">
							<b>$ ' . number_format($op_info['op_precio'], 2) . '</b>
						</td>
						<td style="text-align:left;">' . $provs[$info_ped['prov_id']] . ' </td>
						<td><a href="pedidos.php?accion=consultar&pedido=' . $op_info['op_pedido'] . '">' . $op_info['op_pedido'] . '</a></td>
					</tr>'."\n";
				if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
			
			}
		}
	
		
		if($export == 1){ // ---- Hoja de calculo ----
			exit;
		}
		else{ // ---- HTML ---
			// --- Mostrar páginas ---
			echo '
					<tr>
						<td>

							<a href="extrae_partes.php?accion=buscar&pagina=0&tipo=' . $tipo . '&year=' . $year . '&vin=' . $vin . '&parte=' . $parte . '&id_prov=' . $id_prov . '">Inicio</a>&nbsp;';
	
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '
							<a href="extrae_partes.php?accion=buscar&pagina=' . $url .'&tipo=' . $tipo . '&year=' . $year . '&vin=' . $vin . '&parte=' . $parte . '&id_prov=' . $id_prov . '"><b><big> <- </big></b></a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '
							<a href="extrae_partes.php?accion=buscar&pagina=' . $url .'&tipo=' . $tipo . '&year=' . $year . '&vin=' . $vin . '&parte=' . $parte . '&id_prov=' . $id_prov . '"><b><big> -> </big></b></a>&nbsp;';
		}
		echo '
							<a href="extrae_partes.php?accion=buscar&pagina=' . $paginas .'&tipo=' . $tipo . '&year=' . $year . '&vin=' . $vin . '&parte=' . $parte . '&id_prov=' . $id_prov . '">Ultima</a>
						</td>
						
					</tr>
				</table>
				<div class="control">
					<a href="extrae_partes.php">
						<img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la búsqueda de partes" title="Regresar a la búsqueda de partes">
					</a>
					<a href="extrae_partes.php?accion=buscar&export=1&tipo=' . $tipo . '&year=' . $year . '&vin=' . $vin . '&parte=' . $parte . '&id_prov=' . $id_prov . '">
						<img src="idiomas/' . $idioma . '/imagenes/hoja-calculo.png" alt="Exportar" border="0">
					</a>
				</div>
			</div>
		</div>
	</div>
</div>'."\n";
		}

	} else {
		echo '<big><b>No se encontraron registros con los datos proporcionados, por favor vuelva a intentar.<br>' . $mensaje . '</b><big>';
	}
} else {
	
	echo '			
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-sm-5 panel-title">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2>Consultar Precios de Partes</h2>
				</div>
			</div>
		</div>
	</div>'."\n";
	
	if(isset($_SESSION['extrae']['mensaje'])) {
		echo '<span class="alerta">' . $_SESSION['extrae']['mensaje'] . '</span>'."\n";
		unset($_SESSION['extrae']);
	} 
	echo '			
	<div class="row">
		<div class="col-md-6 panel-body">
			<div class="form-group">
				<table>
					<div class="col-md-12">
						<form action="extrae_partes.php?accion=buscar" method="post">
							<tr>
								<td><big><b>Vehículo Tipo: </b></big></td>
								<td><input type="text" class="form-control" id="orden_id" name="tipo" size="40" /></td>
							</tr>
							<tr>
								<td><big><b>Año (4 dígitos): </b></big></td>
								<td><input type="text" class="form-control" name="year" size="40" maxlength="4"/></td>
							</tr>
							<tr>
								<td><big><b>Nombre de parte: </b></big></td>
								<td><input type="text" class="form-control" name="parte" size="40" maxlength="120"/></td>
							</tr>
							<tr>
								<td><big><b>VIN: </b></big></td>
								<td><input type="text" class="form-control" name="vin" size="40" maxlength="17"/></td>
							</tr>
							<tr>
								<td><big><b>Proveedor: </b></big></td>
								<td>
									<select class="form-control" name="id_prov" size="1">
										<option value="0">seleccione un proveedor</option>'."\n";
									foreach($provs as $k => $v) {
										//echo 'key ' . $k . ' val ' . $v . '<br>';
										echo '
										<option value="' . $k . '">' . $v . '</option>'."\n";
									}
										
	echo '
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<span style="color:red; font-weight:bold;">
										<b>Se requiere como mínimo el Tipo de vehículo o el VIN. </b>
									</span>
								</td>
							</tr>
							<tr>
								<td colspan="2" style="text-align:left;">
									<input type="submit" class="btn btn-success" value="Enviar" />
								</td>
							</tr>
						</form>
					</div>
				</table>
			</div>
		</div>
	</div>
</div>'."\n";

	
}

echo '		</div>
	</div>';
include('parciales/pie.php');

/* Archivo extrae_partes.php */
/* autoshop-easy.com */
