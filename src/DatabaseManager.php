<?php
	/**
	 * Handles database connection and operations.
	 **/
	class DatabaseManager {
		
		// define("HOST", "localhost");

		const HOST = "localhost";
		const DB_NAME = "venues";
		const DB_USER = "root";
		const DB_PASSWORD = "1963nfZ95F";

		private $dbConnection;
		
		/**
		 * Establishes a connection to the database.
		 **/
		function connect() {
			try {
				$this->dbConnection = new PDO("mysql:host=" . self::HOST . ";dbname=" . self::DB_NAME, self::DB_USER, self::DB_PASSWORD);
				$this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Throw an exception if something cocks up
				$this->dbConnection->setAttribute(PDO::ATTR_PERSISTENT, TRUE);				   // Keep the connection around
				return TRUE;
			}
			catch(PDOException $e) {
				return $e->getMessage();
			}
		}

		/**
		 * Disconnects from the database.
		 **/
		function disconnect() {
			$this->dbConnection = NULL;
		}

		/**
		 * Loads every 'type' of venue supported by the application, as keypairs.
		 * This is done for extensibility, to stop violating Open/Closed.
		 **/
		function loadVenueTypes() {
			ChromePhp::log("Loading venue types.");
			$this->connect();

			// Load the appropriate list - no user input, hence not a prepared statement
			$typeResults = $this->dbConnection->query("SELECT * FROM Venues.VenueType;");
			$typeResults->setFetchMode(PDO::FETCH_ASSOC);

 			$typeArray = array();

			while($row = ($typeResults->fetch())) {
				$typeArray[$row["TypeID"]] = $row["Description"];
			}
			ChromePhp::log("Loaded " . count($typeArray) . " venue types.");
			$this->disconnect();
			return $typeArray;
		}

		/**
		 * Loads a list of venues of the specified type.
		 **/
		function loadVenues($venueType) {
			ChromePhp::log("Loading venues of type '$venueType'.");
			$this->connect();

			// Load the appropriate list - no user input, hence not a prepared statement
			$venueResults = $this->dbConnection->query("SELECT * FROM Venues.Venues WHERE TypeID = '$venueType' ORDER BY AverageRating DESC;");
			$venueResults->setFetchMode(PDO::FETCH_ASSOC); // Fetch to an associative array
 
 			$venueList = array();

			while($venueRow = ($venueResults->fetch())) {
				$venueID = $venueRow["VenueID"];
				$venue = new Venue($venueID, $venueRow["TypeID"], $venueRow["Name"], $venueRow["Address"], $venueRow["Postcode"],
								   $venueRow["Website"], $venueRow["Telephone"], $venueRow["AverageRating"]);

				$venueList[] = $venue;
				$venue->setReviews($this->loadReviews($venue));
			}
			ChromePhp::log("Loaded " . count($venueList) . " venues.");
			$this->disconnect();
			return $venueList;
		}

		/**
		 * Queries and returns an array of reviews for the specified venue, ordered by rating (descending).
		 * This is the only method in this class that expects a connection to exist, and doesn't disconnect when finished.
		 * It should only ever be called during an active connection.
		 **/
		function loadReviews($venue) {
			if ($this->dbConnection == NULL) {
				PhpChrome::log("dbConnection cannot be null when loading reviews!");
				return NULL;
			}

			$venueID = $venue->getID();

			$reviewList = array();
			$reviewResults = $this->dbConnection->query("SELECT * FROM Venues.Reviews WHERE VenueID = '$venueID' ORDER BY StarRating DESC;");
			$reviewResults->setFetchMode(PDO::FETCH_ASSOC);
	
			while($reviewRow = ($reviewResults->fetch())) {
				$reviewList[] = new Review($reviewRow["ReviewTitle"], $reviewRow["ReviewBody"],
										   $reviewRow["StarRating"], $reviewRow["ReviewDate"]);
			}

			if (count($reviewList) > 0) {
				ChromePhp::log("Loaded " . count($reviewList) . " reviews for '" . $venue->getName() . "'.");
			}
			else {
				ChromePhp::log("Venue has no reviews.");
			}
			return $reviewList;
		}

		/**
		 * Queries the database to retrieve the total number of reviews, so we can get the primary key.
		 **/
		private function getReviewID() {
			$results = $this->dbConnection->query("SELECT COUNT(*) FROM Venues.Reviews;");
			return $results->fetchColumn();
		}

		/**
		 * Saves the specified review data to the database.
		 **/
		function saveReview($venue, $review) {
			$failed = FALSE;

			$this->connect();
			$this->dbConnection->beginTransaction();

			$storeReview = $this->storeReview($venue, $review);

			if ($storeReview == "OK") {
				$storeRating = $this->storeRating($venue);
				
				if ($storeRating == "OK") {
				}
				else {
					$failed = $storeRating;
				}
			}
			else {
				$failed = $storeReview;
			}

			if ($failed) {
				$this->dbConnection->rollBack();
			}
			else {
				$this->dbConnection->commit();
				$venue->setReviews($this->loadReviews($venue)); // Re-load reviews - lazy man's ordering.
			}

			$this->disconnect();
			return ($failed ? $failed : "OK");
		}

		private function storeReview($venue, $review) {
			$reviewID = $this->getReviewID();
			$venueID = $venue->getID();
			
			$fields = "(ReviewID, VenueID, ReviewTitle, ReviewBody, ReviewDate, StarRating)";
			$statement = "INSERT INTO Venues.Reviews $fields VALUES ($reviewID, $venueID, :ReviewTitle, :ReviewBody, :ReviewDate, " . $review->getRating() . ");"; // ($reviewID, $venueID, , ?, ?, ?);";

			// Use a prepared statement to prevent susceptibility to SQL injection
			$prep = $this->dbConnection->prepare($statement);
			
			$title = $review->getTitle();
			$body = $review->getBody();
			$date = $review->getDate();

			$prep->bindParam(':ReviewTitle', $title);
			$prep->bindParam(':ReviewBody', $body);
			$prep->bindParam(':ReviewDate', $date);

			try {
				$prep->execute();
				return "OK";
			}
			catch(Exception $e) {
				ChromePhp::log("Couldn't save review: $e.");
				return $e->getMessage();
			}
		}

		private function storeRating($venue) {
			try {
				$this->dbConnection->query("UPDATE Venues.Venues SET AverageRating = '" . $venue->getAverageRating() . "' WHERE VenueID = '" . $venue->getID() . "';");
				return "OK";
			}
			catch(Exception $e) {
				ChromePhp::log("Couldn't update average rating: $e.");
				return $e->getMessage();
			}
		}
	}
?>