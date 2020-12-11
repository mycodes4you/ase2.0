<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Gestión de Cambios y Devoluciones de Refacciones para la Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Gestión de Cambios y Devoluciones";

// ------ Las devoluciones aplican cuando se devuelve la pieza al proveedor por cualquier causa (defectuosa, equivocada, trabajo cancelado)
// ------ y se aplican los ajustes necesarios para evitar pagos dobles al proveedor, registrar notas de crédito o montos a favor del CRA,
// ------ así como el debido proceso fiscal y contable de las devoluciones.
//
// ------ Los cambios aplican cuando es necesario volver a comprar una pieza ya sea por ser dañada o extraviada por el personal, incluso
// ------ se gestionan las piezas dañadas o extraviadas que no eran parte original de la reparación. Todos los cambios deben ser
// ------ debidamente aplicados como descuentos al personal correspondiente y aplicar los ajustes necesarios a los costos de reparación
// ------ en su caso.

$lang = [
'AccNoAut' => 'Acceso no autorizado',
'Acceso autorizado' => 'Acceso autorizado',
'CambODev' => 'Registro de reemplazo o devolución de refacciones, materiales y consumibles',
'Cantidad' => 'Cantidad',
'ContraRec' => 'Contra recibo de entrega a proveedor',
'Costo' => 'Costo',
'DebidoA' => 'Debido a',
'DelPed' => 'del Pedido',
'DelProv' => 'a el Proveedor',
'DelVeh' => 'para el vehículo',
'DeOt' => 'De la Orden de Trabajo',
'DetalleSol' => 'Detalle de la solicitud',
'DevoAuto' => 'Devolución autorizada de',
'DevoAutoComp' => ', proceder a cotizar, pedir o eliminar según sea necesario',
'Devolucion' => 'Devolución',
'DevolucionCan' => 'Devolución Cancelada',
'DevolucionTer' => 'Devuelto',
'DevoluDe' => 'Devolución de partes #',
'DocAgregado' => 'Se subió la imagen del contra recibo de devolución',
'Enviar' => 'Enviar',
'Estatus' => 'Estatus',
'Estimado' => 'Estimado',
'FeFin' => 'Fecha de Fin',
'FeIni' => 'Fecha de Inicio',
'FecRec' => 'Fecha Recibido',
'ImprimirCR' => 'Imprimir Contra Recibo de Devolución',
'Impuestos' => 'Impuestos',
'IngresaOT' => 'Ingresa el número de OT en donde se requiere el cambio',
'ItemDevuelto' => 'El siguiente item fue devuelto a su proveedor:',
'ListaDic' => 'Lista de Requerimientos Dictaminados',
'Motivo' => 'Motivos',
'MotiDev' => 'Motivo de devolución',
'NombreArea' => 'Área',
'NombreItem' => 'Nombre de Refacción o Material',
'NotiCambDevo' => 'Nuevo requerimiento de Reemplazo o Devolución',
'NuevoItem' => 'Nuevo Item insertado para remplazar cambio o devolución en Tarea',
'OT' => 'OT',
'ParaProv' => 'Para el proveedor:',
'Parte' => 'Parte',
'PartesDev' => 'Partes a devolver a',
'Pedido' => 'Pedido',
'PedidoDevolver' => 'Se inicio devolución de items del pedido',
'PersonaEntrega' => 'Persona del CRA que ENTREGA las partes',
'PersonaRecibe' => 'Persona del Proveedor que RECIBE las partes',
'Proveedor' => 'Proveedor',
'ProveedorMas' => 'Selecciona Items a devolver de UN solo proveedor.',
'Rechazar' => 'Rechazar',
'ReemAuto' => 'Reemplazo autorizado de',
'ReemAutoComp' => ', proceder a cotizar y pedir',
'ReemDe' => 'Reemplazo de',
'Reemplazo' => 'Reemplazo',
'ReemplazoCan' => 'Reemplazo Cancelado',
'ReemplazoTer' => 'Reemplazado',
'Req' => 'Requerimiento', 
'ReqDict' => 'Solicitudes de Reemplazo o Devolución Dictaminados',
'ReqCDCreado' => 'Se creo el requerimiento de Reemplazo o Devolución',
'ReqPendDict' => 'Solicitudes de Reemplazo o Devolución Pendientes de Dictaminar',
'Responsable' => 'Responsable',
'Selecciona' => 'Selecciona',
'SinArea' => 'Para remplazos sin pedido, debe seleccionar el área de la nueva tarea a crear',
'SinCant' => 'La cantidad debe ser mayor que 0',
'SinMotivo' => 'Por favor coloque el motivo del requerimiento',
'SinNomb' => 'Coloque el nombre de la pieza.',
'SinTipoReq' => 'Indique el tipo de requerimiento --Devolución o Reemplazo',
'SinUsuDict' => 'No se ha configurado el usuario que Aprobará o Rechazará los requerimientos de Reemplazo o Devolución',
'Solicitante' => 'Solicitante',
'SubTot' => 'SubTotal',
'SubirCR' => 'Subir Contra Recibo',
'TextoDevol1' => 'Por medio de la presente comunicación solicitamos nos hagan el favor de RECOGER las siguentes partes o refacciones de nuestro PEDIDO',
'TextoDevol2' => 'mismas que le serán devueltas por la siguiente causa: ',
'TipoReq' => 'Tipo de Petición:',
'TodosEstatus' => 'Todos los Estatus',
'TodosProv' => 'Todos los Proveedores',
'TodosTipos' => 'Todas las Solicitudes',
'Total' => 'Total',
'Vehículo' => 'Vehículo',
'' => '',
];

$ayuda = [
	'AsignarFID' => '<p>Podemos seleccionar a que Factura de Proveedor (mediante su 
		identificador FID) queremos asignar un pago. Este pago dejará de estar en 
		la tabla de pagos no asociados y se descontará del importe a pagar de la 
		una factura.</p>',
	
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

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para pedidos */ 
