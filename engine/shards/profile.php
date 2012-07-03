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

// profile.php
// PROFILE tab

require("forumlib.php");
require("profilelib.php");
require("user_profilelib.php");
require("cryptpasslib.php");

if ($CURRENTUSER == "bot")
	header("Location: ".make_link("forum"));


if($CURRENTUSER == "anonymous" || $CURRENTUSER == "")
   	$menuStr = "<div class='subMenu' style='padding-bottom:10px;'><div class='subMenuLine1'><b>$LANG[PLEASE_CONNECT]</b></div></div>";
else {
	if (!array_key_exists('filter', $_REQUEST))
		$_REQUEST['filter'] = "2";
	$menuStr = "<div class='subMenu' style='padding-bottom:10px;'><div class='subMenuLine1'>";

	$menuStr .= "<a href='".make_link("profile","&amp;action=g_ep&amp;filter=2")."' style='margin-left: 5px;' class='threadType".checkSelectedP('2')."' title='$LANG[EDIT_PROFILE]'>$LANG[EDIT_PROFILE]</a>";
	if($CURRENTSTATUS != "banned")
		$menuStr .= "<a href='".make_link("profile","&amp;action=g_settings&amp;filter=3")."' style='margin-left: 5px;' class='threadType".checkSelectedP('3')."' title='$LANG[FORUM_SETTINGS]'>$LANG[FORUM_SETTINGS]</a>";
	if($CURRENTSTATUS != "banned")
		$menuStr .= "<a href='".make_link("profile","&amp;action=g_tags_list&amp;filter=7")."' style='margin-left: 5px;' class='threadType".checkSelectedP('7')."' title=\"$LANG[MY_TAGS]\">$LANG[MY_TAGS]</a>";
	if($CURRENTSTATUS != "banned")
		$menuStr .= "<a href='".make_link("profile","&amp;action=g_pictures&amp;filter=6")."' style='margin-left: 5px;' class='threadType".checkSelectedP('6')."' title=\"$LANG[PICTURES_ALBUM_TAB]\">$LANG[PICTURES_ALBUM_TAB]</a>";
	if($CURRENTSTATUS != "banned")
		$menuStr .= "<a href='".make_link("forum","&amp;action=g_users&amp;userf=al")."' style='margin-left: 5px;' class='threadType".checkSelectedP('5')."' title='$LANG[USERS_LIST]'>$LANG[USERS_LIST]</a>";
	if(isInGroup($CURRENTUSER, 'admin'))
		$menuStr .= "<a href='".make_link("admin")."' style='margin-left: 5px;' class='threadType' title='$LANG[ADMIN_CONTROL_PANEL]'>$LANG[ADMIN_CONTROL_PANEL]</a>";

	$menuStr .= "</div></div>";

	if (!array_key_exists('filter2', $_REQUEST))
		$_REQUEST['filter2'] = "1";
	$menuStr2 = "<div class='subMenu' style='padding-bottom:10px;'><center><div class='subMenuLine2' style='margin-top:7px;'>";
	$menuStr2 .= "<a href='".make_link("profile","&amp;action=g_ep&amp;filter=2&amp;filter2=1")."' style='margin-left: 5px;' class='threadType2".checkSelected2('1')."' title='$LANG[USER_PROFILE_GLIMPSE]'><b>$LANG[USER_PROFILE_GLIMPSE]</b></a>";
	$menuStr2 .= "<span style='margin-left: 60px;'><b> $LANG[MODIFY]</b></span>";
	if($CURRENTSTATUS != "banned")
		$menuStr2 .= "<a href='".make_link("profile","&amp;action=g_edit_profile&amp;filter=2&amp;filter2=2")."' style='margin-left: 5px;' class='threadType2".checkSelected2('2')."' title='$LANG[USER_PROFILE_PERSONAL_INFO]'><b>$LANG[USER_PROFILE_PERSONAL_INFO_SHORT]</b></a>";
	if($CURRENTSTATUS != "banned")
		$menuStr2 .= "<a href='".make_link("profile","&amp;action=g_change_avatar&amp;filter=2&amp;filter2=3")."' style='margin-left: 5px;' class='threadType2".checkSelected2('3')."' title='$LANG[AVATAR]'><b>$LANG[AVATAR]</b></a>";
	if($CURRENTSTATUS != "banned")
		$menuStr2 .= "<a href='".make_link("profile","&amp;action=g_edit_sig&amp;filter=2&amp;filter2=4")."' style='margin-left: 5px;' class='threadType2".checkSelected2('4')."' title='$LANG[PROMPT_SIG]'><b>$LANG[PROMPT_SIG]</b></a>";
	$menuStr2 .= "<a href='".make_link("profile","&amp;action=g_change_passwd&amp;filter=2&amp;filter2=5")."' style='margin-left: 5px;' class='threadType2".checkSelected2('5')."' title='$LANG[PASSWORD]'><b>$LANG[PASSWORD]</b></a>";
	$menuStr2 .= "<a href='".make_link("profile","&amp;action=g_remove_user_confirm&amp;filter=2&amp;filter2=6")."' style='margin-left: 5px;' class='threadType2".checkSelected2('6')."' title='$LANG[DELETE]'><b>$LANG[DELETE_USER_BUTTON]</b></a>";
	$menuStr2 .= "</div></center></div>";
	
}

    switch ($action):
    
    
    case "g_default":
if ($CURRENTUSER != "anonymous") {
		$thisContentObj = New contentObj;
		$thisContentObj->primaryContent = $menuStr;
		$thisContentObj->primaryContent .= $menuStr2;

		$thisContentObj->primaryContent .= "<div id='contentU'>".userprofile($CURRENTUSERID,true)."</div>";

		$shardContentArray[] = $thisContentObj;
    }
    break;
    
    case "g_remove_user_confirm":
	if($CURRENTUSER != "anonymous") {
    	$userInfo = mf_query("select * from users where username=\"$CURRENTUSER\"");
    	$u = mysql_fetch_assoc($userInfo);
    
		//------------------------------------------------------------------------------
		// Create contentObj for this content object
		//------------------------------------------------------------------------------
		$thisContentObj = New contentObj;
		$thisContentObj->primaryContent = $menuStr;
		$thisContentObj->primaryContent .= $menuStr2;
		$thisContentObj->primaryContent .= "<div style='height:20px'></div>";
		$thisContentObj->primaryContent .= "<center><h2>$LANG[DELETE_ACCOUNT_TITLE]</h2></center>";
		$thisContentObj->primaryContent .= "<div style='height:20px'></div>";
		$thisContentObj->primaryContent .= "<center><h3>$LANG[DELETE_ACCOUNT_WARNING]</h3></center>";
		$thisContentObj->primaryContent .= "<br/>$LANG[DELETE_ACCOUNT_TEXT]";
		$thisContentObj->primaryContent .= "<div style='height:20px'></div>";
		$thisContentObj->primaryContent .= "<center><form name='deleteconf' action=\"".make_link("adduser","&amp;action=g_remove&amp;ID=$CURRENTUSERID")."\" method='post'>
				<table>
				<tr><td style='text-align:right;'>$LANG[DELETE_ACCOUNT_CONFIRM_CHECKBOX]:</td>
				<td><input type='checkbox' name='delete_conf' class='bselect' /></td>
				<td>&nbsp;<input type='submit' class='button' value=\"$LANG[DELETE_ACCOUNT_CONFIRM_BUTTON]\" />

				</td></tr></table>
				</form></center>";

		$shardContentArray[] = $thisContentObj;
	}	
	break;

	case "g_edit_profile":
if($CURRENTUSER != "anonymous" and $CURRENTUSER != "" and $CURRENTSTATUS != "banned") {
			$calendardiv = "<img src='engine/grafts/$siteSettings[graft]/images/calendar.png' style='vertical-align:middle;margin-bottom:1px;' alt='' /></a><div id='calendardiv' style='position:absolute;visibility:hidden;background-color:white;'></div>";

			
			$userInfo = mf_query("select * from users where username=\"$CURRENTUSER\"");
			$u = mysql_fetch_assoc($userInfo);

			$thisContentObj = New contentObj;
			$thisContentObj->primaryContent = $menuStr;
			$thisContentObj->primaryContent .= $menuStr2;
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

			$birthdate = "";
			if ($u['birthdate'])
				$birthdate = date($LANG['DATE_LINE_MINIMAL2'],$u['birthdate']);
			$thisContentObj->primaryContent .= "<form name='edit_profile' action='".make_link("profile","&amp;action=g_submit_profile_edit")."' method='post'>
			<table align='center'>
			<tr><td style='text-align:right;'>$LANG[PROMPT_REAL_NAME]:</td><td>
			<input class='bselect' type='text' name='realname' size='20' maxlength='20' value=\"$u[realname]\" />
			</td></tr><tr><td style='text-align:right;'>$LANG[PROMPT_BIRTH_DATE]:</td><td>
			<input type='text' name='birthdate' value=\"$birthdate\" size='12' class='bselect' readonly='readonly' style='border:0px;'/>
		<a href='#' onclick=\"cal4.select(document.forms['edit_profile'].birthdate,'anchor1','$LANG[CAL_FORMAT]'); return false;\" name='anchor1' id='anchor1'>
		$calendardiv
			$LANG[PROMPT_BIRTH_DATE2]
			</td></tr><tr><td style='text-align:right;'>$LANG[PROMPT_LOCATION]:</td><td>
			<input class='bselect' type='text' name='location' size='20' maxlength='20' value=\"$u[location]\" />
			</td></tr><tr><td style='text-align:right;'>$LANG[PROMPT_EMAIL]:</td><td>
			<input class='bselect' type='text' name='email' size='35' value='$u[email]' />
			</td></tr><tr><td style='text-align:right;'>$LANG[PROMPT_EMAIL2]:</td><td>
			<input class='bselect' type='text' name='email2' size='35' value='$u[email]' />
			</td></tr><tr><td style='text-align:right;'>$LANG[PROMPT_WEBSITE]:</td><td>
			<input class='bselect' type='text' name='website' size='35' value='$u[website]' />
			</td></tr><tr><td style='text-align:right;'>$LANG[PROMPT_IM]:</td><td>
			<input class='bselect' type='text' name='IM' size='35' maxlength='35' value='$u[IM]' />
			</td></tr>";
	if ($u['facebookID']) {
		$fbusername = "";
		if (isset($fbuser->name))
			$fbusername = $fbuser->name;
		$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'>$LANG[FACEBOOK_LINKED]:</td><td>".$fbusername."&nbsp;
			<span class='button_mini' onclick=\"document.location.href='index.php?shard=profile&amp;action=removefacebook';\">$LANG[FACEBOOK_REMOVED]</span>
			</td></tr>";
	}
			$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'>$LANG[PROMPT_PROFILE]:</td><td>
			<textarea class='bselect' name='profile' cols='45' rows='8'>$u[profile]</textarea>
			</td></tr><tr><td></td><td><input class='button' type='submit' value=\"$LANG[SUBMIT]\" /></td></tr>
			</table></form>";
		$shardContentArray[] = $thisContentObj;
		}
		break;

