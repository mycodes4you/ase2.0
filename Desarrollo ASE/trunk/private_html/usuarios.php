<?php 
foreach($_POST as $k => $v){$$k=$v;}  //echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');


/*  ----------------  obtener nombres de usuarios   ------------------- */
	
		$consulta = "SELECT usuario, nombre, apellidos, comision FROM " . $dbpfx . "usuarios WHERE rol09 = '1' AND acceso = '0' AND activo = '1' ORDER BY nombre";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo selección de usuarios!");
//		$num_provs = mysql_num_rows($arreglo);
   	$usu = array();
//   	$provs[0] = 'Sin Proveedor';
		while ($usua = mysql_fetch_array($arreglo)) {
			$usu[$usua['usuario']] = array('nom' => $usua['nombre'], 'ape' => $usua['apellidos'], 'com' => $usua['comision']);
		}

/*
if (($accion==='insertar') || ($accion==='actualizar') || ($accion==='ingresar') || ($accion==='clave') || ($accion==='ajustar') || ($accion==='terminar')) { 
	// no cargar encabezado
} else {
	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}
*/

if ($accion==="ingresar") {
	
	$funnum = 1135000;
	
	$usuario = limpiarNumero($usuario);
	$usuario = intval($usuario);
	$pregunta = "SELECT * FROM " . $dbpfx . "usuarios WHERE usuario = '$usuario' AND activo = '1'";	
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección!");
  	$usr = mysql_fetch_array($matriz);
	$num_cols = mysql_num_rows($matriz);
	if($num_cols == 1) {
		$verificar = md5($clave);
		//$verificar = $clave;
		if ($verificar==$usr['clave']) {
			if($usr['codigo'] >= '2000' && $usr['ensesion'] == '1' && $verifica_ses == '1') {
				redirigir('usuarios.php?mensaje=El Usuario ya tiene una sesión activa!');
			}
			$instan = md5(preg_replace('/[^A-Za-z0-9]/', '', time()));
			session_unset();
			session_destroy();
			session_id($instan.$usr['usuario']);
			session_start();
			//$_SESSION[$dbpfx][$usr['usuario']]= '1';
			$_SESSION['usuario']= $usr['usuario'];
			$_SESSION['puesto']= $usr['puesto'];
			$_SESSION['localidad']= $usr['localidad'];
			$_SESSION['acceso']= $usr['acceso'];
			$_SESSION['codigo']= $usr['codigo'];
			$_SESSION['nombre']= $usr['nombre'];
			$_SESSION['apellidos']= $usr['apellidos'];
			$_SESSION['email']= $usr['email'];
			$_SESSION['aseg'] = $usr['aseg'];
			$_SESSION['prov'] = $usr['prov'];
			$_SESSION['rol01'] = $usr['rol01'];
			$_SESSION['rol02'] = $usr['rol02'];
			$_SESSION['rol03'] = $usr['rol03'];
			$_SESSION['rol04'] = $usr['rol04'];
			$_SESSION['rol05'] = $usr['rol05'];
			$_SESSION['rol06'] = $usr['rol06'];
			$_SESSION['rol07'] = $usr['rol07'];
			$_SESSION['rol08'] = $usr['rol08'];
			$_SESSION['rol09'] = $usr['rol09'];
			$_SESSION['rol10'] = $usr['rol10'];
			$_SESSION['rol11'] = $usr['rol11'];
			$_SESSION['rol12'] = $usr['rol12'];
			$_SESSION['rol13'] = $usr['rol13'];
			$_SESSION['rol14'] = $usr['rol14'];
			$_SESSION['rol17'] = $usr['rol17'];
			touch("../tmp/access-" . session_id());
			$parme = "usuario = '" . $usr['usuario'] ."'";
			$sqdat = ['ensesion' => '1'];
			ejecutar_db($dbpfx . 'usuarios', $sqdat, 'actualizar', $parme);
			unset($sqdat);

// ------ Obtener la IP del usuario ------
/*			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				} else {
					$ip = $_SERVER['REMOTE_ADDR'];
				}
			} */
			
    if (isset($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
        $ip = $_SERVER["HTTP_X_FORWARDED"];
    } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
        $ip = $_SERVER["HTTP_FORWARDED_FOR"];
    } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
        $ip = $_SERVER["HTTP_FORWARDED"];
    } else {
        $ip = $_SERVER["REMOTE_ADDR"];
    }
			
			
			bitacora('0', 'El usuario ' . $_SESSION['usuario'] . ' se firmó con la IP: ' . $ip, $dbpfx);
			// ------- Función para cambio de password cada cierto tiempo
			if($fechapassword > '0' && $_SESSION['acceso'] == '0') {
				$dias = intval((strtotime("now") - strtotime( $usr['fecha_password'] )) / 86400);
				if($dias > $fechapassword) {
					$_SESSION['cambio_pass'] = 1;
					redirigir('usuarios.php?mensaje=Es necesario hacer cambio de contraseña&chang=1');
				} else {
					$_SESSION['cambio_pass'] = 0;
					redirigir('index.php');
				}
			}
			redirigir('index.php');
		}
		redirigir('usuarios.php?mensaje=El Usuario y/o la Clave son incorrectos <br> Verifica e intenta de nuevo.');
	}
	redirigir('usuarios.php?mensaje=El Usuario es incorrecto o se encuentra inactivo <br> Verifica e intenta de nuevo.');
}

