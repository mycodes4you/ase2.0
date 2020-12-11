<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/reportes.php');
foreach($_POST as $k => $v){$$k = limpiar_cadena($v);} //  echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k = limpiar_cadena($v);} // echo $k.' -> '.$v.' | ';

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

if(isset($reportes_y)) { $accion='reportes'; }
if(isset($graficas_y)) { $accion='graficas'; }

	function tiemposOT($ini, $fin, $tac, $min, $max) {
		$t = $fin - $ini;
		$tac = $tac + $t;
		$max = ($t > $max) ? $t : $max;
		$min = ($t < $min) ? $t : $min;
		if(!$min) { $min = $t ;}
		return array($tac, $min, $max);
	}

	function dinero($str) {
		list($etiqueta, $valor) = explode(' ', $str, 2);
		return $etiqueta . ' $' . number_format($valor, '0', '.', ',');
	}

// echo 'del ' . $feini . ' al ' . $fefin . ' ---<br>';

	$prega = '';
	$t_ot = 'OTs Iniciadas en ';
	$t_vg = 'Ventas globales en ';
	$t_va = 'Ventas por área en ';

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
	}

// ------ Si el mes viene colocado calcular las fechas ini y fin ---
	if(isset($mfe) && $mfe != '' && $tipo_reparacion == 'reparados') {
		$estemes = date('n');
		$year = date('Y');
		if($mfe == 't' ) { $mes = 3; }
		else { $mes = $mfe; }
		$elmes = $estemes - $mes;
		if($elmes < 1) { $year = $year -1; $elmes = $elmes + 12; }
		$etiqmes = strftime('%B del %G', mktime(0,0,0,$elmes));
		$feini = $year . '-' . $elmes . '-01 00:00:00';
		if($mfe == 't' ) {
			$fefin = date('Y-m-t 23:59:59', time());
		} else {
			$fefin = date('Y-m-t 23:59:59', strtotime($feini));
		}
	}
	// --- Mes referencia calculado en el index ---
	elseif(isset($mes_r) && $mes_r != '') {
		$hoy = strtotime(date('Y-m-d 23:59:59'));
		$estemes = date('n');
		$year = date('Y');
		
		if($mes_r > 3){
			$mes_ini = 3;
			$mes_fin = 0;
			// --- calcular fecha ini y fecha fin de los ultimos 4 meses en curso ---
			$elmes = $estemes - $mes_ini;
			if($elmes < 1) { $year = $year -1; $elmes = $elmes + 12; }
			$etiqmes = strftime('%B del %G', mktime(0,0,0,$elmes));
			$feini = $year.'-'.$elmes.'-01 00:00:00';
			
			$elmes = $estemes - $mes_fin;
			if($elmes < 1) { $year = $year -1; $elmes = $elmes + 12; }
			$etiqmes = strftime('%B del %G', mktime(0,0,0,$elmes));
			$feini_e = $year.'-'.$elmes.'-01 00:00:00';
			$fefin_e = strtotime($feini_e);
			$fefin = date('Y-m-t 23:59:59', $fefin_e);
			
		} else{
			$elmes = $estemes - $mes_r;
			if($elmes < 1) { $year = $year -1; $elmes = $elmes + 12; }
			$etiqmes = strftime('%B del %G', mktime(0,0,0,$elmes));
			
			$feini = $year.'-'.$elmes.'-01 00:00:00';
			$fefin = strtotime($feini);
			$fefin = date('Y-m-t 23:59:59', $fefin);	
		}
	}

	$t_ini = strftime('%e de %B del %Y', strtotime($feini));
	$t_fin = strftime('%e de %B del %Y', strtotime($fefin));

	if($accion =='desglose') {
		$encabezado = ' OTs con Tareas del ' . $t_ini . ' al ' . $t_fin;
	} else {
		$encabezado = ' OTs ' . $nomrep . ' del ' . $t_ini . ' al ' . $t_fin;
	}

	$estemes = date('n');
	for($j=0;$j<5;$j++) {
		$elmes = $estemes - $j;
		if($elmes < 1) {$elmes = $elmes + 12;}
		$etiqmes[$j] = strtoupper(strftime('%b', mktime(0,0,0,$elmes)));
	}

	$t_grafica1 = utf8_decode($t_ot . $t_per . "\n(Agrupadas por Estatus)");
	$t_grafica2 = utf8_decode($t_ot . $t_per . "\n(Agrupadas por Etapa)");
	$t_grafica3 = utf8_decode($t_vg . $t_per . "\n(Agrupadas por Área)");
	$t_grafica4 = utf8_decode($t_vg . $t_per . "\n(Agrupadas por Tipo)");
	$t_grafica5 = utf8_decode($t_va . $t_per . "\n(Compuestas por Tipo)");
//	$encabezado = utf8_decode($encabezado);
// ----------------------- ARREGLO CUENTAS --------------------------------------------
		$preg_cuenta = "SELECT ban_id, ban_nombre, ban_cuenta FROM " . $dbpfx . "cont_cuentas";
		$array = mysql_query($preg_cuenta) or die("ERROR: Fallo selección de cuentas! " . $preg_cuenta);
		while ($cuenta = mysql_fetch_array($array)) {
			$cuent[$cuenta['ban_id']][0] = $cuenta['ban_nombre'];
			$cuent[$cuenta['ban_id']][1] = $cuenta['ban_cuenta'];
		}
// ------------------------------------------------------------------------------------

//  ----------------  obtener nombres de proveedores -------------------

		$consulta = "SELECT prov_id, prov_nic, prov_iva FROM " . $dbpfx . "proveedores ORDER BY prov_nic";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
		$num_provs = mysql_num_rows($arreglo);
		$provs = array();
//		$provs[0] = 'Sin Proveedor';
		while ($prov = mysql_fetch_array($arreglo)) {
			$provs[$prov['prov_id']] = $prov['prov_nic'];
			$provs_iva[$prov['prov_id']] = $prov['prov_iva'];
		}
//		print_r($provs);
//  -----------------------------------------------------------------------
//  ----------------  obtener nombres de aseguradoras	-------------------
	$consulta = "SELECT aseguradora_id, aseguradora_logo, aseguradora_nic, aseguradora_razon_social FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_nic";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
	while ($aseg = mysql_fetch_array($arreglo)) {
		$ase[$aseg['aseguradora_id']][0] = $aseg['aseguradora_logo'];
		$ase[$aseg['aseguradora_id']][1] = $aseg['aseguradora_nic'];
		$ase[$aseg['aseguradora_id']][2] = $aseg['aseguradora_razon_social'];
	}
	$ase[0][0] = 'particular/logo-particular.png';
	$ase[0][1] = 'Particular';
	$ase[0][2] = 'Particular';

//  -------------------------------------------------------------------
//  ----------------  obtener nombres de usuarios	-------------------

	$pregusu = "SELECT nombre, apellidos, usuario, codigo, activo, rol09 FROM " . $dbpfx . "usuarios WHERE acceso = '0' ORDER BY nombre, apellidos";
	$matrusu = mysql_query($pregusu) or die("ERROR: Fallo selección de usuarios!");
	while ($ases = mysql_fetch_array($matrusu)) {
		$usuario[$ases['usuario']] = $ases['nombre'] . ' ' . $ases['apellidos'];
		$usu_rol09[$ases['usuario']] = $ases['rol09'];
		$usr_cod[$ases['usuario']] = $ases['codigo'];
		$usr_activo[$ases['usuario']] = $ases['activo'];
	}

//  ----------------  nombres de asesores -------------------

	$accpf = 0;
	$f1125005 = validaAcceso('1125005', $dbpfx); if($f1125005 == '1') { $accpf = 1; }
	$f1125010 = validaAcceso('1125010', $dbpfx); if($f1125010 == '1') { $accpf = 1; }
	$f1125015 = validaAcceso('1125015', $dbpfx); if($f1125015 == '1') { $accpf = 1; }
	$f1125025 = validaAcceso('1125025', $dbpfx); if($f1125025 == '1') { $accpf = 1; }
	$f1125030 = validaAcceso('1125030', $dbpfx); if($f1125030 == '1') { $accpf = 1; }
	$f1125035 = validaAcceso('1125035', $dbpfx); if($f1125035 == '1') { $accpf = 1; }
	$f1125045 = validaAcceso('1125045', $dbpfx); if($f1125045 == '1') { $accpf = 1; }
	$f1125055 = validaAcceso('1125055', $dbpfx); if($f1125055 == '1') { $accpf = 1; }
	$f1125060 = validaAcceso('1125060', $dbpfx); if($f1125060 == '1') { $accpf = 1; }
	$f1125070 = validaAcceso('1125070', $dbpfx); if($f1125070 == '1') { $accpf = 1; }
	$f1125085 = validaAcceso('1125085', $dbpfx); if($f1125085 == '1') { $accpf = 1; }
	$f1125090 = validaAcceso('1125090', $dbpfx); if($f1125090 == '1') { $accpf = 1; }
	$f1125095 = validaAcceso('1125095', $dbpfx); if($f1125095 == '1') { $accpf = 1; }
	$f1125100 = validaAcceso('1125100', $dbpfx); if($f1125100 == '1') { $accpf = 1; }
	$f1125105 = validaAcceso('1125105', $dbpfx); if($f1125105 == '1') { $accpf = 1; }
	$f1125110 = validaAcceso('1125110', $dbpfx); if($f1125110 == '1') { $accpf = 1; }
	$f1125115 = validaAcceso('1125115', $dbpfx); if($f1125115 == '1') { $accpf = 1; }
	$f1125120 = validaAcceso('1125120', $dbpfx); if($f1125120 == '1') { $accpf = 1; }
	$f1125125 = validaAcceso('1125125', $dbpfx); if($f1125125 == '1') { $accpf = 1; }
	$f1125130 = validaAcceso('1125130', $dbpfx); if($f1125130 == '1') { $accpf = 1; }

//	$f = validaAcceso('', $dbpfx); if($f == '1') { $accpf = 1; }

	if($accpf == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1' || $_SESSION['rol12'] == '1' || $_SESSION['rol13'] == '1') {
		$mensaje = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso no autorizado, ingresar Usuario y Clave correcta');
	}

