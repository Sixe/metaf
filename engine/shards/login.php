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
// login.php
   
require("cryptpasslib.php");

switch ($action):

case "g_default":

	$thisContentObj = New contentObj;
	
	$thisContentObj->title = "$LANG[AUTHENTICATION]";	
	$thisContentObj->primaryContent = "<p style='margin-left: 10'> $LANG[USE_LOGIN_MENU].</p><br /><br />";

	$shardContentArray[] = $thisContentObj;
break;

case "g_tentatives":

	$thisContentObj = New contentObj;
	
	$dateStr = round((make_num_safe($_REQUEST['next']) - time()) / 60);
	
	$thisContentObj->title = "$LANG[ERROR_OCCURED] : ";
	$thisContentObj->primaryContent = "<div style='font-size:1.8em;'>$LANG[LOGIN_TENTATIVE4]</div> ";
	$thisContentObj->primaryContent .= "<div style='font-size:1.8em;'>$LANG[LOGIN_TENTATIVE5] $dateStr $LANG[LOGIN_TENTATIVE6] ";
	$thisContentObj->primaryContent .= " <span style='font-size:1.0em;'>(<a href='".make_link("login","&amp;action=g_recover_pass")."'>$LANG[FORGOTTEN_PASSWORD]</a>)</span></div>";
	
	$shardContentArray[] = $thisContentObj;
break;

case "g_error":

	$thisContentObj = New contentObj;
	
	$thisContentObj->title = "$LANG[ERROR_OCCURED] : ";
	$thisContentObj->primaryContent = "<div style='font-size:1.8em;'>$LANG[VERIFY_USER_PASS]. </div> ";
	$tentatives = "0";
	if (is_numeric($_REQUEST['tent']))
		$tentatives = 2 - $_REQUEST['tent'];
	$thisContentObj->primaryContent .= "<div><span style='font-size:1.8em;'>$LANG[LOGIN_TENTATIVE1] $tentatives $LANG[LOGIN_TENTATIVE2]</span>";
	
	$thisContentObj->primaryContent .= " <span style='font-size:1.0em;'>(<a href='".make_link("login","&amp;action=g_recover_pass")."'>$LANG[FORGOTTEN_PASSWORD]</a>)</span></div>";
	if ($tentatives == 0)
		$thisContentObj->primaryContent .= "<div style='font-size:1.8em;'>$LANG[LOGIN_TENTATIVE3]</div>";
	
	$shardContentArray[] = $thisContentObj;
break;
 
case "g_error1":


	$thisContentObj = New contentObj;
	
	$thisContentObj->title = "$LANG[ERROR_OCCURED] : ";
	$thisContentObj->primaryContent = "<h2>$LANG[ACCOUNT_NOT_ACTIVATED]. </h2> ";
	
	$shardContentArray[] = $thisContentObj;
break;

case "g_error2":


	$thisContentObj = New contentObj;
	
	$thisContentObj->title = "$LANG[ERROR_OCCURED] : ";
	$thisContentObj->primaryContent = "<h2>$LANG[ERROR_HAS_OCCURED_TEXT11] </h2> ";
	
	$shardContentArray[] = $thisContentObj;
break;

