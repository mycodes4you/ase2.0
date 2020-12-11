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
	'ConMonto' => 'con un monto total de',
	'CondPago' => 'Condiciones de Pago (OPCIONAL)',
	'Cuenta' => 'Cuenta',
	'DatosComprobante' => 'Datos del Comprobante',
	'DatosEmisor' => 'Datos del Emisor',
	'DatosReceptor' => 'Datos del Receptor',
	'DebeSelUsoNC' => 'Debe seleccionar uno uso para la Nota de Crédito',
	'Desglose0' => '<strong>Resumen de Tareas:</strong> por cada una de las Tareas a facturar se crean los conceptos necesarios para agrupar las Refacciones, Mano de Obra y Consumibles.',
	'Desglose1' => '<strong>Desglosado:</strong> Se agrega un concepto detallado de cada uno de los items de todas las tareas a facturar.',
	'Desglose2' => '<strong>Auda:</strong> Sigue al formato de valuaciones AudaGold creando tres conceptos: --Mano de Obra de Chapa y Mecánica--, -Refacciones-- y --Materiales de Pintura junto con la Mano de Obra de Pintura--.',
	'Desglose3' => '<strong>Desglosado con M.O. Unificada:</strong> Se agrega un concepto detallado de cada uno de los items de todas las tareas a facturar. La Mano de Obra se transforma para dejar la cantidad en 1 (uno) y el precio unitario de la Hora de Mano de Obra se sustituye por el subtotal del concepto.',
	'Desglose4' => '<strong>Venta de Mostrador:</strong> Igual que la opción --Resumen de Tareas-- pero se utilizan los datos fiscales genéricos para nacionales en los datos del receptor de la factura.',
	'DesSiMotNo'=> 'Existe descuento, pero no indicó motivo',
	'E48ClaPro' => '76122405',
	'E48ClaUni' => 'E48',
	'ElPP' => 'el Presupuesto Previo:',
	'Enviar' => 'Enviar',
	'EstilosFacturar' => 'Estilos para organizar los conceptos a Facturar',
	'ExisVarServ' => 'Existe más de un servicio que se puede facturar, elija el siniestro adecuado o 0 (cero) para trabajo particular:',
	'Factura' => 'Factura',
	'FacturaEnviada' => 'Los archivos de su factura fueron enviados correctamente.',
	'FacturaNoEnv' => 'Hubo un error de comunicaciones, por favor descargue desde --Documentos Asociados-- los archivos de la factura a su computadora y envíelos manualmente a su cliente desde su cuenta de correo.',
	'FacturaPara' => 'Factura para',
	'FaltaFolio' => 'Por favor coloque un número de folio',
	'FaltaMP' => 'Seleccione un método de pago para el Método de Pago',
	'FormaDePago' => 'Forma de Pago',
	'ImpTotFac' => 'por el importe total de la factura',
	'info' => 'info',
	'Incompletos' => 'Incompletos',
	'LotRef' => 'Lote de Refacciones',
	'LaOT' => 'la Orden de Trabajo:',
	'MOChaMec' => 'Mano de Obra Chapa y Mecánica',
	'MotivoNC' => 'Motivo de la Nota de Crédito',
	'MetPag' => 'Método de Pago',
	'MontoExced' => 'El Valor Monetario indicado excede el máximo posible para la Nota de Crédito.',
	'MontoNC' => '<span style="color:red; font-weight:bold;">Ó </span> Monto de la Nota de Crédito como <strong>Cantidad Monetaria</strong> Máximo',
	'NCPara' => 'Nota de Crédito para la factura',
	'NoMotNC' => 'No se agregó el motivo para generar la Nota de Crédito',
	'NotaCredito' => 'Nota de Crédito',
	'NoXml' => 'No se localizó el archivo XML o el XML no es válido',
	'NumNC' => 'y el número de Nota de Crédito',
	'NoRefacturaCompleta' => 'No se ingresó el monto completo de la factura. Si recibió pagos, deberá desasociarlos de esta factura.',
	'Particular' => 'Particular',
	'PorcentajeNC' => 'Monto de la Nota de Crédito como <strong>Porcentaje del Total de Factura</strong> Máximo',
	'PrctExced' => 'El Porcenaje indicado excede el máximo posible para la Nota de Crédito.',
	'PrctOMonet' => 'Coloque sólo el Porcentaje o el Valor Monetario',
	'Quedan' => 'Quedan',
	'¿Qué es esto?' => '¿Qué es esto?',
	'RefNoValida' => 'No es válido el número de Orden de Trabajo o Presupuesto Previo indicado.',
	'RefPorReci' => 'No se puede facturar, aún hay refacciones por recibir.',
	'Regimen' => 'RÉGIMEN FISCAL',
	'RegresarOT' => 'Regresar a la Orden de Trabajo',
	'RFC' => 'RFC',
	'RFCCompartido' => 'Los siguientes clientes comparten el mismo RFC. Seleccione a cual desea enviar los archivos de la factura.',
	'SeVaUtilizar' => 'Se va a utilizar la Serie',
	'Seleccione...' => 'Seleccione...',
	'SeleccioneCVMP' => 'Seleccione una cantidad válida de métodos de pago: de 1 a',
	'SelFormPago' => 'Seleccione forma de pago',
	'SelTareaFact' => 'Seleccione las Tareas a facturar para el',
	'SinEmail' => 'El cliente no tiene configurado un correo electrónico para el envío de sus facturas.',
	'Siniestro' => 'Siniestro',
	'TareasPorTerm' => 'No se puede facturar, aún hay tareas por terminar.',
	'TimbresAgot' => 'Timbres agotados, no se pueden emitir más comprobantes fiscales.',
	'TimbresDisp' => 'timbres disponibles',
	'TrabPart' => 'Trabajo Particular',
	'UniSer' => 'Servicio',
	'usoCFDI' => 'usoCFDI',
	'UsoCFDi' => 'Uso del CFDi',
	'UsoNCPreg' => '¿Que uso dará al importe a disminuir?',
	'UsoNCTotal' => 'Refacturar el 100% de la factura',
	'UsoNCParcial' => 'Refactuar sólo el importe disminuido',
	'UsoNCDescuento' => 'Dejarlo como descuento',
	'UtilSer0' => 'Se va a utilizar la Serie<strong>',
	'UtilSer1' => '</strong>  y el número de Factura',
	'ValCero' => 'No se puede facturar, el importe no puede ser menor o igual a 0 cero.',
	'VtaMosNom' => 'VENTA PUBLICO EN GENERAL',
	'VtaMosRfc' => 'XAXX010101000',
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
