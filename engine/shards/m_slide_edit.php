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
    //m_slide_edit.php
    //----------------------------------------------------------------------------------------------
sajax_init();

include("ajax_commonlib.php");

sajax_handle_client_request();


function createSlideList($posneg) {
	global $LANG;
	global $siteSettings;
	$slide = "";
	$cs = mf_query("select * from slide_shard order by ID");
	while ($row=mysql_fetch_array($cs)) {
		$slide .= "<tr><td>$row[title]</td>";
		$slide .= "<td>$row[type]</td>";
		$slide .= "<td><img src='$row[img]' width='24' height='36' border='0' style='vertical-align:middle;' alt='picture' /> $row[img]</td>";
		$visible = "";
		if ($row['visible'])
			$visible = "<center><b>X</b></center>";
		$slide .= "<td>$visible</td>";
		$slide .= "<td>";
		$slide .= "<a href='index.php?shard=m_slide_edit&amp;action=g_editSlide&amp;editID=".$row[ID]."'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/edit.gif' border='0' align='top' alt='$LANG[EDIT]' /></a> &nbsp;";
		$slide .= " <a href='index.php?shard=m_slide_edit&amp;action=deleteSlide&amp;ID=".$row[ID]."'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/b_drop.png' border='0' align='top' alt='$LANG[DELETE]' /></a>";
		$slide .= "</td></tr>";
	}

	return $slide;
}



global $siteSettings;

switch ($action):

case "g_default":


if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level6')) {
	$co = New contentObj;
	$co->title = "$LANG[M_SLIDE_TITLE2]";
	$co->contentType="generic";
	$co->primaryContent .= "<form action='index.php?shard=m_slide_edit&amp;action=addNewSlide' method='post'>
					<table>
					<tr>
					<td><b>$LANG[M_SLIDE_TITLE_SLIDE]</b></td>
					<td><b>$LANG[M_SLIDE_TYPE]</b></td>
					<td><b>$LANG[M_SLIDE_IMG] ( $LANG[M_SLIDE_SIZE] 161x240 )</b></td>
					<td><b>Visible</b></td></tr>
					<tr><td><input type='text' name='title' size='20' /></td>
					<td><input type='text' name='type' size='20' /></td>
					<td><input type='text' name='img' size='46' /></td>
					<td></td>
					<td><input type='submit' value='$LANG[SUBMIT]' /></td></tr>";

	$co->primaryContent .= createSlideList(0);
	$co->primaryContent .= "</table></form>";

	$shardContentArray[] = $co;

	} 
break;

/// add Slide to the database

case "addNewSlide":

if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	$title = make_var_safe( $_REQUEST['title']);
	$type = make_var_safe( $_REQUEST['type']);
	$img = make_var_safe( $_REQUEST['img']);

	$is = "insert into slide_shard (title, type, img) VALUES ('$title', '$type', '$img')";
	$insertSub = mf_query($is);

	$sub = mf_query("select * from slide_shard where title='$title'");
	$sub = mysql_fetch_array($sub);

	header("Location: index.php?shard=m_slide_edit&action=g_editSlide&editID=$sub[ID]");
}
break;

// Delete

case "deleteSlide":
if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	if( is_numeric( $_REQUEST['ID']))
		$del = mf_query("delete from slide_shard where ID=$_REQUEST[ID]");
	header("Location: index.php?shard=m_slide_edit&action=g_default");
}
break;

// Edition

case "g_editSlide":

if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	if( is_numeric( $_REQUEST['editID'])) {
		$editid = $_REQUEST['editID'];

		$sub = mf_query("select * from slide_shard where ID=$editid");
		$sub = mysql_fetch_array($sub);

		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->title = "$LANG[M_SLIDE_EDIT]";
		$thisContentObj->primaryContent = "<br/><br/>";

		$thisContentObj->primaryContent .= "<form action='index.php?shard=m_slide_edit&amp;action=submitEditSlide&amp;editID=$editid' method='post'>";
		$thisContentObj->primaryContent .= "<table><tr><td>$LANG[M_SLIDE_TITLE_SLIDE]</td>";
		$thisContentObj->primaryContent .= "<td><input type='text' name='title' size='40' value='$sub[title]' /></td></tr>";
		$thisContentObj->primaryContent .= "<tr><td>$LANG[M_SLIDE_TYPE] </td>";
		$thisContentObj->primaryContent .= "<td><input type='text' name='type' size='40' value='$sub[type]' /></td></tr>";
		$thisContentObj->primaryContent .= "<tr><td>$LANG[M_SLIDE_IMG] </td>";
		$thisContentObj->primaryContent .= "<td><input type='text' name='img' size='88' value='$sub[img]' /><small>&nbsp;( $LANG[M_SLIDE_SIZE] 161x240 )</small></td></tr>";
		$thisContentObj->primaryContent .= "<tr><td>$LANG[M_SLIDE_LINK] </td>";
		$thisContentObj->primaryContent .= "<td><input type='text' name='link' size='88' value='$sub[link]' /></td></tr>";
		$checked = "";
		if ($sub['visible'])
			$checked = "checked='checked'";
		$thisContentObj->primaryContent .= "<tr><td>Visible ? </td>";
		$thisContentObj->primaryContent .= "<td><input $checked type='checkbox' name='visible' /></td></tr>";

		$thisContentObj->primaryContent .= "<tr><td></td><td align='right'><input type='submit' value='$LANG[SUBMIT]' /></td></tr>";
		$thisContentObj->primaryContent .= "</table></form>";

		$shardContentArray[] = $thisContentObj;
	}
}
break;

case "submitEditSlide":

if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	if ($_REQUEST['visible'])
		$visible = "1";
	if( is_numeric($_REQUEST['editID'])) {
		$link = make_var_safe($_REQUEST['link']);
		$link = htmlspecialchars($link);
		$edit = mf_query("update slide_shard set title='$_REQUEST[title]', type='$_REQUEST[type]', img='$_REQUEST[img]', link='$link', visible='$visible' where ID=$_REQUEST[editID]");
	}
	header("Location: index.php?shard=m_slide_edit&action=g_default");
}
break;

endswitch; 

?>