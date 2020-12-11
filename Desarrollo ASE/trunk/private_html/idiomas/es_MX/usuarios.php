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

$lang = array(
'No' => 'No',
'Si' => 'Sí',
'HEntrada' => 'Hora de Entrada',
'HComida' => 'Hora de Comida',
'HSalida' => 'Hora de Salida',
'RolesAdi' => 'Roles Adicionales',
'' => '',
'' => '',
'' => '',
);


$segmento = [
'1' => '7:00',
'2' => '7:30',
'3' => '8:00',
'4' => '8:30',
'5' => '9:00',
'6' => '9:30',
'7' => '10:00',
'8' => '10:30',
'9' => '11:00',
'10' => '11:30',
'11' => '12:00',
'12' => '12:30',
'13' => '13:00',
'14' => '13:30',
'15' => '14:00',
'16' => '14:30',
'17' => '15:00',
'18' => '15:30',
'19' => '16:00',
'20' => '16:30',
'21' => '17:00',
'22' => '17:30',
'23' => '18:00',
'24' => '18:30',
'25' => '19:00',
'26' => '19:30',
'27' => '20:00',
'28' => '20:30',
];



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

$roles = [
'rol01' => $codigos[1],
'rol02' => $codigos[10],
'rol03' => $codigos[12],
'rol04' => $codigos[15],
'rol05' => $codigos[20],
'rol06' => $codigos[30],
'rol07' => $codigos[40],
'rol08' => $codigos[50],
'rol09' => $codigos[60],
'rol10' => $codigos[70],
'rol11' => $codigos[80],
'rol12' => $codigos[90],
'rol13' => $codigos[100],
'rol14' => $codigos[2000],
'rol15' => $codigos[75],
];

$cod_puesto = [
'1' => 'rol01',
'10' => 'rol02',
'12' => 'rol03',
'15' => 'rol04',
'20' => 'rol05',
'30' => 'rol06',
'40' => 'rol07',
'50' => 'rol08',
'60' => 'rol09',
'70' => 'rol10',
'80' => 'rol11',
'90' => 'rol12',
'100' => 'rol13',
'2000' => 'rol14',
'75' => 'rol15'];

$areas = array(
'Operativo' => array('comentarios.php', 'documentos.php', 'entrega.php', 'index.php', 'ingreso.php', 'monitoreo.php', 'notifica.php', 'ordenes-de-trabajo.php', 'ordenes.php', 'previas.php', 'proceso.php', 'produccion.php', 'reg-express.php', 'reportes.php?accion=comentreg', 'reportes.php?accion=entregados', 'reportes.php?accion=seguimiento', 'reportes.php?accion=valuaciones', 'reportes.php?accion=vigilancia', 'seguimiento.php', 'vehiculos.php'),
'Recusos Humanos' => array('recibosrh.php',),
'Usuarios' => array('boletines.php usuarios.php', 'usuarios.php', 'usuarioscb.php'),
'Clientes' =>  array('aseguradoras.php', 'cliente.php', 'personas.php', 'reportes.php?accion=aseguradora', 'reportes.php?accion=cliente'),
'Finanzas' => array('anticipos.php', 'cambdevol.php', 'contabilidad.php', 'destajos.php', 'factura.php', 'informes.php', 'presupuestos.php', 'reportes.php', 'eportes.php?accion=cliente', 'reportes.php?accion=audatrace', 'reportes.php?accion=cuentasxcobrar', 'reportes.php?accion=cuentasxpagar', 'reportes.php?accion=cxcglobal', 'reportes.php?accion=cliente', 'reportes.php?accion=destajo', 'reportes.php?accion=facturacion', 'reportes.php?accion=finanzas', 'reportes.php?accion=manodeobra', 'reportes.php?accion=operadores', 'reportes.php?accion=reportes'),
'Almacén' => array('almacen.php', 'pedidos.php', 'performance-talleres.php', 'proveedores.php', 'Reportes.php?accion=refproceso'),
'Refacciones' => array('refacciones.php')
);
/* Página de idiomas para personas */ 

$rolesMenuPersmisos = [
'rol01' => 'Sistemas',
'rol02' => 'Gerente',
'rol03' => 'Asist. Gerente',
'rol04' => 'J. Taller',
'rol05' => 'Valuador',
'rol06' => 'Asesor',
'rol07' => 'J. Área',
'rol08' => 'Refacciones',
'rol09' => 'Operador',
'rol10' => 'Auxiliar',
'rol11' => 'Calidad',
'rol12' => 'Cobranza',
'rol13' => 'Pagos',
'rol14' => 'Aseguradora',
'rol15' => 'Vigilancia'
];