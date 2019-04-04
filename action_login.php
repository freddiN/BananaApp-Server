<?php
	include_once "json_classes.php";
	include_once "generate_error.php";
	include_once "persist.php";
	include_once "ldap.php";

	function handleActionLogin($jsonRQ) {
		$name = "login";
				
		if (!ldapValidate($jsonRQ)) {
			return generateError($name, "username invalid (ldap)");
		}
		
		if (!ldapLoginValid($jsonRQ)) {
			return generateError($name, "login error (ldap)");
		}
		
		//if (!persistIsUserConfigured($jsonRQ)) {
	//		return generateError($name, "login error (database)");
	//	}
		
		$action = new BananaAction();
		$action->actionname = $name;
		$action->status = $name . " ok";

		$action->action_result = persistLogin($jsonRQ);

		return json_encode($action);
	}
?>