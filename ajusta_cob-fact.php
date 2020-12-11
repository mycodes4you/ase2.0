<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';
include('parciales/funciones.php');
//include('idiomas/' . $idioma . '/presupuestos.php');

/*
if ($_SESSION['usuario'] == '701' || $_SESSION['usuario'] == '1000') {
	// Acceso autorizado
} else {
	redirigir('usuarios.php');
}
*/

// Ajustar la aseguradora o el cliente de los cobros en cobros y cobros_facturas

$preg = "SELECT cobro_id, fact_id FROM " . $dbpfx . "cobros_facturas WHERE cobro_id = '" . $cobro . "' LIMIT 1";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion de cobros enlazados! " . $preg);
while ($cob = mysql_fetch_array($matr)) {
	$preg1 = "SELECT aseguradora_id, cliente_id FROM " . $dbpfx . "facturas_por_cobrar WHERE fact_id = '" . $cob['fact_id'] . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo seleccion de facturas por cobrar! " . $preg1);
	$procesados = 0;
	while ($fact = mysql_fetch_array($matr1)) {
		if($fact['aseguradora_id'] > 0) {
			echo 'Aseg: ' . $fact['aseguradora_id'] . ' ';
                        $act_cobro = "UPDATE " . $dbpfx . "cobros SET aseguradora_id = '" . $fact['aseguradora_id'] . "', cliente_id = NULL WHERE cobro_id = '" . $cob['cobro_id'] . "'";
                        $matr_act_cobro = mysql_query($act_cobro) or die("ERROR: Fallo actualización de tabla cobros! " . $act_cobro);
                        $act_cobro = "UPDATE " . $dbpfx . "cobros_facturas SET aseguradora_id = '" . $fact['aseguradora_id'] . "', cliente_id = NULL WHERE cobro_id = '" . $cob['cobro_id'] . "'";
                        $matr_act_cobro = mysql_query($act_cobro) or die("ERROR: Fallo actualización de tabla cobros_facturas! " . $act_cobro);
		} else {
			echo 'Cliente: ' . $fact['cliente_id'] . ' ';
                        $act_cobro = "UPDATE " . $dbpfx . "cobros SET cliente_id = '" . $fact['cliente_id'] . "', aseguradora_id = NULL WHERE cobro_id = '" . $cob['cobro_id'] . "'";
                        $matr_act_cobro = mysql_query($act_cobro) or die("ERROR: Fallo actualización de tabla cobros! " . $act_cobro);
                        $act_cobro = "UPDATE " . $dbpfx . "cobros_facturas SET cliente_id = '" . $fact['cliente_id'] . "', aseguradora_id = NULL WHERE cobro_id = '" . $cob['cobro_id'] . "'";
                        $matr_act_cobro = mysql_query($act_cobro) or die("ERROR: Fallo actualización de tabla cobros! " . $act_cobro);
		}
//		echo 'Cobro: ' . $cob['cobro_id'] . '<br>';
	}
}

?>
