<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Vehículos en Proceso Express";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Vehículos en Proceso Express";

define('TABLA_MONITOR_CRITICO', 'OT Detenidas');
define('TABLA_MONITOR_PREVENTIVO', 'OT Rezagadas');
define('TABLA_MONITOR_REFACCIONES', 'Esperando Refacciones');
define('TABLA_MONITOR_NO_REPARAR', 'No Reparar');
define('TABLA_MONITOR_NORMAL', 'OT En Tiempo');
define('COL_MOV', 'Último movimiento');
define('COL_NUM_ORDEN', '# Orden');
define('COL_ESTATUS', 'Estatus');
define('COL_RESPONSABLE', 'Grupo Responsable');
define('COL_ALERTA', 'Alerta');
define('TABLA_AVISOS_SUB', 'Ordenes de Trabajo en otra área de responsabilidad');

$lang = array(
'Alerta' => 'Alerta',
'ALMACEN' => 'ALMACEN',
'Área' => 'Ubicación Área',
'Asesor' => 'Asesor',
'ASESORES' => 'ASESORES',
'CALIDAD' => 'CALIDAD',
'Cliente' => 'Cliente',
'Comentario' => 'Comentario',
'comentarios de seguimiento' => ' comentarios de seguimiento...',
'ComSeg' => 'Comentarios de Seguimiento',
'Comp' => 'Comp',
'Días para' => 'Días para',
'En Taller' => 'Sólo en Taller y Anexos',
'En Tránsito' => 'Sólo en Tránsito',
'Entrega' => 'Entrega',
'Estatus' => 'Estatus',
'Estructurales Pendientes' => 'Refacciones Estructurales Pendientes',
'Fecha Ing' => 'Fecha Ingreso',
'cFPE' => 'Con Fecha Promesa',
'Hist' => 'Hist',
'LAVADORES' => 'LAVADORES',
'No estructurales Pendientes' => 'Refacciones Pendientes',
'Num Serie' => 'Num Serie',
'OPERADORES' => 'OPERADORES',
'Reporte de Seguimiento en Proceso' => 'Reporte de Seguimiento de Ordenes de Trabajo en Proceso',
'sFPE' => 'Sin Fecha Promesa',
'Seg' => 'Seg',
'Siniestro' => 'Siniestro',
'SUPERVISORES' => 'SUPERVISORES',
'Taller' => 'Taller',
'Todos' => 'Todos',
'Usuario' => 'Usuario',
'VALUADORES' => 'VALUADORES',
'Vehículo' => 'Vehículo',
'' => '',
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
	}

//define('', '');
/* Página de idiomas para monitoreo */ 