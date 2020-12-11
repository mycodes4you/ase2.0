<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/recordatorio.php');

// ------ Sólo se ejecuta el script si está definida la OT de inicio de recordadorios y estás activos los mensajes internos.
 
if($ordiniregllamcli > 0 && $mensjint == 1) {

//--------------- Selección de ordenes activas --------------
	$preg_ord_activas = "SELECT orden_id, orden_asesor_id, orden_estatus, orden_fecha_recepcion FROM " . $dbpfx . "ordenes  WHERE orden_estatus < 90 AND orden_id >= '" . $ordiniregllamcli . "'";
	$mtr_ord_activas = mysql_query($preg_ord_activas) or die("ERROR: Fallo selección de ordenes! ");
//	echo $preg_ord_activas . '<br>';

	while($con_ord_activas = mysql_fetch_array($mtr_ord_activas)) {

		$preg_nom_asesor = "SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '" . $con_ord_activas['orden_asesor_id'] . "'";
		$mtr_nom_asesor = mysql_query($preg_nom_asesor) or die("ERROR: Fallo selección de nombre! ");
		$consulta_nombre = mysql_fetch_array($mtr_nom_asesor);
		
		$bienvenida = 0;
		$determinacion = 0;
		$avances = 0;
		$termino = 0;
		$llamada = "";
		$notificar_sup = 0;
		$etapa_com = 0;
		$reciente = 0;
		$estatus = $con_ord_activas['orden_estatus'];
		
		$preg_llamadas = "SELECT orden_id, etapa_com, fecha_com FROM " . $dbpfx . "comentarios WHERE orden_id = '" . $con_ord_activas['orden_id'] . "' AND interno = '2' ";
		$mtr_llamadas = mysql_query($preg_llamadas) or die("ERROR: Fallo selección de cometarios de seguimiento! " . $preg_llamadas);
		while($consulta_llamada = mysql_fetch_array($mtr_llamadas)) {
// ------ Revisión de cumplimiento por Etapa ------------
			if($consulta_llamada['etapa_com'] == 10) { $bienvenida = 1; }
			if($consulta_llamada['etapa_com'] == 20) { $determinacion = 1; }
			if($consulta_llamada['etapa_com'] == 30) { $avances = 1; }
			if($consulta_llamada['etapa_com'] == 40) { $termino = 1; }
			if(strtotime($consulta_llamada['fecha_com']) > $reciente) { $reciente = strtotime($consulta_llamada['fecha_com']); }
		}
		if($reciente == 0) { $reciente = strtotime($con_ord_activas['orden_fecha_recepcion']); }

		$comentario = 'El asesor ' . $consulta_nombre['nombre'] . ' ' .  $consulta_nombre['apellidos'] .  ' NO registró la llamada de seguimiento correspondiente a la etapa del proceso';

// ------ Determina la etapa en la que se encuentra la OT y aplica acciones correspondientes ** Usar los mismos criterios que en comentarios.php
		if($estatus == 17 || $estatus <= 3 || ($estatus >= 24 && $estatus <= 29) || $estatus == 20) {
			$etapa_com = 10;
			if($bienvenida < 1) { $llamada = 'no'; } //-------- Aplicar recordatorio --------
  		} elseif($estatus == 4 || ($estatus >= 30 && $estatus <= 35)) {
  			$etapa_com = 20;
			if($determinacion < 1) { $llamada = 'no'; } //-------- Aplicar recordatorio --------
			if($bienvenida < 1) {
				//-------- la orden avanzó y no se registro llamada
				bitacora($con_ord_activas['orden_id'], $comentario . ' ' . $llamada_tipo_text[$etapa_com], $dbpfx, $comentario . ' ' . $llamada_tipo_text[$etapa_com], 2, '', '', '', 10);
				$notificar_sup = 1;
			}
		} elseif($estatus >= 7 && $estatus <= 11) {
			$etapa_com = 30;
			if($avances < 1) { $llamada = 'no'; } //-------- Aplicar recordatorio --------
			if($determinacion < 1) {
				bitacora($con_ord_activas['orden_id'], $comentario . ' ' . $llamada_tipo_text[$etapa_com], $dbpfx, $comentario . ' ' . $llamada_tipo_text[$etapa_com], 2, '', '', '', 20);
				$notificar_sup = 1;
			}
			if($bienvenida < 1) {
				bitacora($con_ord_activas['orden_id'], $comentario . ' ' . $llamada_tipo_text[$etapa_com], $dbpfx, $comentario . ' ' . $llamada_tipo_text[$etapa_com], 2, '', '', '', 10);
				$notificar_sup = 1;
			}
		} elseif($estatus == 5 || $estatus == 6 || ($estatus >= 12 && $estatus <= 16) || $estatus == 21) {
			$etapa_com = 40;
			if($termino < 1) { $llamada = 'no'; } //-------- Aplicar recordatorio --------
			if($avances < 1) {
					bitacora($con_ord_activas['orden_id'], $comentario . ' ' . $llamada_tipo_text[$etapa_com], $dbpfx, $comentario . ' ' . $llamada_tipo_text[$etapa_com], 2, '', '', '', 30);
					$notificar_sup = 1;
			}
			if($determinacion < 1) {
				bitacora($con_ord_activas['orden_id'], $comentario . ' ' . $llamada_tipo_text[$etapa_com], $dbpfx, $comentario . ' ' . $llamada_tipo_text[$etapa_com], 2, '', '', '', 20);
				$notificar_sup = 1;
			}
			if($bienvenida < 1) {
				bitacora($con_ord_activas['orden_id'], $comentario . ' ' . $llamada_tipo_text[$etapa_com], $dbpfx, $comentario . ' ' . $llamada_tipo_text[$etapa_com], 2, '', '', '', 10);
				$notificar_sup = 1;
			}
		}
		
// ------ Si no se registraron comentarios a tiempo y hay número de usuario colocado en $usr_sup_asesores en se envía notificación.		
		if($usr_sup_asesores > 0 && $notificar_sup == 1) {
			$preg_cambio_estatus = "SELECT bit_id FROM " . $dbpfx . "comentarios WHERE orden_id ='" . $con_ord_activas['orden_id'] . "' AND interno = '3' AND recordatorio = '1' AND para_usuario = '" . $usr_sup_asesores . "' ORDER BY bit_id DESC LIMIT 1";
			$mtr_cambio_estatus = mysql_query($preg_cambio_estatus) or die("ERROR: Fallo selección del último comentario de llamada al cliente no registrada! " . $preg_cambio_estatus);
			$fila_cambio_estatus = mysql_num_rows($mtr_cambio_estatus);
			if($fila_cambio_estatus == 1) {
				bitacora($con_ord_activas['orden_id'], $comentario . ' ' . $llamada_tipo_text[$etapa_com], $dbpfx, $comentario . ' ' . $llamada_tipo_text[$etapa_com], 3, '', '', $usr_sup_asesores, '', 1);
			}
		}
		
// ------ Si se tienen que liberar recordatorios
		if($notificar_sup == 1) {
			$preg_recordatorios = "SELECT bit_id FROM " . $dbpfx . "comentarios WHERE orden_id = '" . $con_ord_activas['orden_id'] . "' AND recordatorio = '1' AND interno = '3' AND para_usuario = '" . $con_ord_activas['orden_asesor_id'] . "'";
			$mtr_recordatorios = mysql_query($preg_recordatorios) or die("ERROR: Fallo selección de cambio de estatus! " . $preg_recordatorios);
			$sql_data_array = [
				'fecha_visto' => date('Y-m-d H:i:s', time()),
				'recordatorio' => 0,
			];
			while($consulta_recordatorios = mysql_fetch_array($mtr_recordatorios)) {
				$parametros = 'bit_id = ' . $consulta_recordatorios['bit_id'];
				ejecutar_db($dbpfx . 'comentarios', $sql_data_array, 'actualizar', $parametros);
			}
			unset($sql_data_array);
		}
		
// ------ Si la orden no tiene la llamda en en el estatus actual
		if($llamada == 'no') {
			$preg_cambio_estatus = "SELECT bit_id FROM " . $dbpfx . "comentarios WHERE orden_id ='" . $con_ord_activas['orden_id'] . "' AND interno = '3' AND recordatorio = '1' AND para_usuario = '" . $con_ord_activas['orden_asesor_id'] . "' ORDER BY bit_id DESC LIMIT 1";
			$mtr_cambio_estatus = mysql_query($preg_cambio_estatus) or die("ERROR: Fallo selección del ultimo comentario de llamada al cliente! " . $preg_cambio_estatus);
			$fila_cambio_estatus = mysql_num_rows($mtr_cambio_estatus);
			$consulta_cambio_estatus = mysql_fetch_array($mtr_cambio_estatus);
			$hoy = strtotime(date('Y-m-d 20:00:01'));
			$dias = intval(($hoy - $reciente) / 86400);
			$comentario = 'Para ' . $consulta_nombre['nombre'] . ' ' .  $consulta_nombre['apellidos'] . ': ' . $lang['Han pasado'] . $dias . $lang['registrado'] . $llamada_tipo_text[$etapa_com];
// ------ Si hay mensaje actual, lo actualiza!; si no, genera uno!
			if($dias > 0) {
				if($fila_cambio_estatus == 1) {
					$param = " bit_id = '" . $consulta_cambio_estatus['bit_id'] . "' ";
					$sql_data = ['comentario' => $comentario];
					ejecutar_db($dbpfx . 'comentarios', $sql_data, 'actualizar', $param);
					unset($sql_data);
				} else {
					$status = 'Recordatorio de autoshop ' . $comentario;
					bitacora($con_ord_activas['orden_id'], $status, $dbpfx, $comentario, 3, '', '', $con_ord_activas['orden_asesor_id'], '', 1);
				}				
			}
		}
	}
}
?>

