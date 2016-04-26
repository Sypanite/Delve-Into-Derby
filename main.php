<?php
    require "Venue.inc";
    // require "Review.inc";
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
		echo "<p>Loading venues of type $venueType...</p>";
		
		// Load the appropriate list
		$results = $dbConnection->query("SELECT * FROM Venues.Venues WHERE TypeID = '$venueType';");
		$results->setFetchMode(PDO::FETCH_ASSOC); // Fetch to an associative array
 
 		$venues = array();

		while($row = ($results->fetch())) {			
			$venues[] = new Venue($row["VenueID"], $row["TypeID"], $row["Name"],
								  $row["Address"], $row["Postcode"], $row["Website"], $row["Telephone"]);

			echo var_dump(end($venues))."<br>";
		}
		$_SESSION["venues_$placeType"] = $venues;
	}
	session_commit();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Test</title>
    </head>
    <body>
        
    </body>
</html>
