<?php
include('parciales/funciones.php');
foreach($_POST as $k => $v) {$$k = limpiar_cadena($v);}
foreach($_GET as $k => $v) {$$k = limpiar_cadena($v);}

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

include('idiomas/' . $idioma . '/' . $base);

echo '<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<title>Ayuda AutoShop Easy</title>
		<link href="css/estilos.css" type="text/css" rel="stylesheet" />
	</head>
	<body>
		<div id="container">'."\n";

echo $ayuda[$apartado];

echo '		</div>
	</body>
</html>';

?>
