<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Documentos de Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Documentos Asociados a Ordenes de Trabajo";

$lang = array(
'AVANCE DE REPARACIÓN?' => 'AVANCE DE REPARACIÓN?',
'Confidencial' => 'La imagen ¿Es un documento Confidencial?',
'Agregar múltiples documentos' => 'Agregar múltiples documentos relacionados a este<br>Expediente (Max 2Mb por archivo, Max 10MB en Total)',
'' => '',
'' => '',
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}


/* Página de idiomas para vehiculos */ 