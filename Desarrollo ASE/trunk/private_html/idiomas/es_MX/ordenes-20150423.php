<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
if($accion=='listar') {
	$pagina_actual="Resultados de Búsqueda de Ordenes de Trabajo y Presupuestos Prévios";
} else {
	$pagina_actual="Resumen de Orden de Trabajo";
}

define('CATEGORIA_DE_REPARACION_1', 'Pesado');
define('CATEGORIA_DE_REPARACION_2', 'Urbano');
define('CATEGORIA_DE_REPARACION_3', 'Rápido');
define('CATEGORIA_DE_REPARACION_4', 'Express');

$lang = array(
'Placas' => ' Placas: ',
'Placa' => 'Placas',
'Registrar cobro de Anticipo' => 'Registrar cobro de Anticipo',
'Cambio de números' => 'Cambio de números de Siniestro y Póliza de Seguro',
'Cambiar número de Póliza de' => 'Cambiar número de Póliza de',
'Cambiar número de Siniestro de' => 'Cambiar número de Siniestro de',
'Cambiar monto de Deducible de' => 'Cambiar monto de Deducible de',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para Ordenes de Trabajo */ 