case "removefacebook": {
	if($CURRENTUSER != "anonymous")
		mf_query("UPDATE users SET facebookID = '0' WHERE ID = '$CURRENTUSERID' LIMIT 1");
	
	header("Location: ".make_link("profile"));
}
break;

	case "g_change_avatar":
	if($CURRENTUSER != "anonymous" and $CURRENTUSER != "" and $CURRENTSTATUS != "banned") {
	
		$thisContentObj = New contentObj;
		$thisContentObj->primaryContent = $menuStr;
		$thisContentObj->primaryContent .= $menuStr2;
		$thisContentObj->primaryContent .= "<form name='upload' method='post' action='".make_link("profile","&amp;action=g_change_avatar&amp;filter=2&amp;filter2=3")."' enctype='multipart/form-data'>";
	
		if (array_key_exists('imagefile', $_FILES)) // Check to see if a file is being uploaded...
		{
			// Check if uploaded file is correct file type
			if (($_FILES['imagefile']['type'] == "image/jpeg" || $_FILES['imagefile']['type'] == "image/pjpeg" || $_FILES['imagefile']['type'] == "image/png" || $_FILES['imagefile']['type'] == "image/gif") && in_array(strtolower(substr(strrchr($_FILES['imagefile']['name'], '.'),1)), array('gif', 'jpg', 'jpeg', 'png')))
			{
				if (filesize($_FILES['imagefile']['tmp_name']) > 50000)
				{
					// File is too big.  Tell them to resize it and try again
					$thisContentObj->primaryContent .="<table align='center'><tr><td><h2>$LANG[FILE_TOO_LARGE] (" . filesize($_FILES['imagefile']['tmp_name']) . " bytes)</h2><br/><br/>";
				}
				else
				{
					// File is correct type and size.  Copy it to server and update user's profile accordingly.
					$file_ext_array = explode("." , $_FILES['imagefile']['name']);
					$file_ext = $file_ext_array[ sizeof($file_ext_array) - 1 ];					
					$name_avatar=time();
					
					if (!move_uploaded_file( $_FILES['imagefile']['tmp_name'] , "images/$name_avatar.$file_ext" ))
						$thisContentObj->primaryContent .= "<table align='center'><tr><td><b>$LANG[AV_UP_FAILED]</b><br/><br/>";
					else
					{
						$old_avatar = mf_query("select avatar from users where username=\"$CURRENTUSER\" limit 1");
						$old_avatar = mysql_fetch_assoc($old_avatar);
						if ($old_avatar['avatar'])
							$a = unlink($old_avatar['avatar']);
						mf_query("update users set avatar='images/$name_avatar.$file_ext' where username=\"$CURRENTUSER\"");
						
						$thisContentObj->primaryContent .= "<table align='center'><tr><td><h2>$LANG[NEW_AV]:</h2><img class='profileAvatarHolder' src='images/$name_avatar.$file_ext' alt='Avatar' /><br/><br/>";
					}
				}
			}
			else
			{	
				$thisContentObj->primaryContent .= "<table align='center'><tr><td><h2>$LANG[INCORRECT_FILE_TYPE]: " . $_FILES['imagefile']['type'] . "</h2><br/><br/>";
			}			
		}
		else  // no file was being uploaded, display current avatar
		{
			$thisContentObj->primaryContent .= "<table align='center'><tr><td><h2>$LANG[CURRENT_AVATAR]:</h2>";
			
			$av = mf_query("select avatar from users where username=\"$CURRENTUSER\" limit 1");
			$av = mysql_fetch_assoc($av);
			
			if ($av['avatar'] != "")
				$thisContentObj->primaryContent .= "<img class='profileAvatarHolder' src='$av[avatar]' alt='$LANG[AVATAR]' /><br/><br/>";
				
			else
				$thisContentObj->primaryContent .= "(none)<br/><br/>";
		}
		
		$thisContentObj->primaryContent .= "$LANG[UPLOAD_AVATAR]:
				<input type='hidden' name='upload_flag' value='true' />
				<input type='file' name='imagefile' />&nbsp;
				<br/><br/>
				$LANG[AVATAR_RULES].<br/>
				<input type='submit' name='Submit' value=\"$LANG[SUBMIT]\" class='button' />
				</td></tr></table></form>";

		    
		//------------------------------------------------------------------------------
		// Add this contentObject to the shardContentArray
		//------------------------------------------------------------------------------
		$shardContentArray[] = $thisContentObj;
	}	
    break;
    
case "g_edit_sig":
if($CURRENTUSER != "anonymous" and $CURRENTUSER != "" and $CURRENTSTATUS != "banned") {
	$userInfo = mf_query("select * from users where username=\"$CURRENTUSER\"");
	$u = mysql_fetch_assoc($userInfo);
	
	$thisContentObj = New contentObj;
	$thisContentObj->primaryContent = $menuStr;
	$thisContentObj->primaryContent .= $menuStr2;
	$thisContentObj->primaryContent .= "<form action='index.php?shard=profile&amp;action=submit_sig_edit' method='post'>
		<table align='center'>
		<tr><td style='text-align:right;'>$LANG[PROMPT_SIG]:</td><td>
		<textarea class='text' name='sig' cols='30' rows='4'>$u[sig]</textarea>
		</td></tr><tr><td></td><td><input class='button' type='submit' value=\"$LANG[SUBMIT]\" /></td></tr></table></form>";
	$shardContentArray[] = $thisContentObj;
}	
break;

case "g_submit_profile_edit":
if($CURRENTUSER != "anonymous" and $CURRENTUSER != "" and $CURRENTSTATUS != "banned") {
	if (array_key_exists("realname" , $_REQUEST ) == TRUE )
		$realname = make_var_safe(htmlspecialchars($_REQUEST["realname"]));
	if (array_key_exists("birthdate" , $_REQUEST ) == TRUE ) {
		$birthdate = make_var_safe(htmlspecialchars($_REQUEST["birthdate"]));
		$birthdate = str_replace("/","",$birthdate);
		$birthdate = str_replace("-","",$birthdate);
		$birthdate = str_replace(".","",$birthdate);
		$birthdate = str_replace(" ","",$birthdate);
		$birthdate = make_num_safe($birthdate);
		if (strlen($birthdate) == 8) {
			if ($LANG['CAL_FORMAT'] == "dd/MM/yyyy")
				$birthdate = substr($birthdate,4,4).substr($birthdate,2,2).substr($birthdate,0,2);
			else if ($LANG['CAL_FORMAT'] == "yyyy/MM/dd")
				$birthdate = substr($birthdate,0,4).substr($birthdate,4,2).substr($birthdate,6,2);
			$birthdate = strtotime($birthdate);
		}
		else
			$birthdate = "";
	}
	if (array_key_exists("location" , $_REQUEST ) == TRUE )
		$location = make_var_safe(htmlspecialchars($_REQUEST["location"]));
	if (array_key_exists("email" , $_REQUEST ) == TRUE )
		$email = make_var_safe(htmlspecialchars($_REQUEST["email"]));
	if (array_key_exists("email2" , $_REQUEST ) == TRUE )
		$email2 = make_var_safe(htmlspecialchars($_REQUEST["email2"]));
	if (array_key_exists("website" , $_REQUEST ) == TRUE )
		$website = make_var_safe(htmlspecialchars($_REQUEST["website"]));
	if (array_key_exists("IM" , $_REQUEST ) == TRUE )
		$IM = make_var_safe(htmlspecialchars($_REQUEST["IM"]));
	if (array_key_exists("profile" , $_REQUEST ) == TRUE )
		$profile = make_var_safe(htmlspecialchars($_REQUEST["profile"]));

	$verifmail = mf_query("select email from users where username=\"$CURRENTUSER\" limit 1");
	$verifmail = mysql_fetch_assoc($verifmail);
	$userstatus = ", userstatus = NULL";
	$thisContentObj = New contentObj;
	
	mf_query("update users set realname='$realname', birthdate='$birthdate', location='$location', website='$website', IM='$IM', profile='$profile' where username=\"$CURRENTUSER\"");
	
	if ($email != $email2)
	{
		$thisContentObj->primaryContent = "<h3>$LANG[CHANGE_MAIL5]</h3>";
		$thisContentObj->primaryContent .= "<a href='".make_link("profile","&amp;action=g_edit_profile&amp;filter=2&amp;filter2=2")."' class=button>$LANG[BUTTON_BACK]</a>";
		$shardContentArray[] = $thisContentObj;
	}
	else if ((!isInGroup($CURRENTUSER, 'admin') and !isInGroup($CURRENTUSER, 'level4') and !isInGroup($CURRENTUSER, 'level3') and !isInGroup($CURRENTUSER, 'level1') and !isInGroup($CURRENTUSER, 'level2')) && ($email == ""))
	{
		$thisContentObj->primaryContent = "<h3>$LANG[CHANGE_MAIL4]</h3>";
		$thisContentObj->primaryContent .= "<a href='".make_link("profile","&amp;action=g_edit_profile&amp;filter=2&amp;filter2=2")."' class=button>$LANG[BUTTON_BACK]</a>";
		$shardContentArray[] = $thisContentObj;
	}
	else if ($email != $verifmail['email'])
	{
		if (!isInGroup($CURRENTUSER, 'admin') and !isInGroup($CURRENTUSER, 'level4') and !isInGroup($CURRENTUSER, 'level3') and !isInGroup($CURRENTUSER, 'level1') and !isInGroup($CURRENTUSER, 'level2'))
		{
			if (!strpos($email,"@") or !strpos($email,".") or strpos($email,",") or strpos($email," ") or strpos($email,";"))
			{
				header("Location: ".make_link("adduser","&action=g_error7"));
				exit();
			}
			if (strpos($email,":") or strpos($email,"(") or strpos($email,")") or strpos($email,"[") or strpos($email,"]"))
			{
				header("Location: ".make_link("adduser","&action=g_error7"));
				exit();
			}
			if (stristr($email,"@mailinator") 
				or stristr($email,"@mailbidon") 
				or stristr($email,"@mailincubato") 
				or stristr($email,"@yopmail") 
				or stristr($email,"@spamgourmet") 
				or stristr($email,"@ephemail") 
				or stristr($email,"@brefemail") 
				or stristr($email,"@kleemail") 
				or stristr($email,"@haltospam") 
				or stristr($email,"@guerrillamail") 
				or stristr($email,"@kasmail") 
				or stristr($email,"@dodgit") 
				or stristr($email,"@pookmail") 
				or stristr($email,"@bugmenot") 
				or stristr($email,"@jetable")
				or stristr($email,"@tempomail")
				or stristr($email,"@0-mail.com")
				or stristr($email,"@brefmail.com")
				or stristr($email,"@tempinbox.com")
				or stristr($email,"@beefmilk.com")
				or stristr($email,"@lookugly.com")
				or stristr($email,"@link2mail")
				or stristr($email,"@spambox")
				or stristr($email,"@smellfear.com")) {
				header("Location: ".make_link("adduser","&action=g_error_mail"));
				exit();
			}
		
			$thisContentObj->primaryContent = "<h3>$LANG[CHANGE_MAIL1]</h3> $LANG[CHANGE_MAIL2]";
			$thisContentObj->primaryContent .= "<center><h3>$LANG[CHANGE_MAIL3]</h3>";
			$thisContentObj->primaryContent .= "<form name='upload' method='post' action='".make_link("profile","&amp;action=g_change_email&amp;filter=2&amp;filter2=2")."' enctype='multipart/form-data'>
				Ancienne adresse : $verifmail[email]<br/>
				Nouvelle adresse : <input type='hidden' name='email' value='$email' />$email<br/>
				<input class='button' type='submit' value=\"$LANG[YES]\" /> 
				&nbsp;<a href='".make_link("profile","&amp;action=g_edit_profile&amp;filter=2&amp;filter2=2")."' class=button>$LANG[NO]</a>
				</form></center>";
			$shardContentArray[] = $thisContentObj;
		}
		else
		{
			mf_query("update users set email='$email' where username=\"$CURRENTUSER\" limit 1");
			header("Location: ".make_link("profile","&action=g_success"));
		}
	}
	else
		header("Location: ".make_link("profile","&action=g_success"));
}
break;

