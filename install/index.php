<?php
/*
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
    //-----------------------------------------------------------------------
    // index.php
    //
    // Metafora installer.
    //
    //-----------------------------------------------------------------------

	if (file_exists("../engine/core/settings.php")) {
		header("Location: update.php");
		exit();
	}

	include("indexlib.php");

	$header = "<!DOCTYPE html
            PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
            \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	<html>
	<head>
		<title>Metafora Installer / Installateur de Metafora</title>
		<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\" />
	</head>
	<body>
		<div id=\"header2\">
			<div id=\"image_header\"></div>
		</div>
		<div id=\"coin_left\"></div>
		<div id=\"coin_right\"></div>
		<div id=\"bandeau_top\"></div>
		<div id=\"screenCover\"></div>
		<div id=\"page\">
		<div class=\"window\">
			<div style=\"display:table-row;\">
				<div class=\"border_left\"></div>
				<div class=\"content\">";

	$footer="</div>
				<div class=\"border_right\"></div>
				</div>
				<div style=\"display:table-row;\">
					<div class=\"border_cbl\"></div>
					<div class=\"border_bottom\"></div>
					<div class=\"border_cbr\"></div>
				</div>
			</div>
		</div>
	</body>
	</html>";

	if( isset($_REQUEST['step']))
		$step = $_REQUEST['step'];
	else
		$step = "lang";
						  
	if( isset($_REQUEST['lang'])) {
		$lang = $_REQUEST['lang'];
		include("lang/$lang.php");
	}
	else
		$step = "lang";

	switch($step):

	case "lang"; {
	
		print($header);
		print("<div style='text-align:center;padding-top:64px;padding-bottom:64px;'>
			<div style='font-size:2em;margin-bottom:16px;'>
				<div>Please choose a language</div>
				<div>Veuillez choisir une langue</div>
			</div>
			<a href='index.php?step=intro&amp;lang=en' class='button lang'>English</a>
			<a href='index.php?step=intro&amp;lang=fr' class='button lang'>Français</a>
		</div>
		<div style='height:16px;'></div>");
		print($footer);
	}
	break;

	case "intro": {
		
		print($header);
		print("<div class='step'>".$LANG['STEP']." 1 - ".$LANG['INTRO_TITLE']."</div>
			<div style='padding-top:64px;padding-bottom:64px;'>
				<div style='font-size:1.6em;margin-bottom:16px;'>".$LANG['INTRO_TEXT1']."</div>
				<div style='font-size:1.2em;margin-bottom:16px;'>".$LANG['INTRO_TEXT2']."</div>
				<div style='font-size:1.2em;'>".$LANG['INTRO_TEXT3']."</div>
			</div>
			<div style='float:right;'><a href='index.php?step=db&amp;lang=$lang' class='bigb button'>".$LANG['NEXT']."</a></div>");
		print($footer);
	}
	break;

	case "db": {
		$db_server = "localhost";
		if(isset($_REQUEST['db_server']))
			$db_server = $_REQUEST['db_server'];
		$db_name = "";
		if(isset($_REQUEST['db_name']))
			$db_name = $_REQUEST['db_name'];
		$db_user = "";
		if(isset($_REQUEST['db_user']))
			$db_user = $_REQUEST['db_user'];
		print($header);
		print("
		<div class='step'>".$LANG['STEP']." 2 - ".$LANG['DB_TITLE']."</div>
		<form name='db' method='post' action='index.php?step=checkdb&amp;lang=$lang'>
			<div style='padding-top:64px;padding-bottom:64px;font-size:1.6em;'>
				<div style='font-size:0.7em;margin-bottom:16px;'>".$LANG['DB_TEXT']."</div>
				<div style='display:table;font-size:0.8em;margin-left:16px;'>
					<div style='display:table-row;'>
						<div class='cell_left'>".$LANG['DB_SERVER']."</div>
						<div class='cell_right'><input type='text' name='db_server' value=\"$db_server\" size='32'/></div>
					</div>
					<div style='display:table-row;'>
						<div class='cell_left'>".$LANG['DB_NAME']."</div>
						<div class='cell_right'><input type='text' name='db_name' value=\"$db_name\" size='32'/></div>
					</div>
					<div style='display:table-row;'>
						<div class='cell_left'>".$LANG['DB_USER']."</div>
						<div class='cell_right'><input type='text' name='db_user' value=\"$db_user\" size='32'/></div>
					</div>
					<div style='display:table-row;'>
						<div class='cell_left'>".$LANG['DB_PASS']."</div>
						<div class='cell_right'><input type='password' name='db_pass' value='' size='32'/></div>
					</div>
				</div>
			</div>
			<div style='float:left;'><a href='index.php?step=intro&amp;lang=$lang' class='bigb button'>".$LANG['PREVIOUS']."</a></div>
			<div style='float:right;'><input type='submit' value='".$LANG['NEXT']."' class='bigb button'/></div>
		</form>");
		print($footer);
	}
	break;

	case "checkdb": {
		$db_server = $_POST['db_server'];
		$db_name = $_POST['db_name'];
		$db_user = $_POST['db_user'];
		$db_pass = $_POST['db_pass'];

		$db_OK = false;
		print($header);
		print("<div class='step'>".$LANG['STEP']." 3 - ".$LANG['DB_CONNECT']."</div>");
		print("<div style='padding-top:64px;padding-bottom:64px;font-size:1.4em;'>");
		$server_connect = mysql_connect($db_server,$db_user,$db_pass);
		if (!$server_connect)
			print("<div style='color:red;'>$LANG[DB_ERROR1]</div>");
		else {
			$db_connect = mysql_select_db($db_name);
			if (!$db_connect)
				print "<div style='color:red;'>$LANG[DB_ERROR2]</div>";
			else {
				if (write_settings($db_name,$db_user,$db_server,$db_pass )) {
					copy("../engine/core/settings.php", "../engine/core/settings.php.bak");
					print("<div style='color:green;'>$LANG[DB_OK]</div>
							<div style='font-weight:bold;'>$LANG[DB_OK_COMP]</div>");
					$db_OK = true;
				}
				else
					print("<div style='color:red;'>$LANG[SETTINGS_ERROR4]</div>");
			}
		}
		print("</div>");
		print("<div style='float:left;'><a href='index.php?step=db&amp;lang=$lang&amp;db_server=$db_server&amp;db_name=$db_name&amp;db_user=$db_user' class='bigb button'>".$LANG['PREVIOUS']."</a></div>");
		if ($db_OK)
			print("<div style='float:right;'><a href='index.php?step=dbtables&amp;lang=$lang' class='bigb button'>".$LANG['NEXT']."</a></div>");
		print($footer);
	}
	break;

	case "dbtables": {
		include("../engine/core/settings.php");
		$db_OK = false;
		print($header);
		print("<div class='step'>".$LANG['STEP']." 4 - ".$LANG['DB_TABLES']."</div>");
		print("<div style='padding-top:64px;padding-bottom:64px;font-size:1.4em;'>");
		mysql_connect( $siteSettings['server'], $siteSettings['user'], $siteSettings['password'] );
		mysql_select_db( $siteSettings['db'] );
		mysql_set_charset('utf8');
		$result = load_sql("metafora.sql");
		if($result !== TRUE)
			echo $result;
		else {
			$db_OK = true;
			mysql_query("INSERT IGNORE INTO `forum_user_nri` (`ID`, `name`, `times_quoted`, `quote_other`, `num_posts`, `num_posts_notnri`, `cum_post_rating`, `num_posts_thread`, `num_mods`, `num_votes`, `rawrating`, `num_threads`, `num_posmods`, `num_negmods`, `num_received_posmods`, `num_received_negmods`, `lastupdate`, `userID`) VALUES
(1, 'Skynet', 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1)") or die(mysql_error());
			mysql_query("INSERT IGNORE INTO `settings` (`lmenu`, `rmenu`, `defaultShard`, `graft`, `mobile_graft`, `forumGraft`, `titlebase`, `titledesc`, `datestyle`, `fancyEditor`, `siteurl`, `keywords`, `db`, `user`, `server`, `password`, `admin_mail`, `alert_mail`, `allowAnonPosting`, `allowBlogPosting`, `showReplyFormDefault`, `showRecentBlogCommentsPane`, `useBlurpass`, `lang`, `dChannel`, `verifyemail`, `system_notify`, `custom_css`, `loadavg`, `buriedlimit`, `rules`, `rulesthread`, `rulespictures_thread`, `rules_et_thread`, `flood_ID`, `introduce_ID`, `mod_rewrite`, `threadupdate`, `postupdate`, `message`, `teamadmin`, `teammodo`, `widgets`, `team_maxfilesize`, `picture_maxfilesize`) VALUES
(NULL, NULL, NULL, 'metafora', '', NULL, 'Forum', 'Description', NULL, NULL, '', 'metafora', NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, 'fr', 1, '', NULL, '', '', '-2.50', '', '', 0, 0, '', '', '', 30, 20, '', '0', '0', 'm_login,m_google_adsense,m_whoOnline', 1, 3)") or die(mysql_error());
			mysql_query("INSERT IGNORE INTO `users` (`ID`, `username`, `password`, `location`, `email`, `website`, `IM`, `facebookID`, `facebookID_cache`, `facebook_disabled`, `profile`, `introducethread`, `rating`, `datejoined`, `userstatus`, `rules`, `rulespictures`, `tentatives`, `next_tentative`, `reset_pass`, `rules_et`, `avatar`, `lat`) VALUES
(1, 'Skynet', 'noneshallpass', NULL, NULL, NULL, NULL, '0', '', 0, NULL, NULL, 1000, 59054400, 'GOD', NULL, 0, 0, 0, '', 0, 'images/Skynet.jpg', 1303214400)") or die(mysql_error());
			print($LANG['DB_TABLES_SUCCESS']);
		}
		print("</div>");
		if ($db_OK)
			print("<div style='float:right;'><a href='index.php?step=admin&amp;lang=$lang' class='bigb button'>".$LANG['NEXT']."</a></div>");
		else
			print("<div style='float:left;'><a href='index.php?step=db&amp;lang=$lang' class='bigb button'>".$LANG['PREVIOUS']."</a></div>");
		print($footer);
	}
	break;

	case "admin": {
		$login = "";
		if(isset($_REQUEST['login'])) {
			$login = $_REQUEST['login'];
		}
		$email1 = "";
		if(isset($_REQUEST['email1'])) {
			$email1 = $_REQUEST['email1'];
		}
		$email2 = "";
		if(isset($_REQUEST['email2'])) {
			$email2 = $_REQUEST['email2'];
		}
		$textlogin = "";
		if(isset($_REQUEST['error1']))
			$textlogin = "<span class='error'>".$LANG['ERROR1']."</span>";
		$textemail1 = "";
		if(isset($_REQUEST['error2']))
			$textemail1 = "<span class='error'>".$LANG['ERROR2']."</span>";
		$textemail2 = "";
		if(isset($_REQUEST['error3']))
			$textemail2 = "<span class='error'>".$LANG['ERROR3']."</span>";
		$textpass1 = "";
		if(isset($_REQUEST['error4']))
			$textpass1 = "<span class='error'>".$LANG['ERROR4']."</span>";
		$textpass2 = "";
		if(isset($_REQUEST['error5']))
			$textpass2 = "<span class='error'>".$LANG['ERROR5']."</span>";
		print($header);
		print("<div class='step'>".$LANG['STEP']." 5 - ".$LANG['CREATE_ADMIN']."</div>");
		print("<form name='admin' method='post' action='index.php?step=end&amp;lang=$lang'>
			<div style='padding-top:64px;padding-bottom:64px;font-size:1.6em;'>
				<div style='font-size:0.7em;margin-bottom:16px;'>".$LANG['CREATE_ADMIN_TEXT']."</div>
				<div style='display:table;font-size:0.8em;margin-left:16px;'>
					<div style='display:table-row;'>
						<div class='cell_left'>".$LANG['CREATE_ADMIN_PSEUDO']."</div>
						<div class='cell_right'><input type='text' name='login' value=\"$login\" size='32'/> $textlogin</div>
					</div>
					<div style='display:table-row;'>
						<div class='cell_left'>".$LANG['CREATE_ADMIN_EMAIL']."</div>
						<div class='cell_right'><input type='text' name='email1' value=\"$email1\" size='32'/> $textemail1</div>
					</div>
					<div style='display:table-row;'>
						<div class='cell_left'>".$LANG['CREATE_ADMIN_EMAIL_CONF']."</div>
						<div class='cell_right'><input type='text' name='email2' value=\"$email2\" size='32'/> $textemail2</div>
					</div>
					<div style='display:table-row;'>
						<div class='cell_left'>".$LANG['CREATE_ADMIN_PASS']."</div>
						<div class='cell_right'><input type='password' name='pass1' value='' size='32'/> $textpass1</div>
					</div>
					<div style='display:table-row;'>
						<div class='cell_left'>".$LANG['CREATE_ADMIN_PASS_CONF']."</div>
						<div class='cell_right'><input type='password' name='pass2' value='' size='32'/> $textpass2</div>
					</div>
				</div>
			</div>
			<div style='float:right;'><input type='submit' value='".$LANG['NEXT']."' class='bigb button'/></div>
		</form>");
		print($footer);

	}
	break;

	case "end": {
		include("../engine/core/settings.php");
		$db_OK = false;
		$error = "";
		$login = $_POST['login'];
		$email1 = $_POST['email1'];
		$email2 = $_POST['email2'];
		$pass1 = $_POST['pass1'];
		$pass2 = $_POST['pass2'];
		if (!$login)
			$error = "&error1=1";
		if (!$email1)
			$error .= "&error2=1";
		if ($email1 != $email2)
			$error .= "&error3=1";
		if (!$pass1)
			$error .= "&error4=1";
		if ($pass1 != $pass2)
			$error .= "&error5=1";
		if ($error) {
			header("Location: index.php?step=admin&lang=$lang$error&email1=$email1&email2=$email2&login=$login");
			exit();
		}
		else {
			$today = time();
			$password = sha1($pass1);
			$connect = mysql_connect( $siteSettings['server'], $siteSettings['user'], $siteSettings['password']);
			mysql_select_db( $siteSettings['db'],$connect);
			mysql_set_charset('utf8',$connect);
								
			mysql_query("INSERT INTO permissiongroups (username, userID, pGroup, added_date) VALUES ('$login', '2', 'admin', '$today')") or die(mysql_error());
			mysql_query("INSERT INTO permissiongroups (username, userID, pGroup, added_date) VALUES ('$login', '2', 'sysadmin', '$today')") or die(mysql_error());
			mysql_query("INSERT INTO users (username, password, email, rating, datejoined) VALUES (\"$login\", '$password', \"$email1\", '0', '$today')") or die(mysql_error());
			mysql_query("INSERT INTO forum_user_nri (name, userID) VALUES (\"$login\", '2')") or die(mysql_error());
			mysql_query("INSERT INTO forum_topics (title, body, user, userID, date,	threadtype, num_comments, num_comments_T, last_post_id,	last_post_id_T, last_post_date, last_post_date_T, last_post_user, last_post_user_T,	category) VALUES (\"$LANG[T_TITLE]\", \"$LANG[T_BODY]\", \"Skynet\", '1', '$today', '2', '1', '1', '1', '1', '$today', '$today', 'Skynet', 'Skynet','1')") or die(mysql_error());
			mysql_query("INSERT INTO forum_posts (body, user, userID, date,	posttype, threadID) VALUES (\"$LANG[T_BODY]\", \"Skynet\", '1', '$today', '2', '1')") or die(mysql_error());
			mysql_query("INSERT INTO categories (name, num_posts, num_threads, parent_id, nb) VALUES (\"$LANG[CAT_NAME]\", '1', '1', '0', '1')") or die(mysql_error());
			mysql_query("UPDATE settings SET lang=\"$lang\"") or die(mysql_error());

			$metafora_version = file_get_contents("../metafora-version.txt");
			mysql_query("INSERT INTO version (site, version) VALUES (\"metafora_version\",\"$metafora_version\")") or die(mysql_error());

			print($header);
			print("<div class='step'>".$LANG['END']." - ".$LANG['END_TITLE']."</div>");
			print("<div style='padding-top:64px;padding-bottom:64px;font-size:1.4em;'>");
			print("<div>".$LANG['END_TEXT1']."</div>");
			print("<div>".$LANG['END_TEXT2']."</div>");
			print("<div>".$LANG['END_TEXT3']."</div>");
			print("<div style='text-align:center;margin-top:12px;margin-bottom:8px;'><a href='../delinstall.php' class='button' style='font-size:1.2em;'>".$LANG['END_TEXT4']."</a></div>");
			print("<div style='color:red;font-weight:bold;'><img src='images/warning.png' alt=''/>".$LANG['END_TEXT5']."</div>");
			print($footer);
		}
	}
	break;


	endswitch;

?>5