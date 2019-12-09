-- create the tables for our movies

CREATE TABLE itemlist(
 `PostID` int(32) unsigned NOT NULL AUTO_INCREMENT,
 `Item` varchar(255) NOT NULL,
 `Description` varchar(3072) NOT NULL,
 `DatePosted` datetime DEFAULT NULL,
 `Status` varchar(255) NOT NULL,
 `Latitude` varchar(255) NOT NULL,
 `Longitude` varchar(255) NOT NULL,
 PRIMARY KEY (`PostID`)
);