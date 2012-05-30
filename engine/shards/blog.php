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

// blog.php

	require("bloglib.php");
	require("user_profilelib.php");
	require("forumlib.php");
	foreach($_REQUEST as $keyVar => $value) {
		$_REQUEST[$keyVar] = xss_clean($_REQUEST[$keyVar]);		
	}

	switch( $action ):

	case "g_default": 
	case "g_view":
	

	if ($CURRENTUSER != "anonymous" && $CURRENTUSER != "bot") {
		if (!isInGroup($CURRENTUSER, 'log_ip') && (($verifyEditDelete) or (isInGroup($CURRENTUSER, 'level0')) or (isInGroup($CURRENTUSER, 'level1')) or (isInGroup($CURRENTUSER, 'level7'))))
			$ip = "";
		else
			$ip=$_SERVER["REMOTE_ADDR"];
	
		$update = mf_query("update users set lat=".time().", laid=0, ip='$ip' where ID=$CURRENTUSERID limit 1");
	}
	if ($CURRENTUSERAJAX || $CURRENTUSER == "anonymous")
		$jt = "</span>";

	$userID = "0";
	$channel = "0";
	if (array_key_exists('channel', $_REQUEST))
		$channel = make_num_safe($_REQUEST['channel']);

	$pc = "1";
	if (array_key_exists('pageCount', $_REQUEST))
		$pc = make_num_safe($_REQUEST['pageCount']);

	$listblog = "";
	$displaybloglist = "";
	if (array_key_exists('listblog', $_REQUEST)) {
		$listblog = generateBlogList();
		$displaybloglist = "display:block;";
	}
	
	$webname = "";
	if (array_key_exists('blog', $_REQUEST)) {
		$blogwebname = make_var_safe($_REQUEST['blog']);
//		$blogwebname = substr($blogwebname,1,strlen($blogwebname) -1);
		$readwebname = mf_query("SELECT userID, webname FROM blog WHERE webname = \"$blogwebname\" limit 1");
		$readwebname = mysql_fetch_assoc($readwebname);
		$userID = $readwebname['userID'];
		$webname = $readwebname['webname'];
		if ($userID)
			$_REQUEST['userID'] = $userID;
	}

	$ID = "0";
	if (array_key_exists('ID', $_REQUEST))
		$ID = make_num_safe($_REQUEST['ID']);
	
	$siteSettings['titledesc'] = $LANG['BLOG_USER_TITLE1'].$siteSettings['titlebase'].$LANG['BLOG_USER_TITLE2'];
	$blogfrom = "";
		$blog_user = "";
	$bloguserpannel = "";
		$blogad = "<div id='blogad'>
			<center><script type=\"text/javascript\"><!--
