<?php
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

$segundos = strtotime('2019-08-07T13:17:17');

echo $segundos . '<br>';

echo date('Y-m-d H:i:s', $segundos);


?>
