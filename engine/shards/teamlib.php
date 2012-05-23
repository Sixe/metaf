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

// teamlib.php

function ajax_rename_file($dataline) {
	$retstr = rename_file($dataline);
	return $retstr;
}

function ajax_rename_folder($dataline) {
	$retstr = rename_folder($dataline);
	return $retstr;
}

function ajax_move_selection($dataLine) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $CURRENTSTATUS;
	global $LANG;
	global $siteSettings;

	$dataLine = explode("@@::ttt::@@", $dataLine);
	if ($CURRENTUSER != "anonymous" && is_numeric($dataLine[2])) {
		$teamID = $dataLine[2];
			
		$dataline = explode("@@::apply_sel::@@", $dataLine[0]);
		$folderID = "0";
		if ($dataLine[1])
			$folderID = make_num_safe($dataLine[1]);
		$excluded_folders[] = "";
		$i = 0;
		foreach ($dataline as $item) {
			if (substr($item,0,4) == "fold") {
				$lenght = strlen($item) - 4;
				$excluded_folders[$i] = substr($item,4,$lenght);
				$i ++;
			}
		}
		$opened_folders[] = "";
		$subfolderID = $folderID;
		$i = 0;
		while ($subfolderID != "0") {
			$query_folder = mf_query("SELECT subfolderID FROM teams_folders WHERE teamID = '$teamID' AND folderID = '$subfolderID' LIMIT 1");
			$query_folder = mysql_fetch_assoc($query_folder);
			if ($query_folder['subfolderID']) {
				$opened_folders[$i] = $query_folder['subfolderID'];
				$i ++;
			}
			$subfolderID = $query_folder['subfolderID'];
		}
		
		$isinteam = isInTeam($teamID,$CURRENTUSERID);
		if ($isinteam) {
			$listFolders = "<div style='font-weight:bold;margin-bottom:12px;'>";
			if ($dataLine[3] == "1")
				$listFolders .= "$LANG[FILE_MANAGER_MOVE_SELECTION]";
			else
				$listFolders .= "$LANG[FILE_MANAGER_COPY_SELECTION]";
			$listFolders .= ":</div><div id='apply_file_selection_folders'>";
			$query_teams = mf_query("SELECT teams.* FROM teams_users JOIN teams ON teams.teamID = teams_users.teamID WHERE teams_users.userID = '$CURRENTUSERID' ORDER BY teams.teamName");
			while ($row = mysql_fetch_assoc($query_teams)) {
				$open = false;
				$display1 = "inline";
				$display2 = "none";
				if ($teamID == $row['teamID']) {
					$open = true;
					$display1 = "none";
					$display2 = "inline";
				}
				$listFolders .= "
					<div id='listfoldTeam$row[teamID]' style='display:block;'>
						<div style='display:inline-block;width:12px;'>
							<img src='engine/grafts/" . $siteSettings['graft'] . "/images/menuright.gif' alt='' onclick=\"toggleLayer('listfold_0_".$row['teamID']."'); toggleLayer('imglistfold_on_0_".$row['teamID']."','inline'); toggleLayer('imglistfold_off_0_".$row['teamID']."','inline');\" id='imglistfold_off_0_".$row['teamID']."' style='display:$display1;vertical-align:sub;'/><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menudown.gif' alt='' onclick=\"toggleLayer('listfold_0_".$row['teamID']."'); toggleLayer('imglistfold_on_0_".$row['teamID']."','inline'); toggleLayer('imglistfold_off_0_".$row['teamID']."','inline');\" id='imglistfold_on_0_".$row['teamID']."' style='display:$display2;vertical-align:sub;'/>
						</div> ";
			if ($folderID == "0" &&  $row['teamID'] == $teamID)
				$listFolders .= "<span style='cursor:no-drop;'>";
			else {
				if ($dataLine[3] == "1")
					$listFolders .= "<span onclick=\"move_to_selection('0',$row[teamID]);\" class='link'>";
				else
					$listFolders .= "<span onclick=\"copy_to_selection('0',$row[teamID]);\" class='link'>";
			}
			$listFolders .= $row['teamName'];
			$listFolders .= "</span>";
			$listFolders .= "</div>";
				$listFolders .= list_subfolders("0",$row['teamID'],"0",$open,$excluded_folders,false,$opened_folders,$folderID,$dataLine[3]);
//				$listFolders .= "</div>";
			}
			$listFolders .= "
				</div><div style='margin-top:8px;float:right;'>
					<span class='button' onclick=\"closeDiv('apply_file_selection');\">$LANG[CANCEL]</span> &nbsp; 
					<span style='display:none;' id='selection_cache'>$dataLine[0]</span>
				</div>";
			return $listFolders;
		}
	}
}

function ajax_delete_selection($dataLine) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $CURRENTSTATUS;
	global $LANG;
	global $siteSettings;

	$dataLine = explode("@@::ttt::@@", $dataLine);
	if ($CURRENTUSER != "anonymous" && is_numeric($dataLine[2])) {
		$teamID = $dataLine[2];

		$dataline = explode("@@::apply_sel::@@", $dataLine[0]);
		$folderID = "0";
		if ($dataLine[1])
			$folderID = make_num_safe($dataLine[1]);

		$isinteam = isInTeam($teamID,$CURRENTUSERID);
		if ($isinteam) {
			foreach ($dataline as $item) {
				if (substr($item,0,4) == "fold") {
					$lenght = strlen($item) - 4;
					$deleteFolderID = substr($item,4,$lenght);
					mf_query("DELETE FROM teams_folders WHERE folderID = '$deleteFolderID' AND teamID = '$teamID' LIMIT 1");
					mf_query("DELETE FROM teams_files WHERE folderID = '$deleteFolderID' AND teamID = '$teamID'");
					$query_subfold = mf_query("SELECT folderID FROM teams_folders WHERE subfolderID = '$deleteFolderID' AND teamID = '$teamID'");
					while ($row_subfold = mysql_fetch_assoc($query_subfold)) {
						delete_subfold($row_subfold['folderID'],$teamID);
					}
				}
				else if (substr($item,0,4) == "file") {
					$lenght = strlen($item) - 4;
					$deleteFileID = substr($item,4,$lenght);
					mf_query("DELETE FROM teams_files WHERE folderID = '$folderID' AND teamID = '$teamID' AND fileID = '$deleteFileID' LIMIT 1");
				}
			}
/*			$query_files = mf_query("SELECT fileEncodedName FROM files WHERE fileID NOT IN (SELECT fileID FROM teams_files)");
			while ($row_files = mysql_fetch_assoc($query_files)) {
				if(file_exists("files/" . $row_files['fileEncodedName']))
					unlink("files/" . $row_files['fileEncodedName']);
			}
			mf_query("DELETE FROM files WHERE fileID NOT IN (SELECT fileID FROM teams_files)");
*/		}
	}
	return;
}

