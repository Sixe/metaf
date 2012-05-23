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

// team.php

require("teamlib.php");


if ($siteSettings['rules_et_thread'] && !$CURRENTUSERRULESET && $CURRENTUSER != "anonymous" && $action != "acceptrules") {

	$thisContentObj = New contentObj;
	$thisContentObj->primaryContent = $menuStr;
	$thisContentObj->primaryContent .= "<div style='height:20px'></div>";

	$thisContentObj->primaryContent .= "<div style='padding:16px;'>";
	$thisContentObj->primaryContent .= rules_et();
	$thisContentObj->primaryContent .= "<form name='rulesaccept' action='index.php?shard=teams&amp;action=acceptrules' method='post'>
				<div style='margin-top:12px;text-align:center;'>
					<div><input class='controls' type='checkbox' name='acceptrules' />$LANG[RULES_ET_TEXT1]</div>
					<div><input name='rulessubmit' type='submit' value=\"$LANG[SUBMIT]\" class='button' /></div>
					<div>($LANG[RULES_ET_TEXT2])</div>
				</div></form>";
	$thisContentObj->primaryContent .= "</div>";
	$shardContentArray[] = $thisContentObj;
}
else
	switch ($action):

case "g_default":
if ($CURRENTUSER != "anonymous") {

	$co = New contentObj;
	$co->primaryContent = "<div id='container'>";
	$co->primaryContent .= "<div style='font-size:2.5em;'>$LANG[TEAM_SHARE]</div>";
	$co->primaryContent .= "<div style='font-size:0.8em;margin-top:8px;margin-bottom:32px;'>$LANG[TEAM_TEXT]</div>";
	
	if ($CURRENTSTATUS != "banned")
		$co->primaryContent .= "<div style='text-align:center;'><form action='".make_link("teams","&amp;action=g_addNewTeam")."' method='post'>
					<div style='display;table;'>
					<div class='row'><div class='cell_right'>$LANG[CREATE_NEW_TEAM]:&nbsp;</div>
					<div class='cell'><input type='text' name='newteam' size='30' class='bselect'/></div>
					<div class='cell'>&nbsp;<input class='button' type='submit' value=\"$LANG[SUBMIT]\" /></div></div>
					</div></form></div>";

	$teams = mf_query("SELECT teams_users.userID, teams_users.level, teams.* FROM teams_users JOIN teams ON teams_users.teamID = teams.teamID WHERE teams_users.userID = '$CURRENTUSERID' AND teams_users.level = '3' ORDER BY teams.teamName");
	$invited = listteam($teams,"1");
	if ($invited) {
		$co->primaryContent .= "<div style='margin-top:32px;font-size:2em;text-align:center;border-bottom:1px solid black;'>$LANG[TEAM_INVITES]: </div>";
		$co->primaryContent .= $invited;
	}

	$teams = mf_query("SELECT teams_users.userID, teams_users.level, teams.* FROM teams_users JOIN teams ON teams_users.teamID = teams.teamID WHERE teams_users.userID = '$CURRENTUSERID' AND teams_users.level != '3' ORDER BY teams.teamName");
	$co->primaryContent .= "<div style='margin-top:32px;font-size:2em;text-align:center;border-bottom:1px solid black;'>$LANG[TEAM_YOURS]: </div>";
	$co->primaryContent .= listteam($teams);

	if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level9')) {
		$teams = mf_query("SELECT teams_users.userID, teams.* FROM teams LEFT JOIN teams_users ON teams_users.teamID = teams.teamID WHERE teams.teamID NOT IN (SELECT teamID FROM teams_users WHERE userID = '$CURRENTUSERID') ORDER BY teams.teamName");
		$listteam = listteam($teams);
		if ($listteam)
			$co->primaryContent .= "<div style='margin-top:32px;font-size:2em;text-align:center;border-bottom:1px solid black;'>$LANG[TEAM_NOT_YOURS]:</div>".$listteam;
	}

	$co->primaryContent .= "</div>";

	if ($siteSettings['rules_et_thread']) {
		$co->primaryContent .= "<div class='subMenuParam'></div>";
		$co->primaryContent .= display_rules_et();
	}

	$shardContentArray[] = $co;
}
break;

case "acceptrules":
if ($CURRENTUSER != "anonymous") {
   	$rules = $_POST['acceptrules'];
	if ($rules == "on")
		$rules = time();
	else
		$rules = "";

	mf_query("UPDATE users SET rules_et = '$rules' WHERE ID = '$CURRENTUSERID' LIMIT 1");
	if ($rules != "")
		header("Location: ".make_link("teams"));
	else
		header("Location: ".make_link("forum"));
}
break;

case "g_addNewTeam":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned") {

	$newteam = ltrim(rtrim(make_var_safe(htmlspecialchars($_REQUEST['newteam']))));
	$shortname = $newteam;
	if (strlen($shortname) > 20)
		$shortname = substr($shortname,0,20);
	$shortname = htmlentities($shortname);
	
	if ($newteam) {
		$verify = mf_query("SELECT teamName FROM teams WHERE teamName = \"$newteam\" LIMIT 1");
		if ($verify = mysql_fetch_assoc($verify)) {
			$co = New contentObj;
			$co->title = "$LANG[ERROR]!";
			$co->contentType="generic";
			$co->primaryContent = "<div style='margin-top:32px;font-weight:bold;font-size:2em;'>$LANG[TEAM_ALREADY_EXIST]</div>";
			$co->primaryContent .= "<div style='margin-top:8px;'><a href='".make_link("teams")."' class='button'>$LANG[BUTTON_BACK]</a></div>";
			$shardContentArray[] = $co;
			break;
		}
		else {
			$createdDate = time();
			mf_query ("INSERT INTO teams (teamName, teamShortName, createdBy, createdDate) VALUES (\"$newteam\", \"$shortname\", '$CURRENTUSERID', '$createdDate')");
			$teamID = mf_query("SELECT teamID FROM teams WHERE teamName = \"$newteam\" and createdDate = '$createdDate' LIMIT 1");
			$teamID = mysql_fetch_assoc($teamID);
			mf_query ("INSERT INTO teams_users (teamID, userID, level) VALUES (\"$teamID[teamID]\", '$CURRENTUSERID', '1')");

			header("Location: ".make_link("teams")."#team$teamID[teamID]");
		}
	}
}
break;

