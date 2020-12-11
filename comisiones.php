<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/comisiones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

include('parciales/encabezado.php');
echo '	<div id="body">' ."\n";
include('parciales/menu_inicio.php');
echo '		<div id="principal">' ."\n";

if($accion === "generar") {
	if (validaAcceso('1165000', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol02']=='1'))) {
		// Acceso autorizado
	} else {
		 redirigir('index.php?mensaje=El acceso a esta función es sólo para gerentes');
	}

	echo '			<div class="page-content content-box">'."\n";
	echo '				<div class="row"><div class="col-md-12"><div class="content-box-header"><div class="panel-title">
								<h2>' . $lang['GENERAR COMISIONES'] . ' <small>' . $lang['Configura el pago.'] . '</small></h2> 
				</div></div></div></div>
				<br>'."\n";

// ----------------  Obtener las comisiones disponibles -------------------
	$preg1 = "SELECT * FROM " . $dbpfx . "comisiones_tipo";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de comisiones! " . $preg1);

	echo '				<form action="comisiones.php?accion=generar" method="post" enctype="multipart/form-data" name="seltipocom">
				<div class="row"><div class="col-sm-8">
					<legend class="legend">Selecciona la comisión que deseas calcular</legend>
					<select name="comision" size="0" onchange="document.seltipocom.submit()";>
						<option value="0">Selecciona una comisión...</option>'."\n";
	$com_desc[0] = 'Usa el selector arriba de este mensaje para mostrar y utilizar alguna de las comisiones disponibles.';
	while ($com = mysql_fetch_array($matr1)) {
		echo '						<option value="' . $com['com_id'] . '"';
		if($comision == $com['com_id']) {
			$com_desc[$com['com_id']] = $com['com_desc'];
			$com_archivo[$com['com_id']] = $com['com_archivo'];
			echo " selected ";
		}
		echo '>' . utf8_encode($com['com_nombre']) . '</option>'."\n";
	}
	echo '					</select>'."\n";
	echo '				</div></div>'."\n";

	echo '				<div class="row">
					<div class="col-sm-8">
						<p style="font-size:1.2em;"><strong>Descripción de la comisión: </strong>' . utf8_encode($com_desc[$comision]) . '</p>'."\n";
	echo '				</div></div></form>'."\n";
	if(file_exists($com_archivo[$comision])) {
		include($com_archivo[$comision]);
	}
	echo '				<div class="row"><div class="col-sm-8 izq">
						<p></p>'."\n";
//						<a href="reportes.php"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo"></a>
	echo '					</div>
				</div>'."\n";
}

elseif($accion === "procesar") {
	if (validaAcceso('1165000', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol02']=='1'))) {
		// Acceso autorizado
	} else {
		redirigir('index.php?mensaje=El acceso a esta función es sólo para gerentes');
	}

// --- Obtener datos de la comision a procesar ---
	$preg1 = "SELECT * FROM " . $dbpfx . "comisiones_tipo WHERE com_id = '" . $comision . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de comisiones! " . $preg1);
	$com = mysql_fetch_array($matr1);

	if(file_exists($com['com_archivo'])) {
		include($com['com_archivo']);
	}

	redirigir('comisiones.php?accion=consultar');
}

elseif($accion === "consultar"){
	if (validaAcceso('1165005', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol02']=='1'))) {
		// Acceso autorizado
	} else {
		redirigir('index.php?mensaje=El acceso a esta función es sólo para gerentes');
	}
	
	// --- Info para tc_calendar.php ---
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
// --- Se obtirnen los nombres y apellidos de todos los usuarios ---
	$pregase = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE acceso = '0' ORDER BY nombre, apellidos ";
	$matrase = mysql_query($pregase) or die("ERROR: Fallo selección de Asesores! " . $pregase);
	while($usu = mysql_fetch_array($matrase)) {
		$usuario[$usu['usuario']] = $usu['nombre'] . ' ' . $usu['apellidos'];
	}

// --- Se obtienen los nombres de los tipos de comisiones --
	$pregase = "SELECT com_id, com_nombre FROM " . $dbpfx . "comisiones_tipo";
	$matrase = mysql_query($pregase) or die("ERROR: Fallo selección de tipos de comisiones! 118 " . $pregase);
	while($usu = mysql_fetch_array($matrase)) {
		$comision[$usu['com_id']] = utf8_encode($usu['com_nombre']);
	}

	require_once("calendar/tc_calendar.php");

	// --- Comienza cuerpo de la pag. ---
	echo '
			<div class="page-content content-box">'."\n";
	echo '				<form action="comisiones.php?accion=consultar" method="post" enctype="multipart/form-data" name="filtrorep">
				<input type="hidden" name="feini" value="' . $feini . '" />
				<input type="hidden" name="fefin" value="' . $fefin . '" />
				<input type="hidden" name="usu_filt" value="' . $usu_filt . '" />
				<input type="hidden" name="estatus" value="' . $estatus . '" />
				<div class="row"><div class="col-sm-12"><div class="content-box-header"><div class="panel-title">
							<h2>' . $lang['CONSULTA DE COMISIONES'] . ' <small>' . $lang['Del:'] . ' ' . date('Y-m-d', strtotime($feini)) . ' ' . $lang['Al:'] . ' ' . date('Y-m-d', strtotime($fefin)) . '</small></h2>
				</div></div></div></div>'."\n";

// --- Filros de búsqueda ---
	echo '				<div class="row"><div class="col-sm-12 panel-body">
					<div class="col-sm-3">
						<STRONG>' . $lang['Fecha de inicio:'] . '</STRONG><br>'."\n";
						//instantiate class and set properties
						$myCalendar = new tc_calendar("feini", true);
						$myCalendar->setPath("calendar/");
						$myCalendar->setIcon("calendar/images/iconCalendar.gif");
						$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)),date("Y", strtotime($feini)));
						$myCalendar->setYearInterval(2011, 2020);
						$myCalendar->setAutoHide(true, 5000);
						//output the calendar
						$myCalendar->writeScript();
	echo '					</div>
					<div class="col-sm-3">
						<STRONG>' . $lang['Fecha de fin:'] . '</STRONG><br>'."\n";
						//instantiate class and set properties
						$myCalendar = new tc_calendar("fefin", true);
						$myCalendar->setPath("calendar/");
						$myCalendar->setIcon("calendar/images/iconCalendar.gif");
						$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
						$myCalendar->setYearInterval(2011, 2020);
						$myCalendar->setAutoHide(true, 5000);
						//output the calendar
						$myCalendar->writeScript();
	echo '					</div>
					<div class="col-sm-2 padding-left">
						<STRONG>' . $lang['Estatus:'] . '</STRONG>
						<a style="cursor:help; text-decoration:none; color:green;" href="ayuda.php?apartado=' . $lang['¿Qué es esto?'] . '&base=comisiones.php" onclick="window.open(this.href,' . "'Ayuda','left=300,top=200,width=450,height=350,toolbar=0,resizable=0,scrollbars=1,titlebar=0');" . ' return false;"><small>' . $lang['¿Qué es esto?'] . '</small></a>
						<br>
						<select name="estatus" onchange="document.filtrorep.submit()";>
							<option value="todos"';
		if($estatus == 'todos') { echo ' selected '; }
		echo '>' . $lang['Todos'] . '</option>
							<option value="10"';
		if($estatus == '10') { echo ' selected '; }
		echo '>' . $lang['Sin recibo'] . '</option>
							<option value="20"';
		if($estatus == '20') { echo ' selected '; }
		echo '>' . $lang['Pendientes de pago'] . '</option>
							<option value="30"';
		if($estatus == '30') { echo ' selected '; }
		echo '>' . $lang['Pagadas'] . '</option>
						</select>'."\n";
		echo '
					</div>
					<div class="col-sm-3 padding-left">
						<STRONG>' . $lang['Usuarios:'] . '</STRONG><br>
						<select name="usu_filt" onchange="document.filtrorep.submit()";>
							<option value="todos"';
		if($usu_filt == 'todos') { echo ' selected '; }
		echo '>' . $lang['Todos'] . '</option>';
		// ---- Select del usuario -----
		foreach($usuario as $usu => $dusu) {
			echo '							<option value="' . $usu . '" ';
			if($usu_filt == $usu) { echo ' selected '; }
			echo '>' . $dusu . '</option>'."\n";
		}
		echo '						</select>
					</div>
				</div>
				<div class="row"><div class="col-sm-3 padding-left">
						<input class="btn btn-success" type="submit" value="' . $lang['CONSULTAR'] . '"/>
				</div></div></form>'."\n";

	// --- creamos consulta con los criterios de búsqueda enviados ---
	$preg_comisiones = "SELECT * FROM " . $dbpfx . "comisiones WHERE fecha_creacion >= '" . $feini . "' AND fecha_creacion <= '" . $fefin . "' ";
	// ------------ Agregar condiciones a la consulta -----------
	// --- SELECCION DE ESTATUS ---
	if($estatus != 'todos' && $estatus != '') {
		$preg_comisiones .= " AND estatus = '" . $estatus . "' ";
	}
	// --- SELECCION DE USUARIO ---
	if($usu_filt != 'todos' && $usu_filt != ''){
		$preg_comisiones .= " AND usuario = '" . $usu_filt . "' ";
	}
	$matr_comisiones = mysql_query($preg_comisiones) or die("ERROR: Fallo selección de comisiones! " . $preg_comisiones);

	echo '
		<div class="row">
			<div class="col-sm-12 ">
				<div class="col-sm-12">
					<div id="content-tabla">
						<form action="comisiones.php?accion=recibos" method="post" enctype="multipart/form-data">
						<table cellspacing="0" class="table-new">
							<tr>
								<th>' . $lang['COMISIÓN'] . '</th>
								<th>' . $lang['O.T'] . '</th>
								<th>' . $lang['DESCRIPCIÓN'] . '</th>
								<th>' . $lang['USUARIO'] . '</th>
								<th>' . $lang['FECHA EVENTO'] . '</th>
								<th>' . $lang['MONTO COMISIÓN'] . '</th>
								<th>' . $lang['FECHA GENERACIÓN'] . '</th>
								<th>' . $lang['ESTATUS COMISIÓN'] . '</th>
								<th>' . $lang['RECIBO'];
	echo '									<input type="hidden" name="recibo_usu" value="' . $usu_filt . '" />
									<input type="hidden" name="estatus" value="' . $estatus . '" />
									<input type="hidden" name="feini" value="' . $feini . '" />
									<input type="hidden" name="fefin" value="' . $fefin . '" />
									<input type="hidden" name="comision" value="1" />
								</th>'."\n";	
	echo '							</tr>'."\n";
