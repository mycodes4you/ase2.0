<?php

$servidor='localhost';
$dbusuario='usuario';
$dbclave='Clave';
$dbnombre='sepsa';
$dbpfx = 'sep_';
$valida_accesos = 1; // Cambiar a 1 para activar el control de accesos por Usuario.
$urlpub = 'http://sepsa.autoshop-easy.com/';
$inv_detalle = 0; // Colocar en 1 para habilitar la captura de inventario en l�nea
$inv_gas = 0; // Colocar en 1 para habilitar la captura de tanque de combustible
$seguimiento = 0; // Cambiar a 1 para solicitar usuario y clave para actualizar estado en proceso 
$solocomseg = 1; // Cambiar a 1 para desplegar solo los comentarios de seguimiento en la p�gina de monitoreo.
$notifica_cliente = 0; // Cambiar a 0 para no notificar automáticamente al cliente automáticamente
$idioma = 'es_MX';
setlocale(LC_ALL, $idioma);
define('L_LANG', $idioma);
date_default_timezone_set('America/Mexico_City');
define('DIR_DOCS', 'documentos/');
$Arr43p87=1;
$metodo='c';  // metodo de asignación de operadores. nada (''), c o d
$num_areas_servicio = 8;
$num_almacenes = 12;
$impuesto_iva = 0.16;
$defmarg = 60;
$img_avances = 1;  // Colocar en 1 para habilitar la subida de imagenes de avance de reparacion
$adm_docs = 0; // Colocar en 1 para subir hoja de admisi�n y da�os desde Reg-Express
$cotizar = 1; // Colocar en 1 si desea utilizar cotizaciones antes de fincar pedidos.
$ver00a = 0; // Colocar en 1 para evitar verificación de subida o llena de campos candado.
$saltapres = 0; // Colocar en 1 para saltar presupuesto y dejar listo para valuación.
$compara = 0; // Colocar e 1 para activar la captura de valuación autorizada con fines de comparar solicitados contra autorizados. 
$confolio = 0; // Colocar en 1 para indicar directamente el número de Orden de Trabajo y remover autoincrment para orden_id
$usuauthcom = array(1000,1001); // Usuarios autorizados a enviar comentarios a clientes.  
$todoscomseg = 1; // Colocar a 1 para s�lo mostrar comentarios de seguimiento en monitoreo.
$factsinpend = 1; // Cambiar a 1 para únicamente permitir generar facturas sin refacciones pendientes y trabajos terminados.
$soloref = 0; // Cambiar a 1 para excluir mano de obra en impresión e SOT para operadores. 
$est_trans = 1; // Cambiar a 1 para habilitar los cambios de estatus para vehiculos en transito.
$sotsindesc = 0; // Cambiar a 1 para excluir mano de obra y refacciones en impresión e SOT para operadores.
$preaut = 0; // Cambiar a 1 para activar Acuerdo de confianza con Aseguradoras y permitir inicio inmediato de reparación.
$igualador = 1; // Cambiar a 1 para agregar en automático una tarea para Igualadores.
$pularmado = 0; // Colocar el 1 para agregar tareas de pulido (8) y armado (4) autom�ticamnte al crear hojalater�a.
$hoja_ingreso = 'particular/hoja_sepsa.php'; 
$deschi = 1; // Cambiar a 1 para agregar las descripciones de las tareas a hoja de ingreso.
$preexistentes = 'particular/dibujos-de-autos.png';
$provdd=3; // Días promedio que tardan en surtir refacciones proveedores de aseguradoras 
$preciout = '';
if($preciout == '') { $preciout = 180; } // Colocar precio default para MO.
$pciva = 1; // Precios de particulares con IVA incluido. Cambiar a 0 para agregar IVA al final.
for($i=1; $i<=$num_areas_servicio; $i++) { $destajo[$i] = 0.35;} // Porcentaje de destajo general para todas las área de trabajo Aseguradoras.
$destajo[7] = 0.50; // Porcentaje de destajo para un área especifica.
for($i=1; $i<=$num_areas_servicio; $i++) { $destpart[$i] = 0.375;} // Porcentaje de destajo general para todas las área de trabajo Particulares.
$destpart[7] = 0.33333333; // Porcentaje de destajo para un área especifica de Particulares.
$destpiezas = 0;// Cambiar a 1 para habilitar el pago de destajo de pintura por piezas pintadas
$fcompcr = 1; // Habilitar fecha Compromiso de Taller
$bloqueaprecio = 0;
// Datos para solicitar el registro en aseguradora 
$basenumusuarios = 2885;
$extrae_partes = 0; // En 1 habilita el menú de búsqueda de partes de la base de refacciones.
$moycons = 0; // Colocar en 1 para sumar MO y Consumibles en el cálculo de destajos (Pintura)
$ajustadores = 0; // Colocar en 1 para registrar a los ajustadores y habilitar reporte.
define('REC_RH_BANCO', 'Mi Banco');
define('REC_RH_CUENTA', '00000');
define('REC_PROV_BANCO', 'Mi Banco');  
define('REC_PROV_CUENTA', '00000');  
$tipotaller = 'TALLER';
$zona = 'MEX';
$agencia ='SEPSA';
$agencia_email = 'sepsa1@prodigy.net.mx';
$nombre_agencia = 'SEPSA';
$agencia_telefonos = '(55) 5884-1827';
$agencia_razon_social = 'Servicios de Equipo Pesado, S.A. de C.V.';
$agencia_rfc = 'SEP000921EZ5';
$agencia_regimen = 'Persona Moral del Régimen General';
$agencia_calle = 'Av. José López Portillo';
$agencia_numext = '222';
$agencia_numint = '';
$agencia_direccion = $agencia_calle . '. #' . $agencia_numext;
$agencia_colonia = 'Bello Horizonte';
$agencia_municipio = 'Tultitlán';
$agencia_estado = 'Estado de México';
$agencia_cp = '54948';
$agencia_pais = 'México';
$agencia_lugar_emision = 'Tultitlán, Estado de México';
$agencia_referencia = 'Sin Referencia';
$agencia_firma = $agencia."\n".$agencia_direccion."\n".$agencia_colonia.", ".$agencia_municipio."\n".$agencia_cp.". ".$agencia_estado."\n".$agencia_email."\n".$agencia_telefonos."\n";
$agencia_tipo_pago = 'Una sola exhibición';
$agencia_metodo_pago = '';
$agencia_folio_inicial = '0';
$agencia_folio_final = '100	';
$agencia_serie = 'A';
$pac_url = 'http://timbrado.expidetufactura.com.mx:8080/pruebas/TimbradoWS?wsdl';
$pac_usuario = 'pruebas';
$pac_clave = '123456';
// $pac_url = 'http://timbrado.expidetufactura.com.mx:8080/produccion/TimbradoWS?wsdl';
// $pac_usuario = '';
// $pac_clave = '';
$fact_resumen = 1; // Colocar en 0 para deplegar cada una de las partes, refacciones, consumibles, materiales y mano de obra incluidas en cada tarea a facturar.
$ref_pend_email = 'monitoreo@controldeservicio.com'; // Colocar las direcciones de e-mail de quienes recibien los reportes diarios de refacciones pendientes.
$asientos = 0;  // Colocar en 1 para activar el módulo de contabilidad 
$vehad = 0;  // Colocar en 1 para habilitar características adicionales de vehículos.
$envfotoref = 1; // habilita el envío de fotos de refacciones a pedir.
$envcotex = 0; // Colocar en 1 para habilitar el envío de cotización  en excel
$cotizataller = 0; // Colocar en 1 para habilitar distinción de cotizaciones a cargo de aseguradora o taller
$notidedu = 0; // Colocar en 1 para habilitar el envío de notificaciones de Valuación autorizada y deducibles por pagar
$notiase = 1; // Colocar en 1 para habilitar el envío de notificaciones de ingreso  y terminado de vehículo a la Aseguradora
$perfcr = 0; // Habilitar el reporte de Performance del Centro de Reparación.
$cambubic = 1; // Habilitar la opción de cambio de ubicación para asesores y jefes de taller
$ubicaciones = array('Taller', 'Anexo 1', 'Anexo 2'); // Diversas ubicaciones preestablecidas, especialmente fuera del taller y diferente de Transito 
$fpromesa = 1; // Habilitar cambio de fecha Promesa de Entrega
$docingreso = 0; // Colocar en 1 para forzar el registro de documentos y fotos de ingreso
$pidepres = 0; // Colocar en 1 para copiar refacciones presupuestadas como autorizadas
$cierrapres = 1; // Colocar en 1 para forzar el declarar concluido presupuestos de otras áreas cuando terminen array $areapres   
$areapres = array(1,6); // Áreas que al concluir provocan cierre de todas las demás 
$codigomon = 80; // Código máximo ( de 0 a este) de usuarios que tienen permiso de ver información monetaria.
$sincosto = 0; // Colocar en 1 para permitir recibir partes sin colocar el costo de compra -- No se debe generalizar --


/* Archivo de configuración de acceso a BD */