case "g_ConfdeleteTeam":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	if (isInTeam($teamID,$CURRENTUSERID) == "1" || isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level9')) {
		$verify = mf_query("SELECT teamName FROM teams WHERE teamID = '$teamID' LIMIT 1");
		if ($verify = mysql_fetch_assoc($verify)) {
			$co = New contentObj;
			$co->title = "$LANG[TEAM_DELETE] $team";
			$co->contentType="generic";
			$co->primaryContent .= "<div style='font-size:1.5em;color:red;margin-bottom:8px;margin-top:8px;'>$LANG[TEAM_DELETE_TEXT1]</div>$LANG[TEAM_DELETE_TEXT2] \"$LANG[TEAM_DELETE_BUTTON]\"<br/> $LANG[TEAM_DELETE_TEXT3] <span class='bold'>$verify[teamName]</span> :";
			$co->primaryContent .= "&nbsp;<a href='index.php?shard=teams&amp;action=deleteTeam&amp;teamID=$teamID' class='button_mini'>
				<img src='engine/grafts/$siteSettings[graft]/images/b_drop.png' border='0' style='vertical-align:middle;' alt='X' /> $LANG[TEAM_DELETE_BUTTON]</a>";
			$co->primaryContent .= " &nbsp; <a href='".make_link("teams")."' class='button bold'>$LANG[TEAM_NOTDELETE_BUTTON]</a>";
			$shardContentArray[] = $co;
		}
	}
}
break;

case "deleteTeam":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];

	if (isInTeam($teamID,$CURRENTUSERID) == "1" || isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level9')) {
		mf_query("DELETE FROM teams WHERE teamID = '$teamID' LIMIT 1");
		mf_query("DELETE FROM teams_users WHERE teamID = '$teamID'");
		mf_query("DELETE FROM teams_files WHERE teamID = '$teamID'");
		mf_query("DELETE FROM teams_series WHERE teamID = '$teamID'");
		mf_query("DELETE FROM files WHERE fileID NOT IN (SELECT fileID FROM teams_files)");
//		mf_query("UPDATE series SET teamID = '' WHERE teamID = '$teamID'");
	}
}
header("Location: ".make_link("teams"));
break;

case "g_Members":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	$team = mf_query("SELECT * FROM teams WHERE teamID = '$teamID' LIMIT 1");
	$team = mysql_fetch_assoc($team);

	$isinteam = isInTeam($teamID,$CURRENTUSERID);
	if (!$isinteam && !isInGroup($CURRENTUSER, 'admin') && !isInGroup($CURRENTUSER, 'level9'))
		exit($LANG['TEAM_NOT_MEMBER']);
	else if (!$isinteam && !isInGroup($CURRENTUSER, 'admin') && isInGroup($CURRENTUSER, 'level9') && $team['validated'] != "3" && $team['hidemembers'])
		exit($LANG['TEAM_NOT_ADMIN_ACCESS']);

	$team_settings = false;
	if ($isinteam == "1" || isInGroup($CURRENTUSER, 'admin') || ((!$team['hidemembers'] || $team['validated'] == "3") && isInGroup($CURRENTUSER, 'level9')))
		$team_settings = true;

	$co = New contentObj;
	$co->contentType="generic";
	$co->primaryContent = team_menu($teamID,true,$team_settings);
	$co->primaryContent .= "<div style='font-size:2em;'>Team $team[teamName]</div>";


	$userlist = "";
	$query_userlist = mf_query("SELECT teams_users.level, teams_users.userID, users.username FROM teams_users JOIN users ON users.ID = teams_users.userID WHERE teams_users.teamID = '$teamID'");
	while ($row = mysql_fetch_assoc($query_userlist)) {
		$teamlevel = "";
		$teamdelete = "";
		if ($row['level'] == "1")
			$teamlevel = " <span class='bold' style='font-size:0.9em;'>($LANG[TEAM_ADMIN])</span>";
		else if ($row['level'] == "3")
			$teamlevel = " <span class='bold' style='color:blue;font-size:0.9em;'>($LANG[TEAM_INVITE_SENT])</span>";
		if (($isinteam == "1" && $row['level'] != "1") || $CURRENTUSERID == $row['userID'])
			$teamdelete = "<a href='index.php?shard=teams&amp;action=removeUser&amp;userID=$row[userID]&amp;teamID=$teamID'><img src='engine/grafts/$siteSettings[graft]/images/b_drop.png' border='0' align='top' style='vertical-align:middle;' alt='X' /></a>";
		$userlist .= "<div class='row'";
		if (($isinteam == "1" || isInGroup($CURRENTUSER,"admin") || isInGroup($CURRENTUSER, 'level9')) && $row['level'] != "3")
			$userlist .= "style='cursor:pointer;' onclick=\"toggleLayer('userlevel$row[userID]','table-row');\"";
		$userlist .= "><div class='cell'>".$row['username']."&nbsp;</div><div class='cell'>".$teamlevel."</div><div class='cell'>".$teamdelete."</div></div>";
		if (($isinteam == "1" || isInGroup($CURRENTUSER,"admin") || isInGroup($CURRENTUSER, 'level9')) && $row['level'] != "3") {
			$userrights = "<form name='userrights$row[userID]' action='index.php?shard=teams&amp;action=change_userRights' method='post'><select name='changelevel' class='bselect'>";
			if ($row['level'] == "1")
				$userrights .= "<option value='2'>$LANG[TEAM_USER]</option><option value='1' selected='selected'>$LANG[TEAM_ADMIN]</option>";
			else
				$userrights .= "<option value='2' selected='selected'>$LANG[TEAM_USER]</option><option value='1'>$LANG[TEAM_ADMIN]</option>";
			$userrights .= "</select><div><input type='hidden' name='teamID' value='$teamID'/><input type='hidden' name='userID' value='$row[userID]'/><input type='hidden' name='currentlevel' value='$row[level]'/><input type='submit' class='button_mini' value=\"$LANG[SUBMIT_EDIT]\"/></div></form>";
			$userlist .= "<div class='row' id='userlevel$row[userID]' style='display:none;'><div class='cell right'>$LANG[TEAM_USER_RIGHTS]: </div><div class='cell'>$userrights</div><div class='cell'></div></div>";
		}
	}

	if ($isinteam == "1" || isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level9'))
		$co->primaryContent .= "
					<div style='display:inline-block;margin-left:8px;'><form action='".make_link("teams","&amp;action=g_addUserToTeam")."' method='post'>
						<div style='margin-top:8px;display:table;'>
							<div class='row'>
								<div class='cell'>$LANG[TEAM_INVITE_USER]: </div>
								<div class='cell'>
									<input type='text' size='12' name='name' autocomplete='off' class='bselect' style='vertical-align: middle;color:#000000;' id='userprofilename1' onkeyup=\"input_user(1); return false;\" onfocus=\"show_select_user(1);\" onblur=\"hide_select_user(1);\"/> 
									<input type='hidden' value='$teamID' name='teamID' /><span id='not_userprofile1'></span>
									<input class='button_mini' type='submit' value=\"$LANG[SUBMIT]\" />
									<div id='inputSelectUser1' class='user_list'></div>
								</div>
							</div>
						</div>
					</form></div>";

	$co->primaryContent .= "<div style='display:table;margin-top:8px;'>$userlist</div>";
	
	$co->primaryContent .= "<br/><br/><a href='".make_link("teams")."#team$teamID' class='button'>$LANG[BUTTON_BACK]</a>";
	$shardContentArray[] = $co;
}
break;

