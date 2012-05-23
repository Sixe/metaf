var cwidth = 0;
var savewidth = 0;
var main_width = 0;
var min_min_main_width = 610;
var max_min_main_width = 819;
var min_main_width = 0;
var main_max_width = 2048;
if (GetCookie('main_max_width'))
	main_max_width = GetCookie('main_max_width');
var max_widgetwidth = 420;
var min_widgetwidth = 216;
var min_widgetwidth2 = 260;
var widgetwidth = max_widgetwidth;
if (document.getElementById('widgets_left').offsetWidth <= 8 || document.getElementById('widgets_right').offsetWidth <= 8) {
	widgetwidth = min_widgetwidth;
}
var switch_widgets = 1100;

window.onbeforeunload = confirmExit;
setTimeout("checksystem()", 300000);

var so = new SWFObject("engine/core/alert3.swf", "mymovie", "1", "1", "4", "#FFFFFF");

if (currentuser != "anonymous") {
	var menu_shard = $$('#menu_shard_left, #menu_shard_right');
	mySortables = new Sortables(menu_shard,{handle:'#title_handle', opacity: .5});
	sendPosition();
}

var sidebar_space = document.getElementById('sidebar_space_left').style.height;
if (savewidth >= switch_widgets && sidebar_space == '0px') {
	savewidth = 0;
}
else if (savewidth < switch_widgets && sidebar_space != '0px') {
	savewidth = 1280;
}
shards_priority();

function shards_priority() {
	var shards_priority = document.getElementById('shards_priority');
	if (shards_priority.checked == true) {
		SetCookie('shards_priority', 'yes', -1);
		min_main_width = min_min_main_width;
	}
	else {
		SetCookie('shards_priority', 'not', 365);
		min_main_width = max_min_main_width;
	}

	verifywidth();
}

function mainMaxWidth() {
	main_max_width = 2048;
	verifywidth();
	setTimeout(function() {SetmainMaxWidth();}, 300);
}

function SetmainMaxWidth() {
	main_max_width = document.getElementById('main').offsetWidth;
	SetCookie('main_max_width',main_max_width,730);
}

function verifywidth() {
	if (document.body.clientWidth)
		var cwidth = document.body.clientWidth;
	else
		var cwidth = document.body.offsetWidth;

	if (savewidth > switch_widgets && cwidth < switch_widgets) {
		document.getElementById('sidebar_right2').innerHTML = document.getElementById('sidebar_left1').innerHTML;
		document.getElementById('sidebar_left1').innerHTML = "";
		if (document.getElementById('sidebar_space_left'))
			document.getElementById('sidebar_space_left').style.height = "0px";
		savewidth = cwidth;
		widgetwidth = min_widgetwidth;
		SetCookie('screen_width',cwidth,365);
		menu_shard = $$('#menu_shard_left, #menu_shard_right');
		mySortables = new Sortables(menu_shard,{handle:'#title_handle', opacity: .5});
		sendPosition();
		if (GetCookie('shards_priority') == "not")
			document.getElementById('shards_priority').checked = true;
	}
	else if (savewidth < switch_widgets && cwidth > switch_widgets) {
		document.getElementById('sidebar_left1').innerHTML = document.getElementById('sidebar_right2').innerHTML;
		document.getElementById('sidebar_right2').innerHTML = "";
		if (document.getElementById('sidebar_space_left'))
			document.getElementById('sidebar_space_left').style.height = "200px";
		savewidth = cwidth;
		widgetwidth = max_widgetwidth;
		SetCookie('screen_width',cwidth,365);
		if (currentuser != "anonymous") {
			menu_shard = $$('#menu_shard_left, #menu_shard_right');
			mySortables = new Sortables(menu_shard,{handle:'#title_handle', opacity: .5});
			sendPosition();
		}
		if (GetCookie('shards_priority') == "not")
			document.getElementById('shards_priority').checked = true;
	}
	if (cwidth > switch_widgets && (document.getElementById('widgets_left').offsetWidth <= 8 || document.getElementById('widgets_right').offsetWidth <= 8)) {
		widgetwidth = min_widgetwidth2;
	}
	content_width(cwidth);
}

function content_width(cwidth) {

	if (cwidth < 1300) {
		document.getElementById('shards_priority_div').style.display = "block";
		document.getElementById('main_max_width').style.display = "none";
	}
	else {
		document.getElementById('shards_priority_div').style.display = "none";
		document.getElementById('main_max_width').style.display = "block";
	}
	main_width = cwidth - widgetwidth;
	if (main_width < min_main_width)
		main_width = min_main_width;
	if (main_width > main_max_width)
		main_width = main_max_width*1;
	
	document.getElementById('main').style.width = main_width + "px";
	document.getElementById('footer').style.width = (main_width - 99) + "px";
	document.getElementById('border_bottom').style.width = (main_width ) + "px";

	newClass('.postContentWidth','max-width:' + (main_width - 160) + 'px;');
	var imgmaxwidth = main_width - 150;
	newClass('img','max-width:' + imgmaxwidth + 'px;');
	document.getElementById('onglet_table').style.width = (main_width + 8) + "px";
	if (document.getElementById('onglet_right_logged'))
		document.getElementById('onglet_right_logged').style.width = (main_width - 562) + "px";
	else
		document.getElementById('onglet_right_anonymous').style.width = (main_width - 473) + "px";
}

function sortMyShards(position) {
	var sortShard = document.getElementById('menu_shard_' + position).getChildren();
	var shardOrder = "";
	var virg = "";
	for (i = 0; i < sortShard.length; i++) {
		if (sortShard[i].getProperty('id')){
			var current_shard = sortShard[i].getProperty('id').replace('shardmenu_','');

			if (current_shard != "sidebar_space_left" && current_shard != "sidebar_space_right") {
				shardOrder = shardOrder + virg + current_shard;
				virg = ",";
			}
		}
	}

	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace(',closed','');
	shardOrder = shardOrder.replace('closed,','');
	shardOrder = shardOrder.replace('closed','');

	if (shardOrder == "0,")
		shardOrder = "0";

	return shardOrder;
}

function closeShard(shard_id) {
	if (currentuser != "anonymous") {
		div_name = "shardmenu_" +shard_id;
		document.getElementById(div_name).innerHTML = "";
		document.getElementById(div_name).setAttribute('id','closed');
		sendPosition();
	}
}

function restoreShards() {
	SetCookie('shard_left','',-1);
	SetCookie('shard_right','',-1);
	window.location.reload();
}

if (currentuser != "anonymous") {
	if (document.cookie.indexOf('shard_left') == -1) {
		var cookie_shard_left = GetCookie('shard_left');
		var shard_list_left = mySortables.serialize();
		SetCookie('shard_left',shard_list_left,365);
	}
	if (document.cookie.indexOf('shard_right') == -1) {
		var cookie_shard_right = GetCookie('shard_right');
		var shard_list_right = mySortables.serialize();
		SetCookie('shard_right',shard_list_right,365);
	}
}

function sendPosition() {
	if (currentuser != "anonymous") {
		duree = setTimeout("execPosition()",100);
	}
}

function execPosition() {
	var shard_list = sortMyShards('left');
	SetCookie('shard_left',shard_list,365);
	var shard_list = sortMyShards('right');
	SetCookie('shard_right',shard_list,365);
}

