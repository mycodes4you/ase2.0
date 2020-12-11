<?php

$servidor='localhost';
$dbusuario='usuario';
$dbclave='Clave';
$dbnombre='agnrnlt';
$dbpfx = 'tlalne_';
$seguimiento = 0; // Cambiar a 1 para solicitar usuario y clave para actualizar estado en proceso 
$notifica_cliente = 0; // Cambiar a 0 para no notificar automáticamente al cliente cuando se han terminado todas las tareas de su OT
$idioma = 'es_MX';
setlocale(LC_ALL, $idioma);
define('L_LANG', $idioma);
date_default_timezone_set('America/Mexico_City');
define('DIR_DOCS', 'documentos/');
define('ROOT_DOCS', '/home/agustin/Webs/e-taller.mex3.com/documentos/');
$Arr43p87=1;
$metodo='c';
$preciout=10;
$provdd=3; // Días promedio que tardan en surtir refacciones provedores de aseguradoras 
// Datos para solicitar el registro en aseguradora 
$tipotaller = 'AGENCIA';
$zona = 'MEX';
$agencia = 'TLALNEPANTLA';
$agencia_email = 'contacto@autoshop-easy.com.mx';
$nombre_agencia = 'ProfreAuto, S.A. de C.V.';
$agencia_telefonos = 'Tel. (55) 5646-5702.';

/* Archivo de configuración de acceso a BD */