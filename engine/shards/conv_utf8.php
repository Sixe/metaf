<?php
	//-----------------------------------------------------------------------------------------------
	// conv_utf-8.php
	//----------------------------------------------------------------------------------------------

	$co = &New contentObj;
	$co->contentType="generic";
	$co->primaryContent .= "<div style='padding-bottom: 8px; border-bottom:solid 1px silver;'></div>";
	$co->primaryContent .= "<div style='height:20px'></div>";
	$co->primaryContent .= "<div style='text-align:center;font-size:2em;'>Convert to UTF-8</div>";
	$co->primaryContent .= "<div style='height:40px'></div>";

if (!isInGroup($CURRENTUSER, "admin"))
	exit("");

switch ($action):

case "g_default"; {
/*	$co->primaryContent .= "<div><a href='index.php?shard=conv_utf8&amp;action=g_part1'>Tables avant 'fhits'</a></div>";
	$co->primaryContent .= "<div><a href='index.php?shard=conv_utf8&amp;action=g_part2'>Table 'fhits'</a></div>";
	$co->primaryContent .= "<div><a href='index.php?shard=conv_utf8&amp;action=g_part3'>Tables après 'fhits' sauf 'forum_posts' et 'postratings'</a></div>";
	$co->primaryContent .= "<div><a href='index.php?shard=conv_utf8&amp;action=g_part4'>Table 'forum_posts'</a></div>";
	$co->primaryContent .= "<div><a href='index.php?shard=conv_utf8&amp;action=g_part5'>Table 'postratings'</a></div>";
*/	
	$co->primaryContent .= "<div style='text-align:center;'><a href='index.php?shard=conv_utf8&amp;action=g_conv' class='button'>Lancer la conversion des tables.</a></div>";
	$shardContentArray[] = $co;

}
break;

case "g_conv": {
	$sql = 'SHOW TABLES';
	$result = mysql_query($sql) or die( mysql_error() );

	while ($row = mysql_fetch_row($result)) {
		
		$table = mysql_real_escape_string($row[0]);
		$sql = "ALTER TABLE $table DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin";
		mysql_query($sql) or die( mysql_error() );
		$co->primaryContent .= "<div>'$table' changed to UTF-8.</div>";
	}
	$shardContentArray[] = $co;
}
break;

case "g_part1": {

	$sql = 'SHOW TABLES';
	$result = mysql_query($sql) or die( mysql_error() );

	while ($row = mysql_fetch_row($result)) {
		
		$table = mysql_real_escape_string($row[0]);
		if ($table == "fhits") {
			$shardContentArray[] = $co;
			exit();
		}
		$sql = "ALTER TABLE $table DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin";
		mysql_query($sql) or die( mysql_error() );
		$co->primaryContent .= "<div>\"$table\" changed to UTF-8.</div>";
	}
	$shardContentArray[] = $co;
}
break;

case "g_part2": {

	$sql = 'SHOW TABLES';
	$result = mysql_query($sql) or die( mysql_error() );

	while ($row = mysql_fetch_row($result)) {
		
		$table = mysql_real_escape_string($row[0]);
		if ($table == "fhits") {
			$sql = "ALTER TABLE $table DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin";
			mysql_query($sql) or die( mysql_error() );
			$co->primaryContent .= "<div>$table changed to UTF-8.</div>";
		}
	}
	$shardContentArray[] = $co;
}
break;

case "g_part3": {

	$sql = 'SHOW TABLES';
	$result = mysql_query($sql) or die( mysql_error() );
	$noaction = true;
	while ($row = mysql_fetch_row($result)) {
		
		$table = mysql_real_escape_string($row[0]);
		if (!$noaction && $table != "forum_posts" && $table != "postratings") {
			$sql = "ALTER TABLE $table DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin";
			mysql_query($sql) or die(mysql_error_override($sql));
			$co->primaryContent .= "<div>$table changed to UTF-8.</div>";
		}
		if ($table == "fhits")
			$noaction = false;
	}
	$shardContentArray[] = $co;
}
break;

case "g_part4": {

	$sql = 'SHOW TABLES';
	$result = mysql_query($sql) or die( mysql_error() );
	$noaction = true;
	while ($row = mysql_fetch_row($result)) {
		
		$table = mysql_real_escape_string($row[0]);
		if (!$noaction && $table == "forum_posts") {
			$sql = "ALTER TABLE $table DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin";
			mysql_query($sql) or die( mysql_error() );
			$co->primaryContent .= "<div>$table changed to UTF-8.</div>";
		}
		if ($table == "fhits")
			$noaction = false;
	}
	$shardContentArray[] = $co;
}
break;

case "g_part5": {

	$sql = 'SHOW TABLES';
	$result = mysql_query($sql) or die( mysql_error() );
	$noaction = true;
	while ($row = mysql_fetch_row($result)) {
		
		$table = mysql_real_escape_string($row[0]);
		if (!$noaction && $table == "postratings") {
			$sql = "ALTER TABLE $table DEFAULT CHARACTER SET utf8 COLLATE utf8_bin, CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin";
			mysql_query($sql) or die( mysql_error() );
			$co->primaryContent .= "<div>$table changed to UTF-8.</div>";
		}
		if ($table == "fhits")
			$noaction = false;
	}
	$shardContentArray[] = $co;
}
break;


endswitch;

?>