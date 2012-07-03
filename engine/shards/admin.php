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

// admin.php


	require("user_profilelib.php");

	////////////////////
	// ajax functions //
	////////////////////
	function ajax_addNewModOption($dataline) {
		global $CURRENTUSER;

		if (isInGroup($CURRENTUSER, 'admin')) {

			$datalineArray = explode("::@noption@::", $dataline);
			$tag = utf8_encode($datalineArray[1]);
			$tag = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $tag);
			$tag = html_entity_decode($tag, ENT_NOQUOTES, 'UTF-8');
			$tag = trim($tag);
			$tag = str_replace("::@plus@::","+",$tag);
			$tag = str_replace("::@euro@::","€",$tag);
			$tag = make_var_safe(htmlspecialchars($tag));

			$add = mf_query("insert into postratingcomments (posneg, comment) values ($datalineArray[0], \"$tag\")");

			return $datalineArray[0] . "::" . createModList($datalineArray[0]);
		}
	}
	
	function ajax_changeGraft($newGraft) {
		global $CURRENTUSER;

		if (isInGroup($CURRENTUSER, 'admin')) {

			$check = mf_query("select count(*) as expr1 from settings");
			$check = mysql_fetch_assoc($check);

			if ($check['expr1'] > 0)		
				$changeGraft = mf_query("update settings set graft='$newGraft' where 1;");
			else
				$changeGraft = mf_query("insert into settings (graft) VALUES ('$newGraft')");

			return 1;
		}
	}

	
	sajax_init();
	
	include("ajax_commonlib.php");

	sajax_export("ajax_addNewModOption", "ajax_changeGraft"); // list of functions to export
	sajax_handle_client_request(); // serve client instances	
	
	//////////////
	// end ajax //
	//////////////
	

	function menuOptions() {
		global $LANG;
		global $CURRENTUSER;
		$retStr = "<div id='adminOptionsList'>";
		if (isInGroup($CURRENTUSER, 'admin')) {
			$retStr .= "<a href='".make_link("admin","&amp;action=g_modifyChannels")."' class='button'>$LANG[MODIFY_CHANNELS]</a> &nbsp; 
				<a href='".make_link("admin","&amp;action=g_tags")."' class='button'>$LANG[TAG_MANAGEMENT]</a> &nbsp; 
				<a href='".make_link("admin","&amp;action=g_modifyModOptions")."' class='button'>$LANG[MODERATING_OPTIONS]</a> &nbsp;";
			$retStr .= "<div style='height:16px;'></div>";
			$retStr .= "<a href='".make_link("admin","&amp;action=g_chooseGraft")."' class='button'>$LANG[SITE_GRAFT]</a> &nbsp; 
				 <a href='".make_link("admin","&amp;action=g_customCSS")."' class='button'>$LANG[CUSTOM_CSS]</a> &nbsp;
				 <a href='".make_link("admin","&amp;action=g_widgets")."' class='button'>$LANG[WIDGETS_SETTINGS]</a> &nbsp;
				 <a href='".make_link("admin","&amp;action=g_fb")."' class='button'>$LANG[FB_SETTINGS]</a>
				 <div style='height:16px;'></div>
				<a href='".make_link("admin","&amp;action=g_banned_users")."' class='button'>$LANG[BANNED_USERS]</a> &nbsp; ";
			if (isInGroup($CURRENTUSER, 'sysadmin') && isInGroup($CURRENTUSER, 'admin'))
				$retStr .= "<a href='".make_link("admin","&amp;action=g_clean_users")."' class='button'>$LANG[CLEAN_USERS_DATABASE]</a> &nbsp; ";
			$retStr .= "<a href='".make_link("admin","&amp;action=g_rename_user")."' class='button'>$LANG[ADMIN_RENAME_USER]</a> &nbsp; 
				 <a href='".make_link("admin","&amp;action=g_group")."' class='button'>$LANG[GROUP_MANAGEMENT]</a>";
			$retStr .= "<div style='height:16px;'></div>";
			$retStr .= "<a href='".make_link("admin","&amp;action=g_options")."' class='button'>$LANG[SITE_OPTIONS]</a> &nbsp; ";
			$retStr .= "<a href='".make_link("admin","&amp;action=g_siteSettings")."' class='button'>$LANG[SITE_SETTINGS]</a> &nbsp; ";
		}
		if (isInGroup($CURRENTUSER, 'sysadmin'))
			$retStr .= "<a href='".make_link("admin","&amp;action=g_sysSettings")."' class='button'>$LANG[SYS_SETTINGS]</a> &nbsp; ";
		$retStr .= "</div>";
		return $retStr;
	}

	function findChildren($cs, $cpnl) {
		global $LANG;
		$children = array();	
		while ($row = mysql_fetch_assoc($cs)) {
			if ($row['parent_id'] == $cpnl)
				$children[] = $row;			
		}	
		mysql_data_seek($cs, 0);

		if (count($children) > 0) {
			$rs = "";		
			foreach($children as $c) {
				$rs .= "<div style='margin-left: 15px;'><b>- $c[name]</b> - $c[num_posts] $LANG[POSTS], $c[num_threads] $LANG[THREADS] - <small>[ <a href='".make_link("admin","&amp;action=g_edit&amp;editID=$c[ID]'").">$LANG[EDIT]</a> | <a href='".make_link("admin","&amp;action=g_confirmDelete&amp;delID=$c[ID]")."' title=\"$LANG[DELETE_CHANNEL]\">$LANG[DELETE]</a> | <a href='".make_link("admin","&amp;action=g_moveChannel&amp;moveID=$c[ID]")."'>$LANG[MOVE]</a> | <a href='".make_link("admin","&amp;action=g_add&amp;parent_id=$c[ID]")."'>$LANG[ADD_SUBCHANNEL]</a> ]</small>";
				$rs .= findChildren($cs, $c['ID']);
				$rs .= "</div>\r\n\t";
			}		
			return $rs;		
		}
		else // exit condition
			return "";	
	}
	
	function createModList($posneg) {
		global $LANG;
		global $siteSettings;
		$retStr = "";
		if ($posneg == 0) {
			$posOptions = mf_query("select * from postratingcomments where posneg=0");
			while ($row=mysql_fetch_assoc($posOptions)) {
				$retStr .= "$row[comment] <a href='index.php?shard=admin&amp;action=delete&amp;ID=$row[ID]' title='$LANG[DELETE]'>
					<img src='engine/grafts/$siteSettings[graft]/images/b_drop.png' style='vertical-align:middle;' alt='$LANG[DELETE]' /></a><br/>";
			}
		}
		else {
			$posOptions = mf_query("select * from postratingcomments where posneg=1");
			while ($row=mysql_fetch_assoc($posOptions)) {
				$retStr .= "$row[comment] <a href='index.php?shard=admin&amp;action=delete&amp;ID=$row[ID]' title='$LANG[DELETE]'>
				<img src='engine/grafts/$siteSettings[graft]/images/b_drop.png' style='vertical-align:middle;' alt='$LANG[DELETE]' /></a><br/>";
			}
		}

		return $retStr;
	}
	
	function updateThreadLastPostInfo($threadID) {
		$u = mf_query("select user, date, ID from forum_posts where threadID=$threadID order by date desc limit 1");
		if ($row = mysql_fetch_assoc($u)) {
			$u = mf_query("update forum_topics set last_post_id=$row[ID], last_post_user=\"$row[user]\", last_post_date=$row[date], num_comments=(num_comments - 1) where ID=$threadID limit 1");
		}
	}	


    
    if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'sysadmin')) {

		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->title = "$LANG[ADMIN_SITE_OPTIONS]";
		$thisContentObj->primaryContent = "<div style='margin-top:8px;'>$LANG[ADMIN_CP_WELCOME]</div>";
		$thisContentObj->primaryContent .= menuOptions();

		$server_name = $_SERVER['SERVER_NAME'];
		$version = mf_query("select version, date, comment, reset_widgets from version where site = \"$server_name\" order by version DESC limit 1");
		$version = mysql_fetch_assoc($version);

		$url_release = "http://www.metafora.fr/version.php?version=$mf_version&server_url=".urlencode($_SERVER['HTTP_HOST']);
		$lastrelease_version = @file($url_release);

		$thisContentObj->primaryContent .= "<div style='text-align:center;color:silver;'>$LANG[ADMIN_INSTALLED_VERSION]: $mf_version / $LANG[ADMIN_JAVASCRIPT_VERSION]: $versionj / $LANG[ADMIN_INTERNAL_VERSION]: $version[version]</div>";
	if ($lastrelease_version[0] > $mf_version)
			$thisContentObj->primaryContent .= "<div style='text-align:center;color:green;'>$LANG[ADMIN_UPDATED_VERSION1] ($lastrelease_version[0]) $LANG[ADMIN_UPDATED_VERSION2] <a href='http://www.metafora.fr' title='' style='color:green;'>www.metafora.fr</a></div>";

		$shardContentArray[] = $thisContentObj;

        switch ($action):

        case "g_default":
        break;


        case "g_siteSettings":
		if (isInGroup($CURRENTUSER, 'admin')) {
			$thisContentObj = New contentObj;
			$thisContentObj->contentType = "generic";
			$thisContentObj->title = "$LANG[SITE_SETTINGS]";


			$channellist = "";
			$channels = mf_query("select name, ID from categories");
			while ($row = mysql_fetch_assoc($channels)) {
				$S = "";
				if ($siteSettings['dChannel'] == $row['ID'])
					$S = "selected='selected'";

				$channellist .= "<option $S value='$row[ID]'>$row[name]</option>";
			}

			$channelsignallist = "";
			$channels = mf_query("select name, ID from categories");
			while ($row = mysql_fetch_assoc($channels)) {
				$S = "";
				if ($siteSettings['channel_signal'] == $row['ID'])
					$S = "selected='selected'";

				$channelsignallist .= "<option $S value='$row[ID]'>$row[name]</option>";
			}

			$channelfloodlist = "<option value=''></option>";
			$channels = mf_query("select name, ID from categories");
			while ($row = mysql_fetch_assoc($channels)) {
				$S = "";
				if ($siteSettings['flood_ID'] == $row['ID'])
					$S = "selected='selected'";

				$channelfloodlist .= "<option $S value='$row[ID]'>$row[name]</option>";
			}

			$channelintrolist = "";
			$channels = mf_query("select name, ID from categories");
			while ($row = mysql_fetch_assoc($channels)) {
				$S = "";
				if ($siteSettings['introduce_ID'] == $row['ID'])
					$S = "selected='selected'";

				$channelintrolist .= "<option $S value='$row[ID]'>$row[name]</option>";
			}

			$listteams = "";
			$listteams_modo = "";
			$teams = mf_query("SELECT teamID, teamName FROM teams ORDER BY teamName");
			while ($row = mysql_fetch_assoc($teams)) {
				$S = "";
				if ($siteSettings['teamadmin'] == $row['teamID'])
					$S = "selected='selected'";
				$Smodo = "";
				if ($siteSettings['teammodo'] == $row['teamID'])
					$Smodo = "selected='selected'";

				$listteams .= "<option $S value='$row[teamID]'>$row[teamName]</option>";
				$listteams_modo .= "<option $Smodo value='$row[teamID]'>$row[teamName]</option>";
			}
			$checkedverifyEmail = "";
			if ($siteSettings['verifyEmail'])
				$checkedverifyEmail = "checked='checked'";
			$checkedrules = "";
			if ($siteSettings['rules'])
				$checkedrules = "checked='checked'";
			$maxfilesize = $siteSettings['team_maxfilesize'] / 1024;
			$maxpicturesize = $siteSettings['picture_maxfilesize'] / 1024;
			
			$checkedquoteall = "";
			if ($siteSettings['quote_all_post'])
				$checkedquoteall = "checked='checked'";
			
			$checkedviewmodlist= "";
			if ($siteSettings['viewmodlist'])
				$checkedviewmodlist = "checked='checked'";
			
			$checkedchange_page = "";
			if ($siteSettings['change_page'])
				$checkedchange_page = "checked='checked'";
			
			$checkedhide_filters = "";
			if ($siteSettings['hide_filters'])
				$checkedhide_filters = "checked='checked'";
			
			$viewmodlist = "<select name='viewmodlist' class='bselect'><option value='0'>$LANG[ADMIN_VIEWMODLIST_0]</option>";
			if ($siteSettings['module_friends']) {
				$selected = "";
				if ($siteSettings['viewmodlist'] == "1")
					$selected = "selected='selected'";
				$viewmodlist .= "<option value='1' $selected>$LANG[ADMIN_VIEWMODLIST_1]</option>";
				$selected = "";
				if ($siteSettings['viewmodlist'] == "2")
					$selected = "selected='selected'";
				$viewmodlist .= "<option value='2' $selected>$LANG[ADMIN_VIEWMODLIST_2]</option>";
			}
			$selected = "";
			if ($siteSettings['viewmodlist'] == "3")
				$selected = "selected='selected'";
			$viewmodlist .= "<option value='3' $selected>$LANG[ADMIN_VIEWMODLIST_3]</option>";

			$thisContentObj->primaryContent .= "<br/><br/><form action='index.php?shard=admin&amp;action=submitSiteSettings' method='post'>
				<table cellpadding='2'>
				<tr>
				<td></td>
				<td class='bold' style='height:32px;vertical-align:bottom;font-size:1.1em;'>$LANG[ADMIN_OPTIONS_SUB1]</td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[SITE_TITLE]:</td>
				<td><input class='bselect' size='50' type='text' name='titlebase' value=\"$siteSettings[titlebase]\" /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[SITE_DESC]:</td>
				<td><input class='bselect' size='50' type='text' name='titledesc' value=\"$siteSettings[titledesc]\" /></td>
				</tr>
				<tr>
				<td style='text-align:right;vertical-align:top;'>$LANG[SITE_KEYWORDS]:</td>
				<td><textarea class='bselect' cols='60' rows='2' name='sitekeywords'>$siteSettings[sitekeywords]</textarea></td>
				</tr>
				<tr>
				<td></td>
				<td class='bold' style='height:32px;vertical-align:bottom;font-size:1.1em;'>$LANG[ADMIN_OPTIONS_SUB2]</td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[ADMIN_MAIL]</td>
				<td><input class='bselect' size='30' type='text' name='admin_mail' value='$siteSettings[admin_mail]' /></td>
				</tr>
				<tr>
				<tr>
				<td style='text-align:right;'>$LANG[ALERT_MAIL]</td>
				<td><input class='bselect' size='30' type='text' name='alert_mail' value='$siteSettings[alert_mail]' /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[BURIED_LIMIT]:</td>
				<td><input class='bselect' size='8' type='text' name='buriedlimit' value='$siteSettings[buriedlimit]' /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[RULES_ADMIN1]:</td>
				<td><input class='bselect' type='checkbox' name='rules' $checkedrules /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[RULES_ADMIN2]:</td>
				<td><input class='bselect' size='8' type='text' name='rulesthread' value='$siteSettings[rulesthread]' /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[RULES_PICTURES]:</td>
				<td><input class='bselect' size='8' type='text' name='rulespictures' value='$siteSettings[rulespictures_thread]' /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[RULES_ET]:</td>
				<td><input class='bselect' size='8' type='text' name='ruleset' value='$siteSettings[rules_et_thread]' /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[TEAM_FILE_MAX_SIZE]:</td>
				<td><input class='bselect' size='8' type='text' name='maxfilesize' value='$maxfilesize' /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[PICTURE_FILE_MAX_SIZE]:</td>
				<td><input class='bselect' size='8' type='text' name='maxpicturesize' value='$maxpicturesize' /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[ADMIN_TEAM]:</td>
				<td><select class='bselect' name='teamadmin'><option value='0'><option>$listteams</select></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[MODO_TEAM]:</td>
				<td><select class='bselect' name='teammodo'><option value='0'><option>$listteams_modo</select></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[ADMIN_NUM_MODS_TO_BAN]:</td>
				<td><input class='bselect' type='text' size='1' name='num_mods_to_ban' value='$siteSettings[num_mods_to_ban]' /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[CHANNEL_SIGNAL_NUM]:</td>
				<td><select class='bselect' name='channel_signal'>".$channelsignallist."</select></td>
				</tr>
				<tr>
				<td></td>
				<td class='bold' style='height:32px;vertical-align:bottom;font-size:1.1em;'>$LANG[ADMIN_OPTIONS_SUB3]</td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[VERIFY_EMAIL]</td>
				<td><input class='bselect' type='checkbox' name='verifyEmail' $checkedverifyEmail /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[DEFAULT_CHANNEL]:</td>
				<td><select class='bselect' name='channel'>".$channellist."</select></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[ADMIN_FLOOD]:</td>
				<td><select class='bselect' name='flood_ID'>".$channelfloodlist."</select></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[ADMIN_INTRODUCE]:</td>
				<td><select class='bselect' name='introduce_ID'>".$channelintrolist."</select></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[ADMIN_QUOTE_ALL_POST]:</td>
				<td><input class='bselect' type='checkbox' name='quoteall' $checkedquoteall /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[ADMIN_VIEWMODLIST]:</td>
				<td>$viewmodlist</td>
				</tr>
				<tr>
				<td></td>
				<td class='bold' style='height:32px;vertical-align:bottom;font-size:1.1em;'>$LANG[ADMIN_OPTIONS_SUB4]</td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[CHANGE_PAGE_ANONYMOUS]:</td>
				<td><input class='bselect' type='checkbox' name='change_page' $checkedchange_page /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[HIDE_FILTERS_ANONYMOUS]:</td>
				<td><input class='bselect' type='checkbox' name='hide_filters' $checkedhide_filters /></td>
				</tr>
				<tr>
				<td></td><td><input class='button' type='submit' value=\"$LANG[SAVE_SETTINGS]\" /></td>
				</tr>
				</table>
				</form>";


			//------------------------------------------------------------------------------
			// Add this contentObject to the shardContentArray
			//------------------------------------------------------------------------------
			$shardContentArray[] = $thisContentObj;
		}
        break;

        // Change site title and desc
        case "submitSiteSettings":
        if (isInGroup($CURRENTUSER, 'admin')) {
			$ve = "checked";
        	if (!isset($_POST['verifyEmail']))
        		$ve = "";

			$quoteall = 0;
        	if (isset($_POST['quoteall']))
        		$quoteall = 1;

			$rules = "";
        	if (isset($_POST['rules']))
        		$rules = "checked";

			$change_page = 0;
        	if (isset($_POST['change_page']))
        		$change_page = 1;

			$hide_filters = 0;
        	if (isset($_POST['hide_filters']))
        		$hide_filters = 1;

			$rulesthread = "";
			if ($_POST['rulesthread'])
				$rulesthread = make_num_safe($_POST['rulesthread']);
			if ($rulesthread == "")
				$rules = "";

			$rulespictures = "";
			if ($_POST['rulespictures'])
				$rulespictures = make_num_safe($_POST['rulespictures']);

			$ruleset = "";
			if ($_POST['ruleset'])
				$ruleset = make_num_safe($_POST['ruleset']);

			$buriedlimit = "-1.0";
			if ($_POST['buriedlimit'])
				$buriedlimit = make_num_safe($_POST['buriedlimit']);

			$floodID = NULL;
			if ($_POST['flood_ID'])
				$floodID = make_num_safe($_POST['flood_ID']);

			$introduceID = NULL;
			if ($_POST['introduce_ID'])
				$introduceID = make_num_safe($_POST['introduce_ID']);

			$teamadmin = "";
			if (is_numeric($_POST['teamadmin']))
				$teamadmin = $_POST['teamadmin'];

			$num_mods_to_ban = "1";
			if (is_numeric($_POST['num_mods_to_ban']) && $_POST['num_mods_to_ban'] > 1)
				$num_mods_to_ban = $_POST['num_mods_to_ban'];

			$viewmodlist = "0";
			if (is_numeric($_POST['viewmodlist']))
				$viewmodlist = $_POST['viewmodlist'];

			$team_maxfilesize = "1";
			if (is_numeric($_POST['maxfilesize']))
				$team_maxfilesize = $_POST['maxfilesize'];
			$picture_maxfilesize = "1";
			if (is_numeric($_POST['maxpicturesize']))
				$picture_maxfilesize = $_POST['maxpicturesize'];

			$teammodo = "";
			if (is_numeric($_POST['teammodo']))
				$teammodo = $_POST['teammodo'];

			$admin_mail = "";
			if ($_POST['admin_mail'])
				$admin_mail = make_var_safe($_POST['admin_mail']);
			
			$alert_mail = "";
			if ($_POST['alert_mail'])
				$alert_mail = make_var_safe($_POST['alert_mail']);

			$keywords = make_var_safe($_POST['sitekeywords']);
			$titlebase = make_var_safe($_POST['titlebase']);
			$titledesc = make_var_safe($_POST['titledesc']);
			$dChannel = make_var_safe($_POST['channel']);
			$channel_signal = make_num_safe($_POST['channel_signal']);
			
	
        	mf_query("update settings set titlebase=\"$titlebase\", titledesc=\"$titledesc\", dChannel='$dChannel', verifyemail='$ve', rules='$rules', rulesthread='$rulesthread', rulespictures_thread='$rulespictures', rules_et_thread='$ruleset', flood_ID='$floodID', introduce_ID = '$introduceID', buriedlimit='$buriedlimit', admin_mail='$admin_mail', alert_mail='$alert_mail', keywords=\"$keywords\", teamadmin = '$teamadmin', teammodo = '$teammodo', team_maxfilesize = '$team_maxfilesize', picture_maxfilesize = '$picture_maxfilesize', quote_all_post = '$quoteall', viewmodlist = '$viewmodlist', num_mods_to_ban = '$num_mods_to_ban', change_page = '$change_page', hide_filters = '$hide_filters', channel_signal = '$channel_signal' WHERE 1");
			
			header("Location: ".make_link("admin"));
		}
        break;


		// SYSTEM SETTINGS
        case "g_sysSettings":
		if (isInGroup($CURRENTUSER, 'sysadmin')) {
			$thisContentObj = New contentObj;
			$thisContentObj->contentType = "generic";
			$thisContentObj->primaryContent = "<div style='font-size:1.6em;'>$LANG[SYS_SETTINGS] - $server_name</div>";

			$resetchecked = "";
			if ($version['reset_widgets'])
				$resetchecked = "checked='checked'";

		$GetsiteSettings = mf_query("SELECT * FROM settings LIMIT 1");
		$GetsiteSettings = mysql_fetch_assoc($GetsiteSettings);

			$langList = "";

			$checkedmod_rewrite = "";
			if ($siteSettings['mod_rewrite'])
				$checkedmod_rewrite = "checked='checked'";

			$thisContentObj->primaryContent .= "<br/><br/><form action='index.php?shard=admin&amp;action=submitSysSettings' method='post'>
				<table cellpadding='2'>
				<tr>
				<td style='text-align:right;'>$LANG[SITE_URL]:</td>
				<td><input class='bselect' size='50' type='text' name='siteurl' value='$siteSettings[siteurl]' /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[URL_REWRITE]:</td>
				<td><input class='bselect' type='checkbox' name='mod_rewrite' $checkedmod_rewrite /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[LANGUAGE]:</td><td><select class='bselect' name='language'>";

        	$handle = opendir( "engine/core/lang/" );
        	while ( ($langfile = readdir( $handle )) != FALSE ) {

				if ( $langfile != "." && $langfile != ".." && $langfile != ".svn" ) {
					$S = "";
					$langfile = str_replace(".php", "", $langfile);

				if ($GetsiteSettings['lang'] == $langfile)
						$S = "selected='selected'";
						
					$thisContentObj->primaryContent .= "<option $S value=\"$langfile\">$langfile</option>";
				}
			} 																		
			$thisContentObj->primaryContent .= "</select></td></tr>";
			
			$timezones = array(
				'Pacific/Midway'    => "(GMT-11:00) Midway Island",
				'US/Samoa'          => "(GMT-11:00) Samoa",
				'US/Hawaii'         => "(GMT-10:00) Hawaii",
				'US/Alaska'         => "(GMT-09:00) Alaska",
				'US/Pacific'        => "(GMT-08:00) Pacific Time (US &amp; Canada)",
				'America/Tijuana'   => "(GMT-08:00) Tijuana",
				'US/Arizona'        => "(GMT-07:00) Arizona",
				'US/Mountain'       => "(GMT-07:00) Mountain Time (US &amp; Canada)",
				'America/Chihuahua' => "(GMT-07:00) Chihuahua",
				'America/Mazatlan'  => "(GMT-07:00) Mazatlan",
				'America/Mexico_City' => "(GMT-06:00) Mexico City",
				'America/Monterrey' => "(GMT-06:00) Monterrey",
				'Canada/Saskatchewan' => "(GMT-06:00) Saskatchewan",
				'US/Central'        => "(GMT-06:00) Central Time (US &amp; Canada)",
				'US/Eastern'        => "(GMT-05:00) Eastern Time (US &amp; Canada)",
				'US/East-Indiana'   => "(GMT-05:00) Indiana (East)",
				'America/Bogota'    => "(GMT-05:00) Bogota",
				'America/Lima'      => "(GMT-05:00) Lima",
				'America/Caracas'   => "(GMT-04:30) Caracas",
				'Canada/Atlantic'   => "(GMT-04:00) Atlantic Time (Canada)",
				'America/La_Paz'    => "(GMT-04:00) La Paz",
				'America/Santiago'  => "(GMT-04:00) Santiago",
				'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
				'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
				'Greenland'         => "(GMT-03:00) Greenland",
				'Atlantic/Stanley'  => "(GMT-02:00) Stanley",
				'Atlantic/Azores'   => "(GMT-01:00) Azores",
				'Atlantic/Cape_Verde' => "(GMT-01:00) Cape Verde Is.",
				'Africa/Casablanca' => "(GMT) Casablanca",
				'Europe/Dublin'     => "(GMT) Dublin",
				'Europe/Lisbon'     => "(GMT) Lisbon",
				'Europe/London'     => "(GMT) London",
				'Africa/Monrovia'   => "(GMT) Monrovia",
				'Europe/Amsterdam'  => "(GMT+01:00) Amsterdam",
				'Europe/Belgrade'   => "(GMT+01:00) Belgrade",
				'Europe/Berlin'     => "(GMT+01:00) Berlin",
				'Europe/Bratislava' => "(GMT+01:00) Bratislava",
				'Europe/Brussels'   => "(GMT+01:00) Brussels",
				'Europe/Budapest'   => "(GMT+01:00) Budapest",
				'Europe/Copenhagen' => "(GMT+01:00) Copenhagen",
				'Europe/Ljubljana'  => "(GMT+01:00) Ljubljana",
				'Europe/Madrid'     => "(GMT+01:00) Madrid",
				'Europe/Paris'      => "(GMT+01:00) Paris",
				'Europe/Prague'     => "(GMT+01:00) Prague",
				'Europe/Rome'       => "(GMT+01:00) Rome",
				'Europe/Sarajevo'   => "(GMT+01:00) Sarajevo",
				'Europe/Skopje'     => "(GMT+01:00) Skopje",
				'Europe/Stockholm'  => "(GMT+01:00) Stockholm",
				'Europe/Vienna'     => "(GMT+01:00) Vienna",
				'Europe/Warsaw'     => "(GMT+01:00) Warsaw",
				'Europe/Zagreb'     => "(GMT+01:00) Zagreb",
				'Europe/Athens'     => "(GMT+02:00) Athens",
				'Europe/Bucharest'  => "(GMT+02:00) Bucharest",
				'Africa/Cairo'      => "(GMT+02:00) Cairo",
				'Africa/Harare'     => "(GMT+02:00) Harare",
				'Europe/Helsinki'   => "(GMT+02:00) Helsinki",
				'Europe/Istanbul'   => "(GMT+02:00) Istanbul",
				'Asia/Jerusalem'    => "(GMT+02:00) Jerusalem",
				'Europe/Kiev'       => "(GMT+02:00) Kyiv",
				'Europe/Minsk'      => "(GMT+02:00) Minsk",
				'Europe/Riga'       => "(GMT+02:00) Riga",
				'Europe/Sofia'      => "(GMT+02:00) Sofia",
				'Europe/Tallinn'    => "(GMT+02:00) Tallinn",
				'Europe/Vilnius'    => "(GMT+02:00) Vilnius",
				'Asia/Baghdad'      => "(GMT+03:00) Baghdad",
				'Asia/Kuwait'       => "(GMT+03:00) Kuwait",
				'Europe/Moscow'     => "(GMT+03:00) Moscow",
				'Africa/Nairobi'    => "(GMT+03:00) Nairobi",
				'Asia/Riyadh'       => "(GMT+03:00) Riyadh",
				'Europe/Volgograd'  => "(GMT+03:00) Volgograd",
				'Asia/Tehran'       => "(GMT+03:30) Tehran",
				'Asia/Baku'         => "(GMT+04:00) Baku",
				'Asia/Muscat'       => "(GMT+04:00) Muscat",
				'Asia/Tbilisi'      => "(GMT+04:00) Tbilisi",
				'Asia/Yerevan'      => "(GMT+04:00) Yerevan",
				'Asia/Kabul'        => "(GMT+04:30) Kabul",
				'Asia/Yekaterinburg' => "(GMT+05:00) Ekaterinburg",
				'Asia/Karachi'      => "(GMT+05:00) Karachi",
				'Asia/Tashkent'     => "(GMT+05:00) Tashkent",
				'Asia/Kolkata'      => "(GMT+05:30) Kolkata",
				'Asia/Kathmandu'    => "(GMT+05:45) Kathmandu",
				'Asia/Almaty'       => "(GMT+06:00) Almaty",
				'Asia/Dhaka'        => "(GMT+06:00) Dhaka",
				'Asia/Novosibirsk'  => "(GMT+06:00) Novosibirsk",
				'Asia/Bangkok'      => "(GMT+07:00) Bangkok",
				'Asia/Jakarta'      => "(GMT+07:00) Jakarta",
				'Asia/Krasnoyarsk'  => "(GMT+07:00) Krasnoyarsk",
				'Asia/Chongqing'    => "(GMT+08:00) Chongqing",
				'Asia/Hong_Kong'    => "(GMT+08:00) Hong Kong",
				'Asia/Irkutsk'      => "(GMT+08:00) Irkutsk",
				'Asia/Kuala_Lumpur' => "(GMT+08:00) Kuala Lumpur",
				'Australia/Perth'   => "(GMT+08:00) Perth",
				'Asia/Singapore'    => "(GMT+08:00) Singapore",
				'Asia/Taipei'       => "(GMT+08:00) Taipei",
				'Asia/Ulaanbaatar'  => "(GMT+08:00) Ulaan Bataar",
				'Asia/Urumqi'       => "(GMT+08:00) Urumqi",
				'Asia/Seoul'        => "(GMT+09:00) Seoul",
				'Asia/Tokyo'        => "(GMT+09:00) Tokyo",
				'Asia/Yakutsk'      => "(GMT+09:00) Yakutsk",
				'Australia/Adelaide' => "(GMT+09:30) Adelaide",
				'Australia/Darwin'  => "(GMT+09:30) Darwin",
				'Australia/Brisbane' => "(GMT+10:00) Brisbane",
				'Australia/Canberra' => "(GMT+10:00) Canberra",
				'Pacific/Guam'      => "(GMT+10:00) Guam",
				'Australia/Hobart'  => "(GMT+10:00) Hobart",
				'Australia/Melbourne' => "(GMT+10:00) Melbourne",
				'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
				'Australia/Sydney'  => "(GMT+10:00) Sydney",
				'Asia/Vladivostok'  => "(GMT+10:00) Vladivostok",
				'Asia/Magadan'      => "(GMT+11:00) Magadan",
				'Pacific/Auckland'  => "(GMT+12:00) Auckland",
				'Pacific/Fiji'      => "(GMT+12:00) Fiji",
				'Asia/Kamchatka'    => "(GMT+12:00) Kamchatka",
			);
			$thisContentObj->primaryContent .= "<tr>
				<td style='text-align:right;'>$LANG[TIMEZONE]:</td><td><select class='bselect' name='timezone'>";
			foreach ($timezones as $tzname => $tzdesc) {
				$S = "";
				if ($tzname == $SSDB['timezone'])
					$S = "selected='selected'";
				$thisContentObj->primaryContent .= "<option $S value=\"$tzname\">$tzname $tzdesc</option>";
			} 																		
			$thisContentObj->primaryContent .= "</select></td></tr>";

		$crypt_method_sha1 = "checked='checked'";
		$crypt_method_blowfish = "";
		if (CRYPT_BLOWFISH == 1 && $GetsiteSettings['crypt_method'] == "blowfish") {
			$crypt_method_sha1 = "";
			$crypt_method_blowfish = "checked='checked'";
		}

			$thisContentObj->primaryContent .= "<tr>
				<td style='text-align:right;'>$LANG[THREAD_UPDATE]:</td>
				<td><input class='bselect' size='3' type='text' name='threadupdate' value='".$siteSettings['threadupdate'] / 1000 ."' />$LANG[WAIT_TO_POST2]</td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[POST_UPDATE]:</td>
				<td><input class='bselect' size='3' type='text' name='postupdate' value='".$siteSettings['postupdate'] / 1000 ."' />$LANG[WAIT_TO_POST2]</td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[LOADAVG]:</td>
				<td><input class='bselect' size='8' type='text' name='loadavg' value='$siteSettings[loadavg]' /> (loadavg)</td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[MESSAGE]:</td>
			<td><input class='bselect' size='100' type='text' name='message' value=\"".$GetsiteSettings['message']."\" /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[VERSION_INTERNAL]</td>
				<td><input class='bselect' size='10' type='text' name='version' value='$version[version]' />
				$LANG[RESET_SHARDS]? <input type='checkbox' class='bselect' name='reset_widgets' $resetchecked />
				<input type='hidden' name='date' value='$version[date]' />
				<input type='hidden' name='oldversion' value='$version[version]' />
				</td>
				</tr>
				<tr>
				<td style='text-align:right;vertical-align:top;'>$LANG[RECAPTCHA_SETTINGS]</td>
				<td>
				<div>$LANG[RECAPTCHA_PUBLIC_KEY] <input class='bselect' size='50' type='text' name='recaptcha_pubKey' value=\"".$GetsiteSettings['recaptcha_pubKey']."\" /></div>
				<div>$LANG[RECAPTCHA_PRIVATE_KEY] <input class='bselect' size='50' type='text' name='recaptcha_privKey' value=\"".$GetsiteSettings['recaptcha_privKey']."\" /></div>
				</td>
				</tr>";
		if (CRYPT_BLOWFISH == 1)
			$thisContentObj->primaryContent .= "<tr>
			<td style='text-align:right;vertical-align:top;'>$LANG[CRYPT_METHOD]</td>
			<td>
				<div><input class='bselect' type='radio' name='crypt_method' value='sha1' $crypt_method_sha1 /> SHA1</div>
				<div><input class='bselect' type='radio' name='crypt_method' value='blowfish' $crypt_method_blowfish /> CRYPT Blowfish</td>
			</tr>";

			$thisContentObj->primaryContent .= "<tr>
				<td></td><td><input class='button' type='submit' value=\"$LANG[SAVE_SETTINGS]\" /></td>
				</tr>
				</table>
				</form>";


			//------------------------------------------------------------------------------
			// Add this contentObject to the shardContentArray
			//------------------------------------------------------------------------------
			$shardContentArray[] = $thisContentObj;
		}
        break;

        // Change system settings
        case "submitSysSettings":
		if (isInGroup($CURRENTUSER, 'sysadmin')) {
			$mod_rewrite = "checked";
        	if (!isset($_POST['mod_rewrite']))
        		$mod_rewrite = "";

			$loadavg = "";
			if ($_POST['loadavg'])
				$loadavg = make_num_safe($_POST['loadavg']);

			$threadupdate = make_num_safe($_POST['threadupdate']);
			if ($threadupdate < 5)
				$threadupdate = 5;

			$postupdate = make_num_safe($_POST['postupdate']);
			if ($postupdate < 5)
				$postupdate = 5;

			$url = make_var_safe($_POST['siteurl']);
			if (substr($url,0,7) == "http://")
				$url = substr($url,7,strlen($url) - 7);
	
			$timezone = make_var_safe($_POST['timezone']);
			$message = make_var_safe($_POST['message']);
			$recaptcha_privKey = make_var_safe($_POST['recaptcha_privKey']);
			$recaptcha_pubKey = make_var_safe($_POST['recaptcha_pubKey']);

		$crypt_method = "";
		if (isset($_POST['crypt_method']) && $_POST['crypt_method'] == "blowfish")
			$crypt_method = "blowfish";
	
		mf_query("UPDATE settings SET lang='$_POST[language]', loadavg='$loadavg', mod_rewrite='$mod_rewrite', siteurl=\"$url\", threadupdate='$threadupdate', postupdate='$postupdate', message=\"$message\", recaptcha_privKey = \"$recaptcha_privKey\", recaptcha_pubKey = \"$recaptcha_pubKey\", timezone = \"$timezone\", crypt_method = '$crypt_method' WHERE 1");

			$server_name = $_SERVER['SERVER_NAME'];
			$version = make_var_safe($_POST['version']);
			$reset_widgets = "0";
        	if (isset($_POST['reset_widgets']))
        		$reset_widgets = "1";
			if ($_POST['date'] && ($_POST['version'] == $_POST['oldversion']))
				$version_date = make_num_safe($_POST['date']);
			else
				$version_date = time();

			mf_query("delete from version where site = \"$server_name\" and version = \"$version\" limit 1");
			mf_query("insert into version (site, version, date, reset_widgets) value (\"$server_name\", \"$version\", '$version_date', '$reset_widgets')");

			header("Location: ".make_link("admin"));
		}
        break;
        
		// Choose Widgets
		case "g_widgets":
		if (isInGroup($CURRENTUSER, 'sysadmin')) {

			$inactives_widgets = "";
			$actives_widgets = "";
			
			if (isset($_REQUEST["add"])) {
				$siteSettings['widgets'] .= ",".$_REQUEST["add"];
				mf_query("UPDATE settings SET widgets = \"$siteSettings[widgets]\"");
				header("Location: ".make_link("admin","&action=g_widgets#title"));
				exit();
			}
			else if (isset($_REQUEST["remove"])) {
				if ($_REQUEST["remove"] == "m_login")
					exit();
				$siteSettings['widgets'] = "";
				$virg = "";
				for ($i=0; $i < sizeof($widgets); $i++) {
					if ($widgets[$i] && $widgets[$i] != $_REQUEST["remove"]) {
						$siteSettings['widgets'] .= $virg.$widgets[$i];
						$virg = ",";
					}
				}
				mf_query("UPDATE settings SET widgets = \"$siteSettings[widgets]\"");
				header("Location: ".make_link("admin","&action=g_widgets#title"));
				exit();
			}
			else if (isset($_REQUEST["down"])) {
				$key = array_search($_REQUEST["down"], $widgets);
				$keyPlus = $key + 1;
				$widgetTemp = $widgets[$keyPlus];
				$widgets[$keyPlus] = $widgets[$key];
				$widgets[$key] = $widgetTemp;
				$siteSettings['widgets'] = "";
				$virg = "";
				for ($i=0; $i < sizeof($widgets); $i++) {
					if ($widgets[$i]) {
						$siteSettings['widgets'] .= $virg.$widgets[$i];
						$virg = ",";
					}
				}
				mf_query("UPDATE settings SET widgets = \"$siteSettings[widgets]\"");
				header("Location: ".make_link("admin","&action=g_widgets#title"));
				exit();
			}
			else if (isset($_REQUEST["up"])) {
				$key = array_search($_REQUEST["up"], $widgets);
				$keyMinus = $key - 1;
				$widgetTemp = $widgets[$keyMinus];
				$widgets[$keyMinus] = $widgets[$key];
				$widgets[$key] = $widgetTemp;
				$siteSettings['widgets'] = "";
				$virg = "";
				for ($i=0; $i < sizeof($widgets); $i++) {
					if ($widgets[$i]) {
						$siteSettings['widgets'] .= $virg.$widgets[$i];
						$virg = ",";
					}
				}
				mf_query("UPDATE settings SET widgets = \"$siteSettings[widgets]\"");
				header("Location: ".make_link("admin","&action=g_widgets#title"));
				exit();
			}
			$thisContentObj->primaryContent .= "<div style='font-size:2em;margin-top:16px;margin-bottom:16px;'><a name='title'> </a>$LANG[WIDGETS_SETTINGS_TITLE]</div>";
			$thisContentObj->primaryContent .= "<div class='bold' style='margin-bottom:16px;'>$LANG[WIDGETS_WARNING]</div>";
			$handle = opendir( "engine/shards/" );
        	while (($widgetfile = readdir($handle)) != false) {
				if (substr($widgetfile,0,2) == "m_" && substr($widgetfile,-4) == ".php" && substr($widgetfile,-9) != "_edit.php" && substr($widgetfile,-8) != "_nav.php")
					$widgetfileArray[] = str_replace(".php", "", $widgetfile);
			}
			for ($i=0; $i < sizeof($widgets); $i++) {
				$actives_widgets .= "<div class='row'>";
				if ($widgets[$i] == "m_login")
					$actives_widgets .= "<div class='widgets_set_login'>";
					else
					$actives_widgets .= "<div onclick=\"location.href='".make_link("admin","&amp;action=g_widgets&remove=$widgets[$i]")."';\" class='widgets_set'>";
				$actives_widgets .= $widgets[$i];
				$actives_widgets .= "</div><div class='cell'>";
				if ($i < sizeof($widgets))
					$actives_widgets .= "&nbsp;<a href='".make_link("admin","&amp;action=g_widgets&down=$widgets[$i]")."' style=''><img src='engine/grafts/$siteSettings[graft]/images/downarrowoff.gif' alt=''/></a>";
				$actives_widgets .= "</div><div class='cell'>";
				if ($i >= 1)	
					$actives_widgets .= "<a href='".make_link("admin","&amp;action=g_widgets&up=$widgets[$i]")."' style=''><img src='engine/grafts/$siteSettings[graft]/images/uparrowoff.gif' alt=''/></a>";
				$actives_widgets .= "</div></div>";
			}

			for ($i=0; $i < sizeof($widgetfileArray); $i++) {
				if (!in_array($widgetfileArray[$i], $widgets))
					$inactives_widgets .= "<div class='row'><div onclick=\"location.href='".make_link("admin","&amp;action=g_widgets&add=$widgetfileArray[$i]")."';\" class='widgets_set'>".$widgetfileArray[$i]."</div></div>";
			}
			$thisContentObj->primaryContent .= "<div>$LANG[WIDGETS_ENABLED]</div><div class='widgets_set_box'>$actives_widgets</div><div>$LANG[WIDGETS_DISABLED]</div><div class='widgets_set_box'>$inactives_widgets</div>";
		}
		break;
		
	// Facebook Settings
		case "g_fb":
		if (isInGroup($CURRENTUSER, 'sysadmin')) {
			$thisContentObj->primaryContent .= "<div style='font-size:2em;margin-top:16px;margin-bottom:16px;'>$LANG[FB_SETTINGS_T]</div>";
			$thisContentObj->primaryContent .= "<form name='fbset' action='index.php?shard=admin&amp;action=writefb' method='post'>
												FACEBOOK_APP_ID : <input type='text' value='' name='appid'/> &nbsp; 
												FACEBOOK_SECRET : <input type='text' value='' name='secret'/>
												<input type='submit' value=\"$LANG[SUBMIT_EDIT]\" class='button'/>
												</form>";
		}
		break;

		case "writefb":
		if (isInGroup($CURRENTUSER, 'sysadmin')) {
			$content = "<?php
//---------------------------------------------------
// settings for Facebook connectivity
//
//---------------------------------------------------
define('FACEBOOK_APP_ID', '".$_POST['appid']."');
define('FACEBOOK_SECRET', '".$_POST['secret']."');


?>";
			$fp = fopen("engine/core/fbsettings.php", 'w+');
			fputs($fp, $content);
			fclose($fp);
			
			header("Location: ".make_link("admin"));
		}
		break;

        // Choose site graft
        case "g_chooseGraft":
        if (isInGroup($CURRENTUSER, 'admin')) {
			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[CHOOSE_GRAFT]";
        	$thisContentObj->primaryContent = "$LANG[GRAFT_INSTRUCT].<br/><br/>";        

        	$handle = opendir( "engine/grafts/" );
        	while ( ($graft = readdir( $handle )) != FALSE ) {
				if ( $graft != "." && $graft != ".." && $graft != ".svn" && $graft != "core-styles" && substr($graft,-4) != ".php" && substr($graft,-7) != "_mobile") {

					$thisContentObj->primaryContent .= "<input name='chooseGraft' type='radio' onclick=\"chooseGraft('$graft');\" /> $LANG[GRAFT]: <b>" . $graft . "</b><br/>";

					if (file_exists("engine/grafts/$graft/images/screenshot.jpg"))
						$thisContentObj->primaryContent .= "<img src='engine/grafts/$graft/images/screenshot.jpg' alt='screenshot' />";

					$thisContentObj->primaryContent .= "<br/><br/><hr width='500' align='left'/>";
				}
			}
			$shardContentArray[] = $thisContentObj;
		}
        break;

        // Modify Moderating Options
        case "g_modifyModOptions":        
		if (isInGroup($CURRENTUSER, 'admin')) {
			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[MOD_MOD_OPTIONS]";
        	$thisContentObj->primaryContent = "<br/><br/>";

        	$thisContentObj->primaryContent .= "
				<center><table><tr>
				<td valign='top'><h3><b>$LANG[POS_OPTIONS]: </b></h3><br/><br/>
				<div id='posOptionsHolder'>";

			$thisContentObj->primaryContent .= createModList(0);

			$thisContentObj->primaryContent .= "</div></td>											
				<td valign='top'><h3><b>$LANG[NEG_OPTIONS]: </b></h3><br/><br/> 
    			<div id='negOptionsHolder'>";

			$thisContentObj->primaryContent .= createModList(1);

			$thisContentObj->primaryContent .= "</div></td></tr><tr><td>
				<input class='bselect' type='text' name='addNewPosOption' id='addNewPosOption' size='20' />
				<input onclick='javascript: addOption(0);' class='button' type='submit' value=\"$LANG[ADD_MOD]\" />
				 &nbsp;&nbsp;</td><td>
				<input class='bselect' type='text' name='addNewNegOption' id='addNewNegOption' size='20' />
				<input onclick='javascript: addOption(1);' class='button' type='submit' value=\"$LANG[ADD_MOD]\" /></td>
				</tr></table></center>";


        	$shardContentArray[] = $thisContentObj;
		}
        break;

        case "delete":
		if (isInGroup($CURRENTUSER, 'admin')) {	
			if( is_numeric( $_REQUEST['ID']))
        	   $del = mf_query("delete from postratingcomments where ID=$_REQUEST[ID]");
            header("Location: ".make_link("admin","&action=g_modifyModOptions"));
        }
		break;        

        // Modify Channel Listings
        case "g_modifyChannels":
        if (isInGroup($CURRENTUSER, 'admin')) {	
			
        	$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[MODIFY_CHANNELS]";
        	$thisContentObj->primaryContent = "<br/><br/>";

        	$allChannels = mf_query("select * from categories order by nb");
        	$thisContentObj->primaryContent .= "<div class='modifyChannelListing'>[ <a href='".make_link("admin","&amp;action=g_add&amp;parent_id=0")."'>$LANG[ADD_TOP_CHAN]</a> ]".findChildren($allChannels, 0)."</div>";

			$shardContentArray[] = $thisContentObj;
		}
        break;        

        case "g_confirmDelete":
		if (isInGroup($CURRENTUSER, 'admin')) {
			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[CONFIRM_DELETE]";
        	$thisContentObj->primaryContent = "<br/><br/>";

        	// Check to see if it's the only channel!
        	$checkChannels = mf_query("select count(ID) as Expr1 from categories");
        	$checkChannels = mysql_fetch_assoc($checkChannels);

        	if ($checkChannels['Expr1'] < 2) {
				$thisContentObj->primaryContent .= "$LANG[DEL_WARNING]<br/><br/>
					<a href='index.php?shard=admin&amp;action=deleteChannel&amp;force=true&amp;ID=$_REQUEST[delID]'>$LANG[YES]</a>  -- 
					<a href='".make_link("admin","&amp;action=g_modifyChannels")."'>$LANG[NO]</a>";
			}
			else {
				$cInfo = mf_query("select * from categories where ID=$_REQUEST[delID]");
				$cInfo = mysql_fetch_assoc($cInfo);

				$thisContentObj->primaryContent .= "$LANG[DELETING_CHANNEL]: <b>$cInfo[name].</b>  $cInfo[num_threads] $LANG[THREADS], $cInfo[num_posts] $LANG[POSTS].<br/>";
				$thisContentObj->primaryContent .= "$LANG[DEL_CHANNEL_WARN].<br/><br/><form action='index.php?shard=admin&amp;action=deleteChannel' method='post'>";
				$thisContentObj->primaryContent .= "<b>$LANG[MOVE_TP_TO]:</b><br/><div style='margin-left: 10px;'>";
				$getchannels = mf_query("select * from categories order by num_threads desc, num_posts desc");

				while ($row = mysql_fetch_assoc($getchannels))
				{
					$thisContentObj->primaryContent .= "<input type='radio' name='channelToMoveTo' value='$row[ID]' />$row[name]<br/>";
				}
				
				$thisContentObj->primaryContent .= "<br/>
					<input type='hidden' name='ID' value='$_REQUEST[delID]' />
					<input class='button' type='submit' value=\"$LANG[DELETE_CHANNEL]\" /></form></div>";
			}
        	
        	$shardContentArray[] = $thisContentObj;
		}
        break;

        case "deleteChannel":
        if (isInGroup($CURRENTUSER, 'admin')) {
			if ($_REQUEST['force'] == "true")
        		$del = mf_query("delete from categories where ID=$_REQUEST[ID]");
       	 	else {
				if( (is_numeric($_REQUEST['ID'])) && (is_numeric($_REQUEST['channelToMoveTo'])) ) {
					$cInfo = mf_query("select * from categories where ID=$_REQUEST[ID]");
					if ($cInfo = mysql_fetch_assoc($cInfo)) {
						$changeChan = mf_query("update categories set num_posts=num_posts + $cInfo[num_posts], num_threads=num_threads + $cInfo[num_threads] where ID=$_REQUEST[channelToMoveTo]");
						$changeThreads = mf_query("update forum_topics set category=$_REQUEST[channelToMoveTo] where category=$_REQUEST[ID]");
						$changeChild = mf_query("update categories set parent_id = $cInfo[parent_id] where parent_id=$_REQUEST[ID]");
						$del = mf_query("delete from categories where ID=$_REQUEST[ID]");
					}
				}
				else {
					print "$LANG[NO_CHANNEL_TO_MOVE_TO] - $_REQUEST[ID] , $_REQUEST[channelToMoveTo]";
					exit();
				}
			}
            header("Location: ".make_link("admin","&action=g_modifyChannels"));
		}
        break;

        case "g_moveChannel":
		if (isInGroup($CURRENTUSER, 'admin')) {
			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[CONFIRM_MOVE]";
        	$thisContentObj->primaryContent = "<br/><br/>";

			$cInfo = mf_query("select * from categories where ID=$_REQUEST[moveID]");
			$cInfo = mysql_fetch_assoc($cInfo);

			$thisContentObj->primaryContent .= "$LANG[MOVING_CHANNEL]: <b>$cInfo[name].</b>  <br/>";
			$thisContentObj->primaryContent .= "$LANG[MOVE_CHANNEL_WARN].<br/><br/>
												<form action='index.php?shard=admin&amp;action=moveChannel' method='post'>";
			$thisContentObj->primaryContent .= "<b>$LANG[MOVE_CHANNEL_TO]:</b><br/><div style='margin-left: 10px;'>";

			// Allow option for top level moving 
			$thisContentObj->primaryContent .= "<input type='radio' name='channelToMoveTo' value='0' />$LANG[TOP_LVL_NOCHAN]<br/>";

			$getchannels = mf_query("select * from categories order by num_threads desc, num_posts desc");

			while ($row = mysql_fetch_assoc($getchannels)) {
				if ($row['ID'] != $_REQUEST['moveID'])
					$thisContentObj->primaryContent .= "<input type='radio' name='channelToMoveTo' value='$row[ID]' />$row[name]<br/>";
			}

			$thisContentObj->primaryContent .= "<br/>
					<input type='hidden' name='ID' value='$_REQUEST[moveID]' />
					<input class='button' type='submit' value=\"$LANG[MOVE_CHAN]\" /></form></div>";
				
        	$shardContentArray[] = $thisContentObj;				
		}
        break;

        case "moveChannel":
		if (isInGroup($CURRENTUSER, 'admin')) {
			if( (is_numeric($_REQUEST['ID'])) && (is_numeric($_REQUEST['channelToMoveTo'])) ) {
				$moveChannel = mf_query("update categories set parent_id = $_REQUEST[channelToMoveTo] where ID=$_REQUEST[ID] limit 1");
        	}
        	else {
				print "$LANG[NO_CHANNEL_TO_MOVE_TO2] - $_REQUEST[ID] , $_REQUEST[channelToMoveTo]";
				exit();
			}
            header("Location: ".make_link("admin","&action=g_modifyChannels"));
		}
        break;

        // g_edit 
        case "g_edit":
		if (isInGroup($CURRENTUSER, 'admin')) {	
			if( is_numeric( $_REQUEST['editID'])) {
                $editid = make_num_safe($_REQUEST['editID']);
        	
            	$channel = mf_query("select * from categories where ID='$editid'");
            	$channel = mysql_fetch_assoc($channel);

            	$thisContentObj = New contentObj;
            	$thisContentObj->contentType = "generic";
            	$thisContentObj->title = "$LANG[EDIT_CHAN]";

    			$thisContentObj->primaryContent = "
					<form action='index.php?shard=admin&amp;action=submitEdit&amp;editID=$editid' method='post'>
    				<br/>$LANG[CHANNEL_NAME]:<br/>
    				<input type='text' name='name' size='50' value=\"$channel[name]\" /><br/><br/>
    				$LANG[CHANNEl_DESC]:<br/>
    				<input type='text' name='description' size='100' value=\"$channel[description]\" /><br/><br/>
    				$LANG[ORDER]:	<input type='text' name='nb' size='3' value='$channel[nb]' /><br/><br/>
					$LANG[CHANNEL_NOT_NRI]: <input type='checkbox' name='not_nri' $channel[not_nri] /><br/><br/>
    				<input type='submit' value=\"$LANG[SUBMIT_EDIT]\" class='button' />
    				</form>";
				$thisContentObj->primaryContent .= "<div style='font-weight:bold;margin-top:6px;margin-bottom:6px;'>$LANG[OR]</div>
					<a href='".make_link("admin","&amp;action=g_convert_to_tag&amp;editID=$editid")."' class='button'>$LANG[CHANNEL_TO_TAG]</a>";

    			$shardContentArray[] = $thisContentObj;           	
        	}
		}
        break;


        case "submitEdit":
		if (isInGroup($CURRENTUSER, 'admin')) {	
			if( is_numeric($_REQUEST['editID'])) {
				$not_nri = "";
				if (isset($_REQUEST['not_nri']))
					$not_nri = "checked";

        	   $edit = mf_query("update categories set name='$_REQUEST[name]', description='$_REQUEST[description]', nb='$_REQUEST[nb]', not_nri='$not_nri' where ID=$_REQUEST[editID]");
        	}
            header("Location: ".make_link("admin","&action=g_modifyChannels"));
		}
        break;

		case "g_convert_to_tag":
		if (isInGroup($CURRENTUSER, 'admin')) {
		
                $editid = make_num_safe($_REQUEST['editID']);
        	
            	$thisContentObj = New contentObj;
            	$thisContentObj->contentType = "generic";
            	$thisContentObj->title = "$LANG[CHANNEL_TO_TAG2]";
				$thisContentObj->primaryContent = "<br/><br/>";

				$parentChannel = mf_query("select name from categories where ID='$editid' LIMIT 1");
   				$parentChannel = mysql_fetch_assoc($parentChannel);
				$chan_tag = mb_strtolower(trim($parentChannel['name']),'UTF-8');
   				$thisContentObj->primaryContent .= "<form action='index.php?shard=admin&amp;action=convert_to_tag' method='post'>";
				$thisContentObj->primaryContent .= "<input type='hidden' value='$editid' name='editid' />";
				$thisContentObj->primaryContent .= "<div>$LANG[TAG]: <input type='text' name='tag_name' class='bselect' size='16' value=\"$chan_tag\" /></div>";
				$thisContentObj->primaryContent .= "$LANG[CHANNEL_TO_TAG3] \"<b>$parentChannel[name]</b>\" $LANG[CHANNEL_TO_TAG4]";
				$thisContentObj->primaryContent .= "<input type='checkbox' name='delete_conf' class='bselect' />";
				$thisContentObj->primaryContent .= "&nbsp;<input type='submit' class='button' value=\"$LANG[CHANNEL_TO_TAG5]\" /></form>";
				
				$shardContentArray[] = $thisContentObj;
		}
		break;
		
		case "convert_to_tag":
		if (isInGroup($CURRENTUSER, 'admin') && $_POST['delete_conf']) {
			$editid = make_num_safe($_POST['editid']);
			$tag = mb_strtolower(trim(make_var_safe($_POST['tag_name'])),'UTF-8');
			if ($tag && $editid) {
				$verify_tag = mf_query("SELECT tag FROM tags WHERE tag=\"$tag\" LIMIT 1");
   				$verify_tag = mysql_fetch_assoc($verify_tag);
				if ($verify_tag['tag'] != $tag)
					mf_query("INSERT IGNORE INTO tags (tag,userID) VALUES (\"$tag\",'$CURRENTUSERID')");
				$i = 0;
				$query = mf_query("SELECT ID FROM forum_topics WHERE category = '$editid'");
   				while ($row = mysql_fetch_assoc($query)) {
					$i ++;
					mf_query("INSERT IGNORE INTO forum_tags (tag, threadID) VALUES (\"$tag\",'$row[ID]')");
				}
				mf_query("UPDATE tags SET total_use = total_use + $i WHERE tag = \"$tag\" LIMIT 1");
				header("Location: ".make_link("admin","&action=g_modifyChannels"));
			}
			else
				header("Location: ".make_link("admin","&action=g_edit&amp;editID=$_POST[editID]"));
		}
		else
			header("Location: ".make_link("admin","&action=g_edit&amp;editID=$_POST[editID]"));
		break;

		case "g_tags":
		if (isInGroup($CURRENTUSER, 'admin')) {
           	$thisContentObj = New contentObj;
           	$thisContentObj->contentType = "generic";
           	$thisContentObj->title = "$LANG[TAG_MANAGEMENT]";
			$thisContentObj->primaryContent = "<div style='margin-top:16px;'>";
			$thisContentObj->primaryContent .= "<a href='".make_link("admin","&amp;action=g_tags_list")."' class='button'>$LANG[TAG_LIST]</a> &nbsp; ";
			$thisContentObj->primaryContent .= "<a href='".make_link("admin","&amp;action=g_count_tags")."' class='button'>$LANG[TAG_RECOUNT_TITLE]</a> &nbsp; ";
			$thisContentObj->primaryContent .= "</div>";
			$shardContentArray[] = $thisContentObj;
		}
		break;

		// tags list
		case "g_tags_list":
		if (isInGroup($CURRENTUSER, 'admin')) {
           	$thisContentObj = New contentObj;
           	$thisContentObj->contentType = "generic";
           	$thisContentObj->title = "$LANG[TAG_LIST]";
			
			$order = "ID";
			$sens = "DESC";
			$link = make_link("admin","&amp;action=g_tags_list");
			$link_ID = "&amp;order=ID&amp;sens=ASC";
			$link_tag = "&amp;order=tag&amp;sens=ASC";
			$link_total = "&amp;order=total&amp;sens=ASC";
			$link_user = "&amp;order=user&amp;sens=ASC";
			$img_ID = " <img src='engine/grafts/$siteSettings[graft]/images/menuup.gif' alt'X' />";
			$img_tag = "";
			$img_total = "";
			$img_user = "";
			if (isset($_REQUEST['order'])) {
				if ($_REQUEST['sens'] == "ASC") {
					$sens = "ASC";
					$sens_link = "DESC";
					$sens_img = "down";
				}
				else {
					$sens = "DESC";
					$sens_link = "ASC";
					$sens_img = "up";
				}
				if ($_REQUEST['order'] == "ID") {
					$order = "ID";
					$link_ID = "&amp;order=ID&amp;sens=$sens_link";
					$img_ID = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'X' />";
				}
				else if ($_REQUEST['order'] == "tag") {
					$order = "tag";
					$link_tag = "&amp;order=tag&amp;sens=$sens_link";
					$img_tag = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'X' />";
				}
				else if ($_REQUEST['order'] == "total") {
					$order = "total_use";
					$link_total = "&amp;order=total&amp;sens=$sens_link";
					$img_tag = "";
					$img_total = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'X' />";
				}
				else if ($_REQUEST['order'] == "user") {
					$order = "username";
					$link_user = "&amp;order=user&amp;sens=$sens_link";
					$img_tag = "";
					$img_user = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'X' />";
				}
			}
			$backgrd = "";
			$tags_list = "<div class='row'>
							<div class='cell bold' style='padding-left:4px;padding-right:4px;'><a href='$link$link_ID'>ID$img_ID</a></div>
							<div class='cell bold' style='padding-left:8px;padding-right:4px;'><a href='$link$link_tag'>Tag$img_tag</a></div>
							<div class='cell bold' style='padding-left:4px;padding-right:4px;'><a href='$link$link_total'>$LANG[TAG_TOTAL_USE]$img_total</a></div>
							<div class='cell bold' style='padding-left:4px;padding-right:4px;'><a href='$link$link_user'>$LANG[TAG_CREATOR]$img_user</a></div>
							<div class='cell bold' style='padding-left:4px;padding-right:4px;'>$LANG[TAG_EDIT]</a></div>
							<div class='cell bold' style='padding-left:4px;padding-right:4px;'>$LANG[TAG_DEL]</a></div>
						</div>";
			$query = mf_query("SELECT tags.*, users.username FROM tags JOIN users ON tags.userID = users.ID ORDER BY $order $sens");
			while ($row = mysql_fetch_assoc($query)) {
				$zerouse = "";
				if ($row['total_use'] == 0)
					$zerouse = "color:red;";
				if (!$backgrd)
					$backgrd = "background-color:#E7E7E7;";
				else
					$backgrd = "";
				$tags_list .= "<div class='row' style='$backgrd'>
								<div class='cell_right' style='color:#777777;'>$row[ID]</div>
								<div class='cell' style='padding-left:8px;'>$row[tag]</div>
								<div class='cell right' style='padding-right:4px;$zerouse'>$row[total_use]</div>
								<div class='cell'>$row[username]</div>
								<div class='cell center'><a href='".make_link("admin","&amp;action=g_edit_tag&tagID=$row[ID]")."'><img src='engine/grafts/$siteSettings[graft]/images/edit2.gif' alt'E' /></a></div>
								<div class='cell center'><a href='".make_link("admin","&amp;action=g_delete_tag&tagID=$row[ID]")."'><img src='engine/grafts/$siteSettings[graft]/images/b_drop_mini.png' alt'X' /></a></div>
							</div>";
			}
			$thisContentObj->primaryContent = "<div style='display:table;'>$tags_list</div>";
			
			$shardContentArray[] = $thisContentObj;

		}
		break;
		
		case "g_edit_tag":
		if (isInGroup($CURRENTUSER, 'admin')) {

			$tagID = make_num_safe($_REQUEST['tagID']);

           	$thisContentObj = New contentObj;
           	$thisContentObj->contentType = "generic";
			
			$tagList = "";
			$query = mf_query("SELECT ID,tag FROM tags WHERE ID != '$tagID' ORDER BY tag");
			while ($row = mysql_fetch_assoc($query))
				$tagList .= "<option value=\"$row[tag]\">$row[tag]</option>";

			$query = mf_query("SELECT tags.*, users.username FROM tags JOIN users ON tags.userID = users.ID WHERE tags.ID = '$tagID' LIMIT 1");
			$tag_row = mysql_fetch_assoc($query);
			
			$thisContentObj->title = "$LANG[TAG_EDIT_TITLE] \"$tag_row[tag]\"";
			$thisContentObj->primaryContent = "<form name='rename_tag' action='index.php?shard=admin&amp;action=proc_edit_tag' method='post'>
												<input type='hidden' name='tagID' value='$tagID' />
												<input type='hidden' name='tagName' value=\"$tag_row[tag]\" />
												$LANG[TAG_NEW_NAME] <input type='text' name='new_tagName' value=\"$tag_row[tag]\" size='40' class='bselect' />
												<input type='submit' class='button_mini' value=\"$LANG[TAG_RENAME]\" />
												</form>";
			$thisContentObj->primaryContent .= "<form style='margin-top:8px;' name='move_tag' action='index.php?shard=admin&amp;action=proc_move_tag' method='post'>
												<input type='hidden' name='tagID' value='$tagID' />
												<input type='hidden' name='total_use' value='$tag_row[total_use]' />
												<input type='hidden' name='total_use_week' value='$tag_row[total_use_week]' />
												<input type='hidden' name='total_use_month' value='$tag_row[total_use_month]' />
												<input type='hidden' name='total_use_year' value='$tag_row[total_use_year]' />
												<input type='hidden' name='tagName' value=\"$tag_row[tag]\" />
												$LANG[TAG_MOVE] <select name='move_to_tag' class='bselect'>$tagList</select>
												<input type='submit' class='button_mini' value=\"$LANG[TAG_MOVE_BUTTON]\" />
												</form>";
			$thisContentObj->primaryContent .= "<div style='height:16px;'></div><a href='".make_link("admin","&amp;action=g_tags_list")."' class='button'>$LANG[BUTTON_BACK]</a>";
			$shardContentArray[] = $thisContentObj;
		}
		break;

		case "g_tag_exist":
		if (isInGroup($CURRENTUSER, 'admin')) {

			$tagID = make_num_safe($_REQUEST['tagID']);
			$tagName = make_var_safe($_REQUEST['tagName']);
			$new_tagName = make_var_safe($_REQUEST['new_tagName']);

           	$thisContentObj = New contentObj;
           	$thisContentObj->contentType = "generic";
			
			$thisContentObj->title = "Erreur !";
			$thisContentObj->primaryContent = "$LANG[TAG_RENAME_ERROR1] \"<span class='bold'>$tagName</span>\" $LANG[TAG_RENAME_ERROR2] \"<span class='bold'>$new_tagName</span>\" $LANG[TAG_RENAME_ERROR3] \"<span class='bold'>$new_tagName</span>\" $LANG[TAG_RENAME_ERROR4]";
			$thisContentObj->primaryContent .= "&nbsp; <a href='".make_link("admin","&amp;action=g_edit_tag&amp;tagID=$tagID")."' class='button'>$LANG[BUTTON_BACK]</a>";

			$shardContentArray[] = $thisContentObj;
		}
		break;

		case "proc_edit_tag":

		$tagID = make_num_safe($_POST['tagID']);
		$tagName = make_var_safe($_POST['tagName']);
		$new_tagName = make_var_safe($_POST['new_tagName']);
		
		$query = mf_query("SELECT ID FROM tags WHERE tag = \"$new_tagName\" LIMIT 1");
		if ($row = mysql_fetch_assoc($query))
			header("Location: ".make_link("admin","&action=g_tag_exist&tagID=$tagID&tagName=$tagName&new_tagName=$new_tagName"));
		else {
			mf_query("UPDATE tags SET tag = \"$new_tagName\" WHERE ID = '$tagID' LIMIT 1");
			mf_query("UPDATE forum_tags SET tag = \"$new_tagName\" WHERE tag = \"$tagName\"");
		
			load_tags();
			
			header("Location: ".make_link("admin","&action=g_tags_list"));
		}

		break;

		case "proc_move_tag":
		
		$tagID = make_num_safe($_POST['tagID']);
		$total_use = make_num_safe($_POST['total_use']);
		$total_use_week = make_num_safe($_POST['total_use_week']);
		$total_use_month = make_num_safe($_POST['total_use_month']);
		$total_use_year = make_num_safe($_POST['total_use_year']);
		$tagName = make_var_safe($_POST['tagName']);
		$new_tagName = make_var_safe($_POST['move_to_tag']);
		
		mf_query("DELETE FROM tags WHERE ID = '$tagID' LIMIT 1");
		mf_query("UPDATE tags SET total_use = total_use + $total_use, total_use_week = total_use_week + $total_use_week, total_use_month = total_use_month + $total_use_month, total_use_year = total_use_year + $total_use_year WHERE tag = \"$new_tagName\" LIMIT 1");
		mf_query("UPDATE IGNORE forum_tags SET tag = \"$new_tagName\" WHERE tag = \"$tagName\"");
		
		load_tags();

		header("Location: ".make_link("admin","&action=g_tags_list"));

		break;

		case "g_delete_tag":
		if (isInGroup($CURRENTUSER, 'admin')) {
			$tagID = make_num_safe($_REQUEST['tagID']);
			$tag = mf_query("SELECT tag FROM tags WHERE ID = \"$tagID\" LIMIT 1");
			if ($tag = mysql_fetch_assoc($tag)) {
				$forum_tag = mf_query("SELECT COUNT(tag) AS total FROM forum_tags WHERE tag = \"$tag[tag]\"");
				$forum_tag = mysql_fetch_assoc($forum_tag);
				if ($forum_tag['total'] > 4) {
					$thisContentObj = New contentObj;
					$thisContentObj->contentType = "generic";
					$thisContentObj->title = "$LANG[TAG_ERROR]";
					$thisContentObj->primaryContent = "<div style='margin-top:16px;'>$LANG[TAG_DEL_ADMIN]";
					$thisContentObj->primaryContent .= "&nbsp; <a href='".make_link("admin","&amp;action=proc_delete_tag&amp;tagID=$tagID")."' class='button'>$LANG[TAG_DEL_ADMIN_CONFIRM]</a></div>";
					$shardContentArray[] = $thisContentObj;
				}
				else {
					mf_query("DELETE FROM forum_tags WHERE tag = \"$tag[tag]\"");
					mf_query("DELETE FROM tags WHERE ID = '$tagID' LIMIT 1");
					header("Location: ".make_link("admin","&action=g_tags_list"));
				}
			}
		}
		break;

		case "proc_delete_tag":
		if (isInGroup($CURRENTUSER, 'admin')) {
			$tagID = make_num_safe($_REQUEST['tagID']);
			$tag = mf_query("SELECT tag FROM tags WHERE ID = \"$tagID\" LIMIT 1");
			$tag = mysql_fetch_assoc($tag);
			mf_query("DELETE FROM forum_tags WHERE tag = \"$tag[tag]\"");
			mf_query("DELETE FROM tags WHERE ID = '$tagID' LIMIT 1");
		
			header("Location: ".make_link("admin","&action=g_tags_list"));
		}
		break;

		// Count total use of tags
		case "g_count_tags":
		if (isInGroup($CURRENTUSER, 'admin')) {
			$i = 0;
			$query = mf_query("SELECT tag,total_use FROM tags");
			while ($row = mysql_fetch_assoc($query)) {
				$query_f = mf_query("SELECT COUNT(threadID) AS total_thread FROM forum_tags WHERE tag = \"$row[tag]\"");
				$row_f = mysql_fetch_assoc($query_f);
				if ($row_f['total_thread'] != $row['total_use']) {
					mf_query("UPDATE tags SET total_use = '$row_f[total_thread]' WHERE tag = \"$row[tag]\"");
					$i ++;
				}
				$date_min = time() - (3600 * 24 * 7);
				$query_f = mf_query("SELECT COUNT(forum_tags.threadID) AS total_thread 
								FROM forum_tags 
								JOIN forum_topics ON forum_topics.ID = forum_tags.threadID
								WHERE 
									forum_tags.tag = \"$row[tag]\"
									AND forum_topics.date > '$date_min'");
				$row_f = mysql_fetch_assoc($query_f);
				mf_query("UPDATE tags SET total_use_week = '$row_f[total_thread]' WHERE tag = \"$row[tag]\"");
				$date_min = time() - (3600 * 24 * 30);
				$query_f = mf_query("SELECT COUNT(forum_tags.threadID) AS total_thread 
								FROM forum_tags 
								JOIN forum_topics ON forum_topics.ID = forum_tags.threadID
								WHERE 
									forum_tags.tag = \"$row[tag]\"
									AND forum_topics.date > '$date_min'");
				$row_f = mysql_fetch_assoc($query_f);
				mf_query("UPDATE tags SET total_use_month = '$row_f[total_thread]' WHERE tag = \"$row[tag]\"");
				$date_min = time() - (3600 * 24 * 365);
				$query_f = mf_query("SELECT COUNT(forum_tags.threadID) AS total_thread 
								FROM forum_tags 
								JOIN forum_topics ON forum_topics.ID = forum_tags.threadID
								WHERE 
									forum_tags.tag = \"$row[tag]\"
									AND forum_topics.date > '$date_min'");
				$row_f = mysql_fetch_assoc($query_f);
				mf_query("UPDATE tags SET total_use_year = '$row_f[total_thread]' WHERE tag = \"$row[tag]\"");

			}
           	$thisContentObj = New contentObj;
           	$thisContentObj->contentType = "generic";
           	$thisContentObj->title = "$LANG[TAG_RECOUNT_TITLE]";
			$thisContentObj->primaryContent = "<br/><br/>";
			if ($i == 0)
				$thisContentObj->primaryContent .= $LANG['TAG_RECOUNT_NONE'];
			else if ($i == 1)
				$thisContentObj->primaryContent .= $LANG['TAG_RECOUNT_ONE'];
			else
				$thisContentObj->primaryContent .= $LANG['TAG_RECOUNT_SOME1'].$i.$LANG['TAG_RECOUNT_SOME2'];
				
			$shardContentArray[] = $thisContentObj;
		}
		break;

        // g_add channel
        case "g_add":
        if (isInGroup($CURRENTUSER, 'admin')) {
			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[ADD_NEW_CHANNEL]";
        	$thisContentObj->primaryContent = "<br/><br/>";

			$parent_id = $_REQUEST['parent_id'];

			if ($parent_id == 0)
				$thisContentObj->primaryContent .= "$LANG[ADDING_NEW_CHANNEL]";
			else if( is_numeric($parent_id)) {
    			$parentChannel = mf_query("select name from categories where ID='$parent_id'");
    			$parentChannel = mysql_fetch_assoc($parentChannel);
    			$thisContentObj->primaryContent .= "$LANG[ADDING_SUB_CHANNEL] <b>$parentChannel[name]</b> $LANG[CHANNEL]...";
			}

			$thisContentObj->primaryContent .= "
				<form action='index.php?shard=admin&amp;action=addNew&amp;parent_id=".$parent_id."' method='post'>
				<br/>$LANG[CHANNEL_NAME]:<br/>
				<input type='text' name='name' size='50' /><br/><br/>
				$LANG[CHANNEL_DESCRIPTION]:<br/>
				<input type='text' name='description' size='100' /><br/><br/>
				<input type='submit' value=\"$LANG[SUBMIT]\" />
				</form>";

			$shardContentArray[] = $thisContentObj;           
		}
        break;


        // functional add
        case "addNew":
		if (isInGroup($CURRENTUSER, 'admin')) {	
			$name = make_var_safe( $_POST['name']);
            $desc = make_var_safe( $_POST['description']);
            if( is_numeric( $_REQUEST['parent_id'])) {
        	   $is = "insert into categories (name, description, parent_id) VALUES ('$name', '$desc', $_REQUEST[parent_id])";
        	   $insertChannel = mf_query($is);
        	}
        	header("Location: ".make_link("admin","&action=g_modifyChannels"));
		}
        break;

       
        case "recalcnri":
        if (isInGroup($CURRENTUSER, 'admin')) {
			$userlist = mf_query("select * from users order by rating desc");
        	
        	while ($row=mysql_fetch_assoc($userlist)) {
				print $row['username']."<br/>";
				$newNRI = calculateRank($row['ID']);
				mf_query("update users set rating=$newNRI where ID=$row[ID] limit 1");
				print "<br/><br/><br/>----------------------------<br/><br/><br/>";
			}
        }
        break;
        
        
        case "banuser":
        break;
        
		case "g_clean_users":
        if (isInGroup($CURRENTUSER, 'sysadmin') && isInGroup($CURRENTUSER, 'admin')) {
			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[CLEAN_USERS_DATABASE]";
        	$thisContentObj->primaryContent = "$LANG[CLEAN_USERS_DATABASE_SUBTITLE]<br/><br/>";        

			$datejoined_older = time() - (3600 * 24 * 7);
			$user_not_validated = mf_query("SELECT COUNT(ID) AS total FROM users WHERE userstatus = 'pending' AND datejoined < '$datejoined_older'");
			$user_not_validated = mysql_fetch_assoc($user_not_validated);

			$user_never_connected = mf_query("SELECT COUNT(ID) AS total FROM users WHERE userstatus IS NULL AND lat = 0 AND datejoined < '$datejoined_older'");
			$user_never_connected = mysql_fetch_assoc($user_never_connected);

			$twelvemonths = time() - (3600 * 24 * 365);
			$user_older = mf_query("SELECT COUNT(ID) AS total FROM users WHERE lat < $twelvemonths");
			$user_older = mysql_fetch_assoc($user_older);

			$thisContentObj->primaryContent .= "<br/><br/>
				<form action='".make_link("admin","&amp;action=g_submitCleanUsers")."' method='post'>
				<table cellpadding='2'>";
			$thisContentObj->primaryContent .= "
				<tr><td>$LANG[CLEAN_USERS_DATABASE_PENDING]</td>
				<td><input class='bselect' type='checkbox' name='cleanpending' /> ($user_not_validated[total])
				</td></tr>
				<tr><td>$LANG[CLEAN_USERS_DATABASE_NEVER]</td>
				<td><input class='bselect' type='checkbox' name='cleanneverlogged' /> ($user_never_connected[total])
				</td></tr>
				<tr><td><div style='white-space:nowrap;'>$LANG[CLEAN_USERS_DATABASE_OLD]
				<input class='bselect' size='2' type='text' name='cleanmonths' value='12' /> $LANG[CLEAN_USERS_DATABASE_OLD_MONTHS] </div></td>
				<td><input class='bselect' type='checkbox' name='cleanolderthan' /> ($user_older[total])
				</td></tr>
				<tr><td>$LANG[CLEAN_USERS_DATABASE_OLD_FHITS]</td>
				<td><input class='bselect' type='checkbox' name='cleanfhits' />
				</td></tr>
				<tr><td colspan='2'>
				<b>$LANG[CLEAN_USERS_DATABASE_WARNING]</b> &nbsp;
				<input class='button' type='submit' value=\"$LANG[CLEAN_USERS_DATABASE_BUTTON]\" />
				</td></tr>
				</table></form>";

			$shardContentArray[] = $thisContentObj;
		}
		break;
		
		case "g_submitCleanUsers":

		if (isInGroup($CURRENTUSER, 'sysadmin') && isInGroup($CURRENTUSER, 'admin')) {
			$cleanmonths = $_POST['cleanmonths'];
		
			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[CLEAN_USERS_COMPLETE]";
        	$thisContentObj->primaryContent = "<br/><br/>";
		
			if (isset($_POST['cleanpending'])) {
				$cleana = mf_query("SELECT COUNT(ID) as Expr1 from users");
				$cleana = mysql_fetch_assoc($cleana);
				$dayclean = time() - (3600 * 24);
				mf_query("DELETE FROM `forum_user_nri` WHERE `userID` IN (SELECT `ID` FROM `users` WHERE `userstatus` = 'pending' and datejoined < '$dayclean' and rating='0')");
				mf_query("DELETE FROM `users` WHERE `userstatus` = 'pending'");
				mf_query("DELETE FROM `permissiongroups` WHERE `pGroup` = 'everyone'");
				mf_query("DELETE FROM `verify` WHERE `userID` NOT IN (SELECT `ID` FROM `users`)");
				$cleanb = mf_query("SELECT COUNT(ID) as Expr1 from users");
				$cleanb = mysql_fetch_assoc($cleanb);
				$clean = $cleana['Expr1'] - $cleanb['Expr1'];
				$thisContentObj->primaryContent .= "$clean $LANG[CLEAN_USERS_PENDING_DELETED]";
				$thisContentObj->primaryContent .= "<br/><br/>";
			}

			if (isset($_POST['cleanneverlogged'])) {
				$cleana = mf_query("SELECT COUNT(ID) as Expr1 from users");
				$cleana = mysql_fetch_assoc($cleana);
				$weekclean = time() - (3600 * 24 * 7);
				mf_query("DELETE FROM `forum_user_nri` WHERE `userID` IN (SELECT `ID` FROM `users` WHERE `lat` = 0 AND userstatus IS NULL and datejoined < '$weekclean')");
				mf_query("DELETE FROM `users` WHERE `lat` = 0 AND userstatus IS NULL and datejoined < '$weekclean'");
				$cleanb = mf_query("SELECT COUNT(ID) as Expr1 from users");
				$cleanb = mysql_fetch_assoc($cleanb);
				$clean = $cleana['Expr1'] - $cleanb['Expr1'];
				$thisContentObj->primaryContent .= "$clean $LANG[CLEAN_USERS_NEVER_LOGGED_DELETED]";
				$thisContentObj->primaryContent .= "<br/><br/>";
			}

			if (isset($_POST['cleanolderthan']) && is_numeric($cleanmonths) && $cleanmonths > 1) {
				$dateclean = time();
				$monthclean = 2592000 * $cleanmonths;
				$dateclean = $dateclean - $monthclean;
				$cleana = mf_query("SELECT COUNT(ID) as Expr1 from users");
				$cleana = mysql_fetch_assoc($cleana);
				mf_query("DELETE FROM forum_user_nri WHERE num_posts = 0 AND num_posts_notnri = 0 AND userID IN (SELECT ID FROM users WHERE lat > 0 AND lat < '$dateclean')");
				mf_query("DELETE FROM `users` WHERE userstatus IS NULL AND `username` NOT IN (SELECT `name` FROM `forum_user_nri`)");
				$cleanb = mf_query("SELECT COUNT(ID) as Expr1 from users");
				$cleanb = mysql_fetch_assoc($cleanb);
				$clean = $cleana['Expr1'] - $cleanb['Expr1'];
				$thisContentObj->primaryContent .= "$clean $LANG[CLEAN_USERS_OLDER_DELETED1] $cleanmonths $LANG[CLEAN_USERS_OLDER_DELETED2]";
				$thisContentObj->primaryContent .= "<br/><br/>";
			}

			if (isset($_POST['cleanfhits'])) {
				$cleana = mf_query("SELECT COUNT(userID) as Expr1 from fhits");
				$cleana = mysql_fetch_assoc($cleana);
				mf_query("DELETE FROM fhits WHERE userID NOT IN (SELECT ID FROM users)");
				$cleanb = mf_query("SELECT COUNT(userID) as Expr1 from fhits");
				$cleanb = mysql_fetch_assoc($cleanb);
				$clean = $cleana['Expr1'] - $cleanb['Expr1'];
				$thisContentObj->primaryContent .= "$clean $LANG[CLEAN_USERS_FHITS_DELETED]";
				$thisContentObj->primaryContent .= "<br/><br/>";
			}

			$shardContentArray[] = $thisContentObj;
		}
		break;



		case "g_rename_user":
		if (isInGroup($CURRENTUSER, 'admin')) {
			$curname = "";
			if (array_key_exists('user', $_REQUEST))
				$curname = make_var_safe($_REQUEST['user']);
			
			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[ADMIN_RENAME_USER]";
			$thisContentObj->primaryContent = "<br/><br/>";        

			$thisContentObj->primaryContent .= "<br/><br/>
				<form action='".make_link("admin","&amp;action=g_submitrename")."' method='post'>
				<table cellpadding='2'>";
			$thisContentObj->primaryContent .= "
				<tr><td>$LANG[RENAME_USER_OLD]</td>
				<td><input class='bselect' type='text' name='current_name' value=\"$curname\" />
				</td></tr>
				<tr><td>$LANG[RENAME_USER_NEW]</td>
				<td><input class='bselect' type='text' name=\"new_name\" />
				</td></tr>
				<tr><td></td>
				<td><input class='button' type='submit' value=\"$LANG[RENAME_USER_BUTTON]\" />
				</td></tr>
				</table></form>";

			$shardContentArray[] = $thisContentObj;
		}
		break;

		case "g_submitrename":
		if (isInGroup($CURRENTUSER, 'admin')) {
			$currentname = make_var_safe($_POST['current_name']);
			$newname = make_var_safe($_POST['new_name']);
			if (!$newname || !$currentname || ($newname == $currentname))
				header("Location: ".make_link("admin"));

			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[ADMIN_RENAME_USER]";
        	$thisContentObj->primaryContent = "<br/><br/>";

			$controlcurrent = mf_query("SELECT * from users where username = \"$currentname\" limit 1");
			if (!$controlcurrent = mysql_fetch_assoc($controlcurrent)) {
				$thisContentObj->primaryContent = "$LANG[RENAME_USER_UNKNOWN1] <span class='bold'>$currentname</span> $LANG[RENAME_USER_UNKNOWN2]";
				$shardContentArray[] = $thisContentObj;
				break;
			}	

			if (mb_strtolower($currentname,'UTF-8') != mb_strtolower($newname,'UTF-8')) {
				$controlnew = mf_query("SELECT * from users where username = \"$newname\" limit 1");
				if ($controlnew = mysql_fetch_assoc($controlnew)) {
					$thisContentObj->primaryContent = "$LANG[RENAME_USER_EXIST1] <span class='bold'>$currentname</span> $LANG[RENAME_USER_EXIST2]";
					$shardContentArray[] = $thisContentObj;
					break;
				}	
			}

			mf_query("UPDATE forum_posts SET user = \"$newname\" WHERE user = \"$currentname\"");
			mf_query("UPDATE forum_topics SET user = \"$newname\" WHERE user = \"$currentname\"");
			mf_query("UPDATE forum_topics SET last_post_user = \"$newname\" WHERE last_post_user = \"$currentname\"");
			mf_query("UPDATE forum_user_nri SET name = '$newname' WHERE name = '$currentname'");
			mf_query("UPDATE permissiongroups SET username = \"$newname\" WHERE username = \"$currentname\"");
			mf_query("UPDATE postratings SET user = \"$newname\" WHERE user = \"$currentname\"");
			mf_query("UPDATE users SET username = \"$newname\" WHERE username = \"$currentname\"");
			mf_query("INSERT INTO log_rename_user (userID, oldname, newname, rename_date) VALUES ('$controlcurrent[ID]', \"$currentname\", \"$newname\", '".time()."')");
			

			$thisContentObj->primaryContent .= "$LANG[RENAME_USER_END]";
			$thisContentObj->primaryContent .= "<br/><br/>";
			
			
			$shardContentArray[] = $thisContentObj;
		}
		break;


		case "g_group":
		if (isInGroup($CURRENTUSER, 'admin')) {
			
			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[GROUP_MANAGEMENT]";
        	$thisContentObj->primaryContent = "<br/><a href='".make_link("admin","&amp;action=g_group_users")."' class='button'>$LANG[GROUP_SEE_USERS]</a><br/>
				<form action='index.php?shard=admin&amp;action=submitGroups' method='post'>
				<center>
				<table cellpadding='2' cellspacing='6'>
				<tr><td><b>$LANG[GROUP_MANAGEMENT_SYSTEM_NAME]</b></td>
				<td><b>$LANG[GROUP_MANAGEMENT_DISPLAYED_NAME]</b></td>
				<td><b>$LANG[GROUP_MANAGEMENT_DESCRIPTION]</b></td></tr>";

			$groups = mf_query("select * from groups where ID=1 limit 1");
			$name = mysql_fetch_assoc($groups);

			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_ADMIN]</td>
				<td>$LANG[GROUP_MANAGEMENT_ADMIN_TEXT]</td>
				<td>$LANG[GROUP_MANAGEMENT_ADMIN_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_SYSADMIN]</td>
				<td>$LANG[GROUP_MANAGEMENT_SYSADMIN_TEXT]</td>
				<td>$LANG[GROUP_MANAGEMENT_SYSADMIN_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_MODO]</td>
				<td>$LANG[GROUP_MANAGEMENT_MODO_TEXT]</td>
				<td>$LANG[GROUP_MANAGEMENT_MODO_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_LEVEL01]</td>
				<td><input class='bselect' size='16' type='text' name='namedisp1' value=\"$name[namedisp1]\" /></td>
				<td>$LANG[GROUP_MANAGEMENT_LEVEL01_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_LEVEL02]</td>
				<td><input class='bselect' size='16' type='text' name='namedisp2' value=\"$name[namedisp2]\" /></td>
				<td>$LANG[GROUP_MANAGEMENT_LEVEL02_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_LEVEL03]</td>
				<td><input class='bselect' size='16' type='text' name='namedisp3' value=\"$name[namedisp3]\" /></td>
				<td>$LANG[GROUP_MANAGEMENT_LEVEL03_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_LEVEL04]</td>
				<td><input class='bselect' size='16' type='text' name='namedisp4' value=\"$name[namedisp4]\" /></td>
				<td>$LANG[GROUP_MANAGEMENT_LEVEL04_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_LEVEL05]</td>
				<td><input class='bselect' size='16' type='text' name='namedisp5' value=\"$name[namedisp5]\" /></td>
				<td>$LANG[GROUP_MANAGEMENT_LEVEL05_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_LEVEL06]</td>
				<td><input class='bselect' size='16' type='text' name='namedisp6' value=\"$name[namedisp6]\" /></td>
				<td>$LANG[GROUP_MANAGEMENT_LEVEL06_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_LEVEL07]</td>
				<td><input class='bselect' size='16' type='text' name='namedisp7' value=\"$name[namedisp7]\" /></td>
				<td>$LANG[GROUP_MANAGEMENT_LEVEL07_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_LEVEL08]</td>
				<td><input class='bselect' size='16' type='text' name='namedisp8' value=\"$name[namedisp8]\" /></td>
				<td>$LANG[GROUP_MANAGEMENT_LEVEL08_DESC]</td></tr>";
			$thisContentObj->primaryContent .= "<tr><td>$LANG[GROUP_MANAGEMENT_LEVEL09]</td>
				<td><input class='bselect' size='16' type='text' name='namedisp9' value=\"$name[namedisp9]\" /></td>
				<td>$LANG[GROUP_MANAGEMENT_LEVEL09_DESC]</td></tr>";

			$thisContentObj->primaryContent .= "
				<tr><td></td><td></td><td><input class='button' type='submit' value=\"$LANG[SAVE_SETTINGS]\" /></td>
				</tr></table></center></form>";

			$shardContentArray[] = $thisContentObj;
		}
		break;		

        case "submitGroups":
		if (isInGroup($CURRENTUSER, 'admin')) {
			$namedisp1 = make_var_safe( $_REQUEST['namedisp1']);
			$namedisp2 = make_var_safe( $_REQUEST['namedisp2']);
			$namedisp3 = make_var_safe( $_REQUEST['namedisp3']);
			$namedisp4 = make_var_safe( $_REQUEST['namedisp4']);
			$namedisp5 = make_var_safe( $_REQUEST['namedisp5']);
			$namedisp6 = make_var_safe( $_REQUEST['namedisp6']);
			$namedisp7 = make_var_safe( $_REQUEST['namedisp7']);
			$namedisp8 = make_var_safe( $_REQUEST['namedisp8']);
			$namedisp9 = make_var_safe( $_REQUEST['namedisp9']);

            $is = mf_query("DELETE FROM groups WHERE ID = 1");
			$is = mf_query("INSERT INTO groups (ID, namedisp1, namedisp2, namedisp3, namedisp4, namedisp5, namedisp6, namedisp7, namedisp8, namedisp9) VALUES ('1', '$namedisp1', '$namedisp2', '$namedisp3', '$namedisp4', '$namedisp5', '$namedisp6', '$namedisp7', '$namedisp8', '$namedisp9')");

        	header("Location: ".make_link("admin"));
		}
		break;

		case "g_group_users":
		if (isInGroup($CURRENTUSER, 'admin')) {
			global $LANG;
			
			$thisContentObj = New contentObj;
        	$thisContentObj->contentType = "generic";
        	$thisContentObj->title = "$LANG[GROUP_USERS]";

			$groups = mf_query("select * from groups where ID=1 limit 1");
			$groups = mysql_fetch_assoc($groups);

        	$group_buttons = "";

			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))
				if ($_REQUEST['group'] == "admin")
					$button_class = "class='button_mini_on'";
			$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=admin")."' $button_class>$LANG[GROUP_MANAGEMENT_ADMIN_TEXT]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))
				if ($_REQUEST['group'] == "sysadmin")
					$button_class = "class='button_mini_on'";
			$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=sysadmin")."' $button_class>$LANG[GROUP_MANAGEMENT_SYSADMIN_TEXT]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))
				if ($_REQUEST['group'] == "modo")
					$button_class = "class='button_mini_on'";
			$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=modo")."' $button_class>$LANG[GROUP_MANAGEMENT_MODO_TEXT]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))
			if ($_REQUEST['group'] == "level1")
				$button_class = "class='button_mini_on'";
			if ($groups['namedisp1'])
				$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=level1")."' $button_class>$groups[namedisp1]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))
			if ($_REQUEST['group'] == "level2")
				$button_class = "class='button_mini_on'";
			if ($groups['namedisp2'])
				$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=level2")."' $button_class>$groups[namedisp2]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))
			if ($_REQUEST['group'] == "level3")
				$button_class = "class='button_mini_on'";
			if ($groups['namedisp3'])
				$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=level3")."' $button_class>$groups[namedisp3]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))	
			if ($_REQUEST['group'] == "level4")
				$button_class = "class='button_mini_on'";
			if ($groups['namedisp4'])
				$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=level4")."' $button_class>$groups[namedisp4]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))	
			if ($_REQUEST['group'] == "level5")
				$button_class = "class='button_mini_on'";
			if ($groups['namedisp5'])
				$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=level5")."' $button_class>$groups[namedisp5]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))	
			if ($_REQUEST['group'] == "level6")
				$button_class = "class='button_mini_on'";
			if ($groups['namedisp6'])
				$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=level6")."' $button_class>$groups[namedisp6]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))	
			if ($_REQUEST['group'] == "level7")
				$button_class = "class='button_mini_on'";
			if ($groups['namedisp7'])
				$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=level7")."' $button_class>$groups[namedisp7]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))	
			if ($_REQUEST['group'] == "level8")
				$button_class = "class='button_mini_on'";
			if ($groups['namedisp8'])
				$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=level8")."' $button_class>$groups[namedisp8]</a>&nbsp;";
			$button_class = "class='button_mini_off'";
			if (array_key_exists('group', $_REQUEST))	
			if ($_REQUEST['group'] == "level9")
				$button_class = "class='button_mini_on'";
			if ($groups['namedisp9'])
				$group_buttons .= "<a href='".make_link("admin","&amp;action=g_group_users&amp;group=level9")."' $button_class>$groups[namedisp9]</a>&nbsp;";
			
			$thisContentObj->primaryContent = "<br/>" . $group_buttons;
			$thisContentObj->primaryContent .= "<div style='padding-bottom: 8px; margin-bottom:4px; border-bottom:solid 1px silver;'></div>";

			if (array_key_exists('group', $_REQUEST)) {
				$group = make_var_safe($_REQUEST['group']);
				$users = mf_query("select permissiongroups.*, users.lat, users.ID 
								from permissiongroups 
								JOIN users ON permissiongroups.username = users.username 
								where permissiongroups.pGroup='$group' 
								ORDER BY permissiongroups.username");
				$today = time();
				$userlist = "";
				while ($row = mysql_fetch_assoc($users)) {
					$teams = "";
					$query2 = mf_query("SELECT team FROM permissiongroups WHERE username = \"$row[username]\" AND team !='' ORDER BY team");
					while ($row2 = mysql_fetch_assoc($query2 ))
						$teams.= "[ $row2[team] ] ";
					if ($teams)
						$teams = "title=\"$LANG[TEAMS] ".$teams."\"";
					$userlist .= "<div style='display:inline-block;margin:2px;height:30px;vertical-align:top;' class='button' $teams>";
					$userlist .= "<div style='display:inline-block;width:111px;'><a href='".make_link("forum","&amp;action=g_ep&amp;ID=$row[ID]","#user/$row[ID]")."'>$row[username]</a></div>";
					$old_class = "color:#777777;";
					if (($today - $row['lat']) > 3888000)
						$old_class = "color:black;";
					if (($today - $row['lat']) > 7776000)
						$old_class = "color:red;";

					$userlist .= "<div style='display:inline-block;width:54px;$old_class'><i><small>" . date($LANG['DATE_LINE_MINIMAL2'], $row['lat']) . "</small></i></div>";
					if ($row['added_by']) {
						$userlist .= "<div style='font-weight:normal;font-size:0.8em;'>$LANG[ADDED_BY] $row[added_by] $LANG[ON] " . date($LANG['DATE_LINE_MINIMAL3'], $row['added_date']) . "</div>";
					}
					$userlist .= "</div>";
				}
				$thisContentObj->primaryContent .= $userlist;

			}

			$shardContentArray[] = $thisContentObj;
		}
		break;		


        case "g_customCSS":
		if (isInGroup($CURRENTUSER, 'admin')) {
			$thisContentObj = New contentObj; 
			$thisContentObj->title = "$LANG[CUSTOM_CSS]";
			$thisContentObj->contentType = "generic";


			if (array_key_exists('success', $_REQUEST)) {
			if ($_REQUEST['success'] == 1)
				$thisContentObj->primaryContent .= "<div style='margin: 20px; color: green; font-weight: bold;'>".$LANG[CSS_SUCCESS]."</div>";
			}
			$thisContentObj->primaryContent .= "<br/><br/>$LANG[CUSTOM_CSS_TEXT]";
			$thisContentObj->primaryContent .= "<br/><br/>
				<form action='index.php?shard=admin&amp;action=save_css' method='post'>
				<textarea name='custom_css' rows='30' cols='60'>$siteSettings[custom_css]</textarea>	
			<br/><input class='button' type='submit' value=\"$LANG[SUBMIT]\" /></form>";



			$shardContentArray[] = $thisContentObj;
		}
        break;        
        
        case "save_css":
		if (isInGroup($CURRENTUSER, 'admin')) {
        	mf_query("update settings set custom_css='$_REQUEST[custom_css]'");
        	header("Location: ".make_link("admin","&action=g_customCSS&success=1"));
		}
        break;


	case "g_banned_users":
	if (isInGroup($CURRENTUSER, 'admin')) {
		$thisContentObj = New contentObj;
       	$thisContentObj->contentType = "generic";
       	$thisContentObj->title = "$LANG[BANNED_USERS]";
       	$thisContentObj->primaryContent = "<br/><br/><table>";
		$thisContentObj->primaryContent .= "
											<tr>
											<td>&nbsp;<small><i>$LANG[USER]</i></small></td>
											<td>&nbsp;<small><i>$LANG[BAN_DATE]</i></small></td>
											<td>&nbsp;<small><i>$LANG[BAN_BY]</i></small></td>
											<td>&nbsp;<small><i>$LANG[BAN_REASON]</i></small></td>
											</tr>";
		$i = 0;
		$changestyle = 2;

		$query = mf_query("SELECT ID, username, date, admin, reason from ban where banned = 1 ORDER BY date DESC, ID DESC");
		while ($users = mysql_fetch_assoc($query)) {
			$query2 = mf_query("SELECT ID from ban where username = \"".$users['username']."\" and banned = 1 ORDER BY ID DESC limit 1");
			$userstatus = mysql_fetch_assoc($query2);
			$query3 = mf_query("SELECT ID from ban where username = \"".$users['username']."\" and banned = 0 ORDER BY ID DESC limit 1");
			$userstatus3 = mysql_fetch_assoc($query3);
			if (($userstatus['ID'] == $users['ID']) && ($userstatus3['ID'] < $users['ID'])) {
				$thisContentObj->primaryContent .= "
					<tr ".(!($i%$changestyle)?"style=\"background-color:#efefef;\"":"").">
					<td><a href=\"?shard=forum&amp;action=un2id&amp;name=".$users['username']."\">$users[username]</a></td>";
				if ($users['date'])	
					$thisContentObj->primaryContent .= "
						<td><small>".date($LANG['DATE_LINE_MINIMAL2'], $users['date']).$LANG['DATE_LINE_FULL2'].date("G:i",$users['date'])."</small></td>";
				else
					$thisContentObj->primaryContent .= "<td></td>";
				$thisContentObj->primaryContent .= "
					<td>$users[admin]</td>
					<td><small>".htmlspecialchars($users['reason'])."</small></td>
					</tr>";

				$i ++;
			}
		}	
		$thisContentObj->primaryContent .= "</table>";
		$shardContentArray[] = $thisContentObj;
	}
	break;
	
	case "g_load_lang":
	if (isInGroup($CURRENTUSER, 'sysadmin')) {
		$langused = $siteSettings['lang'];
		if ($usr['lang'])
			$langused = $usr['lang'];

		foreach ($LANG as $langkey => $langvalue) {
			mf_query("INSERT INTO lang (keyword, value, lang) VALUES (\"$langkey\", \"".mysql_real_escape_string($langvalue)."\", \"$langused\")");
		}
	}
	break;
	
	case "g_set_lang":
	if (isInGroup($CURRENTUSER, 'admin')) {

		$langused = $siteSettings['lang'];
		if ($usr['lang'])
			$langused = $usr['lang'];

			$query = mf_query("SELECT * FROM lang WHERE lang = 'fr' ORDER BY ID");
		while ($row = mysql_fetch_assoc($query)) {
			$lang1array[$row['keyword']] = $row['value'];
		}
		$query = mf_query("SELECT * FROM lang WHERE lang = 'en' ORDER BY ID");
		while ($row = mysql_fetch_assoc($query)) {
			$lang2array[$row['keyword']] = $row['value'];
		}
		
		$thisContentObj->primaryContent .= "<div style='display:table;'>";
		foreach ($lang1array as $langkey => $langvalue) {
			$thisContentObj->primaryContent .= "
				<div class='row'>
					<div class='cell bold' style='padding:2px;'><input type='checkbox' name=\"".$langkey."\" class='checkbox'/> $langkey</div>
					<div class='cell' style='padding:2px;'><input type='text' size='32' name=\"".$langkey."_1\" value=\"".htmlspecialchars($langvalue)."\"/></div>
					<div class='cell' style='padding:2px;'><input type='text' size='32' name=\"".$langkey."_2\" value=\"".htmlspecialchars($lang2array[$langkey])."\"/></div>
				</div>";
		}
		
		$thisContentObj->primaryContent .= "</div>";
	}
	break;

	case "g_options":
	if (isInGroup($CURRENTUSER, 'admin')) {
		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->title = "$LANG[SITE_OPTIONS]";

		$checkedFriends = "";
		if ($siteSettings['module_friends'])
			$checkedFriends = "checked='checked'";

		$checkeddeezer= "";
		if ($siteSettings['deezer'])
			$checkeddeezer = "checked='checked'";

		$checkedmetacafe= "";
		if ($siteSettings['metacafe'])
			$checkedmetacafe = "checked='checked'";

		$checkediframe= "";
		if ($siteSettings['iframe'])
			$checkediframe = "checked='checked'";

		$checkedmobile= "";
		if ($siteSettings['mobile_enabled'])
			$checkedmobile = "checked='checked'";

		$thisContentObj->primaryContent = "<br/><br/><form action='index.php?shard=admin&amp;action=submitOptions' method='post'>
				<table cellpadding='2'>
				<tr>
				<td style='text-align:right;'>$LANG[MODULE_FRIENDS]:</td>
				<td><input class='bselect' type='checkbox' name='module_friends' $checkedFriends /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[MODULE_DEEZER]:</td>
				<td><input class='bselect' type='checkbox' name='deezer' $checkeddeezer /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[MODULE_METACAFE]:</td>
				<td><input class='bselect' type='checkbox' name='metacafe' $checkedmetacafe /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[MODULE_IFRAME]:</td>
				<td><input class='bselect' type='checkbox' name='iframe' $checkediframe /></td>
				</tr>
				<tr>
				<td style='text-align:right;'>$LANG[OPTION_MOBILE_ENABLED]:</td>
				<td><input class='bselect' type='checkbox' name='mobile_enabled' $checkedmobile /></td>
				</tr>
				<tr>
				<td></td><td><input class='button' type='submit' value=\"$LANG[SAVE_SETTINGS]\" /></td>
				</tr>
				</table>
				</form>";
		
		$shardContentArray[] = $thisContentObj;
	}
	break;

	case "submitOptions":
	if (isInGroup($CURRENTUSER, 'admin')) {

		$module_friends = "0";
		if (isset($_POST['module_friends']))
			$module_friends = "1";

		$deezer = "0";
		if (isset($_POST['deezer']))
			$deezer = "1";

		$metacafe = "0";
		if (isset($_POST['metacafe']))
			$metacafe = "1";

		$iframe = "0";
		if (isset($_POST['iframe']))
			$iframe = "1";

		$mobile_enabled = "0";
		if (isset($_POST['mobile_enabled']))
			$mobile_enabled = "1";

		mf_query("UPDATE settings SET deezer = '$deezer', module_friends = '$module_friends', metacafe = '$metacafe', iframe = '$iframe', mobile_enabled = '$mobile_enabled' WHERE 1");
		
		header("Location: ".make_link("admin"));
	}

	break;

	case "recalc_user_stats":
	if (isInGroup($CURRENTUSER, 'admin')) {

		$username = mf_query("SELECT username FROM users WHERE ID = '$_REQUEST[userID]' LIMIT 1");
		$username = mysql_fetch_assoc($username);

		update_rank_optional($_REQUEST['userID'],$username['username']);
		
		header("Location: ".make_link("forum","&action=g_ep&ID=$_REQUEST[userID]","#user/$_REQUEST[userID]"));
	}
	break;
	
	
	endswitch;
}
?>