<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Facturación de Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Facturación de Ordenes de Trabajo";
define('CONCEPTOS_0', 'REPARACION HOJALATERIA'); 
define('CONCEPTOS_1', ' Y PINTURA.'); 
define('CONCEPTOS_2', ', PINTURA Y REFACCIONES.'); 

$mes = array(
	'01' => 'Enero',
	'02' => 'Febrero',
	'03' => 'Marzo',
	'04' => 'Abril',
	'05' => 'Mayo',
	'06' => 'Junio',
	'07' => 'Julio',
	'08' => 'Agosto',
	'09' => 'Septiembre',
	'10' => 'Octubre',
	'11' => 'Noviembre',
	'12' => 'Diciembre',
);

$lang = array(
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para factura */ 