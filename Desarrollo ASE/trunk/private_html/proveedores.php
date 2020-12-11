<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/proveedores.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

//  ----------------  obtener nombres de proveedores   -------------------

	$consulta = "SELECT prov_id, prov_nic, prov_activo FROM " . $dbpfx . "proveedores ORDER BY prov_nic";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
	$num_provs = mysql_num_rows($arreglo);
	$provs = array();
//	$provs[0] = 'Sin Proveedor';
	while ($prov = mysql_fetch_array($arreglo)) {
		$provs[$prov['prov_id']] = $prov['prov_nic'];
		$provsact[$prov['prov_id']] = $prov['prov_activo'];
	}
//	print_r($provs);
//  ----------------  nombres de proveedores   -------------------

if (($accion==="crear") || ($accion==="modificar")) {

	$funnum = 1105000;

	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
//	echo $retorno;
	if ($retorno == '1') {
		$mensaje = 'Acceso autorizado';
		include('idiomas/' . $idioma . '/proveedores.php');
		include('parciales/encabezado.php');
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	if($accion==="modificar") {
		$pregunta = "SELECT * FROM " . $dbpfx . "proveedores WHERE prov_id = '$prov_id'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$filas = mysql_num_rows($matriz);
		if ($filas > 0) {
		$prov = mysql_fetch_array($matriz);
		} else {
			$accion='crear';
		}
	} else {
//		unset($_SESSION['prov']);
	}

//	echo 'Estamos en la sección crear';
	echo '
		<div class="page-content">
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
					<div class="content-box-header">
						<div class="panel-title">
							<h2>Datos del Proveedor: '; echo ($prov['prov_razon_social'] != '') ? $prov['prov_razon_social'] : $_SESSION['prov']['nombre']; echo '</h2>
						</div>
					</div>
				</div>
			</div>
			<br>'."\n";

	if($_SESSION['prov']['mensaje'] != '') {
		echo '
			<div class="row">
				<div class="col-md-12">
					<span class="alerta">' . $_SESSION['prov']['mensaje'] . '</span>
				</div>
			</div>'."\n";
	}

	echo '			<div class="row">
				<form action="proveedores.php?accion='; if ($accion==="modificar") { echo 'actualizar';} else {echo 'insertar';} echo '" method="post" enctype="multipart/form-data">
				<div class="col-md-12 panel-body">
					<div class="form-group">
						<div class="col-md-12">
							<div class="form-group" style="margin-bottom: 1rem;">
								<label style="font-size: initial;" for="">Razón Social</label>
										<input type="text" class="form-control" name="nombre" size="69" maxlength="255" value="'; echo ($prov['prov_razon_social'] != '') ? $prov['prov_razon_social'] : $_SESSION['prov']['nombre']; echo '">
							</div>
							<div class="form-row" style="display: flex; margin-bottom: 1rem;">
								<div class="form-group">
									<label style="font-size: initial;" for="nic">NIC</label>
										<input type="text" class="form-control" name="nic" size="30" maxlength="32" value="'; echo ($prov['prov_nic'] != '') ? $prov['prov_nic'] : $_SESSION['prov']['nic']; echo '">
								</div>
								<div class="form-group">
									<label style="font-size: initial;" for="rfc">RFC</label>
										<input type="text" class="form-control" name="rfc" size="30" maxlength="13" value="'; echo ($prov['prov_rfc'] != '') ? $prov['prov_rfc'] : $_SESSION['prov']['rfc']; echo '">
								</div>
							</div>
							<div class="form-group" style="margin-bottom: 1rem;">
									<label style="font-size: initial;" for="">Calle</label>
										<input type="text" class="form-control" name="calle" size="69" maxlength="120" value="'; echo ($prov['prov_calle'] != '') ? $prov['prov_calle'] : $_SESSION['prov']['calle']; echo '">
							</div>
							<div class="form-row" style="display: flex; margin-bottom: 1rem;">
								<div class="form-group">
									<label style="font-size: initial;" for="">Colonia</label>
										<input type="text" class="form-control" name="colonia" size="30" maxlength="60" value="'; echo ($prov['prov_colonia'] != '') ? $prov['prov_colonia'] : $_SESSION['prov']['colonia']; echo '">
								</div>
								<div class="form-group">
									<label style="font-size: initial;" for="">Municipio / Delegación</label>
										<input type="text" class="form-control" name="municipio" size="30" maxlength="60" value="'; echo ($prov['prov_municipio'] != '') ? $prov['prov_municipio'] : $_SESSION['prov']['municipio']; echo '">
								</div>
							</div>
							<div class="form-row" style="display: flex; margin-bottom: 1rem;">
								<div class="form-group">
									<label style="font-size: initial;" for="">País:</label>
										<input type="text" class="form-control" name="pais" size="30" maxlength="40"  value="'; echo ($prov['prov_pais'] != 'México') ? 'México' : $prov['prov_pais']; echo '">
								</div>
								<div class="form-group">
									<label style="font-size: initial;" for="">Estado:</label>
										<select name="estado" class="form-control" size="1">

											<option value="" '; if ($prov['prov_estado']=='') {echo 'selected="1"';} echo '>Seleccione...</option>
											<option value="AGS" '; if ($prov['prov_estado']=='AGS' || $_SESSION['prov']['estado']=='AGS') {echo 'selected="1"';} echo '>Aguascalientes</option>
											<option value="BC" '; if ($prov['prov_estado']=='BC' || $_SESSION['prov']['estado']=='BC') {echo 'selected="1"';} echo '>Baja California</option>
											<option value="BCS" '; if ($prov['prov_estado']=='BCS' || $_SESSION['prov']['estado']=='BCS') {echo 'selected="1"';} echo '>Baja California Sur</option>
											<option value="CAM" '; if ($prov['prov_estado']=='CAM' || $_SESSION['prov']['estado']=='CAM') {echo 'selected="1"';} echo '>Campeche</option>
											<option value="CHIS" '; if ($prov['prov_estado']=='CHIS' || $_SESSION['prov']['estado']=='CHIS') {echo 'selected="1"';} echo '>Chiapas</option>
											<option value="CHIH" '; if ($prov['prov_estado']=='CHIH' || $_SESSION['prov']['estado']=='CHIH') {echo 'selected="1"';} echo '>Chihuahua</option>
											<option value="COA" '; if ($prov['prov_estado']=='COA' || $_SESSION['prov']['estado']=='COA') {echo 'selected="1"';} echo '>Coahuila</option>
											<option value="COL" '; if ($prov['prov_estado']=='COL' || $_SESSION['prov']['estado']=='COL') {echo 'selected="1"';} echo '>Colima</option>
											<option value="CDMX" '; if ($prov['prov_estado']=='CDMX' || $_SESSION['prov']['estado']=='CDMX') {echo 'selected="1"';} echo '>Ciudad de México</option>
											<option value="DUR" '; if ($prov['prov_estado']=='DUR' || $_SESSION['prov']['estado']=='DUR') {echo 'selected="1"';} echo '>Durango</option>
											<option value="MEX" '; if ($prov['prov_estado']=='MEX' || $_SESSION['prov']['estado']=='MEX') {echo 'selected="1"';} echo '>Estado de México</option>
											<option value="GUE" '; if ($prov['prov_estado']=='GUE' || $_SESSION['prov']['estado']=='GUE') {echo 'selected="1"';} echo '>Guerrero</option>
											<option value="GUA" '; if ($prov['prov_estado']=='GUA' || $_SESSION['prov']['estado']=='GUA') {echo 'selected="1"';} echo '>Guanajuato</option>
											<option value="HGO" '; if ($prov['prov_estado']=='HGO' || $_SESSION['prov']['estado']=='HGO') {echo 'selected="1"';} echo '>Hidalgo</option>
											<option value="JAL" '; if ($prov['prov_estado']=='JAL' || $_SESSION['prov']['estado']=='JAL') {echo 'selected="1"';} echo '>Jalisco</option>
											<option value="MICH" '; if ($prov['prov_estado']=='MICH' || $_SESSION['prov']['estado']=='MICH') {echo 'selected="1"';} echo '>Michoacán</option>
											<option value="MOR" '; if ($prov['prov_estado']=='MOR' || $_SESSION['prov']['estado']=='MOR') {echo 'selected="1"';} echo '>Morelos</option>
											<option value="NAY" '; if ($prov['prov_estado']=='NAY' || $_SESSION['prov']['estado']=='NAY') {echo 'selected="1"';} echo '>Nayarit</option>
											<option value="NL" '; if ($prov['prov_estado']=='NL' || $_SESSION['prov']['estado']=='NL') {echo 'selected="1"';} echo '>Nuevo León</option>
											<option value="OAX" '; if ($prov['prov_estado']=='OAX' || $_SESSION['prov']['estado']=='OAX') {echo 'selected="1"';} echo '>Oaxaca</option>
											<option value="PUE" '; if ($prov['prov_estado']=='PUE' || $_SESSION['prov']['estado']=='PUE') {echo 'selected="1"';} echo '>Puebla</option>
											<option value="QRO" '; if ($prov['prov_estado']=='QRO' || $_SESSION['prov']['estado']=='QRO') {echo 'selected="1"';} echo '>Querétaro</option>
											<option value="QR" '; if ($prov['prov_estado']=='QR' || $_SESSION['prov']['estado']=='QR') {echo 'selected="1"';} echo '>Quintana Roo</option>
											<option value="SLP" '; if ($prov['prov_estado']=='SLP' || $_SESSION['prov']['estado']=='SLP') {echo 'selected="1"';} echo '>San Luís Potosí</option>
											<option value="SIN" '; if ($prov['prov_estado']=='SIN' || $_SESSION['prov']['estado']=='SIN') {echo 'selected="1"';} echo '>Sinaloa</option>
											<option value="SON" '; if ($prov['prov_estado']=='SON' || $_SESSION['prov']['estado']=='SON') {echo 'selected="1"';} echo '>Sonora</option>
											<option value="TAB" '; if ($prov['prov_estado']=='TAB' || $_SESSION['prov']['estado']=='TAB') {echo 'selected="1"';} echo '>Tabasco</option>
											<option value="TAM" '; if ($prov['prov_estado']=='TAM' || $_SESSION['prov']['estado']=='TAM') {echo 'selected="1"';} echo '>Tamaulipas</option>
											<option value="TLX" '; if ($prov['prov_estado']=='TLX' || $_SESSION['prov']['estado']=='TLX') {echo 'selected="1"';} echo '>Tlaxcala</option>
											<option value="VER" '; if ($prov['prov_estado']=='VER' || $_SESSION['prov']['estado']=='VER') {echo 'selected="1"';} echo '>Veracruz</option>
											<option value="YUC" '; if ($prov['prov_estado']=='YUC' || $_SESSION['prov']['estado']=='YUC') {echo 'selected="1"';} echo '>Yucatán</option>
											<option value="ZAC" '; if ($prov['prov_estado']=='ZAC' || $_SESSION['prov']['estado']=='ZAC') {echo 'selected="1"';} echo '>Zacatecas</option>
										</select>
								</div>
								<div class="form-group">
									<label style="font-size: initial;" for="">Codigo Postal</label>
										<input type="text" class="form-control" name="codigop" size="10" maxlength="5" value="'; echo ($prov['prov_cp'] != '') ? $prov['prov_cp'] : $_SESSION['prov']['cp']; echo '">
								</div>
							</div>

							<div class="form-row" style="display: flex; margin-bottom: 1rem;">
								<div class="form-group">
									<label style="font-size: initial;" for="">% de IVA</label>
										<input type="text" class="form-control" name="iva" size="17" maxlength="4" value="'; echo ($prov['prov_iva'] != '') ? ($prov['prov_iva'] * 100) : ($_SESSION['prov']['iva'] * 100); echo '">
								</div>
								<div class="form-group">
									<label style="font-size: initial;" for="">Días de crédito</label>
										<input type="text" class="form-control" name="dias_credito" size="17" maxlength="4" value="'; echo ($prov['prov_dias_credito'] != '') ? $prov['prov_dias_credito'] : $_SESSION['prov']['dias_credito']; echo '">
								</div>
								<div class="form-group">
									<label style="font-size: initial;" for="">Días Prom. de Entrega</label>
										 <input type="text" class="form-control" name="dde" size="18" maxlength="2" value="'; echo ($prov['prov_dde'] != '') ? $prov['prov_dde'] : $_SESSION['prov']['dde']; echo '" />
								</div>
							</div>
								<div class="row" style="margin-bottom: 1rem;display: flex; font-size: initial;>
									<legend class="col-form-label col-sm-2 pt-0">' . $lang['ProvEstat'] . '</legend>
										<div class="col-md-10">

											<div class="form-check" style="position: relative;display: block;padding-left: 1.25rem;">
												<input type="radio" name="activo" value="0"'; if($prov['prov_activo'] == '0') { echo ' checked'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="">' . $lang['Deshab'] . '</label>
											</div>
											<div class="form-check" style="position: relative;display: block;padding-left: 1.25rem;">
												<input type="radio" name="activo" value="1"'; if($prov['prov_activo'] == '1') { echo ' checked'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="">' . $lang['Activo'] . '</label>
											</div>
											<div class="form-check" style="position: relative;display: block;padding-left: 1.25rem;">
												<input type="radio" name="activo" value="2"'; if($prov['prov_activo'] == '2') { echo ' checked'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="">' . $lang['Bloqueado'] . '</label>
											</div>
										</div>
								</div>';

	if($asientos == '1') {
		echo '					<div class="form-group" style="margin-bottom: 1rem;">
									<label style="font-size: initial;" for="">Cuenta Contable</label>
										<input type="text" class="form-control" name="contable" size="69" maxlength="60" value="'; echo ($prov['cuenta_contable'] != '') ? $prov['cuenta_contable'] : $_SESSION['prov']['contable']; echo '" />
								</div>';
	}

	echo '					<div class="form-group" style="margin-bottom: 1rem;">
									<label style="font-size: initial;" for="">Habilitar Notificaciones E-mail?:</label>
										<input type="checkbox" name="notifica"'; if($prov['prov_env_ped'] == '1') { echo ' checked'; } echo ' />
								</div>';
	// --- CONTACTO 1 --
	echo '				<p><label style="font-size: initial;"><b>REPRESENTANTE</b></label><br>
							<div style="border-style: dashed;border-width: 2px; border-color: #007bff; width: min-content;">
								<div class="form-row" style="display: flex; margin-bottom: 1rem;">
									<div class="form-group">
										<label style="font-size: initial;" for="">Nombre Representante</label>
											<input type="text" class="form-control"  name="representante" size="30" maxlength="120" value="'; echo ($prov['prov_representante'] != '') ? $prov['prov_representante'] : $_SESSION['prov']['representante']; echo '">
									</div>
									<div class="form-group">
										<label style="font-size: initial;" for="puesto1">Puesto</label>
											<input type="text" class="form-control"  name="puesto1" size="30" maxlength="120" value="'; echo ($prov['prov_rep1_puesto'] != '') ? $prov['prov_rep1_puesto'] : $_SESSION['prov']['rep1_puesto']; echo '">
									</div>
								</div>

								<div class="form-row" style="display: flex; margin-bottom: 1rem;">
									<div class="form-group">
										<label style="font-size: initial;" for="">Cuenta Bancaria</label>
											<input type="text" class="form-control"  name="cbancaria1" size="30" maxlength="40" value="'; echo ($prov['prov_rep1_cbancaria'] != '') ? $prov['prov_rep1_cbancaria'] : $_SESSION['prov']['rep1_cbancaria']; echo '">
									</div>
									<div class="form-group">
										<label style="font-size: initial;" for="">CLABE Interbancaria</label>
											<input type="text" class="form-control" name="clabe1" size="30" maxlength="40" value="'; echo ($prov['prov_rep1_clabe'] != '') ? $prov['prov_rep1_clabe'] : $_SESSION['prov']['rep1_clabe']; echo '">
									</div>
								</div>

								<div class="form-row" style="display: flex; margin-bottom: 1rem;">
									<div class="form-group">
										<label style="font-size: initial;" for="">Teléfono</label>
											<input type="text" class="form-control"  name="telefono1" size="30" maxlength="40" value="'; echo ($prov['prov_telefono1'] != '') ? $prov['prov_telefono1'] : $_SESSION['prov']['telefono1']; echo '">
									</div>
									<div class="form-group">
										<label style="font-size: initial;" for="">Celular</label>
											<input type="text" class="form-control" name="telefono2" size="30" maxlength="40" value="'; echo ($prov['prov_telefono2'] != '') ? $prov['prov_telefono2'] : $_SESSION['prov']['telefono2rep2_']; echo '">
									</div>
								</div>
									<div class="form-group" style="margin-bottom: 1rem;">
										<label style="font-size: initial;" for="">E-mail</label>
											<input type="text" class="form-control" name="email" size="69" maxlength="128" value="'; echo ($prov['prov_email'] != '') ? $prov['prov_email'] : $_SESSION['prov']['email']; echo '">
									</div>
							</div>';


	echo '						<div class="form-group">
						<input type="submit" class="btn btn-success" value="Guardar" />&nbsp;
						<hr>
					</div>
					</div>
				</div>
			</div>'."\n";
						/*
						MOSTRAR / OCULTAR JQUERY	echo '
			<script src="js/jquery-1.9.1.js"></script>
			<script type="text/javascript">
				$( "#btn-c-1" ).click(function() {
				  $( "#oculta2" ).toggle( "slow" );
				  $( "#contacto2" ).toggle( "slow" );
				  $( "#btn-c-1" ).toggle( "slow" );
				});
				$( "#btn-c-2" ).click(function() {
				  $( "#oculta3" ).toggle( "slow" );
				  $( "#contacto3" ).toggle( "slow" );
				  $( "#btn-c-2" ).toggle( "slow" );
				});
				$( "#btn-c-2-o" ).click(function() {
				  $( "#oculta2" ).toggle( "slow" );
				  $( "#contacto2" ).toggle( "slow" );
				  $( "#btn-c-1" ).toggle( "slow" );
				});
				$( "#btn-c-3-o" ).click(function() {
				  $( "#oculta3" ).toggle( "slow" );
				  $( "#contacto3" ).toggle( "slow" );
				  $( "#btn-c-2" ).toggle( "slow" );
				});
			</script>';*/

	if($accion==="modificar") {
		echo '			<input type="hidden" name="prov_id" value="' . $prov['prov_id'] . '" />';
	}

	echo '			</form>
		</div>'."\n";
	unset($_SESSION['prov']);
}

elseif (($accion==='insertar') || ($accion==='actualizar')) {

	if (validaAcceso('1105000', $dbpfx) == '1') {
		$mensaje = 'Acceso autorizado';
	} else {
			$_SESSION['msjerror'] = $lang['acceso_error'];
		 redirigir('almacen.php');
	}

	unset($_SESSION['prov']);
	$_SESSION['prov'] = array();
	$calle=preparar_entrada_bd($calle); $_SESSION['prov']['calle'] = $calle;
	$colonia=preparar_entrada_bd($colonia); $_SESSION['prov']['colonia'] = $colonia;
	$contable=preparar_entrada_bd($contable); $_SESSION['prov']['contable'] = $contable;
	$dde=limpiarNumero($dde); $_SESSION['prov']['dde'] = $dde;
	$dias_credito = limpiarNumero($dias_credito); $_SESSION['prov']['dias_credito'] = $dias_credito;
	$email=preparar_entrada_bd($email); $_SESSION['prov']['email'] = $email;
	$estado=preparar_entrada_bd($estado); $_SESSION['prov']['estado'] = $estado;
	$iva=(limpiarNumero($iva) / 100); $_SESSION['prov']['iva'] = $iva;
	$municipio=preparar_entrada_bd($municipio); $_SESSION['prov']['municipio'] = $municipio;
	$nic=preparar_entrada_bd($nic); $_SESSION['prov']['nic'] = $nic;
	$nombre=preparar_entrada_bd($nombre); $_SESSION['prov']['nombre'] = $nombre;
	$pais=preparar_entrada_bd($pais); $_SESSION['prov']['pais'] = $pais;
	$representante=preparar_entrada_bd($representante); $_SESSION['prov']['representante'] = $representante;
	$rfc=preparar_entrada_bd($rfc); $_SESSION['prov']['rfc'] = $rfc;
	$telefono1=preparar_entrada_bd($telefono1); $_SESSION['prov']['telefono1'] = $telefono1;
	$telefono2=preparar_entrada_bd($telefono2); $_SESSION['prov']['telefono2'] = $telefono2;
	// SE AGREGAN 2 CAMPOS PARA CONTACTOS
	$representante2=preparar_entrada_bd($representante2); $_SESSION['prov']['representante2'] = $representante2;
	$representante3=preparar_entrada_bd($representante3); $_SESSION['prov']['representante3'] = $representante3;
	$puesto1=preparar_entrada_bd($puesto1); $_SESSION['prov']['rep1_puesto'] = $puesto1;
	$puesto2=preparar_entrada_bd($puesto2); $_SESSION['prov']['rep2_puesto'] = $puesto2;
	$puesto3=preparar_entrada_bd($puesto3); $_SESSION['prov']['rep3_puesto'] = $puesto3;
	$cbancaria1=preparar_entrada_bd($cbancaria1); $_SESSION['prov']['rep1_cbancaria'] = $cbancaria1;
	$cbancaria2=preparar_entrada_bd($cbancaria2); $_SESSION['prov']['rep2_cbancaria'] = $cbancaria2;
	$cbancaria3=preparar_entrada_bd($cbancaria3); $_SESSION['prov']['rep3_cbancaria'] = $cbancaria3;
	$clabe1=preparar_entrada_bd($clabe1); $_SESSION['prov']['rep1_clabe'] = $clabe1;
	$clabe2=preparar_entrada_bd($clabe2); $_SESSION['prov']['rep2_clabe'] = $clabe2;
	$clabe3=preparar_entrada_bd($clabe3); $_SESSION['prov']['rep3_clabe'] = $clabe3;
	$cont2_telefono=preparar_entrada_bd($cont2_telefono); $_SESSION['prov']['rep2_telefono'] = $cont2_telefono;
	$cont3_telefono=preparar_entrada_bd($cont2_telefono); $_SESSION['prov']['rep3_telefono'] = $cont3_telefono;
	$cont2_celular=preparar_entrada_bd($cont2_celular); $_SESSION['prov']['rep2_celular'] = $cont2_celular;
	$cont3_celular=preparar_entrada_bd($cont3_celular); $_SESSION['prov']['rep3_celular'] = $cont3_celular;
	$email2=preparar_entrada_bd($email2); $_SESSION['prov']['rep2_email'] = $email2;
	$email3=preparar_entrada_bd($email3); $_SESSION['prov']['rep3_email'] = $email3;
	$codigop=preparar_entrada_bd($codigop); $_SESSION['prov']['cp'] = $codigop;


//	echo $nombre;
//	print_r($_SESSION['prov']);

	$error = 'no';
	$mensaje= '';
//	echo '<br><br>====== ' . $error . ' <<>> ' . $mensaje . ' ======<br><br>';
//	echo strlen($telefono1);

	if(($activo == '1' && $accion==='actualizar') || $accion==='insertar') {
		if (strlen($nombre) < 3) {$error = 'si'; $mensaje .='El nombre de la Razón Social es muy corto: ' . $nombre . '<br>';}
		if (strlen($nic) < 2) {$error = 'si'; $mensaje .='El NIC es muy corto: ' . $nic . '<br>';}
		if (strlen($email) < 6) {$error = 'si'; $mensaje .='La dirección de la cuenta de correo es muy corta: ' . $email . '<br>';}
		if (strlen($telefono1) < 8) {$error = 'si'; $mensaje .='Los números de teléfono deben tener lada y número local: ' . $telefono1 . '<br>';}
		if (strlen($rfc) < 12) {$error = 'si'; $mensaje .='El RFC es muy corto: 12 posiciones para empresas, 13 para personas.<br>';}
		if (strlen($rfc) > 13) {$error = 'si'; $mensaje .='El RFC es muy largo: 12 posiciones para empresas, 13 para personas.<br>';}
/*
		if (strlen($telefono2) < 10) {$error = 'si'; $mensaje .='El número debe tener lada y número local: ' . $telefono2 . '<br>';}
		if (strlen($calle) < 5) {$error = 'si'; $mensaje .='La calle y número es muy corto: ' . $calle . '<br>';}
		if (strlen($colonia) < 3) {$error = 'si'; $mensaje .='La colonia es muy corta: ' . $colonia . '<br>';}
		if (strlen($municipio) < 4) {$error = 'si'; $mensaje .='El municipio o delegación es muy corto: ' . $municipio . '<br>';}
*/
	}

	// ---- VERIFICAR RFC Y NIC ----
	if($accion == 'insertar'){
	$preg_rfc_mail = " SELECT prov_email, prov_rfc, prov_id FROM " . $dbpfx . "proveedores WHERE prov_email LIKE '%" . $email . "%' OR prov_rfc = '" . $rfc . "' ";
	$mtrz_rfc_mail = mysql_query($preg_rfc_mail) or die("Falló la selección de proveedores! 364 " . $preg_rfc_mail);
	$repetidos = mysql_num_rows($mtrz_rfc_mail);
	$actual = mysql_fetch_assoc($mtrz_rfc_mail);
	echo $actual['prov_email'] . ' -> ' . $email . ' <br > ' .$actual['prov_rfc']. ' -> ' . $rfc;
	if($email != $actual['prov_email'] || $rfc != $actual['prov_rfc']){
		if($repetidos >= 1){ //se coloca 2 por el registro del correo actual del proveedor, ya que si se coloca 0 cuenta el correo actual guardado como repetido
			$error = 'si'; 
			$mensaje ='El correo elegido y/o RFC ya existe en la lista de proveedores, no se puede agregar más de uno con estos datos.<br>';
		}
		else {
			$error = 'no';
		}
	}
	
	}
	

//	echo '<br><br>====== ' . $error . ' ======<br><br>';
	if ($error === 'no') {
		if($accion==='actualizar') { $parametros='prov_id = ' . $prov_id; } else { $parametros=''; }


		$sql_data_array = array('prov_razon_social' => $nombre,
			'prov_nic' => $nic,
			'prov_rfc' => $rfc,
			'prov_calle' => $calle,
			'prov_colonia' => $colonia,
			'prov_municipio' => $municipio,
			'prov_estado' => $estado,
			'prov_pais' => $pais,
			'prov_representante' => $representante,
			'prov_rep1_puesto' => $puesto1,	
			'prov_rep1_cbancaria' => $cbancaria1,	
			'prov_rep1_clabe' => $clabe1, 
			'prov_telefono1' => $telefono1,
			'prov_telefono2' => $telefono2,
			'prov_email' => $email,
			'prov_cp' => $codigop,
			'prov_iva' => $iva,
			'prov_dias_credito' => $dias_credito,
			'cuenta_contable' => $contable);

		if(isset($notifica)) { $sql_data_array['prov_env_ped'] = 1; } else { $sql_data_array['prov_env_ped'] = 0; }
		if($dde >= 1) { $sql_data_array['prov_dde'] = $dde; }
		if($accion==='insertar') {
			$sql_data_array['prov_activo'] = 1;
		} else {
			$sql_data_array['prov_activo'] = $activo;
		}

		if ($accion==='insertar') {
			$prov_id = ejecutar_db($dbpfx . 'proveedores', $sql_data_array, $accion, $parametros);
		} else {


		$preg_rfc_mail = " SELECT * FROM " . $dbpfx . "proveedores WHERE prov_email LIKE '%" . $email . "%' OR prov_rfc LIKE '" . $rfc . "' ";
	$mtrz_rfc_mail = mysql_query($preg_rfc_mail) or die("Falló la selección de proveedores! 364 " . $preg_rfc_mail);
	$repetidos1 = mysql_num_rows($mtrz_rfc_mail);
	$repetidos2 = mysql_num_rows($mtrz_rfc_mail);
	$actual = mysql_fetch_assoc($mtrz_rfc_mail);
	echo $actual['prov_email'] . ' -> ' . $email . ' <br > ' .$actual['prov_rfc']. ' -> ' . $rfc;
	if($email != $actual['prov_email'] || $rfc != $actual['prov_rfc']){
		if($repetidos1 >= 2){ //se coloca 2 por el registro del correo actual del proveedor, ya que si se coloca 0 cuenta el correo actual guardado como repetido
			$error = 'si'; 
			$_SESSION['msjerror'] ='El correo elegido y/o RFC ya existe en la lista de proveedores, no se puede agregar más de uno con estos datos.';
		}
		else {
			$error = 'no';
			ejecutar_db($dbpfx . 'proveedores', $sql_data_array, $accion, $parametros);
		$mensaje = 'Datos Actualizados correctamente';
		}
	}else{
		ejecutar_db($dbpfx . 'proveedores', $sql_data_array, $accion, $parametros);
		$mensaje = 'Datos Actualizados correctamente';
	}

			
		}
		bitacora('999999992', 'Se acaba de ' . $accion . ' el proveedor ' . $prov_id, $dbpfx);
		unset($_SESSION['prov']);
		redirigir('proveedores.php?accion=consultar&msjpass='.$mensaje.'&prov_id=' . $prov_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		if ($accion==='insertar') {
			redirigir('proveedores.php?accion=crear');
		} else {
			redirigir('proveedores.php?accion=modificar&prov_id=' . $prov_id);
		}
	}
}

elseif ($accion==="consultar") {

//	echo 'Estamos en la sección  consulta';

	if (validaAcceso('1105010', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol13']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1'))) {
			$mensaje = 'Acceso autorizado';
			include('parciales/encabezado.php');
			echo '	<div id="body">';
			include('parciales/menu_inicio.php');
			echo '		<div id="principal">';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	$error = 'si'; $num_cols = 0;
	if ($prov_id!='') {
		$pregunta = "SELECT * FROM " . $dbpfx . "proveedores WHERE prov_id = '$prov_id'";
		$error = 'no';
		} else {
		$nombre=preparar_entrada_bd($nombre);
		$email=preparar_entrada_bd($email);
		$nic=preparar_entrada_bd($nic);
		$mensaje= 'Se necesita al menos un dato para buscar.<br>';
		if (($nombre!='') || ($email!='') || ($nic!='')) {
			$error = 'no'; $mensaje ='';
			$pregunta = "SELECT * FROM " . $dbpfx . "proveedores WHERE ";
			if ($nombre) {$pregunta .= "prov_razon_social like '%$nombre%' ";}
		if (($nombre) && ($email)) {$pregunta .= "AND prov_email like '%$email%' ";}
			elseif ($email) {$pregunta .= "prov_email like '%$email%' ";}
		if ((($email) || ($nombre)) && ($nic)) {$pregunta .= "AND prov_nic like '%$nic%'";}
			elseif ($nic) {$pregunta .= "prov_nic like '%$nic%'";}
		}
	}
	if ($error ==='no') {
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
	}
	if ($num_cols>0) {
		echo '
		<div class="page-content">
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
					<div class="content-box-header">
						<div class="panel-title">
							<h2>Datos del Proveedor:</h2>
						</div>
					</div>
				</div>
			</div>
			<br>'."\n";

		while ($prov = mysql_fetch_array($matriz)) {
			echo '
			<div class="row">
				<div class="col-md-4 panel-body shadow-box">
					<div class="form-group">
						<div class="col-md-12">
							<table>
								<tr>
									<td align="right" class="obscuro"><big><b>Número de Proveedor:</b></big></td>
									<td><big>' . $prov['prov_id'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Razón Social:</b></big></td>
									<td><big>' . $prov['prov_razon_social'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>RFC:</b></big></td>
									<td><big>' . $prov['prov_rfc'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>NIC:</b></big></td>
									<td><big>' . $prov['prov_nic'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>QVID:</b></big></td>
									<td><big>' . $prov['prov_qv_id'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Activo?:</b></big></td>
									<td><big>';
			if($prov['prov_activo'] == 1) { echo 'Sí'; }
			elseif($prov['prov_activo'] == 2) { echo 'Bloqueado'; }
			else { echo 'No'; }
			echo '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Enviar Notificaciones:</b></big></td>
									<td>
										<big>';
			echo ($prov['prov_env_ped'] == '1') ? 'Sí' : 'No';
			echo '
										</big>
									</td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Representante:</b></big></td>
									<td><big>' . $prov['prov_representante'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Teléfono 1:</b></big></td>
									<td><big>' . $prov['prov_telefono1'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Teléfono 2:</b></big></td>
									<td><big>' . $prov['prov_telefono2'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>E-Mail:</b></big></td>
									<td><big>' . $prov['prov_email'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Días Promesa de Entrega:</b></big></td>
									<td><big>' . $prov['prov_dde'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>% de IVA:</b></big></td>
									<td><big>' . ($prov['prov_iva'] * 100) . '%</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Días de crédito:</b></big></td>
									<td><big>' . $prov['prov_dias_credito'] . '</big></td>
								</tr>'."\n";

			if($asientos == '1') {
				echo '
								<tr>
									<td align="right" class="obscuro"><big><b>Cuenta Contable:</b></big></td>
									<td><big>' . $prov['cuenta_contable'] . '</big></td>
								</tr>'."\n";
			}
			echo '
								<tr>
									<td align="right" class="obscuro"><big><b>Calle con número:</b></big></td>
									<td><big>' . $prov['prov_calle'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Colonia:</b></big></td>
									<td><big>' . $prov['prov_colonia'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Delegación o Municipio:</b></big></td>
									<td><big>' . $prov['prov_municipio'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Estado:</b></big></td>
									<td><big>' . $prov['prov_estado'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>País:</b></big></td>
									<td><big>' . $prov['prov_pais'] . '</big></td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Codigo Postal:</b></big></td>
									<td><big>' . $prov['prov_cp'] . '</big></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
			echo '
			<div class="row" style="align-content: center;display: flex;">
				<div class="col-md-4 panel-body">
					<div class="form-group">'."\n";
//			echo '<a href="proveedores.php?accion=modificar&prov_id=' . $prov['prov_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/cambiar-datos.png" alt="Modificar" title="Modificar" class="btn"></a>'."\n";
			echo '	<a style="text-decoration: none;" href="proveedores.php?accion=modificar&prov_id=' . $prov['prov_id'] . '">
							<button type="button" class="btn btn-light" style="background-color: #0060f7; border-color: #013c97; color: white; margin: 5px;" ><b>Modificar <br>Datos</b></button>
						</a>'."\n";
			echo	'<a style="text-decoration: none;" href="proveedores.php?accion=contactos&prov_id=' . $prov['prov_id'] . '&prov_razon_social=' . $prov['prov_razon_social'] . '">
							<button type="button" class="btn btn-light" style="background-color: #009af7; border-color: #0060f7; color: white; margin: 5px;"><!--<img src="idiomas/' . $idioma . '/imagenes/administrar_contactos.png" width="48px" style="padding-bottom: 5px;padding-top: 5px; margin: 5px;">--><b>Ver<br>Contactos</b></button>
						</a>'."\n";
			echo '
						<a style="text-decoration: none;" href="proveedores.php?accion=pagos_grupales&prov_id=' . $prov['prov_id'] . '">
							<button type="button" class="btn btn-light" style="background-color: #00BDF7; border-color: #0282AA; color: white; margin: 5px;"><b>Pagos <br>Grupales</b></button>
						</a>
						<a style="text-decoration: none;" href="proveedores.php?accion=regpago&provid=' . $prov['prov_id'] . '">
							<button type="button" class="btn btn-primary" style="background-color: #00EBF7; border-color: #02B4BD; color: #005A5E; margin: 5px;"><b>Registrar <br>Pagos</b></button>
						</a>
						<a style="text-decoration: none;" href="proveedores.php?accion=pedidos&prov_id=' . $prov['prov_id'] . '&pendiente=1">
							<button type="button" class="btn btn-light" style="background-color: #00F78F; border-color: #029758; color: #015934; margin: 5px;" ><b>Pedidos <br>Pendientes</b></button>
						</a>
						<a style="text-decoration: none;" href="proveedores.php?accion=pedidos&prov_id=' . $prov['prov_id'] . '&pendiente=2">
							<button type="button" class="btn btn-light" style="background-color: #00F70F; border-color: #03930C; color: #03930c; margin: 5px;"><b>Pedidos <br>Pagados</b></button>
						</a>
						<a style="text-decoration: none;" href="proveedores.php?accion=pedidos&prov_id=' . $prov['prov_id'] . '&pendiente=3">
							<button type="button" class="btn btn-light" style="background-color: #03cc77; border-color: #00844c; color: white; margin: 5px;"><b>Todos los <br>Pedidos</b></button>
						</a>

						<hr>
					</div>
				</div>
			</div>'."\n";
		}
		echo '
		</div>'."\n";
	} else {
		$mensaje .='No se encontraron registros con esos datos.</br>';
		echo '<p>' . $mensaje . '</p>';
	}
}

elseif ($accion==="contactos") {
// SECCION CONTACTOS DE PROVEeDORES
		$funnum = 1105000;

		$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);

		if ($retorno == '1') {
			$mensaje = 'Acceso autorizado';
			include('idiomas/' . $idioma . '/proveedores.php');
			include('parciales/encabezado.php');
			echo '	<div id="body">';
			include('parciales/menu_inicio.php');
			echo '		<div id="principal">';
		}
		else {
			redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
		}

		echo '
				<div class="page-content">

					<div class="row"> <!-box header del título. -->
						<div class="col-md-12">
							<div class="content-box-header">
								<div class="panel-title">
									<h2>Contactos Activos del Proveedor: ' . $_GET['prov_razon_social'] . '</h2>
								</div>
							</div>
						</div>
					</div>
					<br>'."\n";

			echo '	<div class="row">
						<div class="col-md-12 panel-body">
							<div class="form-group">
								<div class="col-md-10">
									<a href="proveedores.php?accion=crear_contacto&contacto_proveedor=' . $prov_id . '">
										<button type="button" class="btn btn-success"><b>Agregar <br>Nuevo Contacto</b></button>
									</a>
									<a href="proveedores.php?accion=contactos_des&prov_razon_social=' . $prov_razon_social . '&prov_id=' . $prov_id . '">
										<button type="button" class="btn btn-danger"><b>Ver Contactos <br>Desactivados</b></button>
									</a>
									<a href="proveedores.php?accion=consultar&prov_id=' . $prov_id . '">
										<button type="button" class="btn btn-warning"><b>Ver <br>Proveedor</b></button>
									</a><p>

									<table cellspacing="0" class="table-new">
										<tbody>
											<tr>
												<th><big>Nombre</big></th>
												<th><big>Puesto</big></th>
												<th><big>Teléfono</big></th>
												<th><big>Email</big></th>
												<th></th>
											</tr>'."\n";

							$preg5 = "SELECT * FROM " . $dbpfx . "prov_contactos WHERE contacto_proveedor = $prov_id AND contacto_activo = 1 ORDER BY contacto_nombre ASC";
							$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de valores comunes! " . $preg5);
							$clase = 'claro';
							$cuantos_c = mysql_num_rows($matr5);
							if ($cuantos_c != 0){
								while ($res5 = mysql_fetch_array($matr5)) {
			echo '
											<tr class="' . $clase . '">
												<td style="text-align: left !important;">
													<big>' . $res5['contacto_nombre'] . '</big>
												</td>
												<td style="text-align: left !important;">
													<big>' . $res5['contacto_puesto'] . '</big>
												</td>
												<td style="text-align: left !important;">
													<big>' . $res5['contacto_telefono'] . '</big>
												</td>
												<td style="text-align: left !important;">
													<big>' . $res5['contacto_email'] . '</big>
												</td>
												<td style="text-align:center;">
													<a href="proveedores.php?accion=ver_contacto&contacto_id=' . $res5['contacto_id'] . '">
														<button type="button" class="btn btn-warning btn-small"><b>Consultar</b></button>
													</a>
												</td>
											</tr>'."\n";

							if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }

							}

								
							}
							else {
								echo '<tr><td colspan="5"><center><h2><b>Aún no existen contactos</b></h2></center></td></tr>';
						}
			echo '
										</tbody>
									</table>';
			echo '<br><a href="proveedores.php?accion=consultar&prov_id=' . $prov_id . '">
										<button type="button" class="btn btn-warning"><b>Regresar a <br>Proveedor</b></button>
									</a>';

			echo '
								</div>
							</div>
						</div>
					</div>';
}

