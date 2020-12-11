<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Registro Express | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Registro Express";

$lang = array(
'acceso_error' => 'Acceso NO autorizado ingresar Usuario y Clave correcta!',
'Apellidos' => 'Apellidos',
'Categoría de Servicio' => 'Categoría de Servicio',
'Celular' => 'Celular',
'cilindrada' => 'Cilindrada',
'cilindros' => 'Cilindros',
'cliente' => 'Cliente',
'clietipo' => 'Asegurado o Propietario: ',
'color' => 'Color',
'conductor' => 'Tipo de<br>conductor',
'cr1' => 'Daño Fuerte',
'cr2' => 'Daño Medio',
'cr3' => 'Daño Leve',
'cr4' => 'Express',
'cualot' => ' Indique OT con reclamo.',
'deseaemail' => 'Desea recibir actualizaciones por e-mail? ',
'Directo' => 'Directo:',
'docadmin' => 'Agregar imagen de orden de admisión: ',
'docrep' => 'Agregar imagen de hoja de daños: ',
'donde' => 'Selecciona si el auto <strong>se queda <br>"En Taller" o se va en "Tránsito"</strong>:',
'email' => 'e-Mail',
'En Taller' => 'En Taller',
'Garantía' => 'Garantía: ',
'marca' => 'Marca',
'motor' => 'Tipo de Motor',
'Nextel' => 'Nextel',
'no_id' => 'No se localizó un identificador válido, favor de reportarlo a soporte@controldeservicio.com Gracias!',
'Nombre' => 'Nombre',
'os1' => 'Con Cita',
'os2' => 'Garantía',
'os3' => 'Sin Cita',
'os4' => 'Siniestro',
'Otro' => 'Otro',
'Placa' => 'Placas',
'puertas' => 'Puertas',
'Seleccione Asesor' => 'Seleccione Asesor',
'Seleccione Categoría' => 'Seleccione Categoría',
'serie' => 'Serie',
'servicio' => 'Asegurdora<br>o Convenio:',
'subtipo' => 'Subtipo',
'Teléfono' => 'Teléfono',
'tercero' => '<br>Tercero: ',
'Tipo de Servicio' => 'Tipo de Servicio:',
'Tipo de Servicio' => 'Tipo de Servicio',
'tipo' => 'Tipo',
'Torre' => 'Torre',
'Tránsito' => 'Tránsito',
'vehiculo' => 'Vehículo',
'year' => 'Año',
'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}
/* Página de idiomas para Registro Express */ 