<?php

function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
} 

rrmdir("install");
if (file_exists("mf-version.txt"))
	unlink("mf-version.txt");
unlink("delinstall.php");
header("Location: index.php");
?>