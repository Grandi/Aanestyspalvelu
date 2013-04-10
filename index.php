<?php

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