case "proc_login":
    $login_name = make_var_safe($_POST['login_name']);
    $login_pass = $_POST['login_pass'];

    if (($login_name == "") || ($login_pass == "")) {

        header("Location: ".make_link("login","&action=g_error&notok=1"));
        exit();
    }
     
	$verif_login = false;
	$result = mf_query("SELECT username, password, salt, email, ID, userstatus, tentatives, next_tentative, crypt_method FROM users WHERE LOWER(username) = \"".mb_strtolower($login_name,'UTF-8')."\" LIMIT 1");
	if ($row = mysql_fetch_assoc($result))
		$verif_login = true;
	else if (stristr($login_name, "@")) {
		$result = mf_query("SELECT username, password, salt, email, ID, userstatus, tentatives, next_tentative, crypt_method FROM users WHERE LOWER(email) = \"".mb_strtolower($login_name,'UTF-8')."\" LIMIT 1");
		if ($row = mysql_fetch_assoc($result))
			$verif_login = true;
	}
	if ($verif_login) {
		$verif_login = false;
		if ($row['next_tentative'] && $row['next_tentative'] < time()) {
			$row['tentatives'] = "0";
			mf_query("UPDATE users SET tentatives = '0' AND next_tentative = '0' WHERE ID=\"$row[ID]\" LIMIT 1"); // Reset tentatives counter
		}
		if ($row['userstatus'] == "pending") {	
			$verif_login = false;
			header("Location: index.php?shard=login&action=g_error1");
			break;	 // Unverified account
		}
		else if ($row['tentatives'] == "3") {
			$verif_login = false;
			header("Location: index.php?shard=login&action=g_tentatives&next=$row[next_tentative]");
			break;	 // Already 3 wrong password 
		}

		if (!$row['crypt_method'] && $row['password'] == sha1($login_pass)) {
			$verif_login = true;
		}
		else if ($row['crypt_method'] == "blowfish") {
		    $stored_password = $row['password'];
		    $stored_salt2 = $row['salt'];
		    $verif_pass = crypt($login_pass . $stored_salt2, $stored_password); //compare the crypt of input+stored_salt2 to the stored crypt password
		    if ($verif_pass == $stored_password) {
		        $verif_login = true;
		    }
		}
		// Apply login
		if ($verif_login) {
			// Verify if password encryption method has changed
			$reload_user = false;
			if ($row['crypt_method'] != $siteSettings['crypt_method'] && !$row['crypt_method']) {
				crypt_password($login_pass,$row['ID']);
				$reload_user = true;
			}			
			else if ($row['crypt_method'] != $siteSettings['crypt_method']) {
				$sha1password = sha1($login_pass);
				mf_query("UPDATE users SET password = '$sha1password', salt = '', crypt_method = '' WHERE ID=\"$row[ID]\" LIMIT 1");
				$reload_user = true;
			}			
			if ($reload_user) {
				$result = mf_query("SELECT ID, username, password, tentatives FROM users WHERE ID=\"$row[ID]\" LIMIT 1");
				$row = mysql_fetch_assoc($result);
			}

			$end_remember = 0;
			if (isset($_POST['rememberme']))
				$end_remember = time()+86400000; // 1000 days

			if ($row['tentatives'] > 0)
			mf_query("UPDATE users SET tentatives = '0' AND next_tentative = '0' WHERE ID=\"$row[ID]\" LIMIT 1");

			setcookie("b6".$siteSettings['db']."username", "$row[username]", $end_remember, "/");
			setcookie("b6".$siteSettings['db']."password", "$row[password]", $end_remember, "/");
			setcookie("b6".$siteSettings['db']."userID", "$row[ID]", $end_remember, "/");
			
			$shard = $siteSettings["defaultShard"];
			
			if ((isset($_REQUEST['login_redirect'])) && ($_REQUEST['login_redirect'] != ""))
				header("Location: $_REQUEST[login_redirect]");
            else if (isset($_SERVER['HTTP_REFERER']) && !strstr( $_SERVER['HTTP_REFERER'], "shard=login") && !strstr($_SERVER['HTTP_REFERER'], "shard=adduser"))
				header("Location: $_SERVER[HTTP_REFERER]");
			else
				header("Location: index.php?shard=$shard");

		}
		else {          
			$dateStr = time() + 3600;
			mf_query("UPDATE users SET tentatives = tentatives + 1 , next_tentative = '$dateStr' WHERE ID=\"$row[ID]\" LIMIT 1");
			header("Location: index.php?shard=login&action=g_error&notok=2&tent=$row[tentatives]");
		}			
	}
	else {
		header("Location: index.php?shard=login&action=g_error2&notok=3");
		exit();				
	}

