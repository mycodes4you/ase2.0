<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');


if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

// Requiere el numero de aseguradora en $aseg y el número de asesor en $asesor para aplicar el cambio

$pregusu = "SELECT usuario FROM " . $dbpfx . "usuarios WHERE usuario = '$asesor' AND activo = '1' AND rol06 = '1' AND acceso = '0'";
$matrusu = mysql_query($pregusu) or die("ERROR: Fallo selección de asesor! " . $pregusu);
$filausu = mysql_num_rows($matrusu);
if($aseg != '' && !is_null($aseg) && $filausu == '1') {
	$preg = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_estatus < '90' AND orden_asesor_id != '$asesor'";
	$matr = mysql_query($preg) or die("ERROR: Fallo selección de OTs! " . $preg);
	echo 'Aplicando cambio de asesor en las OTs:<br>';
	$cuantas = 0;
	while ($ord = mysql_fetch_array($matr)) {
		$preg0 = "SELECT orden_id FROM " . $dbpfx . "subordenes WHERE sub_aseguradora = '" . $aseg . "' AND orden_id = '" . $ord['orden_id'] . "' AND sub_estatus < '190' GROUP BY orden_id";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
		while ($sub = mysql_fetch_array($matr0)) {
			$parametros = 'orden_id = ' . $sub['orden_id'];
	  		$sql_data_array = array('orden_asesor_id' => $asesor);
//			print_r($sql_data_array);
			ejecutar_db($dbpfx . 'ordenes', $sql_data_array, 'actualizar', $parametros);
			echo $sub['orden_id'] . ', ';
			$cuantas++;
		}
	}
	echo $cuantas;
}
?>
