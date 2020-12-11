<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Proveedores | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico de Clase Mundial.";
$pagina_actual="Administración de Proveedores";

$lang = array(
'acceso_error' => 'Acceso NO autorizado ingresar Usuario y Clave correcta!',
'Año (aaaa)' => 'Año (aaaa): ',
'Consultar' => 'Consultar',
'Factura' => 'Factura',
'Fecha del Pago' => 'Fecha del Pago',
'Lista de Facturas' => 'Listado de Pagos a Proveedores',
'Mes (mm)' => 'Mes (mm): ',
'Monto' => 'Monto',
'No Aplica' => 'No Aplica',
'Nombre del Proveedor' => 'Nombre del Proveedor: ',
'Número de Factura' => 'Número de Factura: ',
'OT' => 'OT',
'Pedido' => 'Pedido',
'Periodo equivocado' => 'El año y mes indicados son anteriores a los registros disponibles.',
'Proveedor' => 'Proveedor',
'referencia' => 'Referencia',
'Referencia' => 'Referencia: ',
'Sin resultados' => 'No se encontraron registros con los datos proporcionados, intente de nuevo por favor.<br>',
'Sin selectores' => 'No se indicó el Proveedor, la factura o la referencia de pago, intente de nuevo por favor.<br>',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
);

// ' . $lang[''] . '

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para proveedores */ 