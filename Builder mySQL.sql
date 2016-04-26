-- Builds a relational database to store data for the restaurant application.

CREATE DATABASE venues;

-- Stores the various 'types' of venue - restaurant, cinema, museum
CREATE TABLE Venues.VenueType (
	TypeID				CHAR			PRIMARY KEY,
	Description			VARCHAR(16)
);

-- Stores the days of the week
CREATE TABLE Venues.Days (
	DayID				SMALLINT		NOT NULL,
	DayName				VARCHAR(10) 	NOT NULL,
	PRIMARY KEY (DayID, DayName);
);

-- Insert these values here as they're 'constants', for a better word
INSERT INTO Venues.VenueType VALUES ('R', 'Restaurant');
INSERT INTO Venues.VenueType VALUES ('C', 'Cinema');
INSERT INTO Venues.VenueType VALUES ('M', 'Museum');
INSERT INTO Venues.Days VALUES (0, "Monday");
INSERT INTO Venues.Days VALUES (1, "Tuesday");
INSERT INTO Venues.Days VALUES (2, "Wednesday");
INSERT INTO Venues.Days VALUES (3, "Thursday");
INSERT INTO Venues.Days VALUES (4, "Friday");
INSERT INTO Venues.Days VALUES (5, "Saturday");
INSERT INTO Venues.Days VALUES (6, "Sunday");

-- Stores an opening time for a given day. Venues can have multiple opening times on each day.
CREATE TABLE OpenTime (
	VenueID			SMALLINT			NOT NULL,	
	DayID			SMALLINT			NOT NULL,
	Opens			TIME				NOT NULL,
	Closes			TIME				NOT NULL,
	FOREIGN KEY (DayID) REFERENCES Venues.Days(DayID) ON DELETE CASCADE
	FOREIGN KEY (VenueID) REFERENCES Venues.Venues(VenueID) ON DELETE CASCADE
	PRIMARY KEY (OpenTime, CloseTime);
);

-- Stores each venue's opening times. Venues may have multiple open/close times for each day.
CREATE TABLE Venues.OpeningTimes (
	VenueID			SMALLINT			PRIMARY KEY,
	Monday			OpenTime
);

-- Stores each venue
CREATE TABLE Venues.Venues (
	TypeID			CHAR				NOT NULL,
	VenueID			SMALLINT			PRIMARY KEY,
	Name			VARCHAR(32)			NOT NULL,
	Address			VARCHAR(128)		NOT NULL,
	Postcode		VARCHAR(8)			NOT NULL,
	Website			VARCHAR(128),
	Phone			VARCHAR(12),

	FOREIGN KEY (TypeID) REFERENCES Venues.VenueType(TypeID) ON DELETE CASCADE
);

-- Stores each review for each venue
CREATE TABLE Venues.Reviews (
	ReviewID		INTEGER				AUTO_INCREMENT PRIMARY KEY,	-- The review's unique index
	VenueID			SMALLINT			NOT NULL,	-- The unique ID of the establishment
	ReviewTitle		VARCHAR(32)			NOT NULL,		-- The "title" of the review - a short summary
	ReviewBody		VARCHAR(128),						-- The body of the review, containing the main points
	ReviewDate		DATE				NOT NULL,		-- Date the review was left
	StarRating		SMALLINT			NOT NULL,		-- Given number of stars, out of five

	FOREIGN KEY (VenueID) REFERENCES Venues.Venues(VenueID) ON DELETE CASCADE
);