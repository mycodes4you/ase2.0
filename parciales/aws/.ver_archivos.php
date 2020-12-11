<?php
// --- Se definen las llaves de acceso y la region
define('AWS_KEY', 'AKIARZTK4UL53S45JEE2');
define('AWS_SECRET_KEY', '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');
define('REGION', 'us-west-1');
// --- cargamos el sdk de amazon
require_once 'aws-autoloader.php';

// --- Creamos el array con los datos de cliente
$client = new Aws\S3\S3Client(array(
    'credentials' => array(
        'key' => AWS_KEY,
        'secret' => AWS_SECRET_KEY,
    ),
    'region' => REGION,
    'version' => 'latest',
));

// -- Seleccionamos las clases que se ocuparan
//use Aws\Exception\AwsException;
					require_once 'Aws/Exception/AwsException.php';
					use Aws\S3\ObjectUploader;
					require_once 'Aws/S3/ObjectUploader.php';

// --- SUBIR ARCHIVO

// --- Definimos el bucket donde se subira el archivo
$bucket = 'asepruebas';
// --- Este es el nombre que recibira el archivo
$key = 'indices.png';
// --- La carpte donde se subuira el archivo
$path = 'pruebas';

// --- Indicamos la ruta del archivo que se subira
$source = fopen('índice.png', 'rb');

if ($path != ''){
	$key = $path . '/' . $key;
}

$uploader = new ObjectUploader(
    $client,
    $bucket,
    $key,
    $source
);

do {
    try {
        $result = $uploader->upload();
        if ($result["@metadata"]["statusCode"] == '200') {
            echo '<p>Se ha subido correctamente el archivo a: <br><a href="' . $result["ObjectURL"] . '" target="_blank">' . $result["ObjectURL"] . '</a></p>';
        }
        //print($result);
    } catch (MultipartUploadException $e) {
        rewind($source);
        $uploader = new MultipartUploader($client, $source, [
            'state' => $e->getState(),
        ]);
    }
} while (!isset($result));

// --- OBTENER URL PREFIRMADA
//Creating a presigned URL

$cmd = $client->getCommand('GetObject', [
    'Bucket' => 'asepruebas',
    'Key' => $key
]);

$request = $client->createPresignedRequest($cmd, '+5 minutes');

// Get the actual presigned-url
$presignedUrl = (string)$request->getUri();

echo '<a href="'.$presignedUrl.'" target="_blank">'.$presignedUrl.'</a><br>';
?>

Enlace valido por: 
<div id="countdown"></div>
<script>
var end = new Date(Date.now() + (5 * 60 * 1000));

    var _second = 1000;
    var _minute = _second * 60;
    var _hour = _minute * 60;
    var _day = _hour * 24;
    var timer;

    function showRemaining() {
        var now = new Date();
        var distance = end - now;
        if (distance < 0) {

            clearInterval(timer);
            document.getElementById('countdown').innerHTML = 'EXPIRED!';

            return;
        }
        var days = Math.floor(distance / _day);
        var hours = Math.floor((distance % _day) / _hour);
        var minutes = Math.floor((distance % _hour) / _minute);
        var seconds = Math.floor((distance % _minute) / _second);

        document.getElementById('countdown').innerHTML = days + ' dias, ';
        document.getElementById('countdown').innerHTML += hours + ' horas, ';
        document.getElementById('countdown').innerHTML += minutes + ' minutos y ';
        document.getElementById('countdown').innerHTML += seconds + ' segundos';
    }

    timer = setInterval(showRemaining, 1000);
</script>

<?php

?>