case "change_userRights":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_POST['teamID']) && is_numeric($_POST['userID'])) {
	$teamID = $_POST['teamID'];
	$userID = $_POST['userID'];
	if ($_POST['changelevel'] != $_POST['currentlevel']) {
		$isinteam = isInTeam($teamID,$CURRENTUSERID);
		if ($isinteam != "1" && !isInGroup($CURRENTUSER,"admin") && !isInGroup($CURRENTUSER,"level9"))
			exit("$LANG[TEAM_NOT_MEMBER]");

		if ($_POST['changelevel'] == "2") {
			$countuser = mf_query("SELECT userID FROM teams_users WHERE teamID = '$teamID' AND level = '1'");
			if (mysql_num_rows($countuser) < 2) {
				header("Location: ".make_link("teams","&action=g_only1admin&teamID=$teamID"));
				exit();
			}
			else
				mf_query("UPDATE teams_users SET level = '2' WHERE teamID = '$teamID' AND userID = '$userID' LIMIT 1");
		}
		else
			mf_query("UPDATE teams_users SET level = '1' WHERE teamID = '$teamID' AND userID = '$userID' LIMIT 1");
	}
	header("Location: ".make_link("teams","&action=g_Members&teamID=$teamID"));

}
break;

case "g_only1admin":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	$co = New contentObj;

	$co->contentType="generic";
	$co->primaryContent = "$LANG[TEAM_ONLY_ONE_ADMIN]";
	$co->primaryContent .= "<div style='margin-top:16px;'><a href='".make_link("teams","&action=g_Members&teamID=$teamID")."' class='button'>$LANG[BUTTON_BACK]</a></div><hr/>";
	$shardContentArray[] = $co;

}
break;

case "g_param":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_REQUEST['teamID'])) {
	
	$teamID = $_REQUEST['teamID'];
	$isinteam = isInTeam($teamID,$CURRENTUSERID);
	if (!$isinteam && !isInGroup($CURRENTUSER, 'admin') && !isInGroup($CURRENTUSER, 'level9'))
		exit("$LANG[TEAM_NOT_MEMBER]");
	else if ($isinteam && $isinteam != "1")
		exit($LANG['REFUSED']);

	$team = mf_query("SELECT * FROM teams WHERE teamID = '$teamID' LIMIT 1");
	$team = mysql_fetch_assoc($team);
	$team_members = false;
	if ($isinteam || isInGroup($CURRENTUSER, 'admin') || ((!$team['hideteam'] || $team['validated'] == "3") && isInGroup($CURRENTUSER, 'level9')))
		$team_members = true;

	$co = New contentObj;
	$co->contentType="generic";
	$co->primaryContent = team_menu($teamID,$team_members,true);
	$co->primaryContent .= "<div style='font-size:2em;'>Team $team[teamName]</div>";

	$co->primaryContent .= "<div style='margin-top:16px;border-top:solid 1px black;'>
					<form name='team$row[teamID]' action='index.php?shard=teams&action=teamparam' method='post'>";
	if ($team['validated'] != "3") {
		$checked = "";
		if ($team['hidemembers'])
			$checked = "checked='checked'";
		$co->primaryContent .= "<div>$LANG[SET_TEAM_NAME]: <input type='text' size='40' value=\"".$team['teamName']."\" name='teamName'/></div>
					$LANG[SET_TEAM_HIDE_MEMBERS]: <input type='checkbox' $checked name='hidemembers' style='vertical-align:sub;'/>";
		$checked = "";
		if ($team['hideteam'])
			$checked = "checked='checked'";
		$co->primaryContent .= " &nbsp; $LANG[SET_TEAM_HIDE]: <input type='checkbox' $checked name='hideteam' style='vertical-align:sub;'/>";
	}
	else
		$co->primaryContent .= "<input type='hidden' value=\"".$team['teamName']."\" name='teamName'/>
								<input type='hidden' name='hidemembers' value='$team[hidemembers]'/>
								<input type='hidden' name='hideteam' value='$team[hideteam]'/>";
	$co->primaryContent .= "<input type='hidden' value='$teamID' name='teamID'/>
					<div style='display:block;margin-bottom:16px;'></div>";
	$validated = "";
	$validation_thread = "";
	if ($team['validation_thread'])
		$validation_thread = "&nbsp; <a href='".make_link("forum","&amp;action=g_reply&amp;ID=$threadID&amp;page=1","#thread/$team[validation_thread]/1")."'>($LANG[TEAM_VALIDATION_THREAD])</a>";
	if ($team['validated'] == "1")
		$validated = "checked='checked'";
	if (isInGroup($CURRENTUSER, 'level9') && (!$team['validated'] || $team['validated'] == "1"))
		$co->primaryContent .= "$LANG[TEAM_VALIDATED]: <input type='checkbox' name='validate' $validated class='bselect' /> $validation_thread";
	else if (isInGroup($CURRENTUSER, 'level9') && $team['validated'] == "2")
		$co->primaryContent .= "$LANG[TEAM_VALIDATED]: <input type='checkbox' name='validate' $validated class='bselect' /> $validation_thread";
	if (!isInGroup($CURRENTUSER, 'level9') && !$team['validated'] && $isinteam)
		$co->primaryContent .= "<span class='button' onclick=\"location.href='index.php?shard=teams&amp;action=ask_validation&amp;teamID=$teamID';\">$LANG[TEAM_ASK_VALIDATION]</span> ($LANG[TEAM_ASK_VALIDATION_WARNING])";
	else if (!isInGroup($CURRENTUSER, 'level9') && $team['validated'] == "3" && $isinteam)
		$co->primaryContent .= "<div style='font-weight:bold;color:blue;'>$LANG[TEAM_VALIDATION_REQUESTED] &nbsp; <span class='button' onclick=\"location.href='index.php?shard=teams&amp;action=cancel_validation&amp;teamID=$teamID';\" style='color:black;'>$LANG[TEAM_VALIDATION_REQUEST_CANCEL]</span></div>";
	else if ($team['validated'] == "2" && $isinteam) {
		$co->primaryContent .= "<div style='font-weight:bold;color:red;'>$LANG[TEAM_VALIDATION_REFUSED]</div>";
		if (!isInGroup($CURRENTUSER, 'level9'))
			$co->primaryContent .= "<div style='margin-top:8px;'></div><span class='button' onclick=\"location.href='index.php?shard=teams&amp;action=ask_validation&amp;teamID=$teamID';\">$LANG[TEAM_ASK_VALIDATION_AGAIN]</span>";
	}
	else if (isInGroup($CURRENTUSER, 'level9') && $team['validated'] == "3")
		$co->primaryContent .= "<span class='button' style='color:green;' onclick=\"location.href='index.php?shard=teams&amp;action=validate_yes&amp;teamID=$teamID';\">$LANG[TEAM_VALIDATE]</span>&nbsp;<span class='button' style='color:red;' onclick=\"location.href='index.php?shard=teams&amp;action=validate_no&amp;teamID=$teamID';\">$LANG[TEAM_NOT_VALIDATE]</span> $validation_thread";
	else if ($team['validated'] == "1" && !isInGroup($CURRENTUSER, 'level9'))
		$co->primaryContent .= "<div style='font-weight:bold;color:green;'>$LANG[TEAM_VALIDATED]</div>";
	$co->primaryContent .= "<div style='display:block;margin-bottom:16px;'></div>";
	$co->primaryContent .= "<input type='submit' class='button' value=\"$LANG[SUBMIT_EDIT]\" />";
//	$co->primaryContent .= "<span style='margin-left:8px;'><a href='".make_link("teams")."#team$teamID' class='button'>$LANG[BUTTON_BACK]</a></span>";
	$co->primaryContent .= "</form></div>";

	$shardContentArray[] = $co;

}	
break;