elseif (($accion==="crear") || ($accion==="modificar")) {
	
	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}

	$funnum = 1135005;
	$retorno = validaAcceso($funnum, $dbpfx);

	if ($retorno == '1') {
		$mensaje = '';
		unset($_SESSION['pers']);
		$_SESSION['pers'] = array();
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}

	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	
		

	if($accion==="modificar") {
		$pregunta = "SELECT * FROM " . $dbpfx . "usuarios WHERE usuario = '$usuario'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección! " . $pregunta);
		$num_cols = mysql_num_rows($matriz);
		if ($num_cols > 0) {
			$_SESSION['pers'] = mysql_fetch_array($matriz);
		} else {
			$accion='crear';
		}
	}

	if ($accion==="modificar") { $tipo = 'actualizar';}
	else {
		$tipo = 'insertar';
		$_SESSION['pers']['nombre'] = (isset($_SESSION['pers']['nombre'])) ? $_SESSION['pers']['nombre'] : '';
		$_SESSION['pers']['apellidos'] = (isset($_SESSION['pers']['apellidos'])) ? $_SESSION['pers']['apellidos'] : '';
		$_SESSION['pers']['puesto'] = (isset($_SESSION['pers']['puesto'])) ? $_SESSION['pers']['puesto'] : '';
		$_SESSION['pers']['codigo'] = (isset($_SESSION['pers']['codigo'])) ? $_SESSION['pers']['codigo'] : '';
		$_SESSION['pers']['areas'] = (isset($_SESSION['pers']['areas'])) ? $_SESSION['pers']['areas'] : '';
		$_SESSION['pers']['aseg'] = (isset($_SESSION['pers']['aseg'])) ? $_SESSION['pers']['aseg'] : '';
		$_SESSION['pers']['prov'] = (isset($_SESSION['pers']['prov'])) ? $_SESSION['pers']['prov'] : '';
		$_SESSION['pers']['rol01'] = (isset($_SESSION['pers']['rol01'])) ? $_SESSION['pers']['rol01'] : '';
		$_SESSION['pers']['rol02'] = (isset($_SESSION['pers']['rol02'])) ? $_SESSION['pers']['rol02'] : '';
		$_SESSION['pers']['rol03'] = (isset($_SESSION['pers']['rol03'])) ? $_SESSION['pers']['rol03'] : '';
		$_SESSION['pers']['rol04'] = (isset($_SESSION['pers']['rol04'])) ? $_SESSION['pers']['rol04'] : '';
		$_SESSION['pers']['rol05'] = (isset($_SESSION['pers']['rol05'])) ? $_SESSION['pers']['rol05'] : '';
		$_SESSION['pers']['rol06'] = (isset($_SESSION['pers']['rol06'])) ? $_SESSION['pers']['rol06'] : '';
		$_SESSION['pers']['rol07'] = (isset($_SESSION['pers']['rol07'])) ? $_SESSION['pers']['rol07'] : '';
		$_SESSION['pers']['rol08'] = (isset($_SESSION['pers']['rol08'])) ? $_SESSION['pers']['rol08'] : '';
		$_SESSION['pers']['rol09'] = (isset($_SESSION['pers']['rol09'])) ? $_SESSION['pers']['rol09'] : '';
		$_SESSION['pers']['rol10'] = (isset($_SESSION['pers']['rol10'])) ? $_SESSION['pers']['rol10'] : '';
		$_SESSION['pers']['rol11'] = (isset($_SESSION['pers']['rol11'])) ? $_SESSION['pers']['rol11'] : '';
		$_SESSION['pers']['rol12'] = (isset($_SESSION['pers']['rol12'])) ? $_SESSION['pers']['rol12'] : '';
		$_SESSION['pers']['rol13'] = (isset($_SESSION['pers']['rol13'])) ? $_SESSION['pers']['rol13'] : '';
		$_SESSION['pers']['rol14'] = (isset($_SESSION['pers']['rol14'])) ? $_SESSION['pers']['rol14'] : '';
		$_SESSION['pers']['rol15'] = (isset($_SESSION['pers']['rol15'])) ? $_SESSION['pers']['rol15'] : '';
		$_SESSION['pers']['rol16'] = (isset($_SESSION['pers']['rol16'])) ? $_SESSION['pers']['rol16'] : '';
		$_SESSION['pers']['rol17'] = (isset($_SESSION['pers']['rol17'])) ? $_SESSION['pers']['rol17'] : '';
		$_SESSION['pers']['activo'] = (isset($_SESSION['pers']['activo'])) ? $_SESSION['pers']['activo'] : '';
		$_SESSION['pers']['ensesion'] = (isset($_SESSION['pers']['ensesion'])) ? $_SESSION['pers']['ensesion'] : '';
		$_SESSION['pers']['usuario'] = (isset($_SESSION['pers']['usuario'])) ? $_SESSION['pers']['usuario'] : '';
		$_SESSION['pers']['calle_numero'] = (isset($_SESSION['pers']['calle_numero'])) ? $_SESSION['pers']['calle_numero'] : '';
		$_SESSION['pers']['municipio'] = (isset($_SESSION['pers']['municipio'])) ? $_SESSION['pers']['municipio'] : '';
		$_SESSION['pers']['colonia'] = (isset($_SESSION['pers']['colonia'])) ? $_SESSION['pers']['colonia'] : '';
		$_SESSION['pers']['estado'] = (isset($_SESSION['pers']['estado'])) ? $_SESSION['pers']['estado'] : '';
		$_SESSION['pers']['telefono'] = (isset($_SESSION['pers']['telefono'])) ? $_SESSION['pers']['telefono'] : '';
		$_SESSION['pers']['telefono_laboral'] = (isset($_SESSION['pers']['telefono_laboral'])) ? $_SESSION['pers']['telefono_laboral'] : '';
		$_SESSION['pers']['movil'] = (isset($_SESSION['pers']['movil'])) ? $_SESSION['pers']['movil'] : '';
		$_SESSION['pers']['email'] = (isset($_SESSION['pers']['email'])) ? $_SESSION['pers']['email'] : '';
		$_SESSION['pers']['email_laboral'] = (isset($_SESSION['pers']['email_laboral'])) ? $_SESSION['pers']['email_laboral'] : '';
		$_SESSION['pers']['rfc'] = (isset($_SESSION['pers']['rfc'])) ? $_SESSION['pers']['rfc'] : '';
		$_SESSION['pers']['contrato'] = (isset($_SESSION['pers']['contrato'])) ? $_SESSION['pers']['contrato'] : '';
		$_SESSION['pers']['comision'] = (isset($_SESSION['pers']['comision'])) ? $_SESSION['pers']['comision'] : '';


	} 

			


	echo '	
		<div class="col-lg-6 col-md-8 col-sm-12" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px; margin-left: 5px; background-color: #b9b7b7;">
		
			<div class="row"> <!-box header del título. -->
				<div class="col-lg-12 col-md-12 col-sm-12">
	  				<div class="content-box-header">
						<div class="panel-title">
		  					<h2>Datos del Usuario: ' . $_SESSION['pers']['nombre'] . ' ' . $_SESSION['pers']['apellidos'] . '</h2>
						</div>
			  		</div>
				</div>
			</div>
			<br>'."\n"; 

	echo '
		<div class="row">

			<form action="usuarios.php?accion=' . $tipo . '" method="post" enctype="multipart/form-data">
				<div class="col-md-12 panel-body">
					<div class="form-group">
					<big><b>Datos Laborales</b></big><p>
              			<div class="col-lg-12 col-md-8 col-sm-8">		
							<div class="form-group" style="margin-bottom: 1rem;">
								<label style="font-size: initial;" for="nombre">Nombre</label>
									<input type="text" class="form-control"  name="nombre" style="width: 100%" maxlength="120" value="'. $_SESSION['pers']['nombre'] . '">
							</div>
							<div class="form-group" style="margin-bottom: 1rem;">
								<label style="font-size: initial;" for="apellidos">Apellidos</label>
									<input type="text" class="form-control"  name="apellidos" style="width: 100%" maxlength="120" value="'. $_SESSION['pers']['apellidos'] . '">
							</div>
							<div class="form-group" style="margin-bottom: 1rem;">
								<label style="font-size: initial;" for="puesto">Puesto</label>
									<input type="text" class="form-control"  name="puesto" style="width: 100%" maxlength="120" value="'. $_SESSION['pers']['puesto'] . '">
							</div>
							<div class="form-group" style="margin-bottom: 1rem;">
								<label style="font-size: initial;" for="email">Email Laboral</label>
									<input type="text" class="form-control"  name="email" style="width: 100%" maxlength="40" value="'. $_SESSION['pers']['email'] . '">
							</div>
							<div class="form-group" style="margin-bottom: 1rem;">
								<label style="font-size: initial;" for="telefono_laboral">Teléfono Laboral</label>
									<input type="text" class="form-control" name="telefono_laboral" style="width: 100%" maxlength="40" value="'. $_SESSION['pers']['telefono_laboral'] . '">
							</div>

							<div class="form-group">
								<label style="font-size: initial;" for="codigo">Codigo de Puesto:</label>
									<select name="codigo" class="form-control" size="1">
										<option value=""'; if ($_SESSION['pers']['codigo']=='') {echo ' selected="1"';} echo '>Seleccione...</option>
										<option value="10"'; if ($_SESSION['pers']['codigo']==10) {echo ' selected="1"';} echo '>' . $lang['GERENTE'] . '</option>
										<option value="12"'; if ($_SESSION['pers']['codigo']==12) {echo ' selected="1"';} echo '>' . $lang['ASISTENTE'] . '</option>
										<option value="15"'; if ($_SESSION['pers']['codigo']==15) {echo ' selected="1"';} echo '>' . $lang['JEFE DE TALLER'] . '</option>
										<option value="20"'; if ($_SESSION['pers']['codigo']==20) {echo ' selected="1"';} echo '>' . $lang['VALUADOR'] . '</option>
										<option value="30"'; if ($_SESSION['pers']['codigo']==30) {echo ' selected="1"';} echo '>' . $lang['ASESOR'] . '</option>
										<option value="40"'; if ($_SESSION['pers']['codigo']==40) {echo ' selected="1"';} echo '>' . $lang['JEFE DE AREA'] . '</option>
										<option value="50"'; if ($_SESSION['pers']['codigo']==50) {echo ' selected="1"';} echo '>' . $lang['ALMACEN'] . '</option>
										<option value="60"'; if ($_SESSION['pers']['codigo']==60) {echo ' selected="1"';} echo '>' . $lang['OPERADOR'] . '</option>
										<option value="70"'; if ($_SESSION['pers']['codigo']==70) {echo ' selected="1"';} echo '>' . $lang['AUXILIAR'] . '</option>
										<option value="75"'; if ($_SESSION['pers']['codigo']==75) {echo ' selected="1"';} echo '>' . $lang['VIGILANCIA'] . '</option>
										<option value="80"'; if ($_SESSION['pers']['codigo']==80) {echo ' selected="1"';} echo '>' . $lang['CALIDAD'] . '</option>
										<option value="90"'; if ($_SESSION['pers']['codigo']==90) {echo ' selected="1"';} echo '>' . $lang['COBRANZA'] . '</option>
										<option value="100"'; if ($_SESSION['pers']['codigo']==100) {echo ' selected="1"';} echo '>' . $lang['PAGOS'] . '</option>
										<option value="2000"'; if ($_SESSION['pers']['codigo']==2000) {echo ' selected="1"';} echo '>' . $lang['ASEGURADORA'] . '</option>
									</select>
							</div><br>

							<div class="form-group">
								<table>
									<tbody>
										<tr>
											<td colspan="3">
											<div class="form-group" style="margin-bottom: 1rem;">
												<big><b>Roles Adicionales para este Usuario</b></big><p>
											</div>
											</td>
										</tr>
										<tr>
											<td>
												<input type="checkbox" name="rol02" value="1"'; if($_SESSION['pers']['rol02']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol02">' . $lang['GERENTE'] . '</label>
											</td>
											<td>
												<input type="checkbox" name="rol08" value="1"'; if($_SESSION['pers']['rol08']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol02">' . $lang['ALMACEN'] . '</label>
											</td>
											<td>											
												<input type="checkbox" name="rol03" value="1"'; if($_SESSION['pers']['rol03']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol02">' . $lang['ASISTENTE'] . '</label>
											</td>
										</tr>
										<tr>
											<td>
												<input type="checkbox" name="rol11" value="1"'; if($_SESSION['pers']['rol11']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol02">' . $lang['CALIDAD'] . '</label>
											</td>
											<td>
												<input type="checkbox" name="rol04" value="1"'; if($_SESSION['pers']['rol04']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol03">' . $lang['JEFE DE TALLER'] . '</label>
											</td>
											<td>
												<input type="checkbox" name="rol12" value="1"'; if($_SESSION['pers']['rol12']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol03">' . $lang['COBRANZA'] . '</label>
											</td>
										</tr>
										<tr>
											<td>
												<input type="checkbox" name="rol05" value="1"'; if($_SESSION['pers']['rol05']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol03">' . $lang['VALUADOR'] . '</label>
											</td>
											<td>
												<input type="checkbox" name="rol13" value="1"'; if($_SESSION['pers']['rol13']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol03">' . $lang['PAGOS'] . '</label>
											</td>
											<td>
												<input type="checkbox" name="rol06" value="1"'; if($_SESSION['pers']['rol06']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol03">' . $lang['ASESOR'] . '</label>
											</td>
										</tr>
										<tr>
											<td>
												<input type="checkbox" name="rol09" value="1"'; if($_SESSION['pers']['rol09']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol03">' . $lang['OPERADOR'] . '</label>
											</td>
											<td>
												<input type="checkbox" name="rol07" value="1"'; if($_SESSION['pers']['rol07']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol03">' . $lang['JEFE DE AREA'] . '</label>
											</td>
											<td>
												<input type="checkbox" name="rol10" value="1"'; if($_SESSION['pers']['rol10']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol03">' . $lang['AUXILIAR'] . '</label>
											</td>
										</tr>
										<tr>
											<td colspan="3">
												<input type="checkbox" name="rol15" value="1"'; if($_SESSION['pers']['rol15']==1) { echo ' checked="checked"'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="rol03">' . $lang['VIGILANCIA'] . '</label>
											</td>
										</tr>
									</tbody>
								</table>
							</div><br>';
							if ($accion == 'modificar') {
								echo '
							<div class="form-group" style="margin-bottom: 1rem;padding-left: 20%;">';
									$seleccionado = $_SESSION['pers']['codigo'];
									$rolnav1 = '';
									foreach ($cod_puesto as $key => $value) {
										if($key == $seleccionado){
											$rolnav1.= $cod_puesto[$key];
											//echo $rolnav1;
										}
									}
								
										
							echo '	
								<a href="usuarios.php?accion=permisos&amp;userautoshop=' . $_SESSION['pers']['usuario'] . '&amp;nombre=' . $_SESSION['pers']['nombre'] . ' ' . $_SESSION['pers']['apellidos'] . '&amp;rolnav=' . $rolnav1 . '">
								

									
									<button type="button" class="btn btn-success" style="margin-bottom: 20px;"><b>Detalle de Permisos</b>
									</button>
								</a>
							</div>';
							}
							else {
							echo '
							<div class="form-group" style="margin-bottom: 1rem;padding-left: 20%;">';
									
										
							echo '	
								
								

									
									<button type="button" class="btn btn-success" style="margin-bottom: 20px;" disabled><b>Para Modificar los Detalles de Permisos <br> Primero debe crear el usuario y regresar.</b>
									</button>
								
							</div>';


							}
							echo '
							<table>
								<tbody>	
									<tr>
										<td colspan="3">
											<div class="form-group" style="margin-bottom: 1rem;">
												<big><b>Áreas activas para ' . $lang['JEFE DE AREA'] . ' y ' . $lang['OPERADOR'] . '</b></big><p>
												
											</div>
										</td>
										</tr>
										<tr>
											<td style="text-align:left;" colspan="3">'."\n";
												$ubr = 0;
												
												$uarea = explode('|', $_SESSION['pers']['areas']);
												for($i=1;$i<=$num_areas_servicio;$i++){

													echo '<input type="checkbox" name="area['.$i.']" value="'.$i.'"';
													foreach($uarea as $k) {
														if($k == $i) {
															echo ' checked="checked"';
														}

													} 
													echo ' />' . constant('NOMBRE_AREA_'.$i) . '&nbsp;&nbsp;'."\n";	
													
													if($ubr==2 || $ubr==5){ 
														echo '<br>';
													}
													$ubr++;
												}
												
									echo '	</td>
										</tr>
										</tbody>
									</table><br>';
								if ($accion==="modificar") {
								echo '	
								<big><b>Estado y Clave</b></big><p>
								<div style="border-style: dashed;border-width: 2px; border-color: #007bff; width: min-content;background-color: #b1f493;">
									<table>
										<tbody>
										<tr>
											<td>
												<label class="form-check-label style="font-size: initial;" for="">Usuario Activo
												</label>
											
												<input type="checkbox" name="activo"'; if ($_SESSION['pers']['activo']==1) {echo ' checked="checked"';} echo ' />
											</td>
										
											<td>
												<label class="form-check-label style="font-size: initial;" for="">Usuario Conectado
												</label>
											<input type="checkbox" name="ensesion"'; if ($_SESSION['pers']['ensesion']==1) {echo ' checked="checked"';} echo ' />
											</td>
										</tr>
										<tr>
											<td colspan="2">
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div class="form-group" style="margin-bottom: 1rem;">
													<label style="font-size: initial;" for="email">Nueva Clave
													</label>											
													<input type="password" class="form-control" name="clavesu01" style="width: 100%"/>
													<input type="hidden" name="usuario" style="width: 100%" maxlength="15" value="' . $usuario . '"/>
												</div>
											</td>
										</tr>
										</tbody>
									</table>
								</div>'."\n";
								}
								echo'				
								<br>
								<big><b>Datos Domiciliarios</b></big><p>
								<div class="form-group" style="margin-bottom: 1rem;">
								<label style="font-size: initial;" for="calle_numero">Calle y número de Domicilio</label><br>
										<input type="text" class="form-control" name="calle_numero" style="width: 100%" maxlength="40" value="' . $_SESSION['pers']['calle_numero'] . '" />
								</div>
								<div class="form-row" style="display: flex; margin-bottom: 1rem;">
									<div class="form-group">
										<label style="font-size: initial;" for="colonia">Colonia</label><br>
											<input type="text" class="form-control" name="colonia" style="width: 100%" maxlength="60" value="' . $_SESSION['pers']['colonia'] . '" />
									</div>
									<div class="form-group">
										<label style="font-size: initial;" for="">Delegación o Municipio</label><br>
											<input type="text" class="form-control" name="municipio" style="width: 100%" maxlength="60" value="' . $_SESSION['pers']['municipio'] . '" />
									</div>
								</div>
								<div class="form-group" style="margin-bottom: 1rem;">
									<label style="font-size: initial;" for="">Estado</label><br>
										<input type="text" class="form-control" name="estado" style="width: 100%" maxlength="60" value="' . $_SESSION['pers']['estado'] . '" />
								</div>
								<div class="form-row" style="display: flex; margin-bottom: 1rem;">
									<div class="form-group">
										<label style="font-size: initial;" for="">Teléfono</label><br>
											<input type="text" class="form-control" name="telefono" style="width: 100%" maxlength="40" value="' . $_SESSION['pers']['telefono'] . '" />
									</div>
									<div class="form-group">
										<label style="font-size: initial;" for="">Celular</label><br>
											<input type="text" class="form-control" name="movil" style="width: 100%" maxlength="40" value="' . $_SESSION['pers']['movil'] . '" />
									</div>
								</div>
								<div class="form-group" style="margin-bottom: 1rem;">
									<label style="font-size: initial;" for="">E-mail personal</label><br>
										<input type="text" class="form-control" name="email_personal" style="width: 100%" maxlength="120" value="' . $_SESSION['pers']['email_personal'] . '" />
								</div>

										<big><b>Datos fiscales</b></big><p>

								<div class="form-row" style="display: flex; margin-bottom: 1rem;">
									<div class="form-group">
										<label style="font-size: initial;" for="">RFC</label><br>
											<input type="text" class="form-control" name="rfc" style="width: 100%" maxlength="15" value="' . $_SESSION['pers']['rfc'] . '"/>
									</div>
									<div class="form-group">
										<label style="font-size: initial;" for="">Contrato</label><br>
											<input type="text" class="form-control" name="contrato" style="width: 100%" maxlength="15" value="' . $_SESSION['pers']['contrato'] . '"/>
									</div>
									<div class="form-group">
										<label style="font-size: initial;" for="">Comisión</label><br>
											<input type="text" class="form-control" name="comision" style="width: 100%" maxlength="15" value="' . $_SESSION['pers']['comision'] . '"/>
									</div>
								</div>

										<input type="submit" class="btn btn-success btn-md" class="btn btn-success" value="Enviar" />';
										//<input type="reset" class="btn btn-danger" name="limpiar" value="Borrar" />
				echo '			</div>
							</div>
						</div>
			</form>
		</div>';
		/* FORMULARIO ANTERIOR 
	echo '							
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td colspan="3"><span class="alerta">' . $_SESSION['pers']['mensaje'] . '</span></td></tr>
		<tr><td>Nombre</td><td colspan="2" style="text-align:left;"><input type="text" name="nombre" size="60" maxlength="60" value="' . $_SESSION['pers']['nombre'] . '" /></td></tr>
		<tr><td>Apellidos</td><td colspan="2" style="text-align:left;"><input type="text" name="apellidos" size="60" maxlength="60" value="' . $_SESSION['pers']['apellidos'] . '" /></td></tr>
		<tr><td>Puesto</td><td colspan="2" style="text-align:left;"><input type="text" name="puesto" size="60" maxlength="120" value="' . $_SESSION['pers']['puesto'] . '" /></td></tr>
		<tr><td>E-mail laboral</td><td colspan="2" style="text-align:left;"><input type="text" name="email" size="60" maxlength="120" value="' . $_SESSION['pers']['email'] . '" /></td></tr>
		<tr><td>Teléfono laboral</td><td colspan="2" style="text-align:left;"><input type="text" name="telefono_laboral" size="60" maxlength="120" value="' . $_SESSION['pers']['telefono_laboral'] . '" /></td></tr>'."\n";
	echo '		<tr><td>Código de Puesto</td><td style="text-align:left;" colspan="2">
			<select name="codigo" size="1">
				<option value=""'; if ($_SESSION['pers']['codigo']=='') {echo ' selected="1"';} echo '>Seleccione...</option>
				<option value="10"'; if ($_SESSION['pers']['codigo']==10) {echo ' selected="1"';} echo '>' . $lang['GERENTE'] . '</option>
				<option value="12"'; if ($_SESSION['pers']['codigo']==12) {echo ' selected="1"';} echo '>' . $lang['ASISTENTE'] . '</option>
				<option value="15"'; if ($_SESSION['pers']['codigo']==15) {echo ' selected="1"';} echo '>' . $lang['JEFE DE TALLER'] . '</option>
				<option value="20"'; if ($_SESSION['pers']['codigo']==20) {echo ' selected="1"';} echo '>' . $lang['VALUADOR'] . '</option>
				<option value="30"'; if ($_SESSION['pers']['codigo']==30) {echo ' selected="1"';} echo '>' . $lang['ASESOR'] . '</option>
				<option value="40"'; if ($_SESSION['pers']['codigo']==40) {echo ' selected="1"';} echo '>' . $lang['JEFE DE AREA'] . '</option>
				<option value="50"'; if ($_SESSION['pers']['codigo']==50) {echo ' selected="1"';} echo '>' . $lang['ALMACEN'] . '</option>
				<option value="60"'; if ($_SESSION['pers']['codigo']==60) {echo ' selected="1"';} echo '>' . $lang['OPERADOR'] . '</option>
				<option value="70"'; if ($_SESSION['pers']['codigo']==70) {echo ' selected="1"';} echo '>' . $lang['AUXILIAR'] . '</option>
				<option value="75"'; if ($_SESSION['pers']['codigo']==75) {echo ' selected="1"';} echo '>' . $lang['VIGILANCIA'] . '</option>
				<option value="80"'; if ($_SESSION['pers']['codigo']==80) {echo ' selected="1"';} echo '>' . $lang['CALIDAD'] . '</option>
				<option value="90"'; if ($_SESSION['pers']['codigo']==90) {echo ' selected="1"';} echo '>' . $lang['COBRANZA'] . '</option>
				<option value="100"'; if ($_SESSION['pers']['codigo']==100) {echo ' selected="1"';} echo '>' . $lang['PAGOS'] . '</option>
				<option value="2000"'; if ($_SESSION['pers']['codigo']==2000) {echo ' selected="1"';} echo '>' . $lang['ASEGURADORA'] . '</option>
			</select></td></tr>'."\n";
	
	echo '		<tr><td>Roles adicionales para este usuario</td><td style="text-align:left;">';
	echo '		<input type="checkbox" name="rol02" value="1"'; if($_SESSION['pers']['rol02']==1) { echo ' checked="checked"'; } echo ' />' . $lang['GERENTE'] . '<br>'."\n";	
	echo '		<input type="checkbox" name="rol03" value="1"'; if($_SESSION['pers']['rol03']==1) { echo ' checked="checked"'; } echo ' />' . $lang['ASISTENTE'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol04" value="1"'; if($_SESSION['pers']['rol04']==1) { echo ' checked="checked"'; } echo ' />' . $lang['JEFE DE TALLER'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol05" value="1"'; if($_SESSION['pers']['rol05']==1) { echo ' checked="checked"'; } echo ' />' . $lang['VALUADOR'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol06" value="1"'; if($_SESSION['pers']['rol06']==1) { echo ' checked="checked"'; } echo ' />' . $lang['ASESOR'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol07" value="1"'; if($_SESSION['pers']['rol07']==1) { echo ' checked="checked"'; } echo ' />' . $lang['JEFE DE AREA'] . "\n";
	echo '</td><td style="text-align:left;">'."\n";
	echo '		<input type="checkbox" name="rol08" value="1"'; if($_SESSION['pers']['rol08']==1) { echo ' checked="checked"'; } echo ' />' . $lang['ALMACEN'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol11" value="1"'; if($_SESSION['pers']['rol11']==1) { echo ' checked="checked"'; } echo ' />' . $lang['CALIDAD'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol12" value="1"'; if($_SESSION['pers']['rol12']==1) { echo ' checked="checked"'; } echo ' />' . $lang['COBRANZA'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol13" value="1"'; if($_SESSION['pers']['rol13']==1) { echo ' checked="checked"'; } echo ' />' . $lang['PAGOS'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol09" value="1"'; if($_SESSION['pers']['rol09']==1) { echo ' checked="checked"'; } echo ' />' . $lang['OPERADOR'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol10" value="1"'; if($_SESSION['pers']['rol10']==1) { echo ' checked="checked"'; } echo ' />' . $lang['AUXILIAR'] . '<br>'."\n";
	echo '		<input type="checkbox" name="rol15" value="1"'; if($_SESSION['pers']['rol15']==1) { echo ' checked="checked"'; } echo ' />' . $lang['VIGILANCIA'] . '<br>'."\n";

	echo '		<tr><td>Áreas activas para<br>' . $lang['JEFE DE AREA'] . ' y ' . $lang['OPERADOR'] . '</td><td style="text-align:left;" colspan="2">'."\n";
	
	
	$uarea = explode('|', $_SESSION['pers']['areas']);
	for($i=1;$i<=$num_areas_servicio;$i++){
		echo '		<input type="checkbox" name="area['.$i.']" value="'.$i.'"';
		foreach($uarea as $k) {
			if($k == $i) {
				echo ' checked="checked"';
			}
		} 
		echo ' />' . constant('NOMBRE_AREA_'.$i) . '&nbsp;&nbsp;'."\n";	
	}

	echo '</td></tr>'."\n";

	if ($accion==="modificar") {
		echo '<tr><td colspan="3" style="text-align: center;"><a href="usuarios.php?accion=permisos&amp;userautoshop=' . $_SESSION['pers']['usuario'] . '&amp;nombre=' . $_SESSION['pers']['nombre'] . ' ' . $_SESSION['pers']['apellidos'] . '&amp;rolnav=rol02"><button type="button" class="btn btn-primary" style="margin-bottom: 20px;">Detalle de Permisos</button></a></td></tr>';
		echo '		<tr>
			<td></td>
			<td style="text-align:left;" colspan="2">
				
			</td></td></tr>'."\n";
		echo '		<tr><td>Usuario Activo</td><td style="text-align:left;" colspan="2"><input type="checkbox" name="activo"'; if ($_SESSION['pers']['activo']==1) {echo ' checked="checked"';} echo ' /></td></tr>'."\n";
		echo '		<tr><td>Usuario Conectado</td><td style="text-align:left;" colspan="2"><input type="checkbox" name="ensesion"'; if ($_SESSION['pers']['ensesion']==1) {echo ' checked="checked"';} echo ' /></td></tr>'."\n";
		echo '		<tr><td>Nueva Clave</td><td style="text-align:left;" colspan="2"><input type="password" name="clavesu01"/><input type="hidden" name="usuario" size="60" maxlength="15" value="' . $usuario . '"/></td></tr>'."\n";
	}

	echo '		<tr class="cabeza_tabla"><td colspan="3">Datos Particulares de Usuario</td></tr>
		<tr><td>Calle y número de Domicilio</td><td colspan="2" style="text-align:left;"><input type="text" name="calle_numero" size="60" maxlength="40" value="' . $_SESSION['pers']['calle_numero'] . '" /></td></tr>
		<tr><td>Colonia</td><td colspan="2" style="text-align:left;"><input type="text" name="colonia" size="60" maxlength="60" value="' . $_SESSION['pers']['colonia'] . '" /></td></tr>
		<tr><td>Delegación o<br>Municipio</td><td colspan="2" style="text-align:left;"><input type="text" name="municipio" size="60" maxlength="60" value="' . $_SESSION['pers']['municipio'] . '" /></td></tr>
		<tr><td>Estado</td><td colspan="2" style="text-align:left;"><input type="text" name="estado" size="60" maxlength="60" value="' . $_SESSION['pers']['estado'] . '" /></td></tr>
		<tr><td>Teléfono</td><td colspan="2" style="text-align:left;"><input type="text" name="telefono" size="60" maxlength="40" value="' . $_SESSION['pers']['telefono'] . '" /></td></tr>
		<tr><td>Celular</td><td colspan="2" style="text-align:left;"><input type="text" name="movil" size="60" maxlength="40" value="' . $_SESSION['pers']['movil'] . '" /></td></tr>
		<tr><td>E-mail personal</td><td colspan="2" style="text-align:left;"><input type="text" name="email_personal" size="60" maxlength="120" value="' . $_SESSION['pers']['email_personal'] . '" /></td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="3">Datos fiscales</td></tr>
		<tr><td>RFC</td><td colspan="2" style="text-align:left;"><input type="text" name="rfc" size="60" maxlength="15" value="' . $_SESSION['pers']['rfc'] . '"/></td></tr>
		<tr><td>Contrato</td><td colspan="2" style="text-align:left;"><input type="text" name="contrato" size="60" maxlength="15" value="' . $_SESSION['pers']['contrato'] . '"/></td></tr>
		<tr><td>Comisión</td><td colspan="2" style="text-align:left;"><input type="text" name="comision" size="60" maxlength="15" value="' . $_SESSION['pers']['comision'] . '"/></td></tr>';
	echo '		<tr><td colspan="3" style="text-align:left;"><input type="submit" class="btn btn-success btn-md" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
	</table>';
	echo '	</form>';	*/
	unset($_SESSION['pers']);
}

