<?php
	if($rekisteroitymisVirhe != "")
		echo '<p id="virhepalkki">Virhe: '. $rekisteroitymisVirhe .'</p>';
?>

<h2>Rekisteröidy palveluun</h2>

<form method="post" style="margin-top: 30px;">
	<input name="rekisteroityminen" type="hidden" />
	
	<p>
		<label><b>Käyttäjänimi.</b> 4-16 merkkiä. Saa sisältää isoja ja pieniä kirjaimia A-Z, alaviivoja _, sekä numeroita 0-9.</label> <br />
		<input name="kayttajanimi" />
	</p>
	
	<p>
		<label><b>Salasana kahdesti.</b> 8-20 merkkiä.</label> <br />
		<input name="salasana" type="password" /><br />
		<input name="salasana_uudestaan" type="password" />
	</p>
	
	<input type="submit" value="Rekisteröidy" />
</form>