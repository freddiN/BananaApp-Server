<?php
	include_once "mysql.php";
	include_once "json_classes.php";
	include_once "pushy.php";
	include_once "firebase.php";
	
	function persistIsUserConfigured($jsonRQ) {
		$bRetun = false;
		
		$users = persistGetUserList("");
		foreach ($users as $useritem) {
			if ($useritem->ad_user === $jsonRQ->login->user) {
				$bRetun = true;
				break;
			}
		}

		unset($users);
		return $bRetun;
	}
	
	function persistGetUserList($team_name="") {
		$users = array();
		
		$mysqlUsers = mysqlSelectUsers($team_name);
		foreach ($mysqlUsers as $user) {
			array_push($users, new BananaActionUser(
				$user["id"],
				$user["display_name"],
				$user["ad_user"],
				$user["is_admin"],
				$user["bananas_to_spend"],
				$user["bananas_received"],
				$user["login_token"],
				$user["token_expiration_timestamp"],
				$user["token_duration"],
				$user["team_name"],
				$user["visibility"]));
		}

		unset($mysqlUsers);
		return $users;
	}
	
	function persistGetTeamList() {
		$teams = array();
		
		$mysqlTeams = mysqlSelectTeams();
		foreach ($mysqlTeams as $team) {
			array_push($teams, $team["team_name"]);
		}

		unset($mysqlTeams);
		return $teams;
	}
	
	function persistLogin($jsonRQ) {
		$result = array();
		$users = persistGetUserList("");
		foreach ($users as $useritem) {
			if ($useritem->ad_user === $jsonRQ->login->user) {
				$id = $useritem->id;
				$duration_in_hours = $useritem->token_duration;

				$token = uuidSecure();
				$new_time = date("d.m.Y H:i:s", strtotime(sprintf("+%d hours", intval($duration_in_hours))));

				$success = mysqlUpdateUser($id , null, null, null, null, $token, $new_time, null, null);
				if ($success) {
					array_push($result, new BananaLogin($token,$new_time));
				}
			}
		}
		
		unset($users);
		return $result;
	}
	
	function persistLogout($jsonRQ) {
		$result = array();
		$users = persistGetUserList("");
		foreach ($users as $useritem) {
			if ($useritem->login_token === $jsonRQ->login->token) {
				array_push($result, mysqlUpdateUser($useritem->id, null, null, null, null, " ", " ", null, null));
			}
		}
		
		unset($users);
		return $result;
	}
	
	/**
	 * in jeder Action aufgerufen
	 *
	 * @param $jsonRQ
	 * @return bool
	 */
	function persistIsTokenValid($jsonRQ) {
		$result = false;
		$users = persistGetUserList("");
	
		$now_dt = new DateTime(date("d.m.Y H:i:s"));
		foreach ($users as $useritem) {
			$expire_dt = new DateTime($useritem->token_expiration_timestamp, new DateTimeZone("Europe/Berlin"));

			if (isset($jsonRQ->login->token) && $useritem->login_token === $jsonRQ->login->token && 
				$now_dt < $expire_dt) {
					
				$result = true;
			}
		}
		
		unset($users);
		return $result;
	}
	
	function persistGetUserdetailFromToken($token, $what) {
		$result = "";
		$users = persistGetUserList("");
	
		$now_dt = new DateTime(date("d.m.Y H:i:s"));
		foreach ($users as $useritem) {
			$expire_dt = new DateTime($useritem->token_expiration_timestamp, new DateTimeZone("Europe/Berlin"));

			if (isset($token) && $useritem->login_token === $token && $now_dt < $expire_dt) {
				if ($what == "display_name") {
					$result = $useritem->display_name;
				} else if ($what == "visibility") {
					$result = $useritem->visibility;
				}
			}
		}
		
		unset($users);
		return $result;
	}
	
	function persistGetUserdetailFromUsername($display_name, $what) {
		$result = "";
		$users = persistGetUserList("");
	
		$now_dt = new DateTime(date("d.m.Y H:i:s"));
		foreach ($users as $useritem) {
			if ($useritem->display_name === $display_name) {
				if ($what == "id") {
					$result = $useritem->id;
				} else if ($what == "ad_user") {
					$result = $useritem->ad_user;
				} else if ($what == "is_admin") {
					$result = $useritem->is_admin;
				} else if ($what == "login_token") {
					$result = $useritem->login_token;
				} else if ($what == "token_expiration_timestamp") {
					$result = $useritem->token_expiration_timestamp;
				} else if ($what == "token_duration") {
					$result = $useritem->token_duration;
				} else if ($what == "team_name") {
					$result = $useritem->team_name;
				} else if ($what == "visibility") {
					$result = $useritem->visibility;
				} 
			}
		}
		
		unset($users);
		return $result;
	}

	function persistGetTransactionList($jsonRQ) {
		$transactions = array();
		
		$limit = 100000;
		if (isset($jsonRQ->action_request->limit)) {
			$limit = intval($jsonRQ->action_request->limit);
			if ($limit < 1) {
				$limit = 100000;
			}
		}
		
		$check_visibility = false;
		if (isset($jsonRQ->action_request->check_visibility)) {
			$check_visibility = ($jsonRQ->action_request->check_visibility === "true");
		}
		
		$localUsername = persistGetUserdetailFromToken($jsonRQ->login->token, "display_name");

		$mysqlTransactions = mysqlSelectTransactions($limit);
		foreach ($mysqlTransactions as $transaction) {
			if ($check_visibility == true) {
				$to_user = $transaction["to_user"];
				$visibility_of_to_user = persistGetUserdetailFromUsername($to_user, "visibility");		
	
				if ($localUsername != $transaction["from_user"] && $to_user != $localUsername && $visibility_of_to_user == "0") {
					array_push($transactions, new BananaActionTransaction(
						$transaction["id"],
						$transaction["timestamp"],
						$transaction["from_user"],
						"(anonymized)",
						$transaction["banana_count"],
						$transaction["comment"],
						$transaction["source"],
						$transaction["category"],
						$transaction["from_user_team"],
						$transaction["to_user_team"]));
				} else {
					array_push($transactions, new BananaActionTransaction(
					$transaction["id"],
					$transaction["timestamp"],
					$transaction["from_user"],
					$transaction["to_user"],
					$transaction["banana_count"],
					$transaction["comment"],
					$transaction["source"],
					$transaction["category"],
					$transaction["from_user_team"],
					$transaction["to_user_team"]));
				}	
					
			} else {
				array_push($transactions, new BananaActionTransaction(
					$transaction["id"],
					$transaction["timestamp"],
					$transaction["from_user"],
					$transaction["to_user"],
					$transaction["banana_count"],
					$transaction["comment"],
					$transaction["source"],
					$transaction["category"],
					$transaction["from_user_team"],
					$transaction["to_user_team"]));
			}
		}
	
		unset($mysqlTransactions);
		return $transactions;
	}
	
	function persistGetAccountDetails($jsonRQ) {
		$userdetails = array();
		$users = persistGetUserList("");
		foreach ($users as $useritem) {
			if ($useritem->login_token === $jsonRQ->login->token) {
				array_push($userdetails, $useritem);
				break;
			}
		}

		unset($users);
		return $userdetails;
	}
	
	function persistIsUserAdmin($jsonRQ) {
		$result = false;
		$users = persistGetUserList("");
		foreach ($users as $useritem) {
			if ($useritem->login_token === $jsonRQ->login->token && 
				$useritem->is_admin === 1) {
				$result = true;
			}
		}

		unset($users);
		return $result;
	}
	
	function persistGetTransactionDetails($jsonRQ) {
		$transactiondetails = array();
		
		$transactions = persistGetTransactionList($jsonRQ);
		foreach ($transactions as $transaction) {
			if ($transaction->id === $jsonRQ->action_request->transaction_id) {
				array_push($transactiondetails, $transaction);
				break;
			}
		}

		unset($transactions);
		return $transactiondetails;
	}
	
	function persistMoveBananas($from, $to, $count, $comment, $source, $category) {
		$transaction_id = array();

		$from_spend = -1;
		$from_userid = -1;

		// User finden
		$users = persistGetUserList("");
		foreach ($users as $user) {
			//echo $user->id . " " . $user->display_name . " " . $user->ad_user . " " . $user->bananas_to_spend . " " . $user->bananas_received . " " . $user->is_admin . "<br>\n";
			if ($user->display_name === $from) {
				$from_spend = intval($user->bananas_to_spend);
				$from_userid = intval($user->id);
			} else if ($user->display_name === $to) {
				$to_userid = intval($user->id);
			}
		}
		
		// user checken, das ist nicht die Aufgabe des MySQL Handler
		if (!isset($from_spend) || !isset($from_userid) || $from_spend < 0 || $from_userid < 0) {
			error_log("Userdaten fehlen fuer Quelle: " . $from);
			return false;
		}
		
		if (!isset($to_userid) || $to_userid < 0) {
			error_log("Userdaten fehlen fuer Ziel: " . $to);
			return false;
		}
		
		if (intval($from_spend) < intval($count)) {
			error_log("from user hat nicht genug Bananen " . $count);
			return false;
		}
		
		if ($from_userid === $to_userid) {
			error_log("from user und to user identisch ");
			return false;
		}

        if (!isset($category) || strlen(trim($category)) < 1 || persistCheckCategoryAndErrorOccured($category)) {
            error_log("category error");
            return false;
        }

		// doublebooking check
		$pseudo_rq = new stdClass();
		$pseudo_rq->action_request = new stdClass();
		$pseudo_rq->action_request->limit = 10;
		$transactions = persistGetTransactionList($pseudo_rq);
		foreach ($transactions as $transaction) {
			if ($transaction->from_user === $from &&
				$transaction->to_user === $to && 
				$transaction->comment === $comment) {
				error_log("Abbruch wegen Doppelbuchung");
				return false;
			} 
		}

		$from_spend -= intval($count);

		array_push($transaction_id, mysqlBananaTransaction($from_userid, $from, $from_spend, $to, $count, $comment, date("Y-m-d H:i:s"), $source, $category));
		
		persistSendMessage($from, $to, $comment, $transaction_id[0]);
		
		return $transaction_id;
	}
	
	function persistBananaRain() {
		$cfg = parse_ini_file("config.ini.php", true);
		$bananarain = $cfg["bananarain"];
		
		$from = "Booking";
		if (isset($bananarain["from"])) {
			$from = $bananarain["from"];
		}
		
		$result = mysqlBananaRain($from, "Everyone", "Here, have a fresh set of bananas!", date("Y-m-d H:i:s"), "rain");
		
		//persistSendMessage($from, "Everyone", "Here, have a fresh set of bananas!", $result);
		
		return ($result != -1);
	}

	function persistEditUser($jsonRQ) {
		$result = array();

		$new_ad = "";
		if (!empty($jsonRQ->action_request->ad_user)) {
			$new_ad = $jsonRQ->action_request->ad_user;
		}
		
		$new_admin = "";
		if (!empty($jsonRQ->action_request->is_admin)) {
			$new_admin = $jsonRQ->action_request->is_admin;
		}
		
		$new_spend = "";
		if (!empty($jsonRQ->action_request->banana_to_spend)) {
			$new_spend = $jsonRQ->action_request->banana_to_spend;
		}
		
		$new_token = "";
		if (!empty($jsonRQ->action_request->login_token)) {
			$new_token = $jsonRQ->action_request->login_token;
		}
		
		$new_token_expiration = "";
		if (!empty($jsonRQ->action_request->token_expiration_timestamp)) {
			$new_token_expiration = $jsonRQ->action_request->token_expiration_timestamp;
		}
		
		$new_duration = "";
		if (!empty($jsonRQ->action_request->token_duration) && is_numeric($jsonRQ->action_request->token_duration)) {
			$new_duration = intval($jsonRQ->action_request->token_duration);
		}
		
		$new_visibility = "";
		if (is_numeric($jsonRQ->action_request->visibility)) {
			$new_visibility = intval($jsonRQ->action_request->visibility);
		}
		
		$users = persistGetUserList("");
		foreach ($users as $useritem) {
			if ($useritem->display_name === $jsonRQ->action_request->display_name) {
				array_push($result, mysqlUpdateUser(
					$useritem->id, 
					"", //display name, nicht ändern
					$new_ad,
					$new_admin, 
					$new_spend,
					$new_token, 
					$new_token_expiration, 
					$new_duration,
					$new_visibility));
				break;
			}
		}

		return $result;
	}
	
	function persistEditTransaction($jsonRQ) {
		$result = array();

		$transaction_id = $jsonRQ->action_request->transaction_id;
		$comment = $jsonRQ->action_request->comment;
	
		$transactions = persistGetTransactionList($jsonRQ);
		foreach ($transactions as $transaction) {
			if ($transaction->id === $transaction_id) {
				//TODO: Wenn < 1 Stunde -> mysql call durchführen, für admins immer ok
				
				//error_log("Userdaten fehlen fuer Quelle: " . $from);
				array_push($result, mysqlUpdateTransaction($transaction_id, $comment, null));
				break;
			}
		}

		return $result;
	}
	
	/** quelle http://php.net/manual/en/function.uniqid.php */
	function uuidSecure() {

		$pr_bits = "";
		for($cnt=0; $cnt < 16; $cnt++){
			$pr_bits .= chr(mt_rand(0, 255));
		}
       
        $time_low = bin2hex(substr($pr_bits,0, 4));
        $time_mid = bin2hex(substr($pr_bits,4, 2));
        $time_hi_and_version = bin2hex(substr($pr_bits,6, 2));
        $clock_seq_hi_and_reserved = bin2hex(substr($pr_bits,8, 2));
        $node = bin2hex(substr($pr_bits,10, 6));
       
        /**
         * Set the four most significant bits (bits 12 through 15) of the
         * time_hi_and_version field to the 4-bit version number from
         * Section 4.1.3.
         * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
         */
        $time_hi_and_version = hexdec($time_hi_and_version);
        $time_hi_and_version = $time_hi_and_version >> 4;
        $time_hi_and_version = $time_hi_and_version | 0x4000;
       
        /**
         * Set the two most significant bits (bits 6 and 7) of the
         * clock_seq_hi_and_reserved to zero and one, respectively.
         */
        $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;
       
        return sprintf('%08s-%04s-%04x-%04x-%012s',
            $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
    }
	
	function persistSendMessage($from, $to, $comment, $message_id) {
		//sendMessagePushy($from, $to, $comment, $message_id);
		sendMessageFirebase($from, $to, $comment, $message_id);	
	}
	
	function persistGetStats($team) {
		$result = array();
		$months = mysqlStats($team);

		foreach ($months as $monthitem) {
			array_push($result, new MonthlyStats($monthitem["month"], $monthitem["year"], $monthitem["count"]));
		}
				
		unset($months);
		return $result;
	}

	function persistCheckCategoryAndErrorOccured($category) {
		$cfg = parse_ini_file("config.ini.php", true);
		$categories = $cfg["categories"];

		if ($categories["required"] === "true") {
			$category_names = explode(":", $categories["names"]);

			foreach ($category_names as $cat_name) {
				if ($category === $cat_name) {
					return false;
				}
			}

			error_log("category " . $category . " not in whitelist");
			return true;
		}

		return false;
	}

	function persistCreateUser($display_name, $ad_user, $team, $is_admin=0, $to_spend=10, $token_duration=168, $visibility=1) {
		if (empty($display_name)) {
			return FALSE;
		}
		
		if (empty($ad_user)) {
			return FALSE;
		}
		
		if (empty($team)) {
			return FALSE;
		}
		
		if ($visibility < 0 || $visibility > 1) {
			return FALSE;
		}
		
		return mysqlInsertUser($display_name, $ad_user, $team, $is_admin, $to_spend, "", "", $token_duration, $visibility);
	}
	
	function persistDeleteUser($display_name, $team) {
		if (empty($display_name)) {
			return FALSE;
		}
		
		if (empty($team)) {
			return FALSE;
		}
		
		return mysqlDeleteUser($display_name, $team);
	}
	
	function persistAnonymizeTransactions($display_name, $team) {
		if (empty($display_name)) {
			return FALSE;
		}
		
		if (empty($team)) {
			return FALSE;
		}
		
		return mysqlAnonymizeTransactions($display_name, $team);
	}
	
	function persistDeleteTransaction($transaction_id) {
		if (empty($transaction_id)) {
			return FALSE;
		}
		
		return mysqlDeleteTransaction($transaction_id);
	}
	
	function persistRevertTransaction($transaction_id) {
		if (empty($transaction_id)) {
			return FALSE;
		}
		
		return mysqlRevertTransaction($transaction_id);
	}
?>