elseif ($accion==="contactos_des") {
// SECCION CONTACTOS DE PRov DESACTIVADOS
		$funnum = 1105000;

		$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);

		if ($retorno == '1') {
			$mensaje = 'Acceso autorizado';
			include('idiomas/' . $idioma . '/proveedores.php');
			include('parciales/encabezado.php');
			echo '	<div id="body">';
			include('parciales/menu_inicio.php');
			echo '		<div id="principal">';
		}
		else {
			redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
		}



		echo '
				<div class="page-content">

					<div class="row"> <!-box header del título. -->
						<div class="col-md-12">
							<div class="content-box-header">
								<div class="panel-title">
									<h2>Contactos Desactivados del Proveedor: ' . $_GET['prov_razon_social'] . '</h2>
								</div>
							</div>
						</div>
					</div>
					<br>'."\n";

			echo '	<div class="row">
						<div class="col-md-12 panel-body">
							<div class="form-group">
								<div class="col-md-10">
									<a href="proveedores.php?accion=crear_contacto&contacto_proveedor=' . $prov_id . '">
										<button type="button" class="btn btn-success"><b>Agregar <br>Nuevo Contacto</b></button>
									</a>
									<a href="proveedores.php?accion=contactos&prov_razon_social=' . $prov_razon_social . '&prov_id=' . $prov_id . '">
										<button type="button" class="btn btn-primary"><b>Ver Contactos <br>Activos</b></button>
									</a>
									<a href="proveedores.php?accion=consultar&prov_id=' . $prov_id . '">
										<button type="button" class="btn btn-warning"><b>Ver <br>Proveedor</b></button>
									</a><p>

									<table cellspacing="0" class="table-new">
										<tbody>
											<tr>
												<th><big>Nombre</big></th>
												<th><big>Puesto</big></th>
												<th><big>Teléfono</big></th>
												<th><big>Email</big></th>
												<th></th>
											</tr>'."\n";

							$preg5 = "SELECT * FROM " . $dbpfx . "prov_contactos WHERE contacto_proveedor = $prov_id AND contacto_activo = 0 ORDER BY contacto_nombre ASC";
							$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de valores comunes! " . $preg5);
							$clase = 'claro';
							$cuantos_c = mysql_num_rows($matr5);
							if ($cuantos_c != 0){
								while ($res5 = mysql_fetch_array($matr5)) {
				echo '
												<tr class="' . $clase . '">
													<td style="text-align: left !important;">
														<big>' . $res5['contacto_nombre'] . '</big>
													</td>
													<td style="text-align: left !important;">
														<big>' . $res5['contacto_puesto'] . '</big>
													</td>
													<td style="text-align: left !important;">
														<big>' . $res5['contacto_telefono'] . '</big>
													</td>
													<td style="text-align: left !important;">
														<big>' . $res5['contacto_email'] . '</big>
													</td>
													<td style="text-align:center;">
														<a href="proveedores.php?accion=ver_contacto&contacto_id=' . $res5['contacto_id'] . '">
															<button type="button" class="btn btn-warning btn-small"><b>Consultar</b></button>
														</a>
													</td>
												</tr>'."\n";

							if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }

								}
							}
							else {
								echo '<tr><td colspan="5"><center><h2><b>No hay contactos Desactivados</b></h2></center></td></tr>';
						}
			echo '
										</tbody>
									</table>';
			echo '<br><a href="proveedores.php?accion=consultar&prov_id=' . $prov_id . '">
										<button type="button" class="btn btn-warning"><b>Regresar a <br>Proveedor</b></button>
									</a>';


			echo '
								</div>
							</div>
						</div>
					</div>';
}

