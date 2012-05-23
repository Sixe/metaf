SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


ALTER TABLE `settings` ADD `recaptcha_privKey` VARCHAR( 255 ) NOT NULL ,
ADD `recaptcha_pubKey` VARCHAR( 255 ) NOT NULL ;