function ajax_move_selToFolder($dataLine) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $CURRENTSTATUS;
	global $LANG;
	global $siteSettings;

	$dataLine = explode("@@::ttt::@@", $dataLine);
	if ($CURRENTUSER != "anonymous" && is_numeric($dataLine[2])) {
		$teamID = $dataLine[2];
		$folderID = "0";
		if ($dataLine[1])
			$folderID = make_num_safe($dataLine[1]);

		$dataline = explode("@@msel@@", $dataLine[0]);
		if (is_numeric($dataline[0]))
			$toFolderID = $dataline[0];
		$toTeamID = $dataline[1];
		$dataline = explode("@@::apply_sel::@@", $dataline[2]);

		if ($folderID == "0")
			$folderintoTeam = true;
		else
			$folderintoTeam = verifyFolderInTeam($folderID,$teamID);
		if ($toFolderID == "0")
			$toFolderintoTeam = true;
		else
			$toFolderintoTeam = verifyFolderInTeam($toFolderID,$toTeamID);
		$isinteam = isInTeam($teamID,$CURRENTUSERID);
		$isintoTeam = isInTeam($toTeamID,$CURRENTUSERID);
		if ($isinteam && $isintoTeam && $folderintoTeam && $toFolderintoTeam) {
			foreach ($dataline as $item) {
				if (substr($item,0,4) == "fold") {
					$lenght = strlen($item) - 4;
					$movedFolderID = substr($item,4,$lenght);
					mf_query("UPDATE teams_folders SET subfolderID = '$toFolderID', teamID = '$toTeamID' WHERE folderID = '$movedFolderID' AND teamID = '$teamID' LIMIT 1");
					if ($teamID != $toTeamID)
						change_folderTeam($movedFolderID,$teamID,$toTeamID);
				}
				else if (substr($item,0,4) == "file") {
					$lenght = strlen($item) - 4;
					$movedFileID = substr($item,4,$lenght);
					mf_query("UPDATE teams_files SET folderID = '$toFolderID', teamID = '$toTeamID' WHERE fileID = '$movedFileID' AND teamID = '$teamID'");
				}
			}
		}
	}
	return;
}

function ajax_copy_selToFolder($dataLine) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $CURRENTSTATUS;
	global $LANG;
	global $siteSettings;

	$dataLine = explode("@@::ttt::@@", $dataLine);
	if ($CURRENTUSER != "anonymous" && is_numeric($dataLine[2])) {
		$teamID = $dataLine[2];
		$folderID = "0";
		if ($dataLine[1])
			$folderID = make_num_safe($dataLine[1]);

		$dataline = explode("@@msel@@", $dataLine[0]);
		if (is_numeric($dataline[0]))
			$toFolderID = $dataline[0];
		$toTeamID = $dataline[1];
		$dataline = explode("@@::apply_sel::@@", $dataline[2]);

		if ($folderID == "0")
			$folderintoTeam = true;
		else
			$folderintoTeam = verifyFolderInTeam($folderID,$teamID);
		if ($toFolderID == "0")
			$toFolderintoTeam = true;
		else
			$toFolderintoTeam = verifyFolderInTeam($toFolderID,$toTeamID);
		$isinteam = isInTeam($teamID,$CURRENTUSERID);
		$isintoTeam = isInTeam($toTeamID,$CURRENTUSERID);
		$now = time();
		if ($isinteam && $isintoTeam && $folderintoTeam && $toFolderintoTeam) {
			foreach ($dataline as $item) {
				if (substr($item,0,4) == "fold") {
					$lenght = strlen($item) - 4;
					$movedFolderID = substr($item,4,$lenght);
					$copyfold = mf_query("SELECT folderName FROM teams_folders WHERE folderID = '$movedFolderID' AND teamID = '$teamID' LIMIT 1");
					$copyfold = mysql_fetch_assoc($copyfold);
					if ($copyfold['folderName']) {
						mf_query("INSERT IGNORE INTO teams_folders (subfolderID, teamID, folderName, folderDate) VALUES ('$toFolderID', '$toTeamID', \"$copyfold[folderName]\", '$now')");
						$newfold = mf_query("SELECT folderID FROM teams_folders WHERE subfolderID = '$toFolderID' AND teamID = '$toTeamID' AND folderName = \"$copyfold[folderName]\" LIMIT 1");
						$newfold = mysql_fetch_assoc($newfold);
						if ($newfold['folderID']) {
							$query_file = mf_query("SELECT fileID FROM teams_files WHERE teamID = '$teamID' AND folderID = '$movedFolderID'");
							while ($row_file = mysql_fetch_assoc($query_file)) {
								mf_query("INSERT IGNORE INTO teams_files (folderID, teamID, fileID) VALUES ('$newfold[folderID]', '$toTeamID', '$row_file[fileID]')");
							}
							copy_folder($movedFolderID,$teamID,$toTeamID,$newfold['folderID']);
						}
					}
				}
				else if (substr($item,0,4) == "file") {
					$lenght = strlen($item) - 4;
					$movedFileID = substr($item,4,$lenght);
					mf_query("INSERT IGNORE INTO teams_files (folderID, teamID, fileID) VALUES ('$toFolderID', '$toTeamID', '$movedFileID')");
				}
			}
		}
	}
	return;
}

function ajax_file_manager($dataLine) {
	return file_manager($dataLine);
}

function ajax_file_upload($dataLine) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $CURRENTSTATUS;
	global $LANG;
	global $siteSettings;
	global $verifyEditDelete;

	$dataline = explode("@@::fu::@@", $dataLine);
	if ($CURRENTUSER != "anonymous" && is_numeric($dataline[0])) {
		$teamID = $dataline[0];
		$isinteam = isInTeam($teamID,$CURRENTUSERID);
		if ($isinteam) {
			$last_post_date = "last_post_date";
			if ($verifyEditDelete)
				$last_post_date = "last_post_date_T";
			$threadID = "";
			if (is_numeric($dataline[1]))
				$threadID = $dataline[1];
			$threadList = "";
			$query_threads = mf_query("SELECT ID, title FROM forum_topics WHERE teamID = '$teamID' AND threadtype < 3 ORDER BY $last_post_date DESC");
			while ($row = mysql_fetch_assoc($query_threads))
				$threadList .= "<option value='$row[ID]'>$row[title]</option>";
		
			if ($threadList)
				$threadList = "$LANG[FILE_UPLOAD_ANNOUNCE] <select name='team_thread' class='bselect' id='team_thread'><option value='0'>$LANG[FILE_UPLOAD_SELECT_THREAD]</option>".$threadList."</select>";
		}
		return $threadList;
	}
}

