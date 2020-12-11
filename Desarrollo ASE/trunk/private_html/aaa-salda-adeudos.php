<?php
include('parciales/funciones.php');
foreach($_GET as $k => $v){$$k = limpiar_cadena($v);}  // echo $k.' -> '.$v.' | ';

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

if($ord != '') {
	$preg1 = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_id = '$ord'";
} elseif($orden_ini != '' && $orden_fin != '') {
	$preg1 = "SELECT orden_id FROM " . $dbpfx . "ordenes WHERE orden_id >= '$orden_ini' AND orden_id <= '$orden_fin' AND orden_estatus >= '90' LIMIT 100";
}

$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de OT! " . $preg1);
$fila1 = mysql_num_rows($matr1);
echo 'Encontradas: ' . $fila1 . '<br>';


if($fila1 > 0) {
	
	while ($ord = mysql_fetch_array($matr1)) {
		$orden = $ord['orden_id'];
		echo 'OT: ' . $orden . '<br>';
		
		$preg_adeudos = "SELECT * FROM " . $dbpfx . "prods_pendientes WHERE orden_id = '$orden'";
		$matr_adeudos = mysql_query($preg_adeudos) or die("ERROR: Fallo seleccion!");
		
		while($adeduos = mysql_fetch_array($matr_adeudos)){ // --- saldar los adeudos ---
			
			$sql_data_array = [
				'prods_pendiente_surtidos' => '0',
				'prods_pendiente_entregados' => $adeduos['prods_pendiente_requeridos'],
				'prods_pendiente_adeudos' => '0'
			];
			$hacer = 'actualizar';
			$parametros = " prods_pendiente_id = '" . $adeduos['prods_pendiente_id'] . "' ";
			ejecutar_db($dbpfx . 'prods_pendientes', $sql_data_array, $hacer, $parametros);
			
			echo 'Se salvo el id ' . $adeduos['prods_pendiente_id'] . '<br>';
		}
		
		
	}
	
}

?>