elseif (($accion==="crear_contacto") || ($accion==="modificar_contacto")) {

	$funnum = 1105000;

	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
//	echo $retorno;
	if ($retorno == '1') {
		$mensaje = 'Acceso autorizado';
		include('idiomas/' . $idioma . '/proveedores.php');
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
	}
	else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	if($accion==="modificar_contacto") {
		$pregunta = "SELECT * FROM " . $dbpfx . "prov_contactos WHERE contacto_id = '$contacto_id'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$filas = mysql_num_rows($matriz);
		if ($filas > 0) {
		$contacto = mysql_fetch_array($matriz);
		} else {
			$accion='crear_contacto';
		}
	} else {
//		unset($_SESSION['prov']);
	}

//	echo 'Estamos en la sección crear';

	echo '
		<div class="page-content">
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
					<div class="content-box-header">
						<div class="panel-title">
							<h2>Datos del Contacto: '; echo ($contacto['contacto_nombre'] != '') ? $contacto['contacto_nombre'] : $_SESSION['contacto']['nombre']; echo '</h2>
						</div>
					</div>
				</div>
			</div>
			<br>'."\n";

	if($_SESSION['contacto']['mensaje'] != '') {

		echo '
		<div class="row">
			<div class="col-md-12">
				<span class="alerta">' . $_SESSION['contacto']['mensaje'] . '</span>
			</div>
		</div>'."\n";

	}


		echo '<div class="row">
				<form action="proveedores.php?accion='; if ($accion==="modificar_contacto") { echo 'actualizar_contacto';} else {echo 'insertar_contacto';} echo '" method="post" enctype="multipart/form-data">
				<div class="col-md-12 panel-body">
					<div class="form-group">
						<div class="col-md-12">';

					echo '

								<div class="form-row" style="display: flex; margin-bottom: 1rem;">
									<div class="form-group">
										<label style="font-size: initial;" for="">Nombre Completo</label>
											<input type="text" class="form-control"  name="contacto_nombre" size="30" maxlength="120" value="'; echo ($contacto['contacto_nombre'] != '') ? $contacto['contacto_nombre'] : $_SESSION['contacto']['nombre']; echo '">
									</div>
									<div class="form-group">
										<label style="font-size: initial;" for="contacto_puesto">Área de Trabajo</label>
											<input type="text" class="form-control"  name="contacto_puesto" size="30" maxlength="120" value="'; echo ($contacto['contacto_puesto'] != '') ? $contacto['contacto_puesto'] : $_SESSION['contacto']['puesto']; echo '">
									</div>
								</div>

								<div class="form-row" style="display: flex; margin-bottom: 1rem;">
									<div class="form-group">
										<label style="font-size: initial;" for="">Cuenta Bancaria</label>
											<input type="text" class="form-control"  name="contacto_cbancaria" size="30" maxlength="40" value="'; echo ($contacto['contacto_cbancaria'] != '') ? $contacto['contacto_cbancaria'] : $_SESSION['contacto']['bancaria']; echo '">
									</div>
									<div class="form-group">
										<label style="font-size: initial;" for="">CLABE Interbancaria</label>
											<input type="text" class="form-control" name="contacto_CLABE" size="30" maxlength="40" value="'; echo ($contacto['contacto_CLABE'] != '') ? $contacto['contacto_CLABE'] : $_SESSION['contacto']['CLABE']; echo '">
									</div>
								</div>

								<div class="form-row" style="display: flex; margin-bottom: 1rem;">
									<div class="form-group">
										<label style="font-size: initial;" for="">Teléfono</label>
											<input type="text" class="form-control"  name="contacto_telefono" size="30" maxlength="40" value="'; echo ($contacto['contacto_telefono'] != '') ? $contacto['contacto_telefono'] : $_SESSION['contacto']['telefono']; echo '">
									</div>
									<div class="form-group">
										<label style="font-size: initial;" for="">Celular</label>
											<input type="text" class="form-control" name="contacto_movil" size="30" maxlength="40" value="'; echo ($contacto['contacto_movil'] != '') ? $contacto['contacto_movil'] : $_SESSION['contacto']['movil']; echo '">
									</div>
								</div>
									<div class="form-group" style="margin-bottom: 1rem;">
										<label style="font-size: initial;" for="">E-mail</label>
											<input type="text" class="form-control" name="contacto_email" size="69" maxlength="128" value="'; echo ($contacto['contacto_email'] != '') ? $contacto['contacto_email'] : $_SESSION['contacto']['email']; echo '">
									</div>
									<div class="form-group" style="margin-bottom: 1rem;">
										<label style="font-size: initial;" for="">Anotaciones</label>
											<textarea class="form-control" name="contacto_notas" maxlength="400" style="width: 596px; height: 100px;">'; echo ($contacto['contacto_notas'] != '') ? $contacto['contacto_notas'] : $_SESSION['contacto']['notas']; echo '</textarea>
									</div>

										<br>

									<div class="row" style="margin-bottom: 1rem;display: flex; font-size: initial;>
									<legend class="col-form-label col-sm-2 pt-0">Estado del Contacto</legend>
										<div class="col-md-10">

											<div class="form-check" style="position: relative;display: block;padding-left: 1.25rem;">
												<input type="radio" name="contacto_activo" value="0"'; if($contacto['contacto_activo'] == '0') { echo ' checked'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="">Desactivado</label>
											</div>
											<div class="form-check" style="position: relative;display: block;padding-left: 1.25rem;">
												<input type="radio" name="contacto_activo" value="1"'; if($contacto['contacto_activo'] == '1') { echo ' checked'; } echo ' />
												<label class="form-check-label style="font-size: initial;" for="">Activado</label>
											</div>
												<input type="hidden" name="contacto_proveedor" value="'; echo ($contacto['contacto_proveedor'] != '') ? $contacto['contacto_proveedor'] : $_GET['contacto_proveedor']; echo '">
												<input type="hidden" name="contacto_id" value="' . $contacto['contacto_id'] . '" />
										</div>
									</div>


						</div>';

							echo '	<div class="form-group">
										<input type="submit" class="btn btn-success" value="Guardar" />&nbsp;
										<hr>
									</div>
					</div>
				</form>
				</div>
			</div>'."\n";

	if($accion==="modificar_contacto") {
		echo '<input type="hidden" name="contacto_id" value="' . $contacto['contacto_id'] . '" />';
	}

echo '	</form>
	</div>';

	unset($_SESSION['contacto']);
}

elseif (($accion==='insertar_contacto') || ($accion==='actualizar_contacto')) {

	if (validaAcceso('1105000', $dbpfx) == '1') {
		$mensaje = 'Acceso autorizado';
	} else {
			$_SESSION['msjerror'] = $lang['acceso_error'];
		 redirigir('almacen.php');
	}
	//s$contacto_proveedor = $contacto['contacto_proveedor'];
	unset($_SESSION['contacto']);
	$_SESSION['contacto'] = array();
	$contacto_nombre=preparar_entrada_bd($contacto_nombre); $_SESSION['contacto']['nombre'] = $contacto_nombre;
	$contacto_puesto=preparar_entrada_bd($contacto_puesto); $_SESSION['contacto']['puesto'] = $contacto_puesto;
	$contacto_cbancaria=preparar_entrada_bd($contacto_cbancaria); $_SESSION['contacto']['cbancaria'] = $contacto_cbancaria;
	$contacto_CLABE=preparar_entrada_bd($contacto_CLABE); $_SESSION['contacto']['CLABE'] = $contacto_CLABE;
	$contacto_telefono=preparar_entrada_bd($contacto_telefono); $_SESSION['contacto']['telefono'] = $contacto_telefono;
	$contacto_movil=preparar_entrada_bd($contacto_movil); $_SESSION['contacto']['movil'] = $contacto_movil;
	$contacto_email=preparar_entrada_bd($contacto_email); $_SESSION['contacto']['email'] = $contacto_email;
	$contacto_proveedor=preparar_entrada_bd($contacto_proveedor); $_SESSION['contacto']['proveedor'] = $contacto_proveedor;
	$contacto_activo=preparar_entrada_bd($contacto_activo); $_SESSION['contacto']['activo'] = $contacto_activo;
	$contacto_notas=preparar_entrada_bd($contacto_notas); $_SESSION['contacto']['notas'] = $contacto_notas;


//	print_r($_SESSION['prov']);

	$error = 'no';
	$mensaje= '';
//	echo '<br><br>====== ' . $error . ' <<>> ' . $mensaje . ' ======<br><br>';
//	echo strlen($telefono1);


//	echo '<br><br>====== ' . $error . ' ======<br><br>';
	if ($error === 'no') {
		if($accion==='actualizar_contacto'){$parametros='contacto_id = ' . $contacto_id;} else {$parametros='';}
		$sql_data_array = array(
			'contacto_nombre' => $contacto_nombre,
			'contacto_puesto' => $contacto_puesto,
			'contacto_cbancaria' => $contacto_cbancaria,
			'contacto_CLABE' => $contacto_CLABE,
			'contacto_telefono' => $contacto_telefono,
			'contacto_movil' => $contacto_movil,
			'contacto_email' => $contacto_email,
			'contacto_activo' => $contacto_activo,
			'contacto_notas' => $contacto_notas,
			'contacto_proveedor' => $contacto_proveedor);

		if ($accion==='insertar_contacto') {
			$contacto_id = ejecutar_db($dbpfx . 'prov_contactos', $sql_data_array, 'insertar', $parametros);

		} else {
			echo $_SESSION['contacto']['nombre']. '<br>';
			echo $contacto_nombre . '<br>';

			foreach ($_SESSION['contacto'] as $key => $value) {
				echo 'key: ' .$key. ' value: ' .$value. '<br>';

			}
			ejecutar_db($dbpfx . 'prov_contactos', $sql_data_array, 'actualizar', $parametros);

		}
		bitacora('999999992', 'Se acaba de ' . $accion . ' ' . $contacto_id . ' para el proveedor ' . $contacto_proveedor, $dbpfx);
		unset($_SESSION['contacto']);
		redirigir('proveedores.php?accion=ver_contacto&contacto_id=' . $contacto_id);
	} else {
		$_SESSION['msjerror'] = $mensaje;
		if ($accion==='insertar_contacto') {
			redirigir('proveedores.php?accion=crear_contacto');
		} else {
			redirigir('proveedores.php?accion=modificar_contacto&contacto_id=' . $contacto_id);
		}
	}
}

elseif ($accion==="ver_contacto") {

//	echo 'Estamos en la sección  consulta de contacto';

	if (validaAcceso('1105010', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol13']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1'))) {
			$mensaje = 'Acceso autorizado';
			include('parciales/encabezado.php');
			echo '	<div id="body">';
			include('parciales/menu_inicio.php');
			echo '		<div id="principal">';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

					$preg6 = "SELECT * FROM " . $dbpfx . "prov_contactos WHERE contacto_id = '" . $_GET['contacto_id'] . "' ORDER BY contacto_nombre";
					$matr6 = mysql_query($preg6) or die("ERROR: Fallo seleccion!");
					$res6 = mysql_fetch_assoc($matr6);

					$preg7 = "SELECT * FROM " . $dbpfx . "proveedores WHERE prov_id = '" . $res6['contacto_proveedor'] . "'";
					$matr7 = mysql_query($preg7) or die("ERROR: Fallo seleccion!");
					$res7 = mysql_fetch_assoc($matr7);



			echo '
						<div class="page-content">

							<div class="row"> <!-box header del título. -->
								<div class="col-md-12">
									<div class="content-box-header">
										<div class="panel-title">
											<h2>Datos del Contacto: <u>' . $res6['contacto_nombre'] . '</u> del proveedor: <u>' . $res7['prov_razon_social'] . '</u></h2>
										</div>
									</div>
								</div>
							</div>
							<br>'."\n";

			echo '	<div class="row">
						<div class="col-md-4 panel-body shadow-box">
							<div class="form-group">
								<div class="col-md-12">
									<table>
										<tbody>
											<tr>
												<td class="obscuro" style="text-align: right;">
													<big><b>Nombre</b></big></td>
												<td class="claro"><big>' . $res6['contacto_nombre'] . '</big></td>
											</tr>
											<tr>
												<td class="obscuro" style="text-align: right;">
													<big><b>Puesto</b></big></td>
												<td class="claro"><big>' . $res6['contacto_puesto'] . '</big></td>
											</tr>
											<tr>
												<td class="obscuro" style="text-align: right;">
													<big><b>Teléfono</b></big></td>
												<td class="claro"><big>' . $res6['contacto_telefono'] . '</big></td>
											</tr>
											<tr>
												<td class="obscuro" style="text-align: right;">
													<big><b>Movil</b></big></td>
												<td class="claro"><big>' . $res6['contacto_movil'] . '</big></td>
											</tr>
											<tr>
												<td  class="obscuro" style="text-align: right;">
													<big><b>Email</b></big></td>
												<td class="claro"><big>' . $res6['contacto_email'] . '</big></td>

											</tr>
												<td class="obscuro" style="text-align: right;">
													<big><b>Cuenta Bancaria</b></big>
												</td>

												<td class="claro"><big>' . $res6['contacto_cbancaria'] . '</big></td>
											</tr>
											<tr>
												<td class="obscuro" style="text-align: right;">
													<big><b>Clabe Interbancaria</b></big>
												</td>
												<td class="claro"><big>' . $res6['contacto_CLABE'] . '</big></td>
											</tr>
											<tr>
												<td class="obscuro" style="text-align: right;">
													<big><b>Proveedor</b></big>
												</td>
												<td class="claro"><big>' . $res7['prov_razon_social'] . '</big></td>
											</tr>
											<tr>
												<td class="obscuro" style="text-align: right;">
													<big><b>Estado</b></big>
												</td>
												<td class="claro"><big>'; if($res6['contacto_activo'] == 1){ echo 'Activo'; }else { echo 'Desactivado'; } echo '</big></td>
											</tr>
											<tr>
												<td colspan="2" class="obscuro" style="text-align: center"><big><b>Anotaciones</b></big></td>
											</tr>';

						echo '				<tr>
												<td colspan="2" class="claro"><big>' . nl2br($res6['contacto_notas']) . '</big></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div><p><p>'."\n";



			echo '	<a href="proveedores.php?accion=modificar_contacto&contacto_id=' . $res6['contacto_id'] . '">
						<button type="button" class="btn btn-success"><b>Modificar <br> Datos</b></button>
					</a>
					<a href="proveedores.php?accion=consultar&prov_id=' . $res7['prov_id'] . '">
						<button type="button" class="btn btn-primary"><b>Ver <br> Proveedor</b></button>
					</a>
					<a href="proveedores.php?accion=contactos&prov_id=' . $res7['prov_id'] . '&prov_razon_social=' . $res7['prov_razon_social'] . '">
						<button type="button" class="btn btn-warning"><b>Ver Contactos <br> Proveedor</b></button>
					</a>'."\n";





}

elseif ($accion==="listar") {

//	echo 'Estamos en la sección listar. Cliente: ' . $cliente_id . ' Vehiculo: ' . $vehiculo_id;

	if (validaAcceso('1105015', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol13']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1'))) {
		$mensaje = 'Acceso autorizado';
		include('idiomas/' . $idioma . '/proveedores.php');
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	echo '
	<div class="page-content">'."\n";

	$error = 'no'; $mensaje ='';

	if($estatus_prov == ''){
		$estatus_prov = '1'; // --- Activo ---
	}


	$pregunta = "SELECT * FROM " . $dbpfx . "proveedores WHERE prov_activo = '" . $estatus_prov . "' ORDER BY prov_nic";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	$num_cols = mysql_num_rows($matriz);

	echo '

		<div class="row"> <!-box header del título. -->
			<div class="col-sm-12">
				<div class="content-box-header">
					<div class="panel-title">
						<h2>Lista de Proveedores de ' . $nombre_agencia . '</h2>
					</div>
				</div>
			</div>
		</div>
		<br>

	<!---- Filros de búsqueda ---->
	<form action="proveedores.php?accion=listar" method="post" enctype="multipart/form-data" name="procesar_comisiones">
		<div class="row">
			<div class="col-sm-12 panel-body">
				<div class="col-sm-2">
					<STRONG>Estatus del proveedor:</STRONG>
					<select name="estatus_prov" class="form-control">
						<option value="1"';
		if($estatus_prov == '1' || $estatus_prov == '' ) { echo ' selected '; }
		echo '>Activo</option>
						<option value="0"';
		if($estatus_prov == '0') { echo ' selected '; }
		echo '>Inactivo</option>
						<option value="2"';
		if($estatus_prov == '2') { echo ' selected '; }
		echo '>Bloqueado</option>
					</select>
				</div>
				<div class="col-sm-2">
					<input class="btn btn-success" type="submit" value="Consultar"/>
				</div>
			</div>
		</div>
	</form>
		<div class="row">
			<div class="col-sm-12 ">
				<div class="col-sm-12">
					<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr>
								<th><big>Número</big></th>
								<th><big>Apodo</big></th>
								<th><big>Nombre</big></th>
								<th><big>Teléfono</big></th>
								<th><big>Email</big></th>
								<th><big>¿Enviar<br>Pedidos?</big></th>
								<th><big>Pedidos</big></th>
							</tr>'."\n";

		$j=0;
		$clase = 'claro';
		while ($prov = mysql_fetch_array($matriz)) {

			echo '
							<tr class="' . $clase . '">
								<td>
									<big><a href="proveedores.php?accion=consultar&prov_id=' . $prov['prov_id'] . '">' . $prov['prov_id'] . '</a></big>
								</td>
								<td style="text-align: left !important;">
									<big>' . $prov['prov_nic'] . '</big>
								</td>
								<td style="text-align: left !important;">
									<big>' . $prov['prov_razon_social'] . '</big>
								</td>
								<td style="text-align: left !important;">
									<big>' . $prov['prov_telefono1'] . '</big>
								</td>
								<td style="text-align: left !important;">
									<big>' . $prov['prov_email'] . '</big>
								</td>
								<td style="text-align:center;">
									<big>';
			if($prov['prov_env_ped']== '1') { echo "Sí";} else {echo "No";}
			echo '
									</big>
								</td>
								<td style="text-align:center;">
									<big><a href="proveedores.php?accion=pedidos&prov_id=' . $prov['prov_id'] . '">Ver...</a></big>
								</td>
							</tr>'."\n";

			if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
			$j++;
			if($j==2) {$j=0;}
		}
		echo '
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>';


}

