<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Usuarios de Trabajo en AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Administración de Usuarios";

define('GERENTE', 'Gerente de Taller');
define('JEFE_DE_TALLER', 'Jefe de Taller');
define('VALUADOR', 'Valuador');
define('ASESOR', 'Asesor de Servicio');
define('SUPERVISOR', 'Jefe de Área');
define('ALMACEN', 'Administrador de Almacén');
define('OPERADOR', 'Operador de Taller');
define('AYUDANTE', 'Lavador - Ayudante');
define('VIGILANTE', 'Vigilancia');
define('CALIDAD', 'Calidad');
define('VENTAS', 'Ventas');
define('ASISTENTE', 'Asistente de Gerencia');
define('SUPER_ADMIN', 'Administrador de la Aplicación');
define('PAGOS', 'Pagos a Proveedores');
define('ASEGURADORA', 'Consulta de Aseguradoras');

$lang = array();

if(file_exists('idiomas/' . $idioma . '/comun.php')) {
	include('idiomas/' . $idioma . '/comun.php');
	$lang = array_replace($lang, $langextra);
    
}

$codigos = array(1 => $lang['SUPER_ADMIN'],
	10 => $lang['GERENTE'],
	12 => $lang['ASISTENTE'],
	15 => $lang['JEFE DE TALLER'],
	20 => $lang['VALUADOR'],
	30 => $lang['ASESOR'],
	40 => $lang['JEFE DE AREA'],
	50 => $lang['ALMACEN'],
	60 => $lang['OPERADOR'],
	70 => $lang['AUXILIAR'],
	75 => $lang['VIGILANCIA'],
	80 => $lang['CALIDAD'],
	90 => $lang['COBRANZA'],
	100 => $lang['PAGOS'],
	2000 => $lang['ASEGURADORA'],
    );
/* Página de idiomas para personas */ 