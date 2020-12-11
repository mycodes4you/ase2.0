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
     alert( "Por favor indique el Tipo o Modelo" );
     document.rapida.tipo.focus() ;
     return false;
   }
   if( document.rapida.modelo.value == "" )
   {
     alert( "Por favor indique el Año" );
     document.rapida.modelo.focus() ;
     return false;
   }
   if( document.rapida.colores.value == "" && document.rapida.docingreso.value == "1")
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

   if( document.rapida.asesor.value == "Seleccione" )
   {
     alert( "Por favor seleccione un Asesor" );
     document.rapida.asesor.focus() ;
     return false;
   }
   if( document.rapida.servicio.value == "" )
   {
     alert( "Por favor indique el Tipo de Servicio" );
     document.rapida.servicio.focus() ;
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
