<?php
/*  

Ajusta los textos de acuerdo a tu idioma


*/
$titulo="Facturación de Ordenes de Trabajo | AutoShop Easy";
$keywords="administración de taller, control de taller";
$pag_desc="AutoShop Easy: Aplicación para administración de todas las etapas del proceso de un Taller Mecánico Clase Mundial.";
$pagina_actual="Facturación de Ordenes de Trabajo";
define('CONCEPTOS_0', 'REPARACION HOJALATERIA'); 
define('CONCEPTOS_1', ' Y PINTURA.'); 
define('CONCEPTOS_2', ', PINTURA Y REFACCIONES.'); 

$mes = array(
	'01' => 'Enero',
	'02' => 'Febrero',
	'03' => 'Marzo',
	'04' => 'Abril',
	'05' => 'Mayo',
	'06' => 'Junio',
	'07' => 'Julio',
	'08' => 'Agosto',
	'09' => 'Septiembre',
	'10' => 'Octubre',
	'11' => 'Noviembre',
	'12' => 'Diciembre',
);

$lang = [
	'AccesoNegado' => 'Acceso NO autorizado',
	'Clave' => 'Clave',
	'ClaveUnidad' => 'ClaveUnidad',
	'Cliente' => 'Cliente',
	'ConceptosFacturar' => 'Conceptos a Facturar',
	'condiciones_pago' => 'condiciones_pago',
	'CondPago' => 'Condiciones de Pago (OPCIONAL)',
	'Cuenta' => 'Cuenta',
	'DatosComprobante' => 'Datos del Comprobante',
	'DatosEmisor' => 'Datos del Emisor',
	'DatosReceptor' => 'Datos del Receptor',
	'DesSiMotNo'=> 'Existe descuento, pero no indicó motivo',
	'E48ClaPro' => '76122405',
	'E48ClaUni' => 'E48',
	'ElPP' => 'el Presupuesto Previo:',
	'Enviar' => 'Enviar',
	'ExisVarServ' => 'Existe más de un servicio que se puede facturar, elija el siniestro adecuado o 0 (cero) para trabajo particular:',
	'Factura' => 'Factura',
	'FacturaEnviada' => 'Los archivos de su factura fueron enviados correctamente.',
	'FacturaNoEnv' => 'Hubo un error de comunicaciones, por favor descargue desde --Documentos Asociados-- los archivos de la factura a su computadora y envíelos manualmente a su cliente desde su cuenta de correo.',
	'FacturaPara' => 'Factura para',
	'FaltaFolio' => 'Por favor coloque un número de folio',
	'FaltaMP' => 'Seleccione un método de pago para el Método de Pago',
	'FormaDePago' => 'Forma de Pago',
	'info' => 'info',
	'Incompletos' => 'Incompletos',
	'LotRef' => 'Lote de Refacciones',
	'LaOT' => 'la Orden de Trabajo:',
	'MOChaMec' => 'Mano de Obra Chapa y Mecánica',
	'MetPag' => 'Método de Pago',
	'Particular' => 'Particular',
	'Quedan' => 'Quedan',
	'¿Qué es esto?' => '¿Qué es esto?',
	'RefNoValida' => 'No es válido el número de Orden de Trabajo o Presupuesto Previo indicado.',
	'RefPorReci' => 'No se puede facturar, aún hay refacciones por recibir.',
	'Regimen' => 'RÉGIMEN FISCAL',
	'RegresarOT' => 'Regresar a la Orden de Trabajo',
	'RFC' => 'RFC',
	'RFCCompartido' => 'Los siguientes clientes comparten el mismo RFC. Seleccione a cual desea enviar los archivos de la factura.',
	'Seleccione...' => 'Seleccione...',
	'SeleccioneCVMP' => 'Seleccione una cantidad válida de métodos de pago: de 1 a',
	'SelFormPago' => 'Seleccione forma de pago',
	'SinEmail' => 'El cliente no tiene configurado un correo electrónico para el envío de sus facturas.',
	'TareasPorTerm' => 'No se puede facturar, aún hay tareas por terminar.',
	'TimbresAgot' => 'Timbres agotados, no se pueden emitir más comprobantes fiscales.',
	'TimbresDisp' => 'timbres disponibles: solicitar nuevos folios a la brevedad!',
	'UniSer' => 'Servicio',
	'usoCFDI' => 'usoCFDI',
	'UsoCFDi' => 'Uso del CFDi',
	'ValCero' => 'No se puede facturar, el importe no puede ser menor o igual a 0 cero.',
	'' => '',
	'' => '',
	'' => '',
	'' => '',
	'' => '',
	'' => '',
];

