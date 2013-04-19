<?php 
	
	/**
	 * Tarkistaa mikä annetuista vaihtoehdoista sai eniten ääniä.
	 * @param $vaihtoehdot Taulukko äänestyksen vaihtoehdoista.
	 * @return Suurin äänimäärä.
	 */
	function laskeEnitenAaniaSaanut($vaihtoehdot) {
		
		$eniten = 0;
		foreach($vaihtoehdot as &$tiedot)
			if($eniten < $tiedot['aanestyskerrat'])
				$eniten = $tiedot['aanestyskerrat'];
		return $eniten;
	}
	
	/**
	 * Kertoo käyttäjän äänestämän vaihtoehdon.
	 * @param $aanestys Se äänestys, johon hänen oletetaan äänestäneen.
	 * @return False, mikäli ei ole äänestänyt, muutoin äänestysvaihtoehdon id.
	 */
	function kayttajanAanestamaVaihtoehto($aanestys) {
		
		if(!isset($_SESSION['kayttajanimi']))
			return false;
			
		$kysely = lahetaSQLKysely("
			SELECT * FROM aani WHERE aanestaja = ? AND aanestys = ?",
			array($_SESSION['kayttajanimi'], $aanestys));
		
		if($kysely->rowCount() == 0)
			return false;

		$tiedot = $kysely->fetch();
		return $tiedot['vaihtoehto'];
	}
	
	/**
	 * Kertoo, onko äänestys vielä voimassa.
	 * @param $aanestys Äänestys, jonka voimassaolemisesta ollaan kiinnostuneita.
	 * @return True tai false.
	 */
	function aanestysOnVielaVoimassa($aanestys) {
		
		$kysely = lahetaSQLKysely("
			SELECT * FROM aanestys
			WHERE
				id = ? AND
				paattymispaiva > now()",
			array($aanestys));

		return $kysely->rowCount() > 0;
	}
	
	/**
	 * Tallentaa käyttäjän antaman äänen tietokantaan. Olettaa, että tarkistelemiset
	 * virheiden varalta on tätä kutsuttaessa jo suoritettu.
	 * @param $aanestys Se äänestys, jossa äänestetään.
	 * @param $vaihtoehto Se vaihtoehto, jota äänestetään.
	 */
	function aanesta($aanestys, $vaihtoehto) {
		
		lahetaSQLKysely("
			INSERT INTO aani (aanestys, vaihtoehto, aanestaja, paivamaara)
			VALUES (?, ?, ?, current_date);",
			array($aanestys, $vaihtoehto, $_SESSION['kayttajanimi']));

		lahetaSQLKysely("
			UPDATE aanestysvaihtoehto
				SET aanestyskerrat = aanestyskerrat + 1
				WHERE id = ? AND aanestys = ?",
			array($vaihtoehto, $aanestys));
	}
	
	/**
	 * Tarkistaa yksittäisen äänestysvaihtoehdon kelpoisuuden.
	 * @param $vaihtoehto Vaihtoehto taulukkomuodossa. Ensimmäinen indeksi on nimi, toinen on kuvaus.
	 * @param $numerointi Kuinka mones tämä vaihtoehto on äänestyksessä. Käytetään virheilmoituksien selkeyttämiseen.
	 * @return Tyhjä merkkijono, mikäli äänestysvaihtoehto on validi. Muutoin virheilmoitus.
	 */
	function tarkistaAanestysvaihtoehdonKelpoisuus($vaihtoehto, $numerointi) {

		if(strlen($vaihtoehto[0]) > 32)
			return "Äänestysvaihtoehdolla " . $numerointi . " on liian pitkä nimi!";

		if(strlen($vaihtoehto[1]) > 128)
			return "Äänestysvaihtoehdon " . $numerointi . " kuvaus on liian pitkä!";

		return "";
	}
	
	/**
	 * Tarkistaa, onko äänestys sellainen, että se voidaan luoda tietokantaan.
	 * @param $nimi Äänestyksen nimi.
	 * @param $kuvaus Äänestyksen kuvaus.
	 * @param $voimassa Kuinka monta päivää äänestys on voimassa.
	 * @param $vaihtoehdot Taulukko äänestykselle lisättävistä vaihtoehdoista.
	 * @return Tyhjä merkkijono, mikäli äänestyksen voisi luoda. Muutoin virheilmoitus.
	 */
	function tarkistaAanestyksenKelpoisuus($nimi, $kuvaus, $voimassa, $vaihtoehdot) {
		
		if($nimi == "") 		 return "Äänestykselle on annettava nimi!";
		if(strlen($nimi) > 128) return "Äänestyksen nimi on liian pitkä!";
			
		if($voimassa < 1)
			return "Voimassaolopäiviä on oltava vähintään yksi!";
			
		if(count($vaihtoehdot) < 2) return "Vaihtoehtoja pitää antaa vähintään kaksi.";
		if(count($vaihtoehdot) > 5) return "Vaihtoehtoja voi olla enintään viisi.";
		
		$numerointi = 1;
		foreach($vaihtoehdot as $vaihtoehto) {
			$kelpous = tarkistaAanestysvaihtoehdonKelpoisuus($vaihtoehto, $numerointi);
			if($kelpous != "")
				return $kelpous;
			$numerointi++;
		}
		
		return "";
	}
	
	/**
	 * Lisää annetut vaihtoehdot sisäänkirjautuneen käyttäjän uusimpaan äänestykseen.
	 * @param $vaihtoehdot Taulukko, joka sisältää lisättävät vaihtoehdot.
	 */
	function lisaaVaihtoehdotAanestykseen($vaihtoehdot) {
	
		$kysely = lahetaSQLKysely("
			SELECT MAX(aanestys.id) AS uusin FROM aanestys
			WHERE aanestys.tekija = ?
			GROUP BY aanestys.tekija",
			array($_SESSION['kayttajanimi']));

		$rivi = $kysely->fetch();
		$uusin = intval($rivi["uusin"]);
		
		$numerointi = 0;
		foreach($vaihtoehdot as $vaihtoehto)
			lahetaSQLKysely("
				INSERT INTO aanestysvaihtoehto (id, aanestys, nimi, kuvaus, aanestyskerrat)
				SELECT ?, ?, ?, ?, 0",
				array($numerointi++, $uusin,
					htmlspecialchars($vaihtoehto[0]),
					htmlspecialchars($vaihtoehto[1])));
	}
	
	/**
	 * Luo uuden äänestyksen tietokantaan.
	 * @param $nimi Äänestyksen nimi.
	 * @param $kuvaus Äänestyksen kuvaus.
	 * @param $voimassa Määrä päiviä, jonka äänestys on voimassa.
	 * @param $vaihtoehdot Taulukko, joka sisältää äänestysvaihtoehdot.
	 * @return Tyhjä merkkijono, jos luonti onnistuu. Muutoin virheilmoitus.
	 */
	function luoAanestys($nimi, $kuvaus, $voimassa, $vaihtoehdot) {
		
		$virhe = tarkistaAanestyksenKelpoisuus($nimi, $kuvaus, $voimassa, $vaihtoehdot);
		if($virhe != "")
			return $virhe;
			
		lahetaSQLKysely("
			INSERT INTO aanestys
				(nimi, kuvaus, tekija, luontiaika, paattymispaiva) VALUES
				(?, ?, ?, current_timestamp, current_timestamp + interval '" . $voimassa ." days')",
			array(htmlspecialchars($nimi), htmlspecialchars($kuvaus), $_SESSION['kayttajanimi']));

		lisaaVaihtoehdotAanestykseen($vaihtoehdot);
		return "";
	}
	
	/**
	 * Poistaa äänestyksen ja kaiken siihen liittyvän datan.
	 * @param $aanestysID poistettavan äänestyksen ID.
	 */
	function poistaAanestys($aanestysID) {
		
		lahetaSQLKysely("
			DELETE FROM aani WHERE aanestys = ?",
			array($aanestysID));
		
		lahetaSQLKysely("
			DELETE FROM aanestysvaihtoehto WHERE aanestys = ?",
			array($aanestysID));

		lahetaSQLKysely("
			DELETE FROM aanestys WHERE id = ?",
			array($aanestysID));
		
		if(file_exists("loppuraportit/" . $aanestysID . ".png"))
			unlink("loppuraportit/" . $aanestysID . ".png");
	}
?>