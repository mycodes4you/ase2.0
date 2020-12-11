<?php
foreach($_POST as $k => $v){$$k=$v; } //echo $k.' -> '.$v.' | '; }
foreach($_GET as $k => $v){$$k=$v; } //echo $k.' -> '.$v.' | '; }
include('parciales/funciones.php');

$ruta="documentos/rpe/";
$directorio=dir($ruta);
//	echo "Directorio " . $ruta . ":<br><br>";
$reg = '/\w*+.+xml$/';
$archivos = array();
while ($archivo = $directorio->read()) {
	 if(preg_match($reg, $archivo) && !is_dir($archivo)){
//	 	echo $archivo."<br>";
	 	$archivos[] = $archivo;
	 }
}

$directorio->close();
$segs = array();

	foreach($archivos as $archxml) {
		echo $archxml."<br>";
		$arc = file_get_contents($ruta.$archxml);
	
		$xml = new DOMDocument();
		$xml->loadXML($arc);

		$Comprobante = $xml->getElementsByTagName('Comprobante')->item(0);

		if($Comprobante->getAttribute("TipoDeComprobante") == 'P') {
			$Timbre = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);
			$doctos = $xml->getElementsByTagName('DoctoRelacionado');
			$serie = $Comprobante->getAttribute("Serie");
			$folio = $Comprobante->getAttribute("Folio");
			$uuid = $Timbre->getAttribute("UUID");
			$preg2 = "SELECT fact_id FROM " . $dbpfx . "facturas WHERE fact_serie = '" . $serie . "' AND fact_num = '" . $folio . "'";
			$matr2 = mysql_query($preg2) or die("Error: no se conectó! " . $preg2);
			$rpe = mysql_fetch_array($matr2);
			foreach($doctos as $docto) {
				$uuidrel = $docto->getAttribute("IdDocumento");
//				echo $uuid . '<br>';
				$preg1 = "SELECT cf.cobro_id FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "facturas_por_cobrar f WHERE f.fact_uuid LIKE '" . $uuidrel . "' AND f.fact_id = cf.fact_id ";
				$matr1 = mysql_query($preg1) or die("Error: no se conectó! " . $preg1);
				$cobro = mysql_fetch_array($matr1);

				$param = " fact_id = '" . $rpe['fact_id'] . "'";
				$sql_data = array('fact_uuid' => $uuid);
				ejecutar_db($dbpfx . 'facturas', $sql_data, 'actualizar', $param);

				$param = " cobro_id = '" . $cobro['cobro_id'] . "'";
				$sql_data = array('rpe_id' => $rpe['fact_id']);
				ejecutar_db($dbpfx . 'cobros', $sql_data, 'actualizar', $param);
			}
		}

	}

/*
	foreach($imagenes as $k => $imgs) {
		$info = pathinfo($imgs);
//		echo $info['filename'] . ' extension: ' . $info['extension'] . ' = ' . $info['filename'] . '.jpg <br>';
		if ( strtolower($info['extension']) == 'jpeg' || strtolower($info['extension']) == 'jpg' ) {
			$imgjpg = $info['filename'] . '.jpg';
			rename($ruta.$imgs, $ruta.$imgjpg);
			$imgs = $imgjpg;
		}
		$v = explode('_', $imgs);
		$v[1] = substr($v[1], 0,10);
		$nom_doc = 'Subida con APP ASE';
		$copia_archivo = copy($ruta.$imgs, "seguimiento/procesadas/$imgs");
		$nombre_archivo = rename($ruta.$imgs, "documentos/$imgs");
//		echo 'El resultado fue: ' . $nombre_archivo . '<br>';
		if ($nombre_archivo == '1') {
			$sql_data_array = array('orden_id' => $v[0],
				'doc_nombre' => $nom_doc,
				'doc_usuario' => $usuario,
				'doc_archivo' => $imgs,
				'doc_fecha_ingreso' => date('Y-m-d H:i:s', $v[1]),
				'doc_etapa' => '0'
				);
//			print_r($sql_data_array);
			ejecutar_db($dbpfx.'documentos', $sql_data_array, 'insertar');
			creaMinis($imgs);
			bitacora($v[0], 'Se subió la foto desde APP ASE', $dbpfx);
		} else {
			echo 'No se logró mover el archivo ' . $imgs;
		}
	}
		
/*	echo '	<table cellpadding="4" cellspacing="0" border="1">'."\n";	
	echo '		<tr><td colspan="4">Datos de Seguimiento</td></tr>'."\n";
	echo '		<tr><td>Tarea</td><td>Operador</td><td>Accion</td><td>Fecha</td></tr>'."\n";
	$eti = array(
		'1' => 'Inicio',
		'2' => 'Pausa',
		'5' => 'Continua',
		'7' => 'Termina'
	);
*/

