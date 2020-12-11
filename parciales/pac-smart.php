<?php

// ------ Se obtiene un token de acceso para este usuario por cada requerimiento de timbrado 
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $pac_autentica,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => array(
    "Cache-Control: no-cache",
//    "Postman-Token: 3ddee84c-c8bf-08e9-ce0c-acab94cf66eb",
    "Content-Length: 0",
    "password: $pac_clave",
    "user: $pac_usuario"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
	$Token = json_decode($response)->data->token;
}

// ------ Una vez obtenido el token de acceso, se envía el CFDi codificado en base 64

$XML = base64_encode($cfdi);

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $pac_url_33,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => '{"data": "'.$XML.'"}',
  CURLOPT_HTTPHEADER => array(
    "Authorization: bearer $Token",
    "Cache-Control: no-cache",
    "Content-Type: application/json"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
	if(json_decode($response)->data->tfd) {
// ------ Si se recibe el timbre, se decodifica de B64 y luego de JSON 
		$timbre = base64_decode(json_decode($response)->data->tfd);
	} else {
		$mensaje = json_decode($response)->message;
		echo 'Mensaje: ' . $mensaje . '<br>';
	}
}

?>
