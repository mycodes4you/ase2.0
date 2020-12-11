-- Cambios a bases de datos.

-- Cambio del 20200107
-- Requerido para identificar que pedidos fueron gestionados a traves de Quien Vende
ALTER TABLE `ins_pedidos` ADD `pedido_qv` INT NULL DEFAULT NULL ;
-- Cambio no aplicado


-- Cambio del 20191121
-- Requerido para subir de forma automática los documentos de pago de pedidos desde QV --
ALTER TABLE `ins_facturas_por_pagar` ADD `f_doc_pdf` VARCHAR(256) NULL DEFAULT NULL , ADD `f_doc_xml` VARCHAR(256) NULL DEFAULT NULL ;
ALTER TABLE `ins_prod_prov` ADD `prod_foto_prov` VARCHAR(256) NULL DEFAULT NULL ;
ALTER TABLE `ins_pedidos` ADD `pedido_doc_cr` VARCHAR(256) NULL DEFAULT NULL ;
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('DiaRevFact', NULL, NULL, '0', 'Día de la semana en que se revisan facturas de proveedores');
-- Cambio no aplicado

-- Cambio del 20191113.
-- Se crea la tabla prov_contactos con sus campos correspndientes
-- Area de contactos para proveedores
CREATE TABLE IF NOT EXISTS `ins_prov_contactos` ( 
  `contacto_id` INT(11) NOT NULL AUTO_INCREMENT , 
  `contacto_nombre` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , 
  `contacto_puesto` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL , 
  `contacto_cbancaria` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL , 
  `contacto_CLABE` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL , 
  `contacto_telefono` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL , 
  `contacto_movil` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL , 
  `contacto_email` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL , 
  `contacto_proveedor` INT(11) NOT NULL , 
  `contacto_activo` TINYINT(1) NOT NULL , 
  `contacto_notas` TEXT NULL , 
  PRIMARY KEY (`contacto_id`)) ENGINE = InnoDB; 

-- Cambio del 20191113.
-- Se agregan campos para los datos del contacto representante de proveedor
ALTER TABLE `ins_proveedores`  
ADD `prov_rep1_puesto` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL  AFTER `prov_email`,
ADD `prov_rep1_cbancaria` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL  AFTER `prov_rep1_puesto`,
ADD `prov_rep1_clabe` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL  AFTER `prov_rep1_cbancaria`;


