<?php
    /**
     * Represents a venue. Loaded from the database.
     **/
    public class Venue {
		
		private $venueID;
		private $typeID;

        private $name = "P. Sherman";
		private $address = "42 Wallaby Way, Sydney";
        private $postCode = "DE0 0ED";

		private $reviews;		// List of reviews

		function __construct($_venueID, $_typeID, $_name, $_address, $_postCode) {
			$this->venueID = $_venueID;
			$this->typeID = $_typeID;
			$this->name = $_name;
			$this->postCode = $_postCode;
			$this->address = $_address;
		}
    }
?>