<?php

	function ldapValidate($jsonRQ) {
		if (!isset($jsonRQ) || empty($jsonRQ->login->user)) {
			return false;
		}
		
		//Buchstaben, Punkt
		return !preg_match("/[^a-zA-Z.]+/", $jsonRQ->login->user);
	}
	
	function ldapLoginValid($jsonRQ) {
		$user = $jsonRQ->login->user;
		$pass = $jsonRQ->login->pass;

		return checkUser($user, $pass);
	}

	//$user=frederik.netterdon
	function checkUser($user, $pass) {
		$rueckgabe = FALSE;
	
		$cfg = parse_ini_file("config.ini.php", true);
		$ldapdata = $cfg["ldap"];

		$loginuser = $user . $ldapdata["ldap_user_postfix"];

		// verbinden zum LDAP Server
		$ldapconn = ldap_connect($ldapdata["ldap_server"]);
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

		if ($ldapconn) {
			// binden zum ldap server
			$ldapbind = ldap_bind($ldapconn, $loginuser, $pass);

			// Bindung überpfrüfen
			if ($ldapbind) {
				$rueckgabe = TRUE;
			}// else {
				//echo "LDAP bind fehlgeschlagen...<br>";
			//}
		}
		
		ldap_unbind($ldapconn);
		
		unset($cfg);
		unset($ldapdata);

		return $rueckgabe;
	}
?>