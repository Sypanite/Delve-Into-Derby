<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Delve Into Derby</title>
		<link rel="icon" href="img/favicon.png">
        <link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css">
        <link rel="stylesheet" href="stylesheet.css">
		<script src="js/index.js"></script>
    </head>
    <body>
		<?php

			require "lib/chromephp/ChromePhp.php";

			require "src/Venue.php";
			require "src/Review.php";
			require "src/DatabaseManager.php";

			session_start();
	
			// Debug
			// $_SESSION["venueType"] = "R";			// Set to restaurants
			// $_SESSION["venueType"] = "C";			// Set to cinemas
			$_SESSION["venueType"] = "M";				// Set to museums
			//
			
			ChromePhp::log("Checking database.");

			if (!array_key_exists("databaseManager", $_SESSION)) {
				$databaseManager = new DatabaseManager();
				$_SESSION["databaseManager"] = $databaseManager;

				$connectAttempt = $databaseManager->connect();

				if ($connectAttempt == "TRUE") {
					ChromePhp::log("Successfully established a connection to the database.");
				}
				else {
					ChromePhp::log("Failed to establish a connection to the database: $connectAttempt.");
				}
			}
			else {
				$databaseManager = $_SESSION["databaseManager"];
				ChromePhp::log("Using session database instance.");
			}

			// No venue specified
			if (!array_key_exists("venueType", $_SESSION)) {
				$_SESSION["venueType"] = "R"; // Default to restaurants
			}

			// Change of venue
			if (array_key_exists("t", $_POST)) {
				$_SESSION["venueType"] = $_POST["t"];

				// Set the default venue
				switch($_SESSION["venueType"]) {
					case "R":
						$_SESSION["currentVenue"] = 0;
					break;
					
					case "C":
						$_SESSION["currentVenue"] = 20;
					break;

					case "M":
						$_SESSION["currentVenue"] = 24;
					break;
				}
			}

			$venueType = $_SESSION["venueType"];
			$databaseManager = $_SESSION["databaseManager"];
			
			if (!array_key_exists("venues_$venueType", $_SESSION)) {
				ChromePhp::log("No venue list in session - creating.");
				$_SESSION["venues_$venueType"] = $databaseManager->loadVenues($venueType);
			}

			$venueList = $_SESSION["venues_$venueType"];
			
			if (array_key_exists("v", $_POST)) {
				$_SESSION["currentVenue"] = $_POST["v"];
			}

			createVenueList($venueList);
			// createReviewList($venueList[$_SESSION["currentVenue"]]->getReviewList());
			createMap($venueList[$_SESSION["currentVenue"]]);

			session_write_close();

			/**
			 * Loads and displays the Google Maps map.
			 **/
			function createMap($venue) {
				PhpConsole::log("Querying maps for '" . $venue->getName() . "'.");
				$query = str_replace(' ', '+', $venue->getName() . " Derby"); // Should do it
				PhpConsole::log("Querying maps: $query");

				echo '<iframe width="600" height="450"
					  frameborder="0" style="border:0"
					  src="https://www.google.com/maps/embed/v1/place?key=AIzaSyCe4BB7jH_lBh7vMj0xJrvh8vivkhJNwj0
						   &q=' . $query . '">
					  </iframe>';
			}

			/**
			 * Creates the left-side venue list.
			 **/
			function createVenueList($venueList) {
				echo '<nav class="w3-sidenav w3-light-grey" onmouseout="closeVenueList()" style="width:30%">
					  <div class="w3-container w3-section">
					  <div class="w3-container">
						<li class="w3-dropdown-hover w3-light-grey">
						<h4><a href="#">' . getVenueTypeName() . 's</a></h4>
						<div class="w3-dropdown-content">
						  <a href="#" onclick="swapVenueType(\'R\')">Restaurants</a>
						  <a href="#" onclick="swapVenueType(\'C\')">Cinemas</a>
						  <a href="#" onclick="swapVenueType(\'M\')">Museums</a>
						</div>
					  </div>';
					  
				echo '<ul href="#" class="w3-ul w3-hoverable w3-container w3-section">';

				// Add each venue to the list
				for ($i = 0; $i != count($venueList); $i++) {
					$venue = $venueList[$i];

					$id = $venue->getID();
					$name = $venue->getName();
					$address = $venue->getAddress();
					$postcode = $venue->getPostcode();
					$rating = $venue->getRating();
				
					
					if ($id == $_SESSION["currentVenue"]) {
						echo '<li class="w3-blue">';
					}
					else {
						echo '<li>';
					}
					echo '<a href="#" onclick="swapVenue(\'' . $id . '\')" style="fill_div" class="w3-hover-none w3-hover-text-white" >';
					echo '<span class="w3-large">';
					echo '<img src="img/rating/' . $rating . '.png" class="w3-right" style="width:25%">';
					echo "$name</span><br>";
					echo "<span>$address, Derby, $postcode</span>";
					echo '</li>';
				}
				echo '</ul>
					  </div>
					  </nav>';
			}

			function getVenueTypeName() {
				switch($_SESSION["venueType"]) {
					case "R":
						return "Restaurant";

					case "C":
						return "Cinema";

					case "M":
						return "Museum";
				}
			}
		?>
    </body>
</html>
