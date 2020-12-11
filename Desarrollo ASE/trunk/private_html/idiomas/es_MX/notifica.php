<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/

define('EMAIL_AVISO_ASUNTO', 'Su Automóvil está listo.'); /* AutoShop-Easy: su XXX está terminado  */
define('EMAIL_AVISO_SALUDO', 'Estimad@ ');
define('EMAIL_AVISO_CONT1', 'De manera preliminar le informamos que hemos concluido las tareas presupuestadas en la Orden de Trabajo: ');
define('EMAIL_AVISO_CONT2', 'realizadas al vehículo');
define('EMAIL_AVISO_CONT3', 'Nuestro asesor de servicio tiene como tarea contactarle a usted  para acordar fecha y hora en que podría retirar su vehículo, si gusta, puede llamarnos en la primera oportunidad para programar la entrega.');
define('EMAIL_AVISO_CONT4', 'Reciba un cordial saludo.');
define('EMAIL_AVISO_CONT5', '<br>');
// define('', '');
/* Página de idiomas para vehiculos */ 

$lang = array(
'asunto' => 'Su Automóvil está listo en ' . $agencia,
'aviso1' => 'De manera preliminar le informamos que hemos concluido las tareas de reparación autorizadas en su vehículo. En caso de que deba pagar Deducible, le pedimos que traiga su comprobante de pago. Su vehículo:',
'aviso2' => 'Nuestro Asesor de Servicio tiene como tarea contactarle a Usted con para acordar fecha y hora en que podría acudir a retirar su vehículo; si gusta, puede llamarnos en la primera oportunidad para programar la entrega.',
'Cliente sin email' => 'Cliente sin email capturado.',
'despedida' => 'Reciba un cordial saludo.',
'orden' => 'Orden de Trabajo: ',
'placas' => 'Placas: ',
'saludo' => 'Estimad@ ',
'vehiculo' => 'Vehículo: ',
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para comentarios */ 
