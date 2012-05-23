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

// m_forum_nav.php


	$thisMenu->menuTitle = "Forum Nav";
	$thisMenu->menuType = "nav";
	$thisMenu->menuContentArray[] = "<a href='".make_link("blog")."' style='outline:none;'><span id='blog_tab'>$LANG[BLOGS]</span></a>";
	$thisMenu->menuContentArray[] = "<a name='nav' id='anchor_nav'></a><a href='".make_link("forum","","#threadlist")."' style='outline:none;'><span id='forum_tab'>$LANG[FORUM_HOME]</span></a>";

	if ($CURRENTUSER != "anonymous" and $CURRENTUSER != "bot")
		$thisMenu->menuContentArray[] = "<a href='".make_link("profile")."' style='outline:none;'>Profil</a>";
?>