function ajax_file_status($dataLine) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $CURRENTSTATUS;
	global $siteSettings;
	global $LANG;

	$dataline = explode("@@::stat::@@", $dataLine);
	if ($CURRENTUSER != "anonymous" && is_numeric($dataline[0]) && is_numeric($dataline[1])) {
		if (isInTeam($dataline[1],$CURRENTUSERID)) {
			$query_team = mf_query("SELECT fileID FROM teams_files WHERE teamID = '$dataline[1]' AND fileID = '$dataline[0]'");
			$query_team = mysql_fetch_assoc($query_team);
			if ($query_team['fileID']) {
				$query_file = mf_query("SELECT publicfile FROM files WHERE fileID = '$dataline[0]'");
				$query_file = mysql_fetch_assoc($query_file);
				if ($query_file['publicfile'] != "1") {
					mf_query("UPDATE files SET publicfile='1' WHERE fileID = '$dataline[0]'");
					$retstr = "$LANG[FILE_MANAGER_STATUS_PUBLIC] <span onclick=\"file_status($dataline[0]);\" class='button_mini'>$LANG[FILE_MANAGER_STATUS_PUBLIC_TEXT]</span>";
				}
				else {
					mf_query("UPDATE files SET publicfile='0' WHERE fileID = '$dataline[0]'");
					$retstr = "$LANG[FILE_MANAGER_STATUS_PRIVATE] <span onclick=\"file_status($dataline[0]);\" class='button_mini'>$LANG[FILE_MANAGER_STATUS_PRIVATE_TEXT]</span>";
				}
				return $dataline[0]."::@@::".$query_file['publicfile']."::@@::".$retstr;
			}
		}
	}
}

sajax_init();

include("ajax_commonlib.php");

	sajax_export("ajax_rename_file","ajax_rename_folder","ajax_move_selection","ajax_delete_selection","ajax_move_selToFolder","ajax_copy_selToFolder","ajax_file_manager","ajax_file_upload","ajax_file_status"); // list of functions to export
	sajax_handle_client_request(); // serve client instances


