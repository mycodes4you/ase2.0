<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/

define('ORDEN_ESTATUS_','No Definido');
define('ORDEN_ESTATUS_0','Ingreso');
define('ORDEN_ESTATUS_1','Descripción de Daños');
define('ORDEN_ESTATUS_2','Valuado');
define('ORDEN_ESTATUS_3','Por Autorizar Reparación');
define('ORDEN_ESTATUS_4','Reparación por iniciar');
define('ORDEN_ESTATUS_5','Entregado con Refacciones Pendientes');
define('ORDEN_ESTATUS_6','Entregado con Refacciones Recibidas por Instalar');
define('ORDEN_ESTATUS_7','En Reproceso - Pausa');
define('ORDEN_ESTATUS_8','En Proceso - Pausa');
define('ORDEN_ESTATUS_9','En Proceso - Inicio');
define('ORDEN_ESTATUS_10','En Proceso - Continua');
define('ORDEN_ESTATUS_11','En Proceso - Concluido');
define('ORDEN_ESTATUS_12','Notificar a Cliente');
define('ORDEN_ESTATUS_13','Cliente Notificado');
define('ORDEN_ESTATUS_14','Vehículo Lavado');
define('ORDEN_ESTATUS_15','Listo para entregar');
define('ORDEN_ESTATUS_16','Entregado por Documentar');
define('ORDEN_ESTATUS_17','Recepción');
define('ORDEN_ESTATUS_20','Esperando Autorización');
define('ORDEN_ESTATUS_21','Revisión de Calidad');
define('ORDEN_ESTATUS_22','Por Lavar');
define('ORDEN_ESTATUS_23','Lavando');
define('ORDEN_ESTATUS_24','Por Presupuestar');
define('ORDEN_ESTATUS_25','Presupuesto en pausa');
define('ORDEN_ESTATUS_26','Presupuestando');
define('ORDEN_ESTATUS_27','Presupuesto Concluido');
define('ORDEN_ESTATUS_28','Cotizando Refacciones');
define('ORDEN_ESTATUS_29','Valuando');
define('ORDEN_ESTATUS_30','Entrega - Pago de Daños');
define('ORDEN_ESTATUS_31','Entrega - Pérdida Total');
define('ORDEN_ESTATUS_32','Entrega - Pago Plus');
define('ORDEN_ESTATUS_33','Improcedente: No reparar');
define('ORDEN_ESTATUS_34','Menor a Deducible: No reparar');
define('ORDEN_ESTATUS_35','Entrega Sin reparar');
define('ORDEN_ESTATUS_90','Cancelada');
define('ORDEN_ESTATUS_91','Presupuesto Archivado');
define('ORDEN_ESTATUS_93','Entregado - Sin Reparar');
define('ORDEN_ESTATUS_94','Entregado - Menor a Deducible');
define('ORDEN_ESTATUS_95','Entregado - Improcedente');
define('ORDEN_ESTATUS_96','Entregado - Pago Plus');
define('ORDEN_ESTATUS_97','Entregado - Pérdida Total');
define('ORDEN_ESTATUS_98','Entregado - Pago de Daños');
define('ORDEN_ESTATUS_99','Cerrada - Entregado');
define('ORDEN_ESTATUS_210','Cerrada - En Revisión');

////////////////  Estatus para vehículos en Tránsito    ///////////////////////

define('ORDEN_ESTATUS_T_','No definido');
define('ORDEN_ESTATUS_T_1','Descripción de Daños');
define('ORDEN_ESTATUS_T_2','Valuado');
define('ORDEN_ESTATUS_T_3','En Proceso - Por asignar');
define('ORDEN_ESTATUS_T_4','Reparación por iniciar');
define('ORDEN_ESTATUS_T_20','Autorización Solicitada');
define('ORDEN_ESTATUS_T_24','Por Presupuestar');
define('ORDEN_ESTATUS_T_27','Presupuesto Concluido');
define('ORDEN_ESTATUS_T_28','Cotizando Refacciones');
define('ORDEN_ESTATUS_T_29','Valuando');


