<?php
/*
  
*/

	include('particular/config.php');
	mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
	mysql_select_db($dbnombre) or die('Falló la seleccion la DB');
	include_once('particular/comun.php');
	include_once('particular/estatus.php'); 

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
    $patterns = array ('/ +/','/[<>]/');
    $replace = array (' ', '_');
    return preg_replace($patterns, $replace, trim($string));
  }

	function ejecutar_db($table, $data, $action = 'insertar', $parameters = '') {
    reset($data);
    if ($action == 'insertar') {
      $query = 'insert into ' . $table . ' (';
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
      $query = 'update ' . $table . ' set ';
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
      $query = substr($query, 0, -2) . ' where ' . $parameters;
//      echo $query;
    }
	return $result = mysql_query($query) or die($query);
  }

	function limpiarEspacio($orden_id, $dbpfx) {
		$query = "UPDATE " . $dbpfx . "espacios SET ";
		$query .= "esp_area = 'Vacio', ";
		$query .= "orden_id = NULL, ";
		$query .= "esp_vehiculo_id = NULL, ";
		$query .= "esp_usuario = NULL, ";
		$query .= "esp_fecha = NULL ";
		$query .= "WHERE orden_id = '$orden_id'";
		return $result = mysql_query($query) or die($query);
	}

	function bitacora($orden_id, $estatus, $dbpfx, $comentario, $interno, $sub_orden_id) {
		$query = "insert into " . $dbpfx . "bitacora (`orden_id`,`usuario`,`bit_estatus`) VALUES ";
		$query .= "('" . $orden_id . "','" . $_SESSION['usuario'] . "','" . $estatus . "')";
		$result = mysql_query($query) or die($query);
		$bit_id = mysql_insert_id();
		if ($comentario!='') {
			$query = "insert into " . $dbpfx . "comentarios (`bit_id`,`orden_id`,`interno`,`comentario`,`usuario`,`sub_orden_id`) VALUES ";
			$query .= "('" . $bit_id . "','" . $orden_id . "','" . $interno . "','" . $comentario . "','" . $_SESSION['usuario'] . "','" . $sub_orden_id . "')";
			$result = mysql_query($query) or die($query);
		}
	}

	function redirigir($url) {
	$host  = $_SERVER['HTTP_HOST'];
//	header('Location:' . $url);
	header('Location: https://' . $host . '/' . $url);
    exit();
	}
	
	function limpiarString($texto) {
      $textoLimpio = preg_replace('/[^A-Za-z0-9_\.]/', '', $texto);
      return $textoLimpio;
	}

	function limpiarNumero($numero) {
      $numLimpio = preg_replace('/[^0-9\.-]/', '', $numero);
      return $numLimpio;
	}

	function lista_documento ($orden_id, $estatus, $dbpfx, $presel) {
		global $codigomon;

		$infomon = validaAcceso('1040065', $dbpfx);  // Valida acceso a mostrar información monetaria.
		
		$pregunta = "SELECT * FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' ";
		if($infomon == '1' || $_SESSION['codigo'] <= $codigomon) { 
			$pregunta .= " AND (doc_clasificado = '0' OR doc_clasificado = '1') "; 
		} else { 
			$pregunta .= " AND doc_clasificado = '0'";
		}
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
		$num_cols = mysql_num_rows($matriz);
		if ($num_cols>0) {
			echo '	<table cellspacing="2" cellpadding="2" border="1">
		<TH colspan="6">Documentos relacionados a la Orden de Trabajo ' . $orden_id . ' con estatus ' . constant('ORDEN_ESTATUS_' . $estatus) . ' </TH>
		<tr><td>Nombre</td><td>Archivo</td><td>Fecha de registro</td><td>Usuario</td><td>Vista previa</td>';
			if ($_SESSION['rol05']=='1' || $_SESSION['rol06']=='1') {
				echo '<td>Eliminar<br>Descargar<br>';
				echo '<form action="documentos.php?accion=listar&orden_id=' . $orden_id . '" method="post" enctype="multipart/form-data" name="partidas"><input type="checkbox" name="presel" value="1" ';
				if($presel == '1') { echo 'checked="checked" '; }
				echo 'onchange="document.partidas.submit()"; /></form>';
				echo '</td>';
			} else {
				echo '<td>&nbsp;</td>';
			}
			echo '</tr>'."\n";
			$c = 0;
			if($_SESSION['rol06']=='1' || $_SESSION['rol05']=='1') {
				echo '	<form action="documentos.php?accion=depurar" method="post" enctype="multipart/form-data">';
			}
			while ($documento = mysql_fetch_array($matriz)) {
				echo '		<tr>
			<td>' . $documento['doc_nombre'] . '</td>
			<td>' . $documento['doc_archivo'] . '</td>
			<td>' . $documento['doc_fecha_ingreso'] . '</td>
			<td>' . $documento['doc_usuario'] . '</td>
<!--			<td>' . constant('ORDEN_ESTATUS_' . $documento['doc_etapa']) . '</td> -->';
				if ($documento['doc_archivo'] != '') {
					$tipo_archivo = pathinfo($documento['doc_archivo']);
					echo '			<td><a href="' . DIR_DOCS . $documento['doc_archivo'] . '" target="_blank"><img src="';
					if(($tipo_archivo['extension']=='JPG' || $tipo_archivo['extension']=='PNG' || $tipo_archivo['extension']=='jpg' || $tipo_archivo['extension']=='png' || $tipo_archivo['extension']=='gif') && file_exists(DIR_DOCS . 'minis/' .$documento['doc_archivo'])) {
						echo DIR_DOCS . 'minis/' . $documento['doc_archivo'] . '" '; 
					} else { 
						echo DIR_DOCS . 'documento.png" '; 
					} 
					echo 'width="48" border="0"></a></td>';
				} else {
					echo '			<td><img src="' . DIR_DOCS . 'documento.png" alt="Sin imagen" title="Sin imagen"></td>';
				}
				if ($_SESSION['rol06']=='1' || $_SESSION['rol05']=='1') {
					echo '<td><input type="checkbox" name="eliminar[' . $c . ']" ';
					if($presel == '1') { echo 'checked="checked"'; }
					echo ' /><input type="hidden" name="doc_id[' . $c . ']" value="' . $documento['doc_id'] . '"/><input type="hidden" name="doc_arch[' . $c . ']" value="' . $documento['doc_archivo'] . '"/><input type="hidden" name="orden_id" value="' . $orden_id . '"/><input type="hidden" name="estatus" value="' . $estatus . '"/></td>';
				} else {
					echo '<td>&nbsp;</td>';
				}
				echo '		</tr>'."\n";
				$c++;
			}
			if ($_SESSION['rol06']=='1' || $_SESSION['rol05']=='1') {
				echo '<tr><td colspan="6" style="text-align:left;"><input type="submit" name="enviar" value="Eliminar" /><label>Si marcó documentos para eliminar, al presionar "Eliminar" serán eliminados. | <input type="submit" name="enviar" value="Descargar" /></label></td></tr>';
			}
			echo '	</table></form><br>'."\n";
		} else {
			return $mensaje ='No se encontraron documentos para la orden de trabajo ' . $orden_id;
		}
	}

	function actualiza_suborden ($orden_id, $area, $dbpfx) {
		$preg1 = "SELECT * FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND sub_estatus < '190'";
		$matr1 = mysql_query($preg1) or die('actualiza_suborden' . $preg1);
		$num_dedu = mysql_num_rows($matr1);
		$deducible = array();
		$refac = 0;
  		$estatus_suborden = 189; 
   	$parametros='orden_id = ' . $orden_id;
  		$estat_area = 'orden_estatus_' . $area;
	  	$sql_data_array = array();
		while($sub = mysql_fetch_array($matr1)) {
			if($deducible[$sub['sub_reporte']] < $sub['sub_deducible']) {
				$deducible[$sub['sub_reporte']] = $sub['sub_deducible'];
			}
   		if($sub['sub_refacciones_recibidas'] > $refac) {
   			$refac = $sub['sub_refacciones_recibidas'];
   		}
			if ($sub['sub_area'] == $area) {
  				if ($sub['sub_estatus'] < $estatus_suborden) { $estatus_suborden = $sub['sub_estatus']; }
			}
		}
		$ded = 0;
		foreach($deducible as $i => $v) {
			$ded = $ded + $v;
		}
		if($estatus_suborden == 189) {
			$estatus_suborden = 'null';
		} else {
			$estatus_suborden = $estatus_suborden - 100;
		}
		$sql_data_array[$estat_area] = $estatus_suborden;
   	$sql_data_array['orden_ref_pendientes'] = $refac;
   	$sql_data_array['orden_deducible'] = $ded;
	  	ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
	}
	
	function actualiza_orden ($orden_id, $dbpfx) {
		$pregunta3 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $orden_id . "'";
//		echo $pregunta3;
   	$matriz3 = mysql_query($pregunta3) or die($pregunta3);
   	$parametros='orden_id = ' . $orden_id;
   	$orden = mysql_fetch_array($matriz3); 
   	$sql_data_array = array();
		if($orden['orden_estatus'] <= 17 || ($orden['orden_estatus']>= 20 && $orden['orden_estatus']<= 89)) {
   		$status_orden = 99; $calidad = 0;
   		if($orden['orden_estatus_1']==21 || $orden['orden_estatus_2']==21 || $orden['orden_estatus_3']==21 || $orden['orden_estatus_4']==21 || $orden['orden_estatus_5']==21 || $orden['orden_estatus_6']==21 || $orden['orden_estatus_7']==21 || $orden['orden_estatus_8']==21 || $orden['orden_estatus_9']==21 || $orden['orden_estatus_10']==21) { $calidad = 21; }
//   		echo 'Calidad -> ' . $calidad . '<br>';
	   	if (($orden['orden_estatus_1'] <= $status_orden) && ($orden['orden_estatus_1'] > 0)) { $status_orden = $orden['orden_estatus_1']; }
   		if (($orden['orden_estatus_2'] <= $status_orden) && ($orden['orden_estatus_2'] > 0)) { $status_orden = $orden['orden_estatus_2']; }
   		if (($orden['orden_estatus_3'] <= $status_orden) && ($orden['orden_estatus_3'] > 0)) { $status_orden = $orden['orden_estatus_3']; }
	   	if (($orden['orden_estatus_4'] <= $status_orden) && ($orden['orden_estatus_4'] > 0)) { $status_orden = $orden['orden_estatus_4']; }
	   	if (($orden['orden_estatus_5'] <= $status_orden) && ($orden['orden_estatus_5'] > 0)) { $status_orden = $orden['orden_estatus_5']; }
	   	if (($orden['orden_estatus_6'] <= $status_orden) && ($orden['orden_estatus_6'] > 0)) { $status_orden = $orden['orden_estatus_6']; }
	   	if (($orden['orden_estatus_7'] <= $status_orden) && ($orden['orden_estatus_7'] > 0)) { $status_orden = $orden['orden_estatus_7']; }
	   	if (($orden['orden_estatus_8'] <= $status_orden) && ($orden['orden_estatus_8'] > 0)) { $status_orden = $orden['orden_estatus_8']; }
	   	if (($orden['orden_estatus_9'] <= $status_orden) && ($orden['orden_estatus_9'] > 0)) { $status_orden = $orden['orden_estatus_9']; }
   		if (($orden['orden_estatus_10'] <= $status_orden) && ($orden['orden_estatus_10'] > 0)) { $status_orden = $orden['orden_estatus_10']; }
//   		echo 'Nuevo Estatus -> ' . $status_orden . '<br>';
   		if($calidad==21 && $status_orden==12) { $status_orden=21; }
	  		$sql_data_array['orden_estatus'] = $status_orden;
//		  	$sql_data_array['orden_presupuesto'] = $pres_orden;
	  		if ($status_orden != $orden['orden_estatus']) {
	  			$sql_data_array['orden_fecha_ultimo_movimiento'] = date('Y-m-d H:i:s');
		  		$sql_data_array['orden_alerta'] = 0;
			  	bitacora($orden_id, constant('ORDEN_ESTATUS_' . $status_orden), $dbpfx);
		  	}
   	}  

   	if ($orden['orden_ref_pendientes']=='0' && $status_orden > 2 && $status_orden < 12 && is_null($orden['orden_fecha_ref_recibidas'])) {
	   		$sql_data_array['orden_fecha_ref_recibidas'] = date('Y-m-d H:i:s'); 
   	}
	  	
	  	if (($status_orden=='12') && (is_null($orden['orden_fecha_proceso_fin']))) {
	  		$sql_data_array['orden_fecha_proceso_fin'] = date('Y-m-d H:i:s');
	  	}
	  	
	  	if(count($sql_data_array) > 0) {
		  	ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);	
	  	}
	}
	  	
	function agrega_documento ($orden_id, $imagen, $nom_doc, $dbpfx) {
		
		$nom_doc = ereg_replace("[^A-Za-zñÑáéíóúÁÉÍÓÚ0-9 ]", "", $nom_doc);
		$nombre_archivo = basename($imagen['name']);
		$nombre_archivo = limpiarstring($nombre_archivo);
		$nombre_archivo = $orden_id . '-' . time() . '-' . $nombre_archivo;
		if (move_uploaded_file($imagen['tmp_name'], DIR_DOCS . $nombre_archivo)) {
			$sql_data_array = array('orden_id' => $orden_id,
				'doc_nombre' => $nom_doc,
				'doc_usuario' => $_SESSION['usuario'],
				'doc_archivo' => $nombre_archivo);
			ejecutar_db($dbpfx . 'documentos', $sql_data_array, 'insertar');
			creaMinis($nombre_archivo);
			$resultado = array('error' => 'no', 'mensaje' => '', 'nombre' => $nombre_archivo);
		} else {
			$resultado = array('error' => 'si', 'mensaje' => 'No se logró subir el archivo.<br>');
		}
		return $resultado;
	}

	function ajusta_suborden ($orden_id, $area, $dbpfx) {
		$pregunta2 = "SELECT sub_estatus FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND sub_area = '" . $area . "' AND sub_estatus < '190'";
//		echo $pregunta2;
   	$matriz2 = mysql_query($pregunta2) or die('ajusta_suborden' . $pregunta2);
		$filas = mysql_num_rows($matriz2);
  		$estatus_suborden = 103; 
   	$parametros='orden_id = ' . $orden_id;
  		$estat_area = 'orden_estatus_' . $area;
	  	$sql_data_array = array();
		if ($filas>0) {
   		while($sub = mysql_fetch_array($matriz2)) {
   			if ($sub['sub_estatus'] > $estatus_suborden) { $estatus_suborden = $sub['sub_estatus']; }
	   	}
	   	$estatus_suborden = $estatus_suborden - 100;
			$sql_data_array[$estat_area] = $estatus_suborden;
	  	}
	  	$pregunta3 = "SELECT sub_refacciones_recibidas FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND sub_estatus < '190'";
	  	$matriz3 = mysql_query($pregunta3) or die('actualiza_suborden' . $pregunta3);
		$refac = 0;
	  	while($sub = mysql_fetch_array($matriz3)) {
   		if ($sub['sub_refacciones_recibidas'] > $refac) {
   			$refac = $sub['sub_refacciones_recibidas'];
   		}
			$sql_data_array['orden_ref_pendientes'] = $refac;
	   }
  		ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
	}
	
	function ajusta_orden ($orden_id, $dbpfx) {
		$pregunta3 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $orden_id . "'";
//		echo $pregunta3;
   	$matriz3 = mysql_query($pregunta3) or die($pregunta3);
   	$status_orden = 3; 
   	$parametros='orden_id = ' . $orden_id;
   	$orden = mysql_fetch_array($matriz3); 
   	if ($orden['orden_estatus_1'] > $status_orden && $orden['orden_estatus_1'] < 11) { $status_orden = $orden['orden_estatus_1']; }
   	if ($orden['orden_estatus_2'] > $status_orden && $orden['orden_estatus_2'] < 11) { $status_orden = $orden['orden_estatus_2']; }
   	if ($orden['orden_estatus_3'] > $status_orden && $orden['orden_estatus_3'] < 11) { $status_orden = $orden['orden_estatus_3']; }
   	if ($orden['orden_estatus_4'] > $status_orden && $orden['orden_estatus_4'] < 11) { $status_orden = $orden['orden_estatus_4']; }
   	if ($orden['orden_estatus_5'] > $status_orden && $orden['orden_estatus_5'] < 11) { $status_orden = $orden['orden_estatus_5']; }
   	if ($orden['orden_estatus_6'] > $status_orden && $orden['orden_estatus_6'] < 11) { $status_orden = $orden['orden_estatus_6']; }
   	if ($orden['orden_estatus_7'] > $status_orden && $orden['orden_estatus_7'] < 11) { $status_orden = $orden['orden_estatus_7']; }
   	if ($orden['orden_estatus_8'] > $status_orden && $orden['orden_estatus_8'] < 11) { $status_orden = $orden['orden_estatus_8']; }
   	if ($orden['orden_estatus_9'] > $status_orden && $orden['orden_estatus_9'] < 11) { $status_orden = $orden['orden_estatus_9']; }
   	if ($orden['orden_estatus_10'] > $status_orden && $orden['orden_estatus_10'] < 11) { $status_orden = $orden['orden_estatus_10']; }
   	if($status_orden > '3') {
	  		$sql_data_array = array('orden_estatus' => $status_orden,
				'orden_fecha_ultimo_movimiento' => date('Y-m-d H:i:s'),
				'orden_alerta' => 0);
			bitacora($orden_id, constant('ORDEN_ESTATUS_' . $status_orden), $dbpfx);
  			ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
  		}
	}

	function dia_habil ($dias) {
		$hoy = date('w', time());
		$year = date('Y', time());
		$ene1 = mktime(0,0,0,1,1,$year);
		$feb1 = mktime(0,0,0,2,1,$year);
		$feb7 = mktime(0,0,0,2,7,$year);
		$mar15 = mktime(0,0,0,3,15,$year);
		$mar21 = mktime(0,0,0,3,21,$year);
		$may1 = mktime(0,0,0,5,1,$year);
		$sep16 = mktime(0,0,0,9,16,$year);
		$nov15 = mktime(0,0,0,11,15,$year);
		$nov21 = mktime(0,0,0,11,21,$year);
		$dic25 = mktime(0,0,0,12,25,$year);
		
		if(($hoy + $dias) > 6) {
			$dias++;
		}
		$t_habil = time() + ($dias * 86400);
		$domingo = date('w', $t_habil); 
		if ($domingo==0) { $t_habil = $t_habil + 86400; }
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
		if($diferencia > 518400) { $sem = 3 ;} // +7
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

	function horasEmpleadas($sub_orden_id, $dbpfx) {
		$preg = "SELECT * FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '".$sub_orden_id."' ORDER BY seg_hora_registro";
		$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
		$tiempo = 0; $tiempo2 = time();
		while($seg = mysql_fetch_array($matriz1)) {
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
		$horas = intval($tiempo/3600);
		$minutos = intval(($tiempo - ($horas*3600))/60);
		$lapso = $horas . ':' . $minutos;
		return $lapso;
	}

	function creaMinis($archivo) {
		$info = pathinfo(DIR_DOCS . $archivo);
		$guarda_mini = DIR_DOCS . 'minis/';
		if ( strtolower($info['extension']) == 'jpg' ) {
			$img = imagecreatefromjpeg( DIR_DOCS . $archivo );
      	$width = imagesx( $img );
  	   	$height = imagesy( $img );
     		$new_width = 48;
     		$new_height = floor( $height * ( 48 / $width ) );
      	$tmp_img = imagecreatetruecolor( $new_width, $new_height );
  	   	imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
     		imagejpeg( $tmp_img, $guarda_mini . $archivo );
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

	function datosVehiculo($orden_id, $dbpfx) {
		$pregunta = "SELECT v.vehiculo_marca, v.vehiculo_tipo, v.vehiculo_color, v.vehiculo_modelo, v.vehiculo_placas, v.vehiculo_serie FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "ordenes o WHERE o.orden_vehiculo_id = v.vehiculo_id AND o.orden_id = '" . $orden_id . "'";
		$matriz = mysql_query($pregunta) or die("ERROR: Fallo selección de vehículo!" . $pregunta);
		$veh = mysql_fetch_array($matriz);
		$preg0 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '" . $orden_id . "' AND doc_archivo LIKE '%-i-3-%' ORDER BY doc_id DESC LIMIT 1";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de IMAGEN!" . $preg0);
		$img = mysql_fetch_array($matr0);
		if($img['doc_archivo'] != '') {$foto = '<a href="' . DIR_DOCS . $img['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . $img['doc_archivo'] . '" width="180" border="0"></a>';}
		else { $foto = 'Sin imagen de ingreso'; } 
		$vehiculo = array('marca' => $veh['vehiculo_marca'],
			'tipo' => $veh['vehiculo_tipo'],
			'color' => $veh['vehiculo_color'],
			'modelo' => $veh['vehiculo_modelo'],
			'placas' => $veh['vehiculo_placas'],
			'serie' => $veh['vehiculo_serie'],
			'frontal' => $foto,
			'completo' => $veh['vehiculo_marca'] . ' ' . $veh['vehiculo_tipo'] . ' ' . $veh['vehiculo_color'] . ' ' . $veh['vehiculo_modelo'] . ' Placas:' . $veh['vehiculo_placas'],
			'refacciones' => $veh['vehiculo_marca'] . ' ' . $veh['vehiculo_tipo'] . ' ' . $veh['vehiculo_color'] . ' VIN: ' . $veh['vehiculo_serie'] . ' Placas:' . $veh['vehiculo_placas'] . ' Modelo: ' . $veh['vehiculo_modelo']);
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
  				ejecutar_db($dbpfx . 'cont_libro_diario', $sqlarray, 'insertar');
  				$lib_id = mysql_insert_id();
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
		$query = "insert into " . $dbpfx . "cont_bitacora (`ciclo`,`poliza`,`usuario`,`evento`) VALUES ";
		$query .= "('" . $ciclo . "','" . $poliza . "','" . $_SESSION['usuario'] . "','" . $evento . "')";
		$result = mysql_query($query) or die($query);
		$bit_id = mysql_insert_id();
		if ($comentario!='') {
			$query = "insert into " . $dbpfx . "comentarios (`bit_id`,`orden_id`,`interno`,`comentario`,`usuario`,`sub_orden_id`) VALUES ";
			$query .= "('" . $bit_id . "','" . $orden_id . "','" . $interno . "','" . $comentario . "','" . $_SESSION['usuario'] . "','" . $sub_orden_id . "')";
			$result = mysql_query($query) or die($query);
		}
	}


/*  */
