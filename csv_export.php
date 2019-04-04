<?php
	# Quelle: https://stackoverflow.com/questions/4249432/export-to-csv-via-php
	# http://blog.next-motion.de/2010/07/17/umlaute-in-csv-export-per-php-zeichensatzkonvertierung/
	
	include_once "persist.php";
	
	$selection = htmlspecialchars($_GET["function"]);
	if ($selection == "getTransactionsAsCSV") {
		download_send_headers("transactions_" . date("d-m-Y-H:i:s") . ".csv");
		
		$transactions = array();
		$mysqlTransactions = mysqlSelectTransactions(100000);
		foreach ($mysqlTransactions as $transaction) {
			$visibility_of_to_user = persistGetUserdetailFromUsername($transaction["to_user"], "visibility");		

			if ($visibility_of_to_user == "0") {
				$transaction["to_user"] = "(anonymized)";
			} 
			array_push($transactions, $transaction);
		}

		echo array2csv($transactions);
		die;
	} else if ($selection == "getTransactionsAsCSVanonymized") {
		download_send_headers("transactions_" . date("d-m-Y-H:i:s") . ".csv");
		
		$transactions = array();
		$mysqlTransactions = mysqlSelectTransactions(100000);
		foreach ($mysqlTransactions as $transaction) {
      if ($transaction["source"] != "rain") {

        $from = persistGetUserdetailFromUsername($transaction["from_user"], "team_name");
        if (!empty($from)) {
          $transaction["from_user"] = $from;
        }
        
        $to = persistGetUserdetailFromUsername($transaction["to_user"], "team_name");
        if (!empty($to)) {
          $transaction["to_user"] = $to;
        }
      }

			array_push($transactions, $transaction);
		}

		echo array2csv($transactions);
		die;
	}/*else if ($selection == "getUsersAsCSV") {
		download_send_headers("users_" . date("d-m-Y-H:i:s") . ".csv");
		echo array2csv(mysqlSelectUsers());
		die;
	}*/
	
	function download_send_headers($filename) {
		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		//header("Content-Type: text/html; charset=UTF-8");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download  
		header("Content-Type: application/force-download; charset=UTF-8");
		header("Content-Type: application/octet-stream; charset=UTF-8");
		header("Content-Type: application/download; charset=UTF-8");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}
	
	function array2csv(array &$array) {
		if (count($array) == 0) {
			return null;
		}
	   
		ob_start();
		$df = fopen("php://output", 'w');
		fputcsv($df, array_keys(reset($array)), ";");
		foreach ($array as $row) {
			$row["login_token"] = "";
			$row["token_expiration_timestamp"] = "";

			fputcsv($df, array_map("convertToWindowsCharset",$row), ";");
		}
		fclose($df);
		return ob_get_clean();
	}
	
	function convertToWindowsCharset($string) {
		$charset =  mb_detect_encoding(
			$string,
			"UTF-8, ISO-8859-1, ISO-8859-15",
			true
		);
	 
		$string =  mb_convert_encoding($string, "Windows-1252", $charset);
		return $string;
	}
?>