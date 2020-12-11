<?php
include('parciales/funciones.php');
include('../idiomas/' . $idioma . '/presupuestos.php');
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

echo 'Hola<br>';
/*  ----------------  Pedidos en estatus 50   ------------------- */

$preg_pedidos_50 = "SELECT pedido_id FROM " . $dbpfx . "pedidos WHERE pedido_estatus = '50'";
$mtr_pedidos_50 = mysql_query($preg_pedidos_50) or die("ERROR: Fallo ! " . $preg_pedidos_50);

while($pedidos = mysql_fetch_array($mtr_pedidos_50)){

	// --- Consultar pedidos que tengan más de 1 elemento ---
	$preg_elementos = "SELECT op_id FROM " . $dbpfx . "orden_productos WHERE op_pedido = '" . $pedidos['pedido_id'] . "'";
	$mtr_elementos = mysql_query($preg_elementos) or die("ERROR: Fallo ! " . $preg_elementos);
	$elementos = mysql_num_rows($mtr_elementos);
	
	if($elementos > 1){
		echo $pedidos['pedido_id'] . '<br>';
	}

}

?>