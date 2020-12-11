<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';

include('parciales/funciones.php');
if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}
include('idiomas/' . $idioma . '/almacen.php');

$_SESSION['selector']['grupo'] = $grupo;
$_SESSION['selector']['ref_presel'] = $ref_presel;
$_SESSION['selector']['ongest'] = $ongest;


/*  ----------------  obtener nombres de proveedores   ------------------- */

		$consulta = "SELECT prov_id, prov_razon_social, prov_nic, prov_qv_id, prov_rfc, prov_env_ped, prov_representante, prov_email, prov_dde, prov_iva, prov_dias_credito FROM " . $dbpfx . "proveedores WHERE prov_email != '' AND prov_activo = '1' ORDER BY prov_nic";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo proveedores!");
		$num_provs = mysql_num_rows($arreglo);
		$provs = array();
//		$provs[0] = 'Sin Proveedor';
		while ($prov = mysql_fetch_array($arreglo)) {
			$provs[$prov['prov_id']] = array('nombre' => $prov['prov_razon_social'], 'nic' => $prov['prov_nic'], 'qvid' => $prov['prov_qv_id'], 'rfc' => $prov['prov_rfc'], 'env' => $prov['prov_env_ped'], 'contacto' => $prov['prov_representante'], 'email' => $prov['prov_email'], 'dde' => $prov['prov_dde'], 'iva' => $prov['prov_iva'], 'dias_credito' => $prov['prov_dias_credito']);
		}
//		print_r($provs);

/*  ----------------  nombres de aseguradoras   ------------------- */
		$consulta = "SELECT aseguradora_id, aseguradora_logo, autosurtido, aseguradora_nic, prov_def FROM " . $dbpfx . "aseguradoras ORDER BY aseguradora_id";
		$arreglo = mysql_query($consulta) or die("ERROR: Fallo aseguradoras!");
		while ($aseg = mysql_fetch_array($arreglo)) {
			define('ASEGURADORA_' . $aseg['aseguradora_id'], $aseg['aseguradora_logo']);
			define('ASEGURADORA_NIC_' . $aseg['aseguradora_id'], $aseg['aseguradora_nic']);
			$asegnic[$aseg['aseguradora_id']] = $aseg['aseguradora_nic'];
			$autosurt[$aseg['aseguradora_id']] = $aseg['autosurtido'];
			$prov_def[$aseg['aseguradora_id']] = $aseg['prov_def'];
		}
		$asegnic[0] = 'Particular';
		$autosurt[0] = 1;

if (($accion==='gestiona') || ($accion==='actualizar') || ($accion==='cotpedprod' || $accion==='gestprod') || ($accion==='insertar') || ($accion==='inspcpaq') || ($accion==='actpcpaq') || $accion==='guardacotiza' || $export == 1 || $accion==='flash') {
	/* no cargar encabezado */
} else {
	
	if($export == 1){ // ---- Hoja de calculo ---- 
			
	} else{ // ---- HTML ----
		include('parciales/encabezado.php'); 
		echo '	
				<div id="body">'."\n";
		include('parciales/menu_inicio.php');
		echo '		
				<div id="principal">'."\n";
	}
}

