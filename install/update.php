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
    //-----------------------------------------------------------------------
    // update.php
    //
    // Metafora Updater.
    //
    //-----------------------------------------------------------------------

	include("indexlib.php");

	$header = "<!DOCTYPE html
            PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
            \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	<html>
	<head>
		<title>Metafora Updater / Mise à jour de Metafora</title>
		<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\" />
	</head>
	<body>
		<div id=\"header2\">
			<div id=\"image_header\"></div>
		</div>
		<div id=\"coin_left\"></div>
		<div id=\"coin_right\"></div>
		<div id=\"bandeau_top\"></div>
		<div id=\"screenCover\"></div>
		<div id=\"page\">
		<div class=\"window\">
			<div style=\"display:table-row;\">
				<div class=\"border_left\"></div>
				<div class=\"content\">";

	$footer="</div>
				<div class=\"border_right\"></div>
				</div>
				<div style=\"display:table-row;\">
					<div class=\"border_cbl\"></div>
					<div class=\"border_bottom\"></div>
					<div class=\"border_cbr\"></div>
				</div>
			</div>
		</div>
	</body>
	</html>";

	// Get Metafora old version number from file (old method)
	if (file_exists("../mf-version.txt"))
		$mf_version = file_get_contents("../mf-version.txt");
	// Get Metafora new version number
	$metafora_version = file_get_contents("../metafora-version.txt");

	// Load db settings
	include("../engine/core/settings.php");	
    include("../engine/core/db.php");
    connectMysql($siteSettings);
 
	// Get Metafora old version number from db (new method)
	$old_version = mf_query("SELECT version FROM version WHERE site = \"metafora_version\" LIMIT 1");
    if ($old_version = mysql_fetch_assoc($old_version))
		$mf_version = $old_version['version'];
	
	// Load site settings
    $SSDB = mf_query("SELECT * FROM settings LIMIT 1");
    $SSDB = mysql_fetch_assoc($SSDB);

	// Load lang file
	include("lang/" . $SSDB['lang'] . ".php");

	// Go to resquested step
	if( isset($_REQUEST['update']))
		$step = $_REQUEST['update'];
	else
		$step = "intro";
	switch($step):

	// Welcome Page
	case "intro": {
		
		print($header);
		print("<div class='step'>".$LANG['STEP']." 1 - ".$LANG['INTRO_TITLE']."</div>
			<div style='padding-top:64px;padding-bottom:64px;'>
				<div style='font-size:1.6em;margin-bottom:16px;'>".$LANG['UPDATE_INTRO_TEXT1']."</div>
				<div style='font-size:1.2em;margin-bottom:16px;'>".$LANG['UPDATE_FROM_1']."<span style='font-weight:bold;'>".$mf_version."</span>".$LANG['UPDATE_FROM_2']."<span style='font-weight:bold;'>".$metafora_version."</span></div>
				<div style='font-size:1.2em;'>".$LANG['INTRO_TEXT3']."</div>
			</div>
			<div style='float:right;'><a href='update.php?update=upd' class='bigb button'>".$LANG['NEXT']."</a></div>");
		print($footer);
	}
	break;

	case "upd": {

		$old = false;
		$error_msg = "";
		print($header);
		print("<div class='step'>".$LANG['STEP']." 2 - ".$LANG['UPDATE_DB_TABLES']."</div>");
		print("<div style='padding-top:64px;padding-bottom:64px;font-size:1.4em;'>");
		if (strlen($mf_version) > 4 && substr($mf_version,0,4) == "1.00") {
			$result = load_sql("update_prior_to_1.00.sql");
			if($result !== TRUE)
				$error_msg .= $result."</br>";
			$old = true;
		}
		if ($old || $mf_version == "1.00") {
			$result = load_sql("update_1.00_to_1.10b1.sql");
			if($result !== TRUE)
				$error_msg .= $result."</br>";
			$old = true;
		}
		if ($old || $mf_version == "1.10b1") {
			$result = load_sql("update_1.10b1_to_1.10b2.sql");
			if($result !== TRUE)
				$error_msg .= $result."</br>";
			$old = true;
		}
		if ($old || $mf_version == "1.10b2") {
			$result = load_sql("update_1.10b2_to_1.10b3.sql");
			if($result !== TRUE)
				$error_msg .= $result."</br>";
			$old = true;
		}
		if ($old || $mf_version == "1.10b3") {
			$result = load_sql("update_1.10b3_to_1.10b4.sql");
			if($result !== TRUE)
				$error_msg .= $result."</br>";
			$old = true;
		}
		if ($old || $mf_version == "1.10b4" || $mf_version == "1.10b5") {
			$result = load_sql("update_1.00_to_1.10b6.sql");
			if($result !== TRUE)
				$error_msg .= $result."</br>";
			$old = true;
		}
		if ($old || $mf_version == "1.10b6" || $mf_version == "1.10") {
			$result = load_sql("update_1.10_to_1.20b1.sql");
			if($result !== TRUE)
				$error_msg .= $result."</br>";
			$old = true;
		}
		if (!$error_msg) {
			print($LANG['UPDATE_DB_SUCCESS']);
		}
		else {
			print("<div style='color:red;margin-bottom:8px;'>$LANG[UPDATE_DB_UNSUCCESS]</div>");
			print("<div style='max-height:120px;overflow:auto;border:1px solid red;padding:4px;font-size:0.7em;'>$error_msg</div>");
		}
		print("</div>");
		if ($error_msg)
			print("<div style='float:left;'><a href='update.php?update=intro' class='bigb button'>".$LANG['PREVIOUS']."</a></div>");
		print("<div style='float:right;'><a href='update.php?update=end' class='bigb button'>".$LANG['NEXT']."</a></div>");
		print($footer);
	}
	break;


	case "end": {

		mf_query("DELETE FROM version WHERE site = \"metafora_version\" LIMIT 1");
		mf_query("INSERT INTO version (site, version) VALUES (\"metafora_version\",\"$metafora_version\")");

		print($header);
		print("<div class='step'>".$LANG['END']." - ".$LANG['UPDATE_END_TITLE']."</div>");
		print("<div style='padding-top:64px;padding-bottom:64px;font-size:1.4em;'>");
		print("<div>".$LANG['UPDATE_END_TEXT1']."</div>");
		print("<div>".$LANG['END_TEXT3']."</div>");
		print("<div style='text-align:center;margin-top:12px;margin-bottom:8px;'><a href='../delinstall.php' class='button' style='font-size:1.2em;'>".$LANG['END_TEXT4']."</a></div>");
		print("<div style='color:red;font-weight:bold;'><img src='images/warning.png' alt=''/>".$LANG['END_TEXT5']."</div>");
		print($footer);
	}
	break;


	endswitch;

?>