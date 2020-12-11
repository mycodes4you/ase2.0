<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Destajos";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Administración de Destajos - AutoShop Easy";

$lang = array(
'Acceso autorizado' => 'Acceso autorizado',
'Acceso para Administradores' => 'Acceso sólo para Administradores, ingresar Usuario y Clave correcta',
'Agregar la OT' => 'Agregar la OT: ',
'Area' => 'Area',
'Calculados' => 'Calculados',
'Consumibles' => 'Consumibles',
'Confirme Operador' => 'El Operador al que se pagará el Destajo es: ',
'Costo' => 'Costo',
'del reporte' => ' del reporte ',
'Destajo %' => '% Destajo /',
'Destajo' => 'Destajo',
'Destajos' => 'Destajos: ',
'Diferente Operador' => '<span style="color:red; font-weight:bold;">Diferentes Operadores, seleccione a quién pagará</span>',
'El destajo de' => 'El destajo de ',
'El destajo es mayor al 100% o menor al 0%' => 'El destajo asignado es mayor al 100% o menor al 0%. Revise su selección.',
'en la OT' => ' en la OT ',
'Enviar' => 'Enviar',
'Enviar a Pre-recibo' => 'Enviar selecciones a Pre Recibo',
'Fecha Terminado' => 'Fecha Terminado',
'generado para Operador' => ' generado para Operador ',
'Gestionar Pago Destajos' => 'Gestionar el Pago de Destajos',
'Horas' => 'Horas',
'IVA al 16' => 'IVA al 16%',
'Limpiar Pantalla' => 'Limpiar Pantalla',
'Limpiar selecciones' => 'Limpiar seleccionadas',
'limpiar' => 'limpiar',
'MO' => 'MO',
'NA' => ' N/A',
'No se ha capturado el Costo de Pintura' => 'No se ha capturado el Costo de Pintura',
'no se ha presupuestado MO' => ' no se ha presupuestado mano de obra, no hay destajos por pagar...',
'No se ha Terminado la Tarea' => 'No se ha Terminado la Tarea',
'Operador' => 'Operador:',
'Operador no tiene comisión asignada' => 'El Operador seleccionado no tiene comisión asignada. Revise el perfil del Operador.',
'OT' => 'OT',
'Pago por Pieza' => 'Pago por Pieza',
'Particular' => 'Particular',
'piezas pintadas esta en 0 para la OT' => 'Las piezas pintadas esta en 0 para la OT ',
'Piezas' => 'Piezas',
'Pintura' => 'Pintura',
'Placas' => ' Placas: ',
'Por Calcular' => 'Por Calcular',
'Porcentaje' => 'Porcentaje',
'Promedio por Destajo antes de IVA' => ' Promedio por Destajo antes de IVA: ',
'Recalcular' => 'Recalcular',
'Recibo de destajo' => 'Recibo de destajo ',
'Recibo y Datos / Seleccionar' => 'Recibo y Datos / Seleccionar',
'Regresar a Destajos' => 'Regresar a Destajos',
'Regresar a Listado' => 'Regresar a listado de destajos',
'Regresar a Usuarios' => 'Regresar a Usuarios',
'Seleccion Tareas para Pago de Destajos' => 'Seleccionar Tareas para Pago de Destajos',
'Seleccione las fechas' => 'Seleccione las fechas que desea consultar',
'Seleccione Operador que se pagará' => 'Seleccione al Operador al que se pagará el Destajo:',
'Seleccione' => 'Seleccione...',
'Siniestro' => 'Siniestro',
'SubTotal' => 'SubTotal',
'Todos' => 'Todos',
'Total' => 'Total',
'Vehículo' => 'Vehículo',
'ya había sido agregado al pre-recibo' => ' ya había sido agregado al pre-recibo.','' => '',
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
if(file_exists('particular/textos/destajos.php')) {
	include('particular/textos/destajos.php');
	$lang = array_replace($lang, $langextra);
}
//define('', '');
/* Página de idiomas para vehiculos */ 