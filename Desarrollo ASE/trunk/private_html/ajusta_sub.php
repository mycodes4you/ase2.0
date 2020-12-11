<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}

$preg = "SELECT orden_id, orden_estatus, orden_estatus_1, orden_estatus_2, orden_estatus_3, orden_estatus_4, orden_estatus_5, orden_estatus_6, orden_estatus_7, orden_estatus_8, orden_estatus_9, orden_estatus_10 FROM " . $dbpfx . "ordenes WHERE orden_estatus < '90'";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
while ($orden = mysql_fetch_array($matr)) {
	$orden_id = $orden['orden_id'];
	for($i=1; $i <=10 ; $i++) {
		$area = $i;
		$pregunta2 = "SELECT sub_orden_id FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND sub_estatus < '190'";
		$pregunta2 .= " AND sub_area = '" . $area . "'";
//		echo $pregunta2;
   	$matriz2 = mysql_query($pregunta2) or die('actualiza_suborden' . $pregunta2);
		$num_cols = mysql_num_rows($matriz2);
		if ($num_cols == 0) {
	   	$parametros='orden_id = ' . $orden_id;
  			$estat_area = 'orden_estatus_' . $area;
	  		$sql_data_array = array();
			$sql_data_array[$estat_area] = 'null';
		  	ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
		}
	}
		if($orden['orden_estatus'] < 12 || $orden['orden_estatus']== 17 || ($orden['orden_estatus']>= 20 && $orden['orden_estatus']<= 29)) {
	   	$parametros='orden_id = ' . $orden_id;
   		$sql_data_array = array();
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
   		if($calidad==21 && $status_orden==12) { $status_orden=21; }
	  		$sql_data_array['orden_estatus'] = $status_orden;
		  	ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);	
   	}  
}

?>
