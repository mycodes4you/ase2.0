<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Creación de Hoja de Costo Medio | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Creación de Hoja de Costo Medio";

$lang = array(
'Seleccione su archivo Excel a procesar' => 'Seleccione su archivo Excel a procesar',
'' => '',
'' => '',
'' => '',
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para costo-medio */ 
