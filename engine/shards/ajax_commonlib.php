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

function ajax_loadavg()	{
	global $CURRENTUSER;
	global $verifyEditDelete;

	if ($verifyEditDelete || isInGroup($CURRENTUSER, 'sysadmin'))	{
		$loadavg = trim(file_get_contents('/proc/loadavg'));
		$loads = explode(" ",$loadavg);
		$load = trim($loads[0]);
		$tenMinutes = time() - 300;
		$s = mf_query("select count(ID) as counter from users where lat > " . (time() - 300) . " ");
		$row=mysql_fetch_assoc($s);
		$counter = $row['counter'];
		$loadavg = "<span onclick=\"loadavg();\">U $counter / Load $load</span>";

		return $loadavg;
	}
}

function ajax_userprofile($data) {
	$dataline = explode("::@@user@@::", $data);
	$user = urldecode($dataline[0]);

	if ($dataline[1]) {
		$userID = make_num_safe($dataline[1]);
		$user = mf_query("select username from users where ID='$userID' limit 1");
		$user = mysql_fetch_assoc($user);
		$user = $user['username'];
	}

	if ($user) {
		$user = make_var_safe($user);

			$idl = mf_query("select ID, username from users where LOWER(username)=\"".mb_strtolower($user,'UTF-8')."\" limit 1");
		$idl = mysql_fetch_assoc($idl);
		$userID = $idl['ID'];
		if ($userID == "")
			$userID = "1";

		$returnStr2 = "<div id='contentU'>";
		$returnStr2 .= userprofile($userID);
		$returnStr2 .= "</div>";
	}

	return $returnStr2 . "::cur@lo::$userID::cur@lo::$idl[username]";
}

function ajax_inputUser($data) {
	$dataline = explode("::@@iu@@::", $data);
		$user = $dataline[0];
	$type = $dataline[1];
	global $CURRENTUSER;
	global $CURRENTUSERID;

	if ($CURRENTUSER == "anonymous")
		exit();

	$add = "create";
	
		$user = urldecode($user);
		$user = utf8_encode($user);
		$user = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $user);
		$user = html_entity_decode($user, ENT_NOQUOTES, 'UTF-8');
		$user = str_replace("::@plus@::","+",$user);
		$user = str_replace("::@euro@::","€",$user);
		$user = make_var_safe($user);

	$user_list = "";
	$userID_list = "";
	$i = 0;

	$deb = "";
	if (strlen($user) > 1)
		$deb = "%";
		$query = mf_query("SELECT ID, username, avatar FROM users WHERE userstatus IS NULL AND LOWER(username) LIKE \"".$deb.mb_strtolower($user,'UTF-8')."%\" ORDER BY rating DESC, username LIMIT 64");
	while ($row = mysql_fetch_assoc($query)) {
		$user_name = preg_replace("/$user/i", "<span style='font-size:1.3em;'>$user</span>", $row['username']);
		$user_list .= "<div onclick=\"inputselect_user('$row[ID]','$type');\" class='user_list_line'><div class='user_list_element'>$user_name</div>";
		if ($row['avatar'])
			$user_list .= "<img src='$row[avatar]' alt='' class='user_list_avatar'/>";
		$user_list .= "<span id='selectuser_".$type."_".$row['ID']."' style='display:none;'>$row[username]</span></div>";

		if ($row['username'] == $user)
			$add = "add";
		$i ++;
	}
	if ($i == 1 && $add == "add")
		$user_list = "";
	else {
		$i ++;
		if ($i > 5)
			$i = 5;
		$user_list .= "@@:t:@@".$i;
	}

	$retstr = $add."@@:t:@@".$user_list."@@:t:@@".$type;

	return $retstr;

}

function ajax_m_blog_thread() {
	$returnStr = m_blog_thread();
	return $returnStr;
}

function ajax_m_blog_com() {
	$returnStr = m_blog_com();
	return $returnStr;
}

function ajax_verify_version($version) {

	$version = make_var_safe($version);
	$retstr = checkversion($version);

	return $retstr;
}

