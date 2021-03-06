<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';    
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

		$consulta = "SELECT prov_id, prov_razon_social, prov_nic, prov_qv_id, prov_rfc, prov_env_ped, prov_representante, prov_email, prov_dde, prov_iva, prov_dias_credito FROM " . $dbpfx . "proveedores WHERE prov_email != '' AND prov_activo = '1' ORDER BY prov_nic";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
		$num_provs = mysql_num_rows($arreglo);
		$provs = array();
//		$provs[0] = 'Sin Proveedor';
		while ($prov = mysql_fetch_array($arreglo)) {
			$provs[$prov['prov_id']] = array('nombre' => $prov['prov_razon_social'], 'nic' => $prov['prov_nic'], 'qvid' => $prov['prov_qv_id'], 'rfc' => $prov['prov_rfc'], 'env' => $prov['prov_env_ped'], 'contacto' => $prov['prov_representante'], 'email' => $prov['prov_email'], 'dde' => $prov['prov_dde'], 'iva' => $prov['prov_iva'], 'dias_credito' => $prov['prov_dias_credito']);
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
		$asegnic[0] = 'Particular';
		$autosurt[0] = 1;

if (($accion==='gestiona') || ($accion==='actualizar') || ($accion==='cotpedprod' || $accion==='gestprod') || ($accion==='insertar') || ($accion==='inspcpaq') || ($accion==='actpcpaq') || $accion==='guardacotiza' || $export == 1 || $accion==='flash') {
	/* no cargar encabezado */
} else {
	
	if($export == 1){ // ---- Hoja de calculo ---- 
			
	} else{ // ---- HTML ----
		include('parciales/encabezado.php'); 
		echo '	
				<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		
				<div id="principal">'."\n";
	}
}

if($accion==="pendientes") {

	$funnum = 1115000;

	if (validaAcceso('1115000', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol08']=='1'))) {
		// Acceso autotizado
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	$mensaje = '';
	$error = 'si'; $num_cols = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));

	//if($estadoref < 1) { $estadoref = 1; }
	$llave = '';
	$renglones = 50;
	if($limpiar == 'Limpiar') { $margen = ''; $estadoref = ''; $pedidoref = ''; $ordenref = ''; $nombreref = ''; }
	//echo 'Orden Ref: ' .$ordenref;
	if($provref && $ordenref == '') {
		$llave .= ' Filtrado por Proveedor ' . $provs[$provref]['nic'];
		$preg = "SELECT pedido_id FROM " . $dbpfx . "pedidos WHERE pedido_estatus < 10 AND prov_id = '$provref'";
		//echo $preg;
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de pedidos pendientes!" . $preg);
		$inx = 0; $pnx = 0;
		while($ped = mysql_fetch_array($matr)) {
			$preg1 = "SELECT o.op_id FROM " . $dbpfx . "orden_productos o, " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes r WHERE o.op_tangible = 1 AND o.op_ok = '0' AND o.op_pedido = '" . $ped['pedido_id'] . "' AND o.sub_orden_id = s.sub_orden_id AND r.orden_id = s.orden_id AND r.orden_estatus < '90' AND s.sub_estatus < '130' ";
			if($fecharef != '') { $preg1 .= " AND o.op_fecha_promesa LIKE '%$fecharef%' "; $llave .= ' Fecha Promesa'; }
			//echo $preg1.'<br>';
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de refaaciones pendientes de proveedor! " . $preg1);
			while($op = mysql_fetch_array($matr1)) {
				$opr[$pnx][] = $op['op_id'];
				$inx++;
				if($inx == $renglones) { $pnx++; $inx = 0; }
			}
		}
	} elseif($ordenref) {
		$renglones = 150;
		//echo 'prov ' . $provref . '<br>';
		
		$llave .= ' Filtrado por Orden de Trabajo ' . $ordenref;

		if($provref > 0){
			$llave .= ' y Proveedor ' . $provs[$provref]['nic'];	
		}
		
		$preg = "SELECT s.sub_orden_id, s.orden_id, s.sub_estatus, o.orden_estatus FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE s.sub_estatus < 130 AND s.orden_id = '$ordenref' AND o.orden_estatus < '90' AND s.orden_id = o.orden_id";
		//echo $preg . '<br>';
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de tareas! " . $preg);
		$elementos = mysql_num_rows($matr);
		//echo 'Resultados ' . $elementos . '<br>';
		$inx = 0; $pnx = 0;
		while($sub = mysql_fetch_array($matr)) {
			$preg1 = "SELECT op_id, op_pedido, op_autosurtido, op_pres FROM " . $dbpfx . "orden_productos WHERE op_tangible = 1 AND op_ok = '0' AND sub_orden_id = '" . $sub['sub_orden_id'] . "'";
			if($estadoref == '1') { $preg1 .= " AND (op_cotizado_a IS NULL OR op_cotizado_a = '') AND op_pedido < 1 "; $llave .= ' Por Cotizar'; }
			elseif($estadoref == '2') { $preg1 .= " AND op_pedido < 1 AND op_cotizado_a IS NOT NULL AND op_cotizado_a != '' "; $llave .= ' Por Pedir'; }
			elseif($estadoref == '3') { $preg1 .= " AND op_pedido > '0' "; $llave .= ' Por Recibir'; }
			if($fecharef != '') { $preg1 .= " AND op_fecha_promesa LIKE '%$fecharef%' "; $llave .= ' Fecha Promesa'; }
			//echo $preg1 .'<br>';
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de refaciones pendientes de proveedor! " . $preg1);
			while($op = mysql_fetch_array($matr1)) {
				if(($op['op_pres'] == '1' && ($sub['sub_estatus'] >= 124 || $sub['sub_estatus'] == 120)) || $op['op_pedido'] > 0 || is_null($op['op_pres'])) {
					if($provref > 0){ // --- Si el filtro de proveedor está activado realizar verificación ---
						$preg_prov = "SELECT pedido_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $op['op_pedido'] . "' AND pedido_estatus < 10 AND prov_id = '$provref'";
						$matr_prov = mysql_query($preg_prov) or die("ERROR: Fallo selección de pedidos pendientes!" . $preg_prov);
						$match = mysql_num_rows($matr_prov);
						if($match == 1){
							$opr[$pnx][] = $op['op_id'];
							$inx++;
							if($inx == $renglones) { $pnx++; $inx = 0; }
						}
					}
					else{
						$opr[$pnx][] = $op['op_id'];
						$inx++;
						if($inx == $renglones) { $pnx++; $inx = 0; }	
					}
				}
			}
		}
	} else {
		$preg = "SELECT p.op_id, p.op_pres, p.op_pedido, s.sub_estatus FROM " . $dbpfx . "orden_productos p, " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE p.op_tangible = '1' AND p.op_ok = '0' AND s.sub_orden_id = p.sub_orden_id AND s.orden_id = o.orden_id AND s.sub_estatus < '130' AND o.orden_estatus < '90' ";
		if(($nombreref) || ($estadoref) || ($pedidoref) || ($fecharef)) {
			if($nombreref != '') { $prega .= " AND op_nombre LIKE '%$nombreref%' "; $llave .= ' Nombre'; }
			if($pedidoref != '') { $prega .= " AND op_pedido = '$pedidoref' "; $llave .= ' Pedido'; }
			if($estadoref == '1') { $prega .= " AND (op_cotizado_a IS NULL OR op_cotizado_a = '') AND op_pedido < 1 "; $llave .= ' Por Cotizar'; }
			elseif($estadoref == '2') { $prega .= " AND op_pedido < 1 AND op_cotizado_a IS NOT NULL AND op_cotizado_a > '' "; $llave .= ' Por Pedir'; }
			elseif($estadoref == '3') { $prega .= " AND op_pedido > '0' "; $llave .= ' Por Recibir'; }
			if($fecharef != '') { $prega .= " AND op_fecha_promesa LIKE '%$fecharef%' "; $llave .= ' Fecha Promesa'; }
		}
		$pregc = $preg . $prega;
		//echo $pregc . '<br>';
		$matriz = mysql_query($pregc) or die("ERROR: Fallo cuenta de refacciones pendientes!" . $pregc);
		$filas = mysql_num_rows($matriz);
		$inx = 0; $pnx = 0;
		while($op = mysql_fetch_array($matriz)) {
			if(($op['op_pres'] == '1' && ($op['sub_estatus'] >= 124 || $op['sub_estatus'] == 120)) || $op['op_pedido'] > 0 || is_null($op['op_pres'])) {
				$opr[$pnx][] = $op['op_id'];
				$inx++;
				if($inx == $renglones) { $pnx++; $inx = 0; }
			}
		}
	}

	//echo 'Filas: ' . $filas . ' ';
	//echo 'OPR <br>';
	//print_r($opr);
	$itemsref = ($pnx * $renglones) + $inx;
	$paginas = $pnx;
	if(!isset($pagina)) { $pagina = 0;}
	$inicial = $pagina * $renglones;
  	//echo $paginas;

	if($export == 1){ // ---- Hoja de calculo ---- 
		
		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'Refacciones Pendientes: ' . $nombre_agencia;
			
		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("Refacciones Pendientes")
					->setKeywords("AUTOSHOP EASY");

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($celda, $titulo)
					->setCellValue("A3", $fecha_export);

		// ------ ENCABEZADOS ---
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A4", "Cant")
					->setCellValue("B4", "Pendientes")
					->setCellValue("C4", "Nombre")
					->setCellValue("D4", "Estado")
					->setCellValue("E4", "Costo")
					->setCellValue("F4", "Sugerido Venta")
					->setCellValue("G4", "Proveedor con Pedido")
					->setCellValue("H4", "# Pedido")
					->setCellValue("I4", "F Promesa")
					->setCellValue("J4", "Área")
					->setCellValue("K4", "Orden ID")
					->setCellValue("L4", "Días en proceso");

		$z= 5;
		// ------------------- Margen para sugerir precio de Venta -------
		if($margen == '') { $margen = $defmarg; }
			
	} else{ // ---- HTML ----
		
		echo '
	<div class="page-content">
		<div class="row"> <!-box header del título. -->
			<div class="col-md-12">
	  			<div class="content-box-header">
					<div class="panel-title">
		  				<h2>
							LISTADO DE REFACCIONES PENDIENTES ' . $llave . '
						</h2>
					</div>
			  	</div>
			</div>
		</div>
		<br>
		
		<div class="row">
			<div class="col-md-12">
				<form action="refacciones.php?accion=pendientes" method="post" enctype="multipart/form-data">
				<div class="col-sm-3 padding">
					<input type="text" class="form-control" placeholder="Nombre de la refacción" name="nombreref" value="' .  $nombreref . '" size="25">
					<select name="estadoref" class="form-control">
						<option value="" selected="selected">Estado de la refacción</option>
						<option value="1"';
		if($estadoref == '1') { echo ' selected '; }
					echo '>Por Cotizar</option>'."\n";
					echo '
						<option value="2"';
		if($estadoref == '2') { echo ' selected '; }
					echo '>Por Pedir</option>'."\n";
					echo '						
						<option value="3"';
		if($estadoref == '3') { echo ' selected '; }
					echo '>Por Recibir</option>'."\n";
		echo '					
					</select>'."\n";
	
		echo '
					<select name="provref" class="form-control">
						<option value="0" selected="selected">Selecciona Proveedor</option>'."\n";
		
		foreach($provs as $k => $v) {
			echo '						
						<option value="' . $k . '"';
			//if($provref == $k) { echo ' selected '; }
			echo '>' . substr($v['nic'],0,17);
			if(strlen($v['nic']) > '17') { echo '...'; }
			echo '
						</option>'."\n";
		}
		
		echo '						
					</select>
				</div>'."\n";
		
		// ------------------- Margen para sugerir precio de Venta -------
		if($margen == '') { $margen = $defmarg; }
		
		echo '	
				<div class="col-sm-2 padding">
				
					<input type="text" class="form-control" placeholder="# Pedido" name="pedidoref" value="' .  $pedidoref . '" size="6">
					<b>% Sugerido Venta:</b>
					<input type="text" class="form-control" name="margen" value="' . $margen . '" size="2"/>
				</div>
			
				<div class="col-sm-2 padding">
					<input type="text" class="form-control" placeholder="Fecha promesa" name="fecharef" size="10">
					<input type="text" class="form-control" placeholder="OT" name="ordenref" value="' .  $ordenref . '" size="6">
				</div>
			
				<div class="col-sm-1 padding">
					<input class="btn btn-danger" type="submit" name="limpiar" value="Limpiar">
					<br><br>
					<input class="btn btn-success" type="submit" name="filtrar" value="Filtrar">
				</div>
				</form>
			
				<div class="col-sm-1 padding"> <!-- Export hoja de calculo -->
					<a href="refacciones.php?accion=pendientes&export=1&ordenref=' . $ordenref . '&nombreref=' . $nombreref . '&estadoref=' . $estadoref . '&pedidoref=' . $pedidoref . '&fecharef=' . $fecharef . '&provref=' . $provref . '">
						<img src="idiomas/' . $idioma . '/imagenes/hoja-calculo.png" alt="Exportar" border="0">
					</a>	
				</div>
			
				<div class="col-sm-1 padding">
					<br><br><br><br>
					<a href="refacciones.php?accion=pendientes&pagina=0&nombreref=' . $nombreref . '&estadoref=' . $estadoref . '&pedidoref=' . $pedidoref . '&fecharef=' . $fecharef . '&provref=' . $provref . '&ordenref=' . $ordenref . '">Inicio</a>&nbsp;';
	
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '
					<a href="refacciones.php?accion=pendientes&pagina=' . $url . '&nombreref=' . $nombreref . '&estadoref=' . $estadoref . '&pedidoref=' . $pedidoref . '&fecharef=' . $fecharef . '&provref=' . $provref . '&ordenref=' . $ordenref . '">anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '
					<a href="refacciones.php?accion=pendientes&pagina=' . $url . '&nombreref=' . $nombreref . '&estadoref=' . $estadoref . '&pedidoref=' . $pedidoref . '&fecharef=' . $fecharef . '&provref=' . $provref . '&ordenref=' . $ordenref . '">siguiente</a>&nbsp;';
		}
		echo '
					<a href="refacciones.php?accion=pendientes&pagina=' . $paginas . '&nombreref=' . $nombreref . '&estadoref=' . $estadoref . '&pedidoref=' . $pedidoref . '&fecharef=' . $fecharef . '&provref=' . $provref . '&ordenref=' . $ordenref . '">Ultima</a>
				</div>
			</div>
		</div>
		<br>
		
		<!-- Pintar encabezado de la tabla -->
		<form action="refacciones.php?accion=act_pre_venta" method="post" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-12">
				<div id="content-tabla">
					<table cellspacing="0" class="table-new">
						<tr>
							<th><big><b>Cant</b></big></th>
							<th><big><b>Pendientes</b></big></th>
							<th><big><b>Nombre</b></big></th>
							<th><big><b>Estado</b></big></th>
							<th><big><b>Costo</b></big></th>
							<th>
								<div style="position: relative; display: inline-block;">
									<a onclick="muestraAyudaPrecios()" class="ayuda">Precio actual | Sugerido Venta</a>
									<div id="AyudaPrecios" class="muestra-contenido">
										En esta columna puedes actualizar los precios de venta de las refacciones, colocando el precio sugerido (basado en el cálculo de utilidad colocado en la casilla de "% Sugerido Venta"), para poder actulizar es necesario:
										<ul>
											<li>Tener el filtro de "ORDEN DE TRABAJO" activo.</li>
											<li>Tener el el rol de Gerente , valuador o el permiso para actualizar los precios de venta.</li>
										</ul>
									</div>
								</div>
								<script>
									function muestraAyudaPrecios() {
										document.getElementById("AyudaPrecios").classList.toggle("mostrar");
									}
								</script>
							</th>
							<th><big><b>Proveedor con Pedido</b></big></th>
							<th><big><b># Pedido</b></big></th>
							<th><big><b>F Promesa</b></big></th>
							<th><big><b>Área</b></big></th>
							<th><big><b>Orden ID</b></big></th>
							<th><big><b>Días en<br>proceso</b></big></th>
							<th><big><b>Foto</b></big></th>
						</tr>'."\n";
		
	}

	//   echo $preg;
	$j=0;
	$fondo = 'claro';
	if($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1') {
		//	echo '			<form action="refacciones.php?accion=gestiona" method="post" enctype="multipart/form-data">'."\n";
	}

	// ------ Sólo Hoja de cálculo ya que arroja todos los resultados ------
	if($export == 1) { // ---- Hoja de calculo ---- 
		
		foreach($opr as $pagina => $vv) {
			//	echo $pagina;
			foreach($opr[$pagina] as $op_id) {
				$preg = "SELECT p.*, s.orden_id, s.sub_orden_id, s.sub_estatus FROM " . $dbpfx . "orden_productos p, " . $dbpfx . "subordenes s WHERE op_id = '$op_id' AND s.sub_orden_id = p.sub_orden_id ";
				$matriz = mysql_query($preg) or die("ERROR: Fallo selección de refacciones! " . $preg);
				$prods = mysql_fetch_array($matriz);
//		echo 'Tratando a OPID: ' . $prods['op_id'] . '<br>';
				$pend = $prods['op_cantidad'] - $prods['op_recibidos'];
				if((is_null($prods['op_cotizado_a']) || $prods['op_cotizado_a'] == '') && $prods['op_pedido'] < 1) {
					$estado = 'Por Cotizar'; $estref = 1;
				} elseif(!is_null($prods['op_cotizado_a']) && $prods['op_cotizado_a'] != '' && $prods['op_pedido'] < 1 ) {
					$estado = 'Por Pedir'; $estref = 2;
				} elseif( $prods['op_pedido'] > 0 ) {
					$estado = 'Por Recibir'; $estref = 3;
				} else {
					$estado = ''; $estref = 0;
				}

				$saltar = 0;
				if($prods['op_item_seg'] > 0) {
					$saltar = 1;
				}

				if($saltar == 0) {
					
					// ---- Calcular días en proceso ----
					$preg_orden = "SELECT orden_fecha_de_entrega, orden_fecha_recepcion, orden_fecha_ultimo_movimiento, orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $prods['orden_id'] . "' ";
					$matriz_orden = mysql_query($preg_orden) or die("ERROR: Fallo selección de orden! " . $preg_orden);
					$ord = mysql_fetch_assoc($matriz_orden);

					$fde = strtotime($ord['orden_fecha_de_entrega']);
					if($fde > strtotime('2012-01-01')) {
						$dias = intval(($fde - strtotime($ord['orden_fecha_recepcion'])) / 86400) + 1;
					} elseif($ord['orden_estatus'] == '90') {
						$dias = intval((strtotime($ord['orden_fecha_ultimo_movimiento']) - strtotime($ord['orden_fecha_recepcion'])) / 86400);
					} else {
						$dias = intval(($hoy - strtotime($ord['orden_fecha_recepcion'])) / 86400) + 1;
					}
				
					// --- Celdas a grabar ----
					$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
					$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
					$kkk = 'K'.$z; $l = 'L'.$z;

					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($a, $prods['op_cantidad'])
								->setCellValue($b, $pend)
								->setCellValue($c, $prods['op_nombre'])
								->setCellValue($d, $estado)
								->setCellValue($e, $prods['op_costo'])
								->setCellValue($f, ($prods['op_costo']/(1-($margen/100))))
								->setCellValue($j, constant('NOMBRE_AREA_' . $prods['op_area']))
								->setCellValue($kkk, $prods['orden_id'])
								->setCellValue($l, $dias);
					

					if($estref == 2) {
						
						$cot = explode('|', $prods['op_cotizado_a']);
						$pcot = array();
						
						foreach($cot as $k => $v) {
							$pcot[$v] = $v;
						}
						
						$j = 0;
						$cotizados = '';
						
						foreach($pcot as $i) {
							// --- CONSULTAR COTIZACIONES CONTESTADAS ---
							$preg_cot_ok = "SELECT prod_costo FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $prods['op_id'] . "' AND prod_prov_id = '" . $i . "'";
							$matr_cot_ok = mysql_query($preg_cot_ok) or die("ERROR: Fallo selección de costo cotización! " . $preg_cot_ok);
							$cost_cot = mysql_fetch_assoc($matr_cot_ok);

							if($cotizados != '') {
								$cotizados = $cotizados . ', ' . $provs[$i]['nic'];
							} else {
								$cotizados = $provs[$i]['nic'];
							}
							
						}
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($g, $cotizados);
						
						
					} elseif($estref == 3) {
						
						// --- Consultar id del proveedor del pedido  ---
						$preg_prov = "SELECT prov_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $prods['op_pedido'] . "'";
						$matriz_prov = mysql_query($preg_prov) or die("ERROR: Fallo selección de prov! " . $preg_prov);
						$prov = mysql_fetch_assoc($matriz_prov);
							
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($g, $provs[$prov['prov_id']]['nic']);
						
					}

					if($prods['op_pedido'] > 1 ) {
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($h, $prods['op_pedido']);
						
					} elseif($estref == 2) {
						
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($h, 'Pedir Refacciones');
						
					}


					if(!is_null($prods['op_fecha_promesa']) && $prods['op_fecha_promesa'] != '0000-00-00 00:00:00') {
						$fpe = date('Y-m-d', strtotime($prods['op_fecha_promesa']));
						$fpe = PHPExcel_Shared_Date::PHPToExcel( strtotime($fpe) );

						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($i, $fpe);
						// --- cambiar el formato de la celda tipo fecha/date ---
						$objPHPExcel->getActiveSheet()
									->getStyle($i)
									->getNumberFormat()
									->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
					}
					$z++;


					$ind++;

					if($filapres == $renglones) { break;}
					$rfinal = $ind + 1;
				}
			}
		}
		
	} else { // ---- HTML ----
	foreach($opr[$pagina] as $op_id) {
		$preg = "SELECT p.*, s.orden_id, s.sub_orden_id, s.sub_estatus FROM " . $dbpfx . "orden_productos p, " . $dbpfx . "subordenes s WHERE op_id = '$op_id' AND s.sub_orden_id = p.sub_orden_id ";
		$matriz = mysql_query($preg) or die("ERROR: Fallo selección de refacciones! " . $preg);
		$prods = mysql_fetch_array($matriz);
//		echo 'Tratando a OPID: ' . $prods['op_id'] . '<br>';
		$pend = $prods['op_cantidad'] - $prods['op_recibidos'];
		if((is_null($prods['op_cotizado_a']) || $prods['op_cotizado_a'] == '') && $prods['op_pedido'] < 1) {
			$estado = 'Por Cotizar'; $estref = 1;
		} elseif(!is_null($prods['op_cotizado_a']) && $prods['op_cotizado_a'] != '' && $prods['op_pedido'] < 1 ) {
			$estado = 'Por Pedir'; $estref = 2;
		} elseif( $prods['op_pedido'] > 0 ) {
			$estado = 'Por Recibir'; $estref = 3;
		} else {
			$estado = ''; $estref = 0;
		}

		$saltar = 0;
		if($prods['op_item_seg'] > 0) {
			$saltar = 1;
		}

		if($saltar == 0) {
			
			$sugerido = ($prods['op_costo']/(1-($margen/100)));
			$sugerido = round($sugerido, 2);
			
			echo '			
						<tr class="' . $fondo . '">
							<td>' . $prods['op_cantidad'] . '</td>
							<td>' . $pend . '</td>
							<td style="text-align: left !important;"';
			if($prods['op_pres'] == 1) { echo ' class="pre' . $fondo . '"'; } else { echo ' class="aut' . $fondo . '"'; }
			echo '
							<td>
								' . $prods['op_nombre'] . '
							</td>
							<td align="center">' . $estado . '</td>
							<td style="text-align:right;">$' . number_format($prods['op_costo'],2) . '</td>
							<td>
								$' . number_format($prods['op_precio'],2) . ' <b>|</b> $' . number_format($sugerido,2) . ''."\n";
			
			if($prods['op_costo'] > 0 && $ordenref != ''){
				echo '
								<input type="checkbox" name="actualiza[' . $prods['op_id'] . ']" value="1" />
								<input type="hidden" name="precio[' . $prods['op_id'] . ']" value="' . $sugerido . '">
								<input type="hidden" name="precio_original[' . $prods['op_id'] . ']" value="' . $prods['op_precio'] . '">
								<input type="hidden" name="cantidad[' . $prods['op_id'] . ']" value="' . $prods['op_cantidad'] . '">'."\n";
			}
			
			echo '
							</td>
							<td align="center">'."\n";
			
				
			if($estref == 2) {
      			$cot = explode('|', $prods['op_cotizado_a']);
      			$pcot = array();
					
      			foreach($cot as $k => $v) {
      			$pcot[$v] = $v;
      			}
      			$j = 0;
				$cotizados = '';	
				foreach($pcot as $i) {
					
					
      				if($j>0) { echo '<br>'; }
					
						
					// --- CONSULTAR COTIZACIONES CONTESTADAS ---
					$preg_cot_ok = "SELECT prod_costo FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $prods['op_id'] . "' AND prod_prov_id = '" . $i . "'";
					$matr_cot_ok = mysql_query($preg_cot_ok) or die("ERROR: Fallo selección de costo cotización! " . $preg_cot_ok);
					$cost_cot = mysql_fetch_assoc($matr_cot_ok);
					
					echo '
								<a href="proveedores.php?accion=consultar&prov_id=' . $i . '" target="_blank">' . $provs[$i]['nic'] . '</a>'."\n";
      				if($cost_cot['prod_costo'] == 0){
						echo '
								<img src="idiomas/' . $idioma . '/imagenes/edit-delete-6.png" alt="Desasociar" title="Desasociar">'."\n";
					} else{
							echo '
								<img src="idiomas/' . $idioma . '/imagenes/ok.png" alt="Desasociar" title="Desasociar">'."\n";
					}
					$j++;
      					
      			}
				
				
			} elseif($estref == 3) {
				
				// --- Consultar id del proveedor del pedido  ---
				$preg_prov = "SELECT prov_id FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $prods['op_pedido'] . "'";
				$matriz_prov = mysql_query($preg_prov) or die("ERROR: Fallo selección de prov! " . $preg_prov);
				$prov = mysql_fetch_assoc($matriz_prov);
				
      			echo '
								' . $ppvv['prov_id'] . '<a href="proveedores.php?accion=consultar&prov_id=' . $prov['prov_id'] . '" target="_blank">' . $provs[$prov['prov_id']]['nic'] . '</a>'."\n";

      		}
      		
			echo '
	  						</td>
							<td align="center">'."\n";
			
			
			if($prods['op_pedido'] > 1 ) {
		  
				echo '
								<a href="pedidos.php?accion=consultar&pedido=' . $prods['op_pedido'] . '" target="_blank">' . $prods['op_pedido'] . '</a>'."\n";
					
      		} elseif($estref == 2) {
      			
				echo '
								<a href="refacciones.php?accion=gestionar&orden_id=' . $prods['orden_id'] . '" target="_blank">Pedir Refacciones</a>'."\n";
		  		
			
			} else {

			}

			echo '
	  						</td>
							<td align="center">'."\n";
			
      		if(!is_null($prods['op_fecha_promesa']) && $prods['op_fecha_promesa'] != '0000-00-00 00:00:00') {
      			echo date('Y-m-d', strtotime($prods['op_fecha_promesa']));
      		}
			
			// ---- Calcular días en proceso ----
			$preg_orden = "SELECT orden_fecha_de_entrega, orden_fecha_recepcion, orden_fecha_ultimo_movimiento, orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $prods['orden_id'] . "' ";
			$matriz_orden = mysql_query($preg_orden) or die("ERROR: Fallo selección de orden! " . $preg_orden);
			$ord = mysql_fetch_assoc($matriz_orden);
			
			$fde = strtotime($ord['orden_fecha_de_entrega']);
			if($fde > strtotime('2012-01-01')) {
				$dias = intval(($fde - strtotime($ord['orden_fecha_recepcion'])) / 86400) + 1;
			} elseif($ord['orden_estatus'] == '90') {
				$dias = intval((strtotime($ord['orden_fecha_ultimo_movimiento']) - strtotime($ord['orden_fecha_recepcion'])) / 86400);
			} else {
				$dias = intval(($hoy - strtotime($ord['orden_fecha_recepcion'])) / 86400) + 1;
			}
			if(!isset($estanciamax) || $estanciamax == '') { $estanciamax = '20'; }
			if($dias > $estanciamax) {
					
				$dias = '<span style="font-weight:bold; color:red; background-color:yellow;">'.$dias.'</span>';	 
					
			}
			
			echo '
	  						</td>
							<td>' . constant('NOMBRE_AREA_' . $prods['op_area']) . '</td>
							<td align="center">
								<a href="refacciones.php?accion=gestionar&orden_id=' . $prods['orden_id'] . '" target="_blank">' . $prods['orden_id'] . '</a>
							</td>
							<td>
								' . $dias . '
							</td>
							<td>'."\n";
			
      
			if($prods['op_doc_id'] != '') {
				
				$preg4 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE doc_id = '" . $prods['op_doc_id'] . "'";
//				echo $preg4;
				$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección foto refacción!");
				$resu4 = mysql_fetch_array($matr4);
		  		
				echo '				
								<a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a><br>'."\n";
				
			}
			
			echo '				
							</td>
						</tr>'."\n";
				if($fondo == 'obscuro') { $fondo = 'claro'; } else { $fondo = 'obscuro';}
				$filapres++; 
			
		} else {

		}

		$ind++;

		if($filapres == $renglones) { break;}
		$rfinal = $ind + 1;
	}
	}

	if($export == 1){ // ---- Hoja de calculo ---- 
		
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Refacciones-Pendientes.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
		
			
	} else{ // ---- HTML ----
		
		$precio_venta = validaAcceso('1115080', $dbpfx); // --- Revisión de precios de venta --- 
		if($ordenref != 0 && ($_SESSION['rol05'] == '1' || $_SESSION['rol02'] == '1' || $precio_venta == '1')){
			echo '		
					<tr class="' . $fondo . '">
						<td colspan="5"></td>
						<td>
							<input type="hidden" name="ordenref" value="' . $ordenref . '">
							<input type="hidden" name="provref" value="' . $provref . '">
							<input type="submit" class="btn btn-success" value="Actualizar precios">
						</td>
						<td colspan="7"></td>
					</tr>'."\n";	
		}
		
		echo '
					</table>
				</div>
			</div>
		</div>
		</form>
	</div>'."\n";
	}
	
}

