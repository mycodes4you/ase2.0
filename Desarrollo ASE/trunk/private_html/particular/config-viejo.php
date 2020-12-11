<?php

$servidor='localhost';
$dbusuario='usuario';
$dbclave='Clave';
$dbnombre='ledisa';
$dbpfx = 'ins_';
$idioma = 'es_MX';
setlocale(LC_ALL, $idioma);
date_default_timezone_set('America/Mexico_City');
define('DIR_DOCS', 'documentos/');
define('INSTANCIA', 'interno');
define('L_LANG', $idioma);
define('ROOT_DOCS', '/home/agustin/Webs/ledisa/documentos/');
$notifica_cliente = 0; // Cambiar a 0 para no notificar automáticamente al cliente automáticamente
$num_almacenes = 9;
$num_areas_servicio = 8;
$adm_docs = 1; // Colocar en 1 para subir hoja de admisión y daños desde Reg-Express
$areapres = array(1,6); // Áreas que al concluir provocan cierre de todas las demás 
$Arr43p87 = 1; // Colocar en 1 para mostrar los teléfonos de contacto en la página de contacto.
$asientos = 1;  // Colocar en 1 para activar el módulo de contabilidad 
$basenumusuarios = 1000;
$bloqueaprecio = 0;
$cambubic = 1; // Habilitar la opción de cambio de ubicación para asesores y jefes de taller
$cierrapres = 1; // Colocar en 1 para forzar el declarar concluido presupuestos de otras áreas cuando terminen array $areapres   
$codigomon = 30; // Código máximo ( de 0 a este) de usuarios que tienen permiso de ver información monetaria.
$compara = 1; // Colocar e 1 para activar la captura de valuación autorizada con fines de comparar solicitados contra autorizados. 
$confcs = 1; // Habilitar la opción de cambio de Categoría de Servicio.
$confolio = 0; // Colocar en 1 para indicar directamente el número de Orden de Trabajo (se debe remover autoincrment para orden_id)
$cotizar = 1; // Colocar en 1 si desea utilizar cotizaciones antes de fincar pedidos.
$ajustacodigo = 1; // Colocar en 1 para habilitar el ajuste de códigos de parte desde la pantalla de refacciones. 
$cotizataller = 0; // Colocar en 1 para habilitar distinción de cotizaciones a cargo de aseguradora o taller
// Datos para solicitar el registro en aseguradora 
$defmarg = 60;
for($i=1; $i<=$num_areas_servicio; $i++) { $destajo[$i] = 0.35;} // Porcentaje de destajo general para todas las áreas de trabajo Aseguradoras.
$destajo[7] = 3; // Porcentaje (0.20 = 20%) o Monto/100 por Pieza completa ( Pintura pieza completa 3 = 300 pesos ) de destajo para un Área especifica.
$destiva = 0; // Cambiar a 1 para agregar el IVA a los recibos de destajo de Operadores.
$destoper = 1; // Cambiar a 1 para habilitar el uso de la comisión definida por usuario en lugar de la general para pago de destajos.
for($i=1; $i<=$num_areas_servicio; $i++) { $destpart[$i] = 0.375;} // Porcentaje de destajo general para todas las áreas de trabajo Particulares.
$destpart[7] = 0.333333; // Porcentaje (0.20 = 20%) o Monto por Pieza completa ( Pintura piesa completa 3 = 300 pesos ) de destajo para un área especifica de Particulares. 
$destpiezas = 0; // Cambiar a 1 para habilitar el pago de destajo de pintura por piezas pintadas
$docingreso = 0; // Colocar en 1 para forzar el registro de documentos y fotos de ingreso
$envcotex = 0; // Colocar en 1 para habilitar el envío de cotización en excel
$envfotoref = 0; // habilita el envío de fotos de refacciones a pedir.
$est_trans = 1; // Cambiar a 1 para habilitar los cambios de estatus para vehiculos en transito.
$extrae_partes = 1; // En 1 habilita el menú de búsqueda de partes de la base de refacciones.
$fact_resumen = 1; // Colocar en 0 para deplegar cada una de las partes, refacciones, consumibles, materiales y mano de obra incluidas en cada tarea a facturar.
$factsinpend = 1; // Cambiar a 1 para únicamente permitir generar facturas sin refacciones pendientes y trabajos terminados.
$fcompcr = 1; // Habilitar fecha Compromiso de Taller
$fpromesa = 1; // Habilitar cambio de fecha Promesa de Entrega
$hoja_ingreso = 'hoja_scuderia.php'; 
$igualador = 0; // Cambiar a 1 para agregar en automático una tarea para Igualadores.
$img_avances = 1;  // Colocar en 1 para habilitar la subida de imagenes de avance de reparacion
$impuesto_iva = 0.16;
$inv_detalle = 1; // Colocar en 1 para habilitar la captura de inventario en línea
$inv_gas = 1; // Colocar en 1 para habilitar la captura de tanque de combustible
$metodo = 'c';  // metodo de asignación de operadores. nada (''), c o d
$metrico = 0; // Colocar en 1 para cambiar el orden metrico a Millas, y 2 para Horas. Dejar en 0 para Kilómetros. 
$moycons = 0; // Colocar en 1 para sumar MO y Consumibles en el cálculo de destajos (Pintura)
$notidedu = 1; // Colocar en 1 para habilitar el envío de notificaciones de Valuación autorizada y deducibles por pagar
$notiase = 1; // Colocar en 1 para habilitar el envío de notificaciones de ingreso  y terminado de vehículo a la Aseguradora
$pac_clave = '123456';
$pac_url = 'http://timbrado.expidetufactura.com.mx:8080/pruebas/TimbradoWS?wsdl';
$pac_usuario = 'pruebas';
$pciva = 0; // Precios de particulares con IVA incluido. Cambiar a 0 para agregar IVA al final.
$perfcr = 1; // Habilitar el reporte de Performance del Centro de Reparación.
$pidepres = 0; // Colocar en 1 para copiar refacciones presupuestadas como autorizadas
$preaut = 1; // Cambiar a 1 para activar Acuerdo de confianza con Aseguradoras y permitir inicio inmediato de reparación.
if(!isset($preciout) || $preciout == '') { $preciout = 120; } // Colocar el precio por default para la hora de trabajo. 
$presolnop = 0; // Colocar en 1 para forzar la subida del presupuesto solicitado.
$provdd = 3; // Días promedio que tardan en surtir refacciones proveedores de aseguradoras 
$pularmado = 0; // Colocar el 1 para agregar tareas de pulido (8) y armado (4) automáticamnte al crear hojalatería.
$ref_pend_email = 'monitor@controldeservicio.com, agustindiazz@yahoo.com.mx'; // Colocar las direcciones de e-mail de quienes recibien los reportes diarios de refacciones pendientes.
$regexpext = 1; // Colocar en 1 para habilitar registros extras en Registro Express (motor, cilindros, etc...)
// $ref_presel = 0;  // Preselecciona todas las refacciones para hacer pedidos y cotizaciones.
$saltapres = 0; // Colocar en 1 para saltar presupuesto y dejar listo para valuación.
$seguimiento = 0; // Cambiar a 1 para solicitar usuario y clave para actualizar estado en proceso 
$sincosto = 0; // Colocar en 1 para permitir recibir partes sin colocar el costo de compra -- No se debe generalizar --
$sin_autorizadas = 0; // Colocar en 1 para no hacer seguimiento de refacciones autorizadas, sólo pedidas.
$soloautorizadas = 0; // Colocar en 1 para no mostrar refacciones presupuestadas en la gestión de refacciones.
$solocomseg = 1; // Cambiar a 1 para desplegar solo los comentarios de seguimiento en la página de monitoreo.
$soloref = 0; // Cambiar a 1 para excluir mano de obra en impresión e SOT para operadores.
$sotsindesc = 0; // Cambiar a 1 para excluir mano de obra y refacciones en impresión e SOT para operadores.
$tipotaller = 'TALLER';
$todoscomseg = 1; // Colocar a 1 para sólo mostrar comentarios de seguimiento en monitoreo.
$ubicaciones = array('Taller', 'Anexo 1', 'Anexo 2'); // Diversas ubicaciones preestablecidas, especialmente fuera del taller y diferente de Transito, la primera debe ser "Taller"
$urlpub = 'http://demo.autoshop-easy.com/';  // Página para Clientes
$usuauthcom = array(1000,1001); // Usuarios autorizados a enviar comentarios a clientes.  
$valida_accesos = 1; // Cambiar a 1 para activar el control de accesos por Usuario.  
$vehad = 0;  // Colocar en 1 para habilitar características adicionales de vehículos.
$ver00a = 0; // Colocar en 1 para evitar verificación de subida o llena de campos candado.