elseif (($accion==='insertar') || ($accion==='actualizar')) {
	
	$funnum = 1135005;
	include('idiomas/' . $idioma . '/usuarios.php');
	//echo 'Estamos en la sección inserta.<br>';
	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}
	
	$retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == 1) {
		$mensaje = '';
		unset($_SESSION['pers']);
		$_SESSION['pers'] = array();
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}
	
	
	if(isset($activo)) { $activo = 1;}
	if($codigo == 1) {$rol01 = 1; }
	if($codigo == 10) {$rol02 = 1; }
	if($codigo == 12) {$rol03 = 1; }
	if($codigo == 15) {$rol04 = 1; }
	if($codigo == 20) {$rol05 = 1; }
	if($codigo == 30) {$rol06 = 1; }
	if($codigo == 40) {$rol07 = 1; }
	if($codigo == 50) {$rol08 = 1; }
	if($codigo == 60) {$rol09 = 1; }
	if($codigo == 70) {$rol10 = 1; }
	if($codigo == 75) {$rol15 = 1; }
	if($codigo == 80) {$rol11 = 1; }
	if($codigo == 90) {$rol12 = 1; }
	if($codigo == 100) {$rol13 = 1; }
	if($codigo == 2000) {$rol14 = 1; }

	$cuenta = 0;
	foreach($area as $k => $v) {
		if($cuenta > 0) { $areas .= '|'; }
		$areas .= $v;
		$cuenta++;
	}
	$_SESSION['pers']['areas'] = $areas;
	$_SESSION['pers']['rol01'] = $rol01;
	$_SESSION['pers']['rol02'] = $rol02;
	$_SESSION['pers']['rol03'] = $rol03;
	$_SESSION['pers']['rol04'] = $rol04;
	$_SESSION['pers']['rol05'] = $rol05;
	$_SESSION['pers']['rol06'] = $rol06;
	$_SESSION['pers']['rol07'] = $rol07;
	$_SESSION['pers']['rol08'] = $rol08;
	$_SESSION['pers']['rol09'] = $rol09;
	$_SESSION['pers']['rol10'] = $rol10;
	$_SESSION['pers']['rol11'] = $rol11;
	$_SESSION['pers']['rol12'] = $rol12;
	$_SESSION['pers']['rol13'] = $rol13;
	$_SESSION['pers']['rol14'] = $rol14;
	$_SESSION['pers']['rol15'] = $rol15;
	//$_SESSION['pers']['rol016'] = $rol16;
	//$_SESSION['pers']['rol017'] = $rol17;
	$_SESSION['pers']['activo'] = $activo;
	$_SESSION['pers']['ensesion'] = $ensesion;
	$nombre = preparar_entrada_bd($nombre); $_SESSION['pers']['nombre'] = $nombre;
	$apellidos = preparar_entrada_bd($apellidos); $_SESSION['pers']['apellidos'] = $apellidos;
	$puesto = preparar_entrada_bd($puesto); $_SESSION['pers']['puesto'] = $puesto;
	$codigo = preparar_entrada_bd($codigo); $_SESSION['pers']['codigo'] = $codigo;
	$aseg = preparar_entrada_bd($aseg); $_SESSION['pers']['aseg'] = $aseg;
	$prov = preparar_entrada_bd($prov); $_SESSION['pers']['prov'] = $prov;
	$calle_numero = preparar_entrada_bd($calle_numero); $_SESSION['pers']['calle_numero'] = $calle_numero;
	$municipio = preparar_entrada_bd($municipio); $_SESSION['pers']['municipio'] = $municipio;
	$colonia = preparar_entrada_bd($colonia); $_SESSION['pers']['colonia'] = $colonia;
	$estado = preparar_entrada_bd($estado); $_SESSION['pers']['estado'] = $estado;
	$telefono = limpiarNumero($telefono); $_SESSION['pers']['telefono'] = $telefono;
	$movil = limpiarNumero($movil); $_SESSION['pers']['movil'] = $movil;
	$email = preparar_entrada_bd($email); $_SESSION['pers']['email'] = $email;
	$rfc = preparar_entrada_bd($rfc); $_SESSION['pers']['rfc'] = $rfc;
	$contrato = preparar_entrada_bd($contrato); $_SESSION['pers']['contrato'] = $contrato;
	$comision = preparar_entrada_bd($comision); $_SESSION['pers']['comision'] = $comision;
	$email_personal = preparar_entrada_bd($email_personal); $_SESSION['pers']['email_personal'] = $email_personal;
	$telefono_laboral = preparar_entrada_bd($telefono_laboral);
	$error = 'no';
	$mensaje= '';
	//echo '<br><br>====== ' . $error . ' <<>> ' . $mensaje . ' ======<br><br>';
	//echo strlen($telefono1);

	if (strlen($nombre) < 3) {$error = 'si'; $mensaje .='El nombre es muy corto: ' . $nombre . '<br>';}
	if (strlen($apellidos) < 3) {$error = 'si'; $mensaje .='El apellido es muy corto: ' . $apellidos . '<br>';}
	if ($codigo < 10) {$error = 'si'; $mensaje .='Por favor seleccione el código de puesto para el usuario: ' . $nombre . ' ' . $apellidos . '<br>';}
