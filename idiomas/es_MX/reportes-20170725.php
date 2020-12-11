<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Reportes, Gráficas y Tablas | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Reportes y Gráficas";

$nombredia = array('0' => 'Domingo','1' => 'Lunes','2' => 'Martes','3' => 'Miércoles','4' => 'Jueves','5' => 'Viernes','6' => 'Sábado');


$lang = array(
'A Cargo Aseguradora' => 'A Cargo Aseguradora',
'Asesor' => 'Asesor',
'Bienvenida' => 'Bienvenida',
'Bodega' => 'Bodega',
'Cliente' => 'Cliente',
'Comisiones' => 'Comisiones',
'Consumibles' => 'Consumibles',
'Costo Item' => 'Costo Item',
'Deducible' => 'Deducible',
'Día de corte' => 'Día de corte:',
'DiasP' => 'Días en<br>proceso',
'Entregado a' => 'Con',
'Entregado' => 'Entregado a Cliente',
'Estatus de Cobro' => 'Estatus de Cobro',
'Estatus' => 'Estatus',
'Fecha de Entrega' => 'El rango de fechas aplica a la fecha de entrega del vehículo.',
'Fecha de Facturas' => 'El rango de fechas aplica a la fecha de emisión de las facturas.',
'Fecha de Recepción' => 'El rango de fechas aplica a la fecha de recepción del vehículo.',
'Fecha de Ultimo Movimiento' => 'El rango de fechas aplica a la fecha del último movimiento de la OT.',
'Fecha No Aplica' => 'No se aplica el rango de fechas, se muestran todos los registros.',
'FechaPromesa' => '<strong>Fecha Promesa de Entrega</strong>',
'FechaR' => 'Fecha<br>Recibido',
'Filtrar Partes' => 'Filtrar Partes por Nombre',
'Filtrar por Estatus' => 'Filtrar por Estatus',
'Filtrar por Tipo' => 'Filtrar por Tipo de Producto',
'Flujo Egresos' => 'Egresos',
'Flujo Ingresos' => 'Ingresos',
'Garantía' => 'Garantía',
'Item' => 'Cantidad y Descripción',
'MO' => 'M.O.',
'Monto Pagado' => 'Monto Pagado',
'Monto por Pagar' => 'Monto por Pagar',
'Operario' => 'Operario',
'OT Cancelada' => 'Cancelada',
'OT Cobrada' => 'Cobrada',
'OT' => 'OT',
'OT Por Cobrar' => 'Por Cobrar',
'OT Sin Facturar' => 'Sin Facturar',
'Partes' => 'Partes',
'Particular' => 'Particular',
'Pedido Directo' => 'Pedido Directo',
'Pedido' => 'Pedido',
'Pendiente' => 'Por Recibir',
'Placa' => 'Placas',
'Por Devolver' => 'Por Devolver',
'Por Entregar' => 'Instalado',
'PromesaE' => 'Promesa<br>Entrega',
'Proveedor' => 'Proveedor',
'Recibido sin asignación' => 'Recibido sin asignación',
'Recibido sin entregar' => 'Recibido sin entregar',
'Reparación' => 'Reparación',
'Reporte de Refacciones en Proceso' => 'Reporte de Refacciones en Proceso',
'Se encontraron' => 'Se encontraron',
'Sin Factura' => 'Sin Factura',
'Sin Fecha' => '<span style="background-color: red; color: white; font-weight:bold;">Sin Fecha</span>',
'Siniestro' => 'Siniestro',
'Terminado' => 'Terminado',
'Tipo Item' => 'Tipo de<br>Producto',
'Todos' => 'Todos',
'Todos Operarios' => 'Todos los Operarios',
'Total Pagado' => 'Total Pagado',
'Total Por Pagar' => 'Total Por Pagar',
'Total Refacciones' => 'Total Refacciones',
'TrabajosCliente' => 'trabajos para este cliente en este periodo',
'Trabajos para' => 'OTs con Trabajos para',
'Vehículo' => 'Vehículo',
'Ventas Adicionales' => 'Ventas Adicionales',
'VentaTotal' => 'Venta Total: $',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
);

$ayuda = [
	$lang['Comisiones'] => '			<h1>Calcular pago de comisiones</h1>
			<div id="body">
				<div id="principal">
					Ahora es posible hacer calculos de comisiones apartir de diferentes metas obtenidas por el taller, es decir que podemos generar comisiones a los usuarios usando la iformación proporcionada por los reportes de resultados.				
				</div>
			</div>'."\n",
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