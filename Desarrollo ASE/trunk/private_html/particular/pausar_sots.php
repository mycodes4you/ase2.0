<?php
include('config.php');
include('../parciales/funciones.php');
mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
mysql_select_db($dbnombre) or die('Falló la seleccion la DB');
$pregunta = "SELECT usuario, estatus, sub_orden_id FROM " . $dbpfx . "usuarios WHERE estatus = '1'";
$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion!");
while($pausa = mysql_fetch_array($matriz)) {
	$sql_data = [
		"usuario" => $pausa['usuario'],
		'sub_orden_id' => $pausa['sub_orden_id'],
		'seg_tipo' => '2'
	];
	ejecutar_db($dbpfx . 'seguimiento', $sql_data);
	unset($sql_data);
/*	$query = "INSERT INTO " . $dbpfx . "seguimiento (usuario, sub_orden_id, seg_tipo) VALUES ";
	$query .= "('" . $pausa['usuario'] . "','" . $pausa['sub_orden_id'] . "','2')";
	$resultado = mysql_query($query) or die($query); */
	
	$sql_data = ['sub_estatus' => '108'];
	$param = " sub_orden_id = '" . $pausa['sub_orden_id'] . "'";
	ejecutar_db($dbpfx . 'subordenes', $sql_data, 'actualizar', $param);
	unset($sql_data);
/*	$query = "UPDATE " . $dbpfx . "subordenes SET sub_estatus = '108' WHERE sub_orden_id = '" . $pausa['sub_orden_id'] . "'";
	$resultado = mysql_query($query) or die($query); */

	$sql_data = ['estatus' => '0'];
	$param = " usuario = '" . $pausa['usuario'] . "'";
	ejecutar_db($dbpfx . 'usuarios', $sql_data, 'actualizar', $param);
	unset($sql_data);
/*	$query = "UPDATE " . $dbpfx . "usuarios SET estatus = '0' WHERE usuario = '" . $pausa['usuario'] . "'";
	$resultado = mysql_query($query) or die($query); */
	
	$preg0 = "SELECT orden_id FROM " . $dbpfx . "subordenes WHERE sub_orden_id = '" . $pausa['sub_orden_id'] . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de suborden!");
	$ord = mysql_fetch_array($matr0);
	
	$comentario = 'Tarea ' . $pausa['sub_orden_id'] . ' puesta en pausa por cron.';
	$sql_data = [
		'orden_id' => $ord['orden_id'],
		'usuario' => '1000',
		'bit_estatus' => $comentario
	];
	ejecutar_db($dbpfx . 'bitacora', $sql_data);
	unset($sql_data);
/*	$query2 = "insert into " . $dbpfx . "bitacora (`orden_id`,`usuario`,`bit_estatus`) VALUES ";
	$query2 .= "('" . $ord['orden_id'] . "','1000','" . $comentario . "')";
	$result2 = mysql_query($query2) or die($query2); */

	$orden_id = $ord['orden_id'];
// Ajustar el estatus de la OT

//	$preg1 = "SELECT * FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
//	echo $pregunta3;
//	$matr1 = mysql_query($preg1) or die($preg1);
   
   actualiza_orden($orden_id, $dbpfx);
   
}

?>