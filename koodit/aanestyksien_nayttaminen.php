<?php

	/**
	 * Lyhentää kuvausta listauksessa, jos se on liian pitkä.
	 * @param $tiedot Äänestyksen merkkijonotyyppinen kuvaus.
	 */
	function lyhennettyKuvaus($kuvaus) {
		if(strlen($kuvaus) < 255)
			return $kuvaus;
			
		return substr($kuvaus, 0, 255) . "... <i>(Kuvausta on lyhennetty.)</i>";
	}

	/**
	 * Listaa yksittäisen äänestyksen sivulle.
	 * @param $tiedot Äänestyksen tiedot taulukkomuodossa.
	 */
	function listaaAanestys($tiedot) {

		if(strtotime($tiedot["paattymispaiva"]) > time())
			echo '<div id="aanestys_listassa">';
		else
			echo '<div id="vanhentunut_aanestys_listassa">';

		echo '
			<h3><a href="?aanestys='.$tiedot["id"].'" style="font-weight:bolder;">'.$tiedot["nimi"].'</a></h3>
			<p style="width:100%;">'.lyhennettyKuvaus($tiedot["kuvaus"]).'</p>
		</div>';
	}

	/**
	 * Listaa tietokannassa olevat äänestykset.
	 */
	function listaaAanestykset() {
	
		$kysely = lahetaSQLKysely("
			SELECT * FROM aanestys
			ORDER BY luontiaika DESC");

		if($kysely->rowCount() == 0)
			echo '<i>Tietokannasta ei löytynyt ainuttakaan äänestystä.</i>';
		else
			echo '<p>Punaisella taustalla olevista äänestyksistä on äänestysaika umpeutunut.</p>';
		
		while($rivi = $kysely->fetch())
			listaaAanestys($rivi);
	}
	
	/**
	 * Tulostaa sivulle äänestyksen perustiedot.
	 * @param $tiedot Taulukko äänestyksen tiedoista.
	 */
	function naytaAanestyksenTiedot($tiedot) {
		echo '
			<br />
			<p style="text-align:left;">
				<b>Tekijä: </b> '.$tiedot["tekija"].'<br />
				<b>Luomishetki: </b> '.$tiedot["luontiaika"].'<br />
				<b>Päättymishetki: </b> '.$tiedot["paattymispaiva"].'
			</p>';
	}
	
	/**
	 * Hakee äänestyksen tiedot.
	 * @param $aanestysID Sen äänestyksen ID-tunnus, jonka tiedot tahdotaan hakea.
	 * @return Äänestyksen tiedot taulukkomuodossa.
	 */
	function haeAanestyksenTiedot($aanestysID) {
	
		$kysely = lahetaSQLKysely("
			SELECT nimi, kuvaus, tekija,
				to_char(luontiaika, 'DD.MM.YYYY kello HH24:MI') as luontiaika,
				to_char(paattymispaiva, 'DD.MM.YYYY kello HH24:MI') as paattymispaiva
			FROM aanestys WHERE id = ?",
			array($aanestysID));
		
		if($kysely->rowCount() == 0)
			die("<h2>404</h2><p>Hakemaasi äänestystä ei löydy tietokannasta.
					<a href=\"index.php\">Palaa etusivulle</a>.</p>");
		
		return $kysely->fetch();
	}
	
	/**
	 * Näyttää äänestyssivun sisällön.
	 * @param $aanestysID Se äänestys, jota tahdotaan tarkastella.
	 */
	function naytaAanestyssivu($aanestysID) {
	
		global $aanestamisVirhe;
		if($aanestamisVirhe != "")
			echo '<p id="virhepalkki">Virhe: '. $aanestamisVirhe .'</p>';
		
		$tiedot = haeAanestyksenTiedot($aanestysID);
		if($tiedot["tekija"] == $_SESSION["kayttajanimi"])
			echo '<h2>'.$tiedot["nimi"].' (<a href="?poista_aanestys&poistettava='.$aanestysID.'">Poista</a>)</h2>';
		else
			echo '<h2>'.$tiedot["nimi"].'</h2>';
		
		if($tiedot["kuvaus"] != "")
			echo '<p>'.$tiedot["kuvaus"].'</p>';
		
		naytaAanestyksenTulokset($aanestysID, kayttajanAanestamaVaihtoehto($aanestysID));
		naytaAanestyksenTiedot($tiedot);
	}
	
	/**
	 * Näyttää äänestysvaihtoehdon nimen eli otsikon. Mikäli äänestysvaihtoehtoon liittyy kuvaus,
	 * otsikko esitetään kuvauksen klikatessa näyttävänä linkkinä.
	 * @param $tiedot Äänestysvaihtoehdon tiedot taulukkomuodossa.
	 */
	function naytaVaihtoehdonNimi($tiedot) {
		
		echo '<td style="padding-right:20px;">';
		if($tiedot['kuvaus'] != "") {
			echo '<a href="javascript:naytaKuvauksena(\'<b>'.$tiedot['nimi'].':</b> &nbsp;
				<i>'.$tiedot['kuvaus'].'</i>\')">'. str_replace(' ','&nbsp;',$tiedot['nimi']) . '</a>';
		} else
			echo str_replace(' ','&nbsp;',$tiedot['nimi']);
		echo '</td>';
	}

	/**
	 * Näyttää yksittäisen äänestysvaihtoehdon.
	 * @param $tiedot Äänestysvaihtoehdon tiedot.
	 * @param $onAanestetty Kertoo, onko katselija jo äänestänyt äänestyksessä.
	 * @param $vertailukohta Eniten ääniä saaneen vaihtoehdon äänet,
		jotta palkista saadaan sopivan pituinen.
	 */
	function naytaAanestysvaihtoehto($tiedot, $onAanestettavissa, $vertailukohta) {

		global $varit;
		echo '<tr>';
		
		if($onAanestettavissa)
			echo '<td style="width:10px;"><input name="vaihtoehdot" value="'.$tiedot['id'].'" type="radio" /></td>';
		
		naytaVaihtoehdonNimi($tiedot);
		
		echo '<td style="width:30px; text-align:center;">'. $tiedot["aanestyskerrat"] .'</td>';

		$vari = $varit[$tiedot["id"]];
		if($tiedot["aanestyskerrat"] == 0)
			echo '<td style="width:100%;"><div id="aanestyspalkki" style="background:rgb(150,150,150); width:10px;"></div></td>';
		else {
			$palkinPituus = $tiedot["aanestyskerrat"] / $vertailukohta * 100;
			echo '<td style="width:100%;"><div id="aanestyspalkki"
				style="background:rgb('.$vari[0].','.$vari[1].','.$vari[2].'); width:'.$palkinPituus.'%;"></div></td>';
		}
		
		echo '</tr>';
	}
	
	/**
	 * Listaa sivulle sisäänkirjautuneen käyttäjän omat äänestykset.
	 */
	function listaaKayttajanOmatAanestykset() {
	
		if(!isset($_SESSION["kayttajanimi"]))
			die("Kirjaudu sisään katsellaksesi omia äänestyksiäsi.");
		
		$kysely = lahetaSQLKysely("
			SELECT * FROM aanestys
			WHERE tekija = ?
			ORDER BY luontiaika DESC",
			array($_SESSION["kayttajanimi"]));

		if($kysely->rowCount() == 0)
			echo '<i>Tietokannasta ei löydy sellaisia äänestyksiä, jotka sinä olisit tehnyt.</i>';
		
		while($rivi = $kysely->fetch())
			listaaAanestys($rivi);
	}

	/**
	* Näyttää äänestysvaihtoehdot ja niiden saamat äänet.
	* @param $äänestys Äänestys, jota olemme kiinnostuneita tarkastelemaan.
	* @param $aanestettyVaihtoehto Se vaihtoehto, jota tarkastelija on jo äänestänyt. False, jos ei ole äänestänyt.
	*/
	function naytaAanestyksenTulokset($aanestys, $aanestettyVaihtoehto = false) {
		
		$kysely = lahetaSQLKysely("
			SELECT * FROM aanestysvaihtoehto
			WHERE aanestys = ?
			ORDER BY id",
			array($aanestys));
		
		$vaihtoehdot = $kysely->fetchAll();
		$enitenAaniaSaanut = laskeEnitenAaniaSaanut($vaihtoehdot);
		
		echo '
		<form id="aanestyslaatikko" method="post">
			<input name="aanestaminen" type="hidden" value="'.$aanestys.'" />
			<div id="kuvausteksti" style="margin-bottom:10px;display:none;"></div>
			<table>';
		
		$vielaVoimassa = aanestysOnVielaVoimassa($aanestys);
		foreach($vaihtoehdot as &$vaihtoehto) {
			$voiAanestaa = isset($_SESSION["kayttajanimi"]) && $aanestettyVaihtoehto === false && $vielaVoimassa;
			naytaAanestysvaihtoehto($vaihtoehto, $voiAanestaa, $enitenAaniaSaanut);
		}

		echo '</table>';
		
		if(!$vielaVoimassa)
			echo '<i><br />Äänestysaika on umpeutunut. <a href="javascript:naytaTaiPiilota(\'loppuraportti\')">Katso äänimäärien kasvua kuvaava käyrä</a>.</i>';
		else if($aanestettyVaihtoehto === false) {
			if(isset($_SESSION['kayttajanimi']))
				echo '<input type="submit" value="Äänestä!" style="margin-left:auto;width:75px;display:block;" />';
			else
				echo '<i><br />Kirjaudu sisään äänestääksesi.</i>';
		} else {
			$q = $vaihtoehdot[$aanestettyVaihtoehto];
			echo '<i><br />Äänestit vaihtoehtoa \''. $q["nimi"] . '\'.</i>';
		}
		echo '</form>';
		
		if(!$vielaVoimassa)
			echo '<div id="loppuraportti" style="display:none"><center><img src="' . haeLoppuraportti($aanestys) . '" /></center></div>';
	}
?>