<?php

$servidor='localhost';
$dbusuario='usuario';
$dbclave='Clave';
$dbnombre='ledisa';
$dbpfx = 'ins_';
$seguimiento = 0; // Cambiar a 1 para solicitar usuario y clave para actualizar estado en proceso 
$notifica_cliente = 0; // Cambiar a 0 para no notificar automáticamente al cliente automáticamente
$idioma = 'es_MX';
setlocale(LC_ALL, $idioma);
define('L_LANG', $idioma);
date_default_timezone_set('America/Mexico_City');
define('DIR_DOCS', 'documentos/');
define('ROOT_DOCS', '/home/agustin/Webs/ledisa/documentos/');
$Arr43p87=1;
$metodo='c';  // metodo de asignación de operadores. nada (''), c o d
$num_areas_servicio = 8;
$num_almacenes = 9;
$impuesto_iva = 0.16;
$defmarg = 60;
$cotizar = 0; // Colocar en 1 si desea utilizar cotizaciones antes de fincar pedidos.
$ver00a = 0; // Colocar en 1 para evitar verificación de subida o llena de campos candado.
$saltapres = 0; // Colocar en 1 para saltar presupuesto y dejar listo para valuación.
$provdd=3; // Días promedio que tardan en surtir refacciones proveedores de aseguradoras 
// Datos para solicitar el registro en aseguradora 
$tipotaller = 'TALLER';
$zona = 'MEX';
$agencia = 'CARBAND';
$agencia_email = 'carband@carband.com.mx';
$nombre_agencia = 'Excelencia en Laminados, S. de R.L. de C.V.';
$agencia_telefonos = 'Tel. (55) 5915-1199.';
$agencia_razon_social = 'Excelencia en Laminados, S. de R.L. de C.V.';
$agencia_rfc = 'EEL1204202X9';
$agencia_regimen = 'Persona Moral del Régimen General';
$agencia_direccion = 'Francisco Barreda #11-A.';
$agencia_colonia = 'San Juan Bosco';
$agencia_municipio = 'Atizapán de Zaragoza';
$agencia_estado = 'Estado de México';
$agencia_cp = '52946';
$agencia_pais = 'México';
$agencia_lugar_emision = 'Atizapán de Zaragoza.<br>Estado de México.';
$agencia_firma = $agencia_razon_social."\n".$agencia_direccion."\n".$agencia_colonia.", ".$agencia_municipio."\n".$agencia_cp.". ".$agencia_estado."\n".$agencia_email."\n".$agencia_telefonos."\n";
$agencia_tipo_pago = 'Una sola exhibición';
$agencia_metodo_pago = '';
$agencia_cbb = 'imagenes/cbb.png';
$agencia_sicofi = '23944346';
$agencia_folio_inicial = '10';
$agencia_folio_final = '200	';
$agencia_serie = 'AAS';
$agencia_fecha_aprobacion = '26/09/2012';
$fact_resumen = 1; // Colocar en 0 para deplegar cada una de las partes, refacciones, 
// consumibles, materiales y mano de obra incluidas en cada tarea a facturar.



/* Archivo de configuración de acceso a BD */