<?php
/*
	Copyright 2009 Golgi
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

    // m_whoOnline.php

	function checkwhoonline() {
		global $CURRENTUSER;

		if ($CURRENTUSER != "anonymous") {

			$msdate = filemtime('cache/who_online.cache');
			$diff = time() - $msdate;
			if ($diff > 60) {
				$ms = whoonline();
			}
			else
				$ms = file_get_contents ('cache/who_online.cache');
			
			return $ms . "@@::WHO!ONLINE::@@";
		}
	}


	function whoonline() {
		global $CURRENTUSER;
		global $LANG;

		if ($CURRENTUSER != "anonymous") {

			$tenMinutes = time() - 600;
    
			$s = mf_query("select username, ID, lat, hidemyself from users where lat > '$tenMinutes' and ID != '1' order by lat desc");
	$counter = 0;	
			$hiddenusers = 0;
			$hidediv = "<div id='hiddenUsers' style='display: none;'>";
	$ms = "";
			while ($row=mysql_fetch_assoc($s)) {
				if ($counter == 20) {
					$ms .= $hidediv;
					$hidediv = "";
				}
				if (!$row['hidemyself']) {
		$usernameFull = $row['username'];
					if (strlen(utf8_decode($row['username'])) >= 16)
						$row['username'] = substr($row['username'], 0, 13) . "...";

					$ms .= "<div style='float: left; text-align: left;font-size:10px;'>
							<a href='index.php?shard=forum&amp;action=g_ep&amp;ID=$row[ID]' title='$usernameFull'>
							<span onclick='javascript:userprofile(\"$usernameFull\"); return false;'>
							$row[username]</span></a></div> 
							<div style='float: right; margin-right:3px;text-align:right;font-size:10px;'><small>".date("i:s", (time() - $row['lat']))."</small></div>";
		$ms .= "<div class='clearfix'></div>";
					$counter++;
				}
				else
					$hiddenusers++;
	}

			$s = mf_query("select count(ID) as counter from anonymous where lat > '$tenMinutes' order by lat desc");
			$s = mysql_fetch_assoc($s);
	$anonymous = $s['counter'];

	if ($counter == 0)
		$ms .= "0 $LANG[NO_USERS]";    
	if ($counter > 19)
				$ms .= "</div><div style='margin-top:6px;margin-bottom:4px;text-align:center;'><span style='text-align:center;' class='button_mini'><a href=\"javascript:toggleLayer('hiddenUsers');\">$LANG[SEE_ALL]</a></span></div>";
			if ($anonymous) {
				$ms .= "<div style='font-size:10px;'>";
	if ($anonymous == 1)
					$ms .= "... $LANG[AND2] 1 $LANG[GUEST]";
	else if ($anonymous > 1)
					$ms .= "... $LANG[AND2] $anonymous $LANG[GUESTS]";
				$ms .= "</div>";
			}
			if ($hiddenusers) {
				$ms .= "<div style='font-size:10px;'>";
				if ($hiddenusers == 1)
					$ms .= "... $LANG[AND2] 1 $LANG[HIDDEN_USER]";
				else if ($hiddenusers > 1)
					$ms .= "... $LANG[AND2] $hiddenusers $LANG[HIDDEN_USERS]";
				$ms .= "</div>";
			}
			$totalusers = $counter + $anonymous + $hiddenusers;
			$ms .= "@@::WHO!ONLINE::@@" . $totalusers;

			$fp = fopen("cache/who_online.cache", 'w+');
			fputs($fp, $ms);
			fclose($fp);

			return $ms;
		}
	}

if ($CURRENTUSER != "anonymous") {

	$thisMenu->menuTitle = "<span id='widget$widgetID' style='display:none;' class='". time() . "'></span>
							<script type=\"text/javascript\">setTimeout(\"reloadwhooneline()\",3000)</script>
							<div class='title_handle' style='width:154px;'>$LANG[USERS_ONLINE]: <span id='whooneline_title'></span></div>";

//	$thisMenu->menuTitle .= "";
	$thisMenu->menuTitle .= "<a onclick=\"this.blur();\" href=\"javascript:closeShard('$widgetID');\">
							<img src='engine/grafts/".$siteSettings['graft']."/images/shard_exit.png' border='0' title='$LANG[SHARD_CLOSE]' alt='X' />
							</a>";

	$thisMenu->menuContentArray[] = "<div id='whooneline_content'></div>";
 }       
?>