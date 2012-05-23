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

	// m_faq.php

    $thisMenu->menuTitle = "<span id='widget$widgetID' style='display:none;'>". time() . "</span>
							<div class='title_handle' style='width:154px;'>$LANG[M_FAQ_TITLE]</div>";
	if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0'))
		$thisMenu->menuTitle .= "<div style='position:absolute;margin-left:112px;'><a href='index.php?shard=m_faq_edit'>($LANG[SHARD_EDIT])</a></div>";
//	$thisMenu->menuTitle .= "<a onclick='this.blur();' href=\"javascript:toggleLayer('faq_hidden');\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/shard_open.png' border='0' title='Réduire/Agrandir' alt='+' /></a>";
	$thisMenu->menuTitle .= "<a onclick='this.blur();' href=\"javascript:closeShard('$widgetID');\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/shard_exit.png' title='Fermer' alt='X' /></a>";

	$jt = "";
    if ($_REQUEST['shard'] == "forum" && $CURRENTUSERAJAX) {
		if (!array_key_exists('action', $_REQUEST))
			$jt = "</span>";
		else if ($_REQUEST['action'] == "g_default")
			$jt = "</span>";
	}
	
   	$hidden = "";
	$cs = mf_query("select * from faq_shard where visible != '1' order by ID");
	while ($row=mysql_fetch_assoc($cs)) {
		$hidden .= "<div><a href='".make_link("forum","&amp;action=g_reply&amp;ID=$row[threadID]")."'>";
		if ($jt)
			$hidden .= "<span onclick=\"emptymainThread('$row[threadID]','','',''); return false;\" style='cursor:pointer;'>";
		$hidden .= "$row[title]$jt</a></div>";
	}

   	$visible = "<div style='font-size:0.8em;'>";
	$visible .= "<div style='float: left; text-align: left;'>";
	$cs = mf_query("select * from faq_shard where visible = '1' order by ID");
	while ($row=mysql_fetch_assoc($cs)) {
		$visible .= "<div><a href='".make_link("forum","&amp;action=g_reply&amp;ID=$row[threadID]")."'>";
		if ($jt)
			$visible .= "<span onclick=\"emptymainThread('$row[threadID]','','',''); return false;\" style='cursor:pointer;'>";
		$visible .= "$row[title]$jt</a></div>";
	}

	$visible .= "</div></div><div class='clearfix'></div>";

	$thisMenu->menuContentArray[] = $visible;
	if ($hidden) {
	$thisMenu->menuContentArray[] = "<div id='faq_hidden_off' style='display:block;float:right;cursor:pointer;' onclick=\"toggleLayer('faq_hidden_off'); toggleLayer('faq_hidden_on'); toggleLayer('faq_hidden');\">(+)<img src='engine/grafts/" . $siteSettings['graft'] . "/images/menudown.gif' alt='' title=\"\" /></div><div id='faq_hidden_on' style='display:none;float:right;cursor:pointer;' onclick=\"toggleLayer('faq_hidden_off'); toggleLayer('faq_hidden_on'); toggleLayer('faq_hidden');\">(-)<img src='engine/grafts/" . $siteSettings['graft'] . "/images/menuup.gif' alt='' title=\"\"/>";
		$thisMenu->menuContentArray[] = "<div id='faq_hidden' style='display: none;font-size:0.8em;padding-top:10px;'><div style='float: left; text-align: left;'>";
	$thisMenu->menuContentArray[] = $hidden;
		$thisMenu->menuContentArray[] = "</div></div><div class='clearfix'></div></div>";
	}
?>