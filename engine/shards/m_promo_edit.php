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
    //m_promo_edit.php
    //----------------------------------------------------------------------------------------------

sajax_init();

include("ajax_commonlib.php");

sajax_handle_client_request();


function createPromoList($posneg) {
	global $LANG;
	global $siteSettings;
	$promo = "";
	$cs = mf_query("select * from promo_shard order by ID");
	while ($row=mysql_fetch_array($cs)) {
		$promo .= "<tr><td>$row[title]</td>";
		$promo .= "<td>$row[type]</td>";
		$promo .= "<td><img src='$row[img]' width='80' height='40' border='0' style='vertical-align:middle;' alt='picture' /> $row[img]</td>";
		
		$promo .= "<td>";
		$promo .= "<a href='index.php?shard=m_promo_edit&amp;action=g_editPromo&amp;editID=".$row['ID']."'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/edit.gif' border='0' align='top' alt='$LANG[EDIT]' /></a> &nbsp;";
		$promo .= " <a href='index.php?shard=m_promo_edit&amp;action=deletePromo&amp;ID=".$row['ID']."'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/b_drop.png' border='0' align='top' alt='$LANG[DELETE]' /></a>";
		$promo .= "</td></tr>";
	}
	return $promo;
}



switch ($action):

case "g_default":


if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	$co = New contentObj;
	$co->title = "$LANG[M_PROMO_TITLE2]";
	$co->contentType="generic";
	$co->primaryContent .= "<form action='index.php?shard=m_promo_edit&amp;action=addNewPromo' method='post'>
					<table>
					<tr>
					<td><b>$LANG[M_PROMO_TITLE]</b></td>
					<td><b>$LANG[M_PROMO_TYPE]</b></td>
					<td><b>$LANG[M_PROMO_IMG] ( $LANG[M_PROMO_SIZE] 180x80 )</b></td></tr>
					<tr><td><input type='text' name='title' size='20' /></td>
					<td><input type='text' name='type' size='24' /></td>
					<td><input type='text' name='img' size='48' /></td>
					<td><input type='submit' value='$LANG[SUBMIT]' /></td></tr>";

	$co->primaryContent .= createPromoList(0);
	$co->primaryContent .= "</table></form>";

	$shardContentArray[] = $co;

}
break;

/// add Promo to the database

case "addNewPromo":

if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	$title = make_var_safe( $_REQUEST['title']);
	$type = make_var_safe( $_REQUEST['type']);
	$img = make_var_safe( $_REQUEST['img']);
	$is = "insert into promo_shard (title, type, img) VALUES ('$title', '$type', '$img')";
	$insertSub = mf_query($is);

	$sub = mf_query("select * from promo_shard where title='$title'");
	$sub = mysql_fetch_array($sub);

	header("Location: index.php?shard=m_promo_edit&action=g_editPromo&editID=$sub[ID]");
}
break;

// Delete

case "deletePromo":
if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	if( is_numeric( $_REQUEST['ID']))
		$del = mf_query("delete from promo_shard where ID=$_REQUEST[ID]");
	header("Location: index.php?shard=m_promo_edit&action=g_default");
}
break;

// Edition

case "g_editPromo":

if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	if( is_numeric( $_REQUEST['editID'])) {
		$editid = $_REQUEST['editID'];

		$sub = mf_query("select * from promo_shard where ID=$editid");
		$sub = mysql_fetch_array($sub);

		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->title = "$LANG[M_PROMO_EDIT]";
		$thisContentObj->primaryContent = "<br/><br/>";

		$thisContentObj->primaryContent .= "<form action='index.php?shard=m_promo_edit&amp;action=submitEditPromo&amp;editID=$editid' method='post'>";
		$thisContentObj->primaryContent .= "<table><tr><td>$LANG[M_PROMO_TITLE_PROMO]</td>";
		$thisContentObj->primaryContent .= "<td><input type='text' name='title' size='40' value='$sub[title]' /></td></tr>";
		$thisContentObj->primaryContent .= "<tr><td>$LANG[M_PROMO_TYPE] </td>";
		$thisContentObj->primaryContent .= "<td><input type='text' name='type' size='40' value='$sub[type]' /></td></tr>";
		$thisContentObj->primaryContent .= "<tr><td>$LANG[M_PROMO_IMG] </td>";
		$thisContentObj->primaryContent .= "<td><input type='text' name='img' size='88' value='$sub[img]' /><small>&nbsp;( $LANG[M_PROMO_SIZE] 180x80 )</small></td></tr>";
		$thisContentObj->primaryContent .= "<tr><td>$LANG[M_PROMO_LINK] </td>";
		$thisContentObj->primaryContent .= "<td><input type='text' name='link' size='88' value='$sub[link]' /></td></tr>";

		$thisContentObj->primaryContent .= "<tr><td></td><td align='right'><input type='submit' value='$LANG[SUBMIT]' /></td></tr>";
		$thisContentObj->primaryContent .= "</table></form>";

		$shardContentArray[] = $thisContentObj;
		}
	}
break;

case "submitEditPromo":

if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0')) {
	if( is_numeric($_REQUEST['editID']))
		$edit = mf_query("update promo_shard set title='$_REQUEST[title]', type='$_REQUEST[type]', img='$_REQUEST[img]', link='$_REQUEST[link]' where ID=$_REQUEST[editID]");
	header("Location: index.php?shard=m_promo_edit&action=g_default");
}
break;

endswitch; 

?>