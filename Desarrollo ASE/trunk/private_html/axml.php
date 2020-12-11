<?php
$respuesta = file_get_contents('acuse.xml');
				$xml = simplexml_load_string($respuesta) or die("\n\n\nXML no valido");
				$uuidr = $xml->Folios->UUID;
				$euidr = $xml->Folios->EstatusUUID;
//				echo "EstatusUUID " . $euidr . '<br>';
				if($euidr == '201' || $euidr == '202') {
					$msj = 'Factura Cancelada';
					$nr = 'CANCELADA';
				} else {
					$msj = 'La factura no fue calcelada, error ' . $euidr . '<br>';
					$nr = 'ERROR-'.$euidr;
					$error = 'si';
				}
				$nombre_acuse = 'XXX-'.$nr.'-' . $uuidr;
				$xml->asXml('documentos/'.$nombre_acuse.'.xml');
echo $msj;
?>

