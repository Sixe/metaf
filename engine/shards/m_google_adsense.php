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
//----------------------------------------------------
// m_google_adsense.php
//
// display Google Ads Along the Sidebar
//----------------------------------------------------



	$thisMenu->menuTitle = "<span id='widget$widgetID' style='display:none;'>". time() . "</span>
							<div class='title_handle' style='width:154px;'>$LANG[RELATED_LINKS]</div>";
	$thisMenu->menuTitle .= "<a onclick='this.blur();' href=\"javascript:closeShard('$widgetID');\"><img src='engine/grafts/".$siteSettings['graft']."/images/shard_exit.png' border='0' title='$LANG[SHARD_CLOSE]' alt='X' /></a>";
	$thisMenu->menuContentArray[] = "<center><script type=\"text/javascript\"><!--
google_ad_client = \"pub-1572380860248461\";
/* 160x600, widget */
google_ad_slot = \"7368201317\";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type=\"text/javascript\"
src=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\">
</script></center>";

?>