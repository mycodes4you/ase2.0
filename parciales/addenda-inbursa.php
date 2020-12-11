<?php
			
			if($accion==='confirma') {
				
				if(file_exists('particular/textos/addenda-inbursa.php')) {
					include_once('particular/textos/addenda-inbursa.php');
				}

				if($Recalcula != 'Recalcula') {
					echo '<input type="hidden" name="totantesdesc" value="' . $gsubt . '" />';
					$addendescuento = 0;
				} else {
					$addendescuento = $totantesdesc - $gsubt;
				}
				$addensin = explode('-', $reporte);
				$pregtipo = "SELECT c.cliente_tipo FROM " . $dbpfx . "clientes c, " . $dbpfx . "ordenes o WHERE o.orden_id = '" . $orden_id . "' AND o.orden_cliente_id = c.cliente_id";
				$matrtipo = mysql_query($pregtipo) or die("ERROR: Fallo selección! " . $pregtipo);
				$restipo = mysql_fetch_array($matrtipo);
				if($restipo['cliente_tipo'] == '1') { $cliente_tipo = 'A'; } else { $cliente_tipo = 'T'; }
				$pregsub = "SELECT sub_partes, sub_mo FROM " . $dbpfx . "subordenes WHERE orden_id = '" . $orden_id . "' AND sub_reporte = '" . $reporte . "'";
				$matrsub = mysql_query($pregsub) or die("ERROR: Fallo selección! " . $pregsub);
				$addenmo = 0; $addenpartes = 0;
				while ($ressub = mysql_fetch_array($matrsub)) {
					$addenpartes = $addenpartes + $ressub['sub_partes'];
					$addenmo = $addenmo + $ressub['sub_mo'];
				}

				echo '			<tr><td colspan="2">------------ Datos para Addenda --------------------</td></tr>'."\n";
				echo '			<tr><td>Afectado</td><td><input type="text" name="addenafectado" value="' . $cliente_tipo . '"/></td></tr>'."\n";
				echo '			<tr><td>Emisor</td><td><input type="text" name="addenemisor" value="' . $addensin[0] . '"/></td></tr>'."\n";
				echo '			<tr><td>Número</td><td><input type="text" name="addenreporte" value="' . $addensin[1] . '"/></td></tr>'."\n";
				echo '			<tr><td>Monto Deducible</td><td><input type="hidden" name="addendeducible" value="' . $dedu . '"/>' . $dedu . '</td></tr>'."\n";
				echo '			<tr><td>Descuento</td><td><input type="hidden" name="addendescuento" value="' . $addendescuento . '" />' . $addendescuento . '</td></tr>'."\n";
				echo '			<tr><td>Total Mano de Obra</td><td><input type="text" name="addenmo" value="' . $addenmo . '" /></td></tr>'."\n";
				echo '			<tr><td>Total Refacciones</td><td><input type="text" name="addenpartes" value="' . $addenpartes . '" /></td></tr>'."\n";

			} elseif($accion==='imprime') {

				include_once('parciales/numeros-a-letras.php');
				$letraadden = '(' . strtoupper(letras2($total)) . ')';

				$adden = '		<ReferenciaReceptor>
			<Siniestro Afectado="' . $addenafectado . '" Numero="' . $addenreporte . '" Emisor="' . $addenemisor . '" />
			<Deducible Importe="' . $addendeducible . '" />
			<Descuento Importe="' . $addendescuento . '" />
			<TotalManoObra Importe="' . $addenmo . '" />
			<TotalRefacciones Importe="' . $addenpartes . '" />
		</ReferenciaReceptor>
'."\n";

				if($microtiempo != '') {
					$addentemporal = 'addenda-' . $microtiempo . '.xml';
					file_put_contents(DIR_DOCS . $addentemporal, $adden);
				}

// ---------------   Inserta addenda en xml ya timbrado  ------------------

				if($microtiempo == '') {
					if($addentemporal != '') {
						unset($adden);
						$adden = file_get_contents(DIR_DOCS . $addentemporal);
					}
					$xml = new DOMDocument();
					$xml->loadXML($cfdi) or die("\n\n\nXML timbrado no valido antes de addenda");
// Valida que la addenda sea un documento xml válido
					$aaxml = new DOMDocument();
					$aaxml->loadXML($adden) or die("\n\n\nXML de addenda no valido\n");
// # Extrae la addenda (si existe)
					$xmlaa = new DOMDocument('1.0', 'UTF-8');
// # Extrae el nodo
					$paso = $aaxml->getElementsByTagName('ReferenciaReceptor')->item(0);
					$paso = $xmlaa->importNode($paso, true);
					$xmlaa->appendChild($paso);
					unset($paso);
// # Agrega la addenda al CFDi 
					$add = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Addenda')->item(0);
					$aa = $xmlaa->getElementsByTagName('ReferenciaReceptor')->item(0);
					$aa = $xml->importNode($aa, true);
					$add->appendChild($aa);
// Guarda el CFDi
					$cfdi = $xml->saveXML();
					unset($aaxml, $xml, $xmlaa, $paso, $addenda, $aa);
				}
			}

?>