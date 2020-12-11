<p class="footer">Derechos Reservados (c) 2011, 2017 <img src="imagenes/hecho-en-mexico.png" alt="Hecho en México" longdesc="Hecho en México" /></p>
	</div>
	<div align="center" class="control">
		<img src="css/logo-vaicop.png" alt="Logo VAICOP, S. de R.L. de C.V." /><br clear="all"><br>
		<span style="color:#666;font-size:9px;"><?php echo gethostname(); ?></span>
	</div>
</div>
<script>
// Close the dropdown menu if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.ayuda')) {

    var dropdowns = document.getElementsByClassName("muestra-contenido");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('mostrar')) {
        openDropdown.classList.remove('mostrar');
      }
    }
  }
}
</script>
</body>
</html>