case "ask_validation":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	if (isInTeam($teamID,$CURRENTUSERID) == "1") {
		$text = "";
		$validated = mf_query("SELECT validated, teamName FROM teams WHERE teamID = \"$teamID\" LIMIT 1");
		$validated = mysql_fetch_assoc($validated);
		if (!$validated['validated'])
			$text = "$LANG[TEAM_VALIDATION_REQUEST] [$validated[teamName]]";
		else if ($validated['validated'] == "2")
			$text = "$LANG[TEAM_VALIDATION_REQUEST_BIS] [$validated[teamName]]";
		if ($text) {
			$text2 = $text;
			$text2 .= "[br]$LANG[TEAM_VALIDATION_REQUEST_TEXT1] ([url=".make_link("teams","&action=g_param&teamID=$teamID")."]$LANG[LINK][/url]) $LANG[TEAM_VALIDATION_REQUEST_TEXT2]";
			$inTime = time();
			mf_query("INSERT INTO forum_topics
						(title, body, user, userID, date, threadtype, pthread, category, locked, num_views)
						VALUES (\"".htmlspecialchars($text)."\", \"$text2\", \"$siteSettings[systemuser]\", 1, $inTime, 1, 1, '1', '0', 0)");
			$getThreadId = mf_query("select ID, category from forum_topics 
										where threadtype < 3 AND userID = '1' and title = \"$text\" and date = '$inTime' limit 1");
			$getThreadId2 = mysql_fetch_assoc($getThreadId);
			$threadID = $getThreadId2['ID'];
			mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"".htmlspecialchars($text2)."\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
			$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=1 AND date='$inTime' AND threadID = '$threadID' ORDER BY ID LIMIT 0,1");
			$lastPost = mysql_fetch_assoc($lastPost);
			mf_query("update forum_topics 
						set last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = 1, num_comments_T = 1 
						where ID='$threadID' limit 1");
			$query = mf_query("SELECT userID FROM permissiongroups WHERE pGroup = 'level9'");
			while ($addadmin = mysql_fetch_assoc($query))
				mf_query("INSERT IGNORE INTO fhits (threadID, userID) VALUES ($threadID, $addadmin[userID])"); // admin
				
			mf_query("UPDATE teams SET validated = '3', validation_thread = '$threadID' WHERE teamID = '$teamID' LIMIT 1");
		}
	}
}
header("Location: ".make_link("teams","&action=g_param&teamID=$teamID"));
break;

case "cancel_validation":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	if (isInTeam($teamID,$CURRENTUSERID) == "1") {
		$text = "";
		$validated = mf_query("SELECT validated, validation_thread FROM teams WHERE teamID = \"$teamID\" LIMIT 1");
		$validated = mysql_fetch_assoc($validated);
		if ($validated['validated'] == "3") {
			$text2 .= "[b]$LANG[TEAM_VALIDATION_REQUEST_CANCELED][/b]";
			$inTime = time();
			mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"".htmlspecialchars($text2)."\", \"$siteSettings[systemuser]\", 1, $inTime, '$validated[validation_thread]', 0)");
			$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=1 AND date='$inTime' AND threadID = '$validated[validation_thread]' ORDER BY ID LIMIT 0,1");
			$lastPost = mysql_fetch_assoc($lastPost);
			mf_query("UPDATE forum_topics 
						SET threadtype='2', last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 
						WHERE ID='$validated[validation_thread]' LIMIT 1");
				
			mf_query("UPDATE teams SET validated = '', validation_thread = '' WHERE teamID = '$teamID' LIMIT 1");
		}
	}
}
header("Location: ".make_link("teams","&action=g_param&teamID=$teamID"));
break;

case "validate_yes":
if (isInGroup($CURRENTUSER, 'level9') && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	mf_query("UPDATE teams SET validated = '1', hideteam = '0' WHERE teamID = '$teamID' LIMIT 1");
	$validated = mf_query("SELECT teamName, validation_thread FROM teams WHERE teamID = \"$teamID\" LIMIT 1");
	$validated = mysql_fetch_assoc($validated);
	$text2 = "[color=green]$LANG[TEAM_VALIDATION_ACCEPTED1] \"$validated[teamName]\" $LANG[TEAM_VALIDATION_ACCEPTED2][/color]";
	$inTime = time();
	mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"".htmlspecialchars($text2)."\", \"$siteSettings[systemuser]\", 1, $inTime, '$validated[validation_thread]', 0)");
	$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=1 AND date='$inTime' AND threadID = '$validated[validation_thread]' ORDER BY ID LIMIT 0,1");
	$lastPost = mysql_fetch_assoc($lastPost);
	mf_query("UPDATE forum_topics 
						SET threadtype = '2', last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 
						WHERE ID='$validated[validation_thread]' LIMIT 1");
	header("Location: ".make_link("teams","&action=g_param&teamID=$teamID"));
}
break;

