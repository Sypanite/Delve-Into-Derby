/**
 * Handles the Google Maps map.
 * Often based on example code by Google.
 **/

var map;
var centreLatLong;
var userLatLong;

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
	var label;

	latLong = document.getElementById("centreLatLong").getAttribute("name");
	latLong = latLong.split(",");
	centreLatLong = {lat: parseFloat(latLong[0]), lng: parseFloat(latLong[1])};
	
	var map = new google.maps.Map(document.getElementById("map"),
	{
		center: centreLatLong,
		zoom: 14
	});

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

	var venue = document.getElementById("venue_0");
	var i = 0;
	
	while (venue) {
		latLong = venue.getAttribute("name");
		var tokens = latLong.split("[@]");

		var name = tokens[0];
		latLong = tokens[1].split("[,]");

		latLong = {lat: parseFloat(latLong[0]), lng: parseFloat(latLong[1])};

		var placeMarker = new google.maps.Marker(
		{
			position: latLong,
			map: map,
			title: name,
			index: i
		});
		
		addListener(placeMarker);
		venue = document.getElementById("venue_" + (i++));
	}

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

function addListener(marker) {
	marker.addListener('click',
		function () {
			postValue("v", marker.index - 1);
		}
	);
}

/**
 * Displays the route to the specified venue on the map from the user's current position.
 * Based on example code by Google.
 * Doesn't seem to work. Forget it - priorities.
 **/
function mapDirections() {
	var directionsDisplay = new google.maps.DirectionsRenderer({
		map: map
	});

	var request = {
		destination: venueLatLong,
		origin: userLatLong,
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