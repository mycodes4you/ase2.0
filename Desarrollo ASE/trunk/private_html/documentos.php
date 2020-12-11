<?php 
foreach($_POST as $k => $v){$$k=$v;}  // echo $k.' -> '.$v.' | <br>';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

include('idiomas/' . $idioma . '/documentos.php');

if ($accion==="listar") {

	if(validaAcceso('1025000', $dbpfx)=='1' || validaAcceso('1025025', $dbpfx)=='1' || $solovalacc != '1') {
		include('parciales/encabezado.php'); 
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
	} elseif ($solovalacc != '1' && $_SESSION['codigo'] < '2000' && $_SESSION['codigo'] != '60' && $_SESSION['codigo'] != '70' && $_SESSION['codigo'] != '75') {
		include('parciales/encabezado.php'); 
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
	} else {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso para Administradores']);
	}

// ------ Revisamos si el número de usuario está dentro del array de usuarios a omitir
	$omitir = 0;
	foreach($usu_omit_fd as $i => $j) {
		if($_SESSION['usuario'] == $j) {
			$omitir = 1;
		}
	}
	
	//	echo 'Estamos en la sección listar.';
	$error = 'si'; $num_cols = 0;
	$mensaje= 'Se necesita al menos un dato para buscar.<br>';
	if($orden_id!='') {
		$mensaje='';
		$pregunta3 = "SELECT orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $orden_id . "'";
		// $funnum = 1025025; Reducir selección a sólo ingreso y avance.
		$matriz3 = mysql_query($pregunta3) or die("ERROR: Fallo seleccion!");
		$orden = mysql_fetch_array($matriz3);
		if($_SESSION['codigo'] >= '2000') {
			$preg1 = "SELECT sub_aseguradora FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "'";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de tareas! " . $preg1);
			while($aseg = mysql_fetch_array($matr1)) {
				$ase[$aseg['sub_aseguradora']] = 1;
			}
			if($ase[$_SESSION['aseg']] == 1 ) {
				lista_documento ($orden_id, $orden['orden_estatus'], $dbpfx, $presel, '', $omitir, $ayuda['AyudaAccesos']);
			}
		} else {
			lista_documento ($orden_id, $orden['orden_estatus'], $dbpfx, $presel, '', $omitir, $ayuda['AyudaAccesos']);
		}
		if($_SESSION['codigo'] < '2000') {
			echo '	
			<form action="documentos.php?accion=insertar" method="post" enctype="multipart/form-data">
				<table cellpadding="0" cellspacing="0" border="0" class="agrega">
					<tr>
						<td colspan="2">
							<span class="alerta">' . $_SESSION['documento']['mensaje'] . '</span>
						</td>
					</tr>
					<tr>
						<td>
							' . $lang['Agregar múltiples documentos'] . '
						</td>
						<td style="text-align:left;">
							<input type="file" name="imagen[]" multiple size="30" />
						</td>
					</tr>
					<tr>
						<td>
							Indique el nombre del documento a agregar:
						</td>
						<td style="text-align:left;">
							<input type="text" name="nom_doc" size="30" value="' . $_SESSION['documento']['nom_doc'] . '" >
						</td>
					</tr>'."\n";

			if($restrictotoper == 1 && ($_SESSION['codigo'] == '60' || $_SESSION['codigo'] == '70' || $_SESSION['codigo'] == '75')) {
				echo '
					<input type="hidden" name="avance" value="1" >'."\n";
			} else {
				echo '		
					<tr>
						<td>
							<span style="font-weight:bold; color:red;">' . $lang['Confidencial'] . '</span>:
						</td>
						<td style="text-align:left;">
							Sí <input type="checkbox" name="confidencial" value="1" >
						</td>
					</tr>'."\n";

				if($img_avances == '1') {
				echo '		
					<tr>
						<td>
							La imagen es <span style="font-weight:bold; color:red;">' . $lang['AVANCE DE REPARACIÓN?'] . '</span>:
						</td>
						<td style="text-align:left;">
							Sí <input type="checkbox" name="avance" value="1" >
						</td>
					</tr>'."\n";
				}
			}
			$_SESSION['documento']['mensaje']='';
			echo '		
					<tr>
						<td colspan="2">
							<input type="hidden" name="orden_id" value="' . $orden_id . '" />
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:left;">
							<input type="submit" value="Enviar" class="btn btn btn-success"/>&nbsp;
							<input type="reset" name="limpiar" value="Borrar" class="btn btn btn-danger"/>
						</td>
					</tr>
				</tr>
			</table>
		</form>
		<br>'."\n";
		}
		echo '
		<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '">
			<img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Orden de Trabajo" title="Regresar a la Orden de Trabajo">
		</a>
		<br>'."\n";

	} elseif($previa_id!='') {
		lista_documento ('', '', $dbpfx, $presel, $previa_id, $omitir);
		echo '
		<a href="previas.php?accion=consultar&previa_id=' . $previa_id . '">
			<img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar al Presupuesto Previa" title="Regresar al Presupuesto Previa">
		</a>
		<br>
		<form action="documentos.php?accion=insertar" method="post" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" border="0" class="agrega">
				<tr>
					<td>' . $lang['Agregar múltiples documentos'] . '</td>
					<td style="text-align:left;"><input type="file" name="imagen[]" multiple size="30" /></td>
				</tr>
				<tr>
					<td>Indique el nombre del documento a agregar:</td>
					<td style="text-align:left;">
						<input type="text" name="nom_doc" size="30" value="' . $_SESSION['documento']['nom_doc'] . '" >
					</td>
				</tr>'."\n";

		if($restrictotoper == 1 && ($_SESSION['codigo'] == '60' || $_SESSION['codigo'] == '70' || $_SESSION['codigo'] == '75')) {
			echo '
				<input type="hidden" name="avance" value="1" >'."\n";
		} else {
			echo '		
				<tr>
					<td>
						<span style="font-weight:bold; color:red;">' . $lang['Confidencial'] . '</span>:
					</td>
					<td style="text-align:left;">
						Sí <input type="checkbox" name="confidencial" value="1" >
					</td>
				</tr>'."\n";

			if($img_avances == '1') {
			echo '		
				<tr>
					<td>
						La imagen es <span style="font-weight:bold; color:red;">' . $lang['AVANCE DE REPARACIÓN?'] . '</span>:
					</td>
					<td style="text-align:left;">
						Sí <input type="checkbox" name="avance" value="1" >
					</td>
				</tr>'."\n";
			}
		}
		$_SESSION['documento']['mensaje']='';
		echo '		
				<tr class="cabeza_tabla">
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="hidden" name="previa_id" value="' . $previa_id . '" />
					</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:left;">
						<input type="submit" value="Enviar" class="btn btn btn-succsess"/>&nbsp;
						<input type="reset" name="limpiar" value="Borrar" class="btn btn btn-danger"/>
					</td>
				</tr>
			</table>
		</form>'."\n";
		
	} else {
		if (($cliente_id!='') || ($vehiculo_id!='')) {
			$mensaje='';
			$pregunta2 = "SELECT orden_id, orden_estatus FROM " . $dbpfx . "ordenes WHERE ";
			if ($cliente_id) {$pregunta2 .= "orden_cliente_id = '$cliente_id' ";}
			if (($cliente_id) && ($vehiculo_id)) {$pregunta2 .= "AND orden_vehiculo_id = '$vehiculo_id' ";}
			elseif ($vehiculo_id) {$pregunta2 .= "orden_vehiculo_id = '$vehiculo_id' ";}
			$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
			$num_ordenes = mysql_num_rows($matriz2);
			if ($num_ordenes > 0) {
				while($orden = mysql_fetch_array($matriz2)) {
					lista_documento ($orden['orden_id'], $orden['orden_estatus'], $dbpfx);
					echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden['orden_id'] . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Mostrar la Orden de Trabajo" title="Mostrar a la Orden de Trabajo"></a>';
					if ($cliente_id!='') {
						echo ' <a href="clientes.php?accion=consultar&cliente_id=' . $cliente_id . '"><img src="idiomas/' . $idioma . '/imagenes/clientes.png" alt="Mostrar datos de Cliente" title="Mostrar datos de Cliente"></a>';
					}
					if ($vehiculo_id!='') {
						echo ' <a href="vehiculos.php?accion=consultar&vehiculo_id=' . $vehiculo_id . '"><img src="idiomas/' . $idioma . '/imagenes/automovil.png" alt="Mostrar datos del Vehículo" title="Mostrar datos del Vehículo"></a>';
					}
				}
			} else {
				$mensaje='No se encontró ningún documento para este vehículo.';
			}
		}
	}
	
	//	echo $pregunta;
	

	echo '<p>' . $mensaje . '</p>';

}

