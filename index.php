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
		<script src="js/map.js"></script>
    </head>
    <body>
		<?php

			$databaseManager = "N/A";
			$venueType = NULL;
			$venueID = NULL;
			$venueList = NULL;
			$displayReview = -1;
			$venueTypeList = NULL;
			$latitude = 0;
			$longitude = 0;

			// $_SESSION = array(); // Clear session

			ChromePhp::log("_POST contents (" . count($_POST) . " values):\n" . var_export($_POST, true));
			ChromePhp::log("Ready to load session. _SESSION contains " . count($_SESSION) . " values.");

			/*
			 * Check $_POST for incoming data, and load the session.
			 */
			
			// Check for a request to display a review
			if (isset($_POST["displayReview"])) {
				$_SESSION["displayReview"] = $_POST["displayReview"];
				ChromePhp::log("Display review request: " . $_SESSION["displayReview"]);
				session_write_close();
				header("Location: index.php");
				exit();
			}
			else if (isset($_SESSION["displayReview"])) {
				$displayReview = $_SESSION["displayReview"];
				ChromePhp::log("Stored display request: $displayReview");
			}

			// Check for latitude/longitude
			if (isset($_POST["latitude"])) {
				$latitude = $_POST["latitude"];
				$longitude = $_POST["longitude"];
				ChromePhp::log("Reveived latitude/longitude: $latitude, $longitude");
			}
			else if (isset($_SESSION["latitude"])) {
				$latitude = $_SESSION["latitude"];
				$longitude = $_SESSION["longitude"];
				ChromePhp::log("Stored latitude/longitude request: $latitude, $longitude");
			}
			
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
			
			// Check for the venue list
			if (isset($_SESSION["venueList"])) {
				$venueList = $_SESSION["venueList"];
				ChromePhp::log("Using session venue list.");
			}
			else {
				$venueList = $databaseManager->loadVenues($venueType);
			}

			// Check for the venue type list
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
				
				$_SESSION["reviewLeft"] = $databaseManager->saveReview($venueList[$venueID], $newRevue);
				header("Location: index.php");
				exit();
			}
			
			if (isset($_SESSION["reviewLeft"])) {
				ChromePhp::log("Review was left: '" . $_SESSION["reviewLeft"] . "'.");
				createModal_ReviewConfirmed($venueList[$venueID], strtolower($venueTypeList[$venueType]), $_SESSION["reviewLeft"]);
				unset($_SESSION["reviewLeft"]);
			}

			// Create the sidenav
			echo '<nav id="sidenav" class="w3-sidenav w3-light-grey w3-border-right" style="width:25%">';
			
			createMenuBar($venueID);

			if ($venueID == -1) {
				createVenueList($venueList, $venueTypeList, $venueType);
				echo '</nav>';
				createHeader(NULL);
				createMap(NULL, $latitude, $longitude);
			}
			else {
				$venueTypeName = strtolower($venueTypeList[$venueType]);

				createReviewList($venueList[$venueID], $venueTypeName);
				echo '</nav>';
				createModal_WriteReview($venueList[$venueID], $venueTypeName);

				if ($displayReview != -1) {
					createModal_DisplayReview($venueList[$venueID]->getReview($displayReview));
					$displayReview = -1;
				}
				createHeader($venueList[$venueID]);
				createMap($venueList[$venueID], $latitude, $longitude);
			}

			// Save the session
			ChromePhp::log("Saving session...");
			$_SESSION["databaseManager"] = $databaseManager;
			$_SESSION["venueType"] = $venueType;
			$_SESSION["venueTypeList"] = $venueTypeList;
			$_SESSION["venueID"] = $venueID;
			$_SESSION["venueList"] = $venueList;
			$_SESSION["displayReview"] = $displayReview;

			if ($latitude != 0) {
				$_SESSION["latitude"] = $latitude;
				$_SESSION["longitude"] = $longitude;
			}
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
					echo '<button class="w3-btn w3-black w3-xlarge w3-hover-text-blue" onclick="show(\'writeReviewModal\') w3-tooltip">
							<i class="fa fa-commenting-o"></i>
						</button>';
					/*
						<button class="w3-btn w3-black w3-xlarge w3-hover-text-blue" onclick="mapDirections()">
							<i class="fa fa-map-marker"></i>
						</button>
					*/
				}
				echo '</div>';
			}

			/**
			 * Loads and displays the Google Maps map.
			 **/
			function createMap($venue, $userLatitude, $userLongitude) {
				$latitude;
				$longitude;
				$label;

				if ($venue != NULL) {
					// Venue's lat/long
					$latitude = $venue->getLatitude();
					$longitude = $venue->getLongitude();
					$label = $venue->getName();
				}
				else {
					// Derby's lat/long
					$latitude = 52.92253;
					$longitude = -1.474619;
					$label = "Derby";
				}

				echo '
					<div id="latLong" name="' . $latitude . ',' . $longitude . '" style="display:none;"></div>
					<div id="venueName" name="' . $label . '" style="display:none;"></div>
				';

					if ($userLatitude != 0) {
						echo '<div id="userLatLong" name="' . $userLatitude . ',' . $userLongitude . '" style="display:none;"></div>';
					}

				echo '
					<div class="w3-white" style="margin-left:25%">
						<div id="map" class="w3-container w3-margin w3-center w3-display-container w3-black w3-padding-medium"
									  style="position: relative; overflow: hidden; padding: 0; float: inherit; height: ' . getMapHeight($venue). 'px; width: 97.65%;">
							<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCe4BB7jH_lBh7vMj0xJrvh8vivkhJNwj0&callback=initMap" async defer></script>
						</div>
					</div>
				';
			}

			/**
			 * Returns the height, in pixels, of the Google map based on whether or not a venue is
			 * displayed. This is a total hack and completely goes against my intention of keeping
			 * things responsive, but I cannot get height to respond.
			 **/
			function getMapHeight($venue) {
				return ($venue == NULL ? 800 : 770);				
			}

			/**
			 * Crates the application's header based on the current venue.
			 **/
			function createHeader($venue) {
				if ($venue == NULL) {
					echo '
						<div class="w3-white" style="margin-left:25%">
							<div class="w3-row w3-margin w3-center w3-black w3-padding-small">
								<img class="w3-row" src="img/header.png">
							</div>
						</div>';
				}
				else {
					echo '
						<div class="w3-white" style="margin-left:25%">
							<div class="w3-container w3-margin w3-center w3-display-container w3-black w3-padding-medium">
								<div class="w3-column w3-left">
									<div class="w3-row">
										<h2 class="w3-left"><b>' . $venue->getName() . '</b></h2>
									</div>

									<div class="w3-row">
										<i class="fa fa-map w3-medium w3-text-white">
											<span class="w3-padding-ver-4 w3-large w3-slim">' . $venue->getAddress() . ', Derby, ' . $venue->getPostcode() . '</span>
										</i>
									</div>
								
									<div class="w3-row">
										<i class="fa fa-phone w3-large w3-text-white w3-left">
											<span class="w3-padding-ver-4 w3-slim"> ' . $venue->getTelephoneNumber() . '</span>
										</i>
									</div>
								
									<div class="w3-row">
										<i class="fa fa-globe w3-large w3-text-white w3-left">
											<a class="w3-padding-ver-4 w3-slim w3-container w3-section"
													  href="http://www.' . $venue->getWebsite() . '" target="_blank"> www.' . $venue->getWebsite() . '
											</a>
										</i>
									</div>
								</div>
							
								<div class="w3-column w3-section w3-right">
									<div class="w3-row w3-right">
										<div id="ratingContainer" class="w3-row w3-center">
					';
											// Create the stars
											for ($i = 1; $i != 6; $i++) {
												echo '<img id="star_' . $i . '" src="img/rating/' . ($i > $venue->getAverageRating() ? "no" : "") . 'star.png">';
											}
											// <canvas id="starCanvas" width="180" height="30" onload="fillStars(\'' . $venue->getAverageRating() . '\')"></canvas>
					echo '
										</div>
										<div class="w3-row w3-center">
											<span class="w3-color-white w3-small"><b>' . ($venue->getReviewCount() == 0 ? "No" : $venue->getReviewCount()) . ' reviews</b></span>						
										</div>
									</div>
								</div>

								<div class="w3-display-bottomright">
									<div class="w3-padding w3-right" style="width:75%">
										<p class="w3-color-white w3-medium w3-right-align">' . $venue->getDescription() . '</p>						
									</div>
								</div>
							</div>
						</div>
					';
				}
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
						
							<div class="w3-content w3-section w3-center" w3-padding>
				';
								// Create the stars
								for ($i = 1; $i != 6; $i++) {
				echo '
									<a href="#"><img id="star_' . $i . '" src="img/rating/nostar.png"
									 onclick="setRating(' . $i . ')"
									 onmouseover="displayRating(' . $i . ')"
									 onmouseout="clearDisplayRating()"></a>
				';
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
					</div>
				';
			}

			/**
			 * Creates the 'review confirmed' modal.
			 **/

			/**
			 * Creates the 'review confirmed' modal.
			 **/
			function createModal_ReviewConfirmed($venue, $venueTypeName, $result) {
				echo '
				<div id="confirmReviewModal" class="w3-modal" style="display: block">
					<div class="w3-modal-content w3-card-8 w3-animate-zoom" style="max-width:600px">
						<span onclick="hide(\'confirmReviewModal\')" class="w3-closebtn w3-container w3-padding-hor-16 w3-display-topright">&times;</span>
						 
						<div class="w3-center w3-padding-large">
							';

							if ($result == "OK") {
				echo '
								<h2>
									<b>Thank you!</b>
								</h2>
								<div class="w3-section w3-margin-top w3-padding-small w3-round-xlarge">
									<span>Thank you for taking the time to tell us what you thought. Your feedback will help other patrons in their ' . $venueTypeName . '-related endeavours.</span>
								</div>
				';
							}
							else {
				echo '
								<h2>
									<b>Oh-oh!</b>
								</h2>
								<div class="w3-section w3-margin-top w3-padding-small w3-round-xlarge">
									<span>Well, this is embarrassing - something went wrong whilst saving your review. Please try again later.</span>
								</div>
				';
							}
				echo '
						</div>
					</div>
				</div>
				';
			}
			
			/**
			 * Creates the 'display review' modal.
			 **/
			function createModal_DisplayReview($review) {
				echo '
				<div id="displayReviewModal" class="w3-modal" style="display: block">
					<div class="w3-modal-content w3-card-8 w3-animate-zoom" style="max-width:600px">
						<span onclick="hide(\'displayReviewModal\')" class="w3-closebtn w3-container w3-padding-hor-16 w3-display-topright">&times;</span>
						 
						<div class="w3-center w3-padding-large">
							<h2>
								<b>' . $review->getTitle() . '</b>
							</h2>
							
							<div class="w3-container">';
								// Create the stars
								for ($i = 1; $i != 6; $i++) {
									echo '<img id="star_' . $i . '" src="img/rating/' . ($i > $review->getRating() ? "no" : "") . 'star.png">';
								}
							// strtotime: found at https://stackoverflow.com/questions/2588998/numerical-date-to-text-date-php
							echo '
							</div>

							<div class="w3-container w3-border w3-margin-top w3-padding-small w3-round-xlarge">
								<span class="w3-left">' . $review->getBody() . '</span>
							</div>

							<div class="w3-section w3-center w3-small">
								<span>Written ' . date('F jS Y', strtotime($review->getDate())) . '</span>
							</div>
						</div>
					</div>
				</div>';
			}

			/**
			 * Creates a list of venues in the navigation bar, based on the specified venue.
			 **/
			function createReviewList($venue, $venueTypeName) {
				$reviewList = $venue->getReviewList();
				echo '
					<div class="w3-container w3-section w3-light-grey" >
						<h3 class="w3-center"><b>Reviews</b></h3>
					</div>
				';

				if (count($reviewList) == 0) {
					// <a href="#" style="normal-link">add one</a>?</span></li>

					echo '
						<ul href="#" class="w3-ul w3-container w3-section w3-center">
							<li><span>There are no reviews for this ' . $venueTypeName . '! Why not add one?</span></li>
						</ul>
					';
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
								<a href="#" onclick="showReview(\'' . $i . '\')" style="fill_div" class="w3-hover-none">
									<span class="w3-large">
									<img src="img/rating/' . $rating . '.png" class="w3-right" style="width:25%">
									' . $title . '
									</span><br>
									' . $snippet . '
								</a>
							</li>
						';
					}
					echo '</ul>';
				}
			}

			/**
			 * Creates a list of venues in the navigation bar.
			 **/
			function createVenueList($venueList, $venueTypeList, $venueType) {
				echo '
				<div class="w3-container w3-section border-bottom w3-border-dark-gray">
					<li class="w3-dropdown-hover">
						<h3><a href="#" class="w3-center w3-border"><b>' . $venueTypeList[$venueType] . 's</b></a></h3>
						<div class="w3-dropdown-content w3-border">';
							foreach ($venueTypeList as $key => $value) {
								echo '<a class="w3-hover-blue" href="#" onclick="swapVenueType(\'' . $key . '\')">' . $value . 's</a>';
							}
				echo '	</div>
					</li>
				</div>
				<ul href="#" class="w3-ul w3-hoverable w3-container w3-section">';
					// Add each venue to the list
					for ($i = 0; $i != count($venueList); $i++) {
						// $id = $venueList[$i]->getID();
						$name = $venueList[$i]->getName();
						$address = $venueList[$i]->getAddress();
						$postcode = $venueList[$i]->getPostcode();
						$rating = $venueList[$i]->getAverageRating();
				
						echo '
						<li>
							<a href="#" onclick="swapVenue(\'' . $i . '\')" style="fill_div" class="w3-hover-none">
								<div class="w3-large">
									<img src="img/rating/' . $rating . '.png" class="w3-right" style="width:25%">
									' . $name . '<br>';
								//	<span class="w3-medium">' . $address . ', Derby, ' . $postcode . '</span>
						echo '
									<span class="w3-medium">' . $venueList[$i]->getListNote() . '</span>
								</div>
							</a>
						</li>';
					}
			echo '
				</ul>';
			}
		?>
    </body>
</html>