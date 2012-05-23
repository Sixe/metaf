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
*/    //
// user_profilelib.php

// Get permissions *once*
if (isInGroup($CURRENTUSER, "admin")) {
	$verifyEditDelete = true;
}
else {
	$verifyEditDelete = false;
}


function userprofile($userID,$self=false) {    
	global $siteSettings;
	global $CURRENTUSERID;
	global $CURRENTUSER;
	global $LANG;
	global $verifyEditDelete;
	global $CURRENTUSERAJAX;

    if ($CURRENTUSER != "anonymous") {
    	if (!is_numeric($userID))
    		exit('$LANG[USER_PROFILE_INVALID_USERID]');
	
		$returnStr = "";

		$pInfo = mf_query("SELECT users.*, forum_user_nri.*, users_friends.friendType 
						FROM users
						LEFT JOIN forum_user_nri ON users.ID=forum_user_nri.userID
						LEFT JOIN users_friends ON (users_friends.userID = '$CURRENTUSERID' AND users_friends.target_userID=users.ID) 
						WHERE users.ID='$userID' limit 1");
		$pInfo = mysql_fetch_assoc($pInfo);
		
		if ($pInfo['userstatus'] == "DELETED") {
			$userID = "1";
			$username_del = $pInfo['username'];
			unset($pInfo);
			$pInfo['username'] = $username_del;
			$pInfo['userstatus'] = "DELETED";
			
		}

		$canseemod = false;
		if (isInGroup($CURRENTUSER, 'modo') || isInGroup($CURRENTUSER, 'admin') || $CURRENTUSERID == $userID || $siteSettings['viewmodlist'] == "3")
			$canseemod = true;
		else if ($siteSettings['module_friends'] && ($siteSettings['viewmodlist'] == "1" || $siteSettings['viewmodlist'] == "2")) {
			$friendstatus = friendstatus($CURRENTUSERID,$userID);
			if ($siteSettings['viewmodlist'] == "1" && $friendstatus == 2)
				$canseemod = true;
			else if ($siteSettings['viewmodlist'] == "2" && $friendstatus == 1)
				$canseemod = true;
		}
		
		$inline = time() - $pInfo['lat'];
		if ($inline < 300)
			$inline = $LANG['ONLINE'];
		else
			$inline = "";

		if ($pInfo['avatar'] == "")
			$avStr = "<div class='profileAvatarHolder' style='width: 100px; height: 100px;text-align:center;'>($LANG[USER_PROFILE_NO_AVATAR])</div>";
		else
			$avStr = "<img class='profileAvatarHolder' src='$pInfo[avatar]' alt='$LANG[AVATAR]' />";
		if (!$self) {
			$returnStr .= "<div style='display:table;width:100%;border-bottom:1px dashed silver;'>";
			$returnStr .= "
					<div style='display:inline-block;margin-left:8px;vertical-align:bottom;float:right;margin-bottom:6px;'>
						<div style='display:table;'>
							<div class='row'>
								<div class='cell' style='vertical-align:bottom;'>$LANG[SEE_PROFILE]</div>
								<div class='cell'>
									<input type='text' size='24' name='name' autocomplete='off' class='bselect' style='margin-left:4px;vertical-align: middle;color:#000000;' id='userprofilename0' onkeyup=\"input_user(0); return false;\" onfocus=\"show_select_user(0);\" onblur=\"hide_select_user(0);\"/> 
									<div id='inputSelectUser0' class='user_list' style='margin-left:4px;'></div>
								</div>
							</div>
						</div>
					</div>";
			$returnStr .= "<div style='float:left;margin-bottom:6px;'>";
			if (!array_key_exists( "ID", $_REQUEST ) || $_REQUEST['shard'] == "blog")
				$returnStr .= "<div style='display:inline-block;text-align:right;'>
						<a href='".make_link("forum")."'>
							<span onclick=\"emptymain(); return false;\" class='button'><img src='engine/grafts/$siteSettings[graft]/images/arrow_left.png' style='vertical-align: top; margin-top: 0px;' alt='' />$LANG[RETURN_PREVIOUS_SHORT]</span>
						</a>
					</div>";
			if ($siteSettings['module_friends'] && $CURRENTUSERID != $userID) {
				$returnStr .= " <div style='display:inline-block;margin-left:6px;' id='hide_user'>";
				if ($pInfo['friendType'] != 3 && $pInfo['friendType'] != 1) {
					if ($userID != "1" && !isInGroup($userID, 'modo') && !isInGroup($userID, 'admin'))
						$returnStr .= "<span onclick=\"hide_user('$userID','3');\" class='button' title=\"$LANG[BLOCK_USER_BUTTON]\">$LANG[BLOCK_USER] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/downarrowoff.gif' style='vertical-align:middle;' alt='' /></span>";
					$returnStr .= "<span onclick=\"hide_user('$userID','1');\" class='button' title=\"$LANG[FRIEND_USER_BUTTON]\" style='margin-left:8px;'>$LANG[FRIEND_USER] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' style='vertical-align:middle;' alt='' /></span>";
				}
				else if ($pInfo['friendType'] == 3)
					$returnStr .= "<span onclick=\"hide_user('$userID','2');\" class='button' title=\"\">$LANG[UNBLOCK_USER] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/uparrowoff.gif' style='vertical-align:middle;' alt='' /></span>";
				else if ($pInfo['friendType'] == 1)
					$returnStr .= "<span onclick=\"hide_user('$userID','2');\" class='button' title=\"\">$LANG[UNFRIEND_USER] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/unsubscribed.png' style='vertical-align:middle;' alt='' /></span>";
				$returnStr .= "</div> ";
			}
			if ($siteSettings['module_friends']) {
				$returnStr .= "<a href='".make_link("forum","&amp;action=g_users&amp;type=1")."' title=\"\" class='button'  style='margin-left:6px;'>$LANG[FRIEND_LIST_FRIEND]</a>";
				$returnStr .= "<a href='".make_link("forum","&amp;action=g_users&amp;type=3")."' title=\"\" class='button'  style='margin-left:8px;'>$LANG[FRIEND_LIST_BLOCKED]</a>";
			}
			$returnStr .= "<div style='display:inline-block;margin-left:8px;'>
						<a href='".make_link("forum","&amp;action=g_users&amp;userf=al")."' class='button' title='$LANG[USERS_LIST]'>
							$LANG[USERS_LIST]
						</a>
					</div>";
			$returnStr .= "</div></div><div style='clear:both;'></div>";

			$returnStr .= "<div style='font-size:1.8em;'>$LANG[MEMBER_PROFILE]
					<a href='".make_link("forum","&action=g_ep&ID=$userID","#user/$userID")."' onclick=\"userprofile2('$userID'); return false;\">
					<span style='font-weight:bold;font-size:1.2em;'>$pInfo[username]</span></a></div>";
			if ($pInfo['userstatus'] == "DELETED")
				$returnStr .= " <span style='color:red;font-size:1.5em;'>($LANG[ACCOUNT_DELETED])</span>";
		}

		$returnStr .= "<table><tr><td valign='top'>$avStr</td>";

		$returnStr .= "<td  width='380px' valign='top'>";
		$returnStr .= "<div class='profileHeader'>$LANG[USER_PROFILE_PERSONAL_INFO]</div><table cellpadding='3'>";
		
		if ($pInfo['realname'] != "")
			$returnStr .= "<tr><td style='text-align:right; width: 120px;'><b>$LANG[USER_PROFILE_REALNAME]</b> </td><td>" . $pInfo['realname'] . "</td></tr>";
		
		if ($pInfo['birthdate'] != "" && is_numeric($pInfo['birthdate']))
			$returnStr .= "<tr><td style='text-align:right;'><b>$LANG[USER_PROFILE_BIRTHDAY]</b> </td><td>" . date($LANG['DATE_LINE_MINIMAL2'],$pInfo['birthdate']) . "</td></tr>";		
			
		if ($pInfo['location'] != "")
			$returnStr .= "<tr><td style='text-align:right;'><b>$LANG[USER_PROFILE_LOCATION]</b> </td><td>" . $pInfo['location'] . "</td></tr>";
			
		if ($verifyEditDelete) {
			if ($pInfo['email']) {
				$listusermail = mf_query("SELECT ID, username FROM users WHERE email = '$pInfo[email]'");
				$i = 0;
				$maillist = "";
				while ($mail = mysql_fetch_assoc($listusermail)) {
					$i ++;
					$selected = "";
					if ($mail['ID'] == $userID)
						$selected = "selected='selected'";
					$maillist .= "<option value='$mail[ID]' $selected>$mail[username]</option>";
				}
				$returnStr .= "<tr><td style='text-align:right;'>
				<b>$LANG[USER_PROFILE_EMAIL]</b> </td><td>" . $pInfo['email'] . "</td></tr>";
				if ($i > 1) {	
					$returnStr .= "<tr><td style='text-align:right;'>";
					$returnStr .= "<b>$LANG[MULTIPLE_ACCOUNT_MAIL]</b> : </td><td><select class='bselect' id='usernamemail' name='usernamemail' onchange=\"userprofile4('usernamemail');\">$maillist</select>";
					$returnStr .= "</td></tr>";
				}
			}
			
			$user_ip = $pInfo['ip'];
			$iplist = "";
			if ($user_ip) {
				$banned = "";
				$ipbanned = mf_query("SELECT IP FROM ip WHERE IP = '$user_ip' LIMIT 1");
				$ipbanned = mysql_fetch_assoc($ipbanned);
				if ($ipbanned['IP'])
					$banned = "(banned)";
				$listuserip = mf_query("SELECT ID, username FROM users WHERE ip = '$user_ip'");
				$i = 0;
				while ($ip = mysql_fetch_assoc($listuserip)) {
					$i ++;
					$selected = "";
					if ($ip['ID'] == $userID)
						$selected = "selected='selected'";
					$iplist .= "<option value='$ip[ID]' $selected>$ip[username]</option>";
				}
				$returnStr .= "<tr><td style='text-align:right;'><b>IP:</b> </td><td>" . $user_ip . " $banned</td></tr>";
				if ($i > 1) {
					$returnStr .= "<tr><td style='text-align:right;'>";
					$returnStr .= "<b>$LANG[MULTIPLE_ACCOUNT_IP]</b> : </td><td><select class='bselect' id='usernameip' name='usernameip' onchange=\"userprofile4('usernameip');\">$iplist</select>";
					$returnStr .= "</td></tr>";
				}
			}
		}
		else
			$returnStr .= "<tr><td style='text-align:right;'><b>$LANG[USER_PROFILE_EMAIL]</b> </td><td>($LANG[USER_PROFILE_HIDEN])</td></tr>";			
			
		if ($pInfo['website'] != "")
			$returnStr .= "<tr><td style='text-align:right;'><b>$LANG[USER_PROFILE_WEBSITE]</b> </td><td><a href='$pInfo[website]'>" . $pInfo['website'] . "</a></td></tr>";			
			
		if ($pInfo['IM'] != "")
			$returnStr .= "<tr><td style='text-align:right;'>
				<b>$LANG[USER_PROFILE_IMSCREENAME]</b> </td>
				<td style='vertical-align:bottom;'>" . $pInfo['IM'] . "</td></tr>";			

		if ($pInfo['introducethread']) {
			$JSS2 = mf_query ("SELECT body from forum_topics WHERE ID = ".$pInfo['introducethread']." limit 1");
			$JSS2 = mysql_fetch_assoc($JSS2);
			$JSS2 = format_post($JSS2['body'], true);
			$JSS2 = str_replace("'", "\'", $JSS2);
			$JSS2 = str_replace("\r", "<br />", $JSS2);
			$JSS2 = str_replace("\n", "<br />", $JSS2);
			$JSS2 = str_replace("\t", "<br />", $JSS2);
			$JSS2 = str_replace("\"", "", $JSS2);
			$JSS2 = str_replace("<", "&#60;", $JSS2);
			$JSS2 = str_replace(">", "&#62;", $JSS2);
			$JSS2 = str_replace("(", "&#40;", $JSS2);
			$JSS2 = str_replace(")", "&#41;", $JSS2);

			$intro = $JSS2;

			$returnStr .= "<tr><td style='text-align:right;'><b>$LANG[FORUM_SETTING_PRESENT]:</b> </td><td>
				<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$pInfo[introducethread]","#thread/$pInfo[introducethread]/1")."' onmouseover=\"return newlayer('$intro', 'postContent_layer', 300, event);\" onmousemove=\"return movelayer(event);\" onmouseout=\"return closelayer();\">
				<b>$LANG[CLICK_HERE]</b></a></td></tr>";			
		}
			
		if ($pInfo['profile'] != "")
			$returnStr .= "<tr><td style='text-align:right; vertical-align:top;'>
			<b>$LANG[USER_PROFILE_PROFILE]</b> </td><td>" . $pInfo['profile'] . "</td></tr>";		
			
		$returnStr .= "</table></td>
			<td valign='top' width='230px'><div class='profileHeader'>$LANG[USER_PROFILE_STATS]</div>";
			
		$total_threads = $pInfo['num_threads'];
		$total_posmod = $pInfo['num_posmods'];
		$total_negmod = $pInfo['num_negmods'];
		$total_received_posmod = $pInfo['num_received_posmods'];
		$total_received_negmod = $pInfo['num_received_negmods'];

		$returnStr .= "
			<div style='display:inline-table;font-size:0.8em;'>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[USER_PROFILE_MEMBERSINCE]
				</div>
				<div style='display:table-cell;'>
					" . date($LANG['DATE_LINE_MINIMAL2'], $pInfo['datejoined']) . "
				</div>
			</div>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[LAST_CONNEXION]
				</div>
				<div style='display:table-cell;'>";
		if (!$pInfo['hidemyself'] && $inline)
			$returnStr .= "$inline";
		else
			$returnStr .= date($LANG['DATE_LINE_MINIMAL2'], $pInfo['lat']);
		$returnStr .= "
				</div>
			</div>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[USER_PROFILE_NRIRATING]
				</div>
				<div style='display:table-row;'>
					" . number_format($pInfo['rating'], 4) . "
				</div>
			</div>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[USER_PROFILE_POSTS]
				</div>
				<div style='display:table-cell;'>
					<span title=\"$LANG[USER_PROFILE_TEXT1_POSTS]\" >
						" . $pInfo['num_posts'] . "
					</span> / 
					<span title=\"$LANG[USER_PROFILE_TEXT2_POSTS]\" >
						" . $pInfo['num_posts_notnri'] . "
					</span>
				</div>
			</div>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[USER_PROFILE_THREADS]
				</div>
				<div style='display:table-cell;'>
					<span title=\"$LANG[USER_PROFILE_TEXT_THREADS]\" >$total_threads</span>
				</div>
			</div>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[USER_PROFILE_TIMESQUOTED]
				</div>
				<div style='display:table-cell;'>
					<span title=\"$LANG[USER_PROFILE_TEXT_TIMESQUOTED]\" >" . $pInfo['times_quoted'] . "</span>
				</div>
			</div>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[USER_PROFILE_TOTALPOSTRATING]
				</div>
				<div style='display:table-cell;'>
					<span title=\"$LANG[USER_PROFILE_TEXT_TOTALPOSTRATING]\" >" . $pInfo['cum_post_rating'] . "</span>
				</div>
			</div>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[USER_PROFILE_DONE_POSMODS]
				</div>
				<div style='display:table-cell;'>
					<span title=\"$LANG[USER_PROFILE_TEXT_DONE_POSMODS]\" >$total_posmod</span>";
		if ($canseemod)
			$returnStr .= "<div><span class='button_mini' onclick=\"list_modedposts('$userID','1','1');\">$LANG[LIST_MODED_POSTS]</span>&nbsp;<span class='button_mini' onclick=\"list_modedthreads('$userID','1','1');\">$LANG[LIST_MODED_THREADS]</span></div>";
		$returnStr .= "</div>
			</div>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[USER_PROFILE_DONE_NEGMODS]
				</div>
				<div style='display:table-cell;'>
					<span title=\"$LANG[USER_PROFILE_TEXT_DONE_NEGMODS]\" >$total_negmod</span>";
		if ($canseemod)
			$returnStr .= "<div><span class='button_mini' onclick=\"list_modedposts('$userID','2','1');\">$LANG[LIST_MODED_POSTS]</span>&nbsp;<span class='button_mini' onclick=\"list_modedthreads('$userID','2','1');\">$LANG[LIST_MODED_THREADS]</span></div>";
		$returnStr .= "</div>
			</div>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[USER_PROFILE_RECEIVED_POSMODS]
				</div>
				<div style='display:table-cell;'>
					<span title=\"$LANG[USER_PROFILE_TEXT_RECEIVED_POSMODS]\" >$total_received_posmod</span>";
		if ($canseemod)
			$returnStr .= "<div><span class='button_mini' onclick=\"list_modedposts('$userID','3','1');\">$LANG[LIST_MODED_POSTS]</span>&nbsp;<span class='button_mini' onclick=\"list_modedthreads('$userID','3','1');\">$LANG[LIST_MODED_THREADS]</span></div>";
		$returnStr .= "</div>
			</div>
			<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					$LANG[USER_PROFILE_RECEIVED_NEGMODS]
				</div>
				<div style='display:table-cell;'>
					<span title=\"$LANG[USER_PROFILE_TEXT_RECEIVED_NEGMODS]\" >$total_received_negmod</span>";
		if ($canseemod)
			$returnStr .= "<div><span class='button_mini' onclick=\"list_modedposts('$userID','4','1');\">$LANG[LIST_MODED_POSTS]</span>&nbsp;<span class='button_mini' onclick=\"list_modedthreads('$userID','4','1');\">$LANG[LIST_MODED_THREADS]</span></div>";
		$returnStr .= "</div>
			</div>";
		if (isInGroup($CURRENTUSER, 'admin'))
			$returnStr .= "<div style='display:table-row;'>
				<div style='display:table-cell;text-align:right;font-weight:bold;'>
					<a href='index.php?shard=admin&amp;action=recalc_user_stats&userID=$userID' class='button_mini'>Recalc stats</a>
				</div>
				<div style='display:table-cell;'>
				</div>
			</div>";

			$returnStr .= "</div></td></tr></table>";

		// List modedposts
		$returnStr .= "<div id='list_moded' style='background:white;min-width:680px;max-width:780px;position:absolute;border:2px solid silver;padding:4px;-webkit-box-shadow: -3px 3px 10px #AAA;-moz-box-shadow: -3px 3px 10px #AAA;box-shadow: -3px 3px 10px #AAA;display:none;z-index:2;'></div>";
		
		// Display public pictures albums
		$listalbums = "";
		$albums_query = mf_query("select ID from albums where public = '1' AND profile = '1' AND userID='$userID' order by date");
		while ($albums = mysql_fetch_assoc($albums_query)) {
			$listalbums .= load_album($albums['ID']);
		}
		if ($listalbums) {
			$returnStr .= "<div class='profileHeader' style='margin-bottom:8px;'>$LANG[USER_PROFILE_ALBUMS_PUBLIC]</div>";
			$returnStr .= "$listalbums";
		}

		// Display private pictures albums
		$listalbums = "";
		$albums_query = mf_query("select albumID from albums_users where userID = '$CURRENTUSERID' and owner_userID = '$userID'");
		while ($albums = mysql_fetch_assoc($albums_query)) {
			$listalbums .= load_album($albums['albumID']);
		}
		if ($listalbums) {
			$returnStr .= "<div class='profileHeader' style='margin-bottom:8px;'>$LANG[USER_PROFILE_ALBUMS_SHARED]</div>";
			$returnStr .= "$listalbums";
		}

		// Display last 10 blog threads
		$listblog = "";
		$blog_p_query = mf_query("select ID,title,date from forum_topics where blog = 2 AND threadtype < 3 and userID='$userID' order by date DESC limit 10");
		while ($blog_p = mysql_fetch_assoc($blog_p_query)) {
			$listblog .= "<div style='font-size:1.1em;font-weight:bold;'>
							<a href='".make_link("blog","&action=g_view&ID=$blog_p[ID]&userID=$userID")."'>
								$blog_p[title]
							</a>
						</div>
						<div style='font-size:0.71em;'>$LANG[POSTED] $LANG[ON] ".date($LANG['DATE_LINE_MINIMAL2'],$blog_p['date'])."</div>";
		}
		if ($listblog) {
			$returnStr .= "<div class='profileHeader'>$LANG[USER_PROFILE_LAST_BLOGS]<div style='float:right;font-size:1em;'><a href='".make_link("blog","&amp;userID=$userID")."'>$LANG[VIEW_BLOG_PERSO]</a></div></div>";
			$returnStr .= "$listblog";
		}

		// Teams
		$teams = "";
		if ($pInfo['hidemyteams'] == '0' || $verifyEditDelete) {
			$query = mf_query("SELECT teams.teamName FROM teams JOIN teams_users ON teams.teamID = teams_users.teamID WHERE teams_users.userID = '$userID' AND teams_users.level < 3 AND teams.hidemembers = 0 AND teams.hideteam = 0 ORDER BY teams.teamName");
			while ($row = mysql_fetch_assoc($query ))
				$teams.= "<div>$row[teamName]</div>";
			if ($teams) {
				$returnStr .= "<div class='profileHeader' style='margin-left:300px;'>$LANG[TEAM]</div>";
				$returnStr .= "<div style='margin-left:306px; margin-top:6px;'>$teams</div>";
			}
		}
	
		$groupAdmin = "";
		$adminname = "";
		if (!$self && ($verifyEditDelete || isInGroup($CURRENTUSER, 'level1') || isInGroup($CURRENTUSER, 'level9')))
			$adminname = $pInfo['username'];
		if (!$self && ($verifyEditDelete || isInGroup($CURRENTUSER, 'level1') || isInGroup($CURRENTUSER, 'level9'))) {
			$i = 0;
			$group0 = "";
			$group1 = "";
			$group2 = "";
			$group3 = "";
			$group4 = "";
			$group5 = "";
			$group6 = "";
			$group7 = "";
			$group8 = "";
			$group9 = "";
			$groups = mf_query("select * from groups where ID=1 limit 1");
			$groupname = mysql_fetch_assoc($groups);
			
			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='admin' limit 1");
			if ($pGroup = mysql_fetch_assoc($pGroup)) {
				$groupAdmin = $pGroup['pGroup'];
				$i++; }

//			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='level0' limit 1");
//			if ($pGroup = mysql_fetch_assoc($pGroup)) {
//				$group0 = $pGroup['pGroup'];
//				$i++; }

			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='level9' limit 1");
			if ($pGroup = mysql_fetch_assoc($pGroup)) {
				$group9 = $pGroup['pGroup'];
				$i++; }

			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='level1' limit 1");
			if ($pGroup = mysql_fetch_assoc($pGroup)) {
				$group1 = $pGroup['pGroup'];
				$i++; }

			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='level2' limit 1");
			if ($pGroup = mysql_fetch_assoc($pGroup)) {
				$group2 = $pGroup['pGroup'];
				$i++; }

			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='level3' limit 1");
			if ($pGroup = mysql_fetch_assoc($pGroup)) {
				$group3 = $pGroup['pGroup'];
				$i++; }

			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='level4' limit 1");
			if ($pGroup = mysql_fetch_assoc($pGroup)) {
				$group4 = $pGroup['pGroup'];
				$i++; }

			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='level5' limit 1");
			if ($pGroup = mysql_fetch_assoc($pGroup)) {
				$group5 = $pGroup['pGroup'];
				$i++; }

			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='level6' limit 1");
			if ($pGroup = mysql_fetch_assoc($pGroup)) {
				$group6 = $pGroup['pGroup'];
				$i++; }

			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='level7' limit 1");
			if ($pGroup = mysql_fetch_assoc($pGroup)) {
				$group7 = $pGroup['pGroup'];
				$i++; }

			$pGroup = mf_query("select * from permissiongroups where username=\"$adminname\" and pGroup ='level8' limit 1");
			if ($pGroup = mysql_fetch_assoc($pGroup)) {
				$group8 = $pGroup['pGroup'];
				$i++; }

			$userstatusb = $pInfo['userstatus'];

			if ($userID != '1') {
				$returnStr .= "<div class='profileHeader' style='margin-bottom:8px;'>$LANG[ADMIN]</div>
								<div style='display:inline-block;vertical-align:top;'><b>$LANG[GROUP]</b>: </div>
								<div style='display:inline-block;'><div style='display:block;'>";

				if (isInGroup($CURRENTUSER, 'admin')) {
					if (!$groupAdmin) {
						if ($group0)
							$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_0_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$LANG[MODERATOR]</a></span>";
						else
							$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_0&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$LANG[MODERATOR]</a></span>";
					}
					if (!$groupAdmin && $groupname['namedisp8']) {
						if ($group8)
						$returnStr .= "<span class='groups_button'>
							<a href='index.php?shard=user_profile&amp;action=g_8_off&amp;grname=$adminname' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp8]</a></span>";
						else
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_8&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp8]</a></span>";
					}
					if (!$groupAdmin && $groupname['namedisp7']) {
						if ($group7)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_7_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp7]</a></span>";
						else
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_7&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp7]</a></span>";
					}
					if (!$groupAdmin && $groupname['namedisp6']) {
						if ($group6)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_6_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp6]</a></span>";
						else
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_6&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp6]</a></span>";
					}
					if (!$groupAdmin && $groupname['namedisp5']) {
						if ($group5)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_5_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp5]</a></span>";
						else
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_5&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp5]</a></span>";
					}
					$returnStr .= "</div><div style='display:block;margin-top:6px;'>";
					if (!$groupAdmin && $groupname['namedisp4']) {
						if ($group4)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_4_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp4]</a></span>";
						else
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_4&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp4]</a></span>";
					}
					if (!$groupAdmin && $groupname['namedisp3']) {
						if ($group3)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_3_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp3]</a></span>";
						else
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_3&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp3]</a></span>";
					}
					if (!$groupAdmin && $groupname['namedisp2']) {
						if ($group2)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_2_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp2]</a></span>";
						else
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_2&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp2]</a></span>";
					}
					if (!$groupAdmin && $groupname['namedisp1']) {
						if ($group1)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_1_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp1]</a></span>";
						else
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_1&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp1]</a></span>";
					}
					if ($groupname['namedisp9']) {
						if ($group9)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_9_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp9]</a></span>";
						else
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_9&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp9]</a></span>";
					}
				}
				else if (isInGroup($CURRENTUSER, 'level9')) {
					if ($groupname['namedisp2']) {
					if ($group2)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_2_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp2]</a></span>";
					else if (!$group9)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_2&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp2]</a></span>";
					}
					if ($groupname['namedisp1']) {
					if ($group1)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_1_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp1]</a></span>";
					else
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_1&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp1]</a></span>";
					}
					if ($groupname['namedisp9']) {
						if ($group9)
							$returnStr .= "<span class='groups_button'><span class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp9]</span></span>";
					}
				}
				else if (isInGroup($CURRENTUSER, 'level1')) {
					if ($groupname['namedisp2']) {
						if ($group2)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_2_off&amp;grname=$adminname&amp;userID=$userID' class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp2]</a></span>";
						else if (!$group1 && !$group9)
						$returnStr .= "<span class='groups_button'><a href='index.php?shard=user_profile&amp;action=g_2&amp;grname=$adminname&amp;userID=$userID' class='button_mini_off' style='vertical-align: middle;'>$groupname[namedisp2]</a></span>";
					}
					if ($groupname['namedisp1']) {
						if ($group1)
						$returnStr .= "<span class='groups_button'><span class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp1]</span></span>";
					}
					if ($groupname['namedisp9']) {
						if ($group9)
							$returnStr .= "<span class='groups_button'><span class='button_mini_on' style='vertical-align: middle;'>$groupname[namedisp9]</span></span>";
					}
				}
				$returnStr .= "</div></div></div>";
			}
		}
			// BAN MANAGEMENT
		$userstatusb = $pInfo['userstatus'];
		if (($userstatusb == "" || $userstatusb == "banned") && $userID != '1' && !$groupAdmin && (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'modo'))) {
				// Load list of moderators having requested the ban of an active user
				$adminlist = "";
				$userstatus_text = "";
				$already_voted = 0;
				$thread_input = "";
				if ($i != 0 || $pInfo['rating'] >= .32) {
					$count_vote_admin = 0;
					$date24h = time() - (3600*24);
					$query = mf_query("SELECT adminname, threadID, reason, date from ban_requested where username = \"$adminname\" AND date > '$date24h' ORDER BY date DESC");
					while ($adminlistq = mysql_fetch_assoc($query)) {
						$count_vote_admin ++;
						$adminlist .= "<b>$adminlistq[adminname]</b> le ".date($LANG['DATE_LINE_MINIMAL2'],$adminlistq['date'])." $LANG[TO_MIN] ".date($LANG['DATE_LINE_TIME'],$adminlistq['date'])." - $LANG[BAN_REASON]: <i>\"$adminlistq[reason]\"</i><br/>";
						$thread_input = "<input type='hidden' name='threadID' value='$adminlistq[threadID]' />";
						if ($adminlistq['adminname'] == $CURRENTUSER)
							$already_voted = 1;
					}
					if ($adminlist)
						$adminlist = "<br/><br/><u>$LANG[BAN_WHO_ASKED]:</u><br/>$adminlist";
				}
				//
				// Automatic unban 
				if ($pInfo['userstatus'] == "banned") {
					$infoban = mf_query("SELECT * FROM ban WHERE username = \"$adminname\" ORDER BY DATE DESC limit 1");
					$infoban = mysql_fetch_assoc($infoban);
					if ($infoban['end_date'] && $infoban['end_date'] < time()) {
						$userstatus_text = NULL;
						$pInfo['userstatus'] = NULL;
						$ban_username = $adminname;
						$getThreadId = mf_query("select ID, category from forum_topics 
												where threadtype < 3 AND userID = '1' and title = \"$LANG[BANNED_THREAD]\" limit 1");
						$getThreadId2 = mysql_fetch_assoc($getThreadId);
						$threadID = $getThreadId2['ID'];
						$msg = "$LANG[UNBAN_1] [url=http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?shard=forum&amp;action=un2id&amp;name=".$ban_username."][b]".$ban_username."[/b][/url] $LANG[UNBAN_2][br]$LANG[UNBAN_3]";
						if ($infoban['ip'])
							$msg .= "[br]$LANG[UNBAN_IP]";
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

						// User ban thread
						$user_threadID = $infoban['threadID'];
						$msg = "$LANG[UNBAN_4]";
						$inTime = time();
						$result = mf_query("INSERT INTO forum_posts
									(body, user, userID, date, threadID, rating)
									VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $user_threadID , 0)");
						$lastPost = mf_query("select ID, user from forum_posts where userID=1 and date='$inTime' order by ID limit 0,1");
						$lastPost = mysql_fetch_assoc($lastPost);
						mf_query("update forum_topics 
								set threadtype='2', last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', 
								num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 where ID='$user_threadID ' limit 1");


						mf_query("update users set userstatus=NULL where username=\"$ban_username\" limit 1");
						$time_banned = time();
						mf_query("insert into ban (username, date, admin, banned) value (\"$ban_username\", '$time_banned', \"$siteSettings[systemuser]\", '0')");
						if ($infoban['ip'])
							mf_query("delete from ip where IP='$infoban[ip]' limit 1");
					}
					else
						$userstatus_text = "$LANG[BANNED]";
				}
				$returnStr .= "<div style='display:table;'><div class='row'>
								<div class='cell bold' style='vertical-align:top;'>$LANG[STATUS]:</div>";
				// User already banned
				$ban_ip_disabled = "";
				if ($pInfo['userstatus'] == "banned") {

					$returnStr .= "<div class='cell' style='padding-left:6px;font-size:0.9em;vertical-align:middle;'>
									<span class='bold' style='vertical-align:top;color:red;'>$userstatus_text</span>
									&nbsp; <a href='index.php?shard=user_profile&amp;action=g_unban&amp;banname=$adminname&amp;user_threadID=$infoban[threadID]' class='button_mini' style='vertical-align: middle;'>$LANG[UNBAN_6]</a>
									<div style='margin-top:6px;'>
										$adminlist
									</div>";
					if (!$infoban['end_date'] || $infoban['end_date'] == "1618790400") {
						$end_ban = "$LANG[UNBAN_5]";
						$ban_hours = "0";
						$ban_def = "checked='checked'";
					}
					else {
						$end_ban = "$LANG[BANNED_TILL] ".date($LANG['DATE_LINE_MINIMAL2'],$infoban['end_date'])." $LANG[TO_MIN] ".date($LANG['DATE_LINE_TIME'],$infoban['end_date']).".";
						$ban_hours = round(($infoban['end_date'] - time()) / 3600,1);
					}
					if (!$user_ip && !$infoban['ip'])
						$ban_ip_disabled = "disabled='disabled'";
					if ($infoban['ip']) {
						$ban_ip = "checked='checked'";
						$ban_ip_state = "<input type='hidden' name='ban_ip_state' value='$infoban[ip]' />";
					}

					$returnStr .= "<div style='margin-top:6px;'>";
					if ($infoban['date'])
					$returnStr .= "$LANG[BANNED_DATE] ".date($LANG['DATE_LINE_MINIMAL2'],$infoban['date'])." $LANG[TO_MIN] ".date($LANG['DATE_LINE_TIME'],$infoban['date'])." $LANG[BY] <b>$infoban[admin]</b> $end_ban &nbsp; ";
					$returnStr .= "<span onclick=\"this.blur();toggleLayer('ban_form');\" class='button_mini'>$LANG[BAN_MODIFY]</span>
						</div>
						<div id='ban_form' style='display:none;padding-top:6px;'>
						<form action='index.php?shard=user_profile&amp;action=g_ban_update' method='post'>
						<input type='hidden' name='userip' value='$user_ip'/>
						<input type='hidden' name='banname' value=\"$adminname\" />
						<div style='margin-top:6px;'>
						$LANG[BANNED_TIME_TO_DO]: <input type='text' class='bselect' name='ban_time' size='1' value='$ban_hours' /> $LANG[BANNED_HOURS]
							<input type='checkbox' class='bselect' name='ban_def' $ban_def style='vertical-align:bottom;margin-left:8px;' /> $LANG[BAN_ALWAYS]
							<input type='checkbox' $ban_ip_disabled class='bselect' $ban_ip name='ban_ip' style='vertical-align:bottom;margin-left:8px;' /> $LANG[BAN_BLOCK_IP]
							$ban_ip_state
						</div>
						<div style='margin-top:6px;'>
							$LANG[BAN_REASON]: <textarea cols='40' rows='3' name='ban_reason' class='bselect' style='vertical-align:top;'>$infoban[reason]</textarea>
							<input class='button' type='submit' value=\"$LANG[BAN_MODIF_T0]\" style='vertical-align:bottom;margin-left:8px;' />
						</div>	
						</form>";

						$returnStr .= "</div></div></div>";
				}
				// Not active or privileged user
				else if ($i == 0 && $pInfo['rating'] < .32) {
					if (!$user_ip)
						$ban_ip_disabled = "disabled='disabled'";
					$returnStr .= "<div class='cell' style='padding-left:6px;font-size:0.9em;vertical-align: middle;'><span onclick=\"this.blur();toggleLayer('ban_form');\" class='button_mini'>$LANG[BAN_ASK]</span> ";
					$returnStr .= "<div id='ban_form' style='display:none;'>
						<form action='index.php?shard=user_profile&amp;action=g_ban' method='post'>
						<input type='hidden' name='userip' value='$user_ip'/>
						<input type='hidden' name='banname' value=\"$adminname\" />
						<input type='hidden' name='banadmin' value=\"$CURRENTUSER\" />
						<div style='margin-top:6px;'>
						$LANG[BAN_LENGHT]: <input type='text' class='bselect' name='ban_time' size='1' value='24' /> $LANG[BANNED_HOURS]
							<input type='checkbox' class='bselect' name='ban_def' style='vertical-align:bottom;margin-left:8px;' /> $LANG[BAN_ALWAYS]
							<input type='checkbox' $ban_ip_disabled class='bselect' name='ban_ip' style='vertical-align:bottom;margin-left:8px;' /> $LANG[BAN_BLOCK_IP]
						</div>
						<div style='margin-top:6px;'>
							$LANG[BAN_REASON]: <textarea class='bselect' cols='40' rows='3' name='ban_reason' style='vertical-align:top;'></textarea>
							<input type='submit' class='button' value=\"$LANG[BAN_USER]\" style='vertical-align:bottom;margin-left:8px;'/>
							<span style='vertical-align:bottom;margin-left:8px;'>$LANG[BAN_REASON_MANDATORY]</span>
						</div>
						</form>";
					$returnStr .= "</div></div></div>";
				}
				// Active or prileged user
				// requesting vote
				else if ($count_vote_admin < ($siteSettings['num_mods_to_ban'] - 1)) {
					if (!$user_ip)
						$ban_ip_disabled = "disabled='disabled'";
					if (!$already_voted)
						$returnStr .= "<div class='cell' style='padding-left:6px;font-size:0.9em;vertical-align: middle;'><span onclick=\"this.blur();toggleLayer('ban_form');\" class='button_mini'>$LANG[BAN_ASK]</span> ";
					else
						$returnStr .= "<div class='cell' style='font-size:0.9em;'>$LANG[BAN_YOU_ASKED]";

					$returnStr .= $adminlist;
					$returnStr .= "<div id='ban_form' style='display:none;'>
						<form action='index.php?shard=user_profile&amp;action=g_ban_request' method='post'>
						<input type='hidden' name='banname' value=\"$adminname\" />
						<div class='bold' style='margin-top:6px;'>$LANG[BAN_MUST_HAVE_ASKED]</div>
						<div style='display:table;margin-top:6px;'>
							<div class='row'>
								<div class='cell' style='vertical-align:top;'>$LANG[BAN_REASON]:</div>
								<div class='cell' style='padding-left:4px;'><textarea class='bselect' cols='40' rows='3' name='ban_reason' style='vertical-align:top;'></textarea></div>
								<div class='cell' style='vertical-align:bottom;padding-left:4px;'>$thread_input
									<div><input type='submit' class='button' value=\"$LANG[BAN_ASK2]\"  style='vertical-align:bottom;'/></div>
									<div>$LANG[BAN_PT_CREATE].</div>
								</div>
							</div>
						</div>
						</form>";
					$returnStr .= "</div></div></div>";
				}
				// At least the minimum requested modos have voted to ban the user
				else {
					if (!$user_ip)
						$ban_ip_disabled = "disabled='disabled'";
					if (!$already_voted)
						$returnStr .= "<div class='cell' style='padding-left:6px;font-size:0.9em;vertical-align: middle;'><span onclick=\"this.blur();toggleLayer('ban_form');\" class='button_mini'>$LANG[BAN_ASK]</span> ";
					else
						$returnStr .= "<div class='cell' style='padding-left:6px;font-size:0.9em;vertical-align: middle;'>$LANG[BAN_YOU_ASKED]";
					$returnStr .= $adminlist;
					$returnStr .= "<div id='ban_form' style='display:none;'>
						<form action='index.php?shard=user_profile&amp;action=g_ban' method='post'>
						<input type='hidden' name='userip' value='$user_ip'/>
						<input type='hidden' name='banname' value=\"$adminname\" />
						<input type='hidden' name='banadmin' value=\"$CURRENTUSER\" />
						<div style='margin-top:6px;'>
						$LANG[BAN_LENGHT]: <input type='text' class='bselect' name='ban_time' size='1' value='24' /> $LANG[BANNED_HOURS]
							<input type='checkbox' class='bselect' name='ban_def' style='vertical-align:bottom;margin-left:8px;' /> $LANG[BAN_ALWAYS]
							<input type='checkbox' $ban_ip_disabled class='bselect' name='ban_ip' style='vertical-align:bottom;margin-left:8px;' /> $LANG[BAN_BLOCK_IP]
						</div>
						<div style='margin-top:6px;'>
							$LANG[BAN_REASON]: <textarea class='bselect' cols='40' rows='3' name='ban_reason' style='vertical-align:top;'></textarea>
						$thread_input
							<input type='submit' class='button' value=\"$LANG[BAN_USER]\" style='vertical-align:bottom;margin-left:8px;' />
							<span style='vertical-align:bottom;margin-left:8px;'>$LANG[BAN_REASON_MANDATORY]</span>
						</div>
						</form>";
					$returnStr .= "</div></div></div>";
				}

				$returnStr .= "</div>";
			}
			$returnStr .= "<div class='clearfix'></div>";

// View threads and messages, bar
		if ($userID != '1'){
			$returnStr .= "<div class='profileHeader'><small>";
			if ($pInfo['name'] == $CURRENTUSER && !$self && $CURRENTUSERAJAX)
				$returnStr .= "
				<span onclick='searchUser(\"$pInfo[name]\",\"1\",\"\"); return false;' class='button'>$LANG[ALL_MY_THREADS]</span> 
				<span onclick='searchUser(\"$pInfo[name]\",\"\",\"1\"); return false;' class='button'>$LANG[ALL_MY_POSTS]</span>";
			else if ($pInfo['name'] == $CURRENTUSER || $self)
				$returnStr .= "
				<a href='".make_link("forum","&amp;user=$pInfo[name]")."' class='button'>$LANG[ALL_MY_THREADS]</a> 
				<a href='".make_link("forum","&amp;user=$pInfo[name]&amp;search=1")."' class='button'>$LANG[ALL_MY_POSTS]</a>";
			else if ($CURRENTUSERAJAX && !array_key_exists( "ID", $_REQUEST ) and $_REQUEST['shard'] != "blog")
				$returnStr .= " 
				<a href='".make_link("forum","&amp;action=g_crt_new&amp;toList=$pInfo[name]")."' class='button'>
				$LANG[USER_PROFILE_SEND_PT_TO]</a>
				<span onclick='searchUser(\"$pInfo[name]\",\"1\",\"\"); return false;' class='button'>
				$LANG[USER_PROFILE_ALLTHREADS]</span>  
				<span onclick='searchUser(\"$pInfo[name]\",\"\",\"1\"); return false;' class='button'>
				$LANG[USER_PROFILE_ALLPOSTS]</span>";
		else
				$returnStr .= " 
				<a href='".make_link("forum","&amp;action=g_crt_new&amp;toList=$pInfo[name]")."' class='button'>
				$LANG[USER_PROFILE_SEND_PT_TO]</a>
				<a href='".make_link("forum","&amp;user=$pInfo[name]")."' class='button'>
				$LANG[USER_PROFILE_ALLTHREADS]</a> 
				<a href='".make_link("forum","&amp;user=$pInfo[name]&amp;search=1")."' class='button'>
				$LANG[USER_PROFILE_ALLPOSTS]</a>";
			if ($verifyEditDelete && !$self && $pInfo['name'] != $CURRENTUSER)
				$returnStr .= " <a href='".make_link("admin","&amp;action=g_rename_user&amp;user=$pInfo[name]")."' class='button'>
				$LANG[RENAME_USER]</a>";
		}
		$returnStr .= "</small></div>";

    return $returnStr;
	}
}

?>