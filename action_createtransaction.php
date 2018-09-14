<?php
	include_once "json_classes.php";
	include_once "generate_error.php";
	include_once "persist.php";
	
	function handleCreateTransaction($jsonRQ) {
		$name = "create_transaction";
		
		if (!persistIsTokenValid($jsonRQ)) {
			return generateError($name, "login error (token invalid or expired)");
		}

		$action = new BananaAction();
		$action->actionname = $name;
		
		$user = persistGetAccountDetails($jsonRQ);
		
		// genug Bananen auf dem Konto?
		if ($user[0]->bananas_to_spend < $jsonRQ->action_request->banana_count) {
			return generateError($name, "not enough bananas in account");
		}
		
		// nicht selbst Bananen schicken
		if ($user[0]->display_name == $jsonRQ->action_request->to_user) {
			return generateError($name, "you are not allowed to send yourself bananas");
		}
		
		// Kommentargroesse checken
		if (strlen($jsonRQ->action_request->comment) < 3 || strlen($jsonRQ->action_request->comment) > 500){
			return generateError($name, "comment length must be between 3 and 500 characters");
		}

		$action->action_result = persistMoveBananas($user[0]->display_name, $jsonRQ->action_request->to_user, $jsonRQ->action_request->banana_count, $jsonRQ->action_request->comment, $jsonRQ->login->source);
		if ($action->action_result == false) {
			return generateError($name, "error generating transaction");
		} 

		$action->status = $name . " ok";
		
		return json_encode($action);
	}
?>
