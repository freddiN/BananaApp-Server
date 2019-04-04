<?php
	include_once "json_classes.php";
	include_once "generate_error.php";
	include_once "persist.php";

	function handleActionUserList($jsonRQ) {
		$name = "get_user_list";
		
		if (!persistIsTokenValid($jsonRQ)) {
			return generateError($name, "login error (token invalid or expired)");
		}
		
		$action = new BananaAction();
		$action->actionname = $name;
		$action->status = $name . " ok";

		$action->action_result = persistGetUserList("");
		censorData($action->action_result);
		return json_encode($action/*, JSON_PRETTY_PRINT*/);
	}
	
	/** Blacklist */
	function censorData($array_of_users) {
		foreach ($array_of_users as $user) {
			unset($user->id);// = null;
			unset($user->ad_user_tt);
			unset($user->ad_user_ama);
			unset($user->login_token);
			unset($user->token_expiration_timestamp);
			unset($user->token_duration);
		}
	}
?>