//	if (strlen($email) < 7) {$error = 'si'; $mensaje .='La dirección de la cuenta de correo es muy corta: ' . $email . '<br>';}
//	if (strlen($telefono) < 10) {$error = 'si'; $mensaje .='El número debe tener lada y número local: ' . $telefono . '<br>';}
//	if (strlen($rfc) != 13) {$error = 'si'; $mensaje .='El RFC es de 13 posiciones para personas.<br>';}
//	if (strlen($colonia) < 3) {$error = 'si'; $mensaje .='La colonia es muy corta: ' . $colonia . '<br>';}
//	if (strlen($municipio) < 3) {$error = 'si'; $mensaje .='El municipio o delegación es muy corto: ' . $municipio . '<br>';} 

	//	echo '<br><br>====== ' . $error . ' ======<br><br>';

	if ($error === 'no') {
		if($accion === 'actualizar'){
		//SE CONSULTAN LOS ROLES QUE TIENE ACTIVADOS EL USUARIO
		$rolesad = "SELECT rol01, rol02, rol03, rol04, rol05, rol06, rol07, rol08, rol09, rol10, rol11, rol12, rol13, rol14, rol15, rol16, rol17 FROM " . $dbpfx . "usuarios WHERE usuario = $usuario";
		$matr3 = mysql_query($rolesad) or die("ERROR: Fallo selección de valores comunes! " . $rolesad);
		$roles_activos = mysql_fetch_assoc($matr3);
		}

		$sql_data_array = array(
			'nombre' => $nombre,
			'apellidos' => $apellidos,
			'puesto' => $puesto,
			'codigo' => $codigo,
			//'aseg' => $aseg,
			//'prov' => $prov,
			'areas' => $areas,
			'calle_numero' => $calle_numero,
			'colonia' => $colonia,
			'municipio' => $municipio,
			'estado' => $estado,
			'telefono' => $telefono,
			'telefono_laboral' => $telefono_laboral,
			'movil' => $movil,
			'email' => $email,
			'email_personal' => $email_personal,
			'rfc' => $rfc,
			'contrato' => $contrato,
			'rol01' => $rol01,
			'rol02' => $rol02,
			'rol03' => $rol03,
			'rol04' => $rol04,
			'rol05' => $rol05,
			'rol06' => $rol06,
			'rol07' => $rol07,
			'rol08' => $rol08,
			'rol09' => $rol09,
			'rol10' => $rol10,
			'rol11' => $rol11,
			'rol12' => $rol12,
			'rol13' => $rol13,
			'rol14' => $rol14,
			'rol15' => $rol15,
			/*'rol16' => $rol16,
			'rol17' => $rol17,*/
			'comision' => $comision);


		if ($accion==='insertar') {
			$parametros='';
			$str = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz1234567890";
			$clave = "";
			for($i=0;$i<8;$i++) {$clave .= substr($str,rand(0,59),1);}
			$nueva_clave = md5($clave);
			$sql_data_array[clave] = $nueva_clave; 
			$sql_data_array[fecha_password] = date('Y-m-d H:i:s');
			$pregunta = mysql_query( "SHOW TABLE STATUS LIKE '" . $dbpfx . "usuarios'" );
			$res = mysql_fetch_assoc($pregunta);
			$usuario = $res['Auto_increment'] + $basenumusuarios;
			$sql_data_array['usuario'] = $usuario;
		} else {
			if ($clavesu01 != '') {$nueva_clave = md5($clavesu01); $sql_data_array['clave'] = $nueva_clave;}
			$sql_data_array['activo'] = $activo;
			$sql_data_array['ensesion'] = $ensesion;
			$parametros = 'usuario = ' . $usuario;						
		}
		ejecutar_db($dbpfx . 'usuarios', $sql_data_array, $accion, $parametros);
		bitacora('0', 'Se acaba de ' . $accion . ' el usuario ' . $usuario, $dbpfx);




		if($accion == 'actualizar'){

			foreach ($roles as $key => $value) {

				//echo $key . ' = ' .$$key.'<br>';
				//echo '<b>' . $key  . '</b> = "' . $$key .'" <br><hr>';

				//echo '$roles_activos[$key] '. $roles_activos[$key] . '<br>';

				if(($$key == 0 || $$key == '') && ($roles_activos[$key] == 1)){
					
					//echo '<h2> ' . $key . ' ' . $roles_activos[$key] .' </h2><br>';
						
					mysql_close();
					mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
					mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
					$preg1 = "SELECT fun_num, fun_descripcion, fun_padre FROM funciones WHERE $key = 1";
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de valores comunes! " . $preg1);
					while ($res = mysql_fetch_array($matr1)) {
						
						$funex = $res['fun_num'];
						mysql_close();
						mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
						mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);
						$preg2 = "SELECT num_funcion, activo, acceso_id FROM " . $dbpfx . "usr_permisos WHERE usuario = $usuario AND num_funcion = $funex";
						$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de valores comunes! " . $preg2);
						while ($res2 = mysql_fetch_array($matr2)) {
							if($res2['activo'] == 1){
								//echo '===> ' . $res2['num_funcion']. ' Desactivado<br><hr>';
								$prametros = 'acceso_id = ' . $res2['acceso_id'];
								$sql_data_array = array(
									'activo' => 0,
									'fecha_de_cambio' => date('Y-m-d H:i:s'),
									'administrador' => $_SESSION['usuario']);
								ejecutar_db($dbpfx . 'usr_permisos', $sql_data_array, 'actualizar', $prametros);

							}
						}
					}

					

				}	
			}

			//VOLVEMOS A LEER LOS PERMISOS
			foreach ($roles as $key => $value) {

				if($$key == 1){

					//echo '<h2>Rol ' . $key . ' Activo </h2><br>';
					//SE LLAMAN A LOS PERMISOS DE LOS ROLES ACTIVOS
					mysql_close();
					mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
					mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
					$preg1 = "SELECT fun_num, fun_descripcion, fun_padre FROM funciones WHERE $key = 1";
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de valores comunes! " . $preg1);
					while ($res = mysql_fetch_array($matr1)) {

						//echo '==> Permiso: ' . $res['fun_num'] . '<br>';

						$funex = $res['fun_num'];
						mysql_close();
						mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
						mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);
						//VERIFICAMOS SI YA SE CREO EL PERMISO PARA EL USUARIO
						$preg2 = "SELECT num_funcion, activo, acceso_id FROM " . $dbpfx . "usr_permisos WHERE usuario = $usuario AND num_funcion = $funex LIMIT 1";
						//echo $preg2.'<br>';
						$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de valores comunes! " . $preg2);
						
						$permiso_encontrado = mysql_num_rows($matr2);
						//SI EXISTE PERO ESTA DESACTIVADO, SE CAMBIA EL ESTADO

						if($permiso_encontrado == 1){
							$res2 = mysql_fetch_assoc($matr2);
							//if($res2['activo'] == 1 ){
								
							$idpermiso = $res2['acceso_id'];
							$prametros = 'acceso_id = ' . $idpermiso;
									$sql_data_array = array(
										'activo' => 1,
										'fecha_de_cambio' => date('Y-m-d H:i:s'),
										'administrador' => $_SESSION['usuario']);
									ejecutar_db($dbpfx . 'usr_permisos', $sql_data_array, 'actualizar', $prametros);
								//echo 'existe '. $res2['num_funcion'] . ' y se activo<br><hr>';
							}
						//SI NO EXISTE SE CREA
						else{
							$prametros = 'usuario = ' . $usuario;
							$sql_data_array = array(
								'usuario' => $usuario,
								'num_funcion' => $res['fun_num'],
								'num_padre' => $res['fun_padre'],
								'descripcion' => $res['fun_descripcion'],
								'activo' => 1,
								'fecha_de_cambio' => date('Y-m-d H:i:s'),
								'administrador' => $_SESSION['usuario']);
							ejecutar_db($dbpfx . 'usr_permisos', $sql_data_array, 'insertar');
							//echo 'se creo permiso: '.$res['fun_num']. '<br><hr>';
						}


						
						

					}


				}
				
						
			}
		}

		if($accion==='insertar'){

					//SE INSERTAN PERMISOS AL USUARIO NUEVO X CODIGO SeLECCIONADO

					$rolesad = "SELECT rol01, rol02, rol03, rol04, rol05, rol06, rol07, rol08, rol09, rol10, rol11, rol12, rol13, rol14, rol15, rol16, rol17 FROM " . $dbpfx . "usuarios WHERE usuario = $usuario";
					$matr3 = mysql_query($rolesad) or die("ERROR: Fallo selección de valores comunes! " . $rolesad);
					$roles_activos = mysql_fetch_assoc($matr3);
						
							
							foreach ($roles as $key => $value) {
								if($roles_activos[$key] == 1){
									
									mysql_close();
									mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
									mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
									//SE LLAMAN A LOS PERMISOS DE LOS ROLES ACTIVOS
									$preg1 = "SELECT fun_num, fun_descripcion, fun_padre FROM funciones WHERE $key = 1";
									$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de valores comunes! " . $preg1);
									
									while ($res = mysql_fetch_array($matr1)) {

										$funex = $res['fun_num'];
										mysql_close();
										mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
										mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);
										//VERIFICAMOS SI YA SE CREO EL PERMISO PARA EL USUARIO
										$preg2 = "SELECT num_funcion FROM " . $dbpfx . "usr_permisos WHERE usuario = $usuario AND num_funcion = $funex";
										$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de valores comunes! " . $preg1);
									    $res2 = mysql_fetch_assoc($matr2);
										//SI YA EXISTE NO SE HACE NADA
										if($res2['num_funcion'] == $res['fun_num']){
									    	//echo 'existe '. $res2['num_funcion'] . '<br>';
									    }
									    //SI NO EXISTE SE CREA
									    else {
											$prametros = 'usuario = ' . $usuario;
											$sql_data_array = array(
												'usuario' => $usuario,
												'num_funcion' => $res['fun_num'],
												'num_padre' => $res['fun_padre'],
												'descripcion' => $res['fun_descripcion'],
												'activo' => 1,
												'fecha_de_cambio' => date('Y-m-d H:i:s'),
												'administrador' => $_SESSION['usuario']);
											ejecutar_db($dbpfx . 'usr_permisos', $sql_data_array, 'insertar');
										}
									}
								}
							}
		}	
		



			unset($_SESSION['pers']);
			if ($accion==='insertar') {
				$mensaje = 'usuarios.php?mensaje=El nuevo usuario ' . $usuario . ' tiene la clave: ' . $clave;
				redirigir($mensaje);
			} else {
				redirigir('usuarios.php?mensaje=Datos de Usuario y permisos actualizados.');
			}
	} else {
			$_SESSION['pers']['mensaje'] = $mensaje;
			if ($accion==='insertar') {
				redirigir('usuarios.php?accion=crear&mensaje=' . $mensaje);
		 	} else {
		 		redirigir('usuarios.php?accion=modificar&usuario=' . $usuario . '&mensaje=' . $mensaje);
		 	}
		//echo $mensaje;
		//print_r ($_SESSION['pers']);
		}
}

