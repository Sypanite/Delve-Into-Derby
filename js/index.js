/*
 * Handles the swapping of venues via $_POST.
 */
function swapVenue(id) {
	// alert("Clicked " + id);
	form = document.createElement("form");
	form.setAttribute("method", "POST");
	form.setAttribute("action", "index.php");

	c = document.createElement("input");
	c.setAttribute("name", "v");
	c.setAttribute("type", "hidden");
	c.setAttribute("value", id);
	form.appendChild(c);

	document.body.appendChild(form);
	form.submit();
}