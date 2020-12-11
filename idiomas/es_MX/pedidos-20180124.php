<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Gestión de Refacciones para la Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Gestión de Pedidos y Pago a Proveedores";

$lang = [
'AccNoAut' => 'Acceso no autorizado',
'AccGerente' => 'Acceso sólo para Gerentes',
'AsignarFID' => 'Asignar a FID',
'Cancelar Factura' => 'Cancelar Factura',
'DesAsocXElim' => 'Debe desasociar los pagos antes de proceder a eliminarlos',
'ElPagoMonto' => 'El pago con monto',
'estatus' => 'estatus',
'ExcedeMonto' => 'excede el monto por pagar de',
'exportar pedidos' => 'exportar pedidos',
'NoAsocFact' => ', no se asoció a la factura',
'NoAsociados' => 'NO ASOCIADOS A FACTURAS',
'no_asociados' => 'no_asociados',
'Nombre' => 'Nombre /<br>Promesa de Entrega',
'Pagado' => 'Con Pago',
'PagosProv' => 'PAGOS AL PROVEEDOR',
'Placas' => ' Placas: ',
'Quitar' => 'Quitar',
'QuitarFacturas' => 'Por favor cancele las facturas del proveedor antes de quitar elementos del pedido',
'Regresa Chatarra' => 'Retorno de Chatarra al almacén',
'RegFactDoc' => 'Registrar Factura o Documento de Cobro de Proveedor',
'RR para VSRP Explica' => 'Refacciones recibidas para tarea terminada con refacciones pendientes. Si ya se recibieron todas las refacciones que estaban pendientes y el vehículo ya fue entregado al cliente, por favor contacta al cliente para que regrese por sus refacciones, gracias.',
'RR para VSRP' => 'Refacciones recibidas para tarea terminada con refacciones pendientes.',
'' => '',
'' => '',
'' => '',
];

$ayuda = [
	
	'NombreFPE' => '<p>Nombre de la Refacción, Consumible o Trabajo en Otro Taller (TOT) 
					seguido de la fecha promesa de entrega de dicho producto obtenido 
					de la cotización del proveedor o de los días de entrega predefinidos 
					para este proveedor.</p>',
	
	'QuitarPago' => '<p>Puede remover o des asociar pagos de una factura de proveedor, 
					estos se enviarán a la tabla de <b>Pagos al Proveedor no Asociados a 
					Facturas</b> y podrán ser reasignados en cualquier factura de cualquier
					pedido a nombre del proveedor.<br>
					Es necesario remover los pagos para que se pueda Cancelar una Factura
					de Proveedor.</p>',
	
	'NoAsociados' => '<p style="color:black; font-weight:normal;">En esta tabla se listan
					los pagos hechos al proveedor y que no están relacionados con ninguna 
					factura, ya sean anticipos, devoluciones o cualquier otra causa. Se pueden
					asociar a una factura del proveedor o incluso se pueden eliminar del
					sistema.<br>
					<span style="color:red; font-weight:bold;">Los pagos que se 
					eliminen, ya no se pueden recuperar</span>.</p>',
	
	'AsignarFID' => '<p>Podemos seleccionar a que Factura de Proveedor (mediante su 
					identificador FID) queremos asignar un pago. Este pago dejará de estar en 
					la tabla de pagos no asociados y se descontará del importe a pagar de la 
					una factura.</p>',
	
	'AyudaEstatus' => '
			<h2>Estatus del Pedido</h2>
			<ul>
			<li><b>' . PEDIDO_ESTATUS_0 . ':</b> El pedido fue enviado y se espera confirmación del proveedor de haberlo recibido</li>
			<li><b>' . PEDIDO_ESTATUS_5 . ':</b> El proveedor esta por entregar las refacciones</li>
			<li><b>' . PEDIDO_ESTATUS_7 . ':</b> Se realizó un pago adelantado al proveedor para que entregue las refacciones</li>
			<li><b>' . PEDIDO_ESTATUS_10 . ':</b> Las refacciones fueron recibidas y se iniciará el proceso de pago</li>
			<li><b>' . PEDIDO_ESTATUS_20 . ':</b> Ya fue programado el pago al proveedor</li>
			<li><b>' . PEDIDO_ESTATUS_25 . ':</b> Ya se realizaron pagos parciales al proveedor</li>
			<li><b>' . PEDIDO_ESTATUS_30 . ':</b> Pagado con adelanto, pero aún no se reciben las facturas del proveedor</li>
			<li><b>' . PEDIDO_ESTATUS_90 . ':</b> Se eliminaron todas las partes, facturas y pagos de este pedido</li>
			<li><b>' . PEDIDO_ESTATUS_91 . ':</b> Partes devueltas por defectos de fábrica</li>
			<li><b>' . PEDIDO_ESTATUS_92 . ':</b> Partes devueltas por razones diferentes a defectos de fábrica</li>
			<li><b>' . PEDIDO_ESTATUS_99 . ':</b> Pedido completo en todos sus aspectos.</li>
			</ul>',
];

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para pedidos */ 