define('SUBORDEN_ESTATUS_100','<img src="idiomas/' . $idioma . '/imagenes/sin-tareas.png" alt="Sin Tareas" title="Sin Tareas">');
define('SUBORDEN_ESTATUS_101','<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Captura de Daños" title="Captura de Daños">Captura de Daños');
define('SUBORDEN_ESTATUS_102','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Valuado" title="Valuado">Valuado');
define('SUBORDEN_ESTATUS_103','<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Por asignar" title="Por Asignar">Asignar');
define('SUBORDEN_ESTATUS_104','<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Por iniciar" title="Por iniciar">Reparación<br>por iniciar');
define('SUBORDEN_ESTATUS_105','<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Esperando refacciones" title="Esperando refacciones">Esperando refacciones');
define('SUBORDEN_ESTATUS_106','<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Refacciones surtidas" title="Refacciones surtidas">Refacciones recibidas');
define('SUBORDEN_ESTATUS_107','<img src="idiomas/' . $idioma . '/imagenes/alerta-critica.png" alt="Reproceso" title="Reproceso">Reproceso por iniciar');
define('SUBORDEN_ESTATUS_108','<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Operador en pausa" title="Operador en pausa">Operador en pausa');
define('SUBORDEN_ESTATUS_109','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Operador inició" title="Operador inició">Operador inició');
define('SUBORDEN_ESTATUS_110','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Operador Reanuda" title="Operador Reanuda">Operador continua');
define('SUBORDEN_ESTATUS_111','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Operador concluyó" title="Operador concluyó">Operador concluyó');
define('SUBORDEN_ESTATUS_112','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Tarea terminada" title="Tarea terminada">Tarea terminada');
define('SUBORDEN_ESTATUS_114','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Tarea terminada" title="Tarea terminada">Tarea terminada');
define('SUBORDEN_ESTATUS_120','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Autorización Solicitada" title="Autorización Solicitada">Autorización Solicitada');
define('SUBORDEN_ESTATUS_121','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Revisión de Calidad" title="Revisión de Calidad">Revisión de Calidad');
define('SUBORDEN_ESTATUS_122','<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Por Lavar" title="Por Lavar">Por Lavar');
define('SUBORDEN_ESTATUS_123','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Lavando" title="Lavando">Lavando');
define('SUBORDEN_ESTATUS_124','<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Por Presupuestar" title="Por Presupuestar">Por Presupuestar');
define('SUBORDEN_ESTATUS_125','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Presupuesto en pausa" title="Presupuesto en pausa">Presupuesto en pausa');
define('SUBORDEN_ESTATUS_126','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Creando Presupuesto" title="Creando Presupuesto">Creando Presupuesto');
define('SUBORDEN_ESTATUS_127','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Presupuesto Concluido" title="Presupuesto Concluido">Presupuesto Concluido');
define('SUBORDEN_ESTATUS_128','<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Por Cotizar" title="Por Cotizar">Por Cotizar');
define('SUBORDEN_ESTATUS_129','<img src="idiomas/' . $idioma . '/imagenes/alerta-preventiva.png" alt="Valuando" title="Valuando">Valuando');
define('SUBORDEN_ESTATUS_130','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Pago de Daños" title="Pago de Daños">Pago de Daños');
define('SUBORDEN_ESTATUS_131','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Pérdida Total" title="Pérdida Total">Pérdida Total');
define('SUBORDEN_ESTATUS_132','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Pago Plus" title="Pago Plus">Pago Plus');
define('SUBORDEN_ESTATUS_133','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Improcedente" title="Improcedente">Improcedente');
define('SUBORDEN_ESTATUS_134','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Menor a Deducible" title="Menor a Deducible">Menor a Deducible');
define('SUBORDEN_ESTATUS_135','<img src="idiomas/' . $idioma . '/imagenes/alerta-normal.png" alt="Entregado Sin Reparar" title="Entregado Sin Reparar">Entregado Sin Reparar');
define('SUBORDEN_ESTATUS_190','Cancelado');
define('SUBORDEN_ESTATUS_197','No realizado');
define('SUBORDEN_ESTATUS_198','No realizado');
define('SUBORDEN_ESTATUS_199','Terminado');

