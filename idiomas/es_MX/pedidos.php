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
'Adelanto' => 'Adelanto',
'Alerta' => 'Alerta',
'AsignarFID' => 'Asignar a FID',
'Banco' => 'Banco',
'Cancelar' => 'Cancelar',
'Cancelar Factura' => 'Cancelar Factura',
'CancelaItem' => 'Cancele factura',
'CanNoAuth' => 'No Autorizado',
'Cuenta' => 'Cuenta',
'DelPedido' => 'A factura del pedido',
'DesAsocXElim' => 'Debe desasociar los pagos antes de proceder a eliminarlos',
'DetPag' => 'Detalles del pago',
'Devolver' => 'Devolver',
'Devuelto' => 'Devuelto',
'ElPagoMonto' => 'El pago con monto',
'estatus' => 'estatus',
'Estatus' => 'Estatus',
'ExcedeMonto' => 'excede el monto por pagar de',
'exportar pedidos' => 'exportar pedidos',
'FiltXEsta' => 'Filtrar por Estatus',
'FiltXProv' => 'Filtrar por Proveedor',
'FormPago' => 'Forma de pago',
'FPedido' => 'Fecha Pedido',
'FRecibido' => 'Fecha Recibido',
'Incompleto' => 'Incompleto',
'Huerfano' => 'Huerfano',
'MontoPagado' => 'Monto Pagado',
'MontoPedido' => 'Monto del Pedido',
'NoAsocFact' => ', no se asoció a la factura',
'NoAsociados' => 'NO ASOCIADOS A FACTURAS',
'NotaCred' => 'Nota de Crédito',
'no_asociados' => 'no_asociados',
'Nombre' => 'Nombre /<br>Promesa de Entrega',
'Normal' => 'Normal',
'OrdenID' => 'OrdenID',
'Pagado' => 'Con Pago',
'PagoAdelan' => 'Pago Adelantado',
'PagosProv' => 'PAGOS AL PROVEEDOR',
'Pedido' => 'Pedido',
'Placas' => ' Placas: ',
'PorDevolver' => 'Por Devolver',
'Proveedor' => 'Proveedor',
'Quitar' => 'Quitar',
'QuitarFacturas' => 'Por favor cancele las facturas del proveedor antes de quitar elementos del pedido',
'Referencia' => 'Referencia',
'RegFactDoc' => 'Registrar Factura o Documento de Cobro',
'Regresa Chatarra' => 'Retorno de Chatarra al almacén',
'RR para VSRP Explica' => 'Refacciones recibidas para tarea terminada con refacciones pendientes. Si ya se recibieron todas las refacciones que estaban pendientes y el vehículo ya fue entregado al cliente, por favor contacta al cliente para que regrese por sus refacciones, gracias.',
'RR para VSRP' => 'Refacciones recibidas para tarea terminada con refacciones pendientes.',
'TipoPedido' => 'Tipo de Pedido',
'Utilidad' => 'Utilidad',
'' => '',
];

/* --------------------- Pedidos ------------------------- */   

define('PEDIDO_ESTATUS_0','Por Confirmar Recepción');
define('PEDIDO_ESTATUS_5','Esperando Refacciones');
define('PEDIDO_ESTATUS_7','Anticipo Ref por Recibir');
define('PEDIDO_ESTATUS_10','Recibido, por registrar factura(s)');
define('PEDIDO_ESTATUS_15','Recibido con registro parcial de facturas');
define('PEDIDO_ESTATUS_20','Pago parcial con Factura(s) registrada(s)');
define('PEDIDO_ESTATUS_23','Pago parcial sin Factura(s) registrada(s)');
define('PEDIDO_ESTATUS_25','Factura(s) registrada(s) con pagos parciales');
define('PEDIDO_ESTATUS_30','Pagado sin registro de pagos en factura(s)');
define('PEDIDO_ESTATUS_50','Por Devolver');
define('PEDIDO_ESTATUS_55','Devuelto, esperando NC');
define('PEDIDO_ESTATUS_90','Cancelado');
define('PEDIDO_ESTATUS_91','Cancelado por Garantía');
define('PEDIDO_ESTATUS_92','Cancelado por Devolución');
define('PEDIDO_ESTATUS_99','Cerrado, recibido y pagado');

$PedidoEstatus = [
	'1' => 'Por Confirmar Recepción',
	'5' => 'Esperando Refacciones',
	'7' => 'Anticipo Ref por Recibir',
	'10' => 'Recibido, por registrar factura(s)',
	'15' => 'Recibido con registro parcial de facturas',
	'20' => 'Pago parcial con factura(s) registrada(s)',
	'23' => 'Pago parcial sin factura registrada',
	'25' => 'Factura(s) registrada(s) con pagos parciales',
	'30' => 'Pagado sin registro de pagos factura(s)',
	'50' => 'Por Devolver',
	'55' => 'Devuelto, esperando NC',
	'90' => 'Cancelado',
	'91' => 'Cancelado por Garantía',
	'92' => 'Cancelado por Devolución',
	'99' => 'Cerrado, recibido y pagado'
]; 

