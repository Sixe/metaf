<?php
    // fblogin.php
   
	require("adduserlib.php");
   
    if ($CURRENTUSERID)
		header("Location: index.php?shard=".$siteSettings['defaultShard']);

    $errormsg = "";
	if (array_key_exists("errormsg" , $_REQUEST ) == TRUE )
        $errormsg = make_var_safe(htmlspecialchars($_REQUEST["errormsg"]));

	switch ($action):

    case "g_error":
		$thisContentObj = New contentObj;
		$thisContentObj->title = "<div class='bold'>".$LANG['ERROR_HAS_OCCURED']."</div>";
		$thisContentObj->primaryContent = "<div style='font-size:1.5em;margin-bottom:16px;'>".$LANG['ERROR_HAS_OCCURED_TEXT'.$errormsg]."</div>";
		$thisContentObj->primaryContent .= "<span class='button' onclick='history.back()'>$LANG[BUTTON_BACK]</span>";
		$shardContentArray[] = $thisContentObj;   
    break;


    case "g_default":
    
		if (isset($_REQUEST['location']))
			$location = $_REQUEST['location'];
		else {
			$location = $_SERVER['REQUEST_URI'];
			if (substr($location,0,1) == "/");
				$location = substr($location,1,strlen($location) -1);
			$location = urlencode($location);
		}

		$thisContentObj = New contentObj;
		$thisContentObj->primaryContent = "<div style='margin:10px;'><div style='font-size:2em;margin-bottom:8px;'>$LANG[FB_CONNECT]</div>";

		$fbemail = $fbuser->email;
		if (stristr($fbemail,"proxymail.facebook")) {
			$fbemail = "Anonyme Facebook";
		}
		
		if ($fbuser->id)
			setcookie("b6_fb".$fbuser->id, "", time()-2000, "/");

		$userlist = "";
		$i = 0;
		if ($fbuser->email) {
			$query = mf_query("SELECT ID, username FROM users WHERE email = '".$fbuser->email."'"); 
			while ($row = mysql_fetch_assoc($query)) {
				$userlist .= "<div style='margin-bottom:12px;'><div style='display:inline-block;font-weight:bold;min-width:120px;'>$row[username]</div>&nbsp;<span class='button' onclick=\"document.location.href='index.php?shard=fblogin&amp;action=asso_user&amp;userID=$row[ID]&amp;location=$location';\">$LANG[FB_CONNECT_ASSO]</span></div>";
				$i ++;
			}
		}

		$thisContentObj->primaryContent .= "<div style='font-size:1.2em;margin-bottom:28px;'>$LANG[FB_CONNECT_EMAIL1] \"<span style='font-weight:bold;'>".$fbemail."</span>\" $LANG[FB_CONNECT_EMAIL2] \"<span style='font-weight:bold;'>".$fbuser->name."</span>\"</div>";
		if ($i > 0) {
			$thisContentObj->primaryContent .= "<div style='border:1px solid silver;padding:8px;'><div style='font-size:1.7em;margin-bottom:12px;'>$LANG[FB_CONNECT_ASSO_EXIST]</div>";
			$thisContentObj->primaryContent .= "<div style='font-size:1.2em;margin-left:92px;'>";
			$thisContentObj->primaryContent .= "$userlist</div>";
			$thisContentObj->primaryContent .= "</div><div style='margin-top:16px;border:1px solid silver;padding:8px;'><div style='font-size:1.7em;margin-bottom:8px;'><span style='font-weight:bold;'>$LANG[FB_CONNECT_OR]</span> $LANG[FB_CONNECT_ASSO_FB]</div>";

		}
		else {
	    	$thisContentObj->primaryContent .= "<div style='font-size:1.2em;'>$LANG[FB_CONNECT_EMAIL3] \"<span style='font-weight:bold;'>".$fbemail."</span>\" $LANG[FB_CONNECT_EMAIL4] \"<span style='font-weight:bold;'>".$fbuser->name."</span>\" $LANG[FB_CONNECT_EMAIL5]</div>";
			$thisContentObj->primaryContent .= "<div style='margin-top:16px;border:1px solid silver;padding:8px;'><div style='font-size:1.7em;'>$LANG[FB_CONNECT_ASSO_FB2]</div>";
		}
		$thisContentObj->primaryContent .= "<div style='margin-left:32px;'>
				<form name='newfbaccount' action='index.php?shard=fblogin&amp;action=newaccount&amp;location=$location' method='post'>
					<div>
						<div style='display:inline-block;width:180px;font-size:1.5em;text-align:right;'>$LANG[PROMPT_CREATE_USERNAME]: </div>
						<input id='newNick' size='10' type='text' value=\"".$fbuser->name."\" name='newNick' style='font-weight:bold;color:red;font-size:1.9em;' class='bselect' onkeyup=\"verif_nick();\" />&nbsp;<span id='messagenewNick' style='font-size:1.5em;'></span>
					</div>
					<div style='font-size:1.2em;'>$LANG[FB_CONNECT_PASS1]</div><div style='border:1px solid silver;padding-top:2px;padding-bottom:3px;'>
					<div style='font-size:0.9em;text-align:right;margin-right:2px;'>($LANG[FB_CONNECT_PASS2])</div>
					<div>
						<div style='display:inline-block;width:180px;font-size:1.2em;margin-top:4px;text-align:right;'>$LANG[FB_CONNECT_PASS3] </div>
						<input id='password' size='10' type='password' value='' name='password' style='font-weight:bold;color:red;font-size:1.2em;' class='bselect' onkeyup=\"verif_pass();\" />&nbsp;<span id='messagePass' style='font-size:1.5em;'></span>
					</div>
					<div>
						<div style='display:inline-block;width:180px;font-size:1.2em;margin-top:4px;text-align:right;'>$LANG[FB_CONNECT_PASS4] </div>
						<input id='vpassword' size='10' type='password' value='' name='vpassword' style='font-weight:bold;color:red;font-size:1.2em;' class='bselect' onkeyup=\"verif_vpass();\" />&nbsp;<span id='messageVPass' style='font-size:1.5em;'></span>
					</div>
					</div>
					<div style='height:30px;margin-left:184px;margin-top:8px;'>
						<input id='submitnewNick' type='submit' style='font-size:1.2em;display:none;' value=\"$LANG[FB_CONNECT_CREATE]\" class='button'/>
					</div>
				</form>
			</div>";
		$thisContentObj->primaryContent .= "<script type=\"text/javascript\">verif_nick();</script></div>";
		
		$thisContentObj->primaryContent .= "<div style='margin-top:16px;border:1px solid silver;padding:8px;'><div style='font-size:1.7em;margin-bottom:12px;'><span style='font-weight:bold;'>$LANG[FB_CONNECT_OR]</span> $LANG[FB_CONNECT_NOT]</div>";
		$thisContentObj->primaryContent .= "<a href='index.php?shard=fblogin&amp;action=nofb&amp;location=$location' style='margin-left:216px;font-size:1.2em;' class='button'>$LANG[FB_CONNECT_NOT2]</a><div style='height:12px;'></div></div>";


		$shardContentArray[] = $thisContentObj;
    break;

    case "nofb":
	
		$location = $_REQUEST['location'];
		if ($fbuser->id)
			setcookie("b6_fb".$fbuser->id, "not", time()+864000000);
			
		header("Location: ".urldecode($location));
	
	break;


	case "newaccount":

		if (!$fbuser->email)
			exit();

		$location = $_REQUEST['location'];
		$user = make_var_safe($_POST['newNick']);
		$password = make_var_safe($_POST['password']);
		$vpassword = make_var_safe($_POST['vpassword']);
		
		if (strlen($user) < 3) {
			header("Location: ".make_link("fblogin","&action=g_error&errormsg=6&location=$location"));
			exit();
		}

		// Checking for password verification
		if ($password != $vpassword) {
			header("Location: ".make_link("fblogin","&action=g_error&errormsg=1&location=$location"));
			exit();
		}

		// Checking for password length
		if ($password && strlen($password) < 6) {
			header("Location: ".make_link("fblogin","&action=g_error&errormsg=2&location=$location"));
			exit();
		}

		// Check for existing username
		$result = mf_query("SELECT count(*) from users where LOWER(username)=\"".mb_strtolower($user,'UTF-8')."\"");
		$result = mysql_fetch_row($result);
		if ($result[0] > 0) {
			header("Location: ".make_link("fblogin","&action=g_error&errormsg=3&location=$location"));
			exit();
		}

		$datejoined = time();
		if ($password)
			$encpassword = sha1($password);
		else {
			$possible = "0123456789bcdfghjklmnpqrstvwxyz,;ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
			$i = 0; 
			while ($i < 16) { 
				$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
				if (!strstr($password, $char)) { 
					$password .= $char;
					$i++;
				}
			}
			$encpassword = sha1($password);
		}

		$avatar_path = "";
		
		mf_query("INSERT into users (username, password, email, rating, datejoined, userstatus, avatar, facebookID) values (\"$user\", \"$encpassword\", \"".$fbuser->email."\", 0, '$datejoined', NULL, \"$avatar_path\", '".$fbuser->id."' )");
		
		$userID = mf_query("SELECT ID from users where datejoined='$datejoined' and username=\"$user\" limit 1");
		if ($row = mysql_fetch_assoc($userID)) {
			mf_query("INSERT into forum_user_nri (name, userID) values (\"$user\", $row[ID])");
			if ($siteSettings['crypt_method'] == "blowfish")
				crypt_password($password,$row['ID']);
			
			header("Location: ".urldecode($location));
			exit();
		}
     
    break;


    case "asso_user":
    
		if (!$fbuser->email)
			exit();

		$location = $_REQUEST['location'];
		$userID = make_num_safe($_REQUEST['userID']);

		$query = mf_query("SELECT email FROM users WHERE ID = '$userID' LIMIT 1"); 
		$verif = mysql_fetch_assoc($query);
		if (($fbuser->email) == $verif['email']) {
			mf_query("UPDATE users SET facebookID = '".$fbuser->id ."' WHERE ID = '$userID' LIMIT 1");
		}
		header("Location: ".urldecode($location));

    break;

    endswitch;
?>