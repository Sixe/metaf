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
    //-----------------------------------------------------------------------------------------------
    //m_faq_edit.php
    //----------------------------------------------------------------------------------------------


sajax_init();

include("ajax_commonlib.php");

sajax_handle_client_request();

function createFaqList($posneg) {
	global $LANG;
	global $siteSettings;
	$slide = "";
	$cs = mf_query("select * from faq_shard order by ID");
	while ($row=mysql_fetch_assoc($cs)) {
		$slide .= "<div class='row'><div class='cell'>$row[title]</div>";
		$slide .= "<div class='cell'>$row[threadID]</div>";
		$visible = "";
		if ($row['visible'])
			$visible = "X";
		$slide .= "<div class='cell bold' style='text-align:center;'>$visible</div>";
		$slide .= "<div class='cell'>";
		$slide .= "<a href='index.php?shard=m_faq_edit&amp;action=g_editFaq&amp;editID=".$row['ID']."'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/edit.gif' border='0' align='top' alt='$LANG[EDIT]' /></a> &nbsp;";
		$slide .= " <a href='index.php?shard=m_faq_edit&amp;action=deleteFaq&amp;ID=".$row['ID']."'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/b_drop.png' border='0' align='top' alt='$LANG[DELETE]' /></a>";
		$slide .= "</div></div>";
	}
	return $slide;
}



global $siteSettings;

switch ($action):

case "g_default":

if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level6')) {
	$co = New contentObj;
	$co->title = "$LANG[M_FAQ_TITLE2]";
	$co->contentType="generic";
	$co->primaryContent .= "
			<form action='index.php?shard=m_faq_edit&amp;action=addNewFaq' method='post'>
				<div style='display:table;'>
					<div class='row'>
						<div class='cell bold' style='padding:2px;'>$LANG[M_FAQ_TITLE_FAQ]</div>
						<div class='cell bold' style='padding:2px;'>$LANG[M_FAQ_THREAD]</div>
						<div class='cell bold' style='padding:2px;'>$LANG[M_FAQ_VISIBLE]</div>
					</div>
					<div class='row'>
						<div class='cell' style='padding:2px;'><input type='text' name='title' size='50' /></div>
						<div class='cell' style='padding:2px;'><input type='text' name='threadID' size='6' /></div>
						<div class='cell' style='padding:2px;'></div>
						<div class='cell' style='padding:2px;'><input type='submit' class='button' value='$LANG[ADD]' /></div>
					</div>
					<div style='height:16px;'></div>";

	$co->primaryContent .= createFaqList(0);
	$co->primaryContent .= "</div></form>";
	$shardContentArray[] = $co;
} 
break;

/// add Faq to the database

case "addNewFaq":

if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	$title = make_var_safe( $_POST['title']);
	$threadID = make_num_safe( $_POST['threadID']);
	
	mf_query("insert into faq_shard (title, threadID) VALUES (\"$title\", '$threadID')");

	$sub = mf_query("select * from faq_shard where title='$title'");
	$sub = mysql_fetch_assoc($sub);

	header("Location: index.php?shard=m_faq_edit&action=g_editFaq&editID=$sub[ID]");
}
break;

// Delete

case "deleteFaq":
if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	if( is_numeric( $_REQUEST['ID']))
		$del = mf_query("delete from faq_shard where ID=$_REQUEST[ID]");
	header("Location: index.php?shard=m_faq_edit&action=g_default");
}
break;

// Edition

case "g_editFaq":

if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	if( is_numeric( $_REQUEST['editID'])) {
		$editid = $_REQUEST['editID'];

		$sub = mf_query("select * from faq_shard where ID='$editid'");
		$sub = mysql_fetch_assoc($sub);

		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->title = "$LANG[M_FAQ_EDIT]";
		$thisContentObj->primaryContent = "<br/><br/>";

		$thisContentObj->primaryContent .= "<form action='index.php?shard=m_faq_edit&amp;action=submitEditFaq&amp;editID=$editid' method='post'>";
		$thisContentObj->primaryContent .= "<table><tr><td>$LANG[M_FAQ_TITLE_FAQ]</td>";
		$thisContentObj->primaryContent .= "<td><input type='text' name='title' size='40' value=\"$sub[title]\" /></td></tr>";
		$thisContentObj->primaryContent .= "<tr><td>$LANG[M_FAQ_THREAD] </td>";
		$thisContentObj->primaryContent .= "<td><input type='text' name='threadID' size='6' value='$sub[threadID]' /></td></tr>";
		$checked = "";
		if ($sub['visible'])
			$checked = "checked='checked'";
		$thisContentObj->primaryContent .= "<tr><td>$LANG[WIDGET_VISIBLE] </td>";
		$thisContentObj->primaryContent .= "<td><input $checked type='checkbox' name='visible' /></td></tr>";

		$thisContentObj->primaryContent .= "<tr><td></td><td align='right'><input type='submit' class='button' value='$LANG[SUBMIT_EDIT]' /></td></tr>";
		$thisContentObj->primaryContent .= "</table></form>";

		$shardContentArray[] = $thisContentObj;
	}
}
break;

case "submitEditFaq":

if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	$visible = "";
	if (isset($_POST['visible']))
		$visible = "1";
	if( is_numeric($_REQUEST['editID'])) {
		mf_query("update faq_shard set title=\"".make_var_safe($_POST['title'])."\", threadID='".make_num_safe($_POST['threadID'])."', visible='$visible' where ID='".make_num_safe($_REQUEST['editID'])."'");
	}
	header("Location: index.php?shard=m_faq_edit&action=g_default");
}
break;

endswitch; 

?>