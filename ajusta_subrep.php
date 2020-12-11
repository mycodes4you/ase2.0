<?php
foreach($_GET as $k => $v){$$k=$v;}  // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if ($_SESSION['usuario'] != '701') {
	redirigir('usuarios.php');
}

$preg = "SELECT sub_orden_id, sub_estatus, orden_id FROM " . $dbpfx . "subordenes WHERE orden_id >= '$orden' AND (sub_estatus = '104' OR sub_estatus = '108' OR sub_estatus = '109' OR sub_estatus = '110') LIMIT 100";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
while ($sub = mysql_fetch_array($matr)) {
	$actualiza = 0;
	$procesa = 'Orden: ' . $sub['orden_id'] . ' -> ';
	$preg0 = "SELECT seg_tipo FROM " . $dbpfx . "seguimiento WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "' AND seg_opr_apoyo IS NULL ORDER BY seg_id DESC LIMIT 1";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
	while ($seg = mysql_fetch_array($matr0)) {
		$enco = 0;
		$param = " sub_orden_id = '" . $sub['sub_orden_id'] . "'";
		if($seg['seg_tipo'] == '1' && $sub['sub_estatus'] != '109') { $sqldata = ['sub_estatus' => '109']; $enco = 1; }
		elseif($seg['seg_tipo'] == '2' && $sub['sub_estatus'] != '108') { $sqldata = ['sub_estatus' => '108']; $enco = 1; }
		elseif($seg['seg_tipo'] == '5' && $sub['sub_estatus'] != '110') { $sqldata = ['sub_estatus' => '110']; $enco = 1; }
		elseif($seg['seg_tipo'] == '7' && $sub['sub_estatus'] != '111') { $sqldata = ['sub_estatus' => '111']; $enco = 1; }
		if($enco == 1) {
			echo $procesa . ' Tarea: ' . $sub['sub_orden_id'] . ' cambio de ' . $sub['sub_estatus'] . ' a ' . $seg['seg_tipo'] .  '<br>';
		} else {
			echo $procesa . ' Tarea: ' . $sub['sub_orden_id'] . ' se encontró OK en estatus ' . $sub['sub_estatus'] . '<br>';
		}
		
		if($hacer == 'si' && $enco == 1) {
//			ejecutar_db($dbpfx . 'subordenes', $sqldata, 'actualizar', $param);
			$actualiza = 1;
		}
		unset($sqldata);
		$enco = 0;
	}
	if($hacer == 'si' && $actualiza == 1) {
		$actualiza = 0;
//		actualiza_orden($sub['orden_id'], $dbpfx);
	}
}
?>