break;
    
case "g_proc_logout":

	$thisContentObj = New contentObj;
	$thisContentObj->contentType = "generic";
	$thisContentObj->title = "$LANG[PROMPT_LOGOUT]";	
	if (!$siteSettings['facebook'])
		$thisContentObj->primaryContent = "<div class='deleteConfirm'><form action='index.php?shard=login' method='post'><input type='hidden' name='action' value='proc_logout' /><span style='float:left;'><input type='checkbox' name='logoutDidCheck' /> $LANG[YES]</span><span style='float: right;'><input type='submit' class='button' value=\"$LANG[LOGOUT]\" /></span><div class='clearfix'></div></form></div>";
	else {
		$thisContentObj->primaryContent = "<div class='deleteConfirm'>Veuillez vous déconnecter via l'interface de déconnexion de <b><a href='http://www.facebook.com' target='_blank'>Facebook</a></b> ou connectez-vous en vous identifiant : ";
	$thisContentObj->primaryContent .= "<b><a href=\"javascript:toggleLayer('loginform_logout');\" title=\"$LANG[LOGIN]\">$LANG[LOGIN]</a></b>
	<div id='loginform_logout' style='text-align:center;'><form method='post' action='index.php?shard=login&amp;action=proc_login'>     
        $LANG[USERNAME]:<br />
        <input type='text' size='10' name='login_name' value='' /><br />
        $LANG[PASSWORD]:<br />
        <input type='password' size='10' name='login_pass' /><br /><br />
        <input class='button' type='submit' value='$LANG[LOGIN]' /></form></div>";

	}

	$shardContentArray[] = $thisContentObj;    
break;
 
case "proc_logout":
	if (array_key_exists("logoutDidCheck", $_POST)) {
		if ($_POST['logoutDidCheck'] == "on") 	{
			setcookie("b6".$siteSettings['db']."username", "", time()-3600, "/");
			setcookie("b6".$siteSettings['db']."userID", "", time()-3600, "/");
			setcookie("b6".$siteSettings['db']."password", "", time()-3600, "/");
		}
		
	}
	$shard = $siteSettings["defaultShard"];
		
	header("Location: ".make_link("$shard"));
break;
 
case "logout":

	setcookie("b6".$siteSettings['db']."username", "", time()-3600, "/");
	setcookie("b6".$siteSettings['db']."userID", "", time()-3600, "/");
	setcookie("b6".$siteSettings['db']."password", "", time()-3600, "/");
	setcookie("fblogged", "", time()-3600, "/");
	$shard = $siteSettings["defaultShard"];
	header("Location: ".make_link($shard));
break;
 
case "g_login_success":

	$thisContentObj = New contentObj;
	
	$thisContentObj->title = "$LANG[LOGIN_SUCCESS]";
	$thisContentObj->primaryContent = "$LANG[LOGGED_IN_AS] " . $_REQUEST['cookieusername'];

	$shardContentArray[] = $thisContentObj;
	header("Location: " . $_SERVER['HTTP_REFERER']);
break;

case "g_login_failed":

	$thisContentObj = New contentObj;

	$thisContentObj->title = "$LANG[LOGIN_FAILED]";
	$thisContentObj->contentType = "generic";
	$thisContentObj->primaryContent = "";

	$shardContentArray[] = $thisContentObj;
break;
 
case "g_logout_success":

	$thisContentObj = New contentObj;
	
	$thisContentObj->title = "$LANG[LOGOUT_SUCCESS]";
	$thisContentObj->primaryContent = "$LANG[LOGGED_OUT].";
	
	$shardContentArray[] = $thisContentObj;
break;

