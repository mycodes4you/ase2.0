<?php
foreach($_POST as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';    
foreach($_GET as $k => $v){$$k=$v;} // echo $k.' -> '.$v.'<br>';
include('parciales/funciones.php');
include('idiomas/' . $idioma . '/presupuestos.php');


if (!isset($_SESSION['usuario'])) {
	redirigir('usuarios.php');
}


// Ajustar la aseguradora o el cliente de los cobros en cobros y cobros_facturas

	$preg = "SELECT cf.cobro_id, cf.fact_id, fc.aseguradora_id, fc.cliente_id FROM " . $dbpfx . "cobros_facturas cf, " . $dbpfx . "facturas_por_cobrar fc WHERE cf.fact_id = fc.fact_id AND cf.cobro_id >= '" . $cobro . "' AND (cf.cliente_id IS NULL AND cf.aseguradora_id IS NULL) LIMIT 300";
	$matr = mysql_query($preg) or die("ERROR: Fallo seleccion de cobros enlazados!");
	$total = mysql_num_rows($matr);

	echo '<strong>Total cobros: ' . $total . '</strong>';

	$procesados = 0;
	while ($cob = mysql_fetch_array($matr)) {	
		
		if($cob['aseguradora_id'] == 0){
			echo '<br> cliente ' . $cob['cliente_id'];
			
			$act_cobro = "UPDATE " . $dbpfx . "cobros SET cliente_id = '" . $cob['cliente_id'] . "' WHERE cobro_id = '" . $cob['cobro_id'] . "'";
			$matr_act_cobro = mysql_query($act_cobro) or die("ERROR: Fallo actualización de tabla cobros! " . $act_cobro);
			
			$act_cobro_fact = "UPDATE " . $dbpfx . "cobros_facturas SET cliente_id = '" . $cob['cliente_id'] . "' WHERE cobro_id = '" . $cob['cobro_id'] . "'";
			$matr_act_cobro_fact = mysql_query($act_cobro_fact) or die("ERROR: Fallo actualización de tabla cobros_facturas! " . $act_cobro_fact);
			
		}
		elseif($cob['cliente_id'] == 0){
			echo '<br> adeguradora ' . $cob['aseguradora_id'];
			
			$act_cobro = "UPDATE " . $dbpfx . "cobros SET aseguradora_id = '" . $cob['aseguradora_id'] . "' WHERE cobro_id = '" . $cob['cobro_id'] . "'";
			$matr_act_cobro = mysql_query($act_cobro) or die("ERROR: Fallo actualización de tabla cobros! " . $act_cobro);
			
			$act_cobro_fact = "UPDATE " . $dbpfx . "cobros_facturas SET aseguradora_id = '" . $cob['aseguradora_id'] . "' WHERE cobro_id = '" . $cob['cobro_id'] . "'";
			$matr_act_cobro_fact = mysql_query($act_cobro_fact) or die("ERROR: Fallo actualización de tabla cobros_facturas! " . $act_cobro_fact);
			
		}
		$fact_id = $cob['fact_id'];
		$procesados = $procesados + 1;
	}

	echo '<br><br><strong>Total cobros ajustados: ' . $procesados . ' Ultima factura: ' . $fact_id . '</strong>';
