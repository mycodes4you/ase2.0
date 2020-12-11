<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/

define('EMAIL_AVISO_ASUNTO', 'Avance de reparación en su vehículo en ' . $agencia); 
define('EMAIL_AVISO_SALUDO', 'Estimad@ ');
$cont1 = 'Le informamos que hemos concluido los trabajos del área de ' . constant('NOMBRE_AREA_' . $sub['sub_area']) . ' correspondientes a '; 
if($sub['sub_reporte'] != '0' && $sub['sub_reporte'] != '') {
	$cont1 .= 'el siniestro ' . $sub['sub_reporte'];
} else {
	$cont1 .= 'el trabajo Particular';
}
$cont1 .= ' de la Orden de Trabajo: ';
define('EMAIL_AVISO_CONT1', $cont1);
define('EMAIL_AVISO_CONT2', 'realizadas al vehículo');
define('EMAIL_AVISO_CONT3', 'Este mensaje automático de avance de reparación refrenda nuestro compromiso para atenderle con la calidad y oportunidad que Usted merece. Una vez que concluyamos la totalidad de los trabajos nuestro Asesor de Servicio se comunicará con Usted para acordar la cita de entrega de su vehículo.');
define('EMAIL_AVISO_CONT4', 'Reciba un cordial saludo.');
define('EMAIL_AVISO_CONT5', '<br>');
$vaicop_bcc = 'monitoreo@controldeservicio.com';
$lang = [
    "saludo" => "Estimado (a): ",
    "aviso1" => "De manera preliminar le informamos que hemos concluido las tareas presupuestadas en el vehículo: ",
    "orden"  => "Orden de trabajo:",
    "marca"  => "Marca:",
	"placas" => "Placas:",
	"aviso2" => "Nuestro asesor de servicio tiene como tarea contactarle a usted para acordar fecha y hora en que podría recoger su vehículo; si gusta, puede llamarnos en la primera oportunidad para programar la entrega.",
	"aviso3" => "Reciba un cordial saludo.",
	"asunto" => "Su Automóvil está listo.",
];
// define('', '');
/* Página de idiomas para entrega - Notificación de Tareas Terminadas */ 
