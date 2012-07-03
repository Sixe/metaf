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

// forumajax.php

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

		$query = mf_query("UPDATE postratings SET comment = \"".$result['comment']."\" WHERE postID = '$dataLineArrayC[1]' AND user = \"".$CURRENTUSER."\" LIMIT 1");

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
				if ($CURRENTUSERID != $usertomod) {
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

		$pc = make_num_safe($page);
		$upperBound = $pc * 20;
		$lowerBound = $upperBound - 20;

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
					";

		$posts = mf_query($forumsStr);
	$result_totalposts = $result_displayedposts = 0;
		while( $post = mysql_fetch_assoc( $posts )) {
		if ($result_totalposts >= $lowerBound && $result_totalposts < $upperBound) {
			$restrpost = "<div id='post$post[ID]' class='threadInfo' style='border-bottom: 2px solid #D3D3D3;'>";
			$restrpost .= "<div style='padding-right: 0px; text-align: center; width: 36px; float:left;'></div>";
			$restrpost .= "<table width='100%' border='0' class='threadTable'><tr>";
			$restrpost .= "<td class='noNewPosts' colspan='7'>";

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
			$restrpost .= "<span class='threadTitleText'><small>$LANG[SEARCH_TIT2]</small>$NWS $locked $sticky $private $spoiler $poll
				<a href='index.php?shard=forum&action=calculatePageLocationForFirstNew&postID=$post[ID]&sl=$datepost'>";
			if ($jt)
				$restrpost .= "<span onclick=\"emptymainThread($post[threadID],$datepost,1,0); return false;\" style='cursor:pointer;'>";
			$restrpost .= "<b>$post[title]</b>$jt</a></span>
				<span class='subThreadTitleLine' style='margin-left: 15px; float: right;'>$pagesStr</span>
				&nbsp;$externalLinks $subscribed $hidethread $edStr</div></td></tr>";
			$restrpost .= "<tr><td class='subThreadTitleLine'>$text</td>";
			$restrpost .= "<td width='134px' class='threadInfoTDsmall' style='text-align: right; vertical-align:top'>
				".date($LANG['DATE_LINE_SHORT'], $post['date'])."</a>
			<br/>$LANG[DATE_LINE_FULL1]: <a href=\"index.php?shard=forum&amp;action=un2id&amp;name=$post[user]\">";
			if ($jt)
				$restrpost .= "<span onclick=\"userprofile('".urlencode($post['user'])."'); return false;\">";
			$restrpost .= "$post[user]$jt</a>
				<br/><br/>$LANG[CHANNEL] $post[categoryname]";
			$restrpost .="</td></tr></table></div>";
			
			$retstr .= $restrpost;
			$result_displayedposts++;
		}
		$result_totalposts++;
	}
	$numpages = floor($result_totalposts/20);

		if ($retstr == "")
			$searchresult = "<br/><br/><b>$LANG[NO_POSTS]</b><div style='padding-top: 210px;'></div>";
		else {
		$searchresult = $retstr;
		if ($result_displayedposts < 3)	{
			$spaceheight = (4 - $result_displayedposts)* 50;
				$searchresult .= "<div style='padding-top: ".$spaceheight."px;'></div>";
			}
		}
	$retstr = $page_prec = $page_suiv = "";

	for ($page = 1; $page<=($numpages); $page++ ) {
			if ($page == $pc)
			$retstr .= "<span onclick=\"changepagepost($page);\" class='pageListSelected'>$page</span> ";
		else if ($page < 4 || $page > ($numpages - 4) || ($page > ($pc - 6) && $page < ($pc + 6)))
			$retstr .= "<span onclick=\"changepagepost($page);\" class='pageListUnSelected'>$page</span> ";
		else if ($page == 4 || $page == ($numpages - 4) || $page == ($pc - 6) || $page == ($pc + 6))
			$retstr .= "<span> ... </span>";
		}

	$retstr = "<div>$LANG[PAGES]: ".$retstr."</div>";
		if ($pc > 1) {
			$page_prec = $pc - 1;
	$retstr .= "<div style='margin-top:8px;margin-bottom:8px;'>
					<span onclick=\"changepagepost($page_prec);\" class='button_mini' style='vertical-align: middle;'>
					<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[PREVIOUS_PAGE]\" />$LANG[PREVIOUS_PAGE]</span>";
		}
		$page_suiv = $pc + 1;
		if (!$page_prec)
		$retstr .= "<div style='margin-top:8px;margin-bottom:8px;'>";
		else
		$retstr .= "&nbsp;";
	if ($result_totalposts >= 21)
		$retstr .= "<span onclick=\"changepagepost($page_suiv);\" class='button_mini' style='vertical-align: middle;'>$LANG[NEXT_PAGE]
				<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt=\"$LANG[NEXT_PAGE]\" />
		</span>";
		
	$retstr .= "&nbsp; <span style='font-size:0.8em;'>$LANG[GO_TO_PAGE]:</span> <input type='text' size='1' value='' name='gotopage' id='gotopage' class='bselect'/> <span onclick=\"changepagepost();\" class='button_mini'>$LANG[SUBMIT]</span>
				</div>";
	
	return $searchresult."::arrdlm::".$search0."::arrdlm::".$search1."::arrdlm::".$searchdatesav."::arrdlm::".$retstr."::arrdlm::".$search_inthread;
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

		return;
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
	

?>