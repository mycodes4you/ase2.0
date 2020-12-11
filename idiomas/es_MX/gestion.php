<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Gestión de Partes, Productos y Proveedores | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Gestión de Partes, Productos y Proveedores";

$lang = array(
'Almacén de Refacciones' => 'Almacén de Refacciones',
'Apodo NIC' => 'Apodo NIC',
'Autorización y Seguimiento de Refacciones' => 'Autorización y Seguimiento de <br> Refacciones',
'Borrar' => 'Borrar',
'CambDevol' => 'Cambios y Devoluciones',
'Consultar Proveedor' => 'Consultar Proveedor',
'e-Mail' => 'e-Mail',
'Enviar' => 'Enviar',
'Listado de Pedidos' => 'Listado de Pedidos',
'Listado de Pendientes' => 'Refacciones Pendientes',
'Listado de Refacciones' => 'Listado de Refacciones',
'Modificar Proveedor' => 'Modificar Proveedor',
'no_autorizado' => 'Acceso NO autorizado ingresar Usuario y Clave correcta',
'Número de Proveedor:' => 'Número de Proveedor:',
'# Orden de Trabajo' => '# Orden de Trabajo ',
'Paquetes de Servicio' => 'Paquetes de Servicio',
'# Pedido' => '# Pedido ',
'Pedidos de Refacciones' => 'Pedidos de Refacciones',
'Proveedores' => 'Proveedores',
'Razón Social' => 'Razón Social',
'Reportes de Refacciones' => 'Reportes de Refacciones',
'Venta de Refacciones' => 'Venta de Refacciones',
'Ventas Cobradas' => 'Ventas Cobradas',
'Ventas por Cobrar' => 'Ventas por Cobrar',
'# Venta' => '# Venta',
'' => '',
'' => '',
);

// ' . $lang[''] . '

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}


/* Página de idiomas para almacen */ 
