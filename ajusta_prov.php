<?php 

include('parciales/funciones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if ($_SESSION['usuario'] == '701' || $_SESSION['usuario'] == '1000') {
	// Acceso permitido
} else {
	redirigir('usuarios.php');
}

/*
$preg1 = "SELECT pp.pp_id, pp.op_id, pp.sub_orden_id FROM " . $dbpfx . "prod_prov pp, " . $dbpfx . "proveedores p WHERE p.prov_id = pp.prod_prov_id AND p.prov_qv_id > 0 AND pp.fecha_cotizado > '2019-12-26 00:00:00' AND pp.cotqv IS NULL AND pp.prod_costo = '0' LIMIT 100";
$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Prod_Prov! " . $preg1);
$fila1 = mysql_num_rows($matr1);
while($pp = mysql_fetch_array($matr1)) {
	$param = "pp_id = '" . $pp['pp_id'] . "'";
	$sqlop['cotqv'] = '1';
	ejecutar_db($dbpfx . 'prod_prov', $sqlop, 'actualizar', $param);
	echo 'Tarea: ' . $pp['sub_orden_id'] . ' OP: ' . $pp['op_id'] . '<br>';
}
*/

$preg0 = "SELECT prov_id, cuenta_contable FROM " . $dbpfx . "proveedores WHERE cuenta_contable IS NOT NULL ORDER BY cuenta_contable DESC LIMIT 1";
$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de cuenta de Proveedores! " . $preg0);
$fila0 = mysql_num_rows($matr0);
if($fila0 > 0) {
	$ccp = mysql_fetch_array($matr0);
	$consec = explode('-', $ccp['cuenta_contable']);
	echo 'folio: ' . $consec[1] . '<br>';
	$folio = intval($consec[1]);
} else {
	$folio = 0;
}
$folio++;

echo 'Último folio: ' . $folio . '<br>';


$preg1 = "SELECT prov_id, prov_razon_social FROM " . $dbpfx . "proveedores WHERE cuenta_contable IS NULL OR cuenta_contable = '' LIMIT 1";
$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de Proveedores! " . $preg1);
while($prov = mysql_fetch_array($matr1)) {
	// --- Creamos número de cuenta contable --
	$cc = str_pad($folio, 3, "0", STR_PAD_LEFT);
	$cuenta = '2110-' . $cc . '-000-000-00';
	// --- Se actualiza proveedor --
	$sql_data['cuenta_contable'] = $cuenta;
	$param = "prov_id = '" . $prov['prov_id'] . "'";
	//print_r($sql_data);
	ejecutar_db($dbpfx . 'proveedores', $sql_data, 'actualizar', $param);
	$folio++;

	// --- Se agrega la nueva cuenta al catálogo --
	$sql_cat = [
		'cat_codagrup' => '201.01',
		'cuenta_contable' => $cuenta,
		'nombre_contable' => strtoupper($prov['prov_razon_social']),
		'cat_naturaleza' => 'Acreedora',
	];
	//print_r($sql_cat);
	ejecutar_db($dbpfx . 'cont_cat', $sql_cat, 'insertar');
}


echo 'Se procesaron ' . $fila1 . ' OPs<br>';


?>