function ajax_verify_message() {

	$retstr = checkmessage();

	return $retstr;
}

function ajax_reloadwhooneline() {

	$retstr = checkwhoonline();

	return $retstr;
}

function ajax_right_picture($data) {
	$retstr = load_picture($data);
	return $retstr;
}

function ajax_left_picture($data) {
	$retstr = load_picture($data,"DESC");
	return $retstr;
}

function ajax_load_tags() {
	$retstr = load_tags();
	return $retstr;
}

function ajax_inputTag($tag) {
	global $CURRENTUSER;
	global $CURRENTUSERID;

	if ($CURRENTUSER == "anonymous")
		exit();

	$add = "create";
	
	$tag = utf8_encode($tag);
	$tag = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $tag);
	$tag = html_entity_decode($tag, ENT_NOQUOTES, 'UTF-8');
	$tag = mb_strtolower(trim($tag),'UTF-8');
	$tag = str_replace("::@plus@::","+",$tag);
	$tag = str_replace("::@euro@::","€",$tag);
	$tag = make_var_safe(htmlspecialchars($tag));
	$tag_list = "";
	$i = 0;

	$deb = "";
	if (strlen($tag) > 1)
		$deb = "%";
	$query = mf_query("SELECT ID, tag FROM tags WHERE tag LIKE \"".$deb."$tag%\" ORDER BY tag LIMIT 16");
	while ($row = mysql_fetch_assoc($query)) {
		$tag_list .= "<option value=\"$row[tag]\">$row[tag]</option>";
		if ($row['tag'] == $tag)
			$add = "add";
		$i ++;
	}
	if ($i == 1 && $add == "add")
		$tag_list = "";
	else {
		$i ++;
		if ($i > 5)
			$i = 5;
		$tag_list .= "@@:t:@@".$i;
	}
	
	
	$verify_exist = "";
	if (isset($_REQUEST['ID'])) {
		$verify_exist = mf_query("SELECT ID FROM forum_tags WHERE tag = \"$tag\" AND threadID = '".make_num_safe($_REQUEST['ID'])."' LIMIT 1");
		if ($verify_exist = mysql_fetch_assoc($verify_exist))
			$add = "exist";
	}

	$retstr = $add."@@:t:@@".$tag_list;

	return $retstr;
}

function ajax_addTag($tag) {
	global $CURRENTUSER;
	global $CURRENTUSERID;

	if ($CURRENTUSER == "anonymous")
		exit();

	$tag = utf8_encode($tag);
	$tag = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $tag);
	$tag = html_entity_decode($tag, ENT_NOQUOTES, 'UTF-8');
	$tag = mb_strtolower(trim($tag), 'UTF-8');
	$tag = str_replace("::@plus@::","+",$tag);
	$tag = str_replace("::@euro@::","€",$tag);
	$tag = make_var_safe(htmlspecialchars($tag));

		mf_query("INSERT IGNORE INTO tags (tag,userID) VALUES (\"$tag\", '$CURRENTUSERID')");
	$tagID = mf_query("SELECT ID FROM tags WHERE tag = \"$tag\" LIMIT 1");
	$tagID = mysql_fetch_assoc($tagID);

	return $tagID['ID'];
}

