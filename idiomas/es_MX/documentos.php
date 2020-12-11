<?php
/*

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Documentos de Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Documentos Asociados a Ordenes de Trabajo";

$lang = array(
'AVANCE DE REPARACIÓN?' => 'AVANCE DE REPARACIÓN?',
'Confidencial' => 'La imagen ¿Es un documento Confidencial?',
'Acceso para Administradores' => 'Acceso no autorizado para su Usuario',
'Agregar múltiples documentos' => 'Agregar múltiples documentos relacionados a este<br>Expediente (Max 2Mb por archivo, Max 10MB en Total)',
'asunto' => 'Informe sobre Su Automóvil en ' . $agencia,
'Cliente sin email' => 'Cliente sin email capturado.',
'Estatus Eliminar' => 'No se pueden eliminar documentos en este estatus: ' . constant('ORDEN_ESTATUS_' . $estatus),
'' => '',
'' => '',
'' => '',
);

$ayuda = [
	'AyudaAccesos' => 	'<p align="justify">
					Muestra dos indicadores que presentan el estatus de (confidencialidad y Visibilidad)
					que tiene el documento:<br>
					<img src="idiomas/es_MX/imagenes/documento_clasificado.png" alt="clasificado" title="clasificado" width="35" height="35""> Documento clasificado.<br>
					<img src="idiomas/es_MX/imagenes/documento_no_clasificado.png" alt="No clasificado" title="No clasificado" width="35" height="35""> Documento NO clasificado.<br>
					<img src="idiomas/es_MX/imagenes/cliente_visible.png" alt="visible para cliente" title="visible para cliente" width="35" height="35""> Documento visible por cliente.<br>
					<img src="idiomas/es_MX/imagenes/cliente_no_visible.png" alt="No visible para cliente" title="No visible para cliente" width="35" height="35""> Documento NO visible por cliente.<br>
					Si eres un usuario con permiso para hacer modificaciones puedes hacer click en el ícono del candado o el ojo para cambiar su estatus.
				</p>',
];

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}


/* Página de idiomas para vehiculos */ 
