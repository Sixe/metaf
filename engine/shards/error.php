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

// error.php

    switch ($action):
    case "g_default":
    
		$error = "";
		if (isset($_REQUEST['error']))
			$error= make_var_safe($_REQUEST['error']);

		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->primaryContent = "<div style='font-size:2em;margin-bottom:8px;'>$LANG[ERROR_PAGE1]:</div>";
		$thisContentObj->primaryContent .= "<div style='font-size:1.5em;padding-bottom:100px;'>$LANG[ERROR_PAGE2] (\"$error\") $LANG[ERROR_PAGE3]</div>";
		$shardContentArray[] = $thisContentObj;
	
	break;
    
    endswitch;

?>
