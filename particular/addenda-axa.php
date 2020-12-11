<?php

// remplazo de variables en addenda por datos del cliente.

if($metop == '') { $metop = 'TRANSFERENCIA BANCARIA'; } 
if($cuenp == '') { $cuenp = '1730'; } 
if($condp == '') { $condp = 'CONTADO'; } 

if($procesoid == '') { $procesoid = 'VILLAHERMOSA'; }
if($fechaprefactura == '') { $fechaprefactura = date('d-m-Y H:i:s', time());}
 
if($Tipo == '') { $Tipo = 'BASICO'; }

?>