// --- RESAULTADOS DE LA CONSULTA ---
	$clase = 'claro';
	while($comisiones = mysql_fetch_array($matr_comisiones)) {
		$monto = '$' . number_format($comisiones['monto'], 2);
		$stat = $comision_sta[$comisiones['estatus']];
		$orden = '<a href="ordenes.php?accion=consultar&orden_id=' . $comisiones['orden_id'] . '" style=" display:block;">' . $comisiones['orden_id'] . '</a>';
		$recibo = '<a href="recibosrh.php?accion=consultar&recibo_id=' . $comisiones['recibo_id'] . '" style=" display:block;">' . $comisiones['recibo_id'] . '</a>';
		echo '
							<tr class="' . $clase . '">
								<td>' . $comisiones['com_id'] . '</td>
								<td>' . $orden . '</td>
								<td>' . $comision[$comisiones['comision_tipo']] . '</td>
								<td>' . $usuario[$comisiones['usuario']] . '</td>
								<td>' . date('Y-m-d', strtotime($comisiones['fecha_evento'])) . '</td>
								<td>' . $monto . '</td>
								<td>' . date('Y-m-d', strtotime($comisiones['fecha_creacion'])) . '</td>
								<td>' . $stat . '</td>
								<td>' . $recibo . '</td>'."\n";
		echo '							</tr>'."\n";
		if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
		$total_comisiones = $total_comisiones + $comisiones['monto'];
	}
	echo '
							<tr>
								<th colspan="5" style="text-align:right;">' . $lang['Total $'] . '</th>
								<th>$ ' . number_format($total_comisiones,2) . '</th>
								<th colspan="3"></th>
							</tr>	
    					</table>
						</form>
						<br>
					</div>
  				</div>
			</div>
		</div>'."\n";
	echo '
	</div>'."\n";
	//echo $preg_comisiones;
	unset($_SESSION['coincidencias']);
	unset ($_SESSION['dest']);
}

