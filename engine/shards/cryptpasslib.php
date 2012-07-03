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
// cryptpass.php
   
function crypt_password($password,$userID) {
	if ($password) {
		//find the longest valid salt allowed by server
		$max_salt = CRYPT_SALT_LENGTH;

		//blowfish hashing with a salt as follows: "$2a$", a two digit cost parameter, "$", and 22 base 64
		$blowfish = '$2a$10$';

		//get the longest salt, could set to 22 crypt ignores extra data
		$salt = get_salt ( $max_salt );

		//get a second salt to strengthen password
		$salt2 = get_salt ( 30 ); //set to whatever


		//append salt2 data to the password, and crypt using salt, results in a 60 char output
		$crypt_pass = crypt ( $password . $salt2, $blowfish . $salt );

		//insert crypt pass along with salt2 into database.
		mf_query("UPDATE users SET password = \"$crypt_pass\", salt = \"$salt2\", crypt_method = \"blowfish\" WHERE ID = '$userID' LIMIT 1");

		return true;
    }
}

function get_salt($length) {
	$options = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./';

	$salt = '';

	for($i = 0; $i <= $length; $i ++) {
	    $options = str_shuffle ( $options );
	    $salt .= $options [mt_rand ( 0, 63 )];
	}
	return $salt;
}

?>