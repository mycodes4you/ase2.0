<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Proceso de Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Proceso de Ordenes de Trabajo";

define('GRADO_DIFICULTAD_1', 'Pesado');
define('GRADO_DIFICULTAD_2', 'Mediano');
define('GRADO_DIFICULTAD_3', 'Ligero');

$lang = array(
'Placas' => ' Placas: ',
'Tiempo Extra' => '¿Tiempo Extra? Sí',
'Consumibles' => 'Consumibles',
'Partes' => 'Refacciones',
'MO' => 'Mano de Obra',
'Acceso no autorizado' => 'Acceso sólo para roles de Jefe de Taller o Supervisor de área.',
'Visto' => 'Resuelto',
'Operador' => 'Operador',
'NumSin' => 'Número de Siniestro',
'AjusPrecPres' => 'Ajustar',
'' => '',
'' => '',
);

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

/* Página de idiomas para vehiculos */ 
