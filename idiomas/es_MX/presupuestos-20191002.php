<?php
/*  Ajusta los textos de acuerdo a tu idioma  */
$titulo="Administración de Presupuestos | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Presupuestos de Ordenes de Trabajo";

$lang = array(
'Acceso No Autorizado' => 'Su usuario no tiene permisos para ejecutar esta acción.',
'AgregaCONSUMIBLE' => 'Agrega un CONSUMIBLE o MATERIAL por renglón:',
'AgregaMO' => 'Agrega la MANO DE OBRA, un trabajo por renglón',
'AgregaREFACCION' => 'Agrega una REFACCION por renglón:',
'AjusPrecPres' => 'Ajustar',
'Cantidad' => 'Cantidad',
'Concepto' => 'Trabajo o Pieza a cambiar o reparar',
'Consumible' => 'Consumible',
'CONSUMIBLES' => 'CONSUMIBLES',
'Descripcion' => 'Reparación de ',
'descrip_mecanica' => 'Revisión de posibles daños mecánicos por colisión.',
'EncabezadoDirectoHPM' => 'Agrega Refacciones, Productos, Materiales y Mano de Obra a presupuestar para la Reparación:',
'EnPesos' => 'en Pesos',
'EnUTs' => 'en UTs',
'Factura' => 'Factura',
'Garantía' => 'Garantía',
'Imagen escaneada' => 'Imagen escaneada en JPG de la',
'InstCons' => 'Cantidad, Descripción y Precio Unitario al Cliente (Precio Público, si no sabes el precio coloca un # en lugar del precio)',
'InstMO' => 'Descripción y Precio Total al Público de cada trabajo: (si no sabes el precio coloca un # en lugar del precio)',
'InstRef' => 'Cantidad, Descripción y Precio Unitario al Cliente (Precio Público, si no sabes el precio coloca un # en lugar del precio)',
'Instrucciones' => 'Instrucciones:',
'Interno' => 'Interno',
'MANOOBRA' => 'MANO DE OBRA',
'MO' => 'MO',
'MO Cambiar' => 'Mano de Obra por Cambiar',
'Modificar Datos Tarea' => 'Modificar Datos Generales da la Tarea',
'MO Pintar' => 'Mano de Obra por Pintar',
'MO Reparar' => 'Mano de Obra por Reparar',
'nomdocadmin' => 'Orden de Admisión',
'nomdocrep' => 'Hoja de Daños',
'NumPoliza' => 'Número de Póliza',
'NumSin' => 'Número de Siniestro',
'NumSinAseg' => 'Número de reporte de aseguradora',
'NumSinVacio' => 'El número de reporte no puede estar vacío. Si es Particular coloque 0',
'Pedido' => 'Pedido',
'Placas' => ' Placas: ',
'PorcenMatPint' => '<br>% Materiales',
'Precio Pintar' => 'Precio por Pintar',
'PrecioUT' => 'Precio de Hora de Trabajo',
'Recibo' => 'Recibo',
'Refacción' => 'Refacción',
'REFACCIONES' => 'REFACCIONES',
'Rines' => 'Rines',
'Severidad OT' => 'Severidad General de la Reparación: ', 
'Visto' => 'Resuelto',
'' => '',
'' => '',
'' => '',
'' => '',
);

$lang['Severidad'][1] = 'Express';
$lang['Severidad'][2] = 'Leve';
$lang['Severidad'][3] = 'Media';
$lang['Severidad'][4] = 'Fuerte';


$ayuda = [
	'Item' => 'Indica el orden en el que fueron agregados los conceptos, si este se
				sombrea color <b class="rojo_tenue">"rojo"</b> significa que fue asociado a la tarea pero no se definió
				un área, por lo que debe ser seleccionado para ser movido a donde corresponda.',
	'Mover' => 'Selecciona los items que desees mover de tarea, condicones:<br>
				<b>1</b> La tarea no debe de tener destajos pagados en el caso de mano de Obra.<br>
				<b>2</b> La tarea no debe de haber sido facturada (mano de obra y refacciones).<br>
				<b>3</b> La tarea no debe de tener conceptos de descuento (mano de obra y refacciones).<br>',
	'Tipo_p' => 'Se mostrará un ícono para informar el tipo de pedido con el que se gestionó el Item.
				<img src="idiomas/' . $idioma . '/imagenes/tipopedido-1.png" border="0" width="20" height="20"> <b>A cargo de aseguradora</b>.
				<img src="idiomas/' . $idioma . '/imagenes/tipopedido-2.png" border="0" width="20" height="20"> <b>Contado</b>.
				<img src="idiomas/' . $idioma . '/imagenes/tipopedido-3.png" border="0" width="20" height="20"> <b>Crédito</b>.'
];

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para presupuestos */ 
