var rating = 0;

function submitReview() {
	var reviewSummary = document.getElementById("reviewSummary").value;
	var reviewBody = document.getElementById("reviewBody").value;

	if (rating == 0) {
	 	document.getElementById("errorMessage").innerHTML = "Please provide a rating.";
	 	return;
	}
	if (reviewSummary == "") {
	 	document.getElementById("errorMessage").innerHTML = "Please provide a title.";
	 	return;
	}
	if (reviewBody == "") {
	 	document.getElementById("errorMessage").innerHTML = "Please provide a brief review.";
	 	return;
	}

	form = document.createElement("form");
	form.setAttribute("name", "reviewForm");
	form.setAttribute("method", "POST");
	form.setAttribute("action", "index.php");
	
	form.appendChild(prepValue("reviewRating", rating));
	form.appendChild(prepValue("reviewSummary", reviewSummary));
	form.appendChild(prepValue("reviewBody", reviewBody));

	document.body.appendChild(form);
	form.submit();
	alert("Review submitted.");
}

function prepValue(name, value) {
	var e = document.createElement("input");
	e.setAttribute("name", name);
	e.setAttribute("type", "hidden");
	e.setAttribute("value", value);
	return e;
}

function setRating(_rating) {
	rating = _rating;
	updateStars(rating);
}

function updateStars(display) {
	for (i = 1; i != 6; i++) {
		img = document.getElementById('star_' + i);
		img.setAttribute("src", "img/rating/" + (i == 0 || i > display ? "no" : "") + "star.png");
	}
}

function displayRating(toDisplay) {
	updateStars(toDisplay);
}

function clearDisplayRating() {
	updateStars(rating);
}

/**
 * Displays the 'write review' modal.
 **/
function showModal_writeReview() {
	document.getElementById('reviewModal').style.display = 'block';
}

/**
 * Hides the 'write review' modal.
 **/
function hideModal_writeReview() {
	document.getElementById('reviewModal').style.display = 'none';
}

/*
 * Handles the swapping of venues via $_POST.
 * Returns to the index page, with no venue selected if -1 is passed.
 */
function swapVenue(id) {
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