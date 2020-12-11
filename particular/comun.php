<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
define('ORDEN_SERVICIO_1','Con Cita');
define('ORDEN_SERVICIO_2','Garantía');
define('ORDEN_SERVICIO_3','Sin Cita');
define('ORDEN_SERVICIO_4','Siniestro');

define('CATEGORIA_DE_REPARACION_2', 'Tradicional');
define('CATEGORIA_DE_REPARACION_1', 'Daño Fuerte');
define('CATEGORIA_DE_REPARACION_3', 'Daño Leve');
//define('CATEGORIA_DE_REPARACION_4', 'Express');

define('UNIDAD_0','Kilometros');
define('UNIDAD_1','Millas');
define('UNIDAD_2','Horas');
define('ZONA_DE_ESPERA','Zona de Espera');

define('ALARMA_', ''); // para resultados nulos
define('ALARMA_0', '<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Normal" title="Normal">');
define('ALARMA_1', '<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Rezagado" title="Rezagado">');
define('ALARMA_2', '<img src="idiomas/' . $idioma . '/imagenes/alerta-critica.png" alt="Crítico" title="Crítico">');
define('ALARMA_3', '<img src="idiomas/' . $idioma . '/imagenes/alerta-refacciones.png" alt="Refacciones" title="Refacciones">');
define('ALARMA_4', '<img src="idiomas/' . $idioma . '/imagenes/alerta-pago-perdida.png" alt="No reparar" title="No reparar">');
define('ALARMA_5', '<img src="idiomas/' . $idioma . '/imagenes/alerta-ref-estruc.png" alt="Estructurales Completas" title="Estructurales Completas">');

/***************  Nombres de Áreas ***********************/

define('NOMBRE_AREA_', ' ');
define('NOMBRE_AREA_1', 'Mecánica');
define('NOMBRE_AREA_2', 'Accesorios');
define('NOMBRE_AREA_3', 'Area 3');
define('NOMBRE_AREA_4', 'Area 4');
define('NOMBRE_AREA_5', 'Area 5');
define('NOMBRE_AREA_6', 'Hojalatería');
define('NOMBRE_AREA_7', 'Pintura');
define('NOMBRE_AREA_8', 'No Definido');
define('NOMBRE_AREA_9', 'No Definido');
define('NOMBRE_AREA_10', 'No Definido');

/***************  Nombres de Almacenes ***********************/

$nom_almacen = array(
 '1' => 'Aceite',
 '2' => 'Afinación',
 '3' => 'Frenos',
 '4' => 'MO Tracto',
 '5' => 'MO Cajas',
 '6' => 'Hojalatería',
 '7' => 'Pintura',
 '8' => 'Varios',
 '9' => 'No Definido',
 '10' => 'No Definido');


define('NOMBRE_ALMACEN_1', 'Aceite');
define('NOMBRE_ALMACEN_2', 'Afinación');
define('NOMBRE_ALMACEN_3', 'Frenos');
define('NOMBRE_ALMACEN_4', 'MO Tractos');
define('NOMBRE_ALMACEN_5', 'MO Cajas');
define('NOMBRE_ALMACEN_6', 'Hojalatería');
define('NOMBRE_ALMACEN_7', 'Pintura');
define('NOMBRE_ALMACEN_8', 'Varios');
define('NOMBRE_ALMACEN_9', 'No Definido');
define('NOMBRE_ALMACEN_10', 'No Definido');

/***************  Inventario de Ingreso ***********************/
define('INV_ING_0', 'Antena');
define('INV_ING_1', 'Tapones');
define('INV_ING_2', 'Encendedor');
define('INV_ING_3', 'Espejos');
define('INV_ING_4', 'Tapón de Gasolina');
define('INV_ING_5', 'Cables Corriente');
define('INV_ING_6', 'Rines');
define('INV_ING_7', 'Tapetes');
define('INV_ING_8', 'Llanta Refacción');
define('INV_ING_9', 'Herramientas');
define('INV_ING_10', 'Reflejantes');
define('INV_ING_11', 'Extinguidor');
define('INV_ING_12', 'Radio');
define('INV_ING_13', 'Gato');
define('INV_ING_14', 'Vestiduras');
define('INV_ING_15', 'Cristales');
define('INV_ING_16', 'Objetos de Valor');

/***************  ordenes.php ***********************/
define('REGISTRO_ORDEN_TRABAJO','Registro de Orden de Trabajo');
define('ASESOR_ASIGNADO', 'Asesor');
define('TIPO_DE_SERVICIO', 'Tipo de Servicio:');
define('CATEGORIA_DE_SERVICIO', 'Categoría:');
define('IMPORTE_DE_REPARACION', 'Presupuesto Total');
define('IMPORTE_DE_PAGO_30', 'Pago de Daños');
define('IMPORTE_DE_PAGO_31', 'Pérdida Total');
define('IMPORTE_DE_PAGO_32', 'Pago Plus');
define('REFACCIONES_PENDIENTES', 'Refacciones pendientes');
define('REFACCIONES_ESTRUCTURALES', 'Ref. Indispensables Pend.');

