<?php
    /**
     * Represents a review. Loaded from the database.
     **/
    class Review {

        private $title = "Title";
        private $body = "Body";

        private $rating = 0;	// Rating out of 5 stars
		private $date;			// Date of review

		function __construct($_title, $_body, $_rating, $_date) {
			$this->title = $_title;
			$this->body = $_body;
			$this->rating = $_rating;
			$this->date = $_date;
		}

		function getRating() {
			return $this->rating;
		}
    }
?>