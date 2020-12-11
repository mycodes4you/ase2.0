<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Comentarios en Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Comentarios en Ordenes de Trabajo";

define('COM_TIPO_0', 'Interno');
define('COM_TIPO_1', 'Cliente');
define('COM_TIPO_2', 'Seguimiento');
define('COM_TIPO_3', 'Mensaje a Usuario');
define('COM_TIPO_5', 'Vigilancia');
// define('COM_TIPO_', '');

$lang = array(
'com_tipo_0' => 'Comentario General',
'com_tipo_1' => 'Enviar Correo al Cliente',
'com_tipo_2' => 'Registrar llamada al Cliente',
'com_tipo_3' => 'Mensaje a Usuario',
'com_tipo_5' => 'Vigilancia',
'Cliente' => 'Cliente',
'Comentario de Seguimiento' => 'Comentario de Seguimiento',
'comentario es para uso' => 'Indicar si el comentario es para uso:',
'Comentarios OT' => 'Comentarios de la OT: ',
'comentarios OT interno y cliente' => 'Agregar comentarios a la OT para uso interno y para cliente final.',
'cordial saludo' => 'Reciba un cordial saludo.',
'Enviar' => 'Enviar',
'Estimad' => 'Estimad@ ',
'Informe sobre Automóvil' => 'Informe sobre Su Automóvil en ',
'Interno' => 'Interno',
'Placas' => ' Placas: ', 
'Regresar' => 'Regresar',
'Seguimiento' => 'Seguimiento',
'Se ha agreg sig coment OT' => 'Se ha agregado el siguiente comentario a la Orden de Trabajo ',
'Sistema de Administración y Seguimiento, relativo a su vehículo' => 'en nuestro Sistema de Administración y Seguimiento, relativo a su vehículo',
'asunto' => 'Informe sobre Su Automóvil en ' . $agencia,
'saludo' => 'Estimad@ ',
'aviso1' => 'Hemos agregado el siguiente comentario en nuestro Sistema de Administración y Seguimiento respecto a la reparación de su vehículo:',
'aviso2' => '<strong>Comentario: ' . $motivo . '.</strong>',
'despedida' => 'Reciba un cordial saludo.',
'orden' => 'Orden de Trabajo: ',
'vehiculo' => 'Vehículo: ',
'placas' => 'Placas: ',
'' => '',
'' => '',
);

$llamada_tipo = array(
'bienvenida' => 10,
'veredicto_rep' => 20,
'avance_rep' => 30,
'termino_rep' => 40,
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para comentarios */ 
