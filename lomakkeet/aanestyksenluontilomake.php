<?php
	if($aanestyksenluontiVirhe != "")
		echo '<p id="virhepalkki">Virhe: '. $aanestyksenluontiVirhe .'</p>';
?>

<h2>Luo uusi äänestys</h2>

<?php
	if(!isset($_SESSION['kayttajanimi']))
		echo '<i>Kirjaudu sisään luodaksesi äänestyksiä!</i>';
	else {
?>
	<form method="post">
		<input type="hidden" name="uuden_aanestyksen_luominen" />
	
		<h3>Tiedot</h3>
		<label>Äänestyksen otsikko: </label><input name="nimi" style="width:100%;" /><br />
		<label>Voimassa (päivinä): </label><input name="voimassa" value="7" style="width:100%;" /><br />
		<label>Lyhyt kuvaus:</label><br />
		<textarea name="kuvaus" style="width:100%;height:150px;"></textarea>

		<h3>Äänestysvaihtoehdot</h3>		
		<fieldset id="vaihtoehdot">
			Vähintään kaksi, enintään viisi. <a href="javascript:lisaaKentta()">Lisää</a>
			<script type="text/javascript">
				// Lisätään pari vaihtoehtoa valmiiksi.
				for(i = 0; i < 2; i++)
					lisaaKentta();
			</script>
		</fieldset>
		
		<input type="submit" value="Luo äänestys!" />
	</form>
<?php
	}
?>