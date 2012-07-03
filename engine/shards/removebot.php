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
    // adduser.php


// Suppression d'un compte utilisateur

		$user_posted = mf_query("select ID from users WHERE location LIKE \"%The five Street New%\" OR location LIKE \"%Cherry Street%\" ");
		while ($row = mysql_fetch_array($user_posted)) {
			$ID = $row['ID'];
	    	mf_query("delete from fhits where userID = '$ID'");
			mf_query("delete from blog where userID = '$ID'");
			mf_query("delete from albums_users where userID = '$ID'");
			mf_query("delete from albums_users where albumID IN (select ID from albums where userID = '$ID')");
			mf_query("delete from albums_topics where albumID IN (select ID from albums where userID = '$ID')");
			mf_query("delete from albums where userID = '$ID'");
			$query_image = mf_query("SELECT name, name_thumb FROM pictures WHERE userID='$ID'");
			while ($image = mysql_fetch_array($query_image)) {
				unlink($image['name']);
				unlink($image['name_thumb']);
			}
			mf_query("delete from pictures where userID = '$ID'");

			mf_query("delete from users where ID = '$ID' limit 1");
			mf_query("delete from forum_user_nri where userID = '$ID' limit 1");
		}

?>