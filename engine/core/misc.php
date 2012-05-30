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

    //misc.php
    
    
//
// This function helps protect against harmful $_GET vars
//
function make_var_safe( $var ) {
		if( get_magic_quotes_gpc() != 1 )
        	return addslashes(trim($var));
        else
        	return trim($var);
    }
    
//
// Validates vars that need to be numbers
//    
function make_num_safe( $var ) {
        if( is_numeric( $var ))
            return $var;
        else
            return 1;  // Should we be more elaborate.  '1' may not exist either.
    }

//
// Subvert XSS attacks
//
function xss_clean ($var) {
	$var = preg_replace("/^javascript:/i","",$var);
	//$var = str_replace("javascript", "java script", $var);
	return $var;
}  
    
//
// This function was built for use with the content shard.  It takes an article
// title and makes it URL friendly ('GeForce 6800 Review' becomes 'GeForce_6800_Review').
// It's intended to help create more user-friendly URLs, for use with slickurls (see index.php).
//
function make_url_friendly($text ) {
        $url = htmlspecialchars($text , ENT_QUOTES );
        $url = explode(' ' , $url );
        $underscored = implode('_' , $url );
        return $underscored;
    }

function get_numeric_month($month ) {
        if (strcmp($month, 'January' ) == 0 )
            return 1;
        if (strcmp($month, 'February' ) == 0 )
            return 2;
        if (strcmp($month, 'March' ) == 0 )
            return 3;
        if (strcmp($month, 'April' ) == 0 )
            return 4;
        if (strcmp($month, 'May' ) == 0 )
            return 5;
        if (strcmp($month, 'June' ) == 0 )
            return 6;
        if (strcmp($month, 'July' ) == 0 )
            return 7;
        if (strcmp($month, 'August' ) == 0 )
            return 8;
        if (strcmp($month, 'September' ) == 0 )
            return 9;
        if (strcmp($month, 'October' ) == 0 )
            return 10;
        if (strcmp($month, 'November' ) == 0 )
            return 11;
        if (strcmp($month, 'December' ) == 0 )
            return 12;
    }

function days_in_month($month, $year) {
        if (checkdate($month, 31, $year)) return 31;
        if (checkdate($month, 30, $year)) return 30;
        if (checkdate($month, 29, $year)) return 29;
        if (checkdate($month, 28, $year)) return 28;
        return 0; // error
    }

