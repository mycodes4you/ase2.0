<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Gestión de Refacciones para la Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Gestión de Refacciones";

$unidad = array("Pieza", "Litro", "Kilo", "Gramo", "Juego", "Servicio");

$lang = array(
'RR para VSRP' => 'Refacciones recibidas para tarea terminada con refacciones pendientes.',
'RR para VSRP Explica' => 'Refacciones recibidas para tarea terminada con refacciones pendientes. Si ya se recibieron todas las refacciones que estaban pendientes y el vehículo ya fue entregado al cliente, por favor contacta al cliente para que regrese por sus refacciones, gracias.',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
);

// ' . $lang[''] . '

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}


/* Página de idiomas para refacciones */ 