google_ad_client = \"pub-1572380860248461\";
/* 728x90 blog */
google_ad_slot = \"0346237214\";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type=\"text/javascript\"
src=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\">
</script></center></div>";
		if (array_key_exists('userID', $_REQUEST))
		{
			$blogview = "";
			$blogtitle = "";
			$userID = make_num_safe($_REQUEST['userID']);
			$blog = mf_query("select * from blog where userID = $userID limit 1");
			$blog = mysql_fetch_assoc($blog);
			if ($blog)
			{
				$blogtitle = $blog['title'];
				$blogsubtitle = $blog['subtitle'];
				$blogview = $blog['view'];
			}
			$blog_user = "&amp;userID=$userID";
			$bloguser = mf_query("select username, avatar, rating as userRating from users where ID = $userID limit 1");
			$bloguser = mysql_fetch_assoc($bloguser);
			$blogfrom = $bloguser['username'];
			if (!$blog)
				$blogcreate = mf_query("insert into blog (user, userID) values ('$blogfrom', $userID)");
			if ($userID != $CURRENTUSERID)
				$blogupdate = mf_query("update blog set view = view +1 where userID = $userID limit 1");
			$bloguseravatar = $bloguser['avatar'];
			if ($bloguser['avatar'] == "")
				$bloguseravatar = "engine/grafts/" . $siteSettings['graft'] . "/images/noavatar.png";
            $bloguserrating = "";
            if ($bloguser['userRating'] > 0)
				$bloguserrating = "<span class='userNriDisplay'>".number_format($bloguser['userRating'], 4)."</span>";
			$blogview = "$LANG[BLOG_VIEW]$blogview $LANG[BLOG_VIEW2]";	
			$bloguserpannel = "<div class='postUserInfo' style='width:110px;'>
				<div class='avatar'><img class='avatarPicture' src='$bloguseravatar' alt='$LANG[AVATAR]' /></div>
				<a href='xml/$userID.xml'><img src='engine/grafts/$siteSettings[graft]/images/rss.gif' alt='RSS' /></a>
				<a href=\"".make_link("forum","&amp;action=g_ep&amp;ID=$userID","#user/$userID")."\">
				<span onclick=\"userprofile('','bas','$userID'); return false;\">
				$blogfrom</span></a>
				<br/>$bloguserrating<br/>
				<br/>$blogview<br/></div>";
			if ($blogtitle)
			{
				$blogfrom = "<div class='blogheaduser' id='blogtitle'>
					<div id='blogtitleuser' class='blogtitleuser'>$blogtitle</div>
					<div id='blogsubtitleuser' class='blogsubtitleuser'>$blogsubtitle</div></div>";
				$siteSettings['titledesc'] = $blogtitle." - ".$blogsubtitle;
			}
			else
			{
				$blogfrom = "<div class='bloghead' id='blogtitle'>
					<center>$LANG[BLOG_USER_TITLE1]$blogfrom $LANG[BLOG_USER_TITLE2]</center></div>";
				$siteSettings['titledesc'] = $LANG['BLOG_USER_TITLE1'].$bloguser['username'].$LANG['BLOG_USER_TITLE2'];
			}
		}
	else
		$webname = "blog";

	if (!$siteSettings['mod_rewrite'])
		$webname = "";

	$pageStr = "";
		$blogs = mf_query(generateBlogStr(isset($ID)?$ID:NULL,$userID,$channel,$pc,0));
		if ($ID == "0")
			$pageStr = generatePageStr(generateBlogStr(0,$userID,$channel,0,1),$pc,$userID);
	$leftbloglist = leftbloglist(generateBlogStr(0,$userID,$channel,0,2),$userID,$webname);

		$bhead = New contentObj;
		
		$bhead->primaryContent .= "<div id='threadlist'>$blogad<div id='parentC'>$blogfrom<div class='blogbuttontop'>";
		if ($CURRENTUSER != "anonymous" && (($verifyBlogger && !$userID) || ($CURRENTUSERID == $userID))) {
			$bhead->primaryContent .= "<div style='float:left;'>
				<span onclick=\"callNewThreadForm();\" title='$LANG[CREATE_NEW_THREAD]' class='button'>
				$LANG[NEW_THREAD] <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt='$LANG[NEW_THREAD]' /></span>";
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
		$bhead->primaryContent .= "<a href='".make_link("blog","&amp;listblog=1$blog_user")."' onclick=\"showBlogList(); return false;\" title='$LANG[BLOGS_LIST]' class='button'>$LANG[BLOGS_LIST] <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt='$LANG[BLOGS_LIST]' /></a></span></div>
			<div id='BlogList' class='blogListPlaceholder' style='$displaybloglist'>$listblog</div>
			<div id='blogConf' class='blogPlaceholder'></div>
			<div id='newThreadFormPlaceholder' class='blogPlaceholder'></div>";

		$bhead->primaryContent .= "<div class='post'><table width='100%'><tr>
			<td style='width:125px;vertical-align:top;'>
			<div class='blogLeft'>$bloguserpannel</div>
			<div class='blogleftlist'>$leftbloglist</div></td>
			<td style='vertical-align:top;'>";

		if($action=="g_view" || $channel)
		{
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
		{
			$bhead->primaryContent .= "<div class='blogpagestr'>$pageStr<a name='1'></a></div>";
		}

		$bhead->primaryContent .= "
		<span id='blogcache' style='display:none'>$ID:!bl@:$userID:!bl@:$channel:!bl@:g_default:!bl@:$pc:!bl@:$webname</span>
			<div id='timestamp' class='" . time() . "'></div>
			<div id='blogparentC'>";
		$shardContentArray[] = $bhead;

		$nb = New contentObj;
		$i = 1;
		while ($row = mysql_fetch_assoc($blogs)) {
			$nb->primaryContent .= generateBlogCore($row,$action,$i,$userID,$channel,$blog_user,$webname);
			$i ++;
		}
			$shardContentArray[] = $nb;

		$bbot = New contentObj;
		$bbot->primaryContent .= "</div></td></tr></table></div>
		<div class='clearfix'></div><div class='blogpagestr'>$pageStr</div>";
		$bbot->primaryContent .= "</div></div>
								<div id='thread' style='display:none;'></div>
								<div id='user_profile' style='display:none;'></div>
							";
		$shardContentArray[] = $bbot;
		
		$siteSettings['bodyOnload'] = " onload=\"blogautorefresh()\"";

break;

	case "g_user":

		$username = make_var_safe($_REQUEST['user']);
		$user = mf_query("select ID from users where username = '$username' limit 1");
		$user = mysql_fetch_assoc($user);
		$userID = $user['ID'];

		$webname = "";
		if ($siteSettings['mod_rewrite']) {
			$readwebname = mf_query("select webname from blog where userID = '$userID' limit 1");
		$readwebname = mysql_fetch_assoc($readwebname);
			if ($readwebname['webname'])
				$webname = $readwebname['webname'];

			if ($webname)
				$header = "Location: ".$webname.".html";
		}
		if (!$webname)
			$header = "Location: ".make_link("blog","&userID=$userID");

		header($header);

	break;

endswitch;
?>