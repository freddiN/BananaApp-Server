<?php
	include_once "json_classes.php";

	function generateError($actionname, $msg) {
		$action = new BananaAction();
		$action->actionname = $actionname;
		$action->status = $msg;
		return json_encode($action/*, JSON_PRETTY_PRINT*/);
	}
?>