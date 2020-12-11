<?php

if(basename($_SERVER['PHP_SELF'])=='pausar_sots.php' || basename($_SERVER['PHP_SELF'])=='actualiza_refacciones.php' || basename($_SERVER['PHP_SELF'])=='actualiza_alarmas.php' || basename($_SERVER['PHP_SELF'])=='recibe.php') {
// Acceso directo
} else {
	include('particular/config.php');
	mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
	mysql_select_db($dbnombre) or die('Falló la seleccion la DB');
	include_once('particular/comun.php');
	include_once('particular/estatus.php');
	include_once('parciales/valores.php');
	if(file_exists('../variables_aws.php')) {
		include_once('../variables_aws.php');
	}
}
	error_reporting(0);

	session_start();

//	if (basename($_SERVER['PHP_SELF'])=='seguimiento.php') {   }

	$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
	if($accion == '' && isset($_POST['accion'])) { $accion = $_POST['accion']; }

	function preparar_entrada_bd($string) {
	if (is_string($string)) {
		return trim(limpiar_cadena(stripslashes($string)));
	} elseif (is_array($string)) {
		reset($string);
		while (list($key, $value) = each($string)) {
			$string[$key] = preparar_entrada_bd($value);
		}
		return $string;
	} else {
		return $string;
	}
  }

	function limpiar_cadena($string) {
		$patterns = "/[<*°\'\\\[\{\]\}\!#\=$%&+\">]/";
		$replace = '';
		if (is_string($string)) {
			return preg_replace($patterns, $replace, $string);
		} elseif (is_array($string)) {
			reset($string);
			while (list($key, $value) = each($string)) {
				$string[$key] = limpiar_cadena($value);
			}
			return $string;
		}
	}

	function ejecutar_db($table, $data, $action = 'insertar', $parameters = '') {
		reset($data);
		if ($action == 'insertar') {
			$preg0 = "SHOW INDEXES FROM " . $table . " WHERE Key_name = 'PRIMARY'";
			$matr0 = mysql_query($preg0);
			$fila0 = mysql_fetch_assoc($matr0);
			$tablaid = $fila0['Column_name'];
			$query = 'INSERT INTO ' . $table . ' (';
			while (list($columns, ) = each($data)) {
				$query .= $columns . ', ';
			}
			$query = substr($query, 0, -2) . ') values (';
			reset($data);
			while (list(, $value) = each($data)) {
				switch ((string)$value) {
					case 'now()':
						$query .= 'now(), ';
						break;
					case 'null':
						$query .= 'null, ';
						break;
					default:
						$query .= '\'' . addslashes($value) . '\', ';
						break;
				}
			}
			$query = substr($query, 0, -2) . ')';
		} elseif ($action == 'actualizar') {
			$query = 'UPDATE ' . $table . ' SET ';
			while (list($columns, $value) = each($data)) {
				switch ((string)$value) {
					case 'now()':
						$query .= $columns . ' = now(), ';
						break;
					case 'null':
						$query .= $columns .= ' = null, ';
						break;
					default:
						$query .= $columns . ' = \'' . addslashes($value) . '\', ';
						break;
				}
			}
			$query = substr($query, 0, -2) . ' WHERE ' . $parameters;
		} elseif ($action == 'eliminar') {
			$query = 'DELETE FROM ' . $table . ' WHERE ' . $parameters;
		}
		$result = mysql_query($query) or die('Fallo el query: ' . $query);
		$nid = mysql_insert_id();
		if ($action == 'insertar') {
			if($nid < 1) {
				$preg1 = "SELECT * FROM " . $table . " ORDER BY $tablaid DESC LIMIT 1";
			} else {
				$preg1 = "SELECT * FROM " . $table . " WHERE $tablaid = '" . $nid . "'";
			}
			$matr1 = mysql_query($preg1) or die('Fallo la consulta del ultimo: ' . $preg1);
			$datos = mysql_fetch_assoc($matr1);
			$query = "INSERT INTO " . $table . " VALUES (";
			while (list(, $value) = each($datos)) {
				switch ((string)$value) {
					case 'now()':
						$query .= 'now(), ';
						break;
					case 'null':
						$query .= 'null, ';
						break;
					default:
						$query .= '\'' . addslashes($value) . '\', ';
						break;
				}
			}
			$query = substr($query, 0, -2) . ')';
		}
		$archivo = '../logs/' . time() . '.ase';
		$myfile = file_put_contents($archivo, $query.';'.PHP_EOL , FILE_APPEND | LOCK_EX);
		return $nid;
	}

	function bitacora($orden_id, $estatus, $dbpfx, $comentario = NULL, $interno = NULL, $sub_orden_id = NULL, $previa_id = NULL, $parausr = NULL, $etapa_com = NULL, $recordatorio = NULL, $deusuario = NULL) {
		if($deusuario == '') { $deusuario = $_SESSION['usuario']; }
		if(basename($_SERVER['PHP_SELF'])=='recibe.php') {
			$deusuario = '700';
		}
		if($previa_id != '') {
			$data = ['orden_id' => $orden_id, 'previa_id' => $previa_id, 'usuario' => $deusuario, 'bit_estatus' => $estatus];
			$bit_id = ejecutar_db($dbpfx . 'bitacora', $data, 'insertar');
		} else {
			$data = ['orden_id' => $orden_id, 'usuario' => $deusuario, 'bit_estatus' => $estatus];
			$bit_id = ejecutar_db($dbpfx . 'bitacora', $data, 'insertar');
		}
		if ($comentario!='') {
			$data = ['bit_id' => $bit_id, 'orden_id' => $orden_id, 'interno' => $interno, 'comentario' => $comentario, 'usuario' => $deusuario, 'sub_orden_id' => $sub_orden_id, 'para_usuario' => $parausr, 'etapa_com' => $etapa_com, 'recordatorio' => $recordatorio];
			ejecutar_db($dbpfx . 'comentarios', $data, 'insertar');
		}
	}

	function redirigir($url) {
		$host  = $_SERVER['HTTP_HOST'];
		//header('Location:' . $url);
		//	header('Location: https://' . $host . '/' . $url);
 		//exit();
 		if (!headers_sent()) {    
       		header('Location:' . $url);
        	exit();
        }
    	else {  
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>'; exit;
    	}
		
	}

	function limpiarString($texto) {
		$textoLimpio = preg_replace('/[^A-Za-z0-9_\.]/', '', $texto);
		return $textoLimpio;
	}

	function limpiarNumero($numero) {
		$numLimpio = preg_replace('/[^0-9\.-]/', '', $numero);
		return $numLimpio;
	}

	function encuentra_ultima($tratar){

		$Ecadena  =explode(' ', $tratar);
		// --- contamos cuantas palabras hay ---
		$Ccadena = count($Ecadena);
		// --- le restamos 1 ya que el array empieza de 0 ---
		$CRcadena = $Ccadena-1;
		// --- contamos los caracteres de la ultima palabra ---
		$Cletras = strlen($Ecadena[$CRcadena]);
		// --- contamos cuantos caracteres tiene la cadena completa ---
		$Cletras2 = strlen($tratar);
		// --- restamos ---
		$CTotal = $Cletras2-$Cletras;
		//seteamos lo que queremos mostrar
		$fact_num = substr($tratar,$CTotal,$Cletras2);

		return $fact_num;
	}

	function lista_documento ($orden_id, $estatus, $dbpfx, $presel, $previa_id, $omitir, $ayuda) {
		global $codigomon;
		global $restrictotoper;
		global $limitaaccdocs;
                global $bucket;
                global $aws_key;
                global $aws_secret;
                global $aws_region;
                global $aws_web_ap;

		$infomon = validaAcceso('1040065', $dbpfx);  // Valida acceso a mostrar información confidencial.
		$cambia_clasificación = validaAcceso('1025035', $dbpfx);  // Habilita acceso para cambiar clasificación.
		$cambia_visibilidad = validaAcceso('1025040', $dbpfx); // Habilita acceso para cambiar visibilidad.

		$pregunta = "SELECT d.* FROM " . $dbpfx . "documentos d, " . $dbpfx . "ordenes o, " . $dbpfx . "previas p WHERE";
		if($previa_id > 0) {
			$pregunta .= " p.previa_id = d.previa_id AND d.previa_id = '$previa_id'";
		} else {
			$pregunta .= " o.orden_id = d.orden_id AND o.orden_estatus != '90' AND d.orden_id = '$orden_id'";
		}

		$pregunta .= " AND (d.doc_clasificado = '0' OR d.doc_clasificado = '1') ";

		if($infomon == '1' || $_SESSION['codigo'] <= $codigomon) {
			//$pregunta .= " AND (doc_clasificado = '0' OR doc_clasificado = '1') ";
		} else {
			//$pregunta .= " AND doc_clasificado = '0'";
		}
/*
        // ------ Sección que limita los documentos que se permiten mostrar por infomon y su código de puesto
		if($limitaaccdocs == 1 || ($_SESSION['codigo'] == '60' || $_SESSION['codigo'] == '70' || $_SESSION['codigo'] == '75')) {
			if($limitaaccdocs != 1 && $restrictotoper == '1' ) {
				$pregunta .= " AND doc_usuario = '" . $_SESSION['usuario'] . "' "; // Muestra sólo los documentos que el mismo usuario a subido
			}
			if(validaAcceso('1025025', $dbpfx) == 1 || ($limitaaccdocs != 1 && $_SESSION['codigo'] == '60')) {
				$pregunta .= " AND (doc_etapa = '1' OR doc_archivo LIKE '%-i-%') ";  // Default para operadores
			} elseif(validaAcceso('1025000', $dbpfx) == 1) {
				$pregunta .= ""; // Muestra todos los documentos permitidos por infomon y su código de puesto
			} else {
				$pregunta .= " AND doc_id < '1' "; // No muestra ningún documento
			}
		}
*/
        //		echo $pregunta;
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion de documentos! " . $pregunta);
		$num_cols = mysql_num_rows($matriz);
		if ($num_cols>0) {
			echo '
		<div class="page-content">
			<div class="row"> <!-box header del título. -->
				<div class="col-md-10">
					<div class="content-box-header">
						<div class="panel-title">'."\n";
			if($previa_id != '') {
				echo '
							<h2>Documentos relacionados al presupuesto previo ' . $previa_id . '</h2>'."\n";
			} else {
				echo '
							<h2>Documentos relacionados a la Orden de Trabajo ' . $orden_id . ' con estatus ' . constant('ORDEN_ESTATUS_' . $estatus) . '</h2>'."\n";
			}
			echo '
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-10">
					<div id="content-tabla">
						<table cellspacing="0" class="table-new">			
							<tr>
								<th><big>Nombre</big></th>
								<th><big>Archivo</big></th>'."\n";
			// ------ Verificar el array $usu_omit_fd en config.php para ver si a el usuario actual se le debe mostrar este campo
			if($omitir != '1') {
				echo '
								<th><big>Fecha de registro</big></th>'."\n";
			}
			echo '
								<th><big>Usuario</big></th>
								<th><big>Vista previa</big></th>'."\n";
			$ayuda['AyudaAccesos'] = '
								<p align="justify">
									Muestra dos indicadores que presentan el estatus de (confidencialidad y Visibilidad) que tiene el documento:<br>
									<img src="idiomas/es_MX/imagenes/documento_clasificado.png" alt="clasificado" title="clasificado" width="35" height="35""> Documento clasificado.<br>
									<img src="idiomas/es_MX/imagenes/documento_no_clasificado.png" alt="No clasificado" title="No clasificado" width="35" height="35""> Documento NO clasificado.<br>
									<img src="idiomas/es_MX/imagenes/cliente_visible.png" alt="visible para cliente" title="visible para cliente" width="35" height="35""> Documento visible por cliente.<br>
									<img src="idiomas/es_MX/imagenes/cliente_no_visible.png" alt="No visible para cliente" title="No visible para cliente" width="35" height="35""> Documento NO visible por cliente.<br>
									Si eres un usuario con permiso para hacer modificaciones puedes hacer click en el ícono del candado o el ojo para cambiar su estatus.
								</p>';
			echo '
								<th>
									<div style="position: relative; display: inline-block;">
										<a onclick="muestraAbajoEstatus()" class="ayuda"><big>Accesos</big></a>
										<div id="AyudaAccesos" class="muestra-contenido" style="right: 50px !important;">
											' . $ayuda['AyudaAccesos'] . '
										</div>
									</div>
								</th>'."\n";
			echo '
								<script>
									function muestraAbajoEstatus() {
										document.getElementById("AyudaAccesos").classList.toggle("mostrar");
									}
								</script>'."\n";
			if (validaAcceso('1025030', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol05']=='1' || $_SESSION['rol06']=='1'))) {
				// ------ Permiso concedido para descarga o eliminación en grupo de imagenes
				echo '
								<th><big>Eliminar<br>Descargar</big><br>'."\n";
				echo '
									<form action="documentos.php?accion=listar&orden_id=' . $orden_id . '" method="post" enctype="multipart/form-data" name="partidas">
										<input type="checkbox" name="presel" value="1" ';
				if($presel == '1') { echo 'checked="checked" '; }
				echo 'onchange="document.partidas.submit()"; />
									</form>'."\n";
// ------ Selección individual de archivos a descargar o eliminar
				echo '
									<form action="documentos.php?accion=depurar" method="post" enctype="multipart/form-data">';
				echo '
								</th>'."\n";
			} else {
				echo '
								<th>&nbsp;</th>'."\n";
			}
			echo '
							</tr>'."\n";
			$c = 0;
			$fondo = 'claro';
			while ($documento = mysql_fetch_array($matriz)) {
				$urlAmazon = 'https://' . $aws_web_ap . '.amazonaws.com/'.$dbpfx.'/';
				$docAmazon = str_replace($urlAmazon, "", $documento['doc_archivo']);

				// --- INICIA FIRMA A AWS

				// --- cargamos el sdk de amazon
				require_once 'aws/aws-autoloader.php';

				// --- Creamos el array con los datos de cliente
				$client = new Aws\S3\S3Client(array(
				    'credentials' => array(
				        'key' => $aws_key,
				        'secret' => $aws_secret,
				    ),
				    'region' => $aws_region,
				    'version' => 'latest',
				));

				// -- Seleccionamos las clases que se ocuparan
				//use Aws\Exception\AwsException;
				require_once 'aws/Aws/Exception/AwsException.php';
				//use Aws\S3\ObjectUploader;

				require_once 'aws/Aws/S3/ObjectUploader.php';

				$archivo = $dbpfx.'/'.$docAmazon;
				$cmd = $client->getCommand('GetObject', [
				    'Bucket' => $bucket,
				    'Key' => $archivo
				]);

				$request = $client->createPresignedRequest($cmd, '+5 minutes');

				// Get the actual presigned-url
				$urlFirmada = (string)$request->getUri();


				// --- Revisar si el documento es una factura ---
				if(preg_match("/Factura PDF/i", $documento['doc_nombre'])){ // --- SE CONSTRUYE LINK PARA para ex-3.3 ---

					$fact_num = encuentra_ultima($documento['doc_nombre']);
					// ------------------------ OBTENER NUM REPORTE ---------------------------- //
					$preg_siniestro = "SELECT reporte FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_num = '" . $fact_num . "' AND orden_id = '" . $orden_id . "'";
					$matr_sin = mysql_query($preg_siniestro) or die("ERROR! " . $preg_siniestro);
					$n_reporte = mysql_fetch_assoc($matr_sin);
					// ------------------ CAMBIAR LA EXTENSIÓN A XML ----------------------}
					$xml = str_replace(".pdf", ".xml", $documento['doc_archivo']);
					$genera_pdf = '<a href="ex-3.3.php?axml=' . $xml . '&orden_id=' . $orden_id . '&reporte=' . $n_reporte['reporte'] . '" target="_blank" /><small>PDF</small></a>';

				} elseif(preg_match("/Recibo Pago Electrónico PDF/i", $documento['doc_nombre'])){ // --- SE CONSTRUYE LINK PARA rpe-3.3-pdf.php ---

					// ------------------ CAMBIAR LA EXTENCIÓN A XML ----------------------}
					$xml = str_replace(".pdf", ".xml", $documento['doc_archivo']);
					$genera_pdf = '<a href="rpe-3.3-pdf.php?axml=' . $xml . '" target="_blank" /><small>PDF</small></a>';
				} else {
					$genera_pdf = '';
				}
				echo '
							<tr class="' . $fondo . '">
								<td style="text-align: left !important;"><big>' . $documento['doc_nombre'] . ' ' . $genera_pdf . '</big></td>'."\n";
				if($documento['doc_clasificado'] == 1){
					if($infomon == '1' || $_SESSION['codigo'] <= $codigomon){
						echo '
								<td style="text-align: left !important;">' . $docAmazon . '</td>'."\n";
					} else{
						echo '
								<td style="text-align: left !important;"><b>CONFIDENCIAL</b></td>'."\n";
					}
				} else{
					echo '
								<td style="text-align: left !important;">' . $docAmazon . '</td>'."\n";
				}
				if($omitir != '1') {
					echo '
								<td>' . $documento['doc_fecha_ingreso'] . '</td>'."\n";
				}
				echo '
								<td><b>' . $documento['doc_usuario'] . '</b></td>'."\n";	
				echo '
								<!--<td>' . constant('ORDEN_ESTATUS_' . $documento['doc_etapa']) . '</td> -->'."\n";
				if($docAmazon != '') {
					$tipo_archivo = pathinfo($docAmazon);
					if($documento['doc_clasificado'] == 1) {
						if($infomon == '1' || $_SESSION['codigo'] <= $codigomon) {
							echo '
								<td>';
									if (strpos($documento['doc_archivo'], $urlAmazon) !== false) {
									    echo '<a href="' . $urlFirmada . '" target="_blank">';
									}
									else{
										echo '<a href="' . DIR_DOCS . $docAmazon . '" target="_blank">';
									}
									echo
									'<img src="';
							if(($tipo_archivo['extension']=='JPG' || $tipo_archivo['extension']=='JPEG' || $tipo_archivo['extension']=='jpeg' || $tipo_archivo['extension']=='PNG' || $tipo_archivo['extension']=='jpg' || $tipo_archivo['extension']=='png' || $tipo_archivo['extension']=='gif') && file_exists(DIR_DOCS . 'minis/' .$docAmazon)) {
								echo DIR_DOCS . 'minis/' . $docAmazon . '" ';
							} else {
								echo DIR_DOCS . 'documento.png" ';
							}
							echo 'width="48" border="0">
									</a>
								</td>'."\n";
						} else {
							echo '
								<td><img src="' . DIR_DOCS . 'documento.png" alt="Sin imagen" title="Sin imagen"></td>'."\n";
						}
					} elseif($documento['doc_clasificado'] == 0) {
						echo '
								<td>';
									if (strpos($documento['doc_archivo'], $urlAmazon) !== false) {
									    echo '<a href="' . $urlFirmada . '" target="_blank">';
									}
									else{
										echo '<a href="' . DIR_DOCS . $docAmazon . '" target="_blank">';
									}
									echo
									'<img src="';
							if(($tipo_archivo['extension']=='JPG' || $tipo_archivo['extension']=='JPEG' || $tipo_archivo['extension']=='jpeg' || $tipo_archivo['extension']=='PNG' || $tipo_archivo['extension']=='jpg' || $tipo_archivo['extension']=='png' || $tipo_archivo['extension']=='gif') && file_exists(DIR_DOCS . 'minis/' .$docAmazon)) {
								echo DIR_DOCS . 'minis/' . $docAmazon . '" ';
							} else {
								echo DIR_DOCS . 'documento.png" ';
							}
							echo 'width="48" border="0">
									</a>
								</td>'."\n";
					}
				} else {
					echo '
								<td><img src="' . DIR_DOCS . 'documento.png" alt="Sin imagen" title="Sin imagen"></td>'."\n";
				}
				echo '
								<td>'."\n";
				// ------ Cambiar el tipo de clasificación del documento ------
				if($_SESSION['rol02'] == '1' || $_SESSION['rol05'] == '1' || $_SESSION['rol12'] == '1' || $_SESSION['rol13'] == '1' || $cambia_clasificacion == '1') {
					if($documento['doc_clasificado'] == '1'){ // --- Info de documento clasificado ---
						echo '
									<a href="documentos.php?accion=cambiar_clasificacion&tipo=clasificado&doc_id=' . $documento['doc_id'] . '&orden_id=' . $orden_id . '"><img src="idiomas/es_MX/imagenes/documento_clasificado.png" alt="clasificado" title="clasificado" width="35" height="35""></a>'."\n";
					} else {
						echo '
									<a href="documentos.php?accion=cambiar_clasificacion&tipo=clasificado&doc_id=' . $documento['doc_id'] . '&orden_id=' . $orden_id . '"><img src="idiomas/es_MX/imagenes/documento_no_clasificado.png" alt="no clasificado" title="no clasificado" width="35" height="35""></a>'."\n";
					}
				} else { // --- Si el usuario no tiene acceso a cambiar clasificación solo muestra el tipo ---
					if($documento['doc_clasificado'] == '1'){ // --- Info de documento clasificado ---
						echo '
									<img src="idiomas/es_MX/imagenes/documento_clasificado.png" alt="clasificado" title="clasificado" width="35" height="35"">'."\n";
					} else {
						echo '
									<img src="idiomas/es_MX/imagenes/documento_no_clasificado.png" alt="no clasificado" title="no clasificado" width="35" height="35"">'."\n";
					}
				}
				// --- Si el documento es clasificado no se permite el cambio para que sea visible para el cliente ---
				if($documento['doc_clasificado'] == '0' && ($tipo_archivo['extension']=='JPG' || $tipo_archivo['extension']=='jpg' || $tipo_archivo['extension']=='jpeg' || $tipo_archivo['extension']=='JPEG')){
					// --- Cambiar la visibilidad del cliente ---
					if($_SESSION['rol06'] == '1' || $_SESSION['rol04'] == '1' || $_SESSION['rol02'] == '1' || $cambia_visibilidad == '1') {
						if($documento['doc_etapa'] == '1') {
							echo '
										<a href="documentos.php?accion=cambiar_clasificacion&tipo=visible&doc_id=' . $documento['doc_id'] . '&orden_id=' . $orden_id . '"><img src="idiomas/es_MX/imagenes/cliente_visible.png" alt="visible para cliente" title="visible para cliente" width="35" height="35""></a>'."\n";
						} else {
							echo '
										<a href="documentos.php?accion=cambiar_clasificacion&tipo=visible&doc_id=' . $documento['doc_id'] . '&orden_id=' . $orden_id . '"><img src="idiomas/es_MX/imagenes/cliente_no_visible.png" alt="no visible para cliente" title="no visible para cliente" width="35" height="35""></a>'."\n";
						}
					}
				} else { // --- Si el usuario no tiene acceso a cambiar la visibilidad solo muestra el tipo ---
					if($documento['doc_etapa'] == '1' && ($tipo_archivo['extension']=='JPG' || $tipo_archivo['extension']=='jpg' || $tipo_archivo['extension']=='jpeg' || $tipo_archivo['extension']=='JPEG')){
						echo '
									<img src="idiomas/es_MX/imagenes/cliente_visible.png" alt="visible para cliente" title="visible para cliente" width="35" height="35"">'."\n";
					}
				}
				echo '
								</td>'."\n";
				if (validaAcceso('1025030', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol06']=='1' || $_SESSION['rol05']=='1'))) {
					echo '
								<td>
									<input type="checkbox" name="eliminar[' . $c . ']" ';
					if($presel == '1') { echo 'checked="checked"'; }
					echo ' />
									<input type="hidden" name="doc_id[' . $c . ']" value="' . $documento['doc_id'] . '"/>
									<input type="hidden" name="doc_arch[' . $c . ']" value="' . $documento['doc_archivo'] . '"/>
								</td>'."\n";
				} else {
					echo '
								<td>&nbsp;</td>'."\n";
				}
				echo '
							</tr>'."\n";
				$c++;
				if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
			}
			if (validaAcceso('1025030', $dbpfx) == '1' || ($solovalacc != 1 && ($_SESSION['rol06']=='1' || $_SESSION['rol05']=='1'))) {
				echo '
							<tr class="obscuro">
								<td colspan="2" style="text-align:left; vertical-align:top;">
									<input type="submit" name="enviar" value="Eliminar" class="btn btn btn-danger"/>
									<br>
									<label>Si marcó documentos para eliminar, al presionar "Eliminar" serán eliminados.</label>
								</td>
								<td colspan="5" style="text-align:left; vertical-align:top;">
									<input type="submit" name="enviar" value="Descargar" class="btn btn btn-success" />
									<br>
									<span style="color:red;">
										Para ver los documentos que descargaste,<br>dale doble click al archivo o carpeta ZIP<br>
										y en la ventana que aparezca,<br>dale doble click a documentos.
									</span>
									<input type="hidden" name="orden_id" value="' . $orden_id . '"/>
									<input type="hidden" name="estatus" value="' . $estatus . '"/>
								</td>
							</tr>'."\n";
			}
			echo '
						</table>
						</form>
					</div>
				</div>
			</div>
		</div>
		<br>'."\n";
		} else {
			return $mensaje ='No se encontraron documentos para la orden de trabajo ' . $orden_id;
		}
	}

	function lista_fotos($orden_id, $op_id, $dbpfx, $nombre){
		

		$pregunta = "SELECT * FROM " . $dbpfx . "documentos WHERE  orden_id = '$orden_id'  AND doc_clasificado = '0'";

        //		echo $pregunta;
		$matriz = mysql_query($pregunta) or die ("ERROR: Fallo seleccion de documentos! " . $pregunta);
		$num_cols = mysql_num_rows($matriz);

		if ($num_cols>0) {
            echo '		
        <div class="page-content">
            <div class="row"> <!-box header del título. -->
                <div class="col-md-8">
                    <div class="content-box-header">
                        <div class="panel-title">
                            <h2>Fotos de la orden ' . $orden_id . ', disponibles para ligar a la refacción "' . $nombre . '"</h2>
                        </div>
                    </div>
                </div>
            </div>
			<div class="row">
				<div class="col-md-8">
					<div id="content-tabla">
						<form action="refacciones.php?accion=ligar&orden_id=' . $orden_id . '&op_id=' . $op_id . '&confirma=si" method="post" enctype="multipart/form-data" name="grupo">
						<table cellspacing="0" class="table-new">			
							<tr>
								<th><big>Nombre</big></th>
								<th><big>Archivo</big></th>
								<th><big>Vista previa</big></th>
                            	<th><big>Asociar</big></th>
							</tr>'."\n";

            $c = 0;
            $fondo = 'claro';
			while ($documento = mysql_fetch_array($matriz)) {

				$tipo_archivo = pathinfo($documento['doc_archivo']);
				
				if($documento['doc_archivo'] != '') {
					
					if(preg_match("/-i-/i", $documento['doc_archivo'])){
						
					} else{
						
						
						if($tipo_archivo['extension'] == 'JPG' || $tipo_archivo['extension'] == 'jpg' || $tipo_archivo['extension'] == 'png' || $tipo_archivo['extension'] == 'PNG'){
						
							echo '		
								<tr class="' . $fondo . '">
									<td style="text-align: left !important;"><big>' . $documento['doc_nombre'] . ' ' . $genera_pdf . '</big></td>
									<td style="text-align: left !important;">' . $documento['doc_archivo'] . '</td>	
									<td>
										<a href="' . DIR_DOCS . $documento['doc_archivo'] . '" target="_blank">
											<img src="';

							if(file_exists(DIR_DOCS . 'minis/' .$documento['doc_archivo'])){
								echo DIR_DOCS . 'minis/' . $documento['doc_archivo'] . '" ';
							} else{
								echo DIR_DOCS . 'documento.png" ';
							}

							echo 'width="48" border="0">
										</a>
									</td>	
									<td>
										<input type="radio" name="doc_id" value="' . $documento['doc_id'] . '"/>
									</td>
								</tr>'."\n";

							$c++;
							if ($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
					
						}
					
					}
				
				}
				
			}
			
                echo '		
                            <tr class="obscuro">
								<td colspan="3">
								</td>
								<td style="text-align:center; vertical-align:top;">
                                    <input type="submit" name="enviar" value="Asociar" class="btn btn btn-success" />
                                </td>
                            </tr>
						</table>
						</form>
					</div>
				</div>
			</div>
		</div>
                <br>'."\n";
		} else {
			return $mensaje ='No se encontraron documentos para la orden de trabajo ' . $orden_id;
		}
	}

	function actualiza_suborden ($orden_id, $area, $dbpfx) {

		// Ahora se procesa todo en actualiza_orden

	}

	function actualiza_orden ($orden_id, $dbpfx) {
		global $num_areas_servicio, $fetermprod;
//		Primero ajustamos el estatus de las áreas

		$preg1 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
		$matr1 = mysql_query($preg1) or die($preg1);
		$refac = 0;
		while ($orden = mysql_fetch_array($matr1)) {
			$orden_id = $orden['orden_id'];
			$parametros='orden_id = ' . $orden_id;
			$prestotal = 0;
			$sql_data_array = array();
			for($area = 1; $area <= $num_areas_servicio; $area++) {
				$preg3 = "SELECT sub_estatus, sub_refacciones_recibidas, sub_presupuesto FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND sub_area = '" .$area . "' AND sub_estatus < '190'";
				$matr3 = mysql_query($preg3) or die('actualiza_suborden' . $preg3);
				$estat_area = 'orden_estatus_' . $area;
				$filasub = mysql_num_rows($matr3);
				if($filasub > 0) {
					while($sub = mysql_fetch_array($matr3)) {
						if($sub['sub_estatus'] >= 104 && $sub['sub_estatus'] < 111) {
							$srep[] = $sub['sub_estatus'];
						} elseif($sub['sub_estatus'] == 101 || ($sub['sub_estatus'] > 121 && $sub['sub_estatus'] < 130)) {
							$sdoc1[] = $sub['sub_estatus'];
						} elseif($sub['sub_estatus'] == 102 || $sub['sub_estatus'] == 103 || $sub['sub_estatus'] == 120) {
							$sdoc2[] = $sub['sub_estatus'];
						} elseif($sub['sub_estatus'] == 121 || ($sub['sub_estatus'] >= 111 && $sub['sub_estatus'] <= 116)) {
							$ster[] = $sub['sub_estatus'];
						} else {
							$sper[] = $sub['sub_estatus'];
						}
						if($sub['sub_refacciones_recibidas'] > $refac) {
							$refac = $sub['sub_refacciones_recibidas'];
						}
							$prestotal = $prestotal + $sub['sub_presupuesto'];
					}

					if(is_array($sdoc1)) {
						$sta = 129;
						foreach($sdoc1 as $k) {
							if($k < $sta) { $sta = $k; }
						}
						$sql_data_array[$estat_area] = ($sta - 100);
					} elseif(is_array($sdoc2)) {
						$sta = 120;
						foreach($sdoc2 as $k) {
							if($k < $sta) { $sta = $k; }
						}
						$sql_data_array[$estat_area] = ($sta - 100);
					} elseif(is_array($srep)) {
						$sta = 0;
						foreach($srep as $k) {
							if($k > $sta) { $sta = $k; }
						}
						$sql_data_array[$estat_area] = ($sta - 100);
					} elseif(is_array($ster)) {
						$sta = 121;
						foreach($ster as $k) {
							if($k < $sta) { $sta = $k; }
						}
						$sql_data_array[$estat_area] = ($sta - 100);
					} elseif(is_array($sper)) {
						$sta = 0;
						foreach($sper as $k) {
							if($k > $sta) { $sta = $k; }
						}
						$sql_data_array[$estat_area] = ($sta - 100);
					} else {
						$sql_data_array[$estat_area] = 'null';
					}
				} else {
					$sql_data_array[$estat_area] = 'null';
				}

/*				echo 'sdoc '; print_r($sdoc); echo '<br>';
				echo 'srep '; print_r($srep); echo '<br>';
				echo 'ster '; print_r($ster); echo '<br>';
				echo 'sper '; print_r($sper); echo '<br>';
				echo '<br>';
*/
				unset($srep); unset($sdoc1); unset($sdoc2); unset($ster); unset($sper);
			}
			$sql_data_array['orden_presupuesto'] = $prestotal;
			$sql_data_array['orden_ref_pendientes'] = $refac;
			ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
			unset($sql_data_array);
		}
		unset($sql_data_array);

// 	Ahora ajustamos la OT

		$preg1 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
	   $matr1 = mysql_query($preg1) or die($preg1);
	   while ($orden = mysql_fetch_array($matr1)) {
			$orden_id = $orden['orden_id'];
			$parametros='orden_id = ' . $orden['orden_id'];
			$calidad = 0;
				if($orden['orden_estatus_1']==21 || $orden['orden_estatus_2']==21 || $orden['orden_estatus_3']==21 || $orden['orden_estatus_4']==21 || $orden['orden_estatus_5']==21 || $orden['orden_estatus_6']==21 || $orden['orden_estatus_7']==21 || $orden['orden_estatus_8']==21 || $orden['orden_estatus_9']==21 || $orden['orden_estatus_10']==21) { $calidad = 21; }
// 				echo 'Calidad -> ' . $calidad . '<br>';
 			for($i = 1; $i <= $num_areas_servicio ; $i++) {
 				if($orden['orden_estatus_'.$i] >= 4 && $orden['orden_estatus_'.$i] <= 11) {
 					$rep[$i] = $orden['orden_estatus_'.$i];
	 			} elseif($orden['orden_estatus_'.$i] == 1 || $orden['orden_estatus_'.$i] == 17 || ($orden['orden_estatus_'.$i] > 21 && $orden['orden_estatus_'.$i] < 30)) {
 					$doc1[$i] = $orden['orden_estatus_'.$i];
 				} elseif($orden['orden_estatus_'.$i] == 2 || $orden['orden_estatus_'.$i] == 3 || $orden['orden_estatus_'.$i] == 20) {
 					$doc2[$i] = $orden['orden_estatus_'.$i];
 				} elseif($orden['orden_estatus_'.$i] == 21 || ($orden['orden_estatus_'.$i] >= 12 && $orden['orden_estatus_'.$i] <= 16)) {
 					$ter[$i] = $orden['orden_estatus_'.$i];
	 			} elseif($orden['orden_estatus_'.$i] >= 30 && $orden['orden_estatus_'.$i] <= 89) {
 					$per[$i] = $orden['orden_estatus_'.$i];
 				}
	 		}

 			$ent = 0;
			if($orden['orden_estatus'] >= 90 && $orden['orden_estatus'] <= 99) {
				$ent = $orden['orden_estatus'];
			}

			if(is_array($doc1)) {
				$sta = 29;
				foreach($doc1 as $k) {
					if($k < $sta) { $sta = $k; }
				}
				$sql_data_array = array('orden_estatus' => $sta);
			} elseif(is_array($doc2)) {
				$sta = 20;
				foreach($doc2 as $k) {
					if($k < $sta) { $sta = $k; }
				}
				$sql_data_array = array('orden_estatus' => $sta);
			} elseif(is_array($rep)) {
				$sta = 0;
				foreach($rep as $k) {
					if($k > $sta) { $sta = $k; }
				}
				$sql_data_array = array('orden_estatus' => $sta);
			} elseif(is_array($ter)) {
				$sta = 0;
				foreach($ter as $k) {
					if($k > $sta) { $sta = $k; }
				}
				if($ent < 99 && ($orden['orden_estatus'] <= 12 || $orden['orden_estatus'] > 16)) {
					$sql_data_array = array('orden_estatus' => $sta);
				}
			} elseif(is_array($per)) {
				$sta = 0;
				foreach($per as $k) {
					if($k > $sta) { $sta = $k; }
				}
				if($ent < 90) {
					$sql_data_array = array('orden_estatus' => $sta);
				} elseif($sta == 30) {
					$sql_data_array = array('orden_estatus' => '98');
				} elseif($sta == 31) {
					$sql_data_array = array('orden_estatus' => '97');
				} elseif($sta == 32) {
					$sql_data_array = array('orden_estatus' => '96');
				} elseif($sta == 33) {
					$sql_data_array = array('orden_estatus' => '95');
				} elseif($sta == 34) {
					$sql_data_array = array('orden_estatus' => '95');
				} elseif($sta == 35) {
					$sql_data_array = array('orden_estatus' => '95');
				}
			} else {
				$sql_data_array = array('orden_estatus' => '90');
			}

			unset($rep); unset($doc); unset($doc1); unset($doc2); unset($ter); unset($per);

/*			echo 'doc '; print_r($doc); echo '<br>';
			echo 'rep '; print_r($rep); echo '<br>';
			echo 'ter '; print_r($ter); echo '<br>';
			echo 'per '; print_r($per); echo '<br>';
			echo $ent . '<br>';
*/

			if ($orden['orden_ref_pendientes']=='0' && $sql_data_array['orden_estatus'] > 2 && $sql_data_array['orden_estatus'] < 12 && is_null($orden['orden_fecha_ref_recibidas'])) {
				$sql_data_array['orden_fecha_ref_recibidas'] = date('Y-m-d H:i:s');
					bitacora($orden_id, 'Todas las refacciones recibidas', $dbpfx);
			}

				if ($sql_data_array['orden_estatus'] == 12 && is_null($orden['orden_fecha_proceso_fin']) && $fetermprod != 1) {
					$sql_data_array['orden_fecha_proceso_fin'] = date('Y-m-d H:i:s');
				}

				if ($sql_data_array['orden_estatus'] != $orden['orden_estatus'] && $sql_data_array['orden_estatus'] > 0 && $sql_data_array['orden_estatus'] != '') {
					$sql_data_array['orden_fecha_ultimo_movimiento'] = date('Y-m-d H:i:s');
					$sql_data_array['orden_alerta'] = 0;
					bitacora($orden_id, 'Cambio a estatus ' . $sql_data_array['orden_estatus'] . ' ' . constant('ORDEN_ESTATUS_' . $sql_data_array['orden_estatus']) . ' anterior: ' . $orden['orden_estatus'] . ' ' . constant('ORDEN_ESTATUS_' . $orden['orden_estatus']), $dbpfx);
				}

				if(count($sql_data_array) > 0) {
					ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
				}
				unset($sql_data_array);
		}
	}

	function agrega_documento ($orden_id, $imagen, $nom_doc, $dbpfx, $pago_id, $clasificado) {
		global $servrespaldo;
		$nom_doc = ereg_replace("[^A-Za-zñÑáéíóúÁÉÍÓÚ0-9 ]", "", $nom_doc);
		$nombre_archivo = basename($imagen['name']);
		$nombre_archivo = limpiarstring($nombre_archivo);
		if($pago_id != '') {
			$nombre_archivo = 'RHP-' . $pago_id . '-' . time() . '-' . $nombre_archivo;
		} else {
			$nombre_archivo = $orden_id . '-' . time() . '-' . $nombre_archivo;
		}
		if (move_uploaded_file($imagen['tmp_name'], DIR_DOCS . $nombre_archivo)) {
			$sql_data_array = [
				'orden_id' => $orden_id,
				'doc_nombre' => $nom_doc,
				'doc_usuario' => $_SESSION['usuario'],
				'doc_archivo' => $nombre_archivo,
			];
			if($pago_id != '') {
				$sql_data_array['pago_rh'] = $pago_id;
			}
			$sql_data_array['doc_clasificado'] = $clasificado;
			ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
			creaMinis($nombre_archivo);
			$resultado = array('error' => 'no', 'mensaje' => '', 'nombre' => $nombre_archivo);
			// --- Copia el archivo al servidor de respaldo ---
			if($servrespaldo != '') {
				$conecta = ssh2_connect($servrespaldo, 2922);
				ssh2_scp_send($conecta, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, 0644);
			}
		} else {
			$resultado = array('error' => 'si', 'mensaje' => 'No se logró subir el archivo.<br>');
		}
		return $resultado;
	}

	function agrega_foto_almacen($prod_id, $imagen, $nombre, $dbpfx, $hacer) {
		global $servrespaldo;
		$nombre = ereg_replace("[^A-Za-zñÑáéíóúÁÉÍÓÚ0-9 ]", "", $nombre);
		$nombre_archivo = limpiarstring($nombre);

		$nombre_archivo = 'PROD-' . $prod_id . '-' . $nombre_archivo . '-' . time() . '.jpg';
		$param = '';

		if (move_uploaded_file($imagen['tmp_name'], DIR_DOCS . $imagen['name'])) {
			$info = pathinfo(DIR_DOCS . $imagen['name']);
			if (strtolower($info['extension']) == 'png') {
				$imagen_png = imagecreatefrompng(DIR_DOCS . $imagen['name']);
				imagejpeg($imagen_png, DIR_DOCS . $nombre_archivo, 100);
				imagedestroy($imagen_png);
			} else {
				rename (DIR_DOCS . $imagen['name'], DIR_DOCS . $nombre_archivo);
			}
			if($hacer == 'actualizar') { $param = " prod_id = '" . $prod_id . "'"; }
			$sql_data_array = array('prod_id' => $prod_id,
				'doc_nombre' => $nombre,
				'doc_usuario' => $_SESSION['usuario'],
				'doc_archivo' => $nombre_archivo);
			ejecutar_db($dbpfx . 'documentos', $sql_data_array, $hacer, $param);
			creaMinis($nombre_archivo);
			creaMedianas($nombre_archivo);
			$resultado = array('error' => 'no', 'mensaje' => '', 'nombre' => $nombre_archivo);
			// --- Copia el archivo al servidor de respaldo ---
			if($servrespaldo != '') {
				$conecta = ssh2_connect($servrespaldo, 2922);
				ssh2_scp_send($conecta, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, 0644);
			}
		} else {
			$resultado = array('error' => 'si', 'mensaje' => 'No se logró subir el archivo.<br>');
		}
		return $resultado;
	}

	function ajustaTarea ($sub_orden_id, $dbpfx) {
		$preg1 = "SELECT op_id, op_cantidad, op_precio, op_precio_revisado, op_tangible, op_ok, op_estructural, op_autosurtido FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '$sub_orden_id' AND op_pres IS NULL";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos para recálculo! " . $preg1);
		$op_ref = 0; $op_cons = 0; $op_mo = 0; $completo = 1; $estruc = 1;
		while($rec = mysql_fetch_array($matr1)) {
			if($rec['op_autosurtido']=='1') {
				$op_sub = 0;
			} else {
				$op_sub = $rec['op_cantidad'] * $rec['op_precio'];
			}
			if($rec['op_precio_revisado'] > 0) {
				$param = "op_id = '" . $rec['op_id'] . "'";
				$sql_data = array('op_subtotal' => $op_sub);
				ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
			}
			if($rec['op_tangible'] == 1) {
				$op_ref = $op_ref + $op_sub;
				if($rec['op_ok'] == '0') {
					$completo = 0;
					if($rec['op_estructural'] == '1') {
						$estruc = 0;
					}
				}
			} elseif($rec['op_tangible']== 2) {
				$op_cons = $op_cons + $op_sub;
			} elseif($rec['op_tangible']== 0)  {
				$op_mo = $op_mo + $op_sub;
				$tiempo = $tiempo + $rec['op_cantidad'];
			}
		}

		$horas = intval($tiempo);
		$minutos = round((($tiempo - $horas)*60), 2);
		if($minutos==0) {$minutos='00';}
		$programadas = $horas . ':' . $minutos;
		$nvo_pres = $op_ref + $op_cons + $op_mo;
		$sql_data = array('sub_presupuesto' => $nvo_pres,
			'sub_partes' => $op_ref,
			'sub_consumibles' => $op_cons,
			'sub_mo' => $op_mo,
			'sub_fecha_presupuesto' => date('Y-m-d H:i:s'),
			'sub_valuador' => $_SESSION['usuario'],
			'sub_horas_programadas' => $programadas);

		if($completo == 1) {
			$sql_data['sub_refacciones_recibidas'] = '0';
		} elseif($estruc == 1) {
			$sql_data['sub_refacciones_recibidas'] = '1';
		} else {
			$sql_data['sub_refacciones_recibidas'] = '2';
		}
		$param = "sub_orden_id = '" . $sub_orden_id . "'";
		ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $param);
	}

	function ajusta_orden ($orden_id, $dbpfx) {

		return actualiza_orden($orden_id, $dbpfx);

	}

	function dia_habil ($dias, $corte = NULL) {
		$hoy = date('w', time());
		$year = date('Y', time());
		$t_habil = strtotime(date('Y-m-d 00:00:00', time())) + ($dias * 86400);
		// ------ Si no cae en el día de corte, llevarlo al siguiente día de corte --
		if(!is_null($corte) && date('w', $t_habil) != $corte) {
			$ndia = date('w', $t_habil);
			if($ndia < $corte) {
				$agregad = $corte - $ndia;
			} else {
				$agregad = (7 + $corte) - $ndia;
			}
			$t_habil = $t_habil + ($agregad * 86400);
		}
		// ------ Si la fecha cae en domingo, aumenta un día
		if (date('w', $t_habil) == 0) { $t_habil = $t_habil + 86400; }
		// ------ Si la fecha cae en un día no habil, aumenta un día
		if($t_habil == mktime(0,0,0,1,1,$year)) { $t_habil = $t_habil + 86400; }
		if($t_habil == mktime(0,0,0,5,1,$year)) { $t_habil = $t_habil + 86400; }
		if($t_habil == mktime(0,0,0,9,16,$year)) { $t_habil = $t_habil + 86400; }
		if($t_habil == mktime(0,0,0,12,25,$year)) { $t_habil = $t_habil + 86400; }
		$mes = date('n', $t_habil);
		// ------ Verifica si es un mes con feriado tercer lunes, y aumenta un día si cae ese día --
		if($mes == 3 || $mes == 11) {
			$dia = date('d', $t_habil);
			$d1 = date('w', mktime(0,0,0,$mes,1,$year));
			if($d1 == 0 && $dia == '16') { $t_habil = $t_habil + 86400; }
			if($d1 == 1 && $dia == '15') { $t_habil = $t_habil + 86400; }
			if($d1 == 2 && $dia == '21') { $t_habil = $t_habil + 86400; }
			if($d1 == 3 && $dia == '20') { $t_habil = $t_habil + 86400; }
			if($d1 == 4 && $dia == '19') { $t_habil = $t_habil + 86400; }
			if($d1 == 5 && $dia == '18') { $t_habil = $t_habil + 86400; }
			if($d1 == 6 && $dia == '17') { $t_habil = $t_habil + 86400; }
		}
		// ------ Verifica si es un mes con feriado primer lunes, y aumenta un día si cae ese día --
		if($mes == 2) {
			$dia = date('d', $t_habil);
			$d1 = date('w', mktime(0,0,0,$mes,1,$year));
			if($d1 == 0 && $dia == '2') { $t_habil = $t_habil + 86400; }
			if($d1 == 1 && $dia == '1') { $t_habil = $t_habil + 86400; }
			if($d1 == 2 && $dia == '7') { $t_habil = $t_habil + 86400; }
			if($d1 == 3 && $dia == '6') { $t_habil = $t_habil + 86400; }
			if($d1 == 4 && $dia == '5') { $t_habil = $t_habil + 86400; }
			if($d1 == 5 && $dia == '4') { $t_habil = $t_habil + 86400; }
			if($d1 == 6 && $dia == '3') { $t_habil = $t_habil + 86400; }
		}
		$f_habil = date('Y-m-d 18:00:00', $t_habil);
		return $f_habil;
	}

	function semana($fecha) {
		// echo $fecha;
		$diferencia = (strtotime(date('Y-m-d 23:59:59')) - strtotime($fecha));
		if($diferencia > 1900800) { $sem = 4 ;}
		elseif($diferencia > 1296000) { $sem = 3 ;}
		elseif($diferencia > 691200) { $sem = 2 ;}
		elseif($diferencia > 345600) { $sem = 1 ;}
		else { $sem = 0 ;}
		// echo $sem;
		return $sem;
	}

	function dias($fecha) {
		// echo $fecha;
		$diferencia = (time() - strtotime($fecha));
		if($diferencia > 1641600) { $sem = 5 ;} // 20 o más
		elseif($diferencia > 1036800) { $sem = 4 ;} // 13 a 19
		elseif($diferencia > 518400) { $sem = 3 ;} // 7 a 12
		elseif($diferencia > 259200) { $sem = 2 ;} // 4 a 6
		elseif($diferencia > 86400) { $sem = 1 ;} // 2 a 3
		else { $sem = 0 ;} // menos de 1
		// echo $sem;
		return $sem;
	}

	function quemes($fecha) {
		$estemes = date('n');
		$elmes = date('n', strtotime($fecha));
		$year = date('Y');
		$elyear = date('Y', strtotime($fecha));
		$estemes = (($year - $elyear) * 12) + $estemes;
		$mes = $estemes - $elmes;
		return $mes;
	}

	function horasProgramadas($sub_orden_id, $dbpfx) {

	}

	function horasEmpleadas($sub_orden_id, $dbpfx) {
		$preg1 = "SELECT usuario FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" . $sub_orden_id . "' GROUP BY usuario";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de operarios para la tarea " . $sub_orden_id . "! " . $preg1);
		$tiempo = 0; $tiempo2 = time();
		while($usu = mysql_fetch_array($matr1)) {
			$preg2 = "SELECT seg_tipo, seg_hora_registro FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '".$sub_orden_id."' AND usuario = '" . $usu['usuario'] . "' ORDER BY seg_hora_registro";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo seleccion! " . $preg2);
			while($seg = mysql_fetch_array($matr2)) {
				if ($seg['seg_tipo']==1) { $estampa1 = strtotime($seg['seg_hora_registro']); $anterior =1;}
				if (($seg['seg_tipo']==2) && ($anterior==1)) {
					$estampa2 = strtotime($seg['seg_hora_registro']); $tiempo = $tiempo + ($estampa2 - $estampa1); $anterior =2;}
				if (($seg['seg_tipo']==2) && ($anterior == 5)) {
					$estampa2 = strtotime($seg['seg_hora_registro']); $tiempo = $tiempo + ($estampa2 - $estampa3); $anterior =2;}
				if ($seg['seg_tipo']==5) { $estampa3 = strtotime($seg['seg_hora_registro']); $anterior =5;}
				if (($seg['seg_tipo']==7) && ($anterior == 5)) {
					$estampa2 = strtotime($seg['seg_hora_registro']); $tiempo = $tiempo + ($estampa2 - $estampa3); $anterior =7;}
				if (($seg['seg_tipo']==7) && ($anterior == 1)) {
					$estampa2 = strtotime($seg['seg_hora_registro']); $tiempo = $tiempo + ($estampa2 - $estampa1); $anterior =7;}
			}
		}
		$horas = intval($tiempo/3600);
		$minutos = intval(($tiempo - ($horas*3600))/60);
		$lapso = $horas . ':' . $minutos;
		return $lapso;
	}

	function creaMinis($archivo) {
		global $servrespaldo;
// ------ La función minis tambien reduce el tamaño de la imagen principal
		$info = pathinfo(DIR_DOCS . $archivo);
		$guarda_mini = DIR_DOCS . 'minis/';
		if (strtolower($info['extension']) == 'jpg' || strtolower($info['extension']) == 'jpeg' || strtolower($info['type']) == 'image/jpeg' || strtolower($info['type']) == 'image/jpg') {
			$img = imagecreatefromjpeg( DIR_DOCS . $archivo );
 				$width = imagesx( $img );
					$height = imagesy( $img );
				$new_width = 48;
				$new_height = floor( $height * ( 48 / $width ) );
 			$tmp_img = imagecreatetruecolor( $new_width, $new_height );
				imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
				imagejpeg( $tmp_img, $guarda_mini . $archivo );
			// --- Copia el archivo al servidor de respaldo ---
			if($servrespaldo != '') {
				$conecta = ssh2_connect($servrespaldo, 2922);
				ssh2_scp_send($conecta, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/minis/' . $archivo, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/minis/' . $archivo, 0644);
			}
// ------ Inicia el redimensionado de la imagen original
			if ($width > 800 || $height > 1200) {
				if ($width > 800) {
						$new_width = 800;
						$new_height = floor( $height * ( 800 / $width ) );
 					$tmp_img = imagecreatetruecolor( $new_width, $new_height );
				} else {
						$new_height = 1200;
						$new_width = floor( $width * ( 1200 / $height ) );
 					$tmp_img = imagecreatetruecolor( $new_width, $new_height );
				}
						imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
						imagejpeg( $tmp_img, DIR_DOCS . $archivo );
			}
		}
	}

	function creaMedianas($archivo) {
		global $servrespaldo;
		$info = pathinfo(DIR_DOCS . $archivo);
		$guarda_mini = DIR_DOCS . 'medianas/';
		if ( strtolower($info['extension']) == 'jpg' ) {
			$img = imagecreatefromjpeg( DIR_DOCS . $archivo );
			$width = imagesx( $img );
			$height = imagesy( $img );
			$new_width = 200;
			$new_height = floor( $height * ( 200 / $width ) );
			$tmp_img = imagecreatetruecolor( $new_width, $new_height );
			imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
			imagejpeg( $tmp_img, $guarda_mini . $archivo );
			// --- Copia el archivo al servidor de respaldo ---
			if($servrespaldo != '') {
				$conecta = ssh2_connect($servrespaldo, 2922);
				ssh2_scp_send($conecta, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/medianas/' . $archivo, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/medianas/' . $archivo, 0644);
			}
		}
	}

	function datosVehiculo($orden_id, $dbpfx, $previa_id) {
		if($orden_id != '') {
			$pregunta = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_subtipo, v.vehiculo_color, v.vehiculo_modelo, v.vehiculo_placas, v.vehiculo_serie FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "ordenes o WHERE o.orden_vehiculo_id = v.vehiculo_id AND o.orden_id = '" . $orden_id . "'";
		} else {
			$pregunta = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_color, v.vehiculo_modelo, v.vehiculo_placas, v.vehiculo_serie FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "previas o WHERE o.previa_vehiculo_id = v.vehiculo_id AND o.previa_id = '" . $previa_id . "'";
		}
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de vehículo!" . $pregunta);
		$veh = mysql_fetch_array($matriz);
		$preg0 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '" . $orden_id . "' AND doc_archivo LIKE '%-i-%' ORDER BY doc_id";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de IMAGEN!" . $preg0);
		$foto = '';
		while($img = mysql_fetch_array($matr0)) {
			if(preg_match("/-i-3-/", $img['doc_archivo'])) {
				$foto = '<a href="' . DIR_DOCS . $img['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . $img['doc_archivo'] . '" width="180" border="0"></a>';
				$imagen = DIR_DOCS . $img['doc_archivo'];
				$foto_frontal = $img['doc_archivo'];
			} elseif(preg_match("/-i-1-/", $img['doc_archivo'])) {
				$foto_vin = $img['doc_archivo'];
			} elseif(preg_match("/-i-2-/", $img['doc_archivo'])) {
				$foto_izquierda = $img['doc_archivo'];
			} elseif(preg_match("/-i-5-/", $img['doc_archivo'])) {
				$foto_derecha = $img['doc_archivo'];
			}
		}
		if($foto == '') { $foto = 'Sin imagen de ingreso'; }
		$vehiculo = array('marca' => $veh['vehiculo_marca'],
			'tipo' => $veh['vehiculo_tipo'],
			'subtipo' => $veh['vehiculo_subtipo'],
			'color' => $veh['vehiculo_color'],
			'modelo' => $veh['vehiculo_modelo'],
			'placas' => $veh['vehiculo_placas'],
			'serie' => $veh['vehiculo_serie'],
			'eco' => $veh['vehiculo_eco'],
			'foto_frontal' => $foto_frontal,
			'foto_vin' => $foto_vin,
			'foto_izquierda' => $foto_izquierda,
			'foto_derecha' => $foto_derecha,
			'frontal' => $foto,
			'imagen' => $imagen,
			'completo' => $veh['vehiculo_marca'] . ' ' . $veh['vehiculo_tipo'] . ' ' . $veh['vehiculo_color'] . ' ' . $veh['vehiculo_modelo'] . ' Placas:' . $veh['vehiculo_placas'],
			'refacciones' => $veh['vehiculo_marca'] . ' ' . $veh['vehiculo_tipo'] . ' ' . $veh['vehiculo_color'] . ' VIN: ' . $veh['vehiculo_serie'] . ' ' . $lang['Placas'] . ': ' . $veh['vehiculo_placas'] . ' Modelo: ' . $veh['vehiculo_modelo']);
		return $vehiculo;
	}

	function validaAcceso($num_funcion, $dbpfx) {
		global $valida_accesos;
		if($valida_accesos == '1') {
			$pregunta = "SELECT * FROM " . $dbpfx . "usr_permisos WHERE num_funcion = '" . $num_funcion . "' AND usuario = '" . $_SESSION['usuario'] . "' LIMIT 1";
			$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de permisos!" . $pregunta);
			$acc = mysql_fetch_array($matriz);
			$filas = mysql_num_rows($matriz);
			if($filas == 1 && $acc['activo'] == '1') {
				$acceso = 1;
			} else {
				$acceso = 0;
			}
		}
		return $acceso;
	}

	function regAsiento($terc, $tipo, $poltipo, $ciclo, $polnum, $num_funcion, $descripcion, $importe, $orden_id, $factura) {
		global $asientos; global $dbpfx;
		if($asientos == '1') {
			if($terc == 0) {
				$pregunta = "SELECT c.cuenta_contable FROM " . $dbpfx . "funciones f, " . $dbpfx . "cont_cat c WHERE f.fun_num = '" . $num_funcion . "' AND f.cat_id = c.cat_id LIMIT 1";
				$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de función! " . $pregunta);
			} elseif($terc == 1) {
				$pregunta = "SELECT cuenta_contable FROM " . $dbpfx . "proveedores WHERE prov_id = '" . $num_funcion . "' LIMIT 1";
				$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de proveedor! " . $pregunta);
			} elseif($terc == 2) {
				$pregunta = "SELECT cuenta_contable FROM " . $dbpfx . "usuarios WHERE usuario = '" . $num_funcion . "' LIMIT 1";
				$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de usuario! " . $pregunta);
			} elseif($terc == 3) {
				$pregunta = "SELECT cuenta_contable FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $num_funcion . "' LIMIT 1";
				$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de aseguradora! " . $pregunta);
			} elseif($terc == 4) {
				$preg0 = "SELECT cliente_empresa_id FROM " . $dbpfx . "clientes WHERE cliente_id = '" . $num_funcion . "'";
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cliente! " . $preg0);
				$clie = mysql_fetch_array($matr0);
				$preg1 = "SELECT cuenta_contable FROM " . $dbpfx . "empresas WHERE empresa_id = '" . $clie['cliente_empresa_id'] . "' LIMIT 1";
				$matriz = mysql_query($preg1) or die("ERROR: Fallo selección de empresa! " . $preg1);
			} elseif($terc == 5) {
				$pregunta = "SELECT cuenta_contable FROM " . $dbpfx . "cont_cuentas WHERE ban_id = '" . $num_funcion . "' LIMIT 1";
				$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de banco! " . $pregunta);
			}
			$fun = mysql_fetch_array($matriz);
			$filas = mysql_num_rows($matriz);
			if($filas > 0) {
				$sqlarray = array('lib_poliza_per' => $ciclo,
					'lib_poliza' => $polnum,
					'lib_cuenta' => $fun['cuenta_contable'],
					'lib_tipo' => $tipo,
					'lib_descripcion' => $descripcion);
				if($tipo == '0') { $sqlarray['lib_debe'] = $importe; $exito = 1; }
				elseif($tipo == '1') { $sqlarray['lib_haber'] = $importe;  $exito = 1; }
				else { $exito = 0;}
			} else {
				$exito = 0;
			}
			if($exito == '1') {
				$lib_id = ejecutar_db($dbpfx . 'cont_libro_diario', $sqlarray, 'insertar');
			} else {
				bitaconta($ciclo, $polnum, 'No se registro el movimiento contable de ' . $num_funcion . ' ' . $descripcion . ' por ' . $importe, $dbpfx);
			}
		} else {
			$exito = 2;
		}
		return $exito;
	}

	function regPoliza($poltipo, $descripcion, $factura) {
		global $asientos; global $dbpfx;
		if($asientos == '1') {

			$ciclo = date('Ym', time());

			$preg0 = "SELECT * FROM " . $dbpfx . "cont_ciclos WHERE ciclo_id = '$ciclo'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de ciclos!");
			$per = mysql_fetch_array($matr0);
			$fila = mysql_num_rows($matr0);
			if($fila < 1) {
				$sql_data = array('ciclo_id' => $ciclo);
				ejecutar_db($dbpfx . 'cont_ciclos', $sql_data, 'insertar');
				$polnum = 1;
				unset($sql_data);
			} else {
				$polnum = intval($per['ciclo_poliza']) + 1;
			}

			$sql_data = array('poliza_ciclo' => $ciclo,
				'poliza_num' => $polnum,
				'poliza_tipo' => $poltipo,
				'poliza_descripcion' => $descripcion,
				'poliza_factura' => $factura,
				'poliza_estatus' => '5',
				'poliza_fecha_ultima' => date('Y-m-d H:i:s', time()),
				'usuario' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'cont_polizas', $sql_data);
			bitaconta($ciclo, $polnum, $descripcion, $dbpfx);
			unset($sql_data);

			$param = "ciclo_id = '$ciclo'";
			$sql_data = array('ciclo_poliza' => $polnum);
			ejecutar_db($dbpfx . 'cont_ciclos', $sql_data, 'actualizar', $param);
			$resultado = array('ciclo' => $ciclo, 'polnum' => $polnum);
			return $resultado;
		}
	}

	function bitaconta($ciclo, $poliza, $evento, $dbpfx) {
		$data = ['ciclo' => $ciclo, 'poliza' => $poliza, 'usuario' => $_SESSION['usuario'], 'evento' => $evento];
		$bit_id = ejecutar_db($dbpfx . 'cont_bitacora', $data, 'insertar');
		if ($comentario!='') {
			$data = ['bit_id' => $bit_id, 'orden_id' => $orden_id, 'interno' => $interno, 'comentario' => $comentario, 'usuario' => $_SESSION['usuario'], 'sub_orden_id' => $sub_orden_id];
			ejecutar_db($dbpfx . 'comentarios', $data, 'insertar');
		}
	}

	function cambioEstatus($orden_id, $estatus, $dbpfx) {
//		global $valida_accesos;
			$pregunta = "SELECT * FROM " . $dbpfx . "bitacora WHERE orden_id = '" . $orden_id . "' AND bit_estatus LIKE '%Cambio a estatus " . $estatus . "%'";
			$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de bitacora!" . $pregunta);
			$filas = mysql_num_rows($matriz);
			if($filas > 0) {
				while($res = mysql_fetch_array($matriz)) {
					$bit[] = array('usuario' => $res['usuario'], 'fecha' => $res['bit_fecha']);
				}
			}
		return $bit;
	}

	function limpiar_especiales($string) {
		$string = trim($string);
		$string = str_replace(
			array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
			array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
			$string
		);

		$string = str_replace(
			array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
			array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
			$string
		);

		$string = str_replace(
			array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
			array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
			$string
		);

		$string = str_replace(
			array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
			array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
			$string
		);

		$string = str_replace(
			array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
			array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
			$string
		);

		$string = str_replace(
			array('ñ', 'Ñ', 'ç', 'Ç'),
			array('n', 'N', 'c', 'C',),
			$string
		);

		$string = str_replace(
			array(' '),
			array('-'),
			$string
		);

		return $string;
	}

	function actualiza_pedido($pedido_id, $dbpfx, $btc) {

// ------ Determinar el estatus del pedido
		$preg = "SELECT * FROM " . $dbpfx . "pedidos WHERE pedido_id = '" . $pedido_id . "'";
		$matr = mysql_query($preg) or die("ERROR: Fallo selección de pedidos! " . $preg);
		$pedat = mysql_fetch_array($matr);

		// --- Determinar estatus de items del pedido para determinar el estatus base (0, 5, 7 ó 10) del pedido
		$preg1 = "SELECT op_id, op_recibidos, op_cantidad, op_costo, op_precio, op_pres, op_tangible FROM " . $dbpfx . "orden_productos WHERE op_pedido='" . $pedido_id . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden_productos! " . $preg1);
		$fila1 = mysql_num_rows($matr1);
		$sql_data = array();
			$recibido = 1;
			$subtotal = 0; $iva = 0; $calc_utilidad = 'si'; $total_venta = 0; $partidas = 0; $endevo = 0; $devols = 99;
			while ($prod = mysql_fetch_array($matr1)) {
				if($prod['op_tangible'] < '4') {
					$partidas++;
					if($prod['op_recibidos'] < $prod['op_cantidad']) {
						$recibido = 0;
					}
					$subtotal = $subtotal + round(($prod['op_cantidad'] * $prod['op_costo']), 2);
					// ----- Determinar utilidad del pedido
					if($prod['op_pres'] == '1') { // --- Pedido desde Presupuesto ---
						// --- consultar su asociación con los autorizados ---
						$preg_seg = "SELECT op_precio, op_cantidad FROM " . $dbpfx . "orden_productos WHERE op_item_seg = '" . $prod['op_id'] . "'";
						$matr_seg = mysql_query($preg_seg) or die("ERROR: Fallo selección de items relacionados! " . $preg_seg);
						$asociado = mysql_num_rows($matr_seg);
						if($asociado > 0) {
							while ($op_val = mysql_fetch_array($matr_seg)) {
								// --- VERIFICAR QUE EL ITEM ASOCIADO TENGA PRECIO MAYOR A 0 ---
								if($op_val['op_precio'] > 0) {
									$total_venta = $total_venta + ($op_val['op_precio'] * $op_val['op_cantidad']);
								} else {
									// --- Si no hay precio de venta no se podrá calcular la utilidad de todo el pedido ---
									$calc_utilidad = 'no';
								}
							}
						} else {
							// --- Si no hay item asociado no se podrá calcular la utilidad de todo el pedido ---
							$calc_utilidad = 'no';
						}
					} else {
						if($prod['op_precio'] > 0) {
							$total_venta = $total_venta + ($prod['op_precio'] * $prod['op_cantidad']);
						} else {
							// --- Si no hay precio de venta no se podrá calcular la utilidad de todo el pedido ---
							$calc_utilidad = 'no';
						}
					}
				} elseif($prod['op_tangible'] == '8') {
					$preg2 = "SELECT dictamen FROM " . $dbpfx . "cambdevol_elementos WHERE op_id = '" . $prod['op_id'] . "' AND tipo_cd = '1'";
					$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de devoluciones! " . $preg2);
					$dev_id = mysql_fetch_array($matr2);
					if($dev_id['dictamen'] < $devols) { $devols = $dev_id['dictamen']; }
					$endevo = 1;
				}
			}
			// --- Se calcula utilidad del pedido ---
			if($calc_utilidad == 'si' && $partidas > 0) {
				$sql_data['utilidad'] = (($total_venta - $subtotal) / $total_venta) * 100;
			} else {
				$sql_data['utilidad'] = 'null';
			}
			$sql_data['subtotal'] = $subtotal;
			$orden_id = $pedat['orden_id'];

			$preg_prov = "SELECT prov_iva FROM " . $dbpfx . "proveedores WHERE prov_id = '" . $pedat['prov_id'] . "'";
			$matr_prov = mysql_query($preg_prov) or die("ERROR: Fallo selección de proveedor! " . $preg_prov);
			$prov = mysql_fetch_array($matr_prov);

			$sql_data['impuesto'] = round(($subtotal * $prov['prov_iva']), 2);
			$monto_pedido = round(($subtotal + $sql_data['impuesto']),2);

// ------ Determinar monto de las facturas del pedido ------
			$preg_facturas = "SELECT f_monto, fact_id FROM " . $dbpfx . "facturas_por_pagar WHERE doc_int_id = '" . $pedido_id . "' AND pagada < '2'";
			$matr_facturas = mysql_query($preg_facturas) or die("ERROR: Fallo selección de facturas! " . $preg_facturas);
			$monto_facturas = 0;
			while($facturas = mysql_fetch_array($matr_facturas)) {
				$monto_facturas = $monto_facturas + round($facturas['f_monto'],2);
			}

// ------ Determina los pagos al pedido -------
			$preg_pagos = "SELECT pago_id, fact_id, monto, fecha FROM " . $dbpfx . "pagos_facturas WHERE pedido_id = '" . $pedido_id . "'";
			$matr_pagos = mysql_query($preg_pagos) or die("ERROR: Fallo selección de pagos!" . $preg_pagos);
			$monto_pagos = 0; $monto_adelantos = 0;
			while($pagos = mysql_fetch_array($matr_pagos)) {
				if(!is_null($pagos['fact_id']) && ($pagos['fact_id'] > 0)) {
					$monto_pagos = $monto_pagos + round($pagos['monto'],2);
				} else {
					$monto_adelantos = $monto_adelantos + round($pagos['monto'],2);
				}
				if(strtotime($fepag) < strtotime($pagos['fecha'])) {
					$fepag = $pagos['fecha'];
				}
			}
			$pagado = round(($monto_pagos + $monto_adelantos),2);

			if($recibido == 0) {
				// --- Determinar estatus de pedidos no recibidos
				if($pagado > 0) {
					$coment = 'Anticipo, Items por Recibir';
					$sql_data['pedido_estatus'] = '7';
				} else {
					$coment = 'Pedido recibido por proveedor';
					$sql_data['pedido_estatus'] = '5';
				}
			} else {
				// --- Si el pedido es con cargo a la aseguradora, se debe dar por terminado.
				if($pedat['pedido_tipo'] == '1') {
					$coment = 'Pedido con Cargo a Aseguradora Recibido';
					$sql_data['pedido_estatus'] = '99';
				// --- Determinar estatus de pedidos recibidos
				} elseif($pedat['pedido_estatus'] != '90' && $pedat['pedido_estatus'] != '92') {
					$sql_data['pedido_pagado'] = '0';
					if($monto_facturas == '0' && $monto_adelantos == '0' && $partidas > 0) {
						$coment = 'Items Recibidos sin pagos y sin registro de documentos de pago';
						$sql_data['pedido_estatus'] = '10';
					} elseif($monto_pedido > $monto_facturas && $monto_facturas > '0') {
						$coment = 'Registro parcial de Documentos de pago';
						$sql_data['pedido_estatus'] = '15';
					} elseif($monto_pedido == $monto_facturas && $pagado == '0' && $partidas > 0) {
						$coment = 'Documentos de cobro registrados sin pagos registrados';
						$sql_data['pedido_estatus'] = '20';
					} elseif($pagado < $monto_pedido && $monto_facturas == '0' && $partidas > 0) {
						$coment = 'Pago parcial sin Documentos de cobro registrados';
						$sql_data['pedido_estatus'] = '23';
					} elseif($pagado < $monto_pedido && $partidas > 0) {
						$coment = 'Pedido pagado parcialmente, Items recibidos';
						$sql_data['pedido_estatus'] = '25';
					} elseif($pagado >= ($monto_pedido -1) && ($monto_pedido > $monto_facturas || $monto_pagos < $monto_facturas) && $partidas > 0) {
						$sql_data['pedido_pagado'] = '1';
						$coment = 'Pedido pagado sin documentos de pago completos';
						$sql_data['pedido_estatus'] = '30';
					} elseif(($pagado + 1) > $monto_pedido && $endevo == 1 && $devols < '99') {
						$coment = 'No se ha completado la devolución del pedido';
						$sql_data['pedido_estatus'] = '50';
					} elseif(($pagado + 1) > $monto_pedido && $endevo == 1) {
						$coment = 'No se ha registrado una Nota de Crédito';
						$sql_data['pedido_estatus'] = '55';
					} elseif($pagado >= $monto_pedido) {
						$sql_data['pedido_pagado'] = '1';
						$coment = 'Pedido terminado: recibido y pagado';
						$sql_data['pedido_estatus'] = '99';
					} else {
// ------ Pedido en estatus no identificado
						$sql_data['pedido_estatus'] = '95';
						bitacora($orden_id, 'Pedido en revisión de Soporte Técnico.', $dbpfx, 'Pedido ' . $pedido_id . ' con estatus indeterminado', 3, '', '', 701);
					}
				}
			}
		if($partidas == 0 && $monto_pedido == 0 && $pagado >= $monto_facturas) {
// ------ Localiza pagos para dejarlos huérfanos si los hubiera -------
// ------ Determina los pagos al pedido -------
			$preg_pagos = "SELECT pf.pago_id, pp.pago_referencia FROM " . $dbpfx . "pagos_facturas pf, " . $dbpfx . "pedidos_pagos pp WHERE pf.pedido_id = '" . $pedido_id . "' AND (pf.fact_id < '0' OR pf.fact_id IS NULL) AND pf.pago_id = pp.pago_id";
			$matr_pagos = mysql_query($preg_pagos) or die("ERROR: Fallo selección de pagos!" . $preg_pagos);
			while($pags = mysql_fetch_array($matr_pagos)) {
				$sqlpf = ['pedido_id' => 'null', 'fact_id' => 'null'];
				$sqlpp = ['pago_referencia' => $pags['pago_referencia'] . '. Pedido Original: ' . $pedido_id];
				$param = " pago_id ='" . $pags['pago_id'] . "'";
				ejecutar_db($dbpfx . 'pagos_facturas', $sqlpf, 'actualizar', $param);
				ejecutar_db($dbpfx . 'pedidos_pagos', $sqlpp, 'actualizar', $param);
			}
			if($pedat['pedido_estatus'] >= '50') {
				$sql_data['pedido_estatus'] = '92';
				$coment = 'Pedido Cancelado';
			} else {
				$sql_data['pedido_estatus'] = '90';
				$coment = 'Pedido Cancelado';
			}
			$sql_data['subtotal'] = '0';
			$sql_data['impuesto'] = '0';
			$sql_data['pedido_pagado'] = '0';
		}

// ------ Si el pedido está pagado, se debe marcar como pagado aún cuando no tenga facturas ---
		if($pagado >= $monto_pedido && $monto_pedido > 0) {
			$sql_data['pedido_fecha_de_pago'] = $fepag;
			$sql_data['pedido_pagado'] = '1';
		}

//		if($btc == 1) {
			bitacora($orden_id, 'Pedido ' . $pedido_id . ': ' . $coment, $dbpfx);
//		}
		$parametros = " pedido_id ='" . $pedido_id . "'";
		ejecutar_db($dbpfx . 'pedidos', $sql_data, 'actualizar', $parametros);
		$mmm = ['TotalPagado' => $pagado, 'PagosFacturas' => $monto_pagos, 'MontoFacturas' => $monto_facturas];
		return $mmm;
	}

	function refaccionesCompletas($sub_orden_id, $dbpfx) {
		global $preaut;
		$preg1 = "SELECT * FROM " . $dbpfx . "orden_productos WHERE sub_orden_id = '" . $sub_orden_id . "' AND op_tangible = '1'";
//		if($preaut == 1) { $preg1 .= " AND (op_pres = '1' OR (op_pres IS NULL AND op_item_seg IS NULL))"; }
//		else { $preg1 .= " AND ((op_pres = '1' AND op_pedido > '0') OR (op_pres IS NULL AND op_item_seg IS NULL))"; }
		$preg1 .= " AND ((op_pres = '1' AND op_pedido > '0') OR (op_pres IS NULL AND op_item_seg IS NULL))";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de refacciones autorizadas!" . $preg1);
		$fila1 = mysql_num_rows($matr1);
		$oprec = 0;
		if($fila1 > 0) {
			while($op = mysql_fetch_array($matr1)) {
				if($op['op_ok'] == '1') { $oprec++;}
			}
			$porcen = round((($oprec / $fila1)*100),0);
		} else {
			$porcen = 200;
		}
		return $porcen;
	}

	function recalcUtilPed($pedido_id, $dbpfx) {
//		global $dbpfx;
		if($pedido_id > 0) {
			$preg1 = "SELECT op_id, op_recibidos, op_cantidad, op_costo, op_precio, op_pres FROM " . $dbpfx . "orden_productos WHERE op_pedido='" . $pedido_id . "' AND op_tangible <= '2'";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de orden_productos!");
			$fila1 = mysql_num_rows($matr1);
			$sql_data = array();
			if($fila1 > 0 ) {
				$subtotal = 0; $iva = 0; $calc_utilidad = 'si'; $total_venta = 0;
				while ($prod = mysql_fetch_array($matr1)) {
					if($prod['op_pres'] == '1') { // --- Pedido desde Presupuesto ---
						// --- consultar su asociación con los autorizados ---
						$preg_seg = "SELECT op_precio, op_cantidad FROM " . $dbpfx . "orden_productos WHERE op_item_seg = '" . $prod['op_id'] . "'";
						$matr_seg = mysql_query($preg_seg) or die("ERROR: Fallo selección de items relacionados! " . $preg_seg);
						$asociado = mysql_num_rows($matr_seg);
						if($asociado > 0) {
							while ($op_val = mysql_fetch_array($matr_seg)) {
								// --- VERIFICAR QUE EL ITEM ASOCIADO TENGA PRECIO MAYOR A 0 ---
								if($op_val['op_precio'] > 0) {
									$total_venta = $total_venta + ($op_val['op_precio'] * $op_val['op_cantidad']);
								} else {
									// --- Si no hay precio de venta no se podrá calcular la utilidad de todo el pedido ---
									$calc_utilidad = 'no';
								}
							}
						} else {
							// --- Si no hay item asociado no se podrá calcular la utilidad de todo el pedido ---
							$calc_utilidad = 'no';
						}
					} else {
						if($prod['op_precio'] > 0) {
							$total_venta = $total_venta + ($prod['op_precio'] * $prod['op_cantidad']);
						} else {
							// --- Si no hay precio de venta no se podrá calcular la utilidad de todo el pedido ---
							$calc_utilidad = 'no';
						}
					}
					$subtotal = $subtotal + ($prod['op_costo'] * $prod['op_cantidad']);
				}
				// --- Se ajustan subtotal e impuesto del pedido.
				$consulta = "SELECT pr.prov_iva FROM " . $dbpfx . "proveedores pr, " . $dbpfx . "pedidos p WHERE p.pedido_id = $pedido_id AND pr.prov_id = p.prov_id";
				$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
				$provs = mysql_fetch_array($arreglo);
				$subtotal = round(($subtotal + 0.004999),2);
				$iva = round(($subtotal * $provs['prov_iva']), 2);
				$sql_data['subtotal'] = $subtotal;
				$sql_data['impuesto'] = $iva;
				// --- Se calcula utilidad del pedido ---
				if($calc_utilidad == 'si') {
					$sql_data['utilidad'] = (($total_venta - $subtotal) / $total_venta) * 100;
				} else {
					$sql_data['utilidad'] = 'null';
				}
				$parametros = " pedido_id ='" . $pedido_id . "'";
				ejecutar_db($dbpfx . 'pedidos', $sql_data, 'actualizar', $parametros);
				return $sql_data;
			}
		}
	}

	function determina_area($cadena, $conexion){

		$reemplazar = [":", "-", ".", "/", ";", "(", ")", "+", "*", "$"];
		$quitar_simbolos = str_replace($reemplazar, " ", $cadena); // --- Reemplazar simbolos por espacios ---
		$quitar_numeros = preg_replace('/[0-9]+/', '', $quitar_simbolos); // --- quitar números ---
		//echo '<pre>Antes de limpieza: ' . $cadena . '<br>';
		//echo 'Primer limpieza: ' . $quitar_simbolos . '<br>';
		//echo 'Segunda limpieza: ' . $quitar_numeros . '<br><br>';
		$segmentos = preg_split("/[\s,]+/", $quitar_numeros);
		$cont = 0;
		// --- Recorrer el array generado ---
		foreach($segmentos as $key => $val){
			//echo 'string a procesar: <b>' . $val . '</b><br>';
			// --- consultar coincidencia ---
			// --- Extraer longitud de la palabra ---
			$long = iconv_strlen($val,'UTF-8');
			//echo 'Longitud = ' . $long . '<br>';
			if($long > '2'){ // --- Si la palabra consta de menos de 3 caracteres no se procesa ---

				$pregunta_coincidencia = "SELECT * FROM productos_mo WHERE nombre  LIKE '" . $val . "%' LIMIT 1";
				$matr_coincidencia = mysql_query($pregunta_coincidencia) or die ("Falló conexión con ASEBase - Funciones.php " . $pregunta_coincidencia);
				$coincidencia = mysql_num_rows($matr_coincidencia);
				if($coincidencia == 1){ // --- si la palabra tuvo coincidencia
					$match = mysql_fetch_assoc($matr_coincidencia);
					//echo 'Preg ' . $pregunta_coincidencia . ' <br> SE ENCONTRÓ UNA COINCIDENCIA<br>' . $val . ' Coincidió con: <b>' . $match['nombre'] . '</b> del área: ' . $match['area'] . '<br>';
					// --- Almacenar en un array el area a la que coincidio ---
					$procesados[$cont]['nombre'] = $val;
					$procesados[$cont]['area'] = $match['area'];
					$cont++;
				}
			}
			//echo '<br>';
		}
		$matches = count($procesados);
//		print_r($procesados);
		if($matches == 1){ // --- si se encontró solo una coincidencia se asociara a esa area ---
			//echo '<br>Palabra con ' . $matches . ' match, se asociará al área: ' . $procesados[0]['area'] . '<br>';
			$asignar_a = $procesados[0]['area'];
		}
		elseif($matches > 1){ // --- Si hubo más de una coincidencia, evaluarlas para saber a cuál se agregará ---
			//echo '<br>Palabra con ' . $matches . ' matches<br>';
			$area_1 = 0;
			$area_6 = 0;
			foreach($procesados as $key => $val){
				//echo '$key = ' . $key . ' $val = ' . $val['nombre'] . ' ' . $val['area'] . '<br>';
				if($val['area'] == 1){
					$area_1++;
				}
				elseif($val['area'] == 6){
					$area_6++;
				}
			}

			if($area_1 > $area_6){
				$asignar_a = 1;
				//echo 'área ganadora = ' . $asignar_a . ' con: ' . $area_1 . ' matches<br>';
			}
			elseif($area_6 > $area_1){
				$asignar_a = 6;
				//echo 'área ganadora = ' . $asignar_a . ' con: ' . $area_6 . ' matches<br>';

			}
			elseif($area_6 == $area_1){
				$asignar_a = 0;
				//echo 'área ganadora = ' . $asignar_a . ' con: un empate de matches<br>';
			}
		}
		else{ // --- Si no hubo ninguún match, guardar en array de no encontrados ---
			//echo '<br>Palabra sin ' . $matches . ' matches<br>';
			$asignar_a = 0;

		}
		return $asignar_a;

	}

	function conv_segundos($hora){ // --- Formato 00:00:00 ---

		$horas = substr($hora,0,2);
		//echo '$horas ' . $horas . '<br>';
		$minutos = substr($hora,3,2);
		//echo '$minutos ' . $minutos . '<br>';
		$segundos = substr($hora,6,2);
		//echo '$segundos ' . $segundos . '<br>';
		$resultado = ((($horas*60)*60)+($minutos*60)+$segundos);
		return $resultado;

	}

	function conv_seg_hora($seg){ // --- Recibe cadena de segundos y los convierte a 00:00:00 ---

		if($seg < 1){
			$seg = $seg * -1;
			$negativa = 1;
		}
		//echo $seg;
		$horas = floor($seg/3600);
		$minutos = floor(($seg - ($horas * 3600)) / 60);
		$segundos = $seg - ($minutos * 60) - ($horas * 3600);
		if($horas == 0){ $horas = '00'; }
		if($horas < 10 && $horas > 0){ $horas = '0' . $horas; }
		if($minutos == 0){ $minutos = '00'; }
		if($minutos < 10 && $minutos > 0){ $minutos = '0' . $minutos; }
		if($segundos == 0){ $segundos = '00'; }
		if($segundos < 10 && $segundos > 0){ $segundos = '0' . $segundos; }
		// --- Agrupar ---
		$hora = $horas . ':' . $minutos . ':' . $segundos;
		if($negativa == 1){
			$hora = '- ' . $hora;
		}
		return $hora;
	}

	function notificaTelegram($chat_id, $motivo) {
		global $TelegramToken;
		$data = ['chat_id' => $chat_id, 'text' => $motivo];
		$response = file_get_contents("https://api.telegram.org/bot$TelegramToken/sendMessage?" . http_build_query($data));
		// --- Testigo en Celular MoviStar de ADZ
		$datos = ['chat_id' => '649280389', 'text' => $chat_id . ' ' . $motivo];
		$testigo = file_get_contents("https://api.telegram.org/bot$TelegramToken/sendMessage?" . http_build_query($datos));
		return $response;
	}
