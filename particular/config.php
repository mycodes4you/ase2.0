<?php

$servidor='localhost';
$dbusuario='root';
$dbclave='';
///$servidor='mycodes4you.com';
///$dbusuario='as67Wy6';
///$dbclave='92@uy5Kv';
$dbnombre='admin_autoshop';
$dbpfx = 'saier_';
$instancia = 'entrenamiento'; // Subdominio que identifica al cliente
//$nick = strtoupper(substr($dbpfx, 0, -1)); // Identificador de Comprador para Quien Vende?
$nick = 'entrenamiento';
$idioma = 'es_MX';
setlocale(LC_ALL, $idioma);
date_default_timezone_set('America/Mexico_City');
define('DIR_DOCS', 'documentos/');
define('INSTANCIA', 'entrenamiento');
define('L_LANG', $idioma);
$num_areas_servicio = 8;
//------------------

$abreotforz = 0; // Colocar en 1 para forzar que sólo se puedan reabrir OTs cerradas con permiso de usuario
$adm_docs = 1; // Colocar en 1 para subir hoja de admisión desde Reg-Express
$adm_docid = 1;  // Colocar en 1 para verificar con JavaScript subir hoja de daños o identificación oficial desde Reg-Express
$ajuprecpres = 1; // Colocar en 1 para permitir al cotizador colocar precios de venta en presupuestadas
$ajustacodigo = 0; // Colocar en 1 para habilitar el ajuste de códigos de parte desde la pantalla de refacciones.
$ajustadores = 0; // Colocar en 1 para registrar a los ajustadores y habilitar reporte. 
$arciase = 0; // Colocar en 1 para enviar archivo de cierre a Aseguradora (útil para enviar encuestas de satisfacción, por ejemplo).
$areapres = array(1,6,7); // Áreas que al concluir provocan cierre de todas las demás 
$Arr43p87=1; // Colocar en 1 para mostrar los teléfonos de contacto en la página de contacto.
$asecerrado = 0; // Colocar en 1 para no mostrar la ventana de nuevo registro de vehículos.
$asesorpi = 0; // Colocar en 0 para mostrar a los Asesores únicamente sus Ordenes de Trabajo y en 1 para mostrar todas las OTs.  
$avanmagua = 0; // Colocar en 1 para habilitar marcas de agua en avance de repararción.
$basenumusuarios = 2969;
$bloqueaprecio = 0;
$cancelacfdi = 0; // Habilita la cancelación directa al SAT de facturas emitidas desde ASE 
$cambubic = 1; // Habilitar la opción de cambio de ubicación para asesores y jefes de taller
$cierrapres = 1; // Colocar en 1 para forzar el declarar concluido presupuestos de otras áreas cuando terminen array $areapres   
$codigomon = 30; // Código máximo ( de 0 a este) de usuarios que tienen permiso de ver información monetaria.
$compara = 0; // Colocar e 1 para activar la captura de valuación autorizada con fines de comparar solicitados contra autorizados. 
$confcs = 1; // Habilitar la opción de cambio de Categoría de Servicio.
$confolio = 0; // Colocar en 1 para indicar directamente el número de Orden de Trabajo (se debe remover autoincrment para orden_id)

// --- Contabilidad --
$asientos = 1;  // Colocar en 1 para activar el módulo de contabilidad 
$cont_aux_prov = [
	'1' => [
		'pedidos.php,proveedores.php',
		'Cuenta de Contado',
	],
]; // 1 al 3, al agregarlo lo habilita. Este parametro no debe cambiar una vez iniciados los asientos.
$cont_aux_aseg = [
	'1' => [
		'entrega.php,aseguradoras.php,personal.php',
		'Cuenta de Resultados',
	],
]; // 1 al 3, al agregarlo lo habilita. Este parametro no debe cambiar una vez iniciados los asientos.
$ase[0]['cc'] = '1150-002-099-000-00'; // -- Cuenta contable y auxiliares para público en general
$ase[0]['ccaux1'] = '4100-001-099-000-00';
$ase[0]['ccaux2'] = '';
$ase[0]['ccaux3'] = '';

// --------------------------------------------

$cotizar = 1; // Colocar en 1 si desea utilizar cotizaciones antes de fincar pedidos.
$cotizadirecto = 1; // Cambiar a cotización directa en consumibles cuando está habilitada la cotización múltiple
$cotsimnoaut = 0; // Colocar en 1 para deshabilitar cotizacion multiple en aseguradoreas sin autosurtido
$cotizataller = 0; // Colocar en 1 para habilitar distinción de cotizaciones a cargo de aseguradora o taller
$defmarg = 60;
$deschi = 1; // Colocar en 1 para mostrar las descripciones de tareas 1,6 y 7 en el cuadro de observaciones de hoja de ingreso.

