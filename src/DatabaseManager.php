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
		 * Disconnects from the database, after rolling back any open transactions.
		 **/
		function disconnect() {
			$this->dbConnection->rollBack();
			$this->dbConnection = NULL;
		}

		/*
		 * Loads a list of venues of the specified type.
		 */
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

				// Load the reviews for this venue into an array
				$reviewList = array();
				$reviewResults = $this->dbConnection->query("SELECT * FROM Venues.Reviews WHERE VenueID = '$venueID';");
				$reviewResults->setFetchMode(PDO::FETCH_ASSOC);
			
				while($reviewRow = ($reviewResults->fetch())) {
					$reviewList[] = new Review($reviewRow["ReviewTitle"], $reviewRow["ReviewBody"],
											   $reviewRow["StarRating"], $reviewRow["ReviewDate"]);
				}
				$venue->setReviews($reviewList);

				if (count($reviewList) > 0) {
					ChromePhp::log("Loaded " . count($reviewList) . " reviews for '" . $venue->getName() . "'.");
				}
				else {
					ChromePhp::log("Venue has no reviews.");
				}
			}
			ChromePhp::log("Loaded " . count($venueList) . " venues.");
			$this->disconnect();
			return $venueList;
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
		 * TODO: Add the new review to the venue's list.
		 **/
		function saveReview($venue, $review) {
			$this->connect();
			$this->dbConnection->beginTransaction();
			$failed = FALSE;

			if (storeReview($venue, $review) == "OK"
			 && storeRating($venue, $review) == "OK") { // Might be easier just throwing an exception
				$this->dbConnection->commit();
			 }
			 else {
				$this->dbConnection->rollBack();
				$failed = TRUE;
			 }
			 
			disconnect();
			return ($failed ? "OK" : "Failure");
		}

		private function storeReview($venue, $review) {
			$reviewID = $this->getReviewID();
			$venueID = $venue->getID();
			
			$fields = "(ReviewID, VenueID, ReviewTitle, ReviewBody, ReviewDate, StarRating)";
			$statement = "INSERT INTO Venues.Reviews $fields VALUES ($reviewID, $venueID, :ReviewTitle, :ReviewBody, :ReviewDate, " . $review->getRating() . ");"; // ($reviewID, $venueID, , ?, ?, ?);";

			// Use a prepared statement to prevent susceptibility to SQL injection
			$prep = $this->dbConnection->prepare($statement);
			
			$prep->bindParam(':ReviewTitle', $review->getTitle());
			$prep->bindParam(':ReviewBody', $review->getBody());
			$prep->bindParam(':ReviewDate', $review->getDate());

			try {
				$prep->execute();
				return "OK";
			}
			catch(Exception $e) {
				ChromePhp::log("Couldn't save review: $e.");
				return $e->getMessage();
			}
		}

		private function storeRating() {
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