<?php
    //-----------------------------------------------------------------------------------------------
    // rules.php
    //
    //----------------------------------------------------------------------------------------------

    switch ($action):
    
    
    //---------------------------------------------------
    // g_default -- default display of forum
    //---------------------------------------------------
	case "g_default":
    
    $thisContentObj = New contentObj;
	$thisContentObj->contentType = "generic";
	if ($CURRENTUSER != "anonymous" && $CURRENTUSER != "bot") {
		$thisContentObj->primaryContent = "<div style='border-top:1px solid black;border-bottom:1px solid black;padding-top:16px;margin-top:16px;padding-bottom:16px;margin-bottom:16px;font-size:2em;text-align:center;'>$LANG[RULES_TITLE0] $siteSettings[titlebase]$LANG[RULES_TITLE2]</div>";
		$thisContentObj->primaryContent .= display_tos();

		$shardContentArray[] = $thisContentObj;
	} 
	break;


    endswitch;
?>