$ayuda = [
	$lang['usoCFDI'] => '			<h1>Uso del CFDI</h1>
			<div id="body">
				<div id="principal">
					<p>
						Se debe registrar la clave que corresponda al uso que le dará al comprobante fiscal el receptor.
					</p>
					<table cellspacing="1" cellpadding="2" border="1">
						<tr>
							<td style="text-align: center;"><strong>USO CFDI</strong></td>
							<td style="text-align: center;"><strong>DESCRIPCIÓN</strong></td>
							<td colspan="2">
								<table>
									<tr>
										<td colspan="2" style="text-align: center;"><strong><small>Aplica para tipo persona</strong></small>
										</td>
									</tr>
									<tr>
										<td style="text-align: center;"><strong><small>Física</small></strong>
										</td>
										<td style="text-align: center;"><strong><small>Moral</small></strong></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr><td style="text-align: center;">G01</td><td>Adquisición de mercancias</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">G02</td><td>Devoluciones, descuentos o bonificaciones</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">G03</td><td>Gastos en general</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">I01</td><td>Construcciones</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">I02</td><td>Mobilario y equipo de oficina por inversiones</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">I03</td><td>Equipo de transporte</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">I04</td><td>Equipo de computo y accesorios</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">I05</td><td>Dados, troqueles, moldes, matrices y herramental</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">I06</td><td>Comunicaciones telefónicas</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">I07</td><td>Comunicaciones satelitales</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">I08</td><td>Otra maquinaria y equipo</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
						<tr><td style="text-align: center;">D01</td><td>Honorarios médicos, dentales y gastos hospitalarios.</td><td style="text-align: center;">Sí</td><td style="text-align: center;">No</td></tr>
						<tr><td style="text-align: center;">D02</td><td>Gastos médicos por incapacidad o discapacidad</td><td style="text-align: center;">Sí</td><td style="text-align: center;">No</td></tr>
						<tr><td style="text-align: center;">D03</td><td>Gastos funerales.</td><td style="text-align: center;">Sí</td><td style="text-align: center;">No</td></tr>
						<tr><td style="text-align: center;">D04</td><td>Donativos.</td><td style="text-align: center;">Sí</td><td style="text-align: center;">No</td></tr>
						<tr><td style="text-align: center;">D05</td><td>Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).</td><td style="text-align: center;">Sí</td><td style="text-align: center;">No</td></tr>
						<tr><td style="text-align: center;">D06</td><td>Aportaciones voluntarias al SAR.</td><td style="text-align: center;">Sí</td><td style="text-align: center;">No</td></tr>
						<tr><td style="text-align: center;">D07</td><td>Primas por seguros de gastos médicos.</td><td style="text-align: center;">Sí</td><td style="text-align: center;">No</td></tr>
						<tr><td style="text-align: center;">D08</td><td>Gastos de transportación escolar obligatoria.</td><td style="text-align: center;">Sí</td><td style="text-align: center;">No</td></tr>
						<tr><td style="text-align: center;">D09</td><td>Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.</td><td style="text-align: center;">Sí</td><td style="text-align: center;">No</td></tr>
						<tr><td style="text-align: center;">D10</td><td>Pagos por servicios educativos (colegiaturas)</td><td style="text-align: center;">Sí</td><td style="text-align: center;">No</td></tr>
						<tr><td style="text-align: center;">P01</td><td>Por definir</td><td style="text-align: center;">Sí</td><td style="text-align: center;">Sí</td></tr>
					</table>
				</div>
			</div>'."\n",
	$lang['condiciones_pago'] => '			<h1>Condiciones de Pago</h1>
			<div id="body">
				<div id="principal">
					<p>
					Se pueden registrar las condiciones comerciales aplicables para el pago del comprobante fiscal, cuando existan éstas.
					</p>
					<p>
					Ejemplo:<br>
					<strong>Condiciones de Pago: 3 meses</strong>
					</p>
				</div>
			</div>'."\n",
	$lang['Clave'] => '			<h1>Clave de producto o servicio</h1>
			<div id="body">
				<div id="principal">
					<p>	En este campo se debe registrar una clave que permita clasificar los
						conceptos del comprobante como productos o servicios; se deben
						utilizar las claves de los diversos productos o servicios de
						conformidad con el catálogo c_ClaveProdServ publicado en el Portal
						del SAT, cuando los conceptos que se registren por sus actividades
						correspondan a estos.<br>
						<a href="http://www.sat.gob.mx/informacion_fiscal/factura_electronica/Paginas/Anexo_20_version3.3.aspx">Documentación SAT</a>
					</p>
				</div>
			</div>'."\n",
	$lang['ClaveUnidad'] => '			<h1>Clave Unidad</h1>
			<div id="body">
				<div id="principal">
					<p>
					En este campo se debe registrar la clave de unidad de medida estandarizada de conformidad con el catálogo publicado en el Portal del SAT, aplicable para la cantidad expresada en cada concepto. La unidad debe corresponder con la descripción del concepto.
					<br>
					<a href="http://www.sat.gob.mx/informacion_fiscal/factura_electronica/Paginas/Anexo_20_version3.3.aspx">Documentación SAT</a>
					</p>
				</div>
			</div>'."\n",
	
];

if(file_exists('particular/textos/variantes.php')) {
	include('particular/textos/variantes.php');
	$lang = array_replace($lang, $langextra);
}

/* Página de idiomas para factura */ 
