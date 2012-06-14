-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 24, 2011 at 11:14 AM
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `metaf`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE IF NOT EXISTS `albums` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_bin NOT NULL,
  `date` int(11) NOT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  `coverID` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `profile` tinyint(1) NOT NULL,
  `lastupdate` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `albums_topics`
--

CREATE TABLE IF NOT EXISTS `albums_topics` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `albumID` int(11) NOT NULL,
  `threadID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `albumID` (`albumID`,`threadID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `albums_users`
--

CREATE TABLE IF NOT EXISTS `albums_users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `albumID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `owner_userID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `albumID` (`albumID`,`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `anonymous`
--

CREATE TABLE IF NOT EXISTS `anonymous` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IP` varchar(15) COLLATE utf8_bin NOT NULL,
  `lat` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ban`
--

CREATE TABLE IF NOT EXISTS `ban` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(13) COLLATE utf8_bin NOT NULL,
  `date` varchar(11) COLLATE utf8_bin NOT NULL,
  `admin` varchar(13) COLLATE utf8_bin NOT NULL,
  `banned` int(1) NOT NULL,
  `reason` text COLLATE utf8_bin NOT NULL,
  `ip` varchar(15) COLLATE utf8_bin NOT NULL,
  `end_date` int(11) NOT NULL,
  `threadID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ban_requested`
--

CREATE TABLE IF NOT EXISTS `ban_requested` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_bin NOT NULL,
  `adminname` varchar(50) COLLATE utf8_bin NOT NULL,
  `date` int(11) NOT NULL,
  `threadID` int(11) NOT NULL,
  `reason` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE IF NOT EXISTS `blog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(80) COLLATE utf8_bin NOT NULL,
  `userID` int(11) NOT NULL,
  `title` varchar(32) COLLATE utf8_bin NOT NULL,
  `subtitle` varchar(84) COLLATE utf8_bin NOT NULL,
  `view` int(11) NOT NULL,
  `webname` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  UNIQUE KEY `ID` (`ID`),
  KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `num_posts` int(11) DEFAULT '0',
  `num_threads` int(11) DEFAULT '0',
  `description` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `parent_id` int(11) DEFAULT '0',
  `logo` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `nb` int(11) DEFAULT '0',
  `not_nri` varchar(8) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `logo` (`logo`),
  KEY `nb` (`nb`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `faq_shard`
--

CREATE TABLE IF NOT EXISTS `faq_shard` (
  `ID` tinyint(4) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) COLLATE utf8_bin NOT NULL,
  `threadID` int(11) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `order` tinyint(4) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fhits`
--

