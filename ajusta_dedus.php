<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

/*
$preg = "SELECT sub_reporte, orden_id FROM " . $dbpfx . "subordenes WHERE orden_id >= '$orden_id' AND sub_siniestro = '1'  AND sub_estatus < '130' GROUP BY sub_reporte";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion!");
while ($rep = mysql_fetch_array($matr)) {
		$preg1 = "SELECT sub_deducible FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $rep['orden_id'] . "' AND sub_reporte = '" . $rep['sub_reporte'] . "'";
		$matr1 = mysql_query($preg1) or die('actualiza_suborden' . $preg1);
		$dedu = 0;
   	$parametros= "orden_id = '" . $rep['orden_id'] . "' AND sub_reporte = '" . $rep['sub_reporte'] . "'";
		while($sub = mysql_fetch_array($matr1)) {
			if($sub['sub_deducible'] > $dedu) {
				$dedu = $sub['sub_deducible'];
			}
		}
	  	$sql_data_array = array('sub_deducible' => $dedu);
	  	ejecutar_db($dbpfx . 'subordenes', $sql_data_array, 'actualizar', $parametros);
	  	unset($sql_data_array);
}
*/

$preg = "SELECT * FROM " . $dbpfx . "pedidos";
$matr = mysql_query($preg) or die('Error: no se conectó a pedidos! ' . $preg);
$num_fields = mysql_num_fields($matr);
$headers = array();
for ($i = 0; $i < $num_fields; $i++) {
    $headers[] = mysql_field_name($matr , $i);
}
$fp = fopen('php://output', 'w');
if ($fp && $matr) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    fputcsv($fp, $headers);
    while ($row = mysql_fetch_assoc($matr)) {
        fputcsv($fp, array_values($row));
    }
    die;
}

?>