elseif ($accion==="avances" && $img_avances == '1') {

	if(validaAcceso('1025015', $dbpfx)=='1' || ($solovalacc != '1' && ($_SESSION['rol04'] == '1' || $_SESSION['rol07'] == '1'))) {
		// Acceso autorizado
	} else {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso para Administradores']);
	}

	include('parciales/encabezado.php');
	echo '	<div id="body">'."\n";
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">'."\n";
	//	echo 'Estamos en la sección agregar';
	echo '	<form action="documentos.php?accion=insertar" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">';
	//	echo '		<tr><td colspan="2"><span class="alerta">' . $_SESSION['documento']['mensaje'] . '</span></td></tr>'."\n";
	echo '		<tr class="cabeza_tabla"><td colspan="2">Fotos de avance de reparación para la OT <input type="text" name="orden_id" value="' . $orden_id . '" /></td></tr>';
	for($i=0;$i<1;$i++) {
		echo '		<tr><td>Foto de avance de reparación: <input type="file" name="imagen[' . $i . ']" multiple size="30" /></td></tr>'."\n";
	}
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2"><input type="hidden" name="avance" value="1" ></td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Subir Fotos" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
		</tr>
	</table>
	</form>';
	//	print_r($_SERVER); 
}

elseif ($accion==="agregar") {

	if(validaAcceso('1025010', $dbpfx)=='1' || ($solovalacc != '1')) {
		include('parciales/encabezado.php'); 
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
	} elseif ($solovalacc != '1' && $_SESSION['codigo'] < '2000' && $_SESSION['codigo'] != '60' && $_SESSION['codigo'] != '70' && $_SESSION['codigo'] !== '75') {
		include('parciales/encabezado.php'); 
		echo '	<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		<div id="principal">'."\n";
	} else {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso para Administradores']);
	}

	//	echo 'Estamos en la sección agregar';
	echo '	<form action="documentos.php?accion=insertar" method="post" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" border="0" class="agrega">';
	echo '		<tr><td colspan="2"><span class="alerta">' . $_SESSION['documento']['mensaje'] . '</span></td></tr>
		<tr>
			<td>' . $lang['Agregar múltiples documentos'] . '</td>
			<td style="text-align:left;"><input type="file" name="imagen[]" multiple size="30" /></td>
		</tr>
		<tr>
			<td>Indique el nombre del documento a agregar, mínimo 5 caracteres:</td>
			<td style="text-align:left;">
				<input type="text" name="nom_doc" size="30" value="' . $_SESSION['documento']['nom_doc'] . '" >
			</td>
		</tr>'."\n";
		echo '		<tr>
			<td><span style="font-weight:bold; color:red;">' . $lang['Confidencial'] . '</span>:</td>
			<td style="text-align:left;">
				Sí <input type="checkbox" name="confidencial" value="1" >
			</td>
		</tr>'."\n";
	if($img_avances == '1') {
		echo '		<tr>
			<td>La imagen es <span style="font-weight:bold; color:red;">' . $lang['AVANCE DE REPARACIÓN?'] . '</span>:</td>
			<td style="text-align:left;">
				Sí <input type="checkbox" name="avance" value="1" >
			</td>
		</tr>'."\n";
	}		
	echo '		<tr class="cabeza_tabla"><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2"><input type="hidden" name="orden_id" value="' . $orden_id . '" /></td></tr>
		<tr><td colspan="2" style="text-align:left;"><input type="submit" value="Enviar" />&nbsp;<input type="reset" name="limpiar" value="Borrar" /></td></tr>
		</tr>
	</table>
	</form>';
}