// ------ Configuración de Destajos
for($i=1; $i<=$num_areas_servicio; $i++) { $destajo[$i] = 0;} // Porcentaje de destajo general para todas las áreas de trabajo Aseguradoras.
//$destajo[6] = -0.50; // Porcentaje (0.20 = 20%) o Monto*100 por Pieza completa ( Pintura pieza completa 3 = 300 pesos ) de destajo para un Área especifica.
//$destajo[7] = 0.4; // Porcentaje (0.20 = 20%) o Monto*100 por Pieza completa ( Pintura pieza completa 3 = 300 pesos ) de destajo para un Área especifica.
for($i=1; $i<=$num_areas_servicio; $i++) { $destpart[$i] = 0;} // Porcentaje de destajo general para todas las áreas de trabajo Particulares.
//$destpart[6] = -0.50; // Porcentaje (0.20 = 20%) o Monto por Pieza completa ( Pintura piesa completa 3 = 300 pesos ) de destajo para un área especifica de Particulares. 
$desdiacorte = 3; // Día de la semana para corte de destajo 1 = Lunes, 6 = Sábado, 0 = Domingo.
$destiva = 0; // Cambiar a 1 para agregar el IVA a los recibos de destajo de Operadores.
$destsinterm = 0; // Cambiar a 1 para permitir calculo de destajo en estatus 105, 106, 111 y 121.
$destcomdir = 1; // Permitir el pago directo de comisiones cuando no hay mano de obra para calcular destajo.
$destoper = 0; // Cambiar a 1 para habilitar el uso de la comisión definida por usuario en lugar de la general para pago de destajos.
$destpiezas = 0; // Cambiar a 1 para habilitar el pago de destajo de pintura por piezas pintadas
$moycons = 0; // Colocar en 1 para sumar MO y Consumibles en el cálculo de destajos (Pintura)
// --------------------------------------------

$docingreso = 1; // Colocar en 1 para forzar el registro de documentos y fotos de ingreso
$envcotex = 0; // Colocar en 1 para habilitar el envío de cotización en excel
$envfotoref = 1; // habilita el envío de fotos de refacciones a pedir.
$estanciamax = 12; // Número de días para alarmar como excedida la estancia máxima en Taller
$est_trans = 1; // Cambiar a 1 para habilitar los cambios de estatus para vehiculos en transito.
$extrae_partes = 0; // En 1 habilita el menú de búsqueda de partes de la base de refacciones.
$fact_resumen = 1; // Colocar en 0 para deplegar cada una de las partes, refacciones, consumibles, materiales y mano de obra incluidas en cada tarea a facturar.
$factsinpend = 0; // Cambiar a 1 para únicamente permitir generar facturas sin refacciones pendientes y trabajos terminados.
$fechapassword = 60; // Colocar el número de días para cambio de password en general para todos los usuarios
$fcompcr = 1; // Habilitar fecha Compromiso de Taller
$fltprodcod = 0; // Cambiar a 1 para habiliar a filtrado por código de parte las refacciones en el reporte de Finanzas
$fpromesa = 1; // Habilitar cambio de fecha Promesa de Entrega
$gestxarea = 0; // Colocar en 1 para habiltar la gestión de refacciones limitada a las áreas configuradas en el perfil del usuario. 
$grua_reg = 1; // Forzar la verificación de ingreso en Grua.
$hoja_ingreso = 'particular/hoja_ingreso.php'; 
$igualador = 0; // Cambiar a 1 para agregar en automático una tarea para Igualadores.
$img_avances = 1;  // Colocar en 1 para habilitar la subida de imagenes de avance de reparacion
$impuesto_iva = 0.16;
$inv_detalle = 1; // Colocar en 1 para habilitar la captura de inventario en línea
$inv_gas = 1; // Colocar en 1 para habilitar la captura de tanque de combustible
$margutil = 40; // El color del fondo de la celda que muestra la utilidad de una OT o Reporte cambiará cuando sea menor a este porcentaje
$mecanica = 0; // Colocar en 1 para agregar tareas de mecanica (1) automáticamnte al crear hojalatería.
$mensjint = 1; // Colocar en 1 para habilitar la mensajeria interusuarios.
$metodo='c';  // metodo de asignación de operadores. nada (''), c o d
$metrico = 0; // Colocar en 1 para cambiar el orden metrico a Millas, y 2 para Horas. Dejar en 0 para Kilómetros. 
$multiorden = 0; // Colocar en 1 para permitir varias OTs activas para el mismo vehículo 
$notiase = 0; // Colocar en 1 para habilitar el envío de notificaciones de ingreso  y terminado de vehículo a la Aseguradora
$notiavances = 0; // Colocar en 1 para envío de correo a clientes avisando de nueva imagen de Avance de Reparación. 
$noticantarea = 0; // Colocar en 1 para notificar a gerencia la cancelación de Tareas Particulares.
$notidedu = 1; // Colocar en 1 para habilitar el envío de notificaciones de Valuación autorizada y deducibles por pagar
$notifica_cliente = 0; // Cambiar a 0 para no notificar automáticamente al cliente automáticamente
$notifica_bienvenida = 0; //Colocar en 1 para enviar notificación de bienvenida al cliente.
$notiprovvis = 0; // Colocar en 1 para enviar en modo visible las direcciones de los proveedores a cotizar
$notisupase = 0; // Colocar en 1 para habilitar el envío de notificaciones a supervisor de Aseguradora en terminado de tareas y OT
$notifica_tareas = array(1 => 0, 6 => 0, 7 => 0); // Cambiar a 1 el area correspondiente para notificar al cliente la tarea terminada esa area 
$num_almacenes = 9;
$ordiniregllamcli = 0; // Colocar la OT a patír de la que se realizará registro de llamadas al cliente. Dejar en 0 para desactivar función.