case "g_change_email":
if($CURRENTUSER != "anonymous" and $CURRENTUSER != "" and $CURRENTSTATUS != "banned") {
	if (array_key_exists("email" , $_REQUEST ) == TRUE )
		$email = make_var_safe(htmlspecialchars($_REQUEST["email"]));

	$thisContentObj = New contentObj;
	
	mf_query("update users set email='$email', userstatus='pending' where username=\"$CURRENTUSER\" limit 1");

	if ($siteSettings['verifyEmail'] == "checked") {
		$vstring = SHA1(time());
		mf_query("delete from verify where userID = '$CURRENTUSERID'");
		$verifyInsert = mf_query("insert into verify (userID, verifystring) VALUES ($CURRENTUSERID, '". $vstring ."')");
		srand((double)microtime()*1000000);
		$boundary = md5(uniqid(rand()));
		$header ="From: $siteSettings[titlebase] <$siteSettings[admin_mail]>\n";
		$header .="Reply-To: $siteSettings[admin_mail] \n";
		$header .="MIME-Version: 1.0\n";
		$header .="Content-Type: multipart/alternative;boundary=$boundary\n";
		$message = "\nThis is a multi-part message in MIME format.";
		$message .="\n--" . $boundary . "\nContent-Type: text/html;charset=\"iso-8859-1\"\n\n";
		$message .= "<html><body>\n";
		$message .="<img src='http://" . $siteSettings['siteurl'] . "/engine/grafts/" . $siteSettings['graft'] . "/images/MailheaderImage.png' alt='Picture' />\n";
		$message .= "\n<br/><h3>" . $LANG['CHANGE_MAIL7'] . "</h3><br/>\n";
		$message .= $LANG['CHANGE_MAIL8'] . " <i><b>" . $user . "</b></i> " . $LANG['MAIL_ACTIVATION3'];
		$message .= $siteSettings['titlebase'] . ".<br/><br/>" . $LANG['MAIL_ACTIVATION4'];
		$message .= "<a href=\"http://" . $siteSettings['siteurl'] . $_SERVER['PHP_SELF'] . "?shard=adduser&action=g_confirm&i=" . $CURRENTUSERID . "&v=" . $vstring . "\">";
		$message .= $LANG['MAIL_ACTIVATION5'] . "</a>" . $LANG['CHANGE_MAIL9'] . "<br/>\n\n";
		$message .= "http://" . $siteSettings['siteurl'] . $_SERVER['PHP_SELF'];
		$message .= "?shard=adduser&action=g_confirm&i=" . $CURRENTUSERID . "&v=" . $vstring;
		$message .= "<br/><br/><br/>\n\n" . $LANG['MAIL_ACTIVATION7'] . "\n<br/><br/>" . $siteSettings['titlebase'];
		$message .="\n--" . $boundary . "--\n end of the multi-part";

		mail($email, "$siteSettings[titlebase] $LANG[CHANGE_MAIL7]", $message, $header) or die('Could not send mail');
	}
	$thisContentObj->primaryContent = "<h3>$LANG[CHANGE_MAIL6]</h3>";
	$thisContentObj->primaryContent .= "<h3>$LANG[ACCOUNT_NOT_ACTIVATED]</h3>";
	$shardContentArray[] = $thisContentObj;
}
break;

case "submit_sig_edit":
if($CURRENTUSER != "anonymous" and $CURRENTUSER != "" and $CURRENTSTATUS != "banned") {
	if (array_key_exists("sig" , $_REQUEST ) == TRUE )
		$sig = make_var_safe(htmlspecialchars($_REQUEST["sig"]));	        

	$result = mf_query("update users set sig='$sig' where username=\"$CURRENTUSER\"");
	header("Location: ".make_link("profile","&action=g_success"));
}
break;

case "g_success": {

	//------------------------------------------------------------------------------
	// Create contentObj for this content object
	//------------------------------------------------------------------------------
	$thisContentObj = New contentObj;
	$thisContentObj->primaryContent = $menuStr;
	$thisContentObj->primaryContent .= "<br/><br/><div style='width:100%;text-align:center;'><h2>$LANG[CHANGES_SAVED]</h2></div><br/>";
	//------------------------------------------------------------------------------
	// Add this contentObject to the shardContentArray
	//------------------------------------------------------------------------------
	$shardContentArray[] = $thisContentObj;
}
break;   

case "g_settings":
if($CURRENTUSER != "anonymous" and $CURRENTUSER != "" and $CURRENTSTATUS != "banned") {

	$userInfo = mf_query("SELECT * FROM users WHERE ID='$CURRENTUSERID' LIMIT 1");
	$u = mysql_fetch_assoc($userInfo);
	$checked = "";
	$checkedmail_alert = "";
	if ($u['mail_alert'])
		$checkedmail_alert = "checked='checked'";
	$checkedpm_alert = "";
	if ($u['pm_alert'])
		$checkedpm_alert = "checked='checked'";
	$checkedsound_alert = "";
	if ($u['sound_alert'])
		$checkedsound_alert = "checked='checked'";
	$checkedflood = "";
	if ($u['flood'])
		$checkedflood = "checked='checked'";
	$checkedfboff = "";
	if ($u['facebook_disabled'])
		$checkedfboff = "checked='checked'";
	$checkedhidemyself = "";
	if ($u['hidemyself'])
		$checkedhidemyself = "checked='checked'";
	$checkedhidemyteams = "";
	if ($u['hidemyteams'])
		$checkedhidemyteams = "checked='checked'";
	$checkedstillsmilies = "";
	if (isset($_COOKIE['stillSmilies']))
		$checkedstillsmilies = "checked='checked'";
	if ($u['lang'] == "")
		$u['lang'] = $siteSettings['lang'];
	$handle = opendir( "engine/core/lang/" );
	$langlist = "";
	while (($langfile = readdir( $handle )) != FALSE ) {
		if ( $langfile != "." && $langfile != ".." && $langfile != ".svn") {
			$selected = "";
			$langfile = str_replace(".php", "", $langfile);
			if ($u['lang'] == $langfile)
				$selected = "selected='selected'";
			$langlist .= "<option $selected value=\"$langfile\">$langfile</option>";
		}
	} 																		

	if ($u['graft'] == "")
		$u['graft'] = $siteSettings['graft'];
	$handle = opendir( "engine/grafts/" );
	$graftlist = "";
	while (($graftfile = readdir( $handle )) != FALSE ) {
		if ($graftfile != "." && $graftfile != ".." && substr($graftfile,-4,4) != ".php" && $graftfile != "core-styles" && substr($graftfile,-7,7) != "_mobile") {
			$selected = "";
			if ($u['graft'] == $graftfile)
				$selected = "selected='selected'";
			$graftlist .= "<option $selected value=\"$graftfile\">$graftfile</option>";
		}
	}

	$add_to_pt = "<select name='add_to_pt' class='bselect'>";
	$selected = "";
	if ($u['accept_pm_from'] == "0")
		$selected = "selected='selected'";
	$add_to_pt .= "<option value='0' $selected>$LANG[FORUM_SETTING_PT_ALL]</option>";
	$selected = "";
	if ($u['accept_pm_from'] == "1")
		$selected = "selected='selected'";
	$add_to_pt .= "<option value='1' $selected>$LANG[FORUM_SETTING_PT_FRIENDS_OF]</option>";
	$selected = "";
	if ($u['accept_pm_from'] == "2")
		$selected = "selected='selected'";
	$add_to_pt .= "<option value='2' $selected>$LANG[FORUM_SETTING_PT_FRIENDS]</option>";
	$add_to_pt .= "</select>";

	$thisContentObj = New contentObj;
	$thisContentObj->primaryContent = $menuStr;
	$thisContentObj->primaryContent .= "<form action='index.php?shard=profile&amp;action=submit_settings' method='post'>
								<table align='center'>";

	$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'><h3>$LANG[WIDGETS]</h3></td><td></td></tr>";
	$thisContentObj->primaryContent .= "
								<tr>
								<td style='text-align:right;'>$LANG[RESET_SHARDS]:</td>
								<td><span onclick='restoreShards();' class='button_mini''>$LANG[RESET]</span></td>
								</tr>";

	$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'><h3>$LANG[SETTINGS_ALERT_TITLE]</h3></td><td></td></tr>";
	$thisContentObj->primaryContent .= "
								<tr>
								<td style='text-align:right;'>$LANG[FORUM_SETTING_ALERT_NEW]</td>
								<td><input class='cselect' type='checkbox' name='mail_alert' $checkedmail_alert /></td>
								</tr>
								<tr>
								<td style='text-align:right;'>$LANG[FORUM_SETTING_ALERT_PM]</td>
								<td><input class='cselect' type='checkbox' name='pm_alert' $checkedpm_alert /></td>
								</tr>									
								<tr>
								<td style='text-align:right;'>$LANG[SOUND_ALERT]</td>
								<td><input class='cselect' type='checkbox' name='sound_alert' $checkedsound_alert /></td>
								</tr>";

	$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'><h3>$LANG[SETTINGS_MISC_TITLE]</h3></td><td></td></tr>";
	$thisContentObj->primaryContent .= "
								<tr><td style='text-align:right'>$LANG[LANGUAGE]:</td>
								<td><select class='bselect' name='lang'>$langlist</select></td></tr>";
//	if (isInGroup($CURRENTUSER, 'admin'))
	$thisContentObj->primaryContent .= "
								<tr><td style='text-align:right'>$LANG[GRAFT]:</td>
								<td><select class='bselect' name='graft'><option value='0'>$LANG[DEFAULT_GRAFT]</option>$graftlist</select></td></tr>";
		$thisContentObj->primaryContent .= "<tr>
									<td style='text-align:right;'>$LANG[SMILIES_STILL]</td>
									<td><input class='cselect' type='checkbox' name='smilies' $checkedstillsmilies /></td>
									</tr>";

	$thisContentObj->primaryContent .= "
								<tr><td style='text-align:right;'>$LANG[POSTS_PER_PAGE]:</td>
								<td>
								<input class=cselect size=5 type=text name='posts_per_page' value='$u[posts_per_page]'>
								</td></tr>";
	if ($CURRENTUSERRULES == "2" && $siteSettings['rules'])
		$thisContentObj->primaryContent .= "<tr>
								<td style='text-align:right;'>$LANG[RULES]:</td>
								<td><a href='".make_link("forum","&amp;action=g_reset_rules")."' class='button_mini'>$LANG[RULES_VIEW]</a>
								</tr>";
	if ($siteSettings['flood_ID'])
		$thisContentObj->primaryContent .= "<tr>
								<td style='text-align:right;'>$LANG[FORUM_SETTING_FLOOD]</td>
								<td><input class='cselect' type='checkbox' name='flood' $checkedflood /></td>
								</tr>";
	$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'>$LANG[BURIED_THRESHOLD]:</td>
								<td><input class='cselect' size='3' type='text' name='dtt' value='$u[dtt]'/></td>
								</tr>";
	$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'>$LANG[HIDDEN_THRESHOLD]:</td>
								<td><input class='cselect' size='3' type='text' name='dtp' value='$u[dtp]'/></td>
								</tr>";
	$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'>$LANG[GOOGLE_CHROME_ONLY]:</td>
								<td><span onclick=\"allowNotification()\" class='button_mini'>$LANG[ALLOW_NOTIFICATIONS]</span> &nbsp; $LANG[CLOSE_NOTIFICATIONS_AFTER]<input type='text' value='$u[notify_lenght]' name='notify_lenght' size='2' class='bselect'/> $LANG[CLOSE_NOTIFICATIONS_AFTER_SEC]</td></tr>";
	$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'>$LANG[FACEBOOK_OFF]:</td>
								<td><input class='cselect' type='checkbox' name='fb_off' $checkedfboff /></td></tr>";

								$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'><h3>$LANG[SETTINGS_PRIVATE_TITLE]</h3></td><td></td></tr>";
	$thisContentObj->primaryContent .= "
								<tr><td style='text-align:right;'>$LANG[FORUM_SETTING_PT]:</td>
								<td>$add_to_pt
								</td></tr>
								<tr><td style='text-align:right;'>$LANG[FORUM_SETTING_PRESENT]:</td>
								<td><input class='cselect' size='5' type='text' name='introducethread' value='$u[introducethread]' />
								</td></tr>
								<tr><td style='text-align:right;'>$LANG[SETTINGS_HIDE_MYSELF]:</td>
								<td><input class='cselect' type='checkbox' name='hidemyself' $checkedhidemyself /></td>
								</tr>";
	$thisContentObj->primaryContent .= "<tr><td style='text-align:right;'>$LANG[SETTINGS_HIDE_MYTEAMS]:</td>
								<td><input class='cselect' type='checkbox' name='hidemyteams' $checkedhidemyteams /></td>
								</tr>";

	$thisContentObj->primaryContent .= "<tr><td style='text-align:center;' colspan='2'>
								<input class='button' type='submit' value=\"$LANG[SAVE_SETTINGS]\" /></td></tr>
								</table></form>";

	$shardContentArray[] = $thisContentObj;
}
break;
	
