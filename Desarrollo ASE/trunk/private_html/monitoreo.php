<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/monitoreo.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
} else {
	if($export != 1) {
		include('parciales/encabezado.php');
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php'); 
		echo '		<div id="principal">'."\n";
	}
}

	$funnum = 1075000;
	//  ----------------  obtener nombres de usuarios   ------------------- 

	$consulta = "SELECT nombre, apellidos, usuario FROM " . $dbpfx . "usuarios WHERE acceso = '0'";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo selección de asesores!");
	while ($ases = mysql_fetch_array($arreglo)) {
		$usuario[$ases['usuario']] = $ases['nombre'] . ' ' . $ases['apellidos'];
	}

	//  ----------------  nombres de asesores   ------------------- 
	//  ----------------  obtener nombres de aseguradoras   ------------------- 

  	$consulta = "SELECT aseguradora_id, aseguradora_logo, aseguradora_nic FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_nic";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
	while($aseg = mysql_fetch_array($arreglo)){
		
		$ase[$aseg['aseguradora_id']][0] = $aseg['aseguradora_logo'];
		$ase[$aseg['aseguradora_id']][1] = $aseg['aseguradora_nic'];
		
	}
	$ase[0][0] = 'particular/logo-particular.png';
	$ase[0][1] = 'Particular';

	//  ------------------------------------------------------------------- 

	$codigos = [
		'rol04' => 'JEFE_DE_TALLER',
		'rol05' => $lang['VALUADORES'],
		'rol06' => $lang['ASESORES'],
		'rol07' => $lang['SUPERVISORES'],
		'rol08' => $lang['ALMACEN'],
		'rol09' => $lang['OPERADORES'],
		'rol10' => $lang['LAVADORES'],
		'rol11' => $lang['CALIDAD']
	];

	if($export == 1){ // ---- Hoja de calculo ---- 
			
		
		
	}
	else{ // ---- HTML ----
		
		require_once("calendar/tc_calendar.php");

		if($feini == '' && $filtro == '5'){
			$feini = date('Y-m-d 00:00:00');
			$fefin = date('Y-m-d 23:59:59');
		}elseif($feini == '') {
			$feini = date('Y-m-01 00:00:00');
			$fefin = date('Y-m-t 23:59:59');
		}else{
			$feini = date('Y-m-d 00:00:00', strtotime($feini));
			$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
		}
	}


	if($export == 1){ // ---- Hoja de calculo ---- 
		
	}
	else{ // ---- HTML ----

		echo '			
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-sm-12">
			<div class="content-box-header">
				<div class="panel-title">
  					<h2>MONITOREO <img src="idiomas/' . $idioma . '/imagenes/buscar.png" border="0" width="20" height="20"></h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row"> <!- Formulario -->
		<div class="col-sm-12">
			<div class="col-sm-3 padding">
				<form action="monitoreo.php?accion=listar" method="post" enctype="multipart/form-data" name="filtro">
					<big><b>Filtra por:</b></big><br>
					<select name="filtro" class="form-control" size="1" onchange="document.filtro.submit()";>
						<option value="">' . $lang['Todos'] .'</option>
							<option value="1"';

		if($filtro == '1') { echo ' selected '; }
		echo '>' . $lang['cFPE'] . '</option>'."\n";
		echo '							
							<option value="2"';
		if($filtro == '2') { echo ' selected '; }
		echo '>' . $lang['sFPE'] . '</option>'."\n";
		echo '							
							<option value="3"';
		if($filtro == '3') { echo ' selected '; }
		echo '>' . $lang['En Taller'] . '</option>'."\n";
		echo '							
							<option value="4"';
		if($filtro == '4') { echo ' selected '; }
		echo '>' . $lang['En Tránsito'] . '</option>'."\n";
		echo '							
							<option value="5"';
		if($filtro == '5') { echo ' selected '; }
		echo '>' . $lang['ComSeg'] . '</option>'."\n";
		echo '						
					</select>
					<br>
					<input class="btn btn-success" type="submit" value="Enviar" />
			</div>'."\n";

		if($filtro == 1 || $filtro == 5) {
			echo '			
			<div class="col-sm-3 padding">
				<big><b>Fecha de Inicio:</b></big><br>';
		
			// --- instantiate class and set properties
			$myCalendar = new tc_calendar("feini", true);
			$myCalendar->setPath("calendar/");
			$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
			$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), 	date("Y", strtotime($feini)));
			//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
			//$myCalendar->disabledDay("sun");
			$myCalendar->setYearInterval(2011, 2020);
			$myCalendar->setAutoHide(true, 5000);
			// --- output the calendar
			$myCalendar->writeScript();

			echo '
			</div>
			<div class="col-sm-3 padding">
				<big><b>Fecha de Fin:</b></big><br>';

			// --- instantiate class and set properties
			$myCalendar = new tc_calendar("fefin", true);
			$myCalendar->setPath("calendar/");
			$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
			$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), 	date("Y", strtotime($fefin)));
			//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
			//$myCalendar->disabledDay("sun");
			$myCalendar->setYearInterval(2011, 2020);
			$myCalendar->setAutoHide(true, 5000);
			// --- output the calendar
			$myCalendar->writeScript();
			echo '
			</div>'."\n";
		}

		echo '
			
			<div class="col-sm-3 padding">
				<big><b>Filtra por M.O y Ref.:</b></big><br>
					<select name="mano_obra" class="form-control" size="1" onchange="document.filtro.submit()";>
					mano_obra
						<option value="" disabled selected>Seleccione</option>
						
						<option value="1"';
						if($mano_obra == '1') { echo ' selected '; }
						echo '>Ordenes sin refacciones</option>'."\n";
	
		echo '
						<option value="0"';
						if($mano_obra == '0') { echo ' selected '; }
						echo '>Todas las ordenes de trabajo</option>
						
						<option value="2"';
						if($mano_obra == '2') { echo ' selected '; }
						echo '>Ordenes con refacciones completas</option>'."\n";
		
		echo '
					</select>	
			</div>
		
			</form>
			<div class="col-sm-2 padding">
				<a href="monitoreo.php?accion=listar&feini=' . $feini . '&fefin=' . $fefin . '&filtro=' . $filtro . '&mano_obra=' . $mano_obra . '&export=1">
					<img src="idiomas/' . $idioma . '/imagenes/hoja-calculo.png" alt="Exportar" border="0">
				</a>
			</div>
		</div>
	</div>'."\n";
		
	}

	if($filtro == 5) {
		$preg1 = "SELECT o.orden_id, o.orden_estatus, o.orden_alerta, o.orden_ubicacion, o.orden_categoria, o.orden_asesor_id, o.orden_ref_pendientes, o.orden_fecha_recepcion, o.orden_fecha_ultimo_movimiento, o.orden_fecha_promesa_de_entrega, o.orden_fecha_compromiso_de_taller, s.sub_aseguradora, s.sub_reporte FROM " . $dbpfx . "ordenes o, " . $dbpfx . "subordenes s, " . $dbpfx . "comentarios c WHERE o.orden_estatus < '90' AND o.orden_estatus != '16' AND s.sub_estatus < '190' AND o.orden_id = s.orden_id AND o.orden_id = c.orden_id AND c.fecha_com >= '" . $feini . "' AND c.fecha_com <= '" . $fefin . "' AND c.interno = '2'";
	} else {
		$preg1 = "SELECT o.orden_id, o.orden_estatus, o.orden_alerta, o.orden_ubicacion, o.orden_categoria, o.orden_asesor_id, o.orden_ref_pendientes, o.orden_fecha_recepcion, o.orden_fecha_ultimo_movimiento, o.orden_fecha_promesa_de_entrega, o.orden_fecha_compromiso_de_taller, s.sub_aseguradora, s.sub_reporte FROM " . $dbpfx . "ordenes o, " . $dbpfx . "subordenes s WHERE ((o.orden_estatus < '90' AND o.orden_estatus != '16' AND s.sub_estatus < '190' AND o.orden_id = s.orden_id) OR o.orden_estatus = '1' OR o.orden_estatus = '17') ";
		if($filtro == 1) {
			$preg1 .= " AND o.orden_fecha_promesa_de_entrega >= '" . $feini . "' AND o.orden_fecha_promesa_de_entrega <= '" . $fefin . "' ";
		} elseif($filtro == 2) {
			$preg1 .= " AND o.orden_fecha_promesa_de_entrega IS NULL ";
		} elseif($filtro == 3) {
			$preg1 .= " AND o.orden_ubicacion != 'Transito' ";
		} elseif($filtro == 4) {
			$preg1 .= " AND o.orden_ubicacion = 'Transito' ";
		}
	}

	$preg1 .= "GROUP BY o.orden_id ";

	if($ordenar == 'ot') {
		$preg1 .= "ORDER BY o.orden_id";		
	} elseif($ordenar == 'asesor') {
		$preg1 .= "ORDER BY o.orden_asesor_id,o.orden_fecha_promesa_de_entrega,o.orden_id";		
	} elseif($ordenar == 'cliente') {
		$preg1 .= "ORDER BY s.sub_aseguradora,o.orden_id";		
	} elseif($ordenar == 'reporte') {
		$preg1 .= "ORDER BY s.sub_reporte,o.orden_id";		
	} elseif($ordenar == 'lugar') {
		$preg1 .= "ORDER BY o.orden_ubicacion,o.orden_id";		
	} elseif($ordenar == 'estatus') {
		$preg1 .= "ORDER BY o.orden_estatus,o.orden_id";		
	} elseif($ordenar == 'asesor') {
		$preg1 .= "ORDER BY o.orden_asesor_id,o.orden_fecha_promesa_de_entrega,o.orden_id";		
	} else {
		$preg1 .= "ORDER BY o.orden_fecha_promesa_de_entrega,o.orden_id";
	}
	
	//echo $preg1;

	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de lapso! " . $preg1);
	$filas = mysql_num_rows($matr1);	
	if($fcompcr == 1) { $campos = 16; } else { $campos = 15; }

	if($export == 1){ // ---- Hoja de calculo ---- 
		
		// -------------------   Creación de Archivo Excel   ---------------------------
		$celda = 'A1';
		$titulo = 'Reporte de Monitoreo ' . $miprov['prov_razon_social'] . ' exportado: ' . date('l jS \of F Y h:i:s A');
			
		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle("Reporte de mONITOREO")
					->setKeywords("AUTOSHOP EASY");

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($celda, $titulo);

		// ------ ENCABEZADOS ---
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A4", "OT")
					->setCellValue("B4", $lang['Vehículo'])
					->setCellValue("C4", $lang['Num Serie'])
					->setCellValue("D4", $lang['Cliente'])
					->setCellValue("E4", $lang['Siniestro'])
					->setCellValue("F4", "Categoria de Servicio")
					->setCellValue("G4", $lang['Días para'] . ' ' . $lang['Entrega']);
		
		if($fcompcr == 1){ 

			// ------ ENCABEZADOS ---
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue("H4", $lang['Comp'] . ' ' . $lang['Taller']);
		}
		
		if($lista_operadores == 1){
			// ------ ENCABEZADOS ---
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue("P4", "Hojalatero(s)")
						->setCellValue("P4", "Pintor(es)");
		} 
	
		$z= 5;

	}
	else{ // ---- HTML ----
		echo '		
	<div class="row">
		<div class="col-sm-12">
		<div class="col-sm-12">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
					<tr>
						<th style="text-align:left;"colspan="' . $campos . '">
							<big><b>'. $lang['Reporte de Seguimiento en Proceso'].'</b></big>
						</th>
					</tr>
					<tr>
						<th>
							<a href="monitoreo.php?accion=listar&ordenar=ot&filtro=' . $filtro . '&feini=' . $feini . '&fefin=' . $fefin . '">OT</a>
						</th>
						<th>
							' . $lang['Vehículo'] . '
						</th>
						<th>
							' . $lang['Placas'] . '
						</th>
						<th>
							<a href="monitoreo.php?accion=listar&ordenar=cliente&filtro=' . $filtro . '&feini=' . $feini . '&fefin=' . $fefin . '">' . $lang['Cliente'] . '</a>
						</th>
						<th>
							<a href="monitoreo.php?accion=listar&ordenar=reporte&filtro=' . $filtro . '&feini=' . $feini . '&fefin=' . $fefin . '">' . $lang['Siniestro'] . '
						</th>
						<th>
							Categoria de Servicio
						</th>
						<th>
							<a href="monitoreo.php?accion=listar&ordenar=entrega&filtro=' . $filtro . '&feini=' . $feini . '&fefin=' . $fefin . '">' . $lang['Días para'] . ' ' . $lang['Entrega'] . '</a>
						</th>'."\n";

		if($fcompcr == 1){ 
			echo '
						<th>
							' . $lang['Comp'] . ' ' . $lang['Taller'] . '
						</th>'."\n"; 
		}

		echo '
						<th>
							' . $lang['Alerta'] . ' Hrs:Min
						</th>
						<th>
							<a href="monitoreo.php?accion=listar&ordenar=estatus&filtro=' . $filtro . '&feini=' . $feini . '&fefin=' . $fefin . '">' . $lang['Estatus'] . '</a>
						</th>
						<th>
							<a href="monitoreo.php?accion=listar&ordenar=asesor&filtro=' . $filtro . '&feini=' . $feini . '&fefin=' . $fefin . '">'. $lang['Asesor'] . '</a>
						</th>
						<th>
							<a href="monitoreo.php?accion=listar&ordenar=lugar&filtro=' . $filtro . '&feini=' . $feini . '&fefin=' . $fefin . '">' . $lang['Área'] .  '</a>
						</th>
						<th>
							' . $lang['Fecha Ing'] . '
						</th>
						<th>
							' . $lang['Comentario'] . '
						</th>
						<th>
							' . $lang['Usuario'] . '
						</th>
						<th>
							' . $lang['Hist'] . '
						</th>' . "\n";
						
					if($lista_operadores == 1){
						
						echo '
						<th>
							<big>Hojalatero(s)</big>
						</th>
						<th>
							<big>Pintor(es)</big>
						</th>' . "\n";

					}
					
					echo '	
					</tr>'."\n";

	}

	$fondo = 'claro';
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	while($ord = mysql_fetch_array($matr1)) {
		
		if($mano_obra == 1){ //--- Si el filtro de solo mano de obra esta activado, se debe de verificar ---
			
			$mostrar = 'si';
			$preg_tareas = "SELECT sub_orden_id, sub_estatus FROM " . $dbpfx . "subordenes WHERE orden_id = '" .$ord['orden_id'] . "' AND sub_estatus < '190'";
			$matr_tareas = mysql_query($preg_tareas) or die("ERROR: " . $preg_tareas);
			
			while($tareas = mysql_fetch_array($matr_tareas)) {
				
				if(($tareas['sub_estatus'] >= '104' && $tareas['sub_estatus'] <= '111') || ($tareas['sub_estatus'] == '128' || $tareas['sub_estatus'] == '129' || $tareas['sub_estatus'] == '120')){
					
					// ---- Consultar si la tarea tiene refacciones ----
					$preg_refacciones = "SELECT op_id FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $tareas['sub_orden_id'] . "' AND op_tangible = '1'";
					$matr_refacciones = mysql_query($preg_refacciones) or die("ERROR: " . $preg_refacciones);
					$refacciones = mysql_num_rows($matr_refacciones);

					if($refacciones > 0){
						$mostrar = 'no';
					}
					
				} else{
					$mostrar = 'no';
				}

			}
			
		}
		elseif($mano_obra == 2){ // --- Ordenes con refacciones completas
			
			//echo 'Orden----> ' . $ord['orden_id'] . '<br>';
			
			$mostrar = 'no';
			$preg_tareas = "SELECT sub_orden_id, sub_estatus, sub_refacciones_recibidas FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' AND (sub_area = '1' OR sub_area = '6')";
			$matr_tareas = mysql_query($preg_tareas) or die("ERROR: " . $preg_tareas);
			$refacciones_pendientes = 0;
			$ops_pendientes_de_recibir = 0;
			unset($hojalateros);
			unset($pintores);
			
			while($tareas = mysql_fetch_array($matr_tareas)) {
				
				//echo 'estatus tarea ' . $tareas['sub_estatus'] . '<br>';
				
				
				if(($tareas['sub_estatus'] >= '104' && $tareas['sub_estatus'] <= '111') || ($tareas['sub_estatus'] == '128' || $tareas['sub_estatus'] == '129' || $tareas['sub_estatus'] == '120')){
					
					//echo 'tarea ' . $tareas['sub_orden_id'] . '<br>';
					
					if($tareas['sub_refacciones_recibidas'] == 1){ // --- refacciones pendientes ---						
						$refacciones_pendientes = 1; 
						//echo 'Refacciones pendientes<br>';
					}
					
					//echo 'Tarea con variable $refacciones_pendientes = ' . $refacciones_pendientes . '<br>';
					// --- Consultar que halla refacciones y todas esten recibidas ---
					$preg_ops = "SELECT op_tangible, op_ok FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $tareas['sub_orden_id'] . "' AND op_tangible = 1 AND op_pres IS NULL";
					$matr_ops = mysql_query($preg_ops) or die("ERROR: " . $preg_ops);
					$existen_refacciones = mysql_num_rows($matr_ops);
					$revision = 0;

					if($existen_refacciones > 0){ // ---- Si hay refacciones se recorren para revisar que esten recibidas ---
						//echo 'se revisan los ops<br>';
						$revision = 1;
						while($ops = mysql_fetch_array($matr_ops)){
							if($ops['op_ok'] == 0){ // --- item pendiente de recibir ---
								$ops_pendientes_de_recibir = 1;
							}
						}
					}
					
					if($revision == 1){
						if($refacciones_pendientes == 0){
							$mostrar = 'si';
							$revision = 0;
						} 	
					}
					
					//echo 'pendientes: ' . $ops_pendientes_de_recibir . '<br>';
					/*
					if($ops_pendientes_de_recibir == 0){
						$mostrar = 'si';
						echo 'cambio a mostrar si <br>';
					} else{
						$mostrar = 'no';
					}
					*/
					
				} else{
					$mostrar = 'no';
				}
				
				//echo 'Tarea termina con mostrar en = ' . $mostrar . '<br>';

			}
			
			//echo 'Orden ' . $ord['orden_id'] . ' como valor $mostrar = ' . $mostrar . '<br>';
			
		}
		
		$preg2 = "SELECT sub_reporte, sub_aseguradora, sub_area, sub_operador FROM " . $dbpfx . "subordenes WHERE orden_id = '" .	$ord['orden_id'] . "' AND sub_estatus < '190' ";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de subordenes!".$preg2);
		
		unset($hojalateros);
		unset($pintores);
			
		while($tareas = mysql_fetch_array($matr2)){
			// --- Agrupar operadores ---
			if($tareas['sub_area'] == 6){ // --- Hojalatería ---
				$hojalateros[$tareas['sub_operador']] = 1;
			}
			if($tareas['sub_area'] == 7){ // --- Pintura ---
				$pintores[$tareas['sub_operador']] = 1;
			}
		}
		
		// ---- Recorrer Hojalateros ---
		$texto_hojalateros = '';
		foreach($hojalateros as $key => $val){
			// --- Consultar nombre del operador en curso ---
			$preg_operador = " SELECT nombre FROM " . $dbpfx . "usuarios WHERE usuario = '" . $key . "'";
			$matr_operador = mysql_query($preg_operador) or die ($preg_operador);
			$operador = mysql_fetch_assoc($matr_operador);
			//echo $preg_operador;
			$texto_hojalateros .= $operador['nombre'] . ', ';
		}
		// ---- Recorrer Pintores ---
		$texto_pintores = '';
		foreach($pintores as $key => $val){
			// --- Consultar nombre del operador en curso ---
			$preg_operador = " SELECT nombre FROM " . $dbpfx . "usuarios WHERE usuario = '" . $key . "'";
			$matr_operador = mysql_query($preg_operador) or die ($preg_operador);
			$operador = mysql_fetch_assoc($matr_operador);
			//echo $preg_operador;
			$texto_pintores .= $operador['nombre'] . ', ';
		}
		
		if($mano_obra == 1 || $mano_obra == 2){

		}
		else{
			$mostrar = 'si';
		}
		
		if($mostrar == 'si'){
		
			$dias = intval((strtotime($ord['orden_fecha_promesa_de_entrega']) - time()) / 86400);
			if($dias < -365 ) { $dias = 'Sin fecha'; }

			elseif($dias < 3 && $dias > 0){
				if($export == 1){ // ---- Hoja de calculo ----

				}
				else{ // ---- HTML ----
					$dias = '<span class="warning">' . $dias . '</span>';
				}
			}
			elseif($dias <= 0 ){
				if($export == 1){ // ---- Hoja de calculo ----

				}
				else{ // ---- HTML ----
					$dias = '<span class="danger">'.$dias.'</span>';
				}
			}
			else{
				if($export == 1){ // ---- Hoja de calculo ----

				}
				else{ // ---- HTML ----
					$dias = '<span class="success">'.$dias.'</span>';
				}

			}
			$preg2 = "SELECT * FROM " . $dbpfx . "comentarios WHERE orden_id = '" . $ord['orden_id'] . "'";
			if($solocomseg == '1') { $preg2 .= " AND interno = '2' "; }  
			$preg2 .= " ORDER BY bit_id DESC";
	//		echo $preg2;
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Comentario!");
			$com = mysql_fetch_array($matr2);
			$coment = mysql_num_rows($matr2);
			$vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);
			$tiempo = time() - strtotime($ord['orden_fecha_ultimo_movimiento']);
			$horas = intval($tiempo / 3600);
			$minutos = intval(($tiempo - ($horas * 3600))/60);
			if($minutos==0) {$minutos='00';}
			$programadas = $horas . ':' . $minutos;
	// Obtener estatus de OT en proceso de reparación
			$reciente = 0; $currarea = 0; $aseg = array(); $repo = array();
			$preg3 = "SELECT sub_orden_id, sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" .$ord['orden_id'] . "' AND sub_estatus < '190'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de subordenes!");
			$fila3 = mysql_num_rows($matr3);
			if($fila3 > 0) {
				while($sub = mysql_fetch_array($matr3)) {
	//				echo 'Ubicando a ' . $ord['orden_id'] . '<br>';
					$preg4 = "SELECT * FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" .$sub['sub_orden_id'] . "' ORDER BY seg_id DESC LIMIT 1";
					$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de seguimientos!");
					$seg = mysql_fetch_array($matr4);
	//				print_r($seg);
	//				echo ' tiempo ' . strtotime($seg['seg_hora_registro']) . '<br>';
					if(strtotime($seg['seg_hora_registro']) > $reciente) {
						$currarea = $seg['sub_area']; 
						$reciente = strtotime($seg['seg_hora_registro']);
	//					echo $reciente . ' -> ' . $currarea . '<br>';
					}
					$aseg[$sub['sub_aseguradora']] = 1;
					$repo[$sub['sub_reporte']] = 1;
				}
			}

			if($export == 1){ // ---- Hoja de calculo ---- 

				// --- Celdas a grabar ----
				$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
				$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
				$kkk = 'K'.$z; $l = 'L'.$z; $m = 'M'.$z; $n = 'N'.$z; $o = 'O'.$z;
				$p = 'P'.$z; $q = 'Q'.$z;

				$info_vehiculo = strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']);

				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($a, $ord['orden_id'])
							->setCellValue($b, $info_vehiculo)
							->setCellValue($c, $vehiculo['placas']);

			}
			else{ // ---- HTML ----

				echo '				
						<tr class="' . $fondo . '">
							<td>
								<a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" style=" display:block;">' . $ord['orden_id'] . '</a>
							</td>
							<td style="text-align:left;">
								' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']) . '
							</td>
							<td style="padding-left:10px; padding-right:10px;">
								' . $vehiculo['placas'] . '
							</td>
							<td style="text-align:left;">'."\n";
			}

			$info_ase = '';

			foreach($aseg as $asegu => $v) {

				if($export == 1){ // ---- Hoja de calculo ---- 
					$info_ase .= ' ' . $ase[$asegu][1];
				}
				else{ // ---- HTML ----
					echo '
								' . $ase[$asegu][1] . ' <img src="' . $ase[$asegu][0] . '" alt="" height="16" > ';
				}

			}

			if($export == 1){ // ---- Hoja de calculo ---- 

				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($d, $info_ase);

			}
			else{ // ---- HTML ----

				echo '
							</td>
							<td>'."\n";
			}

			$info_reporte = '';

			foreach($repo as $reporte => $v) {
				if($reporte != '0' && $reporte != ''){ 

					if($export == 1){ // ---- Hoja de calculo ---- 
						$info_reporte .= ' ' . $reporte;
					}
					else{ // ---- HTML ----
						echo '
								' . $reporte . ' ';
					}
				}
			}

			if($export == 1){ // ---- Hoja de calculo ---- 

				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($e, $info_reporte)
							->setCellValue($f, constant('CATEGORIA_DE_REPARACION_' . $ord['orden_categoria']))
							->setCellValue($g, $dias);

			}
			else{ // ---- HTML ----

				echo '
							</td>
							<td>
								' . constant('CATEGORIA_DE_REPARACION_' . $ord['orden_categoria']) . '
							</td>
							<td>
								' . $dias . '
							</td>'."\n";
			}

			if($fcompcr == 1) {

				if($export == 1){ // ---- Hoja de calculo ---- 

					if(!is_null($ord['orden_fecha_compromiso_de_taller'])){
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($h, date('Y-m-d', strtotime($ord['orden_fecha_compromiso_de_taller'])));
					}

				}
				else{ // ---- HTML ----	
					echo '
							<td>'."\n";

					if(!is_null($ord['orden_fecha_compromiso_de_taller'])){ 
						echo '
								' . date('j-M', strtotime($ord['orden_fecha_compromiso_de_taller']));
					}

					echo '
							</td>'."\n";
				}

			}

			if($export == 1){ // ---- Hoja de calculo ---- 

				// --- cambiar el formato de la celda tipo fecha/date ---
				$objPHPExcel->getActiveSheet()
							->getStyle($i)
							->getAlignment()
							->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($i, $programadas);

			}	
			else{ // ---- HTML ----	

				echo '
							<td>
								' . constant('ALARMA_' . $ord['orden_alerta']) . ' ' . $programadas . '
							</td>
							<td style="text-align:left;">'."\n";
			}

			$estatus_general = '';

			if($ord['orden_ubicacion'] == 'Transito' && $est_trans == '1'){

				if($export == 1){ // ---- Hoja de calculo ---- 

					$estatus_general .= constant('ORDEN_ESTATUS_T_' . $ord['orden_estatus']);

				}	
				else{ // ---- HTML ----	
					echo '
								' . constant('ORDEN_ESTATUS_T_' . $ord['orden_estatus']);
				}

			} else {

				if($export == 1){ // ---- Hoja de calculo ---- 

					$estatus_general .= constant('ORDEN_ESTATUS_' . $ord['orden_estatus']);

				}	
				else{ // ---- HTML ----	

					echo constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) ;

				}
			}
			if($ord['orden_ref_pendientes'] == '2') {

				if($export == 1){ // ---- Hoja de calculo ---- 

					$estatus_general .= ' ' . $lang['Estructurales Pendientes'];

				}	
				else{ // ---- HTML ----	
					echo '
								<br>'. $lang['Estructurales Pendientes'];
				}

			} elseif($ord['orden_ref_pendientes'] == '1') {

				if($export == 1){ // ---- Hoja de calculo ---- 

					$estatus_general .= ' ' . $lang['No estructurales Pendientes'];

				}	
				else{ // ---- HTML ----	
					echo '
								<br>'. $lang['No estructurales Pendientes'];
				}

			} else {
				//echo '<br>Refacciones Completas';
			}

			if($export == 1){ // ---- Hoja de calculo ---- 


				$ubicacion = $ord['orden_ubicacion'];

				if($currarea > 0){

					$ubicacion .= ' ' . constant('NOMBRE_AREA_' . $currarea); 
				}

				$fech_recp = date('Y-m-d', strtotime($ord['orden_fecha_recepcion']));
				$fech_recp = PHPExcel_Shared_Date::PHPToExcel( strtotime($fech_recp));

				// --- cambiar el formato de la celda tipo fecha/date ---
				$objPHPExcel->getActiveSheet()
							->getStyle($m)
							->getNumberFormat()
							->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);

				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($j, $estatus_general)
							->setCellValue($kkk, $usuario[$ord['orden_asesor_id']])
							->setCellValue($l, $ubicacion)
							->setCellValue($m, $fech_recp);

			}	
			else{ // ---- HTML ----	

				echo '
							</td>
							<td style="text-align:left;">
								' . $usuario[$ord['orden_asesor_id']] . '
							</td>
							<td style="text-align:left;">
								' . $ord['orden_ubicacion'] . ''."\n";

				if($currarea > 0){

					echo '
								' . ' ' . constant('NOMBRE_AREA_' . $currarea); 
				}

				echo '
							</td>
							<td>
								' . date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])) . '
							</td>
							<td style="text-align:left;">'."\n";
			}

			if($export == 1){ // ---- Hoja de calculo ---- 

				$comentario = '';
				if(!is_null($com['fecha_com'])){
					$comentario .= date('Y-m-d', strtotime($com['fecha_com']));
				}
				$comentario .= ' ' . $com['comentario'];

				if($coment > 1){
					$comentario .= ' Hay ' .  $coment . $lang['comentarios de seguimiento'];
				}

				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($n, $comentario)
							->setCellValue($o, $usuario[$com['usuario']]);
				
				if($lista_operadores == 1){
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($p, $texto_hojalateros)
								->setCellValue($q, $texto_pintores);
				}
				
				/*
				$objPHPExcel->getActiveSheet()
							->getColumnDimension($n)
							->setAutoSize(true);
				*/
				$z++;

			}	
			else{ // ---- HTML ----	

				if(!is_null($com['fecha_com'])){
					echo '
								' . date('Y-m-d', strtotime($com['fecha_com'])) . ' '; 
				}
				echo $com['comentario'];
				if($coment > 1) {
					echo '
								<br><span style="font-weight:bold;"> Hay ' . $coment . $lang['comentarios de seguimiento'].'</span>'."\n";
				}
				echo '
							</td>
							<td style="text-align:left;">
								' . $usuario[$com['usuario']] . '
							</td>
							<td align="center">
								<a href="comentarios.php?accion=mostrar&orden_id=' . $ord['orden_id'];

				if($solocomseg == '1') {
					echo '&tiposeg=2';
				} 
				echo '" onclick="window.open(this.href,';
				echo "'Comentarios','left=20,top=20,width=550,height=650,toolbar=0,resizable=0,scrollbars=1');";
				echo ' return false;" >'. $lang['Seg'].'</a>
							</td>'."\n";
				
				if($lista_operadores == 1){
					echo '
						<td>
							' . $texto_hojalateros . '
						</td>
						<td>
							' . $texto_pintores . '
						</td>'."\n";
				}
				
				echo ' 
						</tr>'."\n";

				$j++;
				if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }

			}
			
		}
		
	}

	if($export == 1){ // ---- Hoja de calculo ---- 
	
		//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Reporte-Monitoreo.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}	
	else{ // ---- HTML ----	
		
	}

	echo '			
				</table>
			</div>
		</div>
		</div>
	</div>
</div>'."\n";
	
?>
</div>		
</div>		
<?php include('parciales/pie.php'); 
/* Archivo index.php */
/* AutoShop Easy */
	
