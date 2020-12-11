<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Presupuestos | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Presupuestos de Ordenes de Trabajo";

$lang = array(
'Placas' => ' Placas: ',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para presupuestos */ 