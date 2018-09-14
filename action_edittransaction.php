<?php
	include_once "json_classes.php";
	include_once "generate_error.php";
	include_once "persist.php";

	function handleActionEditTransaction($jsonRQ) {
		$name = "edit_transaction";
		
		if (!persistIsTokenValid($jsonRQ)) {
			return generateError($name, "login error (token invalid or expired)");
		}
		
		if (empty($jsonRQ->action_request->transaction_id)) {
			return generateError($name, "missing transaction id");
		}
		
		// Kommentargroesse checken (Werte siehe createTransaction())
		if (strlen($jsonRQ->action_request->comment) < 3 || strlen($jsonRQ->action_request->comment) > 500){
			return generateError($name, "comment length must be between 3 and 500 characters");
		}
		
		// gültige id
		$transaction = persistGetTransactionDetails($jsonRQ);
		if (count($transaction) != 1) {
			return generateError($name, "transaction not found");
		}
		
		$user = persistGetAccountDetails($jsonRQ);
		if ($user[0]->is_admin !== 1) {
			
			// Non Admin: nur eigenene Komemntare ändern
			if ($user[0]->display_name !== $transaction[0]->from_user) {
				return generateError($name, "you are only allowed to modify your own transactions");
			}
			
			// Zeitpunkt: Nicht länger als eine Stunde her
			$diff = strtotime("now") - strtotime($transaction[0]->timestamp);
			if ($diff > 3600) {
				return generateError($name, "you are not allowed to change old comments");
			}
		}

		$action = new BananaAction();
		$action->actionname = $name;
		$action->status = $name . " ok";

		$action->action_result = persistEditTransaction($jsonRQ);

		return json_encode($action);
	}
?>