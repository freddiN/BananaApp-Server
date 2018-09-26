<?php

	include_once "logging.php";

	function sendMessageFirebase($from_user, $to_user, $comment, $message_id) {
		$cfg = parse_ini_file("config.ini.php", true);
		$notifications = $cfg["notifications"];
		
		if (empty($notifications["firebase_url"]) || 
			empty($notifications["firebase_key"]) || 
			empty($notifications["topic"])) {
			return false;
		}
		
		$data = array
		(
			"from_user" => $from_user,
			"to_user"   => $to_user,
			"comment"   => $comment,
			"subtopic"  => $notifications["topic"],
			"msg_id"	=> $message_id
		);
		
		$msg = array
		(
			"to"	=> "/topics/banana",
			"time_to_live" => 86400,
			"priority" => "high",
			"data"	=> $data
		);

		$headers = array
		(
			"Authorization: key=" . $notifications["firebase_key"],
			"Content-Type: application/json"
		);
		 
		//http://php.net/manual/de/function.curl-setopt.php
		
		//echo json_encode($msg);
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $notifications["firebase_url"]);
		
		if (isset($notifications["firebase_proxy"])) {
			curl_setopt($ch, CURLOPT_PROXY, $notifications["firebase_proxy"]);
		}
		
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($msg));
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
		$result = curl_exec($ch);
		//$info = curl_getinfo($result);
		//print_r($info);
		curl_close($ch);
		
		doLogging("Firebase Message gesendet, msgid=" . $message_id . " result=" . $result);
		
		unset($cfg);
		unset($notifications);
		
		return $result;
	}	
?>