/*
	foreach($segs as $k => $v) {
		$preg0 = "SELECT sub_estatus, sub_area, orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $v['tarea'] . "'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de tareas!");
		$sub = mysql_fetch_array($matr0);
		$num_cols = mysql_num_rows($matr0);
		if($num_cols > 0) {
			$seleccion = 0;
			$v['timestamp'] = substr($v['timestamp'], 0,10);
			if($v['actividad'] == '1') {
				if($sub['sub_estatus'] == '104') { 
					$estatus = 109;
					$seleccion = 1; 
					$usr_estat = array('estatus' => '1');
					$sql_data_array['sub_fecha_inicio'] = date('Y-m-d H:i:s', $v['timestamp']);
					$pregunta2 = "SELECT orden_fecha_proceso_inicio FROM " . $dbpfx . "ordenes WHERE orden_id = '" . $sub['orden_id'] . "'";
					$matriz2 = mysql_query($pregunta2) or die("ERROR: Fallo seleccion!");
					$dato = mysql_fetch_array($matriz2);
					if (is_null($dato['orden_fecha_proceso_inicio'])) {
						$sql_data['orden_fecha_proceso_inicio'] = date('Y-m-d H:i:s', $v['timestamp']);
					}

				} else {
					$estatus = 110;
					$seleccion = 5; 
					$usr_estat = array('estatus' => '1');
					bitacora($sub['orden_id'], 'Intento de registrar inicio en estatus diferente de 104 para tarea ' . $v['tarea'], $dbpfx);
				}
			} elseif($v['actividad'] == '2') { 
				if($sub['sub_estatus'] =='109' || $sub['sub_estatus']=='110') { 
					$estatus = 108; $usr_estat = array('estatus' => '0'); $seleccion = 2;
				} else {
					bitacora($sub['orden_id'], 'Intento de registrar pausa en estatus diferente de 109 o 110 para tarea ' . $v['tarea'], $dbpfx);
				}
			} elseif($v['actividad'] == '5') { 
				if($sub['sub_estatus'] >= '105' && $sub['sub_estatus'] <='108') { 
					$estatus = 110; $usr_estat = array('estatus' => '1'); $seleccion = 5;
				} else {
					bitacora($sub['orden_id'], 'Intento de registrar continuar en estatus diferente de 105 a 108 para tarea ' . $v['tarea'], $dbpfx);
				}
			} elseif($v['actividad'] == '7') { 
				if($sub['sub_estatus'] == '108' || $sub['sub_estatus'] == '109' || $sub['sub_estatus'] == '110') {
					$estatus = 111; $usr_estat = array('estatus' => '0'); $seleccion = 7;
				} else {
					bitacora($sub['orden_id'], 'Intento de registrar termino en estatus diferente de 108, 109 o 110 para tarea ' . $v['tarea'], $dbpfx);
				}
			}
			if($seleccion > 0) {
				$sql_data_array = array('usuario' => $v['operador'],
					'sub_orden_id' => $v['tarea'],
					'seg_tipo' => $seleccion,
					'sub_area' => $sub['sub_area'],
					'seg_hora_registro' => date('Y-m-d H:i:s', $v['timestamp']));
				ejecutar_db($dbpfx . 'seguimiento', $sql_data_array);
				unset($sql_data_array);
				$sql_data = array();
				$sql_data_array['sub_estatus'] = $estatus;
				$sql_data_array['sub_operador'] = $v['operador'];
				if($estatus == '111') {
					$sql_data['orden_ubicacion'] = constant('ZONA_DE_ESPERA');
				} else {
					$sql_data['orden_ubicacion'] = constant('NOMBRE_AREA_' . $sub['sub_area']);
				}
				$parametros = 'orden_id = ' . $sub['orden_id'];
				ejecutar_db($dbpfx . 'ordenes', $sql_data, 'actualizar', $parametros);
				$parametros = 'sub_orden_id = ' . $v['tarea'];
				ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
				$parametros = 'usuario = ' . $v['operador'];
				$usr_estat['sub_orden_id'] = $v['tarea'];
				ejecutar_db($dbpfx . 'usuarios', $usr_estat, 'actualizar', $parametros);
				bitacora($sub['orden_id'], 'Registro exitoso de tiempo de reparación desde APP. Nuevo estatus: ' . $estatus, $dbpfx);
				actualiza_orden($sub['orden_id'], $dbpfx);
			}
		} else {
			bitacora('0', 'Intento de registro de tiempos de reparación en tarea inexistente a esta fecha: ' . $v['tarea'], $dbpfx);
		}
		
		
//		echo '		<tr><td>' . $v['tarea'] . '</td><td>' . $v['operador'] . '</td><td>' . $eti[$v['actividad']] . '</td><td>' . date('Y-m-d H:i:s', $v['timestamp']) . '</td></tr>'."\n";
	}
*/
//	echo '	</table>'."\n";

?>
