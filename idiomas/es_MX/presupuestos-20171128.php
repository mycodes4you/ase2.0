<?php
/*

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Presupuestos | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Presupuestos de Ordenes de Trabajo";

$lang = array(
'Acceso No Autorizado' => 'Su usuario no tiene permisos para ejecutar esta acción.',
'AjusPrecPres' => 'Ajustar',
'Cantidad' => 'Cantidad',
'Concepto' => 'Trabajo o Pieza a cambiar o reparar',
'EncabezadoDirectoHPM' => 'Agrega Refacciones, Productos, Materiales y Mano de Obra a presupuestar para la Reparación:',
'EnPesos' => 'en Pesos',
'EnUTs' => 'en UTs',
'Imagen escaneada' => 'Imagen escaneada en JPG de la',
'MO Cambiar' => 'Mano de Obra por Cambiar',
'Modificar Datos Tarea' => 'Modificar Datos Generales da la Tarea',
'MO Pintar' => 'Mano de Obra por Pintar',
'MO Reparar' => 'Mano de Obra por Reparar',
'nomdocadmin' => 'Orden de Admisión',
'nomdocrep' => 'Hoja de Daños',
'Placas' => ' Placas: ',
'PorcenMatPint' => '<br>% Materiales',
'Precio Pintar' => 'Precio por Pintar',
'Severidad OT' => 'Severidad General de la Reparación: ', 
'Visto' => 'Resuelto',
'NumPoliza' => 'Número de póliza de seguro: ',
'NumSin' => 'Número de reporte o siniestro: ',
'' => '',
'' => '',
'' => '',
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