if($accion==='listar') {
	
	if (validaAcceso('1115030', $dbpfx) == 1) {
		// Acceso autotizado
	} elseif($solovalacc != 1 && ($_SESSION['rol08']=='1')) {
		// Acceso autotizado
	} else {
		redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}

	if(isset($paquete) && $paquete > 0) { $_SESSION['ref']['paquete'] = $paquete;}
	$paquete = $_SESSION['ref']['paquete'];
	if($limpiar == 'Restablecer Filtros') { $codigo=''; $nombre=''; $almacen=''; }
	
	if($pedpend == 1) { $_SESSION['ref']['pedpend'] = 1; }
	if($cotpend == 1) { $_SESSION['ref']['cotpend'] = 1; }
	if($cot_contestadas == 1) { $_SESSION['ref']['cot_contestadas'] = 1; }
	if($pedpend == 2){ 
		unset($_SESSION['ref']['pedpend']); 
		unset($_SESSION['ref']['cotpend']);
		unset($_SESSION['ref']['cot_contestadas']);
	}
	
	echo '
		<div class="page-content">

			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header">
						<div class="panel-title">
		  					<h2>Refacciones, Consumibles y Mano de Obra por Almacén</h2>
						</div>
			  		</div>
				</div>
			</div>
			
			<div class="row"> <!- mensajes -->
				<div class="col-md-12">
	  				<span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span>
				</div>
			</div>'."\n";
	
	unset($_SESSION['ref']['mensaje']);
	
	echo '		
		
			<div class="row">
				<div class="col-md-8 shadow-box">
					<div class="form-group">
      					<div class="col-md-12">
							<form action="almacen.php?accion=listar" method="post" enctype="multipart/form-data">
							<table>
								<tr>
									<td align="right" class="obscuro"><big><b>Buscar por codigo:</b></big></td>
									<td>
										<input class="form-control" type="text" name="codigo" id="codigo" size="15" value="' . $codigo . '">
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Buscar por nombre:</b></big></td>
									<td>
										<input class="form-control" type="text" name="nombre" size="15" value="' . $nombre . '">
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Filtrar por Almacén:</b></big></td>
									<td>
										<select class="form-control" name="area" size="1">
												<option value="">Seleccionar...</option>'."\n";
											foreach($nom_almacen as $k => $v) {
												echo '				
												<option value="' . $k . '"';
												if($almacen == $k) { echo ' selected="selected" ';}
												echo '>' . $v . '</option>'."\n";
											}
	
	echo '			
										</select>
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
									<td>
										<input class="btn btn-danger" type="submit" name="limpiar" value="Restablecer Filtros">
									</td>
									<td>
									</td>
									<td>
										<a href="almacen.php?accion=item&nuevo=1">
											<img src="idiomas/' . $idioma . '/imagenes/agregar.png" alt="Nuevo Producto" title="Nuevo Producto" width="45" height="45">
										</a>
									</td>
									<td>
										<a href="almacen.php?accion=cotpedprod">
											<img src="idiomas/' . $idioma . '/imagenes/carro-de-compra.png" alt="Pedidos y Cotizaciones" title="Pedidos y Cotizaciones" width="45" height="45">
										</a>
									</td>'."\n";
	
	if($_SESSION['ref']['pedpend'] == 1 || $_SESSION['ref']['cotpend'] == 1 || $_SESSION['ref']['cot_contestadas'] == 1) {
		echo '
									<td>
										<a href="almacen.php?accion=listar&pedpend=2"><img src="idiomas/' . $idioma . '/imagenes/flag-black.png" alt="Todo" title="Todo" width="45" height="45"></a>
									</td>'."\n";
	} else {
		echo '
									<td>
										<a href="almacen.php?accion=listar&pedpend=1">
											<img src="idiomas/' . $idioma . '/imagenes/flag-yellow.png" alt="Sólo Items Pendientes por Recibir" title="Sólo Items Pendientes por Recibir" width="45" height="45">
										</a>
									</td>
									<td>
										<a href="almacen.php?accion=listar&cot_contestadas=1">
											<img src="idiomas/' . $idioma . '/imagenes/cotizaciones_contestadas.png" alt="Sólo Items con cotizaciones contestadas" title="Sólo Items con cotizaciones contestadas" width="45" height="45">
										</a>
									</td>
									<td>
										<a href="almacen.php?accion=listar&cotpend=1"><img src="idiomas/' . $idioma . '/imagenes/cotizacion-por-recibir.png" alt="Sólo Cotizaciones Pendientes por Recibir" title="Sólo Cotizaciones Pendientes por Recibir" width="45" height="45"></a>
									</td>'."\n";
	}
	echo '
									<td>
										<a href="almacen.php?accion=generacb"><img src="idiomas/' . $idioma . '/imagenes/barcode_scanner.png" alt="Generar Etiquetas de Código de Barras" title="Generar Etiquetas de Código de Barras" width="45" height="45"></a>
									</td>
								</tr>	
							</table>
							</form>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12">'."\n";

	if((isset($almacen) && $almacen!='') || (isset($nombre) && $nombre!='') || (isset($codigo) && $codigo!='')) { 
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
	}
	if($_SESSION['ref']['pedpend'] == 1) { $preg .= "AND prod_cantidad_pedida > '0' "; }
	
	$preg0 = "SELECT prod_id FROM " . $dbpfx . "productos WHERE prod_activo='1' ";
	$preg0 = $preg0 . $preg;
	//echo $preg0 . '<br>';
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos! " . $preg0);
	$filas = mysql_num_rows($matr0);
	if($filas == 0 && $codigo!='') {
   		echo '
						<div>
							No se encontró ningún producto con el código ' . $codigo . ',<br>¿Desea agregarlo como nuevo a la lista de productos? <a href="almacen.php?accion=item&nuevo=1&codigo=' . $codigo . '">Agregar</a>&nbsp;
						</div>'."\n";
   	} else {

	   $renglones = 50;
	   $paginas = (round(($filas / $renglones) + 0.49999999) - 1);
   		if(!isset($pagina)) { $pagina = 0;}
	   $inicial = $pagina * $renglones;
		//echo $paginas;
		if($_SESSION['ref']['cotpend'] == 1) {
			$preg1 = "SELECT p.prod_id, p.prod_marca, p.prod_codigo, p.prod_nombre, p.prod_cantidad_pedida, p.prod_cantidad_existente, p.prod_cantidad_disponible, p.prod_precio, p.prod_almacen, p.prod_tangible FROM " . $dbpfx . "productos p, " . $dbpfx . "prod_prov pp WHERE p.prod_activo = '1' AND pp.prod_id = p.prod_id AND pp.prod_costo = '0'";
			//$preg1 = $preg1 . $preg;
			$preg1 .= " GROUP BY p.prod_id ORDER BY p.prod_almacen, p.prod_id LIMIT " . $inicial . ", " . $renglones;
		} elseif($_SESSION['ref']['cot_contestadas'] == 1){
			$preg1 = "SELECT p.prod_id, p.prod_marca, p.prod_codigo, p.prod_nombre, p.prod_cantidad_pedida, p.prod_cantidad_existente, p.prod_cantidad_disponible, p.prod_precio, p.prod_almacen, p.prod_tangible FROM " . $dbpfx . "productos p, " . $dbpfx . "prod_prov pp WHERE p.prod_activo = '1' AND pp.prod_id = p.prod_id AND pp.prod_costo > 0";
			//$preg1 = $preg1 . $preg;
			$preg1 .= " GROUP BY p.prod_id ORDER BY p.prod_almacen, p.prod_id LIMIT " . $inicial . ", " . $renglones;
		} else {
			$preg1 = "SELECT prod_id, prod_marca, prod_codigo, prod_nombre, prod_cantidad_pedida, prod_cantidad_existente, prod_cantidad_disponible, prod_precio, prod_almacen, prod_tangible FROM " . $dbpfx . "productos WHERE prod_activo = '1' ";
			$preg1 = $preg1 . $preg;
			$preg1 .= " GROUP BY prod_id ORDER BY prod_almacen, prod_id LIMIT " . $inicial . ", " . $renglones;
		}
		//echo $preg1;
		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos! " . $preg1);

		echo '		
						<div align="right">
							<br><br><br>
							<a href="almacen.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="almacen.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="almacen.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="almacen.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>
						</div>'."\n";
		
		
		echo '			
						<div id="content-tabla">
							<table cellspacing="0" class="table-new">
								<tr>
									<th><big>Almacén</th></big>
									<th><big>Nombre</th></big>
									<th><big>Marca</th></big>
									<th><big>Código</th></big>
									<th><big>Existencia</th></big>
									<th><big>Disponible</th></big>
									<th><big>Adeudos</th></big>
									<th><big>Precio Unitario<br>de Venta</th></big>
									<th><big>Foto</th></big>
									<th><big>Acciones</th></big>
								</tr>'."\n";
		$cue = 0;
		$clase = 'claro';
		while($prods = mysql_fetch_array($matr1)){
			//print_r($prods);
			$preg3 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE prod_id = '" . $prods['prod_id'] . "'";
			$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de productos!");
			$foto = mysql_fetch_array($matr3);
			if(!file_exists(DIR_DOCS . $foto['doc_archivo'])) { baja_archivo($foto['doc_archivo']); }
			if ($foto['doc_archivo'] != '') {
				$etiqueta= '<a href="' . DIR_DOCS  .$foto['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'minis/' .$foto['doc_archivo'] . '"</a>';
			} else {
				$etiqueta= '<img src="' . DIR_DOCS . 'documento.png" alt="Sin imagen" title="Sin imagen">';
			}
			
			// --- Consultar cotizaciones contestadas ---
			$preg_cots_con = "SELECT pp_id FROM " . $dbpfx . "prod_prov  WHERE prod_id='" . $prods['prod_id'] . "' AND prod_costo > 0";
			$matr_cots_con = mysql_query($preg_cots_con) or die("ERROR: Fallo selección de cotizaciones contestadas! " . $preg_cots_con);
  			$cotiz_contestadas = mysql_num_rows($matr_cots_con);

			// --- Consultar adeudos ---
			$preg_adeudos = "SELECT * FROM " . $dbpfx . "prods_pendientes WHERE prod_id = '" .  $prods['prod_id'] . "' AND prods_pendiente_adeudos > 0";
			$matr_adeudos = mysql_query($preg_adeudos) or die("ERROR: Fallo selección de adeudos! " . $preg_adeudos);
			$adeudos = mysql_num_rows($matr_adeudos);
		
			$total_adeudos = 0;
			if($adeudos > 0){ // --- Sumar adeudos ---
				while($consulta_adeudos = mysql_fetch_array($matr_adeudos)){
					$total_adeudos = $total_adeudos + $consulta_adeudos['prods_pendiente_adeudos'];
				}	
			}
			
			echo '					
								<tr class="' . $clase . '">
									<td>' . $nom_almacen[$prods['prod_almacen']] . '</td>
									<td>' . $prods['prod_nombre'] . '</td>
									<td>' . $prods['prod_marca'] . '</td>
									<td>' . $prods['prod_codigo'] . '</td>
									<td style="text-align:right;">' . $prods['prod_cantidad_existente'] . '</td>
									<td style="text-align:right;">' . $prods['prod_cantidad_disponible'] . '</td>
									<td style="text-align:right;">' . $total_adeudos . '</td>
									<td style="text-align:right;">$' . number_format($prods['prod_precio'],2) . '</td>
									<td>' . $etiqueta . '</td>
									<td>'."\n";
			
			if(isset($paquete) && $paquete > 0) {
				echo '
										<a href="almacen.php?accion=inspcpaq&paquete=' . $paquete . '&prod_id=' . $prods['prod_id'] . '">
											<img src="idiomas/' . $idioma . '/imagenes/agregar.png" alt="Agregar" title="Agregar a paquete" width="25" height="25">
										</a>'."\n";
			} else {
				echo '
										<a href="almacen.php?accion=item&prod_id=' . $prods['prod_id'] . '">
											<img src="idiomas/' . $idioma . '/imagenes/prod-editar.png" alt="Detalles" title="Detalles" width="25" height="25">
										</a> / 
										<a href="almacen.php?accion=cotpedprod&prod_id=' . $prods['prod_id'] . '">
											<img src="idiomas/' . $idioma . '/imagenes/carro-de-compra.png" width="25" height="25" alt="Agregar a Pedido o Cotización" title="Agregar a Pedido o Cotización">
										</a>'."\n";

				if($prods['prod_cantidad_pedida'] > 0 && $prods['prod_tangible'] > 0) { 
					echo '
										<img src="idiomas/' . $idioma . '/imagenes/flag-yellow.png" width="25" height="25" alt="Pendiente por Recibir" title="Pendiente por Recibir">'."\n";
				}

				$preg2 = "SELECT prod_id FROM " . $dbpfx . "prod_prov WHERE prod_id = '" . $prods['prod_id'] . "' AND prod_costo = '0' ";
				$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de cotizaciones!");
				$fila2 = mysql_num_rows($matr2);
				if($fila2 > 0) { 
					echo '
										<img src="idiomas/' . $idioma . '/imagenes/cotizacion-por-recibir.png" alt="Cotización por Recibir" width="25" height="25" title="Cotización por Recibir">';
				}
				
				if($cotiz_contestadas > 0){
					echo '
										<img src="idiomas/' . $idioma . '/imagenes/cotizaciones_contestadas.png" alt="Cotización contestada" width="25" height="25" title="Cotización contestada">';
				}
			}
			
			echo '
									</td>
								</tr>'."\n";
			$cue++;
			if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
		}
		
		echo '				
							</table>
						</div>
						<div align="right">
							<a href="almacen.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="almacen.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="almacen.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="almacen.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>
						</div>'."\n";
	}
	
	echo '
					</div>
				</div>
			</div>
		</div>'."\n";
}

elseif($accion==='item') {
	
	if (validaAcceso('1115035', $dbpfx) == 1) {	
		// Acceso autotizado
	} elseif($solovalacc != 1 && ($_SESSION['rol08']=='1')) {
		// Acceso autotizado
	} else {
		redirigir('index.php?mensaje=Acceso no Autorizado');
	}
	
	if($prod_id!='') {
		$preg0 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id='" . $prod_id . "'";
		//echo $preg0 . '<br>';
		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
	   $prod = mysql_fetch_array($matr0);
	}
	
	echo '
		<div class="page-content">

			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header">
						<div class="panel-title">
		  					<h2>Detalle de Producto</h2>
						</div>
			  		</div>
				</div>
			</div>
			<br>'."\n";
	
	// --- Sección de foto del producto ---
	$preg3 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE prod_id = '" . $prod_id . "'";
	$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de productos!");
	$foto = mysql_fetch_array($matr3);
	if(!file_exists(DIR_DOCS . $foto['doc_archivo'])) { baja_archivo($foto['doc_archivo']); }
	if ($foto['doc_archivo'] != '') {
		$etiqueta = '
					<div align="center">
						<a href="' . DIR_DOCS  .$foto['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'medianas/' . $foto['doc_archivo'] . '" alt=""></a>
						<input type="hidden" name="hacer" value="actualizar"/>
					</div>'."\n";
	} else {
		$etiqueta = '
					<div align="center">
						<img src="' . DIR_DOCS . 'documento.png" alt="Sin imagen" title="Sin imagen">
						<input type="hidden" name="hacer" value="insertar"/>
					</div>'."\n";
	}
	
	// --- Sección de ultimos movimientos ---
	$preg4 = "SELECT * FROM " . $dbpfx . "prod_bitacora WHERE prod_id='" . $prod_id . "' ORDER BY bit_id DESC LIMIT 5";
	//echo $preg4 . '<br>';
  	$matr4 = mysql_query($preg4) or die("ERROR: Fallo selección de bitácoras!".$preg4);
  	$fila4 = mysql_num_rows($matr4);
	$preg5 = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE activo = '1'";
	$matr5 = mysql_query($preg5) or die("ERROR: Fallo selección de Usuario!" . $preg5);
	while($usu = mysql_fetch_array($matr5)) {
		$usr[$usu['usuario']] = $usu['nombre'] . ' ' . $usu['apellido'];
	}
	
	echo '
			<form action="almacen.php?accion=';
	
	if($nuevo=='1') { echo 'insertar';} else { echo 'actualizar';}

	echo '" method="post" enctype="multipart/form-data">';
	
	echo '	
			<br>
			<div class="row">
			
				<div class="col-md-4 shadow-box">
					<h2 align="center">FOTO</h2>
					' . $etiqueta . '<br>
					<div align="center">
						<small>
							<input type="file" name="imagen" size="30"/>
						</small>
					</div>
				</div>
				
				<div class="col-md-1">
				</div>
				
				<div class="col-md-5">'."\n";
					
					if($fila4 > 0){
				   		echo '			
						<div id="content-tabla">
							<table cellspacing="0" class="table-new">
								<tr>
									<th>FECHA</th>
									<th>EVENTO <br><small>Histórico de 5 últimos movimientos</small></th>
									<th>MOTIVO</th>
									<th>USUARIO</th>
								</tr>'."\n";

						$clase = 'claro';
				   		while($hist = mysql_fetch_array($matr4)) {

							echo '				
								<tr class="' . $clase . '">
									<td>' . $hist['fecha_evento'] . '</td>
									<td>' . $hist['evento'] . '</td>
									<td>' . $hist['motivo'] . '</td>
									<td>' . $usr[$hist['usuario']] . '</td>
									
								</tr>'."\n";
							if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
							
						}
						echo '			
							</table>
						</div>'."\n";
					}
	echo '
					
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-5">
					<span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span>
				</div>
			</div>
			<br>
			<div class="row">'."\n";
			
	unset($_SESSION['ref']['mensaje']);
	
	echo '
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Nombre:</b></big>
									</td>
									<td>
										<input class="form-control" type="text" name="nombre" size="20" maxlength="255" value="';
										echo ($_SESSION['ref']['nombre']) ? $_SESSION['ref']['nombre']:$prod['prod_nombre']; echo '" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<br>
										<big><b>Marca:</b></big>
									</td>
									<td>
										<br>
										<input class="form-control" type="text" name="marca" size="20" maxlength="32" value="'; 
										echo ($_SESSION['ref']['marca']) ? $_SESSION['ref']['marca']:$prod['prod_marca']; echo '" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<br><br><big><b>Código:</b></big>
									</td>
									<td>
										<br>
										Código de Barras del producto.
										<input class="form-control" type="text" name="codigo" size="20" maxlength="20" value="';
										if($nuevo=='1' && $codigo!='') {
											echo $codigo;
										} else {
											echo ($_SESSION['ref']['codigo']) ? $_SESSION['ref']['codigo']:$prod['prod_codigo'];
										} 
										echo '" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				
				<div class="col-md-2">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Unidad:</b></big>
									</td>
									<td>
										<select class="form-control" name="uniprod" size="1">
											<option value="">Seleccionar...</option>'."\n";
											foreach ($valarr['unidad'] as $k) {
												echo '												<option value="' . $k . '" '; if($prod['prod_unidad'] == $k) {echo 'selected="selected" ';} echo '>' . $k . '</option>'."\n";
											}
	echo '
										</select>
									</td>
								</tr>
								<tr>
									<td align="right">
										<br><big><b>Tipo:</b></big>
									</td>
									<td>
										<br>
										<select class="form-control" name="tangible" size="1">
											<option value="9">Seleccionar...</option>'."\n"; 
											foreach ($valarr['prod_tipo'] as $k => $v) {
												echo '												<option value="' . $k . '" '; if($prod['prod_tangible'] == $k) {echo 'selected="selected" ';} echo '>'. $v .'</option>'."\n";
											}
	echo '		
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>'."\n";

	if(isset($nuevo) && $nuevo=='1') {
		echo '		
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td colspan="2">
										<span class="alerta">
											Una vez dado de alta el producto, la cantidad y su costo se podrán ajustar desde recibo de productos.
										</span>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>';
	} else {
		echo '		
				<div class="col-md-2">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Existencias:</b></big>
									</td>
									<td>
										&nbsp;&nbsp;&nbsp;&nbsp;<big><big><b>' . $prod['prod_cantidad_existente'] . '</b></big></big>
										<input class="form-control" type="hidden" name="existencia" value="' . $prod['prod_cantidad_existente'] . '" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<big><b>Disponibles:</b></big>
									</td>
									<td>
										&nbsp;&nbsp;&nbsp;&nbsp;<big><big><b>' . $prod['prod_cantidad_disponible'] . '</b></big></big>
										<input type="hidden" name="disponible" value="' . $prod['prod_cantidad_disponible'] . '" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<big><b>Ajustar existencias:</b></big>
									</td>
									<td>
										<input class="form-control" type="text" name="nvaexist" size="1" maxlength="10" value="' . $prod['prod_cantidad_existente'] . '" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Motivo de ajuste:</b></big>
									</td>
									<td>
										<textarea class="form-control" name="motivoajuste" rows="3" cols="20">' . $_SESSION['ref']['motivoajuste'] . '</textarea>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	}
	
	$margen = (($prod['prod_precio'] - $prod['prod_costo']) / $prod['prod_precio']);
	
	echo '
			<br>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Precio de Venta Público:</b></big>
									</td>
									<td>
										<input class="form-control" type="text" name="precio" size="11" maxlength="20" value="'; 
										echo $prod['prod_precio'] . '" style="text-align:right;" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<br><big><b>Precio de Venta Interno:</b></big>
									</td>
									<td>
										<br><input class="form-control" type="text" name="precioint" size="11" maxlength="20" value="'; 
										echo $prod['prod_precioint'] . '" style="text-align:right;" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Costo de Compra:</b></big>
									</td>
									<td>
										<input class="form-control" type="text" name="prodcosto" size="11" maxlength="20" value="'; 
										echo $prod['prod_costo'] . '" style="text-align:right;" />
									</td>
								</tr>
								<tr>
									<td align="right">
										<br><big><b>Margen de Utilidad:</b></big></td>
									<td>
										<br>&nbsp;&nbsp;&nbsp;&nbsp;<big><b>' . round(($margen * 100), 2) . '% </b></big>
										<input type="hidden" name="margen" size="4" maxlength="6" value="' . $margen . '" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Almacén:</b></big>
									</td>
									<td>
										<select class="form-control" name="almacen" size="1">
											<option value="">Seleccionar...</option>'."\n";
											foreach($nom_almacen as $k => $v) {
												echo '
												<option value="'.$k.'" '; if($prod['prod_almacen']==$k) {echo 'selected="selected" ';} echo '>' . $v . '</option>'."\n";
											} 
	echo '		
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	
	echo '
			<br>
			<div class="row">
				<div class="form-group">
              		<div class="col-md-5">
						<table>
							<tr>
								<td align="right">
									<big><b>Proveedor default:</b></big>
								</td>
								<td>
									<select class="form-control" name="prov_id">
											<option  value="0">...Seleccione</option>'."\n";
										foreach($provs as $i => $j) {
											echo '			  	
											<option  value="'.$i.'" '."\n";
											
											if($prod['prod_prov_id'] == $i){
												echo 'selected';
											}
												
												echo '>'."\n";
											echo '
												' . $j['nic'] . '
											</option>'."\n";
											
										}
		echo '			
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>'."\n";
	

	echo '	
			<br>
			<div class="row">
				<div class="col-md-3">
					<div class="form-group">
              			<div class="col-md-12">
							<table>	
								<tr>
									<td align="right">
										<br><big><b>Resurtir:</b></big>
									</td>
									<td colspan="2">
										Solicitar pedido cuando quede esta cantidad.
										<input class="form-control" type="text" name="resurtir" size="1" value="'; 
										echo ($_SESSION['ref']['resurtir']) ? $_SESSION['ref']['resutir']:$prod['prod_resurtir']; echo '" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
              			<div class="col-md-12">
							<table>	
								<tr>
									<td align="right">
										<br><big><b>Cantidad a cotizar:</b></big>
									</td>
									<td colspan="2">
										Fijar la cantidad que se cotizará una vez que el producto se agote.
										<input class="form-control" type="text" name="cant_cotizar" size="1" value="'; 
										echo ($_SESSION['ref']['prod_cant_cotizar']) ? $_SESSION['ref']['prod_cant_cotizar'] : $prod['prod_cant_cotizar']; echo '" />
									</td>
								</tr>'."\n";
	
	if($qv_activo == 1){ // --- Habilitar la opción de cotización en quien-vende.com ---
		
		echo '
								<tr>
									<td align="right">
										<br><big><b>Cotizar en quien-vende.com:</b></big>
									</td>
									<td colspan="2">
										<br><input type="radio" name="qv" id="q-vende" value="1"';
										if($prod['prod_qv'] == 1){
											echo ' checked ';
										}
										echo ' /<big>SI</big><br>
										<input type="radio" name="qv" id="q-vende" value="0"';
										if($prod['prod_qv'] == ''){
											echo ' checked ';
										}
										echo ' /<big>NO</big>
									</td>
								</tr>'."\n";
	}
		
	echo '
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-5">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<br><big><b>Ubicación:</b></big>
									</td>
									<td colspan="2">
										Lugar dentro del almacén en donde está ubicado el producto.
										<input class="form-control" type="text" name="local" size="15" maxlength="32" value="'; 
										echo ($_SESSION['ref']['local']) ? $_SESSION['ref']['local']:$prod['prod_local']; echo '" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	
	// --- Consultar pendientes ---
	$preg_pendientes = "SELECT * FROM " . $dbpfx . "prods_pendientes WHERE prod_id = '" . $prod_id . "' AND (prods_pendiente_adeudos > 0 OR prods_pendiente_entregados < prods_pendiente_requeridos)";
	$matr_pendientes = mysql_query($preg_pendientes) or die("ERROR: Fallo selección de prods_pendientes! " . $preg_pendientes);
	$num_pendientes = mysql_num_rows($matr_pendientes);
	
	if($prod['prod_cantidad_existente'] > 0 && $num_pendientes > 0) {
		echo '
			<div class="row">
				<div class="col-md-5">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Borrar?:</b></big>
									</td>	
									<td colspan="2" style="text-align:left;">
										&nbsp;&nbsp;Para poder desactivar un producto, primero debe quedar sin existencias y no tener adeudos
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	} else {
		echo '
			<div class="row">
				<div class="col-md-5">
					<div class="form-group">
              			<div class="col-md-12">
							<table>
								<tr>
									<td align="right">
										<big><b>Borrar?:</b></big>
									</td>	
									<td colspan="2" style="text-align:left;">
										&nbsp;&nbsp;<input type="checkbox" name="borrar" value="1" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	}
	
	
	echo '
			<br>
			<div class="row">
				<div class="col-md-4">
					<input type="hidden" name="prod_id" value="' . $prod['prod_id'] . '" />
					<input type="hidden" name="nuevo" value="' . $nuevo . '" />
					<input class="btn btn-lg btn-success" type="submit" value="Enviar" />
				</div>
			</div>'."\n";

	if(!isset($nuevo) || $nuevo=='') {
		$preg2 = "SELECT pp.op_cantidad, pp.op_costo, pp.op_pedido, pp.op_recibidos, p.prov_id, pp.op_fecha_promesa FROM " . $dbpfx . "orden_productos pp, " . $dbpfx . "pedidos p WHERE pp.prod_id='" . $prod_id . "' AND pp.op_pedido = p.pedido_id";
//		echo $preg2 . '<br>';
  		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de proveedores!");
	  	$pedidos = mysql_num_rows($matr2);
	
  		echo '
			<br>
			<div class="row">
				<div class="col-md-12">
					<h2 align="center">PEDIDOS</h2>
					<div class="form-group">
              			<div class="col-md-12">
							<div id="content-tabla">
								<table cellspacing="0" class="table-new">
									<tr>
										<th>Proveedor</th>
										<th>Pedido</th>
										<th>Cantidad</th>
										<th>Costo</th>
										<th>Fecha Promesa<br>de Entrega</th>
										<th>Recibidas</th>
										<th>Pendientes</th>
									</tr>'."\n";
		if($pedidos > 0) {
			$clase = 'claro';
			while($ped = mysql_fetch_array($matr2)) {
				$pendientes = $ped['op_cantidad'] - $ped['op_recibidos'];
				echo '			
									<tr class="' . $clase . '">
										<td>'."\n";
				if($prod['prod_tangible'] == 3) {
					echo '
											' . $usr[$ped['prov_id']] . ''."\n";
				} else {
					echo '
											<a href="proveedores.php?accion=consultar&prov_id=' . $ped['prov_id'] . '" target="_blank" >' . $provs[$ped['prov_id']]['nic'] . '</a>'."\n";
				}
				echo '
										</td>
										<td>
											<a href="pedidos.php?accion=consultar&pedido=' . $ped['op_pedido'] . '" target="_blank">' . $ped['op_pedido'] . '</a>
										</td>
										<td>
											' . $ped['op_cantidad'] . '
										</td>
										<td style="text-align:right;">
											' . money_format('%n', $ped['op_costo']) . '
										</td>
										<td>
											' . $ped['op_fecha_promesa'] . '
										</td>
										<td>
											' . $ped['op_recibidos'] . '
										</td>
										<td>
											' . $pendientes . '
										</td>
									</tr>'."\n";

				if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
			}
		} else {
			echo '
									<tr class="claro">
										<td colspan="7">
											<big><b>No se encontró ningún pedido.</b></big>
										</td>
									</tr>'."\n";
		}
		echo '		
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>'."\n";
		
		$preg3 = "SELECT * FROM " . $dbpfx . "prod_prov  WHERE prod_id='" . $prod_id . "'";
		//echo $preg3 . '<br>';
		$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de proveedores!");
  		$cotiza = mysql_num_rows($matr3);
   	
	  	echo '		
			<br>
			<div class="row">
				<div class="col-md-12">
					<h2 align="center">COTIZACIONES</h2>
					<div class="form-group">
              			<div class="col-md-12">
							<div id="content-tabla">
								<table cellspacing="0" class="table-new">
									<tr>
										<th>Proveedor</th>
										<th>Costo<br>Unitario</th>
										<th>Días de Entrega</th>
										<th>Días de crédito</th>
										<th>Solicitada</th>
										<th>Remover Cotización</th>
										<th>Hacer Pedido</th>
									</tr>'."\n";
		if($cotiza > 0) {
			$j=0;
			$clase = 'claro';
			while($cot = mysql_fetch_array($matr3)) {
				echo '
									<input type="hidden" name="cot_prov_id['.$j.']" value="'.$cot['prod_prov_id'].'" />
									<tr class="' . $clase . '">
										<td>
											' . $provs[$cot['prod_prov_id']]['nic'] . '
										</td>
										<td>
											<input type="text" name="cot_costo['.$j.']" value="' . money_format('%n', $cot['prod_costo']) . '" size="10"/>
										</td>
										<td>
											<input type="text" name="cot_entrega['.$j.']" value="' . $cot['dias_entrega'] . '" size="3"/>
										</td>
										<td>
											<input type="text" name="cot_credito['.$j.']" value="' . $cot['dias_credito'] . '" size="3"/>
										</td>
										<td>
											'.$cot['fecha_cotizado'].'
										</td>
										<td>
											<input type="checkbox" name="quitacot['.$j.']" value="1" />
										</td>
										<td>
											<input type="radio" name="hazped" value="' . $prod_id . ':' . $cot['prod_prov_id'] . '" />
										</td>
									</tr>'."\n";
				$j++;
				if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
			}
		} else {
			echo '
									<tr class="' . $clase . '">
										<td colspan="7">
											<b><big>No se encontró ninguna cotización.</big></b>
										</td>
									</tr>'."\n";
		}
		echo '		
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>'."\n";
	}
		
	echo '
			<br>
			<div class="row">
				<div class="col-md-4">
					<input type="hidden" name="prod_id" value="' . $prod['prod_id'] . '" />
					<input type="hidden" name="nuevo" value="' . $nuevo . '" />
					<input class="btn btn-lg btn-success" type="submit" value="Enviar" />
				</div>
			</div>
			</form>
			<br>
			
			<div class="row">'."\n";
	
	$preg_pendientes = "SELECT * FROM " . $dbpfx . "prods_pendientes WHERE prod_id = '" . $prod_id . "' AND (prods_pendiente_adeudos > 0 OR prods_pendiente_entregados < prods_pendiente_requeridos)";
	$matr_pendientes = mysql_query($preg_pendientes) or die("ERROR: Fallo selección de prods_pendientes! " . $preg_pendientes);
	
	echo '
			
				<div class="col-md-10">
					<h2>Pendientes de Surtir y entregar</h2>
					<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr>
								<th>O.T.</th>
								<th>Tarea</th>
								<th>Fecha del requerimiento</th>
								<th>Requeridos</th>
								<th>Apartados</th>
								<th>Adeudos</th>
								<th>Entregados a operador</th>
							</tr>'."\n";
	
	$clase = 'claro';
	while($pendientes = mysql_fetch_array($matr_pendientes)){
		$fecha =  date('d-m-Y', strtotime($pendientes['prods_pendiente_fecha']));
		echo '
							<tr class="' . $clase . '">
								<td>
									<a href="ordenes.php?accion=consultar&orden_id=' . $pendientes['orden_id'] . '">' . $pendientes['orden_id'] . '</a>
								</td>
								<td>
									<a href="proceso.php?accion=consultar&orden_id=' . $pendientes['orden_id'] . '#' . $pendientes['sub_orden_id'] . '">' . $pendientes['sub_orden_id'] . '</a>
								</td>
								<td>
									' . $fecha . '
								</td>
								<td>' . $pendientes['prods_pendiente_requeridos'] . '</td>
								<td>' . $pendientes['prods_pendiente_surtidos'] . '</td>
								<td>' . $pendientes['prods_pendiente_adeudos'] . '</td>
								<td>' . $pendientes['prods_pendiente_entregados'] . '</td>
							</tr>'."\n";
		
		if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
	}
	
	echo '
						</table>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-1">
					<a href="almacen.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a la Lista de Partes" title="Regresar a la Lista de Partes"></a>
				</div>
			</div>


		</div>'."\n";
}

elseif($accion==="actualizar" || $accion==="insertar") {

	if (validaAcceso('1115035', $dbpfx) == 1) {	
		// Acceso autotizado
	} elseif($solovalacc != 1 && ($_SESSION['rol08']=='1')) {
		// Acceso autotizado
	} else {
		redirigir('index.php?mensaje=Acceso no Autorizado');
	}

	unset($_SESSION['ref']);
	$_SESSION['ref'] = array();
	$_SESSION['ref']['mensaje']='';
	$mensaje = '';
	$error = 'no'; 
	
	$nombre = preparar_entrada_bd($nombre); $_SESSION['ref']['nombre']=$nombre;
	$marca = preparar_entrada_bd($marca); $_SESSION['ref']['marca']=$marca;
	$codigo = preparar_entrada_bd($codigo); $_SESSION['ref']['codigo']=$codigo;
	$motivoajuste = preparar_entrada_bd($motivoajuste); $_SESSION['ref']['motivoajuste'] = $motivoajuste;
	$nvaexist = limpiarNumero($nvaexist);
	$cant_cotizar = limpiarNumero($cant_cotizar);
	
	$precio = limpiarNumero($precio); $_SESSION['ref']['precio']=$precio;
	$precioint = limpiarNumero($precioint); $_SESSION['ref']['precioint']=$precioint;
	$prodcosto = limpiarNumero($prodcosto); $_SESSION['ref']['prodcosto']=$prodcosto;
	$resurtir = limpiarNumero($resurtir); $_SESSION['ref']['resurtir']=$resurtir;
	$cant_cotizar = limpiarNumero($cant_cotizar); $_SESSION['ref']['cant_cotizar']=$cant_cotizar;
	$local = preparar_entrada_bd($local); $_SESSION['ref']['local']=$local;

/*	if($prod_id!='') {
		$preg0 = "SELECT prod_codigo FROM " . $dbpfx . "productos WHERE prod_codigo='" . $codigo . "'";
//	echo $preg0 . '<br>';
   	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
		$filas = mysql_num_rows($matr0);
		$prod = mysql_fetch_array($matr0);
		if($filas > 0 && ($accion==="insertar" || $nuevo=='1')) { 
//			$_SESSION['ref']['mensaje'] = 'Ya existe un producto en este código de identificación.';
//			redirigir('almacen.php?accion=listar&codigo=' . $codigo); 
		}
	} else {
		$codigo = time();
	}
*/
	
//	echo 'Nombre: ' . $nombre . ' Código: ' . $codigo . '<br>'; 
	
	if($nuevo != '1') {
		if($nvaexist < 0) { $error = 1; $msj .= 'Las existencias no pueden ser menores a cero.<br>'; }
		
		if($nvaexist != $existencia) {
			$reservados = $existencia - $disponible;
			if($nvaexist >= $reservados) {
				$neok = 1;
				$nvadispo = $nvaexist - $reservados;
			} else {
				$error = 1; $msj .= 'La nueva existencia no puede ser menor a los reservados (existencia - disponibles).<br>';
			}
		} else {
			$nvadispo = $disponible;
		}
		if($neok == 1 && $motivoajuste == '') { $error = 1; $msj .= 'Se debe indicar el motivo del ajuste de existencias.<br>'; }
		
//		if($precio=='' && $margen=='') { $error = 1; $msj .= 'Por favor indique el precio de venta o el margen de venta.<br>'; }
//		if($precio < $costo && ($margen=='' || $margen <= 0 )) { $error = 1; $msj .= 'El precio de venta no puede ser menor al costo.<br>'; }
//		if(($precio < $costo || $precio == '') && $margen > 0) { $precio = round(((($costo * $margen)/100) + $costo), 2) ; }
//		if($precio >= $costo && $costo > 0) { $margen = round(((($precio * 100) / $costo) - 100), 2) ; }
//		if($precio == $costo && $costo == 0) { $margen = $defmarg; }
	} else {
//		$margen = $defmarg;
	}
	if(isset($borrar) && $borrar == '1') {$activo = 0;} else {$activo = 1;}
	if($nombre=='') { $error = 1; $msj .= 'Se requiere el nombre del producto.<br>'; }
	if($cant_cotizar == '' || $cant_cotizar == 0){
		$error = 1; $msj .= 'Debe seleccionar una cantidad a cotizar.<br>';
	}
//	if($marca=='') { $error = 1; $msj .= 'Se requiere la Marca del producto.<br>'; }
	if($almacen=='') { $error = 1; $msj .= 'Debe seleccionar un almacén.<br>'; }	
	if($tangible=='9') { $error = 1; $msj .= 'Debe seleccionar un tipo de Producto.<br>'; }	
//	if() { $error = 1; $msj .= '<br>'; }

//	if($costo < 0) { $error = 1; $msj .= 'El costo no puede ser menor que cero.<br>'; }

	if($error === 'no') {
		$preg1 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id='" . $prod_id . "'";
		//	echo $preg0 . '<br>';
   		$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");
		$fila1 = mysql_num_rows($matr1);
		$pact = mysql_fetch_array($matr1);
		if(($pact['prod_nombre'] != $nombre || $pact['prod_marca'] != $marca || $pact['prod_unidad'] != $uniprod || $pact['prod_tangible'] != $tangible) && $nuevo != '1') {
			foreach ($valarr['prod_tipo'] as $k => $v) {
				if($pact['prod_tangible'] == $k) { $nomant = $v; }
				if($tangible == $k) { $nomnvo = $v; }
			}
			$sql_data_array = array('prod_id' => $prod_id,
				'tipo' => 10, // Comentarios de cambio de identidad de producto
				'evento' => 'Cambio de: ' . $pact['prod_nombre'] . ' ' . $pact['prod_marca'] . ' ' . $pact['prod_unidad'] . ' ' . $nomant,
				'motivo' => 'A: ' . $nombre . ' ' . $marca . ' ' . $uniprod . ' ' . $nomnvo,
				'usuario' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');
		}
		if(($pact['prod_precio'] != $precio || $pact['prod_precioint'] != $precioint || $pact['prod_costo'] != $prodcosto) && $nuevo != '1') {
			$sql_data_array = array('prod_id' => $prod_id,
				'tipo' => 20, // Comentarios de cambio de precios de producto
				'evento' => 'Cambio de precios: Venta Público de ' . $pact['prod_precio'] . ' a ' . $precio . ', Venta Interno de ' . $pact['prod_precioint'] . ' a ' . $precioint . ' y Compra de ' . $pact['prod_costo'] . ' a ' . $prodcosto,
				'motivo' => 'Ajuste manual de precios',
				'usuario' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');
		}
		if(($pact['prod_almacen'] != $almacen || $pact['prod_resurtir'] != $resurtir || $pact['prod_local'] != $local) && $nuevo != '1') {
			$sql_data_array = array('prod_id' => $prod_id,
				'tipo' => 30, // Comentarios de cambio de clasificación y ubicación de producto
				'evento' => 'Cambio de: ' . $nom_almacen[$pact['prod_almacen']] . ' ' . $pact['prod_resurtir'] . ' ' . $pact['prod_local'],
				'motivo' => 'A: ' . $nom_almacen[$almacen] . ' ' . $resurtir . ' ' . $local,
				'usuario' => $_SESSION['usuario']);
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');
		}
		
		if($accion=='insertar') { $parametros = ''; } else { $parametros = 'prod_id = ' . $prod_id;}
		$sql_data_array = [
			'prod_codigo' => $codigo,
			'prod_nombre' => $nombre,
			'prod_marca' => $marca,
			'prod_unidad' => $uniprod,
			'prod_tangible' => $tangible,
			'prod_cantidad_existente' => $nvaexist,
			'prod_cantidad_disponible' => $nvadispo,
			'prod_precio' => $precio,
			'prod_precioint' => $precioint,
			'prod_costo' => $prodcosto,
			'prod_almacen' => $almacen,
			'prod_resurtir' => $resurtir,
			'prod_cant_cotizar' => $cant_cotizar,
			'prod_local' => $local,
			'prod_activo' => $activo,
		];
		
		if($prov_id != ''){
			$sql_data_array['prod_prov_id'] = $prov_id;
		}
		
		if($qv == 1){
			$sql_data_array['prod_qv'] = 1;
		} elseif($qv == 0){
			$sql_data_array['prod_qv'] = 'null';
		}
		
		if($accion=='insertar') {
			$prod_id = ejecutar_db($dbpfx . 'productos', $sql_data_array, $accion, $parametros);
		} else {
			ejecutar_db($dbpfx . 'productos', $sql_data_array, $accion, $parametros);
		} 
		
		if($neok == 1) {
			//echo 'Se actualiza existencias<br>';
			unset($sql_data_array);
			$sql_data_array = [
				'prod_id' => $prod_id,
				'tipo' => 0, // Comentarios de ajuste manual de existencias
				'evento' => 'Ajuste manual de existencias de ' . $existencia . ' a ' . $nvaexist . '.',
				'motivo' => $motivoajuste,
				'usuario' => $_SESSION['usuario']
			];
			ejecutar_db($dbpfx . 'prod_bitacora', $sql_data_array, 'insertar');
			
			// ------- Se busca si hay adeudos con paquetes de servicio ---------
			$preg_adeudos = "SELECT * FROM " . $dbpfx . "prods_pendientes WHERE prod_id = '" . $prod_id . "' AND prods_pendiente_adeudos > 0";
			//echo $preg_adeudos . '<br>';
			$matr_pendientes = mysql_query($preg_adeudos) or die("ERROR:! " . $preg_adeudos);
			$adeudos = mysql_num_rows($matr_pendientes);
			
			if($adeudos > 0){
				
				while($con_adeudos = mysql_fetch_array($matr_pendientes)){
					
					if($nvadispo >= $con_adeudos['prods_pendiente_adeudos']){ // --- Si nueva disponibilidad alcanza para surtir se procede ---
						$surtido_actualizado = $con_adeudos['prods_pendiente_surtidos'] + $con_adeudos['prods_pendiente_adeudos'];
						unset($sql_data);
						$sql_data = [
							'prods_pendiente_surtidos' => $surtido_actualizado,
							'prods_pendiente_adeudos' => 0,
						];
						$parametros = " op_id = '" . $con_adeudos['op_id'] . "' ";
						ejecutar_db($dbpfx . 'prods_pendientes', $sql_data, 'actualizar', $parametros);
						$nvadispo = $nvadispo - $con_adeudos['prods_pendiente_adeudos'];

					} elseif($nvadispo < $con_adeudos['prods_pendiente_adeudos']){ // ---- Surtir las disponibles ---
						
						$surtido_actualizado = $con_adeudos['prods_pendiente_surtidos'] + $nvadispo;
						$adeudos_actualizado = $con_adeudos['prods_pendiente_adeudos'] - $nvadispo;
						unset($sql_data);
						$sql_data = [
							'prods_pendiente_surtidos' => $surtido_actualizado,
							'prods_pendiente_adeudos' => $adeudos_actualizado,
						];
						$parametros = " op_id = '" . $con_adeudos['op_id'] . "' ";
						ejecutar_db($dbpfx . 'prods_pendientes', $sql_data, 'actualizar', $parametros);
						$nvadispo = 0;
					}
					
					// ---- Actualizar el op_id ----
					$preg_op_id = "SELECT op_cantidad, op_recibidos FROM " . $dbpfx . "orden_productos WHERE op_id = '" . $con_adeudos['op_id'] . "'";
					$matr_op_id = mysql_query($preg_op_id) or die("ERROR:! " . $preg_op_id);
					$info_op_id = mysql_fetch_assoc($matr_op_id);
					
					// --- revisar si se marca como ok el producto ---
					$total_recibido = $info_op_id['op_recibidos'] + $surtido_actualizado;
					
					if($total_recibido == $info_op_id['op_cantidad']){ // --- Ya fue surtido por completo el elemento ---
						$op_ok = 1;
						$surtido_op = $total_recibido;
					} else { // --- Aún hay pendientes por surtir ---
						$op_ok = 0;
						$surtido_op = $total_recibido;
					}
					
					unset($sql_data);
					$sql_data = [
						'op_recibidos' => $surtido_op,
						'op_ok' => $op_ok,
					];
					$parametros = " op_id = '" . $con_adeudos['op_id'] . "'";
					ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $parametros);
					
				}
				
				// ------- Actualizar nuevos disponibles ----
				unset($sql_data_array);
				$sql_data_array = [
					'prod_cantidad_disponible' => $nvadispo,
				];
				$parametros = "prod_id = '" . $prod_id . "' ";
				ejecutar_db($dbpfx . 'productos', $sql_data_array, 'actualizar', $parametros);
			}
			
			
		}
		
		
		
		if(isset($cot_prov_id) && $cot_prov_id != '') {
			$duplicado = 0;
			for($i=0;$i < count($cot_prov_id);$i++) {
				if($quitacot[$i] != '1') {
					$parametros = "prod_id = '" . $prod_id. "' AND prod_prov_id = '".$cot_prov_id[$i]."' ";
					$sql_data_array = array('prod_costo' => limpiarNumero($cot_costo[$i]),
						'dias_entrega' => $cot_entrega[$i],
						'dias_credito' => $cot_credito[$i]);
					ejecutar_db($dbpfx . 'prod_prov', $sql_data_array, 'actualizar', $parametros);
					if($cot_prov_id[$i] == $prov_id) { $duplicado = 1;}
				} else {
					$preg1 = "DELETE FROM " . $dbpfx . "prod_prov WHERE prod_id = '" . $prod_id. "' AND prod_prov_id = '" . $cot_prov_id[$i] . "'";
					$resultado = mysql_query($preg1);
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $preg1 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				}
			}
		}
		if($duplicado == 0 && $prov_id > '0') {
			/*
			$parametros = '';
			$sql_data_array = array('prod_prov_id' => $prov_id,
				'prod_costo' => limpiarNumero($costo),
				'dias_entrega' => $entrega,
				'dias_credito' => $credito,
				'prod_id' => $prod_id,
				'fecha_cotizado' => date('Y-m-d H:i:s', time()));
			ejecutar_db($dbpfx . 'prod_prov', $sql_data_array, 'insertar');
			*/
		}
		if($_FILES['imagen']) {
//			echo 'Imagen recibida';
			agrega_foto_almacen($prod_id, $_FILES['imagen'], $nombre, $dbpfx, $hacer);
		}

		unset($sql_data_array);
		unset($_SESSION['ref']);
		if($hazped != '') {
			$nvoped = explode(':', $hazped);
			redirigir('almacen.php?accion=cotpedprod&prod_id=' . $nvoped[0] .'&nvoped1=' . $nvoped[1]);
		}
		redirigir('almacen.php?accion=item&prod_id=' . $prod_id);
	} else {
   	$_SESSION['ref']['mensaje'] = $msj;
   	redirigir('almacen.php?accion=item&prod_id=' . $prod_id . '&nuevo=' . $nuevo);
   }
}

elseif($accion==='cotpedprod') {
	
	$funnum = 1115045;
	
//	unset($_SESSION['recibo']);	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	if(isset($prod_id) && $prod_id != '') {

		$preg0 = "SELECT prod_id, prod_marca, prod_nombre, prod_codigo, prod_costo, prod_tangible, prod_cantidad_existente, prod_unidad FROM " . $dbpfx . "productos WHERE prod_activo = '1' AND prod_id = '" . $prod_id . "'";
//		echo $preg0 . '<br>';
	   $matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
   		$filas = mysql_num_rows($matr0);
		$prods = mysql_fetch_array($matr0);
		if($filas > '0') {
			$_SESSION['cotped']['prod_id'][] = $prods['prod_id']; 
			$_SESSION['cotped']['prod_marca'][] = $prods['prod_marca']; 
			$_SESSION['cotped']['prod_nombre'][] = $prods['prod_nombre']; 
			$_SESSION['cotped']['prod_codigo'][] = $prods['prod_codigo'];
			$_SESSION['cotped']['prod_tangible'][] = $prods['prod_tangible']; 
			$_SESSION['cotped']['prod_unidad'][] = $prods['prod_unidad']; 
			$_SESSION['cotped']['prod_cantidad_existente'][] = $prods['prod_cantidad_existente']; 
		}
		if($nvoped1 != '') {
			$preg1 = "SELECT prod_costo FROM " . $dbpfx . "prod_prov WHERE prod_prov_id = '$nvoped1' AND prod_id = '" . $prod_id . "'";
			$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de cotizaciones de productos!".$preg1);
			$cotprov = mysql_fetch_array($matr1);
			$_SESSION['cotped']['prod_costo'][] = $cotprov['prod_costo'];
		} else {
			$_SESSION['cotped']['prod_costo'][] = $prods['prod_costo'];
		}
	}
	
	// --- Sección de foto del producto ---
	$preg3 = "SELECT doc_archivo FROM " . $dbpfx . "documentos WHERE prod_id = '" . $prod_id . "'";
	$matr3 = mysql_query($preg3) or die("ERROR: Fallo selección de productos!");
	$foto = mysql_fetch_array($matr3);
	if(!file_exists(DIR_DOCS . $foto['doc_archivo'])) { baja_archivo($foto['doc_archivo']); }
	if ($foto['doc_archivo'] != '') {
		$etiqueta = '
					<div align="center">
						<a href="' . DIR_DOCS  .$foto['doc_archivo'] . '" target="_blank"><img src="' . DIR_DOCS . 'medianas/' . $foto['doc_archivo'] . '" alt=""></a>
						<input type="hidden" name="hacer" value="actualizar"/>
					</div>'."\n";
	} else {
		$etiqueta = '
					<div align="center">
						<img src="' . DIR_DOCS . 'documento.png" alt="Sin imagen" title="Sin imagen">
						<input type="hidden" name="hacer" value="insertar"/>
					</div>'."\n";
	}

	

	include('parciales/encabezado.php'); 
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
	
	echo '
		<div class="page-content">
		
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header">
						<div class="panel-title">
		  					<h2>Cotizaciones y Pedidos de Refacciones y Consumibles</h2>
						</div>
			  		</div>
				</div>
			</div>
			<br>
			
			<form action="almacen.php?accion=gestprod" method="post" enctype="multipart/form-data">
			<div class="row">
				<div class="col-sm-12 ">
					<div class="col-sm-10">
						<div id="content-tabla">
							<table cellspacing="0" class="table-new">
								<tr>
									<th><big>Nombre</big></th>
									<th><big>Marca</big></th>
									<th><big>Código</big></th>
									<th><big>Existencias</big></th>
									<th><big>Unidad</big></th>
									<th><big>Cantidad</big></th>
									<th><big>Costo Unitario</big></th>
									<th><big>Acciones</big></th>
								</tr>'."\n";
	
	$cue = 0;
	$clase = 'claro';
	foreach($_SESSION['cotped']['prod_id'] as $k => $v) {
		echo '					
								<tr class="' . $clase . '">
								<td>
									<a href="almacen.php?accion=item&prod_id=' . $v . '">' . $_SESSION['cotped']['prod_nombre'][$k] . '</a><input type="hidden" name="nombre[' . $k . ']" value="' . $_SESSION['cotped']['prod_nombre'][$k] . '" />
								</td>
								<td>
									' . $_SESSION['cotped']['prod_marca'][$k] . '<input type="hidden" name="prod_id[' . $k . ']" value="' . $v . '" />
								</td>
								<td>
									' . $_SESSION['cotped']['prod_codigo'][$k] . '<input type="hidden" name="codigo[' . $k . ']" value="' . $_SESSION['cotped']['prod_codigo'][$k] . '" />
								</td>
								<td style="text-align:right;">
									<input type="hidden" name="existente[' . $k . ']" value="' . $_SESSION['cotped']['prod_cantidad_existente'][$k] . '">
									<input type="hidden" name="tangible[' . $k . ']" value="' . $_SESSION['cotped']['prod_tangible'][$k] . '" />' . $_SESSION['cotped']['prod_cantidad_existente'][$k] . '
								</td>
								<td style="text-align:right;">
									' . $_SESSION['cotped']['prod_unidad'][$k] . '
								</td>
								<td>
									<input type="text" name="cantidad[' . $k . ']" size="4" value="' . $_SESSION['cotped']['cantidad'][$k] . '" style="text-align:right;">
								</td>
								<td style="text-align:right;">
									<input type="text" name="costo[' . $k . ']" size="11" value="' . number_format($_SESSION['cotped']['prod_costo'][$k],2) . '" style="text-align:right;">
								</td>
								<td>
									Quitar<input type="checkbox" name="quitar[' . $k . ']" value="1">
								</td>
							</tr>'."\n";
		$cue++;
		if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
	}
	
	echo '	
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	
	echo '	
			<div class="row">
				<div class="col-md-12 panel-body">
					<div class="form-group">
      					<div class="col-md-12">
							<a href="almacen.php?accion=listar">Agregar más productos</a>
							<table>
								<tr>
									<td align="right"><big><b>Proveedor:</b></big></td>
									<td>
										<br>
										<select class="form-control" name="prov_selec[]" multiple="multiple" size="4"/>'."\n";
											foreach($provs as $k => $v) {
												echo '			<option value="' . $k . '"';
												if($k == $nvoped1) { echo ' selected="selected" '; }  // haciendo pedido desde item
												echo '>' . $v['nic'] . '</option>'."\n";
											}
	echo '		
										</select>
									</td>
								</tr>
								<tr>
									<td align="right"><big><b>Tipo de Solicitud:</b></big></td>
									<td >
										<select class="form-control" name="tipo_pedido" />
											<option value="">Seleccionar...</option>
											<option value="2">' . TIPO_PEDIDO_2 . '</option>
											<option value="3">' . TIPO_PEDIDO_3 . '</option>
											<option value="10">' . TIPO_PEDIDO_10 . '</option>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-12">
					<input type="hidden" name="bodega" value="1" />
					<input class="btn btn-success" name="enviar" value="Enviar" type="submit">
					<button class="btn btn-danger" name="limpiar" value="limpiar">Eliminar Partidas</button>	
				</div>
			</div>
			</form>
		</div>'."\n";

}

elseif($accion==="gestprod") {
	
	$funnum = 1115005;
	$funnum = 1115010;
	$funnum = 1115015;
	$funnum = 1115020;
	$funnum = 1115025;

	if ($_SESSION['rol08']=='1') {
		$mensaje = '';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	
	if(isset($limpiar) && $limpiar=='limpiar') { 
		unset ($_SESSION['cotped']);
		redirigir('almacen.php?accion=cotpedprod');
	}
	
	$error = 'no';

	if($tipo_pedido >= '2' && $tipo_pedido <= '3') {
		$cuantos_prov = count($prov_selec);
		if($cuantos_prov > 1) { $mensaje .= 'Seleccione SOLO UN proveedor para pedido.<br>'; $error = 'si'; }
		if($cuantos_prov < 1) { $mensaje .= 'Seleccione al menos UN proveedor para pedido.<br>'; $error = 'si'; }
	}
	if($tipo_pedido < 1) { $mensaje .= 'Seleccione el tipo de solicitud.<br>'; $error = 'si'; }

	foreach($prod_id as $j => $w) {
		$cantidad[$j] = limpiarNumero($cantidad[$j]);
		$costo[$j] = limpiarNumero($costo[$j]);
		if($cantidad[$j] <= 0 && $quitar[$j] != '1') {
			$mensaje .= 'La cantidad del producto ' . $nombre[$j] . ' es menor o igual a CERO.<br>'; $error = 'si';
		}
		if($costo[$j] <= 0 && $quitar[$j] != '1') {
			$mensaje .= 'El costo del producto ' . $nombre[$j] . ' es menor o igual a CERO.<br>'; $error = 'si';
		}
	}


	$j=0;

	if($error == 'no') {
		$j=0;
		if($tipo_pedido < 4) {
// ------------ Crear pedidos de almacén --------------------
			$prov_id = $prov_selec[0];
			$subtotal = 0; 
			foreach($prod_id as $j => $w) {
				if($cantidad[$j] > 0 && $costo[$j] >0 && $quitar[$j] != '1') {
					$subtotal = $subtotal + ($cantidad[$j] * $costo[$j]);
				}
			}
			$iva = round(($subtotal * $impuesto_iva), 2);
			$sql_array = array('prov_id' => $prov_id,
				'orden_id' => '999999997',
				'pedido_tipo' => $tipo_pedido,
				'subtotal' =>  $subtotal,
				'impuesto' => $iva,
				'usuario_pide' => $_SESSION['usuario']);
			$pedido = ejecutar_db($dbpfx . 'pedidos', $sql_array, 'insertar');
			$fpromped = dia_habil($provs[$prov_id]['dde']);
			$param = "pedido_id = '" . $pedido . "'";
			$sql_data = array('fecha_promesa' => $fpromped);
			$sql_data['pedido_estatus'] = 5;
			$sql_data['fecha_pedido'] = date('Y-m-d H:i:s');
			ejecutar_db($dbpfx . 'pedidos', $sql_data, 'actualizar', $param);
			foreach($prod_id as $j => $w) {
				if($quitar[$j] != '1') {
					$costoprod = limpiarNumero($costo[$j]);
					$cantprod = limpiarNumero($cantidad[$j]);
					$sql_data = array('prod_id' => $w,
						'op_codigo' => $codigo[$j],
						'op_nombre' => $nombre[$j],
						'sub_orden_id' => '999999997',
						'op_pedido' => $pedido,
						'op_cantidad' => $cantprod,
						'op_costo' => $costoprod,
						'op_tangible' => $tangible[$j],
						'op_autosurtido' => $tipo_pedido);
					ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'insertar');
					$preg1 = "UPDATE " . $dbpfx . "productos SET prod_cantidad_pedida = prod_cantidad_pedida + '" . $cantprod . "' WHERE prod_id = '$w'"."\n";
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo actualización de productos!".$preg1);
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $preg1 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				}
			}
			$acargo = constant('TIPO_PEDIDO_'.$tipo_pedido);
			$empresa = $provs[$prov_id]['nombre'];
			$contacto = $provs[$prov_id]['contacto'];
			$para = $provs[$prov_id]['email'];
			$enviar_prov = $provs[$prov_id]['env'];
			$asunto = 'Pedido ' . $pedido . ' de ' . $agencia;
			$texto_t_solicitud = 'Pedido';
		} elseif($tipo_pedido == '10') {
			$para =''; $cuenta = 0;
			foreach($prov_selec as $k) {
				if($provs[$k]['env'] == '1') {$enviar_prov = 1;}
				foreach($prod_id as $j => $w) {
					$preg1 = "SELECT prod_id FROM " . $dbpfx . "prod_prov WHERE prod_id = '$w' AND prod_prov_id = '$k'";
					$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos a proveedores! " . $preg1);
					$fila1 = mysql_num_rows($matr1);
					$sql_data = array(
						'prod_id' => $w, 
						'prod_prov_id' => $k,
						'prod_costo' => NULL,
						'dias_entrega' => NULL,
						'dias_credito' => NULL,
						'fecha_cotizado' => date('Y-m-d H:i:s', time())
					);
					if($fila1 > 0) { 
						$param = "prod_id = '$w' AND prod_prov_id = '$k'";
						ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'actualizar', $param);
					} else { 
						ejecutar_db($dbpfx . 'prod_prov', $sql_data, 'insertar');
					}
				}
			}
			$asunto = 'Cotización para ' . $agencia;
			$texto_t_solicitud = 'Cotizar';
		}
		
/* ------------ Enviar por e-mail pedidos a Proveedores --------------------*/
//		echo ' Por enviar correo!';
		if($enviar_prov == '1') {
						
			require ('parciales/PHPMailerAutoload.php');

			$mail = new PHPMailer;

			$mail->CharSet = 'UTF-8';
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $smtphost;  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = $smtpusuario;                 // SMTP username
			$mail->Password = $smtpclave;                           // SMTP password
//			$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
			$mail->Port       = $smtppuerto; 

			$mail->From = constant('EMAIL_PROVEEDOR_FROM');
			$mail->FromName = 'Refacciones de ' . $nombre_agencia;
			
			if($tipo_pedido < '4') {
				$ma = explode(',', $provs[$prov_id]['email']);
				foreach($ma as $k) {
					$mail->addAddress($k);     // Add a recipient
				}
			} else {
				foreach($prov_selec as $k) {
					$ma = explode(',', $provs[$k]['email']); 
					foreach($ma as $j) {
						$mail->addAddress($j);     // Add a recipient
					}
				}
			} 
//			$mail->addAddress($para);     // Add a recipient

			$mail->addReplyTo(constant('EMAIL_PROVEEDOR_RESPONDER'));

			$ma = explode(',', constant('EMAIL_PROVEEDOR_CC'));
			foreach($ma as $k) {
				$mail->addCC($k);     // Add a recipient
			}
//			$mail->addCC(constant('EMAIL_PROVEEDOR_CC'));

			if($bcc) { $mail->addCC($bcc);}
			$mail->addBCC('monitoreo@controldeservicio.com');

			$email_order = '<!DOCTYPE HTML PUBLIC \'-//W3C//DTD HTML 4.0 Transitional//EN\'><html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" /></head><body style="font-family:Arial;">';
			if($tipo_pedido < 4) {
				$email_order .= '<p>Estimad@ ' . $contacto . '<br>' . $empresa . '</p>'."\n";
				$email_order .= '<p>' . EMAIL_TEXT_DESCRIPCION . '</p>'."\n";
			} else {
				$email_order .= '<p>Estimados Proveedores.</p>'."\n";
				$email_order .= '<p>' . EMAIL_TEXT_COTIZACION . '</p>'."\n";
			}

			$email_order .= '<table cellpadding="2" cellspacing="0" border="1" width="800"><tr><td colspan="2">' . $nombre_agencia . '</td><td><strong>' . $texto_t_solicitud . ': </strong>' . $pedido . '</td><td><strong>';
			$email_order .= $acargo;
			$email_order .= '</strong></td><td>Fecha: ' . date('Y-m-d') . '</td></tr>'."\n";
			$email_order .= '</table>'."\n";
			$email_order .= '<table cellpadding="2" cellspacing="0" border="1" width="800"><tr><td>Cantidad</td><td>Nombre</td><td>Código</td></tr>';
			foreach($prod_id as $j => $w) {
				if($quitar[$j] != '1') {
					$cantprod = limpiarNumero($cantidad[$j]);
					$email_order .= '<tr><td>' . $cantprod . '</td><td>' . $nombre[$j] . '</td><td>' . $codigo[$j] . '</td></tr>'."\n";
				}
			}

			$email_order .= '</table>'."\n";
			$email_order .= '<p>Atentamente.<br><br>' . JEFE_DE_ALMACEN . '<br>'.$agencia_razon_social."<br>".$agencia_direccion."<br>Col. ".$agencia_colonia.", ".$agencia_municipio."<br>C.P.: ".$agencia_cp.". ".$agencia_estado."<br>E-mail: ".EMAIL_DE_ALMACEN."<br>Tels: ".$agencia_telefonos.'<br>' . TELEFONOS_ALMACEN . '</p>';
			$email_order .= '</body></html>';

			$mail->isHTML(true);                                  // Set email format to HTML

			$mail->Subject = $asunto;
			$mail->Body    = $email_order;
//			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			if(!$mail->send()) {
				$mensaje = 'Errores en notificación automática: ';
				$mensaje .=  $mail->ErrorInfo;
	   		$_SESSION['msjerror'] = $mensaje;
			} 
		}
//		echo $email_order;
		
		unset($_SESSION['cotped']); 
		if($tipo_pedido < 4) {
			redirigir('pedidos.php?accion=consultar&pedido=' . $pedido);
		}
		redirigir('almacen.php?accion=cotpedprod');
	} else {
   	$_SESSION['msjerror'] = $mensaje;
   	redirigir('almacen.php?accion=cotpedprod');
   }
}

