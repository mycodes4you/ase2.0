<?php
$instancia = strtoupper(substr($dbpfx, 0, -1));
echo '<div style="background-color: white;"><p>Para poder abrir nuevas Ordenes de Trabajo (OTs), por favor deposite su pago en cualquiera de las siguientes cuentas a nombre de <strong>AGUSTIN DIAZ ZAMORA</strong>:</p>';
echo '	<table cellpadding="3" cellspacing="0" border="1">
		<tr><td>Banco</td><td>Cuenta</td><td>Clabe</td></tr>
		<tr><td>Banorte</td><td>0818208788</td><td>072180008182087888</td></tr>
		<tr><td>Banco Azteca</td><td>21240104690657</td><td>127180001046906575</td></tr>
	</table>
<p>Le recordamos que puede adquirir cualquier cantidad de OTs con un <span style="font-weight:bold; color:#d00;">mínimo de ';
if($ordpretari == 1) {
	$precio = 30;
	echo '50';
} elseif($ordpretari == 3) {
	$precio = 15;
	echo '100';
} else {
	$precio = 40;
	echo '50';
}
echo '</span> a un precio de $' . number_format($precio, 2) . ' + 16% de IVA cada una. Si adquiere de 500 a 999 OTs obtendrá un descuento de 10% y si adquiere 1000 o más OTs obtendrá un 15% de descuento.</p>'."\n";
echo '<p>Para agilizar la configuración de sus OTs por favor coloque lo siguiente como<br><strong>Referencia: ' . $instancia . '</strong></p>'."\n";
echo '	<table cellpadding="3" cellspacing="0" border="1" class="centrado">
		<tr><td>Para Adquirir</td><td>Por Favor Deposite</td></tr>'."\n";
if($ordpretari != 3) {
	echo '		<tr><td>50 OTs</td><td>$' . number_format(($precio * 50 * 1.16), 2) . '</td></tr>'."\n";
}
echo '		<tr><td>100 OTs</td><td>$' . number_format(($precio * 100 * 1.16), 2) . '</td></tr>
		<tr><td>200 OTs</td><td>$' . number_format(($precio * 200 * 1.16), 2) . '</td></tr>
		<tr><td>300 OTs</td><td>$' . number_format(($precio * 300 * 1.16), 2) . '</td></tr>
		<tr><td>500 OTs</td><td>$' . number_format((($precio * 0.90) * 500 * 1.16), 2) . '</td></tr>
		<tr><td>1000 OTs</td><td>$' . number_format((($precio * 0.85) * 1000 * 1.16), 2) . '</td></tr>
		<tr><td colspan="2">Ya Incluye IVA y Descuentos</td></tr>
	</table><hr></div>'."\n";
echo '<p>También puede realizar su pago con tarjeta de crédito o débito a través de PayPal, el reconocido y confiable sistema de recepción de pagos por Internet,<strong>sin embargo, si elige este método de pago tendremos que hacer un cargo adicional por la comisión de la transacción que nos cobra PayPal. Este monto ya está incluido en el botón de pago.</strong></p>'."\n";

if($ordpretari == 1) {
// ------------  Botón de pago Tarifa Producto 1 ------------------
	echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="2Y3ZR6RUFR9WS">
<table>
<tr><td><input type="hidden" name="on0" value="Cantidad">Cantidad</td></tr><tr><td><select name="os0">
	<option value="50 Ordenes AutoShop Easy">50 Ordenes AutoShop Easy $1,830.00 MXN</option>
	<option value="100 Ordenes AutoShop Easy">100 Ordenes AutoShop Easy $3,660.00 MXN</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="MXN">
<input type="image" src="https://www.paypalobjects.com/es_XC/MX/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal, la forma más segura y rápida de pagar en línea.">
<img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1">
</form>'."\n";
} elseif($ordpretari == 3) {
// ------------  Botón de pago Tarifa Producto 3 ------------------
	echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="RD79VHJKAEM98">
<table>
<tr><td><input type="hidden" name="on0" value="Cantidad">Cantidad</td></tr><tr><td><select name="os0">
	<option value="100 Ordenes AutoShop Easy">100 Ordenes AutoShop Easy $1,830.00 MXN</option>
	<option value="200 Ordenes AutoShop Easy">200 Ordenes AutoShop Easy $3,660.00 MXN</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="MXN">
<input type="image" src="https://www.paypalobjects.com/es_XC/MX/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal, la forma más segura y rápida de pagar en línea.">
<img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1">
</form>'."\n";
} else {
// ------------  Botón de pago Tarifa Producto 16 ------------------	
	echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="CKX6DNP9RFHBE">
<table>
<tr><td><input type="hidden" name="on0" value="Cantidad">Cantidad</td></tr><tr><td><select name="os0">
	<option value="50 Ordenes AutoShop Easy">50 Ordenes AutoShop Easy $2,440.00 MXN</option>
	<option value="100 Ordenes AutoShop Easy">100 Ordenes AutoShop Easy $4,880.00 MXN</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="MXN">
<input type="image" src="https://www.paypalobjects.com/es_XC/MX/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal, la forma más segura y rápida de pagar en línea.">
<img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1">
</form>'."\n";
}
?>
