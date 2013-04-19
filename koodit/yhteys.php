<?php

	/**
	 * Alustaa PostgreSQL-tietokantayhteyden.
	 */
	function alustaTietokantayhteys() {
		global $yhteys;

		try {
			$yhteys = new PDO("pgsql:host=localhost;dbname=rara", "rara",
				file_get_contents("/home/rara/.psql_password"));
		} catch(PDOException $poikkeus) {
			die("<b>Tietokantaan ei saatu yhteyttä:</b> " . $poikkeus->getMessage());
		}
		
		$yhteys->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$yhteys->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	/**
	 * Lähettää SQL-kyselyn tietokantaan.
	 * @param $SQLKoodi SQL-koodi, joka tahdotaan lähettää palvelimelle.
	 * @param $parametrit Parametrit, jotka korvaavat SQL-koodissa olevat ?-merkit.
	 * @return Tietokannan lähettämä tulos.
	 */
	function lahetaSQLKysely($SQLKoodi, $parametrit = array()) {
		global $yhteys;
		
		if(!$yhteys)
			die("<b>lahetaSQLKysely():</b> Tietokantayhteyttä ei oltu muodostettu!");
		
		$kysely = $yhteys->prepare($SQLKoodi);
		
		if(count($parametrit) > 0)
			$kysely->execute($parametrit);
		else
			$kysely->execute();
		
		return $kysely;
	}
?>