elseif($accion==='listpaqs') {
	
	$funnum = 1115055;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	
	echo '
		<div class="page-content">
		
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header">
						<div class="panel-title">
		  					<h2>
								Paquetes de Servicio '."\n";
								if($codigo != ''){
									echo 'Código: ' . $codigo . ' ';
								}
								if($nombre != ''){
									echo 'Nombre: ' . $nombre . ' ';
								}
	echo '
							</h2>
						</div>
			  		</div>
				</div>
			</div>
			
			<div class="row"> <!- mensajes -->
				<div class="col-md-12">
	  				<span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-6 panel-body shadow-box">
					<div class="form-group">
      					<div class="col-md-12">
							<form action="almacen.php?accion=listpaqs" method="post" enctype="multipart/form-data">
							<table>
								<tr>
									<td align="right" class="obscuro"><big><b>Buscar por código:</b></big></td>
									<td>
										<input class="form-control" type="text" name="codigo" id="codigo" size="15">
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Buscar por nombre:</b></big></td>
									<td>
										<input class="form-control" type="text" name="nombre" size="15">
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td align="right" class="obscuro"><big><b>Filtrar por Área de Servicio:</b></big></td>
									<td>
										<select class="form-control" name="area" size="1">
											<option value="">Seleccionar...</option>'."\n";
											for($i=1;$i<=$num_areas_servicio;$i++) {
											echo '				
												<option value="' . $i . '">' . constant('NOMBRE_AREA_' . $i) . '</option>'."\n";
											}											
	echo '			
										</select>
									</td>
									<td>
										<input class="btn btn-success" name="Enviar" value="Enviar" type="submit">
									</td>
								</tr>
								<tr>
									<td>
										<a href="almacen.php?accion=paquete&nuevo=1">
											<img src="idiomas/' . $idioma . '/imagenes/agregar_paquete_servicio.png" width="50" height="50"><br>
											Nuevo Paquete
										</a>
									</td>
								</tr>
							</table>
							</form>
						</div>
					</div>'."\n";
	
	if((isset($area) && $area!='') || (isset($nombre) && $nombre!='') || (isset($codigo) && $codigo!='')) { 
		if(isset($area) && $area!='') { $preg .= "AND paq_area='" . $area . "' "; }
		if(isset($nombre) && $nombre!='') { $preg .= "AND paq_nombre LIKE '%" . $nombre . "%' "; }
		if(isset($codigo) && $codigo!='') { $preg .= "AND paq_nic LIKE '%" . $codigo . "%' "; }
	}
	
	$preg0 = "SELECT paq_id FROM " . $dbpfx . "paquetes WHERE paq_activo='1' ";
	$preg0 = $preg0 . $preg;
	//echo $preg0 . '<br>';
	$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
	$filas = mysql_num_rows($matr0);
	$renglones = 20;
	$paginas = (round(($filas / $renglones) + 0.49999999) - 1);

   	if(!isset($pagina)) { $pagina = 0;}
	$inicial = $pagina * $renglones;
	//echo $paginas;
	$preg1 = "SELECT * FROM " . $dbpfx . "paquetes WHERE paq_activo = '1' ";
	$preg1 = $preg1 . $preg;
	$preg1 .= "ORDER BY paq_area, paq_nombre LIMIT " . $inicial . ", " . $renglones;
	//echo $preg1;
   	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de paquetes!");

	echo '
					<div align="right">
						<a href="almacen.php?accion=listpaqs&pagina=0">Inicio</a>&nbsp;';

	if($pagina > 0) {
		$url = $pagina - 1;
		echo '<a href="almacen.php?accion=listpaqs&pagina=' . $url . '">Anterior</a>&nbsp;';
	}
	if($pagina < $paginas) {
		$url = $pagina + 1;
		echo '<a href="almacen.php?accion=listpaqs&pagina=' . $url . '">Siguiente</a>&nbsp;';
	}
	echo '<a href="almacen.php?accion=listpaqs&pagina=' . $paginas . '">Ultima</a>'."\n";
		
	echo '	
					</div>
					<div id="content-tabla">
						<table cellspacing="0" class="table-new">
							<tr>
								<th><big>Área</big></th>
								<th><big>Nombre</big></th>
								<th><big>Nic</big></th>
								<th><big>Acciones</big></th>
							</tr>'."\n";
	
	$cue = 0;
	$clase = 'claro';
	while($paq = mysql_fetch_array($matr1)){
		echo '					
							<tr class="' . $clase . '">
								<td>' . constant('NOMBRE_AREA_'.$paq['paq_area']) . '</td>
								<td>' . $paq['paq_nombre'] . '</td>
								<td>' . $paq['paq_nic'] . '</td>
								<td>
									<a href="almacen.php?accion=paquete&paq_id=' . $paq['paq_id'] . '">Listar</a> / <a href="almacen.php?accion=paquete&paq_id=' . $paq['paq_id'] . '&quitar=1">Remover</a>
								</td>
							</tr>'."\n";
		$cue++;
		if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
	}
	
					
	echo '
						</table>
					</div>
				</div>
			</div>
			<br>
		</div>'."\n";
}