case "submit_settings":
if($CURRENTUSER != "anonymous" && $CURRENTUSER != "" && $CURRENTSTATUS != "banned" && is_numeric($_POST['posts_per_page'])) {

	$sound_q = mf_query("select sound_alert, flood from users where ID = '$CURRENTUSERID' limit 1");
	$sound_q = mysql_fetch_assoc($sound_q );

	$lang = make_var_safe($_POST['lang']);

	$usergraft = "";

	$usergraft = ", graft = \"".make_var_safe($_POST['graft'])."\"";
	$mail_alert=0;
	if (isset($_POST['mail_alert']))
		$mail_alert=1;

	$hidemyteams=0;
	if (isset($_POST['hidemyteams']))
		$hidemyteams=1;

	$hidemyself=0;
	if (isset($_POST['hidemyself']))
		$hidemyself=1;

	$fb_off=0;
	$fb_del = "";
	if (isset($_POST['fb_off'])) {
		$fb_off=1;
		$fb_del = ", facebookID = ''";
		setcookie("fbs_" . FACEBOOK_APP_ID, "", time()-2000, "/");
	}

	$pm_alert = NULL;
	if (isset($_POST['pm_alert']))
		$pm_alert=1;

	$sound_alert = 0;
	if (isset($_POST['sound_alert']))
		$sound_alert=1;
	if ($sound_q['sound_alert'] && !$sound_alert)
		setcookie("mf_speaker", "speakerOff", time()+864000000, "/");
	else if (!$sound_q['sound_alert'] && $sound_alert)
		setcookie("mf_speaker", "speakerOn", time()+864000000, "/");

	if (!isset($_POST['smilies']))
		setcookie("stillSmilies", "", -1);
	else
		setcookie("stillSmilies", "still", time()+86400000);

	$flood=0;
	if (isset($_POST['flood'])) {
		$flood=1;
		if (!$sound_q['flood'])
		setcookie("metaChannelFilter2", "", -1);
	}
	else if ($sound_q['flood'])
		setcookie("metaChannelFilter2", $siteSettings['flood_ID'], time()+86400);

	$intro_error = 0;
	$introducethread = make_var_safe($_POST['introducethread']);
	if (!$introducethread)
		$introducethread = "NULL";
	else {
		$intro_ID = mf_query("select introduce_ID from settings limit 1");
		$intro_ID = mysql_fetch_assoc($intro_ID);
		$intro_ID = $intro_ID['introduce_ID'];
		$introducethread = make_num_safe($introducethread);
		$verifythread = mf_query("select userID, pthread, category from forum_topics WHERE ID = \"$introducethread\" limit 1");
		$verifythread = mysql_fetch_assoc($verifythread);
		if ($verifythread['userID'] != $CURRENTUSERID) {
			$intro_error = 1;
			$introducethread = "NULL";
		}
		if ($verifythread['pthread'] == "1") {
			$intro_error = 2;
			$introducethread = "NULL";
		}
		if ($verifythread['category'] != $intro_ID) {
			$intro_error = 3;
			$introducethread = "NULL";
		}
	}
	$dtt = make_num_safe($_POST['dtt']);
	if ($dtt > 0) $dtt = 0;
	$dtp = make_num_safe($_POST['dtp']);
	if ($dtp > 0) $dtp = 0;

	$notify_lenght = "0";
	if (is_numeric($_POST['notify_lenght']))
		$notify_lenght = $_POST['notify_lenght'];

	$add_to_pt = "0";
	if (is_numeric($_POST['add_to_pt']))
		$add_to_pt = $_POST['add_to_pt'];

	mf_query("UPDATE users SET posts_per_page=$_POST[posts_per_page], mail_alert='$mail_alert', pm_alert='$pm_alert', sound_alert='$sound_alert', flood='$flood', hidemyself = '$hidemyself', hidemyteams = '$hidemyteams', introducethread=$introducethread, dtt='$dtt', dtp='$dtp', lang='$lang', notify_lenght = '$notify_lenght', facebook_disabled = '$fb_off', accept_pm_from = '$add_to_pt' $fb_del $usergraft WHERE ID=$CURRENTUSERID");


		if (!$intro_error)
			header("Location: ?shard=profile&action=g_success&filter=3");
		else
			header("Location: ?shard=profile&action=g_fail&error=$intro_error");
	}
break;
	
case "g_fail": {

	$intro_error = make_num_safe($_REQUEST['error']);
	$thisContentObj = New contentObj;
	$thisContentObj->primaryContent = $menuStr;
	$thisContentObj->primaryContent .= "<br/><div style='width:100%;text-align:center;'><h3>";
	if ($intro_error == 1)
		$thisContentObj->primaryContent .= $LANG['SETTINGS_FAIL1'];
	if ($intro_error == 2)
		$thisContentObj->primaryContent .= $LANG['SETTINGS_FAIL2'];
	if ($intro_error == 3)
		$thisContentObj->primaryContent .= $LANG['SETTINGS_FAIL3'];
	$thisContentObj->primaryContent .= "		</h3></div><br/>";
	$shardContentArray[] = $thisContentObj;
}
break;
	
    case "g_change_passwd":
	if($CURRENTUSER != "anonymous" and $CURRENTUSER != "") {
    	$userInfo = mf_query("select * from users where username=\"$CURRENTUSER\"");
    	$u = mysql_fetch_assoc($userInfo);
    
		//------------------------------------------------------------------------------
		// Create contentObj for this content object
		//------------------------------------------------------------------------------
		$thisContentObj = New contentObj;
		$thisContentObj->primaryContent = $menuStr;
		$thisContentObj->primaryContent .= $menuStr2;
		$thisContentObj->primaryContent .= "<form action='".make_link("profile","&amp;action=g_submit_passwd")."' method='post'>$LANG[NEWPASSINSTRUCTION].<br/><br/>
						<table cellpadding='2' align='center'>
						<tr>
						<td style='text-align:right;'>$LANG[OLD_PASSWD]</td>
						<td><input class='bselect' size='16' type='password' name='oldp' /></td>
						</tr><tr>
						<tr>
						<td style='text-align:right;'>$LANG[NEW_PASSWD]</td>
						<td><input class='bselect' size='16' type='password' name='new1' /></td>
						</tr><tr>
						<td style='text-align:right;'>$LANG[NEW_PASSWD2]</td>
						<td><input class='bselect' size='16' type='password' name='new2' /></td>
						</tr></table>
						<center><input class='button' type='submit' value=\"$LANG[SAVE_PASSWD]\" />
						</center></form>";
		$shardContentArray[] = $thisContentObj;
	}	
	break;
	
	case "g_submit_passwd":
	if($CURRENTUSER != "anonymous" && $CURRENTUSER != "") {
		$error = "";
		$oldp = "";
		if (array_key_exists("oldp" , $_POST ) == TRUE )
	        $oldp = make_var_safe(htmlspecialchars($_POST["oldp"]));
		$new1 = "";
		if (array_key_exists("new1" , $_POST ) == TRUE )
	        $new1 = make_var_safe(htmlspecialchars($_POST["new1"]));
	    $new2 = "";
	    if (array_key_exists("new2" , $_POST ) == TRUE )
	        $new2 = make_var_safe(htmlspecialchars($_POST["new2"]));
	        
		$oldpass = mf_query("SELECT password, crypt_method, salt FROM users where ID = '$CURRENTUSERID' limit 1");
		$oldpass = mysql_fetch_assoc($oldpass);
		if (!$oldpass['crypt_method'] && $oldpass['password'] != sha1($oldp)) {
			$error .= $LANG['OLD_PASS_NOT_MATCH']."<br/>";
		}
		else if ($oldpass['crypt_method'] == "blowfish") {
		    $stored_password = $oldpass['password'];
		    $stored_salt2 = $oldpass['salt'];
		    $verif_pass = crypt($oldp . $stored_salt2, $stored_password); //compare the crypt of input+stored_salt2 to the stored crypt password
		    if ($verif_pass != $stored_password) {
		        $error .= $LANG['OLD_PASS_NOT_MATCH']."<br/>";
		    }
		}
		if ($new1 != $new2) {
			$error .= $LANG['PASS_NOT_MATCH']."<br/>";
		}
		if ((strlen($new1) < 6) || (strlen($new1) > 16)) {
			$error .= $LANG['PASS_LENGH_INCORRECT']."<br/>";
		}
		
		if (!$error) {	        
	        if (!$siteSettings['crypt_method']) {
	        $new1 = SHA1($new1);
		        mf_query("UPDATE users set password='$new1' where ID='$CURRENTUSERID' LIMIT 1");
		    }
		    else {
		    	crypt_password($new1,$CURRENTUSERID);
				$result = mf_query("SELECT password FROM users WHERE ID='$CURRENTUSERID' LIMIT 1");
				$result = mysql_fetch_assoc($result);
				$new1 = $result['password'];

		    }
	        setcookie("b6".$siteSettings['db']."password", "$new1", time()+864000000, "/");
		    header("Location: ".make_link("profile","&action=g_success"));
		}
		else {
			$thisContentObj = New contentObj;
			$thisContentObj->primaryContent = $menuStr;
			$thisContentObj->primaryContent .= $menuStr2;
			$thisContentObj->primaryContent .= "<div style='padding:8px;'><div style='font-size:1.6em;color:red;margin-bottom:8px;'>$error</div>";
			$thisContentObj->primaryContent .= "<a href='".make_link("profile","&amp;action=g_change_passwd")."' class='button'>$LANG[BUTTON_BACK]</a></div>";
			$shardContentArray[] = $thisContentObj;
		}
	}
	break;
    


case "g_ep":
    if ($CURRENTUSER != "anonymous") {
		$thisContentObj = New contentObj;
		$thisContentObj->primaryContent = $menuStr;
		$thisContentObj->primaryContent .= $menuStr2;
		
		$thisContentObj->primaryContent .= userprofile($CURRENTUSERID,true);
		
		$shardContentArray[] = $thisContentObj;
		
    }
break;

case "acceptrules":
    if ($CURRENTUSER != "anonymous") {
    	$rules = $_POST['acceptrules'];
		if ($rules == "on")
			$rules = time();
		else
			$rules = "";

		mf_query("UPDATE users SET rulespictures = '$rules' WHERE ID = '$CURRENTUSERID' limit 1");
		if ($rules != "")
			header("Location: ".make_link("profile","&action=g_pictures&filter=6"));
		else
			header("Location: ".make_link("profile"));
	}
break;

