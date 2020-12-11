<?php
include('../particular/config.php');

mysql_connect($servidor,$dbusuario,$dbclave)  or die('Falló la conexion a la DB');
mysql_select_db($dbnombre) or die('Falló la seleccion la DB');
include('../parciales/funciones.php');
include_once('../parciales/valores.php');

$body = file_get_contents("php://input");
$webhook = json_decode($body, true);

// ------ Obtenemos la información del mensaje ------

$clientemsj = limpiar_cadena($webhook["message"]["text"]);
$chat_id = $webhook["message"]["chat"]["id"];
$respuesta = $chat_id . '|' . $clientemsj;

// ------ Antes que nada, guardamos el mensaje original ------
file_put_contents('telegram.txt', $respuesta.';'.PHP_EOL , FILE_APPEND | LOCK_EX);

// ------ Buscamos un cliente con el número de Chat_Id de Telegram
$preg1 = "SELECT cliente_id, cliente_nombre, cliente_apellidos FROM " . $dbpfx . "clientes WHERE cliente_telegram_id = '" . $chat_id . "' LIMIT 1";
$matr1 = mysql_query($preg1) or die("Error: no se conectó con clientes! " . $preg1);
$fila1 = mysql_num_rows($matr1);

if($fila1 == '1') {
	// --- Si existe, localizamos la OT más reciente sin importar estatus y obtenemos el asesor de servicio ---
	$clie = mysql_fetch_array($matr1);
	$preg2 = "SELECT orden_id, orden_asesor_id FROM " . $dbpfx . "ordenes WHERE orden_cliente_id = '" . $clie['cliente_id'] . "' ORDER BY orden_id DESC LIMIT 1";
	$matr2 = mysql_query($preg2) or die("Error: no se conectó con ordenes! " . $preg2);
	$fila2 = mysql_num_rows($matr2);
	if($fila2 == '1') {
		// --- Si existe OT, enviamos el mensaje al asesor de servicio por los métodos que tenga disponibles ---
		$ord = mysql_fetch_array($matr2);
		if($AdminTelegram != '') {
			$destino = $AdminTelegram;
		} else {
			$destino = $ord['orden_asesor_id'];
		}
		bitacora($ord['orden_id'], 'Cliente vía Telegram: ' . $clientemsj, $dbpfx, 'El Cliente ' . $clie['cliente_nombre'] . ' ' . $clie['cliente_apellidos'] . ' te envió por Telegram: ' . $clientemsj, 1, '', '', $destino);
	} elseif($fila2 == '0') {
		$preg3 = "SELECT previa_id, previa_asesor_id FROM " . $dbpfx . "previas WHERE previa_cliente_id = '" . $clie['cliente_id'] . "' ORDER BY previa_id DESC LIMIT 1";
		$matr3 = mysql_query($preg3) or die("Error: no se conectó con previas! " . $preg3);
		$fila3 = mysql_num_rows($matr3);
		if($fila3 == '1') {
			// --- Si existe OT, enviamos el mensaje al asesor de servicio por los métodos que tenga disponibles ---
			$prev = mysql_fetch_array($matr3);
			if($AdminTelegram != '') {
				$destino = $AdminTelegram;
			} else {
				$destino = $prev['previa_asesor_id'];
			}
			bitacora('', 'Cliente vía Telegram: ' . $clientemsj, $dbpfx, 'El Cliente ' . $clie['cliente_nombre'] . ' ' . $clie['cliente_apellidos'] . ' por Telegram: ' . $clientemsj, 3, '', $prev['previa_id'], $destino);
		}
	} else {
		$mensaje = 'Mensaje Telegram cliente ' . $clie['cliente_id'] . ' sin OT o Previa';
		bitacora('1', $mensaje, $dbpfx, $mensaje, 1, '', '', '701');
	}

} else {
// ------ Si no existe cliente con ese Chat_Id, tal vez es un nuevo registro a notificaciones Telegram, buscamos un vehículo por placas ------
	$depurado = preg_replace('/[^A-Za-z0-9 ]/', '', $clientemsj);
	$placas = explode(' ', $depurado);
	$bloque = preg_replace('/[^A-Za-z0-9]/', '', $depurado);
	$cliente = 0;
	foreach($placas as $k) {
		$preg1 = "SELECT v.vehiculo_id, c.cliente_id, c.cliente_nombre, c.cliente_apellidos FROM " . $dbpfx . "vehiculos v, " . $dbpfx . "clientes c WHERE (v.vehiculo_placas = '" . $k . "' OR v.vehiculo_placas = '" . $bloque . "') AND v.vehiculo_placas != '' AND v.vehiculo_placas != ' ' AND v.vehiculo_cliente_id = c.cliente_id LIMIT 1";
		$matr1 = mysql_query($preg1) or die("Error: no se conectó con ordenes! " . $preg1);
		$fila1 = mysql_num_rows($matr1);
		if($fila1 == '1') {
			// --- Si existe OT, enviamos el mensaje al asesor de servicio por los métodos que tenga disponibles ---
			$clie = mysql_fetch_array($matr1);
			$preg2 = "SELECT orden_id, orden_asesor_id FROM " . $dbpfx . "ordenes WHERE orden_vehiculo_id = '" . $clie['vehiculo_id'] . "' ORDER BY orden_id DESC LIMIT 1";
			$matr2 = mysql_query($preg2) or die("Error: no se conectó con ordenes2! " . $preg2);
			$fila2 = mysql_num_rows($matr2);
			if($fila2 == '1') {
				// --- Si existe OT, enviamos el mensaje al asesor de servicio por los métodos que tenga disponibles ---
				$ord = mysql_fetch_array($matr2);
				if($AdminTelegram != '') {
					$destino = $AdminTelegram;
				} else {
					$destino = $ord['orden_asesor_id'];
				}
				bitacora($ord['orden_id'], 'Cliente vía Telegram: ' . $clientemsj, $dbpfx, 'El Cliente ' . $clie['cliente_nombre'] . ' ' . $clie['cliente_apellidos'] . ' te envió por Telegram: ' . $clientemsj, 1,'','',$destino);
			} elseif($fila2 == '0') {
				// --- No hubo OT, ahora buscamos en Previas ---
				$preg3 = "SELECT previa_id, previa_asesor_id FROM " . $dbpfx . "previas WHERE previa_vehiculo_id = '" . $clie['vehiculo_id'] . "' ORDER BY previa_id DESC LIMIT 1";
				$matr3 = mysql_query($preg3) or die("Error: no se conectó con previas! " . $preg3);
				$fila3 = mysql_num_rows($matr3);
				if($fila3 == '1') {
					// --- Si existe Previa, enviamos el mensaje al asesor de servicio por los métodos que tenga disponibles ---
					$prev = mysql_fetch_array($matr3);
					if($AdminTelegram != '') {
						$destino = $AdminTelegram;
					} else {
						$destino = $prev['previa_asesor_id'];
					}
					bitacora('', 'Cliente vía Telegram: ' . $clientemsj, $dbpfx, 'El Cliente ' . $clie['cliente_nombre'] . ' ' . $clie['cliente_apellidos'] . ' por Telegram: ' . $clientemsj, 1, '', $prev['previa_id'], $destino);
				}
			} else {
				$mensaje = 'Mensaje Telegram cliente ' . $clie['cliente_id'] . ' sin OT o Previa';
				bitacora('1', $mensaje, $dbpfx, $mensaje, 3, '', '', '701');
			}
			$param = "cliente_id = '" . $clie['cliente_id'] . "'";
			$sqlcli['cliente_telegram_id'] = $chat_id;
			ejecutar_db($dbpfx.'clientes', $sqlcli, 'actualizar', $param);
			$cliente = 1;
			$pregvals = "SELECT val_texto FROM  " . $dbpfx . "valores WHERE val_nombre = 'TelegramToken'";
			$matrvals = mysql_query($pregvals);
			$res = mysql_fetch_array($matrvals);
			$TelegramToken = $res['val_texto'];
			notificaTelegram($chat_id, 'Bienvenido a las notificaciones a través de Telegram, nuestro Asesor de Servicio estará atento a sus mensajes. Muchas gracias!');
			break;
		}
	}
	if($cliente == 0) {
		// --- Le pedimos al cliente que envíe otro mensaje con las placas de su vehículo ---
		$TelegramToken = $res['val_texto'];
		notificaTelegram($chat_id, 'Hola buen día, por favor envíenos un mensaje con las placas (sin espacios) de su vehículo para darlo de alta en estas notificaciones. Si no recibe un mensaje de bienvenida, le pedimos se comunique vía telefónica con su Asesor de Servicio para verificar si capturamos mal la placa de su vehículo. Muchas gracias.');
		$mensaje = 'Mensaje Telegram SIN cliente localizado. Chat ID: ' . $chat_id . ' Mensaje: ' . $clientemsj;
		bitacora('1', $mensaje, $dbpfx, $mensaje, 3, '', '', '701');
	}
}

?>

