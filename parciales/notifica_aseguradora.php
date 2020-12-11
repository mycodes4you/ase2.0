<?php

include('idiomas/' . $idioma . '/notifica.php');

$dato = datosVehiculo($orden_id, $dbpfx);

include ('particular/notifica_aseguradora.php');

$para = $asenoti[$aseguradora]['email'];

			$preg5 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE orden_id = '$orden_id' AND ";
			if($accion==='insertar') {
				$preg5 .= "doc_archivo LIKE '%-i-3-%' ";
			} elseif($accion==="cierra" && $arciase == '1') {
				$preg5 .= "doc_archivo = '" . $resulta['nombre'] . "' ";
			} else {
				$preg5 .= "doc_etapa = '1' ";
			}
			$preg5 .= "ORDER BY doc_id DESC LIMIT 1 ";
			
			$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de fotos de ingreso!");
			$filaimg = mysql_num_rows($matr5);
			if($filaimg > 0) {
				$resu5 = mysql_fetch_array($matr5);
				$fotos[0] = DIR_DOCS . $resu5['doc_archivo'];
			}

include ('parciales/notifica2.php');
