/**
 * Handles the Google Maps map
 **/

/*
 * Displays the specified venue on the map.
 */
function mapVenue(venue) {
}

/*
 * Initialises the Maps map.
 */
function initMap() {
	var map = new google.maps.Map(document.getElementById("map"), {
		center: { lat: -34.397, lng: 150.644 },
		scrollwheel: false,
		zoom: 8
	});
}

/*
 * Displays the route to the specified venue on the map from the user's current position.
 */
function mapDirections(venue) {
	var chicago = {lat: 41.85, lng: -87.65};
	var indianapolis = {lat: 39.79, lng: -86.14};

	var map = new google.maps.Map(document.getElementById("map"), {
		center: chicago,
		scrollwheel: false,
		zoom: 7
	});

	var directionsDisplay = new google.maps.DirectionsRenderer({
		map: map
	});

	// Set destination, origin and travel mode.
	var request = {
		destination: indianapolis,
		origin: chicago,
		travelMode: google.maps.TravelMode.DRIVING
	};

  // Pass the directions request to the directions service.
	var directionsService = new google.maps.DirectionsService();
		directionsService.route(request, function(response, status) {

		if (status == google.maps.DirectionsStatus.OK) {
	// Display the route on the map.
			directionsDisplay.setDirections(response);
		}
	});
}