<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Contacto, Soporte y Ayuda | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Contacto, Soporte y Ayuda";

$lang = array(
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para contacto */ 