elseif ($accion==="pedidos") {

//	echo 'Estamos en la sección listar pedidos de proveedores

	if (validaAcceso('1105020', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol13']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1'))) {
		$mensaje = 'Acceso autorizado';
		include('idiomas/' . $idioma . '/proveedores.php');

		if($export == 1){ // ---- Hoja de calculo ----

		} else{ // ---- HTML ----
			include('parciales/encabezado.php');
			echo '
			<div id="body">';
			include('parciales/menu_inicio.php');
			echo '
				<div id="principal">';
		}
	} else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	if(isset($feini) && $feini != '0000-00-00') {
		$feini = date('Y-m-d 00:00:00', strtotime($feini));
		$t_ini = strftime('%e de %B del %Y', strtotime($feini));
		if(!isset($fefin) || $fefin == '' || $fefin == '0000-00-00') {
			$fefin = date('Y-m-d 23:59:59', time());
		}
		$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
		$t_fin = strftime('%e de %B del %Y', strtotime($fefin));
	} else {
		$feini = date('Y-m-01 00:00:00');
		$fefin = date('Y-m-t 23:59:59');
		$t_ini = strftime('%e de %B del %Y', strtotime($feini));
		$t_fin = strftime('%e de %B del %Y', strtotime($fefin));
	}

	if($export == 1){ // ---- Hoja de calculo ----

	} else{ // ---- HTML ----
		require_once("calendar/tc_calendar.php");
	}

	// --- consultar proveedor ---
	$consulta = "SELECT prov_razon_social, prov_iva prov FROM " . $dbpfx . "proveedores WHERE prov_id = '$prov_id'";
	$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
	$miprov = mysql_fetch_array($arreglo);

	// --- Consultar el número total de pedidos ----
	$preg_total_ped = "SELECT * FROM " . $dbpfx . "pedidos WHERE prov_id = '$prov_id' ";
	$mtr_total_ped = mysql_query($preg_total_ped) or die("ERROR: " . $preg_total_ped);
	$total_pedidos = mysql_num_rows($mtr_total_ped);

	// --- Consultar pedidos pagados y por pagar
	$preg0 = "SELECT COUNT(pedido_id), pedido_pagado FROM " . $dbpfx . "pedidos WHERE prov_id = '" . $prov_id . "' AND pedido_estatus != 90 AND subtotal > '0' GROUP BY pedido_pagado ORDER BY pedido_pagado";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de pedidos!");
	$p0=0; $p1=0;
	while($cua = mysql_fetch_array($matr0)) {
		if($cua['pedido_pagado'] == '0') { $p0 = $cua['COUNT(pedido_id)']; }
		if($cua['pedido_pagado'] == '1') { $p1 = $cua['COUNT(pedido_id)']; }
	}

	// ---- Consultar pagos huerfanos ----
	$preg0 = "SELECT monto FROM " . $dbpfx . "pagos_facturas WHERE pedido_id IS NULL AND proveedor_id = '" . $prov_id . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de pedidos pagos!");
	while($pag = mysql_fetch_array($matr0)){
		$pagado_huerfano = $pagado_huerfano + $pag['monto'];
	}

	// ---- CONSULTAR PEDIDOS CANCELADOS ----
	$preg_cancelados = "SELECT * FROM " . $dbpfx . "pedidos WHERE prov_id = '$prov_id' AND pedido_estatus = '90'";
	$mtr_cancelados = mysql_query($preg_cancelados) or die("ERROR: " . $preg_cancelados);
	$total_cancelados = mysql_num_rows($mtr_cancelados);

	if($export == 1){ // ---- Hoja de calculo ----

	} else{ // ---- HTML ----

		echo '
	<div class="page-content">
		<div class="row"> <!-box header del título. -->
			<div class="col-md-12">
				<div class="content-box-header">
					<div class="panel-title">
						<h2>Lista de ';

		if($pendiente == '1') { echo 'pedidos pendientes de pago '; }
		elseif($pendiente == '2') { echo 'pedidos pagados '; }
		else { echo 'todos los pedidos '; }

		echo 'del proveedor: ' . $miprov['prov_razon_social'];
		echo '
						con ' . $total_pedidos . ' pedidios
						</h2>
					</div>
				</div>
			</div>
		</div>
		<br>

		<div class="row">'."\n";
	}

	// ----- Módulo de Estadisticas ------
	// --- Consultar el primer PEDIDO ----
	$preg_primer_ped = "SELECT * FROM " . $dbpfx . "pedidos WHERE prov_id = '$prov_id' LIMIT 1";
	$mtr_primer_ped = mysql_query($preg_primer_ped) or die("ERROR: " . $preg_primer_ped);
	$primer_ped = mysql_fetch_assoc($mtr_primer_ped);

	// --- Consultar el Ultimo PEDIDO ----
	$preg_ultimo_ped = "SELECT * FROM " . $dbpfx . "pedidos WHERE prov_id = '$prov_id' ORDER BY pedido_id DESC LIMIT 1";
	$mtr_ultimo_ped = mysql_query($preg_ultimo_ped) or die("ERROR: " . $preg_ultimo_ped);
	$ultimo_ped = mysql_fetch_assoc($mtr_ultimo_ped);

	$anio_inicio = date("Y", strtotime($primer_ped['fecha_pedido']));
	$anio_fin = date("Y", strtotime($ultimo_ped['fecha_pedido']));

	$mes = 1;
	if($anio == ''){ $anio = date('Y'); }

	while($mes <= 12){

		//echo 'Mes ' . $mes . '<br>';
		// --- Consultar los pedidos hechos al proveedor en el mes y en el año en curso ----
		$preg_mes = "SELECT * FROM " . $dbpfx . "pedidos where month(fecha_pedido) = '" . $mes . "' and year(fecha_pedido) = '" . $anio . "' AND prov_id = '" . $prov_id . "'";
		$mtr_mes = mysql_query($preg_mes) or die ("ERROR: " . $preg_mes);

		// --- Recolectar los datos para las estadisticas mensuales ----
		$cancelados = 0;
		$rec_y_pagado = 0;
		$activos = 0;
		$monto_pedidos = 0;
		$yapagado = 0;
		while($info_mes = mysql_fetch_array($mtr_mes)){

			//echo 'pedido ' . $info_mes['pedido_id'] . '<br>';
			if($info_mes['pedido_estatus'] == 90){ // ---- cancelado ---
				$cancelados = $cancelados + 1;
			}
			elseif($info_mes['pedido_estatus'] == 99){ // ---- cancelado ---
				$rec_y_pagado = $rec_y_pagado + 1;
			}

			// --- Sumar costos ---
			if($info_mes['pedido_estatus'] != 90){
				$activos = $activos +1;
				$monto_pedidos = $monto_pedidos + $info_mes['subtotal'] + $info_mes['impuesto'];
			}

			// --- Sumar pagos ---
			$pagado = 0;
			$monto = $info_mes['subtotal'] + $info_mes['impuesto'];
			if($monto > 0) {
				$preg0 = "SELECT monto FROM " . $dbpfx . "pagos_facturas WHERE pedido_id = '" . $info_mes['pedido_id'] . "' AND proveedor_id = '" . $prov_id . "'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de pedidos pagos!");
				while($pag = mysql_fetch_array($matr0)){
					$pagado = $pagado + $pag['monto'];
				}
			}
			$yapagado = $yapagado + $pagado;
		}

		$mes_array[$mes]['cancelados'] = $cancelados;
		$mes_array[$mes]['activos'] = $activos;
		$mes_array[$mes]['total_pedidos'] = $activos + $cancelados;
		$mes_array[$mes]['total'] = $monto_pedidos;
		$mes_array[$mes]['pagado'] = $yapagado;
		//echo $preg_mes . '<br>';

		$mes++;
	}

	if($export == 1){ // ---- Hoja de calculo ----

	} else{ // ---- HTML ----
		echo '
			<div class="col-md-12">
				<div class="col-sm-4 padding">
					<table cellspacing="0" class="shadow-box">
						<tr class="obscuro">
							<th colspan="6">
								Resumen del año ' . $anio . '
							</th>
						</tr>
						<tr class="obscuro">
							<th class="der">
								AÑO
								<form action="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '" method="post" enctype="multipart/form-data" name="año">
								<select name="anio" class="form-control" size="1" onchange="document.año.submit()";>
									<option value="0"';
						if($anio == ''){echo ' selected ';}
							echo '>Seleccione</option>'."\n";

						if($anio > $anio_fin){
							echo '
									<option value="' .  $anio . '" selected>
										' . $anio . '
									</option>'."\n";

						}

						// --- Recorres los años que serán mostrados en el select ---
						while($anio_inicio <= $anio_fin){

							echo '
							<option value="' . $anio_inicio . '"';
							if($anio == $anio_inicio) { echo ' selected '; }
							echo '>' . $anio_inicio . '</option>'."\n";
							$anio_inicio++;

						}
		echo '
								</form>
							</th>
							<th>PEDIDOS<br>ACTIVOS</th>
							<th>CANCELADOS</th>
							<th>TOTAL</th>
							<th>$</th>
							<th>PAGADO</th>
						</tr>'."\n";

		$clase = 'claro';
		$total_montos = 0;
		$total_num_ped = 0;
		$total_num_ped_can = 0;
		$mes_cont = 1;
		foreach($meses_anio as $key => $val){

			// ---- construir fechas de links de mes ----
			$mes_link = '';
			if($key <= 9){ $mes_link = 0 . $key;} else{ $mes_link = $key; }
			$f_ini = $anio . '-' . $mes_link . '-' . '01';
			$f_fin = date('Y-m-t 23:59:59' , strtotime($f_ini));

			if($mes_cont == 1){
				$f_ini_total = $f_ini;
			}
			if($mes_cont == 12){
				$f_fin_total = $f_fin;
			}

			echo '
							<tr class="' . $clase . '">
								<td class="der">
									<small><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&feini=' . $f_ini . '&fefin=' . $f_fin . '&anio=' . $anio . '">'. $val . '</a></b></small>
								</td>
								<td align="center">
									'."\n";

									if($mes_array[$key]['activos'] != ''){
										echo '<small><span class="primary">' . $mes_array[$key]['activos'] . '</span></small>'."\n";
									}
			echo '

								</td>
								<td align="center">
									'."\n";

									if($mes_array[$key]['cancelados'] != ''){
										echo '<small><span class="danger">' . $mes_array[$key]['cancelados'] . '</span></small>'."\n";
									}
			echo '

								</td>
								<td align="center">
								'."\n";

									if($mes_array[$key]['total_pedidos'] != ''){
										echo '<small><span class="success">' . $mes_array[$key]['total_pedidos'] . '</span></small>'."\n";
									}
			echo '

								</td>
								<td align="center">
									<small><b>$'. number_format($mes_array[$key]['total']) . '</b></small>
								</td>
								<td align="center">
									<small><b>$'. number_format($mes_array[$key]['pagado']) . '</b></small>
								</td>
							</tr>'."\n";

			if($clase == 'claro'){ $clase = 'obscuro'; } else{ $clase = 'claro'; }
			$mes_cont++;
			// --- Sumar totales -----
			$total_montos = $total_montos + $mes_array[$key]['total'];
			$total_num_ped = $total_num_ped + $mes_array[$key]['total_pedidos'];
			$total_activos = $total_activos + $mes_array[$key]['activos'];
			$total_num_ped_can = $total_num_ped_can + $mes_array[$key]['cancelados'];
			$total_pagado_anual = $total_pagado_anual + $mes_array[$key]['pagado'];

		}

		echo '					<tr class="' . $clase . '">
								<td class="der">
									<small><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&feini=' . $f_ini_total . '&fefin=' . $f_fin_total . '&anio=' . $anio . '">TOTAL</a></b></small>
								</td>
								<td align="center">
									<small><span class="success">' . $total_activos . '</span></small>
								</td>
								<td align="center">
									<small><span class="success">' . $total_num_ped_can . '</span></small>
								</td>
								<td align="center">
									<small><span class="success">' . $total_num_ped . '</span></small>
								</td>
								<td align="center">
									<small><b>$'. number_format($total_montos) . '</b></small>
								</td>
								<td align="center">
									<small><b>$'. number_format($total_pagado_anual) . '</b></small>
								</td>
							<tr>
					</table>
				</div>'."\n";

		echo '
				<form action="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '" method="post" enctype="multipart/form-data" name="consultar pagos">
				<div class="col-sm-2 padding">
					<big><b>FILTRA PEDIDIOS POR FECHA Y TIPO</b></big><br><br>
					<big><b>Fecha de inicio:</b></big><br>'."\n";
					//instantiate class and set properties
					$myCalendar = new tc_calendar("feini", true);
					$myCalendar->setPath("calendar/");
					$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
					$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
					$myCalendar->setYearInterval(2011, 2020);
					$myCalendar->setAutoHide(true, 5000);
					//output the calendar
					$myCalendar->writeScript();

		echo '
					<br><br>
					<big><b>Fecha de fin:</b></big><br>'."\n";
					//instantiate class and set properties
					$myCalendar = new tc_calendar("fefin", true);
					$myCalendar->setPath("calendar/");
					$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
					$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
					$myCalendar->setYearInterval(2011, 2020);
					$myCalendar->setAutoHide(true, 5000);
					//output the calendar
					$myCalendar->writeScript();
		echo '
					<br><br>
					<big><b>Tipo de pedido:</b></big><br>
					<select name="pendiente" size="1">
						<option value="0"';
						if($pendiente == 0 || $pendiente == '') { echo ' selected '; }
						echo '>Todos</option>
						<option value="2"';
						if($pendiente == 2) { echo ' selected '; }
						echo '>Pagados</option>
						<option value="1"';
						if($pendiente == 1) { echo ' selected '; }
						echo '>Por pagar</option>
					</select>
					<br><br>
					<input type="submit" class="btn btn-success" value="Consultar">
				</div>
				</form>
				<div class="col-sm-4 padding">
					<table cellspacing="0" class="shadow-box">
						<tr class="obscuro">
							<td class="der">
								<big><b>POR PAGAR:</b></big>
							</td>
							<td align="center" colspan="2">
								<big><span class="primary">' . $p0 . '</span></big>
							</td>
						</tr>
						<tr class="claro">
							<td class="der">
								<big><b>PAGADOS:</b></big>
							</td>
							<td align="center" colspan="20">
								<big><span class="danger">' . $p1 . '</span></big>
							</td>
						</tr>
						<tr class="claro">
							<td class="der">
								<big><b>CANCELADOS:</b></big>
							</td>
							<td align="center" colspan="20">
								<big><span class="grey">' . $total_cancelados . '</span></big>
							</td>
						</tr>
					</table>
					<br><br>
					<h2>Saldo a favor $' . number_format($pagado_huerfano, 2) . '</h2>
					<a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&orden=' . $orden . '&export=1">
						<img src="idiomas/' . $idioma . '/imagenes/hoja-calculo.png" alt="Exportar" border="0">
					</a>

				</div>
			</div>
		</div>'."\n";

		if($por == ''){ // --- Si no está colocado el orden, se muestra le orden dfault "Pedidos"
			$por = 'Pedido';
			$orden = 'pedido_id';
		}

		echo '
		<div class="row">
			<div class="col-md-12">
				<div class="content-box-header">
					<div class="panel-title">
						<b><big>Ordenado por: ' .  $por . ' de ' . date('d-m-y', strtotime($feini)) . ' a ' . date('d-m-y', strtotime($fefin)) . '</big></b>
					</div>
				</div>
			</div>
		</div>'."\n";
	}

	$error = 'no'; $mensaje ='';
	$pregunta = "SELECT * FROM " . $dbpfx . "pedidos WHERE prov_id = '$prov_id' AND fecha_pedido >= '" . $feini . "' AND fecha_pedido <= '" . $fefin . "'";
	if($pendiente == '1') { $pregunta .= " AND pedido_pagado = 0"; }
	elseif($pendiente == '2') { $pregunta .= " AND pedido_pagado = 1"; }
	$pregunta .= " ORDER BY pedido_id";

	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
	$num_cols = mysql_num_rows($matriz);

	if ($num_cols>0) {

		$j=0; $sum_total =0; $sum_pagado =0; $sum_pendiente = 0; $contador = 0;
		$clase = 'claro';
		$hoy = date('Y-m-d');

		while($ped = mysql_fetch_array($matriz)){

			if($ped['pedido_estatus'] != 90){
				// ---- Calcular pagos ----
				$pagado = 0; $yapagado = 0; $fpago = '';
				$monto = $ped['subtotal'] + $ped['impuesto'];
				if($monto > 0) {
					$preg0 = "SELECT monto FROM " . $dbpfx . "pagos_facturas WHERE pedido_id = '" . $ped['pedido_id'] . "' AND proveedor_id = '" . $prov_id . "'";
					$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de pedidos pagos!");
					while($pag = mysql_fetch_array($matr0)){
						$pagado = $pagado + $pag['monto'];
					}
				}
				//echo $preg0 . '<br>';
				$yapagado = $pagado;
				$ppagar = $monto - $yapagado;

				// ----- Calcular fecha promesa de entrega ------
				$preg_fecha_promesa = "SELECT op_id FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $ped['pedido_id'] . "'";
				$matr_fecha_promesa = mysql_query($preg_fecha_promesa) or die("ERROR: Fallo selección de orden productos! " . $preg_fecha_promesa);

				unset($array_fech_promesa);
				while($ops = mysql_fetch_array($matr_fecha_promesa)){
					// ---- Consultar la cotización del proveedor ----op_id
					$preg_coti = "SELECT dias_entrega FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $ops['op_id'] . "' AND prod_prov_id = '" . $prov_id . "'";
					$matr_coti = mysql_query($preg_coti) or die("ERROR: Fallo! " . $preg_coti);
					$cot = mysql_fetch_assoc($matr_coti);

					// --- Convertir dias a segundos ---
					$segundos = $cot['dias_entrega'] * 86400;
					$fecha_promesa = strtotime($ped['fecha_pedido']);
					$fecha_promesa = $fecha_promesa + $segundos;
					$array_fech_promesa[] = date('Y-m-d', $fecha_promesa);

				}
				// --- Calcular cual es la fecha promesa mayor ---
				$fecha_prom =  max($array_fech_promesa);

				// --- calcular fecha de recibido ---
				$dias = '';
				$color_fecha_rec = '';
				if($ped['pedido_estatus'] >= 10){ // --- si el pedido ya fue recibido ---
					$fecha_recibido = date('Y-m-d', strtotime($ped['fecha_recibido']));
					// --- Calcular días a favor o en contra ---
					if($fecha_recibido > $fecha_prom){ // --- Días en contra ---
						// echo 'DIAS EN CONTRA <br>';
						/*
						// --- encontar diferencia modo nuevo ---
						// --- Restar días ---
						$fecha1 = new DateTime($fecha_recibido);
						$fecha2 = new DateTime($fecha_prom);
						$diferencia = $fecha1->diff($fecha2);
						//$dias = '<span class="danger">-' . $diferencia->days . '</span>';
						$dias = $diferencia->days;
						$dias = $dias * -1;
						*/

						// --- encontar diferencia modo clasico ---
						$dif = strtotime($fecha_prom) - strtotime($fecha_recibido);
						$dif = $dif / 86400;
						$dif =round($dif);

					} else{ // --- A tiempo o días a favor ---
						/*
						// --- encontar diferencia modo nuevo ---
						// --- Restar días ---
						$fecha1 = new DateTime($fecha_recibido);
						$fecha2 = new DateTime($fecha_prom);
						$diferencia = $fecha1->diff($fecha2);
						//$dias = '<span class="success">' . $diferencia->days . '</span>';
						$dias = $diferencia->days;
						*/

						// --- encontar diferencia modo clasico ---
						$dif = strtotime($fecha_prom) - strtotime($fecha_recibido);
						$dif = $dif / 86400;
						$dif =round($dif);
					}

				} else{
					$fecha_recibido = '';
				}

			}


			if($monto > 0) {

				// ---- Almacenamos en array los resultados ----
				$resultados[$contador]['pedido_id'] = $ped['pedido_id'];
				$resultados[$contador]['orden_id'] = $ped['orden_id'];

				if($ped['pedido_estatus'] != 90){

					if($ped['pedido_estatus'] >= 10){
						$recibido = strtotime($fecha_recibido);
					} else{
						$recibido = '';
						$dif = 0;
					}

					// ---- array para ordenar los días ----
					$array_dias[$contador]['dias'] = $dif;

					// ---- Almacenamos en array los resultados ----
					$resultados[$contador]['subtotal'] = $ped['subtotal'];
					$resultados[$contador]['impuesto'] = $ped['impuesto'];
					$resultados[$contador]['monto'] = $monto;
					$resultados[$contador]['yapagado'] = $yapagado;
					$resultados[$contador]['ppagar'] = $ppagar;
					$resultados[$contador]['fecha_pedido'] = $ped['fecha_pedido'];
					$resultados[$contador]['fecha_prom'] = $fecha_prom;
					$resultados[$contador]['fecha_recibido'] = $recibido;
					$resultados[$contador]['dias'] = $dif;
					$resultados[$contador]['estatus'] = $ped['pedido_estatus'];

				} else{

					// ------ Almacenar en array -------
					$resultados[$contador]['fecha_recibido'] = 'CANCELADO';
					$resultados[$contador]['subtotal'] = '';
					$resultados[$contador]['impuesto'] = '';
					$resultados[$contador]['monto'] = '';
					$resultados[$contador]['yapagado'] = '';
					$resultados[$contador]['ppagar'] = '';
					$resultados[$contador]['fecha_pedido'] = $ped['fecha_pedido'];
					$resultados[$contador]['fecha_prom'] = '';
					$resultados[$contador]['dias'] = '';
					$resultados[$contador]['estatus'] = $ped['pedido_estatus'];
				}

				if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
				$j++; $contador++;
				if($j==2) {$j=0;}
			}
		}

		// --- Ordenar array  de resultados de acuerdo al filtro seleccionado ----


		if($orden == 'dias'){

			asort($array_dias);

		} else{
			//$orden = 'orden_id';
			function sort_by($clave) { // --- funcion para ordenar array por parametro enviado ---
				return function ($a, $b) use ($clave) {
					return strnatcmp($a[$clave], $b[$clave]);
			};
			}
			usort($resultados, sort_by($orden));

		}

		if($export == 1){ // ---- Hoja de calculo ----

			// -------------------   Creación de Archivo Excel   ---------------------------
			$celda = 'A1';
			$titulo = 'Listado de pedidos ' . $miprov['prov_razon_social'];

			require_once ('Classes/PHPExcel.php');
			$objReader = PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load("parciales/export.xls");
			$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
						->setTitle("Listado de pedidos")
						->setKeywords("AUTOSHOP EASY");

			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($celda, $titulo);

			// ------ ENCABEZADOS ---
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue("A4", "Pedido")
						->setCellValue("B4", "OT")
						->setCellValue("C4", "Monto")
						->setCellValue("D4", "Impuesto")
						->setCellValue("E4", "Total")
						->setCellValue("F4", "Pagado")
						->setCellValue("G4", "Por Pagar")
						->setCellValue("H4", "Fecha de Pedido")
						->setCellValue("I4", "Fecha promesa")
						->setCellValue("J4", "Fecha recibido")
						->setCellValue("K4", "Días Favor / Contra");

			$z= 5;


		} else{ // ---- HTML ----

			// ---- Pintar encabezado de la tabla -----
			echo '
		<div class="row">
			<div class="col-md-12">
					<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr>
								<th><big><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=pedido_id&por=Pedido">Pedido</a></b></big></th>
								<th><big><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=orden_id&por=OT">OT</a></b></big></th>
								<th><big><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=subtotal&por=Monto">Monto</a></b></big></th>
								<th><big><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=impuesto&por=Impuesto">Impuesto</a></b></big></th>
								<th><big><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=monto&por=Total">Total</a></b></big></th>
								<th><big><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=yapagado&por=Pagado">Pagado</a><b></b></big></th>
								<th><big><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=ppagar&por=Por Pagar">Por Pagar</a></b></big></th>
								<th><big><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=fecha_pedido&por=Fecha de Pedido">Fecha de Pedido</a></b></big></th>
								<th><big><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=fecha_prom&por=Fecha promesa">Fecha promesa</a></b></big></th>
								<th><big><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=fecha_recibido&por=Fecha recibido">Fecha recibido</a></b></big></th>
								<th><big><b><a href="proveedores.php?accion=pedidos&prov_id=' . $prov_id . '&pendiente=' . $pendiente . '&feini=' . $feini . '&fefin=' . $fefin . '&anio=' . $anio . '&orden=dias&por=Días Favor/Contra">Días <br>Favor / Contra</a></b></big></th>
							</tr>'."\n";

			$j=0; $sum_total =0; $sum_pagado =0; $sum_pendiente = 0; $contador = 0; $sum_subtotal = 0; $sum_totalimpuesto = 0; $sum_total = 0;
			$clase = 'claro'; $hoy = date('Y-m-d'); $contador = 0;
		}

			// ----- Pintar contenido del array resultados -------
			if($orden == 'dias'){

				$num_cols = 0; // solo se cuentan los pedidos que no se hallan cancelado.

				foreach($array_dias as $key => $val){

					if($export == 1){ // ---- Hoja de calculo ----

						// --- Celdas a grabar ----
						$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
						$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
						$kkk = 'K'.$z; $l = 'L'.$z;

						if($val['fecha_pedido'] != ''){
							$resultados[$key]['fecha_pedido'] = date('Y-m-d', strtotime($resultados[$key]['fecha_pedido']));
							$resultados[$key]['fecha_pedido'] = PHPExcel_Shared_Date::PHPToExcel( strtotime($resultados[$key]['fecha_pedido']));

							// --- cambiar el formato de la celda tipo fecha/date ---
							$objPHPExcel->getActiveSheet()
									->getStyle($h)
										->getNumberFormat()
									->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);

						}
						if($resultados[$key]['fecha_prom'] != ''){
							$resultados[$key]['fecha_prom'] = date('Y-m-d', strtotime($resultados[$key]['fecha_prom']));
							$resultados[$key]['fecha_prom'] = PHPExcel_Shared_Date::PHPToExcel( strtotime($resultados[$key]['fecha_prom']));

							// --- cambiar el formato de la celda tipo fecha/date ---
							$objPHPExcel->getActiveSheet()
									->getStyle($i)
									->getNumberFormat()
									->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
						}
						if($resultados[$key]['fecha_recibido'] != 'CANCELADO'){

							if($resultados[$key]['fecha_recibido'] == ''){

								$resultados[$key]['fecha_recibido'] == '';

							} else{

								$resultados[$key]['fecha_recibido'] = date('Y-m-d', $resultados[$key]['fecha_recibido']);
								$resultados[$key]['fecha_recibido'] = PHPExcel_Shared_Date::PHPToExcel( strtotime($resultados[$key]['fecha_recibido']));

								// --- cambiar el formato de la celda tipo fecha/date ---
								$objPHPExcel->getActiveSheet()
											->getStyle($j)
										->getNumberFormat()
										->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
							}

						}


						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($a, $resultados[$key]['pedido_id'])
									->setCellValue($b, $resultados[$key]['orden_id'])
									->setCellValue($c, $resultados[$key]['subtotal'])
									->setCellValue($d, $resultados[$key]['impuesto'])
									->setCellValue($e, $resultados[$key]['monto'])
									->setCellValue($f, $resultados[$key]['yapagado'])
									->setCellValue($g, $resultados[$key]['ppagar'])
									->setCellValue($h, $resultados[$key]['fecha_pedido'])
									->setCellValue($i, $resultados[$key]['fecha_prom'])
									->setCellValue($j, $resultados[$key]['fecha_recibido'])
									->setCellValue($kkk, $resultados[$key]['dias']);

						$z++;

					} else{ // ---- HTML ----
						//echo 'KEY ' . $key . ' VAL ' . $val . '<br>';
						// --- Sumar totales ---
						$sum_pagado = $sum_pagado + $resultados[$key]['yapagado'];
						$sum_pendiente = $sum_pendiente +  $resultados[$key]['ppagar'];
						$sum_subtotal = $sum_subtotal + $resultados[$key]['subtotal'];
						$sum_totalimpuesto = $sum_totalimpuesto + $resultados[$key]['impuesto'];
						$sum_total = $sum_total + $resultados[$key]['monto'];

						if($resultados[$key]['fecha_recibido'] != 'CANCELADO'){ $num_cols++; }
						if($resultados[$key]['subtotal'] != ''){ $resultados[$key]['subtotal'] = number_format($resultados[$key]['subtotal'], 2); }
						if($resultados[$key]['impuesto'] != ''){ $resultados[$key]['impuesto'] = number_format($resultados[$key]['impuesto'], 2); }
						if($resultados[$key]['monto'] != ''){ $resultados[$key]['monto'] = number_format($resultados[$key]['monto'], 2); }
						if($resultados[$key]['yapagado'] != ''){ $resultados[$key]['yapagado'] = number_format($resultados[$key]['yapagado'], 2); }
						if($resultados[$key]['ppagar'] != ''){ $resultados[$key]['ppagar'] = number_format($resultados[$key]['ppagar'], 2); }
						if($resultados[$key]['fecha_pedido'] != ''){ $resultados[$key]['fecha_pedido'] = date('Y-m-d', strtotime($resultados[$key]['fecha_pedido'])); }
						if($resultados[$key]['fecha_prom'] != ''){ $resultados[$key]['fecha_prom'] = date('Y-m-d', strtotime($resultados[$key]['fecha_prom'])); }
						if($resultados[$key]['fecha_recibido'] != 'CANCELADO'){ $resultados[$key]['fecha_recibido'] = date('Y-m-d', $resultados[$key]['fecha_recibido']); }

						if($resultados[$key]['dias'] < 0){
							$style_fe_rec = 'Background-color: #d9534f; color: white;';
							$resultados[$key]['dias'] = '<span class="danger">' . $resultados[$key]['dias'] . '</span>';
						} else{
							$style_fe_rec = '';
							$resultados[$key]['dias'] = '<span class="success">' . $resultados[$key]['dias'] . '</span>';
						}

						echo '
							<tr class="' . $clase . '">
								<td>
									<big><b><a href="pedidos.php?accion=consultar&pedido=' . $val['pedido_id'] . '">' . $resultados[$key]['pedido_id'] . '</a></b></big>
								</td>
								<td>
									<big><b><a href="ordenes.php?accion=consultar&orden_id=' . $val['orden_id'] . '">' . $resultados[$key]['orden_id'] . '</a></b></big>
								</td>
								<td style="text-align:right;">
									<big><b>' . $resultados[$key]['subtotal'] . '</b></big>
								</td>
								<td style="text-align:right;">
									<big><b>' . $resultados[$key]['impuesto'] . '</b></big>
								</td>
								<td style="text-align:right;">
									<big><b>' . $resultados[$key]['monto'] . '</b></big>
								</td>
								<td style="text-align:right;">
									<big><b>' . $resultados[$key]['yapagado'] . '</b></big>
								</td>
								<td style="text-align:right;">
									<big><b>' . $resultados[$key]['ppagar'] . '</b></big>
								</td>
								<td>
									<big><b>' . $resultados[$key]['fecha_pedido'] . '</b></big>
								</td>
								<td>
									<big><b>' . $resultados[$key]['fecha_prom'] . '</b></big>
								</td>
								<td style="' . $style_fe_rec . '">
									<big><b>' . $resultados[$key]['fecha_recibido'] . '</b></big>
								</td>
								<td>
									<big><b>' . $resultados[$key]['dias'] . '</b></big>
								</td>
							</tr>'."\n";
					}

				}

			} else{

				foreach($resultados as $key => $val){

					if($export == 1){ // ---- Hoja de calculo ----

						// --- Celdas a grabar ----
						$a = 'A'.$z; $b = 'B'.$z; $c = 'C'.$z; $d = 'D'.$z; $e = 'E'.$z;
						$f = 'F'.$z; $g = 'G'.$z; $h = 'H'.$z; $i = 'I'.$z; $j = 'J'.$z;
						$kkk = 'K'.$z; $l = 'L'.$z;

						if($val['fecha_pedido'] != ''){
							$val['fecha_pedido'] = date('Y-m-d', strtotime($val['fecha_pedido']));
							$val['fecha_pedido'] = PHPExcel_Shared_Date::PHPToExcel( strtotime($val['fecha_pedido']));

							// --- cambiar el formato de la celda tipo fecha/date ---
							$objPHPExcel->getActiveSheet()
									->getStyle($h)
									->getNumberFormat()
									->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);

						}
						if($val['fecha_prom'] != ''){
							$val['fecha_prom'] = date('Y-m-d', strtotime($val['fecha_prom']));
							$val['fecha_prom'] = PHPExcel_Shared_Date::PHPToExcel( strtotime($val['fecha_prom']));

							// --- cambiar el formato de la celda tipo fecha/date ---
							$objPHPExcel->getActiveSheet()
									->getStyle($i)
									->getNumberFormat()
									->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
						}
						if($val['fecha_recibido'] != 'CANCELADO'){

							if($val['fecha_recibido'] == ''){

							} else{
								$val['fecha_recibido'] = date('Y-m-d', $val['fecha_recibido']);
								$val['fecha_recibido'] = PHPExcel_Shared_Date::PHPToExcel( strtotime($val['fecha_recibido']));

								// --- cambiar el formato de la celda tipo fecha/date ---
								$objPHPExcel->getActiveSheet()
									->getStyle($j)
									->getNumberFormat()
									->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
							}

						}


						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($a, $val['pedido_id'])
									->setCellValue($b, $val['orden_id'])
									->setCellValue($c, $val['subtotal'])
									->setCellValue($d, $val['impuesto'])
									->setCellValue($e, $val['monto'])
									->setCellValue($f, $val['yapagado'])
									->setCellValue($g, $val['ppagar'])
									->setCellValue($h, $val['fecha_prom'])
									->setCellValue($i, $val['fecha_pedido'])
									->setCellValue($j, $val['fecha_recibido'])
									->setCellValue($kkk, $val['dias']);

						$z++;

					} else{ // ---- HTML ----
						//echo 'KEY ' . $key . ' VAL ' . $val . '<br>';
						// --- Sumar totales ---
						$sum_pagado = $sum_pagado + $val['yapagado'];
						$sum_pendiente = $sum_pendiente +  $val['ppagar'];
						$sum_subtotal = $sum_subtotal + $val['subtotal'];
						$sum_totalimpuesto = $sum_totalimpuesto + $val['impuesto'];
						$sum_total = $sum_total + $val['monto'];

						if($val['subtotal'] != ''){ $val['subtotal'] = number_format($val['subtotal'], 2); }
						if($val['impuesto'] != ''){ $val['impuesto'] = number_format($val['impuesto'], 2); }
						if($val['monto'] != ''){ $val['monto'] = number_format($val['monto'], 2); }
						if($val['yapagado'] != ''){ $val['yapagado'] = number_format($val['yapagado'], 2); }
						if($val['ppagar'] != ''){ $val['ppagar'] = number_format($val['ppagar'], 2); }
						if($val['fecha_pedido'] != ''){ $val['fecha_pedido'] = date('Y-m-d', strtotime($val['fecha_pedido'])); }
						if($val['fecha_prom'] != ''){ $val['fecha_prom'] = date('Y-m-d', strtotime($val['fecha_prom'])); }
						if($val['fecha_recibido'] != 'CANCELADO'){ $val['fecha_recibido'] = date('Y-m-d', $val['fecha_recibido']); }

						if($val['dias'] < 0){
							$style_fe_rec = 'Background-color: #d9534f; color: white;';
							$val['dias'] = '<span class="danger">' . $val['dias'] . '</span>';
						} else{
							$style_fe_rec = '';
							$val['dias'] = '<span class="success">' . $val['dias'] . '</span>';
						}

						echo '
							<tr class="' . $clase . '">
								<td>
									<big><b><a href="pedidos.php?accion=consultar&pedido=' . $val['pedido_id'] . '">' . $val['pedido_id'] . '</a></b></big>
								</td>
								<td>
									<big><b><a href="ordenes.php?accion=consultar&orden_id=' . $val['orden_id'] . '">' . $val['orden_id'] . '</a></b></big>
								</td>
								<td style="text-align:right;">
									<big><b>' . $val['subtotal'] . '</b></big>
								</td>
								<td style="text-align:right;">
									<big><b>' . $val['impuesto'] . '</b></big>
								</td>
								<td style="text-align:right;">
									<big><b>' . $val['monto'] . '</b></big>
								</td>
								<td style="text-align:right;">
									<big><b>' . $val['yapagado'] . '</b></big>
								</td>
								<td style="text-align:right;">
									<big><b>' . $val['ppagar'] . '</b></big>
								</td>
								<td>
									<big><b>' . $val['fecha_pedido'] . '</b></big>
								</td>
								<td>
									<big><b>' . $val['fecha_prom'] . '</b></big>
								</td>
								<td style="' . $style_fe_rec . '">
									<big><b>' . $val['fecha_recibido'] . '</b></big>
								</td>
								<td>
									<big><b>' . $val['dias'] . '</b></big>
								</td>
							</tr>'."\n";
					}

				}
			}

			if($export == 1){ // ---- Hoja de calculo ----

				//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="Listado-de-pedidos.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				$objWriter->save('php://output');
				exit;

			} else{ // ---- HTML ----
				echo '
							<tr class="blanco">
								<td colspan="2" align="center"><big><b>Monstrando: ' . $num_cols . '</b></big></td>
								<td style="text-align:right;"><big><b>' . number_format($sum_subtotal, 2) . '</b></big></td>
								<td style="text-align:right;"><big><b>' . number_format($sum_totalimpuesto, 2) . '</b></big></td>
								<td style="text-align:right;"><big><b>' . number_format($sum_total, 2) . '</b></big></td>
								<td style="text-align:right;"><big><b>' . number_format($sum_pagado, 2) . '</b></big></td>
								<td style="text-align:right;"><big><b>' . number_format($sum_pendiente, 2) . '</b></big></td>
								<td colspan="4">&nbsp;</td>
							</tr>
						</table>
					</div>
			</div>
		</div>
		</div>'."\n";
			}

	} else {
		$mensaje ='No se encontraron proveedores con esos datos.</br>';
	}
}

