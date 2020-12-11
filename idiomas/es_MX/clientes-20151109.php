<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Bienvenidos a e-Taller";
$keywords="administración de taller, control de taller";
$pag_desc="Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Administración de Clientes";

$lang = array(
'Acceso Autorizado' => 'Acceso Autorizado',
'Acceso NO autorizado' => 'Acceso NO autorizado.',
'Agregar nuevo cliente' => 'Agregar nuevo cliente',
'Apellido' => 'Apellido ',
'Borrar' => 'Borrar',
'Cliente ID' => 'Cliente ID: ',
'Clientes' => 'Clientes',
'Consultar Cliente' => 'Consultar Cliente',
'Consultar Vehículo' => 'Consultar Vehículo',
'Cuentas por Cobrar' => 'Cuentas por Cobrar',
'e-Mail' => 'e-Mail ',
'Empresa' => 'Empresa',
'Enviar' => 'Enviar',
'los siguientes campos' => '(Cualquiera de los siguientes campos)',
'Modificar Cliente' => 'Modificar Cliente: ',
'Modificar Vehículo' => 'Modificar Vehículo',
'Nombre' => 'Nombre ',
'Nuevo Cliente' => 'Nuevo Cliente',
'Nuevo Vehículo' => 'Nuevo Vehículo',
'Número de Cliente' => 'Número de Cliente: ',
'Número de Placas' => 'Número de Placas: ',
'Número de Vehículo' => 'Número de Vehículo: ',
'Número' => 'Número ',
'Placas' => 'Placas ',
'Teléfono Trabajo' => 'Teléfono Trabajo: ',
'vehículos agregan desde el cliente' => 'Los vehículos se agregan desde los datos del cliente.',
'Vehículos' => 'Vehículos',
'' => '',
'' => '',
'' => '',
'' => '',
);
if(file_exists('particular/textos/clientes.php')) {
	include('particular/textos/clientes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para vehiculos */ 