elseif ($accion==='terminar') {
	
	$funnum = 1135015;
	
	$parme = "usuario = '" . $usr['usuario'] ."'";
	$sqdat = ['ensesion' => '0'];
	ejecutar_db($dbpfx . 'usuarios', $sqdat, 'actualizar', $parme);
	unset($sqdat);

	unlink ("../tmp/access-" . session_id());
	session_unset();
	redirigir('usuarios.php');
}

elseif ($accion==="consultar") {
	
	$funnum = 1135020;

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}
	
	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	if ($_SESSION['codigo'] < 60) {
		$mensaje = '';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}

	$nombre=preparar_entrada_bd($nombre);
	$apellidos=preparar_entrada_bd($apellidos);
	$error = 'si'; $num_cols = 0;
	$mensaje= 'Se necesita al menos un dato para buscar.<br>';
	if (($usuario!='') || ($nombre!='') || ($apellidos!='')) {
		$error = 'no'; $mensaje ='No se encontró ningún usuario con los datos proporcionados';
		$pregunta = "SELECT * FROM " . $dbpfx . "usuarios WHERE ";
		if ($usuario) {$pregunta .= "usuario = '$usuario' ";}
		if (($usuario) && ($nombre)) {$pregunta .= "AND nombre LIKE '%" . $nombre . "%' ";}
			elseif ($nombre) {$pregunta .= "nombre LIKE '%" . $nombre . "%' ";}
		if (($nombre) && ($apellidos)) {$pregunta .= "AND apellidos LIKE '%" . $apellidos . "%' ";} 
			elseif ($apellidos) {$pregunta .= "apellidos LIKE '%" . $apellidos . "%' ";}
   	$matriz = mysql_query($pregunta) or die($pregunta);
   	$num_cols = mysql_num_rows($matriz);
   }
   if ($num_cols > 0) {
   	$mensaje ='';
		echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
		while ($pers = mysql_fetch_array($matriz)) {
		echo '		<tr class="cabeza_tabla" style="text-align: left;"><td colspan="2">INFORMACIÓN DE USUARIO: ' . $pers['nombre'] . ' ' . $pers['apellidos'] . ' </td></tr>
		<tr><td><hr></td><td class="izqI"><STRONG>INFORMACIÓN LABORAL</STRONG></td></tr>
		<tr><td><STRONG>Número de Usuario:</STRONG></td><td class="izqI">' . $pers['usuario'] . '</td></tr>
		<tr><td><STRONG>Puesto:</STRONG></td><td class="izqI">' . $pers['puesto'] . '</td></tr>
		<tr><td><STRONG>Código de Puesto:</STRONG></td><td class="izqI">' . $pers['codigo'] . '</td></tr>
		<tr><td><STRONG>Activo:</STRONG></td><td class="izqI">' . $pers['activo'] . '</td></tr>
		<tr><td><STRONG>Carga programada:</STRONG></td><td class="izqI">' . $pers['horas_programadas'] . '</td></tr>
		<tr><td><STRONG>E-mail laboral:</STRONG></td><td class="izqI">' . $pers['email'] . '</td></tr>
		<tr><td><STRONG>Teléfono laboral:</STRONG></td><td class="izqI">' . $pers['telefono_laboral'] . '</td></tr>
		<tr><td><hr></td><td class="izqI"><STRONG>INFORMACIÓN PERSONAL</STRONG></td></tr>
		<tr><td><STRONG>Calle y número de Domicilio:</STRONG></td><td class="izqI">' . $pers['calle_numero'] . '</td></tr>
		<tr><td><STRONG>Colonia:</STRONG></td><td class="izqI">' . $pers['colonia'] . '</td></tr>
		<tr><td><STRONG>Delegación o Municipio:</STRONG></td><td class="izqI">' . $pers['municipio'] . '</td></tr>
		<tr><td><STRONG>Estado:</STRONG></td><td class="izqI">' . $pers['estado'] . '</td></tr>
		<tr><td><STRONG>Teléfono:</STRONG></td><td class="izqI">' . $pers['telefono'] . '</td></tr>
		<tr><td><STRONG>Celular:</STRONG></td><td class="izqI">' . $pers['movil'] . '</td></tr>
		<tr><td><STRONG>E-mail personal:</STRONG></td><td class="izqI">' . $pers['email_laboral'] . '</td></tr>
		<tr><td><hr></td><td class="izqI"><STRONG>DATOS FISCALES</STRONG></td></tr>
		<tr><td><STRONG>RFC:</STRONG></td><td class="izqI">' . $pers['rfc'] . '</td></tr>
		<tr><td><STRONG>Contrato:</STRONG></td><td class="izqI">' . $pers['contrato'] . '</td></tr>
		<tr><td><STRONG>Comisión:</STRONG></td><td class="izqI">' . $pers['comision'] . '</td></tr>
		<tr><td><STRONG>Fecha de ingreso:</STRONG></td><td class="izqI">' . $pers['fecha_alta'] . '</td></tr>
		<tr><td><STRONG>Fecha de baja:</STRONG></td><td>' . $pers['fecha_baja'] . '</td></tr>'."\n";
		}	
		echo '	</table>'."\n";
	} 
	echo $mensaje;
}

elseif ($accion==="clave") {
	
	$funnum = 1135025;

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}
	
	if($clave1!=$clave2) {
		redirigir('usuarios.php?mensaje=La nueva clave y su repetición no coinciden - Intenta de nuevo');
	}
	if($clave === $clave1) {
		redirigir('usuarios.php?mensaje=La nueva clave y la clave actual no pueden ser iguales - Intenta de nuevo');
	}
	if(strlen($clave1) < 6) {
		$error_clave = "La clave debe tener al menos 6 caracteres";
		redirigir('usuarios.php?mensaje=La clave debe tener al menos 6 caracteres - Intenta de nuevo');
	}
	if (!preg_match('`[a-z]`',$clave1)) {
		redirigir('usuarios.php?mensaje=La clave debe tener al menos una letra minúscula - Intenta de nuevo');
	}
	if (!preg_match('`[A-Z]`',$clave1)) {
		redirigir('usuarios.php?mensaje=La clave debe tener al menos una letra mayúscula - Intenta de nuevo');
	}
	if (!preg_match('`[0-9]`',$clave1)) {
		redirigir('usuarios.php?mensaje=La clave debe tener al menos un número - Intenta de nuevo');
	}
	
	$pregunta = "SELECT usuario, clave FROM " . $dbpfx . "usuarios WHERE usuario = '$usuario'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
  	$pers = mysql_fetch_array($matriz);
	$num_cols = mysql_num_rows($matriz);
	if($num_cols > 0) {
		$verificar = md5($clave);
		//$verificar = $clave;
		$nueva_clave = md5($clave1);
		if ($verificar==$pers['clave']) {
			$sql_data_array = array(
				'clave' => $nueva_clave,
				'fecha_password' => date('Y-m-d H:i:s')
			);
			$parametros = 'usuario = ' . $usuario;
			ejecutar_db($dbpfx . 'usuarios', $sql_data_array, 'actualizar', $parametros);
			bitacora('0', 'El usuario ' . $usuario . ' acaba de cambiar su clave de acceso.', $dbpfx);
			session_unset();
			redirigir('usuarios.php?mensaje=Clave actualizada - Ingresa de nuevo');
		}
		redirigir('usuarios.php?mensaje=Clave incorrecta - Intenta de nuevo');
	}
	redirigir('usuarios.php?mensaje=Usuario incorrecto - Intenta de nuevo');
}