function file_manager($dataLine) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $CURRENTSTATUS;
	global $LANG;
	global $siteSettings;

	$dataline = explode("@@:f:@@", $dataLine);
	if ($CURRENTUSER != "anonymous") {
		$teamID = "";
		if (is_numeric($dataline[1])) {
			$teamID = $dataline[1];
			$isinteam = isInTeam($teamID,$CURRENTUSERID);
			if (!$isinteam)
				exit($LANG['REFUSED']);
			$teamName = team_name($teamID);
		}
		$folderID = "0";
		if (is_numeric($dataline[0]))
			$folderID = $dataline[0];
		$order = "files.fileName";
		$sens = "ASC";
		$img_name = " <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt'' />";
		$img_size = "";
		$img_date = "";
		$img_user = "";
		$link_order = "";
		if (isset($dataline[2])) {
			$link_order = $dataline[2];
			if ($dataline[3] == "ASC") {
				$sens = "ASC";
				$sens_link = "DESC";
				$sens_img = "down";
			}
			else {
				$sens = "DESC";
				$sens_link = "ASC";
				$sens_img = "up";
			}
			if ($link_order == "name") {
				$order = "files.fileName";
				$img_name = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'' />";
			}
			else if ($link_order == "size") {
				$order = "files.fileSize";
				$img_name = "";
				$img_size = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'' />";
			}
			else if ($link_order == "date") {
				$order = "files.fileUploadDate";
				$img_name = "";
				$img_date = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'' />";
			}
			else if ($link_order == "user") {
				$order = "users.username";
				$img_name = "";
				$img_user = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'' />";
			}
		}
		
		$fileslist = "<div style='display:table;'>
						<div class='row'>
							<div class='cell' style='padding-right:4px;padding-top:2px;border-right:2px solid silver;'>
								<div class='bold'>$LANG[FILE_MANAGER_FOLDER_COL]</div>
								<div id='browse_folders' style='display:block;min-height:250px;'>".list_folders($teamID,$folderID,$link_order,$sens)."</div>
							</div>
							<div class='cell'><div style='min-height:250px;'>
								<div style='display:table;'>";
		$fileslist .= "
				<div class='row bold''>
					<div class='cell'></div>
					<div class='cell' style='padding-left:8px;min-width:200px;'><span onclick=\"file_manager($folderID,$teamID,'name','$sens_link');\" class='link'>$LANG[FILE_MANAGER_FILE_COL]".$img_name."</span>&nbsp;";
		if ($teamID)
			$fileslist .= "<span onclick=\"upload_file($teamID);\" class='button_mini'>$LANG[TEAM_UPLOADFILE]</span>";
		$fileslist .= "</div>
					<div class='cell' style='padding-left:8px;'><span onclick=\"file_manager($folderID,$teamID,'size','$sens_link');\" class='link'>$LANG[FILE_MANAGER_SIZE_COL]".$img_size."</span></div>
					<div class='cell' style='padding-left:8px;'><span onclick=\"file_manager($folderID,$teamID,'date','$sens_link');\" class='link'>$LANG[FILE_MANAGER_DATE_COL]".$img_date."</span></div>
					<div class='cell' style='padding-left:8px;'><span onclick=\"file_manager($folderID,$teamID,'user','$sens_link');\" class='link'>$LANG[FILE_MANAGER_UPLOADEDBY_COL]".$img_user."</span></div>
					<div class='cell' style='padding-left:8px;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/downarrowon.gif' alt='' /></div>
					<div class='cell'></div>
				</div>";
		if ($folderID != "0") {
			$subfolder = mf_query("SELECT subfolderID FROM teams_folders WHERE teamID = '$teamID' AND folderID = '$folderID' LIMIT 1");
			$subfolder = mysql_fetch_assoc($subfolder);
			$icon = "<img src='images/core/dirup.png' alt='' style='vertical-align:text-bottom;'/>";
			$fileslist .= "
				<div class='row onmouseover'>
					<div class='cell'></div>
					<div class='cell' style='padding-left:4px;padding-right:4px;padding-top:2px;border-right:1px solid silver;'><span onclick=\"file_manager($subfolder[subfolderID],$teamID);\" style='cursor:pointer;'>$icon ...</span></div>
					<div class='cell right' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;'></div>
					<div class='cell' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;'></div>
					<div class='cell' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;'></div>
					<div class='cell'></div>
				</div>";
		}
		$i = 0;
		$icon = "<img src='images/core/folder.png' alt='' style='vertical-align:text-bottom;'/>";
		$query_folders = mf_query("SELECT * FROM teams_folders WHERE teamID = '$teamID' AND subfolderID = '$folderID' ORDER BY folderName");
		while ($row = mysql_fetch_assoc($query_folders)) {
			$folderName = $row['folderName'];
			if (strlen($folderName) > 35)
				$folderName = substr($folderName,0,35)."[...]";
			$fileslist .= "
				<div class='row onmouseover'>
					<div class='cell'><input type='checkbox' id='file$i' name='fold$row[folderID]' value=''/></div>
					<div class='cell' style='padding-left:4px;padding-right:4px;padding-top:2px;border-right:1px solid silver;'>
						<div onclick=\"file_manager($row[folderID],$teamID);\" style='cursor:pointer;display:inline-block;'>
							$icon 
						</div>
						<div onclick=\"file_manager($row[folderID],$teamID);\" style='cursor:pointer;display:inline-block;'>
							<span style='display:inline;' id='foldername$row[folderID]' >$folderName</span>
							<span style='display:none;' id='saved_foldername$row[folderID]'>$row[folderName]</span>
						</div>
						<div style='display:none;' id='renamefolder$row[folderID]'>
							<input type='text' name='renamefolder$row[folderID]' id='inputrenamefolder$row[folderID]' value=\"$row[folderName]\" size='14' class='bselect' onblur=\"close_renamefold($row[folderID]);\"/>
							<span class='button_mini' onclick=\"rename_folder($row[folderID]);\">$LANG[OK]</span>
							<span class='button_mini' >$LANG[CANCEL]</span>
						</div>
					</div>
					<div class='cell right' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;'></div>
					<div class='cell' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;font-size:0.8em;'>".date($LANG['DATE_LINE_SHORT2'],$row['folderDate'])."</div>
					<div class='cell' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;'></div>
					<div class='cell' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;'></div>
					<div class='cell'>
						<div style='width:80px;margin-left:0px;height:15px;overflow:hidden;text-align:right;' id='actions_folder_$row[folderID]' onclick=\"document.getElementById('actions_folder_$row[folderID]').style.height = '70px';\"><span style='cursor:pointer;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menudown.gif' alt='+' /></span>
							<div style='cursor:auto;margin-top:8px;text-align:left;' onmouseover=\"document.getElementById('actions_folder_$row[folderID]').style.height = '70px';\" onmouseout=\"document.getElementById('actions_folder_$row[folderID]').style.height = '15px';\" >
								<div onclick=\"document.getElementById('foldername$row[folderID]').style.display='none';document.getElementById('renamefolder$row[folderID]').style.display='inline';document.getElementById('inputrenamefolder$row[folderID]').focus();\" class='link' style='margin-bottom:8px;'>$LANG[FILE_MANAGER_RENAME_FILE]</div>
								<div onclick=\"file_delete('fold$row[folderID]');\" class='link'>$LANG[FILE_MANAGER_DELETE_FILE]</div>
							</div>
						</div>
					</div>
				</div>";
			$i ++;
		}
		$query_files = mf_query("SELECT files.*, users.username FROM teams_files LEFT JOIN files ON files.fileID = teams_files.fileID LEFT JOIN users ON users.ID = files.userID WHERE teams_files.teamID = '$teamID' AND teams_files.folderID = '$folderID' ORDER BY $order $sens");
		while ($row = mysql_fetch_assoc($query_files)) {
			if ($row['fileSize'] < 1048576)
				$filesize = round(($row['fileSize'] / 1024),2)." Kb";
			else
				$filesize = round(($row['fileSize'] / 1048576),2)." Mb";
			$icon = "<img src='images/core/";
			if (file_exists("images/core/".$row['fileExtension'].".png"))
				$icon .= $row['fileExtension'];
			else
				$icon .= "unknown";
			$icon .= ".png' alt='' style='vertical-align:text-bottom;'/>";
			$fileName = $row['fileName'];
			if (strlen($fileName) > 35)
				$fileName = substr($fileName,0,35)."[...]";
			if ($row['publicfile']) {
				$filestatus = "$LANG[FILE_MANAGER_STATUS_PUBLIC] <span onclick=\"file_status($row[fileID]);\" class='button_mini'>$LANG[FILE_MANAGER_STATUS_PUBLIC_TEXT]</span>";
				$filestatuscolor = "green";
			}
			else {
				$filestatus = "$LANG[FILE_MANAGER_STATUS_PRIVATE] <span onclick=\"file_status($row[fileID]);\" class='button_mini'>$LANG[FILE_MANAGER_STATUS_PRIVATE_TEXT]</span>";
				$filestatuscolor = "black";
			}
			$fileslist .= "
				<div class='row onmouseover'>
					<div class='cell'>
						<input type='checkbox' id='file$i' name='file$row[fileID]' value=''/>
					</div>
					<div class='cell' style='padding-left:4px;padding-right:4px;padding-top:2px;border-right:1px solid silver;white-space:nowrap;'>
						<div onclick=\"location.href='index.php?shard=teams&action=download&teamID=$teamID&fileID=$row[fileID]';\" style='cursor:pointer;display:inline-block;'>
							$icon 
						</div>
						<div style='cursor:pointer;display:inline-block;'>
							<span style='display:inline;' id='filename$row[fileID]' ><a href='".make_link("teams","&amp;action=download&amp;teamID=$teamID&amp;fileID=$row[fileID]")."' title=\"$row[fileName]\" id='filenamecell$row[fileID]' style='color:$filestatuscolor;'>$fileName</a></span>
							<span style='display:none;' id='saved_filename$row[fileID]'>$row[fileName]</span>
						</div>
						<div style='display:none;' id='renamefile$row[fileID]'>
							<input type='text' name='renamefile$row[fileID]' id='inputrenamefile$row[fileID]' value=\"$row[fileName]\" size='24' class='bselect' onblur=\"close_rename($row[fileID]);\"/>
							<span class='button_mini' onclick=\"rename_file($row[fileID]);\">$LANG[OK]</span>
							<span class='button_mini' >$LANG[CANCEL]</span>
						</div>
						<div style='display:none;' id='filestatus$row[fileID]'>
							<div style='display:inline-block;' id='filestatuscontent$row[fileID]'>$filestatus</div>
							<span onclick=\"document.getElementById('filename$row[fileID]').style.display='inline';document.getElementById('renamefile$row[fileID]').style.display='none';document.getElementById('filestatus$row[fileID]').style.display='none';\" class='button_mini' >$LANG[BUTTON_BACK]</span>
						</div>
					</div>
					<div class='cell right' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;'>$filesize</div>
					<div class='cell' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;font-size:0.8em;'>".date($LANG['DATE_LINE_SHORT2'],$row['fileDate'])."</div>
					<div class='cell' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;'>$row[username]</div>
					<div class='cell right' style='padding-left:4px;padding-right:4px;border-right:1px solid silver;font-size:0.8em;'>$row[downloads]</div>
					<div class='cell'>
						<div style='width:80px;margin-left:0px;height:15px;overflow:hidden;text-align:right;' id='actions_file_$row[fileID]' onclick=\"document.getElementById('actions_file_$row[fileID]').style.height = '80px';\"><span style='cursor:pointer;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menudown.gif' alt='+' /></span>
							<div style='cursor:auto;margin-top:8px;text-align:left;' onmouseover=\"document.getElementById('actions_file_$row[fileID]').style.height = '80px';\" onmouseout=\"document.getElementById('actions_file_$row[fileID]').style.height = '15px';\" >
								<div onclick=\"document.getElementById('filename$row[fileID]').style.display='none';document.getElementById('renamefile$row[fileID]').style.display='inline';document.getElementById('filestatus$row[fileID]').style.display='none';document.getElementById('inputrenamefile$row[fileID]').focus();\" class='link' style='margin-top:-4px;margin-bottom:8px;'>$LANG[FILE_MANAGER_RENAME_FILE]</div>
								<div onclick=\"file_delete('file$row[fileID]');\" class='link' style='margin-bottom:8px;'>$LANG[FILE_MANAGER_DELETE_FILE]</div>
								<div onclick=\"document.getElementById('filename$row[fileID]').style.display='none';document.getElementById('renamefile$row[fileID]').style.display='none';document.getElementById('filestatus$row[fileID]').style.display='inline';\" class='link'>$LANG[FILE_MANAGER_STATUS]</div>
							</div>
						</div>
					</div>
				</div>";
			$i ++;
		}
		$retstr = "<div style='border-bottom:1px silver solid;margin-bottom:8px;height:24px;'>&nbsp;<a href='".make_link("teams")."' class='button' style='margin-right:8px;'>$LANG[BACK_TEAMS_LIST]</a>";
		$retstr .= "<a href='".make_link("forum","","#threadlist/teams/$teamID")."' class='button'>
				<img src='engine/grafts/$siteSettings[graft]/images/thread.png' border='0' style='vertical-align:middle;' alt='' /> $LANG[THREAD_LISTING]</a></div>";
		$retstr .= "<div style='font-size:2em;'>$LANG[TEAM_FILE_MANAGER]</div>";

		$maxfilesize = $siteSettings['team_maxfilesize'] / 1024;
		$retstr .= "<div id='file_upload' class='displayDiv'><span class='bold'>$LANG[TEAM_UPLOADFILE] ($LANG[FILE_MAX_SIZE]: $maxfilesize $LANG[TEAM_FILE_SIZE_UNIT])</span><div style='float:right;' class='button' onclick=\"closeDiv('file_upload');\">X</div><div style='clear:both;margin-bottom:12px;'></div><form name='file_upload' action='".make_link("teams","&amp;action=g_fileUpload&amp;teamID=$teamID&folderID=$folderID")."' method='post' enctype='multipart/form-data'>
				<input type='file' multiple='true' name='newfile[]' size='50' class='bselect'/>
				&nbsp;<input class='button_mini' type='submit' value=\"$LANG[SUBMIT]\" />
				<div id='file_upload_thread' style='margin-top:8px;'></div>
				</form></div>";
		$retstr .= "<div id='makefold' class='displayDiv'><span class='bold'>$LANG[CREATE_SUBFOLDER]</span><div style='float:right;' class='button' onclick=\"closeDiv('makefold');\">X</div><div style='clear:both;margin-bottom:12px;'></div><form name='makefold' method='post' action='".make_link("teams","&amp;action=g_file_newfolder&amp;teamID=$teamID&amp;folderID=$folderID")."'><input id='newfolddiv' type='text' value='' name='newfold' size='30' class='bselect'/> <input type='submit' value=\"$LANG[CREATE_SUBFOLDER2]\" class='button_mini'/></form></div>";

		$retstr .= "<div style='margin-top:16px;'></div>".$fileslist."</div></div>";
		$retstr .= "
			<div style='font-size:0.9em;margin-top:8px;border-top:1px solid silver;' class='bold'>";
		if ($teamID)		
			$retstr .= "<input type='checkbox' name='file$i' id='file_sel_all' value='' style='vertical-align:middle;' onchange=\"select_files_checkbox();\"/>
				<span style='cursor:pointer;' onclick=\"select_files();\">$LANG[FILE_MANAGER_SELECT_ALL]</span> / 
				<span style='cursor:pointer;' onclick=\"unselect_files();\">$LANG[FILE_MANAGER_UNSELECT_ALL]</span>
				&nbsp;<span onclick=\"displayDiv('makefold');document.getElementById('newfolddiv').focus();\" class='button_mini'>$LANG[CREATE_SUBFOLDER]</span>
				<div style='margin-left:24px;'>
					<select name='file_sel_apply' id='file_sel_apply' class='bselect' onchange=\"apply_select();\">
						<option value='0'>$LANG[FILE_MANAGER_APPLY_TO_SELECTION]:</option>
						<option value='sep' disabled='disabled'>&nbsp;</option>
						<option value='3'>$LANG[FILE_MANAGER_COPY_TO_FOLDER]</option>
						<option value='1'>$LANG[FILE_MANAGER_MOVE_TO_FOLDER]</option>
						<option value='2'>$LANG[FILE_MANAGER_DELETE_FILE]</option>
					</select>
				</div>";
		$retstr .= "</div></div></div></div>";
		
		return $retstr;
	}
}

