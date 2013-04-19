<?php

	/**
	 * Tarkistaa, ovatko annetut kirjautumistiedot oikein.
	 * @param $kayttajanimi Käyttäjänimi, jolla tahdotaan kirjautua järjestelmään.
	 * @param $salasana Käyttäjätunnusta vastaava salasana.
	 */
	function kirjautumistiedotOvatOikein($kayttajanimi, $salasana) {	
		
		$tulos = lahetaSQLKysely("
			SELECT * FROM kayttaja
			WHERE kayttajanimi = ?",
			array($kayttajanimi));
		
		$rivi = $tulos->fetch();
		return $rivi["salasana"] == md5($salasana);
	}
	
	/**
	 * Päivittää kirjautumisyritysten määrän sivun ladanneelle IP-osoitteelle.
	 * @param $uusiMaara Se määrä, joka tahdotaan asettaa kirjautumisyrityksiksi.
	 */
	function paivitaKirjautumisyritystenMaara($uusiMaara) {
		
		lahetaSQLKysely("
			UPDATE kirjautumisyritykset
			SET maara = ?
			WHERE ip = ?",
			array($uusiMaara, $_SERVER["REMOTE_ADDR"]));
	}
	
	/**
	 * Kertoo kuinka monta kertaa sivun ladannut henkilö on yrittänyt virheellisesti
	 * kirjautua sisään.
	 * @return Virheellisten yritysten määrä kokonaislukuna.
	 */
	function kirjautumisyritystenMaara() {

		$tulos = lahetaSQLKysely("
			SELECT * FROM kirjautumisyritykset
			WHERE ip = ?",
			array($_SERVER["REMOTE_ADDR"]));
			
		if($tulos->rowCount() == 0) {
			lahetaSQLKysely("
				INSERT INTO kirjautumisyritykset (ip, maara)
				VALUES (?, 0)",
				array($_SERVER["REMOTE_ADDR"]));
			return 0;	
		}
		
		$rivi = $tulos->fetch();
		return $rivi["maara"];
	}
	
	/**
	 * Suorittaa kirjautumisen kokeilemisen.
	 * @param $kayttajanimi Käyttäjänimi, jolla tahdotaan kirjautua järjestelmään.
	 * @param $salasana Käyttäjätunnusta vastaava salasana.
	 */
	function kirjauduSisaan($kayttajanimi, $salasana) {
		
		if(isset($_SESSION["kayttajanimi"]))
			return "Olet jo kirjautunut.";
		
		$kirjautumisyritykset = kirjautumisyritystenMaara();

		if($kirjautumisyritykset >= 3 || !kirjautumisTiedotOvatOikein($kayttajanimi, $salasana)) {
			paivitaKirjautumisyritystenMaara($kirjautumisyritykset + 1);
			return "Käyttäjätunnus tai salasana on väärin.";
		}
	
		$_SESSION["kayttajanimi"] = $kayttajanimi;
		paivitaKirjautumisyritystenMaara(0);
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
			SELECT * FROM kayttaja
			WHERE kayttajanimi = ?",
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
			INSERT INTO kayttaja (kayttajanimi, salasana)
			VALUES (?, ?)",
			array($kayttajanimi, md5($salasana)));
	}
	

?>