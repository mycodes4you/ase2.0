<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Acciones para SOTs | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Acciones para SOTs";

$lang = array(
'Acceso autorizado' => 'Acceso autorizado',
'Acceso NO autorizado' => 'Acceso NO autorizado, ingresar Usuario y Clave correcta.',
'aprobacion de Jefe de Taller' => 'La tarea requiere de aprobacion de un Supervisor o Jefe de Taller',
'coloca el número correcto' => 'Para resolver esto, coloca el número ',
'consulta con Jefe de Taller' => ', consulta con el Jefe de Taller!!',
'CONTINUAR' => 'CONTINUAR',
'Corrija el código de seguimiento' => 'Corrija el código de seguimiento o cambie de Usuario',
'El Operador' => 'El Operador ',
'estás ocupado.' => ' que es en la que estás ocupado.',
'INICIAR APOYO' => 'INICIAR APOYO',
'INICIAR' => 'INICIAR',
'No hacer actualización' => 'No hacer actualización',
'No hubo selección válida' => 'No hubo selección válida',
'NO puede trabajar al mismo tiempo más de una SOT' => ' y NO puede trabajar al mismo tiempo en más de una Tarea',
'No se encontró o no válida la Tarea' => 'No se encontró o no es válida la Tarea ',
'No se indicó la Tarea' => 'No se indicó la Tarea',
'No se puede reparar' => 'Terminada o No está autorizada para reparación.',
'Número de Operador' => 'Número de Operador',
'Número' => 'Número: ',
'OCUPADO en el vehiculo' => ' está OCUPADO en el vehículo ',
'Operador diferente en el código de seguimiento' => 'El Usuario firmado es diferente al asignado a la Tarea.',
'Operador ingresado no concuerda con la tarea' => 'El número de Operador ingresado no concuerda con el asignado a la Tarea',
'Operador no existe o no tiene el código de puesto' => 'El número de Operador ingresado no existe o no tiene el código de puesto apropiado',
'Pasa lectora en ta tarea y presiona ENTER' => 'Pasa la lectora por el código de barras de tu área (hojalatería, pintura, mecánica, etc.) o escribe el número de la tarea en el siguiente campo y después presiona ENTER.',
'Pasa lectora sobre credencial de Operador' => 'Pasa la pistola lectora sobre el código de barras de tu credencial de Operador o escribe el número y después presiona ENTER.',
'pasa tu credencial y pones en pausa o terminar la tarea' => 'en la casilla de abajo y presiona ENTER y después pasa la lectora por el código de barras de tu credencial y podrás poner en pausa o terminar la tarea ',
'PAUSAR' => 'PAUSAR',
'Registro de Avances' => 'Registro de Avances',
'Selecciona acción para la Tarea' => 'Selecciona la acción para la Tarea de ',
'tarea' => ' en la tarea ',
'Tarea marcada como' => 'Esta Tarea fue marcada como',
'Tarea no esta en proceso' => 'La tarea no esta en proceso de reparación',
'TERMINAR APOYO' => 'TERMINAR APOYO',
'TERMINAR' => 'TERMINAR',
'TRABAJO CONJUNTO AL OPERADOR' => 'TRABAJO CONJUNTO AL OPERADOR ',
'' => '',
'' => '',
'' => '',
);
if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para seguimiento */ 
