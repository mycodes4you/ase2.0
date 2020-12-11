<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Gestión de Refacciones para la Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Gestión de Pedidos y Pago a Proveedores";

$lang = array(
'Placas' => ' Placas: ',
'Cancelar Factura' => 'Cancelar Factura',
'Regresa Chatarra' => 'Retorno de Chatarra al almacén',
'RR para VSRP' => 'Refacciones recibidas para vehículo entregado con refacciones pendientes.',
'RR para VSRP Explica' => 'Refacciones recibidas para tarea terminada con refacciones pendientes. Si ya se recibieron todas las refacciones que estaban pendientes y el vehículo ya fue entregado al cliente, por favor contacta al cliente para que regrese por sus refacciones, gracias.',
'Nombre' => 'Nombre /<br>Promesa de Entrega',
'' => '',
);

$ayuda = [
	$lang['Nombre'] => '			<h1>' . $lang['Nombre'] . '</h1>
			<div id="body">
				<div id="principal">
					Nombre de la Refacción, Consumible o Trabajo en Otro Taller (TOT) 
					seguido de la fecha promesa de entrega de dicho producto obtenido 
					de la cotización del proveedor o de los días de entrega predefinidos 
					para este proveedor.
				</div>
			</div>'."\n",
];

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para pedidos */ 
