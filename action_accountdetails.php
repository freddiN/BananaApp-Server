<?php
	include_once "json_classes.php";
	include_once "generate_error.php";
	include_once "persist.php";

	function handleActionAccountDetails($jsonRQ) {
		$name = "get_account_details";

		if (!persistIsTokenValid($jsonRQ)) {
			return generateError($name, "login error (token invalid or expired)");
		}
		
		$action = new BananaAction();
		$action->actionname = $name;
		$action->status = "login ok";

		$action->action_result = persistGetAccountDetails($jsonRQ);


		return json_encode($action/*, JSON_PRETTY_PRINT*/);
	}
?>