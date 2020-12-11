<?php 
foreach($_POST as $k => $v){$$k=$v;}  // echo $k.' -> '.$v.'<br>';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';

include('parciales/funciones.php');
if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}
include('idiomas/' . $idioma . '/salidas.php');

if ($accion==='agregapv') { 
	/* no cargar encabezado */
} else {
	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}

if($accion==='listar') {
	
	$funnum = 1115030;

	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}

	
	if($limpiar == 'Restablecer Filtros') { $codigo=''; $nombre=''; $almacen=''; $marca=''; }
	echo '		<form action="salidas.php?accion=listar" method="post" enctype="multipart/form-data" name="tcliente">'."\n";
	echo '		<input type="hidden" name="codigo" value="' . $codigo . '">'."\n";
	echo '		<input type="hidden" name="nombre" value="' . $nombre . '">'."\n";
	echo '		<input type="hidden" name="almacen" value="' . $almacen . '">'."\n";
	echo '		<input type="hidden" name="marca" value="' . $marca . '">'."\n";
	echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="840">'."\n";
	echo '			<tr class="cabeza_tabla"><td colspan="4" style="text-align:left; font-size:16px;">Venta de Refacciones, Consumibles y Chatarra</td></tr>'."\n";
	echo '			<tr><td style="text-align:left;"><strong>A Quien se le Vende:</strong> <input type="radio" name="tcliente" value="1" onchange="document.tcliente.submit()"';
	if($tcliente == 1) {  echo ' checked ';}
	echo '>Operario, <input type="radio" name="tcliente" value="2" onchange="document.tcliente.submit()"';
	if($tcliente == 2) {  echo ' checked ';}
	echo '>Aseguradora, <input type="radio" name="tcliente" value="3" onchange="document.tcliente.submit()"';
	if($tcliente == 3) {  echo ' checked ';}
	echo '>Cliente</td>'."\n";
	echo '				<td>'."\n";
	if($tcliente == 1) {
		echo '					<select name="id" onchange="document.tcliente.submit()">'."\n";
		echo '						<option value="na">Selecciona Operario</option>'."\n";
		$preg0 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE activo = '1' AND acceso = 0 AND (codigo = 40 OR codigo = 60 OR codigo = 70) ORDER BY nombre";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo operarios! " . $preg0);
		while ($usr = mysql_fetch_array($matr0)) {
			echo '						<option value="' . $usr['usuario'] . '"';
			if($id == $usr['usuario']) { 
				echo ' selected ';
				$prega = " AND operario_id = '$id'";
			}
			echo '>' . $usr['nombre'] . ' ' . $usr['apellidos'] . '</option>'."\n";
		}
		echo '					</select>'."\n";
	} elseif($tcliente == 2) {
		echo '					<select name="id" onchange="document.tcliente.submit()">'."\n";
		echo '						<option value="na">Selecciona Aseguradora</option>'."\n";
		$preg0 = "SELECT aseguradora_id, aseguradora_nic FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_nic";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo aseguradoras! " . $preg0);
		while ($ase = mysql_fetch_array($matr0)) {
			echo '						<option value="' . $ase['aseguradora_id'] . '"';
			if($id == $ase['aseguradora_id']) {
				echo ' selected ';
				$prega = " AND aseguradora_id = '$id'";
			}
			echo '>' . $ase['aseguradora_nic'] . '</option>'."\n";
		}
		echo '					</select>'."\n";
	} elseif($tcliente == 3) {
		echo 'Cliente Número:<input type="text" name="id" onchange="document.tcliente.submit()" value="' . $id . '" size="4"><br>'."\n";
		$preg0 = "SELECT e.empresa_id, e.empresa_razon_social, e.empresa_rfc FROM " . $dbpfx . "empresas e, " . $dbpfx . "clientes c WHERE c.cliente_id = '$id' AND e.empresa_id = c.cliente_empresa_id";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo empresas! " . $preg0);
		$emp_num = mysql_num_rows($matr0);
		$emp = mysql_fetch_array($matr0);
		if($emp_num > 0 && $emp['empresa_razon_social'] != '') {
			echo $emp['empresa_razon_social'] . "\n";
		} else {
			echo '<span style="font-weight:bold; color:red;">Cliente sin Razon Social capturada.</span>'."\n";
		}		
	}
	echo '				</td>'."\n";
	echo '				<td>'."\n";
	if($tcliente > 0 && $id > 0) {
		$preg0 = "SELECT vp.vp_id FROM " . $dbpfx . "ventas_prod vp, " . $dbpfx . "ventas v WHERE vp.venta_id = v.venta_id AND v.venta_estatus = '0' AND (v.empresa_id = '$id' OR v.aseguradora_id = '$id' OR v.operario_id = '$id')";
		$preg0 .= $prega;
//		echo $preg0;
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo Ventas Productos! " . $preg0);
		$fila0 = mysql_num_rows($matr0);
		echo '<span style="font-size:28px; font-weight:bold; vertical-align:top;">' . $fila0 . '</span><a href="salidas.php?accion=preventa&tcliente=' . $tcliente . '&id=' . $id . '"><img src="idiomas/' . $idioma . '/imagenes/carro-de-compra.png" alt="Preventa" title="Preventa"></a>';
	}
	echo '				</td></tr>'."\n";
	echo '		</table></form>'."\n";

	echo '		<form action="salidas.php?accion=listar" method="post" enctype="multipart/form-data" name="filtros">'."\n";
	echo '				<input type="hidden" name="tcliente" value="' . $tcliente . '">'."\n";
	echo '				<input type="hidden" name="id" value="' . $id . '">'."\n";
	echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="840">'."\n";
	echo '			<tr class="obscuro">
				<td style="text-align:left; width:50%;">Buscar por codigo: <input type="text" name="codigo" id="codigo" size="15" value="' . $codigo . '" onchange="document.filtros.submit();"></td>
				<td style="width:50%;">Buscar por nombre: <input type="text" name="nombre" size="15" value="' . $nombre . '" onchange="document.filtros.submit();"></td></tr>'."\n";
	echo '			<tr class="obscuro"><td style="text-align:left;">Filtrar por Almacén: 
				<select name="almacen" size="1" onchange="document.filtros.submit();">
					<option value="">Seleccionar...</option>'."\n";
	foreach($nom_almacen as $k => $v) {
		echo '					<option value="' . $k . '"';
		if($almacen == $k) { echo ' selected="selected" ';}
		echo '>' . $v . '</option>'."\n";
	}											
	echo '				</select><br><input type="submit" name="limpiar" value="Restablecer Filtros">'."\n";
	echo '			</td>'."\n";
	echo '			<td style="vertical-align:top;">Buscar por Marca u Origen:<input type="text" name="marca" size="15" value="' . $marca . '" onchange="document.filtros.submit();">'."\n";
	echo '			</td></tr></table></form>'."\n";

	if((isset($almacen) && $almacen!='') || (isset($nombre) && $nombre!='') || (isset($codigo) && $codigo!='') || (isset($marca) && $marca!='')) {
		if(isset($almacen) && $almacen!='') { $preg .= "AND prod_almacen='" . $almacen . "' "; }
		if(isset($nombre) && $nombre!='') {
			$nomped = explode(' ', $nombre);
			if(count($nomped) > 0) {
				foreach($nomped as $kc => $vc){
					$preg .= "AND prod_nombre LIKE '%" . $vc . "%' ";
				}
			} 
		}
		if(isset($codigo) && $codigo!='') { $preg .= "AND prod_codigo LIKE '%" . $codigo . "%' "; }
		if(isset($marca) && $marca!='') { $preg .= "AND prod_marca LIKE '%" . $marca . "%' "; }
	}
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="840">'."\n";
	$preg0 = "SELECT prod_id FROM " . $dbpfx . "productos WHERE prod_activo='1' ";
	$preg0 = $preg0 . $preg;
