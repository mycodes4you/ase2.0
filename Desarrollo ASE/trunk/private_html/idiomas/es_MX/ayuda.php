<?php
foreach($_POST as $k => $v){$$k=$v;} //  echo $k.' -> '.$v.' | ';
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';
/*include('../..parciales/funciones.php');
if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}
*/
echo '<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<title>Ayuda AutoShop Easy</title>
		<link href="../../css/estilos.css" type="text/css" rel="stylesheet" />
	</head>
	<body>
		<div id="container">'."\n";

include($base);

if($apartado == $lang['Estatus'] && $base == 'reportes.php') {
	echo '			<h1>' . $lang['Estatus'] . '</h1>
			<div id="body">
				<div id="principal">
					<ul>
						<li><b>' . $lang['Pendiente'] . ':</b> Producto o servicio pedido aún no recibido.</li>
						<li><b>' . $lang['Recibido sin asignación'] . ':</b> Recibido pero no se ha registrado el inicio de trabajos.</li>
						<li><b>' . $lang['Recibido sin entregar'] . ':</b> No se ha registrado a que Operario se entregó.</li>
						<li><b>' . $lang['Entregado a'] . ':</b> Se indica el nombre del Operario que recibió el Producto o servicio.</li>
						<li><b>' . $lang['Por Devolver'] . ':</b> Producto o servicio recibido en una tarea que se determinó como NO reparable por lo que se debe realizar la devolución correspondiente.</li>
						<li><b>' . $lang['Por Entregar'] . ':</b> Producto o servicio instalado y terminado por entregar al cliente.</li>
						<li><b>' . $lang['Entregado'] . ':</b> Producto o servicio instalado y entregado al cliente.</li>
					</ul> 
				</div>
			</div>'."\n";
} elseif($apartado == $lang['Costo Item'] && $base == 'reportes.php') {
	echo '			<h1>' . $lang['Costo Item'] . '</h1>
			<div id="body">
				<div id="principal">
					En esta columna se indica el costo a pagar por el item o partida del pedido, pero en los casos en que la aseguradora o el cliente de convenio entrega la pieza, material o servicio, <b>no hay ningún costo que pagar</b>, por lo tanto se indica esta condición con la leyenda <b><i>"A Cargo Aseguradora"</i></b> aún cuando se hubiera capturado el costo en el pedido.  Tampoco se suma al monto total a pagar.
				</div>
			</div>'."\n";
} elseif($apartado == $lang['Estatus de Cobro'] && $base == 'reportes.php') {
	echo '			<h1>' . $lang['Estatus de Cobro'] . '</h1>
			<div id="body">
				<div id="principal">
					La tarea a la que pertenece este producto o servicio:
					<ul>
						<li><b>' . $lang['OT Sin Facturar'] . ':</b> No tiene registro de documento de Cobro al Cliente (Remisión o Factura). No necesariamente es un error, puede ser que aún no esté terminada la tarea o que aún no se haya entregado el vehículo al cliente; en todo caso se recomienda revisar el estatus general de la OT.</li>
						<li><b>' . $lang['OT Por Cobrar'] . ':</b> Ya tiene un documento de cobro pero aún no se ha cobrado.</li>
						<li><b>' . $lang['OT Cobrada'] . ':</b> Ya fue cobrada, ya se recuperó la inversión.</li>
						<li><b>' . $lang['OT Cancelada'] . ':</b> Tiene un documento de cobro que fue cancelado, por lo que podría ser vuelto a cobrar o puede terminar en devolución del producto o servicio. Se debe revisar la situación con el Valuador.</li>
					</ul> 
				</div>
			</div>'."\n";
}

echo '		</div>
	</body>
</html>';

?>
