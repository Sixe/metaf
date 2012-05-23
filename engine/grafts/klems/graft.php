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

// graft.php

    function html_header($rss) {
        global $siteSettings;
        global $shard;
        global $action;
        global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTUSERAJAX;
		global $LANG;
		global $CURRENTUSERNOTIFYLENGHT;
		global $FACEBOOK_OFF;
		global $versionj;
		global $fbcookie;
        $g = "engine/grafts/" . $siteSettings['graft'] . "/";
		$versionc = file_get_contents($g."graft-version.txt"); // graft.css

        include("engine/grafts/head.php");
		
		print("$head_file
            <style type=\"text/css\" media=\"screen\">

            @import \"engine/grafts/core-styles/core-styles.css\";

            @import \"" . $g . "graft-$versionc.css\";
            @import \"slide/css/jd.gallery.css\";

            </style>
            <style type=\"text/css\" media=\"screen\">
            $siteSettings[custom_css]
            </style>
            <script type=\"text/javascript\" src=\"engine/core/metafora-$versionj.js\"></script>
            <script type=\"text/javascript\" src=\"engine/core/swfobject.js\"></script>
			<script type=\"text/javascript\" src=\"engine/core/mootools-1.2.1-core-yc.js\"></script>
			<script type=\"text/javascript\" src=\"engine/core/mootools-1.2-more.js\"></script>
			<script type=\"text/javascript\" src=\"engine/core/slide/scripts/jd.gallery.js?2\"></script>");
			if (defined("FACEBOOK_APP_ID")) {
				if ($fbcookie || $CURRENTUSER == "anonymous" || !$FACEBOOK_OFF)
					print ("<script type=\"text/javascript\" src=\"http://connect.facebook.net/fr_FR/all.js\"></script>");
			}
            print ("<script type=\"text/javascript\"> /* <![CDATA[ */");                    	
            sajax_show_javascript();
			print ("var already_rated = new Array;");
			print ("
				var b6_tu = \"$siteSettings[threadupdate] \";
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
			");
            ?>
            /* ]]> */</script>
            </head>
            <body <?php if (array_key_exists('bodyOnload', $siteSettings)) echo $siteSettings['bodyOnload']; ?>>
            <div id="system_message"></div>
            <div id="header2">
                <div id="image_header">
                </div>
            </div>
            <div id="coin_left">
            </div>
            <div id="coin_right">
            </div>
            <div id="bandeau_top">
            </div>
			<div id='pleasewait2'><div id='pleasewaitText1'><?php echo $LANG['LOADING'] ?></div><img src='images/core/ajax-loader.gif' alt='Wait' /><div id='pleasewaitText2'><?php echo $LANG['PLEASE_WAIT'] ?></div><div class='clearfix'></div></div><div id='pleasewait'></div>
			<div id='waitToLong'><div style='margin-bottom:20px;'><?php echo $LANG['WAIT_TO_LONG'] ?></div><span onclick='waitMore();' class='button'><?php echo $LANG['WAIT_MORE'] ?></span> &nbsp; <span onclick='window.location.reload();' class='button'><?php echo $LANG['RELOAD_PAGE'] ?></span></div>
			<div id='screenCover' onclick='closelayer();return;' oncontextmenu='newcontexmenu(event);return false;'></div><div id='showLastPost'></div>
			<div id='display_picture' ></div><span id='picture_name' name='' style='display:none'></span>
			<div id='full_button' onclick='close_picture(); return false;'></div><span id='picture_info' style='display:none;'></span>
			<div id='previewPane'></div><span id='site_version' style='display:none;'></span>
			<div id='displayedlayerShadow' class='displayedlayerShadow'></div>
			<div id='displayedlayer' class='displayedlayer'></div>
			<div id='allow_notifications' class='displayedlayer' style='text-align:center;height:120px;position:fixed;'><div onclick='closeDiv("allow_notifications");' class='closeButton'></div><div style='padding:16px;max-width:400px;margin-top:16px;'><?php echo $LANG['NOTIFICATIONS_ASK_BROWSER1']?><span class='bold'><?php echo $siteSettings['titlebase']; ?></span><?php echo $LANG['NOTIFICATIONS_ASK_BROWSER2'];?></div><span onclick='allowNotification();closeDiv("allow_notifications");' class='button'><?php echo $LANG['NOTIFICATIONS_ASK_BROWSER3'];?></span> &nbsp; <span onclick='donotaskNotification();closeDiv("allow_notifications");' class='button'><?php echo $LANG['NOTIFICATIONS_ASK_BROWSER4'];?></span></div>
			<div id='ajaxload' class='ajaxload'><img src='images/core/indicator.gif' alt='wait' /></div>
			<div id='screen_info' style='position: fixed; left:50px;width: 180px;display:none; background-color: white;border: 1px solid silver;padding:2px;'></div>
			<div style='text-align:center;vertical-align:middle;position:fixed;display:none;visible:hidden;left: 0px;top: 0px;padding: 8px;background-color:transparent;z-index:111;' id='show_tags'></div><div style='display:none;' class='full_button' id='show_tags_button' onclick='hide_tags_click()'></div>
            
            <?php
    }
     
    function generatePage($pageController, $siteSettings, $rss) {

		html_header($rss);
		
			global $CURRENTUSER;
			global $LANG;
			global $siteSettings;
			global $CURRENTUSERAJAX;

			$login_form = "<div style='position:absolute;background-color:white;margin-left:4px;padding:4px;border: 1px solid black;z-index:100;'>
						<div style='inline-block' id='login'>
				<b><a href=\"javascript:toggleLayer('loginform2');\" title=\"$LANG[LOGIN]\" class='button_mini'>$LANG[LOGIN]</a></b>
				<div id='loginform2' style='display:none;'><br/>
						<form method='post' action='index.php?shard=login&amp;action=proc_login'>     
						$LANG[USERNAME]:<br />
						<input class='search' type='text' size='10' name='login_name' /><br />
						$LANG[PASSWORD]:<br />
						<input class='search' type='password' size='10' name='login_pass' /><br /><br />
				<input class='button' type='submit' value=\"$LANG[LOGIN]\" /></form>";
			if ($CURRENTUSER != "bot") {
				$login_form .= "<div style='margin-top:6px;'><small><a href='".make_link("adduser")."'>$LANG[GET_ACCOUNT]</a></small></div><div><small><a href='".make_link("login","&amp;action=g_recover_pass")."'>Mot de passe oublié ?</a></small></div>";
				if ($siteSettings['verifyEmail'] == "checked")
					$login_form .= "<div><small><a href='".make_link("adduser","&amp;action=g_resendauthent")."'>Mail d'activation</a></small></div>";
			}
			$login_form .= "</div></div></div>";
			
			if ($siteSettings['mobile']) {
				if ($CURRENTUSER != "anonymous" and $CURRENTUSER != "")	{
					print ("<div style='position:absolute;color:white;'>
								<div style='float:left;margin-left:5px;'>
									<div>$LANG[WELCOME_BACK], <span class='bold'>$CURRENTUSER</span>
									</div>
									<div style='margin-top:2px;'><a href='".make_link("login","&amp;action=logout")."' class='button_mini'>$LANG[LOGOUT]</a></div>
								</div>
							</div>");
				}
				else
					print ("$login_form");
			}
			else if ($CURRENTUSER == "anonymous")
					print ("$login_form");
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
				global $CURRENTUSER;
				global $LANG;
				global $mf_version;
				if ($CURRENTUSER != "anonymous")
					$logged = "logged";

				print ("</td><td id=\"onglet_right\"></td><td id=\"onglet_right_".$logged."\"></td><td id=\"corner_right\"></td></tr></table>

				<div style=\"float:left;margin-left:6px;margin-right:6px;\">
					<table cellspacing=\"0\" cellpadding=\"0\">
						<tr>
							<td id=\"border_left\"></td>
							<td style=\"background-color:white;\">
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
  
					print ("<div class=\"footer\" id=\"footer\" style=\"color:#000000;\" >");
					// PLEASE DO NOT REMOVE OR MODIFY THESE LINES [BEGIN] *****
					print ("<div><a style=\"color:#000000;\" href=\"http://www.metafora.fr\">$LANG[POWERED_BY_METAFORA] Metafora.fr</a> $LANG[METAFORA_VERSION]$mf_version ©&nbsp;2004-2011 <a style=\"color:#000000;\" href=\"http://www.blursoft.com/metaForum\">Blursoft</a> &amp; ©&nbsp;2011 Alexis Dury</div><div>$LANG[UNDER_GPL_LICENCE]</div>");
					// [END]*****
					if ($CURRENTUSER != "anonymous")
						$link_mobile_full = "<span onclick=\"fullsite(); return false;\" style='cursor:pointer;font-weight:bold;'>$LANG[SITE_FORMAT_FULL]</span>";
					else
						$link_mobile_full = "<span style='cursor:not-allowed;font-weight:bold;'>$LANG[SITE_FORMAT_FULL]</span>";
					if (!$siteSettings['mobile']) {
						if (!$CURRENTUSERAJAX)
							$link_mobile_full .= " / <span style='color:#555555'>$LANG[SITE_FORMAT_HTML]</span> /";
						else {
							$link_mobile_full = "<span style='color:#555555'>$LANG[SITE_FORMAT_FULL]</span>";
							if ($CURRENTUSER != "anonymous")
								$link_mobile_full .= " / <span onclick=\"fullsite_simple(); return false;\" style='cursor:pointer;font-weight:bold;'>$LANG[SITE_FORMAT_HTML]</span> /";
						}
					}
					else
						$link_mobile_full .= " / <span onclick=\"fullsite_simple(); return false;\" style='cursor:pointer;font-weight:bold;'>$LANG[SITE_FORMAT_HTML]</span> /";
					$link_mobile_plus = "<span onclick=\"mobilesiteplus(); return false;\" style='cursor:pointer;font-weight:bold;'>$LANG[SITE_FORMAT_MOBILE_PLUS]</span> /";
					if ($siteSettings['mobile'] && $siteSettings['full_site'] == "mobilesiteplus")
						$link_mobile_plus = "<span style='color:#555555'>$LANG[SITE_FORMAT_MOBILE_PLUS]</span> /";
					else if ($CURRENTUSER == "anonymous")
						$link_mobile_plus = "";
					$link_mobile = "<span onclick=\"mobilesite(); return false;\" style='cursor:pointer;font-weight:bold;'>$LANG[SITE_FORMAT_MOBILE]</span>";
					if ($siteSettings['mobile'] && $siteSettings['full_site'] != "mobilesiteplus")
						$link_mobile = "<span style='color:#555555'>$LANG[SITE_FORMAT_MOBILE]</span>";
					print ("<div>$link_mobile_full $link_mobile_plus $link_mobile</div>");
					if ($siteSettings['rules'] && $CURRENTUSER != "anonymous")
						print ("<div><a href='".make_link("rules")."'>$LANG[RULES_TITLE0] $siteSettings[titlebase]</a></div>");
					
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
			html_footer();
	}

	
	
	function vMenu($menuArray,$poslr="")
	{
		global $shard, $action;
		print "<!-- google_ad_section_start(weight=ignore) -->";

		$sizemenu = sizeof($menuArray);
		$firstvalue = 0;
		if ($poslr == "left")
				$sizemenu = floor(sizeof($menuArray) / 2);
		else if ($poslr == "right")
			$firstvalue = floor(sizeof($menuArray) / 2);

		for ($i=$firstvalue;$i<$sizemenu;$i++)
		{
		
			switch( $menuArray[$i]->menuType ):

				case "search":
					for( $j=0; $j < sizeof( $menuArray[$i]->menuContentArray); $j++ )
					{
						print $menuArray[$i]->menuContentArray[$j] . "<br />";
					}
				break;

				case "about":
					print ("<h2>".$menuArray[$i]->menuTitle."</h2><br />");
					for( $j=0; $j < sizeof( $menuArray[$i]->menuContentArray); $j++ )
					{
						print $menuArray[$i]->menuContentArray[$j] . "<br /><br />";
					}
				break;
				
				case "nav":
					print ("<div class=\"menuContainer\">
					<ul id=\"miniflex\">");
					for( $j=0; $j < sizeof( $menuArray[$i]->menuContentArray); $j++)
					{	
						$selected = "";
						if (stristr( $menuArray[$i]->menuContentArray[$j] , $shard ))
							$selected = "class='miniflexSelected'";
						
						$height='5px';
						if ($selected != '')
						{
							$height='3px';
						}

						print ("<li ".$selected."><div style=\"padding-top:".$height."\"></div>".$menuArray[$i]->menuContentArray[$j]."</li>");
						if ($j < (sizeof( $menuArray[$i]->menuContentArray)-1))
						{
							print "<li id=\"onglet_separator".$j."\"></li>";
						}
					}
					print "</ul></div>";
				break;
				
				default:
					print ("<li id=\"shardmenu_".$i."\" class=\"shard\" onmouseup=\"sendPosition();\">
					<div class=\"menuWrapper\">
					<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_topleft\"></td>
					<td class=\"shard_title\"><div style=\"padding-top:3px;padding-left:3px;\">".$menuArray[$i]->menuTitle."</div></td>
					<td class=\"shard_topright\"></td></tr></table>
					<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_left\"></td><td class=\"menuInfo\">");
					
					for ($j=0;$j<sizeof($menuArray[$i]->menuContentArray);$j++)

					{
						print("");
						print "<div>".$menuArray[$i]->menuContentArray[$j] . "</div>";
						print("");
					}
					
					print ("</td><td class=\"shard_right\"></td></tr></table>
					<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_bottomleft\"></td><td class=\"shard_bottom\">
					</td><td class=\"shard_bottomright\"></td></tr></table>
					</div>
					</li>");
				break;				

			endswitch;
		}
	print "<!-- google_ad_section_end -->";
	}


	function cookieMenu($menuArray,$shard_pos,$poslr)
	{
		$cookie_tab = explode(",",$_COOKIE[$shard_pos]);
		
		global $shard;
		
		$ident_already = false;	
		$cookie_tab_size = sizeof($cookie_tab);
		for ($i=0;$i<$cookie_tab_size;$i++)
		{
			if ($cookie_tab[$i] != "") {
				$cookie_pos = (int)$cookie_tab[$i];
				print ("<li id=\"shardmenu_".$cookie_pos."\" class=\"shard\" onmouseup=\"sendPosition();\">
				<div class=\"menuWrapper\">
				<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_topleft\"></td>
				<td class=\"shard_title\"><div style=\"padding-top:3px;padding-left:3px;\">".$menuArray[$cookie_pos]->menuTitle."</div></td>
				<td class=\"shard_topright\"></td></tr></table>
				<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_left\"></td><td class=\"menuInfo\">");
			
				$menuArrayCookieSize = sizeof($menuArray[$cookie_pos]->menuContentArray);
				for ($j=0;$j<$menuArrayCookieSize;$j++)
				{
				print "<div>".$menuArray[$cookie_pos]->menuContentArray[$j]."</div>";
				}
				print ("</td><td class=\"shard_right\"></td></tr></table>
				<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_bottomleft\"></td>
				<td class=\"shard_bottom\"></td>
				<td class=\"shard_bottomright\"></td></tr></table>
				</div>
				</li>");
			}
		}
	}
	
	
	
function vContent($contentArray)
{
	global $action;
	global $siteSettings;

	print"<div id=\"content\" class=\"content\">\n";
	
	for ($i=0;$i<sizeof($contentArray);$i++) {
		
		print renderPost($contentArray, $i);

	}

	print "</div>\n";
}

function renderPost($contentArray, $i)
{
	$retStr = "";
	
		//---------------------------------------------
		// Probably have only 2 or 3 different styles
		//---------------------------------------------
		switch ($contentArray[$i]->contentType):
		

		
		case "contentType1":
		case "contentType2":
		case "contentType3":
		
			$alternatePostStyle = "post";
			
			if (($contentArray[$i]->postid % 2) == 0)
				$alternatePostStyle = "post2";
				
			$postID = $contentArray[$i]->postid;

			$retStr .= "<div class='postWrapper' id='postid".$contentArray[$i]->postid."'>\n";
			$retStr .= $contentArray[$i]->anchor;
			$retStr .= "<div style='display:none;' id='currentNRIvalue".$contentArray[$i]->postid."'>".$contentArray[$i]->rating."</div>";
			
			global $CURRENTUSERDTP;
			global $CURRENTUSERRATING;
			global $CURRENTUSERID;
			global $LANG;
			global $CURRENTUSER;
			global $CURRENTUSERAJAX;
			
			$jp = "";
			if ($CURRENTUSERAJAX)
				$jp = "</span>";

			if ($contentArray[$i]->rating == 0)
				$postRatingColorGradient = "postRatingColorGradient1";		
			else if ($contentArray[$i]->rating > 0)
				$postRatingColorGradient = "postRatingColorGradient2";
			else if ($contentArray[$i]->rating < 0)
				$postRatingColorGradient = "postRatingColorGradient3";			
				
			$showPlus = "";
			if ($contentArray[$i]->rating > 0)
				$showPlus = "+";
			
			$upClass = 'uparrowoff';
			$downClass = 'downarrowoff';
			$displayClass = "<div class='postRatingDisplay'><div style='float: right; margin-left: 5px;' class='$postRatingColorGradient' id='ratingDisplaypost".$postID."'>$showPlus".$contentArray[$i]->rating."</div> <div id='postRatingStatus".$postID."' style='float:right;' class='postTitle'>$LANG[RATING] </div></div>";

			$rated = false;
			$userrated = mf_query("SELECT rating FROM postratings WHERE postID = '$postID' AND user = \"$CURRENTUSER\" LIMIT 1");
			if ($userrated = mysql_fetch_array($userrated)) {			

				$rated = true;
				if ($userrated['rating'] > 0) {
					$upClass = 'uparrowon';
					$downClass = 'downarrowoff';
					$displayClass = "<div class='postRatingDisplay'><div style='float: right; margin-left: 5px;' class='$postRatingColorGradient' id='ratingDisplaypost".$postID."'>$showPlus".$contentArray[$i]->rating."</div> <div id='postRatingStatus".$postID."' style='float:right;' class='postTitlePositive'> $LANG[RATED] </div></div>";
				}
				else if ($userrated['rating'] == 0) {
					$upClass = 'uparrowon';
					$downClass = 'downarrowon';
					$displayClass = "<div class='postRatingDisplay'><div style='float: right; margin-left: 5px;' class='$postRatingColorGradient' id='ratingDisplaypost".$postID."'>$showPlus".$contentArray[$i]->rating."</div> <div id='postRatingStatus".$postID."' style='float:right;' class='postTitlePositive'> $LANG[RATED] </div></div>";
				}
				else {
					$downClass = 'downarrowon';
					$upClass = 'uparrowoff';
					$displayClass = "<div class='postRatingDisplay'><div style='float: right; margin-left: 5px;' class='$postRatingColorGradient' id='ratingDisplaypost".$postID."'>$showPlus".$contentArray[$i]->rating."</div> <div id='postRatingStatus".$postID."' style='float:right;' class='postTitleNegative'> $LANG[RATED] </div></div>";
				}
			}			
			if (!$contentArray[$i]->rating)
				$contentArray[$i]->rating = "0";
			if ($contentArray[$i]->rating < $CURRENTUSERDTP && $CURRENTUSER != $contentArray[$i]->author)
			{
				$hiddenLine = "display: block;";
				$normalLine = "display: none;";
			}
			else
			{
				$hiddenLine = "display: none;";
				$normalLine = "display: block;";
			}


				$retStr .= "<div class=\"post-top\" style='$hiddenLine' id='hidden".$contentArray[$i]->postid."'>
							<div style='float: left; display: block;' class='postTitle'>" .$contentArray[$i]->title . "," . $LANG['POSTEDBY2'] . "<b>" . $contentArray[$i]->author . "</b>" . $LANG['DATE_LINE_FULL2'] . date($LANG['DATE_LINE_TIME'], $contentArray[$i]->dateCreated) . " " . $LANG['ON'] . " " . date($LANG['DATE_LINE_MINIMAL2'], $contentArray[$i]->dateCreated) . "</div>";

				$retStr .= "</div>\n";
						
				
				
				$retStr .= "<div class=\"post-top\" style='$normalLine' id='normal".$contentArray[$i]->postid."'>";
				$retStr .= "<div class='rate_container'>";
				$retStr .= "<div id='up_rate".$postID."' class='rate_up'><select class='up_rate' name='up_rate".$postID."' onchange=\"selectRate(this.options[this.selectedIndex].value,'up_rate".$postID."','".$postID."','".$CURRENTUSER."');\">";
				$retStr .= $_SESSION['option_up'];
				$retStr .= "</select></div>";
				
				$retStr .= "<div id='down_rate".$postID."' class='rate_down'><select class='down_rate' onchange=\"selectRate(this.options[this.selectedIndex].value,'down_rate".$postID."','".$postID."','".$CURRENTUSER."');\">";
				$retStr .= $_SESSION['option_down'];
				$retStr .= "</select></div>";
				$retStr .= "</div>";
				
				$retStr .= "<div style='float: left; display: block;' class='postTitle'>" .$contentArray[$i]->title . "," .$LANG['AT2'] . date($LANG['DATE_LINE_TIME'], $contentArray[$i]->dateCreated) . " " . $LANG['ON'] . " " . date($LANG['DATE_LINE_MINIMAL2'], $contentArray[$i]->dateCreated) . "&nbsp;&nbsp;" . $contentArray[$i]->subText2 . "</div>";
				if (($CURRENTUSERID != $contentArray[$i]->userID) && isset($CURRENTUSERRATING))
				{	
					$retStr .= "<div id='arrowpost".$postID."'>
						<div onclick=\"already_rated = setRateVisible('up_rate".$postID."','".$postID."','down_rate".$postID."','".$rated."',already_rated); toggleRatingArrow('post', $postID, 'uparrow', ".number_format($CURRENTUSERRATING, 2).");\" id='uparrowpost".$postID."' class='$upClass'></div>
						<div onclick=\"already_rated = setRateVisible('down_rate".$postID."','".$postID."','up_rate".$postID."','".$rated."',already_rated); toggleRatingArrow('post', $postID, 'downarrow', ".number_format($CURRENTUSERRATING, 2).");\" id='downarrowpost".$postID."' class='$downClass'></div>
						</div>";
				}	

				$retStr .=" $displayClass
						</div>\n";


			$retStr .= "<div style='$hiddenLine' id=\"hiddenpost". $contentArray[$i]->postid ."\"><center><small>$LANG[POST_BELOW_THRESHOLD] (<a href=\"javascript:toggleLayer('post". $contentArray[$i]->postid ."');toggleLayer('hiddenpost". $contentArray[$i]->postid ."');toggleLayer('hidden". $contentArray[$i]->postid ."');toggleLayer('normal". $contentArray[$i]->postid ."');\">$LANG[SHOW_ANYWAY]</a>)</small></center></div>";
			$retStr .= "<div id=\"postedit". $contentArray[$i]->postid ."\" style='display:none;margin-left:-3px;'></div>";
			$retStr .= "<div style='$normalLine clear: both;' class=\"$alternatePostStyle\" id=\"post". $contentArray[$i]->postid ."\"><div class='postUserInfo'>";
			
			$retStr .= "<div class='avatar'>";
			
			if ($contentArray[$i]->picture != "")
				$retStr .= "<img class='avatarPicture' alt='$LANG[AVATAR]' src=\"". $contentArray[$i]->picture ."\" />";
			else
				$retStr .= "( $LANG[USER_PROFILE_NO_AVATAR] )";
				
			$retStr .= "</div>";
			
			$tooLongName = "";
			if (strlen($contentArray[$i]->author) > 13)
				$tooLongName = "style='letter-spacing: -1px;'";
				
			if ($CURRENTUSER != "anonymous")
			{
				$retStr .= "<a $tooLongName href='".make_link("forum","&amp;action=g_ep&amp;ID=".$contentArray[$i]->userID,"#user/".$contentArray[$i]->userID)."'>";
				if ($jp and !strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
					$retStr .= "<span onclick=\"userprofile(0,". $contentArray[$i]->postid .",".$contentArray[$i]->userID."); return false;\">";
				$retStr .= $contentArray[$i]->author."$jp</a> <br/><span class='userNriDisplay'>".$contentArray[$i]->userRating."</span></div>";
			}
			else
				$retStr .= $contentArray[$i]->author." <br/></div>";

			$retStr .= "<div id='postContent".$contentArray[$i]->postid."' class='postContent'><!-- google_ad_section_start -->". $contentArray[$i]->primaryContent . "<!-- google_ad_section_end -->" . "";
			$retStr .= "<div class='sig'><br/>".stripslashes($contentArray[$i]->sig)."</div>";

			$retStr .= "</div><div class='clearfix'></div></div></div>
						<div class='postContextmenu' onmousedown=\"newcontexmenu(event,$postID);return false;\"></div>";
			
		break;

		
		case "contentArticle":
			
			//------------------------------------------------------------------------
			// Define the way a content article should appear.  Follow examples above.
			// All data is accessed via the $contentArray[$i] object.
			// See clsContentObj.php for all the different fields available.
			//------------------------------------------------------------------------
			
		break;
		
		
		
		case "generic":
		
			$retStr .="<div class='generic'>";
			
			
			$retStr .= "<h2>" . $contentArray[$i]->title . "</h2>";
			
			$retStr .= "";
			$retStr .= $contentArray[$i]->primaryContent ."";
			$retStr .= "</div>";
		
		
		break;
		
		
		
		//--------------------------------------------------------------------
		// clsContentObj.contentType to display if none is selected (generic)
		//--------------------------------------------------------------------
		default:
			
			$retStr .="";
			
			
			$retStr .= "" . $contentArray[$i]->title . "";
			
			$retStr .= "";
			$retStr .= $contentArray[$i]->primaryContent ."";
			$retStr .= "";			
		break;
		
		
		
		
		endswitch;	
		
		return $retStr;
	
}

    function html_footer() {
		global $LANG;
		global $siteSettings;
		global $CURRENTUSER;
		global $fbcookie;
		global $FACEBOOK_OFF;

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
/*		print ("<!--<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"\" id=\"blinger\" height=\"1\" width=\"1\">
			<param name=movie value=\"engine/core/blinger2.swf\">
			<embed play=false swliveconnect=\"true\" name=\"blinger\" src=\"engine/core/blinger2.swf\" quality=high width=1 height=1 type=\"application/x-shockwave-flash\">
			</embed>
			</object>-->");*/
		print ("<div id=\"flashcontent\">
			  .
			</div>");
			
			?>
			
			<script type="text/javascript"> /* <![CDATA[ */
				
	var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			   string: navigator.userAgent,
			   subString: "iPhone",
			   identity: "iPhone/iPod"
	    },
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};
BrowserDetect.init();

				var mfbrowser = BrowserDetect.browser;
				var mfversion = BrowserDetect.version;
				if (document.cookie.indexOf('old_browser') == -1 && document.getElementById('errorPaneText').innerHTML == "") {
					if (mfbrowser == "Firefox" && mfversion < 3.5)
						displayError("<div onclick=\'closeError();\' class=\'closeButton\'></div><?php echo $LANG['OLD_BROWSER_TEXT1'];?> " + mfbrowser + " " + mfversion + "<div style='height:6px;'></div><?php echo $LANG['OLD_BROWSER_FIREFOX1'];echo $siteSettings['titlebase'];?>.<div style='height:6px;'></div><?php echo $LANG['OLD_BROWSER_TEXT3'];echo $siteSettings['titlebase'];echo $LANG['OLD_BROWSER_TEXT4'];echo $LANG['OLD_BROWSER_FIREFOX2'];?><u><a href='http://www.getfirefox.com' target='_blank'>Mozilla</a></u><div style='height:6px;'></div><a href='http://www.mozilla-europe.org/fr/' target='_blank'><img src='http://www.mozilla-europe.org/img/tignish/home/feature-logo.png' /></a><div style='height:6px;'></div><input type='checkbox' name='ff_dontbother' id='ff_dontbother' style='vertical-align:sub;' /> <?php echo $LANG['OLD_BROWSER_DONT'];?>");
					else if (mfbrowser == "Explorer" && mfversion < 7)
						displayError("<div onclick=\'closeError();\' class=\'closeButton\'></div><?php echo $LANG['OLD_BROWSER_TEXT1'];?> " + mfbrowser + " " + mfversion + "<div style='height:6px;'></div><?php echo $LANG['OLD_BROWSER_TEXT2'];?><div style='height:6px;'></div><?php echo $LANG['OLD_BROWSER_TEXT3'];echo $siteSettings['titlebase'];echo $LANG['OLD_BROWSER_TEXT4'];?> <div style='height:6px;'></div><a href='http://www.mozilla-europe.org/fr/' target='_blank'><img src='images/core/firefox_mini.png' /></a><a href='http://www.opera.com' target='_blank'><img src='images/core/opera.png' /></a><a href='http://www.google.fr/chrome' target='_blank'><img src='images/core/chrome.jpg' /></a><a href='http://www.apple.com/fr/safari/' target='_blank'><img src='images/core/safari.png' /></a><div style='height:6px;'></div><input type='checkbox' name='ie_dontbother' id='ie_dontbother' /> <?php echo $LANG['OLD_BROWSER_DONT'];?>");
				}
				var basewidth = 854;
				var main_width = 819;
				var footer_width = 720;
				var border_bottom_width = 819;
				var onglet_table_width = 827;
				if (document.getElementById('onglet_right_logged'))
					var ongletright_width = 257;
				else
					var ongletright_width = 346;
				var img_width = 664;

				if (document.body.clientWidth)
					var cwidth_tp = document.body.clientWidth;
				else
					var cwidth_tp = document.body.offsetWidth;
				content_width(cwidth_tp);
				window.onbeforeunload = confirmExit;
				setTimeout("checksystem()", 300000);

				var so = new SWFObject("engine/core/alert3.swf", "mymovie", "1", "1", "4", "#FFFFFF");
				
				if (currentuser != "anonymous")
				{
					var menu_shard = $$('#menu_shard_left, #menu_shard_right');
					mySortables = new Sortables(menu_shard,{handle:'#title_handle', opacity: .5});
					sendPosition();
				}


				var sidebar_space = document.getElementById('sidebar_space_left').style.height;
				if (savewidth >= 1170 && sidebar_space == '0px') {
					savewidth = 0;
					verifywidth();
				}
				else if (savewidth < 1170 && sidebar_space != '0px') {
					savewidth = 1280;
					verifywidth();
				}

				function verifywidth()
				{
					if (document.body.clientWidth)
						var cwidth = document.body.clientWidth;
					else
						var cwidth = document.body.offsetWidth;

					content_width(cwidth);

					if (savewidth > 1170 && cwidth < 1170) {
						document.getElementById('sidebar_right2').innerHTML = document.getElementById('sidebar_left1').innerHTML;
						document.getElementById('sidebar_left1').innerHTML = "";
						if (document.getElementById('sidebar_space_left'))
							document.getElementById('sidebar_space_left').style.height = "0px";
						savewidth = cwidth;
						SetCookie('screen_width',cwidth,300);
						menu_shard = $$('#menu_shard_left, #menu_shard_right');
						mySortables = new Sortables(menu_shard,{handle:'#title_handle', opacity: .5});
						sendPosition();
						content_width(cwidth);
					}
					if (savewidth < 1170 && cwidth > 1170) {
						document.getElementById('sidebar_left1').innerHTML = document.getElementById('sidebar_right2').innerHTML;
						document.getElementById('sidebar_right2').innerHTML = "";
						if (document.getElementById('sidebar_space_left'))
							document.getElementById('sidebar_space_left').style.height = "200px";
						savewidth = cwidth;
						SetCookie('screen_width',cwidth,300);
						if (currentuser != "anonymous") {
							menu_shard = $$('#menu_shard_left, #menu_shard_right');
							mySortables = new Sortables(menu_shard,{handle:'#title_handle', opacity: .5});
							sendPosition();
						}
					}
				}

				function shards_priority()
				{
					var shards_priority = document.getElementById('shards_priority');
					if (shards_priority.checked == true)
						SetCookie('shards_priority', 'yes', -1);
					else
						SetCookie('shards_priority', 'not', 365);
						
					if (document.body.clientWidth)
						var cwidth = document.body.clientWidth;
					else
						var cwidth = document.body.offsetWidth;
						
					content_width(cwidth);
				}

				function content_width(cwidth)
				{

					if (cwidth < 1040)
						document.getElementById('shards_priority_div').style.display = "block";
					else
						document.getElementById('shards_priority_div').style.display = "none";
					if (GetCookie('shards_priority') != "not" && (document.getElementById('widgets_left').offsetWidth <= 8 || document.getElementById('widgets_right').offsetWidth <= 8))
						basewidth = 1040;
					else
						basewidth = 854;
					var reducewidth = basewidth - cwidth;
					if (reducewidth >= 244)
						reducewidth = 244;
					else if (reducewidth < 0)
						reducewidth = 0;

					document.getElementById('main').style.width = (main_width - reducewidth) + "px";
					document.getElementById('footer').style.width = (footer_width - reducewidth) + "px";
					document.getElementById('border_bottom').style.width = (border_bottom_width - reducewidth) + "px";

					var imgmaxwidth = img_width - reducewidth;
					newClass('img','max-width:' + imgmaxwidth + 'px;');
					document.getElementById('onglet_table').style.width = (onglet_table_width - reducewidth) + "px";
					if (document.getElementById('onglet_right_logged'))
						var ongletright = document.getElementById('onglet_right_logged');
					else
						var ongletright = document.getElementById('onglet_right_anonymous');
					ongletright.style.width = (ongletright_width - reducewidth) + "px";
				}

				function sortMyShards(position)
				{
					var sortShard = document.getElementById('menu_shard_' + position).getChildren();
					var shardOrder = "";
					var virg = "";
					for (i = 0; i < sortShard.length; i++)
					{
						if (sortShard[i].getProperty('id')){
							var current_shard = sortShard[i].getProperty('id').replace('shardmenu_','');

							if (current_shard != "sidebar_space_left" && current_shard != "sidebar_space_right") {
								shardOrder = shardOrder + virg + current_shard;
								virg = ",";
							}
						}
					}

					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace(',closed','');
					shardOrder = shardOrder.replace('closed,','');
					shardOrder = shardOrder.replace('closed','');

					if (shardOrder == "0,")
					{
						shardOrder = "0";
					}

					return shardOrder;
				}

				function closeShard(shard_id)
				{
					if (currentuser != "anonymous") {
						div_name = "shardmenu_" +shard_id;
						document.getElementById(div_name).innerHTML = "";
						document.getElementById(div_name).setAttribute('id','closed');
						sendPosition();
					}
				}

				function restoreShards()
				{
					SetCookie('shard_left','',-1);
					SetCookie('shard_right','',-1);
					window.location.reload();
				}
				
			
				if (currentuser != "anonymous") {
					if (document.cookie.indexOf('shard_left') == -1) {
						var cookie_shard_left = GetCookie('shard_left');
						var shard_list_left = mySortables.serialize();
						SetCookie('shard_left',shard_list_left,300);
					}
					if (document.cookie.indexOf('shard_right') == -1) {
						var cookie_shard_right = GetCookie('shard_right');
						var shard_list_right = mySortables.serialize();
						SetCookie('shard_right',shard_list_right,300);
					}
				}
				function sendPosition()
				{
					if (currentuser != "anonymous")
					{
						duree = setTimeout("execPosition()",100);
					}
				}
				function execPosition()
				{
					var shard_list = sortMyShards('left');
					SetCookie('shard_left',shard_list,300);
					var shard_list = sortMyShards('right');
					SetCookie('shard_right',shard_list,300);
				}

			/* ]]> */</script>			
			
			<?php
			if (defined("FACEBOOK_APP_ID") && ($fbcookie || $CURRENTUSER == "anonymous" || !$FACEBOOK_OFF))
				print("<div id=\"fb-root\"></div>
<script type=\"text/javascript\">
  FB.init({appId: '".FACEBOOK_APP_ID."', status: true, cookie: true, xfbml: true,reloadIfSessionStateChanged: true});
  FB.Event.subscribe('auth.sessionChange', function(response) {
    if (response.session) {
      window.location.reload();
    } else {
      window.location.reload();
    }
  });
</script>");

			if (file_exists("analytics.html"))
				include("analytics.html");
			print ("
			</body>\n
            </html>");
    }
?>