if(file_exists('particular/textos/pedidos.php')) {
	include('particular/textos/pedidos.php');
	$lang = array_replace($lang, $langextra);
}



$ayuda = [
	
	'NombreFPE' => '<p>Nombre de la Refacción, Consumible o Trabajo en Otro Taller (TOT) 
					seguido de la fecha promesa de entrega de dicho producto obtenido 
					de la cotización del proveedor o de los días de entrega predefinidos 
					para este proveedor.</p>
					<p class="rojo_tenue">Si el recuadro o casilla aparece con fondo de este color
					quiere decir que esta refacción no está asociada a una refacción autorizada
					o siendo autorizada, no tiene precio de venta.</p>',
	
	'QuitarPago' => '<p style="text-align:justify">Para <strong>Cancelar o quitar una factura</strong> o documento de pago del Pedido, es 
					necesario remover o desasociar los Pagos o Adelantos que estén ligados o asociados a una 
					factura. Los Adelantos y Pagos removidos de la factura se enviarán al cuadro de <strong>
					Pagos al Proveedor no Asociados a Facturas</strong>, mismos que se podrán aplicar a una 
					nueva factura o documento de cobro del proveedor en este pedido.<br><br>
					<span style="color:red; font-weight:bold">Es necesario remover los pagos para que se pueda 
					Cancelar una Factura o Documento de Pago del Proveedor.</span></p>',
	
	'NoAsociados' => '<p style="text-align:justify; color:black; font-weight:normal;">En esta tabla se 
					listan los Adelantos o Anticipos y Pagos Normales (hechos después de recibir los productos) 
					hechos al proveedor por este pedido que no han sido aplicados a ninguna factura, así como 
					los <strong>Pagos Huerfanos</strong> que originalmente fueron hechos a 
					este proveedor en otros pedidos que fueron cancelados. Estos pagos se pueden aplicar a 
					una factura de este pedido e incluso, con los permisos adecuados, se pueden eliminar por 
					completo del sistema.<br>
					Primero se aplican a las facturas los pagos hechos a este pedido y después se habilitan los 
					Pagos Huerfanos para, en su caso, aplicarlos a las facturas de este pedido.<br> 
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
			<li><b>' . PEDIDO_ESTATUS_15 . ':</b> El monto de las facturas registradas es menor al monto del pedido </li>
			<li><b>' . PEDIDO_ESTATUS_20 . ':</b> Ya fue programado el pago al proveedor</li>
			<li><b>' . PEDIDO_ESTATUS_25 . ':</b> Ya se realizaron pagos parciales al proveedor</li>
			<li><b>' . PEDIDO_ESTATUS_30 . ':</b> Pagado con adelanto, pero aún no se reciben las facturas del proveedor</li>
			<li><b>' . PEDIDO_ESTATUS_90 . ':</b> Se eliminaron todas las partes, facturas y pagos de este pedido</li>
			<li><b>' . PEDIDO_ESTATUS_91 . ':</b> Partes devueltas por defectos de fábrica</li>
			<li><b>' . PEDIDO_ESTATUS_92 . ':</b> Partes devueltas por razones diferentes a defectos de fábrica</li>
			<li><b>' . PEDIDO_ESTATUS_99 . ':</b> Pedido completo en todos sus aspectos.</li>
			</ul>',
			
			'Cancelar' => '<p style="text-align:justify">Es posible cancelar o quitar un elemento del pedido sólo	en las siguientes condiciones:<br>
					1.- Cuando no se ha recibo ninguna cantidad del elemento y no hay registrado para pago una 
					factura o documento de cobro del proveedor.<br>
					2.- Los elementos recibidos pueden ser cancelados por las personas autorizadas (regularmente Gerentes) 
					siempre que no se haya registrado para pago una factura o documento de cobro del proveedor.<br><br>
					<span style="color: red;"><strong>Si no hay condiciones para cancelar</strong></span>, aparecerá leyenda 
					de la posible acción<br><br>
					En caso de quitar o remover todos los elementos de un pedido, éste será cancelado automáticamente y los 
					pagos realizados a este pedido (Anticipos y posterirores) se conviertirán en <strong>Pagos Huerfanos,
					</strong> es decir, dejan de pertenecer al este pedido (que fue cancelado) y ahora se podrán reutilizar 
					o aplicar en cualquier otra factura de cualquier otro pedido del mismo proveedor.</p>',
];

/* Página de idiomas para pedidos */ 