elseif ($accion==="alertas") {
	
	$funnum = 1135030;

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}
	
	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	if ($_SESSION['codigo'] <= 15) {
		$mensaje = '';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}

	$pregunta = "SELECT * FROM " . $dbpfx . "alertas ORDER BY al_categoria, al_sort";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	echo '	<br>
	<form action="usuarios.php?accion=ajustar" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="3" border="0">
		<tr class="cabeza_tabla"><td colspan="4">Tiempos de alerta <span style="color: #f00;">en HORAS</span> para cada Estatus del Proceso</td></tr>
		<tr><td class="der">Estatus</td><td class="cen">Alerta Preventiva</td><td class="cen">Alerta Crítica</td><td class="cen">Categoría</td></tr>
		';
	$j=0;
	while($alerta = mysql_fetch_array($matriz)) {
		echo '		<tr>
			<td class="der"><input type="hidden" name="al_id[' . $j . ']" value="' . $alerta['al_id'] . '" />' . constant('ORDEN_ESTATUS_' . $alerta['al_estatus']) . '</td>
			<td class="cen"><input type="text" name="preventivo[' . $j . ']" value="' . $alerta['al_preventivo'] . '" size="6" /></td>
			<td class="cen"><input type="text" name="critico[' . $j . ']" value="' . $alerta['al_critico'] . '" size="6" /></td>
			<td class="cen">' . constant('CATEGORIA_DE_REPARACION_' . $alerta['al_categoria']) . '</td>
		</tr>'."\n";
		$j++;
	}
	echo '<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
	</table>';
	echo '</form>';	
}

elseif ($accion==='ajustar') {
	
	$funnum = 1135030;
	
	//	echo 'Estamos en la sección inserta.<br>';

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}	

	if (($_SESSION['rol04']==1) || ($_SESSION['codigo'] <= 15)) {
		$mensaje = 'Continuar';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}
	$error = 'no';
   if (($error === 'no') && (is_array($al_id))) {
		for($i=0;$i<count($al_id);$i++) {
			$sql_data_array = array('al_preventivo' => $preventivo[$i],
				'al_critico' => $critico[$i]);
			$parametros = 'al_id = ' . $al_id[$i];
			ejecutar_db($dbpfx . 'alertas', $sql_data_array, 'actualizar', $parametros);
		}
		redirigir('usuarios.php?mensaje=Tiempo de alertas ajustado');
	} else {
		include('idiomas/' . $idioma . '/personas.php');
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		echo $mensaje;
	}
}

elseif ($accion==='permisos') {

		//permisos y funciones INICIO
		$funnum = 1135005;
		
		if (!isset($_SESSION['usuario'])) {
			redirigir('usuarios.php');
		}	

		$funnum = 1135005;
		$retorno = validaAcceso($funnum, $dbpfx);

		if ($retorno == '1') {
		//	Acceso autorizado
		} else {
			redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
		}
						
		include('idiomas/' . $idioma . '/usuarios.php');
		include('parciales/encabezado.php'); 
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '	<script src="js/jquery-1.9.1.js"></script>
				<script src="js/jquery-1.12.4.js"></script>
				<script type="text/javascript" src="js/jquery.basic.toast.js"></script>';
		echo '	
				<div id="principal">
					<div class="col-md-12">'; //TITULO DEL USUAIO
		echo '
						<div class="content-box-header">
							<div class="panel-title">
								<h3>Permisos Adicionales para: <u>' . $nombre . '</u> Usuario: <u>' . $userautoshop . '</u></h3> 
							</div>
						</div>
					</div>
					
					<div class="row" style="display: flex; width: inherit;">
    					<div class="col-md-6" style="justify-content: center;display: flex;align-items: center;">
							<h4>ROLES ACTIVOS PARA ESTE USUARIO:</h4>
								<u style="list-style-type: none;">';
									$rolesad = "SELECT rol01, rol02, rol03, rol04, rol05, rol06, rol07, rol08, rol09, rol10, rol11, rol12, rol13, rol14, rol15, rol16, rol17 FROM " . $dbpfx . "usuarios WHERE usuario = $userautoshop";
									$matr3 = mysql_query($rolesad) or die("ERROR: Fallo selección de valores comunes! " . $rolesad);
									$roles_activos = mysql_fetch_assoc($matr3);
										foreach ($rolesMenuPersmisos as $key => $value) {
											if($roles_activos[$key] == 1){

												echo '<li>' . $rolesMenuPersmisos[$key] . '</li>';
											}
										}
								echo '	
								</ul>

						</div>
						
						<div class="col-md-6" style="justify-content: center;display: flex;align-items: center;">
							<div id="content-tabla">
								<table style="width: 100%; background-color: #c9c9c9;padding: 30px 20px;border-top: 1px solid #ccc;padding-top: 0px;padding-bottom: 0px;">
									<tbody>
									<tr>
										<td>
											<h4 style="text-align: right;">BUSCAR UN PERMISO</h4>
										</td>
										<td>
											<input class="form_control" type="text" name="busqueda" id="busqueda" onKeyUp="buscar();"/>
										</td>
									</tr>
									<tr>
										<td colspan="2">
												
										</td>
									</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
		
					<div id="resultadoBusqueda" class="col-md-12"></div>
					<div id="lista">
						<div class="col-md-12 ">
							<div class="col-md-12">
								<div id="navegador" style="float: none;">
									<a name="rolnav"></a><br>
									<ul style="padding-bottom: 64px;"><li class="activa">';
										$j = 0;
										$i = 0;
										foreach ($rolesMenuPersmisos as $key => $value) {
											if ($i++ != 0){
												if($key == 'rol01'){}
												//echo $rolnav1;
												echo '<li'."\n"; if($rolnav == $key) { echo ' class="activa"'; }
												echo '><a href="usuarios.php?accion=permisos&userautoshop=' . $userautoshop . '&nombre=' . $nombre . '&rolnav=' . $key . '">' . $rolesMenuPersmisos[$key] . '</a></li>'."\n";
												$j++;
											}
										}
										echo '	
									</ul>
								</div>
							</div>
						</div>';

						//se corren las areas por rol acivo
						if($cont == 1 || $cont2 > 1){
						} 
						else {
							$cont = 0;
							$cont2 = 0;
						}
							$j = 0;		
							$conta = 0;
						foreach ($areas as $key => $value) {
							echo '	
							<div class="col-md-12 ">
								<div class="col-md-12">
									<h2 style="text-align: center;">' . $key . '</h2>
								</div>
							</div>';
								//JQUERY PARA EL FUNCIONAMIENTO DEL BUScADOR 
								?>
								<script>
									$(document).ready(function() {
									   // $("#resultadoBusqueda").html('<p>VACIO</p>');
									});

										function buscar() {
										    var textoBusqueda = $("input#busqueda").val();
										 
										    if (textoBusqueda != "") {
										        $.post("usuarios.php?accion=busca_permiso", {valorBusqueda: textoBusqueda, usuario: <?php echo $userautoshop; ?>}, function(mensaje) {
										            $("#resultadoBusqueda").html(mensaje);
										        }); 
										    } 	else { 
										       $("#resultadoBusqueda").html("<!--JQUERY VACIO-->");
										        };
										};

										$('#busqueda').keyup(function() {
			        						$(this).val().length ? $('#lista').hide(1000) : $('#lista').show(1000);
			   							});
								</script> 
								<?php echo '
							<table style="width: 100%; background-color: #c9c9c9;padding: 30px 20px;border-top: 1px solid #ccc;padding-top: 0px;">
								<tbody>	';	
						  
									$res_encontrados = 0;	
									foreach ($value as $archivo) {
										//echo '$value: ' .$archivo. '<br>';
										mysql_close();
										mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
										mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
										$preg1 = "SELECT fun_num, fun_descripcion, fun_padre, Archivo FROM funciones WHERE $rolnav = 1 AND Archivo = '$archivo' ORDER BY Archivo";
										$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de valores comunes! " . $preg1);
										$res = mysql_num_rows($matr1);
										$res_encontrados = $res_encontrados + $res;
										if($res_encontrados == 0){
											$res_encontrados = $res_encontrados + $res_encontrados;
										} 
										else{
											//echo $res_encontrados;
											while ($res = mysql_fetch_array($matr1)) {

												mysql_close();
												mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
												mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);
												$funBd1 = $res['fun_num'];
												$preg2 = "SELECT usuario, num_funcion, activo FROM " . $dbpfx . "usr_permisos WHERE num_funcion = $funBd1 AND usuario = $userautoshop AND activo = 1";
												$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de valores comunes! " . $preg2);
						
												while ($res2 = mysql_fetch_array($matr2)) {
													$funASE = $res2['num_funcion'];
												} 
									
												if($cont == 0){
													if($cont2%2==0){
														echo '
														<tr style="background-color: #989898">'."\n";
													}
												else {
													echo '
													<tr style="background-color: #b7b7b7">'."\n";
												} 
											}

											echo '
											<td width="50%">
												<label class="container" style="padding-left: 40px;">
													<input type="checkbox"';
														if($funASE == $funBd1) {
														echo 'checked="checked"';
														 }
													echo 'name="' . $res['fun_num'] . '" id="' . $res['fun_num'] . '" style="display: none;">
													<span class="slider round" style="box-shadow: 1px 3px 0px 0px #000;"></span>  '. $res['fun_descripcion'] . '';
													echo '<br><small class="tooltip"><b>Este permiso se Comparte con:</b>
															<div class="right">
		        												<h3>Este Permiso se Comparte con los Roles:</h3>
														        <p><ul>';
									
											$esta_funcion = $res['fun_num'];
											mysql_close(); //OBTENER LOS ROLES QUE COMPARTEN LOS PERMISOS
											mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
											mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
											$roles_comp = "SELECT rol01, rol02, rol03, rol04, rol05, rol06, rol07, rol08, rol09, rol10, rol11, rol12, rol13, rol14, rol15, rol16, rol17 FROM funciones WHERE fun_num = $esta_funcion";
											$matr_roles = mysql_query($roles_comp) or die("ERROR: Fallo selección de valores comunes! " . $roles_comp);
											while ($r_compartidos = mysql_fetch_assoc($matr_roles)) {
												
												foreach ($r_compartidos as $key => $value) {
													if($value == 1){
													echo '<li>' . $rolesMenuPersmisos[$key] . '</li>';
													}
												}
											}
											mysql_close();
											//echo '</i></b></span></small></label>';
											echo		'</ul></p>
													     <i></i>
													    </div>
											</small></label>
										</td>'."\n";
										?>

										<script type="text/javascript">
										    $(document).ready(function(){
										    	var data2;
										    	var data;
										        $(":checkbox#<?php echo $res['fun_num']; ?>").change(function(){
										        	
			            							
										        	if ($(":checkbox#<?php echo $res['fun_num']; ?>:checked").length == 0){ 
										        		$.Toast("<big><b>Se Desactivo Permiso:</b></big><br><?php echo $res['fun_descripcion']; ?><br>", {'duration':8000,'align': 'left','class': 'alert2', 'position': 'bottom'});
										        		data2 = 0;
										        	}
										        		

										        	if ($(":checkbox#<?php echo $res['fun_num']; ?>:checked").length) {
										        		$.Toast("<big><b>Se Activo Permiso:</b></big><br><?php echo $res['fun_descripcion']; ?><br>", {'duration':8000,'class':'success','align':'left','position': 'bottom'});
										        		data2 = 1;
										        	}
										        		
										        		
														
										        	$.ajax({
											            type: 'POST',
											           	data: {box:data2,usuario:<?php echo $userautoshop; ?>,funcion:<?php echo $res['fun_num']; ?>,padre:<?php echo $res['fun_padre']; ?>,descripcion:"<?php echo $res['fun_descripcion']; ?>", admin:"<?php echo $_SESSION['usuario']; ?>"},
											           url: 'usuarios.php?accion=actualiza_permisos_detalle',
											         //success: function(response) {
											          //    alert(response);
											         //  }
										            });
										   		});

										        $(":checkbox#<?php echo $res['fun_num']; ?>").click(function(){
										   		//$('#cambio<?php echo $res['fun_num']; ?>').toggle(1000);
										        });

										   	
										});

										    	
										</script>

										<?php
											$cont++;
											if($cont == 2){
												if($conta == 1){
												echo '</tr>'."\n";
												}
												$cont = 0;
												$cont2++;	
											}
									}
					}
					$conta++;
			}

			
			$cont = 0;
			if($res_encontrados == 0){
				echo '<tr style="background-color: #b7b7b7"><td style="text-align: center;"><label class="container" style="padding-left: 40px;">No se encontraron perimisos en esta Área para el rol ' . $roles[$rolnav]. ' </label></td></tr>';
				echo '';
			}
			echo '</tbody></table>';
		}
		echo '</div></div>';

}
elseif ($accion==='busca_permiso') {
			include('idiomas/' . $idioma . '/usuarios.php');
			mysql_close();
			mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
			mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');

			$consultaBusqueda = $valorBusqueda;
			$caracteres_malos = array("<", ">", "\"", "'", "/", "<", ">", "'", "/");
			$caracteres_buenos = array("& lt;", "& gt;", "& quot;", "& #x27;", "& #x2F;", "& #060;", "& #062;", "& #039;", "& #047;");
			$consultaBusqueda = str_replace($caracteres_malos, $caracteres_buenos, $consultaBusqueda);

			if (isset($consultaBusqueda)) {

				$preg2 = "SELECT fun_num, fun_descripcion, fun_padre FROM funciones WHERE fun_descripcion LIKE '%$consultaBusqueda%'";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de valores comunes! " . $preg2);
				
					echo 'Resultados para <strong>'.$consultaBusqueda.'</strong>';
					echo '<table><tbody>';
					
					$cont2 = 0;
					$cont3 = 0;
					

					while($res2 = mysql_fetch_array($matr2)) {
						$descripcion = $res2['fun_descripcion'];
						mysql_close();
						mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
						mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);
						
						//$usuario = $_POST['usuario'];
						$funBd1 = $res2['fun_num'];
						$preg3 = "SELECT usuario, num_funcion, activo FROM " . $dbpfx . "usr_permisos WHERE num_funcion = $funBd1 AND usuario = $usuario AND activo = 1";
						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de valores comunes! " . $preg3);
						
						while ($res3 = mysql_fetch_array($matr3)) {
							$funASE = $res3['num_funcion'];
						} 


						if($cont2 == 0){

								if($cont3%2==0){

								echo ' 
								<tr style="background-color: #989898;">'."\n";
								
							}
							else{ 
								echo '
								<tr style="background-color: #b7b7b7">'."\n"; 
								}
							}		
					
							echo '<td width="33%"><label class="container" style="padding-left: 40px;">
									<input type="checkbox"';
											if($funASE == $funBd1) {
											echo 'checked="checked"';
											 }
											 
							echo 'name="' . $res2['fun_num'] . '" id="' . $res2['fun_num'] . '" style="display: none;">
										<span class="slider round" style="box-shadow: 1px 3px 0px 0px #000;"></span>  '. $res2['fun_descripcion'] . ''."\n";
							echo '<br><small class="tooltip"><b>Este permiso se Comparte con:</b>
										<div class="right">
		        							<h3>Este Permiso se Comparte con los Roles:</h3>
												<p><ul>';
											$esta_funcion = $res2['fun_num'];
											mysql_close(); //OBTENER LOS ROLES QUE COMPARTEN LOS PERMISOS
											mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
											mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
											$roles_comp = "SELECT rol01, rol02, rol03, rol04, rol05, rol06, rol07, rol08, rol09, rol10, rol11, rol12, rol13, rol14, rol15, rol16, rol17 FROM funciones WHERE fun_num = $esta_funcion";
											$matr_roles = mysql_query($roles_comp) or die("ERROR: Fallo selección de valores comunes! " . $roles_comp);
											while ($r_compartidos = mysql_fetch_assoc($matr_roles)) {
												
												foreach ($r_compartidos as $key => $value) {
													if($value == 1){
													echo '<li>' . $rolesMenuPersmisos[$key] . '</li>';
													}
												}
											}
											mysql_close();

							echo		'</ul></p>
													     <i></i>
													    </div>
											</small></label>
										</td>'."\n";

			?>
						<script type="text/javascript">
						    $(document).ready(function(){
						    	var data2;
						    	var data;
						        $(":checkbox#<?php echo $res2['fun_num']; ?>").change(function(){
										        	if ($(":checkbox#<?php echo $res2['fun_num']; ?>:checked").length == 0){ 
										        		$.Toast("<big><b>Se Desactivo Permiso:</b></big><br><?php echo $res2['fun_descripcion']; ?><br>", {'duration':8000,'align': 'left','class': 'alert', 'position': 'bottom'});
										        		data2 = 0;
										        	}
										        		

										        	if ($(":checkbox#<?php echo $res2['fun_num']; ?>:checked").length) {
										        		$.Toast("<big><b>Se Activo Permiso:</b></big><br><?php echo $res2['fun_descripcion']; ?><br>", {'duration':8000,'class':'success','align':'left','position': 'bottom'});
										        		data2 = 1;
										        	}
										        		
										
						        	$.ajax({
							            type: 'POST',
							           	data: {box:data2,usuario:<?php echo $usuario; ?>,funcion:<?php echo $res2['fun_num']; ?>,padre:<?php echo $res2['fun_padre']; ?>,descripcion:"<?php echo $res2['fun_descripcion']; ?>", admin:"<?php echo $_SESSION['usuario']; ?>"},
							           url: 'usuarios.php?accion=actualiza_permisos_detalle',
							         // success: function(response) {
							          //     alert(response);
							           // }
						            });
						   		});
						});
						</script>
						<?php
									$cont2++;
									$j++;
										
									if($cont2 == 2){
										echo '
										</tr>'."\n";
										$cont2 = 0;
										$cont3++;
									}						


					};//Fin while $resultados

						//}; //Fin else $res2
					echo '</tbody></table>';
			};//Fin isset $consultaBusqueda
}

