var rating = 0;

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
 * Hides the specified element.
 **/
function hide(id) {
	document.getElementById(id).style.display = 'none';
}

/**
 * Shows the specified element.
 **/
function show(id) {
	document.getElementById(id).style.display = 'block';
}

// POST handling functions

/**
 * Requests the 'read review' modal.
 **/
function showReview(reviewID) {
	postValue("displayReview", reviewID);
}

/**
 * Handles the swapping of venues via $_POST.
 * Returns to the index page, with no venue selected if -1 is passed.
 **/
function swapVenue(id) {
	postValue("v", id);
}

/**
 * Handles the swapping of venues via $_POST.
 **/
function swapVenueType(type) {
	postValue("t", type);
}

/**
 * Called when the user submits a review. POSTs the rating, summary, and body to the server,
 * after error checking. If the data is erroneous, an error message is displayed.
 **/
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
	postValues([["reviewRating", rating], ["reviewSummary", reviewSummary], ["reviewBody", reviewBody]]);
}

/**
 * Convenience method - POSTs the specified name/value pairs to the server.
 * They should be defined in an array, with each pair being a sub-array - name:value.
 * Usage: post([["name", "value"], ["name", "value"]]);
 **/
function postValues(arguments) {
	form = document.createElement("form");
	form.setAttribute("name", "postman");
	form.setAttribute("method", "POST");
	form.setAttribute("action", "index.php");

	for (i = 0; i != arguments.length; i++) {
		e = document.createElement("input");
		e.setAttribute("name", arguments[i][0]);
		e.setAttribute("type", "hidden");
		e.setAttribute("value", arguments[i][1]);
		form.appendChild(e);
	}
	document.body.appendChild(form);
	form.submit();
}

/**
 * Convenience method - POSTs the specified name/value pair to the server.
 **/
function postValue(name, value) {
	form = document.createElement("form");
	form.setAttribute("name", "postman");
	form.setAttribute("method", "POST");
	form.setAttribute("action", "index.php");

	c = document.createElement("input");
	c.setAttribute("name", name);
	c.setAttribute("type", "hidden");
	c.setAttribute("value", value);
	form.appendChild(c);

	document.body.appendChild(form);
	form.submit();
}