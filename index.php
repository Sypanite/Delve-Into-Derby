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

		<!-- stylesheet.css - includes my styling  -->
        <link rel="stylesheet" href="stylesheet.css">

		<!-- Font Awesome - handles menu icons -->
        <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">

		<script src="js/index.js"></script>
    </head>
    <body>
		<?php

			$databaseManager = "N/A";
			$venueType = NULL;
			$venueID = NULL;
			$venueList = NULL;
			$displayReview = -1;
			$venueTypeList = NULL;

			// $_SESSION = array(); // Clear session

			ChromePhp::log("_POST contents (" . count($_POST) . " values):\n" . var_export($_POST, true));
			ChromePhp::log("Ready to load session. _SESSION contains " . count($_SESSION) . " values.");

			/*
			 * Check $_POST for incoming data, and load the session.
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
			
			// Check for a request to display a review
			if (isset($_POST["displayReview"])) {
				$displayReview = $_POST["displayReview"];
				ChromePhp::log("Display request: $displayReview");
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

			if (isset($_SESSION["venueList"])) {
				$venueList = $_SESSION["venueList"];
				ChromePhp::log("Using session venue list.");
			}
			else {
				$venueList = $databaseManager->loadVenues($venueType);
			}

			if (isset($_SESSION["venueTypeList"])) {
				$venueTypeList = $_SESSION["venueTypeList"];
				ChromePhp::log("Using session venue type list.");
			}
			else {
				$venueTypeList = $databaseManager->loadVenueTypes();
			}

			// Check for a review submission
			if (isset($_POST["reviewRating"])) {
				$title = $_POST["reviewSummary"];
				$body = $_POST["reviewBody"];
				$rating = $_POST["reviewRating"];

				ChromePhp::log("POSTed a new review - $title / $body / $rating");
				$newRevue = new Review($title, $body, $rating, date("Y-m-d H:i:s"));
				
				$saveResult = $databaseManager->saveReview($venueList[$venueID], $newRevue);

				if ($saveResult == "OK") {
					// Saved it to the database, now add it to the session
					$venueList[$venueID]->addReview($newRevue);
				}
				header("Location: index.php");
			}

			// Create the sidenav
			echo '<nav class="w3-sidenav w3-light-grey" style="width:25%">';
			
			createMenuBar($venueID);

			if ($venueID == -1) {
				createVenueList($venueList, $venueTypeList, $venueType);
				echo '</nav>';
				createHeader(NULL);
			}
			else {
				$venueTypeName = strtolower($venueTypeList[$venueType]);

				createReviewList($venueList[$venueID], $venueTypeName);
				echo '</nav>';
				createModal_WriteReview($venueList[$venueID], $venueTypeName);

				if ($displayReview != -1) {
					createModal_DisplayReview($venueList[$venueID]->getReview($displayReview));
				}
				createHeader($venueList[$venueID]);
			}

			// createMap($venueList[$venueID]);

			// Save the session
			ChromePhp::log("Saving session...");
			$_SESSION["databaseManager"] = $databaseManager;
			$_SESSION["venueType"] = $venueType;
			$_SESSION["venueTypeList"] = $venueTypeList;
			$_SESSION["venueID"] = $venueID;
			$_SESSION["venueList"] = $venueList;
			session_write_close();
			ChromePhp::log("Session saved.");

			/*
			 Functions
			 */

			/**
			 * Creates the side navigation's menu bar.
			 **/
			function createMenuBar($venueID) {
				// Create the menu bar
				echo '<div class="w3-padding w3-xxlarge w3-black">
						<button class="w3-btn w3-black w3-xlarge w3-hover-text-blue" onclick="swapVenue(-1)">
							<i class="fa fa-home"></i>
						</button>';

				if ($venueID != -1) {
					echo '<button class="w3-btn w3-black w3-xlarge w3-hover-text-blue" onclick="show(\'writeReviewModal\')">
							<i class="fa fa-commenting-o"></i>
						</button>
						<button class="w3-btn w3-black w3-xlarge w3-hover-text-blue">
							<i class="fa fa-map-marker"></i>
						</button>';
				}
				echo '</div>';
			}

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
			 * Crates the application's header based on the current venue.
			 **/
			function createHeader($venue) {
				echo '<div class="w3-container" style="margin-left:25% ">';
				
				if ($venue == NULL) {
				}
				else {
					echo '<section class="w3-container" width:20%>
							<i class="fa fa-phone w3-xlarge w3-left w3-top">
								<span class="w3-container w3-section">' . $venue->getTelephoneNumber() . '</span>
							</i>
							<i class="fa fa-globe w3-xlarge w3-left w3-bottom">
								<a href="http://www.' . $venue->getWebsite() . '" target="_blank" class="w3-container w3-section">www.' . $venue->getWebsite() . '</a>
							</i>
						   </section>';
				}
				echo '</<div>';
			}

			/**
			 * Creates the 'write review' modal.
			 **/
			function createModal_WriteReview($venue, $venueTypeName) {
				echo '
				<div id="writeReviewModal" class="w3-modal">
				  <div class="w3-modal-content w3-card-8 w3-animate-zoom" style="max-height:800px max-width:600px">
				  <span onclick="hide(\'writeReviewModal\')" class="w3-closebtn w3-container w3-padding-hor-16 w3-display-topright">&times;</span>
  
					<div class="w3-center w3-hover-none"><br>
					  <h2><b>' . $venue->getName() . '</b></h2>
					  <span>What did you think of this ' . $venueTypeName . '?</span>
					</div>
					<div class="w3-content w3-section w3-center" w3-padding>';

					  // Create the stars
					  for ($i = 1; $i != 6; $i++) {
						  echo '<a href="#"><img id="star_' . $i . '" src="img/rating/nostar.png"
								 onclick="setRating(' . $i . ')"
								 onmouseover="displayRating(' . $i . ')"
								 onmouseout="clearDisplayRating()"></a>';
					  }
					  
					  echo '
					</div>

					<div class="w3-container">
					  <div class="w3-section">

						<input id="reviewSummary" class="w3-input w3-border w3-margin-bottom" type="text" placeholder="Brief summary">
						<textarea id="reviewBody" rows="4" class="w3-input w3-border" type="text" placeholder="Why did you give this ' . $venueTypeName . ' the rating you did?"></textarea>
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
			
			/**
			 * Creates the 'display review' modal.
			 **/
			function createModal_DisplayReview($review) {
				ChromePhp::log("Creating display modal.");
				echo '
				<div id="displayReviewModal" class="w3-modal" style="display: block">
				  <div class="w3-modal-content w3-card-8 w3-animate-zoom" style="max-width:600px">
				    <span onclick="hide(\'displayReviewModal\')" class="w3-closebtn w3-container w3-padding-hor-16 w3-display-topright">&times;</span>
					 
					  <div class="w3-center w3-padding-large">
						<h2><b>\'' . $review->getTitle() . '\'</b></h2>
						<div class="w3-container w3-border w3-padding-small w3-round-xlarge">
						  <span class="w3-left">' . $review->getBody() . '</span>
						</div>

					    <div class="w3-content w3-section w3-center w3-padding-large">';

							// Create the stars
							for ($i = 1; $i != 6; $i++) {
								 echo '<img id="star_' . $i . '" src="img/rating/' . ($i == 0 || $i > $review->getRating() ? "no" : "") . 'star.png">';
							}

						// strtotime: found at https://stackoverflow.com/questions/2588998/numerical-date-to-text-date-php
					 	echo '
						<div class="w3-section w3-center w3-small">
						  <span>Written ' . date('F jS Y', strtotime($review->getDate())) . '</span>
					    </div>
					  </div>
					</div>
				  </div>
				</div>';
				ChromePhp::log("Creating display modal.");
			}

			/**
			 * Creates a list of venues in the navigation bar, based on the specified venue.
			 **/
			function createReviewList($venue, $venueTypeName) {
				$reviewList = $venue->getReviewList();
				echo '<div class="w3-container w3-section w3-light-grey" >
						 <h3 class="w3-center"><b>Reviews</b></h3>
					  </div>';

				if (count($reviewList) == 0) {
					echo '<ul href="#" class="w3-ul w3-container w3-section">';
					echo '<li><span>There are no reviews for this ' . $venueTypeName . '!
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
				
						echo '
						<li>
							<a href="#" onclick="showReview(\'' . $i . '\')" style="fill_div" class="w3-hover-none w3-hover-text-white">
								<span class="w3-large">
								<img src="img/rating/' . $rating . '.png" class="w3-right" style="width:25%">
								' . $title . '
								</span><br>
								' . $snippet . '
							</a>
						</li>';
					}
					echo '</ul>';
				}
			}

			/**
			 * Creates a list of venues in the navigation bar.
			 **/
			function createVenueList($venueList, $venueTypeList, $venueType) {
				ChromePhp::log("Creating venue list.");

				echo '<div class="w3-container w3-section">
						 <li class="w3-dropdown-hover w3-light-grey">
							<h3><a href="#" class="w3-center"><b>' . $venueTypeList[$venueType] . 's</b></a></h3>

							  <div class="w3-dropdown-content w3-border">';

							  foreach ($venueTypeList as $key => $value) {
								  echo '<a href="#" onclick="swapVenueType(\'' . $key . '\')">' . $value . 's</a>';
							  }
						  echo '</div>
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
		?>
    </body>
</html>