elseif($accion==='paquete') {
	
	$funnum = 1115060;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
		
	if($paq_id!='') {
		$preg0 = "SELECT * FROM " . $dbpfx . "paquetes WHERE paq_id='" . $paq_id . "'";
		//echo $preg0 . '<br>';
   		$matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
	   	$paq = mysql_fetch_array($matr0);
	}
	
	echo '
		<div class="page-content">
		
			<div class="row"> <!-box header del título. -->
				<div class="col-md-12">
	  				<div class="content-box-header">
						<div class="panel-title">'."\n";
						
						if($paq_id != ''){
							echo ' <h2>Paquete de Servicio  "' . $paq['paq_nombre'] . '" </h2>'."\n";
						} else{
							echo ' <h2>Nuevo Paquete de Servicio</h2>'."\n";
						}
	
	echo '
						</div>
			  		</div>
				</div>
			</div>
			
			<div class="row"> <!- mensajes -->
				<div class="col-md-12">
	  				<span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span>
				</div>
			</div>'."\n";
	
	
	echo '	
			<form action="almacen.php?accion=';
	if($nuevo=='1') { echo 'inspaq';} else { echo 'actpaq';}
	echo '" method="post" enctype="multipart/form-data">'."\n";
	
	echo '
			<div class="row">
				<div class="col-md-12 panel-body">
					<div class="form-group">
      					<div class="col-md-12">
							<table>
								<tr>
									<td align="right"><big><b>Nombre:</b></big></td>
									<td>
										<input class="form-control" type="text" name="nombre" size="50" maxlength="255" value="'; 
										echo ($_SESSION['ref']['nombre']) ? $_SESSION['ref']['nombre']:$paq['paq_nombre']; echo '" />
									</td>
								</tr>
								<tr>
									<td align="right"><big><b>Descripción:</b></big></td>
									<td>
										<input class="form-control" type="text" name="descripcion" size="50" maxlength="255" value="'; 
										echo ($_SESSION['ref']['descripcion']) ? $_SESSION['ref']['descripcion']:$paq['paq_descripcion']; echo '" />
									</td>
								</tr>
								<tr>
									<td align="right"><big><b>Nic:</b></big></td>
									<td>
										<input class="form-control" type="text" name="nic" size="40" maxlength="32" value="'; 
										echo ($_SESSION['ref']['nic']) ? $_SESSION['ref']['nic']:$paq['paq_nic']; echo '" />
									</td>
								<tr>
									<td align="right"><big><b>Area Padre:</b></big></td>
									<td>
										<select class="form-control" name="areapadre" size="1">
											<option value="" disabled selected>Seleccionar...</option>'."\n";

											for($i=1;$i<=$num_areas_servicio;$i++) {
												echo '					
													<option value="'.$i.'" '; if($paq['paq_area']==$i) {echo 'selected="selected" ';} echo '>' . constant('NOMBRE_AREA_'.$i) . '</option>'."\n";
											}
	echo '					
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";

		
	if($nuevo!='1') {
		
		echo '
			<div class="row">
				<div class="col-md-12">
	  				<div>
						<div class="panel-title">
		  					<h2>Productos de este Paquete</h2>
						</div>
			  		</div>
				</div>
				<a href="almacen.php?accion=listar&paquete=' . $paq_id . '">
					<img src="idiomas/' . $idioma . '/imagenes/agregar_producto.png" width="50" height="50"><br>Agregar Nuevo<br>Producto
				</a><br>
				Primero agregue cada una de las refacciones, consumibles y mano de obra que integraran el paquete y<br>después indique a que área serán asignadas así como su cantidad.
				
			</div>'."\n";
		
		
		echo '
			<div class="row">
				<div class="col-md-10 panel-body">
					<div class="form-group">
      					<div class="col-md-12">
							<table cellspacing="0" class="table-new">
								<tr>
									<th><big>Area</big></th>
									<th><big>Nombre</big></th>
									<th><big>Marca</big></th>
									<th><big>Código</big></th>
									<th><big>Cant</big></th>
									<th><big>Precio</big></th>
									<th><big>Acciones</big></th>
								</tr>'."\n";

	$preg1 = "SELECT pc_id, pc_prod_id, pc_prod_cant, pc_area_id FROM " . $dbpfx . "paq_comp WHERE pc_paq_id='" . $paq_id . "'";
	//echo $preg1 . '<br>';
  	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");

   	while($pc = mysql_fetch_array($matr1)) {
		$preg2 = "SELECT * FROM " . $dbpfx . "productos WHERE prod_id='" . $pc['pc_prod_id'] . "'";
		//echo $preg2 . '<br>';
  		$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de productos!");
		$clase = 'claro';
  		while($prod = mysql_fetch_array($matr2)) {
  			echo '					
								<tr class="' . $clase . '">
									<td>
										<select class="form-control" name="area['.$pc['pc_id'].']" size="1" required>
											<option value="" disabled selected>Seleccionar...</option>'."\n";
										for($i=1;$i<=$num_areas_servicio;$i++) {
											echo '					
												<option value="'.$i.'" ';
											if($pc['pc_area_id']==$i){
												echo 'selected="selected" ';
											} 
											echo '>' . constant('NOMBRE_AREA_'.$i) . '</option>'."\n";
										}

				echo '		
										</select>
									</td>
									<td>
										'.$prod['prod_nombre'].'
									</td>
									<td>
										'.$prod['prod_marca'].'
									</td>
									<td>
										'.$prod['prod_codigo'].'
									</td>
									<td style="text-align:center;">
										<input type="text" name="cantidad['.$pc['pc_id'].']" size="1" value="'.$pc['pc_prod_cant'].'">
									</td>
									<td>
										'. money_format('%n', $prod['prod_precio']) .'</td><td>Quitar <input type="checkbox" name="quitar['.$pc['pc_id'].']" value="1">
									</td>
								</tr>'."\n";
				if($clase == 'claro') { $clase = 'obscuro'; } else { $clase = 'claro'; }
	  	}
   	}
	
	echo '		
								<tr class="claro">
									<td>Borrar Paquete?</td>
									<td> 
										<input type="checkbox" name="borrar" value="1"/>
									</td>
									<td colspan="5"></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>'."\n";
	} 
	echo '
			<div class="row">
				<div class="col-md-12">
					<input type="hidden" name="paq_id" value="' . $paq['paq_id'] . '" />
					<input type="hidden" name="nuevo" value="' . $nuevo . '" />
					<input class="btn btn-success" type="submit" value="Enviar" />
				</div>
			</div>
			</form>
		</div>'."\n";
}

