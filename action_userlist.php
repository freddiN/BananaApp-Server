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
			unset($user->ad_user);// = null;
			unset($user->login_token);// = null;
			unset($user->token_expiration_timestamp);// = null;
			unset($user->token_duration);// = null;
			
/*
				$user["id"],
				$user["display_name"],
				$user["ad_user"],
				$user["is_admin"],
				$user["bananas_to_spend"],
				$user["bananas_received"],
				$user["login_token"],
				$user["token_expiration_timestamp"],
				$user["token_duration"]));*/
		}
	}
?>