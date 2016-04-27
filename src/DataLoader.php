<?php
    require "src/Venue.php";
    require "src/Review.php";
	require "src/DatabaseManager.php";
	
	$databaseManager;
	
	// Debug
	 unset($_SESSION["databaseManager"]);
	// unset($_SESSION["venues_$placeType"]);
	//

	if (!array_key_exists("databaseManager", $_SESSION)) {
		$databaseManager = new DatabaseManager();
		$_SESSION["databaseManager"] = $databaseManager;

		$connectAttempt = $databaseManager->connect();

		if ($connectAttempt == "TRUE") {
			echo "<p>Successfully established a connection to the database.</p>";
		}
		else {
			echo "<p>Failed to establish a connection to the database: $connectAttempt</p>";
		}
	}
	else {
		echo "<p>Using session database instance.</p>";
		$databaseManager = $_SESSION["databaseManager"];
	}

	// Debug
	$_SESSION["venueType"] = "R";			// Set to restaurants
	//
	
	// Retrieved via GET
	if (!array_key_exists("venueType", $_SESSION)) {
		echo "<p>No venue type specified.</p>";
		return;
	}

	$venueType = $_SESSION["venueType"];

	// Check if we've loaded the appropriate venues before - there's no point loading them repeatedly
	if (!array_key_exists("venues_$venueType", $_SESSION)) {
		echo "254<br>";
		echo var_dump($databaseManager) . "<br>";
		$venues = $databaseManager->loadVenues($venueType);
		echo "255<br>";

		$_SESSION["venues_$venueType"] = $venues;
		echo var_dump($_SESSION["venues_$venueType"]) . "<br>";
		echo "<p>Loaded venues.</p>";
	}
	else {
		echo "<p>Using session venue list.</p>";
	}
?>