elseif($accion==="inspcpaq") {
	
	$funnum = 1115065;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Rol Almacén, ingresar Usuario y Clave correcta.');
	}
	$sql_data_array = array('pc_paq_id' => $paquete,
		'pc_prod_id' => $prod_id,
		'pc_prod_cant' => '1');
	ejecutar_db($dbpfx . 'paq_comp', $sql_data_array, 'insertar');
	bitacora('999997', 'Nuevo producto '.$prod_id.' agregado al paquete '.$paquete, $dbpfx);
	unset($sql_data_array);
	unset($_SESSION['ref']);
	redirigir('almacen.php?accion=paquete&paq_id=' . $paquete);
}

elseif($accion==="inspaq" || $accion==="actpaq") {
	
	$funnum = 1115065;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Rol Almacén, ingresar Usuario y Clave correcta.');
	}
	unset($_SESSION['ref']);
	$_SESSION['ref'] = array();
	$mensaje = '';
	$error = 'no'; 

	$nombre = preparar_entrada_bd($nombre); $_SESSION['ref']['nombre']=$nombre;
	$descripcion = preparar_entrada_bd($descripcion); $_SESSION['ref']['nombre']=$descripcion;
	$nic = preparar_entrada_bd($nic); $_SESSION['ref']['nic']=$nic;
