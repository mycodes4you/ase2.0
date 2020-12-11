<?php
					if((basename($_SERVER['PHP_SELF'])=='presupuestos.php' || basename($_SERVER['PHP_SELF'])=='proceso.php') && (($tarea['sub_estatus'] >= '104') && ($tarea['sub_estatus'] < '112') || ($tarea['sub_estatus'] >= '124') && ($tarea['sub_estatus'] < '130') || $tarea['sub_estatus'] == '120')) {
						echo '												<a href="presupuestos.php?accion=modificar&sub_orden_id=' . $sub_orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/modificar.png" alt="Modificar Convenio" title="Modificar Convenio"></a>'."\n";
					} 
?>
