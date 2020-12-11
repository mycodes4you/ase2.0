<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Vehículos | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Datos de vehículo";

define('ENCABEZADO_CONSULTA', 'Datos de los vehículos:');

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
'Placa' => 'Placas',
);

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}


// define('', '');
/* Página de idiomas para vehiculos */ 