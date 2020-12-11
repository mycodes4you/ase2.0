<?php 
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/boletines.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

include('parciales/encabezado.php');
echo '	<div id="body">';
include('parciales/menu_inicio.php');
echo '		<div id="principal">' ."\n";
echo '			<div class="page-content">'."\n";

if($accion === "gestionar") {

	if (validaAcceso('1170100', $dbpfx) == 1 || ($solovalacc != 1 && ($_SESSION['rol02'] == 1))) {
		// Acceso autorizado
	} else {
		$_SESSION['msjerror'] = $lang['AccNoAut'] ;
		redirigir('usuarios.php');
	}

// ------ Creamos consulta de boletines
	$preg_boletines = "SELECT * FROM " . $dbpfx . "boletines ORDER BY boletin_id DESC";
	$mtr_boletines = mysql_query($preg_boletines) or die ('Fallo conexión a boletines ' . $preg_boletines);
	$num_resultados = mysql_num_rows($mtr_boletines); // --- total de boletines ---

	//echo $num_resultados . "<br>";

	// ***** Definimos el num. de prod. x pag. y total de pags. *****
	$renglones = 10;
	$paginas = (round(($num_resultados / $renglones) + 0.49999999) - 1);	
			
	// -------------- Si no esta seteada la página se coloca en 0 -----------
	if(!isset($pagina)) { $pagina = 0;}

	// --- Calculamos los resultados que deben de ser consultados ---
	$inicial = $pagina * $renglones;
	$final = $inicial + $renglones;
	$indicador = $inicial + 1;

	// --- CONSULTAMOS LOS RESULTADOS DE LA PÁGINA ACTUAL ---
	$preg_boletines .= " LIMIT " . $inicial . ", " . $renglones;

	$matr_pag_actual = mysql_query($preg_boletines);

	/*
	echo 'pagina: ' . $pagina . '<br>';
	echo 'inicial: ' . $inicial . '<br>';

	echo 'total de prods: ' .  $num_resultados . '<br>';
	echo 'total de elementos: ' . $renglones . '<br>';
	echo 'total de paginas: ' . $paginas . '<br>';
	*/
	
	// --- Comienza cuerpo de la pag. ---


	// --- errores ---
	if($_SESSION['mensaje'] != ''){
		
		echo '
		<div class="row">
			<div class="col-md-12">
					<span class="alerta">' . $_SESSION['mensaje']['publicado'] . '</span>
					<span class="alerta">' . $_SESSION['mensaje']['desactivado'] . '</span>
			</div>
		</div>
		<br>'."\n";

		unset($_SESSION['mensaje']);
	}


	echo '
		<div class="row"> <!-box header del título. -->
			<div class="col-md-12">
					<div class="content-box-header">
					<div class="panel-title">
							<h2>' . $lang['Gestión de Boletines'] . ' ' . $nombre_agencia . '</h2>
					</div>
					<a href="boletines.php?accion=crear">
						<button type="button" class="btn btn-success">' . $lang['CREAR BOLETÍN'] . '</button>
					</a>
					</div>
			</div>
		</div>
		<br>
		
		<div class="row">
			<div class="col-md-12 ">
				<div class="col-md-12">
					<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr>
								<th>' . $lang['BOLETÍN'] . '</th>
								<th>' . $lang['ACTIVO?'] . '</th>
								<th>' . $lang['TÍTULO'] . '</th>
								<th>' . $lang['FECHA_PUBLICACION'] . '</th>
								<th>' . $lang['FECHA_VENCIMIENTO'] . '</th>
							</tr>'."\n";

	// ----------------- RESAULTADOS DE LA CONSULTA -------------------
	$clase = 'claro';
	while($boletines = mysql_fetch_array($matr_pag_actual)){

		if($boletines['boletin_activo'] == 1){
			$imagen = '<img src="idiomas/' . $idioma . '/imagenes/ok.png" alt="leido" title="leido">';
		} else{
			$imagen = '<img src="idiomas/' . $idioma . '/imagenes/edit-delete-6.png" alt="no leido" title="no leido">';
		}
		
		echo '
							<tr class="' . $clase . '">
								<td>
									<big><a href="boletines.php?accion=consultar&boletin_id=' . $boletines['boletin_id'] . '">
										' . $boletines['boletin_id'] . '
									</a></big>
								</td>
								<td>' . $imagen . '</td>
								<td style="text-align: left !important;"><big>' . $boletines['boletin_titulo'] . '</big></td>
								<td><big>' . date('Y-m-d', strtotime($boletines['boletin_fecha_publicacion'])) . '</big></td>
								<td><big>' . date('Y-m-d', strtotime($boletines['boletin_fecha_vencimiento'])) . '</big></td>
							</tr>'."\n";

		if($clase == 'claro'){
			$clase = 'obscuro';
		} else{
			$clase = 'claro';
		}

	}

	// --- Selector de Páginas ---
	
	// --- pag. inicio ---
	if($pagina == 0){ 
				
		$mostar_primera = 0;
				
	} else{
				
		$mostar_primera = 1;
		$href_primera = "boletines.php?pagina=0&accion=gestionar";
				
	}
	// --- pag. anterior ---
	if($pagina > 0) {
				
		$mostar_anterior = 1;
		$url = $pagina - 1;
		$href_anterior = "boletines.php?pagina=$url&accion=gestionar";
												
	}
	// --- pag. actual ---
	$pag_actual = $pagina + 1;
																								
	// --- pag. siguiente ---
	if($pagina < $paginas) {
				
		$mostar_siguiente = 1;
		$url = $pagina + 1;
		$href_siguiente = "boletines.php?pagina=$url&accion=gestionar";
												
	}

	// --- pag. fin ---
	if($pagina != $paginas){
				
		$mostar_ultima = 1;
		$href_ultima = "boletines.php?pagina=$paginas&accion=gestionar";
												
	}

	echo '
							<tr class="' . $clase . '">
								<td colspan="3"> </td>
								<td>
								<big>';

	if($mostar_ultima == ''){

		echo "<b>mostrando $indicador - $num_resultados  de  $num_resultados  resultados</b>";

	} else {

		echo "<b>mostrando  $indicador - $final  de $num_resultados</b>";

	}

	echo '								
								</big>
								</td>
								<td>
								<big>';

	if($mostar_primera == 1){
			
		// --- primera Pagina ---
		echo '<a href="' . $href_primera . '">Primera </a>';

	}

	if($mostar_anterior == 1){

		// --- anterior Pagina ---
		echo '<a href="' . $href_anterior . '"><--</a> ';
	}

	// --- actual Pagina ---
	echo '<b> ' . $pag_actual . ' </b>';

	if($mostar_siguiente == 1){

		// --- siguiente Pagina ---
		echo '<a href="' . $href_siguiente . '"> --></a> ';
	}

	if($mostar_ultima == 1){

		// --- última Pagina ---
		echo ' <a href="' . $href_ultima . '">Última</a> ';
	}

	echo '						
								</big>
								</td>
							</tr>
							'."\n";


	echo '
						</table>
					</div>
				</div>
			</div>
		</div>'."\n";
	echo '
	</div>'."\n";

}