elseif($accion==='insertar') {

	if(validaAcceso('1025015', $dbpfx)=='1' || (validaAcceso('1025025', $dbpfx)=='1' && $avance > 0)) {
		$mensaje = '';
	} elseif ($solovalacc != '1' && $_SESSION['codigo'] < '2000' && $_SESSION['codigo'] != '60' && $_SESSION['codigo'] != '70' && $_SESSION['codigo'] !== '75') {
		$mensaje = '';
	} else {
		 redirigir('usuarios.php?mensaje='.$lang['Acceso para Administradores']);
	}

	$_SESSION['msjerror'] = '';

	unset($_SESSION['documento']);
	unset($fotos);
	$_SESSION['documento'] = array();
	$nom_doc = preparar_entrada_bd($nom_doc); $_SESSION['documento']['nom_doc'] = $nom_doc;
	$error = 'no';
	$mensaje = '';
	if($avance > 0 && $nom_doc != '') { $nom_doc = $nom_doc . ' - Avance de Reparación';}
	elseif($avance > 0 && $nom_doc == '') { $nom_doc = 'Avance de Reparación';}
	if (strlen($nom_doc) < 5) { $nom_doc = 'Imagen';}
	$nom_doc = ereg_replace("[^A-Za-zñÑáéíóúÁÉÍÓÚ0-9 ]", "", $nom_doc);
	for($i=0;$i < count($_FILES['imagen']['name']); $i++) {
		$info = pathinfo($_FILES['imagen']['name'][$i]);
		$nombre_archivo = limpiar_especiales($nom_doc);
		if($previa_id != '') {
			$nombre_archivo = 'pv-' . $previa_id . '-' . $nombre_archivo . '-'. $i .'-' . time() . '.' . strtolower($info['extension']);
		} else {
			$nombre_archivo = $orden_id . '-' . $nombre_archivo . '-'. $i .'-' . time() . '.' . strtolower($info['extension']);
		}
        $tammax=4000000;

        if($_FILES['imagen']['size'][$i]>$tammax){
            $_SESSION['msjerror'] .= 'Error, la imagen ' . $_FILES['imagen']['name'][$i] . ' excede el tamaño permitido '.'<br>';
        }

		if (move_uploaded_file($_FILES['imagen']['tmp_name'][$i], DIR_DOCS . $nombre_archivo) && $_FILES['imagen']['size'][$i] <= $tammax) {

			// --- INICIA SUBIDA DE ARCHIVOS A AWS
			require_once 'aws/aws-autoloader.php';

			// --- La carpeta en donde se subuira el archivo
			$path = $dbpfx . '/' . $nombre_archivo;

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

			// --- SUBIR ARCHIVO

			// --- Indicamos la ruta del archivo que se subira
			$source = fopen(DIR_DOCS.$nombre_archivo, 'rb');

			$uploader = new Aws\S3\ObjectUploader(
			    $client,
			    $bucket,
			    $path,
			    $source
			);

			do {
			    try {
			        $result = $uploader->upload();
			        if ($result["@metadata"]["statusCode"] == '200') {
			            $mensaje .= 'Se ha subido correctamente el archivo a: <a href="' . $result["ObjectURL"] . '" target="_blank">' . $result["ObjectURL"] . '</a><br>';
			        }
			        //print($result);
			    } catch (MultipartUploadException $e) {
			        rewind($source);
			        $uploader = new MultipartUploader($client, $source, [
			            'state' => $e->getState(),
			        ]);
			    }
			} while (!isset($result));

                        // --- FIN DE SUBIDA DE ARCHIVOS A AWS

			$sql_data_array = array('doc_nombre' => $nom_doc,
				'doc_usuario' => $_SESSION['usuario'],
				'doc_archivo' => $result["ObjectURL"]);

			// --- Copia el archivo al servidor de respaldo ---
			if($servrespaldo != '') {
				$conecta = ssh2_connect($servrespaldo, 2922);
				ssh2_scp_send($conecta, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, '/home/autoshop/domains/' . $_SERVER['HTTP_HOST'] . '/private_html/documentos/' . $nombre_archivo, 0644);
			}

			if($avance > 0) {
				if($avanmagua == 1) {
					// -----  Variables de la imagen 
					$calidad_imagen= 90;
					// -----  Libreria
					require_once('parciales/imageworkshop.php');

					//------ Función de marca de agua
					function calculAngleBtwHypoAndLeftSide($bottomSideWidth, $leftSideWidth) {
						$hypothenuse = sqrt(pow($bottomSideWidth, 2) + pow($leftSideWidth, 2));
						$bottomRightAngle = acos($bottomSideWidth / $hypothenuse) + 180 / pi();
						return -round(90 - $bottomRightAngle);
					}

					//------	Imagen a procesar
					$norwayLayer = new ImageWorkshop(array("imageFromPath" => DIR_DOCS . $nombre_archivo,));
					//------	Propiedades de la marca de agua
					$textLayer = new ImageWorkshop(array(
						"text" => $nombre_agencia,
						"fontSize" => 60,
						"fontColor" => "ffffff",
						"textRotation" => calculAngleBtwHypoAndLeftSide($norwayLayer->getWidth(), $norwayLayer->getHeight()),
					));
 
					//------ Opacidad al texto
					$textLayer->opacity(70);

					//------ Incorporamos el texto a la imagen
					$norwayLayer->addLayer(1, $textLayer, 0, 0, 'MM');
 
					//------ Resultado de la imagen procesada 
					$image = $norwayLayer->getResult();

					//------ Guardar la imagen procesada
					imagejpeg($image, DIR_DOCS . $nombre_archivo, $calidad_imagen);


                }
                
                
				$sql_data_array['doc_etapa']= $avance;
				$tipo_doc = 'Avance de reparación';
			} 
			if($confidencial == 1) {
				$sql_data_array['doc_clasificado'] = '1';
				$tipo_doc = 'Documento confidencial agregado';
			} else {
				$tipo_doc = 'Documentos agregados';
			}
			if($previa_id != '') { $sql_data_array['previa_id'] = $previa_id; }
			else { $sql_data_array['orden_id'] = $orden_id; }
			ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
			creaMinis($nombre_archivo);
			// --- Borrar archivo
			//unlink(DIR_DOCS.$nombre_archivo);
			if($previa_id != '') {
				bitacora('', 'Documentos de Presupuesto Previo', $dbpfx, '','','',$previa_id);
			} else {
				bitacora($orden_id, $tipo_doc, $dbpfx);
			}
			$_SESSION['documento']['mensaje']='';
			$fotos[] = DIR_DOCS . $nombre_archivo;
		} else {
			$_SESSION['msjerror'] .= 'No se subió el archivo ' . $_FILES['imagen']['name'][$i] . '<br>';
		} 
	}
	unset($_SESSION['documento']);	
	if($notiavances == '1' && count($fotos) > 0 && $avance > 0) {
		include('particular/notiavances.php');
	}
	if($avance > 0 && $_SESSION['codigo'] != '60') {
		redirigir('documentos.php?accion=avances&orden_id=' . $orden_id);
	} else {
		if($previa_id != '') {
			redirigir('documentos.php?accion=listar&previa_id=' . $previa_id);
		} else {
			redirigir('documentos.php?accion=listar&orden_id=' . $orden_id);
		}
	}
}

