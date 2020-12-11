<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Bienvenidos a AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Bienvenido a AutoShop Easy";

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
'Área' => 'Ubicación<br>Área',
'Asesor' => 'Asesor',
'ASESORES' => 'ASESORES',
'CALIDAD' => 'CALIDAD',
'Comentario' => 'Comentario de seguimiento al Cliente',
'comentarios de seguimiento' => ' comentarios de seguimiento...',
'Comp' => 'Comp',
'Días para' => 'Días para',
'Entrega' => 'Entrega',
'Estatus' => 'Estatus',
'Estructurales Pendientes' => 'Refacciones Estructurales Pendientes',
'Fecha Ing' => 'Fecha<br>Ingreso',
'Hist' => 'Hist',
'LAVADORES' => 'LAVADORES',
'No estructurales Pendientes' => 'Refacciones Pendientes',
'Num Serie' => 'Placas',
'OPERADORES' => 'OPERADORES',
'Reporte de Seguimiento en Proceso' => 'Reporte de Seguimiento de Ordenes de Trabajo en Proceso',
'Seg' => 'Seg',
'SUPERVISORES' => 'SUPERVISORES',
'Taller' => 'Taller',
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
