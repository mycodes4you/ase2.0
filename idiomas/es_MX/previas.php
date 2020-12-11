<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Registro de Presupuestos Previos de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Registro de Presupuestos Previos de Trabajo";

$lang = array(
'Cliente' => 'Cliente',
'Comentario de Seguimiento' => 'Comentario de Seguimiento',
'comentario es para uso' => 'Indicar si el comentario es para uso:',
'Comentarios OT' => 'Comentarios de la OT: ',
'comentarios uso interno y cliente' => 'Agregar comentarios a la OT para uso interno y para cliente final.',
'cordial saludo' => 'Reciba un cordial saludo.',
'Descripción de Tareas' => 'Descripción de Tareas incluidas en este presupuesto',
'Enviar' => 'Enviar',
'Estatus del Presupuesto' => 'Estatus del Presupuesto',
'Estimad' => 'Estimad@ ',
'Informe sobre Automóvil' => 'Informe sobre Su Automóvil en ',
'Interno' => 'Interno',
'Listado de Presupuestos Previos' => 'Listado de Presupuestos Previos para el ',
'No se han creado tareas' => 'No se han creado tareas para este presupuesto',
'Número de Presupuesto' => 'Número de Presupuesto',
'Placas' => ' Placas: ', 
'Regresar' => 'Regresar',
'Seguimiento' => 'Seguimiento',
'Se ha agreg sig coment OT' => 'Se ha agregado el siguiente comentario a la Orden de Trabajo ',
'Sistema de Administración y Seguimiento, relativo a su vehículo' => 'en nuestro Sistema de Administración y Seguimiento, relativo a su vehículo',
'Ya existe Presupuesto Previo' => 'Ya existe un Presupuesto Previo abierto para el vehículo ',
'Ya hay una OT activa' => 'Ya hay una OT activa, por favor agregue tareas adicionales en lugar de Presupuesto Previo.',
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para comentarios */ 
