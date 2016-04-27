<?php
	session_start();
    require "src/DataLoader.php";

	$databaseManager;

	if (!array_key_exists("databaseManager", $_SESSION)) {
		echo "<p>NO DATABASE MANAGER!</p>";
		return;
	}
	else {
		// echo "<p>Database manager exists: ";
		$databaseManager = $_SESSION["databaseManager"];
		// echo var_dump($databaseManager) . "<br>";
	}
	
	// $databaseManager->saveReview(0, "Meh", "This is a test. Food was all right.", 4);
	session_write_close();
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