elseif($accion==='depurar') {

	if(validaAcceso('1025020', $dbpfx)=='1' || validaAcceso('1025030', $dbpfx)=='1' || ($solovalacc != '1' && ($_SESSION['rol02'] == '1' || $_SESSION['rol05'] == '1' || $_SESSION['rol06'] == '1'))) {
		$mensaje = '';
	} else {
		 redirigir('usuarios.php?mensaje=Usuario no autorizado o Estatus no modificable. ' . $enviar);
	}

	unset($_SESSION['documento']);
	$_SESSION['documento'] = array();
	$error = 'no';
	$mensaje = '';
	if (($error === 'no') && (is_array($doc_id))) {
		if($enviar == 'Eliminar' && (($_SESSION['rol02'] == '1' || validaAcceso('1025020', $dbpfx)=='1') || ($solovalacc != '1' && (($_SESSION['rol05'] == '1' || $_SESSION['rol06'] == '1') && ($estatus < 4 || ($estatus >= 24 && $estatus < 30) || $estatus == 20))))) {
			for($i=0;$i<count($doc_id);$i++) {
				if (isset($eliminar[$i])) {
					$pregunta="DELETE FROM " . $dbpfx . "documentos WHERE doc_id = '" . $doc_id[$i] . "'";
					$resultado = mysql_query($pregunta);
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $pregunta . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				}
			}
			bitacora($orden_id, count($doc_id) . ' Documentos removidos.', $dbpfx);
		} elseif($enviar == 'Descargar' && (validaAcceso('1025030', $dbpfx)=='1' || ($solovalacc != '1' && ($_SESSION['rol02'] == '1' || $_SESSION['rol05'] == '1' || $_SESSION['rol06'] == '1') || $_SESSION['codigo'] < '2000'))) {
			bitacora($orden_id, 'Documentos descargados', $dbpfx);
			$archivo = DIR_DOCS . $orden_id.'-descarga.zip';
			if (file_exists($archivo)) { unlink ($archivo); }
			$zip = new ZipArchive();
			 if ($zip->open($archivo, ZIPARCHIVE::CREATE )!==TRUE) {
				exit("No se puede abrir <$archivo>\n");
			} 
			foreach($doc_arch as $i => $j) {
				if (isset($eliminar[$i])) {
					 $zip->addFile(DIR_DOCS . $j, $j);
				}
			}
			$zip->close();
			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=$archivo");
			header("Pragma: no-cache");
			header("Expires: 0");
			readfile("$archivo"); 
			exit;
		} else {
			$_SESSION['msjerror'] = $lang['Estatus Eliminar'];
		}
		redirigir('documentos.php?accion=listar&orden_id=' . $orden_id);
	} else {
		$_SESSION['msjerror'] = 'No se recibió lista de documentos';  
		redirigir('documentos.php?accion=listar&orden_id=' . $orden_id);
	}
}

