<?php

$servidor='localhost';
$dbusuario='autoshop';
$dbclave='6tb1f10c';
$dbnombre='scuderia';
$dbpfx = 'scu_';
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
$provdd=3; // Días promedio que tardan en surtir refacciones proveedores de aseguradoras 
// Datos para solicitar el registro en aseguradora 
$tipotaller = 'TALLER';
$zona = 'MEX';
$agencia = 'SCUDERIA S3';
$agencia_email = 'contacto@scuderia-s3.com.mx';
$nombre_agencia = 'Scudería Ese3, SA de C.V.';
$agencia_telefonos = 'Tel. (55) 1674-4514.';
$agencia_razon_social = 'Scudería Ese3, SA de C.V.';
$agencia_rfc = 'SES080410M17';
$agencia_regimen = 'Persona Moral del Régimen General';
$agencia_direccion = 'Av. División del Norte #3281-A';
$agencia_colonia = 'Candelaria';
$agencia_municipio = 'Coyoacán';
$agencia_estado = 'Distrito Federal';
$agencia_cp = '04380';
$agencia_pais = 'México';
$agencia_lugar_emision = 'Coyoacán.<br>Distrito Federal.';
$agencia_firma = $agencia_razon_social."\n".$agencia_direccion."\n".$agencia_colonia.", ".$agencia_municipio."\n".$agencia_cp.". ".$agencia_estado."\n".$agencia_email."\n".$agencia_telefonos."\n";
$agencia_tipo_pago = 'Una sola exhibición';
$agencia_metodo_pago = '';
$agencia_cbb = 'imagenes/cbb.png';
$agencia_sicofi = '';
$agencia_folio_inicial = '';
$agencia_folio_final = '	';
$agencia_serie = '';
$agencia_fecha_aprobacion = '';
$fact_resumen = 1; // Colocar en 0 para deplegar cada una de las partes, refacciones, 
// consumibles, materiales y mano de obra incluidas en cada tarea a facturar.



/* Archivo de configuración de acceso a BD */