case "validate_no":
if (isInGroup($CURRENTUSER, 'level9') && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	mf_query("UPDATE teams SET validated = '2' WHERE teamID = '$teamID' LIMIT 1");
	$validated = mf_query("SELECT teamName, validation_thread FROM teams WHERE teamID = \"$teamID\" LIMIT 1");
	$validated = mysql_fetch_assoc($validated);
	$text2 = "[color=red]$LANG[TEAM_VALIDATION_REFUSED1] \"$validated[teamName]\" $LANG[TEAM_VALIDATION_REFUSED2][/color]";
	$inTime = time();
	mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"".htmlspecialchars($text2)."\", \"$siteSettings[systemuser]\", 1, $inTime, '$validated[validation_thread]', 0)");
	$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=1 AND date='$inTime' AND threadID = '$validated[validation_thread]' ORDER BY ID LIMIT 0,1");
	$lastPost = mysql_fetch_assoc($lastPost);
	mf_query("UPDATE forum_topics 
						SET threadtype = '2', last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 
						WHERE ID='$validated[validation_thread]' LIMIT 1");
	header("Location: ".make_link("teams","&action=g_param&teamID=$teamID"));
}
break;

case "teamparam":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_POST['teamID'])) {
	$teamID = $_POST['teamID'];
	if (isInTeam($teamID,$CURRENTUSERID) == "1" || isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level9')) {
		$teamName = ltrim(rtrim(make_var_safe(htmlspecialchars($_POST['teamName']))));
		if ($teamName) {
			$shortname = $teamName;
			if (strlen($shortname) > 20)
				$shortname = substr($shortname,0,20);
			$shortname = htmlentities($shortname);

			$hidemembers = make_var_safe( $_POST['hidemembers']);
			if ($hidemembers)
				$hidemembers = 1;
			else
				$hidemembers = 0;

			$hideteam = make_var_safe( $_POST['hideteam']);
			if ($hideteam)
				$hideteam = 1;
			else
				$hideteam = 0;

			if (isInGroup($CURRENTUSER, 'level9')) {
				$validate = make_var_safe( $_POST['validate']);
				if ($validate == "on")
					$validate = ", validated = '1'";
				else
					$validate = ", validated = '0'";
			}

			mf_query("UPDATE teams SET teamName = \"$teamName\", teamShortName = \"$shortname\", hidemembers = '$hidemembers', hideteam = '$hideteam' $validate WHERE teamID = '$teamID' LIMIT 1");
		}
	}
	header("Location: ".make_link("teams","#team$teamID"));
}
break;

