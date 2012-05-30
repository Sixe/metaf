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

	require("adduserlib.php");
   
    if ($CURRENTUSERID)
		header("Location: ".make_link($siteSettings['defaultShard']));

    $user = $password = $vpassword = $birthdate = $location = $email = $emailv = $errormsg = "";
     
     
    if (array_key_exists("user" , $_POST ) == TRUE )
        $user = make_var_safe(htmlspecialchars($_POST["user"]));
	else if (isset($_COOKIE['fbname']))
		$user = $_COOKIE['fbname'];
        
    if (array_key_exists("password" , $_POST ) == TRUE )
        $password = make_var_safe(htmlspecialchars($_POST["password"]));
        
    if (array_key_exists("vpassword" , $_POST ) == TRUE )
        $vpassword = make_var_safe(htmlspecialchars($_POST["vpassword"]));
        
    if (array_key_exists("birthdate" , $_POST ) == TRUE )
        $birthdate = make_var_safe(htmlspecialchars($_POST["birthdate"]));
        
    if (array_key_exists("location" , $_POST ) == TRUE )
        $location = make_var_safe(htmlspecialchars($_POST["location"]));
        
    if (array_key_exists("email" , $_POST ) == TRUE )
        $email = make_var_safe(htmlspecialchars($_POST["email"]));
        
    if (array_key_exists("emailv" , $_POST ) == TRUE )
        $emailv = make_var_safe(htmlspecialchars($_POST["emailv"]));

     
    switch ($action):

    case "g_error":
		$thisContentObj = New contentObj;
		$thisContentObj->title = "<div class='bold'>".$LANG['ERROR_HAS_OCCURED']."</div>";
		$thisContentObj->primaryContent = "<div style='font-size:1.5em;margin-bottom:16px;'>";
		if (is_numeric($errormsg))
			$thisContentObj->primaryContent .= $LANG['ERROR_HAS_OCCURED_TEXT'.$errormsg];
		else
			$thisContentObj->primaryContent .= urldecode($errormsg);
		$thisContentObj->primaryContent .= "</div>";
		$thisContentObj->primaryContent .= "<span class='button' onclick='history.back()'>$LANG[BUTTON_BACK]</span>";
		$shardContentArray[] = $thisContentObj;   
    break;
    

    case "g_default": {
			
		$recaptcha = false;
		if ($SSDB['recaptcha_privKey'] && $SSDB['recaptcha_pubKey']) {
			$recaptcha = true;
			$recaptcha_privKey = $SSDB['recaptcha_privKey'];
			$recaptcha_pubKey = $SSDB['recaptcha_pubKey'];
		}
		if (isset($_POST['user'])) {
			if ($user == "" || $password == "" || $vpassword == "" || $email == "" || $vemail = "")
				$errormsg = "6";
			if (!$errormsg && $password != $vpassword)
				$errormsg = "1";
			if (!$errormsg &&  strlen($password) < 6)
				$errormsg = "2";
			if (!$errormsg) {
				$result = mf_query("SELECT COUNT(*) FROM users WHERE LOWER(username) =\"".mb_strtolower($user,'UTF-8')."\"");
				$result = mysql_fetch_row($result);
				if ($result[0] > 0)
					$errormsg = "3";
			}
			if (!$errormsg) {
				$error_email = checkemail($email);
				if ($error_email)
					$errormsg = $error_email;
			}
			if (!$errormsg && $email != $emailv)
				$errormsg = "10";
			if (!$errormsg && $recaptcha) {
				require_once('recaptchalib.php');
				$resp = recaptcha_check_answer ($recaptcha_privKey,$_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);
				if (!$resp->is_valid) {
					$errormsg= urlencode($resp->error);
				}
			}
			if (!$errormsg) {
				if ($birthdate != "") {
					$birthdate = str_replace("/","",$birthdate);
					$birthdate = str_replace("-","",$birthdate);
					$birthdate = str_replace(".","",$birthdate);
					$birthdate = str_replace(" ","",$birthdate);
					$birthdate = make_num_safe($birthdate);
					if (is_numeric($birthdate) && strlen($birthdate) == 8 && $birthdate > 0) {
						if ($LANG['CAL_FORMAT'] == "dd/MM/yyyy")
							$birthdate = substr($birthdate,4,4).substr($birthdate,2,2).substr($birthdate,0,2);
						else if ($LANG['CAL_FORMAT'] == "yyyy/MM/dd")
							$birthdate = substr($birthdate,0,4).substr($birthdate,4,2).substr($birthdate,6,2);
						$birthdate = strtotime($birthdate);
					}
					else
						$birthdate = "";
				}
		
				// add entry into database
				$datejoined = time();
				$password = sha1($password);
				$avatar_path = "";
				
				$verifyEmail = "NULL";
				if ($siteSettings['verifyEmail'] == "checked")
					$verifyEmail = "'pending'";
				
				mf_query("insert into users (username, password, birthdate, location, email, rating, datejoined, userstatus, avatar, lang) values (\"$user\", '$password', '$birthdate', '$location', '$email', 0, $datejoined, $verifyEmail, '$avatar_path', '$siteSettings[lang]' )");
		
				$userID = mf_query("select ID from users where datejoined=$datejoined and username=\"$user\" limit 1");
				if ($row = mysql_fetch_array($userID)) {		
					mf_query("insert into forum_user_nri (name, userID) values (\"$user\", $row[ID])");
					
					if ($siteSettings['verifyEmail'] == "checked") {

						$vstring = SHA1(time());
						mf_query("insert into verify (userID, verifystring) VALUES ($row[ID], '". $vstring ."')");

						srand((double)microtime()*1000000);
						$boundary = md5(uniqid(rand()));
						$header ="From: $siteSettings[titlebase] <$siteSettings[admin_mail]>\n";
						$header .="Reply-To: $siteSettings[admin_mail] \n";
						$header .="MIME-Version: 1.0\n";
						$header .="Content-Type: multipart/alternative;boundary=$boundary\n";

						$message = "\nThis is a multi-part message in MIME format.";
						$message .="\n--" . $boundary . "\nContent-Type: text/html;charset=\"utf-8\"\n\n";
						$message .= "<html><body>\n";
						$message .="<img src='" . $siteSettings['siteurl'] . "/engine/grafts/" . $siteSettings['graft'] . "/images/MailheaderImage.png'>\n";
						$message .= "\n<br/><h3>" . $LANG['MAIL_ACTIVATION1'] . $siteSettings['titlebase'] . "</h3><br/>\n";
						$message .= $LANG['MAIL_ACTIVATION2'] . " <i><b>" . $user . "</b></i> " . $LANG['MAIL_ACTIVATION3'];
						$message .= $siteSettings['titlebase'] . ".<br/><br/>" . $LANG['MAIL_ACTIVATION4'];
						$message .= "<a href=\"http://" . $siteSettings['siteurl'] . $_SERVER['PHP_SELF'] . "?shard=adduser&action=g_confirm&i=" . $row['ID'] . "&v=" . $vstring . "\">";
						$message .= $LANG['MAIL_ACTIVATION5'] . "</a>" . $LANG['MAIL_ACTIVATION6'] . "<br/>\n\n";
						$message .= "http://" . $siteSettings['siteurl'] . $_SERVER['PHP_SELF'];
						$message .= "?shard=adduser&action=g_confirm&i=" . $row['ID'] . "&v=" . $vstring;
						$message .= "<br/><br/><br/>\n\n" . $LANG['MAIL_ACTIVATION7'] . "\n<br/><br/>" . $siteSettings['titlebase'];
						$message .="\n--" . $boundary . "--\n end of the multi-part";

						mail($email, "$siteSettings[titlebase] $LANG[MAIL_ACTIVATION1]", $message, $header) or die($LANG['COULD_NOT_SEND_MAIL']);
					}
					
					header("Location: ".make_link("adduser","&action=g_success&user=$user"));
					exit();			
				}
				else {
							$thisContentObj = New contentObj;
							$thisContentObj->contentType = "generic";
							$thisContentObj->primaryContent = $LANG['MAIL_ACTIVATION8'];
							$shardContentArray[] = $thisContentObj;
					exit();
				}
			}
		}



		$calendardiv = "<img src='engine/grafts/$siteSettings[graft]/images/calendar.png' style='vertical-align:middle;margin-bottom:1px;' alt='' /></div><div id='calendardiv' style='position:absolute;visibility:hidden;background-color:white;'></div>";

		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->primaryContent = "";
		if ($errormsg) {
			$thisContentObj->primaryContent .= "<div style='font-size:1.5em;color:red;'>";
			if (is_numeric($errormsg))
				$thisContentObj->primaryContent .= $LANG['ERROR_HAS_OCCURED_TEXT'.$errormsg];
			else
				$thisContentObj->primaryContent .= urldecode($errormsg);
			$thisContentObj->primaryContent .= "</div>";
		}

		$default_value = "0";
		$form = "g_default";
		$user_list = "";
		if ($fbemail) {
			$thisContentObj->primaryContent .= "<div style='font-size:2.0em;'>".$LANG['FB_CONNECT_TITLE']."</div><img src=\"https://graph.facebook.com/".$fbuserID."/picture\"/> <span style='font-size:2.5em;font-weight:bold;vertical-align:top;'>$_COOKIE[fbname]</span><div style='height:32px;'></div>";
			$default_value = "1";
			$form = "add_user_fb";
			$user_sql = mf_query("SELECT ID, username FROM users WHERE email = '$fbemail' ");
			while ($user_row = mysql_fetch_assoc($user_sql)) {
				$user_list .= "<div><div style='margin-left:16px;font-size:1.6em;width:200px;display:inline-block;'>$user_row[username]</div><a class='button' href='index.php?shard=adduser&amp;action=asso_fb&amp;id=$user_row[ID]'>$LANG[FB_CONNECT_ASSO]</a></div>";
			}
		}
		if ($user_list)
			$thisContentObj->primaryContent .= "<div style='font-size:1.7em;'>".$LANG['FB_CONNECT_ASSO_EXIST']."</div>".$user_list."<div style='font-size:1.7em;margin-top:20px;'>".$LANG['FB_CONNECT_OR']."</div>";

		if (!$fbemail) {
			$thisContentObj->primaryContent .= "<div style='font-size:1.7em;'>".$LANG['CREATE_USERNAME']."</div>";
			$thisContentObj->primaryContent .= "<div>$LANG[CREATE_USERNAME_FORM1]</div>";
//			if (defined("FACEBOOK_APP_ID"))
//				$thisContentObj->primaryContent .= "<div>$LANG[CREATE_USERNAME_FORM2]: <span id=\"fb-auth2\" style='border:1px solid black;background-color:#5872A7;color:#ffffff;' class='button'>$LANG[FACEBOOK_CONNECT]</span></div>";
		}
		else
			$thisContentObj->primaryContent .= "<span id=\"fb-auth2\" style='margin-left:16px;border:1px solid black;background-color:#5872A7;color:#ffffff;' class='button'>$LANG[FACEBOOK_CONNECT]</span><div style='height:20px;'></div><div style='font-size:1.7em;margin-top:20px;'>".$LANG['FB_CONNECT_OR']."</div>";
		if (!$fbemail) {
			$thisContentObj->primaryContent .= "<div style='font-size:1.7em;'>$LANG[CREATE_USERNAME_FORM3]:</div>";
			$thisContentObj->primaryContent .= "<script type=\"text/javascript\" src=\"engine/core/CalendarPopup.js\"></script>";
			$thisContentObj->primaryContent .= "<script language=\"JavaScript\" id=\"js1\" type=\"text/javascript\">
			var cal4 = new CalendarPopup('calendardiv');
			cal4.showNavigationDropdowns();
			cal4.setMonthNames(".$LANG['CAL_MONTHS'].");
			cal4.setDayHeaders(".$LANG['CAL_DAYS'].");
			cal4.setWeekStartDay(".$LANG['CAL_WEEK_START_DAY'].");
			cal4.setTodayText(\"".$LANG['CAL_TODAY_TEXT']."\");
			</script>
			<script language=\"JavaScript\" type=\"text/javascript\">document.write(getCalendarStyles());</script>";
			$thisContentObj->primaryContent .= $LANG['NEW_USER_INSTRUCT'];
		}

		$thisContentObj->primaryContent .= "<form action='index.php?shard=adduser&amp;action=$form' method='post' name='new_profile' >";
		$thisContentObj->primaryContent .= "<input type='hidden' name='nick_ok' id='nick_ok' value='0'/>
		<input type='hidden' name='pass_ok' id='pass_ok' value='$default_value'/>
		<input type='hidden' name='vpass_ok' id='vpass_ok' value='$default_value'/>
		<input type='hidden' name='email_ok' id='email_ok' value='$default_value'/>
		<input type='hidden' name='vemail_ok' id='vemail_ok' value='$default_value'/>
		<div style='display:table;border-spacing:0.8em;' class='fineC2'>
			<div class='row'>
				<div class='cell right bold'>$LANG[PROMPT_CREATE_USERNAME]:</div>
				<div class='cell'>
					<input id='newNick' class='bselect adduser' type='text' name='user' size='13' maxlength='13' value=\"$user\" onkeyup=\"verif_nick();\" onchange=\"verif_nick();\" /> <span class='bold'>*</span>
					&nbsp;<span id='messagenewNick' style='font-size:1.1em;'></span>
				</div>
			</div>";
			if (!$fbemail) {
				$thisContentObj->primaryContent .= "<div class='row'>
				<div class='cell right bold'>$LANG[PROMPT_CREATE_PASSWORD]:</div>
				<div class='cell'>
					<input id='password' class='bselect adduser' type='password' name='password' value=\"$password\" size='20' maxlength='16' value='' onkeyup=\"verif_pass();\" onchange=\"verif_pass();\" /> <span class='bold'>*</span>
					&nbsp;<span id='messagePass' style='font-size:1.0em;'></span>
				</div>
			</div>
			<div class='row'>
				<div class='cell right bold'>$LANG[PROMPT_CREATE_PASSWORD_VERIFY]:</div>
				<div class='cell'>
					<input id='vpassword' class='bselect adduser' type='password' name='vpassword' value=\"$vpassword\" size='20' maxlength='16' value='' onkeyup=\"verif_vpass();\" onchange=\"verif_vpass();\" /> <span class='bold'>*</span>
					&nbsp;<span id='messageVPass' style='font-size:1.0em;'></span>
				</div>
			</div>
			<div class='row'>
				<div class='cell right bold'>$LANG[PROMPT_EMAIL]:</div>
				<div class='cell'>
					<input id='email' class='bselect adduser' type='text' name='email' size='25' value='$email' onkeyup=\"verif_email();\" onchange=\"verif_email();\" /> <span class='bold'>*</span>
					&nbsp;<span id='messageemail' style='font-size:1.0em;'></span>
				</div>
			</div>
			<div class='row'>
				<div class='cell right bold'>$LANG[PROMPT_EMAIL2]:</div>
				<div class='cell'>
					<input id='vemail' class='bselect adduser' type='text' name='emailv' size='25' value='$emailv' onkeyup=\"verif_vemail();\" onchange=\"verif_vemail();\" /> <span class='bold'>*</span>
					&nbsp;<span id='messagevemail' style='font-size:1.0em;'></span>
				</div>
			</div>
			<div class='row'>
				<div class='cell right'>$LANG[PROMPT_BIRTH_DATE]:</div>
				<div class='cell'>
			<div onclick=\"cal4.select(document.forms['new_profile'].birthdate,'anchor1','$LANG[CAL_FORMAT]'); return false;\" name='anchor1' id='anchor1'>
			<input onfocus=\"cal4.select(document.forms['new_profile'].birthdate,'anchor1','$LANG[CAL_FORMAT]'); return false;\" type='text' name='birthdate' value='$birthdate' size='10' readonly='readonly'/>
					$calendardiv
				</div>
			</div>
			<div class='row'>
				<div class='cell right'>$LANG[PROMPT_LOCATION]:</div>
				<div class='cell'><input class='bselect' type='text' name='location' size='20' maxlength='20' value=\"$location\" /></div>
			</div>";
			}
			if ($recaptcha) {
				$thisContentObj->primaryContent .= "
				<div class='row'>
					<div class='cell'></div>
					<div class='cell'>";
				require_once('recaptchalib.php');
				$thisContentObj->primaryContent .= recaptcha_get_html($recaptcha_pubKey)."</div>
				</div>";
			}
			$thisContentObj->primaryContent .= "
			<div class='row'>
				<div class='cell'></div>
				<div class='cell'>
					<input id='submitnewNick' class='button' style='font-size:1.2em;border:2px solid red;' type='submit' value=\"$LANG[SUBMIT]\" />
				</div>
			</div>";
			if (!$fbemail)
				$thisContentObj->primaryContent .= "
			<div class='row'>
				<div class='cell'><span class='bold'>*</span> <span style='font-style:italic;'>$LANG[MANDATORY_FIELD]</span></div>
				<div class='cell'></div>
			</div>
		</div></form>";
			$thisContentObj->primaryContent .= "<script language=\"JavaScript\" type=\"text/javascript\">verif_all();</script>";
			$shardContentArray[] = $thisContentObj;
			
    }
    break;
     
	case "asso_fb": {
		if (is_numeric($_REQUEST['id'])) {
			$verif = mf_query("SELECT ID, username FROM users WHERE ID = '$_REQUEST[id]' AND email = '$fbemail' LIMIT 1");
			if ($verif = mysql_fetch_assoc($verif)) {
				mf_query("UPDATE users SET facebookID = '$fbuserID' WHERE ID = '$_REQUEST[id]' LIMIT 1");
			}
			header("Location: ".make_link($siteSettings["defaultShard"]));
			exit();			
		}
	}
	break;

    case "g_success": {

			//------------------------------------------------------------------------------
			// Create contentObj for this content object
			//------------------------------------------------------------------------------
			$thisContentObj = New contentObj;
			$thisContentObj->contentType = "generic";
    		$thisContentObj->primaryContent = "$LANG[CREATE_USER_SUCCESS]: <b>" . $_REQUEST['user'] . ".</b><br/>  ";
			if ($siteSettings['verifyEmail'] == "checked")
				$thisContentObj->primaryContent .= "  <b>$LANG[ACCOUNT_NOT_ACTIVATED].</b>";
			//------------------------------------------------------------------------------
			// Add this contentObject to the shardContentArray
			//------------------------------------------------------------------------------
			$shardContentArray[] = $thisContentObj;   
	}		
    break;
    
    case "add_user_fb": {
     
		$user = make_var_safe(htmlspecialchars($_POST["user"]));

		if ($user == "") {
			header("Location: ".make_link("adduser","&action=g_error&errormsg=6"));
			exit();
		}

		// Check for existing username
		$result = mf_query("select count(*) from users where LOWER(username) =\"".mb_strtolower($user,'UTF-8')."\"");
		$result = mysql_fetch_row($result);
		if ($result[0] > 0) {
			header("Location: ".make_link("adduser","&action=g_error&errormsg=3"));
			exit();
		}

		// add entry into database
		$datejoined = time();
		$password = sha1(generatePassword());
		$avatar_path = "";
		
		$verifyEmail = "NULL";
		
		mf_query("insert into users (username, password, email, rating, datejoined, userstatus, avatar, lang, facebookID) values (\"$user\", '$password', '$fbemail', 0, $datejoined, $verifyEmail, '$avatar_path', '$siteSettings[lang]', '$fbuserID' )");
		
		$userID = mf_query("select ID from users where datejoined=$datejoined and username=\"$user\" limit 1");
		if ($row = mysql_fetch_array($userID)) {		
			mf_query("insert into forum_user_nri (name, userID) values (\"$user\", $row[ID])");
			
			header("Location: ".make_link("adduser","&action=g_success&user=$user"));
			exit();			
		}
    }
    break;

	
    case "g_confirm":
    	
    	if (is_numeric($_REQUEST['i']))
    	{
			
			//------------------------------------------------------------------------------
			// Create contentObj for this content object
			//------------------------------------------------------------------------------
			$thisContentObj = New contentObj;
			$thisContentObj->contentType = "generic";
			$thisContentObj->title="$LANG[ACCOUNT_CONFIRM_TITLE]";
			
			
			$check = mf_query("select verifystring from verify where userID=$_REQUEST[i] limit 1");
			if ($row=mysql_fetch_array($check))
			{
				
				if ($row['verifystring'] == $_REQUEST['v'])
				{
					// update user account to be verified
					$update = mf_query("update users set userstatus=NULL where ID=$_REQUEST[i] limit 1");
					$update = mf_query("delete from verify where userID=$_REQUEST[i] limit 1");
					$thisContentObj->primaryContent = "$LANG[ACCOUNT_CONFIRM_SUCCESS].";
				}
				else
					$thisContentObj->primaryContent = "$LANG[ACCOUNT_CONFIRM_FAILURE].";
					
			}
			else
				$thisContentObj = "$LANG[ACCOUNT_CONFIRM_FAILURE]";
			
			$shardContentArray[] = $thisContentObj;
		}
    
    
    break;

/// send again activation mail
	
    case "g_resendauthent":

	$thisContentObj = New contentObj;
	$thisContentObj->contentType = "generic";
	$thisContentObj->title = $LANG['MAIL_ACTIVATION10'];
	$thisContentObj->primaryContent = "<table>";
	$thisContentObj->primaryContent .= "<form action='index.php?shard=adduser&amp;action=g_resendmail' method='post'>
					<tr>
					<td><b>$LANG[MAIL_ACTIVATION11] </b><input type='text' name=\"user\" size='13' maxlength='13' /></td>
					<td><b>$LANG[MAIL_ACTIVATION12] </b><input type='text' name='email' size='35' /></td></tr>
					<tr><td><input type='submit' value=\"$LANG[SUBMIT]\" class='button' /></td></tr>";

	$thisContentObj->primaryContent .= "</form></table>";

	$shardContentArray[] = $thisContentObj;

	break;
	
case "g_resendmail": {
	
	$thisContentObj = New contentObj;
	$thisContentObj->contentType = "generic";

	$user = make_var_safe( $_POST['user']);
	$email = make_var_safe( $_POST['email']);
	if ($user == "" || $email == "") {
		$thisContentObj->title = $LANG['MAIL_ACTIVATION14'];
		$thisContentObj->primaryContent = "<input type='button' class='button' value=\"$LANG[BUTTON_BACK]\" onclick='history.back()' />";
		$shardContentArray[] = $thisContentObj;
		break;
	}
	
	$userID = mf_query("select ID, email, userstatus, username from users where LOWER(username) =\"".mb_strtolower($user,'UTF-8')."\" limit 1");
	if ($row = mysql_fetch_array($userID)) {
		if ($row['email'] != $email) {
			$thisContentObj->title = $LANG['MAIL_ACTIVATION13'];
			$thisContentObj->primaryContent = "<input type='button' class='button' value=\"$LANG[BUTTON_BACK]\" onclick='history.back();' />";
			$shardContentArray[] = $thisContentObj;
			break;
		}
		if ($row['userstatus'] == NULL) {
			$thisContentObj->title = $LANG['MAIL_ACTIVATION15'];
			$thisContentObj->primaryContent = "<input type='button' class='button' value=\"$LANG[BUTTON_BACK]\" onclick='history.back();' />";
			$shardContentArray[] = $thisContentObj;
			break;
		}

		// delete old verify string
		$check = mf_query("select verifystring from verify where userID=$row[ID] limit 1");
		if ($check=mysql_fetch_array($check))
			$update = mf_query("delete from verify where userID=$row[ID] limit 1");

		// add new verify sting
		$vstring = SHA1(time());
		$verifyInsert = mf_query("insert into verify (userID, verifystring) VALUES ($row[ID], '". $vstring ."')");

		srand((double)microtime()*1000000);
		$boundary = md5(uniqid(rand()));
		$header ="From: $siteSettings[titlebase] <$siteSettings[admin_mail]>\n";
		$header .="Reply-To: $siteSettings[titlebase] \n";
		$header .="MIME-Version: 1.0\n";
		$header .="Content-Type: multipart/alternative;boundary=$boundary\n";

		$message = "\nThis is a multi-part message in MIME format.";
		$message .="\n--" . $boundary . "\nContent-Type: text/html;charset=\"utf-8\"\n\n";
		$message .= "<html><body>\n";
		$message .="<img src='http://" . $siteSettings['siteurl'] . "/engine/grafts/" . $siteSettings['graft'] . "/images/MailheaderImage.png'>\n";
		$message .= "\n<br/><h3>" . $LANG['MAIL_ACTIVATION9'] . "</h3><br/>\n";
		$message .= $LANG['MAIL_ACTIVATION92'] . " <i><b>" . $row['username'] . "</b></i> " . $LANG['MAIL_ACTIVATION3'];
		$message .= $siteSettings['titlebase'] . ".<br/><br/>" . $LANG['MAIL_ACTIVATION4'];
		$message .= "<a href=\"http://" . $siteSettings['siteurl'] . $_SERVER['PHP_SELF'] . 
				"?shard=adduser&action=g_confirm&i=" . $row['ID'] . "&v=" . $vstring . "\">";
		$message .= $LANG['MAIL_ACTIVATION5'] . "</a>" . $LANG['MAIL_ACTIVATION93'] . "<br/>\n\n";
		$message .= "http://" . $siteSettings['siteurl'] . $_SERVER['PHP_SELF'];
		$message .= "?shard=adduser&action=g_confirm&i=" . $row['ID'] . "&v=" . $vstring;
		$message .= "<br/><br/><br/>\n\n" . $LANG['MAIL_ACTIVATION7'] . "\n<br/><br/>" . $siteSettings['titlebase'];
		$message .="\n--" . $boundary . "--\n end of the multi-part";

		mail($email, "$siteSettings[titlebase] - $LANG[MAIL_ACTIVATION91]", $message, $header) or die('Could not send mail');


		$thisContentObj->primaryContent = "$LANG[MAIL_ACTIVATION16]: <b>" . $user . ".<br/>  $LANG[ACCOUNT_NOT_ACTIVATED].</b>";
		$shardContentArray[] = $thisContentObj;
	}
}
break;

// Suppression d'un compte utilisateur
case "g_remove":

	if($_REQUEST['ID'] == $CURRENTUSERID) {
		if (isset($_POST['delete_conf'])) {
	    	mf_query("delete from fhits where userID = '$CURRENTUSERID'");
			mf_query("delete from blog where userID = '$CURRENTUSERID'");
			mf_query("delete from permissiongroups where username = \"$CURRENTUSER\"");
			mf_query("delete from albums_users where userID = '$CURRENTUSERID'");
			mf_query("delete from albums_users where albumID IN (select ID from albums where userID = '$CURRENTUSERID')");
			mf_query("delete from albums_topics where albumID IN (select ID from albums where userID = '$CURRENTUSERID')");
			mf_query("delete from albums where userID = '$CURRENTUSERID'");
			$query_image = mf_query("SELECT name, name_thumb FROM pictures WHERE userID='$CURRENTUSERID'");
			while ($image = mysql_fetch_array($query_image)) {
				unlink($image['name']);
				unlink($image['name_thumb']);
			}
			mf_query("delete from pictures where userID = '$CURRENTUSERID'");

			$user_posted = mf_query("select ID from forum_posts where userID='$CURRENTUSERID' limit 1");
			if ($row = mysql_fetch_array($user_posted))
				mf_query("UPDATE users SET userstatus = 'DELETED', password = 'USER_DELETED', birthdate='', realname='', sexe='', location='', website='', IM='', profile='', introducethread='', rating='0', avatar='', sig='' WHERE ID = '$CURRENTUSERID' limit 1");
			else {
				mf_query("delete from users where ID = '$CURRENTUSERID' limit 1");
				mf_query("delete from forum_user_nri where userID = '$CURRENTUSERID' limit 1");
			}

			setcookie("b6".$siteSettings['db']."username", "", time()-3600, "/");
			setcookie("b6".$siteSettings['db']."userID", "", time()-3600, "/");
			setcookie("b6".$siteSettings['db']."password", "", time()-3600, "/");

			header("Location: ".make_link("adduser","&action=g_remove_confirmed"));

		}
		else
			header("Location: ".make_link("profile","&action=g_remove_user_confirm&filter=2&filter2=6"));
	}
break;

// Suppression d'un compte utilisateur confirmée
case "g_remove_confirmed":

		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->primaryContent = "<center><h3>$LANG[ACCOUNT_CONFIRM_DELETE]</h3></center>";
		$shardContentArray[] = $thisContentObj;
break;

endswitch;

?>