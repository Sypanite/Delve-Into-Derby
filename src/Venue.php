<?php
    /**
     * Represents a venue. Loaded from the database.
     **/
    class Venue {
		
		private $venueID;
		private $typeID;

        private $name = "P. Sherman";
		private $address = "42 Wallaby Way, Sydney";
        private $postcode = "DE0 0ED";

		private $reviews;				// List of reviews
		private $averageRating = 0;		// Mean rating
		
		function __construct($_venueID, $_typeID, $_name, $_address, $_postCode) {
			$this->venueID = $_venueID;
			$this->typeID = $_typeID;
			$this->name = $_name;
			$this->postcode = $_postCode;
			$this->address = $_address;
		}
		
		/**
		 * Set the reviews, loaded from the database.
		 * Also updates "averageRating" with the current mean of the ratings.
		 **/
		function setReviews($reviews) {
			$this->reviews = $reviews;

			$sum = 0;

			for ($i = 0; $i != $this->getReviewCount(); $i++) {
				$sum += $this->reviews[$i]->getRating();
			}
			$averageRating = intval($sum / $this->getReviewCount());
		}

		/**
		 * Returns the venue's ID.
		 **/
		function getID() {
			return $this->venueID;
		}
		
		/**
		 * Returns the total number of reviews.
		 **/
		function getReviewCount() {
			return count($this->reviews);
		}

		/**
		 * Returns this venue's list of reviews.
		 **/
		function getReviewList() {
			return $this->reviews;
		}
		
		/**
		 * Returns the venue's name.
		 **/
		function getName() {
			return $this->name;
		}
		
		/**
		 * Returns the venue's address.
		 **/
		function getAddress() {
			return $this->address;
		}
		
		/**
		 * Returns the venue's postcode.
		 **/
		function getPostcode() {
			return $this->postcode;
		}
		
		/**
		 * Returns the venue's average rating.
		 **/
		function getRating() {
			return $this->averageRating;
		}
    }
?>