//	$area = preparar_entrada_bd($area); $_SESSION['ref']['area']=$area;
	
	if(isset($borrar) && $borrar == '1') {$activo = 0;} else {$activo = 1;}
	if($nombre=='') { $error = 1; $msj .= 'Se requiere el nombre del paquete.<br>'; }
	if($descripcion=='') { $error = 1; $msj .= 'Se requiere la descripción del paquete.<br>'; }
	if($nic=='') { $error = 1; $msj .= 'Se requiere asignar un nombre corto o nic.<br>'; }
//	if($area=='') { $error = 1; $msj .= 'Debe seleccionar una área de servicio en que se utilizará este paquete.<br>'; }
		
	
	if($error === 'no') {
		if($accion=='inspaq'){
			$parametros = '';
			$modifica = 'insertar'; 
		} else{ 
			$parametros = 'paq_id = ' . $paq_id; 
			$modifica = 'actualizar';
		} 
		$sql_data_array = [
			'paq_nombre' => $nombre,
			'paq_descripcion' => $descripcion,
			'paq_area' => $areapadre,
			'paq_nic' => $nic,
			'paq_activo' => $activo
		];
		if($accion=='inspaq') {
			$paq_id = ejecutar_db($dbpfx . 'paquetes', $sql_data_array, $modifica, $parametros);
			$concepto = 'Paquete ' . $paq_id . ' agregado'; 
		} else {
			ejecutar_db($dbpfx . 'paquetes', $sql_data_array, $modifica, $parametros);
			foreach($cantidad as $k => $v) {
				$preg0 = "UPDATE " . $dbpfx . "paq_comp SET pc_prod_cant = '" . $v . "', pc_area_id = '" . $area[$k] . "' WHERE pc_id = '" . $k . "'";
//				echo $preg0 . '<br>';
				$matr0 = mysql_query($preg0) or die("ERROR: Fallo actualización de productos!");
				$archivo = '../logs/' . time() . '-base.ase';
				$myfile = file_put_contents($archivo, $preg0 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
			}
			foreach($quitar as $k => $v) {
				if($v == '1') { 
					$preg0 = "DELETE FROM " . $dbpfx . "paq_comp WHERE pc_id = '" . $k . "'";
//						echo $preg0 . '<br>';
					$matr0 = mysql_query($preg0) or die("ERROR: Fallo remoción de productos!");
					$archivo = '../logs/' . time() . '-base.ase';
					$myfile = file_put_contents($archivo, $preg0 . ';'.PHP_EOL , FILE_APPEND | LOCK_EX);
				}
			} 
			$concepto = 'Paquete ' . $paq_id . ' modificado';
		}
		bitacora('999998', $concepto, $dbpfx);
		unset($_SESSION['ref']);
		redirigir('almacen.php?accion=listpaqs');
	} else {
   	$_SESSION['ref']['mensaje'] = $msj;
   	redirigir('almacen.php?accion=paquete&prod_id=' . $paq_id . '&nuevo=' . $nuevo);
   }
   
}

