<?php
	include_once "json_classes.php";
	include_once "generate_error.php";
	include_once "persist.php";

	function handleActionBananaRain($jsonRQ) {
		$name = "banana_rain";

		if (!isValidMagicKey($jsonRQ)) {
			if (!persistIsTokenValid($jsonRQ)) {
				return generateError($name, "login error (token invalid or expired or invalid key)");
			}
			
			if (!persistIsUserAdmin($jsonRQ)) {
				return generateError($name, "login error (missing admin rights)");
			}
		}
		
		$action = new BananaAction();
		$action->actionname = $name;
		$action->status = $name . " ok";

		/*$action->action_result = */persistBananaRain($jsonRQ);

		return json_encode($action/*, JSON_PRETTY_PRINT*/);
	}
	
	function isValidMagicKey($jsonRQ) {
		$rueckgabe = FALSE;
		$cfg = parse_ini_file("config.ini.php", true);
		$bananarain = $cfg["bananarain"];
		
		if (isset($jsonRQ->login->magic_key) && $bananarain["magic_key"] === $jsonRQ->login->magic_key) {
			$rueckgabe = TRUE;
		} 
		
		unset($cfg);
		unset($bananarain);
		
		return $rueckgabe;
	}
?>