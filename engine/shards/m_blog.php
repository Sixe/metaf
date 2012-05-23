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
    // m_blog.php

function checkSelectedB($checkStr) {
	if (($_REQUEST['filter'] == $checkStr) || ($_REQUEST['filter'] == "" && $checkStr == 'thread'))
		return "Sel";
	else
		return "";
}

function m_blog_thread() {
	global $LANG;
	global $siteSettings;
	
	$s = mf_query("select * from forum_topics where pthread =0 and threadtype < 3 and blog > 0 and rating > -1 and category <> '$siteSettings[flood_ID]' order by ID desc limit 20");

	$counter = 0;
	$jumpline = "";
	$hiddenblog = "<div id='hiddenBlogs' style='display: none;'>";
	$ms = "";
			while ($row=mysql_fetch_assoc($s)) {
				$counter++;
				if ($counter != 6) {
					$ms .= "$jumpline<span class='m_blog'>";
					$ms .= "<i>$row[user] $LANG[ON] " . date($LANG['DATEFORMAT'],$row['date'])." :</i><br/>";
					if ($row['blog'] == 2)
						$ms .= "<a href='index.php?shard=blog&amp;action=g_view&amp;ID=$row[ID]&amp;userID=$row[userID]'>$row[title]";
					else
						$ms .= "<a href='index.php?shard=blog&amp;action=g_view&amp;ID=$row[ID]'>$row[title]";
					$ms .= "</a></span>";
				}
				else {
					$ms .= $hiddenblog;
					$hiddenblog = "";
					$ms .= "$jumpline<span class='m_blog'>";
					$ms .= "<i>$row[user] $LANG[ON] " . date($LANG['DATEFORMAT'],$row['date'])." :</i><br/>";
					if ($row['blog'] == 2)
						$ms .= "<a href='index.php?shard=blog&amp;action=g_view&amp;ID=$row[ID]&amp;userID=$row[userID]'>$row[title]";
					else
						$ms .= "<a href='index.php?shard=blog&amp;action=g_view&amp;ID=$row[ID]'>$row[title]";
					$ms .= "</a></span>";
				}
				$jumpline = "<br/><br/><div class='clearfix'></div>";
			}

			if ($counter > 5)
				$ms .= "</div>";

			if ($counter == 0)
				$ms .= "$LANG[BLOG_EMPTY]";
	return $ms;
}

//
function m_blog_com() {
	global $LANG;

	$s = mf_query("select * from forum_topics WHERE last_post_date != date AND pthread = 0 AND threadtype < 3 AND blog > 0 AND rating > -1 order by last_post_date desc limit 20");

	$counter = 0;
	$jumpline = "";
	$hiddenblog = "<div id='hiddenBlogsC' style='display: none;'>";
	$msc = "";
			while ($row=mysql_fetch_assoc($s)) {
				$counter++;
				if ($counter != 6) {
					$msc .= "$jumpline<span class='m_blog'>";
					$msc .= "<i>$row[last_post_user] $LANG[ON] " . date($LANG['DATEFORMAT'],$row['last_post_date'])." $LANG[AT] ".date($LANG['TIMEFORMAT'],$row['last_post_date'])." :</i><br/>";
					if ($row['blog'] == 2)
						$msc .= "<a href='index.php?shard=blog&amp;action=g_view&amp;ID=$row[ID]&amp;userID=$row[userID]'>$row[title]";
					else
						$msc .= "<a href='index.php?shard=blog&amp;action=g_view&amp;ID=$row[ID]'>$row[title]";
					$msc .= "</a></span>";
				}
				else {
					$msc .= $hiddenblog;
					$hiddenblog = "";
					$msc .= "$jumpline<span class='m_blog'>";
					$msc .= "<i>$row[last_post_user] $LANG[ON] " . date($LANG['DATEFORMAT'],$row['last_post_date'])." $LANG[AT] ".date($LANG['TIMEFORMAT'],$row['last_post_date'])." :</i><br/>";
					if ($row['blog'] == 2)
						$msc .= "<a href='index.php?shard=blog&amp;action=g_view&amp;ID=$row[ID]&amp;userID=$row[userID]'>$row[title]";
					else
						$msc .= "<a href='index.php?shard=blog&amp;action=g_view&amp;ID=$row[ID]'>$row[title]";
					$msc .= "</a></span>";
				}
				$jumpline = "<br/><br/><div class='clearfix'></div>";
			}

			if ($counter > 5)
				$msc .= "</div>";

			if ($counter == 0)
				$msc .= "Aucun blog";
	
	return $msc;
}

	$thisMenu->menuTitle = "<span id='widget$widgetID' style='display:none;'>". time() . "</span>
							<div class='title_handle' style='width:144px;'>$LANG[BLOGS]</div>";
	$thisMenu->menuTitle .= "<span style='cursor:pointer;' onclick=\"this.blur(); toggleLayer('hiddenBlogs'); toggleLayer('hiddenBlogsC');\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/shard_open.png' border='0' title='$LANG[SHARD_SIZE]' alt='+' /></span>";
	$thisMenu->menuTitle .= "<a onclick='this.blur();' href=\"javascript:closeShard('$widgetID');\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/shard_exit.png' border='0' title='$LANG[SHARD_CLOSE]' alt='X' /></a>";

	$checkStr = "thread";
	$msm = "<a onclick='this.blur();' href=\"javascript:untogglem_blog('listThread','listCom');\" title='$LANG[BLOG_THREADS_TITLE]' style='margin-left: 32px;' class='blogTypeSel' id='listThread'>$LANG[BLOG_THREADS]</a>";
	$msm .= "<a onclick='this.blur();' href=\"javascript:untogglem_blog('listCom','listThread');\" title='$LANG[BLOG_POSTS_TITLE]' style='margin-left: 8px;' class='blogType' id='listCom'>$LANG[BLOG_POSTS]</a>";
	$thisMenu->menuContentArray[] = $msm."<br/>";
	$thisMenu->menuContentArray[] = "<div id='listThreadl' style='display:block;'>".m_blog_thread()."</div>";
	$thisMenu->menuContentArray[] = "<div id='listComl' style='display:none;'>".m_blog_com()."</div>";
	$thisMenu->menuContentArray[] = "<div class='clearfix'></div>";
	$thisMenu->menuContentArray[] = "<div style='text-align:center;'>
		<a href='http://".$siteSettings['siteurl']."/xml/blog.xml'><img src='images/core/rss.png' alt='' /></a>&nbsp;

		</div>";
	$thisMenu->menuContentArray[] = "<div class='clearfix'></div>";
?>