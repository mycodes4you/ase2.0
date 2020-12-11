<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Venta desde Almacén | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Venta desde Almacén";

$unidad = array("Pieza", "Litro", "Kilo", "Gramo", "Juego", "Servicio");
$tangible = array(0 => "Mano de Obra", 1 => "Refacción", 2 => "Consumible", 3 => "Chatarra");

$lang = array(
'Cantidad' => 'Cantidad',
'Código' => 'Código',
'Descripción' => 'Nombre y descripción',
'Precio Unitario' => 'Precio Unitario',
'Sub Total' => 'Sub Total',
'' => '',
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