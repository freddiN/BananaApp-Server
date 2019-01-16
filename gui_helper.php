<?php
	include_once "action_accountdetails.php";
	include_once "action_userlist.php";

	function guiHasToken() {
		return isset($_COOKIE["banana-app-token"]);
	}

	function guiFetchToken() {
		if (isset($_COOKIE["banana-app-token"])) {
			return htmlspecialchars($_COOKIE["banana-app-token"]);
		} else if (isset($_POST["token"])) {
			setcookie("banana-app-token", htmlspecialchars($_POST["token"]), time()+86400*30);
			return htmlspecialchars($_POST["token"]);
		} else {
			return "";
		}
	}

	function guiShowSendBanana() {
		return (isset($_POST["top-button-send"]) || isset($_POST["submit-button-send"]));
	}

	function guiShowAccount() {
		return (isset($_POST["top-button-account"]) || isset($_POST["submit-button-account"]));
	}

	function guiShowUserlist() {
		return (isset($_POST["top-button-userlist"]) || isset($_POST["submit-button-userlist"]));
	}

	function guiShowTransactionlist() {
		return (isset($_POST["top-button-transactionlist"]) || isset($_POST["submit-button-transactionlist"]));
	}

	function guiShowAdmin() {
		return (
		isset($_POST["top-button-admin"]) || 
		isset($_POST["submit-button-makeitrain"]) ||
		isset($_POST["submit-button-admin-newuser"]) ||
		isset($_POST["submit-button-admin-deleteuser"]) ||
		isset($_POST["submit-button-admin-deletetransaction"]) ||
		isset($_POST["submit-button-admin-reverttransaction"]) ||
		isset($_POST["submit-button-admin-anonymizeuser"])
		);
	}

	function guiShowSetup() {
		return isset($_POST["top-button-setup"]);
	}
	
	function guiShowStats() {
		return isset($_POST["top-button-stats"]);
	}

	function guiShowLogin() {
		return isset($_POST["top-button-login"]);
	}

	function guiShowLogout() {
		return isset($_POST["top-button-logout"]);
	}

	function guiGetUserlist() {
		$jsonRQ = new stdClass();
		$jsonRQ->actionname = "get_user_list";
		$jsonRQ->login = new stdClass();
		$jsonRQ->login->token = htmlspecialchars(guiFetchToken());
		return json_decode(handleActionUserList($jsonRQ));
	}
	
	function guiGetUserlist2($team_name) {
		$users = persistGetUserList($team_name);
		censorData($users);

		return $users;
	}

	function guiPrintLogin($button_name, $button_text) {
		print "<p>\n";
		print "<form name=\"login\" method=\"post\">\n";
		print "	<h4>Please Login</h4>\n";
		print "	<table id=\"loginTable\">\n";
		print "		<tr>\n";
		print "			<td>Token: </td>\n";
		print "			<td><input type=\"text\" name=\"token\" value=\"\"/></td>\n";
		print "		</tr>\n";
		print "	</table>\n";
		print "	<button type=\"submit\" name=\"" . $button_name . "\">" . $button_text . "</button>\n";
		print "</form>\n";
		print "</p>\n";
	}

	function createBasicRequest($name) {
		$jsonRQ = new stdClass();
		$jsonRQ->actionname = $name;
		$jsonRQ->login = new stdClass();
		$jsonRQ->login->token = htmlspecialchars(guiFetchToken());
		$jsonRQ->login->source = "webpage";
		$jsonRQ->action_request = new stdClass();
		return $jsonRQ;
	}

	function guiGetSelf($what="display_name") {
		$self = "";
		$jsonRQ = createBasicRequest("get_account_details");

		$response = json_decode(handleActionAccountDetails($jsonRQ));
		if($response->status == "login ok"){
			if ($what == "display_name") {
				$self = $response->action_result[0]->display_name;
			} else if ($what == "token_expiration_timestamp") {
				$self = $response->action_result[0]->token_expiration_timestamp;
			} else if ($what == "is_admin") {
				$self = $response->action_result[0]->is_admin;
			}
		}
		
		return $self;
	}
	
	function guiPrintStatsForTeam($team) {
		print "<table rules=\"all\" frame=\"border\">\n";
		print "<caption>" . $team . "</caption>\n";
		print "  <tr align=\"center\" valign=\"top\">\n";
		print "    <th> month </th>\n";
		print "    <th> count </th>\n";
		print "  </tr>\n";

		$stats = persistGetStats($team);
		foreach ($stats as $line){
			print "<tr align=\"center\" valign=\"top\">\n";
			print " <td align=\"center\"> " . $line->month . " / " . $line->year . " </td>\n";
			print " <td align=\"center\"> " . $line->count . " </td>\n";
			print "</tr>\n";
		}
		print "</table><br/>\n";
	}
?>