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
	 * Käsittelee käyttäjän pyynnön poistaa oman äänestyksensä.
	 * Huom: Näitä virheilmoituksia ei näytetä käyttäjälle, koska ovat oikeastaan täysin turhia.
	 * @return Tyhjä merkkijono, jos äänestyksen poistaminen onnistui. Muutoin virheilmoitus.
	 */
	function kasitteleAanestyksenPoistopyynto() {
		
		if(isset($_GET['poista_aanestys'])) {

			if(!$_SESSION['kayttajanimi'])
				return "Et voi poistaa äänestystä ellet ole kirjautuneena sisään!";
				
			$tulos = lahetaSQLKysely(
				"SELECT * FROM aanestys
				WHERE id = ?",
				array($_GET['poistettava']));
			
			if($tulos->rowCount() == 0)
				return "Moista äänestystä ei ole olemassa!";
			
			$rivi = $tulos->fetch();
			if($_SESSION['kayttajanimi'] != $rivi["tekija"])
				return "Vain äänestyksen tekijä voi poistaa äänestyksen.";

			poistaAanestys($_GET['poistettava']);
			header("location: index.php?omat_aanestykset");
		}
	}
	
	/**
	 * Käsittelee pyynnön luoda uuden äänestyksen.
	 * @return Tyhjä merkkijono, jos luominen onnistuu. Muutoin virheilmoitus.
	 */
	function kasitteleUudenAanestyksenLuomispyynto() {
		
		if(isset($_POST['uuden_aanestyksen_luominen'])) {
		
			$vaihtoehdot = array();
			for($i = 1; $i <= 5 && isset($_POST['vaihtoehto_' . $i]); $i++) {
				if($_POST['vaihtoehto_' . $i] == "")
					continue;
				array_push($vaihtoehdot, array($_POST['vaihtoehto_' . $i], $_POST['kuvaus_' . $i]));
			}
			
			$virhe = luoAanestys($_POST['nimi'], $_POST['kuvaus'], $_POST['voimassa'], $vaihtoehdot);
			if($virhe != "")
				return $virhe;
				
			header("location: index.php?omat_aanestykset");
		}
	}
	
	/**
	 * Käsittelee käyttäjän lähettämät pyynnöt.
	 */
	function kasittelePyynnot() {
		global $kirjautumisVirhe;
		global $rekisteroitymisVirhe;
		global $aanestamisVirhe;
		global $aanestyksenluontiVirhe;

		$kirjautumisVirhe =       kasitteleSisaankirjautumispyynto();
		                          kasitteleUloskirjautumispyynto();
		$rekisteroitymisVirhe =   kasitteleRekisteroitymispyynto();
		$aanestamisVirhe =        kasitteleAanestamispyynto();
		$aanestyksenluontiVirhe = kasitteleUudenAanestyksenLuomispyynto();
								   kasitteleAanestyksenPoistopyynto();
	}
?>