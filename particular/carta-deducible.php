<?php

$mes = date('n');
if($mes == 1) {$mes = 'ENERO';}
elseif($mes == 2) {$mes = 'FEBRERO';}
elseif($mes == 3) {$mes = 'MARZO';}
elseif($mes == 4) {$mes = 'ABRIL';}
elseif($mes == 5) {$mes = 'MAYO';}
elseif($mes == 6) {$mes = 'JUNIO';}
elseif($mes == 7) {$mes = 'JULIO';}
elseif($mes == 8) {$mes = 'AGOSTO';}
elseif($mes == 9) {$mes = 'SEPTIEMBRE';}
elseif($mes == 10) {$mes = 'OCTUBRE';}
elseif($mes == 11) {$mes = 'NOVIEMBRE';}
elseif($mes == 12) {$mes = 'DICIEMBRE';}

echo '<br><br><br><br><br><br><br><br>';
echo '<table cellpadding="0" cellspacing="0" border="0" class="centrado mediana" width="850">
<tr><td align="center"><span style="font-size:24px;">' . date('j') . ' de ' . $mes . ' del ' . date('Y') . '</span></td></tr>';
echo '</table>';
echo '<br><br><br>';
echo '<table cellpadding="0" cellspacing="0" border="0" class="izquierda" width="850"  style="font-size:16px;">
<tr><td style="font-size:24px; font-weight:bold;">' . $aseg['aseguradora_razon_social'] . '</td></tr>
<tr><td>' . $aseg['aseguradora_calle'] . ' #' . $aseg['aseguradora_ext'] . '. Col. ' . $aseg['aseguradora_colonia'] . '.</td></tr>
<tr><td>C.P. ' . $aseg['aseguradora_cp'] . '. ' . $aseg['aseguradora_municipio'] . ', ' . $aseg['aseguradora_estado'] . '.</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td style="font-size:24px;">Asunto: <strong>Pago de Deducible</strong>.</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>Por este medio, se realiza el Pago del Deducible que <strong>' . $agencia_razon_social . '</strong> cobró por orden y cuenta de ' . $aseg['aseguradora_razon_social'] . ' de la reparación del vehículo que aquí se describe:</td></tr>
<tr><td>&nbsp;</td></tr>';
include('parciales/numeros-a-letras.php');
$letra = strtoupper(letras2($fact['fact_monto']));
echo '<tr><td>Marca de vehículo: '. $veh['marca'] .'<br>'.'Modelo: '. $veh['tipo'] .'<br>'.'Con Placas: '. $veh['placas'] .'<br>'.'No. de Siniestro: ' . $rep[0] . '<br>'.'No. de Poliza: ' . $rep[2] . '<br>'.'Por la Cantidad de: $' .  number_format($fact['fact_monto'], 2) . '<br>' . $letra . '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>
<tr><td>Sin más por el momento envío un cordial saludo.</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>';
echo '</table>';

$preg2 = "SELECT nombre, apellidos, puesto FROM " . $dbpfx . "usuarios WHERE usuario = '1000'";
$matr2 = mysql_query($preg2) or die("ERROR: Fallo selección de gerente!");
$usu =  mysql_fetch_array($matr2);


echo '<table cellpadding="0" cellspacing="0" border="0" class="centrado mediana" width="850">
 <tr><td align="center">____________________ <br> ATENTAMENTE <br> ' . $usu['nombre'] . ' ' . $usu['apellidos'] . '<br>' . $usu['puesto'] . '<br></td></tr>';
echo '</table>';
echo '<p>Recibo Folio: ' . $fact['fact_num'] . '<br>Orden: '. $orden_id .'</p>'."\n";
?>