<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Bienvenidos a AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Bienvenido a AutoShop Easy";

define('TABLA_AVISOS_ENC', 'Ordenes de Trabajo bajo la responsabilidad de su área');
define('TABLA_AVISOS_MOV', 'Último movimiento');
define('TABLA_AVISOS_NUM_ORDEN', '# Orden');
define('TABLA_AVISOS_ESTATUS', 'Estatus');
define('TABLA_AVISOS_ALERTA', 'Alerta');
define('TABLA_AVISOS_SUB', 'Ordenes de Trabajo en otra área de responsabilidad');
define('TABLA_AVISOS_RESPONSABLE', 'Grupo Responsable');

$lang = array(
'1' => '1',
'2 a 3' => '2 a 3',
'4 a 6' => '4 a 6',
'7 o +' => '7 o +',
'Días' => 'Días',
'En Taller' => 'En Taller: ',
'En Tránsito' => 'En Tránsito: ',
'Entregas Facturables por Mes' => 'Entregas Facturables por Mes',
'Enviar' => 'Enviar',
'Meses Pas' => 'Meses<br>Pasados',
'Ordenes con Refacc Pend' => 'Ordenes con Refacciones Pendientes',
'Ordenes en Etapa de Entrega' => 'Ordenes en Etapa de Entrega',
'OT del Grupo ' => 'Ordenes de Trabajo del Grupo ',
'OTs en Transito estado 4' => 'Autorizados en Tránsito',
'OT en Estatus ' => 'Ordenes de Trabajo en Estatus ',
'OT' => 'OT: ',
'Por estatus' => 'Por estatus: ',
'Seleccione' => 'Seleccione ...',
'Total Sin Reparar por Entregar' => 'Total Sin Reparar por Entregar',
'Facturas cobradas' => 'Facturas Cobradas',
'Facturas sin cobrar' => 'Facturas Sin Cobrar',
'Tareas sin facturar' => 'Tareas Sin Facturar',
'' => '',
'' => '',
);
if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para index */ 