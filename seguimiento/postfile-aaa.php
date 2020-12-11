<?php
		require_once ('../parciales/PHP_Compat-1.6.0a3/Compat/Function/file_get_contents.php');
		$data = php_compat_file_get_contents('php://input'); 
		$filename = $_GET['filename'];
		if (file_put_contents($filename,$data)) {
			if (filesize($filename) != 0) {
//				echo "Sincronización Completa.";
//				header("Location: https://" . $_SERVER['HTTP_HOST'] . "/extrae-xml.php?usuario=" . $usuario);
			} else {
				header("HTTP/1.0 400 Bad Request");
				echo "El Archivo está vacío.";
			}
		} else {
			file_put_contents("log.txt","No hubo datos");
		}

?>
