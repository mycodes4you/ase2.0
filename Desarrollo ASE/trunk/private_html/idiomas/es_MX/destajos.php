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
'Area' => 'Área',
'Calculados' => 'Calculados',
'Comisión Directa' => 'Comisión Directa',
'Confirme Operador' => 'El Operador al que se pagará el Destajo es: ',
'Consumibles' => 'Consumibles',
'Costo' => 'Costo',
'del reporte' => ' del reporte ',
'Destajo %' => '% Destajo /',
'Destajo' => 'Destajo',
'Destajos' => 'Destajos: ',
'Diferente Operador' => '<span style="color:red; font-weight:bold;">Diferentes Operadores, seleccione a quién pagará</span>',
'El destajo asignado es mayor al 100% o menor al 0%' => 'El destajo asignado es mayor al 100% o menor al 0%. Revise su selección.',
'El destajo de' => 'El destajo de ',
'en la OT' => ' en la OT ',
'Enviar a Pre-recibo' => 'Enviar selecciones a Pre Recibo',
'Enviar' => 'Enviar',
'Fecha Terminado' => 'Fecha Terminado',
'generado para Operador' => ' generado para Operador ',
'Gestionar Pago Destajos' => 'Gestionar el Pago de Destajos',
'Horas' => 'Horas',
'IVA al 16' => 'IVA al ' . ($impuesto_iva * 100) . '%',
'limpiar' => 'limpiar',
'Limpiar Pantalla' => 'Limpiar Pantalla',
'Limpiar selecciones' => 'Limpiar seleccionadas',
'Monto' => 'Monto a<br>Pagar',
'MO' => 'MO',
'NA' => ' N/A',
'No se ha capturado el Costo de Pintura' => 'No se ha capturado el Costo de Pintura',
'no se ha presupuestado MO' => ' no se ha presupuestado mano de obra, no hay destajos por pagar...',
'No se ha Terminado la Tarea' => 'No se ha Terminado la Tarea',
'No en reparación' => 'Aún no está en reparación',
'Operador no tiene comisión asignada' => 'El Operador seleccionado no tiene comisión asignada. Revise el perfil del Operador.',
'Operador' => 'Operador:',
'OT' => 'OT',
'Pago por Pieza' => 'Pago por Pieza',
'Particular' => 'Particular',
'Piezas' => 'Piezas',
'piezas pintadas esta en 0 para la OT' => 'Las piezas pintadas esta en 0 para la OT ',
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
'Seleccione las fechas' => '<span style="color: red; font-size:1.2em; font-weight:bold;">Seleccione las fechas en que fueron Terminadas<br>las Tareas que desea consultar</span>',
'Seleccione Operador que se pagará Destajo' => 'Seleccione al Operador al que se pagará el Destajo:',
'Seleccione Operador que se pagará' => 'Seleccione al Operador al que se pagará el Destajo:',
'Seleccione' => 'Seleccione...',
'Seleccion Tareas para Pago de Destajos' => 'Seleccionar Tareas para Pago de Destajos',
'Siniestro' => 'Siniestro',
'SubTotal' => 'SubTotal',
'TareasSinTerminar' => 'Hay tareas sin terminar',
'Todos' => 'Todos',
'Total' => 'Total',
'Vehículo' => 'Vehículo',
'ya había sido agregado al pre-recibo' => ' ya había sido agregado al pre-recibo.',
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

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para destajos */ 