// ------ Control de Prepago ------------
$ordprepago = 1; // Colocar en 1 para activar el modo Prepago de OTs.
$ordinicred = 1; // Número inicial de OT para prepago.
$ordcomppre = 6429; // Número de OTs incluidas en la última compra de creditos +500+600+180+180-5+300+25+100
$ordprecred = 120; // OTs acreditables del periodo anterior.
$ordpreavis = 25; // Fija el mínimo de OTs antes de enviar la alerta de Prepago
$ordpretari = 1; // Colocar en 1 para Tarifa de producto 1 ($30 por OT), colocar en 0 para tarifa de producto 16 ($40 por OT) 
// --------------------------------------


// -----------  Productivo SW  -------------
//$pac_prov = 'pac-smart.php';
//$pac_clave = 'Gfz6mpdRsn7b';
//$pac_usuario = 'autoclinic-mixcoac@autoshop-easy.com';
//$pac_url_33 = 'https://services.sw.com.mx/cfdi33/stamp/json/v1/b64';  // Versión 3.3 cURL
//$pac_autentica = 'https://services.sw.com.mx/security/authenticate';

// ----------- Pruebas SW ------------------
$pac_prov = 'pac-prueba.php';
$pac_clave = '123456789';
$pac_url_33 = 'http://services.test.sw.com.mx';  // Versión 3.3
$pac_url = 'https://pruebascfdi.smartweb.com.mx/Timbrado/wsTimbrado.asmx?WSDL';
$pac_autentica = 'https://pruebascfdi.smartweb.com.mx/Autenticacion/wsAutenticacion.asmx?WSDL';
$pac_usuario = 'demo';

// ------ Pruebas XPD ------------
//$pac_prov = 'pac-xpd.php';
//$pac_clave = '12345678a';
//$pac_url_33 = 'https://pruebastimbrado.expidetufactura.com.mx:8443/preproduccion/TimbradoWS?wsdl';
//$pac_usuario = 'pruebas';


$particpres = 0; // En la creación de nuevas tareas particulares dentro de OTs avanzadas, colocar en 1 para enviarlas a 124 Por presupuestar o dejar en 0 para enviarlas a 102 Valuado.
$pciva = 0; // Dejar en 0 para precios de particulares con IVA incluido. Cambiar a 1 para precios + IVA.
$perfcr = 1; // Habilitar el reporte de Performance del Centro de Reparación.
$pedpfx = 0; // Colocar en 1 para habilitar la modificación de asunto y $bcc por aseguradora en archivos pedpfx-Aseguradora en parciales
$pidepres = 0; // Colocar en 1 para copiar refacciones presupuestadas como autorizadas
$preaut = 1; // Cambiar a 1 para activar Acuerdo de confianza con Aseguradoras y permitir inicio inmediato de reparación.
if(!isset($preciout) || $preciout == '') { $preciout = 120; } // Colocar el precio por default para la hora de trabajo.
$prechat = 0.2; // Porcentaje del costo de una refacción nueva para calcular el precio de Chatarra 
$presolnop = 1; // Colocar en 1 para forzar la subida del presupuesto solicitado.
$provdd=3; // Días promedio que tardan en surtir refacciones proveedores de aseguradoras 
$pularmado = 0; // Colocar el 1 para agregar tareas de pulido (8) y armado (4) automáticamnte al crear hojalatería.

// ------ QuienVende! -------------------
$qv_activo = 0; // Colocar en 1 para activar comunicación con Quien Vende!
// --------------------------------------

