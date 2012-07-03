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
*/

    //authenticate.php

    
   	if (array_key_exists('b6'.$siteSettings['db'].'static', $_COOKIE ) == FALSE ) {
		$staticTrack = "static-" . $_SERVER['REMOTE_ADDR'] ."-" . date("d-m-Y");
		setcookie("b6".$siteSettings['db']."static", "$staticTrack", time()+86400000, "/");
	}

	$authenticated = false;
	$bot = false;
	$ipc=$_SERVER["REMOTE_ADDR"];
	$pos = mf_query("SELECT * FROM ip WHERE IP = '$ipc' LIMIT 1"); 
	$bots = mysql_num_rows($pos);
	if ($bots) {
		$bot = true;
		$row = mysql_fetch_assoc($pos);
		$typeip = $row['type'];
	}

	$fbcookie = "";
	$fbemail = "";
	if (isset($_COOKIE['fblogged'])) {
		$fbcookie = $_COOKIE['fblogged'];
		$fbcookie_array = explode(",",$fbcookie);
		if ($fbcookie_array[1] != "undefined") {
			$fbuserID = $fbcookie_array[0];
			$fbemail = $fbcookie_array[1];
		}
		else {
			$fbcookie = "";
//			setcookie("fblogged", "", time()-2000, "/");
		}
	}

	$siteSettings['facebook'] = false;
	if (!$authenticated && array_key_exists('b6'.$siteSettings['db'].'userID' , $_COOKIE ) == TRUE ) {
		if (is_numeric($_COOKIE['b6'.$siteSettings['db'].'userID'])) {
			$cookieuserID = $_COOKIE['b6'.$siteSettings['db'].'userID'];
			$usr = mf_query("SELECT * FROM users WHERE ID = '$cookieuserID' LIMIT 1");
			
			if ($usr = mysql_fetch_assoc($usr)) {
				if ($usr['lang'])
					$siteSettings['lang'] = $usr['lang'];
				include("engine/core/lang/" . $siteSettings['lang'] . ".php");
					
				if ($usr['graft'] && file_exists("engine/grafts/$usr[graft]") && !$siteSettings['mobile'])
					$siteSettings['graft'] = $usr['graft'];

				if ($_COOKIE['b6'.$siteSettings['db'].'password'] == $usr['password'])
					$authenticated = true;
				else {
					setcookie("b6".$siteSettings['db']."username", "", time()-2000, "/");
					setcookie("b6".$siteSettings['db']."userID", "", time()-2000, "/");
					header("Location: index.php?shard=login&action=g_login_failed");
					exit();
				}
			}
			else {
				include("engine/core/lang/" . $siteSettings['lang'] . ".php");
				header("Location: index.php?shard=login&action=g_login_failed");
				setcookie("b6".$siteSettings['db']."userID", "", time()-2000, "/");
				header("Location: index.php?shard=login&action=g_login_failed");
				exit();
			}
		}
		else {
			exit();
		}
    }
    else if (!$authenticated && $fbemail) {
		
		$fb = mf_query("SELECT * FROM users WHERE facebookID = '".$fbuserID."' LIMIT 1"); 
		$usr = mysql_fetch_assoc($fb);
		if ($usr['ID']) {
			if ($usr['lang'])
				$siteSettings['lang'] = $usr['lang'];
			include("engine/core/lang/" . $siteSettings['lang'] . ".php");

			if ($usr['graft'] && file_exists("engine/grafts/$usr[graft]") && !$siteSettings['mobile'])
				$siteSettings['graft'] = $usr['graft'];

			$authenticated = true;
			$cookieuserID = $usr['ID'];
			$siteSettings['facebook'] = true;
		}
		else if ($_REQUEST["shard"] != "adduser" && $_REQUEST["shard"] != "login") {
			if (!isset($_COOKIE['b6_fb'.$fbuserID]))
				$_REQUEST["shard"] = "adduser";
		}
	}
	if (!$authenticated) {
        if (array_key_exists('lang' , $_COOKIE ) == TRUE ) {
			$siteSettings['lang'] = $_COOKIE['lang'];
		}
        include("engine/core/lang/" . $siteSettings['lang'] . ".php");
        $CURRENTUSER = "anonymous";
		$CURRENTUSERID = "";
        $CURRENTUSERDTT = -.20;
        $CURRENTUSERDTP = -.20;
		$CURRENTUSERPPP = 20;
		$CURRENTUSERIP = $ipc;
		$CURRENTSTATUS = "";
		$CURRENTUSERRULES = "";
		$CURRENTUSERAJAX = "";
		$CURRENTUSERFLOOD ="";

		$anony = mf_query("SELECT ID, lat FROM anonymous WHERE IP = '$ipc' LIMIT 1"); 
		if ($anony = mysql_fetch_assoc($anony)) {
			$lattime = time() - $anony['lat'];
			if ($lattime > 600)
				mf_query("UPDATE anonymous SET lat = '" . time() . "' WHERE ID = '$anony[ID]' LIMIT 1");
		}
		else
			mf_query("INSERT IGNORE INTO anonymous (IP, lat) VALUES ('$ipc', '" . time() . "')");
    }
	
	$FACEBOOK_OFF = "";
	$CURRENTSTATUS = "";
	$CURRENTUSERNOTIFYLENGHT = "";
	if ($authenticated) {
		$CURRENTUSER = $usr['username'];
		$CURRENTSTATUS = $usr['userstatus'];
		$CURRENTUSERID = $cookieuserID;
		$CURRENTUSERRATING = $usr['rating'];
		$CURRENTUSERNEWPT = $usr['newpt'];
		$CURRENTUSERPPP = $usr['posts_per_page'];
		$CURRENTUSERDTT = $usr['dtt'];
		$CURRENTUSERDTP = $usr['dtp'];
		$CURRENTUSERFLOOD = $usr['flood'];
		$CURRENTUSERRULES = $usr['rules'];
		$CURRENTUSERTEAMINPTHREAD = $usr['team_in_pthread'];
		$CURRENTUSERUNREADPTHREAD = $usr['displayunreadPthread'];
		$CURRENTUSERNOPRIVSTICKY = $usr['no_private_sticky'];
		$CURRENTUSERRULESPIC = $usr['rulespictures'];
		$CURRENTUSERRULESET = $usr['rules_et'];
		$CURRENTUSERNOTIFYLENGHT = $usr['notify_lenght'];
		$CURRENTUSERSOUNDALERT = $usr['sound_alert'];
		$FACEBOOK_OFF = $usr['facebook_disabled'];
		$isInGroup = array();
		$query_groups = mf_query("SELECT pGroup	FROM permissiongroups WHERE username = \"$CURRENTUSER\"");
		while ($row_groups = mysql_fetch_assoc($query_groups)) {
			$group = $row_groups['pGroup'];
			$isInGroup[$group] = true;
		}
		if (isset($isInGroup['admin']) || isset($isInGroup['level7']) || isset($isInGroup['level1']))
			$CURRENTUSERRULES = "1";
		
		$isInTeam[0] = false;
		$CURRENTUSERINTEAM = false;
		$query_teams = mf_query("SELECT teamID,level FROM teams_users WHERE userID = '$CURRENTUSERID'");
		while ($row_teams = mysql_fetch_assoc($query_teams)) {
			$teamID = $row_teams['teamID'];
			$isInTeam[$teamID] = $row_teams['level'];
			$CURRENTUSERINTEAM = true;
		}
		$userversion = "";
		$server_name = $_SERVER['SERVER_NAME'];
		$version_cookie = mf_query("SELECT version FROM version WHERE site = \"$server_name\" AND reset_widgets = '1' ORDER BY version DESC LIMIT 1");
		$version_cookie = mysql_fetch_assoc($version_cookie);
		$version = mf_query("SELECT version FROM version WHERE site = \"$server_name\" ORDER BY version DESC LIMIT 1");
		$version = mysql_fetch_assoc($version);
		if ($version_cookie['version']) {
			if (array_key_exists('mfversion' , $_COOKIE ) == TRUE )
				$userversion = $_COOKIE['mfversion'];
			if ($userversion < $version_cookie['version']) {
				setcookie("shard_left", "", time()-4000);
				setcookie("shard_right", "", time()-4000);
				setcookie("mfversion", $version['version'], time()+86400000);
				echo("<script type=\"text/javascript\">window.location.reload();</script>");
				exit();
			}
			else if ($userversion < $version['version'])
				setcookie("version", $version['version'], time()+86400000);
		}

		if ($CURRENTSTATUS == "banned") {
			$infoban = mf_query("SELECT * FROM ban WHERE username = \"$CURRENTUSER\" ORDER BY ID DESC LIMIT 1");
			$infoban = mysql_fetch_assoc($infoban);
			if ($infoban['end_date'] && $infoban['end_date'] < time()) {
				$CURRENTSTATUS = NULL;
				$ban_username = $adminname;
				$getThreadId = mf_query("SELECT ID, category FROM forum_topics 
									WHERE threadtype < 3 AND userID = '1' AND title = \"$LANG[BANNED_THREAD]\" LIMIT 1");
				$getThreadId2 = mysql_fetch_assoc($getThreadId);
				$threadID = $getThreadId2['ID'];
				$msg = "$LANG[UNBAN_1] [url=http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?shard=forum&amp;action=un2id&amp;name=".$CURRENTUSER."][b]".$CURRENTUSER."[/b][/url] $LANG[UNBAN_2].[br]$LANG[UNBAN_3]";
				if ($infoban['ip'])
					$msg .= "[br]$LANG[UNBAN_IP]";
				$inTime = time();
				$result = mf_query("INSERT INTO forum_posts
									(body, user, userID, date, threadID, rating)
										VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
				$lastPost = mf_query("SELECT ID, user from forum_posts WHERE userID=1 and date='$inTime' ORDER BY ID LIMIT 0,1");
				$lastPost = mysql_fetch_assoc($lastPost);
				mf_query("UPDATE forum_topics 
						SET last_post_id='$lastPost[ID]', last_post_user='$lastPost[user]', last_post_date='$inTime', 
						num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 
						WHERE ID='$threadID' LIMIT 1");

				// User ban thread
				$user_threadID = $infoban['threadID'];
				$msg = "$LANG[UNBAN_4]";
				$inTime = time();
				$result = mf_query("INSERT INTO forum_posts
									(body, user, userID, date, threadID, rating)
									VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $user_threadID , 0)");
				$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=1 AND date='$inTime' ORDER BY ID LIMIT 0,1");
				$lastPost = mysql_fetch_assoc($lastPost);
				mf_query("UPDATE forum_topics 
								SET threadtype='2', last_post_id='$lastPost[ID]', last_post_user='$lastPost[user]', last_post_date='$inTime', 
								num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 WHERE ID='$user_threadID ' LIMIT 1");

				mf_query("UPDATE users SET userstatus=NULL WHERE username=\"$CURRENTUSER\" LIMIT 1");
				$time_banned = time();
				mf_query("INSERT INTO ban (username, date, admin, banned) VALUES (\"$CURRENTUSER\", '$time_banned', \"$siteSettings[systemuser]\", '0')");
				if ($infoban['ip'])
					mf_query("DELETE FROM ip WHERE IP='$infoban[ip]' LIMIT 1");
				mf_query("DELETE FROM ban_requested WHERE username=\"$CURRENTUSER\"");
			}
		}
	}

	if ($bot) {
		if ($typeip == "bot")
			$CURRENTUSER = "bot";
		if ($typeip == "banned")
			$CURRENTSTATUS = "banned";
	}


//	session_start();
	if (!isset($_SESSION[$siteSettings['titlebase']]) || $_SESSION[$siteSettings['titlebase']] != true) {
		$_SESSION[$siteSettings['titlebase']] = true;
		$query_rate = mf_query("SELECT ID, comment, posneg FROM postratingcomments ORDER BY comment");
		$_SESSION['option_up'] = "<option value='0'>$LANG[POS_OPTIONS]:</option>";
		$_SESSION['option_down'] = "<option value='0'>$LANG[NEG_OPTIONS]:</option>";
		while ($result_rate = mysql_fetch_assoc($query_rate)) {
			if ($result_rate['posneg'] == '0') {
				$_SESSION['option_up'] .= "<option value='".$result_rate['ID']."'>".stripslashes($result_rate['comment'])." </option>";
			}
			else {
				$_SESSION['option_down'] .= "<option value='".$result_rate['ID']."'>".stripslashes($result_rate['comment'])." </option>";
			}
		}	
	}

?>