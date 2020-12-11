<?php
// Valores de Configuración de Instancia
			$pregvals = "SELECT val_nombre, val_numerico, val_texto, val_arreglo FROM  " . $dbpfx . "valores";
			$matrvals = mysql_query($pregvals) or die ('ERROR: Falla en selección de valores! '.$pregvals);
			$valor = array(); $nomarr = '';
			while($res = mysql_fetch_array($matrvals)) {
				if($res['val_arreglo'] == 1) {
//					$valarr[$res['val_nombre']][] = [$res['val_numerico'], $res['val_texto']];
					$valarr[$res['val_nombre']][$res['val_numerico']] = $res['val_texto'];
				} else {
					$valor[$res['val_nombre']] = [$res['val_numerico'], $res['val_texto']];
// ------ Conversión de datos de tabla a variables de configuración.
					if($res['val_numerico'] != '') { $$res['val_nombre'] = $res['val_numerico']; }
					else { $$res['val_nombre'] = $res['val_texto']; }
// ------
				}
			}

// ------ Conectando a ASEBase para obtener datos de Variables comunesa todas las instancias ---------------

			mysql_close();
			mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
			mysql_select_db('ASEBase') or die('Falló la seleccion la DB ASEBase');
		
			$preg1 = "SELECT val_nombre, val_numerico, val_texto, val_arreglo FROM valores";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de valores comunes! " . $preg1);
			while ($res = mysql_fetch_array($matr1)) {
				if($res['val_arreglo'] == 2) {
					$valtra[$res['val_nombre']][] = [$res['val_numerico'], $res['val_texto']];
//					$valtra[$res['val_nombre']][$res['val_numerico'] = $res['val_texto'];
				}
// ------
			}
			mysql_close();
			mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
			mysql_select_db($dbnombre) or die('Falló la seleccion la DB ' . $dbnombre);

// ------ Cierre de ASEBase de datos comunes -----------------------------		
			
			
?>