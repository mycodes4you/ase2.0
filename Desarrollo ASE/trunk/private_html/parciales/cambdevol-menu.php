<?php

// ------ Include de sub menú de Cambios y Devoluciones  

	echo '
				<div class="row">
					<div class="col-sm-12">
						<div>
	  						<a href="cambdevol.php?accion=pendientes"><img src="idiomas/' . $idioma . '/imagenes/solicitudes-pendientes.png" alt="Solicitudes Pendientes" title="Solicitudes Pendientes"></a>
	  						<a href="cambdevol.php?accion=autorizados"><img src="idiomas/' . $idioma . '/imagenes/solicitudes-aprobadas_h.png" alt="Devoluciones o Reemplazos Autorizados" title="Devoluciones o Reemplazos Autorizados"></a>
					  	</div>
					</div>
				</div>'."\n";

?>