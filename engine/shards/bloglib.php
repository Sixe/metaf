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

// bloglib.php

    // Get permissions *once*
    if (isInGroup($CURRENTUSER, "admin") || isInGroup($CURRENTUSER, "level8"))
        $verifyEditDelete = true;
    else
        $verifyEditDelete = false;


	function generateBlog($data) {
		global $siteSettings;
		global $verifyEditDelete;
		global $verifyBlogger;
		global $CURRENTUSERAJAX;
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $LANG;

		$dataLine = explode(":!bl@:", $data);

		if (!is_numeric($dataLine[0]))
			exit();

		$ID = "0";
		if ($dataLine[0])
			$ID = make_num_safe($dataLine[0]);

		$userID = "0";
		if ($dataLine[1])
			$userID = make_num_safe($dataLine[1]);

		$channel = "0";
		if ($dataLine[2])
			$channel = make_num_safe($dataLine[2]);

		$action = make_var_safe($dataLine[3]);

		$pc = "1";
		if ($dataLine[4])
			$pc = make_num_safe($dataLine[4]);

		$jt = "";
		if ($CURRENTUSERAJAX or $CURRENTUSER == "anonymous")
			$jt = "</span>";

		$blogad = "<div id='blogad'></div>";

		$blog_user = "";
		$blogfrom = "";
		$bloguserpannel = "";
		$blogtitle = "";
		$blog_thread_title = "";
		if ($userID) {
			$blog = mf_query("select * from blog where userID = $userID limit 1");
			$blog = mysql_fetch_assoc($blog);
			if ($blog) {
				$blogtitle = $blog['title'];
				$blogsubtitle = $blog['subtitle'];
				$webname = $blog['webname'];
				$blogview = $blog['view'];
			}
			$blog_user = "&amp;userID=$userID";
			$bloguser = mf_query("select username, avatar, rating as userRating from users where ID = $userID limit 1");
			$bloguser = mysql_fetch_assoc($bloguser);
			$blogfrom = $bloguser['username'];
			$bloguseravatar = $bloguser['avatar'];
			if ($bloguser['avatar'] == "")
				$bloguseravatar = "engine/grafts/" . $siteSettings['graft'] . "/images/noavatar.png";
            if ($bloguser['userRating'] > 0)
				$bloguserrating = "<span class='userNriDisplay'>".number_format($bloguser['userRating'], 4)."</span>";
			$blogview = "$LANG[BLOG_VIEW]$blogview $LANG[BLOG_VIEW2]";
			$bloguserpannel = "<div class='postUserInfo' style='width:110px;'>
				<div class='avatar'><img class='avatarPicture' src='$bloguseravatar' alt='$LANG[AVATAR]'/></div>
				<a href='xml/$userID.xml'><img src='engine/grafts/$siteSettings[graft]/images/rss.gif' alt='RSS' /></a>
				<a href=\"index.php?shard=forum&amp;action=un2id&amp;name=$blogfrom\">
				<span onclick=\"userprofile('".urlencode($blogfrom)."','bas'); return false;\">
				$blogfrom</span></a>
				<br/>$bloguserrating<br/>
				<br/>$blogview<br/>";
			if ($blogtitle)
				$blogfrom = "<div class='blogheaduser' id='blogtitle'>
					<div id='blogtitleuser' class='blogtitleuser'>$blogtitle</div>
					<div id='blogsubtitleuser' class='blogsubtitleuser'>$blogsubtitle</div></div>";
			else
				$blogfrom = "<div class='bloghead' id='blogtitle'>
					<center>$LANG[BLOG_USER_TITLE1]$blogfrom $LANG[BLOG_USER_TITLE2]</center></div>";
		}
		else
			$webname = "blog";
		
		if (!$siteSettings['mod_rewrite'])
			$webname = "";

		$countview = "";
		$blogs = mf_query(generateBlogStr(isset($ID)?$ID:NULL,$userID,$channel,$pc,0));
		$pageStr = "";
		if ($ID == "0")
			$pageStr = generatePageStr(generateBlogStr(0,$userID,$channel,0,1),$pc,$userID);
		else
			$countview = mf_query("update forum_topics set num_views = num_views + 1 where ID = $ID AND userID != $CURRENTUSERID limit 1");
		$leftbloglist = leftbloglist(generateBlogStr(0,$userID,$channel,0,2),$userID,$webname);

		$bhead = New contentObj;
		$bhead->primaryContent .= "$blogad<div id='parentC'>$blogfrom<div class='blogbuttontop'>";
		if (($verifyBlogger and !$userID) or ($CURRENTUSERID == $userID)) {
			$bhead->primaryContent .= "<div style='float:left;'>
				<span onclick=\"callNewThreadForm();\" title='$LANG[CREATE_NEW_THREAD]' class='button'>
				$LANG[NEW_THREAD] <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt='$LANG[NEW_THREAD]]' /></span>";
			if ($CURRENTUSERID == $userID)
				$bhead->primaryContent .= "&nbsp;&nbsp;&nbsp;&nbsp;
					<span onclick=\"showblogConf();\" title='$LANG[BLOG_CONF_LINK]' class='button'>
					$LANG[BLOG_CONF_LINK] <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt='$LANG[BLOG_CONF_LINK]' /></span>";
			$bhead->primaryContent .= "</div>";
		}
		$bhead->primaryContent .= "<span style='float:right;'>";
		if ($userID != $CURRENTUSERID) {
			$gowebname = "";
			if ($siteSettings['mod_rewrite']) {
				$readwebname = mf_query("select webname from blog where userID = '$CURRENTUSERID' limit 1");
				$readwebname = mysql_fetch_assoc($readwebname);
				if ($readwebname['webname'])
					$gowebname = $readwebname['webname'];
			}
			if ($gowebname)
				$bhead->primaryContent .= "<a href='$gowebname.html' title='$LANG[BLOG_YOURS]' class='button'>$LANG[BLOG_YOURS]</a>&nbsp;&nbsp;&nbsp;&nbsp;";
			else
				$bhead->primaryContent .= "<a href='".make_link("blog","&amp;userID=$CURRENTUSERID")."' title='$LANG[BLOG_YOURS]' class='button'>$LANG[BLOG_YOURS]</a>&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		$bhead->primaryContent .= "<a href='".make_link("blog","&listblog=1$blog_user")."' onclick=\"showBlogList(); return false;\" title='$LANG[BLOGS_LIST]' class='button'>$LANG[BLOGS_LIST] <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt='$LANG[BLOGS_LIST]' /></a></span></div>
			<div id='BlogList' class='blogListPlaceholder'></div>
			<div id='blogConf' class='blogPlaceholder'></div>
			<div id='newThreadFormPlaceholder' class='blogPlaceholder'></div>";

		$bhead->primaryContent .= "<div class='post'><table width='100%'><tr>
			<td style='width:125px;vertical-align:top;>
			<div class='blogLeft'>$bloguserpannel</div>
			<div class='blogleftlist'>$leftbloglist</div></td>
			<td style='vertical-align:top;'>";

		if($action=="g_view" || $channel) {
			$bhead->primaryContent .= "<div class='blogpagestr'>";
			if ($webname)
				$bhead->primaryContent .= "<a href='".$webname.".html'>";
			else
				$bhead->primaryContent .= "<a href='".make_link("blog","$blog_user")."'>";
			if ($jt)
				$bhead->primaryContent .= "<span onclick=\"emptymainBlog(0,$userID,0,'g_default'); return false;\">";
			$bhead->primaryContent .= "$LANG[ALLPOSTS].$jt</a>&nbsp;$pageStr</div>";
		} 
		else
			$bhead->primaryContent .= "<div class='blogpagestr'>$pageStr</div>";

		$bhead->primaryContent .= "
		<span id='blogcache' style='display:none'>$ID:!bl@:$userID:!bl@:$channel:!bl@:$action:!bl@:$pc:!bl@:$webname</span>
		<div id='timestamp' class='" . time() . "'></div>
		<div id='blogparentC'>";
		$shardContentArray[] = $bhead;

		$nb = New contentObj;
		$i = 1;
		while ($row = mysql_fetch_assoc($blogs)) {
			$nb->primaryContent .= generateBlogCore($row,$action,$i,$userID,$channel,$blog_user,$webname);
			$i ++;
			if ($countview)
				$blog_thread_title = " - ".$row['title'];
		}

		$shardContentArray[] = $nb;
		
		$bbot = New contentObj;
		$bbot->primaryContent .= "</div></td></tr></table></div>
			<div class='clearfix'></div><div class='blogpagestr'>$pageStr</div>";
		$bbot->primaryContent .= "</div>";
		$shardContentArray[] = $bbot;

		$retstr = "";

		for ($i=0;$i<sizeof($shardContentArray);$i++)
			$retstr .= renderPost($shardContentArray, $i);
		if ($blogtitle)
			$nav_title = $blogtitle;
		else
			$nav_title = $LANG['BLOG_USER_TITLE1'].$siteSettings['titlebase'].$LANG['BLOG_USER_TITLE2'];
		$nav_title .= $blog_thread_title;
		return $retstr."</div>::cur@blo::$action::cur@blo::".$nav_title;
	}


	function generateBlogCore($row,$action,$i,$userID="",$channel="",$blog_user="",$webname="") {
		global $siteSettings;
		global $LANG;
		global $CURRENTUSERRATING;
		global $CURRENTUSER;
		global $CURRENTUSERAJAX;
		global $verifyEditDelete;
		
		$jt = "";
		if ($CURRENTUSERAJAX or $CURRENTUSER == "anonymous")
			$jt = "</span>";

		$context = "blogpreview";

		if($action=="g_view")
			$siteSettings['titledesc'] = $row['title'];
		else if(isset($_REQUEST['channel']))
			$siteSettings['titledesc'] = "$LANG[CATEGORY]: $row[categoryname]";

		$nb = "<div class='blogWrapper'>";
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
		$postRatingColorGradient = "postRatingColorGradient1";
		if ($row['rating'] > 0) {
			$showPlusSign="+";
			$postRatingColorGradient = "postRatingColorGradient2";
		}
		else if ($row['rating'] < 0)
			$postRatingColorGradient = "postRatingColorGradient3";

		$ratingStr = "<div id='rating$row[ID]' style='display: none;'>".number_format($row['rating'], 4)."</div>
			<div style='margin-bottom: 1px; margin-left: 0px; float:none; margin-right: 0px;' class='$upClass' id='uparrowthread$row[ID]' onclick=\" toggleRatingArrow('thread', $row[ID], 'uparrow', ".number_format($CURRENTUSERRATING, 4).");\"></div>
			<span id='ratingDisplaythread$row[ID]' class='$postRatingColorGradient'>$showPlusSign".number_format($row['rating'],2)."</span>
			<div style='margin-top: 1px; margin-left: 0px; float:none; margin-right: 0px;' class='$downClass' id='downarrowthread$row[ID]' onclick=\" toggleRatingArrow('thread', $row[ID], 'downarrow', ".number_format($CURRENTUSERRATING, 4).");\"></div>
			$displayClass";

		$nb .= "<table><tr><td class='blogratebloc'><div><center>$ratingStr</center></div></td>
			<td style='vertical-align:top;width:100%;'><div class='blogtitlebloc'><div class='BlogTitle' id='blogid$row[ID]'>";
		if ($webname)
			$nb .= "<a href='$webname.html#blog/$userID/$row[ID]/0/g_view/1'>";
		else
			$nb .= "<a href='".make_link("blog","&amp;action=g_view&amp;ID=$row[ID]$blog_user")."'>";
		if ($jt)
			$nb .= "<span onclick=\"emptymainBlog($row[ID],$userID,$channel,'g_view','',$i); return false;\">";
		$nb .= " $row[title]$jt</a>";
		if (($verifyEditDelete) || ($CURRENTUSER == $row['user'])) {
			if ($blog_user)
				$bloglink = "&amp;blog=2";
			else
				$bloglink = "&amp;blog=1";
			$nb .= "<a title='$LANG[EDIT_THREAD]' href='".make_link("forum","&amp;action=g_editThread&amp;ID=$row[ID]$bloglink")."'> <img src='engine/grafts/" . $siteSettings['graft'] . "/images/edit.gif' class='edHolder' alt='$LANG[EDIT_THREAD]' /></a>";
		}
		$nb .= "</div>";

		$locked = $row['locked']==1?"<img src='engine/grafts/" . $siteSettings['graft'] . "/images/lock.gif' alt='$LANG[LOCKED]' />":"";
		$nb .= "<div class='blogbyline'>$LANG[POSTEDBY] 
			<a href=\"index.php?shard=forum&amp;action=un2id&amp;name=$row[user]\">
		<span onclick=\"userprofile('".urlencode($row['user'])."',$i); return false;\">$row[user]</span>
		</a> $LANG[ON] " . date($LANG['DATEFORMAT'],$row['postDate'])." $LANG[AT] ".date($LANG['TIMEFORMAT'],$row['postDate'])." $locked ";
		$nb .= "&nbsp; - &nbsp; $LANG[CATEGORY] : 
			<a href='".make_link("blog","&amp;channel=$row[category]$blog_user")."'>";
		if ($jt)
			$nb .= "<span onclick=\"emptymainBlog(0,$userID,$row[category],'g_default'); return false;\">";
		$nb .= "$row[categoryname]$jt</a>";
		$nb .= "&nbsp; - &nbsp; $LANG[NUM_VIEW_1]: <span id='numview$row[ID]'>$row[num_views]</span> $LANG[BLOG_VIEW2]";

		$nb .= "</div></div></td></tr></table>";

		$body = $row['body'];
		if($action!="g_view" and strlen($body) > 1000) {
			$body = preg_replace("/\[embed\](.+?)\[\/embed\]/i", " [ $LANG[BLOG_MEDIA] ] ", $body);
			$body = preg_replace("/\[youtube\](.+?)\[\/youtube\]/i", " [ $LANG[BLOG_MEDIA] ] ", $body);
			$body = preg_replace("/\[daily\](.+?)\[\/daily\]/i", " [ $LANG[BLOG_MEDIA] ] ", $body);
			$body = format_post($body,true,$context,$row['ID']);
			$body = "<div class='blogbody' style='height: 350px;'>$body</div>";
			$body .= "<div class='blogseenext'>[...] ";
			if ($webname)
				$body .= "<a href='$webname.html#blog/$userID/$row[ID]/0/g_view/1'>";
			else
				$body .= "<a href='".make_link("blog","&amp;action=g_view&amp;ID=$row[ID]$blog_user")."'>";
			if ($jt)
				$body .= "<span onclick=\"emptymainBlog($row[ID],$userID,0,'g_view','',$i); return false;\">";
			$body .= "$LANG[SEE_NEXT2]$jt</a></div><br/><br/>";
		}
		else
			$body = "<div class='blogbody'>".format_post($body,true,$context,$row['ID'])."</div>";

		if ($row['spoiler'])
			$body = "<span onclick=\"toggleLayer('spoil$row[ID]');\" style='cursor:pointer'>
				<b><u>$LANG[SPOILER_TEXT]</u></b></span>
				<div id='spoil$row[ID]' style='display:none'><br/>$body</div>";

		$nb .= $body;

		if ($row['poll'] > 0) {
			$nb .= "<div id='pollHolder'>";
			$nb .= renderPollResults($row['poll']);
			$nb .= "</div>";
			$nb .= "<div class='pollBottom'><a href='#' id='voteLink' onclick=\" toggleLayer('pollQuestionsHolder'); toggleLayer('pollResultsHolder'); return false;\">$LANG[CAST_VOTE]</a></div>";
		}

		$com = $row['numComments'] -1;
		$num_new = $row['num_new'];
		if ($num_new > $com)
			$num_new = $com;
		$datefirstpost = $row['postDate'] + 2;
		$nb .= "<div class='clearfix'></div>";
		$nb .= "<div class='blogcomments'><center>[ ";
		if ($com > 0) {
			$nb .= "<a href='".make_link("forum","&amp;action=calculatePageLocationForFirstNew&amp;ID=$row[ID]&amp;sl=$datefirstpost")."'>";
			if ($jt)
				$nb .= "<span onclick=\"emptymainThread($row[ID],$datefirstpost,'','','',$i); return false;\" style='cursor:pointer;'>";
			$nb .= "$LANG[COMMENTS] ($com)$jt</a> / ";
			if ($num_new > 0)
			{
				$nb .= "<a href='".make_link("forum","&amp;action=calculatePageLocationForFirstNew&amp;ID=$row[ID]&amp;sl=$row[last_read_date]")."'>";
				if ($jt)
					$nb .= "<span onclick=\"emptymainThread($row[ID],$row[last_read_date],'','','',$i); return false;\" style='cursor:pointer;'>";
				$nb .= "$LANG[BLOG_UNREAD_COMMENT] ($num_new)$jt</a> / ";
			}
			else
				$nb .= "$LANG[BLOG_UNREAD_COMMENT] (0) / ";
		}
		else
			$nb .= "$LANG[COMMENTS] (0) / ";
		$nb .= "<a href='".make_link("forum","&amp;action=calculatePageLocationForFirstNew&amp;ID=$row[ID]&amp;sl=$row[lastPostDate]")."'>";
		if ($jt)
			$nb .= "<span onclick=\"emptymainThread($row[ID],'0','0','1','reply',$i); return false;\" style='cursor:pointer;'>";
		$nb .= "$LANG[COMMENTS2]$jt</a> | ";
		if ($webname)
			$nb .= "<a href='".$webname."_".$row['ID'].".html'>";
		else
		$nb .= "<a href='".make_link("blog","&amp;action=g_view&amp;ID=$row[ID]$blog_user")."'>";
		if ($jt)
			$nb .= "<span onclick=\"emptymainBlog($row[ID],$userID,0,'g_view','',$i); return false;\">";
		$nb .= "$LANG[PERMALINK]$jt</a> ]";
		$i ++;
		$nb .= "</center></div><a name='$i'></a></div>";

		return $nb;
	}



	function refreshblogCore($data) {
		global $siteSettings;
		global $verifyEditDelete;
		global $verifyBlogger;
		global $CURRENTUSERAJAX;
		global $CURRENTUSER;
		global $CURRENTUSERID;

		$dataLine = explode(":!bl@:", $data);

		if (!is_numeric($dataLine[0]))
			exit();

		$ID = "0";
		if ($dataLine[0])
			$ID = make_num_safe($dataLine[0]);

		$userID = "0";
		$blog_user = "";
		if ($dataLine[1]) {
			$userID = make_num_safe($dataLine[1]);
			$blog_user = "&amp;userID=$userID";
		}

		$channel = "0";
		if ($dataLine[2])
			$channel = make_num_safe($dataLine[2]);

		$action = make_var_safe($dataLine[3]);

		$pc = "1";
		if ($dataLine[4])
			$pc = make_num_safe($dataLine[4]);

		$webname = "0";
		if ($dataLine[5])
			$webname = make_var_safe($dataLine[5]);

		$jt = "";
		if ($CURRENTUSERAJAX or $CURRENTUSER == "anonymous")
			$jt = "</span>";

		$blogs = mf_query(generateBlogStr(isset($ID)?$ID:NULL,$userID,$channel,$pc,0));

		$i = 1;
		$nb = "";
		while ($row = mysql_fetch_assoc($blogs)) {
			$nb .= generateBlogCore($row,$action,$i,$userID,$channel,$blog_user,$webname);
			$i ++;
		}
		
		return $nb."::cur@blo::vide";
	}
	

	
	function generateBlogStr($ID=NULL,$tu="",$channel="",$pc="", $totalCount=0,$timeAgo=0) {
		global $CURRENTUSERID;
		global $CURRENTUSER;
		global $verifyEditDelete;
		
		$numcom = "num_comments";
		$last_post_user = "last_post_user";
		$last_post_id = "last_post_id";
		$last_post_date = "last_post_date";
		if ($verifyEditDelete) {
			$numcom = "num_comments_T";
			$last_post_user = "last_post_user_T";
			$last_post_id = "last_post_id_T";
			$last_post_date = "last_post_date_T";
		}

		$limitID = "";
		if ($ID)
			$limitID = "f1.ID = $ID AND";

		if ($pc)
			$_REQUEST['pageCount'] = $pc;
		if (array_key_exists('pageCount', $_REQUEST)) {
			$pc = make_num_safe( $_REQUEST['pageCount']);

			if ($pc == "1")
				$limitBoundary = "0,10"; 
			else {
				$upperBound = $pc * 10;
				$lowerBound = $upperBound - 10;
				$limitBoundary = "$lowerBound, 10";
			}
		}
		else
			$limitBoundary = "0,10";

		if ($ID)
			$limitBoundary = "1";

		if (!is_numeric($CURRENTUSERID))
			$CURRENTUSERID=0;

		$exclusiveChannel = "";
		if ($channel) {
			$chan = make_num_safe($channel);
			$exclusiveChannel = "f1.category=$chan AND";
		}

		$channelFilterList = "";
		if (array_key_exists('metaChannelFilter', $_COOKIE)) {
			if ($_COOKIE['metaChannelFilter'] != "") {
				$filterArray = explode(",", $_COOKIE['metaChannelFilter']);

				foreach($filterArray as $channel) {	
					if ($channel != "")
						$channelFilterList .= "f1.category <> ".make_num_safe($channel)." AND ";
				}
			}
		}

		$threadTypeSelector = "";
		if ($tu)
			$threadTypeSelector = "f1.pthread = 0 AND f1.threadtype < 3 AND f1.userID = ".make_num_safe($tu)." AND f1.blog = 2 ";
		else
			$threadTypeSelector = "f1.pthread = 0 AND f1.threadtype < 3 AND (f1.blog = 1 OR f1.news = 1) ";

//		$pThreadsOnly = "AND (f2.pthread = 0 OR (f2.pthread=1 AND fh.userID IS NOT NULL))";

		$last_date_flag = "";
		if ($timeAgo > 0)
			$last_date_flag = "f1.$last_post_date > $timeAgo AND";
			
		$fhUser = " AND fh.userID = $CURRENTUSERID";
		
		if ($totalCount == 0) {
			$forumsStr = "SELECT
					f2.*,
					c1.name as categoryname,
					pr.rating as userrated,
					IFNULL(numComments - fh.num_posts, numComments) as num_new,
					fh.date as last_read_date
				FROM (
					SELECT
						f1.ID,
						f1.title,
						f1.body,
						f1.user,
						f1.$numcom as numComments,
						f1.num_views,
						f1.rating,
						f1.date as postDate,
						f1.category,
						f1.pthread,
						f1.$last_post_id as lastPostID,
						f1.$last_post_date as lastPostDate,
						f1.$last_post_user as lastPostUser,
						f1.threadtype,
						f1.blog,
						f1.locked,
						f1.poll,
						f1.spoiler
					FROM forum_topics as f1
					WHERE
						$limitID
						$exclusiveChannel
						$last_date_flag
						$threadTypeSelector
					ORDER BY postDate desc
					
				) as f2
				LEFT JOIN postratings AS pr
					ON (
						pr.threadID = f2.ID AND
						pr.user = \"$CURRENTUSER\"
					)
				LEFT JOIN categories AS c1
					ON (
						f2.category = c1.ID
					)
				LEFT JOIN fhits as fh
					ON (
						fh.threadID = f2.ID
						$fhUser
					)
					LIMIT $limitBoundary";
		}
		if ($totalCount == 1) {
			$forumsStr = "SELECT
					count(f2.id) as Expr1
				FROM (
					SELECT
						f1.ID,
						f1.title,
						f1.rating,
						f1.date as postDate,
						f1.category,
						f1.pthread,
						f1.threadtype,
						f1.blog,
						f1.locked
					FROM forum_topics as f1
					WHERE
						$limitID
						$exclusiveChannel
						$last_date_flag
						$threadTypeSelector
					) as f2";
		}
		if ($totalCount == 2) {
			$forumsStr = "SELECT
						f1.ID,
						f1.title,
						f1.$numcom - 1 as numComments,
						f1.date as postDate,
						f1.category,
						f1.pthread,
						f1.threadtype,
						f1.blog,
						f1.locked
					FROM forum_topics as f1
					WHERE
						$limitID
						$exclusiveChannel
						$last_date_flag
						$threadTypeSelector
					ORDER BY postDate desc
					LIMIT 0,50";
		}
		return $forumsStr;
	}


function generateBlogList() {
	global $LANG;
	global $siteSettings;
	
	$bll = "SELECT
				userID as ID,
				user as username
			FROM forum_topics 
			WHERE
				threadtype < 3 AND 
				pthread = 0 AND 
				blog = 2 
			ORDER by username
				";

	$bll = mf_query($bll);
	$retStr = "<b>$LANG[BLOGS_LIST]</b><br/><table class='BlogListing'><tr><td><table cellspacing=0 class='BlogListing'><td>";
	$nblog = mysql_num_rows($bll);
	$user = "";
	$userID = "";
	$i = 0;
	$j = 0;
	$k = 1;
	$nbloguser = 0;
	while ($row = mysql_fetch_assoc($bll)) {
		if ($row['ID'] != $userID) {
			if ($user) {
				if ($siteSettings['mod_rewrite']) {
					$webname = "";
					$readwebname = mf_query("select webname, title, subtitle from blog where userID = '$userID' limit 1");
					$readwebname = mysql_fetch_assoc($readwebname);
					if ($readwebname['webname'])
						$webname = $readwebname['webname'];
				}
				$title = $user;
				if ($readwebname['title'])
					$title = "$user - <b>$readwebname[title]</b> - $readwebname[subtitle]";
				$numt = "$k $LANG[THREADSB]";
				if ($k > 1)
					$numt = "$k $LANG[THREADSBS]";
				if ($webname)
					$retStr .= "<tr><a href='$webname.html'>$title <i>($numt)</i></a><br/></tr>";
				else
					$retStr .= "<tr><a href='index.php?shard=blog&amp;action=g_default&amp;userID=$userID'>$title <i>($numt)</i></a><br/></tr>";

			}
			$userID = $row['ID'];
			$user = $row['username'];
			$k = "1";
		}
		else
			$k ++;
	}
	if ($siteSettings['mod_rewrite']) {
		$webname = "";
		$readwebname = mf_query("select webname, title, subtitle from blog where userID = '$userID' limit 1");
		$readwebname = mysql_fetch_assoc($readwebname);
		if ($readwebname['webname'])
			$webname = $readwebname['webname'];
	}
	$title = $user;
	if ($readwebname['title'])
		$title = "$user - <b>$readwebname[title]</b> - $readwebname[subtitle]";
	$numt = "$k $LANG[THREADSB]";
	if ($k > 1)
		$numt = "$k $LANG[THREADSBS]";
	if ($webname)
		$retStr .= "<tr><a href='$webname.html'>$title <i>($numt)</i></a><br/></tr>";
	else
		$retStr .= "<tr><a href='".make_link("blog","&amp;userID=$userID")."'>$title <i>($numt)</i></a><br/></tr>";
	$retStr .= "</td></table></td></td></table>";
	
	return $retStr;
}

function generatePageStr($pageStr,$pc,$userID="0") {
	global $LANG;
	global $siteSettings;
	
	$pageCount = mf_query($pageStr);
	$pageCount = mysql_fetch_assoc($pageCount);
	$numPages = ceil(($pageCount['Expr1'] / 10));
	$retStr = "";
	$pagprec = "";
	$pagsuiv = "";
	for ($page = 1; $page<=$numPages; $page++ ) {
		if ($page == $pc)
			$pageCountStr = "<span class='pageListSelected'";
		else
			$pageCountStr = "<span class='pageListUnSelected'";

		$retStr .= "$pageCountStr onclick=\"emptymainBlog(0,$userID,0,'g_default',$page);\">$page</span>";
	}

	if ($numPages > 0) {
		if ($pc > 1 and $numPages >1) {
			$prev_page = $pc -1;
			$pagprec = "<a href='".make_link("blog","&amp;pageCount=$prev_page")."' onclick=\"emptymainBlog(0,$userID,0,'g_default',$prev_page); return false;\" class='button_mini' style='vertical-align: middle;display: inline-block;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_left.png' style='vertical-align: inherit; margin-top: 0px;' alt='$LANG[PREVIOUS_PAGE]' />$LANG[PREVIOUS_PAGE]</span></a>";
		}
		if ($pc < $numPages) {
			$next_page = $pc +1;
			$pagsuiv = "<a href='".make_link("blog","&amp;pageCount=$next_page")."' onclick=\"emptymainBlog(0,$userID,0,'g_default',$next_page); return false;\" class='button_mini' style='vertical-align: middle;display: inline-block;'>$LANG[NEXT_PAGE]<img src='engine/grafts/" . $siteSettings['graft'] . "/images/arrow_right.png' style='vertical-align: inherit; margin-top: 0px;' alt='$LANG[NEXT_PAGE]' /></a>";
		}
		$retStr = $pagprec."&nbsp;&nbsp;".$LANG['PAGES'].": ".$retStr ."&nbsp;&nbsp;".$pagsuiv;
	}
	else
		$retStr = "<table><tr><td><div id=\"pageCountLeft\">$LANG[PAGES]: 0</div></td></tr></table>";

	return $retStr;
}
function leftbloglist($pageStr,$userID="0",$webname="") {
	global $LANG;
	global $CURRENTUSERAJAX;
	global $CURRENTUSER;

	if ($userID)
		$blog_user = "&amp;userID=$userID";

	$jt = "";
	if ($CURRENTUSERAJAX or $CURRENTUSER == "anonymous")
		$jt = "</span>";

	$pageStr = mf_query($pageStr);
	$leftStr = "";
	while ($row = mysql_fetch_assoc($pageStr)) {
		$leftStr .= "<br/>$LANG[ON] ".date($LANG['DATEFORMAT'],$row['postDate'])." $LANG[AT] ".date($LANG['TIMEFORMAT'],$row['postDate'])."<br/>";
		if ($webname)
			$leftStr .= "<a href='$webname.html#blog/$userID/$row[ID]/0/g_view/1'>";
		else
			$leftStr .= "<a href='".make_link("blog","&amp;action=g_view&amp;ID=$row[ID]$blog_user")."'>";
		if ($jt)
			$leftStr .= "<span onclick=\"emptymainBlog($row[ID],$userID,'','g_view'); return false;\">";
		$leftStr .= "$row[title]$jt</a><br/><br/>";
	}
	
	return $leftStr;
}
function generateBlogConf() {
	global $LANG;
	global $CURRENTUSERID;
	global $CURRENTUSER;
	global $siteSettings;
	
	$blog = mf_query("select * from blog where userID = $CURRENTUSERID limit 1");
	$blog = mysql_fetch_assoc($blog);
	if (!$blog)
		$blogcreate = mf_query("insert into blog (user, userID) values ('$CURRENTUSER', $CURRENTUSERID)");

	$title = $blog['title'];
	$subtitle = $blog['subtitle'];
	$webname = $blog['webname'];
	$retStr = "<form action='index.php' name='blogconf' method='post'><table align='center'>";
	$retStr .= "<tr><td style='text-align:right;'>$LANG[BLOG_TITLE]: </td>";
	$retStr .= "<td><input class='bselect' type='text' name='blogtitle' id='inputblogtitle' size='32' maxlength='32' value=\"$title\" /></td></tr>";
	$retStr .= "<tr><td style='text-align:right;'>$LANG[BLOG_SUBTITLE] : </td>";
	$retStr .= "<td><input class='bselect' type='text' name='blogsubtitle' id='inputblogsubtitle' size='84' maxlength='84' value=\"$subtitle\" /></td></tr>";
	if ($siteSettings['mod_rewrite'])
		$retStr .= "<tr><td style='text-align:right;'>$LANG[BLOG_URL_SHORTCUT]: </td>
			<td><input class='bselect' type='text' name='blogwebname' id='inputblogwebname' size='20' maxlength='20' value=\"$webname\" /></td></tr>";
	$retStr .= "<tr><td></td><td><br/>";
	$retStr .= "<span onclick=\"saveblogConf();\" title='$LANG[APPLY]' class='button'>$LANG[APPLY]</span>";
	$retStr .= "&nbsp;&nbsp;<span onclick=\"showblogConf();\" title='$LANG[CANCEL]' class='button'>$LANG[CANCEL]</span>";
	$retStr .= "</tr></table></form>";
	
	return $retStr;
}

function saveBlogConf($data) {
	global $LANG;
	global $CURRENTUSERID;
	global $CURRENTUSER;
	global $siteSettings;

	$dataline = explode(":@#!:", $data);
	$title = make_var_safe(htmlspecialchars($dataline[0]));
	$subtitle = make_var_safe(htmlspecialchars($dataline[1]));

	$blog = mf_query("update blog set title = '$title', subtitle = '$subtitle' where userID = $CURRENTUSERID limit 1");

	if ($dataline[2] and $siteSettings['mod_rewrite']) {
		$webname = make_var_safe($dataline[2]);
		$webname = str_replace(" ","_",$webname);
		$webname = rawurlencode($webname);
		$readwebname = mf_query("select webname from blog where userID != '$CURRENTUSERID' and webname = \"$webname\" limit 1");
		if (!mysql_fetch_assoc($readwebname))
			$blog = mf_query("update blog set webname = \"$webname\" where userID = $CURRENTUSERID limit 1");
		else {
			$title = "ERREUR !";
			$subtitle = "Cette adresse est déjà prise";
		}
	}
	
	$subtitle = stripslashes($subtitle);
	$retStr = "<div id='blogtitleuser' class='blogtitleuser'>$title</div>
			<div id='blogsubtitleuser' class='blogsubtitleuser'>$subtitle</div>";
	
	return $retStr;
}

function blogUpdate($data) {
	$dataLine = explode(":!bl@:", $data);
	
		$ID = "0";
		if ($dataLine[0])
			$ID = make_num_safe($dataLine[0]);

		$userID = "0";
		$blog_user = "";
		if ($dataLine[1]) {
			$userID = make_num_safe($dataLine[1]);
			$blog_user = "&amp;userID=$userID";
		}

		$channel = "0";
		if ($dataLine[2])
			$channel = make_num_safe($dataLine[2]);

		$action = make_var_safe($dataLine[3]);

		$pc = "1";
		if ($dataLine[4])
			$pc = make_num_safe($dataLine[4]);

		$timeAgo = make_num_safe($dataLine[5]);

		$blogs = mf_query(generateBlogStr(isset($ID)?$ID:NULL,$userID,$channel,$pc,0,$timeAgo));

		$retStr = "";
		if (mysql_num_rows($blogs) > 0)
			$retStr = "1";
		
	return $retStr."::cur@blo::".time();
}

?>