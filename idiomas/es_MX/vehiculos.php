<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Vehículos | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Datos de vehículo";

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


define('ENCABEZADO_CONSULTA', '');
define('TIPO_TRANSMISION', 'Tipo de transmisión');
define('SEGUROS_PTAS', 'Seguros de puertas');
define('AIRE_ACONDICINADO', 'Aire acondicionado');
define('ELEVADORES', 'Elevadores de ventanas');
define('ESPEJOS', 'Espejos laterales');
define('VIDRIOS', 'Color de vidrios');
define('MEDALLON', 'Medallón');
define('PARABRISAS', 'Parabrisas');
define('QUEMACOCOS', 'Quemacocos');
define('DIRECCION', 'Tipo de dirección');
define('BOLSA_AIRE', 'Bolsas de aire');
define('TIPO_FRENOS', 'Tipo de Frenos');
define('TIPO_SUSPENSION', 'Tipo de suspensión');
define('TIPO_RINES', 'Tipo de Rines');
define('RINES_ORIGINALES', 'Rines Originales?');
define('VESTIDURAS', 'Vestiduras');
define('TIPO_DE_FAROS', 'Tipo de Faros');
define('NUM_VEL', 'Número de Velocidades');

$lang = array(
'Acceso autorizado' => 'Acceso autorizado',
'Acceso NO autorizado' => 'Acceso NO autorizado.',
'Acciones' => 'Acciones',
'Activo' => 'Activo',
'Agregando vehiculo' => 'Agregando un nuevo vehículo de ',
'alta vehiculo perfil del cliente' => 'Los vehículos se deben dar de alta desde el perfil del cliente',
'Año' => 'Año',
'Automático' => ' Automático',
'Cilindrada' => 'Cilindrada',
'Cilindros' => 'Cilindros',
'Color' => 'Color',
'con defroster' => 'Con defroster',
'Datos del vehículo' => 'Datos del vehículo',
'Datos mínimos' => 'Datos mínimos obligatorios',
'datos iguales de vehiculo' => 'Hay más de un vehículo con estos datos',
'Eléctricos' => ' Eléctricos',
'Estándar' => 'Estándar',
'Manual' => 'Manual',
'Marca' => 'Marca',
'Modificando vehículo' => 'Modificando datos del vehículo',
'No encontró cliente' => 'No se encontró el número de cliente ',
'No encontró vehículo' => 'No se encontró ningún vehículo con estos datos',
'No' => ' No',
'Número de vehículo' => 'Número de vehículo',
'Num Motor' => 'Número de Motor',
'Placas' => 'Placas',
'Propiedad del Cliente' => 'Cliente',
'Puertas' => 'Puertas',
'Regresar al Cliente' => 'Regresar a los Datos del Cliente',
'Serie' => 'Serie',
'Serie' => 'Serie',
'Sí' => 'Sí',
'Sombreado' => ' Sombreado',
'Subtipo' => 'Subtipo',
'Térmicos' => ' Térmicos',
'Tintado' => 'Tintado',
'Tipo de Motor' => 'Tipo de Motor',
'Tipo de Transmisión' => 'Tipo de Transmisión',
'Tipo' => 'Tipo',
'Un dato para buscar' => 'Se requiere un dato para buscar, por favor intente de nuevo',
'Vehículo' => 'Vehículo',

'Aire acondicionado' => 'Aire acondicionado',
'Bolsas de aire' => 'Bolsas de aire',
'Color de vidrios' => 'Color de vidrios',
'Elevadores de ventanas' => 'Elevadores de ventanas',
'Espejos laterales' => 'Espejos laterales',
'Medallón' => 'Medallón',
'Número de Velocidades' => 'Número de Velocidades',
'Parabrisas' => 'Parabrisas',
'Quemacocos' => 'Quemacocos',
'Rines Originales?' => 'Rines Originales?',
'Seguros de puertas' => 'Seguros de puertas',
'Tipo de dirección' => 'Tipo de dirección',
'Tipo de Faros' => 'Tipo de Faros',
'Tipo de Frenos' => 'Tipo de Frenos',
'Tipo de Rines' => 'Tipo de Rines',
'Tipo de suspensión' => 'Tipo de suspensión',
'Vestiduras' => 'Vestiduras',

'' => '',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}


// define('', '');
/* Página de idiomas para vehiculos */ 