<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Reportes, Gráficas y Tablas | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Reportes y Gráficas";


$lang = array(
'Placa' => 'Placas',
);

$lang_grua = [
	""  => "--",
    "0" => "--",
    "1" => "SI",
	"2" => "NO",
		];

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para reportes */ 