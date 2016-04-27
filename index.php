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
			$_SESSION["currentVenue"] = 24;				// Set the current venue ID
			//
			
			ChromePhp::log("Checking database.");

			if (!array_key_exists("databaseManager", $_SESSION)) {
				$databaseManager = new DatabaseManager();
				$_SESSION["databaseManager"] = $databaseManager;

				$connectAttempt = $databaseManager->connect();

				if ($connectAttempt == "TRUE") {
					ChromePhp::log('Successfully established a connection to the database.');
				}
				else {
					fail("Failed to establish a connection to the database: $connectAttempt");
				}
			}
			else {
				$databaseManager = $_SESSION["databaseManager"];
				ChromePhp::log("Using session database instance.");
			}

			if (!array_key_exists("venueType", $_SESSION)) {
				fail("No venue type specified.");
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
			// createReviewList();

			// $databaseManager->saveReview(0, "Meh", "This is a test. Food was all right.", 4);
			session_write_close();

			/**
			 * Echo the error, print it to the console, and stop execution of the script.
			 **/
			function fail($message) {
				echo "<p>$message</p>";
				ChromePhp::log("$message");
				exit(1);
			}

			function createVenueList($venueList) {
				ChromePhp::log("Creating venue list.");

				// Create the list of venues
				
				echo '<nav class="w3-sidenav w3-light-grey" style="width:30%">
					  <div class="w3-container w3-section">
					  <div class="w3-container w3-dark-grey">
						<h4>' . getVenueTypeName() . 's</h4>
					  </div>';
					  
				echo '<ul href="#" class="w3-ul w3-hoverable w3-container w3-section">';
			
				ChromePhp::log("Current venue: " . $_SESSION["currentVenue"]);
				ChromePhp::log("List: '$venueList'");

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
					echo "<span>$address, Derby, $postcode / $rating</span>";
					echo '</li>';
				}
				echo '</ul>
					  </div>
					  </nav>';

				echo '<div style="margin-left:50%">
						  <div class="w3-container">
							<p>...</p>
						  </div>
					  </div>';

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