elseif ($accion==='actualiza_permisos_detalle'){
		
		/*foreach ($_POST as $key => $value) {
		$box = $_POST['box'];
		$funcion = $_POST['funcion'];
		$usuario = $_POST['usuario'];
		$padre = $_POST['padre'];
		$descripcion = $_POST['descripcion'];
		$admin = $_POST['admin'];
			}*/

		if($box == '0'){
			$cambio = 'desactivo';
		}else{
			$cambio = 'activo';
		}
		// Actualiza permiso
		$preg1="SELECT * FROM " . $dbpfx . "usr_permisos WHERE num_funcion = '$funcion' AND usuario = '$usuario'";
		$resultado=mysql_query($preg1) or die (mysql_error());
		if (mysql_num_rows($resultado)>0)
		{
		$preg2 = "UPDATE " . $dbpfx . "usr_permisos SET activo = $box WHERE num_funcion = $funcion AND usuario = '$usuario'";
		$matrtar = mysql_query($preg2) or die("ERROR: Fallo actualización! " . $preg2);
		bitacora('1000000', 'El usuario ' . $admin . ' ' . $cambio . ' el permiso ' . $funcion . ' al usuario '. $usuario, $dbpfx);
		} else {

		$accion == 'insertar';
		$parametros = 'usuario = ' . $usuario;
				$sql_data_array = array(
					'usuario' => $usuario,
					'num_funcion' => $funcion,
					'num_padre' => $padre,
					'descripcion' => $descripcion,
					'activo' => 1,
					'fecha_de_cambio' => date('Y-m-d H:i:s'),
					'administrador' => $admin
				);
				ejecutar_db($dbpfx . 'usr_permisos', $sql_data_array, 'insertar');
				bitacora('1000000', 'El usuario ' . $admin . ' activo el permiso ' . $funcion . ' al usuario '. $usuario, $dbpfx);
				

		}
}

elseif ($accion==='guardapermisos'){

	// ------ Permisos y funciones INICIO
	
	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}

	if (validaAcceso('1135005', $dbpfx) == '1') {
	//Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}

	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	
	// ------ Iniciamos el foreach que nos leera cada uno de los valores del arreglo
	foreach ($fun as $i => $cont) {


	// ------ Consultamos en la base de datos si el usuario esta registrado en la tabla usr_permisos con la función en curso
		$pregunta1= "SELECT * FROM " . $dbpfx . "usr_permisos WHERE usuario = '" . $userautoshop . "' AND num_funcion = '" . $i . "'";
		$matr1= mysql_query($pregunta1) or die ("fallo selección ". $pregunta1); 
		$fila1= mysql_num_rows($matr1);  
		$accion= 'nada';
		// ------ Decidiremos si se actualizara o insertará
		if ($fila1 > '0') {
			$usrfun = mysql_fetch_array($matr1);
			if($usrfun['activo'] == '1' && $cont[0] == '1') {
				$accion= 'nada';
			} elseif($usrfun['activo'] == '0' && $cont[0] != '1') {
				$accion= 'nada';
			} else {
				$accion= 'actualizar';
				$hacer= 'actualizar';
			}
		} elseif($cont[0] == '1') {
			$hacer= 'insertar';
			$accion= 'insertar';
		}
		if($accion == 'insertar' || $accion == 'actualizar' ){
		// ------ Asignamos si se activará la función o se desactivará
		if ($cont == '1') { $activo= 1; } else { $activo= 0; }
		$parametro= "acceso_id='" . $usrfun['acceso_id'] . "'";
		// ------ Guardaremos en un arreglo los datos que serán guardados en usr_permisos
		$sql_data_array = array(
			'usuario' => $userautoshop,
			'num_funcion' => $i,
			'num_padre' => $padre,
			'descripcion' => $cont[1],
			'activo' => $cont[0],
			'fecha_de_cambio' => date('Y-m-d H:i:s'),
			'administrador' => $_SESSION['usuario']
		);
		ejecutar_db($dbpfx . 'usr_permisos', $sql_data_array, $hacer, $parametro);
		}
	}
	redirigir('usuarios.php?accion=permisos&userautoshop=' . $userautoshop . '&nombre='. $_SESSION['pers']['nombre'] . ' ' . $_SESSION['pers']['apellidos'] . '#' .$padre);
}

elseif ($accion==="listar") {
	
	$funnum = 1135020;
	//	echo 'Estamos en la sección listar. Cliente: ' . $cliente_id . ' Vehiculo: ' . $vehiculo_id;

	if (!isset($_SESSION['usuario'])) {
		redirigir('usuarios.php');
	}

	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	if ($_SESSION['codigo'] < 60) {
		$mensaje = '';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Administradores de la aplicación, ingresar Usuario y Clave correcta');
	}
	
	$error = 'no'; $mensaje ='';
	$pregunta = "SELECT usuario, nombre, apellidos, puesto, codigo FROM " . $dbpfx . "usuarios WHERE acceso = '0' AND activo = '1' ORDER BY nombre";
   $matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	$num_cols = mysql_num_rows($matriz);
	if ($num_cols>0) {
		echo '	<table cellspacing="2" cellpadding="2" border="0" class="izquierda">
		<tr class="cabeza_tabla"><td colspan="4" align="left">Lista de Usuarios</td></tr>
		<tr><td>Usuario</td><td>Nombre</td><td>Puesto</td><td>Grupo</td></tr>';
		$j=0;
		while ($usr = mysql_fetch_array($matriz)) {
			echo '		<tr'; if($j==0) { echo ' class="claro" >';} else { echo ' class="obscuro" >';}
			echo '			<td><a href="usuarios.php?accion=modificar&usuario=' . $usr['usuario'] . '">' . $usr['usuario'] . '</a></td>
			<td>' . $usr['nombre'] . ' ' . $usr['apellidos'] . '</td>
			<td>' . $usr['puesto'] . '</td>
			<td>' . $codigos[$usr['codigo']] . '</td>
		</tr>';
			$j++;
			if($j==2) {$j=0;}
		}
		echo '	</table>';
	} else {
		$mensaje ='No se encontraron usuarios con esos datos.</br>';
	}
}

