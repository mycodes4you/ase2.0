<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Consulta de Ordenes de Trabajo en AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Consulta de Ordenes de Trabajo";

$lang = array(
'Número de Placas' => 'Número de Placas: ',
'Tipo o Modelo' => 'Tipo o Modelo: ',
'Número de Siniestro' => 'Número de Siniestro: ',
'Serie o Placas' => 'Serie (VIN) o Placas: ',
'Torre' => 'Torre',
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para ordenes de trabajo */ 