case "g_recover_pass":
	$thisContentObj = New contentObj;
	$thisContentObj->contentType = "generic";
	$thisContentObj->title = "$LANG[PASS_RESET3]";
	$thisContentObj->primaryContent = "<div>$LANG[PASS_RESET4]</div><br /><br />
	<form method='post' action=\"".$_SERVER['PHP_SELF']."?shard=login&amp;action=g_recover_pass\" name='recover_pass'>
	<center><table>
	<tr><td style='text-align:right;'>$LANG[MAIL_ACTIVATION11] </td><td><input type='text' name='nick_user' class='bselect'/></td></tr>
	<tr><td style='text-align:right;'>$LANG[USER_PROFILE_EMAIL] </td><td><input type='text' name='email_user' class='bselect'/></td></tr>
	<tr><td></td><td><input type='submit' value=\"$LANG[PASS_RESET5]\" class='button'/></td></tr>
	</form></table></center></p>";

	if ((isset($_POST['nick_user'])) && (isset($_POST['email_user']))) {
		$nick_user = make_var_safe($_POST['nick_user']);
		$email_user = make_var_safe($_POST['email_user']);

	$result = mf_query("SELECT ID, username FROM users where LOWER(username) =\"".mb_strtolower($nick_user,'UTF-8')."\" AND email=\"$email_user\" LIMIT 1");

	if ($row = mysql_fetch_assoc($result)) {
			$new_password = $nick_user."_";
			$possible = "0123456789bcdfghjkmnpqrstvwxyz";
			$i = 0; 
		while ($i < 12) {
				$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
				if (!strstr($new_password, $char)) {
					$new_password .= $char;
					$i++;
				}
			}
			$auth = sha1($new_password);
		mf_query("UPDATE users SET reset_pass=\"".$auth."\" WHERE ID=\"$row[ID]\" LIMIT 1");

			srand((double)microtime()*1000000);
			$boundary = md5(uniqid(rand()));
			$header ="From: $siteSettings[titlebase] <$siteSettings[alert_mail]>\n";
			$header .="Reply-To: $siteSettings[alert_mail] \n";
			$header .="MIME-Version: 1.0\n";
			$header .="Content-Type: multipart/alternative;boundary=$boundary\n";

			$to = $email_user;
			$subject = $LANG['PASS_RESET'];

			$message = "\nThis is a multi-part message in MIME format.";
			$message .="\n--" . $boundary . "\nContent-Type: text/html;charset=\"utf-8\"\n\n";
			$message .= "<html><body>\n";
			$message .="<img src='" . $siteSettings['siteurl'] . "/engine/grafts/" . $siteSettings['graft'] . "/images/MailheaderImage.png'><br/><br/>\n";
			$message .= "\n$LANG[PASS_RESET]<br/>\n";
			$message .= "\n$LANG[PASS_RESET1]:<br/>\n";
			$message .= "http://".$siteSettings['siteurl']."/".make_link("login","&action=g_newpass&auth=$auth")."<br><br>\n";
			$message .= "$LANG[DO_NOT_ANSWER]\n";
			$message .="\n--" . $boundary . "--\n end of the multi-part";

			if(@mail($to, $subject, $message, $header))
				$thisContentObj->primaryContent = "<p><b>$LANG[PASS_RESET2]</b></p>";
			else
				$thisContentObj->primaryContent = "<p>$LANG[NO_MAIL_SERVER]</p>";
		}
		else
			$thisContentObj->primaryContent .= "<p>$LANG[LOGIN_ERROR1]</p>";
	}
	$shardContentArray[] = $thisContentObj;
break;