function delete_subfold($folderID,$teamID) {
	mf_query("DELETE FROM teams_folders WHERE folderID = '$folderID' AND teamID = '$teamID' LIMIT 1");
	mf_query("DELETE FROM teams_files WHERE folderID = '$folderID' AND teamID = '$teamID'");
	$query_subfold = mf_query("SELECT folderID FROM teams_folders WHERE subfolderID = '$folderID' AND teamID = '$teamID'");
	while ($row_subfold = mysql_fetch_assoc($query_subfold)) {
		delete_subfold($row_subfold['folderID'],$teamID);
	}
}

function change_folderTeam($movedFolderID,$teamID,$toTeamID) {
	$query_folder = mf_query("SELECT folderID FROM teams_folders WHERE teamID = '$teamID' AND subfolderID = '$movedFolderID'");
	while ($row_folder = mysql_fetch_assoc($query_folder)) {
		mf_query("UPDATE teams_folders SET teamID = '$toTeamID' WHERE folderID = '$row_folder[folderID]' AND teamID = '$teamID' LIMIT 1");
		mf_query("UPDATE teams_files SET teamID = '$toTeamID' WHERE folderID = '$movedFolderID' AND teamID = '$teamID'");
		$verify_folder = mf_query("SELECT folderID FROM teams_folders WHERE teamID = '$teamID' AND subfolderID = '$row_folder[folderID]' LIMIT 1");
		if ($verify_folder = mysql_fetch_assoc($verify_folder))
			change_folderTeam($row_folder['folderID'],$teamID,$toTeamID);
	}
}

