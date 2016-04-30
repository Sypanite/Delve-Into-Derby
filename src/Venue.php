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
        private $website = "www.website.com";
        private $telephone = "012345 678910";

		private $reviews;				// List of reviews
		private $averageRating = 0;		// Mean rating
		
		function __construct($_venueID, $_typeID, $_name, $_address, $_postcode, $_website, $_telephone, $_rating) {
			$this->venueID = $_venueID;
			$this->typeID = $_typeID;
			$this->name = $_name;
			$this->address = $_address;
			$this->postcode = $_postcode;
			$this->website = $_website;
			$this->telephone = $_telephone;
			$this->averageRating = $_rating;
		}
		
		/**
		 * Set the reviews, loaded from the database.
		 * Also updates "averageRating" with the current mean of the ratings.
		 **/
		function setReviews($reviews) {
			$this->reviews = $reviews;
		}

		/**
		 * Adds the specified review to the list, and updates the average rating.
		 * This is strictly for the user - database modification is done separately.
		 **/
		function addReview($review) {
			$this->reviews[] = $review;
			$this->averageRating = $this->calculateAverageReview();
			ChromePhp::log("Added review - new average: " . $this->averageRating . ".");
		}

		function calculateAverageReview() {
			$sum = 0;

			for ($i = 0; $i != count($this->reviews); $i++) {
				$sum += $this->reviews[$i]->getRating();
			}
			return intval($sum / $this->getReviewCount());
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
		function getAverageRating() {
			return $this->averageRating;
		}
		
		/**
		 * Returns the venue's telephone number.
		 **/
		function getTelephoneNumber() {
			return $this->telephone;
		}
		
		/**
		 * Returns the venue's website URL.
		 **/
		function getWebsite() {
			return $this->website;
		}
    }
?>