<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');


if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}


// Comentario de descripción de función.

/*$preg = "SELECT cf_id, cobro_id FROM " . $dbpfx . "cobros_facturas";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion de cobros facturas!");
while ($cob = mysql_fetch_array($matr)) {
	$preg0 = "SELECT cobro_monto FROM " . $dbpfx . "cobros WHERE cobro_id = '" . $cob['cobro_id'] . "'";
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion!");
	$mon = mysql_fetch_array($matr0);
	$preg1 = "UPDATE " . $dbpfx . "cobros_facturas SET monto = '" . $mon['cobro_monto'] . "' WHERE cf_id = '" . $cob['cf_id'] . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo actualización! ".$preg1);
}

*/

$preg = "SELECT f.fact_id, f.fact_num, c.cobro_fecha FROM " . $dbpfx . "facturas_por_cobrar f, " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "cobros c WHERE f.fact_id = cf.fact_id AND cf.cobro_id = c.cobro_id AND c.cobro_tipo = 4 AND cf.monto = f.fact_monto AND f.fact_cobrada = 0 LIMIT 1";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion de cobros facturas!");
while ($cob = mysql_fetch_array($matr)) {
	$preg1 = "UPDATE " . $dbpfx . "facturas_por_cobrar SET fact_cobrada = '2', fact_fecha_cobrada = '" . $cob['cobro_fecha'] . "' WHERE fact_id = '" . $cob['fact_id'] . "'";
	$matr1 = mysql_query($preg1) or die("ERROR: Fallo actualización! ".$preg1);
	echo 'Factura ' . $cob['fact_num'] . ' actualizada. <br>';
}





?>
