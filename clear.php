<!--
<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
	    <link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css">
		<title>Delve Into Derby</title>
	</head>
	<body>
		<span>Loading...</span>
		<div class="w3-progress-container">
			<div id="myBar" class="w3-progressbar w3-grey w3-centre" style="width:1%" onload="progress()"></div>
		</div>
	</body>
</html>
-->
<?php
	// Clear the session.
	session_start();
	$_SESSION = array();
	session_write_close();
	
	// Redirect to index.
	header("Location: index.php");
?>