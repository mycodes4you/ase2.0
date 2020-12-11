<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
include('parciales/funciones.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
} 

	include('idiomas/' . $idioma . '/ordenes-de-trabajo.php');
	include('parciales/encabezado.php'); 
	echo '	<div id="body">' . "\n";
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">'."\n";
	echo '<div class="row">';
	echo ' <div class="col-lg-6 col-md-8 col-md-12">';

	$funnum = 1040040;

	if (validaAcceso('1040005', $dbpfx) == '1' || ($_SESSION['codigo'] < '70' || $_SESSION['codigo'] > '75')) {
		echo '			<div class="col-lg-6 col-md-12 col-sm-12">'."\n";
		echo '				<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
					<h3>Consultar Orden de Trabajo</h3>
					<div>
						<form action="ordenes.php?accion=consultar" method="get">
						<input type="hidden" name="accion" value="consultar" />
						<div style="float:left; width:130px;">Número de Orden: </div><div><input type="text" id="orden_id" name="orden_id" size="10" maxlength="11" /><br style="clear:left;"></div>
						<div style="width:130px;"><input type="submit" class="btn btn-success btn-md"value="Enviar" /><br style="clear:left;"></div>
						</form>
					</div>
				</div>';

		echo '	<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
					<h3>Consultar Presupuestos Previos</h3>
					<div>
						<form action="ordenes.php?accion=listar" method="get">
						<input type="hidden" name="accion" value="listar" />
						<div style="float:left; width:130px;">Número de Presupuesto: </div><div><input type="text" name="previa_id" size="10" maxlength="11" /><br style="clear:left;"></div>
						<div style="width:130px;"><input type="submit" class="btn btn-success btn-md"value="Enviar" /><br style="clear:left;"></div>
						</form>
					</div>
				</div>';

		echo '	<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
					<h3>Buscar Orden de Trabajo o Presupuestos Previos</h3>
					<form action="ordenes.php?accion=listar" method="post">
					<div>
						<div style="float:left; width:130px;">' . $lang['Número de Placas'] . '</div><div><input type="text" name="placas" size="10" maxlength="30" /><br style="clear:left;"></div>
						<div style="float:left; width:130px;">' . $lang['Número de Siniestro'] . '</div><div><input type="text" name="siniestro" size="10" maxlength="20" /><br style="clear:left;"></div>
						<div style="float:left; width:130px;">' . $lang['Tipo o Modelo'] . '</div><div><input type="text" name="tipo" size="10" maxlength="30" /><br style="clear:left;"></div>
						<div style="float:left; width:130px;">' . $lang['Torre'] . '</div><div><input type="text" name="torre" size="10" maxlength="20" /><br style="clear:left;"></div>
						<div><input type="submit" class="btn btn-success btn-md"value="Enviar" /></div>
					</div>
					</form>
				</div>'."\n";
		echo '			</div>'."\n";				
	}
	if($ordprepago == 1) {
		$preg2 = "SELECT orden_id, orden_estatus FROM " . $dbpfx . "ordenes WHERE orden_id >= '$ordinicred'";
		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de ordenes! ".$preg2);
		while($ord = mysql_fetch_array($matr2)) {
			if($ord['orden_estatus'] < 30 || $ord['orden_estatus'] == 99 || $ord['orden_estatus'] == 210) {
				$ordcob++;
			}
		}
		$orddisp = ($ordcomppre - $ordcob) + $ordprecred;
//		echo $orddisp;
		if($orddisp <= '0') { $asecerrado = '1';}
	}
	if ($_SESSION['codigo'] < '2000') {
		if($_SESSION['codigo'] != '60' && $_SESSION['codigo'] != '70' && $_SESSION['codigo'] != '75') {
			
			echo '			<div class="col-lg-6 col-md-12 col-sm-12">'."\n";
			$result = mysql_query("SHOW TABLE STATUS WHERE name = '" . $dbpfx . "ordenes'");
			$data = mysql_fetch_assoc($result);
			$proximo = $data['Auto_increment'];
			echo '				<div class="obscuro espacio" style="border-radius: 10px;box-shadow: 3px 4px 10px 0px #484848;margin-bottom: 10px;">
					<h3 style="color:#d00;">Nuevo Ingreso al Taller</h3>
					<div>'."\n";
			if($asecerrado != '1') {
				echo '					<form action="ordenes.php?accion=recepcion" name="recibe" id="recibe" method="post">'."\n";
				if($confolio == 1) {
					echo '';
				} else {
					echo '						<div>PROXIMO FOLIO: <span style="color:#d00;font-weight:bold;font-size:1.3em;">' . $proximo . '</span></div>'."\n";
				}
				echo '						<div style="float:left; width:130px;">' . $lang['Serie o Placas'] . '</div><div><input type="text" name="placas" size="10" maxlength="30" /></div>
						<div style="float:left; width:130px;"><input type="submit" class="btn btn-success btn-md"value="Enviar" onclick="pregunta(recibe);return false;" /></div>
					</form>'."\n";
			}
			if($orddisp <= $ordpreavis && $ordprepago == 1) {
				echo '						<div style="float:left;"><span style="color:#d00;font-weight:bold;font-size:1.3em;">' . $lang['Alerta de folios'] . $orddisp . $lang['avisa a Gerencia'] . '</span><br>';
				if($_SESSION['codigo'] <= '12') {
					include('parciales/formas-de-pago.php');
				}
			} else {
				echo '						<div style="float:left;"><span style="color:#2c5ba0;font-weight:bold;font-size:1.3em;">QUEDAN ' . $orddisp . ' ORDENES DE TRABAJO</span><br>';
			}
			echo '						</div><br style="clear:left;">'."\n";
			echo '					</div>'."\n";
			echo '				</div>'."\n";
		} elseif($_SESSION['codigo'] == '75') {
//			print_r($ubicaciones);
//			echo $_SESSION['localidad'];
			echo '			<div style="font-size:28px; line-height: 120%;">'."\n";
			echo '				<div class="col-lg-6 col-md-12 col-sm-12">
					<h3>Control de Entradas y Salidas del Centro de Reparación ' . $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'] . '</h3>
					<form action="ordenes.php?accion=recepcion" method="post">
					<div class="acceso">
						<div style="float:left; width:15%;">
							<input type="radio" name="movimiento" id="entrada" value="1">
							<label for="entrada">Entra</label>
						</div>
						<div>
							<input type="radio" name="movimiento" id="salida" value="2" />
							<label for="salida">Sale</label>
							<br style="clear:left;">
						</div>
					</div>
					<div>
						<div style="float:left; width:28%;">' . $lang['Número de Placas'] . '</div><div><input type="text" name="placas" value="' . $_SESSION['ord']['placas'] . '" size="10" maxlength="20" style="font-size:28px; line-height: 120%;"/><br style="clear:left;"></div>
						<div style="float:left; width:28%;">' . $lang['Kilometros'] . '</div><div><input type="text" name="kms" value="' . $_SESSION['ord']['kms'] . '" size="10" maxlength="30" style="font-size:28px; line-height: 120%;"/><br style="clear:left;"></div>'."\n";
			$preg0 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE activo = '1' AND acceso = '0' ORDER BY nombre,apellidos";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de usuarios!");
			echo '						<div style="float:left; width:28%;">' . $lang['Conductor'] . '</div><div>
							<select name="conductor" style="font-size:28px; line-height: 120%;">
								<option value="0">Seleccione</option>'."\n";
			echo '								<option value="1">Cliente</option>'."\n";
			echo '								<option value="2">Otra persona</option>'."\n";
			while ($usu = mysql_fetch_array($matr0)) {
				echo '								<option value="' . $usu['usuario'] . '">' . $usu['nombre'] . ' ' . $usu['apellidos'] . '</option>'."\n";
			}
			
			echo '							</select><br style="clear:left;"></div>'."\n";
			echo '						<div style="float:left; width:28%;">' . $lang['Observaciones'] . '</div><div><textarea name="obs" cols="28" rows="4" style="font-size:28px; line-height: 120%;">' . $_SESSION['ord']['obs'] . '</textarea><br style="clear:left;"></div>'."\n";
			unset($_SESSION['ord']);
			echo '						<div><input type="submit" class="btn btn-success btn-md"value="Enviar" style="font-size:28px; line-height: 120%;"/></div>
					</div>
					</form>
				</div>'."\n";
			echo '				<div class="col-lg-6 col-md-12 col-sm-12">'."\n";
			$preg1 = "SELECT * FROM " . $dbpfx . "comentarios WHERE interno = '5' AND usuario = '" . $_SESSION['usuario'] . "' ORDER BY bit_id DESC LIMIT 50";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de comentarios! ".$preg1);
			echo '					<div>'."\n";
			$fondo = 'claro';
			while ($com = mysql_fetch_array($matr1)) {
				echo '					<div class="' . $fondo . '" style="padding:10px;">';
				echo $com['fecha_com'] . ' -> ' . $com['comentario'];
				echo '					</div>'."\n";
				if($fondo == 'claro') { $fondo = 'obscuro'; } else { $fondo = 'claro'; }
			}
			echo '					</div>'."\n";
			echo '				</div>'."\n";
		}
		echo '			</div>'."\n";
	}

	echo '		</div>';
	echo '		</div>'."\n";
	echo '		</div>';
	echo ' 		</div>';
include('parciales/pie.php');

/* Archivo index.php */
/* e-Taller */