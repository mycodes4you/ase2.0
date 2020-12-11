<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Resumen de Orden de Trabajo";

define('CATEGORIA_DE_REPARACION_1', 'Pesado');
define('CATEGORIA_DE_REPARACION_2', 'Urbano');
define('CATEGORIA_DE_REPARACION_3', 'Rápido');
define('CATEGORIA_DE_REPARACION_4', 'Express');

$lang = array(
'Placas' => ' Placas: ',
'Placa' => 'Placas',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para Ordenes de Trabajo */ 