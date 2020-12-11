<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Proveedores | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico de Clase Mundial.";
$pagina_actual="Administración de Proveedores";

// --- Recorrer los meses ---
	
$meses_anio = [
	'1' => 'ENERO', 
	'2' => 'FEBRERO', 
	'3' => 'MARZO', 
	'4' => 'ABRIL', 
	'5' => 'MAYO', 
	'6' => 'JUNIO', 
	'7' => 'JULIO', 
	'8' => 'AGOSTO', 
	'9' => 'SEPTIEMBRE', 
	'10' => 'OCTUBRE', 
	'11' => 'NOVIEMBRE', 
	'12' => 'DICIEMBRE', 
];
	


$lang = array(
'acceso_error' => 'Acceso NO autorizado ingresar Usuario y Clave correcta!',
'Año (aaaa)' => 'Año (aaaa): ',
'Banco Origen' => 'Banco de origen:',
'Consultar' => 'Consultar',
'Cuenta del pago' => 'Cuenta del pago:',
'del proveedor' => 'del proveedor',
'Desglose' => 'Detalle de Cuentas por Pagar',
'Factura' => 'Factura',
'Facturas pendientes de pago' => 'Facturas pendientes de pago',
'Fecha del pago' => 'Fecha del pago:',
'Incompleto' => 'Incompleto',
'Informe Global por Proveedor' => 'Informe Global por Proveedor',
'Ir a Detalle de Cuentas por Pagar' => 'Ir a Detalle de Cuentas por Pagar',
'Lista de Facturas' => 'Listado de Pagos a Proveedores',
'Mes (mm)' => 'Mes (mm): ',
'Método de pago' => 'Método de pago:',
'Monto de este pago' => 'Monto de este pago:',
'Monto' => 'Monto',
'No Aplica' => 'No Aplica',
'Nombre del Proveedor' => 'Nombre del Proveedor: ',
'Num cheque o transferencia' => 'Num cheque o transferencia:',
'Número de Factura' => 'Número de Factura: ',
'OT' => 'OT',
'Pedido' => 'Pedido',
'Periodo equivocado' => 'El año y mes indicados son anteriores a los registros disponibles.',
'Proveedor' => 'Proveedor',
'RecSinFactura' => 'Sin factura',
'referencia' => 'Referencia',
'Referencia' => 'Referencia: ',
'Registrar pago' => 'Registro de pago ',
'Seleccione' => 'Seleccione',
'Sin resultados' => 'No se encontraron registros con los datos proporcionados, intente de nuevo por favor.<br>',
'Sin selectores' => 'No se indicó el Proveedor, la factura o la referencia de pago, intente de nuevo por favor.<br>',
'TodosProv' => 'Todos los Proveedores',
'' => '',
'' => '',
'' => '',
'' => '',
);

// ' . $lang[''] . '

if(file_exists('particular/textos/proveedores.php')) {
	include('particular/textos/proveedores.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para proveedores */ 