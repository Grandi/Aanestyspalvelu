<?php
	if($kirjautumisVirhe != "")
		echo '<p style="color:red;font-weight:bolder;">Virhe: '. $kirjautumisVirhe .'</p>';
?>

<form method="post">
	<input name="kirjautuminen" type="hidden" />
	<label>Käyttäjänimi:</label> <input name="kayttajanimi" /><br />
	<label>Salasana:</label> <input name="salasana" type="password" /><br />
	<input type="submit" value="Kirjaudu" />
	<br /><br />
	Ei tunnusta? <a href="?rekisteroidy">Rekisteröidy!</a>
</form>