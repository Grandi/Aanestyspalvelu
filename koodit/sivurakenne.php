<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fi" lang="fi">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title>Äänestyspalvelu</title>
		<link rel="stylesheet" type="text/css" href="tyyli.css" />
		<script src="javascript.js"></script>
	</head>
	<body>
		<h1>Äänestyspalvelu</h1>
		<div id="linkkipalkki">
			<a href="index.php">Selaa äänestyksiä</a>
			<div style="clear: both"></div>
		</div>
		<div id="sisalto">
			<?php

				if(isset($_GET['luonti']))
					include 'lomakkeet/aanestyksenluontilomake.php';
				
				else if(isset($_GET['aanestys']))
					naytaAanestyssivu($_GET['aanestys']);
				
				else if(isset($_GET['rekisteroidy']))
					include "lomakkeet/rekisteroitymislomake.php";
				
				else if(isset($_GET['omat_aanestykset'])) {
					echo '<h2>Tekemäsi äänestykset</h2>';
					listaaKayttajanOmatAanestykset();

				} else {
					echo '<h2>Äänestykset</h2>';
					listaaAanestykset();
				}

			?>
		</div>
		
		<?php
			include "koodit/sivupalkki.php"
		?>
		
		<div style="clear: both"></div>
		<div id="footer"></div>
	</body>
</html>