SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


ALTER TABLE `forum_topics` ADD `creator_locked` TINYINT( 1 ) NOT NULL AFTER `locked` ;
ALTER TABLE `settings` ADD `quote_all_post` TINYINT( 1 ) NOT NULL , ADD `module_friends` TINYINT( 1 ) NOT NULL ;
ALTER TABLE `users` ADD `accept_pm_from` INT( 1 ) NOT NULL AFTER `pm_alert` ;


CREATE TABLE IF NOT EXISTS `users_friends` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `target_userID` int(11) NOT NULL,
  `friendType` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