elseif ($accion==="pagos") {

	if (validaAcceso('1105025', $dbpfx) == '1' || ($solovalacc != '1' && ($_SESSION['rol13']=='1' || $_SESSION['rol02']=='1'))) {
		$mensaje = 'Acceso autorizado';
	} else {
		 redirigir('usuarios.php?mensaje='.$lang['acceso_error']);
	}

	$referencia = preparar_entrada_bd($referencia);
	$numfact = preparar_entrada_bd($numfact);
	if($mes != '') {
		$periodo = $year . '-' . $mes;
	} else {
		$periodo = $year;
	}
	$periodo = preparar_entrada_bd($periodo);
	$tmes = strtotime($periodo);
	$nomprov = preparar_entrada_bd($nomprov);
	$error = 'no'; $mensaje ='';

	if ($error == 'no') {
		include('parciales/encabezado.php');
		echo '	<div id="body">';
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">';
		echo '			<form action="proveedores.php?accion=pagos" method="post" enctype="multipart/form-data">'."\n";
		echo '			<table cellspacing="2" cellpadding="2" border="1" class="izquierda" width="840">'."\n";
		echo '				<tr class="cabeza_tabla"><td colspan="3" align="left">' . $lang['Lista de Facturas'] . '</td></tr>'."\n";
		echo '				<tr><td>' . $lang['Nombre del Proveedor'] . '<br><input type="text" name="nomprov" /></td><td>' . $lang['Año (aaaa)'] . '<br><input type="text" name="year" size="5" /></td><td>' . $lang['Mes (mm)'] . '<br><input type="text" name="mes" size="3" /></td></tr>'."\n";
		echo '				<tr><td colspan="3">' . $lang['Referencia'] . '<br><input type="text" name="referencia" /></td></tr>'."\n";
		echo '				<tr><td colspan="3">' . $lang['Número de Factura'] . '<br><input type="text" name="numfact" /></td></tr>'."\n";
		echo '				<tr><td colspan="3"><button name="Consultar" value="Consultar" type="submit">' . $lang['Consultar'] . '</button></td></tr>'."\n";
		echo '			</table></form>'."\n";

		echo '			<table cellspacing="2" cellpadding="2" border="0" class="centrado">'."\n";
		echo '				<tr><td>' . $lang['Pedido'] . '</td><td>' . $lang['Factura'] . '</td><td>' . $lang['Fecha del Pago'] . '</td><td>' . $lang['Monto'] . '</td><td>' . $lang['referencia'] . '</td><td>' . $lang['Proveedor'] . '</td><td>' . $lang['OT'] . '</td><tr>'."\n";
		if($nomprov != '') {
			$preg0 = "SELECT prov_id, prov_nic, prov_razon_social FROM " . $dbpfx . "proveedores WHERE prov_activo = '1' AND (prov_nic LIKE '%" . $nomprov . "%' OR prov_razon_social LIKE '%" . $nomprov . "%')";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Proveedor!".$preg0);
		while ($prov = mysql_fetch_array($matr0)) {
			$preg1 = "SELECT pp.* FROM " . $dbpfx . "pedidos p, " . $dbpfx . "pedidos_pagos pp ";
			if($numfact != '') {
				$pregf = "SELECT * FROM " . $dbpfx . "facturas_por_pagar WHERE fact_num LIKE '%" . $numfact . "%'";
			$matrf = mysql_query($pregf) or die("ERROR: Fallo selección de Factura!".$pregf);
				$fact = mysql_fetch_array($matrf);
				$filaf = mysql_num_rows($matrf);
				if($filaf > 0) { $preg1 .= ", " . $dbpfx . "facturas_por_pagar f "; }
			}
			$preg1 .= " WHERE p.prov_id = '" . $prov['prov_id'] . "' AND pp.pedido_id = p.pedido_id ";
			if($tmes > 1357020000) { $preg1 .= " AND pp.pago_fecha LIKE '%" . $periodo . "%' "; }
			if($referencia != '') { $preg1 .= " AND pp.pago_referencia LIKE '%" . $referencia . "%' "; }
			if($filaf > 0) { $preg1 .= " AND pp.fact_id = f.fact_id "; }
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion de!".$preg1);
				$j = 'claro';
				while ($pag = mysql_fetch_array($matr1)) {
					echo '				<tr class="' . $j . '">';
					echo '					<td><a href="pedidos.php?accion=consultar&pedido=' . $pag['pedido_id'] . '">' . $pag['pedido_id'] . '</a></td>'."\n";
					$preg2 = "SELECT * FROM " . $dbpfx . "facturas_por_pagar WHERE fact_id = '" . $pag['fact_id'] . "'";
					$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de facturas!".$preg2);
					$fact = mysql_fetch_array($matr2);
					echo '					<td>' . $fact['fact_num'] . '</td><td>' . date('Y-m-d', strtotime($pag['pago_fecha'])) . '</td><td>' . number_format($pag['pago_monto'],2) . '</td><td>' . $pag['pago_referencia'] . '</td>';
					if($prov['prov_id'] != '') {
						echo '<td>' . $prov['prov_razon_social'] . '</td>';
					} else {
						$preg3 = "SELECT prov_id, prov_nic, prov_razon_social FROM " . $dbpfx . "proveedores WHERE prov_id = '" . $fact['tercero_id'] . "'";
						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de proveedor!".$preg3);
						$cua = mysql_fetch_array($matr3);
						echo '<td>' . $cua['prov_razon_social'] . '</td>';
					}
					echo '<td style="text-align:center;">';
					if($pag['orden_id'] != '999999997') {
						echo '<a href="ordenes.php?accion=consultar&orden_id=' . $pag['orden_id'] . '">' . $pag['orden_id'] . '</a>';
					} else {
						echo $lang['No Aplica'];
					}
					echo '</td></tr>'."\n";
					if($j == 'claro') { $j = 'obscuro'; } else { $j = 'claro';}
				}
			}
		} elseif($numfact != '') {
		$preg1 = "SELECT pp.* FROM " . $dbpfx . "facturas_por_pagar f, " . $dbpfx . "pedidos_pagos pp  WHERE f.fact_num LIKE '%" . $numfact . "%' AND f.fact_id = pp.fact_id";
		if($tmes > 1357020000) { $preg1 .= " AND pp.pago_fecha LIKE '%" . $periodo . "%' ";}
		if($referencia != '') { $preg1 .= " AND pp.pago_referencia LIKE '%" . $referencia . "%' ";}
//			echo $preg1;
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion de!".$preg1);
				$j = 'claro';
				while ($pag = mysql_fetch_array($matr1)) {
					echo '				<tr class="' . $j . '">';
					echo '					<td><a href="pedidos.php?accion=consultar&pedido=' . $pag['pedido_id'] . '">' . $pag['pedido_id'] . '</a></td>'."\n";
					$preg2 = "SELECT * FROM " . $dbpfx . "facturas_por_pagar WHERE fact_id = '" . $pag['fact_id'] . "'";
					$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de facturas!".$preg2);
					$fact = mysql_fetch_array($matr2);
					echo '					<td>' . $fact['fact_num'] . '</td><td>' . date('Y-m-d', strtotime($pag['pago_fecha'])) . '</td><td>' . number_format($pag['pago_monto'],2) . '</td><td>' . $pag['pago_referencia'] . '</td>';
					if($prov['prov_id'] != '') {
						echo '<td>' . $prov['prov_razon_social'] . '</td>';
					} else {
						$preg3 = "SELECT prov_id, prov_nic, prov_razon_social FROM " . $dbpfx . "proveedores WHERE prov_id = '" . $fact['tercero_id'] . "'";
						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de proveedor!".$preg3);
						$cua = mysql_fetch_array($matr3);
						echo '<td>' . $cua['prov_razon_social'] . '</td>';
					}
					echo '<td style="text-align:center;">';
					if($pag['orden_id'] != '999999997') {
						echo '<a href="ordenes.php?accion=consultar&orden_id=' . $pag['orden_id'] . '">' . $pag['orden_id'] . '</a>';
					} else {
						echo $lang['No Aplica'];
					}
					echo '</td></tr>'."\n";
					if($j == 'claro') { $j = 'obscuro'; } else { $j = 'claro';}
				}

		} elseif ($referencia != '') {
			$preg1 = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE pago_referencia LIKE '%" . $referencia . "%'";
			if($tmes > 1357020000) { $preg1 .= " AND pago_fecha LIKE '%" . $periodo . "%' ";}
//			echo $preg1;
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion de!".$preg1);
				$j = 'claro';
				while ($pag = mysql_fetch_array($matr1)) {
					echo '				<tr class="' . $j . '">';
					echo '					<td><a href="pedidos.php?accion=consultar&pedido=' . $pag['pedido_id'] . '">' . $pag['pedido_id'] . '</a></td>'."\n";
					$preg2 = "SELECT * FROM " . $dbpfx . "facturas_por_pagar WHERE fact_id = '" . $pag['fact_id'] . "'";
					$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de facturas!".$preg2);
					$fact = mysql_fetch_array($matr2);
					echo '					<td>' . $fact['fact_num'] . '</td><td>' . date('Y-m-d', strtotime($pag['pago_fecha'])) . '</td><td>' . number_format($pag['pago_monto'],2) . '</td><td>' . $pag['pago_referencia'] . '</td>';
					if($prov['prov_id'] != '') {
						echo '<td>' . $prov['prov_razon_social'] . '</td>';
					} else {
						$preg3 = "SELECT prov_id, prov_nic, prov_razon_social FROM " . $dbpfx . "proveedores WHERE prov_id = '" . $fact['tercero_id'] . "'";
						$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de proveedor!".$preg3);
						$cua = mysql_fetch_array($matr3);
						echo '<td>' . $cua['prov_razon_social'] . '</td>';
					}
					echo '<td style="text-align:center;">';
					if($pag['orden_id'] != '999999997') {
						echo '<a href="ordenes.php?accion=consultar&orden_id=' . $pag['orden_id'] . '">' . $pag['orden_id'] . '</a>';
					} else {
						echo $lang['No Aplica'];
					}
					echo '</td></tr>'."\n";
					if($j == 'claro') { $j = 'obscuro'; } else { $j = 'claro';}
				}
		}
		echo '			</table>'."\n";
	} else {
		$_SESSION['msjerror'] = $mensaje;
		redirigir('proveedores.php?accion=pagos');
	}
}

