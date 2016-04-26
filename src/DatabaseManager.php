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

		private static $s_instance;

		private $dbConnection;
		
		private function __construct() {
		}

		/**
		 * Creates a new singleton instance of DatabaseManager.
		 * If one exists, that is returned.
		 **/
		public static function create() {
			if (!$s_instance) {
				$s_instance = new DatabaseManager();
			}
			return $s_instance;
		}

		/**
		 * Retrieves the singleton instance.
		 **/
		public static function get() {
			return $s_instance;
		}

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
				echo "<p>";
				$venueID = $venueRow["VenueID"];
				$venue = new Venue($venueID, $venueRow["TypeID"], $venueRow["Name"],
								   $venueRow["Address"], $venueRow["Postcode"], $venueRow["Website"], $venueRow["Telephone"]);
				$venueList[] = $venue;
				echo var_dump($venue)."<br>";
			
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
				echo var_dump($venueReviewList)."<br>";
				echo "</p>";
			}
			return $venueList;
		}
	}
?>