--Cambio del 20191113.
--Correccion, se agrega ">" faltante
UPDATE `ins_acciones` SET `accion_descripcion` = 'cancelar.png\" alt=\"Cancelar Orden de Trabajo\" title=\"Cancelar Orden de Trabajo\">' WHERE `ins_acciones`.`accion_url` = 'ordenes.php?accion=cancelar&orden_id=';
--Cambio de imagen carta promesa axa
UPDATE `ins_acciones` SET `accion_descripcion` = 'axa2.png\" alt=\"Carta Promesa de Entrega\" title=\"Carta Promesa de Entrega\">' WHERE `ins_acciones`.`accion_url` = 'ingreso.php?accion=cartaaxa&orden_id='
--en ASEBASE Variable para habilitar el menu responsivo de acciones en ordenes.php
INSERT INTO `valores` (`val_id`, `val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES (NULL, 'AccionesResponsivas', '1', NULL, '0', 'Habilita el ménu responsivo para las acciones en ordenes.php'); 
-- Aplicado el 20200119 -- ADZ --


-- Cambio del 20191107 --
-- Requerido por AutoClinic para evitar que cualquier usuario pueda agregar tareas después de el estatus 1 Descripción de daños --
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('SinAgregarTareas', '0', NULL, '0', 'Colocar a 1 para remover de las acciones la acción Agregar Tarea');
-- Cambio no aplicado


-- Cambio del 20191029 --
-- Ajuste de columna para guardar datos de comisiones no ligadas a OTs --
ALTER TABLE `ins_comisiones` CHANGE `indicador` `indicador` VARCHAR(64) NULL DEFAULT NULL;
ALTER TABLE `ins_comisiones` CHANGE `com_id` `com_id` INT(11) NOT NULL AUTO_INCREMENT;
-- Cambio no aplicado


-- Cambio del 20191027 --
-- Requerido para el acomodo apropiado de las comisiones en los recibos de destajo.
ALTER TABLE `ins_destajos_elementos` CHANGE `area` `area` TINYINT(2) NULL DEFAULT NULL, CHANGE `costcons` `costcons` DOUBLE NULL DEFAULT NULL, CHANGE `comision` `comision` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
-- Cambio no aplicado


-- Cambio del 20191023 --
-- Se agregó la descripción de la comisión pagada para permitir usar los recibos de pago para otras comisiones diferetes de destajos de operadores
ALTER TABLE `ins_destajos_elementos` ADD `comision` VARCHAR(128) NULL DEFAULT NULL ;

DROP TABLE IF EXISTS `ins_comisiones_tipo`;
CREATE TABLE IF NOT EXISTS `ins_comisiones_tipo` (
`com_id` int(11) NOT NULL AUTO_INCREMENT,
  `com_nombre` varchar(64) DEFAULT NULL,
  `com_desc` text,
  `com_archivo` varchar(64) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `ins_comisiones_tipo`
 ADD PRIMARY KEY (`com_id`);

ALTER TABLE `ins_comisiones_tipo`
MODIFY `com_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

-- Cambio no aplicado


-- Cambio del 20191017 --
-- Requerido para atender necesidad de registrar con que RFC se emitió la factura en donde se utiliz más de un RFC ---
ALTER TABLE `ins_facturas_por_cobrar` ADD `fact_rfc_emisor` VARCHAR(13) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `aseguradora_id`;
ALTER TABLE `ins_facturas_por_cobrar` CHANGE `fact_rfc` `fact_rfc_receptor` VARCHAR(13) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
-- Cambio no aplicado


-- Cambio del 20191014 --
-- Requerido por SAI para sus solicitudes de presupuestos a Mapfre con tres precios sugeridos de venta a partir de las cotizaciones más altas recibidas de sus proveedores --
INSERT INTO `ins_valores` (`val_id`, `val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES (NULL, 'MargMapf', '0', NULL, '0', 'Porcentaje del Margen de utilidad coloquial sobre el costo de refacciones.');



-- Cambio del 20190614 ---
-- Requerido por atender a Alpha para alojar diversos identioficadores de unidades de sus clientes--
ALTER TABLE `ins_vehiculos` ADD `vehiculo_eco` VARCHAR(30) NULL DEFAULT NULL AFTER `vehiculo_serie`;
ALTER TABLE `ins_vehiculos` CHANGE `vehiculo_aseguradora` `vehiculo_aseguradora` INT(11) NULL DEFAULT NULL;
ALTER TABLE `ins_vehiculos` DROP `antena`, DROP `tapones`, DROP `encendedor`, DROP `espejo`, DROP `tgas`, DROP `cables`, DROP `rines`, DROP `tapetes`, DROP `llanta`, DROP `herramientas`, DROP `reflejantes`, DROP `extinguidor`, DROP `estereo`, DROP `gato`, DROP `vestiduras`, DROP `cristales`, DROP `objvalor`;
ALTER TABLE `ins_vehiculos` CHANGE `obs` `vehiculo_obs` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `ins_vehiculos` ADD `vehiculo_encierro` VARCHAR(128) NULL DEFAULT NULL AFTER `vehiculo_poliza`, ADD `vehiculo_ciudad` VARCHAR(64) NULL DEFAULT NULL AFTER `vehiculo_encierro`, ADD `vehiculo_grupo` VARCHAR(64) NULL DEFAULT NULL AFTER `vehiculo_ciudad`, ADD `vehiculo_tipo_servicio` VARCHAR(32) NULL DEFAULT NULL AFTER `vehiculo_grupo`;
-- Cambio no aplicado

-- Cambio del 20190612 ---
-- Requerido por Kater, Sarsan, Keiken y Alpha para identificar ingreso de unidades en diferentes localidades ---
INSERT INTO `ins_valores` (`val_id`, `val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES (NULL, 'multisitio', '1', NULL, '0', 'Habilita con numérico en 1 la identificación de Sitios cuando hay más de 1 en CDRs');
ALTER TABLE `ins_aseguradoras` ADD `aseguradora_activa` TINYINT(1) NULL DEFAULT '1' AFTER `aseguradora_rfc`;
-- Cambio no aplicado


-- Cambio del 2019-05-24 ---
-- Este cambio Agrega una columna en la tabla de productos para configurar si el producto debe cotizarse dentro de quin vende --
ALTER TABLE `ins_productos` ADD `prod_qv` TINYINT NULL AFTER `prod_prov_id`;
-- Cambio no aplicado

-- Cambio del 2019-05-24 --
-- Este cambio Agrega una columna en la tabla de productos para guardar la cantidad a a cotizar en automático una vez que el producto baja a su mínimo --
ALTER TABLE `ins_productos` ADD `prod_cant_cotizar` INT(4) NULL AFTER `prod_resurtir`;
-- Cambio no aplicado

-- Cambio del 2019-05-24 --
-- Este cambió agrega una Nueva tabla para tener control de los adeudos y los pendientes de entragar en tareas generadas por paquetes de servicio ---
-- Estructura de tabla para la tabla `ins_prods_pendientes`
CREATE TABLE `ins_prods_pendientes` (
  `prods_pendiente_id` int(11) NOT NULL,
  `prod_id` int(11) DEFAULT NULL,
  `prods_pendiente_requeridos` int(11) DEFAULT NULL,
  `prods_pendiente_surtidos` int(11) DEFAULT NULL,
  `prods_pendiente_entregados` int(11) DEFAULT NULL,
  `prods_pendiente_adeudos` double DEFAULT NULL,
  `op_id` int(11) DEFAULT NULL,
  `sub_orden_id` int(11) DEFAULT NULL,
  `orden_id` int(11) DEFAULT NULL,
  `prods_pendiente_fecha` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Indices de la tabla `ins_prods_pendientes`

ALTER TABLE `ins_prods_pendientes` ADD PRIMARY KEY (`prods_pendiente_id`);

-- AUTO_INCREMENT de la tabla `ins_prods_pendientes`
ALTER TABLE `ins_prods_pendientes` MODIFY `prods_pendiente_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
-- Cambio no aplicado


-- Cambio del 2019-05-24 --
-- Este cambió agrega una columna a la tabla subordenes para marcar si la tarea pertenece a un paquete de servicio ---
ALTER TABLE `ins_subordenes` ADD `sub_paquete_id` INT(4) NULL AFTER `sub_refacciones_recibidas`;
-- Cambio no aplicado

-- Cambio del 20190607
-- Requerido para permitir múltiples instalaciones físicas (talleres) de una misma instancia 
ALTER TABLE `ins_ordenes` ADD `orden_sitio_ingreso` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `orden_metrico`, ADD `orden_sitio_actual` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `orden_sitio_ingreso`;
-- Cambio no aplicado

-- Cambio del 20190427
-- Con este convenio se solucionará la separación de Garantías de otros trabajos particulaes
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('ConvenioGarantia', NULL, NULL, '0', 'ID del convenio que se utiliza para separar las Garantías de Particulares');
INSERT INTO `ins_aseguradoras` (`aseguradora_razon_social`, `aseguradora_nic`, `aseguradora_logo`, `aseguradora_rfc`, `aseguradora_addenda`, `aseguradora_descuento`, `aseguradora_calle`, `aseguradora_ext`, `aseguradora_int`, `aseguradora_colonia`, `aseguradora_municipio`, `aseguradora_estado`, `aseguradora_pais`, `aseguradora_cp`, `aseguradora_representante`, `aseguradora_telefono`, `aseguradora_email`, `aseguradora_v_email`, `aseguradora_alta`, `autosurtido`, `prov_def`, `prov_dde`, `preciout`, `calc_pintura`, `area_ut`, `omite_notificaciones`, `omite_inventario`, `algoritmo_pres`, `algoritmo_autorizados`, `omite_datos_pdf`, `aseguradora_saltapres`, `cuenta_contable`, `base_destajo`) VALUES
('GARANTIAS', 'GARANTIAS', 'particular/logo-garantias.png', 'AAA010101999', NULL, 0, 'Por Definir', 'Por Definir', 'Por Definir', 'Por Definir', 'Por Definir', 'Ciudad de México', 'México', '99999', 'Por Definir', 'Por Definir', 'Por Definir', '0', 0, 0, 0, 3, 120.00, 1, '0|0|0|0|0|0|0|0|0|0', NULL, NULL, 'parciales/directo-hpm.php', 'parciales/directo-hpm.php', NULL, 0, NULL, NULL);


-- Cambio del 20190402
-- Utilizada para verificar si todas las tareas de una OT están terminadas para proceder al pago de destajos. 
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('DestSubTerm', '0', NULL, '0', 'Variable que se usa en caso de que se deba verificar si todas la tareas de una OT están Terminadas para habilitar el pago de destajos');

-- Cambio del 20190219
-- Marca la casilla si, por default en el inventario detallado
INSERT INTO `ins_valores` (`val_id`, `val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES (NULL, 'llena_inv', '0', NULL, '0', 'Marca la casilla si, por default en el inventario detallado');

-- Cambio del 20181205
-- Se agrega la opción de cambiar la codificación de los correos electrónicos enviados desde PHPMailer
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('mailcodificacion', NULL, '8bit', '0', 'Ajusta la codificación de correos enviados por PHP Mailer. Los valores pueden ser 8bit, 7bit, binary, base64 y quoted-printable. Por default es 8bit.');
-- Cambio aplicado el 20181205


-- Cambio del 20181023
-- Se introduce la posibilidad de controlar el envío de cotizaciones y pedidos de refacciones Sólo Por QV
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('EnvSoloQV', NULL, NULL, '0', 'Colocar a 1 para sólo enviar a través de QV pedidos y requerimientos de cotización y dejar de enviar correo electrónico.');
-- Cambio no aplicado 


-- Cambio del 20181011 pero con origen antiguo....
-- Con la finalidad de que los gerentes se den cuenta de trabajos cerrados sin valuación.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('ValidaValTrab', '0', NULL, '0', 'Verifica que todos los trabajos tengan valuación, si no, regresa las tareas al estatus 103 y la OT a 3.');


-- Cambio del 20180928
-- Requerido para mostrar RPE en el reporte de facturación .
ALTER TABLE `ins_cobros` ADD `rpe_id` INT(11) NULL AFTER `fact_id`;
-- Cambio aplicado 20180928


-- Cambio del 20180924
-- Requerido por SMS para no verificar el monto de MO de cambio cuando colocan precio a refacciones ya que en la mayoría no cobran por cambio.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('NoVerficMOCamb', '0', NULL, '0', 'Con Metodo de valuacion Directo HPM, colocar en 1 para omitir la verificación de MO de Cambio para refacciones.');
-- Cambio aplicado 20180924


-- Cambio del 20180923
-- Requerido para estandarizar nombre de Marcas y modelos de Vehículos.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('UsarMarcas', '0', NULL, '0', 'Habilitar el uso de Marcas y modelos establecidos desde ASEBase ');
-- Cambio aplicado el 20180924


-- Cambio del 20180918
-- Requerido por Kater para pagar con diferente base de destajo por aseguradora.
ALTER TABLE `ins_aseguradoras` ADD `base_destajo` DOUBLE NULL;
-- Cambio aplicado el 20180918


-- Cambio del 20180917
-- Requerido por SAI para hacer el salto de presupuesto de manera individual por aseguradora o convenio
ALTER TABLE `ins_aseguradoras` ADD `aseguradora_saltapres` TINYINT(3) NULL DEFAULT '0' AFTER `omite_datos_pdf`;
-- Cambio aplicado el 20180917


-- Cambio del 20180915
-- Requerido para facilitar la captura manual de valuaciones AudaGold
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('nonumpart', '1', NULL, '0', 'Poner a 1 para deshabilitar la extraccion del codigo de parte durante la conversión de la valaución de texto a datos');
-- Cambio aplicado 20180915


-- Cambio del 20180831
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('SerieRPE', NULL, NULL, '0', 'Número de serie para recibos electrónicos de pago');
-- Cambio aplicado


-- Cambio del 20180814
-- Sugerido por SAI para concentrar sus mensajes en un sólo usuario encargado de la comunicación.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('AdminTelegram', NULL, NULL, '0', 'Usuario que recibirá todos los mensajes Telegram de clientes.');
-- Cambio aplicado 20180814


-- Cambio del 20180803
-- Con la puesta en marcha de ASE 1.1.2 se notó que no se habían aplicado las modificaciones para acepta cotozaciones automáticas desde Quien-Vende.com
ALTER TABLE `ins_prod_prov` ADD `sub_orden_id` INT(11) NULL DEFAULT NULL AFTER `fecha_cotizado`;

ALTER TABLE `ins_prod_prov` ADD `cotqv` TINYINT(1) NULL DEFAULT NULL AFTER `sub_orden_id`,
ADD `prod_mensaje` TEXT NULL DEFAULT NULL AFTER `cotqv`,
ADD `prod_vencimiento` DATETIME NULL DEFAULT NULL AFTER `prod_mensaje`,
ADD `prod_origen` VARCHAR(32) NULL DEFAULT NULL AFTER `prod_vencimiento`,
ADD `prod_condicion` VARCHAR(32) NULL DEFAULT NULL AFTER `prod_origen`,
ADD `prod_disponibilidad` DOUBLE NULL DEFAULT NULL AFTER `prod_condicion`,
ADD `prod_costo_envio` DOUBLE NULL DEFAULT NULL AFTER `prod_disponibilidad`;
-- Cambio aplicado el 20180907


-- Ajuste de tablas de boletines
-- Se detectó que no tienen AutoIndex
ALTER TABLE `ins_boletines` CHANGE `boletin_id` `boletin_id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ins_boletines_leidos` CHANGE `lectura_boletin_id` `lectura_boletin_id` INT(11) NOT NULL AUTO_INCREMENT;
-- Cambio aplicado el 20180730

-- Se agrega usuario de notificaciones para Telegram como origen de mensajes internos
INSERT INTO `ins_usuarios` (`usuario`, `clave`, `acceso`, `nombre`, `apellidos`, `puesto`, `localidad`, `codigo`, `areas`, `aseg`, `prov`, `rol01`, `rol02`, `rol03`, `rol04`, `rol05`, `rol06`, `rol07`, `rol08`, `rol09`, `rol10`, `rol11`, `rol12`, `rol13`, `rol14`, `rol15`, `rol16`, `rol17`, `inicio`, `fin`, `comida`, `contrato`, `comision`, `rfc`, `calle_numero`, `colonia`, `municipio`, `estado`, `telefono`, `telefono_laboral`, `movil`, `email`, `email_personal`, `fecha_alta`, `fecha_baja`, `fecha_password`, `activo`, `horas_programadas`, `estatus`, `sub_orden_id`, `ensesion`) VALUES ('700', 'dd45966a5c9524ac91ddafcfc5ac15cf', '1', 'Telegram', 'AutoShop Easy', 'Bot', '0', '1', '1|2|3|4|5|6|7|8|9|10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', '20', '13', '', '0.00', '', '', '', '', '', '', NULL, '', '', NULL, '2018-07-25 12:46:01', NULL, '2030-07-15 00:00:01', '1', NULL, NULL, NULL, '0');
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('TelegramToken', NULL, NULL, '0', 'Toket del API Telegram para comunicación con el bot de la instancia');
ALTER TABLE `ins_clientes` ADD `cliente_telegram_id` VARCHAR(40) NULL DEFAULT NULL AFTER `cliente_movil2`;
-- Cambio aplicado el 20180730


-- Se agrega campo necesario a devoluciones para aplicar disminución a facturas y pagos.
ALTER TABLE `ins_cambdevol_elementos` ADD `monto` DOUBLE NULL DEFAULT NULL AFTER `cantidad`;
ALTER TABLE `ins_pedidos_pagos` CHANGE `pago_referencia` `pago_referencia` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `pago_documento` `pago_documento` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
-- Cambio aplicado el 20180724


-- Cambio del 20180706
-- Originado en SMS para ocultar todos los documentos al personal, con excepción de los permisos por usuario 1025000 y 1025025.
INSERT INTO `ins_valores` (`val_id`, `val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES (NULL, 'limitaaccdocs', '0', NULL, '0', 'Colocar a 1 para no mostrar ningún documento con excepción de los indicados en permisos por usuario 1025000 y 1025025');
-- Cambio aplicado el 20180714


-- Cambio del 20170911 agregado a Valores el 20180707
-- Originalmente requerido por SAI
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('abreotforz', '0', NULL, '0', 'Colocar en 1 para forzar que sólo se puedan reabrir OTs cerradas con permiso de usuario');
-- Cambio aplicado el 20180714


-- Cambio del 20180701
-- Originado por el desarrollo de Devoluciones y Reemplazos para alojar las cotrarecibos de devoluciones.

CREATE TABLE IF NOT EXISTS `ins_cambdevol_devoluciones` (
`dev_id` int(11) NOT NULL,
  `dev_usuario_id` int(11) DEFAULT NULL,
  `dev_fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prov_id` int(11) DEFAULT NULL,
  `dev_persona_recibio` varchar(128) DEFAULT NULL,
  `dev_doc_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ins_cambdevol_devoluciones` ADD PRIMARY KEY (`dev_id`);

ALTER TABLE `ins_cambdevol_devoluciones` MODIFY `dev_id` int(11) NOT NULL AUTO_INCREMENT;
-- Cambio aplicado el 20180714


-- Cambio del 20180609
-- Originado por requerimiento de cálculo de % de utilida de pedidos en SAI.
ALTER TABLE `ins_pedidos` ADD `utilidad` DOUBLE NULL DEFAULT NULL AFTER `impuesto`;
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('utilcompras', '30', NULL, '0', 'Utilidad mínima para compras');
-- Cambio aplicado el 20180609



-- Cambio del 20180608
-- Variable para controlar por usuario a quien se le muestran los datos de Revisión de Precio de Ventas en gestión de refacciones.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('revprevent', NULL, NULL, '0', 'Colocar valor numérico en 1 para controlar por usuario quien puede ver la revisión de precios.');
-- Cambio aplicado el 20180608


-- Cambio del 20180608
-- Nuevo permiso de usuario requerido por SMS para controlar el Mostrar el recuadro de Revisión de Precios de Venta
INSERT INTO `ASEBase`.`funciones` (`fun_id`, `fun_num`, `fun_descripcion`, `fun_padre`, `cat_id`, `archivo`, `rol01`, `rol02`, `rol03`, `rol04`, `rol05`, `rol06`, `rol07`, `rol08`, `rol09`, `rol10`, `rol11`, `rol12`, `rol13`, `rol14`, `rol15`, `rol16`, `rol17`) VALUES (NULL, '1115110', 'Mostrar el recuadro de Revisión de Precios de Venta', '1115', NULL, 'refacciones.php ', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
-- Cambio aplicado a ASEBase Codero


-- Cambio del 20180516
-- Propagar el módulo de ventas

CREATE TABLE IF NOT EXISTS `ins_ventas` (
`venta_id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `aseguradora_id` int(11) DEFAULT NULL,
  `operario_id` int(11) DEFAULT NULL,
  `orden_id` int(11) DEFAULT NULL,
  `subtotal` double DEFAULT NULL,
  `impuesto` double DEFAULT NULL,
  `autoriza` int(11) DEFAULT NULL,
  `fecha_creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_entregado` datetime DEFAULT NULL,
  `observaciones` text,
  `usuario_vende` int(11) DEFAULT NULL,
  `venta_estatus` tinyint(2) DEFAULT NULL,
  `venta_alerta` tinyint(1) DEFAULT NULL,
  `venta_tipo` tinyint(1) DEFAULT NULL,
  `venta_pagado` tinyint(1) DEFAULT NULL,
  `venta_fecha_de_pago` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `ins_ventas`
 ADD PRIMARY KEY (`venta_id`);

ALTER TABLE `ins_ventas`
MODIFY `venta_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `ins_ventas_prod` (
`vp_id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `cantidad` double DEFAULT NULL,
  `entregados` double DEFAULT NULL,
  `precio_unitario` double DEFAULT NULL,
  `tangible` tinyint(1) DEFAULT NULL,
  `fecha_entregado` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `ins_ventas_prod`
 ADD PRIMARY KEY (`vp_id`), ADD KEY `venta_id` (`venta_id`,`prod_id`);

ALTER TABLE `ins_ventas_prod`
MODIFY `vp_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
-- Cambio aplicado el 20180714


-- Cambio del 20180516
-- Originado por SMS ya que trabajan con siniestros, no vehículos completos y pueden existir varios siniestros simultaneos para un mismo véhiculo.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('noverifserie', '0', NULL, '0', 'Para CRA con varias OTs simultaneas del mismo número de serie');
-- Cambio aplicado el 20180516



-- Cambio del 20180512
-- Originado por creación de resumenes en reportes requeridos por Sarsan
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('resumen_ing_egre', '1', NULL, '0', 'Mostrar resúmenes en reporte de vehículos recibidos y entregados');
-- Cambio aplicado el 20180714


-- Cambio del 20180508
-- Originado por nueva categoría de serevicio para SAI
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('numcatsservicio', '4', NULL, '0', 'Número de categorias de servicio');
-- Cambio aplicado el 20180508



-- Cambio el 20180429
-- Introducido en el módulo de cambios y devoluciones, utilizado en pedidos.php
-- Variable para guardar el porcentaje por agregar al costos relacionados a remmplazo de partes con cargo a personal del taller.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('prcxremp', '0', '0', '0', 'Porcentaje a agregar al COSTOS por remplazo de piezas descontadas a personal del taller');
-- Cambio aplicado 20180714


-- Cambio del 20180413
-- Permiso de usuario introducido para Kater.
-- Controla el acceso a los usuarios que pueden editar la fecha de termino de producción.
INSERT INTO `ASEBase`.`funciones` (`fun_id`, `fun_num`, `fun_descripcion`, `fun_padre`, `cat_id`, `archivo`, `rol01`, `rol02`, `rol03`, `rol04`, `rol05`, `rol06`, `rol07`, `rol08`, `rol09`, `rol10`, `rol11`, `rol12`, `rol13`, `rol14`, `rol15`, `rol16`, `rol17`) VALUES (NULL, '1040100', 'Cambio de Fecha de Termino de Producción', '1040', NULL, 'ordenes.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
-- Cambio no aplicado



-- Cambio del 20180412
-- Originado para registrar el número de descuento creado para OTs de tipo Interno que generen un remplazo o retrabajo.
ALTER TABLE `ins_subordenes` ADD `sub_descuento` INT NULL DEFAULT NULL AFTER `recibo_id`;
-- Cambio aplicado el 20180714


-- Cambio del 20180404
-- Permiso de usuario introducido para el módulo Boletines.
-- Controla el acceso a los usuarios que pueden gestionar los boletines.
INSERT INTO `ASEBase`.`funciones` (`fun_id`, `fun_num`, `fun_descripcion`, `fun_padre`, `cat_id`, `archivo`, `rol01`, `rol02`, `rol03`, `rol04`, `rol05`, `rol06`, `rol07`, `rol08`, `rol09`, `rol10`, `rol11`, `rol12`, `rol13`, `rol14`, `rol15`, `rol16`, `rol17`) VALUES (NULL, '1170100', 'Acceso a gestión de boletines', '1170', NULL, 'boletines.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);


-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ins_boletines` (
`boletin_id` int(11) NOT NULL,
  `boletin_titulo` varchar(150) DEFAULT NULL,
  `boletin_contenido` text,
  `boletin_archivo` varchar(150) DEFAULT NULL,
  `boletin_fecha_publicacion` datetime DEFAULT NULL,
  `boletin_fecha_vencimiento` datetime DEFAULT NULL,
  `boletin_correspondecia` varchar(200) DEFAULT NULL,
  `boletin_activo` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `ins_boletines`
 ADD PRIMARY KEY (`boletin_id`);

ALTER TABLE `ins_boletines`
  MODIFY `boletin_id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ins_boletines_leidos` (
`lectura_boletin_id` int(11) NOT NULL,
  `boletin_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `instancia` varchar(16) DEFAULT NULL,
  `lectura_fecha` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `ins_boletines_leidos`
 ADD PRIMARY KEY (`lectura_boletin_id`);

ALTER TABLE `ins_boletines_leidos`
  MODIFY `lectura_boletin_id` int(11) NOT NULL AUTO_INCREMENT;

-- Cambio aplicado el 20180714



-- Cambio del 20180402
-- Introducido para el módulo de Cambios y Devoluciones.
-- Se requiere la variable $usudictcd para indicar que usuario será el encargado de aprobar, modificar o rechazar un requerimiento,
-- así mismo recibirá las notificaciones de nuevos requerimientos.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('usudictcd', '0', NULL, '0', 'Usuario encargado de aprobar, modificar o rechazar un cambio o devolución');

CREATE TABLE IF NOT EXISTS `ins_cambdevol` (
  `cd_id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `prov_id` int(11) NOT NULL,
  `usu_responsable` int(11) NOT NULL,
  `motivo` varchar(256) DEFAULT NULL,
  `usu_requiere` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cd_estatus` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `ins_cambdevol` ADD PRIMARY KEY (`cd_id`);

ALTER TABLE `ins_cambdevol` MODIFY `cd_id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE IF NOT EXISTS `ins_cambdevol_elementos` (
  `elem_id` int(11) NOT NULL,
  `cd_id` int(11) NOT NULL,
  `op_id` int(11) NOT NULL,
  `cantidad` double DEFAULT NULL,
  `nombre` varchar(256) DEFAULT NULL,
  `usu_dictamina` int(11) DEFAULT NULL,
  `fecha_dictamen` datetime DEFAULT NULL,
  `dictamen` tinyint(3) NOT NULL DEFAULT '0',
  `dev_id` int(11) DEFAULT NULL,
  `devueltos` double DEFAULT NULL,
  `fecha_devuelto` datetime DEFAULT NULL,
  `tipo_cd` tinyint(3) NOT NULL,
  `area` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `ins_cambdevol_elementos` ADD PRIMARY KEY (`elem_id`);

ALTER TABLE `ins_cambdevol_elementos` MODIFY `elem_id` int(11) NOT NULL AUTO_INCREMENT;

-- Cambio aplicado el 20180714



-- Cambio del 20180323
-- Introducido para SMS con la finalidad de controlar que algoritmo de presupuestos y valuación utilizar en tareas
-- particulares.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('AlgoritmoDefault', NULL, 'parciales/auda-gold.php', 0, 'Algoritmo default para Presupuestos y Valuación.');
-- Cambio no aplicado. (ojo, algunas instancias ya lo tienen instalado)



-- Cambio del 20180313
-- Introducido para SAI con la finalidad de capturar de forma automática, en los pedidos A Cargo de la Aseguradora
-- el precio de venta como costo y de esta forma lograr que aparezcan como Costos de Aseguradora
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('costoeqprec', '0', NULL, '0', 'Cuando las piezas las surte la aseguradora y los clientes desean colocar el precio de venta como costo en los pedidos a cargo de la aseguradora');
-- Introducido para Paint Explosion ya que envían todo el presupuesto a un proveedor externo para que les maquile las valuaciones.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('cotextcmo', '0', NULL, '0', 'Agregar Mano de Obra a la hoja de cálculo que se envía a Aseguradora como presupuesto solicitado.');
-- Cambio aplicado el 20180313



-- Cambio del 20180222
-- Introducido para SMS con la finalidad de conservar pedidos ya que es necesario calcular las perdidas debido a que en
-- la mayoría de los casos no es posible hacer devoluciones.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES ('conservpedsperd', '0', NULL, '0', 'Conserva pedidos en situaciones de Perdidas ya que no es posible devolverlos todos y se desea conservar montos de costos y precios');
-- Cambio aplicado el 20180222



-- Cambio del 20180219.
-- Se requere para llevar el control de los pagos a los destajos, con la nueva incorporación de pagos padre y pagos hijo

ALTER TABLE `ins_destajos_pagos` ADD `pago_monto_origen` DOUBLE NULL AFTER `recibo_id`;
ALTER TABLE `ins_destajos_pagos` ADD `origen_pago_id`  INT(11) NULL AFTER `pago_monto_origen`;
ALTER TABLE `ins_destajos_pagos` ADD `herencia_pagos_id` VARCHAR(100) NULL AFTER `origen_pago_id`;
ALTER TABLE `ins_destajos_pagos` CHANGE `fecha_registro` `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;


-- Cambio del 20180215
-- 1.- Esta variable remueve de la vista de Operadores casi toda la información relativa al vehículo
-- dejando sólo acceso a subir fotos de avance de reparación.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES
('restrictotoper', '0', NULL, '0', 'Colocar val_numerico en 1 para restringir la vista de operadores a datos básicos del vehículo y a subir imágenes de avance sin derecho a ver otras imágenes.');
-- Cambio aplicado el 20180215.


-- Cambio del 20171208
-- 1.- Envío automático de la factura generada a receptor de la misma a través del correo electrónico registrado en su perfil.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES
('asunto_factura_email', NULL, 'Factura de ', '0', 'Primera parte del enunciado que construye el asunto del email de envío automático de facturas.');
-- Cambio aplicado el 20171208.


-- Cambio del 20171206
-- 1.- con la variable enviacdfi se controlará si se intenta enviar por correo electrónico los archivos de CFDi
-- recien generados.  Se está en 1 se incluye el archivo parciales/envia-cfdi.php que en caso de encontrar un correo electrónico
-- enviará los archivos con un mensaje estandar.
INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES
('enviacdfi', '0', NULL, '0', 'Colocar valor numérico en 1 para enviar al correo electrónico del cliente los archivos de CFDi recién generados');
-- Cambio aplicado el 20171208.
<

-- Cambio del 20171016
-- 1.- se agregó una nueva opción de configuración para controlar si se elimina la FPE de una OT
-- que se encuentra con estatus de reparación o entrega y se agrega una nueva tarea.  Regularmente
-- se debería eliminar para que el Jefe de Taller tome conciencia del cambio mediante los avisios
-- de OT Sin FPE que aparecen en varios reportes por lo que la inexistencia de este parametro o la
-- colocación de su valor numérico en 0 eliminará la FPE en la circunstancia antes descrita. Si
-- desea que se conserve cambie el valor numérico de este parámetro a 1

INSERT INTO `ins_valores` (`val_nombre`, `val_numerico`, `val_texto`, `val_arreglo`, `val_descripcion`) VALUES
('mantenerfpe', '1', NULL, 0, 'Colocar en 1 para no eliminar la FPE cuando se agrega una nueva tarea a la OT en etapa de reparación o entrega');
-- Cambio aplicado el 20171016.


--
-- Se aplicaron los cambios anteriores al 20171016
--

-- Cambios del 20170826.
-- 1.- omitir_notificaciones: Se utiliza para lograr configurar por aseguradora la omisión de
-- notificaciones a clientes. Para mantener compatibilidad con datos de aseguradoras ya existentes
-- se crea como default NULL tipo TinyINT. El las aseguradoras para las que se desea dejar de enviar
-- notificaciones a clientes, colocar este campo en 1.
-- 2.- omitir_inventario: En los sistemas donde está activo el candado de captura de inventario de
-- ingreso, al colocar este campo a 1 evitará la captura forsoza del inventario de ingreso para los
-- vehículos de esta aseguradora o convenio.
-- 3.- algoritmo_pres: Se especifica la ruta relativa del archivo que contiene el código que procesa
-- la translación o captura de refacciones, consumibles y mano de obra que utiliza en específico esta
-- aseguradora y convenio para el presupuesto.

ALTER TABLE `ins_aseguradoras` ADD `omite_notificaciones` TINYINT(3) NULL DEFAULT NULL AFTER `calc_pintura`,
ADD `omite_inventario` TINYINT(3) NULL DEFAULT NULL AFTER `omite_notificaciones`,
ADD `algoritmo_pres` VARCHAR(64) NULL DEFAULT 'parciales/directo-hpm.php' AFTER `omite_inventario`;
-- Cambio aplicado al 20180221


-- Cambio del 20170829
-- 1.- area_ut: Este campo se utiliza para saber si alguna área de esta aseguradora utiliza Unidades de Tiempo.
-- En caso afirmativo contendrá el importe por UT para cada área ya que en algunos casos los importes difieren
-- según el área. En caso de utilizar pesos este valor deberá dejarse en 0.
-- 2.- Este campo deberá remplazar a calc_pintura.
-- 3.- algoritmo_pres: Se especifica la ruta relativa del archivo que contiene el código que procesa
-- la translación o captura de refacciones, consumibles y mano de obra que utiliza en específico esta
-- aseguradora y convenio para el presupuesto.

ALTER TABLE `ins_aseguradoras` ADD `area_ut` VARCHAR(64) NOT NULL DEFAULT '0|0|0|0|0|0|0|0|0|0' AFTER `calc_pintura`,
ADD `algoritmo_autorizados` VARCHAR(64) NOT NULL DEFAULT 'parciales/auda-gold.php' AFTER `algoritmo_pres`;
-- Cambio aplicado al 20180221


-- Cambio del 20170912.
-- Se requiere migrar variables y arreglos de los archivos de configuración a la tabla de "valores" para
-- permitir su mantenimiento desde el front de ASE.
-- 1.- Se cambia el tipo de campo para val_numerico de DOUBLE a VARCHAR(64) para alojar en este los índices
-- de las variables contenidas en un arreglo en caso de que el valor sea parte de un arreglo.
-- 2.- Se agrega un nuevo campo val_arreglo para indicar que esta fila es parte de un arreglo definido en
-- 'val_nombre'

ALTER TABLE `ins_valores` CHANGE `val_numerico` `val_numerico` VARCHAR(64) NULL DEFAULT NULL;
ALTER TABLE `ins_valores` ADD `val_arreglo` TINYINT(1) NOT NULL DEFAULT '0' AFTER `val_texto`;
-- Cambio aplicado al 20180221