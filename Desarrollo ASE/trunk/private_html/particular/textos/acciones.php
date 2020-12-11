<?php
					if($_SESSION['rol06'] == '1') {
						echo '				<a href="ingreso.php?accion=caratula&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/caratula.png" alt="Caratula" title="Caratula"></a><br>'."\n"; }

					$esaxa = 0;
					foreach($ase2k as $ka => $va) {
						if($ase[$ka]['nic'] == 'AXA' || $ase[$ka]['nic'] == 'Axa') {
							$esaxa = 1;
						}
					}
					if($_SESSION['rol06'] == '1' && $esaxa == 1) {
						echo '				<a href="ingreso.php?accion=cartaaxa&orden_id=' . $orden_id . '"><img class="acciones" src="idiomas/' . $idioma . '/imagenes/axa.png" alt="Carta Promesa de Entrega" title="Carta Promesa de Entrega"></a><br>'."\n";
					}
?>
