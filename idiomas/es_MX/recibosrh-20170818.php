<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Recibos para Pagos a Personal";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Recibos para Pagos a Personal";
/* Página de idiomas para personas */ 

define('TIPO_PAGO_1', 'Efectivo');
define('TIPO_PAGO_2', 'Cheque');
define('TIPO_PAGO_3', 'Transferencia');

$lang = array(
'Acceso NO autorizado' => 'Acceso NO autorizado, ingresar Usuario y Clave correcta.',
'Acceso NO autorizado, ingresar Usuario y Clave correcta.' => 'Acceso NO autorizado, ingresar Usuario y Clave correcta.',
'Acceso sólo Gerente' => 'Acceso sólo para Gerente, ingresar Usuario y Clave correcta.',
'Acceso sólo para Rol Gerente, ingresar Usuario y Clave correcta.' => 'Acceso sólo para Rol Gerente, ingresar Usuario y Clave correcta.',
'ADJUNTAR HOJA A SU FACTURA DE AUTORIZADO' => 'ES INDISPENSABLE ADJUNTAR ESTA HOJA A SU FACTURA CON SELLO Y FIRMA DE AUTORIZADO',
'Anterior' => 'Anterior',
'Área' => 'Área',
'Banco' => 'Banco',
'Borrar datos' => 'Borrar datos',
'Comprobante de pago del Pedido' => 'Comprobante de pago del Pedido ',
'comprobante de pago' => 'Imagen comprobante de pago: ',
'Costo de' => 'Costo de',
'Cuenta' => 'Cuenta',
'Destajo' => 'Destajo',
'DESTAJO PAGADO' => 'DESTAJO PAGADO EL ',
'Documento' => 'Documento',
'Enviar' => 'Enviar',
'Fecha Creado' => 'Fecha Creado',
'Fecha del pago' => 'Fecha del pago',
'Fecha' => 'Fecha',
'Fecha Pagado' => 'Fecha Pagado',
'Forma de pago' => 'Forma de pago',
'forma de pago' => 'Seleccione una forma de pago.<br>',
'HOJA DE PAGO DE DESTAJOS' => 'HOJA DE PAGO DE DESTAJOS',
'indicar Banco y Cuenta para la forma de pago' => 'Debe indicar Banco y Cuenta para la forma de pago elegida.<br>',
'Inicio' => 'Inicio',
'IVA 16' => 'IVA al 16%:',
'Listado de Recibos' => 'Listado de Recibos',
'Materiales' => 'Materiales',
'Método de Pago' => 'Método de Pago',
'monto del pago no puede ser cero' => 'El monto del pago no puede ser cero, negativo o vacío.<br>',
'monto del pago no puede ser superior al monto pendiente por pagar' => 'El monto del pago no puede ser superior al monto pendiente por pagar.<br>',
'Monto' => 'Monto',
'Monto de pago' => 'Monto de este pago',
'Monto Pagado' => 'Monto Pagado',
'Monto Total' => 'Monto Calculado',
'Nombre' => 'Nombre: ',
'No se encontró recibo' => 'No se encontró un recibo con los datos proporcionados.',
'Num cheque o transferencia' => 'Num cheque o transferencia',
'Operador:' => 'Operador: ',
'Operador' => 'Operador',
'OT' => 'OT',
'Pagados' => 'Pagados',
'Por Pagar' => 'Por Pagar',
'Promedio antes de IVA' => ' Promedio antes de IVA: ',
'Recibo:' => 'Recibo: ',
'Recibo' => 'Recibo',
'Referencia' => 'Referencia',
'Registrar Pago a Operador' => 'Registrar Pago a Operador',
'Regresar a Usuarios' => 'Regresar a Usuarios',
'Seleccione las fechas' => 'Seleccione las fechas',
'Seleccione' => 'Seleccione... ',
'Siguiente' => 'Siguiente',
'Siniestro' => 'Siniestro',
'Subtotal Destajo' => 'Subtotal Destajo:',
'Subtotal Materiales' => 'Subtotal Materiales:',
'SubTotal' => 'Subtotal',
'Tareas' => 'OTs y Vehículos incluidos',
'Todos' => 'Todos',
'Total Calculado' => 'Total Calculado',
'Total Destajo' => 'Total Destajo:',
'Total Materiales' => 'Total Materiales:',
'Total Neto a Pagar' => 'Total Neto a Pagar',
'Total OTs' => 'Total OTs: ',
'Total Pagado' => 'Total Pagado',
'Total por pagar' => 'Total por pagar:',
'Última' => 'Última',
'Vehículo' => 'Vehículo',
'' => '',
'' => '',
'' => '',
);
if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para recibosrh */