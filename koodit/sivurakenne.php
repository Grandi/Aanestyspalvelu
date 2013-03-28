<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fi" lang="fi">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title>Äänestyspalvelu</title>
		<link rel="stylesheet" type="text/css" href="tyyli.css" />    
	</head>
	<body>
		<h1>Äänestyspalvelu</h1>
		<div id="linkkipalkki">
			
			<div style="clear: both"></div>
		</div>
		<div id="sisalto">
			<?php
				if(isset($_GET['rekisteroidy']))
					include "lomakkeet/rekisteroitymislomake.php";
				else
					include "testisisalto.php";
			?>
		</div>
		<div id="sivupalkki">
			<?php
				if(isset($_SESSION["kayttajanimi"])) {
					echo '<h3>Kirjauduttu käyttäjäksi <i>' . $_SESSION["kayttajanimi"] . '</i></h3>';
					echo '<a href="?kirjaudu_ulos">Kirjaudu ulos</a>';
				} else
					include "lomakkeet/kirjautumislomake.php";
			?>
		</div>
		<div style="clear: both"></div>
		<div id="footer">
		</div>
	</body>
</html>