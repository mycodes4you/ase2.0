<?php
include('../particular/config.php');
mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
mysql_select_db($dbnombre) or die('Falló la seleccion la DB');

$usuario = preg_replace('/[^0-9]/', '', $_GET['u']);
$usuario = intval($usuario);
$pregunta = "SELECT clave FROM " . $dbpfx . "usuarios WHERE usuario = '$usuario' AND activo = '1'";
$matriz = mysql_query($pregunta);
$usr = mysql_fetch_array($matriz);
$num_cols = mysql_num_rows($matriz);
if($num_cols == 1) {
	$verificar = md5($_GET['p']);
	if ($verificar == $usr['clave']) {
		require_once ('../parciales/PHP_Compat-1.6.0a3/Compat/Function/file_get_contents.php');
		$data = php_compat_file_get_contents('php://input'); 
		$filename = $_GET['filename'];
		if (file_put_contents($filename,$data)) {
			if (filesize($filename) != 0) {
//				echo "Sincronización Completa.";
				header("Location: https://" . $_SERVER['HTTP_HOST'] . "/extrae-xml.php?usuario=" . $usuario);
			} else {
				header("HTTP/1.0 400 Bad Request");
				echo "El Archivo está vacío.";
			}
		} else {
			header("HTTP/1.0 400 Bad Request");
			echo "Sincronización Fallida.";
		}
	} else {
		header("HTTP/1.0 400 Bad Request");
		echo "Acceso denegado!";     //reports if accesskey is wrong
		$query = "insert into " . $dbpfx . "bitacora (`orden_id`,`usuario`,`bit_estatus`) VALUES ";
		$query .= "('0','" . $usuario . "','Intento de acceso desde APP con usuario " . $usuario . " y clave equivocada')";
		$result = mysql_query($query) or die($query);
	}
} else {
		header("HTTP/1.0 400 Bad Request");
		echo "Acceso no reconocido!";     //reports if accesskey is wrong
		$query = "insert into " . $dbpfx . "bitacora (`orden_id`,`usuario`,`bit_estatus`) VALUES ";
		$query .= "('0','" . $usuario . "','Intento de acceso desde APP con usuario " . $usuario . " no reconocido')";
		$result = mysql_query($query) or die($query);
}
?>
