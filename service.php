<?php
	//ini_set('display_errors', 1);
	//error_reporting(E_ALL);
	
	include_once "json_classes.php";
	
	include_once "generate_error.php";

	include_once "action_userlist.php";
	include_once "action_accountdetails.php";
	include_once "action_transactionlist.php";
	include_once "action_createtransaction.php";
	include_once "action_login.php";
	include_once "action_logout.php";
	include_once "action_bananarain.php";

	header("Content-type: application/json");
	
	if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
		ob_start("ob_gzhandler");
	} else {
		ob_start();
	}

	$action_json = file_get_contents('php://input');

	$jsonRQ = json_decode($action_json);
	$actionname = $jsonRQ->actionname;
	if ($actionname == "get_account_details") {
		echo handleActionAccountDetails($jsonRQ);
	} else if ($actionname == "get_user_list") {
		echo handleActionUserList($jsonRQ);
	} else if ($actionname == "get_transaction_list") {
		echo handleTransactionList($jsonRQ);
	} else if ($actionname == "create_transaction") {
		echo handleCreateTransaction($jsonRQ);
	} else if ($actionname == "login") {
		echo handleActionLogin($jsonRQ);
	} else if ($actionname == "logout") {
		echo handleActionLogout($jsonRQ);
	} else if ($actionname == "edit_user") {
		echo handleActionEditUser($jsonRQ);
	} else if ($actionname == "banana_rain") {
		echo handleActionBananaRain($jsonRQ);
	} else {
		echo generateError("", "unknown action: " . $actionname);
	}
?>