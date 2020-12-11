<?php

// remplazo de variables en addenda por datos del cliente.

if($metop1 == '') { $metop1 = '03'; } 
if($cuenp == '') { $cuenp = '1730'; } 
if($condp == '') { $condp = 'CONTADO'; } 

if($procesoid == '') { $procesoid = 'VILLAHERMOSA'; }
if($fechaprefactura == '') { $fechaprefactura = date('d-m-Y H:i:s', time());}
 
if($Tipo == '') { $Tipo = 'BASICO'; }

?>