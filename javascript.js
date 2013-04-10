
/**
 * Näyttää tai piilottaa tahdotun elementin.
 * @param id Sen elementin ID, jonka näkyvyyttä tahdotaan säädellä.
 */
function naytaTaiPiilota(id) {
	elementti = document.getElementById(id);
	elementti.style.display =
		elementti.style.display == 'block'? 'none' : 'block';
}