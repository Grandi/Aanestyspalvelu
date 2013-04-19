
<div id="sivupalkki">
	<?php
		if(isset($_SESSION["kayttajanimi"])) {
			echo '<h3>Hei, <i>' . $_SESSION["kayttajanimi"] . '</i>!</h3>';
			
			echo '<a href="?omat_aanestykset">&raquo; Tekemäsi äänestykset</a><br />';
			echo '<a href="?luonti">&raquo; Luo uusi</a><br /><br />';
			echo '<a href="?kirjaudu_ulos" style="color:red;">&raquo; Kirjaudu ulos</a>';
		} else
			include "lomakkeet/kirjautumislomake.php";
	?>
</div>