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

/*
 * Load a SQL file
 */ 
function load_sql( $filename ) {
	global $LANG;

	$fp = fopen( $filename, 'r' );
	if ( false === $fp ) {
		return "$LANG[DBCOPY_ERROR1]\n";
	}

	$cmd = "";
	$done = false;

	while ( ! feof( $fp ) ) {
		$line = trim( fgets( $fp, 1024 ) );
		$sl = strlen( $line ) - 1;

		if ( $sl < 0 ) { continue; }
		if ( '-' == $line{0} && '-' == $line{1} ) { continue; }

		if ( ';' == $line{$sl} && ($sl < 2 || ';' != $line{$sl - 1})) {
			$done = true;
			$line = substr( $line, 0, $sl );
		}

		if ( '' != $cmd ) { $cmd .= ' '; }
		$cmd .= "$line\n";

		if ( $done ) {
			$cmd = str_replace(';;', ";", $cmd);
			$res = query( $cmd );

			if ( false === $res) {
				$err = lastError();
				if (!strstr($err, "Duplicate column name"))
					return "$LANG[DBCOPY_ERROR2] \"{$cmd}\" $LANG[DBCOPY_ERROR3] \"<span style='color:red;'>$err</span>\".\n";
			}

			$cmd = '';
			$done = false;
		}
	}
	fclose( $fp );
	return true;
}


function query( $sql ) {
	$mLastQuery = $sql;

	# Do the query and handle errors
	$ret = do_query( $sql );

	# Try reconnecting if the connection was lost
	if ( false === $ret) {
	   echo $ret;
	}

	return $ret;
}

/**
 * The DBMS-dependent part of query()
 * @param string $sql SQL query.
 */
function do_query( $sql ) {
	
   $res = mysql_query( $sql );
   return $res;
}


function lastError() {
    $error = mysql_error();
	return $error;
}



function write_settings($db,$user,$server,$password) {
	global $LANG;
    $settingspath = "../engine/core/settings.php";
    
    if (copy( "../engine/core/settings-RELEASE.php", $settingspath )) {	
    
		// Open settings file for reading/writing
		$fp = fopen( $settingspath, "r" );
		if($fp === FALSE) {
			echo "$LANG[SETTINGS_ERROR1]";
			return FALSE;
		}
		else {
			$buf = '';
			$search = array( "{EDIT_DBNAME}","{EDIT_DBUSER}","{EDIT_DBSERVER}","{EDIT_DBPASSWORD}");
			$replace = array( $db , $user, $server, $password );

			while( !feof($fp)) {
				$buf .= str_replace($search, $replace, fgets( $fp, 4096));
			}

			fclose( $fp );

			// Reopen and then overwrite file with new settings
			// if we're here we can open this file.  
			$fp = fopen( $settingspath, "w" );
			if( $fp === FALSE ) {
				echo "$LANG[SETTINGS_ERROR2]";
				return FALSE;
			}
			else {
				fwrite( $fp, $buf );
				fclose( $fp );
			}
		}
	}
	else {
		echo "$LANG[SETTINGS_ERROR3]";
		return FALSE;
	}
    
    return TRUE;
}


?>