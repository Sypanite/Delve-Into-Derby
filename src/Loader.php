<?php
    require "src/Venue.php";
    require "src/Review.php";
    session_start();

    // Database constants
	// $dbDomain = "13.95.150.76";
	$dbHost = "localhost";
	$dbName = "venues";
    $dbUser = "root";
    $dbPassword = "1963nfZ95F";
	$dbConnection;

	try {
		$dbConnection = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
		$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Throw an exception if something cocks up
		echo "<p>Successfully to established a connection to the database.</p>";
	}
	catch(PDOException $e) {
        echo "<p>Failed to establish a connection to the database: ";
		echo $e->getMessage();
		echo "</p>";
		return;
	}

	// Debug
	$_SESSION["venueType"] = "R";			// Set to restaurants
	unset($_SESSION["venues_$placeType"]);	// Delete existing
	//

	if (!array_key_exists("venueType", $_SESSION)) {
		echo "<p>No venue type specified.</p>";
		return;
	}

	$venueType = $_SESSION["venueType"];
	echo "<p>Venue type: $venueType.</p>";

	// Check if we've loaded the appropriate places before - there's no point loading them repeatedly
	if (!isset($_SESSION["venues_$placeType"])) {
		echo "<p>Loading venues of type $venueType.</p>";
		
		// Load the appropriate list - no user input, hence not a prepared statement
		$venueResults = $dbConnection->query("SELECT * FROM Venues.Venues WHERE TypeID = '$venueType';");
		$venueResults->setFetchMode(PDO::FETCH_ASSOC); // Fetch to an associative array
 
 		$venueList = array();

		while($venueRow = ($venueResults->fetch())) {
			echo "<p>";
			$venueID = $venueRow["VenueID"];
			$venue = new Venue($venueID, $venueRow["TypeID"], $venueRow["Name"],
							   $venueRow["Address"], $venueRow["Postcode"], $venueRow["Website"], $venueRow["Telephone"]);
			$venueList[] = $venue;
			echo var_dump($venue)."<br>";
			
			// Load the reviews for this venue into an array
			$reviewList = array();
			$reviewResults = $dbConnection->query("SELECT * FROM Venues.Reviews WHERE VenueID = '$venueID';");
			$reviewResults->setFetchMode(PDO::FETCH_ASSOC);
			
			while($reviewRow = ($reviewResults->fetch())) {
				$reviewList[] = new Review($reviewRow["ReviewTitle"], $reviewRow["ReviewBody"],
										   $reviewRow["ReviewRating"], $reviewRow["ReviewDate"]); 
			}
			$venue->setReviews($reviewList);

			$venueReviewList = $venue->getReviewList();
			echo var_dump($venueReviewList)."<br>";
			echo "</p>";
		}
		$_SESSION["venues_$placeType"] = $venueList;
	}
	echo "<p>Done.</p>";
	session_commit();
?>