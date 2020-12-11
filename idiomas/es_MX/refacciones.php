<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Gestión de Refacciones para la Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Gestión de Refacciones";

// --- Ahora en la tabla Valores --- $unidad = array("Pieza", "Litro", "Galón", "Kilo", "Gramo", "Juego", "Servicio");

$lang = array(
'AgregarPedido' => 'Agregar a<br>Pedido',
'CancelarCotización' => 'Cancelar Cotización',
'Condición' => 'Condición',
'CostoTotal' => 'Costo Total<br>sin IVA',
'CostoUnitario' => 'Costo Unitario<br>sin IVA',
'CotGanadora' => 'Cotizacion Ganadora',
'DíasCrédito' => 'Días<br>Crédito',
'DíasEntrega' => 'Días de<br>Entrega',
'Costo' => 'Costo',
'CostoEnvio' => 'Costo de Envio',
'Estimado Proveedor' => 'Estimado Proveedor',
'FechaCotización' => 'Fecha<br>Cotizado',
'Imitación' => 'Imitación',
'IVA' => 'IVA al ',
'MensajeVendedor' => 'Mensaje del Proveedor',
'No hay tareas' => 'No hay tareas para mostrar en esta Orden de Trabajo<br> o este usuario NO tiene permiso de gestionar las tareas existentes.',
'Nuevo' => 'Nuevo',
'OCM' => 'Origen<br>Condición',
'Origen' => 'Origen',
'Original' => 'Original',
'Proveedor' => 'Proveedor',
'RecibidaQV' => 'Automática',
'Reconstruido' => 'Reconstruido',
'RR para VSRP Explica' => 'Refacciones recibidas para tarea terminada con refacciones pendientes. Si ya se recibieron todas las refacciones que estaban pendientes y el vehículo ya fue entregado al cliente, por favor contacta al cliente para que regrese por sus refacciones, gracias.',
'RR para VSRP' => 'Refacciones recibidas para tarea terminada con refacciones pendientes.',
'SelProvCot' => 'Seleccione al menos UN proveedor para cotizar',
'SelSoloUnProv' => 'Seleccione SOLO UN proveedor para pedido',
'SelTipoSol' => 'Seleccione el tipo de solicitud',
'SelUnItem' => 'Seleccione al menos una refacción, consumible o mano de obra',
'SelUnProv' => 'Seleccione un proveedor para pedido',
'Subtotal' => 'Subtotal',
'Total' => 'Total',
'Usado' => 'Usado',
'Vencida' => 'Vencida',
'Vencimiento' => 'Vigencia de<br>Cotización<br>YYYY-mm-dd',
'' => '',
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

$ayuda = [
	'AyudaDatosCotizaciones' => '
					<p>Se presenta una abreviatura de dos a cuatro letras para identificar el Origen y Condición de la refacción cotizada, si hay mensaje del proveedor y si hay costos de envío de la refacción.</p>
					<ul><li>La primera letra corresponde al origen de la refacción <b>O</b> para Original, <b>T</b>  para Taiwan o Imitación, <b>H</b> para Homologado y <b>R</b> para Reconstruido.</li>
					<li>La segunda letra indica la condición: <b>N</b> para Nueva, <b>U</b> para Usada y <b>R</b> para Reparada.</li>
					<li>Si existe un mensaje del proveedor para esta cotización, se agregará una letra <b>M</b> a la abreviatura.</li>
					<li>Por último, si existe algún costo de envío para la cotización se agregará un símbolo de <b>$</b> dinero con el importe para denotar esta situación. Es importante remarcar que este Costo de Envio puede ser por una o más refacciones.</li>
					</ul>
					<p>De esta manera:<br><b>TU</b> significa <b>T</b>aiwan y <b>U</b>sada.<br><b>OU</b> significa <b>O</b>riginal y <b>U</b>sada.<br><b>ORM</b> significa <b>O</b>riginal, <b>R</b>eparada y con <b>M</b>ensaje del proveedor.<br>Por último <b>ON$100</b> signiifica que la refacción es <b>O</b>riginal, <b>N</b>ueva y tiene un costo de envío de <b>$100</b>.<br><br>El mensaje del proveedor se puede ver al Cargar Datos de Cotizaciones.</p>
					<p style="background-color:#79FF00;">ASE determina la mejor cotización comparando el Costo, Días de Entrega, Días de Crédito y Reputación del Proveedor de cada cotización de acuerdo a la importancia que la dirección del CDR asigna a cada uno de estos parámetros. Preferentemente se debe utilizar esta detemimación al hacer pedidos, de otra manera podría tener que explicar su decisión.</p>'."\n",
	'CotizaciónQV' => '<p style="text-align: justify;">Si se trata de una cotización capturada directamente por el cotizador, este podrá modificarla, incluso cancelarla si tiene permisos adecuados, pero si la cotización fue recibida desde el sistema <b>Quien-Vende.com?</b>, esta sólo podrá ser retirada o modificada por el proveedor desde el sistema <b>Quien-Vende.com?</b>.</p>',
];

/* Página de idiomas para refacciones */ 
