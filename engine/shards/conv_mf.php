<?php
	//-----------------------------------------------------------------------------------------------
	// conv_mf.php
	// Version 0.9.0.1
	//----------------------------------------------------------------------------------------------


	$co = &New contentObj;
	$co->contentType="generic";
	$co->primaryContent .= "<div style='padding-bottom: 8px; border-bottom:solid 1px silver;'></div>";
	$co->primaryContent .= "<div style='height:20px'></div>";
	$co->primaryContent .= "<div style='text-align:center;font-size:2em;'>Convert Blursoft Metaforum to Metafora</div>";
	$co->primaryContent .= "<div style='height:40px'></div>";

if (!isInGroup($CURRENTUSER, "admin"))
	exit("Forbidden");

switch ($action):

case "g_default"; {
//	$co->primaryContent .= "<div style='text-align:center;padding:8px;'><a href='index.php?shard=conv_mf&amp;action=g_conv1' class='button'>Launch tables conversion 1st part.</a></div>";
	$co->primaryContent .= "<div style='text-align:center;padding:8px;'><a href='index.php?shard=conv_mf&amp;action=g_values' class='button'>Set values for new fields.</a></div>";
	$shardContentArray[] = $co;

}
break;

case "g_conv1": {
	mysql_query("ALTER TABLE `categories` 
ADD `not_nri` VARCHAR( 8 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ") or die( mysql_error() );
	$co->primaryContent .= "<div>'categories' converted.</div>";

	mysql_query("ALTER TABLE `fhits` ADD `addedDate` INT( 11 ) NOT NULL , ADD `notifiedDate` INT( 11 ) NOT NULL ") or die( mysql_error() );
	$co->primaryContent .= "<div>'fhits' converted.</div>";

	mysql_query("ALTER TABLE `forum_posts` 
ADD `posttype` INT( 2 ) NOT NULL DEFAULT '2',
ADD `depubBy` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `depubDate` INT( 11 ) NOT NULL ,
ADD `IP` INT( 15 ) NOT NULL ") or die( mysql_error() );
	mysql_query("ALTER TABLE `forum_posts` CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ") or die( mysql_error() );
	$co->primaryContent .= "<div>'forum_posts' converted.</div>";

	mysql_query("ALTER TABLE `forum_topics` 
ADD `stickytime` INT( 11 ) NOT NULL ,
ADD `num_comments_T` INT( 11 ) NOT NULL ,
ADD `last_post_id_T` INT( 11 ) NOT NULL ,
ADD `last_post_date_T` INT( 11 ) NOT NULL ,
ADD `last_post_user_T` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `news` INT( 2 ) NOT NULL DEFAULT '0',
ADD `spoiler` INT( 1 ) NOT NULL ,
ADD `teamID` INT( 11 ) NOT NULL ,
ADD `unvisible` INT( 2 ) NOT NULL DEFAULT '0'") or die( mysql_error() );
	$co->primaryContent .= "<div>'forum_topics' converted.</div>";

	mysql_query("ALTER TABLE `forum_user_nri` 
ADD `num_posts_notnri` INT( 11 ) NOT NULL DEFAULT '0',
ADD `num_threads` INT( 11 ) NOT NULL ,
ADD `num_posmods` INT( 11 ) NOT NULL ,
ADD `num_negmods` INT( 11 ) NOT NULL ,
ADD `num_received_posmods` INT( 11 ) NOT NULL ,
ADD `num_received_negmods` INT( 11 ) NOT NULL ,
ADD `lastupdate` INT( 11 ) NOT NULL ") or die( mysql_error() );
	$co->primaryContent .= "<div>'forum_user_nri' converted.</div>";

	mysql_query("ALTER TABLE `permissiongroups` 
ADD `userID` INT( 11 ) NOT NULL ,
ADD `added_by` VARCHAR( 16 ) NOT NULL ,
ADD `added_date` INT( 11 ) NOT NULL ") or die( mysql_error() );
	$co->primaryContent .= "<div>'permissiongroups' converted.</div>";

	mysql_query("ALTER TABLE `postratings` 
ADD `modeduserID` INT( 11 ) NULL ,
ADD `modeddate` INT( 11 ) NULL ") or die( mysql_error() );
	$co->primaryContent .= "<div>'postratings' converted.</div>";

	mysql_query("ALTER TABLE `settings` 
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
ADD `postupdate` INT(4) NOT NULL DEFAULT '20', 
ADD `message` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NULL, 
ADD `teamadmin` INT(11) NOT NULL, 
ADD `teammodo` INT(11) NOT NULL, 
ADD `widgets` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT 'm_login,m_google_adsense,m_whoOnline', 
ADD `team_maxfilesize` INT(11) NOT NULL DEFAULT '1', 
ADD `picture_maxfilesize` INT(11) NOT NULL DEFAULT '2'") or die( mysql_error() );
	$co->primaryContent .= "<div>'settings' converted.</div>";

	mysql_query("ALTER TABLE `users` 
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
ADD `sound_alert` INT( 1 ) NOT NULL DEFAULT '1'") or die( mysql_error() );

	mysql_query("ALTER TABLE `users` 
ADD `hidemyself` INT( 1 ) NOT NULL DEFAULT '0',
ADD `hidemyteams` INT( 1 ) NOT NULL DEFAULT '1',
ADD `team_in_pthread` TINYINT( 1 ) NOT NULL ,
ADD `displayunreadPthread` TINYINT( 1 ) NOT NULL ,
ADD `no_private_sticky` TINYINT( 1 ) NOT NULL ,
ADD `notify_lenght` TINYINT( 4 ) NOT NULL ,
ADD `flood` INT( 1 ) NOT NULL ,
ADD `ajax` INT( 1 ) NOT NULL DEFAULT '1',
ADD `lang` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `graft` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
ADD `ip` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ,
ADD `version` VARCHAR( 12 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ") or die( mysql_error() );
	$co->primaryContent .= "<div>'users' converted.</div>";

	$shardContentArray[] = $co;
}
break;

case "g_values": {
	// Set new fields values in forum_topics
	mf_query("UPDATE forum_topics SET num_comments_T = num_comments, last_post_id_T = last_post_id, last_post_date_T = last_post_date, last_post_user_T = last_post_user");
	
	// Set userID for modded threads
	$query = mf_query("SELECT postratings.ID, forum_topics.userID FROM postratings
						LEFT JOIN forum_topics ON postratings.threadID = forum_topics.ID
						WHERE postratings.threadID IS NOT NULL");
	while ($row = mysql_fetch_array($query)) {
		mf_query("UPDATE postratings SET userID = '$row[userID]' WHERE ID = '$row[ID]'");
	}

	// Set userID for modded posts
	$query = mf_query("SELECT postratings.ID, forum_posts.userID FROM postratings
						LEFT JOIN forum_posts ON postratings.postID = forum_posts.ID
						WHERE postratings.postID IS NOT NULL");
	while ($row = mysql_fetch_array($query)) {
		mf_query("UPDATE postratings SET userID = '$row[userID]' WHERE ID = '$row[ID]'");
	}

	// Set new fields values in forum_user_nri
	$query = mf_query("SELECT userID, name FROM forum_user_nri");
	while ($row = mysql_fetch_array($query)) {
		$username = $row['name'];
		$userID = $row['userID'];
		$datenow = time();
		$total_threads = mf_query("SELECT ID FROM forum_topics WHERE userID='$userID'");
		$total_threads = mysql_num_rows($total_threads);
		$total_posmod = mf_query("SELECT ID FROM postratings WHERE user=\"$username\" and rating > 0");
		$total_posmod = mysql_num_rows($total_posmod);
		$total_negmod = mf_query("SELECT ID FROM postratings WHERE user=\"$username\" and rating < 0");
		$total_negmod = mysql_num_rows($total_negmod);
		$total_received_posmod = mf_query("SELECT ID FROM postratings WHERE modeduserID='$userID' and rating > 0");
		$total_received_posmod = mysql_num_rows($total_received_posmod);
		$total_received_negmod = mf_query("SELECT ID FROM postratings WHERE modeduserID='$userID' and rating < 0");
		$total_received_negmod = mysql_num_rows($total_received_negmod);

		mf_query("UPDATE forum_user_nri SET num_threads='$total_threads', num_posmods='$total_posmod', num_negmods='$total_negmod', num_received_posmods='$total_received_posmod', num_received_negmods='$total_received_negmod', lastupdate='$datenow' WHERE userID='$userID' LIMIT 1");
	}

	// Add userID in permissiongroups
	$query = mf_query("SELECT permissiongroups.ID AS permID, users.ID AS userID
							FROM permissiongroups 
							JOIN users ON permissiongroups.username = users.username
							WHERE permissiongroups.userID = ''");
	while ($row = mysql_fetch_array($query)) {
		mf_query("UPDATE permissiongroups SET userID = '$row[userID]' WHERE ID = '$row[permID]' LIMIT 1");
	}
	
	// Convert birthdate to Linux time
	$query = mf_query("SELECT ID,birthdate FROM users WHERE birthdate != ''");
	while ($c = mysql_fetch_array($query)) {
		if (strtotime(date("Ymd",$c['birthdate'])) != $c['birthdate']) {
			$birthdate = make_var_safe(htmlspecialchars($c["birthdate"]));
			$birthdate = str_replace("/","",$birthdate);
			$birthdate = str_replace("-","",$birthdate);
			$birthdate = str_replace(".","",$birthdate);
			$birthdate = str_replace(" ","",$birthdate);
			$birthdate = make_num_safe($birthdate);
			if (strlen($birthdate) == 8) {
				$birthdate = substr($birthdate,4,4).substr($birthdate,2,2).substr($birthdate,0,2);
				$birthdate = strtotime($birthdate);
			}
			else
				$birthdate = "";
			mf_query("UPDATE users SET birthdate = '$birthdate' WHERE ID = '$c[ID]' LIMIT 1");
		}
	}

	$co->primaryContent .= "<div>Set of values finished.<br/>You now can use your Metafora converted Blursoft Metaforum</div>";
	$shardContentArray[] = $co;

}
break;

endswitch;

?>