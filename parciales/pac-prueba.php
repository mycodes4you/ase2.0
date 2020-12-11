<?php

$pac_prov = 'pac-smart.php';
$pac_usuario = 'facturacion@controldeservicio.com';
$pac_clave = 'Dup.t8xmKla6xesep';
$pac_url_33 = 'http://services.test.sw.com.mx/cfdi33/stamp/json/v1/b64';	// Versión 3.3
$pac_autentica = 'https://services.sw.com.mx/security/authenticate'; // Versión 3.3


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

echo 'Token: ' . $Token . '<br>';
echo '----------------------------------';