function copy_folder($movedFolderID,$teamID,$toTeamID,$newfoldID) {
	$now = time();
	$query_folder = mf_query("SELECT folderID FROM teams_folders WHERE teamID = '$teamID' AND subfolderID = '$movedFolderID'");
	while ($row_folder = mysql_fetch_assoc($query_folder)) {
		$copyfold = mf_query("SELECT folderName FROM teams_folders WHERE folderID = '$row_folder[folderID]' AND teamID = '$teamID' LIMIT 1");
		$copyfold = mysql_fetch_assoc($copyfold);
		if ($copyfold['folderName']) {
			mf_query("INSERT IGNORE INTO teams_folders (subfolderID, teamID, folderName, folderDate) VALUES ('$newfoldID', '$toTeamID', \"$copyfold[folderName]\", '$now')");
			$newfold = mf_query("SELECT folderID FROM teams_folders WHERE subfolderID = '$newfoldID' AND teamID = '$toTeamID' AND folderName = \"$copyfold[folderName]\" LIMIT 1");
			$newfold = mysql_fetch_assoc($newfold);
			if ($newfold['folderID']) {	
				$query_file = mf_query("SELECT fileID FROM teams_files WHERE teamID = '$teamID' AND folderID = '$row_folder[folderID]'");
				while ($row_file = mysql_fetch_assoc($query_file)) {
					mf_query("INSERT IGNORE INTO teams_files (folderID, teamID, fileID) VALUES ('$newfold[folderID]', '$toTeamID', '$row_file[fileID]')");
				}
				copy_folder($row_folder['folderID'],$teamID,$toTeamID,$newfold['folderID']);
			}
		}
	}
}

function list_folders($teamID,$folderID,$link_order,$sens) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $CURRENTSTATUS;
	global $LANG;
	global $siteSettings;

	if ($CURRENTUSER != "anonymous") {
		$linksave = "";
		if ($link_order)
			$linksave = "&order=$link_order&sens=$sens";
			
		if ($teamID > 0)
			$teamID = make_num_safe($teamID);
		else
			$teamID = "";

		$opened_folders[] = "";
		$subfolderID = $folderID;
		$i = 0;
		while ($subfolderID != "0") {
			$query_folder = mf_query("SELECT subfolderID FROM teams_folders WHERE teamID = '$teamID' AND folderID = '$subfolderID' LIMIT 1");
			$query_folder = mysql_fetch_assoc($query_folder);
			if ($query_folder['subfolderID']) {
				$opened_folders[$i] = $query_folder['subfolderID'];
				$i ++;
			}
			$subfolderID = $query_folder['subfolderID'];
		}

		$listFolders = "";
		$query_teams = mf_query("SELECT teams.* FROM teams_users JOIN teams ON teams.teamID = teams_users.teamID WHERE teams_users.userID = '$CURRENTUSERID' ORDER BY teams.teamName");
		while ($row = mysql_fetch_assoc($query_teams)) {
			$open = false;
			$display1 = "inline";
			$display2 = "none";
			if ($teamID == $row['teamID']) {
				$open = true;
				$display1 = "none";
				$display2 = "inline";
			}
			$listFolders .= "<div id='browsefoldTeam$row[teamID]' style='display:block;'>";
			$listFolders .= "<div style='display:inline-block;width:12px;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menuright.gif' alt='' onclick=\"toggleLayer('browsefold_0_".$row['teamID']."'); toggleLayer('imgbrowsefold_on_0_".$row['teamID']."','inline'); toggleLayer('imgbrowsefold_off_0_".$row['teamID']."','inline');\" id='imgbrowsefold_off_0_".$row['teamID']."' style='display:$display1;vertical-align:sub;'/><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menudown.gif' alt='' onclick=\"toggleLayer('browsefold_0_".$row['teamID']."'); toggleLayer('imgbrowsefold_on_0_".$row['teamID']."','inline'); toggleLayer('imgbrowsefold_off_0_".$row['teamID']."','inline');\" id='imgbrowsefold_on_0_".$row['teamID']."' style='display:$display2;vertical-align:sub;'/></div> ";
			$currentfolder = "";
			if ($folderID == "0" && $teamID == $row['teamID'])
				$currentfolder = "color:#F5F5F5;background-color:#333333";
			$listFolders .= "<span onclick=\"file_manager('0',$row[teamID]);\" class='link bold' style='text-decoration:underline;$currentfolder'>";
			$listFolders .= $row['teamName'];
			$listFolders .= "</span>";
			$listFolders .= "</div>";
			$listFolders .= browse_subfolders("0",$row['teamID'],"0",$open,$opened_folders,$folderID);
		}
		return $listFolders;
	}
}

function browse_subfolders($folderID,$teamID,$margin,$open,$opened_folders,$link_folder) {
	global $siteSettings;

	if ($open || in_array($folderID,$opened_folders))
		$listFolders = "<div id='browsefold_".$folderID."_".$teamID."' style='display:block;'>";
	else
		$listFolders = "<div id='browsefold_".$folderID."_".$teamID."' style='display:none;'>";
	$margin = $margin + 16;
	$query_folder = mf_query("SELECT * FROM teams_folders WHERE teamID = '$teamID' AND subfolderID = '$folderID' ORDER BY folderName");
	while ($row = mysql_fetch_assoc($query_folder)) {
		$listFolders .= "<div style='margin-left:".$margin."px;'>";
		$query_subfolder = mf_query("SELECT * FROM teams_folders WHERE teamID = '$teamID' AND subfolderID = '$row[subfolderID]' LIMIT 1");
		if ($row_subfolder = mysql_fetch_assoc($query_subfolder)) {
			$listFolders .= "<div style='display:inline-block;width:12px;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menuright.gif' alt='' onclick=\"toggleLayer('browsefold_".$row['folderID']."_".$teamID."'); toggleLayer('imgbrowsefold_on_".$row['folderID']."_".$teamID."','inline'); toggleLayer('imgbrowsefold_off_".$row['folderID']."_".$teamID."','inline');\" id='imgbrowsefold_off_".$row['folderID']."_".$teamID."' style='display:inline;vertical-align:sub;'/><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menudown.gif' alt='' onclick=\"toggleLayer('browsefold_".$row['folderID']."_".$teamID."'); toggleLayer('imgbrowsefold_on_".$row['folderID']."_".$teamID."','inline'); toggleLayer('imgbrowsefold_off_".$row['folderID']."_".$teamID."','inline');\" id='imgbrowsefold_on_".$row['folderID']."_".$teamID."' style='display:none;vertical-align:sub;'/></div> ";
			$currentfolder = "";
			if ($row['folderID'] == $link_folder)
				$currentfolder = "color:#F5F5F5;background-color:#333333";
			$listFolders .= "<span onclick=\"file_manager($row[folderID],$teamID);\" class='link' style='$currentfolder'>";
			$listFolders .= $row['folderName'];
			$listFolders .= "</span>";
			$listFolders .= "</div>";
			$listFolders .= browse_subfolders($row['folderID'],$teamID,$margin,false,$opened_folders,$link_folder);
		}
	}
	$listFolders .= "</div>";
	return $listFolders;
}	