else {

	include('idiomas/' . $idioma . '/usuarios.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">'."\n";
	include('parciales/menu_inicio.php');
	
	echo '		<div id="principal">'."\n";
	echo '			<div class="row">';
	echo '				<div class="col-lg-9 col-md-12 col-sm-12">';
	echo '					<div class="col-lg-4 col-md-3 col-sm-6">';

	$funnum = 1135045;
	
	if ($_SESSION['codigo'] >= 1) {
		if (isset($mensaje)) { echo '			<div class="contener" style="margin-top: 5px;margin-bottom: 5px;margin-right: 5px;margin-left: 5px;"><div class="alert alert-danger" role="alert">' . $mensaje .  '</div></div>'."\n"; }
		if($_SESSION['cambio_pass'] == 1) {
			echo '			
						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
							<h3>Cambiar Clave</h3>
							<p>La clave debe de contener:</p>
							<ul style="margin-left: 20px;">
								<li><STRONG>Un minimo de seis caracteres.
								<li>Una minúscula. 
								<li>Una mayúscula.
								<li>Un número.</STRONG>
							</ul>
							<form action="usuarios.php?accion=clave" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Clave actual: </td><td><input type="password" name="clave" size="10" maxlength="20" /></td></tr>
									<tr><td>Clave nueva: </td><td><input type="password" name="clave1" size="10" maxlength="20" /></td></tr>
									<tr><td>Repetir clave nueva: </td><td><input type="password" name="clave2" size="10" maxlength="20" />
									<input type="hidden" name="usuario" value="' . $_SESSION['usuario'] . '" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md" value="Enviar" /></td></tr>
								</table>
							</form>
						</div>
					'."\n";
		} else {
			echo '			
						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
							<h3>Cambiar Clave</h3>
							<p>La clave debe de contener:</p>
							<ul style="margin-left: 20px;">
								<li><STRONG>Un minimo de seis caracteres.
								<li>Una minúscula. 
								<li>Una mayúscula.
								<li>Un número.</STRONG>
							</ul>
							<form action="usuarios.php?accion=clave" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Clave actual: </td><td><input type="password" name="clave" size="10" maxlength="20" /></td></tr>
									<tr><td>Clave nueva: </td><td><input type="password" name="clave1" size="10" maxlength="20" /></td></tr>
									<tr><td>Repetir clave nueva: </td><td><input type="password" name="clave2" size="10" maxlength="20" />
									<input type="hidden" name="usuario" value="' . $_SESSION['usuario'] . '" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md" value="Enviar" /></td></tr>
								</table>
							</form>
						</div>'."\n";

			$funnum = 1135050;
			// ------ > Validar acceso a cálculo de Destajos		 	$funnum = 1135050;
			$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
			if ($_SESSION['codigo'] <= 12 || ($solovalacc != 1 && ($retorno == '1' || $_SESSION['rol01'] == 1 || $_SESSION['rol02'] == 1 || $_SESSION['rol03'] == 1))) {
				echo '						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
							<h3>Destajos</h3> 
							<form action="destajos.php?accion=gestionar" method="post">
								Por OT especifica: <input type="text" name="orden_ver" size="10" />&nbsp;<input type="submit" class="btn btn-success btn-md" name="usuarios" value="Enviar" />
							</form><br>
							<a href="destajos.php?accion=generar"><img src="idiomas/' . $idioma . '/imagenes/destajos-listar.png" alt="Listado de Destajos por Calcular" title="Listado de Destajos por Calcular" ></a>	
							<a href="comisiones.php?accion=consultar"><img src="idiomas/' . $idioma . '/imagenes/comisiones_h.png" alt="Listado de Comisiones" title="Listado de Comisiones" ></a>
							<a href="comisiones.php?accion=generar"><img src="idiomas/' . $idioma . '/imagenes/comisionesXcalcular_h.png" alt="Generar recibos de Comisiones" title="Generar recibos de Comisiones" ></a>
						</div>'."\n";
				echo '						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
							<h3>Recibos de pago</h3>
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Operador: </td><td>
										<form action="recibosrh.php?accion=listar" method="post">
											<select name="operador" style="width: 150px;">
												<option value="Seleccione">Seleccione</option>'."\n";
				foreach($usu as $n => $v) {
					echo '												<option value="' . $n . '">' . $v['nom'] . ' ' . $v['ape'] . '</option>'."\n";
				}
				echo '											</select>
											<input type="submit" class="btn btn-success btn-md" value="Enviar" />
										</form>
									</td></tr>
									<tr><td>Listado de<br>Pendientes: </td><td>
										<form action="recibosrh.php?accion=listar" method="post">
											<input type="submit" class="btn btn-success btn-md" value="Ver todos" />
										</form>
									</td></tr>
									<tr><td>Recibo: </td><td>
										<form action="recibosrh.php?accion=consultar" method="post">
											<input type="text" name="recibo_id" size="10" />&nbsp;<input type="submit" class="btn btn-success btn-md" value="Enviar" />
										</form>
									</td></tr>
								</table>
						</div></div>'."\n";
			}
			echo '					'."\n";
			$funnum = 1135005;
			$retorno = validaAcceso($funnum, $dbpfx);
			if ($retorno == 1 || $_SESSION['codigo'] <= 15 || ($solovalacc != 1 && ($_SESSION['rol01'] == 1 || $_SESSION['rol02'] == 1 || $_SESSION['rol03'] == 1 || $_SESSION['rol04'] == 1))) {
				echo '					
					<div class="col-lg-4 col-md-3 col-sm-6">
						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
						<table>
						<tr>
						<th width="50%" align="center"><h3>Crear Nuevo Usuario</h3></th>
						<th width="50%" align="center"><h3>Listar <br>Usuarios</h3></th>
						</tr>
						<tr>
						<td width="50%" align="center"><a href="usuarios.php?accion=crear"><img src="idiomas/' . $idioma . '/imagenes/nuevo-usuario.png" alt="Agregar Nuevo usuario" title="Agregar Nuevo usuario"></a></td>
						<td width="50%" align="center"><a href="usuarios.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/consultar.png" alt="Listar usuarios" title="Listar usuarios"></a></td>
						</tr>
					</table>
						</div>	
						<!--
						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
							<h3>Modificar Permisos de Usuario</h3>
							<form action="usuarios.php?accion=permisos" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Número de Usuario: </td><td><input type="text" name="usuario" size="10" maxlength="11" /></td></tr>
								<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md" value="Enviar" /></td></tr>
								</table>
							</form>
						</div>
						-->

						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
							<h3>Consultar Datos de Usuario</h3>
							<form action="usuarios.php?accion=consultar" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Número de Usuario: </td><td><input type="text" name="usuario" size="10" maxlength="11" /></td></tr>
									<tr><td>Nombre: </td><td><input type="text" name="nombre" size="10" maxlength="20" /></td></tr>
									<tr><td>Apellidos: </td><td><input type="text" name="apellidos" size="10" maxlength="30" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md" value="Enviar" /></td></tr>
								</table>
							</form>
						</div>
						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
							<h3>Modificar Datos de Usuario</h3>
							<form action="usuarios.php?accion=modificar" method="post">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td>Número de Usuario: </td><td><input type="text" name="usuario" size="10" maxlength="11" /></td></tr>
									<tr><td colspan="2" style="text-align:left;"><input type="submit" class="btn btn-success btn-md" value="Enviar" /></td></tr>
								</table>
							</form>
						</div></div>
					'."\n";
			}

			echo '					'."\n";
			if (validaAcceso('1135030', $dbpfx) == 1 || $_SESSION['codigo'] <= 15 || ($solovalacc != 1 && ($_SESSION['rol01'] == 1 || $_SESSION['rol02'] == 1 || $_SESSION['rol03'] == 1 || $_SESSION['rol04'] == 1))) {
				echo '	<div class="col-lg-4 col-md-3 col-sm-6">';
				echo '						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
							<h3>Ajustar Tiempos de Alertas</h3>
							<a href="usuarios.php?accion=alertas"><img src="idiomas/' . $idioma . '/imagenes/tiempos.png" alt="Modificar Tiempos de Alertas" title="Modificar Tiempos de Alertas"></a></div>'."\n";
			}

			// ------ Codificación de Acceso a Sitio Web de Capacitación.

			if($_SESSION['codigo'] < '2000' && $_SESSION['codigo'] != 60 && $_SESSION['codigo'] != 70 && $_SESSION['codigo'] != 75) {
				$pregunta = "SELECT clave FROM " . $dbpfx . "usuarios WHERE usuario = '" . $_SESSION['usuario'] . "' AND activo = '1'";
				$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección! " . $pregunta);
  				$usr = mysql_fetch_array($matriz);

				$principio = substr($usr['clave'], 0, '13');
				$medio = substr($usr['clave'], 13, '4');
				$final = substr($usr['clave'], 17);
				$cfx = $final . $medio. $principio;
				$usuver =  md5($dbpfx . $_SESSION['usuario']);
				$principio = substr($usuver, 0, '13');
				$medio = substr($usuver, 13, '4');
				$final = substr($usuver, 17);
				$usuver = $final . $medio. $principio;
				$cfx = $usuver . $cfx;

				/*			
				$tens = strlen($cfx);
				$prin2 = substr($cfx, -13);
				$med2 = substr($cfx, -17, '4');
				$fin2 = substr($cfx, 32, 15);
				$ensamblada = $prin2 . $med2 . $fin2; */

				echo '						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">'."\n";
				//echo '		Clave: ' . $usr['clave'] . '<br>Tamaño: ' . $tens . '<br>Principio:  ' . $principio . '<br>Medio:  ' . $medio . '<br>y Final:  ' . $final . '<br>Cfx:  ' . $cfx . '<br>Princio 2:  ' . $prin2 . '<br>Medio 2:  ' . $med2 . '<br>Filan 2:  ' . $fin2 . '<br>Resamble:  ' . $ensamblada . '<br>'."\n";
				echo '							<h3>Capacitación de Usuarios</h3>
							<form action="https://capacitacion.vaicop.com/capacitacion.php" method="post" target="_blank">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr><td colspan="2" ><img src="idiomas/' . $idioma . '/imagenes/video-tutoriales_h.png" alt="Video Tutoriales" title="Video Tutoriales"></td></tr>
									<tr><td colspan="2" style="text-align:left;">
										<input type="hidden" name="inspfxrk" value="' . $dbpfx . '" />
										<input type="hidden" name="prn3rhy2" value="' . $cfx . '" />
										<input type="submit" class="btn btn-success btn-md" value="Video Tutoriales" />
									</td></tr>
								</table>
							</form>
						</div>'."\n";
			}
// ------ Módulo de boletines --------
			echo '						
						<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
							<h3>Boletín Interno</h3>'."\n";
			
			// ------ Acceso a gestión de boletines ------
			if (validaAcceso('1170100', $dbpfx) == 1) {
				echo '						
							<a href="boletines.php?accion=gestionar"><button type="button" class="btn btn-primary">GESTIONAR</button></a>
							<br>'."\n";
			}
			echo '
							<br>
							<a href="boletines.php?accion=listar"><button type="button" class="btn btn-primary">MIS BOLETINES</button></a>
							</div></div></div></div>'."\n";
			echo '					'."\n";
		}
	} else {
		
		$funnum = 1135060; ?>
	</div>

			<div class="row" style="margin: 10px;">
				
				<div class="col-lg-5 col-md-7 col-sm-7" style="border-radius: 10px;box-shadow: 3px 3px 6px #000;margin: 5px;background-color: white;display: flex;align-items: center;text-align: center;">
					<div class="row" style="margin: 10px;">
						<div class="col-lg-12 col-md-12 col-sm-12">
							<img src="imagenes/logo-ase.png">
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12">
							<h2><b>Bienvenido a Autoshop-Easy</b></h2>
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12">
							<?php 	if (isset($mensaje)) { ?> 
							<div class="contener" style="margin-top: 5px;margin-bottom: 5px;margin-right: 5px;margin-left: 5px;">
								<div class="alert alert-danger" role="alert">
									<?php echo $mensaje;?>
								</div>
							</div>
							<?php 	} else { ?>
								<label class="labellogin" style="color:dodgerblue;"><b>Accede con tu Usuario y Clave</b></label>
							<?php 	} ?>
						</div>
						<div class="col-lg-12 col-md-12 col-sm-12">
							<form action="usuarios.php?accion=ingresar" method="post">
								<label class="labellogin" for="usuario">Usuario:</label><input placeholder="Ingresar Usuario" type="text" class="loginusuario" id="codigo" name="usuario" size="10" maxlength="11" />
								<label class="labellogin" for="clave">Clave:</label><input placeholder="Ingresar Clave" class="loginusuario" type="password" name="clave" size="20" maxlength="20" />
								<input class="btn btn-primary" type="submit" value="Ingresar" style="margin-top: 5px;" />
							</form>
						</div>
					</div>
									
									
								
						
						
				</div>
				<div class="col-lg-3 col-md-2 col-sm-2">
					
				</div>
			</div>
		</div>

<?php }
}
			
echo '		</div>
	</div>'."\n";



include('parciales/pie.php');
/* Archivo usuarios.php */
/* AutoShop-Easy.com */