if($export != 1) { // ---- Hoja de calculo ----
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	if ($accion==="reportes" || $accion==="entregados" || $accion==="seguimiento" || $accion==="desglose" || $accion==="destajo" || $accion==="operadores" || $accion==="finanzas" || $accion==="cliente" || $accion==="manodeobra" || $accion==="audatrace" || $accion==="ajustadores" || $accion==="comentreg" || $accion==="refproceso" || $accion==="valuaciones" || $accion==="facturacion" || $accion==="comentarios" || $accion==="vigilancia" || $accion==="deducibles" || $accion==="ingext" || $accion==="ingresos" || $accion === "carga_operador" || $accion === "encuesta" || $accion==="carga_operador" || $accion==="cumplimiento_operadores" || $accion === "utilidad_area" || $accion === "factura-provs") {


		echo '		<div class="menureportes">'."\n";
		if ($f1125010 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
		echo '					<div><a href="reportes.php?accion=reportes&nomrep=Recibidos"><img src="idiomas/' . $idioma . '/imagenes/r_recibidos.png" alt="Recibidos" title="Recibidos"></a></div>'."\n";
		}
		if ($f1125090 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
			echo '					<div><a href="reportes.php?accion=comentreg&nomrep=Entregados"><img src="idiomas/' . $idioma . '/imagenes/r_entregados.png" alt="Entregados" title="Entregados"></a></div>'."\n";
		}
		if ($f1125070 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1') {
			echo '					<div><a href="reportes.php?accion=cliente&nomrep=por Aseguradora"><img src="idiomas/' . $idioma . '/imagenes/r_ots-por-aseguradora.png" alt="OTs por Aseguradora" title="OTs por Aseguradora"></a></div>'."\n";
		}
		if ($f1125085 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1') {
			if($ajustadores == '1') {
				echo '					<div><a href="reportes.php?accion=ajustadores&nomrep=Ajustadores"><img src="idiomas/' . $idioma . '/imagenes/ajustadores_h.png" alt="Ajustadores" title="Ajustadores"></a></div>'."\n";
			}
		}
		if ($f1125025 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1') {
			echo '					<div><a href="reportes.php?accion=deducibles&nomrep=Deducibles"><img src="idiomas/' . $idioma . '/imagenes/r_desglose.png" alt="Desglose" title="Desglose"></a></div>'."\n";
		}
		if ($f1125060 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1') {
			echo '					<div><a href="reportes.php?accion=manodeobra&nomrep=Mano de Obra"><img src="idiomas/' . $idioma . '/imagenes/r_mano-de-obra.png" alt="Mano de Obra" title="Mano de Obra"></a></div>'."\n";
		}
		if ($f1125055 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1') {
			echo '					<div><a href="reportes.php?accion=destajo&nomrep=Destajo"><img src="idiomas/' . $idioma . '/imagenes/r_destajo.png" alt="Destajo" title="Destajo"></a></div>'."\n";
		}

		//if ($f1125030 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1') {

		if ($f1125030 == '1' || $_SESSION['rol02']=='1') {
			echo '					<div><a href="reportes.php?accion=finanzas&nomrep=Finanzas"><img src="idiomas/' . $idioma . '/imagenes/r_finanzas.png" alt="Finanzas" title="Finanzas"></a></div>'."\n";
		}
		if ($f1125030 == '1' || $_SESSION['rol02']=='1') {
			echo '					<div><a href="reportes.php?accion=utilidad_area&nomrep=Utilidad-x-Área"><img src="idiomas/' . $idioma . '/imagenes/utilidad_area.png" alt="utilidad por área" title="utilidad por área"></a></a></div>'."\n";
		}

		// --- Facturas a Clientes -------------------------------
		if ($f1125100 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol12'] == '1') {
			echo '					<div><a href="reportes.php?accion=facturacion&nomrep=Facturación"><img src="idiomas/' . $idioma . '/imagenes/facturas-de-clientes_R.png" alt="Facturación" title="Facturación"></a></div>'."\n";
		}

		//------ Cuentas por Cobrar --------------------------
		if ($f1125035 == '1') {
			echo '					<div><a href="personas.php?accion=cuentasxcobrar"><img src="idiomas/' . $idioma . '/imagenes/cuentasxcobrar.png" alt="Cuentas X Cobrar" title="Cuentas X Cobrar"></a></div>'."\n";
		}

		// --- Cobros y Pagos -------------------------------
		if ($f1125115 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol12'] == '1' || $_SESSION['rol13'] == '1') {
			echo '					<div><a href="reportes.php?accion=ingresos&nomrep=cobros y pagos"><img src="idiomas/' . $idioma . '/imagenes/ingresos.png" alt="cobros y pagos" title="cobros y pagos"></a></div>'."\n";
		}

		// --- Facturas de Proveedores -------------------------------
		if ($f1125130 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol13'] == '1') {
			echo '					<div><a href="reportes.php?accion=factura-provs&nomrep=Facturación"><img src="idiomas/' . $idioma . '/imagenes/facturas-de-proveedores_R.png" alt="Facturas de Proveedores" title="Facturas de Proveedores"></a></div>'."\n";
		}

		//------ Cuentas por Pagar --------------------------
		if ($f1125045 == '1') {
			echo '					<div><a href="proveedores.php?accion=cuentasxpagar"><img src="idiomas/' . $idioma . '/imagenes/cuentasxpagar.png" alt="Cuentas X Pagar" title="Cuentas X Pagar"></a></div>'."\n";
		}
		//----------------------------------------------------
		if ($f1125120 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol04'] == '1') {
			echo '					<div><a href="reportes.php?accion=carga_operador"><img src="idiomas/' . $idioma . '/imagenes/carga-de-trabajo.png" alt="carga operadores" title="carga operadores"></a></div>'."\n";
		}
		//----------------------------------------------------
		if ($f1125120 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol04'] == '1') {
			echo '					<div><a href="reportes.php?accion=cumplimiento_operadores"><img src="idiomas/' . $idioma . '/imagenes/rendimiento_operadores.png" alt="cumplimiento operadores" title="cumplimiento operadores"></a></div>'."\n";
		}
		//----------------------------------------------------
		if ($f1125125 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1') {
			echo '					<div><a href="reportes.php?accion=encuesta"><img src="idiomas/' . $idioma . '/imagenes/encuestas.png" alt="encuestas" title="encuestas"></a></div>'."\n";
		}
		//----------------------------------------------------
		if ($f1125095 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1') {
			echo '					<div><a href="reportes.php?accion=valuaciones"><img src="idiomas/' . $idioma . '/imagenes/r_valuacion.png" alt="Valuaciones" title="Valuaciones"></a></div>'."\n";
		}
		if ($f1125105 == '1' || $_SESSION['rol02'] == '1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1') {
			echo '					<div><a href="reportes.php?accion=refproceso"><img src="idiomas/' . $idioma . '/imagenes/inventario-en-proceso_h.png" alt="Inventario en Proceso" title="Inventario en Proceso"></a></div>'."\n";
		}

		if ($f1125090 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
			echo '					<div><a href="reportes.php?accion=comentarios&nomrep=Seguimiento"><img src="idiomas/' . $idioma . '/imagenes/r_reporte-de-comentarios.png" alt="Reporte de Comentarios" title="Reporte de Comentarios"></a></div>'."\n";
		}
		if ($f1125110 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
			echo '					<div><a href="reportes.php?accion=vigilancia&nomrep=Control de Entradas y Salidas"><img src="idiomas/' . $idioma . '/imagenes/r_vigilancia.png" alt="Bitacora de Entradas y Salidas" title="Bitacora de Entradas y Salidas"></a></div>'."\n";
		}
		if ($f1125005 == '1' || $f1125010 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
			echo '					<div><a href="reportes.php?accion=ingext&nomrep=Ingreso Extendido"><img src="idiomas/' . $idioma . '/imagenes/ingext.png" alt="Ingreso Extendido" title="Ingreso Extendido"></a></div>'."\n";
		}
		echo '				</div>'."\n";

		if($accion != "ot10refpend" && $accion != "carga_operador") {
		echo '		<form action="reportes.php" method="post" enctype="multipart/form-data" name="filtrorep">
		<table cellpadding="0" cellspacing="0" border="0" width="80%" style="clear:left;">'."\n";

		require_once("calendar/tc_calendar.php");
		echo '					<tr><td style="vertical-align:top;">Fecha de Inicio<br>';
		//instantiate class and set properties
		$myCalendar = new tc_calendar("feini", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
		//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		//$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2011, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();
		echo '</td><td style="vertical-align:top;">Fecha de Fin<br>';
		//instantiate class and set properties
		$myCalendar = new tc_calendar("fefin", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
		//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		//$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2011, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();
		echo '</td><td style="vertical-align:top;">'."\n";

		// ------------ Filtro por tipo de flujo -------------------
		if($accion === 'ingresos') {
			echo 'Filtra por Tipo de Movimientos<br>';
			echo '							<select name="flujoflt" size="1" onchange="document.filtrorep.submit()";>'."\n";
			echo '								<option value=""';
			if($flujoflt == '') { echo ' selected '; }
			echo '>Todos los Movimientos</option>'."\n";
			echo '								<option value="1"';
			if($flujoflt == '1') { echo ' selected '; }
			echo '>' . $lang['Flujo Ingresos'] . '</option>'."\n";
			echo '								<option value="2"';
			if($flujoflt == '2') { echo ' selected '; }
			echo '>' . $lang['Flujo Egresos'] . '</option>'."\n";
			echo '							</select>'."\n";
		}
		elseif($accion === 'cumplimiento_operadores'){
			echo '
						<b><big>Operadores</big><b><br>'."\n";
			echo '							<select class="form-control" name="operador" size="1">'."\n";
			echo '								<option value="0"'."\n";
			if($operador == '0') { echo ' selected '; }
			echo '>Selecciona Operador</option>'."\n";

			foreach($usuario as $key => $val){
				echo '							<option value="' . $key . '"';
				if($operador == $key) { echo ' selected '; }
				echo '> ' . $val . '</option>'."\n";
			}
			echo '							</select><br>'."\n";
			echo '
						<b><big>Estatus de tareas:</big><b><br>'."\n";
			echo '							<select class="form-control" name="estatus_tareas" size="1">'."\n";
			echo '								<option value="0"'."\n";
			if($estatus_tareas == '0') { echo ' selected '; }
			echo '>Todas las tareas</option>'."\n";
			echo '								<option value="1"'."\n";
			if($estatus_tareas == '1') { echo ' selected '; }
			echo '>Tareas sin terminar</option>'."\n";
			echo '								<option value="2"'."\n";
			if($estatus_tareas == '2') { echo ' selected '; }
			echo '>Tareas terminadas</option>'."\n";

			echo '							</select><br>'."\n";
			
		}
		// ----------------------------------------------------------
		if($accion === 'finanzas' || $accion === 'deducibles' || $accion === 'utilidad_area') {
			echo 'Filtra por Estatus<br>';
			echo '							<select name="estatusflt" size="1">'."\n";
			echo '								<option value="0"';
			if($estatusflt == '0') { echo ' selected '; }
			echo '>Todos los Recibidos</option>'."\n";
			echo '								<option value="1"';
			if($estatusflt == '1') { echo ' selected '; }
			echo '>En documentación</option>'."\n";
			echo '								<option value="2"';
			if($estatusflt == '2') { echo ' selected '; }
			echo '>En reparación</option>'."\n";
			echo '								<option value="3"';
			if($estatusflt == '3') { echo ' selected '; }
			echo '>Terminados</option>'."\n";
			echo '								<option value="4"';
			if($estatusflt == '4') { echo ' selected '; }
			echo '>Entregados</option>'."\n";
			echo '								<option value="5"';
			if($estatusflt == '5') { echo ' selected '; }
			echo '>Cobrados</option>'."\n";
			echo '								<option value="6"';
			if($estatusflt == '6') { echo ' selected '; }
			echo '>Facturados No cobrados</option>'."\n";
			echo '								<option value="7"';
			if($estatusflt == '7') { echo ' selected '; }
			echo '>Cobrables No Facturados</option>'."\n";
			echo '							</select>'."\n";
		} elseif($accion === 'comentarios') {
			echo '						Ordenar por<br>';
			echo '							<select name="orden_comentarios" size="1">'."\n";
			echo '								<option value="0"';
			if($orden_comentarios == '0') { echo ' selected '; }
			echo '>Fecha de llamada</option>'."\n";
			echo '								<option value="1"';
			if($orden_comentarios == '1') { echo ' selected '; }
			echo '>Orden de trabajo</option>'."\n";
			echo '							</select>
						</td>'."\n";
			echo '						<td style="vertical-align:top;">
						Asesores<br>'."\n";
			echo '							<select name="asesor_select" size="1">'."\n";
			echo '								<option value="0"'."\n";
			if($asesor_select == '0') { echo ' selected '; }
			echo '>Selecciona Asesor</option>'."\n";

			foreach ($usuario as $clave => $valor) {
				if($usr_cod[$clave] == 30) {
					echo '							<option value="' . $clave . '"';
					if($asesor_select == $clave) { echo ' selected '; }
					echo '> ' . $valor . '</option>'."\n";
				}
			}
			echo '							</select>'."\n";
		} elseif($accion === 'vigilancia') {
			echo 'Selecciona Localidad<br>';
			echo '							<select name="localidad" size="1">'."\n";
			foreach($ubicaciones as $k => $v) {
				echo '								<option value="' . $k . '"';
				if($k == $localidad) { echo ' selected '; }
				echo '>' . $v . '</option>'."\n";
			}
			echo '								<option value="T"';
			if(!isset($localidad) || $localidad == 'T') { echo ' selected '; }
			echo '>Todas</option>'."\n";
			echo '							</select>'."\n";
		} elseif($accion === 'valuaciones') {
			$nivel = limpiarNumero($nivel);
			echo 'Indica el monto de alerta<br>';
			echo '							$<input type="text" name="nivel" value="' . number_format($nivel,0) . '" size="6" style="text-align:right;" />'."\n";
		} elseif($accion==="refproceso") {
			echo '						' . $lang['Filtrar por Tipo'] . '<br>';
			echo '							<select name="tipo_producto" size="1">'."\n";
			echo '								<option value="T"';
			if($tipo_producto == '' || $tipo_producto == 'T' ) { echo ' selected '; }
			echo '>' . $lang['Todos'] . '</option>'."\n";
			echo '								<option value="0"';
			if($tipo_producto == '0') { echo ' selected '; }
			echo '>' . TIPO_PRODUCTO_0 . '</option>'."\n";
			echo '								<option value="1"';
			if($tipo_producto == '1') { echo ' selected '; }
			echo '>' . TIPO_PRODUCTO_1 . '</option>'."\n";
			echo '								<option value="2"';
			if($tipo_producto == '2') { echo ' selected '; }
			echo '>' . TIPO_PRODUCTO_2 . '</option>'."\n";
			echo '							</select>'."\n";
			// ------ Posible filtro por estatus de Refacción.
			/*		echo '						</td>'."\n";
			echo '						<td>'."\n";
			echo '						' . $lang['Filtrar por Estatus'] . '<br>';
			echo '							<select name="tipo_estatus" size="1">'."\n";
			echo '								<option value=""';
			if($tipo_estatus == '') { echo ' selected '; }
			echo '>' . $lang['Todos'] . '</option>'."\n";
			echo '								<option value="1"';
			if($tipo_estatus == '1') { echo ' selected '; }
			echo '>' . $lang['Pendiente'] . '</option>'."\n";
			echo '								<option value="2"';
			if($tipo_estatus == '2') { echo ' selected '; }
			echo '>' . $lang['Recibido sin asignación'] . '</option>'."\n";
			echo '								<option value="3"';
			if($tipo_estatus == '3') { echo ' selected '; }
			echo '>' . $lang['Recibido sin entregar'] . '</option>'."\n";
			echo '								<option value="4"';
			if($tipo_estatus == '4') { echo ' selected '; }
			echo '>' . $lang['Operario'] . '</option>'."\n";
			echo '								<option value="5"';
			if($tipo_estatus == '5') { echo ' selected '; }
			echo '>' . $lang['Por Entregar'] . '</option>'."\n";
			echo '								<option value="6"';
			if($tipo_estatus == '6') { echo ' selected '; }
			echo '>' . $lang['Por Devolver'] . '</option>'."\n";
			echo '							</select>'."\n";
			*/
		}
		// ------------ Filtro por Aseguradora o Convenio -------------------
		//	if($accion === 'finanzas' || $accion === 'deducibles' || $accion === 'ingresos') {
		if($accion === 'ingresos' && $flujoflt == '1') {
			echo '<br>Filtra por Cliente<br>';
			echo '							<select name="asegflt" size="1" onchange="document.filtrorep.submit()";>'."\n";
			echo '								<option value=""';
			if($asegflt == '') { echo ' selected '; }
			echo '>Todos los Clientes</option>'."\n";
			foreach($ase as $ka => $va) {
				echo '								<option value="' . $ka . '"';
				if($asegflt == '' . $ka . '') { echo ' selected '; }
				echo '>' . $va[1] . '</option>'."\n";
			}
			echo '							</select>'."\n";
			
		} elseif($accion === 'ingresos' && $flujoflt == '2') {
			echo '<br>Filtra por Proveedor<br>';
			echo '							<select name="provflt" onchange="document.filtrorep.submit()";>'."\n";
			echo '								<option value=""';
			if($asegflt == '') { echo ' selected '; }
			echo '>Todos los Proveedores</option>'."\n";
			foreach($provs as $ka => $va) {
				echo '								<option value="' . $ka . '"';
				if($provflt == '' . $ka . '') { echo ' selected '; }
				echo '>' . $va . '</option>'."\n";
			}
			echo '							</select>'."\n";
		} elseif($accion == 'reportes' || $accion == 'finanzas' || $accion === 'utilidad_area' || $accion == 'ingresos' || $accion == 'comentreg') {
			echo '<br>Filtra por Cliente<br>';
			echo '							<select name="asegflt" size="1" onchange="document.filtrorep.submit()";>'."\n";
			echo '								<option value=""';
			if($asegflt == '') { echo ' selected '; }
			echo '>Todos los Clientes</option>'."\n";
			foreach($ase as $ka => $va) {
				echo '								<option value="' . $ka . '"';
				if($asegflt == '' . $ka . '') { echo ' selected '; }
				echo '>' . $va[1] . '</option>'."\n";
			}
			echo '							</select><br>'."\n";
		} elseif($accion == 'cliente'){
			echo '<br>Filtra por Cliente<br>';
			echo '							<select name="a" size="1" onchange="document.filtrorep.submit()";>'."\n";
			echo '								<option value=""';
			if($a == '') { echo ' selected '; }
			echo '>Todos los Clientes</option>'."\n";
			foreach($ase as $ka => $va) {
				echo '								<option value="' . $ka . '"';
				if($a == '' . $ka . '') { echo ' selected '; }
				echo '>' . $va[1] . '</option>'."\n";
			}
			echo '							</select><br>'."\n";
		}
		elseif($accion === 'ingresos' && $flujoflt == '2') {
			echo '<br>Filtra por Proveedor<br>';
			echo '							<select name="provflt" onchange="document.filtrorep.submit()";>'."\n";
			echo '								<option value=""';
			if($asegflt == '') { echo ' selected '; }
			echo '>Todos los Proveedores</option>'."\n";
			foreach($provs as $ka => $va) {
				echo '								<option value="' . $ka . '"';
				if($provflt == '' . $ka . '') { echo ' selected '; }
				echo '>' . $va . '</option>'."\n";
			}
			echo '							</select>'."\n";
		} elseif($accion == 'reportes' || $accion == 'finanzas' || $accion == 'ingresos' || $accion == 'comentreg') {
			echo 'Filtra por Cliente<br>';
			echo '							<select name="asegflt" size="1" onchange="document.filtrorep.submit()";>'."\n";
			echo '								<option value=""';
			if($asegflt == '') { echo ' selected '; }
			echo '>Todos los Clientes</option>'."\n";
			foreach($ase as $ka => $va) {
				echo '								<option value="' . $ka . '"';
				if($asegflt == '' . $ka . '') { echo ' selected '; }
				echo '>' . $va[1] . '</option>'."\n";
			}
			echo '							</select><br>'."\n";
		}
		// ------------ Filtro por Tipo de Servicio -------------------
		if($accion == 'reportes' || $accion == 'comentreg') {
			echo 'Filtra por Tipo de Servicio<br>';
			echo '							<select name="servflt" size="1" onchange="document.filtrorep.submit()";>'."\n";
			echo '								<option value=""';
			if($servflt == '') { echo ' selected '; }
			echo '>Todos los Tipos</option>'."\n";
			for($it =1; $it <= $num_tipos; $it++) {
				echo '								<option value="' . $it . '"';
				if($servflt == '' . $it . '') { echo ' selected '; }
				echo '>' . constant('ORDEN_SERVICIO_' . $it) . '</option>'."\n";
			}
			echo '							</select><br>'."\n";
			
			if($accion == 'comentreg'){
				echo '
				Filtra por Tipo de Fecha:<br>
				<select name="tipo_fecha" size="1" onchange="document.filtrorep.submit()";>
					<option value=""';
				if($tipo_fecha == '') { echo ' selected '; }
				echo '>Fecha de Entrega</option>
					<option value="fech_termino"';
				if($tipo_fecha == 'fech_termino') { echo ' selected '; }
				echo '>Fecha Termino de Proceso</option>
				</select>
				<br>
				'."\n";			
			}
			
		}
		if($accion == 'reportes' || $accion == 'comentreg' || $accion == 'cliente') {
			echo '
			Reparados y No Reparados:<br>
			<select name="tipo_reparacion" size="1" onchange="document.filtrorep.submit()";>
				<option value=""';
			if($tipo_reparacion == '') { echo ' selected '; }
			echo '>Todos</option>
				<option value="reparados"';
			if($tipo_reparacion == 'reparados') { echo ' selected '; }
			echo '>Reparados</option>
				<option value="no_reparados"';
			if($tipo_reparacion == 'no_reparados') { echo ' selected '; }
			echo '>No Reparados</option>
			</select>
			<br>'."\n";
			if($accion == 'comentreg'){
				echo '
				Filtra estatus:<br>
				<select name="estatus_cerrado" size="1" onchange="document.filtrorep.submit()";>
					<option value=""';
				if($estatus_cerrado == '') { echo ' selected '; }
				echo '>Todos los entregados</option>
					<option value="99"';
				if($estatus_cerrado == '99') { echo ' selected '; }
				echo '>' . ORDEN_ESTATUS_99 . '</option>
					<option value="5"';
				if($estatus_cerrado == '5') { echo ' selected '; }
				echo '>' . ORDEN_ESTATUS_5 . '</option>
					<option value="6"';
				if($estatus_cerrado == '6') { echo ' selected '; }
				echo '>' . ORDEN_ESTATUS_6 . '</option>
					<option value="16"';
				if($estatus_cerrado == '16') { echo ' selected '; }
				echo '>' . ORDEN_ESTATUS_16 . '</option>
					<option value="98"';
				if($estatus_cerrado == '98') { echo ' selected '; }
				echo '>' . ORDEN_ESTATUS_98 . '</option>
					<option value="97"';
				if($estatus_cerrado == '97') { echo ' selected '; }
				echo '>' . ORDEN_ESTATUS_97 . '</option>
					<option value="96"';
				if($estatus_cerrado == '96') { echo ' selected '; }
				echo '>' . ORDEN_ESTATUS_96 . '</option>
					<option value="95"';
				if($estatus_cerrado == '95') { echo ' selected '; }
				echo '>' . ORDEN_ESTATUS_95 . '</option>
					<option value="94"';
				if($estatus_cerrado == '94') { echo ' selected '; }
				echo '>' . ORDEN_ESTATUS_94 . '</option>
					<option value="210"';
				if($estatus_cerrado == '210') { echo ' selected '; }
				echo '>' . ORDEN_ESTATUS_210 . '</option>
					<option value="sin-cerrar"';
				if($estatus_cerrado == 'sin-cerrar') { echo ' selected '; }
				echo '>Entregados Sin cerrar</option>
				</select>
				<br>
				'."\n";			
			}
		}
		// ------------------------------------------------------------------
		if($accion === 'cliente') {
			if($tipo_reparacion == 'reparados' && $mfe != '') { $estatusflt = 1; }
			if($estatusflt != 1) { $estatusflt = 0; }
			echo 'Filtra por Fecha Ingreso o Entrega<br>';
			echo '							<select name="estatusflt" size="1" onchange="document.filtrorep.submit()";>'."\n";
			echo '								<option value="0"';
			if($estatusflt == '0') { echo ' selected '; }
			echo '>Por fecha de Ingreso</option>'."\n";
			echo '								<option value="1"';
			if($estatusflt == '1') { echo ' selected '; }
			echo '>Por fecha de Entrega</option>'."\n";
			echo '							</select>'."\n";
		}
		if($accion==="facturacion") {
			echo 'Filtra por tipo de documento de corbo:<br>';
			echo '							<select name="fact_tipo" size="1">'."\n";
			echo '								<option value="0"';
			if($fact_tipo == '0' || $fact_tipo == '') { echo ' selected '; }
			echo '>TODOS</option>'."\n";
			echo '								<option value="1"';
			if($fact_tipo == '1') { echo ' selected '; }
			echo '>FACTURAS</option>'."\n";
			echo '								<option value="2"';
			if($fact_tipo == '2') { echo ' selected '; }
			echo '>REMISONES</option>'."\n";
			echo '							</select>'."\n";
		} elseif($accion==="factura-provs") {
			echo 'Rango de fechas:<br>';
			echo '							<select name="tipo_fecha" size="1">'."\n";
			echo '								<option value="0"';
			if($tipo_fecha < '1') { echo ' selected '; }
			echo '>Fecha Recibida</option>'."\n";
			echo '								<option value="1"';
			if($tipo_fecha == '1') { echo ' selected '; }
			echo '>Fecha Programada</option>'."\n";
			echo '								<option value="2"';
			if($tipo_fecha == '2') { echo ' selected '; }
			echo '>Fecha Pagada</option>'."\n";
			echo '								<option value="3"';
			if($tipo_fecha == '3') { echo ' selected '; }
			echo '>Fecha Creada</option>'."\n";
			echo '							</select>'."\n";
			echo '					</td>
				</tr>
				<tr>
					<td style="text-align: right;">
						Monto Factura <input type="text" name="fmontofactura" value="' . $fmontofactura . '" size="12" /><br>
						Banco <input type="text" name="fbanco" value="' . $fbanco . '"  size="12" /><br>
						Cuenta <input type="text" name="fcuenta" value="' . $fcuenta . '" size="12" /><br>
					</td>
					<td style="text-align: right;">
						Pago Global <input type="text" name="fglobal" value="' . $fglobal . '" size="12" /><br>
						Referencia <input type="text" name="freferencia" value="' . $freferencia . '" size="12" /><br>
						OT <input type="text" name="fot" value="' . $fot . '" size="8" />
					</td>
					<td style="text-align: right;">
						Factura <input type="text" name="ffactura" value="' . $ffactura . '" size="12" /><br>
						Pedido <input type="text" name="fpedido" value="' . $fpedido . '" size="12" /><br>
						<select name="fproveedor" size="1">
							<option value="0">Todos los Proveedores</option>'."\n";
			foreach($provs as $ka => $va) {
				echo '								<option value="' . $ka . '"';
				if($fproveedor == $ka) { echo ' selected '; }
				echo '>' . $va . '</option>'."\n";
			}
			echo '							</select>'."\n";
		} elseif($accion == 'finanzas') {
			echo 'Filtra por Área:<br>
											<select name="area_filtro" size="1">
												<option value="0"';
			if($area_filtro == '0' || $area_filtro == '') { echo ' selected '; }
			echo '>Todas las Áreas</option>'."\n";
			$cont = 1;
			while($cont <= $num_areas_servicio){
				
				echo '							<option value="' . $cont . '"';
				if($fact_tipo == $cont) { echo ' selected '; }
				echo '>' . constant('NOMBRE_AREA_' . $cont) . '</option>'."\n";

				$cont++;
			}
			echo '							</select>'."\n";
		}
		// ----------------------- Fin de Filtros----------------------------

		// ----------------------- Export Hoja de calculo -------------------
        if($accion == 'reportes'){
			$href = 'reportes.php?accion=reportes&export=1&feini='.$feini.'&fefin='.$fefin.'&asegflt=' . $asegflt . '&servflt=' . $servflt;
		} elseif($accion == 'comentreg'){
			$href = 'reportes.php?accion=comentreg&export=1&feini='.$feini.'&fefin='.$fefin.'&servflt=' . $servflt . '&asegflt=' . $asegflt . '&tipo_fecha=' . $tipo_fecha .'&estatus_cerrado=' . $estatus_cerrado;
		} elseif($accion == 'deducibles'){
			$href = 'reportes.php?accion=deducibles&export=1&feini='.$feini.'&fefin='.$fefin.'&estatusflt=' . $estatusflt;
		} elseif($accion == "destajo"){
			$href = 'reportes.php?accion=destajo&export=1&feini='.$feini.'&fefin='.$fefin;
		} elseif($accion == "finanzas"){
			$href = 'reportes.php?accion=finanzas&export=1&feini='.$feini.'&fefin='.$fefin.'&estatusflt='.$estatusflt.'&asegflt='.$asegflt;
		} elseif($accion == "ingresos"){
			$href = 'reportes.php?accion=ingresos&export=1&feini='.$feini.'&fefin='.$fefin.'&flujoflt='.$flujoflt.'&provflt='.$provflt.'&asegflt='.$asegflt;
		} elseif($accion == "facturacion") {
			$href = 'reportes.php?accion=facturacion&export=1&feini='.$feini.'&fefin='.$fefin.'&cliente=' . $cliente . '&aseguradora_id=' . $aseguradora_id . '&factura=' . $factura . '&ot=' . $ot . '&fltcli=' . $fltcli . '&fact_monto=' . $fact_monto . '&t_ini=' . $t_ini . '&t_fin=' . $t_fin .'&fact_tipo=' . $fact_tipo;
		} elseif($accion == "factura-provs") {
			$href = 'reportes.php?accion=factura-provs&export=1&feini='.$feini.'&fefin='.$fefin.'&tipo_fecha=' . $tipo_fecha . '&fmontofactura=' . $fmontofactura . '&fot=' . $fot . '&ffactura=' . $ffactura . '&fpedido=' . $fpedido . '&fproveedor=' . $fproveedor . '&t_ini=' . $t_ini . '&t_fin=' . $t_fin;
		} elseif($accion == 'manodeobra'){
			$href = 'reportes.php?accion=manodeobra&export=1&feini='.$feini.'&fefin='.$fefin;
		} elseif($accion == "cliente"){
			$href = 'reportes.php?accion=cliente&export=1&feini='.$feini.'&fefin='.$fefin.'&estatusflt=' . $estatusflt . '&tipo_reparacion=' . $tipo_reparacion . '&a=' . $a;
		} elseif($accion == "refproceso"){
			$href = 'reportes.php?accion=refproceso&export=1&feini='.$feini.'&fefin='.$fefin.'&tipo_producto=' . $tipo_producto . '&fltrefnom1=' . $fltrefnom1;
		} elseif($accion == "cumplimiento_operadores"){
			$href = 'reportes.php?accion=cumplimiento_operadores&export=1&feini='.$feini.'&fefin='.$fefin.'&operador=' . $operador . '&estatus_tareas=' . $estatus_tareas.'&filtro='.$filtro;
		} elseif($accion == "valuaciones"){
			$href = 'reportes.php?accion=valuaciones&export=1&feini='.$feini.'&fefin='.$fefin.'&nivel=' . $nivel;
		} elseif($accion == "utilidad_area"){
			$href = 'reportes.php?accion=utilidad_area&export=1&feini='.$feini.'&fefin='.$fefin.'&estatusflt=' . $estatusflt . '&asegflt=' . $asegflt;
		}

		if($accion == 'reportes' || $accion == 'comentreg' || $accion == 'deducibles' || $accion === "destajo" || $accion == "finanzas" || $accion == "ingresos" || $accion == "facturacion" || $accion == "factura-provs" || $accion == 'manodeobra' || $accion == "cliente" || $accion == "refproceso" || $accion == "cumplimiento_operadores" || $accion == "valuaciones" || $accion == "utilidad_area"){
			echo '
					</td>'."\n";
			echo '
					<td>
						<a href="' . $href . '">
                        <img src="idiomas/' . $idioma . '/imagenes/hoja-calculo.png" alt="Exportar" border="0">
                        </a>
					<td>'."\n";
			
			if($accion === "destajo"){
				echo '
					<td><b>El export de este reporte solo soporta 9 semanas máximo</b></td>'."\n";
			}
		}

		
			
			echo '
					</td>
				</tr>
                <tr><td colspan="3">
                    	<input type="hidden" name="accion" value="' . $accion . '" />
                    	<input type="hidden" name="nomrep" value="' . $nomrep . '" />
                   		<input type="hidden" name="ordenar" value="' . $ordenar . '" />
                    	<input type="submit" value="Enviar" />
                    </td>
				</tr>
			</table></form>'."\n";
            }

            echo '		<div style="clear: both;"></div>'."\n";
	}
}

	$mes_este = date('n');
	//	echo date('Y-m-t');
	$preg0 = "SELECT orden_id, orden_cliente_id, orden_estatus, orden_categoria, orden_servicio, orden_vehiculo_placas, orden_vehiculo_tipo, orden_vehiculo_color, orden_vehiculo_id, orden_alerta, orden_asesor_id, orden_fecha_recepcion, orden_fecha_proceso_fin, orden_fecha_de_entrega, orden_fecha_promesa_de_entrega, orden_fecha_ultimo_movimiento, orden_grua FROM " . $dbpfx . "ordenes WHERE ";

	$preg0 .= $prega ;

	if($accion == 'reportes') {
		if($tipo_reparacion == 'reparados'){
			$preg0 .= " AND (orden_estatus <= '29' OR orden_estatus = '99')  ";
		} elseif($tipo_reparacion == 'no_reparados'){
			$preg0 .= " AND (orden_estatus >= '30' AND orden_estatus <= '98') ";
		}
	}

	if($servflt != '') {
		$preg0 .= " AND orden_servicio = '" . $servflt . "' ";
	}

	if($ordenar == 'asesor') {
		$pregn = $preg0 . "ORDER BY orden_asesor_id,orden_estatus,orden_id";
	} elseif($ordenar == 'estatus') {
		$pregn = $preg0 . "ORDER BY orden_estatus,orden_asesor_id,orden_id";
	} else {
		$pregn = $preg0 . "ORDER BY orden_id";
	}

	//	echo $preg0;
	$matr0 = mysql_query($pregn) or die("ERROR: Fallo selección de lapso! 787 " . $pregn);
	$filas = mysql_num_rows($matr0);

if ($accion==="graficas") {

	if ($f1125000 == '1' || $_SESSION['rol02']=='1') {
		// acceso autorizado

	//  ======================================  Operación =================================

	$mensaje = '';
	$error = 'si'; $num_cols = 0;

	$pregunta = "SELECT orden_estatus, COUNT(orden_id) FROM " . $dbpfx . "ordenes WHERE ";
	$pregunta .= $prega ;
	$pregunta .= " GROUP BY orden_estatus" ;
	//	echo $pregunta;
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo primera selección!");
	$filas = mysql_num_rows($matriz);
	$j=0;
	$fondo = 'claro';
	$data = array();
	$data2 = array();
	while($ord = mysql_fetch_array($matriz)) {
		$etiqueta = utf8_decode(constant('ORDEN_ESTATUS_' . $ord['orden_estatus']));
		$data[] = array($etiqueta, $ord['COUNT(orden_id)']);

		if($ord['orden_estatus'] < 3 || $ord['orden_estatus'] == 20 || $ord['orden_estatus'] == 17) { $doc_aut = $doc_aut + $ord['COUNT(orden_id)']; }
		elseif($ord['orden_estatus'] > 2 && $ord['orden_estatus'] < 12) { $proc_rep = $proc_rep + $ord['COUNT(orden_id)']; }
		elseif($ord['orden_estatus'] > 89) { $term = $term  + $ord['COUNT(orden_id)']; }
		else { $doc_ent = $doc_ent + $ord['COUNT(orden_id)']; }
	}

	$data2[] = array(utf8_decode('Documentación'), $doc_aut);
	$data2[] = array(utf8_decode('Reparación'), $proc_rep);
	$data2[] = array(utf8_decode('Entrega'), $doc_ent);
	$data2[] = array(utf8_decode('Terminadas'), $term);


	$pregunta1 = "SELECT orden_fecha_recepcion, orden_fecha_presupuesto, orden_fecha_proceso_inicio, orden_fecha_proceso_fin, orden_fecha_notificacion, orden_fecha_de_entrega, orden_estatus FROM " . $dbpfx . "ordenes WHERE ";
	$pregunta1 .= $prega ;
	//	echo $pregunta;
	$matriz1 = mysql_query($pregunta1) or die("ERROR: Fallo  segunda seleccion!");
	$filas = mysql_num_rows($matriz1);
	//	$c99=0; $max=0; $min=0;
	while($fech = mysql_fetch_array($matriz1)) {
		$estatus = $fech['orden_estatus'];
		foreach($fech as &$v) { $v = strtotime($v); }
		if($estatus==99) {
			$c99++;
	//			echo $fech['orden_fecha_proceso_fin'] . ' - ' . $fech['orden_fecha_de_entrega'] . ' - ';
			list($t99, $min99, $max99) = tiemposOT($fech['orden_fecha_recepcion'], $fech['orden_fecha_de_entrega'], $t99, $min99, $max99);
			list($te, $mine, $maxe) = tiemposOT($fech['orden_fecha_proceso_fin'], $fech['orden_fecha_de_entrega'], $te, $mine, $maxe);
			list($tr, $minr, $maxr) = tiemposOT($fech['orden_fecha_proceso_inicio'], $fech['orden_fecha_proceso_fin'], $tr, $minr, $maxr);
			list($ta, $mina, $maxa) = tiemposOT($fech['orden_fecha_presupuesto'], $fech['orden_fecha_proceso_inicio'], $ta, $mina, $maxa);
			list($tp, $minp, $maxp) = tiemposOT($fech['orden_fecha_recepcion'], $fech['orden_fecha_presupuesto'], $tp, $minp, $maxp);
	//			echo $min99 . ' - ' . $t99  . ' - ' . $max99 . ' - ' . $mine . ' - ' . $te . ' - ' . $maxe . '<br>';
		}
		if($estatus==98) {
			$c98++;
			list($t98, $min98, $max98) = tiemposOT($fech['orden_fecha_recepcion'], $fech['orden_fecha_de_entrega'], $t98, $min98, $max98);
			list($tpt, $minpt, $maxpt) = tiemposOT($fech['orden_fecha_presupuesto'], $fech['orden_fecha_de_entrega'], $tpt, $minpt, $maxpt);
			list($tp, $minp, $maxp) = tiemposOT($fech['orden_fecha_recepcion'], $fech['orden_fecha_presupuesto'], $tp, $minp, $maxp);
		}
		if($estatus==97) {
			$c97++;
			list($t97, $min97, $max97) = tiemposOT($fech['orden_fecha_recepcion'], $fech['orden_fecha_de_entrega'], $t97, $min97, $max97);
			list($tpd, $minpd, $maxpd) = tiemposOT($fech['orden_fecha_presupuesto'], $fech['orden_fecha_de_entrega'], $tpd, $minpd, $maxpd);
			list($tp, $minp, $maxp) = tiemposOT($fech['orden_fecha_recepcion'], $fech['orden_fecha_presupuesto'], $tp, $minp, $maxp);
		}
		if($estatus > 11 && $estatus < 17) {
			$cr++;
			list($tr, $minr, $maxr) = tiemposOT($fech['orden_fecha_proceso_inicio'], $fech['orden_fecha_proceso_fin'], $tr, $minr, $maxr);
			list($ta, $mina, $maxa) = tiemposOT($fech['orden_fecha_presupuesto'], $fech['orden_fecha_proceso_inicio'], $ta, $mina, $maxa);
			list($tp, $minp, $maxp) = tiemposOT($fech['orden_fecha_recepcion'], $fech['orden_fecha_presupuesto'], $tp, $minp, $maxp);
		}
		if($estatus > 2 && $estatus < 11) {
			$cp++;
			list($tp, $minp, $maxp) = tiemposOT($fech['orden_fecha_recepcion'], $fech['orden_fecha_presupuesto'], $tp, $minp, $maxp);
		}
	}
	$t99prom = $t99/$c99;
	$teprom = $te/$c99;
	$t98prom = $t98/$c98;
	$tptprom = $tpt/$c98;
	$t97prom = $t97/$c97;
	$tpdprom = $tpd/$c97;
	$trprom = $tr/($c99+$cr);
	$taprom = $ta/($c99+$cr);
	$tpprom = $tp/($c99+$c98+$c97+$cr+$cp);

	$data_t99 = array(
		array('May', 5, 7.69, 24.64, 37.01),
		array('Jun', 6, 5.87, 17.70, 31.20),
		array('Jul', 7, 5.99, 12.54, 21.25),
	);

	/*
	echo 'Cerradas: ' . round(($min99 / 86400), 2) . ', ' . round(($t99prom / 86400), 2) . ', ' . round(($max99 / 86400), 2) . '<br>';
	echo 'Pago de daños: ' . round(($min98 / 86400), 2) . ', ' . round(($t98prom / 86400), 2) . ', ' . round(($max98 / 86400), 2) . '<br>';
	echo 'Pérdida total: ' . round(($min97 / 86400), 2) . ', ' . round(($t97prom / 86400), 2) . ', ' . round(($max97 / 86400), 2) . '<br>';
	echo 'Etapa de entrega: ' . round(($mine / 86400), 2) . ', ' . round(($teprom / 86400), 2) . ', ' . round(($maxe / 86400), 2) . '<br>';
	echo 'Etapa de entrega PT: ' . round(($minpt / 86400), 2) . ', ' . round(($tptprom / 86400), 2) . ', ' . round(($maxpt / 86400), 2) . '<br>';
	echo 'Etapa de entrega PD: ' . round(($minpd / 86400), 2) . ', ' . round(($tpdprom / 86400), 2) . ', ' . round(($maxpd / 86400), 2) . '<br>';
	echo 'Etapa de reparación: ' . round(($minr / 86400), 2) . ', ' . round(($trprom / 86400), 2) . ', ' . round(($maxr / 86400), 2) . '<br>';
	echo 'Etapa de asignación: ' . round(($mina / 86400), 2) . ', ' . round(($taprom / 86400), 2) . ', ' . round(($maxa / 86400), 2) . '<br>';
	echo 'Etapa de documentación: ' . round(($minp / 86400), 2) . ', ' . round(($tpprom / 86400), 2) . ', ' . round(($maxp / 86400), 2) . '<br>';
	*/

	$preg0 = "SELECT orden_id, orden_presupuesto FROM " . $dbpfx . "ordenes WHERE ";
	$preg0 .= $prega ;

	$matr0 = mysql_query($preg0) or die("ERROR: Fallo tercera seleccion!");
	$filas0 = mysql_num_rows($matr0);
	$data3 = array();
	$data4 = array();
	$data5 = array();
	$mo = ''; $partes = ''; $consumibles = '';
	while($ord0 = mysql_fetch_array($matr0)) {
		/*		if(!is_null($ord0['orden_pres_mecanica'])) { $mecanica = $mecanica + $ord0['orden_pres_mecanica']; }
		if(!is_null($ord0['orden_pres_electrica'])) { $electrica = $electrica + $ord0['orden_pres_electrica']; }
		if(!is_null($ord0['orden_pres_hojalateria'])) { $hojalateria = $hojalateria + $ord0['orden_pres_hojalateria']; }
		if(!is_null($ord0['orden_pres_pintura'])) { $pintura = $pintura + $ord0['orden_pres_pintura']; }
		if(!is_null($ord0['orden_pres_accesorios'])) { $accesorios = $accesorios + $ord0['orden_pres_accesorios']; }
		*/		
		$preg1 = "SELECT sub_area, sub_partes, sub_consumibles, sub_mo FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord0['orden_id'] . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion!");
		$filas1 = mysql_num_rows($matr1);
		//		echo $preg1 . '<br>' . $filas1;
		while($sub1 = mysql_fetch_array($matr1)) {
			$mo = $mo + $sub1['sub_mo']; // echo 'mi mano de obra: ' . $mi_mo . '<br>';
			$partes = $partes + $sub1['sub_partes'];
			$consumibles = $consumibles + $sub1['sub_consumibles'];

			$por_area[$sub1['sub_area']][0] = $por_area[$sub1['sub_area']][0] + $sub1['sub_mo'];
			$por_area[$sub1['sub_area']][1] = $por_area[$sub1['sub_area']][1] + $sub1['sub_partes'];
			$por_area[$sub1['sub_area']][2] = $por_area[$sub1['sub_area']][2] + $sub1['sub_consumibles'];
		}
	}
	for($i=1; $num_areas_servicio > $i; $i++) {
		$por_area[$i][3] = $por_area[$i][0] + $por_area[$i][1] +$por_area[$i][2];
		$nombre_area = constant('NOMBRE_AREA_'.$i);
		//		echo $nombre_area . ' -> ' . $por_area[$i][3];
		$data3[] = array(utf8_decode($nombre_area), $por_area[$i][3]);
	}
	/*
	$data3[] = array(utf8_decode('Mecánica'), $por_area[1][3]);
	$data3[] = array(utf8_decode('Hojalatería'), $hojalateria);
	$data3[] = array(utf8_decode('Pintura'), $pintura);
	$data3[] = array(utf8_decode('Accesorios'), $accesorios);
	$data3[] = array(utf8_decode('Eléctrico'), $electrica);
	*/
	$data4[] = array(utf8_decode('MO'), $mo);
	$data4[] = array(utf8_decode('Refacciones'), $partes);
	$data4[] = array(utf8_decode('Consumibles'), $consumibles);

	foreach ($por_area as $l =>$w) {
		$nombre_area = constant('NOMBRE_AREA_'.$l);
		$data5[] = array(utf8_decode($nombre_area), $w[0], $w[1], $w[2]);
		//		print_r($w); echo '<br>';
	}

	//	print_r($data5); echo '<br>';


	# PHPlot Example: Pie/text-data-single
	require_once ('parciales/phplot.php');

	//  ======================================  OTs ======================================

	$plot = new PHPlot(500,300);
	$plot->SetFailureImage(False); // No error images
	$plot->SetPrintImage(False); // No automatic output
	$plot->SetImageBorderType('plain');
	$plot->SetPlotType('bars');
	$plot->SetDataType('text-data');
	$plot->SetDataValues($data);
	//	$plot->SetDataColors(array('red', 'green', 'blue', 'yellow', 'cyan', 'magenta', 'brown', 'lavender', 'pink', 'gray', 'orange'));
	$plot->SetPlotAreaWorld(NULL, 0, NULL, NULL);
	$plot->SetYLabelType('data');
	$plot->SetXLabelAngle('90');
	$plot->SetTitle($t_grafica1);
	$plot->DrawGraph();

	$pastel = new PHPlot(500,300);
	$pastel->SetFailureImage(False); // No error images
	$pastel->SetPrintImage(False); // No automatic output
	$pastel->SetImageBorderType('plain'); // Improves presentation in the manual
	$pastel->SetPlotType('pie');
	$pastel->SetDataType('text-data-single');
	$pastel->SetDataValues($data2);
	$pastel->SetTitle($t_grafica2);
	//	$pastel->SetPieLabelType(array('percent', 'label'), 'custom', 'mycallback');
	foreach ($data2 as $row) {
		$pastel->SetLegend(implode(': ', $row));
	}
	# Place the legend in the upper left corner:
	$pastel->SetLegendPixels(5,150);
	$pastel->DrawGraph();

	//  ======================================  Ventas ====================================

	$vent_glob = new PHPlot(500,300);
	$vent_glob->SetFailureImage(False); // No error images
	$vent_glob->SetPrintImage(False); // No automatic output
	$vent_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$vent_glob->SetPlotType('pie');
	$vent_glob->SetDataType('text-data-single');
	$vent_glob->SetDataValues($data3);
	$vent_glob->SetTitle($t_grafica3);
	$vent_glob->SetPieLabelType(array('label','value'), 'custom', 'dinero');
	# Place the legend in the upper left corner:
	$vent_glob->SetLegendPixels(5,5);

	$vent_glob->DrawGraph();

	$vent_gtip = new PHPlot(500,300);
	$vent_gtip->SetFailureImage(False); // No error images
	$vent_gtip->SetPrintImage(False); // No automatic output
	$vent_gtip->SetImageBorderType('plain'); // Improves presentation in the manual
	$vent_gtip->SetPlotType('pie');
	$vent_gtip->SetDataType('text-data-single');
	$vent_gtip->SetDataValues($data4);
	$vent_gtip->SetTitle($t_grafica4);
	$vent_gtip->SetPieLabelType(array('label','value'), 'custom', 'dinero');
	$vent_gtip->DrawGraph();

	$vent_atip = new PHPlot(500,300);
	$vent_atip->SetFailureImage(False); // No error images
	$vent_atip->SetPrintImage(False); // No automatic output
	$vent_atip->SetImageBorderType('plain'); // Improves presentation in the manual
	$vent_atip->SetPlotType('stackedbars');
	$vent_atip->SetDataType('text-data');
	$vent_atip->SetDataValues($data5);
	$vent_atip->SetTitle($t_grafica5);
	$vent_atip->SetLegend(array('MO', 'Refacciones', 'Consumibles'));
	$vent_atip->SetXTickLabelPos('none');
	$vent_atip->SetXTickPos('none');
	//	$vent_atip->SetLegendPixels(70,50);
	$vent_atip->SetLegendReverse(True);
	$vent_atip->SetYDataLabelPos('plotstack');

	$vent_atip->DrawGraph();


	//  ======================================  Operaciones ======================================

	$t99_glob = new PHPlot(500,300);
	$t99_glob->SetFailureImage(False); // No error images
	$t99_glob->SetPrintImage(False); // No automatic output
	$t99_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$t99_glob->SetPlotType('linepoints');
	$t99_glob->SetDataType('data-data');
	$t99_glob->SetDataValues($data_t99);
	$t99_glob->SetTitle("Tiempo Global de Proceso\nOrden de Trabajo Entregada");
	$t99_glob->SetYTitle(utf8_decode('Días'));
	$t99_glob->SetYDataLabelPos('plotin');
	$t99_glob->SetYTickLabelPos('none');
	$t99_glob->SetYTickPos('none');
	$t99_glob->SetDrawYGrid(False);
	$t99_glob->SetXTickPos('none');
	$t99_glob->SetXTickLabelPos('none');

	$t99_glob->SetLegend(array('Minimo', 'Promedio', 'Maximo'));
	//	$t99_glob->SetPieLabelType(array('label','value'), 'custom', 'dinero');
	# Place the legend in the upper left corner:
	//	$t99_glob->SetLegendPixels(5,5);
	$t99_glob->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$t99_glob->DrawGraph();


	$data_t98 = array(
		array('May', 5, 2.1, 11.95, 25.91),
		array('Jun', 6, 2.66, 7.93, 18.97),
		array('Jul', 7, 7.16, 12.39, 18.24),
	);

	$t98_glob = new PHPlot(500,300);
	$t98_glob->SetFailureImage(False); // No error images
	$t98_glob->SetPrintImage(False); // No automatic output
	$t98_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$t98_glob->SetPlotType('linepoints');
	$t98_glob->SetDataType('data-data');
	$t98_glob->SetDataValues($data_t98);
	$t98_glob->SetTitle(utf8_decode("Tiempo Global de Proceso\nPago de Daños"));
	$t98_glob->SetYTitle(utf8_decode('Días'));
	$t98_glob->SetYDataLabelPos('plotin');
	$t98_glob->SetYTickLabelPos('none');
	$t98_glob->SetYTickPos('none');
	$t98_glob->SetDrawYGrid(False);
	$t98_glob->SetXTickPos('none');
	$t98_glob->SetXTickLabelPos('none');

	$t98_glob->SetLegend(array('Minimo', 'Promedio', 'Maximo'));
	$t98_glob->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$t98_glob->DrawGraph();


	$data_t97 = array(
		array('May', 5, 4.3, 22, 44.69),
		array('Jun', 6, 12.19, 17.58, 22.97),
		array('Jul', 7, 1.96, 11.23, 22.38),
	);

	$t97_glob = new PHPlot(500,300);
	$t97_glob->SetFailureImage(False); // No error images
	$t97_glob->SetPrintImage(False); // No automatic output
	$t97_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$t97_glob->SetPlotType('linepoints');
	$t97_glob->SetDataType('data-data');
	$t97_glob->SetDataValues($data_t97);
	$t97_glob->SetTitle(utf8_decode("Tiempo Global de Proceso\nPérdida Total"));
	$t97_glob->SetYTitle(utf8_decode('Días'));
	$t97_glob->SetYDataLabelPos('plotin');
	$t97_glob->SetYTickLabelPos('none');
	$t97_glob->SetYTickPos('none');
	$t97_glob->SetDrawYGrid(False);
	$t97_glob->SetXTickPos('none');
	$t97_glob->SetXTickLabelPos('none');

	$t97_glob->SetLegend(array('Minimo', 'Promedio', 'Maximo'));
	$t97_glob->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$t97_glob->DrawGraph();


	$data_te = array(
		array('May', 5, 0.01, 3.52, 8.24),
		array('Jun', 6, 0.01, 1.76, 14.21),
		array('Jul', 7, 0.01, 1.35, 8.09),
	);

	$te_glob = new PHPlot(500,300);
	$te_glob->SetFailureImage(False); // No error images
	$te_glob->SetPrintImage(False); // No automatic output
	$te_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$te_glob->SetPlotType('linepoints');
	$te_glob->SetDataType('data-data');
	$te_glob->SetDataValues($data_te);
	$te_glob->SetTitle(utf8_decode("Tiempo en Etapa de Entrega\nAutos Terminados"));
	$te_glob->SetYTitle(utf8_decode('Días'));
	$te_glob->SetYDataLabelPos('plotin');
	$te_glob->SetYTickLabelPos('none');
	$te_glob->SetYTickPos('none');
	$te_glob->SetDrawYGrid(False);
	$te_glob->SetXTickPos('none');
	$te_glob->SetXTickLabelPos('none');

	$te_glob->SetLegend(array('Minimo', 'Promedio', 'Maximo'));
	$te_glob->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$te_glob->DrawGraph();


	$data_te98 = array(
		array('May', 5, 1.37, 17.08, 40.85),
		array('Jun', 6, 9.21, 11.72, 14.22),
		array('Jul', 7, 0.87, 7.85, 17.11),
	);

	$te98_glob = new PHPlot(500,300);
	$te98_glob->SetFailureImage(False); // No error images
	$te98_glob->SetPrintImage(False); // No automatic output
	$te98_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$te98_glob->SetPlotType('linepoints');
	$te98_glob->SetDataType('data-data');
	$te98_glob->SetDataValues($data_te98);
	$te98_glob->SetTitle(utf8_decode("Tiempo en Etapa de Entrega\nPago de Daños"));
	$te98_glob->SetYTitle(utf8_decode('Días'));
	$te98_glob->SetYDataLabelPos('plotin');
	$te98_glob->SetYTickLabelPos('none');
	$te98_glob->SetYTickPos('none');
	$te98_glob->SetDrawYGrid(False);
	$te98_glob->SetXTickPos('none');
	$te98_glob->SetXTickLabelPos('none');

	$te98_glob->SetLegend(array('Minimo', 'Promedio', 'Maximo'));
	$te98_glob->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$te98_glob->DrawGraph();


	$data_te97 = array(
		array('May', 5, 0.2, 8.77, 22.91),
		array('Jun', 6, 1.18, 6.3, 14.98),
		array('Jul', 7, 3.9, 8.44, 16.92),
	);

	$te97_glob = new PHPlot(500,300);
	$te97_glob->SetFailureImage(False); // No error images
	$te97_glob->SetPrintImage(False); // No automatic output
	$te97_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$te97_glob->SetPlotType('linepoints');
	$te97_glob->SetDataType('data-data');
	$te97_glob->SetDataValues($data_te97);
	$te97_glob->SetTitle(utf8_decode("Tiempo en Etapa de Entrega\nPérdida Total"));
	$te97_glob->SetYTitle(utf8_decode('Días'));
	$te97_glob->SetYDataLabelPos('plotin');
	$te97_glob->SetYTickLabelPos('none');
	$te97_glob->SetYTickPos('none');
	$te97_glob->SetDrawYGrid(False);
	$te97_glob->SetXTickPos('none');
	$te97_glob->SetXTickLabelPos('none');

	$te97_glob->SetLegend(array('Minimo', 'Promedio', 'Maximo'));
	$te97_glob->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$te97_glob->DrawGraph();


	$data_tr = array(
		array('May', 5, 0, 3.74, 18.75),
		array('Jun', 6, 0, 6.49, 25.33),
		array('Jul', 7, 0, 2.6, 6.1),
	);

	$tr_glob = new PHPlot(500,300);
	$tr_glob->SetFailureImage(False); // No error images
	$tr_glob->SetPrintImage(False); // No automatic output
	$tr_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$tr_glob->SetPlotType('linepoints');
	$tr_glob->SetDataType('data-data');
	$tr_glob->SetDataValues($data_tr);
	$tr_glob->SetTitle(utf8_decode("Tiempo en Etapa de Reparación"));
	$tr_glob->SetYTitle(utf8_decode('Días'));
	$tr_glob->SetYDataLabelPos('plotin');
	$tr_glob->SetYTickLabelPos('none');
	$tr_glob->SetYTickPos('none');
	$tr_glob->SetDrawYGrid(False);
	$tr_glob->SetXTickPos('none');
	$tr_glob->SetXTickLabelPos('none');

	$tr_glob->SetLegend(array('Minimo', 'Promedio', 'Maximo'));
	$tr_glob->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$tr_glob->DrawGraph();


	$data_ta = array(
		array('May', 5, 0, 12.34, 21.87),
		array('Jun', 6, 0.84, 7.8, 27.79),
		array('Jul', 7, 0.03, 4.66, 14.54),
	);

	$ta_glob = new PHPlot(500,300);
	$ta_glob->SetFailureImage(False); // No error images
	$ta_glob->SetPrintImage(False); // No automatic output
	$ta_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$ta_glob->SetPlotType('linepoints');
	$ta_glob->SetDataType('data-data');
	$ta_glob->SetDataValues($data_ta);
	$ta_glob->SetTitle(utf8_decode("Tiempo en Etapa de Autorizados\nantes de reparación"));
	$ta_glob->SetYTitle(utf8_decode('Días'));
	$ta_glob->SetYDataLabelPos('plotin');
	$ta_glob->SetYTickLabelPos('none');
	$ta_glob->SetYTickPos('none');
	$ta_glob->SetDrawYGrid(False);
	$ta_glob->SetXTickPos('none');
	$ta_glob->SetXTickLabelPos('none');

	$ta_glob->SetLegend(array('Minimo', 'Promedio', 'Maximo'));
	$ta_glob->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$ta_glob->DrawGraph();


	$data_tp = array(
		array('May', 5, 1.03, 4.55, 25.98),
		array('Jun', 6, 0.13, 2.4, 8.75),
		array('Jul', 7, 0.05, 3.97, 13.04),
	);

	$tp_glob = new PHPlot(500,300);
	$tp_glob->SetFailureImage(False); // No error images
	$tp_glob->SetPrintImage(False); // No automatic output
	$tp_glob->SetImageBorderType('plain'); // Improves presentation in the manual
	$tp_glob->SetPlotType('linepoints');
	$tp_glob->SetDataType('data-data');
	$tp_glob->SetDataValues($data_tp);
	$tp_glob->SetTitle(utf8_decode("Tiempo en Etapa de Autorización"));
	$tp_glob->SetYTitle(utf8_decode('Días'));
	$tp_glob->SetYDataLabelPos('plotin');
	$tp_glob->SetYTickLabelPos('none');
	$tp_glob->SetYTickPos('none');
	$tp_glob->SetDrawYGrid(False);
	$tp_glob->SetXTickPos('none');
	$tp_glob->SetXTickLabelPos('none');

	$tp_glob->SetLegend(array('Minimo', 'Promedio', 'Maximo'));
	$tp_glob->SetPlotAreaWorld(NULL, 0, NULL, NULL);

	$tp_glob->DrawGraph();

	//  ======================================  Gráficas ===================================

	echo '	<table cellpadding="3" cellspacing="1" border="0" width="100%">';
	echo '		<tr class="cabeza_tabla"><td colspan="2">
				<table cellpadding="0" cellspacing="0" border="0" width="70%">
					<tr><td><a href="reportes.php?accion=graficas&periodo=estemes">Gráficas de este mes</a></td>
						<td><a href="reportes.php?accion=graficas&periodo=mespasado">Gráficas del mes pasado</a></td>
						<td><a href="reportes.php?accion=graficas&periodo=30dias">Gráficas de los útimos 30 días</a></td>
						<td><a href="reportes.php?accion=graficas&periodo=60dias">Gráficas de los útimos 60 días</a></td>
					</tr>
				</table>
			</td>
		</tr>'."\n";
	/*	echo '		<tr><td><img src="' . $plot->EncodeImage() . '" alt="gráficas"></td>
			<td><img src="' . $pastel->EncodeImage() . '" alt="gráficas"></td></tr>';
	//	echo '		<tr><td>&nbsp;</td></tr>'."\n";
	*/	
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '		<tr><td><img src="' . $vent_glob->EncodeImage() . '" alt="gráficas"></td>
			<td><img src="' . $vent_gtip->EncodeImage() . '" alt="gráficas"></td></tr>';
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '		<tr><td><img src="' . $vent_atip->EncodeImage() . '" alt="gráficas"></td>
			<td><img src="' . $t99_glob->EncodeImage() . '" alt="gráficas"></td></tr>';
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '		<tr><td><img src="' . $t98_glob->EncodeImage() . '" alt="gráficas"></td>
			<td><img src="' . $t97_glob->EncodeImage() . '" alt="gráficas"></td></tr>';
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '		<tr><td><img src="' . $te_glob->EncodeImage() . '" alt="gráficas"></td>
			<td><img src="' . $te98_glob->EncodeImage() . '" alt="gráficas"></td></tr>';
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '		<tr><td><img src="' . $te97_glob->EncodeImage() . '" alt="gráficas"></td>
			<td><img src="' . $tr_glob->EncodeImage() . '" alt="gráficas"></td></tr>';
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '		<tr><td><img src="' . $ta_glob->EncodeImage() . '" alt="gráficas"></td>
			<td><img src="' . $tp_glob->EncodeImage() . '" alt="gráficas"></td></tr>';
	echo '	</table>'."\n";

	//	echo '<img src="' . $plot->EncodeImage() . '" alt="gráficas"><br><img src="' . $pastel->EncodeImage() . '" alt="gráficas"><br><img src="' . $vent_glob->EncodeImage() . '" alt="gráficas"><br><img src="' . $vent_gtip->EncodeImage() . '" alt="gráficas"><br><img src="' . $vent_atip->EncodeImage() . '" alt="gráficas">';

	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}

if ($accion==="tabla") {

	$f1125005 = validaAcceso('1125005', $dbpfx);

	if ($f1125005 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1') {

	$preg0 = "SELECT o.orden_id, o.orden_estatus, o.orden_categoria, o.orden_servicio, o.orden_ubicacion, o.orden_asesor_id, o.orden_ref_pendientes, o.orden_vehiculo_id, o.orden_alerta, o.orden_fecha_recepcion, o.orden_fecha_de_entrega, o.orden_fecha_promesa_de_entrega, o.orden_fecha_ultimo_movimiento, o.orden_grua";
	if($confolio == 1 && $id == 17) {
		$preg0 .= ", o.oid, o.orden_vehiculo_tipo, o.orden_vehiculo_placas ";
	}

	$preg0 .= " FROM " . $dbpfx . "ordenes o";

	if(isset($area) && $area != '') {
		$preg0 .= ", " . $dbpfx . "subordenes s";
	}

	$preg0 .= " WHERE ";

	// Tabla llamada por estatus

	if(isset($id) && $id != '') {
		if($id == '4') {
			$preg0 .= "o.orden_estatus = '$id' AND o.orden_ubicacion != 'Transito' ";
		} else {
			$preg0 .= "o.orden_estatus = '$id' ";
		}
	}

	if(isset($pp) && $pp != '') {
		$preg0 .= "o.orden_ref_pendientes > '0' ";
	}

	if(isset($otsr) && $otsr != '') {
		$preg0 .= "o.orden_estatus >= '30' AND o.orden_estatus <= '39' ";
	}

	if(isset($of) || isset($osf)) {
		$preg0 .= "o.orden_estatus < '30' OR o.orden_estatus = '99' ";
	}

	if(isset($rit) && $rit != '') {
		$preg0 .= "o.orden_estatus = '4' AND o.orden_ubicacion = 'Transito' ";
		$encabezado = ' OTs Autorizadas en Tránsito.';
	}

	if(isset($otmf) && $otmf != '') {
		$preg0 .= "o.orden_estatus < '90' AND o.orden_ubicacion = 'Transito' ";
		$encabezado = ' OTs en Tránsito.';
	}

	if(isset($otm) && $otm != '') {
		$preg0 .= "o.orden_estatus < '90' AND o.orden_ubicacion != 'Transito' ";
		$larubi = count($ubicaciones);
		for($idxu = 1; $idxu <= $larubi; $idxu++ ) {
			$preg0 .= " AND o.orden_ubicacion != '" . $ubicaciones[$idxu] . "' ";
		}
		$encabezado = ' OTs en Taller.';
	}

	if(isset($ottp) && $ottp != '') {
		$preg0 .= "o.orden_estatus < '90' ";
		$encabezado = ' OTs Abiertas.';
	}

	if(isset($ubc) && $ubc != '') {
		$preg0 .= "o.orden_estatus < '90' AND o.orden_ubicacion = '$ubicaciones[$ubc]' ";
		$encabezado = ' OTs en ' . $ubicaciones[$ubc];
	}

	if(isset($area) && $area != '') {
		$preg0 .= " s.sub_area = '$area' AND s.orden_id = o.orden_id AND s.sub_estatus > '106' AND s.sub_estatus < '112' ";
	}


	if($n=='5') {
			$dias = (time() - 1641600);
			$fefin = date('Y-m-d H:i:s', $dias);
			$preg0 .= "AND o.orden_fecha_recepcion <= '$fefin' ";
	} elseif($n=='4') {
			$dias = (time() - 1641600);
			$feini = date('Y-m-d H:i:s', $dias);
			$dias = (time() - 1036800);
			$fefin = date('Y-m-d H:i:s', $dias);
			$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' AND o.orden_fecha_recepcion <= '$fefin' ";
	} elseif($n=='3') {
			$dias = (time() - 1036800);
			$feini = date('Y-m-d H:i:s', $dias);
			$dias = (time() - 518400);
			$fefin = date('Y-m-d H:i:s', $dias);
			$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' AND o.orden_fecha_recepcion <= '$fefin' ";
	} elseif($n=='2') {
			$dias = (time() - 518400);
			$feini = date('Y-m-d H:i:s', $dias);
			$dias = (time() - 259200);
			$fefin = date('Y-m-d H:i:s', $dias);
			$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' AND o.orden_fecha_recepcion <= '$fefin' ";
	} elseif($n=='1') {
			$dias = (time() - 259200);
			$feini = date('Y-m-d H:i:s', $dias);
			$dias = (time() - 86400);
			$fefin = date('Y-m-d H:i:s', $dias);
			$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' AND o.orden_fecha_recepcion <= '$fefin' ";
	} elseif($n=='0') {
			$dias = (time() - 86400);
			$feini = date('Y-m-d H:i:s', $dias);
			$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' ";
	} elseif($n=='d') {
			$dias = (time() - 518400);
			$fefin = date('Y-m-d H:i:s', $dias);
			$preg0 .= "AND o.orden_fecha_recepcion <= '$fefin' ";
	} elseif($n=='a') {
			$dias = (time() - 518400);
			$feini = date('Y-m-d H:i:s', $dias);
			$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' ";
	}

	$estemes = date('n');
	$year = date('Y');
	if(isset($mfe) && $mfe <= 3) { $mes = $mfe; }
	elseif(isset($mfr) && $mfr <= 3) { $mes = $mfr; }
	elseif($mfr > 2) { $mes = 2; }
	elseif($mfe > 3 ) { $mes = 3; }
	$elmes = $estemes - $mes;
	if($elmes < 1) { $year = $year -1; $elmes = $elmes + 12; }


	if(isset($mfe) && $mfe != '') {
		if($mfe < 3) {
			$feini = $year.'-'.$elmes.'-01 00:00:00';
			$fefin = strtotime($feini);
			$fefin = date('Y-m-t 23:59:59', $fefin);
		} else {
			$feini = '2013-01-01 00:00:00';
			$fefin = $year.'-'.$elmes.'-01 00:00:00';
			$fefin = strtotime($fefin);
			$fefin = date('Y-m-t 23:59:59', $fefin);
		}
		if($mfe > '3') {
				$preg0 .= "AND o.orden_fecha_de_entrega <= '$fefin' ";
		} elseif($mfe <= '3' && $mfe >= '1') {
				$preg0 .= "AND o.orden_fecha_de_entrega >= '$feini' AND o.orden_fecha_de_entrega <= '$fefin' ";
		} else {
				$preg0 .= "AND o.orden_fecha_de_entrega >= '$feini' ";
		}
	} elseif(isset($mes) && $mes != '') {
		if($mes <= 2) {
			$feini = $year.'-'.$elmes.'-01 00:00:00';
			$fefin = strtotime($feini);
			$fefin = date('Y-m-t 23:59:59', $fefin);
		} else {
			$feini = '2013-01-01 00:00:00';
			$fefin = $year.'-'.$elmes.'-01 00:00:00';
			$fefin = strtotime($fefin);
			$fefin = date('Y-m-t 23:59:59', $fefin);
		}

		if($mes >= '3') {
			if($id == 16){
				$preg0 .= "AND o.orden_fecha_de_entrega <= '$fefin' ";
			} else{
				$preg0 .= "AND o.orden_fecha_recepcion <= '$fefin' ";
			}
		} elseif($mes <= '2' && $mes >= '1') {
			if($id == 16){
				$preg0 .= "AND o.orden_fecha_de_entrega >= '$feini' AND o.orden_fecha_de_entrega <= '$fefin' ";
			} else{
				$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' AND o.orden_fecha_recepcion <= '$fefin' ";
			}
		} else {
			if($id == 16){
				$preg0 .= "AND o.orden_fecha_de_entrega >= '$feini' ";
			} else{
				$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' ";
			}
		}
	}

	//	$preg0 .= "ORDER BY orden_vehiculo_tipo, orden_vehiculo_color";
	if($ordenar == 'asesor') {
		$preg0 .= "ORDER BY o.orden_asesor_id,o.orden_estatus,o.orden_id";
	} else {
		$preg0 .= "ORDER BY o.orden_id";
	}
	//echo $preg0;

		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección! " . $preg0);
		$filas = mysql_num_rows($matr0);
		if(isset($of) || isset($osf)) {
			$preg4 = "SELECT orden_id, fact_id, fact_cobrada, fact_num, fact_monto, fact_impuesto FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_cobrada < 2 AND fact_tipo < 3";
			$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de facturas! ".$preg4);
		}

	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}


	if ($f1125005 == '1' || $f1125010 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {

		echo '
<div class="page-content">
	<div class="row"> <!-box header del título. -->
		<div class="col-md-12">
			<div class="content-box-header">
				<div class="panel-title">
	  				<h2>' . $filas . $encabezado . '</h2>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 ">
			<div id="content-tabla">
				<table cellspacing="0" class="table-new">
				<tr>
					<th>'."\n";

		if($confolio == 1 && $id == 17) {
			echo '
						Provisional'."\n";
		} else {
			echo '
						OT'."\n";
		}

		echo '
					</th>
					<th>
						Vehículo
					</th>
					<th>
						' . $lang['Placa'] . '
					</th>
					<th>
						Categoría de Servicio
					</th>
					<th>
						Tipo de Servicio
					</th>
					<th>
						Cliente
					</th>
					<th>
						Siniestro
					</th>
					<th>
						Grúa
					</th>
					<th>
						Estatus
					</th>
					<th>'."\n";

		if($ordenar == '' || !isset($ordenar)) {
			echo '
						<a href="' . $_SERVER['REQUEST_URI'] . '&ordenar=asesor">Asesor</a>'."\n";
		} elseif(isset($of) || isset($osf)) {
			echo '
						Factura'."\n";
		} else {
			echo '
						Asesor'."\n";
		}

		echo '
					</th>
					<th>'."\n";

		if(isset($of) || isset($osf)) {
			echo '
						Fecha Entregado'."\n";
		} else {
			echo '
						Fecha Recibido'."\n";
		}

		echo '
					</th>
					<th>
						' . $lang['FechaPromesa'] . '
					</th>
					<th>
						Días en proceso
					</th>'."\n";
		
		if($lista_operadores == 1){
						
			echo '
					<th>
						Hojalatero(s)
					</th>
					<th>
						Pintor(es)
					</th>' . "\n";

		}
		
		echo '
				</tr>' . "\n";

		$fondo = 'claro';
		$j = 0;
		$hoy = strtotime(date('Y-m-d 23:59:59'));
		while($ord = mysql_fetch_array($matr0)) {

			$oculta = 'no';
			$preg2 = "SELECT sub_reporte, sub_aseguradora, sub_area, sub_operador FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' ";
			if($asegflt != '') {
				$preg2 .= " AND sub_aseguradora = '" . $asegflt . "' ";
			}
			$preg2 .= " GROUP BY sub_reporte";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de subordenes!".$preg2);
			$fila2 = mysql_num_rows($matr2);
			if($asegflt != '' && $fila2 == 0 && $ord['orden_estatus'] != '17' && $ord['orden_estatus'] != '1' && $ord['orden_estatus'] != '90') {
				$oculta = 'si';
			}
			
			if($lista_operadores == 1){
				
				// --- Consultar las tareas de la orden ---
				if($oculta == 'no'){
					$preg_tareas = "SELECT sub_reporte, sub_aseguradora, sub_area, sub_operador FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' ";
					$matr_tareas = mysql_query($preg_tareas) or die("ERROR: Fallo selección de subordenes! ".$preg_tareas);
					
					unset($hojalateros);
					unset($pintores);
					// --- Agrupar operadores ---
					while($consulta_aseg = mysql_fetch_array($matr_tareas)){
						if($consulta_aseg['sub_area'] == 6){ // --- Hojalatería ---
							$hojalateros[$consulta_aseg['sub_operador']] = 1;
						}
						if($consulta_aseg['sub_area'] == 7){ // --- Pintura ---
							$pintores[$consulta_aseg['sub_operador']] = 1;
						}
					}
					// ---- Recorrer Hojalateros ---
					$texto_hojalateros = '';
					foreach($hojalateros as $key => $val){
					// --- Consultar nombre del operador en curso ---
					$preg_operador = " SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '" . $key . "'";
					$matr_operador = mysql_query($preg_operador) or die ($preg_operador);
					$operador = mysql_fetch_assoc($matr_operador);
					//echo $preg_operador;
					$texto_hojalateros .= $operador['nombre'] . ' ' . $operador['apellidos'] . ', ';
				}
				// ---- Recorrer Pintores ---
				$texto_pintores = '';
				foreach($pintores as $key => $val){
					// --- Consultar nombre del operador en curso ---
					$preg_operador = " SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '" . $key . "'";
					$matr_operador = mysql_query($preg_operador) or die ($preg_operador);
					$operador = mysql_fetch_assoc($matr_operador);
					//echo $preg_operador;
					$texto_pintores .= $operador['nombre'] . ' ' . $operador['apellidos'] . ', ';
				}
				}
				
				
			}
			

			if($oculta == 'no') {
				if(isset($of) || isset($osf)) {
					$ocu = 0;
					mysql_data_seek($matr4, 0);
					while($fact = mysql_fetch_array($matr4)) {
						if($ord['orden_id'] == $fact['orden_id']) {
							if($fact['fact_cobrada'] == '1' && $of == '1') {
								$ordfac[$ord['orden_id']][] = array(
									'fact_id' => $fact['fact_id'],
									'fact_num' => $fact['fact_num']);
									$ocu = 1;
							} elseif($fact['fact_cobrada'] == '0' && $of == '0') {
								$ordfac[$ord['orden_id']][] = array(
									'fact_id' => $fact['fact_id'],
									'fact_num' => $fact['fact_num']);
								$ocu = 1;
							}
						} elseif($osf == '1') {
							$ocu = 1;
						}
					}
					if($ocu == '0') {
						continue ;
					}
				}

				$fde = strtotime($ord['orden_fecha_de_entrega']);
				if($fde > strtotime('2012-01-01')) {
					$dias = intval(($fde - strtotime($ord['orden_fecha_recepcion'])) / 86400) + 1;
				} elseif($ord['orden_estatus'] == '90') {
					$dias = intval((strtotime($ord['orden_fecha_ultimo_movimiento']) - strtotime($ord['orden_fecha_recepcion'])) / 86400);
				} else {
					$dias = intval(($hoy - strtotime($ord['orden_fecha_recepcion'])) / 86400) + 1;
				}
				if(!isset($estanciamax) || $estanciamax == '') { $estanciamax = '20'; }
				if($dias > $estanciamax) { $dias = '<span style="font-weight:bold; color:red; background-color:yellow;">'.$dias.'</span>'; }
				$vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);

				echo '				
				<tr class="' . $fondo . '">
					<td style="padding-left:10px; padding-right:10px; text-align:center;"><a href="ordenes.php?accion=consultar&';
				if($confolio == 1 && $id == 17) {
					echo 'oid=' . $ord['oid'];
				} else {
					echo 'orden_id=' . $ord['orden_id'];
				}
				echo '">';

				if($confolio == 1 && $id == 17) {
					echo $ord['oid'];
				} else {
					echo $ord['orden_id'];
				}
				echo '</a></td>
					<td style="text-align: left !important;" padding-right:10px;">';
				if($confolio == 1 && $id == 17) {
					echo strtoupper($ord['orden_vehiculo_tipo']) . '</td>
					<td style="text-align: left !important;" padding-right:10px;">' . $ord['orden_vehiculo_placas'] . '</td>'."\n";
				} else {
					echo strtoupper($vehiculo['marca']) . ' ' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']) . '</td>
					<td style="text-align: left !important;">' . $vehiculo['placas'] . '</td>'."\n";
				}
				echo '					<td>' . constant('CATEGORIA_DE_REPARACION_' . $ord['orden_categoria']) . '</td>
					<td>' . constant('ORDEN_SERVICIO_' . $ord['orden_servicio']) . '</td>'."\n";
				$preg1 = "SELECT sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' GROUP BY sub_reporte";
				$matr1 = mysql_query($preg1) or die($preg1);
				$reporte = array(); $ima = array();
				while($aseico = mysql_fetch_assoc($matr1)) {
					$ima[$aseico['sub_aseguradora']] = [$ase[$aseico['sub_aseguradora']][0], $ase[$aseico['sub_aseguradora']][1]];
					$reporte[$aseico['sub_reporte']] = 1;
				}
				echo '					<td>';
				foreach($ima as $k => $v) {
					echo $v[1] . ' <img src="' . $v[0] . '" alt="" height="16" > ';
				}
				echo '</td>
					<td>';
				foreach($reporte as $k => $v) {
					if($k != '' && $k != "0") { echo $k . ' '; }
				}
				echo '</td>
					<td style="text-align:center;">' . $lang_grua[$ord['orden_grua']] . '</td>
					<td style="padding-left:10px; padding-right:10px;">' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '';
				if(($t==1 && $est_trans == 1) || $ord['orden_ubicacion'] == 'Transito') {
					//echo constant('ORDEN_ESTATUS_T_' . $ord['orden_estatus']) ;
					echo ' en Tránsito.';
				}
				if($ord['orden_ref_pendientes'] == '2') {
					echo ' ' . REFACCIONES_ESTRUCTURALES;
				} elseif($ord['orden_ref_pendientes'] == '1') {
					echo ' ' . REFACCIONES_PENDIENTES;
				} else {
					//echo '<br>Refacciones Completas';
				}

				echo '</td>
					<td>';
				if(isset($of) || isset($osf)) {
					foreach($ordfac as $k => $v) {
						foreach($v as $w) {
							echo '<a href="entrega.php?accion=cobros&orden_id=' . $k . '">' . $w['fact_num'] . '</a> ';
						}
					}
				} else {
					$asesor_id[$ord['orden_asesor_id']]++;
					echo $usuario[$ord['orden_asesor_id']];
					if($ordenar == 'asesor') { echo ' +' . $asesor_id[$ord['orden_asesor_id']]; }
				}
				$fpe = strtotime($ord['orden_fecha_promesa_de_entrega']);
				if($fpe > strtotime('2012-01-01')) {
					$fpe =  date('Y-m-d', $fpe);
				} else {
					$fpe = $lang['Sin Fecha'];
				}
				echo '</td>
					<td style="padding-left:10px; padding-right:10px;">' . date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])) . '</td>
					<td style="padding-left:10px; padding-right:10px;">' . $fpe . '</td>
					<td align="center">'.$dias.'</td>'."\n";
				if($lista_operadores == 1){ // --- Variable declarada en la tabla de valores ---
					echo '					<td>' . $texto_hojalateros . '</td>
					<td>' . $texto_pintores . '</td>'."\n";
				}
				echo '				</tr>';
				if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
			}
		}
		echo '				</table>
			</div>
		</div>
	</div>
</div>'."\n";
	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta. Reportes o Tablas.</p>';
	}
	
}

if ($accion==="reportes") {

	//---- Include del reporte ----
	include('parciales/reportes/ingresos.php');
}

if ($accion==="entregados") {

	if ($f1125015 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1') {

	$preg1 = "SELECT orden_id, orden_estatus, orden_vehiculo_placas, orden_vehiculo_tipo, orden_vehiculo_color, orden_vehiculo_id, orden_alerta, orden_fecha_recepcion, orden_fecha_de_entrega, orden_fecha_ultimo_movimiento FROM " . $dbpfx . "ordenes WHERE ";
	$preg1 .= " orden_fecha_de_entrega > '" . $feini . "' AND orden_fecha_de_entrega < '" . $fefin . "' ";
	$preg1 .= "ORDER BY orden_id";
	//	echo $preg0;
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de lapso! 1839 " . $preg1);
	$filas = mysql_num_rows($matr1);

	echo '			<table cellspacing="1" cellpadding="2" border="0">' . "\n";
	echo '				<tr class="cabeza_tabla"><td colspan="7" style="text-align: right;">' . $filas . $encabezado . '</td></tr>' . "\n";
	echo '				<tr><td>OT</td><td>Vehículo</td><td>' . $lang['Placa'] . '</td><td>Siniestros</td><td>Estatus</td><td>Fecha Recibido</td><td>Fecha de Entrega</td><td>Días en proceso</td></tr>' . "\n";
	$fondo = 'claro';
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	while($ord = mysql_fetch_array($matr1)) {
		$dias = intval((strtotime($ord['orden_fecha_de_entrega']) - strtotime($ord['orden_fecha_recepcion'])) / 86400);
		if(!isset($estanciamax) || $estanciamax == '') { $estanciamax = '20'; }
		if($dias > $estanciamax) { $dias = '<span style="font-weight:bold; color:red; background-color:yellow;">'.$dias.'</span>'; }
		$vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);
		$preg2 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '130' GROUP BY sub_reporte";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de lapso! 1854 " . $preg2);
		$fila2 = mysql_num_rows($matr2);
		echo '				<tr class="' . $fondo . '">
					<td style="padding-left:10px; padding-right:10px; text-align:center;"><a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" style=" display:block;">' . $ord['orden_id'] . '</a></td><td style="padding-left:10px; padding-right:10px;">' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $vehiculo['placas'] . '</td><td>';
		while($sub = mysql_fetch_array($matr2)) {
			if($sub['sub_reporte'] == '' || $sub['sub_reporte'] == '0') { echo 'Particular'; } else { echo $sub['sub_reporte']; }
			if($fila2 > '1') { echo '<br>'; }
		}
		echo '<td style="padding-left:10px; padding-right:10px;">' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['orden_fecha_recepcion'] . '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['orden_fecha_de_entrega'] . '</td><td align="center">'.$dias.'</td>
				</tr>';
		$j++;
		if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
	}
	echo '			</table>';
}

if ($accion==="seguimiento") {

	$funnum = 1125020;

	$preg1 = "SELECT orden_id, orden_estatus, orden_alerta, orden_fecha_recepcion, orden_fecha_ultimo_movimiento, orden_fecha_promesa_de_entrega FROM " . $dbpfx . "ordenes WHERE orden_estatus < 40 AND";
	$preg1 .= " orden_fecha_recepcion > '" . $feini . "' AND orden_fecha_recepcion < '" . $fefin . "' ";
	$preg1 .= "ORDER BY orden_fecha_promesa_de_entrega,orden_id";
	//	echo $preg1;
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de lapso! 1878 " . $preg1);
	$filas = mysql_num_rows($matr1);

	echo '			<table cellspacing="1" cellpadding="2" border="0">' . "\n";
	echo '				<tr class="cabeza_tabla"><td colspan="8" style="text-align: right;">' . $filas . $encabezado . '</td></tr>' . "\n";
	echo '				<tr><td>OT</td><td>Vehículo</td><td>' . $lang['Placa'] . '</td><td>Días para<br>Entrega</td><td>Alerta</td><td>Estatus</td><td>Fecha Seguimiento</td><td>Comentario</td><td>Usuario</td><td>Todos los comentarios</td></tr>' . "\n";
	$fondo = 'claro';
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	while($ord = mysql_fetch_array($matr1)) {
		$dias = intval((strtotime($ord['orden_fecha_promesa_de_entrega']) - time()) / 86400);
		if($dias < 2) { $dias = '<span style="font-weight:bold; color:red; background-color:yellow;">'.$dias.'</span>'; }
		$preg2 = "SELECT * FROM " . $dbpfx . "comentarios WHERE orden_id = '" . $ord['orden_id'] . "' AND interno = '2' ";
		$preg2 .= "ORDER BY bit_id DESC LIMIT 1";
		//echo $preg2;
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Comentario!");
		$com = mysql_fetch_array($matr2);
		$vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);
		echo '				<tr class="' . $fondo . '">
					<td style="padding-left:10px; padding-right:10px; text-align:center;"><a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" style=" display:block;">' . $ord['orden_id'] . '</a></td><td style="padding-left:10px; padding-right:10px;">' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $vehiculo['placas'] . '</td>';
		echo '<td style="text-align:center;">' . $dias . '</td><td>' . constant('ALARMA_' . $ord['orden_alerta']) . '</td>';
		echo '<td style="padding-left:10px; padding-right:10px;">' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $com['fecha_com'] . '</td><td style="padding-left:10px; padding-right:10px;">' . $com['comentario'] . '</td><td style="padding-left:10px; padding-right:10px;">' . $usuario[$com['usuario']] . '</td><td align="center"><a href="comentarios.php?accion=mostrar&orden_id=' . $ord['orden_id'] . '" onclick="window.open(this.href,';
		echo "'Comentarios','left=20,top=20,width=550,height=650,toolbar=0,resizable=0,scrollbars=1');";
		echo ' return false;" >Comentarios</a></td>'."\n";
		echo '				</tr>';
		$j++;
		if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
	}
	echo '			</table>';

	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}

