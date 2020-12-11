<?php
foreach($_POST as $k => $v){$$k=$v; } //echo $k.' -> '.$v.' | '; }
foreach($_GET as $k => $v){$$k=$v; } //echo $k.' -> '.$v.' | '; }
include('parciales/funciones.php');

$ruta="seguimiento/";
$directorio=dir($ruta);
//	echo "Directorio " . $ruta . ":<br><br>";
$reg = '/\w*+.+xml$/';
$rei = '/\w*+.+j*g$/';
$archivos = array();
$imagenes = array();
while ($archivo = $directorio->read()) {
	 if(preg_match($reg, $archivo) && !is_dir($archivo)){
//	 	echo $archivo."<br>";
	 	$archivos[] = $archivo;
	 }
	 if(preg_match($rei, $archivo) && !is_dir($archivo)){
//	 	echo $archivo."<br>";
	 	$imagenes[] = $archivo;
	 }
}

$directorio->close();
$segs = array();

	foreach($archivos as $archxml) {
//		echo $archxml."<br>";
		$arc = file_get_contents($ruta.$archxml);
	
		$xml = new DOMDocument();
		$xml->loadXML($arc);

		$seguimiento = $xml->getElementsByTagName('seg');

		foreach($seguimiento as $k) {
			$seg = array();
			if($k->childNodes->length) {
				foreach($k->childNodes as $v) {
					$seg[$v->nodeName] = $v->nodeValue;
				}
			}
			$segs[] = $seg;
			unset($seg);
		}
		$tiempo = substr($archxml, 5,10);
		$mtiempo = substr($archxml, 5,13);
		$nvonom = 'tarea-' . date('Ymd', $tiempo) . '-' . $mtiempo . '.txt';
		rename($ruta.$archxml, $ruta.$nvonom);
	}
	
	foreach($imagenes as $k => $imgs) {
		$info = pathinfo($imgs);
		$verifica = 0; $accion = 'insertar'; $param = '';
//		echo $info['filename'] . ' extension: ' . $info['extension'] . ' = ' . $info['filename'] . '.jpg <br>';
		if ( strtolower($info['extension']) == 'jpeg' || strtolower($info['extension']) == 'jpg' ) {
			$imgjpg = $info['filename'] . '.jpg';
			rename($ruta.$imgs, $ruta.$imgjpg);
			$imgs = $imgjpg;
		}
		$copia_archivo = copy($ruta.$imgs, "seguimiento/procesadas/$imgs");
		$v = explode('_', $imgs);
		if (strlen($v[2]) > 6) {
			$fecha = substr($v[2], 0,10);
			$nom_doc = 'Imagen General';
			$nombre_archivo = rename($ruta.$imgs, "documentos/" . $v[0] . "-" . $v[2]);
			$nom_arc = $v[0] . "-" . $v[2];
		} else {
			$fecha = substr($v[3], 0,10);
			if($v[2] == '-i-0-') { $nom_doc = 'Tablero'; }
			elseif($v[2] == '-i-1-') { $nom_doc = 'VIN'; }
			elseif($v[2] == '-i-2-') { $nom_doc = 'Costado izquierdo'; }
			elseif($v[2] == '-i-3-') { $nom_doc = 'Frontal'; }
			elseif($v[2] == '-i-4-') { $nom_doc = 'Esquina frontal derecha'; $file[$cont]['orden'] = '4'; $file[$cont]['etiqueta'] = 'fefder';}
			elseif($v[2] == '-i-5-') { $nom_doc = 'Costado derecho'; }
			elseif($v[2] == '-i-6-') { $nom_doc = 'Posterior'; }
			elseif($v[2] == '-i-7-') { $nom_doc = 'Esquina posterior izquierda'; }
			elseif($v[2] == '-i-8-') { $nom_doc = 'Motor'; }
			elseif($v[2] == '-i-9-') { $nom_doc = 'Cajuela'; }
			$nombre_archivo = rename($ruta.$imgs, "documentos/" . $v[0] . $v[2] . $v[3]);
			$nom_arc = $v[0] . $v[2] . $v[3];
			$verifica = 1;
		}
		
		
//		echo 'El resultado fue: ' . $nombre_archivo . '<br>';
		if ($nombre_archivo == '1') {
			$sql_data_array = array('orden_id' => $v[0],
				'doc_nombre' => $nom_doc,
				'doc_usuario' => $v[1],
				'doc_archivo' => $nom_arc,
				'doc_fecha_ingreso' => date('Y-m-d H:i:s', $fecha),
				'doc_etapa' => '0'
				);
//			print_r($sql_data_array);
			if($verifica == 1) {
				$preg1 = "SELECT doc_id FROM " . $dbpfx . "documentos WHERE doc_archivo LIKE '" . $v[0] . $v[2] . "%' LIMIT 1";
				$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion de documentos! " . $preg1);
				$fila1 = mysql_num_rows($matr1);
				if($fila1 > 0) {
					$doc = mysql_fetch_array($matr1);
					$accion = 'actualizar';
					$param = " doc_id = '" . $doc['doc_id'] . "'";
				}
			}
			ejecutar_db($dbpfx.'documentos', $sql_data_array, $accion, $param);
			creaMinis($nom_arc);
			bitacora($v[0], 'Se subió ' . $nom_doc . ' desde APP ASE', $dbpfx);
		} else {
			bitacora($v[0], 'No se logró procesar la foto ' . $nom_arc . ' recibida desde la APP ASE ', $dbpfx);
		}
	}

	foreach($segs as $k => $v) {
		$preg0 = "SELECT sub_estatus, sub_area, orden_id, sub_fecha_inicio, sub_fecha_terminado FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $v['tarea'] . "'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de tareas!");
		$sub = mysql_fetch_array($matr0);
		$sql_data = array(); $sql_data_sub = array(); 
		$num_cols = mysql_num_rows($matr0);
		if($num_cols > 0 && ($sub['sub_estatus'] <= '111' || $sub['sub_estatus'] >= '120') && $sub['sub_estatus'] < '130') {
			$seleccion = 0;
			$v['timestamp'] = substr($v['timestamp'], 0,10);
			if($v['actividad'] == '1') {
				if($sub['sub_estatus'] == '104') {
					$estatus = 109;
					$seleccion = 1; 
					$usr_estat = array('estatus' => '1');
				} elseif($sub['sub_estatus'] >= '105' && $sub['sub_estatus'] <= '111') {
					$estatus = 110;
					$seleccion = 5;
					$usr_estat = array('estatus' => '1');
					bitacora($sub['orden_id'], 'Registro de inicio en estatus diferente de 104 para tarea ' . $v['tarea'], $dbpfx);
				} else {
					$estatus = $sub['sub_estatus'];
					$seleccion = 1;
					$usr_estat = array('estatus' => '1');
					bitacora($sub['orden_id'], 'Inicio de reparación antes de autorización para tarea ' . $v['tarea'], $dbpfx);
				}
				if(is_null($sub['sub_fecha_inicio'] ) || strtotime($sub['sub_fecha_inicio']) < '1000000') {
					$sql_data_sub['sub_fecha_inicio'] = date('Y-m-d H:i:s', $v['timestamp']);
				}
			} elseif($v['actividad'] == '2') {
				if(($sub['sub_estatus'] >= '104' && $sub['sub_estatus'] <= '107') || $sub['sub_estatus'] == '111') {
					$estatus = 108;
					$seleccion = 2;
					$usr_estat = array('estatus' => '0');
					bitacora($sub['orden_id'], 'Registro de pausa en estatus diferente de 109 o 110 para tarea ' . $v['tarea'], $dbpfx);
				} elseif($sub['sub_estatus'] >= '108' && $sub['sub_estatus'] <= '110') {
					$estatus = 108;
					$seleccion = 2;
					$usr_estat = array('estatus' => '0');
				} else {
					$estatus = $sub['sub_estatus']; $usr_estat = array('estatus' => '0'); $seleccion = 2;
					bitacora($sub['orden_id'], 'Registro de pausa antes de autorización para tarea ' . $v['tarea'], $dbpfx);
				}
			} elseif($v['actividad'] == '5') {
				if($sub['sub_estatus'] >= '105' && $sub['sub_estatus'] <='108') {
					$estatus = 110; $usr_estat = array('estatus' => '1'); $seleccion = 5;
				} elseif($sub['sub_estatus'] == '104') {
					$estatus = 109;
					$seleccion = 1; 
					$usr_estat = array('estatus' => '1');
				} elseif($sub['sub_estatus'] >= '109' && $sub['sub_estatus'] <= '111') {
					$estatus = 110;
					$seleccion = 5;
					$usr_estat = array('estatus' => '1');
					bitacora($sub['orden_id'], 'Intento de registrar continuar en estatus diferente de 105 a 108 para tarea ' . $v['tarea'], $dbpfx);
				} else {
					$estatus = $sub['sub_estatus']; $usr_estat = array('estatus' => '1'); $seleccion = 5;
					bitacora($sub['orden_id'], 'Registro de pausa antes de autorización para tarea ' . $v['tarea'], $dbpfx);
				}
				if(is_null($sub['sub_fecha_inicio'] ) || strtotime($sub['sub_fecha_inicio']) < '1000000') {
					$sql_data_sub['sub_fecha_inicio'] = date('Y-m-d H:i:s', $v['timestamp']);
				}
			} elseif($v['actividad'] == '7') {
				if($sub['sub_estatus'] == '104') {
					$estatus = 111; $usr_estat = array('estatus' => '0'); $seleccion = 7;
					bitacora($sub['orden_id'], 'Registro de término sin inicio para tarea ' . $v['tarea'], $dbpfx);
				} elseif($sub['sub_estatus'] >= '105' && $sub['sub_estatus'] <= '107') {
					$estatus = 111; $usr_estat = array('estatus' => '0'); $seleccion = 7;
					bitacora($sub['orden_id'], 'Registro de término para tarea ' . $v['tarea'] . ' en reproceso o esperando refacciones.', $dbpfx);
				} elseif($sub['sub_estatus'] >= '108' && $sub['sub_estatus'] <= '110') {
					$estatus = 111; $usr_estat = array('estatus' => '0'); $seleccion = 7;
				} else {
					$estatus = $sub['sub_estatus']; $usr_estat = array('estatus' => '0'); $seleccion = 7;
					bitacora($sub['orden_id'], 'Registro de término antes de autorización para tarea ' . $v['tarea'], $dbpfx);
				}
				if(is_null($sub['sub_fecha_terminado'] ) || strtotime($sub['sub_fecha_terminado']) < '1000000') {
					$sql_data_sub['sub_fecha_terminado'] = date('Y-m-d H:i:s', $v['timestamp']);
				}
			}
			if($seleccion > 0) {
				$sql_data_array = array('usuario' => $v['operador'],
					'sub_orden_id' => $v['tarea'],
					'seg_tipo' => $seleccion,
					'sub_area' => $sub['sub_area'],
					'seg_hora_registro' => date('Y-m-d H:i:s', $v['timestamp']));
				ejecutar_db($dbpfx . 'seguimiento', $sql_data_array);
				if($estatus >= '105' && $estatus <= '110') {
					$sql_data['orden_ubicacion'] = constant('NOMBRE_AREA_' . $sub['sub_area']);
				}
				if(count($sql_data) > 0) {
					$parametros = 'orden_id = ' . $sub['orden_id'];
					ejecutar_db($dbpfx . 'ordenes', $sql_data, 'actualizar', $parametros);
				}
				$sql_data_sub['sub_estatus'] = $estatus;
				$sql_data_sub['sub_operador'] = $v['operador'];
				$parametros = 'sub_orden_id = ' . $v['tarea'];
				ejecutar_db($dbpfx . 'subordenes', $sql_data_sub, 'actualizar', $parametros);
				$parametros = 'usuario = ' . $v['operador'];
				$usr_estat['sub_orden_id'] = $v['tarea'];
				ejecutar_db($dbpfx . 'usuarios', $usr_estat, 'actualizar', $parametros);
				bitacora($sub['orden_id'], 'Registro exitoso de tiempo de reparación desde APP. Nuevo estatus: ' . $estatus, $dbpfx);
				actualiza_orden($sub['orden_id'], $dbpfx);
			}
		} else {
			bitacora('0', 'Intento de registro de tiempos de reparación en tarea ' . $v['tarea'] . ' que está terminada o no es reparable o no existe en esta fecha.', $dbpfx);
		}
	}

?>
