<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Registro Express | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Registro Express";

$lang = array(
'acceso_error' => 'Acceso NO autorizado!',
'Apellidos' => 'Apellidos',
'Categoría de Servicio' => 'Categoría de Servicio',
'Celular' => 'Movil1',
'cilindrada' => 'Cilindrada',
'cilindros' => 'Cilindros',
'cliente' => 'Cliente',
'clietipo' => 'Asegurado o Propietario',
'color' => 'Color',
'conductor' => 'Tipo de conductor',
'Contacto' => 'Contacto',
'cr1' => 'Daño Fuerte',
'cr2' => 'Daño Medio',
'cr3' => 'Daño Leve',
'cr4' => 'Express',
'cualot' => 'Si es Garantía, indica que OT se reclama: ',
'deseaemail' => 'Desea recibir actualizaciones por e-mail? ',
'Directo' => 'Directo:',
'docadmin' => 'Agregar imagen de orden de admisión: ',
'docrep' => 'Agregar imagen de hoja de daños: ',
'donde' => 'Selecciona si el auto <strong>se queda <br>"En Taller" o se va en "Tránsito"</strong>:',
'EditarDatos' => 'Editar Datos',
'email' => 'e-Mail',
'Empresa' => 'Empresa',
'En Taller' => 'En Taller',
'Garantía' => 'Garantía: ',
'Grua' => '¿Llegó en Grua?',
'marca' => 'Marca',
'motor' => 'Tipo de Motor',
'Nextel' => 'Movil2',
'no_id' => 'No se localizó un identificador válido, favor de reportarlo a soporte@controldeservicio.com Gracias!',
'Nombre' => 'Nombre',
'nomdocadmin' => 'Orden de Admisión',
'nomdocrep' => 'Hoja de Daños',
'os1' => 'Particular Con Cita',
'os2' => 'Garantía',
'os3' => 'Particular Sin Cita',
'os4' => 'Siniestro',
'os5' => '---',
'os6' => 'Venta Mostrador',
'Otro' => 'Tel2',
'Placa' => 'Placas',
'puertas' => 'Puertas',
'Requeridos' => 'Por favor complete todos los campos requeridos',
'Seleccione Asesor' => 'Seleccione Asesor',
'Seleccione Categoría' => 'Seleccione Categoría',
'Sel Servicio' => 'Seleccione Tipo de Servicio',
'serie' => 'Serie',
'servicio' => 'Asegurdora o Convenio:',
'subtipo' => 'Subtipo',
'Teléfono' => 'Tel1',
'tercero' => 'Tercero',
'Tipo de Servicio' => 'Tipo de Servicio',
'tipo' => 'Tipo',
'Torre' => 'Torre',
'Tránsito' => 'Tránsito',
'vehiculo' => 'Vehículo',
'year' => 'Año',
'Ya existe empresa' => 'Ya existe empresa o contacto ',
'Elija una' => '. Por favor agregue el vehículo aquí en una de las empresas listadas y después regrese a Asociar el Vehículo.',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}
/* Página de idiomas para Registro Express */ 