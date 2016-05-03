
var rating = 0;

/**
 * Updates the review stars to reflect the current review being displayed.
 **/
function updateStars(display) {
	for (i = 1; i != 6; i++) {
		img = document.getElementById('star_' + i);
		img.setAttribute("src", "img/rating/" + (i == 0 || i > display ? "no" : "") + "star.png");
	}
}

/**
 * Sets the current rating to the specified value.
 **/
function setRating(_rating) {
	rating = _rating;
	updateStars(rating);
}

/**
 * Configures the stars to represent the specified rating.
 **/
function displayRating(toDisplay) {
	updateStars(toDisplay);
}

/**
 * Configures the stars to represent the current selected rating.
 **/
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

	var check = checkReviewSubmission(reviewSummary, reviewBody);

	if (check != "OK") {
	 	document.getElementById("errorMessage").innerHTML = check;
	}
	else {
		postValues([["reviewRating", rating], ["reviewSummary", reviewSummary], ["reviewBody", reviewBody]]);
	}
}

/*
 * Error checks the user's review submission. Returns "OK" if all is in order, otherwise returns the appropriate error message.
 */
function checkReviewSubmission(reviewSummary, reviewBody) {
	if (rating == 0) {
	 	return "Please provide a rating.";
	}

	if (reviewSummary == "") {
		return "Please provide a title.";
	}
	else if (reviewSummary.length >= 30) {
		var tooLong = (reviewSummary.length - 31);
		return "Your title is " + tooLong + " character" + (tooLong == 1 ? "" : "s") + " too long!";
	}

	if (reviewBody == "") {
	 	return "Please provide a brief review.";
	}
	else if (reviewBody.length >= 251) {
		var tooLong = (reviewBody.length - 251);
		return "Your review is " + tooLong + " character" + (tooLong == 1 ? "" : "s") + " too long!";
	}
	return "OK";
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