elseif($accion === "act_pre_venta"){
	
	//echo 'act_pre_venta<br>';
	//print_r($actualiza);
	
	$precio_venta = validaAcceso('1115080', $dbpfx); // --- Revisión de precios de venta --- 
	
	if($_SESSION['rol05'] == '1' || $_SESSION['rol02'] == '1' || $precio_venta == '1'){
		
		if($actualiza == ''){
			$_SESSION['msjerror'] = 'Debes de seleccionar mínimo una refacción para actualizar precio de venta';
			redirigir('refacciones.php?accion=pendientes&ordenref=' . $ordenref . '&provref=' . $provref);
		}
		
		foreach($actualiza as $key => $val){
			
			//echo 'op_id ' . $key . '<br>';
			//echo 'precio ' . $precio[$key] . '<br>';
			//echo 'precio_original ' . $precio_original[$key] . '<br>';
			//echo 'cantidad ' . $cantidad[$key] . '<br>';
			
			//$sub_total = $precio[$key] * $cantidad[$key];
			
			// --- Actualizar precio de venta ---
			$sql_data_array = [
				'op_precio_revisado' => 1,
				'op_precio' => $precio[$key],
				'op_precio_original' => $precio_original[$key],
			];
			$param = " op_id = '" . $key . "'";
			ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $param);
			
			$concepto = 'El usuario ' . $_SESSION['usuario'] . ' actualizó el precio de venta del op_id ' . $key;
			bitacora($ordenref, $concepto, $dbpfx);
			
		}
		$_SESSION['msjerror'] = 'Se actualizaron los precios de venta de las refacciones seleccionadas';
		redirigir('refacciones.php?accion=pendientes&ordenref=' . $ordenref . '&provref=' . $provref);
		
	}
	
}


