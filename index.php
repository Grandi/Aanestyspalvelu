<?php

	/**
	 * Äänestysvaihtoehtojen värit.
	 */
	$varit = array(
		0 => array(255, 0, 0),   /* Punainen */
		1 => array(0, 0, 255),   /* Sininen */
		2 => array(0, 200, 0),   /* Vihreä */
		3 => array(255, 150, 0), /* Oranssi */
		4 => array(200, 0, 200)  /* Magenta */
	);

	session_start();

	include "koodit/yhteys.php";
	include "koodit/kayttaja.php";
	include "koodit/pyynnot.php";
	include "koodit/aanestys.php";
	include "koodit/loppuraportti.php";
	include "koodit/aanestyksien_nayttaminen.php";
	
	alustaTietokantayhteys();	
	kasittelePyynnot();
	
	if(isset($_GET["debug"]))
		generoiTestiaanestys();

	/** Näytetään käyttäjälle itse sivu. */
	include "koodit/sivurakenne.php";
?>