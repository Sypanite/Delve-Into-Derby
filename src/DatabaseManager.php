<?php
	/**
	 * Handles database connection and operations.
	 **/
	class DatabaseManager {
		
		// define("GREETING", "Welcome to W3Schools.com!");

		const HOST = "localhost";
		const DB_NAME = "venues";
		const DB_USER = "root";
		const DB_PASSWORD = "1963nfZ95F";

		private $dbConnection;
		
		/**
		 * Establishes a connection to the database.
		 **/
		public function connect() {
			try {
				$this->dbConnection = new PDO("mysql:host=" . self::HOST . ";dbname=" . self::DB_NAME, self::DB_USER, self::DB_PASSWORD);
				$this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Throw an exception if something cocks up
				return TRUE;
			}
			catch(PDOException $e) {
				return $e->getMessage();
			}
		}

		/*
		 * Loads a list of venues of the specified type.
		 */
		function loadVenues($venueType) {
			echo "<p>Loading venues of type $venueType.</p>";
		
			// Load the appropriate list - no user input, hence not a prepared statement
			$venueResults = $this->dbConnection->query("SELECT * FROM Venues.Venues WHERE TypeID = '$venueType';");
			$venueResults->setFetchMode(PDO::FETCH_ASSOC); // Fetch to an associative array
 
 			$venueList = array();

			while($venueRow = ($venueResults->fetch())) {
				$venueID = $venueRow["VenueID"];
				$venue = new Venue($venueID, $venueRow["TypeID"], $venueRow["Name"],
								   $venueRow["Address"], $venueRow["Postcode"], $venueRow["Website"], $venueRow["Telephone"]);
				$venueList[] = $venue;
			
				// Load the reviews for this venue into an array
				$reviewList = array();
				$reviewResults = $this->dbConnection->query("SELECT * FROM Venues.Reviews WHERE VenueID = '$venueID';");
				$reviewResults->setFetchMode(PDO::FETCH_ASSOC);
			
				while($reviewRow = ($reviewResults->fetch())) {
					$reviewList[] = new Review($reviewRow["ReviewTitle"], $reviewRow["ReviewBody"],
											   $reviewRow["ReviewRating"], $reviewRow["ReviewDate"]); 
				}
				$venue->setReviews($reviewList);

				$venueReviewList = $venue->getReviewList();
			}
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
		 **/
		public function saveReview($venueID, $title, $body, $rating) {
			$reviewID = $this->getReviewID();
			
			$fields = "(ReviewID, VenueID, ReviewTitle, ReviewBody, ReviewDate, StarRating)";
			$statement = "INSERT INTO Venues.Reviews $fields VALUES ($reviewID, $venueID, :ReviewTitle, :ReviewBody, :ReviewDate, $rating);"; // ($reviewID, $venueID, , ?, ?, ?);";

			// Use a prepared statement to prevent susceptibility to SQL injection
			$prep = $this->dbConnection->prepare($statement);
			
			$prep->bindParam(':ReviewTitle', $title);
			$prep->bindParam(':ReviewBody', $body);
			$prep->bindParam(':ReviewDate', date("Y-m-d H:i:s"));

			try {
				$prep->execute();
				// echo "<p>Saved review: $reviewID / $venueID / $title / $body / $rating</p>";
			}
			catch(Exception $e) {
				echo "Coulnd't save review: $e<br>";
			}
		}
	}
?>