<?php

	/**
	 * Käsittelee pyynnön kirjautua sisään.
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
	 * Käsittelee käyttäjän lähettämät pyynnöt.
	 */
	function kasittelePyynnot() {
		global $kirjautumisVirhe;
		global $rekisteroitymisVirhe;

		$kirjautumisVirhe = kasitteleSisaankirjautumispyynto();
		kasitteleUloskirjautumispyynto();
		$rekisteroitymisVirhe = kasitteleRekisteroitymispyynto();
	}
?>