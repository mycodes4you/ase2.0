<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Bienvenidos a AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Datos personales del Cliente";

$estados = array(
'Aguascalientes',
'Baja California',
'Baja California Sur',
'Campeche',
'Chiapas',
'Chihuahua',
'Ciudad de México',
'Coahuila',
'Colima',
'Durango',
'Estado de México',
'Guerrero',
'Guanajuato',
'Hidalgo',
'Jalisco',
'Michoacán',
'Morelos',
'Nayarit',
'Nuevo León',
'Oaxaca',
'Puebla',
'Querétaro',
'Quintana Roo',
'San Luís Potosí',
'Sinaloa',
'Sonora',
'Tabasco',
'Tamaulipas',
'Tlaxcala',
'Veracruz',
'Yucatán',
'Zacatecas'
);

$lang = array(
'Acceso NO autorizado' => 'Acceso NO autorizado ingresar Usuario y Clave correcta',
'Agregar Vehículos' => 'Agregar Vehículos',
'apellido corto' => 'El apellido es muy corto: ',
'Apellidos' => 'Apellidos',
'Borrar' => 'Borrar',
'Calle' => 'Calle',
'calle corto' => 'El nombre de la calle es muy corto: ',
'Celular' => 'Móvil 1',
'Clave de Cliente' => 'Clave de Cliente',
'Cliente' => 'Cliente',
'Colonia' => 'Colonia',
'colonia corta' => 'La colonia es muy corta:',
'correo corta' => 'La dirección de la cuenta de correo es muy corta: ',
'CP corto' => 'El código postal es de 5 números.',
'C.P.' => 'C.P.',
'Datos de contacto' => 'Datos de contacto',
'Datos facturación' => 'Datos de facturación:',
'Datos fiscales de facturación' => 'Datos fiscales de facturación',
'Datos del cliente' => 'Datos de contacto:',
'Delegación Municipio' => 'Delegación o Municipio',
'Delegación o Municipio' => 'Delegación<br>o Municipio',
'Desea actualizar Datos fiscales?' => '¿Desea actualizar los Datos fiscales para facturación?',
'Desea recibir e-mail' => 'Sí desea recibir notificaciones por e-mail.',
'e-Mail' => 'e-Mail',
'Empresa' => 'Empresa',
'Enviar' => 'Enviar',
'Estado' => 'Estado',
'Modificar' => 'Modificar',
'municipio delegación corto' => 'El municipio o delegación es muy corto: ',
'Nextel' => 'Móvil 2',
'No hay datos' => 'No se encontraron registros con esos datos.',
'nombre corto' => 'El nombre es muy corto: ',
'Nombre' => 'Nombre',
'Nombre de empresa o Nombre completo del cliente' => 'Nombre de empresa o Nombre completo del cliente',
'notificaciones e-mail' => 'Suscrito a notificaciones por e-mail?',
'Notificaciones por e-mail?' => 'Notificaciones por e-mail?',
'Número de cliente' => 'Cliente',
'número exterior corto' => 'El número exterior es muy corto: ',
'Número exterior' => 'Número exterior',
'Número interior' => 'Número interior',
'País' => 'País',
'Razón Social corto' => 'El nombre de la Razón Social es muy corto: ',
'Razón Social' => 'Razón Social',
'Representante' => 'Representante',
'RFC corto' => 'El RFC es muy corto: 12 posiciones para empresas, 13 para personas.',
'RFC largo' => 'El RFC es muy largo: 12 posiciones para empresas, 13 para personas.',
'RFC' => 'RFC',
'Selecciona Estado' => 'Selecciona un Estado',
'Si no requiere factura para deduccion fiscal, colocar RFC genérico XAXX010101000' => 'Si no requiere factura para deduccion fiscal, colocar RFC genérico XAXX010101000',
'Tel contacto' => 'Tel del Representante',
'Tel del Representante' => 'Teléfono del<br>Representante',
'Teléfono Casa' => 'Otro Teléfono',
'teléfono tener lada' => 'El número de teléfono debe tener al menos 7 digitos de número local: ',
'Teléfono Trabajo' => 'Teléfono Principal',
'un dato para buscar' => 'Se necesita al menos un dato para buscar.',
'Ver Documentos' => 'Ver Documentos',
'Ver OT' => 'Ver Ordenes de Trabajo',
'Ver Previa' => 'Ver Presupuestos Previos',
'Ver Vehículos' => 'Ver Vehículos',
'Ya existe empresa' => 'Ya existe un Cliente con los siguientes datos: ',
'' => '',
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}


/* Página de idiomas para personas */ 