case "g_pictures": {
    if ($siteSettings['rulespictures_thread'] && !$CURRENTUSERRULESPIC && $CURRENTUSER != "anonymous") {

		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->primaryContent = $menuStr;
		$thisContentObj->primaryContent .= "<div style='height:20px'></div>";

		$thisContentObj->primaryContent .= "<div style='padding:16px;'>";
		$thisContentObj->primaryContent .= rulespictures();
		$thisContentObj->primaryContent .= "<form name='rulesaccept' action='index.php?shard=profile&amp;action=acceptrules' method='post'>
				<div style='margin-top:12px;text-align:center;'>
					<div><input class='controls' type='checkbox' name='acceptrules' />$LANG[RULES_PICTURES_TEXT1]</div>
					<div><input name='rulessubmit' type='submit' value=\"$LANG[SUBMIT]\" class='button' /></div>
					<div>($LANG[RULES_PICTURES_TEXT2])</div>
				</div></form>";
		$thisContentObj->primaryContent .= "</div>";
		$shardContentArray[] = $thisContentObj;
	}
	else if ($CURRENTUSER != "anonymous") {
		$imagelist = "";
		$query_image = mf_query("SELECT * FROM pictures WHERE userID = '$CURRENTUSERID' ORDER by date_added DESC LIMIT 6");
		while ($images = mysql_fetch_assoc($query_image)) {
			$imagelist .= "<img src='$images[name_thumb]' alt='$images[name]' title=\"$images[description]\" onclick=\"view_picture('$images[name]','$images[description]','$images[width]','$images[height]','$images[albumID]','$images[date_added]'); return false;\" style='margin-right:4px;'/>";
		}
		$albumlist_details = "";
		$albumlist = "<option value=''></option>";
		$query_albums = mf_query("SELECT ID, name, description, coverID FROM albums WHERE userID = '$CURRENTUSERID' ORDER by date DESC");
		while ($album = mysql_fetch_assoc($query_albums)) {
			$albumlist .= "<option value='$album[ID]'>$album[name]</option>";
			$cover = mf_query("SELECT description, name_thumb FROM pictures WHERE ID = '$album[coverID]'  and userID='$CURRENTUSERID' LIMIT 1");
			$cover = mysql_fetch_assoc($cover);
			$albumlist_details .= "<div style='display:inline-block;width:300px;border:2px solid silver;text-align:center;padding:4px;margin:8px;'>
							<a href='".make_link("profile","&amp;action=g_modifyalbum&amp;albumID=$album[ID]&amp;filter=6")."'>";
			if ($cover['name_thumb'])
				$albumlist_details .= "<img src='$cover[name_thumb]' alt='' title=\"$cover[description]\" />";
			$albumlist_details .= "<div style='font-size:2em;'>$album[name]</div>
									<div style='font-size:1.2em;'>$album[description]</div>
							</a></div>";
		}
		$co = New contentObj;
		$co->contentType = "generic";
		$co->primaryContent = $menuStr;
		
		$co->primaryContent .= "<div style='height:20px'></div>";
		$co->primaryContent .= "<div style='font-weight:bold;padding-bottom:6px;'>$LANG[PICTURES_ALBUM_LAST_PICTURES]</div>";
		$co->primaryContent .= $imagelist;
		$co->primaryContent .= "<div class='subMenuParam'></div>";
		$co->primaryContent .= "
			<form name='upload_picture' method='post' action='".make_link("profile","&amp;action=g_addpicture")."' enctype='multipart/form-data'>
				<div style='width:740px;margin-left:100px;'>
					$LANG[PICTURES_UPLOAD] <input type='file' name='imagefile[]' multiple='true' class='bselect'/>
					$LANG[PICTURES_ALBUM_LINK] <select class='bselect' name='albumID'>$albumlist</select>
					<input type='submit' value=\"$LANG[ADD]\" class='button_mini' />
				</div>
			</form>";
		$co->primaryContent .= "<form name='add_album' method='post' action='".make_link("profile","&amp;action=g_addalbum")."' style='margin-top:8px;'>
								<div style='width:740px;text-align:center;'>$LANG[PICTURES_ALBUM_NEW] <input type='text' name='album_name' size='60' class='bselect' />
								<input type='submit' value=\"$LANG[PICTURES_ALBUM_NEW_BUTTON]\" class='button_mini' /></div>
								</form>";
		$co->primaryContent .= "<div class='subMenuParam'></div>";
		$co->primaryContent .= "<div style='text-align:center;font-size:2em;'>$LANG[PICTURES_ALBUM_LIST]</div>";
		$co->primaryContent .= "<div style='text-align:center;'>$albumlist_details</div>";
		$add_to_album_list = add_to_album_list();
		if ($add_to_album_list) {
			$co->primaryContent .= $add_to_album_list;
		}
		$co->primaryContent .= "<div style='margin-top:16px;'></div>";
		if ($siteSettings['rulespictures_thread']) {
			$co->primaryContent .= "<div class='subMenuParam'></div>";
			$co->primaryContent .= display_rulespicture();
		}

		$shardContentArray[] = $co;
    }
    }
break;