/***************  vehiculos.php ***********************/
define('DATOS_VEHICULO', 'Datos del vehículo');
define('VEHICULO', 'Vehículo');
define('PLACAS', 'Placas');
define('SERIE', 'Serie');
define('NUMERO_MOTOR', '# Motor');
define('NUM_MOTOR', 'Número de Motor');
define('TIPO_MOTOR', 'Tipo de Motor');
define('CILINDROS', 'Cilindros');
define('LITROS', 'Cilindrada');
define('FABRICANTE', 'Marca');
define('TIPO', 'Tipo');
define('SUBTIPO', 'Subtipo');
define('YEAR', 'Año');
define('PUERTAS', 'Puertas');
define('COLOR', 'Color');
define('ASEGURADORA', 'Aseguradora');
define('POLIZA', 'Póliza');
define('UNO_PARA_BUSCAR', 'Se necesita al menos un dato para buscar.');
define('ACCIONES', 'Acciones');

define('ETIQUETA_DETALLES', 'Detalles');
define('ETIQUETA_LISTAR_OT', 'Listar OTs');
define('ETIQUETA_NUEVA_OT', 'Crear Nueva OT');
define('ETIQUETA_VER_DOCUMENTOS', 'Ver Documentos');

/***************  Pedidos ***********************/
define('TIPO_PEDIDO_1', 'A cargo de Aseguradora');
define('TIPO_PEDIDO_2', 'Compra Directa a Crédito');
define('TIPO_PEDIDO_3', 'Compra Directa de Contado');
define('TIPO_PEDIDO_4', 'Revisión Precio de Venta');
define('TIPO_PEDIDO_5', 'Subir fotos de refacciones');
define('TIPO_PEDIDO_6', 'Guardar Costos de refacciones');
define('TIPO_PEDIDO_10', 'Cotizar');
define('TIPO_PEDIDO_11', 'Cotizar para Taller');


$opcpago = 7;
define('TIPO_PAGO_1', 'Efectivo');
define('TIPO_PAGO_2', 'Cheque');
define('TIPO_PAGO_3', 'Transferencia');
define('TIPO_PAGO_4', 'Nota de Crédito');
define('TIPO_PAGO_5', 'Anticipo');
define('TIPO_PAGO_6', 'Tarjeta de Crédito');
define('TIPO_PAGO_7', 'Tarjeta de Débito');

/***************  Cobros ***********************/
define('REC_CLI_BANCO', '');
define('REC_CLI_CUENTA', '');

/***************  Refacciones ***********************/
define('GRADO_DIFICULTAD_1', 'Pesado');
define('GRADO_DIFICULTAD_2', 'Mediano');
define('GRADO_DIFICULTAD_3', 'Ligero');

define('EMAIL_TEXT_TITULO', 'Pedido de Refacciones');
define('EMAIL_TEXT_DESCRIPCION', 'Por medio de la presente solicitamos nos hagan el favor de SURTIR el siguiente PEDIDO de refacciones. Por favor confirme la fecha promesa de entrega para cada parte.  En caso de no recibir una o varias de las refacciones dentro de la fecha promesa de entrega, nuestro sistema cancelará automáticamente el pedido de dichas refacciones.');
define('EMAIL_TEXT_COTIZACION', 'Por medio de la presente solicitamos nos hagan el favor de cotizar el siguiente listado de refacciones incluyendo existencias en planta y backorders.  Le agradeceremos que responda esta solicitud dentro de las siguientes 4 horas hábiles.');
define('EMAIL_PROVEEDOR_FROM', 'nr-aaa@mg.notifica-ase.com.mx');
define('EMAIL_PROVEEDOR_RESPONDER', 'rsamano@autoclinic.com.mx');
define('EMAIL_PROVEEDOR_CC', 'rsamano@autoclinic.com.mx, refacciones.autoclinic@gmail.com');
define('TEXTO_AUTOSURTIDO', 'Centro de Reparación - Crédito.');
define('JEFE_DE_ALMACEN', 'ROSA ELENA SAMANO');
define('TELEFONOS_ALMACEN', '(55) 5611-3210');
define('EMAIL_DE_ALMACEN', 'rsamano@autoclinic.com.mx');

$smtphost = 'smtp.mailgun.org';
$smtpusuario = 'nr-aaa@mg.notifica-ase.com.mx';
$smtpclave = 'Jyreod59xpe3pgds81nd9s';
$smtppuerto = '587';

/***************  Refacciones Pendientes ***********************/
define('EMAIL_PARTES_ASUNTO', 'Recordatorio de Partes Pendientes.');
define('EMAIL_PARTES_SALUDO', 'Estimad@ ');
define('EMAIL_PARTES_CONT1', 'Por la presente le informamos que a la fecha no nos han sido entregadas las partes, refacciones o productos que se detallan a continuación.  De antemano le agradecemos su amable atención para que nos envíen los siguientes productos pendientes: ');
define("EMAIL_PARTES_CONT2", "Quedamos pendientes y a la orden para cualquier aclaración o comentario.<br><br>");
define("EMAIL_PARTES_CONT3", "Atentamente.<br>Agustín Díaz.<br>AutoShop Easy<br><br>");

//define('', '');

/* Página de idiomas para estatus */ 
$num_tipos = 4; // Número de Tipos de Servicio
