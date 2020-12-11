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
'CotAcept' => 'Aceptar Cotizaciones',
'CotBloq' => 'Bloquear Proveedor',
'CotPosp' => 'Ahora no, quiza después',
'Días' => 'Días en Taller',
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
'ProvVer' => 'Ver Proveedor',
'Seleccione' => 'Seleccione ...',
'Total Sin Reparar por Entregar' => 'PTs, PDs y Otros Sin Reparar<br>por Entregar',
'Facturas cobradas' => 'Facturas Cobradas',
'Facturas sin cobrar' => 'Facturas Sin Cobrar',
'Trabajos pendientes por facturar' => 'Trabajos pendientes por facturar',
'Visto' => 'Resuelto',
'VerDatosNvoProv' => 'Ver Nuevo Proveedor',
);


if(file_exists('idiomas/' . $idioma . '/comun.php')) {
	include('idiomas/' . $idioma . '/comun.php');
	$lang = array_replace($lang, $langextra);
    
}

$codigos = array('10' => array($lang['GERENTE'],'rol02'),
    '12' => array($lang['ASISTENTE'],'rol03'),
	'15' => array($lang['JEFE DE TALLER'],'rol04'),
	'20' => array($lang['VALUADOR'],'rol05'),
	'30' => array($lang['ASESOR'],'rol06'),
	'40' => array($lang['JEFE DE AREA'],'rol07'),
	'50' => array($lang['ALMACEN'],'rol08'),
	'60' => array($lang['OPERADOR'],'rol09'),
	'70' => array($lang['AUXILIAR'],'rol10'),
	'80' => array($lang['CALIDAD'],'rol11'),
	'90' => array($lang['COBRANZA'],'rol12'),
	'100' => array($lang['PAGOS'],'rol13'),
	'2000' => array($lang['ASEGURADORA'],'rol14'),
    );

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para index */ 