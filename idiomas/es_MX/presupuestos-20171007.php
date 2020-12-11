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
'Severidad OT' => 'Severidad General de la Reparación: ',
'Visto' => 'Resuelto',
'nomdocadmin' => 'Orden de Admisión',
'nomdocrep' => 'Hoja de Daños',
'Imagen escaneada' => 'Imagen escaneada en JPG de la',
'' => '',
'' => '',
);

$lang['Severidad'][1] = 'Express';
$lang['Severidad'][2] = 'Leve';
$lang['Severidad'][3] = 'Media';
$lang['Severidad'][4] = 'Fuerte';

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para presupuestos */ 
