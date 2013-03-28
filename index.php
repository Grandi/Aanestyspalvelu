<?php
	session_start();

	include "koodit/yhteys.php";
	include "koodit/kayttaja.php";
	include "koodit/pyynnot.php";
	
	alustaTietokantayhteys();
	kasittelePyynnot();

	/** Näytetään käyttäjälle itse sivu. */
	include "koodit/sivurakenne.php";
?>