<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Administración de Aseguradoras | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico de Clase Mundial.";
$pagina_actual="Administración de Aseguradoras";

$lang = array(
'Acceso autorizado' => 'Acceso autorizado',
'Acceso NO autorizado ingresar Usuario y Clave correcta' => 'Acceso NO autorizado ingresar Usuario y Clave correcta',
'Acciones' => 'Acciones',
'aseguradora' => ' a la aseguradora ',
'Autosurtido' => 'Autosurtido?',
'Banco Origen' => 'Banco Origen',
'Banco y Cuenta de cobro' => 'Debe indicar la cuenta en la que se reciben los fondos.',
'Borrar' => 'Borrar',
'Calle' => 'Calle',
'Calle con número' => 'Calle con número',
'calle y número corto' => 'La calle y número es muy corto: ',
'cliente' => ' al cliente ',
'Cobro de factura ' => 'Cobro de factura ',
'cobro de la factura' => ' del cobro de la factura ',
'Cobro Total' => 'Cobro Total de ',
'Comprob Cobro de factu' => 'Comprobante de Cobro de Factura',
'Colonia' => 'Colonia',
'colonia corta' => 'La colonia es muy corta:',
'Contacto' => 'Contacto',
'correo corto' => 'La dirección de la cuenta de correo es muy corta:',
'CP' => 'Código Postal',
'CP de 5 dígitos' => 'El Código Postal es de 5 dígitos:',
'Cuenta de Cobro' => 'Cuenta de Depósito',
'Datos de la Aseguradora' => 'Datos de la Aseguradora',
'Datos fiscales' => 'Datos Fiscales',
'Datos incompletos' => '',
'de la Aseguradora' => 'de la Aseguradora',
'El municipio o delegación es muy corto: ' => 'El municipio o delegación es muy corto: ',
'E mail' => 'E mail',
'Emails para altas' => 'Emails para altas a',
'Enviar Altas de Ingresos' => 'Enviar Altas de Ingresos',
'Enviar' => 'Enviar',
'Estado' => 'Estado',
'Factura' => 'Factura',
'Fecha del cobro' => 'Fecha del cobro',
'Habilitar envío de Altas?' => 'Habilitar envío de Altas?',
'Imagen de comprobante de cobro' => 'Imagen de comprobante de cobro: ',
'IVA cobrado de la factura' => 'IVA cobrado de la factura ',
'IVA' => 'IVA (16%):',
'IVA trasladado por cobrar de la factura' => 'IVA Trasladado por cobrar de la factura ',
'Método de Cobro' => 'Método de Cobro',
'Modificar' => 'Modificar',
'Monto de este cobro' => 'Monto de este cobro $',
'Monto del cobro no debe ser diferente' => 'El monto del cobro no debe ser diferente a la suma de las cantidades asignadas a cada factura.',
'monto del cobro no puede ser cero' => 'El monto del cobro no puede ser cero, negativo o vacío.',
'Municipio Delegación' => 'Municipio / Delegación',
'NIC corto' => 'El NIC es muy corto:',
'NIC' => 'NIC',
'Nombre' => 'Nombre',
'No se encontraron registros con esos datos' => 'No se encontraron registros con esos datos.',
'Num cheque o transferencia' => 'Indique el número de cheque o transferencia.',
'Número de Aseguradora' => 'Número de Aseguradora',
'Num Ext' => 'Num Ext',
'Num Int' => 'Num Int',
'OT' => ' de la OT ',
'OT' => ' de la OT ',
'País' => 'País',
'Precio de MO' => 'Precio de MO',
'Precio de Unidad de Trabajo' => 'Precio de Unidad de Trabajo:',
'Proveedor Default' => 'Proveedor Default',
'Razón Social corto' => 'El nombre de la Razón Social es muy corto:',
'Razón Social' => 'Razón Social',
'Registrar cobro' => 'Registro de pago ',
'Registrar Cobros' => 'Registrar Cobros',
'Registro en cuenta' => 'Registro en cuenta ',
'Regresar a aseguradora' => 'Regresar a Datos de Aseguradora',
'Regresar a cobros' => 'Regresar a cobros',
'Representante' => 'Representante',
'RFC corto: 12 posiciones para provs, 13 para personas' => 'El RFC es muy corto: 12 posiciones para provs, 13 para personas',
'RFC largo: 12 posiciones para provs, 13 para personas' => 'El RFC es muy largo: 12 posiciones para provs, 13 para personas',
'RFC' => 'RFC',
'Selecc forma de pago' => 'Seleccione una forma de pago.',
'Seleccione...' => 'Seleccione...',
'Seleccione' => 'Seleccione...',
'Se necesita al menos un dato para buscar' => 'Se necesita al menos un dato para buscar.',
'teléfono debe tener lada y número local' => 'El número de teléfono debe tener lada y número local:',
'Teléfono' => 'Teléfono',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
'' => '',
);
if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}


/* Página de idiomas para aseguradoras */ 
