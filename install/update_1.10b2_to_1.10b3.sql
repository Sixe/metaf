SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


ALTER TABLE `forum_topics` ADD `creator_locked` TINYINT( 1 ) NOT NULL AFTER `locked` ;
ALTER TABLE `settings` ADD `mobile_enabled` TINYINT( 1 ) NOT NULL DEFAULT '1' ;
ALTER TABLE `settings` ADD `change_page` TINYINT( 1 ) NOT NULL ;
ALTER TABLE `settings` ADD `hide_filters` TINYINT( 1 ) NOT NULL ;
ALTER TABLE `settings` ADD `channel_signal` INT( 5 ) NOT NULL  ;