elseif($accion==="gestionar") {

	$funnum = 1115005;
	$funnum = 1115010;
	$funnum = 1115015;
	$funnum = 1115020;
	$funnum = 1115025;
	$grvaux = validaAcceso('1115022', $dbpfx); // Vista para Auxiliares de Almacén Gestion de refaciones. 
	$ajuprecpres = validaAcceso('1115100', $dbpfx);

/*	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
*/

	$mensaje = '';
	$error = 'si'; $num_cols = 0;
	$pregunta = "SELECT sub_orden_id, sub_area, sub_estatus, sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190'";
		// ------ Filtrar búsqueda por Tarea específica ------		
		if($subflt != '' && is_numeric($subflt)) {
			$pregunta .= " AND sub_orden_id = '" . $subflt . "' "; 
		}

		// ------ Selección de áreas en las que puede participar el usuario de gestión de refacciones
		if($gestxarea == 1) {
			$pregusr = "SELECT areas FROM " . $dbpfx . "usuarios WHERE usuario = '" . $_SESSION['usuario'] . "'";
			$matrusr = mysql_query($pregusr) or die("ERROR: Fallo selección de areas de usuario! " . $pregusr);
			$uarea = mysql_fetch_array($matrusr);
			$uar = explode('|', $uarea['areas']);
			$pregunta .= " AND (";
			$cuars = count($uar); $cci = 1;
			foreach($uar as $ar) {
				$pregunta .= "sub_area = '$ar'";
				if($cci < $cuars) { $pregunta .= " OR "; }
				$cci++;
			}
			$pregunta .= ")";
		}
// ------

		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de tareas!" . $pregunta);
		$f1 = mysql_num_rows($matriz);
		$error = 'no';
		$pregunta2 = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_color, v.vehiculo_placas, v.vehiculo_serie, v.vehiculo_modelo, o.orden_estatus FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "ordenes o WHERE o.orden_id = '$orden_id' AND o.orden_vehiculo_id = v.vehiculo_id";
		$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
		$orden = mysql_fetch_array($matriz2);
		$vehiculo = datosVehiculo($orden_id, $dbpfx); 
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="840">'."\n";
		echo '		<tr><td colspan="3" style="text-align:left; font-size: 1.2em; font-weight: bold;">' . $vehiculo['completo'];
		if($subflt != '' && is_numeric($subflt)) {
			echo ' - Filtrado para la Tarea: ' . $subflt;
		}
		echo '</td></tr>'."\n";

//	echo $pregunta;
	if ($f1>0) {
		echo '		<tr><td style="text-align:left;">'."\n";
//		if($pidepres == '1' && $ongest != '1') { $ongest = '1'; }
		if(!isset($grupo) || $grupo =='') { $grupo=1; }
		if($grvaux == 1 || $_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol13']=='1') {
			echo '	<form action="refacciones.php?accion=gestionar&orden_id=' . $orden_id . '" method="post" enctype="multipart/form-data" name="grupo">'."\n";
			echo '	Grupo: <select name="grupo" onchange="document.grupo.submit()"; />'."\n";
			echo '			<option value="0"'; if($grupo==0) {echo ' selected ';} echo '>Mano de Obra</option>'."\n";
			echo '			<option value="1"'; if($grupo==1) {echo ' selected ';} echo '>Refacciones</option>'."\n";
			echo '			<option value="2"'; if($grupo==2) {echo ' selected ';} echo '>Consumibles</option>'."\n";
			echo '			<option value="3"'; if($grupo==3) {echo ' selected ';} echo '>Chatarra</option></select>'."\n";
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
		$cotizarantes = $cotizar;
		while ($sub = mysql_fetch_array($matriz)) {
//------------------- Deshabilitar cotización múltiple para Aseguradoras y convenios sin Autosurtido
//			$cotsimnoaut es la variable de configuración para controlar si se deshabilita o no.
			if($cotsimnoaut == 1 && $autosurt[$sub['sub_aseguradora']] == '0' && $sub['sub_aseguradora'] > 0) { 
				$cotizar = 0;
			} else {
				$cotizar = $cotizarantes;
			}
//-------------------
// ------ Cambiar a cotización directa en consumibles y Mano de Obra
			if(($grupo==2 || $grupo==0) && $cotizadirecto == 1) { $cotizar = 0;}
// ------------------
      	$pregunta1 = "SELECT o.op_id, o.prod_id, o.op_item, o.op_item_seg, o.op_cantidad, o.op_nombre, o.op_codigo, o.sub_orden_id, o.op_estructural, o.op_tangible, o.op_surtidos, o.op_reservado, o.op_pedido, o.op_fecha_promesa, o.op_ok, o.op_costo, o.op_precio, o.op_precio_original, o.op_precio_revisado, o.op_doc_id, o.op_autosurtido, o.op_pres";
//      	if($sub['sub_reporte'] == '0') { $pregunta1 .= ", p.prod_cantidad_existente, p.prod_cantidad_disponible";	}
      	$pregunta1 .= " FROM " . $dbpfx . "orden_productos o";
//      	if($sub['sub_reporte'] == '0') {$pregunta1 .= ", " . $dbpfx . "productos p ";}
			$pregunta1 .= " WHERE o.sub_orden_id = '" . $sub['sub_orden_id'] . "' AND o.op_tangible = '$grupo'";
      	if($soloautorizadas == '1') { $pregunta1 .= " AND o.op_pres IS NULL "; }
//      	elseif($sin_autorizadas == '1') { $pregunta1 .= " AND o.op_pres = '1' "; }
      	
//      	echo $pregunta1;
//			$pregunta1 .= " ORDER BY o.op_item";
//------	Deshabilitar cotización múltiple para Aseguradoras y convenios sin Autosurtido
//			$cotsimnoaut es la variable de configuración para controlar si se deshabilita o no.
			if($cotsimnoaut == 1 && $autosurt[$sub['sub_aseguradora']] == '0') { $cotizar = 0;}
//------
			$matriz1 = mysql_query($pregunta1) or die("ERROR: Fallo seleccion! " . $pregunta1);
			$f2 = mysql_num_rows($matriz1);

			// --- Guardar los items en seguimiento ---
			while($ops = mysql_fetch_array($matriz1)) {
				$itsseg[$ops['op_id']] = [
					'op_item' => $ops['op_item'],
					'op_costo' => $ops['op_costo'],
					'op_cantidad' => $ops['op_cantidad'],
					'op_pedido' => $ops['op_pedido']
				];
			}
			mysql_data_seek($matriz1, 0);
			// ------

//------ Obtener sólo las cotizaciones que pertenecen a esta Tarea para buscar sólo en estos las de las refacciones de la Tarea!! ---
			$preg3 = "SELECT * FROM " . $dbpfx . "prod_prov WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección! " . $preg3);
			while ($opv = mysql_fetch_array($matr3)) {
				$opprov[$opv['op_id']][] = [
					'prod_costo' => $opv['prod_costo'],
					'prod_prov_id' => $opv['prod_prov_id'],
					'dias_entrega' => $opv['dias_entrega'],
					'dias_credito' => $opv['dias_credito'],
					'fecha_cotizado' => $opv['fecha_cotizado'],
					'sub_orden_id' => $opv['sub_orden_id'],
					'cotqv' => $opv['cotqv'],
					'prod_mensaje' => $opv['prod_mensaje'],
					'prod_vencimiento' => $opv['prod_vencimiento'],
					'prod_origen' => $opv['prod_origen'],
					'prod_condicion' => $opv['prod_condicion'],
					'prod_disponibilidad' => $opv['prod_disponibilidad'],
					'prod_costo_envio' => $opv['prod_costo_envio'],
				];
			}

			if($f2 > 0) {
				$jj++;
				while($prods = mysql_fetch_array($matriz1)) {
//					echo 'Aseg: ' . $sub['sub_aseguradora'] . ' Autosurt: ' . $prods['op_autosurtido'] . '<br>';
					if(($prods['op_autosurtido'] != '1' || $sub['sub_aseguradora'] == '0' ) && $prods['op_pres'] != '1') {
						$tot_auth = $tot_auth + ($prods['op_cantidad'] * $prods['op_precio']);
					}
					if($prods['op_pres'] == '1') { $tr = 'pre'; $fondo = $tr.$fondo; } else { $tr = 'aut'; $fondo = $tr.$fondo; } 
					$ref[$tr][$j] = '';

					$itseg = $itsseg[$prods['op_item_seg']];
					if($grupo == 0 && $prods['op_cantidad'] == 0) { $prods['op_cantidad'] = 1; }
					if(($prods['op_reservado'] < $prods['op_cantidad'] || $prods['op_tangible'] == '0' ) && $prods['op_pedido'] < '1' && (is_null($prods['op_item_seg']) || $prods['op_item_seg'] < 1)) {
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
							} elseif($grvaux == 1 || $_SESSION['rol02']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol12']=='1') {
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
							if($grvaux == 1 || $_SESSION['rol08']=='1') {
								if($prods['prod_cantidad_disponible'] >= $prods['op_cantidad'] && $prods['op_cantidad'] > '0') {
									$ref[$tr][$j] .= '<td colspan="3">Disponible en Almacén. ¿Reservar? <input type="checkbox" name="reservar[' . $j . ']" value="' . $prods['op_cantidad'] . '" /><input type="hidden" name="nvodisp[' . $j . ']" value="'; $ref[$tr][$j] .= $prods['prod_cantidad_disponible'] - $prods['op_cantidad']; $ref[$tr][$j] .= '" /></td>'."\n";
								} else {
									if($cotizar == '1') {
										if(count($opprov[$prods['op_id']]) > 0) {
											$ref[$tr][$j] .= '<td colspan="3"><table cellpadding="2" cellspacing="0" border="0" class="izquierda">'."\n";
//											$ref[$tr][$j] .= '							<tr><td>' . $lang['Proveedor'] . '</td><td>' . $lang['FechaCotización'] . '</td><td>' . $lang['Costo'] . '</td><td>' . $lang['DíasCrédito'] . '</td><td>' . $lang['DíasEntrega'] . '</td><td><a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['AyudaDatosCotizaciones'] . '&base=refacciones.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=700,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;">' . $lang['OCM'] . '</a></td><td>' . $lang['AgregarPedido'] . '</td></tr>'."\n";
											$ref[$tr][$j] .= '							<tr><td>' . $lang['Proveedor'] . '</td><td>' . $lang['FechaCotización'] . '</td><td>' . $lang['Costo'] . '</td><td>' . $lang['DíasCrédito'] . '</td><td>' . $lang['DíasEntrega'] . '</td><td><div style="position: relative; display: inline-block;"><a onclick="muestraAbajo' . $prods['op_id'] . '()" class="ayuda" >' . $lang['OCM'] . '</a><div id="AyudaDatosCotizaciones' . $prods['op_id'] . '" class="muestra-contenido">' . $ayuda['AyudaDatosCotizaciones'] . '</div></div></td><td>' . $lang['AgregarPedido'] . '</td></tr>'."\n";
											$ref[$tr][$j] .= '							<script>
								function muestraAbajo' . $prods['op_id'] . '() {
    								document.getElementById("AyudaDatosCotizaciones' . $prods['op_id'] . '").classList.toggle("mostrar");
								}
							</script>'."\n";
											foreach($opprov[$prods['op_id']] as $miop => $prov3) {
												$f_promesa = dia_habil ($prov3['dias_entrega']);
												$ocm = substr($prov3['prod_origen'],0,1) . substr($prov3['prod_condicion'],0,1);
												if($prov3['prod_mensaje'] != '') { $ocm .= 'M';}
												if($prov3['prod_costo_envio'] > 0) { $ocm .= '$'.$prov3['prod_costo_envio'];}
												$ref[$tr][$j] .= '							<tr><td>' . $provs[$prov3['prod_prov_id']]['nic'] . '</td><td>' . date('d-m-Y', strtotime($prov3['fecha_cotizado'])) . '</td><td>$ ' . number_format($prov3['prod_costo'],2) . '</td><td>' . $prov3['dias_credito'] . '</td><td>' . $prov3['dias_entrega'] . '</td><td>' . $ocm . '</td>';
												if($_SESSION['rol08']=='1' || validaAcceso('1050002', $dbpfx) == 1 && (strtotime($prov3['prod_vencimiento']) > time() || strtotime($prov3['prod_vencimiento']) < 1)) {
													$ref[$tr][$j] .= '<td style="width:60px;text-align:center;"><input type="radio" name="sel_prov[' . $j . ']" value="' . $prov3['prod_prov_id'] . '" /><input type="hidden" name="op_costo[' . $j . '][' . $prov3['prod_prov_id'] . ']" value="' . $prov3['prod_costo'] . '" /></td>';
												} elseif($_SESSION['rol08']=='1') {
													$ref[$tr][$j] .= '<td style="text-align:center;">' . $lang['Vencida'] . '</td>';
												} else {
													$ref[$tr][$j] .= '<td></td>';
												}
												$ref[$tr][$j] .= '</tr>'."\n";
											}
											$ref[$tr][$j] .= '							<tr>';
											if($_SESSION['rol08']=='1' || validaAcceso('1050003', $dbpfx) == 1) {
												$ref[$tr][$j] .= '<td colspan="2" style="text-align:left;">Seleccionar para pedir cotizaciones adicionales: <input type="checkbox" name="selecionado[' . $j . ']" value="1" /></td><td colspan="2"><a href="refacciones.php?accion=cargacotiza&op_id=' . $prods['op_id'] . '&orden_id=' . $orden_id . '">Cargar datos de cotizaciones</a></td>';
											} else {
												$ref[$tr][$j] .= '<td colspan="4"></td>';
											}
											$ref[$tr][$j] .= '<td colspan="2" style="text-align:left;">Foto: <a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a></td></tr>'."\n";
											$ref[$tr][$j] .= '						</table>'."\n";
										} else {
											if($tr == 'aut' && $soloautorizadas != '1') {
												$ref[$tr][$j] .= '<td style="text-align:left;">';
												if($_SESSION['rol08']=='1' || $grvaux == 1) {
													$ref[$tr][$j] .= '<input type="text" name="asoc_item[' . $j . ']" size="2"> Asociar con Item de Presupuesto';
												}
												$ref[$tr][$j] .= '</td><td colspan="2">';
											} else {
												$ref[$tr][$j] .= '<td colspan="3">';
											}
											if($_SESSION['rol08']=='1' || validaAcceso('1050003', $dbpfx) == 1) {
												$ref[$tr][$j] .= 'No hay cotizaciones disponibles para este producto<br><a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a> Solicitar cotización a proveedor seleccionado abajo: <input type="checkbox" name="selecionado[' . $j . ']" value="1"';
												if($ref_presel == '1') { $ref[$tr][$j] .= ' checked="checked"'; }
												$ref[$tr][$j] .= ' />';
												$ref[$tr][$j] .= '<br><input class="btn" type="file" name="fotoref[' . $j . ']" />
												<a href="refacciones.php?accion=ligar&op_id=' . $prods['op_id'] . '&orden_id=' . $orden_id . '&nombre=' . $prods['op_nombre'] . '">
													<img src="idiomas/' . $idioma . '/imagenes/camara.png" alt="Ligar a foto de documentos asociados" title="Ligar a foto de documentos asociados" width="23" height="23">
												</a>'."\n";
											}
											$ref[$tr][$j] .= '</td>'."\n";
										}
									} else {
										$ref[$tr][$j] .= '<td colspan="3" style="text-align:left;">';
										if($tr == 'aut' && $soloautorizadas != '1' && ($_SESSION['rol08']=='1' || $grvaux == 1)) {
											$ref[$tr][$j] .= '<input type="text" name="asoc_item[' . $j . ']" size="2"> Asociar con Item de Presupuesto o ';
										}
										if($_SESSION['rol08']=='1' || validaAcceso('1050002', $dbpfx) == 1) {
											$ref[$tr][$j] .= 'Seleccionar para proveedor: <input type="checkbox" name="selecionado[' . $j . ']" value="1"';
											if($ref_presel == '1') { $ref[$tr][$j] .= ' checked="checked"'; }
											$ref[$tr][$j] .= ' />';
										}
										$ref[$tr][$j] .= '</td></tr>'."\n";
										$ref[$tr][$j] .= '						<tr>';
										if($ajuprecpres == 1 && $tr == 'pre') { // $ajuprecpres es permiso por usuario 
											if($revprevent == '' || ($revprevent == '1' && validaAcceso('1115110', $dbpfx) == 1)) {
												// --- Controla si se muestra o no la información de precios de venta a usuarios
												if($prods['op_precio_revisado'] == '0') {
													$ref[$tr][$j] .= '<td>Valuación original: $' . number_format($prods['op_precio'],2) . '. Precio Revisado: $';
													if($grvaux == 1) {
														$ref[$tr][$j] .= number_format($prods['op_precio'],2);
													} else {
														$ref[$tr][$j] .= ' <input type="text" name="precio[' . $j . ']" size="6" />';
													}
														$ref[$tr][$j] .= '</td>'."\n";
												} elseif($prods['op_precio_revisado'] == '2') {
													$ref[$tr][$j] .= '<td>Valuación original: $' . number_format($prods['op_precio_original'],2) . ' Precio Revisado: $';
													if($grvaux == 1) {
														$ref[$tr][$j] .= number_format($prods['op_precio'],2);
													} else {
														$ref[$tr][$j] .= '<input type="text" name="precio[' . $j . ']" size="6" value="' . number_format($prods['op_precio'],2) . '" />';
													}
													$ref[$tr][$j] .= '</td>'."\n";
												} else {
													$ref[$tr][$j] .= '<td>Valuación original: $' . number_format($prods['op_precio_original'],2) . ' Precio Revisado: $';
													if($grvaux == 1) {
														$ref[$tr][$j] .= number_format($prods['op_precio'],2);
													} else {
														$ref[$tr][$j] .= '<input type="text" name="precio[' . $j . ']" size="6" value="' . number_format($prods['op_precio'],2) . '" />';
													}
													$ref[$tr][$j] .= '</td>'."\n";
												}
											} else {
												$ref[$tr][$j] .= '<td></td>'."\n";
											}
											$ref[$tr][$j] .= '<td colspan="2">Refacción NO gestionada aún.<br>Foto de refacción: <a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a>';
											if($grvaux != 1) {
												$ref[$tr][$j] .= '<input class="btn" type="file" name="fotoref[' . $j . ']" />
												<a href="refacciones.php?accion=ligar&op_id=' . $prods['op_id'] . '&orden_id=' . $orden_id . '&nombre=' . $prods['op_nombre'] . '">
													<img src="idiomas/' . $idioma . '/imagenes/camara.png" alt="Ligar a foto de documentos asociados" title="Ligar a foto de documentos asociados" width="23" height="23">
												</a>';
											}
											$ref[$tr][$j] .= '</td>'."\n";
										} else {
											$ref[$tr][$j] .= '<td colspan="3">Refacción NO gestionada aún.<br>Foto de refacción: <a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a>';
											if($grvaux != 1) {
												$ref[$tr][$j] .= '<input class="btn" type="file" name="fotoref[' . $j . ']" />
												<a href="refacciones.php?accion=ligar&op_id=' . $prods['op_id'] . '&orden_id=' . $orden_id . '&nombre=' . $prods['op_nombre'] . '">
													<img src="idiomas/' . $idioma . '/imagenes/camara.png" alt="Ligar a foto de documentos asociados" title="Ligar a foto de documentos asociados" width="23" height="23">
												</a>';
											}
											$ref[$tr][$j] .= '</td>'."\n";
										}
									}
								}
							} elseif($_SESSION['rol05']=='1') {
								if($prods['op_item_seg'] != '') {
									$ref[$tr][$j] .= '<td colspan="3">Refacción Gestionada con el Item ' . $itseg['op_item'] . ' de presupuestados.';
								} else {
									$ref[$tr][$j] .= '<td colspan="3">Refacción no Gestionada aún.';
								}
								$ref[$tr][$j] .= '<br>Foto: <a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a> <input class="btn" type="file" name="fotoref[' . $j . ']" size="30" />
												<a href="refacciones.php?accion=ligar&op_id=' . $prods['op_id'] . '&orden_id=' . $orden_id . '&nombre=' . $prods['op_nombre'] . '">
													<img src="idiomas/' . $idioma . '/imagenes/camara.png" alt="Ligar a foto de documentos asociados" title="Ligar a foto de documentos asociados" width="23" height="23">
												</a></td>'."\n";
							} elseif($_SESSION['rol07']=='1') {
								if($prods['op_item_seg'] != '') {
									$ref[$tr][$j] .= '<td colspan="3">Refacción Gestionada con el Item ' . $itseg['op_item'] . ' de presupuestados.';
								} else {
									$ref[$tr][$j] .= '<td colspan="3">Refacción no Gestionada aún.';
								}
								$ref[$tr][$j] .= '<br>Foto: <a href="' . DIR_DOCS . $resu4['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' . $resu4['doc_archivo'] . '" alt="" /></a> <input class="btn" type="file" name="fotoref[' . $j . ']" size="30" />
												<a href="refacciones.php?accion=ligar&op_id=' . $prods['op_id'] . '&orden_id=' . $orden_id . '&nombre=' . $prods['op_nombre'] . '">
													<img src="idiomas/' . $idioma . '/imagenes/camara.png" alt="Ligar a foto de documentos asociados" title="Ligar a foto de documentos asociados" width="23" height="23">
												</a></td>'."\n";
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
					} elseif($prods['op_pedido'] > 0 || $prods['op_item_seg'] > 0) {
//						echo '				<tr class="' . $fondo . '"><td>'."\n";
						$ref[$tr][$j] .= '					<table border="1" cellpadding="2" cellspacing="0" width="100%">
						<tr><td style="font-weight:bold; text-align:left;"><a name="' . $prods['op_id'] . '"></a>' . $prods['op_cantidad'] . ' ' . $prods['op_nombre'];
						$ref[$tr][$j] .= '<input type="hidden" name="op_id[' . $j . ']" value="' . $prods['op_id'] . '" />';
						$ref[$tr][$j] .= '<input type="hidden" name="prod_id[' . $j . ']" value="' . $prods['prod_id'] . '" /></td>'."\n";
						if($grvaux == 1 || $_SESSION['rol08'] ==1 || $_SESSION['rol02'] ==1 || $_SESSION['rol12'] ==1 || $_SESSION['rol13'] ==1 || $_SESSION['rol05'] ==1) {
							$ref[$tr][$j] .= '<td style="text-align:right;"><input type="hidden" name="op_costo[' . $j . ']" value="' . $prods['op_costo'] . '"/>Costo: $' . number_format($prods['op_costo'],2) . '</td>'."\n";
						} else {
								$ref[$tr][$j] .= '<td></td>';
						}
						$ref[$tr][$j] .= '<td style="text-align:right;">Tarea: ' . $prods['sub_orden_id'] . ' Item #' . $prods['op_item'] . '</td><tr>'."\n";
						$ref[$tr][$j] .= '						<tr>';
//						if(($_SESSION['rol02'] ==1 || $_SESSION['rol12'] ==1) && $tr == 'aut') {
						if(($grvaux == 1 || $_SESSION['rol02'] == 1 || $_SESSION['rol08'] == 1 || $_SESSION['rol12'] == 1)) {
							if($prods['op_item_seg'] != '') {
								if($prods['op_precio'] == 0 && $itseg['op_costo'] > 0) {
									$margen = -100;
								} else {
									$margen = round((((($prods['op_precio'] * $prods['op_cantidad']) - ($itseg['op_costo'] * $itseg['op_cantidad'])) / ($prods['op_precio'] * $prods['op_cantidad'])) * 100), 2);
								}
								$item_asociado = 1;
							} elseif($prods['op_pres'] == 1 && $prods['op_pedido'] > 0) {
								$margen = round((((($prods['op_precio'] * $prods['op_cantidad']) - ($prods['op_costo'] * $prods['op_cantidad'])) / ($prods['op_precio'] * $prods['op_cantidad'])) * 100), 2);
								$item_asociado = 1;
							} else {
								if($prods['op_precio'] == 0 && $prods['op_costo'] > 0) {
									$margen = -100;
								} else {
									$margen = round(((($prods['op_precio'] - $prods['op_costo'])/$prods['op_precio']) * 100), 2);
								}
							}
							if($revprevent != '1' || ($revprevent == '1' && validaAcceso('1115110', $dbpfx) == 1)) {
								// --- Controla si se muestra o no la información de precios de venta a usuarios
								if($prods['op_precio_revisado'] == '0') {
									$ref[$tr][$j] .= '<td>Valuación original: ' . number_format($prods['op_precio'],2) . '<br>Precio Revisado:';
									if($grvaux == 1) {
										if(validaAcceso('1115110', $dbpfx) == 1){
											$ref[$tr][$j] .= ' <input type="text" name="precio[' . $j . ']" size="6" />';
										} else{
											$ref[$tr][$j] .= number_format($prods['op_precio'],2);	
										}
									} else {
										$ref[$tr][$j] .= ' <input type="text" name="precio[' . $j . ']" size="6" />';
									}
									$ref[$tr][$j] .= ALARMA_1 . '<br>Utilidad:' . $margen . '%</td>'."\n";
								} elseif($prods['op_precio_revisado'] == '2') {
									$ref[$tr][$j] .= '<td>Valuación original: ' . number_format($prods['op_precio_original'],2) . '<br>Precio Revisado:';
									if($grvaux == 1) {
										if(validaAcceso('1115110', $dbpfx) == 1){
											$ref[$tr][$j] .= '<input type="text" name="precio[' . $j . ']" size="6" value="' . number_format($prods['op_precio'],2) . '" />';
										} else{
											$ref[$tr][$j] .= number_format($prods['op_precio'],2);	
										}
									} else {
										$ref[$tr][$j] .= '<input type="text" name="precio[' . $j . ']" size="6" value="' . number_format($prods['op_precio'],2) . '" />';
									}
									$ref[$tr][$j] .= ALARMA_2 . '<br>Utilidad:' . $margen . '%</td>'."\n";
								} else {
									$ref[$tr][$j] .= '<td>Valuación original: ' . number_format($prods['op_precio_original'],2) . '<br>Precio Revisado:';
									if($grvaux == 1) {
										if(validaAcceso('1115110', $dbpfx) == 1){
											$ref[$tr][$j] .= '<input type="text" name="precio[' . $j . ']" size="6" value="' . number_format($prods['op_precio'],2) . '" />';
										} else{
											$ref[$tr][$j] .= number_format($prods['op_precio'],2);	
										}
									} else {
										$ref[$tr][$j] .= '<input type="text" name="precio[' . $j . ']" size="6" value="' . number_format($prods['op_precio'],2) . '" />';
									}
									$ref[$tr][$j] .= ALARMA_0 . '<br>Utilidad:' . $margen . '%</td>'."\n";
								}
							} else {
								$ref[$tr][$j] .= '<td></td>'."\n";
							}
//							if(($prods['op_autosurtido'] == '2' || $prods['op_autosurtido'] == '3') && $tr == 'aut') {
							if($prods['op_autosurtido'] == '2' || $prods['op_autosurtido'] == '3') {
								$tot_costo = $tot_costo + ($prods['op_costo'] * $prods['op_cantidad']);
							}
							if(($prods['op_autosurtido'] == '2' || $prods['op_autosurtido'] == '3') || ($prods['op_item_seg'] > 0 && ($item_seg['op_autosurtido'] == '2' || $item_seg['op_autosurtido'] == '3'))) {
									$tot_precio = $tot_precio + ($prods['op_precio'] * $prods['op_cantidad']);	
							}
							$ref[$tr][$j] .= '							<td colspan="2">';
						} else {
							$ref[$tr][$j] .= '							<td colspan="3">';
						}
						if($prods['op_item_seg'] != '') {
							$ref[$tr][$j] .= 'Este producto fue gestionado con el ';
							if(($grvaux == 1 || $_SESSION['rol02'] ==1 || $_SESSION['rol08'] ==1) && $tr == 'aut') {
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
							if($grvaux == 1 || $_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol13']=='1') { 
								$ref[$tr][$j] .= '<a href="pedidos.php?accion=consultar&pedido=' . $prods['op_pedido'] . '">Pedido: ' . $prods['op_pedido'] . '</a> a '."\n"; 
							}
							$ref[$tr][$j] .= $provs[$resu4['prov_id']]['nic'] . '<br>';
							if($prods['op_ok'] == '1') {
								$ref[$tr][$j] .= 'Recibido OK el: ' . $prods['op_fecha_promesa']; 
							} else { 
								$ref[$tr][$j] .= '<span style="color:red;">Pendiente NO recibido</span> con promesa de entrega el: ' . $prods['op_fecha_promesa']; 
							}
						}
						if($cotizar == 1) {
							$ref[$tr][$j] .= '<br><a href="refacciones.php?accion=vercotiza&op_id=' . $prods['op_id'] . '&orden_id=' . $orden_id . '">Ver Cotizaciones</a>';
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
						} elseif($grvaux == 1) {
							$ref[$tr][$j] .= '<td>' . number_format($prods['op_costo'],2) . '</td>';
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
		$fpre = count($ref['pre']); // echo 'presupestados: ' . $fpre . '<br>'; print_r($ref['pre']); 
		$faut = count($ref['aut']); // echo 'autorizados: ' . $faut . '<br>'; print_r($ref['aut']);
		if($refhorz == '1') {
			echo '		<tr><td class="preobscuro" style="text-align:center; font-size:1.2em; font-weight:bold;">Presupuestados</td></tr>'."\n";
			foreach($ref['pre'] as $k){
				if($fondo == 'claro') {$fondo = 'obscuro';} else {$fondo = 'claro';}
				echo '		<tr><td class="pre' . $fondo . '" style="text-align:center; vertical-align:top;">' . $k . '</td></tr>'."\n";
			}
			echo '		<tr><td class="autobscuro" style="text-align:center; font-size:1.2em; font-weight:bold;">Autorizados</td></tr>'."\n";
			foreach($ref['aut'] as $k){
				if($fondo == 'claro') {$fondo = 'obscuro';} else {$fondo = 'claro';}
				echo '		<tr><td class="aut' . $fondo . '" style="text-align:center; vertical-align:top;">' . $k . '</td></tr>'."\n";
			}
		} else {
			if($fpre > $faut) { $filas = $fpre; } else { $filas = $faut; }
			echo '		<tr>'."\n";
			$ancho2 = '100%';
			if($soloautorizadas != '1') {
				$ancho2 = '50%';
				echo '			<td class="preobscuro" style="text-align:center; font-size:1.2em; font-weight:bold; width:' . $ancho2 . ';">Presupuestados</td>'."\n";
			}
			echo '			<td class="autobscuro" style="text-align:center; font-size:1.2em; font-weight:bold; width:' . $ancho2 . ';">Autorizados</td>'."\n";
			echo '			</td></tr>'."\n";			
			$fondo = 'claro';
			echo '		<tr>'."\n";
			if($soloautorizadas != '1') {
				echo '			<td class="pre' . $fondo . '" style="text-align:center; vertical-align:top;">'."\n";
				echo reset($ref['pre']);
				echo '			</td>'."\n";
			}
			echo '			<td class="aut' . $fondo . '" style="text-align:center; vertical-align:top;">'."\n";
			echo reset($ref['aut']);
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
		}

//		echo '			</table></td></tr>'."\n";

		if(validaAcceso('1115115', $dbpfx) == 1 || $_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol07']=='1') {
			if(validaAcceso('1115115', $dbpfx) == 1 || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1') {
				$tot_margen = round(((($tot_auth - $tot_costo) / $tot_auth) * 100), 2);
				echo '		<tr class="cabeza_tabla"><td colspan="2">Costo de Refacciones Gestionadas: $' . number_format($tot_costo, 2) . '. Venta de Refacciones Autorizadas: $' . number_format($tot_auth, 2) . '. Utilidad: ' . $tot_margen . '%</td></tr>'."\n";
			}
			echo '		<tr><td style="text-align:left;" colspan="2">'."\n";
			echo '			<table cellpadding="2" cellspacing="0" border="0">'."\n";
			echo '				<tr><td>Tipo de Solicitud:</td><td style="text-align:left;"><select name="tipo_pedido" /><option value="">Seleccionar...</option>'."\n";

			if($_SESSION['rol08']=='1' || validaAcceso('1050002', $dbpfx) == 1) {
				echo '				<option value="1">' . TIPO_PEDIDO_1 . '</option>'."\n";
				echo '				<option value="2">' . TIPO_PEDIDO_2 . '</option>'."\n";
				echo '				<option value="3">' . TIPO_PEDIDO_3 . '</option>'."\n";
			}

			if($_SESSION['rol08']=='1' || validaAcceso('1050003', $dbpfx) == 1) {
				echo '				<option value="10">' . TIPO_PEDIDO_10 . '</option>'."\n";
				if($cotizataller == '1') { echo '			<option value="11">' . TIPO_PEDIDO_11 . '</option>'."\n"; }
				echo '				<option value="6" selected>' . TIPO_PEDIDO_6 . '</option>'."\n";
				echo '				<option value="5">' . TIPO_PEDIDO_5 . '</option>'."\n";
			}
			if($_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || ($_SESSION['rol08']=='1' && $ajuprecpres == 1)) {
				echo '				<option value="4"';
				if($_SESSION['rol02']=='1' || $_SESSION['rol12']=='1') { echo ' selected ';} 
				echo '>' . TIPO_PEDIDO_4 . '</option>'."\n";
			}
			if($_SESSION['rol05']=='1' || $_SESSION['rol07']=='1') {
				echo '				<option value="5" selected>' . TIPO_PEDIDO_5 . '</option>'."\n";
			}
			echo '			</select></td></tr>'."\n";
			if($_SESSION['rol08']=='1' || validaAcceso('1050003', $dbpfx) == 1 || validaAcceso('1050002', $dbpfx) == 1) {
				echo '			<tr><td>Proveedor:</td><td style="text-align:left;"><select name="prov_selec[]" multiple="multiple" size="4"/>'."\n";
				foreach($provs as $k => $v) {
					echo '				<option value="' . $k . ':' .$v['dde'] . '">' . $v['nic'] . '</option>'."\n";
				}
				echo '			</select></td></tr>'."\n";
//				echo 'Descuento Global a Costo de Compra <input type="text" name="descuento" value="" size="4" /><br>en seleccionadas:<br>';
			} elseif($_SESSION['rol12']=='1' && $grupo == 1) {
//				echo 'Descuento Global en Precio de Venta <input type="text" name="descuento" value="" size="4" /><br> de Refacciones:<br>';
			}
			echo '			<tr><td>¿Enviar Pedido por email?</td><td style="text-align:left;"><input type="checkbox" name="siemail" value="1" checked="checked" /></td></tr>'."\n";
			echo '			<tr><td>Comentario Opcional<br>dentro del email de<br>Cotización o Pedido:</td><td style="text-align:left;"><textarea name="instruccion" cols="40" rows="4"></textarea></td></tr>'."\n";
			echo '			</table>'."\n";
			echo '		</td></tr>'."\n";
		}
		
		echo '		<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>'."\n";
		if($imprimelista == '1') {
			echo ' &nbsp; <a href="refacciones.php?accion=imprimelista&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="Imprimir Listado" title="Imprimir Listado"></a>'."\n";
		}
		echo '</div></td></tr>'."\n";
		if($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol07']=='1') {
			echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;<input type="hidden" name="orden_id" value="' . $orden_id . '" /><input type="hidden" name="sub_reporte" value="' . $sub['sub_reporte'] . '" /><input type="hidden" name="grupo" value="' . $grupo . '" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Limpiar selecciones" /></td></tr>'."\n";
		}
		echo '	</table>'."\n";
		if($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol07']=='1') {
	echo '	</form>';
		}
	} else {
		echo '		<tr><td colspan="3" style="text-align:left;">' . $lang['No hay tareas'] . '</td></tr>'."\n";
		echo '		<tr><td colspan="3" style="text-align:left;"><div class="control"><a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a></div></td></tr>'."\n";
		echo '		</table>'."\n";
	}
}

elseif($accion==="gestiona") {
	
	$funnum = 1115005;
	$funnum = 1115010;
	$funnum = 1115015;
	$funnum = 1115020;
	$funnum = 1115025;
	$grvaux = validaAcceso('1115022', $dbpfx); // Vista para Auxiliares de Almacén Gestion de refaciones.

	if ($_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol07']=='1' || $grvaux == '1') {
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
		$pregi = "SELECT * FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $it[$asoc_item[$i]] . "'";
//		echo '<br>' .$pregi;
		$matri = mysql_query($pregi) or die("ERROR: Fallo selección de productos!");
		$ii = mysql_fetch_array($matri);
		if($asoc_item[$i] > 0) {
			if($ii['op_pedido'] > 0) {
				$preg6 = "UPDATE " . $dbpfx . "orden_productos SET op_item_seg = '" . $it[$asoc_item[$i]] . "' WHERE op_id = '" . $v ."'";
//				echo '<br>' .$preg6;
				$matr6 = mysql_query($preg6) or die("ERROR: Fallo actualización de productos!");
				$archivo = '../logs/' . time() . '-base.ase';
				$myfile = file_put_contents($archivo, $preg6 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				if($qv_activo == 1) {
					$xmlitem .= '			<Ref op_id="' . $v . '" op_estatus="90" />'."\n";
				}
				if($ii['op_ok'] == 1) {
					$preg6 = "UPDATE " . $dbpfx . "orden_productos SET op_recibidos = op_cantidad, op_ok = 1, op_costo = 0, op_subtotal = 0, op_fecha_promesa = '"  . $ii['op_fecha_promesa'] . "', op_autosurtido = '" . $ii['op_autosurtido'] . "' WHERE op_id = '" . $v ."'";
					$matr6 = mysql_query($preg6) or die("ERROR: Fallo actualización de productos!");
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $preg6 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				}
				$preg6 = "SELECT sub_estatus, sub_mo, sub_consumibles, sub_aseguradora, sub_descuento FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $ii['sub_orden_id'] . "'";
				$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de tarea! ". $preg6);
				$subid = mysql_fetch_array($matr6);
				$subop[$ii['sub_orden_id']]['estatus'] = $subid['sub_estatus'];
				$subop[$ii['sub_orden_id']]['mo'] = $subid['sub_mo'];
				$subop[$ii['sub_orden_id']]['cons'] = $subid['sub_consumibles'];
				$subop[$ii['sub_orden_id']]['aseg'] = $subid['sub_aseguradora'];
				$subop[$ii['sub_orden_id']]['pago_id'] = $subid['sub_descuento'];
				$selecionado[$i] = 0;
			} else {
				$mensaje .= 'El Item ' . $asoc_item[$i] . ' no tiene pedido y debe tenerlo para poder asociarlo.<br>'; $error = 'si';
			}
		}
// ---------------- Ajusta Código para que sea independiente al tipo de pedido
		if($ajustacodigo == 1) {
			$op_codigo[$i] = preparar_entrada_bd($op_codigo[$i]);
			if($op_codigo[$i] != $ii['op_codigo']) {
				unset($datacod);
				$param = "op_id = '" . $v . "'";
				$datacod['op_codigo'] = $op_codigo[$i];
				$concepto = 'Código cambiado de ' . $ii['op_codigo'] . ' a ' . $op_codigo[$i] . ' en ' . $ii['op_nombre'] . ' OP:' . $v;
				bitacora($orden_id, $concepto, $dbpfx);
				ejecutar_db($dbpfx . 'orden_productos', $datacod, 'actualizar', $param);
			}
		}
// ------------------------------------------------------------------------------

		if($rem_item[$i] == 1) {
			$preg6 = "UPDATE " . $dbpfx . "orden_productos SET op_item_seg = NULL, op_fecha_promesa = NULL, op_recibidos = 0, op_ok = 0, op_autosurtido = 0 WHERE op_id = '" . $v ."'";
			$matr6 = mysql_query($preg6) or die("ERROR: Fallo actualización de productos!");
			$archivo = '../logs/' . time() . '-base.ase';
			$myfile = file_put_contents($archivo, $preg6 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
			$pregi = "SELECT o.sub_orden_id, o.op_cantidad, o.op_nombre, o.op_codigo, d.doc_archivo FROM " . $dbpfx . "orden_productos o LEFT JOIN " . $dbpfx . "documentos d ON o.op_doc_id = d.doc_id WHERE o.op_id = '" . $v . "'";
			$matri = mysql_query($pregi) or die("ERROR: Fallo selección de productos! ".$pregi);
			$ii = mysql_fetch_array($matri);
//			if($qv_activo == 1) {
//				$xmlitem .= '			<Ref op_id="' . $v . '" op_cantidad="' . $ii['op_cantidad'] . '" op_nombre="' . $ii['op_nombre'] . '" op_codigo="' . $ii['op_codigo'] . '" op_estatus="10" foto_ref="' . $ii['doc_archivo'] . '" />'."\n";
//			}
			$preg6 = "SELECT sub_estatus, sub_mo, sub_consumibles, sub_aseguradora, sub_descuento FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $ii['sub_orden_id'] . "'";
			$matr6 = mysql_query($preg6) or die("ERROR: Fallo selección de tarea! ". $preg6);
			$subid = mysql_fetch_array($matr6);
			$subop[$ii['sub_orden_id']]['estatus'] = $subid['sub_estatus'];
			$subop[$ii['sub_orden_id']]['mo'] = $subid['sub_mo'];
			$subop[$ii['sub_orden_id']]['cons'] = $subid['sub_consumibles'];
			$subop[$ii['sub_orden_id']]['aseg'] = $subid['sub_aseguradora'];
			$subop[$ii['sub_orden_id']]['pago_id'] = $subid['sub_descuento'];
		}
		if($ii['op_pedido'] > 0) {
			$utilped[$ii['op_pedido']] = 1;
		}
	}

/*	if($qv_activo == 1 && isset($xmlitem)) {
	// ------ Si QV está activo, genera el encabezado del XML para regresar a cotización items ---
		$veh = datosVehiculo($orden_id, $dbpfx);
		$mtime = substr(microtime(), (strlen(microtime())-3), 3);
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
		$xml .= '		<Solicitud tiempo="' . microtime() . '">10</Solicitud>'."\n";
		$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'" foto_frontal="' . $veh['foto_frontal'] .'" foto_izquierda="' . $veh['foto_izquierda'] .'" foto_derecha="' . $veh['foto_derecha'] .'" foto_vin="' . $veh['foto_vin'] .'">'."\n";
		$xml .= $xmlitem;
		$xml .= '		</OT>'."\n";
		$xml .= '	</Comprador>'."\n";
		$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.xml';
		file_put_contents("../qv-salida/".$xmlnom, $xml);
		unset($xmlitem); unset($xml);
	}
*/
//	print_r($subop);

	foreach($subop as $i => $v) {
		$preg7 = "SELECT op_id, op_item_seg, op_ok, op_estructural, op_pedido, op_pres, op_cantidad, op_costo, op_precio, op_autosurtido, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $i . "' ";
		if($sin_autorizadas == 1) {
			$preg7 .= " AND op_autosurtido >='1' AND op_autosurtido <='3'";
// ------ Se elimina la segmentación para recalcular el monto a facturar
//		} else {
//			$preg7 .= " AND op_item_seg IS NULL";
		}
//		echo $preg7 . '<br>';
		$matr7 = mysql_query($preg7) or die("ERROR: Fallo selección de orden_productos 3! " . $preg7);
		$estruc = 1; $completo = 1; $op_ref = 0; $descxremp = 0;
		while($opp = mysql_fetch_array($matr7)) {
// ------ Cálculo de descuento por remplazo de refacciones
			if($v['pago_id'] > 0) {
				$descxremp = $descxremp + ($opp['op_cantidad'] * $opp['op_costo']);
			}
// ------
			if($opp['op_ok'] == '0' && $opp['op_tangible'] == '1') {
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
// ------ Recalcula monto de refacciones facturables.
			if(is_null($opp['op_pres']) && $opp['op_tangible'] == '1') {
				$op_sub = round(($opp['op_cantidad'] * $opp['op_precio']), 2);
				if(!is_null($opp['op_item_seg'])) {
					$preg8 = "SELECT op_id, op_autosurtido FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $opp['op_item_seg'] . "'";
					$matr8 = mysql_query($preg8) or die("ERROR: Fallo selección de productos asociados." . $preg8);
					$sit = mysql_fetch_array($matr8);
					if(($autosurt[$v['aseg']] == 1 && $sit['op_autosurtido'] != '1') || $sit['op_autosurtido'] == '2' || $sit['op_autosurtido'] == '3') {
						$op_ref = $op_ref + $op_sub;
					}
				} elseif(($autosurt[$v['aseg']] == 1 && $opp['op_autosurtido'] != '1') || $opp['op_autosurtido'] == '2' || $opp['op_autosurtido'] == '3') {
					$op_ref = $op_ref + $op_sub;
				}
			}
// ---------------------------------------------------
		}
		unset($sql_data_array);
		$parametros = "sub_orden_id = '" . $i ."'";
//		echo 'Completo: ' . $completo . '<br>';
		if($completo == 1) {
			$sql_data_array = array('sub_refacciones_recibidas' => '0');
			if($v['estatus'] == '105') {
				$sql_data_array['sub_estatus'] = '106';
//				bitacora($orden_id, $lang['RR para VSRP'], $dbpfx, $lang['RR para VSRP Explica'], 2, $i);
//				if($mensjint == 1) {
//					$preg8 = "SELECT orden_asesor_id FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $orden_id . "'";
//					$matr8 = mysql_query($preg8) or die("ERROR: Fallo selección de asesor! " . $preg8);
//					$asesor = mysql_fetch_array($matr8);
//					bitacora($orden_id, $lang['RR para VSRP'], $dbpfx, $lang['RR para VSRP Explica'], 3, $sub['sub_orden_id'], '', $asesor['orden_asesor_id']);
//				}
			}
		} elseif($estruc == 1) {
			$sql_data_array = array('sub_refacciones_recibidas' => '1');
		} else {
			$sql_data_array = array('sub_refacciones_recibidas' => '2');
		}
		$nvo_pres = $op_ref + $v['cons'] + $v['mo'];
		$sql_data_array['sub_presupuesto'] = $nvo_pres;
		$sql_data_array['sub_partes'] = $op_ref;
		ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
		actualiza_orden($orden_id, $dbpfx);

// ------ Cálculo de descuento por remplazo de refacciones
		unset($sql_data_array);
//		echo 'PagoID: ' . $sub['sub_descuento'] . ' Monto descuento: ' . $descxremp; 
		if($v['pago_id'] > 0) {
			$parametros = "pago_id = '" . $v['pago_id'] ."'";
// ------ Sumar el porcentaje que determine el taller --$prcxremp-- a los costos de refacciones de remplazo
			$descxremp = round(($descxremp * (1 + ($prcxremp / 100))), 2);
			$sql_data_array['pago_monto_origen'] = $descxremp;
			ejecutar_db($dbpfx . 'destajos_pagos', $sql_data_array, 'actualizar', $parametros);
			bitacora($orden_id, 'Guardando descuento de ' . $descxremp . ' en PagoID ' . $sub['sub_descuento'], $dbpfx);
		}
		unset($sql_data_array);
// ------
	}

// ------ Actualizar utilidad de Pedidos ----------
		foreach($utilped as $uk => $uv) {
//			echo count($utilped) . ' -> Pedido: ' . $uk . '<br>';
			$actutilped = recalcUtilPed($uk, $dbpfx);
		}

// ------ Creación de Pedidos ---------------------	

	$descuento = (limpiarNumero($descuento) / 100 );
// ------ Cambiar a cotización directa en consumibles y Mano de Obra
	if(($grupo==2 || $grupo==0) && $cotizadirecto == 1) { $cotizar = 0;}
// ------------------
	if($tipo_pedido >= '1' && $tipo_pedido <= '3') {
		if($cotizar == '1' && $cotsimnoaut != '1') {
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
			if($cuantos_prov > 1) { $mensaje .= $lang['SelSoloUnProv'] . '.<br>'; $error = 'si'; }
			if($cuantos_prov < 1) { $mensaje .= $lang['SelUnProv'] . '.<br>'; $error = 'si'; }
		}
	}
	if($tipo_pedido < 1 && $reenvia_mail != 'si') { $mensaje .= $lang['SelTipoSol'] . '.<br>'; $error = 'si'; }
	if(!is_array($selecionado) && (($tipo_pedido < 4 && $cotizar != '1') || ($tipo_pedido >= '10' && $tipo_pedido <= '14'))) {
		$mensaje .= $lang['SelUnItem'] . '.<br>'; $error = 'si';
	}
	if(($tipo_pedido >= '10' && $tipo_pedido <= '14') && count($prov_selec) < 1) {
		$mensaje .= $lang['SelProvCot'] . '.<br>'; $error = 'si';
	}

	$j=0;

	if($re_pedido != '') {
		// --- Número de pedido a reenviar ---
		$error = 'no';
		$mensaje = '';
	}

	if($error == 'no') {
		if($orden_id != '') { $vehiculo = datosVehiculo($orden_id, $dbpfx);}
		$j=0;
		if($tipo_pedido < 4) {
			foreach($op_id as $i => $v) {
// ------------ Reservados --------------------
				if($reservar[$i] > 0) {
//				echo $reservar[$i] . ' ' . $i . '<br>';
					$param = "op_id = '".$op_id[$i]."'";
//				echo $param;
					$sql_data_array= array('op_reservado' => $reservar[$i], 'op_autosurtido' => $tipo_pedido);
					ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $param);
					$param = "prod_id = '".$prod_id[$i]."'";
					$sql_data_array= array('prod_cantidad_disponible' => $nvodisp[$i]);
					ejecutar_db($dbpfx . 'productos', $sql_data_array, 'actualizar', $param);
// ------------ Preparar pedidos de refacciones --------------------
// ------------   Modo Cotización ----------------------------------
				} elseif($sel_prov[$i] > 0) {
					$descuc = round(($costo[$i] * $descuento), 2);
					$costo[$i] = $costo[$i] - $descuc;
					$descup = round(($op_costo[$i][$sel_prov[$i]] * $descuento), 2);
//						echo 'Hola';
					$op_costo[$i][$sel_prov[$i]] = $op_costo[$i][$sel_prov[$i]] - $descup;
//						if($tipo_pedido == '1') $op_costo[$i][$sel_prov[$i]] = 0;
//						echo $op_costo[$i][$sel_prov[$i]] . '<br>';
					$pedprov[$sel_prov[$i]] = 0;
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
					if($grupo == 0 && $cantidad[$i] == 0) { $cantidad[$i] = 1; }
					$pedop[$op_id[$i]] = array($prov_sel[0], $fprom, $tipo_pedido, $cantidad[$i], $op_nombre[$i], $op_codigo[$i], $op_costo[$i], $precio[$i]);
				}
			}
//		print_r($pedprov);
// ------------ Crear pedidos de refacciones --------------------
			foreach($pedprov as $i => $v) {
				// --- Si está activo QV, fincar pedidos si hay Compra o Cancelar si es autosurtido ---
				unset($xmlitem);
				if($qv_activo == 1) { $pedestatini = 5; } else { $pedestatini = 5; }
				$sql_array = array('prov_id' => $i,
					'orden_id' => $orden_id,
					'pedido_tipo' => $pedido_tipo[$i],
					'pedido_estatus' => $pedestatini,
					'fecha_pedido' => date('Y-m-d H:i:s'),
					'usuario_pide' => $_SESSION['usuario']);
				$pedido = ejecutar_db($dbpfx . 'pedidos', $sql_array, 'insertar');
				$pedprov[$i] = $pedido;
				bitacora($orden_id, 'Se creo el pedido ' . $pedido, $dbpfx);
//				$fpromped = dia_habil($provs[$i]['dde']);
//				$sql_data = array('fecha_promesa' => $fpromped);
				$dcred = 0;
				foreach($pedop as $j => $w) {
					if($w[0] == $i) {
						$uop_id = $j;  // Último número de op_id para obtener aseguradora y reporte más adelante
						$param = "op_id = '" . $j . "'";
						$w[7] = limpiarNumero($w[7]);
						if($cotizar == 1) {
							$prop = "SELECT o.op_cantidad, o.op_precio, o.op_tangible, p.dias_entrega, p.dias_credito FROM " . $dbpfx . "orden_productos o, " . $dbpfx . "prod_prov p WHERE o.op_id = '$j' AND o.op_id = p.op_id AND p.prod_prov_id = '$i'";
						} else {
							$prop = "SELECT op_cantidad, op_precio, op_tangible, op_pres FROM " . $dbpfx . "orden_productos WHERE op_id = '$j'";
						}
						$maop = mysql_query($prop) or die("ERROR: Fallo selección de orden producto! " . $prop);
						$elop = mysql_fetch_array($maop);

						$sql_data = array('op_pedido' => $pedido,
							'op_autosurtido' => $w[2]);
// ------ Agregar la fecha promesa de entrega según el tipo de cotización configurada
						if($cotizar == 1) {
							$sql_data['op_fecha_promesa'] = dia_habil($elop['dias_entrega']);
						} else {
							$sql_data['op_fecha_promesa'] = dia_habil($provs[$i]['dde']);
// ------ Aplicando ajuste para que en cotización directa el costo ($w[6]) sea considerado como op_subtotal y se calcule op_costo dividiendolo entre la cantidad.
							$nvocosto = $w[6];
							$nvocant = $elop['op_cantidad'];
							if($grupo == 0 && $elop['op_pres'] == 1 && $elop['op_cantidad'] == 0) { $nvocant = 1; }
							$w[6] = round(($nvocosto / $nvocant),6);
						}
						$sql_data['op_costo'] = $w[6];
// ------ Para algunos clientes, cuando las piezas las surte la aseguradora y estos desean colocar el precio de venta como costo en los pedidos a cargo de la aseguradora, poner a uno la variable $costoeqprec
						if($costoeqprec == 1 && $pedido_tipo[$i] == '1') {
							$sql_data['op_costo'] = $elop['op_precio'];
						}
						if($w[7] > 0 && $w[7] != '') {
							$sql_data['op_precio_original'] = $elop['op_precio'];
							$sql_data['op_precio_revisado'] = '1';
							$sql_data['op_precio'] = $w[7];
							$concepto = 'Precio Revisado en OP:' . $j;
							bitacora($orden_id, $concepto, $dbpfx);
							if($costoeqprec == 1 && $pedido_tipo[$i] == '1') {
								$sql_data['op_costo'] = $w[7];
							}
						}
						if($ajustacodigo == 1) {
							$sql_data['op_codigo'] = $w[5];
						}
						if($grupo == 0 && $elop['op_pres'] == 1 && $elop['op_cantidad'] == 0) { $sql_data['op_cantidad'] = 1; }
						ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
						$recalcular = 1;
// ------ Obtiene el máximo de días de crédito de acuerdo a cotizado ---
						if($dcred < $elop['dias_credito']) { $dcred = $elop['dias_credito']; }
						if($qv_activo == 1 && $elop['op_tangible'] == '1') {
							if($w[2] > 1) {
								// --- Item se inserta a Pedido esperando confirmación de recibido del proveedor ---
								$xmlitem .= '			<Ref op_id="' . $j . '" ped_ref="' . $pedido . '" prov_id="' . $provs[$i]['qvid'] . '" prov_rfc="' . $provs[$i]['rfc'] . '" op_estatus="27" />'."\n";
							} else {
								// -- Item con cargo a Aseguradora, se debe remover de cotizaciones ---
								$xmlitem .= '			<Ref op_id="' . $j . '" op_estatus="92" />'."\n";
							}
						}
					}
				}
				$actutilped = recalcUtilPed($pedido, $dbpfx);
// ------ Inserta al pedido el número de días de crédito que se obtuvo de cotizaciones ---
				$parmped = "pedido_id = '" . $pedido . "'";
				$sqldcred = ['dias_credito' => $dcred];
				ejecutar_db($dbpfx . 'pedidos', $sqldcred, 'actualizar', $parmped);
				if($qv_activo == 1 && isset($xmlitem)) {
					// ------ Si QV está activo, genera el encabezado del XML para agregar pedidos ---
					$mtime = substr(microtime(), (strlen(microtime())-3), 3);
					$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
					$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
					$xml .= '		<Solicitud tiempo="' . microtime() . '">30</Solicitud>'."\n";
					$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $vehiculo['marca'] . '" tipo="' . $vehiculo['tipo'] . '" color="' . $vehiculo['color'] . '" vin="' . $vehiculo['serie'] . '" modelo="' . $vehiculo['modelo'] . '" foto_frontal="' . $vehiculo['foto_frontal'] .'" foto_izquierda="' . $vehiculo['foto_izquierda'] .'" foto_derecha="' . $vehiculo['foto_derecha'] .'" foto_vin="' . $vehiculo['foto_vin'] .'">'."\n";
					$xml .= $xmlitem;
					$xml .= '		</OT>'."\n";
					$xml .= '	</Comprador>'."\n";
					$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.xml';
					file_put_contents("../qv-salida/".$xmlnom, $xml);
/*					if($valor['EnvSoloQV'][0] == '1') {
						// --- Establece que el pedido espera confirmación de surtido por proveedor ---
						$parmped = "pedido_id = '" . $pedido . "'";
						$sql_array = array('pedido_estatus' => 5);
						ejecutar_db($dbpfx . 'pedidos', $sql_array, 'actualizar', $parmped);
					} */
					unset($xmlitem);
				}
			}
			$preg = "SELECT sub_orden_id FROM " . $dbpfx . "orden_productos WHERE op_id = '$uop_id'";
			$matr = mysql_query($preg) or die("ERROR: Fallo selección de productos!");
			$sub2 = mysql_fetch_array($matr);
			$pregs = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub2['sub_orden_id'] . "'";
			$matrs = mysql_query($pregs) or die("ERROR: Fallo selección de suborden!");
			$sub3 = mysql_fetch_array($matrs);
			if($tipo_pedido == '1') {
				$acargo = 'Cargo a ' . $asegnic[$sub3['sub_aseguradora']] . '.<br>Reporte: ' . $sub3['sub_reporte'];
			} else {
				$acargo = constant('TIPO_PEDIDO_'.$tipo_pedido);
			}
//			echo 'Provedor: '.$i.' Aseg: '.$aseguradora[$i].' Rep: '.$reporte[$i];
			$siniestro = $sub3['sub_reporte'];
			$para = $provs[$i]['email'];
			$enviar_prov = $provs[$i]['env'];
//			echo 'Usuario: ' . $_SESSION['usuario'];
			$asunto = 'Pedido ' . $pedido . ' de ' . $agencia . ' OT ' . $orden_id;
			if($pedpfx == 1 && file_exists('parciales/pedpfx-' . $asegnic[$sub3['sub_aseguradora']] . '.php')) {
				include('parciales/pedpfx-' . $asegnic[$sub3['sub_aseguradora']] . '.php');
			}
			$texto_t_solicitud = 'Pedido';
			$preg1 = "SELECT op_id, op_cantidad, op_nombre, op_codigo, op_doc_id, op_costo FROM " . $dbpfx . "orden_productos WHERE op_pedido = '$pedido'";
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
						$doc_id = ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
						// --- Copia el archivo al servidor de respaldo ---
						if($servrespaldo != '') {
							$conecta = ssh2_connect($servrespaldo, 2922);
							ssh2_scp_send($conecta, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, 0644);
						}
						creaMinis($nombre_archivo);
						$param = "op_id = '" . $op_id[$k] . "'";
						$sql_data = array('op_doc_id' => $doc_id);
						ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
						bitacora($orden_id, 'Imagen de refacción ' . $op_id[$k], $dbpfx);
						if($qv_activo == 1 && $grupo == '1') {
							$xmlitem .= '			<Ref op_id="' . $op_id[$k] . '" op_doc_id="' . $doc_id . '" foto_ref="' . $nombre_archivo . '" />'."\n";
						}
					} else {
						$_SESSION['msjerror'] .= 'Error, no subió el archivo ' . $_FILES['fotoref']['name'][$k] . '<br>';
					}
				}
			}
			if($xmlitem != '') {
				$veh = datosVehiculo($orden_id, $dbpfx);
				$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
				$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
				$xml .= '		<Solicitud tiempo="' . microtime() . '">10</Solicitud>'."\n";
				$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'" foto_frontal="' . $veh['foto_frontal'] .'" foto_izquierda="' . $veh['foto_izquierda'] .'" foto_derecha="' . $veh['foto_derecha'] .'" foto_vin="' . $veh['foto_vin'] .'">'."\n";
				$xml .= $xmlitem;
				$xml .= '		</OT>'."\n";
				$xml .= '	</Comprador>'."\n";
				$mtime = substr(microtime(), (strlen(microtime())-3), 3);
				$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.xml';
				file_put_contents("../qv-salida/".$xmlnom, $xml);
			}

			redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id . '&grupo=' . $grupo);
		} elseif($tipo_pedido == '10' || ($tipo_pedido >= 12 && $tipo_pedido <= 14)) {
			$para =''; $cuenta = 0; unset($xmlitem);
			foreach($op_id as $j => $w) {
				if($selecionado[$j] == '1') {
					$preg5 = "SELECT sub_orden_id, op_cantidad, op_nombre, op_codigo, op_doc_id, op_precio FROM " . $dbpfx . "orden_productos WHERE op_id = '$w'";
					$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden producto! " . $preg5);
					$procot = mysql_fetch_array($matr5);
					$cotiprod[$w] = array(
						'sub_orden_id' => $procot['sub_orden_id'],
						'op_cantidad' => $procot['op_cantidad'],
						'op_nombre' => $procot['op_nombre'],
						'op_codigo' => $procot['op_codigo'],
						'op_doc_id' => $procot['op_doc_id'],
						'op_precio' => $procot['op_precio'],
					);
// ------ Controlar el tipo de cotizacion requerida en Quien Vende ------
					$prec_comp[$w] = round(($procot['op_precio'] * ((100 - $defmarg) / 100)),2);
					if($qv_activo == 1 && $grupo == '1' && $tipo_pedido < 14) {
						// --- Se ajusta el tipo de Cotizacion deseada ---
						$xmlitem .= '			<Ref op_id="' . $w . '" op_cantidad="' . $procot['op_cantidad'] . '" op_nombre="' . $procot['op_nombre'] . '" op_codigo="' . $procot['op_codigo'] . '" op_doc_id="' . $procot['op_doc_id'] . '" op_estatus="10" s_tipo="';
						if($tipo_pedido == 13) { $xmlitem .= 'subasta'; }
						else { $xmlitem .= 'ciega'; }
						$xmlitem .= '" />'."\n";
					}
				}
			}

			foreach($prov_selec as $k) {
				$m = explode(':', $k);
				if($provs[$m[0]]['env'] == '1') {$enviar_prov = 1;}
/*				if($refpend == 1) {
					foreach($selecionado as $rp => $rpoid) {
						$op_id[$rp] = $rp;
					}
				} */

				foreach($cotiprod as $w => $x) {
					$preg1 = "SELECT op_id, sub_orden_id, cotqv FROM " . $dbpfx . "prod_prov WHERE op_id = '$w' AND prod_prov_id = '" . $m[0] . "'";
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos a proveedores! " . $preg1);
					$fila1 = mysql_num_rows($matr1);
					$qvop = 0;
					while($opqv = mysql_fetch_array($matr1)) {
						if($opqv['cotqv'] == 1) { $qvop = 1; }
					}
					if($qvop == '0') {
						$preg2 = "SELECT sub_orden_id FROM " . $dbpfx . "orden_productos WHERE op_id = '$w'";
						$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de ops! " . $preg2);
						$opprov = mysql_fetch_array($matr2);
						$sql_data = array(
							'op_id' => $w,
							'prod_prov_id' => $m[0],
							'sub_orden_id' => $opprov['sub_orden_id'],
							'dias_entrega' => $provs[$m[0]]['dde'],
							'dias_credito' => $provs[$m[0]]['dias_credito'],
							'fecha_cotizado' => date('Y-m-d H:i:s', time())
						);
						if($tipo_pedido == 14) { $sql_data['prod_costo'] = $prec_comp[$w]; } else { $sql_data['prod_costo'] =  NULL; }
					}
					if($qvop == '1') {
						//--- No modificar prod_prov ya que su origen es de QV ---
					} elseif($fila1 > 0 && $qvop == '0') {
						$param = "op_id = '$w' AND prod_prov_id = '" . $m[0] . "'";
						ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'actualizar', $param);
					} else {
						ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'insertar');
					}
// ------ Obtención del número de sub_orden_id almacenándolo como indice de elemento de array para después obtener
// ------ los números de siniestro y enviarlos como otro dato de información en la solicitud de cotización.
						$sub_id[$opprov['sub_orden_id']] = 1;

					if($cuenta > 0) { $cotiza_a .= '|'; }
					$cotiza_a .= $m[0];
					$qvids[$provs[$m[0]]['qvid']] = 1;
					$rfcs[$provs[$m[0]]['rfc']] = 1;
					$cuenta++;
				}
				if($cuenta > 0) { $cotiza_a .= '|'; }
				$cotiza_a .= $m[0];
				$cuenta++;
			}
			foreach($qvids as $kqv => $vqv) {
				if($kqv >= '1' && $qv_activo == 1) {
					$semilla = $kqv . $orden_id . $dbpfx;
					$llave = md5($semilla);
					$kqv = $kqv . '-' . $llave;
				}
				if($qvprovs != '') { $qvprovs .= '|'; }
				$qvprovs .= $kqv;
			}
			if($tipo_pedido == 14) {
				$asunto = 'Compra Relámpago de Refacciones de ' . $vehiculo['completo'] . ' para ' . $agencia . ' OT ' . $orden_id;
			} else {
				$asunto = 'Cotización de Refacciones de ' . $vehiculo['completo'] . ' para ' . $agencia . ' OT ' . $orden_id;
			}
			$pregs = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND (";
			$contsin = 1;
			$cantsin = count($sub_id);
			foreach($sub_id as $gk => $gv) {
				$pregs .= "sub_orden_id = '$gk'";
				$contsin++;
				if($contsin <= $cantsin) { $pregs .= " OR "; }
			}
			$pregs .= ") AND sub_estatus < '190' GROUP BY sub_reporte";
			$matrs = mysql_query($pregs) or die("ERROR: Fallo selección de reporte! " . $pregs);
			while ($sub3 = mysql_fetch_array($matrs)) {
				$asesin[$sub3['sub_aseguradora']][$sub3['sub_reporte']] = '1';
			}
			$contsin = 0;
			$acargo = '';
			foreach($asesin as $asenum => $repnum) {
				if($cotizataller == '1' && $contsin == 0 && $asenum > 0) { $acargo = 'A cargo de '; $contsin++; }
				if($asenum > 0 && ($cotizataller == '1' || $autosurt[$asenum] < 1)) {
					$acargo .= 'Aseguradora ' . $asegnic[$asenum] . '. Reportes: ';
					foreach($repnum as $rv) {
						$acargo .= $rv . ' ';
					}
					$acargo .= '<br>';
				} else {
					$acargo = 'Para taller.' ;
				}
			}
			$texto_t_solicitud = 'Cotizar';

			if($xmlitem != '') {
				$veh = datosVehiculo($orden_id, $dbpfx);
				$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
				$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
				$xml .= '		<Solicitud tiempo="' . microtime() . '">10</Solicitud>'."\n";
				$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'" rfc="' . $qvprovs . '" foto_frontal="' . $veh['foto_frontal'] .'" foto_izquierda="' . $veh['foto_izquierda'] .'" foto_derecha="' . $veh['foto_derecha'] .'" foto_vin="' . $veh['foto_vin'] .'">'."\n";
				$xml .= $xmlitem;
				$xml .= '		</OT>'."\n";
				$xml .= '	</Comprador>'."\n";
				$mtime = substr(microtime(), (strlen(microtime())-3), 3);
				$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.xml';
				file_put_contents("../qv-salida/".$xmlnom, $xml);
			}

		} elseif($tipo_pedido == '11') {
			$para =''; $cuenta = 0;
			foreach($prov_selec as $k) {
				$m = explode(':', $k);
				if($provs[$m[0]]['env'] == '1') {$enviar_prov = 1;}
				foreach($op_id as $j => $w) {
//					echo 'OP ID: ' . $w . ' K -> ' . $k . '<br>';
					if($selecionado[$j] == '1') {
						$preg1 = "SELECT op_id FROM " . $dbpfx . "prod_prov WHERE op_id = '$w' AND prod_prov_id = '" . $m[0] . "'";
						$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos a proveedores! " . $preg1);
						$fila1 = mysql_num_rows($matr1);
						$sql_data = array(
							'op_id' => $w, 
							'prod_prov_id' => $m[0],
							'prod_costo' => NULL,
							'dias_entrega' => NULL,
							'dias_credito' => $provs[$m[0]]['dias_credito'],
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
//---------- Ajuste de costo durante revisión de precio
				$param = "op_id = '" . $v . "'";
				$op_costo[$k] = limpiarNumero($op_costo[$k]);
				$sql_data = array();
				$prop = "SELECT op_costo, op_precio_revisado, op_codigo, op_pedido FROM " . $dbpfx . "orden_productos WHERE op_id = '$v'";
				$maop = mysql_query($prop) or die("ERROR: Fallo selección de orden producto! " . $prop);
				$elop = mysql_fetch_array($maop);
				if($elop['op_pedido'] > 0) {
					$utilped[$elop['op_pedido']] = 1;
				}
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
//----- Fin de actualización de costo durante revisión de precio
				$param = "op_id = '" . $v . "'";
				$precio[$k] = limpiarNumero($precio[$k]);
				$sql_data = array();
				$prop = "SELECT op_precio, op_precio_revisado FROM " . $dbpfx . "orden_productos WHERE op_id = '$v'";
				$maop = mysql_query($prop) or die("ERROR: Fallo selección de orden producto! " . $prop);
				$elop = mysql_fetch_array($maop);
//				echo 'op_id: ' . $v . ' precio: ' . $precio[$k] . '<br>';
//				if($precio[$k] != '' && $precio[$k] >= '0' && $precio[$k] != $elop['op_precio']) {
				if($precio[$k] != '' && $precio[$k] >= '0') {
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
// ------ Actualizar utilidad de Pedidos ----------
			foreach($utilped as $uk => $uv) {
				$actutilped = recalcUtilPed($uk, $dbpfx);
			} 
		} elseif($tipo_pedido == '6') {
			foreach($op_id as $k => $v) {
				$param = "op_id = '" . $v . "'";
				$op_costo[$k] = limpiarNumero($op_costo[$k]);
				$op_costo[$k] = round(($op_costo[$k] + 0.004999), 2);
				$sql_data = array();
				$prop = "SELECT op_costo, op_precio_revisado, op_codigo, op_pedido FROM " . $dbpfx . "orden_productos WHERE op_id = '$v'";
				$maop = mysql_query($prop) or die("ERROR: Fallo selección de orden producto! " . $prop);
				$elop = mysql_fetch_array($maop);
				if($elop['op_pedido'] > 0) {
					$utilped[$elop['op_pedido']] = 1;
				}
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
//				print_r($sql_data);
			}
// ------ Actualizar utilidad de Pedidos ----------
			foreach($utilped as $uk => $uv) {
				$actutilped = recalcUtilPed($uk, $dbpfx);
			}
		}

// -------------- Agregar números de proveedores a quienes se cotizó la refacción

		if($tipo_pedido >= '10' && $tipo_pedido <= '13') {
			foreach($cotiprod as $v => $k) {
				$preg5 = "SELECT op_cotizado_a FROM " . $dbpfx . "orden_productos WHERE op_id = '$v'";
				$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de orden producto! " . $preg5);
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

// 			Recalcular subtotal de refacciones y presupuesto
		if($recalcular == 1) {
			$preg3 = "SELECT sub_orden_id, sub_consumibles, sub_mo, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '130'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de subordenes!");
			while($sub1 = mysql_fetch_array($matr3)) {
				$preg2 = "SELECT op_id, op_cantidad, op_precio, op_precio_revisado, op_autosurtido, op_recibidos, op_estructural, op_pres, op_pedido, op_item_seg FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub1['sub_orden_id'] . "' AND op_tangible = '1'";
//				echo $preg2;
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos para recálculo!");
				$op_ref = 0; $refacciones = 0;
				while($rec = mysql_fetch_array($matr2)) {
					$op_sub = round(($rec['op_cantidad'] * $rec['op_precio']), 2);
					if($rec['op_precio_revisado'] > 0) {
						$param = "op_id = '" . $rec['op_id'] . "'";
						$sql_data = array('op_subtotal' => $op_sub);
						ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
					}
					if(is_null($rec['op_pres'])) {
						if(!is_null($rec['op_item_seg'])) {
							$preg4 = "SELECT op_id, op_autosurtido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub1['sub_orden_id'] . "' AND op_tangible = '1' AND op_id = '" . $rec['op_item_seg'] . "'";
							$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de productos asociados.");
							$sit = mysql_fetch_array($matr4);
							if(($autosurt[$sub1['sub_aseguradora']] == 1 && $sit['op_autosurtido'] != '1') || $sit['op_autosurtido'] == '2' || $sit['op_autosurtido'] == '3') {
								$op_ref = $op_ref + $op_sub;
							}
						} elseif(($autosurt[$sub1['sub_aseguradora']] == 1 && $rec['op_autosurtido'] != '1') || $rec['op_autosurtido'] == '2' || $rec['op_autosurtido'] == '3') {
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

		
// ------------------- Crear archivo ZIP con imágenes de ingreso para envío a proveedores en Cotización -----------------		
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

		if($reenvia_mail == 'si') {
			// --- Consulta información del pedido --
			$preg_pedido_re = "SELECT pedido_id, prov_id, orden_id, usuario_pide, pedido_estatus, pedido_tipo FROM " . $dbpfx . "pedidos WHERE pedido_id ='" . $re_pedido . "'";
			$matr_pedido_re = mysql_query($preg_pedido_re) or die("ERROR: Fallo selección de pedido!");
			$consulta_pedido = mysql_fetch_array($matr_pedido_re);
			// --- Consulta información usuario que levantó pedido --
			$preg_usuario_re = "SELECT email, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario ='" . $consulta_pedido['usuario_pide'] . "'";
			$matr_usuario_re = mysql_query($preg_usuario_re) or die("ERROR: Fallo selección de usuario!");
			$consulta_usuario_re = mysql_fetch_array($matr_usuario_re);	

			//--- Variables ---
			$pedprov[$consulta_pedido['prov_id']] = $re_pedido;
			$para = $consulta_prov['prov_email'];
			$tipo_pedido = $consulta_pedido['pedido_tipo'];
			$pedido = $re_pedido;
			$orden_id = $consulta_pedido['orden_id'];
		}

		if($tipo_pedido != '14') {
			// --- Crea el cuerpo comun del correo que despues se copia a los encabezados por Proveedor --
			if($tipo_pedido <= '3') {
				foreach($pedprov as $i => $v) {
					$preg1 = "SELECT op_id, op_cantidad, op_nombre, op_codigo, op_fecha_promesa, op_doc_id, op_costo FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $v . "'";
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos! " . $preg1);
					if($uop_id == '') {
						$prod = mysql_fetch_array($matr1);
						$uop_id = $prod['op_id'];
						mysql_data_seek($matr1,0);
					}

					$preg = "SELECT sub_orden_id FROM " . $dbpfx . "orden_productos WHERE op_id = '$uop_id'";
					$matr = mysql_query($preg) or die("ERROR: Fallo selección de productos!");
					$sub2 = mysql_fetch_array($matr);
					$pregs = "SELECT sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $sub2['sub_orden_id'] . "'";
					$matrs = mysql_query($pregs) or die("ERROR: Fallo selección de suborden!");
					$sub3 = mysql_fetch_array($matrs);
					if($tipo_pedido == '1') {
						$acargo = 'Cargo a ' . $asegnic[$sub3['sub_aseguradora']] . '.<br>Reporte: ' . $sub3['sub_reporte'];
					} else {
						$acargo = constant('TIPO_PEDIDO_'.$tipo_pedido);
					}
					//			echo 'Provedor: '.$i.' Aseg: '.$aseguradora[$i].' Rep: '.$reporte[$i];
					$vehiculo = datosVehiculo($orden_id, $dbpfx);
					$siniestro = $sub3['sub_reporte'];
					$para = $provs[$i]['email'];
					$concopia = (constant('EMAIL_PROVEEDOR_CC'));
					$texto_t_solicitud = 'Pedido';
					if($re_pedido != '') {
						$respondera = $consulta_usuario_re['email'];
						$texto_t_solicitud = 'Reenvío de pedido ';
					} elseif($_SESSION['email'] != '') {
						$respondera = $_SESSION['email'];
					} else {
						$respondera = (constant('EMAIL_PROVEEDOR_RESPONDER'));
					}
					$enviar_prov = $provs[$i]['env'];
//					echo 'Usuario: ' . $_SESSION['usuario'];
					$asunto = 'Pedido ' . $v . ' de ' . $agencia . ' OT ' . $orden_id;
					if($pedpfx == 1 && file_exists('parciales/pedpfx-' . $asegnic[$sub3['sub_aseguradora']] . '.php')) {
						include('parciales/pedpfx-' . $asegnic[$sub3['sub_aseguradora']] . '.php');
					}
					// --- Construir el contenido --
					$cabezacont = '<!-- BODY -->
<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h3>' . $texto_t_solicitud . ' para ' . $nombre_agencia . '</h3>
				<p class="lead">' . $lang['Estimado Proveedor'] . "\n";
					$cabezacont .= '<br>' . $provs[$i]['nombre'] . "\n";
					$cabezacont .= '<br><br>'."\n";
					$cabezacont .= EMAIL_TEXT_DESCRIPCION;
					$cabezacont .= '</p>
			</div>
		</td>
		<td></td>
	</tr>
</table>'."\n";

					$contenido .= '<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<table  bgcolor="#AECCF2">
					<tr><th>Solicitud:</th><td>' . $texto_t_solicitud . ' ' . $pedido . '</td></tr>
					<tr><th>Tipo:</th><td>' . $acargo . '</td></tr>
               <tr><th>Detalles:</th><td>OT: ' . $orden_id . '. Fecha: ' . date('Y-m-d') . '</td></tr>
					<tr><th>Detalle de Refacciones para:</th><td>' . $vehiculo['refacciones'] . '</td></tr>
				</table>
			</div>
		</td>
		<td></td>
	</tr>'."\n";
					$contenido .= '	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<table border=1 cellspacing=0 bgcolor="#AECCF2" width="100%">
					<tr>
						<th align="center">Cantidad</th>
						<th align="center">Nombre</th>
						<th align="center">Código</th>
						<th align="center">Costo</th>
						<th align="center">Promesa de<br>Entrega</th>
					</tr>'."\n";

					$sbttp = 0;
					while ($prod = mysql_fetch_array($matr1)) {
						if($cotizar == 1) {
							$preg5 = "SELECT dias_entrega FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $prod['op_id'] . "' AND prod_prov_id = '" . $i . "'";
							$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de días de entrega! " . $preg5);
							$resu5 = mysql_fetch_array($matr5);
						} else {
							$resu5['dias_entrega'] = $provs[$i]['dde'];
						}
						$prod['op_costo'] = round($prod['op_costo'],2);
						$sbttp = $sbttp + ($prod['op_cantidad'] * $prod['op_costo']);
						$contenido .= '					<tr>
						<td style="text-align: center; ">' . $prod['op_cantidad'] . '</td>
						<td>' . $prod['op_nombre'] . '</td>
						<td>' . $prod['op_codigo'] . '</td>
						<td style="text-align:right;">' . number_format($prod['op_costo'], 2) . '</td>'."\n";
						$contenido .= '						<td style="text-align: center; ">' . date('Y-m-d', strtotime(dia_habil($resu5['dias_entrega']))) . '</td>'."\n";
						$contenido .= '					</tr>'."\n";
						if($envfotoref == '1') {
							$preg4 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE doc_id = '" . $prod['op_doc_id'] . "'";
							$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección foto refacción!");
							$resu4 = mysql_fetch_array($matr4);
							$filaimg = mysql_num_rows($matr4);
							if($filaimg > 0) {
								$fotos[] = DIR_DOCS.$resu4['doc_archivo'];
							}
						}
					}
					if($envcotex == '1') {
						$preg5 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND (doc_archivo LIKE '%-i-1-%' OR doc_archivo LIKE '%-i-2-%' OR doc_archivo LIKE '%-i-3-%' OR doc_archivo LIKE '%-i-6-%')";
						$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de fotos de ingreso!");
						$filaimg = mysql_num_rows($matr5);
						if($filaimg > 0) {
							while($resu5 = mysql_fetch_array($matr5)) {
								$fotos[] = DIR_DOCS.$resu5['doc_archivo'];
							}
						}
					}

					$imp = round(($sbttp * $provs[$i]['iva']),2);
					$contenido .= '					<tr><td colspan="3" style="text-align:right;">' . $lang['Subtotal'] . '</td><td style="text-align:right;">' . number_format($sbttp,2) . '</td><td></td></tr>'."\n";
					$contenido .= '					<tr><td colspan="3" style="text-align:right;">' . $lang['IVA'] . ($provs[$i]['iva'] * 100) . '%</td><td style="text-align:right;">' . number_format($imp,2) . '</td><td></td></tr>'."\n";
					$contenido .= '					<tr><td colspan="3" style="text-align:right;">' . $lang['Total'] . '</td><td style="text-align:right;">' . number_format(($sbttp + $imp),2) . '</td><td></td></tr>'."\n";
					$contenido .= '</table>
		</div>
		</td>
		<td></td>
	</tr>'."\n";

					// --- Incluir instruciones adicionales en pedido --
					if($instruccion != '') {
						$contenido .= '	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h5>Comentarios:</h5>
				<p class="lead">' . $instruccion . '</p>
			</div>
		</td>
		<td></td>
	</tr>'."\n";
					}

					$contenido .= '</table>
<table class="body-wrap" >
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h5>Atentamente:</h5>'."\n";
					if($_SESSION['email'] != '') {
						$contenido .= '				<p>' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '<br>'."\n";
					} else {
						$contenido .= '				<p>' . JEFE_DE_ALMACEN . '<br>'."\n";
					}
					$contenido .= '				' .$agencia_razon_social. '<br>
					' .$agencia_direccion. '<br>
					Col. ' .$agencia_colonia. ' ' .$agencia_municipio. '<br>
					C.P.: ' .$agencia_cp. ' . ' .$agencia_estado. '<br>'."\n";
					if($_SESSION['email'] != '') {
						$contenido .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' . $_SESSION['email'] . '">' . $_SESSION['email'] . '</a><br>'."\n";
					} else {
						$contenido .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' .EMAIL_DE_ALMACEN. '">' .EMAIL_DE_ALMACEN. '</a><br>'."\n";
					}
					$contenido .= '				Tels: ' .$agencia_telefonos. '<br>
				' . TELEFONOS_ALMACEN . '<br>
				</p>
				<p style="font-size:9px;font-weight:bold;">Este mensaje fue
				enviado desde un sistema automático, si desea hacer algún
				comentario respecto a esta notificación o cualquier otro asunto
				respecto al Centro de Reparación por favor responda a los
				correos electrónicos o teléfonos incluidos en el cuerpo de este
				mensaje. De antemano le agradecemos su atención y preferencia.</p>
			</div>
		</td>
		<td></td>
	</tr>
</table>
<!-- /BODY -->'."\n";

					$contenido = $cabezacont . $contenido;

					include('parciales/notifica2.php');

					if($instruccion != '') {
						bitacora($orden_id, 'Instrucción adicional en el pedido ' . $pedido, $dbpfx, 'Instrucción adicional en el pedido ' . $pedido . ': ' . $instruccion, 0);
					}
				}
			} elseif($tipo_pedido >= '10' && $tipo_pedido <= '13') {
				// --- Construir el contenido --
				$cabezacont = '<!-- BODY -->
<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h3>' . $texto_t_solicitud . ' para ' . $nombre_agencia . '</h3>
				<p class="lead">' . $lang['Estimado Proveedor'] . "\n";
				$cabezacont .= '<br><br>'."\n";
				$cabezacont .= EMAIL_TEXT_COTIZACION;
				$cabezacont .= '</p>
			</div>
		</td>
		<td></td>
	</tr>
</table>'."\n";

					// --- Cotización general sin uso de Quien-Vende.com --
					$contenido = '<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<table  bgcolor="#AECCF2">
					<tr><th>Solicitud:</th><td>' . $texto_t_solicitud . '</td></tr>
					<tr><th>Tipo:</th><td>' . $acargo . '</td></tr>
               <tr><th>Detalles:</th><td>OT: ' . $orden_id . '. Fecha: ' . date('Y-m-d') . '</td></tr>
					<tr><th>Detalle de Refacciones para:</th><td>' . $vehiculo['refacciones'] . '</td></tr>
				</table>
			</div>
		</td>
		<td></td>
	</tr>'."\n";
					$contenido .= '	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<table border=1 cellspacing=0 bgcolor="#AECCF2" width="100%">
					<tr>
						<th align="center">Cantidad</th>
						<th align="center">Nombre</th>
						<th align="center">Código</th>
						<th align="center">Costo</th>
						<th align="center">Promesa de<br>Entrega</th>
					</tr>'."\n";

					foreach($cotiprod as $v => $k) {
						$contenido .= '					<tr>
						<td style="text-align: center; ">' . $k['op_cantidad'] . '</td>
						<td>' . $k['op_nombre'] . '</td>
						<td>' . $k['op_codigo'] . '</td>
						<td></td>
						<td></td>
					</tr>'."\n";
						if($envfotoref == '1') {
							$preg4 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE doc_id = '" . $procot['op_doc_id'] . "'";
							$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección foto refacción!");
							$resu4 = mysql_fetch_array($matr4);
							$filaimg = mysql_num_rows($matr4);
							if($filaimg > 0) {
								$fotos[] = DIR_DOCS.$resu4['doc_archivo'];
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
							if($filaimg > 0) {
								$fotos[] = DIR_DOCS.$resu5['doc_archivo'];
							}
						}
						$preg5 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND (doc_archivo LIKE '%-i-1-%' OR doc_archivo LIKE '%-i-2-%' OR doc_archivo LIKE '%-i-3-%' OR doc_archivo LIKE '%-i-6-%')";
						$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de fotos de ingreso!");
						$filaimg = mysql_num_rows($matr5);
						if($filaimg > 0) {
							while($resu5 = mysql_fetch_array($matr5)) {
								$fotos[] = DIR_DOCS.$resu5['doc_archivo'];
							}
						}
					}

					$contenido .= '</table>
		</div>
		</td>
		<td></td>
	</tr>'."\n";

					// --- Incluir instruciones adicionales en cotización --
					if($instruccion != '') {
						$contenido .= '	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h5>Comentarios:</h5>
				<p class="lead">' . $instruccion . '</p>
			</div>
		</td>
		<td></td>
	</tr>'."\n";
					}
					$contenido .= '</table>'."\n";

				$piecont = '<table class="body-wrap" >
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<h5>Atentamente:</h5>'."\n";
				if($_SESSION['email'] != '') {
					$piecont .= '				<p>' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '<br>'."\n";
				} else {
					$piecont .= '				<p>' . JEFE_DE_ALMACEN . '<br>'."\n";
				}
				$piecont .= '				' .$agencia_razon_social. '<br>
					' .$agencia_direccion. '<br>
					Col. ' .$agencia_colonia. ' ' .$agencia_municipio. '<br>
					C.P.: ' .$agencia_cp. ' . ' .$agencia_estado. '<br>'."\n";
				if($_SESSION['email'] != '') {
					$piecont .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' . $_SESSION['email'] . '">' . $_SESSION['email'] . '</a><br>'."\n";
				} else {
					$piecont .= '				E-mail: <a class="moz-txt-link-abbreviated" href="' .EMAIL_DE_ALMACEN. '">' .EMAIL_DE_ALMACEN. '</a><br>'."\n";
				}
				$piecont .= '				Tels: ' .$agencia_telefonos. '<br>
				' . TELEFONOS_ALMACEN . '<br>
				</p>
				<p style="font-size:9px;font-weight:bold;">Este mensaje fue
				enviado desde un sistema automático, si desea hacer algún
				comentario respecto a esta notificación o cualquier otro asunto
				respecto al Centro de Reparación por favor responda a los
				correos electrónicos o teléfonos incluidos en el cuerpo de este
				mensaje. De antemano le agradecemos su atención y preferencia.</p>
			</div>
		</td>
		<td></td>
	</tr>
</table>
<!-- /BODY -->'."\n";


				// --- Determinar Responder a --
				if($_SESSION['email'] != '') {
					$respondera = $_SESSION['email'];
				} else {
					$respondera = (constant('EMAIL_PROVEEDOR_RESPONDER'));
				}
				$concopia = (constant('EMAIL_PROVEEDOR_CC'));
				$para = '';
				foreach($prov_selec as $k) {
					$m = explode(':', $k);
					if($EnvSoloQV == '1' && $qv_activo == 1 && $provs[$m[0]]['qvid'] >= 1) {
						$semilla = $provs[$m[0]]['qvid'] . $orden_id . $dbpfx;
						$llaverej = md5($semilla);
						$qvpara = $provs[$m[0]]['email'];
						$contcotexp = '<table class="body-wrap">
	<tr>
		<td></td>
		<td class="container" bgcolor="#F2F2F2">
			<div class="content">
				<a href="https://quien-vende.com/cotizacion-express.php?accion=cotizar&azbcd=' . $llaverej . '">Abre el formulario de Cotización Express,</a> no necesitas ingresar al sistema <strong>Quien-Vende.com</strong>.
				<br>
				Gracias!<br>
				<br>
				Manual de uso de <a href="https://quien-vende.com/manuales/Manual-de-Cotizacion-Express.pdf">Cotización Express</a>
			</div>
		</td>
		<td></td>
	</tr>
</table>'."\n";

						$cuerpomail = $cabezacont . $contcotexp . $piecont;

						if(file_exists('particular/logo-base64.php')) {
							include ('particular/logo-base64.php');
						} elseif(file_exists('logo-base64.php')) {
							include ('logo-base64.php');
						}

						$email_order = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>' . $asunto . '</title>
	<style type="text/css">
* { margin:0; padding:0; }
* { font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; }
img { max-width: 100%; }
.collapse { margin:0; padding:0; }
body { -webkit-font-smoothing:antialiased; -webkit-text-size-adjust:none; width: 100%!important; height: 100%; }
a { color: #2BA6CB;}
table.head-wrap { width: 100%;}
.header.container table td.logo { padding: 15px; }
.header.container table td.label { padding: 15px; padding-left:0px;}
table.body-wrap { width: 100%;}
table.footer-wrap { width: 100%;	clear:both!important;}
.footer-wrap .container td.content p { border-top: 1px solid rgb(215,215,215); padding-top:15px;}
.footer-wrap .container td.content p { font-size:10px; font-weight: bold; }
h1,h2,h3,h4,h5,h6 { font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; line-height: 1.1; margin-bottom:15px; color:#000; }
h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }
h1 { font-weight:200; font-size: 44px;}
h2 { font-weight:200; font-size: 37px;}
h3 { font-weight:500; font-size: 27px;}
h4 { font-weight:500; font-size: 23px;}
h5 { font-weight:900; font-size: 17px;}
h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}
.collapse { margin:0!important; color: #ffffff;}
p, ul { 
	margin-bottom: 10px; 
	font-weight: normal; 
	font-size:14px; 
	line-height:1.6;
	text-align: justify;
}
p.lead { font-size:17px; }
p.last { margin-bottom:0px;}
ul li {
	margin-left:5px;
	list-style-position: inside;
}
/* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
.container {
	display:block!important;
	max-width:600px!important;
	margin:0 auto!important; /* makes it centered */
	clear:both!important;
}

.contenedor80 {
	display:block!important;
	max-width:80%!important;
	margin:0 auto!important; /* makes it centered */
	clear:both!important;
}

/* This should also be a block element, so that it will fill 100% of the .container */
.content {
	padding:15px;
	max-width:600px;
	margin:0 auto;
	display:block; 
}
.content table { width: 100%; }
/* Odds and ends */
.column {
	width: 300px;
	float:left;
}
.column tr td { padding: 15px; }
.column-wrap { 
	padding:0!important; 
	margin:0 auto; 
	max-width:600px!important;
}
.column table { width:100%;}
/* Be sure to place a .clear element after each set of columns, just to be safe */
.clear { display: block; clear: both; }
/* ------------------------------------------- 
		PHONE
		For clients that support media queries.
		Nothing fancy. 
-------------------------------------------- */
@media only screen and (max-width: 600px) {
	a[class="btn"] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}
	div[class="column"] { width: auto!important; float:none!important;}
}
	</style>
	</head>
	<body bgcolor="#FFFFFF">
	<!-- HEADER -->
	<table class="head-wrap" bgcolor="#395259">
		<tr>
			<td></td>
				<td class="header container">
					<div class="content">
						<table bgcolor="#395259">
							<tr>
								<td><img src="' . $logobase64 . '"/></td>
								<td align="right"><h4 class="collapse">' . $agencia .'</h4></td>
							</tr>
						</table>
					</div>
				</td>
			<td></td>
		</tr>
	</table>
	<!-- /HEADER -->
		' . $cuerpomail . '
	<!-- FOOTER -->
<table class="footer-wrap">
	<tr>
		<td></td>
		<td class="container">
				<!-- content -->
				<div class="content">
				<table>
				<tr>
					<td align="center">
						<p>
							<a>Producido por:</a> |
							<a>AutoShop-Easy.com</a>
						</p>
					</td>
				</tr>
			</table>
				</div><!-- /content -->
		</td>
		<td></td>
	</tr>
</table>
<!-- /FOOTER -->
	</body>
</html>';


						require_once ('parciales/PHPMailerAutoload.php');
						$mail = new PHPMailer;

						$mail->CharSet = 'UTF-8';
						$mail->isSMTP();                                      // Set mailer to use SMTP
						$mail->Host = $smtphost;  // Specify main and backup SMTP servers
						$mail->SMTPAuth = true;                               // Enable SMTP authentication
						$mail->Username = $smtpusuario;                 // SMTP username
						$mail->Password = $smtpclave;                           // SMTP password
//			$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
						$mail->Port       = $smtppuerto; 

						$mail->From = $smtpusuario;
						$mail->FromName = $nombre_agencia;

						$pa = explode(',', $qvpara);
						foreach($pa as $k) {
							$mail->addAddress($k);     // Add a recipient
						}

						if($respondera != '') {
							$ma = explode(',', $respondera);
							foreach($ma as $k) {
								$mail->addReplyTo($k);
							}
						} else {
							$mail->addReplyTo($agencia_email);
						}

						if($concopia != '') {
							$ma = explode(',', $concopia);
							foreach($ma as $k) {
								$mail->addCC($k);
							}
						} else {
							$mail->addCC($agencia_email);
						}

						if($vaicop_bcc) { $mail->addBCC($vaicop_bcc); }
						if($bcc) {
							$ma = explode(',', $bcc);
							foreach($ma as $k) {
								$mail->addBCC($k);     // Add a recipient
							}
						}

						if($_SESSION['email'] != '') { $mail->addCC($_SESSION['email']); }

						$mail->addBCC('monitoreo@controldeservicio.com');
						$mail->isHTML(true);                                  // Set email format to HTML

						$mail->Subject = $asunto;

						foreach($fotos as $pic) {
							$mail->addAttachment($pic);
						}

						$mail->Body    = $email_order;
			//			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

						if($mailcodificacion != '') {
							$mail->Encoding = $mailcodificacion;
						}

						if(!$mail->send()) {
							$mensaje .= 'Errores en notificación automática: ' . $qvpara . '<br>';
							$mensaje .=  $mail->ErrorInfo;
							$msjerror = 1;
						} else {
							$mensaje .= 'Se envió el correo a ' . $qvpara . '<br>';
							$msjerror = 0;
						}
						$_SESSION['msjerror'] = $mensaje;
						unset($email_aviso);
						unset($fotos);
					} else {
						if($para != '') {
							$para .= ', ';
						}
						$para .= $provs[$m[0]]['email'];
					}
				}
				if($para != '') {
					// --- Determina que tipo de envío se va a realizar --
					if($notiprovvis != 1) { // Notificaciones individuales en modo Visible
						$bcc = $para;
						$para = (constant('EMAIL_PROVEEDOR_RESPONDER'));
					}
					$contenido = $cabezacont . $contenido . $piecont;
					include('parciales/notifica2.php');
					if($instruccion != '') {
						bitacora($orden_id, 'Instrucción adicional en requerimiento de cotización', $dbpfx, 'Instrucción adicional en requerimiento de cotización ' . $instruccion, 0);
					}
				}
			}
			if($tipo_pedido < 4) {
				redirigir('pedidos.php?accion=consultar&pedido=' . $pedido);
			} elseif($refpend == 1) {
				redirigir('refacciones.php?accion=pendientes');
			}
			redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id . '&grupo=' . $grupo);

		} elseif($tipo_pedido == 14 && validaAcceso('1115100', $dbpfx) == '1') {
			// ------ Cotización tipo Flash para QV --
			include('parciales/encabezado.php');
			echo '		<div id="body">'."\n";
			include('parciales/menu_inicio.php');
			echo '			<div id="principal">'."\n";
			echo '				<div class="row"> <!-box header del título. -->
					<div class="col-sm-12">
						<div class="content-box-header">
							<div class="panel-title">
								<h2>' . $lang['DetCoTFlash'] . '</h2> 
							</div>
						</div>
					</div>
				</div>'."\n";
			echo '				<form action="refacciones.php?accion=flash" method="post" enctype="multipart/form-data">'."\n";
			echo '				<div class="row">
					<div class="col-sm-3">' . $lang['NomRef'] . '</div>
					<div class="col-sm-1">' . $lang['PrecioFlash'] . '</div>
					<div class="col-sm-2">' . $lang['Origen'] . '</div>
					<div class="col-sm-2">' . $lang['Condición'] . '</div>
					<div class="col-sm-3">' . $lang['MensajeComprador'] . '</div>
				</div>'."\n";
			foreach($cotiprod as $w => $j) {
				echo '				<div class="row">
					<div class="col-sm-3">' . $j['op_nombre'] . '
						<input type="hidden" name="nombre[' . $w . ']" value="' . $j['op_nombre'] . '" />
						<input type="hidden" name="cantidad[' . $w . ']" value="' . $j['op_cantidad'] . '" />
						<input type="hidden" name="codigo[' . $w . ']" value="' . $j['op_codigo'] . '" />
						<input type="hidden" name="doc_id[' . $w . ']" value="' . $j['op_doc_id'] . '" />
					</div>
					<div class="col-sm-1"><input style="text-align:right;" type="text" name="precio[' . $w . ']" value="' . number_format($prec_comp[$w],2) . '" required size="6"></div>
					<div class="col-sm-2"><select name="origen[' . $w . ']" required>
						<option value="Original">' . $lang['Original'] . '</option>
						<option value="Independiente">' . $lang['OriInde'] . '</option>
						<option value="Taiwan">' . $lang['OriTw'] . '</option>
						<option value="Indistinto">' . $lang['OriIndis'] . '</option>
						</select>
					</div>
					<div class="col-sm-2"><select name="condicion[' . $w . ']" required>
						<option value="Nuevo">' . $lang['Nuevo'] . '</option>
						<option value="Usado">' . $lang['Usado'] . '</option>
						<option value="Reconstruido">' . $lang['Reconstruido'] . '</option>
						<option value="Indistinto">' . $lang['Indist'] . '</option></select>
					</div>
					<div class="col-sm-3"><input type="text" name="mnsj[' . $w . ']" size="32" maxlegth="256"></div>
				</div>'."\n";
			}
			echo '				<div class="row">
					<div class="col-sm-1">
						<input type="submit" value="' . $lang['Enviar'] . '" />
						<input type="hidden" name="qvprovs" value="' . $qvprovs . '" />
						<input type="hidden" name="orden_id" value="' . $orden_id . '" />
						<input type="hidden" name="grupo" value="' . $grupo . '" />
						<input type="hidden" name="pedido" value="' . $pedido . '" />
					</div>
				</div>'."\n";
			echo '				</form>'."\n";
		} elseif($tipo_pedido == 14 && validaAcceso('1115100', $dbpfx) != 1) {
			$mensaje .= $lang['SinPermFlash'] . '<br>';
			$_SESSION['ref']['mensaje'] = $mensaje;
			redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id . '&grupo=' . $grupo);
		}
		if($tipo_pedido < 4) {
			redirigir('pedidos.php?accion=consultar&pedido=' . $pedido);
		} else {
			redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id . '&grupo=' . $grupo);
		}
	} else {
		$_SESSION['ref']['mensaje'] = $mensaje;
		redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id . '&grupo=' . $grupo);
	}
}

elseif($accion==="ligar"){
	
	//echo 'Sección para ligar foto, op_id ' . $op_id . ', orden_id ' . $orden_id . ', nombre ' . $nombre . ', doc_id ' . $doc_id . '<br>';
	
	if($confirma == 'si'){
		
		if($doc_id == ''){
			$_SESSION['msjerror'] = 'Debes de seleccionar una imagen para asociarla';
			redirigir('refacciones.php?accion=ligar&orden_id=' . $orden_id . '&op_id=' . $op_id);
		}
		
		// --- Verificar si el documento ya está asociado a otra refacción ---
		$preg_asociado = "SELECT op_id FROM " . $dbpfx . "orden_productos WHERE op_doc_id = '" . $doc_id . "'";
		$matr_asociado = mysql_query($preg_asociado) or die ("ERROR: Fallo seleccion op asociado a doc.! " . $preg_asociado);
		$asociado = mysql_num_rows($matr_asociado);
							
		if($asociado >= 1){
			// --- Desasociar ---
			$info_asociado = mysql_fetch_array($matr_asociado);
			// --- Actualizar op_id ---
			$sql_data_array = [
				'op_doc_id' => 'null',
			];
			$parametros = 'op_id = ' . $info_asociado['op_id'];
			ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $parametros);
		}
		
		//echo 'Confirma<br>';
		// --- Consultar el documento ---
		$preg_documento = "SELECT * FROM " . $dbpfx . "documentos WHERE doc_id = '" . $doc_id . "'";
		$matr_documento = mysql_query($preg_documento) or die("ERROR! " . $preg_documento);
	   	$doc = mysql_fetch_array($matr_documento);
		
		$tipo_archivo = pathinfo($doc['doc_archivo']);
		
		// ---- Consultar el op_id ----
		$preg_op = "SELECT * FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op_id . "'";
		$matr_op = mysql_query($preg_op) or die("ERROR! " . $preg_op);
	   	$op = mysql_fetch_array($matr_op);
	
		//echo 'Nombre original ' . $op['op_nombre'] . '<br>';
		$nombre_antiguo = $doc['doc_nombre'];

		// --- limpieza del nombre de la refacción ---
		$texto_sin_numeros = preg_replace('/[0-9]+/', '', $op['op_nombre']);
		$texto_sin_simbolos = preg_replace('', '', $texto_sin_numeros);
		$texto_sin_simbolos = str_replace(array("\\", "¨", "º", "-", "~", "#", "@", "|", "!", "\"", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", "¿", "[", "^", "]", "+", "}", "{", "¨", "´", ">", "< ", ";", ",", ":", "."), '', $texto_sin_numeros );
		$texto_sin_espacios = str_replace(array(" "), '-', $texto_sin_simbolos );
		
		// ---- Nuevo nombre del documento ---
		$nuevo_nombre = $orden_id . '-p-' . $texto_sin_espacios . '-' . time() . '.' . $tipo_archivo['extension'];
		
		// --- Actualizar op_id ---
		$sql_data_array = [
			'op_doc_id' => $doc['doc_id'],
		];
		$parametros = 'op_id = ' . $op_id;
		ejecutar_db($dbpfx . 'orden_productos', $sql_data_array, 'actualizar', $parametros);
		
		// --- Renombar el documentos en carpetas (documentos, minis) ---
		rename("documentos/" . $doc['doc_archivo'], "documentos/" . $nuevo_nombre) or die ("No se pudo renombrar");
		rename("documentos/minis/" . $doc['doc_archivo'], "documentos/minis/" . $nuevo_nombre) or die ("No se pudo renombrar");
		
		// --- Actualizar documento ---
		// --- Actualizar op_id ---
		$sql_data_array = [
			'doc_archivo' => $nuevo_nombre,
			'doc_nombre' => $texto_sin_simbolos,
		];
		$parametros = 'doc_id = ' . $doc['doc_id'];
		ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'actualizar', $parametros);
		$bitacora = 'El usuario ' . $_SESSION['usuario'] . ' renombró la imagen de ' . $nombre_antiguo . ' a ' . $texto_sin_simbolos;
		bitacora($orden_id, $bitacora, $dbpfx);

                // --- Actualiza el nombre del archivo en QV
		if($qv_activo == 1) {
			$veh = datosVehiculo($orden_id, $dbpfx);
			$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
			$xml .= '		<Solicitud tiempo="' . microtime() . '">10</Solicitud>'."\n";
			$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'" foto_frontal="' . $veh['foto_frontal'] .'" foto_izquierda="' . $veh['foto_izquierda'] .'" foto_derecha="' . $veh['foto_derecha'] .'" foto_vin="' . $veh['foto_vin'] .'">'."\n";
			$xml .= '                   <Ref op_id="' . $op_id . '" op_doc_id="' . $doc['doc_id'] . '" foto_ref="' . $nuevo_nombre . '" />'."\n";
			$xml .= '		</OT>'."\n";
			$xml .= '	</Comprador>'."\n";
			$mtime = substr(microtime(), (strlen(microtime())-3), 3);
			$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.xml';
			file_put_contents("../qv-salida/".$xmlnom, $xml);
		}

		$_SESSION['msjerror'] = 'Se actualizó la foto de la refacción ' . $texto_sin_simbolos . ', item ' . $op['op_item'];
		redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id . '&grupo=1');
		
	} else{
		lista_fotos($orden_id, $op_id, $dbpfx, $nombre);	
	}
	
}


elseif($accion==='listar') {
	
	if (validaAcceso('1115030', $dbpfx) == 1) {
		// Acceso autotizado
	} elseif($solovalacc != 1 && ($_SESSION['rol08']=='1')) {
		// Acceso autotizado
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}

	if(isset($paquete) && $paquete > 0) { $_SESSION['ref']['paquete'] = $paquete;}
	$paquete = $_SESSION['ref']['paquete'];
	if($limpiar == 'Restablecer Filtros') { $codigo=''; $nombre=''; $almacen=''; }
	
	if($pedpend == 1) { $_SESSION['ref']['pedpend'] = 1; }
	if($cotpend == 1) { $_SESSION['ref']['cotpend'] = 1; }
	if($cot_contestadas == 1) { $_SESSION['ref']['cot_contestadas'] = 1; }
	if($pedpend == 2){ 
		unset($_SESSION['ref']['pedpend']); 
		unset($_SESSION['ref']['cotpend']);
		unset($_SESSION['ref']['cot_contestadas']);
	}
	
	echo '
		<div class="page-content">

			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header">
						<div class="panel-title">
		  					<h2>Refacciones, Consumibles y Mano de Obra por Almacén</h2>
						</div>
			  		</div>
				</div>
			</div>
			
			<div class="row"> <!- mensajes -->
				<div class="col-md-12">
	  				<span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span>
				</div>
			</div>'."\n";
	
	unset($_SESSION['ref']['mensaje']);
	
	echo '		
		
			<div class="row">
				<div class="col-md-8 shadow-box">
					<div class="form-group">
      					<div class="col-md-12">
							<form action="refacciones.php?accion=listar" method="post" enctype="multipart/form-data">
							<table>
								<tr>
									<td align="right" class="obscuro"><big><b>Buscar por codigo:</b></big></td>
									<td>
										<input class="form-control" type="text" name="codigo" id="codigo" size="15" value="' . $codigo . '">
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Buscar por nombre:</b></big></td>
									<td>
										<input class="form-control" type="text" name="nombre" size="15" value="' . $nombre . '">
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Filtrar por Almacén:</b></big></td>
									<td>
										<select class="form-control" name="area" size="1">
												<option value="">Seleccionar...</option>'."\n";
											foreach($nom_almacen as $k => $v) {
												echo '				
												<option value="' . $k . '"';
												if($almacen == $k) { echo ' selected="selected" ';}
												echo '>' . $v . '</option>'."\n";
											}
	
	echo '			
										</select>
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
									<td>
										<input class="btn btn-danger" type="submit" name="limpiar" value="Restablecer Filtros">
									</td>
									<td>
									</td>
									<td>
										<a href="refacciones.php?accion=item&nuevo=1">
											<img src="idiomas/' . $idioma . '/imagenes/agregar.png" alt="Nuevo Producto" title="Nuevo Producto" width="45" height="45">
										</a>
									</td>
									<td>
										<a href="refacciones.php?accion=cotpedprod">
											<img src="idiomas/' . $idioma . '/imagenes/carro-de-compra.png" alt="Pedidos y Cotizaciones" title="Pedidos y Cotizaciones" width="45" height="45">
										</a>
									</td>'."\n";
	
	if($_SESSION['ref']['pedpend'] == 1 || $_SESSION['ref']['cotpend'] == 1 || $_SESSION['ref']['cot_contestadas'] == 1) {
		echo '
									<td>
										<a href="refacciones.php?accion=listar&pedpend=2"><img src="idiomas/' . $idioma . '/imagenes/flag-black.png" alt="Todo" title="Todo" width="45" height="45"></a>
									</td>'."\n";
	} else {
		echo '
									<td>
										<a href="refacciones.php?accion=listar&pedpend=1">
											<img src="idiomas/' . $idioma . '/imagenes/flag-yellow.png" alt="Sólo Items Pendientes por Recibir" title="Sólo Items Pendientes por Recibir" width="45" height="45">
										</a>
									</td>
									<td>
										<a href="refacciones.php?accion=listar&cot_contestadas=1">
											<img src="idiomas/' . $idioma . '/imagenes/cotizaciones_contestadas.png" alt="Sólo Items con cotizaciones contestadas" title="Sólo Items con cotizaciones contestadas" width="45" height="45">
										</a>
									</td>
									<td>
										<a href="refacciones.php?accion=listar&cotpend=1"><img src="idiomas/' . $idioma . '/imagenes/cotizacion-por-recibir.png" alt="Sólo Cotizaciones Pendientes por Recibir" title="Sólo Cotizaciones Pendientes por Recibir" width="45" height="45"></a>
									</td>'."\n";
	}
	echo '
									<td>
										<a href="refacciones.php?accion=generacb"><img src="idiomas/' . $idioma . '/imagenes/barcode_scanner.png" alt="Generar Etiquetas de Código de Barras" title="Generar Etiquetas de Código de Barras" width="45" height="45"></a>
									</td>
								</tr>	
							</table>
							</form>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12">'."\n";

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
		if(isset($codigo) && $codigo!='') { $preg .= "AND prod_codigo LIKE '%" . $codigo . "%' "; }
	}
	if($_SESSION['ref']['pedpend'] == 1) { $preg .= "AND prod_cantidad_pedida > '0' "; }
	
	$preg0 = "SELECT prod_id FROM " . $dbpfx . "productos WHERE prod_activo='1' ";
	$preg0 = $preg0 . $preg;
	//echo $preg0 . '<br>';
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos! " . $preg0);
	$filas = mysql_num_rows($matr0);
	if($filas == 0 && $codigo!='') {
   		echo '
						<div>
							No se encontró ningún producto con el código ' . $codigo . ',<br>¿Desea agregarlo como nuevo a la lista de productos? <a href="refacciones.php?accion=item&nuevo=1&codigo=' . $codigo . '">Agregar</a>&nbsp;
						</div>'."\n";
   	} else {

	   $renglones = 50;
	   $paginas = (round(($filas / $renglones) + 0.49999999) - 1);
   		if(!isset($pagina)) { $pagina = 0;}
	   $inicial = $pagina * $renglones;
		//echo $paginas;
		if($_SESSION['ref']['cotpend'] == 1) {
			$preg1 = "SELECT p.prod_id, p.prod_marca, p.prod_codigo, p.prod_nombre, p.prod_cantidad_pedida, p.prod_cantidad_existente, p.prod_cantidad_disponible, p.prod_precio, p.prod_almacen, p.prod_tangible FROM " . $dbpfx . "productos p, " . $dbpfx . "prod_prov pp WHERE p.prod_activo = '1' AND pp.prod_id = p.prod_id AND pp.prod_costo = '0'";
			//$preg1 = $preg1 . $preg;
			$preg1 .= " GROUP BY p.prod_id ORDER BY p.prod_almacen, p.prod_id LIMIT " . $inicial . ", " . $renglones;
		} elseif($_SESSION['ref']['cot_contestadas'] == 1){
			$preg1 = "SELECT p.prod_id, p.prod_marca, p.prod_codigo, p.prod_nombre, p.prod_cantidad_pedida, p.prod_cantidad_existente, p.prod_cantidad_disponible, p.prod_precio, p.prod_almacen, p.prod_tangible FROM " . $dbpfx . "productos p, " . $dbpfx . "prod_prov pp WHERE p.prod_activo = '1' AND pp.prod_id = p.prod_id AND pp.prod_costo > 0";
			//$preg1 = $preg1 . $preg;
			$preg1 .= " GROUP BY p.prod_id ORDER BY p.prod_almacen, p.prod_id LIMIT " . $inicial . ", " . $renglones;
		} else {
			$preg1 = "SELECT prod_id, prod_marca, prod_codigo, prod_nombre, prod_cantidad_pedida, prod_cantidad_existente, prod_cantidad_disponible, prod_precio, prod_almacen, prod_tangible FROM " . $dbpfx . "productos WHERE prod_activo = '1' ";
			$preg1 = $preg1 . $preg;
			$preg1 .= " GROUP BY prod_id ORDER BY prod_almacen, prod_id LIMIT " . $inicial . ", " . $renglones;
		}
		//echo $preg1;
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos! " . $preg1);

		echo '		
						<div align="right">
							<br><br><br>
							<a href="refacciones.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="refacciones.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>
						</div>'."\n";
		
		
		echo '			
						<div id="content-tabla">
							<table cellspacing="0" class="table-new">
								<tr>
									<th><big>Almacén</th></big>
									<th><big>Nombre</th></big>
									<th><big>Marca</th></big>
									<th><big>Código</th></big>
									<th><big>Existencia</th></big>
									<th><big>Disponible</th></big>
									<th><big>Adeudos</th></big>
									<th><big>Precio Unitario<br>de Venta</th></big>
									<th><big>Foto</th></big>
									<th><big>Acciones</th></big>
								</tr>'."\n";
		$cue = 0;
		$clase = 'claro';
		while($prods = mysql_fetch_array($matr1)){
			//print_r($prods);
			$preg3 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE prod_id = '" . $prods['prod_id'] . "'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de productos!");
			$foto = mysql_fetch_array($matr3);
			if ($foto['doc_archivo'] != '') {
				$etiqueta= '<a href="' . DIR_DOCS  .$foto['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' .$foto['doc_archivo'] . '"</a>';
			} else {
				$etiqueta= '<img src="' . DIR_DOCS . 'documento.png" alt="Sin imagen" title="Sin imagen">';
			}
			
			// --- Consultar cotizaciones contestadas ---
			$preg_cots_con = "SELECT pp_id FROM " . $dbpfx . "prod_prov  WHERE prod_id='" . $prods['prod_id'] . "' AND prod_costo > 0";
			$matr_cots_con = mysql_query($preg_cots_con) or die("ERROR: Fallo selección de cotizaciones contestadas! " . $preg_cots_con);
  			$cotiz_contestadas = mysql_num_rows($matr_cots_con);

			// --- Consultar adeudos ---
			$preg_adeudos = "SELECT * FROM " . $dbpfx . "prods_pendientes WHERE prod_id = '" .  $prods['prod_id'] . "' AND prods_pendiente_adeudos > 0";
			$matr_adeudos = mysql_query($preg_adeudos) or die("ERROR: Fallo selección de adeudos! " . $preg_adeudos);
			$adeudos = mysql_num_rows($matr_adeudos);
		
			$total_adeudos = 0;
			if($adeudos > 0){ // --- Sumar adeudos ---
				while($consulta_adeudos = mysql_fetch_array($matr_adeudos)){
					$total_adeudos = $total_adeudos + $consulta_adeudos['prods_pendiente_adeudos'];
				}	
			}
			
			echo '					
								<tr class="' . $clase . '">
									<td>' . $nom_almacen[$prods['prod_almacen']] . '</td>
									<td>' . $prods['prod_nombre'] . '</td>
									<td>' . $prods['prod_marca'] . '</td>
									<td>' . $prods['prod_codigo'] . '</td>
									<td style="text-align:right;">' . $prods['prod_cantidad_existente'] . '</td>
									<td style="text-align:right;">' . $prods['prod_cantidad_disponible'] . '</td>
									<td style="text-align:right;">' . $total_adeudos . '</td>
									<td style="text-align:right;">$' . number_format($prods['prod_precio'],2) . '</td>
									<td>' . $etiqueta . '</td>
									<td>'."\n";
			
			if(isset($paquete) && $paquete > 0) {
				echo '
										<a href="refacciones.php?accion=inspcpaq&paquete=' . $paquete . '&prod_id=' . $prods['prod_id'] . '">
											<img src="idiomas/' . $idioma . '/imagenes/agregar.png" alt="Agregar" title="Agregar a paquete" width="25" height="25">
										</a>'."\n";
			} else {
				echo '
										<a href="refacciones.php?accion=item&prod_id=' . $prods['prod_id'] . '">
											<img src="idiomas/' . $idioma . '/imagenes/prod-editar.png" alt="Detalles" title="Detalles" width="25" height="25">
										</a> / 
										<a href="refacciones.php?accion=cotpedprod&prod_id=' . $prods['prod_id'] . '">
											<img src="idiomas/' . $idioma . '/imagenes/carro-de-compra.png" width="25" height="25" alt="Agregar a Pedido o Cotización" title="Agregar a Pedido o Cotización">
										</a>'."\n";

				if($prods['prod_cantidad_pedida'] > 0 && $prods['prod_tangible'] > 0) { 
					echo '
										<img src="idiomas/' . $idioma . '/imagenes/flag-yellow.png" width="25" height="25" alt="Pendiente por Recibir" title="Pendiente por Recibir">'."\n";
				}

				$preg2 = "SELECT prod_id FROM " . $dbpfx . "prod_prov WHERE prod_id = '" . $prods['prod_id'] . "' AND prod_costo = '0' ";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cotizaciones!");
				$fila2 = mysql_num_rows($matr2);
				if($fila2 > 0) { 
					echo '
										<img src="idiomas/' . $idioma . '/imagenes/cotizacion-por-recibir.png" alt="Cotización por Recibir" width="25" height="25" title="Cotización por Recibir">';
				}
				
				if($cotiz_contestadas > 0){
					echo '
										<img src="idiomas/' . $idioma . '/imagenes/cotizaciones_contestadas.png" alt="Cotización contestada" width="25" height="25" title="Cotización contestada">';
				}
			}
			
			echo '
									</td>
								</tr>'."\n";
			$cue++;
			if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
		}
		
		echo '				
							</table>
						</div>
						<div align="right">
							<a href="refacciones.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="refacciones.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="refacciones.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>
						</div>'."\n";
	}
	
	echo '
					</div>
				</div>
			</div>
		</div>'."\n";
}

elseif($accion==='item') {
	
	if (validaAcceso('1115035', $dbpfx) == 1) {	
		// Acceso autotizado
	} elseif($solovalacc != 1 && ($_SESSION['rol08']=='1')) {
		// Acceso autotizado
	} else {
		redirigir('index.php?mensaje=Acceso no Autorizado');
	}
	
	if($prod_id!='') {
		$preg0 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id='" . $prod_id . "'";
		//echo $preg0 . '<br>';
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
	   $prod = mysql_fetch_array($matr0);
	}
	
	echo '
		<div class="page-content">

			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header">
						<div class="panel-title">
		  					<h2>Detalle de Producto</h2>
						</div>
			  		</div>
				</div>
			</div>
			<br>'."\n";
	
	// --- Sección de foto del producto ---
	$preg3 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE prod_id = '" . $prod_id . "'";
	$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de productos!");
	$foto = mysql_fetch_array($matr3);
	if ($foto['doc_archivo'] != '') {
		$etiqueta = '
					<div align="center">
						<a href="' . DIR_DOCS  .$foto['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'medianas/' . $foto['doc_archivo'] . '" alt=""></a>
						<input type="hidden" name="hacer" value="actualizar"/>
					</div>'."\n";
	} else {
		$etiqueta = '
					<div align="center">
						<img src="' . DIR_DOCS . 'documento.png" alt="Sin imagen" title="Sin imagen">
						<input type="hidden" name="hacer" value="insertar"/>
					</div>'."\n";
	}
	
	// --- Sección de ultimos movimientos ---
	$preg4 = "SELECT * FROM " . $dbpfx . "prod_bitacora WHERE prod_id='" . $prod_id . "' ORDER BY bit_id DESC LIMIT 5";
	//echo $preg4 . '<br>';
  	$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de bitácoras!".$preg4);
  	$fila4 = mysql_num_rows($matr4);
	$preg5 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE activo = '1'";
	$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de Usuario!" . $preg5);
	while($usu = mysql_fetch_array($matr5)) {
		$usr[$usu['usuario']] = $usu['nombre'] . ' ' . $usu['apellido'];
	}
	
	echo '
			<form action="refacciones.php?accion=';
	
	if($nuevo=='1') { echo 'insertar';} else { echo 'actualizar';}

	echo '" method="post" enctype="multipart/form-data">';
	
	echo '	
			<br>
			<div class="row">
			
				<div class="col-md-4 shadow-box">
					<h2 align="center">FOTO</h2>
					' . $etiqueta . '<br>
					<div align="center">
						<small>
							<input type="file" name="imagen" size="30"/>
						</small>
					</div>
				</div>
				
				<div class="col-md-1">
				</div>
				
				<div class="col-md-5">'."\n";
					
					if($fila4 > 0){
				   		echo '			
						<div id="content-tabla">
							<table cellspacing="0" class="table-new">
								<tr>
									<th>FECHA</th>
									<th>EVENTO <br><small>Histórico de 5 últimos movimientos</small></th>
									<th>MOTIVO</th>
									<th>USUARIO</th>
								</tr>'."\n";

						$clase = 'claro';
				   		while($hist = mysql_fetch_array($matr4)) {

							echo '				
								<tr class="' . $clase . '">
									<td>' . $hist['fecha_evento'] . '</td>
									<td>' . $hist['evento'] . '</td>
									<td>' . $hist['motivo'] . '</td>
									<td>' . $usr[$hist['usuario']] . '</td>
									
								</tr>'."\n";
							if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
							
						}
						echo '			
							</table>
						</div>'."\n";
					}
	echo '
					
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-5">
					<span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span>
				</div>
			</div>
			<br>
			<div class="row">'."\n";
			
	unset($_SESSION['ref']['mensaje']);
	
	echo '
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Nombre:</b></big>
									</td>
									<td>
										<input class="form-control" type="text" name="nombre" size="20" maxlength="255" value="';
										echo ($_SESSION['ref']['nombre']) ? $_SESSION['ref']['nombre']:$prod['prod_nombre']; echo '" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<br>
										<big><b>Marca:</b></big>
									</td>
									<td>
										<br>
										<input class="form-control" type="text" name="marca" size="20" maxlength="32" value="'; 
										echo ($_SESSION['ref']['marca']) ? $_SESSION['ref']['marca']:$prod['prod_marca']; echo '" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<br><br><big><b>Código:</b></big>
									</td>
									<td>
										<br>
										Código de Barras del producto.
										<input class="form-control" type="text" name="codigo" size="20" maxlength="20" value="';
										if($nuevo=='1' && $codigo!='') {
											echo $codigo;
										} else {
											echo ($_SESSION['ref']['codigo']) ? $_SESSION['ref']['codigo']:$prod['prod_codigo'];
										} 
										echo '" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				
				<div class="col-md-2">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Unidad:</b></big>
									</td>
									<td>
										<select class="form-control" name="uniprod" size="1">
											<option value="">Seleccionar...</option>'."\n";
											foreach ($valarr['unidad'] as $k) {
												echo '												<option value="' . $k . '" '; if($prod['prod_unidad'] == $k) {echo 'selected="selected" ';} echo '>' . $k . '</option>'."\n";
											}
	echo '
										</select>
									</td>
								</tr>
								<tr>
									<td align="right">
										<br><big><b>Tipo:</b></big>
									</td>
									<td>
										<br>
										<select class="form-control" name="tangible" size="1">
											<option value="9">Seleccionar...</option>'."\n"; 
											foreach ($valarr['prod_tipo'] as $k => $v) {
												echo '												<option value="' . $k . '" '; if($prod['prod_tangible'] == $k) {echo 'selected="selected" ';} echo '>'. $v .'</option>'."\n";
											}
	echo '		
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>'."\n";

	if(isset($nuevo) && $nuevo=='1') {
		echo '		
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td colspan="2">
										<span class="alerta">
											Una vez dado de alta el producto, la cantidad y su costo se podrán ajustar desde recibo de productos.
										</span>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>';
	} else {
		echo '		
				<div class="col-md-2">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Existencias:</b></big>
									</td>
									<td>
										&nbsp;&nbsp;&nbsp;&nbsp;<big><big><b>' . $prod['prod_cantidad_existente'] . '</b></big></big>
										<input class="form-control" type="hidden" name="existencia" value="' . $prod['prod_cantidad_existente'] . '" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<big><b>Disponibles:</b></big>
									</td>
									<td>
										&nbsp;&nbsp;&nbsp;&nbsp;<big><big><b>' . $prod['prod_cantidad_disponible'] . '</b></big></big>
										<input type="hidden" name="disponible" value="' . $prod['prod_cantidad_disponible'] . '" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<big><b>Ajustar existencias:</b></big>
									</td>
									<td>
										<input class="form-control" type="text" name="nvaexist" size="1" maxlength="10" value="' . $prod['prod_cantidad_existente'] . '" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Motivo de ajuste:</b></big>
									</td>
									<td>
										<textarea class="form-control" name="motivoajuste" rows="3" cols="20">' . $_SESSION['ref']['motivoajuste'] . '</textarea>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	}
	
	$margen = (($prod['prod_precio'] - $prod['prod_costo']) / $prod['prod_precio']);
	
	echo '
			<br>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Precio de Venta Público:</b></big>
									</td>
									<td>
										<input class="form-control" type="text" name="precio" size="11" maxlength="20" value="'; 
										echo $prod['prod_precio'] . '" style="text-align:right;" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<br><big><b>Precio de Venta Interno:</b></big>
									</td>
									<td>
										<br><input class="form-control" type="text" name="precioint" size="11" maxlength="20" value="'; 
										echo $prod['prod_precioint'] . '" style="text-align:right;" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Costo de Compra:</b></big>
									</td>
									<td>
										<input class="form-control" type="text" name="prodcosto" size="11" maxlength="20" value="'; 
										echo $prod['prod_costo'] . '" style="text-align:right;" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<br><big><b>Margen de Utilidad:</b></big></td>
									<td>
										<br>&nbsp;&nbsp;&nbsp;&nbsp;<big><b>' . round(($margen * 100), 2) . '% </b></big>
										<input type="hidden" name="margen" size="4" maxlength="6" value="' . $margen . '" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Almacén:</b></big>
									</td>
									<td>
										<select class="form-control" name="almacen" size="1">
											<option value="">Seleccionar...</option>'."\n";
											foreach($nom_almacen as $k => $v) {
												echo '
												<option value="'.$k.'" '; if($prod['prod_almacen']==$k) {echo 'selected="selected" ';} echo '>' . $v . '</option>'."\n";
											} 
	echo '		
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	
	echo '
			<br>
			<div class="row">
				<div class="form-group">
              		<div class="col-md-5">
						<table>
							<tr>
								<td align="right">
									<big><b>Proveedor default:</b></big>
								</td>
								<td>
									<select class="form-control" name="prov_id">
											<option  value="0">...Seleccione</option>'."\n";
										foreach($provs as $i => $j) {
											echo '			  	
											<option  value="'.$i.'" '."\n";
											
											if($prod['prod_prov_id'] == $i){
												echo 'selected';
											}
												
												echo '>'."\n";
											echo '
												' . $j['nic'] . '
											</option>'."\n";
											
										}
		echo '			
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>'."\n";
	

	echo '	
			<br>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>	
								<tr>
									<td align="right">
										<br><big><b>Resurtir:</b></big>
									</td>
									<td colspan="2">
										Solicitar pedido cuando quede esta cantidad.
										<input class="form-control" type="text" name="resurtir" size="1" value="'; 
										echo ($_SESSION['ref']['resurtir']) ? $_SESSION['ref']['resutir']:$prod['prod_resurtir']; echo '" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
              			<div class="col-md-12">
							<table>	
								<tr>
									<td align="right">
										<br><big><b>Cantidad a cotizar:</b></big>
									</td>
									<td colspan="2">
										Fijar la cantidad que se cotizará una vez que el producto se agote.
										<input class="form-control" type="text" name="cant_cotizar" size="1" value="'; 
										echo ($_SESSION['ref']['prod_cant_cotizar']) ? $_SESSION['ref']['prod_cant_cotizar'] : $prod['prod_cant_cotizar']; echo '" />
									</td>
								</tr>'."\n";
	
	if($qv_activo == 1){ // --- Habilitar la opción de cotización en quien-vende.com ---
		
		echo '
								<tr>
									<td align="right">
										<br><big><b>Cotizar en quien-vende.com:</b></big>
									</td>
									<td colspan="2">
										<br><input type="radio" name="qv" id="q-vende" value="1"';
										if($prod['prod_qv'] == 1){
											echo ' checked ';
										}
										echo ' /<big>SI</big><br>
										<input type="radio" name="qv" id="q-vende" value="0"';
										if($prod['prod_qv'] == ''){
											echo ' checked ';
										}
										echo ' /<big>NO</big>
									</td>
								</tr>'."\n";
	}
		
	echo '
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-5">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<br><big><b>Ubicación:</b></big>
									</td>
									<td colspan="2">
										Lugar dentro del almacén en donde está ubicado el producto.
										<input class="form-control" type="text" name="local" size="15" maxlength="32" value="'; 
										echo ($_SESSION['ref']['local']) ? $_SESSION['ref']['local']:$prod['prod_local']; echo '" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	
	// --- Consultar pendientes ---
	$preg_pendientes = "SELECT * FROM " . $dbpfx . "prods_pendientes WHERE prod_id = '" . $prod_id . "' AND (prods_pendiente_adeudos > 0 OR prods_pendiente_entregados < prods_pendiente_requeridos)";
	$matr_pendientes = mysql_query($preg_pendientes) or die("ERROR: Fallo selección de prods_pendientes! " . $preg_pendientes);
	$num_pendientes = mysql_num_rows($matr_pendientes);
	
	if($prod['prod_cantidad_existente'] > 0 && $num_pendientes > 0) {
		echo '
			<div class="row">
				<div class="col-md-5">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Borrar?:</b></big>
									</td>	
									<td colspan="2" style="text-align:left;">
										&nbsp;&nbsp;Para poder desactivar un producto, primero debe quedar sin existencias y no tener adeudos
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	} else {
		echo '
			<div class="row">
				<div class="col-md-5">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Borrar?:</b></big>
									</td>	
									<td colspan="2" style="text-align:left;">
										&nbsp;&nbsp;<input type="checkbox" name="borrar" value="1" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	}
	
	
	echo '
			<br>
			<div class="row">
				<div class="col-md-4">
					<input type="hidden" name="prod_id" value="' . $prod['prod_id'] . '" />
					<input type="hidden" name="nuevo" value="' . $nuevo . '" />
					<input class="btn btn-lg btn-success" type="submit" value="Enviar" />
				</div>
			</div>'."\n";

	if(!isset($nuevo) || $nuevo=='') {
		$preg2 = "SELECT pp.op_cantidad, pp.op_costo, pp.op_pedido, pp.op_recibidos, p.prov_id, pp.op_fecha_promesa FROM " . $dbpfx . "orden_productos pp, " . $dbpfx . "pedidos p WHERE pp.prod_id='" . $prod_id . "' AND pp.op_pedido = p.pedido_id";
//		echo $preg2 . '<br>';
  		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de proveedores!");
	  	$pedidos = mysql_num_rows($matr2);
	
  		echo '
			<br>
			<div class="row">
				<div class="col-md-12">
					<h2 align="center">PEDIDOS</h2>
					<div class="form-group">
              			<div class="col-md-12">
							<div id="content-tabla">
								<table cellspacing="0" class="table-new">
									<tr>
										<th>Proveedor</th>
										<th>Pedido</th>
										<th>Cantidad</th>
										<th>Costo</th>
										<th>Fecha Promesa<br>de Entrega</th>
										<th>Recibidas</th>
										<th>Pendientes</th>
									</tr>'."\n";
		if($pedidos > 0) {
			$clase = 'claro';
			while($ped = mysql_fetch_array($matr2)) {
				$pendientes = $ped['op_cantidad'] - $ped['op_recibidos'];
				echo '			
									<tr class="' . $clase . '">
										<td>'."\n";
				if($prod['prod_tangible'] == 3) {
					echo '
											' . $usr[$ped['prov_id']] . ''."\n";
				} else {
					echo '
											<a href="proveedores.php?accion=consultar&prov_id=' . $ped['prov_id'] . '" target="_blank" >' . $provs[$ped['prov_id']]['nic'] . '</a>'."\n";
				}
				echo '
										</td>
										<td>
											<a href="pedidos.php?accion=consultar&pedido=' . $ped['op_pedido'] . '" target="_blank">' . $ped['op_pedido'] . '</a>
										</td>
										<td>
											' . $ped['op_cantidad'] . '
										</td>
										<td style="text-align:right;">
											' . money_format('%n', $ped['op_costo']) . '
										</td>
										<td>
											' . $ped['op_fecha_promesa'] . '
										</td>
										<td>
											' . $ped['op_recibidos'] . '
										</td>
										<td>
											' . $pendientes . '
										</td>
									</tr>'."\n";

				if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
			}
		} else {
			echo '
									<tr class="claro">
										<td colspan="7">
											<big><b>No se encontró ningún pedido.</b></big>
										</td>
									</tr>'."\n";
		}
		echo '		
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>'."\n";
		
		$preg3 = "SELECT * FROM " . $dbpfx . "prod_prov  WHERE prod_id='" . $prod_id . "'";
		//echo $preg3 . '<br>';
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de proveedores!");
  		$cotiza = mysql_num_rows($matr3);
   	
	  	echo '		
			<br>
			<div class="row">
				<div class="col-md-12">
					<h2 align="center">COTIZACIONES</h2>
					<div class="form-group">
              			<div class="col-md-12">
							<div id="content-tabla">
								<table cellspacing="0" class="table-new">
									<tr>
										<th>Proveedor</th>
										<th>Costo<br>Unitario</th>
										<th>Días de Entrega</th>
										<th>Días de crédito</th>
										<th>Solicitada</th>
										<th>Remover Cotización</th>
										<th>Hacer Pedido</th>
									</tr>'."\n";
		if($cotiza > 0) {
			$j=0;
			$clase = 'claro';
			while($cot = mysql_fetch_array($matr3)) {
				echo '
									<input type="hidden" name="cot_prov_id['.$j.']" value="'.$cot['prod_prov_id'].'" />
									<tr class="' . $clase . '">
										<td>
											' . $provs[$cot['prod_prov_id']]['nic'] . '
										</td>
										<td>
											<input type="text" name="cot_costo['.$j.']" value="' . money_format('%n', $cot['prod_costo']) . '" size="10"/>
										</td>
										<td>
											<input type="text" name="cot_entrega['.$j.']" value="' . $cot['dias_entrega'] . '" size="3"/>
										</td>
										<td>
											<input type="text" name="cot_credito['.$j.']" value="' . $cot['dias_credito'] . '" size="3"/>
										</td>
										<td>
											'.$cot['fecha_cotizado'].'
										</td>
										<td>
											<input type="checkbox" name="quitacot['.$j.']" value="1" />
										</td>
										<td>
											<input type="radio" name="hazped" value="' . $prod_id . ':' . $cot['prod_prov_id'] . '" />
										</td>
									</tr>'."\n";
				$j++;
				if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
			}
		} else {
			echo '
									<tr class="' . $clase . '">
										<td colspan="7">
											<b><big>No se encontró ninguna cotización.</big></b>
										</td>
									</tr>'."\n";
		}
		echo '		
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>'."\n";
	}
		
	echo '
			<br>
			<div class="row">
				<div class="col-md-4">
					<input type="hidden" name="prod_id" value="' . $prod['prod_id'] . '" />
					<input type="hidden" name="nuevo" value="' . $nuevo . '" />
					<input class="btn btn-lg btn-success" type="submit" value="Enviar" />
				</div>
			</div>
			</form>
			<br>
			
			<div class="row">'."\n";
	
	$preg_pendientes = "SELECT * FROM " . $dbpfx . "prods_pendientes WHERE prod_id = '" . $prod_id . "' AND (prods_pendiente_adeudos > 0 OR prods_pendiente_entregados < prods_pendiente_requeridos)";
	$matr_pendientes = mysql_query($preg_pendientes) or die("ERROR: Fallo selección de prods_pendientes! " . $preg_pendientes);
	
	echo '
			
				<div class="col-md-10">
					<h2>Pendientes de Surtir y entregar</h2>
					<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr>
								<th>O.T.</th>
								<th>Tarea</th>
								<th>Fecha del requerimiento</th>
								<th>Requeridos</th>
								<th>Apartados</th>
								<th>Adeudos</th>
								<th>Entregados a operador</th>
							</tr>'."\n";
	
	$clase = 'claro';
	while($pendientes = mysql_fetch_array($matr_pendientes)){
		$fecha =  date('d-m-Y', strtotime($pendientes['prods_pendiente_fecha']));
		echo '
							<tr class="' . $clase . '">
								<td>
									<a href="ordenes.php?accion=consultar&orden_id=' . $pendientes['orden_id'] . '">' . $pendientes['orden_id'] . '</a>
								</td>
								<td>
									<a href="proceso.php?accion=consultar&orden_id=' . $pendientes['orden_id'] . '#' . $pendientes['sub_orden_id'] . '">' . $pendientes['sub_orden_id'] . '</a>
								</td>
								<td>
									' . $fecha . '
								</td>
								<td>' . $pendientes['prods_pendiente_requeridos'] . '</td>
								<td>' . $pendientes['prods_pendiente_surtidos'] . '</td>
								<td>' . $pendientes['prods_pendiente_adeudos'] . '</td>
								<td>' . $pendientes['prods_pendiente_entregados'] . '</td>
							</tr>'."\n";
		
		if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
	}
	
	echo '
						</table>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-1">
					<a href="refacciones.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Lista de Partes" title="Regresar a la Lista de Partes"></a>
				</div>
			</div>


		</div>'."\n";
}

elseif($accion==="actualizar" || $accion==="insertar") {

	if (validaAcceso('1115035', $dbpfx) == 1) {	
		// Acceso autotizado
	} elseif($solovalacc != 1 && ($_SESSION['rol08']=='1')) {
		// Acceso autotizado
	} else {
		redirigir('index.php?mensaje=Acceso no Autorizado');
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
	$cant_cotizar = limpiarNumero($cant_cotizar);
	
	$precio = limpiarNumero($precio); $_SESSION['ref']['precio']=$precio;
	$precioint = limpiarNumero($precioint); $_SESSION['ref']['precioint']=$precioint;
	$prodcosto = limpiarNumero($prodcosto); $_SESSION['ref']['prodcosto']=$prodcosto;
	$resurtir = limpiarNumero($resurtir); $_SESSION['ref']['resurtir']=$resurtir;
	$cant_cotizar = limpiarNumero($cant_cotizar); $_SESSION['ref']['cant_cotizar']=$cant_cotizar;
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
	if($cant_cotizar == '' || $cant_cotizar == 0){
		$error = 1; $msj .= 'Debe seleccionar una cantidad a cotizar.<br>';
	}
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
			foreach ($valarr['prod_tipo'] as $k => $v) {
				if($pact['prod_tangible'] == $k) { $nomant = $v; }
				if($tangible == $k) { $nomnvo = $v; }
			}
			$sql_data_array = array('prod_id' => $prod_id,
				'tipo' => 10, // Comentarios de cambio de identidad de producto
				'evento' => 'Cambio de: ' . $pact['prod_nombre'] . ' ' . $pact['prod_marca'] . ' ' . $pact['prod_unidad'] . ' ' . $nomant,
				'motivo' => 'A: ' . $nombre . ' ' . $marca . ' ' . $uniprod . ' ' . $nomnvo,
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
				'evento' => 'Cambio de: ' . $nom_almacen[$pact['prod_almacen']] . ' ' . $pact['prod_resurtir'] . ' ' . $pact['prod_local'],
				'motivo' => 'A: ' . $nom_almacen[$almacen] . ' ' . $resurtir . ' ' . $local,
				'usuario' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');
		}
		
		if($accion=='insertar') { $parametros = ''; } else { $parametros = 'prod_id = ' . $prod_id;}
		$sql_data_array = [
			'prod_codigo' => $codigo,
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
			'prod_cant_cotizar' => $cant_cotizar,
			'prod_local' => $local,
			'prod_activo' => $activo,
		];
		
		if($prov_id != ''){
			$sql_data_array['prod_prov_id'] = $prov_id;
		}
		
		if($qv == 1){
			$sql_data_array['prod_qv'] = 1;
		} elseif($qv == 0){
			$sql_data_array['prod_qv'] = 'null';
		}
		
		if($accion=='insertar') {
			$prod_id = ejecutar_db($dbpfx . 'productos', $sql_data_array, $accion, $parametros);
		} else {
			ejecutar_db($dbpfx . 'productos', $sql_data_array, $accion, $parametros);
		} 
		
		if($neok == 1) {
			//echo 'Se actualiza existencias<br>';
			unset($sql_data_array);
			$sql_data_array = [
				'prod_id' => $prod_id,
				'tipo' => 0, // Comentarios de ajuste manual de existencias
				'evento' => 'Ajuste manual de existencias de ' . $existencia . ' a ' . $nvaexist . '.',
				'motivo' => $motivoajuste,
				'usuario' => $_SESSION['usuario']
			];
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');
			
			// ------- Se busca si hay adeudos con paquetes de servicio ---------
			$preg_adeudos = "SELECT * FROM " . $dbpfx . "prods_pendientes WHERE prod_id = '" . $prod_id . "' AND prods_pendiente_adeudos > 0";
			//echo $preg_adeudos . '<br>';
			$matr_pendientes = mysql_query($preg_adeudos) or die("ERROR:! " . $preg_adeudos);
			$adeudos = mysql_num_rows($matr_pendientes);
			
			if($adeudos > 0){
				
				while($con_adeudos = mysql_fetch_array($matr_pendientes)){
					
					if($nvadispo >= $con_adeudos['prods_pendiente_adeudos']){ // --- Si nueva disponibilidad alcanza para surtir se procede ---
						$surtido_actualizado = $con_adeudos['prods_pendiente_surtidos'] + $con_adeudos['prods_pendiente_adeudos'];
						unset($sql_data);
						$sql_data = [
							'prods_pendiente_surtidos' => $surtido_actualizado,
							'prods_pendiente_adeudos' => 0,
						];
						$parametros = " op_id = '" . $con_adeudos['op_id'] . "' ";
						ejecutar_db($dbpfx . 'prods_pendientes', $sql_data, 'actualizar', $parametros);
						$nvadispo = $nvadispo - $con_adeudos['prods_pendiente_adeudos'];

					} elseif($nvadispo < $con_adeudos['prods_pendiente_adeudos']){ // ---- Surtir las disponibles ---
						
						$surtido_actualizado = $con_adeudos['prods_pendiente_surtidos'] + $nvadispo;
						$adeudos_actualizado = $con_adeudos['prods_pendiente_adeudos'] - $nvadispo;
						unset($sql_data);
						$sql_data = [
							'prods_pendiente_surtidos' => $surtido_actualizado,
							'prods_pendiente_adeudos' => $adeudos_actualizado,
						];
						$parametros = " op_id = '" . $con_adeudos['op_id'] . "' ";
						ejecutar_db($dbpfx . 'prods_pendientes', $sql_data, 'actualizar', $parametros);
						$nvadispo = 0;
					}
					
					// ---- Actualizar el op_id ----
					$preg_op_id = "SELECT op_cantidad, op_recibidos FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $con_adeudos['op_id'] . "'";
					$matr_op_id = mysql_query($preg_op_id) or die("ERROR:! " . $preg_op_id);
					$info_op_id = mysql_fetch_assoc($matr_op_id);
					
					// --- revisar si se marca como ok el producto ---
					$total_recibido = $info_op_id['op_recibidos'] + $surtido_actualizado;
					
					if($total_recibido == $info_op_id['op_cantidad']){ // --- Ya fue surtido por completo el elemento ---
						$op_ok = 1;
						$surtido_op = $total_recibido;
					} else { // --- Aún hay pendientes por surtir ---
						$op_ok = 0;
						$surtido_op = $total_recibido;
					}
					
					unset($sql_data);
					$sql_data = [
						'op_recibidos' => $surtido_op,
						'op_ok' => $op_ok,
					];
					$parametros = " op_id = '" . $con_adeudos['op_id'] . "'";
					ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $parametros);
					
				}
				
				// ------- Actualizar nuevos disponibles ----
				unset($sql_data_array);
				$sql_data_array = [
					'prod_cantidad_disponible' => $nvadispo,
				];
				$parametros = "prod_id = '" . $prod_id . "' ";
				ejecutar_db($dbpfx . 'productos', $sql_data_array, 'actualizar', $parametros);
			}
			
			
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
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $preg1 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				}
			}
		}
		if($duplicado == 0 && $prov_id > '0') {
			/*
			$parametros = '';
			$sql_data_array = array('prod_prov_id' => $prov_id,
				'prod_costo' => limpiarNumero($costo),
				'dias_entrega' => $entrega,
				'dias_credito' => $credito,
				'prod_id' => $prod_id,
				'fecha_cotizado' => date('Y-m-d H:i:s', time()));
			ejecutar_db($dbpfx . 'prod_prov', $sql_data_array, 'insertar');
			*/
		}
		if($_FILES['imagen']) {
//			echo 'Imagen recibida';
			agrega_foto_almacen($prod_id, $_FILES['imagen'], $nombre, $dbpfx, $hacer);
		}

		unset($sql_data_array);
		unset($_SESSION['ref']);
		if($hazped != '') {
			$nvoped = explode(':', $hazped);
			redirigir('refacciones.php?accion=cotpedprod&prod_id=' . $nvoped[0] .'&nvoped1=' . $nvoped[1]);
		}
		redirigir('refacciones.php?accion=item&prod_id=' . $prod_id);
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
	
	// --- Sección de foto del producto ---
	$preg3 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE prod_id = '" . $prod_id . "'";
	$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de productos!");
	$foto = mysql_fetch_array($matr3);
	if ($foto['doc_archivo'] != '') {
		$etiqueta = '
					<div align="center">
						<a href="' . DIR_DOCS  .$foto['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'medianas/' . $foto['doc_archivo'] . '" alt=""></a>
						<input type="hidden" name="hacer" value="actualizar"/>
					</div>'."\n";
	} else {
		$etiqueta = '
					<div align="center">
						<img src="' . DIR_DOCS . 'documento.png" alt="Sin imagen" title="Sin imagen">
						<input type="hidden" name="hacer" value="insertar"/>
					</div>'."\n";
	}

	

	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	
	echo '
		<div class="page-content">
		
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header">
						<div class="panel-title">
		  					<h2>Cotizaciones y Pedidos de Refacciones y Consumibles</h2>
						</div>
			  		</div>
				</div>
			</div>
			<br>
			
			<form action="refacciones.php?accion=gestprod" method="post" enctype="multipart/form-data">
			<div class="row">
				<div class="col-sm-12 ">
					<div class="col-sm-10">
						<div id="content-tabla">
							<table cellspacing="0" class="table-new">
								<tr>
									<th><big>Nombre</big></th>
									<th><big>Marca</big></th>
									<th><big>Código</big></th>
									<th><big>Existencias</big></th>
									<th><big>Unidad</big></th>
									<th><big>Cantidad</big></th>
									<th><big>Costo Unitario</big></th>
									<th><big>Acciones</big></th>
								</tr>'."\n";
	
	$cue = 0;
	$clase = 'claro';
	foreach($_SESSION['cotped']['prod_id'] as $k => $v) {
		echo '					
								<tr class="' . $clase . '">
								<td>
									<a href="refacciones.php?accion=item&prod_id=' . $v . '">' . $_SESSION['cotped']['prod_nombre'][$k] . '</a><input type="hidden" name="nombre[' . $k . ']" value="' . $_SESSION['cotped']['prod_nombre'][$k] . '" />
								</td>
								<td>
									' . $_SESSION['cotped']['prod_marca'][$k] . '<input type="hidden" name="prod_id[' . $k . ']" value="' . $v . '" />
								</td>
								<td>
									' . $_SESSION['cotped']['prod_codigo'][$k] . '<input type="hidden" name="codigo[' . $k . ']" value="' . $_SESSION['cotped']['prod_codigo'][$k] . '" />
								</td>
								<td style="text-align:right;">
									<input type="hidden" name="existente[' . $k . ']" value="' . $_SESSION['cotped']['prod_cantidad_existente'][$k] . '">
									<input type="hidden" name="tangible[' . $k . ']" value="' . $_SESSION['cotped']['prod_tangible'][$k] . '" />' . $_SESSION['cotped']['prod_cantidad_existente'][$k] . '
								</td>
								<td style="text-align:right;">
									' . $_SESSION['cotped']['prod_unidad'][$k] . '
								</td>
								<td>
									<input type="text" name="cantidad[' . $k . ']" size="4" value="' . $_SESSION['cotped']['cantidad'][$k] . '" style="text-align:right;">
								</td>
								<td style="text-align:right;">
									<input type="text" name="costo[' . $k . ']" size="11" value="' . number_format($_SESSION['cotped']['prod_costo'][$k],2) . '" style="text-align:right;">
								</td>
								<td>
									Quitar<input type="checkbox" name="quitar[' . $k . ']" value="1">
								</td>
							</tr>'."\n";
		$cue++;
		if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
	}
	
	echo '	
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	
	echo '	
			<div class="row">
				<div class="col-md-12 panel-body">
					<div class="form-group">
      					<div class="col-md-12">
							<a href="refacciones.php?accion=listar">Agregar más productos</a>
							<table>
								<tr>
									<td align="right"><big><b>Proveedor:</b></big></td>
									<td>
										<br>
										<select class="form-control" name="prov_selec[]" multiple="multiple" size="4"/>'."\n";
											foreach($provs as $k => $v) {
												echo '			<option value="' . $k . '"';
												if($k == $nvoped1) { echo ' selected="selected" '; }  // haciendo pedido desde item
												echo '>' . $v['nic'] . '</option>'."\n";
											}
	echo '		
										</select>
									</td>
								</tr>
								<tr>
									<td align="right"><big><b>Tipo de Solicitud:</b></big></td>
									<td >
										<select class="form-control" name="tipo_pedido" />
											<option value="">Seleccionar...</option>
											<option value="2">' . TIPO_PEDIDO_2 . '</option>
											<option value="3">' . TIPO_PEDIDO_3 . '</option>
											<option value="10">' . TIPO_PEDIDO_10 . '</option>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-12">
					<input type="hidden" name="bodega" value="1" />
					<input class="btn btn-success" name="enviar" value="Enviar" type="submit">
					<button class="btn btn-danger" name="limpiar" value="limpiar">Eliminar Partidas</button>	
				</div>
			</div>
			</form>
		</div>'."\n";

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
		$costo[$j] = limpiarNumero($costo[$j]);
		if($cantidad[$j] <= 0 && $quitar[$j] != '1') {
			$mensaje .= 'La cantidad del producto ' . $nombre[$j] . ' es menor o igual a CERO.<br>'; $error = 'si';
		}
		if($costo[$j] <= 0 && $quitar[$j] != '1') {
			$mensaje .= 'El costo del producto ' . $nombre[$j] . ' es menor o igual a CERO.<br>'; $error = 'si';
		}
	}


	$j=0;

	if($error == 'no') {
		$j=0;
		if($tipo_pedido < 4) {
// ------------ Crear pedidos de almacén --------------------
			$prov_id = $prov_selec[0];
			$subtotal = 0; 
			foreach($prod_id as $j => $w) {
				if($cantidad[$j] > 0 && $costo[$j] >0 && $quitar[$j] != '1') {
					$subtotal = $subtotal + ($cantidad[$j] * $costo[$j]);
				}
			}
			$iva = round(($subtotal * $impuesto_iva), 2);
			$sql_array = array('prov_id' => $prov_id,
				'orden_id' => '999999997',
				'pedido_tipo' => $tipo_pedido,
				'subtotal' =>  $subtotal,
				'impuesto' => $iva,
				'usuario_pide' => $_SESSION['usuario']);
			$pedido = ejecutar_db($dbpfx . 'pedidos', $sql_array, 'insertar');
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
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $preg1 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
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
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos a proveedores! " . $preg1);
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
//			$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
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
	
	echo '
		<div class="page-content">
		
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header">
						<div class="panel-title">
		  					<h2>
								Paquetes de Servicio '."\n";
								if($codigo != ''){
									echo 'Código: ' . $codigo . ' ';
								}
								if($nombre != ''){
									echo 'Nombre: ' . $nombre . ' ';
								}
	echo '
							</h2>
						</div>
			  		</div>
				</div>
			</div>
			
			<div class="row"> <!- mensajes -->
				<div class="col-md-12">
	  				<span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-6 panel-body shadow-box">
					<div class="form-group">
      					<div class="col-md-12">
							<form action="refacciones.php?accion=listpaqs" method="post" enctype="multipart/form-data">
							<table>
								<tr>
									<td align="right" class="obscuro"><big><b>Buscar por código:</b></big></td>
									<td>
										<input class="form-control" type="text" name="codigo" id="codigo" size="15">
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Buscar por nombre:</b></big></td>
									<td>
										<input class="form-control" type="text" name="nombre" size="15">
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Filtrar por Área de Servicio:</b></big></td>
									<td>
										<select class="form-control" name="area" size="1">
											<option value="">Seleccionar...</option>'."\n";
											for($i=1;$i<=$num_areas_servicio;$i++) {
											echo '				
												<option value="' . $i . '">' . constant('NOMBRE_AREA_' . $i) . '</option>'."\n";
											}											
	echo '			
										</select>
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td>
										<a href="refacciones.php?accion=paquete&nuevo=1">
											<img src="idiomas/' . $idioma . '/imagenes/agregar_paquete_servicio.png" width="50" height="50"><br>
											Nuevo Paquete
										</a>
									</td>
								</tr>
							</table>
							</form>
						</div>
					</div>'."\n";
	
	if((isset($area) && $area!='') || (isset($nombre) && $nombre!='') || (isset($codigo) && $codigo!='')) { 
		if(isset($area) && $area!='') { $preg .= "AND paq_area='" . $area . "' "; }
		if(isset($nombre) && $nombre!='') { $preg .= "AND paq_nombre LIKE '%" . $nombre . "%' "; }
		if(isset($codigo) && $codigo!='') { $preg .= "AND paq_nic LIKE '%" . $codigo . "%' "; }
	}
	
	$preg0 = "SELECT paq_id FROM " . $dbpfx . "paquetes WHERE paq_activo='1' ";
	$preg0 = $preg0 . $preg;
	//echo $preg0 . '<br>';
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
	$filas = mysql_num_rows($matr0);
	$renglones = 20;
	$paginas = (round(($filas / $renglones) + 0.49999999) - 1);

   	if(!isset($pagina)) { $pagina = 0;}
	$inicial = $pagina * $renglones;
	//echo $paginas;
	$preg1 = "SELECT * FROM " . $dbpfx . "paquetes WHERE paq_activo = '1' ";
	$preg1 = $preg1 . $preg;
	$preg1 .= "ORDER BY paq_area, paq_nombre LIMIT " . $inicial . ", " . $renglones;
	//echo $preg1;
   	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de paquetes!");

	echo '
					<div align="right">
						<a href="refacciones.php?accion=listpaqs&pagina=0">Inicio</a>&nbsp;';

	if($pagina > 0) {
		$url = $pagina - 1;
		echo '<a href="refacciones.php?accion=listpaqs&pagina=' . $url . '">Anterior</a>&nbsp;';
	}
	if($pagina < $paginas) {
		$url = $pagina + 1;
		echo '<a href="refacciones.php?accion=listpaqs&pagina=' . $url . '">Siguiente</a>&nbsp;';
	}
	echo '<a href="refacciones.php?accion=listpaqs&pagina=' . $paginas . '">Ultima</a>'."\n";
		
	echo '	
					</div>
					<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr>
								<th><big>Área</big></th>
								<th><big>Nombre</big></th>
								<th><big>Nic</big></th>
								<th><big>Acciones</big></th>
							</tr>'."\n";
	
	$cue = 0;
	$clase = 'claro';
	while($paq = mysql_fetch_array($matr1)){
		echo '					
							<tr class="' . $clase . '">
								<td>' . constant('NOMBRE_AREA_'.$paq['paq_area']) . '</td>
								<td>' . $paq['paq_nombre'] . '</td>
								<td>' . $paq['paq_nic'] . '</td>
								<td>
									<a href="refacciones.php?accion=paquete&paq_id=' . $paq['paq_id'] . '">Listar</a> / <a href="refacciones.php?accion=paquete&paq_id=' . $paq['paq_id'] . '&quitar=1">Remover</a>
								</td>
							</tr>'."\n";
		$cue++;
		if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
	}
	
					
	echo '
						</table>
					</div>
				</div>
			</div>
			<br>
		</div>'."\n";
}

elseif($accion==='paquete') {
	
	$funnum = 1115060;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
		
	if($paq_id!='') {
		$preg0 = "SELECT * FROM " . $dbpfx . "paquetes WHERE paq_id='" . $paq_id . "'";
		//echo $preg0 . '<br>';
   		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
	   	$paq = mysql_fetch_array($matr0);
	}
	
	echo '
		<div class="page-content">
		
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header">
						<div class="panel-title">'."\n";
						
						if($paq_id != ''){
							echo ' <h2>Paquete de Servicio  "' . $paq['paq_nombre'] . '" </h2>'."\n";
						} else{
							echo ' <h2>Nuevo Paquete de Servicio</h2>'."\n";
						}
	
	echo '
						</div>
			  		</div>
				</div>
			</div>
			
			<div class="row"> <!- mensajes -->
				<div class="col-md-12">
	  				<span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span>
				</div>
			</div>'."\n";
	
	
	echo '	
			<form action="refacciones.php?accion=';
	if($nuevo=='1') { echo 'inspaq';} else { echo 'actpaq';}
	echo '" method="post" enctype="multipart/form-data">'."\n";
	
	echo '
			<div class="row">
				<div class="col-md-12 panel-body">
					<div class="form-group">
      					<div class="col-md-12">
							<table>
								<tr>
									<td align="right"><big><b>Nombre:</b></big></td>
									<td>
										<input class="form-control" type="text" name="nombre" size="50" maxlength="255" value="'; 
										echo ($_SESSION['ref']['nombre']) ? $_SESSION['ref']['nombre']:$paq['paq_nombre']; echo '" />
									</td>
								</tr>
								<tr>
									<td align="right"><big><b>Descripción:</b></big></td>
									<td>
										<input class="form-control" type="text" name="descripcion" size="50" maxlength="255" value="'; 
										echo ($_SESSION['ref']['descripcion']) ? $_SESSION['ref']['descripcion']:$paq['paq_descripcion']; echo '" />
									</td>
								</tr>
								<tr>
									<td align="right"><big><b>Nic:</b></big></td>
									<td>
										<input class="form-control" type="text" name="nic" size="40" maxlength="32" value="'; 
										echo ($_SESSION['ref']['nic']) ? $_SESSION['ref']['nic']:$paq['paq_nic']; echo '" />
									</td>
								<tr>
									<td align="right"><big><b>Area Padre:</b></big></td>
									<td>
										<select class="form-control" name="areapadre" size="1">
											<option value="" disabled selected>Seleccionar...</option>'."\n";

											for($i=1;$i<=$num_areas_servicio;$i++) {
												echo '					
													<option value="'.$i.'" '; if($paq['paq_area']==$i) {echo 'selected="selected" ';} echo '>' . constant('NOMBRE_AREA_'.$i) . '</option>'."\n";
											}
	echo '					
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";

		
	if($nuevo!='1') {
		
		echo '
			<div class="row">
				<div class="col-md-12">
	  				<div>
						<div class="panel-title">
		  					<h2>Productos de este Paquete</h2>
						</div>
			  		</div>
				</div>
				<a href="refacciones.php?accion=listar&paquete=' . $paq_id . '">
					<img src="idiomas/' . $idioma . '/imagenes/agregar_producto.png" width="50" height="50"><br>Agregar Nuevo<br>Producto
				</a><br>
				Primero agregue cada una de las refacciones, consumibles y mano de obra que integraran el paquete y<br>después indique a que área serán asignadas así como su cantidad.
				
			</div>'."\n";
		
		
		echo '
			<div class="row">
				<div class="col-md-10 panel-body">
					<div class="form-group">
      					<div class="col-md-12">
							<table cellspacing="0" class="table-new">
								<tr>
									<th><big>Area</big></th>
									<th><big>Nombre</big></th>
									<th><big>Marca</big></th>
									<th><big>Código</big></th>
									<th><big>Cant</big></th>
									<th><big>Precio</big></th>
									<th><big>Acciones</big></th>
								</tr>'."\n";

	$preg1 = "SELECT pc_id, pc_prod_id, pc_prod_cant, pc_area_id FROM " . $dbpfx . "paq_comp WHERE pc_paq_id='" . $paq_id . "'";
	//echo $preg1 . '<br>';
  	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");

   	while($pc = mysql_fetch_array($matr1)) {
		$preg2 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id='" . $pc['pc_prod_id'] . "'";
		//echo $preg2 . '<br>';
  		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos!");
		$clase = 'claro';
  		while($prod = mysql_fetch_array($matr2)) {
  			echo '					
								<tr class="' . $clase . '">
									<td>
										<select class="form-control" name="area['.$pc['pc_id'].']" size="1" required>
											<option value="" disabled selected>Seleccionar...</option>'."\n";
										for($i=1;$i<=$num_areas_servicio;$i++) {
											echo '					
												<option value="'.$i.'" ';
											if($pc['pc_area_id']==$i){
												echo 'selected="selected" ';
											} 
											echo '>' . constant('NOMBRE_AREA_'.$i) . '</option>'."\n";
										}

				echo '		
										</select>
									</td>
									<td>
										'.$prod['prod_nombre'].'
									</td>
									<td>
										'.$prod['prod_marca'].'
									</td>
									<td>
										'.$prod['prod_codigo'].'
									</td>
									<td style="text-align:center;">
										<input type="text" name="cantidad['.$pc['pc_id'].']" size="1" value="'.$pc['pc_prod_cant'].'">
									</td>
									<td>
										'. money_format('%n', $prod['prod_precio']) .'</td><td>Quitar <input type="checkbox" name="quitar['.$pc['pc_id'].']" value="1">
									</td>
								</tr>'."\n";
				if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
	  	}
   	}
	
	echo '		
								<tr class="claro">
									<td>Borrar Paquete?</td>
									<td> 
										<input type="checkbox" name="borrar" value="1"/>
									</td>
									<td colspan="5"></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	} 
	echo '
			<div class="row">
				<div class="col-md-12">
					<input type="hidden" name="paq_id" value="' . $paq['paq_id'] . '" />
					<input type="hidden" name="nuevo" value="' . $nuevo . '" />
					<input class="btn btn-success" type="submit" value="Enviar" />
				</div>
			</div>
			</form>
		</div>'."\n";
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
		if($accion=='inspaq'){
			$parametros = '';
			$modifica = 'insertar'; 
		} else{ 
			$parametros = 'paq_id = ' . $paq_id; 
			$modifica = 'actualizar';
		} 
		$sql_data_array = [
			'paq_nombre' => $nombre,
			'paq_descripcion' => $descripcion,
			'paq_area' => $areapadre,
			'paq_nic' => $nic,
			'paq_activo' => $activo
		];
		if($accion=='inspaq') {
			$paq_id = ejecutar_db($dbpfx . 'paquetes', $sql_data_array, $modifica, $parametros);
			$concepto = 'Paquete ' . $paq_id . ' agregado'; 
		} else {
			ejecutar_db($dbpfx . 'paquetes', $sql_data_array, $modifica, $parametros);
			foreach($cantidad as $k => $v) {
				$preg0 = "UPDATE " . $dbpfx . "paq_comp SET pc_prod_cant = '" . $v . "', pc_area_id = '" . $area[$k] . "' WHERE pc_id = '" . $k . "'";
//				echo $preg0 . '<br>';
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo actualización de productos!");
				$archivo = '../logs/' . time() . '-base.ase';
				$myfile = file_put_contents($archivo, $preg0 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
			}
			foreach($quitar as $k => $v) {
				if($v == '1') { 
					$preg0 = "DELETE FROM " . $dbpfx . "paq_comp WHERE pc_id = '" . $k . "'";
//						echo $preg0 . '<br>';
					$matr0 = mysql_query($preg0) or die("ERROR: Fallo remoción de productos!");
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $preg0 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
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
	if ($retorno == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol08']=='1' || validaAcceso('1050003', $dbpfx) == 1) {
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
		echo '		<table cellpadding="2" cellspacing="0" border="1" width="840">'."\n";
		echo '		'."\n";
		echo '			<tr class="cabeza_tabla"><td colspan="12">Captura de Cotizaciones para Cantidad: ' . $op['op_cantidad'] . '. Descripción: ' . $op['op_nombre'] . '.<br>del vehículo ' . $veh['completo'] . ' de la OT ' . $orden_id . '</td></tr>'."\n";
		echo '			<tr><td style="text-align:left;"><div style="position: relative; display: inline-block;"><a onclick="muestraAbajo' .  $op_id . '()" class="ayuda" >' . $lang['Proveedor'] . '</a><div id="CancelarCotización' . $prods['op_id'] . '" class="muestra-contenido">' . $ayuda['CotizaciónQV'] . '</div></div></td><td style="text-align:left;">' . $lang['FechaCotización'] . '</td><td style="text-align:left;">' . $lang['CostoUnitario'] . '</td><td style="text-align:left;">' . $lang['CostoTotal'] . '</td><td style="text-align:left;">' . $lang['DíasCrédito'] . '</td><td style="text-align:left;">' . $lang['DíasEntrega'] . '</td>';
		echo '<td>' . $lang['Origen'] . '</td><td>' . $lang['Condición'] . '</td><td>' . $lang['Vencimiento'] . '</td><td>' . $lang['CostoEnvio'] . '</td><td>' . $lang['MensajeVendedor'] . '</td><td style="text-align:center;">' . $lang['CancelarCotización'] . '</td>';
		echo '</tr>'."\n";
		echo '				<script>
				function muestraAbajo' . $op_id . '() {
    				document.getElementById("CancelarCotización' . $prods['op_id'] . '").classList.toggle("mostrar");
				}
			</script>'."\n";
		$j = 0;
		while($prov3 = mysql_fetch_array($matr1)) {
			if($prov3['cotqv'] == 1) {
				echo '			<tr><td>' . $provs[$prov3['prod_prov_id']]['nic'] . '</td>';
				$sbt = $op['op_cantidad'] * $prov3['prod_costo'];
				echo '<td>' . date('Y-m-d H:i', strtotime($prov3['fecha_cotizado'])) . '</td>';
				echo '<td style="text-align:right;">' . number_format($prov3['prod_costo'],2) . '</td>';
				echo '<td style="text-align:right;">' . number_format($sbt,2) . '</td>';
				echo '<td style="text-align:center;">' . $prov3['dias_credito'] . '</td>';
				echo '<td style="text-align:center;">' . $prov3['dias_entrega'] . '</td>';
				echo '<td style="text-align:center;">' . $prov3['prod_origen'] . '</td>';
				echo '<td style="text-align:center;">' . $prov3['prod_condicion'] . '</td>';
				echo '<td>' . date('Y-m-d', strtotime($prov3['prod_vencimiento'])) . '</td>';
				echo '<td style="text-align:center;">' . number_format($prov3['prod_costo_envio'],2) . '</td>';
				echo '<td>' . $prov3['prod_mensaje'] . '</td>';
				echo '<td>' . $lang['RecibidaQV'] . '</td></tr>'."\n";
			} else {
				echo '			<tr><td>' . $provs[$prov3['prod_prov_id']]['nic'] . '<input type="hidden" name="op_id[' . $j . ']" value="' . $op_id . '" /><input type="hidden" name="prov_id[' . $j . ']" value="' . $prov3['prod_prov_id'] . '" /></td>';
				$sbt = $op['op_cantidad'] * $prov3['prod_costo'];
				echo '<td>' . date('Y-m-d', strtotime($prov3['fecha_cotizado'])) . '</td>';
				echo '<td><input style="text-align:right;" type="text" name="costo[' . $j . ']" value="' . $prov3['prod_costo'] . '" size="4" /></td>';
				echo '<td><input style="text-align:right;" type="text" name="sbt[' . $j . ']" value="' . $sbt . '" size="4" /></td>';
				echo '<td><input style="text-align:right;" type="text" name="credito[' . $j . ']" value="' . $prov3['dias_credito'] . '" size="2" /></td>';
				echo '<td><input style="text-align:right;" type="text" name="entrega[' . $j . ']" value="' . $prov3['dias_entrega'] . '" size="2" /></td>'."\n";
				echo '				<td><select name="origen[' . $j . ']">'."\n";
				echo '					<option value="">Opcional</option>'."\n";
				echo '					<option value="' . $lang['Original'] . '"';
				if($prov3['prod_origen'] == $lang['Original']) { echo ' selected '; }
				echo '>' . $lang['Original'] . '</option>'."\n";
				echo '					<option value="' . $lang['Imitación'] . '"';
				if($prov3['prod_origen'] == $lang['Imitación']) { echo ' selected '; }
				echo '>' . $lang['Imitación'] . '</option>'."\n";
				echo '					<option value="' . $lang['Reconstruido'] . '"';
				if($prov3['prod_origen'] == $lang['Reconstruido']) { echo ' selected '; }
				echo '>' . $lang['Reconstruido'] . '</option>'."\n";
				echo '					<option value="' . $lang['Homologado'] . '"';
				if($prov3['prod_origen'] == $lang['Homologado']) { echo ' selected '; }
				echo '>' . $lang['Homologado'] . '</option>'."\n";
				echo '				</select></td>'."\n";
				echo '				<td><select name="condicion[' . $j . ']">'."\n";
				echo '					<option value="">Opcional</option>'."\n";
				echo '					<option value="' . $lang['Nuevo'] . '"';
				if($prov3['prod_condicion'] == $lang['Nuevo']) { echo ' selected '; }
				echo '>' . $lang['Nuevo'] . '</option>'."\n";
				echo '					<option value="' . $lang['Usado'] . '"';
				if($prov3['prod_condicion'] == $lang['Usado']) { echo ' selected '; }
				echo '>' . $lang['Usado'] . '</option>'."\n";
				echo '					<option value="' . $lang['Reparado'] . '"';
				if($prov3['prod_condicion'] == $lang['Reparado']) { echo ' selected '; }
				echo '>' . $lang['Reparado'] . '</option>'."\n";
				echo '				</select></td>'."\n";
				echo '				<td><input type="text" name="vencimiento[' . $j . ']" value="';
				if(strtotime($prov3['prod_vencimiento']) > 1000000) { echo date('Y-m-d', strtotime($prov3['prod_vencimiento'])); }
//				echo $prov3['prod_vencimiento'];
				echo '" size="8"></td>';
				echo '<td><input style="text-align:right;" type="text" name="costoenvio[' . $j . ']" value="' . $prov3['prod_costo_envio'] . '" size="4" /></td><td></td>';
				echo '<td style="width:60px;text-align:center;"><input type="radio" name="cancela[' . $j . ']" value="1" /></td></tr>'."\n";
				$j++;
			}
		}
		echo '			<tr><td colspan="12"><input type="submit" name="enviar" value="Aplicar">
				<input type="hidden" name="orden_id" value="' . $orden_id . '" />
				<input type="hidden" name="op_cantidad" value="' . $op['op_cantidad'] . '" />
			</td></tr>'."\n";
		echo '			<tr><td colspan="12" style="text-align:left;"><div class="control"><a href="refacciones.php?accion=gestionar&orden_id=' . $orden_id . '#' . $op_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Gestión de Refacciones" title="Regresar a la Gestión de Refacciones"></a></div></td></tr>'."\n";
		echo '		</table></form>'."\n";
	} else {
		echo 'No hay cotizaciones disponibles para este producto';
	}
}

elseif($accion==="guardacotiza") {

	$funnum = 1115090;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol08']=='1' || validaAcceso('1050003', $dbpfx) == 1) {
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
		$costoenvio[$k] = limpiarNumero($costoenvio[$k]);
		$vencimiento[$k] = strtotime($vencimiento[$k]);
		$preg1 = "SELECT * FROM " . $dbpfx . "prod_prov WHERE op_id = '$v' AND prod_prov_id = '" . $prov_id[$k] . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección Prod-Prov!");
		$cot = mysql_fetch_array($matr1);
		if($sbt[$k] != ($op_cantidad * $costo[$k]) && $sbt[$k] != 0) {
			$costo[$k] = round(($sbt[$k] / $op_cantidad), 6);
			$costo[$k] = $costo[$k] + 0.004999;
		}
		$costo[$k] = round($costo[$k], 2);
		$dato = ' Prov: ' . $cot['prod_prov_id'] . ' Costo: ' . $cot['prod_costo']; 
		if($cancela[$k] == '1') {
			$consulta = "DELETE FROM " . $dbpfx . "prod_prov WHERE op_id = '$v' AND prod_prov_id = '" . $prov_id[$k] . "'";
			$resultado = mysql_query($consulta) or die("ERROR: Fallo borrado de cotizaciones".$consulta);
			$archivo = '../logs/' . time() . '.ase';
			$myfile = file_put_contents($archivo, $consulta . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
			bitacora($orden_id, 'Se eliminó cotización para OP ' . $v . $dato, $dbpfx);
		} else {
			$sql_data = [
				'prod_costo' => $costo[$k],
				'dias_entrega' => $entrega[$k],
				'dias_credito' => $credito[$k],
				'prod_vencimiento' => date('Y-m-d', $vencimiento[$k]),
				'prod_origen' => $origen[$k],
				'prod_condicion' => $condicion[$k],
				'prod_costo_envio' => $costoenvio[$k]
			];
			$param = "op_id = '" . $v . "' AND prod_prov_id = '" . $prov_id[$k] . "'";
			ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'actualizar', $param);
			bitacora($orden_id, 'Se actualizó cotización para OP ' . $v . ' Antes ' . $dato, $dbpfx);
		}
		$name = $v;
	}
	redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id . '#' . $name);
}

elseif($accion==="vercotiza") {

	$funnum = 1115090;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol05']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol13']=='1') {
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
		echo '		<table cellpadding="2" cellspacing="0" border="1">'."\n";
		echo '			<tr class="cabeza_tabla"><td colspan="11">Captura de Cotizaciones para Cantidad: ' . $op['op_cantidad'] . '. Descripción: ' . $op['op_nombre'] . '.<br>del vehículo ' . $veh['completo'] . ' de la OT ' . $orden_id . '</td></tr>'."\n";
		echo '			<tr><td style="text-align:left;">' . $lang['Proveedor'] . '</td><td style="text-align:left;">' . $lang['FechaCotización'] . '</td><td style="text-align:left;">' . $lang['CostoUnitario'] . '</td><td style="text-align:left;">' . $lang['CostoTotal'] . '</td><td style="text-align:left;">' . $lang['DíasCrédito'] . '</td><td style="text-align:left;">' . $lang['DíasEntrega'] . '</td>';
		echo '<td>' . $lang['Origen'] . '</td><td>' . $lang['Condición'] . '</td><td>' . $lang['Vencimiento'] . '</td><td>' . $lang['CostoEnvio'] . '</td><td>' . $lang['MensajeVendedor'] . '</td>';
		echo '</tr>'."\n";
		$j = 0;
		while($prov3 = mysql_fetch_array($matr1)) {
			echo '			<tr><td>' . $provs[$prov3['prod_prov_id']]['nic'] . '
			<input type="hidden" name="op_id[' . $j . ']" value="' . $op_id . '" />
			<input type="hidden" name="prov_id[' . $j . ']" value="' . $prov3['prod_prov_id'] . '" />
			</td>';
			$sbt = $op['op_cantidad'] * $prov3['prod_costo'];
			echo '<td>' . date('d-m-Y', strtotime($prov3['fecha_cotizado'])) . '</td>';
			echo '<td>' . number_format($prov3['prod_costo'],2) . '</td>';
			echo '<td>' . number_format($sbt,2) . '</td>';
			echo '<td>' . $prov3['dias_credito'] . '</td>';
			echo '<td>' . $prov3['dias_entrega'] . '</td>';
			echo '<td style="text-align:center;">' . $prov3['prod_origen'] . '</td>';
			echo '<td style="text-align:center;">' . $prov3['prod_condicion'] . '</td>';
			echo '<td>' . date('Y-m-d', strtotime($prov3['prod_vencimiento'])) . '</td>';
			echo '<td style="text-align:center;">' . number_format($prov3['prod_costo_envio'],2) . '</td>';
			echo '<td>' . $prov3['prod_mensaje'] . '</td></tr>'."\n";
			$j++;
		}
		echo '			<tr><td colspan="11" style="text-align:left;"><div class="control"><a href="refacciones.php?accion=gestionar&orden_id=' . $orden_id . '#' . $op_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Gestión de Refacciones" title="Regresar a la Gestión de Refacciones"></a></div></td></tr>'."\n";
		echo '		</table>'."\n";
	} else {
		echo 'No hay cotizaciones disponibles para este producto';
	}
}

elseif($accion==='generacb') {
	
	$funnum = 1115095;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || $_SESSION['rol08']=='1') {
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
			<tr class="obscuro"><td style="text-align:left; width:50%;">Filtrar por Almacén: 
				<select name="almacen" size="1">
					<option value="">Seleccionar...</option>'."\n";
	foreach($nom_almacen as $k => $v) {
		echo '					<option value="' . $k . '"';
		if($almacen == $k) { echo ' selected="selected" ';}
		echo '>' . $v . '</option>'."\n";
	}											
	echo '				</select>
				<input name="Enviar" value="Enviar" type="submit"><br><input type="submit" name="limpiar" value="Restablecer Filtros">'."\n";
	echo '			</td><td style="text-align:right; width:50%;">'."\n";
	echo '&nbsp;<a href="refacciones.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Listado de Refacciones y Productos" title="Listado de Refacciones y Productos"></a>'."\n";
	echo '			</td></tr></table></form>'."\n";
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
		if(isset($codigo) && $codigo!='') { $preg .= "AND prod_codigo LIKE '%" . $codigo . "%' "; }
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
	if ($retorno == '1' || $_SESSION['rol08']=='1') {
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

elseif($accion==='flash') {
// ------ Genera el archivo XML para Quien Vende en modo flash
	foreach($nombre as $j => $w) {
		$xmlitem .= '			<Ref op_id="' . $j . '" op_cantidad="' . $cantidad[$j] . '" op_nombre="' . $w . '" op_codigo="' . $codigo[$j] . '" op_doc_id="' . $doc_id[$j] . '" op_estatus="10" s_tipo="flash"  precio="' . $precio[$j] . '" origen="' . $origen[$j] . '" condiciones="' . $condicion[$j] . '" mensaje="' . $mnsj[$j] . '" />'."\n";
	}
	if($xmlitem != '') {
		$veh = datosVehiculo($orden_id, $dbpfx);
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xml .= '	<Comprador instancia="' . $instancia . '" nick="' . $nick . '" >'."\n";
		$xml .= '		<Solicitud tiempo="' . time() . '">10</Solicitud>'."\n";
		$xml .= '		<OT orden_id="' . $orden_id . '" marca="' . $veh['marca'] . '" tipo="' . $veh['tipo'] . '" color="' . $veh['color'] . '" vin="' . $veh['serie'] . '" modelo="' . $veh['modelo'] .'" rfc="' . $qvprovs . '">'."\n";
		$xml .= $xmlitem;
		$xml .= '		</OT>'."\n";
		$xml .= '	</Comprador>'."\n";
		$mtime = substr(microtime(), (strlen(microtime())-3), 3);
		$xmlnom = $nick . '-' . date('YmdHis') . $mtime . '.xml';
		file_put_contents("../qv-salida/".$xmlnom, $xml);
	}
	redirigir('refacciones.php?accion=gestionar&orden_id=' . $orden_id . '&grupo=' . $grupo);
}

?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
