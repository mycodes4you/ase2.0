<?php
/*
Ajusta los textos de acuerdo a tu idioma
*/
$titulo="Comisiones Variables del Personal";
$keywords="administración de taller, control de taller";
$pag_desc="Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Comisiones Variables del Personal";

$lang = [
	'CONSULTA DE COMISIONES' => 'CONSULTA DE COMISIONES',
	'Del:' => 'Del:',
	'Al:' => 'Al:',
	'No se pudieron procesar los siguientes elementos:' => 'No se pudieron procesar los siguientes elementos:',
	'Fecha de inicio:' => 'Fecha de inicio:',
	'Fecha de fin:' => 'Fecha de fin:',
	'Estatus:' => 'Estatus:',
	'¿Qué es esto?' => '¿Qué es esto?',
	'Usuarios:' => 'Usuarios:',
	'Todos' => 'Todos',
	'Sin recibo' => 'Sin recibo',
	'Pendientes de pago' => 'Pendientes de pago',
	'Pagadas' => 'Pagadas',
	'*Selecciona un usuario para generar  recibo' => '*Selecciona un usuario para generar  recibo',
	'CONSULTAR' => 'CONSULTAR',
	'COMISIÓN' => 'COMISIÓN',
	'O.T' => 'O.T',
	'DESCRIPCIÓN' => 'DESCRIPCIÓN',
	'USUARIO' => 'USUARIO',
	'USUARIO' => 'USUARIO',
	'FECHA EVENTO' => 'FECHA EVENTO',
	'MONTO COMISIÓN' => 'MONTO COMISIÓN',
	'FECHA GENERACIÓN' => 'FECHA GENERACIÓN',
	'ESTATUS COMISIÓN' => 'ESTATUS COMISIÓN',
	'PAGAR' => 'PAGAR',
	'BORRAR' => 'BORRAR',
	'Total $' => 'Total $',
	'¿?' => '¿?',
	'GENERAR COMISIONES' => 'GENERAR COMISIONES',
	'Configura el pago.' => 'Configura el pago.',
	'PAGO DE COMISIÓN POR' => 'PAGO DE COMISIÓN POR',
	'O.T. a incluir en las comisiones:' => 'O.T. a incluir en las comisiones:',
	'Monto a pagar por cada orden $' => 'Monto a pagar por cada orden $',
	'GENERAR' => 'GENERAR',
	'RECIBO' => 'RECIBO',
	'' => '',
	'' => '',
	'' => '',
	'' => '',
	'' => '',
	'' => '',
	'' => '',
	'' => '',
];

$ayuda = [
	$lang['¿Qué es esto?'] => '			<h1>ESTATUS DE COMISONES</h1>
			<div id="body">
				<div id="principal">
					<Strong>Sin recibo: </Strong>
					<br>
					Comisiones que fueron creadas pero aun no se ha gestionado un pago, por lo que están esperando a ser asignadas a un recibo de destajo.
					<br>
					<Strong>Pendientes de pago:</Strong>
					<br>
					Comisiones que ya fueron gestionadas con un recibo de destajo, pero aun no se ha registrado el pago del recibo.
					<br>
					<Strong>Pagadas:</Strong>
					<br>
					Comisiones cuyo recibo de destajo ya fue pagado, este es el estatus optimo de una comisión.
					
				</div>
			</div>'."\n",
	$lang['¿?'] => '			<h1>Pagar y Borrar</h1>
			<div id="body">
				<div id="principal">
					<Strong>Pagar: </Strong>
					<br>
					Comisiones que se pretende pagar, al seleccionar las comisiones y dar click en pagar se enviará la información a un prerecibo de destajo, donde posteriormente se le dara seguimiento al generar el recibo. 
					<br>
					<Strong>Borrar:</Strong>
					<br>
					Comisiones que por algún motivo no necestiamos y hemos decidido que al ser innesesariar deben de ser borradas.
					<br>
					
					<br>
					Es necesario aplicar la búsqueda de un usuario especifico para habilitar los botones "Pagar" y "Borrar".					
				</div>
			</div>'."\n",
]









/* Página de idiomas para comisiones.php */
?>