CREATE TABLE IF NOT EXISTS `fhits` (
  `threadID` int(11) NOT NULL DEFAULT '0',
  `date` int(11) DEFAULT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `subscribed` int(1) NOT NULL DEFAULT '0',
  `num_posts` int(11) DEFAULT NULL,
  `addedDate` int(11) NOT NULL,
  `notifiedDate` int(11) NOT NULL,
  PRIMARY KEY (`userID`,`threadID`),
  KEY `date` (`date`),
  KEY `userID` (`userID`),
  KEY `threadID` (`threadID`),
  KEY `subscribed` (`subscribed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `fhits_anonymous`
--

CREATE TABLE IF NOT EXISTS `fhits_anonymous` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `threadID` int(11) NOT NULL,
  `IP` varchar(15) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `fileID` int(11) NOT NULL AUTO_INCREMENT,
  `fileName` varchar(255) COLLATE utf8_bin NOT NULL,
  `fileEncodedName` varchar(255) COLLATE utf8_bin NOT NULL,
  `fileExtension` varchar(11) COLLATE utf8_bin NOT NULL,
  `fileSize` int(11) NOT NULL,
  `fileDate` int(11) NOT NULL,
  `fileUploadDate` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `IP` varchar(15) COLLATE utf8_bin NOT NULL,
  `downloads` int(11) NOT NULL,
  `publicfile` tinyint(1) NOT NULL,
  UNIQUE KEY `fileID` (`fileID`),
  UNIQUE KEY `fileEncodedName` (`fileEncodedName`),
  KEY `fileName` (`fileName`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE IF NOT EXISTS `forum_posts` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `body` text COLLATE utf8_bin,
  `user` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `posttype` int(2) NOT NULL DEFAULT '2',
  `threadID` int(11) DEFAULT NULL,
  `rating` float DEFAULT '0',
  `title` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `RAWrating` float DEFAULT '0',
  `notes` text COLLATE utf8_bin,
  `depubBy` varchar(50) COLLATE utf8_bin NOT NULL,
  `depubDate` int(11) NOT NULL,
  `IP` varchar(15) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `threadID` (`threadID`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts_history`
--

CREATE TABLE IF NOT EXISTS `forum_posts_history` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(11) NOT NULL,
  `threadID` int(11) NOT NULL,
  `postID` int(11) NOT NULL,
  `user` varchar(50) COLLATE utf8_bin NOT NULL,
  `body` text COLLATE utf8_bin NOT NULL,
  `posttype` int(1) NOT NULL,
  `IP` varchar(15) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `forum_tags`
--

CREATE TABLE IF NOT EXISTS `forum_tags` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) COLLATE utf8_bin NOT NULL,
  `threadID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `tag` (`tag`,`threadID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `forum_topics`
--

CREATE TABLE IF NOT EXISTS `forum_topics` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `body` text COLLATE utf8_bin,
  `user` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `threadtype` int(2) DEFAULT '2',
  `stickytime` int(11) NOT NULL,
  `rating` float DEFAULT '0',
  `num_comments` int(11) DEFAULT NULL,
  `num_comments_T` int(11) DEFAULT NULL,
  `num_views` int(11) DEFAULT NULL,
  `last_post_id` int(11) DEFAULT NULL,
  `last_post_id_T` int(11) NOT NULL,
  `last_post_date` int(11) DEFAULT NULL,
  `last_post_date_T` int(11) NOT NULL,
  `last_post_user` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `last_post_user_T` varchar(50) COLLATE utf8_bin NOT NULL,
  `category` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `RAWrating` float DEFAULT '0',
  `pthread` int(2) DEFAULT '0',
  `locked` int(2) DEFAULT '0',
  `creator_locked` tinyint(1) NOT NULL,
  `poll` int(11) NOT NULL DEFAULT '0',
  `blog` int(2) NOT NULL DEFAULT '0',
  `news` int(2) NOT NULL DEFAULT '0',
  `spoiler` int(1) NOT NULL,
  `team` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `teamID` int(11) NOT NULL,
  `unvisible` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `last_post_date` (`last_post_date`),
  KEY `threadtype` (`threadtype`,`rating`),
  KEY `pthread` (`pthread`),
  KEY `category` (`category`),
  KEY `unvisible` (`unvisible`),
  KEY `blog` (`blog`),
  KEY `news` (`news`),
  KEY `spoiler` (`spoiler`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `forum_topics_users`
--

CREATE TABLE IF NOT EXISTS `forum_topics_users` (
  `threadID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `threadtype` tinyint(1) NOT NULL,
  UNIQUE KEY `threadID` (`threadID`,`userID`),
  KEY `threadtype` (`threadtype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `forum_user_nri`
--

CREATE TABLE IF NOT EXISTS `forum_user_nri` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `times_quoted` int(11) DEFAULT '0',
  `quote_other` int(11) DEFAULT '0',
  `num_posts` int(11) DEFAULT '0',
  `num_posts_notnri` int(11) DEFAULT '0',
  `cum_post_rating` float DEFAULT '0',
  `num_posts_thread` int(11) DEFAULT '0',
  `num_mods` int(11) DEFAULT '0',
  `num_votes` int(11) DEFAULT '0',
  `rawrating` float DEFAULT '0',
  `num_threads` int(11) NOT NULL,
  `num_posmods` int(11) NOT NULL,
  `num_negmods` int(11) NOT NULL,
  `num_received_posmods` int(11) NOT NULL,
  `num_received_negmods` int(11) NOT NULL,
  `lastupdate` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `userID` (`userID`),
  KEY `num_posts_thread` (`num_posts_thread`),
  KEY `cum_post_rating` (`cum_post_rating`),
  KEY `num_mods` (`num_mods`),
  KEY `times_quoted` (`times_quoted`),
  KEY `quote_other` (`quote_other`),
  KEY `rawrating` (`rawrating`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;


-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `ID` int(11) NOT NULL,
  `namedisp0` varchar(18) COLLATE utf8_bin NOT NULL DEFAULT 'level0',
  `namedisp1` varchar(18) COLLATE utf8_bin NOT NULL DEFAULT 'level1',
  `namedisp2` varchar(18) COLLATE utf8_bin NOT NULL DEFAULT 'level2',
  `namedisp3` varchar(18) COLLATE utf8_bin NOT NULL DEFAULT 'level3',
  `namedisp4` varchar(18) COLLATE utf8_bin NOT NULL DEFAULT 'level4',
  `namedisp5` varchar(18) COLLATE utf8_bin NOT NULL DEFAULT 'css',
  `namedisp6` varchar(18) COLLATE utf8_bin NOT NULL,
  `namedisp7` varchar(18) COLLATE utf8_bin NOT NULL DEFAULT 'vip',
  `namedisp8` varchar(18) COLLATE utf8_bin NOT NULL DEFAULT 'blog',
  `namedisp9` varchar(18) COLLATE utf8_bin NOT NULL,
  UNIQUE KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `ip`
--

CREATE TABLE IF NOT EXISTS `ip` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IP` varchar(15) COLLATE utf8_bin NOT NULL,
  `type` varchar(8) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IP` (`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `log_rename_user`
--

CREATE TABLE IF NOT EXISTS `log_rename_user` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `oldname` varchar(50) COLLATE utf8_bin NOT NULL,
  `newname` varchar(50) COLLATE utf8_bin NOT NULL,
  `rename_date` int(1) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `log_user_pthread`
--

CREATE TABLE IF NOT EXISTS `log_user_pthread` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `threadID` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8_bin NOT NULL,
  `addedby` varchar(50) COLLATE utf8_bin NOT NULL,
  `removedby` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `modoptions`
--

CREATE TABLE IF NOT EXISTS `modoptions` (
  `ID` int(3) NOT NULL AUTO_INCREMENT,
  `posneg` int(3) DEFAULT '0',
  `optionName` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `permissiongroups`
--

CREATE TABLE IF NOT EXISTS `permissiongroups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `userID` int(11) NOT NULL,
  `pGroup` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `team` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `added_by` varchar(20) COLLATE utf8_bin NOT NULL,
  `added_date` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `subindex` (`username`,`pGroup`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pictures`
--

CREATE TABLE IF NOT EXISTS `pictures` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(112) COLLATE utf8_bin NOT NULL,
  `name_thumb` varchar(122) COLLATE utf8_bin NOT NULL,
  `width` int(5) NOT NULL,
  `height` int(5) NOT NULL,
  `description` varchar(200) COLLATE utf8_bin NOT NULL,
  `picture_date` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `date_added` int(11) NOT NULL,
  `albumID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `poll_answers`
--

CREATE TABLE IF NOT EXISTS `poll_answers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `answer` varchar(500) COLLATE utf8_bin NOT NULL,
  `poll_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `poll_responses`
--

CREATE TABLE IF NOT EXISTS `poll_responses` (
  `poll_ID` int(11) NOT NULL,
  `answer_ID` int(11) NOT NULL,
  `user_ID` int(11) NOT NULL,
  PRIMARY KEY (`poll_ID`,`user_ID`),
  KEY `user_ID` (`user_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `poll_topics`
--

CREATE TABLE IF NOT EXISTS `poll_topics` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) COLLATE utf8_bin NOT NULL,
  `end_date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `postratingcomments`
--

CREATE TABLE IF NOT EXISTS `postratingcomments` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `comment` varchar(50) COLLATE utf8_bin DEFAULT '0',
  `posneg` int(3) DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `postratings`
--

CREATE TABLE IF NOT EXISTS `postratings` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `postID` int(11) DEFAULT NULL,
  `threadID` int(11) DEFAULT '0',
  `user` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `rating` float DEFAULT NULL,
  `comment` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `modeduserID` int(11) DEFAULT NULL,
  `modeddate` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `postID` (`postID`),
  KEY `user` (`user`),
  KEY `threadID` (`threadID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `post_votes`
--

CREATE TABLE IF NOT EXISTS `post_votes` (
  `postID` int(11) NOT NULL,
  `voteName` varchar(20) COLLATE utf8_bin NOT NULL,
  `total_vote_for` int(11) NOT NULL,
  `total_vote_against` int(11) NOT NULL,
  UNIQUE KEY `postID_2` (`postID`,`voteName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `post_votes_user`
--

CREATE TABLE IF NOT EXISTS `post_votes_user` (
  `postID` int(11) NOT NULL,
  `voteName` varchar(50) COLLATE utf8_bin NOT NULL,
  `userID` int(11) NOT NULL,
  `vote_for` int(1) NOT NULL,
  `vote_against` int(1) NOT NULL,
  UNIQUE KEY `postID_2` (`postID`,`voteName`,`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `promo_shard`
--

CREATE TABLE IF NOT EXISTS `promo_shard` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_bin NOT NULL,
  `type` varchar(100) COLLATE utf8_bin NOT NULL,
  `img` varchar(100) COLLATE utf8_bin NOT NULL,
  `link` varchar(200) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `search_log`
--

CREATE TABLE IF NOT EXISTS `search_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(30) COLLATE utf8_bin NOT NULL,
  `date` int(11) NOT NULL,
  `search` varchar(50) COLLATE utf8_bin NOT NULL,
  `type` int(2) NOT NULL,
  `user_searched` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `lmenu` text COLLATE utf8_bin,
  `rmenu` text COLLATE utf8_bin,
  `defaultShard` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `graft` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `mobile_graft` varchar(50) COLLATE utf8_bin NOT NULL,
  `forumGraft` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `titlebase` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `titledesc` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `datestyle` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `fancyEditor` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `siteurl` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `keywords` text COLLATE utf8_bin NOT NULL,
  `db` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `user` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `server` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `password` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `admin_mail` varchar(80) COLLATE utf8_bin NOT NULL,
  `alert_mail` varchar(80) COLLATE utf8_bin NOT NULL,
  `allowAnonPosting` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `allowBlogPosting` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `showReplyFormDefault` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `showRecentBlogCommentsPane` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `useBlurpass` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `lang` varchar(5) COLLATE utf8_bin DEFAULT NULL,
  `dChannel` int(11) DEFAULT '1',
  `verifyemail` varchar(8) COLLATE utf8_bin DEFAULT 'checked',
  `system_notify` varchar(8) COLLATE utf8_bin DEFAULT 'checked',
  `custom_css` text COLLATE utf8_bin NOT NULL,
  `loadavg` varchar(5) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `buriedlimit` varchar(8) COLLATE utf8_bin NOT NULL DEFAULT '-2.50',
  `rules` varchar(8) COLLATE utf8_bin DEFAULT NULL,
  `rulesthread` varchar(8) COLLATE utf8_bin DEFAULT NULL,
  `rulespictures_thread` int(11) NOT NULL,
  `rules_et_thread` int(11) NOT NULL,
  `flood_ID` varchar(8) COLLATE utf8_bin DEFAULT NULL,
  `introduce_ID` varchar(8) COLLATE utf8_bin NOT NULL,
  `mod_rewrite` varchar(8) COLLATE utf8_bin DEFAULT NULL,
  `threadupdate` int(4) NOT NULL DEFAULT '30',
  `postupdate` int(4) NOT NULL DEFAULT '30',
  `message` varchar(100) COLLATE utf8_bin NOT NULL,
  `teamadmin` varchar(255) COLLATE utf8_bin NOT NULL,
  `teammodo` varchar(255) COLLATE utf8_bin NOT NULL,
  `widgets` varchar(255) COLLATE utf8_bin NOT NULL,
  `team_maxfilesize` int(11) NOT NULL DEFAULT '3',
  `picture_maxfilesize` int(11) NOT NULL DEFAULT '3',
  `quote_all_post` tinyint(1) NOT NULL,
  `module_friends` tinyint(1) NOT NULL,
  `viewmodlist` int(1) NOT NULL,
  `num_mods_to_ban` int(2) NOT NULL DEFAULT '1',
  `deezer` int(1) NOT NULL,
  `metacafe` int(1) NOT NULL,
  `iframe` int(1) NOT NULL,
  `recaptcha_privKey` varchar(255) COLLATE utf8_bin NOT NULL,
  `recaptcha_pubKey` varchar(255) COLLATE utf8_bin NOT NULL,
  `mobile_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `change_page` tinyint(1) NOT NULL,
  `hide_filters` tinyint(1) NOT NULL,
  `channel_signal` int(5) NOT NULL,
  `timezone` varchar(120) COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `slide`
--

CREATE TABLE IF NOT EXISTS `slide` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_bin NOT NULL,
  `subtitle` varchar(100) COLLATE utf8_bin NOT NULL,
  `filename` varchar(50) COLLATE utf8_bin NOT NULL,
  `link_title` varchar(80) COLLATE utf8_bin NOT NULL,
  `image` varchar(100) COLLATE utf8_bin NOT NULL,
  `nb` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `slide_shard`
--

CREATE TABLE IF NOT EXISTS `slide_shard` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_bin NOT NULL,
  `type` varchar(100) COLLATE utf8_bin NOT NULL,
  `img` varchar(100) COLLATE utf8_bin NOT NULL,
  `link` varchar(200) COLLATE utf8_bin NOT NULL,
  `visible` int(1) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `visible` (`visible`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) COLLATE utf8_bin NOT NULL,
  `total_use` int(11) NOT NULL,
  `total_use_week` int(11) NOT NULL,
  `total_use_month` int(11) NOT NULL,
  `total_use_year` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE IF NOT EXISTS `teams` (
  `teamID` int(11) NOT NULL AUTO_INCREMENT,
  `teamName` varchar(255) COLLATE utf8_bin NOT NULL,
  `teamShortName` varchar(60) COLLATE utf8_bin NOT NULL,
  `createdBy` int(11) NOT NULL,
  `createdDate` int(11) NOT NULL,
  `hidemembers` tinyint(1) NOT NULL,
  `hideteam` tinyint(1) NOT NULL,
  `validated` tinyint(1) NOT NULL,
  `validation_thread` int(11) NOT NULL,
  PRIMARY KEY (`teamID`),
  KEY `teamName` (`teamName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `teams_files`
--

CREATE TABLE IF NOT EXISTS `teams_files` (
  `teamID` int(11) NOT NULL,
  `fileID` int(11) NOT NULL,
  `folderID` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `teamID` (`teamID`,`fileID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `teams_folders`
--

CREATE TABLE IF NOT EXISTS `teams_folders` (
  `folderID` int(11) NOT NULL AUTO_INCREMENT,
  `teamID` int(11) NOT NULL,
  `folderName` varchar(255) COLLATE utf8_bin NOT NULL,
  `subfolderID` int(11) NOT NULL,
  `folderDate` int(11) NOT NULL,
  PRIMARY KEY (`folderID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `teams_users`
--

CREATE TABLE IF NOT EXISTS `teams_users` (
  `teamID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `level` tinyint(1) NOT NULL,
  `addedBy` int(11) NOT NULL,
  `addedDate` int(11) NOT NULL,
  `invite_thread` int(11) NOT NULL,
  UNIQUE KEY `teamID_2` (`teamID`,`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `password` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `realname` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `birthdate` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `sexe` int(1) NOT NULL DEFAULT '0',
  `location` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `website` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `IM` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `facebookID` varchar(20) COLLATE utf8_bin NOT NULL,
  `facebookID_cache` varchar(20) COLLATE utf8_bin NOT NULL,
  `facebook_disabled` int(1) NOT NULL,
  `profile` text COLLATE utf8_bin,
  `introducethread` int(11) DEFAULT NULL,
  `rating` float DEFAULT NULL,
  `datejoined` int(11) DEFAULT NULL,
  `userstatus` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `rules` int(1) DEFAULT NULL,
  `rulespictures` int(11) NOT NULL,
  `tentatives` tinyint(1) NOT NULL,
  `next_tentative` int(11) NOT NULL,
  `reset_pass` varchar(100) COLLATE utf8_bin NOT NULL,
  `rules_et` int(11) NOT NULL,
  `avatar` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `sig` varchar(160) COLLATE utf8_bin DEFAULT NULL,
  `newpt` int(3) DEFAULT NULL,
  `posts_per_page` int(5) DEFAULT '50',
  `mail_alert` int(1) NOT NULL DEFAULT '0',
  `pm_alert` int(1) NOT NULL,
  `accept_pm_from` int(1) NOT NULL,
  `sound_alert` int(1) NOT NULL DEFAULT '1',
  `hidemyself` int(1) NOT NULL DEFAULT '0',
  `hidemyteams` int(1) NOT NULL DEFAULT '1',
  `team_in_pthread` tinyint(1) NOT NULL,
  `displayunreadPthread` tinyint(1) NOT NULL,
  `no_private_sticky` tinyint(1) NOT NULL,
  `notify_lenght` tinyint(4) NOT NULL,
  `flood` int(1) NOT NULL,
  `ajax` int(1) DEFAULT '1',
  `lat` int(11) DEFAULT '0',
  `laid` int(11) DEFAULT '0',
  `dtt` float DEFAULT '-0.75',
  `dtp` float DEFAULT '-0.75',
  `lang` varchar(5) COLLATE utf8_bin NOT NULL,
  `graft` varchar(50) COLLATE utf8_bin NOT NULL,
  `ip` varchar(15) COLLATE utf8_bin DEFAULT NULL,
  `version` varchar(12) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `username_idx` (`username`),
  KEY `lat` (`lat`,`laid`),
  KEY `laid` (`laid`),
  KEY `datejoined` (`datejoined`,`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `verify`
--

CREATE TABLE IF NOT EXISTS `verify` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `verifystring` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `version`
--

CREATE TABLE IF NOT EXISTS `version` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `site` varchar(50) COLLATE utf8_bin NOT NULL,
  `version` varchar(12) COLLATE utf8_bin NOT NULL,
  `date` int(11) NOT NULL,
  `comment` text COLLATE utf8_bin NOT NULL,
  `reset_widgets` int(1) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Table structure for table `users_friends`
--

CREATE TABLE IF NOT EXISTS `users_friends` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `target_userID` int(11) NOT NULL,
  `friendType` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
