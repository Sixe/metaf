SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


ALTER TABLE `albums` ADD `profile` TINYINT( 1 ) NOT NULL AFTER `public` ;

ALTER TABLE `settings` ADD `picture_maxfilesize` INT( 11 ) NOT NULL DEFAULT '3' ;

CREATE TABLE `log_rename_user` (
`ID` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`userID` INT( 11 ) NOT NULL ,
`oldname` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`newname` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`rename_date` INT( 1 ) NOT NULL ,
PRIMARY KEY ( `ID` )
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