function list_subfolders($folderID,$teamID,$margin,$open,$excluded_folders,$excluded,$opened_folders,$link_folder,$action_type="1") {
	global $siteSettings;
	if ($open || in_array($folderID,$opened_folders))
		$listFolders = "<div id='listfold_".$folderID."_".$teamID."' style='display:block;'>";
	else
		$listFolders = "<div id='listfold_".$folderID."_".$teamID."' style='display:none;'>";
	$margin = $margin + 16;
	$query_folder = mf_query("SELECT * FROM teams_folders WHERE teamID = '$teamID' AND subfolderID = '$folderID' ORDER BY folderName");
	while ($row = mysql_fetch_assoc($query_folder)) {
		if ($row['subfolderID'] == $link_folder)
			$excluded = false;
		if (in_array($row['folderID'],$excluded_folders))
			$excluded = true;
		
		$listFolders .= "<div style='margin-left:".$margin."px;'>";
		$query_subfolder = mf_query("SELECT * FROM teams_folders WHERE teamID = '$teamID' AND subfolderID = '$row[subfolderID]' LIMIT 1");
		if ($row_subfolder = mysql_fetch_assoc($query_subfolder)) {
			$listFolders .= "<div style='display:inline-block;width:12px;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menuright.gif' alt='' onclick=\"toggleLayer('listfold_".$row['folderID']."_".$teamID."'); toggleLayer('imglistfold_on_".$row['folderID']."_".$teamID."','inline'); toggleLayer('imglistfold_off_".$row['folderID']."_".$teamID."','inline');\" id='imglistfold_off_".$row['folderID']."_".$teamID."' style='display:inline;vertical-align:sub;'/><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menudown.gif' alt='' onclick=\"toggleLayer('listfold_".$row['folderID']."_".$teamID."'); toggleLayer('imglistfold_on_".$row['folderID']."_".$teamID."','inline'); toggleLayer('imglistfold_off_".$row['folderID']."_".$teamID."','inline');\" id='imglistfold_on_".$row['folderID']."_".$teamID."' style='display:none;vertical-align:sub;'/></div> ";
			$currentfolder = "";
			if ($row['folderID'] == $link_folder)
				$currentfolder = "color:#F5F5F5;background-color:#333333";
			if ($excluded ||  $row['folderID'] == $link_folder)
				$listFolders .= "<span style='cursor:no-drop;$currentfolder'>";
			else {
				if ($action_type == "1")
					$listFolders .= "<span onclick=\"move_to_selection($row[folderID],$teamID);\" class='link'>";
				else
					$listFolders .= "<span onclick=\"copy_to_selection($row[folderID],$teamID);\" class='link'>";
			}
			$listFolders .= $row['folderName'];
			$listFolders .= "</span>";
			$listFolders .= "</div>";
			$listFolders .= list_subfolders($row['folderID'],$teamID,$margin,false,$excluded_folders,$excluded,$opened_folders,$link_folder,$action_type);
		}
	}
	$listFolders .= "</div>";
	return $listFolders;
}	
	
function listteam($teams,$invites=false) {

	global $siteSettings;
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $LANG;

	$listteam = "";
	$teamID_temp = "";
	while ($row = mysql_fetch_assoc($teams)) {
		$teamID = $row['teamID'];
		if ($teamID != $teamID_temp) {
			$teamname = $row['teamName'];
			if (!$invites && ($row['userID'] == $CURRENTUSERID || ((!$row['hideteam'] || $row['validated'] == "3") && isInGroup($CURRENTUSER, 'level9')) || isInGroup($CURRENTUSER, 'admin'))) {
				$teamID_temp = $teamID;
				$team = "";
				$countmembers = mf_query("SELECT count(teamID) AS countmemb FROM teams_users WHERE teamID = '$teamID'");
				$countmembers = mysql_fetch_assoc($countmembers);
				$totalmembers = $countmembers['countmemb'];
				$listteam_temp = "";
				if ($row['userID'] == $CURRENTUSERID)
					$listteam_temp = "&nbsp;<a href='".make_link("forum","","#threadlist/teams/$teamID")."' class='button_mini'>
						<img src='engine/grafts/$siteSettings[graft]/images/thread.png' border='0' style='vertical-align:middle;' alt='thread' /> $LANG[THREAD_LISTING]</a>";

				$listteam .= "<div style='text-align:center;padding-bottom:2px;border-bottom:1px solid silver;'><div><span style='font-size:2.0em;font-weight:bold;'>$teamname</span><a name='team$teamID'></a> ($totalmembers $LANG[TEAM_TOTAL_MEMBERS])</div>";
				if ($row['userID'] == $CURRENTUSERID)	
					$listteam .= "<a href='".make_link("teams","&action=g_files&amp;teamID=$teamID")."' class='button_mini'>
				<img src='engine/grafts/$siteSettings[graft]/images/folder.png' border='0' style='vertical-align:middle;' alt='folder' /> $LANG[TEAM_FILE_MANAGER]</a>";
				$listteam .= $listteam_temp;

				if (isInGroup($CURRENTUSER, 'admin') || ((!$row['hidemembers'] || $row['validated'] == "3") && isInGroup($CURRENTUSER, 'level9')) || $row['userID'] == $CURRENTUSERID)
					$listteam .= "&nbsp;<a href='".make_link("teams","&amp;action=g_Members&amp;teamID=$teamID")."' class='button_mini'>
						<img src='engine/grafts/$siteSettings[graft]/images/user.png' border='0' style='vertical-align:middle;' alt='Members' /> $LANG[TEAM_MEMBERS]</a>";
				if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level9') || $row['level'] == "1") {	
					$listteam .= "&nbsp;<a href='".make_link("teams","&amp;action=g_param&amp;teamID=$teamID")."' class='button_mini'> $LANG[TEAM_SETTINGS]</a>";
				
					$listteam .= "<span style='margin-left:32px;'><a href='".make_link("teams","&amp;action=g_ConfdeleteTeam&amp;teamID=$teamID")."' class='button_mini'>
					<img src='engine/grafts/$siteSettings[graft]/images/b_drop.png' border='0' style='vertical-align:middle;' alt='' /> $LANG[TEAM_DELETE_BUTTON]</a></span>";
				}
				$listteam .= "</div>";
			}
			else if ($invites) {
				$listteam .= "<div style='text-align:center;padding-bottom:6px;border-bottom:1px solid silver;'><div><span style='font-size:2.0em;font-weight:bold;'>$teamname</span><a name='team$teamID'></a></div>";
				$listteam .= "<a href='".make_link("teams","&action=invite_accept&amp;teamID=$teamID")."' class='button'>
				<img src='engine/grafts/$siteSettings[graft]/images/increase.png' border='0' style='vertical-align:sub;' alt='' /> $LANG[TEAM_ACCEPT_INVITE]</a>";
				$listteam .= "<span style='margin-left:32px;'><a href='".make_link("teams","&amp;action=invite_refuse&amp;teamID=$teamID")."' class='button'>
					<img src='engine/grafts/$siteSettings[graft]/images/decrease_red.png' border='0' style='vertical-align:sub;' alt='' /> $LANG[TEAM_REFUSE_INVITE]</a></span>";
				$listteam .= "</div>";
			
			}
		}
	}
	return $listteam;
}

