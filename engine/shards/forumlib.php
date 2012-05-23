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

// forumlib.php

    // Get permissions *once*
	$verifyEditDelete = false;
	if (isInGroup($CURRENTUSER, "admin")) {
        $verifyEditDelete = true;
    }

	$verifyBlogger = false;
	if(isInGroup($CURRENTUSER, "admin") || isInGroup($CURRENTUSER, "level8"))
		$verifyBlogger = true;

	//////////////////////////
	///// AJAX FUNCTIONS /////
	//////////////////////////

	function ajax_submitRateComment($dataLine) {

		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $CURRENTUSERRULES;
		global $siteSettings;
		global $CURRENTUSERDTP;

		if ($CURRENTUSER == "anonymous" || $CURRENTSTATUS == "banned")
			exit();

		if ($CURRENTUSERRULES != "1" && $siteSettings['rules'])
			exit();

		$dataLineArrayC = explode("::", $dataLine);
		if (!is_numeric($dataLineArrayC[0]) || !is_numeric($dataLineArrayC[1]))
			exit();

		$query = mf_query("SELECT comment FROM postratingcomments WHERE ID = '".$dataLineArrayC[0]."' LIMIT 1");
		$result = mysql_fetch_assoc($query);

		$query = mf_query("UPDATE postratings SET comment = \"".$result['comment']."\" WHERE postID = '$dataLineArrayC[1]' AND user = \"".make_var_safe($dataLineArrayC[2])."\" LIMIT 1");

		$threadIDRS = mf_query("SELECT rating FROM forum_posts WHERE ID='$dataLineArrayC[1]' LIMIT 1");
		$threadIDRS = mysql_fetch_assoc($threadIDRS);
		$hidden = "";
		if ($threadIDRS['rating'] < $CURRENTUSERDTP)
			$hidden = "1";

		$whorated = whorated($dataLineArrayC[1]);
		$retstr = $dataLineArrayC[1] . "::@@::" . $whorated . "::@@::" . $hidden;

		
		return $retstr;
	}
	
	function ajax_showEditWindow($rowID) {
		global $LANG;
		global $verifyEditDelete;
		global $verifyBlogger;
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTSTATUS;

		if ($CURRENTUSER == "anonymous")
			exit();

		if (is_numeric($rowID)) {
			$postContent = mf_query("SELECT body, posttype, threadID, userID, date FROM forum_posts WHERE ID='$rowID' LIMIT 1");
			$postContent = mysql_fetch_assoc($postContent);

			$clean_body = str_replace("<br />", "[br]", $postContent['body']);
			$diff_time = time() - $postContent['date'];

			$firstpost = mf_query("SELECT ID FROM forum_posts WHERE threadID='$postContent[threadID]' ORDER BY date LIMIT 1");
			$firstpost = mysql_fetch_assoc($firstpost);

			$blog = mf_query("SELECT blog, user, teamID, pthread FROM forum_topics WHERE ID='$postContent[threadID]' LIMIT 1");
			$blog = mysql_fetch_assoc($blog);
			if ($CURRENTSTATUS == "banned" && !$blog['teamID'])
				exit();

			$fhits = true;
			if ($blog['pthread']) {
				$fhits = false;
				$fhits = mf_query("SELECT userID FROM fhits WHERE userID='$CURRENTUSERID' and threadID='$postContent[threadID]' LIMIT 1");
				if (!$fhits = mysql_fetch_assoc($fhits))
					exit();
			}

			if (($CURRENTUSERID == $postContent['userID']) || $verifyEditDelete || isInGroup($CURRENTUSER, 'modo') || ($blog['blog'] == "2" && $blog['user'] == $CURRENTUSER) || ($blog['teamID'] > 0) || (isInGroup($CURRENTUSER, 'level8') && !$blog['pthread'])) {

				$retStr = $rowID.":!@:<div style='display:none;' id='posteditCache$rowID'></div><table><tr><td style='vertical-align: top;'>
					<div style='display:inline-block;width:566px;vertical-align:top;' id='main_edit$rowID' class='deleteConfirm'>";
				$editbuttons = "";

				if ($firstpost['ID'] != $rowID && (($diff_time < 60 && $CURRENTUSERID == $postContent['userID']) || ($blog['teamID'] && $postContent['userID'] == "1")))
					$editbuttons = "<form name='editForm' action='index.php' onsubmit=\"return callAjaxSubmitDelete('" . $rowID . "');\" method='post'>
						<input class='button' type='submit' value=\"$LANG[DELETE_POST_BUTTON]\" /></form>";
				else if (!$verifyEditDelete && !isInGroup($CURRENTUSER, 'modo') && ($blog['blog'] && $blog['user'] == $CURRENTUSER) && $firstpost['ID'] != $rowID)
					$editbuttons = "<form name='editForm' action='index.php' onsubmit=\"return SubmitDePublish('" . $rowID . "');\" method='post'>
							<input class='button' type='submit' value=\"$LANG[DELETE_POST_BUTTON]\" /> $LANG[DELETE_POST]</form>";
				else if (!$verifyEditDelete && !isInGroup($CURRENTUSER, 'modo') && ($postContent['userID'] == $CURRENTUSERID) && $firstpost['ID'] != $rowID)
					$editbuttons = "<form name='editForm' action='index.php' onsubmit=\"return SubmitDePublish('" . $rowID . "');\" method='post'>
							<input class='button' type='submit' value=\"$LANG[DELETE_POST_BUTTON]\" /> $LANG[DELETE_POST]</form>";
				else if (($verifyEditDelete || isInGroup($CURRENTUSER, 'modo')) && $firstpost['ID'] != $rowID) {
					if ($verifyEditDelete)
						$editbuttons = "<span class='button' style='display:inline-block;;vertical-align:top;' onclick=\"return callAjaxSubmitDelete('" . $rowID . "');\">$LANG[DELETE_POST_BUTTON]</span>";
					$editbuttons .= "<span class='button' style='display:inline-block;vertical-align:top;' onclick=\"return SubmitDePublish('" . $rowID . "');\">";
					if ($postContent['posttype'] < 3)
						$editbuttons .= "$LANG[DEPUBLISH_POST_BUTTON]";
					else
						$editbuttons .= "$LANG[UNDEPUBLISH_POST_BUTTON]";
					$editbuttons .= "</span>";

				}
				if ($editbuttons)
					$retStr .= "<div>" . $editbuttons . "</div>$LANG[OR]...";
				$retStr .= "<div>
					<b>$LANG[EDIT_POST_TITLE]</b>:
					<form name='editForm' action='index.php' onSubmit=\"callAjaxSubmitEdit('" . $rowID . "');  return false;\" method='post'>
					<div><textarea name='editText$rowID' id='postArea$rowID' cols='65' rows='14' class='post_textarea'></textarea></div>";
				$retStr .= "<div style='height:26px;margin-top:2px;'>
					<span style='margin-left: 0px;float:left;'><input class='button' type='submit' value=\"$LANG[SUBMIT_EDIT]\"/></span>". printFormattingPaneB($rowID)."
					</div>";
				$retStr .= "</form></div></div></td>
					<td style='vertical-align: top;'>
					<div class='smiley_bar' id='smiley_bar$rowID'>". printFormattingPane($rowID) ."</div></td></tr></table>:!@:".$clean_body;
			}
		}
		return $retStr;
	}
	
	function ajax_submitEdit($dataLine) {
		$dataLineArray = explode(":!@:", $dataLine);		

		if (!is_numeric($dataLineArray[0]))
			exit();

		global $LANG;
		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $CURRENTUSERRULES;
		global $verifyEditDelete;
		global $siteSettings;

		$ipc=$_SERVER["REMOTE_ADDR"];

		$dataLineArray[1] = utf8_encode($dataLineArray[1]);
		$dataLineArray[1] = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $dataLineArray[1]);
		$dataLineArray[1] = html_entity_decode($dataLineArray[1], ENT_NOQUOTES, 'UTF-8');
		$dataLineArray[1] = str_replace("::@plus@::","+",$dataLineArray[1]);
		$dataLineArray[1] = str_replace("::@euro@::","€",$dataLineArray[1]);
		$dataLineArray[1] = preformat_body($dataLineArray[1]);

		if ($CURRENTUSER == "anonymous")
			exit();

		if ($CURRENTUSERRULES != "1" and $siteSettings['rules'])
			exit();

		$verify = mf_query("SELECT user, userID, threadID, posttype, notes, body, date, rating, IP FROM forum_posts WHERE ID='$dataLineArray[0]' LIMIT 1");
		$verify = mysql_fetch_assoc($verify);
		$userinfo = mf_query("SELECT sig FROM users WHERE ID='$verify[userID]' LIMIT 1");
		$userinfo = mysql_fetch_assoc($userinfo);

		$blog = mf_query("SELECT blog, user, category, teamID, pthread FROM forum_topics WHERE ID='$verify[threadID]' LIMIT 1");
		$blog = mysql_fetch_assoc($blog);
		if ($CURRENTSTATUS == "banned" && !$blog['teamID'])
			exit();

		// Verify if the post is the first post of the thread
		$firstpost = mf_query("SELECT ID FROM forum_posts WHERE threadID='$verify[threadID]' ORDER BY date LIMIT 1");
		$firstpost = mysql_fetch_assoc($firstpost);

		if ($dataLineArray[1] && (($CURRENTUSER == $verify['user']) || $verifyEditDelete || isInGroup($CURRENTUSER, 'modo') || ($blog['blog'] == "2" && $blog['user'] == $CURRENTUSER) || ($blog['teamID'] > 0) || (isInGroup($CURRENTUSER, 'level8') && !$blog['pthread']))) {
			if( get_magic_quotes_gpc() == 1 )
				$dataLineArray[1] = htmlspecialchars($dataLineArray[1]);
			else
				$dataLineArray[1] = make_var_safe(htmlspecialchars($dataLineArray[1]));

			// prevent user from using [css] bbcode
//			if (!isInGroup($CURRENTUSER, "admin") && !isInGroup($CURRENTUSER, "level5"))
//					$dataLineArray[1] = preg_replace("/\[css/i","[ css",$dataLineArray[1]);

			$dataLineArray[1] = preg_replace("/\[vote]/i","[vote.$dataLineArray[0]]",$dataLineArray[1]);

			$diff_time = time() - $verify['date'];
			$notesLine = $verify['notes'];

			if (stristr($dataLineArray[1], "[qq"))
				$dataLineArray[1] = qq_lookup($dataLineArray[1]);
			// Historique des éditions
			if ($verify['body'] != $dataLineArray[1]) {
				if (($firstpost['ID'] == $dataLineArray[0] && $diff_time > 180) || $blog['teamID'] > 0) {
					if ($verify['notes'])
						$notesLine = $verify['notes']."<br/>*<small>$LANG[EDITED_AT] " . date($LANG['DATE_LINE_TIME'], time()) . " $LANG[ON] " . date($LANG['DATE_LINE_MINIMAL2'], time()) . " $LANG[BY] $CURRENTUSER</small>";	
					else
						$notesLine = $verify['notes']."*<small>$LANG[EDITED_AT] " . date($LANG['DATE_LINE_TIME'], time()) . " $LANG[ON] " . date($LANG['DATE_LINE_MINIMAL2'], time()) . " $LANG[BY] $CURRENTUSER</small>";
				}
				else
					$notesLine = "*<small>$LANG[EDITED_AT] " . date($LANG['DATE_LINE_TIME'], time()) . " $LANG[ON] " . date($LANG['DATE_LINE_MINIMAL2'], time()) . "</small>";

				mf_query("INSERT INTO forum_posts_history
							(body, user, date, threadID, postID, IP)
						VALUES
						('".mysql_real_escape_string($verify['body'])."', \"$CURRENTUSER\", ".time().", '$verify[threadID]', $dataLineArray[0], \"$verify[IP]\")");
			}

			$sig = "<div class='sig'><br/>" . post_footer($dataLineArray[0],$userinfo['sig'],$notesLine,$verify['rating']) . "</div>";

			$posttype = "";
			$depublishInfos = "";
			$depubDate = "";
			if ($verify['posttype'] > 2 and $dataLineArray[2] == 'depublish') {
				$ptype = "2";
				$posttype = ", posttype = 2";
				mf_query("UPDATE fhits SET num_posts = num_posts + 1 WHERE threadID='$verify[threadID]' AND num_posts > 0 and userID NOT IN (SELECT userID FROM permissiongroups WHERE (pGroup = 'admin' OR pGroup = 'modo'))");
			}
			else if ($verify['posttype'] == 2 and $dataLineArray[2] == 'depublish')	{
				if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
					$ptype = "3";
				else
					$ptype = "4";
				$posttype = ", posttype = $ptype";
				mf_query("UPDATE fhits SET num_posts = num_posts - 1 WHERE threadID='$verify[threadID]' AND num_posts > 0 and userID NOT IN (SELECT userID FROM permissiongroups WHERE (pGroup = 'admin' OR pGroup = 'modo'))");
				$depubDate = time();
				$depublishInfos = ", depubBy = \"$CURRENTUSER\", depubDate = '".time()."'";

			}
			if($posttype)
				mf_query("INSERT INTO forum_posts_history
						(user, date, threadID, postID, posttype, IP)
						VALUES
						(\"$CURRENTUSER\", ".time().", '$verify[threadID]', '$dataLineArray[0]', '$ptype', \"$verify[IP]\")");

				$result = mf_query("UPDATE forum_posts SET body='$dataLineArray[1]', IP = '$ipc', notes=\"$notesLine\" $posttype $depublishInfos WHERE ID='$dataLineArray[0]' LIMIT 1");

			if ($verify['posttype'] > 2 and $dataLineArray[2] == 'depublish')
				updateThreadLastPostInfo2($verify['threadID']);
			else if ($verify['posttype'] == 2 and $dataLineArray[2] == 'depublish')
				updateThreadLastPostInfo($verify['threadID']);

				$isFirst = mf_query("SELECT ID FROM forum_posts WHERE threadID = $verify[threadID] ORDER BY ID ASC LIMIT 1");
			$isFirst = mysql_fetch_assoc($isFirst);

			if ($isFirst['ID'] == $dataLineArray[0]) {
				$result = mf_query("UPDATE forum_topics SET body='".$dataLineArray[1]."' WHERE ID=$verify[threadID] LIMIT 1");
			}

			$dataLineArray[1] = str_replace("\'", "'", $dataLineArray[1]);
			$dataLineArray[1] = str_replace("\&quot;", "&quot;", $dataLineArray[1]);

			$dataLineArray[1] = format_quickquote($dataLineArray[1], $CURRENTUSER, $dataLineArray[0]);
			$dataLineArray[1] = format_post($dataLineArray[1], false, $verify['threadID']);
			if (($verify['posttype'] > 2 and $dataLineArray[2] != 'depublish') || ($verify['posttype'] < 3 and $dataLineArray[2] == 'depublish')) {
				if (isInGroup($CURRENTUSER, "admin") || isInGroup($CURRENTUSER, "modo")) {
						$depub = "<span style='font-weight:bold;padding:3px;background-color:#FFFF00;'>$LANG[DEPUBLISHED_POST]";
					if ($depubDate)
						$depub .= " $LANG[BY] $CURRENTUSER $LANG[AT] ".date($LANG['TIMEFORMAT'],$depubDate)." $LANG[ON] ".date($LANG['DATEFORMAT'],$depubDate);
						$dataLineArray[1] = $depub."</span><div style='border-style: solid; border-color: #FFFF00;'>$dataLineArray[1]</div>";
				}
				else
					$dataLineArray[1] = $LANG['POST_DELETE_SUCCESS'];
			}
			$retStr = $dataLineArray[0] . ":!@:" . $dataLineArray[1].$sig;

			return $retStr;
		}
	}

	function ajax_submitDelete($dataLine) {	
		
		if (!(is_numeric($dataLine)))
			exit();
		else {
			global $LANG;
			global $CURRENTUSER;
			global $CURRENTUSERID;
			global $CURRENTSTATUS;
			global $CURRENTUSERRULES;
			global $verifyEditDelete;
			global $siteSettings;

			if ($CURRENTUSER == "anonymous")
			exit();

		if ($CURRENTUSERRULES != "1" and $siteSettings['rules'])
				exit();
			
			$verify = mf_query("SELECT user, userID, threadID, posttype, date FROM forum_posts WHERE ID='$dataLine' LIMIT 1");
			$verify = mysql_fetch_assoc($verify);
			$blog = mf_query("SELECT blog, user, teamID, pthread FROM forum_topics WHERE ID='$verify[threadID]' LIMIT 1");
			$blog = mysql_fetch_assoc($blog);
			if ($CURRENTSTATUS == "banned" && !$blog['teamID'])
				exit();
			
			$inteam = false;
			if ($blog['teamID'] && $blog['pthread'] && $verify['userID'] == "1") {
				$level = mf_query("SELECT level FROM teams_users WHERE teamID = '$blog[teamID]' AND userID = '$CURRENTUSERID' LIMIT 1");
				if ($level = mysql_fetch_assoc($level))
					$inteam = true;
			}

			$isFirst = mf_query("SELECT ID FROM forum_posts WHERE threadID = $verify[threadID] ORDER BY ID ASC LIMIT 1");
			$isFirst = mysql_fetch_assoc($isFirst);
			if ($isFirst['ID'] == $dataLine)
				exit();

			if (($CURRENTUSER == $verify['user']) || $verifyEditDelete || $inteam || ($blog['blog'] == "2" && $blog['user'] == $CURRENTUSER)) {
				$result = mf_query("delete FROM forum_posts WHERE ID='$dataLine' LIMIT 1");
				
				if ($verify['posttype'] < 3) {
					mf_query("UPDATE fhits SET num_posts = num_posts - 1 WHERE threadID='$verify[threadID]' AND num_posts > 0");
					$u = mf_query("SELECT user, date, ID FROM forum_posts WHERE threadID='$verify[threadID]' AND posttype < 3 ORDER BY date DESC LIMIT 1");
					if ($row = mysql_fetch_assoc($u))
						mf_query("UPDATE forum_topics SET last_post_id='$row[ID]', last_post_id_T='$row[ID]', last_post_user=\"$row[user]\", last_post_user_T=\"$row[user]\", last_post_date='$row[date]', last_post_date_T='$row[date]', num_comments=(num_comments - 1), num_comments_T=(num_comments_T - 1) WHERE ID='$verify[threadID]' LIMIT 1");
				}
				else {
					mf_query("UPDATE fhits SET num_posts = num_posts - 1 WHERE threadID='$verify[threadID]' AND num_posts > 0 and userID IN (SELECT userID FROM permissiongroups WHERE (pGroup = 'admin' OR pGroup = 'modo'))");
					$u = mf_query("SELECT user, date, ID FROM forum_posts WHERE threadID='$verify[threadID]' ORDER BY date DESC LIMIT 1");
					if ($row = mysql_fetch_assoc($u))
						mf_query("UPDATE forum_topics SET last_post_id='$row[ID]', last_post_id_T='$row[ID]', last_post_user=\"$row[user]\", last_post_user_T=\"$row[user]\", last_post_date='$row[date]', last_post_date_T='$row[date]', num_comments_T=(num_comments_T - 1) WHERE ID='$verify[threadID]' LIMIT 1");
				}

				return $dataLine.":!@:".$LANG['POST_DELETE_SUCCESS'];
			}
		}
	}	
	
	function ajax_threadUpdate($dataLine) {
		global $CURRENTUSERID;
		global $CURRENTUSER;
		global $verifyEditDelete;
		$returnStr = "false";

		$dataLineArray = explode("::@tu@::", $dataLine);

		// Are there new posts?
		$TimeAgo = $dataLineArray[0];
		$teamID = make_var_safe($dataLineArray[8]);
		$filter = make_var_safe($dataLineArray[4]);
		$page = make_var_safe($dataLineArray[5]);
		$channels = str_replace(",", "/", make_var_safe($dataLineArray[6]));
		$tags = $dataLineArray[7];
		
		$numcom = "num_comments";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$numcom = "num_comments_T";

		$newPostsQuery = generateForumStr($TimeAgo,true,"",$filter,$page,$channels,$tags,$teamID);

		$newPosts = mf_query($newPostsQuery);
		$totnewPosts = 0;
		$newPostsList = 0;
		$newPostsStrContent = "";

		if (mysql_num_rows($newPosts) > 0 and $CURRENTUSER != "anonymous") {				
			while ($row = mysql_fetch_assoc($newPosts)) {
				if ($row['Expr1'] > 0)
					$returnStr = true;
			}
		}

		$returnStr = (time() - 2) . "!@timeDlm@!" . $returnStr . "!@timeDlm@!" . $CURRENTUSERID;
		
		if ($CURRENTUSER != "anonymous")
			mf_query("UPDATE users SET lat=".time().", laid=0 WHERE ID=$CURRENTUSERID LIMIT 1");

		return $returnStr;
	}
	
	function ajax_postUpdate($dataLine) {
		global $siteSettings;
		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $CURRENTUSERID;
		global $verifyEditDelete;
		$retStr = "false";
		$dataLineArray = explode("::", $dataLine);

		$TimeAgo = make_num_safe($dataLineArray[0]);
		$dataLineArray[1] = make_num_safe($dataLineArray[1]);

		$numcom = "num_comments";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$numcom = "num_comments_T";

		$pthread=mf_query("SELECT pthread, $numcom, blog, user, teamID, category FROM forum_topics WHERE ID='$dataLineArray[1]' LIMIT 1");
		if ($pthread=mysql_fetch_assoc($pthread)) {
			$num_comments = $pthread[$numcom];
			$blog=$pthread['blog'];
			$userblog=$pthread['user'];
			$teamID=$pthread['teamID'];
			$pthread=$pthread['pthread'];
			$categoryID= "";
			if (isset($pthread['category']))
				$categoryID = $pthread['category'];

			$posttype = "AND forum_posts.posttype < 3 ";
			if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
				$posttype = "";

			$childRS = mf_query("SELECT 
									forum_posts.*, 
									users.ID as Expr1, 
									users.username,
									users.sig, 
									users.avatar, 
									users.rating as userRating 
									FROM forum_posts, users 
									where 
									users.username=forum_posts.user 
									and forum_posts.threadID=$dataLineArray[1] 
									and forum_posts.date > $TimeAgo   
									$posttype
									order by ID asc");

				if (mysql_num_rows($childRS) > 0) {	
					mysql_data_seek($childRS, 0);

					$lastPostTimeStamp = 0;
					$cur = 2;

					while ($row=mysql_fetch_assoc($childRS)) {
						if ($retStr == "false")
							$retStr = "";

						$retStr .= "__postDlm__";
						$thisContentObj = New contentObj;
						$contentArray[0] = assemblePost($thisContentObj, $row, $cur, -1, $pthread, $blog, $userblog, $teamID, $dataLineArray[1],'',$categoryID);
						$retStr .= renderPost($contentArray, 0);
						$lastPostTimeStamp = $row['date'];

						if ($cur == 2)
							$cur == 3;
						else
							$cur == 2;

						$retStr .= "__postIDDlm__$row[ID]";
					}

					if ($retStr != "false")
						$retStr = $lastPostTimeStamp . "__timeDlm__" . $retStr . "__timeDlm__" . $dataLineArray[1];
				}

			if ($CURRENTUSER != "anonymous") {
					mf_query("UPDATE users SET lat=".time().", laid='$dataLineArray[1]' WHERE ID=$CURRENTUSERID LIMIT 1");
					$checkHits = mf_query("SELECT userID, subscribed FROM fhits WHERE userID=$CURRENTUSERID and threadID='$dataLineArray[1]' LIMIT 1");
				if (mysql_num_rows($checkHits) > 0) {
					$row = mysql_fetch_assoc($checkHits);
					$subd = "";
					if ($row['subscribed'] == 1)
						$subd = ", subscribed = 2";				
						mf_query("UPDATE fhits SET date = '".time()."', num_posts = '$num_comments' $subd WHERE threadID='$dataLineArray[1]' and userID='$CURRENTUSERID' LIMIT 1");
				}
				else if ($pthread == "0")
						mf_query("INSERT INTO fhits (threadID, date, userID, num_posts) VALUES ('$dataLineArray[1]', '".time()."', $CURRENTUSERID, '$num_comments')");
				else
					exit();
			}
			
			return $retStr;
		}
	}

	function  ajax_list_modedposts($dataLine) {
		global $siteSettings;
		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $CURRENTUSERID;
		global $verifyEditDelete;
		global $LANG;
		$dataLineArray = explode("@@::moded::@@", $dataLine);

		$userID = make_num_safe($dataLineArray[0]);
		$modtype = make_var_safe($dataLineArray[1]);
		$pc = make_num_safe($dataLineArray[2]);
		$limit = "LIMIT ".(50*($pc - 1)).", 4";
		
		$canseemod = false;
		if (isInGroup($CURRENTUSER, 'modo')|| isInGroup($CURRENTUSER, 'admin') || $CURRENTUSERID == $userID || $siteSettings['viewmodlist'] == "3")
			$canseemod = true;
		else if ($siteSettings['module_friends'] && ($siteSettings['viewmodlist'] == "1" || $siteSettings['viewmodlist'] == "2")) {
			$friendstatus = friendstatus($CURRENTUSERID,$userID);
			if ($siteSettings['viewmodlist'] == "1" && $friendstatus == 2)
				$canseemod = true;
			else if ($siteSettings['viewmodlist'] == "2" && $friendstatus == 1)
				$canseemod = true;
		}
		if (!$canseemod) {
			return $LANG['REFUSED'];
			exit();
		}

		if ($modtype == "3")
			$title = $LANG['LIST_MODED_POST_REC_POS_TITLE'];
		else if ($modtype == "4")
			$title = $LANG['LIST_MODED_POST_REC_NEG_TITLE'];
		else if ($modtype == "1")
			$title = $LANG['LIST_MODED_POST_GIV_POS_TITLE'];
		else if ($modtype == "2")
			$title = $LANG['LIST_MODED_POST_GIV_NEG_TITLE'];
		$retStr = "<span style='float:right;cursor:pointer;' onclick=\"document.getElementById('list_moded').style.display = 'none';\"><img src='engine/grafts/$siteSettings[graft]/images/close.png' alt=''/></span><div style='font-size:1.5em;margin-bottom:8px;'>$title</div><div style='clear:both;'></div>";

		$sens = ">";
		if ($modtype == "2" || $modtype == "4")
			$sens = "<";
			
		$posttype = "AND p.posttype < 3 ";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$posttype = "";

		$user = mf_query("SELECT username, sig, avatar, rating AS userRating FROM users WHERE ID = '$userID' LIMIT 1");
		$user = mysql_fetch_assoc($user);

		$sav_postID = "";
		$result = false;
		if ($modtype == "1" || $modtype == "2") {
			$query=mf_query("SELECT postratings.ID FROM postratings WHERE postratings.user = \"$user[username]\" AND postratings.rating $sens 0 AND postratings.postID IS NOT NULL");
			$num_rows = mysql_num_rows($query);
			$query=mf_query("SELECT 
											t.user AS t_user, t.userID AS t_userID, t.category, t.teamID, t.blog, t.spoiler, t.poll, t.title AS t_title,
											postratings.rating AS user_p_rated, postratings.modeddate, postratings.user AS moduser,
											p.*,
											u.ID AS Expr1, u.username, u.sig, u.avatar, u.rating AS userRating
										FROM postratings 
										JOIN forum_posts AS p ON (p.ID = postratings.postID $posttype)
										JOIN forum_topics AS t ON (t.pthread = 0 AND t.threadtype < 3 AND p.threadID = t.ID)
										JOIN users AS u ON (u.ID = postratings.modeduserID)
										WHERE postratings.user = \"$user[username]\" AND postratings.rating $sens 0 AND postratings.postID IS NOT NULL
										ORDER BY postratings.postID DESC $limit");
		}
		else if ($modtype == "3" || $modtype == "4") {
			$query=mf_query("SELECT postratings.ID FROM postratings WHERE postratings.modeduserID = '$userID' AND postratings.rating $sens 0 AND postratings.postID IS NOT NULL");
			$num_rows = mysql_num_rows($query);
			$query=mf_query("SELECT 
											t.user AS t_user, t.userID AS t_userID, t.category, t.teamID, t.blog, t.spoiler, t.poll, t.title AS t_title,
											postratings.rating AS user_p_rated, postratings.modeddate, postratings.user AS moduser,
											p.*
										FROM postratings 
										JOIN forum_posts AS p ON (p.ID = postratings.postID $posttype)
										JOIN forum_topics AS t ON (t.pthread = 0 AND t.threadtype < 3 AND p.threadID = t.ID)
										WHERE postratings.modeduserID = '$userID' AND postratings.rating $sens 0 AND postratings.postID IS NOT NULL
										ORDER BY postratings.postID DESC $limit");
		}
		$page_lists = "";
		if ($num_rows > 50) {
			$numPages = ceil($num_rows / 50);
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
				$pagprec = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedposts('$userID','$modtype','$prev_page');\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt='' />$LANG[PREVIOUS_PAGE]</span>";
			if ($pc > 10)
				$pagprec10 = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedposts('$userID','$modtype','$prev_page10');\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt='- 10' />-10 $LANG[PAGES2] </span>";
			if ($pc > 100)
				$pagprec100 = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedposts('$userID','$modtype','$prev_page100');\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt='-100' />-100 $LANG[PAGES2] </span>";

			if ($pc < $numPages)
				$pagsuiv = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedposts('$userID','$modtype','$next_page');\">$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></span>";
			if ($pc < ($numPages + 10))
				$pagsuiv10 = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedposts('$userID','$modtype','$next_page10');\">+10 $LANG[PAGES2] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt='+10' /></span>";
			if ($pc < ($numPages + 100))
				$pagsuiv100 = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedposts('$userID','$modtype','$next_page100');\">+100 $LANG[PAGES2] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt='+100' /></span>";
			$page_lists .= "<div style='padding-top:6px;padding-bottom:6px;'>".$pagprec100." ".$pagprec10." ".$pagprec."&nbsp; $LANG[PAGE] $pc / $numPages &nbsp;";
			$page_lists .= $pagsuiv." ".$pagsuiv10." ".$pagsuiv100."</div>";
		}
		$retStr .= $page_lists."<div style='border-bottom:1px dashed silver;margin-bottom:8px;'></div>";
		while ($row=mysql_fetch_assoc($query)) {
			$result = true;
			$userblog=$row['t_user'];
			$teamID=$row['teamID'];
			$pthread="0";
			$categoryID=$row['category'];
			if ($modtype == "3" || $modtype == "4") {
				$row['Expr1'] = $userID;
				$row['username'] = $user['username'];
				$row['sig'] = $user['sig'];
				$row['avatar'] = $user['avatar'];
				$row['userRating'] = $user['userRating'];
			}

			if ($row['ID'] != $sav_postID) {
				$sav_postID = $row['ID'];
				$retStr .= "<div>";

				$blog = "";
				if ($row['blog'] == 1) {
					$blog = "<span class='blogNotification'>";
					$blog .= "<a href='".make_link("blog")."'>";
					$blog .= "[".$LANG['BLOG']."]</a></span>&nbsp;";
				}
				if ($row['blog'] == 2)
					$blog = "<span class='blogNotification'>
						<a href=\"".make_link("blog","&amp;action=g_user&amp;user=$row[t_user]")."\">
						[".$LANG['BLOG']."]</a></span>&nbsp;";
				$privateteam = "";
				if ($row['teamID'] > 0) {
					$privateteam = "<span class='spoilerNotification'>[ ".team_name($row['teamID'])." ]</span>";
				}
				$poll = "";
				if ($row['poll'] > 0)
					$poll = "<span class='pollNotification'>[".$LANG['POLL']."]</span>";
				$spoiler = "";
				if ($row['spoiler'] > 0)
					$spoiler = "<span class='spoilerNotification'>[".$LANG['SPOILER']."]</span>";

				$retStr .= "<div style='font-size:1.2em;font-family:Tahoma,Verdana,arial,helvetica;margin-bottom:3px;'>".$blog;
				$retStr .= $privateteam;
				$retStr .= $poll;
				$retStr .= $spoiler;
				$retStr .= " <span class='bold'><a href='".make_link("forum","&amp;action=calculatePageLocationForFirstNew&amp;postID=$row[ID]&amp;sl=".($row['date'] -1),"#post/$row[ID]")."'>$row[t_title]</span></div>";

				$thisContentObj = New contentObj;
				$contentArray[0] = assemblePost($thisContentObj, $row, 2, -1, $pthread, $row['blog'], $userblog, $teamID, $row['threadID'],'',$categoryID,'',"block");
				$retStr .= renderPost($contentArray, 0,'',true);
				$retStr .= "</div>";
			}
		}
		if (!$result)
			$retStr .= $LANG['LIST_MODED_NORESULT'];
		else
			$retStr .= "<div style='border-top:1px dashed silver;margin-top:8px;'></div>".$page_lists;

		return $retStr;
	}

	function  ajax_list_modedthreads($dataLine) {
		global $siteSettings;
		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $CURRENTUSERID;
		global $verifyEditDelete;
		global $LANG;
		$dataLineArray = explode("@@::moded::@@", $dataLine);

		$userID = make_num_safe($dataLineArray[0]);
		$modtype = make_var_safe($dataLineArray[1]);
		$pc = make_num_safe($dataLineArray[2]);
		$limit = "LIMIT ".(10*($pc - 1)).", 4";
		
		$canseemod = false;
		if (isInGroup($CURRENTUSER, 'modo')|| isInGroup($CURRENTUSER, 'admin') || $CURRENTUSERID == $userID || $siteSettings['viewmodlist'] == "3")
			$canseemod = true;
		else if ($siteSettings['module_friends'] && ($siteSettings['viewmodlist'] == "1" || $siteSettings['viewmodlist'] == "2")) {
			$friendstatus = friendstatus($CURRENTUSERID,$userID);
			if ($siteSettings['viewmodlist'] == "1" && $friendstatus == 2)
				$canseemod = true;
			else if ($siteSettings['viewmodlist'] == "2" && $friendstatus == 1)
				$canseemod = true;
		}
		if (!$canseemod) {
			return $LANG['REFUSED'];
			exit();
		}

		if ($modtype == "3")
			$title = $LANG['LIST_MODED_THREAD_REC_POS_TITLE'];
		else if ($modtype == "4")
			$title = $LANG['LIST_MODED_THREAD_REC_NEG_TITLE'];
		else if ($modtype == "1")
			$title = $LANG['LIST_MODED_THREAD_GIV_POS_TITLE'];
		else if ($modtype == "2")
			$title = $LANG['LIST_MODED_THREAD_GIV_NEG_TITLE'];
		$retStr = "<span style='float:right;cursor:pointer;' onclick=\"document.getElementById('list_moded').style.display = 'none';\"><img src='engine/grafts/$siteSettings[graft]/images/close.png' alt=''/></span><div style='font-size:1.5em;margin-bottom:8px;'>$title</div><div style='clear:both;'></div>";

		$sens = ">";
		if ($modtype == "2" || $modtype == "4")
			$sens = "<";
			
		$posttype = "AND p.posttype < 3 ";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$posttype = "";

		$user = mf_query("SELECT username, sig, avatar, rating AS userRating FROM users WHERE ID = '$userID' LIMIT 1");
		$user = mysql_fetch_assoc($user);

		$sav_postID = "";

		if ($modtype == "1" || $modtype == "2") {
			$query=mf_query("SELECT postratings.ID FROM postratings WHERE postratings.user = \"$user[username]\" AND postratings.rating $sens 0 AND postratings.threadID IS NOT NULL");
			$num_rows = mysql_num_rows($query);
			$query=mf_query("SELECT 
											t.user, t.userID, t.category, t.teamID, t.blog, t.spoiler, t.poll, t.title AS t_title, t.body, t.date, t.rating, t.ID,
											postratings.rating AS user_p_rated, postratings.modeddate, postratings.user AS moduser,
											u.ID AS Expr1, u.username, u.sig, u.avatar, u.rating AS userRating
										FROM postratings 
										JOIN forum_topics AS t ON (t.pthread = 0 AND t.threadtype < 3 AND postratings.threadID = t.ID)
										JOIN users AS u ON (u.ID = postratings.modeduserID)
										WHERE postratings.user = \"$user[username]\" AND postratings.rating $sens 0 AND postratings.threadID IS NOT NULL
										ORDER BY postratings.threadID DESC $limit");
		}
		else if ($modtype == "3" || $modtype == "4") {
			$query=mf_query("SELECT postratings.ID FROM postratings WHERE postratings.modeduserID = '$userID' AND postratings.rating $sens 0 AND postratings.threadID IS NOT NULL");
			$num_rows = mysql_num_rows($query);
			$query=mf_query("SELECT 
											t.user, t.userID, t.category, t.teamID, t.blog, t.spoiler, t.poll, t.title AS t_title, t.body, t.date, t.rating, t.ID,
											postratings.rating AS user_p_rated, postratings.modeddate, postratings.user AS moduser
										FROM postratings 
										JOIN forum_topics AS t ON (t.pthread = 0 AND t.threadtype < 3 AND postratings.threadID = t.ID)
										WHERE postratings.modeduserID = '$userID' AND postratings.rating $sens 0 AND postratings.threadID IS NOT NULL
										ORDER BY postratings.threadID DESC $limit");
		}
		$page_lists = "";
		if ($num_rows > 10) {
			$numPages = ceil($num_rows / 10);
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
				$pagprec = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedthreads('$userID','$modtype','$prev_page');\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt='' />$LANG[PREVIOUS_PAGE]</span>";
			if ($pc > 10)
				$pagprec10 = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedthreads('$userID','$modtype','$prev_page10');\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt='- 10' />-10 $LANG[PAGES2] </span>";
			if ($pc > 100)
				$pagprec100 = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedthreads('$userID','$modtype','$prev_page100');\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt='-100' />-100 $LANG[PAGES2] </span>";

			if ($pc < $numPages)
				$pagsuiv = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedthreads('$userID','$modtype','$next_page');\">$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></span>";
			if ($pc < ($numPages + 10))
				$pagsuiv10 = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedthreads('$userID','$modtype','$next_page10');\">+10 $LANG[PAGES2] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt='+10' /></span>";
			if ($pc < ($numPages + 100))
				$pagsuiv100 = "<span class='button_mini' style='margin-left:5px;vertical-align: middle;' onclick=\"list_modedthreads('$userID','$modtype','$next_page100');\">+100 $LANG[PAGES2] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt='+100' /></span>";
			$page_lists .= "<div style='padding-top:6px;padding-bottom:6px;'>".$pagprec100." ".$pagprec10." ".$pagprec."&nbsp; $LANG[PAGE] $pc / $numPages &nbsp;";
			$page_lists .= $pagsuiv." ".$pagsuiv10." ".$pagsuiv100."</div>";
		}
		$retStr .= $page_lists."<div style='border-bottom:1px dashed silver;margin-bottom:8px;'></div>";
		while ($row=mysql_fetch_assoc($query)) {
			$userblog=$row['user'];
			$teamID=$row['teamID'];
			$row['threadID'] = $row['ID'];
			$row['notes'] = "";
			$row['posttype'] = "1";
			$pthread="0";
			$categoryID=$row['category'];
			if ($modtype == "3" || $modtype == "4") {
				$row['Expr1'] = $userID;
				$row['username'] = $user['username'];
				$row['sig'] = $user['sig'];
				$row['avatar'] = $user['avatar'];
				$row['userRating'] = $user['userRating'];
			}

			$lastPostTimeStamp = 0;
			$cur = 2;

			if ($row['ID'] != $sav_postID) {
				$sav_postID = $row['ID'];
				$retStr .= "<div>";

				$blog = "";
				if ($row['blog'] == 1) {
					$blog = "<span class='blogNotification'>";
					$blog .= "<a href='".make_link("blog")."'>";
					$blog .= "[".$LANG['BLOG']."]</a></span>&nbsp;";
				}
				if ($row['blog'] == 2)
					$blog = "<span class='blogNotification'>
						<a href=\"".make_link("blog","&amp;action=g_user&amp;user=$row[user]")."\">
						[".$LANG['BLOG']."]</a></span>&nbsp;";
				$privateteam = "";
				if ($row['teamID'] > 0) {
					$privateteam = "<span class='spoilerNotification'>[ ".team_name($row['teamID'])." ]</span>";
				}
				$poll = "";
				if ($row['poll'] > 0)
					$poll = "<span class='pollNotification'>[".$LANG['POLL']."]</span>";
				$spoiler = "";
				if ($row['spoiler'] > 0)
					$spoiler = "<span class='spoilerNotification'>[".$LANG['SPOILER']."]</span>";

				$retStr .= display_thread_rating($row['threadID'],$row['rating'],$row['user_p_rated']);
				$retStr .= "<div style='font-size:1.2em;font-family:Tahoma,Verdana,arial,helvetica;margin-bottom:3px;'>".$blog;
				$retStr .= $privateteam;
				$retStr .= $poll;
				$retStr .= $spoiler;
				$retStr .= " <span class='bold'><a href='".make_link("forum","&action=g_reply&ID=$row[threadID]","#thread/$row[threadID]/1")."'>$row[t_title]<a></span></div>";
				
				$retStr .= "<div style='margin-bottom:3px;'>".display_thread_moderation($row['threadID'],true)."</div>";

				$thisContentObj = New contentObj;
				$contentArray[0] = assemblePost($thisContentObj, $row, $cur, -1, $pthread, $row['blog'], $userblog, $teamID, $row['threadID'],'',$categoryID,$row['threadID']);
				$retStr .= renderPost($contentArray, 0, false,true);
				$retStr .= "</div>";
			}
		}

		return $retStr;
	}

	function ajax_check_favorites($timeAgo) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $LANG;
		global $verifyEditDelete;

		if ($CURRENTUSER != "anonymous" && is_numeric($timeAgo)) {
			$last_post_date = "last_post_date";
			if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
				$last_post_date = "last_post_date_T";
			$threads = "";
			$timeAgo_noDuplicate = $timeAgo - 29;
			$query = mf_query("SELECT fhits.threadID, forum_topics.title 
							FROM fhits JOIN forum_topics ON fhits.threadID = forum_topics.ID 
							WHERE 
								fhits.userID = '$CURRENTUSERID' 
								AND fhits.date < '$timeAgo' 
								AND fhits.subscribed = '1' 
								AND fhits.notifiedDate < '$timeAgo_noDuplicate' 
								AND forum_topics.$last_post_date > '$timeAgo'");
			while ($row = mysql_fetch_assoc($query)) {
				mf_query("UPDATE fhits SET notifiedDate = '$timeAgo' WHERE userID = '$CURRENTUSERID' AND threadID = '$row[threadID]'");
				$threads .= "@@:.cn.:@@".$row['threadID']."@@::@@".$row['title'];
			}
			return time().$threads;
		}
	}

	function ajax_check_pt($timeAgo) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $LANG;

		if ($CURRENTUSER != "anonymous" && is_numeric($timeAgo)) {
			$threads = "";
			$query = mf_query("SELECT fhits.threadID, forum_topics.title FROM fhits JOIN forum_topics ON fhits.threadID = forum_topics.ID WHERE fhits.userID = '$CURRENTUSERID' AND fhits.date = '0' AND fhits.addedDate > '$timeAgo' AND forum_topics.pthread = '1'");
			while ($row = mysql_fetch_assoc($query)) {
				$threads .= $row['threadID']."@@::@@".$row['title']."@@:.cnp.:@@";
			}
			return time()."@@:.cnp.:@@".$threads;
		}
	}

	function ajax_postRefresh($dataLine) {
		global $siteSettings;
		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $CURRENTUSERID;
		global $verifyEditDelete;
		global $LANG;
		$retStr = "";
		$dataLineArray = explode("::@@pr@@::", $dataLine);

		$TimeAgo = make_num_safe($dataLineArray[0]);
		$tid = make_num_safe($dataLineArray[1]);
		$firstpost = make_num_safe($dataLineArray[2]);
		$lastrefresh = make_num_safe($dataLineArray[3]);

		$query=mf_query("SELECT
			forum_posts.ID, forum_posts.body, forum_posts.rating, forum_posts.notes, forum_posts.posttype, forum_posts.depubBy, forum_posts.depubDate 
			FROM forum_posts_history 
			JOIN forum_posts ON forum_posts.ID = forum_posts_history.postID 
			WHERE 
				forum_posts_history.threadID = '$tid' 
				AND forum_posts_history.date >= '$lastrefresh' 
				AND forum_posts.date >= '$firstpost' 
				AND forum_posts.date <= '$TimeAgo'
			");
		
		while ($row=mysql_fetch_assoc($query)) {
			$body = $row['body'];
			$body = str_replace("\'", "'", $body);
			$body = str_replace("\&quot;", "&quot;", $body);
			$body = format_quickquote($body, $CURRENTUSER, $row['ID']);
			$body = format_post($body, false, $tid, $row['ID']);
			if ($verifyEditDelete || isInGroup($CURRENTUSER, "modo")) {
				if ($row['posttype'] == "3") {
					$depub = "<span style='padding:3px; background-color: #FFFF00;'><b>$LANG[DEPUBLISHED_POST]";
					if ($row['depubDate'])
						$depub .= " $LANG[BY] $row[depubBy] $LANG[AT] ".date($LANG['TIMEFORMAT'],$row['depubDate'])." $LANG[ON] ".date($LANG['DATEFORMAT'],$row['depubDate']);
					$body = $depub."</b></span><div style='border-style: solid; border-color: #FFFF00;'>$body</div>";
				}
				if ($row['posttype'] == "4")
					$body = "<span style='padding:3px; background-color: #FF00FF;'><b>$LANG[DELETED_BY_CREATOR]</b></span>
							<div style='border-style: solid; border-color: #FF00FF;'>$body</div>";
				$row['posttype'] = "2";
			}

			$sig = "<div class='sig'><br/><div id='postsig$row[ID]'></div>";
			$sig .= "<br/><div id='postnotes$row[ID]' class='postnotes'>";
			if ($row['notes'] != "") {
				$deblongnote = "";
				$finlongnote = "";
				if (strlen($row['notes']) > 100) {
					$deblongnote = "<span onclick=\"toggleLayer('hiddenpostnotes".$row['ID'] ."');\" class='jl'>
						$LANG[EDIT_HISTORY]</span>
						<div id='hiddenpostnotes". $row['ID'] ."' style='display:none'>";
					$finlongnote = "</div>";
				}
			}
			$sig .= $deblongnote.$row['notes'].$finlongnote."</div>";
			$sig .= "<div id='postwhorated$row[ID]'></div></div>";

			$retStr .= $row['ID']."::@@::".$body.$sig."::@@::".$row['posttype']."::@p@::";
		
		}
		if ($retStr)
			$retStr = time()."::@p@::".$retStr;
		return $retStr;
	}

	function ajax_modRefresh($dataLine) {
		global $siteSettings;
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTUSERDTP;
		global $verifyEditDelete;
		$retStr = "";
		$dataLineArray = explode("::@@pr@@::", $dataLine);

		$TimeAgo = make_num_safe($dataLineArray[0]);
		$tid = make_num_safe($dataLineArray[1]);
		$firstpost = make_num_safe($dataLineArray[2]);
		$lastrefresh = make_num_safe($dataLineArray[3]);

		$query=mf_query("SELECT
			forum_posts.ID, forum_posts.rating 
			FROM forum_posts 
			JOIN postratings ON forum_posts.ID = postratings.postID 
			WHERE 
				forum_posts.threadID = '$tid' 
				AND postratings.modeddate >= '$lastrefresh' 
				AND forum_posts.date >= '$firstpost' 
				AND forum_posts.date <= '$TimeAgo'
			");
		
		while ($row=mysql_fetch_assoc($query)) {
			$hidden = "";
			if ($row['rating'] < $CURRENTUSERDTP)
				$hidden = "1";
			$rating = $row['rating'];
			$row['rating'] = number_format($row['rating'], 2);
			$postRatingColorGradient = "postRatingColorGradient1";
			if ($row['rating'] > 0) {
				$rating = "+".$row['rating'];
				$postRatingColorGradient = "postRatingColorGradient2";
			}
			else if ($row['rating'] < 0) {
				$rating = $row['rating'];
				$postRatingColorGradient = "postRatingColorGradient3";
			}
			$whorated = whorated($row['ID']);
			$retStr .= $row['ID']."::@@::".$row['rating']."::@@::".$rating."::@@::".$postRatingColorGradient."::@@::".$whorated."::@@::".$hidden."::@p@::";
		
		}
		if ($retStr)
			$retStr = time()."::@p@::".$retStr;
		return $retStr;
	}

	function ajax_submitPost($dataLine) {
		global $CURRENTUSERPPP;
		global $CURRENTUSERAJAX;
		global $siteSettings;
		global $verifyEditDelete;
		global $CURRENTUSER;

		$dataLineArray = explode("__lineDlm__", $dataLine);
		$dataLineArray[2] = make_num_safe($dataLineArray[2]);
		$dataLineArray[1] = make_num_safe($dataLineArray[1]);
		$dataLineArray[0] = utf8_encode($dataLineArray[0]);
		$dataLineArray[0] = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $dataLineArray[0]);
		$dataLineArray[0] = html_entity_decode($dataLineArray[0], ENT_NOQUOTES, 'UTF-8');
		$dataLineArray[0] = str_replace("::@plus@::","+",$dataLineArray[0]);
		$dataLineArray[0] = str_replace("::@euro@::","€",$dataLineArray[0]);
		$dataLineArray[0] = preformat_body($dataLineArray[0]);
		submitPostToDB($dataLineArray[0], $dataLineArray[2], $dataLineArray[1]);

		$numcom = "num_comments";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$numcom = "num_comments_T";

		if ($dataLineArray[3] == "0") {
			$lastPage = mf_query("SELECT $numcom FROM forum_topics WHERE ID=$dataLineArray[2]");
			$lastPage = mysql_fetch_assoc($lastPage);
			$lastPage = $lastPage[$numcom];
			$lastPage = ceil($lastPage / $CURRENTUSERPPP);

			if ($CURRENTUSERAJAX)
				$retstr = $dataLineArray[2].":!@:0:!@:".$lastPage.":!@:1";
			else
				$retstr = make_link("forum","&action=g_reply&ID=$dataLineArray[2]&page=$lastPage").":!@:reload";
		}
		else
			$retstr = $dataLineArray[2];

		return $retstr;		
	}
	
	function ajax_returnLastPost($dataLine) {
		global $siteSettings;
		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $CURRENTUSERID;
		global $LANG;
		global $verifyEditDelete;
		global $CURRENTUSERRULES;

		$dataLine = explode(":!@:", $dataLine);

		if (!is_numeric($dataLine[0]))
			exit();

		$numcom = "num_comments";
		$last_post_id = "last_post_id";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo')) {
			$numcom = "num_comments_T";
			$last_post_id = "last_post_id_T";
		}

		$posttype = "AND posttype < 3 ";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$posttype = "";

		// get post contents
		$childRS = mf_query("SELECT 
								forum_posts.body,
								forum_posts.ID,
								forum_posts.user,
								forum_posts.threadID
								FROM forum_posts WHERE threadID='$dataLine[0]' $posttype
								order by ID desc limit 0,1");

		$num_comments = mf_query("SELECT $numcom, pthread, locked, creator_locked, userID, $last_post_id AS last_post_id FROM forum_topics WHERE ID='$dataLine[0]' LIMIT 1");
		if (!$num_comments = mysql_fetch_assoc($num_comments))
			exit();

		$checkHits = mf_query("SELECT userID, subscribed FROM fhits WHERE userID='$CURRENTUSERID' and threadID='$dataLine[0]' LIMIT 1");
		$checkHits = mysql_fetch_assoc($checkHits);
		if (!$checkHits['userID'] && $num_comments['pthread'] == "1")
			exit($num_comments['pthread']);

		if ($dataLine[1] == "true" && $CURRENTUSER != "anonymous") {
			$subscribed = "";
			if ($checkHits['userID'] == $CURRENTUSERID) {
				if ($checkHits['subscribed'] == 1)
					$subscribed = ", subscribed = '2'";
				mf_query("UPDATE fhits SET date=".time().", num_posts='$num_comments[$numcom]' $subscribed WHERE userID='$CURRENTUSERID' and threadID='$dataLine[0]' LIMIT 1");
			}
			else {
				if ($num_comments['pthread'] == "1")
					exit();
				else
					mf_query("INSERT INTO fhits (threadID, date, userID, num_posts) VALUES ($dataLine[0], ".time().", $CURRENTUSERID, $num_comments[$numcom])");
			}
		}
								
		if ($row = mysql_fetch_assoc($childRS)) {
			if ($num_comments['last_post_id'] == $row['ID']) {
				if ($CURRENTUSER != "anonymous" && $dataLine[1] == "true")
					mf_query("UPDATE forum_topics SET num_views = num_views + 1 WHERE ID='$dataLine[0]' LIMIT 1");
				else if ($CURRENTUSER == "anonymous" && $num_comments['pthread'] != "1") {
					global $CURRENTUSERIP;
					if ($CURRENTUSERIP) {
						$checkHits = mf_query("SELECT ID FROM fhits_anonymous WHERE IP='$CURRENTUSERIP' and threadID='$dataLine[0]' LIMIT 1");
						if (mysql_num_rows($checkHits) == 0) {
							mf_query("INSERT INTO fhits_anonymous (threadID, IP) VALUES ($dataLine[0], '$CURRENTUSERIP')");
							mf_query("UPDATE forum_topics SET num_views = num_views + 1 WHERE ID='$dataLine[0]' LIMIT 1");
						}
					}
				}
			}

			$formatted = $row['body'];
			$formatted = format_quickquote($formatted , $row['user'], $row['ID']);
			$retStr = format_post($formatted, false, $dataLine[0],$row['ID']);
			if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && ($CURRENTUSERRULES == "1" || !$siteSettings['rules']) && ($num_comments['creator_locked'] == '0' || $num_comments['userID'] == $CURRENTUSERID))
				$retStr .= "<div class='lastPostReply'><span onclick=\"toggleLayer('quickReply');\" class='jl'>$LANG[QUICKREPLY]</span></div>
						<div id='quickReply' style='display: none;'>
						<form name='replyForm' id='replyForm' onsubmit=\"return submitQuickPost($row[threadID]);\" action='index.php?shard=forum&amp;action=proc_reply' method='post'>
						<textarea id='postAreaQuick' class='bodyinput' name='message' rows='5' cols='65' style='font-size:12px;'></textarea><br/>
						<input name='replySubmit' type='submit' value=\"$LANG[SUBMIT]\" class='button' style='margin-top: 2px;' /></form></div>";

		}
		else {
			exit();
		}

		$retStr .= "::arrdlm::".$CURRENTUSERID."::arrdlm::".$dataLine[0];
		return $retStr;
	}

	function ajax_updateMod($dataLine) {
		global $CURRENTUSER, $CURRENTUSERRATING, $CURRENTSTATUS, $CURRENTUSERID, $CURRENTUSERRULES, $LANG, $siteSettings;
		
		if ($CURRENTUSER == "anonymous" or $CURRENTSTATUS == "banned")
			exit();

		if ($CURRENTUSERRULES != "1" and $siteSettings['rules'])
			exit();
		
		$dataLineArray = explode(":", $dataLine);
		$location = "forum_topics";
		if ($dataLineArray[1] == "post")
			$location = "forum_posts";
		else
			$dataLineArray[1] = "thread";

		if (is_numeric($dataLineArray[0])) {
			$checkPrevious = mf_query("SELECT count(ID) as Expr1 FROM postratings WHERE user=\"$CURRENTUSER\" and ".$dataLineArray[1]."ID=$dataLineArray[0] LIMIT 1");
			$checkPrevious = mysql_fetch_assoc($checkPrevious);
				
			if ($checkPrevious['Expr1'] == 0) {
				$amountToMod = mf_query("SELECT rating FROM users WHERE ID=$CURRENTUSERID LIMIT 1");
				$amountToMod = mysql_fetch_assoc($amountToMod);				
				
				if ($location == "forum_posts") {
					$threadIDRS = mf_query("SELECT userID, threadID FROM $location WHERE ID=$dataLineArray[0] LIMIT 1");
					$threadIDRS = mysql_fetch_assoc($threadIDRS);
					$usertomod = $threadIDRS['userID'];
					$threadcat = mf_query("SELECT category, pthread, locked FROM forum_topics WHERE ID=$threadIDRS[threadID]  LIMIT 1");
					$threadcat = mysql_fetch_assoc($threadcat);
				}
				else {
					$threadcat = mf_query("SELECT category, userID, pthread, locked FROM forum_topics WHERE ID=$dataLineArray[0] LIMIT 1");
					$threadcat = mysql_fetch_assoc($threadcat);
					$usertomod = $threadcat['userID'];
					$amtStr = number_format($amountToMod['rating'], 4);
					
					if ($dataLineArray[2] == "uparrow")
						$amtStr = "[b]+".$amtStr."[/b]";
					else
						$amtStr = "[b]-".$amtStr."[/b]";
				}	

				$not_nri = mf_query("SELECT not_nri FROM categories WHERE ID='$threadcat[category]' LIMIT 1");
				$not_nri = mysql_fetch_assoc($not_nri);

				if ($threadcat['pthread'] == 1)
					$amountToMod['rating'] = 0;

				$modeddate = time();
				if ($dataLineArray[2] == "uparrow") {
					mf_query("UPDATE $location SET rating = rating + $amountToMod[rating] WHERE ID=$dataLineArray[0] LIMIT 1");
					mf_query("INSERT INTO postratings (user, ".$dataLineArray[1]."ID, rating, modeduserID, modeddate) VALUES (\"$CURRENTUSER\", $dataLineArray[0], $amountToMod[rating], $usertomod, $modeddate)");

					if ($threadcat['pthread'] == 1 || $threadcat['locked'] == 1 || $not_nri['not_nri'] == "checked")
						$amountToMod['rating'] = 0;

					// Update receiving user's cumulative post ratings
					mf_query("UPDATE forum_user_nri SET cum_post_rating = (cum_post_rating + $amountToMod[rating]), num_received_posmods = num_received_posmods + 1 WHERE userID='$usertomod' LIMIT 1");		

					// Update applying user's cumulative moderations
					mf_query("UPDATE forum_user_nri SET num_mods = (num_mods + 1), num_posmods = num_posmods + 1 WHERE userID='$CURRENTUSERID' LIMIT 1");

				}
				else if ($dataLineArray[2] == "downarrow") {
					mf_query("INSERT INTO postratings (user, ".$dataLineArray[1]."ID, rating, modeduserID, modeddate) VALUES (\"$CURRENTUSER\", $dataLineArray[0], -$amountToMod[rating], $usertomod, $modeddate)");
					mf_query("UPDATE $location SET rating = rating - $amountToMod[rating] WHERE ID=$dataLineArray[0] LIMIT 1");
					
					if ($threadcat['pthread'] == 1 || $threadcat['locked'] == 1 || $not_nri['not_nri'] == "checked")
						$amountToMod['rating'] = 0;

					// Update receiving user's cumulative post ratings
					mf_query("UPDATE forum_user_nri SET cum_post_rating = (cum_post_rating - $amountToMod[rating]), num_received_negmods = num_received_negmods + 1 WHERE userID='$usertomod'");		

					// Update applying user's cumulative moderations
					mf_query("UPDATE forum_user_nri SET num_mods = (num_mods + 1), num_negmods = num_negmods + 1 WHERE userID='$CURRENTUSERID'");

				}
			}
		}
		
		return 1;
	}
	
	function ajax_resetThreadList($dataLine) {
		global $CURRENTUSER;
		global $LANG;		
		global $siteSettings;
		global $CURRENTUSERID;
		global $verifyEditDelete;

		$dataLineArray = explode(":@@:", $dataLine);

		$timestamp = time();
		// Are there new posts?
		$searchterm = $dataLineArray[0];
		$teamID = make_var_safe($dataLineArray[1]);
		$filter = make_var_safe($dataLineArray[2]);
		$page = "";
		if (array_key_exists(3, $dataLineArray))
			$page = make_var_safe($dataLineArray[3]);
		$channels = "";
		if (array_key_exists(4, $dataLineArray))
			$channels = str_replace(",", "/", make_var_safe($dataLineArray[4]));
		$tags = "";
		if (array_key_exists(5, $dataLineArray)) {
			$tags = $dataLineArray[5];
			$tags = utf8_encode($tags);
			$tags = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $tags);
			$tags = html_entity_decode($tags, ENT_NOQUOTES, 'UTF-8');
			$tags = str_replace("::@plus@::","+",$tags);
			$tags = str_replace("::@euro@::","€",$tags);
		}
		$newPostsStrContent = "";
		$newPostsList  = "";

		$returnStr = "false";

		$numcom = "num_comments";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$numcom = "num_comments_T";

		$forums = mf_query(generateForumStr(0,false,$searchterm,$filter,$page,$channels,$tags,$teamID));

		$team_links = "";
		if ($teamID) {
			$verifyuser = mf_query("SELECT userID FROM teams_users WHERE userID = '$CURRENTUSERID' AND teamID = '$teamID' AND level < 3 LIMIT 1");
			if ($verifyuser = mysql_fetch_assoc($verifyuser)) {
				$virg = "";
				$teammembers = "";
				$members = mf_query("SELECT teams_users.userID, users.username FROM teams_users JOIN users ON users.ID = teams_users.userID WHERE teams_users.teamID = '$teamID' AND teams_users.level < 3");
				while ($row = mysql_fetch_assoc($members)) {
					$teammembers .= $virg.$row['username'];
					$virg = ",";
				}
	
				$team_links = "<div style='margin-left:6px;margin-top:3px;margin-bottom:4px;'>";
				$team_links .= "<div style='display:inline-block;'><form name='new_team_thread' action='".make_link("forum","&amp;action=g_crt_new")."' method='post'><input type='hidden' name='teamID' value='$teamID' /><input type='hidden' name='toList' value=\"$teammembers\" /><input type='submit' class='button' style='padding-top:1px;padding-bottom:1px;'value=\"$LANG[TEAM_NEW_THREAD]\" /></form></div>";
				if (isInTeam($teamID,$CURRENTUSERID) == "1")
					$team_links .= "&nbsp;&nbsp;<a href='".make_link("teams","&amp;action=g_addThread&amp;teamID=$teamID")."' class='button'>$LANG[TEAM_ADD_EXISTING_THREAD]</a>";
				$team_links .= "&nbsp;&nbsp;<a href='".make_link("teams","&amp;action=g_files&amp;teamID=$teamID")."' class='button'>
			<img src='engine/grafts/$siteSettings[graft]/images/folder.png' border='0' style='vertical-align:middle;' alt='folder' /> $LANG[TEAM_FILE_MANAGER]</a></div>";
			}
		}
		$totnewPosts = 0;
		$list_tags = "";
		if (mysql_num_rows($forums) > 0) {
			$newPostsList = "";
			$virg = "";
			$array_tags = "";
			while ($row = mysql_fetch_assoc($forums)) {
				if ($returnStr == "false")
					$returnStr = "";

				if ($row['num_new'] > 0) {
					$totnewPosts += $row['num_new'];
					$newPostsList .= $virg.$row['ID'];
					$virg = ",";
				}

				$returnStr .= assembleThread($row);
				if ($teamID)
					$returnStr .= "<span class='button_mini' style='margin-left:50px'>
						<a href='".make_link("teams","&amp;action=g_files&amp;threadID=$row[ID]&amp;teamID=$teamID")."'>
						$LANG[TEAM_UPLOADFILE]</a></span><br/><br/>";
				
				if (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus") {
					$query_tags = mf_query("SELECT tag FROM forum_tags WHERE threadID='$row[ID]'");
					while ($row_tags = mysql_fetch_assoc($query_tags)) {
						if (isset($array_tags[$row_tags['tag']]))
							$array_tags[$row_tags['tag']] ++;
						else
							$array_tags[$row_tags['tag']] = 1;
					}
				}
			}
			if (sizeof($array_tags) > 1 && (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus")) {
				arsort($array_tags);
				$tag_size = 1.6;
				$retstr = "";
				$total_use = 0;
				$tag_ital = "normal";
				$i_tag = 0;
				foreach ($array_tags as $tag_name => $tag_nb) {
					if ($total_use != $tag_nb && $tag_size > 0.81) {
						$tag_size = $tag_size - 0.2;
						$total_use = $tag_nb;
					}
					if ($tag_nb == 3 && $tag_size > 1.2)
						$tag_size  = 1.2;
					if ($tag_nb == 2 && $tag_size > 1)
						$tag_size  = 1.0;
					if ($tag_nb == 1)
						$tag_size  = 0.8;
					if ($tag_ital == "italic")
						$tag_ital = "normal";
					else
						$tag_ital = "italic";
					$list_tags .= "<div id='tag_$i_tag' onclick=\"view_onetag('$i_tag');\" class='tag_header' style='font-size:".$tag_size."em;font-style:".$tag_ital.";'>$tag_name</div>";
				$i_tag ++;
				}
			}

			$PagesString = getPagesString($searchterm,$filter,$page,$channels,$tags,$teamID);
		}
		$newPostsStrContent = "";
		if ($CURRENTUSER != "anonymous") {
			$newpost_one = "none;";
			$newpost_multi = "none;";
			if ($totnewPosts > 1)
				$newpost_multi = "inline-block;";
			else if ($totnewPosts == 1)
				$newpost_one = "inline-block;";
			else
				$totnewPosts = "";
			$newPostsStrContent = " &nbsp; <span id='numpostu' class='bold'>$totnewPosts</span> 
					<div id='newPostsStr_one' style='display:$newpost_one'>
						$LANG[NEW_POSTS] 
						<span onclick=\"markall('$CURRENTUSERID');\" title=\"$LANG[MARK_AS_READ1]\" class='jl' style='font-size:0.8em;'>
						($LANG[MARK_AS_READ1])</span>
					</div>
					<div id='newPostsStr_multi' style='display:$newpost_multi'>
						$LANG[NEW_POSTS] 
						<span onclick=\"markall('$CURRENTUSERID');\" title=\"$LANG[MARK_AS_READ]\" class='jl' style='font-size:0.8em;'>
						($LANG[MARK_AS_READ])</span>
					</div>";

		}
		else {
			$newPostsStrContent = " &nbsp; <span id='numpostu' class='bold'></span> 
					<div id='newPostsStr_one' style='display:none;'></div>
					<div id='newPostsStr_multi' style='display:none;'></div>";
		}
		
		if ($returnStr != "false")
			$returnStr = $team_links.$returnStr;

		$channelMaintain = "";
		if (array_key_exists('channel', $_REQUEST)) {
			$chan = make_num_safe( $_REQUEST['channel']);
			$channelMaintain = "&amp;channel=$chan";
		}

		$numrows = mysql_num_rows($forums);
		if ($returnStr == "false") {
			$returnStr = $team_links."<br/><br/><b>$LANG[NO_THREADS]</b><div style='padding-top: 210px;'></div>";
			$PagesString = "<table><tr><td><div class=\"pageCountLeft\">$LANG[PAGES]: 0</div></td></tr></table>";
		}
		else if ($numrows < 5) {
			if ($numrows == 1) $spaceheight = 193;
			if ($numrows == 2) $spaceheight = 142;
			if ($numrows == 3) $spaceheight = 91;
			if ($numrows == 4) $spaceheight = 40;
			$returnStr .= "<div style='padding-top: ".$spaceheight."px;'></div>";
		}

		$returnStr = $returnStr."::arrdlm::".$PagesString;
		$returnStr .= "::arrdlm::".$newPostsStrContent;
		$returnStr .= "::arrdlm::".$newPostsList . "::arrdlm::" . $CURRENTUSERID;
//		if ($searchterm)
		$returnStr .= "::arrdlm::". $searchterm . "::arrdlm::". $teamID."::arrdlm::".$list_tags."::arrdlm::".$timestamp;

		return $returnStr;
	}
	
	function ajax_callNewThreadForm($chan="") {

		if (!is_numeric($chan))
			$chan = "";

		global $LANG;
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTSTATUS;
		global $CURRENTUSERRULES;
		global $siteSettings;
		global $verifyBlogger;
		global $verifyEditDelete;
		global $CURRENTUSERINTEAM;

		$returnStr = "";
		
		 if ($CURRENTUSER == 'anonymous' || $CURRENTSTATUS == "banned" || $CURRENTUSER == "bot" || ($CURRENTUSERRULES != "1" && $siteSettings['rules']))
			$returnStr = "$LANG[ACTION_NOT_AVAILABLE].";
		 else {

			$teamID = "";
			if (array_key_exists("teamID", $_POST)) {
				$teamID = make_var_safe($_POST['teamID']);
				$teamName = mf_query("SELECT teamName FROM teams WHERE teamID = '$teamID' LIMIT 1");
				if ($teamName = mysql_fetch_assoc($teamName))
					$teamName = $teamName['teamName'];
				else
					$teamID = "";
			}

			$pc = "<div style='font-weight:bold;font-size:1.5em;'>";
			if (!$teamID)
				$pc .="$LANG[CREATE_NEW_THREAD]";
			else
				$pc .="$LANG[CREATE_TEAM_NEW_THREAD] \"$teamName\"";
			$pc .= "</div><form name='thread_form' id='replyForm' action='index.php?shard=forum&amp;action=proc_new' method='post'>
				<input type='hidden' name='shardname' value='$_REQUEST[shard]' />
				<br/>";
			if ($teamID)
				$pc .= "<input type='hidden' value='$teamID' name='teamID' />";

			// Private thread
			$toList = "";
			if (array_key_exists("toList", $_POST))
				$toList = make_var_safe($_POST['toList']);
			else if (array_key_exists("toList", $_REQUEST))
				$toList = make_var_safe($_REQUEST['toList']);
			if ($_REQUEST['shard'] != "blog") {
				$pthreadChecked = "";
				$toListVisible = "none";
				if ($toList) {
					$pthreadChecked = "checked='checked'";
					$toListVisible = "block;";
				}
				$pc .= "<div><input $pthreadChecked class='controls' type='checkbox' name='pThread' id='pThread' onclick=\"toggleLayer('pThreadParticipants');\" /> $LANG[SET_PTHREAD]?</div>
						<div style='padding: 10px; margin-left: 15px;display:$toListVisible' id='pThreadParticipants'>
							<div style='display:table;'><div class='row'>
								<div class='cell'>$LANG[ADDUSER]:</div>
								<div class='cell'>
									<input type='text' autocomplete='off' style='vertical-align: middle;color:#000000;' size='22' class='bselect' id='userprofilename3' onkeyup=\"input_user(3); return false;\" onfocus=\"show_select_user(3);\" onblur=\"hide_select_user(3);\" />
									<div id='inputSelectUser3' class='user_list'></div>
								</div>
							</div></div>
							<div style='font-size:0.8em;'>$LANG[PARTICIPANTS]: </div>
							<div><input size='100' class='controls' type='text' name='toList' value=\"$toList\" id='toList' /></div>
							<div style='font-size:0.8em;'>$LANG[SEPERATE_USERNAMES]</div>
						</div>";
			}
			// Polls
			$pc .= "<input class='controls' type='checkbox' name='poll' id='poll' onclick=\"toggleLayer('pollOptions');\" /> $LANG[CREATE_POLL]?<br/>
					<div class='pollOptions' id='pollOptions'>
						<div class='gridDataField'>$LANG[POLL_QUESTION]:</div><div class='gridDataField'><input type='text' class='controls' name='pollQuestion' id='pollQuestion' size='60' /></div>
						<div class='clearfix'></div>
						<br/><br/><br/>
						<div class='gridDataField'>$LANG[OPTION] 1:</div><div class='gridDataField'><input type='text' class='controls' size='40' name='pollOption1' id='pollOption1' /></div>
						<div class='clearfix'></div>
						
						<div class='gridDataField'>$LANG[OPTION] 2:</div><div class='gridDataField'><input type='text' class='controls' size='40' name='pollOption2' id='pollOption2' /></div>
						<div class='clearfix'></div>
						
						<div class='gridDataField'>$LANG[OPTION] 3:</div><div class='gridDataField'><input type='text' class='controls' size='40' name='pollOption3' id='pollOption3' /></div>
						<div class='clearfix'></div>
						
						<div class='gridDataField'>$LANG[OPTION] 4:</div><div class='gridDataField'><input type='text' class='controls' size='40' name='pollOption4' id='pollOption4' /></div>
						<div class='clearfix'></div>
						
						<div class='gridDataField'>$LANG[OPTION] 5:</div><div class='gridDataField'><input type='text' class='controls' size='40' name='pollOption5' id='pollOption5' /></div>
						<div class='clearfix'></div>						
						<br/><br/><br/>
						<div class='gridDataField'>$LANG[POLL_TIME]:</div><div class='gridDataField' style='width:400px;'><input type='text' class='controls' size='3' value='0' name='pollDays' id='pollDays' /><small>$LANG[POLL_TIME2]</small></div>
						<div class='clearfix'></div>
					</div>";


			if (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus") {
				$onfocus = "onfocus=\"previewPost('2'); return false;\"";
				$onblur = "onblur=\"previewPost_lostfocus('2'); return false;\"";
			}

			$pc .= "
				<br/> 
				$LANG[TITLE]:
				<br />
				<input size='64' class='controls' type='text' name='title' id='newthreadtitle' />
				<br /><br/>
				$LANG[POST_BODY]:<br/><table><tr><td style='vertical-align: top;'>
				<span id='valid_form' style='display:none;'></span>
				<div><textarea name='message' rows='14' cols='65' id='postArea0' class='post_textarea' $onfocus $onblur ></textarea></div>";
			$pc .= printFormattingPaneB('0');
			$pc .="<div id='previewPostT0' style='margin-top:28px;'></div>
				<div id='previewPost0' class='previewPost2' style='display: none; width: 536px;' onclick=\"StoppreviewPost(2);\"></div>";

		$tags_cache = "";
		$tags_buttons = "";
		$virg = "";
/*		$query = mf_query("SELECT ID,tag FROM forum_tags WHERE threadID = '$id' ORDER BY tag"); 
		while ($row = mysql_fetch_assoc($query)) {
			if ($row['tag']) {
				$tags_cache .= $virg . $row['tag'];
				$virg = ", ";
				$tags_buttons .= "<div id='t_buttag_".$row['ID']."' class='button_tag'><span id='t_seltag_".$row['ID']."' class='selected_tag'>".$row['tag']."</span><span class='deleteButton' onclick=\"t_remove_onetag('".$row['ID']."');\">x</span></div>";
			}
		}*/
		$pc .= "<div></div>$LANG[TAGS]: <div id='t_tags_list' style='display:inline-block;'>$tags_buttons</div>
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

			$catlist = "";
			$channelTags = mf_query("SELECT ID, name FROM categories ORDER BY nb");
			while ($c = mysql_fetch_assoc($channelTags)) {
				if (!$chan && $c['ID'] == $siteSettings['dChannel'])
					$catlist .= "<option selected='selected' value=$c[ID]>$c[name]</option>";
				else if ($chan && $c['ID'] == $chan)
					$catlist .= "<option selected='selected' value=$c[ID]>$c[name]</option>";
				else
					$catlist .= "<option value=$c[ID]>$c[name]</option>";
			}	

			$pc .= "<div style='height:12px;'></div><b>$LANG[CHANNEL_TAG]:</b><br/>
				<select class='bselect' name='channelTag' id='channelTag'>$catlist</select><br/>";

			$pc .= "<br/><input class='controls' type='checkbox' name='creator_locked' /> $LANG[SET_CREATOR_LOCKED]";
			if ($verifyEditDelete || $teamID)
				$pc .= "<br/><input class='controls' type='checkbox' name='sticky' id='toList' /> $LANG[SET_STICKY]";

			$pc .= "<br/><input class='controls' type='checkbox' name='spoiler' id='toList' /> $LANG[SPOILER]";

			$blog_fVisible = "display:none;";
			$blog_Checked = "";
			$blog_pChecked = "";
			if ($_REQUEST['shard'] == "blog") {
				$blog_Checked = "checked='checked'";
				$blog_fVisible = "display:block;";
				if (array_key_exists('userID', $_REQUEST))
					$blog_pChecked = "checked='checked'";
			}
			if ($verifyBlogger) {
				$pc .= "<br/><input $blog_Checked class='controls' type='checkbox' name='blog' onclick=\"toggleLayer('blog_f');\" id='toList' /> $LANG[SET_BLOG]<br/>";
				$pc .= "<div id='blog_f' style='$blog_fVisible'><br/><input $blog_pChecked class='controls' type='checkbox' name='blog_p' style='margin-left:20px' id='toList' /> $LANG[SET_BLOG_PERSO2]<br/>";
				$pc .= "<br/><input class='controls' type='checkbox' name='blog_f' style='margin-left:20px' id='toList' /> $LANG[SET_BLOG_FORUM]<br/></div>";
			}
			else {
				$pc .= "<br/><input $blog_Checked class='controls' type='checkbox' name='blog' onclick=\"toggleLayer('blog_f');\" id='toList' /> $LANG[SET_BLOG_PERSO]<br/>";
				$pc .= "<div id='blog_f' style='$blog_fVisible'><br/><input class='controls' type='checkbox' name='blog_f' style='margin-left:20px' id='toList' /> $LANG[SET_BLOG_FORUM]<br/></div>";
			}

			$pc .= "<br/><br/>
					<span id='sendThread' class='button' onclick=\"validateForm();\">$LANG[SUBMIT]</span>
					<span id='sendThreadDisabled' class='button' style='display:none;color:silver;'>$LANG[SUBMIT]</span>";

			$pc .= "</td><td style='vertical-align: top;'>".printFormattingPane('0')."</div></td></tr></table></form>";


			$returnStr = $pc;
		}
		return $returnStr;
	}

	function ajax_add_new_pthread_user($data) {
		global $LANG;
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTSTATUS;
		global $CURRENTUSERRULES;
		global $siteSettings;
		global $siteSettings;

		if ($CURRENTUSER == "anonymous" or $CURRENTSTATUS == "banned")
			exit();

		if ($CURRENTUSERRULES != "1" and $siteSettings['rules'])
			exit();

		$dataLine = explode(':!@dpu@:', $data);

		$user = urldecode($dataLine[0]);
		$user = utf8_encode($user);
		$user = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $user);
		$user = html_entity_decode($user, ENT_NOQUOTES, 'UTF-8');
		$user = str_replace("::@plus@::","+",$user);
		$user = str_replace("::@euro@::","€",$user);
		$value = make_var_safe($user);
		
		$findUser = mf_query("SELECT ID, pm_alert, email, username, accept_pm_from FROM users WHERE LOWER(username)=\"".mb_strtolower($value,'UTF-8')."\" AND userstatus IS NULL LIMIT 1");
		$findUser = mysql_fetch_assoc($findUser);
		
		if (!is_numeric($findUser['ID']))
			return "false::arrdlm::<div style='text-align:center;'><span style='font-weight:bold;'>$value</span>$LANG[USERNOTFOUND] </div>";
		else if (is_numeric($dataLine[1])) {
			// Check if not a hack
			global $CURRENTUSERID;
			$threadInfo = mf_query("SELECT pthread, title, user FROM forum_topics WHERE ID='$dataLine[1]' LIMIT 1");
			if ($threadInfo = mysql_fetch_assoc($threadInfo)) {
				if ($threadInfo['pthread'] == "1") {
					$checkPthread = mf_query("SELECT userID FROM fhits WHERE userID='$CURRENTUSERID' and threadID='$dataLine[1]' LIMIT 1");
					if (!$checkPthread = mysql_fetch_assoc($checkPthread))
						exit();
				}
			}
		}
		else
			exit();

		if (verify_add_to_pm($CURRENTUSERID,$findUser['ID'],$findUser['accept_pm_from'] )) {
			$fhits = mf_query("SELECT count(threadID) as Expr1 FROM fhits WHERE userID='$findUser[ID]' and threadID='$dataLine[1]' LIMIT 1");
			$fhits = mysql_fetch_assoc($fhits);
			if ($fhits['Expr1'] == 0) {
				$fhits = mf_query("INSERT INTO fhits (threadID, date, userID,addedDate) VALUES ($dataLine[1], 0, $findUser[ID], ".time().")");
				mf_query("INSERT INTO log_user_pthread (threadID, date, username, addedby) VALUES ($dataLine[1], ".time().", \"$findUser[username]\", \"$CURRENTUSER\")");
				if ($findUser['pm_alert'] && $findUser['email']) {

					srand((double)microtime()*1000000);
					$boundary = md5(uniqid(rand()));
					$header ="From: $siteSettings[titlebase] <$siteSettings[alert_mail]>\n";
					$header .="Reply-To: $siteSettings[alert_mail] \n";
					$header .="MIME-Version: 1.0\n";
					$header .="Content-Type: multipart/alternative;boundary=$boundary\n";

					$to = $findUser['email'];
					$subject = "$LANG[PT_ALERT1]: ".$threadInfo['title'];

					$message = "\nThis is a multi-part message in MIME format.";
					$message .="\n--" . $boundary . "\nContent-Type: text/html;charset=\"utf-8\"\n\n";
					$message .= "<html><body>\n";
					$message .="<img src='" . $siteSettings['siteurl'] . "/engine/grafts/" . $siteSettings['graft'] . "/images/MailheaderImage.png'><br/><br/>\n";
					if ($CURRENTUSER != $threadInfo['user'])
						$message .= "\n$CURRENTUSER $LANG[PT_ALERT2] $threadInfo[user]<br/>\n";
					else
						$message .= "\n$CURRENTUSER $LANG[PT_ALERT4]<br/>\n";
					$message .= "\n$LANG[PT_ALERT3]:<br/>\n";
					$message .= "http://".$siteSettings['siteurl']."/".make_link("forum","&action=g_reply&ID=$dataLine[1]","#thread/$dataLine[1]/1")."<br/><br/>\n";
					$message .= "$LANG[DO_NOT_ANSWER]\n";
					$message .="\n--" . $boundary . "--\n end of the multi-part";

					@mail($to, $subject, $message, $header);
				}
				return ("true::arrdlm::".findPthreadUsers($dataLine[1],$threadInfo['user']));
			}
			else
				return ("false::arrdlm::$LANG[ALREADYHASACCESS]");
		}
		else {
			if ($findUser['accept_pm_from'] == "1")
				return ("false::arrdlm::<span style='font-weight:bold;'>$findUser[username]</span> $LANG[PM_ONLY_FROM_FRIENDS_OF]");
			else if ($findUser['accept_pm_from'] == "2")
				return ("false::arrdlm::<span style='font-weight:bold;'>$findUser[username]</span> $LANG[PM_ONLY_FROM_FRIENDS]");
		}
	}			
	
	function ajax_delete_pthread_user($data) {
		global $CURRENTUSER, $CURRENTUSERID, $CURRENTSTATUS, $CURRENTUSERRULES, $siteSettings, $LANG;

		$dataLine = explode(':!@dpu@:', $data);
		if (is_numeric($dataLine[0]) && is_numeric($dataLine[1])) {
		if ($CURRENTUSER == "anonymous" or $CURRENTSTATUS == "banned")
			exit();

		if ($CURRENTUSERRULES != "1" and $siteSettings['rules'])
			exit();

		$usernamemp = mf_query("SELECT username FROM users WHERE ID = '$dataLine[0]' LIMIT 1");
		$usernamemp = mysql_fetch_assoc($usernamemp);
		if ($usernamemp['username'] != $CURRENTUSER && isInGroup($usernamemp['username'], "admin"))
				exit("$LANG[MP_DELETE_ERROR_ADMIN]");

		if (is_numeric($dataLine[0]) && is_numeric($dataLine[1])) {
				$checkThread = mf_query("SELECT user, userID, pthread FROM forum_topics WHERE ID='$dataLine[1]' AND pthread = '1' LIMIT 1");
			if ($checkThread = mysql_fetch_assoc($checkThread)) {
				if ($dataLine[0] != $CURRENTUSERID && $CURRENTUSERID != $checkThread['userID'] && !isInGroup($CURRENTUSER, "admin"))
						exit("$LANG[MP_DELETE_ERROR_NO_RIGHTS]");
					$checkPthread = mf_query("SELECT userID FROM fhits WHERE userID='$CURRENTUSERID' AND threadID='$dataLine[1]' LIMIT 1");
				$checkPthread = mysql_fetch_assoc($checkPthread);
				if (!$checkPthread['userID'])	
						exit("$LANG[MP_DELETE_ERROR_NO_USER1] ".$CURRENTUSERID." $LANG[MP_DELETE_ERROR_NO_USER2]");
			}
			else
					exit("$LANG[MP_DELETE_ERROR_NO_ID]");

				mf_query("DELETE FROM fhits WHERE threadID='$dataLine[1]' AND userID='$dataLine[0]'");
			mf_query("INSERT INTO log_user_pthread (threadID, date, username, removedby) VALUES ($dataLine[1], ".time().", \"$usernamemp[username]\", \"$CURRENTUSER\")");

			return ("true::arrdlm::".findPthreadUsers($dataLine[1],$checkThread['user']));
		}
	}
	}
	
	function ajax_submit_poll_vote($data) {
		global $CURRENTUSER, $CURRENTUSERID;
		global $LANG;
		
		$dataLine = explode(':arrdlm:', $data);
		if (is_numeric($dataLine[0]) && is_numeric($dataLine[1]) && $CURRENTUSER != "anonymous") {
			mf_query("delete FROM poll_responses WHERE poll_ID = $dataLine[0] and user_ID = $CURRENTUSERID LIMIT 1"); 
			mf_query("INSERT INTO poll_responses (poll_ID, answer_ID, user_ID) VALUES ($dataLine[0], $dataLine[1], $CURRENTUSERID)");
			
			return($dataLine[0].':arrdlm:'.$dataLine[1].':arrdlm:'.renderPollResults($dataLine[0]));
			
		}
		else {
			return "$dataLine[0]:arrdlm:".$LANG['POLL_ERROR'];
		}
	}
	
	function ajax_subscribe($ID) {
		global $CURRENTUSER, $CURRENTUSERID, $LANG, $siteSettings;
		if (is_numeric($ID)) {
			if (mf_query("UPDATE fhits SET subscribed=2 WHERE userID='$CURRENTUSERID' AND threadID='$ID' LIMIT 1"))
				return "<span onclick=\"unsubscribe($ID);\" class='button'>$LANG[UNSUBSCRIBE] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' style='vertical-align:middle;' alt=\"$LANG[SUBSCRIBE]\" /></span>::@@UNSUBS@@::<div onclick=\"closelayer();unsubscribe($ID);\" class='contextMenuelement'><div class='contextMenuelementimg'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' alt=\"$LANG[UNSUBSCRIBE]\" /></div><div class='contextMenuelementtxt'>$LANG[UNSUBSCRIBE]</div></div>";
			else
				return mysql_error();
		}
	}

	function ajax_unsubscribe($ID) {
		global $CURRENTUSER, $CURRENTUSERID, $LANG, $siteSettings;
		if (is_numeric($ID)) {
			if (mf_query("UPDATE fhits SET subscribed=0 WHERE userID='$CURRENTUSERID' and threadID='$ID' LIMIT 1"))
				return "<span onclick=\"subscribe($ID);\" class='button'>$LANG[SUBSCRIBE] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' style='vertical-align:middle;' alt=\"$LANG[SUBSCRIBE]\" /></span>::@@UNSUBS@@::<div onclick=\"closelayer();subscribe($ID);\" class='contextMenuelement'><div class='contextMenuelementimg'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' alt=\"$LANG[SUBSCRIBE]\" /></div><div class='contextMenuelementtxt'>$LANG[SUBSCRIBE]</div></div>";
			else
				return mysql_error();
		}
	}	

	function ajax_subscribe2($ID) {
		global $CURRENTUSER, $CURRENTUSERID, $LANG;
		if (is_numeric($ID)) {
			$verif = mf_query("SELECT threadID FROM fhits WHERE userID='$CURRENTUSERID' and threadID='$ID' LIMIT 1");
			if ($verif = mysql_fetch_assoc($verif))
				mf_query("UPDATE fhits SET subscribed=2 WHERE userID='$CURRENTUSERID' and threadID='$ID' LIMIT 1");
			else {
				$verif = mf_query("SELECT pthread FROM forum_topics WHERE ID='$ID' LIMIT 1");
				if ($verif = mysql_fetch_assoc($verif)) {
					if (!$verif['pthread'])
						mf_query("INSERT INTO fhits (subscribed, userID, threadID) VALUES (2, '$CURRENTUSERID', '$ID')");
				}
			}
		}
	}

	function ajax_unsubscribe2($ID)	{
		global $CURRENTUSER, $CURRENTUSERID, $LANG;
		if (is_numeric($ID))
			mf_query("UPDATE fhits SET subscribed=0 WHERE userID='$CURRENTUSERID' and threadID='$ID' LIMIT 1");
	}	

	function ajax_hide($ID) {
		global $CURRENTUSER, $CURRENTUSERID, $LANG, $siteSettings;
		if (is_numeric($ID)) {
			if (mf_query("UPDATE fhits SET subscribed=3 WHERE userID='$CURRENTUSERID' and threadID='$ID' LIMIT 1"))
				return "<span onclick=\"unhide($ID);\" class='button'>$LANG[UNHIDE] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/uparrowoff.gif' style='vertical-align:middle;' alt=\"$LANG[UNHIDE]\" /></span>";
			else
				return mysql_error();
		}
	}	

	function ajax_unhide($ID) {
		global $CURRENTUSER, $CURRENTUSERID, $LANG, $siteSettings;
		if (is_numeric($ID)) {
			if (mf_query("UPDATE fhits SET subscribed=0 WHERE userID='$CURRENTUSERID' and threadID='$ID' LIMIT 1"))
				return "<span onclick=\"hide($ID);\" class='button'>$LANG[HIDE] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/downarrowoff.gif' style='vertical-align:middle;' alt=\"$LANG[HIDE]\" /></span>";
			else
				return mysql_error();
		}
	}

	function ajax_hide2($ID) {
		global $CURRENTUSER, $CURRENTUSERID;
		if (is_numeric($ID)) {
			$verif = mf_query("SELECT threadID FROM fhits WHERE userID='$CURRENTUSERID' and threadID='$ID' LIMIT 1");
			if ($verif = mysql_fetch_assoc($verif))
				mf_query("UPDATE fhits SET subscribed=3 WHERE userID='$CURRENTUSERID' and threadID='$ID' LIMIT 1");
			else {
				$verif = mf_query("SELECT pthread FROM forum_topics WHERE ID='$ID' LIMIT 1");
				if ($verif = mysql_fetch_assoc($verif)) {
					if (!$verif['pthread'])
						mf_query("INSERT INTO fhits (subscribed, userID, threadID) VALUES (3, '$CURRENTUSERID', '$ID')");
				}
			}
		}
	}

	function ajax_unhide2($ID) {
		global $CURRENTUSER, $CURRENTUSERID, $LANG;
		if (is_numeric($ID)) {
			if (mf_query("UPDATE fhits SET subscribed=0 WHERE userID=$CURRENTUSERID and threadID=$ID LIMIT 1"))
				{}
			else
				return mysql_error();
		}
	}
	
	function ajax_updateChannelsList($chan) {
		global $CURRENTUSER, $CURRENTUSERID, $CURRENTUSERFLOOD, $LANG, $siteSettings;
		
		$flood = "";
		if (!$CURRENTUSERFLOOD)
			$flood = "$siteSettings[flood_ID]";

		$channelsList = mf_query("SELECT * FROM categories ORDER BY nb");
		$channelFilteredList = make_var_safe($chan);
		$CFLArray = explode(',', $channelFilteredList);
		$someFiltered = false;
		$numchan = mysql_num_rows($channelsList);
		$i = 0;
		$j = 0;
		$uniquechannel = "";
		$currentChannel = "";
		while ($row = mysql_fetch_assoc($channelsList))	{
			$i ++;
				$isFiltered = false;
			foreach($CFLArray as $CFLID) {
				if ($CFLID == $row['ID']) {
					$isFiltered = true;
					if ($row['ID'] != $flood)
						$someFiltered = true;
				}
			}
			
			$row['name'] = trimNicely($row['name'], 45);
			
			if (!$isFiltered) {
				if ($row['ID'] != $flood)
					$j ++;
				$uniquechannel = trimNicely($row['name'],18);
			}

			if (array_key_exists('channel', $_REQUEST)) {
			if ($_REQUEST['channel'] == $row['ID'])
				$currentChannel = $row['name'];
			}
		}
		
		if ($someFiltered) {
			if ($j == 1)
				$currentChannel = $uniquechannel . $currentChannel;
			else
				$currentChannel = "$LANG[CUSTOM]" . $currentChannel;
		}
		else if (!$currentChannel)  {
			$currentChannel = $LANG['ALL_CHANNELS'];
			$resetchan = "display: none;";	
		}
			
		$currentChannel = trimNicely($currentChannel, 40);
		$currentChannelD = "$currentChannel <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt=\"$LANG[CHANNEL_LIST]\" />";

		return $currentChannelD;
	}	

	function ajax_markAll($newPostsList) {
		global $CURRENTUSER, $CURRENTUSERID, $LANG, $siteSettings;
		global $verifyEditDelete;


		$numcom = "num_comments";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$numcom = "num_comments_T";

		if($newPostsList != "") {
			$newPostsList = explode(",", $newPostsList);
				for ($i=0; $i < sizeof($newPostsList); $i++) {
					if (($newPostsList[$i] != '') && (is_numeric($newPostsList[$i]))) {
						$num_comments = mf_query("SELECT $numcom FROM forum_topics WHERE ID='$newPostsList[$i]' LIMIT 1");
					$num_comments = mysql_fetch_assoc($num_comments);
					$num_comments = $num_comments[$numcom];

						$verif = mf_query("SELECT threadID FROM fhits WHERE userID='$CURRENTUSERID' and threadID='$newPostsList[$i]' LIMIT 1");
						if ($verif = mysql_fetch_assoc($verif))
							mf_query("UPDATE fhits SET date='".time()."', num_posts = '$num_comments' WHERE threadID='$newPostsList[$i]' AND userID=$CURRENTUSERID LIMIT 1");
						else {
							$verif = mf_query("SELECT pthread FROM forum_topics WHERE ID='$newPostsList[$i]' LIMIT 1");
							if ($verif = mysql_fetch_assoc($verif)) {
								if (!$verif['pthread'])
									mf_query("INSERT INTO fhits (threadID, date, userID, num_posts) VALUES ('$newPostsList[$i]', ".time().", $CURRENTUSERID, $num_comments)");
							}
						}
				}
			}
		}

		$waitloadmessage = "<span class='pleasewait'><center><br/><img src='engine/grafts/$siteSettings[graft]/images/ajax-loader.gif' alt=\"$LANG[PLEASE_WAIT]\" /><br/><br/>$LANG[PLEASE_WAIT]<br/><br/></center></span>";

		return "please" . "::cur@lo::" . $waitloadmessage;

	}
	
	function ajax_previewPost($data) {
		$dataLine = explode("::@ppo@::", $data);
		$body = urldecode($dataLine[1]);
		$body = utf8_encode($body);
		$body = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $body);
		$body = html_entity_decode($body, ENT_NOQUOTES, 'UTF-8');

		if (stristr($body, "[qq.")) {
			$body = qq_lookup_preview($body);
		}
	
		$formatted = format_post($body, false);
		$formatted = str_replace("\'", "'", $formatted);
		$formatted = stripslashes($formatted);
	
		return $dataLine[0]."::cur@lo::".$formatted."::cur@lo::".$dataLine[2];
	}

	function ajax_g_reply($dataLine) {
		$return = g_reply($dataLine);
		return $return;
	}

	function ajax_search_posts($dataLine) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTSTATUS;
		global $LANG;
		global $siteSettings;	
		global $verifyEditDelete;
		global $CURRENTUSERDTT;
		global $CURRENTUSERRULES;
		global $CURRENTUSERRATING;
		global $CURRENTUSERAJAX;
		global $CURRENTUSERTEAMINPTHREAD;
		global $CURRENTUSERUNREADPTHREAD;

		$jt = "";
		if ($CURRENTUSERAJAX)
			$jt = "</span>";

		$dataLineArray = explode(":@@:", $dataLine);

		$value = $dataLineArray[0];
		$filters = make_var_safe($dataLineArray[2]);
		$page = make_var_safe($dataLineArray[3]);
		$channels = make_var_safe($dataLineArray[4]);
		$tags = make_var_safe($dataLineArray[5]);
		$pagesStr = "";
		$sw = "";
		$externalLinks = "";
		$subscribed = "";
		$hidethread = "";
		$edStr = "";
		$row = "";
		$relnofollow = "";
		$NWS = "";
		$locked = "";
		$sticky = "";
		$search1 = "";
		$retstr = "";
		$limitpages = "";

		$numcom = "num_comments";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$numcom = "num_comments_T";

		$limitBoundary = "0,20";
		$pc = make_num_safe($page);

		if ($pc > 1) {
			$upperBound = $pc * 20;
			$lowerBound = $upperBound - 20;
			$limitBoundary = "$lowerBound, 20";
		}
		else
			$pc = 1;

		if (!is_numeric($CURRENTUSERID))
			$CURRENTUSERID=0;

	$search0 = "";
	$search1 = "";
	$search2 = "";
	$searchdatesav = "";
		if ($value) {
			$dataSearch = explode(":!@:", $value);
			$searchterm ="";
			if ($dataSearch[0]) {
				$search0 = utf8_encode($dataSearch[0]);
				$search0 = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $search0);
				$search0 = html_entity_decode($search0, ENT_NOQUOTES, 'UTF-8');
				$search0 = str_replace("::@plus@::","+",$search0);
				$search0 = str_replace("::@euro@::","€",$search0);
				$search0 = make_var_safe($search0);
			}
			if ($dataSearch[1]) {
				$search1 = utf8_encode($dataSearch[1]);
				$search1 = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $search1);
				$search1 = html_entity_decode($search1, ENT_NOQUOTES, 'UTF-8');
				$search1 = str_replace("::@plus@::","+",$search1);
				$search1 = str_replace("::@euro@::","€",$search1);
				$search1 = make_var_safe($search1);
			}
			// log search
			mf_query("INSERT INTO search_log (user, date, search, type, user_searched)
			values
			(\"$CURRENTUSER\", '".time()."', \"".mysql_real_escape_string($search0)."\", '0', \"".mysql_real_escape_string($search1)."\")");

			if ($search0) {
				if ($dataSearch[4] == "exact") {
					$term_array[0] = $search0;
				$searchterm = "LOWER(f1.body) LIKE \"%".mysql_real_escape_string(mb_strtolower($search0,'UTF-8'))."%\" AND ";
				}			
				else if ($dataSearch[4] == "all") {
					$term_array = explode(' ', $search0);
					foreach($term_array as $term) {
						if (strlen($term) > 0)
						$searchterm .= "LOWER(f1.body) LIKE '%".mb_strtolower($term,'UTF-8')."%' AND ";
					}
				}
				else if ($dataSearch[4] == "one") {
					$term_array = explode(' ', $search0);
					$searchterm = "(";
					$or_multi = "";
					foreach($term_array as $term) {
						if (strlen($term) > 0) {
						$searchterm .= "$or_multi LOWER(f1.body) LIKE '%".mb_strtolower($term,'UTF-8')."%' ";
							$or_multi = "OR";
						}
					}
					$searchterm .= ") AND ";
				}
			}


			if ($dataSearch[2]) {
				$searchdate = htmlspecialchars($dataSearch[2]);
				$searchdate = make_var_safe($searchdate);
				if (substr($searchdate,4,1) == "-") { // US date format
					$searchmonth = substr($searchdate,5,2);
					$searchday = substr($searchdate,8,2);
					$searchyear = substr($searchdate,0,4);
				}
				else {
					$searchmonth = substr($searchdate,3,2);
					$searchday = substr($searchdate,0,2);
					$searchyear = substr($searchdate,6,4);
				}
				$search2 = mktime(0, 0, 0,"$searchmonth", "$searchday", "$searchyear") + 1;
				$searchdatesav = $searchdate;
			}
			$search_inthread = "";
			$search_inthread_query = "f1.threadID > 0 ";
			if (is_numeric($dataSearch[3])) {
				$search_inthread = $dataSearch[3];
				$search_inthread_query = "f1.threadID = '$search_inthread' ";
			}
		}

		$searchuser ="";
		if ($search1)
		$searchuser = "LOWER(f1.user) = \"".mb_strtolower($search1,'UTF-8')."\" AND ";

		$searchdate ="";
		if ($search2)
			$searchdate = "f1.date < '$search2' AND ";
	
		$posttype = "f1.posttype < 3 AND ";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$posttype = "";

		$channelFilterList = "";
		if ($channels) {
			if ($channels != "none") {
				$filterArray = explode(",", $channels);
				foreach($filterArray as $channel) {	
					if ($channel != "")
						$channelFilterList .= "t2.category <> ".make_num_safe($channel)." AND ";
				}
			}
		}
	
		$tags_join = "";
		if ($tags) {
			$tags_array = explode(",",make_var_safe($tags));
			
			$i_tag = 0;
			foreach($tags_array as $tag) {
				$i_tag ++;
				$tags_join .= " JOIN forum_tags AS tag_$i_tag ON (f1.threadID = tag_$i_tag.threadID AND tag_$i_tag.tag = \"$tag\") ";
			}
		}

		$threadTypeSelector = "AND t2.threadtype > 0 AND t2.threadtype < 3 ";

		$filter = "";
		if ($CURRENTUSER != "anonymous") {
			if ($filters)
				$filter = $filters;
			else if (array_key_exists('threadFilter', $_COOKIE)) {
				$filter = $_COOKIE["threadFilter"];
				if ($filter == "undefined") $filter = "";
			}
		}

		$pThreadsOnly = "";
		$team_in_pthread = "";
		if ($CURRENTUSER != "anonymous") {
			if ($filter == "pthreads") {
				$pThreadsOnly = "AND (t2.pthread=1 AND fh.userID IS NOT NULL)";
				$emptyPthreads = "";
				if (!$CURRENTUSERTEAMINPTHREAD)
					$team_in_pthread = "f1.teamID = 0 AND";
			}
			else {
				$pThreadsOnly = "AND (t2.pthread = 0 OR (t2.pthread=1 AND fh.userID IS NOT NULL))";
				if (!$CURRENTUSERUNREADPTHREAD)
					$emptyPthreads = "AND ((t2.pthread = 0) OR (t2.pthread = 1 AND IFNULL(t2.$numcom - fh.num_posts, t2.$numcom) > 0) OR (t2.pthread = 1 AND (fh.userID IS NOT NULL AND subscribed > 0 AND subscribed < 3)))";
				else
					$emptyPthreads = "";
			}
		}
		else {
			$pThreadsOnly = "AND t2.pthread = 0";
			$emptyPthreads = "AND t2.pthread = 0";
		}

		$subscribedOnly = "";
		$subscribedJoin = "LEFT";
		if ($filter == "subscribed") {
			$subscribedOnly = " AND fh.subscribed > 0 AND fh.subscribed < 3";
			$subscribedJoin = "";
			$emptyPthreads = "";
			if ($channelFilterList == "t2.category <> $siteSettings[flood_ID] AND " and !$CURRENTUSERFLOOD)
				$channelFilterList = "";
		}

		$fhUser = " AND fh.userID = $CURRENTUSERID";
		if ($filter == "hidden") {
			$subscribedOnly = " AND fh.subscribed = 3";
			$subscribedJoin = "";
			$emptyPthreads = "";
			if ($channelFilterList == "t2.category <> $siteSettings[flood_ID] AND " and !$CURRENTUSERFLOOD) {
				$channelFilterList = "";
				$fhUser = " AND ((t2.category = $siteSettings[flood_ID] AND fh.userID IS NULL) OR (t2.category = $siteSettings[flood_ID] AND fh.userID = $CURRENTUSERID AND fh.subscribed < 3) OR (fh.userID = $CURRENTUSERID AND fh.subscribed = 3))";
				$subscribedOnly = "";
			}
		}

		$hidehidden = "";
		if ($filter == "" and $CURRENTUSER != "anonymous") {
			$hidehidden = "AND (fh.userID IS NULL OR fh.subscribed < 3)";
		}

		
		if ($CURRENTUSER != "anonymous") {
			if ($filter == "buried")
				$ratingCondition = "(t2.rating <= $CURRENTUSERDTT)";
			else
				$ratingCondition = "(t2.rating > -100)";
		}
		else 
			$ratingCondition = "t2.rating >= 0";
			
		if ($filter == "subscribed" or $filter == "pthread")
			$ratingCondition = "(t2.rating > -100)";

		if ($filter == "all") {
			$ratingCondition = "(t2.rating > -100)";
			$channelFilterList = "";
			$pThreadsOnly = " AND (t2.pthread = 0 OR (t2.pthread=1 AND fh.userID IS NOT NULL)) ";
			$exclusiveChannel = "";
			$subscribedJoin = "LEFT";
			$subscribedOnly = "";
			$emptyPthreads = "";
			$threadTypeSelector = "AND t2.threadtype > 0 AND t2.threadtype < 3 ";
			$fhUser = " AND fh.userID = $CURRENTUSERID";
		}

		if ($filter == "teams") {
			$ratingCondition = "(t2.rating > -100)";
			$pThreadsOnly = " AND (t2.pthread = 0 OR (t2.pthread=1 AND fh.userID IS NOT NULL)) ";
			$subscribedJoin = "LEFT";
			$subscribedOnly = "";
			$emptyPthreads = "";
			$threadTypeSelector = "AND t2.threadtype > 0 AND t2.threadtype < 3 AND t2.teamID > 0 ";
			$fhUser = " AND fh.userID = $CURRENTUSERID";
		}

		$limitpages_text = "";
		if ($searchuser == "" && $channelFilterList == "" && ($filter == "" || $filter == "all") && $search_inthread = "") {
			$limitpages = "LIMIT 0,400";
			$limitpages_text = "&nbsp;<span style='vertical-align:sub;'><small>($LANG[SEARCH_LIMIT_PAGES])</small></span>";
		}


		$forumsStr = "SELECT
							f2.*,
							c1.name as categoryname,
							t2.title, t2.pthread, t2.spoiler, t2.poll, t2.teamID
						FROM (
							SELECT
								f1.ID,
								f1.threadID,
								f1.body,
								f1.user,
								f1.date
							FROM forum_posts as f1 
							$tags_join
							WHERE
								$searchterm
								$searchuser
								$searchdate
								$posttype
								$search_inthread_query
							ORDER BY f1.date desc	
						) as f2 
						LEFT JOIN forum_topics AS t2
							ON (
								t2.ID = f2.threadID
							)
						LEFT JOIN categories AS c1
							ON (
								c1.ID = t2.category 
							) 
						$subscribedJoin JOIN fhits as fh
							ON (
								fh.threadID = t2.ID
								$fhUser
						$subscribedOnly
					) WHERE 
						$channelFilterList
						$ratingCondition
						$emptyPthreads
						$hidehidden
						$pThreadsOnly
						$threadTypeSelector
						LIMIT $limitBoundary
					";


					$forumsStrPageString = "SELECT
					count(f2.ID) as Expr1					
				FROM (
					SELECT
						f1.ID,
						f1.threadID
					FROM forum_posts as f1
					$tags_join
					WHERE
						$searchterm
						$searchuser
						$searchdate
						$posttype
						$search_inthread_query 
					$limitpages
				) as f2
				LEFT JOIN forum_topics AS t2
				ON (
					t2.ID = f2.threadID
				)
				$subscribedJoin JOIN fhits as fh
					ON (
						fh.threadID = t2.ID
						$fhUser
						$subscribedOnly
					) WHERE
						$channelFilterList
						$ratingCondition
						$emptyPthreads
						$pThreadsOnly
						$hidehidden
						$threadTypeSelector";

	
		$posts = mf_query($forumsStr);

		while( $post = mysql_fetch_assoc( $posts )) {
			$retstr .= "<div id='post$post[ID]' class='threadInfo' style='border-bottom: 2px solid #D3D3D3;'>";
			$retstr .= "<div style='padding-right: 0px; text-align: center; width: 36px; float:left;'></div>";
			$retstr .= "<table width='100%' border='0' class='threadTable'><tr>";
			$retstr .= "<td class='noNewPosts' colspan='7'>";

			$text = format_newlines($post['body'] );
			$text = format_blurcode($text , $relnofollow );
			$text = format_urldetect($text, $relnofollow );
			$text = format_smilies($text );
			if ($searchterm) {
				$text = highlight($text,$term_array);
			}

			$private = "";
			if ($post['pthread'] == 1)
				$private = "<span class='privateNotification'>[$LANG[PRIVATE]]</span>";

			$privateteam = "";
			if ($post['teamID']) {
				if ($post['pthread'] == 1)
				$private = "<span class='privateNotification'>[ Team ".team_name($post['teamID'])." ]</span>";
				else
					$private = "<span class='spoilerNotification'>[ ".team_name($post['teamID'])." ]</span>";
			}

			$poll = "";
			if ($post['poll'] > 0)
				$poll = "<span class='pollNotification'>[$LANG[POLL]]</span>";

			$spoiler = "";
			if ($post['spoiler'] > 0)
				$spoiler = "<span class='spoilerNotification'>[$LANG[SPOILER]]</span>";

			$datepost = $post['date'] -1;
			$retstr .= "<span class='threadTitleText'><small>$LANG[SEARCH_TIT2]</small>$NWS $locked $sticky $private $spoiler $poll
				<a href='index.php?shard=forum&action=calculatePageLocationForFirstNew&postID=$post[ID]&sl=$datepost'>";
			if ($jt)
				$retstr .= "<span onclick=\"emptymainThread($post[threadID],$datepost,1,0); return false;\" style='cursor:pointer;'>";
			$retstr .= "<b>$post[title]</b>$jt</a></span>
				<span class='subThreadTitleLine' style='margin-left: 15px; float: right;'>$pagesStr</span>
				&nbsp;$externalLinks $subscribed $hidethread $edStr</div></td></tr>";
			$retstr .= "<tr><td class='subThreadTitleLine'>$text</td>";
			$retstr .= "<td width='134px' class='threadInfoTDsmall' style='text-align: right; vertical-align:top'>
				".date($LANG['DATE_LINE_SHORT'], $post['date'])."</a>
			<br/>$LANG[DATE_LINE_FULL1]: <a href=\"index.php?shard=forum&amp;action=un2id&amp;name=$post[user]\">";
			if ($jt)
			$retstr .= "<span onclick=\"userprofile('".urlencode($post['user'])."'); return false;\">";
			$retstr .= "$post[user]$jt</a>
				<br/><br/>$LANG[SEARCH_FILTER_CHANNEL] $post[categoryname]";
			$retstr .="</td></tr></table></div>";		
		}

		if ($retstr == "")
			$searchresult = "<br/><br/><b>$LANG[NO_POSTS]</b><div style='padding-top: 210px;'></div>";
		else {
		$searchresult = $retstr;
			if (mysql_num_rows($posts) < 3)	{
				$spaceheight = (4 - mysql_num_rows($posts))* 50;
				$searchresult .= "<div style='padding-top: ".$spaceheight."px;'></div>";
			}
		}
		$retstr= "";
	
		$retStr = "";
	
		$page_prec ="";
		$page_suiv = "";

		for ($page = 1; $page<=$pc; $page++ ) {
			if ($page == $pc)
				$pageCountStr = "class='pageListSelected'";
			else
				$pageCountStr = "class='pageListUnSelected'";

				$retStr .= "<span onclick=\"changepagepost($page);\" $pageCountStr>$page</span> ";
		}

		$retStr = "<div>$LANG[PAGES]: ".$retStr." (... ?)</div>";
		if ($pc > 1) {
			$page_prec = $pc - 1;
			$retStr .= "<div style='margin-top:8px;margin-bottom:8px;'>
					<span onclick=\"changepagepost($page_prec);\" class='button_mini' style='vertical-align: middle;'>
					<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
		}
		$page_suiv = $pc + 1;
		if (!$page_prec)
			$retStr .= "<div style='margin-top:8px;margin-bottom:8px;'>";
		else
			$retStr .= "&nbsp;";
		$retStr .= "<span onclick=\"changepagepost($page_suiv);\" class='button_mini' style='vertical-align: middle;'>$LANG[NEXT_PAGE]
				<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" />
					</span>
				&nbsp; <span style='font-size:0.8em;'>$LANG[GO_TO_PAGE]:</span> <input type='text' size='1' value='' name='gotopage' id='gotopage' class='bselect'/> <span onclick=\"changepagepost();\" class='button_mini'>$LANG[SUBMIT]</span>
				</div>";
	
		return $searchresult."::arrdlm::".$search0."::arrdlm::".$search1."::arrdlm::".$searchdatesav."::arrdlm::".$retStr."::arrdlm::".$search_inthread;
	}
	
	function ajax_userreadlist($tid) {
		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $LANG;

		if (!is_numeric($tid))
			exit();
	
		if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && $CURRENTUSER != "bot") {
			$retStr = "$LANG[USER_BROWSING_THREAD]: ".getUserList($tid);
			return $retStr;
		}
	}

	function ajax_refreshUnreadP($tid) {
		global $CURRENTUSERID;
		global $CURRENTUSER;
		global $LANG;
		global $verifyEditDelete;

		$numcom = "num_comments";
		$last_post_date = "last_post_date";
		$posttype = "AND posttype < '3'";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo')) {
			$numcom = "num_comments_T";
			$last_post_date = "last_post_date_T";
			$posttype = "";
		}

		if ($CURRENTUSER != "anonymous" && $CURRENTUSER != "bot" && is_numeric($tid)) {
			$read = mf_query("SELECT num_posts,date FROM fhits WHERE threadID = '$tid' AND userID = '$CURRENTUSERID' LIMIT 1");
			$read = mysql_fetch_assoc($read);
		
			$total = mf_query("SELECT $numcom, $last_post_date FROM forum_topics WHERE ID = '$tid' LIMIT 1");
			$total = mysql_fetch_assoc($total);

			if ($read['num_posts'] > $total[$numcom]) {
				mf_query("UPDATE fhits SET num_posts = '$total[$numcom]' WHERE threadID = '$tid' AND userID = '$CURRENTUSERID' LIMIT 1");
				$read['num_posts'] = $total[$numcom];
			}
			$unread = $total[$numcom] - $read['num_posts'];

			$lastread = mf_query("SELECT date FROM forum_posts WHERE threadID = '$tid' AND date > '$read[date]' $posttype ORDER by date ASC LIMIT 1");
			$lastread = mysql_fetch_assoc($lastread);
			$lastpost = mf_query("SELECT date,ID FROM forum_posts WHERE threadID = '$tid' $posttype ORDER by ID DESC LIMIT 1");
			$lastpost = mysql_fetch_assoc($lastpost);
			$lastread = $lastread['date'] - 1;
			$lastpostID = $lastpost['ID'];
			$lastpost = $lastpost['date'] - 1;

			$retStr = total_num_new_Posts($tid,$unread,$total[$numcom],$lastread,$lastpost,$lastpostID);

			return $retStr;
		}
	}

	function ajax_blogThread($data)	{
		$returnStr = generateBlog($data);
		return $returnStr;
	}

	function ajax_showBlogList() {
		$returnStr = generateBlogList();
		return $returnStr;
	}

	function ajax_showblogConf() {
		$returnStr = generateBlogConf();
		return $returnStr;
	}

	function ajax_saveblogConf($data) {
		$returnStr = saveBlogConf($data);
		return $returnStr;
	}

	function ajax_blogUpdate($data)	{
		$returnStr = blogUpdate($data);
		return $returnStr;

	}

	function ajax_resetblogCore($data) {
		$returnStr = refreshblogCore($data);
		return $returnStr;
	}

	function ajax_unstick($ID) {
		global $verifyEditDelete;
		global $CURRENTUSER;

		if (is_numeric($ID) && $verifyEditDelete)
			mf_query("UPDATE forum_topics SET threadtype=2 WHERE ID=$ID LIMIT 1") ;
	}
	
	function ajax_signal_admin($dataLine) {
		global $siteSettings;
		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $CURRENTUSERID;
		global $LANG;
		global $verifyEditDelete;
		global $CURRENTUSERRULES;

		$dataLine = explode(":!@:", $dataLine);
		
		if (!is_numeric($dataLine[0]))
			exit();

		$posttype = "AND posttype < 3 ";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$posttype = "";

		// get post contents
		$childRS = mf_query("SELECT 
								forum_posts.body,
								forum_posts.ID,
								forum_posts.user,
								forum_posts.threadID
								FROM forum_posts WHERE ID=$dataLine[0] $posttype
								order by ID desc limit 0,1");
								

		if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && $row = mysql_fetch_assoc($childRS)) {
			$verify = mf_query("SELECT threadID FROM fhits WHERE threadID = '$row[threadID]' AND userID = '$CURRENTUSERID' LIMIT 1");
			if ($verify = mysql_fetch_assoc($verify)) {
			$retStr = "<div style='margin-bottom:8px;'>$LANG[SIGNAL_ADMIN_CONFIRM]</div>";
				$retStr .= "$LANG[SIGNAL_ADMIN_YOUR_COMMENT]:<div style='margin-bottom:4px;'><input type='text' name='signal_comment' id='signal_comment' size='74' />
			<span class='button' onclick=\"submitSignal_admin(".$row['ID']."); return false;\">
			<b>$LANG[SIGNAL_ADMIN_BUTTON]</b></span>
			</div>";
			$retStr .= "$LANG[SIGNAL_ADMIN_THE_POST]:<div style='border: 2px solid red; padding: 2px;'>".format_post($row['body'], false)."</div>";
		}
		else
			exit();
		}
		else
			exit();
		
		$retStr .= "::arrdlm::".$CURRENTUSERID;
		return $retStr;
	}

	function ajax_submitSignal_admin($dataLine)	{
		global $CURRENTUSERID;
		global $CURRENTUSER;
		global $verifyEditDelete;
		global $LANG;
		global $siteSettings;
		global $CURRENTSTATUS;


		$dataLine = explode("::!sg@::", $dataLine);
		$postID = make_num_safe($dataLine[0]);
		$comment = utf8_encode($dataLine[1]);
		$comment = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $comment);
		$comment = html_entity_decode($comment, ENT_NOQUOTES, 'UTF-8');
		$comment = mb_strtolower(trim($comment),'UTF-8');
		$comment = str_replace("::@plus@::","+",$comment);
		$comment = str_replace("::@euro@::","€",$comment);
		$comment = make_var_safe(htmlspecialchars($comment));

		if ($CURRENTUSER == "anonymous" || $CURRENTSTATUS == "banned" || !$postID)
			exit();

		$inTime = time();
		$getThreadId = mf_query("SELECT ID, category FROM forum_topics WHERE userID = 1 and threadtype < 3 and title = \"".$LANG['SIGNAL_ADMIN_TITLE']."\" LIMIT 1");
		if ($getThreadId2 = mysql_fetch_assoc($getThreadId)) {
			$ThreadID = $getThreadId2['ID'];
			if ($getThreadId2['category'] != $siteSettings['channel_signal'])
				mf_query("UPDATE forum_topics SET category = '$siteSettings[channel_signal]' WHERE ID = '$ThreadID' LIMIT 1");
		}
		else { // Create thread if it doesn't exist
			mf_query("INSERT INTO forum_topics
					(title, body, user, userID, date, threadtype, pthread, category)
					VALUES (\"".$LANG['SIGNAL_ADMIN_TITLE']."\", \"".$LANG['SIGNAL_ADMIN_TITLE']."\", \"$siteSettings[systemuser]\", 1, $inTime, 1, 1, '$siteSettings[channel_signal]' )");
			$getThreadId = mf_query("SELECT ID, category FROM forum_topics WHERE userID = 1 and threadtype < 3 and title = \"".$LANG['SIGNAL_ADMIN_TITLE']."\" LIMIT 1");
			$getThreadId2 = mysql_fetch_assoc($getThreadId);
			$ThreadID = $getThreadId2['ID'];
			mf_query("INSERT INTO forum_posts
					(body, user, userID, date, threadID, rating)
					VALUES (\"".$LANG['SIGNAL_ADMIN_TITLE']."\", \"$siteSettings[systemuser]\", 1, $inTime, $ThreadID, 0)");
			$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=1 and date='$inTime' ORDER BY ID limit 0,1");
			$lastPost = mysql_fetch_assoc($lastPost);
			mf_query("UPDATE forum_topics SET last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = 1, num_comments_T = 1 WHERE ID='$ThreadID' LIMIT 1");
		}
		// Ajout des admins dans le sujet
		if ($siteSettings['teamadmin'] || $siteSettings['teammodo']) {
			$query = mf_query("SELECT userID FROM teams_users WHERE (teamID = '$siteSettings[teamadmin]' OR teamID = '$siteSettings[teammodo]') AND level < 3");
			while ($adduser = mysql_fetch_assoc($query))
				mf_query("INSERT IGNORE INTO fhits (threadID, userID, addedDate) VALUES ($ThreadID, $adduser[userID], ".time().")");
		}
		else {
			$query = mf_query("SELECT userID FROM permissiongroups WHERE pGroup = 'admin' OR pGroup = 'modo' ");
			while ($adduser = mysql_fetch_assoc($query))
				mf_query("INSERT IGNORE INTO fhits (threadID, userID, addedDate) VALUES ($ThreadID, $adduser[userID], ".time().")");
		}
		$signaledPost = mf_query("SELECT ID, body, user, date, threadID FROM forum_posts WHERE ID='$postID' LIMIT 1");
		$signaledPost = mysql_fetch_assoc($signaledPost);
		$signaledTopic = mf_query("SELECT title, pthread FROM forum_topics WHERE ID='$signaledPost[threadID]' LIMIT 1");
		$signaledTopic = mysql_fetch_assoc($signaledTopic);
		if ($signaledTopic['pthread'] == 1)
			exit();
		$topictittle = $signaledTopic['title'];
		$toreplace = array("[", "]");
		$replaceby = array("-", "-");
		$topictittle = str_replace($toreplace, $replaceby, $topictittle);
		$msg = $LANG['SIGNAL_TEXT1'] . " \[b\]$CURRENTUSER\[\/b\] " . $LANG['SIGNAL_TEXT2'] . " :\[br\]
				" . $LANG['SIGNAL_TEXT3'] . " \[b\]\[url=http:\/\/www.".$siteSettings['titlebase']."\/index.php?shard=forum&action=un2id&name=".$signaledPost['user']."\]".$signaledPost['user']."\[\/url\]\[\/b\] " . $LANG['ON'] . " ".date($LANG['DATE_LINE_MINIMAL2'],$signaledPost['date']).$LANG['DATE_LINE_FULL2'].date("G:i",$signaledPost['date'])." " . $LANG['SIGNAL_TEXT4'] . " \[b\]\[url=http:\/\/www.".$siteSettings['titlebase']."\/index.php?shard=forum&action=calculatePageLocationForFirstNew&postID=".$signaledPost['ID']."&sl=".$signaledPost['date']."\]".$topictittle."\[\/url\]\[\/b\]\[br\]\[hr\]";
		$msg .= $signaledPost['body']."\[hr\]";
		if ($comment)
			$msg .= "\[br\]" . $LANG['SIGNAL_TEXT5'] . " :\[br\]\[b\]".$comment."[\/b\]";
		$inTime = time();
		$result = mf_query("INSERT INTO forum_posts
							(body, user, userID, date, threadID, rating)
							VALUES (\"$msg\", \"$siteSettings[systemuser]\", 1, $inTime, $ThreadID, 0)");
		$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=1 and date='$inTime' ORDER BY ID limit 0,1");
		$lastPost = mysql_fetch_assoc($lastPost);
		mf_query("UPDATE forum_topics SET last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 WHERE ID='$ThreadID'");

		return $ThreadID;
	}

	function ajax_removerating($id) {
		
		if (is_numeric($id)) {
			global $CURRENTUSER;
			global $CURRENTUSERID;
			global $CURRENTSTATUS;
			global $CURRENTUSERDTP;
			global $CURRENTUSERRATING;
			global $LANG;

			$query = mf_query("SELECT * FROM postratings WHERE ID = '$id' LIMIT 1");
			$rate = mysql_fetch_assoc($query);
			if (!isInGroup($CURRENTUSER, "admin") && $rate['user'] != $CURRENTUSER)
				exit();
			$postID = $rate['postID'];
			mf_query("delete FROM postratings WHERE ID = '$id' LIMIT 1");
			if ($rate['rating'] > 0) {
				mf_query("UPDATE forum_user_nri SET cum_post_rating = (cum_post_rating - $rate[rating]), num_received_posmods = num_received_posmods - 1 WHERE userID='$rate[modeduserID]' LIMIT 1");
				mf_query("UPDATE forum_user_nri SET num_mods = (num_mods - 1), num_posmods = num_posmods - 1 WHERE userID='$CURRENTUSERID' LIMIT 1");
			}
			if ($rate['rating'] < 0) {
				mf_query("UPDATE forum_user_nri SET cum_post_rating = (cum_post_rating - $rate[rating]), num_received_negmods = num_received_negmods - 1 WHERE userID='$rate[modeduserID]' LIMIT 1");
				mf_query("UPDATE forum_user_nri SET num_mods = (num_mods - 1), num_negmods = num_negmods - 1 WHERE userID='$CURRENTUSERID' LIMIT 1");
			}
			if ($postID) {
				$query = mf_query("SELECT rating FROM forum_posts WHERE ID = '$postID' LIMIT 1");
				$post = mysql_fetch_assoc($query);

				$rating = $post['rating'] - $rate['rating'];
				if ($rating > -0.01 && $rating < 0.01)
					$rating = 0;
				mf_query("UPDATE forum_posts SET rating = '$rating' WHERE ID='$postID' LIMIT 1");

				$post['rating'] = number_format($rating, 2);
				$rating = 0;
				$postRatingColorGradient = "postRatingColorGradient1";
				if ($post['rating'] > 0) {
					$rating = "+".$post['rating'];
					$postRatingColorGradient = "postRatingColorGradient2";
				}
				else if ($post['rating'] < 0) {
					$rating = $post['rating'];
					$postRatingColorGradient = "postRatingColorGradient3";
				}
				$whorated = whorated($postID);
				
				$rated = false;
				$arrows = "<div onclick=\" already_rated = setRateVisible('up_rate".$postID."','".$postID."','down_rate".$postID."','".$rated."',already_rated); toggleRatingArrow('post', $postID, 'uparrow', ".number_format($CURRENTUSERRATING, 2).");\" id='uparrowpost".$postID."' class='uparrowoff'></div>
						<div onclick=\" already_rated = setRateVisible('down_rate".$postID."','".$postID."','up_rate".$postID."','".$rated."',already_rated); toggleRatingArrow('post', $postID, 'downarrow', ".number_format($CURRENTUSERRATING, 2).");\" id='downarrowpost".$postID."' class='downarrowoff'></div>";

				$up_rate = "<select class='up_rate' name='up_rate".$postID."' onchange=\"selectRate(this.options[this.selectedIndex].value,'up_rate".$postID."','".$postID."');\">";
				$up_rate .= $_SESSION['option_up'];
				$up_rate .= "</select>";
				$down_rate = "<select class='down_rate' onchange=\"selectRate(this.options[this.selectedIndex].value,'down_rate".$postID."','".$postID."');\">";
				$down_rate .= $_SESSION['option_down'];
				$down_rate .= "</select>";

				$retstr = $rate['postID']."::@@::".$post['rating']."::@@::".$rating."::@@::".$postRatingColorGradient."::@@::".$whorated."::@@::::@@::$LANG[RATING]::@@::$arrows::@@::$up_rate::@@::$down_rate";
				
				return $retstr;
			}
			else
				mf_query("UPDATE forum_topics SET rating = rating - $rate[rating] WHERE ID='$rate[threadID]' LIMIT 1");
		}
	}

	function ajax_usertotalpost() {
		global $CURRENTUSERID;
		global $CURRENTUSER;
	
		if ($CURRENTUSER != "anonymous") {
			$usertotalpost = mf_query("SELECT num_posts_notnri FROM forum_user_nri WHERE userID='$CURRENTUSERID' LIMIT 1");
			$usertotalpost = mysql_fetch_assoc($usertotalpost);
			$usertotalpost = $usertotalpost['num_posts_notnri'];
		}
		return $usertotalpost .  "::@totp@::" . $CURRENTUSERID;
	}

	function ajax_gotopost($dataLine) {
		global $CURRENTUSER;
		$dataLine = explode(":!p@:", $dataLine);

		if (is_numeric($dataLine[0])) {
			$postID = $dataLine[0];
			
			$tid = mf_query("SELECT threadID, date FROM forum_posts WHERE ID='$postID' LIMIT 1");
			$tid = mysql_fetch_assoc($tid);
			$postdate = $tid['date'] - 1;
			$tid = $tid['threadID'];

			return $tid . ":!@:" . $postdate . ":!@::!@::!@:" . $postID . ":!@:" . $dataLine[1];
		}
	}

	function ajax_refreshTags($tag_time="") {
		global $siteSettings;
		if (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus") {
		$verif = true;
		if (is_numeric($tag_time)) {
			$msdate = @filemtime('html/tag_cloud.html');
			if (($msdate - $tag_time) < 3)
				$verif = false;
		}
		$retstr = "";
		if ($verif)
			$retstr = @file_get_contents("html/tag_cloud.html");

		return $retstr;
	}
	}
	
	function ajax_searchTag($dataLine) {
		$dataLine = explode("::@@st@@::", $dataLine);
		
		$tag = utf8_encode($dataLine[0]);
		$tag = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $tag);
		$tag = html_entity_decode($tag, ENT_NOQUOTES, 'UTF-8');
		$tag = mb_strtolower(trim($tag),'UTF-8');
		$tag = str_replace("::@plus@::","+",$tag);
		$tag = str_replace("::@euro@::","€",$tag);
		$tag = make_var_safe(htmlspecialchars($tag));
		$tag_list = "";

		$query = mf_query("SELECT ID, tag FROM tags WHERE tag LIKE \"%".$tag."%\" ORDER BY tag LIMIT 16");
		while ($row = mysql_fetch_assoc($query)) {
			$tag_list .= "$row[ID],";
		}
		
		return $tag_list."@@:st:@@".$dataLine[1];
	}
	
	function ajax_vote_for($dataLine) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		$dataLine = explode("::@@vote@@::", $dataLine);
		if ($CURRENTUSER == "anonymous")
			exit();

		if (is_numeric($dataLine[0])) {
			$postID = make_num_safe($dataLine[0]);
			$voteName = make_var_safe($dataLine[1]);
			if (strlen($voteName) > 20)
				$voteName = substr($voteName,0,20);
			$query = mf_query("SELECT * FROM post_votes_user WHERE postID ='$postID' AND voteName = \"$voteName\" AND userID = \"$CURRENTUSERID\" LIMIT 1");
			if ($row=mysql_fetch_assoc($query)) {
				if ($row['vote_for'] != "1") {
					mf_query("UPDATE post_votes_user SET vote_for = '1', vote_against = '0' WHERE postID = '$postID' AND voteName = \"$voteName\" AND userID = '$CURRENTUSERID' LIMIT 1");
					mf_query("UPDATE post_votes SET total_vote_for = total_vote_for + 1, total_vote_against = total_vote_against - 1 WHERE postID = '$postID' AND voteName = \"$voteName\" LIMIT 1");
				}
			}
			else {
				mf_query("INSERT IGNORE INTO post_votes_user (postID,voteName,userID,vote_for,vote_against) VALUES ('$postID', \"$voteName\", '$CURRENTUSERID', '1', '0')");
				mf_query("UPDATE post_votes SET total_vote_for = total_vote_for + 1 WHERE postID = '$postID' AND voteName = \"$voteName\" LIMIT 1");
			}
			$retstr = load_vote($postID,$voteName);
			return $retstr."::@@::vote_".$postID."_".$voteName;
		}
	}

	function ajax_vote_against($dataLine) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		$dataLine = explode("::@@vote@@::", $dataLine);
		if ($CURRENTUSER == "anonymous")
			exit();

		if (is_numeric($dataLine[0])) {
			$postID = make_num_safe($dataLine[0]);
			$voteName = make_var_safe($dataLine[1]);
			if (strlen($voteName) > 20)
				$voteName = substr($voteName,0,20);
			$query = mf_query("SELECT * FROM post_votes_user WHERE postID ='$postID' AND voteName = \"$voteName\" AND userID = \"$CURRENTUSERID\" LIMIT 1");
			if ($row=mysql_fetch_assoc($query)) {
				if ($row['vote_against'] != "1") {
					mf_query("UPDATE post_votes_user SET vote_for = '0', vote_against = '1' WHERE postID = '$postID' AND voteName = \"$voteName\" AND userID = '$CURRENTUSERID' LIMIT 1");
					mf_query("UPDATE post_votes SET total_vote_for = total_vote_for - 1, total_vote_against = total_vote_against + 1 WHERE postID = '$postID' AND voteName = \"$voteName\" LIMIT 1");
				}
			}
			else {
				mf_query("INSERT IGNORE INTO post_votes_user (postID,voteName,userID,vote_for,vote_against) VALUES ('$postID', \"$voteName\", '$CURRENTUSERID', '0', '1')");
				mf_query("UPDATE post_votes SET total_vote_against = total_vote_against + 1 WHERE postID = '$postID' AND voteName = \"$voteName\" LIMIT 1");
			}
			$retstr = load_vote($postID,$voteName);
			return $retstr."::@@::vote_".$postID."_".$voteName;
		}
	}
	
	function ajax_delete_thread_mod($modID) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		if (!is_numeric($modID))
			exit();

		$maxdate = time() - (3600 *24);
		$query = mf_query("SELECT * FROM postratings WHERE ID = '$modID' AND user = \"$CURRENTUSER\" AND modeddate > '$maxdate' LIMIT 1");
		if ($row=mysql_fetch_assoc($query)) {
			mf_query("DELETE FROM postratings WHERE ID = '$modID' LIMIT 1");
			mf_query("UPDATE forum_topics SET rating = rating - $row[rating] WHERE ID = '$row[threadID]' LIMIT 1");
			if ($row['rating'] > 0) {
				mf_query("UPDATE forum_user_nri SET num_mods = (num_mods - 1), num_posmods = num_posmods - 1 WHERE userID = '$CURRENTUSERID' LIMIT 1");
				mf_query("UPDATE forum_user_nri SET num_received_posmods = num_received_posmods - 1, cum_post_rating = (cum_post_rating - $row[rating]) WHERE userID = '$row[modeduserID]' LIMIT 1");
			}
			else {
				mf_query("UPDATE forum_user_nri SET num_mods = (num_mods - 1), num_negmods = num_negmods - 1 WHERE userID = '$CURRENTUSERID' LIMIT 1");
				mf_query("UPDATE forum_user_nri SET num_received_negmods = num_received_negmods - 1, cum_post_rating = (cum_post_rating - $row[rating]) WHERE userID = '$row[modeduserID]' LIMIT 1");
			}
		}
		return $modID;
	}

	function ajax_format_object($dataLine) {
		global $CURRENTUSER;
		if ($CURRENTUSER != "anonymous") {
			$dataLine = explode("::@@ob@@::", urldecode($dataLine));
			$object = preg_replace("/<a (.+?)<\/a>/i", "", $dataLine[1]);
			$object = preg_replace("/<img (.+?)\/>/i", "", $dataLine[1]);
			$object = preg_replace("/</i", "[", $object);
			$object = preg_replace("/ \/>/i", "]", $object);
			$object = preg_replace("/\/>/i", "]", $object);
			$object = preg_replace("/>/i", "]", $object);
			$object = preg_replace("/=\"/i", "={", $object);
			$object = preg_replace("/\"/i", "}", $object);
			$object = preg_replace("/='/i", "={", $object);
			$object = preg_replace("/'/i", "}", $object);
			$object = preg_replace("/\[\/param\]/i", "", $object);
			$src = preg_split("/src={(.+?)}/",$object, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
			if ($src[1] == "")
				$src = preg_split("/name={movie} value={(.+?)}/",$object, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
			$width = preg_split("/width={(.+?)}/",$object, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
			$height = preg_split("/height={(.+?)}/",$object, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
			preg_match_all("/\[param (.+?)\]/i",$object,$matches);
			$object = preg_split("/\[\/object\]/", $object);
			$object = "[object data={".$src[1]."} width={".$width[1]."} height={".$height[1]."}]";
			foreach ($matches[0] as $param)
				$object .= $param;
			$object .= "[/object]";
			return $dataLine[0]."::@@::".$object;
			
		}
	}
	
	function ajax_getTeamName($teamID) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTSTATUS;

		$teamName = "";
		$dataline = explode("@@::rf::@@", $dataline);
		if ($CURRENTUSER != "anonymous" && is_numeric($teamID)) {
			$isinteam = isInTeam($teamID,$CURRENTUSERID);
			if ($isinteam)
				$teamName = team_name($teamID);
		}
		return $teamName;
	}
	
	function ajax_team_in_pthread($value) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTUSERTEAMINPTHREAD;
		if ($CURRENTUSER != "anonymous" && is_numeric($value) && $CURRENTUSERTEAMINPTHREAD != $value)
			mf_query("UPDATE users SET team_in_pthread = '$value' WHERE ID = '$CURRENTUSERID' LIMIT 1");
		return;
	}

	function ajax_displayunreadPthread($value) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTUSERUNREADPTHREAD;
		if ($CURRENTUSER != "anonymous" && is_numeric($value) && $CURRENTUSERUNREADPTHREAD != $value)
			mf_query("UPDATE users SET displayunreadPthread = '$value' WHERE ID = '$CURRENTUSERID' LIMIT 1");
		return;
	}
	
	function ajax_userstick($ID) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		if ($CURRENTUSER != "anonymous" && is_numeric($ID)) {
			mf_query("INSERT INTO forum_topics_users (threadID, userID, threadtype) VALUES ('$ID', '$CURRENTUSERID', '1') ON DUPLICATE KEY UPDATE threadtype = '1'");
		}
	}

	function ajax_userunstick($ID) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		if ($CURRENTUSER != "anonymous" && is_numeric($ID)) {
			mf_query("INSERT INTO forum_topics_users (threadID, userID, threadtype) VALUES ('$ID', '$CURRENTUSERID', '2') ON DUPLICATE KEY UPDATE threadtype = '2'");
		}
	}
	
	function ajax_no_private_sticky($value) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTUSERNOPRIVSTICKY;
		if ($CURRENTUSER != "anonymous" && is_numeric($value) && $CURRENTUSERNOPRIVSTICKY != $value)
			mf_query("UPDATE users SET no_private_sticky = '$value' WHERE ID = '$CURRENTUSERID' LIMIT 1");
		return;
	}
	
	function ajax_set_last_unread($dataLine) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $verifyEditDelete;

		$dataLine = explode("@@::cpt::@@",$dataLine);
		if ($CURRENTUSER != "anonymous" && is_numeric($dataLine[0]) && is_numeric($dataLine[1])) {
			

			$posttype = "AND posttype < 3 ";
			if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
				$posttype = "";
	
			$countt = mf_query("SELECT COUNT(ID) AS totalPost FROM forum_posts WHERE threadID='$dataLine[0]' AND ID < '$dataLine[1]' $posttype");
			$countt = mysql_fetch_assoc($countt);
			$totalPost = $countt['totalPost'] + 1;

			$post = mf_query("SELECT date FROM forum_posts WHERE ID = '$dataLine[1]' LIMIT 1");
			$post = mysql_fetch_assoc($post);

			mf_query("UPDATE fhits SET date='$post[date]', num_posts='$totalPost' WHERE threadID='$dataLine[0]' and userID = '$CURRENTUSERID' LIMIT 1");
			
			return $dataLine[0]."@@".$CURRENTUSERID;
		}
	}
	
	sajax_init();

	include("ajax_commonlib.php");

	sajax_export("ajax_submitSignal_admin","ajax_signal_admin","ajax_unstick","ajax_submitDePublish","ajax_resetblogCore","ajax_blogUpdate","ajax_saveblogConf","ajax_showblogConf","ajax_showBlogList","ajax_blogThread","ajax_refreshUnreadP","ajax_userreadlist","ajax_search_posts","ajax_g_reply","ajax_previewPost","ajax_markAll","ajax_updateChannelsList","ajax_unhide2","ajax_hide2","ajax_unhide","ajax_hide","ajax_subscribe2","ajax_unsubscribe2","ajax_unsubscribe","ajax_subscribe", "ajax_show_smiles", "ajax_submit_poll_vote", "ajax_delete_pthread_user", "ajax_add_new_pthread_user", "ajax_submitRateComment", "ajax_callNewThreadForm", "ajax_submitDelete", "ajax_resetThreadList", "ajax_updateMod", "ajax_returnLastPost", "ajax_submitPost", "ajax_showEditWindow", "ajax_submitEdit", "ajax_threadUpdate", "ajax_postUpdate","ajax_postRefresh","ajax_modRefresh","ajax_removerating","ajax_usertotalpost","ajax_gotopost","ajax_refreshTags","ajax_searchTag","ajax_vote_for","ajax_vote_against","ajax_delete_thread_mod","ajax_format_object","ajax_getTeamName","ajax_team_in_pthread","ajax_displayunreadPthread","ajax_userstick","ajax_userunstick","ajax_set_last_unread","ajax_check_favorites","ajax_check_pt","ajax_no_private_sticky","ajax_list_modedposts","ajax_list_modedthreads"); // list of functions to export
	sajax_handle_client_request(); // serve client instances
	
	///////////////////////
	// End AJAX Handling //
	///////////////////////

	
	/////////////////////////////////////
	// Common Use Functions, extracted //
	// for AJAX and normal usage alike //
	/////////////////////////////////////
	
 
	function findPthreadUsers($tid, $usercreate=0) {
		global $siteSettings;
		global $LANG;
		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $verifyEditDelete;

				$mpuser = "";
				$usert = "";
		$listusermp = mf_query("SELECT users.ID, users.username, fhits.date FROM fhits, users WHERE fhits.userID=users.ID AND fhits.threadID='$tid' ORDER BY LOWER(users.username)");
		while ($row=mysql_fetch_assoc($listusermp))	{
					$indicate = "";
					if ($row['date'] == 0)
						$indicate = "style='color: red;'";
						
			$mpuser[] = "<span class='bold' $indicate>$row[username]</span>";
					if ($CURRENTSTATUS != "banned" && (($verifyEditDelete) || ($CURRENTUSER == $usercreate) || ($CURRENTUSER == $row['username'])))
				$mpuser[] = "<span class='deleteButton' title=\"$LANG[DELETEPTHREADUSERDESC]\" onclick=\"deletePthreadUser($row[ID],$tid);\">x</span>";
					else
						$mpuser[] = "<span class='deleteButton'>o</span>";
				}
				
				$ms3 = "";
				$ms3 .= implode(" ", $mpuser);
				
				return $ms3;
	}
	
	function generateForumStr($timeAgo=0, $totalCount=false, $search="", $filters="", $page="1", $channels="", $tags="", $teamID="") {
		global $CURRENTUSERID;
		global $CURRENTUSER;
		global $CURRENTUSERFLOOD;
		global $CURRENTUSERDTT;
		global $siteSettings;
		global $verifyEditDelete;
		global $CURRENTUSERTEAMINPTHREAD;
		global $CURRENTUSERUNREADPTHREAD;
		global $CURRENTUSERNOPRIVSTICKY;
		
		$numcom = "num_comments";
		$last_post_user = "last_post_user";
		$last_post_id = "last_post_id";
		$last_post_date = "last_post_date";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo')) {
			$numcom = "num_comments_T";
			$last_post_user = "last_post_user_T";
			$last_post_id = "last_post_id_T";
			$last_post_date = "last_post_date_T";
		}

		$pc = "";
		$limitBoundary = "0," .$siteSettings['threadpp'];
		if ($page)
			$pc = make_num_safe($page);
		if ($pc > "1") {
			$upperBound = $pc * $siteSettings['threadpp'];
			$lowerBound = $upperBound - $siteSettings['threadpp'];
			$limitBoundary = "$lowerBound, " . $siteSettings['threadpp'];
		}
	
		if (!is_numeric($CURRENTUSERID))
			$CURRENTUSERID=0;
		
		$search0 = "";
		$search1 = "";
		$search2 = "";
		$searchterm ="";
		if ($search) {
			$dataSearch = explode(":!@:", $search);
			$time_search = time();
			$search0 = "";
			$search1 = "";
			if ($dataSearch[0]) {
				$search0 = utf8_encode($dataSearch[0]);
				$search0 = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $search0);
				$search0 = html_entity_decode($search0, ENT_NOQUOTES, 'UTF-8');
				$search0 = str_replace("::@plus@::","+",$search0);
				$search0 = str_replace("::@euro@::","€",$search0);
				$search0 = make_var_safe($search0);
			}
			if ($dataSearch[1]) {
				$search1 = utf8_encode($dataSearch[1]);
				$search1 = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $search1);
				$search1 = html_entity_decode($search1, ENT_NOQUOTES, 'UTF-8');
				$search1 = str_replace("::@plus@::","+",$search1);
				$search1 = str_replace("::@euro@::","€",$search1);
				$search1 = make_var_safe($search1);
			}
			mf_query("INSERT INTO search_log (user, date, search, type, user_searched)
					values
					(\"$CURRENTUSER\", $time_search, \"".mysql_real_escape_string($search0)."\", '1', \"".mysql_real_escape_string($search1)."\")");
				
			if ($search0) {	
				if ($dataSearch[4] == "exact") {
					$term_array[0] = mb_strtolower($search0,'UTF-8');
					$searchterm = "LOWER(f1.title) LIKE '%".mb_strtolower($search0,'UTF-8')."%' AND ";
				}			
				else if ($dataSearch[4] == "all") {
					$term_array = explode(' ', $search0);
					foreach($term_array as $term) {
						if (strlen($term) > 0)
							$searchterm .= "LOWER(f1.title) LIKE '%".mb_strtolower($term,'UTF-8')."%' AND ";
					}
				}
				else if ($dataSearch[4] == "one") {
					$term_array = explode(' ', $search0);
					$searchterm = "(";
					$or_multi = "";
					foreach($term_array as $term) {
						if (strlen($term) > 0) {
							$searchterm .= "$or_multi LOWER(f1.title) LIKE '%".mb_strtolower($term,'UTF-8')."%' ";
							$or_multi = "OR";
						}
					}
					$searchterm .= ") AND ";
				}
			}

			if ($dataSearch[2]) {
				$searchdate = make_var_safe($dataSearch[2]);
				if (substr($searchdate,4,1) == "-") { // US date format
					$searchmonth = substr($searchdate,5,2);
					$searchday = substr($searchdate,8,2);
					$searchyear = substr($searchdate,0,4);
				}
				else {
					$searchmonth = substr($searchdate,3,2);
					$searchday = substr($searchdate,0,2);
					$searchyear = substr($searchdate,6,4);
				}
				$search2 = mktime(0, 0, 0,"$searchmonth", "$searchday", "$searchyear") + 1;
			}
		}

		$searchuser ="";
		if ($search1)
			$searchuser = "LOWER(f1.user) = \"".mb_strtolower($search1,'UTF-8')."\" AND ";

		$searchdate ="";
		if ($search2)
			$searchdate = "f1.date < '$search2' AND ";

		$exclusiveChannel = "";
		if (array_key_exists('channel', $_REQUEST)) {
			$chan = make_num_safe( $_REQUEST['channel'] );
			$exclusiveChannel = "f1.category=$chan AND";
		}
		
		$exlusiveTeam = "";
		if ($teamID)
			$exlusiveTeam = "f1.teamID = '$teamID' AND";

		$channelFilterList = "";
		if ($channels) {
			if ($channels != "none") {
				$filterArray = explode("/", $channels);

				foreach($filterArray as $channel) {	
					if ($channel != "")
						$channelFilterList .= "f1.category <> ".make_num_safe($channel)." AND ";
				}
			}
		}
		else if (array_key_exists('metaChannelFilter2', $_COOKIE) && $CURRENTUSER != "anonymous") {
			if ($_COOKIE['metaChannelFilter2'] != "") {
				$filterArray = explode(",", $_COOKIE['metaChannelFilter2']);

				foreach($filterArray as $channel) {	
					if ($channel != "")
						$channelFilterList .= "f1.category <> ".make_num_safe($channel)." AND ";
				}
			}
		}
		if ($CURRENTUSER == "anonymous")
			$channelFilterList .= "f1.category <> '$siteSettings[flood_ID]' AND ";
			
		$tags_join = "";
		if ($tags) {
			$tags_array = explode(",",make_var_safe($tags));
			
			$i_tag = 0;
			foreach($tags_array as $tag) {
				$i_tag ++;
				$tags_join .= " JOIN forum_tags AS tag_$i_tag ON (f1.ID = tag_$i_tag.threadID AND tag_$i_tag.tag = \"$tag\") ";
			}
		}

		$threadTypeSelector = "f1.threadtype > 0 AND f1.threadtype < 3 AND unvisible = 0 ";

		$last_date_flag = "";
		if ($timeAgo > 0 && is_numeric($timeAgo))
			$last_date_flag = "$last_post_date > $timeAgo AND";
		
		$filter = "";
		if ($CURRENTUSER != "anonymous") {
			if ($filters)
				$filter = $filters;
			else if (array_key_exists('threadFilter', $_COOKIE)) {
				$filter = $_COOKIE["threadFilter"];
				if ($filter == "undefined") $filter = "";
			}
		}

		$pThreadsOnly = "";
		$team_in_pthread = "";
		if ($CURRENTUSER != "anonymous") {
			if ($filter == "pthreads") {
				$pThreadsOnly = "AND (f2.pthread=1 AND fh.userID IS NOT NULL)";
				$emptyPthreads = "";
				if (!$CURRENTUSERTEAMINPTHREAD)
					$team_in_pthread = "f1.teamID = 0 AND";
			}
			else {
				$pThreadsOnly = "AND (f2.pthread = 0 OR (f2.pthread=1 AND fh.userID IS NOT NULL))";
				if (!$CURRENTUSERUNREADPTHREAD)
					$emptyPthreads = "AND ((f2.pthread = 0) OR (f2.pthread = 1 AND IFNULL(f2.$numcom - fh.num_posts, f2.$numcom) > 0) OR (f2.pthread = 1 AND (fh.userID IS NOT NULL AND subscribed > 0 AND subscribed < 3)))";
				else
					$emptyPthreads = "";
			}
		}
		else {
			$pThreadsOnly = "AND f2.pthread = 0";
			$emptyPthreads = "";
		}

		$subscribedOnly = "";
		$subscribedJoin = "LEFT";
		$fhUser = " AND fh.userID = $CURRENTUSERID";

		if ($filter == "subscribed") {
			$subscribedOnly = " AND fh.subscribed > 0 AND fh.subscribed < 3";
			$subscribedJoin = "";
			$emptyPthreads = "";
			if ($channelFilterList == "f1.category <> $siteSettings[flood_ID] AND " and !$CURRENTUSERFLOOD)
				$channelFilterList = "";
		}

		$hidehidden = "";
		if (($filter == "sel" || $filter == "") && $CURRENTUSER != "anonymous")
			$hidehidden = "AND (fh.userID IS NULL OR fh.subscribed < 3)";

		if ($filter == "hidden") {
			$subscribedOnly = " AND fh.subscribed = 3";
			$subscribedJoin = "";
			$emptyPthreads = "";
			if ($channelFilterList == "f1.category <> $siteSettings[flood_ID] AND " and !$CURRENTUSERFLOOD) {
				$channelFilterList = "";
				$fhUser = " AND ((f2.category = $siteSettings[flood_ID] AND fh.userID IS NULL) OR (f2.category = $siteSettings[flood_ID] AND fh.userID = $CURRENTUSERID AND fh.subscribed < 3) OR (fh.userID = $CURRENTUSERID AND fh.subscribed = 3))";
				$subscribedOnly = "";
			}
		}

		if ($CURRENTUSER != "anonymous") {
			if ($filter == "buried")
				$ratingCondition = "(f2.rating <= $CURRENTUSERDTT)";
			else
				$ratingCondition = "(f2.rating >= $CURRENTUSERDTT OR (fh.userID IS NOT NULL AND subscribed > 0 AND subscribed < 3))";
		}
		else 
			$ratingCondition = "f2.rating >= 0";
			
		if ($filter == "subscribed" or $filter == "pthread" or $search0)
			$ratingCondition = "(f2.rating > -100)";

		$orderby = "threadtype asc, lastPostDate desc";
		if ($filter == "all") {
			$ratingCondition = "(f2.rating > -100)";
			$channelFilterList = "";
			$pThreadsOnly = " AND (f2.pthread = 0 OR (f2.pthread=1 AND fh.userID IS NOT NULL)) ";
			$exclusiveChannel = "";
			$subscribedJoin = "LEFT";
			$subscribedOnly = "";
			$emptyPthreads = "";
			$threadTypeSelector = "f1.threadtype > 0 AND f1.threadtype < 3 ";
			$fhUser = " AND fh.userID = $CURRENTUSERID";
		}
			
		if ($filter == "teams") {
			$ratingCondition = "(f2.rating > -100)";
			$pThreadsOnly = " AND (f2.pthread = 0 OR (f2.pthread=1 AND fh.userID IS NOT NULL)) ";
			$subscribedJoin = "LEFT";
			$subscribedOnly = "";
			$emptyPthreads = "";
			$threadTypeSelector = "f1.threadtype > 0 AND f1.threadtype < 3 AND f1.teamID > 0 ";
			$fhUser = " AND fh.userID = $CURRENTUSERID";
		}

		$private_sticky = "IF(ftu.threadtype,ftu.threadtype,f1.threadtype) AS threadtype,";	
		if ($CURRENTUSERNOPRIVSTICKY)
			$private_sticky = "f1.threadtype,";	
		
		$selected_users = "";
		$selected_users_join = "";
		$selected_users_select = "";
		if ($siteSettings['module_friends'] && $CURRENTUSER != "anonymous" && $filter != "hidden" && $filter != "all") {
			$selected_users = "AND (f2.friendType IS NULL OR  f2.friendType != 3) ";
			$selected_users_join = " LEFT JOIN users_friends AS uf1 ON (uf1.userID = '$CURRENTUSERID' AND uf1.target_userID = f1.userID) ";
			$selected_users_select = ", uf1.friendType";
		}
		
		if ($totalCount == false) {
			$forumsStr = "SELECT
							f2.*,
							c1.name AS categoryname,
							pr.rating AS userrated,
							IFNULL(f2.$numcom - fh.num_posts, f2.$numcom) AS num_new,
							fh.date AS last_read_date
						FROM (
							SELECT
								f1.ID,
								f1.title,
								f1.body,
								f1.user,
								f1.userID,
								f1.$numcom,
								f1.num_views,
								f1.rating,
								f1.date,
								f1.category,
								f1.pthread,
								f1.$last_post_id AS lastPostID,
								f1.$last_post_date AS lastPostDate,
								f1.$last_post_user AS lastPostUser,
								$private_sticky 
								f1.stickytime,
								f1.locked,
								f1.creator_locked,
								f1.blog,
								f1.poll,
								f1.spoiler,
								f1.teamID
								$selected_users_select 
							FROM forum_topics AS f1
							$tags_join
							$selected_users_join
							LEFT JOIN forum_topics_users AS ftu ON (f1.ID = ftu.threadID AND ftu.userID = '$CURRENTUSERID') 
							WHERE
								$searchterm
								$searchuser
								$searchdate
								$exclusiveChannel
								$channelFilterList
								$exlusiveTeam
								$last_date_flag
								$team_in_pthread
								$threadTypeSelector
						) AS f2 
						LEFT JOIN postratings AS pr
							ON (pr.threadID = f2.ID AND pr.user = \"$CURRENTUSER\")
						LEFT JOIN categories AS c1
							ON (f2.category = c1.ID)
						$subscribedJoin JOIN fhits AS fh
							ON (fh.threadID = f2.ID	$fhUser $subscribedOnly) 
						WHERE 
							$ratingCondition 
						$emptyPthreads
						$selected_users 
						$hidehidden
						$pThreadsOnly
						ORDER BY f2.threadtype ASC, f2.lastPostDate DESC
					LIMIT $limitBoundary";
		}
		else if ($totalCount == true) {
			$forumsStr = "SELECT
					count(f2.id) as Expr1					
				FROM (
					SELECT
						f1.ID,
						f1.pthread,
						f1.category,
						f1.rating,
						f1.$numcom
						$selected_users_select 
					FROM forum_topics as f1
					$tags_join
					$selected_users_join
					WHERE
						$searchterm
						$searchuser
						$searchdate
						$exclusiveChannel
						$channelFilterList
						$exlusiveTeam
						$last_date_flag
						$team_in_pthread
						$threadTypeSelector
				) as f2
				$subscribedJoin JOIN fhits as fh
					ON (
						fh.threadID = f2.ID
						$fhUser
						$subscribedOnly
					) WHERE $ratingCondition
						$emptyPthreads
						$selected_users 
						$hidehidden
						$pThreadsOnly
						";
		}
		
		return $forumsStr;
		
	
	}
	
	function assembleThread($row) {
		global $LANG;
		global $siteSettings;
		global $CURRENTUSERPPP;
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTUSERRATING;
		global $CURRENTUSERAJAX;
		global $verifyEditDelete;
		global $CURRENTSTATUS;
		global $isInTeam;
		global $CURRENTUSERINTEAM;
		global $CURRENTUSERNOPRIVSTICKY;
				
		$jt = "";	
		if ($CURRENTUSERAJAX)
			$jt = "</span>";


		$numcom = "num_comments";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$numcom = "num_comments_T";

		$postRatingColorGradient = "postRatingColorGradient1";

		$row['rating'] = number_format($row['rating'], 2);

		if ($row['rating'] == 0)
			$postRatingColorGradient = "postRatingColorGradient1";		
		else if ($row['rating'] > 0)
			$postRatingColorGradient = "postRatingColorGradient2";
		else if ($row['rating'] < 0)
			$postRatingColorGradient = "postRatingColorGradient3";


		$returnStr = "";
		if ($row['num_new'] > 0) {
			$imgpath = 'engine/grafts/' . $siteSettings['graft'] . '/images/forward.png';
			$classStr = "";
		}
		else {
			$imgpath = 'engine/grafts/' . $siteSettings['graft'] . '/images/forwardGray.png';
			$classStr = "class='noNewPosts'";
		}

			$upClass = 'uparrowoff';
			$downClass = 'downarrowoff';
			$displayClass = "<div id='threadRatingStatus".$row['ID']."' style='display:none;' class='postTitle'></div>";

			if ($row['userrated'] > 0) {			
				$upClass = 'uparrowon';
				$downClass = 'downarrowoff';
			}
			else if ($row['userrated'] < 0) {			
				$upClass = 'uparrowoff';
				$downClass = 'downarrowon';
			}
	
		$row['rating'] = number_format($row['rating'], 4);
		
		$showPlusSign="";
		if ($row['rating'] > 0)
			$showPlusSign="+";
		
		$returnStr .= "<div id='post$row[ID]' class='threadInfo cat$row[category]'><!-- post -->
					<div id='rating$row[ID]' style='display: none;'>".number_format($row['rating'], 4)."</div>
					<table cellspacing='0' cellpadding='0' border='0' width='100%'>
					<tr><td style='width:36px;'>
					<div id='moderation$row[ID]' style='padding-right: 0px; text-align: center; width: 36px; float:left;'><!-- moderation -->

					<div style='margin-bottom: 1px; float: none; margin-left: auto; margin-right: auto;' class='$upClass' id='uparrowthread$row[ID]' onclick=\" toggleRatingArrow('thread', $row[ID], 'uparrow', ".number_format($CURRENTUSERRATING, 4).");\"></div>

					<span id='ratingDisplaythread$row[ID]' class='$postRatingColorGradient'>$showPlusSign".number_format($row['rating'],2)."</span>

					<div style='margin-top: 1px; float: none; margin-left: auto; margin-right: auto;' class='$downClass' id='downarrowthread$row[ID]' onclick=\" toggleRatingArrow('thread', $row[ID], 'downarrow', ".number_format($CURRENTUSERRATING, 4).");\"></div>
					$displayClass
					</div>
					</td>

					<td><table border='0' class='threadTable' width='100%'><tr>
				  ";
		$returnStr .= "<td id='newPostsToggle$row[ID]' $classStr colspan='7'>";


		// Thread Preview
		$JSS = "";
		$tcache = "";
		if (!$row['spoiler'] && !$siteSettings['mobile']) {
			$JSS = htmlspecialchars($row['body']);
			$JSS = str_replace("\r\n" , "<br />" , $JSS );
			$JSS = str_replace("\n" , "<br />" , $JSS );
			$JSS = preg_replace("/\[code\](.+?)\[\/code\]/i", "<pre class='code'>CODE</pre>", $JSS);
			$JSS = format_blurcode($JSS , true);
			$JSS = format_smilies($JSS);
			$JSS = str_replace("'", "\'", $JSS);
			$JSS = str_replace("\r", "<br />", $JSS);
			$JSS = str_replace("\t", "<br />", $JSS);
			$JSS = str_replace("\"", "\'", $JSS);
		}
		else if ($row['spoiler'] && !$siteSettings['mobile']) {
			$JSS = "<center><table cellpadding='0'><tr><td><img src='engine/grafts/$siteSettings[graft]/images/warning.png' border='0' alt='' /></td><td> <span style='font-size: 1.5em;'><b>$LANG[SPOILER]</b></span> </td><td><img src='engine/grafts/$siteSettings[graft]/images/warning.png' border='0' alt='' /></td></tr></table><br />$LANG[SPOILER_WARNING]</center>";
			$JSS = str_replace("'", "\'", $JSS);
		}

		if ($row['pthread'] == 1 && $row['num_new'] == $row[$numcom] && !$siteSettings['mobile'])
			$JSS = "<center>$LANG[NO_PREVIEW_PT]</center>";

		if ($row['lastPostUser'] == "")
			$row['lastPostUser'] = $row['user'];
			
		if ($row['categoryname'] == "Not Work Safe") {
			$NWS = " <img src='images/smilies/nws.gif' alt='Not Work Safe' /> ";
			$JSS = str_replace("<img src=", "image: ", $JSS);
			$JSS = str_replace("class=\'blurImage\' alt=\'image\'/>", "", $JSS);
		}
		else
			$NWS = "";
		
		$sticky = "";
		$usersticky = "";
		if ($row['threadtype'] == 1) {
			$sticky = "<span class='stickyNotification";
			if ($verifyEditDelete && $row['stickytime']) {
				$titlesticky = "$LANG[UNSTICK] (".date($LANG['DATE_LINE_SHORT'],$row['stickytime']).")";
				$sticky .= " jl_img' onclick=\"unstick($row[ID]);\" title='$titlesticky";
			}
			$sticky .= "'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/sticky.png' align='top' alt=\"$LANG[STICKY]\" />";
			$sticky .= "</span>&nbsp;";
			if ($CURRENTUSER != "anonymous" && !$CURRENTUSERNOPRIVSTICKY)
				$usersticky = "<span onclick=\"userunstick($row[ID]);\" title=\"$LANG[UNSTICK_PERSO]\" class='jl_img'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/sticky.png' align='top' class='edHolder' title=\"$LANG[UNSTICK_PERSO]\" alt='' /></span>&nbsp;";
		}
		else if ($CURRENTUSER != "anonymous" && !$CURRENTUSERNOPRIVSTICKY)
			$usersticky = "<span onclick=\"userstick($row[ID]);\" title=\"$LANG[SET_STICKY_PERSO]\" class='jl_img'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/sticky.png' align='top' class='edHolder' title=\"$LANG[SET_STICKY_PERSO]\" alt='' /></span>&nbsp;";


		$blog = "";
		if ($row['blog'] == 1) {
			$blog = "<span class='blogNotification'>";
			$blog .= "<a href='".make_link("blog")."'>";
			$blog .= "[$LANG[BLOG]]</a></span>&nbsp;";
		}
		if ($row['blog'] == 2)
			$blog = "<span class='blogNotification'>
				<a href=\"".make_link("blog","&amp;action=g_user&amp;user=$row[user]")."\">
				[$LANG[BLOG]]</a></span>&nbsp;";

		$private = "";
		if ($row['pthread'] == 1)
			$private = "<span class='privateNotification'>[$LANG[PRIVATE]]</span>";
			
		$privateteam = "";
		if ($row['teamID'] > 0) {
			if ($row['pthread'] == 1)
				$private = "<span class='privateNotification'>[ Team ".team_name($row['teamID'])." ]</span>";
			else
				$private = "<span class='spoilerNotification'>[ ".team_name($row['teamID'])." ]</span>";
		}

		$poll = "";
		if ($row['poll'] > 0)
			$poll = "<span class='pollNotification'>[$LANG[POLL]]</span>";

		$spoiler = "";
		if ($row['spoiler'] > 0)
			$spoiler = "<span class='spoilerNotification'>[$LANG[SPOILER]]</span>";
			
		$subscrib = mf_query("SELECT subscribed FROM fhits WHERE userID=$CURRENTUSERID and threadID=$row[ID] LIMIT 1");
		$subscrib = mysql_fetch_assoc($subscrib);
		
		$subscribed = "";
		$hidethread = "";
		$edStr = "";
		if ($CURRENTUSER != "anonymous") {
		if ($subscrib['subscribed'] > 0 && $subscrib['subscribed'] < 3)
				$subscribed = "<span class='stickyNotification jl_img' onclick=\"unsubscribe2($row[ID]);\" title=\"$LANG[UNSUBSCRIBE]\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' align='top' alt=\"$LANG[UNSUBSCRIBE]\" /></span>&nbsp;";
			else if ($subscrib['subscribed'] == 0) {
				$subscribed = "<span class='stickyNotification jl_img' onclick=\"subscribe2($row[ID]);\" title=\"$LANG[SUBSCRIBE]\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/unsubscribed.png' align='top' class='edHolder' alt=\"$LANG[SUBSCRIBE]\" /></span>&nbsp;";
				$hidethread = "<span onclick=\"hide2($row[ID]);\" title=\"$LANG[HIDE]\" class='jl_img'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/downarrowoff.gif' align='top' class='edHolder' title=\"$LANG[HIDE]\" alt=\"$LANG[HIDE]\" /></span>&nbsp;";
			}
			else if ($subscrib['subscribed'] == 3)
				$hidethread = "<span onclick=\"unhide2($row[ID]);\" title=\"$LANG[UNHIDE]\" class='jl_img'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/uparrowoff.gif' align='top' class='edHolder' title=\"$LANG[UNHIDE]\" alt=\"$LANG[UNHIDE]\" /></span>&nbsp;";
		
			if (!isset($isInTeam[$row['teamID']]))
				$isInTeam[$row['teamID']] = "";
			if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo') || ($CURRENTUSER == $row['user']) || $isInTeam[$row['teamID']] == "1") {
				$edStr = "<a title=\"$LANG[EDIT_THREAD]\" href='";
				$edStr .= make_link("forum","&amp;action=g_editThread&amp;ID=$row[ID]");
				$edStr .= "'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/edit.gif' class='edHolder' alt=\"$LANG[EDIT_THREAD]\" /></a>";
			}
		}

		$pagesStr = "";
		$isLive = "";
		$isLivej = "0";
		
		if (!is_numeric($CURRENTUSERPPP))
			$CURRENTUSERPPP = 60;
			
		$pagesNeeded = ceil($row[$numcom] / $CURRENTUSERPPP);
		if ($pagesNeeded > 1) {
			$pagesStr = "[$LANG[PAGES]: ";
			
			if ($pagesNeeded <= 8) {
				for($i=1; $i<=$pagesNeeded; $i++) {
					$comma = ",";
					if ($i == $pagesNeeded) {
						$comma = "";
						$isLive = "&amp;isLive=true";
						$isLivej = "1";
					}
						
					$pagesStr .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$row[ID]&amp;page=$i$isLive","#thread/$row[ID]/$i")."'>";
					if ($jt)
						$pagesStr .= "<span onclick=\"emptymainThread($row[ID],0,$i,$isLivej); return false;\" style='cursor:pointer;'>";
					$pagesStr .= "$i$jt</a>$comma ";
					$isLive = "";
					$isLivej = "0";
				}
			}
			else {
				for($i=1; $i<=3; $i++) {
					$comma = ",";
					if ($i == $pagesNeeded) {
						$comma = "";
						$isLive = "&amp;isLive=true";
						$isLivej = "1";
					}
						
					$pagesStr .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$row[ID]&amp;page=$i$isLive","#thread/$row[ID]/$i")."'>";
					if ($jt)
						$pagesStr .= "<span onclick=\"emptymainThread($row[ID],0,$i,$isLivej); return false;\" style='cursor:pointer;'>";
					$pagesStr .= "$i$jt</a>$comma ";
					$isLive = "";
					$isLivej = "0";
				}
				$pagesStr .= "... ";
				
				for($i=($pagesNeeded-1); $i<=$pagesNeeded; $i++) {
					$comma = ",";
					if ($i == $pagesNeeded) {
						$comma = "";
						$isLive = "&amp;isLive=true";
						$isLivej = "1";
					}
						
					$pagesStr .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$row[ID]&amp;page=$i$isLive","#thread/$row[ID]/$i")."'>";
					if ($jt)
						$pagesStr .= "<span onclick=\"emptymainThread($row[ID],0,$i,$isLivej); return false;\" style='cursor:pointer;'>";
					$pagesStr .= "$i$jt</a>$comma ";
					$isLive = "";
					$isLivej = "0";
				}
		
				
			}
			
			$pagesStr .= "]";
		}			
		else
			$isLive="&amp;isLive=true";
			
		$catNameFull = $row['categoryname'];
		if (strlen($row['categoryname']) >= 30) {
			
			$row['categoryname'] = substr($row['categoryname'], 0, 27) . "...";
		}
		
		$locked = "";
		if ($row['locked'] == 1)
			$locked = "<img src='http://".$siteSettings['siteurl']. "/engine/grafts/" . $siteSettings['graft'] . "/images/lock.gif' alt=\"$LANG[LOCKED]\" />";
			
		$externalLinks = generateExternalLinks($row['body']);	
		
		if ($isLive =="&amp;isLive=true")
			$isLivej = "1";
		else
			$isLivej = "0";

		if (!$siteSettings['mobile'])
			$JSS = "onmouseover=\"return newlayer('$JSS', 'postContent_layer', 300, event);\" onmousemove=\"return movelayer(event);\" onblur=\"return closelayer();\" onmouseout=\"return closelayer();\"";

		$returnStr .= "<span class='threadTitleText'>$NWS $locked $sticky $private $spoiler $blog $poll <a href='".make_link("forum","&amp;action=g_reply&amp;ID=$row[ID]&amp;page=1$isLive","#thread/$row[ID]/1")."' $JSS>";
		if ($jt)
			$returnStr .= "<span onclick=\"emptymainThread($row[ID],0,1,$isLivej); return false;\" style='cursor:pointer;'>";
		$returnStr .= "$tcache<!-- google_ad_section_start --> $row[title]<!-- google_ad_section_end -->$jt</a></span><span class='subThreadTitleLine' style='margin-left: 15px; float: right;'>$pagesStr</span>
						&nbsp;$externalLinks $usersticky $subscribed $hidethread $edStr
											</td></tr><tr><td class='subThreadTitleLine' width='160px'>$LANG[AUTHOR]: ";
		if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned") {
			$returnStr .= "<a href='".make_link("forum","&action=g_ep&ID=$row[userID]","#user/$row[userID]")."'>";
			if ($jt)
				$returnStr .= "<span onclick=\"userprofile(0,0,".$row['userID']."); return false;\">";
			$returnStr .= "$row[user]$jt</a>";
		}
		else
				$returnStr .= "$row[user]";
		$returnStr .= " &nbsp;- <i>" . date($LANG['DATEFORMAT'], $row['date']) . "</i>";
		if ($CURRENTUSER != "anonymous")
			$returnStr .= "</td><td width='10px'></td>
											<td width='100px' class='threadInfoTDsmall' style='text-align:left;'><span onclick=\"viewOneChannel($row[category]); return false;\" title=\"$catNameFull\" class='jl2'>$row[categoryname]</span></td>
											<td width='70px' class='threadInfoTD'>";
		else
			$returnStr .= "</td><td width='70px'></td>
											<td width='100px' class='threadInfoTDsmall' style='text-align: left;'>$row[categoryname]</td>
											<td width='70px' class='threadInfoTD'>";
		
		
		$get_new_all = total_num_new_Posts($row['ID'],$row['num_new'],$row[$numcom],$row['last_read_date'],$row['lastPostDate'] - 1,$row['lastPostID']);
		$get_new_all = explode("::arrudlm::", $get_new_all);
		
		$returnStr .= "<div id='newPosts$row[ID]' class='newposts'>".$get_new_all[1]."</div>";
		if ($get_new_all[1] && $get_new_all[2])
			$returnStr .= "<span id='newPosts_separator$row[ID]' style='display:inline;'> / </span>";
		else
			$returnStr .= "<span id='newPosts_separator$row[ID]' style='display:none;'> / </span>";
		$returnStr .= "<span id='numPosts$row[ID]' style='display:inline;' class='numposts'>".$get_new_all[2]."</span>";

			$timeAgo = time() - $row['lastPostDate'];
		if ($timeAgo < 86400) {
			if(floor($timeAgo / 3660) < 1) {
					if (floor($timeAgo / 60) <= 1)
						$timeAgo = "<span class='updateMinute'>1</span> ". $LANG['ONE_MIN_AGO'];
					else
						$timeAgo = "<span class='updateMinute'>". floor($timeAgo / 60)."</span> $LANG[MINUTES_AGO]";
				}
			else {
					if (floor($timeAgo / 3660) == 1)
						$timeAgo = "$LANG[ONE_HOUR_AGO]";
					else
						$timeAgo = floor($timeAgo / 3660)." $LANG[HOURS_AGO]";
				}
			}
		else {
				if (floor($timeAgo / 86400) == 1)
					$timeAgo = "$LANG[ONE_DAY_AGO]";
				else
					$timeAgo = floor($timeAgo / 86400)." $LANG[DAYS_AGO]";
			}	
		
		$returnStr .= "</td><td width='50px' class='threadInfoTD'>$row[num_views]</td>
						<td width='160px' class='threadInfoTDsmall' style='text-align: right;'>
						<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$row[ID]&amp;page=$pagesNeeded&amp;isLive=true#$row[lastPostID]","#thread/$row[ID]/$pagesNeeded/$row[lastPostID]")."' title=\"$LANG[JUMP_LAST_POST]\">";
		if ($jt)
			$returnStr .= "<span onclick=\"emptymainThread($row[ID],0,$pagesNeeded,1,$row[lastPostID]); return false;\">";
		$returnStr .= "".$timeAgo."$jt</a> $LANG[BY] ";
		if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned") {
			$returnStr .= "<a href=\"index.php?shard=forum&amp;action=un2id&amp;name=$row[lastPostUser]\">";
			if ($jt)
				$returnStr .= "<span onclick=\"userprofile('".urlencode($row['lastPostUser'])."'); return false;\">";
			$returnStr .= "$row[lastPostUser] $jt</a>";
		}
		else
				$returnStr .= "$row[lastPostUser] ";

		$returnStr .= "</td><td style='width:16px;text-align:center;'>";
		if ((!$row['spoiler'] && $row['pthread'] == 0) || ($row['num_new'] != $row[$numcom]))
			$returnStr .= "<span id='lastpostpreview$row[ID]' onclick=\"callAjaxShowLastPost('$row[ID]', event);\" style='cursor:pointer;'>
				<img border='0' src='engine/grafts/" . $siteSettings['graft'] . "/images/preview.png' alt=\"$LANG[LAST_POST_PREVIEW]\" />
				</span>";
		$returnStr .= "</td></tr></table></td></tr></table></div>";		

		return $returnStr;
		
	}
	
	function total_num_new_Posts($tid,$unread,$numcom,$lastread,$lastpost,$lastpostID) {
		global $LANG;
		global $CURRENTUSERAJAX;
		
		$numposts = "";
		$jt = "";
		if ($CURRENTUSERAJAX)
			$jt = "</div>";
		if (!$lastread)
			$lastread = "0";

		if ($unread > 0) {
			if ($unread == $numcom) {
				$newposts = "<a href='index.php?shard=forum&amp;action=calculatePageLocationForFirstNew&amp;ID=$tid&amp;sl=$lastread' title=\"$unread $LANG[JUMP_NEW_POSTS]\">";
				if ($jt)
					$newposts .= "<div style='display:inline-block;' onclick=\"emptymainThread($tid,$lastread,0,0); return false;\">";
				$newposts .= "<b><span id='numnewPosts$tid'>$unread</span></b>$jt</a>";
				$numposts .= "";
			}
			else {				
				$newposts = "<a href='index.php?shard=forum&amp;action=calculatePageLocationForFirstNew&amp;ID=$tid&amp;sl=$lastread' title=\"$unread $LANG[JUMP_NEW_POSTS]\">";
				if ($jt)
					$newposts .= "<div style='display:inline-block;' onclick=\"emptymainThread($tid,$lastread,0,0); return false;\">";
				$newposts .= "<b><span id='numnewPosts$tid'>$unread</span></b>$jt</a>";
				$numposts = "<a href='".make_link("forum","&amp;action=calculatePageLocationForFirstNew&amp;ID=$tid&amp;sl=$lastpost","#post/$lastpostID")."' title=\"$LANG[JUMP_LAST_POST]\">";
				if ($jt)
					$numposts .= "<div style='display:inline-block;' onclick=\"emptymainThread($tid,$lastpost,0,1); return false;\">";
				$numposts .= "<span>$numcom</span>$jt</a>";
			}
		}
		else {
			$newposts = "";
			$numposts .= "<a href='".make_link("forum","&amp;action=calculatePageLocationForFirstNew&amp;ID=$tid&amp;sl=$lastpost","#post/$lastpostID")."'>";
			if ($jt)
				$numposts .= "<div style='display:inline-block;' onclick=\"emptymainThread($tid,$lastpost,0,1); return false;\">";
			$numposts .= "$numcom$jt</a>";
		}
		$lastpost++;
		$retStr = $tid . "::arrudlm::" . $newposts . "::arrudlm::" . $numposts . "::arrudlm::" . $lastpost;
		return $retStr;
	}

	function assemblePost($thisContentObj, $row, $cur, $lastCommentID, $pthread, $blog="", $user="", $teamID="", $tid="", $page="", $categoryID, $displayThread=false,$modhidden = "none") {

		global $siteSettings;
		global $CURRENTUSERRATING;
		global $CURRENTUSERID,$CURRENTUSER;
		global $CURRENTUSERRULES;
		global $LANG;
		global $verifyEditDelete;
		global $CURRENTSTATUS;
			
		$thisContentObj->dateCreated = offsetTimezone($row['date']);
		$thisContentObj->picture = $row['avatar'];
		$thisContentObj->postid = $row['ID'];
		if ($CURRENTUSER != "anonymous" and $CURRENTSTATUS != "banned")
			$thisContentObj->rating = $row['rating'];
		$thisContentObj->pthread = $pthread;
		if ($row['userRating'] > 0)
			$thisContentObj->userRating = number_format($row['userRating'], 4);
		else 
			$thisContentObj->userRating = 0;

		if ($row['rating'] !=0)
		   	$thisContentObj->rating = number_format($row['rating'], 2);
		
		if ($row['avatar'] == "")
			$thisContentObj->picture = "engine/grafts/" . $siteSettings['graft'] . "/images/noavatar.png";

		// post footer
		$thisContentObj->sig = post_footer($row['ID'],$row['sig'],$row['notes'],$row['rating'],$modhidden,$displayThread);

		// use the user ID to access the user's profile FROM the users table
		$userid = $row['Expr1'];
		$thisContentObj->author = $row['username'];
		$thisContentObj->userID = $userid;


		$formatted = $row['body'];
//		$formatted = format_newlines($formatted);
		if (!$displayThread && $modhidden == "none")
			$formatted = format_quickquote($formatted , $row['username'], $row['ID'] );
		$formatted = format_post($formatted, false, $tid, $row['ID']);
	
		if ($row['posttype'] == 3) {
			$depub = "<span style='padding:3px; background-color: #FFFF00;'><b>$LANG[DEPUBLISHED_POST]";
			if ($row['depubDate'])
				$depub .= " $LANG[BY] $row[depubBy] $LANG[AT] ".date($LANG['TIMEFORMAT'],$row['depubDate'])." $LANG[ON] ".date($LANG['DATEFORMAT'],$row['depubDate']);
			$formatted = $depub."</b></span><div style='border-style: solid; border-color: #FFFF00;'>$formatted</div>";
		}
		if ($row['posttype'] == 4)
			$formatted = "<span style='padding:3px; background-color: #FF00FF;'><b>$LANG[DELETED_BY_CREATOR]</b></span><div style='border-style: solid; border-color: #FF00FF;'>$formatted</div>";

		if ($displayThread) {
			$thisContentObj->contentType = "contentType3";
			$thisContentObj->title = "$LANG[THREAD] $LANG[NUMBER_SHORT] <a href='".make_link("forum","&action=g_reply&ID=$displayThread","#thread/$displayThread/1")."'>".$displayThread."</a>";
			$loc = "forum_topics";
		}
		else if( $cur == 1 ) {
			// Should be the first post (OP) of the thread
			$thisContentObj->contentType = "contentType3";
			$cur = 2;
			$thisContentObj->title = "$LANG[ORIGINAL_POST] $LANG[NUMBER_SHORT] <a href='".make_link("forum","&amp;action=calculatePageLocationForFirstNew&amp;postID=$row[ID]&amp;sl=".($row['date'] -1),"#post/$row[ID]")."'>".$row['ID']."</a>";
			$loc = "forum_topics";
		}
		else if ( $cur == 2 ) {
			$thisContentObj->contentType = "contentType3";
			$cur = 3;
			$thisContentObj->title = "$LANG[CAP_POST] $LANG[NUMBER_SHORT] <a href='".make_link("forum","&amp;action=calculatePageLocationForFirstNew&amp;postID=$row[ID]&amp;sl=".($row['date'] -1),"#post/$row[ID]")."'>".$row['ID']."</a>";
			$loc = "forum_posts";
		}
		else {
			$thisContentObj->contentType = "contentType3";
			$cur = 2;
			$thisContentObj->title = "$LANG[CAP_POST] $LANG[NUMBER_SHORT] <a href='".make_link("forum","&amp;action=calculatePageLocationForFirstNew&amp;postID=$row[ID]&amp;sl=".($row['date'] -1),"#post/$row[ID]")."'>".$row['ID']."</a>";
			$loc = "forum_posts";
		}

		if ($lastCommentID == $row['ID'])
			$thisContentObj->anchor .= "<a name='last'></a><span id='postidlast'></span>";

		$thisContentObj->anchor .= "<a name='post/$row[ID]'></a><a name='$row[ID]'></a>";

		$thisContentObj->primaryContent = $formatted;

		$thisContentObj->subText2 = "";
		if ($CURRENTUSER != "anonymous") {
			$margin_signal = "84px";
			if (!$displayThread && ($row['username'] == $CURRENTUSER || $verifyEditDelete || isInGroup($CURRENTUSER, 'modo') || ($blog == "2" && $user == $CURRENTUSER && !$verifyEditDelete && !isInGroup($CURRENTUSER, 'modo')) || $teamID) && ($CURRENTSTATUS != "banned" || $teamID)) {
				$thisContentObj->subText2 = "<span class='jl' onclick=\"callAjaxShowEditWindow('$row[ID]');\">$LANG[EDIT]</span>";
				$margin_signal = "32px";
			}
			if (!$displayThread && $CURRENTSTATUS != "banned" && !$pthread && $row['userID'] != '1' && $row['username'] != $CURRENTUSER)
				$thisContentObj->subText2 .= "<span class='jl' style='margin-left:$margin_signal;' onclick=\"signal_admin($row[ID],event);\">$LANG[SIGNAL_ADMIN]</span>";
		}
		return $thisContentObj;
	}
	
	function post_footer($pid,$sig,$notes,$rating,$modhidden="none",$displayThread=false) {
		global $CURRENTSTATUS;
		global $CURRENTUSER;
		global $LANG;

		if ($sig != "")		            
			$retstr = "<div id='postsig$pid'>--<br/>" .format_newlines(format_urldetect($sig, false));
		else
			$retstr = "<div id='postsig$pid'>";
		$retstr .= "</div>";
					
		$retstr.= "<div id='postnotes$pid' class='postnotes'>";
		if ($notes != "") {
			$deblongnote = "";
			$finlongnote = "";
			if (strlen($notes) > 100) {
				$deblongnote = "<span onclick=\"toggleLayer('hiddenpostnotes".$pid ."');\" class='jl'>
					$LANG[EDIT_HISTORY]</span>
					<div id='hiddenpostnotes". $pid ."' style='display:none'>";
				$finlongnote = "</div>";
			}
			$retstr .= $deblongnote.$notes.$finlongnote;
		}
		$retstr .= "</div>";

		$retstr .= "<div id='postwhorated$pid'>";
		if (!$displayThread && $CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned")
			$retstr .= whorated($pid,$modhidden);
		$retstr .= "</div>";
		
		return $retstr;
	}

	function whorated($pid,$modhidden="none") {
		global $LANG;
		global $CURRENTUSER;
		global $siteSettings;

		$listrated = "";
		
		$ms3 = "";
		$whorated = mf_query("SELECT * FROM postratings WHERE postID='$pid' ORDER BY ID");
		while ($row2=mysql_fetch_assoc($whorated)) {
			$comment = "";
			if ($row2['comment'] != "")
				$comment = " (".$row2['comment'].")";
			$listrated .= "<div><b>$row2[user]</b> : " . number_format($row2['rating'],2) . $comment;
			if ($row2['user'] == $CURRENTUSER)
				$listrated .= "&nbsp;<span onclick=\"removerating('$row2[ID]');\" class='deleteButton'>x</span>";
			$listrated .= "</div>";
		}
		if ($listrated) {
			$ms3 = "<div style='float: left;'><span onclick=\"toggleLayer('whorated".$pid ."');\" class='jl'>$LANG[MOD_POST_LIST]</span>";
			$ms3 .= "<div class='clearfix'></div>";
			$ms3 .= "<div id='whorated".$pid ."' style='display:$modhidden; text-align: left;'>";
			$ms3 .= $listrated;
			$ms3 .= "<div class='clearfix'></div>";
			$ms3 .= "</div></div>";
		}

		return $ms3;
	}

	function submitPostToDB($message, $ID, $channelTag, $userID = 'null', $firstpost = false) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTSTATUS;
		global $siteSettings;
		global $verifyEditDelete;
			
		// Sometimes posts need to be entered in for users who are not CURRENTUSER
		if ($userID == 'null')
			$userID = $CURRENTUSERID;

		if (!is_numeric($ID))
			exit();

		// Verify if the user hasn't posted a message in the last 5 seconds
		if ($userID != "1") {
			$checkTime = time() - 3;
			$checklastpost = mf_query("SELECT userID FROM forum_posts WHERE userID='$userID' AND date > '$checkTime' LIMIT 1");
			if ($checklastpost = mysql_fetch_assoc($checklastpost))
				exit();
		}

		$numcom = "num_comments";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$numcom = "num_comments_T";

		$checkLocked = mf_query("SELECT userID, creator_locked, locked, category, pthread, title, teamID, $numcom FROM forum_topics WHERE ID='$ID'");
		if ($checkLocked = mysql_fetch_assoc($checkLocked)) {
			// Verify if user hasn't been banned since he gets into the thread
			if (!$checkLocked['teamID']) {
				$getusertatus = mf_query("SELECT userstatus FROM users WHERE username=\"$CURRENTUSER\" LIMIT 1");
				$getusertatus = mysql_fetch_assoc($getusertatus);
				if ($getusertatus['userstatus'] == "banned"){
					$CURRENTSTATUS = "banned";
					$getuserthread = mf_query("SELECT threadID FROM ban WHERE username=\"$CURRENTUSER\" ORDER BY ID DESC LIMIT 1");
					$getuserthread = mysql_fetch_assoc($getuserthread);
					if ($getuserthread['threadID'] != $ID)
						exit();
				}
			}
			if ($checkLocked['creator_locked'] == 1 && $checkLocked['userID'] != $CURRENTUSERID)
				exit();
			if ($checkLocked['locked'] == 1 && !$verifyEditDelete && !isInGroup($CURRENTUSER, 'modo') && $userID != "1")
				exit();
			// Verify if the user isn't trying to hack in a pthread
			if ($checkLocked['pthread'] == 1 && $userID != "1") {
				$checkPthread = mf_query("SELECT userID FROM fhits WHERE userID='$userID' AND threadID='$ID' LIMIT 1");
				if (!$checkPthread = mysql_fetch_assoc($checkPthread))
					exit();
			}
			
			$cleanmsg = $message;

//			if (!$verifyEditDelete and !isInGroup($CURRENTUSER, "level5"))
//				$cleanmsg = preg_replace("/\[css/i","[css",$cleanmsg);


			if ($checkLocked['locked'] != 1  || $verifyEditDelete || isInGroup($CURRENTUSER, 'modo')) {
				if( get_magic_quotes_gpc() == 1 )
					$cleanmsg = htmlspecialchars($cleanmsg);
				else
					$cleanmsg = make_var_safe(htmlspecialchars($cleanmsg));

			if (stristr($cleanmsg, "[qq.")) {
					$cleanmsg = qq_lookup( $cleanmsg, $checkLocked['pthread'], $checkLocked['category'] );

					$not_nri = mf_query("SELECT not_nri FROM categories WHERE ID='$checkLocked[category]' LIMIT 1");
				$not_nri = mysql_fetch_assoc($not_nri);

				if ($checkLocked['pthread']==0 && $not_nri['not_nri'] != "checked")
						$addUserNriQuote = mf_query("UPDATE forum_user_nri SET quote_other = (quote_other + 1) WHERE userID=$userID");
			}


				$inTime = time();
				if (!isInGroup($CURRENTUSER, 'log_ip') && (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'sysadmin') || isInGroup($CURRENTUSER, 'level9') || isInGroup($CURRENTUSER, 'level7')))
				$ip = "";
			else
				$ip=$_SERVER["REMOTE_ADDR"];

			$result = mf_query("INSERT INTO forum_posts
								(body, user, userID, date, threadID, rating, IP)
								VALUES
					(\"$cleanmsg\", \"$CURRENTUSER\", $userID, $inTime, $ID, 0, '$ip')");

			// Update the forum_topic to reflect changes
				$lastPost = mf_query("SELECT ID, user FROM forum_posts WHERE userID=$userID AND date='$inTime' ORDER BY ID limit 0,1");
			$lastPost = mysql_fetch_assoc($lastPost);				

			
			$format_votes = preg_replace("/\[vote]/i","[vote.$lastPost[ID]]",$cleanmsg);
			if ($format_votes != $cleanmsg)
				mf_query("UPDATE forum_posts SET body = '$format_votes' WHERE ID = '$lastPost[ID]' LIMIT 1");

			// Notify subscribed users that there is a new post
				notify_subscribed($ID, $checkLocked['title'],$lastPost['ID'],$inTime);


			if ($userID != 'dosystem') {
				if ($firstpost)
					$firstpost = "body = '$cleanmsg',";
					$updateComments = mf_query("UPDATE forum_topics SET $firstpost last_post_id='$lastPost[ID]', last_post_id_T='$lastPost[ID]', last_post_user=\"$lastPost[user]\", last_post_user_T=\"$lastPost[user]\", last_post_date='$inTime', last_post_date_T='$inTime', num_comments = num_comments + 1, num_comments_T = num_comments_T + 1 WHERE ID='$ID'");
					$updateChannelTag = mf_query("UPDATE categories SET num_posts = num_posts + 1 WHERE ID=$checkLocked[category]");

				// Update user's post count
				$postplusun = 0;
					$not_nri = mf_query("SELECT not_nri FROM categories WHERE ID='$checkLocked[category]' LIMIT 1");
				$not_nri = mysql_fetch_assoc($not_nri);

				if ($checkLocked['pthread'] == 0 && $not_nri['not_nri'] != "checked")
					$postplusun = 1;
					$updatePostCount = mf_query("UPDATE forum_user_nri SET num_posts = (num_posts + '$postplusun'), num_posts_notnri = (num_posts_notnri + 1)  WHERE userID=$CURRENTUSERID");

				// Update the user's NRi
				$userNRi = calculateRank($userID);
					$updateNRi = mf_query("UPDATE users SET rating=$userNRi WHERE ID=$userID LIMIT 1");

				if ($CURRENTUSER != "anonymous") {
					$paddedTime = time() + 2;
						$checkHits = mf_query("SELECT userID FROM fhits WHERE userID=$userID and threadID=$ID LIMIT 1");
					if (mysql_num_rows($checkHits) > 0)
							$fhits = mf_query("UPDATE fhits SET date='$paddedTime', num_posts = $checkLocked[$numcom] + 1 WHERE threadID='$ID' and userID=$userID LIMIT 1");
					else
							$fhits = mf_query("INSERT INTO fhits (threadID, date, userID, num_posts) VALUES ($ID, ".$paddedTime.", $userID, $checkLocked[$numcom] + 1 )");
				}
			}
		}
	}
}
	
	function notify_subscribed($ID, $title, $postID, $postDate) {
		global $LANG, $siteSettings, $CURRENTUSERID;
		
		// get subscribed users who need to be notified
		$users = mf_query("SELECT users.email, fhits.userID FROM users JOIN fhits ON fhits.userID = users.ID AND fhits.threadID = $ID AND fhits.subscribed = 2 AND users.mail_alert = 1");
		while ($row = mysql_fetch_assoc($users)) {
			if (($row['email'] != "") && ($row['userID'] != $CURRENTUSERID)) {
				srand((double)microtime()*1000000);
				$boundary = md5(uniqid(rand()));
				$header ="From: $siteSettings[titlebase] <$siteSettings[alert_mail]>\n";
				$header .="Reply-To: $siteSettings[alert_mail] \n";
				$header .="MIME-Version: 1.0\n";
				$header .="Content-Type: multipart/alternative;boundary=$boundary\n";

				$to = $row['email'];
				$subject = "$LANG[NEW_POSTS_SUBJ]: $title";

				$message = "\nThis is a multi-part message in MIME format.";
				$message .="\n--" . $boundary . "\nContent-Type: text/html;charset=\"utf-8\"\n\n";
				$message .= "<html><body>\n";
				$message .="<img src='" . $siteSettings['siteurl'] . "/engine/grafts/" . $siteSettings['graft'] . "/images/MailheaderImage.png'><br/><br/>\n";
				$message .= "\n$LANG[NEW_POSTS_MESS]<br/>\n";
				$message .= "\n$LANG[THREAD]: $title<br/>\n";
				$message .= "http://".$siteSettings['siteurl']."/".make_link("forum","&action=calculatePageLocationForFirstNew&postID=ID&sl=$postDate","#post/$postID")."<br/><br/>\n";
				$message .= "$LANG[DO_NOT_ANSWER]\n";
				$message .="\n--" . $boundary . "--\n end of the multi-part";

				@mail($to, $subject, $message, $header);
			}
		}

		$update = mf_query("UPDATE fhits SET subscribed=1 WHERE threadID='$ID' and subscribed=2");
	}
    
	function printFormattingPane($rowID="") {

		global $LANG;
		global $smiliesfile;
		global $smiliesDir;

		$retStr = "<div class='formattingPane'>";

		$retStr .= "<b><u>$LANG[SMILIES]</u></b><br/>$LANG[CLICK_2_ADD_SMILIES]<br/><center>";
/*    	$smiliesDirHandle = opendir( "images/smilies/" );
		while( ($sfile = readdir( $smiliesDirHandle )) != FALSE ) {
			if( $sfile != "." && $sfile != ".." && $sfile != ".svn") {
				$sfiles[] = $sfile;
			}
		} 	*/

		$i = 0;
		while (isset($smiliesfile[$i])) {
			$retStr .= "<img src='".$smiliesDir.$smiliesfile[$i]."' title=':".substr_replace($smiliesfile[$i],"", -4).":' alt=':".substr_replace($smiliesfile[$i],"", -4).":' onclick=\"addSmily(':".substr_replace($smiliesfile[$i],"", -4).":','$rowID');\" /> &nbsp;&nbsp;";
			$i++;
		}

        return $retStr."</center></div>";

	}
	
	function generateExternalLinks($text) {
		global $siteSettings;
		global $LANG;

		$rs = "";
		$i = 0;
		
		preg_match_all ( "/(\[url=(http|ftp)+(s)?:\/\/[^<>\s]+[\w])/i", $text, $matches );
		foreach($matches[0] as $match) {
			if (!stristr($match, "[/img") && !stristr($match, "[/embed") && !stristr($match, "[/youtube") && !stristr($match, "[/daily") && $i < 10) {
				$i ++;
				$match = substr($match,5,strlen($match)-5);
				if (stristr($match, ']'))
					$match = substr($match,0,strpos($match,']'));
				if (stristr($match, '['))
					$match = substr($match,0,strpos($match,'['));
				$rs .= "<a target='_blank' href='$match' title=\"$match\"><img border='0' src='engine/grafts/" . $siteSettings['graft'] . "/images/external.png' width='10px' alt='' /></a>&nbsp;";
			}
		}

		preg_match_all ( "/(?<![a-zA-Z0-9]=(\"|'))(?<![a-zA-Z0-9]={)(?<![a-zA-Z0-9]=)((http|ftp)+(s)?:\/\/[^<>\s]+[\w])/i", $text, $matches );
		foreach($matches[0] as $match) {
			if (!stristr($match, "[/img") && !stristr($match, "[/embed") && !stristr($match, "[/youtube") && !stristr($match, "[/daily") && $i < 10) {
				$i ++;
				if (stristr($match, ']'))
					$match = substr($match,0,strpos($match,']'));
				if (stristr($match, '['))
					$match = substr($match,0,strpos($match,'['));
				$rs .= "<a target='_blank' href='$match' title=\"$match\"><img border='0' src='engine/grafts/" . $siteSettings['graft'] . "/images/external.png' width='10px' alt='' /></a>&nbsp;";
			}
		}
		
		preg_match_all ( "/(?<![a-zA-Z0-9]=(\"|'))(?<![a-zA-Z0-9]={)(?<![a-zA-Z0-9]=)(?<!http:\/\/)((www)\.[^<>\s]+[\w])/i", $text, $matches );
		foreach($matches[0] as $match)
		{
			if (!stristr($match, "[/img") && !stristr($match, "[/embed") && !stristr($match, "[/youtube") && !stristr($match, "[/daily") && $i < 10)
			{
				$i ++;
				if (stristr($match, ']'))
					$match = substr($match,0,strpos($match,']'));
				if (stristr($match, '['))
					$match = substr($match,0,strpos($match,'['));
				$rs .= "<a target='_blank' href='http://$match' title=\"$match\"><img border='0' src='engine/grafts/" . $siteSettings['graft'] . "/images/external.png' width='10px' alt='' /></a>&nbsp;";
			}
		}		
		
		return $rs;
	}
	
	function findChildren($cs, $cpnl, $offset="") {
		global $siteSettings;
		
	   	$channelTag = "";
	   	if (array_key_exists("channel", $_REQUEST))
			$channelTag = make_num_safe($_REQUEST['channel']);



		$children = array();	
		while ($row = mysql_fetch_assoc($cs)) {
			if ($row['parent_id'] == $cpnl)
				$children[] = $row;			
		}	
		mysql_data_seek($cs, 0);

		if (count($children) > 0) { // recursive condition
			$offset .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			$rs = "";		
			foreach($children as $c) {
				$SELECTED = "";
				if (($channelTag == $c['ID']) || ($channelTag == "" && $siteSettings['dChannel'] == $c['ID']))
					$SELECTED = "checked='checked'";

				$rs .= "$offset<input type='radio' name='channelTag' value='$c[ID]' $SELECTED />$c[name]<br/>";
				$rs .= findChildren($cs, $c['ID'], $offset);

			}		
			return $rs;		
		}
		else // exit condition
			return "";	
	}
	
	function getUserList($ID) {
		global $CURRENTUSER;
		global $CURRENTSTATUS;
		global $CURRENTUSERAJAX;
		
		$jt = "";
		if ($CURRENTUSERAJAX)
			$jt = "</span>";

		if ($CURRENTUSER != "anonymous" and $CURRENTSTATUS != "banned") {
			global $siteSettings;
			global $LANG;
			global $verifyEditDelete;

			$load = 0;
			if (file_exists("/proc/loadavg")) {
				if ($siteSettings['loadavg'] > 0 && !$verifyEditDelete) {
			
					$loadavg = trim(file_get_contents('/proc/loadavg'));
					$loads = explode(" ",$loadavg);
					$load = trim($loads[0]);
				}
			}
			if ($load <= $siteSettings['loadavg'] || $siteSettings['loadavg'] == 0) {
				$laid = "";
				if ($ID != 0)
					$laid = " and laid=$ID";
			
				$s = mf_query("SELECT username, ID, lat FROM users WHERE lat > " . (time() - 1800) . " $laid AND hidemyself != '1' ORDER BY lat desc");
		
				if ($row = mysql_fetch_assoc($s)) {
					$rs  = "<a href='".make_link("forum","&amp;action=g_ep&amp;ID=$row[ID]","#user/$row[ID]")."'>";
					if ($jt)
						$rs .= "<span onclick=\"userprofile(0,'bas','$row[ID]'); return false;\">";
					$rs .= "$row[username]$jt</a>";
			
					while ($row=mysql_fetch_assoc($s)) {
						$rs .= ", <a href='".make_link("forum","&amp;action=g_ep&amp;ID=$row[ID]","#user/$row[ID]")."'>";
						if ($jt)
							$rs .= "<span onclick=\"userprofile(0,'bas','$row[ID]'); return false;\">";
						$rs .= "$row[username]$jt</a>";
					}
				}
				else
					$rs = "(none)";
			}
			else
				$rs = "($LANG[LOADAVG])";
			return $rs;
		}
	}
	
	// Thread list page list
	function getPagesString($search="",$filters="",$page="1",$channels="",$tags="",$teamID="") {
		global $LANG;
		global $CURRENTUSERDTT;
		global $CURRENTUSERID;
		global $CURRENTUSER;
		global $siteSettings;

		$retStr = "";
		if ($CURRENTUSER == "anonymous" && !$siteSettings['change_page']) {
			$retStr = "<table><tr><td><div class=\"pageCountLeft\"><small>$LANG[PAGE_DISABLED]</small></div></td></tr></table>";
			return $retStr;
			exit();
		}

		$pageCountStr = generateForumStr(0 , true, $search,$filters,$page,$channels,$tags,$teamID);

		$channelMaintain = "";
		if (array_key_exists('channel', $_REQUEST)) {
		   $chan = make_num_safe( $_REQUEST['channel']);

			$channelMaintain = "&amp;channel=$chan";
		}

		if ($filters) {
			$filter = $filters;
			if ($filter == "sel") $filter = "";
		}
		else if (array_key_exists('threadFilter', $_COOKIE)) {
			$filter = $_COOKIE["threadFilter"];
			if ($filter == "undefined") $filter = "";
		}
			

		$page = make_num_safe($page);
		
		$pageCount = mf_query($pageCountStr);
		$pageCount = mysql_fetch_assoc($pageCount);
		$numPages = ceil(($pageCount['Expr1'] / $siteSettings['threadpp']));

		if ($numPages >= 1 && $numPages < 32) {
			$retStr .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
		
			$retStr .= "$LANG[PAGES]: ";
			
			$prev_page = 0;
			$next_page = 0;
			
			
			for ( $pageCount=1; $pageCount<=$numPages; $pageCount++ ) {
				if ($page == $pageCount) {
					$pageCountStr = "class='pageListSelected'";
					$prev_page = $pageCount-1;
					if (($pageCount+1)<=$numPages)
						$next_page = $pageCount+1;
				}
				else
					$pageCountStr = "class='pageListUnSelected'";

				$isLive="";
				if ($pageCount == $numPages)
					$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
			}
			$retStr .= "</div><div class=\"pageCountRight\">";
			if ($prev_page > 0)
			{
				$retStr .= "<span onclick=\"changepage('".$prev_page."'); return false;\" class='button_mini' style='vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
			}
			if ($next_page > 0)
			{
				if ($prev_page > 0)
					$retStr .= "  ";
				if ($numPages > 1)
					$retStr .= "<span onclick=\"changepage('".$next_page."'); return false;\" class='button_mini' style='vertical-align: middle;'>$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></span>";
			}
			
			$retStr .= "</div></td></tr></table>";

		}

		if ($numPages >= 32 && $numPages <= 36)
		{
			if ($page <= 17)
			{
				$retStr .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	

								$retStr .= "$LANG[PAGES]: ";				
				$prev_page = 0;
				$next_page = 0;

				for ( $pageCount=1; $pageCount<=18; $pageCount++ )
				{
					if ($page == $pageCount)
					{
						$pageCountStr = "class='pageListSelected'";
						$prev_page = $pageCount-1;
						if (($pageCount+1)<=$numPages)
							$next_page = $pageCount+1;
					}
					else
						$pageCountStr = "class='pageListUnSelected'";


					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
				for ( $pageCount=($numPages-11); $pageCount<=$numPages; $pageCount++ )
				{
					if ($page == $pageCount)
					{
						$pageCountStr = "class='pageListSelected'";
						$prev_page = $pageCount-1;
						if (($pageCount+1)<=$numPages)
							$next_page = $pageCount+1;
					}
					else
						$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				
				$retStr .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$retStr .= "<span onclick=\"changepage('".$prev_page."'); return false;\" class='button_mini' style='vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
				}
				if ($next_page > 0)
				{
					if ($prev_page > 0)
					{
						$retStr .= "  ";
					}
					$retStr .= "<span onclick=\"changepage('".$next_page."'); return false;\" class='button_mini' style='vertical-align: middle;'>$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></span>";
				}
				
				$retStr .= "</div></td></tr></table>";
			}
			if ($page > 17)
			{
				$retStr .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
								$retStr .= "$LANG[PAGES]: ";				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=11; $pageCount++ )
				{
					if ($page == $pageCount)
					{
						$pageCountStr = "class='pageListSelected'";
						$prev_page = $pageCount-1;
						if (($pageCount+1)<=$numPages)
							$next_page = $pageCount+1;
					}
					else
						$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
				for ( $pageCount=($numPages-18); $pageCount<=$numPages; $pageCount++ )
				{
					if ($page == $pageCount)
					{
						$pageCountStr = "class='pageListSelected'";
						$prev_page = $pageCount-1;
						if (($pageCount+1)<=$numPages)
							$next_page = $pageCount+1;
					}
					else
						$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";

				}
				
				$retStr .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$retStr .= "<span onclick=\"changepage('".$prev_page."'); return false;\" class='button_mini' style='vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
				}
				if ($next_page > 0)
				{
					if ($prev_page > 0)
						$retStr .= "  ";
					$retStr .= "<span onclick=\"changepage('".$next_page."'); return false;\" class='button_mini' style='vertical-align: middle;'>$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></span>";
				}
				
				$retStr .= "</div></td></tr></table>";
			}			
		}
		
		if ($numPages > 36 && $numPages < 100)
		{
			if ($page <= 17)
			{
				$retStr .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
								$retStr .= "$LANG[PAGES]: ";				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=18; $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";

				}
				$retStr .= "... ";
				for ( $pageCount=(round($numPages/2)); $pageCount<=(round($numPages/2)+2); $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";

				}
				$retStr .= "... ";
				for ( $pageCount=($numPages-4); $pageCount<=$numPages; $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				
				$retStr .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$retStr .= "<span onclick=\"changepage('".$prev_page."'); return false;\" class='button_mini' style='vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
				}
				if ($next_page > 0)
				{
					if ($prev_page > 0)
						$retStr .= "  ";
					$retStr .= "<span onclick=\"changepage('".$next_page."'); return false;\" class='button_mini' style='vertical-align: middle;'>$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></span>";
				}
				
				$retStr .= "</div></td></tr></table>";
			}
			if ($page > 17 && $page <= $numPages-17)
			{
				$retStr .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
								$retStr .= "$LANG[PAGES]: ";				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=5; $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
				for ( $pageCount=($page -8); $pageCount<=($page +8); $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
				for ( $pageCount=($numPages-4); $pageCount<=$numPages; $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				
				$retStr .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$retStr .= "<span onclick=\"changepage('".$prev_page."'); return false;\" class='button_mini' style='vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
				}
				if ($next_page > 0)
				{
					if ($prev_page > 0)
						$retStr .= "  ";
					$retStr .= "<span onclick=\"changepage('".$next_page."'); return false;\" class='button_mini' style='vertical-align: middle;'>$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></span>";
				}
				
				$retStr .= "</div></td></tr></table>";
			}
			if ($page >  $numPages-17)
			{
				$retStr .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
								$retStr .= "$LANG[PAGES]: ";				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=5; $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
				for ( $pageCount=(round($numPages/2)-1); $pageCount<=(round($numPages/2)+1); $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
				for ( $pageCount=($numPages-16); $pageCount<=$numPages; $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

					$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";

				}
				
				$retStr .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$retStr .= "<span onclick=\"changepage('".$prev_page."'); return false;\" class='button_mini' style='vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
				}
				if ($next_page > 0)
				{
					if ($prev_page > 0)
						$retStr .= "  ";
				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				
				$retStr .= "</div></td></tr></table>";
			}
		}
		if ($numPages >= 100)
		{
			if ($page <= 18)
			{
				$retStr .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
								$retStr .= "$LANG[PAGES]: ";				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=19; $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
			
				
				$retStr .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$retStr .= "<span onclick=\"changepage('".$prev_page."'); return false;\" class='button_mini' style='vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
				}
				if ($next_page > 0)
				{
					if ($prev_page > 0)
						$retStr .= "  ";
					$retStr .= "<span onclick=\"changepage('".$next_page."'); return false;\" class='button_mini' style='vertical-align: middle;'>$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></span>";
				}
				
				$retStr .= "</div></td></tr></table>";
			}
			if ($page > 18 && $page <= $numPages-8)
			{
				$retStr .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
								$retStr .= "$LANG[PAGES]: ";				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=5; $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
				for ( $pageCount=($page -8); $pageCount<=($page +8); $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
				
				$retStr .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$retStr .= "<span onclick=\"changepage('".$prev_page."'); return false;\" class='button_mini' style='vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
				}
				if ($next_page > 0)
				{
					if ($prev_page > 0)
						$retStr .= "  ";
					$retStr .= "<span onclick=\"changepage('".$next_page."'); return false;\" class='button_mini' style='vertical-align: middle;'>$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></span>";
				}
				
				$retStr .= "</div></td></tr></table>";
			}
			if ($page >  $numPages-8)
			{
				$retStr .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
								$retStr .= "$LANG[PAGES]: ";				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=5; $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
				for ( $pageCount=(round($numPages/2)-1); $pageCount<=(round($numPages/2)+1); $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				$retStr .= "... ";
				for ( $pageCount=($numPages-7); $pageCount<=$numPages; $pageCount++ )
				{
						if ($page == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
								$next_page = $pageCount+1;
						}
						else
							$pageCountStr = "class='pageListUnSelected'";

					$isLive="";
					if ($pageCount == $numPages)
						$isLive="&amp;isLive=true";

				$retStr .= "<span onclick=\"changepage('".$pageCount."'); return false;\" $pageCountStr>$pageCount</span>";
				}
				
				$retStr .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$retStr .= "<span onclick=\"changepage('".$prev_page."'); return false;\" class='button_mini' style='vertical-align: middle;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
				}
				if ($next_page > 0)
				{
					if ($prev_page > 0)
						$retStr .= "  ";
					$retStr .= "<span onclick=\"changepage('".$next_page."'); return false;\" class='button_mini' style='vertical-align: middle;'>$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></span>";
				}
				$retStr .= "</div></td></tr></table>";
			}
		}

		if ($retStr == "")
			$retStr = "<table><tr><td><div class=\"pageCountLeft\">$LANG[PAGES]: 0</div></td></tr></table>";

		return $retStr;
	}
	
	function updateThreadLastPostInfo($threadID) {
		$u = mf_query("SELECT user, date, ID FROM forum_posts WHERE threadID='$threadID' AND posttype < 3 ORDER BY date desc LIMIT 1");
		if ($row = mysql_fetch_assoc($u))
			mf_query("UPDATE forum_topics SET last_post_id='$row[ID]', last_post_user=\"$row[user]\", last_post_date='$row[date]', num_comments=(num_comments - 1) WHERE ID='$threadID' LIMIT 1");
	}

	function updateThreadLastPostInfo2($threadID) {
		$u = mf_query("SELECT user, date, ID FROM forum_posts WHERE threadID='$threadID' AND posttype < 3 ORDER BY date desc LIMIT 1");
		if ($row = mysql_fetch_assoc($u))
			mf_query("UPDATE forum_topics SET last_post_id='$row[ID]', last_post_user=\"$row[user]\", last_post_date='$row[date]', num_comments=(num_comments + 1) WHERE ID='$threadID' LIMIT 1");
	}

	// Thread List Menu 
	function drawForumSubMenu($newPosts="", $obt=0, $newPostsList="", $filter="") {
		global $LANG;
		global $action;
		global $siteSettings;
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $CURRENTUSERDTT;
		global $CURRENTSTATUS;
		global $CURRENTUSERRULES;
		global $CURRENTUSERINTEAM;
		global $CURRENTUSERFLOOD;
		global $CURRENTUSERAJAX;
		global $CURRENTUSERTEAMINPTHREAD;
		global $CURRENTUSERUNREADPTHREAD;
		global $CURRENTUSERNOPRIVSTICKY;

		if ($action == "g_default")
			$torp = "";
		else
			$torp = $LANG['POSTS_BELOW_THRESHOLD'];

		$flood = "";
		if (!$CURRENTUSERFLOOD)
			$flood = "$siteSettings[flood_ID]";

		$newPostsStrContent = "";
		$newPostsStr = "<span id='listnewPostsStr' style='display:none;'>$newPostsList</span>
						<div id='newPostsStr' style='display:inline-block;'>";
		if ($CURRENTUSER != "anonymous"  && $CURRENTUSER != "bot")
			$newPostsStrContent = $newPosts;
		else if ($CURRENTUSER == "anonymous" && !$siteSettings['hide_filters'])
			$newPostsStrContent = "</div><div style='display:inline-block;'><small>$LANG[MISSING_OUT]</small>";
		if ($CURRENTUSERRULES == "2")
			$newPostsStrContent .= "</div><br/><div style='display:inline-block;'><small>$LANG[RULES_TEXT9]<a href='index.php?shard=forum&amp;action=g_reset_rules' class='button_mini'>$LANG[RULES_VIEW2]</a></small>";
		$newPostsStr .= $newPostsStrContent."</div>";

		$showNewThreadButton = "";
		$search = "";
		$teamName = "";
		$threadFilters = "<span id='filter' style='display:none;'>$filter</span>";
		if ($CURRENTUSER != "anonymous" && $CURRENTUSER != "bot") {
			if ($CURRENTSTATUS != "banned" && ($CURRENTUSERRULES == "1" || !$siteSettings['rules'])) {
				$showNewThreadButton = "<div style='display:inline-block;margin-top:3px;' id='NewThreadButton'><span onclick=\"callNewThreadForm();\" title=\"$LANG[CREATE_NEW_THREAD]\" class='button'>$LANG[NEW_THREAD]</span></div>";
				if (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus")
				$search = " <div style='display:inline-block;float:right;'>
					<span onclick=\"searchForm();\" title=\"$LANG[SEARCH_BUTTON]\" class='button' id='searchbtnop' style='display:block;vertical-align:top; margin-left:60px;'>$LANG[SEARCH] <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt='X' title=\"$LANG[SEARCH]\" /></span>
					<span onclick=\"searchForm();\" title=\"$LANG[SEARCH_BUTTON]\" class='button' id='searchbtncl' style='display:none;vertical-align:top;'>$LANG[SEARCH_CLOSE] <img src='engine/grafts/$siteSettings[graft]/images/menuup.gif' style='vertical-align:middle;' alt='X' title=\"$LANG[SEARCH_CLOSE]\"/></span>
</div>";
			}
			else {
				$showNewThreadButton = "<div style='display:inline-block;margin-top:3px;' id='NewThreadButton'><span title=\"$LANG[CREATE_NEW_THREAD]\" class='button' style='cursor:not-allowed;'>$LANG[NEW_THREAD]</span></div>";
				if (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus")
					$search = " <div style='display:inline-block;float:right;'>
					<span title=\"$LANG[SEARCH_BUTTON]\" class='button' id='searchbtnop' style='cursor:not-allowed;display:block;vertical-align:top; margin-left:60px;'>$LANG[SEARCH] <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt='X' title=\"$LANG[SEARCH]\" /></span>
					<span title=\"$LANG[SEARCH_BUTTON]\" class='button' id='searchbtncl' style='cursor:not-allowed;display:none;vertical-align:top;'>$LANG[SEARCH_CLOSE]<img src='engine/grafts/$siteSettings[graft]/images/menuup.gif' style='vertical-align:middle;' alt='X' title=\"$LANG[SEARCH_CLOSE]\"/></span>
</div>";
			}

			$forum_link = make_link("forum");
			
			$reload = "";
			if (!$CURRENTUSERAJAX)
				$reload = "_reload";
			$threadFilters .= "
				<div class='threadFilter bold' style='margin-right:5px;'>
					$LANG[VISIBLE]
				</div>";
			// Filter "My selection"
			$displayunreadPthread_checked = "";
			if ($CURRENTUSERUNREADPTHREAD)
				$displayunreadPthread_checked = "checked='checked'";
			$no_private_sticky_checked = "";
			if ($CURRENTUSERNOPRIVSTICKY)
				$no_private_sticky_checked = "checked='checked'";
			$threadFilters .= "
				<div class='threadFilter' onclick=\"displayFilterMenu('filterMenuSelect','');\" onmouseout=\"hideFilterMenu('filterMenuSelect','');\">
					<div class='threadTMenu".checkSelected('sel',$filter)."' id='filter5' onclick=\"displayFilterMenu('filterMenuSelect','');\">
						<a href='$forum_link#threadlist/sel' onclick=\"displayFilter$reload('sel'); return false;\" title=\"$LANG[VISIBLE_ALL] $CURRENTUSERDTT\">$LANG[MY_SELECTION]</a>
					</div>
					<div class='threadFilterMenu' id='filterMenuSelect' onmouseover=\"displayFilterMenu('filterMenuSelect','');\" onmouseout=\"hideFilterMenu('filterMenuSelect','filter5');\">
						<div class='threadFilterMenuItem'>$LANG[FILTER_OPTIONS]:</div>
						<div class='threadFilterMenuSubItem'><input type='checkbox' $displayunreadPthread_checked name='displayunreadPthread' id='displayunreadPthread'  onchange=\"displayunreadPthread();\" class='checkbox'/> $LANG[DISPLAY_UNREAD_PTHREADS]</div>
						<div class='threadFilterMenuSubItem'><input type='checkbox' $no_private_sticky_checked name='no_private_sticky' id='no_private_sticky'  onchange=\"no_private_sticky();\" class='checkbox'/> $LANG[NO_PRIVATE_STICKY]</div>
						<div style='border-top:1px dashed silver;padding-top:3px;' class='threadFilterMenuItem'><a href='".make_link("profile","&amp;action=g_settings&amp;filter=3")."'>$LANG[FORUM_SETTINGS].</a></div>
						<div class='threadFilterMenuBottom'></div>
					</div>
				</div>";
			// Filter "Private"
			$checked = "";
			if ($CURRENTUSERTEAMINPTHREAD)
				$checked = "checked='checked'";
			$threadFilters .= "
				<div class='threadFilter' onclick=\"displayFilterMenu('filterMenuPrivate','');\" onmouseout=\"hideFilterMenu('filterMenuPrivate','');\">
					<div class='threadTMenu".checkSelected('pthreads',$filter)."' id='filter1' onclick=\"displayFilterMenu('filterMenuPrivate','');\">
						<a href='$forum_link#threadlist/pthreads' onclick=\"displayFilter$reload('pthreads'); return false;\" title=\"$LANG[VISIBLE_PRIVATE]\">$LANG[PRIVATE]</a>
					</div>
					<div class='threadFilterMenu' id='filterMenuPrivate' onmouseover=\"displayFilterMenu('filterMenuPrivate','filter1');\" onmouseout=\"hideFilterMenu('filterMenuPrivate','filter1');\">
						<div class='threadFilterMenuItem'>$LANG[FILTER_OPTIONS]:</div>
						<div class='threadFilterMenuSubItem'><input type='checkbox' $checked name='displayTeaminPthread' id='displayTeaminPthread' onchange=\"team_in_pthread();\" class='checkbox'/> $LANG[DISPLAY_TEAMS_PTHREADS]</div>
						<div class='threadFilterMenuBottom'></div>
					</div>
				</div>";

			$space_right = "50";
			// Filter "Teams"
			$threadFilters .= "<input type='hidden' name='selectteam' id='selectteam' value=''/>";
			$teamName = "<div style='display:none;font-weight:bold;font-size:1.3em;' id='teamNameDiv'>Team \"<span id='teamName'></span>\"</div>";
			if ($CURRENTUSERINTEAM) {
				$listteams = "<div class='listteam'>";
				$query_team = mf_query("SELECT teams_users.teamID,teams.teamName FROM teams_users JOIN teams ON teams_users.teamID = teams.teamID WHERE teams_users.userID = '$CURRENTUSERID' AND teams_users.level < 3 ORDER BY teams.teamName");
				while ($row_team = mysql_fetch_assoc($query_team)){
					$listteams .= "<div onclick=\"selectTeam($row_team[teamID]);\" class='listteamelement' id='teamName$row_team[teamID]'>$row_team[teamName]</div>";
				}
				$listteams .= "</div>";

				$space_right = "14";
				$threadFilters .= "
					<div class='threadFilter' onmouseover=\"displayFilterMenu('filterMenuTeam','');\" onmouseout=\"hideFilterMenu('filterMenuTeam','');\">
						<div class='threadTMenu".checkSelected('teams',$filter)."' id='filter6' onmouseover=\"displayFilterMenu('filterMenuTeam','');\" onmouseout=\"hideFilterMenu('filterMenuTeam','');\">
							<a href='$forum_link#threadlist/teams' onclick=\"displayFilter$reload('teams'); return false;\" onmouseover=\"displayFilterMenu('filterMenuTeam','');\" onmouseout=\"hideFilterMenu('filterMenuTeam','');\" title=\"$LANG[TEAM_THREADS]\">$LANG[TEAM]</a>
						</div>
						<div class='threadFilterMenu' id='filterMenuTeam' onmouseover=\"displayFilterMenu('filterMenuTeam','filter6');\" onmouseout=\"hideFilterMenu('filterMenuTeam','filter6');\">
							<div class='threadFilterMenuItem'>$LANG[TEAM_YOURS]:</div>
							$listteams
							<div class='threadFilterMenuBottom'></div>
						</div>
					</div>";
			}
			else
				$threadFilters .= "<span class='threadType' id='filter6' style='padding: 0px;'></span>";
			// Filter "Subscribed"
			$threadFilters .= "<div class='threadFilter'><a href='$forum_link#threadlist/subscribed' onclick=\" displayFilter$reload('subscribed'); return false;\" class='threadType".checkSelected('subscribed',$filter)."' id='filter2' title=\"$LANG[VISIBLE_SUBSCRIBED]\">$LANG[SUBSCRIBED]</a></div>";
			// Filter "Buried"
			$threadFilters .= "<div class='threadFilter'><a href='$forum_link#threadlist/buried' onclick=\" displayFilter$reload('buried'); return false;\" class='threadType".checkSelected('buried',$filter)."' id='filter3' title=\"$LANG[VISIBLE_BURIED] $CURRENTUSERDTT\">$LANG[BURIED]</a></div>";
			// Filter "Hidden"
			$threadFilters .= "<div class='threadFilter'><a href='$forum_link#threadlist/hidden' onclick=\" displayFilter$reload('hidden'); return false;\" class='threadType".checkSelected('hidden',$filter)."' id='filter4' title=\"$LANG[VISIBLE_HIDDEN]\">$LANG[HIDDEN]</a></div>";
			// Filter "All"
			$threadFilters .= "<div class='threadFilter'><a href='$forum_link#threadlist/all' onclick=\" displayFilter$reload('all'); return false;\" class='threadType".checkSelected('all',$filter)."' id='filter0' title=\"$LANG[ALL_THREADS]\">$LANG[ALL]</a></div>";
		}
		else if (!$siteSettings['hide_filters']) {
			$showNewThreadButton = "<div style='display:inline-block;margin-top:3px;' id='NewThreadButton'><span title=\"$LANG[CREATE_NEW_THREAD]\" class='button' style='cursor:not-allowed;'>$LANG[NEW_THREAD]</span></div>";
			$search = " <div style='display:inline-block;float:right;'>
					<span title=\"$LANG[SEARCH_BUTTON]\" class='button' id='searchbtnop' style='cursor:not-allowed;display:block;vertical-align:top; margin-left:60px;'>$LANG[SEARCH] <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt=\"$LANG[SEARCH]\" /></span>
					<span title=\"$LANG[SEARCH_BUTTON]\" class='button' id='searchbtncl' style='cursor:not-allowed;display:none;vertical-align:top;'>$LANG[SEARCH_CLOSE] <img src='engine/grafts/$siteSettings[graft]/images/menuup.gif' style='vertical-align:middle;' alt=\"$LANG[SEARCH_CLOSE]\" /></span>
</div>";

			$forum_link = make_link("forum");

			$threadFilters .= "
				<b>$LANG[VISIBLE]<span style='margin-left: 5px;'></span>
				<a class='threadTypeSel' id='filter5' title=\"$LANG[VISIBLE_ALL] $CURRENTUSERDTT\" style='cursor:not-allowed;'>$LANG[MY_SELECTION]</a>

				<a class='threadType' id='filter1' title=\"$LANG[VISIBLE_PRIVATE]\" style='cursor:not-allowed;'>$LANG[PRIVATE]</a>";

			$space_right = "50";
			$threadFilters .= "<span class='threadType' id='filter6' style='padding: 0px;'></span>";
			$threadFilters .= "<a class='threadType' id='filter2' title=\"$LANG[VISIBLE_SUBSCRIBED]\" style='cursor:not-allowed;'>$LANG[SUBSCRIBED]</a>
				<a class='threadType' id='filter3' title=\"$LANG[VISIBLE_BURIED] $CURRENTUSERDTT\" style='cursor:not-allowed;'>$LANG[BURIED]</a>
				<a class='threadType' id='filter4' title=\"$LANG[VISIBLE_HIDDEN]\" style='cursor:not-allowed;'>$LANG[HIDDEN]</a>
				<a class='threadType' id='filter0' title=\"$LANG[ALL_THREADS]\" style='cursor:not-allowed;'>$LANG[ALL]</a>";
			$threadFilters .= "</b>";
		}
		else {
			$showNewThreadButton = "<div style='display:none' id='NewThreadButton'></div>";
			$search = "";
			$forum_link = make_link("forum");
			$threadFilters .= "";
			$space_right = "50";
		}
		
		$channelFilteredList = "";
		$largechan = "";
		if ($CURRENTUSER != "anonymous") {
			$channelsList = mf_query("SELECT * FROM categories ORDER BY nb");
			$rs = "<div style='margin-bottom:2px;font-size:0.8em;'><div>$LANG[SELECT_CHANNEL_TEXT1]</div><div>$LANG[SELECT_CHANNEL_TEXT2]</div></div>";
			$rs .= "<form action='index.php' name='channelFilter' method='post'>";
			if (array_key_exists('metaChannelFilter2', $_COOKIE))
				$channelFilteredList = $_COOKIE["metaChannelFilter2"];
			$CFLArray = explode(',', $channelFilteredList);
			$someFiltered = false;
			$rs .= "<table cellspacing='0'><tr><td valign='top'>";
			$numchan = mysql_num_rows($channelsList);
			$split2 = 0;
			if ($numchan < 41)
				$split1 = round(($numchan + 1) / 2);
			else {
				$split1 = round(($numchan + 2) / 3);
				$split2 = $split1 * 2;
				$largechan = "width: 568px;";
			}
			$i = 0;
			$j = 0;
			$uniquechannel = "";
			$currentChannel = "";
			$channelsStr = "";
			while ($row = mysql_fetch_assoc($channelsList)) {
				$i ++;
				if ($i == $split1)
					$rs .= "</td><td valign='top'>";
				if ($i == $split2)
					$rs .= "</td><td valign='top'>";

				$isFiltered = false;
				foreach($CFLArray as $CFLID) { // $CFLID : Channel Filtered List ID
					if ($CFLID == $row['ID']) {
						$isFiltered = true;
						if ($row['ID'] != $flood)
							$someFiltered = true;
					}
				}

				$row['name'] = trimNicely($row['name'], 45);

				if ($isFiltered)
					$rs .= "<table class='channelListingFiltered' cellspacing='0'>
							<tr>
							<td width='8px' valign='top'>
							<input type='checkbox' name='$row[ID]' id='channel$row[ID]' onclick=' return modifyChannelFilter($row[ID]);' /></td>
							<td><div><span onclick=\"modifyChannelFilterExclusive($row[ID]);\" class='jl2'>$row[name]</span></div></td>
							</tr>
							</table>";
				else {
					$rs .= "<table class='channelListing' cellspacing='0'>
							<tr>
							<td width='8px' valign='top'>
							<input checked='checked' type='checkbox' name='$row[ID]' id='channel$row[ID]' onclick=' return modifyChannelFilter($row[ID]);' /></td>
							<td><div><span onclick=\"modifyChannelFilterExclusive($row[ID]);\" class='jl2'>$row[name]</span></div></td>
							</tr>
							</table>";
					if ($row['ID'] != $flood)
						$j ++;
					$uniquechannel = trimNicely($row['name'],18);
				}
				
				if (array_key_exists('channel', $_REQUEST)) {
				if ($_REQUEST['channel'] == $row['ID'])
					$currentChannel = $row['name'];
				}

			}
			$rs .= "</td></tr></table>";		

			$resetchan = "display: inline;";
			if ($someFiltered) {
				if ($j == 1)
					$currentChannel = $uniquechannel . $currentChannel;
				else
					$currentChannel = "$LANG[CUSTOM]" . $currentChannel;
			}
			else if (!$currentChannel) {
				$currentChannel = $LANG['ALL_CHANNELS'];
				$resetchan = "display: none;";	
			}

			$currentChannel = trimNicely($currentChannel, 40);

			$channelsStr .= $rs . "</form><div style='height:4px;'></div>
				<span onclick=\"closeChannels();\" title=\"$LANG[CANCEL]\" style='float:right;' class='button'>$LANG[CANCEL]</span>
				<span onclick=\"resetChannels($flood);\" title=\"$LANG[RESET]\" style='float:right;margin-right:10px;' class='button'>$LANG[RESET]</span>
				<span onclick=\"applyChannelFilter($flood);\" title=\"$LANG[APPLY]\" style='float:right;margin-right:10px;margin-left:10px;' class='button'>$LANG[APPLY]</span>
				<span style='float: left;' id='span_chan_make_default'><input type='checkbox' name='chan_make_default' id='chan_make_default' style='vertical-align:middle;'/> <label for='chan_make_default' style='cursor:pointer;font-weight:bold;'>$LANG[CHAN_MAKE_DEFAULT]</label></span>";
		}
		else
			$channelsStr = "";
			$resetchan = "";


		$returnStr = "<span id='numpage_cache' style='display:none;'>1</span>
						<span id='chan_cache' style='display:none;'>$channelFilteredList</span>
						<span id='listthreadteam' style='display:none'></span>";
		if (!$siteSettings['hide_filters'] || $CURRENTUSER != "anonymous") {
				$returnStr .= "<div class='subMenu' id='threadfilterselector' style='display:table;width:100%;'>
						<div style='float:left;margin-bottom:4px;'>$threadFilters</div>";

			if ($CURRENTUSER != "anonymous") {
				$channelfilt = "inline-block";
				if (checkSelected('all') == "Sel")
					$channelfilt = "none";
				$teamfilt = "none";
				if (checkSelected('teams') == "Sel") {
					$teamfilt = "inline-block";
					$channelfilt = "none";
				}

				if (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus")
				$returnStr .= "<div id='channelfilt' style='margin-bottom:3px;margin-right:4px;float:right;display:$channelfilt;'>
							<span style='margin-left: ".$space_right."px;'><b>$LANG[CHANNEL]</b>:</span>
							<span id='channelsAnchor' onclick=\" showChannels();\" class='currentChannelHolder'>$currentChannel<img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt=\"$LANG[CHANNEL_LIST]\" /></span><span id='resetchan' style='vertical-align: sub; $resetchan'><span onclick=\"viewAllChannels($flood);\" style='outline:none;cursor:pointer;'> <img border='0' style='margin-top:-4px;' alt=\"$LANG[CHANNEL_SEE_ALL]\" src='engine/grafts/" . $siteSettings['graft'] . "/images/reset.png' /></span></span></div><div id='channelsWindow' style='$largechan'>$channelsStr</div>";
				else
					$returnStr .= "<div id='channelfilt' style='margin-bottom:3px;margin-right:4px;float:right;display:none;'></div>
								<span id='channelsAnchor' class='currentChannelHolder' style='display:none;'></span>
								<div id='channelsWindow' style='display:none;'></div>";

					$returnStr .= "<div id='tags_cache' style='display:none;'></div><div id='uptag_cache' style='display:none;'></div>";
			}
			else {
				if (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus")
					$returnStr .= "<div id='channelfilt' style='margin-bottom:3px;margin-right:4px;float:right;'>
							<span style='margin-left: ".$space_right."px;'><b>$LANG[CHANNEL]</b>:</span>
							<span id='channelsAnchor' class='currentChannelHolder' style='cursor:not-allowed;'>$LANG[CUSTOM]<img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt=\"$LANG[CHANNEL_LIST]\" /></span><span id='resetchan' style='vertical-align: sub; $resetchan'><span style='outline:none;cursor:not-allowed;'> <img border='0' style='margin-top:-4px;' alt=\"$LANG[CHANNEL_SEE_ALL]\" src='engine/grafts/" . $siteSettings['graft'] . "/images/reset.png' /></span></span></div><div id='channelsWindow' style='$largechan'></div>";
				else
					$returnStr .= "<div id='channelfilt' style='margin-bottom:3px;margin-right:4px;float:right;display:none;'></div>
							<span id='channelsAnchor' class='currentChannelHolder' style='display:none;'></span>
							<div id='channelsWindow' style='display:none;'></div>";

				$returnStr .= "<div id='tags_cache' style='display:none;'></div><div id='uptag_cache' style='display:none;'></div>";
			}
		}
		$returnStr .= "</div>";
//		load_tags();
		$usertotalpost = "";
		if ($CURRENTUSER != "anonymous") {
			$usertotalpost = mf_query("SELECT num_posts_notnri FROM forum_user_nri WHERE userID='$CURRENTUSERID' LIMIT 1");
			$usertotalpost = mysql_fetch_assoc($usertotalpost);
			$usertotalpost = $usertotalpost['num_posts_notnri'];
		}
		if (!$siteSettings['hide_filters'] || $CURRENTUSER != "anonymous") {
			if ($CURRENTUSERRULES != "2")
				$returnStr .= "<div class='subMenu' style='padding-bottom:11px;margin-right:4px;'><div class='subMenuLine2'><span id='usertotalpost' class='$usertotalpost'></span>
							$showNewThreadButton
							$teamName
							<div style='display:inline-block;'>$newPostsStr</div>
							$search
							<div style='display:inline-block;float:right;margin-top:3px;'><span id='indicator' class='indicator'></span></div>";
			else
				$returnStr .= "<div class='subMenu' style='padding-bottom:15px;height:32px;'><div class='subMenuLine2'>
							<div style='display:inline-block;'>$newPostsStr</div>";

			$returnStr .= "</div></div>";
		}
		else {
			$returnStr .= "<div style='display:none;'>$newPostsStr</div>$showNewThreadButton$threadFilters<div id='tags_cache' style='display:none;'></div><div id='uptag_cache' style='display:none;'></div>";
		}
		return $returnStr;
	}
	
	function trimNicely($text, $length)	{
		if (strlen($text) > $length)
			return substr($text, 0, $length) . "...";
		else
			return $text;
	}

	function checkSelected($checkStr,$filters="") {
		
		$filter = "";
		if ($filters) {
			$filter = $filters;
			if ($filter == "sel") $filter = "";
		}
		else if (array_key_exists('threadFilter', $_COOKIE)) {
			$filter = $_COOKIE["threadFilter"];
			if ($filter == "undefined") $filter = "sel";
		}
		
		if (($filter == $checkStr) || ($filter == "" && $checkStr == 'sel'))
			return "Sel";
		else
			return "";
	}
	
	// Thread Top Menu
	function drawThreadSubMenu($ID, $obt=0, $emptymain="") {
		global $siteSettings, $LANG, $CURRENTUSERID, $CURRENTUSER, $CURRENTUSERRULES, $CURRENTUSERAJAX, $shard, $CURRENTSTATUS;
		
		$hidStr = "";
		$jt = "";
		$contextmenusubscribe = "";
		$bottompage = "";
		if (!array_key_exists( "ID", $_REQUEST ) && $CURRENTUSERAJAX)
			$jt = "</span>";

		$contextmenulastread = "";
		if ($CURRENTUSER != "anonymous") {
			
			$countsubscribe = mf_query("SELECT count(userID) as Expr1 FROM fhits WHERE threadID=$ID and subscribed>0 and subscribed<3 ");
			$countsubscribe = mysql_fetch_assoc($countsubscribe);
			$counthidden = mf_query("SELECT count(userID) as Expr1 FROM fhits WHERE threadID=$ID and subscribed=3 ");
			$counthidden = mysql_fetch_assoc($counthidden);
			
			if ($countsubscribe['Expr1'] > 1)
				$totalsubscribed = "$countsubscribe[Expr1] $LANG[TOTAL_SUBSCRIBES]";
			else
				$totalsubscribed = "$countsubscribe[Expr1] $LANG[TOTAL_SUBSCRIBE]";
				
			if ($counthidden['Expr1'] > 1)
				$totalhidden = "$counthidden[Expr1] $LANG[TOTAL_HIDDENS]";
			else
				$totalhidden = "$counthidden[Expr1] $LANG[TOTAL_HIDDEN]";
				
			$checkSub = mf_query("SELECT userID FROM fhits WHERE userID='$CURRENTUSERID' and threadID='$ID' and subscribed > 0 and subscribed < 3 LIMIT 1");
			$subStr = "";
			$contextmenusubscribe = "";
			if (mysql_num_rows($checkSub) > 0) {
				$subStr = "<span onclick=\"unsubscribe($ID);\" class='button'>$LANG[UNSUBSCRIBE] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' style='vertical-align:middle;' title=\"$totalsubscribed\" alt=\"$LANG[UNSUBSCRIBE]\" /></span>";
				$contextmenusubscribe = "<div id='subscriptionNotificationcache'><div onclick=\"closelayer();unsubscribe($ID);\" class='contextMenuelement'><div class='contextMenuelementimg'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' title=\"$totalsubscribed\" alt=\"$LANG[UNSUBSCRIBE]\" /></div><div class='contextMenuelementtxt'>$LANG[UNSUBSCRIBE]</div></div></div>";
				mf_query("UPDATE fhits SET subscribed=2 WHERE userID='$CURRENTUSERID' and threadID='$ID' LIMIT 1");
			}
			else {
				$subStr = "<span onclick=\"subscribe($ID);\" class='button'>$LANG[SUBSCRIBE] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' style='vertical-align:middle;' title=\"$totalsubscribed\" alt=\"$LANG[SUBSCRIBE]\" /></span>";
				$contextmenusubscribe = "<div id='subscriptionNotificationcache'><div onclick=\"closelayer();subscribe($ID);\" class='contextMenuelement'><div class='contextMenuelementimg'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' title=\"$totalsubscribed\" alt=\"$LANG[SUBSCRIBE]\" /></div><div class='contextMenuelementtxt'>$LANG[SUBSCRIBE]</div></div></div>";
			}
			$contextmenulastread = "<div class='contextMenuelementimg'></div><div class='contextMenuelementtxt'><hr/></div>
			<div onclick=\"closelayer();set_last_unread($ID);\" class='contextMenuelement'><div class='contextMenuelementimg'></div><div class='contextMenuelementtxt'>$LANG[SET_AS_LAST_READ]</div><span id='contextmenupostID' style='display:none;'></span></div>";
			$checkSub = mf_query("SELECT userID FROM fhits WHERE userID=$CURRENTUSERID and threadID=$ID and subscribed = 3 LIMIT 1");
			$hidStr = "";
			if (mysql_num_rows($checkSub) > 0) {
				$hidStr = "<span onclick=\"unhide($ID);\" class='button'>$LANG[UNHIDE] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/uparrowoff.gif' style='vertical-align:middle;' title=\"$totalhidden\" alt=\"$LANG[UNHIDE]\" /></span>";
			}
			else
				$hidStr = "<span onclick=\"hide($ID);\" class='button'>$LANG[HIDE] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/downarrowoff.gif' style='vertical-align:middle;' title=\"$totalhidden\" alt=\"$LANG[HIDE]\" /></span>";
				
		}
		else if ($CURRENTUSER == "anonymous" || ($CURRENTUSERRULES != "1" && $siteSettings['rules']))
			$subStr = "";
		
		$searchinthread = "";
		$contextmenusearchinthread = "";
		if ($emptymain == "2" && $jt) {
			$searchinthread = "<span class='button' onclick=\"emptymain$emptymain($ID,'$CURRENTUSERID','','',$ID); return false;\">$LANG[SEARCH_IN_THREAD]</span>";
			$contextmenusearchinthread = "<div onclick=\"closelayer();emptymain$emptymain($ID,'$CURRENTUSERID','','',$ID); return false;\" class='contextMenuelement'><div class='contextMenuelementimg'></div><div class='contextMenuelementtxt'>$LANG[SEARCH_IN_THREAD]</div></div>";
		}
		
		$contextmenubackthreadlist = "<div onclick=\"";
		if ($jt)
			$contextmenubackthreadlist .= "closelayer();emptymain$emptymain($ID,'$CURRENTUSERID'); return false;\"";
		else
			$contextmenubackthreadlist .= "closelayer();location.href='".make_link($shard)."'\"";
		$contextmenubackthreadlist .= "class='contextMenuelement'>
			<div class='contextMenuelementimg'>
			<img src='engine/grafts/$siteSettings[graft]/images/arrow_left.png' alt=\"$LANG[BACK_THREAD_LIST]\" />
			</div><div class='contextMenuelementtxt'>$LANG[BACK_THREAD_LIST]</div></div>";

		$contextmenu = "<div id='contextmenucache' style='display:none;'>
			$contextmenubackthreadlist
			<div onclick=\"closelayer();scrolltoID('bottom_page_button');\" class='contextMenuelement'><div class='contextMenuelementimg'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_up.png' alt=\"$LANG[TOP_PAGE]\" /></div><div class='contextMenuelementtxt'>$LANG[TOP_PAGE]</div></div>
			<div onclick=\"closelayer();scrolltoID('top_page_button');\" class='contextMenuelement'><div class='contextMenuelementimg'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_down.png' alt=\"$LANG[BOTTOM_PAGE]\" /></div><div class='contextMenuelementtxt'>$LANG[BOTTOM_PAGE]</div></div>
			$contextmenusubscribe
			$contextmenusearchinthread
			$contextmenulastread";


		$contextmenu .= "</div>";

		
		$returnStr = "<div style='margin-left:92px;white-space:nowrap;margin-top:6px;margin-bottom:6px;'>
						<span id='subscriptionNotification'>$subStr</span>
					&nbsp; <span id='hideNotification'>$hidStr</span>
					&nbsp; $searchinthread
					</div>";
		$returnStr .= $contextmenu;

		return $returnStr;		
	}
	
	function renderPollResults($poll_var) {
		global $LANG, $CURRENTUSER, $CURRENTUSERID;
		$returnStr = "";
		
			// Get poll info
			$pollInfo = mf_query("SELECT * FROM poll_topics WHERE ID = $poll_var LIMIT 1");
			$pollInfo = mysql_fetch_assoc($pollInfo);
			$pollOptions = mf_query("SELECT * FROM poll_answers WHERE poll_ID = $poll_var ORDER BY ID");			
			$pollResponses = mf_query("SELECT * FROM poll_responses WHERE poll_ID = $poll_var");
			
			$returnStr .= "<div id='submittingVoteIndicator' class='indicator'></div><span class='pollTitle'>$LANG[POLL]: $pollInfo[question]</span>";
			

			// If person hasn't voted yet, show them poll options
			$hasVoted = false;
			$totalVotes = 0;
			while ($row=mysql_fetch_assoc($pollResponses))
			{
				if (($CURRENTUSERID == $row['user_ID']) || $CURRENTUSER == "anonymous")
					$hasVoted = true;
					
				$totalVotes ++;
			}
			
			if (!$hasVoted)
			{
				$optionsDisplay = 'block';
				$resultsDisplay = 'none';
			}
			else if ($hasVoted)
			{
				$optionsDisplay = 'none';
				$resultsDisplay = 'block';
			}			
			
			if ($totalVotes > 0)
				mysql_data_seek($pollResponses, 0);
			
			$returnStr .= "<div id='pollQuestionsHolder' class='pollContents' style='display:$optionsDisplay;'>
											$LANG[PLEASE_ANSWER]
											<div class='pollContents'><form action='index.php' name='$pollInfo[ID]pollChoice' id='pollChoice' method='post'>";
			while ($row = mysql_fetch_assoc($pollOptions))
			{
				$returnStr .= "<input type='radio' name='answer' value='$row[ID]' /> $row[answer]<br/>";
			}											
											
			$returnStr .= "</form><br/><input name='$pollInfo[ID]submit' type='submit' class='button' value=\"$LANG[VOTE]\" onclick=\" submitPollVote($pollInfo[ID]);\" /></div></div>"; // Ends poll options div
			
			
			// Show Poll Results //
			$returnStr .= "<div id='pollResultsHolder' class='pollContents' style='display: $resultsDisplay;'>";
			
			if (mysql_data_seek($pollOptions, 0))
			{			
				while ($row = mysql_fetch_assoc($pollOptions))
				{
					// Count up how many people picked this option
					$votes = 0;
					while ($responses = mysql_fetch_assoc($pollResponses))
					{
						if ($responses['answer_ID'] == $row['ID'])
							$votes++;
					}
					
					if ($totalVotes > 0)
					{
						$voteSize = 300 / $totalVotes;
						$votePercent = $votes / $totalVotes * 100;
					}
					else
					{
						$voteSize = 0;
						$votePercent = 0;
					}
					
					$returnStr .= "<div class='pollLegend'>$row[answer] ($votes $LANG[VOTES2])</div>
												 <div class='pollGraphBG'>
												 	<div class='pollGraphBar' style='width: ".($voteSize * $votes)."px;'></div>
												 	<span style='margin-left: 5px;'><small>".number_format($votePercent, 2)."%</small></span>
												 </div>
												 <div class='clearfix'></div>";
					
					if ($totalVotes > 0)
						mysql_data_seek($pollResponses, 0);
				}
			}
			else
				$returnStr .= "Couldn't SET pollOptions or pollResponses back to 0 index";
				
			$returnStr .= "</div>";
			$returnStr .= "Total Votes: $totalVotes";
				
		return $returnStr;
	}	

	function replyForm($channelTag,$tid,$isLive,$lastPostTimeStamp,$page) {
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $LANG;
		global $siteSettings;

		$replyForm = "<div id='lastPostTimeStamp' class='$lastPostTimeStamp'></div>";
   
		if ($channelTag['locked'] == 1 && !isInGroup($CURRENTUSER, 'admin'))
			$replyForm .= "<img src='http://".$siteSettings['siteurl']. "/engine/grafts/" . $siteSettings['graft'] . "/images/lock.gif' alt=''' /> $LANG[LOCKED_DISABLED].";
		else if ($channelTag['creator_locked'] == 1 && $channelTag['userID'] != $CURRENTUSERID)
			$replyForm .= "<img src='http://".$siteSettings['siteurl']. "/engine/grafts/" . $siteSettings['graft'] . "/images/lock.gif' alt='' /> $LANG[CREATOR_LOCKED_DISABLED].";
		else {
			if ($channelTag['creator_locked'] == 1 && $channelTag['userID'] == $CURRENTUSERID)
				$replyForm .= "<img src='http://".$siteSettings['siteurl']. "/engine/grafts/" . $siteSettings['graft'] . "/images/lock.gif' alt='' /> <span class='bold'>".$LANG['CREATOR_LOCKED_DISABLED_TEXT']."</span>";

			if ( array_key_exists( "isLive", $_REQUEST ) or $isLive == "1")
				$replyForm .= "<form action='index.php' name='replyForm' id='replyForm' onsubmit=\"return submitPost('$channelTag[categoryID]', $tid, 1); return false;\" method='post'>";  
			else
				$replyForm .= "<form action='index.php' name='replyForm' id='replyForm' onsubmit=\"submitPost('$channelTag[categoryID]', $tid, 0); return false;\" method='post'>";  

			$replyForm .= "$LANG[POSTING_AS] <b>$CURRENTUSER</b>. 
				<small>(<a href='".make_link("login","&amp;action=logout")."'>$LANG[NOT_YOU]</a>)</small><br />";

			$replyForm .= "<small>$LANG[POST_BODY]:</small>
				<br /><span id='postidreply'></span>
			<table><tr><td style='vertical-align: top;'>";
			$onfocus = "";
			$onblur = "";
			if (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus") {
				$onfocus = "onfocus=\"previewPost('1'); return false;\"";
				$onblur = "onblur=\"previewPost_lostfocus('1'); return false;\"";
			}
			$replyForm .= "<div><textarea $onfocus $onblur id='postArea' class='post_textarea' name='message' rows='14' cols='65'></textarea></div>
			<input type='hidden' name='ID' value='" . $tid . "' />
			<input type='hidden' name='channelTag' value=\"$channelTag[categoryID]\" />
			<input name='replySubmit' type='submit' value=\"$LANG[SUBMIT]\" style='margin-left: 0px; float:left;' class='button' />";
			$replyForm .= printFormattingPaneB();
			if (!$siteSettings['mobile'] || $siteSettings['full_site'] == "mobilesiteplus")
				$replyForm .="<div id='previewPostT' style='margin-top:28px;'></div>
						<div id='previewPost' class='previewPost2' style='display: none;' onclick=\"StoppreviewPost('1');\"></div>";
			$replyForm .="</td><td style='vertical-align: top;'>".printFormattingPane()."</td></tr></table></form>";
		}
		return $replyForm;
	}

	function printFormattingPaneB($postID="") {
		global $siteSettings;
		global $LANG;

		$format_margin = "24";
		if (!$postID)
			$format_margin = "40";
		$retstr = "
			<span class='bt_style' id='bt_b$postID' style='margin-left: ".$format_margin."px;' onclick=\"pushBt('b','$postID');\" title=\"$LANG[FONT_BOLD]\"><b>B</b></span>
			<span class='bt_style' id='bt_u$postID' onclick=\"pushBt('u','$postID');\" title=\"$LANG[FONT_UNDERLINE]\"><u>U</u></span>
			<span class='bt_style' id='bt_i$postID' onclick=\"pushBt('i','$postID');\" title=\"$LANG[FONT_ITAL]\"><i>I</i></span>
			<span class='bt_style' id='bt_s$postID' onclick=\"pushBt('s','$postID');\" title=\"$LANG[FONT_STRIKE]\"><strike>S</strike></span>
			<span class='bt_style' id='bt_img$postID' onclick=\"pushBt('img','$postID');\" title=\"$LANG[BB_LINK_PICT]\">[img]</span>
			<div class='displayDiv' id='info_img$postID'>
				<div><div style='display:inline-block;width:40px;'>$LANG[BB_LINK_URL]:</div><input type='text' value='' size='60' class='bselect' id='url_img$postID'/></div>
				<div style='margin-top:8px;float:right;'>
					<span class='button' onclick=\"closeDiv('info_img$postID','url_img$postID');\">$LANG[CANCEL]</span> &nbsp; 
					<span class='button' onclick=\"pushBt('img','$postID');\">$LANG[SUBMIT]</span>
				</div>
			</div>
			<span class='bt_style' id='bt_url$postID' onclick=\"pushBt('url','$postID');\" title=\"$LANG[FORMPANE_URL]\">[url]</span>
			<div class='displayDiv' id='info_url$postID'>
				<div><div style='display:inline-block;width:40px;'>$LANG[BB_LINK_URL]:</div><input type='text' value='' size='60' class='bselect' id='url_url$postID'/></div>
				<div style='margin-top:4px;'><div style='display:inline-block;width:40px;'>Texte :</div><input type='text' value='' size='40' class='bselect' id='text_url$postID'/></div>
				<div style='margin-top:8px;float:right;'>
					<span class='button' onclick=\"closeDiv('info_url$postID','url_url$postID','text_url$postID');\">$LANG[CANCEL]</span> &nbsp; 
					<span class='button' onclick=\"pushBt('url','$postID');\">$LANG[SUBMIT]</span>
				</div>
			</div>
			<span class='bt_style' id='bt_spoiler$postID' onclick=\"pushBt('spoiler','$postID');\" title=\"$LANG[FORMPANE_SPOILER]\">[spoiler]</span>";
		$retstr .= "<div class='bt_style' style='width:110px;margin-left:400px;max-height:20px;overflow:hidden;z-index:2;position:absolute;' id='more_format_on_$postID' onmouseover=\"document.getElementById('more_format_on_$postID').style.maxHeight = '110px';\" onmouseout=\"document.getElementById('more_format_on_$postID').style.maxHeight = '20px';\">
				<span onclick=\"document.getElementById('more_format_on_$postID').style.maxHeight = '110px';\">$LANG[FORMPANE_MORE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/menudown.gif' alt='+' /><span>
		<div style='cursor:auto;margin-top:8px;text-align:left;'>
			<div id='format_option_1_$postID' class='format_option' onclick=\"format_option('1','$postID');\">$LANG[FORMPANE_MEDIAS]</div>
			<div id='format_option_2_$postID' class='format_option' onclick=\"format_option('2','$postID');\">$LANG[FORMPANE_FORM]</div>
			<div id='format_option_3_$postID' class='format_option' onclick=\"format_option('3','$postID');\">$LANG[FORMPANE_PUB]</div>
			<div id='format_option_4_$postID' class='format_option' onclick=\"format_option('4','$postID');\">$LANG[FORMPANE_OTHER]</div>
		</div>
	</div>
	<div style='margin-top:24px;z-index:1;'>
		<div id='option_1_$postID' style='display:none;'>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'>$LANG[FORMPANE_MEDIAS_TIT]</span>
				<span style='float:left;'>$LANG[FORMPANE_MEDIAS_DED]</span>
			</div>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'> &nbsp; </span>";
		$retstr .= "<span class='bt_style' id='bt_daily$postID' onclick=\"pushBt('daily','$postID');\"  title=\"DailyMotion\">[daily]</span>
				<div class='displayDiv' id='info_daily$postID'>
					<div>
						<div>$LANG[FORMPANE_MEDIAS_NAME]</div>
						<input type='text' value='' size='60' class='bselect' id='url_daily$postID'/>
					</div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_daily$postID','url_daily$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('daily','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>";
		$retstr .= "<span class='bt_style' id='bt_youtube$postID' onclick=\"pushBt('youtube','$postID');\" title=\"YouTube\">[youtube]</span>
				<div class='displayDiv' id='info_youtube$postID'>
					<div>
						<div>$LANG[FORMPANE_MEDIAS_NAME_URL]</div>
						<input type='text' value='' size='60' class='bselect' id='url_youtube$postID'/>
					</div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_youtube$postID','url_youtube$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('youtube','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>";
		if ($siteSettings['metacafe'])
			$retstr .= "<span class='bt_style' id='bt_metacafe$postID' onclick=\"pushBt('metacafe','$postID');\" title=\"Metacafe\">[metacafe]</span>
				<div class='displayDiv' id='info_metacafe$postID'>
					<div>
						<div>$LANG[FORMPANE_MEDIAS_NAME]</div>
						<input type='text' value='' size='60' class='bselect' id='url_metacafe$postID'/>
					</div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_metacafe$postID','url_metacafe$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('metacafe','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>";
		if ($siteSettings['deezer'])
			$retstr .= "<span class='bt_style' id='bt_deezer$postID' onclick=\"pushBt('deezer','$postID');\" title=\"Deezer\">[deezer]</span>
				<div class='displayDiv' id='info_deezer$postID'>
					<div>
						<div>$LANG[FORMPANE_MEDIAS_DEEZER]</div>
						<input type='text' value='' size='60' class='bselect' id='url_deezer$postID'/>
					</div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_deezer$postID','url_deezer$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('deezer','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>";
		$retstr .= "</div>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'> &nbsp; </span>
				<span style='float:left;'>$LANG[FORMPANE_MEDIAS_OTHER]</span>
			</div>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'> &nbsp; </span>	
				<span class='bt_style' id='bt_object$postID' onclick=\"pushBt('object','$postID');\" title=\"$LANG[FORMPANE_MEDIAS_OBJECT_T1]\">[object]</span><span style='padding-top:5px;font-size:0.8em;float:left;margin-left:4px;'>($LANG[FORMPANE_MEDIAS_OBJECT_T2])</span>
				<div class='displayDiv' id='info_object$postID'>
					<div>
						<div style='display:inline-block;width:40px;'>Lien :</div>
						<textarea cols='60' rows='5' class='bselect' id='url_object$postID'></textarea>
					</div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_object$postID','url_object$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"format_object('$postID');\">$LANG[SUBMIT]</span>
					</div>
					<span id='formated_object$postID' style='display:none;'></span>
				</div>
			</div>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'> &nbsp; </span>	
				<span style='float:left;'>$LANG[FORMPANE_MEDIAS_SPECIAL]</span>
			</div>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'> &nbsp; </span>
				<span class='bt_style' id='bt_video$postID' onclick=\"pushBt('video','$postID');\" title=\"$LANG[FORMPANE_MEDIAS_VIDEO]\">[video]</span><span style='padding-top:5px;font-size:0.8em;float:left;margin-left:4px;'>($LANG[FORMPANE_MEDIAS_VIDEO_T])</span>
				<div class='displayDiv' id='info_video$postID'>
					<div>
						<div>$LANG[FORMPANE_MEDIAS_VIDEO_5]</div>
						<input type='text' value='' size='60' class='bselect' id='url_video$postID'/>
					</div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_video$postID','url_video$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('video','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>
			</div>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'> &nbsp; </span>	
				<span class='bt_style' id='bt_media$postID' onclick=\"pushBt('media','$postID');\" title=\"Flash\">[media]</span><span style='padding-top:5px;font-size:0.8em;float:left;margin-left:4px;'>($LANG[FORMPANE_MEDIAS_FLASH_T1])</span>
				<div class='displayDiv' id='info_media$postID'>
					<div>
						<div>$LANG[FORMPANE_MEDIAS_FLASH_T2]</div>
						<input type='text' value='' size='60' class='bselect' id='url_media$postID'/>
					</div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_media$postID','url_media$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('media','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>
			</div>
		</div>
		<div id='option_2_$postID' style='display:none;'>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'>$LANG[FORMPANE_FORM_T]</span>
				<span class='bt_style' id='bt_size$postID' style='width:38px;' onclick=\"pushBt('size','$postID','');\" title=\"$LANG[FORMPANE_FORM_SIZE]\">[size=]</span>
				<div class='displayDiv' id='info_size$postID'>
					<span class='bt_style' style='width:38px;font-size:.75em;' onclick=\"pushBt('size','$postID','2');\">[size=2]</span>
					<span class='bt_style' style='width:54px;font-size:1.25em;' onclick=\"pushBt('size','$postID','4');\">[size=4]</span>
					<span class='bt_style' style='width:74px;font-size:1.75em;' onclick=\"pushBt('size','$postID','6');\">[size=6]</span>
					<span class='bt_style' style='width:97px;font-size:2.25em;' onclick=\"pushBt('size','$postID','8');\">[size=8]</span>
					<span class='bt_style' style='width:132px;font-size:2.75em;' onclick=\"pushBt('size','$postID','10');\">[size=10]</span>
					<div>
						<div style='display:inline-block;width:40px;'>Taille :</div>
						<input type='text' value='' size='2' class='bselect' id='val_size$postID'/>
					</div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_size$postID','val_size$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('size','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>
				<span class='bt_style' id='bt_color$postID' onclick=\"pushBt('color','$postID','');\" title=\"$LANG[FORMPANE_FORM_COLOR]\">[color=]</span>
				<div class='displayDiv' id='info_color$postID'>
					<span class='bt_style_color' style='color:Blue;' onclick=\"pushBt('color','$postID','Blue');\">[Blue]</span>
					<span class='bt_style_color' style='color:Red;' onclick=\"pushBt('color','$postID','Red');\">[Red]</span>
					<span class='bt_style_color' style='color:Green;' onclick=\"pushBt('color','$postID','Green');\">[Green]</span>
					<span class='bt_style_color' style='color:Gold;' onclick=\"pushBt('color','$postID','Gold');\">[Gold]</span>
					<span class='bt_style_color' style='color:Navy;' onclick=\"pushBt('color','$postID','Navy');\">[Navy]</span>
					<div style='clear:both;height:6px;'></div>
					<span class='bt_style_color' style='color:HotPink;' onclick=\"pushBt('color','$postID','HotPink');\">[HotPink]</span>
					<span class='bt_style_color' style='color:Magenta;' onclick=\"pushBt('color','$postID','Magenta');\">[Magenta]</span>
					<span class='bt_style_color' style='color:DarkTurquoise;' onclick=\"pushBt('color','$postID','DarkTurquoise');\">[Turquoise]</span>
					<span class='bt_style_color' style='color:Maroon;' onclick=\"pushBt('color','$postID','Maroon');\">[Maroon]</span>
					<span class='bt_style_color' style='color:Grey;' onclick=\"pushBt('color','$postID','Grey');\">[Grey]</span>
					<div style='clear:both;height:6px;'></div>
					<span class='bt_style_color' style='color:Purple;' onclick=\"pushBt('color','$postID','Purple');\">[Purple]</span>
					<span class='bt_style_color' style='color:Highlight;' onclick=\"pushBt('color','$postID','Highlight');\">[Highlight]</span>
					<span class='bt_style_color' style='color:Lime;' onclick=\"pushBt('color','$postID','lime');\">[lime]</span>
					<span class='bt_style_color' style='color:BlueViolet;' onclick=\"pushBt('color','$postID','BlueViolet');\">[BlueViolet]</span>
					<span class='bt_style_color' style='color:DarkOrange;' onclick=\"pushBt('color','$postID','DarkOrange');\">[Orange]</span>
					<div style='clear:both;width:100%;text-align:center;padding-top:8px;'>
						<div style='width:162px;display:inline-block;'>
							<div style='text-align:left;'>
								<div style='display:inline-block;width:50px;'>$LANG[FORMPANE_FORM_COLOR_T]</div>
								<input type='text' value='' size='16' class='bselect' id='val_color$postID'/>
							</div>
							<div style='margin-top:8px;text-align:right;'>
								<span class='button' onclick=\"closeDiv('info_color$postID','val_color$postID');\">$LANG[CANCEL]</span> &nbsp; 
								<span class='button' onclick=\"pushBt('color','$postID');\">$LANG[SUBMIT]</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id='option_3_$postID' style='display:none;'>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'>$LANG[FORMPANE_PUB_T]</span>
				<span class='bt_style' id='bt_br$postID' onclick=\"pushBt('br','$postID');\" title=\"$LANG[FORMPANE_PUB_BR]\">[br]</span>
				<span class='bt_style' id='bt_hr$postID' onclick=\"pushBt('hr','$postID');\" title=\"$LANG[FORMPANE_PUB_HR]\">[hr]</span>
				<span class='bt_style' id='bt_center$postID' onclick=\"pushBt('center','$postID');\" title=\"$LANG[FORMPANE_PUB_CENTER]\">[center]</span>
				<span class='bt_style' id='bt_justify$postID' onclick=\"pushBt('justify','$postID');\" title=\"$LANG[FORMPANE_PUB_JUSTIFY]\">[justify]</span>
				<span class='bt_style' id='bt_blocl$postID' onclick=\"pushBt('blocl','$postID');\" title=\"$LANG[FORMPANE_PUB_LEFT]\">[blocl]</span>
				<span class='bt_style' id='bt_blocr$postID' onclick=\"pushBt('blocr','$postID');\" title=\"$LANG[FORMPANE_PUB_RIGHT]\">[blocr]</span>
			</div>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'> &nbsp;</span>
				<span class='bt_style' id='bt_ul$postID' onclick=\"pushBt('ul','$postID');\" title=\"$LANG[FORMPANE_PUB_UL]\">[ul]</span>
				<span class='bt_style' id='bt_ol$postID' onclick=\"pushBt('ol','$postID');\" title=\"$LANG[FORMPANE_PUB_OL]\">[ol]</span>
				<span class='bt_style' id='bt_li$postID' onclick=\"pushBt('li','$postID');\" title=\"$LANG[FORMPANE_PUB_LI]\">[li]</span>
			</div>
		</div>
		<div id='option_4_$postID' style='display:none;'>
			<div style='clear:both;padding-top:8px;'><span style='float:left;width:100px;'>$LANG[FORMPANE_OTHER_T]</span>
				<span class='bt_style' id='bt_code$postID' onclick=\"pushBt('code','$postID');\" title=\"$LANG[FORMPANE_OTHER_CODE]\">[code]</span>
				<span class='bt_style' id='bt_quote$postID' onclick=\"pushBt('quote','$postID');\" title=\"$LANG[FORMPANE_OTHER_QUOTE]\">[quote]</span>
				<span class='bt_style' id='bt_iurl$postID' onclick=\"pushBt('iurl','$postID');\" title=\"$LANG[FORMPANE_OTHER_IURL]\">[iurl]</span>
				<div class='displayDiv' id='info_iurl$postID'>
					<div>
						<div style='display:inline-block;width:40px;'>$LANG[FORMPANE_OTHER_IURL_LINK]</div>
						<input type='text' value='' size='60' class='bselect' id='url_iurl$postID'/>
					</div>
					<div style='margin-top:4px;'>
						<div style='display:inline-block;width:40px;'>$LANG[FORMPANE_OTHER_IURL_TEXT]</div>
						<input type='text' value='' size='40' class='bselect' id='text_iurl$postID'/>
					</div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_iurl$postID','url_iurl$postID','text_iurl$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('iurl','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>
				<span class='bt_style' id='bt_name$postID' onclick=\"pushBt('name','$postID');\" title=\"$LANG[FORMPANE_OTHER_NAME]\">[name]</span>
				<div class='displayDiv' id='info_name$postID'>
					<div><div style='display:inline-block;width:40px;'>Code :</div><input type='text' value='' size='20' class='bselect' id='url_name$postID'/></div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_name$postID','url_name$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('name','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>
				<span class='bt_style' id='bt_album$postID' onclick=\"pushBt('album','$postID');\" title=\"$LANG[FORMPANE_OTHER_ALBUM]\">[album]</span>
				<div class='displayDiv' id='info_album$postID' style='width:600px;'>
					<div>$LANG[FORMPANE_OTHER_ALBUM_ID] <input type='text' value='' class='bselect' id='num_album$postID'/></div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_album$postID','num_album$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('album','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>
				<span class='bt_style' id='bt_pict$postID' onclick=\"pushBt('pict','$postID');\" title=\"$LANG[PICTURES_ALBUM_HERE]\">[pict]</span>
				<div class='displayDiv' id='info_pict$postID' style='max-width:600px;'>
					<div><input type='text' value='' class='bselect' id='num_pict$postID'/></div>
					<div style='margin-top:8px;float:right;'>
						<span class='button' onclick=\"closeDiv('info_pict$postID','num_pict$postID');\">$LANG[CANCEL]</span> &nbsp; 
						<span class='button' onclick=\"pushBt('pict','$postID');\">$LANG[SUBMIT]</span>
					</div>
				</div>";
		$retstr .= "<span class='bt_style' id='bt_vote$postID' onclick=\"pushBt('vote','$postID');\" title=\"$LANG[FORMPANE_OTHER_VOTE]\">[vote]</span>
			</div>
		</div>
		<div style='clear:both;'></div>
	</div>";
		$retstr .= "<span id='scroll_position$postID' style='display:none'></span>";

	return $retstr;
	}
			
// Thread page list
function pageListString($channelTag,$tid,$page) {
	global $CURRENTUSERPPP;
	global $CURRENTUSER;
	global $CURRENTUSERAJAX;
	global $LANG;
	global $siteSettings;
	global $verifyEditDelete;

	$numcom = "num_comments";
	if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
		$numcom = "num_comments_T";

	$numPages = ceil(($channelTag[$numcom] / $CURRENTUSERPPP));
	$pagesListString = "";
	
	$jt = "";
	if (!array_key_exists( "ID", $_REQUEST ) && $CURRENTUSERAJAX)
		$jt = "</span>";
	
	if ($page)
		$_REQUEST['page'] = $page;

		$pagesuivcach = "<div id='contextmenucachepage' style='display:none;'>";
		if ($numPages > 1 && $page < $numPages) {
			$isLive="";
			$isLivej="0";
			$pagesuivnum = $page + 1;
			if ($pagesuivnum == $numPages) {
				$isLive="&amp;isLive=true";
				$isLivej="1";
			}
			$pagesuivcach .= "<div onclick=\"";
			if ($jt)
				$pagesuivcach .= "closelayer();emptymainThreadPage($tid,0,$pagesuivnum,$isLivej); return false;";
			else	
				$pagesuivcach .= "closelayer();location.href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pagesuivnum$isLive","#thread/$tid/$pagesuivnum")."';";
			$pagesuivcach .= "\" class='contextMenuelement'><div class='contextMenuelementimg'></div><div class='contextMenuelementtxt'>$LANG[NEXT_PAGE]</div></div>";
		}
		if ($numPages > 1 && $page > 1) {
			$pageprecnum = $page - 1;
			$pagesuivcach .= "<div onclick=\"";
			if ($jt)
				$pagesuivcach .= "closelayer();emptymainThreadPage($tid,0,$pageprecnum,0); return false;";
			else	
				$pagesuivcach .= "closelayer();location.href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageprecnum","#thread/$tid/$pageprecnum")."';";
			$pagesuivcach .= "\" class='contextMenuelement'><div class='contextMenuelementimg'></div><div class='contextMenuelementtxt'>$LANG[PREVIOUS_PAGE]</div></div>";
		}
		$pagesuivcach .= "</div>";

		if ($numPages == 1)
			$pagesListString = "<table><tr><td><div class=\"pageCountLeft\">$LANG[PAGES]: <span class='pageListSelected'>1</span></div></td></tr></table>";
		if ($numPages > 1 && $numPages < 32)
		{
			$pagesListString .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";
		
			$pagesListString .= "$LANG[PAGES]: ";
			
			$prev_page = 0;
			$next_page = 0;
			
			
			for ( $pageCount=1; $pageCount<=$numPages; $pageCount++ )
			{
				if (array_key_exists('page', $_REQUEST))
				{	
					if ($_REQUEST['page'] == $pageCount)
					{
						$pageCountStr = "class='pageListSelected'";
						$prev_page = $pageCount-1;
						if (($pageCount+1)<=$numPages)
						{
							$next_page = $pageCount+1;
						}
					}
					else
						$pageCountStr = "class='pageListUnSelected'";
				}
				else
				{
					if ($pageCount == 1)
						$pageCountStr = "class='pageListSelected'";
					else
						$pageCountStr = "class='pageListUnSelected'";
				}

				$isLive="";
				$isLivej="0";
				if ($pageCount == $numPages)
				{
					$isLive="&amp;isLive=true";
					$isLivej="1";
				}
				$jtpage = "";
				if ($jt)
					$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
				$pagesListString .= "$pageCount</a> ";
			}

			$pagesListString .= "</div><div class=\"pageCountRight\">";
			if ($prev_page > 0)
			{
				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$prev_page","#thread/$tid/$prev_page")."' class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
				if ($jt)
				{
					$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$prev_page,0); return false;\"  style='vertical-align: middle;'>";
				}
				$pagesListString .= "<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]$jt</a>";
			}
			if ($next_page > 0)
			{
				if ($prev_page > 0)
				{
					$pagesListString .= "  ";
				}
				$isLive="";
				$isLivej="0";
				if ($next_page == $numPages)
				{
					$isLive="&amp;isLive=true";
					$isLivej="1";
				}

				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$next_page$isLive","#thread/$tid/$next_page")."'  class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
				if ($jt)
				{
					$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$next_page,$isLivej); return false;\"  style='vertical-align: middle;'>";
				}

				$pagesListString .= "$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></a>";
			}
			
			$pagesListString .= "</div></td></tr></table>";
		}
		
		if ($numPages >= 32 && $numPages <= 36)
		{
			if ($_REQUEST['page'] <= 17)
			{
				$pagesListString .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
				$pagesListString .= "$LANG[PAGES]: ";
				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=18; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

				$jtpage = "";
				if ($jt)
					$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
				$pagesListString .= "$pageCount</a>";

				}
				$pagesListString .= "..... ";
				for ( $pageCount=($numPages-11); $pageCount<=$numPages; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

				$jtpage = "";
				if ($jt)
					$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
				$pagesListString .= "$pageCount</a>";

				}
				
				$pagesListString .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$prev_page","#thread/$tid/$prev_page")."' class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
				if ($jt)
				{
					$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$prev_page,0); return false;\"  style='vertical-align: middle;'>";
				}
				$pagesListString .= "<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]$jt</a>";
				}
				if ($next_page > 0)
				{
					$isLive="";
					$isLivej="0";
					if ($next_page == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}
					if ($prev_page > 0)
					{
						$pagesListString .= "  ";
					}
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$next_page$isLive","#thread/$tid/$next_page")."'  class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$next_page,$isLivej); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></a>";
				}
				
				$pagesListString .= "</div></td></tr></table>";
			}
			if ($_REQUEST['page'] > 17)
			{
				$pagesListString .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
				$pagesListString .= "$LANG[PAGES]: ";
				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=11; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

				$jtpage = "";
				if ($jt)
					$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
				$pagesListString .= "$pageCount</a>";

				}
				$pagesListString .= "..... ";
				for ( $pageCount=($numPages-18); $pageCount<=$numPages; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

				$jtpage = "";
				if ($jt)
					$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
				$pagesListString .= "$pageCount</a>";
				}
				
				$pagesListString .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$prev_page","#thread/$tid/$prev_page")."' class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
				if ($jt)
				{
					$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$prev_page,0); return false;\"  style='vertical-align: middle;'>";
				}
				$pagesListString .= "<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]$jt</a>";
				}
				if ($next_page > 0)
				{
					$isLive="";
					$isLivej="0";
					if ($next_page == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}
					if ($prev_page > 0)
					{
						$pagesListString .= "  ";
					}
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$next_page$isLive","#thread/$tid/$next_page")."'  class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$next_page,$isLivej); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></a>";
				}
				
				$pagesListString .= "</div></td></tr></table>";
			}			
		}
		
		if ($numPages > 36 && $numPages < 100)
		{
			if ($_REQUEST['page'] <= 17)
			{
				$pagesListString .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
				$pagesListString .= "$LANG[PAGES]: ";
				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=18; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=(round($numPages/2)); $pageCount<=(round($numPages/2)+2); $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=($numPages-4); $pageCount<=$numPages; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				
				$pagesListString .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$prev_page","#thread/$tid/$prev_page")."' class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
				if ($jt)
				{
					$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$prev_page,0); return false;\"  style='vertical-align: middle;'>";
				}
				$pagesListString .= "<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]$jt</a>";
				}
				if ($next_page > 0)
				{
					$isLive="";
					$isLivej="0";
					if ($next_page == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}
					if ($prev_page > 0)
					{
						$pagesListString .= "  ";
					}
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$next_page$isLive","#thread/$tid/$next_page")."'  class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$next_page,$isLivej); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></a>";
				}
				
				$pagesListString .= "</div></td></tr></table>";
			}
			if ($_REQUEST['page'] > 17 && $_REQUEST['page'] <= $numPages-17)
			{
				$pagesListString .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
				$pagesListString .= "$LANG[PAGES]: ";
				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=5; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=($_REQUEST['page']-8); $pageCount<=($_REQUEST['page']+8); $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=($numPages-4); $pageCount<=$numPages; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				
				$pagesListString .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
				$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$prev_page","#thread/$tid/$prev_page")."' class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
				if ($jt)
				{
					$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$prev_page,0); return false;\"  style='vertical-align: middle;'>";
				}
				$pagesListString .= "<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]$jt</a>";
				}
				if ($next_page > 0)
				{
					$isLive="";
					$isLivej="0";
					if ($next_page == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}
					if ($prev_page > 0)
					{
						$pagesListString .= "  ";
					}
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$next_page$isLive","#thread/$tid/$next_page")."'  class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$next_page,$isLivej); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></a>";
				}
				
				$pagesListString .= "</div></td></tr></table>";
			}
			if ($_REQUEST['page'] >  $numPages-17)
			{
				$pagesListString .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
				$pagesListString .= "$LANG[PAGES]: ";
				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=5; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=(round($numPages/2)-1); $pageCount<=(round($numPages/2)+1); $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=($numPages-16); $pageCount<=$numPages; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				
				$pagesListString .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$prev_page","#thread/$tid/$prev_page")."' class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$prev_page,0); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]$jt</a>";
				}
				if ($next_page > 0)
				{
					$isLive="";
					$isLivej="0";
					if ($next_page == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}
					if ($prev_page > 0)
					{
						$pagesListString .= "  ";
					}
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$next_page$isLive","#thread/$tid/$next_page")."'  class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$next_page,$isLivej); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></a>";
				}
				
				$pagesListString .= "</div></td></tr></table>";
			}
		}
		if ($numPages >= 100)
		{
			if ($_REQUEST['page'] <= 8)
			{
				$pagesListString .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
				$pagesListString .= "$LANG[PAGES]: ";
				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=8; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=(round($numPages/2)); $pageCount<=(round($numPages/2)+2); $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=($numPages-4); $pageCount<=$numPages; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				
				$pagesListString .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$prev_page","#thread/$tid/$prev_page")."' class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$prev_page,0); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]$jt</a>";
				}
				if ($next_page > 0)
				{
					$isLive="";
					$isLivej="0";
					if ($next_page == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}
					if ($prev_page > 0)
					{
						$pagesListString .= "  ";
					}
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$next_page$isLive","#thread/$tid/$next_page")."'  class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$next_page,$isLivej); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></a>";
				}
				
				$pagesListString .= "</div></td></tr></table>";
			}
			if ($_REQUEST['page'] > 8 && $_REQUEST['page'] <= $numPages-8)
			{
				$pagesListString .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
				$pagesListString .= "$LANG[PAGES]: ";
				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=5; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=($_REQUEST['page']-4); $pageCount<=($_REQUEST['page']+4); $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=($numPages-4); $pageCount<=$numPages; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				
				$pagesListString .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$prev_page","#thread/$tid/$prev_page")."' class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$prev_page,0); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]$jt</a>";
				}
				if ($next_page > 0)
				{
					$isLive="";
					$isLivej="0";
					if ($next_page == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}
					if ($prev_page > 0)
					{
						$pagesListString .= "  ";
					}
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$next_page$isLive","#thread/$tid/$next_page")."'  class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$next_page,$isLivej); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></a>";
				}
				
				$pagesListString .= "</div></td></tr></table>";
			}
			if ($_REQUEST['page'] >  $numPages-8)
			{
				$pagesListString .= "<table style='width: 100%;'><tr><td><div class=\"pageCountLeft\">";	
			
				$pagesListString .= "$LANG[PAGES]: ";
				
				$prev_page = 0;
				$next_page = 0;
				
				
				for ( $pageCount=1; $pageCount<=5; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=(round($numPages/2)-1); $pageCount<=(round($numPages/2)+1); $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				$pagesListString .= "..... ";
				for ( $pageCount=($numPages-7); $pageCount<=$numPages; $pageCount++ )
				{
					if (array_key_exists('page', $_REQUEST))
					{	
						if ($_REQUEST['page'] == $pageCount)
						{
							$pageCountStr = "class='pageListSelected'";
							$prev_page = $pageCount-1;
							if (($pageCount+1)<=$numPages)
							{
								$next_page = $pageCount+1;
							}
						}
						else
							$pageCountStr = "class='pageListUnSelected'";
					}
					else
					{
						if ($pageCount == 1)
							$pageCountStr = "class='pageListSelected'";
						else
							$pageCountStr = "class='pageListUnSelected'";
					}

					$isLive="";
					$isLivej="0";
					if ($pageCount == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}

					$jtpage = "";
					if ($jt)
						$jtpage .= "onclick=\"closelayer();emptymainThreadPage($tid,0,'$pageCount',$isLivej); return false;\"";
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pageCount$isLive","#thread/$tid/$pageCount")."' $jtpage $pageCountStr>";
					$pagesListString .= "$pageCount</a>";
				}
				
				$pagesListString .= "</div><div class=\"pageCountRight\">";
				if ($prev_page > 0)
				{
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$prev_page","#thread/$tid/$prev_page")."' class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$prev_page,0); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]$jt</a>";
				}
				if ($next_page > 0)
				{
					$isLive="";
					$isLivej="0";
					if ($next_page == $numPages)
					{
						$isLive="&amp;isLive=true";
						$isLivej="1";
					}
					if ($prev_page > 0)
					{
						$pagesListString .= "  ";
					}
					$pagesListString .= "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$next_page$isLive","#thread/$tid/$next_page")."'  class='button_mini' style='padding-bottom:4px;vertical-align:middle;'>";
					if ($jt)
					{
						$pagesListString .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,$next_page,$isLivej); return false;\"  style='vertical-align: middle;'>";
					}
					$pagesListString .= "$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" /></a>";
				}
				
				$pagesListString .= "</div></td></tr></table>";
			}
		}
	return $pagesListString . $pagesuivcach;
}

function make_rss($userID) {
	global $siteSettings;
	global $CURRENTUSER;
	global $CURRENTSTATUS;
	global $LANG;

	if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned") {
		$rss = "xml/blog.xml";
		$webname = "blog";
		if ($userID) {
			$userID = make_num_safe($userID);
			$rss = "xml/".$userID.".xml";
		}
		$userlink = "";
		if ($userID) {
			$userlink = "&amp;userID=$userID";
			$user = mf_query("SELECT * FROM blog WHERE userID = \"$userID\" LIMIT 1");
			$user = mysql_fetch_assoc($user);
			$username = $user['user'];
			if ($user['webname']) {
				$webname = $user['webname'];
				$userlink = "";
			}
			$blogtitle = "Blog de $username";
			if ($user['title']) {
				$blogtitle = $user['title'];
				if ($user['subtitle'])
					$blogtitle .= " - ".$user['subtitle'];
			}
		}

		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .='<rss version="2.0"><channel>';
		if (!$userID) {
			$userID = "0";
			$xml.='<title>'.$siteSettings['titlebase'].' - Le Blog</title>';
			$xml.='<link>http://'.$siteSettings['siteurl'].'</link>';
		}
		else {
			$xml.='<title>'.$siteSettings['titlebase'].' - '.$blogtitle.'</title>';
			$xml.='<link>http://'.$siteSettings['siteurl'].'/'.make_link('$webname','$userlink').'</link>';
		}
		$xml.='<description>'.$siteSettings['titledesc'].'</description>';
		$xml.='<image>';
		$xml.='<url>http://'.$siteSettings['siteurl'].'/engine/grafts/'.$siteSettings['graft'].'/images/RSSheaderImage.png</url>';
		$xml.='<title>'.$siteSettings['titlebase'].'</title>';
		$xml.='<link>http://'.$siteSettings['siteurl'].'</link>';
		$xml.='</image>';

		if ($userID)
			$resss= mf_query("SELECT * FROM forum_topics WHERE blog = 2 and userID = '$userID' and threadtype < 3 and pthread = 0 ORDER BY ID desc limit 0,20");
		else
			$resss= mf_query("SELECT * FROM forum_topics WHERE (blog = 1 or (blog = 2 and news = 1)) and threadtype < 3 and pthread = 0 ORDER BY ID desc limit 0,20");

		while ($row=mysql_fetch_assoc($resss)) {
//			$text = substr(htmlspecialchars(remove_formating($row['body'])),0,500);
			$text = htmlspecialchars(format_post($row['body'],false,$row['ID']));
			$adresse="http://".$siteSettings['siteurl']."/".make_link("$webname","&amp;action=g_view&amp;ID=$row[ID]$userlink","#blog/$userID/$row[ID]/0/g_view/1");
			$datephp = date("D, d M Y H:i:s",$row['date']);
			$xml .= '<item>';
			$xml .= '<title>'.$row['title'].'</title>';
			$xml .= '<description>'.$text.'</description>';
			$xml .= '<link>'.$adresse.'</link>';
			$xml .= '<pubDate>'.$datephp.' +0100</pubDate>'; 
			$xml .= '<author>'.$row['user'].'</author>'; 
			$xml .= '</item>';
		}
		$xml .= '</channel>';
		$xml .= '</rss>';
		$fp = fopen($rss, 'w+');
		fputs($fp, $xml);
		fclose($fp);
		$xml = "";
	}
}

function g_reply($dataLine) {
	global $CURRENTUSER, $CURRENTUSERID, $CURRENTSTATUS, $LANG, $siteSettings, $verifyEditDelete, $CURRENTUSERPPP, $CURRENTUSERRULES, $CURRENTUSERRATING, $CURRENTUSERAJAX, $FACEBOOK_OFF;

	$dataLine = explode(":!@:", $dataLine);

	if (!is_numeric($dataLine[0]))
		exit();
	
	$emptymain = "2";
	$blog = false;
	if ($_REQUEST['shard'] == "blog") {
		$blog = true;
		$emptymain = "3";
	}
	
	$numcom = "num_comments";
	if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
		$numcom = "num_comments_T";

	if (!is_numeric($CURRENTUSERPPP))
		$CURRENTUSERPPP = 60;

	$tid = $dataLine[0];
	$isLive = $dataLine[3];
	$sl = $dataLine[1];

	$page =  make_var_safe($dataLine[2]);
	$post =  make_var_safe($dataLine[4]);

	$jt = "";
	if (!array_key_exists( "ID", $_REQUEST ) && $CURRENTUSERAJAX)
		$jt = "</span>";

	$channelTag = mf_query("SELECT categories.name, 
        						categories.ID as categoryID, 
        						forum_topics.*
        						FROM forum_topics, categories 
        						WHERE forum_topics.ID=$tid AND forum_topics.category=categories.ID");
	if ($channelTag = mysql_fetch_assoc($channelTag)) {

		$userlaid['laid'] = "";
		if ($CURRENTUSER != "anonymous" and $CURRENTUSER != "bot") {
			$userlaid = mf_query("SELECT laid FROM users WHERE ID=$CURRENTUSERID LIMIT 1");
			$userlaid = mysql_fetch_assoc($userlaid);

			mf_query("UPDATE users SET lat=".time().", laid=$tid WHERE ID=$CURRENTUSERID LIMIT 1");
			
			$userIDs = mf_query("SELECT userID FROM fhits WHERE userID=$CURRENTUSERID and threadID=$tid LIMIT 1");
			$userIDs = mysql_fetch_assoc($userIDs);
		}        

		if ($channelTag['pthread'] == 1) {
			if ($CURRENTUSER == "anonymous" or $CURRENTUSER == "bot") {
				return unauthorized();
				exit($LANG['UNAUTHORIZED']);
			}

			if ($userIDs['userID'] != $CURRENTUSERID) {
				return unauthorized();
				exit($LANG['UNAUTHORIZED']);
			}
		}

		$num_views = $channelTag['num_views'];
		if ($userlaid['laid'] != $tid) {
			$views = mf_query("UPDATE forum_topics SET num_views = num_views+1 WHERE ID=$tid LIMIT 1");
			$num_views ++;
		}

    	$lastCommentID=-1;
    	if ($sl) {
            $sl = make_num_safe($sl);

			$lastCommentID = mf_query("SELECT ID FROM forum_posts WHERE threadID=$tid and date > '$sl' ORDER BY ID asc limit 0,1");
			$lastCommentID = mysql_fetch_assoc($lastCommentID);
			$lastCommentID = $lastCommentID['ID'];
			$post = $lastCommentID;
			
			$posttype = "AND posttype < 3";
			if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'modo'))
				$posttype = "";

			$postList = mf_query("SELECT ID, date FROM forum_posts WHERE threadID='$tid' $posttype ORDER BY ID asc");

			$counter = 1;
			$totalPosts = 1;
			while ($row = mysql_fetch_assoc($postList))	{
				if ($row['date'] <= $sl)
					$counter++;

				$totalPosts++;
			}

			$pageLocation = ceil($counter / $CURRENTUSERPPP);
			$totalPages = ceil($totalPosts / $CURRENTUSERPPP);

			$isLive="0";
			if ($pageLocation == $totalPages)
				$isLive="1";

			$page = $pageLocation;
			if ($post == "undefined")
				$post = "last";
		}

		if ($page == "last") {
			$page = ceil($channelTag[$numcom] / $CURRENTUSERPPP);
		}
		$thisContentObj = New contentObj;

		$imgpath = 'engine/grafts/' . $siteSettings['graft'] . '/images/down.png';

		global $CURRENTUSERPPP;
		if (!is_numeric($CURRENTUSERPPP))
			$CURRENTUSERPPP = 60;

		$pc = "";
		if ($page) {
			$pc = make_num_safe($page);

			if ($pc == "1")	{
				$limitBoundary = "limit 0,$CURRENTUSERPPP";
				if ($channelTag[$numcom] < $CURRENTUSERPPP)	{
					$fhitsnum_posts = $channelTag[$numcom];
					$isLive="1";
				}
				else
					$fhitsnum_posts = $CURRENTUSERPPP;
			}
			else {
				$upperBound = $pc * $CURRENTUSERPPP;
				$lowerBound = $upperBound - $CURRENTUSERPPP;
				$limitBoundary = "limit $lowerBound, $CURRENTUSERPPP";
				if ($channelTag[$numcom] < $upperBound)	{
					$fhitsnum_posts = $channelTag[$numcom];
					$isLive="1";
				}
				else
					$fhitsnum_posts = $upperBound;
			}
		}
		else {
			$limitBoundary = "limit 0,$CURRENTUSERPPP";			
			if ($channelTag[$numcom] < $CURRENTUSERPPP)	{
				$fhitsnum_posts = $channelTag[$numcom];
				$isLive="1";
			}
			else
				$fhitsnum_posts = $CURRENTUSERPPP;

		}

		$posttype = "AND p.posttype < 3 ";
		if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo'))
			$posttype = "";

		$filter = "";
		if ($CURRENTUSER != "anonymous") {
			if (isset($dataLine[5]))
				$filter = make_var_safe($dataLine[5]);
			else if (array_key_exists('threadFilter', $_COOKIE)) {
				$filter = $_COOKIE["threadFilter"];
				if ($filter == "undefined") $filter = "";
			}
		}
		$selected_users = "";
		$selected_users_join = "";
		$selected_users_select = "";
		if ($siteSettings['module_friends'] && $CURRENTUSER != "anonymous" && $filter != "hidden" && $filter != "all") {
			$selected_users = "AND (uf1.friendType IS NULL OR  uf1.friendType != 3) ";
			$selected_users_join = " LEFT JOIN users_friends AS uf1 ON (uf1.userID = '$CURRENTUSERID' AND uf1.target_userID = p.userID) ";
			$selected_users_select = ", uf1.friendType";
		}

		$childRSStr = "SELECT
								p.*,
								u.ID as Expr1,
								u.username,
								u.sig,
								u.avatar,
								u.rating as userRating
								$selected_users_select
								FROM forum_posts p INNER JOIN
								users u ON p.userID = u.ID 
								$selected_users_join
								WHERE p.threadID=$tid $posttype $selected_users
								ORDER BY p.ID ASC $limitBoundary";

		$childRS = mf_query($childRSStr); 
		$childRSnumrows = mysql_num_rows($childRS);

		$minR = 0;
		$maxR = 0;
		$arrR = array();

		$lastTimestamp = 0;
		while ($row=mysql_fetch_assoc($childRS)) {
			// stats stuff
			if ($row['rating'] < $minR)
				$minR = $row['rating'];

			if ($row['rating'] > $maxR)
				$maxR = $row['rating'];

			$arrR[] = $row['rating'];			

			$lastTimestamp=$row['date'];
		}

		if ($CURRENTUSER != 'anonymous' and $CURRENTUSER != "bot") {
			if ($userIDs['userID'] == $CURRENTUSERID)
				mf_query("UPDATE fhits SET date='$lastTimestamp', num_posts='$fhitsnum_posts' WHERE threadID='$tid' and userID='$CURRENTUSERID' and (num_posts < '$fhitsnum_posts' OR num_posts is null) LIMIT 1");
			else
				mf_query("INSERT INTO fhits (threadID, date, userID, num_posts) VALUES ($tid, $lastTimestamp, '$CURRENTUSERID', '$fhitsnum_posts')");
		}
		else if ($CURRENTUSER == "anonymous") {
			global $CURRENTUSERIP;
			if ($CURRENTUSERIP) {
				$checkanony = mf_query("SELECT ID FROM fhits_anonymous WHERE IP='$CURRENTUSERIP' and threadID='$tid' LIMIT 1");
				if (mysql_num_rows($checkanony) == 0)
					mf_query("INSERT INTO fhits_anonymous (threadID, IP) VALUES ($tid, '$CURRENTUSERIP')");
			}
		}

		if (!$verifyEditDelete) {
			$countuserview = mf_query("SELECT COUNT(userID) AS userview FROM fhits WHERE threadID='$tid' AND date > 0");
			$countuserview = mysql_fetch_assoc($countuserview);
			$countuserview = $countuserview['userview'];
		}
		else {
			$countuserview = 0;
			$virg = "";
			$listuserview = "";
			$userview = mf_query("SELECT fhits.userID, fhits.date, users.username FROM fhits JOIN users ON users.ID = fhits.userID WHERE fhits.threadID='$tid'and fhits.date > 0 ORDER BY users.username");
			while ($userview_row = mysql_fetch_assoc($userview)) {
				$countuserview ++;
				$listuserview .= $virg."<a href='#' title=\"".date($LANG['DATE_LINE_SHORT'], $userview_row['date'])."\">".$userview_row['username']."</a>";
				$virg = ", ";
			}
		}
		$countanonview = mf_query("SELECT COUNT(ID) AS anonview FROM fhits_anonymous WHERE threadID='$tid'");
		$countanonview = mysql_fetch_assoc($countanonview);
		$totalview = $countuserview + $countanonview['anonview'];
		if ($verifyEditDelete)
			$totalview = "<span onclick=\"toggleLayer('userview');\" class='jl'>$totalview</span>";

		$backthreadlist = "<img src='engine/grafts/$siteSettings[graft]/images/arrow_left.png' style='vertical-align: top; margin-top: 0px;' alt=\"$LANG[BACK_THREAD_LIST]\" />$LANG[BACK_THREAD_LIST]";
		if ($jt)
			$backthreadlist = "<span onclick=\"closelayer();emptymain$emptymain($tid,'$CURRENTUSERID'); return false;\" class='button'>$backthreadlist</span>";
		else
			$backthreadlist = "<span class='button' onclick=\"closelayer();location.href='".make_link($_REQUEST['shard'])."'\">$backthreadlist</span>";

		$bottompage = "<span onclick=\"scrolltoID('top_page_button');\" class='button' id='bottom_page_button'>$LANG[BOTTOM_PAGE] 
				<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_down.png' style='vertical-align: top; margin-top: 0px;' alt=\"$LANG[BOTTOM_PAGE]\" /></span>";
		
		$thisContentObj->primaryContent .= "<a name='header'></a>
					<div style='float:left;margin-bottom:6px;margin-left:4px;white-space:nowrap;'>";
		if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && $emptymain != "3")
			$thisContentObj->primaryContent .= "<span onclick=\"callNewThreadForm();\" title=\"$LANG[CREATE_NEW_THREAD]\" class='button'>$LANG[NEW_THREAD]</span>";
		else if ($emptymain != "3")
			$thisContentObj->primaryContent .= "<span title=\"$LANG[CREATE_NEW_THREAD]\" class='button' style='cursor:not-allowed;'>$LANG[NEW_THREAD]</span>";
		$thisContentObj->primaryContent .= "</div>
					<div style='float:right;margin-bottom:6px;margin-right:4px;white-space:nowrap;'>
					&nbsp; $backthreadlist
					&nbsp; $bottompage
					</div>
					<div style='clear:both;width:100%;border-bottom: 1px silver dashed;'></div>";
		if ($CURRENTUSERAJAX)
			$thisContentObj->primaryContent .= "</div>";

			$thisContentObj->primaryContent .= "<div id='newThreadFormPlaceholder' class='submenuPlaceholder'></div>";
 
		$shardContentArray[] = $thisContentObj;
		// End AJAX menus		


        // Thread has been deleted, don't let them go to it
        if ($channelTag['threadtype'] == 3) {
			return unauthorized();
			exit($LANG['UNAUTHORIZED']);
		}

        $siteSettings['titledesc'] = $channelTag['title'];
		$metadescription = remove_formating($channelTag['body']);
		if (strlen($metadescription) > 200)
			$metadescription= substr($metadescription,0,194)." [...]";
		$siteSettings['description'] = $metadescription;
 
        $thisContentObjCT = New contentObj;
		$thisContentObjCT->primaryContent .= "<div class='threadHeaderInfo'>";

		$pagesListString = pageListString($channelTag,$tid,$pc);
		$thisContentObjCT->primaryContent .= "<div class='pagesListPaneB'>$pagesListString</div><span id='thread_current_page' style='display:none;'>$pc</span>";
		$thisContentObjCT->primaryContent .= "<div id='bas'  class='subMenuThreadBottom'>";
		$thisContentObjCT->primaryContent .= "<a href='".make_link("forum")."' class='button'>";
		if ($jt)	
			$thisContentObjCT->primaryContent .= "<span onclick=\"closelayer();emptymain$emptymain($tid,'$CURRENTUSERID'); return false;\">";
		$thisContentObjCT->primaryContent .= "<img src='engine/grafts/$siteSettings[graft]/images/arrow_left.png' style='vertical-align: top; margin-top: 0px;' alt=\"$LANG[BACK_THREAD_LIST]\" />$LANG[BACK_THREAD_LIST]$jt</a>&nbsp;&nbsp;";
		$thisContentObjCT->primaryContent .= "<span onclick=\"scrolltoID('bottom_page_button');\" class='button' id='top_page_button'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_up.png' style='vertical-align: top; margin-top: 0px;' alt=\"$LANG[TOP_PAGE]\" />&nbsp;$LANG[TOP_PAGE]</span></div>";
				
		if ($channelTag['teamID'] && $channelTag['pthread'] == 1) {
			$thisContentObjCT->primaryContent .=  "<span class='button_mini' style='float:right;margin-right:14px;margin-top:2px;'>
			<img src='engine/grafts/$siteSettings[graft]/images/folder.png' border='0' style='vertical-align:middle;' alt=\"$LANG[TEAM_UPLOADFILE]\" />
			<a href='".make_link("teams","&amp;action=g_files&amp;teamID=$channelTag[teamID]")."'>
			$LANG[TEAM_UPLOADFILE]</a></span></span>";
		}

		$isLivej = "0";
		$isLivei = "";
		if ($isLive == "1") {
			$isLivei = "&amp;isLive=true";
			$isLivej = "1";
		}
		$titlethread = "<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$tid&amp;page=$pc$isLivei","#thread/$tid/$pc")."'>";
		if ($jt) {
			$titlethread .= "<span onclick=\"closelayer();emptymainThreadPage($tid,0,'$pc',$isLivej); return false;\">";
		}
		$titlethread .= $channelTag['title']." $jt</a>";

		$lockimg = "";
		if ($channelTag['locked'] == 1)
			$lockimg = "<img src='http://".$siteSettings['siteurl']. "/engine/grafts/" . $siteSettings['graft'] . "/images/lock.gif' alt=\"$LANG[LOCKED_DISABLED]\" />";

		if ($CURRENTUSER != "bot" && $CURRENTUSER != "anonymous") {
			$thisContentObjCT->primaryContent .= "<a href='".make_link("forum")."'>";
			$thisContentObjCT->primaryContent .= "<span onclick=\"";
			if ($jt)
				$thisContentObjCT->primaryContent .= "emptymain$emptymain($tid,$CURRENTUSERID); return false;";
			else
				$thisContentObjCT->primaryContent .= "location.href='".make_link($_REQUEST['shard'])."';";
			$thisContentObjCT->primaryContent .= "\" style='cursor:pointer;'>
			$LANG[THREAD_LISTING]</span></a> \ 
			<span onclick=\"if (document.getElementById('parentC')) {emptymain$emptymain($tid,$CURRENTUSERID,".$channelTag['categoryID']."); return false;}\" style='cursor:pointer;'>
			$channelTag[name]</span> \ $lockimg <b>$titlethread</b> $lockimg";
		}

		if ($CURRENTUSER == "anonymous") {
			$thisContentObjCT->primaryContent .= "<a href='".make_link("forum")."'>";
			$thisContentObjCT->primaryContent .= "$LANG[THREAD_LISTING]</a> \  <b>$channelTag[title]</b>";
		}


		if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && $CURRENTUSER != "bot")
			$thisContentObjCT->primaryContent .= "<br/><div id='usersBrowsing'>$LANG[USER_BROWSING_THREAD]: ".getUserList($tid)."</div>
				<br/><span onclick=\"userreadlist($tid)\" class='button_mini'>$LANG[USER_BROWSING_REFRESH]</span>";

		$thisContentObjCT->primaryContent .= "</div>";

        $topHeaderInfoObj = New contentObj;
        $topHeaderInfoObj->primaryContent .= "<div class='topThreadHeaderInfo'><div class='clearfix'></div>";

		$pThreadNotice = "";
		$ms2 = "";

        if ($channelTag['pthread'] == 1) {
			if ($CURRENTUSER == "anonymous" or $CURRENTUSER == "bot") {
				return unauthorized();
				exit($LANG['UNAUTHORIZED']);
			}

			$pThreadNotice = "<span class='privateNotification'>[ $LANG[PRIVATE] ]</span>";
			if ($channelTag['teamID'])
				$pThreadNotice = "<span class='privateNotification'>[ Team ".team_name($channelTag['teamID'])." ]</span>";
				
			
			if ($userIDs['userID'] == $CURRENTUSERID) {
				$ms2 .= "<div id='listpThreadUsersPane' class='menuWrapper'>
						<div class='menuInfo2' id='listpThreadUsersPaneTop'>
							<div>$LANG[PTHREADUSERS1]</div>
							<div>$LANG[PTHREADUSERS2] <span style='color:red;'>$LANG[PTHREADUSERS3]</span>$LANG[PTHREADUSERS4]</div>
						</div>
						<div class='menuInfo2' id='listpThreadUsers'> ";
				$ms2 .= findPthreadUsers($tid, $channelTag['user']);
				$ms2 .= "</div>";
				if ($verifyEditDelete || isInGroup($CURRENTUSER, 'modo') || ($CURRENTUSER == $channelTag['user']) || isInGroup($CURRENTUSER, 'level1') || isInGroup($CURRENTUSER, 'level9'))
					$ms2 .= "<span onclick=\"toggleLayer('add_pthread_user');\" class='button_mini'>$LANG[ADDUSER]...</span>
							<span id='add_pthread_user' style='display:none;margin-top:5px;'>
							<input type='text' autocomplete='off' style='vertical-align: middle;color:#000000;' size='20' class='bselect' id='userprofilename2' onkeyup=\"input_user(2); return false;\" onfocus=\"show_select_user(2);\" onblur=\"hide_select_user(2);\" /> <a id='add_username_button' class='button_mini' href=\"#\" onclick=\"addUserToPthread($tid); return false;\">$LANG[SUBMIT]</a></span><div id='inputSelectUser2' class='user_list'></div>";
				$ms2 .= "</div>";

			}
		}		
        $topHeaderInfoObj->primaryContent .= "";
        
		if ($channelTag['teamID'] && $channelTag['pthread'] == 1) {
			$topHeaderInfoObj->primaryContent .=  "<span class='button_mini' style='float:right;margin-right:-7px;margin-top:-4px;'>
				<img src='engine/grafts/$siteSettings[graft]/images/folder.png' border='0' style='vertical-align:middle;' alt=\"$LANG[TEAM_UPLOADFILE]\" />
				<a href='".make_link("teams","&amp;action=g_files&amp;teamID=$channelTag[teamID]")."'>
				$LANG[TEAM_UPLOADFILE]</a></span>";
		}
		// Display thread rate arrows and rate
		if ($channelTag['pthread'] == 0)
			$topHeaderInfoObj->primaryContent .= display_thread_rating($tid,$channelTag['rating']);

		if ($channelTag['spoiler'])
			$titlethread = "<span class='spoilerNotification'>[$LANG[SPOILER]]</span> $titlethread";
		// Share thread
		if ($CURRENTUSER != "bot" && $CURRENTUSER != "anonymous" && $channelTag['pthread'] != 1 && !$siteSettings['mobile']) {
		$topHeaderInfoObj->primaryContent .= "<div style='float:right;'>";
		// Google Plus One button
		if ($channelTag['pthread'] != 1 && !$siteSettings['mobile'])
			$topHeaderInfoObj->primaryContent .= "<div id='google_plusone' style='margin-bottom:4px;'><g:plusone size=\"medium\" href=\"http://$siteSettings[siteurl]/".make_link("forum","&amp;action=g_reply&amp;ID=$tid")."\"></g:plusone></div>";
		// Facebook Share button
			if (!$FACEBOOK_OFF)
				$topHeaderInfoObj->primaryContent .= "<a name=\"fb_share\" share_url=\"http://$siteSettings[siteurl]/index.php?shard=forum&amp;action=g_reply&ID=$tid\"></a>";
			// Permalink
			$topHeaderInfoObj->primaryContent .= "<div style='margin-bottom:4px;'><a class='button_mini' href=\"http://$siteSettings[siteurl]/".make_link("forum","&amp;action=g_reply&amp;ID=$tid")."\" target='_blank'>$LANG[PERMALINK]</a></div>";

		$topHeaderInfoObj->primaryContent .= "</div>";
		}
		
		if ($CURRENTUSER != "bot" && $CURRENTUSER != "anonymous") {
			$topHeaderInfoObj->primaryContent .= "<div>
				<a href='".make_link("forum")."'>
				<span onclick=\"";
				if ($jt)
					$topHeaderInfoObj->primaryContent .= "emptymain$emptymain($tid,$CURRENTUSERID); return false;";
				else
					$topHeaderInfoObj->primaryContent .= "location.href='".make_link($_REQUEST['shard'])."';";
				$topHeaderInfoObj->primaryContent .= "\" style='cursor:pointer;'>$LANG[THREAD_LISTING]
				</span></a> \
				<span onclick=\"if (document.getElementById('parentC')) {emptymain$emptymain($tid,$CURRENTUSERID,".$channelTag['categoryID']."); return false;}\" style='cursor:pointer;'>$channelTag[name]</span></div>";

			
			$topHeaderInfoObj->primaryContent .= "<div class='threadTitleHolder' id='threadid$tid'>$pThreadNotice $titlethread ";
			if (($verifyEditDelete) || ($CURRENTUSER == $channelTag['user'])) {
				$topHeaderInfoObj->primaryContent .= "
					<a title=\"$LANG[EDIT_THREAD]\" href='".make_link("forum","&amp;action=g_editThread&amp;ID=$tid")."'>
					<img src='engine/grafts/" . $siteSettings['graft'] . "/images/edit.gif' class='edHolder' alt=\"$LANG[EDIT_THREAD]\" /></a>";
			}
		}
		if ($CURRENTUSER == "anonymous") {
			$topHeaderInfoObj->primaryContent .= "<div>";
			$topHeaderInfoObj->primaryContent .= "<a href='".make_link("forum")."'>";
			$topHeaderInfoObj->primaryContent .= "$LANG[THREAD_LISTING]</a></div>
				<div class='threadTitleHolder' id='threadid$tid'>$pThreadNotice $titlethread ";
		}

		$topHeaderInfoObj->primaryContent .=  "</div>";
		$topHeaderInfoObj->primaryContent .= "<div style='display:inline;font-size:9px;color:#333333'>$LANG[THREAD] $LANG[NUMBER_SHORT]<span id='numthreadID'>$tid</span> $LANG[CREATED_DATE] ".date($LANG['DATE_LINE_MINIMAL2'],$channelTag['date'])." $LANG[TO_MIN] ".date($LANG['DATE_LINE_TIME'],$channelTag['date'])." $LANG[BY] $channelTag[user] - $LANG[NUM_VIEW_1] $num_views $LANG[NUM_VIEW_2] $totalview $LANG[NUM_VIEW_3]</div>";	
		
		$topHeaderInfoObj->primaryContent .= "</div>";
		if ($verifyEditDelete)
			$topHeaderInfoObj->primaryContent .= "<div id='userview' style='display:none;font-size:9px;margin-left:90px;border-style: solid; border-color: #D3D3D3;'>$listuserview</div>";        										
		
		$topHeaderInfoObj->primaryContent .= drawThreadSubMenu($tid,0,$emptymain);

		// Tags list
		$thread_taglist = "";
		$query = mf_query("SELECT tag FROM forum_tags WHERE threadID='$tid' ORDER BY ID");
		while ($tags_row = mysql_fetch_assoc($query)) {
			$thread_taglist .= "<span style='padding:4px;'>$tags_row[tag]</span>";
		}
		if ($thread_taglist)
			$thread_taglist = "<span class='bold'>".$LANG['TAGS'].": </span>".$thread_taglist;
		$topHeaderInfoObj->primaryContent .= "<div id='thread_taglist'>$thread_taglist</div>";
		$topHeaderInfoObj->primaryContent .= $ms2;

		// List of thread moderations
		$topHeaderInfoObj->primaryContent .= display_thread_moderation($tid);

		$topHeaderInfoObj->primaryContent .= "<div class='pagesListPane'>$pagesListString</div>";
		

        $shardContentArray[] = $topHeaderInfoObj;
        
        /////////////////////////////////////////////////
        ///// Display a Poll, if that sucker exists /////
        /////////////////////////////////////////////////

        if ($channelTag['poll'] > 0) {
			$pollObj = New contentObj;
			$pollObj->primaryContent ="<div id='pollHolder'>";
			$pollObj->primaryContent .= renderPollResults($channelTag['poll']);
			$pollObj->primaryContent .= "</div>";

			$pollObj->primaryContent .= "<div class='pollBottom'><a href='#' id='voteLink' onclick=\" toggleLayer('pollQuestionsHolder'); toggleLayer('pollResultsHolder'); return false;\">$LANG[CAST_VOTE]</a></div>";


			$pollObj->primaryContent .= "";
			$shardContentArray[] = $pollObj;
		}



        $cur = 1; //used to format first post in regular way, without the rel='nofollow' attribute
        $lastPostTimeStamp = $firstPostTimeStamp = 0; // used to keep track of the first & last post in the thread's time stamp


        if ($childRSnumrows) {
			mysql_data_seek($childRS, 0);
        
			// Display thread parent post and comments
			while ($row = mysql_fetch_assoc($childRS) ) {

				if (!$firstPostTimeStamp)
					$firstPostTimeStamp = $row['date'];
				$lastPostTimeStamp = $row['date']; // will get overwritten everytime except the last

				$thisContentObj = New contentObj;
				$thisContentObj = assemblePost($thisContentObj, $row, $cur, $lastCommentID, $channelTag['pthread'], $channelTag['blog'], $channelTag['user'], $channelTag['teamID'], $tid, $pc, $channelTag['category']);

				if ($cur == 1)
					$cur = 2;
				else if ($cur == 2)
					$cur = 3;
				else
					$cur = 2;

				$shardContentArray[] = $thisContentObj;
			}
		}

        $thisContentObjCT2 = New contentObj;
		$thisContentObjCT2->primaryContent = "<div id='firstPostTimeStamp' style='display:none;' class='$firstPostTimeStamp'></div>";
		$thisContentObjCT2->primaryContent .= "<div id='timelastrefresh' style='display:none;' class='".time()."'></div>";
        $thisContentObjCT2->primaryContent .= "<div id='newPostPlaceHolder' class='0'></div>" . $thisContentObjCT->primaryContent;
/*   		$thisContentObjCT2->primaryContent .= "<script type=\"text/javascript\">";
		$thisContentObjCT2->primaryContent .= "function openAll() {";
		for ($i=0; $i <= count($id_tab); $i++)
		{
			$thisContentObjCT2->primaryContent .= "toggleLayer('whorated".$id_tab[$i]."');";
		}

		$thisContentObjCT2->primaryContent .= "} </script>";
*/
        $thisContentObjCT2->primaryContent .= "<div id='newPostIndicator' class='indicator'></div>";


		$shardContentArray[] = $thisContentObjCT2; 

        $lastpost = sizeof($shardContentArray);
        if ($lastpost == 1)
        	$lastpost = $lastpost - 1;
        else
        	$lastpost = $lastpost - 2;

        $shardContentArray[$lastpost]->title .= "<a name='bottom'></a>";



        //Resseting $row info
        if(mysql_data_seek($childRS, 0))
        {

			$row = mysql_fetch_assoc($childRS);

			//Display reply form if verified to reply

			//------------------------------------------------------------------------------
			// Create contentObj for this content object
			//------------------------------------------------------------------------------

			$ban_thread = false;
			if ($CURRENTSTATUS == "banned"){
				$getuserthread = mf_query("SELECT threadID FROM ban WHERE username=\"$CURRENTUSER\" ORDER BY ID DESC LIMIT 1");
				$getuserthread = mysql_fetch_assoc($getuserthread);
				if ($getuserthread['threadID'] != $tid)
					$ban_thread = true;
			}

			if ($CURRENTUSER != "anonymous" && (!$ban_thread || $channelTag['teamID']) && $CURRENTUSER != "bot" && ($CURRENTUSERRULES == "1" || !$siteSettings['rules'])) {
				$thisContentObj2 = New contentObj;
				$thisContentObj2->contentType = "generic";
				$thisContentObj2->title = "<span onclick=\"scrolltoID('postidreply');\" class='link'>$LANG[REPLYING_TO_POST]</span>: <i>" . $row['title'] . "</i>";
				$thisContentObj2->primaryContent = replyForm($channelTag,$tid,$isLive,$lastPostTimeStamp,$page);

			//------------------------------------------------------------------------------
			// Add this contentObject to the shardContentArray
			//------------------------------------------------------------------------------
				$shardContentArray[] = $thisContentObj2;
			}
		}

		$retstr = "";

	if ($channelTag['locked'] == 1)
		$isLive = "0";

		for ($i=0;$i<sizeof($shardContentArray);$i++) {
		$retstr .= renderPost($shardContentArray, $i);
	}

		$retstr .= "::cur@lo::".$tid."::cur@lo::".$post."::cur@lo::".$isLive."::cur@lo::".htmlspecialchars_decode($channelTag['title'])."::cur@lo::".$CURRENTUSERID."::cur@lo::".$blog."::cur@lo::".$page ."::cur@lo::::cur@lo::".htmlspecialchars_decode($metadescription);

	return $retstr;
	}
	else {
		return unauthorized();
		exit($LANG['UNAUTHORIZED']);
	}
	
}

function display_thread_rating($tid,$rating,$user_rate="none") {
	global $CURRENTUSER, $CURRENTSTATUS, $CURRENTUSERRULES, $LANG, $siteSettings, $CURRENTUSERRATING;
	
	$retstr = "";
	if ($CURRENTUSER != "anonymous" && $CURRENTSTATUS != "banned" && ($CURRENTUSERRULES == "1" || !$siteSettings['rules'])) {
		$marginb = "-36";
		$upClass = 'uparrowoff';
		$downClass = 'downarrowoff';
		$displayClass = "<span class='postTitle' id='threadTRatingStatus".$tid."' style='margin-left:-16px;'>$LANG[RATING]</span>";

		if ($user_rate != "none")
			$userrated['rating'] = $user_rate;
		else {
			$userrated = mf_query("SELECT rating FROM postratings WHERE threadID = '$tid' AND user = \"$CURRENTUSER\" LIMIT 1");
			$userrated = mysql_fetch_assoc($userrated);
		}
		if ($userrated['rating'] > 0) {			
			$upClass = 'uparrowon';
			$downClass = 'downarrowoff';
			$displayClass = "<span class='postTitlePositive' id='threadTRatingStatus".$tid."' style='margin-left:-16px;'>$LANG[RATED]</span>";
		}
		else if ($userrated['rating'] < 0) {
			$downClass = 'downarrowon';
			$upClass = 'uparrowoff';
			$displayClass = "<span class='postTitleNegative' id='threadTRatingStatus".$tid."' style='margin-left:-16px;'>$LANG[RATED]</span>";
		}

		$retstr .="<div id='threadModRating'>
								<div style='float: right;'>
								<div style='float: none; position:absolute; margin-left:-14px;' class='$upClass' id='uparrowthreadT".$tid."' onclick=\" toggleRatingArrow('threadT', $tid, 'uparrow', ".number_format($CURRENTUSERRATING, 2).", 'thread');\"></div>
								<div style='float: none; position:absolute;  margin-left:-14px; margin-top:17px !important; margin-top:20px' class='$downClass' id='downarrowthreadT".$tid."' onclick=\"toggleRatingArrow('threadT', $tid, 'downarrow', ".number_format($CURRENTUSERRATING, 2).", 'thread');\"></div>
								</div>	
								$displayClass";

		$margin = "-32";
		if ($rating > 10) { $margin = "-35"; }
		if ($rating < -10) { $margin = "-41"; }
		$retstr .= "<span id='ratingDisplaythreadT".$tid."' style='position:absolute; margin-left:".$margin."px; margin-top:12px;'>".number_format($rating, 2)."</span></div>";
	}
	return $retstr;
}

// List of thread moderations
function display_thread_moderation($tid,$showall=false) {
	global $CURRENTUSER, $CURRENTUSERAJAX, $LANG;
	$uml = mf_query("SELECT * FROM postratings WHERE threadID='$tid' ORDER BY rating DESC");
	$maxdate = time() - (3600 *24);
	$anyoneModded = false;
	if ($CURRENTUSER != "anonymous") {
		$counter = 1;
		while ($umlrow = mysql_fetch_assoc($uml)) {
			$anyoneModded = true;
			$negPos = "postRatingColorGradient2";
			$sign = "";
			if ($umlrow['rating'] < 0) {
				$negPos = "postRatingColorGradient3";
			}
			else {
				$sign = "+";
			}			

			$startHiddenTag = "";
			if (!$showall && $counter == 10) {
				$startHiddenTag = "<br/><span onclick=\"toggleLayer('whoratethread');\" class='jl'>Plus...</span><div id='whoratethread' style='display:none;'>";
			}

			$one_mod = "";
			if ($umlrow['user'] == $CURRENTUSER)
				$one_mod = "style='color:green;font-weight:bold;'";
			
			$delete_one_mod = "";
			if ($umlrow['user'] == $CURRENTUSER && $umlrow['modeddate'] > $maxdate)
				$delete_one_mod = "<span class='deleteButton' onclick=\"delete_thread_mod($umlrow[ID])\">x</span>";

			$counter++;
		
			if ($CURRENTUSERAJAX)
				$usersModList[] = "<div style='display:inline-block;' id='thread_mod_$umlrow[ID]'><a href=\"index.php?shard=forum&amp;action=un2id&amp;name=$umlrow[user]\"><span onclick=\"userprofile('".urlencode($umlrow['user'])."','header'); return false;\" $one_mod>$umlrow[user]</span></a> <span class='$negPos'>$sign".number_format($umlrow['rating'], 2)."</span> $delete_one_mod</div> $startHiddenTag";
			else
				$usersModList[] = "<div style='display:inline-block;' id='thread_mod_$umlrow[ID]'><a href=\"index.php?shard=forum&amp;action=un2id&amp;name=$umlrow[user]\" $one_mod>$umlrow[user]</a> <span class='$negPos'>$sign".number_format($umlrow['rating'], 2)."</span> $delete_one_mod</div> $startHiddenTag";
			
		}
	}
	$retStr = "";
	if ($anyoneModded) {
		$retStr .= "<div id='listThreadModsPane' class='menuWrapper'><span style='text-decoration:underline;'>$LANG[USER_MODERATED_THREAD]:</span><div class='menuInfo2' id='listThreadMods' style='padding-top:0px;font-size:0.9em;'> ";
			$retStr .= implode(", ", $usersModList);
		if (!$showall && $counter > 10)
			$retStr .= "</div>";
		$retStr .= "</div></div>";
	}

	return $retStr;
}

function usermplist($tid,$tidt)	{
	global $LANG;
	global $CURRENTUSER;
				
	if (isInGroup($CURRENTUSER, "admin")) {
		
		$mpuser = "";
		$usert = "";
		$cs = mf_query("SELECT * FROM fhits WHERE threadID='$tid'");
		while ($row=mysql_fetch_assoc($cs))	{
			$usert = "$row[userID]";
			$usern = mf_query("SELECT name FROM forum_user_nri WHERE userID='$usert' LIMIT 1");
			$row=mysql_fetch_assoc($usern);

			$mpuser .= "<tr><td>$usert</td><td>$row[name]</td>
						<td><small>[<a href=\"index.php?shard=forum&amp;action=mpusers_deleteUser&amp;user2=$usert&amp;thread2=$tid&amp;threadt=$tidt\">$LANG[DELETE]</a>]</small></td></tr>";
		}
		
		return $mpuser;
	}
}

function load_vote($postID,$voteName,$prev="") {
	global $LANG;
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $siteSettings;
	$retstr = "";

	$postID = make_num_safe($postID);
	if (strlen($voteName) > 20)
		$voteName = substr($voteName,0,20);
	$voteName = make_var_safe($voteName);

	if (!$prev) {
		$query = mf_query("SELECT post_votes.*, post_votes_user.vote_for, post_votes_user.vote_against 
			FROM post_votes 
			LEFT JOIN 
				post_votes_user ON post_votes.postID = post_votes_user.postID 
			AND post_votes.voteName = post_votes_user.voteName 
			AND post_votes_user.userID = '$CURRENTUSERID'
			WHERE post_votes.postID ='$postID' AND post_votes.voteName = \"$voteName\"
			LIMIT 1");
		if (!$row=mysql_fetch_assoc($query)) {
			mf_query("INSERT IGNORE INTO post_votes (postID, voteName) VALUES ('$postID', \"$voteName\")");
			$row['total_vote_for'] = "0";
			$row['total_vote_against'] = "0";
		}
	}
	if ($CURRENTUSER != "anonymous" && !$prev) {
		$highlight_for = "";
		$highlight_against = "";
		if ($row['vote_for'])
			$highlight_for = "border: 1px solid silver;";
		else if ($row['vote_against'])
			$highlight_against = "border: 1px solid silver;";
		$retstr .= "<span style='font-size:1.5em;$highlight_for'>$row[total_vote_for]</span> <img src='engine/grafts/$siteSettings[graft]/images/increase_green.png' border='0' style='vertical-align:baseline;cursor:pointer;' alt='+' onclick=\"vote_for('$postID','$voteName');\"/> / <span style='font-size:1.5em;$highlight_against'>$row[total_vote_against]</span> <img src='engine/grafts/$siteSettings[graft]/images/decrease_red.png' border='0' style='vertical-align:baseline;cursor:pointer;' alt='-' onclick=\"vote_against('$postID','$voteName');\" />";
	}
	else if (!$prev)
		$retstr .= "<span style='font-size:1.5em;'>$row[total_vote_for]</span> <img src='engine/grafts/$siteSettings[graft]/images/increase_green.png' border='0' style='vertical-align:baseline;cursor:not-allowed;' alt='+' /> / <span style='font-size:1.5em;'>$row[total_vote_against]</span> <img src='engine/grafts/$siteSettings[graft]/images/decrease_red.png' border='0' style='vertical-align:baseline;cursor:not-allowed;' alt='-' />";
	else
		$retstr .= "<span style='font-size:1.5em;'>0</span> <img src='engine/grafts/$siteSettings[graft]/images/increase_green.png' border='0' style='vertical-align:baseline;cursor:not-allowed;' alt='+' /> / <span style='font-size:1.5em;'>0</span> <img src='engine/grafts/$siteSettings[graft]/images/decrease_red.png' border='0' style='vertical-align:baseline;cursor:not-allowed;' alt='-' />";

	return $retstr;
}

function highlight($str,$term_array="") {
	if ($term_array) {
		$i = 0;
		$temp_array = array();
		$color_array = array();
		$color_array[0] = "<span style='background-color:#FFFF00;'>\\1</span>";
		$color_array[1] = "<span style='background-color:#FF00FF;'>\\1</span>";
		$color_array[2] = "<span style='background-color:#00FFFF;'>\\1</span>";
		$color_array[3] = "<span style='background-color:#00FF00;'>\\1</span>";
		$color_array[4] = "<span style='background-color:#FF0000;'>\\1</span>";
		foreach($term_array as $term) {
			$term_array[$i] = "/(".$term.")/i";
			$temp_array[$i] = "/(@@".$term."@@)/i";
			if ($i > 4)
				$color_array[$i] = "<span style='background-color:#0000FF;'>\\1</span>";
			$i ++;
		}
		$str = preg_replace($term_array, "@@\\1@@", $str);
		$str = preg_replace($temp_array, $color_array, $str);
		$str = str_replace("@@", "", $str);
	}

	return $str;
}

function unauthorized() {
	global $LANG;
	
	$retstr = "<div style='font-size:1.8em;'>$LANG[UNAUTHORIZED]</div>";
	$retstr .= "<div style='display:inline-block;margin-top:4px;' class='button' onclick=\"location.href='".make_link("forum","","#threadlist")."';\">$LANG[BUTTON_BACK]</div>";
	
	return $retstr;
}

?>