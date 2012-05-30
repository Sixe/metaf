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

    //-----------------------------------------------------------------------
    // index.php
    //
    // Handles initialization and menu setup.
    //
    //-----------------------------------------------------------------------
    
	// Metafora release version
	$mf_version = file_get_contents("metafora-version.txt");
	// Javascript version
	$versionj = file_get_contents("engine/core/metafora_js-version.txt"); // metafora.js
	
	include("engine/core/misc.php");
	
    // Do some hacking checking
    if (isset($_REQUEST["ID"])) {
		if (!(is_numeric($_REQUEST["ID"])))
			exit();
	}

	foreach($_GET as $keyVar => $value) {
		$_GET[$keyVar] = xss_clean($_GET[$keyVar]);		
	}

	foreach($_POST as $keyVar => $value) {
		$_POST[$keyVar] = xss_clean($_POST[$keyVar]);		
	}	


	// Load Facebook settings 
	if (file_exists("engine/core/fbsettings.php"))
		include('engine/core/fbsettings.php');

    // Load site settings 
    if (file_exists("engine/core/settings.php"))
		include("engine/core/settings.php");
	else {
		header("Location: install/index.php");
	}

	if (is_dir("install"))
		$_REQUEST['shard'] = "error_install";
	
    // Connect to database
    include("engine/core/db.php");
    connectMysql($siteSettings);

	// Detect if mobile browser
	$siteSettings['mobile'] = false;
	$siteSettings['full_site'] = "";
	if (!array_key_exists('full_site', $_COOKIE)) {
		$siteSettings['mobile'] = mobile_device_detect();
		$siteSettings['full_site'] = "";
		if (!$siteSettings['mobile'])
		$CURRENTUSERAJAX = "1";
	}
	else if ($_COOKIE['full_site'] == "fullsite_simple") {
		$siteSettings['full_site'] = "";
		$CURRENTUSERAJAX = "";
	}
	else if ($_COOKIE['full_site'] == "mobilesiteplus") {
		$siteSettings['mobile'] = true;
		$siteSettings['full_site'] = $_COOKIE['full_site'];
		$CURRENTUSERAJAX = "1";
	}
	else if ($_COOKIE['full_site'] == "mobilesite") {
		$siteSettings['mobile'] = true;
		$siteSettings['full_site'] = $_COOKIE['full_site'];
		$CURRENTUSERAJAX = "";
	}
	else {
		$siteSettings['mobile'] = false;
		$siteSettings['full_site'] = $_COOKIE['full_site'];
		$CURRENTUSERAJAX = "1";
	}

	// Number of theads per page
	$siteSettings['threadpp'] = 40;
	if ($siteSettings['mobile'])
		$siteSettings['threadpp'] = 20;

	// Populate graft setting from DB
    $SSDB = mf_query("SELECT * FROM settings LIMIT 1");
    $SSDB = mysql_fetch_assoc($SSDB);

	// Timezone
	if (!isset($SSDB['timezone']) || !$SSDB['timezone']) {
		date_default_timezone_set('Europe/Paris');
		$SSDB['timezone'] = "Europe/Paris";
	}
	else
		date_default_timezone_set($SSDB['timezone']);

    // Name of the "Skynet" user (system messages)
	if (!isset( $SSDB['user'] )) {
		$query_sysuser=mf_query("SELECT username FROM users WHERE ID=1");
		$query_sysuser=mysql_fetch_array($query_sysuser);
		if (!$query_sysuser['username']) {
			mf_query("INSERT INTO `users` (`ID`, `username`, `password`, `location`, `email`, `website`, `IM`, `facebookID`, `facebookID_cache`, `facebook_disabled`, `profile`, `introducethread`, `rating`, `datejoined`, `userstatus`, `rules`, `rulespictures`, `tentatives`, `next_tentative`, `reset_pass`, `rules_et`, `avatar`, `lat`) VALUES
(1, \"Skynet\", 'noneshallpass', NULL, NULL, NULL, NULL, '0', '', 0, NULL, NULL, 1000, 59054400, 'GOD', NULL, 0, 0, 0, '', 0, 'images/Skynet.jpg', 1303214400);");
			$query_sysuser['username'] = "Skynet";
		}
		$SSDB['user'] = $query_sysuser['username'];
		mf_query("UPDATE settings SET user = \"$SSDB[user]\" WHERE 1");
	}
	
	if (isset( $SSDB['graft'] ))
    	$siteSettings['graft'] = $SSDB['graft'];

	if ($siteSettings['mobile'] && $siteSettings['mobile_graft'])
		$siteSettings['graft'] = $siteSettings['mobile_graft'];

	if (isset ($SSDB['titlebase'] ))
    	$siteSettings['titlebase'] = $SSDB['titlebase'];

    if (isset ($SSDB['titledesc'] ))
    	$siteSettings['titledesc'] = $SSDB['titledesc'];

	$siteSettings['description'] = "";

   	$userlang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2);
	if ($userlang == "fr")
		$siteSettings['lang'] = "fr";
	else
		$siteSettings['lang'] = "en";

    $siteSettings['dChannel'] = $SSDB['dChannel'];
    $siteSettings['verifyEmail'] = $SSDB['verifyemail'];
    $siteSettings['custom_css'] = $SSDB['custom_css'];
    $siteSettings['allowBlogPosting'] = $SSDB['allowBlogPosting'];
	$siteSettings['admin_mail'] = $SSDB['admin_mail'];
	$siteSettings['alert_mail'] = $SSDB['alert_mail'];
    $siteSettings['loadavg'] = $SSDB['loadavg'];
	$siteSettings['buriedlimit'] = $SSDB['buriedlimit'];
	$siteSettings['rules'] = $SSDB['rules'];
	$siteSettings['rulesthread'] = $SSDB['rulesthread'];
	$siteSettings['rulespictures_thread'] = $SSDB['rulespictures_thread'];
	$siteSettings['rules_et_thread'] = $SSDB['rules_et_thread'];
	$siteSettings['flood_ID'] = $SSDB['flood_ID'];
	$siteSettings['mod_rewrite'] = $SSDB['mod_rewrite'];
	$siteSettings['siteurl'] = $SSDB['siteurl'];
	$siteSettings['sitekeywords'] = $SSDB['keywords'];
	$siteSettings['threadupdate'] = $SSDB['threadupdate'] * 1000;
	$siteSettings['postupdate'] = $SSDB['postupdate'] * 1000;
	$siteSettings['teamadmin'] = $SSDB['teamadmin'];
	$siteSettings['teammodo'] = $SSDB['teammodo'];
	$siteSettings['widgets'] = $SSDB['widgets'];
	$siteSettings['team_maxfilesize'] = $SSDB['team_maxfilesize'] * 1024;
	$siteSettings['picture_maxfilesize'] = $SSDB['picture_maxfilesize'] * 1024;
	$siteSettings['systemuser'] = $SSDB['user'];
	$siteSettings['module_friends'] = $SSDB['module_friends'];
	$siteSettings['quote_all_post'] = $SSDB['quote_all_post'];
	$siteSettings['viewmodlist'] = $SSDB['viewmodlist'];
	$siteSettings['deezer'] = $SSDB['deezer'];
	$siteSettings['metacafe'] = $SSDB['metacafe'];
	$siteSettings['iframe'] = $SSDB['iframe'];	
	$siteSettings['num_mods_to_ban'] = $SSDB['num_mods_to_ban'];
	$siteSettings['mobile_enabled'] = $SSDB['mobile_enabled'];
	$siteSettings['change_page'] = $SSDB['change_page'];
	$siteSettings['hide_filters'] = $SSDB['hide_filters'];
	$siteSettings['channel_signal'] = $SSDB['channel_signal'];
	if (!$siteSettings['channel_signal'])
		$siteSettings['channel_signal'] = "1";
	$siteSettings['introduce_ID'] = $SSDB['introduce_ID'];

	if (isset($_REQUEST["shard"])) {
		$shard = make_var_safe($_REQUEST['shard']);
		if (strpos($shard,"&")) {
			$url_elements = explode("&", $shard);
			$shard = $url_elements[0];
			$_REQUEST['shard'] = $shard;
			for ($i=1;$i<=sizeof($url_elements);$i++) {
				$url_elements_in = explode("=", $url_elements[$i]);
				$_REQUEST[$url_elements_in[0]] = $url_elements_in[1];
			}
		}
		
		if (preg_match('/\W/', $shard)) {
			header("Location: ".make_link("error","error=$shard"));
			exit();
		}
		else if (!file_exists("engine/shards/" . $shard . ".php")) {
			$blog_perso = mf_query("SELECT ID FROM blog WHERE webname = \"$shard\" LIMIT 1");
			if ($blog_perso = mysql_fetch_assoc($blog_perso)) {
				$_REQUEST['shard'] = "blog";
				$_REQUEST['blog'] = $shard;
			}
			else {
				if (!isset($url_elements[1]))
					$url_elements[1] = "";
				header("Location: ".make_link("error","&error=$shard&erreur=$url_elements[1]"));
				exit();
			}
		}
	}
	else {
		$shard = $siteSettings["defaultShard"];
		$_REQUEST["shard"] = $shard;
	}

	
    // Authenticate user
    include("engine/core/authenticate.php");  

	if ($siteSettings['mobile'] && $_COOKIE['full_site'] != "mobilesiteplus")
		$CURRENTUSERAJAX = "";

	if ($CURRENTUSER == "anonymous" && $_REQUEST["shard"] != "login" && $siteSettings['loadavg'] > 0) {
		if (file_exists("/proc/loadavg")) {
			$loadavg = trim(file_get_contents('/proc/loadavg'));
			$loads = explode(" ",$loadavg);
			$load = trim($loads[0]);
		}
		else
			$load = "0";
	}

	// debug toggle
    $debugging = false;

    // include core shards

    include("engine/core/formatting.php");
    include("engine/core/clsMenuObj.php");
    include("engine/core/clsContentObj.php");
    include("engine/core/clsMetaObj.php");
    require("engine/core/Sajax.php");

    if (!isset($_REQUEST["shard"]))
        $_REQUEST["shard"] = $siteSettings["defaultShard"];

	$shard = make_var_safe($_REQUEST["shard"]);

    if (!isset($_REQUEST["action"]))
		$_REQUEST["action"] = "g_default";

	$action = make_var_safe($_REQUEST["action"]);

    //=========================================================================
    //render( $shard, $action);
    if (strpos($action, "g_") !== FALSE) {
		// Setup page controller object
    	$pageController = New metaObj;

    	// Specify Graft
		$g = "engine/grafts/" . $siteSettings['graft'] . "/";
		$versioncorecss = file_get_contents("engine/grafts/core-styles/core-styles-version.txt"); // core-styles.css
		$versionc = file_get_contents($g."graft-version.txt"); // graft.css
		
		// Load Tabs
		$thisMenu = New menuObj;
		include('engine/shards/m_forum_nav.php');

		if( $thisMenu->menuTitle != null )
			$pageController->menuArray1[] = $thisMenu;

		// Generate Widgets (Left & Right)
		if (!$siteSettings['mobile']) {
			$widgets = explode(",", $siteSettings['widgets']);
			for ($i = 0; $i < sizeof($widgets); $i++) {
				$thisMenu = New menuObj;
				$widgetID = $i;
				include('engine/shards/' . $widgets[$i] . '.php');

				// Add menu to menu list, if it was created (might not have been due to permissions)
				if( $thisMenu->menuTitle != null )
					$pageController->menuArray2[] = $thisMenu;
			}
		}

    	//--------------------------------------------------
    	// Menus built, moving on to main content object.
    	// 'shardContentArray' is the array of ContentObjects
    	// built by the shard.  
    	//--------------------------------------------------

    	$shardContentArray = array();
    	include('engine/shards/' . $shard .'.php');
    	$pageController->contentArray = $shardContentArray;


		// Load rss
		$rss = "blog.xml";
		if (array_key_exists('userID', $_REQUEST) && $_REQUEST['shard'] == "blog")
			$rss = make_num_safe($_REQUEST['userID']).".xml";

		//------------------------------------------------------------------------------
    	// Now hand the $pageController off to the graft for rendering
    	//------------------------------------------------------------------------------

		// Head
		include("engine/grafts/head.php");
		print($head_file);
		// Load CSS
		print("<style type=\"text/css\" media=\"screen\">
            @import \"engine/grafts/core-styles/core-styles-$versioncorecss.css\";
            @import \"" . $g . "graft-$versionc.css\";");
 		if (!$siteSettings['mobile'])
			print("@import \"engine/core/slide/css/jd.gallery.css\";");
		print("</style>
            <style type=\"text/css\" media=\"screen\">
            $siteSettings[custom_css]
            </style>");
		// Load Javascripts

		print("<script type=\"text/javascript\" src=\"engine/core/metafora-$versionj.js\"></script>
			<script type=\"text/javascript\" src=\"engine/core/swfobject.js\"></script>");
		if (!$siteSettings['mobile'])
			print("<script type=\"text/javascript\" src=\"engine/core/mootools-1.2.1-core-yc.js\"></script>
					<script type=\"text/javascript\" src=\"engine/core/mootools-1.2-more.js\"></script>
					<script type=\"text/javascript\" src=\"engine/core/slide/scripts/jd.gallery.js?2\"></script>");
        print ("<script type=\"text/javascript\" src=\"engine/core/favicon.js\"></script>");
		if (!$siteSettings['mobile'])
			print("<script type=\"text/javascript\" src=\"https://apis.google.com/js/plusone.js\">{lang: '".$siteSettings['lang']."'}</script>");

//		if (defined("FACEBOOK_APP_ID")) {
//			if ($fbcookie || $CURRENTUSER == "anonymous" || !$FACEBOOK_OFF)
//				print ("<script type=\"text/javascript\" src=\"https://connect.facebook.net/".$LANG['FB_LANG']."/all.js\"></script>");
//		}
//		if (!$FACEBOOK_OFF)
//			print ("<script src=\"http://static.ak.fbcdn.net/connect.php/js/FB.Share\" type=\"text/javascript\"></script>");

		print ("<script type=\"text/javascript\"> /* <![CDATA[ */");                    	
		sajax_show_javascript();
		print ("var already_rated = new Array;");
		print ("var b6_tu = \"$siteSettings[threadupdate] \";
				var b6_pu = \"$siteSettings[postupdate] \";
				var b6_site = \"$siteSettings[titlebase] \";
				var b6_graft = \"$siteSettings[graft]\";
				var b6_wait = \"$LANG[PLEASE_WAIT]\";
				var b6_profil = \"$LANG[MEMBER_PROFILE]\";
				var b6_rated = \"$LANG[RATED]\";
				var b6_said = \" $LANG[SAID]:\";
				var b6_stopprev = \"$LANG[STOP_PREVIEW]\";
				var b6_starprev = \"$LANG[START_PREVIEW]\";
				var b6_edition = \"$LANG[EDITION_PLEASE_WAIT]\";
				var b6_error1 = \"$LANG[ERROR_NO_TITLE_BODY]\";
				var b6_postin = \"$LANG[WAIT_TO_POST1]\";
				var b6_seconds = \"$LANG[WAIT_TO_POST2]\";
				var b6_new = \"$LANG[NEW]\";
				var b6_page = \"$LANG[PAGE]\";
				var currentuserid = \"$CURRENTUSERID\";
				var currentuser = \"$CURRENTUSER\";
				var b6_confirm1 = \"$LANG[CONFIRM_1]\";
				var b6_notify_lenght = \"$CURRENTUSERNOTIFYLENGHT\" * 1000;
				var b6_forum_tab = \"$LANG[FORUM_HOME]\";
				var b6_blog_tab = \"$LANG[BLOGS]\";
				var b6_clock = \"$LANG[CLOCK_ERROR]\";
				var b6_confirm_exit = \"$LANG[CONFIRM_EXIT]\";
				var b6_pass_nomatch = \"$LANG[PASS_NOT_MATCH]\";
				var b6_notif_pt = \"$LANG[NOTIF_NEW_PT]\";
				var b6_notif_post = \"$LANG[NOTIF_NEW_POST]\";
			");
            ?>
            /* ]]> */</script>
            </head>
            <body <?php if (array_key_exists('bodyOnload', $siteSettings)) echo $siteSettings['bodyOnload']; ?>>

  
            <?php
			print("<div id=\"meta_description\"></div><div id=\"system_message\"></div>
            <div id=\"header2\">
                <div id=\"image_header\">
                </div>
            </div>");
            if (!$siteSettings['mobile'])
				print("<div id=\"coin_left\"></div><div id=\"coin_right\"></div>");
            print("<div id=\"bandeau_top\">
            </div>
			<div id='pleasewait2'><div id='pleasewaitText1'>".$LANG['LOADING']."</div><img src='images/core/ajax-loader.gif' alt='Wait' /><div id='pleasewaitText2'>".$LANG['PLEASE_WAIT']."</div><div class='clearfix'></div></div><div id='pleasewait'></div>
			<div id='waitToLong'><div style='margin-bottom:20px;'>".$LANG['WAIT_TO_LONG']."</div><span onclick='waitMore();' class='button'>".$LANG['WAIT_MORE']."</span> &nbsp; <span onclick='window.location.reload();' class='button'>".$LANG['RELOAD_PAGE']."</span></div>
			<div id='screenCover' onclick='closelayer();return;' oncontextmenu='newcontexmenu(event);return false;'></div><div id='showLastPost'></div>
			<div id='display_content' ></div><div id='full_button_content' onclick='close_content(); return false;'></div>
			<div id='display_picture' ></div><span id='picture_name' name='' style='display:none'></span>
			<div id='full_button' onclick='close_picture(); return false;'></div><span id='picture_info' style='display:none;'></span>
			<div id='previewPane'></div><span id='site_version' style='display:none;'></span>
			<div id='displayedlayer' class='displayedlayer'></div>
			<div id='allow_notifications' class='displayedlayer'><div onclick='closeDiv(\"allow_notifications\");' class='closeButton'></div><div style='padding:16px;max-width:400px;margin-top:16px;'>".$LANG['NOTIFICATIONS_ASK_BROWSER1']."<span class='bold'>".$siteSettings['titlebase']."</span>".$LANG['NOTIFICATIONS_ASK_BROWSER2']."</div><span onclick='allowNotification();closeDiv(\"allow_notifications\");' class='button'>".$LANG['NOTIFICATIONS_ASK_BROWSER3']."</span> &nbsp; <span onclick='donotaskNotification();closeDiv(\"allow_notifications\");' class='button'>".$LANG['NOTIFICATIONS_ASK_BROWSER4']."</span></div>
			<div id='ajaxload' class='ajaxload'><img src='images/core/indicator.gif' alt='wait' /></div>
			<div id='screen_info'></div>
			<div id='show_tags'></div>
			<div style='display:none;' class='full_button' id='show_tags_button' onclick='hide_tags_click()'></div>");
            
 
			$login_form = "
				<div id='mini_login_form'>
					<div style='inline-block' id='login'>
						<span class='bold'><a href=\"javascript:toggleLayer('loginform2');\" title=\"$LANG[LOGIN]\" class='button_mini'>$LANG[LOGIN]</a></span>
						<div id='loginform2' style='display:none;'><br/>
							<form method='post' action='index.php?shard=login&amp;action=proc_login'>     
								<div>$LANG[EMAIL_OR_USERNAME]:</div>
								<div><input class='search' type='text' size='20' name='login_name' /></div>
								<div>$LANG[PASSWORD]:</div>
								<div><input class='search' type='password' size='10' name='login_pass' /></div>
								<div><input type='checkbox' name='rememberme' style='vertical-align:middle;' /> $LANG[REMEMBER_ME]</div><br />
								<input class='button' type='submit' value=\"$LANG[LOGIN]\" />
							</form>";
			if ($CURRENTUSER != "bot") {
				$login_form .= "<div style='margin-top:8px;'><small><a href='".make_link("adduser")."'>$LANG[GET_ACCOUNT]</a></small></div><div><small><a href='".make_link("login","&amp;action=g_recover_pass")."'>$LANG[PASS_RESET3]</a></small></div>";
				if ($siteSettings['verifyEmail'] == "checked")
					$login_form .= "<div><small><a href='".make_link("adduser","&amp;action=g_resendauthent")."'>$LANG[MAIL_ACTIVATION]</a></small></div>";
			}
			$login_form .= "</div></div></div>";
			
			if ($siteSettings['mobile']) {
				if ($CURRENTUSER != "anonymous" and $CURRENTUSER != "")	{
					print ("<div id='mobile_logged'>
								<div id='mobile_logged_sub'>
									<div>$LANG[WELCOME_BACK], <span class='bold'>$CURRENTUSER</span></div>
									<div style='margin-top:2px;'><a href='".make_link("login","&amp;action=logout")."' class='button_mini'>$LANG[LOGOUT]</a></div>
								</div>
							</div>");
				}
				else
					print ("$login_form");
			}
			else if ($CURRENTUSER == "anonymous")
					print ("$login_form");
			
			if ($CURRENTUSER == "anonymous" && !$siteSettings['mobile']) {
				$lang_list = "";
				$selected = "";
				if ($siteSettings['lang'] == "en")
					$selected = "selected='selected'";
				$lang_list .= "<option value='en' $selected>English</option>";
				$selected = "";
				if ($siteSettings['lang'] == "fr")
					$selected = "selected='selected'";
				$lang_list .= "<option value='fr' $selected>Français</option>";

				$lang_form = "
					<div id='mini_lang_form'>$LANG[LANGUAGE]: <select id='change_lang' name='lang' class='cselect' onchange=\"savelang();\">$lang_list</select>
					</div>";
				
				print ("$lang_form");
			}

			print("<a name=\"threadlist\"></a><div id=\"page\">
			  <table class=\"main\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\"><tr valign=\"top\">");
			if (!$siteSettings['mobile']) {
				print("<td id=\"widgets_left\" style=\"padding-top:26px;max-width:217px;\">
						<div class=\"sidebar\" id=\"sidebar_left1\">");
					
				if (array_key_exists('screen_width', $_COOKIE)) {
					if ($_COOKIE['screen_width'] >= 1170) {
						print("<ul id=\"menu_shard_left\">");
						if (!array_key_exists('shard_left', $_COOKIE) || $CURRENTUSER == "anonymous")
							vMenu($pageController->menuArray2,'left');
						else
							cookieMenu($pageController->menuArray2,'shard_left','left');
						print ("<li id=\"sidebar_space_left\" style=\"height:200px;min-width:8px;list-style-type:none;\"></li></ul>");
					}	
				}
				else {
					print("<ul id=\"menu_shard_left\">");
					vMenu($pageController->menuArray2,'left');
					print ("<li id=\"sidebar_space_left\" style=\"height:200px;min-width:8px;list-style-type:none;\"></li></ul>");
				}
						
						
				print("</div>
						</td>");
			}
			
			print ("<td>
					<table cellspacing=\"0\" cellpadding=\"0\" id=\"onglet_table\"><tr valign=\"top\"><td id=\"corner_left\" onclick=\"scrolltoID('corner_left');\"></td><td id=\"onglet_left\"></td><td>");
				vMenu($pageController->menuArray1);

				$logged = "anonymous";
				if ($CURRENTUSER != "anonymous")
					$logged = "logged";

				print ("</td><td id=\"onglet_right\"></td><td id=\"onglet_right_".$logged."\"></td><td id=\"corner_right\"></td></tr></table>

				<div class='centerwindow'>
					<table cellspacing=\"0\" cellpadding=\"0\">
						<tr>
							<td id=\"border_left\"></td>
							<td id='center_content'>
								<span id=\"anchorthread\" style=\"display:none;\"></span>
								<span id=\"anchorthread2\" style=\"display:none;\"></span>
								<span id=\"anchor_cache\" style=\"display:none;\"></span>
								<div id=\"blogad_cache\" style=\"display:none;\"></div>
								<div id=\"main\">
									<div id='errorPane'>
										<div id='errorPaneText'></div>
										<div class='clearfix'></div>
										<div style='float:right'><a href=\"javascript: closeError();\" class='button'>$LANG[CLOSE]</a></div>
									</div>");
					
					vContent($pageController->contentArray);
					
					print ("</td><td id=\"border_right\"></td></tr></table>
					<table cellspacing=\"0\" cellpadding=\"0\"><tr><td id=\"border_cbl\"></td>
					<td id=\"border_bottom\"></td><td id=\"border_cbr\"></td></tr></table>
					<span id=\"cacheS\" style=\"display:none;\"></span>
					<span id=\"titlecache\" style=\"display:none;\"></span>
					<div id=\"fb_like_cache\" style=\"display:none;\"></div>");
  
					print ("<div class=\"footer\" id=\"footer\" >");
					// PLEASE DO NOT REMOVE OR MODIFY THESE LINES [BEGIN] *****
					print ("<div><a href=\"http://www.metafora.fr\">$LANG[POWERED_BY_METAFORA] Metafora.fr</a> $LANG[METAFORA_VERSION]$mf_version ©&nbsp;2004-2011 <a href=\"http://www.blursoft.com/metaForum\">Blursoft</a> &amp; ©&nbsp;2011-2012 Alexis Dury</div><div>$LANG[UNDER_GPL_LICENCE1] <a href='licence.txt'>$LANG[UNDER_GPL_LICENCE2]</a></div>");
					// [END]*****
					if ($CURRENTUSER != "anonymous")
						$link_mobile_full = "<span onclick=\"fullsite(); return false;\" style='cursor:pointer;font-weight:bold;'>$LANG[SITE_FORMAT_FULL]</span>";
					else
						$link_mobile_full = "<span style='cursor:not-allowed;font-weight:bold;'>$LANG[SITE_FORMAT_FULL]</span>";
					if (!$siteSettings['mobile']) {
						if (!$CURRENTUSERAJAX)
							$link_mobile_full .= " / <span class='display_version_sel'>$LANG[SITE_FORMAT_HTML]</span> ";
						else {
							$link_mobile_full = "<span class='display_version_sel'>$LANG[SITE_FORMAT_FULL]</span>";
							if ($CURRENTUSER != "anonymous")
								$link_mobile_full .= " / <span onclick=\"fullsite_simple(); return false;\" style='cursor:pointer;font-weight:bold;'>$LANG[SITE_FORMAT_HTML]</span> ";
						}
					}
					else
						$link_mobile_full .= " / <span onclick=\"fullsite_simple(); return false;\" style='cursor:pointer;font-weight:bold;'>$LANG[SITE_FORMAT_HTML]</span> ";
					$link_mobile = $link_mobile_plus = "";
					if ($siteSettings['mobile_enabled']) {
						$link_mobile_plus = "/ <span onclick=\"mobilesiteplus(); return false;\" style='cursor:pointer;font-weight:bold;'>$LANG[SITE_FORMAT_MOBILE_PLUS]</span> /";
						if ($siteSettings['mobile'] && $siteSettings['full_site'] == "mobilesiteplus")
							$link_mobile_plus = "/ <span class='display_version_sel'>$LANG[SITE_FORMAT_MOBILE_PLUS]</span> /";
						else if ($CURRENTUSER == "anonymous")
							$link_mobile_plus = "";
						$link_mobile = "<span onclick=\"mobilesite(); return false;\" style='cursor:pointer;font-weight:bold;'>$LANG[SITE_FORMAT_MOBILE]</span>";
						if ($siteSettings['mobile'] && $siteSettings['full_site'] != "mobilesiteplus")
							$link_mobile = "<span class='display_version_sel'>$LANG[SITE_FORMAT_MOBILE]</span>";
					}
					print ("<div>$link_mobile_full $link_mobile_plus $link_mobile</div>");
					if ($siteSettings['rules'] && $CURRENTUSER != "anonymous")
						print ("<div><a href='".make_link("tos")."'>$LANG[RULES_TITLE0] $siteSettings[titlebase]</a></div>");
					
					print ("</div></div></td>");
					if (!$siteSettings['mobile']) {
						print ("<td id=\"widgets_right\" style=\"padding-top:26px;\">
							<div class=\"sidebar\" id=\"sidebar_right2\">");
							if (array_key_exists('screen_width', $_COOKIE)) if ($_COOKIE['screen_width'] < 1170) {
							print("<ul id=\"menu_shard_left\">");
							if (!array_key_exists('shard_left', $_COOKIE) || $CURRENTUSER == "anonymous")
								vMenu($pageController->menuArray2,'left');
							else
								cookieMenu($pageController->menuArray2,'shard_left','left');
							print ("<li id=\"sidebar_space_left\" style=\"height:0px;min-width:8px;list-style-type:none;\"></li></ul>");
						}
						print ("</div>
							<div class=\"sidebar\" id=\"sidebar_right1\"><ul id=\"menu_shard_right\">");
						if (!array_key_exists('shard_right', $_COOKIE) || $CURRENTUSER == "anonymous")
						{
							vMenu($pageController->menuArray2,'right');
						}
						else
						{
							cookieMenu($pageController->menuArray2,'shard_right','right');
						}
						print ("<li id=\"sidebar_space_right\" style=\"height:200px;min-width:8px;list-style-type:none;\"></li></ul></div></td>");
					}
					print ("</tr></table></div>");

  

		print ("<div id=\"sajax_debug_panel\"></div>");
		print ("<script type=\"text/javascript\">
				if (document.body.clientWidth)
					var savewidth = document.body.clientWidth;
				else
					var savewidth = document.body.offsetWidth;

				window.onfocus = function() { gainedFocus(); }
				window.onblur = function() { lostFocus(); }
				window.onresize = function() { verifywidth(); }
				
			</script>");
		print ("<div id=\"flashcontent\">
			  .
			</div>");
			
			?>
			
			<script type="text/javascript"> /* <![CDATA[ */
			<?php
				include("engine/core/browserdetect.js");
				if (!$siteSettings['mobile'])
					include("engine/core/graft_normal.js");
				else
					include("engine/core/graft_mobile.js");
			?>
			/* ]]> */</script>			
			
			<?php
			
			if (isInGroup($CURRENTUSER, "sysadmin") && !isset($lastrelease_version)) {
				$url_release = "http://www.metafora.fr/version.php?version=$mf_version&server_url=".urlencode($_SERVER['HTTP_HOST']);
				$lastrelease_version = @file($url_release);
			}
			if (file_exists("analytics.html"))
				include("analytics.html");
			print ("
			</body>\n
            </html>");

	}
    else {
		include('engine/shards/' . $shard . '.php');
	}

    // uninitialize
    mysql_close();

?>