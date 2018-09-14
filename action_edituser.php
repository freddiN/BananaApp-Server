<?php
	include_once "json_classes.php";
	include_once "generate_error.php";
	include_once "persist.php";

	function handleActionEditUser($jsonRQ) {
		$name = "edit_user";
		
		if (!persistIsTokenValid($jsonRQ)) {
			return generateError($name, "login error (token invalid or expired)");
		}
		
		$user = persistGetAccountDetails($jsonRQ);
		// Admin immer ok, wenn nicht: eigener Token mit to_user muss stimmen
		if ($user[0]->is_admin !== 1 && $user[0]->display_name !== $jsonRQ->action_request->display_name) {
			return generateError($name, "without admin rights you can only change your own data");
		}
		
		$action = new BananaAction();
		$action->actionname = $name;
		$action->status = $name . " ok";

		$action->action_result = persistEditUser($jsonRQ);

		return json_encode($action);
	}
?>