function ajax_displayAlbums($data) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $LANG;
	
	$dataline = explode("::ID@ID::", $data);
	$postID = $dataline[0];
		if ($dataline[0])
			$postID = make_num_safe($dataline[0]);
	$threadID = make_num_safe($dataline[1]);

	if ($CURRENTUSER == "anonymous")
		exit();

	$albums = "<div style='margin-top:8px;float:right;' class='button' onclick=\"closeDiv('info_album$postID','num_album$postID');\">$LANG[CANCEL]</div>
			<input type='hidden' value='' id='num_album$postID'/>
			<div style='clear:both;margin-bottom:8px;'></div>
			<div style='max-height:600px;overflow:auto;'>";

	$query = mf_query("SELECT albums.ID, albums.public, albums_topics.threadID FROM albums 
					LEFT JOIN albums_topics ON (albums_topics.albumID = albums.ID AND albums_topics.threadID = '$threadID') 
					WHERE albums.userID = '$CURRENTUSERID' ORDER BY albums.ID DESC LIMIT 10");
	while ($row = mysql_fetch_assoc($query)) {
		if ($row['public'] || $row['threadID'])
			$onclick = "onclick=\"document.getElementById('num_album$postID').value=$row[ID];pushBt('album','$postID');\">$LANG[PICTURES_ALBUM_SELECT]";
		else
			$onclick = "onclick=\"share_album('$row[ID]','$threadID');document.getElementById('num_album$postID').value=$row[ID];pushBt('album','$postID');\">$LANG[PICTURES_ALBUM_SELECT_SHARE]";
		$albums .= "<div style='margin-top:8px;float:right;margin-right:8px;' class='button' $onclick</div>".load_album($row['ID']);
	}
	return $postID."::albs::".$albums."</div>";
}

function ajax_displayPicts($postID) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $LANG;

	if ($CURRENTUSER == "anonymous")
		exit();

		if ($postID)
			$postID = make_num_safe($postID);

	$picts = "<div style='float:left;margin-top:8px;'>
				<span class=\"button\" onclick=\"document.getElementById('up_pict$postID').style.display='inline-block';window.open('".make_link("profile","&action=g_pictures&filter=6")."');\">$LANG[PICTURES_UPLOAD_BUTTON]</span> &nbsp; <span class='button' style='display:none;' id='up_pict$postID' onclick=\"document.getElementById('up_pict$postID').style.display='none';x_ajax_displayPicts('$postID', displayPicts);\">$LANG[PICTURES_UPLOAD_FIN]</span>
			</div>
			<div style='margin-top:8px;float:right;' class='button' onclick=\"closeDiv('info_pict$postID','num_pict$postID');\">$LANG[CANCEL]</div>
			<input type='hidden' value='' id='num_pict$postID'/>
			<div style='clear:both;margin-bottom:8px;'></div>
			<div style='max-height:600px;overflow:auto;'>";

	$query = mf_query("SELECT ID FROM pictures WHERE userID = '$CURRENTUSERID' AND albumID != '0' ORDER BY ID DESC LIMIT 30");
	while ($row = mysql_fetch_assoc($query)) {
		$picts .= "<div style='display:inline-block;margin:8px;' onclick=\"verify_pict('$postID','$row[ID]');\">".load_pict($row['ID'],'',true)."</div>";
	}
	return $postID."::pcts::".$picts."</div>";
}

function ajax_share_album($data) {
	global $CURRENTUSERID;

	$dataline = explode("::IDs@sID::", $data);
	$albumID = make_num_safe($dataline[0]);
	$threadID = make_num_safe($dataline[1]);

	$verify = mf_query("SELECT ID FROM albums WHERE ID = '$albumID' AND userID = '$CURRENTUSERID' LIMIT 1");
	if ($verify = mysql_fetch_assoc($verify))
		mf_query("INSERT INTO albums_topics (albumID, threadID) VALUES ('$albumID', '$threadID')");
	
	return "true";
}

