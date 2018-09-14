<?php

	function doLogging($logme) {
		$logfile = "./bananaapp_log.txt";
		$log = date("d.m.Y H:i:s") . " : " . $logme . "\n";
		file_put_contents($logfile, $log, FILE_APPEND);
	}	
?>