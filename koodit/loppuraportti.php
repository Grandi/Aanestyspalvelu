<?php

	/**
	 * Generoi taulukon päiviä. Jokainen päiväindeksi taas sisältää oman taulukkonsa,
	 * jossa on sen hetkiset äänimäärät kullekin äänestysvaihtoehdolle.
	 * @param $aanet Taulukko äänestykselle annetuista äänistä.
	 * @return Taulukko äänestyksen äänestyshistoriasta.
	 */
	function generoiAanestyshistoria($aanet) {

		$historia =  array();
		$tilastot =  array(0, 0, 0, 0, 0);
		$edelliset = $tilastot;
		$edellinen = null;
		
		array_push($historia, $tilastot);
		
		foreach($aanet as $aani) {
			
			if($edellinen != null) {

				$uusi =  new DateTime($aani["paivamaara"]);
				$vanha = new DateTime($edellinen);

				$ero = $uusi->diff($vanha);
				if($ero->days > 0) {	

					for($i = 0; $i < $ero->days - 1; $i++)
						array_push($historia, $edelliset);
					
					array_push($historia, $tilastot);
					$edelliset = $tilastot;
				}
			}
			
			$edellinen = $aani["paivamaara"];
			$tilastot[$aani["vaihtoehto"]]++;
		}
		
		array_push($historia, $tilastot);
		
		$suurinAanimaara = 0;
		foreach($tilastot as $aanimaara)
			$suurinAanimaara = max($suurinAanimaara, $aanimaara);
		
		return array($historia, $suurinAanimaara);
	}
	
	/**
	 * Piirtää yksinkertaisen havainnollistavan kuvan siitä kuinka äänestystyksen
	 * vaihtoehdot ovat ajan mittaan saaneet ääniä.
	 * @param $aanestyshistoria Taulukko eri päivien äänimääristä.
	 * @param $suurinAanimaara Suurin äänimäärä, jonka äänestyksen jokin vaihtoehto on saanut.
	 * @return Äänestyshistoria graafisessa muodossa.
	 */
	function piirraKuvaAanestyshistoriasta($aanestyshistoria, $suurinAanimaara) {
		global $varit;
		
		$kuva = imagecreate(640, 340);
		$tausta = imagecolorallocate($kuva, 255, 255, 255);
		$musta = imagecolorallocate($kuva, 0, 0, 0);
		
		imagesetthickness($kuva, 3);
		
		imageline($kuva, 20, 20, 20, 320, $musta);
		imageline($kuva, 20, 320, 620, 320, $musta);
		
		$pigmentti = array();
		foreach($varit as $vari)
			array_push($pigmentti, imagecolorallocate($kuva, $vari[0], $vari[1], $vari[2]));
			
		$xHyppy = 600 / count($aanestyshistoria);
		$yHyppy = 300 / $suurinAanimaara;
		
		for($vuorokausi = 0; $vuorokausi < count($aanestyshistoria) - 1; $vuorokausi++) {
			
			$tamanpaivaiset = $aanestyshistoria[$vuorokausi];
			$huomiset = $aanestyshistoria[$vuorokausi + 1];
			
			for($vaihtoehto = 0; $vaihtoehto < 5; $vaihtoehto++) {

				if($huomiset[$vaihtoehto] == 0)
					continue;
				
				imageline($kuva,
					20  + $vuorokausi * $xHyppy,
					320 - $tamanpaivaiset[$vaihtoehto] * $yHyppy,
					20  + ($vuorokausi + 1) * $xHyppy,
					320 - $huomiset[$vaihtoehto] * $yHyppy,
						$pigmentti[$vaihtoehto]);
			}
		}
		
		return $kuva;
	}

	/**
	 * Generoi loppuraportin eli äänestyshistoriaa kuvaavan kuvan.
	 * @param $aanestys Sen äänestyksen ID-tunnus, jolle tahdotaan loppuraportti.
	 */
	function generoiLoppuraportti($aanestys) {

		$kysely = lahetaSQLKysely("
			SELECT * FROM aani
			WHERE aanestys = ?
			ORDER BY paivamaara",
			array($aanestys));

		$aanet = $kysely->fetchAll();
		list($historia, $suurinAanimaara) = generoiAanestyshistoria($aanet);
		
		$kuva = piirraKuvaAanestyshistoriasta($historia, $suurinAanimaara);
		imagepng($kuva, "loppuraportit/" . $aanestys . ".png");
		chmod("loppuraportit/".$aanestys.".png", 0755);
	}
	
	/**
	 * Kertoo, missä äänestyksen loppuraportti sijaitsee. Jos sitä ei vielä ole,
	 * se generoidaan samalla.
	 * @param $aanestys Se äänestys, jonka loppuraportista ollaan kiinnostuneita.
	 * @return Merkkijonona loppuraportin sijainti.
	 */
	function haeLoppuraportti($aanestys) {

        if(!file_exists("loppuraportit"))
            mkdir("loppuraportit");
	
		$sijainti = "loppuraportit/".$aanestys.".png";
		if(!file_exists($sijainti))
			generoiLoppuraportti($aanestys);
		
		return $sijainti;
	}
?>
