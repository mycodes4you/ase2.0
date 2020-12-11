<?php

// remplazo de variables en addenda por datos del cliente.

if($metop == '') { $metop = 'TRANSFERENCIA BANCARIA'; } 
if($cuenp == '') { $cuenp = '1730'; } 
if($condp == '') { $condp = 'CONTADO'; } 

if($CdgIntEmisor == '') { $CdgIntEmisor = '01391'; } 
if($EmisorNombre == '') { $EmisorNombre = 'Irene del Carmen Zacarias Herrera'; }
if($EmisorEmail == '') { $EmisorEmail = 'zahi01@hotmail.com'; }
if($EmisorTelefono == '') { $EmisorTelefono = '9933511065'; }

if($ReceptorTipo == '') { $ReceptorTipo = 'coordinador'; }
if($ReceptorNombre == '') { $ReceptorNombre = 'Liliana Segura'; }
if($ReceptorEmail == '') { $ReceptorEmail = 'lsegura@qualitas.com.mx'; }
if($ReceptorTelefono == '') { $ReceptorTelefono = '50025500'; }

if($INC == '') { $INC = '0001'; }
if($TpoCliente == '') { $TpoCliente = '0'; }

if($oficinaEntrega == '') { $oficinaEntrega = '093';}
if($bancoDepositoDeducible == '') { $bancoDepositoDeducible = 'X';}
if($fechaDepositoDeducible == '') { $fechaDepositoDeducible = '0000-00-00';}
if($montoDemerito == '') { $montoDemerito = '0.00';}
if($bancoDepositoDemerito == '') { $bancoDepositoDemerito = 'X';}
if($fechaDepositoDemerito == '') { $fechaDepositoDemerito = '0000-00-00';}

?>