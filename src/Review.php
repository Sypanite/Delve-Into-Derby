<?php
    /**
     * Represents a review. Loaded from the database.
     **/
    class Review {

        private $title = "Title";
        private $body = "Body";

        private $rating = 0;	// Rating out of 5 stars
		private $date;			// Date of review

		const SNIPPET_LENGTH = 100;

		function __construct($_title, $_body, $_rating, $_date) {
			$this->title = $_title;
			$this->body = $_body;
			$this->rating = $_rating;
			$this->date = $_date;
		}

		/**
		 * Returns the title of the review.
		 **/
		function getTitle() {
			return $this->title;
		}
		
		/**
		 * Returns the body of the review.
		 **/
		function getBody() {
			return $this->body;
		}

		/**
		 * Returns a snippet of the body. Displayed in the sidebar.
		 **/
		function getSnippet() {
			if (strlen($this->body) > self::SNIPPET_LENGTH) {
				return substr($this->body, 0, self::SNIPPET_LENGTH) . "...";
			}
			return $this->body;
		}
		
		/**
		 * Returns the rating, out of five.
		 **/
		function getRating() {
			return $this->rating;
		}
		
		/**
		 * Returns the date that the review was created.
		 **/
		function getDate() {
			return $this->date;
		}
    }
?>