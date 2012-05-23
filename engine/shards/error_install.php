<?php

switch ($action):

case "g_default": {
	$thisContentObj = New contentObj;
	$thisContentObj->contentType = "generic";
	$thisContentObj->primaryContent = "<h3>ERREUR</h3><br/>";
	$thisContentObj->primaryContent .= "<div>$LANG[INSTALL_ERROR1]</div>";
	$thisContentObj->primaryContent .= "<div>$LANG[INSTALL_ERROR2]</div>";
	$shardContentArray[] = $thisContentObj;
}
break;

endswitch;
?>