//	echo $preg0 . '<br>';
   $matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
   $filas = mysql_num_rows($matr0);
   if($filas == 0 && $codigo!='') {
   	echo '		<tr><td colspan="2">No se encontró ningún producto con el código ' . $codigo . ',<br>¿Desea agregarlo como nuevo a la lista de productos? <a href="refacciones.php?accion=item&nuevo=1&codigo=' . $codigo . '">Agregar</a>&nbsp;</td></tr>';
   } else {
	   $renglones = 10;
	   $paginas = (round(($filas / $renglones) + 0.49999999) - 1);
   	if(!isset($pagina)) { $pagina = 0;}
	   $inicial = $pagina * $renglones;
//	echo $paginas;
		$preg1 = "SELECT prod_id, prod_marca, prod_codigo, prod_nombre, prod_cantidad_pedida, prod_cantidad_existente, prod_cantidad_disponible, prod_precio, prod_precioint, prod_almacen, prod_tangible FROM " . $dbpfx . "productos WHERE prod_activo = '1' ";
		$preg1 = $preg1 . $preg;
		$preg1 .= " GROUP BY prod_id ORDER BY prod_almacen, prod_id LIMIT " . $inicial . ", " . $renglones;
//	echo $preg1;
   	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");

		echo '			<tr><td colspan="2"><a href="salidas.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '&tcliente=' . $tcliente . '&id=' . $id . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="salidas.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '&tcliente=' . $tcliente . '&id=' . $id . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="salidas.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '&tcliente=' . $tcliente . '&id=' . $id . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="salidas.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '&tcliente=' . $tcliente . '&id=' . $id . '">Ultima</a>';
		echo '</td></tr>'."\n";
		if($tcliente >= 1 && $tcliente <= 3 && $id > 0) {
			echo '			<tr><td colspan="2" style="text-align:left;">'."\n";
			echo '				<form action="salidas.php?accion=agregapv" method="post" enctype="multipart/form-data">'."\n";
			echo '				<input type="hidden" name="tcliente" value="' . $tcliente . '">'."\n";
			echo '				<input type="hidden" name="id" value="' . $id . '">'."\n";
			echo '				<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">
					<tr><td>Almacén</td><td>Nombre</td><td>Marca u<br>Origen</td><td>Código</td><td>Precio Unitario<br>de Venta</td><td>Existencia</td><td>Disponible</td><td>Cantidad<br>a Vender</td></tr>'."\n";
			$cue = 0;
/*		echo 'Tcli: ' . $tcliente . '<br>'."\n";
		echo 'Id: ' . $id . '<br>'."\n";
*/
			while($prods = mysql_fetch_array($matr1)) {
				echo '					<tr><td>';
				echo $nom_almacen[$prods['prod_almacen']]; 
				echo '</td><td><a href="salidas.php?accion=item&prod_id=' . $prods['prod_id'] . '&pagina=' . $pagina . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '&tcliente=' . $tcliente . '&id=' . $id . '">' . $prods['prod_nombre'] . '</a></td><td>' . $prods['prod_marca'] . '</td><td>' . $prods['prod_codigo'] . '</td><td style="text-align:right;">$';
				if($tcliente == 3) {
					echo number_format($prods['prod_precio'],2);
				} else {
					echo number_format($prods['prod_precioint'],2);
				}
				echo '</td><td style="text-align:right;">' . $prods['prod_cantidad_existente'] . '</td><td style="text-align:right;">' . $prods['prod_cantidad_disponible'] . '</td>';
				echo '<td>';
				if($prods['prod_cantidad_disponible'] > '0') {
					echo '<input type="text" name="cant[' . $prods['prod_id'] . ']" size="3" maxlength="8">';
				}
				echo '</td></tr>'."\n";
				$cue++;
			}
			echo '				</table>'."\n";
			echo '				<button name="agregapv" value="agregar" type="submit">Agregar a Preventa</button>'."\n";
			echo '				</form>'."\n";
			echo '			</td>
		</tr>'."\n";
		} else {
			echo '			<tr><td colspan="2" style="text-align:center; color:red; background-color:yellow; font-size:1.2em; font-weight: bold;">Por favor selecciona a Quién se le Vende o Entrega</td></tr>'."\n";
		}
		echo '			<tr><td style="text-align: left;">&nbsp;</td><td><a href="salidas.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '&tcliente=' . $tcliente . '&id=' . $id . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="salidas.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '&tcliente=' . $tcliente . '&id=' . $id . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="salidas.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '&tcliente=' . $tcliente . '&id=' . $id . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="salidas.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '&tcliente=' . $tcliente . '&id=' . $id . '">Ultima</a>';
		echo '</td></tr>'."\n";
	}
	echo '		<tr><td colspan="2"><hr></td></tr>'."\n";
	echo '	</table>'."\n";
}

