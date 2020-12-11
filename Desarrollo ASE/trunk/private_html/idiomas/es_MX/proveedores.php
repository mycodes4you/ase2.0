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
'Activo' => 'Activo',
'Año (aaaa)' => 'Año (aaaa): ',
'Banco Origen' => 'Banco de origen:',
'Bloqueado' => 'Bloqueado',
'Consultar' => 'Consultar',
'CotAcept' => 'Aceptar Cotizaciones',
'CotBloq' => 'Bloquear Proveedor',
'CotPosp' => 'Ahora no quiza después',
'Cuenta del pago' => 'Cuenta del pago:',
'CuentasPorPagar' => 'Pedidos por Pagar',
'del proveedor' => 'del proveedor',
'Desglose' => 'Concentrado de pedidos',
'Deshab' => 'Deshabilitado',
'DiasPPagar' => 'Días para pagar',
'Enviar' => 'Enviar',
'Exportar' => 'Exportar a hoja de cálculo',
'Factura' => 'Factura',
'Facturas' => 'Factura(s)',
'Facturas pendientes de pago' => 'Facturas pendientes de pago',
'FechaDePago' => 'Fecha de Pago',
'Fecha del pago' => 'Fecha del pago:',
'FechaFin' => 'Fecha final de Pedios Recibidos',
'FechaInicio' => 'Fecha inicial de Pedios Recibidos',
'FechaRecibido' => 'Fecha Recibido',
'GlobalAl' => 'Global al ',
'ImpVenc' => 'Importe Vencido',
'Incompleto' => 'Incompleto',
'Informe Global por Proveedor' => 'Informe Global por Proveedor',
'Ir a Detalle de Cuentas por Pagar' => 'Ir a Detalle de Pedidos por Pagar',
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
'Pagado' => 'Pagado',
'PagoProgramado' => 'Pago Programado',
'para' => ' para ',
'#Pedido' => '# de Pedido',
'Pedido' => 'Pedido',
'PedGestQV' => 'Pedido Quien Vende?',
'Periodo equivocado' => 'El año y mes indicados son anteriores a los registros disponibles.',
'PorOper' => ' por Operador',
'PorPagar' => 'Por Pagar',
'PorProv' => ' por Proveedor',
'ProvDesea' => 'Desea enviar cotizaciones para las refacciones que ' . $agencia . ' está publicando en Quien-Vende.com',
'Proveedor' => 'Proveedor',
'ProvEstat' => 'Proveedor Activo?',
'ProvNvo' => 'Nuevo Proveedor',
'RecSinFactura' => 'Sin factura',
'referencia' => 'Referencia',
'Referencia' => 'Referencia: ',
'Registrar pago' => 'Registro de pago ',
'RepCompSinFech' => 'Completo: Sin Rango de Fechas',
'RepRangoFech' => 'Regresar a Rango de Fechas',
'Seleccione' => 'Seleccione',
'Sin resultados' => 'No se encontraron registros con los datos proporcionados, intente de nuevo por favor.<br>',
'Sin selectores' => 'No se indicó el Proveedor, la factura o la referencia de pago, intente de nuevo por favor.<br>',
'TipoPedido' => 'Tipo de Pedido',
'TodosProv' => 'Todos los Proveedores',
'Total' => 'Total',
'Utilidad' => 'Utilidad',
'VerDatosNvoProv' => 'Ver Datos',
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