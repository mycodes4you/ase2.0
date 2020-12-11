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

$ayuda = [
	$lang['Estatus'] => '			<h1>' . $lang['Estatus'] . '</h1>
			<div id="body">
				<div id="principal">
					<ul>
						<li><b>' . $lang['Pendiente'] . ':</b> Producto o servicio pedido aún no recibido.</li>
						<li><b>' . $lang['Recibido sin asignación'] . ':</b> Recibido pero no se ha registrado el inicio de trabajos.</li>
						<li><b>' . $lang['Recibido sin entregar'] . ':</b> No se ha registrado a que Operario se entregó.</li>
						<li><b>' . $lang['Entregado a'] . ':</b> Se indica el nombre del Operario que recibió el Producto o servicio.</li>
						<li><b>' . $lang['Por Devolver'] . ':</b> Producto o servicio recibido en una tarea que se determinó como NO reparable por lo que se debe realizar la devolución correspondiente.</li>
						<li><b>' . $lang['Por Entregar'] . ':</b> Producto o servicio instalado y terminado por entregar al cliente.</li>
						<li><b>' . $lang['Entregado'] . ':</b> Producto o servicio instalado y entregado al cliente.</li>
					</ul> 
				</div>
			</div>',
	$lang['Costo Item'] => '			<h1>' . $lang['Costo Item'] . '</h1>
			<div id="body">
				<div id="principal">
					En esta columna se indica el costo a pagar por el item o partida del pedido, pero en los casos en que la aseguradora o el cliente de convenio entrega la pieza, material o servicio, <b>no hay ningún costo que pagar</b>, por lo tanto se indica esta condición con la leyenda <b><i>"A Cargo Aseguradora"</i></b> aún cuando se hubiera capturado el costo en el pedido.  Tampoco se suma al monto total a pagar.
				</div>
			</div>',
	$lang['Estatus de Cobro'] => '			<h1>' . $lang['Estatus de Cobro'] . '</h1>
			<div id="body">
				<div id="principal">
					La tarea a la que pertenece este producto o servicio:
					<ul>
						<li><b>' . $lang['OT Sin Facturar'] . ':</b> No tiene registro de documento de Cobro al Cliente (Remisión o Factura). No necesariamente es un error, puede ser que aún no esté terminada la tarea o que aún no se haya entregado el vehículo al cliente; en todo caso se recomienda revisar el estatus general de la OT.</li>
						<li><b>' . $lang['OT Por Cobrar'] . ':</b> Ya tiene un documento de cobro pero aún no se ha cobrado.</li>
						<li><b>' . $lang['OT Cobrada'] . ':</b> Ya fue cobrada, ya se recuperó la inversión.</li>
						<li><b>' . $lang['OT Cancelada'] . ':</b> Tiene un documento de cobro que fue cancelado, por lo que podría ser vuelto a cobrar o puede terminar en devolución del producto o servicio. Se debe revisar la situación con el Valuador.</li>
					</ul>
				</div>
			</div>'
];

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
