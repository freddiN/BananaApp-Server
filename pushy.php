<?php

	include_once "logging.php";

	function sendMessagePushy($from_user, $to_user, $comment, $message_id) {
		$cfg = parse_ini_file("config.ini.php", true);
		$notifications = $cfg["notifications"];
		
		if (empty($notifications["pushy_url"]) || 
			empty($notifications["pushy_key"]) || 
			empty($notifications["topic"])) {
			return;
		}
		
		// https://pushy.me/docs/api/send-notifications
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
			"to"			=> "/topics/banana",
			"data"			=> $data,
			"time_to_live"  => 86400
		);

		$headers = array
		(
			"Content-Type: application/json"
		);
		 
		//http://php.net/manual/de/function.curl-setopt.php
		
		//echo json_encode($msg);
	
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $notifications["pushy_url"] . "?api_key=" . $notifications["pushy_key"]);

		if (isset($notifications["pushy_proxy"])) {
			curl_setopt($ch, CURLOPT_PROXY, $notifications["pushy_proxy"]);
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
		
		doLogging("Pushy Message gesendet, msgid=" . $message_id . " result=" . $result);
		
		unset($cfg);
		unset($notifications);
		
		return $result;
	}	
?>