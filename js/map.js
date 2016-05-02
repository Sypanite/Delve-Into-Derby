/**
 * Handles the Google Maps map.
 * Often based on example code by Google.
 **/

/*
 * $_POST the user's location to the server so it can be stored in $_SESSION.
 */
function getUserLatLong(position) {
	alert("Posting lat/long");
	postValues([["latitude", position.coords.latitude], ["longitude", position.coords.longitude]]);
}

/*
 * Initialises the map.
 */
function initMap() {
	var latLong;
	var mapLatLong;
	var userLatLong;
	var label;

	latLong = document.getElementById("latLong").getAttribute("name");
	latLong = latLong.split(",");
	mapLatLong = {lat: parseFloat(latLong[0]), lng: parseFloat(latLong[1])};

	label = document.getElementById("venueName").getAttribute("name");

	if (navigator.geolocation) {
		if (!document.getElementById("userLatLong")) {
			navigator.geolocation.getCurrentPosition(getUserLatLong);
		}
		else {
			latLong = document.getElementById("userLatLong").getAttribute("name");
			latLong = latLong.split(",");
			userLatLong = {lat: parseFloat(latLong[0]), lng: parseFloat(latLong[1])};
		}
	}
	
	var map = new google.maps.Map(document.getElementById("map"),
	{
		center: mapLatLong,
		zoom: 14
	});

	var placeMarker = new google.maps.Marker(
	{
		position: mapLatLong,
		map: map,
		title: label
	});

	if (userLatLong) {
		var userMarker = new google.maps.Marker(
		{
			position: userLatLong,
			map: map,
			title: "You",
			label: "Y"// "img/maps/restaurants.png"
		});
	}
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