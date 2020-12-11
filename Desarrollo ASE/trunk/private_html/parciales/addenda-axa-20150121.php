<?php
			
			if($accion==='confirma') {
				
				if(file_exists('particular/textos/addenda-axa.php')) {
					include_once('particular/textos/addenda-axa.php');
				}
				echo '			<tr><td colspan="2">------------ Datos para Addenda --------------------</td></tr>'."\n";
				echo '			<tr><td>Proceso ID</td><td><input type="text" name="procesoid" value="' . $procesoid . '"/></td></tr>'."\n";
				echo '			<tr><td>Fecha de Prefactura</td><td><input type="text" name="fechaprefactura" value="' . $fechaprefactura . '" /> Ejemplo: 05-12-2014 09:31:16</td></tr>'."\n";
				echo '			<tr><td>Tipo</td><td><input type="text" name="Tipo" value="' . $Tipo . '" /></td></tr>'."\n";
				echo '			<tr><td>Número de Autorización</td><td><input type="text" name="NumAutorizacion" value="' . $NumAutorizacion . '" /></td></tr>'."\n";

			} elseif($accion==='imprime') {

				include_once('parciales/numeros-a-letras.php');
				$letraadden = '(' . strtoupper(letras2($total)) . ')';
				$adden = '   <if:FacturaInterfactura xmlns:if="https://www.interfactura.com/Schemas/Documentos" if:schemaLocation="https://www.interfactura.com/Schemas/Documentos https://www.interfactura.com/Schemas/Documentos/DocumentoInterfactura.xsd" Id="" TipoDocumento="Factura" TipoDocumentoId="">
   <if:Emisor RI=""/>
   <if:Receptor RI="" Activo="True"/>
   <if:Encabezado formaDePago="' . $condp . '" SubTotal="' . $subtotal . '" IVAPCT="16" Iva="' . $iva . '" Total="' . $total . '" Moneda="MXN" CondicionPago="' . $condp . '" FolioReferencia="' . $agencia_serie . $fact_num . '" Descuento="' . $descuento . '" Deducible="' . $deducible . '" ProcesoId="' . $procesoid . '" FolioPrefactura="' . $procesoid . '" FechaPrefactura="' . $fechaprefactura . '" ImporteNeto="' . $total . '" ImporteBruto="' . $subtotal . '" importeConLetra="' . $letraadden . '" TipoFacturacion="AUTOS" Siniestro="' . $reporte . '" Lesionado="" ModeloAutomovil="' . $tipo . '" PlacasAutomovil="' . $placas . '" Marca="' . $marca . '" Tipo="' . $Tipo . '" AnoAutomovil="' . $modelo . '" NumPoliza="' . $poliza . '" NumAutorizacion="' . $NumAutorizacion . '" TipoComprobante="INGRESO">'."\n";
				$renglon = 1;
				foreach ($cantext as $k => $v) {
					if($v > 0) {
						$adden .= '	    <if:Cuerpo Renglon="' . $renglon . '" Cantidad="' . $v . '" Concepto="' . $descext[$k] . '" PUnitario="' . $precext[$k] . '" Importe="' . $impext[$k] . '"/>'."\n";
						$renglon++;
					}
				}
				$adden .= '	   </if:Encabezado>
   </if:FacturaInterfactura>
'."\n";				

// ---------------   Inserta addenda en xml ya timbrado  ------------------

				$xml = new DOMDocument();
				$xml->loadXML($cfdi) or die("\n\n\nXML timgrado no valido antes de addenda");
// Valida que la addenda sea un documento xml válido
				$aaxml = new DOMDocument();
				$aaxml->loadXML($adden) or die("\n\n\nXML de addenda no valido\n");
// # Extrae la addenda (si existe)
				$xmlaa = new DOMDocument('1.0', 'UTF-8');
// # Extrae el nodo
				$paso = $aaxml->getElementsByTagNameNS('https://www.interfactura.com/Schemas/Documentos', 'FacturaInterfactura')->item(0);
				$paso = $xmlaa->importNode($paso, true);
				$xmlaa->appendChild($paso);
				unset($paso);
// # Agrega la addenda al CFDi 
				$add = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Addenda')->item(0);
				$aa = $xmlaa->getElementsByTagNameNS('https://www.interfactura.com/Schemas/Documentos', 'FacturaInterfactura')->item(0);
				$aa = $xml->importNode($aa, true);
				$add->appendChild($aa);
// Guarda el CFDi
				$cfdi = $xml->saveXML();
				unset($aaxml, $xml, $xmlaa, $paso, $addenda, $aa);
			}

?>