<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Reportes, Gráficas y Tablas | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Reportes y Gráficas";


$lang = array(
'A Cargo Aseguradora' => 'A Cargo Aseguradora',
'Bodega' => 'Bodega',
'Costo Item' => 'Costo Item',
'Entregado' => 'Entregado a Cliente',
'Entregado a' => 'Con',
'Estatus de Cobro' => 'Estatus de Cobro',
'Estatus' => 'Estatus',
'Filtrar Partes' => 'Filtrar Partes por Nombre',
'Filtrar por Tipo' => 'Filtrar por Tipo de Producto',
'Filtrar por Estatus' => 'Filtrar por Estatus',
'Flujo Egresos' => 'Egresos',
'Flujo Ingresos' => 'Ingresos',
'Item' => 'Cantidad y Descripción',
'Monto Pagado' => 'Monto Pagado',
'Monto por Pagar' => 'Monto por Pagar',
'Operario' => 'Operario',
'OT' => 'OT',
'OT Cancelada' => 'Cancelada',
'OT Cobrada' => 'Cobrada',
'OT Por Cobrar' => 'Por Cobrar',
'OT Sin Facturar' => 'Sin Facturar',
'Pedido Directo' => 'Pedido Directo',
'Pedido' => 'Pedido',
'Pendiente' => 'Por Recibir',
'Placa' => 'Placas',
'Por Devolver' => 'Por Devolver',
'Por Entregar' => 'Instalado',
'Proveedor' => 'Proveedor',
'Recibido sin entregar' => 'Recibido sin entregar',
'Recibido sin asignación' => 'Recibido sin asignación',
'Reporte de Refacciones en Proceso' => 'Reporte de Inventario en Proceso',
'Sin Factura' => 'Sin Factura',
'Tipo Item' => 'Tipo de<br>Producto',
'Todos' => 'Todos',
'Total Pagado' => 'Total Pagado',
'Total Por Pagar' => 'Total Por Pagar',
'Total Refacciones' => 'Total Refacciones',
'Vehículo' => 'Vehículo',
'' => '',
'' => '',
'' => '',
);

$lang_grua = [
	""  => "--",
	"0" => "--",
	"1" => "SI",
	"2" => "NO",
];

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para reportes */ 