elseif ($accion==="cuentasxpagar") {

	if(validaAcceso('1125045', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol13'] == 1))) {
		$mensaje = 'Acceso autorizado';
	} else {
		$_SESSION['msjerror'] = $lang['acceso_error'];
		redirigir('almacen.php');
	}

	$fondo = 'claro';
	$error = 'no';
	$hoy = strtotime(date('Y-m-d 23:59:59', time()));
	if($provid != '') { $operid = ''; $tipoprov = 1; }
	elseif($operid != '') { $tipoprov = 2; }
	else {$tipoprov = 1;}

// ------ Creación de Archivo Excel   ---------------------------
	if($export == 1) {
		$celda = 'A1';
		$titulo = $lang['CuentasPorPagar'] . ': ' . $nombre_agencia;
		require_once ('Classes/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel = $objReader->load("parciales/export.xls");
		$objPHPExcel->getProperties()->setCreator("AutoShop Easy")
					->setTitle($lang['CuentasPorPagar'])
					->setKeywords("AutoShop Easy");

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($celda, $titulo);

		// ------ ENCABEZADOS ---
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A4", $lang['Proveedor'])
					->setCellValue("B4", $lang['#Pedido'])
					->setCellValue("C4", $lang['FechaRecibido'])
					->setCellValue("D4", $lang['DiasCredito'])
					->setCellValue("E4", $lang['PagoProgramado'])
					->setCellValue("F4", $lang['DiasPPagar'])
					->setCellValue("G4", $lang['Facturas'])
					->setCellValue("H4", $lang['Total'])
					->setCellValue("I4", $lang['Pagado'])
					->setCellValue("J4", $lang['FechaDePago'])
					->setCellValue("K4", $lang['PorPagar'])
					->setCellValue("L4", $lang['ImpVenc'])
					->setCellValue("M4", $lang['Utilidad'])
					->setCellValue("N4", $lang['TipoPedido'])
					->setCellValue("O4", $lang['PedGestQV']);
		$z= 5;
	} else { // ---- HTML ----
		include('parciales/encabezado.php');
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
		echo '			<div class="page-content">
				<div class="row">
					<div class="col-sm-12">
						<div class="content-box-header">
							<div class="panel-title">
								<h2>' . strtoupper($lang['CuentasPorPagar']) . '</h2>
							</div>
						</div>
					</div>
				</div>
				<form action="proveedores.php?accion=cuentasxpagar" method="post" enctype="multipart/form-data" name="filtroprov">
				<div class="row">'."\n";
		if($rngcmp == 1) {
			echo '					<div class="col-sm-6">
						<a href="proveedores.php?accion=cuentasxpagar&provid=' . $provid . '&operid=' . $operid . '&feini=' . $feini . '&fefin=' . $fefin . '&rngcmp=0"><button type="button" class="btn btn-danger">' . $lang['RepRangoFech'] . '</button></a>'."\n";
			echo '					</div>'."\n";
		} else {
			echo '					<div class="col-sm-3">
						<strong><big>' . $lang['FechaInicio'] . '</big></strong><br>'."\n";
		require_once("calendar/tc_calendar.php");
		if($feini == '') {
			$feini = date('Y-m-01 00:00:00');
		}
		$myCalendar = new tc_calendar("feini", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
		$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
		//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		//$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2013, 2025);
		$myCalendar->setAutoHide(true, 5000);
		$myCalendar->writeScript();
		$feini = date('Y-m-d 00:00:00', strtotime($feini));
		echo '						<a href="proveedores.php?accion=cuentasxpagar&provid=' . $provid . '&operid=' . $operid . '&feini=' . $feini . '&fefin=' . $fefin . '&rngcmp=1"><button type="button" class="btn">' . $lang['RepCompSinFech'] . '</button></a>'."\n";
		echo '					</div>
					<div class="col-sm-3">
						<strong><big>' . $lang['FechaFin'] . '</big></strong><br>'."\n";
		if($fefin == '') {
			$fefin = date('Y-m-t 23:59:59');
		}
		$myCalendar = new tc_calendar("fefin", true);
		$myCalendar->setPath("calendar/");
		$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
		$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
		//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
		//$myCalendar->disabledDay("sun");
		$myCalendar->setYearInterval(2013, 2025);
		$myCalendar->setAutoHide(true, 5000);
		$myCalendar->writeScript();
		$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
		echo '					</div>'."\n";
		}
		echo '					<div class="col-sm-5">
						<strong><big>' . $lang['Proveedor'] . '</big></strong><br>
						<select name="provid" />
							<option value="" >' . $lang['TodosProv'] . '</option>'."\n";
		foreach($provs as $kp => $vp) {
			if($provsact[$kp] == 1) {
				echo '							<option value="' . $kp . '" ';
				if($kp == $provid) { echo 'selected '; }
				echo '>' . $vp . '</option>'."\n";
			}
		}
		echo '						</select>
					</div>
					<div class="col-sm-1">
						<a href="proveedores.php?accion=cuentasxpagar&export=1&provid=' . $provid . '&operid=' . $operid . '&feini=' . $feini . '&fefin=' . $fefin . '&rngcmp=' . $rngcmp . '">
							<img src="idiomas/' . $idioma . '/imagenes/hoja-calculo.png" alt="' . $lang['Exportar'] . '" title="' . $lang['Exportar'] . '" border="0">
						</a>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<input type="submit" name="' . $lang['Enviar'] . '" value="' . $lang['Enviar'] . '"/>
					</div>
				</div>
				</form>
				<div class="row">
					<div class="col-sm-12">
						<div id="content-tabla">
							<table cellspacing="0" class="table-new">
								<tr>
									<th colspan="5" style="text-align:left;">';
		echo '<a href="proveedores.php?accion=cxpglobal"><button type="button" class="btn btn-danger">' . $lang['Desglose'];
		if($provid != '') {
			echo $lang['PorProv'];
		} elseif( $operid != '') {
			echo $lang['PorOper'];
		}
		echo '</button></a></th><th colspan="6"><big><b>' . $lang['CuentasPorPagar'];

		if($provid != ''){
			echo $lang['para'] . $provs[$provid];
		}
		elseif($operid != ''){
			echo $lang['para'] . $usuario[$operid];
		}

		echo '							</b></big>
						</th>
					</tr>
					<tr>
						<th>' . $lang['Proveedor'] . '</th>
						<th>' . $lang['Pedido'] . '</th>
						<th>' . $lang['FechaRecibido'] . '</th>
						<th>' . $lang['DiasCredito'] . '</th>
						<th>' . $lang['PagoProgramado'] . '</th>
						<th>' . $lang['DiasPPagar'] . '</th>
						<th>' . $lang['Facturas'] . '</th>
						<th>' . $lang['Total'] . '</th>
						<th>' . $lang['Pagado'] . '</th>
						<th>' . $lang['FechaDePago'] . '</th>
						<th>' . $lang['PorPagar'] . '</th>
						<th>' . $lang['ImpVenc'] . '</th>
						<th>' . $lang['Utilidad'] . '</th>
						<th>' . $lang['TipoPedido'] . '</th>
						<th>' . $lang['PedGestQV'] . '</th>
					</tr>'."\n";
	}

	$preg1 = "SELECT p.* FROM " . $dbpfx . "pedidos p, " . $dbpfx . "proveedores pv WHERE p.pedido_pagado < '1' AND p.pedido_estatus >= '10' AND (p.pedido_estatus < '50' OR p.pedido_estatus = '99') AND (p.pedido_tipo = '2' OR p.pedido_tipo = '3') AND pv.prov_activo = '1' AND p.prov_id = pv.prov_id";
	if($rngcmp != 1) {
		// --- Verifica si es filtrado por fechas o es rango completo ---
		$preg1 .= " AND p.fecha_recibido >= '" . $feini . "' AND p.fecha_recibido <= '" . $fefin . "' ";
	}
	if($provid != '') {
		// --- Filtrar por Proveedor ---
		$preg1 .= " AND p.prov_id = '" . $provid . "' ";
	} elseif($operid != '') {
		$preg1 .= " AND p.prov_id = '" . $operid . "' ";
	}
	$preg1 .= " ORDER BY p.prov_id, p.pedido_id";
//	echo $preg1;
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de pedidos! " . $preg1);
	$txc = 0; $tyc = 0; $tve = 0; $txp = 0;
	while($pori = mysql_fetch_array($matr1)) {
		unset($montfact); unset($montpago);
		$monto_pedido = $pori['subtotal'] + $pori['impuesto'];
		$monto_pagado = 0; unset($fa);
		$fa['tped'] = $pori['pedido_tipo'];
		$fa['ped'] = '<a href="pedidos.php?accion=consultar&pedido=' . $pori['pedido_id'] . '" target="_blank">' . $pori['pedido_id'] . '</a> ';
		$fa['pedido'] = $pori['pedido_id'];
		$nombre = $provs[$pori['prov_id']];
		$fa['prov'] = '<a href="proveedores.php?accion=consultar&prov_id=' . $pori['prov_id'] . '" target="_blank">';
		$fa['rec'] = date('Y-m-d', strtotime($pori['fecha_recibido']));
//		$fa['dochcal'] = $lang['RecSinFactura'];
		// ------ Recolección de pagos a facturas -------
		$preg2 = "SELECT fact_id, fact_num, f_monto, f_rec, f_prog, f_pago FROM " . $dbpfx . "facturas_por_pagar WHERE doc_int_id = '" . $pori['pedido_id'] . "' AND pagada = '0'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de facturas por pagar! " . $preg2);
//		echo $preg2 .'<br>';
		$cobrada = 0; $fact_cob = 0; $fact_num = ''; $fact_fech = ''; $fech_cob = ''; $mpp = 0; $vencido = 0; $diaspp = '';
		while($fact = mysql_fetch_array($matr2)) {
				$fa['doc'] .= '<a href="pedidos.php?accion=consultar&pedido=' . $pori['pedido_id'] . '" target="_blank">' . $fact['fact_num'] . '</a> ';
				$fa['dochcal'] .= $fact['fact_num'] . ' ';
				if(!is_null($fact['f_prog']) && strtotime($fact['f_prog']) > strtotime($fa['prog'])) {
					$fa['prog'] = date('Y-m-d', strtotime($fact['f_prog']));
				}
				$montfact[$fact['fact_id']] = $fact['f_monto'];
		}
		$preg_pagos = "SELECT pf.monto, pf.fact_id, pf.pago_id, pp.pago_referencia, pp.pago_fecha FROM " . $dbpfx . "pagos_facturas pf,  " . $dbpfx . "pedidos_pagos pp WHERE pf.pedido_id = '" . $pori['pedido_id'] . "' AND pf.pago_id = pp.pago_id ";
//		echo $preg_pagos . '<br>';
		$matr_pagos = mysql_query($preg_pagos) or die("ERROR: Fallo selección de pagos! " . $preg_pagos);
		while($consulta_pagos = mysql_fetch_array($matr_pagos)) {
			$monto_pagado = $monto_pagado + $consulta_pagos['monto'];
			if(strtotime($fa['pag']) < strtotime($consulta_pagos['pago_fecha'])) {
				$fa['pag'] = date('Y-m-d', strtotime($consulta_pagos['pago_fecha']));
			}
			if($consulta_pagos['fact_id'] > 0) {
				$montpago[$consulta_pagos['fact_id']] = $consulta_pagos['monto'];
			}
		}
		if(strtotime($fa['prog']) > 1000) {
			$diaspp = intval((strtotime($fa['prog']) - $hoy)/86400);
		}

		foreach($montfact as $fid => $mf) {
			if(strtotime($fa['prog']) < $hoy && strtotime($fa['prog']) > 1000) {
				$vencido = $vencido + ($mf - $montpago[$fid]);
			}
		}

		$tve = $tve + $vencido;
		$mpp = $monto_pedido - $monto_pagado;
		$tpp = $tpp + $mpp;
		$typ = $typ + $monto_pagado;
		$txp = $txp + $monto_pedido;

		$fondo_utilidad = 'rojo_tenue';
		$utilidad = number_format($pori['utilidad'],2);
		if($pori['utilidad'] >= $utilcompras) {
			$fondo_utilidad = '';
		} elseif(is_null($pori['utilidad'])) {
			$utilidad = $lang['Incompleto'];
		}

		if($export == 1) { // ---- Hoja de calculo ----
			if(strtotime($fa['rec']) > 1000) {
				$fa['rec'] = PHPExcel_Shared_Date::PHPToExcel( strtotime($fa['rec']));
				// --- cambiar el formato de la celda tipo fecha/date ---
				$objPHPExcel->getActiveSheet()
					->getStyle('C'.$z)
					->getNumberFormat()
					->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			} else {
				$fa['rec'] = '';
			}
			if(strtotime($fa['prog']) > 1000) {
				$fa['prog'] = PHPExcel_Shared_Date::PHPToExcel( strtotime($fa['prog']));
				// --- cambiar el formato de la celda tipo fecha/date ---
				$objPHPExcel->getActiveSheet()
					->getStyle('D'.$z)
					->getNumberFormat()
					->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			} else {
				$fa['prog'] = '';
			}
			if(strtotime($fa['pag']) > 1000) {
				$fa['pag'] = PHPExcel_Shared_Date::PHPToExcel( strtotime($fa['pag']));
				// --- cambiar el formato de la celda tipo fecha/date ---
				$objPHPExcel->getActiveSheet()
					->getStyle('I'.$z)
					->getNumberFormat()
					->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			} else {
				$fa['pag'] = '';
			}
			if(is_numeric($utilidad)) {
				$utilcalc = $utilidad / 100;
			} else {
				$utilcalc = $utilidad;
			}
			$pedqv = 'No';
			if($pori['pedido_qv'] >= 1) {
				$pedqv = 'Sí';
			}
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$z, $nombre)
				->setCellValue('B'.$z, $fa['pedido'])
				->setCellValue('C'.$z, $fa['rec'])
				->setCellValue('D'.$z, $pori['dias_credito'])
				->setCellValue('E'.$z, $fa['prog'])
				->setCellValue('F'.$z, $diaspp)
				->setCellValue('G'.$z, $fa['dochcal'])
				->setCellValue('H'.$z, $monto_pedido)
				->setCellValue('I'.$z, $monto_pagado)
				->setCellValue('J'.$z, $fa['pag'])
				->setCellValue('K'.$z, $mpp)
				->setCellValue('L'.$z, $vencido)
				->setCellValue('M'.$z, $utilcalc)
				->setCellValue('N'.$z, constant('TIPO_PEDIDO_' . $fa['tped']))
				->setCellValue('O'.$z, $pedqv);
			$z++;
		} else { // ---- HTML ----
			echo '					<tr class="' . $fondo . '">'."\n";
			// echo '							' . $fa['prov'] . $nombre . '</a>
			echo '						<td style="text-align:left;"><a href="proveedores.php?accion=regpago&provid=' . $pori['prov_id'] . '" target="_blank">' . $nombre . '</a></td>
						<td style="text-align:left;">' . $fa['ped'];
			if($pori['pedido_qv'] >= 1) {
				echo '<br><img src="imagenes/logo-quien-vende-80.png" height="24" alt="Pedido gestionado en Quien-Vende.com">';
			}
			echo '</td>
						<td>' . $fa['rec'] . '</td>
						<td>' . $pori['dias_credito'] . '</td>
						<td>' . $fa['prog'] . '</td>
						<td>' . $diaspp . '</td>
						<td style="text-align:center;">' . $fa['doc'] . '</td>
						<td style="text-align:right;">' . number_format($monto_pedido,2) . '</td>
						<td class="aut' . $fondo . '" style="text-align:right;">' . number_format($monto_pagado,2) . '</td>
						<td style="text-align:center;">' . $fa['pag'] . '</td>
						<td class="pre' . $fondo . '" style="text-align:right;">' . number_format($mpp,2) . '</td>
						<td style="text-align:right;">' . number_format($vencido,2) . '</td>
						<td class="' . $fondo_utilidad . '" style="text-align:right;">' . $utilidad . '%</td>
						<td style="text-align:center;"><img src="idiomas/' . $idioma . '/imagenes/tipopedido-' . $fa['tped'] . '.png" width="24" alt="' . constant('TIPO_PEDIDO_' . $fa['tped']) . '" title="' . constant('TIPO_PEDIDO_' . $fa['tped']) . '"></td>
					</tr>'."\n";
			if ($fondo == 'obscuro') { $fondo = 'claro'; } else { $fondo = 'obscuro'; }
		}
	}

		if($export == 1) { // ---- Hoja de calculo ----
			//  Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="pedidos-por-pagar.xls"');
			header('Cache-Control: max-age=0');
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
		} else { // ---- HTML ----
			echo '
					<tr>
						<td colspan="6" style="text-align:left;"><a href="proveedores.php?accion=cxpglobal"><button type="button" class="btn btn-danger">' . $lang['Desglose'];
			if($provid != '') {
				echo $lang['PorProv'];
			} elseif( $operid != '') {
				echo $lang['PorOper'];
			}
			echo '</button></a>';
			echo '</td>
						<td colspan="1" style="text-align:right;"><big><b>' . number_format($txp,2) . '</b></big></td>
						<td style="text-align:right;"><big><b>' . number_format($typ,2) . '</b></big></td>
						<td colspan="1"></td>
						<td colspan="1" style="text-align:right;"><big><b>' . number_format($tpp,2) . '</b></big></td>
						<td style="text-align:right;"><big><b>' . number_format($tve,2) . '</b></big></td>
						<td colspan="2"></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>'."\n";
		}

}

elseif ($accion==="cxpglobal") {

	if (validaAcceso('1125045', $dbpfx) == '1') {
		$mensaje = 'Acceso autorizado';
	} else {
		$_SESSION['msjerror'] = $lang['acceso_error'];
		redirigir('almacen.php');
	}

	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	$fondo = 'claro';
	$hoy = strtotime(date('Y-m-d 23:59:59'));
//	$preg1 = "SELECT tercero_id FROM " . $dbpfx . "facturas_por_pagar WHERE tipo = '1' GROUP BY tercero_id";
	$preg1 = "SELECT prov_id, prov_nic FROM " . $dbpfx . "proveedores WHERE prov_activo = '1' ORDER BY prov_nic";
//	$preg1 = "SELECT prov_id, prov_nic FROM " . $dbpfx . "proveedores ORDER BY prov_nic";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de proveedores! " . $preg1);
	echo '			<div class="page-content">
				<div class="row"> <!-box header del título. -->
					<div class="col-sm-6">
						<div class="content-box-header">
							<div class="panel-title">
								<h2>' . $lang['CuentasPorPagar'] . ' ' . $lang['GlobalAl'] . date('Y-m-d') . '</h2>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<div id="content-tabla">
							<table cellspacing="0" class="table-new">
								<tr>
									<th colspan="3" style="text-align: center;"><big>' . $lang['Informe Global por Proveedor'] . '</big></th>
								</tr>
								<tr>
									<th><big>' . $lang['Proveedor'] . '</big></th>
									<th><big>' . $lang['PorPagar'] . '</big></th>
									<th><big>' . $lang['ImpVenc'] . '</big></th>
								</tr>'."\n";
	$totpp = 0; $totve = 0; ;
	while($prov = mysql_fetch_array($matr1)) {
		$preg2 = "SELECT pedido_id, subtotal, impuesto FROM " . $dbpfx . "pedidos WHERE prov_id = '" . $prov['prov_id'] . "' AND pedido_pagado < '1' AND pedido_estatus >= '10' AND (pedido_estatus < '50' OR pedido_estatus = '99') AND (pedido_tipo = '2' OR pedido_tipo = '3')";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de pedidos! " . $preg2);
		$pagada = 0; $fact_pag = 0; $dedu_pag = 0; $dedu_no = 0; $fact_num = ''; $fact_fech = ''; $dedu_num = ''; $fech_pag = ''; $fech_deducob = ''; $txp = 0; $typ = 0; $tve = 0; $mpp = 0; unset($pagos); $monto_pagado = 0;
		while($ped = mysql_fetch_array($matr2)) {
			unset($fa);
			$txp = $txp + ($ped['subtotal'] + $ped['impuesto']);
			$preg3 = "SELECT monto, fact_id FROM " . $dbpfx . "pagos_facturas WHERE pedido_id = '" . $ped['pedido_id'] . "'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de pagos a pedidos! " . $preg3);
			while($pag = mysql_fetch_array($matr3)) {
				$monto_pagado = $monto_pagado + $pag['monto'];
				$pagos[$pag['fact_id']] = $pagos[$pag['fact_id']] + $pag['monto'];
			}
			$preg4 = "SELECT fact_id, f_monto, f_prog FROM " . $dbpfx . "facturas_por_pagar WHERE doc_int_id = '" . $ped['pedido_id'] . "' AND tercero_id = '" . $prov['prov_id'] . "' AND pagada = '0'";
			$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de facturas del pedido " . $ped['pedido_id'] . "! " . $preg4);
			while($fact = mysql_fetch_array($matr4)) {
				if(strtotime($fact['f_prog']) < $hoy) {
					$tve = $tve + ($fact['f_monto'] - $pagos[$fact['fact_id']]);
				}
			}
		}
		$txp = $txp - $monto_pagado;
		echo '								<tr class="' . $fondo . '">
									<td style="text-align:left;"><a href="proveedores.php?accion=cuentasxpagar&provid=' . $prov['prov_id'] . '&rngcmp=1">' . $provs[$prov['prov_id']] . '</a></td>
									<td style="text-align:right;">' . number_format($txp,2) . '</td>
									<td style="text-align:right;">' . number_format($tve,2) . '</td>
								</tr>'."\n";
		$totpp = $totpp + $txp;
		$totve = $totve + $tve;
		if ($fondo == 'obscuro') { $fondo = 'claro';} else { $fondo = 'obscuro'; }
	}
	echo '								<tr>
									<td><a href="proveedores.php?accion=cuentasxpagar"><button type="button" class="btn btn-danger">' . $lang['Ir a Detalle de Cuentas por Pagar'] . '</button></a></td><td><big><strong>' . $lang['Total'] . ' ' . strtolower($lang['PorPagar']) . '</strong></big></td><td><big><strong>' . $lang['Total'] . ' ' . strtolower($lang['ImpVenc']) . '</strong></big></td>
								</tr>
								<tr>
									<td></td><td><big><strong>$' . number_format($totpp,2) . '</strong></big></td><td><big><strong>$' . number_format($totve,2) . '</strong></big></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'. "\n";
}

