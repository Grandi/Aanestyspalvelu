<?php

	/**
	 * Tarkistaa, ovatko annetut kirjautumistiedot oikein.
	 * @param $kayttajanimi Käyttäjänimi, jolla tahdotaan kirjautua järjestelmään.
	 * @param $salasana Käyttäjätunnusta vastaava salasana.
	 */
	function kirjautumistiedotOvatOikein($kayttajanimi, $salasana) {	
		
		$tulos = lahetaSQLKysely("
			SELECT * FROM kayttaja WHERE kayttajanimi = ?",
			array($kayttajanimi));
		
		$rivi = $tulos->fetch();
		return $rivi["salasana"] == md5($salasana);
	}
	
	/**
	 * Suorittaa kirjautumisen kokeilemisen.
	 * @param $kayttajanimi Käyttäjänimi, jolla tahdotaan kirjautua järjestelmään.
	 * @param $salasana Käyttäjätunnusta vastaava salasana.
	 */
	function kirjauduSisaan($kayttajanimi, $salasana) {
		
		if(isset($_SESSION["kayttajanimi"]))
			return "Olet jo kirjautunut.";
		
		if($_SESSION["kirjautumisyritykset"] >= 100)
			return "Olet yrittänyt kirjautumista virheellisesti liian monta kertaa.";
		
		if(!kirjautumisTiedotOvatOikein($kayttajanimi, $salasana)) {
			$_SESSION["kirjautumisyritykset"]++;
			return "Käyttäjätunnus tai salasana on väärin.";
		}
			
		$_SESSION["kayttajanimi"] = $kayttajanimi;
		$_SESSION["kirjautumisyritykset"] = 0;
		return "";
	}
	
	/**
	 * Suorittaa uloskirjautumisen.
	 */
	function kirjauduUlos() {
		unset($_SESSION["kayttajanimi"]);
	}
	
	/**
	 * Tarkistaa, onko käyttäjänimi halutunlainen
	 * (4-16 merkkiä sisältäen vain a-z, A-Z, 0-9, _.)
	 * @param $kayttajanimi Käyttäjänimi, jota tahdotaan kokeilla.
	 */
	function kayttajanimiOnhyva($kayttajanimi) {
		return preg_match("/[a-zA-Z0-9_]{4,16}/", $kayttajanimi);
	}
	
	/** Kertoo, voiko annetuilla tiedoilla rekisteröidä uuden tunnuksen.
	 * @param $kayttajanimi Käyttäjänimi, joka uudelle tunnukselle tahdotaan.
	 * @param $salasana Salasana, joka tunnukselle tahdotaan.
	 */
	function voiRekisteroida($kayttajanimi, $salasana) {
		
		if(!kayttajanimiOnHyva($kayttajanimi))
			return "Käyttäjänimi ei vastaa vaatimuksia.";
			
		if(strlen($salasana) < 8 || strlen($salasana) > 20)
			return "Salasana ei vastaa vaatimuksia.";

		$tulos = lahetaSQLKysely("
			SELECT * FROM kayttaja WHERE kayttajanimi = ?",
			array($kayttajanimi));
		
		if($tulos->rowCount() > 0)
			return "Käyttäjänimi on jo käytössä.";
		return "";
	}
	
	/** Rekisteröi tunnuksen annetuilla tiedoilla.
	 * @param $kayttajanimi Käyttäjänimi, joka uudelle tunnukselle tahdotaan.
	 * @param $salasana Salasana, joka tunnukselle tahdotaan.
	 */
	function rekisteroi($kayttajanimi, $salasana) {
		
		$tulos = lahetaSQLKysely("
			INSERT INTO kayttaja (kayttajanimi, salasana) VALUES (?, ?)",
			array($kayttajanimi, md5($salasana)));
	}
	

?>