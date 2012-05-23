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
    //
    // formatting.php
    // handles the formatting of posts
	//
    //
    
    //
    // Load list of smilies once    
    //	
    	
    if (!isset($_COOKIE['stillSmilies']))
		$smiliesDir = "images/smilies/";
	else
		$smiliesDir = "images/smilies/small/";
	$smiliesDirHandle = opendir( $smiliesDir );
	while( ($sfile = readdir( $smiliesDirHandle )) != FALSE ) {
		if( $sfile != "small" && $sfile != "." && $sfile != ".." && $sfile != ".svn")		
			$smiliesfile[] = $sfile;
	}    


    //---------------------------------------------------------------------------------------
    // format_smilies - implements the smily system
    //
    //---------------------------------------------------------------------------------------    
	function format_smilies($text) {
		global $smiliesfile;
		global $smiliesDir;

		if( count( $smiliesfile ) > 0 ) {	
			foreach( $smiliesfile as $d ) {	
				$fileArray = explode(".", $d);
				if ($fileArray[1] == "gif" || $fileArray[1] == "png")
					$text = str_replace(":$fileArray[0]:", "<img src='".$smiliesDir."$d' alt='$fileArray[0]' title=':$fileArray[0] :' />", $text);
			}
		}
		return $text;
	}
 
	function format_blurcode($text , $relnofollow=true ,$tid="",$postID="",$imgmaxW=""){
		global $CURRENTUSERAJAX;
		global $CURRENTUSER;
		global $siteSettings;

        if( $relnofollow == true )
                $tag = " rel='nofollow'";
        else
                $tag = "";
		$reg_exs_find = array(                
                "/\[email\]([^\[]+?)\[\/email\]/i",
                "/\[email=([^\]]+?)\]([^\[]]+?)\[\/email\]/i",
                "/\[br\]/i",
				"/\[\*\]/i",
                "/\[hr\]/i",
                "/\[quote\](.+?)\[\/quote\]/i",
                "/\[t1\](.+?)\[\/t1\]/i",
				"/\[t1=([^\]]+?)\](.+?)\[\/t1\]/i",
                "/\[t2\](.+?)\[\/t2\]/i",
                "/\[t3\](.+?)\[\/t3\]/i",
				"/\[t3c\](.+?)\[\/t3c\]/i",
				"/\[css=([^\]]+?)\](.+?)\[\/css\]/ie",
				"/\[blocl\](.+?)\[\/blocl\]/i",
				"/\[blocr\](.+?)\[\/blocr\]/i",
				"/\[center\](.+?)\[\/center\]/i",
				"/\[justify\](.+?)\[\/justify\]/i",
				"/\[code\](.*?)\[\/code\]/isx",
                "/\[size=([0-9]{1,2})([^\]]+?)?\](.+?)\[\/size\]/ie",
                "/\[color=([^\]]+?)\](.+?)\[\/color\]/i",
				"/\[name=([^\]]+?)\]/i",
                "/\[b\](.+?)\[\/b\]/i",
				"/\[i\](.+?)\[\/i\]/i",
				"/\[u\](.+?)\[\/u\]/i",
				"/\[s\](.+?)\[\/s\]/i",
				"/\[ol\](.+?)\[\/ol\]/i",
				"/\[ul\](.+?)\[\/ul\]/i",
				"/\[li\](.+?)\[\/li\]/i",
				"/\[album\](.+?)\[\/album\]/ie",
				"/\[pict\](.+?)\[\/pict\]/ie",
				"/\[vote\](.+?)\[\/vote\]/ie",
				"/\[vote.([^\]]+?)\](.+?)\[\/vote\]/ie"
        );
        $reg_exs_replace = array(
                "<a href=\"mailto:\\1\"$tag>\\1</a>",
                "<a href=\"mailto:\\1\"$tag>\\2</a>",
                "<br />",
				"<li />",
                "<hr />",
                "<div class='blockquote'>\\1</div>",
                "<table cellpadding='0'>\\1</table>",
				"<table cellpadding='\\1'>\\2</table>",
                "<tr>\\1</tr>",
                "<td>\\1</td>",
				"<td align='center'>\\1</td>",
				"'<div style=\''.make_var_safe('\\1').'\'>\\2</div>'",
				"<div style='float:left;margin-right:7px;'>\\1</div>",
				"<div style='float:right;margin-left:7px;'>\\1</div>",
				"<center>\\1</center>",
				"<div style=\"text-align: justify; margin-right: 20px;\">\\1</div>",
				"<pre class='code' >\\1</pre>",
                "'<div style=\'display:inline;font-size: '.((\\1/4)+.25).'em;\'>\\3</div>'",
                "<div style='display:inline;color: \\1;'>\\2</div>",
				"<a name='\\1' id='anchor_\\1'></a>",
                "<b>\\1</b>",
                "<i>\\1</i>",
                "<u>\\1</u>",
				"<strike>\\1</strike>",
                "<ol>\\1</ol>",
                "<ul>\\1</ul>",
				"<li>\\1</li>",
				"'<div>'.load_album(\\1,\"$tid\").'</div>'",
				"'<div>'.load_pict(\\1,\"$tid\").'</div>'",
				"'<span id=\'vote_".$postID."_\\1\'>'.load_vote(\"$postID\",\"\\1\",1).'</span>'",
				"'<span class=\'vote_\\1_\\2\'>'.load_vote(\"\\1\",\"\\2\").'</span>'"
        );
        
        $text = preg_replace($reg_exs_find, $reg_exs_replace, $text);

		$counter = 0;
        while(stristr($text, "[quote]") && $counter < 5) {
			$text = preg_replace($reg_exs_find, $reg_exs_replace, $text);
			$counter++;
		}

		if ($imgmaxW)
			$imgmaxW = "max-width:$imgmaxW;";
		$text=preg_replace("/\[img=?([^\]]+?)?\](https?:\/\/)?([^\[\s]+)\[\/img\]/i","<img src=\"\\2\\3\" border=\"0\" style=\"$imgmaxW\\1\" alt=\" [x] \" />",$text);
		$text=preg_replace("/\[url=([^\]]*)\](.+?)\[\/url\]/i","<a href='\\1' target='_blank'><span style='color:#993530;'>\\2</span></a>",$text);
		$text=preg_replace("/\[url\]([^\[]+)\[\/url\]/i","<a href='\\1' target='_blank'>\\1</a>",$text);
		if ($CURRENTUSERAJAX)
			$text=preg_replace("/\[iurl=([^\[]+)\](.+?)\[\/iurl\]/i","<span class='ajaxianLink' onclick=\"ajaxlink('\\1');\">\\2</span>",$text);
		else
		$text=preg_replace("/\[iurl=([^\[]+)\](.+?)\[\/iurl\]/i","<span class='ajaxianLink'><a href='\\1' >\\2</a></span>",$text);
		$text=preg_replace("/\[iurl\]([^\[]+)\[\/iurl\]/i","<span class='ajaxianLink'><a href='\\1' >\\1</a></span>",$text);
		// Object
		$text=preg_replace("/\[object data={([^\]]+?)} width={([0-9]{2,3}%?p?x?)} height={([0-9]{2,3}%?p?x?)}\](.+?)?\[\/object\]/i","<object data=\"\\1\" type=\"application/x-shockwave-flash\" width=\"\\2\" height=\"\\3\">\\4<param name=\"src\" value=\"\\1\" /></object>",$text);
		$text=preg_replace("/\[param name={([^\]]+?)} value={([^\]]+?)}\]/i","<param name=\"\\1\" value=\"\\2\" />",$text);
		// iFrame
		if ($siteSettings['iframe'])
			$text=preg_replace("/\[iframe data={([^\]]+?)} width={([0-9]{2,3}%?p?x?)} height={([0-9]{2,3}%?p?x?)} type={([^\]]+?)}\]([^\]]+?)?\[\/iframe\]/i","<object data=\"\\1\" type=\"\\4\" width=\"\\2\" height=\"\\3\">\\5<param name=\"src\" value=\"\\1\" /></object>",$text);
		// youtube
		$text=preg_replace("/\[youtube\](http:\/\/([a-zA-Z0-9]+\.)?youtube\.com\/watch\?v=)?([A-Za-z0-9_-]+)([^\[]+)?\[\/youtube\]/i","<iframe width=\"560\" height=\"315\" src=\"http://www.youtube.com/embed/\\3\" frameborder=\"0\" allowfullscreen ></iframe>",$text);
		//Dailymotion
		$text=preg_replace("/\[daily\](http:\/\/([a-zA-Z0-9]+\.)?dailymotion\.com\/swf\/)?([A-Za-z0-9_-]+)\[\/daily\]/i","<object width=\"480\" height=\"375\" data=\"http://www.dailymotion.com/swf/\\3\" type=\"application/x-shockwave-flash\"><param name=\"allowfullscreen\" value=\"true\" /><param name=\"allowScriptAccess\" value=\"always\" /><param name=\"src\" value=\"http://www.dailymotion.com/swf/\\3\" /></object>",$text);
		//Metacafe
		if ($siteSettings['metacafe'])
			$text=preg_replace("/\[metacafe\](http:\/\/www\.metacafe\.com\/watch\/)?([0-9]+)\/?([A-Za-z0-9_-]+)?\/?\[\/metacafe\]/i","<object width=\"460\" height=\"395\" data=\"http://www.metacafe.com/fplayer/\\2/play.swf\" type=\"application/x-shockwave-flash\"><param name=\"allowfullscreen\" value=\"true\" /><param name=\"wmode\" value=\"transparent\" /><param name=\"src\" value=\"http://www.metacafe.com/fplayer/\\2/play.swf\" /></object>",$text);
		//Deezer
		if ($siteSettings['deezer'])
			$text=preg_replace("/\[deezer\](http:\/\/([a-zA-Z0-9]+\.))?(deezer\.com\/track\/)?(deezer\.com\/listen-)?([A-Za-z0-9_-]+)([^\[]+)?\[\/deezer\]/i","<object width=\"220\" height=\"55\" data=\"http://www.deezer.com/embedded/small-widget-v2.swf?idSong=\\5&amp;colorBackground=0xF1F1F1&amp;textColor1=0x000000&amp;colorVolume=0xAF403B&amp;autoplay=0\" type=\"application/x-shockwave-flash\"><param name=\"allowfullscreen\" value=\"true\" /><param name=\"src\" value=\"http://www.deezer.com/embedded/small-widget-v2.swf?idSong=\\5&amp;colorBackground=0xF1F1F1&amp;textColor1=0x000000&amp;colorVolume=0xAF403B&amp;autoplay=0\" /></object>",$text);
		// Video
		$text=preg_replace("/\[video\]([^\[]+)\[\/video\]/i","<video src=\"\\1\" width=\"600\" controls></video>",$text);
		// Média
		$text=preg_replace("/\[media\]([^\[]+)\[\/media\]/i","<object data=\"\\1\" type=\"application/x-shockwave-flash\" width=\"520\" height=\"414\"><param name=\"wmode\" value=\"transparent\" /><param name=\"src\" value=\"\\1\" /></object>",$text);
		// old embed : DON'T USE
		$text=preg_replace("/\[embed\]([^\[]+)\[\/embed\]/i","<embed \\1></embed>",$text);
		// Spoiler
		$text=preg_replace("/\[spoiler=([^\]]*)\](.+?)\[\/spoiler\]/i","<fieldset class='spoilerFieldset'><legend><a href='#' onclick=\"toggleElement(this.parentNode.parentNode.lastChild); return false;\">\\1</a></legend><div style='display:none; margin-left: 20px;'>\\2</div></fieldset>",$text);
		//
		$text = str_replace("[!","[",$text);
		return $text;
}	
    
    //---------------------------------------------------------------------------------------
    // format_newlines - replace newlines with HTML breaks
    //
    //---------------------------------------------------------------------------------------
    function format_newlines($text ) {
        $text = str_replace("\r\n" , "<br />" , $text );
        $text = str_replace("\n" , "<br />" , $text );
        return $text;
    }    

    function format_newlinesBB($text ) {
        $text = str_replace("\r\n" , "[br]" , $text );
        $text = str_replace("\n" , "[br]" , $text );
        return $text;
    }    

    //---------------------------------------------------------------------------------------
    // format_post - applied to post body to perform the desired formatting
    //	if $relnofollow is true, the rel=nofollow attribute will be applied to links
    //---------------------------------------------------------------------------------------
    function format_post($text , $relnofollow ,$tid="",$postID="",$imgmaxW="") {
        // start_benchmark( "format_post" );
//		$relnofollow  = false;
        $text = format_newlines($text );
        $text = format_blurcode($text , $relnofollow ,$tid,$postID,$imgmaxW);
		$text = format_urldetect($text, $relnofollow );
        $text = format_smilies($text );
        // stop_benchmark( );
         
        return $text;
    }
    
	//---------------------------------------------------------------------------------------
    // format_urldetect - detect URLs and make them into working links
    // adds Google's rel=nofollow attribute to curb comment spam
    //---------------------------------------------------------------------------------------
	function format_urldetect( $text , $relnofollow ) {	
		if( $relnofollow == true )
			$tag = " rel='nofollow'";
		else
			$tag = "";

		//
		// First, look for strings beginning with http:// that AREN't preceded by anything but a white char	
		//
		$text = preg_replace( '#(?<!\w|=|\'|{|"|;)((http|ftp)+(s)?:\/\/[^<>\s]+[\w])#i', "<a target=\"_blank\" href=\"\\0\"" . $tag . ">\\0</a>", $text );

		//
		// Second, look for strings with casual urls (www.something.com...) that AREN't preceded by anything but a white char
		//
		$text = preg_replace( '#(?<!\w|=|\'|{|"|;)(?<!http:\/\/)(?<!https:\/\/)((www)\.[^<>\s]+[\w])#i', "<a target=\"_blank\" href=\"http://\\0\"" . $tag . ">\\0</a>", $text );

		return $text;
	}

	function convertBrDelims($text) {
		$text = str_replace("<br />", ":::ZOMGLINEBREAKSZZ:::", $text);
		
		return $text;
	}

	function convertBrDelimsBB($text) {
		$text = str_replace("<br />", "[br]", $text);
		
		return $text;
	}

     
    //---------------------------------------------------------------------------------------
    // format_quickquote - adds javascript QuickQuote (tm) links to each paragraph
    //      typically called after format_post has processed the text
    //
    //---------------------------------------------------------------------------------------
    function format_quickquote($text, $poster, $postID) {
        global $siteSettings;
		global $LANG;
        $text = format_newlines($text );
        //
        // break the string according to any instances of two <br>'s
        // these indicate a new paragraph basically--whitespace between
        // the things being said
        //

        $text = preg_replace("/\[spoiler=([^\]]*)\](.+?)\[\/spoiler\]/e", "'[spoiler=\\1]'.convertBrDelims('\\2').'[/spoiler]'", $text);
		$text = preg_replace("/\[code\](.*?)\[\/code\]/ie", "'[code]'.convertBrDelims('\\1').'[/code]'", $text);
		$text = preg_replace("/\[quote\](.+?)\[\/quote\]/ie", "'[quote]'.convertBrDelims('\\1').'[/quote]'", $text);
        $stringArray = preg_split( '/(<br\s\/>)(<br\s\/>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
        $arrayLength = count($stringArray);
        $code_inter = false;
		$stringQQ = "";
		$codeBegin_inter = "";
		$codeEnd_inter = "";
		$j = 0;
         
        for ($i = 0; $i < $arrayLength; $i++ ) {
            $codeBegin = "";
            $codeEnd = "";

			// $stringArray[$i] = str_replace("<br />", "", $stringArray[$i]);
            //
            // depending on how many <br>'s were put in explode() may have broken the string up
            // into sections without any text--ignore those; who wants to quote whitespace?
            //
                $imgpath = 'engine/grafts/' . $siteSettings['graft'] . '/images/qq.png';

                if ($stringArray[$i] != "<br />" && $stringArray[$i] != "" && $stringArray[$i] != "<br /><br />") {
					if ($code_inter) {
						$codeBegin_inter = "[code]";
						$codeEnd_inter = "[/code]";
					}

					if (stristr($stringArray[$i], "[code]") && !stristr($stringArray[$i], "[/code]")) {
						$stringArray[$i] = str_replace("[code]", "", $stringArray[$i]);
						$codeBegin = "[code]";
						$codeEnd = "[/code]";
						$code_inter = true;
					}
					if (stristr($stringArray[$i], "[/code]") && !stristr($stringArray[$i], "[code]")) {
						$stringArray[$i] = str_replace("[/code]", "", $stringArray[$i]);
						$codeBegin = "[code]";
						$codeEnd = "[/code]";
						$code_inter = false;
						$codeBegin_inter = "";
						$codeEnd_inter = "";
					}

                	$stringQQ .= "$codeBegin <div class='qqContainer' id='qqC$postID.$j' onmouseout=\"hideQQButton('qqB$postID.$j');\" onmouseover=\"showQQButton(this, 'qqB$postID.$j');\">";
                	$stringQQ .= "<div class='qqContent' id='qq$postID.$j'>$codeBegin_inter". $stringArray[$i] . "$codeEnd_inter</div>";
                	$stringQQ .= "<a id='qqB$postID.$j' class='qq' href='#' onclick=\"quickQuote('".make_var_safe($poster)."', '$postID', '$j'); return false;\" onmouseover=\"qqHover('qqC$postID.$j');return true;\" onmouseout=\"qqHoverOff('qqC$postID.$j');return true;\" ><img alt='[Q]' title=\"$LANG[QUOTE_ALT]\"  src='" . $imgpath . "' /></a>";
                	$stringQQ .= "<div class='clearfix'></div></div> $codeEnd";
					$j++;
				}
				else {
                	$stringQQ = $stringArray[$i];
				}
                $stringArray[$i] = $stringQQ;
        }
        $text = implode("" , $stringArray );
        $text = str_replace(":::ZOMGLINEBREAKSZZ:::", "<br />", $text);
		$text = str_replace("<br />", "<div class='clearfix' style='height: 4px;'></div>", $text);

        return $text;
    }

    
    function qq_lookup($message, $pthread=0, $category=0) {		
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $LANG;
		$msgArray = preg_split('((\[qq\.\d*\.\d*\.\])|(\[/qq\]))', $message,-1, PREG_SPLIT_DELIM_CAPTURE);
		$posttype = "AND forum_posts.posttype < 3";
		if (isInGroup($CURRENTUSER, 'admin'))
			$posttype = "";

		for ($i=0;$i<count($msgArray);$i++) {
			if (stristr($msgArray[$i], "[qq.")) {
				$qqArr = explode(".", $msgArray[$i]);
				for ($j=0;$j<count($qqArr);$j++) {
					if (stristr($qqArr[$j], "[qq")) {
						$post = mf_query("SELECT forum_posts.userID, forum_posts.body, forum_posts.user, forum_posts.date, forum_topics.pthread, forum_topics.ID   
							FROM forum_posts
							JOIN forum_topics ON forum_posts.threadID = forum_topics.ID 
							WHERE forum_posts.ID='". $qqArr[$j+1]."' $posttype LIMIT 1");
						$post = mysql_fetch_assoc($post);
						if ($post['pthread'] == "1") {
							$verify = mf_query("SELECT threadID FROM fhits WHERE threadID = '$post[ID]' AND userID = '$CURRENTUSERID' LIMIT 1");
							if (!$verify = mysql_fetch_assoc($verify)) {
								$post['body'] = "";
								$post['user'] = "";
								$post['date'] = "59054400";
							}
						}
						$msgArray[$i] = "[quote]";
						$msgArray[$i+1]="[i][iurl=#post/". $qqArr[$j+1]."]$post[user] $LANG[SAID] [color=silver]$LANG[ON] ".date($LANG['DATE_LINE_MINIMAL2'], $post['date'])." $LANG[AT] ".date($LANG['DATE_LINE_TIME'], $post['date'])."[/color][/iurl] :[/i][br]";
						if ($qqArr[$j+2] != "9999") {
							$post['body'] = format_newlines($post['body']);
							$post['body'] = preg_replace("/\[spoiler=([^\]]*)\](.+?)\[\/spoiler\]/e", "'[spoiler=\\1]'.convertBrDelimsBB('\\2').'[/spoiler]'", $post['body']);
							$post['body'] = preg_replace("/\[quote\](.+?)\[\/quote\]/ie", "'[quote]'.convertBrDelimsBB('\\1').'[/quote]'", $post['body']);
							$post['body'] = preg_replace("/\[code\](.*?)\[\/code\]/ie", "'[code]'.convertBrDelims('\\1').'[/code]'", $post['body']);
							$stringArray = preg_split( '/(<br\s\/>)(<br\s\/>)/', str_replace("'", "\'", $post['body']), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
							$k1 = 0;
							$k2 = 0;
							while (isset($stringArray[$k1])) {
								if ($stringArray[$k1] != "<br />" && $stringArray[$k1] != "" && $stringArray[$k1] != "<br /><br />") {
									$validstringArray[$k2] = $stringArray[$k1];
									$k2++;
								}
								$k1++;
							}
							$msgArray[$i+1].= $validstringArray[$qqArr[$j+2]];
						}
						else {
							$post['body'] = format_newlinesBB($post['body']);
							$msgArray[$i+1].= str_replace("'", "\'", $post['body']);
						}

						// Add to user's quoted nri 
						if ($pthread == 0 && $post['user'] != $CURRENTUSER) {
							$addQuotedNri = mf_query("update forum_user_nri set times_quoted = (times_quoted + 1) where userID='$post[userID]'");
						}

						$j = count($qqArr);
					}
				}
			}
		}
		
		$msg = implode("", $msgArray);
		$msg = str_replace("[/qq]", "[/quote]", $msg);
		return $msg;		
	}

    function qq_lookup_preview($message, $pthread=0, $category=0) {		
		global $CURRENTUSER;
		global $CURRENTUSERID;
		global $LANG;
		
		$msgArray = preg_split('((\[qq\.\d*\.\d*\.\])|(\[/qq\]))', $message,-1, PREG_SPLIT_DELIM_CAPTURE);
		$posttype = "AND forum_posts.posttype < 3";
		if (isInGroup($CURRENTUSER, 'admin'))
			$posttype = "";
		
		for ($i=0;$i<count($msgArray);$i++) {
			if (stristr($msgArray[$i], "[qq.")) {
				$qqArr = explode(".", $msgArray[$i]);
				for ($j=0;$j<count($qqArr);$j++) {
					if (stristr($qqArr[$j], "[qq")) {
						$post = mf_query("SELECT forum_posts.userID, forum_posts.body, forum_posts.user, forum_posts.date, forum_topics.pthread, forum_topics.ID   
							FROM forum_posts
							JOIN forum_topics ON forum_posts.threadID = forum_topics.ID 
							WHERE forum_posts.ID='". $qqArr[$j+1]."' $posttype LIMIT 1");
						$post = mysql_fetch_assoc($post);
						if ($post['pthread'] == "1") {
							$verify = mf_query("SELECT threadID FROM fhits WHERE threadID = '$post[ID]' AND userID = '$CURRENTUSERID' LIMIT 1");
							if (!$verify = mysql_fetch_assoc($verify)) {
								$post['body'] = "";
								$post['user'] = "";
								$post['date'] = "59054400";
							}
						}
						$msgArray[$i] = "[quote]";
						$msgArray[$i+1]="[i][iurl=#post/". $qqArr[$j+1]."]$post[user] $LANG[SAID] [color=silver]$LANG[ON] ".date($LANG['DATE_LINE_MINIMAL2'], $post['date'])." $LANG[AT] ".date($LANG['DATE_LINE_TIME'], $post['date'])."[/color][/iurl] :[/i][br]";
						if ($qqArr[$j+2] != "9999") {
							$post['body'] = format_newlines($post['body']);
							$post['body'] = preg_replace("/\[spoiler=([^\]]*)\](.*)\[\/spoiler\]/e", "'[spoiler=\\1]'.convertBrDelimsBB('\\2').'[/spoiler]'", $post['body']);
							$post['body'] = preg_replace("/\[quote\](.+?)\[\/quote\]/ie", "'[quote]'.convertBrDelimsBB('\\1').'[/quote]'", $post['body']);
							$post['body'] = preg_replace("/\[code\](.*?)\[\/code\]/ie", "'[code]'.convertBrDelimsBB('\\1').'[/code]'", $post['body']);
							$stringArray = preg_split( '/(<br\s\/>)(<br\s\/>)/', str_replace("'", "\'", $post['body']), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
							$k1 = 0;
							$k2 = 0;
							while (isset($stringArray[$k1])) {
								if ($stringArray[$k1] != "<br />" && $stringArray[$k1] != "" && $stringArray[$k1] != "<br /><br />") {
									$validstringArray[$k2] = $stringArray[$k1];
									$k2++;
								}
								$k1++;
							}
							$msgArray[$i+1].= $validstringArray[$qqArr[$j+2]];
						}
						else {
							$post['body'] = format_newlinesBB($post['body']);
							$msgArray[$i+1].= str_replace("'", "\'", $post['body']);
						}

						$j = count($qqArr);
					}
				}
			}
		}
		
		$msg = implode("", $msgArray);
		$msg = str_replace("[/qq]", "[/quote]", $msg);
		return $msg;		
	}
	
	function preformat_body($data) {

		$data = remove_br($data,"[url]","[/url]");
		$data = remove_br($data,"[ol]","[/ol]");
		$data = remove_br($data,"[ul]","[/ul]");
		$data = remove_br($data,"[li]","[/li]");
		$data = str_replace("url=www." , "url=http://www." , $data);

		return $data;
	}
	
	function remove_br($data,$bbcode2,$bbcode1) {

		while (strstr($data, "\r\n" .$bbcode1))
			$data = str_replace("\r\n" .$bbcode1 , $bbcode1 , $data);
		while (strstr($data, "\n" .$bbcode1))
			$data = str_replace("\n" .$bbcode1 , $bbcode1 , $data);
		while (strstr($data, $bbcode2. "\r\n"))
			$data = str_replace($bbcode2. "\r\n" , $bbcode2 , $data);
		while (strstr($data, $bbcode2. "\n"))
			$data = str_replace($bbcode2. "\n" , $bbcode2 , $data);

		return $data;
	}

	function remove_formating($text) {

		$text = format_newlines($text );
		$text=preg_replace("/\[blocl\](.+?)\[\/blocl\]/i","",$text);
		$text=preg_replace("/\[blocr\](.+?)\[\/blocr\]/i","",$text);
		$text=preg_replace("/\[center\](.+?)\[\/center\]/i","\\1",$text);
		$text=preg_replace("/\[justify\](.+?)\[\/justify\]/i","\\1",$text);
		$text=preg_replace("/\[css=([^\]]+?)\](.+?)\[\/css\]/i","\\2",$text);
		$text=preg_replace("/\[t1\](.+?)\[\/t1\]/i","",$text);
		$text=preg_replace("/\[t1=([^\]]+?)\](.+?)\[\/t1\]/i","",$text);
		$text=preg_replace("/\[name=([^\]]+?)\]/i","",$text);
		$text=preg_replace("/\[color=([^\]]+?)\](.+?)\[\/color\]/i","\\2",$text);
		$text=preg_replace("/\[size=([1-9])([^\]]+?)?\](.+?)\[\/size\]/i","\\2",$text);
		$text=preg_replace("/\[url=([^\]]*)\](.+?)\[\/url\]/i","",$text);
		$text=preg_replace("/\[url\]([^\[]+)\[\/url\]/i","",$text);
		$text=preg_replace("/\[iurl=([^\[]+)\](.+?)\[\/iurl\]/i","",$text);
		$text=preg_replace("/\[iurl\]([^\[]+)\[\/iurl\]/i","",$text);
		$text=preg_replace("/\[img\](https?:\/\/)?([^\[\s]+)\[\/img\]/i","",$text);
		// Object
		$text=preg_replace("/\[object data={([^\]]+?)} width={([0-9]{2,3})} height={([0-9]{2,3})}\](.+?)\[\/object\]/i","",$text);
		$text=preg_replace("/\[param name={([^\]]+?)} value={([^\]]+?)}\]/i","",$text);
		// youtube
		$text=preg_replace("/\[youtube\](http:\/\/([a-zA-Z0-9]+\.)?youtube\.com\/watch\?v=)?([A-Za-z0-9_-]+)([^\[]+)?\[\/youtube\]/i","",$text);
		//Dailymotion
		$text=preg_replace("/\[daily\](http:\/\/([a-zA-Z0-9]+\.)?dailymotion\.com\/swf\/)?([A-Za-z0-9_-]+)\[\/daily\]/i","",$text);
		//Metacafe
		$text=preg_replace("/\[metacafe\](http:\/\/www\.metacafe\.com\/watch\/)?([0-9]+)\/?([A-Za-z0-9_-]+)?\/?\[\/metacafe\]/i","",$text);
		//Deezer
		$text=preg_replace("/\[deezer\](http:\/\/([a-zA-Z0-9]+\.))?(deezer\.com\/track\/)?(deezer\.com\/listen-)?([A-Za-z0-9_-]+)([^\[]+)?\[\/deezer\]/i","",$text);
		// Spoiler
		$text=preg_replace("/\[spoiler=([^\]]*)\](.+?)\[\/spoiler\]/i","",$text);
		$text=preg_replace("/\[media\]([^\[]+)\[\/media\]/i","",$text);
		$text=preg_replace("/\[embed\]([^\[]+)\[\/embed\]/i","",$text);
		$text=preg_replace("/\[quote\]([^\[]+)\[\/quote\]/i","",$text);
		$text=preg_replace("/\[code\]([^\[]+)\[\/code\]/i","",$text);
		$text=preg_replace("/\[email\]([^\[]+?)\[\/email\]/i","",$text);
		$text=preg_replace("/\[email=([^\]]+?)\]([^\[]]+?)\[\/email\]/i","",$text);
		$text=preg_replace("/\[i\](.+?)\[\/i\]/i","\\1",$text);
		$text=preg_replace("/\[b\](.+?)\[\/b\]/i","\\1",$text);
		$text=preg_replace("/\[u\](.+?)\[\/u\]/i","\\1",$text);

		global $smiliesfile;	
		if( count( $smiliesfile ) > 0 ) {	
			foreach( $smiliesfile as $d ) {	
				$fileArray = explode(".", $d);
				if ($fileArray[1] == "gif" || $fileArray[1] == "png")
					$text = str_replace(":$fileArray[0]:", "", $text);
			}
		}

		$text = str_replace("<br />", "", $text);
		
	/*	$text = format_blurcode($text);
		while (substr($text,0,6) == "<br />")
			$text = substr($text,6,strlen($text) - 6);
		$text = str_replace("<br /><br /><br />", "<br />", $text);
		$text = str_replace("<br /><br />", "<br />", $text);
		*/
		return $text;
	}