if ($accion==="desglose") {

	if ($f1125025 == '1') {

	echo '			<table cellspacing="1" cellpadding="2" border="0">' . "\n";
	echo '				<tr class="cabeza_tabla"><td colspan="18" style="text-align: right;">' . $filas . $encabezado . '</td></tr>' . "\n";
	echo '				<tr><td>OT</td><td>Vehículo</td><td>' . $lang['Placa'] . '</td><td>Estatus</td><td>Siniestro</td><td class="area6">&nbsp;</td><td class="area6" style="text-align:center;">Hojalatería</td><td class="area6">&nbsp;</td><td class="area6">&nbsp;</td><td class="area7">&nbsp;</td><td class="area7" style="text-align:center;">Pintura</td><td class="area7">&nbsp;</td><td class="area7">&nbsp;</td><td class="areaotra">&nbsp;</td><td class="areaotra" style="text-align:center;">Otros</td><td class="areaotra">&nbsp;</td><td class="areaotra">&nbsp;</td></tr>'."\n";
	echo '				<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></td><td class="area6" style="text-align:center;">Refacciones</td><td class="area6" style="text-align:center;">Costo Refacciones</td><td class="area6" style="text-align:center;">Mano de Obra</td><td class="area6" style="text-align:center;">Destajo</td><td class="area7" style="text-align:center;">Consumibles</td><td class="area7" style="text-align:center;">Costo Consumibles</td><td class="area7" style="text-align:center;">Mano de Obra</td><td class="area7" style="text-align:center;">Destajo</td><td class="areaotra" style="text-align:center;">Refacciones</td><td class="areaotra" style="text-align:center;">Costo Refacciones</td><td class="areaotra" style="text-align:center;">Mano de Obra</td><td class="areaotra" style="text-align:center;">Destajo</td></tr>'."\n";
	$fondo = 'claro';
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
	$tpartes6 = 0; $tparteso = 0; $tmo6 = 0; $tmoo = 0; $tmo7 = 0; $tcons7 = 0;
	$cpartes6 = 0; $cparteso = 0; $ccons7 = 0;
	$numots = mysql_num_rows($matr0);
	while($ord = mysql_fetch_array($matr0)) {
		if(isset($im) && substr($im, 0, 2) == 'oc') {
			$dias = intval((strtotime($ord['orden_fecha_de_entrega']) - strtotime($ord['orden_fecha_recepcion'])) / 86400);
		} else {
			$dias = intval(($hoy - strtotime($ord['orden_fecha_recepcion'])) / 86400);
		}
		if(!isset($estanciamax) || $estanciamax == '') { $estanciamax = '20'; }
		if($dias > $estanciamax) { $dias = '<span style="font-weight:bold; color:red; background-color:yellow;">'.$dias.'</span>'; }
		$vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);
		$preg2 = "SELECT sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' GROUP BY sub_reporte";
		$matr2 = mysql_query($preg2);
		while($gsub = mysql_fetch_array($matr2)) {
			echo '				<tr class="' . $fondo . '">
					<td style="padding-left:10px; padding-right:10px; text-align:center;"><a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" style=" display:block;">' . $ord['orden_id'] . '</a></td><td style="padding-left:10px; padding-right:10px;">' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $vehiculo['placas'] . '</td><td style="padding-left:10px; padding-right:10px;">' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '</td><td>';
			if($gsub['sub_reporte'] == '0') { echo 'Particular'; } else { echo $gsub['sub_reporte']; }
			echo '</td>';
			$preg1 = "SELECT sub_orden_id, sub_presupuesto, sub_partes, sub_consumibles, sub_mo, sub_area, sub_deducible FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $gsub['sub_reporte'] . "' AND orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' ";
			$matr1 = mysql_query($preg1) or die("Falló selección de subordenes");
			//echo '<table cellpadding="2" cellspacing="0" border="1" width="100%">';
			$partes = 0; $cons = 0; $mos = 0; $costo = 0;
			while($sub = mysql_fetch_array($matr1)) {
				if($sub['sub_area']=='6') {
					$preg4 = "SELECT op_costo, op_precio, op_cantidad, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
					$matr4 = mysql_query($preg4) or die("Falló selección de Productos");
					while($op = mysql_fetch_array($matr4)) {
						if($op['op_tangible'] == '1') {
							$partes = $partes + ($op['op_cantidad'] * $op['op_precio'] );
							$costo = $costo + ($op['op_cantidad'] * $op['op_costo'] );
						}
						if($op['op_tangible'] == '0') { $mos = $mos + ($op['op_cantidad'] * $op['op_precio'] ); }
					}
				}
			}
			$tpartes6 = $tpartes6 + $partes;
			$tmo6 = $tmo6 + $mos;
			$cpartes6 = $cpartes6 + $costo;
			echo '<td class="area6">' . money_format("%n", $partes) . '</td><td class="area6">' . money_format("%n", $costo) . '</td><td class="area6">' . money_format("%n", $mos) . '</td><td class="area6">' . money_format('%n', ($mos * $destajo[6])) . '</td>';
			//echo '</td><td>';
			//echo '<table cellpadding="2" cellspacing="0" border="1" width="100%">';
			$partes = 0; $cons = 0; $mos = 0; $costo = 0;
			mysql_data_seek($matr1, 0);
			while($sub = mysql_fetch_array($matr1)) {
				if($sub['sub_area']=='7') {
					$preg4 = "SELECT op_costo, op_precio, op_cantidad, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
					$matr4 = mysql_query($preg4) or die("Falló selección de Productos");
					while($op = mysql_fetch_array($matr4)) {
						if($op['op_tangible'] == '2') {
							$cons = $cons + ($op['op_cantidad'] * $op['op_precio'] );
							$costo = $costo + ($op['op_cantidad'] * $op['op_costo'] );
						}
						if($op['op_tangible'] == '0') { $mos = $mos + ($op['op_cantidad'] * $op['op_precio'] ); }
					}
				}
			}
			$tcons7 = $tcons7 + $cons;
			$tmo7 = $tmo7 + $mos;
			$ccons7 = $ccons7 + $costo;
			echo '<td class="area7">' . money_format("%n", $cons) . '</td><td class="area7">' . money_format("%n", $costo) . '</td><td class="area7">' . money_format("%n", $mos) . '</td><td class="area7">' . money_format('%n', ($mos * $destajo[7])) . '</td>';
			//echo '</td><td>';
			//echo '<table cellpadding="2" cellspacing="0" border="1" width="100%">';
			$partes = 0; $cons = 0; $mos = 0; $opers = '';
			mysql_data_seek($matr1, 0);
			while($sub = mysql_fetch_array($matr1)) {
				if($sub['sub_area']!='6' && $sub['sub_area']!='7') {
					$preg4 = "SELECT op_costo, op_precio, op_cantidad, op_tangible FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
					$matr4 = mysql_query($preg4) or die("Falló selección de Productos");
					while($op = mysql_fetch_array($matr4)) {
						if($op['op_tangible'] == '1') {
							$partes = $partes + ($op['op_cantidad'] * $op['op_precio'] );
							$costo = $costo + ($op['op_cantidad'] * $op['op_costo'] );
						}
						if($op['op_tangible'] == '0') { $mos = $mos + ($op['op_cantidad'] * $op['op_precio'] ); }
					}
					$mi_area = $sub['sub_area'];
				}
			}
			$tparteso = $tparteso + $partes;
			$tmoo = $tmoo + $mos;
			$cparteso = $cparteso + $costo;
			echo '<td class="areaotra">' . money_format("%n", $partes) . '</td><td class="areaotra">' . money_format("%n", $costo) . '</td><td class="areaotra">' . money_format("%n", $mos) . '</td><td class="areaotra">' . money_format('%n', ($mos * $destajo[$mi_area])) . '</td>';
			echo '</tr>'."\n";
			$j++;
			if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
		}
	}
	echo '				<tr><td colspan="5" style="text-align:right;">Totales</td><td class="area6">' . money_format('%n', $tpartes6) . '</td><td class="area6" style="text-align:center;">' . money_format('%n', $cpartes6) . '</td><td class="area6">' . money_format('%n', $tmo6) . '</td><td class="area6">&nbsp;</td><td class="area7">' . money_format('%n', $tcons7) . '</td><td class="area7" style="text-align:center;">' . money_format('%n', $ccons7) . '</td><td class="area7">' . money_format('%n', $tmo7) . '</td><td class="area7">&nbsp;</td><td class="areaotra">' . money_format('%n', $tparteso) . '</td><td class="areaotra" style="text-align:center;">' . money_format('%n', $cparteso) . '</td><td class="areaotra">' . money_format('%n', $tmoo) . '</td><td class="areaotra">&nbsp;</td></tr>'."\n";

	echo '				<tr><td colspan="5" style="text-align:right;">Promedio</td><td class="area6">' . money_format('%n', ($tpartes6 / $numots)) . '</td><td class="area6" style="text-align:center;">' . money_format('%n', ($cpartes6 / $numots)) . '</td><td class="area6">' . money_format('%n', ($tmo6 / $numots)) . '</td><td class="area6">&nbsp;</td><td class="area7">' . money_format('%n', ($tcons7 / $numots)) . '</td><td class="area7" style="text-align:center;">' . money_format('%n', ($ccons7 / $numots)) . '</td><td class="area7">' . money_format('%n', ($tmo7 / $numots)) . '</td><td class="area7">&nbsp;</td><td class="areaotra">' . money_format('%n', ($tparteso / $numots)) . '</td><td class="areaotra" style="text-align:center;">' . money_format('%n', ($cparteso / $numots)) . '</td><td class="areaotra">' . money_format('%n', ($tmoo / $numots)) . '</td><td class="areaotra">&nbsp;</td></tr>'."\n";

	echo '			</table>';

	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}

