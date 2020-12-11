		var posicionCampo=1;
		var posicionObra=1;
		var posicionSin=1;

		function agregarTrabajo(){
			nuevaFila = document.getElementById("tablaSin").insertRow(-1);
			nuevaFila.id=posicionSin;
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><select name='areas["+posicionSin+"]' size='1' ><option value='mecanica'>Mecánica</option><option value='electrica'>Eléctrico</option><option value='hojalateria'>Hojalatería</option><option value='pintura'>Pintura</option><option value='accesorios'>Accesorios</option></select></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><textarea name='descripcions["+posicionSin+"]' rows='3' cols='42'></textarea></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='button' value='Eliminar' onclick='eliminarTarea(this)'></td>";
			posicionSin++;
		}
		
		function agregarTarea(){
			nuevaFila = document.getElementById("tablaTareas").insertRow(-1);
			nuevaFila.id=posicionCampo;
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><select name='area["+posicionCampo+"]' size='1' ><option value='mecanica'>Mecánica</option><option value='electrica'>Eléctrico</option><option value='hojalateria'>Hojalatería</option><option value='pintura'>Pintura</option><option value='accesorios'>Accesorios</option></select></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><textarea name='descripcion["+posicionCampo+"]' rows='3' cols='42'></textarea></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='button' value='Eliminar' onclick='eliminarTarea(this)'></td>";
			posicionCampo++;
		}
		
		function agregarProducto(){
			nuevaFila = document.getElementById("tablaTareas").insertRow(-1);
			nuevaFila.id=posicionCampo;
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='nombre["+posicionCampo+"]' size='30' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='referencia["+posicionCampo+"]' size='15' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='cantidad["+posicionCampo+"]' size='4' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='precio["+posicionCampo+"]' size='12' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='button' value='Eliminar' onclick='eliminarTarea(this)'></td>";
			posicionCampo++;
		}

		function agregarPintProd(){
			nuevaFila = document.getElementById("tablaMats").insertRow(-1);
			nuevaFila.id=posicionCampo;
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='mpnombre["+posicionCampo+"]' size='30' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='mpreferencia["+posicionCampo+"]' size='15' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='mpcantidad["+posicionCampo+"]' size='4' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='mpprecio["+posicionCampo+"]' size='12' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='mpdd["+posicionCampo+"]' size='4' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='button' value='Eliminar' onclick='eliminarTarea(this)'></td>";
			posicionCampo++;
		}

		function agregarObra(){
			nuevaFila = document.getElementById("tablaObra").insertRow(-1);
			nuevaFila.id=posicionObra;
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='obradesc["+posicionObra+"]' size='30' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='obracantidad["+posicionObra+"]' size='4' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='text' name='obraprecio["+posicionObra+"]' size='12' ></td>";
			nuevaCelda=nuevaFila.insertCell(-1);
			nuevaCelda.innerHTML="<td><input type='button' value='Eliminar' onclick='eliminarTarea(this)'></td>";
			posicionObra++;
		}

		function eliminarTarea(obj){
			var oTr = obj;
			while(oTr.nodeName.toLowerCase()!='tr'){
				oTr=oTr.parentNode;
			}
			var root = oTr.parentNode;
			root.removeChild(oTr);
		}
		
		function pregunta(){
    		if (confirm('¿Desea CREAR un nuevo Ingreso?')){
       		recibe.submit()
    		}
		} 

// var admdocs1  = '<?php echo $adm_docs; ?>';
// var admdocs  = '1';