elseif($accion === 'cambiar_clasificacion'){

	echo 'cambiar clasificación y visibilidad.<br>';

	if($tipo == 'clasificado'){

		echo 'cambió de clasificación.<br>';
		
		// --- Consultar la clasificación del documento ---
		$preg_doc = "SELECT doc_clasificado FROM " . $dbpfx . "documentos WHERE doc_id = '" . $doc_id . "'";
		$matr_doc = mysql_query($preg_doc) or die("Falló! " . $preg_doc);
		$info_doc = mysql_fetch_assoc($matr_doc);

		if($info_doc['doc_clasificado'] == 1){ // --- Cambia a desclasificado ---

			$sql_data_array = [
				'doc_clasificado' => 0,
			];
			$parametros = " doc_id = '" . $doc_id . "'";
			ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'actualizar', $parametros);

		} elseif($info_doc['doc_clasificado'] == 0){ // --- Cambia a clasificado ---

			$sql_data_array = [
				'doc_clasificado' => 1,
				'doc_etapa' => 0,
			];
			$parametros = " doc_id = '" . $doc_id . "'";
			ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'actualizar', $parametros);
		}

	}
	elseif($tipo == 'visible'){

		echo 'cambió de visibilidad para cliente.<br>';

		// --- Consultar la clasificación y etapa del documento ---
		$preg_doc = "SELECT doc_clasificado, doc_etapa FROM " . $dbpfx . "documentos WHERE doc_id = '" . $doc_id . "'";
		$matr_doc = mysql_query($preg_doc) or die("Falló! " . $preg_doc);
		$info_doc = mysql_fetch_assoc($matr_doc);

		if($info_doc['doc_etapa'] == 1){ // --- Cambiar a no visible ---

			$sql_data_array = [
				'doc_etapa' => 0,
			];
			$parametros = " doc_id = '" . $doc_id . "'";
			ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'actualizar', $parametros);

		} elseif($info_doc['doc_etapa'] == 0 && $info_doc['doc_clasificado'] == 0){

			$sql_data_array = [
				'doc_etapa' => 1,
			];
			$parametros = " doc_id = '" . $doc_id . "'";
			ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'actualizar', $parametros);
		}

	}

	redirigir("documentos.php?accion=listar&orden_id=" . $orden_id);

}
?>

		</div>
	</div>
<?php include('parciales/pie.php'); ?>
