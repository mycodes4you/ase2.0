<?php
$instancia = strtoupper(substr($dbpfx, 0, -1));
echo '<p>Para poder abrir nuevas Ordenes de Trabajo, por favor deposite su pago en cualquiera de las siguientes cuentas a nombre de <strong>AGUSTIN DIAZ ZAMORA</strong>:</p>';
echo '	<table cellpadding="3" cellspacing="0" border="1">
		<tr><td>Banco</td><td>Cuenta</td><td>Clabe</td></tr>
		<tr><td>Banorte</td><td>0818208788</td><td>072180008182087888</td></tr>
		<tr><td>Banco Azteca</td><td>21240104690657</td><td>127180001046906575</td></tr>
	</table>
<p>1 Crédito = 1 Orden de Trabajo. Le recordamos que puede adquirir cualquier cantidad de créditos con un <span style="font-weight:bold; color:#d00;">';
if($ordpretari == 1) {
	echo 'mínimo de 50</span> a un precio de $30.00 cada uno';
} elseif($ordpretari == 3) {
	echo 'mínimo de 100</span> a un precio de $15.00 cada uno';
} else {
	echo 'mínimo de 50</span> a un precio de $40.00 cada uno';
}
echo ' + 16% de IVA. Si adquiere de 500 a 999 créditos obtendrá un descuento de 10% y si adquiere 1000 o más créditos obtendrá un 15% de descuento. En ambos casos el descuento es sobre el precio de venta. Precios más 16% de IVA.</p>
<p>Para agilizar la configuración de sus OTs por favor coloque lo siguiente como<br><strong>Referencia: ' . $instancia . '</strong></p>';
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