if ($accion==="finanzas") {

	//---- Include del reporte ----
    include('parciales/reportes/finanzas.php');
	
}

if($accion==="utilidad_area"){

	//---- Include del reporte ----
    include('parciales/reportes/utilidad_area.php');

}

if ($accion==="operadores") {

	if ($f1125065 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1') {

	echo '			<table cellspacing="1" cellpadding="2" border="0">' . "\n";
	echo '				<tr class="cabeza_tabla"><td colspan="15" style="text-align: right;">' . $encabezado . '</td></tr>' . "\n";
	echo '				<tr><td>OT</td><td>Vehículo</td><td>' . $lang['Placa'] . '</td><td>Estatus</td><td>Siniestro</td><td>Fecha de</td><td class="area6">&nbsp;</td><td class="area6" style="text-align:center;">Hojalatería</td><td class="area6">&nbsp;</td><td class="area7">&nbsp;</td><td class="area7" style="text-align:center;">Pintura</td><td class="area7">&nbsp;</td><td class="areaotra">&nbsp;</td><td class="areaotra" style="text-align:center;">Otros</td><td class="areaotra">&nbsp;</td></tr>'."\n";
	echo '				<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>Terminado</td></td><td class="area6" style="text-align:center;">Mano de Obra</td><td class="area6" style="text-align:center;">Destajo</td><td class="area6" style="text-align:center;">Operadores</td><td class="area7" style="text-align:center;">Mano de Obra</td><td class="area7" style="text-align:center;">Destajo</td><td class="area7" style="text-align:center;">Operadores</td><td class="areaotra" style="text-align:center;">Mano de Obra</td><td class="areaotra" style="text-align:center;">Destajo</td><td class="areaotra" style="text-align:center;">Operadores</td></tr>'."\n";
	$fondo = 'claro';
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
		$preg2 = "SELECT sub_reporte, orden_id, sub_fecha_terminado FROM " . $dbpfx . "subordenes WHERE sub_fecha_terminado > '" . $feini . "' AND sub_fecha_terminado < '" . $fefin . "' AND sub_estatus < '190' GROUP BY sub_reporte ORDER BY sub_fecha_terminado";
		$matr2 = mysql_query($preg2);
		while($gsub = mysql_fetch_array($matr2)) {
			$preg0 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $gsub['orden_id'] . "'";
			$matr0 = mysql_query($preg0);
			$ord = mysql_fetch_array($matr0);
			echo '				<tr class="' . $fondo . '">
					<td style="padding-left:10px; padding-right:10px; text-align:center;"><a href="ordenes.php?accion=consultar&orden_id=' . $ord['orden_id'] . '" style=" display:block;">' . $ord['orden_id'] . '</a></td>';
			$vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);
			echo '<td style="padding-left:10px; padding-right:10px;">' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $vehiculo['placas'] . '</td><td style="padding-left:10px; padding-right:10px;">' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '</td><td>';
			if($gsub['sub_reporte'] == '0') { echo 'Particular'; } else { echo $gsub['sub_reporte']; }
			echo '</td><td>' . $gsub['sub_fecha_terminado'] . '</td>';
			$preg1 = "SELECT sub_orden_id, sub_presupuesto, sub_partes, sub_consumibles, sub_mo, sub_area, sub_deducible FROM " . $dbpfx . "subordenes WHERE sub_reporte = '" . $gsub['sub_reporte'] . "' AND orden_id = '" . $ord['orden_id'] . "'";
			$matr1 = mysql_query($preg1) or die("Falló selección de subordenes");
			//echo '<table cellpadding="2" cellspacing="0" border="1" width="100%">';
			$mos = 0; $opers = '';
			while($sub = mysql_fetch_array($matr1)) {
				if($sub['sub_area']=='6') {
					$mos = $mos + $sub['sub_mo'];
					$preg4 = "SELECT usuario FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' GROUP BY usuario ORDER BY usuario";
					$matr4 = mysql_query($preg4) or die("Falló selección de Operadores");
					while($seg = mysql_fetch_array($matr4)) {
						$opers .= $seg['usuario'] . ' ';
					}
				}
			}
			echo '<td class="area6">' . money_format("%n", $mos) . '</td><td class="area6">' . money_format('%n', ($mos * $destajo[6])) . '</td><td class="area6">' . $opers . '</td>';
			//echo '</td><td>';
			//echo '<table cellpadding="2" cellspacing="0" border="1" width="100%">';
			$mos = 0; $opers = '';
			mysql_data_seek($matr1, 0);
			while($sub = mysql_fetch_array($matr1)) {
				if($sub['sub_area']=='7') {
					$mos = $mos + $sub['sub_mo'];
					$preg4 = "SELECT usuario FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' GROUP BY usuario ORDER BY usuario";
					$matr4 = mysql_query($preg4) or die("Falló selección de Operadores");
					while($seg = mysql_fetch_array($matr4)) {
						$opers .= $seg['usuario'] . ' ';
					}
				}
			}
			echo '<td class="area7">' . money_format("%n", $mos) . '</td><td class="area7">' . money_format('%n', ($mos * $destajo[7])) . '</td><td class="area7">' . $opers . '</td>';
			//echo '</td><td>';
			//echo '<table cellpadding="2" cellspacing="0" border="1" width="100%">';
			$mos = 0; $opers = '';
			mysql_data_seek($matr1, 0);
			while($sub = mysql_fetch_array($matr1)) {
				if($sub['sub_area']!='6' && $sub['sub_area']!='7') {
					$mos = $mos + $sub['sub_mo'];
					$preg4 = "SELECT usuario FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' GROUP BY usuario ORDER BY usuario";
					$matr4 = mysql_query($preg4) or die("Falló selección de Operadores");
					while($seg = mysql_fetch_array($matr4)) {
						$opers .= $seg['usuario'] . ' ';
					}
					$mi_area = $sub['sub_area'];
				}
			}
			echo '<td class="areaotra">' . money_format("%n", $mos) . '</td><td class="areaotra">' . money_format('%n', ($mos * $destajo[$mi_area])) . '</td><td class="areaotra">' . $opers . '</td>';
			echo '</tr>'."\n";
			$j++;
			if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
		}
	echo '			</table>';

	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}

