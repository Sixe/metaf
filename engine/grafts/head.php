<?php 

if (!isset($siteSettings['thumbnail']))
	$siteSettings['thumbnail'] = "http://$siteSettings[siteurl]/engine/grafts/".$siteSettings['graft']."/images/SF_Thumbnail.png";

$head_file = "<!DOCTYPE html
            PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
            \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
            <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"$siteSettings[lang]\" lang=\"$siteSettings[lang]\" xmlns:fb=\"http://www.facebook.com/2008/fbml\">
            <head>
			<link rel=\"alternate\" type=\"application/rss+xml\" href=\"http://$siteSettings[siteurl]/xml/$rss\" />
			<link rel=\"icon\" type=\"image/png\" href=\"engine/grafts/".$siteSettings['graft']."/images/favicon.png\" />
			<link rel=\"stylesheet\" href=\"slide/css/jd.slideshow.css\" type=\"text/css\" media=\"screen\" />
			<meta name=\"keywords\" content=\"$siteSettings[sitekeywords]\" />
			<meta name=\"description\" content=\"$siteSettings[description]\" />
            <meta name=\"dc.title\" content=\"$siteSettings[titlebase] - $siteSettings[titledesc]\" />
            <meta name=\"dc.subject\" content=\"$siteSettings[sitekeywords]\" />
            <meta name=\"dc.description\" content=\"$siteSettings[description]\" />
	<meta itemprop=\"name\" content=\"$siteSettings[titlebase] - $siteSettings[titledesc]\" />
	<meta itemprop=\"description\" content=\"$siteSettings[description]\" />
	<meta itemprop=\"image\" content=\"$siteSettings[thumbnail]\" />
	<meta property=\"og:title\" content=\"$siteSettings[titlebase] - $siteSettings[titledesc]\" />
	<meta property=\"og:description\" content=\"$siteSettings[description]\" />
	<meta property=\"og:image\" content=\"$siteSettings[thumbnail]\" />
            <link id=\"link_thumbnail\" rel=\"image_src\" href=\"$siteSettings[thumbnail]\" />
            <meta name=\"Robots\" content=\"follow,index,all\" />
            <meta http-equiv=\"Content-Language\" content=\"$siteSettings[lang]\" />
            <meta name=\"reply-to\" content=\"$siteSettings[admin_mail]\" />
            <meta name=\"category\" content=\"Environnement\" />
            <meta name=\"distribution\" content=\"global\" />
            <meta name=\"revisit-after\" content=\"7 days\" />
            <meta name=\"author\" lang=\"fr\" content=\"$siteSettings[titlebase]\" />
            <meta name=\"copyright\" content=\"$siteSettings[titlebase]\" />
            <meta name=\"identifier-url\" content=\"$siteSettings[siteurl]\" />
            <meta name=\"expires\" content=\"never\" />
            <meta name=\"Date-Creation-yyyymmdd\" content=\"20090628\" />
            <meta http-equiv=\"Content-Type\" content=\"text/html;charset=UTF-8\" />
            <title>$siteSettings[titlebase] - $siteSettings[titledesc]</title>";
?>