function validarExp() {
 	
   if( document.rapida.placas.value == "" )
   {
     alert( "Por favor indique las placas" );
     document.rapida.placas.focus() ;
     return false;
   }
   if( document.rapida.serie.value == "" )
   {
     alert( "Por favor indique el VIN" );
     document.rapida.serie.focus() ;
     return false;
   }
   if( document.rapida.marca.value == "" )
   {
     alert( "Por favor indique la Marca" );
     document.rapida.marca.focus() ;
     return false;
   }
   if( document.rapida.tipo.value == "" )
   {
     alert( "Por favor indique el Modelo" );
     document.rapida.tipo.focus() ;
     return false;
   }
   if( document.rapida.modelo.value == "" )
   {
     alert( "Por favor indique el Año" );
     document.rapida.modelo.focus() ;
     return false;
   }
   if( document.rapida.colores.value == "" && document.rapida.docingreso.value == "1" )
   {
     alert( "Por favor indique el Color" );
     document.rapida.colores.focus() ;
     return false;
   }


   if( document.rapida.servicio[3].checked && document.rapida.orden_adm.value == "" && document.rapida.admdocs.value == "1")
   {
     alert( "Por favor agregue imagen de la Orden de admisión" );
     document.rapida.orden_adm.focus() ;
     return false;
   }

   if( document.rapida.levante.value == "" && document.rapida.admdocid.value == "1")
   {
     alert( "Por favor agregue imagen de la Hoja de Daños o Identificación Oficial (INE)" );
     document.rapida.levante.focus() ;
     return false;
   }




/*   if( document.rapida.inventario.value == "")
   {
     alert( "Por favor agregue imagen del Inventario" );
     document.rapida.inventario.focus() ;
     return false;
   }
*/   
/*
   if( document.rapida.cilindros.value == "" )
   {
     alert( "Por favor indique número de cilindros" );
     document.rapida.cilindros.focus() ;
     return false;
   }
   if( document.rapida.litros.value == "" )
   {
     alert( "Por favor indique la cilindrada" );
     document.rapida.litros.focus() ;
     return false;
   }
   if( document.rapida.tipomotor.value == "" )
   {
     alert( "Por favor indique el tipo de motor" );
     document.rapida.tipomotor.focus() ;
     return false;
   }
*/
   if( document.rapida.nombre.value == "" )
   {
     alert( "Por favor indique el Nombre del Cliente" );
     document.rapida.nombre.focus() ;
     return false;
   }
   if( document.rapida.apellidos.value == "" )
   {
     alert( "Por favor indique los Apellidos del Cliente" );
     document.rapida.apellidos.focus() ;
     return false;
   }
/*
   if( !document.rapida.clietipo[0].checked && !document.rapida.clietipo[1].checked )
  	{
     alert( "Por favor indique el tipo de conductor" );
  	  return false;
   }

   if( document.rapida.boletin.checked && document.rapida.email.value == "" )
   {
     alert( "Por favor indique email del Cliente" );
     document.rapida.email.focus() ;
     return false;
   }
*/

   if( document.rapida.telefono1.value == "" )
   {
     alert( "Por favor indique el Teléfono Principal" );
     document.rapida.telefono1.focus() ;
     return false;
   }

// ----------------------  Inventario -----------------------   
/*
   if( !document.rapida.antena[0].checked && !document.rapida.antena[1].checked )
   {
     alert( "Por favor indique la antena" );
     return false;
   }

   if( !document.rapida.tapones[0].checked && !document.rapida.tapones[1].checked )
   {
     alert( "Por favor indique los tapones" );
     return false;
   }

   if( !document.rapida.encendedor[0].checked && !document.rapida.encendedor[1].checked )
   {
     alert( "Por favor indique el encendedor" );
     return false;
   }

   if( !document.rapida.espejo[0].checked && !document.rapida.espejo[1].checked )
   {
     alert( "Por favor indique los espejos" );
     return false;
   }

   if( !document.rapida.tgas[0].checked && !document.rapida.tgas[1].checked )
   {
     alert( "Por favor indique el Tapón de Gasolina" );
     return false;
   }

   if( !document.rapida.cables[0].checked && !document.rapida.cables[1].checked )
   {
     alert( "Por favor indique cables pasa corriente" );
     return false;
   }

   if( !document.rapida.rines[0].checked && !document.rapida.rines[1].checked )
   {
     alert( "Por favor indique rines" );
     return false;
   }

   if( !document.rapida.tapetes[0].checked && !document.rapida.tapetes[1].checked )
   {
     alert( "Por favor indique tapetes" );
     return false;
   }

   if( !document.rapida.llanta[0].checked && !document.rapida.llanta[1].checked )
   {
     alert( "Por favor indique llanta de refacción" );
     return false;
   }

   if( !document.rapida.herramientas[0].checked && !document.rapida.herramientas[1].checked )
   {
     alert( "Por favor indique herramientas" );
     return false;
   }

   if( !document.rapida.reflejantes[0].checked && !document.rapida.reflejantes[1].checked )
   {
     alert( "Por favor indique reflejantes" );
     return false;
   }

   if( !document.rapida.extinguidor[0].checked && !document.rapida.extinguidor[1].checked )
   {
     alert( "Por favor indique extinguidor" );
     return false;
   }

   if( !document.rapida.estereo[0].checked && !document.rapida.estereo[1].checked )
   {
     alert( "Por favor indique Radio" );
     return false;
   }

   if( !document.rapida.gato[0].checked && !document.rapida.gato[1].checked )
   {
     alert( "Por favor indique gato" );
     return false;
   }

   if( !document.rapida.vestiduras[0].checked && !document.rapida.vestiduras[1].checked )
   {
     alert( "Por favor indique vestiduras" );
     return false;
   }

   if( !document.rapida.cristales[0].checked && !document.rapida.cristales[1].checked )
   {
     alert( "Por favor indique cristales" );
     return false;
   }

   if( !document.rapida.objvalor[0].checked && !document.rapida.objvalor[1].checked )
   {
     alert( "Por favor indique objvalor" );
     return false;
   }
*/
// --------------------  Fin de Inventario ---------------   

   if( document.rapida.asesor.value == "Seleccione" )
   {
     alert( "Por favor seleccione un Asesor" );
     document.rapida.asesor.focus() ;
     return false;
   }
/*   if( rapida.areat[0].checked==false && rapida.areat[1].checked==false && rapida.areat[2].checked==false )
   {
     alert( "Por favor selecione el Area de servicio" );
     return false;
   }
*/   if( !document.rapida.servicio[0].checked && !document.rapida.servicio[1].checked && !document.rapida.servicio[2].checked && !document.rapida.servicio[3].checked )
   {
     alert( "Por favor indique el Tipo de Servicio" );
     return false;
   }
   if( document.rapida.servicio[1].checked && document.rapida.garantia.value == "")
   {
     alert( "Por favor agregue el número de OT reclamada" );
     document.rapida.garantia.focus() ;
     return false;
   }
   if( document.rapida.categoria.value == "Seleccione" )
   {
     alert( "Por favor indique Seleccione la Categoría" );
     document.rapida.categoria.focus() ;
     return false;
   }
   if( !document.rapida.grua[0].checked && !document.rapida.grua[1].checked && document.rapida.gruareg.value == "1" )
   {
     alert( "Por favor indique si llegó en Grua!" );
     return false;
   }


/*   if( document.rapida.odometro.value == "" )
   {
     alert( "Por favor indique el Kilometraje " );
     document.rapida.odometro.focus() ;
     return false;
   }
*/   if( document.rapida.areas.value == "Seleccione" )
   {
     alert( "Por favor seleccione un Área de Servicio " );
     document.rapida.areas.focus() ;
     return false;
   }
   if( document.rapida.descripcion.value == "")
   {
     alert( "Por favor indique la Tarea a Ejecutar" );
     document.rapida.descripcion.focus() ;
     return false;
   }
/*   if( document.rapida.audasust.value == "" )
   {
     alert( "Por favor indique las Refacciones a utilizar" );
     document.rapida.audasust.focus() ;
     return false;
   }
   if( document.rapida.audamo.value == "" )
   {
     alert( "Por favor indique la Mano de Obra" );
     document.rapida.audamo.focus() ;
     return false;
   }
*/
/*
   if( document.rapida.servicio[3].checked && document.rapida.tablero.value == "")
   {
     alert( "Por favor agregue foto del Tablero" );
     document.rapida.tablero.focus() ;
     return false;
   }
   if( document.rapida.servicio[3].checked && document.rapida.vin.value == "")
   {
     alert( "Por favor agregue foto del Numero de Serie" );
     document.rapida.vin.focus() ;
     return false;
   }
*/
/*   if( document.rapida.servicio[3].checked && document.rapida.orden_adm.value == "")
   {
     alert( "Por favor agregue Orden de Admisión" );
     return false;
   }
   if( document.rapida.servicio[3].checked && document.rapida.reporte.value == "")
   {
     alert( "Por favor indique el número de Reporte" );
     document.rapida.reporte.focus() ;
     return false;
   }
   if( document.rapida.servicio[3].checked && document.rapida.fsin.value == "")
   {
     alert( "Por favor indique la fecha del siniestro" );
     document.rapida.fsin.focus() ;
     return false;
   }
   if( document.rapida.servicio[3].checked && document.rapida.aseguradora.value == "")
   {
     alert( "Por favor seleccione la Aseguradora" );
     document.rapida.aseguradora.focus() ;
     return false;
   }

   if( document.rapida.Zip.value == "" ||
           isNaN( document.rapida.Zip.value ) ||
           document.rapida.Zip.value.length != 5 )
   {
     alert( "Please provide a zip in the format #####." );
     document.rapida.Zip.focus() ;
     return false;
   }
   if( document.rapida.Country.value == "-1" )
   {
     alert( "Please provide your country!" );
     return false;
   }
*/   
//		return( true );
		rapida.submit();
}

function validarPlacas() {
 
   if( document.rapida.placas.value != "" )
   {
   	rapida.submit();
   } else {
     alert( "Por favor indique las Placas" );
     document.rapida.placas.focus() ;
     return false;
   }
    
}

function componerPaquetes(xArea) {
	document.rapida.areas.disabled = true;
	document.rapida.paquetes.length = 0;
	cargarPaquetes(xArea);
	document.rapida.areas.disabled = false;
} 
