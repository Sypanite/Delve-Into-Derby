<?php
	// Debug purposes - clear the session.
	session_start();
	$_SESSION = array();
	session_write_close();
	header("Location: index.php");
?>