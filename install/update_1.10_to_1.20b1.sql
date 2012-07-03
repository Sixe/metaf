SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

ALTER TABLE `users` ADD `salt` VARCHAR( 255 ) NOT NULL AFTER `password` ,
ADD `crypt_method` VARCHAR( 16 ) NOT NULL AFTER `salt` ;

ALTER TABLE `settings` ADD `crypt_method` VARCHAR( 16 ) NOT NULL ;