if ($accion==="manodeobra") {

	//---- Include del reporte ----
    include('parciales/reportes/mano-de-obra.php');
	
}

if ($accion==="destajo") {

	//---- Include del reporte ----
    include('parciales/reportes/destajos.php');
}

if ($accion==="cliente") {

	//---- Include del reporte ----
    include('parciales/reportes/cliente.php');
}

if ($accion==="aseguradora") {

	if ($f1125075 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1') {
		$fondo = 'claro';
		$preg0 = '';
		$hoy = strtotime(date('Y-m-d 23:59:59'));
		$estemes = date('n');
		$year = date('Y');
		if($mfe > 3 ) { $mes = 3; }
		elseif($mfr > 3) { $mes = 3; }
		elseif(isset($mfe)) { $mes = $mfe; }
		else { $mes = $mfr; }
		$elmes = $estemes - $mes;
		if($elmes < 1) { $year = $year -1; $elmes = $elmes + 12; }
		$etiqmes = strftime('%B del %G', mktime(0,0,0,$elmes));

		if(isset($mfe) && $mfe != '') {
			if($mfe <= 3) {
				$feini = $year.'-'.$elmes.'-01 00:00:00';
				$fefin = strtotime($feini);
				$fefin = date('Y-m-t 23:59:59', $fefin);
				$encabezado = $etiqmes;
			} else {
				$feini = $year.'-'.$elmes.'-01 00:00:00';
				$fefin = strtotime($estemes);
				$fefin = date('Y-m-t 23:59:59', $fefin);
				$encabezado = ' Total.';
			}
			if($mfe > '3') {
					$preg0 .= "AND o.orden_fecha_de_entrega >= '$feini' ";
			} elseif($mfe <= '3' && $mfe >= '1') {
					$preg0 .= "AND o.orden_fecha_de_entrega >= '$feini' AND o.orden_fecha_de_entrega <= '$fefin' ";
			} elseif($mfe == '0') {
					$preg0 .= "AND o.orden_fecha_de_entrega >= '$feini' ";
			}
		}

		if(isset($mfr) && $mfr != '') {

			if($mfr <= 3) {
				$feini = $year.'-'.$elmes.'-01 00:00:00';
				$fefin = strtotime($feini);
				$fefin = date('Y-m-t 23:59:59', $fefin);
				$encabezado = $etiqmes;
			} else {
				$feini = $year.'-'.$elmes.'-01 00:00:00';
				$fefin = strtotime($estemes);
				$fefin = date('Y-m-t 23:59:59', $fefin);
				$encabezado = ' Total.';
			}

			if($mfr > '3') {
					$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' ";
			} elseif($mfr <= '3' && $mfr >= '1') {
					$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' AND o.orden_fecha_recepcion <= '$fefin' ";
			} elseif($mfr == '0') {
					$preg0 .= "AND o.orden_fecha_recepcion >= '$feini' ";
			}
		}

		$pregpo = "SELECT s.orden_id FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE s.sub_aseguradora = '" . $a . "' AND o.orden_id = s.orden_id AND s.sub_estatus < '190' AND o.orden_fecha_de_entrega IS NOT NULL ";
		
		$pregpo .= $preg0;
		
		$pregpo .= "GROUP BY s.orden_id ORDER BY o.orden_id";
		
		//echo $pregpo . '<br>';
		
		//echo $pregpo;
		$matrpo = mysql_query($pregpo) or die("ERROR: Fallo selección de subórdenes! " . $pregpo);
		//$filas = mysql_num_rows($matr3);
		echo '			<table cellspacing="1" cellpadding="2" border="0">' . "\n";
		echo '				<tr class="cabeza_tabla"><td colspan="9" style="text-align: right;">' . $filas . ' Ordenes de Trabajo ' . $tipo . ' con tareas de ' . $ase[$a][1] . ' en ' . $encabezado . '&nbsp;<img src="' . $ase[$a][0] . '" alt="" /></td></tr>' . "\n";
		echo '				<tr><td>OT</td><td>Vehículo</td><td>' . $lang['Placa'] . '</td><td>Asesor</td><td>Siniestro</td><td>Estatus</td><td>Fecha Recibido</td><td>Último movimiento</td><td>Cliente</td></tr>' . "\n";
		$j = 0;
		while($gpo = mysql_fetch_array($matrpo)) {
			$preg3 = "SELECT s.sub_aseguradora, s.sub_orden_id, s.sub_reporte, o.orden_id, o.orden_vehiculo_marca, o.orden_vehiculo_tipo, o.orden_vehiculo_color, o.orden_vehiculo_placas, o.orden_asesor_id, o.orden_estatus, o.orden_fecha_recepcion, o.orden_fecha_de_entrega, o.orden_fecha_promesa_de_entrega, o.orden_fecha_ultimo_movimiento FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o  WHERE s.sub_aseguradora = '" . $a . "' AND o.orden_id = s.orden_id AND s.sub_estatus < '190' AND s.orden_id = '" . $gpo['orden_id'] . "' GROUP BY s.sub_reporte";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de subórdenes!");
			//echo $preg3 . '<br>';
			while ($ord = mysql_fetch_array($matr3)) {
				if($ord['orden_fecha_de_entrega'] != '' && ($ord['orden_estatus'] == 99 || $ord['orden_estatus'] <= 29)){
					$dias = intval(($hoy - strtotime($ord['orden_fecha_recepcion'])) / 86400);
					echo '				<tr class="' . $fondo . '">
					<td style="padding-left:10px; padding-right:10px; text-align:center;"><a href="';
					if($ord['orden_estatus'] > 3) { echo 'presupuestos.php'; } else  { echo 'proceso.php'; }
					echo '?accion=consultar&orden_id=' . $ord['orden_id'] . '#' . $ord['sub_orden_id'] . '" style=" display:block;">' . $ord['orden_id'] . '</a></td><td style="padding-left:10px; padding-right:10px;">' . strtoupper($ord['orden_vehiculo_tipo']) . ' ' . strtoupper($ord['orden_vehiculo_color']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['orden_vehiculo_placas'] . '</td><td>' . $usuario[$ord['orden_asesor_id']] . '</td><td>';
					if($ord['sub_reporte'] != '0') {echo $ord['sub_reporte']; } else { echo 'Particular'; }
					echo '</td><td style="padding-left:10px; padding-right:10px;">' . constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['orden_fecha_recepcion'] . '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['orden_fecha_ultimo_movimiento'] . '</td><td>' . $ase[$a][1] . '</td>
					</tr>'."\n";
					if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
					$j++;	
				}
			}
		}
		echo '				<tr><td colspan="9">Se encontrarón ' . $j . ' trabajos para este cliente en este periodo.</td></tr>'."\n";
		echo '		</table>'."\n";
	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}

if ($accion==="audatrace") {

	if ($f1125080 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1') {

	$consulta = "SELECT aseguradora_id, aseguradora_logo, aseguradora_nic FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
	while ($aseg = mysql_fetch_array($arreglo)) {
		$ase[$aseg['aseguradora_id']][0] = $aseg['aseguradora_logo'];
		$ase[$aseg['aseguradora_id']][1] = $aseg['aseguradora_nic'];
	}
	$ase[0][0] = 'imagenes/logo-particular.png';
	$ase[0][1] = 'Particulares';

	echo '			<table cellspacing="1" cellpadding="2" border="0">' . "\n";
	echo '				<tr class="cabeza_tabla"><td colspan="12" style="text-align: right;">' . $encabezado . '</td></tr>' . "\n";
	echo '				<tr><td>OT</td><td>Ubicación</td><td>Fecha Recibido</td><td>Fecha Promesa</td><td>Siniestro</td><td>Marca</td><td>Tipo</td><td>Color</td><td>Año</td><td>' . $lang['Placa'] . '</td><td>Cliente</td><td>Telefonos</td></tr>' . "\n";
	$fondo = 'claro';
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));

	foreach($ase as $k => $v) {
		$preg3 = "SELECT s.sub_aseguradora, s.sub_orden_id, s.sub_reporte, o.orden_id, v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_color, v.vehiculo_modelo, o.orden_vehiculo_placas, o.orden_ubicacion, o.orden_estatus, o.orden_fecha_recepcion, o.orden_fecha_promesa_de_entrega, o.orden_fecha_ultimo_movimiento, c.cliente_nombre, c.cliente_apellidos, c.cliente_telefono1, c.cliente_telefono2 FROM " . $dbpfx . "subordenes s, " . $dbpfx . "clientes c, " . $dbpfx . "vehiculos v," . $dbpfx . "ordenes o  WHERE s.sub_aseguradora = '" . $k . "' AND o.orden_id = s.orden_id AND o.orden_cliente_id = c.cliente_id AND o.orden_vehiculo_id = v.vehiculo_id AND s.sub_estatus < '190' AND o.orden_estatus < 90 AND o.orden_fecha_recepcion > '" . $feini . "' AND o.orden_fecha_recepcion < '" . $fefin . "' GROUP BY o.orden_id ORDER BY o.orden_id";
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de subórdenes!");
		//echo $v[1] . ' -> ' . $preg3 . '<br>';
		echo '				<tr class="cabeza_tabla"><td colspan="12" style="text-align: right;">Ordenes de Trabajo para ' . $v[1] . '&nbsp;<img src="' . $v[0] . '" alt="" /></td></tr>' . "\n";
		while ($ord = mysql_fetch_array($matr3)) {
			$dias = intval(($hoy - strtotime($ord['orden_fecha_recepcion'])) / 86400);
			echo '				<tr class="' . $fondo . '">
					<td style="padding-left:10px; padding-right:10px; text-align:center;"><a href="';
			if($ord['orden_estatus'] > 3) { echo 'presupuestos.php'; } else  { echo 'proceso.php'; }
			echo '?accion=consultar&orden_id=' . $ord['orden_id'] . '#' . $ord['sub_orden_id'] . '" style=" display:block;">' . $ord['orden_id'] . '</a></td><td style="padding-left:10px; padding-right:10px;">';
			if($ord['orden_ubicacion'] == 'Transito') {echo 'TRANSITO'; } else { echo 'TALLER'; }
			echo '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['orden_fecha_recepcion'] . '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['orden_fecha_promesa_de_entrega'] . '</td><td>';
			if($ord['sub_reporte'] != '0') {echo $ord['sub_reporte']; } else { echo 'Particular'; }
			echo '</td><td style="padding-left:10px; padding-right:10px;">' . strtoupper($ord['vehiculo_marca']) . '</td><td style="padding-left:10px; padding-right:10px;">' . strtoupper($ord['vehiculo_tipo']) . '</td><td style="padding-left:10px; padding-right:10px;">' . strtoupper($ord['vehiculo_color']) . '</td><td style="padding-left:10px; padding-right:10px;">' . strtoupper($ord['vehiculo_modelo']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['orden_vehiculo_placas'] . '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['cliente_nombre'] . ' ' . $ord['cliente_apellidos'] . '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['cliente_telefono1'] . ' ' . $ord['cliente_telefono2'] . '</td>
				</tr>';
			$j++;
			if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
		}
	}
	echo '		</table>'."\n";

	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}

if ($accion==="ajustadores") {

	if ($f1125085 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1' || $_SESSION['rol04']=='1') {
		echo '			<div class="tabla"><table cellspacing="1" cellpadding="2" border="0">' . "\n";
		echo '				<tr class="cabeza_tabla"><td colspan="17" style="text-align: right;">' . $encabezado . '</td></tr>' . "\n";
		//echo 'Consulta: '.$preg0;
		unset($datos);
		while($ord = mysql_fetch_array($matr0)) {
			$preg2 = "SELECT s.sub_idajus, s.sub_nomajus, s.sub_reporte, o.orden_id FROM " . $dbpfx . "subordenes s, " . $dbpfx . "ordenes o WHERE s.orden_id = o.orden_id AND s.orden_id = '".$ord['orden_id']."' AND s.sub_area = '6' AND s.sub_reporte != '0' AND s.sub_reporte != '' AND (s.sub_idajus != '' || s.sub_nomajus != '')";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo seleccion!");
			$sub = mysql_fetch_array($matr2);
			$mes = date('n', strtotime($ord['orden_fecha_recepcion']));
			if($sub['sub_idajus'] != '' || $sub['sub_sub_nomajus'] != '') {
				$datos[] = array('idajus' => $sub['sub_idajus'], 'nomajus' => $sub['sub_nomajus'], 'recepcion' => $ord['orden_fecha_recepcion'], 'reporte' => $sub['sub_reporte'], 'vehiculo' => $ord['orden_vehiculo_tipo'] . ' ' . $ord['orden_vehiculo_color'], 'placas' => '<a href="ordenes.php?accion=consultar&orden_id='.$ord['orden_id'].'">'.$ord['orden_vehiculo_placas'].'</a>', 'estatus' => $ord['orden_estatus']);
			}
		}
		foreach ($datos as $key => $row) {
			$nombre[$key]  = $row['nomajus'];
			$estado[$key] = $row['estatus'];
		}
		array_multisort($nombre, SORT_ASC, $estado, SORT_ASC, $datos);
		echo '				<tr><td>Num Ajustador</td><td>Nombre</td><td>Fecha de Ingreso</td><td>Siniestro</td><td>Vehículo</td><td>' . $lang['Placa'] . '</td><td>Dictamen</td></tr>' . "\n";
		foreach ($datos as $key => $row) {
			echo '				<tr class="' . $fondo . '">
					<td>' . $row['idajus'] . '</td>
					<td>' . $row['nomajus'] . '</td>
					<td>' . $row['recepcion'] . '</td>
					<td>' . $row['reporte'] . '</td>
					<td>' . $row['vehiculo'] . '</td>
					<td>' . $row['placas'] . '</td>
					<td>';
			if($row['estatus'] == 30 || $row['estatus'] == 98) { echo 'Pago de Daños';}
			elseif($row['estatus'] == 31 || $row['estatus'] == 97) { echo 'Perdida Total';}
			elseif($row['estatus'] == 32 || $row['estatus'] == 96) { echo 'Pago Plus';}
			elseif($row['estatus'] == 33) { echo 'Condicionada';}
			elseif(($row['estatus'] > 3 && $row['estatus'] < 17) || $row['estatus'] == 91 || $row['estatus'] == 99) { echo 'Reparación';}
			elseif($row['estatus'] == 90) { echo 'Cancelada';}
			else { echo 'Por Dictaminar'; }
			echo '</td>
				</div></tr>';
			$j++;
			if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
		}
		echo '			</table></div>';

	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}

if ($accion==="comentreg") {

	//---- Include del reporte ----
    include('parciales/reportes/entregados.php');
}

if ($accion==="valuaciones") {

	//---- Include del reporte ----
    include('parciales/reportes/valuaciones.php');
}

if ($accion==="facturacion") {

	//---- Include del reporte ----
    include('parciales/reportes/facturacion.php');
}

if ($accion==="ingresos") {

	//---- Include del reporte ----
    include('parciales/reportes/cobros-pagos.php');
}

if ($accion==="comentarios") {

	if ($f1125090 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {
			$pregase = ''; $pregasec = '';
			if($orden_comentarios == '1') {
				$preg1 = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_fecha_recepcion >= '" . $feini . "' AND orden_fecha_recepcion <= '" . $fefin . "' ORDER BY orden_id";
			} else {
				$preg1 = "SELECT * FROM ( SELECT orden_id, fecha_com FROM " . $dbpfx . "comentarios WHERE interno = '2' AND fecha_com >= '" . $feini . "' AND fecha_com <= '" . $fefin . "' ORDER BY fecha_com DESC ) AS temporal GROUP BY LOWER(orden_id) ORDER BY LOWER(fecha_com) DESC";
			}
//			echo $preg1;
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de OTs para mostrar. " . $preg1);
			echo '				<table cellspacing="2" cellpadding="2" border="0" width="100%">
					<tr class="cabeza_tabla">
						<td colspan="10" style="text-align: left;">Reporte de llamadas al cliente en el periodo</td>
					</tr>
					<tr>
					 	<td colspan="6"></td>
						<td colspan="4" style="text-align: center;"><STRONG>Registro por etapa de la OT</STRONG></td>
					</tr>
					<tr>
						<td><STRONG>OT</STRONG></td>
						<td><STRONG>Vehículo</STRONG></td>
						<td><STRONG>' . $lang['Placa'] . '</STRONG></td>
						<td><STRONG>Fecha Ingreso</STRONG></td>
						<td><STRONG>Datos Cliente</STRONG></td>
						<td><STRONG>Asesor</STRONG></td>
						<td class="area6" style="text-align: center;"><STRONG>' . $lang['Bienvenida'] . '</STRONG></td>
						<td class="area7" style="text-align: center;"><STRONG>' . $lang['Deducible'] . '</STRONG></td>
						<td class="area6" style="text-align: center;"><STRONG>' . $lang['Reparación'] . '</STRONG></td>
						<td class="area7" style="text-align: center;"><STRONG>' . $lang['Terminado'] . '</STRONG></td>
					</tr>'."\n";
			$fondo = 'claro';
			while($ots = mysql_fetch_array($matr1)) {
// ------ Información del cliente nombre / teléfono
				$preg_clientes = "SELECT orden_id, orden_vehiculo_color, orden_vehiculo_tipo, orden_vehiculo_placas, orden_cliente_id, orden_asesor_id, orden_fecha_recepcion FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $ots['orden_id'] . "' ";
				$matr_clientes = mysql_query($preg_clientes) or die("ERROR: Fallo selección de opción. " . $preg_clientes);
				$consulta = mysql_fetch_array($matr_clientes);
				if(($asesor_select > 0 && $consulta['orden_asesor_id'] == $asesor_select) || $asesor_select == '' || $asesor_select < 1) {
					$preg_info_cliente = "SELECT cliente_nombre, cliente_apellidos, cliente_telefono1 FROM " . $dbpfx . "clientes WHERE cliente_id = '" . $consulta['orden_cliente_id'] . "'";
					$matr_info_cliente = mysql_query($preg_info_cliente) or die("ERROR: Fallo selección de lapso de Ordenes!");
					$info_cliente = mysql_fetch_array($matr_info_cliente);
					$nombre_cliente = $info_cliente['cliente_nombre'] . " " . $info_cliente['cliente_apellidos'];
					$cliente_telefono = $info_cliente['cliente_telefono1'];
					echo '					<tr class="' . $fondo . '">
						<td style="text-align:center;"><a href="ordenes.php?accion=consultar&orden_id=' . $consulta['orden_id'] . '" style=" display:block;">' . $consulta['orden_id'] . '</a></td>
						<td>' . strtoupper($consulta['orden_vehiculo_tipo']) . ' ' . strtoupper($consulta['orden_vehiculo_color']) . '</td>
						<td>' . $consulta['orden_vehiculo_placas'] . '</td>'."\n";
					echo '						<td>' . date('Y-m-d', strtotime($consulta['orden_fecha_recepcion'])) . '</td>'."\n";
					echo '						<td>' . $nombre_cliente  . '<br><STRONG>Tel. ' . $cliente_telefono . '</STRONG></td>
						<td>' . $usuario[$consulta['orden_asesor_id']] . '</td>'."\n";
					$renglon = ''; $cols = 0; $fila_com_ant = 0; $enc_ant = 0;
					for($ecom = 10; $ecom <= 40; $ecom = $ecom + 10) {
						$preg_eta_com10 = "SELECT * FROM " . $dbpfx . "comentarios WHERE orden_id = '" . $consulta['orden_id'] . "' AND interno = '2' AND etapa_com = '" . $ecom . "' ORDER BY fecha_com DESC LIMIT 1";
					 	$matr2 = mysql_query($preg_eta_com10) or die("ERROR: Fallo selección de Comentario! " . $preg_eta_com10);
						$filacom = mysql_num_rows($matr2);
			 			if($filacom > 0) {
							$com1 = mysql_fetch_array($matr2);
							$renglon .= '						<td style="border: 1px solid; vertical-align: top;" width="14%">
							<table cellpadding="2" cellspacing="2" border="0" width="100%" >'."\n";
//							$resumen = substr($com1['comentario'], 0, 100);
//							if(strlen($com1['comentario']) > 100) {
//								$resumen .= '...+';
//							}
							$resumen = $com1['comentario'];
							$renglon .= '								<tr>
									<td colspan="3">' . date('Y-m-d H:i', strtotime($com1['fecha_com'])) . ' ' . $usuario[$com1['usuario']] . ': ' . $resumen . ' <a href="comentarios.php?accion=mostrar&orden_id=' . $consulta['orden_id'] . '&tiposeg=2" onclick="window.open(this.href,' . "'Comentarios','left=20,top=20,width=550,height=650,toolbar=0,resizable=0,scrollbars=1');" . ' return false;"> Ver todos</a></td>
								</tr>'."\n";
							$renglon .= '							</table>
						</td> '."\n";
							$fila_com_ant = 0;
						} else {
							if($ecom == 10) {
								$preg_com_ant = "SELECT * FROM " . $dbpfx . "comentarios WHERE orden_id = '" . $consulta['orden_id'] . "' AND interno = '2' AND etapa_com IS NULL ORDER BY fecha_com DESC LIMIT 1";
						 		$matr_com_ant = mysql_query($preg_com_ant) or die("ERROR: Fallo selección de Comentario en formato anterior! " . $preg_com_ant);
								$fila_com_ant = mysql_num_rows($matr_com_ant);
					 			if($fila_com_ant > 0) {
									$com1 = mysql_fetch_array($matr_com_ant);
//									$resumen = substr($com1['comentario'], 0, 100);
//									if(strlen($com1['comentario']) > 100){
//										$resumen .= '...+';
//									}
									$resumen = $com1['comentario'];
									$enc_ant = 1;
									$renglon .= '							<table cellpadding="2" cellspacing="2" border="0" width="100%" >'."\n";
									$renglon .= '								<tr>
									<td>' . date('Y-m-d H:i', strtotime($com1['fecha_com'])) . ' ' . $usuario[$com1['usuario']] . ': ' . $resumen . ' <a href="comentarios.php?accion=mostrar&orden_id=' . $consulta['orden_id'] . '&tiposeg=2" onclick="window.open(this.href,' . "'Comentarios','left=20,top=20,width=550,height=650,toolbar=0,resizable=0,scrollbars=1');" . ' return false;"> Ver todos</a></td>
								</tr>'."\n";
									$renglon .= '							</table>
						</td> '."\n";
					 			}
							}
							if($fila_com_ant > 0) {
								$cols++;
							} else {
								$renglon .= '						<td style="border: 1px solid;background: #FFEF8B;"></td>'."\n";
							}
						}
					}
					if($enc_ant > 0) {
						echo '						<td style="border: 1px solid; background: #FFEF8B; vertical-align: top;" colspan="' . $cols .'">'."\n";
					}
					echo $renglon;
					echo '					</tr>'."\n";
					if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
				}
			}
			echo '</table>'."\n";
	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}

if ($accion==="vigilancia") {

	if ($f1125110 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol06'] == '1') {

		echo '			<table cellspacing="1" cellpadding="2" border="0" width="840" class="reportes">' . "\n";
		echo '				<tr class="cabeza_tabla"><td colspan="14" style="text-align: right;">Bitácora de control de entradas y salidas de ';
		if($localidad == '' || $localidad == 'T') {
			echo 'Todas las localidades';
		} else {
			echo $ubicaciones[$localidad];
		}
		echo '</td></tr>' . "\n";
		echo '				<tr><td>OT</td><td>Vehículo</td><td>' . $lang['Placa'] . '</td><td>Fecha</td><td>Movimiento</td><td>Localidad</td><td>Conductor</td><td>Kilometraje</td><td>Bitácora</td></tr>' . "\n";

		$preg1 = "SELECT * FROM " . $dbpfx . "vigilancia WHERE vig_fecha >= '" . $feini . "' AND vig_fecha <= '" . $fefin . "'";
		if(is_numeric($localidad)) {
			$preg1 .= " AND vig_localidad = '$localidad'";
		}
		$preg1 .= " ORDER BY vig_id DESC";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de lapso de Ordenes!");
		$fondo = 'claro';
		while($vig = mysql_fetch_array($matr1)) {
			$veh = datosVehiculo($vig['orden_id'], $dbpfx);
			echo '				<tr class="' . $fondo . '">
					<td><a href="ordenes.php?accion=consultar&orden_id=' . $vig['orden_id'] . '" style=" display:block;">' . $vig['orden_id'] . '</a></td><td>' . strtoupper($veh['tipo']) . ' ' . strtoupper($veh['color']) . '</td><td>' . $vig['vig_placas'] . '</td>';
			echo '<td>' . date('Y-m-d H:i', strtotime($vig['vig_fecha'])) . '</td><td>';
			if($vig['vig_tipo'] == 1) { echo 'Entra a'; } else { echo 'Sale de'; }
			echo '</td><td>' . $ubicaciones[$vig['vig_localidad']] . '</td><td>';
			if($vig['vig_conductor'] == 1  ) { echo 'Cliente'; }
			elseif($vig['vig_conductor'] == 2  ) { echo 'Ver Observaciones'; }
			else { echo $usuario[$vig['vig_conductor']]; }
			echo '</td><td>' . $vig['vig_kms'] . '</td><td>' . $vig['vig_obs'] . '</td></tr>'."\n";
			if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
		}
		echo '			</table>';
	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}

if ($accion==="deducibles") {

	//---- Include del reporte ----
    include('parciales/reportes/deducibles.php');
}

if ($accion==="ingext") {

	if ($f1125005 == '1' || $f1125010 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03'] == '1' || $_SESSION['rol04'] == '1') {

	echo '			<table cellspacing="1" cellpadding="2" border="0">' . "\n";
	echo '				<tr class="cabeza_tabla"><td colspan="11" style="text-align: right;">' . $filas . $encabezado . '</td></tr>' . "\n";
	echo '				<tr><td><a href="reportes.php?accion=reportes&ordenar=ot&feini='.$feini.'&fefin='.$fefin.'">OT</a></td><td>Vehículo</td><td>' . $lang['Placa'] . '</td><td>Cliente</td><td>Siniestro</td><td><a href="reportes.php?accion=reportes&ordenar=estatus&feini='.$feini.'&fefin='.$fefin.'">Estatus</a></td><td><a href="reportes.php?accion=reportes&ordenar=asesor&feini='.$feini.'&fefin='.$fefin.'">Asesor</a></td><td>Fecha Recibido</td><td>Fecha Promesa<br>de Entrega</td><td>Nombre del Cliente</td><td>Asegurado?</td></tr>' . "\n";

	$fondo = 'claro';
	$j = 0;
	$hoy = strtotime(date('Y-m-d 23:59:59'));
//	echo $preg0;
	while($ord = mysql_fetch_array($matr0)) {
		$vehiculo = datosVehiculo($ord['orden_id'], $dbpfx);
		echo '				<tr class="' . $fondo . '">
					<td style="padding-left:10px; padding-right:10px; text-align:center;"><a href="ordenes.php?accion=consultar&';
		if($confolio == 1 && $id == 17) {
			echo 'oid=' . $ord['oid'];
		} else {
			echo 'orden_id=' . $ord['orden_id'];
		}
		echo '" style=" display:block;">';
		if($confolio == 1 && $id == 17) {
			echo $ord['oid'];
		} else {
			echo $ord['orden_id'];
		}
		echo '</a></td><td style="padding-left:10px; padding-right:10px;">';
		if($confolio == 1 && $id == 17) {
			echo strtoupper($ord['orden_vehiculo_tipo']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $ord['orden_vehiculo_placas'] . '</td>';
		} else {
			echo strtoupper($vehiculo['marca']) . ' ' . strtoupper($vehiculo['tipo']) . ' ' . strtoupper($vehiculo['color']) . ' ' . strtoupper($vehiculo['modelo']) . '</td><td style="padding-left:10px; padding-right:10px;">' . $vehiculo['placas'] . '</td>';
		}
		$preg1 = "SELECT sub_aseguradora, sub_reporte FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' GROUP BY sub_reporte";
		$matr1 = mysql_query($preg1) or die($preg1);
		$reporte = array(); $ima = array();
		while($aseico = mysql_fetch_assoc($matr1)) {
			$ima[$aseico['sub_aseguradora']] = [$ase[$aseico['sub_aseguradora']][0], $ase[$aseico['sub_aseguradora']][1]];
			$reporte[$aseico['sub_reporte']] = 1;
		}
		echo '<td>';
		foreach($ima as $k => $v) {
			echo $v[1] . ' <img src="' . $v[0] . '" alt="" height="16" > ';
		}
		echo '</td>';
		echo '<td>';
		foreach($reporte as $k => $v) {
			if($k != '' && $k != "0") { echo $k . '&nbsp;'; }
		}
		echo '</td>';
		echo '<td style="padding-left:10px; padding-right:10px;">';
		echo constant('ORDEN_ESTATUS_' . $ord['orden_estatus']) ;
		if(($t==1 && $est_trans == 1) || $ord['orden_ubicacion'] == 'Transito') {
//			echo constant('ORDEN_ESTATUS_T_' . $ord['orden_estatus']) ;
			echo ' en Tránsito.';
		}
		if($ord['orden_ref_pendientes'] == '2') {
			echo '<br>Refacciones Estructurales Pendientes';
		} elseif($ord['orden_ref_pendientes'] == '1') {
			echo '<br>Refacciones No estructurales Pendientes';
		} else {
//			echo '<br>Refacciones Completas';
		}
		echo '</td><td>';
		if(isset($of) || isset($osf)) {
			foreach($ordfac as $k => $v) {
				foreach($v as $w) {
					echo '<a href="entrega.php?accion=cobros&orden_id=' . $k . '">' . $w['fact_num'] . '</a> ';
				}
			}
		} else {
			$asesor_id[$ord['orden_asesor_id']]++;
			echo $usuario[$ord['orden_asesor_id']];
			if($ordenar == 'asesor') { echo ' +' . $asesor_id[$ord['orden_asesor_id']]; }
		}
		echo '</td><td style="padding-left:10px; padding-right:10px;">' . date('Y-m-d', strtotime($ord['orden_fecha_recepcion'])) . '</td><td style="padding-left:10px; padding-right:10px;">' .  date('Y-m-d', strtotime($ord['orden_fecha_promesa_de_entrega'])) . '</td>'."\n";
		$preg2 = "SELECT cliente_nombre, cliente_apellidos, cliente_tipo FROM " . $dbpfx . "clientes WHERE cliente_id = '" . $ord['orden_cliente_id'] . "'";
		$matr2 = mysql_query($preg2) or die($preg2);
		$clie2 = mysql_fetch_assoc($matr2);

		echo '<td style="padding-left:10px; padding-right:10px;">' . $clie2['cliente_nombre'] . ' ' . $clie2['cliente_apellidos'] . '</td><td align="center">';
		if($clie2['cliente_tipo'] == '1') { echo 'Asegurado'; } else { echo 'Tercero'; }
		echo '</td></tr>'."\n";
		$j++;
		if ($j == 1) { $fondo = 'obscuro'; } else { $fondo = 'claro'; $j = 0; }
	}
	echo '			</table>';

	} else {
		 echo '<p class="alerta">Acceso no autorizado, ingresar Usuario y Clave correcta</p>';
	}
}

if ($accion==="refproceso") {

	//---- Include del reporte ----
    include('parciales/reportes/refproceso.php');
	
}

if ($accion==="carga_operador") {

	//---- Include del reporte ----
    include('parciales/reportes/carga_operadores.php');
	
}

if ($accion==="cumplimiento_operadores") {

	//---- Include del reporte ----
    include('parciales/reportes/cumplimiento_operadores.php');
	
}

if ($accion==="encuesta") {

	if($f1125125 == '1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1'){

		if($detalle == 1){
		$encabezado = "Detales del " .  date('Y-m-d', strtotime($feini)) . " al " . date('Y-m-d', strtotime($fefin));
//------------ CONSULTA DETALLE DE PREGUNTA -----------------
		$con_preg = "SELECT * FROM " . $dbpfx . "pregs_encuesta WHERE preg_id = '" . $preg . "'";
		$matr_preg = mysql_query($con_preg) or die ("FALLO! selección de pregunta" . $con_preg);
		$detalle_preg = mysql_fetch_assoc($matr_preg);
//--------------- CONSULTA RESULTADOS DE LA PREGUNTA --------------
		$con_resultados = "SELECT preg$preg, obs_$preg, orden_id FROM " . $dbpfx . "encuesta WHERE fecha >= '" . $feini . "' AND fecha <= '" . $fefin . "'";
		$matr_resultados = mysql_query($con_resultados) or die ("FALLO! selección de pregunta" . $con_resultados);
			echo '
			<div style=" width:95%; background-color:#c0c0c0; border-radius: 10px; float:center; box-shadow: 5px 5px 10px #000; margin: 0 auto;">
				<h3 class="blanco">' . $encabezado . '</h3>
				<big class="azul-fuerte texto-blanco"><STRONG>' . $detalle_preg['pregunta'] . '</STRONG></big>
				<table cellspacing="1" cellpadding="2" border="1" width="100%" class="izquierda">
					<tr class="cabeza_tabla">
						<td style="text-align: center;"><STRONG>ORDEN</STRONG></td>
						<td style="text-align: center;"><STRONG>CALIFICACIÓN</STRONG></td>
						<td style="text-align: center;"><STRONG>COMENTARIO</STRONG></td>
					</tr>'."\n";
			$fondo = 'claro';
			while($resultados = mysql_fetch_array($matr_resultados)){
				if($resultados['obs_' . $preg] == ''){
					$obs = '- sin comentario -';
				} else{
					$obs = $resultados['obs_' . $preg];
				}
				$ord = '<a href="ordenes.php?accion=consultar&orden_id=' . $resultados['orden_id'] . '" style=" display:block;">' . $resultados['orden_id'] . '</a>';
				echo '
					<tr class="' . $fondo . '" style="text-align: center;">
						<td style="text-align: center;">' . $ord . '</td>
						<td style="text-align: center;">' . $detalle_preg['resp_' . $resultados['preg' . $preg]] . '</td>
						<td style="text-align: center;">' . $obs . '</td>
					</tr>'."\n";
				if($fondo == 'claro'){
					$fondo = 'obscuro';
				} else{
					$fondo = 'claro';
				}
			}
			echo '				</table>
			<div>'."\n";
		} else{
//--------------- CONSULTA DE PREGUNTAS DE ENCUESTA ---------------
		$elementos_encuesta = "SELECT * FROM " . $dbpfx . "pregs_encuesta";
		$matr_elementos_encuesta = mysql_query($elementos_encuesta) or die ("FALLO! Selección de elementos de encuesta" . $elementos_encuesta);
//--------------- IMPRIMIMOS RESUMEN DE CADA PREGUNTA ---------------
		$cont = 1;
		while($encuesta = mysql_fetch_array($matr_elementos_encuesta)){
//--------------- CONSULTAMOS RESULTADO DE LA PREGUNTA Y LO ALMACENAMOS ---------------
			$preg_respuestas = "SELECT preg$cont FROM " . $dbpfx . "encuesta WHERE fecha >= '" . $feini . "' AND fecha <= '" . $fefin . "' AND preg$cont > '0'";
			$matr_respuestas = mysql_query($preg_respuestas) or die ("FALLO! Selección de respuestas" . $preg_respuestas);
			$total = mysql_num_rows($matr_respuestas);
//-------------- ALMACENAMOS LAS RESPUESTAS ES LA MARTIZ $mtr_resp ---------------
			while($respuestas = mysql_fetch_array($matr_respuestas)){
				$mtr_resp[$cont][$respuestas['preg' . $cont]]++;
			}
//--------------- VARIABLES PARA RECORRER EL NUMERO DE RESPUESTAS ---------------
			$num_resp = $encuesta['cant_calif'];
			$cont_resp = $num_resp;
//--------------- CALCULAMOS EL PROMEDIO TOTAL DE LA PREGUNTA ---------------
			$total_prom = 0;
			$prom = 0;
			$cont2 = 1;
			while($cont_resp >= 1){
				$prom_ind = 0;
				$prom_ind = $mtr_resp[$cont][$cont_resp] * $cont_resp;
				$prom = $prom + $prom_ind;
				$cont_resp--;
			}
			$total_prom = round(((($prom / $total) * 100) / $num_resp),1);
			echo '
			<div style=" width:95%; background-color:#c0c0c0; border-radius: 10px; float:center; box-shadow: 5px 5px 10px #000; margin: 0 auto;">
				 <dl>'."\n";
//--------------- PINTAMOS RSULTADOS DE LAS RESPUESTAS ---------------
			echo '
					<dt class="blanco"><STRONG>PREGUNTA ' . $cont . ':</STRONG></dt>
						<dd><h3>' . $encuesta['pregunta'] . '</h3></dd>
						<dd><big class="azul-fuerte texto-blanco">ENCUESTAS CONTESTADAS: <STRONG>' . $total . '</STRONG></big>'."\n";
//--------------- HABILITAMOS EL ENLACE A DETALLE DE LOS COMENTARIOS ---------------
			if($encuesta['observacion'] == 1){
				echo '
						<a href="reportes.php?accion=encuesta&detalle=1&feini=' . $feini . '&fefin&preg=' . $cont . '" style=" display:block;">ver comentarios...</a>'."\n";
			}
			if($num_resp > 2){
				echo '
						<h2>CALIFICACIÓN: ' . $total_prom . ' %</h2>
						</dd><br>'."\n";
			}
//--------------- CREAMOS UNA GRÁFICA POR CADA RESPUESTA DE LA PREGUNTA ---------------
			echo '
						<dd>
							<table cellspacing="1" cellpadding="2" border="0" width="100%" style="text-align: center;">
								<tr>'."\n";
			$pinta = 'si';
			$cont_resp = 1; // reiniciamos contador
			while($cont_resp <= $num_resp){
//--------------- CALCULAMOS EL PORCENTAJE DE LA RESPUESTA ---------------
				$porc = round((($mtr_resp[$cont][$cont_resp] * 100) / $total),2);
				if($cont_resp > 5 && $pinta == 'si'){
					echo '
								</tr>
								<tr>'."\n";
					$pinta = 'no';
				}
//--------------- PINTAMOS EL RESULTADO EN BARRA ---------------
				echo '									<td>
										<div class="graph">
											<strong class="bar" style="width: ' . $porc . '%;"><big>' . $porc . '% <br></big></strong>
										</div>
										<small>' . $mtr_resp[$cont][$cont_resp] . ' Persona(s)</small><br>
										<strong>' . $encuesta['resp_' . $cont_resp] . '</strong>
									</td>'."\n";
				$porc = 0;
				$cont_resp++;
			}
			echo '								</tr>
							</table>
						</dd>
				 </dl>
			</div>'."\n";
			$cont++;
		}
	}
	}
}

if ($accion==="factura-provs") {

	//---- Include del reporte ----
	include('parciales/reportes/factura-provs.php');
}

if($export != 1) {
	echo '		</div>
	</div>'."\n";
	include('parciales/pie.php');
}

?>
