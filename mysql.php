<?php

	function mysqlUpdateTransaction($id, $comment, $timestamp) {
		$result = false;
		
		$cfg = parse_ini_file("config.ini.php", true);
		$mysqldata = $cfg["mysql"];
		$encryptiondata = $cfg["encryption"];

		$array_set = array();
		$array_bind_value = array();
		
		if (!empty($comment)) {
			array_push($array_set, "comment = AES_ENCRYPT(?, ?)");
			array_push($array_bind_value, $comment);
			array_push($array_bind_value, $encryptiondata["pass"]);
		}
		
		if (!empty($timestamp)) {
			array_push($array_set, "timestamp = ?");
			array_push($array_bind_value, $timestamp);
		}
		
		$update = "UPDATE `Transactions` SET ";
		for ($i = 0; $i < count($array_set); $i++) {
			$update .= $array_set[$i];
			if ($i != count($array_set)-1) {
				$update .= ",";
			}
		}
		$update .= " WHERE `id` = " . $id . ";";
		
		try {
			$pdo = new PDO("mysql: host=" . $mysqldata["mysql_server"] . ";dbname=" . $mysqldata["mysql_db"], $mysqldata["mysql_user"], $mysqldata["mysql_pass"]);
	 
			$statement = $pdo->prepare($update);
			if ($statement->execute($array_bind_value)) {
				$result = true;
			}// else {
				//echo "SQL Error <br />";
				//echo "Query: " . $statement->queryString . "<br />\n";
				//echo "errorInfo" . $statement->errorInfo() . "<br />\n";
			//}
			$statement->closeCursor();
			$statement = null;
			$pdo = null;
		} catch (PDOException $e) {
		   //echo "Error!: " . $e->getMessage() . "<br/>";
		   //die();
		}
		
		unset($cfg);
		unset($mysqldata);
		unset($encryptiondata);
		
		return $result;
	}
	
	function mysqlUpdateUser($id, $display_name, $ad_user, $is_admin, $bananas_to_spend, $login_token, $token_expiration_timestamp, $token_duration) {
		$result = false;
		
		$cfg = parse_ini_file("config.ini.php", true);
		$mysqldata = $cfg["mysql"];
		$encryptiondata = $cfg["encryption"];

		$array_set = array();
		$array_bind_value = array();
		
		if (!empty($display_name)) {
			array_push($array_set, "display_name = AES_ENCRYPT(?, ?)");
			array_push($array_bind_value, $display_name);
			array_push($array_bind_value, $encryptiondata["pass"]);
		}

		if (!empty($ad_user)) {
			array_push($array_set, "ad_user = AES_ENCRYPT(?, ?)");
			array_push($array_bind_value, $ad_user);
			array_push($array_bind_value, $encryptiondata["pass"]);
		}
		
		if (!empty($is_admin)) {
			array_push($array_set, "is_admin = ?");
			array_push($array_bind_value, $is_admin);
		}
		
		if (!empty($bananas_to_spend)) {
			array_push($array_set, "bananas_to_spend = ?");
			array_push($array_bind_value, $bananas_to_spend);
		}
		
		if (!empty($login_token)) {
			array_push($array_set, "login_token = ?");
			array_push($array_bind_value, $login_token);
		}
		
		if (!empty($token_expiration_timestamp)) {
			array_push($array_set, "token_expiration_timestamp = ?");
			array_push($array_bind_value, $token_expiration_timestamp);
		}
		
		if (!empty($token_duration)) {
			array_push($array_set, "token_duration = ?");
			array_push($array_bind_value, $token_duration);
		}
		
		$update = "UPDATE `Users` SET ";
		for ($i = 0; $i < count($array_set); $i++) {
			$update .= $array_set[$i];
			if ($i != count($array_set)-1) {
				$update .= ",";
			}
		}
		$update .= " WHERE `id` = " . $id . ";";

		try {
			$pdo = new PDO("mysql: host=" . $mysqldata["mysql_server"] . ";dbname=" . $mysqldata["mysql_db"], $mysqldata["mysql_user"], $mysqldata["mysql_pass"]);
	 
			$statement = $pdo->prepare($update);
			if ($statement->execute($array_bind_value)) {
				$result = true;
			} else {
				//echo "SQL Error <br />";
				//echo "Query: " . $statement->queryString . "<br />\n";
				//echo "errorInfo" . $statement->errorInfo() . "<br />\n";
			}
			$statement->closeCursor();
			$statement = null;
			$pdo = null;
		} catch (PDOException $e) {
		   //echo "Error!: " . $e->getMessage() . "<br/>";
		   //die();
		}
		
		unset($cfg);
		unset($mysqldata);
		unset($encryptiondata);
		
		return $result;
	}
	
	function mysqlSelectUsers() {
		$users = array();
		
		$cfg = parse_ini_file("config.ini.php", true);
		$mysqldata = $cfg["mysql"];
		$encryptiondata = $cfg["encryption"];

		try {
			$pdo = new PDO("mysql: host=" . $mysqldata["mysql_server"] . ";dbname=" . $mysqldata["mysql_db"], $mysqldata["mysql_user"], $mysqldata["mysql_pass"]);
	 
			$statement = $pdo->prepare(
				"SELECT `id`, 
				AES_DECRYPT(display_name, ?) as `display_name`,
				AES_DECRYPT(ad_user, ?) as `ad_user`,
				`is_admin`,
				`bananas_to_spend`,
				(Select count(*) from Transactions where to_user = display_name) as `bananas_received`,
				`login_token`,
				`token_expiration_timestamp`,
				`token_duration`
				FROM `Users` 
				ORDER BY display_name ASC;");
			if ($statement->execute(array($encryptiondata["pass"], $encryptiondata["pass"]))) {
				while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
					array_push($users, $row);
				}
			} //else {
				//echo "SQL Error <br />";
				//echo $statement->queryString."<br />";
				//echo $statement->errorInfo();
			//}
			$statement->closeCursor();
			$statement = null;
			$pdo = null;
		} catch (PDOException $e) {
		   //echo "Error!: " . $e->getMessage() . "<br/>";
		   //die();
		}
		
		return $users;
	}
	
	function mysqlSelectTransactions($limit) {
		$transactions = array();
		
		$cfg = parse_ini_file("config.ini.php", true);
		$mysqldata = $cfg["mysql"];
		$encryptiondata = $cfg["encryption"];

		try {
			$pdo = new PDO("mysql: host=" . $mysqldata["mysql_server"] . ";dbname=" . $mysqldata["mysql_db"], $mysqldata["mysql_user"], $mysqldata["mysql_pass"]);

			$statement = $pdo->prepare(
				"SELECT
				  `id`,
				  `timestamp`,
				  AES_DECRYPT(from_user, ?) AS from_user,
				  AES_DECRYPT(to_user, ?) AS to_user,
				  `banana_count`,
				  AES_DECRYPT(comment, ?) AS comment,
				  `source`,
				  `category`
				FROM
				  `Transactions`
				  ORDER BY timestamp DESC
				LIMIT 0, " . $limit . ";");
			if ($statement->execute(array($encryptiondata["pass"], $encryptiondata["pass"], $encryptiondata["pass"]))) {
				while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
					array_push($transactions, $row);
				}
			}// else {
				//echo "SQL Error <br />";
				//echo $statement->queryString."<br />";
				//print_r($statement->errorInfo());
			//}
			$statement->closeCursor();
			$statement = null;
			$pdo = null;
		} catch (PDOException $e) {
		   //echo "Error!: " . $e->getMessage() . "<br/>";
		   //die();
		}
		
		return $transactions;
	}
	
	// könnte man auch als mehrere Updates auf User oben machen, aber ich will alle Änderungen als einer durchgehende Transaktion haben
	function mysqlBananaTransaction($from_id, $from_user, $from_spend_new, $to_user, $banana_count, $comment, $timestamp, $source, $category) {
		$result = -1;
		
		$cfg = parse_ini_file("config.ini.php", true);
		$mysqldata = $cfg["mysql"];
		$encryptiondata = $cfg["encryption"];

		$pdo = new PDO("mysql: host=" . $mysqldata["mysql_server"] . ";dbname=" . $mysqldata["mysql_db"], $mysqldata["mysql_user"], $mysqldata["mysql_pass"]);
		try {
			$pdo->beginTransaction();
			$statement1 = $pdo->prepare("UPDATE `Users` SET bananas_to_spend = ? WHERE `id` = ?;");
			$statement1->execute(array($from_spend_new, $from_id));

			$statement2 = $pdo->prepare("INSERT INTO `Transactions` (`timestamp`, `from_user`, `to_user`, `banana_count`, `comment`, `source`, `category`)
			VALUES(?, AES_ENCRYPT(?, ?), AES_ENCRYPT(?, ?), ?, AES_ENCRYPT(?, ?), ?, ?);");
			$statement2->execute(array($timestamp, $from_user, $encryptiondata["pass"], $to_user, $encryptiondata["pass"], $banana_count, $comment, $encryptiondata["pass"], $source, $category));

			$result = $pdo->lastInsertId();

			$pdo->commit(); 

			$statement1->closeCursor();
			$statement1 = null;
			
			$statement2->closeCursor();
			$statement2 = null;
		} catch (PDOException $e) {
			$pdo->rollBack(); 
			$result = -1;
		   //echo "Error!: " . $e->getMessage() . "<br/>";
		   //die();
		}
		
		unset($cfg);
		unset($mysqldata);
		unset($encryptiondata);

		$pdo = null;
		unset($pdo);

		return $result;
	}

	// könnte man auch als mehrere Updates machen, aber ich will alle Änderungen als eine durchgehende Transaktion haben
	function mysqlBananaRain($from_user, $to_user, $comment, $timestamp, $source) {
		$result = -1;
		
		$cfg = parse_ini_file("config.ini.php", true);
		$mysqldata = $cfg["mysql"];
		$encryptiondata = $cfg["encryption"];
		$bananarain = $cfg["bananarain"];

		$pdo = new PDO("mysql: host=" . $mysqldata["mysql_server"] . ";dbname=" . $mysqldata["mysql_db"], $mysqldata["mysql_user"], $mysqldata["mysql_pass"]);
		try {
			$pdo->beginTransaction();
			$statement1 = $pdo->prepare("UPDATE `Users` SET bananas_to_spend = ?;");
			$statement1->execute(array($bananarain["defaultvalue"]));
			
			$statement2 = $pdo->prepare("INSERT INTO `Transactions` (`timestamp`, `from_user`, `to_user`, `banana_count`, `comment`, `source`)
			VALUES(?, AES_ENCRYPT(?, ?), AES_ENCRYPT(?, ?), ?, AES_ENCRYPT(?, ?), ?);");
			$statement2->execute(array($timestamp, $from_user, $encryptiondata["pass"], $to_user, $encryptiondata["pass"], $bananarain["defaultvalue"], $comment, $encryptiondata["pass"], $source));

			$result = $pdo->lastInsertId();

			$pdo->commit(); 

			$statement1->closeCursor();
			$statement1 = null;
			
			$statement2->closeCursor();
			$statement2 = null;
		} catch (PDOException $e) {
			$pdo -> rollBack(); 
			$result = -1;
		   //echo "Error!: " . $e->getMessage() . "<br/>";
		   //die();
		}
		
		unset($cfg);
		unset($mysqldata);
		unset($encryptiondata);
		
		$pdo = null;
		unset($pdo);
		
		return $result;
	}
	
	function mysqlStats($team) {

		$month = array();
		
		if ($team == "booking") {
			$db = "BananaServer";
		} else if ($team == "ngo") {
			$db = "BananaServerNGO";
		} else if ($team == "gateway") {
			$db = "BananaServerGW";
		} else {
			return $month;
		}
		
		$cfg = parse_ini_file("config.ini.php", true);
		$mysqldata = $cfg["mysql"];
		$encryptiondata = $cfg["encryption"];

		try {
			$pdo = new PDO("mysql: host=" . $mysqldata["mysql_server"] . ";dbname=" . $db, $mysqldata["mysql_user"], $mysqldata["mysql_pass"]);
	 
			$statement = $pdo->prepare(
				"SELECT COUNT(*) as count, DATE_FORMAT(timestamp, '%Y') as year, DATE_FORMAT(timestamp, '%m') as month FROM Transactions where source != 'rain' GROUP BY DATE_FORMAT(timestamp, '%Y%m');");
			if ($statement->execute()) {
				while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
					array_push($month, $row);
				}
			}// else {
				//echo "SQL Error <br />";
				//echo $statement->queryString."<br />";
				//echo $statement->errorInfo();
			//}
			$statement->closeCursor();
			$statement = null;
			$pdo = null;
		} catch (PDOException $e) {
		   //echo "Error!: " . $e->getMessage() . "<br/>";
		   //die();
		}
		
		return $month;
	}
?>