define('TAREA_ESTATUS_','Sin Estatus');
define('TAREA_ESTATUS_100','Sin Tareas');
define('TAREA_ESTATUS_101','Captura de Daños');
define('TAREA_ESTATUS_102','Valuado');
define('TAREA_ESTATUS_103','Asignar');
define('TAREA_ESTATUS_104','Reparación por iniciar');
define('TAREA_ESTATUS_105','Esperando refacciones');
define('TAREA_ESTATUS_106','Refacciones recibidas');
define('TAREA_ESTATUS_107','Reproceso por iniciar');
define('TAREA_ESTATUS_108','Operador en pausa');
define('TAREA_ESTATUS_109','Operador inició');
define('TAREA_ESTATUS_110','Operador continua');
define('TAREA_ESTATUS_111','Operador concluyó');
define('TAREA_ESTATUS_112','Tarea terminada');
define('TAREA_ESTATUS_114','Tarea terminada');
define('TAREA_ESTATUS_120','Autorización Solicitada');
define('TAREA_ESTATUS_121','Revisión de Calidad');
define('TAREA_ESTATUS_122','Por Lavar');
define('TAREA_ESTATUS_123','Lavando');
define('TAREA_ESTATUS_124','Por Presupuestar');
define('TAREA_ESTATUS_125','Presupuesto en pausa');
define('TAREA_ESTATUS_126','Creando Presupuesto');
define('TAREA_ESTATUS_127','Presupuesto Concluido');
define('TAREA_ESTATUS_128','Por Cotizar');
define('TAREA_ESTATUS_129','Valuando');
define('TAREA_ESTATUS_130','Pago de Daños');
define('TAREA_ESTATUS_131','Pérdida Total');
define('TAREA_ESTATUS_132','Pago Plus');
define('TAREA_ESTATUS_133','Improcedente');
define('TAREA_ESTATUS_134','Entregar Sin Reparar');
define('TAREA_ESTATUS_135','Entregar Sin Reparar');
define('TAREA_ESTATUS_190','Cancelado');
define('TAREA_ESTATUS_197','No realizado');
define('TAREA_ESTATUS_198','No realizado');
define('TAREA_ESTATUS_199','Terminado');


/* --------------------- Panel de Control ------------------------- */   

$etapa = array('1' => array(17,1,24,27,28,29,20,2,3),
	'2' => array(4,9,7,8,10,11),
	'3' => array(21,12,13,14,15),
	'4' => array(16,99,5,6,300), // --- el 300 abarca todos los entregados con estatus menor a 30 ---
	'5' => array(30,31,32,33,34,35),
	'6' => array(90,98,97,96,95),
	);

define('PANEL_CONTROL_1','Recepción y Valuación');
define('PANEL_CONTROL_2','Ordenes Autorizadas en Reparación');
define('PANEL_CONTROL_3','Ubicación por Área de Reparación');
define('PANEL_CONTROL_6','PT, Pago de Daños<br>y Otros Sin Reparación');
define('PANEL_CONTROL_7','Recibidos por Siniestro o Tipo de Cliente');
define('PANEL_CONTROL_8','Reparados Entregados por Siniestro o Tipo de Cliente');
define('PANEL_CONTROL_9','Vehículos en Proceso');

// ------------------ Presupuestos Previos ----------------------

define('PREVIA_ESTATUS_', 'No definido');
define('PREVIA_ESTATUS_10', 'Elaborando Presupuesto');
define('PREVIA_ESTATUS_90', 'Cancelado');
define('PREVIA_ESTATUS_91', 'Agregado a OT');
define('PREVIA_ESTATUS_99', 'Entregado al Cliente');

// ------------------ ESTATUS COMISIONES ------------------------

$comision_sta = [
	'10' => 'Sin recibo',
	'20' => 'Pendiente de pago',
	'30' => 'Pagada',
];

// define tiempos de alerta para cada estatus en la cantidad de segundos transcurridos desde que inició ese estatus 

//define('', '');

/* Página de idiomas para estatus */ 