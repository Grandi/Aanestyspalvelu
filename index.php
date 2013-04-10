<?php
	if($_SERVER['REMOTE_ADDR'] != '80.223.95.21')
		die("Sivu on teon alla. Tule myöhemmin takaisin.");

	session_start();

	include "koodit/yhteys.php";
	include "koodit/kayttaja.php";
	include "koodit/pyynnot.php";
	include "koodit/aanestys.php";
	
	alustaTietokantayhteys();	
	kasittelePyynnot();

	/** Näytetään käyttäjälle itse sivu. */
	include "koodit/sivurakenne.php";
?>