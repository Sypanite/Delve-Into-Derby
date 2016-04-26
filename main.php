<?php
	require "Venue.php";	

    // Database constants
	// $dbDomain = "13.95.150.76";
	$dbHost = "localhost";
	$dbName = "venues";
    $dbUser = "root";
    $dbPassword = "1963nfZ95F";
	
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
	$_POST["placeType"] = "R";
	//

	if (!array_key_exists("placeType", $_GET)) {
		echo "<p>No place type specified.</p>";
	}

	$placeType = $_GET["placeType"];

	// Check if we've loaded the appropriate places before - there's no point loading them repeatedly
	if (!isset($_SESSION["places_$placeType"])) {
		echo "<p>Loading venues of type $placeType...</p>";
		// No user input, no need for prepared statements
		$results = mysql_query("", $dbConnection);
		
		// Load the appropriate list
		if ($results) {
			$STH = $DBH->query("SELECT * FROM venues.venues WHERE TypeID = '$typeID';");
			$STH->setFetchMode(PDO::FETCH_ASSOC); // Fetch to an associative array
 
 			$venues = array();

			while($row = $STH->fetch()) {
				echo $row['name'] . "\n";
				echo $row['addr'] . "\n";
				echo $row['city'] . "\n";

				/*
				
				VenueID			SMALLINT			PRIMARY KEY,
				TypeID			CHAR				NOT NULL,
				Name			VARCHAR(32)			NOT NULL,
				Address			VARCHAR(128)		NOT NULL,
				Postcode		VARCHAR(8)			NOT NULL,
				*/
			}
		}
	}
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