$nombre_agencia = 'Mi Prueba de Centro de Reparación, SA de C.V.';
$zona = 'DF';
$agencia_calle = 'Progreso Tecnológico';
$agencia_colonia = 'Santiago';
$agencia_cp = '13300';
$agencia_email = 'contacto@autoshop-easy.com';
$agencia_estado = 'Distrito Federal';
$agencia_folio_final = '100	';
$agencia_folio_inicial = '1';
$agencia_lugar_emision = 'Un lugar cerca del cielo.';
$agencia_metodo_pago = 'No identificado';
$agencia ='Mi Centro de Reparación Automotriz';
$agencia_municipio = 'Tlahuac';
$agencia_numext = '13';
$agencia_numint = '21';
$agencia_pais = 'México';
$agencia_razon_social = 'AGUSTIN DIAZ ZAMORA';
$agencia_referencia = 'A 100 metros del Metro Zapotitlán';
$agencia_regimen = 'Incorporacion Fiscal';
$agencia_rfc = 'DIZA650617DX4';
$agencia_serie = 'PRUEBA';
$agencia_telefonos = '(55) 8421-3307.';
$agencia_tipo_pago = 'Una sola exhibición';
$agencia_direccion = 'Av. ' . $agencia_calle . '. #' . $agencia_numext . ' - ' . $agencia_numint;
$agencia_firma = $agencia_razon_social."\n".$agencia_direccion."\n".$agencia_colonia.", ".$agencia_municipio."\n".$agencia_cp.". ".$agencia_estado."\n".$agencia_email."\n".$agencia_telefonos."\n";
define('REC_PROV_BANCO', 'Mi Banco');  
define('REC_PROV_CUENTA', '00000');  
define('REC_RH_BANCO', 'Mi Banco');
define('REC_RH_CUENTA', '00000');
$simulador = 0; // Colocar en 1 para cambiar el color del fondo para hacer notar que se está en una instancia no productiva.

/* Archivo de configuración de acceso a BD */