elseif($accion==='retorno') {
	
	$funnum = 1115075;
	
	if ($_SESSION['rol08']!='1') {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
	
	echo '	<form action="almacen.php?accion=recibo" method="post" enctype="multipart/form-data">'."\n";
	echo '	<table cellpadding="0" cellspacing="0" border="0" class="agrega">
		<tr><td colspan="2"><span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span></td></tr>'."\n";
	unset($_SESSION['ref']['mensaje']);
	echo '		<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Retorno de Refacciones y Consumibles al Almacén</td></tr>'."\n";
	echo '			<tr><td colspan="2" style="text-align:left;">
				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td style="width:120px;">Marca</td><td style="width:100px;">Código</td><td style="width:300px;">Nombre</td><td style="width:30px;">Existencias</td><td style="width:75px;">Costo</td><td style="width:30px;">Recibir</td><td>Acciones</td></tr>'."\n";
	$cue = 0;
	foreach($_SESSION['recibo']['prod_id'] as $k => $v) {
		echo '					<tr>
						<td>' . $_SESSION['recibo']['prod_marca'][$k] . '<input type="hidden" name="prod_id[' . $k . ']" value="' . $v . '" /></td>
						<td>' . $_SESSION['recibo']['prod_codigo'][$k] . '</td>
						<td>' . $_SESSION['recibo']['prod_nombre'][$k] . '</td>
						<td style="text-align:right;"><input type="hidden" name="existente[' . $k . ']" value="' . $_SESSION['recibo']['prod_cantidad_existente'][$k] . '">' . $_SESSION['recibo']['prod_cantidad_existente'][$k] . '</td>
						<td style="text-align:right;"><input type="text" name="costo[' . $k . ']" size="11" value="' . money_format('%n', $_SESSION['recibo']['prod_costo'][$k]) . '" style="text-align:right;"></td>
						<td><input type="text" name="recibir[' . $k . ']" size="4" style="text-align:right;"></td>
						<td>Quitar<input type="checkbox" name="quitar[' . $k . ']" value="1"></td></tr>'."\n";
		$cue++;
	}
	echo '				</table>'."\n";
	echo '			</td>
		</tr>'."\n";

	$preg1 = "SELECT prov_id, prov_nic FROM " . $dbpfx . "proveedores WHERE prov_activo='1'";
//	echo $preg0 . '<br>';
   $matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de proveedores!");

	echo '		<tr><td colspan="2" style="text-align:left;"><a href="almacen.php?accion=listar">Agregar más productos</a></td></tr>'."\n";
	echo '		<tr><td style="text-align:left; width:150px;">Proveedor</td><td style="text-align:left; width:85%;"><select name="prov_id" size="1">
			<option value="">Seleccionar...</option>'."\n";
	while($prov = mysql_fetch_array($matr1)) {
		echo '			<option value="'.$prov['prov_id'].'" '; if($prod['prod_prov_id']==$prov['prov_id']) {echo 'selected="selected" ';} echo '>'.$prov['prov_nic'].'</option>'."\n";
	}
	echo '		</select></td></tr>'."\n";
	echo '		<tr><td style="text-align:left; width:150px;">Número de factura: </td><td style="text-align:left;"><input type="text" name="factura" size="20"></td></tr>'."\n";
	echo '		<tr><td colspan="2" style="text-align:left;"><input name="enviar" value="Recibir" type="submit"></td></tr>'."\n";
	echo '		<tr><td colspan="2"><hr></td></tr>'."\n";
	echo '	</table></form>'."\n";

}

elseif($accion==="pventa") {
	
	$funnum = 1115080;
	
	if ($_SESSION['rol02']=='1' || $_SESSION['rol12']=='1') {
		$msj='Acceso autorizado';
	} else {
		redirigir('usuarios.php?mensaje=Acceso NO autorizado ingresar Usuario y Clave correcta');
	}
	unset($_SESSION['ref']);
	$_SESSION['ref'] = array();
	$_SESSION['ref']['mensaje']='';
	$mensaje = '';
	$error = 'no';

	if($error == 'no') {
		$pv_rev = 1;
		foreach($op_id as $j => $w) {
			$precio[$j] = limpiarNumero($precio[$j]);
			$param = "op_id = '" . $w . "'";
			if($op_precio_revisado[$j] < '1') {
				$sql_data['op_precio_original'] = $op_precio_original[$j]; 
			}
			if($precio[$j] > 0) { 
				$sql_data['op_precio_revisado'] = '1'; 
				$sql_data['op_precio'] = $precio[$j];
				ejecutar_db($dbpfx . 'orden_productos', $sql_data, 'actualizar', $param);
				$concepto = 'Precio Revisado en OP:' . $w;
				bitacora($orden_id, $concepto, $dbpfx);
			}
		}
		
		redirigir('ordenes.php?accion=consultar&orden_id=' . $orden_id); 
	} else {
   	$_SESSION['ref']['mensaje'] = $mensaje;
   	redirigir('almacen.php?accion=gestionar&orden_id=' . $orden_id);
   }
}

elseif($accion==='generacb') {
	
	$funnum = 1115095;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || $_SESSION['rol08']=='1') {
		$mensaje='';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}

	if($limpiar == 'Restablecer Filtros') { $codigo=''; $nombre=''; $almacen=''; }
	echo '		<form action="almacen.php?accion=generacb" method="post" enctype="multipart/form-data">'."\n";
	echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">
			<tr><td colspan="2"><span class="alerta">' . $_SESSION['ref']['mensaje'] . '</span></td></tr>'."\n";
	unset($_SESSION['ref']['mensaje']);
	echo '			<tr class="cabeza_tabla"><td colspan="2" style="text-align:left; font-size:16px;">Generación de Códigos de Barras para Productos del Almacén</td></tr>'."\n";
	echo '			<tr class="obscuro"><td style="text-align:left; width:50%;">Buscar por codigo: <input type="text" name="codigo" id="codigo" size="15" value="' . $codigo . '">
				<input name="Enviar" value="Enviar" type="submit"></td><td style="width:50%;">Buscar por nombre: <input type="text" name="nombre" size="15" value="' . $nombre . '">
				<input name="Enviar" value="Enviar" type="submit"></td></tr>
			<tr class="obscuro"><td style="text-align:left; width:50%;">Filtrar por Almacén: 
				<select name="almacen" size="1">
					<option value="">Seleccionar...</option>'."\n";
	foreach($nom_almacen as $k => $v) {
		echo '					<option value="' . $k . '"';
		if($almacen == $k) { echo ' selected="selected" ';}
		echo '>' . $v . '</option>'."\n";
	}											
	echo '				</select>
				<input name="Enviar" value="Enviar" type="submit"><br><input type="submit" name="limpiar" value="Restablecer Filtros">'."\n";
	echo '			</td><td style="text-align:right; width:50%;">'."\n";
	echo '&nbsp;<a href="almacen.php?accion=listar"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Listado de Refacciones y Productos" title="Listado de Refacciones y Productos"></a>'."\n";
	echo '			</td></tr></table></form>'."\n";
	if((isset($almacen) && $almacen!='') || (isset($nombre) && $nombre!='') || (isset($codigo) && $codigo!='')) { 
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
	}
	if($_SESSION['ref']['pedpend'] == 1) { $preg .= "AND prod_cantidad_pedida > '0' "; }
	
	echo '		<table cellpadding="0" cellspacing="0" border="0" class="agrega">'."\n";
	$preg0 = "SELECT prod_id FROM " . $dbpfx . "productos WHERE prod_activo='1' ";
	$preg0 = $preg0 . $preg;
//	echo $preg0 . '<br>';
   $matr0 = mysql_query($preg0) or die("ERROR: Fallo selección de productos!");
   $filas = mysql_num_rows($matr0);
   if($filas == 0 && $codigo!='') {
   	echo '		<tr><td colspan="2">No se encontró ningún producto con el código ' . $codigo . ',<br>¿Desea agregarlo como nuevo a la lista de productos? <a href="almacen.php?accion=item&nuevo=1&codigo=' . $codigo . '">Agregar</a>&nbsp;</td></tr>';
   } else {
	   $renglones = 100;
	   $paginas = (round(($filas / $renglones) + 0.49999999) - 1);
   	if(!isset($pagina)) { $pagina = 0;}
	   $inicial = $pagina * $renglones;
//	echo $paginas;
			$preg1 = "SELECT prod_id, prod_marca, prod_codigo, prod_nombre, prod_cantidad_pedida, prod_cantidad_existente, prod_cantidad_disponible, prod_precio, prod_almacen, prod_tangible FROM " . $dbpfx . "productos WHERE prod_activo = '1' ";
			$preg1 = $preg1 . $preg;
			$preg1 .= " GROUP BY prod_id ORDER BY prod_almacen, prod_nombre LIMIT " . $inicial . ", " . $renglones;
//	echo $preg1;
   	$matr1 = mysql_query($preg1) or die("ERROR: Fallo selección de productos!");

		echo '			<tr><td colspan="2"><a href="almacen.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="almacen.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="almacen.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="almacen.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>';
		echo '</td></tr>'."\n";
		echo '			<tr><td colspan="2" style="text-align:left;">'."\n";
		echo '				<table cellpadding="0" cellspacing="0" border="1" class="izquierda">
					<tr><td style="width:120px;">Almacén</td><td style="width:150px;">Nombre</td><td style="width:80px;">Marca</td><td style="width:0px;">Código</td><td style="width:30px;">Existencia</td><td style="width:100px;">Precio Unitario<br>de Venta</td><td style="text-align:center;">Cuantos?<br>';
		echo '<form action="almacen.php?accion=generacb&almacen=' . $almacen . '&nombre=' . $nombre . '&codigo=' . $codigo . '" method="post" enctype="multipart/form-data" name="partidas"><input type="checkbox" name="presel" value="1" ';
				if($presel == '1') { echo 'checked="checked" '; }
				echo 'onchange="document.partidas.submit()"; /></form>';
		echo '				<form action="almacen.php?accion=imprimecb" method="post" enctype="multipart/form-data" name="items">'."\n";
		echo '</td></tr>'."\n";
		$cue = 0;
		while($prods = mysql_fetch_array($matr1)) {
			echo '					<tr><td>';
			echo $nom_almacen[$prods['prod_almacen']]; 
			echo '</td><td>' . $prods['prod_nombre'] . '</td><td>' . $prods['prod_marca'] . '</td><td>' . $prods['prod_codigo'] . '</td><td style="text-align:right;">' . $prods['prod_cantidad_existente'] . '</td><td style="text-align:right;">$' . number_format($prods['prod_precio'],2) . '</td><td>';
			echo '<input type="hidden" name="imprimecb[' . $cue . ']" value="' . $prods['prod_codigo'] . '|' . $prods['prod_nombre'] . '|' . $prods['prod_marca'] . '" />';
			echo '<input type="text" name="cuantoscb[' . $cue . ']" size="2" style="text-align:center;" ';
			if($presel == '1') { 
				echo ' value="1">';
			} else {
				echo ' value="">';
			}
			echo '</td></tr>'."\n";
			$cue++;
		}
		echo '				</table>'."\n";
		echo '			</td></tr>'."\n";
		echo '			<tr><td colspan="2"><a href="almacen.php?accion=listar&pagina=0&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Inicio</a>&nbsp;';
		if($pagina > 0) {
			$url = $pagina - 1;
			echo '<a href="almacen.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Anterior</a>&nbsp;';
		}
		if($pagina < $paginas) {
			$url = $pagina + 1;
			echo '<a href="almacen.php?accion=listar&pagina=' . $url . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Siguiente</a>&nbsp;';
		}
		echo '<a href="almacen.php?accion=listar&pagina=' . $paginas . '&codigo=' . $codigo . '&nombre=' . $nombre . '&almacen=' . $almacen . '">Ultima</a>';
		echo '</td></tr>'."\n";
	}
	echo '			<tr><td colspan="2" style="text-align: left;"><input type="submit" name="Imprimir" value="Imprimir"></td></tr>'."\n";
	echo '			<tr><td colspan="2"><hr>
					<input type="hidden" name="nombre" value="' . $nombre . '">
					<input type="hidden" name="almacen" value="' . $almacen . '">
					<input type="hidden" name="codigo" value="' . $codigo . '">
				</td></tr>'."\n";
	echo '		</table>'."\n";
}

