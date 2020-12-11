
<?php


$str = '||DIZA650617DX4|1||';

$mihash = sha1($str, $raw_output = true);

//echo $mihash;

$fp=fopen("/home/agustin/Webs/tronco/trunk/particular/aaa.pem","r");
$priv_key=fread($fp,8192);
fclose($fp);

// echo $priv_key;
// $passphrase is required if your key is encoded (suggested)
$res = openssl_get_privatekey($priv_key,'Dup.t8xm');

//echo $res;
/*
 * NOTE:  Here you use the returned resource value
 */
openssl_private_encrypt($mihash,$crypttext,$res);
echo "String crypted: $crypttext";



?>
