/*
	Copyright 2004 Brian Culler
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

var jsCheckTimeout = 0;
var jsCheckTimer = 0;
var jsCurrentUser;
var jsCurrentUserID;
var titleHolder = document.title;
var titleSite = document.title;
var previewPaneDelay;
var lastClickedQQ;
var b;
var c;
var e;
var f;
var g;
var h;
var l;
var m;
var n;
var p;
var t;
var w;
var textareaid;
var ppt;
var textareaCache = "";
var textareaCache0 = "";
var textareaCacheQuick = "";
var layerCache = "";
var windowIsActive = true;
var storedhash;
var br = "<div style='padding-top: 244px;'></div>";
var wait2 =  "</b> &nbsp; <img src='images/core/indicator.gif' style='vertical-align: top;'>";
var etat_bt_b = 0;
var etat_bt_u = 0;
var etat_bt_i = 0;
var etat_bt_s = 0;
var etat_bt_daily = 0;
var etat_bt_youtube = 0;
var etat_bt_img = 0;
var etat_bt_url = 0;
var etat_bt_spoiler = 0;
var b6_postedit = [];
var postoscroll = "";
var posTtoscroll = "";
var state_update = 0;
var scrolltoid_count = 0;
var cursorX = 0;
var cursorY = 0;
var stoppreview = 0;
var dataLineprev = "";

if (window.innerWidth) {
	var expire = new Date((new Date()).getTime() + 31536000000);
	expire = "; expires=" + expire.toGMTString();
	document.cookie = 'screen_width' + "=" + escape(window.innerWidth) + expire;
}

function allowNotification() {
	if("webkitNotifications" in window) {
		webkitNotifications.requestPermission(p297_showNotification);
	}
}

if (!localStorage.isInitialized) {
	localStorage.isActivated = true;
	localStorage.isInitialized = true;
}

var chrome_notif = false;
if (typeof(webkitNotifications) != "undefined" ) {
	if (JSON.parse(localStorage.isActivated)) {
		chrome_notif = true;
	}
}

function SetCookie(cookieName,cookieValue,nDays) {
	var today = new Date();
	var expire = new Date();
	if (nDays == null || nDays == 0) {
		nDays=1;
	}
	today = today.getTime();
	expire.setTime(today + (3600000*24*nDays));
	document.cookie = cookieName + "=" + cookieValue + ";expires=" + expire.toGMTString();
}

function GetCookie(cookiename) {
	var cookiestring=""+document.cookie;
	var index1=cookiestring.indexOf(cookiename);
	if (index1 == -1 || cookiename == "") {
		return ""; 
	}
	var index2=cookiestring.indexOf(';',index1);
	if (index2==-1) {
		index2=cookiestring.length;
	}
	return cookiestring.substring(index1+cookiename.length+1,index2);
}
	
function savelang() {
	var lang = document.getElementById('change_lang').value;
	SetCookie('lang', lang, 365);
	window.location.reload();
}

function lostFocus() {
	windowIsActive = false;
	if (document.getElementById('timeAgo_fav') && chrome_notif) {
		var newtimeago = new Date().getTime();
		newtimeago = Math.round(newtimeago / 1000);
		document.getElementById('timeAgo_fav').className = newtimeago;
		document.getElementById('timeAgo_pt').className = newtimeago;
		clearInterval(n);
		n = setInterval(function() {check_favorites();}, b6_tu);
	}
}
    
function gainedFocus() {
	windowIsActive = true;
	state_update = 0;
	if (document.getElementById('timeAgo_fav') && chrome_notif) {
		document.getElementById('timeAgo_fav').innerHTML = "";
		document.getElementById('timeAgo_pt').innerHTML = "";
		clearInterval(n);
	}
}

function checkhash() {
	clearInterval(h);
	if (window.location.hash != storedhash) {
		storedhash = window.location.hash;
		var harray = storedhash.split('/');
		if (harray[0] == "#threadlist" || !harray[0]) {
			clearInterval(h);
			clearTimeout(ppt);
			clearTimeout(p);
			if (currentuserid && chrome_notif) {
				var notif = window.webkitNotifications.checkPermission();
				if (notif == 1 && document.cookie.indexOf('notifications') == -1) {
					displayDiv('allow_notifications');
				}
			}
			document.getElementById('thread').innerHTML = "";
			document.getElementById('thread').style.display = "none";
			document.getElementById('user_profile').innerHTML = "";
			document.getElementById('user_profile').style.display = "none";
			if (document.getElementById('parentC').innerHTML == "") {
				if (storedhash == "") {
				window.location.hash="#threadlist";
				storedhash = window.location.hash;
				}
				pleasewait();
				if (!harray[1]) {
					harray[1] = document.getElementById('filter').innerHTML;
				}
				else if (harray[1] == "teams" && harray[2]) {
					document.getElementById('selectteam').value = unescape(harray[2]);
				}
					displayFilter(harray[1]);
				}
			else {
				if (harray[1] != document.getElementById('filter').innerHTML) {
					if (!harray[1]) {
						harray[1] = document.getElementById('filter').innerHTML;
						if (harray[1] == "teams") {
							if (unescape(harray[2]) != document.getElementById('selectteam').value) {
								if (harray[2]) {
									document.getElementById('selectteam').value = unescape(harray[2]);
								}
								else {
									document.getElementById('selectteam').value = "";
								}
							}
						}		
					}
					displayFilter(harray[1]);
				}
				else if (harray[1] == "teams") {
					if (unescape(harray[2]) != document.getElementById('selectteam').value) {
						if (harray[2]) {
							document.getElementById('selectteam').value = unescape(harray[2]);
						}
						else {
							document.getElementById('selectteam').value = "";
						}
					displayFilter(harray[1]);
			}
					else {
						document.getElementById('threadlist').style.display = "block";
						main_is_threadlist();
						clearInterval(t);
						t = setInterval(function() {threadUpdate();}, b6_tu);
					}
				}
				else {
					document.getElementById('threadlist').style.display = "block";
					main_is_threadlist();
					clearInterval(t);
					t = setInterval(function() {threadUpdate();}, b6_tu);
				}
			}
		}
		else if (harray[0] == "#thread") {
			document.getElementById('threadlist').style.display = "none";
			document.getElementById('user_profile').innerHTML = "";
			document.getElementById('user_profile').style.display = "none";
			emptymainThread(harray[1],0,harray[2],'',harray[3]);
		}
		else if (harray[0] == "#user") {
			document.getElementById('thread').innerHTML = "";
			document.getElementById('thread').style.display = "none";
			document.getElementById('threadlist').style.display = "none";
			userprofile(0,0,harray[1]);
		}
		else if (harray[0] == "#blog") {
			document.getElementById('thread').innerHTML = "";
			document.getElementById('thread').style.display = "none";
			document.getElementById('user_profile').innerHTML = "";
			document.getElementById('user_profile').style.display = "none";
			if (!harray[1]) {
				emptymainBlog(0,0,0,'g_default');
			}
			else {
				emptymainBlog(harray[2],harray[1],harray[3],harray[4],harray[5]);
	}
		}
		else if (harray[0] == "#post") {
			document.getElementById('threadlist').style.display = "none";
			document.getElementById('user_profile').innerHTML = "";
			document.getElementById('user_profile').style.display = "none";
			gotopost(harray[1]);
	}
	}
	h = setInterval(function() {checkhash();}, 1000);
}

function checkhash_anonymous() {
	clearInterval(h);
	if (window.location.hash != storedhash) {
		storedhash = window.location.hash;
		var harray = storedhash.split('/');
		if (harray[0] == "#thread") {
			document.getElementById('threadlist').style.display = "none";
			document.getElementById('user_profile').innerHTML = "";
			document.getElementById('user_profile').style.display = "none";
			emptymainThread(harray[1],0,harray[2],'',harray[3]);
		}
		else if (harray[0] == "#blog") {
			document.getElementById('thread').innerHTML = "";
			document.getElementById('thread').style.display = "none";
			document.getElementById('user_profile').innerHTML = "";
			document.getElementById('user_profile').style.display = "none";
			if (!harray[1]) {
				emptymainBlog(0,0,0,'g_default');
			}
			else {
				emptymainBlog(harray[2],harray[1],harray[3],harray[4],harray[5]);
			}
		}
		else if (harray[0] == "#post") {
			document.getElementById('threadlist').style.display = "none";
			document.getElementById('user_profile').innerHTML = "";
			document.getElementById('user_profile').style.display = "none";
			gotopost(harray[1]);
		}
	}
	h = setInterval(function() {checkhash();}, 1000);
}

function ajaxlink(dataline) {
	clearInterval(h);
	var harray = dataline.split('/');
	if (harray[0] == "#post") {
		document.getElementById('threadlist').style.display = "none";
		document.getElementById('user_profile').innerHTML = "";
		document.getElementById('user_profile').style.display = "none";
		gotopost(harray[1],harray[2]);
	}
	else {
		window.location.hash = dataline;
		checkhash();
	}
}

function main_is_threadlist() {
	if (document.getElementById('facebook_like')) {
		document.getElementById('facebook_like').innerHTML = document.getElementById('fb_like_cache').innerHTML;
		document.getElementById('fb_like_cache').innerHTML = "";
	}

	if (document.getElementById('newThreadFormPlaceholder')) {
		if (document.getElementById('newThreadFormPlaceholder').style.display == "block") {
			document.getElementById('newThreadFormPlaceholder').style.display = "none";
		}
	}

	document.getElementById('forum_tab').innerHTML = b6_forum_tab;

	document.title = titleSite;
	titleHolder = document.title;
	if (posTtoscroll) {
		window.scrollTo(0,posTtoscroll);
		posTtoscroll = "";
	}

}

function blinkTitle(state) {
	if (windowIsActive != true) {
		if (state == 1) {
			document.title = "[" + b6_new + "] - " + titleHolder;
			state_update = 1;
			state = 2;			
			favicon.change("engine/grafts/" + b6_graft + "/images/faviconnew.png");
		}
		else {
			document.title = "" + titleHolder;
			state = 1;
		}
		setTimeout(function() {blinkTitle(state);}, 1600);
	}
	else {
		state_update = 0;
		document.title = titleHolder;
		favicon.change("engine/grafts/" + b6_graft + "/images/favicon.png");
	}
}

var clientTimezone = new Date();
if (clientTimezone) {
	SetCookie("mf_timezone",clientTimezone.getTimezoneOffset()/60,300);
}

function quickQuote(poster,postID,pCount) {

	var qqTag = 'qq' + postID + '.' + pCount;
	var textarea = "";
	if (document.getElementById('postArea')) {
		textarea = document.getElementById('postArea');
	}
	else {
		textarea = document.getElementById('postAreaQuick');
	}

	if (lastClickedQQ == qqTag) {
		scrolltoID('replyForm');
		textarea.focus();
	}
	else {
		if (document.getElementById) {
			var s = "";
			if (pCount != "9999")
				s = document.getElementById(qqTag).innerHTML;
			else {
				var i = 0;
				while(document.getElementById('qq' + postID + '.' + i)) {
					s += document.getElementById('qq' + postID + '.' + i).innerHTML;
					i = i + 1;
				}
			}
			s = s.replace(/\n/g, "");
			s = s.replace(/\r/g, "");
			s = s.replace(/\t/g, "");
			s = s.replace("<div class=\"clearfix\" style=\"height: 4px;\"></div>", "");
			if (textarea.value != "") {
				textarea.value += "\n";
			}
			textarea.value += "[qq." + postID + "." + pCount +".][i]" + poster + b6_said + "[/i][br]" + s + "[/qq]\n\n";
			if (document.getElementById('postArea')) {
				textareaCache = textarea.value;
		}
			else {
				textareaCacheQuick = textarea.value;
			}
		}
	}
	
	var selEnd = textarea.value.length;
	textarea.setSelectionRange(selEnd, selEnd);

	if (document.getElementById('postArea')) {
		lastClickedQQ = qqTag;
		setTimeout(function() {reset_lastClickedQQ();}, 2000);

	}
}

function reset_lastClickedQQ() {
	lastClickedQQ = "";
}

function cursorToPosition(curPos,ID) {
	var postID = "";
	if (ID) {
		postID = ID;
	}
	var elemrw = document.getElementById('postArea' + postID);
	if (elemrw.selectionStart) {
		elemrw.focus();
		elemrw.setSelectionRange(curPos, curPos);
		elemrw.scrollTop = document.getElementById('scroll_position' + postID).innerHTML;
	}
	else {
		elemrw.focus();
	}
}

function addbbcode(bbcode1,bbcode2,selStart,selEnd,ID) {
	var postID = "";
	if (ID) {
		postID = ID;
	}
	var textArea = document.getElementById("postArea" + postID).value;
	var selectedText = textArea.substring(selStart,selEnd);
			var textStart = "";
			var textEnd = "";

	if (selStart == textArea.length) {
		document.getElementById("postArea" + postID).value += bbcode1;
	}
	else {
		if (selStart == 0) {
			textStart = "";
		}
		else {
			textStart = textArea.substring(0,selStart);
		}
		if (selEnd == textArea.length) {
			textEnd = "";
		}
		else {
			textEnd = textArea.substring(selEnd,textArea.length);
		}
		document.getElementById("postArea" + postID).value = textStart+bbcode1+selectedText+bbcode2+textEnd;
	}
	cursorToPosition(selEnd + bbcode1.length + bbcode2.length,postID);
}

function pushBt(button,ID,option) {
	var postID = "";
	if (ID) {
		postID = ID;
	}
	var selStart = document.getElementById("postArea" + postID).selectionStart;
	var selEnd = document.getElementById("postArea" + postID).selectionEnd;
	var num = "";
	var val = 0;
	var text = "";

	switch(button) {
		case 'b':
			pushBt_type1(button,postID,selStart,selEnd);
			break;
		case 'u':
			pushBt_type1(button,postID,selStart,selEnd);
			break;
		case 'i':
			pushBt_type1(button,postID,selStart,selEnd);
			break;
		case 's':
			pushBt_type1(button,postID,selStart,selEnd);
			break;
		case 'daily':
			pushBt_type3(button,postID,selStart,selEnd);
			break;
		case 'youtube':
			pushBt_type3(button,postID,selStart,selEnd);
		break;
		case 'metacafe':
			pushBt_type3(button,postID,selStart,selEnd);
			break;
		case 'deezer':
			pushBt_type3(button,postID,selStart,selEnd);
		break;
		case 'media':
			pushBt_type3(button,postID,selStart,selEnd);
			break;
		case 'video':
			pushBt_type3(button,postID,selStart,selEnd);
		break;
		case 'object':
				if (selStart == selEnd)	{
				num = document.getElementById('url_' + button + postID);
				if (num.value == "")	{								
					displayDiv('info_' + button + postID);
					num.focus();
			}
			else {
					val = num.value;
					var object = document.getElementById('formated_object' + postID);
					addbbcode(unescape(object.innerHTML),'',selStart,selEnd,postID);
					object.innerHTML = "";
					closeDiv('info_' + button + postID,'url_' + button + postID);
				}
			}
			else if (document.getElementById('bt_' + button + postID).className == "bt_style")	{
				addbbcode('[' + button + ']','[/' + button + ']',selStart,selEnd,postID);
			}
			else {
				addbbcode('[/' + button + ']','',selStart,selEnd,postID);
				document.getElementById('bt_' + button + postID).className = "bt_style";
			}
			break;
		case 'img':
				if (selStart == selEnd)	{
				num = document.getElementById('url_' + button + postID);
				if (num.value == "")	{								
					displayDiv('info_' + button + postID);
					num.focus();
			}
			else {
					val = num.value;
					addbbcode('[' + button + ']' + val + '[/' + button + ']','',selStart,selEnd,postID);
					closeDiv('info_' + button + postID,'url_' + button + postID);
				}
			}
			else if (document.getElementById('bt_' + button + postID).className == "bt_style")	{
				addbbcode('[' + button + ']','[/' + button + ']',selStart,selEnd,postID);
			}
			else {
				addbbcode('[/' + button + ']','',selStart,selEnd,postID);
				document.getElementById('bt_' + button + postID).className = "bt_style";
			}
			break;
		case 'url':
				if (selStart == selEnd)	{
				num = document.getElementById('url_url' + postID);
				if (num.value == "")	{								
					displayDiv('info_url' + postID);
					num.focus();
			}
			else {
					val = num.value;
					text = document.getElementById('text_url' + postID).value;
					if (text) {
						addbbcode('[url=' + val + ']' + text + '[/url]','',selStart,selEnd,postID);
					}
					else {
						addbbcode('[url]' + val + '[/url]','',selStart,selEnd,postID);
					}
					closeDiv('info_url' + postID,'url_url' + postID,'text_url' + postID);
				}
			}
			else if (document.getElementById('bt_url' + postID).className == "bt_style") {
				addbbcode('[url]','[/url]',selStart,selEnd,postID);
			}
			else {
				addbbcode('[/url]','',selStart,selEnd,postID);
				document.getElementById('bt_url' + postID).className = "bt_style";
			}
			break;
		case 'iurl':
			if (selStart == selEnd) {
				num = document.getElementById('url_iurl' + postID);
				if (num.value == "") {								
					displayDiv('info_iurl' + postID);
					num.focus();
				}
				else {
					val = num.value;
					text = document.getElementById('text_iurl' + postID).value;
					if (text) {
						addbbcode('[iurl=' + val + ']' + text + '[/iurl]','',selStart,selEnd,postID);
					}
					else {
						addbbcode('[iurl]' + val + '[/iurl]','',selStart,selEnd,postID);
					}
					closeDiv('info_iurl' + postID,'url_iurl' + postID,'text_iurl' + postID);
				}
			}
			else if (document.getElementById('bt_iurl' + postID).className == "bt_style") {
				addbbcode('[iurl]','[/iurl]',selStart,selEnd,postID);
			}
			else {
				addbbcode('[/iurl]','',selStart,selEnd,postID);
				document.getElementById('bt_iurl' + postID).className = "bt_style";
			}
		break;
		case 'spoiler':
			if (document.getElementById('bt_spoiler' + postID).className == "bt_style")	{
				if (selStart == selEnd)	{
					addbbcode('[spoiler=spoiler]','',selStart,selEnd,postID);
					document.getElementById('bt_spoiler' + postID).className = "bt_style_off";
				}
			else {
					addbbcode('[spoiler=spoiler]','[/spoiler]',selStart,selEnd,postID);
				}
			}
			else {
				addbbcode('[/spoiler]','',selStart,selEnd,postID);
				document.getElementById('bt_spoiler' + postID).className = "bt_style";
			}
			break;
		case 'quote':
			pushBt_type1(button,postID,selStart,selEnd);
		break;
		case 'code':
			pushBt_type1(button,postID,selStart,selEnd);
		break;
		case 'name':
			if (selStart == selEnd) {
				num = document.getElementById('url_name' + postID);
				if (num.value == "") {								
					displayDiv('info_name' + postID);
					num.focus();
				}
				else {
					val = num.value;
					addbbcode('[name=' + val + ']','',selStart,selEnd,postID);
					closeDiv('info_name' + postID,'url_name' + postID);
				}
			}
		break;
		case 'album':
			if (selStart == selEnd) {
				num = document.getElementById('num_album' + postID);
				if (num.value == "")	{
					dataline = postID + "::ID@ID::" + document.getElementById('numthreadID').innerHTML;
					x_ajax_displayAlbums(dataline, displayAlbums);
				}
				else {
					val = num.value;
					addbbcode('[album]' + val + '[/album]','',selStart,selEnd,postID);
					closeDiv('info_album' + postID,'num_album' + postID);
				}
			}
			else {
				addbbcode('[album]','[/album]',selStart,selEnd,postID);
			}
		break;
		case 'pict':
			if (selStart == selEnd) {
				num = document.getElementById('num_pict' + postID);
				if (num.value == "") {								
					x_ajax_displayPicts(postID, displayPicts);
				}
				else {
					val = num.value;
					addbbcode('[pict]' + val + '[/pict]','',selStart,selEnd,postID);
					closeDiv('info_pict' + postID,'num_pict' + postID);
				}
			}
			else {
				addbbcode('[pict]','[/pict]',selStart,selEnd,postID);
			}
		break;
		case 'vote':
			if (selStart == selEnd) {
				var randomnumber = Math.floor(Math.random()*11111111);
				addbbcode('[vote]' + randomnumber + '[/vote]','',selStart,selEnd,postID);
			}
			else {
				addbbcode('[vote]','[/vote]',selStart,selEnd,postID);
			}
		break;
		case 'br':
			addbbcode('[br]','',selStart,selEnd,postID);
		break;
		case 'hr':
			addbbcode('[hr]','',selStart,selEnd,postID);
		break;
		case 'center':
			pushBt_type1(button,postID,selStart,selEnd);
		break;
		case 'justify':
			pushBt_type1(button,postID,selStart,selEnd);
		break;
		case 'blocl':
			pushBt_type1(button,postID,selStart,selEnd);
		break;
		case 'blocr':
			pushBt_type1(button,postID,selStart,selEnd);
		break;
		case 'ul':
			pushBt_type1(button,postID,selStart,selEnd);
		break;
		case 'ol':
			pushBt_type1(button,postID,selStart,selEnd);
		break;
		case 'li':
			pushBt_type1(button,postID,selStart,selEnd);
		break;
		case 'size':
			document.getElementById('info_' + button + postID).style.display = "none";
			num = document.getElementById('val_' + button + postID);
			if (option) {
				if (option > 20) {
					option = 20;
				}
				num.value = option;
				pushBt_type2(button,postID,selStart,selEnd,option);
			}
			else if (num.value != "") {
				if (num.value > 20) {
					num.value = 20;
				}
				pushBt_type2(button,postID,selStart,selEnd,num.value);
			}
			else {
				displayDiv('info_' + button + postID);
				num.focus();
			}
		break;
		case 'color':
			document.getElementById('info_' + button + postID).style.display = "none";
			num = document.getElementById('val_' + button + postID);
			if (option) {
				num.value = option;
				pushBt_type2(button,postID,selStart,selEnd,option);
			}
			else if (num.value != "") {
				pushBt_type2(button,postID,selStart,selEnd,num.value);
			}
			else {
				displayDiv('info_' + button + postID);
				num.focus();
			}
		break;
	}
}

function pushBt_type1(button,postID,selStart,selEnd) {
	if (document.getElementById('bt_' + button + postID).className == "bt_style")	{
		if (selStart == selEnd) {
			addbbcode('[' + button + ']','',selStart,selEnd,postID);
			document.getElementById('bt_' + button + postID).className = "bt_style_off";
		}
		else {
			addbbcode('[' + button + ']','[/' + button + ']',selStart,selEnd,postID);
		}
	}
	else {
		addbbcode('[/' + button + ']','',selStart,selEnd,postID);
		document.getElementById('bt_' + button + postID).className = "bt_style";
	}
}

function pushBt_type2(button,postID,selStart,selEnd,option) {
	if (document.getElementById('bt_' + button + postID).className == "bt_style")	{
		if (selStart == selEnd) {
			addbbcode('[' + button + '=' + option + ']','',selStart,selEnd,postID);
			document.getElementById('bt_' + button + postID).className = "bt_style_off";
			document.getElementById('bt_' + button + postID).innerHTML = "[" + button + "=" + option + "]";
		}
		else {
			addbbcode('[' + button + '=' + option + ']','[/' + button + ']',selStart,selEnd,postID);
			document.getElementById('val_' + button + postID).value = "";
		}
	}
	else {
		addbbcode('[/' + button + ']','',selStart,selEnd,postID);
		document.getElementById('bt_' + button + postID).className = "bt_style";
		document.getElementById('val_' + button + postID).value = "";
		document.getElementById('bt_' + button + postID).innerHTML = "[" + button + "=]";
	}
}

function pushBt_type3(button,postID,selStart,selEnd) {
	if (selStart == selEnd)	{
		var num = document.getElementById('url_' + button + postID);
		if (num.value == "")	{								
			displayDiv('info_' + button + postID);
			num.focus();
		}
		else {
			var val = num.value;
			addbbcode('[' + button + ']' + val + '[/' + button + ']','',selStart,selEnd,postID);
			closeDiv('info_' + button + postID,'url_' + button + postID);
		}
	}
	else if (document.getElementById('bt_' + button + postID).className == "bt_style")	{
		addbbcode('[' + button + ']','[/' + button + ']',selStart,selEnd,postID);
	}
	else {
		addbbcode('[/' + button + ']','',selStart,selEnd,postID);
		document.getElementById('bt_' + button + postID).className = "bt_style";
	}
}

function format_object(postID) {
	var obj = document.getElementById('url_object' + postID);
	if (obj.value) {
		var dataline = postID + '::@@ob@@::' + escape(obj.value);
		x_ajax_format_object(dataline, send_object);
	}
	else {
		closeDiv('info_object' + postID,'url_object' + postID);
	}
}

function send_object(dataline) {
	var dlarray = dataline.split('::@@::');
	document.getElementById('formated_object' + dlarray[0]).innerHTML = escape(dlarray[1]);
	pushBt('object',dlarray[0]);
}

function europlus(data) {
	data = data.replace(/€/g, "::@euro@::");
	data = data.replace(/\+/g, "::@plus@::");
	
	return data;
}

function displayDiv(divName,content,refresh) {
	if (document.getElementById) {
		var div = document.getElementById(divName);
		if (content) {
			div.innerHTML = content;
		}
		div.style.opacity = "1";
		div.style.filter = "alpha(opacity=100)";
		div.style.display = "block";
		var xscroll = 0;
		var yscroll = 0;
		var divPaneW = div.offsetWidth;
		var divPaneH = div.offsetHeight;
		if (window.innerWidth) {
			xscroll = (window.innerWidth - divPaneW) / 2;
			yscroll = (window.innerHeight - divPaneH) / 2;
		}
		else if (document.documentElement.clientWidth) {
			xscroll = (document.documentElement.clientWidth - divPaneW) / 2;
			yscroll = (document.documentElement.clientHeight - divPaneH) / 2;
		}
		div.style.left = xscroll + "px";
		div.style.top = yscroll + "px";
		div.style.visibility = "visible";
		
		if (refresh)
			setTimeout(function() {displayDivRecalc(divName);}, 500);
		if (refresh)
			setTimeout(function() {displayDivRecalc(divName);}, 2000);
	}
}

function displayDivRecalc(divName) {

	var xscroll = 0;
	var yscroll = 0;
	var div = document.getElementById(divName);
	var divPaneW = div.offsetWidth;
	var divPaneH = div.offsetHeight;
	if (window.innerWidth) {
		xscroll = (window.innerWidth - divPaneW) / 2;
		yscroll = (window.innerHeight - divPaneH) / 2;
	}
	else if (document.documentElement.clientWidth) {
		xscroll = (document.documentElement.clientWidth - divPaneW) / 2;
		yscroll = (document.documentElement.clientHeight - divPaneH) / 2;
	}
	div.style.left = xscroll + "px";
	div.style.top = yscroll + "px";
}

function closeDiv(divName,inputName,inputName2) {
	document.getElementById(divName).style.display = "none";
	if (inputName) {
		document.getElementById(inputName).value = "";
	}
	if (inputName2) {
		document.getElementById(inputName2).value = "";
	}
}

function addSmily(smily,ID) {
	if (document.getElementById) {
		var postID = "";
		if (ID) {
			postID = ID;
		}
		var selStart = document.getElementById("postArea" + postID).selectionStart;
		var selEnd = document.getElementById("postArea" + postID).selectionEnd;
		var textArea = document.getElementById("postArea" + postID).value;
		smily = smily + " ";
		addbbcode(smily,'',selStart,selEnd,postID);
	}
}

function qqHover(elementID) {
	if (document.getElementById) {
		document.getElementById(elementID).className = 'qqContainerHover';
	}
}

function qqHoverOff(elementID) {
	if (document.getElementById) {
		document.getElementById(elementID).className = 'qqContainer';
	}
}

function debug(message) {
	if (document.getElementById) {
		document.getElementById('debug').innerHTML = debugPane.innerHTML + "<br />" + message;
	}
}

function runOnce(user) {
	clearInterval(t);
	clearInterval(m);
	clearInterval(h);

	if (document.getElementById('speaker')) {
		var speaker = document.getElementById('speaker');
		if (GetCookie('mf_speaker') != "") {
			speaker.className = GetCookie('mf_speaker');
	}
	}

	storedhash = window.location.hash;
	var harray = storedhash.split('/');
	if (!harray[0]) {
		window.location.hash="#threadlist";
		storedhash = window.location.hash;
		harray[0] = "#threadlist";
	}
	else if (harray[0] == "#thread") {
		emptymainThread(harray[1],0,harray[2],'',harray[3]);
	}
	else if (harray[0] == "#user") {
		userprofile(0,0,harray[1]);
	}
	
	if (harray[0] == "#threadlist") {
	var filter = document.getElementById('filter').innerHTML;
		if (!harray[1]) {
			harray[1] = filter;
		}
		else if (harray[1] == "teams" && harray[2]) {
			document.getElementById('selectteam').value = unescape(harray[2]);
		}
		if (filter != harray[1]) {
			displayFilter(harray[1]);
		}
		else {
			if (!harray[2]) {
		window.location.hash="#threadlist/" + filter;
			storedhash = window.location.hash;
		}
			else {
				window.location.hash="#threadlist/" + filter + "/" + harray[2];
				displayFilter(harray[1]);
		storedhash = window.location.hash;
	}	
		}

		t = setInterval(function() {threadUpdate();}, b6_tu);
		m = setInterval(function() {updateLastPostMinutes();}, 60000);
		if (chrome_notif) {
			n = setInterval(function() {check_favorites();}, b6_tu);
		}
	refreshTags();
}
}

function threadUpdate() {	
	clearInterval(t);
	var ph = document.getElementById('searchForm');
	if (ph.style.display == "none" && state_update == 0) {
		var page = document.getElementById('numpage_cache').innerHTML;
		if (page == "1") {
			var dataLine = "";
			var timeStamp = document.getElementById('timestamp');
			var filter = document.getElementById('filter').innerHTML;
			var channels = document.getElementById('chan_cache').innerHTML;
			var tags = document.getElementById('tags_cache').innerHTML;
			var team = document.getElementById('listthreadteam').innerHTML;

			dataLine = timeStamp.className + "::@tu@::::@tu@::" + 0 + "::@tu@::::@tu@::" + filter + "::@tu@::" + page + "::@tu@::" + channels + "::@tu@::" + tags + "::@tu@::" + team;
			t = setInterval(function() {threadUpdate();}, b6_tu);
			x_ajax_threadUpdate(dataLine, resetThreads);
		}
	}
	else if (state_update != 0) {
		setTimeout(function() {threadUpdate();}, 1000);
	}
}

function runThreadWatcherOnce(threadID) {
	p = setTimeout(function() {postUpdate(threadID);}, b6_pu);
	initAllowNextPostTimer();
	if (document.getElementById('speaker')) {
		var speaker = document.getElementById('speaker');
		if (GetCookie('mf_speaker') != "") {
			speaker.className = GetCookie('mf_speaker');
	}	
	}	
}

function postUpdate(threadID) {	
	if (document.getElementById('threadid' + threadID)) {
		var timeStamp = document.getElementById('lastPostTimeStamp');
		var dataLine = "";
		dataLine = timeStamp.className + "::" + threadID;
		x_ajax_postUpdate(dataLine, appendPosts);

		var timeStamp_first = document.getElementById('firstPostTimeStamp');
		var lastrefresh = document.getElementById('timelastrefresh');
		dataLine = timeStamp.className + "::@@pr@@::" + threadID + "::@@pr@@::" + timeStamp_first.className + "::@@pr@@::" + lastrefresh.className;

		p = setTimeout(function() {postUpdate(threadID);}, b6_pu);
		x_ajax_postRefresh(dataLine, refreshPosts);
		x_ajax_modRefresh(dataLine, refreshModsPosts);
	}
}

function appendPosts(dataLine) {	
	if (dataLine != "false") {
		if (windowIsActive != true) {
			if (document.getElementById('speaker')) {
				if (document.getElementById('speaker').className == "speakerOn") {
				so.write("flashcontent"); 
				}
			}
			blinkTitle(1, 1);
		}		

		var dataLineArray = dataLine.split('__timeDlm__');
		if (document.getElementById('threadid' + dataLineArray[2])) {
			var timeStamp = document.getElementById('lastPostTimeStamp');
			var postCounter = document.getElementById('newPostPlaceHolder');
			timeStamp.className=dataLineArray[0];
			var postArray = dataLineArray[1].split('__postDlm__');
			var postToAppend = "";
			var postCount = "";

			for (i=0;i<postArray.length;i++) {
				if (postArray[i] != "") {	
					var postContentArray = postArray[i].split('__postIDDlm__');
					postToAppend = document.createElement("div");
					postToAppend.id = 'postCounter' + postCounter.className;

					if (!(document.getElementById('postContent' + postContentArray[1]))) {				
						postToAppend.innerHTML = postContentArray[0];
						postToAppend.style.opacity = ".00";
						postToAppend.style.filter = "alpha(opacity=0)";
						postCount = postCounter.className * 1;
						postCounter.className = postCount + 1;
						postCounter.appendChild(postToAppend);
						fadeIn(postToAppend.id,0);
					}
				}
			}
		}
	}
	document.getElementById('newPostIndicator').style.display='none';
}

function refreshPosts(dataLine) {	
	if (dataLine != "") {
		var dataLineArray = dataLine.split('::@p@::');
		document.getElementById('timelastrefresh').className = dataLineArray[0];
		var i = 1;
		while (dataLineArray[i]) {
			var postArray = dataLineArray[i].split('::@@::');
			if (document.getElementById('postid' + postArray[0])) {
				var postid = document.getElementById('postid' + postArray[0]);
				var sig = document.getElementById('postsig' + postArray[0]).innerHTML;
				var whorated = document.getElementById('postwhorated' + postArray[0]).innerHTML;
				document.getElementById('postContent' + postArray[0]).innerHTML = postArray[1];
				document.getElementById('postsig' + postArray[0]).innerHTML = sig;
				document.getElementById('postwhorated' + postArray[0]).innerHTML = whorated;
				if (postArray[2] > 2) {
					postid.style.opacity = "0";
					postid.style.filter = "alpha(opacity=0)";
				}
				else {
					postid.style.opacity = "1";
					postid.style.filter = "alpha(opacity=100)";
				}
			}
			i = i + 1;
		}
	}
}

function refreshModsPosts(dataLine) {	
	if (dataLine != "") {
		var dataLineArray = dataLine.split('::@p@::');
		document.getElementById('timelastrefresh').className = dataLineArray[0];
		var i = 1;
		while (dataLineArray[i]) {
			var postArray = dataLineArray[i].split('::@@::');
			if (document.getElementById('postid' + postArray[0])) {
				document.getElementById('ratingDisplaypost' + postArray[0]).innerHTML = postArray[2];
				document.getElementById('ratingDisplaypost' + postArray[0]).className = postArray[3];
				document.getElementById('postwhorated' + postArray[0]).innerHTML = postArray[4];
				if (postArray[5]) {
					toggleLayer('post' + postArray[0]);
					toggleLayer('hiddenpost' + postArray[0]);
					toggleLayer('hidden' + postArray[0]);
					toggleLayer('normal' + postArray[0]);
				}
			}
			i = i + 1;
		}
	}
}

function fadeIn(element,elementOpac) {
	var elem = document.getElementById(element);

	if (elementOpac < 1) {
		elementOpac = elementOpac * 1;
		elementOpac = (elementOpac + 0.1);
		elem.style.filter = "alpha(opacity=" + (elementOpac*100) + ")";
		elem.style.opacity = elementOpac;
		setTimeout(function() {fadeIn(element, elementOpac);}, 10);
	}
}

function fadeOut(element,elementOpac) {
	var elem = document.getElementById(element);
	if (!elementOpac) {
		elementOpac = 1;
	}
	if (elementOpac > 0) {
		elementOpac = elementOpac * 1;
		elementOpac = (elementOpac - 0.2);
		elem.style.filter = "alpha(opacity=" + (elementOpac*100) + ")";
		elem.style.opacity = elementOpac;
		setTimeout(function() {fadeOut(element, elementOpac);}, 20);
	}
	else {
		elem.innerHTML = "";
		elem.style.display = "none";
	}
}

function submitPost(channelTag, threadID, islive) {
	var textArea = document.getElementById('postArea').value;

	if (textArea != "") {		
		clearTimeout(ppt);
		lastClickedQQ = "";
		if (document.getElementById('previewPost')) {
			var line = document.getElementById('previewPost');
			line.innerHTML = "";
			line.style.display = "none";
			document.getElementById('previewPostT').innerHTML = "";
		}
		var newPostIndicator = document.getElementById('newPostIndicator');
		newPostIndicator.style.opacity = ".00";
		newPostIndicator.style.filter = "alpha(opacity=0)";
		newPostIndicator.style.display = "block";
		fadeIn('newPostIndicator',0);
		var postcontent = textArea;
		postcontent = europlus(postcontent);
		var dataLine = postcontent + '__lineDlm__' + channelTag + '__lineDlm__' + threadID + '__lineDlm__' + islive + '__lineDlm__';
		if (islive == "1") {
			x_ajax_submitPost(dataLine, postUpdateRunOnce);
			var replyForm = document.replyForm;
			replyForm.replySubmit.disabled=true;
		}
		else {
			x_ajax_submitPost(dataLine, emptymainThread2);
		}
	}
	return false;
}

function submitQuickPost(threadID) {
	ajaxload_on();
	var textArea = document.getElementById('postAreaQuick');

	if (textArea.value != '') {	
		var postcontent = textArea.value;
		postcontent = europlus(postcontent);
		var dataLine = postcontent + '__lineDlm__0__lineDlm__' + threadID + '__lineDlm__';
		textArea.value = "";
		textareaCacheQuick = "";
		if (document.getElementById('searchForm').style.display == "block")
			x_ajax_submitPost(dataLine, QuickPostSent);
		else
		x_ajax_submitPost(dataLine, threadUpdateRunOnce);
		return false;
	}	
	closelayer();
	ajaxload_off();
	
	return false;
}

function QuickPostSent() {

}

function threadUpdateRunOnce() {
	var page = document.getElementById('numpage_cache').innerHTML;
	var filter = document.getElementById('filter').innerHTML;
	var channels = document.getElementById('chan_cache').innerHTML;
	var tags = document.getElementById('tags_cache').innerHTML;
	var team = document.getElementById('listthreadteam').innerHTML;
	var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:" + page + ":@@:" + channels + ":@@:" + tags;

	x_ajax_resetThreadList(dataLine, displayResetThreads);

	closelayer();
}

function postUpdateRunOnce(threadID) {
	textareaCache = "";
	document.getElementById('postArea').value = "";
	var timeStamp = document.getElementById('lastPostTimeStamp');
	
	var dataLine = "";
	dataLine = timeStamp.className + "::" + threadID;
	x_ajax_postUpdate(dataLine, appendPosts);
	
	var textArea = document.getElementById('postArea');
	textArea.value='';
//	textArea.focus();
	startAllowNextPostTimer();
}

function startAllowNextPostTimer() {	
	var lastPost = new Date();
	lastPost = lastPost.getTime();
	SetCookie("mf_lastPostTime",lastPost,60);
	initAllowNextPostTimer();		
}

function initAllowNextPostTimer() {
	var lastPost = GetCookie("mf_lastPostTime");
	var replyForm = document.replyForm;
	if (replyForm.postArea) {
		replyForm.postArea.disabled=true;
		replyForm.replySubmit.disabled=true;	
		runAllowNextPostTimer(lastPost);
	}
}

function runAllowNextPostTimer(lastPost) {	
	var replyForm = document.replyForm;
	if (replyForm.postArea) {
		var difference = new Date();
		difference = difference.getTime();
		difference = difference - lastPost*1;
		difference = difference / 1000;
		var textArea = document.getElementById('postArea');

		var timeLeft = 2 - difference;

		if (timeLeft > 30) {
			SetCookie('mf_lastPostTime','', -1);
			textArea.value= b6_clock;
			setTimeout(function() {initAllowNextPostTimer();}, 30000);
		}
		else if (timeLeft > 0 && timeLeft <31) {
			textArea.value= b6_postin + Math.ceil(timeLeft) + b6_seconds;
			setTimeout(function() {runAllowNextPostTimer(lastPost);}, 1000);
		}
		else if (timeLeft <= 0) {
			textArea.value='';
			replyForm.postArea.disabled=false;
			replyForm.replySubmit.disabled=false;		
		}
	}
}

function resetThreads(dataLine) {
	if (document.getElementById) {
		var dataLineArray = dataLine.split('!@timeDlm@!');
		var timeStamp = document.getElementById('timestamp');
		timeStamp.className = dataLineArray[0];

		clearInterval(t);
		t = setInterval(function() {threadUpdate();}, b6_tu);

		if (dataLineArray[1] != "false" || document.getElementById('parentC').innerHTML == "") {
			if (windowIsActive != true) {
				if (document.getElementById('speaker')) {
					if (document.getElementById('speaker').className == "speakerOn") {
					so.write("flashcontent"); 
				}
				}
				blinkTitle(1, 1);
			}
			ajaxload_on();
			var page = document.getElementById('numpage_cache').innerHTML;
			var filter = document.getElementById('filter').innerHTML;
			var channels = document.getElementById('chan_cache').innerHTML;
			var tags = document.getElementById('tags_cache').innerHTML;
			var team = document.getElementById('listthreadteam').innerHTML;
			var dataline = ":@@:" + team + ":@@:" + filter + ":@@:" + page + ":@@:" + channels + ":@@:" + tags;
			x_ajax_resetThreadList(dataline, displayResetThreads);
		}
	}
}

function displayResetThreads(dataline) {	
	if (document.getElementById) {
		clearInterval(t);
		clearInterval(m);

		var dlarray = dataline.split('::arrdlm::');

		document.getElementById('timestamp').className = dlarray[8];
		document.getElementById('newPostsStr').innerHTML = dlarray[2];
		document.getElementById('listnewPostsStr').innerHTML = dlarray[3];
		verify_newPostsStr();
		if (document.getElementById('tags_presents')) {
			document.getElementById('tags_presents').innerHTML = dlarray[7];
		}

		var parent = document.getElementById('parentC');
		parent.innerHTML = dlarray[0];
		parent.style.opacity = "1.00";
		parent.style.filter = "alpha(opacity=100)";
		
		pleasewait_off();
		ajaxload_off();

		document.getElementById('pagesListStr').innerHTML = dlarray[1];
		document.getElementById('pagesListStrT').innerHTML = dlarray[1];
		
		var theadlist = document.getElementById('threadlist');

		if (theadlist.style.display == "none" && document.getElementById('thread').style.display == "none" && document.getElementById('user_profile').style.display == "none") {
			theadlist.style.display = "block";
		}
		if (theadlist.style.display == "block") {
			m = setInterval(function() {updateLastPostMinutes();}, 60000);
			t = setInterval(function() {threadUpdate();}, b6_tu);
		}
		
		if (document.getElementById('show_tags').innerHTML == "") {
			refreshTags();
		}
	}
}

function verify_newPostsStr() {
	var num = document.getElementById('numpostu').innerHTML;
	var newPostsStr_one = document.getElementById('newPostsStr_one');
	var newPostsStr_multi = document.getElementById('newPostsStr_multi');
	if (num == 1) {
		newPostsStr_one.style.display = "inline-block";
		newPostsStr_multi.style.display = "none";
	}
	else if (num > 1) {
		newPostsStr_one.style.display = "none";
		newPostsStr_multi.style.display = "inline-block";
	}
	else {
		newPostsStr_one.style.display = "none";
		newPostsStr_multi.style.display = "none";
	}
}

function submitRateComment(dataLine) {
	x_ajax_submitRateComment(dataLine, rateComment);
	
	return false;
}

function rateComment(dataline) {
		var postArray = dataline.split('::@@::');
		document.getElementById('postwhorated' + postArray[0]).innerHTML = postArray[1];
		already_rated[postArray[0]] = "";
		if (postArray[2]) {
			toggleLayer('post' + postArray[0]);
			toggleLayer('hiddenpost' + postArray[0]);
			toggleLayer('hidden' + postArray[0]);
			toggleLayer('normal' + postArray[0]);
		}

}

function updateComment(result) {
	commentLineArray = result.split('::');
	postTag = "";
	postTag = "comment" + commentLineArray[0];
	
	if (document.getElementById) {
		var x = document.getElementsByTagName('div');

		for (var i=0;i<x.length;i++) {
			if (x[i].className == postTag) {	
				x[i].style.display='block';
				x[i].innerHTML=commentLineArray[1];
			}
		}
	}
}

function callAjaxShowEditWindow(rowID) {
	if (!document.getElementById('postArea' + rowID)) {
		ajaxload_on();
		postTag = "";
		postTag = "postedit" + rowID;
		ratingTag = "postRating"+ rowID;
		if (document.getElementById) {
			document.getElementById(postTag).style.display="block";
			document.getElementById(postTag).innerHTML= b6_edition;

			if (document.getElementById(ratingTag)) {
				document.getElementById(ratingTag).style.display="none";
			}
		}
		x_ajax_showEditWindow(rowID, updateEditWindow);
	}
	else {
		document.getElementById('postContent' + rowID).innerHTML = document.getElementById('posteditCache' + rowID).innerHTML;
		document.getElementById('postedit' + rowID).style.display = "none";
		document.getElementById('postedit' + rowID).innerHTML = "";
	}
}	

function updateEditWindow(result) {
	ajaxload_off();
	resultArray = result.split(':!@:');
	postTag = "";
	postTag = "postedit" + resultArray[0];
	b6_postedit[resultArray[0]] = resultArray[2];
	if (document.getElementById) {
		document.getElementById(postTag).innerHTML=resultArray[1];
		document.getElementById('posteditCache' + resultArray[0]).innerHTML = document.getElementById('postContent' + resultArray[0]).innerHTML;
		document.getElementById('postArea' + resultArray[0]).innerHTML=resultArray[2];
		document.getElementById('smiley_bar' + resultArray[0]).style.height = document.getElementById('main_edit' + resultArray[0]).clientHeight + "px";
		setTimeout(function() {previewEditPost(resultArray[0]);}, 1000);
	}
}

function previewEditPost(postID) {
	if (document.getElementById('postedit' + postID)) {
		if (document.getElementById('postedit' + postID).style.display == "block") {
			var textArea = document.getElementById('postArea' + postID).value;

			if (textArea != b6_postedit[postID]) {
				c = setInterval(function() {checkscrollpos(postID);}, 500);
				b6_postedit[postID] = textArea;
				dataLineprev = postID + "::@ppo@::" + textArea + "::@ppo@::";
				dataLineprev = europlus(dataLineprev);
				x_ajax_previewPost(dataLineprev, showpreviewEditPost);
			}
			else {
				setTimeout(function() {previewEditPost(postID);}, 1000);
			}
		}
	}
}

function showpreviewEditPost(dataline) {
	if (document.getElementById) {
		var dataLineArray = dataline.split('::cur@lo::');
		dataLineArray[1] = dataLineArray[1].replace(/::@plus@::/g, "+");
		dataLineArray[1] = dataLineArray[1].replace(/::@euro@::/g, "€");
		document.getElementById('postContent' + dataLineArray[0]).innerHTML = dataLineArray[1];
		setTimeout(function() {previewEditPost(dataLineArray[0]);}, 1000);
	}
}

function callAjaxSubmitEdit(rowID) {
	ajaxload_on();
	var textArea = document.getElementById('postArea' + rowID).value;
	document.getElementById('postedit' + rowID).style.display = "none";
	document.getElementById('postedit' + rowID).innerHTML = "";
	var postcontent = rowID + ":!@:" + textArea + ":!@::!@:";
	postcontent = europlus(postcontent);

	x_ajax_submitEdit(postcontent, resetEditWindow);
}

function callAjaxSubmitDelete(rowID) {	
	ajaxload_on();
	document.getElementById('postedit' + rowID).style.display = "none";
	document.getElementById('postedit' + rowID).innerHTML = "";

	x_ajax_submitDelete(rowID, resetEditWindow);
	return false;
}

function SubmitDePublish(rowID) {	
	ajaxload_on();
	var textArea = document.getElementById('postArea' + rowID).value;
	document.getElementById('postedit' + rowID).style.display = "none";
	document.getElementById('postedit' + rowID).innerHTML = "";

	var postcontent = rowID + ":!@:" + textArea + ":!@:depublish:!@:";
	postcontent = europlus(postcontent);
	
	x_ajax_submitEdit(postcontent, resetEditWindow);

	return false;
}

function resetEditWindow(result) {
	ajaxload_off();
	resultArray = result.split(':!@:');
	postTag = "";
	ratingTag = "";
	postTag = "postContent" + resultArray[0];
	ratingTag = "postRating"+ resultArray[0];
	b6_postedit[resultArray[0]] = "";

	if (document.getElementById) {
		document.getElementById(postTag).innerHTML=resultArray[1];
		document.getElementById(ratingTag).style.display="block";
	}
}

function callAjaxShowLastPost(rowID, event) {
	if (document.getElementById) {
		ajaxload_on();

		cursorX = event.clientX;
		cursorY = event.clientY;

		var onlyOneNew = false;
		if (document.getElementById('numnewPosts' + rowID)) {
			if (document.getElementById('numnewPosts' + rowID).innerHTML == "1") {
				onlyOneNew = true;
			}
		}
		var dataLine = rowID + ':!@:' + onlyOneNew;
		x_ajax_returnLastPost(dataLine, displayLastPost);

		if (onlyOneNew == true) {
			var newNotifier = document.getElementById('newPosts' + rowID);
			document.getElementById('newPosts_separator' + rowID).style.display = "none";
			var thread = document.getElementById('newPostsToggle' + rowID);
			if (newNotifier.innerHTML != '0') {
				var numid = document.getElementById('numpostu');
				num = numid.innerHTML;
				num = num -1;
				if (num == 0) {
					num = "";
				}
					numid.innerHTML = num;
				verify_newPostsStr();
			}
			newNotifier.innerHTML = '';
			thread.className = 'noNewPosts';
			remove_markunread(rowID);
		}
	}
}

function displayLastPost(dataLine) {
	if (document.getElementById) {	
		ajaxload_on();
		closelayer();
		var dlarray = dataLine.split('::arrdlm::');
//		var lastpostpreview = 'lastpostpreview'  + dlarray[2];
		displaylayer(dlarray[0],cursorX,cursorY,true,'postContent_layer',true);
		
		document.getElementById('postAreaQuick').value = textareaCacheQuick;
		setTimeout(function() {quickpostCache();}, 1000);
	}
}

function quickpostCache() {
	if (document.getElementById('postAreaQuick')) {
		textareaCacheQuick = document.getElementById('postAreaQuick').value;
		setTimeout(function() {quickpostCache();}, 1000);
	}
}

function toggleLayer(whichLayer,display) {
	var distype = 'block';
	var style2 = "";
//	var class2 = "";

	if (display) {
		distype = display;
	}
	if (document.getElementById) {
		// this is the way the standards work
		style2 = document.getElementById(whichLayer).style;
		style2.display=(style2.display==distype)?'none':distype;
		
	}
	else if (document.all) {
		// this is the way old msie versions work
		style2 = document.all[whichLayer].style;
		style2.display = style2.display? "":distype;
	}
	else if (document.layers) {
		//this is the way nn4 works
		style2 = document.layers[whichLayer].style;
		style2.display = style2.display? "":distype;
	}
}

function toggleHeight(layer,height) {
	var div = document.getElementById(layer);
	var currentheight = div.style.maxHeight;
//	alert(currentheight);
	if (currentheight == height)
		div.style.maxHeight = "";
	else
		div.style.maxHeight = height;
}

function untoggleLayer(Layer1, Layer2, Layer3, Layer4, Layer5, Layer6, Layer7, Layer8) {
	var style2 = "";
	var class2 = "";
	if (document.getElementById) {
		// this is the way the standards work
		style2 = document.getElementById(Layer1).style;
		style2.display=(style2.display=='block')?'block':'block';
		class2 = document.getElementById(Layer1 + '2');
		class2.className= 'threadTypeSel';

		style2 = document.getElementById(Layer2).style;
		style2.display=(style2.display=='block')?'none':'none';
		class2 = document.getElementById(Layer2 + '2');
		class2.className= 'threadType';

		style2 = document.getElementById(Layer3).style;
		style2.display=(style2.display=='block')?'none':'none';
		class2 = document.getElementById(Layer3 + '2');
		class2.className= 'threadType';

		style2 = document.getElementById(Layer4).style;
		style2.display=(style2.display=='block')?'none':'none';
		class2 = document.getElementById(Layer4 + '2');
		class2.className= 'threadType';

		style2 = document.getElementById(Layer5).style;
		style2.display=(style2.display=='block')?'none':'none';
		class2 = document.getElementById(Layer5 + '2');
		class2.className= 'threadType';

		style2 = document.getElementById(Layer6).style;
		style2.display=(style2.display=='block')?'none':'none';
		class2 = document.getElementById(Layer6 + '2');
		class2.className= 'threadType';

		style2 = document.getElementById(Layer7).style;
		style2.display=(style2.display=='block')?'none':'none';
		class2 = document.getElementById(Layer7 + '2');
		class2.className= 'threadType';

		style2 = document.getElementById(Layer8).style;
		style2.display=(style2.display=='block')?'none':'none';
		class2 = document.getElementById(Layer8 + '2');
		class2.className= 'threadType';

		
	}
	else if (document.all) {
		// this is the way old msie versions work
		style2 = document.all[Layer1].style;
		style2.display = style2.display? "":"block";
		class2 = document.all[Layer1 + '2'];
		class2.className= 'threadTypeSel';

		style2 = document.all[Layer2].style;
		style2.display = style2.display? "":"";
		class2 = document.all[Layer2 + '2'];
		class2.className= 'threadType';

		style2 = document.all[Layer3].style;
		style2.display = style2.display? "":"";
		class2 = document.all[Layer3 + '2'];
		class2.className= 'threadType';

		style2 = document.all[Layer4].style;
		style2.display = style2.display? "":"";
		class2 = document.all[Layer4 + '2'];
		class2.className= 'threadType';

		style2 = document.all[Layer5].style;
		style2.display = style2.display? "":"";
		class2 = document.all[Layer5 + '2'];
		class2.className= 'threadType';

		style2 = document.all[Layer6].style;
		style2.display = style2.display? "":"";
		class2 = document.all[Layer6 + '2'];
		class2.className= 'threadType';

		style2 = document.all[Layer7].style;
		style2.display = style2.display? "":"";
		class2 = document.all[Layer7 + '2'];
		class2.className= 'threadType';

		style2 = document.all[Layer8].style;
		style2.display = style2.display? "":"";
		class2 = document.all[Layer8 + '2'];
		class2.className= 'threadType';
	}
	else if (document.layers) {
		//this is the way nn4 works
		style2 = document.layers[Layer1].style;
		style2.display = style2.display? "":"block";
		class2 = document.layers[Layer1 + '2'];
		class2.className= 'threadTypeSel';

		style2 = document.layers[Layer2].style;
		style2.display = style2.display? "":"";
		class2 = document.layers[Layer2 + '2'];
		class2.className= 'threadType';

		style2 = document.layers[Layer3].style;
		style2.display = style2.display? "":"";
		class2 = document.layers[Layer3 + '2'];
		class2.className= 'threadType';

		style2 = document.layers[Layer4].style;
		style2.display = style2.display? "":"";
		class2 = document.layers[Layer4 + '2'];
		class2.className= 'threadType';

		style2 = document.layers[Layer5].style;
		style2.display = style2.display? "":"";
		class2 = document.layers[Layer5 + '2'];
		class2.className= 'threadType';

		style2 = document.layers[Layer6].style;
		style2.display = style2.display? "":"";
		class2 = document.layers[Layer6 + '2'];
		class2.className= 'threadType';

		style2 = document.layers[Layer7].style;
		style2.display = style2.display? "":"";
		class2 = document.layers[Layer7 + '2'];
		class2.className= 'threadType';

		style2 = document.layers[Layer8].style;
		style2.display = style2.display? "":"";
		class2 = document.layers[Layer8 + '2'];
		class2.className= 'threadType';
	}
}

function toggleElement(element) {
	element.style.display=(element.style.display=='block')?'none':'block';
}

function toggleClass(whichLayer) {
	var x = "";
	var i = 0;
	if (document.getElementById) {
		x = document.getElementsByTagName('div');

		for (i=0;i<x.length;i++) {
			if (x[i].className == whichLayer) {	
				x[i].style.display=(x[i].style.display=='block')?'none':'block';
			}
		}
	}

	else if (document.all) {
		x = document.getElementsByTagName('div');

		for (i=0;i<x.length;i++) {

			if (x[i].className == whichLayer) {				
				x[i].style.display=(x[i].style.display=='block')?'none':'block';
			}
		}
	}
	else if (document.layers) {
		x = document.getElementsByTagName('div');

		for (i=0;i<x.length;i++) {

			if (x[i].className == whichLayer) {				
				x[i].style.display=(x[i].style.display=='block')?'none':'block';
			}
		}
	}
}

function showPost(postID) {
	if (document.getElementById) {
		var postToShow = document.getElementById('post' + postID);

		postToShow.style.display=(postToShow.style.display=='block')?'none':'block';
		document.getElementById('hidden' + postID).style.display = (document.getElementById('hidden' + postID).style.display=='none')?'block':'none';
		document.getElementById('normal' + postID).style.display = (document.getElementById('normal' + postID).style.display=='block')?'none':'block';
	}
}

function validateForm() {
	returnStr = true;
	if (document.getElementById) {
		document.getElementById('sendThread').style.display="none";
		document.getElementById('sendThreadDisabled').style.display="inline";
		if ((document.getElementById('newthreadtitle').value == "") || (document.getElementById('postArea0').value == "")) {
				text = "<div style='height:16px;'></div>" + b6_error1;
				displayError(text);
				returnStr = false;
		}
	if (returnStr == true) {
			document.getElementById('main').style.opacity = ".30";
			document.getElementById('main').style.filter = "alpha(opacity=30)";
			pleasewait();
			document.getElementById('valid_form').innerHTML = "OK";
			document.thread_form.submit();
		}
	}
}

function displayError(errorStr) {
	if (document.getElementById) {
		var erp = document.getElementById('errorPane');		
		var erptext = document.getElementById('errorPaneText');
		erptext.innerHTML = "<div id='errorImage'></div>" + errorStr;
		erp.style.opacity = "1";
		erp.style.filter = "alpha(opacity=100)";
		erp.style.display = "block";
		var xscroll = 0;
		var yscroll = 0;
		var errorPaneW = erp.offsetWidth;
		var errorPaneH = erp.offsetHeight;
		if (window.innerWidth) {
			xscroll = (window.innerWidth - errorPaneW) / 2;
			yscroll = (window.innerHeight - errorPaneH) / 2;
		}
		else if (document.documentElement.clientWidth) {
			xscroll = (document.documentElement.clientWidth - errorPaneW) / 2;
			yscroll = (document.documentElement.clientHeight - errorPaneH) / 2;
		}
		erp.style.left = xscroll + "px";
		erp.style.top = yscroll + "px";

		erp.style.visibility = "visible";
	}
}

function closeError() {
	if (document.getElementById) {
		var errorPane = document.getElementById('errorPane');		
		var errorPaneText = document.getElementById('errorPaneText');
		errorPane.style.display = "none";
		errorPane.style.visibility = "hidden";
		
		if (document.getElementById('sendThread')) {
			document.getElementById('sendThreadDisabled').style.display="none";
			document.getElementById('sendThread').style.display="inline";
		}

		if (document.getElementById('ff_dontbother')) {
			if (document.getElementById('ff_dontbother').checked) {
				SetCookie('old_browser', 'ff', 365);
			}
			else {
				SetCookie('old_browser', 'ff', 7);
		}
		}
		if (document.getElementById('ie_dontbother')) {
			if (document.getElementById('ie_dontbother').checked) {
				SetCookie('old_browser', 'ie', 365);
			}
			else {
				SetCookie('old_browser', 'ie', 1);
		}
		}
		errorPaneText.innerHTML = "";		
	}
}

function donotaskNotification() {
	SetCookie('notifications', 'dontask', 365);
}

function addOption(posNeg) {
	if (document.getElementById) {
		var posInputElement = document.getElementById('addNewPosOption');

		if (posNeg == 1) {
			posInputElement = document.getElementById('addNewNegOption');
		}

		var dataline = posNeg + "::@noption@::" + europlus(posInputElement.value);
		x_ajax_addNewModOption(dataline, updateOptionsList);
		posInputElement.value = "";
	}
}

function updateOptionsList(dataline) {	
	if (document.getElementById) {
		var datalineArray = dataline.split('::');
		var optionsList;
		if (datalineArray[0] == "0") {
			optionsList = document.getElementById('posOptionsHolder');
		}
		else {
			optionsList = document.getElementById('negOptionsHolder');
		}
		optionsList.innerHTML = datalineArray[1];
	}
}

function chooseGraft(newGraft) {
	x_ajax_changeGraft(newGraft, reloadPage);	
}

function reloadPage(retVal) {
	window.location.reload();
}

function askIfOkay(promptQuestion) {
	var answer = confirm ( promptQuestion );
	if (answer) {
		return true;
	}
	else {
		return false;
}
}

function startTimeoutCheck() {
	if (document.getElementById) {
		document.getElementById('connectionStatus').innerHTML = jsCheckTimeout;

		jsCheckTimeout = (jsCheckTimeout*1) + 1;
	}
	setTimeout(function() {startTimeoutCheck();}, 10000);
}

function toggleRatingArrow(type, ID, upDown, amount, type2) {
	if (document.getElementById) {
		var ratingHolder = document.getElementById('ratingDisplay' + type + ID);
		var rStatus = "";
		var newRating = 0;
		var arrow = "";
		var otherArrow = "";

		if (upDown == 'uparrow') {			
			arrow = document.getElementById('uparrow' + type + ID);
			otherArrow = document.getElementById('downarrow' + type + ID);

			if (arrow.className == 'uparrowoff' && otherArrow.className == 'downarrowoff') {
				otherArrow.className = 'downarrowoff';
				arrow.className = 'uparrowon';

				rStatus = document.getElementById(type + 'RatingStatus' + ID);
				rStatus.className='postTitlePositive';
				rStatus.innerHTML= b6_rated;

				newRating = (ratingHolder.innerHTML * 1) + amount;

				ratingHolder.style.opacity = "0";
				ratingHolder.style.filter = "alpha(opacity=0)";
				ratingHolder.innerHTML = newRating.toFixed(2);
				ratingHolder.className = "postRatingColorGradient2";
				fadeIn('ratingDisplay' + type + ID,0);

				if (type2 && document.getElementById('ratingDisplay' + type2 + ID)) {
					ratingHolder = document.getElementById('ratingDisplay' + type2 + ID);
					arrow = document.getElementById('uparrow' + type2 + ID);
					otherArrow = document.getElementById('downarrow' + type2 + ID);
					otherArrow.className = 'downarrowoff';
					arrow.className = 'uparrowon';

					rStatus = document.getElementById(type2 + 'RatingStatus' + ID);
					rStatus.className='postTitlePositive';
					rStatus.innerHTML= b6_rated;

					newRating = (ratingHolder.innerHTML * 1) + amount;

					ratingHolder.style.opacity = "0";
					ratingHolder.style.filter = "alpha(opacity=0)";
					ratingHolder.innerHTML = newRating.toFixed(2);
					ratingHolder.className = "postRatingColorGradient2";
					fadeIn('ratingDisplay' + type2 + ID,0);
				}
				if (type2) {
					updateMod(ID, type2, upDown);
				}
				else {
					updateMod(ID, type, upDown);
			}
		}
		}
		else if (upDown == 'downarrow') {
			arrow = document.getElementById('downarrow' + type + ID);
			otherArrow = document.getElementById('uparrow' + type + ID);

			if (arrow.className == 'downarrowoff' & otherArrow.className == 'uparrowoff') {
				otherArrow.className = 'uparrowoff';
				arrow.className = 'downarrowon';
				rStatus = document.getElementById(type + 'RatingStatus' + ID);
				rStatus.className='postTitleNegative';
				rStatus.innerHTML= b6_rated;

				newRating = (ratingHolder.innerHTML * 1) - amount;
				ratingHolder.style.opacity = "0";
				ratingHolder.style.filter = "alpha(opacity=0)";
				ratingHolder.innerHTML = newRating.toFixed(2);
				ratingHolder.className = "postRatingColorGradient3";
				fadeIn('ratingDisplay' + type + ID,0);

				if (type2 && document.getElementById('ratingDisplay' + type2 + ID)) {
					ratingHolder = document.getElementById('ratingDisplay' + type2 + ID);
					arrow = document.getElementById('downarrow' + type2 + ID);
					otherArrow = document.getElementById('uparrow' + type2 + ID);
					otherArrow.className = 'uparrowoff';
					arrow.className = 'downarrowon';
					rStatus = document.getElementById(type2 + 'RatingStatus' + ID);
					rStatus.className='postTitleNegative';
					rStatus.innerHTML= b6_rated;

					newRating = (ratingHolder.innerHTML * 1) - amount;
					ratingHolder.style.opacity = "0";
					ratingHolder.style.filter = "alpha(opacity=0)";
					ratingHolder.innerHTML = newRating.toFixed(2);
					ratingHolder.className = "postRatingColorGradient3";
					fadeIn('ratingDisplay' + type2 + ID,0);
				}
				if (type2) {
					updateMod(ID, type2, upDown);
				}
				else {
					updateMod(ID, type, upDown);
			}					
		}
	}
}
}

function setRateVisible(postID,ID,postHIDE,rated,already_rated) {
	var is_rated = false;

	if (document.getElementById('postRatingStatus' + ID).className != "postTitle") {
		is_rated = true;
	}
	if (is_rated == false) {				
		if ((document.getElementById(postHIDE).style.visibility != "visible") && (rated != '1')) {
			document.getElementById(postID).style.visibility="visible";
	}
	}
	return(already_rated);
}

function selectRate(rateID,postID,ID) {
	var dataline = rateID+"::"+ID;
	document.getElementById(postID).style.visibility="hidden";
	submitRateComment(dataline);
}

function updateMod(ID, type, upDown) {	
	x_ajax_updateMod(ID + ':' + type + ':' + upDown, finishUpdateMod);
}

function finishUpdateMod(dataline) {
}

function removerating(id) {
	if (document.getElementById) {
		x_ajax_removerating(id, rateremoved);
	}
}

function rateremoved(data) {
	if (document.getElementById) {
		var postArray = data.split('::@@::');
		if (document.getElementById('postid' + postArray[0])) {
			document.getElementById('ratingDisplaypost' + postArray[0]).innerHTML = postArray[2];
			document.getElementById('ratingDisplaypost' + postArray[0]).className = postArray[3];
			document.getElementById('arrowpost' + postArray[0]).innerHTML = postArray[7];
			document.getElementById('up_rate' + postArray[0]).style.visibility = "hidden";
			document.getElementById('down_rate' + postArray[0]).style.visibility = "hidden";
			document.getElementById('up_rate' + postArray[0]).innerHTML = postArray[8];
			document.getElementById('down_rate' + postArray[0]).innerHTML = postArray[9];
			document.getElementById('postRatingStatus' + postArray[0]).innerHTML = postArray[6];
			document.getElementById('postRatingStatus' + postArray[0]).className = "postTitle";
			document.getElementById('postwhorated' + postArray[0]).innerHTML = postArray[4];
			already_rated[postArray[0]] = "";
			if (postArray[5]) {
				toggleLayer('post' + postArray[0]);
				toggleLayer('hiddenpost' + postArray[0]);
				toggleLayer('hidden' + postArray[0]);
				toggleLayer('normal' + postArray[0]);
			}
		}
	}
}

function searchForm() {
	if (document.getElementById) {
		var ph = document.getElementById('searchForm');
		if (ph.style.display == "block") {
			ph.style.display = "none";
			document.getElementById('threadInfoWrapper').style.display = "block";
			document.getElementById('parentC').style.opacity = ".30";
			document.getElementById('parentC').style.filter = "alpha(opacity=30)";
			document.getElementById('searchbtncl').style.display = "none";
			document.getElementById('searchbtnop').style.display = "block";
			document.getElementById('treag_tagList').style.display = "block";
			pleasewait();

			document.getElementById('numpage_cache').innerHTML = "1";
			filter = document.getElementById('filter').innerHTML;
			document.getElementById('chan_cache').innerHTML = document.getElementById('chan_cache_search').innerHTML;
			document.getElementById('chan_cache_search').innerHTML = "";
			document.getElementById('channelsAnchor').innerHTML = document.getElementById('channelsAnchor_cache').innerHTML;
			document.getElementById('channelsAnchor_cache').innerHTML = "";
			document.getElementById('channelsWindow').innerHTML = document.getElementById('channelsWindow_cache').innerHTML;
			document.getElementById('channelsWindow_cache').innerHTML = "";
			document.getElementById('span_chan_make_default').style.display = "inline";
			channels = document.getElementById('chan_cache').innerHTML;
			var tags = document.getElementById('tags_cache').innerHTML;
			var team = document.getElementById('listthreadteam').innerHTML;
			var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:1:@@:" + channels + ":@@:" + tags;
		
			x_ajax_resetThreadList(dataLine, displayResetThreads);
		}
		else if (ph.style.display == "none") {
			clearInterval(t);
			clearInterval(m);
			ph.style.opacity= ".00";
			ph.style.filter = "alpha(opacity=0)";
			ph.style.display='block';
			fadeIn(ph.id,0);

			document.getElementById('chan_cache_search').innerHTML = document.getElementById('chan_cache').innerHTML;
			document.getElementById('channelsAnchor_cache').innerHTML = document.getElementById('channelsAnchor').innerHTML;
			document.getElementById('channelsWindow_cache').innerHTML = document.getElementById('channelsWindow').innerHTML;
			document.getElementById('span_chan_make_default').style.display = "none";
			document.getElementById('numpage_cache').innerHTML = "1";
			document.getElementById('searchbtnop').style.display = "none";
			document.getElementById('searchbtncl').style.display = "block";
			document.getElementById('parentC').innerHTML = "";
			document.getElementById('pagesListStr').innerHTML = "";
			document.getElementById('pagesListStrT').innerHTML = "";
			document.getElementById('newPostsStr').innerHTML = "";
			document.getElementById('searchterm').focus();
		}
	}
}

function submitsearch() {
	if (document.getElementById) {
		var searchterm = document.getElementById('searchterm');
		var searchusername = document.getElementById('searchusername');
		var searchinthreadid = document.getElementById('searchinthreadid');
		var searchdatelimit = document.getElementById('searchdatelimit');
		if (searchterm.value != "" || searchusername.value != "") {
			clearInterval(t);
			clearInterval(m);
			var exprtype = "";
			if (document.getElementById('expression_exact').checked) {
				exprtype = "exact";
			}
			else if (document.getElementById('expression_all').checked) {
				exprtype = "all";
			}
			else if (document.getElementById('expression_one').checked) {
				exprtype = "one";
			}

			document.getElementById('parentC').style.opacity = ".30";
			document.getElementById('parentC').style.filter = "alpha(opacity=30)";
			pleasewait();
			document.getElementById('numpage_cache').innerHTML = "1";
			var filter = document.getElementById('filter').innerHTML;
			var channels = document.getElementById('chan_cache').innerHTML;
			var tags = document.getElementById('tags_cache').innerHTML;
			var team = document.getElementById('listthreadteam').innerHTML;
			var dataLine = europlus(searchterm.value) + ':!@:' + europlus(searchusername.value) + ':!@:' + searchdatelimit.value + ':!@:' + searchinthreadid.value + ':!@:'  + exprtype + ":@@:" + team + ":@@:" + filter + ":@@:1:@@:" + channels + ":@@:" + tags;

			var cacheS = escape(searchterm.value) + ':!@:' + escape(searchusername.value) + ':!@:' + searchdatelimit.value + ':!@:0:!@:' + searchinthreadid.value + ':!@:'  + exprtype;
				document.getElementById('cacheS').innerHTML = cacheS;
			if (document.getElementById('searchtype_threads').checked) {
				x_ajax_resetThreadList(dataLine, displaySearchResults);
			}
			else {
				x_ajax_search_posts(dataLine, displaySearchPost);
			}
		}
	}
}

function searchUser(user,st,sp,ht) {
	if (document.getElementById) {
		var data = ':!@:' + user + ':!@::!@:' + st + ':!@::!@::!@:exact';
		var cacheS = document.getElementById('cacheS');
		cacheS.innerHTML = data;
		clearInterval(t);
		clearInterval(m);

		if (ht != "1")	{
			clearTimeout(p);

			document.getElementById('user_profile').style.display = "none";
			document.getElementById('user_profile').innerHTML = "";
			document.getElementById('thread').innerHTML = "";

			window.location.hash="#threadlist";
			storedhash = window.location.hash;
			document.getElementById('threadlist').style.display = "block";
			scrolltoID('anchor_nav');
			document.title = titleSite;
			titleHolder = document.title;
		}
		else {
			document.getElementById('threadlist').style.display = "block";
		}

		document.getElementById('parentC').innerHTML = "";
		document.getElementById('pagesListStr').innerHTML = "";
		document.getElementById('pagesListStrT').innerHTML = "";
		document.getElementById('newPostsStr').innerHTML = "";
		document.getElementById('searchbtnop').style.display = "none";
		document.getElementById('searchbtncl').style.display = "block";
		document.getElementById('chan_cache_search').innerHTML = document.getElementById('chan_cache').innerHTML;
		document.getElementById('channelsAnchor_cache').innerHTML = document.getElementById('channelsAnchor').innerHTML;
		document.getElementById('channelsWindow_cache').innerHTML = document.getElementById('channelsWindow').innerHTML;
		document.getElementById('span_chan_make_default').style.display = "none";
		document.getElementById('numpage_cache').innerHTML = "1";

		var ph = document.getElementById('searchForm');
		ph.style.opacity= ".00";
		ph.style.filter = "alpha(opacity=0)";
		ph.style.display='block';
		fadeIn(ph.id,0);
		pleasewait();
		retrievesearch();

		document.getElementById('numpage_cache').innerHTML = "1";
		var filter = document.getElementById('filter').innerHTML;
		var channels = document.getElementById('chan_cache').innerHTML;
		var team = document.getElementById('listthreadteam').innerHTML;
		var tags = document.getElementById('tags_cache').innerHTML;
		var dataLine = data + ":@@:" + team + ":@@:" + filter + ":@@:1:@@:" + channels + ":@@:" + tags;

		if (st) {
			document.getElementById('searchtype_threads').checked = true;
			x_ajax_resetThreadList(dataLine, displaySearchResults);
		}
		else {
			document.getElementById('searchtype_posts').checked = true;
			x_ajax_search_posts(dataLine, displaySearchPost);
		}
	}
}

function changepagepost(page) {
	if (document.getElementById) {
		var cacheS = document.getElementById('cacheS');
		var data = cacheS.innerHTML;
		if (data != "") {
			var dataS = data.split(':!@:');
			var form = document.forms["search"];
			var search = form.elements;
			search[0].value = "";
			search[7].value = "";
			search[8].value = "";
			search[9].value = "";
			if (dataS[0] != "undefined") {
				search[0].value = unescape(dataS[0]);
			}
			if (dataS[1] != "undefined") {
				search[7].value = unescape(dataS[1]);
			}
			if (dataS[2] != "undefined") {
				search[9].value = dataS[2];
			}
			if (dataS[3] == "1") {
				search[3].checked = true;
			}
			if (dataS[4] != "undefined") {
				search[8].value = dataS[4];
			}

			var exprtype = "exact";
			if (dataS[5] != "undefined") {
				exprtype = dataS[5];
				if (exprtype == "all") {
					search[5].checked = true;
				}
				else if (exprtype == "one") {
					search[6].checked = true;
				}
			}

			if (!page)
				page = document.getElementById('gotopage').value;
			document.getElementById('numpage_cache').innerHTML = page;
			var filter = document.getElementById('filter').innerHTML;
			var channels = document.getElementById('chan_cache').innerHTML;
			var tags = document.getElementById('tags_cache').innerHTML;
			var dataLine = europlus(search[0].value) + ':!@:' + europlus(search[7].value) + ':!@:' + search[9].value + ':!@:' + search[8].value + ':!@:' + exprtype + ":@@::@@:" + filter + ":@@:" + page + ":@@:" + channels + ":@@:" + tags;
			document.getElementById('parentC').style.opacity = ".30";
			document.getElementById('parentC').style.filter = "alpha(opacity=30)";
			pleasewait();
			x_ajax_search_posts(dataLine, displaySearchPost);
		}
	}
}

function retrievesearch() {
	if (document.getElementById) {
		var cacheS = document.getElementById('cacheS');
		var data = cacheS.innerHTML;
		if (data != "") {
			var dataS = data.split(':!@:');
			if (dataS[0] != "undefined") {
				form = document.forms["search"];
				search = form.elements;
				search[0].value = "";
				search[7].value = "";
				search[8].value = "";
				search[9].value = "";
				if (dataS[0] != "undefined") {
					search[0].value = unescape(dataS[0]);
				}
				if (dataS[1] != "undefined") {
					search[7].value = unescape(dataS[1]);
				}
				if (dataS[2] != "undefined") {
					search[9].value = dataS[2];
				}
				if (dataS[3] == "1") {
					search[3].checked = true;
				}
				if (dataS[4] != "undefined") {
					search[8].value = dataS[4];
				}
				var exprtype = "exact";
				if (dataS[5] != "undefined") {
					exprtype = dataS[5];
					if (exprtype == "all") {
						search[5].checked = true;
					}
					else if (exprtype == "one") {
						search[6].checked = true;
					}
				}
			}
		}
	}
}

function displaySearchResults(dataline) {	
	if (document.getElementById) {
		var dlarray = dataline.split('::arrdlm::');

		pleasewait_off();

		document.getElementById('newPostsStr').innerHTML = dlarray[2];
		document.getElementById('threadInfoWrapper').style.display = "block";

		var parent = document.getElementById('parentC');
		parent.innerHTML = dlarray[0];
		parent.style.opacity = "1.00";
		parent.style.filter = "alpha(opacity=100)";

		document.getElementById('pagesListStr').innerHTML = dlarray[1];
		document.getElementById('pagesListStrT').innerHTML = dlarray[1];
		if (document.getElementById('tags_presents')) {
			document.getElementById('tags_presents').innerHTML = dlarray[7];
		}

		document.getElementById('listnewPostsStr').innerHTML = dlarray[3];
	}
}

function displaySearchPost(dataline) {	
	if (document.getElementById) {
		var dlarray = dataline.split('::arrdlm::');
		pleasewait_off();
		document.getElementById('newPostsStr').innerHTML = "";
		document.getElementById('treag_tagList').style.display = "none";
		document.getElementById('threadInfoWrapper').style.display = "block";

		var parent = document.getElementById('parentC');
		parent.innerHTML = dlarray[0];
		parent.style.opacity = "1.00";
		parent.style.filter = "alpha(opacity=100)";

		document.getElementById('pagesListStr').innerHTML = dlarray[4];
		document.getElementById('pagesListStrT').innerHTML = dlarray[4];
		scrolltoID('anchor_nav');
	}
}

function callNewThreadForm() {
	if (document.getElementById) {
		var form = document.forms["channelFilter"];
		var channelNodes = form.elements;
		var chan = '';
		var j = 0;
		for (i=0;i<channelNodes.length;i++) {
			if (channelNodes[i].checked == true) {
				chan = channelNodes[i].name;
				j ++;
			}
		}
		if (j > 1)
			chan = '';

		var ph = document.getElementById('newThreadFormPlaceholder');
		if (ph.style.display == "block") {
			ph.style.display = "none";
		}
		else if (ph.style.display == "none") {
			ph.style.display = "block";
			if (textareaCache0) {
				document.getElementById('postArea0').value = textareaCache0;
			}
			if (chan)
				document.getElementById('channelTag').value = chan;
		}
		else {
			x_ajax_callNewThreadForm(chan, showNewThreadForm);
		}
	}
}

function showNewThreadForm(formData) {
	if (document.getElementById) {
		var ph = document.getElementById('newThreadFormPlaceholder');
		ph.innerHTML = formData;
		ph.style.opacity= ".00";
		ph.style.filter = "alpha(opacity=0)";
		ph.style.display='block';
		document.getElementById('postArea0').value = textareaCache0;
		fadeIn(ph.id,0);
	}
}

function toggleDisplay(elementName) {
	if (document.getElementById) {
		var element = document.getElementById(elementName);
		
		if (element.style.display == "none") {
			element.style.opacity = ".00";
			element.style.filter = "alpha(opacity=0)";
			element.style.display = "block";
			fadeIn(elementName,0);
		}
		else if (element.style.display == "block") {
			element.style.display="none";
		}
		else {
			element.style.opacity = ".00";
			element.style.filter = "alpha(opacity=0)";
			element.style.display = "block";
			element.style.position = "relative";
			element.style.visibility = "visible";
			fadeIn(elementName,0);
		}
	}
}

function updateLastPostMinutes() {
	if (document.getElementById) {
		clearInterval(m);
		var elementsToUpdate = document.getElementsByTagName('span');

		for (i=0; i<elementsToUpdate.length; i++) {
			if (elementsToUpdate[i].className == "updateMinute") {
				elementsToUpdate[i].innerHTML = (elementsToUpdate[i].innerHTML * 1) + 1;
			}
		}
		m = setInterval(function() {updateLastPostMinutes();}, 60000);
	}
}

function addUserToPthread(ID) {
	if (document.getElementById) {
		var dataLine = document.getElementById('userprofilename2').value + ':!@dpu@:' + ID;
		x_ajax_add_new_pthread_user(dataLine, updatePthreadUsers);
		document.getElementById('userprofilename2').value = "";
	}
}

function updatePthreadUsers(data) {
	var dataArray = data.split("::arrdlm::");
	
	if (dataArray[0] == "false") {
		displayError(dataArray[1], 'add_username_button');
	}
	else {
		if (document.getElementById) {
			document.getElementById('listpThreadUsers').innerHTML = dataArray[1];
		}
	}
}

function deletePthreadUser(ID,threadID) {
	var dataLine = ID + ':!@dpu@:' + threadID;
	x_ajax_delete_pthread_user(dataLine, updatePthreadUsers);
}

function submitPollVote(ID) {
	poll = document.forms[ID + "pollChoice"];
	polls = poll.elements;
	
	selected = 'false';
	for (counter = 0; counter < polls.length; counter++) {
		if (polls[counter].checked == true) {
			selected = polls[counter].value;
		}
	}		
	document.getElementById('submittingVoteIndicator').style.display = "block";
	
	x_ajax_submit_poll_vote(ID + ":arrdlm:" + selected, updatePoll);
}

function updatePoll(selection) {
	selection = selection.split(':arrdlm:');
	if (IsNumeric(selection[1])) {
		if (document.getElementById) {
			document.getElementById('submittingVoteIndicator').style.display='none';
			var poll = document.getElementById('pollHolder');
			poll.style.opacity = "0.0";
			poll.style.filter = "alpha(opacity=0)";
			poll.innerHTML = selection[2];
			fadeIn('pollHolder',0);
		}
	}
	else {
		document.getElementById('submittingVoteIndicator').style.display='none';
		displayError(selection[1], selection[0] + 'submit');
	}
}

function IsNumeric(sText) {
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;

    for (i = 0; i < sText.length && IsNumber == true; i++) { 
      Char = sText.charAt(i); 
		if (ValidChars.indexOf(Char) == -1) {
         IsNumber = false;
         }
      }
   return IsNumber;
   
}

function showPreview(text, delay, offsetH, offsetV, reference) {
	if (closePreview()) {
		if (delay == 0) {
			if (document.getElementById) {
				document.getElementById('previewPane').innerHTML = text;	
				previewPaneDelay = setTimeout(function() {showPreview('blank', 1, offsetH, offsetV, reference);}, 700);
			}
		}
		else {
			if (document.getElementById) {
				var ref = document.getElementById(reference);
				var preview = document.getElementById('previewPane');
				var offTop = ref.offsetTop + offsetV +146;
				var offLeft = ref.offsetLeft + offsetH -200;

				if (text != 'blank') {
					preview.innerHTML = text;
				}

				preview.style.top = offTop + 'px';
				preview.style.left = offLeft + 'px';
				preview.style.zindex = '1001';
				preview.style.opacity = "0";
				preview.style.filter = "alpha(opacity=0)";
				preview.style.display='block';
				fadeIn('previewPane',0);
			}
		}
	}
}

function closePreview() {
	if (document.getElementById) {
		clearTimeout(previewPaneDelay);
		preview = document.getElementById('previewPane');
		if (preview.style.display == 'block') {
			preview.style.display = 'none';
			return false;
		}
		else {
			preview.style.display = 'none';
			return true;
		}
	}
}

function toggleSound() {
	if (document.getElementById) {
		speaker = document.getElementById('speaker');
		speaker.className=(speaker.className=='speakerOn')?'speakerOff':'speakerOn';
		SetCookie('mf_speaker', speaker.className, 300);
	}
}

function subscribe(ID) {
	if (document.getElementById) {
		x_ajax_subscribe(ID, updateSubscribed);
	}
}

function unsubscribe(ID) {
	if (document.getElementById) {
		x_ajax_unsubscribe(ID, updateSubscribed);
	}
}

function subscribe2(ID) {
	if (document.getElementById) {
		x_ajax_subscribe2(ID, reloadThreadList);
	}
}

function unsubscribe2(ID) {
	if (document.getElementById) {
		x_ajax_unsubscribe2(ID, reloadThreadList);
	}
}

function reloadThreadList() {
	pleasewait();
	var page = document.getElementById('numpage_cache').innerHTML;
	var filter = document.getElementById('filter').innerHTML;
	var channels = document.getElementById('chan_cache').innerHTML;
		var tags = document.getElementById('tags_cache').innerHTML;

		var team = document.getElementById('listthreadteam').innerHTML;

		var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:" + page + ":@@:" + channels + ":@@:" + tags;
		var ph = document.getElementById('searchForm');
	if (ph.style.display == "block") {
		var form = document.forms["search"];
		var searchNodes = form.elements;
		if (searchNodes[0].value != "" || searchNodes[4].value != "" || searchNodes[5].value != "") {
				clearInterval(t);
				clearInterval(m);
				dataLine = searchNodes[0].value + ':!@:' + searchNodes[4].value + ':!@:' + searchNodes[6].value + ':!@:' + searchNodes[5].value + dataLine;
			}
		}
		x_ajax_resetThreadList(dataLine, displayResetThreads);
	}

function updateSubscribed(dataline) {
	if (document.getElementById) {
		var dlarray = dataline.split('::@@UNSUBS@@::');
		
		document.getElementById('subscriptionNotification').innerHTML = dlarray[0];
		document.getElementById('subscriptionNotificationcache').innerHTML = dlarray[1];
	}
}

function hide(ID) {
	if (document.getElementById) {
		x_ajax_hide(ID, updateHide);
	}
}

function hide2(ID) {
	if (document.getElementById) {
		document.getElementById('newPostsStr').innerHTML = "&nbsp; <b>" + b6_wait + wait2;

		x_ajax_hide2(ID, updateSubscribed);

		var page = document.getElementById('numpage_cache').innerHTML;
		var filter = document.getElementById('filter').innerHTML;
		var channels = document.getElementById('chan_cache').innerHTML;
		var tags = document.getElementById('tags_cache').innerHTML;
		var team = document.getElementById('listthreadteam').innerHTML;

		var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:" + page + ":@@:" + channels + ":@@:" + tags;
		var ph = document.getElementById('searchForm');
		if (ph.style.display == "block") {
			var form = document.forms["search"];
			var searchNodes = form.elements;
			if (searchNodes[0].value != "" || searchNodes[4].value != "" || searchNodes[5].value != "") {
				clearInterval(t);
				clearInterval(m);
				dataLine = searchNodes[0].value + ':!@:' + searchNodes[4].value + ':!@:' + searchNodes[6].value + ':!@:' + searchNodes[5].value + dataLine;
			}
		}
		x_ajax_resetThreadList(dataLine, displayResetThreads);
	}
}

function unhide(ID) {
	if (document.getElementById) {
		x_ajax_unhide(ID, updateHide);
	}
}

function unhide2(ID) {
	if (document.getElementById) {
		document.getElementById('newPostsStr').innerHTML = "&nbsp; <b>" + b6_wait + wait2;

		x_ajax_unhide2(ID, updateSubscribed);

		var page = document.getElementById('numpage_cache').innerHTML;
		var filter = document.getElementById('filter').innerHTML;
		var channels = document.getElementById('chan_cache').innerHTML;
		var tags = document.getElementById('tags_cache').innerHTML;
		var team = document.getElementById('listthreadteam').innerHTML;

		var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:" + page + ":@@:" + channels + ":@@:" + tags;
		var ph = document.getElementById('searchForm');
		if (ph.style.display == "block") {
			var form = document.forms["search"];
			var searchNodes = form.elements;
			if (searchNodes[0].value != "" || searchNodes[4].value != "" || searchNodes[5].value != "") {
				clearInterval(t);
				clearInterval(m);
				dataLine = searchNodes[0].value + ':!@:' + searchNodes[4].value + ':!@:' + searchNodes[6].value + ':!@:' + searchNodes[5].value + dataLine;
			}
		}
		x_ajax_resetThreadList(dataLine, displayResetThreads);
	}
}

function updateHide(data) {
	if (document.getElementById) {
		document.getElementById('hideNotification').innerHTML = data;
	}
}

function markall(user) {
	if (document.getElementById) {
		document.getElementById('newPostsStr').innerHTML = "&nbsp; <b>" + b6_wait + wait2;
		var listnewPosts = document.getElementById('listnewPostsStr').innerHTML;
		clearInterval(t);
		x_ajax_markAll(listnewPosts, markallreset);
	}
}

function markallreset() {
	if (document.getElementById) {
		var page = document.getElementById('numpage_cache').innerHTML;
		var filter = document.getElementById('filter').innerHTML;
		var channels = document.getElementById('chan_cache').innerHTML;
		var tags = document.getElementById('tags_cache').innerHTML;
		var team = document.getElementById('listthreadteam').innerHTML;

		var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:" + page + ":@@:" + channels + ":@@:" + tags;
		var ph = document.getElementById('searchForm');
		if (ph.style.display == "block") {
			var form = document.forms["search"];
			var searchNodes = form.elements;
			if (searchNodes[0].value != "" || searchNodes[4].value != "" || searchNodes[5].value != "") {
				dataLine = searchNodes[0].value + ':!@:' + searchNodes[4].value + ':!@:' + searchNodes[6].value + ':!@:' + searchNodes[5].value + dataLine;
			}
		}

		x_ajax_resetThreadList(dataLine, displayResetThreads);
	}
}

function displayFilter(filter) {
	if (document.getElementById) {
		var ph = document.getElementById('searchForm');
		var parent = document.getElementById('parentC');

		document.getElementById('numpage_cache').innerHTML = "1";
		document.getElementById('listthreadteam').innerHTML = "";
		var team = "";
		
		document.getElementById('newPostsStr').innerHTML = "";
		if (ph.style.display != "block") {
			parent.style.opacity = ".30";
			parent.style.filter = "alpha(opacity=30)";
			pleasewait();
		}

		if (filter == '') {
			SetCookie('threadFilter', '', -1);
		}
		else {
			SetCookie('threadFilter', filter, 365);
		}
		clearInterval(h);

		document.getElementById('filter').innerHTML = filter;
		clearTimeout(h);
		if (filter != "") {
			storedhash = "#threadlist/" + filter;
		}
		else {
			storedhash = "#threadlist";
		}
		window.location.hash = storedhash;
		h = setInterval(function() {checkhash();}, 1000);

		document.getElementById('channelfilt').style.display = "inline-block";
		if (document.getElementById('NewThreadButton'))
		document.getElementById('NewThreadButton').style.display = "inline-block";
		if (document.getElementById('teamNameDiv'))
		document.getElementById('teamNameDiv').style.display = "none";

		document.getElementById('filter5').className= 'threadTMenuSel';
		document.getElementById('filter0').className= 'threadType';
		document.getElementById('filter1').className= 'threadTMenu';
		document.getElementById('filter2').className= 'threadType';
		document.getElementById('filter3').className= 'threadType';
		document.getElementById('filter4').className= 'threadType';
		document.getElementById('filter6').className= 'threadTMenu';

		if (filter == 'all') {
			document.getElementById('channelfilt').style.display = "none";
			document.getElementById('filter5').className= 'threadTMenu';
			document.getElementById('filter0').className= 'threadTypeSel';
			document.getElementById('filter1').className= 'threadTMenu';
			document.getElementById('filter2').className= 'threadType';
			document.getElementById('filter3').className= 'threadType';
			document.getElementById('filter4').className= 'threadType';
			document.getElementById('filter6').className= 'threadTMenu';
		}
		if (filter == 'pthreads') {
			document.getElementById('filter5').className= 'threadTMenu';
			document.getElementById('filter0').className= 'threadType';
			document.getElementById('filter1').className= 'threadTMenuSel';
			document.getElementById('filter2').className= 'threadType';
			document.getElementById('filter3').className= 'threadType';
			document.getElementById('filter4').className= 'threadType';
			document.getElementById('filter6').className= 'threadTMenu';
		}
		if (filter == 'subscribed') {
			document.getElementById('filter5').className= 'threadTMenu';
			document.getElementById('filter0').className= 'threadType';
			document.getElementById('filter1').className= 'threadTMenu';
			document.getElementById('filter2').className= 'threadTypeSel';
			document.getElementById('filter3').className= 'threadType';
			document.getElementById('filter4').className= 'threadType';
			document.getElementById('filter6').className= 'threadTMenu';
		}
		if (filter == 'buried') {
			document.getElementById('filter5').className= 'threadTMenu';
			document.getElementById('filter0').className= 'threadType';
			document.getElementById('filter1').className= 'threadTMenu';
			document.getElementById('filter2').className= 'threadType';
			document.getElementById('filter3').className= 'threadTypeSel';
			document.getElementById('filter4').className= 'threadType';
			document.getElementById('filter6').className= 'threadTMenu';
		}
		if (filter == 'hidden') {
			document.getElementById('filter5').className= 'threadTMenu';
			document.getElementById('filter0').className= 'threadType';
			document.getElementById('filter1').className= 'threadTMenu';
			document.getElementById('filter2').className= 'threadType';
			document.getElementById('filter3').className= 'threadType';
			document.getElementById('filter4').className= 'threadTypeSel';
			document.getElementById('filter6').className= 'threadTMenu';
		}
		if (filter == 'teams') {
			document.getElementById('channelfilt').style.display = "none";
			document.getElementById('filter5').className= 'threadTMenu';
			document.getElementById('filter0').className= 'threadType';
			document.getElementById('filter1').className= 'threadTMenu';
			document.getElementById('filter2').className= 'threadType';
			document.getElementById('filter3').className= 'threadType';
			document.getElementById('filter4').className= 'threadType';
			document.getElementById('filter6').className= 'threadTMenuSel';
			team = document.getElementById('selectteam').value;
			if (team) {
				document.getElementById('NewThreadButton').style.display = "none";
				x_ajax_getTeamName(team, displayTeamName);
			}
			document.getElementById('listthreadteam').innerHTML = team;
			clearInterval(h);

			if (team != "") {
				storedhash = "#threadlist/teams/" + team;
			}
			else {
				storedhash = "#threadlist/teams";
			}
			window.location.hash = storedhash;
			h = setInterval(function() {checkhash();}, 1000);
		}

		channels = document.getElementById('chan_cache').innerHTML;
		var tags = document.getElementById('tags_cache').innerHTML;
		dataLine = ":@@:" + team + ":@@:" + filter + ":@@:1:@@:" + channels + ":@@:" + tags;
		if (ph.style.display != "block") {
			x_ajax_resetThreadList(dataLine, displayResetThreads);
		}
		else {
			submitsearch();
	}
}
}

function displayFilterMenu(div,filterdiv) {
	if (document.getElementById('pleasewait').style.visibility == "hidden") {
		document.getElementById(div).style.visibility='visible';
		if (filterdiv) {
			var divfilt = document.getElementById(filterdiv);
			divfilt.style.borderTop = "1px solid silver";
			divfilt.style.borderLeft = "1px solid silver";
			divfilt.style.borderRight = "1px solid silver";
			divfilt.style.marginLeft = "-1px";
			divfilt.style.marginRight = "-1px";
			divfilt.style.marginTop = "-3px";
		}
	}
}

function hideFilterMenu(div,filterdiv) {
	document.getElementById(div).style.visibility='hidden';
	if (filterdiv) {
		var divfilt = document.getElementById(filterdiv);
		divfilt.style.borderTop = "";
		divfilt.style.borderLeft = "";
		divfilt.style.borderRight = "";
		divfilt.style.marginLeft = "";
		divfilt.style.marginRight = "";
		divfilt.style.marginTop = "";
	}
}

function displayTeamName(data) {
	document.getElementById('teamName').innerHTML = data;
	document.getElementById('teamNameDiv').style.display = "inline-block";
}

function displayFilter_reload(filter) {
	if (document.getElementById) {
		if (filter == '') {
			SetCookie('threadFilter', '', -1);
		}
		else {
			SetCookie('threadFilter', filter, 365);
		}
		clearInterval(h);
		window.location.hash = "";
		window.location.reload();
	}
}

function team_in_pthread() {
	var dataline = "0";
	if (document.getElementById('displayTeaminPthread').checked) {
		dataline = "1";
	}
	x_ajax_team_in_pthread(dataline, reloadPage);
}

function displayunreadPthread() {
	var dataline = "0";
	if (document.getElementById('displayunreadPthread').checked) {
		dataline = "1";
	}
	x_ajax_displayunreadPthread(dataline, reloadPage);
}

function no_private_sticky() {
	var dataline = "0";
	if (document.getElementById('no_private_sticky').checked) {
		var dataline = "1";
	}
	x_ajax_no_private_sticky(dataline, reloadPage);
}

function changepage(page) {
	if (document.getElementById) {
		document.getElementById('newPostsStr').innerHTML = "";
		var parent = document.getElementById('parentC');
		parent.style.opacity = ".30";
		parent.style.filter = "alpha(opacity=30)";
		pleasewait();

		var filter = document.getElementById('filter').innerHTML;
		var channels = document.getElementById('chan_cache').innerHTML;
		var tags = document.getElementById('tags_cache').innerHTML;
		var team = document.getElementById('listthreadteam').innerHTML;
		var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:" + page + ":@@:" + channels + ":@@:" + tags;
		var ph = document.getElementById('searchForm');
		if (ph.style.display == "block") {
			var searchterm = document.getElementById('searchterm');
			var searchusername = document.getElementById('searchusername');
			var searchinthreadid = document.getElementById('searchinthreadid');
			var searchdatelimit = document.getElementById('searchdatelimit');
			if (searchterm.value != "" || searchusername.value != "") {
				clearInterval(t);
				clearInterval(m);
				var exprtype = "";
				if (document.getElementById('expression_exact').checked) {
					exprtype = "exact";
				}
				else if (document.getElementById('expression_all').checked) {
					exprtype = "all";
				}
				else if (document.getElementById('expression_one').checked) {
					exprtype = "one";
				}
				var dataLine = europlus(searchterm.value) + ':!@:' + europlus(searchusername.value) + ':!@:' + searchdatelimit.value + ':!@:' + searchinthreadid.value + ':!@:'  + exprtype + dataLine;
			}
		}
		document.getElementById('numpage_cache').innerHTML = page;
		scrolltoID('corner_left');
		x_ajax_resetThreadList(dataLine, displayResetThreads);
	}
}

function selectTeam(team) {
	document.getElementById('filterMenuTeam').style.visibility='hidden';
	document.getElementById('channelfilt').style.display = "none";
	document.getElementById('filter5').className= 'threadTMenu';
	document.getElementById('filter0').className= 'threadType';
	document.getElementById('filter1').className= 'threadTMenu';
	document.getElementById('filter2').className= 'threadType';
	document.getElementById('filter3').className= 'threadType';
	document.getElementById('filter4').className= 'threadType';
	document.getElementById('filter6').className= 'threadTMenuSel';

	if (team) {
		document.getElementById('NewThreadButton').style.display = "none";
		document.getElementById('teamName').innerHTML = document.getElementById('teamName' + team).innerHTML;
		document.getElementById('teamNameDiv').style.display = "inline-block";
	}
	else {
		document.getElementById('NewThreadButton').style.display = "inline-block";
		document.getElementById('teamName').innerHTML = "";
		document.getElementById('teamNameDiv').style.display = "none";
	}
	document.getElementById('listthreadteam').innerHTML = team;
	document.getElementById('filter').innerHTML = "teams";
	var channels = document.getElementById('chan_cache').innerHTML;
	var page = document.getElementById('numpage_cache').innerHTML;
	var tags = document.getElementById('tags_cache').innerHTML;
	var dataLine = ":@@:" + team + ":@@:teams:@@:" + page + ":@@:" + channels + ":@@:" + tags;
	var ph = document.getElementById('searchForm');
	clearInterval(h);

	if (team != "") {
		storedhash = "#threadlist/teams/" + team;
	}
	else {
		storedhash = "#threadlist/teams";
	}
	window.location.hash = storedhash;
	h = setInterval(function() {checkhash();}, 1000);

	var parent = document.getElementById('parentC');
	parent.style.opacity = ".30";
	parent.style.filter = "alpha(opacity=30)";
	if (ph.style.display == "block") {
		var form = document.forms["search"];
		var searchNodes = form.elements;
		if (searchNodes[0].value != "" || searchNodes[4].value != "" || searchNodes[5].value != "") {
			clearInterval(t);
			clearInterval(m);
			dataLine = searchNodes[0].value + ':!@:' + searchNodes[4].value + ':!@:' + searchNodes[6].value + ':!@:' + searchNodes[5].value + dataLine;
		}
	}
	x_ajax_resetThreadList(dataLine, displayResetThreads);
	
}

function showChannels() {
	if (document.getElementById) {
		var preview = document.getElementById('channelsWindow');

		if (preview.style.display == 'block') {
			preview.style.display = 'none';
		}
		else {
			preview.style.visibility='hidden';
			preview.style.display='block';
			var ref = document.getElementById('channelsAnchor');
			document.getElementById('chan_make_default').checked = false;
			var offTop = 0;
			var offLeft = 0;
			while( ref != null ){
				offTop += ref.offsetTop;
				offLeft += ref.offsetLeft;
				ref = ref.offsetParent;
			}
			preview.style.top = offTop + 20 + 'px';
			preview.style.left = document.getElementById('channelsAnchor').offsetWidth - preview.offsetWidth + offLeft + 'px';
			preview.style.zindex = '1001';
			preview.style.opacity = "0";
			preview.style.filter = "alpha(opacity=0)";
			preview.style.visibility='visible';
			fadeIn('channelsWindow',0);
		}
	}
}

function closeChannels() {
	if (document.getElementById) {
		document.getElementById('channelsWindow').style.display = 'none';
	}
}

function modifyChannelFilter(ID) {
	if (document.getElementById) {
		var selectedChannel = document.getElementById('channel' + ID);

		if (selectedChannel.checked == false) {
			selectedChannel.parentNode.parentNode.parentNode.parentNode.className = 'channelListingFiltered';
		}
		else {
			selectedChannel.parentNode.parentNode.parentNode.parentNode.className = 'channelListing';
		}
	}
	return true;
}

function modifyChannelFilterExclusive(ID) {
	if(document.getElementById) {
		form=document.forms["channelFilter"];
		channelNodes = form.elements;

		for(i=0;i<channelNodes.length;i++) {
			if (channelNodes[i].name != ID) {
				channelNodes[i].checked = false;
				modifyChannelFilter(channelNodes[i].name);
			}
			else {
				channelNodes[i].checked = true;
				modifyChannelFilter(channelNodes[i].name);
			}
		}
	}
}

function applyChannelFilter(ID) {
	if(document.getElementById) {
		var form = document.forms["channelFilter"];
		var channelNodes = form.elements;
		var retStr = '';
		var j = 0;
		for (i=0;i<channelNodes.length;i++) {
			if (channelNodes[i].checked == false) {
				retStr += channelNodes[i].name + ',';
				if (channelNodes[i].name != ID) {
					j ++;
			}
		}
		}
		var preview = document.getElementById('resetchan');
		if (j > 0) {
			preview.style.display = 'inline';
		}
		else {
			preview.style.display = 'none';
		}

		document.getElementById('numpage_cache').innerHTML = "1";
		var chan_cache = retStr;
		if (chan_cache == "") {
			chan_cache = "none";
		}
		document.getElementById('chan_cache').innerHTML = chan_cache;
		if (document.getElementById('chan_make_default').checked) {
		SetCookie('metaChannelFilter2', retStr, 365);
		}
		document.getElementById('channelsWindow').style.display = 'none';

		x_ajax_updateChannelsList(retStr, updateChannelsList);
	}
}

function viewOneChannel(ID) {
	if(document.getElementById) {
		modifyChannelFilterExclusive(ID);
		applyChannelFilter();
	}
}

function viewAllChannels(ID) {
	resetChannels(ID);

	if (GetCookie('metaChannelFilter2') != "") {
		var dlarray = GetCookie('metaChannelFilter2').split(',');
		for(i=0;i<dlarray.length;i++) {
			if (dlarray[i]) {
				document.getElementById('channel' + dlarray[i]).checked = false;
				document.getElementById('channel' + dlarray[i]).parentNode.parentNode.parentNode.parentNode.className = 'channelListingFiltered';
			}
		}
	}
	applyChannelFilter();
}

function resetChannels(ID) {
	if (document.getElementById) {
		form=document.forms["channelFilter"];
		channelNodes = form.elements;

		for(i=0;i<channelNodes.length;i++) {
			if (channelNodes[i].name != ID) {
				channelNodes[i].checked = true;
				modifyChannelFilter(channelNodes[i].name);
			}
			else {
				channelNodes[i].checked = false;
				modifyChannelFilter(channelNodes[i].name);
			}
		}
	}
}

function updateChannelsList(data) {
	if (document.getElementById) {
		document.getElementById('channelsAnchor').innerHTML = data;
		var parent = document.getElementById('parentC');
		parent.style.opacity = ".30";
		parent.style.filter = "alpha(opacity=30)";
		pleasewait();

		var filter = document.getElementById('filter').innerHTML;
		var channels = document.getElementById('chan_cache').innerHTML;
		var tags = document.getElementById('tags_cache').innerHTML;
		var team = document.getElementById('listthreadteam').innerHTML;
		var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:1" + ":@@:" + channels + ":@@:" + tags;
		var ph = document.getElementById('searchForm');
		if (ph.style.display == "block") {
			var form = document.forms["search"];
			var searchNodes = form.elements;
			if (searchNodes[0].value != "" || searchNodes[4].value != "" || searchNodes[5].value != "") {
				clearInterval(t);
				clearInterval(m);
				dataLine = searchNodes[0].value + ':!@:' + searchNodes[4].value + ':!@:' + searchNodes[6].value + ':!@:' + searchNodes[5].value + dataLine;
				if (searchNodes[2].checked) {
					x_ajax_resetThreadList(dataLine, displaySearchResults);
				}
				else {
					x_ajax_search_posts(dataLine, displaySearchPost);
			}
			}
			else {
				parent.style.opacity = "1";
				parent.style.filter = "alpha(opacity=100)";
			}
		}
		else {
			x_ajax_resetThreadList(dataLine, displayResetThreads);
	}
}
}

function refreshTags() {
	ajaxload_on();
	x_ajax_refreshTags(0,display_refreshTags);
}

function display_refreshTags(data) {
	if (data) {
		document.getElementById('show_tags').innerHTML = data;
	}
	ajaxload_off();
}

function removeTag(tagID) {
	var tags = document.getElementById('tags').value;
	var tagsArray = tags.split(',');
	var newtags = "";
	var virg = "";
	for (i=0;i<tagsArray.length;i++) {
		if (i != tagID) {
			newtags += virg + tagsArray[i];
			virg = ", ";
		}
	}
	document.getElementById('tags').value = newtags;
	x_ajax_verifytags(newtags,verifyTags);
}

function up_tag(tagID,type) {
	clearInterval(g);
	var uptag_cache = document.getElementById('uptag_cache');
	var cacheArray = uptag_cache.innerHTML.split(',');
	uptag_cache.innerHTML = "";
	var new_uptag = document.getElementById('tag_' + type + '_' + tagID);
	var fsize = new_uptag.style.fontSize;
	fsize = fsize.replace(/em/g, "") * 1;
	
	var new_uptag_cache = 'tag_' + type + '_' + tagID + "@" + fsize;
	if (fsize < 2) {
		fsize = fsize + 0.2;
		new_uptag.style.fontSize = fsize + "em";
	}
	for (i=0;i<cacheArray.length;i++) {
		if (cacheArray[i]) {
			var in_cacheArray = cacheArray[i].split('@');
			var down_tag = document.getElementById(in_cacheArray[0]);
			fsize = down_tag.style.fontSize.replace(/em/g, "") * 1;
			fsize = fsize - 0.05;
			if (fsize > in_cacheArray[1]) {
				new_uptag_cache = new_uptag_cache + "," + in_cacheArray[0] + "@" + in_cacheArray[1];
				down_tag.style.fontSize = fsize + "em";
			}
		}
	}
	uptag_cache.innerHTML = new_uptag_cache;
	g = setInterval(function() {update_up_tag();}, 10);
}

function update_up_tag() {
	clearInterval(g);
	var uptag_cache = document.getElementById('uptag_cache');
	var cacheArray = uptag_cache.innerHTML.split(',');
	uptag_cache.innerHTML = "";
	var new_uptag_cache = "";
	var in_cacheArray = "";
	var down_tag = "";
	for (i=0;i<cacheArray.length;i++) {
		if (i == 0) {
			in_cacheArray = cacheArray[i].split('@');
			down_tag = document.getElementById(in_cacheArray[0]);
			fsize = down_tag.style.fontSize.replace(/em/g, "") * 1;
			fsize = fsize + 0.2;
			new_uptag_cache = in_cacheArray[0] + "@" + in_cacheArray[1];
			if (fsize < 2) {
				down_tag.style.fontSize = fsize + "em";
			}
		}
		else if (cacheArray[i]) {
			in_cacheArray = cacheArray[i].split('@');
			in_cacheArray[1] = in_cacheArray[1] * 1;
			down_tag = document.getElementById(in_cacheArray[0]);
			fsize = down_tag.style.fontSize.replace(/em/g, "") * 1;
			fsize = fsize - 0.05;
			if (fsize > in_cacheArray[1]) {
				down_tag.style.fontSize = fsize + "em";
				new_uptag_cache = new_uptag_cache + "," + in_cacheArray[0] + "@" + in_cacheArray[1];
			}
		}
	}
	uptag_cache.innerHTML = new_uptag_cache;
	if (new_uptag_cache) {
		g = setInterval(function() {update_up_tag();}, 10);
	}
}

function down_tag_size() {
	clearInterval(g);
	var uptag_cache = document.getElementById('uptag_cache');
	var cacheArray = uptag_cache.innerHTML.split(',');
	uptag_cache.innerHTML = "";
	var new_uptag_cache = "";
	for (i=0;i<cacheArray.length;i++) {
			var in_cacheArray = cacheArray[i].split('@');
			var cache_size = in_cacheArray[1] * 1;
			var down_tag = document.getElementById(in_cacheArray[0]);
			fsize = down_tag.style.fontSize.replace(/em/g, "") * 1;
			fsize = fsize - 0.05;
			if (fsize > cache_size) {
				down_tag.style.fontSize = fsize + "em";
				if (i > 0) {
					new_uptag_cache = new_uptag_cache + "," + in_cacheArray[0] + "@" + in_cacheArray[1];
				}
				else {
					new_uptag_cache = in_cacheArray[0] + "@" + in_cacheArray[1];
			}
			}

	}
	uptag_cache.innerHTML = new_uptag_cache;
	if (new_uptag_cache) {
		g = setInterval(function() {down_tag_size();}, 10);
	}
}

function searchTag(id) {
	var tag = document.getElementById('searchTag'+ id).value;
	if (tag.length > 2) {
		var dataline = europlus(tag) + "::@@st@@::" + id;
		x_ajax_searchTag(dataline, displaysearchTag);
	}
	else {
		var cache = document.getElementById('searchTag_cache');
		var cacheArray = cache.innerHTML.split(',');
		cache.innerHTML = "";
		for (i=0;i<cacheArray.length;i++) {
			if (cacheArray[i]) {
				var in_cacheArray = cacheArray[i].split('@');
				document.getElementById(in_cacheArray[0]).style.fontSize = in_cacheArray[1];
				document.getElementById(in_cacheArray[0]).style.color = "black";
				document.getElementById(in_cacheArray[0]).style.fontWeight = "";
			}
		}
	}
}

function displaysearchTag(dataline) {
	var dlarray = dataline.split('@@:st:@@');
	var cache = document.getElementById('searchTag_cache');
	var cacheArray = cache.innerHTML.split(',');
	cache.innerHTML = "";
	for (i=0;i<cacheArray.length;i++) {
		if (cacheArray[i]) {
			var in_cacheArray = cacheArray[i].split('@');
			document.getElementById(in_cacheArray[0]).style.fontSize = in_cacheArray[1];
			document.getElementById(in_cacheArray[0]).style.color = "black";
			document.getElementById(in_cacheArray[0]).style.fontWeight = "";
		}
	}

	var in_cache = "";
	var tagsArray = dlarray[0].split(',');
	for (i=0;i<tagsArray.length;i++) {
		if (tagsArray[i]) {
			in_cache = in_cache + 'tag_' + dlarray[1] + "_" + tagsArray[i] + '@' + document.getElementById('tag_' + dlarray[1] + "_" + tagsArray[i]).style.fontSize + ",";
			document.getElementById('tag_' + dlarray[1] + "_" + tagsArray[i]).style.fontSize = "16px";
			document.getElementById('tag_' + dlarray[1] + "_" + tagsArray[i]).style.color = "green";
			document.getElementById('tag_' + dlarray[1] + "_" + tagsArray[i]).style.fontWeight = "bold";
		}
	}
	cache.innerHTML = in_cache;
}

function show_tags() {
	if (document.getElementById) {
		var erp = document.getElementById('show_tags');		
		erp.style.display = "block";
//		var xscroll = 0;
		var yscroll = 0;
		var errorPaneH = erp.offsetHeight;
		if (window.innerWidth) {
			erp.style.width = window.innerWidth + "px";
			yscroll = (window.innerHeight - errorPaneH) / 2;
		}
		else if (document.documentElement.clientWidth) {
			erp.style.width = document.documentElement.clientWidth + "px";
			yscroll = (document.documentElement.clientHeight - errorPaneH) / 2;
		}
		erp.style.top = yscroll + "px";

		var contentdiv = document.getElementById('page');
		contentdiv.style.opacity = ".15";
		contentdiv.style.filter = "alpha(opacity=15)";

		erp.style.visibility = "visible";
		document.getElementById('show_tags_button').style.display = "block";
		
		document.onkeydown = hide_tags;
	}

}

function hide_tags(e) {
	if (document.getElementById('show_tags').style.display == "block") {
		if (e) {
		TouchKeyDown = (window.event) ? event.keyCode : e.keyCode;
			if (TouchKeyDown == 27) {
			hide_tags_click();
		}
	}
}
}

function hide_tags_click() {
	if (document.getElementById) {
		var erp = document.getElementById('show_tags');
		erp.style.visibility = "hidden";
		erp.style.display = "none";
		var contentdiv = document.getElementById('page');
		contentdiv.style.opacity = "1";
		contentdiv.style.filter = "alpha(opacity=100)";
		document.getElementById('show_tags_button').style.display = "none";
		
		document.onkeydown = "";
	}
}

function view_onetag(tagID,type) {
	var tag = "";
	if (type) {
		tag = document.getElementById('tag_'+ type + '_' + tagID).title;
	}
	else {
		tag = document.getElementById('tag_'+ tagID).innerHTML;
	}
	document.getElementById('tags_display').style.display = "inline-table";
	var tag_cache = document.getElementById('tags_cache');
	var tag_list = document.getElementById('tags_list');
	var tag_exist = false;
	if (tag_cache.innerHTML != "") {
		var tag_array = tag_cache.innerHTML.split(',');
		for (i=0;i<tag_array.length;i++) {
			if (tag_array[i] == tag) {
				tag_exist = true;
			}
		}
		if (!tag_exist) {
			tag_cache.innerHTML = tag_cache.innerHTML + "," + tag;
			tag_list.innerHTML = tag_list.innerHTML + "<div id='buttag_" + tagID + "' class='button_tag'><span id='seltag_" + tagID + "' class='selected_tag'>" + tag + "</span><span class='deleteButton' onclick=\"remove_onetag('" + tagID + "');\">x</span></div>";
			tag = tag_cache.innerHTML;
		}
	}
	else {
		tag_cache.innerHTML = tag;
		tag_list.innerHTML = "<div id='buttag_" + tagID + "' class='button_tag'><span id='seltag_" + tagID + "' class='selected_tag'>" + tag + "</span><span class='deleteButton' onclick=\"remove_onetag('" + tagID + "');\">x</span></div>";
	}
	if (!tag_exist) {
		var parent = document.getElementById('parentC');
		parent.style.opacity = ".30";
		parent.style.filter = "alpha(opacity=30)";
		hide_tags();
		pleasewait();
		var filter = document.getElementById('filter').innerHTML;
		var channels = document.getElementById('chan_cache').innerHTML;
		var team = document.getElementById('listthreadteam').innerHTML;
		tag = europlus(tag);
		var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:1" + ":@@:" + channels + ":@@:" + tag;
		var ph = document.getElementById('searchForm');
		if (ph.style.display == "block") {
			var form = document.forms["search"];
			var searchNodes = form.elements;
			if (searchNodes[0].value != "" || searchNodes[4].value != "" || searchNodes[5].value != "") {
				clearInterval(t);
				clearInterval(m);
				dataLine = searchNodes[0].value + ':!@:' + searchNodes[4].value + ':!@:' + searchNodes[6].value + ':!@:' + searchNodes[5].value + dataLine;
				if (searchNodes[2].checked) {
					x_ajax_resetThreadList(dataLine, displaySearchResults);
				}
				else {
					x_ajax_search_posts(dataLine, displaySearchPost);
			}
			}
			else {
				parent.style.opacity = "1";
				parent.style.filter = "alpha(opacity=100)";
			}
		}
		else {
			x_ajax_resetThreadList(dataLine, displayResetThreads);
	}
}
}

function remove_onetag(tagID) {
	var tag = document.getElementById('seltag_'+ tagID).innerHTML;
	var tag_cache = document.getElementById('tags_cache');
	
	var tag_remove = document.getElementById('buttag_' + tagID);
	tag_remove.parentNode.removeChild(tag_remove );
	
	var tag_array = tag_cache.innerHTML.split(',');
	var tag1 = "";
	for (i=0;i<tag_array.length;i++) {
		if (tag_array[i] != tag) {
			if (tag1) {
				tag1 = tag1 = ",";
			}
			tag1 = tag1 + tag_array[i];
		}
	}
	tag_cache.innerHTML = tag1;

	if (!tag1) {
		document.getElementById('tags_display').style.display = "none";
	}
	var parent = document.getElementById('parentC');
	parent.style.opacity = ".30";
	parent.style.filter = "alpha(opacity=30)";
	pleasewait();
	var filter = document.getElementById('filter').innerHTML;
	var channels = document.getElementById('chan_cache').innerHTML;
	var team = document.getElementById('listthreadteam').innerHTML;
	var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:1" + ":@@:" + channels + ":@@:" + tag1;
	var ph = document.getElementById('searchForm');
	if (ph.style.display == "block") {
			var form = document.forms["search"];
			var searchNodes = form.elements;
			if (searchNodes[0].value != "" || searchNodes[4].value != "" || searchNodes[5].value != "") {
				clearInterval(t);
				clearInterval(m);
				dataLine = searchNodes[0].value + ':!@:' + searchNodes[4].value + ':!@:' + searchNodes[6].value + ':!@:' + searchNodes[5].value + dataLine;
				if (searchNodes[2].checked) {
					x_ajax_resetThreadList(dataLine, displaySearchResults);
				}
				else {
					x_ajax_search_posts(dataLine, displaySearchPost);
			}
			}
			else {
				parent.style.opacity = "1";
				parent.style.filter = "alpha(opacity=100)";
			}
	}
	else {
		x_ajax_resetThreadList(dataLine, displayResetThreads);
	}
}
		
function remove_alltags() {
	var tag_cache = document.getElementById('tags_cache');
	var tag_list = document.getElementById('tags_list');
	tag_cache.innerHTML = "";
	tag_list.innerHTML = "";
	document.getElementById('tags_display').style.display = "none";
	var parent = document.getElementById('parentC');
	parent.style.opacity = ".30";
	parent.style.filter = "alpha(opacity=30)";
	pleasewait();
	var filter = document.getElementById('filter').innerHTML;
	var channels = document.getElementById('chan_cache').innerHTML;
	var team = document.getElementById('listthreadteam').innerHTML;
	var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:1" + ":@@:" + channels + ":@@:";
	var ph = document.getElementById('searchForm');
	if (ph.style.display == "block") {
			var form = document.forms["search"];
			var searchNodes = form.elements;
			if (searchNodes[0].value != "" || searchNodes[4].value != "" || searchNodes[5].value != "") {
				clearInterval(t);
				clearInterval(m);
				dataLine = searchNodes[0].value + ':!@:' + searchNodes[4].value + ':!@:' + searchNodes[6].value + ':!@:' + searchNodes[5].value + dataLine;
				if (searchNodes[2].checked) {
					x_ajax_resetThreadList(dataLine, displaySearchResults);
				}
				else {
					x_ajax_search_posts(dataLine, displaySearchPost);
			}
			}
			else {
				parent.style.opacity = "1";
				parent.style.filter = "alpha(opacity=100)";
			}
	}
	else {
		x_ajax_resetThreadList(dataLine, displayResetThreads);
	}
}

function input_tag() {
	var tag = document.getElementById('inputTag').value;
	tag = tag.replace(/,/g, "");
	tag = tag.replace(/\"/g, "");
	document.getElementById('inputTag').value = tag;
	if (tag) {
		var tag_cache = document.getElementById('t_tags_cache');
		var tag_exist = false;
		if (tag_cache.value != "") {
			var tag_array = tag_cache.value.split(',');
			for (i=0;i<tag_array.length;i++) {
				if (tag_array[i] == tag) {
					tag_exist = true;
					document.getElementById('t_create_tag').style.display = "none";
					document.getElementById('t_add_tag').style.display = "none";
					document.getElementById('inputTag').style.color = "red";
				}
			}
		}
		if (!tag_exist) {
			tag = europlus(tag);
			x_ajax_inputTag(tag, displayinputTag);
		}
	}
	else {
		document.getElementById('t_create_tag').style.display = "none";
		document.getElementById('t_add_tag').style.display = "none";
	}
}

function inputselect_tag() {
	var tag = document.getElementById('inputSelectTag').value;
	document.getElementById('inputTag').value = tag;
	document.getElementById('inputTag').focus();
	input_tag();
}

function displayinputTag(dataline) {
	var dlarray = dataline.split('@@:t:@@');

	if (dlarray[0] == "exist") {
		document.getElementById('t_create_tag').style.display = "none";
		document.getElementById('t_add_tag').style.display = "none";
		document.getElementById('inputTag').style.color = "red";
	}
	else if (dlarray[0] == "add") {
		document.getElementById('t_create_tag').style.display = "none";
		document.getElementById('t_add_tag').style.display = "inline";
		document.getElementById('inputTag').style.color = "green";
	}
	else {
		document.getElementById('t_create_tag').style.display = "inline";
		document.getElementById('t_add_tag').style.display = "none";
		document.getElementById('inputTag').style.color = "black";
	}
	if (dlarray[1]) {
		document.getElementById('inputSelectTag').style.display = "inline-block";
		document.getElementById('inputSelectTag').innerHTML = dlarray[1];
		document.getElementById('inputSelectTag').size = dlarray[2];
	}
	else {
		document.getElementById('inputSelectTag').style.display = "none";
		document.getElementById('inputSelectTag').innerHTML = "";
	}
}

function t_add_tag() {
	var tag = document.getElementById('inputTag').value;
	tag = europlus(tag);

	if (tag) {
		x_ajax_addTag(tag, displayaddedTag);
	}
}

function displayaddedTag(tagID) {
	var tag = document.getElementById('inputTag').value;
	document.getElementById('inputTag').value = "";
	document.getElementById('inputSelectTag').style.display = "none";
	document.getElementById('inputSelectTag').innerHTML = "";
	document.getElementById('t_create_tag').style.display = "none";
	document.getElementById('t_add_tag').style.display = "none";

	var tag_cache = document.getElementById('t_tags_cache');
	var tag_list = document.getElementById('t_tags_list');
	var tag_exist = false;
	if (tag_cache.value != "") {
		var tag_array = tag_cache.value.split(',');
		for (i=0;i<tag_array.length;i++) {
			if (tag_array[i] == tag) {
				tag_exist = true;
			}
		}
		if (!tag_exist) {
			tag_cache.value = tag_cache.value + "," + tag;
			tag_list.innerHTML = tag_list.innerHTML + "<div id='t_buttag_" + tagID + "' class='button_tag'><span id='t_seltag_" + tagID + "' class='selected_tag'>" + tag + "</span><span class='deleteButton' onclick=\"t_remove_onetag('" + tagID + "');\">x</span></div>";
			tag = tag_cache.value;
		}
	}
	else {
		tag_cache.value = tag;
		tag_list.innerHTML = "<div id='t_buttag_" + tagID + "' class='button_tag'><span id='t_seltag_" + tagID + "' class='selected_tag'>" + tag + "</span><span class='deleteButton' onclick=\"t_remove_onetag('" + tagID + "');\">x</span></div>";
	}
}

function t_remove_onetag(tagID) {
	var tag = document.getElementById('t_seltag_'+ tagID).innerHTML;
	var tag_cache = document.getElementById('t_tags_cache');
	
	var tag_remove = document.getElementById('t_buttag_' + tagID);
	tag_remove.parentNode.removeChild(tag_remove );
	
	var tag_array = tag_cache.value.split(',');
	var tag1 = "";
	for (i=0;i<tag_array.length;i++) {
		if (tag_array[i] != tag) {
			if (tag1) {
				tag1 = tag1 + ",";
			}
			tag1 = tag1 + tag_array[i];
		}
	}
	tag_cache.value = tag1;
}

function showQQButton(element, buttonID) {
	if(document.getElementById) {
		button = document.getElementById(buttonID);
		if (button.style.visibility == 'hidden') {
			button.style.visibility = 'visible';		
			fadeIn(buttonID,0);
		}
	}
}

function hideQQButton(buttonID) {
	if(document.getElementById) {
		button = document.getElementById(buttonID);
		button.style.visibility='hidden';
	}
}

function toggleIndicator() {
	if(document.getElementById) {
		var i = document.getElementById('indicator');
		if (i.style.display == 'none' || i.style.display == '') {
			i.style.display = 'block';
		}
		else {
			i.style.display = 'none';
	}
}
}

function displayNumPost(dataline) {	
	if (document.getElementById) {
		var dlarray = dataline.split('::arrdlm::');
		document.getElementById('newPostsStr').innerHTML = dlarray[2];
	}
}

function pleasewait() {
	if (document.getElementById) {
		clearTimeout(w);
		document.getElementById('waitToLong').style.display = "none";
		document.getElementById('waitToLong').style.visibility = "hidden";
		var anchor = document.getElementById('pleasewait2');
		var anchor1 = document.getElementById('pleasewait');
		anchor.style.display = "block";
		var xscroll = 0;
		var yscroll = 0;
		var anchorW = anchor.offsetWidth;
		var anchorH = anchor.offsetHeight;
		if (window.innerWidth) {
			xscroll = (window.innerWidth - anchorW) / 2;
			yscroll = (window.innerHeight - anchorH) / 2;
		}
		else if (document.documentElement.clientWidth) {
			xscroll = (document.documentElement.clientWidth - anchorW) / 2;
			yscroll = (document.documentElement.clientHeight - anchorH) / 2;
		}
		var xscroll1 = xscroll - 30;
		var yscroll1 = yscroll - 30;
		anchor.style.left = xscroll + "px";
		anchor.style.top = yscroll + "px";
		anchor1.style.height = anchorH + 40 + "px";
		anchor1.style.left = xscroll1 + "px";
		anchor1.style.top = yscroll1 + "px";
		anchor1.style.display = "block";
		anchor.style.visibility = "visible";
		anchor1.style.visibility = "visible";
		
		w = setTimeout(function() {waitToLong();}, 40000);
	}
}

function pleasewait_off() {
	clearTimeout(w);
	document.getElementById('pleasewait2').style.display = "none";
	document.getElementById('pleasewait2').style.visibility = "hidden";
	document.getElementById('pleasewait').style.display = "none";
	document.getElementById('pleasewait').style.visibility = "hidden";
	document.getElementById('waitToLong').style.display = "none";
	document.getElementById('waitToLong').style.visibility = "hidden";
}

function waitToLong() {
	if (document.getElementById('pleasewait2').style.display != "none") {
		var anchor = document.getElementById('waitToLong');
		anchor.style.display = "block";
		var xscroll = 0;
		var yscroll = 0;
		var anchorW = anchor.offsetWidth;
		var anchorH = anchor.offsetHeight;
		if (window.innerWidth) {
			xscroll = (window.innerWidth - anchorW) / 2;
			yscroll = (window.innerHeight - anchorH) / 2;
		}
		else if (document.documentElement.clientWidth) {
			xscroll = (document.documentElement.clientWidth - anchorW) / 2;
			yscroll = (document.documentElement.clientHeight - anchorH) / 2;
		}
		anchor.style.left = xscroll + "px";
		anchor.style.top = yscroll + "px";
		anchor.style.visibility = "visible";
	}
}

function waitMore() {
	w = setTimeout(function() {waitToLong();}, 40000);
	document.getElementById('waitToLong').style.display = "none";
	document.getElementById('waitToLong').style.visibility = "hidden";
}

function loadavg() {
	x_ajax_loadavg(0, displayloadavg);
}

function displayloadavg(data) {
	if (document.getElementById) {
		document.getElementById('loadavg').innerHTML = data;
	}
}

function emptymain(ID) {
	if (document.getElementById) {
		var anchorthread = document.getElementById('anchorthread');
		clearInterval(h);
		clearTimeout(ppt);
		document.getElementById('user_profile').style.display = "none";
		document.getElementById('user_profile').innerHTML = "";
		if (document.getElementById('thread').innerHTML != "") {
			document.getElementById('thread').style.display = "block";
			document.title = document.getElementById('titlecache').innerHTML;
			window.location.hash=anchorthread.innerHTML;
			storedhash = window.location.hash;
			anchorthread.innerHTML = "";
			if (textareaCache) {
				document.getElementById('postArea').value = textareaCache;
			}
		}
		else {
			if (document.getElementById('facebook_like')) {
			document.getElementById('facebook_like').innerHTML = document.getElementById('fb_like_cache').innerHTML;
			document.getElementById('fb_like_cache').innerHTML = "";
			}
			textareaCache = "";
			if (document.getElementById('parentC').innerHTML == "") {
				pleasewait();
				threadUpdate();
				refreshTags();
			}
			else  {
				document.getElementById('threadlist').style.display = "block";
			}
			document.title = titleSite;
			titleHolder = document.title;
			if (!document.getElementById('BlogList')) {
			if (document.getElementById('searchForm')) {
				var ph = document.getElementById('searchForm');
				if (ph.style.display == "block") {
					retrievesearch();
			}
			}
			if (anchorthread.innerHTML) {
				window.location.hash=anchorthread.innerHTML;
				storedhash = window.location.hash;
				anchorthread.innerHTML = "";
			}
			else {
				var filter = document.getElementById('filter').innerHTML;
				storedhash = "#threadlist";
				if (filter) {
					filter = "/" + filter;
					storedhash = "#threadlist" + filter;
				}
				window.location.hash = storedhash;
			}
		}
			else {
				document.title = document.getElementById('titlecache').innerHTML;
				window.location.hash=anchorthread.innerHTML;
				storedhash = window.location.hash;
				anchorthread.innerHTML = "";
			}
		}
		if (postoscroll) {
			window.scrollTo(0,postoscroll);
			postoscroll = "";
		}

		h = setInterval(function() {checkhash();}, 1000);
	}
}

function emptymain2(ID,user,chan,filter,searchinthread) {
	if (document.getElementById) {
		clearInterval(h);
		clearTimeout(ppt);
		clearTimeout(p);
		var ph = "";

		document.getElementById('user_profile').style.display = "none";
		document.getElementById('user_profile').innerHTML = "";

		var threadlistempty = false;
		storedhash = "#threadlist";
		if (!filter) {
			filter = document.getElementById('filter').innerHTML;
		}
		storedhash = "#threadlist/" + filter;
		if (filter == "teams") {
			if (document.getElementById('selectteam').value) {
				storedhash += "/" + escape(document.getElementById('selectteam').value);
			}
		}
		window.location.hash = storedhash;

		if (!document.getElementById('parentC')) {
			window.location.href= window.location.href;
		}
		document.getElementById('thread').style.display = "none";
		document.getElementById('thread').innerHTML = "";
		if (document.getElementById('parentC').innerHTML == "") {
			pleasewait();
			threadlistempty = true;
			displayFilter(filter);
		}
		else  {
			document.getElementById('threadlist').style.display = "block";
		}
		main_is_threadlist();
		h = setInterval(function() {checkhash();}, 1000);

		if (chan) {
			modifyChannelFilterExclusive(chan);
			applyChannelFilter();
		}
		else if (searchinthread) {
			ph = document.getElementById('searchForm');
			if (ph.style.display == "none") {
				ph.style.opacity=1;
				ph.style.filter = "alpha(opacity=100)";

				ph.style.display='block';

				document.getElementById('chan_cache_search').innerHTML = document.getElementById('chan_cache').innerHTML;
				document.getElementById('channelsAnchor_cache').innerHTML = document.getElementById('channelsAnchor').innerHTML;
				document.getElementById('channelsWindow_cache').innerHTML = document.getElementById('channelsWindow').innerHTML;
				document.getElementById('span_chan_make_default').style.display = "none";
				document.getElementById('numpage_cache').innerHTML = "1";
				document.getElementById('searchbtnop').style.display = "none";
				document.getElementById('searchbtncl').style.display = "block";
				document.getElementById('parentC').innerHTML = br;
				document.getElementById('pagesListStr').innerHTML = "";
				document.getElementById('pagesListStrT').innerHTML = "";
				document.getElementById('newPostsStr').innerHTML = "";
			}
			document.getElementById('searchterm').focus();

			var form = document.forms["search"];
			var search = form.elements;
			search[0].value = "";
			search[3].checked = true;
			search[7].value = "";
			search[8].value = searchinthread;
		}
		else if (document.getElementById('searchForm')) {
			ph = document.getElementById('searchForm');
			if (ph.style.display == "block") {
				retrievesearch();
				}
			else if (!threadlistempty) {
				x_ajax_refreshUnreadP(ID, refreshUnread);
				x_ajax_usertotalpost(0, usertotalpost);
			}
		}
	}
}

function usertotalpost(dataline) {
	var dlarray = dataline.split('::@totp@::');
	var old_totalpost = document.getElementById('usertotalpost');
	if (old_totalpost.className != dlarray[0]) {
		ajaxload_on();
		var page = document.getElementById('numpage_cache').innerHTML;
		var filter = document.getElementById('filter').innerHTML;
		var channels = document.getElementById('chan_cache').innerHTML;
		var team = document.getElementById('listthreadteam').innerHTML;
		var tags = document.getElementById('tags_cache').innerHTML;

		var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:" + page + ":@@:" + channels + ":@@:" + tags;
		x_ajax_resetThreadList(dataLine, displayResetThreads);
	}
	else {
		clearInterval(t);
		clearInterval(m);
		updateLastPostMinutes();
		threadUpdate();
	}
	old_totalpost.className = dlarray[0];
}

function emptymain3() {
	if (document.getElementById) {
		var thread = document.getElementById('thread');
		var threadlist = document.getElementById('threadlist');
		var parent = document.getElementById('parentC');
		clearTimeout(p);
		clearTimeout(ppt);
			document.getElementById('anchorthread').innerHTML = "";

		thread.style.display = "none";
		threadlist.style.display = "block";
		thread.innerHTML = "";
		parent.style.opacity = "1.00";
		parent.style.filter = "alpha(opacity=100)";
		dataLine = document.getElementById('blogcache').innerHTML;
		var anchorthread = document.getElementById('anchorthread2');
		window.location.hash=anchorthread.innerHTML;
		anchorthread.innerHTML = "";
		document.getElementById('blog_tab').innerHTML = "Blog";
		document.title = titleSite;
		titleHolder = document.title;
		blogautorefresh();
		
		x_ajax_resetblogCore(dataLine, displayblogCore);
	}
}

function refreshUnread(dataline) {	
	if (document.getElementById) {
		clearInterval(t);
		clearInterval(m);

		var dlarray = dataline.split('::arrudlm::');
		document.getElementById('timestamp').className = dlarray[3];
		var num_newposts_old = 0;
		if (document.getElementById('numnewPosts' + dlarray[0])) {
			num_newposts_old = document.getElementById('numnewPosts' + dlarray[0]).innerHTML;
		}
		var newPosts = document.getElementById('newPosts' + dlarray[0]);
		newPosts.innerHTML = dlarray[1];
		var numPosts = document.getElementById('numPosts' + dlarray[0]);
		numPosts.innerHTML = dlarray[2];
		var newPosts_separator = document.getElementById('newPosts_separator' + dlarray[0]);
		if (dlarray[1] && dlarray[2]) {
			newPosts_separator.style.display = "inline";
		}
		else {
			newPosts_separator.style.display = "none";
		}
		var num_newposts_new = 0;
		if (document.getElementById('numnewPosts' + dlarray[0])) {
			num_newposts_new = document.getElementById('numnewPosts' + dlarray[0]).innerHTML;
		}
		var numid = document.getElementById('numpostu');
		var num = numid.innerHTML * 1;
		num = num - (num_newposts_old - num_newposts_new);
		if (num == 0) {
			num = "";
		}
		numid.innerHTML = num;
		verify_newPostsStr();
		if (num_newposts_new == 0 && num_newposts_old > 0) {
			remove_markunread(dlarray[0]);
		}
		else if (num_newposts_new > 0 && num_newposts_old == 0) {
			var listnewPostsStr = document.getElementById('listnewPostsStr');
			if (listnewPostsStr.innerHTML) {
				listnewPostsStr.innerHTML += "," + dlarray[0];
			}
			else {
				listnewPostsStr.innerHTML = dlarray[0];
			}
		}
		var thread = document.getElementById('newPostsToggle' + dlarray[0]);
		if (num_newposts_new == 0) {
				thread.className = 'noNewPosts';
		}
		else {
			thread.className = '';
		}
	}
}

function remove_markunread(tid) {
	if (document.getElementById) {
		var listpost = "";
		var dlarray = document.getElementById('listnewPostsStr').innerHTML.split(",");
		var i = 0;
		var virg = "";
		while (dlarray[i]) {
			if (dlarray[i] != tid) {
				listpost += virg + dlarray[i];
				virg = ",";
			}
			i = i + 1;
		}
		document.getElementById('listnewPostsStr').innerHTML = listpost;
	}
}

function emptymainThread(threadID,sl,page,islive,post,anchor) {
	if (document.getElementById) {
		clearTimeout(ppt);
		clearTimeout(p);
		clearInterval(t);
		clearInterval(m);
		clearInterval(b);
		cacheScroll('2');
		var filter = document.getElementById('filter').innerHTML;
		var dataLine = threadID + ':!@:' + sl + ':!@:' + page + ':!@:' + islive + ':!@:' + post + ':!@:' + filter;
		var main = document.getElementById('main');

		if (anchor > 1) {
			document.getElementById('anchorthread2').innerHTML = window.location.hash;
		}
		closelayer();
		if (document.getElementById('quickReply')) {
			document.getElementById('quickReply').innerHTML = "";
		}
		main.style.opacity = ".30";
		main.style.filter = "alpha(opacity=30)";

		pleasewait();
		document.getElementById('thread').style.display = "block";
		x_ajax_g_reply(dataLine, displayThread);
	}
}

function gotopost(postID,anchor) {
	var dataLine = postID + ':!p@:' + anchor;
	x_ajax_gotopost(dataLine, gotopostthread);
}

function gotopostthread(dataLine) {
	pleasewait();
	var filter = document.getElementById('filter').innerHTML;
	dataLine += ':!@:' + filter;
	x_ajax_g_reply(dataLine, displayThread);
}

function emptymainThreadPage(threadID,sl,page,islive,post) {
	if (document.getElementById) {
		var filter = document.getElementById('filter').innerHTML;
		var dataLine = threadID + ':!@:' + sl + ':!@:' + page + ':!@:' + islive + ':!@:' + post + ':!@:' + filter;
		clearTimeout(p);
		clearTimeout(ppt);
		var main = document.getElementById('main');
		main.style.opacity = ".30";
		main.style.filter = "alpha(opacity=30)";

		pleasewait();

		x_ajax_g_reply(dataLine, displayThread);
	}
}

function emptymainThread2(dataLine) {
	if (document.getElementById) {
		textareaCache = "";

		var dataLineArray = dataLine.split(':!@:');
		if (dataLineArray[1] == "reload") {
			document.getElementById('postArea').value = "";
			window.location.href = dataLineArray[0];
		}
		else {
		clearTimeout(p);
		clearTimeout(ppt);
		var filter = document.getElementById('filter').innerHTML;
		dataLine += ':!@:' + filter;
		x_ajax_g_reply(dataLine, displayThread);
	}
}
}

function displaylist(dataline) {
	if (document.getElementById) {
		var dataLineArray = dataline.split('::cur@lo::');
		line = document.getElementById('main').innerHTML = dataLineArray[0];
		if (dataLineArray[3]) {
			viewOneChannel(dataLineArray[3]);
		}
		runOnce(dataLineArray[1]);
		document.getElementById('listnewPostsStr').innerHTML = dataLineArray[2];
	}
}

function displayThread(dataline) {
	if (document.getElementById) {

		pleasewait_off();
		if (document.getElementById('facebook_like')) {
		document.getElementById('fb_like_cache').innerHTML = document.getElementById('facebook_like').innerHTML;
		document.getElementById('facebook_like').innerHTML = "";
		}
		var dataLineArray = dataline.split('::cur@lo::');
		var main = document.getElementById('main');
		var thread = document.getElementById('thread');
		thread.innerHTML = dataLineArray[0];
		document.getElementById('threadlist').style.display = "none";
		document.getElementById('user_profile').style.display = "none";
		thread.style.display = "block";
		main.style.opacity = "1.00";
		main.style.filter = "alpha(opacity=100)";

		closelayer();
		if (dataLineArray[6] == "1") {
			document.getElementById('blog_tab').innerHTML = "<span onclick=\"emptymain3("+dataLineArray[1]+","+dataLineArray[5]+"); return false;\" style='cursor:pointer;'>" + b6_blog_tab + "</span>";
		}
		else {
			document.getElementById('forum_tab').innerHTML = "<span onclick=\"emptymain2("+dataLineArray[1]+","+dataLineArray[5]+"); return false;\" style='cursor:pointer;'>" + b6_forum_tab + "</span>";
		}
		clearInterval(h);

		if (dataLineArray[2] != "undefined" && dataLineArray[2] != "") {
			storedhash="#thread/" + dataLineArray[1] + "/" + dataLineArray[7] + "/" + dataLineArray[2];
			window.location.hash = storedhash;
			if (!dataLineArray[8] || dataLineArray[8] == "undefined") {
			scrolltoID('postid' + dataLineArray[2]);
				setTimeout(function() {scrolltoID('postid' + dataLineArray[2]);}, 1500);
			}
		}
		else {
			storedhash="#thread/" + dataLineArray[1] + "/" + dataLineArray[7];
			window.location.hash = storedhash;
			if (postoscroll) {
				window.scrollTo(0,postoscroll);
				postoscroll = "";
			}
			else {
				scrolltoID('bottom_page_button');
		}
		}
		if (dataLineArray[8] != "undefined") {
			scrolltoID('anchor_' + dataLineArray[8]);
			setTimeout(function() {scrolltoID('anchor_' + dataLineArray[8]);}, 1500);
		}
		pc = "";
		if (dataLineArray[7]) {
			pc = " - " + b6_page + dataLineArray[7];
		}
		document.title = dataLineArray[4] + pc + " - " + b6_site;
		document.description = dataLineArray[9];
		document.getElementById('meta_description').innerHTML = dataLineArray[9];
//		document.description = "TEST";
		titleHolder = document.title;
		
		h = setInterval(function() {checkhash();}, 1000);


		if (dataLineArray[3] == "1") {
			runThreadWatcherOnce(dataLineArray[1]);
		}
		if (textareaCache) {
			document.getElementById('postArea').value = textareaCache;
		}

		if (document.getElementById('google_plusone'))
			gapi.plusone.go('google_plusone');
	}
}

function threadanchor(data) {
	if (document.getElementById) {
		window.location.hash = data;
	}
}

function userprofile(user,anchor,ID) {
	if (document.getElementById) {
		ajaxload_on();
		pleasewait();
		cacheScroll('1');
		if (document.getElementById('thread').style.display == "block" || document.getElementById('BlogList')) {
			document.getElementById('anchorthread').innerHTML = window.location.hash;
			document.getElementById('titlecache').innerHTML = document.title;
		}
		else {
			if (document.getElementById('facebook_like')) {
			document.getElementById('fb_like_cache').innerHTML = document.getElementById('facebook_like').innerHTML;
			document.getElementById('facebook_like').innerHTML = "";
			}
		}
		userID = "";
		if (ID) {
			userID = ID;
		}
		dataline = user + "::@@user@@::" + userID;
		x_ajax_userprofile(dataline, userprofiledisplay);
	}
}

function userprofile2(userid) {
	if (document.getElementById) {
		if (userid != "") {
			var user = "::@@user@@::" + userid;
			ajaxload_on();
			x_ajax_userprofile(user, userprofiledisplay);
		}
	}
}

function userprofile4(div) {
	if (document.getElementById(div)) {
		ajaxload_on();
		var dataline = "::@@user@@::" + document.getElementById(div).value;
		x_ajax_userprofile(dataline, userprofiledisplay);
	}
}

function userprofiledisplay(dataline) {
	if (document.getElementById) {
		var dataLineArray = dataline.split('::cur@lo::');

		document.getElementById('user_profile').innerHTML = dataLineArray[0];
		document.getElementById('threadlist').style.display = "none";
		document.getElementById('thread').style.display = "none";
		document.getElementById('user_profile').style.display = "block";
		pleasewait_off();
		closelayer();
		ajaxload_off();
		clearInterval(h);
		storedhash="#user/" + dataLineArray[1];
		window.location.hash = storedhash;
		document.title = b6_profil + dataLineArray[2] + " - " + b6_site;
		titleHolder = document.title;
		scrolltoID('corner_left');
		h = setInterval(function() {checkhash();}, 1000);
	}
}

function previewPost(type) {
	var typepost = "";
	if (type == '2') {
		typepost = "0";
	}
	var ph = document.getElementById('previewPost' + typepost);
	var pht = document.getElementById('previewPostT' + typepost);
	if (ph.style.display == "none") {
		ph.style.display = "block";
		ph.style.opacity = "1.00";
		ph.style.filter = "alpha(opacity=100)";
		stoppreview = 0;
		lastClickedQQ = "";
		pht.innerHTML = "<small>(" + b6_stopprev + ")</small>";
		previewPostTime(type);
	}
	else if (stoppreview == 0) {
		lastClickedQQ = "";
		ph.style.opacity = "1.00";
		ph.style.filter = "alpha(opacity=100)";
		pht.innerHTML = "<small>(" + b6_stopprev + ")</small>";
		previewPostTime(type);
	}
}
		
function previewPost_lostfocus(type) {
	var typepost = "";
	if (type == '2') {
		typepost = "0";
	}
	if (stoppreview == 0) {
		var ph = document.getElementById('previewPost' + typepost);
		var pht = document.getElementById('previewPostT' + typepost);
		clearTimeout(ppt);
		ph.style.opacity = ".30";
		ph.style.filter = "alpha(opacity=30)";
		pht.innerHTML = "<small>" + b6_starprev + "</small>";
	}

}

function previewPostTime(type) {
		var typepost = "";
	if (type == '2') {
		typepost = "0";
	}
		
	var textArea = document.getElementById('postArea' + typepost).value;

	if ((type == '2' && textArea != textareaCache0) || (type == '1' && textArea != textareaCache)) {
		if (type == '2') {
			textareaCache0 = textArea;
		}
		else {
			textareaCache = textArea;
		}
		c = setInterval(function() {checkscrollpos(typepost);}, 500);

		var dataLine = textArea;
		if (dataLine != dataLineprev) {
			textArea = europlus(textArea);

			dataLineprev = "0::@ppo@::" + escape(textArea) + "::@ppo@::" + type;

			x_ajax_previewPost(dataLineprev, showpreviewPost);
		}
	}
	if (stoppreview == 0 && type == 1) {
		ppt=setTimeout(function() {previewPostTime(1);}, 2000);
	}
	else if (stoppreview == 0 && type == 2) {
		ppt=setTimeout(function() {previewPostTime(2);}, 2000);
	}
}

function showpreviewPost(dataline) {
	if (document.getElementById) {
		var dataLineArray = dataline.split('::cur@lo::');
			var typepost = "";
		if (dataLineArray[2] == '2') {
			typepost = "0";
		}
		dataLineArray[1] = unescape(dataLineArray[1]);
		dataLineArray[1] = dataLineArray[1].replace(/\+/g, " ");
		dataLineArray[1] = dataLineArray[1];
		dataLineArray[1] = dataLineArray[1].replace(/::@plus@::/g, "+");
		dataLineArray[1] = dataLineArray[1].replace(/::@euro@::/g, "€");
		document.getElementById('previewPost' + typepost).innerHTML = dataLineArray[1];
	}
}

function StoppreviewPost(type) {
		var typepost = "";
	if (type == '2') {
		typepost = "0";
	}
	var ph = document.getElementById('previewPost' + typepost);
	var line = document.getElementById('previewPostT' + typepost);
	if (stoppreview == 1) {
		stoppreview = 0;
		fadeIn('previewPost' + typepost,0.30);
		line.innerHTML = "<small>(" + b6_stopprev + ")</small>";
		previewPostTime(type);
	}
	else if (stoppreview == 0) {
		clearTimeout(ppt);
		stoppreview = 1;
		ph.style.opacity = ".30";
		ph.style.filter = "alpha(opacity=30)";
		line.innerHTML = "<small>" + b6_starprev + "</small>";
	}
}

function checkscrollpos(id) {
	clearTimeout(c);
	if (!id) {
		id = "";
	}
	var textArea = document.getElementById('postArea' + id);

	if (textArea) {
		document.getElementById('scroll_position' + id).innerHTML = textArea.scrollTop;
		c = setInterval(function() {checkscrollpos(id);}, 500);
	}

}

function userreadlist(ID) {
	if (document.getElementById) {
		x_ajax_userreadlist(ID, userreadlistdisplay);
	}
}

function userreadlistdisplay(data) {
	if (document.getElementById) {
		document.getElementById('usersBrowsing').innerHTML = data;
	}
}

function emptymainBlog(threadID,userID,channel,action,page,anchor) {
	if (document.getElementById) {
		var dataLine = threadID + ':!bl@:' + userID + ':!bl@:' + channel + ':!bl@:' + action + ':!bl@:' + page;
		if (action == "g_view") {
			document.getElementById('anchorthread').innerHTML = window.location.hash;
		}
		document.getElementById('blogad_cache').innerHTML = document.getElementById('blogad').innerHTML;
		document.getElementById('blogparentC').style.opacity = ".30";
		document.getElementById('blogparentC').style.filter = "alpha(opacity=30)";

		x_ajax_blogThread(dataLine, displayBlog);
	}
}

function displayBlog(dataline) {
	if (document.getElementById) {
		var dataLineArray = dataline.split('::cur@blo::');
		document.getElementById('threadlist').innerHTML = dataLineArray[0];
		document.getElementById('blogparentC').style.opacity = "1.00";
		document.getElementById('blogparentC').style.filter = "alpha(opacity=100)";
		if (dataLineArray[1] == "g_default") {
			var anchorthread = document.getElementById('anchorthread');
			if (anchorthread.innerHTML) {
				anchorthread.innerHTML = "";
			}
		}
		document.getElementById('blogad').innerHTML = document.getElementById('blogad_cache').innerHTML;
		data = document.getElementById('blogcache').innerHTML;
		var dlArray = data.split(':!bl@:');
		storedhash="#blog/" + dlArray[1] + "/" + dlArray[0] + "/" + dlArray[2] + "/" + dlArray[3] + "/" + dlArray[4];
		window.location.hash = storedhash;

		document.title = dataLineArray[2] + "- " + b6_site;
		titleHolder = document.title;
	}
}

function showBlogList() {
	if (document.getElementById) {
		var ph = document.getElementById('BlogList');
		if (ph.style.display == "block") {
			ph.style.display = "none";
		}
		else if (ph.style.display == "none") {
			ph.style.display = "block";
		}
		else {
			x_ajax_showBlogList('', blogList);
		}
	}
}

function blogList(data) {
	if (document.getElementById) {
		var ph = document.getElementById('BlogList');
		ph.innerHTML = data;
		ph.style.opacity= ".00";
		ph.style.filter = "alpha(opacity=0)";
		ph.style.display='block';
		fadeIn(ph.id,0);
	}
}

function showblogConf() {
	if (document.getElementById) {
		var ph = document.getElementById('blogConf');
		if (ph.style.display == "block") {
			ph.style.display = "none";
		}
		else if (ph.style.display == "none") {
			ph.style.display = "block";
		}
		else {
			x_ajax_showblogConf('', blogConf);
		}
	}
}

function blogConf(data) {
	if (document.getElementById) {
		var ph = document.getElementById('blogConf');
		ph.innerHTML = data;
		ph.style.opacity= ".00";
		ph.style.filter = "alpha(opacity=0)";
		ph.style.display='block';
		fadeIn(ph.id,0);
	}
}

function blogautorefresh() {
	clearTimeout(b);
	clearInterval(h);
	b = setInterval(function() {blogUpdate();}, 300000);
	if (window.location.hash == "" || window.location.hash == "#blog") {
		data = document.getElementById('blogcache').innerHTML;
		var dlArray = data.split(':!bl@:');
		storedhash="#blog/" + dlArray[1] + "/" + dlArray[0] + "/" + dlArray[2] + "/" + dlArray[3] + "/" + dlArray[4];
		window.location.hash = storedhash;
	}
	h = setInterval(function() {checkhash();}, 1000);
}

function blogUpdate() {	
	if (document.getElementById) {
		var timeStamp = document.getElementById('timestamp');
		var dataLine = document.getElementById('blogcache').innerHTML;
		var data = dataLine + ":!bl@:" + timeStamp.className;
	
		x_ajax_blogUpdate(data, verifupdate);
	}
}

function verifupdate(data) {
	if (document.getElementById) {
		var dataLineArray = data.split('::cur@blo::');
		var timeStamp = document.getElementById('timestamp');
		timeStamp.className=dataLineArray[1];
		if (dataLineArray[0] == "1") {
			var dataLine = document.getElementById('blogcache').innerHTML;
			x_ajax_resetblogCore(dataLine, displayblogCore);
		}
	}
}

function saveblogConf() {	
	if (document.getElementById) {
		var ca = document.blogconf.blogtitle.value;
		var cb = document.blogconf.blogsubtitle.value;
		var cc = document.blogconf.blogwebname.value;
		dataLine = ca + ":@#!:" + cb + ":@#!:" + cc;
		var bt = document.getElementById('blogtitle');
		bt.innerHTML = "";
	
		x_ajax_saveblogConf(dataLine, blogNewConf);
	}
}

function blogNewConf(data) {
	if (document.getElementById) {
		var ph = document.getElementById('blogConf');
		ph.innerHTML = "";
		ph.style.display='';
		var bt = document.getElementById('blogtitle');
		bt.className = "blogheaduser";
		bt.style.opacity= ".00";
		bt.style.filter = "alpha(opacity=0)";
		bt.innerHTML = data;
		fadeIn(bt.id,0);
	}
}

function resetblogCore(threadID,userID,channel,action,page,anchor) {
	if (document.getElementById) {
		var dataLine = threadID + ':!bl@:' + userID + ':!bl@:' + channel + ':!bl@:' + action + ':!bl@:' + page;

		x_ajax_resetblogCore(dataLine, displayblogCore);
	}
}

function displayblogCore(dataline) {
	if (document.getElementById) {
		var dataLineArray = dataline.split('::cur@blo::');
		document.getElementById('blogparentC').innerHTML = dataLineArray[0];
	}
}

function untogglem_blog(Layer1, Layer2) {
	if (document.getElementById) {
		document.getElementById(Layer1).className= 'blogTypeSel';
		document.getElementById(Layer2).className= 'blogType';
		if (Layer1 == "listThread") {
			x_ajax_m_blog_thread(0, displaym_blog_thread);
		}
		else {
			x_ajax_m_blog_com(0, displaym_blog_com);
	}
}
}

function displaym_blog_thread(data) {
	if (document.getElementById) {
		document.getElementById("listComl").style.display= 'none';
		document.getElementById("listThreadl").innerHTML= data;
		document.getElementById("listThreadl").style.display= 'block';
	}
}

function displaym_blog_com(data) {
	if (document.getElementById) {
		document.getElementById("listThreadl").style.display= 'none';
		document.getElementById("listComl").innerHTML= data;
		document.getElementById("listComl").style.display= 'block';
	}
}

function unstick(ID) {
	if (document.getElementById) {
		document.getElementById('newPostsStr').innerHTML = "&nbsp; <b>" + b6_wait + wait2;
		x_ajax_unstick(ID, unstickreload);
	}
}

function userstick(ID) {
	if (document.getElementById) {
		x_ajax_userstick(ID, unstickreload);
	}
}

function userunstick(ID) {
	if (document.getElementById) {
		x_ajax_userunstick(ID, unstickreload);
	}
}

function unstickreload() {
	if (document.getElementById) {
		var page = document.getElementById('numpage_cache').innerHTML;
		var filter = document.getElementById('filter').innerHTML;
		var channels = document.getElementById('chan_cache').innerHTML;
		var tags = document.getElementById('tags_cache').innerHTML;
		var team = document.getElementById('listthreadteam').innerHTML;

		var dataLine = ":@@:" + team + ":@@:" + filter + ":@@:" + page + ":@@:" + channels + ":@@:" + tags;

		var ph = document.getElementById('searchForm');
		if (ph.style.display == "block") {
			var form = document.forms["search"];
			var searchNodes = form.elements;
			if (searchNodes[0].value != "" || searchNodes[4].value != "" || searchNodes[5].value != "") {
				clearInterval(t);
				clearInterval(m);
				dataLine = searchNodes[0].value + ':!@:' + searchNodes[4].value + ':!@:' + searchNodes[6].value + ':!@:' + searchNodes[5].value + dataLine;
			}
		}
		x_ajax_resetThreadList(dataLine, displayResetThreads);
	}
}

function signal_admin(rowID,event) {
	if (document.getElementById) {
		cursorX = event.clientX;
		cursorY = event.clientY;

		ajaxload_on();
		var dataLine = rowID + ':!@:' + '_';
		x_ajax_signal_admin(dataLine, displaySignal_admin);
	}
}

function displaySignal_admin(dataLine) {
	if (document.getElementById) {	
		var dlarray = dataLine.split('::arrdlm::');
		displaylayer(dlarray[0],cursorX,cursorY,true,'postContent_layer');
	}
}

function submitSignal_admin(postID) {
	if (postID) {	
		var dataLine = postID + '::!sg@::' + europlus(document.getElementById("signal_comment").value);
		closelayer();
		x_ajax_submitSignal_admin(dataLine,submitSignal_admin_end);
		return false;
	}	
	
	return false;
}

function submitSignal_admin_end() {
	closelayer();
}

function scrolltoID(name) {
	if (document.getElementById(name)) {
		var obj = document.getElementById(name);
		var ytoscroll = 0;
		if (obj.offsetParent) {
			ytoscroll = obj.offsetTop;
			while (obj = obj.offsetParent) {
				ytoscroll += obj.offsetTop;
			}
		}
		window.scrollTo(0,ytoscroll);
		scrolltoid_count = 0;
	}
	else if (scrolltoid_count < 10) {
		scrolltoid_count++;
		setTimeout(function() {scrolltoID(name);}, 1000);
	}
}

function cacheScroll(type) {
	if (window.pageYOffset) {
		if (type == "1") {
			postoscroll = window.pageYOffset;
		}
		else {
			posTtoscroll = window.pageYOffset;
	}
	}
	else if (document.body.scrollTop) {
		if (type == "1") {
			postoscroll = document.body.scrollTop;
		}
		else {
			posTtoscroll = document.body.scrollTop;
	}
}
}

function confirmExit() {
	if (document.getElementById('postArea')) {
		if (document.getElementById('postArea').value != "") {
			return b6_confirm_exit;
		}
	}
	else if (document.getElementById('postArea0')) {
		if (document.getElementById('postArea0').value != "" && document.getElementById('valid_form').innerHTML != "OK") {
			return b6_confirm_exit;
		}
	}
}

function fullsite() {
	SetCookie('full_site','fullsite',730);
	window.location.reload();
}

function fullsite_simple() {
	SetCookie('full_site','fullsite_simple',730);
	window.location.reload();
}

function mobilesiteplus() {
	SetCookie('full_site','mobilesiteplus',730);
	window.location.reload();
}

function mobilesite() {
	SetCookie('full_site','mobilesite',730);
	window.location.reload();
}

function ajaxload_on() {
	var ajaxload = document.getElementById('ajaxload');
	var posX = 0;
	if (window.innerWidth) {
		posX = window.innerWidth + window.pageXOffset - 52;
	}
	else if (document.body.clientWidth) {
		posX = document.body.scrollLeft + document.body.clientWidth - 52;
	}
	ajaxload.style.left = posX + "px";
	ajaxload.style.visibility = "visible";
}

function ajaxload_off() {
	document.getElementById('ajaxload').style.visibility = "hidden";
}

function newlayer(content,classname,timeout, event, above,distance) {
	cursorX = event.clientX;
	cursorY = event.clientY;
	
	clearTimeout(l);
	layerCache = content;
	l = setTimeout(function() {displaylayer('', cursorX, cursorY, '', classname, '', above, distance);}, timeout);
}

function newcontexmenu(event,postID) {
	cursorX = event.clientX;
	cursorY = event.clientY;
	
	clearTimeout(l);
	layerCache = document.getElementById('contextmenucachepage').innerHTML + document.getElementById('contextmenucache').innerHTML;
	document.getElementById('screenCover').style.display = "block";
	l = setTimeout(function() {displaylayer('', cursorX, cursorY, '', 'contextMenu', '', true, 2);}, 1);
	if (postID && document.getElementById('contextmenupostID')) {
		document.getElementById('contextmenupostID').innerHTML = postID;
	}
}

function movelayer(event) {
	cursorX = event.clientX;
	cursorY = event.clientY;
	
	if (document.getElementById('displayedlayer').style.display == "block") {
		displaylayer('',cursorX,cursorY);
	}
}

function displaylayer(content,X,Y,sticky,classname,left,above,distance) {
	var displayedlayer = document.getElementById('displayedlayer');

	if (layerCache) {
		content = layerCache;
		layerCache = "";
	}

	if (sticky) {
		content += "<br/><div onclick=\'closelayer();\' class=\'closeButton\'></div>";
	}

	if (content) {
		displayedlayer.style.left = "0px";
		displayedlayer.style.top = "0px";
		if (classname) {
			displayedlayer.className = classname;
		}
		displayedlayer.innerHTML = content;
		displayedlayer.style.display = "block";
		ajaxload_off();
	}

	if (!distance) {
		distance = 12;
	}

	cursorX = X * 1;
	cursorY = Y * 1;
	var xscroll = 0;
	var yscroll = 0;
	var ymax = 0;
	if (window.innerWidth) {
		xscroll = window.innerWidth + window.pageXOffset;
		yscroll = window.pageYOffset;
		cursorY += yscroll;
		ymax = window.innerHeight + window.pageYOffset;
	}
	else if (document.body.clientWidth) {
		xscroll = document.body.clientWidth + document.body.scrollLeft;
		yscroll = document.documentElement.scrollTop;
		cursorY += yscroll;
		ymax = document.body.clientHeight + document.documentElement.scrollTop;
	}

	var posX = cursorX + distance;
	var layerwidth = displayedlayer.offsetWidth;
	if (left || (xscroll < (cursorX + layerwidth + distance) && (cursorX - layerwidth - distance) > 0)) {
		posX = cursorX - layerwidth - distance;
	}

	var posY = cursorY - displayedlayer.offsetHeight - distance;
	var layerheight = displayedlayer.offsetHeight;
	if (!above && yscroll > (cursorY - layerheight)) {
		posY = yscroll;
	}
	else if (above && (cursorY + layerheight) < ymax) {
		posY = cursorY + distance;
	}
	var posXs = posX - 8;
	var posYs = posY + 8;
	displayedlayer.style.left = posX + "px";
	displayedlayer.style.top = posY + "px";
	displayedlayer.style.visibility = "visible";
}

function closelayer() {
	clearTimeout(l);
	layerCache = "";
	var displayedlayer = document.getElementById('displayedlayer');
	displayedlayer.style.display = "none";
	displayedlayer.style.visibility = "hidden";
	displayedlayer.innerHTML = "";
	document.getElementById('screenCover').style.display = "none";
}

function newClass(classname, attributes) {
	var newStyle = document.createElement('style');
	newStyle.setAttribute('type', 'text/css');

	var cssText = classname +' { '+attributes+' }';
	if(newStyle.styleSheet) {
		 newStyle.styleSheet.cssText = cssText;
	}
	else {
		newStyle.appendChild(document.createTextNode(cssText));
	}
	document.body.appendChild(newStyle);
}

function set_last_unread(ID) {
	var postID = document.getElementById('contextmenupostID').innerHTML;
	var dataline = ID + "@@::cpt::@@" + postID;
	x_ajax_set_last_unread(dataline,proc_last_unread);
}

function proc_last_unread(dataline) {
	var dataLineArray = dataline.split('@@');
	emptymain2(dataLineArray[0],dataLineArray[1]);
}

function checksystem() {
	var version = document.getElementById('site_version').innerHTML;
	x_ajax_verify_version(version, displayVersion);
	if (document.getElementById('main_tag_cloud')) {
		var tag_time = document.getElementById('main_tag_cloud').className;
		x_ajax_refreshTags(tag_time,display_refreshTags);
	}
	setTimeout(function() {checksystem();}, 300000);
}

function displayVersion(dataline) {
	if (dataline) {
	var dataLineArray = dataline.split('::@@sys@@::');
		if (dataLineArray[1] == "") {
			document.getElementById('site_version').innerHTML = dataLineArray[2];
			x_ajax_verify_message(0, displayMessage);
		}
		else {
			displayMessage(dataLineArray[3]);
	}
	}
	else {
		x_ajax_verify_message(0, displayMessage);
	}
}
				
function displayMessage(message) {
	var system_message = document.getElementById('system_message');
	system_message.innerHTML = message;
	if (message) {
		system_message.style.display = "block";
		document.getElementById('ajaxload').style.top = "24px";
	}
	else {
		system_message.style.display = "none";
		document.getElementById('ajaxload').style.top = "0px";
	}
}

function reloadwhooneline() {
	if (document.getElementById('whooneline_content')) {
		setTimeout(function() {reloadwhooneline();}, 300000);
		x_ajax_reloadwhooneline(0,updatewhooneline);
	}
}

function updatewhooneline(dataline) {
	if (dataline) {
		var dataLineArray = dataline.split('@@::WHO!ONLINE::@@');
		document.getElementById('whooneline_content').innerHTML = dataLineArray[0];
		document.getElementById('whooneline_title').innerHTML = dataLineArray[1];
	}
}

function view_picture(data,desc,pictwidth,pictheight,album,pictID) {
	if (document.getElementById ) {
		var screenwidth = document.body.clientWidth;
		var screenheight = window.innerHeight - 36;
		var pictwidthtemp = 700;
		var pictureheight = 0;
		if (pictwidth < 700) {
			pictwidthtemp = pictwidth;
		}
		var rapport = (pictheight / (pictwidth / pictwidthtemp));
		if (rapport > screenheight) {
			pictwidthtemp = (pictwidthtemp / (rapport / screenheight));
		}
		var picture = "<img src=\'" + data + "\' alt=\'\' id=\'Picture\' name='" + pictwidthtemp + "' style=\'max-width:" + screenwidth + "px; width:" + pictwidthtemp + "px;\' onkeydown=\"change_picture(e); return false;\"";
		if (album) {
			picture += " onclick=\"next_picture(); return false;\"";
		}
		picture += " />" + "<span id=\'pictwidth\' style=\'display:none;\'>" + screenheight + "</span><span id=\'testjs\'><center>" + desc + "</center></span>";

		var picturediv = document.getElementById('display_picture');
		document.getElementById('picture_name').name = data;
		if (album) {
			document.getElementById('picture_name').innerHTML = album;
		}
		document.getElementById('picture_name').className = pictID;
		document.getElementById('full_button').style.display = "block";
		picturediv.innerHTML = picture;
		document.getElementById('page').style.opacity = "0.1";
		if (window.pageYOffset) {
			pictureheight = window.pageYOffset;
		}
		else {
			pictureheight = document.body.scrollTop;
		}

		document.getElementById('full_button').style.opacity = "0";
		picturediv.style.display = "block";
		picturediv.style.top= 10 + pictureheight + "px";

		var xscroll = 0;
		if (window.innerWidth) {
			xscroll = window.innerWidth + window.pageXOffset;
		}
		else if (document.body.clientWidth) {
			xscroll = document.body.clientWidth + document.body.scrollLeft;
		}

		picturediv.style.left=((xscroll - pictwidthtemp)/2) + "px";
		fadeIn(picturediv.id,0.9);
		
		if (album) {
		document.onkeydown = change_picture;
	}
		else {
			document.onkeydown = change_single_picture;
		}
	}
}

function close_picture() {
	if (document.getElementById) {
		document.getElementById('screenCover').style.display = "none";
		document.getElementById('full_button').style.display = "none";
		fadeOut('display_picture');
		fadeIn('page',0.1);
	}
}

function close_content() {
	if (document.getElementById) {
		document.getElementById('screenCover').style.display = "none";
		document.getElementById('full_button_content').style.display = "none";
		document.getElementById('page').style.opacity = "1";
		document.getElementById('page').style.filter = "alpha(opacity=100)";
		document.getElementById('display_content').innerHTML = "";
		document.getElementById('display_content').style.display = "none";
	}
}

function change_picture(e) {
	if (document.getElementById('Picture')) {
		var TouchKeyDown = (window.event) ? event.keyCode : e.keyCode;
		var picturediv = document.getElementById('Picture');
		var maxwidth = picturediv.offsetWidth;
		var screenwidth = 0;
		var leftmargin = 0;
		if (window.innerWidth) {
			screenwidth = window.innerWidth + window.pageXOffset;
		}
		else if (document.body.clientWidth) {
			screenwidth = document.body.clientWidth + document.body.scrollLeft;
		}

		if (TouchKeyDown == 39) {
			next_picture();
			picturediv.style.opacity = "0.3";
			picturediv.style.filter = "alpha(opacity=30)";
			return false;
		}
		else if (TouchKeyDown == 37) {
			previous_picture();
			picturediv.style.opacity = "0.3";
			picturediv.style.filter = "alpha(opacity=30)";
			return false;
		}
		else if (TouchKeyDown == 27) {
			close_picture();
			document.onkeypress = "";
			return false;
		}
		else if (TouchKeyDown == 38) {
			if (maxwidth < screenwidth) {
				maxwidth = maxwidth * 1.1;
				leftmargin = (screenwidth - maxwidth)/2;
				picturediv.style.width = maxwidth + "px";
				document.getElementById('display_picture').style.left= leftmargin + "px";
			}
			return false;
		}
		else if (TouchKeyDown == 40) {
			if (maxwidth > 16) {
				maxwidth = maxwidth / 1.1;
				leftmargin = (screenwidth - maxwidth)/2;
				picturediv.style.width = maxwidth + "px";
				document.getElementById('display_picture').style.left= leftmargin + "px";
			}
			return false;
		}
	}
}

function change_single_picture(e) {
	if (document.getElementById('Picture')) {
		var TouchKeyDown = (window.event) ? event.keyCode : e.keyCode;
		var picturediv = document.getElementById('Picture');
		var maxwidth = picturediv.offsetWidth;
		var screenwidth = 0;
		var leftmargin = 0;
		if (window.innerWidth) {
			screenwidth = window.innerWidth + window.pageXOffset;
		}
		else if (document.body.clientWidth) {
			screenwidth = document.body.clientWidth + document.body.scrollLeft;
		}

		if (TouchKeyDown == 27) {
			close_picture();
			document.onkeypress = "";
			return false;
		}
		else if (TouchKeyDown == 38) {
			var screenwidth = document.body.clientWidth;
			if (maxwidth < screenwidth) {
				maxwidth = maxwidth * 1.1;
				leftmargin = (screenwidth - maxwidth)/2;
				picturediv.style.width = maxwidth + "px";
				document.getElementById('display_picture').style.left= leftmargin + "px";
			}
			return false;
		}
		else if (TouchKeyDown == 40) {
			if (maxwidth > 16) {
				maxwidth = maxwidth / 1.1;
				leftmargin = (screenwidth - maxwidth)/2;
				picturediv.style.width = maxwidth + "px";
				document.getElementById('display_picture').style.left= leftmargin + "px";
			}
			return false;
		}
	}
}

function view_picturetemp(dataline) {
	var dlarray = dataline.split('::@@::');
	view_picture(dlarray[0],dlarray[1],dlarray[2],dlarray[3],dlarray[4],dlarray[5]);
	
}

function previous_picture() {
	var picture = document.getElementById('picture_name').name;
	var album = document.getElementById('picture_name').innerHTML;
	var pictID = document.getElementById('picture_name').className;
	var threadID = "";
	if (document.getElementById('numthreadID')) {
		threadID = document.getElementById('numthreadID').innerHTML;
	}
	var dataline = picture + "@@::AA::@@" + album + "@@::AA::@@" + pictID + "@@::AA::@@" + threadID;
	x_ajax_left_picture(dataline,view_picturetemp);
}

function next_picture() {
	var picture = document.getElementById('picture_name').name;
	var album = document.getElementById('picture_name').innerHTML;
	var pictID = document.getElementById('picture_name').className;
	var threadID = "";
	if (document.getElementById('numthreadID')) {
		threadID = document.getElementById('numthreadID').innerHTML;
	}
	var dataline = picture + "@@::AA::@@" + album + "@@::AA::@@" + pictID + "@@::AA::@@" + threadID;
	x_ajax_right_picture(dataline,view_picturetemp);
}

function displayAlbums(dataline) {
	var dlarray = dataline.split('::albs::');
	document.getElementById('info_album' + dlarray[0]).innerHTML = dlarray[1];
	displayDiv('info_album' + dlarray[0],'','true');
}

function displayPicts(dataline) {
	var dlarray = dataline.split('::pcts::');
	document.getElementById('info_pict' + dlarray[0]).innerHTML = dlarray[1];
	displayDiv('info_pict' + dlarray[0],'','true');
}

function share_album(albumID,threadID) {
	dataline = albumID + "::IDs@sID::" + threadID;
	x_ajax_share_album(dataline, album_shared);
}

function album_shared() {

}

function verify_pict(postID,pictID) {
	dataline = postID + "::IDp@pID::" + pictID + "::IDp@pID::" + document.getElementById('numthreadID').innerHTML;
	x_ajax_verify_pict(dataline, pict_shared);
}

function pict_shared(dataline) {
	var dlarray = dataline.split('::pcts::');
	if (dlarray[1] && !dlarray[2]) {
		document.getElementById('num_pict' + dlarray[0]).value = dlarray[1];
		pushBt('pict',dlarray[0]);
	}
	else {
		document.getElementById('info_pict' + dlarray[0]).innerHTML = dlarray[2];
		displayDiv('info_pict' + dlarray[0],'','true');
	}
}

function menu_sections(section1,section2,section3) {
	document.getElementById(section1 + "tab").className = "section_Sel";
	document.getElementById(section2 + "tab").className = "section_unSel";
	if (section3)
		document.getElementById(section3 + "tab").className = "section_unSel";
	document.getElementById(section1).style.display = "block";
	document.getElementById(section2).style.display = "none";
	if (section3)
		document.getElementById(section3).style.display = "none";
}

function verif_nick() {
	clearTimeout(f);
	f = setTimeout(function() {check_nick();}, 300);
	if (!document.getElementById('email'))
		document.getElementById('submitnewNick').style.display = 'none';
}

function check_nick() {
	clearTimeout(f);
	var nick = document.getElementById('newNick').value;
	x_ajax_verif_nick(nick,display_nick);
}

function display_nick(dataline) {
	var dlarray = dataline.split('::@@::');
	if (dlarray[0] == "true") {
		document.getElementById('newNick').style.color = 'green';
		document.getElementById('nick_ok').value = "1";
		document.getElementById('messagenewNick').style.color = 'green';
		document.getElementById('messagenewNick').innerHTML = dlarray[1];
	}
	else {
		document.getElementById('newNick').style.color = 'red';
		document.getElementById('messagenewNick').style.color = 'red';
		document.getElementById('messagenewNick').innerHTML = dlarray[1];
		document.getElementById('nick_ok').value = "0";
	}
	verif_allOK();
}

function verif_pass() {
	clearTimeout(f);
	f = setTimeout(function() {check_pass();}, 300);
}

function check_pass() {
	clearTimeout(f);
	var pass = document.getElementById('password').value;
	x_ajax_verif_pass(pass,display_pass);
}

function display_pass(dataline) {
	var dlarray = dataline.split('::@@::');
	if (dlarray[0] == "true") {
		document.getElementById('password').style.color = 'green';
		document.getElementById('messagePass').style.color = 'green';
		document.getElementById('messagePass').innerHTML = dlarray[1];
		document.getElementById('pass_ok').value = "1";
	}
	else {
		document.getElementById('password').style.color = 'red';
		document.getElementById('messagePass').style.color = 'red';
		document.getElementById('messagePass').innerHTML = dlarray[1];
		document.getElementById('pass_ok').value = "0";
	}
	var pass = document.getElementById('password').value;
	var vpass = document.getElementById('vpassword').value;
	
	verif_allOK();
}

function verif_vpass() {
	var pass = document.getElementById('password').value;
	var vpass = document.getElementById('vpassword').value;
	if (pass != vpass) {
		x_ajax_verif_vpass(pass,display_vpass);
	}
	else {
		document.getElementById('vpassword').style.color = 'green';
		document.getElementById('messageVPass').style.color = 'green';
		document.getElementById('messageVPass').innerHTML = '';
		document.getElementById('vpass_ok').value = "1";
		verif_allOK();
	}
}

function display_vpass(data) {
	var pass = document.getElementById('password').value;
	var vpass = document.getElementById('vpassword').value;
	if (pass != vpass) {
		document.getElementById('vpassword').style.color = 'red';
		document.getElementById('messageVPass').style.color = 'red';
		document.getElementById('messageVPass').innerHTML = data;
		document.getElementById('vpass_ok').value = "0";
	}
	verif_allOK();
}

function verif_email() {
	clearTimeout(f);
	if (document.getElementById('email').value.length > 7)
		f = setTimeout(function() {check_email();}, 300);
}

function check_email() {
	clearTimeout(f);
	var email = document.getElementById('email').value;
	x_ajax_verif_email(email,display_email);
}

function display_email(dataline) {
	var dlarray = dataline.split('::@@::');
	if (dlarray[0] == "true") {
		document.getElementById('email').style.color = 'green';
		document.getElementById('messageemail').style.color = 'green';
		document.getElementById('messageemail').innerHTML = dlarray[1];
		document.getElementById('email_ok').value = "1";
	}
	else {
		document.getElementById('email').style.color = 'red';
		document.getElementById('messageemail').style.color = 'red';
		document.getElementById('messageemail').innerHTML = dlarray[1];
		document.getElementById('email_ok').value = "0";
	}
	verif_allOK();
}

function verif_vemail() {
	var email = document.getElementById('email').value;
	var vemail = document.getElementById('vemail').value;
	if (email != vemail) {
		x_ajax_verif_vemail(email,display_vemail);
	}
	else {
		document.getElementById('vemail').style.color = 'green';
		document.getElementById('messagevemail').style.color = 'green';
		document.getElementById('messagevemail').innerHTML = '';
		document.getElementById('vemail_ok').value = "1";
		verif_allOK();
	}
}

function display_vemail(data) {
	document.getElementById('vemail').style.color = 'red';
	document.getElementById('messagevemail').style.color = 'red';
	document.getElementById('messagevemail').innerHTML = data;
	document.getElementById('vemail_ok').value = "0";
	verif_allOK();
}

function verif_allOK() {
	if (document.getElementById('nick_ok').value == "1" && document.getElementById('pass_ok').value == "1" && document.getElementById('vpass_ok').value == "1" && document.getElementById('email_ok').value == "1" && document.getElementById('vemail_ok').value == "1") {
		document.getElementById('submitnewNick').style.border = "2px solid green";
	}
	else  {
		document.getElementById('submitnewNick').style.border = "2px solid red"
	}
}

function verif_all() {
	if (document.getElementById('newNick').value)
		check_nick();
	if (document.getElementById('password').value)
		check_pass();
	if (document.getElementById('vpassword').value)
		verif_vpass();
	if (document.getElementById('email').value)
		check_email();
	if (document.getElementById('vemail').value)
		verif_vemail();
}

function fb_widget(data) {
	if (data == "on") {
		SetCookie('fb_widget','', -1);
	}
	else {
		SetCookie('fb_widget','off', 365);
	}
}

function input_user(type) {
	clearTimeout(g);
	g = setTimeout(function() {input_user_load(type);}, 400);
}

function input_user_load(type) {
	clearTimeout(g);

	var user = document.getElementById('userprofilename' + type).value;
	if (user) {
		ajaxload_on();
		var dataline = europlus(user) + "::@@iu@@::" + type;
		x_ajax_inputUser(dataline, displayinputUser);
	}
}

function inputselect_user(userID,type) {
	var user = document.getElementById('selectuser_' + type + "_" + userID).innerHTML;
	document.getElementById('userprofilename' + type).value = user;
	
	if (type == "0") {
		userprofile2(userID);
}
	if (type == "3") {
		var tolist = document.getElementById('toList');
		if (tolist.value == "") {
			tolist.value = user;
		}
		else {
			tolist.value += "," + user;
		}
		document.getElementById('userprofilename' + type).value = "";
	}
}

function displayinputUser(dataline) {
	var dlarray = dataline.split('@@:t:@@');

	if (dlarray[0] == "add") {
		document.getElementById('userprofilename' + dlarray[3]).style.color = "green";
	}
	else {
		document.getElementById('userprofilename' + dlarray[3]).style.color = "black";
	}
	if (dlarray[1]) {
		document.getElementById('inputSelectUser' + dlarray[3]).style.display = "block";
		document.getElementById('inputSelectUser' + dlarray[3]).innerHTML = dlarray[1];
	}
	else {
		document.getElementById('inputSelectUser' + dlarray[3]).style.display = "none";
		document.getElementById('inputSelectUser' + dlarray[3]).innerHTML = "";
	}
	ajaxload_off();
}

function show_select_user(type) {
	if (document.getElementById('inputSelectUser' + type).innerHTML) {
		document.getElementById('inputSelectUser' + type).style.display = "block";
	}
}

function hide_select_user(type) {
	clearTimeout(g);
	g = setTimeout(function() {hide_select_user2(type);}, 1000);
}

function hide_select_user2(type) {
	document.getElementById('inputSelectUser' + type).style.display = "none";
}

function vote_for(postID,voteName) {
	var dataline = postID + "::@@vote@@::" + voteName;
	x_ajax_vote_for(dataline, update_vote);
}

function vote_against(postID,voteName) {
	var dataline = postID + "::@@vote@@::" + voteName;
	x_ajax_vote_against(dataline, update_vote);
}

function update_vote(dataline) {
	var dlarray = dataline.split('::@@::');
	var x = document.getElementsByTagName('span');
	for (var i=0;i<x.length;i++) {
		if (x[i].className == dlarray[1]) {	
				x[i].innerHTML=dlarray[0];
		}
	}
}

function delete_thread_mod(modID) {
	x_ajax_delete_thread_mod(modID, deleted_thread_mod);
}

function deleted_thread_mod(modID) {
	var page = document.getElementById('thread_current_page').innerHTML;
	var threadid = document.getElementById('numthreadID').innerHTML;
	emptymainThreadPage(threadid,0,page,0);
//	modID_remove.parentNode.removeChild(modID_remove);
}

function show_threadsTags() {
	clearTimeout(g);
	g = setTimeout(function() {show_threadsTags2();}, 200);
}

function hide_threadsTags() {
	clearTimeout(g);
	g = setTimeout(function() {hide_threadsTags2();}, 300);
}

function show_threadsTags2() {
	document.getElementById('treag_tagList').style.maxHeight='';
}

function hide_threadsTags2() {
	document.getElementById('treag_tagList').style.maxHeight='32px';
}

function format_option(id,postID) {
	var option = document.getElementById('format_option_' + id + '_' + postID);
	if (option.className == "format_option") {
		option.className = "format_optionSel";
		document.getElementById('option_' + id + '_' + postID).style.display = "block";
	}
	else {
		option.className = "format_option";
		document.getElementById('option_' + id + '_' + postID).style.display = "none";
	}
}

function file_manager(folderID,teamID,link_order,link_sens) {
	if (!folderID && folderID != "0") {
		folderID = document.getElementById('folderID').innerHTML;
	}
	else {
		document.getElementById('folderID').innerHTML =  folderID;
	}
	if (!teamID) {
		teamID = document.getElementById('teamID').innerHTML;
	}
	else {
		document.getElementById('teamID').innerHTML = teamID;
	}
	if (!link_order) {
		link_order = document.getElementById('link_order').innerHTML;
	}
	else {
		document.getElementById('link_order').innerHTML = link_order;
	}
	if (!link_sens) {
		link_sens = document.getElementById('link_sens').innerHTML;
	}
	else {
		document.getElementById('link_sens').innerHTML = link_sens;
	}
	var dataline = folderID + "@@:f:@@" + teamID + "@@:f:@@" + link_order + "@@:f:@@" + link_sens;
	x_ajax_file_manager(dataline, show_file_manager);
}

function show_file_manager(data) {
	document.getElementById('team_file_manager').innerHTML = data;
}

function select_files() {
	var i = 0;
	while (document.getElementById('file' + i)) {
		document.getElementById('file' + i).checked = true;
		i = i + 1;
	}
	document.getElementById('file_sel_all').checked = true;
}

function unselect_files() {
	var i = 0;
	while (document.getElementById('file' + i)) {
		document.getElementById('file' + i).checked = false;
		i = i + 1;
	}
	document.getElementById('file_sel_all').checked = false;
}

function select_files_checkbox() {
	if (document.getElementById('file_sel_all').checked == true) {
		select_files();
	}
	else {
		unselect_files();
	}
}

function apply_select() {
	var i = 0;
	var dataline = "";
	while (document.getElementById('file' + i)) {
		if (document.getElementById('file' + i).checked == true) {
			dataline += document.getElementById('file' + i).name + "@@::apply_sel::@@";
		}
		i = i + 1;
	}
	var select = document.getElementById('file_sel_apply');
	if (dataline) {
		var folderID = document.getElementById('folderID').innerHTML;
		var teamID = document.getElementById('teamID').innerHTML;
		dataline += "@@::ttt::@@" + folderID + "@@::ttt::@@" + teamID + "@@::ttt::@@" + select.value;
		if (select.value == "1") {
			x_ajax_move_selection(dataline, move_selection);
		}
		else if (select.value == "2") {
			x_ajax_delete_selection(dataline, delete_selection);
		}
		else if (select.value == "3") {
			x_ajax_move_selection(dataline, move_selection);
		}
	}
	select.value = "0";
	
}

function move_selection(dataline) {
	displayDiv('apply_file_selection',dataline);
}

function file_delete(data) {
	if (data) {
		var folderID = document.getElementById('folderID').innerHTML;
		var teamID = document.getElementById('teamID').innerHTML;
		var dataline = data + "@@::ttt::@@" + folderID + "@@::ttt::@@" + teamID;
		x_ajax_delete_selection(dataline, delete_selection);
	}
}

function move_to_selection(folderID,teamID) {
	var dataline = folderID + "@@msel@@" + teamID + "@@msel@@" + document.getElementById('selection_cache').innerHTML;
	folderID = document.getElementById('folderID').innerHTML;
	teamID = document.getElementById('teamID').innerHTML;
	dataline += "@@::ttt::@@" + folderID + "@@::ttt::@@" + teamID;
	closeDiv('apply_file_selection');
	x_ajax_move_selToFolder(dataline, file_manager);
}

function copy_to_selection(folderID,teamID) {
	var dataline = folderID + "@@msel@@" + teamID + "@@msel@@" + document.getElementById('selection_cache').innerHTML;
	folderID = document.getElementById('folderID').innerHTML;
	teamID = document.getElementById('teamID').innerHTML;
	dataline += "@@::ttt::@@" + folderID + "@@::ttt::@@" + teamID;
	closeDiv('apply_file_selection');
	x_ajax_copy_selToFolder(dataline, file_manager);
}

function delete_selection(dataline) {
	file_manager();
}

function rename_file(id) {
	var name = document.getElementById('inputrenamefile' + id).value;
	if (name) {
		document.getElementById('saved_filename' + id).innerHTML = name;
		var dataline = id + "@@::rf::@@" + escape(name) + "@@::rf::@@" + document.getElementById('folderID').innerHTML + "@@::rf::@@" + document.getElementById('teamID').innerHTML;
		x_ajax_rename_file(dataline, renamed_file);
	}
}

function renamed_file(dataline) {
	var dlarray = dataline.split('::@@::');

	if (!dlarray[1]) {
		document.getElementById('filename' + dlarray[0]).innerHTML = unescape(dlarray[2]);
	}
	else {
		document.getElementById('inputrenamefile' + dlarray[0]).value = document.getElementById('saved_filename' + dlarray[0]).innerHTML;
		alert(dlarray[1]);
	}
}

function close_rename(id) {
	setTimeout(function() {do_close_rename(id);}, 300);
}

function do_close_rename(id) {
	document.getElementById('filename' + id).style.display='inline-block';
	document.getElementById('renamefile' + id).style.display='none';
	document.getElementById('inputrenamefile' + id).value = document.getElementById('saved_filename' + id).innerHTML;
}

function rename_folder(id) {
	var name = document.getElementById('inputrenamefolder' + id).value;
	if (name) {
		document.getElementById('saved_foldername' + id).innerHTML = name;
		var dataline = id + "@@::rf::@@" + escape(name) + "@@::rf::@@" + document.getElementById('folderID').innerHTML + "@@::rf::@@" + document.getElementById('teamID').innerHTML;
		x_ajax_rename_folder(dataline, renamed_folder);
	}
}

function renamed_folder(dataline) {
	var dlarray = dataline.split('::@@::');

	if (!dlarray[1]) {
		document.getElementById('foldername' + dlarray[0]).innerHTML = unescape(dlarray[2]);
	}
	else {
		document.getElementById('inputrenamefolder' + dlarray[0]).value = document.getElementById('saved_foldername' + dlarray[0]).innerHTML;
		alert(dlarray[1]);
	}
}

function close_renamefold(id) {
	setTimeout(function() {do_close_renamefold(id);}, 300);
}

function do_close_renamefold(id) {
	document.getElementById('foldername' + id).style.display='inline-block';
	document.getElementById('renamefolder' + id).style.display='none';
	document.getElementById('inputrenamefolder' + id).value = document.getElementById('saved_foldername' + id).innerHTML;
}

function upload_file(teamID) {
	var dataline = teamID + "@@::fu::@@" + document.getElementById('threadID').innerHTML;
	x_ajax_file_upload(dataline, file_upload_display);
}

function file_upload_display(data) {
	document.getElementById('file_upload_thread').innerHTML = data;
	displayDiv('file_upload');
}

function file_status(id) {
	var teamID = document.getElementById('teamID').innerHTML;
	var dataline = id + "@@::stat::@@" + teamID;
	x_ajax_file_status(dataline, file_status_display);

}

function file_status_display(dataline) {
	var dlarray = dataline.split('::@@::');
	document.getElementById('filestatuscontent' + dlarray[0]).innerHTML = dlarray[2];
	if (dlarray[0] == "1")
		document.getElementById('filenamecell' + dlarray[0]).style.color = "green";
	else
		document.getElementById('filenamecell' + dlarray[0]).style.color = "black";
}

function check_favorites() {
	var timeAgo_fav = document.getElementById('timeAgo_fav').className;
	var timeAgo_pt = document.getElementById('timeAgo_pt').className;
	x_ajax_check_favorites(timeAgo_fav, proc_check_favorites);
	x_ajax_check_pt(timeAgo_pt, proc_check_pt);
	clearInterval(n);
	n = setInterval(function() {check_favorites();}, b6_tu);
}

function proc_check_favorites(dataline) {
	var thread_array = dataline.split("@@:.cn.:@@");
	var timeAgo_fav = document.getElementById('timeAgo_fav');
	var listthreads = timeAgo_fav.innerHTML;
	var cache_array = listthreads.split(",");
	var virg = "";
	if (cache_array[0]) {
		virg = ",";
	}
	var j = 1;
	while (thread_array[j]) {
		var thread = thread_array[j].split("@@::@@");
		if (thread[0]) {
			var i = 0;
			var notified = false;
			while (cache_array[i]) {
				if (cache_array[i] == thread[0]) {
					notified = true;
				}
				i = i + 1;
			}
			if (notified == false) {
				listthreads += virg + thread[0];
				virg = ",";
				chrome_notif_fav("" + thread[1] + "");
			}
		}
		j = j + 1;
	}
	timeAgo_fav.innerHTML = listthreads;
	timeAgo_fav.className = thread_array[0];
}

function proc_check_pt(dataline) {
	var thread_array = dataline.split("@@:.cn.:@@");
	var timeAgo_pt = document.getElementById('timeAgo_pt');
	var listthreads = timeAgo_pt.innerHTML;
	var cache_array = listthreads.split(",");
	var virg = "";
	if (cache_array[0]) {
		virg = ",";
	}
	var j = 1;
	while (thread_array[j]) {
		var thread = thread_array[j].split("@@::@@");
		if (thread[0]) {
			var i = 0;
			var notified = false;
			while (cache_array[i]) {
				if (cache_array[i] == thread[0]) {
					notified = true;
				}
				i = i + 1;
			}
			if (notified == false) {
				listthreads += virg + thread[0];
				virg = ",";
				chrome_notif_pt("" + thread[1] + "");
			}
		}
		j = j + 1;
	}
	timeAgo_pt.innerHTML = listthreads;
	timeAgo_pt.className = thread_array[0];
}

function chrome_notif_fav(text) {

	var now = new Date();
	var hour = now.getHours();
	var min = now.getMinutes();
	if (min < 10) {
		min = "0" + min;
	}
	var notification = webkitNotifications.createNotification(
      'engine/grafts/' + b6_graft + '/images/32ico.png',
      hour + ':' + min,
      b6_notif_post + " \"" + text.replace(/&quot;/g, "\"") + "\"." 
    );
	notification.show();

	if (b6_notify_lenght != "0") {
		window.setTimeout(function() {notification.close();}, b6_notify_lenght);
		window.setTimeout(function() {notification.cancel();}, b6_notify_lenght);
	}
}

function chrome_notif_pt(text) {

	var now = new Date();
	var hour = now.getHours();
	var min = now.getMinutes();
	if (min < 10) {
		min = "0" + min;
	}
	var notification = webkitNotifications.createNotification(
      'engine/grafts/' + b6_graft + '/images/32ico.png',
      hour + ':' + min,
      b6_notif_pt + " \"" + text.replace(/&quot;/g, "\"") + "\"."
    );
	notification.show();
	if (b6_notify_lenght != "0") {
		window.setTimeout(function() {notification.close();}, b6_notify_lenght);
		window.setTimeout(function() {notification.cancel();}, b6_notify_lenght);
	}
}

function allowNotification() {
  if("webkitNotifications" in window) {
    webkitNotifications.requestPermission();
  }
}

function hide_user(userID,type) {
	var dataline = userID + "@@::hu::@@" + type;
	x_ajax_hide_user(dataline, update_hide_user);
}

function update_hide_user(data) {
	document.getElementById('hide_user').innerHTML = data;
}

function list_modedposts(userID,type,page) {
	var dataline = userID + "@@::moded::@@" + type + "@@::moded::@@" + page;
	pleasewait();
	x_ajax_list_modedposts(dataline,show_list_moded);
}

function list_modedthreads(userID,type,page) {
	var dataline = userID + "@@::moded::@@" + type + "@@::moded::@@" + page;
	pleasewait();
	x_ajax_list_modedthreads(dataline,show_list_moded);
}

function show_list_moded(data) {
	document.getElementById('list_moded').style.display = "block";
	document.getElementById('list_moded').innerHTML = data;
	pleasewait_off();
}