elseif($accion==='imprimecb') {

	$funnum = 1115095;
	$retorno = 0; $retorno = validaAcceso($funnum, $dbpfx);
	if ($retorno == '1' || $_SESSION['rol08']=='1') {
		$mensaje='';
	} else {
		 redirigir('usuarios.php?mensaje=Acceso sólo para Almacén, ingresar Usuario y Clave correcta');
	}
//	print_r($imprimecb);
	echo '		<table cellspacing="15" cellpadding="5" border="1" width=840>'."\n";
	$sp = 1;
	foreach($cuantoscb as $k => $v) {
//		$v = limpiaNumero($v);
		$eti = explode("|", $imprimecb[$k]);
		if($j==0) { echo '			<tr>'."\n";}
		for($i=1; $i<=$v; $i++) {
			if($eti[0] != '' && $eti[0] != ' ') {
				echo '				<td valign="top" style="text-align:center;">' . $eti[1] . ' ' . $eti[2] . '<br><img src="parciales/barcode.php?barcode=' . $eti[0] . '&width=380&height=110"><br>' . $eti[0] . '</td>' . "\n";
				$j++; $sp++;
				if($j==2) { echo '			</tr>'."\n"; $j=0;}
				if($sp == 13) {
					echo '		</table>'."\n";
					echo '		<div class="saltopagina"></div> '."\n";
					echo '		<table cellspacing="15" cellpadding="5" border="1" width=840>'."\n";
					$sp = 1;
				}
			}
		}
	}
	echo '			<tr><td colspan="2" style="text-align:left;"><div class="control"><a href="almacen.php?accion=generacb&almacen=' . $almacen . '&nombre=' . $nombre . '&codigo=' . $codigo . '"><img src="idiomas/' . $idioma . '/imagenes/regresar.png" alt="Regresar a Selección de Productos" title="Regresar a Selección de Productos"></a></div></td></tr>'."\n";
	echo '		</table>'."\n";
}

?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>
