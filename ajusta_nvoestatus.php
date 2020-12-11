<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');

if ($_SESSION['usuario'] == '701' || $_SESSION['usuario'] == '1000') {
        // Acceso autorizado
} else {
        redirigir('usuarios.php');
}

$preg0 = "SELECT orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id = '$orden_id'";
$matr0 = mysql_query($preg0) or die("ERROR: Fallo seleccion de ordenes! " . $preg0);
$ord = mysql_fetch_array($matr0);

$preg = "SELECT sub_orden_id, sub_estatus FROM " . $dbpfx . "subordenes WHERE orden_id = '$orden_id' AND sub_estatus < '190' AND fact_id IS NULL AND recibo_id IS NULL";
$matr = mysql_query($preg) or die("ERROR: Fallo seleccion de tareas! " . $preg);
while ($sub = mysql_fetch_array($matr)) {
        $preg1 = "UPDATE " . $dbpfx . "subordenes SET sub_estatus = '" . ($estatus + 100) . "' WHERE sub_orden_id = '" . $sub['sub_orden_id'] . "'";
        $matr1 = mysql_query($preg1) or die("ERROR: Fallo actualizaci  n! " . $preg1);
}
actualiza_orden ($orden_id, $dbpfx);
bitacora ($sub['sub_orden_id'], 'Cambi   el estatus de la OT de ' . $ord['orden_estatus'] . ' a ' . $estatus, $dbpfx);

?>
