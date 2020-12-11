<?php 
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/contabilidad.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

	$funnum = 1150000;
	$resultado = validaAcceso($funnum, $dbpfx);
	if($resultado == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		$msg = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

if ($accion==='insertar' || $accion==='mostrar') {
	// no cargar encabezado --
} else {
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}

if ($accion==='rgeneral') {
	
	$funnum = 1150005;
	$resultado = validaAcceso($funnum, $dbpfx);
	if($resultado == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		$msg = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	
	echo '			<form action="contabilidad.php?accion=previa" method="post" enctype="multipart/form-data">
			<table cellpadding="2" cellspacing="1" border="1" class="agrega">';
	echo '				<tr class="cabeza_tabla"><td colspan="2">Captura de movimientos contables</td></tr>'."\n";
	echo '				<tr><td colspan="2" style="text-align:left;">
					<table cellpadding="2" cellspacing="2" border="0" width="100%">
						<tr><td>Título de la póliza</td><td style="text-align:left;">
							<input type="text" name="polnom" size="60" value="' . $_SESSION['conta']['polnom'] . '"/>
						</td></tr>
						<tr><td>Tipo</td><td style="text-align:left;">Diario<input type="radio" name="poltipo" value="1" ';
	if($_SESSION['conta']['poltipo'] == '1') { echo 'checked="checked" '; }
	echo '/> Ingreso<input type="radio" name="poltipo" value="2" ';
	if($_SESSION['conta']['poltipo'] == '2') { echo 'checked="checked" '; }
	echo '/> Egreso<input type="radio" name="poltipo" value="3" ';
	if($_SESSION['conta']['poltipo'] == '3') { echo 'checked="checked" '; }
	echo '/></td></tr>'."\n";
	
	$preg0 = "SELECT * FROM " . $dbpfx . "cont_ciclos WHERE ciclo_abierto = '1'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuentas! 55 " . $preg0);
	echo '						<tr><td>Periodo</td><td style="text-align:left;">'."\n";
	echo '							<select name="ciclo" size="1">
								<option value="">Seleccionar...</option>'."\n";
	while($per =  mysql_fetch_array($matr0)) {
		echo '								<option value="' . $per['ciclo_id'] . '" ';
		if($_SESSION['conta']['ciclo'] == $per['ciclo_id']) { echo 'selected="selected" '; }
		echo '>' . $per['ciclo_id'] . '</option>'."\n";
	}
	echo '							</select></td></tr>'."\n";
	echo '						<tr><td>Fecha de póliza</td><td style="text-align:left;">'."\n";

	require_once("calendar/tc_calendar.php");
		$fpol = strtotime($_SESSION['conta']['fpol']);
		if($fpol == '') { $fpol = time(); } 
		//instantiate class and set properties
		$myCalendar = new tc_calendar("fpol", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
		$myCalendar->setDate(date("d", $fpol), date("m", $fpol), date("Y", $fpol));
//		$myCalendar->setDate(date("d"), date("m"), date("Y"));
//		$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
//		$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2014, 2020);
		$myCalendar->setAutoHide(true, 5000);

		//output the calendar
		$myCalendar->writeScript();	  
		
	echo '							</td></tr>'."\n";
	echo '						<tr><td>Factura</td><td style="text-align:left;">
							<input type="text" name="factura" size="20"  value="' . $_SESSION['conta']['factura'] . '"/>
						</td></tr>
					</table></td></tr>'."\n";
	echo '				<tr><td colspan="2">'."\n";
	echo '					<table cellpadding="2" cellspacing="0" border="1" class="agrega" width=100%">'."\n";
	echo '						<tr class="cabeza_tabla"><td style="text-align:center;">Cuenta Contable</td><td style="text-align:center;">Debe</td><td style="text-align:center;">Haber</td></tr>'."\n";
//	if($renglones > '7') { $ren = $renglones; } 

	$preg1 = "SELECT cat_id, cuenta_contable, nombre_contable FROM " . $dbpfx . "cont_cat WHERE cat_activo = '1' AND cat_afectable = '1'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de cuentas! 95 " . $preg1);

	for($i=0;$i < 13;$i++) {
		echo '						<tr><td>';
		echo '							<select name="categ[' . $i . ']" size="1">
							<option value="">Seleccionar...</option>'."\n";
		mysql_data_seek($matr1, 0);
		while($cat =  mysql_fetch_array($matr1)) {
			echo '							<option value="' . $cat['cat_id'] . '" ';
			if($_SESSION['conta']['categ'][$i] == $cat['cat_id']) { echo 'selected="selected" '; }
			echo '>' . $cat['cuenta_contable'] . ' = ' . $cat['nombre_contable'] . '</option>'."\n";
		}
		echo '							</select></td>'."\n";
		echo '						<td><input type="text" name="montdebe[' . $i . ']" size="8" value="' . $_SESSION['conta']['debe'][$i] . '" /></td>'."\n";
		echo '						<td><input type="text" name="monthaber[' . $i . ']" size="8" value="' . $_SESSION['conta']['haber'][$i] . '" /></td>'."\n";
		echo '					</tr>'."\n";
	}
	unset($_SESSION['conta']);
	echo '					</table>'."\n";
	echo '				</td></tr>'."\n";
	echo '			</table>'."\n";
	echo '			<input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" />'."\n";
	echo '			</form>'."\n";
	
}

elseif($accion==='previa') {
	
	$funnum = 1150005;
	
	$resultado = validaAcceso($funnum, $dbpfx);
	if($resultado == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		$msg = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	
	$error = 'no';
	$_SESSION['conta']['polnom'] = $polnom; 
	$_SESSION['conta']['poltipo'] = $poltipo; 
	$_SESSION['conta']['factura'] = $factura;
	$_SESSION['conta']['ciclo'] = $ciclo;
	$_SESSION['conta']['fpol'] = $fpol;
	$esciclo = date('Ym', strtotime($fpol));
	
	foreach($categ as $i => $v) {
		$_SESSION['conta']['categ'][$i] = $v;
		if(($montdebe[$i] < 0 || $montdebe[$i] > 0) && ($monthaber[$i] < 0 || $monthaber[$i] > 0)) { 
//			$montdebe[$i] = ''; 
//			$monthaber[$i] = '';
			$error = 'si'; $msj = 'Colocó debe y haber en el mismo renglón.<br>'; 
		}
		$_SESSION['conta']['debe'][$i] = $montdebe[$i];
		$_SESSION['conta']['haber'][$i] = $monthaber[$i];
	}
		
	if(!$polnom || $polnom == '') { $error = 'si'; $msj = 'Por favor indique el título de la póliza.<br>';  }
	if(!$poltipo || $poltipo < '1') { $error = 'si'; $msj = 'Por favor indique el tipo de la póliza.<br>';  }
	if($factura == '' && $poltipo > '1') { $error = 'si'; $msj = 'Por favor indique el número de factura.<br>';  }
	if(!$ciclo || $ciclo == '') { $error = 'si'; $msj = 'Por favor seleccione el periodo de la póliza.<br>';  }
	if($ciclo != $esciclo ) { $error = 'si'; $msj = 'La fecha no corresponde al periodo de la póliza.<br>';  }

	if($error === 'no') {
		echo '			<form action="contabilidad.php?accion=insertar" method="post" enctype="multipart/form-data">
			<table cellpadding="2" cellspacing="1" border="1" class="agrega" width="800">';
		echo '				<tr class="cabeza_tabla"><td colspan="2">Confirmación de movimientos contables</td></tr>'."\n";
		echo '				<tr><td colspan="2" style="text-align:left;">
					<table cellpadding="2" cellspacing="2" border="0" width="100%">
						<tr><td>Título de la póliza</td><td style="text-align:left;"><strong>' . $polnom . '</strong><input type="hidden" name="polnom" value="' . $polnom . '" /></td></tr>'."\n";
		echo '						<tr><td>Tipo</td><td style="text-align:left;"><strong>';
		if($poltipo == '1') { echo 'Diario'; } 
		elseif($poltipo == '2') { echo 'Ingreso'; }
		elseif($poltipo == '3') { echo 'Egreso'; }
		echo '</strong><input type="hidden" name="poltipo" value="' . $poltipo . '" /></td></tr>'."\n";
		echo '						<tr><td>Periodo</td><td style="text-align:left;"><strong>' . $ciclo . '</strong><input type="hidden" name="ciclo" value="' . $ciclo . '" /></td></tr>'."\n";
		echo '						<tr><td>Fecha Póliza</td><td style="text-align:left;"><strong>' . $fpol . '</strong><input type="hidden" name="fpol" value="' . $fpol . '" /></td></tr>'."\n";
		echo '						<tr><td>Factura</td><td style="text-align:left;"><strong>' . $factura . '</strong><input type="hidden" name="factura" value="' . $factura . '" /></td></tr>
					</table></td></tr>'."\n";

		echo '				<tr><td colspan="2">'."\n";
		echo '					<table cellpadding="2" cellspacing="0" border="1" class="agrega" width="100%">'."\n";
	echo '						<tr class="cabeza_tabla"><td style="text-align:center;">Cuenta Contable</td><td style="text-align:center;">Debe</td><td style="text-align:center;">Haber</td></tr>'."\n";
		$fondo = 'claro';
		foreach($categ as $i => $v) {
			if($v != '') {
				$preg0 = "SELECT * FROM " . $dbpfx . "cont_cat WHERE cat_id = '$v'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuentas! 181 " . $preg0);
				$cat = mysql_fetch_array($matr0);
				$tmd = $tmd + $montdebe[$i];
				$tmh = $tmh + $monthaber[$i];
				echo '						<tr class="' . $fondo . '"><td style="text-align:left;">';
				echo '<strong>' . $cat['cuenta_contable'] . '</strong> ' . $cat['nombre_contable'];
				echo '</td>'."\n";
				echo '							<td>' . money_format('%n', $montdebe[$i]) . '<input type="hidden" name="montdebe[' . $i . ']" value="' . $montdebe[$i] . '" /></td>'."\n";
				echo '							<td>' . money_format('%n', $monthaber[$i]) . '<input type="hidden" name="monthaber[' . $i . ']" value="' . $monthaber[$i] . '" />';
				echo '					<input type="hidden" name="cat_id[' . $i . ']" value="' . $cat['cat_id'] . '" /><input type="hidden" name="cat_cuenta[' . $i . ']" value="' . $cat['cuenta_contable'] . '" /><input type="hidden" name="cat_nombre[' . $i . ']" value="' . $cat['nombre_contable'] . '" /></td></tr>'."\n";
				if($fondo == 'claro') { $fondo = 'obscuro';} else { $fondo = 'claro';}
			}
		}
		echo '						<tr class="cabeza_tabla"><td>Totales</td><td>' . money_format('%n', $tmd) . '</td><td>' . money_format('%n', $tmh) . '</td></tr>'."\n";
		echo '					</table>'."\n";
		echo '				</td></tr>'."\n";
		echo '			</table>'."\n";
	
		if($tmd === $tmh && $tmd != 0) {
			echo '			<input type="submit" value="Confirmar Póliza" />'."\n";
//			unset($_SESSION['conta']);
		} elseif($tmd === $tmh && $tmd == 0) { 
			echo '			<a href="contabilidad.php?accion=rgeneral" class="alerta">Movimientos en CERO - Corregir</a>'."\n";
		} else {
			echo '			<a href="contabilidad.php?accion=rgeneral" class="alerta">No hay balance - Corregir</a>'."\n";
		}
		echo '			</form>'."\n";
	} else {
		$_SESSION['msjerror'] = $msj;
		redirigir('contabilidad.php?accion=rgeneral');
	}
}

elseif($accion==='insertar') {
	
	$funnum = 1150005;
	
	$resultado = validaAcceso($funnum, $dbpfx);
	if($resultado == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		$msg = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	
	$preg0 = "SELECT * FROM " . $dbpfx . "cont_ciclos WHERE ciclo_id = '$ciclo'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuentas! 226 " . $preg0);
	$per = mysql_fetch_array($matr0);
	$polnum = intval($per['ciclo_poliza']) + 1;
	
	$sql_data = array('poliza_ciclo' => $ciclo,
			'poliza_num' => $polnum,
			'poliza_tipo' => $poltipo,
			'poliza_descripcion' => $polnom,
			'poliza_factura' => $factura,
			'poliza_fecha_ultima' => date('Y-m-d H:i:s', time()),
			'usuario' => $_SESSION['usuario']);
	ejecutar_db($dbpfx . 'cont_polizas', $sql_data);
	bitaconta($ciclo, $polnum, $polnom . ' de la Factura ' . $factura, $dbpfx);
	unset($sql_data);
	
	$param = "ciclo_id = '$ciclo'";
	$sql_data = array('ciclo_poliza' => $polnum);
	ejecutar_db($dbpfx . 'cont_ciclos', $sql_data, 'actualizar', $param);

	foreach($cat_id as $i => $v) {
		$importe = 0;
		if($montdebe[$i] != 0) { $tipo = 0; $importe = $montdebe[$i]; } else { $tipo = 1; $importe = $monthaber[$i]; } 
		$sqlarray = array('lib_poliza_per' => $ciclo,
			'lib_poliza' => $polnum,
			'lib_cuenta' => $cat_cuenta[$i],
			'lib_tipo' => $tipo,
			'lib_descripcion' => $cat_nombre[$i] . ' relativo a Factura ' . $factura);
		if($tipo == '0') { $sqlarray['lib_debe'] = $importe; $exito = 1; }
		elseif($tipo == '1') { $sqlarray['lib_haber'] = $importe;  $exito = 1; }
		else { $exito = 0;}

		if($exito == '1') {
			$lib_id = ejecutar_db($dbpfx . 'cont_libro_diario', $sqlarray, 'insertar');
		} else {
			bitaconta($ciclo, $polnum, 'No se registro el movimiento contable de ' . $cat_cuenta[$i] . ' ' . $cat_nombre . ' relativo a Factura ' . $factura . ' por ' . $importe, $dbpfx);
		}
	}
	unset($_SESSION['conta']);
	redirigir('contabilidad.php?accion=consultarpoliza&ciclo=' . $ciclo . '&poliza=' . $polnum);
	
}

elseif($accion==='listadocuentas') {
	
	$funnum = 1150030;
	
	$resultado = validaAcceso($funnum, $dbpfx);
	if($resultado == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		$msg = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

//	$preg0 = "SELECT * FROM " . $dbpfx . "cont_cat ORDER BY cuenta_contable,cat_subcuenta";
	$preg0 = "SELECT * FROM " . $dbpfx . "cont_cat ORDER BY cuenta_contable";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuentas! 280 " . $preg0);
	$pol = mysql_fetch_array($matr0);
	$fila = mysql_num_rows($matr0);
	$msj = 'No se localizó la póliza indicada';

	if($fila > 0) {
		echo '			<table cellpadding="2" cellspacing="1" border="1" class="agrega" width="800">';
		echo '				<tr class="cabeza_tabla"><td colspan="2">Catálogo de Cuentas</td></tr>'."\n";
		echo '				<tr><td colspan="2" style="text-align:left;">
					<table class="izquierda" cellpadding="2" cellspacing="2" border="0" width="100%">'."\n";
			echo '						<tr>
							<td>Cuenta Contable</td>
							<td>Subcuenta</td>
							<td>Nombre Contable</td>
							<td>Codigo de<br>Agrupamiento</td>
							<td>Naturaleza</td>
							<td>Grupo</td>
							<td>Habilitado</td>
							<td>Afectable<br>Directamente</td>
							<td>Asociada a<br>Sistema</td>
							<td>Nivel<br>(Mayor)</td></tr>'."\n";
		while($cat = mysql_fetch_array($matr0)) {
			echo '						<tr class="' . $fondo . '">
							<td style="text-align:right;"><strong>';
			if($cat['cat_sistema']!='1') { echo '<a href="contabilidad.php?accion=catmod&catid=' . $cat['cat_id'] . '">'; }
			echo $cat['cuenta_contable'];
			if($cat['cat_sistema']!='1') { echo '</a>'; }
			echo '</strong></td>
							<td style="text-align:left;">' . $cat['cat_subcuenta'] . '</td>
							<td>' . $cat['nombre_contable'] . '</td>
							<td>' . $cat['cat_codagrup'] . '</td>
							<td>' . $cat['cat_naturaleza'] . '</td>
							<td>' . $cat['grupo'] . '</td>
							<td>' . $cat['cat_activo'] . '</td>
							<td>' . $cat['cat_afectable'] . '</td>
							<td>' . $cat['cat_sistema'] . '</td>
							<td>' . $cat['cat_cuenta_mayor'] . '</td></tr>'."\n";
			if($fondo == 'claro') { $fondo = 'obscuro';} else { $fondo = 'claro';}
		}
		echo '					</table>'."\n";
		echo '				</td></tr>'."\n";
		echo '			</table>'."\n";
	} else {
		$_SESSION['msjerror'] = $msj;
		redirigir('contabilidad.php');
	}
}

elseif($accion==='catmod') {

	if(validaAcceso('1150030', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		$msg = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	$preg0 = "SELECT * FROM " . $dbpfx . "cont_cat WHERE cat_id = '$catid'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuentas! 337 " . $preg0);
	$cat = mysql_fetch_array($matr0);
	$fila = mysql_num_rows($matr0);
	$msj = 'No se localizó la póliza indicada';

	if($fila > 0) {
		echo '			<table cellpadding="2" cellspacing="1" border="1" class="agrega" width="800">';
		echo '				<tr class="cabeza_tabla"><td colspan="2">Modificación de Cuentas</td></tr>'."\n";
		echo '				<tr><td>Cuenta Contable</td><td><input type="text" name="" value="' . $cat['cuenta_contable'] . '" /></td></tr>
				<tr><td>Subcuenta</td><td><input type="text" name="" value="' . $cat['cat_subcuenta'] . '" /></td></tr>
				<tr><td>Nombre Contable</td><td><input type="text" name="" value="' . $cat['nombre_contable'] . '" /></td></tr>
				<tr><td>Codigo de Agrupamiento</td><td><input type="text" name="" value="' . $cat['cat_codagrup'] . '" /></td></tr>
				<tr><td>Naturaleza</td><td><input type="text" name="" value="' . $cat['cat_naturaleza'] . '" /></td></tr>
				<tr><td>Grupo</td><td><input type="text" name="" value="' . $cat['grupo'] . '" /></td></tr>
				<tr><td>Habilitado</td><td><input type="text" name="" value="' . $cat['cat_activo'] . '" /></td></tr>
				<tr><td>Afectable Directamente</td><td><input type="text" name="" value="' . $cat['cat_afectable'] . '" /></td></tr>
				<tr><td>Asociada a Sistema</td><td><input type="text" name="" value="' . $cat['cat_sistema'] . '" /></td></tr>
				<tr><td>Nivel (Mayor)</td><td><input type="text" name="" value="' . $cat['cat_cuenta_mayor'] . '" /></td></tr>'."\n";
		echo '					</table>'."\n";
		echo '				</td></tr>'."\n";
		echo '			</table>'."\n";
	} else {
		$_SESSION['msjerror'] = $msj;
		redirigir('contabilidad.php');
	}
}

elseif($accion==='consultarpoliza') {
	
	$funnum = 1150010;
	
	$resultado = validaAcceso($funnum, $dbpfx);
	if($resultado == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		$msg = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	
	$preg0 = "SELECT * FROM " . $dbpfx . "cont_polizas WHERE poliza_ciclo = '$ciclo' AND poliza_num = '$poliza'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de polizas!");
	$pol = mysql_fetch_array($matr0);
	$fila = mysql_num_rows($matr0);
	$msj = 'No se localizó la póliza indicada';

	if($fila > 0) {
		echo '			<table cellpadding="2" cellspacing="1" border="1" class="agrega" width="800">';
		echo '				<tr class="cabeza_tabla"><td colspan="2">Detalle de Póliza</td></tr>'."\n";
		echo '				<tr><td colspan="2" style="text-align:left;">
					<table cellpadding="2" cellspacing="2" border="0" width="100%">
						<tr><td>Título de la póliza</td><td style="text-align:left;"><strong>' . $pol['poliza_descripcion'] . '</strong></td></tr>'."\n";
		echo '						<tr><td>Tipo</td><td style="text-align:left;"><strong>';
		if($pol['poliza_tipo'] == '1') { echo 'Diario'; } 
		elseif($pol['poliza_tipo'] == '2') { echo 'Ingreso'; }
		elseif($pol['poliza_tipo'] == '3') { echo 'Egreso'; }
		echo '</strong></td></tr>'."\n";
		echo '						<tr><td>Póliza</td><td style="text-align:left;"><strong>' . $pol['poliza_ciclo'] . '-' . $pol['poliza_num'] . '</strong></td></tr>'."\n";
		echo '						<tr><td>Fecha Póliza</td><td style="text-align:left;"><strong>' . $pol['poliza_fecha'] . '</strong></td></tr>'."\n";
		echo '						<tr><td>Factura</td><td style="text-align:left;"><strong>' . $pol['poliza_factura'] . '</strong></td></tr>
					</table></td></tr>'."\n";

		echo '				<tr><td colspan="2">'."\n";
		echo '					<table cellpadding="2" cellspacing="0" border="1" class="agrega" width="100%">'."\n";
	echo '						<tr class="cabeza_tabla"><td style="text-align:center;">Cuenta Contable</td><td style="text-align:center;">Debe</td><td style="text-align:center;">Haber</td></tr>'."\n";
		$fondo = 'claro';
		$preg1 = "SELECT * FROM " . $dbpfx . "cont_libro_diario WHERE lib_poliza_per = '$ciclo' AND lib_poliza = '$poliza'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de pólizas!");
		$tmd = 0; $tmh = 0;
		while($mov = mysql_fetch_array($matr1)) {
			$tmd = $tmd + $mov['lib_debe'];
			$tmh = $tmh + $mov['lib_haber'];
			$preg2 = "SELECT nombre_contable FROM " . $dbpfx . "cont_cat WHERE cuenta_contable = '" . $mov['lib_cuenta'] . "'";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cuentas! 408 " . $preg2);
			$cat = mysql_fetch_array($matr2);
			echo '						<tr class="' . $fondo . '"><td style="text-align:left;"><strong>' . $mov['lib_cuenta'] . '</strong> ' . $cat['nombre_contable'] . '</td>'."\n";
			echo '							<td>' . money_format('%n', $mov['lib_debe']) . '</td>'."\n";
			echo '							<td>' . money_format('%n', $mov['lib_haber']) . '</td></tr>'."\n";
			if($fondo == 'claro') { $fondo = 'obscuro';} else { $fondo = 'claro';}
		}
		echo '						<tr class="cabeza_tabla"><td>Totales</td><td>' . money_format('%n', $tmd) . '</td><td>' . money_format('%n', $tmh) . '</td></tr>'."\n";
		echo '					</table>'."\n";
		echo '				</td></tr>'."\n";
		echo '			</table>'."\n";
	} else {
		$_SESSION['msjerror'] = $msj;
		redirigir('contabilidad.php');
	}
}

elseif($accion==='listarpoliza') {
	
	$funnum = 1150015;
	
	$resultado = validaAcceso($funnum, $dbpfx);
	if($resultado == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		$msg = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	$ciclo = preparar_entrada_bd($ciclo);
	$cuenta = preparar_entrada_bd($cuenta);
	$tipo = preparar_entrada_bd($tipo);
	$factura = preparar_entrada_bd($factura);
	$error = 'si'; $num_cols = 0;
	$mensaje = 'Se necesita al menos un dato para buscar.<br>';
	
	if (($ciclo!='') || ($tipo!='') || ($factura!='')) {
		$error = 'no'; $mensaje ='';
		$pregunta = "SELECT * FROM " . $dbpfx . "cont_polizas WHERE ";
		if ($ciclo) {$pregunta .= "poliza_ciclo LIKE '%$ciclo%' ";}
		if (($ciclo) && ($tipo)) {$pregunta .= "AND poliza_tipo LIKE '%$tipo%' ";}
			elseif ($tipo) {$pregunta .= "poliza_tipo LIKE '%$tipo%' ";}
		if (($tipo) && ($factura)) {$pregunta .= "AND poliza_factura LIKE '%$factura%' ";} 
			elseif ($factura) {$pregunta .= "poliza_factura LIKE '%$factura%' ";}
//		echo $pregunta;
	} elseif ($cuenta!='') {
		$preg0 = "SELECT * FROM " . $dbpfx . "cont_libro_diario WHERE lib_cuenta LIKE '%$cuenta%' LIMIT 1";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de suborden!");
		$sub = mysql_fetch_array($matr0);
		$pregunta = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id ='" . $sub['orden_id'] . "'";
	} 

	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de polizas!");
	$pol = mysql_fetch_array($matr0);
	$fila = mysql_num_rows($matr0);
	$msj = 'No se localizó la póliza indicada';

	if($fila > 0) {
		echo '			<table cellpadding="2" cellspacing="1" border="1" class="agrega" width="800">';
		echo '				<tr class="cabeza_tabla"><td colspan="2">Detalle de Póliza</td></tr>'."\n";
		echo '				<tr><td colspan="2" style="text-align:left;">
					<table cellpadding="2" cellspacing="2" border="0" width="100%">
						<tr><td>Título de la póliza</td><td style="text-align:left;"><strong>' . $pol['poliza_descripcion'] . '</strong></td></tr>'."\n";
		echo '						<tr><td>Tipo</td><td style="text-align:left;"><strong>';
		if($pol['poliza_tipo'] == '1') { echo 'Diario'; } 
		elseif($pol['poliza_tipo'] == '2') { echo 'Ingreso'; }
		elseif($pol['poliza_tipo'] == '3') { echo 'Egreso'; }
		echo '</strong></td></tr>'."\n";
		echo '						<tr><td>Póliza</td><td style="text-align:left;"><strong>' . $pol['poliza_ciclo'] . '-' . $pol['poliza_num'] . '</strong></td></tr>'."\n";
		echo '						<tr><td>Fecha Póliza</td><td style="text-align:left;"><strong>' . $pol['poliza_fecha'] . '</strong></td></tr>'."\n";
		echo '						<tr><td>Factura</td><td style="text-align:left;"><strong>' . $pol['poliza_factura'] . '</strong></td></tr>
					</table></td></tr>'."\n";

		echo '				<tr><td colspan="2">'."\n";
		echo '					<table cellpadding="2" cellspacing="0" border="1" class="agrega" width="100%">'."\n";
	echo '						<tr class="cabeza_tabla"><td style="text-align:center;">Cuenta Contable</td><td style="text-align:center;">Debe</td><td style="text-align:center;">Haber</td></tr>'."\n";
		$fondo = 'claro';
		$preg1 = "SELECT * FROM " . $dbpfx . "cont_libro_diario WHERE lib_poliza_per = '$ciclo' AND lib_poliza = '$poliza'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de pólizas!");
		$tmd = 0; $tmh = 0;
		while($mov = mysql_fetch_array($matr1)) {
			$tmd = $tmd + $mov['lib_debe'];
			$tmh = $tmh + $mov['lib_haber'];
			$preg2 = "SELECT nombre_contable FROM " . $dbpfx . "cont_cat WHERE cuenta_contable = '" . $mov['lib_cuenta'] . "'";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cuentas! 491 " . $preg2);
			$cat = mysql_fetch_array($matr2);
			echo '						<tr class="' . $fondo . '"><td style="text-align:left;"><strong>' . $mov['lib_cuenta'] . '</strong> ' . $cat['nombre_contable'] . '</td>'."\n";
			echo '							<td>' . money_format('%n', $mov['lib_debe']) . '</td>'."\n";
			echo '							<td>' . money_format('%n', $mov['lib_haber']) . '</td></tr>'."\n";
			if($fondo == 'claro') { $fondo = 'obscuro';} else { $fondo = 'claro';}
		}
		echo '						<tr class="cabeza_tabla"><td>Totales</td><td>' . money_format('%n', $tmd) . '</td><td>' . money_format('%n', $tmh) . '</td></tr>'."\n";
		echo '					</table>'."\n";
		echo '				</td></tr>'."\n";
		echo '			</table>'."\n";
	} else {
		$_SESSION['msjerror'] = $msj;
		redirigir('contabilidad.php');
	}
}

elseif($accion==='nvamayor') {
	
	$funnum = 1150020;
	
	$resultado = validaAcceso($funnum, $dbpfx);
	if($resultado == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		$msg = 'Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	
	$error = 'no';
	$_SESSION['conta']['nvomayor'] = $nvomayor; 
	$_SESSION['conta']['nommayor'] = $nommayor; 
			
	if(!$nvomayor || $nvomayor == '') { $error = 'si'; $msj = 'Por favor indique el número de la Nueva cuenta Mayor.<br>';  }
	if(!$nommayor || $nommayor < '1') { $error = 'si'; $msj = 'Por favor indique el nombre de la Nueva cuenta Mayor.<br>';  }
	$preg0 = "SELECT * FROM " . $dbpfx . "cont_cat WHERE cuenta_contable = '$nvomayor' AND cat_cuenta_mayor = '1'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuentas! 526 " . $preg0);
	$cat = mysql_fetch_array($matr0);
	$filas = mysql_num_rows($matr0);
	if($filas > 0) { $error = 'si'; $msj = 'Hay otra Cuenta Mayor Igual a ' . $nvomayor . '<br>'; }
	
	if($error === 'no') {
		echo '			<form action="contabilidad.php?accion=insertamayor" method="post" enctype="multipart/form-data">'."\n";
		echo '			<table cellpadding="2" cellspacing="1" border="1" class="agrega" width="800">';
		echo '				<tr class="cabeza_tabla"><td colspan="2">Confirmar datos para Crear Nueva Cuenta Mayor</td></tr>'."\n";
		echo '				<tr><td>Número de Cuenta</td><td style="text-align:left;"><strong>' . $nvomayor . '</strong><input type="hidden" name="polnom" value="' . $nvomayor . '" /></td></tr>'."\n";
		echo '				<tr><td>Nombre de Cuenta</td><td style="text-align:left;"><strong>' . $nummayor . '</strong><input type="hidden" name="polnom" value="' . $nummayor . '" /></td></tr>'."\n";
		echo '				<tr class="cabeza_tabla"><td colspan="2"><button name="enviar" value="Enviar" type="submit"> </button><button name="Regresar" value="1" type="button"></button></td></tr>'."\n";
		echo '			</form>'."\n";
	} else {
		$_SESSION['msjerror'] = $msj;
		redirigir('contabilidad.php?accion=rgeneral');
	}
}

else {

	$funnum = 1150000;
	$resultado = validaAcceso($funnum, $dbpfx);
	if($resultado == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		if (isset($mensaje)) { echo '			<p class="alerta">' . $mensaje .  '</p>'; }
		echo '			<table cellpadding="5" cellspacing="0" border="0" width="80%">
				<tr>
					<td valign="top" width="33%">
						<div class="obscuro espacio">
							<h3>Módulo General de Registro</h3>
							<form action="contabilidad.php?accion=rgeneral" method="post"><input type="submit" value="Ir" /></form>
						</div>'."\n";
		echo '						<div class="obscuro espacio">
							<h3>Consultar Póliza</h3>
							<form action="contabilidad.php?accion=consultarpoliza" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Periodo</td><td><input type="text" name="ciclo" maxlength="6" /></td></tr>
									<tr><td>Póliza</td><td><input type="text" name="poliza" maxlength="11" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
								</table>
							</form>
						</div>'."\n";
/*						<div class="obscuro espacio">
							<h3>Cambiar Clave</h3>
							<form action="usuarios.php?accion=clave" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Clave actual: </td><td><input type="password" name="clave" size="10" maxlength="20" /></td></tr>
									<tr><td>Clave nueva: </td><td><input type="password" name="clave1" size="10" maxlength="20" /></td></tr>
									<tr><td>Repetir clave nueva: </td><td><input type="password" name="clave2" size="10" maxlength="20" />
									<input type="hidden" name="usuario" value="' . $_SESSION['usuario'] . '" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></td></tr>
								</table>
							</form>
						</div>'."\n";
*/
		echo '					</td>';
	}
	$funnum = 1150000;
	$resultado = validaAcceso($funnum, $dbpfx);
	if($resultado == '1' || ($solovalacc != 1 && ($_SESSION['rol12']=='1' || $_SESSION['rol13']=='1' || $_SESSION['rol14']=='1'))) {
		$preg1 = "SELECT cat_id, cuenta_contable, nombre_contable FROM " . $dbpfx . "cont_cat WHERE cat_activo = '1' AND cat_cuenta_mayor = '1'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de cuentas! 587 " . $preg1);


		echo '					<td valign="top" width="67%">
						<div class="obscuro espacio">
							<h3>Catálogo de Cuentas Contables</h3>
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td colspan="2"><a href="contabilidad.php?accion=listadocuentas">Listado de Cuentas</a></td></tr>
									<tr><td colspan="2" style="text-align:left;">&nbsp;</td></tr>
									<tr><td colspan="2" style="text-align:left;"><form action="contabilidad.php?accion=nvamayor" method="post">Crear Nueva Cuenta Mayor&nbsp;</td>
									<tr><td>Número de Cuenta:</td><td><input type="mayor" name="nvomayor" size="10" maxlength="20" /></td></tr>
									<tr><td>Descripción de Cuenta:</td><td><input type="mayor" name="nommayor" size="40" maxlength="128" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></form></td></tr>
									<tr><td colspan="2" style="text-align:left;">&nbsp;</td></tr>
									<tr><td colspan="2" style="text-align:left;"><form action="contabilidad.php?accion=nvasubcuenta" method="post">Crear Nueva Subcuenta</td></tr>'."\n";
		echo '									<tr><td>Cuenta Mayor:</td><td>'."\n";
		echo '										<select name="cat_id" size="1">
											<option value="">Seleccionar Cuenta Mayor</option>'."\n";
//		mysql_data_seek($matr1, 0);
		while($cat =  mysql_fetch_array($matr1)) {
			echo '											<option value="' . $cat['cat_id'] . '" >' . $cat['cuenta_contable'] . ' = ' . $cat['nombre_contable'] . '</option>'."\n";
		}
		echo '										</select></td></tr>'."\n";
										
		echo '									<tr><td>Número de Subcuenta:</td><td><input type="text" name="nvasub" size="10" maxlength="20" /></td></tr>
									<tr><td>Descripción de Subcuenta:</td><td><input type="text" name="nomsub" size="40" maxlength="128" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" /></form></td></tr>
								</table>
						</div>'."\n";
		echo '						<div class="obscuro espacio">
							<h3>Informes</h3>
							<form action="contabilidad.php?accion=catalogo" method="post"><input type="submit" value="Ir" /></form>
						</div>'."\n";

/*						<div class="obscuro espacio">
							<h3>Listar Usuarios</h3>
							<a href="usuarios.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/consultar.png" alt="Listar usuarios" title="Listar usuarios"></a>
						</div>
*/
		echo '					</td>';
	}
	echo '				</tr></table>';
}

?>
		</div>
	</div>
<?php include('parciales/pie.php'); ?>