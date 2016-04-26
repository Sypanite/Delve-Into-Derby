<?php
    /**
     * Represents a review. Loaded from the database.
     **/
    public class Review {

        private $title = "Title";
        private $body = "Body";

        private $rating = 0;		// Rating out of 5 stars
		private DateTime $date;		// Date of review

		public __construct($_title, $_body, $_rating, $_date) {
			$title = $_title;
			$body = $_body;
			$rating = $_rating;
			$date = $_date;
		}
    }
?>