elseif ($accion === "refpend") {

	$funnum = 1105010;

//	echo 'Estamos en la sección  consulta';

	if ($_SESSION['rol13']=='1' || $_SESSION['rol08']=='1' || $_SESSION['rol02']=='1' || $_SESSION['rol03']=='1') {
			$mensaje = 'Acceso autorizado';
			include('parciales/encabezado.php');
			echo '	<div id="body">';
			include('parciales/menu_inicio.php');
			echo '		<div id="principal">';
		}
	else {
		 redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}

	$error = 'no'; $num_cols = 0;
	if ($prov_id < '1' || !isset($prov_id)) {
		$error = 'si'; $mensaje = 'Se requiere el número de proveedor.<br>';
	}

	if ($error ==='no') {
		$preg0 = "SELECT  prov_id, prov_razon_social, prov_representante, prov_email, prov_telefono1, prov_telefono2 FROM " . $dbpfx . "proveedores WHERE prov_activo = '1' AND prov_id = '$prov_id'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de proveedores!");
		while($prov = mysql_fetch_array($matr0)) {
			echo '	<table cellpadding="3" cellspacing="0" border="1" width="840">'."\n";
			echo '		<tr class="cabeza_tabla"><td colspan="6" style="text-align: center;">Reporte de Refacciones Pendientes</td></tr>'."\n";
			echo '		<tr><td colspan="5">Proveedor: ' . $prov['prov_razon_social'] . '</td><td>Generado el</td></tr>'."\n";
			echo '		<tr><td colspan="5">Contacto: ' . $prov['prov_representante'] . '</td><td>' . date('Y-m-d H:i') . '</td></tr>'."\n";
			echo '		<tr><td colspan="2">Email: ' . $prov['prov_email'] . '</td><td colspan="2">Tel. ' . $prov['prov_telefono1'] . '</td><td colspan="2">Tel. ' . $prov['prov_telefono2'] . '</td></tr>'."\n";
			echo '	</table>'."\n";
			echo '	<table cellpadding="3" cellspacing="0" border="1" width="840">'."\n";
			echo "		<tr><td>OT</td><td>Pedido</td><td>Siniestro</td><td>Nombre y referencia</td><td align=center>Cantidad<br>pendiente</td><td align=center>Fecha de<br>vencimiento</td></tr>\n";

			$preg2 = "SELECT pedido_id, fecha_promesa FROM " . $dbpfx . "pedidos WHERE prov_id = '" . $prov['prov_id'] . "' AND pedido_estatus < '10'";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de pedidos!");
			while($ped = mysql_fetch_array($matr2)) {
				$preg3 = "SELECT op_id, sub_orden_id, op_nombre, op_cantidad, op_recibidos, op_fecha_promesa FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $ped['pedido_id'] . "' AND op_ok = 0 AND op_tangible = '1'";
				$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de refacciones!");
//				echo $preg3;
				while($op = mysql_fetch_array($matr3)) {
					$preg4 = "SELECT orden_id, sub_reporte, sub_aseguradora FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $op['sub_orden_id'] . "'";
					$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección! " . $preg4);
					while($aseg = mysql_fetch_array($matr4)) {
						$pendiente = $op['op_cantidad'] - $op['op_recibidos'];
						$filas++;
						if($aseg['sub_reporte'] == '0') { $aseg['sub_reporte'] = 'Particular'; }
						echo '		<tr><td>' . $aseg['orden_id'] . '</td><td>' . $ped['pedido_id'] . '</td><td>' . $aseg['sub_reporte'] . '</td><td>' . $op['op_nombre'] . '</td><td style="text-align: center;">' . $pendiente . '</td><td>' . date('Y-m-d', strtotime($op['op_fecha_promesa'])) . '</td></tr>'."\n";
					}
				}
			}
		}
		echo '	</table>'."\n";
	} else {
		$mensaje .='No se encontraron registros con esos datos.</br>';
		echo '<p>' . $mensaje . '</p>';
	}
}

elseif($accion === "regpago") {

	if (validaAcceso('1105030', $dbpfx) == '1') {
		$msg = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['acceso_error']);
	}

	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';

	echo '		<form action="proveedores.php?accion=procesapago" method="post" name="procesacobro" enctype="multipart/form-data">
		<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	echo '					<tr><td>
				<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="100%">'."\n";
	$tipo = '1';
	$tipo_nom = $lang['Factura'];
	echo '					<tr class="cabeza_tabla"><td colspan="2">'. $lang['Registrar pago'] . ' ' . $lang['del proveedor'] . ' ' . $provs[$provid] . '</td></tr>';
	if(!isset($mont_pp) || $mont_pp == '') { $mont_pp = $_SESSION['prov']['pago']; }
	$mont_pp = limpiarNumero($mont_pp);
	echo '					<tr><td>'. $lang['Monto de este pago'].'</td><td style="text-align:left;"><input type="text" name="pago" value="' . number_format($mont_pp, 2) . '" size="10"  style="text-align:right;"/></td></tr>'."\n";
	echo '					<tr><td>'. $lang['Fecha del pago'].'</td><td style="text-align:left;">';
	require_once("calendar/tc_calendar.php");

	if(isset($_SESSION['prov']['fechapago'])) {
		$dia = date("d", strtotime($_SESSION['prov']['fechapago']));
		$mes = date("m", strtotime($_SESSION['prov']['fechapago']));
		$year = date("Y", strtotime($_SESSION['prov']['fechapago']));
	} else {
		$dia = date("d");
		$mes = date("m");
		$year = date("Y");
	}

		//instantiate class and set properties
	$myCalendar = new tc_calendar("fechapago", true);
	$myCalendar->setPath("calendar/");
	$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
	$myCalendar->setDate($dia, $mes, $year);
//	$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
	$myCalendar->disabledDay("sun");
	$myCalendar->setYearInterval(2011, 2025);
	$myCalendar->setAutoHide(true, 3000);

// output the calendar
	$myCalendar->writeScript();

	echo '</td></tr>';

	echo '					<tr><td>'. $lang['Método de pago'] .'</td><td style="text-align:left;">'."\n";
	echo '						<select name="forma_pago" size="1">
							<option value="" >'. $lang['Seleccione'].'</option>'."\n";
	for($i=1;$i<=$opcpago;$i++) {
		echo '							<option value="' . $i . '"';
		if ($_SESSION['prov']['forma_pago'] == $i) { echo ' selected="selected"'; }
		echo ' >' . constant('TIPO_PAGO_'.$i) . '</option>'."\n";
	}
	echo '						</select>'."\n";
	echo '					</td></tr>'."\n";
	echo '					<tr><td>'. $lang['Cuenta del pago'].'</td><td style="text-align:left;">
						<select name="cuenta" size="1">
							<option value="" >'. $lang['Seleccione'].'</option>'."\n";
	$preg0 = "SELECT * FROM " . $dbpfx . "cont_cuentas WHERE ban_activo = '1'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuentas");
	while ($ban = mysql_fetch_array($matr0)) {
		echo '							<option value="' . $ban['ban_cuenta'] . '"';
		if ($_SESSION['prov']['cuenta'] == $ban['ban_id']) { echo ' selected="selected"'; }
		echo ' >' . $ban['ban_nombre'] . ' - ' . $ban['ban_cuenta'] . '</option>'."\n";
	}
	echo '						</select>';
	echo '					</td></tr>'."\n";
	echo '					<tr><td>'. $lang['Num cheque o transferencia'].'</td><td style="text-align:left;">
	<input type="hidden" name="aseguradora" value="' . $aseguradora_id . '">
	<input type="text" name="referencia" value="' . $_SESSION['prov']['referencia'] . '" size="15" ';
	echo '/></td></tr>'."\n";
	echo '					<tr><td>'. $lang['Imagen de comprobante de cobro'].'</td><td style="text-align:left;"><input type="file" name="comprobante" size="30" /><input type="hidden" name="provid" value="' . $provid . '" /></td></tr>'."\n";
	echo '				</table>'."\n";

// ------------------- Seleccionar facturas por aplicar cobros -----------------------------------
	// --- Consulta de facturas no pagadas ---
	$preg1 = "SELECT * FROM " . $dbpfx . "facturas_por_pagar WHERE tercero_id = '" . $provid . "' AND pagada = 0  AND tipo = 1";
//	echo $preg1;
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de facturas".$preg1);
	$fila1 = mysql_num_rows($matr1);
	echo '				<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="100%">'."\n";
	$acobrar = 0;
	if($fila1 > 0) {
		echo '					<tr class="cabeza_tabla"><td colspan="8" style="text-align:right;">' . $lang['Facturas pendientes de pago'] . '</td></tr>'."\n";
		echo '					<tr><td>Pedido</td><td>Factura</td><td>Fecha</td><td>Importe Total</td><td>Importe Pagado</td><td>Importe Por Pagar</td><td>Seleccionar</td><td>Importe de pago</td></tr>'."\n";
		$tpp = 0; //antes $tpc
		while ($fact = mysql_fetch_array($matr1)) {
			// --- Consulta de pagos parciales por factura ---
			$preg2 = "SELECT monto, pedido_id, orden_id FROM " . $dbpfx . "pagos_facturas WHERE fact_id = '" . $fact['fact_id'] . "'";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cobros " . $preg2);
			$fila2 = mysql_num_rows($matr2);
			$orden = mysql_fetch_assoc($matr2);

			$pregFac = "SELECT pedido_id FROM " . $dbpfx . "pedidos WHERE orden_id = '" . $fact['orden_id'] . "'";
				$matrFac = mysql_query($pregFac) or die("ERROR: Fallo selección de cobros " . $pregFac);
				$factid = mysql_fetch_assoc($matrFac);

			$pagado = 0; //antes cobrado
			while ($pag = mysql_fetch_array($matr2)) {
				$pagado = $pagado + $pag['monto'];
			}
			$porpagar = $fact['f_monto'] - $pagado;
			$facid = $fact['pedido_id'];
			if($porpagar > 0) {
				
				$apagar++; //antes $acobrar
				if($factid['pedido_id']!=''){
				echo '					<tr><td><a href="pedidos.php?accion=consultar&pedido='.$factid['pedido_id'].'" target="_blank">' . $factid['pedido_id'] . '</a></td>';
				}
				else{
					echo '<tr><td>S/N </td>';
				}
				echo '<td>' . $fact['fact_num'] . '</td><td>' . $fact['f_rec'] . '</td><td style="text-align:right;">$' . number_format($fact['f_monto'], 2) . '</td><td style="text-align:right;">$' . number_format($pagado, 2) . '</td><td style="text-align:right;">$' . number_format($porpagar, 2) . '</td><td style="text-align:center;"><input type="checkbox" name="selfact[' . $fact['fact_id'] . ']" value="1" ';
				if($_SESSION['prov']['selfact'][$fact['fact_id']] == '1') {
					echo ' checked="checked" /></td><td style="text-align:right;">$ <input type="text" name="fact_porpag[' . $fact['fact_id'] . ']" value="';
					if($_SESSION['prov']['porpagar'][$fact['fact_id']] > 0) {
						$porpagar = $_SESSION['prov']['porpagar'][$fact['fact_id']];
					}
					echo $porpagar;
					echo '" size="8" style="text-align:right;" />';
					$tpp = $tpp + $porpagar;
				} else {
					echo ' /></td><td style="text-align:right;">';
				}
				echo '<input type="hidden" name="fact_monto[' . $fact['fact_id'] . ']" value="' . $fact['f_monto'] . '"/></td></tr>'."\n";
			}
		}
		$tpp = limpiarNumero($tpp);
		echo '					<tr><td colspan="7" style="text-align:right;">Importe Total a Registrar</td><td style="text-align:right;">$' . number_format($tpp, 2) . '<input type="hidden" name="tpp" value="' . $tpp . '" /></td></tr>'."\n";
		if($apagar == 0) {
			echo '					<tr><td colspan="8" style="text-align:left;"><span class="alerta">Hay facturas no cobradas pero no hay montos pendientes por cobrar para ' . $asenoti[$aseguradora_id]['razon'] . ', por favor contacte a Soporte AutoShop Easy.</span></td></tr>'."\n";
		}
		echo '					<tr><td colspan="8" style="text-align:left;"><input type="submit" name="recalcular" value="Recalcular" /><input type="hidden" name="provid" value="' . $provid . '" />';
		if($tpp == $mont_pp && $mont_pp > 0) {
			echo '<input type="submit" name="enviar" value="Aplicar" />';
		} else {
			echo 'No coinciden los montos';
		}
		echo '</td></tr>'."\n";
	} else {
		echo '					<tr><td colspan="8" style="text-align:left;"><span class="alerta">No se encontraron facturas por registrar cobros para ' . $asenoti[$aseguradora_id]['razon'] . '</span></td></tr>'."\n";
	}
	echo '				</table>'."\n";
	echo '				<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="100%">'."\n";
	echo '					<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>'."\n";
	echo '					<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="proveedores.php?accion=consultar&prov_id=' . $provid . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a aseguradora'].'" title="'. $lang['Regresar a aseguradora'].'"></a></div></td></tr>'."\n";
	echo '				</table>'."\n";
	echo '			</td>
			</tr>
		</table>
		</form>';
	unset($_SESSION['prov']);
}

elseif($accion === "procesapago") {

	if (validaAcceso('1105030', $dbpfx) == '1') {
		$msg = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['Acceso NO autorizado ingresar Usuario y Clave correcta']);
	}
	unset($_SESSION['prov']);
	$_SESSION['prov'] = array();
	foreach($selfact as $k => $v) {
		$_SESSION['prov']['selfact'][$k] = $v;
		$_SESSION['prov']['porpagar'][$k] = $fact_porpag[$k];
	}
	$pago = limpiarNumero($pago); $_SESSION['prov']['pago'] = $pago;
	$_SESSION['prov']['fechapago'] = $fechapago;
	$_SESSION['prov']['forma_pago'] = $forma_pago;
	$banco = preparar_entrada_bd($banco); $_SESSION['prov']['banco'] = $banco;
	$referencia = preparar_entrada_bd($referencia); $_SESSION['prov']['referencia'] = $referencia;
	$cuenta = preparar_entrada_bd($cuenta); $_SESSION['prov']['cuenta'] = $cuenta;
	if($recalcular == 'Recalcular') {
		redirigir('proveedores.php?accion=regpago&provid=' . $provid);
	}
//	print_r($selfact);
//	echo '<br>';
//	echo $pago;

	$mensaje = '';
	$error = 'no';
	$pago = limpiarNumero($pago); $_SESSION['prov']['pago'] = $pago;
//	$_SESSION['aseg']['por_cobrar'] = $por_cobrar;
	$tipo_nom = 'Factura';

	$por_cobrar = 0;
	foreach($fact_porpag as $k => $v) {
		$por_cobrar = $por_cobrar + $v;
	}
	$por_cobrar = limpiarNumero($por_cobrar);

	if($pago <= 0 || $pago == '') {$error = 'si'; $mensaje .= 'El monto no puede ser 0<br>';}
	if($forma_pago == '' || !isset($forma_pago)) {$error = 'si'; $mensaje .= $lang['Selecc forma de pago'].'<br>';}
	if(!isset($cuenta) || $cuenta == '') {$error = 'si'; $mensaje .= '<br>Debe seleccionar una cuenta';}
//	echo $cuenta;
	if($pago != $por_cobrar) {$error = 'si'; $mensaje .= '<br>El monto del pago no puede ser diferente';}

	if(!is_array($fact_monto) || count($fact_monto) < 1) {$error = 'si'; $mensaje .= '<br>Datos Incompletos';}

	if($tpp != $por_cobrar) {
		$_SESSION['msjerror'] = 'El monto Total por Registrar era diferente a la suma de las asignaciones por factura. Se recalculó.<br>';
		redirigir('proveedores.php?accion=regpago&provid=' . $provid);
	}

	if($error === 'no') {
		if($_FILES['comprobante']['name'] != '') {
			$subir = agrega_documento($orden_id, $_FILES['comprobante'], $lang['Comprob Cobro de factu'].$numero, $dbpfx);
		}
		// se crea el pago en pedidos_pagos
		$preg0 = "SELECT * FROM " . $dbpfx . "cont_cuentas WHERE ban_id = '$cuenta'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuenta bancaria");
		$ban = mysql_fetch_array($matr0);
//		print_r($subir);
//		echo 'Resultado de subir<br>';
		$sql_data_array = array(
			'pago_monto' => $pago,
			'pago_tipo' => $forma_pago,
			'pago_banco' => $banco,
			'pago_cuenta' => $cuenta,
			'pago_referencia' => $referencia,
			'pago_fecha' => $fechapago,
			'prov_id' => $provid,
			'pago_documento' => $subir['nombre'],
			'usuario' => $_SESSION['usuario'],

		);
		$pago_id = ejecutar_db($dbpfx . 'pedidos_pagos', $sql_data_array);
		// se crean las relaciones en pagos_facturas
		foreach($fact_porpag as $k => $v) {
			if($v > 0) {
//				echo '<br>fact= ' . $k;
				$preg1 = "SELECT * FROM " . $dbpfx . "facturas_por_pagar WHERE fact_id = '$k'";
				$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de facturas ".$preg1);
				$fact = mysql_fetch_array($matr1);
				$pedido_id = $fact['doc_int_id'];
				$orden_id = $fact['orden_id'];
				unset($sql_data_array);
				$sql_data_array = array(
					'pago_id' => $pago_id,
					'fact_id' => $k,
					'monto' => $v,
					'pedido_id' => $fact['doc_int_id'],
					'proveedor_id' => $provid,
					'usuario' => $_SESSION['usuario'],
					'fecha' => date('Y-m-d H:i:s', time()),
				);
				ejecutar_db($dbpfx . 'pagos_facturas', $sql_data_array);
				bitacora($orden_id, 'pago de Factura ID ' . $k . ' con el pagp id ' . $pago_id . ' por un monto de ' . $v, $dbpfx);
				$pedido_id = $fact['doc_int_id'];
				// --- Se marca como pagada la factura si el pago cubre el monto ---
				$preg2 = "SELECT monto FROM " . $dbpfx . "pagos_facturas WHERE fact_id = '$k'";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cobros - facturas ".$preg2);
				$mcob = 0;
				while($cf = mysql_fetch_array($matr2)) {
					$mcob = $mcob + $cf['monto'];
				}

				if($fact_monto[$k] == $mcob) {
					$coloca = "UPDATE " . $dbpfx . "facturas_por_pagar SET pagada = '1', f_pago = '" . $fechacobro . "' WHERE fact_id = '$k'";
					$graba = mysql_query($coloca) or die("ERROR: Fallo actualización de facturas por cobrar! " . $coloca);
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $coloca . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
					bitacora('', $lang['Cobro Total'] . $tipo_nom . ' id ' . $k, $dbpfx);
				}
// -------------------- Determinar el estatus del pedido -----------------------------

				actualiza_pedido($pedido_id, $dbpfx);

// ----------- Asientos contables ----------------->

				if($asientos == 1) {
					// --- ESTA SECCION ESTA EQUIVOCADA POR COMPLETO YA QUE ES UNA COPIA DE FACTURAS POR COBRAR ---
					$poliza = regPoliza('2', $lang['Registro en cuenta'] . $ban['ban_id'] .$lang['cobro de la factura'] . $fact['fact_num'] . $lang['OT'] . $orden_id, $fact_num);

					$resultado = regAsiento('5', '0', '2', $poliza['ciclo'], $poliza['polnum'], $ban['ban_id'],$lang['Registro en cuenta'] . $ban['ban_id'] . $lang['cobro de la factura'] . $fact['fact_num'] .$lang['OT'] . $orden_id, $v, $orden_id, $fact['fact_num']);

					$iva = round(($v - ($v / 1.16)), 2);
					$resultado = regAsiento('0', '0', '2', $poliza['ciclo'], $poliza['polnum'], '2000010', $lang['IVA trasladado por cobrar de la factura'] . $fact['fact_num'] .$lang['OT'] . $orden_id, $iva, $orden_id, $fact['fact_num']);

					$resultado = regAsiento('0', '1', '2', $poliza['ciclo'], $poliza['polnum'], '2000015', $lang['IVA cobrado de la factura'] . $fact['fact_num'] . $lang['OT'] . $orden_id, $iva, $orden_id, $fact['fact_num']);

					$preg2 = "SELECT reporte, cliente_id, aseguradora_id FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '$k' AND orden_id = '$orden_id'";
					$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de factura por cobrar");
					$fxc = mysql_fetch_array($matr2);
					if($fact['reporte'] == '0') {
						$resultado = regAsiento('4', '1', '2', $poliza['ciclo'], $poliza['polnum'], $fact['cliente_id'], $lang['Cobro de factura'] . $fact['fact_num'] . $lang['cliente'] . $fact['cliente_id'] . $lang['OT'] . $orden_id, $v, $orden_id, $fact['fact_num']);
					} else {
						$resultado = regAsiento('3', '1', '2', $poliza['ciclo'], $poliza['polnum'], $fact['aseguradora_id'], $lang['Cobro de factura'] . $fact['fact_num'] . $lang['aseguradora'] . $fact['aseguradora_id'] .$lang['OT'] . $orden_id, $v, $orden_id, $fact['fact_num']);
					}
				}
			}
		}
		unset($_SESSION['aseg']);
		$mensaje = "Se registró el pago éxitosamente";
		$_SESSION['msjpass'] = $mensaje;
		redirigir('proveedores.php?accion=regpago&provid=' . $provid);
	} else {
		$_SESSION['msjerror'] = $mensaje;
	redirigir('proveedores.php?accion=regpago&provid=' . $provid);
   }
}

