<?php

/**

* letras($n)

* devuelve el valor de $n en letras es español

* $n es un entero

*

* Copyleft 2011 Arnoldo Briceсo

*/

function letras($n){

$cent=array(

1=>'ciento', 'doscientos', 'trescientos', 'cuatrocientos',

'quinientos', 'seiscientos', 'setecientos', 'ochocientos',

'novecientos');

$dec=array( '', '', '', 'treinta', 'cuarenta', 'cincuenta',

'sesenta', 'setenta', 'ochenta', 'noventa' );

$uni=array( '', ' y un', ' y dos', ' y tres', ' y cuatro',

' y cinco', ' y seis', ' y siete', ' y ocho', ' y nueve');



for ($i=0; $i<100;$i++){

$d=(int)($i/10);

$u=$i%10;

$num[$i]=$dec[$d].$uni[$u];

}



$num[0] = '';

$num[1] = 'un';

$num[2] = 'dos';

$num[3] = 'tres';

$num[4] = 'cuatro';

$num[5] = 'cinco';

$num[6] = 'seis';

$num[7] = 'siete';

$num[8] = 'ocho';

$num[9] = 'nueve';

$num[10] = 'diez';

$num[11] = 'once';

$num[12] = 'doce';

$num[13] = 'trece';

$num[14] = 'catorce';

$num[15] = 'quince';

$num[16] = 'dieciseis';

$num[17] = 'diecisiete';

$num[18] = 'dieciocho';

$num[19] = 'diecinueve';

$num[20] = 'veinte';

$num[21] = 'veintiun';

$num[22] = 'veintidos';

$num[23] = 'veintitres';

$num[24] = 'veinticuatro';

$num[25] = 'veinticinco';

$num[26] = 'veintiseis';

$num[27] = 'veintisiete';

$num[28] = 'veintiocho';

$num[29] = 'veintinueve';

$num[30] = 'treinta';

$num[40] = 'cuarenta';

$num[50] = 'cincuenta';

$num[60] = 'sesenta';

$num[70] = 'setenta';

$num[80] = 'ochenta';

$num[90] = 'noventa';

$num[100] = 'cien';



if ($n<=100){

return $num[$n];

}else if($n<1000){

$c=(int)($n/100);

return("$cent[$c] ".letras($n%100));

}else if ($n<1000000){

$c=(int)($n/1000);

$p=letras($c);

return("$p mil " .letras($n%1000));

}else{

$c=(int)($n/1000000);

$p=letras($c);

$q=($p=='un')?'millón':'millones';

return("$p $q " .letras($n%1000000));

}

}



/**

* letras2($n)

* devuelve el valor de $n en letras es español

* $n es un número decimal con dos decimales separados

* por un punto (.)

*

* Ejemplo

* letras2(32.25)= treinta y dos con 25/100

* Copyleft 2011 Arnoldo Briceсo

*/

function letras2($monto){

$cant=explode('.',$monto);

$v1=($cant[0]==0)?'cero':letras($cant[0]);

if($cant[1]==0 || $cant[1]=='' || !isset($cant[1])) { $v2 = '00'; }
elseif(strlen($cant[1])==1) { $v2 = $cant[1].'0'; }
else {$v2=substr($cant[1], 0, 2);}

return $v1.' PESOS '.$v2.'/100 M.N.';

}