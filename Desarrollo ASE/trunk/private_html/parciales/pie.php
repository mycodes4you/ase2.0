	<div class="page-content footer control">
		<div class="row">
			<div class="col-sm-4 col-md-4 col-lg-4 izq">
				<img src="css/logo-vaicop.png" alt="Vaicop, S.A.S. de C.V." width="60%" /><br>
				<big><span style="color:#666;"><?php echo gethostname(); ?></span></big>
			</div>
			<div class="col-sm-7 col-md-7 col-lg-7 der">
				<p align="right">
				Derechos Reservados (c) 2011, 2019  <img src="imagenes/hecho-en-mexico.png" alt="Hecho en México" longdesc="Hecho en México" />
				</p>
			</div>
		</div>
	</div>
</div>
<script>
// Close the dropdown menu if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches(".ayuda")) {

    var dropdowns = document.getElementsByClassName("muestra-contenido");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains("mostrar")) {
        openDropdown.classList.remove("mostrar");
      }
    }

    var dropdownsizq = document.getElementsByClassName("muestra-contenido-izq");
    var i;
    for (i = 0; i < dropdownsizq.length; i++) {
      var openDropdown = dropdownsizq[i];
      if (openDropdown.classList.contains("mostrar")) {
        openDropdown.classList.remove("mostrar");
      }
    }
  }
}
</script>

<script src='js/jquery-1.12.4.js' type='text/javascript'></script>
<div id='IrArriba'>
<a href='#Arriba'><span/></a>
</div>
<script type='text/javascript'>
//<![CDATA[
// Botón para Ir Arriba
jQuery.noConflict();
jQuery(document).ready(function() {
jQuery("#IrArriba").hide();
jQuery(function () {
jQuery(window).scroll(function () {
if (jQuery(this).scrollTop() > 200) {
jQuery('#IrArriba').fadeIn();
} else {
jQuery('#IrArriba').fadeOut();
}
});
jQuery('#IrArriba a').click(function () {
jQuery('body,html').animate({
scrollTop: 0
}, 800);
return false;
});
});

});
//]]>
</script>

</body>
</html>
