/*
 * Handles the swapping of venues via $_POST.
 */
function swapVenue(id) {
	// alert("Clicked " + id);
	form = document.createElement("form");
	form.setAttribute("name", "venueForm");
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

/*
 * Handles the swapping of venues via $_POST.
 */
function swapVenueType(type) {
	// alert("Clicked " + id);
	form = document.createElement("form");
	form.setAttribute("name", "typeForm");
	form.setAttribute("method", "POST");
	form.setAttribute("action", "index.php");

	c = document.createElement("input");
	c.setAttribute("name", "t");
	c.setAttribute("type", "hidden");
	c.setAttribute("value", type);
	form.appendChild(c);

	document.body.appendChild(form);
	form.submit();
}

function openVenueList() {
    document.getElementsByClassName("w3-sidenav")[0].style.display = "block";
}

function closeVenueList() {
    document.getElementsByClassName("w3-sidenav")[0].style.display = "block";
}