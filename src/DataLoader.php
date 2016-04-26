<?php
    require "src/Venue.php";
    require "src/Review.php";
	require "src/DatabaseManager.php";
    session_start();
	
	// Debug
	unset($_SESSION["databaseManager"]);	// Delete existing
	//

	if (!array_key_exists("databaseManager", $_SESSION)) {
		$_SESSION["databaseManager"] = DatabaseManager::create();
		
		$connectAttempt = $_SESSION["databaseManager"]->connect();

		if ($connectAttempt == "TRUE") {
			echo "<p>Successfully to established a connection to the database.</p>";
		}
		else {
			echo "<p>Failed to establish a connection to the database: $connectAttempt</p>";
		}
	}

	$databaseManager = $_SESSION["databaseManager"];

	// Debug
	$_SESSION["venueType"] = "R";			// Set to restaurants
	unset($_SESSION["venues_$placeType"]);	// Delete existing
	//

	if (!array_key_exists("venueType", $_SESSION)) {
		echo "<p>No venue type specified.</p>";
		return;
	}

	$venueType = $_SESSION["venueType"];
	
	// Check if we've loaded the appropriate venues before - there's no point loading them repeatedly
	if (!isset($_SESSION["venues_$venueType"])) {
		$_SESSION["venues_$venueType"] = $databaseManager->loadVenues($venueType);
	}
?>