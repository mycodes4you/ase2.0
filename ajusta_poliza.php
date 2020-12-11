<?php
include('parciales/funciones.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

/*
if ($_SESSION['usuario'] == '701' || $_SESSION['usuario'] == '1000') {
        // Acceso autorizado
} else {
        redirigir('usuarios.php');
}

$preg = "SELECT orden_vehiculo_id, orden_id FROM " . $dbpfx . "ordenes WHERE orden_id > '2024'";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
while ($ord = mysql_fetch_array($matr)) {
	$preg0 = "SELECT vehiculo_poliza FROM " . $dbpfx . "vehiculos WHERE vehiculo_id = '" . $ord['orden_vehiculo_id'] . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
	while ($veh = mysql_fetch_array($matr0)) {
		if($veh['vehiculo_poliza'] != '') { 
			$param = "orden_id = '" . $ord['orden_id'] . "' AND sub_aseguradora > '0'";
			$sqldata = array('sub_poliza' => $veh['vehiculo_poliza']);
			ejecutar_db($dbpfx . 'subordenes', $sqldata, 'actualizar', $param);
		}
	}
}
*/

$preg = "SELECT pp_id, op_id FROM " . $dbpfx . "prod_prov WHERE sub_orden_id IS NULL GROUP BY op_id LIMIT 100";
$matr = mysql_query($preg) or die("ERROR: Fallo selección! " . $preg);
$este = 0;
while ($op = mysql_fetch_array($matr)) {
	$preg0 = "SELECT sub_orden_id FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $op['op_id'] . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección! " . $preg0);
	$fila0 = mysql_num_rows($matr0);
	if($fila0 > 0) {
		$prod = mysql_fetch_array($matr0);
		$preg1 = "UPDATE " . $dbpfx . "prod_prov SET sub_orden_id = '" . $prod['sub_orden_id'] . "' WHERE op_id = '" . $op['op_id'] . "'";
		$este++;
//		echo $op['pp_id'] . '<br>';
	} else {
		$preg1 = "DELETE FROM " . $dbpfx . "prod_prov WHERE op_id = '" . $op['op_id'] . "'";
	}
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo actualización! " . $preg1);
}
echo $este;

?>
