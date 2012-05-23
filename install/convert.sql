
ALTER TABLE `categories` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;
ALTER TABLE `categories` 
ADD `not_nri` VARCHAR( 8 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ;

ALTER TABLE `fhits` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;
ALTER TABLE `fhits` 
ADD `addedDate` INT( 11 ) NOT NULL , 
ADD `notifiedDate` INT( 11 ) NOT NULL ;

ALTER TABLE `forum_posts` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;
ALTER TABLE `forum_posts` 
ADD `posttype` INT( 2 ) NOT NULL DEFAULT '2',
ADD `depubBy` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `depubDate` INT( 11 ) NOT NULL ,
ADD `IP` INT( 15 ) NOT NULL ;
ALTER TABLE `forum_posts` CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ;

ALTER TABLE `forum_topics` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;
ALTER TABLE `forum_topics` 
ADD `stickytime` INT( 11 ) NOT NULL ,
ADD `num_comments_T` INT( 11 ) NOT NULL ,
ADD `last_post_id_T` INT( 11 ) NOT NULL ,
ADD `last_post_date_T` INT( 11 ) NOT NULL ,
ADD `last_post_user_T` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `news` INT( 2 ) NOT NULL DEFAULT '0',
ADD `spoiler` INT( 1 ) NOT NULL ,
ADD `teamID` INT( 11 ) NOT NULL ,
ADD `unvisible` INT( 2 ) NOT NULL DEFAULT '0' ;

ALTER TABLE `forum_user_nri` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;
ALTER TABLE `forum_user_nri` 
ADD `num_posts_notnri` INT( 11 ) NOT NULL DEFAULT '0',
ADD `num_threads` INT( 11 ) NOT NULL ,
ADD `num_posmods` INT( 11 ) NOT NULL ,
ADD `num_negmods` INT( 11 ) NOT NULL ,
ADD `num_received_posmods` INT( 11 ) NOT NULL ,
ADD `num_received_negmods` INT( 11 ) NOT NULL ,
ADD `lastupdate` INT( 11 ) NOT NULL ;

ALTER TABLE `hittracker` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;

ALTER TABLE `modoptions` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;

ALTER TABLE `permissiongroups` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;
ALTER TABLE `permissiongroups` 
ADD `userID` INT( 11 ) NOT NULL ,
ADD `added_by` VARCHAR( 16 ) NOT NULL ,
ADD `added_date` INT( 11 ) NOT NULL ;

ALTER TABLE `poll_answers` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;

ALTER TABLE `poll_responses` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;

ALTER TABLE `poll_topics` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;

ALTER TABLE `postratingcomments` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;

ALTER TABLE `postratings` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;
ALTER TABLE `postratings` 
ADD `modeduserID` INT( 11 ) NULL ,
ADD `modeddate` INT( 11 ) NULL ;

ALTER TABLE `settings` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;
ALTER TABLE `settings` 
ADD `mobile_graft` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, 
ADD `keywords` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, 
ADD `admin_mail` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, 
ADD `alert_mail` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, 
ADD `loadavg` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0', 
ADD `buriedlimit` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '-2.50', 
ADD `rules` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_bin NULL, 
ADD `rulesthread` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_bin NULL, 
ADD `rulespictures_thread` INT(11) NOT NULL, 
ADD `rules_et_thread` INT(11) NULL, 
ADD `flood_ID` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, 
ADD `introduce_ID` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_bin NULL, 
ADD `mod_rewrite` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, 
ADD `threadupdate` INT(4) NOT NULL DEFAULT '30', 
ADD `postupdate` INT(4) NOT NULL DEFAULT '30', 
ADD `message` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NULL, 
ADD `teamadmin` INT(11) NOT NULL, 
ADD `teammodo` INT(11) NOT NULL, 
ADD `widgets` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT 'm_login,m_google_adsense,m_whoOnline', 
ADD `team_maxfilesize` INT(11) NOT NULL DEFAULT '1', 
ADD `picture_maxfilesize` INT(11) NOT NULL DEFAULT '2';

ALTER TABLE `users` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;
ALTER TABLE `users` 
ADD `sexe` INT( 1 ) NOT NULL DEFAULT '0',
ADD `facebookID` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `facebookID_cache` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `facebook_disabled` INT( 1 ) NOT NULL ,
ADD `introducethread` INT( 11 ) NOT NULL ,
ADD `rules` INT( 1 ) NULL ,
ADD `rulespictures` INT( 11 ) NOT NULL ,
ADD `rules_et` INT( 11 ) NOT NULL ,
ADD `tentatives` TINYINT( 1 ) NOT NULL ,
ADD `next_tentative` INT( 11 ) NOT NULL ,
ADD `reset_pass` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `mail_alert` INT( 1 ) NOT NULL DEFAULT '0',
ADD `pm_alert` INT( 1 ) NOT NULL ,
ADD `sound_alert` INT( 1 ) NOT NULL DEFAULT '1', 
ADD `hidemyself` INT( 1 ) NOT NULL DEFAULT '0',
ADD `hidemyteams` INT( 1 ) NOT NULL DEFAULT '1',
ADD `team_in_pthread` TINYINT( 1 ) NOT NULL,
ADD `displayunreadPthread` TINYINT( 1 ) NOT NULL ,
ADD `no_private_sticky` TINYINT( 1 ) NOT NULL ,
ADD `notify_lenght` TINYINT( 4 ) NOT NULL ,
ADD `flood` INT( 1 ) NOT NULL ,
ADD `ajax` INT( 1 ) NOT NULL DEFAULT '1',
ADD `lang` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `graft` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `ip` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ,
ADD `version` VARCHAR( 12 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ;

ALTER TABLE `verify` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin ;