elseif($accion==='item') {
	
//	$funnum = 1115035;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	
	if($prod_id!='') {
		$preg0 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id='" . $prod_id . "'";
//	echo $preg0 . '<br>';
   	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
	   $prod = mysql_fetch_array($matr0);
	}
	
	echo '	<table cellpadding="0" cellspacing="0" border="1" class="izquierda" width="840">'."\n";
	echo '		<tr><td>Nombre</td><td>' . $prod['prod_nombre'] . '</td>';
	echo '<td rowspan="12">Histórico de 5 últimos movimientos:<br>';
	$preg4 = "SELECT * FROM " . $dbpfx . "prod_bitacora WHERE prod_id='" . $prod_id . "' ORDER BY bit_id DESC LIMIT 5";
//	echo $preg4 . '<br>';
   $matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de bitácoras!".$preg4);
   $fila4 = mysql_num_rows($matr4);
	$preg5 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE activo = '1'";
	$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de Usuario!" . $preg5);
	while($usu = mysql_fetch_array($matr5)) {
		$usr[$usu['usuario']] = $usu['nombre'] . ' ' . $usu['apellido'];
	}
   if($fila4 > 0) {
   	echo '			<table cellpadding="0" cellspacing="0" border="1" class="izquierda">'."\n";
	   while($hist = mysql_fetch_array($matr4)) {
   		echo '				<tr><td>' . $hist['fecha_evento'] . '</td><td>' . $usr[$hist['usuario']] . '</td></tr>'."\n";
   		echo '				<tr><td colspan="2">' . $hist['evento'] . '</td></tr>'."\n";
   		if($hist['motivo'] != '') { echo '				<tr><td colspan="2">' . $hist['motivo'] . '</td></tr>'."\n";}
   	}
   	echo '			</table>'."\n";
   }

	echo '</td></tr>'."\n";
	echo '		<tr><td>Marca</td><td>' . $prod['prod_marca'] . '</td></tr>'."\n";
	echo '		<tr><td>Código</td><td>' . $prod['prod_codigo'] . '</td></tr>'."\n";	
	echo '		<tr><td>Unidad</td><td>' . $prod['prod_unidad'] . '</td></tr>'."\n";
	echo '		<tr><td>Tipo</td><td>' . $valarr['prod_tipo'][$prod['prod_tangible']] . '</td></tr>'."\n";
	echo '		<tr><td>Existencias</td><td>' . $prod['prod_cantidad_existente'] . '</td></tr>'."\n";
	echo '		<tr><td>Disponibles</td><td>' . $prod['prod_cantidad_disponible'] . '</td></tr>'."\n";
	echo '		<tr><td>Precio Público</td><td>$' . number_format($prod['prod_precio'],2) . '</td></tr>'."\n";
	echo '		<tr><td>Precio Interno</td><td>$' . number_format($prod['prod_precioint'],2) . '</td></tr>'."\n";
	echo '		<tr><td>Precio de Compra</td><td>$' . number_format($prod['prod_costo'],2) . '</td></tr>'."\n";
	$margen = (($prod['prod_precio'] - $prod['prod_costo']) / $prod['prod_precio']);
	echo '		<tr><td>Margen de Utilidad</td><td>' . ($margen * 100) . '%</td></tr>'."\n";
	echo '		<tr><td>Almacén</td><td>' . $nom_almacen[$prod['prod_almacen']] . '</td></tr>'."\n";
	echo '		<tr><td>Ubicación</td><td colspan="2">' . $prod['prod_local'] . '</td></tr>'."\n";

	if(!isset($nuevo) || $nuevo=='') {
		$preg2 = "SELECT pp.op_cantidad, pp.op_costo, pp.op_pedido, pp.op_recibidos, p.prov_id, pp.op_fecha_promesa FROM " . $dbpfx . "orden_productos pp, " . $dbpfx . "pedidos p WHERE pp.prod_id='" . $prod_id . "' AND pp.op_pedido = p.pedido_id";
//		echo $preg2 . '<br>';
  		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de proveedores!");
	  	$pedidos = mysql_num_rows($matr2);
	
  		echo '		<tr><td>Pedidos</td><td colspan="2">'."\n";
		echo '		<table cellpadding="3" cellspacing="0" border="1" class="centrado">'; 
		echo '			<tr><td>Proveedor</td><td>Pedido</td><td>Cantidad</td><td>Costo</td><td>Fecha Promesa<br>de Entrega</td><td>Recibidas</td><td>Pendientes</td></tr>'."\n";
		if($pedidos > 0) {
			while($ped = mysql_fetch_array($matr2)) {
				$pendientes = $ped['op_cantidad'] - $ped['op_recibidos'];
				echo '			<tr><td>';
				if($prod['prod_tangible'] == 3) {
					echo $usr[$ped['prov_id']];
				} else {
					echo '<a href="proveedores.php?accion=consultar&prov_id=' . $ped['prov_id'] . '" target="_blank" >' . $provs[$ped['prov_id']]['nic'] . '</a>';
				}
				echo '</td><td><a href="pedidos.php?accion=consultar&pedido=' . $ped['op_pedido'] . '" target="_blank">' . $ped['op_pedido'] . '</a></td><td>' . $ped['op_cantidad'] . '</td><td style="text-align:right;">' . money_format('%n', $ped['op_costo']) . '</td><td>' . $ped['op_fecha_promesa'] . '</td><td>' . $ped['op_recibidos'] . '</td><td>' . $pendientes . '</td>'."\n";
				echo '			</tr>'."\n";
			}
		} else {
			echo '<tr><td colspan="7">No se encontró ningún pedido.</td></tr>'."\n";
		}
		echo '		</table></td></tr>'."\n";
	}
	echo '		<tr><td colspan="3" style="text-align:left;"><a href="salidas.php?accion=listar&pagina=' . $pagina . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '&tcliente=' . $tcliente . '&id=' . $id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Lista de Partes" title="Regresar a la Lista de Partes"></a></td></tr>'."\n";
	echo '	</table>'."\n";
}

