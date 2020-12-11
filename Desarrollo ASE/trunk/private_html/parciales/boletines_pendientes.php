<?php
// --- consultar boletines vigentes ---
$hoy_fecha = date('Y-m-d');

for($i = 0; $i < 2; $i++) {
	$prefijo = $dbpfx;
	if($i == 1) {
		// --- Obtenemos la lista de boletines ASE ---
		mysql_close();
		mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
		mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
		$prefijo = '';
	}	
		mysql_close();
						mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
						mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);
	$preg_boletin = "SELECT * FROM " . $dbpfx . "boletines WHERE boletin_fecha_publicacion <= '" . $hoy_fecha . "' AND boletin_fecha_vencimiento >= '" . $hoy_fecha . "' AND boletin_activo = '1'";
	//echo $preg_boletin . '<br>';
	//echo 'Codigo de usuario ' . $_SESSION['codigo'] . '<br>';	
	$mat_boletin = mysql_query($preg_boletin) or die("ERROR: Fallo selección! " . $preg_boletin);
	$cuantos = mysql_num_rows($mat_boletin);

	// ------ Recorrer boletines vigentes ------	
	while($boletines = mysql_fetch_array($mat_boletin)) {
		// --- Consultar correspondencia ---
		$array_correspondencia = explode("|", $boletines['boletin_correspondecia']);
		//echo 'boletin: ' . $boletines['boletin_id'] . ' <br>';
		$muestra_boletin = 0;
		// --- hacer match con el código del usuario ---
		foreach($array_correspondencia as $key => $val){
			//echo 'Key ' . $key . ' - val ' . $val . '<br>';
			if($_SESSION['codigo'] == $val){
				//echo 'Mostrar boletin a usuario<br>';
				$muestra_boletin = 1;
				break;
			}
		}
		//echo 'Muestra Boletin: ' . $muestra_boletin .'<br>';
		// --- Revisar si ya fue leido el boletin ---
		//if($muestra_boletin == 1){
			$preg_leido = "SELECT lectura_boletin_id FROM " . $dbpfx . "boletines_leidos WHERE 	boletin_id = '" . $boletines['boletin_id'] . "' AND usuario_id = '" . $_SESSION['usuario'] . "' ";
			if($i == 1) { $preg_leido .= " "; }
			$preg_leido .= "LIMIT 1";
			$mat_leido = mysql_query($preg_leido) or die("ERROR: Fallo selección! " . $preg_leido);
			$leido = mysql_num_rows($mat_leido);
						
			if($leido == 0){
				//echo 'El usuario no ha leido el boletín<br>';
				$mostrar_anuncio = 1;
				break;
			}	
		//}
	}
}

// ------ Reconectamos a base de datos de la instancia
	mysql_close();
	mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
	mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);

	//if($lmostrar_anuncio == 1){
		// --- Mostrar botón de boletines sin leer ---
		echo '
				<table width="100%">
					<tr>
						<td>
							<div class="page-content content-box">
								<div class="row"  style="background-color: #65baec; border-radius: 10px;"> <!-box header del título. -->
									<div class="col-md-12" >
										<div class="panel-title">
											<center>';


												if($cuantos == 1){
													echo'<img src="imagenes/comentarios-pendientes2.png" alt="" title="" style="position: relative;top: 6px;left: 23px;z-index: 0;color: white;padding-right: 5px;padding-left: 5px;padding-top: 1px;padding-bottom: 1px;">
													<span style="position: relative;top: -9px;left: 3px;z-index: 0;background-color: red;color: white;border-radius: 100%;padding-right: 5px;padding-left: 5px;padding-top: 1px;padding-bottom: 1px;"><b>' . $cuantos . '</b></span>
			  										<b><big>Boletin sin leer</big></b> <a href="boletines.php?accion=listar">
														<button type="button" class="btn btn-small btn-primary">VER</button></a>';

												}
												elseif($cuantos >1){
													echo'<img src="imagenes/comentarios-pendientes2.png" alt="" title="" style="position: relative;top: 6px;left: 23px;z-index: 0;color: white;padding-right: 5px;padding-left: 5px;padding-top: 1px;padding-bottom: 1px;">
													<span style="position: relative;top: -9px;left: 4px;z-index: 0;background-color: red;color: white;border-radius: 100%;padding-right: 5px;padding-left: 5px;padding-top: 1px;padding-bottom: 1px;"><b>' . $cuantos . '</b></span>
			  										<b><big>Boletines sin leer</big></b> <a href="boletines.php?accion=listar">
														<button type="button" class="btn btn-small btn-primary">VER</button></a>';
												}

												else{ 

													echo ' <b><big>Boletines</big></b> <a href="boletines.php?accion=listar">
														<button type="button" class="btn btn-small btn-primary">VER</button></a>';
												}
												
									echo'	</center>
										</div>
									</div>
								</div>
							</div>
						</td>
					</tr>
				</table>
				<br>'."\n";
	//}
?>