function verifyFolderInTeam($folderID,$teamID) {
	$inTeam = false;
	$level = mf_query("SELECT folderID FROM teams_folders WHERE teamID = '$teamID' AND folderID = '$folderID' LIMIT 1");
	if ($level = mysql_fetch_assoc($level))
		$inTeam = true;
	
	return $inTeam;
}

function rename_file($dataline) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $CURRENTSTATUS;
	global $LANG;

	$dataline = explode("@@::rf::@@", $dataline);
	if ($CURRENTUSER != "anonymous" && is_numeric($dataline[3])) {
		$teamID = $dataline[3];
		$subfolderID = "0";
		if (is_numeric($dataline[2]))
			$subfolderID = $dataline[2];
		$fileID = make_num_safe($dataline[0]);
		$retstr = $fileID."::@@::";
		$newName = make_var_safe(urldecode($dataline[1]));
		$newName = utf8_encode($newName);
		$newName = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $newName);
		$newName = html_entity_decode($newName, ENT_NOQUOTES, 'UTF-8');
		
		$isinteam = isInTeam($teamID,$CURRENTUSERID);
		if ($isinteam) {
			$query_file = mf_query("SELECT files.fileID FROM files JOIN teams_files ON files.fileID = teams_files.fileID WHERE files.fileName = \"$newName\" AND files.fileID != '$fileID' AND teams_files.teamID = '$teamID' AND teams_files.folderID = '$subfolderID' LIMIT 1");
			$file = mysql_fetch_assoc($query_file);
			if (!$file['fileID']) {
				$query_file = mf_query("SELECT files.fileID FROM files JOIN teams_files ON files.fileID = teams_files.fileID WHERE files.fileID = '$fileID' AND teams_files.teamID = '$teamID' LIMIT 1");
				$file = mysql_fetch_assoc($query_file);
				if ($file['fileID'])
					mf_query("UPDATE files SET fileName = \"$newName\" WHERE fileID = '$fileID' LIMIT 1");
			}
			else 
				$retstr .= $LANG['FILE_MANAGER_RENAME_ERROR'];
			if (strlen($newName) > 35)
				$newName = substr($newName,0,35)."[...]";
			$retstr .= "::@@::".$newName;

		}
	}
	return $retstr;
}

function rename_folder($dataline) {
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $CURRENTSTATUS;
	global $LANG;

	$dataline = explode("@@::rf::@@", $dataline);
	if ($CURRENTUSER != "anonymous" && is_numeric($dataline[3])) {
		$teamID = $dataline[3];
		$subfolderID = "0";
		if (is_numeric($dataline[2]))
			$subfolderID = $dataline[2];
			
		$folderID = make_num_safe($dataline[0]);
		$retstr = $folderID."::@@::";
		$newName = make_var_safe(urldecode($dataline[1]));
		$newName = utf8_encode($newName);
		$newName = preg_replace_callback("/%u([0-9a-fA-F]{1,4})/", create_function('$matches','return "&#".hexdec($matches[0]).";";'), $newName);
		$newName = html_entity_decode($newName, ENT_NOQUOTES, 'UTF-8');

		
		$isinteam = isInTeam($teamID,$CURRENTUSERID);
		if ($isinteam) {
			$query_folder = mf_query("SELECT folderID FROM teams_folders WHERE folderName = \"$newName\" AND folderID != '$folderID' AND subfolderID = '$subfolderID' AND teamID = '$teamID' LIMIT 1");
			$folder = mysql_fetch_assoc($query_folder);
			if (!$folder['folderID']) {
				$query_folder = mf_query("SELECT folderID FROM teams_folders WHERE folderID = '$folderID' AND teamID = '$teamID' LIMIT 1");
				$folder = mysql_fetch_assoc($query_folder);
				if ($folder['folderID'])
					mf_query("UPDATE teams_folders SET folderName = \"$newName\" WHERE folderID = '$folderID' LIMIT 1");
			}
			else 
				$retstr .= $LANG['FILE_MANAGER_RENAME_ERROR2'];
			if (strlen($newName) > 35)
				$newName = substr($newName,0,35)."[...]";
			$retstr .= "::@@::".$newName;
		}
	}
	return $retstr;
}

function rules_et() {

	global $siteSettings;

	$JSS2 = mf_query ("SELECT body FROM forum_topics WHERE ID = '$siteSettings[rules_et_thread]' LIMIT 1");
	$JSS2 = mysql_fetch_assoc($JSS2);
	$JSS2 = format_post($JSS2['body'], true);
	$JSS2 = str_replace("\r", "<br />", $JSS2);
	$JSS2 = str_replace("\n", "<br />", $JSS2);
	$JSS2 = str_replace("\t", "<br />", $JSS2);
	$JSS2 = str_replace("\"", "", $JSS2);

	return $JSS2;
}

function display_rules_et() {

	global $LANG;

	$rules = "<div onclick=\"toggleLayer('rules_et');\" style='text-align:center;cursor:pointer;font-size:0.8em;'>$LANG[RULES_DISPLAY_ET]<div id='rules_et' style='display:none;text-align:left;padding:16px;'>".rules_et()."</div></div>";

	return $rules;
}

function team_menu($teamID,$team_members=false,$team_settings=false) {
	global $siteSettings;
	global $CURRENTUSER;
	global $CURRENTUSERID;
	global $LANG;

	$isinteam = isInTeam($teamID,$CURRENTUSERID);
	$menu_top = "<div style='border-bottom:1px silver solid;margin-bottom:8px;height:24px;'>";
	$menu_top .= "<a href='".make_link("teams")."#team$teamID' class='button' style='margin-right:8px;'>$LANG[BACK_TEAMS_LIST]</a>";
	if ($isinteam) {
		$menu_top .= "<a href='".make_link("forum","","#threadlist/teams/$teamID")."' class='button' style='margin-right:8px;'>
						<img src='engine/grafts/$siteSettings[graft]/images/thread.png' border='0' style='vertical-align:middle; alt='' /> $LANG[THREAD_LISTING]</a>";
		$menu_top .= "<a href='".make_link("teams","&action=g_files&amp;teamID=$teamID")."' class='button' style='margin-right:8px;'>
				<img src='engine/grafts/$siteSettings[graft]/images/folder.png' border='0' style='vertical-align:middle; alt='' /> $LANG[TEAM_FILE_MANAGER]</a>";
	}
	if ($team_members)
		$menu_top .= "<a href='".make_link("teams","&amp;action=g_Members&amp;teamID=$teamID")."' class='button' style='margin-right:8px;'>
						<img src='engine/grafts/$siteSettings[graft]/images/user.png' border='0' style='vertical-align:middle;' alt='' /> $LANG[TEAM_MEMBERS]</a>";
	if ($team_settings)
		$menu_top .= "<a href='".make_link("teams","&amp;action=g_param&amp;teamID=$teamID")."' class='button' style='margin-right:8px;'> $LANG[TEAM_SETTINGS]</a>";
	$menu_top .= "</div>";
	
	return $menu_top;
}
?>