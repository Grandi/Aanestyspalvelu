<?php 

	/**
	 * Lyhentää kuvausta listauksessa, jos se on liian pitkä.
	 * @param $tiedot Äänestyksen merkkijonotyyppinen kuvaus.
	 */
	function lyhennettyKuvaus($kuvaus) {
		if(strlen($kuvaus) < 255)
			return $kuvaus;
			
		return substr($kuvaus, 0, 255) . " <i>(...)</i>";
	}

	/**
	 * Listaa yksittäisen äänestyksen sivulle.
	 * @param $tiedot Äänestyksen tiedot taulukkomuodossa.
	 */
	function listaaAanestys($tiedot) {
		echo '
		<div id="aanestys_listassa">
			<h3><a href="?aanestys='.$tiedot["id"].'" style="font-weight:bolder;">'.$tiedot["nimi"].'</a></h3>
			<p style="width:100%;">'.lyhennettyKuvaus($tiedot["kuvaus"]).'</p>
			<!--<td>Tekijä:&nbsp;<b>'.$tiedot["tekija"].'</b></td>
			<td>'.$tiedot["luontiaika"].'</td>
			<td>'.$tiedot["paattymispaiva"].'</td>-->
		</div>';
	}

	/**
	 * Listaa tietokannassa olevat äänestykset.
	 */
	function listaaAanestykset() {
	
		$kysely = lahetaSQLKysely("SELECT * FROM aanestys ORDER BY luontiaika DESC");
		while($rivi = $kysely->fetch())
			listaaAanestys($rivi);
	}
	
	/**
	 * Näyttää äänestyssivun sisällön.
	 * @param $aanestysID Se äänestys, jota tahdotaan tarkastella.
	 */
	function naytaAanestyssivu($aanestysID) {
	
		global $aanestamisVirhe;
		if($aanestamisVirhe != "")
			echo '<p id="virhepalkki">Virhe: '. $aanestamisVirhe .'</p>';
		
		$kysely = lahetaSQLKysely("
			SELECT nimi, kuvaus, tekija,
				to_char(luontiaika, 'DD.MM.YYYY kello HH24:MI') as luontiaika,
				to_char(paattymispaiva, 'DD.MM.YYYY kello HH24:MI') as paattymispaiva
			FROM aanestys WHERE id = ?",
			array($aanestysID));
		
		if($kysely->rowCount() == 0)
			die("<h2>404</h2><p>Hakemaasi äänestystä ei löydy tietokannasta. <a href=\"index.php\">Palaa etusivulle</a>.</p>");
		$tiedot = $kysely->fetch();
		
		echo '
			<h2>Äänestys: \''.$tiedot["nimi"].'\'</h2>
			<p><b>Kuvaus:</b> '.$tiedot["kuvaus"].'</p>';
		
		naytaAanestyksenTulokset($aanestysID, kayttajanAanestamaVaihtoehto($aanestysID));
		
		echo '<br />
			<p style="text-align:left;">
				<b>Tekijä: </b> '.$tiedot["tekija"].'<br />
				<b>Luotu: </b> '.$tiedot["luontiaika"].'<br />
				<b>Päättyy: </b> '.$tiedot["paattymispaiva"].'
			</p>';
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
			array($_SESSION['kayttajanimi'], $aanestys)
		);
		
		if($kysely->rowCount() == 0)
			return false;

		$tiedot = $kysely->fetch();
		return $tiedot['vaihtoehto'];
	}
	
	/**
	 * Näyttää yksittäisen äänestysvaihtoehdon.
	 * @param $tiedot Äänestysvaihtoehdon tiedot.
	 * @param $onAanestetty Kertoo, onko katselija jo äänestänyt äänestyksessä.
	 * @param $vertailukohta Eniten ääniä saaneen vaihtoehdon äänet,
		jotta palkista saadaan sopivan pituinen.
	 */
	function naytaAanestysvaihtoehto($tiedot, $onAanestetty, $vertailukohta) {
		
		$palkinPituus = $vertailukohta == 0 ? 10 : ($tiedot["aanestyskerrat"] / $vertailukohta) * 300 + 10;
		if($onAanestetty == $tiedot["id"])
			$tiedot['nimi'] = '<b>'.$tiedot['nimi'].'</b>';
		
		echo '
			<tr>
				<td style="width:100%;">
					<a href="javascript:naytaTaiPiilota(\'kuvaus_'.$tiedot['id'].'\')">'. $tiedot['nimi'] . '</a>
					<div id="kuvaus_'.$tiedot['id'].'" style="display:none;">('.$tiedot['kuvaus'].')</div>
				</td>
				<td style="width:20px;">'. $tiedot["aanestyskerrat"] .'</td>';
		
		if($onAanestetty === false && isset($_SESSION['kayttajanimi']))
			echo '<td><input name="vaihtoehdot" value="'.$tiedot['id'].'" type="radio" /></td>';

		echo '	<td style="width:300px;"><div style="background:red; width:'.$palkinPituus.'px; height:10px;"></div></td>
			</tr>';
	}
	
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
	 * Kertoo, onko äänestys vielä voimassa.
	 * @param $aanestys Äänestys, jonka voimassaolemisesta ollaan kiinnostuneita.
	 * @return True tai false.
	 */
	function aanestysOnVielaVoimassa($aanestys) {
		
		$kysely = lahetaSQLKysely("SELECT * FROM aanestys WHERE id = ? AND paattymispaiva > now()", array($aanestys));
		return $kysely->rowCount() > 0;
	}
	
	/**
	 * Näyttää äänestysvaihtoehdot ja niiden saamat äänet.
	 * @param $äänestys Äänestys, jota olemme kiinnostuneita tarkastelemaan.
	 * @param $aanestettyVaihtoehto Se vaihtoehto, jota tarkastelija on jo äänestänyt. False, jos ei ole äänestänyt.
	 */
	function naytaAanestyksenTulokset($aanestys, $aanestettyVaihtoehto = false) {
		
		$kysely = lahetaSQLKysely("
			SELECT * FROM aanestysvaihtoehto WHERE aanestys = ?",
			array($aanestys));
		
		$vaihtoehdot = $kysely->fetchAll();
		$enitenAaniaSaanut = laskeEnitenAaniaSaanut($vaihtoehdot);
		
		echo '
		<form id="aanestyslaatikko" method="post">
			<input name="aanestaminen" type="hidden" value="'.$aanestys.'" />
			<table>';
			
		foreach($vaihtoehdot as &$vaihtoehto)
			naytaAanestysvaihtoehto($vaihtoehto, $aanestettyVaihtoehto, $enitenAaniaSaanut);
	
		echo '</table>';
		
		if(!aanestysOnVielaVoimassa($aanestys))
			echo '<i>Äänestysaika on umpeutunut.</i>';
		else if($aanestettyVaihtoehto === false) {
			if(isset($_SESSION['kayttajanimi']))
				echo '<input type="submit" value="Äänestä!" style="margin-left:auto;width:75px;display:block;" />';
			else
				echo '<i><br />Kirjaudu sisään äänestääksesi.</i>';
		} else
			echo '<i><br />Olet äänestänyt.</i>';
		echo '</form>';
	}
	
	/**
	 * Tallentaa käyttäjän antaman äänen tietokantaan. Olettaa, että tarkistelemiset
	 * virheiden varalta on tätä kutsuttaessa jo suoritettu.
	 * @param $aanestys Se äänestys, jossa äänestetään.
	 * @param $vaihtoehto Se vaihtoehto, jota äänestetään.
	 */
	function aanesta($aanestys, $vaihtoehto) {
		
		$kysely = lahetaSQLKysely("
			INSERT INTO aani (aanestys, vaihtoehto, aanestaja) VALUES (?, ?, ?);",
			array($aanestys, $vaihtoehto, $_SESSION['kayttajanimi']));

		$kysely = lahetaSQLKysely("
			UPDATE aanestysvaihtoehto
				SET aanestyskerrat = aanestyskerrat + 1
				WHERE id = ?;",
			array($vaihtoehto));
	}
?>