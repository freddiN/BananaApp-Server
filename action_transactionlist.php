<?php
	include_once "json_classes.php";
	include_once "generate_error.php";
	include_once "persist.php";

	function handleTransactionList($jsonRQ) {
		$name = "get_transaction_list";
		
		if (!persistIsTokenValid($jsonRQ)) {
			return generateError($name, "login error (token invalid or expired)");
		}

		$action = new BananaAction();
		$action->actionname = $name;
		$action->status = $name . " ok";

		$action->action_result = persistGetTransactionList($jsonRQ);

		return json_encode($action);
	}
?>