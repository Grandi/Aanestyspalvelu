
/**
 * Näyttää tai piilottaa tahdotun elementin.
 * @param id Sen elementin ID, jonka näkyvyyttä tahdotaan säädellä.
 */
function naytaTaiPiilota(id) {
	elementti = document.getElementById(id);
	elementti.style.display =
		elementti.style.display == 'block'? 'none' : 'block';
}

/**
 * Luo ns. fragmentin, eli HTML-koodia jota voi lisätä sivulle.
 * @param lisattava HTML-koodi.
 */
function luoElementti(lisattava) {
    osa = document.createDocumentFragment();
    valiaikainen = document.createElement('div');

    valiaikainen.innerHTML = lisattava;
    while (valiaikainen.firstChild)
        osa.appendChild(valiaikainen.firstChild);

    return osa;
}

lisatytKentat = 0;

/**
 * Lisää lomakkeeseen uuden kentän.
 */
function lisaaKentta() {
	
	if(lisatytKentat >= 5)
		return;
	
	lisatytKentat++;
	osa = luoElementti(
		'<p><label>Nimi:&nbsp;</label><input name="vaihtoehto_' + lisatytKentat + '"></input><br />' + 
		'<label>Kuvaus:&nbsp; </label><input name="kuvaus_' + lisatytKentat + '"></input></p>');

	document.getElementById('vaihtoehdot').appendChild(osa,
		document.getElementById('vaihtoehdot').children.lastChild);
}

/**
 * Näyttää äänestyslaatikossa kuvauksena tahdotun tekstin.
 */
function naytaKuvauksena(teksti) {
	
	elementti = document.getElementById('kuvausteksti');
	if(elementti.style.display == 'none')
		elementti.style.display = 'block';

	elementti.innerHTML = teksti;
}