case "g_addUserToTeam":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_POST['teamID'])) {
	$teamID = $_POST['teamID'];
	$isinteam = isInTeam($teamID,$CURRENTUSERID);
	if (!$isinteam && !isInGroup($CURRENTUSER, 'admin') && !isInGroup($CURRENTUSER, 'level9'))
		exit("$LANG[TEAM_NOT_MEMBER]");
	else if ($isinteam && $isinteam != "1")
		exit($LANG['REFUSED']);

	$username = make_var_safe($_REQUEST['name']);
	$userID = mf_query("SELECT ID FROM users WHERE username = \"$username\" limit 1");
	$userID = mysql_fetch_assoc($userID);
	$userID = $userID['ID'];
	if (!$userID)
		exit($LANG['TEAM_UNKNOWN_USER']);
	$verify = mf_query("SELECT userID FROM teams_users WHERE teamID = '$teamID' AND userID = '$userID' LIMIT 1");
	$verify = mysql_fetch_assoc($verify);
	if ($verify['userID']) {
		$co = New contentObj;
		$co->title = "$LANG[ERROR]!";
		$co->contentType="generic";
		$co->primaryContent = "<br/><br/><b>$LANG[TEAM_ALREADY_USER]</b>";
		$co->primaryContent .= "<br/><br/><a href='".make_link("teams","&amp;action=g_Members&amp;teamID=$teamID")."' class='button'>$LANG[BUTTON_BACK]</a>";
		$shardContentArray[] = $co;
		break;
	}
	else {
		$addedDate = time();
		
		// Create thread to inform the invited user
		$teamName = team_name($teamID);
		$text = make_var_safe("$LANG[TEAM_INVITE_TEXT1] [$username] $LANG[TEAM_INVITE_TEXT2] [$teamName]");
		if (strlen($text) > 100)
			$text = substr($text,0,100);
		$text2 = "$username,[br]";
		$text2 .= "$LANG[TEAM_INVITE_TEXT3] [b]".$teamName."[/b].";
		$text2 .= "[br]$LANG[TEAM_INVITE_TEXT4] [url=".make_link("teams")."]$LANG[TEAM_INVITE_TEXT5][/url]$LANG[TEAM_INVITE_TEXT6]";
		$text2 = make_var_safe($text2);
		$inTime = time();
		mf_query("INSERT INTO forum_topics
						(title, body, user, userID, date, threadtype, pthread, category, locked, num_views)
						VALUES (\"".$text."\", \"".$text2."\", \"$siteSettings[systemuser]\", 1, $inTime, 1, 1, '1', '0', 0)");
		$getThreadId = mf_query("SELECT ID, category FROM forum_topics 
										WHERE threadtype < 3 AND userID = '1' AND title = \"$text\" AND date = '$inTime' LIMIT 1");
		$getThreadId2 = mysql_fetch_assoc($getThreadId);
		$threadID = $getThreadId2['ID'];
		mf_query("INSERT INTO forum_posts
						(body, user, userID, date, threadID, rating)
						VALUES (\"".$text2."\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
		$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=1 AND date='$inTime' AND threadID = '$threadID' ORDER BY ID LIMIT 0,1");
		$lastPost = mysql_fetch_assoc($lastPost);
		mf_query("UPDATE forum_topics 
						SET last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = 1, num_comments_T = 1 
						WHERE ID='$threadID' LIMIT 1");
		mf_query("INSERT INTO fhits (threadID, userID) VALUES ('$threadID', '$userID')"); // user
		$query = mf_query("SELECT userID FROM teams_users WHERE teamID = '$teamID' AND level = '1'");
		while ($addadmin = mysql_fetch_assoc($query))
			mf_query("INSERT INTO fhits (threadID, userID) VALUES ($threadID, $addadmin[userID])"); // admin

		mf_query("INSERT INTO teams_users (teamID, userID, level, addedBy, addedDate, invite_thread) VALUES ('$teamID', '$userID', '3', \"$CURRENTUSERID\", '$addedDate', '$threadID')");
		header("Location: ".make_link("teams","&action=g_Members&teamID=$teamID"));
	}
}
break;

case "invite_accept":
if ($CURRENTUSER != "anonymous" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	$isinteam = isInTeam($teamID,$CURRENTUSERID);
	if (isInTeam($teamID,$CURRENTUSERID) == "3") {
		mf_query("UPDATE teams_users SET level = '2' WHERE teamID = '$teamID' AND userID = '$CURRENTUSERID' AND level = '3' LIMIT 1");
		$threadID = mf_query("SELECT invite_thread FROM teams_users WHERE userID = '$CURRENTUSERID' AND teamID = '$teamID' LIMIT 1");
		$threadID = mysql_fetch_assoc($threadID);
		$threadID = $threadID['invite_thread'];

		$query_thread = mf_query("SELECT ID FROM forum_topics WHERE teamID = '$teamID' AND threadtype < 3");
		while ($row = mysql_fetch_assoc($query_thread))
			mf_query("INSERT IGNORE INTO fhits (threadID, userID) VALUES ('$row[ID]', '$CURRENTUSERID')");
		$text = "[color=green]$LANG[TEAM_INVITE_ACCEPTED1] [$CURRENTUSER] $LANG[TEAM_INVITE_ACCEPTED2][/color]";
		$inTime = time();
		mf_query("INSERT INTO forum_posts
					(body, user, userID, date, threadID, rating)
					VALUES (\"".htmlspecialchars($text)."\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
		$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=1 AND date='$inTime' AND threadID = '$threadID' ORDER BY ID LIMIT 0,1");
		$lastPost = mysql_fetch_assoc($lastPost);
		mf_query("UPDATE forum_topics 
						SET threadtype = '2', last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 
						WHERE ID='$threadID' LIMIT 1");
	}
	header("Location: ".make_link("teams","#team$teamID"));
}
break;

case "invite_refuse":
if ($CURRENTUSER != "anonymous" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];

	if (isInTeam($teamID,$CURRENTUSERID) == "3") {
		$threadID = mf_query("SELECT invite_thread FROM teams_users WHERE userID = '$CURRENTUSERID' AND teamID = '$teamID' LIMIT 1");
		$threadID = mysql_fetch_assoc($threadID);
		$threadID = $threadID['invite_thread'];
		mf_query("DELETE FROM teams_users WHERE teamID = '$teamID' AND userID = '$CURRENTUSERID' AND level = '3' LIMIT 1");

		$text = "[color=red]$LANG[TEAM_INVITE_REFUSED1] [$CURRENTUSER] $LANG[TEAM_INVITE_REFUSED2][/color]";
		$inTime = time();
		mf_query("INSERT INTO forum_posts
					(body, user, userID, date, threadID, rating)
					VALUES (\"".htmlspecialchars($text)."\", \"$siteSettings[systemuser]\", 1, $inTime, $threadID, 0)");
		$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=1 AND date='$inTime' AND threadID = '$threadID' ORDER BY ID LIMIT 0,1");
		$lastPost = mysql_fetch_assoc($lastPost);
		mf_query("UPDATE forum_topics 
						SET threadtype = '2', last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 
						WHERE ID='$threadID' LIMIT 1");
	}
	header("Location: ".make_link("teams"));
}
break;

case "removeUser":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_REQUEST['teamID']) && is_numeric($_REQUEST['userID'])) {
	$teamID = $_REQUEST['teamID'];
	$userID = $_REQUEST['userID'];
	$isinteam = isInTeam($teamID,$CURRENTUSERID);
	$isinteam_userID = isInTeam($teamID,$userID);
	if (!$isinteam && !isInGroup($CURRENTUSER, 'admin') && !isInGroup($CURRENTUSER, 'level9'))
		exit($LANG['TEAM_NOT_MEMBER']);
	else if ($isinteam && $isinteam != "1" && $CURRENTUSERID != $userID  && !isInGroup($CURRENTUSER, 'admin') && !isInGroup($CURRENTUSER, 'level9'))
		exit($LANG['TEAM_DELETE_USER_REFUSED1']);
	else if ($isinteam_userID == "1" && $CURRENTUSERID != $userID && !isInGroup($CURRENTUSER, 'admin') && !isInGroup($CURRENTUSER, 'level9'))
		exit($LANG['TEAM_DELETE_USER_REFUSED2']);

	if ($isinteam_userID == "1") {
		$countuser = mf_query("SELECT userID FROM teams_users WHERE teamID = '$teamID' AND level = '1'");
		if (mysql_num_rows($countuser) < 2) {
			header("Location: ".make_link("teams","&action=g_only1admin&teamID=$teamID"));
			exit();
		}
	}

	mf_query("DELETE FROM teams_users WHERE userID = '$userID' AND teamID = '$teamID' LIMIT 1");
	mf_query("DELETE FROM fhits WHERE userID = '$userID' AND threadID IN (SELECT ID FROM forum_topics WHERE teamID = '$teamID')");

	if ($CURRENTUSERID != $userID)
		header("Location: ".make_link("teams","&action=g_Members&teamID=$teamID"));
	else
		header("Location: ".make_link("teams"));
}
break;

case "g_addThread":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	$isinteam = isInTeam($teamID,$CURRENTUSERID);
	if (!$isinteam)
		exit($LANG['TEAM_NOT_MEMBER']);
	else if ($isinteam != "1")
		exit($LANG['REFUSED']);

	$co = New contentObj;
	$co->title = "$LANG[TEAM_ADD_EXISTING_THREAD2] $team";
	$co->contentType="generic";
	$co->primaryContent .= "<div style='margin-top:32px;text-align:center;'><form action='".make_link("teams","&amp;action=g_writeaddThread&amp;teamID=$teamID")."' method='post'>
					<div style='display:table;'>
					<div class='row'><div class='cell_right'>$LANG[TEAM_ADD_EXISTING_THREAD3]:</div>
					<div class='cell'><input type='text' name='newthread' size='8' /></div>
					<div class='cell'><input class='button' type='submit' value=\"$LANG[SUBMIT]\" /></div></div>
					</div></form></div>";
	$shardContentArray[] = $co;
}
break;

case "g_writeaddThread":
if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	$team = team_name($teamID);
	$isinteam = isInTeam($teamID,$CURRENTUSERID);
	if (!$isinteam)
		exit($LANG['TEAM_NOT_MEMBER']);
	else if ($isinteam != "1")
		exit($LANG['REFUSED']);

	$threadID = make_var_safe( $_REQUEST['newthread']);
	$verifythread = mf_query("SELECT ID, pthread, team FROM forum_topics WHERE ID = '$threadID' AND pthread = 1 AND threadtype < 3 AND (teamID = '' OR teamID IS NULL) LIMIT 1");
	$verifythread = mysql_fetch_assoc($verifythread);
	if ($threadID != $verifythread['ID']) {
		$co = New contentObj;
		$co->title = "$LANG[ERROR]!";
		$co->contentType="generic";
		$co->primaryContent .= "$LANG[TEAM_ADD_THREAD_ERROR1] <b>$threadID</b> $LANG[TEAM_ADD_THREAD_ERROR2]";
		$co->primaryContent .= "<br/><br/><a href='".make_link("forum","","#threadlist/teams/".$teamID)."' class='button'>$LANG[BUTTON_BACK]</a>";
		$shardContentArray[] = $co;
		break;
	}
	$verifyuser = mf_query("SELECT userID FROM fhits WHERE userID = '$CURRENTUSERID' AND threadID = '$threadID' LIMIT 1");
	$verifyuser = mysql_fetch_assoc($verifyuser);
	if ($verifyuser['userID'] == $CURRENTUSERID) {
		$result = mf_query("UPDATE forum_topics SET teamID = '$teamID', team = \"$team\" WHERE ID='$threadID' LIMIT 1");
		$query = mf_query("SELECT userID FROM teams_users WHERE teamID = \"$teamID\" AND userID != \"$CURRENTUSERID\" AND level < 3");
		while ($adduser = mysql_fetch_assoc($query))
			mf_query("INSERT IGNORE INTO fhits (threadID, userID) VALUES ($threadID, $adduser[userID])");
	}
	else {
		$co = New contentObj;
		$co->title = "$LANG[ERROR]!";
		$co->contentType="generic";
		$co->primaryContent .= "$LANG[TEAM_ADD_THREAD_ERROR3] <b>$threadID</b> $LANG[TEAM_ADD_THREAD_ERROR4]";
		$co->primaryContent .= "<br/><br/><a href='".make_link("forum","","#threadlist/teams/$teamID")."' class='button'>$LANG[BUTTON_BACK]</a>";

		$shardContentArray[] = $co;
		break;
	}

	header("Location: ".make_link("forum","","#threadlist/teams/".$teamID));
}
break;

case "g_files":
if ($CURRENTUSER != "anonymous") {
	$teamID = "";
	if (isset($_REQUEST['teamID'])) {
	if (is_numeric($_REQUEST['teamID']))
		$teamID = $_REQUEST['teamID'];
	}
	$teamname = team_name($teamID);
	$folderID = "0";
	if (isset($_REQUEST['folderID']))
		$folderID = make_num_safe($_REQUEST['folderID']);
	$threadID = "";
	if (isset($_REQUEST['threadID']))
		$threadID = make_num_safe($_REQUEST['threadID']);
	$link_order = "";
	if (isset($_REQUEST['order']))
		$link_order = $_REQUEST['order'];
	$sens = "DESC";
	if (isset($_REQUEST['sens']))
		if ($_REQUEST['sens'] == "ASC")
			$sens = "ASC";
		

/*	// Clean orphan files
	if (isInGroup($CURRENTUSER, 'admin')) {
		$query_files = mf_query("SELECT fileEncodedName FROM files WHERE fileID NOT IN (SELECT fileID FROM teams_files)");
		while ($row_files = mysql_fetch_assoc($query_files)) {
			if(file_exists("files/" . $row_files['fileEncodedName']))
				unlink("files/" . $row_files['fileEncodedName']);
		}
		mf_query("DELETE FROM files WHERE fileID NOT IN (SELECT fileID FROM teams_files)");
	}*/

	$co = New contentObj;
	$co->contentType="generic";
	$co->primaryContent = "<span style='display:none;' id='teamID'>$teamID</span>
								<span style='display:none;' id='folderID'>$folderID</span>
								<span style='display:none;' id='threadID'>$threadID</span>
								<span style='display:none;' id='link_order'>$link_order</span>
								<span style='display:none;' id='link_sens'>$sens</span>
								<div id='team_file_manager'>";
			
	$co->primaryContent .= file_manager($folderID."@@:f:@@".$teamID."@@:f:@@".$link_order."@@:f:@@".$sens);
	$co->primaryContent .= "</div>
			<div class='displayDiv' id='apply_file_selection'></div>";
			
	$shardContentArray[] = $co;
}
break;

case "g_file_newfolder":
if ($CURRENTUSER != "anonymous" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	$isinteam = isInTeam($teamID,$CURRENTUSERID);
	if (!$isinteam)
		exit($LANG['REFUSED']);
	$folderID = "0";
	if ($_REQUEST['folderID'])
		$folderID = make_num_safe($_REQUEST['folderID']);
	$linksave_amp = "";
	$linksave = "";
	if (isset($_REQUEST['order'])) {
		$linksave_amp = "&amp;order=".$_REQUEST['order']."&amp;sens=".$_REQUEST['sens'];
		$linksave = "&order=".$_REQUEST['order']."&sens=".$_REQUEST['sens'];
	}
	$folderName = make_var_safe($_POST['newfold']);
	$folderDate = time();
	mf_query("INSERT INTO teams_folders (teamID,folderName,subfolderID,folderDate) VALUES ('$teamID', \"$folderName\", '$folderID', '$folderDate')");

	header("Location: ".make_link("teams","&action=g_files&teamID=$teamID&folderID=$folderID$linksave"));
}
break;

case "g_fileUpload":
if ($CURRENTUSER != "anonymous" && is_numeric($_REQUEST['teamID'])) {
	$teamID = $_REQUEST['teamID'];
	$isinteam = isInTeam($teamID,$CURRENTUSERID);
	if (!$isinteam)
		exit($LANG['REFUSED']);

	$folderID = "0";
	if ($_REQUEST['folderID'])
		$folderID = make_num_safe($_REQUEST['folderID']);
	$threadID = "";
	if (isset($_POST['team_thread']))
		if ($_POST['team_thread'])
			$threadID = make_num_safe($_POST['team_thread']);
	$linksave_amp = "";
	$linksave = "";
	if (isset($_REQUEST['order'])) {
		$linksave_amp = "&amp;order=".$_REQUEST['order']."&amp;sens=".$_REQUEST['sens'];
		$linksave = "&order=".$_REQUEST['order']."&sens=".$_REQUEST['sens'];
	}

	$uploadfile = false;
	$msg = "";
	$threadOK = false;
	$co = New contentObj;
	$co->contentType = "generic";

	if (array_key_exists('newfile', $_FILES)) {
		if ($threadID) {
			$getThreadId = mf_query("SELECT category FROM forum_topics WHERE ID = '$threadID' LIMIT 1");
			if ($getThreadId2 = mysql_fetch_assoc($getThreadId)) {
				$threadOK = true;
				$toFolder = "";
				$subfolderID = $folderID;
				while ($subfolderID) {
					$query_folder = mf_query("SELECT subfolderID,folderName FROM teams_folders WHERE teamID = '$teamID' AND folderID = '$subfolderID' LIMIT 1");
					$query_folder = mysql_fetch_assoc($query_folder);
					$toFolder = $query_folder['folderName']."/".$toFolder;
					$subfolderID = $query_folder['subfolderID'];
				}
			}
		}
		for($i=0;$i<sizeof($_FILES['newfile']['name']);$i++) {

			if (filesize($_FILES['newfile']['tmp_name'][$i]) > ($siteSettings['team_maxfilesize'] * 1024))
				$co->primaryContent .="<h2>$LANG[FILE_TOO_LARGE] (" . filesize($_FILES['newfile']['tmp_name'][$i]) . " bytes)</h2><br/><br/>";
			else {
				$file_ext_array = explode("." , $_FILES['newfile']['name'][$i]);
				$file_ext = $file_ext_array[ sizeof($file_ext_array) - 1 ];					
				$fileSize = filesize($_FILES['newfile']['tmp_name'][$i]);
				$fileDate = filemtime($_FILES['newfile']['tmp_name'][$i]);
				$fileEncodedName = SHA1(time().$_FILES['newfile']['name'][$i]);

				if (!move_uploaded_file( $_FILES['newfile']['tmp_name'][$i] , "files/$fileEncodedName" ))
					$co->primaryContent .= "<b>$LANG[UPLOAD_ERROR]</b><br/><br/>";
				else {
					$date_added = time();
					$query_file = mf_query("SELECT files.fileID, files.fileEncodedName FROM files JOIN teams_files ON files.fileID = teams_files.fileID WHERE files.fileName = \"".$_FILES['newfile']['name'][$i]."\" AND teams_files.teamID = '$teamID' AND teams_files.folderID = '$folderID' LIMIT 1");
					$file = mysql_fetch_assoc($query_file);
					if (!$file['fileID']) {
						mf_query("INSERT INTO files (fileName, fileEncodedName, fileExtension, fileSize, fileDate, userID, fileUploadDate, IP) VALUE (\"".$_FILES['newfile']['name'][$i]."\", '$fileEncodedName', \"$file_ext\", '$fileSize', '$fileDate', '$CURRENTUSERID', '$date_added', \"".$_SERVER['REMOTE_ADDR']."\")");
						$uploadfile = true;
						$query_file = mf_query("SELECT fileID FROM files WHERE fileEncodedName = '$fileEncodedName' LIMIT 1");
						$file = mysql_fetch_assoc($query_file);
						mf_query("INSERT INTO teams_files (fileID, teamID, folderID) VALUE ('$file[fileID]', '$teamID', '$folderID')");
					}
					else {
						mf_query("UPDATE files SET fileEncodedName = '$fileEncodedName', fileUploadDate = '$date_added', userID = '$CURRENTUSERID', fileSize = '$fileSize', fileDate = '$fileDate', IP = \"".$_SERVER['REMOTE_ADDR']."\" WHERE fileID = '$file[fileID]' LIMIT 1");
						if(file_exists("files/" . $file['fileEncodedName']))
							unlink("files/" . $file['fileEncodedName']);
					}
					// post system message in team's privatre thread
					if ($threadID && $threadOK) {
						$fileextension = mb_strtolower(substr(strrchr($_FILES['newfile']['name'][$i], '.'),1),'UTF-8');
						$icon = "[css=vertical-align:sub;display:inline;][img]images/core/";
						if (file_exists("images/core/".$fileextension.".png"))
							$icon .= $fileextension;
						else
							$icon .= "unknown";
						$icon .= ".png[/img][/css] ";
						$msg .= "[br][url=".make_link("teams","&action=download&teamID=$teamID&fileID=$file[fileID]")."]".$icon.$toFolder."[b]".$_FILES['newfile']['name'][$i]."[/b][/url]";
					}
				}
			}
		}
		if ($threadID && $threadOK) {
			$msg = $CURRENTUSER." $LANG[HAS_UPLOADED]:".$msg;
			$inTime = time();
			$getSystemId = mf_query("SELECT username FROM users WHERE ID='1' LIMIT 1");
			if ($getSystemId2 = mysql_fetch_assoc($getSystemId)) {
				$result = mf_query("INSERT INTO forum_posts
									(body, user, userID, date, threadID, rating)
									VALUES
									(\"$msg\", \"$getSystemId2[username]\", '1', $inTime, $threadID, 0)");

				$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID='1' AND date='$inTime' ORDER BY ID LIMIT 0,1");
				$lastPost = mysql_fetch_assoc($lastPost);
				$updateComments = mf_query("UPDATE forum_topics SET last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 WHERE ID='$threadID'");
				$updateChannelTag = mf_query("UPDATE categories SET num_posts = num_posts + 1 WHERE ID=$getThreadId2[category]");
			}
		}
		header("Location: ".make_link("teams","&action=g_files&teamID=$teamID&folderID=$folderID$linksave"));
		exit();
	}
	else
		$co->primaryContent .= $LANG['CANT_OPEN_FILE'];

	$co->primaryContent .= "<a href='".make_link("teams","&amp;action=g_files&amp;teamID=$teamID&amp;folderID=$folderID$linksave_amp")."' class='button'>$LANG[BUTTON_BACK]</a>";
	$shardContentArray[] = $co;
}
break;

case "download":
if (is_numeric($_REQUEST['teamID']) && is_numeric($_REQUEST['fileID'])) {
	$teamID = $_REQUEST['teamID'];
	$query_file = mf_query("SELECT files.* FROM files JOIN teams_files ON files.fileID = teams_files.fileID WHERE files.fileID = '$_REQUEST[fileID]' AND teams_files.teamID = '$teamID' LIMIT 1");
	$file = mysql_fetch_assoc($query_file);
	if ($file['publicfile'])
		$isinteam = true;
	else
		$isinteam = isInTeam($teamID,$CURRENTUSERID);

	if ($isinteam && $file['fileID']) {
		if (file_exists("files/$file[fileEncodedName]")) {
			mf_query("UPDATE files SET downloads = downloads + 1 WHERE fileID = '$file[fileID]' LIMIT 1");
			$length = filesize("files/$file[fileEncodedName]");
			header("Content-Type: application/force-download; name=\"$file[fileName]\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: $length");
			header("Content-Disposition: attachment; filename=\"$file[fileName]\"");
			header("Expires: 0");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			readfile("files/$file[fileEncodedName]");
		}
		else exit($LANG['DOWNLOAD_NO_FILE'].$file['fileName']);
	}
	else exit($LANG['REFUSED']);

	die();
	
}
break;


endswitch;
?>