case "g_newpass":
	if ($_REQUEST['auth']) {
		$auth = make_var_safe($_REQUEST['auth']);

		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->title = "$LANG[PASS_RESET]";

		$req = mf_query("SELECT username, reset_pass FROM users WHERE reset_pass=\"$auth\" LIMIT 1");
		if ($row = mysql_fetch_assoc($req)) {
			$thisContentObj->primaryContent = "<div style='font-size:1.3em;'>$LANG[MAIL_ACTIVATION11] <span class='bold'>$row[username]</span></div>
				<form method='post' action=\"index.php?shard=login&amp;action=g_procnewpass\" name='new_pass'>
				<input type='hidden' name='auth' value=\"$auth\" />
				<center><table>
				<tr><td style='text-align:right;'>$LANG[PROMPT_CREATE_PASSWORD] </td><td><input class='text' type='password' name='password' size='20' maxlength='16' /></td></tr>
				<tr><td style='text-align:right;'>$LANG[PROMPT_CREATE_PASSWORD_VERIFY] </td><td><input class='text' type='password' name='vpassword' size='20' maxlength='16' /></td></tr>
				<tr><td></td><td><input type='submit' value=\"$LANG[SUBMIT]\" class='button'/></td></tr>
				</form></table></center></p>";
		
		}
		else
			$thisContentObj->primaryContent .= "<p>$LANG[PASS_RESET_ERROR]</p>";

		$shardContentArray[] = $thisContentObj;
	}
break;

case "g_procnewpass":
	if ($_POST['auth']) {
		$error = "";
	$auth = make_var_safe($_POST['auth']);
	if (array_key_exists("password" , $_POST ) == TRUE )
		$password = make_var_safe(htmlspecialchars($_POST["password"]));
	if (array_key_exists("vpassword" , $_POST ) == TRUE )
		$vpassword = make_var_safe(htmlspecialchars($_POST["vpassword"]));

		if ($password != $vpassword)
			$error = $LANG['ERROR_HAS_OCCURED_TEXT1'];
		else if (strlen($password) < 6)
			$error = $LANG['ERROR_HAS_OCCURED_TEXT2'];

		$thisContentObj = New contentObj;
		$thisContentObj->contentType = "generic";
		$thisContentObj->title = "$LANG[PASS_RESET]";
		
		if ($error)
			$thisContentObj->primaryContent = $error." <a href='".make_link("login","&amp;action=g_newpass&amp;auth=$auth")."' class='button'>$LANG[BUTTON_BACK]</a>";
		else {
		$req = mf_query("SELECT ID, username FROM users WHERE reset_pass=\"$auth\" LIMIT 1");
		if ($row = mysql_fetch_assoc($req)) {
			$encpassword = sha1($password);
			mf_query("UPDATE users SET password = \"$encpassword\", reset_pass = '', tentatives = '0', next_tentative = '' WHERE reset_pass = \"$auth\" LIMIT 1");
			if ($siteSettings['crypt_method'] == "blowfish") {
				crypt_password($password,$row['ID']);
				$result = mf_query("SELECT password FROM users WHERE ID='$row[ID]' LIMIT 1");
				$result = mysql_fetch_assoc($result);
				$encpassword = $result['password'];
			}

			setcookie("b6".$siteSettings['db']."username", "$row[username]", time()+864000000, "/");
			setcookie("b6".$siteSettings['db']."password", "$encpassword", time()+864000000, "/");
			setcookie("b6".$siteSettings['db']."userID", "$row[ID]", time()+864000000, "/");
			header("Location: ".make_link("login","&action=g_procnewpass_success"));
			break;

			}
			else
				$thisContentObj->primaryContent .= "<p>$LANG[PASS_RESET_ERROR]</p>";
		}

		$shardContentArray[] = $thisContentObj;
	}
break;

case "g_procnewpass_success":

	$thisContentObj = New contentObj;
	
	$thisContentObj->title = "$LANG[PASS_RESET_SUCCESS]";
	$thisContentObj->contentType = "generic";
	$thisContentObj->primaryContent = "";

	$shardContentArray[] = $thisContentObj;
break;

case "g_confirm_success":

	$thisContentObj = New contentObj;
	
	$thisContentObj->title = "$LANG[ACCOUNT_CONFIRM_SUCCESS]";
	$thisContentObj->contentType = "generic";
	$thisContentObj->primaryContent = "";

	$shardContentArray[] = $thisContentObj;
	break;

    endswitch;
?>