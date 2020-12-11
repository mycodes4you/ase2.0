<?php

					echo '                          <a href="entrega.php?accion=garantia&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/formato-de-entrega.png" alt="Formato de Entrega" title="Formato de Entrega"></a><br>'."\n";

                                        echo '                          <a href="ingreso.php?accion=consultar&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/hoja-ingreso.png" alt="Hoja de Ingreso" title="Hoja de Ingreso"></a><br>'."\n";

if((($orden['orden_estatus'] >= 4 && $orden['orden_estatus'] <= 14) || $orden['orden_estatus'] == 21) && ($_SESSION['rol02'] == '1' || $_SESSION['rol12'] == '1')) {
					echo '				<a href="factura-3.3.php?accion=consultar&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/facturas-3.3.png" alt="Crear Factura" title="Crear Factura"></a><br>'."\n";
}

/*
if($orden['orden_estatus'] == 1 || ($orden['orden_estatus'] >= 2 && $orden['orden_estatus'] <= 30 && ($_SESSION['usuario'] == 3003 || $_SESSION['usuario'] == 3006 || $_SESSION['usuario'] == 1000 || $_SESSION['usuario'] == 701))) {
					echo '                          <a href="presupuestos.php?accion=adicional&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/agregar-tarea.png" alt="Agregar otra tarea" title="Agregar otra tarea"></a><br>'."\n";
}
*/

?>

