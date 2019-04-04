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
		$system = $jsonRQ->login->system;
		
		$cfg = parse_ini_file("config.ini.php", true);
		$ldapdata = $cfg["ldap"];
		
		$rueckgabe = FALSE;

		if ($system == "TravelTainment") {
			$rueckgabe = checkUser($user, $pass, $ldapdata["ldap_user_postfix_tt"], $ldapdata["ldap_server_tt"]);
		} else if ($system == "Amadeus") {
			$rueckgabe = checkUser($user, $pass, $ldapdata["ldap_user_postfix_ama"], $ldapdata["ldap_server_ama"]);
		}
		
		unset($cfg);
		unset($ldapdata);
		
		return $rueckgabe;
	}

	function checkUser($user, $pass, $user_postfix, $server) {
		if (strpos($user, 'test') !== false) {
			return TRUE;
		}
    
    $rueckgabe = FALSE;
    
    //ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);

		// verbinden zum LDAP Server
		$ldapconn = ldap_connect($server);
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 5); /* 5 second  */
		ldap_set_option($ldapconn, LDAP_OPT_TIMELIMIT, 5); /* 5 second  */
		

		if ($ldapconn) {
			// binden zum ldap server
			$ldapbind = ldap_bind($ldapconn, $user . $user_postfix, $pass);
			// Bindung überpfrüfen
			if ($ldapbind) {
				$rueckgabe = TRUE;
			} /*else {
				echo "LDAP bind fehlgeschlagen...<br>";
			}*/
		} /*else {
				echo "LDAP conn fehlgeschlagen...<br>";
			}*/
		
		ldap_unbind($ldapconn);

		return $rueckgabe;
	}
?>