<?php
	include_once "json_classes.php";
	include_once "persist.php";
	
	if(isset($_POST["submit_button"])) {
		//print_r($_POST);
	  
		$jsonRQ = new stdClass();
		$jsonRQ->login = new stdClass();
		$user = $jsonRQ->login->user = $_POST["login"];
		$pass = $jsonRQ->login->pass = $_POST["password"];
	  
		$user = persistGetAccountDetails($jsonRQ);
		if (ldapLoginValid($jsonRQ) && $user[0]->is_admin == 1) {
			print "<br>admin login successful, makin it rain ... <br>";
			$result = makeItRain($_POST["bananacount"]);
			print "<br>banana-rain result: " . $result . "<br>";
		} else {
			print "<br>admin login error, aborting ... <br>"; 
		}
	}
?>
	
	<form name="banana_rain" method="post" action="" >
		<table id="loginTable">
			<tr><td>Username:</td><td><input type="text" name="login" id="loginUsername" /></td></tr>
			<tr><td>Password:</td><td><input type="password" name="password" id="loginPassword" /></td></tr>
			<tr>
				<td>Banana-Count:</td>
				<td><input type="text" name="bananacount" id="bananaCount" size="4"/></td>
			</tr>
		</table>
		<input type="Submit" name="submit_button" value="Make it rain!" />
	</form>