elseif($accion === 'actstatus') {

	if (validaAcceso('1105000', $dbpfx) == '1') {
		$mensaje = 'Acceso autorizado';
	} else {
		$_SESSION['msjerror'] = $lang['acceso_error'];
		 redirigir('almacen.php');
	}

	$parametros = "prov_id = '" . $prov_id . "'";
	$sqldata['prov_activo'] = $activar;
	ejecutar_db($dbpfx . 'proveedores', $sqldata, 'actualizar', $parametros);
	redirigir('index.php');
}

elseif($accion === "pagos_grupales") {
	$funnum = 1005015;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if(isset($feini) == ''){
		$feini = date('Y-m-01');
		$fefin = date('Y-m-t');
	}
	if ($retorno == '1') {
		$msg = $lang['Acceso autorizado'];
	} else {
		redirigir('usuarios.php?mensaje='. $lang['acceso_error']);
	}
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	echo '			<div class="row">
								<div class="col-md-12">
									<div class="content-box-header" style="min-height: 60px;">
										<div class="panel-title">';
											$pregProv = "SELECT prov_razon_social FROM " . $dbpfx . "proveedores WHERE prov_id = $prov_id";
											$matrProv = mysql_query($pregProv) or die("ERROR: Fallo al consultar nombre del proveedor");
											//echo $pregProv;
											$esteProv = mysql_fetch_assoc($matrProv);
											echo '
											<span><big>Pagos grupales a pedidos de: <b> ' . $esteProv['prov_razon_social'] . '</b></big></span>
										</div>
									</div>
								</div>
							</div>';
							if($rango == ''){
								/*PAGINACION*/
								// maximo por pagina
								$limit = 25;
								// pagina pedida
								$pag = (int) $pag;
								if ($pag < 1){$pag = 1;}
								$offset = ($pag-1) * $limit;
								/*PAGINACION*/
								////SQL PAGINACION
								$pregPagos = "SELECT SQL_CALC_FOUND_ROWS * FROM " . $dbpfx . "pedidos_pagos INNER JOIN " . $dbpfx . "proveedores ON " . $dbpfx . "pedidos_pagos.prov_id = " . $dbpfx . "proveedores.prov_id WHERE " . $dbpfx . "pedidos_pagos.prov_id = '$prov_id' ORDER BY `" . $dbpfx . "pedidos_pagos`.`pago_id` DESC LIMIT $offset, $limit";
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Pedidos Pagados");
								$numpagos = mysql_num_rows($matrPagos);

								$sqlTotal = "SELECT FOUND_ROWS() as total";
								$matrsqlT = mysql_query($sqlTotal) or die("ERROR: Fallo selección de sql total");
								$rowTotal = mysql_fetch_assoc($matrsqlT);
								// Total de registros sin limit
								$total = $rowTotal["total"];
								///SQL NUMERO DE PAGOS TOTALES
								$pregPagos2 = "SELECT * FROM " . $dbpfx . "pedidos_pagos WHERE prov_id = '$prov_id'";
								$matrPagos2 = mysql_query($pregPagos2) or die("ERROR: Fallo selección de Pedidos Pagados");
								$numpagos2 = mysql_num_rows($matrPagos2);
							}
							elseif($rango == 1){
								$pregPagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos INNER JOIN " . $dbpfx . "proveedores ON " . $dbpfx . "pedidos_pagos.prov_id = " . $dbpfx . "proveedores.prov_id WHERE " . $dbpfx . "pedidos_pagos.prov_id = '$prov_id' AND pago_fecha BETWEEN '$feini' AND '$fefin' ORDER BY `" . $dbpfx . "pedidos_pagos`.`pago_id` DESC";
								//echo $pregPagos;
								$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Ragno");
								$numpagos = mysql_num_rows($matrPagos);
							}
							echo '
							<div class="row">';
								echo '
								<div class="row">'."\n";
									echo '
									<div class="col-sm-10">';
										if($rango == ''){
											echo '<span text-align="center"><h2>Mostrando (' . $numpagos . ' de ' . $numpagos2 . ') resultados<br></h2></span>';
											echo '<br><a class="btn btn-md btn-success" href="proveedores.php?accion=pagos_grupales&prov_id=' . $prov_id . '&rango=1">Filtrar por Rango de Fechas</a>';
										}
										elseif ($rango == 1) {
											echo '<span text-align="center"><h2>Mostrando (' . $numpagos . ') resultados del ' . $feini . ' al ' . $fefin . ' <br></h2></span>';
										}
										echo '
									</div>
								</div>';
								if($rango == 1){
									echo '
									<form action="proveedores.php?accion=pagos_grupales&prov_id=' . $prov_id . '&rango=1" method="post" enctype="multipart/form-data" name="filtroprov">
										<div class="row">
											<div class="col-sm-3">
												<strong><big>Fecha Inicio</big></strong><br>'."\n";
												require_once("calendar/tc_calendar.php");
												if($feini == '') {
													$feini = date('Y-m-01 00:00:00');
												}
												$myCalendar = new tc_calendar("feini", true);
												$myCalendar->setPath("calendar/");
												$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
												$myCalendar->setDate(date("d", strtotime($feini)), date("m", strtotime($feini)), date("Y", strtotime($feini)));
												//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
												//$myCalendar->disabledDay("sun");
												$myCalendar->setYearInterval(2013, 2025);
												$myCalendar->setAutoHide(true, 5000);
												$myCalendar->writeScript();
												$feini = date('Y-m-d 00:00:00', strtotime($feini));

												echo '
											</div>
											<div class="col-sm-3">
												<strong><big>' . $lang['FechaFin'] . '</big></strong><br>'."\n";
												if($fefin == '') {
													$fefin = date('Y-m-t 23:59:59');
												}
												$myCalendar = new tc_calendar("fefin", true);
												$myCalendar->setPath("calendar/");
												$myCalendar->setIcon("calendar/images/iconCalendar2.gif");
												$myCalendar->setDate(date("d", strtotime($fefin)), date("m", strtotime($fefin)), date("Y", strtotime($fefin)));
												//$myCalendar->dateAllow(date("Y-m-d"), "2020-12-31");
												//$myCalendar->disabledDay("sun");
												$myCalendar->setYearInterval(2013, 2025);
												$myCalendar->setAutoHide(true, 5000);
												$myCalendar->writeScript();
												$fefin = date('Y-m-d 23:59:59', strtotime($fefin));
												echo '
											</div>
											<div class="col-sm-5">
											</div>
											<div class="row">
												<div class="col-sm-12">
													<input class="btn btn-success" type="submit" name="' . $lang['Enviar'] . '" value="' . $lang['Enviar'] . '"/>
													<a class="btn btn-md btn-danger" href="proveedores.php?accion=pagos_grupales&prov_id=' . $prov_id . '">Desactivar por Rango de Fechas</a>
												</div>
											</div>

									</form>
							</div>';
								}
							echo'
							<div class="col-md-7"><br>
								<div style="overflow-x:auto; margin: 10px;">
									<table class="pagostabla">
										<thead style="background-color: white;">
											<tr style="background-color: white;">
												<th>Pago</th>
												<th>Proveedor</th>
												<th>Pedidos</th>
												<th>Facturas</th>
												<th>Banco</th>
												<th>Cuenta</th>
												<th>Monto</th>
												<th>Tipo de Pago</th>
												<th>Referencia</th>
												<th>Fecha</th>
												<th>Usuario</th>													
												<th></th>
											<tr>
										</thead>
										<tbody>';
											$number=0;
											while ($pagosm = mysql_fetch_array($matrPagos)) {
												$number++;
												$pregPagos3 = "SELECT pedido_id FROM " . $dbpfx . "pagos_facturas WHERE `pago_id` = $pagosm[pago_id] ORDER BY fecha DESC";
												$matrPagos3 = mysql_query($pregPagos3) or die("ERROR: Fallo selección de Pedidos Pagados");
												$pregPagos4 = "SELECT * FROM " . $dbpfx . "pagos_facturas WHERE `pago_id` = $pagosm[pago_id] ORDER BY fecha DESC";
												$matrPagos4 = mysql_query($pregPagos4) or die("ERROR: Fallo selección de Pedidos Pagados");
												$excel = array(
													'pago_id' => $pagosm['pago_id'],
													'prov_nic' => $pagosm['prov_nic'],
													'pago_banco' => $pagosm['pago_banco'],
													'pago_cuenta' => $pagosm['pago_cuenta'],
													'pago_monto' => $pagosm['pago_monto'],
													'pago_tipo' => constant("TIPO_PAGO_".$pagosm['pago_tipo']),
													'pago_referencia' => $pagosm['pago_referencia'],
													'pago_fecha' => $pagosm['pago_fecha'],
													'usuario' => $pagosm['usuario'],
													'prov_id' => $pagosm['prov_id'],
													'nombre_agencia' => $nombre_agencia,
													'feini' => $feini,
													'fefin' => $fefin,
													'rango' => $rango,
												);

													echo '
													<tr>
														<td>' . $pagosm['pago_id'] . '</td>
														<td>' . $pagosm['prov_nic'] . '</td>';
															$rowss = mysql_num_rows($matrPagos3);
															if ($rowss == 0){echo '<td><b>No hay Pedidos relacionados</b></td>';
															}else {
																echo '<td>';
																while ($estepago = mysql_fetch_array($matrPagos3)) {
																	//echo '<br>'.$pregPagos3.'<br>';
																	
																	if($pedido == $estepago['pedido_id']){
																		echo '<a class="btn btn-sm" style="color: white; background-color: #02678e;padding-top: 2px;padding-bottom: 2px;padding-left: 2px;padding-right: 2px; border-color: #022a39;text-decoration:none; " href="pedidos.php?accion=consultar&pedido=' . $estepago['pedido_id'] . '" target="_blank"><b>' . $estepago['pedido_id'] . '</b></a> ';
																	}else{
																		echo '<a class="btn btn-sm" style="color: black; background-color: #00c3ff;padding-top: 2px;padding-bottom: 2px;padding-left: 2px;padding-right: 2px; border-color: #015a75;text-decoration:none; " href="pedidos.php?accion=consultar&pedido=' . $estepago['pedido_id'] . '" target="_blank"><b>' . $estepago['pedido_id'] . '</b></a> ';
																	}
																	
																} echo '</td>';
															}
																$rowss2 = mysql_num_rows($matrPagos4);
															if ($rowss2 == 0){echo '<td><b>No hay Facturas relacionados</b></td>';
															}else {
																echo '<td>';			
																while ($estepago2 = mysql_fetch_array($matrPagos4)) {
																										
														
																
																	if($factura == $estepago2['fact_id']){
																		echo '<a class="btn btn-sm" style="color: white; background-color: #047310;padding-top: 2px;padding-bottom: 2px;padding-left: 2px;padding-right: 2px; border-color: #011c04;text-decoration:none; " href="pedidos.php?accion=consultar&pedido=' . $estepago2['pedido_id'] . '" target="_blank"><b>' . $estepago2['fact_id'] . '</b></a> ';
																	}else{
																		echo '<a class="btn btn-sm" style="color: black; background-color: #05e11d;padding-top: 2px;padding-bottom: 2px;padding-left: 2px;padding-right: 2px; border-color: #026c0e;text-decoration:none; " href="pedidos.php?accion=consultar&pedido=' . $estepago2['pedido_id'] . '" target="_blank"><b>' . $estepago2['fact_id'] . '</b></a> ';
																	}
																
															}echo '</td>';
														}
																echo '
														
														<td>';
														if($pagosm['pago_banco'] != ''){echo $pagosm['pago_banco'];}else{echo 'Efectivo';}
															echo '
														</td>														
														<td>';
														if($pagosm['pago_cuenta'] == '' || $pagosm['pago_cuenta'] == 0){echo 'Efectivo';}else{echo $pagosm['pago_cuenta'];}
															echo '
														<td style="text-align: right;"><b>$' . number_format($pagosm['pago_monto'], 2) . '</b></td>
														<td>' . constant("TIPO_PAGO_".$pagosm['pago_tipo']) . '</td>
														<td>';
														if($pagosm['pago_referencia'] == '' || $pagosm['pago_referencia'] == 0){echo 'Efectivo';}else{echo $pagosm['pago_referencia'];}
															echo '
														</td>			
														<td>' . $pagosm['pago_fecha'] . '</td>';
														$sqlUsuario = "SELECT * FROM " . $dbpfx . "usuarios WHERE usuario = '$pagosm[usuario]'";
														$matrsqlU = mysql_query($sqlUsuario) or die("ERROR: Fallo selección de sql total");
														$datosU = mysql_fetch_assoc($matrsqlU);
															echo'
														<td>' . $datosU['nombre'] . ' ' . $datosU['apellidos'] . '</td>
														<td><a href="proveedores.php?accion=ver_detalle_pago&pagoid=' . $pagosm['pago_id'] . '" class="btn btn-info btn-sm" target="_blank"> Ver</a></td>
													</tr>';
												}
												echo '
											</tbody>
											<tfoot>
												<tr>
													<td colspan="11">
														<div style="text-align: center;">';
															$totalPag = ceil($total/$limit);
															$links = array();
															for( $i=1; $i<=$totalPag ; $i++) {
																if($i == $pag ) {
																	$links[] = '<a style="background-color: #2c5ba0; color: white; border-color: white;" class="btn btn-md" href="proveedores.php?accion=pagos_grupales&prov_id=' . $prov_id . '&pag=' . $i . '">' . $i . '</a>';
																} else {
																	$links[] = '<a style="background-color: white; color: #2c5ba0; border-color: #2c5ba0;" class="btn btn-md" href="proveedores.php?accion=pagos_grupales&prov_id=' . $prov_id . '&pag=' . $i . '">' . $i . '</a>';
																}
															}
															echo implode(" ", $links);
															echo '
														</div>
													</td>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
								<div class="col-md-4">
								</div>
								<div class="row">
									<div class="col-md-6">
										<a href="proveedores.php?accion=consultar&prov_id=' . $prov_id . '"><img src="idiomas/es_MX/imagenes/regresar.png"></a>';
										$excel = serialize($excel);
										$excel = urlencode($excel);
										echo'
										<a href="excel-pagos-grupales.php?excel=' .$excel . '"><img src="idiomas/es_MX/imagenes/hoja-calculo.png"></a>
									</div>
								</div>';

}

elseif($accion === "ver_detalle_pago") {

	$funnum = 1005015;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1') {
		$msg = $lang['Acceso autorizado'];
	} else {
		 redirigir('usuarios.php?mensaje='. $lang['acceso_error']);
	}
	//$provid $operid

	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
		$pregPagos = "SELECT * FROM " . $dbpfx . "pedidos_pagos INNER JOIN " . $dbpfx . "proveedores ON " . $dbpfx . "pedidos_pagos.prov_id = " . $dbpfx . "proveedores.prov_id WHERE pago_id = $pagoid ORDER BY pago_fecha DESC";
		$matrPagos = mysql_query($pregPagos) or die("ERROR: Fallo selección de Pedidos Pagados2");
		$estepago = mysql_fetch_array($matrPagos);

	echo '		<div id="principal">';
	echo '			<div class="row">
								<div class="col-md-12">
									<div class="content-box-header" style="min-height: 60px;">
										<div class="panel-title">
											<span><big>Detalles del pago: <b>' . $estepago['pago_id'] . '</b></big></span>
										</div>
									</div>
								</div>
							</div>';
	echo '			<div class="row">
								<div class="col-md-7">';
	echo '					<table class="pagostabla">
										<tr>
											<td style="background-color: white;"><b>Pago</b></td>
											<td colspan="2">' . $estepago['pago_id'] . '</td>
										</tr>
										<tr>
											<td style="background-color: white;"><b>Proveedor</b></td>
											<td>' . $estepago['prov_razon_social'] . '</td>
											<td style="text-align: center;"><a href="proveedores.php?accion=consultar&prov_id=' . $estepago['prov_id'] . '" class="btn btn-info btn-sm">Ver</a></td>
										</tr>
										<tr>
											<td style="background-color: white;"><b>Tipo de Pago</b></td>
											<td colspan="2">' . constant("TIPO_PAGO_".$estepago['pago_tipo']) . '</td>
										</tr>
										<tr>
											<td style="background-color: white;"><b>Banco</b></td>
											<td colspan="2">' . $estepago['pago_banco'] . '</td>
										</tr>
										<tr>
											<td style="background-color: white;"><b>Cuenta</b></td>
											<td colspan="2">';
											$cuenta = $estepago['pago_cuenta'];
											$pregC = "SELECT * FROM " . $dbpfx . "cont_cuentas WHERE ban_cuenta LIKE '$cuenta'";
											$matrC = mysql_query($pregC) or die("ERROR: Fallo selección de cuentas");
											if($estac = mysql_fetch_array($matrC)){
												echo $estac['ban_nombre'] . ' - ' . $estac['ban_cuenta'];
											}

		echo '						</td>
										</tr>
										<tr>
											<td style="background-color: white;"><b>Referencia</b></td>
											<td colspan="2">' . $estepago['pago_referencia'] . '</td>
										</tr>
										<tr>
											<td style="background-color: white;"><b>Fecha de Pago</b></td>
											<td colspan="2">' . $estepago['pago_fecha'] . '</td>
										</tr>
										<tr>
											<td style="background-color: white;"><b>Usuario</b></td>
											<td colspan="2">';
											$usuario = $estepago['usuario'];
											$pregU = "SELECT * FROM " . $dbpfx . "usuarios WHERE usuario = '$usuario'";
											$matrU = mysql_query($pregU) or die("ERROR: Fallo selección de cuentas");
											if($esteU = mysql_fetch_array($matrU)){
												echo $esteU['usuario'] . ' - ' . $esteU['apellidos'] . ' - ' . $esteU['nombre'];
											}
											echo '</td>
										</tr>
										<tr>
											<td style="background-color: white;"><b>Documento</b></td>';
											if ($estepago['pago_documento'] == '') {
												echo '<td colspan="2">Sin Documento</td>';
												}
											else{
												echo '<td colspan="2"><a href="documentos/' . $estepago['pago_documento'] . '"><img src="documentos/' . $estepago['pago_documento'] . '" width="100px"><br>Ver Documento</a></td>';
											}
	echo '						</tr>
										<tr>
											<td style="background-color: white;"><b>Pago Monto</b></td>
											<td colspan="2"><b>$' . number_format($estepago['pago_monto'], 2) . '</b></td>
										</tr>
									</table><br><br>
									<div style="text-align: center;"><h2>Pedidos liquidados con este pago:</h2><br></div>';
									 $pregPagos2 = "SELECT * FROM " . $dbpfx . "pagos_facturas WHERE `pago_id` = $pagoid ORDER BY fecha DESC";
									 $matrPagos2 = mysql_query($pregPagos2) or die("ERROR: Fallo selección de Pedidos Pagados");
									 if($numpagos = mysql_num_rows($matrPagos2)){
	echo '					<table class="pagostabla">
										<thead>
											<tr style="background-color: white;">
												<th>Pedido</th>
												<th>Factura</th>
												<th>Fecha de Pago</th>
												<th>Acción</th>
												<th>Monto</th>
											<tr>
										</thead>
										<tbody>';
											$suma2 = 0;
											while ($pagosm2 = mysql_fetch_array($matrPagos2)) {
												echo '<tr>
																<td>' . $pagosm2['pedido_id'] . '</td>
																<td>' . $pagosm2['fact_id'] . '</td>
																<td>' . $pagosm2['fecha'] . '</td>
																<td style="text-align: center;"><a href="pedidos.php?accion=consultar&pedido=' . $pagosm2['pedido_id'] . '" class="btn btn-info btn-sm" target="_blank"> Ver a detalle</a></td>
																<td style="text-align: right;"><b>$' . number_format($pagosm2['monto'], 2) . '</b></td>
															</tr>';
												$suma2 += $pagosm2['monto'];
											}
	echo '						</tbody>
										<tfoot>
											<tr>
												<td colspan="7">TOTAL: $' . number_format($suma2, 2) . '</td>
											</tr>
										</tfoot>
									</table>';
									}
									else{
										echo '<div style="text-align: center;"><h2>Este pago no tiene pedidos Relacionados</h2><br></div>';
									}
	echo '
								</div>
								<div class="col-md-4">
								</div>
								<div class="row">
									<div class="col-md-6">
										<a href="proveedores.php?accion=pagos_grupales&prov_id=' . $estepago['prov_id'] . '"><img src="idiomas/es_MX/imagenes/regresar.png"></a>
									</div>
								</div>';
}

	if($export != 1) { // ---- HTML ----
			echo '
		</div>
	</div>'."\n";
			include('parciales/pie.php');
	}

?>