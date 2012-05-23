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
    // adduserlib.php
   
	function ajax_verif_nick($nick) {
		global $LANG;
		$retstr = "true::@@::$LANG[CREATE_USERNAME_ALERT1]";
		if ($nick) {
			$nick = make_var_safe($nick);
			
			if (strlen($nick) < 3)
				$retstr = "false::@@::$LANG[CREATE_USERNAME_ALERT2]";
			else {
				$tid = mf_query("SELECT ID FROM users WHERE LOWER(username) = \"".mb_strtolower($nick,'UTF-8')."\" LIMIT 1");
				if ($tid = mysql_fetch_array($tid))
					$retstr = "false::@@::$LANG[CREATE_USERNAME_ALERT3]";
			}
		}
		else
			$retstr = "false::@@::";
		return $retstr;
	}
	
	function ajax_verif_pass($pass) {
		global $LANG;
		$retstr = "true::@@::";
		if ($pass) {
			$pass = make_var_safe($pass);
			
			if (strlen($pass) < 6)
				$retstr = "false::@@::$LANG[PROMPT_CREATE_PASSWORD_ALERT1]";
		}
		return $retstr;
	}

	function ajax_verif_vpass() {
		global $LANG;

		return $LANG['PROMPT_CREATE_PASSWORD_ALERT2'];
	}

	function ajax_verif_email($email) {
		global $LANG;
		$retstr = "true::@@::";
		if ($email) {
			$error_email = checkemail(make_var_safe($email));
			if ($error_email == "7")
				$retstr = "false::@@::$LANG[ERROR_HAS_OCCURED_TEXT7]";
			else if ($error_email == "8")
				$retstr = "false::@@::$LANG[ERROR_HAS_OCCURED_TEXT8]";
			if ($error_email == "9")
				$retstr = "false::@@::$LANG[ERROR_HAS_OCCURED_TEXT9]";
		}
		return $retstr;
	}

	function ajax_verif_vemail() {
		global $LANG;

		return $LANG['ERROR_HAS_OCCURED_TEXT10'];
	}

	sajax_init();
	
	include("ajax_commonlib.php");

	sajax_export("ajax_verif_nick","ajax_verif_pass","ajax_verif_vpass","ajax_verif_email","ajax_verif_vemail"); // list of functions to export
	sajax_handle_client_request(); // serve client instances


	function checkemail($email) {
		$error = false;
		if (!strpos($email,"@") || !strpos($email,".")) {
			$error = "8";
		}
		else if (strpos($email,"@") > strrpos($email,".")) {
			$error = "8";
		}
		else if ((strrpos($email,".") + 3) > strlen($email)) {
			$error = "8";
		}
		else if (strpos($email,":") || strpos($email,"(") || strpos($email,")") || strpos($email,"[") || strpos($email,"]") || strpos($email,",") || strpos($email," ") || strpos($email,";")) {
			$error = "7";
		}
		else if (stristr($email,"@mailinator") 
			|| stristr($email,"@mailbidon") 
			|| stristr($email,"@mailincubato") 
			|| stristr($email,"@yopmail") 
			|| stristr($email,"@spamgourmet") 
			|| stristr($email,"@ephemail") 
			|| stristr($email,"@brefemail") 
			|| stristr($email,"@kleemail") 
			|| stristr($email,"@haltospam") 
			|| stristr($email,"@guerrillamail") 
			|| stristr($email,"@kasmail") 
			|| stristr($email,"@dodgit") 
			|| stristr($email,"@pookmail") 
			|| stristr($email,"@bugmenot") 
			|| stristr($email,"@jetable")
			|| stristr($email,"@tempomail")
			|| stristr($email,"@0-mail.com")
			|| stristr($email,"@brefmail.com")
			|| stristr($email,"@tempinbox.com")
			|| stristr($email,"@beefmilk.com")
			|| stristr($email,"@lookugly.com")
			|| stristr($email,"@link2mail")
			|| stristr($email,"@spambox")
			|| stristr($email,"@smellfear.com")) {
				$error = "9";
		}
		return $error;
	}

	function generatePassword($length = 12) {

		$password = "";
		$possible = ",;#!éèêôîûÉÎÔÈÊ2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
		$maxlength = strlen($possible);

		if ($length > $maxlength) {
			$length = $maxlength;
		}
		
		$i = 0; 
		while ($i < $length) { 
			$char = substr($possible, mt_rand(0, $maxlength-1), 1);

			if (!strstr($password, $char)) { 
				$password .= $char;
				$i++;
			}
		}

		return $password;
	}
?>