elseif($accion==='agregapv') {
	$funnum = 0;
	
	if ($retorno == 1 || $_SESSION['rol08']=='1') {
		// Acceso autorizado
	} else {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Rol Almacén, ingresar Usuario y Clave correcta.');
	}

	$mensaje = '';
	$error = 'no'; 

	$sql_array = array();
	$preg0 = "SELECT * FROM " . $dbpfx . "ventas WHERE venta_tipo = '$tcliente' ";
	if($tcliente == 3) {
		$preg0 .= "AND empresa_id = '$id' ";
		$sql_array['empresa_id'] = $id;
	} elseif($tcliente == 2) {
		$preg0 .= "AND aseguradora_id = '$id' ";
		$sql_array['aseguradora_id'] = $id;
	} elseif($tcliente == 1) {
		$preg0 .= "AND operario_id = '$id' ";
		$sql_array['operario_id'] = $id;
	} else {
		$error = 'sí';
	}
	$preg0 .= "AND venta_estatus = '0'";
//	echo $preg0;
	if($error == 'no') {
		$matr0 = mysql_query($preg0) or die('Error en selección de venta! ' . $preg0);
		$fila0 = mysql_num_rows($matr0);
		if($fila0 == 0) {
			$acc = 'insertar';
		} else {
			$acc = 'actualizar';
			$vent = mysql_fetch_array($matr0);
			$param = "venta_id = '" . $vent['venta_id'] . "'";
		}
		$sql_array['venta_estatus'] = 0;
		$sql_array['venta_alerta'] = 0;
		$sql_array['venta_tipo'] = $tcliente;
		$sql_array['usuario_vende'] = $_SESSION['usuario'];
		if($acc == 'insertar') {
			$venta = ejecutar_db($dbpfx . 'ventas', $sql_array, $acc, $param);
		} else {
			ejecutar_db($dbpfx . 'ventas', $sql_array, $acc, $param);
			$venta = $vent['venta_id'];
		}
		foreach($cant as $k => $v) {
			$cant[$k] = limpiarNumero($v);
			if($v != 0) {
				$preg1 = "SELECT v.vp_id, v.cantidad, p.prod_cantidad_disponible FROM " . $dbpfx . "ventas_prod v, " . $dbpfx . "productos p WHERE v.prod_id = p.prod_id AND v.venta_id = '$venta' AND p.prod_id = '$k'";
				$matr1 = mysql_query($preg1) or die('Error en selección de productos de venta! ' . $preg1);
				$fila1 = mysql_num_rows($matr1);
				if($fila1 > 0) {
					$vp = mysql_fetch_array($matr1);
					$dif = $vp['cantidad'] + $v;
					if($dif > 0 && $v <= $vp['prod_cantidad_disponible']) {
						$preg2 = "UPDATE " . $dbpfx . "ventas_prod SET cantidad = cantidad + '$v' WHERE vp_id = '" . $vp['vp_id'] . "'";
						$matr2 = mysql_query($preg2) or die('Error en selección de productos de venta! ' . $preg2);
						$archivo = '../logs/' . time() . '-base.ase';
						$myfile = file_put_contents($archivo, $preg2 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
						$preg2 = "UPDATE " . $dbpfx . "productos SET prod_cantidad_disponible = prod_cantidad_disponible - '$v' WHERE prod_id = '$k'";
						$matr2 = mysql_query($preg2) or die('Error en selección de productos de venta! ' . $preg2);
						$archivo = '../logs/' . time() . '-base.ase';
						$myfile = file_put_contents($archivo, $preg2 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
					} elseif($dif <= 0) {
						$preg2 = "UPDATE " . $dbpfx . "productos SET prod_cantidad_disponible = prod_cantidad_disponible + '" . $vp['cantidad'] . "' WHERE prod_id = '$k'";
						$matr2 = mysql_query($preg2) or die('Error en selección de productos de venta! ' . $preg2);
						$archivo = '../logs/' . time() . '-base.ase';
						$myfile = file_put_contents($archivo, $preg2 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
						$preg2 = "DELETE FROM " . $dbpfx . "ventas_prod WHERE vp_id = '" . $vp['vp_id'] . "'";
						$matr2 = mysql_query($preg2) or die('Error en selección de productos de venta! ' . $preg2);
						$archivo = '../logs/' . time() . '-base.ase';
						$myfile = file_put_contents($archivo, $preg2 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
					}
				} else {
					$preg2 = "SELECT prod_precio, prod_precioint, prod_tangible, prod_cantidad_disponible FROM " . $dbpfx . "productos WHERE prod_id = '$k'";
					$matr2 = mysql_query($preg2) or die('Error en selección de productos de venta! ' . $preg2);
					$prod = mysql_fetch_array($matr2);
					if($v > $prod['prod_cantidad_disponible']) {
						$v = $prod['prod_cantidad_disponible'];
					}
					if($v > 0) {
						$sql_data = array('venta_id' => $venta,
							'prod_id' => $k,
							'cantidad' => $v,
							'tangible' => $prod['prod_tangible']);
						if($tcliente < '3') {
							$sql_data['precio_unitario'] = $prod['prod_precioint'];
						} else {
							$sql_data['precio_unitario'] = $prod['prod_precio'];
						}
						ejecutar_db($dbpfx . 'ventas_prod', $sql_data, 'insertar');
						$preg2 = "UPDATE " . $dbpfx . "productos SET prod_cantidad_disponible = prod_cantidad_disponible - '$v' WHERE prod_id = '$k'";
						$matr2 = mysql_query($preg2) or die('Error en selección de productos de venta! ' . $preg2);
						$archivo = '../logs/' . time() . '-base.ase';
						$myfile = file_put_contents($archivo, $preg2 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
					}
				}
			}
		}
		redirigir('salidas.php?accion=preventa&tcliente=' . $tcliente . '&id=' . $id);
	} else {
		$_SESSION['msjerror'] = 'No hubo correcta selección de comprador.';
		redirigir('salidas.php?accion=listar');
	}
}

elseif($accion==='preventa'){
	$funnum = 1115030;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);

	if ($retorno == '1' || ($solovalacc != '1' && ($_SESSION['rol08']=='1'))) {
		$msj='Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}

	if($tcliente > 0 && $id > 0) {
		if($tcliente == 1) {
			$preg0 = "SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '$id'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo operarios! " . $preg0);
			$usr = mysql_fetch_array($matr0);
			$quien = $usr['nombre'] . ' ' . $usr['apellidos'];
			$prega = " AND operario_id = '$id'";
		} elseif($tcliente == 2) {
			$preg0 = "SELECT aseguradora_razon_social FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '$id'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo aseguradoras! " . $preg0);
			$ase = mysql_fetch_array($matr0);
			$quien = $ase['aseguradora_razon_social'];
			$prega = " AND aseguradora_id = '$id'";
		} elseif($tcliente == 3) {
			$preg0 = "SELECT e.empresa_razon_social FROM " . $dbpfx . "empresas e, " . $dbpfx . "clientes c WHERE c.cliente_id = '$id' AND c.cliente_empresa_id = e.empresa_id ";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo empresas! " . $preg0);
			$emp = mysql_fetch_array($matr0);
			$quien = $emp['empresa_razon_social'];
			$prega = " AND empresa_id = '$id'";
		}
		echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega" width="840">'."\n";
		echo '			<tr class="cabeza_tabla"><td style="text-align:left; font-size:16px;">Preventa desde Almacén para ' . $quien . '</td></tr></table>'."\n";
		$preg0 = "SELECT * FROM " . $dbpfx . "ventas WHERE venta_estatus = '0' ";
		$preg0 .= $prega;
//		echo $preg0;
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Ventas! " . $preg0);
		$venta = mysql_fetch_array($matr0);
		echo '		<form action="salidas.php?accion=agregapv" method="post" enctype="multipart/form-data">'."\n";
		echo '		<table cellpadding="3" cellspacing="0" border="1" class="izquierda" width="840">'."\n";
		echo '				<tr><td>Almacén</td><td>Prod Id</td><td>Nombre</td><td>Marca</td><td>Código</td><td>Precio Unitario<br>de Venta</td><td>Cantidad<br>a Vender</td><td>Cantidad<br>aún disponible</td><td>+ Sumar<br>- Restar</td></tr>'."\n";
		$preg1 = "SELECT * FROM " . $dbpfx . "ventas_prod WHERE venta_id = '" . $venta['venta_id'] . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo Ventas Productos! " . $preg1);
		while($prod = mysql_fetch_array($matr1)) {
			$preg2 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id = '" . $prod['prod_id'] . "'";
			$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Productos! " . $preg2);
			$pro = mysql_fetch_array($matr2);
			echo '				<tr><td>';
			echo $nom_almacen[$pro['prod_almacen']]; 
			echo '</td><td>' . $prod['prod_id'] . '</td><td>' . $pro['prod_nombre'] . '</td><td>' . $pro['prod_marca'] . '</td><td>' . $pro['prod_codigo'] . '</td><td style="text-align:right;">$' . number_format($prod['precio_unitario'],2) . '</td><td style="text-align:right;">' . $prod['cantidad'] . '</td><td style="text-align:right;">' . $pro['prod_cantidad_disponible'] . '</td>';
			echo '<td><input type="text" name="cant[' . $prod['prod_id'] . ']" size="3" maxlength="8"></td></tr>'."\n";
		}
		echo '				<tr><td colspan="9"><button name="Recalcular" value="Recalcular" type="submit"><img src="idiomas/' . $idioma . '/imagenes/recalcular.png" alt="Recalcular" title="Recalcular"></button><a href="salidas.php?accion=vender&tcliente=' . $tcliente . '&id=' . $id . '&venta=' . $venta['venta_id'] .'"><img src="idiomas/' . $idioma . '/imagenes/vender.png" alt="Vender" title="Vender"></a><a href="salidas.php?accion=listar&tcliente=' . $tcliente . '&id=' . $id . '"><img src="idiomas/' . $idioma . '/imagenes/agregar-mas.png" alt="Agregar más productos" title="Agregar más productos"></a>'."\n";
		echo '					<input type="hidden" name="tcliente" value="' . $tcliente . '">'."\n";
		echo '					<input type="hidden" name="id" value="' . $id . '">'."\n";
		echo '			</td></tr>'."\n";
		echo '		</table></form>'."\n";
	} else {
		$_SESSION['msjerror'] = 'No hubo selección correcta de tipo de cliente. Reporte a Soporte Técnico AutoShop Easy';
		redirigir('salidas.php?accion=listar');
	}
}

elseif ($accion==="vender") {
	
	$funnum = 1115030;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);

	if ($retorno == '1' || ($solovalacc != '1' && ($_SESSION['rol08']=='1'))) {
		$msj='Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}

	if($tcliente > 0 && $id > 0) {
		$preg0 = "UPDATE " . $dbpfx . "ventas SET venta_estatus = '1' WHERE venta_id = '$venta'";
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Ventas! " . $preg0);
		$archivo = '../logs/' . time() . '-base.ase';
		$myfile = file_put_contents($archivo, $preg0 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
		$preg1 = "SELECT * FROM " . $dbpfx . "ventas_prod WHERE venta_id = '" . $venta . "'";
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo Ventas Productos! " . $preg1);
		while($prod = mysql_fetch_array($matr1)) {
			$preg0 = "UPDATE " . $dbpfx . "productos SET prod_cantidad_existente = prod_cantidad_existente - " . $prod['cantidad'] . " WHERE prod_id = '" . $prod['prod_id'] . "'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo Actualización de Productos! " . $preg0);
			$archivo = '../logs/' . time() . '-base.ase';
			$myfile = file_put_contents($archivo, $preg0 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);

			$sql_data_array = array('prod_id' => $prod['prod_id'],
				'tipo' => 40, // Comentarios de cambio de existencias por venta o salida...
				'evento' => 'Salida de ' . $prod['cantidad'] . ' elementos.',
				'motivo' => 'Venta ' . $venta,
				'usuario' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');


		}
		redirigir('salidas.php?accion=venta&venta=' . $venta);
	} else {
		$_SESSION['msjerror'] = 'No hubo selección correcta de tipo de cliente. Reporte a Soporte Técnico AutoShop Easy';
		redirigir('salidas.php?accion=listar');
	}
}

elseif ($accion==="venta") {
	
	$funnum = 1115030;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);

	if ($retorno == '1' || ($solovalacc != '1' && ($_SESSION['rol08']=='1'))) {
		$msj='Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	
	$venta = limpiarNumero($venta);

	$preg0 = "SELECT * FROM " . $dbpfx . "ventas WHERE venta_id = '$venta' ";
//	echo $preg0;
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de Ventas! " . $preg0);
	$ven = mysql_fetch_array($matr0);

	if($ven['venta_tipo'] > 0) {
		if($ven['venta_tipo'] == 1) {
			$preg0 = "SELECT nombre, apellidos FROM " . $dbpfx . "usuarios WHERE usuario = '" . $ven['operario_id'] . "'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo operarios! " . $preg0);
			$usr = mysql_fetch_array($matr0);
			$quien = $usr['nombre'] . ' ' . $usr['apellidos'];
		} elseif($ven['venta_tipo'] == 2) {
			$preg0 = "SELECT aseguradora_razon_social FROM " . $dbpfx . "aseguradoras WHERE aseguradora_id = '" . $ven['aseguradora_id'] . "'";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo aseguradoras! " . $preg0);
			$ase = mysql_fetch_array($matr0);
			$quien = $ase['aseguradora_razon_social'];
		} elseif($ven['venta_tipo'] == 3) {
			$preg0 = "SELECT e.empresa_razon_social FROM " . $dbpfx . "empresas e, " . $dbpfx . "clientes c WHERE c.cliente_id = '" . $ven['empresa_id'] . "' AND c.cliente_empresa_id = e.empresa_id";
			$matr0 = mysql_query($preg0) or die("ERROR: Fallo empresas! " . $preg0);
			$emp = mysql_fetch_array($matr0);
			$quien = $emp['empresa_razon_social'];
		}
			echo '		<table cellpadding="3" cellspacing="0" border="0" width="840" class="izquierda">'."\n";
			echo '			<tr>
			<td style="width:230px;"><img src="particular/logo-agencia.png" alt="' . $agencia_razon_social . '" height="80"></td><br>
		      <td style="width:400px; text-align:center;"><h2>FORMATO DE ENTREGA</h2>
		      	<br><span style="font-size:9px; line-height:6px;">Este documento NO es un recibo de pago o comprobante fiscal.<br>Solo aplica como formato de entrega.</span>.
				</td>
				<td style="width:210px; vertical-align: top; line-height:12px;">' . $agencia_direccion . '<br><br>
				Col. ' . $agencia_colonia . '.<br><br>
				C.P. ' . $agencia_cp . '. '  . $agencia_municipio . '<br><br>' . $agencia_estado . '<br><br>
				Tel. ' . $agencia_telefonos . '</td>
			</tr>'."\n";
			echo '		</table>'."\n";
			echo '		<table cellpadding="3" cellspacing="0" border="0" width="840" class="izquierda">'."\n";
			echo '			<tr><td>Expedido en ' . $agencia_municipio . ', ' . $agencia_estado . ' el ' . date('d') . ' de ' . date('M') . ' del ' . date('Y') . '</td><td style="text-align:right;">Venta: ' . $venta . '</td></tr>'."\n";
			echo '		</table>'."\n";

			echo '		<table cellpadding="3" cellspacing="0" border="1" width="840" class="izquierda" style="font-size:12px;">'."\n";
			echo '			<tr><td colspan="2">Productos entregados a ' . $quien . '</tr>'."\n";
			echo '		</table><br>'."\n";
			echo '		<table cellpadding="3" cellspacing="0" border="1" width="840" class="izquierda">'."\n";
			echo '			<tr class="cabeza_tabla"><td width="10%">' . $lang['Cantidad'] . '</td><td width="7%">' . $lang['Código'] . '</td><td width="53%">' . $lang['Descripción'] . '</td><td width="15%">' . $lang['Precio Unitario'] . '</td><td width="15%">' . $lang['Sub Total'] . '</td></tr>'."\n";
			$totpar = 0;
			$preg1 = "SELECT * FROM " . $dbpfx . "ventas_prod WHERE venta_id = '" . $venta . "'";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo Ventas Productos! " . $preg1);
			while($prod = mysql_fetch_array($matr1)) {
				$preg2 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id = '" . $prod['prod_id'] . "'";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de Productos! " . $preg2);
				$pro = mysql_fetch_array($matr2);
				echo '<tr><td style="text-align:center;">' . $prod['cantidad'] . '</td><td style="text-align:center;">' . $pro['prod_codigo'] . '</td><td style="text-align:left;">' . $pro['prod_nombre'] . '</td><td style="text-align:right;">$ ' . number_format($prod['precio_unitario'],2) . '</td><td style="text-align:right;">$ ' . number_format(($prod['cantidad'] * $prod['precio_unitario']),2) . '</td></tr>'."\n";
				$totpar = $totpar + round(($prod['cantidad'] * $prod['precio_unitario']), 2);
			}
			echo '		</table>'."\n";
			$totiva = round(($totpar * $impuesto_iva), 2);
			$grantotal = $totpar + $totiva;
			echo '		<table cellpadding="3" cellspacing="0" border="0" width="840" class="izquierda">'."\n";
			echo '			<tr><td width="70%" colspan="4" style="border-bottom:1px solid black;">Observaciones:</td><td style="text-align:right;" width="15%">SubTotal</td><td style="text-align:right; border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black;" width="15%">$' . number_format($totpar, 2) . '</td></tr>'."\n";
			echo '			<tr><td colspan="4" style="border-bottom:1px solid black;"></td><td style="text-align:right;">IVA al 16%</td><td style="text-align:right; border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black;">$' . number_format($totiva, 2) . '</td></tr>'."\n";
			echo '			<tr><td colspan="4" style="border-bottom:1px solid black;"></td><td style="text-align:right;">Total</td><td style="text-align:right; border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black;">$' . number_format($grantotal, 2) . '</td></tr>'."\n";
			echo '			<tr><td colspan="6" style="font-size:9px; line-height:12px; text-align:right;">Este documento NO es un recibo de pago o comprobante fiscal. Sólo aplica como formato de entrega.</td></tr>'."\n";
			echo '			<tr><td colspan="6"><div class="control">';
			echo '<a href="javascript:window.print()"><img src="idiomas/' . $idioma . '/imagenes/imprimir.png" alt="' . $lang['Imprimir Conceptos de Factura'] . '" title="' . $lang['Imprimir Conceptos de Factura'] . '"></a> ';
//			echo '<a href="ordenes.php?accion=consultar&orden_id=' . $orden_id . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="'. $lang['Regresar a la OT'].'" title="'. $lang['Regresar a la OT'].'"></a></div></td></tr>'."\n";
			echo '		</table>'."\n";
		} else {
			$_SESSION['msjerror'] = 'No hubo selección correcta de tipo de cliente. Reporte a Soporte Técnico AutoShop Easy';
			redirigir('salidas.php?accion=listar');
		}
}

?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