function ajax_verify_pict($data) {
	global $CURRENTUSERID;
	global $LANG;

	$dataline = explode("::IDp@pID::", $data);
	$postID = $dataline[0];
		if ($postID)
			$postID = make_num_safe($postID);
	$pictID = make_num_safe($dataline[1]);
	$threadID = make_num_safe($dataline[2]);

	$verify = mf_query("SELECT pictures.albumID, albums.public, albums_topics.threadID FROM pictures 
					JOIN albums ON (albums.ID = pictures.albumID AND albums.userID = '$CURRENTUSERID') 
					LEFT JOIN albums_topics ON (albums_topics.albumID = pictures.albumID AND albums_topics.threadID = '$threadID') 
					WHERE pictures.ID = '$pictID'");
	$row = mysql_fetch_assoc($verify);
	if ($row['public'] || $row['threadID'])
		return $postID."::pcts::".$pictID;
	else if ($row['albumID']) {
		$pict = "<div style='margin-top:8px;'>
				<div style='display:table;'>
					<div class='row'>
						<div class='cell'>".load_pict($pictID,'',true)." </div>
						<div class='cell' style='vertical-align:middle;'>$LANG[PICTURES_NOT_SHARED]
							<div style='margin-top:8px;'>
								<span class='button' onclick=\"share_album('$row[albumID]','$threadID');document.getElementById('num_pict$postID').value=$pictID;pushBt('pict','$postID');\">$LANG[PICTURES_ALBUM_THREAD_SHARE]</span>
								<span class='button' onclick=\"closeDiv('info_pict$postID','num_pict$postID');\">$LANG[PICTURES_ALBUM_THREAD_SHARE_NOT]</span>
							</div>
						</div>
					</div>
				</div>
				<div style='max-height:600px;overflow:auto;'>
				".load_album($row['albumID'])."
				</div>
			</div>
			<div style='margin-top:8px;float:right;' class='button' onclick=\"closeDiv('info_pict$postID','num_pict$postID');\">$LANG[CANCEL]</div>
			<input type='hidden' value='' id='num_pict$postID'/>
			<div style='clear:both;margin-bottom:8px;'></div>";

		return $postID."::pcts::".$pictID."::pcts::".$pict;
	}
}

function ajax_hide_user($data) {
	global $CURRENTUSERID;
	global $LANG;
	global $siteSettings;

	$dataline = explode("@@::hu::@@", $data);
	
	if (is_numeric($dataline[0]) && $CURRENTUSERID != $dataline[0]) {
		$retstr = "";
		if ($dataline[1] == "1") {
			mf_query("INSERT INTO users_friends (userID, target_userID, friendType) VALUES ('$CURRENTUSERID', '$dataline[0]', '1')");
			$retstr = "<span onclick=\"hide_user('$dataline[0]','2');\" class='button' title=\"\">$LANG[UNFRIEND_USER] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/unsubscribed.png' style='vertical-align:middle;' alt='' /></span>";
		}
		else if ($dataline[1] == "3" && $dataline[0] != "1" && !isInGroup($dataline[0], 'modo') && !isInGroup($dataline[0], 'admin')) {
			mf_query("INSERT INTO users_friends (userID, target_userID, friendType) VALUES ('$CURRENTUSERID', '$dataline[0]', '3')");
			$retstr = "<span onclick=\"hide_user('$dataline[0]','2');\" class='button' title=\"\">$LANG[UNBLOCK_USER] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/uparrowoff.gif' style='vertical-align:middle;' alt='' /></span>";
		}
		else {
			mf_query("DELETE FROM users_friends WHERE userID = '$CURRENTUSERID' AND target_userID = '$dataline[0]' LIMIT 1");
			$retstr = "<span onclick=\"hide_user('$dataline[0]','3');\" class='button' title=\"$LANG[BLOCK_USER_BUTTON]\">$LANG[BLOCK_USER] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/downarrowoff.gif' style='vertical-align:middle;' alt='' /></span>";
			$retstr .= "<span onclick=\"hide_user('$dataline[0]','1');\" class='button' title=\"$LANG[FRIEND_USER_BUTTON]\" style='margin-left:8px;'>$LANG[FRIEND_USER] <img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' style='vertical-align:middle;' alt='' /></span>";

		}
		return $retstr;
	}
}

sajax_export("ajax_loadavg","ajax_userprofile","ajax_m_blog_thread","ajax_m_blog_com","ajax_vote","ajax_affiche_rate","ajax_verify_version","ajax_verify_message","ajax_reloadwhooneline","ajax_right_picture","ajax_left_picture","ajax_load_tags","ajax_inputTag","ajax_addTag","ajax_inputUser","ajax_displayAlbums","ajax_displayPicts","ajax_share_album","ajax_verify_pict","ajax_hide_user");

?>