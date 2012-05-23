<?php

	// m_slide.php

    if ($CURRENTUSER != "anonymous" and $CURRENTUSER != "bot") {

    	$thisMenu->menuTitle = "<span id='widget$widgetID' style='display:none;'>". time() . "</span>
								<div class='title_handle' style='width:154px;'>$LANG[M_SLIDE_TITLE]</div>";
		if (isInGroup($CURRENTUSER, 'admin') || isInGroup($CURRENTUSER, 'level6'))
			$thisMenu->menuTitle .= "<div style='position:absolute;margin-left:112px;'><a href='index.php?shard=m_slide_edit'>($LANG[SHARD_EDIT])</a></div>";
		$thisMenu->menuTitle .= "<a onclick='this.blur();' href=\"javascript:closeShard('$widgetID');\"><img src='engine/grafts/".$siteSettings['graft']."/images/shard_exit.png' border='0' title='$LANG[SHARD_CLOSE]' alt='X' /></a>";
		$mslide =	'
		<div style="padding-left:11px;padding-top:5px;font-size:10px;">
			<div id="serieGallery" style="text-align:center;">';
		$k = 0;
		$query_serie = mf_query("select * from slide_shard where visible = 1 ORDER BY RAND() LIMIT 16") or die(mysql_error());
		while ($result_serie = mysql_fetch_assoc($query_serie)) {
		$k ++;
		$mslide .= "<div class='imageElement'>
						<h4>$result_serie[title]</h4>
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
			function startSerieGallery()
			{
				var serieGallery = new gallery($('serieGallery'), {
					timed: true,
					showArrows: false,
					showCarousel: false,
					delay:7000
				});
			}
			window.addEvent('domready',startSerieGallery)";
					
	$mslide .= "</script>";
	}			
		$thisMenu->menuContentArray[] = $mslide;
	
	}
?>