$ref_pend_email = 'monitor@controldeservicio.com, agustindiazz@yahoo.com.mx'; // Colocar las direcciones de e-mail de quienes recibien los reportes diarios de refacciones pendientes.
$refhorz = 0; // Colocar en 1 para mostrar apiladas horizontalmente la gestión de refacciones presupuesto y autorizadas 
$regexpext = 0; // Colocar en 1 para habilitar registros extras en Registro Express (motor, cilindros, etc...)
$regfefact = 0; // Colocar en 1 para forzar registro de fecha de recepción y programación de pago en facturas a clientes.
// $ref_presel = 0;  // Preselecciona todas las refacciones para hacer pedidos y cotizaciones.
$saltapres = 0; // Colocar en 1 para saltar presupuesto y dejar listo para valuación.
$seguimiento = 0; // Cambiar a 1 para solicitar usuario y clave para actualizar estado en proceso 
$sincosto = 0; // Colocar en 1 para permitir recibir partes sin colocar el costo de compra -- No se debe generalizar --
$solocomseg = 1; // Cambiar a 1 para desplegar solo los comentarios de seguimiento en la página de monitoreo.
$soloref = 1; // Cambiar a 1 para excluir mano de obra en impresión e SOT para operadores.
$solovalacc = 0; // Cambiar a 1 para deshabilitar los permisos por rol y dejar activos únicamente los permisos por usuario.
$sotsindesc = 0; // Cambiar a 1 para excluir mano de obra y refacciones en impresión e SOT para operadores.
$tipotaller = 'TALLER';
$todoscomseg = 1; // Colocar a 1 para  mostrar comentarios de seguimiento en monitoreo.
$ubicaciones = array('Taller', 'Transito', 'Anexo 1', 'Anexo 2'); // Diversas ubicaciones preestablecidas, especialmente fuera del taller y diferente de Transito, la primera debe ser "Taller"
$urlpub = 'http://' . $instancia  . '.autoshop-easy.com';  // Página para Clientes
$usuauthcom = array(701,706,1000,1001); // Usuarios autorizados a enviar comentarios a clientes.  
$usr_sup_asesores = 1000; // Usuario que recibirá los mensajes de llamadas no realizadas 
$valautcap = 0; // Habilita la captura de fecha de autorización de Valuación por la aseguradora.   
$valida_accesos = 1; // Cambiar a 1 para activar el control de accesos por Usuario.  
$vehad = 0;  // Colocar en 1 para habilitar características adicionales de vehículos.
$ver00a = 0; // Colocar en 1 para evitar verificación de subida o llena de campos candado.
$verifica_ses = 0; // Colocar en 1 para verificar si ya existe una sesión activa (Util para usuarios externos)

// ------ Datos especificos del CRA ---------------

$zona = 'DF';
$tipotaller = 'TALLER';
$nombre_agencia = 'Desarrollo ASE';
$agencia ='Desarrollo ASE';
$agencia_email = 'agustin.diaz@controldeservicio.com';
$agencia_telefonos = '55-8421-3307';

// ------ Pruebas de emisión de CFDI ---------------------
$agencia_razon_social = 'CINDEMEX SA DE CV';
$agencia_regimen = 'General de Personas Morales';
$agencia_reg33 = '601';
$agencia_rfc = 'CACX7605101P8';

//$agencia_razon_social = 'Vaicop, SAS DE CV';
//$agencia_reg33 = '601';
//$agencia_regimen = 'Regimen General para Personas Morales';
//$agencia_rfc = 'VAI1902083P1';

$agencia_calle = 'Heriberto Frias';
$agencia_numext = '1439';
$agencia_numint = '402';
$agencia_colonia = 'Del Valle';
$agencia_cp = '03100';
$agencia_municipio = 'Benito Juárez';
$agencia_estado = 'Cuidad de México';
$agencia_pais = 'México';
$agencia_referencia = '';
$agencia_lugar_emision = '';
$agencia_direccion = 'Calle ' . $agencia_calle . '. #' . $agencia_numext;
$agencia_firma = $agencia_razon_social."\n".$agencia_direccion."\n".$agencia_colonia.", ".$agencia_municipio."\n".$agencia_cp.". ".$agencia_estado."\n".$agencia_email."\n".$agencia_telefonos."\n";

define('REC_PROV_BANCO', 'Mi Banco');  
define('REC_PROV_CUENTA', '00000');  
define('REC_RH_BANCO', 'Mi Banco');
define('REC_RH_CUENTA', '00000');

$Rfcs[0] = [$agencia_rfc,$agencia_razon_social,$agencia_reg33,$agencia_cp];
/* Archivo de configuración de acceso a BD */
