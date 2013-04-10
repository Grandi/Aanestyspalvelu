<?php

	/**
	 * Käsittelee pyynnön kirjautua sisään.
	 * @return Kirjautumisessa tapahtunut virhe. Tyhjä merkkijono, kun kaikki onnistuu.
	 */
	function kasitteleSisaankirjautumispyynto() {
		if(isset($_POST['kirjautuminen']))
			return kirjauduSisaan($_POST['kayttajanimi'], $_POST['salasana']);
	}
	
	/**
	 * Käsittelee pyynnön kirjautua ulos.
	 */
	function kasitteleUloskirjautumispyynto() {
		if(isset($_GET['kirjaudu_ulos'])) {
			kirjauduUlos();
			header("location: index.php");
		}
	}
	
	/**
	 * Käsittelee pyynnön rekisteröityä.
	 * @return Rekisteröitymisessä tapahtunut virhe. Tyhjä merkkijono, kun kaikki onnistuu.
	 */
	function kasitteleRekisteroitymispyynto() {
		
		if(isset($_POST['rekisteroityminen'])) {
			
			$virheilmoitus = voiRekisteroida($_POST['kayttajanimi'], $_POST['salasana']);
			if($virheilmoitus != "")
				return $virheilmoitus;
				
			if($_POST['salasana'] != $_POST['salasana_uudestaan'])
				return "Salasanat eivät täsmää!";
			
			rekisteroi($_POST['kayttajanimi'], $_POST['salasana']);
			$_SESSION['kayttajanimi'] = $_POST['kayttajanimi'];
			header("location: index.php");
			return "";
		}
	}
	
	/**
	 * Käsittelee pyynnön äänestää äänestyksessä.
	 * @return Äänestämisessä tapahtunut virhe. Tyhjä merkkijono, kun kaikki onnistuu.
	 */
	function kasitteleAanestamispyynto() {
		
		if(isset($_POST['aanestaminen'])) {
		
			if($_POST['vaihtoehdot'] == "")
				return "Et valinnut vaihtoehtoa!";
		
			if(!isset($_SESSION['kayttajanimi']))
				return "Sinun on oltava kirjautuneena äänestääksesi!";
		
			$aanestys = $_POST['aanestaminen'];			
			if(!aanestysOnVielaVoimassa($aanestys))
				return "Äänestysaika ehti jo päättyä.";
			
			if(kayttajanAanestamaVaihtoehto($aanestys) !== false)
				return "Olet jo äänestänyt tässä äänestyksessä!";
			
			aanesta($aanestys, $_POST['vaihtoehdot']);
			header("location: index.php?aanestys=".$aanestys);
			return "";
		}
	}
	
	/**
	 * Käsittelee käyttäjän lähettämät pyynnöt.
	 */
	function kasittelePyynnot() {
		global $kirjautumisVirhe;
		global $rekisteroitymisVirhe;
		global $aanestamisVirhe;

		$kirjautumisVirhe =     kasitteleSisaankirjautumispyynto();
		                        kasitteleUloskirjautumispyynto();
		$rekisteroitymisVirhe = kasitteleRekisteroitymispyynto();
		$aanestamisVirhe =      kasitteleAanestamispyynto();
	}
?>