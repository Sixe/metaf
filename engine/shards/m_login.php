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

// m_login.php

    if ($CURRENTUSER != "anonymous" and $CURRENTUSER != "bot" and $CURRENTUSER != "") {
        $thisMenu->menuTitle = "<div class='title_handle' style='width:165px;'>$LANG[MAIN_WIDGET]</div>";
		
		$pAdmin = "";
		$plevel1 = "";
		$plevel2 = "";
		$plevel4 = "";
		$plevel3 = "";
		$plevel7 = "";
		$plevel9 = "";
		$sysadmin = "";
		if (isInGroup($CURRENTUSER, 'admin')) $pAdmin = "1";
		if (isInGroup($CURRENTUSER, 'level1')) $plevel1 = "1";
		if (isInGroup($CURRENTUSER, 'level2')) $plevel2 = "1";
		if (isInGroup($CURRENTUSER, 'level4')) $plevel4 = "1";
		if (isInGroup($CURRENTUSER, 'level3')) $plevel3 = "1";
		if (isInGroup($CURRENTUSER, 'level7')) $plevel7 = "1";
		if (isInGroup($CURRENTUSER, 'level9')) $plevel9 = "1";
		if (isInGroup($CURRENTUSER, 'sysadmin')) $sysadmin = "1";
		
		if ($pAdmin || $sysadmin) {
			$load = "";
			if (@$loadavg = trim(file_get_contents('/proc/loadavg'))) {
				$loads = explode(" ",$loadavg);
				$load = "/ Load ".trim($loads[0]);
			}

			$tenMinutes = time() - 300;
			$s = mf_query("select count(ID) as counter from users where lat > " . (time() - 300) . " ");
			$row=mysql_fetch_assoc($s);
			$counter = $row['counter'];
			$thisMenu->menuTitle .= "<div style='position:absolute;display:inline;margin-left:-85px;' id='loadavg'><a  href=\"javascript:loadavg();\">U $counter $load</a></div>";
		}

		if (!array_key_exists('mf_speaker', $_COOKIE)) {
			$sound_q = mf_query("select sound_alert from users where ID = '$CURRENTUSERID' limit 1");
			$sound_q = mysql_fetch_assoc($sound_q );
			if ($sound_q['sound_alert'])
				$sound = "speakerOn";
			else
				$sound = "speakerOff";
			
			setcookie("mf_speaker", "$sound", time()+864000000, "/");
		}
		else
			$sound = $_COOKIE['mf_speaker'];

		$thisMenu->menuContentArray[] = "
			<div style='text-align:right;float: right;margin-top: 0px;width:18px;'>
			<span id=\"speaker\" class=\"$sound\" onclick=\"toggleSound();\"></span></div>
			$LANG[WELCOME_BACK], <b>$CURRENTUSER</b> 
			<a href='".make_link("login","&amp;action=logout")."' style='vertical-align:baseline;'><span id='mflogout'>
			<img src='engine/grafts/" . $siteSettings['graft'] . "/images/logout.jpg' border='0' alt='$LANG[LOGOUT]' title='$LANG[LOGOUT]' /></span></a>";
//		$thisMenu->menuContentArray[] = "<b><a href='"make_link("dons")."'>Faire un don via Paypal</a></b>";


		if ($pAdmin || $sysadmin)
			$thisMenu->menuContentArray[] = "<small><a href='".make_link("admin")."'>$LANG[ADMIN_CONTROL_PANEL]</a></small>";

		$team = "";
		if ($CURRENTUSER != "anonymous") {
			$team = "<a href='".make_link("teams")."'>$LANG[TEAM_SHARE]</a>";
			if ($CURRENTUSERINTEAM)
				$team .= " / <a href='".make_link("teams","&amp;action=g_files")."'>$LANG[FILES]</a>";
		}

		if ($team)
			$thisMenu->menuContentArray[] = "<small>$team</small>";
			
			
		$thisMenu->menuContentArray[] = "<div style='text-align:center;width:100%;margin-top: 8px;'>
				<small><a href='#' onclick='restoreShards();'>$LANG[RESET_SHARDS] 
				<img src='engine/grafts/" . $siteSettings['graft'] . "/images/reinit.png' style='vertical-align:middle;' alt='$LANG[RESET_SHARDS]' title='$LANG[RESET_SHARDS]' />
				</a></small></div>";
				
	}
    else {
        $thisMenu->menuTitle = "$LANG[AUTHENTICATION]";

		$thisMenu->menuContentArray[] = "<b><a href=\"javascript:toggleLayer('loginform');\" title=\"$LANG[LOGIN]\">$LANG[LOGIN]</a></b>
		<div id='loginform'><form method='post' action='index.php?shard=login&amp;action=proc_login'>     
			<div>$LANG[EMAIL_OR_USERNAME]:</div>
			<div><input class='search' type='text' size='20' name='login_name' maxlength='100' /></div>
			<div>$LANG[PASSWORD]:</div>
			<div><input class='search' type='password' size='10' name='login_pass' /></div>
			<div><input type='checkbox' name='rememberme' style='vertical-align:middle;' /> $LANG[REMEMBER_ME]</div><br />
			<input class='button' type='submit' value=\"$LANG[LOGIN]\" />
            </form></div>";

		if ($CURRENTUSER != "bot") {
        	$thisMenu->menuContentArray[] = "<div style='margin-top:8px;'><small><a href='".make_link("adduser")."'>$LANG[GET_ACCOUNT]</a></small>";
        	$thisMenu->menuContentArray[] = "<small><a href='".make_link("login","&amp;action=g_recover_pass")."'>$LANG[FORGOTTEN_PASSWORD]</a></small></div>";

			if ($siteSettings['verifyEmail'] == "checked")
				$thisMenu->menuContentArray[] = "<small><a href='".make_link("adduser","&amp;action=g_resendauthent")."'>$LANG[MAIL_ACTIVATION]</a></small>";
		}

    }
	$shards_priority = "";
	if (!array_key_exists('shards_priority', $_COOKIE))
		$shards_priority = "checked='checked'";
	else if ($_COOKIE['shards_priority'] == "yes")
			$shards_priority = "checked='checked'";

	$thisMenu->menuContentArray[] = "<div id='shards_priority_div' style='text-align:center;width:100%;display;block;'><small><label for='shards_priority'>$LANG[PRIORITY_TO_SHARDS]:</label><input id='shards_priority' class='cselect' type='checkbox' name='shards_priority' onclick=\"shards_priority();\" $shards_priority style='vertical-align:middle;' /></small></div>";
	$thisMenu->menuContentArray[] = "<div id='main_max_width' style='text-align:center;width:100%;margin-top:4px;display;none;'><span onclick=\"mainMaxWidth();\" class='button_mini' title=\"$LANG[MAIN_MAX_WIDTH_TEXT]\">$LANG[MAIN_MAX_WIDTH]</span></div>";
	$fbdisplay = "block";
	$fbhidden = "none";
	if (isset($_COOKIE['fb_widget'])) {
		$fbdisplay = "none";
		$fbhidden = "block";
	}
	if (defined("FACEBOOK_APP_ID") && ($fbcookie || $CURRENTUSER == "anonymous") && !$FACEBOOK_OFF) {
		$facebook_widget = "<div id='fbwidget_off' style='display:$fbhidden;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menudown.gif' style='float:right;' alt='+' title=\"$LANG[FACEBOOK_WIDGET_DISPLAY]\" onclick=\"toggleLayer('fbwidget_off'); toggleLayer('fbwidget_on'); fb_widget('on');\"/></div><div id='fbwidget_on' style='display:$fbdisplay;'><img src='engine/grafts/" . $siteSettings['graft'] . "/images/menuup.gif' style='margin-top:-4px;float:right;' alt='+' title=\"$LANG[FACEBOOK_WIDGET_HIDE]\" onclick=\"toggleLayer('fbwidget_off'); toggleLayer('fbwidget_on'); fb_widget('off');\"/>";
		if ($CURRENTUSER != "bot")
			$facebook_widget .= "<div style='text-align:center;margin-top:4px;'>
        <div id=\"fb-root\"></div>
        <script type=\"text/javascript\">
            var button;
            var userInfo;
            
            window.fbAsyncInit = function() {
                FB.init({ appId: '".FACEBOOK_APP_ID."', 
                    status: true, 
                    cookie: true,
                    xfbml: true,
                    oauth: true});

               function updateButton(response) {
                    button       =   document.getElementById('fb-auth');
                    userInfo     =   document.getElementById('user-info');
                    
                    if (response.authResponse) {
                        //user is already logged in and connected
                        FB.api('/me', function(info) {
                            login(response, info);
                        });
                        
                        button.onclick = function() {
                            FB.logout(function(response) {
                                logout(response);
                            });
						};
						if (document.getElementById('mflogout')) {
							document.getElementById('mflogout').onclick = function() {
								FB.logout(function(response) {
									logout(response);
								});
							};
						}
						if (document.getElementById('fb-auth2')) {
							document.getElementById('fb-auth2').innerHTML = '".$LANG['FB_CONNECT_NOT2']."';
							document.getElementById('fb-auth2').onclick = function() {
								FB.logout(function(response) {
									logout(response);
								});
							};
						}
                    } else {
                        //user is not connected to your app or logged out
						SetCookie('fblogged', 'vide', -1);
                        button.innerHTML = '".$LANG['FACEBOOK_LOGIN']."';
                        button.onclick = function() {
                            FB.login(function(response) {
                                if (response.authResponse) {
                                    FB.api('/me', function(info) {
                                        login(response, info);
                                    });	   
                                } else {
                                    //user cancelled login or did not grant authorization
                                }
                            }, {scope:'email'});  	
                        }
						if (document.getElementById('fb-auth2')) {
							document.getElementById('fb-auth2').innerHTML = '".$LANG['FACEBOOK_CONNECT']."';
							document.getElementById('fb-auth2').onclick = function() {
								FB.login(function(response) {
									if (response.authResponse) {
										FB.api('/me', function(info) {
											login(response, info);
										});	   
									} else {
										//user cancelled login or did not grant authorization
									}
								}, {scope:'email'});  	
							}
						}
                    }
                }
                
                // run once with current status and whenever the status changes
                FB.getLoginStatus(updateButton);
                FB.Event.subscribe('auth.statusChange', updateButton);	
            };
            (function() {
                var e = document.createElement('script'); e.async = true;
                e.src = document.location.protocol 
                    + '//connect.facebook.net/".$LANG['FB_LANG']."/all.js';
                document.getElementById('fb-root').appendChild(e);
            }());
            
            
            function login(response, info){
                if (response.authResponse) {
                    button.innerHTML                               = '".$LANG['FACEBOOK_LOGOUT']."';
					
					var dataLine = info.id + ',' + info.email;
					
					if (info.email == 'undefined') {
						SetCookie('fblogged', '', -1);
					}
					else {
					if (document.getElementById('nick_ok')) {
						SetCookie('fbname', info.name, 1);
					}
					if (GetCookie('fblogged') != dataLine) {
						SetCookie('fblogged', dataLine, 365);
//							window.location.reload();
					}
					else {
						SetCookie('fblogged', dataLine, 365);
						}
					}
                }
            }
        
            function logout(response){
				SetCookie('fblogged', 'vide', -1);
				window.location.reload();
            }


        </script>

        <span id=\"fb-auth\" style='border:1px solid black;background-color:#5872A7;color:#ffffff;margin-top:6px;' class='button'>".$LANG['FACEBOOK_LOGIN']."</span>

			
			
			</div>";
		$currentshard = $_REQUEST['shard'];
		$facebook_widget .= "<div style='margin-left:20px;text-align:center;margin-top:4px;' id='facebook_like'>";
		$like_link = $_SERVER['REQUEST_URI'];
		if ($like_link == "/forum.html")
			$like_link = "/index.php?shard=forum&action=g_default";
		if ($currentshard == "blog" || ($currentshard == "forum" && $_REQUEST['action'] != "g_users"))
			$facebook_widget .= "<fb:like href=\"http://".$siteSettings['siteurl'].$like_link."\" layout=\"button_count\" show_faces=\"true\" width=\"90\" action=\"like\" colorscheme=\"light\"></fb:like>";
		$facebook_widget .= "</div></div>";
	
		$thisMenu->menuContentArray[] = $facebook_widget;
	}
	if (file_exists("paypal.php"))
		include("paypal.php");

?>