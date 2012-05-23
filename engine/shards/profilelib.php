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
// profilelib.php


function checkSelectedP($checkStr) {
	if (($_REQUEST['filter'] == $checkStr) || ($_REQUEST['filter'] == "" && $checkStr == 'all'))
		return "Sel";
	else
		return "";
}

function checkSelected2($checkStr) {
	if (($_REQUEST['filter2'] == $checkStr) || ($_REQUEST['filter2'] == "" && $checkStr == 'all'))
		return "Sel";
	else
		return "";
}

function checkSelected3($checkStr) {
	if ($_REQUEST['userf'] == $checkStr)
		return "Sel";
	else
		return "";
}
	
function add_to_album_list($albumID="") {
	
	global $CURRENTUSERID;
	global $LANG;

//	$albumID = make_num_safe($ID);

	$main = "<div style='margin-top:16px;'></div>";
	$main .= "<div class='subMenuParam'></div>";
	$main .= "<div style='text-align:center;font-size:2em;'>$LANG[PICTURES_NOT_IN_ALBUM]</div>
			<div style='text-align:center;font-size:1.0em;'>$LANG[PICTURES_NOT_IN_ALBUM_TEXT]</div>
				<div style='height:8px'></div>";
	$imagelist = "<form name='add_to_album' action='index.php?shard=profile&amp;action=submitAddtoalbum' method='POST'>";
	if ($albumID) {
		$imagelist .= "<input type='hidden' name='albumID' value='$albumID' />";
		$imagelist .= "<input type='hidden' name='notalbum' value='' />";
	}
	else
		$imagelist .= "<input type='hidden' name='notalbum' value='1' />";
	$i = 1;
	$query_image = mf_query("SELECT ID, name, name_thumb,description FROM pictures WHERE albumID = '' and userID='$CURRENTUSERID' ORDER by date_added DESC");
	while ($images = mysql_fetch_assoc($query_image)) {
		$imagelist .= "
			<div style='display:inline-block;border:1px solid silver;padding:1px;'>
				<div style='display:table;'>
					<div class='row'>
						<div class='cell' style='vertical-align:top;margin-left:6px;margin-right:-2px;'>
							<div>
								<input type='checkbox' class='bselect' name='check_$i'/>
								<input type='hidden' name='$i' value='$images[ID]' />
								<input type='hidden' name='name$i' value=\"$images[name]\" />
								<input type='hidden' name='namethumb$i' value=\"$images[name_thumb]\" />
							</div>
						</div>
						<div class='cell'>
							<img src='$images[name_thumb]' alt='$images[name]' title=\"$images[description]\" style='vertical-align:top;' />
						</div>
					</div>
				</div>
			</div>";
		$i ++;
	}
	$imagelist .= "<div style='text-align:center;margin-top:8px;'>";
	if (!$albumID) {
		$albumlist = "";
		$query_albums = mf_query("SELECT ID, name, description, coverID FROM albums WHERE userID = '$CURRENTUSERID' ORDER by date DESC");
		while ($album = mysql_fetch_assoc($query_albums)) {
			$albumlist .= "<option value='$album[ID]'>$album[name]</option>";
		}
		$imagelist .= "$LANG[PICTURES_NOT_IN_ALBUM_ADD] <select name='albumID' class='bselect'>$albumlist</select>";
	}
	else
		$imagelist .= "$LANG[PICTURES_ADD_SELECTION]";
	$imagelist .= "<div style='display:inline-block;margin-left:16px;' class='bold'>$LANG[PICTURES_ADD_SELECTION_OR]</div><div style='display:inline-block;margin-left:16px;'><input type='checkbox' name='deletesel' class='bselect checkbox'/> $LANG[PICTURES_DELETE_SELECTION]</div>";
	$imagelist .= "<div style='margin-top:8px;'><input type='submit' class='button_mini' value=\"$LANG[SUBMIT]\" /></div
				</div>
			</form>";
	if ($i == 1) {
		$imagelist = "";
		$main = "";
	}
	
	return $main.$imagelist;

}

function rulespictures() {

	global $siteSettings;

	$JSS2 = mf_query ("SELECT body from forum_topics WHERE ID = '$siteSettings[rulespictures_thread]' limit 1");
	$JSS2 = mysql_fetch_assoc($JSS2);
	$JSS2 = format_post($JSS2['body'], true);
	$JSS2 = str_replace("\r", "<br />", $JSS2);
	$JSS2 = str_replace("\n", "<br />", $JSS2);
	$JSS2 = str_replace("\t", "<br />", $JSS2);
	$JSS2 = str_replace("\"", "", $JSS2);

	return $JSS2;
}

function display_rulespicture() {

	global $LANG;

	$rules = "<div onclick=\"toggleLayer('rulespicture');\" style='text-align:center;cursor:pointer;font-size:0.8em;'>$LANG[RULES_DISPLAY_PICTURES]<div id='rulespicture' style='display:none;text-align:left;padding:16px;'>".rulespictures()."</div></div>";

	return $rules;
}
?>