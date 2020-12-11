<?php

foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';

include('parciales/funciones.php');


if ($_SESSION['usuario'] == '701' || $_SESSION['usuario'] == '1000') {
	// Acceso autorizado
} else {
	redirigir('usuarios.php');
}


$preg0 = "SELECT pedido_id FROM " . $dbpfx . "pedidos WHERE pedido_id >= '" . $pedido . "' AND pedido_qv IS NULL LIMIT 500";
$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de pedidos! " . $preg0);
$cuenta = 0;
while ($ped = mysql_fetch_array($matr0)) {
	$preg1 = "SELECT o.op_pedido FROM " . $dbpfx . "orden_productos o, " . $dbpfx . "prod_prov pp, " . $dbpfx . "pedidos p WHERE o.op_id = pp.op_id AND pp.cotqv > '0' AND o.op_pedido = '" . $ped['pedido_id'] . "' AND o.op_pedido = p.pedido_id AND pp.prod_prov_id = p.prov_id GROUP BY o.op_pedido";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de QVs! " . $preg1);
	$fila1 = mysql_num_rows($matr1);
	echo 'Pedido: ' . $ped['pedido_id'] . ' Fila: ' . $fila1 . '<br>';
	if($fila1 > 0) {
		$param = "pedido_id = '" . $ped['pedido_id'] . "'";
		$sqlped = ['pedido_qv' => '1'];
		ejecutar_db($dbpfx.'pedidos', $sqlped, 'actualizar', $param);
		$cuenta++;
	}
}

echo 'Se actualizaron: ' . $cuenta;


?>
