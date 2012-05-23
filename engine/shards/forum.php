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

// forum.php
    
require("user_profilelib.php");
require("forumlib.php");

foreach($_REQUEST as $keyVar => $value)
	$_REQUEST[$keyVar] = xss_clean($_REQUEST[$keyVar]);		


global $siteSettings;
$siteSettings['showReplyFormDefault'] = true;

//////////////////////////////////////////////////////
//--------------------------------------------------//
// Determine forum action request
//--------------------------------------------------//
//////////////////////////////////////////////////////
switch ($action):


case "doOnce":

break;

//---------------------------------------------------
// g_default -- default display of forum
//---------------------------------------------------
case "g_default": {

$thisContentObj = New contentObj;
if ($CURRENTSTATUS == "pending") {
	$thisContentObj->primaryContent = "<br/><hr/><br/><center><h2>$LANG[MAIL_ACTIVATION17]</h2><hr/>";
	$thisContentObj->primaryContent .= "$LANG[MAIL_ACTIVATION18] <a href='".make_link("adduser","&action=g_resendauthent")."'>$LANG[MAIL_ACTIVATION19]</a>.";
	$shardContentArray[] = $thisContentObj;
	break;
}

// log user IP
if ($CURRENTUSER != "anonymous" && $CURRENTUSER != "bot") {
	if (!isInGroup($CURRENTUSER, 'log_ip') && (($verifyEditDelete) || (isInGroup($CURRENTUSER, 'modo')) || (isInGroup($CURRENTUSER, 'level1')) || (isInGroup($CURRENTUSER, 'level7'))))
		$ip = "";
	else
		$ip=$_SERVER["REMOTE_ADDR"];
	$update = mf_query("update users set lat=".time().", laid=0, ip='$ip' where ID=$CURRENTUSERID limit 1");
}
// lock anonymous access depending on server load
if ($CURRENTUSER == "anonymous" && $siteSettings['loadavg'] > 0) {
	if (file_exists("/proc/loadavg")) {
		if (@$loadavg = trim(file_get_contents('/proc/loadavg'))) {
			$loads = explode(" ",$loadavg);
			$load = trim($loads[0]);
		}
	}
	else
		$load = "0";

	if ($load > ($siteSettings['loadavg']/.5)) {
		$thisContentObj->primaryContent = "<br/><hr/><br/><center>
											<h2>$LANG[LOADAVG]</h2>
											<h2>$LANG[LOADAVG1]</h2>
											</center><br/><hr/><br/>";
		$shardContentArray[] = $thisContentObj;
		break;
	}
} 


if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo') || isInGroup($CURRENTUSER, 'level1') || isInGroup($CURRENTUSER, 'level7')) {
	if (!$CURRENTUSERRULES) // automaticaly set tos as accepted
		$rules = mf_query("UPDATE users SET rules = '1' WHERE ID = '$CURRENTUSERID' limit 1");
}
else {
	// Block users not from admin and some leveled group to access the forum when loadavg too high (set to 0 in setting to disable it)
	if ($siteSettings['loadavg'] > 0 && $CURRENTUSER != "anonymous" && $CURRENTUSER != "bot") {
		if (file_exists("/proc/loadavg")) {	
			$loadavg = trim(file_get_contents('/proc/loadavg'));
			$loads = explode(" ",$loadavg);
			$load = trim($loads[0]);
		}
		else
			$load = "0";

		if ($load > $siteSettings['loadavg'] / .5) {
			$thisContentObj->primaryContent = "<br/><br/><hr/><br/><center><h1>$LANG[LOADAVG]</h1>";
			$thisContentObj->primaryContent .= "<h3>$LANG[LOADAVG2]</h3></center><br/><hr/><br/>";
			$shardContentArray[] = $thisContentObj;
			break;
		}
	}
	if ($siteSettings['rules'] && !$CURRENTUSERRULES && $CURRENTSTATUS != "banned" && $CURRENTUSER != "anonymous") {
			$thisContentObj->contentType = "generic";
		$thisContentObj->primaryContent = "<div style='border-top:1px solid black;border-bottom:1px solid black;padding-top:16px;margin-top:16px;padding-bottom:16px;margin-bottom:16px;font-size:2em;text-align:center;'>$LANG[RULES_TITLE1] $siteSettings[titlebase]$LANG[RULES_TITLE2]</div>";
		$thisContentObj->primaryContent .= display_tos();
		$thisContentObj->primaryContent .= "<form name='rulesaccept' action='index.php?shard=forum&amp;action=g_rules' method='post'>";
			$thisContentObj->primaryContent .= "<div style='text-align:center;margin-top:16px;'>
			<div><input class='bselect' type='checkbox' name='acceptrules' style='margin-right:4px;'/><span style='font-size:1.1em;font-weight:bold;'>$LANG[RULES_TEXT1]</span></div>
			<div style='margin-top:5px;'><input name='rulessubmit' type='submit' value=\"$LANG[SUBMIT]\" class='button' /></div>
			<div style='font-size:0.9em;'>($LANG[RULES_TEXT2])</div>
			<div style='font-size:0.9em;'>($LANG[RULES_TEXT3] \"$LANG[FORUM_PROFILE]\" $LANG[RULES_TEXT4] \"$LANG[FORUM_SETTINGS]\".)</div>
			</div>";
		
		$shardContentArray[] = $thisContentObj;
		break;
	}
}

if (!array_key_exists('metaChannelFilter2', $_COOKIE) && !$CURRENTUSERFLOOD) {
	if ($siteSettings['flood_ID'])
		setcookie("metaChannelFilter2", $siteSettings['flood_ID'], time()+31536000);
}
else if (!$siteSettings['flood_ID'])
	setcookie("metaChannelFilter2", "", -1);

$filter = "";
if (array_key_exists('threadFilter', $_COOKIE))	{
	$filter = $_COOKIE["threadFilter"];
	if ($filter == "undefined")
		$filter = "sel";
}

$teamID = "";
if (array_key_exists('teamID', $_REQUEST))
	$teamID = make_var_safe($_REQUEST['teamID']);

if (!$CURRENTUSERAJAX) {
	$page = "";
	if (isset($_REQUEST['page']))
		$page = make_num_safe($_REQUEST['page']);
	$ThreadListArray = explode("::arrdlm::",ajax_resetThreadList(":@@:$teamID:@@:$filter:@@:$page:@@:"));
	$tempForumContentHolder = "<div id='parentC'>".$ThreadListArray[0]."</div>";
}
else
	$tempForumContentHolder = "<div id='parentC'></div>";

$thisContentObj = New contentObj;

if ($CURRENTUSERID && $CURRENTUSERAJAX)
	$siteSettings['bodyOnload'] = " onload=\"checkhash();\"";
else if ($CURRENTUSERID)
	$siteSettings['bodyOnload'] = " onload=\"runOnce($CURRENTUSERID);\"";
else
	$siteSettings['bodyOnload'] = " onload=\"checkhash_anonymous();\"";
//	else
//		$siteSettings['bodyOnload'] = " onload=\"checkhash();\"";

$channelMaintain = "";
if (array_key_exists('channel', $_REQUEST)) {
	$chan = make_num_safe( $_REQUEST['channel']);
	$channelMaintain = "&amp;channel=$chan";
}

$pageString = "";
$thisContentObj->primaryContent = "<span id='timeAgo_fav' class='".time()."' style='display:none;'></span><span id='timeAgo_pt' class='".time()."' style='display:none;'></span>
								<div id='newThreadFormPlaceholder' class='submenuPlaceholder' style='margin-bottom:6px;'></div>";
if (!$CURRENTUSERAJAX) {
	$pageString = $ThreadListArray[1];
	$thisContentObj->primaryContent .= "<div id='threadlist'>".drawForumSubMenu($ThreadListArray[2], 0, $ThreadListArray[3], $filter);
}
else
	$thisContentObj->primaryContent .= "<div id='threadlist' style='display:none;'>".drawForumSubMenu("", 0, "", $filter);

$user = "";
if ($CURRENTUSER != "anonymous" && $CURRENTUSER != "bot" && $CURRENTSTATUS != "banned") {
	if (array_key_exists('user', $_REQUEST)) {
		$searchuser = htmlspecialchars($_REQUEST['user']);
		$user = make_var_safe($searchuser);
	}
	$searchtype = "";
	if (array_key_exists('search', $_REQUEST)) {
		$searchtype = "posts";
	}

	$radthread = "";
	$radpost = "";
	if ($searchtype == "posts")	{
		$radpost = " checked='checked'";
		$sp = "1";
	}
	else {
		$searchtype = "threads";
		$radthread = " checked='checked'";
		$st = "1";
	}

	$exprtype_exact = "";
	$exprtype_all = "";
	$exprtype_one = "";
	if (!isset($exprtype))
		$exprtype_exact = "checked='checked'";
	else if ($exprtype == "exact" || !$exprtype)
		$exprtype_exact = "checked='checked'";
	else if ($exprtype == "all")
		$exprtype_all = "checked='checked'";
	else if ($exprtype == "one")
		$exprtype_one = "checked='checked'";

	$searchdate = date($LANG['DATE_LINE_MINIMAL2'], time() + 86400);

	$thisContentObj->primaryContent .= "<div id='searchForm' class='submenuPlaceholder' style='border:1px solid silver;display:none;'>";
	$searchform = "<form  id='search' name='search' action=\"javascript:submitsearch();\" method='post'>
		<div style='text-align:center;'>
		<div>
		<input type='text' name='searchterm' id='searchterm' size='34' value='' />
		<input type='submit' value=\"$LANG[SEARCH_BUTTON]\" class='button' />
		</div>";
	$searchform .= "<div><input type='radio' class='bselect' id='searchtype_threads' name='searchtype' value='threads'". $radthread ." /> 
		$LANG[SEARCH_THREAD_TITLE] &nbsp;";
	$searchform .= "<input type='radio' class='bselect' id='searchtype_posts' name='searchtype' value='posts'". $radpost ." /> 
		$LANG[SEARCH_POST_BODY]</div>";
	$searchform .= "<div><input type='radio' class='bselect' id='expression_exact' name='exprtype' value='exact'". $exprtype_exact ." /> 
		$LANG[SEARCH_EXPR_EXACT] &nbsp;";
	$searchform .= "<input type='radio' class='bselect' id='expression_all' name='exprtype' value='all'". $exprtype_all ." /> 
		$LANG[SEARCH_EXPR_ALL] &nbsp;";
	$searchform .= "<input type='radio' class='bselect' id='expression_one' name='exprtype' value='one'". $exprtype_one ." /> 
		$LANG[SEARCH_EXPR_ONE]</div>";
	$searchform .= "<div>";
	$searchform .= "<table cellspacing='6' cellpadding='0' width='100%'><tr>";
	$searchform .= "<td><small>$LANG[SEARCH_FILTER_USER]</small> <input type='text' class='bselect' name='user' size='14' value=\"$user\" /></td>";
	$searchform .= "<td><small>$LANG[SEARCH_FILTER_THREAD]</small> <input type='text' class='bselect' name='thread' size='8' value='' /></td>";
	$searchform .= "<td align='right'><small>$LANG[SEARCH_DATE]</small> <input type='text' class='bselect' name='searchdate' size='10' value='$searchdate' />";
	$searchform .= "</td></tr></table></div></div></form>";
	$thisContentObj->primaryContent .= "<span id='chan_cache_search' style='display:none;'></span>
										<span id='channelsAnchor_cache' style='display:none;'></span>
										<span id='channelsWindow_cache' style='display:none;'></span>
										$searchform</div>";
}
else {
	$thisContentObj->primaryContent .= "<div id='searchForm' class='submenuPlaceholder' style='border:1px solid silver;display:none;'>";
	$thisContentObj->primaryContent .= "<span id='chan_cache_search' style='display:none;'></span>
										<span id='channelsAnchor_cache' style='display:none;'></span>
										<span id='channelsWindow_cache' style='display:none;'></span>
										</div>";
}
// Page list Top
$thisContentObj->primaryContent .= "<div id='pagesListPaneT'><div id='pagesListStrT'>" . $pageString . "</div></div><div style='clear:both;'></div>";

// Tag list
if ($CURRENTUSER != "anonymous" && (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus")) {
	$thisContentObj->primaryContent .= "
	<div style='margin-left:4px;display:none;border-top:1px dashed silver;padding-top:3px;padding-bottom:3px;' id='tags_display'>
		<div class='row'>
			<div style='font-size:0.8em;vertical-align:top;min-width:97px;text-align:right;padding-right:4px;padding-top:1px;font-weight:bold;' class='cell'>$LANG[TAG_SELECTED]:</div>
			<div style='width:100%;vertical-align:bottom;' class='cell'>
				<div id='tags_list' style='display:inline-block;'></div>
				<img border='0' style='margin-top:-4px;vertical-align:text-top;cursor:pointer;' alt='X' src='engine/grafts/" . $siteSettings['graft'] . "/images/reset.png' onclick=\"remove_alltags();\"/>
			</div>
			<div class='cell'></div>
		</div>
	</div>
	<div style='max-height:32px;overflow:hidden;' id='treag_tagList' onmouseover=\"show_threadsTags();\" onmouseout=\"hide_threadsTags();\">
	<div style='margin-left:4px;display:inline-table;border-top:1px dashed silver;'>
		<div class='row'>
			<div style='font-size:0.8em;vertical-align:top;min-width:97px;text-align:right;padding-right:4px;font-weight:bold;' class='cell'><div style='padding-right:6px;'>$LANG[TAG_PRESENT1]</div><div>$LANG[TAG_PRESENT2]:</div></div>
			<div class='cell' style='width:100%;vertical-align:bottom;' id='tags_presents'>";
		if (!$CURRENTUSERAJAX)
			$thisContentObj->primaryContent .= $ThreadListArray[7];
		$thisContentObj->primaryContent .="</div>
			<div class='cell' style='padding-right:4px;padding-top:2px;'>
				<div onclick=\"show_tags();\" class='button' style='display:inline-block;min-width:78px;'>$LANG[TAG_DISPLAY]</div>
			</div>
		</div>
	</div>
	</div>";
}
else if ($CURRENTUSER == "anonymous" && !$siteSettings['hide_filters'] && (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus"))
	$thisContentObj->primaryContent .= "
	<div style='height:26px;' id='treag_tagList'>
	<div style='margin-left:4px;display:inline-table;border-top:1px dashed silver;'>
		<div class='row'>
			<div style='font-size:0.8em;vertical-align:top;min-width:97px;text-align:right;padding-right:4px;font-weight:bold;' class='cell'></div>
			<div class='cell' style='width:100%;vertical-align:bottom;' id='tags_presents'></div>
			<div class='cell' style='padding-right:4px;padding-top:2px;'>
				<div class='button' style='cursor:not-allowed;display:inline-block;min-width:78px;'>$LANG[TAG_DISPLAY]</div>
			</div>
		</div>
	</div>
	</div>";

$thisContentObj->primaryContent .= "<div class='threadInfoWrapper' id='threadInfoWrapper'><div class='threadColumnInfo'><table width='100%' class='threadTable'><tr>
									<td width='36px'> </td>
									<td><table border='0' width='100%'><tr>
										<td width='170px' class='threadInfoTDsmall'>$LANG[TITLE]</td>
										<td width='100px' class='threadInfoTDsmall'>$LANG[CHANNEL]</td>
										<td width='70px' class='threadInfoTDsmall'>$LANG[POSTS]</td>
										<td width='50px' class='threadInfoTDsmall'>$LANG[VIEWS]</td>
										<td width='176px' class='threadInfoTDsmall'>$LANG[LAST_POST]</td>
									</tr></table></td>
									</tr></table></div>";

$thisContentObj->primaryContent .= $tempForumContentHolder;

$thisContentObj->primaryContent .= "</div><div id='timestamp' class='" . time() . "'></div>";

// Page list Bottom
$thisContentObj->primaryContent .= "<div id='pagesListPane'><div id='pagesListStr'>" . $pageString . "</div></div></div>";

//------------------------------------------------------------------------------
// Add this contentObject to the shardContentArray
//------------------------------------------------------------------------------

if ($user)
	$siteSettings['bodyOnload'] = "onload=\"searchUser('$user','$st','$sp','1')\"";

$thisContentObj->primaryContent .= "
									<div id='thread' style='display:none;'></div>
									<div id='user_profile' style='display:none;'></div>
								</div>";
$shardContentArray[] = $thisContentObj;

}
break;

case "calculatePageLocationForFirstNew": {

	$tid = "";
	if (array_key_exists('ID' , $_REQUEST ))
	$tid = make_num_safe( $_REQUEST['ID']);
	$postid = "";
	if (array_key_exists('postID' , $_REQUEST ))
		$postid = make_num_safe( $_REQUEST['postID']);
	
	if (array_key_exists('sl' , $_REQUEST ) == TRUE ) {
		$sl = make_num_safe( $_REQUEST['sl']);
		
		if ($postid) {
			$postList = mf_query("select ID, threadID, date from forum_posts where ID=$postid order by ID asc");
			$row = mysql_fetch_assoc($postList);
			$tid = $row['threadID'];
		}
		
		$posttype = "AND posttype < 3";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$posttype = "";

		$postList = mf_query("select ID, date from forum_posts where threadID=$tid $posttype order by ID asc");
		$postList2 = $postList;

		$counter = 1;
		$totalPosts = 1;
		while ($row = mysql_fetch_assoc($postList)) {
			if ($row['date'] <= $sl)
				$counter++;

			$totalPosts++;
		}

		global $CURRENTUSERPPP;
		if (!is_numeric($CURRENTUSERPPP))
			$CURRENTUSERPPP = 60;
			
		$pageLocation = ceil($counter / $CURRENTUSERPPP);
		$totalPages = ceil($totalPosts / $CURRENTUSERPPP);
		
		$isLive="";
		if ($pageLocation == $totalPages)
			$isLive="&isLive=true";
			
		$postID =  mf_query("select ID from forum_posts where threadID=$tid and date >= '$sl' order by date asc limit 1");
		$postID = mysql_fetch_assoc($postID);

		$hStr = "Location: ".make_link("forum","&action=g_reply&ID=$tid&page=$pageLocation$isLive#$postID[ID]","#thread/$tid/$pageLocation/$postID[ID]");

		header($hStr);
	}    	

    }
break;

//-------------------------------------------------------------------------------------
// g_reply - draw post with QuickQuote, show reply box
//-------------------------------------------------------------------------------------
case "g_reply": {

// Account not activated
if ($CURRENTSTATUS == "pending") {
	$thisContentObj = New contentObj;
	$thisContentObj->primaryContent .= "<br/><hr/><br/><center><h2>$LANG[MAIL_ACTIVATION17]</h2><hr/>";
	$shardContentArray[] = $thisContentObj;
	break;
}
// Anonymous user and Loadavg
if ($CURRENTUSER == "anonymous" and $siteSettings['loadavg'] > 0) {
	if (file_exists("/proc/loadavg")) {
		$loadavg = trim(file_get_contents('/proc/loadavg'));
		$loads = explode(" ",$loadavg);
		$load = trim($loads[0]);
	}
	else
		$load = "0";

	if ($load > ($siteSettings['loadavg']/2)) {
		$thisContentObj = New contentObj;
		$thisContentObj->primaryContent .= "<br/><hr/><br/><center><h2>$LANG[BLOCK_USER_TITLE]</h2>";
		$thisContentObj->primaryContent .= "<h2>$LANG[BLOCK_ANONYMOUS_TITLE]</h2></center><br/><hr/><br/>";
		$shardContentArray[] = $thisContentObj;
		break;
	}
} 

$tid = make_num_safe( $_REQUEST['ID']);
$numcom = "num_comments";
if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
	$numcom = "num_comments_T";

$query= mf_query("SELECT ID,$numcom FROM forum_topics WHERE ID=$tid limit 1");
$isliveV = mysql_fetch_assoc($query);

$islive = "";
if (array_key_exists( "isLive", $_REQUEST ))
	$islive = "1";
$sl = "";
if (array_key_exists('sl' , $_REQUEST ))
	$sl = make_num_safe($_REQUEST['sl']);
$page = "";
if (array_key_exists('page', $_REQUEST))
	$page = make_num_safe($_REQUEST['page']);
if (!$islive && !$sl && !$page) {
	if ($CURRENTUSERPPP > $isliveV[$numcom])
		$islive = "1";
}
$thisContentObj = New contentObj;

if ($isliveV['ID'] == $tid) {
	$dataLine = $tid.":!@:".$sl.":!@:".$page.":!@:".$islive.":!@:";
	$dataLine = ajax_g_reply($dataLine);
	$dataLine = explode("::cur@lo::", $dataLine);

	$islive = $dataLine[3];
	if ($islive && $CURRENTUSER != 'anonymous' && $CURRENTUSER != "bot")
		$siteSettings['bodyOnload'] = " onload=\"runThreadWatcherOnce($tid)\"";

	$thisContentObj->primaryContent .= "
		<div id='threadlist' style='display:none;'><span id='filter' style='display:none;'></span></div>
		<div id='thread'>".$dataLine[0]."</div>
		<div id='user_profile' style='display:none;'></div>";
}
else
	$thisContentObj->primaryContent .= "<h2>$LANG[THREAD_NOT_EXIST]</h2>";

$shardContentArray[] = $thisContentObj;

	} break;

//-------------------------------------------------------------------------------------
// proc_reply - process a reply to a forum post
//-------------------------------------------------------------------------------------
case "proc_reply": {

	if ($CURRENTUSER != 'anonymous' and $CURRENTSTATUS != "banned" and $CURRENTUSER != "bot" and ($CURRENTUSERRULES == "1" or !$siteSettings['rules'])) {
		if( is_numeric( $_REQUEST['ID']) && is_numeric( $_REQUEST['channelTag'])) {
			$_REQUEST['message'] = preformat_body($_REQUEST['message']);

			submitPostToDB($_REQUEST['message'], $_REQUEST['ID'], $_REQUEST['channelTag']);

			// determine last page to send them to
			$numcom = "num_comments";
			if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
				$numcom = "num_comments_T";

			$lastPage = mf_query("select $numcom from forum_topics where ID=$_REQUEST[ID]");
			$lastPage = mysql_fetch_assoc($lastPage);
			$lastPage = $lastPage[$numcom];
			$lastPage = ceil($lastPage / $CURRENTUSERPPP);

			header("Location: ".make_link("forum","&action=g_reply&ID=$_REQUEST[ID]&page=$lastPage&isLive=true#bottom","#thread/$_REQUEST[ID]/$lastPage/bottom"));
		}
	}
	else
		header("Location: ".make_link("forum"));
	}
break;

//-------------------------------------------------------------------------------------
// g_crt_new - create a new forum entry (show the form)
//-------------------------------------------------------------------------------------   
case "g_crt_new": {
 
	$thisContentObj = New contentObj;   
	$thisContentObj->contentType = "generic";
	$thisContentObj->primaryContent = ajax_callNewThreadForm();

	$shardContentArray[] = $thisContentObj;
	}
break;
    
//-------------------------------------------------------------------------------------
// proc_new - process a new forum post (insert the forum)
//-------------------------------------------------------------------------------------
case "proc_new": {

if ($CURRENTUSER != 'anonymous' and $CURRENTSTATUS != "banned" and $CURRENTUSER != "bot" and ($CURRENTUSERRULES == "1" or !$siteSettings['rules'])) {
	global $LANG;
	
	$tid = make_num_safe( $_REQUEST['channelTag']);

	$body = preformat_body($_REQUEST['message']);

	if( get_magic_quotes_gpc() == 1 ) {
		$cleantitle = htmlspecialchars($_REQUEST['title']);
		$_REQUEST['toList'] = htmlspecialchars($_REQUEST['toList']);
	}
	else {
		$cleantitle = make_var_safe( htmlspecialchars( $_REQUEST['title']));
		$_REQUEST['toList'] = make_var_safe( htmlspecialchars($_REQUEST['toList']) );
	}

	$teamID = "";
	if (isset($_POST['teamID']))
		$teamID = make_var_safe($_POST['teamID']);

	$spoiler = 0;
	if (isset($_POST['spoiler']))
		$spoiler = 1;

	

	if (($body != "") && ($cleantitle != "")) {
		$inTime = time();

		$type = 2;
		if (isset($_POST['sticky']) && ($verifyEditDelete || $teamID))
			$type = 1;

		if ($type == 1)
			$stickytime = time();

		$b = 0;
		$unvisible = 0;
		$pthread = 0;

		if (isset($_POST['pThread']) || $teamID > 0)
			$pthread = 1;
		else {
			if ($verifyBlogger && isset($_POST['blog'])) {
				if (isset($_POST['blog_p']))
					$b = 2;
				else
					$b = 1;
				$unvisible = 0;
				if (!isset($_POST['blog_f']) && $pthread == 0)
					$unvisible = 1;
			}

			if (!$verifyBlogger && isset($_POST['blog'])) {
					$b = 2;
				$unvisible = 0;
					if (!isset($_POST['blog_f']) && $pthread == 0) {
					$unvisible = 1;
				}
				else
					$b = 2;
			}
		}
			
			$creator_l = "0";
			if (isset($_POST['creator_locked']))
				$creator_l = "1";

		$stickytime = "";
		if (isset($_POST['sticky']))
			$stickytime = time();

		$poll = 0;
		if (isset($_POST['poll'])) {
			if ($_REQUEST['pollQuestion'] != "" && $_REQUEST['pollOption1'] != "" && $_REQUEST['pollOption2'] != "") {
				if (is_numeric($_REQUEST['pollDays'])) {
					$pollQuestion = make_var_safe(xss_clean($_REQUEST['pollQuestion']));

					if ($_REQUEST['pollDays'] > 0)
						$_REQUEST['pollDays'] = time() + $_REQUEST['pollDays'] * 86400;
					else
						$_REQUEST['pollDays'] = 0;

					$result = mf_query("insert into poll_topics (question, end_date) VALUES ('$pollQuestion', $_REQUEST[pollDays])");
					$findID = mf_query("select ID from poll_topics where question='$pollQuestion' order by ID DESC LIMIT 1");
					$findID = mysql_fetch_assoc($findID);
					$poll = $findID['ID'];

					$c = 1;

					while ($_REQUEST['pollOption'.$c] != "") {
						$pollOption = make_var_safe(xss_clean($_REQUEST['pollOption'.$c]));							
						
						$result = mf_query("insert into poll_answers (answer, poll_ID) VALUES ('$pollOption', $findID[ID])");
						$c++;
					}
				}
			}
		}


		$result = mf_query("insert into forum_topics
				(title, user, userID, date, num_comments, num_comments_T, num_views, threadtype, stickytime, category, rating, pthread, poll, blog, spoiler, unvisible, teamID, creator_locked)
				VALUES
				(\"$cleantitle\", \"$CURRENTUSER\", $CURRENTUSERID, $inTime, 0, 0, 0, $type, '$stickytime', $tid, 0, $pthread, $poll, $b, $spoiler, $unvisible, '$teamID', '$creator_l')");

		$increaseChannel = mf_query("update categories set num_threads = num_threads + 1 where ID=$tid");
		mf_query("UPDATE forum_user_nri SET num_threads = num_threads + 1 WHERE userID = '$CURRENTUSERID' limit 1");

		$autoID = mf_query("select ID from forum_topics where userID=$CURRENTUSERID and date='$inTime'");
		$autoID = mysql_fetch_assoc($autoID);
		
		$tags_array = explode(",",make_var_safe($_POST['tags']));
		$i = 0;
			while (isset($tags_array[$i])) {
			$tag = mb_strtolower(trim($tags_array[$i]),'UTF-8');
			if ($tag) {
				mf_query("INSERT IGNORE into forum_tags (tag, threadID) values (\"$tag\",'$autoID[ID]')");
			}
			$i++;
		}
		count_tags();
		load_tags();

		$erroraddusers = "";
		if (isset($_POST['pThread'])) {
			if (!stristr($_REQUEST['toList'], $CURRENTUSER))
			$_REQUEST['toList'] .= ",".$CURRENTUSER;				
				
			$toListArray = explode(",", str_replace(", ", ",", $_REQUEST['toList']));
			
			foreach ($toListArray as $listItem) {
				$listItem = make_var_safe(xss_clean($listItem));
				$userID = mf_query("SELECT ID, username, pm_alert, email, accept_pm_from FROM users WHERE LOWER(username)=\"".mb_strtolower($listItem,'UTF-8')."\" AND userstatus IS NULL LIMIT 1");
				if ($userID = mysql_fetch_assoc($userID)) {
					if (verify_add_to_pm($CURRENTUSERID,$userID['ID'],$userID['accept_pm_from'])) {
						$result = mf_query("INSERT IGNORE INTO fhits (userID, threadID, date, addedDate) VALUES ('$userID[ID]', '$autoID[ID]', 0, ".time().")");
						if ($userID['pm_alert'] && $userID['email'] && ($CURRENTUSERID != $userID['ID'])) {
							srand((double)microtime()*1000000);
							$boundary = md5(uniqid(rand()));
							$header ="From: $siteSettings[titlebase] <$siteSettings[alert_mail]>\n";
							$header .="Reply-To: $siteSettings[alert_mail] \n";
							$header .="MIME-Version: 1.0\n";
							$header .="Content-Type: multipart/alternative;boundary=$boundary\n";

							$to = $userID['email'];
							$subject = "$LANG[PT_ALERT1]: " . $cleantitle;

							$message = "\nThis is a multi-part message in MIME format.";
								$message .="\n--" . $boundary . "\nContent-Type: text/html;charset=\"utf-8\"\n\n";
							$message .= "<html><body>\n";
							$message .="<img src='" . $siteSettings['siteurl'] . "/engine/grafts/" . $siteSettings['graft'] . "/images/MailheaderImage.png'><br><br>\n";
							$message .= "\n$CURRENTUSER $LANG[PT_ALERT4]<br/>\n";
							$message .= "\n$LANG[PT_ALERT3]:<br/>\n";
							$message .= "http://".$siteSettings['siteurl']."/".make_link("forum","&action=g_reply&ID=$autoID[ID]","#thread/$autoID[ID]/1")."<br><br>\n";
							$message .= "$LANG[DO_NOT_ANSWER]\n";
							$message .="\n--" . $boundary . "--\n end of the multi-part";

							mail($to, $subject, $message, $header) or die($LANG['COULD_NOT_SEND_MAIL']);
						}
					}
					else {
						$erroraddusers .= $userID['ID'].".".$userID['accept_pm_from']."UU";
					}
				}
				else {
					$erroraddusers .= urlencode($listItem).".4UU";
				}
			}
		}			

		submitPostToDB($body, $autoID['ID'], $tid, $CURRENTUSERID,true);

		// Set introduce thread

		$cat_intro_ID = mf_query("select introduce_ID from settings limit 1");
		$cat_intro_ID = mysql_fetch_assoc($cat_intro_ID);
		$cat_intro_ID = $cat_intro_ID['introduce_ID'];

		if ($tid == $cat_intro_ID) {
				mf_query("update users set introducethread = '$autoID[ID]' where ID = '$CURRENTUSERID' limit 1");
		}

		if ($b == 1)
			make_rss("");
		else if ($b == 2)
			make_rss($CURRENTUSERID);

		if ($_REQUEST['shardname'] == "blog" and $b == 1)
			header("Location: ".make_link("blog"));
		else if ($_REQUEST['shardname'] == "blog" and $b == 2)
			header("Location: ".make_link("blog","&userID=$CURRENTUSERID"));
		else if ($erroraddusers)
			header("Location: ".make_link("forum","&action=g_error_add&ID=$autoID[ID]&error=".$erroraddusers));
		else if ($teamID)
			header("Location: ".make_link("forum","","#threadlist/teams/$teamID"));
		else
			header("Location: ".make_link("forum","&action=g_reply&ID=$autoID[ID]&page=1&isLive=true","#thread/$autoID[ID]/1"));
		exit();
	}
	else
		header("Location: ".make_link("forum"));
}
else
	exit();    

}
break;

case "g_error_add": {
	$threadID = make_num_safe($_REQUEST['ID']);
	$listuser = explode("UU",$_REQUEST['error']);
	$retstr = "";
	foreach($listuser as $user) {
		if ($user) {
			$userarray = explode(".",$user);
			if ($userarray[0]) {
				if ($userarray[1] != "4") {
					$username = mf_query("SELECT username FROM users WHERE ID = '".make_num_safe($userarray[0])."' LIMIT 1");
					$username = mysql_fetch_assoc($username);
				}
				$retstr .= "<div style='margin-bottom:4px;'><span style='font-weight:bold;'>";
				if ($userarray[1] == "1")
					$retstr .= "$username[username]</span> ".$LANG['PM_ONLY_FROM_FRIENDS_OF'];
				else if ($userarray[1] == "2")
					$retstr .= "$username[username]</span> ".$LANG['PM_ONLY_FROM_FRIENDS'];
				else if ($userarray[1] == "4")
					$retstr .= urldecode($userarray[0])."</span>".$LANG['USERNOTFOUND'];
				$retstr .= "</div>";
			}
		}
	}

	$thisContentObj = New contentObj;        
	$thisContentObj->contentType="generic";
	$thisContentObj->title = "$LANG[PM_ONLY_ERROR_TITLE]";

	$thisContentObj->primaryContent = "<div style='height:16px;'></div>".$retstr;
	$thisContentObj->primaryContent .= "<div style='margin-top:16px;margin-bottom:32px;'><a href='".make_link("forum","&action=g_reply&ID=$threadID&page=1&isLive=true","#thread/$threadID/1")."' class='button'>$LANG[PM_ONLY_ERROR_THREAD]</a></div>";
	
	$shardContentArray[] = $thisContentObj;
	
}
break;

//-------------------------------------------------------------------------------------
// g_edit - edit the text of an already-created forum entry (show the form)
//-------------------------------------------------------------------------------------
case "g_editThread": {

$id = make_num_safe( $_REQUEST['ID']);

$blog = "";
if (array_key_exists('blog', $_REQUEST)) {
	if ($_REQUEST['blog'] == "1")
		$blog = "1";
	else if ($_REQUEST['blog'] == "2")
		$blog = "2";
}

$numcom = "num_comments";
if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
	$numcom = "num_comments_T";

 
$postData = mf_query("SELECT * FROM forum_topics WHERE ID='$id' LIMIT 1");
$postData = mysql_fetch_assoc($postData);

$level9 = false;
$teamLevel = "0";
if ($postData['teamID'])
	$teamLevel = isInTeam($postData['teamID'],$CURRENTUSERID);

$thisContentObj = New contentObj;        
$thisContentObj->contentType="generic";
$thisContentObj->title = "$LANG[EDIT_THREAD_TITLE]";

    $allowedit = true;
	if ($CURRENTSTATUS == "banned")
		$allowedit = false;
	else if ($postData['threadtype'] == 3)
		$allowedit = false;
	else if ($postData['locked'] && !$verifyEditDelete && !isInGroup($CURRENTUSER, 'modo') && $teamLevel != "1")
		$allowedit = false;
	else if ($CURRENTUSER != $postData['user'] && !$verifyEditDelete && !isInGroup($CURRENTUSER, 'modo') && !$level9 && $teamLevel != "1")
		$allowedit = false;
	else if ($postData['pthread'] == 1) {
		$allowedit = false;
		$verifypthread = mf_query("SELECT date FROM fhits WHERE userID='$CURRENTUSERID' AND threadID = '$id' LIMIT 1");
		if ($verifypthread = mysql_fetch_assoc($verifypthread))
			$allowedit = true;
	}
	
	if ($allowedit) {
		$pc = "";
	if (($verifyEditDelete) || ($postData[$numcom] < 10) || ($postData['pthread'] == 1))
			$pc .= "<div class='deleteConfirm' style='height:23px;'>
				<form name='deletethreadform' action='index.php?shard=forum&amp;action=deleteThread' method='post'>
					<input type='hidden' name='ID' value='$id' />
					<input type='hidden' name='shardname' value=\"$blog\" />
					<span style='float:left;padding-top:4px;'>
						<input type='checkbox' name='deleteThreadDidCheck' class='controls' /> $LANG[DELETE_THREAD]?
					</span>
					<span style='float: right;'>
						<input type='submit' class='button' value=\"$LANG[DELETE_THREAD]\" />
					</span>
				 </form>
				 <div class='clearfix'></div>
				</div>";

	$pc .= "<span id='valid_form' style='display:none;'>OK</span>
		<form name='thread_form' action='index.php?shard=forum&amp;action=proc_edit' method='post'>
		<br />";

	$pc .= "$LANG[TITLE]:
		<br />
		<input size='50' class='controls' type='text' name='title' id='newthreadtitle' value=\"$postData[title]\" />
		<br />";

	$pc .= "$LANG[POST_BODY]:
		<br />
		<textarea class='post_textarea' name='body' id='postArea0' rows='20' cols='65'>$postData[body]</textarea>
		<br />
		<br />
		<input type='hidden' name='ID' value='$id' />
		<input type='hidden' name='shardname' value=\"$blog\" />";

	// Tags
	$tags_cache = "";
	$tags_buttons = "";
	$virg = "";
	$query = mf_query("select ID,tag from forum_tags where threadID = '$id' order by tag"); 
	while ($row = mysql_fetch_assoc($query)) {
		if ($row['tag']) {
			$tags_cache .= $virg . $row['tag'];
			$virg = ", ";
			$tags_buttons .= "<div id='t_buttag_".$row['ID']."' class='button_tag'><span id='t_seltag_".$row['ID']."' class='selected_tag'>".$row['tag']."</span><span class='deleteButton' onclick=\"t_remove_onetag('".$row['ID']."');\">x</span></div>";
		}
	}
	$pc .= "$LANG[TAGS]: <div id='t_tags_list' style='display:inline-block;'>$tags_buttons</div>
			<input name='tags' autocomplete='off' type='hidden' id='t_tags_cache' value=\"$tags_cache\" />
			<div style='margin-top:6px;'>
			<div style='display:inline-block;width:100px;'>$LANG[TAG_THREAD_ADD]:</div>
			<input size='16' name='add_tag' autocomplete='off' id='inputTag' onkeyup=\"input_tag(); return false;\" style='color:#000000;width:196px;' class='bselect'/>
			<span class='button' id='t_create_tag' style='display:none;' onclick=\"t_add_tag();\">$LANG[TAG_BUTTON_CREATE]</span>
			<span class='button' id='t_add_tag' style='display:none;' onclick=\"t_add_tag();\">$LANG[TAG_BUTTON_ADD]</span>
			<div></div>
			<div style='display:inline-block;width:100px;'></div>
			<select name='inputSelectTag' id='inputSelectTag' style='display:none;position:absolute;margin-left:103px;width:200px;' class='bselect' size='5' onchange=\"inputselect_tag();\"></select>
			<div style='height:10px;'></div>";
	
	// Channels
	$pc .= "$LANG[CHANNEL_TAG]:<br/>
		<select class='bselect' name='channelTag'>";
		
	$channelTags = mf_query("select * from categories order by nb"); 
	while ($c = mysql_fetch_assoc($channelTags)) {
		if ($postData['category'] == $c['ID'])
			$pc .= "<option selected='selected' value='$c[ID]'>$c[name]</option>";
		else
			$pc .= "<option value='$c[ID]'>$c[name]</option>";
	}
		
	$pc .= "</select><br/>";
	
	// Sticky
	$checked = "";
	if (($verifyEditDelete && !$level9) || ($postData['teamID'] && $postData['pthread'] && !$level9)) {
		if ($postData['threadtype'] == 1)
			$checked = "checked='checked'";
		$pc .= "<br/><input $checked class='controls' type='checkbox' name='sticky' /> $LANG[SET_STICKY]<br/>";
	}
	else if ($level9) {
		if ($postData['threadtype'] == 1)
			$checked = "checked='checked'";
		$pc .= "<br/><input $checked class='controls' type='checkbox' name='sticky' /> $LANG[SET_STICKY]<br/>";
	}
   else {
		if ($postData['threadtype'] == 1)
			$pc .= "<input type='hidden' name='sticky' value='on' />";
	}

	// Locked
	$lockChecked = "";
	if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo') || ($postData['pthread'] && $CURRENTUSERID == $postData['userID']) || $teamLevel == "1") {
		if ($postData['locked'] == 1)
			$lockChecked = "checked='checked'";
		$pc .= "<br/><input $lockChecked class='controls' type='checkbox' name='locked' /> $LANG[SET_LOCKED]<br/>";
		if (!$lockChecked)
			$pc .= "<span style='margin-left:30px;'><small>$LANG[SET_LOCKED_TEXT]:</small></span><br/>
				<textarea style='margin-left:30px;' class='bselect' cols='60' rows='2' name='lock_text'></textarea><br/>";
	}

		// Creator Locked
		$creatorlockChecked = "";
		if ($CURRENTUSERID == $postData['userID']) {
			if ($postData['creator_locked'] == 1)
				$creatorlockChecked = "checked='checked'";
        	$pc .= "<br/><input $creatorlockChecked class='controls' type='checkbox' name='creator_locked' /> $LANG[SET_CREATOR_LOCKED]<br/>";
		}

	// Blogs
	$blop_ptrue = "";
	$blogChecked = "";
	$blog_pChecked = "";
	$newsChecked = "";
	$blog_fChecked = "";
	$blog_Checked = "";
	$blog_fVisible = "";
	if ($verifyBlogger && $postData['pthread'] == 0)	{
		if ($postData['blog'] == 1)	{
			$blogChecked = "checked='checked'";
			$blog_fVisible = "display:block;";
		}
		else if ($postData['blog'] == 2) {
			$blogChecked = "checked='checked'";
			$blog_pChecked = "checked='checked'";
			$blop_ptrue = "on";
			$blog_fVisible = "display:block;";
		}
		if ($postData['news'] == 1 && $postData['blog'] == 2) {
			$newsChecked = "checked='checked'";
		}

		if ($postData['unvisible'] == 0)
				$blog_fChecked = "checked='checked'";

		$pc .= "<br/><label><input $blogChecked class='controls' type='checkbox' name='blog' onclick=\"toggleLayer('blog_f');\" /> $LANG[SET_BLOG]</label><br/>";
		$pc .= "<div id='blog_f' style='$blog_fVisible'>";
		if ($CURRENTUSERID == $postData['userID'])
			$pc .= "<br/><input $blog_pChecked class='controls' type='checkbox' name='blog_p' style='margin-left:20px' /> $LANG[SET_BLOG_PERSO2]<br/>";
		else 
			$pc .= "<br/><input readonly='readonly' $blog_pChecked class='controls' type='checkbox' name='blog_p' style='margin-left:20px' /> $LANG[SET_BLOG_PERSO2]<input type='hidden' name='blog_p' value='$blop_ptrue' /><br/>";
		$pc .= "<br/><input $newsChecked class='controls' type='checkbox' name='news' style='margin-left:20px' /> $LANG[SET_MAIN_BLOG]<br/>";

			$pc .= "<br/><input $blog_fChecked class='controls' type='checkbox' name='blog_f' style='margin-left:20px' /> $LANG[SET_BLOG_FORUM]<br/>";
		$pc .= "</div>";
	}
	else if ($postData['pthread'] == 0) {
		if ($postData['blog'] == 2)	{
			$blog_Checked = "checked='checked'";
			$blog_fVisible = "display:block;";
			if ($postData['unvisible'] == 0)
				$blog_fChecked = "checked='checked'";
		}
			$pc .= "<br/><input $blog_Checked class='controls' type='checkbox' name='blog' onclick=\"toggleLayer('blog_f');\" /> $LANG[SET_BLOG_PERSO]<br/>";
			$pc .= "<div id='blog_f' style='$blog_fVisible'><br/><input $blog_fChecked class='controls' type='checkbox' name='blog_f' style='margin-left:20px' /> $LANG[SET_BLOG_FORUM]<br/></div>";
		}

	// Spoiler
	$spoilerChecked = "";
	if ($postData['spoiler'] == 1) {
		$spoiler = 1;
		$spoilerChecked = "checked='checked'";
	}
	$pc .= "<br/><input $spoilerChecked class='controls' type='checkbox' name='spoiler' /> Spoiler<br/>";
	
	// Polls
	$no_answer = false;
	if ($postData['poll']) {
		$pollInfo = mf_query("select * from poll_topics where ID = '$postData[poll]' limit 1");
		$pollInfo = mysql_fetch_assoc($pollInfo);
		$pollDays = round(($pollInfo['end_date'] - time()) / 86400);
		if ($pollDays < 1)
			$pollDays = "0";
		$pollOptions = mf_query("select * from poll_answers where poll_ID = '$postData[poll]' ORDER BY ID");
		$a_i = 1;
		while ($row = mysql_fetch_assoc($pollOptions)) {
			$answer[$a_i] .= $row['answer'];
			$a_i ++;
		}
		if (!$verifyEditDelete) {
			$pollresponse = mf_query("select poll_ID from poll_responses where poll_ID = '$postData[poll]'");
			if (!$pollresponse = mysql_fetch_assoc($pollresponse))
				$no_answer = true;
		}
		else
			$no_answer = true;
		
		if ($no_answer)
			$pc .= "<br/><input class='controls' checked='checked' readonly='readonly' type='checkbox' name='poll' id='poll' /> 
				<span onclick=\"toggleLayer('pollOptions');\" class='jl2'>$LANG[MODIFY_POLL]?</span>
				<input type='hidden' name='poll_modif' value='$postData[poll]' />
				<br/>";
	}
	else {
		$pc .= "<br/><input class='controls' type='checkbox' name='poll' id='poll' onclick=\"toggleLayer('pollOptions');\" /> $LANG[CREATE_POLL]?<br/>";
		$pollInfo['question'] = "";
		$answer[1] = "";
		$answer[2] = "";
		$answer[3] = "";
		$answer[4] = "";
		$answer[5] = "";
	}

	if ($no_answer || !$postData['poll']) {
		$pc .= "<div class='pollOptions' id='pollOptions'>
			<div class='gridDataField'>$LANG[POLL_QUESTION]:</div><div class='gridDataField'>
			<input type='text' class='controls' name='pollQuestion' id='pollQuestion' size='60' value=\"$pollInfo[question]\" /></div>
			<div class='clearfix'></div>
			<br/><br/><br/>
			<div class='gridDataField'>$LANG[OPTION] 1:</div><div class='gridDataField'>
			<input type='text' class='controls' size='40' name='pollOption1' id='pollOption1' value=\"$answer[1]\" /></div>
			<div class='clearfix'></div>

			<div class='gridDataField'>$LANG[OPTION] 2:</div><div class='gridDataField'>
			<input type='text' class='controls' size='40' name='pollOption2' id='pollOption2' value=\"$answer[2]\"/></div>
			<div class='clearfix'></div>

			<div class='gridDataField'>$LANG[OPTION] 3:</div><div class='gridDataField'>
			<input type='text' class='controls' size='40' name='pollOption3' id='pollOption3' value=\"$answer[3]\"/></div>
			<div class='clearfix'></div>

			<div class='gridDataField'>$LANG[OPTION] 4:</div><div class='gridDataField'>
			<input type='text' class='controls' size='40' name='pollOption4' id='pollOption4' value=\"$answer[4]\"/></div>
			<div class='clearfix'></div>

			<div class='gridDataField'>$LANG[OPTION] 5:</div><div class='gridDataField'>
			<input type='text' class='controls' size='40' name='pollOption5' id='pollOption5' value=\"$answer[5]\"/></div>
			<div class='clearfix'></div>						
			<br/><br/><br/>
			<div class='gridDataField'>$LANG[POLL_TIME]:</div>
			<div class='gridDataField' style='width:400px;'>
			<input type='text' class='controls' size='3' name='pollDays' id='pollDays' value='0' />
			<small>$LANG[POLL_TIME2]</small></div>
			<div class='clearfix'></div>
			</div>";
	}
   if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo')) {
		$ptchecked = "";
		if ($postData['pthread'] == 1)
			$ptchecked = "checked='checked'";
		$pc .= "<div class='deleteConfirm' style='height:20px;'><span style='float:right;' class='button' onclick=\"toggleLayer('unpthread');\"> $LANG[MANAGE_PRIVATE_THREADS]? </span>
			<span style='display:none; float:left;padding-top:3px;' id='unpthread'>
			<input $ptchecked class='controls' type='checkbox' name='ptchange' /> $LANG[PRIVATE_THREAD] 
			<small> [<a href=\"".make_link("forum","&amp;action=g_mpusers&amp;thread2=$id&amp;threadt=$postData[title]")."\" style='button_mini'>$LANG[MP_LIST]</a>]</small>
			</span><div class='clearfix'></div></div>";
	}
	
	$pc .= "<br/>
			<span id='sendThread' class='button' onclick=\"validateForm();\">$LANG[SAVE_SETTINGS]</span>
			<span id='sendThreadDisabled' class='button' style='display:none;color:silver;'>$LANG[SAVE_SETTINGS]</span>
			</form>";
	 
	 
	$thisContentObj->primaryContent = $pc;
	
	//------------------------------------------------------------------------------
	// Add this contentObject to the shardContentArray
	//------------------------------------------------------------------------------
	$shardContentArray[] = $thisContentObj;
	
}
else {
	$thisContentObj->primaryContent = $LANG['UNAUTHORIZED_EDIT'];
	$thisContentObj->primaryContent .= "&nbsp;<span class='button' onclick=\"history.back();\">".$LANG['BUTTON_BACK']."</span>";
	
	$shardContentArray[] = $thisContentObj;
}

}
break;

//-------------------------------------------------------------------------------------
// proc_edit - process the edited forum (submit into database)
//-------------------------------------------------------------------------------------
case "proc_edit": {

$id = make_num_safe( $_REQUEST['ID']);

$u = mf_query("SELECT * FROM forum_topics WHERE ID='$id' LIMIT 1");
$u = mysql_fetch_assoc($u);
$level9 = false;
$teamLevel = "0";
if ($u['teamID'])
	$teamLevel = isInTeam($u['teamID'],$CURRENTUSERID);

    $allowedit = true;
	if ($CURRENTSTATUS == "banned")
		$allowedit = false;
	else if ($u['threadtype'] == 3)
		$allowedit = false;
	else if ($u['locked'] && !$verifyEditDelete && !isInGroup($CURRENTUSER, 'modo') && $teamLevel != "1")
		$allowedit = false;
	else if ($CURRENTUSER != $u['user'] && !$verifyEditDelete && !isInGroup($CURRENTUSER, 'modo') && !$level9 && $teamLevel != "1")
		$allowedit = false;
	else if ($u['pthread'] == 1) {
		$allowedit = false;
		$verifypthread = mf_query("SELECT date FROM fhits WHERE userID='$CURRENTUSERID' AND threadID = '$id' LIMIT 1");
		if ($verifypthread = mysql_fetch_assoc($verifypthread))
			$allowedit = true;
	}

    if ($allowedit)	{

	$_REQUEST['body'] = preformat_body($_REQUEST['body']);

	if( get_magic_quotes_gpc() == 1 ) {
		$cleanmsg = htmlspecialchars($_REQUEST['body'] );
		$title = htmlspecialchars( $_REQUEST['title'] );
	}
	else {
		$cleanmsg = make_var_safe(htmlspecialchars($_REQUEST['body'] ));
		$title = make_var_safe(htmlspecialchars( $_REQUEST['title'] ));
	}

	// Historique des éditions
	$notesLine = "";
	if ($u['body'] != $cleanmsg) {
		$diff_time = time() - $u['date'];
		$v = mf_query("select ID, notes from forum_posts where threadID='$id' ORDER BY ID limit 1");
		$v = mysql_fetch_assoc($v);
		
		if (($diff_time > 180) || $u['teamID'] > 0) {
			if ($v['notes'])
				$notesLine = $v['notes']."<br/>*<small>$LANG[EDITED_AT] " . date($LANG['DATE_LINE_TIME'], time()) . " $LANG[ON] " . date($LANG['DATE_LINE_MINIMAL2'], time()) . " $LANG[BY] $CURRENTUSER</small>";	
			else
				$notesLine = $v['notes']."*<small>$LANG[EDITED_AT] " . date($LANG['DATE_LINE_TIME'], time()) . " $LANG[ON] " . date($LANG['DATE_LINE_MINIMAL2'], time()) . " $LANG[BY] $CURRENTUSER</small>";
		}
		else
			$notesLine = "*<small>$LANG[EDITED_AT] " . date($LANG['DATE_LINE_TIME'], time()) . " $LANG[ON] " . date($LANG['DATE_LINE_MINIMAL2'], time()) . "</small>";

		mf_query("INSERT INTO forum_posts_history
				(body, user, date, threadID, postID)
				VALUES
				('".mysql_real_escape_string($cleanmsg)."', \"$CURRENTUSER\", ".time().", '$id', $v[ID])");
	}

	$poll = 0;
	if (isset($_POST['poll']) && $_POST['pollQuestion'] != "" && $_POST['pollOption1'] != "" && $_POST['pollOption2'] != "") {
		if (is_numeric($_POST['pollDays'])) {
			$pollQuestion = make_var_safe(xss_clean($_POST['pollQuestion']));

			if ($_POST['pollDays'] > 0)
				$_POST['pollDays'] = time() + $_POST['pollDays'] * 86400;
			else
				$_POST['pollDays'] = 0;

			if (!$_POST['poll_modif']) {
				mf_query("insert into poll_topics (question, end_date) VALUES ('$pollQuestion', $_POST[pollDays])");
				$findID = mf_query("select ID from poll_topics where question='$pollQuestion' order by ID DESC LIMIT 1");
				$findID = mysql_fetch_assoc($findID);
				$poll = $findID['ID'];
				$c = 1;

				while ($_POST['pollOption'.$c] != "") {
					$pollOption = make_var_safe(xss_clean($_POST['pollOption'.$c]));							

					mf_query("insert into poll_answers (answer, poll_ID) VALUES ('$pollOption', $findID[ID])");
					$c++;
				}
			}
			else {
				$poll = make_num_safe($_POST['poll_modif']);
				mf_query("update poll_topics set question = \"$pollQuestion\", end_date = '$_POST[pollDays]' where ID = '$poll' limit 1");
				$responseID = 0;
				for ($c=1;$c<=5;$c++) {
					$pollOption = make_var_safe(xss_clean($_POST['pollOption'.$c]));	
					$query_res = mf_query("SELECT ID FROM poll_answers WHERE poll_ID = '$poll' ORDER BY ID LIMIT $responseID,1"); 
					if ($query_res = mysql_fetch_assoc($query_res)) {
						if ($pollOption)
							mf_query("UPDATE poll_answers SET answer = \"$pollOption\" WHERE ID = '$query_res[ID]' LIMIT 1");
						else
							mf_query("DELETE FROM poll_answers WHERE ID = '$query_res[ID]' LIMIT 1");
					}
					else
						mf_query("INSERT INTO poll_answers (answer, poll_ID) VALUES ('$pollOption', '$poll')");
					$responseID ++;
				}
			}
		}
	}

	if ($_POST['tags']) {
		$tags_array = explode(",",make_var_safe($_POST['tags']));
		$i = 0;
		mf_query("DELETE FROM forum_tags WHERE threadID = '$id'");
		while (array_key_exists($i,$tags_array)) {
			$tag = mb_strtolower(trim($tags_array[$i]),'UTF-8');
			if ($tag) {
				mf_query("INSERT IGNORE INTO forum_tags (tag, threadID) VALUES (\"$tag\",'$id')");
			}
			$i++;
		}
	}

	$s = 2;
	$l = 0;


	if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo')) {	
		$pt = 0;
		if (isset($_POST['ptchange']))
			$pt = 1;

		if (($pt == 1 && $u['pthread'] == 0) || ($pt == 0 && $u['pthread'] == 1))
			$result = mf_query("update forum_topics set pthread='$pt' where ID='$id' limit 1");

		if ($pt == 1)
			$u['pthread'] = 1;
	}
	
	$sticky = "";
	if (array_key_exists('sticky',$_POST))
		$sticky = $_POST['sticky'];
	if ($verifyEditDelete) {	
		if ($sticky == "on")
			$s = 1;
	}
	
	if (array_key_exists('locked',$_POST) && ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo') || $u['pthread'] || $teamLevel == "1")) {
		if (isset($_POST['locked'])) {
			$lock_text = "";
			if ($_POST['lock_text'])
				$lock_text = "[br][br]" . make_var_safe(htmlspecialchars($_POST['lock_text']));

			$lockimg = "[img]engine/grafts/" . $siteSettings['graft'] . "/images/lock.gif[/img]";
			$l = 1;
			if (!$u['locked'])
				submitPostToDB("$lockimg $LANG[LOCKED_DISABLED] $lockimg $lock_text", $id, 0);
		}
	}
	
	// Creator Locked
	$creator_l = "";
	if ($CURRENTUSERID == $u['userID']) {
		$creator_l = "creator_locked = '0', ";
		if (isset($_POST['creator_locked']))
			$creator_l = "creator_locked = '1', ";
	}
	
	$stickytime = "";
	if ($u['threadtype'] == "1" && $sticky == "on")
			$s = 1;
	else if ($u['teamID'] && $u['pthread'] && $sticky == "on")
		$s = 1;
	if ($s == 1 && $u['threadtype'] == '2' && !$u['stickytime'])
		$stickytime = time();
	else if ($u['stickytime'])
		$stickytime = $u['stickytime'];

	$b = 0;
	$news = $u['news'];
	$unvisible = $u['unvisible'];
	$post_blog = "";
	if ($verifyBlogger && isset($_POST['blog'])) {
		if (isset($_POST['blog_p']))
			$b = 2;
		else
			$b = 1;
		$unvisible = 0;
		if (!isset($_POST['blog_f']))
			$unvisible = 1;
		$news = 0;
		if (isset($_POST['news']))
			$news = 1;
	}

	if (!$verifyBlogger && isset($_POST['blog'])) {
		$b = 2;
		$unvisible = 0;
		if (!isset($_POST['blog_f'])) {
			$unvisible = 1;
		}
	}

	$spoiler = 0;
	if (isset($_POST['spoiler']))
		$spoiler = 1;

	count_tags();
	load_tags();

	$category = make_num_safe($_POST['channelTag']);

	mf_query("UPDATE forum_topics SET $creator_l locked = '$l', blog = '$b', news = '$news', title=\"$title\", body='$cleanmsg', threadtype = $s, category='$category', spoiler='$spoiler', stickytime = '$stickytime', unvisible = '$unvisible', poll='$poll' where ID='$id'");
	mf_query("update forum_posts set body='$cleanmsg', notes=\"$notesLine\" where threadID=$id order by ID asc limit 1");
	
	// Set introduce thread

	$cat_intro_ID = mf_query("select introduce_ID from settings limit 1");
	$cat_intro_ID = mysql_fetch_assoc($cat_intro_ID);
	$cat_intro_ID = $cat_intro_ID['introduce_ID'];

	$verify_thread = mf_query("select introducethread from users where ID = '$u[userID]' limit 1");
	$verify_thread = mysql_fetch_assoc($verify_thread);

	
	if ($category != $cat_intro_ID) {
		if ($verify_thread['introducethread'] == $id)
			mf_query("update users set introducethread = NULL where ID = '$u[userID]' limit 1");
	}
	else {
		if ($verify_thread['introducethread'] != $id)
			mf_query("update users set introducethread = '$id' where ID = '$u[userID]' limit 1");
	}

	// RSS writing
	if ($b == 1 or $news == 1)
		make_rss("");
	else if ($b == 2)
		make_rss($u['userID']);

	if ($_REQUEST['shardname'] == "1")
		header("Location: ".make_link("blog"));
	else if ($_REQUEST['shardname'] == "2")
		header("Location: ".make_link("blog","&userID=$u[userID]"));
	else
		header("Location: ".make_link("forum"));

	exit();
}
}
break;

//-------------------------------------------------------------------------------------
// deleteThread - delete a thread
//-------------------------------------------------------------------------------------
case "deleteThread": {

	$ID = make_num_safe( $_REQUEST['ID'] );
	$u = mf_query("select user, userID from forum_topics where ID=$ID limit 1");
	$u = mysql_fetch_assoc($u);
	
	if ($_REQUEST['shardname'] == "1")
		$bloglink = "blog=1";
	else if ($_REQUEST['shardname'] == "2")
		$bloglink = "blog=2";

	if ($verifyEditDelete || ($CURRENTUSER == $u['user'])) {
		if (isset($_POST['deleteThreadDidCheck'])) {
			$delRS = mf_query("update forum_topics set threadtype=3 where ID=$ID limit 1");
			$file = "cache/t_". $ID . ".html";
			if ($wf = @fopen($file,"w")) {
				fclose($wf);
				unlink($file);
			}

			if ($_REQUEST['shardname'] == "1")
				header("Location: ".make_link("blog"));
			else if ($_REQUEST['shardname'] == "2")
				header("Location: ".make_link("blog","&action=g_default&userID=$u[userID]"));
			else
				header("Location: ".make_link("forum"));
		}
		else
			header("Location: ".make_link("forum","&action=g_editThread&ID=$ID&amp;$bloglink"));
	}
	else
		header("Location: ".make_link("forum"));
}
break;

case "g_signIn": {

	$thisContentObj = New contentObj;        

	$thisContentObj->primaryContent = "$LANG[ACTION_NOT_AVAILABLE].";

	$shardContentArray[] = $thisContentObj;

}
break;

case "clearpt": {
	
	$reset = mf_query("update fhits set userID='$CURRENTUSERID' where user=\"$CURRENTUSER\"");
	header("Location: ".make_link("forum"));
}
break;

case "g_ep": {

if ($CURRENTUSER != "anonymous") {
	if (!is_numeric($_REQUEST['ID']))
		exit('$LANG[USER_PROFILE_INVALID_USERID]');

	$thisContentObj = New contentObj;
	$thisContentObj->primaryContent = "<div id='user_profile'>".userprofile($_REQUEST['ID'])."</div>";

	$shardContentArray[] = $thisContentObj;
}
}
break;

case "un2id": {
	if (array_key_exists("name", $_REQUEST)) {
		$_REQUEST['name'] = make_var_safe($_REQUEST['name']);
		
		$idl = mf_query("select ID from users where username=\"$_REQUEST[name]\" limit 1");
		$idl = mysql_fetch_assoc($idl);

		header("Location: ".make_link("forum","&action=g_ep&ID=$idl[ID]","#user/$idl[ID]"));
	}
	else
		header("Location: ".make_link("forum"));
}
break;

case "g_rules": {
	if ($CURRENTUSER != "anonymous") {
		if (isset( $_POST['acceptrules']))
			$rules = "1";
		else
			$rules = "2";

		$rules = mf_query("UPDATE users SET rules = '$rules' WHERE ID = '$CURRENTUSERID' limit 1");
		if ($rules == "1")
			header("Location: ".make_link("forum"));
		else {
			$thisContentObj = New contentObj;
			$thisContentObj->contentType = "generic";
			$thisContentObj->primaryContent = "<h3>$LANG[RULES_TEXT5]$siteSettings[titlebase]$LANG[RULES_TEXT6]</h3><br/>";
			$thisContentObj->primaryContent = "$LANG[RULES_TEXT7]<br/>";
			$thisContentObj->primaryContent = "$LANG[RULES_TEXT8]";
			$shardContentArray[] = $thisContentObj;
		}
	}
}
break;

case "g_reset_rules": {
if ($CURRENTUSER != "anonymous") {
	$rules = mf_query("UPDATE users SET rules = NULL WHERE ID = '$CURRENTUSERID' limit 1");
	header("Location: ".make_link("forum"));
}
}
break;

// Private threads user managment.
case "g_mpusers": {
	if (isInGroup($CURRENTUSER, "admin")) {    

		$tid = make_var_safe( $_REQUEST['thread2']);
		$tidt = make_var_safe( $_REQUEST['threadt']);
		
		$co = New contentObj;
		$co->title = "$LANG[MP_TITLE]: $tidt";
		$co->contentType="generic";
		$co->primaryContent = "<table>";
		$co->primaryContent .= "<b><tr><td><b>$LANG[NUMBER]</b></td><td><b><center>$LANG[USERNAME]</center></b></td></tr><b>";
		$co->primaryContent .= usermplist($tid,$tidt);
		$co->primaryContent .= "<br/></table>";
		$co->primaryContent .= "<form action='index.php?shard=forum&amp;action=mpusers_addNewUser' method='post'>
							<input type='hidden' name='thread' value='$tid' />
							<input type='hidden' name='threadt' value=\"$tidt\" />
							<input class='bselect' type='text' name='user2' size=22 />
							<input class='button_mini' type='submit' value=\"$LANG[ADDUSER]\" />
							</form>";		

		$shardContentArray[] = $co;
	}
}
break;
	
case "mpusers_addNewUser": {
	if (isInGroup($CURRENTUSER, "admin")) {
		$user = make_var_safe( $_REQUEST['user2']);
		$tid = make_var_safe( $_REQUEST['thread']);
		$tidt = $_REQUEST['threadt'];
		$usera = mf_query("select userID from forum_user_nri where name='$user' limit 1");
		if ($userb = mysql_fetch_assoc($usera))
			$is = mf_query("insert ignore into fhits (userID, threadID, date, addedDate) VALUES ($userb[userID], $tid, 0, ".time().")");

			header("Location: ".make_link("forum","&action=g_mpusers&thread2=$tid&threadt=$tidt"));
	}
}
break;
	
case "mpusers_deleteUser": {
	if (isInGroup($CURRENTUSER, "admin")) {
		$tid = make_var_safe( $_REQUEST['thread2']);
		$tidt = make_var_safe($_REQUEST['threadt']);

		if( is_numeric( $_REQUEST['user2']))
		   $del = mf_query("delete from fhits where userID=$_REQUEST[user2] and threadID=$_REQUEST[thread2] limit 1");

		header("Location: ".make_link("forum","&action=g_mpusers&thread2=$tid&threadt=$tidt"));
	}
}
break;

// Users list
case "g_users":
if ($CURRENTUSER != "anonymous" and $CURRENTSTATUS != "banned") {

	$jt = "";
	if ($CURRENTUSERAJAX)
		$jt = "</span>";

	$thisContentObj = New contentObj;
	$thisContentObj->contentType = "generic";

	$menu_top = "";
	$displaytype = "";
	if ($siteSettings['module_friends']) {
		if (isset($_REQUEST['type']))
			$displaytype = $_REQUEST['type'];
		$menu_top .= "<div style='float:left;margin-right:8px;'>";
		if ($displaytype)
			$menu_top .= "<a href='".make_link("forum","&amp;action=g_users")."' title=\"\" class='button'>$LANG[USERS_LIST]</a>&nbsp; ";
		if ($displaytype != "1")
			$menu_top .= "<a href='".make_link("forum","&amp;action=g_users&amp;type=1")."' title=\"\" class='button'>$LANG[FRIEND_LIST_FRIEND_BUTTON]</a>&nbsp; ";
		if ($displaytype != "3")
			$menu_top .= "<a href='".make_link("forum","&amp;action=g_users&amp;type=3")."' title=\"\" class='button'>$LANG[FRIEND_LIST_BLOCKED_BUTTON]</a>&nbsp; ";
		$menu_top .= "</div><div style='clear:both;'></div><div style='margin-bottom:8px;margin-top:8px;border-bottom:solid 1px silver;'></div>";
	}
	$thisContentObj->primaryContent = $menu_top."<div id='threadlist' style='display: block;'><div id='parentC'>";

	if (!$displaytype) {
		$query2 = "";
		$search = "";
		$search2 = "";
		$searchuser = "";
		$userpage = 0;
		$textposition = "";
		$position_name = "";
		$sens = "";
		
		$pc = "1";
		if (array_key_exists('pageCount', $_REQUEST))
		{
			$pc = make_num_safe( $_REQUEST['pageCount']);

			if ($pc == "1")
				$limitBoundary = "0,100";
			else
			{
				$upperBound = $pc * 100;
				$lowerBound = $upperBound - 100;
				$limitBoundary = "$lowerBound, 100";
			}
		}
		else
			$limitBoundary = "0,100";

		$userID = "";
		if (array_key_exists('username', $_POST)) {
			$username = make_var_safe($_POST['username']);
			$user = mf_query("select ID	from users where username = \"$username\" limit 1");
			$user = mysql_fetch_assoc($user);
			$userID = $user['ID'];
		}

		if (!$userID && array_key_exists('userID', $_REQUEST)) {
			if (is_numeric($_REQUEST['userID']))
				$userID = $_REQUEST['userID'];
		}
		else if (!$userID)
			$userID = $CURRENTUSERID;

			$user = mf_query("SELECT 
				users.ID, users.username, users.birthdate, users.location, users.rating, users.datejoined,
					forum_user_nri.times_quoted, forum_user_nri.num_posts, forum_user_nri.num_posts_notnri, forum_user_nri.cum_post_rating,	forum_user_nri.num_threads, forum_user_nri.num_posmods, forum_user_nri.num_negmods, forum_user_nri.num_received_posmods, forum_user_nri.num_received_negmods
				FROM users 
				JOIN forum_user_nri ON users.ID = forum_user_nri.userID 
				WHERE 
				users.ID = '$userID' 
				LIMIT 1");
		$user = mysql_fetch_assoc($user);

		$userfilt = "al";
		if (array_key_exists('userf', $_REQUEST))
			$userfilt = $_REQUEST['userf'];
		
		$tds = "display:table-cell;padding:2px;font-size: .71em;border-right: 1px solid silver;border-bottom: 1px solid silver;";
		$ul = "<div id='userlist' style='display:table;'><div style='display:table-row;'>";
		$ul .= "<div style='$tds'></div>";
		$selected2 = "background-color:#888888;color:white;";
		$link2 = "<a href='".make_link("forum","&amp;action=g_users&amp;userf=");
		$link3 = "&amp;userID=$userID&amp;filter=5' class='threadType2";
		$link4 = "' title=\"$LANG[USERS_LIST_ORDER]";
		$link5 = "</a></div>";

		$class = "al";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "al") {
			$class = "al2";
			$field = "username";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
		}
		if ($userfilt == "al2")	{
			$class = "al";
			$field = "username";
			$query = "$field desc";
			$sel = "Sel";
			$selclass = $class;
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds$sel2'>".$link2.$class.$link3.$sel.$link4."$LANG[USERS_LIST_TEXT_NAME]\">$LANG[USERS_LIST_NAME]".$link5;

		$class = "ad";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "ad") {
			$class = "ad2";
			$field = "location";
			$query = "$field asc";
			$query2 = "and $field != ''";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
		}
		if ($userfilt == "ad2")	{
			$class = "ad";
			$field = "location";
			$query = "$field desc";
			$query2 = "and $field != ''";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds$sel2'>".$link2.$class.$link3.$sel.$link4."$LANG[USERS_LIST_TEXT_ADDRESS]\">$LANG[USERS_LIST_ADDRESS]".$link5;

		$class = "bd";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "bd") {
			$class = "bd2";
			$field = "birthdate";
			$query = "$field asc";
			$query2 = "and $field != ''";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
		}
		if ($userfilt == "bd2") {
			$class = "bd";
			$field = "birthdate";
			$query = "$field desc";
			$query2 = "and $field != ''";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds$sel2'>".$link2.$class.$link3.$sel.$link4."$LANG[USERS_LIST_TEXT_BIRTHDAY]\">$LANG[USERS_LIST_BIRTHDAY]".$link5;

		$class = "nr2";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "nr") {
			$class = "nr2";
			$field = "rating";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_NRI]";
			$sens = "asc";
			$search = "select count(ID) as searchuser from users where $field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select ID from users where $field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by ID asc";
		}
		if ($userfilt == "nr2")	{
			$class = "nr";
			$field = "rating";
			$query = "$field desc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_NRI]";
			$search = "select count(ID) as searchuser from users where $field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select ID from users where $field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by ID asc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_NRI]\">$LANG[USERS_LIST_NRI]".$link5;

		$class = "di";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "di") {
			$class = "di2";
			$field = "datejoined";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_INSCRIPT]";
			$search = "select count(ID) as searchuser from users where $field < '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select ID from users where $field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by ID asc";
		}
		if ($userfilt == "di2") {
			$class = "di";
			$field = "datejoined";
			$query = "$field desc";
			$sel = "Sel";
			$sens = "asc";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_INSCRIPT]";
			$search = "select count(ID) as searchuser from users where $field < '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select ID from users where $field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by ID desc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_JOINED]\">$LANG[USERS_LIST_JOINED]".$link5;

		$class = "ns2";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "ns") {
			$class = "ns2";
			$field = "num_threads";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$sens = "asc";
			$textposition = "$LANG[USER_LIST_THREADS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		if ($userfilt == "ns2") {
			$class = "ns";
			$field = "num_threads";
			$query = "$field desc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$sens = "desc";
			$textposition = "$LANG[USER_LIST_THREADS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_THREADS]\">$LANG[USERS_LIST_THREADS]".$link5;

		$class = "np2";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "np") {
			$class = "np2";
			$field = "num_posts_notnri";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$sens = "asc";
			$textposition = "$LANG[USER_LIST_POSTS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		if ($userfilt == "np2") {
			$class = "np";
			$field = "num_posts_notnri";
			$query = "$field desc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$sens = "desc";
			$textposition = "$LANG[USER_LIST_POSTS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_POSTS]\">$LANG[USERS_LIST_POSTS]".$link5;

		$class = "npi2";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "npi")	{
			$class = "npi2";
			$field = "num_posts";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_POSTS_NRI]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		if ($userfilt == "npi2") {
			$class = "npi";
			$field = "num_posts";
			$query = "$field desc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_POSTS_NRI]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID desc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds width:40px;$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_POSTSNRI]\">$LANG[USERS_LIST_POSTSNRI]".$link5;

		$class = "qu2";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "qu") {
			$class = "qu2";
			$field = "times_quoted";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_QUOTED]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		if ($userfilt == "qu2") {
			$class = "qu";
			$field = "times_quoted";
			$query = "$field desc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_QUOTED]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID desc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_QUOTED]\">$LANG[USERS_LIST_QUOTED]".$link5;

		$class = "cp2";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "cp") {
			$class = "cp2";
			$field = "cum_post_rating";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_POSTS_NOTE]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		if ($userfilt == "cp2") {
			$class = "cp";
			$field = "cum_post_rating";
			$query = "$field desc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_POSTS_NOTE]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID desc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds width:40px;$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_POST_RATING]\">$LANG[USERS_LIST_POST_RATING]".$link5;

		$class = "pm2";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "pm") {
			$class = "pm2";
			$field = "num_posmods";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_POSMODS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		if ($userfilt == "pm2")	{
			$class = "pm";
			$field = "num_posmods";
			$query = "$field desc";
			$sel = "Sel";
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_POSMODS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID desc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds width:40px;$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_DONE_POSMODS]\">$LANG[USERS_LIST_DONE_POSMODS]".$link5;

		$class = "nm2";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "nm") {
			$class = "nm2";
			$field = "num_negmods";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_NEGMODS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		if ($userfilt == "nm2") {
			$class = "nm";
			$field = "num_negmods";
			$query = "$field desc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_NEGMODS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID desc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds width:40px;$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_DONE_NEGMODS]\">$LANG[USERS_LIST_DONE_NEGMODS]".$link5;

		$class = "pmr2";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "pmr") {
			$class = "pmr2";
			$field = "num_received_posmods";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_REC_POSMODS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		if ($userfilt == "pmr2") {
			$class = "pmr";
			$field = "num_received_posmods";
			$query = "$field desc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_REC_POSMODS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID desc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds width:40px;$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_REC_POSMODS]\">$LANG[USERS_LIST_REC_POSMODS]".$link5;

		$class = "nmr2";
		$sel = "";
		$sel2 = "";
		if ($userfilt == "nmr") {
			$class = "nmr2";
			$field = "num_received_negmods";
			$query = "$field asc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_REC_NEGMODS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID asc";
		}
		if ($userfilt == "nmr2") {
			$class = "nmr";
			$field = "num_received_negmods";
			$query = "$field desc";
			$sel = "Sel";
			$sel2 = $selected2;
			$selclass = $class;
			$textposition = "$LANG[USER_LIST_REC_NEGMODS]";
			$search = "select count(forum_user_nri.ID) as searchuser from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field > '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by $query";
			$search2 = "select users.ID from forum_user_nri join users on forum_user_nri.userID = users.ID where forum_user_nri.$field = '".$user[$field]."' and users.userstatus IS NULL and users.lat > 0 ORDER by users.ID desc";
		}
		$ul .= "<div style='text-align:center;font-weight:bold;$tds width:40px;$sel2'>".$link2.$class.$link3.$sel.$link4." $LANG[USERS_LIST_TEXT_REC_NEGMODS]\">$LANG[USERS_LIST_REC_NEGMODS]".$link5;

		$ul .= "</div>";
		$i = 0;
		$pInfo = mf_query("
			select 
				users.ID, users.username, users.birthdate, users.location, users.rating, users.datejoined,
				forum_user_nri.times_quoted, forum_user_nri.num_posts, forum_user_nri.num_posts_notnri, forum_user_nri.cum_post_rating,
				forum_user_nri.num_threads, forum_user_nri.num_posmods, 
				forum_user_nri.num_negmods, forum_user_nri.num_received_posmods, forum_user_nri.num_received_negmods 
			from users 
			join forum_user_nri on users.ID = forum_user_nri.userID 
			where 
				users.userstatus IS NULL and users.lat > 0 $query2 
			ORDER by $query,users.ID asc 
			limit $limitBoundary");
		while ($row = mysql_fetch_assoc($pInfo)) {
			$i++;
			$nri = $row['rating'];
			if ($nri == "0")
				$nri = "";
			$threads = $row['num_threads'];
			if ($threads == "0")
				$threads = "";
			$postnotnri = $row['num_posts_notnri'];
			if ($postnotnri == "0")
				$postnotnri = "";
			$posts = $row['num_posts'];
			if ($posts == "0")
				$posts = "";
			$times_quoted = $row['times_quoted'];
			if ($times_quoted == "0")
				$times_quoted = "";
			$cum_post_rating = $row['cum_post_rating'];
			if ($cum_post_rating == "0")
				$cum_post_rating = "";
			$num_posmods = $row['num_posmods'];
			if ($num_posmods == "0")
				$num_posmods = "";
			$num_negmods = $row['num_negmods'];
			if ($num_negmods == "0")
				$num_negmods = "";
			$num_received_posmods = $row['num_received_posmods'];
			if ($num_received_posmods == "0")
				$num_received_posmods = "";
			$num_received_negmods = $row['num_received_negmods'];
			if ($num_received_negmods == "0")
				$num_received_negmods = "";
			$selected_user = "";
			if ($row['ID'] == $userID)
				$selected_user = "style='font-weight:bold;color:red;'";
			$birthdate = "";
			if (is_numeric($row['birthdate']))
				$birthdate = date($LANG['DATE_LINE_MINIMAL2'],$row['birthdate']);
			$ul .= "<div style='display:table-row;'>
						<div style='text-align:right;$tds'>".($i + (($pc -1) * 100))."</div>
						<div style='$tds'><a href='".make_link("forum","&amp;action=g_ep&amp;ID=$row[ID]","#user/$row[ID]")."' $selected_user >";
			if ($jt)
				$ul .= "<span onclick=\"userprofile(0,'bas','$row[ID]'); return false;\">";
			$ul .= "$row[username]$jt</a></div>
						<div style='$tds'>$row[location]</div>
						<div style='$tds'>$birthdate</div>";
			$ul .= "	<div style='text-align:right;$tds'>$nri</div>
						<div style='text-align:center;$tds'>" . date($LANG['DATEFORMAT'],$row['datejoined'])."<br/>$LANG[AT] ".date($LANG['TIMEFORMAT'],$row['datejoined'])."</div>
						<div style='text-align:right;$tds'>$threads</div>
						<div style='text-align:right;$tds'>$postnotnri</div>
						<div style='text-align:right;$tds'>$posts</div>
						<div style='text-align:right;$tds'>$times_quoted</div>
						<div style='text-align:right;$tds'>$cum_post_rating</div>
						<div style='text-align:right;$tds'>$num_posmods</div>
						<div style='text-align:right;$tds'>$num_negmods</div>
						<div style='text-align:right;$tds'>$num_received_posmods</div>
						<div style='text-align:right;$tds'>$num_received_negmods</div>
					</div>";
		}
		$ul .= "</div>";
		
		$pInfo = mf_query("select count(ID) as countuser from users where userstatus IS NULL and lat > 0 $query2");
		$row = mysql_fetch_assoc($pInfo);
		$countuser = $row['countuser'];
		$numPages = ceil($countuser / 100);

		$prev_page = $pc -1;
		$prev_page10 = $pc -10;
		$prev_page100 = $pc -100;
		$next_page = $pc +1;
		$next_page10 = $pc +10;
		$next_page100 = $pc +100;
		$pagsuiv = "";
		$pagprec = "";
		$pagsuiv10 = "";
		$pagsuiv100 = "";
		$pagprec100 = "";
		$pagprec10 = "";
		if ($pc > 1)
			$pagprec = "<a href='".make_link("forum","&amp;action=g_users&amp;userf=$userfilt&amp;userID=$userID&amp;filter=5&amp;pageCount=$prev_page")."'  class='button_mini' style='margin-left:5px;vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</a>";
		if ($pc > 10)
			$pagprec10 = "<a href='".make_link("forum","&amp;action=g_users&amp;userf=$userfilt&amp;userID=$userID&amp;filter=5&amp;pageCount=$prev_page10")."'  class='button_mini' style='margin-left:5px;vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt='- 10' />-10 $LANG[PAGES2] </a>";
		if ($pc > 100)
			$pagprec100 = "<a href='".make_link("forum","&amp;action=g_users&amp;userf=$userfilt&amp;userID=$userID&amp;filter=5&amp;pageCount=$prev_page100")."'  class='button_mini' style='margin-left:5px;vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt='-100' />-100 $LANG[PAGES2] </a>";

		if ($pc < $numPages)
			$pagsuiv = "<a href='".make_link("forum","&amp;action=g_users&amp;userf=$userfilt&amp;userID=$userID&amp;filter=5&amp;pageCount=$next_page")."' class='button_mini' style='margin-left:5px;vertical-align: middle;'>$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></a>";
		if ($pc < ($numPages + 10))
			$pagsuiv10 = "<a href='".make_link("forum","&amp;action=g_users&amp;userf=$userfilt&amp;userID=$userID&amp;filter=5&amp;pageCount=$next_page10")."' class='button_mini' style='margin-left:5px;vertical-align: middle;'>+10 $LANG[PAGES2] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt='+10' /></a>";
		if ($pc < ($numPages + 100))
			$pagsuiv100 = "<a href='".make_link("forum","&amp;action=g_users&amp;userf=$userfilt&amp;userID=$userID&amp;filter=5&amp;pageCount=$next_page100")."' class='button_mini' style='margin-left:5px;vertical-align: middle;'>+100 $LANG[PAGES2] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt='+100' /></a>";

		if ($search) {
			$searchuser = mf_query($search);
			$searchuser = mysql_fetch_assoc($searchuser);
			$searchuser = $searchuser['searchuser'] + 1;
			$posi = 0;
			$test1 = "";
			$queryuser = mf_query($search2);
			while ($posiuser = @mysql_fetch_assoc($queryuser)) {
				if ($posiuser['ID'] == $userID)
					$queryuser = "";
				else if ($posiuser['ID'])
					$posi ++;
			}
			$position = $searchuser + $posi;
			$position_name = "e";
			if ($searchuser == 1)
				$position_name = "er";
			$userpage = floor(($position / 100) + 1);
			if ($sens == "asc")
				$userpage = $numPages - $userpage + 1;
		}

		$thisContentObj->primaryContent .= "<div style='float:right;margin-top:4px;'><form name='stat_user' action='".make_link("forum","&amp;action=g_users&amp;userf=$userfilt&amp;userID=$userID&amp;filter=5")."' method='post'>$LANG[USERS_LIST_VIEW_USER] <input type='text' size='20' class='bselect' name=\"username\"/>&nbsp;<input type='submit' value=\"$LANG[SUBMIT]\" class='button_mini' /></form></div>";
		$thisContentObj->primaryContent .= "<div style='font-size:1.2em;margin-top:30px;margin-bottom:6px;'>$LANG[USERS_LIST_USER] <span style='font-weight:bold;'>$user[username]</span> : <a href='".make_link("forum","&amp;action=g_users&amp;userf=$userfilt&amp;userID=$userID&amp;filter=5&amp;pageCount=$userpage")."'>".$searchuser."</a>$position_name $textposition</div>";

		$thisContentObj->primaryContent .= "<div style='padding-top:6px;padding-bottom:6px;border-bottom:1px dashed silver;border-top:1px dashed silver;'>".$pagprec100." ".$pagprec10." ".$pagprec."&nbsp; $LANG[PAGE] $pc / $numPages &nbsp;";
		$thisContentObj->primaryContent .= $pagsuiv." ".$pagsuiv10." ".$pagsuiv100."</div>";
		$thisContentObj->primaryContent .= $ul;
		$thisContentObj->primaryContent .= "<div style='margin-top:8px;'>".$pagprec100." ".$pagprec10." ".$pagprec."&nbsp; $LANG[PAGE] $pc / $numPages &nbsp;";
		$thisContentObj->primaryContent .= $pagsuiv." ".$pagsuiv10." ".$pagsuiv100."</div>";
	}
	else if ($displaytype) {
		$userlist = "<div style='font-weight:bold;font-size:1.8em;'>";
		if ($displaytype == "1")
			$userlist .= "$LANG[FRIEND_LIST_FRIEND]";
		else
			$userlist .= "$LANG[FRIEND_LIST_BLOCKED]";
		$userlist .= "</div>";
		$query = mf_query("SELECT users.ID, username, avatar FROM users_friends JOIN users ON (users_friends.target_userID = users.ID) WHERE userID = '$CURRENTUSERID' AND friendType = '$displaytype'");
		while ($row = mysql_fetch_assoc($query)) {
			$userlist .= "<div style='margin-top:8px;'><div style='width:40px;display:inline-block;'>";
			if ($row['avatar'])
				$userlist .= "<img style='max-width:40x;width:40px;' alt='' src=\"".$row['avatar']."\" />";
			$userlist .= "</div><div style='font-size:1.6em;display:inline-block;vertical-align:bottom;margin-left:4px;'><a href='".make_link("forum","&amp;action=g_ep&amp;ID=$row[ID]","#user/$row[ID]")."'>";
			if ($jt)
				$userlist .= "<span onclick=\"userprofile(0,'bas','$row[ID]'); return false;\">";
			$userlist .= $row['username']."$jt</a></div></div>";
		}
		$thisContentObj->primaryContent .= $userlist;
	}
	
	$thisContentObj->primaryContent .= "</div></div><div id='thread' style='display: none;'></div><div id='user_profile' style='display: none;'></div><span id='filter'></span>";
	$shardContentArray[] = $thisContentObj;

}
break;

endswitch;
?>