function logHit($username, $shard, $action, $siteSettings) {
        //initialize strings to nothing.  maybe null is better? also i think they are set by default.
        $id = "";
        $addr = "";
        $refer = "";
         
         
         
        // if indices exist set the variables
        if (array_key_exists('HTTP_REFERER' , $_SERVER ) == TRUE )
            $refer = $_SERVER['HTTP_REFERER'];
        if (array_key_exists('ID' , $_REQUEST ) == TRUE )
            $id = make_num_safe($_REQUEST[ 'ID' ]);
        if (array_key_exists('REMOTE_ADDR' , $_SERVER ) == TRUE )
        {
            $addr = $_SERVER[ 'REMOTE_ADDR' ];
            $addr = $addr . " (" . gethostbyaddr($addr ) . ")";
        }
        
        $staticTrackKey = 'b6'.$siteSettings['db'].'static';
        
        if (array_key_exists($staticTrackKey, $_REQUEST) == TRUE)
        	$staticTrack = make_var_safe($_REQUEST[$staticTrackKey]);
        else
        	$staticTrack = "static-" . $_SERVER['REMOTE_ADDR'] ."-" . date("d-m-Y");
        
        	
        	
        // insert hit tracking information into the database
        $result = mf_query("insert into hittracker (date, user, referer, shard, action, objectID, userIP, static)
            VALUES
            ('".time()."', '$username', '$refer', '$shard', '$action', '$id', '$addr', '$staticTrack')") or die(mysql_error());
            

        	

    }
    
function calculateRank($userID,$maxrating="",$maxposts="",$maxquoted="") {
	   	$prt = false;
	   	if( array_key_exists( 'pT', $_REQUEST) == TRUE )
	   	{
	   	   if (make_var_safe($_REQUEST['pT']) == "true")
	   	       $prt = true;
	   	}
	    
	  	
	  	if (is_numeric($userID))
	  	{
			$s = "select * from forum_user_nri where userID=$userID limit 1";
			$u = mf_query($s);
			$u = mysql_fetch_assoc($u);
			
			
			if (!$maxrating) {
				$pop = mf_query("select MAX(times_quoted) as maxquoted, MAX(num_posts) as maxposts, MAX(cum_post_rating) as maxrating from forum_user_nri");
				$pop = mysql_fetch_assoc($pop);
			}
			else {
				$pop['maxrating'] = $maxrating;
				$pop['maxposts'] = $maxposts;
				$pop['maxquoted'] = $maxquoted;
			}
			if ($pop['maxrating'] == 0)
				$pop['maxrating'] = 1;
				
			if ($pop['maxposts'] == 0)
				$pop['maxposts'] = 1;
				
			if ($pop['maxquoted'] == 0)
				$pop['maxquoted'] = 1;

			$thisPostsRaw = ($u['num_posts'] / $pop['maxposts']) * 3;			
			$thisRatingRaw = ($u['cum_post_rating'] / $pop['maxrating']) * 30;
			$thisQuotedRaw = ($u['times_quoted'] / $pop['maxquoted']) * 10;
			
			$thisTotalRaw = $thisPostsRaw + $thisRatingRaw + $thisQuotedRaw;
			
			
			// Update user's raw score
			$update = mf_query("update forum_user_nri set rawrating=$thisTotalRaw where userID=$userID limit 1");
			
			// Calculate Normative Ranking Index
			$pop = mf_query("select AVG(rawrating) as avgrating, STD(rawrating) as stdrating from forum_user_nri where rawrating > 0");
			$pop = mysql_fetch_assoc($pop);
			
			if ($pop['stdrating'] !=0)
				$thisNRI = ($thisTotalRaw - $pop['avgrating']) / $pop['stdrating'];
			else
				$thisNRI = 0;
				
			$zScore = $thisNRI;
				
			$thisNRI = poz($thisNRI);
			$thisNRI = $thisNRI * $thisNRI;
			
			if ($prt)
				print "Posts :$u[num_posts] | Rating: $u[cum_post_rating] | totalRaw: $thisTotalRaw | zScore: $zScore | nri: $thisNRI <br/>";
				
			return $thisNRI;
			
			
		}
		else
			return 0;
	  	
	  	
   }

function update_rank_optional($userID,$username) {
		if (is_numeric($userID) && $username) {
			$username = make_var_safe($username);
			$datenow = time();
			$total_threads = mf_query("select ID from forum_topics where userID='$userID'");
			$total_threads = mysql_num_rows($total_threads);
			$total_posmod = mf_query("select ID from postratings where user=\"$username\" and rating > 0");
			$total_posmod = mysql_num_rows($total_posmod);
			$total_negmod = mf_query("select ID from postratings where user=\"$username\" and rating < 0");
			$total_negmod = mysql_num_rows($total_negmod);
			$total_received_posmod = mf_query("select ID from postratings where modeduserID='$userID' and rating > 0");
			$total_received_posmod = mysql_num_rows($total_received_posmod);
			$total_received_negmod = mf_query("select ID from postratings where modeduserID='$userID' and rating < 0");
			$total_received_negmod = mysql_num_rows($total_received_negmod);

			$cum_post_rating = mf_query("SELECT SUM(rating) AS cum_post_rating FROM postratings WHERE modeduserID='$userID' < 0");
			$cum_post_rating = mysql_fetch_assoc($cum_post_rating);
			
			mf_query("UPDATE forum_user_nri SET num_threads='$total_threads', num_posmods='$total_posmod', num_negmods='$total_negmod', num_received_posmods='$total_received_posmod', num_received_negmods='$total_received_negmod', cum_post_rating = '$cum_post_rating[cum_post_rating]' WHERE userID='$userID' limit 1");
			
			calculateRank($userID);
		}
	
	}

function update_forum_usernri() {
	
		$datenow = time();
		$datemin = $datenow - (3600);
		$query = mf_query("select forum_user_nri.userID, forum_user_nri.name
						from forum_user_nri 
						join users ON forum_user_nri.userID = users.ID 
						where forum_user_nri.lastupdate <= users.lat");
		while ($row = mysql_fetch_assoc($query)) {
			update_rank_optional($row['userID'],$row['name']);
		}
	}

/*  POZ  --  probability of normal z value
    Adapted from a polynomial approximation in:
                Ibbetson D, Algorithm 209
                Collected Algorithms of the CACM 1963 p. 616
        Note:
                This routine has six digit accuracy, so it is only useful for absolute
                z values <= 6.  For z values > to 6.0, poz() returns 0.0.
*/
function poz($z) {
		
		    $Z_MAX = 6;                    // Maximum ±z value
    		$ROUND_FLOAT = 6;              // Decimal places to round numbers
        
        
        if ($z == 0.0) {
            $x = 0.0;
        } else {
            $y = 0.5 * abs($z);
            if ($y > ($Z_MAX * 0.5)) {
                $x = 1.0;
            } else if ($y < 1.0) {
                $w = $y * $y;
                $x = ((((((((0.000124818987 * $w
                         - 0.001075204047) * $w + 0.005198775019) * $w
                         - 0.019198292004) * $w + 0.059054035642) * $w
                         - 0.151968751364) * $w + 0.319152932694) * $w
                         - 0.531923007300) * $w + 0.797884560593) * $y * 2.0;
            } else {
                $y -= 2.0;
                $x = (((((((((((((-0.000045255659 * $y
                               + 0.000152529290) * $y - 0.000019538132) * $y
                               - 0.000676904986) * $y + 0.001390604284) * $y
                               - 0.000794620820) * $y - 0.002034254874) * $y
                               + 0.006549791214) * $y - 0.010557625006) * $y
                               + 0.011630447319) * $y - 0.009279453341) * $y
                               + 0.005353579108) * $y - 0.002141268741) * $y
                               + 0.000535310849) * $y + 0.999936657524;
            }
        }
        return $z > 0.0 ? (($x + 1.0) * 0.5) : ((1.0 - $x) * 0.5);
    }
    
function average($array){
	if (is_array($array))
	{
   		$sum  = array_sum($array);
   		$count = count($array);
   		if ($count != 0)
   			return $sum/$count;
   		else
   			return false;
	}
	else
		return false;
}

//The average function can be use independantly but the deviation function uses the average function.
function stddev ($array){
  
   $avg = average($array);
   foreach ($array as $value) {
       $variance[] = pow($value-$avg, 2);
   }
   $deviation = sqrt(average($variance));
   return $deviation;
}    

function offsetTimezone($timeVar) {
	if (array_key_exists("mf_timezone" , $_REQUEST ) == TRUE )
    {
		$mf_timezone = $_REQUEST["mf_timezone"];
		
		if (is_numeric($mf_timezone))
		{
			$mf_timezone = $mf_timezone * -1;
			
			$difference = $mf_timezone - ( date("Z") / 3600 );
			
			
			if ($difference != 0)
				$timeVar = $timeVar + ($difference * 3600);			
			
		}        
	}
	
	
   
	return $timeVar;
	
}

function sendToSyncDB($input) {
//	print "$LANG[ERROR_DATABASE]...  $input <br/><br/>$LANG[ERROR]: " . mysql_error();
}

function mysql_error_override($input="") {
	global $LANG;
	global $siteSettings;
	
	if (!isset($LANG['ERROR'])) {
		$LANG['ERROR_DATABASE'] = "Database error";
		$LANG['ERROR_REQUEST'] = "Error";
	}

	$g = "engine/grafts/" . $siteSettings['graft'] . "/";
	$versioncorecss = file_get_contents("engine/grafts/core-styles/core-styles-version.txt");
	$versionc = file_get_contents($g."graft-version.txt");
	$rss = "";
	include("engine/grafts/head.php");

	print "$head_file
            <style type=\"text/css\" media=\"screen\">
            @import \"engine/grafts/core-styles/core-styles-$versioncorecss.css\";
            @import \"" . $g . "graft-$versionc.css\";
            </style>";

	print "</head>
            <body>
            <div id=\"header2\">
            <div id=\"image_header\"></div>
            </div>
            <div id=\"coin_left\">
            </div>
            <div id=\"coin_right\">
            </div>
            <div id=\"bandeau_top\">
            </div>
			<div id='screenCover'></div>";
	
	print "<div id='page'><div style='margin-top:50px;margin-left:16px;margin-right:16px;display:block;padding:8px;background-color:#FFFFFF;border:2px solid silver;'><h2>$LANG[ERROR_DATABASE]:</h2>  " . mysql_error() . "<br/><br/><b>$LANG[ERROR_REQUEST]:</b><br/>$input</div></div>";

	print "</body></html>";
}

function utf8Urldecode($str){
	if(is_array($str)){
		foreach($str as $key => $val)
			$str[$key] = utf8Urldecode($val);
		return $str;
	}
			
        $res = '';
        $i = 0;
        $max = strlen($str) - 6;
        while ($i <= $max) {
                $character = $str[$i];
                if ($character == '%' && $str[$i + 1] == 'u') {
                        $value = hexdec(substr($str, $i + 2, 4));
                        $i += 6;
                        if ($value < 0x0080) // 1 byte: 0xxxxxxx
                                $character = chr($value);
                        else if ($value < 0x0800) // 2 bytes: 110xxxxx 10xxxxxx
                                $character =
                                        chr((($value & 0x07c0) >> 6) | 0xc0)
                                        . chr(($value & 0x3f) | 0x80);
                        else // 3 bytes: 1110xxxx 10xxxxxx 10xxxxxx
                                $character =
                                chr((($value & 0xf000) >> 12) | 0xe0)
                                . chr((($value & 0x0fc0) >> 6) | 0x80)
                                . chr(($value & 0x3f) | 0x80);
                } else
                        $i++;
                $res .= $character;
        }

        return $res . substr($str, $i);
}
/*
  This code is from http://detectmobilebrowsers.mobi/ - please do not republish it without due credit and hyperlink to http://detectmobilebrowsers.mobi
  For help generating the function call visit http://detectmobilebrowsers.mobi/ and use the function generator.
  Published by Andy Moore - .mobi certified mobile web developer - http://andymoore.info/
  This code is free to download and use on non-profit websites, if your website makes a profit or you require support using this code please upgrade.
  Upgrade for use on commercial websites and support: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=1064282
*/
function mobile_device_detect($iphone=true,$ipad=true,$android=true,$opera=true,$blackberry=true,$palm=true,$windows=true,$mobileredirect=false,$desktopredirect=false){

  $mobile_browser   = false; // set mobile browser as false till we can prove otherwise
  $user_agent       = $_SERVER['HTTP_USER_AGENT']; // get the user agent value - this should be cleaned to ensure no nefarious input gets executed
  $accept           = $_SERVER['HTTP_ACCEPT']; // get the content accept value - this should be cleaned to ensure no nefarious input gets executed

  switch(true){ // using a switch against the following statements which could return true is more efficient than the previous method of using if statements

    case (preg_match('/ipad/i',$user_agent)); // we find the word ipad in the user agent
      $mobile_browser = $ipad; // mobile browser is either true or false depending on the setting of ipad when calling the function
      $status = 'Apple iPad';
      if(substr($ipad,0,4)=='http'){ // does the value of ipad resemble a url
        $mobileredirect = $ipad; // set the mobile redirect url to the url value stored in the ipad value
      } // ends the if for ipad being a url
    break; // break out and skip the rest if we've had a match on the ipad // this goes before the iphone to catch it else it would return on the iphone instead

    case (preg_match('/ipod/i',$user_agent)||preg_match('/iphone/i',$user_agent)); // we find the words iphone or ipod in the user agent
      $mobile_browser = $iphone; // mobile browser is either true or false depending on the setting of iphone when calling the function
      $status = 'Apple';
      if(substr($iphone,0,4)=='http'){ // does the value of iphone resemble a url
        $mobileredirect = $iphone; // set the mobile redirect url to the url value stored in the iphone value
      } // ends the if for iphone being a url
    break; // break out and skip the rest if we've had a match on the iphone or ipod

    case (preg_match('/android/i',$user_agent));  // we find android in the user agent
      $mobile_browser = $android; // mobile browser is either true or false depending on the setting of android when calling the function
      $status = 'Android';
      if(substr($android,0,4)=='http'){ // does the value of android resemble a url
        $mobileredirect = $android; // set the mobile redirect url to the url value stored in the android value
      } // ends the if for android being a url
    break; // break out and skip the rest if we've had a match on android

    case (preg_match('/opera mini/i',$user_agent)); // we find opera mini in the user agent
      $mobile_browser = $opera; // mobile browser is either true or false depending on the setting of opera when calling the function
      $status = 'Opera';
      if(substr($opera,0,4)=='http'){ // does the value of opera resemble a rul
        $mobileredirect = $opera; // set the mobile redirect url to the url value stored in the opera value
      } // ends the if for opera being a url 
    break; // break out and skip the rest if we've had a match on opera

    case (preg_match('/blackberry/i',$user_agent)); // we find blackberry in the user agent
      $mobile_browser = $blackberry; // mobile browser is either true or false depending on the setting of blackberry when calling the function
      $status = 'Blackberry';
      if(substr($blackberry,0,4)=='http'){ // does the value of blackberry resemble a rul
        $mobileredirect = $blackberry; // set the mobile redirect url to the url value stored in the blackberry value
      } // ends the if for blackberry being a url 
    break; // break out and skip the rest if we've had a match on blackberry

    case (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i',$user_agent)); // we find palm os in the user agent - the i at the end makes it case insensitive
      $mobile_browser = $palm; // mobile browser is either true or false depending on the setting of palm when calling the function
      $status = 'Palm';
      if(substr($palm,0,4)=='http'){ // does the value of palm resemble a rul
        $mobileredirect = $palm; // set the mobile redirect url to the url value stored in the palm value
      } // ends the if for palm being a url 
    break; // break out and skip the rest if we've had a match on palm os

    case (preg_match('/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i',$user_agent)); // we find windows mobile in the user agent - the i at the end makes it case insensitive
      $mobile_browser = $windows; // mobile browser is either true or false depending on the setting of windows when calling the function
      $status = 'Windows Smartphone';
      if(substr($windows,0,4)=='http'){ // does the value of windows resemble a rul
        $mobileredirect = $windows; // set the mobile redirect url to the url value stored in the windows value
      } // ends the if for windows being a url 
    break; // break out and skip the rest if we've had a match on windows

    case (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i',$user_agent)); // check if any of the values listed create a match on the user agent - these are some of the most common terms used in agents to identify them as being mobile devices - the i at the end makes it case insensitive
      $mobile_browser = true; // set mobile browser to true
      $status = 'Mobile matched on piped preg_match';
    break; // break out and skip the rest if we've preg_match on the user agent returned true 

    case ((strpos($accept,'text/vnd.wap.wml')>0)||(strpos($accept,'application/vnd.wap.xhtml+xml')>0)); // is the device showing signs of support for text/vnd.wap.wml or application/vnd.wap.xhtml+xml
      $mobile_browser = true; // set mobile browser to true
      $status = 'Mobile matched on content accept header';
    break; // break out and skip the rest if we've had a match on the content accept headers

    case (isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE'])); // is the device giving us a HTTP_X_WAP_PROFILE or HTTP_PROFILE header - only mobile devices would do this
      $mobile_browser = true; // set mobile browser to true
      $status = 'Mobile matched on profile headers being set';
    break; // break out and skip the final step if we've had a return true on the mobile specfic headers

    case (in_array(strtolower(substr($user_agent,0,4)),array('1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex','anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai','emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac','iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno','m1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21','mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki','nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600','raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams','sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-','telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','hiba'=>'hiba','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu','x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java','jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-','send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-',))); // check against a list of trimmed user agents to see if we find a match
      $mobile_browser = true; // set mobile browser to true
      $status = 'Mobile matched on in_array';
    break; // break even though it's the last statement in the switch so there's nothing to break away from but it seems better to include it than exclude it

    default;
      $mobile_browser = false; // set mobile browser to false
      $status = 'Desktop / full capability browser';
    break; // break even though it's the last statement in the switch so there's nothing to break away from but it seems better to include it than exclude it

  } // ends the switch 

  // tell adaptation services (transcoders and proxies) to not alter the content based on user agent as it's already being managed by this script, some of them suck though and will disregard this....
	// header('Cache-Control: no-transform'); // http://mobiforge.com/developing/story/setting-http-headers-advise-transcoding-proxies
	// header('Vary: User-Agent, Accept'); // http://mobiforge.com/developing/story/setting-http-headers-advise-transcoding-proxies

  // if redirect (either the value of the mobile or desktop redirect depending on the value of $mobile_browser) is true redirect else we return the status of $mobile_browser
  if($redirect = ($mobile_browser==true) ? $mobileredirect : $desktopredirect){
    header('Location: '.$redirect); // redirect to the right url for this device
    exit;
  }else{ 
		// a couple of folkas have asked about the status - that's there to help you debug and understand what the script is doing
		if($mobile_browser==''){
    return $mobile_browser; // will return either true or false 
		}else{
			return array($mobile_browser,$status); // is a mobile so we are returning an array ['0'] is true ['1'] is the $status value
		}
  }

} // ends function mobile_device_detect

function load_picture($dataline,$sens="") {
	global $CURRENTUSERID;

	$dataLine = explode("@@::AA::@@", $dataline);
	$picture = make_var_safe($dataLine[0]);
	$albumID = make_num_safe($dataLine[1]);
	$pictID = make_num_safe($dataLine[2]);
	$threadID = make_num_safe($dataLine[3]);
	$sens = make_var_safe($sens);
	$sens2 = ">";
	if ($sens) $sens2 = "<";

	$query = mf_query("SELECT * FROM pictures WHERE albumID = '$albumID' AND ID $sens2 '$pictID' ORDER BY date_added $sens limit 1");
	if ($row = mysql_fetch_assoc($query))
		$retstr = $row['name'];
	else {
		$query = mf_query("SELECT * FROM pictures WHERE albumID = '$albumID' ORDER BY ID $sens limit 1");
		if ($row = mysql_fetch_assoc($query))
			$retstr = $row['name'];
	}
	$desc = $row['description'];
	$width = $row['width'];
	if ($width == 0) $width = 700;
	
	$verify = false;
	$query_album = mf_query("SELECT * FROM albums WHERE ID = '$albumID' limit 1");
	$album = mysql_fetch_assoc($query_album);
	$query_user = mf_query("SELECT ID FROM albums_users WHERE albumID = '$albumID' and userID ='$CURRENTUSERID' limit 1");
	if ($row['userID'] == $CURRENTUSERID || $album['public'])
		$verify = true;
	else if (mysql_fetch_assoc($query_user))
		$verify = true;
	else {
		$queryv = mf_query("SELECT ID FROM albums_topics WHERE albumID = '$albumID' AND threadID = '$threadID' and ((threadID IN (select threadID from fhits where threadID = '$threadID' AND userID = '$CURRENTUSERID')) OR (threadID IN (select ID FROM forum_topics WHERE pthread = '0' AND ID = '$threadID' )))");
		if ($queryv = mysql_fetch_assoc($queryv))
			$verify = true;
	}
	
	if ($verify == true) {
		$retstr .= "::@@::".$desc."::@@::".$width."::@@::".$row['height']."::@@::".$albumID."::@@::".$row['ID'];
		return $retstr;
	}
	else
		return "::@@::ACCÈS REFUSÉ";
}

function load_album($albumID,$threadID="") {
	global $CURRENTUSERID;
	global $LANG;
	$verify = false;
	$imagerefused = "<div style='display:inline-block;padding:2px;border:1px solid silver;'>$LANG[PICTURES_ALBUM_REFUSED]</div>";

	if (!is_numeric($albumID)) {
		$title = "";
		$imagelist = $imagerefused;
	}
	else {
	$albumID = make_num_safe($albumID);
	$query_album = mf_query("SELECT * FROM albums WHERE ID = '$albumID' limit 1");
	$album = mysql_fetch_assoc($query_album);
	$query_user = mf_query("SELECT ID FROM albums_users WHERE albumID = '$albumID' and userID ='$CURRENTUSERID' limit 1");

	if (!$album['ID'])
		$imagelist = $imagerefused;

	if ($album['userID'] == $CURRENTUSERID || $album['public'])
		$verify = true;
	else if (mysql_fetch_assoc($query_user))
		$verify = true;
	else {
		$queryv = mf_query("SELECT ID FROM albums_topics WHERE albumID = '$albumID' AND threadID = '$threadID' AND ((threadID IN (select threadID from fhits where threadID = '$threadID' AND userID = '$CURRENTUSERID')) OR (threadID IN (select ID FROM forum_topics WHERE pthread = '0' AND ID = '$threadID' )))");
		if ($queryv = mysql_fetch_assoc($queryv))
			$verify = true;
	}
	
	if ($verify == true) {

		$imagelist = "";
		$title = "";
		$cover_img = "";
		$i = 0;
		$query_image = mf_query("SELECT * FROM pictures WHERE albumID = '$albumID' ORDER BY ID DESC");
		while ($images = mysql_fetch_assoc($query_image)) {
			$desc = $images['description'];
			$width = $images['width'];
			if ($album['coverID'] == $images['ID'])
				$cover_img = "<img src='$images[name_thumb]' alt='' title=\"$images[description]\" onclick=\"view_picture('$images[name]','$desc','$width','$images[height]','$album[ID]','$images[ID]'); return false;\" style='vertical-align:middle;margin-right:3px;' />";
			$i ++;

			$imagelist .= "<img src='$images[name_thumb]' alt='' title=\"$images[description]\" onclick=\"view_picture('$images[name]','$desc','$width','$images[height]','$album[ID]','$images[ID]'); return false;\" style='vertical-align:middle;margin-right:3px;margin-bottom:3px;' />";
		}
		$title = "<div style='border:2px solid silver;padding:4px;margin-bottom:8px;'><div style='display:inline-block;'>$cover_img</div><div style='display:inline-block;vertical-align:middle;'><div style='font-size:2em;'>$album[name]</div><div style='font-size:1.2em;'>$album[description]</div><div style='font-size:1.2em;'>$i $LANG[PICTURES_ALBUM_NUMBER]</div></div><div style='margin-right:10px;margin-left:10px;margin-top:4px;border-top:1px dashed silver;height:6px;'></div><div style='text-align:center;'>";
	}
	else
		$imagelist = $imagerefused;
	}
	return $title.$imagelist."</div></div>";
}

function load_pict($pictureID,$threadID="",$mini=false) {
	global $CURRENTUSERID;
	global $LANG;
	$verify = false;
	$imagerefused = "<div style='display:inline-block;padding:2px;border:1px solid silver;'>$LANG[PICTURES_REFUSED]</div>";
	
	if (!is_numeric($pictureID)) {
		$title = "";
		$imagelist = $imagerefused;
	}
	else {
		$pictureID = make_num_safe($pictureID);
		$query_picture = mf_query("SELECT pictures.*, albums.public FROM pictures JOIN albums ON albums.ID = pictures.albumID WHERE pictures.ID = '$pictureID' LIMIT 1");
		$picture = mysql_fetch_assoc($query_picture);
		$albumID = $picture['albumID'];
		$query_user = mf_query("SELECT ID FROM albums_users WHERE albumID = '$albumID' and userID ='$CURRENTUSERID' limit 1");

		if (!$picture['ID'])
			$imagelist = $imagerefused;

		if ($picture['userID'] == $CURRENTUSERID || $picture['public'])
			$verify = true;
		else if (mysql_fetch_assoc($query_user))
			$verify = true;
		else {
			$queryv = mf_query("SELECT ID FROM albums_topics WHERE albumID = '$albumID' AND threadID = '$threadID' AND ((threadID IN (select threadID from fhits where threadID = '$threadID' AND userID = '$CURRENTUSERID')) OR (threadID IN (select ID FROM forum_topics WHERE pthread = '0' AND ID = '$threadID' )))");
			if ($queryv = mysql_fetch_assoc($queryv))
				$verify = true;
		}
		$picture_name = $picture['name'];
		$onclick = "onclick=\"view_picture('$picture[name]','$picture[description]','$picture[width]','$picture[height]','','$picture[ID]'); return false;\"";
		if ($mini) {
			$picture_name = $picture['name_thumb'];
			$onclick = "";
		}
		if ($verify == true) {
			$imagelist = "<img src='$picture_name' alt='' title=\"$picture[description]\" $onclick style='vertical-align:middle;margin-right:3px;' />";
		}
		else
			$imagelist = $imagerefused;
	}
	return $imagelist;
}

function make_link($shard,$str="",$str2="") {
	global $siteSettings;
	global $CURRENTUSERAJAX;

	if ($siteSettings['mod_rewrite']) {
		$retstr = $shard.".html";
		if ($str2 && ($CURRENTUSERAJAX || !$str))
			$retstr .= $str2;
		else
			$retstr .= $str;
	}
	else {
		$retstr = "index.php?shard=".$shard;
		if ($str2 && ($CURRENTUSERAJAX || !$str))
			$retstr .= $str2;
		else
			$retstr .= $str;
	}

	return $retstr;
}

function get_facebook_cookie($app_id, $application_secret) {
  $args = array();
  parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
  ksort($args);
  $payload = '';
  foreach ($args as $key => $value) {
    if ($key != 'sig') {
      $payload .= $key . '=' . $value;
    }
  }
  if (md5($payload . $application_secret) != $args['sig']) {
    return null;
  }
  return $args;
}

function display_tos() {
	global $siteSettings;
	global $LANG;

	$JSS2 = mf_query ("SELECT body from forum_topics WHERE ID = '$siteSettings[rulesthread]' limit 1");
	$JSS2 = mysql_fetch_assoc($JSS2);
	$JSS2 = format_post($JSS2['body'], true);
	$JSS2 = str_replace("\r", "<br />", $JSS2);
	$JSS2 = str_replace("\n", "<br />", $JSS2);
	$JSS2 = str_replace("\t", "<br />", $JSS2);
	$JSS2 = str_replace("\"", "", $JSS2);
	$rules = $JSS2;

	return $rules;
}

function count_tags() {

	$query = mf_query("SELECT tag,total_use FROM tags");
	while ($row = mysql_fetch_assoc($query)) {
		$query_f = mf_query("SELECT COUNT(forum_tags.threadID) AS total_thread 
								FROM forum_tags 
								JOIN forum_topics ON (forum_topics.ID = forum_tags.threadID AND forum_topics.pthread = '0') 
								WHERE forum_tags.tag = \"$row[tag]\"");
		$row_f = mysql_fetch_assoc($query_f);
		if ($row_f['total_thread'] != $row['total_use']) {
			mf_query("UPDATE tags SET total_use = '$row_f[total_thread]' WHERE tag = \"$row[tag]\"");
		}
		$date_min = time() - (3600 * 24 * 7);
		$query_f = mf_query("SELECT COUNT(forum_tags.threadID) AS total_thread 
								FROM forum_tags 
								JOIN forum_topics ON (forum_topics.ID = forum_tags.threadID AND forum_topics.pthread = '0') 
								WHERE 
									forum_tags.tag = \"$row[tag]\"
									AND forum_topics.date > '$date_min'");
		$row_f = mysql_fetch_assoc($query_f);
		mf_query("UPDATE tags SET total_use_week = '$row_f[total_thread]' WHERE tag = \"$row[tag]\"");
		$date_min = time() - (3600 * 24 * 30);
		$query_f = mf_query("SELECT COUNT(forum_tags.threadID) AS total_thread 
								FROM forum_tags 
								JOIN forum_topics ON (forum_topics.ID = forum_tags.threadID AND forum_topics.pthread = '0') 
								WHERE 
									forum_tags.tag = \"$row[tag]\"
									AND forum_topics.date > '$date_min'");
		$row_f = mysql_fetch_assoc($query_f);
		mf_query("UPDATE tags SET total_use_month = '$row_f[total_thread]' WHERE tag = \"$row[tag]\"");
		$date_min = time() - (3600 * 24 * 365);
		$query_f = mf_query("SELECT COUNT(forum_tags.threadID) AS total_thread 
								FROM forum_tags 
								JOIN forum_topics ON (forum_topics.ID = forum_tags.threadID AND forum_topics.pthread = '0') 
								WHERE 
									forum_tags.tag = \"$row[tag]\"
									AND forum_topics.date > '$date_min'");
		$row_f = mysql_fetch_assoc($query_f);
		mf_query("UPDATE tags SET total_use_year = '$row_f[total_thread]' WHERE tag = \"$row[tag]\"");
	}
}

function load_tags() {
	global $LANG;

	$retstr = "<div style='display:inline-block;' id='main_tag_cloud' class='".time()."'><span id='searchTag_cache' style='display:none;'></span><div style='display:table;'>";
	$query = mf_query("SELECT ID, tag, total_use_week AS total FROM tags WHERE total_use_week > 0 ORDER BY total_use_week DESC");
	$retstr .= "<div class='row'><div class='cell'><div class='cloud_title'>$LANG[TAGS_7_DAYS]</div></div>
				<div class='cell'><div class='cloud_title'>$LANG[TAGS_30_DAYS]</div></div></div>";
	$retstr .= "<div class='row'><div class='cloud'><div style='margin-bottom:8px;'><input type='text' onkeyup=\"searchTag('1');\" class='bselect' id='searchTag1' /></div>".load_tag_query($query,"1")."</div>";
	$query = mf_query("SELECT ID, tag, total_use_month AS total FROM tags WHERE total_use_month > 0 ORDER BY total_use_month DESC");
	$retstr .= "<div class='cloud'><div style='margin-bottom:8px;'><input type='text' onkeyup=\"searchTag('2');\" class='bselect' id='searchTag2' /></div>".load_tag_query($query,"2")."</div></div>";
	$query = mf_query("SELECT ID, tag, total_use_year AS total FROM tags WHERE total_use_year > 0 ORDER BY total_use_year DESC");
	$retstr .= "<div class='row'><div class='cell'><div class='cloud_title'>$LANG[TAGS_12_MONTHS]</div></div>
				<div class='cell'><div class='cloud_title'>$LANG[TAGS_ALL]</div></div></div>";
	$retstr .= "<div class='row'><div class='cloud'><div style='margin-bottom:8px;'><input type='text' onkeyup=\"searchTag('3');\" class='bselect' id='searchTag3' /></div>".load_tag_query($query,"3")."</div>";
	$query = mf_query("SELECT ID, tag, total_use AS total FROM tags WHERE total_use > 0 ORDER BY total_use DESC");
	$retstr .= "<div class='cloud'><div style='margin-bottom:8px;'><input type='text' onkeyup=\"searchTag('4');\" class='bselect' id='searchTag4' /></div>".load_tag_query($query,"4")."</div></div>";
	$retstr .= "</div></div>";

	$file = "html/tag_cloud.html";
	$wf = fopen($file,"w");
	fwrite($wf,$retstr);
	fclose($wf);

	return $retstr;
}

function load_tag_query($query,$type) {
		$tag_size = 2;
		$retstr = "";
		$total_use = 2.5;
		$tag_ital = "normal";
		$lig_y = 100;
		$lig_x = 0;
		$lig_max_x = 1;
		$lig_min_x = -1;
		$sens_h = "right";
		$sens_v = "down";
		$i = 0;
		while ($row = mysql_fetch_assoc($query)) {
			$i ++;
			if ($total_use != $row['total']) {
				if ($tag_size > 0.3)
					$tag_size = $tag_size - 0.1;
				$total_use = $row['total'];
			}
			if ($tag_ital == "italic")
				$tag_ital = "normal";
			else
				$tag_ital = "italic";
			$tagdisplay = $row['tag'];
			if (strlen($tagdisplay) > 20)
				$tagdisplay = substr($tagdisplay,0,20)."[...]";
			$tag = "<div id='tag_".$type."_$row[ID]' onmouseover=\"return up_tag('$row[ID]','$type');\" onmouseout=\"return down_tag_size();\" onclick=\"view_onetag('$row[ID]','$type');\" class='tag_cloud' style='font-style:".$tag_ital.";font-size:".$tag_size."em;' title=\"$row[tag]\">$tagdisplay</div>";
			if (!isset($lig[$lig_y]))
				$lig[$lig_y] = "";
			if ($sens_h == "right") {
				$lig[$lig_y] .= $tag;
				$lig_x ++;
				if ($lig_x > $lig_max_x) {
					$lig_x = $lig_x -1;
					if ($sens_v == "down")
						$lig_y ++;
					else
						$lig_y = $lig_y -1;
					if (!isset($lig[$lig_y])) {
						$lig_max_x ++;
						$sens_h = "left";
						if ($sens_v == "down")
							$sens_v == "up";
						else
							$sens_v == "down";
					}
				}
			}
			else {
				$lig[$lig_y] = $tag.$lig[$lig_y];
				$lig_x = $lig_x -1;
				if ($lig_x < $lig_min_x) {
					$lig_x ++;
					if ($sens_v == "down")
						$lig_y = $lig_y -1;
					else
						$lig_y ++;
					if (!isset($lig[$lig_y])) {
						$lig_min_x = $lig_min_x -1;
						$sens_h = "right";
						if ($sens_v == "down")
							$sens_v == "up";
						else
							$sens_v == "down";
					}
				}
			}
		}
		$i = 0;
		while ($i < 200) {
			if (isset($lig[$i]))
				$retstr .= "<div class='tag_line' style='cursor: no-drop;' onclick=\"hide_tags_click();\">".$lig[$i]."</div>";
			$i ++;
		}
		return $retstr;
}

function team_name($teamID) {

	$team = mf_query("SELECT teamName FROM teams WHERE teamID = '$teamID' LIMIT 1");
	$team = mysql_fetch_assoc($team);
	
	return $team['teamName'];
}

function isInTeam($teamID,$userID) {
	global $CURRENTUSERID;

	if ($userID != $CURRENTUSERID) {
		$level = mf_query("SELECT level FROM teams_users WHERE teamID = '$teamID' AND userID = '$userID' LIMIT 1");
		$level = mysql_fetch_assoc($level);
		return $level['level'];
	}
	else {
		global $isInTeam;
		if (isset($isInTeam[$teamID]))
			return $isInTeam[$teamID];
		else
			return false;
	}
}

function isInGroup($username, $group) {         
	global $CURRENTUSER;

	if ($username != $CURRENTUSER) {
		$checkGrp = mf_query("select ID from permissiongroups where username=\"".$username."\" and pGroup='$group' limit 1");
         
		if ($row = mysql_fetch_assoc($checkGrp))
			return true;             
		else
			return false;
	}
	else {
		global $isInGroup;
		if (isset($isInGroup[$group]))
			return true;             
		else
			return false;             
	}         
}

function verify($username, $ID, $action) {

	$group = mf_query("select permission from blog where ID=$ID");
	$group = mysql_fetch_assoc($group);
	// check if post's visibility matches a user's group membership
	if (isInGroup($username, $group["permission"])) {
		// check for individual restriction actions
		$denies = mf_query("select count(ID) as Expr1 from permissions where objectID=$ID and username=\"".$username."\" and action='$action'");
		$denies = mysql_fetch_assoc($denies);
		if ($denies["Expr1"] == 0) {
			return true;
		}
		else {
			return false;
		}
	}
	else
		return false;
}

function verify_add_to_pm($from_user,$to_user,$to_user_pmstatus) {
	if (is_numeric($from_user) && is_numeric($to_user)) {
		$friendOK = false;
		if (!$to_user_pmstatus)
			$friendOK = true;
		else if ($to_user_pmstatus == "2") {
			$friend = mf_query("SELECT ID FROM users_friends WHERE userID='$to_user' AND target_userID = '$from_user' AND friendType < 3 LIMIT 1");
			if ($friend = mysql_fetch_assoc($friend))
				$friendOK = true;
		}
		else if ($to_user_pmstatus == "1") {
			$friend = mf_query("SELECT ID FROM users_friends WHERE userID='$to_user' AND target_userID = '$from_user' AND friendType < 3 LIMIT 1");
			if ($friend = mysql_fetch_assoc($friend))
				$friendOK = true;
			else {
				$query_f = mf_query("SELECT target_userID FROM users_friends WHERE userID = '$to_user' AND friendType < 3 ");
				while ($row_f = mysql_fetch_assoc($query_f)) {
					$friend = mf_query("SELECT ID FROM users_friends WHERE userID='$row_f[target_userID]' AND target_userID = '$from_user' AND friendType < 3 LIMIT 1");
					if ($friend = mysql_fetch_assoc($friend))
						$friendOK = true;
				}	
			}
		}
		return $friendOK;
	}
}

function friendstatus($from_user,$to_user) {
	if (is_numeric($from_user) && is_numeric($to_user)) {
		$friendstatus = 0;
		$friend = mf_query("SELECT ID FROM users_friends WHERE userID='$to_user' AND target_userID = '$from_user' AND friendType < 3 LIMIT 1");
		if ($friend = mysql_fetch_assoc($friend))
			$friendstatus = 2;
		else {
			$query_f = mf_query("SELECT target_userID FROM users_friends WHERE userID = '$to_user' AND friendType < 3 ");
			while ($row_f = mysql_fetch_assoc($query_f)) {
				$friend = mf_query("SELECT ID FROM users_friends WHERE userID='$row_f[target_userID]' AND target_userID = '$from_user' AND friendType < 3 LIMIT 1");
				if ($friend = mysql_fetch_assoc($friend))
					$friendstatus = 1;
			}	
		}
		return $friendstatus;
	}
}

// Verify if the user has the last version
function checkversion($version) {
	global $LANG;

	$retstr = false;
	$server_name = $_SERVER['SERVER_NAME'];
	$newversion = mf_query("select version from version where site = \"$server_name\" order by version DESC limit 1");
	$newversion = mysql_fetch_assoc($newversion);
	if ($newversion['version'] != $version)
		$retstr = true . "::@@sys@@::$version::@@sys@@::$newversion[version]::@@sys@@::$LANG[UPDATE_AVAILABLE_1] - $LANG[UPDATE_AVAILABLE_2] <span onclick='window.location.reload();' style='cursor:pointer;'><< $LANG[UPDATE_AVAILABLE_3] >></span> $LANG[UPDATE_AVAILABLE_4]";

	return $retstr;
}

// Display a sysadmin message
function checkmessage() {
	$message = mf_query("select message from settings where 1");
	$message = mysql_fetch_assoc($message);
	$retstr = $message['message'];

	return $retstr;
}

function vMenu($menuArray,$poslr="") {
	global $shard, $action;
	print "<!-- google_ad_section_start(weight=ignore) -->";

	$sizemenu = sizeof($menuArray);
	$firstvalue = 0;
	if ($poslr == "left")
			$sizemenu = floor(sizeof($menuArray) / 2);
	else if ($poslr == "right")
		$firstvalue = floor(sizeof($menuArray) / 2);

	for ($i=$firstvalue;$i<$sizemenu;$i++) {
	
		switch( $menuArray[$i]->menuType ):

			case "search":
				for( $j=0; $j < sizeof( $menuArray[$i]->menuContentArray); $j++ ) {
					print $menuArray[$i]->menuContentArray[$j] . "<br />";
				}
			break;

			case "about":
				print ("<h2>".$menuArray[$i]->menuTitle."</h2><br />");
				for( $j=0; $j < sizeof( $menuArray[$i]->menuContentArray); $j++ ) {
					print $menuArray[$i]->menuContentArray[$j] . "<br /><br />";
				}
			break;
			
			case "nav":
				print ("<div class=\"menuContainer\">
				<ul id=\"miniflex\">");
				for( $j=0; $j < sizeof( $menuArray[$i]->menuContentArray); $j++) {	
					$selected = "";
					if (stristr( $menuArray[$i]->menuContentArray[$j] , $shard ))
						$selected = "class='miniflexSelected'";
					
					$height='5px';
					if ($selected != '') {
						$height='3px';
					}

					print ("<li ".$selected."><div style=\"padding-top:".$height."\"></div>".$menuArray[$i]->menuContentArray[$j]."</li>");
					if ($j < (sizeof( $menuArray[$i]->menuContentArray)-1)) {
						print "<li id=\"onglet_separator".$j."\"></li>";
					}
				}
				print "</ul></div>";
			break;
			
			default:
				print ("<li id=\"shardmenu_".$i."\" class=\"shard\" onmouseup=\"sendPosition();\">
				<div class=\"menuWrapper\">
				<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_topleft\"></td>
				<td class=\"shard_title\"><div style=\"padding-top:3px;padding-left:3px;\">".$menuArray[$i]->menuTitle."</div></td>
				<td class=\"shard_topright\"></td></tr></table>
				<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_left\"></td><td class=\"menuInfo\">");
				
				for ($j=0;$j<sizeof($menuArray[$i]->menuContentArray);$j++) {
					print("");
					print "<div>".$menuArray[$i]->menuContentArray[$j] . "</div>";
					print("");
				}
				
				print ("</td><td class=\"shard_right\"></td></tr></table>
				<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_bottomleft\"></td><td class=\"shard_bottom\">
				</td><td class=\"shard_bottomright\"></td></tr></table>
				</div>
				</li>");
			break;				

		endswitch;
	}
print "<!-- google_ad_section_end -->";
}

function cookieMenu($menuArray,$shard_pos,$poslr) {
	$cookie_tab = explode(",",$_COOKIE[$shard_pos]);
	
	global $shard;
	
	$ident_already = false;	
	$cookie_tab_size = sizeof($cookie_tab);
	for ($i=0;$i<$cookie_tab_size;$i++) {
		if ($cookie_tab[$i] != "") {
			$cookie_pos = (int)$cookie_tab[$i];
			if (isset($menuArray[$cookie_pos])) {
			print ("<li id=\"shardmenu_".$cookie_pos."\" class=\"shard\" onmouseup=\"sendPosition();\">
			<div class=\"menuWrapper\">
			<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_topleft\"></td>
			<td class=\"shard_title\"><div style=\"padding-top:3px;padding-left:3px;\">".$menuArray[$cookie_pos]->menuTitle."</div></td>
			<td class=\"shard_topright\"></td></tr></table>
			<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_left\"></td><td class=\"menuInfo\">");
		
			$menuArrayCookieSize = sizeof($menuArray[$cookie_pos]->menuContentArray);
			for ($j=0;$j<$menuArrayCookieSize;$j++) {
				print "<div>".$menuArray[$cookie_pos]->menuContentArray[$j]."</div>";
			}
			print ("</td><td class=\"shard_right\"></td></tr></table>
			<table cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"shard_bottomleft\"></td>
			<td class=\"shard_bottom\"></td>
			<td class=\"shard_bottomright\"></td></tr></table>
			</div>
			</li>");
			}
		}
	}
}
	
function vContent($contentArray) {
	global $action;
	global $siteSettings;

	print"<div id=\"content\" class=\"content\">\n";
	
	for ($i=0;$i<sizeof($contentArray);$i++) {
		
		print renderPost($contentArray, $i);
	}

	print "</div>\n";
}

function renderPost($contentArray, $i, $showrating=true,$noquote=false) {
	$retStr = "";

	switch ($contentArray[$i]->contentType):

	case "contentType1":
	case "contentType2":
	case "contentType3":
	
	$alternatePostStyle = "post";
			
	if (($contentArray[$i]->postid % 2) == 0)
		$alternatePostStyle = "post2";
		
	$postID = $contentArray[$i]->postid;

	$retStr .= "<div class='postWrapper' id='postid".$contentArray[$i]->postid."'>\n";
	$retStr .= $contentArray[$i]->anchor;
	$retStr .= "<div style='display:none;' id='currentNRIvalue".$contentArray[$i]->postid."'>".$contentArray[$i]->rating."</div>";
	
	global $CURRENTUSERDTP;
	global $CURRENTUSERRATING;
	global $CURRENTUSERID;
	global $LANG;
	global $CURRENTUSER;
	global $CURRENTUSERAJAX;
	global $siteSettings;

	$jp = "";
	if ($CURRENTUSERAJAX)
		$jp = "</span>";

	$normalLine = "display: block;";
	$hiddenLine = "display: none;";
	$displayClass = "";
	if ($showrating) {
		if ($contentArray[$i]->rating == 0)
			$postRatingColorGradient = "postRatingColorGradient1";		
		else if ($contentArray[$i]->rating > 0)
			$postRatingColorGradient = "postRatingColorGradient2";
		else if ($contentArray[$i]->rating < 0)
			$postRatingColorGradient = "postRatingColorGradient3";			
			
		$showPlus = "";
		if ($contentArray[$i]->rating > 0)
			$showPlus = "+";
		
		$upClass = 'uparrowoff';
		$downClass = 'downarrowoff';
		$displayClass = "<div class='postRatingDisplay'><div style='float: right; margin-left: 5px;' class='$postRatingColorGradient' id='ratingDisplaypost".$postID."'>$showPlus".$contentArray[$i]->rating."</div> <div id='postRatingStatus".$postID."' style='float:right;' class='postTitle'>$LANG[RATING] </div></div>";

		$rated = false;
		$userrated = mf_query("SELECT rating FROM postratings WHERE postID = '$postID' AND user = \"$CURRENTUSER\" LIMIT 1");
		if ($userrated = mysql_fetch_assoc($userrated)) {			

			$rated = true;
			if ($userrated['rating'] > 0) {
				$upClass = 'uparrowon';
				$downClass = 'downarrowoff';
				$displayClass = "<div class='postRatingDisplay'><div style='float: right; margin-left: 5px;' class='$postRatingColorGradient' id='ratingDisplaypost".$postID."'>$showPlus".$contentArray[$i]->rating."</div> <div id='postRatingStatus".$postID."' style='float:right;' class='postTitlePositive'> $LANG[RATED] </div></div>";
			}
			else if ($userrated['rating'] == 0) {
				$upClass = 'uparrowon';
				$downClass = 'downarrowon';
				$displayClass = "<div class='postRatingDisplay'><div style='float: right; margin-left: 5px;' class='$postRatingColorGradient' id='ratingDisplaypost".$postID."'>$showPlus".$contentArray[$i]->rating."</div> <div id='postRatingStatus".$postID."' style='float:right;' class='postTitlePositive'> $LANG[RATED] </div></div>";
			}
			else {
				$downClass = 'downarrowon';
				$upClass = 'uparrowoff';
				$displayClass = "<div class='postRatingDisplay'><div style='float: right; margin-left: 5px;' class='$postRatingColorGradient' id='ratingDisplaypost".$postID."'>$showPlus".$contentArray[$i]->rating."</div> <div id='postRatingStatus".$postID."' style='float:right;' class='postTitleNegative'> $LANG[RATED] </div></div>";
			}
		}			
		if (!$contentArray[$i]->rating)
			$contentArray[$i]->rating = "0";
		if ($contentArray[$i]->rating < $CURRENTUSERDTP && $CURRENTUSER != $contentArray[$i]->author) {
			$hiddenLine = "display: block;";
			$normalLine = "display: none;";
		}
	}

	$retStr .= "<div class=\"post-top\" style='$hiddenLine' id='hidden".$contentArray[$i]->postid."'>
				<div style='float: left; display: block;' class='postTitle'>" .$contentArray[$i]->title . "," . $LANG['POSTEDBY2'] . "<b>" . $contentArray[$i]->author . "</b>" . $LANG['DATE_LINE_FULL2'] . date($LANG['DATE_LINE_TIME'], $contentArray[$i]->dateCreated) . " " . $LANG['ON'] . " " . date($LANG['DATE_LINE_MINIMAL2'], $contentArray[$i]->dateCreated) . "</div>";
	$retStr .= "</div>\n";

	$retStr .= "<div class=\"post-top\" style='$normalLine' id='normal".$contentArray[$i]->postid."'>";
	if ($showrating) {	
		$retStr .= "<div class='rate_container'>";
		$retStr .= "<div id='up_rate".$postID."' class='rate_up'><select class='up_rate' name='up_rate".$postID."' onchange=\"selectRate(this.options[this.selectedIndex].value,'up_rate".$postID."','".$postID."');\">";
		$retStr .= $_SESSION['option_up'];
		$retStr .= "</select></div>";
		
		$retStr .= "<div id='down_rate".$postID."' class='rate_down'><select class='down_rate' onchange=\"selectRate(this.options[this.selectedIndex].value,'down_rate".$postID."','".$postID."');\">";
		$retStr .= $_SESSION['option_down'];
		$retStr .= "</select></div>";
		$retStr .= "</div>";
	}
	$retStr .= "<div style='float: left; display: block;' class='postTitle'>" .$contentArray[$i]->title . "," .$LANG['AT2'] . date($LANG['DATE_LINE_TIME'], $contentArray[$i]->dateCreated) . " " . $LANG['ON'] . " " . date($LANG['DATE_LINE_MINIMAL2'], $contentArray[$i]->dateCreated) . "&nbsp;&nbsp;" . $contentArray[$i]->subText2 . "</div>";
	if ($showrating && ($CURRENTUSERID != $contentArray[$i]->userID) && isset($CURRENTUSERRATING)) {
		$retStr .= "<div id='arrowpost".$postID."'>
						<div onclick=\"already_rated = setRateVisible('up_rate".$postID."','".$postID."','down_rate".$postID."','".$rated."',already_rated); toggleRatingArrow('post', $postID, 'uparrow', ".number_format($CURRENTUSERRATING, 2).");\" id='uparrowpost".$postID."' class='$upClass'></div>
						<div onclick=\"already_rated = setRateVisible('down_rate".$postID."','".$postID."','up_rate".$postID."','".$rated."',already_rated); toggleRatingArrow('post', $postID, 'downarrow', ".number_format($CURRENTUSERRATING, 2).");\" id='downarrowpost".$postID."' class='$downClass'></div>
						</div>";
	}	
	$retStr .=" $displayClass
			</div>\n";

	$retStr .= "<div style='$hiddenLine' id=\"hiddenpost". $contentArray[$i]->postid ."\"><center><small>$LANG[POST_BELOW_THRESHOLD] (<a href=\"javascript:toggleLayer('post". $contentArray[$i]->postid ."');toggleLayer('hiddenpost". $contentArray[$i]->postid ."');toggleLayer('hidden". $contentArray[$i]->postid ."');toggleLayer('normal". $contentArray[$i]->postid ."');\">$LANG[SHOW_ANYWAY]</a>)</small></center></div>";
	$retStr .= "<div id=\"postedit". $contentArray[$i]->postid ."\" style='display:none;margin-left:-3px;'></div>";
	$retStr .= "<div style='$normalLine clear: both;' class=\"$alternatePostStyle\" id=\"post". $contentArray[$i]->postid ."\"><div class='postUserInfo'>";
	
	$retStr .= "<div class='avatar'>";
	
	if ($contentArray[$i]->picture != "")
		$retStr .= "<img class='avatarPicture' alt='$LANG[AVATAR]' src=\"". $contentArray[$i]->picture ."\" />";
	else
		$retStr .= "( $LANG[USER_PROFILE_NO_AVATAR] )";
		
	$retStr .= "</div>";
	
	$tooLongName = "";
	if (strlen($contentArray[$i]->author) > 13)
		$tooLongName = "style='letter-spacing: -1px;'";
		
	if ($CURRENTUSER != "anonymous") {
		$retStr .= "<a $tooLongName href='".make_link("forum","&amp;action=g_ep&amp;ID=".$contentArray[$i]->userID,"#user/".$contentArray[$i]->userID)."'>";
		if ($jp and !strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
			$retStr .= "<span onclick=\"userprofile(0,". $contentArray[$i]->postid .",".$contentArray[$i]->userID."); return false;\">";
		$retStr .= $contentArray[$i]->author."$jp</a> <br/><span class='userNriDisplay'>".$contentArray[$i]->userRating."</span></div>";
	}
	else
		$retStr .= $contentArray[$i]->author." <br/></div>";

	$retStr .= "<div id='postContent".$contentArray[$i]->postid."' class='postContent postContentWidth'";
	if ($siteSettings['quote_all_post'] && !$noquote)
		$retStr .= "onmouseover=\"document.getElementById('qqpost_".$contentArray[$i]->postid."').style.display='inline';\"  onmouseout=\"document.getElementById('qqpost_".$contentArray[$i]->postid."').style.display='none';\"";
	$retStr .= ">";
	if ($siteSettings['quote_all_post'] && !$noquote)
		$retStr .= "<span style='float:right;display:none;margin-right:-15px;margin-top:-8px;cursor:pointer;' id='qqpost_".$contentArray[$i]->postid."' onclick=\"quickQuote('".make_var_safe($contentArray[$i]->author)."', '".$contentArray[$i]->postid."', '9999'); return false;\"><img src='engine/grafts/" . $siteSettings['graft'] . "/images/qq.png' alt='[Q]' title=\"$LANG[QUOTE_ALT_ALL]\"/></span>";
	$retStr .= "<!-- google_ad_section_start -->". $contentArray[$i]->primaryContent . "<!-- google_ad_section_end -->" . "";
	$retStr .= "<div class='sig'><br/>".stripslashes($contentArray[$i]->sig)."</div>";

	$retStr .= "</div><div class='clearfix'></div></div></div>
				<div class='postContextmenu' onmousedown=\"newcontexmenu(event,$postID);return false;\"></div>";
			
	break;

		
		case "contentArticle":
			
			//------------------------------------------------------------------------
			// Define the way a content article should appear.  Follow examples above.
			// All data is accessed via the $contentArray[$i] object.
			// See clsContentObj.php for all the different fields available.
			//------------------------------------------------------------------------
			
		break;
		
		
		
		case "generic":
		
			$retStr .="<div class='generic'>";
			
			
			$retStr .= "<h2>" . $contentArray[$i]->title . "</h2>";
			
			$retStr .= "";
			$retStr .= $contentArray[$i]->primaryContent ."";
			$retStr .= "</div>";
		
		
		break;
		
		
		
		//--------------------------------------------------------------------
		// clsContentObj.contentType to display if none is selected (generic)
		//--------------------------------------------------------------------
		default:
			
			$retStr .="";
			
			
			$retStr .= "" . $contentArray[$i]->title . "";
			
			$retStr .= "";
			$retStr .= $contentArray[$i]->primaryContent ."";
			$retStr .= "";			
		break;
		
		
		
		
		endswitch;	
		
		return $retStr;
	
}

function remove_zero($data) {
	if ($data == "0")
		$data = "";
		
	return $data;
}
