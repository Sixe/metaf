SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


ALTER TABLE `settings` ADD `viewmodlist` int(1) NOT NULL ;
ALTER TABLE `settings` ADD `num_mods_to_ban` int(2) NOT NULL DEFAULT '1' ;
ALTER TABLE `settings` ADD `deezer` int(1) NOT NULL ;
ALTER TABLE `settings` ADD `metacafe` int(1) NOT NULL ;
ALTER TABLE `settings` ADD `iframe` int(1) NOT NULL ;