elseif($accion === "consultar") {

	$funnum = 1170100; // Acceso a gestión de boletines
	
	if (validaAcceso($funnum, $dbpfx) == 1 || $_SESSION['rol02']=='1') {
		// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=El acceso a esta función es sólo para administradores');
	}

	// --- Consultar Info del boletín ---
	$preg_boletin = "SELECT * FROM " . $dbpfx . "boletines WHERE boletin_id = '$boletin_id'";
	$mat_boletin = mysql_query($preg_boletin) or die("ERROR: Fallo selección!" . $preg_boletin);
		$boletin_info = mysql_fetch_array($mat_boletin);

		// --- contar el total de roles ---
		$total_cods_puesto = count($cod_puesto);
		// --- descontar el rol de sistemas y el rol de aseguradora ---
		$total_cods_puesto = $total_cods_puesto - 2;

		// --- Consultar correspondencia ---
		$array_correspondencia = explode("|", $boletin_info['boletin_correspondecia']);
		$total_correspondencia = count($array_correspondencia);

		//echo 'total roles ' . $total_roles . '<br>';
		//echo 'total correspondencia ' . $total_correspondencia . '<br>';

		if($total_correspondencia == $total_cods_puesto){

			$dirigido_a = $lang['Todo el personal'];

		} else{

			// --- crear cadena con los roles ---
			$dirigido_a = '';

			foreach ($array_correspondencia as $key => $value) {
				//echo $key . ' -> ' . $value . '<br>';
				$dirigido_a .= $cod_puesto[$value] . ', ';

			}
		}
	
	// --- determinar si está activo el boletín ---
	if($boletin_info['boletin_activo'] == 1){
		$imagen = '<img src="idiomas/' . $idioma . '/imagenes/ok.png" alt="leido" title="leido">';
		$activo = 1;
		$boton = '<a href="boletines.php?accion=desactivar&boletin_id=' . $boletin_info['boletin_id'] . '">
					<button type="button" class="btn btn-danger">' . $lang['DESACTIVAR BOLETIN'] . '</button>
				</a>';
	} else{
		$imagen = '<img src="idiomas/' . $idioma . '/imagenes/edit-delete-6.png" alt="no leido" title="no leido">';
		$boton = '<a href="boletines.php?accion=activar&boletin_id=' . $boletin_info['boletin_id'] . '">
					<button type="button" class="btn btn-success">' . $lang['ACTIVAR BOLETIN'] . '</button>
				</a>';
	}

	
		echo '
	<div class="page-content">'."\n";

	echo '
		<div class="row">
			<div class="col-sm-12">
					<div class="content-box-header">
					<div class="panel-title">
							<h2>' . $lang['CONSULTAR'] . '&nbsp;&nbsp;&nbsp; <small>' . $lang['BOLETÍN:'] . $boletin_id . $lang['ACTIVO:'] . $imagen . '</small></h2> 
					</div>
					</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-8">
				<legend class="legend"><b>' . $boletin_info['boletin_titulo'] . '</b></legend>
			</div>
			<div class="col-sm-4">
				<b>' . $lang['Vigencia del boletín'] . '</b> Del ' . date('Y-m-d', strtotime($boletin_info['boletin_fecha_publicacion'])) . ' al ' .  date('Y-m-d', strtotime($boletin_info['boletin_fecha_vencimiento'])) . '<br>
				<b>' . $lang['Dirigido a:'] . '</b>' . $dirigido_a . '
			</div>
		</div>
		<div class="row">
			<div class="col-sm-8 panel-body">
				<p style="line-height: 1.5; text-align : justify;">
				<big>' . nl2br($boletin_info['boletin_contenido']) . '</big>
				</p>

			</div>
		</div>
		'."\n";

		// --- si el boletín contiene un documento se muestra ---
		if($boletin_info['boletin_archivo'] != ''){

			//echo 'El boletín tiene archivo';
			$extencion = explode(".", $boletin_info['boletin_archivo']);

			// --- si el archivo es una imagen desplegarla ---
			if($extencion[01] == 'jpg' || $extencion[01] == 'JPG' || $extencion[01] == 'jpeg' || $extencion[01] == 'JPEG' || $extencion[01] == 'gif' || $extencion[01] == 'GIF' || $extencion[01] == 'png' || $extencion[01] == 'PNG'){
				echo '
				<div class="row">
					<div class="col-md-8 panel-body">
						<a href="' . DIR_DOCS . $boletin_info['boletin_archivo'] . '" target="_blank">
								' . $lang['Ver imgen en grande...'] .'
						</a>
						<br>
						<a href="' . DIR_DOCS . $boletin_info['boletin_archivo'] . '" target="_blank">'."\n";
				echo '						<img style="height:1200px; max-width:800px; height:auto; width:auto;" src="' . DIR_DOCS . $boletin_info['boletin_archivo'] . '" alt="">'."\n";
				echo '						</a>
					</div>
				</div>'."\n";
			} else {
				// --- mostrar link del documento ---
				echo '
				<div class="row">
					<div class="col-md-8 panel-body">
						<b>' . $lang['Click en la imagen para ver el documento anexo:'] . '
						<br> ' . $boletin_info['boletin_archivo'] . '</b>
						<br>
						<a href="' . DIR_DOCS . $boletin_info['boletin_archivo'] . '" target="_blank">
							<img src="' . DIR_DOCS . 'documento.png" width="48" border="0">
						</a>
					</div>
				</div>'."\n";
			}
		}

		echo '
		<div class="row">
			<div class="col-md-4 panel-body">
				<a href="boletines.php?accion=gestionar"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Gestionar boletines" title="Gestionar boletines"></a>
				&nbsp;
				<a href="boletines.php?accion=editar&boletin_id=' . $boletin_info['boletin_id'] . '">
					<button type="button" class="btn btn-success">' . $lang['EDITAR BOLETIN'] . '</button>
				</a>
				' . $boton . '
			</div>
		</div>'."\n";


//  ---------------- obtener nombres de usuarios -------------------

	$pregusu = "SELECT nombre, apellidos, usuario, codigo FROM " . $dbpfx . "usuarios WHERE acceso = '0'";
	if($incusuinact != 1) { $pregusu .= " AND activo = '1'"; }
	$pregusu .= " ORDER BY nombre, apellidos";
	$matrusu = mysql_query($pregusu) or die("ERROR: Fallo selección de usuarios!");
	while ($ases = mysql_fetch_array($matrusu)) {
		$usuario[$ases['usuario']] = $ases['nombre'] . ' ' . $ases['apellidos'];
		$usr_cod[$ases['usuario']] = $ases['codigo'];
		
		// --- Consultar si el usuario ya leyó el boletin ---
		$preg_leido = "SELECT usuario_id, lectura_fecha FROM " . $dbpfx . "boletines_leidos WHERE boletin_id = '" . $boletin_info['boletin_id'] . "' AND usuario_id = '" . $ases['usuario'] . "' LIMIT 1 ";
		$mat_leido = mysql_query($preg_leido) or die("ERROR: Fallo selección! " . $preg_leido);
		$bo_leido = mysql_num_rows($mat_leido);
		if($bo_leido == 1){
			
			$info_leido = mysql_fetch_array($mat_leido);
			$usuarios_leido[$ases['usuario']]['leido'] = 1;
			$usuarios_leido[$ases['usuario']]['fecha'] = $info_leido['lectura_fecha'];
			//echo 'usuario: ' . $ases['usuario'] . ' leyó el boletín<br>';
			
		} else{
			$usuarios_no_leido[$ases['usuario']] = 1;
			//echo 'usuario: ' . $ases['usuario'] . ' no leyó el boletín<br>';
		}
		
	}
	
	echo '
		<div class="row">
			<div class="col-md-8 panel-body">
				<legend class="legend">
					<b>' . $lang['Usuarios que han leido el boletín'] . '</b>
					<small>
						<b style="Background-color: green; color: white;">' . $lang['a tiempo'] . '</b>
						<b style="Background-color: red; color: white;">' . $lang['fuera de tiempo'] . '</b>
						<form action="boletines.php?accion=consultar&boletin_id=' . $boletin_id . '" method="post" enctype="multipart/form-data" name="usuinact">
						' . $lang['IncUsuInact'] . ' <input type="checkbox" name="incusuinact" value="1"';
	if($incusuinact == 1) { echo ' checked ';}
	echo ' onchange="document.usuinact.submit()";/>
						</form>
					</small>
				</legend>
				<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr>
								<th style="text-align: left !important;">' . $lang['NOMBRE'] . '</th>
								<th>' . $lang['PUESTO'] . '</th>
								<th>' . $lang['FECHA DE LECTURA'] . '</th>
							</tr>'."\n";
	
	$clase = 'claro';
	foreach($usuarios_leido as $key => $val){
		
		//echo 'key: ' . $key . " val: " . $val['fecha'] . '<br>';
		// --- determinar si fue leido en tiempo a tiempo ----
		if($val['fecha'] > $boletin_info['boletin_fecha_vencimiento']){
			$estilo = 'style="Background-color: red; color: white;"';
		} else{
			$estilo = 'style="Background-color: green; color: white;"';
		}
		
		echo '
							<tr class="' . $clase . '">
								<td style="text-align: left !important;">' . $usuario[$key] . ' <b>' . $lang['usuario:'] . $key . '</b></td>
								<td>' . $cod_puesto[$usr_cod[$key]] . '</td>
								<td ' . $estilo . '>' . date('Y-m-d', strtotime($val['fecha'])) . '</td>
							</tr>
		'."\n";
		
		if($clase == 'claro'){
			$clase = 'obscuro';
		} else{
			$clase = 'claro';
		}
	}
	
	echo '
						</table>
				</div>
			</div>
		</div>
		'."\n";
	
	
	// --- Usuarios que no han leido el boletin ---
	echo '
		<div class="row">
			<div class="col-md-8 panel-body">
				<legend class="legend"><b>' . $lang['Usuarios que no han leido el boletín'] . '</b></legend>
				<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr>
								<th style="text-align: left !important;">' . $lang['NOMBRE'] . '</th>
								<th>' . $lang['PUESTO'] . '</th>
							</tr>'."\n";
	
	$clase = 'claro';
	foreach($usuarios_no_leido as $key => $val){
		
		//echo 'key: ' . $key . " val: " . $val['fecha'] . '<br>';
		
		
		echo '
							<tr class="' . $clase . '">
								<td style="text-align: left !important;">' . $usuario[$key] . ' <b>' . $lang['usuario:'] . $key . '</b></td>
								<td>' . $cod_puesto[$usr_cod[$key]] . '</td>
							</tr>
		'."\n";
		
		if($clase == 'claro'){
			$clase = 'obscuro';
		} else{
			$clase = 'claro';
		}
	}

	echo '
						</table>
				</div>
			</div>
		</div>
		'."\n"; 

	echo '
		<div class="row">
			<div class="col-md-4 panel-body">
				<a href="boletines.php?accion=gestionar"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Gestionar boletines" title="Gestionar boletines"></a>
				&nbsp;
				<a href="boletines.php?accion=editar&boletin_id=' . $boletin_info['boletin_id'] . '">
					<button type="button" class="btn btn-success">' . $lang['EDITAR BOLETIN'] . '</button>
				</a>
				' . $boton . '
			</div>
		</div>'."\n";

	echo ' 
	</div>'."\n";

}