case "g_addpicture": {
	$uploadimg = false;
	$co = New contentObj;
	$co->contentType = "generic";
	$error = false;

	if (array_key_exists('imagefile', $_FILES)) {
		for($i=0;$i<sizeof($_FILES['imagefile']['name']);$i++) {
			// File type control
			if (($_FILES['imagefile']['type'][$i] == "image/jpeg" || $_FILES['imagefile']['type'][$i] == "image/pjpeg" || $_FILES['imagefile']['type'][$i] == "image/png" || $_FILES['imagefile']['type'][$i] == "image/gif") && in_array(strtolower(substr(strrchr($_FILES['imagefile']['name'][$i], '.'),1)), array('gif', 'jpg', 'jpeg', 'png'))) {
				if (filesize($_FILES['imagefile']['tmp_name'][$i]) > ($siteSettings['picture_maxfilesize'] * 1024)) {
					$error = true;
					$co->primaryContent .="<h2>".$_FILES['imagefile']['name'][$i]." $LANG[FILE_TOO_LARGE] (" . filesize($_FILES['imagefile']['tmp_name'][$i]) . " bytes)</h2><br/><br/>";
				}
				else {
					$file_ext_array = explode("." , $_FILES['imagefile']['name'][$i]);
					$file_ext = $file_ext_array[ sizeof($file_ext_array) - 1 ];					
					$name_picture = SHA1(time().$_FILES['imagefile']['name'][$i]);
	
					if (!move_uploaded_file( $_FILES['imagefile']['tmp_name'][$i] , "pictures/$name_picture.$file_ext" )) {
						$error = true;
						$co->primaryContent .= "<b>$LANG[PICTURES_UPLOAD_FAILED]</b><br/><br/>";
					}
					else {
						// Création de la miniature
						$img = $name_picture.".".$file_ext;
						$dest = $img;
						$pathToThumbs = "pictures/thumbs/$dest";
						if (strtolower($file_ext) == "png")
							$imgt = imagecreatefrompng( "pictures/$dest" );
						else if (strtolower($file_ext) == "gif")
							$imgt = imagecreatefromgif( "pictures/$dest" );
						else	
							$imgt = imagecreatefromjpeg( "pictures/$dest" );
						$width = imagesx( $imgt );
						$height = imagesy( $imgt );
						if ($height >= $width) {
							$new_width = "90";
							$new_height = floor( $height * ( "90" / $width ) );
						}
						else {
							$new_height = "90";
							$new_width = floor( $width * ( "90" / $height ) );
						}
						$tmp_img = imagecreatetruecolor( $new_width, $new_height );

						imagecopyresized( $tmp_img, $imgt, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
						imagejpeg( $tmp_img, $pathToThumbs );
						// Add to DB
						$date_added = time();
						$albumID = "";
						if (isset($_POST['albumID']))
							$albumID = make_num_safe($_POST['albumID']);
					else if (isset($_REQUEST['albumID']))
						$albumID = make_num_safe($_REQUEST['albumID']);
						mf_query("INSERT INTO pictures (name, name_thumb, width, height, userID, date_added,albumID) VALUE ('pictures/$name_picture.$file_ext', 'pictures/thumbs/$name_picture.$file_ext', '$width', '$height', '$CURRENTUSERID', '$date_added', '$albumID')");
						$uploadimg = true;
						$query_image = mf_query("SELECT ID FROM pictures WHERE name = 'pictures/$name_picture.$file_ext' limit 1");
						$image = mysql_fetch_assoc($query_image);
					}
				}
			}
			else {
				$error = true;
				$co->primaryContent .= "<h2>$LANG[INCORRECT_FILE_TYPE]: " . $_FILES['imagefile']['name'][$i] . ".".$_FILES['imagefile']['type'][$i] . "</h2><br/><br/>";
			}
		}
		if (!$error && $albumID)
			header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$albumID&filter=6"));
		else if (!$error)
			header("Location: ".make_link("profile","&action=g_pictures&filter=6"));
		
	}
	$co->primaryContent .= "<a href='".make_link("profile","&amp;action=g_pictures&amp;filter=6")."' class='button'>$LANG[BUTTON_BACK]</a>";
	$shardContentArray[] = $co;
}
break;

// Modification d'une image
case "g_modifypicture":
if ($CURRENTUSER != "anonymous") {
	$pictureID = make_num_safe($_REQUEST['pictureID']);
	$albumID = "";
	if (isset($_REQUEST['albumID']))
		$albumID = make_num_safe($_REQUEST['albumID']);

	$query_image = mf_query("SELECT * FROM pictures WHERE ID = '$pictureID' and userID='$CURRENTUSERID' limit 1");
	$image = mysql_fetch_assoc($query_image);

	$co = New contentObj;
	$co->contentType = "generic";
	$co->primaryContent = $menuStr;

	$co->primaryContent .= "<div id='modifypicture' style='width:740px;text-align:center;'>";
	$co->primaryContent .= "<img src='$image[name_thumb]' alt='' onclick=\"view_picture('$image[name]','$image[description]','$image[width]','$image[height]','$image[albumID]','$image[date_added]'); return false;\"/>";
	$co->primaryContent .= "<div style='height:8px'></div>";
	$co->primaryContent .= "<form name='edit_picture' action='index.php?shard=profile&amp;action=submitEditpicture&amp;editID=$pictureID&amp;albumID=$albumID' method='post'>";
	$co->primaryContent .= "<div style='margin-bottom:4px;'>$LANG[PICTURE_DESCRIPTION] <input type='text' value=\"$image[description]\" size='80' name='description' class='bselect' /></div>";

	$co->primaryContent .= "<div><input type='submit' value=\"$LANG[SUBMIT_EDIT]\" class='button' />&nbsp;";
	$co->primaryContent .= "<input type='button' value=\"$LANG[BUTTON_BACK]\" onclick=\"location.href='".make_link("profile")."&action=";
	if ($albumID)
		$co->primaryContent .= "g_modifyalbum&albumID=$albumID";
	else
		$co->primaryContent .= "g_pictures";
	$co->primaryContent .= "&filter=6'\" class='button' />";
	$co->primaryContent .= "</div></form></div>";

	// Supprimer l'image-
	if ($CURRENTUSER != "anonymous") {
		$co->primaryContent .= "<div class='subMenuParam'></div>";
		$co->primaryContent .= "<center><h2>$LANG[PICTURE_DELETE]</h2></center>";
		$co->primaryContent .= "<div style='height:20px'></div>";
		$co->primaryContent .= "<center><form name='deletepicture' action=\"index.php?shard=profile&amp;action=deletepicture&amp;ID=$pictureID&amp;albumID=$albumID\" method='post'>
			<table>
			<tr><td style='text-align:right;'>
				$LANG[PICTURE_DELETE_LINE]</td>
			<td><input type='checkbox' name='delete_conf' class='bselect' /></td>
			<td>&nbsp;<input type='submit' class='button' value=\"$LANG[PICTURE_DELETE_CONFIRM]\" />
			</td></tr></table>
			</form></center>";
	}
	if ($siteSettings['rulespictures_thread']) {
		$co->primaryContent .= "<div class='subMenuParam'></div>";
		$co->primaryContent .= display_rulespicture();
	}

	$shardContentArray[] = $co;
}
break;

// Enregistrement de la modification d'une image
case "submitEditpicture":
if ($CURRENTUSER != "anonymous") {
	$pictureID = make_num_safe( $_REQUEST['editID']);
	$description = make_var_safe( $_REQUEST['description']);
	if ($_REQUEST['albumID'])
		$albumID = make_num_safe($_REQUEST['albumID']);

	//	$picture_date = convert_input_date(make_var_safe( $_REQUEST['date1']));

	if( is_numeric($_REQUEST['editID']))
		mf_query("update pictures set description=\"".$description."\" where ID='$pictureID' and userID='$CURRENTUSERID' limit 1");

	if ($albumID)
		header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$albumID&filter=6"));
	else
		header("Location: ".make_link("profile","&action=g_pictures&filter=6"));
}
break;

// Suppression d'une image
case "deletepicture":
if ($CURRENTUSER != "anonymous") {
	if( is_numeric( $_REQUEST['ID'])) {
		if ($_REQUEST['albumID'])
			$albumID = make_num_safe($_REQUEST['albumID']);

		
		if (isset($_POST['delete_conf'])) {
			$query_image = mf_query("SELECT * FROM pictures WHERE ID = '$_REQUEST[ID]' and userID='$CURRENTUSERID' limit 1");
			$image = mysql_fetch_assoc($query_image);
			unlink($image['name']);
			unlink($image['name_thumb']);

			mf_query("delete from pictures where ID='$_REQUEST[ID]' and userID='$CURRENTUSERID' limit 1");
			mf_query("update albums set coverID = '' where coverID = '$_REQUEST[ID]' and userID='$CURRENTUSERID'");

			if ($albumID)
				header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$albumID&filter=6"));
			else
				header("Location: ".make_link("profile","&action=g_pictures&filter=6"));
		}
		else
			header("Location: ".make_link("profile","&action=g_modifypicture&pictureID=$_REQUEST[ID]&albumID=$albumID&filter=6"));
	}
}
break;

case "g_addalbum":
if ($CURRENTUSER != "anonymous") {	
	$co = New contentObj;
	$co->contentType = "generic";
	
	if ($_POST['album_name']) {
		$album_name = make_var_safe($_POST['album_name']);
		$date_added = time();
		mf_query("INSERT INTO albums (name, userID, date) VALUE (\"$album_name\", '$CURRENTUSERID', '$date_added')");
		$query_album = mf_query("SELECT ID FROM albums WHERE date = '$date_added' and userID = '$CURRENTUSERID' limit 1");
		$album = mysql_fetch_assoc($query_album);
		header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$album[ID]&filter=6"));
	}
	
	$co->primaryContent .= "<a href='".make_link("profile","&amp;action=g_pictures&amp;filter=6")."' class='button'>$LANG[BUTTON_BACK]</a>";
	$shardContentArray[] = $co;
}
break;

// Album modification
case "g_modifyalbum":
if ((!$siteSettings['rulespictures_thread'] || $CURRENTUSERRULESPIC) && $CURRENTUSER != "anonymous") {
	$albumID = make_num_safe($_REQUEST['albumID']);

	$query_album = mf_query("SELECT * FROM albums WHERE ID = '$albumID' and userID = '$CURRENTUSERID' limit 1");
	$album = mysql_fetch_assoc($query_album);
	
	if (!$album['ID'])
		exit($LANG['REFUSED']);

	if ($album['coverID']) {
		$cover = mf_query("SELECT name_thumb FROM pictures WHERE ID = '$album[coverID]' limit 1");
		$cover = mysql_fetch_assoc($cover);
	}

	$imagelist = "<form name='remove_of_album' action='index.php?shard=profile&amp;action=submitRemoveofalbum&amp;albumID=$albumID' method='POST'>
				<div style='max-height:400px;overflow:auto;border:2px solid silver;padding:2px;'>";
	$i = 1;
	$query_image = mf_query("SELECT * FROM pictures WHERE albumID = '$album[ID]' ORDER by date_added DESC");
	while ($images = mysql_fetch_assoc($query_image)) {
		$defaultimage = "";
		if ($images['ID'] == $album['coverID'])
			$defaultimage = "style='border: 2px solid red; padding: 2px;'";

		$desc = $images['description'];
		$width = $images['width'];

		$imagelist .= "
			<div style='display:inline-block;border:1px solid silver;padding:1px;'>
				<div style='display:table;'>
					<div class='row'>
						<div class='cell' style='vertical-align:top;margin-left:6px;margin-right:-2px;'>
							<div>
								<input type='checkbox' class='bselect' name='check_$i' />
								<input type='hidden' name='$i' value='$images[ID]' />
							</div>
							<div>
								<a href='".make_link("profile","&amp;action=g_modifypicture&amp;albumID=$album[ID]&amp;pictureID=$images[ID]&amp;filter=6")."'>
									<img src='engine/grafts/" . $siteSettings['graft'] . "/images/edit2.gif' alt='' title=\"$LANG[PICTURES_EDIT]\" style='margin-top:4px;' />
								</a>
							</div>
							<div>";
		if ($defaultimage)
			$imagelist .= "<a href='index.php?shard=profile&amp;action=notcoverpicture&amp;pictureID=$images[ID]&amp;albumID=$albumID'>
					<img src='engine/grafts/" . $siteSettings['graft'] . "/images/subscribed.png' alt='C' title=\"$LANG[PICTURES_DO_NOT_MAKE_COVER]\" style='margin-top:4px;' />";
		else
			$imagelist .= "<a href='index.php?shard=profile&amp;action=coverpicture&amp;pictureID=$images[ID]&amp;albumID=$albumID'>
					<img src='engine/grafts/" . $siteSettings['graft'] . "/images/unsubscribed.png' alt='C' title=\"$LANG[PICTURES_MAKE_COVER]\" style='margin-top:4px;' />";
		$imagelist .= "</a></div>
						</div>
						<div class='cell'>
							<img src='$images[name_thumb]' alt='' title=\"$images[description]\" $defaultimage onclick=\"view_picture('$images[name]','$desc','$width','$images[height]','$album[ID]','$images[date_added]'); return false;\" style='vertical-align:top;margin-bottom:4px;' />
						</div>
					</div>
				</div>
			</div>";
		$i ++;
	}
	$imagelist .= "</div>
				<div style='margin-top:8px;'><input type='submit' class='button' value=\"$LANG[PICTURES_REMOVE_SELECTION_ALBUM]\" /></div>
				</form>";
	if ($i == 1)
		$imagelist = "";

	$threadlist = "";
	$query_threads = mf_query("SELECT albums_topics.threadID, forum_topics.title FROM albums_topics
							JOIN forum_topics ON albums_topics.threadID = forum_topics.ID 
							WHERE albums_topics.albumID = '$albumID' ORDER by albums_topics.threadID DESC");
	while ($threads = mysql_fetch_assoc($query_threads)) {
		$threadlist .= "<div style='display:inline-block;margin-right:12px;vertical-align:top;border:1px solid silver;padding:1px;width:200px;'>
					<a href='index.php?shard=profile&amp;action=removethread&amp;albumID=$albumID&amp;ID=$threads[threadID]'>
					<img src='engine/grafts/" . $siteSettings['graft'] . "/images/b_drop_mini.png' border='0' align='top' title='$LANG[DELETE]' alt='DEL' style='vertical-align:top;float:right;'/></a>
					<a href='".make_link("forum","&amp;action=g_reply&amp;ID=$threads[threadID]&amp;page=1","#thread/$threads[threadID]/1")."'>$threads[title]</a>
					</div>";
	}
	$userlist = "";
	$query_users = mf_query("SELECT albums_users.userID, users.username, users.avatar FROM albums_users
							JOIN users ON albums_users.userID = users.ID 
							WHERE albums_users.albumID = '$albumID' ORDER by users.username ASC");
	while ($users = mysql_fetch_assoc($query_users)) {
		if (!$users['avatar'])
			$users['avatar'] = "engine/grafts/" . $siteSettings['graft'] . "/images/noavatar.png";
		$userlist .= "<div style='display:inline-block;margin-right:12px;vertical-align:top;border:1px solid silver;padding:1px;width:150px;'>
					<img src='$users[avatar]' alt='' style='max-width:32px;' />
					<a href='".make_link("forum","&amp;action=g_ep&amp;ID=$users[userID]")."' style='vertical-align:top;'>$users[username]</a>
					<a href='index.php?shard=profile&amp;action=removeuser&amp;albumID=$albumID&amp;userID=$users[userID]'>
					<img src='engine/grafts/" . $siteSettings['graft'] . "/images/b_drop_mini.png' border='0' align='top' title='$LANG[DELETE]' alt='DEL' style='vertical-align:top;float:right;'/></a>
					</div>";
	}
	$co = New contentObj;
	$co->contentType = "generic";
	$co->primaryContent = $menuStr;
	$co->primaryContent .= "";
	
	$co->primaryContent .= "<div id='modifyalbum' style='width:740px;text-align:center;'>";
	// album title
	$co->primaryContent .= "<div style='text-align:center;font-size:2.2em;'>$LANG[PICTURES_ALBUM] \"$album[name]\"</div>";
	// albums tabs
	$albumpicturestab =  "Sel";
	$albumaddtab =  "unSel";
	$albumsettingstab =  "unSel";
	$albumpicturesdiv =  "block";
	$albumadddiv =  "none";
	$albumsettingsdiv =  "none";
	if (isset($_REQUEST['settings'])) {
		$albumpicturestab =  "unSel";
		$albumsettingstab =  "Sel";
		$albumpicturesdiv =  "none";
		$albumsettingsdiv =  "block";
	}
	else if (isset($_REQUEST['add'])) {
		$albumpicturestab =  "unSel";
		$albumaddtab =  "Sel";
		$albumpicturesdiv =  "none";
		$albumadddiv =  "block";
	}
	$co->primaryContent .= "<div class='section_$albumpicturestab' id='albumpicturestab' onclick=\"menu_sections('albumpictures','albumadd','albumsettings');\">$LANG[PICTURES_ALBUM_PICTURES]</div>";
	$co->primaryContent .= "<div class='section_$albumaddtab' id='albumaddtab' onclick=\"menu_sections('albumadd','albumpictures','albumsettings');\">$LANG[PICTURES_ALBUM_ADD_PICTURES]</div>";
	$co->primaryContent .= "<div class='section_$albumsettingstab' id='albumsettingstab' onclick=\"menu_sections('albumsettings','albumpictures','albumadd');\">$LANG[PICTURES_ALBUM_SETTINGS]</div>";
	$co->primaryContent .= "<div class='subMenuParam'></div>";
	// Album pictures
	$co->primaryContent .= "<div id='albumpictures' style='display:$albumpicturesdiv;'>";
	
	$co->primaryContent .= "<div style='height:8px'></div>";
	$co->primaryContent .= $imagelist."</div></div>";
	$co->primaryContent .= "<div style='height:8px'></div>";

	$co->primaryContent .= "<div id='albumadd' style='display:$albumadddiv;'>";
	$co->primaryContent .= "<form name='upload_picture' method='post' action='index.php?shard=profile&amp;action=g_addpicture&albumID=$albumID' enctype='multipart/form-data'>
					<div style='margin-top:4px;'>$LANG[PICTURES_UPLOAD] <input type='file' name='imagefile[]' multiple='true' class='bselect' size='50'/> &nbsp;
					<input type='submit' value=\"$LANG[ADD]\" class='button_mini' />
					</div>
					</form>";
	$co->primaryContent .= add_to_album_list($albumID);
	$co->primaryContent .= "</div></div>";

	// Autorisations
	$public_checked = "";
	if ($album['public'])
		$public_checked = "checked='checked'";
	$profile_checked = "";
	if ($album['profile'])
		$profile_checked = "checked='checked'";
	$co->primaryContent .= "<div id='albumsettings' style='display:$albumsettingsdiv;text-align:center;'>";
	$co->primaryContent .= "<form name='edit_picture' action='index.php?shard=profile&amp;action=submitEditalbum&amp;editID=$albumID' method='post'>
								<div style='margin-bottom:4px;'>
									$LANG[PICTURES_ALBUM_SETTINGS_DESC] <input type='text' value=\"$album[description]\" size='80' name='description' class='bselect' />
									<input type='submit' value=\"$LANG[SUBMIT_EDIT]\" class='button_mini' />
								</div>
							</form>";
	$co->primaryContent .= "<div style='text-align:center;font-size:2em;'>$LANG[PICTURES_ALBUM_AUTHORIZATIONS]</div>";
	$co->primaryContent .= "<div style='border:1px solid silver;padding:4px;margin-bottom:4px;'>";
	$co->primaryContent .= "<form name='public_album_form' action='index.php?shard=profile&amp;action=submitpublicalbum&amp;albumID=$albumID' method='post'>";
	$co->primaryContent .= "<div style='text-align:center;margin-top:4px;'>";
	$co->primaryContent .= "<span class='bold' style='font-size:1.2em;margin-right:16px;'>$LANG[PICTURES_ALBUM_PRIVACY]</span>";
	$co->primaryContent .= "<input type='checkbox' name='public_album' $public_checked class='bselect checkbox' /> $LANG[PICTURES_ALBUM_PUBLIC] &nbsp; ";
	if ($album['public'])
		$co->primaryContent .= "<input type='checkbox' name='profile_album' $profile_checked class='bselect checkbox' /> $LANG[PICTURES_ALBUM_PROFILE] &nbsp; ";
	$co->primaryContent .= " <input type='submit' value=\"$LANG[SUBMIT_EDIT]\" class='button_mini' />";
	$co->primaryContent .= "</div></form></div>";

	// Threads
	$co->primaryContent .= "<div style='border:1px solid silver;padding:4px;margin-bottom:4px;text-align:center;'>";
	if ($threadlist)
		$co->primaryContent .= "<div style='font-size:1em;margin-bottom:4px;text-decoration: underline;'>$LANG[PICTURES_ALBUM_VISIBLE_THREADS]</div>
						<div style='padding-left:16px;font-weight:bold;font-size:1.2em;'>$threadlist</div>";
	if ($threadlist || $album['public'])
		$co->primaryContent .= "<div style='text-align:center;margin-bottom:8px;margin-top:8px;'>$LANG[PICTURES_ALBUM_CODE_TO_SHARE] 
						<input type='text' readonly='readonly' size='15' class='bselect' value=\"&#91;album&#93;$albumID&#91;/album&#93;\" style='font-size:16px;'/></div>";
	$co->primaryContent .= "<form name='add_thread' action='index.php?shard=profile&amp;action=submitaddthreadalbum&amp;albumID=$albumID' method='post'>";
	$co->primaryContent .= "<div style='text-align:center;margin-top:4px;'>
							$LANG[PICTURES_ALBUM_SHARE_THREAD] <input type='text' value='' size='6' name='threadID' class='bselect' />";
	$co->primaryContent .= " <input type='submit' value=\"$LANG[ADD]\" class='button_mini' />";
	$co->primaryContent .= "</div></form></div>";

	// Users
	$co->primaryContent .= "<div style='border:1px solid silver;padding:4px;'>";
	if ($userlist)
		$co->primaryContent .= "<div style='font-size:1em;margin-bottom:4px;text-decoration: underline;'>$LANG[PICTURES_ALBUM_SHARED_USERS]</div>
						<div style='padding-left:16px;font-weight:bold;font-size:1.2em;'>$userlist</div>";
	$co->primaryContent .= "<form name='add_user' action='index.php?shard=profile&amp;action=submitadduseralbum&amp;albumID=$albumID' method='post'>";
	$co->primaryContent .= "<div style='text-align:center;margin-top:4px;'>
							$LANG[PICTURES_ALBUM_SHARE_ADD_USER] <input type='text' value='' size='12' name='username' class='bselect' />";
	$co->primaryContent .= " <input type='submit' value=\"$LANG[ADD]\" class='button_mini' />";
	$co->primaryContent .= "</div></form></div>";

	// Supprimer l'album
	if ($CURRENTUSER != "anonymous") {
		$co->primaryContent .= "<div class='subMenuParam'></div>";
		$co->primaryContent .= "<div style='text-align:center;font-size:2em;'>$LANG[PICTURES_ALBUM_DELETE]</div>";
		$co->primaryContent .= "<div style='height:12px'></div>";
		$co->primaryContent .= "<div style='text-align:center;'>
				<form name='deletealbum' action=\"index.php?shard=profile&amp;action=deletealbum&amp;ID=$albumID\" method='post'>
				$LANG[PICTURES_ALBUM_DELETE_LINE] <input type='checkbox' name='delete_conf' class='bselect' />
				&nbsp;<input type='submit' class='button' value=\"$LANG[PICTURES_ALBUM_DELETE_CONFIRM]\" />
				<div style='margin-top:4px;'>$LANG[PICTURES_ALBUM_DELETE_PICTURES] <input type='checkbox' name='delete_all_conf' class='bselect' /></div>
				</form></div>";
	}
	
	$co->primaryContent .= "</div>";
	
	if ($siteSettings['rulespictures_thread']) {
		$co->primaryContent .= "<div class='subMenuParam'></div>";
		$co->primaryContent .= display_rulespicture();
	}

	$shardContentArray[] = $co;
}
break;

// Public Album
case "submitpublicalbum":
if ($CURRENTUSER != "anonymous") {
	$albumID = make_num_safe( $_REQUEST['albumID']);
	$profile = "";
	if (isset($_POST['public_album'])) {
		$public = "1";
		if (isset($_POST['profile_album']))
			$profile = "1";
	}
	else {
		$public = "";
		$profile = "";
	}
	
	if( is_numeric($_REQUEST['albumID'])) {
		mf_query("update albums set public='$public', profile = '$profile' where ID='$albumID' and userID='$CURRENTUSERID' limit 1");
		header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$albumID&filter=6&settings=1"));
	}

}
break;

// Enregistrement de la modification d'un album
case "submitEditalbum":
if ($CURRENTUSER != "anonymous") {
	$albumID = make_num_safe( $_REQUEST['editID']);
	$description = make_var_safe( $_REQUEST['description']);

	if( is_numeric($_REQUEST['editID']))
		mf_query("update albums set description=\"".$description."\" where ID='$albumID' and userID='$CURRENTUSERID'limit 1");

	header("Location: ".make_link("profile","&action=g_pictures&filter=6"));
}
break;

// Suppression d'une image
case "deletealbum":
if ($CURRENTUSER != "anonymous") {
	if( is_numeric( $_REQUEST['ID'])) {
		if (isset($_POST['delete_conf'])) {
			if (isset($_POST['delete_all_conf'])) {
				$query_image = mf_query("SELECT name, name_thumb FROM pictures WHERE albumID = '$_REQUEST[ID]' and userID='$CURRENTUSERID'");
				while ($image = mysql_fetch_assoc($query_image)) {
					@unlink($image['name']);
					@unlink($image['name_thumb']);
				}
				mf_query("delete from pictures where albumID='$_REQUEST[ID]' and userID='$CURRENTUSERID'");
			}
			else
				mf_query("update pictures set albumID='' where albumID='$_REQUEST[ID]' and userID='$CURRENTUSERID'");

			mf_query("delete from albums_topics where albumID='$_REQUEST[ID]'");
			mf_query("delete from albums where ID='$_REQUEST[ID]' and userID='$CURRENTUSERID' limit 1");

			header("Location: ".make_link("profile","&action=g_pictures&filter=6"));
		}
		else
			header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$_REQUEST[ID]&filter=6"));
	}
}
break;

case "submitAddtoalbum":
if ($CURRENTUSER != "anonymous") {
	$i = 1;
	$albumID = make_num_safe($_POST['albumID']);
	while (isset($_POST[$i])) {
		if (isset($_POST['check_'.$i])) {
			if (isset($_POST['deletesel'])) {
				@unlink(make_var_safe($_POST['name']));
				@unlink(make_var_safe($_POST['name_thumb']));
				mf_query("delete from pictures where ID='".make_num_safe($_POST[$i])."' and userID='$CURRENTUSERID'");
			}
			else
				mf_query("update pictures set albumID = '$albumID' where ID = '".make_num_safe($_POST[$i])."' and userID = '$CURRENTUSERID' limit 1");
		}
		$i ++;
	}
	if (!isset($_POST['deletesel']))
		mf_query("update albums set lastupdate = '".time()."' where ID = '$albumID' and userID = '$CURRENTUSERID' limit 1");

	if ($_POST['notalbum'])
		header("Location: ".make_link("profile","&action=g_pictures&filter=6"));
	else
		header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$albumID&filter=6"));
}
break;

case "submitRemoveofalbum":
if ($CURRENTUSER != "anonymous") {
	$i = 1;
	$albumID = make_num_safe($_REQUEST['albumID']);
	while (isset($_POST[$i])) {
		if (isset($_POST['check_'.$i]))
			mf_query("update pictures set albumID = '' where ID = '".make_num_safe($_POST[$i])."' and userID = '$CURRENTUSERID' limit 1");
		$i ++;
	}
	mf_query("update albums set lastupdate = '".time()."' where ID = '$albumID' and userID = '$CURRENTUSERID' limit 1");

	header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$albumID&filter=6"));
}
break;

case "submitaddthreadalbum":
	if (is_numeric($_POST['threadID'])) {
		$threadID = $_POST['threadID'];
		$albumID = make_num_safe($_REQUEST['albumID']);

		$query_album = mf_query("SELECT threadID FROM fhits WHERE threadID = '$threadID' and userID = '$CURRENTUSERID' limit 1");
		$album = mysql_fetch_assoc($query_album);
		if (!$album['threadID'])
			exit($LANG['REFUSED']);

		$query_album = mf_query("SELECT * FROM albums WHERE ID = '$albumID' and userID = '$CURRENTUSERID' limit 1");
		$album = mysql_fetch_assoc($query_album);
		if (!$album['ID'])
			exit($LANG['REFUSED']);

		mf_query("INSERT IGNORE INTO albums_topics (albumID, threadID) VALUE ('$albumID', '$threadID')");
		header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$albumID&filter=6"));
	}
break;

// Suppression d'un sujet dans un album
case "removethread":
if ($CURRENTUSER != "anonymous") {
	if( is_numeric( $_REQUEST['ID']) && is_numeric( $_REQUEST['albumID']))
	{
		$query_album = mf_query("SELECT ID FROM albums WHERE ID = '$_REQUEST[albumID]' and userID = '$CURRENTUSERID' limit 1");
		$album = mysql_fetch_assoc($query_album);
	
		if (!$album['ID'])
			exit($LANG['REFUSED']);

		mf_query("delete from albums_topics where albumID='$_REQUEST[albumID]' and threadID ='$_REQUEST[ID]' limit 1");

		header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$_REQUEST[albumID]&filter=6"));
	}
}
break;

case "submitadduseralbum":
	if ($_POST['username']) {
		$username = make_var_safe($_POST['username']);
		$albumID = make_num_safe($_REQUEST['albumID']);

		$query_user = mf_query("SELECT ID FROM users WHERE username = \"$username\" limit 1");
		$user = mysql_fetch_assoc($query_user);
		if (!$user['ID']) {
			header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$albumID&filter=6"));
			exit($LANG['REFUSED']);
		}

		$query_album = mf_query("SELECT ID FROM albums WHERE ID = '$albumID' and userID = '$CURRENTUSERID' limit 1");
		$album = mysql_fetch_assoc($query_album);
		if (!$album['ID'])
			exit($LANG['REFUSED']);

		mf_query("INSERT IGNORE INTO albums_users (albumID, userID, owner_userID) VALUE ('$albumID', '$user[ID]', '$CURRENTUSERID')");
		header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$albumID&filter=6"));
	}
break;

// Suppression d'un sujet dans un album
case "removeuser":
if ($CURRENTUSER != "anonymous") {
	if( is_numeric( $_REQUEST['albumID']) && is_numeric( $_REQUEST['userID'])) {
		$query_album = mf_query("SELECT ID FROM albums WHERE ID = '$_REQUEST[albumID]' and userID = '$CURRENTUSERID' limit 1");
		$album = mysql_fetch_assoc($query_album);

		if (!$album['ID'])
			exit($LANG['REFUSED']);

		mf_query("delete from albums_users where albumID='$_REQUEST[albumID]' and userID ='$_REQUEST[userID]' limit 1");

		header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$_REQUEST[albumID]&filter=6"));
	}
}
break;

case "coverpicture":
if ($CURRENTUSER != "anonymous") {
	if( is_numeric( $_REQUEST['pictureID']) && is_numeric( $_REQUEST['albumID'])) {
		mf_query("update albums set coverID = '$_REQUEST[pictureID]' WHERE ID = '$_REQUEST[albumID]' and userID = '$CURRENTUSERID' limit 1");

		header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$_REQUEST[albumID]&filter=6"));
	}
}
break;

case "notcoverpicture":
if ($CURRENTUSER != "anonymous") {
	if( is_numeric( $_REQUEST['pictureID']) && is_numeric( $_REQUEST['albumID'])) {
		mf_query("update albums set coverID = '' WHERE ID = '$_REQUEST[albumID]' and userID = '$CURRENTUSERID' limit 1");

		header("Location: ".make_link("profile","&action=g_modifyalbum&albumID=$_REQUEST[albumID]&filter=6"));
	}
}
break;

// tags list
case "g_tags_list":
if ($CURRENTUSER != 'anonymous' && $CURRENTSTATUS != "banned") {
           	$thisContentObj = New contentObj;
           	$thisContentObj->contentType = "generic";
			$thisContentObj->primaryContent = $menuStr;
			$thisContentObj->primaryContent .= "<div style='font-size:2em;margin-bottom:12px;'>$LANG[MY_TAGS_TITLE]</div>";
			
			$order = "tag";
			$sens = "ASC";
			$link = make_link("profile","&amp;action=g_tags_list&amp;filter=7");
			$link_tag = "&amp;order=tag&amp;sens=ASC";
			$link_total = "&amp;order=total&amp;sens=ASC";
			$link_user = "&amp;order=user&amp;sens=ASC";
			$img_tag = " <img src='engine/grafts/$siteSettings[graft]/images/menudown.gif' alt'X' />";
			$img_total = "";
			if (isset($_REQUEST['order'])) {
				if ($_REQUEST['sens'] == "ASC") {
					$sens = "ASC";
					$sens_link = "DESC";
					$sens_img = "down";
				}
				else {
					$sens = "DESC";
					$sens_link = "ASC";
					$sens_img = "up";
				}
				if ($_REQUEST['order'] == "tag") {
					$order = "tag";
					$link_tag = "&amp;order=tag&amp;sens=$sens_link";
					$img_tag = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'X' />";
				}
				else if ($_REQUEST['order'] == "total") {
					$order = "total_use";
					$link_total = "&amp;order=total&amp;sens=$sens_link";
					$img_tag = "";
					$img_total = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'X' />";
				}
				else if ($_REQUEST['order'] == "user") {
					$order = "username";
					$link_user = "&amp;order=user&amp;sens=$sens_link";
					$img_tag = "";
					$img_user = " <img src='engine/grafts/$siteSettings[graft]/images/menu$sens_img.gif' alt'X' />";
				}
			}
			$backgrd = "";
			$tags_list = "<div class='row'>
							<div class='cell bold' style='padding-left:4px;padding-right:4px;min-width:150px;'><a href='$link$link_tag'>$LANG[TAG]$img_tag</a></div>
							<div class='cell bold' style='padding-left:4px;padding-right:4px;'><a href='$link$link_total'>$LANG[TAG_TOTAL_USE]$img_total</a></div>
							<div class='cell bold' style='padding-left:4px;padding-right:4px;'>$LANG[TAG_EDIT]</a></div>
							<div class='cell bold' style='padding-left:4px;padding-right:4px;'>$LANG[TAG_DEL]</a></div>
						</div>";
			$query = mf_query("SELECT * FROM tags WHERE userID = '$CURRENTUSERID' ORDER BY $order $sens");
			while ($row = mysql_fetch_assoc($query)) {
				$zerouse = "";
				if ($row['total_use'] == 0)
					$zerouse = "color:red;";
				if (!$backgrd)
					$backgrd = "background-color:#E7E7E7;";
				else
					$backgrd = "";
				$tags_list .= "<div class='row' style='$backgrd'>
								<div class='cell'>$row[tag]</div>
								<div class='cell right' style='padding-right:4px;$zerouse'>$row[total_use]</div>
								<div class='cell center'><a href='".make_link("profile","&amp;action=g_edit_tag&tagID=$row[ID]&amp;filter=7")."'><img src='engine/grafts/$siteSettings[graft]/images/edit2.gif' alt'E' /></a></div>
								<div class='cell center'><a href='".make_link("profile","&amp;action=g_delete_tag&tagID=$row[ID]&amp;filter=7")."'><img src='engine/grafts/$siteSettings[graft]/images/b_drop_mini.png' alt'X' /></a></div>
							</div>";
			}
			$thisContentObj->primaryContent .= "<div style='display:table;'>$tags_list</div>";
			
			$shardContentArray[] = $thisContentObj;

}
break;
		
case "g_edit_tag":
if ($CURRENTUSER != 'anonymous' && $CURRENTSTATUS != "banned") {

	$tagID = make_num_safe($_REQUEST['tagID']);

	$thisContentObj = New contentObj;
	$thisContentObj->contentType = "generic";
	$thisContentObj->primaryContent = $menuStr;
				
	$tagList = "";
	$query = mf_query("SELECT ID,tag FROM tags WHERE ID != '$tagID' ORDER BY tag");
	while ($row = mysql_fetch_assoc($query))
		$tagList .= "<option value=\"$row[tag]\">$row[tag]</option>";

	$query = mf_query("SELECT * FROM tags WHERE ID = '$tagID' AND userID = '$CURRENTUSERID' LIMIT 1");
	if ($tag_row = mysql_fetch_assoc($query)) {
	
		$thisContentObj->primaryContent .= "<div style='font-size:2em;margin-bottom:12px;'>$LANG[TAG_EDIT_TITLE] \"$tag_row[tag]\"</div>";
		$thisContentObj->primaryContent .= "<form name='rename_tag' action='index.php?shard=profile&amp;action=proc_edit_tag' method='post'>
										<input type='hidden' name='tagID' value='$tagID' />
										<input type='hidden' name='tagName' value=\"$tag_row[tag]\" />
										$LANG[TAG_NEW_NAME] <input type='text' name='new_tagName' value=\"$tag_row[tag]\" size='40' class='bselect' />
										<input type='submit' class='button_mini' value=\"$LANG[TAG_RENAME]\" />
										</form>";
		$thisContentObj->primaryContent .= "<form style='margin-top:8px;' name='move_tag' action='index.php?shard=profile&amp;action=proc_move_tag' method='post'>
										<input type='hidden' name='tagID' value='$tagID' />
										<input type='hidden' name='total_use' value='$tag_row[total_use]' />
										<input type='hidden' name='total_use_week' value='$tag_row[total_use_week]' />
										<input type='hidden' name='total_use_month' value='$tag_row[total_use_month]' />
										<input type='hidden' name='total_use_year' value='$tag_row[total_use_year]' />
										<input type='hidden' name='tagName' value=\"$tag_row[tag]\" />
										$LANG[TAG_MOVE] <select name='move_to_tag' class='bselect'>$tagList</select>
										<input type='submit' class='button_mini' value=\"$LANG[TAG_MOVE_BUTTON]\" />
										</form>";
		$thisContentObj->primaryContent .= "<div style='height:16px;'></div><a href='".make_link("profile","&amp;action=g_tags_list&filter=7")."' class='button'>$LANG[BUTTON_BACK]</a>";
		$shardContentArray[] = $thisContentObj;
	}
}
break;

case "g_tag_exist":
if ($CURRENTUSER != 'anonymous' && $CURRENTSTATUS != "banned") {

	$tagID = make_num_safe($_REQUEST['tagID']);
	$tagName = make_var_safe($_REQUEST['tagName']);
	$new_tagName = make_var_safe($_REQUEST['new_tagName']);

	$thisContentObj = New contentObj;
	$thisContentObj->contentType = "generic";
	
	$thisContentObj->title = "$LANG[TAG_ERROR]";
	$thisContentObj->primaryContent = "$LANG[TAG_RENAME_ERROR1] \"<span class='bold'>$tagName</span>\" $LANG[TAG_RENAME_ERROR2] \"<span class='bold'>$new_tagName</span>\"$LANG[TAG_RENAME_ERROR3] \"<span class='bold'>$new_tagName</span>\" $LANG[TAG_RENAME_ERROR4]";
	$thisContentObj->primaryContent .= "&nbsp; <a href='".make_link("profile","&amp;action=g_edit_tag&amp;tagID=$tagID&amp;filter=7")."' class='button'>$LANG[BUTTON_BACK]</a>";

	$shardContentArray[] = $thisContentObj;
}
break;

case "proc_edit_tag":
if ($CURRENTUSER != 'anonymous' && $CURRENTSTATUS != "banned") {
			$tagID = make_num_safe($_POST['tagID']);
			$tagName = make_var_safe($_POST['tagName']);
			$new_tagName = make_var_safe($_POST['new_tagName']);
		
			$tag = mf_query("SELECT tag FROM tags WHERE ID = \"$tagID\" AND userID = '$CURRENTUSERID' LIMIT 1");
			if (!$tag = mysql_fetch_assoc($tag))
				exit();

			$query = mf_query("SELECT ID FROM tags WHERE tag = \"$new_tagName\" LIMIT 1");
			if ($row = mysql_fetch_assoc($query)) {
					header("Location: ".make_link("profile","&action=g_tag_exist&tagID=$tagID&tagName=$tagName&new_tagName=$new_tagName&filter=7"));
			}
			else {
				mf_query("UPDATE tags SET tag = \"$new_tagName\" WHERE ID = '$tagID' LIMIT 1");
				mf_query("UPDATE forum_tags SET tag = \"$new_tagName\" WHERE tag = \"$tagName\"");
		
				load_tags();
				
				header("Location: ".make_link("profile","&action=g_tags_list&filter=7"));
			}
		}
break;

case "proc_move_tag":
if ($CURRENTUSER != 'anonymous' && $CURRENTSTATUS != "banned") {
			$tagID = make_num_safe($_POST['tagID']);
			$tag = mf_query("SELECT tag FROM tags WHERE ID = \"$tagID\" AND userID = '$CURRENTUSERID' LIMIT 1");
			if ($tag = mysql_fetch_assoc($tag)) {
				$total_use = make_num_safe($_POST['total_use']);
				$total_use_week = make_num_safe($_POST['total_use_week']);
				$total_use_month = make_num_safe($_POST['total_use_month']);
				$total_use_year = make_num_safe($_POST['total_use_year']);
				$tagName = make_var_safe($_POST['tagName']);
				$new_tagName = make_var_safe($_POST['move_to_tag']);
		
				mf_query("DELETE FROM tags WHERE ID = '$tagID' LIMIT 1");
				mf_query("UPDATE tags SET total_use = total_use + $total_use, total_use_week = total_use_week + $total_use_week, total_use_month = total_use_month + $total_use_month, total_use_year = total_use_year + $total_use_year WHERE tag = \"$new_tagName\" LIMIT 1");
				mf_query("UPDATE IGNORE forum_tags SET tag = \"$new_tagName\" WHERE tag = \"$tagName\"");
		
				load_tags();

				header("Location: ".make_link("profile","&action=g_tags_list&filter=7"));
			}
}
break;

case "g_delete_tag":
if ($CURRENTUSER != 'anonymous' && $CURRENTSTATUS != "banned") {
	$tagID = make_num_safe($_REQUEST['tagID']);
	$tag = mf_query("SELECT tag FROM tags WHERE ID = \"$tagID\" AND userID = '$CURRENTUSERID' LIMIT 1");
	if ($tag = mysql_fetch_assoc($tag)) {
		$forum_tag = mf_query("SELECT COUNT(tag) AS total FROM forum_tags WHERE tag = \"$tag[tag]\"");
		$forum_tag = mysql_fetch_assoc($forum_tag);
		if ($forum_tag['total'] > 4) {
			$thisContentObj = New contentObj;
			$thisContentObj->contentType = "generic";
			$thisContentObj->title = "$LANG[TAG_ERROR]";
			$thisContentObj->primaryContent = "<div style='margin-top:16px;'>$LANG[TAG_DEL_ERROR]";
			$thisContentObj->primaryContent .= "&nbsp; <a href='".make_link("profile","&amp;action=g_tags_list&amp;filter=7")."' class='button'>$LANG[BUTTON_BACK]</a></div>";
			$shardContentArray[] = $thisContentObj;
		}
		else {
			mf_query("DELETE FROM forum_tags WHERE tag = \"$tag[tag]\"");
			mf_query("DELETE FROM tags WHERE ID = '$tagID' AND userID = '$CURRENTUSERID' LIMIT 1");
			header("Location: ".make_link("profile","&action=g_tags_list&filter=7"));
		}
	}
}
break;


endswitch;
 
?>