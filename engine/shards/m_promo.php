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

	// m_slide.php

	
    	$thisMenu->menuTitle = "<span id='widget$widgetID' style='display:none;'>". time() . "</span>
								<div class='title_handle' style='width:154px;'>$LANG[M_PROMO_TITLE]</div>";
		if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level0'))
			$thisMenu->menuTitle .= "<div style='position:absolute;margin-left:112px;'><a href='index.php?shard=m_promo_edit'>($LANG[SHARD_EDIT])</a></div>";
		$thisMenu->menuTitle .= "<a onclick='this.blur();' href=\"javascript:closeShard('$widgetID');\"><img src='engine/grafts/".$siteSettings['graft']."/images/shard_exit.png' border='0' title='$LANG[SHARD_CLOSE]' alt='X' /></a>";
		$mslide =	'
		<div style="padding-left:2px;padding-top:1px;font-size:10px;">
			<div id="promoGallery" style="text-align:center;">';
		$k = 0;
		$query_serie = mf_query("select * from promo_shard ORDER BY RAND() LIMIT 6") or die(mysql_error());
		while ($result_serie = mysql_fetch_assoc($query_serie))	{
			$k ++;
			$mslide .= "<div class='imageElement'>
						<h4> </h4>
						<p>$result_serie[type]</p>
						<a href='$result_serie[link]' title='$result_serie[title]' class='open'></a>
						<img src='$result_serie[img]' class='full' alt='full' />
						<img src='$result_serie[img]' class='thumbnail' alt='thumbnail' />
					</div>";
		}
		$mslide .=	"</div></div>";

	if ($k) {
		$mslide .= "<script type='text/javascript'>";
		$mslide .= "
			function startPromoGallery()
			{
				var promoGallery = new gallery($('promoGallery'), {
					timed: true,
					showArrows: false,
					showCarousel: false,
					delay:7000
				});
			}
			window.addEvent('domready',startPromoGallery)";

		$mslide .= "</script>";
	}			
	$thisMenu->menuContentArray[] = $mslide;

	
?>