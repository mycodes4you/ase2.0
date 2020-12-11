<?php
			
			if($accion==='confirma') {
				
				if(file_exists('particular/textos/addenda-qualitas.php')) {
					include('particular/textos/addenda-qualitas.php');
				}
				if(!$fechaprefactura || $fechaprefactura == '') { $fechaprefactura = date('d-m-Y H:i:s', time());}
				echo '			<tr><td colspan="2">------------ Datos para Addenda --------------------</td></tr>'."\n";
				echo '			<tr><td>Código de emisor</td><td><input type="text" name="CdgIntEmisor" value="' . $CdgIntEmisor . '"/></td></tr>'."\n";
				echo '			<tr><td>Nombre de emisor</td><td><input type="text" name="EmisorNombre" value="' . $EmisorNombre . '"/></td></tr>'."\n";
				echo '			<tr><td>Email de emisor</td><td><input type="text" name="EmisorEmail" value="' . $EmisorEmail . '"/></td></tr>'."\n";
				echo '			<tr><td>Teléfono de emisor</td><td><input type="text" name="EmisorTelefono" value="' . $EmisorTelefono . '"/></td></tr>'."\n";
				echo '			<tr><td>Tipo de receptor</td><td><input type="text" name="ReceptorTipo" value="' . $ReceptorTipo . '"/></td></tr>'."\n";
				echo '			<tr><td>Nombre de receptor</td><td><input type="text" name="ReceptorNombre" value="' . $ReceptorNombre . '"/></td></tr>'."\n";
				echo '			<tr><td>Email de receptor</td><td><input type="text" name="ReceptorEmail" value="' . $ReceptorEmail . '"/></td></tr>'."\n";
				echo '			<tr><td>Teléfono de receptor</td><td><input type="text" name="ReceptorTelefono" value="' . $ReceptorTelefono . '"/></td></tr>'."\n";
				echo '			<tr><td>Número de Reporte</td><td><input type="text" name="NroReporte" value="' . $NroReporte . '" /></td></tr>'."\n";
				echo '			<tr><td>Número de Inciso</td><td><input type="text" name="INC" value="' . $INC . '" /></td></tr>'."\n";
				echo '			<tr><td>Tipo de Cliente</td><td><input type="text" name="TpoCliente" value="' . $TpoCliente . '" /></td></tr>'."\n";
				echo '			<tr><td>Vehículo Tipo</td><td><input type="text" name="VehiculoTipo" value="' . $VehiculoTipo . '" /></td></tr>'."\n";
				$MontoMO = number_format($MontoMO, 2, '.', '');
				$MontoPartes = number_format($MontoPartes, 2, '.', '');
				echo '			<tr><td>Monto de Mano de Obra</td><td><input type="text" name="MontoMO" value="' . $MontoMO . '" /></td></tr>'."\n";
				echo '			<tr><td>Monto de Refacciones</td><td><input type="text" name="MontoPartes" value="' . $MontoPartes . '" /></td></tr>'."\n";
				echo '			<tr><td>Oficina de entrega de Factura</td><td><input type="text" name="oficinaEntrega" value="' . $oficinaEntrega . '" /></td></tr>'."\n";
				if($Recalcula == '') {
					echo '			<tr><td>Folio Electrónico 1</td><td><input type="text" name="folioElectronico1" value="' . $folioElectronico1 . '" /></td></tr>'."\n";
					echo '			<tr><td>Folio Electrónico 2</td><td><input type="text" name="folioElectronico2" value="' . $folioElectronico2 . '" /></td></tr>'."\n";
					echo '			<tr><td>Folio Electrónico 3</td><td><input type="text" name="folioElectronico3" value="' . $folioElectronico3 . '" /></td></tr>'."\n";
					echo '			<tr><td>Folio Electrónico 4</td><td><input type="text" name="folioElectronico4" value="' . $folioElectronico4 . '" /></td></tr>'."\n";
				} elseif($Recalcula == 'Recalcula') {
					if($folioElectronico1 != '000000000000' && $folioElectronico1 != '') {
						echo '			<tr><td>Folio Electrónico 1</td><td><input type="text" name="folioElectronico1" value="' . $folioElectronico1 . '" /></td></tr>'."\n";
					}
					if($folioElectronico2 != '000000000000' && $folioElectronico2 != '') {
						echo '			<tr><td>Folio Electrónico 2</td><td><input type="text" name="folioElectronico2" value="' . $folioElectronico2 . '" /></td></tr>'."\n";
					}
					if($folioElectronico3 != '000000000000' && $folioElectronico3 != '') {
						echo '			<tr><td>Folio Electrónico 3</td><td><input type="text" name="folioElectronico3" value="' . $folioElectronico3 . '" /></td></tr>'."\n";
					}
					if($folioElectronico4 != '000000000000' && $folioElectronico4 != '') {
						echo '			<tr><td>Folio Electrónico 4</td><td><input type="text" name="folioElectronico4" value="' . $folioElectronico4 . '" /></td></tr>'."\n";
					}
				}
				if($Recalcula == 'Recalcula' && $dedu > 0 && ($bancoDepositoDeducible == 'X' || $bancoDepositoDeducible == '' || $fechaDepositoDeducible == '0000-00-00' || $fechaDepositoDeducible == '' || $refDepositoDeducible == '')) {
					$Recalcula = '';
				}
				$deduobligatorio = '';
				if($dedu > 0) {
					$deduobligatorio = 'style="font-weight: bold; color: red;"';
				}
				echo '			<tr><td><span ' . $deduobligatorio . '>Banco de depósito de deducible</span></td><td><input type="text" name="bancoDepositoDeducible" value="' . $bancoDepositoDeducible . '" /></td></tr>'."\n";
				echo '			<tr><td><span ' . $deduobligatorio . '>Fecha de depósito de deducible</span></td><td><input type="text" name="fechaDepositoDeducible" value="' . $fechaDepositoDeducible . '" /></td></tr>'."\n";
				echo '			<tr><td><span ' . $deduobligatorio . '>Folio de referencia de ficha de depósito de deducible</span></td><td><input type="text" name="refDepositoDeducible" value="' . $refDepositoDeducible . '" /></td></tr>'."\n";
				echo '			<tr><td>Monto de demerito</td><td><input type="text" name="montoDemerito" value="' . $montoDemerito . '" /></td></tr>'."\n";
				echo '			<tr><td>Banco de depósito de demerito</td><td><input type="text" name="bancoDepositoDemerito" value="' . $bancoDepositoDemerito . '" /></td></tr>'."\n";
				echo '			<tr><td>Fecha de depósito de demerito</td><td><input type="text" name="fechaDepositoDemerito" value="' . $fechaDepositoDemerito . '" /></td></tr>'."\n";
				echo '			<tr><td>Folio de referencia de ficha de depósito de demerito</td><td><input type="text" name="refDepositoDemerito" value="' . $refDepositoDemerito . '" /></td></tr>'."\n";
				

			} elseif($accion==='imprime') {
				include_once('parciales/numeros-a-letras.php');
				$letraadden = strtoupper(letras2($total));
				$pctdesc = ($descuento / $subtotal) * 100;
				$adden = '		<ECFD version="1.0">
			<Documento ID="T33' . $agencia_serie . $fact_num . '">
				<Encabezado>
					<IdDoc>
						<NroAprob>00000</NroAprob>
						<AnoAprob>0000</AnoAprob>
						<Tipo>33</Tipo>
						<Serie>' . $agencia_serie . '</Serie>
						<Folio>' . $fact_num . '</Folio>
						<Estado>ORIGINAL</Estado>
						<NumeroInterno>01</NumeroInterno>
						<FechaEmis>' . $fecha_emision . '</FechaEmis>
						<FormaPago>PAGO EN UNA SOLA EXHIBICION</FormaPago>
						<Area>
							<IdArea>001</IdArea>
							<IdRevision>003</IdRevision>
						</Area>
					</IdDoc>
					<ExEmisor>
						<RFCEmisor>' . $agencia_rfc . '</RFCEmisor>
						<NmbEmisor>' . $agencia_razon_social . '</NmbEmisor>
						<CodigoExEmisor>
							<TpoCdgIntEmisor>EXT</TpoCdgIntEmisor>
							<CdgIntEmisor>' . $CdgIntEmisor . '</CdgIntEmisor>
						</CodigoExEmisor>
						<DomFiscal>
							<Calle>' . $agencia_calle . '</Calle>
							<NroExterior>' . $agencia_numext . '</NroExterior>
							<Colonia>' . $agencia_colonia . '</Colonia>
							<Municipio>' . $agencia_municipio . '</Municipio>
							<Estado>' . $agencia_estado . '</Estado>
							<Pais>MEXICO</Pais>
							<CodigoPostal>' . $agencia_cp . '</CodigoPostal>
						</DomFiscal>
						<LugarExped>
							<Calle>' . $agencia_calle . '</Calle>
							<NroExterior>' . $agencia_numext . '</NroExterior>
							<Colonia>' . $agencia_colonia . '</Colonia>
							<Municipio>' . $agencia_municipio . '</Municipio>
							<Estado>' . $agencia_estado . '</Estado>
							<Pais>MEXICO</Pais>
							<CodigoPostal>' . $agencia_cp . '</CodigoPostal>
						</LugarExped>
						<ContactoEmisor>
							<Tipo>otro</Tipo>
							<Nombre>' . $EmisorNombre . '</Nombre>
							<eMail>' . $EmisorEmail . '</eMail>
							<Telefono>' . $EmisorTelefono . '</Telefono>
						</ContactoEmisor>
					</ExEmisor>
					<ExReceptor>
						<RFCRecep>' . $cliente['rfc'] . '</RFCRecep>
						<NmbRecep>' . $cliente['nombre'] . '</NmbRecep>
						<DomFiscalRcp>
							<Calle>' . $cliente['calle'] . '</Calle>
							<NroExterior>' . $cliente['numext'] . '</NroExterior>
							<Colonia>' . $cliente['colonia'] . ' ' . $cliente['municipio'] . '</Colonia>
							<Estado>' . $cliente['estado'] . '</Estado>
							<Pais>MEXICO</Pais>
							<CodigoPostal>' . $cliente['cp'] . '</CodigoPostal>
						</DomFiscalRcp>
						<LugarRecep>
							<Calle>' . $cliente['calle'] . '</Calle>
							<NroExterior>' . $cliente['numext'] . '</NroExterior>
							<Colonia>' . $cliente['colonia'] . ' ' . $cliente['municipio'] . '</Colonia>
							<Estado>' . $cliente['estado'] . '</Estado>
							<Pais>MEXICO</Pais>
							<CodigoPostal>' . $cliente['cp'] . '</CodigoPostal>
						</LugarRecep>
						<ContactoReceptor>
							<Tipo>' . $ReceptorTipo . '</Tipo>
							<Nombre>' . $ReceptorNombre . '</Nombre>
							<eMail>' . $ReceptorEmail . '</eMail>
							<Telefono>' . $ReceptorTelefono . '</Telefono>
						</ContactoReceptor>
					</ExReceptor>
					<Totales>
						<Moneda>MXN</Moneda>
						<SubTotal>' . $subtotal . '</SubTotal>
						<MntDcto>' . $descuento . '</MntDcto>
						<PctDcto>' . $pctdesc . '</PctDcto>
						<MntImp>' . $iva . '</MntImp>
						<VlrPagar>' . $total . '</VlrPagar>
						<VlrPalabras>' . $letraadden . '</VlrPalabras>
					</Totales>
					<ExImpuestos>
						<TipoImp>IVA</TipoImp>
						<TasaImp>16</TasaImp>
						<MontoImp>' . $iva . '</MontoImp>
					</ExImpuestos>
					<Poliza>
						<Tipo>autos</Tipo>
						<Numero>' . $poliza . '</Numero>
						<INC>' . $INC . '</INC>
						<TpoCliente>' . $TpoCliente . '</TpoCliente>
						<NroReporte>' . $NroReporte . '</NroReporte>
						<NroSint>' . $reporte . '</NroSint>
					</Poliza>
					<Vehiculo>
						<Tipo>' . $VehiculoTipo . '</Tipo>
						<Marca>' . $marca . '</Marca>
						<Modelo>' . $tipo . '</Modelo>
						<Ano>' . $modelo . '</Ano>
						<Color>' . $color . '</Color>
						<NroSerie>' . $vin . '</NroSerie>
						<Placa>' . $placas . '</Placa>
					</Vehiculo>
				</Encabezado>'."\n";

				$renglon = 1;
				foreach ($cantext as $k => $v) {
					if($v > 0) {
						$adden .= '				<Detalle>
					<NroLinDet>' . $renglon . '</NroLinDet>
					<DscLang>ES</DscLang>
					<DscItem>' . $descext[$k] . '</DscItem>
					<QtyItem>' . $v . '</QtyItem>
					<UnmdItem>' . $uniext[$k] . '</UnmdItem>
					<PrcNetoItem>' . $precext[$k] . '</PrcNetoItem>
					<MontoNetoItem>' . $impext[$k] . '</MontoNetoItem>
				</Detalle>
				<Referencia>
					<NroLinRef>' . $renglon . '</NroLinRef>
					<TpoDocRef>FE</TpoDocRef>
					<SerieRef>' . $agencia_serie . '</SerieRef>
					<FolioRef>' . $fact_num . '</FolioRef>
					<RazonRef>' . $descext[$k] . '</RazonRef>
				</Referencia>'."\n";
						$renglon++;
					}
				}

				$adden .= '				<TimeStamp>' . $fecha_emision . '</TimeStamp>
			</Documento>
			<Personalizados>
				<campoString name="montoManoObra">' . $MontoMO . '</campoString>
				<campoString name="montoRefacciones">' . $MontoPartes . '</campoString>
				<campoString name="fechaFiniquito">' . $fecha_emision . '</campoString>
				<campoString name="fechaEntregaRefacciones">' . $fecha_emision . '</campoString>
				<campoString name="oficinaEntregaFactura">' . $oficinaEntrega . '</campoString>'."\n";
				if(isset($folioElectronico1) && $folioElectronico1 != '000000000000' && $folioElectronico1 != '') {
					$adden .= '				<campoString name="folioElectronico">' . $folioElectronico1 . '</campoString>'."\n";
				}
				if(isset($folioElectronico2) && $folioElectronico2 != '000000000000' && $folioElectronico2 != '') {
					$adden .= '				<campoString name="folioElectronico">' . $folioElectronico2 . '</campoString>'."\n";
				}
				if(isset($folioElectronico3) && $folioElectronico3 != '000000000000' && $folioElectronico3 != '') {
					$adden .= '				<campoString name="folioElectronico">' . $folioElectronico3 . '</campoString>'."\n";
				}
				if(isset($folioElectronico4) && $folioElectronico4 != '000000000000' && $folioElectronico4 != '') {
					$adden .= '				<campoString name="folioElectronico">' . $folioElectronico4 . '</campoString>'."\n";
				}
				$adden .= '				<campoString name="montoDeducible">' . $deducible . '</campoString>
				<campoString name="bancoDepositoDeducible">' . $bancoDepositoDeducible . '</campoString>
				<campoString name="fechaDepositoDeducible">' . $fechaDepositoDeducible . '</campoString>'."\n";
				if($refDepositoDeducible != '') {
				$adden .= '				<campoString name="folioFicha_ReferenciaDeducible">' . $refDepositoDeducible . '</campoString>'."\n";
				}
				$adden .= '				<campoString name="montoDemerito_Recupero">' . $montoDemerito . '</campoString>
				<campoString name="bancoDepositoDemerito_Recupero">' . $bancoDepositoDemerito . '</campoString>
				<campoString name="fechaDepositoDemerito_Recupero">' . $fechaDepositoDemerito . '</campoString>'."\n";
				if($refDepositoDemerito != '') {
				$adden .= '				<campoString name="folioFicha_ReferenciaDemerito">' . $refDepositoDemerito . '</campoString>'."\n";
				}
				$adden .= '				<campoString name="UUID">' . $res_uuid . '</campoString>'."\n";
				$adden .= '
			</Personalizados>
		</ECFD>
	'."\n";				
		
//		file_put_contents(DIR_DOCS.'adden.xml', $adden);


// ---------------   Inserta addenda en xml ya timbrado  ------------------

// Carga el cfdi ya timbrado
				$xml = new DOMDocument();
				$xml->loadXML($cfdi) or die("\n\n\nXML timgrado antes de addenda");
// Carga la addenda y valida que sea un documento xml válido
				$aaxml = new DOMDocument();
				$aaxml->loadXML($adden) or die("\n\n\nXML de addenda no valido\n");
// # Extrae la addenda (si existe)
				$xmlaa = new DOMDocument('1.0', 'UTF-8');
// # Extrae los nodos
				$aadoc = $aaxml->getElementsByTagName('ECFD')->item(0);
				$aadoc = $xmlaa->importNode($aadoc, true);
				$xmlaa->appendChild($aadoc);
				unset($aadoc);
				
// # Agrega la addenda al CFDi 
				$add = $xml->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Addenda')->item(0);
				$aa = $xmlaa->getElementsByTagName('ECFD')->item(0);
				$aa = $xml->importNode($aa, true);
				$add->appendChild($aa);
// Guarda el CFDi
				$cfdi = $xml->saveXML();
				unset($aaxml, $xml, $aa, $add, $xmlaa);
			}

?>