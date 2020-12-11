<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
if($accion=='listar') {
	$pagina_actual="Resultados de Búsqueda de Ordenes de Trabajo y Presupuestos Prévios";
} else {
	$pagina_actual="Resumen de Orden de Trabajo";
}

define('CATEGORIA_DE_REPARACION_1', 'Pesado');
define('CATEGORIA_DE_REPARACION_2', 'Urbano');
define('CATEGORIA_DE_REPARACION_3', 'Rápido');
define('CATEGORIA_DE_REPARACION_4', 'Express');

$lang = array(
'Acceso no autorizado' => 'El acceso a esta función no fue autorizado',
'Cambiar a Particular' => 'Cambiar a Particular',
'Cambiar monto de Deducible de' => 'Cambiar monto de Deducible de',
'Cambiar número de Póliza de' => 'Cambiar número de Póliza de',
'Cambiar número de Siniestro de' => 'Cambiar número de Siniestro de',
'Cambio de números' => 'Cambio de números de Siniestro y Póliza de Seguro',
'Cambio de Placas' => 'Cambio de Placas',
'CambDeSiniestroDe', 'Cambio de siniestro de',
'CambFeProm' => 'Cambio de fecha de término de producción de Vehículo a ',
'CambTermProd' => 'Cambio de fecha de término de producción de Vehículo a ',
'Categoría de Servicio' => 'Categoría de Servicio',
'Cobro a Clientes' => 'Cobro a Clientes',
'CotAcept' => 'Aceptar Cotizaciones',
'CotBloq' => 'Bloquear Proveedor',
'CotPosp' => 'Ahora no, quiza después',
'DePoliza' => 'póliza de',
'Devolver refacciones' => 'Favor de devolver refacciones recibidas y cancelar pedidos antes de cambiar de estatus.',
'en progreso' => 'en progreso',
'Estatus Administrativo' => 'Estatus Administrativo',
'Estatus Operativo' => 'Estatus Operativo',
'Garantía' => 'OT a la que aplica la Garantía',
'Grua' => '¿Llegó en Grua?',
'IVAIncluido' => ' IVA Incluido',
'Llegó en Grua' => '¿Llegó en Grua?',
'MasIVA' => '<span style="font-weight:bold;"> + IVA</span>',
'Modificar Datos OT' => 'Modificar datos generales de la OT',
'Modificar Tarea' => 'Modificar datos es esta Tarea',
'Modificar Trabajo' => 'Modifica Siniestro o Trabajo Particular',
'No identificado' => 'No identificado',
'No' => 'No',
'No se encontro' => 'No se encontraron datos suficientes para aplicar el cambio.',
'NumPoliza' => 'Número de Póliza',
'NumSin' => 'Número de Siniestro',
'os1' => 'Particular Con Cita',
'os2' => 'Garantía',
'os3' => 'Particular Sin Cita',
'os4' => 'Siniestro o Convenio',
'Pago de Destajo' => 'Pago de Destajo',
'Pago de pedidos' => 'Pago de pedidos',
'Placa' => 'Placas',
'Placas' => ' Placas: ',
'por iniciar' => 'por iniciar',
'ProvVer' => 'Ver Proveedor',
'Recibido en' => 'Recibido en',
'Ref Estructurales' => 'Ref. Estructurales Pend',
'Ref Pend', 'Refacciones pendientes',
'Registrar cobro de Anticipo' => 'Registrar cobro de Anticipo',
'Reparación' => 'Reparación',
'Seleccionar Asesor de Servicio' => 'Seleccionar Asesor de Servicio',
'Sin Ref Pend' => 'Sin refacciones pendientes',
'Sí' => 'Sí',
'terminada' => 'terminada',
'TermProd' => 'Término de Producción',
'Tipo de Servicio' => 'Tipo de Servicio',
'Torre' => 'Torre',
'Vehículo en Tránsito' => 'Vehículo en Tránsito',
'VerDatosNvoProv' => 'Ver Nuevo Proveedor',
'Visto' => 'Resuelto',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para Ordenes de Trabajo */ 
