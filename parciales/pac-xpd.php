<?php

require_once('nusoap/nusoap.php');
$servicio=$pac_url_33; //url del servicio

//parametros de la llamada
$parametros=array();

//Se preparan los parametros con los valores adecuados
$parametros['usuario']=$pac_usuario; //String
$parametros['contrasena']=$pac_clave;//String
$parametros['cfdi']=$cfdi;//String

//echo "revisando parametros<br>";
//print_r($parametros);


//Se crea el cliente del servicio
$client = new SoapClient( $servicio, $parametros);

//Se hace el metodo que vamos a probar
$result = $client->timbrar($parametros);

// print_r($parametros);

//Para observar el Dump de lo que regresa, es puramente de debug
// echo "Valor dump del servicio:";
// echo "<BR>";
//var_dump($result);
// echo "<BR>";
// echo "<BR>";

//Aislar cada valor de lo que regresa, y poder manipularlo como sea
foreach($result as $key => $value) {
	
//    echo "Código:";
//    echo "<BR>";
//    $res_codigo = $value->codigo; 
//    $res_uuid = $value->uuid;
//    var_dump($value->codigo);
//	echo $res_codigo;
//    echo "<BR>";
//    echo "<BR>";
//    echo "Mensaje:";
//    echo "<BR>";
//    var_dump($value->mensaje);
//    echo "<BR>";
//    echo "<BR>";
//    echo "Timbre:";
//    echo "<BR>";
//	file_put_contents(DIR_DOCS . 'regresa.xml', $value->timbre);
//	file_put_contents(DIR_DOCS . 'mensaje-xpd.txt', $value->mensaje);
//    var_dump($value->timbre);
//	echo "UUID";
//    echo "<BR>";
//    var_dump($value->uuid);

$timbre = $value->timbre;

}

// echo 'Timbre: <br>' . $timbre;

?>
