<?php
// Valores de Configuración de Instancia
			$pregvals = "SELECT val_nombre, val_numerico, val_texto, val_arreglo FROM  " . $dbpfx . "valores";
			$matrvals = mysql_query($pregvals) or die ('ERROR: Falla en selección de valores! '.$pregvals);
			$valor = array(); $nomarr = '';
			while($res = mysql_fetch_array($matrvals)) {
				if($res['val_arreglo'] == 1) {
					$valarr[$res['val_nombre']][] = [$res['val_numerico'], $res['val_texto']];
// ------ Conversión de datos de tabla a arreglos de configuración.
					if($nomarr != $res['val_nombre']) {
						unset($trans);
						$nomarr = $res['val_nombre'];
					}
					$trans[$res['val_numerico']] = $res['val_texto'];
					$$res['val_nombre'] = $trans;
// ------
				} else {
					$valor[$res['val_nombre']] = [$res['val_numerico'], $res['val_texto']];
// ------ Conversión de datos de tabla a variables de configuración.
					if($res['val_numerico'] != '') { $$res['val_nombre'] = $res['val_numerico']; }
					else { $$res['val_nombre'] = $res['val_texto']; }
// ------
				}
			}
?>