elseif($accion === "recibos"){

	$funnum = 0000000; // deberá cambiar al terminar el módulo
	
	if (validaAcceso($funnum, $dbpfx) == 1 || $_SESSION['rol02']=='1') {
		// Acceso autorizado
	} else {
		redirigir('index.php?mensaje=El acceso a esta función es sólo para gerentes');
	}
	
	if($hacer == 'BORRAR'){
		if($borrar == 1){
			foreach($recibo as $key => $val){
				// --- EXPLODE DEL VALUE ---
				$explode = explode("|", $val);
				// ---- ENVIAR: $explode [0]'COMISIÓN ID', [1]ORDEN_ID, [2]TIPO DE COMISION, [3]USUARIO, [4]MONTO
				$parametros = " com_id ='" . $explode[0] . "'";
				ejecutar_db($dbpfx . 'comisiones', '', 'eliminar', $parametros);
				$bitacora = 'Se eliminó la comisión ' . $explode[0] . ', de la orden ' . $explode[1] . ', del usuario ' . $explode[3] . ', comision tipo: ' . $explode[2] . '  con un monto de $ ' . number_format($explode[4],2);
				bitacora($con_elem['orden_id'], $bitacora, $dbpfx);
			}
			$_SESSION['msjerror'] = 'Se eliminaron éxitosamente las comisiones';
				redirigir("comisiones.php?accion=consultar&usu_filt=$usu_filt&estatus= $estatus&feini=$feini&fefin=$fefin");
		} else{
			if($recibo == ''){
				redirigir("comisiones.php?accion=consultar&usu_filt=$usu_filt&estatus= $estatus&feini=$feini&fefin=$fefin");	
			}
			echo '
				<h2>¿Estás seguro que quieres eliminar las comisiones?</h2>
				<form action="comisiones.php?accion=recibos&hacer=BORRAR&borrar=1&usu_filt=' . $recibo_usu . '&estatus=' . $estatus . '&feini=' . $feini . '&fefin=' . $fefin . '" method="post" enctype="multipart/form-data">
				<table>
					<tr>'."\n";
			foreach($recibo as $key => $val){
				echo '
						<td><input type="hidden" name="recibo[]" value="' . $val . '"></td>'."\n";
			}
			echo '
						<td>
							<input class="btn btn-success" type="submit" name="hacer" value="SI, ELIMINAR"/>
						</td>
						<td><a href="comisiones.php?accion=consultar&usu_filt=' . $recibo_usu . '&estatus=' . $estatus . '&feini=' . $feini . '&fefin=' . $fefin . '"><button type="button" class="btn btn-danger">NO, REGRESAR</button></a></td>
					</tr>
				</table>
				</form>'."\n";
		}
		//print_r($recibo);
	} else{
		//print_r($recibo);
		foreach($recibo as $key => $val){
			// --- EXPLODE DEL VALUE ---
			$explode = explode("|", $val);
			// ---- ENVIAR: $explode [0]'COMISIÓN ID', [1]ORDEN_ID, [2]TIPO DE COMISION, 	[3]USUARIO, [4]MONTO
			$veh = datosVehiculo($explode['1'], $dbpfx);
			//echo '<br>' . $explode[0];
			$_SESSION['dest']['orden_id'][] = $explode[1];
			$_SESSION['dest']['vehiculo'][] = $veh['marca'] . ' ' . $veh['tipo'] . ' ' . 	$veh['color'] . ' ' . $veh['placas'];
			$_SESSION['dest']['sub_reporte'][] = 'N/A';
			$_SESSION['dest']['sub_area'][] = 'N/A';
			$_SESSION['dest']['monto'][] = $explode[4];
			$_SESSION['dest']['operador'][] = $explode[3];
			$_SESSION['dest']['comision'][] = 1;
			$_SESSION['dest']['comi_tipo'][] = $comi_nombre[$explode[2]];
			$_SESSION['dest']['comision_id'][] = $explode[0];
		}
			redirigir('destajos.php?accion=gestionar&oprdest=' . $explode[3] . '');	
	}
}

echo '			</div>
		</div>
	</div>'."\n";

include('parciales/pie.php');
/* Archivo comisiones.php */
/* AutoShop-Easy.com */