elseif($accion === "crear") {

	$funnum = 1170100; // Acceso a gestión de boletines
	
	if (validaAcceso($funnum, $dbpfx) == 1 || $_SESSION['rol02']=='1') {
		// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=El acceso a esta función es sólo para administradores');
	}

	echo '	<script type="text/javascript">
	function marcar(source) {
		checkboxes=document.getElementsByTagName("input"); //obtenemos todos los controles del tipo Input
		//recoremos todos los controles
		for(i=0;i<checkboxes.length;i++) {
			//solo si es un checkbox entramos
			if(checkboxes[i].type == "checkbox"){
				checkboxes[i].checked=source.checked; //si es un checkbox le damos el valor del checkbox que lo llamó (Marcar/Desmarcar Todos)
			}
		}
	}
	</script>';

	echo '
	<div class="page-content">'."\n";

	// --- errores ---
	if($_SESSION['error'] != ''){
		
		echo '
		<div class="row">
			<div class="col-md-12">
					<span class="alerta">' . $_SESSION['error']['rol'] . '</span>
					<span class="alerta">' . $_SESSION['error']['excede'] . '</span>
			</div>
		</div>'."\n";

		unset($_SESSION['error']);
	}


	echo '
		<div class="row">
			<div class="col-md-12">
					<div class="content-box-header">
					<div class="panel-title">
							<h2>' . $lang['CREAR'] . '</h2> 
					</div>
					</div>
			</div>
		</div>
		<br>'."\n";

	echo '
		<div class="row">
		<form name="boletinform" action="boletines.php?accion=procesar" method="post" enctype="multipart/form-data">
			<div class="col-md-8 panel-body">
				<legend class="legend">' . $lang['TÍTULO'] . '</legend>
				<input type="text" class="form-control" name="titulo" placeholder="' . $lang['Título'] . '" required value="' . $_SESSION['boletin']['titulo'] . '">
			</div>
		</div>	

		<div class="row">
			<div class="col-md-8 panel-body">
				<legend><big>' . $lang['CONTENIDO'] . '</big></legend>
				<textarea name="contenido" class="form-control" cols="102" rows="15" placeholder="' . $lang['Escribe es contenido del boletín'] . '" required >' . $_SESSION['boletin']['contenido'] .'</textarea>
			</div>
		</div>

		<div class="row">
			<div class="col-md-8 panel-body">
				<legend><big>' . $lang['AÑADIR ARCHIVO'] . ' <small>' . $lang['puede ser una imagen o un pdf'] . '</small></big></legend> 
				<input type="file" name="archivo" />
			</div>
		</div>

		<div class="row">
			<div class="col-md-8 panel-body">
				<legend class="legend">' . $lang['USUARIOS QUE VERÁN EL BOLETÍN'] . '</legend>

				<br>
				<table>
					<tr>
						<td>
							<input type="checkbox" onclick="marcar(this);" /> <small><b>' . $lang['Marcar/Desmarcar Todos'] . '</b></small>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="codigo[10]" value="1" ';

							if($_SESSION['boletin']['codigo10'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['GERENTE'] . '</big>
						</td>
						<td>		 
							<input type="checkbox" name="codigo[12]" value="1" ';

							if($_SESSION['boletin']['codigo12'] == 1) { echo ' checked="checked"'; }

				echo '					
							/><big>' . $lang['ASISTENTE'] . '</big>
						</td>
						<td>
							<input type="checkbox" name="codigo[15]" value="1" ';

							if($_SESSION['boletin']['codigo15'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['JEFE DE TALLER'] . '</big>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="codigo[20]" value="1" ';

							if($_SESSION['boletin']['codigo20'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['VALUADORES'] . '</big>
						</td>
						<td>		 
							<input type="checkbox" name="codigo[30]" value="1" ';

							if($_SESSION['boletin']['codigo30'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['ASESORES'] . '</big>
						</td>
						<td>
							<input type="checkbox" name="codigo[40]" value="1" ';

							if($_SESSION['boletin']['codigo40'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['JEFE DE ÁREA'] . '</big>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="codigo[50]" value="1" ';

							if($_SESSION['boletin']['codigo50'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['ALMACÉN'] . '</big>
						</td>
						<td>		 
							<input type="checkbox" name="codigo[80]" value="1" ';

							if($_SESSION['boletin']['codigo80'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['CALIDAD'] . '</big>
						</td>
						<td>
							<input type="checkbox" name="codigo[90]" value="1" ';

							if($_SESSION['boletin']['codigo90'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['COBRANZA'] . '</big>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="codigo[100]" value="1" ';

							if($_SESSION['boletin']['codigo100'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['PAGOS'] . '</big>
						</td>
						<td>		 
							<input type="checkbox" name="codigo[60]" value="1" ';

							if($_SESSION['boletin']['codigo60'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['OPERADOR'] . '</big>
						</td>
						<td>
							<input type="checkbox" name="codigo[70]" value="1" ';

							if($_SESSION['boletin']['codigo70'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['AUXILIAR'] . '</big>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="codigo[75]" value="1" ';

							if($_SESSION['boletin']['codigo75'] == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['VIGILANCIA'] . '</big>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="row">		
			<div class="col-md-8 panel-body">
				<legend><big>' . $lang['VIGENCIA'] . ' <small>' . $lang['período de fechas en el que será mostrado el boletín'] . '</small></big></legend>

					<table>
						<tr>
							<td>' . $lang['DE:'] . '</td>
							<td>' . $lang['A:'] . '</td>
						</tr>
						<tr>
							<td>'."\n";
								// --- fecha ---
								//echo $fecha . ' ---<br>';
								require_once("calendar/tc_calendar.php");
								//instantiate class and set properties
								$myCalendar = new tc_calendar("fecha_publicacion", true);
								$myCalendar->setPath("calendar/");
								$myCalendar->setIcon("calendar/images/iconCalendar.gif");
								$myCalendar->setDate(date("d", time()), date("m", time()), date("Y", time()));
								$myCalendar->setYearInterval(2011, 2020);
								$myCalendar->setAutoHide(true, 5000);

								//output the calendar
								$myCalendar->writeScript();
				echo '
							</td>
							<td>'."\n";
								//instantiate class and set properties
								$sietedias = (time() + 604800);
								$myCalendar = new tc_calendar("fecha_vencimiento", true);
								$myCalendar->setPath("calendar/");
								$myCalendar->setIcon("calendar/images/iconCalendar.gif");
								$myCalendar->setDate(date("d", $sietedias), date("m", $sietedias), date("Y", $sietedias));
								$myCalendar->setYearInterval(2011, 2020);
								$myCalendar->setAutoHide(true, 5000);

								//output the calendar
								$myCalendar->writeScript();
								

				echo '
							</td> '."\n";

				echo '
					</table>
			</div>
		</div>

		<div class="row">
			<div class="col-md-8 panel-body">
			<input class="btn btn-primary" type="submit" name="' . $lang['PUBLICAR BOLETÍN'] . 'publicar" value="' . $lang['PUBLICAR BOLETÍN'] . '"/>
			</div>
		</div>'."\n";

		unset($_SESSION['boletin']);
}

elseif($accion === "procesar") {

	$funnum = 1170100; // Acceso a gestión de boletines
	
	if (validaAcceso($funnum, $dbpfx) == 1 || $_SESSION['rol02']=='1') {
		// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=El acceso a esta función es sólo para administradores');
	}

	/*
	echo 'TÍTULO ' . $titulo . "<br>";
	echo 'CONTENIDO ' . $contenido . "<br>";
	echo 'fecha ' . $fecha . "<br>";
	echo 'nom_doc ' . $nom_doc . "<br>";
	echo 'archivo ' . $_FILES['archivo']['name'] . "<br>";
	echo 'tamaño ' . $_FILES['archivo']['size'] . "<br>";
	print_r($codigo);
	echo 'codigo10 ' . $codigo['10'] . '<br>';
	echo 'codigo12 ' . $codigo['12'] . '<br>';
	echo 'codigo15 ' . $codigo['15'] . '<br>';
	echo 'codigo20 ' . $codigo['20'] . '<br>';
	echo 'codigo30 ' . $codigo['30'] . '<br>';
	echo 'codigo40 ' . $codigo['40'] . '<br>';
	echo 'codigo50 ' . $codigo['50'] . '<br>';
	echo 'codigo60 ' . $codigo['60'] . '<br>';
	echo 'codigo70 ' . $codigo['70'] . '<br>';
	echo 'codigo75 ' . $codigo['75'] . '<br>';
	echo 'codigo80 ' . $codigo['80'] . '<br>';
	echo 'codigo90 ' . $codigo['90'] . '<br>';
	echo 'codigo100 ' . $codigo['100'] . '<br>';
	*/

	// --- Limpiar datos y guardarlos en session ---
	// 
	$_SESSION['boletin']['codigo10'] = $codigo['10'];
	$_SESSION['boletin']['codigo12'] = $codigo['12'];
	$_SESSION['boletin']['codigo15'] = $codigo['15'];
	$_SESSION['boletin']['codigo20'] = $codigo['20'];
	$_SESSION['boletin']['codigo30'] = $codigo['30'];
	$_SESSION['boletin']['codigo40'] = $codigo['40'];
	$_SESSION['boletin']['codigo50'] = $codigo['50'];
	$_SESSION['boletin']['codigo60'] = $codigo['60'];
	$_SESSION['boletin']['codigo70'] = $codigo['70'];
	$_SESSION['boletin']['codigo75'] = $codigo['75'];
	$_SESSION['boletin']['codigo80'] = $codigo['80'];
	$_SESSION['boletin']['codigo90'] = $codigo['90'];
	$_SESSION['boletin']['codigo100'] = $codigo['100'];
	$_SESSION['boletin']['nom_doc'] = $archivo;

	$nom_doc = preparar_entrada_bd($nom_doc);
	$titulo = preparar_entrada_bd($titulo); $_SESSION['boletin']['titulo'] = $titulo;
	$contenido = preparar_entrada_bd($contenido); $_SESSION['boletin']['contenido'] = $contenido;
	$fecha = preparar_entrada_bd($fecha); $_SESSION['boletin']['fecha'] = $fecha;

	// --- verificar variables ---
	$error = 'no';

	if($titulo == '' || $contenido == '' || $fecha_publicacion == '' || $fecha_vencimiento == '' ){
		$error = 'si';
	}

	if($codigo == ''){
		$error = 'si';
		$_SESSION['error']['rol'] = 'Debes seleccionar almenos un tipo de usuario';
	}

	// --- Procesar archivo ---
	$tammax = 2000000;
	if($_FILES['archivo']['size']>$tammax){
		$error = 'si';
		$_SESSION['error']['excede'] .= 'Error, la imagen ' . $_FILES['archivo']['name'] . ' excede el tamaño permitido de 2 MB, intente nuevamente'.'<br>';
	}

	if($error == 'si'){
		echo 'hubo error';
		redirigir('boletines.php?accion=crear');
	}

	// --- Preparar registro en BD ---
	if ($error == 'no') {
		// --- Crear variable de correspondencia ---
		$correspondencia = '';
		foreach ($codigo as $key => $value) {
			if($value == 1) { // --- se agrega ---
				$correspondencia .= $key . '|';
			}
		}

		// --- Registrar en BD ---
		unset($sql_data_array);
		$sql_data_array = [
			'boletin_titulo' => $titulo,
			'boletin_contenido' => $contenido,
			'boletin_fecha_publicacion' => $fecha_publicacion,
			'boletin_correspondecia' => $correspondencia,
			'boletin_activo' => 1,
			'boletin_fecha_vencimiento' => $fecha_vencimiento,
		];
		// --- Asignar nombre al archivo --
		$ultimo_id = ejecutar_db($dbpfx . 'boletines', $sql_data_array, 'insertar');
		$info = pathinfo($_FILES['archivo']['name']);
		$nombre_archivo = 'boletin-' . $ultimo_id . '-' . date('Y-m-d-H-i-s') . '-' . $_FILES['archivo']['name'];
		// --- Subir archivo a carpeta de documentos ---		
		if(move_uploaded_file($_FILES['archivo']['tmp_name'], DIR_DOCS . $nombre_archivo) && $_FILES['archivo']['size'] <= $tammax){
			// echo '<br>Se subió el archivo<br>';
			// --- Actualizar nombre en BD ---
			unset($sql_data_array);
			$sql_data_array = [
				'boletin_archivo' => $nombre_archivo,
			];
			$parametros = "boletin_id = '" . $ultimo_id . "'";
			ejecutar_db($dbpfx . 'boletines', $sql_data_array, 'actualizar', $parametros);
		}
		// --- Termino registro en bd ---
		unset($_SESSION['boletin']);
		$_SESSION['mensaje']['publicado'] = 'Se publicó el boletín ' .  $titulo .'<br>';
		redirigir('boletines.php?accion=gestionar');
	}

}

elseif($accion === "editar"){
	
	$funnum = 1170100; // Acceso a gestión de boletines
	
	if (validaAcceso($funnum, $dbpfx) == 1 || $_SESSION['rol02']=='1') {
		// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=El acceso a esta función es sólo para administradores');
	}
	
	echo '
	<script type="text/javascript">
	function marcar(source) {
		checkboxes=document.getElementsByTagName("input"); //obtenemos todos los controles del tipo Input
		//recoremos todos los controles
		for(i=0;i<checkboxes.length;i++) {
			//solo si es un checkbox entramos
			if(checkboxes[i].type == "checkbox"){
				checkboxes[i].checked=source.checked; //si es un checkbox le damos el valor del checkbox que lo llamó (Marcar/Desmarcar Todos)
			}
		}
	}
	</script>'."\n";
	
	// --- Consultar Info del boletín ---
	$preg_boletin = "SELECT * FROM " . $dbpfx . "boletines WHERE boletin_id = '$boletin_id'";
	$mat_boletin = mysql_query($preg_boletin) or die("ERROR: Fallo selección!" . $preg_boletin);
		$boletin_info = mysql_fetch_array($mat_boletin);

	// --- Consultar correspondencia ---
		$array_correspondencia = explode("|", $boletin_info['boletin_correspondecia']);
	foreach($array_correspondencia as $key => $value){
		//echo 'key ' . $key . ' Value ' . $value . '<br>';
		if($value == '10'){ $codigo_10 = 1; }
		if($value == '12'){ $codigo_12 = 1; }
		if($value == '15'){ $codigo_15 = 1; }
		if($value == '20'){ $codigo_20 = 1; }
		if($value == '30'){ $codigo_30 = 1; }
		if($value == '40'){ $codigo_40 = 1; }
		if($value == '50'){ $codigo_50 = 1; }
		if($value == '80'){ $codigo_80 = 1; }
		if($value == '90'){ $codigo_90 = 1; }
		if($value == '100'){ $codigo_100 = 1; }
		if($value == '60'){ $codigo_60 = 1; }
		if($value == '70'){ $codigo_70 = 1; }
		if($value == '75'){ $codigo_75 = 1; }
	}
	
	
	if($_SESSION['boletin']['codigo10'] != ''){
		$codigo_10 = $_SESSION['boletin']['codigo10'];
	}
	if($_SESSION['boletin']['codigo12'] != ''){
		$codigo_12 = $_SESSION['boletin']['codigo12'];
	}
	if($_SESSION['boletin']['codigo15'] != ''){
		$codigo_15 = $_SESSION['boletin']['codigo15'];
	}
	if($_SESSION['boletin']['codigo20'] != ''){
		$codigo_20 = $_SESSION['boletin']['codigo20'];
	}
	if($_SESSION['boletin']['codigo30'] != ''){
		$codigo_30 = $_SESSION['boletin']['codigo30'];
	}
	if($_SESSION['boletin']['codigo40'] != ''){
		$codigo_40 = $_SESSION['boletin']['codigo40'];
	}
	if($_SESSION['boletin']['codigo50'] != ''){
		$codigo_50 = $_SESSION['boletin']['codigo50'];
	}
	if($_SESSION['boletin']['codigo80'] != ''){
		$codigo_80 = $_SESSION['boletin']['codigo80'];
	}
	if($_SESSION['boletin']['codigo90'] != ''){
		$codigo_90 = $_SESSION['boletin']['codigo90'];
	}
	if($_SESSION['boletin']['codigo100'] != ''){
		$codigo_100 = $_SESSION['boletin']['codigo100'];
	}
	if($_SESSION['boletin']['codigo60'] != ''){
		$codigo_60 = $_SESSION['boletin']['codigo60'];
	}
	if($_SESSION['boletin']['codigo70'] != ''){
		$codigo_70 = $_SESSION['boletin']['codigo70'];
	}
	if($_SESSION['boletin']['codigo75'] != ''){
		$codigo_75 = $_SESSION['boletin']['codigo75'];
	}
	
	// --- CONTENIDO A MOSTRAR EN EL FORMULARIO ---
	if($_SESSION['boletin']['titulo'] == ''){
		$titulo = $boletin_info['boletin_titulo'];
	} else{
		$titulo = $_SESSION['boletin']['titulo'];
	}
	
	if($_SESSION['boletin']['contenido'] == ''){
		$contenido = $boletin_info['boletin_contenido'];
	} else{
		$contenido = $_SESSION['boletin']['contenido'];
	}
	
	echo '
	<div class="page-content">'."\n";

	// --- errores ---
	if($_SESSION['error'] != ''){
		
		echo '
		<div class="row">
			<div class="col-md-12">
					<span class="alerta">' . $_SESSION['error']['rol'] . '</span>
					<span class="alerta">' . $_SESSION['error']['excede'] . '</span>
			</div>
		</div>'."\n";

		unset($_SESSION['error']);
	}


	echo '
		<div class="row">
			<div class="col-md-12">
					<div class="content-box-header">
					<div class="panel-title">
							<h2>' . $lang['EDICION DE BOLETÍN'] . '</h2> 
					</div>
					</div>
			</div>
		</div>
		<br>'."\n";

	
	echo '
		<div class="row">
		<form name="boletinform" action="boletines.php?accion=procesar_edicion" method="post" enctype="multipart/form-data" name="procesar_comisiones">
			<div class="col-md-8 panel-body">
				<legend class="legend">' . $lang['TÍTULO'] . '</legend>
				<input type="text" class="form-control" name="titulo" placeholder="' . $lang['Título'] . '" required value="' . $titulo . '">
			</div>
		</div>	

		<div class="row">
			<div class="col-md-8 panel-body">
				<legend><big>' . $lang['CONTENIDO'] . '</big></legend>
				<textarea name="contenido" class="form-control" cols="102" rows="15" placeholder="Escribe es contenido del boletín" required >' . $contenido . '</textarea>
			</div>
		</div>'."\n";
	
	// --- si el boletín contiene un documento se muestra ---
	if($boletin_info['boletin_archivo'] != ''){

		//echo 'El boletín tiene archivo';
		$extencion = explode(".", $boletin_info['boletin_archivo']);

		// --- si el archivo es una imagen desplegarla ---
		if($extencion[01] == 'jpg' || $extencion[01] == 'JPG' || $extencion[01] == 'jpeg' || $extencion[01] == 'JPEG' || $extencion[01] == 'gif' || $extencion[01] == 'GIF' || $extencion[01] == 'png' || $extencion[01] == 'PNG'){
				
			echo '
				<div class="row">
					<div class="col-md-8 panel-body">
						<legend><big>' . $lang['IMAGEN ACTUAL'] . '</big></legend> 
						<br>
						<img src="' . DIR_DOCS . $boletin_info['boletin_archivo'] . '" alt="">
					</div>
				</div>'."\n";

		} else{

			// --- mostrar link del documento ---
			echo '
				<div class="row">
					<div class="col-md-8 panel-body">
						<legend><big>' . $lang['DOCUMENTO ACTUAL'] . '</big></legend><b>' . $lang['Click en la imagen para ver el documento anexo:'] . '
						<br> ' . $boletin_info['boletin_archivo'] . '</b>
						<br>
						<a href="' . DIR_DOCS . $boletin_info['boletin_archivo'] . '" target="_blank">
								<img src="' . DIR_DOCS . 'documento.png" width="48" border="0">
						</a>
					</div>
				</div>'."\n";
		}
	}
	
	echo '
		<div class="row">
			<div class="col-md-8 panel-body">
				<legend><big>' . $lang['NUEVO ARCHIVO'] . ' <small>' . $lang['puede ser una imagen o un pdf'] . '</small></big></legend> 
				<input type="file" name="archivo" />
				<input type="radio" name="quitar_arch" value="0"';
				
				if($boletin_info['boletin_archivo'] != ''){
					echo ' checked="checked"';
				}
	echo '
				><big>Conservar archivo</big>
				<input type="radio" name="quitar_arch" value="1"><big>Quitar archivo</big>
			</div>
		</div>

		<div class="row">
			<div class="col-md-8 panel-body">
				<legend class="legend">' . $lang['USUARIOS QUE VERÁN EL BOLETÍN'] . '</legend>

				<br>
				<table>
					<tr>
						<td>
							<input type="checkbox" onclick="marcar(this);" /> <small><b> ' . $lang['Marcar/Desmarcar Todos'] . ' </b></small>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="codigo[10]" value="1" ';

							if($codigo_10 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['GERENTE'] . '</big>
						</td>
						<td>		 
							<input type="checkbox" name="codigo[12]" value="1" ';

							if($codigo_12 == 1) { echo ' checked="checked"'; }

				echo '					
							/><big>' . $lang['ASISTENTE'] . '</big>
						</td>
						<td>
							<input type="checkbox" name="codigo[15]" value="1" ';

							if($codigo_15 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['JEFE DE TALLER'] . '</big>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="codigo[20]" value="1" ';

							if($codigo_20 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['VALUADORES'] . '</big>
						</td>
						<td>		 
							<input type="checkbox" name="codigo[30]" value="1" ';

							if($codigo_30 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['ASESORES'] . '</big>
						</td>
						<td>
							<input type="checkbox" name="codigo[40]" value="1" ';

							if($codigo_40 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['JEFE DE ÁREA'] . '</big>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="codigo[50]" value="1" ';

							if($codigo_50 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['ALMACÉN'] . '</big>
						</td>
						<td>		 
							<input type="checkbox" name="codigo[80]" value="1" ';

							if($codigo_80 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['CALIDAD'] . '</big>
						</td>
						<td>
							<input type="checkbox" name="codigo[90]" value="1" ';

							if($codigo_90 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['COBRANZA'] . '</big>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="codigo[100]" value="1" ';

							if($codigo_100 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['PAGOS'] . 'PAGOS</big>
						</td>
						<td>		 
							<input type="checkbox" name="codigo[60]" value="1" ';

							if($codigo_60 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['OPERADOR'] . '</big>
						</td>
						<td>
							<input type="checkbox" name="codigo[70]" value="1" ';

							if($codigo_70 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['AUXILIAR'] . '</big>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="codigo[75]" value="1" ';

							if($codigo_75 == 1) { echo ' checked="checked"'; }

				echo '
							/><big>' . $lang['VIGILANCIA'] . '</big>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="row">		
			<div class="col-md-8 panel-body">
				<legend><big>' . $lang['VIGENCIA'] . ' <small>' . $lang['Período de fechas en el que será mostrado el boletín'] . '</small></big></legend>

					<table>
						<tr>
							<td>
								' . $lang['ACTUAL:'] . ' <b>' . date("d-m-Y", strtotime($boletin_info['boletin_fecha_publicacion'])) . '</b><br>' . $lang['DE:'] . ' 
							</td>
							<td>
								' . $lang['ACTUAL:'] . ' <b>' . date("d-m-Y", strtotime($boletin_info['boletin_fecha_vencimiento'])) . '</b><br>' . $lang['A:'] . '
							</td>
						</tr>
						<tr>
							<td>'."\n";
									
								$inicio = strtotime($boletin_info['boletin_fecha_publicacion']);
								$venc = strtotime($boletin_info['boletin_fecha_vencimiento']);
								// --- fecha ---
								//echo $fecha . ' ---<br>';
								require_once("calendar/tc_calendar.php");
								//instantiate class and set properties
								$myCalendar = new tc_calendar("fecha_publicacion", true);
								$myCalendar->setPath("calendar/");
								$myCalendar->setIcon("calendar/images/iconCalendar.gif");
								$myCalendar->setDate(date("d", $inicio), date("m", $inicio), date("Y", $inicio));
								$myCalendar->setYearInterval(2011, 2020);
								$myCalendar->setAutoHide(true, 5000);

								//output the calendar
								$myCalendar->writeScript();
				echo '
							</td>
							<td>'."\n";
								//instantiate class and set properties
								$myCalendar = new tc_calendar("fecha_vencimiento", true);
								$myCalendar->setPath("calendar/");
								$myCalendar->setIcon("calendar/images/iconCalendar.gif");
								$myCalendar->setDate(date("d", $venc), date("m", $venc), date("Y", $venc));
								$myCalendar->setYearInterval(2011, 2020);
								$myCalendar->setAutoHide(true, 5000);

								//output the calendar
								$myCalendar->writeScript();
								

				echo '
							</td> '."\n";

				echo '
					</table>
			</div>
		</div>

		<div class="row">
			<div class="col-md-8 panel-body">
			<input type="hidden" name="boletin_id" value="' . $boletin_id . '">
			<input class="btn btn-primary" type="submit" name="publicar" value="' . $lang['TERMINAR EDICIÓN'] . '"/>
			</div>
		</div>'."\n";

		unset($_SESSION['boletin']);
	
}

elseif($accion === "procesar_edicion"){

	$funnum = 1170100; // Acceso a gestión de boletines
	
	if (validaAcceso($funnum, $dbpfx) == 1 || $_SESSION['rol02']=='1') {
		// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=El acceso a esta función es sólo para administradores');
	}

	/*
	echo 'TÍTULO ' . $titulo . "<br>";
	echo 'CONTENIDO ' . $contenido . "<br>";
	echo 'fecha ' . $fecha . "<br>";
	echo 'nom_doc ' . $nom_doc . "<br>";
	echo 'archivo ' . $_FILES['archivo']['name'] . "<br>";
	echo 'tamaño ' . $_FILES['archivo']['size'] . "<br>";
	print_r($codigo);
	echo 'codigo10 ' . $codigo['10'] . '<br>';
	echo 'codigo12 ' . $codigo['12'] . '<br>';
	echo 'codigo15 ' . $codigo['15'] . '<br>';
	echo 'codigo20 ' . $codigo['20'] . '<br>';
	echo 'codigo30 ' . $codigo['30'] . '<br>';
	echo 'codigo40 ' . $codigo['40'] . '<br>';
	echo 'codigo50 ' . $codigo['50'] . '<br>';
	echo 'codigo60 ' . $codigo['60'] . '<br>';
	echo 'codigo70 ' . $codigo['70'] . '<br>';
	echo 'codigo75 ' . $codigo['75'] . '<br>';
	echo 'codigo80 ' . $codigo['80'] . '<br>';
	echo 'codigo90 ' . $codigo['90'] . '<br>';
	echo 'codigo100 ' . $codigo['100'] . '<br>';
	*/

	// --- Limpiar datos y guardarlos en session ---
	// 
	$_SESSION['boletin']['codigo10'] = $codigo['10'];
	$_SESSION['boletin']['codigo12'] = $codigo['12'];
	$_SESSION['boletin']['codigo15'] = $codigo['15'];
	$_SESSION['boletin']['codigo20'] = $codigo['20'];
	$_SESSION['boletin']['codigo30'] = $codigo['30'];
	$_SESSION['boletin']['codigo40'] = $codigo['40'];
	$_SESSION['boletin']['codigo50'] = $codigo['50'];
	$_SESSION['boletin']['codigo60'] = $codigo['60'];
	$_SESSION['boletin']['codigo70'] = $codigo['70'];
	$_SESSION['boletin']['codigo75'] = $codigo['75'];
	$_SESSION['boletin']['codigo80'] = $codigo['80'];
	$_SESSION['boletin']['codigo90'] = $codigo['90'];
	$_SESSION['boletin']['codigo100'] = $codigo['100'];
	$_SESSION['boletin']['nom_doc'] = $archivo;

	$nom_doc = preparar_entrada_bd($nom_doc);
	$titulo = preparar_entrada_bd($titulo); $_SESSION['boletin']['titulo'] = $titulo;
	$contenido = preparar_entrada_bd($contenido); $_SESSION['boletin']['contenido'] = $contenido;
	$fecha = preparar_entrada_bd($fecha); $_SESSION['boletin']['fecha'] = $fecha;

	// --- verificar variables ---
	
	$error = 'no';

	if($titulo == '' || $contenido == '' || $fecha_publicacion == '' || $fecha_vencimiento == '' ){

		$error = 'si';

	}

	if($codigo == ''){

		$error = 'si';
		$_SESSION['error']['rol'] = 'Debes seleccionar almenos un tipo de usuario';

	}

	
	// --- Procesar archivo ---
	$tammax = 2000000;
	if($_FILES['archivo']['size']>$tammax){

		$error = 'si';
		$_SESSION['error']['excede'] .= 'Error, la imagen ' . $_FILES['archivo']['name'] . ' excede el tamaño permitido de 2 MB, intente nuevamente'.'<br>';

	}

	if($error == 'si'){

		echo 'hubo error';
		redirigir('boletines.php?accion=editar&boletin_id=' . $boletin_id);

	}

	//echo ' paso 1<br>';
	// --- Preparar registro en BD ---
	if ($error == 'no') {

		// --- Crear variable de correspondencia ---
		$correspondencia = '';

		foreach ($codigo as $key => $value) {

			//echo $key . ' -> ' . $value . '<br>';
			if($value == 1){ // --- se agrega ---
				if($correspondencia == ''){
					$correspondencia .= $key;
				} else{
					$correspondencia .= '|' . $key;
				}
			}
		}
		
		// --- Registrar en BD ---
		unset($sql_data_array);
		$sql_data_array = [
			'boletin_titulo' => $titulo,
			'boletin_contenido' => $contenido,
			'boletin_fecha_publicacion' => $fecha_publicacion,
			'boletin_correspondecia' => $correspondencia,
			'boletin_activo' => 1,
			'boletin_fecha_vencimiento' => $fecha_vencimiento,
		];
		
		if($quitar_arch == 1){
			$sql_data_array['boletin_archivo'] = '';
		}

		$parametros = " boletin_id = '" . $boletin_id . "'";
		ejecutar_db($dbpfx . 'boletines', $sql_data_array, 'actualizar', $parametros);
		
		// --- Actualizar archivo --
		if($_FILES['archivo'] != ''){
			$info = pathinfo($_FILES['archivo']['name']);
			$nombre_archivo = 'boletin-' . $boletin_id . '-' . date('Y-m-d-H-i-s') . '-' . $_FILES['archivo']['name'];

			// --- Subir archivo a carpeta de documentos ---		
			if(move_uploaded_file($_FILES['archivo']['tmp_name'], DIR_DOCS . "/" . $nombre_archivo) && $_FILES['archivo']['size'] <= $tammax){

				// echo '<br>Se subió el archivo<br>';
				// --- Actualizar nombre en BD ---
				unset($sql_data_array);
					$sql_data_array = [
					'boletin_archivo' => $nombre_archivo,
				];
				$parametros = " boletin_id = '" . $boletin_id . "'";
				ejecutar_db($dbpfx . 'boletines', $sql_data_array, 'actualizar', $parametros);
			}
		}
		

		// --- Termino registro en bd ---
		unset($_SESSION['boletin']);
		$_SESSION['mensaje']['publicado'] = 'Se actualizó el boletín ' .  $titulo .'<br>';
		redirigir('boletines.php?accion=gestionar');
		
	}

}

elseif($accion === "activar"){
	
	$funnum = 1170100; // Acceso a gestión de boletines
	
	if (validaAcceso($funnum, $dbpfx) == 1 || $_SESSION['rol02']=='1') {
		// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=El acceso a esta función es sólo para administradores');
	}
	
	if($desactivar == 1) {
		
			// --- desactivar boletín ------
			$sql_data_array = [
				'boletin_activo' => '1'
			];
			$parametros = " boletin_id ='" . $boletin_id . "'";
			ejecutar_db($dbpfx . 'boletines', $sql_data_array, 'actualizar', $parametros);
			
			$_SESSION['mensaje']['desactivado'] = 'Boletín ' . $boletin_id . ' activado';
			redirigir('boletines.php?accion=gestionar');
		
	} else {
		
		echo '
		<h2>' . $lang['¿Estás seguro que quieres activar el boletín'] . ' ' . $boletin_id . '?, ' . $lang['será visible por los usuarios.'] . '</h2>
		<table>
			<tr>
				<td><a href="boletines.php?accion=activar&boletin_id=' . $boletin_id . '&desactivar=1"><button type="button" class="btn btn-success">' . $lang['SI, activar boletín'] . '</button></a></td>
				<td><a href="boletines.php?accion=consultar&boletin_id=' . $boletin_id . '"><button type="button" class="btn btn-danger">' . $lang['NO, regresar'] . '</button></a></td>
			</tr>
		</table>'."\n";
		
	}
	
}

elseif($accion === "desactivar"){
	
	$funnum = 1170100; // Acceso a gestión de boletines
	
	if (validaAcceso($funnum, $dbpfx) == 1 || $_SESSION['rol02']=='1') {
		// Acceso autorizado
	} else {
		redirigir('usuarios.php?mensaje=El acceso a esta función es sólo para administradores');
	}
	
	if($desactivar == 1) {
		
			// --- desactivar boletín ------
			$sql_data_array = [
				'boletin_activo' => '0'
			];
			$parametros = " boletin_id ='" . $boletin_id . "'";
			ejecutar_db($dbpfx . 'boletines', $sql_data_array, 'actualizar', $parametros);
			
			$_SESSION['mensaje']['desactivado'] = 'Boletín ' . $boletin_id . ' desactivado';
			redirigir('boletines.php?accion=gestionar');
		
	} else {
		
		echo '
		<h2>' . $lang['¿Estás seguro que quieres desactivar el boletín'] . ' ' . $boletin_id . '?, ' . $lang['ya no será visible por los usuarios.'] . '</h2>
		<table>
			<tr>
				<td><a href="boletines.php?accion=desactivar&boletin_id=' . $boletin_id . '&desactivar=1"><button type="button" class="btn btn-success">' . $lang['SI, desactivar boletín'] . '</button></a></td>
				<td><a href="boletines.php?accion=consultar&boletin_id=' . $boletin_id . '"><button type="button" class="btn btn-danger">' . $lang['NO, regresar'] . '</button></a></td>
			</tr>
		</table>'."\n";
		
	}
	
}

elseif($accion === "listar") {
	
// ------ Obtenemos la lista completa de boletines locales
	$preg_boletines = "SELECT boletin_id, boletin_fecha_publicacion FROM " . $dbpfx . "boletines WHERE boletin_correspondecia LIKE '%" . $_SESSION['codigo'] . "|%' AND boletin_activo = '1' ORDER BY boletin_fecha_publicacion DESC";
	$matr_boletines = mysql_query($preg_boletines) or die("ERROR: selección completa de boletines! " . $preg_boletines);
	$num_locales = mysql_num_rows($matr_boletines); // --- total de boletines locales ---
	$bolet = array();
	while($bols = mysql_fetch_assoc($matr_boletines)) {
		$bolet[] = [
		'origen' => $lang['Local'],
		'boletin_id' => $bols['boletin_id'],
		'fe_pub' => strtotime($bols['boletin_fecha_publicacion'])
		];
	}

// ------Obtenemos la lista de boletines ASE
	mysql_close();
	mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
	mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');

	$preg_ase = "SELECT boletin_id, boletin_fecha_publicacion FROM boletines WHERE ( boletin_correspondecia LIKE '%" . $_SESSION['codigo'] . "%' OR boletin_correspondecia = '" . $_SESSION['codigo'] . "' ) AND boletin_activo = '1' ORDER BY boletin_fecha_publicacion DESC";
	$matr_ase = mysql_query($preg_ase) or die("ERROR: selección de boletines ASE! " . $preg_ase);
	$num_ase = mysql_num_rows($matr_ase); // --- total de boletines AutoShop Easy ---
	while($bols = mysql_fetch_assoc($matr_ase)) {
		$bolet[] = [
		'origen' => 'AutoShop Easy',
		'boletin_id' => $bols['boletin_id'],
		'fe_pub' => strtotime($bols['boletin_fecha_publicacion'])
		];
	}

// ------ Reconectamos a base de datos de la instancia
	mysql_close();
	mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
	mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);

// ------ Ordenar boletines por fecha de publicación

	usort($bolet, function($a, $b) {
		return $b['fe_pub'] - $a['fe_pub'];
	});

// ------ Definimos el num. de prod. x pag. y total de pags.
	$num_resultados = $num_locales + $num_ase;
	$renglones = 20;
	$paginas = (round(($num_resultados / $renglones) + 0.49999999) - 1);	

// ------ Si no existe $pagina la página se coloca en 0
	if(!isset($pagina)) { $pagina = 0;}

// ------ Calculamos los resultados que deben de ser consultados ---
	$inicial = $pagina * $renglones;
	$final = $inicial + $renglones;
	$indicador = $inicial + 1;

// ------ Comienza cuerpo de la pag. ---

	// --- errores ---
/*	if($_SESSION['mensaje'] != ''){
		
		echo '
		<div class="row">
			<div class="col-md-12">
					<span class="alerta">' . $_SESSION['mensaje']['publicado'] . '</span>
			</div>
		</div>
		<br>'."\n";

		unset($_SESSION['mensaje']);
	} */
	echo '
				<div class="row"> <!-box header del título. -->
					<div class="col-md-12">
						<div class="content-box-header">
							<div class="panel-title">
								<h2>' . $lang['Mis boletines'] . ': ' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '</h2> 
							</div>
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-md-12 ">
						<div class="col-md-12">
							<div id="content-tabla">
								<table cellspacing="0" class="table-new">
									<tr>
										<th>' . $lang['BOLETÍN'] . '</th>
										<th>' . $lang['Origen'] . '</th>
										<th>' . $lang['TÍTULO'] . '</th>
										<th>' . $lang['LEIDO?'] . '</th>
										<th>' . $lang['FECHA_PUBLICACION'] . '</th>
										<th>' . $lang['FECHA_VENCIMIENTO'] . '</th>
									</tr>'."\n";

	$clase = 'claro';
//	echo 'Inicial: ' . $inicial . '<br>';
//	echo 'Final: ' . $final . '<br>';
//	while($boletines = mysql_fetch_array($matr_pag_actual)){

	for($i = $inicial; $i < ($inicial + $renglones) ; $i++) {
		$prefijo = $dbpfx;
// ------Consultando el boletín en la base de datos apropiada
		if($bolet[$i]['origen'] == 'AutoShop Easy') {
			mysql_close();
			mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
			mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
			$prefijo = '';
		}
		if($bolet[$i]['boletin_id'] < 1) { break; } // Si ya no hay boletines, sale del loop
// ------Consultar si ya leiste el boletín
		$preg_leido = "SELECT lectura_boletin_id FROM " . $prefijo . "boletines_leidos WHERE boletin_id = '" . $bolet[$i]['boletin_id'] . "' AND usuario_id = '" . $_SESSION['usuario'] . "' ";
		if($bolet[$i]['origen'] == 'AutoShop Easy') {
			$preg_leido .= " AND instancia = '" . $dbpfx . "' ";
		}
		$preg_leido .= " LIMIT 1";
		$mat_leido = mysql_query($preg_leido) or die("ERROR: Fallo selección! " . $preg_leido);
		$leido = mysql_num_rows($mat_leido);

		if($leido == 1) {
			$imagen = '<img src="idiomas/' . $idioma . '/imagenes/ok.png" alt="leido" title="leido">';
		} else {
			$imagen = '<img src="idiomas/' . $idioma . '/imagenes/no-16.png" alt="no leido" title="no leido">';
		}

		$preg1 = "SELECT * FROM " . $prefijo . "boletines WHERE boletin_id = '" . $bolet[$i]['boletin_id'] . "'";
//			echo 'Local: ' . $preg1 . '<br>';
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de boletín! " . $preg1);
		$boletines = mysql_fetch_array($matr1);
		if($bolet[$i]['origen'] == 'AutoShop Easy') {
			mysql_close();
			mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
			mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);
		}

		echo '
									<tr class="' . $clase . '">
										<td>
											<big><a href="boletines.php?accion=leer&boletin_id=' . $bolet[$i]['boletin_id'];
		if($bolet[$i]['origen'] == 'AutoShop Easy') {
			echo '&origen=ase';
		}
		echo '">
												' . $bolet[$i]['boletin_id'] . '
											</a></big>
										</td>
										<td><big>' . $bolet[$i]['origen'] . '</big></td>
										<td style="text-align: left !important;">
											<big><a href="boletines.php?accion=leer&boletin_id=' . $bolet[$i]['boletin_id'];
		if($bolet[$i]['origen'] == 'AutoShop Easy') {
			echo '&origen=ase';
		}
		echo '">' . $boletines['boletin_titulo'] . '</a></big>
										</td>
										<td><big>' . $imagen . '</big></td>
										<td><big>' . date('Y-m-d', strtotime($boletines['boletin_fecha_publicacion'])) . '</big></td>
										<td><big>' . date('Y-m-d', strtotime($boletines['boletin_fecha_vencimiento'])) . '</big></td>
									</tr>'."\n";
		if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
	}

	echo '
									<tr class="' . $clase . '">
										<td colspan="4">
											<big><b>mostrando ' . $indicador . ' a ' . $i . ' de ' . $num_resultados . '</b></big>
										</td>
										<td colspan="2">
											<big>';
	echo '											<a href="boletines.php?accion=listar&pagina=0">' . $lang['Primera'] . ' | </a>'."\n";
	if($pagina > 0) {
		echo '											<a href="boletines.php?accion=listar&pagina=' . ($pagina - 1) . '">' . $lang['Anterior'] . ' | </a>'."\n";
	}
	if(($pagina + 1) < $paginas){
		echo '											<a href="boletines.php?accion=listar&pagina=' . ($pagina + 1) . '">' . $lang['Siguiente'] . ' | </a>'."\n";
	}
	echo '											<a href="boletines.php?accion=listar&pagina=' . $paginas . '">' . $lang['Ultima'] . '</a>'."\n";
	echo '											</big>
										</td>
									</tr>'."\n";
	echo '
								</table>
							</div>
						</div>
					</div>
				</div>'."\n";
}

elseif($accion == 'leer') {

// ------ Consultar Info del boletín ---
	$prefijo = $dbpfx;
	if($origen == 'ase') {
// ------ Si el boletín es ASE, desconectar de la instancia y conectar con ASEBase
		mysql_close();
		mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
		mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
		$prefijo = '';
	}
// ------ Consular correspondencia del boletín ---
	$preg_boletin = "SELECT boletin_correspondecia FROM " . $prefijo . "boletines WHERE boletin_id = '$boletin_id'";
	$mat_boletin = mysql_query($preg_boletin) or die("ERROR: Fallo selección!" . $preg_boletin);
	$boletin_info = mysql_fetch_assoc($mat_boletin);

// ------ Validar que el usuario tiene acceso al boletín ----
	$array_correspondencia = explode("|", $boletin_info['boletin_correspondecia']);
	$muestra_boletin = 0;

// ------ Hacer match con el código del usuario ---
	foreach($array_correspondencia as $key => $val){
		//echo 'Key ' . $key . ' - val ' . $val . '<br>';
		if($_SESSION['codigo'] == $val){
			//echo 'Mostrar boletin a usuario<br>';
			$muestra_boletin = 1;
			break;
		}
	}

	if($muestra_boletin == 0) {
		$_SESSION['msjerror'] = $lang['NoAcceso'];
		redirigir('boletines.php?accion=listar');
	}

	$preg_boletin = "SELECT * FROM " . $prefijo . "boletines WHERE boletin_id = '$boletin_id'";
	$mat_boletin = mysql_query($preg_boletin) or die("ERROR: Fallo selección!" . $preg_boletin);
	$boletin_info = mysql_fetch_array($mat_boletin);

	// --- contar el total de roles ---
	$total_cods_puesto = count($cod_puesto);
	// --- descontar el rol de sistemas y el rol de aseguradora ---
	$total_cods_puesto = $total_cods_puesto - 2;

	// --- Consultar correspondencia ---
	$array_correspondencia = explode("|", $boletin_info['boletin_correspondecia']);
	$total_correspondencia = count($array_correspondencia);

	//echo 'total roles ' . $total_roles . '<br>';
	//echo 'total correspondencia ' . $total_correspondencia . '<br>';

	if($total_correspondencia == $total_cods_puesto) {
		$dirigido_a = $lang['Todo el personal'];
	} else {
		$dirigido_a = '';
		foreach ($array_correspondencia as $key => $value) {
			//echo $key . ' -> ' . $value . '<br>';
			$dirigido_a .= $cod_puesto[$value] . ', ';
		}
	}

	echo '
				<div class="row">
					<div class="col-sm-12">
						<div class="content-box-header">
						<div class="panel-title">
								<h2>' . $lang['CONSULTAR'] . '</h2> 
						</div>
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-sm-8">
						<legend class="legend"><b>' . $boletin_info['boletin_titulo'] . '</b></legend>
					</div>
					<div class="col-sm-4">
						<b>' . $lang['Vigencia del boletín'] . '</b> Del ' . date('Y-m-d', strtotime($boletin_info['boletin_fecha_publicacion'])) . ' al ' .  date('Y-m-d', strtotime($boletin_info['boletin_fecha_vencimiento'])) . '<br>
						<b>' . $lang['Dirigido a:'] . '</b>' . $dirigido_a . '
					</div>
				</div>
				<div class="row">
					<div class="col-md-8 panel-body">
						<p style="line-height: 1.5; text-align : justify;">
						<big>' . nl2br($boletin_info['boletin_contenido']) . '</big>
						</p>
					</div>
				</div>'."\n";

// ------ Si el boletín contiene un documento se muestra ---
	if($boletin_info['boletin_archivo'] != '') {
		$extencion = explode(".", $boletin_info['boletin_archivo']);
		// ------ Si el archivo es una imagen, desplegarla ---
		if($extencion[01] == 'jpg' || $extencion[01] == 'JPG' || $extencion[01] == 'jpeg' || $extencion[01] == 'JPEG' || $extencion[01] == 'gif' || $extencion[01] == 'GIF' || $extencion[01] == 'png' || $extencion[01] == 'PNG'){
			echo '
				<div class="row">
					<div class="col-md-8 panel-body">
						<a href="';
			if($origen == 'ase') {
				echo 'imagenes/';
			} else {
				echo DIR_DOCS;
			}
			echo $boletin_info['boletin_archivo'] . '" target="_blank">
							' . $lang['Ver imgen en grande...'] .'
						</a>
						<br>
						<a href="';
			if($origen == 'ase') {
				echo 'imagenes/';
			} else {
				echo DIR_DOCS;
			}
			echo $boletin_info['boletin_archivo'] . '" target="_blank">'."\n";
			echo '						<img style="height:1200px; max-width:800px; height:auto; width:auto;" src="' . DIR_DOCS . $boletin_info['boletin_archivo'] . '" alt="">'."\n";
			echo '						</a>
					</div>
				</div>'."\n";
		} else {
			// ------ Mostrar link del documento ---
			echo '
				<div class="row">
					<div class="col-md-8 panel-body">
						<b>' . $lang['Click en la imagen para ver el documento anexo:'] .'
						<br> ' . $boletin_info['boletin_archivo'] . '</b>
						<br>
						<a href="';
			if($origen == 'ase') {
				echo 'imagenes/';
			} else {
				echo DIR_DOCS;
			}
			echo $boletin_info['boletin_archivo'] . '" target="_blank">
							<img src="' . DIR_DOCS . 'documento.png" width="48" border="0"></a>
					</div>
				</div>'."\n";
		}
	}

	echo '
				<div class="row">
					<div class="col-md-4 panel-body">
						<a href="boletines.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Listar boletines" title="Listar boletines"></a>
					</div>
				</div>'."\n";

// ------ Revisar si el boletín a fue leido previamnete ---
	$preg_leido = "SELECT lectura_boletin_id FROM " . $prefijo . "boletines_leidos WHERE boletin_id = '" . $boletin_info['boletin_id'] . "' AND usuario_id = '" . $_SESSION['usuario'] . "' ";
	if($origen == 'ase') {
		$preg_leido .= "AND instancia = '" . $dbpfx . "'";
	}
	$preg_leido .= "LIMIT 1";
	$mat_leido = mysql_query($preg_leido) or die("ERROR: Fallo selección! " . $preg_leido);
	$leido = mysql_num_rows($mat_leido);

	if($leido == 0) {
		//echo 'El usuario no ha leido el boletín<br>';
		// --- marcar como leido el boletín ---
		unset($sql_data_array);
		$sql_data_array = [
			'boletin_id' => $boletin_info['boletin_id'],
			'usuario_id' => $_SESSION['usuario'],
			'lectura_fecha' => date('Y-m-d H:i:s'),
		];
		if($origen == 'ase') {
			$sql_data_array['instancia'] = $dbpfx;
		}
		ejecutar_db($prefijo . 'boletines_leidos', $sql_data_array, 'insertar');
	}
	if($origen == 'ase') {
// ------ Reconectamos a base de datos de la instancia
		mysql_close();
		mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
		mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);
	}
}

echo '			</div>
		</div>
	</div>'."\n";

include('parciales/pie.php');
/* Archivo boletines.php */
/* AutoShop-Easy.com */
