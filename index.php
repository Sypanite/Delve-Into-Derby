<?php
require "lib/chromephp/ChromePhp.php";

require "src/Venue.php";
require "src/Review.php";
require "src/DatabaseManager.php";

error_reporting(E_ALL);
ini_set('display_errors', true);

session_start();
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Delve Into Derby</title>
		<link rel="icon" href="img/favicon.png">

		<!-- w3.css - handles most styling -->
        <link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css">

		<!-- stylesheet.css - includes my styling, unused (remove if forever unused)  -->
        <link rel="stylesheet" href="stylesheet.css">

		<!-- Font Awesome - handles menu icons -->
        <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">

		<script src="js/index.js"></script>
    </head>
    <body>
		<?php

			$databaseManager = "N/A";
			$venueType;
			$venueID;
			$venueList;

			// $_SESSION = array(); // Clear session

			ChromePhp::log("_POST contents (" . count($_POST) . " values):\n" . var_export($_POST, true));
			ChromePhp::log("Ready to load session. _SESSION contains " . count($_SESSION) . " values.");

			/*
			 * Check $_POST for incoming data.
			 */

			// Check for changes in venue type
			if (isset($_POST["t"])) {
				$last = "N/A";

				if (isset($_SESSION["venueType"])) {
					$last = $_SESSION["venueType"];
				}

				ChromePhp::log("POST typeID: " . $_POST["t"]);
				$venueType = $_POST["t"];
				unset($_POST["t"]);

				if ($last == "N/A" || $venueType != $last) {
					unset($_SESSION["venueID"]); // Old venue ID is wrong
					unset($_SESSION["venueList"]); // Venues need re-loading
				}
			}
			else {
				if (isset($_SESSION["venueType"])) {
					$venueType = $_SESSION["venueType"];
					ChromePhp::log("Using session venueType ($venueType).");
				}
				else {
					ChromePhp::log("No type stored - defaulting to 'R'.");
					$venueType = "R";
				}
			}

			// Check for change of venue
			if (isset($_POST["v"])) {
				$venueID = $_POST["v"];
				unset($_POST["v"]);
				ChromePhp::log("POST venueID: " . $venueID);
			}
			else {
				if (isset($_SESSION["venueID"])) {
					$venueID = $_SESSION["venueID"];
					ChromePhp::log("Using session venueID ($venueID).");
				}
				else {
					ChromePhp::log("No venueID POSTed or stored.");
					$venueID = -1; // getDefaultVenue($venueType);
				}
			}

			// Check / initialise the database manager
			if (isset($_SESSION["databaseManager"])) {
				$databaseManager = $_SESSION["databaseManager"];
				ChromePhp::log("Using session DatabaseManager.");
			}
			else {
				$databaseManager = new DatabaseManager();
				ChromePhp::log("No DatabaseManager in session - created new instance.");
			}
			
			// Check the venue list

			if (isset($_SESSION["venueList"])) {
				ChromePhp::log("Using session venue list.");
				$venueList = $_SESSION["venueList"];
			}
			else {
				ChromePhp::log("No venue list in session - re-loading.");
				$venueList = $databaseManager->loadVenues($venueType);
			}

			// Check for a review submission
			if (isset($_POST["reviewRating"])) {
				$title = $_POST["reviewSummary"];
				$body = $_POST["reviewBody"];
				$rating = $_POST["reviewRating"];

				ChromePhp::log("POSTed a new review - $title / $body / $rating");
				$newRevue = new Review($title, $body, $rating, date("Y-m-d H:i:s"));

				$venueList[$venueID]->addReview($newRevue);
				// $databaseManager->saveReview(venueList[$venueID], $newRevue);
			}

			// Create the sidebar
			echo '<nav class="w3-sidenav w3-light-grey" style="width:25%">';
			
			// <button class="w3-btn w3-black w3-xlarge"><i class="fa fa-search"></i></button>

			// Create the menu bar
			echo '<div class="w3-padding w3-xxlarge w3-black">
					<button class="w3-btn w3-black w3-xlarge"><i class="fa fa-home" onclick="swapVenue(-1)"></i></button>
					<button class="w3-btn w3-black w3-xlarge">
						<i class="fa fa-commenting-o" onclick="showModal_writeReview();"></i>
					</button>
					<button class="w3-btn w3-black w3-xlarge"><i class="fa fa-map-marker" onclick="showDirections()"></i></button>
				  </div>';

			if ($venueID == -1) {
				createVenueList($venueList, $venueType);
			}
			else {
				createReviewModal($venueList[$venueID], $venueType);
				createReviewList($venueList[$venueID], $venueType);
			}
			echo '</nav>';
			createTopBar($venueList[$venueID]);

			// createMap($venueList[$venueID]);

			// Save the session
			ChromePhp::log("Saving session...");
			$_SESSION["databaseManager"] = $databaseManager;
			$_SESSION["venueType"] = $venueType;
			$_SESSION["venueID"] = $venueID;
			$_SESSION["venueList"] = $venueList;
			session_write_close();
			ChromePhp::log("Session saved.");

			//

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

			function createTopBar($venue) { //  ' . $venue->getTelephoneNumber() . '
				echo '<div class="w3-container" style="margin-left:25% ">
					   <section class="w3-container" width:20%>
						<i class="fa fa-phone w3-xlarge w3-left w3-top">
							<span class="w3-container w3-section">' . $venue->getTelephoneNumber() . '</span>
						</i>
						<i class="fa fa-globe w3-xlarge w3-left w3-bottom">
							<a href="http://www.' . $venue->getWebsite() . '" target="_blank" class="w3-container w3-section">www.' . $venue->getWebsite() . '</a>
						</i>
					   </section>';
					  echo '</<div>';
					 // <i class="fa fa-phone w3-xlarge w3-left"></i> .
			}

			function createReviewModal($venue, $venueType) {
				echo '
				<div id="reviewModal" class="w3-modal">
				  <div class="w3-modal-content w3-card-8 w3-animate-zoom" style="max-height:800px max-width:600px">
				  <span onclick="hideModal_writeReview()" class="w3-closebtn w3-container w3-padding-hor-16 w3-display-topright">&times;</span>
  
					<div class="w3-center"><br>
					  <h2><b>' . $venue->getName() . '</b></h2>
					  <span>What did you think of this ' . strtolower(getVenueTypeName($venueType)) . '?</span>
					  <div class="w3-content w3-section" w3-padding>';

					  // Build the stars
					  for ($i = 1; $i != 6; $i++) {
						  echo '<img id="star_' . $i . '" src="img/rating/nostar.png"
								 onclick="setRating(' . $i . ')"
								 onmouseover="displayRating(' . $i . ')"
								 onmouseout="clearDisplayRating()">';
					  }
					  
					  echo '
					  </div>
					</div>

					<div class="w3-container">
					  <div class="w3-section">

						<input id="reviewSummary" class="w3-input w3-border w3-margin-bottom" type="text" placeholder="Brief summary">
						<textarea id="reviewBody" rows="4" class="w3-input w3-border" type="text" placeholder="Why did you give this ' . strtolower(getVenueTypeName($venueType)) . ' the rating you did?"></textarea>
						<br>
						<div class="w3-center">
						  <b><span id="errorMessage" class="w3-text-red"></span></b>
						</div>
						<br>
						<button class="w3-btn w3-btn-block" onclick="submitReview()">Submit</button>
					  </div>
					</div>
				  </div>
				</div>';
			}
			/*
			<textarea rows="4" cols="50">
			At w3schools.com you will learn how to make a website. We offer free tutorials in all web development technologies. 
			</textarea>
			*/

			/**
			 * Creates a HTML list of reviews based on the array passed in.
			 * The second parameter is the venue type - this is used in the event that there are no reviews.
			 **/
			function createReviewList($venue, $venueType) {
				$reviewList = $venue->getReviewList();
				echo '<h3 class="w3-center">Reviews</h3>';

				if (count($reviewList) == 0) {
					echo '<ul href="#" class="w3-ul w3-container w3-section">';
					echo '<li><span>There are no reviews for this ' . strtolower(getVenueTypeName($venueType)) . '!
									Why not add one?</span></li>';
					echo '</ul>';
					ChromePhp::log("No reviews.");
				}
				else {					
				    echo '<ul href="#" class="w3-ul w3-hoverable w3-container w3-section">';

					// Add each review to the list
					for ($i = 0; $i != count($reviewList); $i++) {
						$title = $reviewList[$i]->getTitle();
						$snippet = $reviewList[$i]->getSnippet();
						$rating = $reviewList[$i]->getRating();
						$date = $reviewList[$i]->getDate();
				
						echo '<li>';
						echo '<a href="#" onclick="showReview(\'' . $i . '\')" style="fill_div" class="w3-hover-none w3-hover-text-white" >';
						echo '<span class="w3-large">';
						echo '<img src="img/rating/' . $rating . '.png" class="w3-right" style="width:25%">';
						echo "$title</span><br>";
						echo "<span>$snippet";
						// echo '<span class="w3-right" style="width:25%">' . $date . '>';
						echo '</li>';
					}
					echo '</ul>';
				
					ChromePhp::log("Looping through " . count($reviewList) . " reviews.");
				}
			}

			/**
			 * Creates the left-side venue list.
			 **/
			function createVenueList($venueList, $venueType) {
				ChromePhp::log("Creating venue list.");

				echo '<div class="w3-container w3-section">
						 <li class="w3-dropdown-hover w3-light-grey">
							<h3><a href="#" class="w3-center">' . getVenueTypeName($venueType) . 's</a></h3>

							  <div class="w3-dropdown-content">
								<a href="#" onclick="swapVenueType(\'R\')">Restaurants</a>
								<a href="#" onclick="swapVenueType(\'C\')">Cinemas</a>
								<a href="#" onclick="swapVenueType(\'M\')">Museums</a>
							  </div>
						 </li>
					 </div>
				     <ul href="#" class="w3-ul w3-hoverable w3-container w3-section">';
				
				ChromePhp::log("Looping through " . count($venueList) . " venues.");

				// Add each venue to the list
				for ($i = 0; $i != count($venueList); $i++) {
					$id = $venueList[$i]->getID();
					$name = $venueList[$i]->getName();
					$address = $venueList[$i]->getAddress();
					$postcode = $venueList[$i]->getPostcode();
					$rating = $venueList[$i]->getAverageRating();
					
					// ChromePhp::log("Creating list entry for $i/" . count($venueList) . ": '" . $name . "'");
				
					echo '<li>';
					echo '<a href="#" onclick="swapVenue(\'' . $id . '\')" style="fill_div" class="w3-hover-none w3-hover-text-white" >';
					echo '<span class="w3-large">';
					echo '<img src="img/rating/' . $rating . '.png" class="w3-right" style="width:25%">';
					echo "$name</span><br>";
					echo "<span>$address, Derby, $postcode</span>";
					echo '</li>';
				}
				echo '</ul>';
			}

			function getVenueTypeName($venueType) {
				switch($venueType) {
					case "R":
						return "Restaurant";

					case "C":
						return "Cinema";

					case "M":
						return "Museum";
				}
			}
			
			/**
			 * Returns the default venue ID for this type (who needs OCP...)
			 **/
			function getDefaultVenue($venueType) {
				switch($venueType) {
					case "R":
						return 0;
				
					case "C":
						return 20;

					case "M":
						return 24;
				}
				return -1;
			}
		?>
    </body>
</html>