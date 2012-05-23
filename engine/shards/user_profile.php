<?php
/*
	Copyright 2004-2010 Brian Culler
	Copyright 2011 Alexis DURY
	
	This file is part of Metafora.

	Metafora is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Metafora is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Metafora.  If not, see <http://www.gnu.org/licenses/>.
*///
// user_profile.php
//
// Crudely displays a user's profile information
//
//
global $siteSettings;


// Get permissions *once*
if (isInGroup($CURRENTUSER, "admin"))
	$verifyEditDelete = true;
else
	$verifyEditDelete = false;

// Création du sujet admin pour les mises au silence
function create_admin_thread($text) {
	global $siteSettings;

	if ($text) {
		$inTime = time();
		mf_query("INSERT INTO forum_topics
				(title, body, user, userID, date, threadtype, pthread, category, num_views)
				VALUES (\"$text\", \"$text\", \"$siteSettings[systemuser]\", 1, $inTime, 2, 1, '1', 0)");
		$getThreadId = mf_query("select ID, category from forum_topics where threadtype < 3 AND userID = '1' and date = '$inTime' limit 1");
		$getThreadId2 = mysql_fetch_assoc($getThreadId);
		$threadID = $getThreadId2['ID'];
		mf_query("INSERT INTO forum_posts
				(body, user, userID, date, threadID, rating)
				VALUES (\"$text\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
		$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
		$lastPost = mysql_fetch_assoc($lastPost);
		mf_query("update forum_topics 
				set last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = 1, num_comments_T = 1 
				where ID='$threadID' limit 1");
		// Add admin users
		$query = mf_query("SELECT userID FROM permissiongroups WHERE pGroup = \"admin\" OR pGroup = \"modo\"");
		while ($adduser = mysql_fetch_assoc($query)) {
			mf_query("INSERT IGNORE INTO fhits (threadID, userID) VALUES ($threadID, $adduser[userID])");
		}
	}
	return $threadID;
}


	$added_date = time();

    switch($action ):
     
    case "g_default":
    
		//------------------------------------------------------------------------------
		// Create contentObj for this content object
		//------------------------------------------------------------------------------
		$thisContentObj = New contentObj;
		
		$userid = make_num_safe($_GET['id']);



		$pcStr = "$LANG[USERPROFILE]<br />";

		$profile = mf_query("SELECT username,realname,location,email,website,IM FROM users WHERE ID='" . $userid . "'");

		while ($row = mysql_fetch_assoc($profile ))
		{
				$username = "$LANG[USERNAME]: " . $row['username'] . "<br />";
				$realname = "$LANG[REAL_NAME]: " . $row['realname'] . "<br />";
				

			$pcStr = $username . $realname;
			
		}

		$thisContentObj->primaryContent = $pcStr;
	
		//------------------------------------------------------------------------------
		// Add this contentObject to the shardContentArray
		//------------------------------------------------------------------------------
		$shardContentArray[] = $thisContentObj;
		
    break;
    
	case "g_0":
		if ($verifyEditDelete) {    
			$userID = make_num_safe($_REQUEST['userID']);
			mf_query("insert into permissiongroups (username, userID, pGroup, added_by, added_date) values (\"$_REQUEST[grname]\", '$userID', 'modo', \"$CURRENTUSER\", '$added_date')");
			$query = mf_query("SELECT fhits.* FROM fhits JOIN forum_topics ON fhits.threadID = forum_topics.ID WHERE fhits.userID = '$userID' AND forum_topics.num_comments != forum_topics.num_comments_T");
			while ($row = mysql_fetch_assoc($query)) {
				$query_posts = mf_query("SELECT COUNT(ID) AS totposts FROM forum_posts WHERE threadID = '$row[threadID]' AND date <= '$row[date]'");
				$row_posts = mysql_fetch_assoc($query_posts);
				mf_query("UPDATE fhits SET num_posts = '$row_posts[totposts]' WHERE userID = '$userID' AND threadID = '$row[threadID]' LIMIT 1");
			}
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;
	case "g_0_off":
		if ($verifyEditDelete) {
			$userID = make_num_safe($_REQUEST['userID']);
			mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='modo' limit 1");
			$query = mf_query("SELECT fhits.* FROM fhits JOIN forum_topics ON fhits.threadID = forum_topics.ID WHERE fhits.userID = '$userID' AND forum_topics.num_comments != forum_topics.num_comments_T");
			while ($row = mysql_fetch_assoc($query)) {
				$query_posts = mf_query("SELECT COUNT(ID) AS totposts FROM forum_posts WHERE threadID = '$row[threadID]' AND date <= '$row[date]' AND posttype < 3");
				$row_posts = mysql_fetch_assoc($query_posts);
				mf_query("UPDATE fhits SET num_posts = '$row_posts[totposts]' WHERE userID = '$userID' AND threadID = '$row[threadID]' LIMIT 1");
			}
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;

	case "g_1":
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'level9')) {    
			$userID = make_num_safe($_REQUEST['userID']);
			mf_query("insert into permissiongroups (username, userID, pGroup, added_by, added_date) values (\"$_REQUEST[grname]\", '$userID', 'level1', \"$CURRENTUSER\", '$added_date')");
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level2' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;
	case "g_1_off":
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'level9')) {
			$userID = make_num_safe($_REQUEST['userID']);
			mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level1' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;

	case "g_2":
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'level1') || isInGroup($CURRENTUSER, 'level9'))
        {    
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("insert into permissiongroups (username, userID, pGroup, added_by, added_date) values (\"$_REQUEST[grname]\", '$userID', 'level2', \"$CURRENTUSER\", '$added_date' )");
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level1' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;
	case "g_2_off":
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'level1') || isInGroup($CURRENTUSER, 'level9'))
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level2' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;

	case "g_3":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("insert into permissiongroups (username, userID, pGroup, added_by, added_date) values (\"$_REQUEST[grname]\", '$userID', 'level3', \"$CURRENTUSER\", '$added_date' )");
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level4' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;
	case "g_3_off":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level3' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;

	case "g_4":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("insert into permissiongroups (username, userID, pGroup, added_by, added_date) values (\"$_REQUEST[grname]\", '$userID', 'level4', \"$CURRENTUSER\", '$added_date' )");
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level3' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;
	case "g_4_off":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level4' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;

	case "g_5":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("insert into permissiongroups (username, userID, pGroup, added_by, added_date) values (\"$_REQUEST[grname]\", '$userID', 'level5', \"$CURRENTUSER\", '$added_date' )");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;
	case "g_5_off":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level5' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;

	case "g_6":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("insert into permissiongroups (username, userID, pGroup, added_by, added_date) values (\"$_REQUEST[grname]\", '$userID', 'level6', \"$CURRENTUSER\", '$added_date' )");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;
	case "g_6_off":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level6' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;

	case "g_7":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("insert into permissiongroups (username, userID, pGroup, added_by, added_date) values (\"$_REQUEST[grname]\", '$userID', 'level7', \"$CURRENTUSER\", '$added_date' )");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;
	case "g_7_off":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level7' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;

	case "g_8":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("insert into permissiongroups (username, userID, pGroup, added_by, added_date) values (\"$_REQUEST[grname]\", '$userID', 'level8', \"$CURRENTUSER\", '$added_date' )");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;
	case "g_8_off":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level8' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;

	case "g_9":
		if ($verifyEditDelete)
        {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("insert into permissiongroups (username, userID, pGroup, added_by, added_date) values (\"$_REQUEST[grname]\", '$userID', 'level9', \"$CURRENTUSER\", '$added_date' )");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;
	case "g_9_off":
		if ($verifyEditDelete) {
			$userID = make_num_safe($_REQUEST['userID']);
			$edit = mf_query("DELETE FROM permissiongroups WHERE username=\"$_REQUEST[grname]\" and pGroup='level9' limit 1");
			header("Location: index.php?shard=forum&action=un2id&name=$_REQUEST[grname]#admin");
		}
	break;

	case "g_ban":
		if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'modo')) {
			$ban_username = make_var_safe($_POST['banname']);
			$threadID2 = make_var_safe($_POST['threadID']);
			$ban_ip = make_var_safe($_POST['ban_ip']);
			if ($ban_ip)
				$ban_user_ip = make_var_safe($_POST['userip']);
			$ban_time = make_var_safe($_POST['ban_time']);
			if ($_POST['ban_def'])
				$ban_time = "1618790400";
			else
				$ban_time = $ban_time*3600 + time();
			$ban_reason = make_var_safe($_POST['ban_reason']);
			if ($ban_reason && $ban_time) {

				// Admins ban thread
				$getThreadId = mf_query("select ID, category from forum_topics where threadtype < 3 AND userID = '1' and title = \"$LANG[BANNED_THREAD]\" limit 1");
				$getThreadId2 = mysql_fetch_assoc($getThreadId);
				$threadID = $getThreadId2['ID'];
				
				if (!$threadID) // Création du sujet si celui-ci n'existe pas
					$threadID = create_admin_thread($LANG['BANNED_THREAD']);

				if ($ban_time == "1618790400")
					$end_ban_text = "$LANG[BAN_LENGHT1]";
				else
					$end_ban_text = "$LANG[BAN_LENGHT2] ".round(($ban_time - time())/3600,1) ." $LANG[BANNED_HOURS]";

				$msg = "$LANG[BAN_MODIF_T2] [url=http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?shard=forum&amp;action=un2id&amp;name=".$ban_username."][b]".$ban_username."[/b][/url] $LANG[BAN_MODIF_T3] $end_ban_text.[br]$LANG[BANNED_DONE_BY] $CURRENTUSER.[br]$LANG[BAN_REASON2] : $ban_reason";
				if ($ban_user_ip && $ban_ip)
					$msg .= "[br]$LANG[BAN_MODIF_IP2]";

				$inTime = time();
				$result = mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
				$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
				$lastPost = mysql_fetch_assoc($lastPost);
				mf_query("update forum_topics set last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 where ID='$threadID' limit 1");

				//  Vote Pritvate Thread
				if ($threadID2) {
					// Post message with the name of the Admin who voted
					$msg = "$CURRENTUSER $LANG[BAN_ASK_OF_2] [url=http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?shard=forum&amp;action=un2id&amp;name=".$ban_username."][b]".$ban_username."[/b][/url].[br]$LANG[BAN_REASON2]: $ban_reason";
					$inTime = time();
					$result = mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID2, 0)");
					$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
					$lastPost = mysql_fetch_assoc($lastPost);
					mf_query("update forum_topics 
						set last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', 
						num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 
						where ID='$threadID2' limit 1");
					// Post message saying the ban has been accepted
					$msg = "$LANG[BAN_DONE_1] [url=http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?shard=forum&amp;action=un2id&amp;name=".$ban_username."][b]".$ban_username."[/b][/url] $LANG[BAN_DONE_2][br]$LANG[BAN_DONE_3]";
					$inTime = time();
					$result = mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID2, 0)");
					$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
					$lastPost = mysql_fetch_assoc($lastPost);
					mf_query("update forum_topics 
						set last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', 
						num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 
						where ID='$threadID2' limit 1");
				}
				// Create thread to inform the banned user
				$text = "$LANG[BAN_DONE_4] ".$ban_username."";
				$text2 = "$LANG[BAN_DONE_5] $end_ban_text.[br]";
				$text2 .= "$LANG[BAN_REASON2]: $ban_reason";
				$text2 .= "[br][br]$LANG[BAN_DONE_6]";
				$text2 .= "[br][br][b]$LANG[BAN_DONE_7][/b]";
				$inTime = time();
				mf_query("INSERT INTO forum_topics
						(title, body, user, userID, date, threadtype, pthread, category, locked, num_views)
						VALUES (\"$text\", \"$text2\", \"$siteSettings[systemuser]\", 1, $inTime, 1, 1, '1', '0', 0)");
				$getThreadId = mf_query("select ID, category from forum_topics 
										where threadtype < 3 AND userID = '1' and title = \"$text\" and date = '$inTime' limit 1");
				$getThreadId2 = mysql_fetch_assoc($getThreadId);
				$threadID = $getThreadId2['ID'];
				mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"$text2\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
				$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
				$lastPost = mysql_fetch_assoc($lastPost);
				mf_query("update forum_topics 
						set last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = 1, num_comments_T = 1 
						where ID='$threadID' limit 1");
				
				$query = mf_query("SELECT userstatus, ID FROM users WHERE username=\"$ban_username\" limit 1");
				$alreadybanned = mysql_fetch_assoc($query);
				mf_query("insert into fhits (threadID, userID) value ($threadID, $alreadybanned[ID])"); // user
				$query = mf_query("SELECT userID FROM permissiongroups WHERE pGroup=\"admin\" OR pGroup=\"modo\"");
				while ($addadmin = mysql_fetch_assoc($query))
					mf_query("INSERT IGNORE INTO fhits (threadID, userID) VALUES ($threadID, $addadmin[userID])"); // admin
				
				//  Enregitrement du ban dans la DB
				if ($alreadybanned['userstatus'] != "banned") {
					mf_query("update users set userstatus='banned' where username=\"$ban_username\" limit 1");
					$time_banned = time();
					mf_query("insert into ban (username, date, admin, reason, banned, ip, end_date, threadID) value (\"$ban_username\", '$time_banned', \"$CURRENTUSER\", \"".$ban_reason."\", '1', '$ban_user_ip', '$ban_time', '$threadID')");
					// ban de l'IP
					if ($ban_user_ip && $ban_ip)
						mf_query("insert into ip (IP, type) values ('$ban_user_ip', 'banned')");
				}
			}

			header("Location: index.php?shard=forum&action=un2id&name=$ban_username");
		}
	break;

	case "g_ban_request":
		if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'modo')) {
			$ban_username = make_var_safe($_POST['banname']);
			$ban_reason = make_var_safe($_POST['ban_reason']);
			$threadID = make_var_safe($_POST['threadID']);
			$time_request = time();

			if ($threadID) {
				$getThreadId = mf_query("select ID, category from forum_topics where ID='$threadID' limit 1");
				$getThreadId2 = mysql_fetch_assoc($getThreadId);
				$threadID = $getThreadId2['ID'];
			}
			else  // Création du sujet si celui-ci n'existe pas
				$threadID = create_admin_thread("$LANG[BAN_ASK_OF] $ban_username");

			$msg = "$CURRENTUSER $LANG[BAN_ASK_OF_2] [url=http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?shard=forum&amp;action=un2id&amp;name=".$ban_username."][b]".$ban_username."[/b][/url].[br]$LANG[BAN_REASON2]: $ban_reason";
			$inTime = time();
			$result = mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
			$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
			$lastPost = mysql_fetch_assoc($lastPost);
			mf_query("update forum_topics 
					set last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', 
					num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 
					where ID='$threadID' limit 1");

			mf_query("insert into ban_requested (username, date, adminname, reason, threadID) value (\"$ban_username\", '$time_request', \"$CURRENTUSER\", \"".$ban_reason."\", '$threadID')");

			header("Location: index.php?shard=forum&action=un2id&name=$ban_username");
		}
	break;

	case "g_ban_update":
		if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'modo')) {
			$ban_username = make_var_safe($_POST['banname']);
			$ban_ip_state = make_var_safe($_POST['ban_ip_state']);
			$ban_ip = make_var_safe($_POST['ban_ip']);
			if ($ban_ip && !$ban_ip_state) {
				mf_query("insert into ip (IP, type) values ('".make_var_safe($_POST['userip'])."', 'banned')");
				$ban_user_ip = "ip = '".make_var_safe($_POST['userip'])."',";
			}
			if (!$ban_ip && $ban_ip_state) {
				mf_query("delete from ip where IP='$ban_ip_state' limit 1");
				$ban_user_ip = "ip = '',";
			}
			$ban_time = make_var_safe($_POST['ban_time']);
			if ($_POST['ban_def'])
				$ban_time = "1618790400";
			else if ($ban_time)
				$ban_time = $ban_time*3600 + time();
			$ban_reason = make_var_safe($_POST['ban_reason']);
			if ($ban_reason && $ban_time) {
				// Admins ban thread
				$getThreadId = mf_query("select ID, category from forum_topics where threadtype < 3 AND userID = '1' and title = \"$LANG[BANNED_THREAD]\" limit 1");
				$getThreadId2 = mysql_fetch_assoc($getThreadId);
				$threadID = $getThreadId2['ID'];
				
				if (!$threadID) // Création du sujet si celui-ci n'existe pas
					$threadID = create_admin_thread($LANG['BANNED_THREAD']);

				if ($ban_time == "1618790400")
					$end_ban_text = "$LANG[BAN_LENGHT1]";
				else
					$end_ban_text = "$LANG[BAN_LENGHT2] ".round(($ban_time - time())/3600,1) ." $LANG[BANNED_HOURS]";

				$msg = "$LANG[BAN_MODIF_T1]:[br]$LANG[BAN_MODIF_T2] [url=http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?shard=forum&amp;action=un2id&amp;name=".$ban_username."][b]".$ban_username."[/b][/url] $LANG[BAN_MODIF_T3] $end_ban_text.[br]$LANG[BAN_MODIF_T4] $CURRENTUSER.[br]$LANG[BAN_REASON2]: $ban_reason";
				if ($ban_user_ip == "ip = '',")
					$msg .= "[br]$LANG[BAN_MODIF_IP1]";
				else if ($ban_ip && !$ban_ip_state)
					$msg .= "[br]$LANG[BAN_MODIF_IP2]";
				$inTime = time();
				$result = mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
				$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
				$lastPost = mysql_fetch_assoc($lastPost);
				mf_query("update forum_topics set last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 where ID='$threadID' limit 1");
				
				$infoban = mf_query("SELECT * FROM ban WHERE username = \"$ban_username\" ORDER BY DATE DESC limit 1");
				$infoban = mysql_fetch_assoc($infoban);
				if ($ban_time != $infoban['end_date'] || $ban_reason != $infoban['reason']) {
					$user_threadID = $infoban['threadID'];
					$msg = "$LANG[BAN_MODIF_T5]";
					if ($ban_time != $infoban['end_date'])
						$msg .= "[br]$LANG[BAN_MODIF_T6] $end_ban_text.";
					if ($ban_reason != $infoban['reason'])
						$msg .= "[br]$LANG[BAN_REASON]: $ban_reason";
					$inTime = time();
					$result = mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $user_threadID , 0)");
					$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
					$lastPost = mysql_fetch_assoc($lastPost);
					mf_query("update forum_topics 
						set last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', 
						num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 
						where ID='$user_threadID ' limit 1");
				}

				//  Enregitrement du ban dans la DB
				mf_query("UPDATE ban SET reason = \"".$ban_reason."\", $ban_user_ip end_date = '$ban_time' WHERE username=\"$ban_username\" AND banned = 1 ORDER BY ID DESC limit 1 ");
			}

			header("Location: index.php?shard=forum&action=un2id&name=$ban_username");
		}
	break;

	case "g_unban":
		if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'modo')) {
			$ban_username = make_var_safe($_REQUEST['banname']);
			$edit = mf_query("update users set userstatus=NULL where username=\"$ban_username\" limit 1");
			$time_banned = time();

			// Admins ban thread
			$getThreadId = mf_query("select ID, category from forum_topics where threadtype < 3 AND userID = '1' and title = \"$LANG[BANNED_THREAD]\" limit 1");
			$getThreadId2 = mysql_fetch_assoc($getThreadId);
			$threadID = $getThreadId2['ID'];
			if (!$threadID) // Création du sujet si celui-ci n'existe pas
				$threadID = create_admin_thread($LANG['BANNED_THREAD']);

			$msg = "$LANG[UNBAN_1] [url=http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?shard=forum&amp;action=un2id&amp;name=".$ban_username."][b]".$ban_username."[/b][/url] $LANG[UNBAN_2][br]$LANG[UNBAN_7] $CURRENTUSER.";
			if ($userip['ip'])
				$msg .= "[br]$LANG[UNBAN_IP]";

			$inTime = time();
			$result = mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
			$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
			$lastPost = mysql_fetch_assoc($lastPost);
			mf_query("update forum_topics set last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 where ID='$threadID' limit 1");
			
			// User ban thread
			$user_threadID = make_var_safe($_REQUEST['user_threadID']);
			$msg = "$LANG[UNBAN_4]";
			$inTime = time();
			$result = mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $user_threadID , 0)");
			$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
			$lastPost = mysql_fetch_assoc($lastPost);
			mf_query("update forum_topics set threadtype='2', last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 where ID='$user_threadID ' limit 1");
			
			//  Enregitrement du ban dans la DB
			mf_query("insert into ban (username, date, admin, banned) value (\"$ban_username\", '$time_banned', \"$CURRENTUSER\", '0')");
			$query = mf_query("SELECT ip from ban WHERE username = \"$ban_username\" AND banned = 1 ORDER BY ID DESC limit 1");
			$userip = mysql_fetch_assoc($query);
			if ($userip['ip'])
				mf_query("delete from ip where IP='$userip[ip]' limit 1");
			mf_query("delete from ban_requested where username=\"$ban_username\"");
			
			header("Location: index.php?shard=forum&action=un2id&name=$ban_username");
		}
	break;


	endswitch;

?>