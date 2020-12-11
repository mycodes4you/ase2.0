<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Consulta histórico de precios de partes";
$keywords="";
$pag_desc="";
$pagina_actual="Precios de Partes";

$lang = array(
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para vehiculos */ 