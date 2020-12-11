<?php 
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.' | ';    
include('parciales/funciones.php');

if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}

$funnum = 1140000;

if (($accion==='insertar') || ($accion==='actualizar')) { 
	/* no cargar encabezado */
} else {
	include('idiomas/' . $idioma . '/personas.php');
	include('parciales/encabezado.php');
	echo '	<div id="body">';
	include('parciales/menu_inicio.php');
	echo '		<div id="principal">';
}

	$pregunta = "SELECT usuario, nombre, apellidos FROM " . $dbpfx . "usuarios WHERE acceso = '0'";
	$matriz = mysql_query($pregunta) or die("ERROR: Fallo seleccion de usuarios!");
	$num_cols = mysql_num_rows($matriz);
	$j=0;
	echo '		<table cellspacing="15" cellpadding="5" border="1">';
	while($usu = mysql_fetch_array($matriz)) {
		if($j==0) { echo '<tr>';}
		echo '			<td valign="top" style="text-align:center;">' . $usu['nombre'] . ' ' . $usu['apellidos'] . '<br><img src="parciales/barcode.php?barcode=' . $usu['usuario'] . '&width=240&height=80"><br>Usuario: ' . $usu['usuario'] . '</td>' . "\n";
		$j++;
		if($j==3) { echo '</tr>'; $j=0;}
	}
	echo '		</table>';

?>			
		</div>
	</div>
<?php include('parciales/pie.php'); ?>