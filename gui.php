<?php
header("Content-Type: text/html; charset=UTF-8");

$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
if ($isSecure) {
	header("Strict-Transport-Security: max-age=31536000");
}

include_once "gui_helper.php";
include_once "json_classes.php";

include_once "action_accountdetails.php";
include_once "action_bananarain.php";
include_once "action_createtransaction.php";
include_once "action_login.php";
include_once "action_logout.php";
include_once "action_transactionlist.php";
include_once "action_userlist.php";
include_once "action_edituser.php";
include_once "action_edittransaction.php";

//var_dump($_REQUEST);

print "<html>\n";
print "<head>\n";
print "	<link rel=\"stylesheet\" type=\"text/css\" href=\"css/style_desktop.css\" media=\"screen and (min-width: 1024px)\"/>\n";
print "	<link rel=\"stylesheet\" type=\"text/css\" href=\"css/style_mobile.css\" media=\"screen and (max-width: 1024px)\"/> \n";
print "	<link rel=\"icon\" type=\"image/png\" href=\"images/favicon.png\"/>\n";
print "	<title>Welcome to the Banana App</title>\n";
print "	<script type=\"text/javascript\" src=\"js/qrcode.js\"></script>\n";
print "	<script type=\"text/javascript\" src=\"js/tablesearch.js\"></script>\n";
print "	<script type=\"text/javascript\" src=\"js/teamselect.js\"></script>\n";
print "</head>\n";

print "<body>\n";
print "	<h1 id=\"header\">The Banana App</h1>\n";
print "	<p>\n";
print "		<form name=\"send banana menu\" method=\"post\" action=\"\" style=\"border: 1px none black\">\n";
print "			<button type=\"submit\" name=\"top-button-send\">Send Banana</button>\n";
print "			<button type=\"submit\" name=\"top-button-account\">View Account</button>\n";
print "			<button type=\"submit\" name=\"top-button-transactionlist\">Display Transactions</button>\n";
print "			<button type=\"submit\" name=\"top-button-stats\">Stats</button>\n";
if (guiGetSelf("is_admin") == "1") {
	print "			<button type=\"submit\" name=\"top-button-admin\" style=\"background-color:Khaki\">Admin</button>\n";
}
print "			<button type=\"submit\" name=\"top-button-setup\" id=\"top-button-setup\" style=\"background-color:LightGreen\">Setup</button>\n";
print "			<button type=\"submit\" name=\"top-button-login\" style=\"background-color:LightPink\">Login</button>\n";
print "			<button type=\"submit\" name=\"top-button-logout\" style=\"background-color:LightPink\">Logout</button>\n";
print "		</form>\n";
print "	</p>\n";

// TOP Buttons gedrückt
if(guiShowSendBanana()) {
	print "<p>\n";
	print "<form id=\"send_banana_form\" name=\"send banana\" method=\"post\" action=\"\">\n";
	print "	<h4>Send Banana!</h4>\n";
	print "	<table>\n";
	print "		<tr>\n";
	print "			<td>Token: </td>\n";
	print "			<td width=\"100%\"><input style=\"width:100%;\" type=\"text\" name=\"token\" value=\"" . guiFetchToken() . "\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>Send to: </td>\n";
	
	if (guiHasToken()) {
		$self = guiGetSelf("display_name");
		$users = guiGetUserlist2("");
		if(!empty($users)){
			$teams = persistGetTeamList();
			$allTeams = "select_AllUsers|";
			foreach ($teams as &$team){
				$allTeams .= "select_" . $team . "|";
			}
			
			print "			<td>\n";
			
			print "			<select name=\"team_select\" id=\"team_select\" onChange=\"showSelect(this.value,'" . substr($allTeams, 0, -1) ."');\">\n";
			print "			 <option value=\"select_AllUsers\">All users</option>\n";
			foreach ($teams as &$team){
				print "			 <option value=\"select_" . $team . "\">" . $team . "</option>\n";
			}
			print "			</select>\n\n";
			
			print "			<select name=\"sendto_select_AllUsers\" id=\"select_AllUsers\">\n";
			print "			 <option>(please select)</option>\n";
			foreach ($users as &$user){
				if ($self != $user->display_name) {
					print "			 <option>" . $user->display_name . "</option>\n";
				}
			}
			print "			</select>\n";
			
			foreach ($teams as &$team){
				$users_tmp = guiGetUserlist2($team);
				print "			<select name=\"sendto_select_" . $team . "\" style=\"display:none\" id=\"select_" . $team . "\">\n";
				print "			 <option>(please select)</option>\n";
				foreach ($users_tmp as &$user_tmp){
					if ($self != $user_tmp->display_name) {
						print "			 <option>" . $user_tmp->display_name . "</option>\n";
					}
				}
				print "			</select>\n";
			}
			
			print "         </td>\n";
		} else {
			print "			<td><input type=\"text\" name=\"sendto\"/></td>\n";
		}
	} else {
		print "			<td><input type=\"text\" name=\"sendto\"/></td>\n";
	}	

	print "		</tr>\n";
	print "		<tr>\n";
	print "       <td>Comment: </td>\n";
	print "       <td><textarea name=\"comment\" style=\"width:100%;height:150px;\"></textarea></td>\n";
	print "		</tr>\n";
    print "		<tr>\n";
    print "       <td>Category: </td>\n";
    print "       <td><select name=\"category\" id=\"category\">\n";

    $cfg = parse_ini_file("config.ini.php", true);
    $categories = $cfg["categories"];
    $category_names = explode(":", $categories["names"]);
    foreach ($category_names as $category) {
        print "          <option>" . $category ."</option>\n";
    }

    unset($cfg);
    unset($categories);
    unset($category_names);

    print "         </select></td>\n";
    print "		</tr>\n";
	print "	</table>\n";
	print "	<button type=\"submit\" name=\"submit-button-send\">Send banana!</button>\n";
	print "</form>\n";
	print "</p>\n";
} else if(guiShowAccount()) {
	if (guiHasToken()) {
		$_POST["submit-button-account"] = "set";
	} else {
		guiPrintLogin("submit-button-account", "Show account details!");
	}
} else if(guiShowTransactionlist()) {
	if (guiHasToken()) {
		$_POST["submit-button-transactionlist"] = "set";
	} else {
		guiPrintLogin("submit-button-transactionlist", "Show transactions!");
	}
} else if(guiShowAdmin()) {
	print "<p>\n";
	
	print "<table width=\"70%\" border=\"2\" style=\"background-color:Khaki\">\n";
	print "  <tr>\n";
	print "    <td>\n";

	print "<form name=\"banana-rain\" method=\"post\" action=\"\" style=\"background-color:Khaki\">\n";
	print "	<h4>Make it rain!</h4>\n";
	print "	<table id=\"loginTable\">\n";
	print "		<tr>\n";
	print "			<td>Token: </td>\n";
	print "			<td><input type=\"text\" name=\"token\" value=\"". guiFetchToken() . "\"/></td>\n";
	print "		</tr>\n";
	print "	</table>\n";
	print "	<button type=\"submit\" name=\"submit-button-makeitrain\" style=\"background-color:White\">Make it rain!</button>\n";
	print "</form>\n";
	
	print "    </td>\n";
	print "    <td>\n";
	
	print "<form name=\"admin-newuser\" method=\"post\" action=\"\" style=\"background-color:Khaki\">\n";
	print "	<h4>Add new user</h4>\n";
	print "	<table id=\"loginTable\">\n";
	print "		<tr>\n";
	print "			<td>Token: </td>\n";
	print "			<td><input type=\"text\" name=\"token\" value=\"". guiFetchToken() . "\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>Display name: </td>\n";
	print "			<td><input type=\"text\" name=\"displayname\" value=\"\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>AD name: </td>\n";
	print "			<td><input type=\"text\" name=\"aduser\" value=\"\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>Team: </td>\n";
	print "			<td><input type=\"text\" name=\"team\" value=\"\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>is admin: </td>\n";
	print "			<td><input type=\"checkbox\" name=\"isadmin\" value=\"\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>Visibility: </td>\n";
	print "			<td><input type=\"text\" name=\"visibility\" value=\"0\" size=\"5\"/> (0=hidden, 1=visible)</td>\n";
	print "		</tr>\n";
	print "	</table>\n";
	print "	<button type=\"submit\" name=\"submit-button-admin-newuser\" style=\"background-color:White\">Create</button>\n";
	print "</form>\n";
	
	print "    </td>\n";
	print "  </tr>\n";
	print "  <tr>\n";
	print "    <td>\n";
	
	print "<form name=\"admin-deleteuser\" method=\"post\" action=\"\" style=\"background-color:Khaki\">\n";
	print "	<h4>Delete user</h4>\n";
	print "	<table id=\"loginTable\">\n";
	print "		<tr>\n";
	print "			<td>Token: </td>\n";
	print "			<td><input type=\"text\" name=\"token\" value=\"". guiFetchToken() . "\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>Display name: </td>\n";
	print "			<td><input type=\"text\" name=\"displayname\" value=\"\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>Team: </td>\n";
	print "			<td><input type=\"text\" name=\"team\" value=\"\"/></td>\n";
	print "		</tr>\n";
	print "	</table>\n";
	print "	<button type=\"submit\" name=\"submit-button-admin-deleteuser\" style=\"background-color:White\">Delete</button>\n";
	print "</form>\n";
	
	print "    </td>\n";
	print "    <td>\n";
	
	print "<form name=\"admin-anonymizetransactions\" method=\"post\" action=\"\" style=\"background-color:Khaki\">\n";
	print "	<h4>Anonymize transactions</h4>\n";
	print "	<table id=\"loginTable\">\n";
	print "		<tr>\n";
	print "			<td>Token: </td>\n";
	print "			<td><input type=\"text\" name=\"token\" value=\"". guiFetchToken() . "\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>Display name: </td>\n";
	print "			<td><input type=\"text\" name=\"displayname\" value=\"\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>Team: </td>\n";
	print "			<td><input type=\"text\" name=\"team\" value=\"\"/></td>\n";
	print "		</tr>\n";
	print "	</table>\n";
	print "	<button type=\"submit\" name=\"submit-button-admin-anonymizetransactions\" style=\"background-color:White\">Anonymize</button>\n";
	print "</form>\n";
	
	print "    </td>\n";
	print "  </tr>\n";
	print "  <tr>\n";
	print "    <td>\n";
	
	print "<form name=\"admin-deletetransaction\" method=\"post\" action=\"\" style=\"background-color:Khaki\">\n";
	print "	<h4>Delete transaction</h4>\n";
	print "	<table id=\"loginTable\">\n";
	print "		<tr>\n";
	print "			<td>Token: </td>\n";
	print "			<td><input type=\"text\" name=\"token\" value=\"". guiFetchToken() . "\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>Transaction id: </td>\n";
	print "			<td><input type=\"text\" name=\"transactionid\" value=\"\"/></td>\n";
	print "		</tr>\n";
	print "	</table>\n";
	print "	<button type=\"submit\" name=\"submit-button-admin-deletetransaction\" style=\"background-color:White\">Delete</button>\n";
	print "</form>\n";
	
	print "    </td>\n";
	print "    <td>\n";
	
	print "<form name=\"admin-anonymizetransactions\" method=\"post\" action=\"\" style=\"background-color:Khaki\">\n";
	print "	<h4>Revert transaction</h4>\n";
	print "	<table id=\"loginTable\">\n";
	print "		<tr>\n";
	print "			<td>Token: </td>\n";
	print "			<td><input type=\"text\" name=\"token\" value=\"". guiFetchToken() . "\"/></td>\n";
	print "		</tr>\n";
	print "		<tr>\n";
	print "			<td>Transaction id: </td>\n";
	print "			<td><input type=\"text\" name=\"transactionid\" value=\"\"/></td>\n";
	print "		</tr>\n";
	print "	</table>\n";
	print "	<button type=\"submit\" name=\"submit-button-admin-reverttransaction\" style=\"background-color:White\">Revert</button>\n";
	print "</form>\n";
	
	print "    </td>\n";
	print "  </tr>\n";
	print "</table>\n";

	print "</p>\n";
} else if(guiShowSetup()) {
	print "<p>\n";
	if (guiHasToken()) {
		print "Current Token: <input type=\"text\" style=\"width:20em\" name=\"token_to_copy\" onClick=\"this.select();\" value=\"" . guiFetchToken() . "\"/><br/>";
	} else {
		print "Currently token not set<br>\n";
	}
	print "</p>\n";
	
	$cfg = parse_ini_file("config.ini.php", true);
	$qrcode = $cfg["qrcode_setup"];
	$notifications = $cfg["notifications"];
	
	print "<p>\n";
	//QR Code script: https://github.com/davidshimjs/qrcodejs
	print "Server: " . $qrcode["server"] . "<br>\n";
	print "HTTP User: " . $qrcode["user"] . "<br>\n";
	print "HTTP Pass: " . $qrcode["pass"] . "<br>\n";
	print "Notifications Topic: " . $notifications["topic"] . "<br>\n";

	print "<div id=\"qrcode_setup\"></div>\n";
	print "<script type=\"text/javascript\">\n";
	print "new QRCode(document.getElementById(\"qrcode_setup\"), \"bsetup:server=" . $qrcode["server"] . "|user=" . $qrcode["user"] . "|pass=" . $qrcode["pass"] . "|topic=" . $notifications["topic"] . "|token=" . guiFetchToken() . "\")";
	print "</script>\n";

	print "</p>\n";
	
	print "<p>\n";
	print "Android App in <a href=\"https://play.google.com/store/apps/details?id=de.freddi.bananaapp&hl=de\" target=\"_blank\">Play Store</a>\n";
	print "</p>\n";
	
} else if(guiShowStats()) {
	if (guiHasToken()) {
		print "<h3>Stats</h3>\n";
		
		guiPrintStatsForTeam("PCC");
	} else {
		guiPrintLogin("", "Show stats!");
	}
} else if(guiShowLogin()) {
	print "<p>\n";
	print "<form name=\"banana_rain\" method=\"post\">\n";
	print "			<table id=\"loginTable\">\n";
	print "				<tr><td>AD Username:</td><td><input type=\"text\" name=\"login\" placeholder=\"firstname.lastname\"/></td></tr>\n";
	print "				<tr><td>AD Password:</td><td><input type=\"password\" name=\"password\"/></td></tr>\n";
	print "			</table>\n";
	print "			<button type=\"submit\" name=\"submit-button-login\">Login</button>\n";
	print "		</form>\n";
	print "</p>\n";
	
	if (guiHasToken()) {
		print "<p id=\"already_logged_in\">(It looks like your are already logged in, logging in again will invalidate your old token)</p>\n";
	} 
} else if(guiShowLogout()){
	$_POST["submit-button-logout"] = "set";
	print "<p id=\"logout_performed\">Logout performed</p>\n";
}


// Aktionen nachdem der jeweiligeSubmit Button gedrückt wurde
if(isset($_POST["submit-button-send"])){
	print "<h3 id=\"action_header\">Send Banana</h3>\n";
	
	$sendTo = "";
	$teamSelect = str_replace(' ', '_',$_POST["team_select"]);	//"select_Minion"
	if ($_POST["sendto_" . $teamSelect] != "(please select)") {// "sendto_select_Minion"
		$sendTo = $_POST["sendto_" . $teamSelect];
	}

	$jsonRQ = createBasicRequest("create_transaction");
	$jsonRQ->action_request->to_user = htmlspecialchars($sendTo);
	$jsonRQ->action_request->banana_count = 1;
	$jsonRQ->action_request->comment = htmlspecialchars($_POST["comment"]);
    $jsonRQ->action_request->category = htmlspecialchars($_POST["category"]);

	$response = json_decode(handleCreateTransaction($jsonRQ));

	if($response->status == "create_transaction ok"){
		print "<p id=\"update\"><font color=\"green\"> Success! Transaction Id: " . $response->action_result[0] . " </font></p>\n";

		$filename = "images/" . $jsonRQ->action_request->to_user . ".gif";
		if (file_exists($filename)) {
			print "<img id=\"minionimage\" src=\"" . $filename . "\"/>\n";
		} else {
			$images = array(
				"<img id=\"minionimage\" src=\"images/happy-minions.gif\" width=\"500\" height=\"226\"/>\n",
				"<img id=\"minionimage\" src=\"images/xmas-minions.gif\" width=\"540\" height=\"540\"/>\n",
				"<img id=\"minionimage\" src=\"images/shopping-minions.gif\" width=\"500\" height=\"270\"/>\n",
				"<img id=\"minionimage\" src=\"images/banana-minions.gif\" width=\"200\" height=\"200\"/>\n"
			);

			$random = rand(0, sizeof($images)-1);
			print $images[$random];
		}
	} else {
		print "<p id=\"update\"><font color=\"red\"> Error: " . $response->status . " </font></p>\n";
	}
} else if(isset($_POST["submit-button-account"])){
	print "<h3 id=\"action_header\">Account Details</h3>\n";

	if (isset($_POST["new_duration"])) {
		$json_edit_user = createBasicRequest("edit_user");
		$json_edit_user->action_request->display_name = guiGetSelf("display_name");
		$json_edit_user->action_request->token_duration = htmlspecialchars($_POST["new_duration"]);
		$json_edit_user->action_request->visibility = htmlspecialchars($_POST["new_visibility"]);

		$response = json_decode(handleActionEditUser($json_edit_user));
		
		if($response->status == "edit_user ok"){
			print "<p id=\"update\"><font color=\"green\">update successful</font></p>\n";
		} else {
			print "<p id=\"update\"><font color=\"red\">update error</font></p>\n";
		}
	}

	$response = json_decode(handleActionAccountDetails(createBasicRequest("get_account_details")));
	if($response->status == "login ok"){
		print "<form name=\"send account update\" method=\"post\" action=\"\">";
		print "	<table id=\"accountTable\">\n";
		print "		<tr>\n";
		print "			<td>Display name: </td>\n";
		print "			<td>" . $response->action_result[0]->display_name . "</td>\n";
		print "		</tr>\n";
		print "		<tr>\n";
		print "			<td>AD user: </td>\n";
		print "			<td>" . $response->action_result[0]->ad_user . "</td>\n";
		print "		</tr>\n";
		print "		<tr>\n";
		print "			<td>Team: </td>\n";
		print "			<td>" . $response->action_result[0]->team_name . "</td>\n";
		print "		</tr>\n";
		print "		<tr>\n";
		print "			<td>Bananas to spend: </td>\n";
		print "			<td><meter value=\"" . $response->action_result[0]->bananas_to_spend . "\" min=\"0\" max=\"10\"></meter> " . $response->action_result[0]->bananas_to_spend . " / 10</td>\n";
		print "		</tr>\n";
		print "		<tr>\n";
		print "			<td>Bananas received: </td>\n";
		print "			<td>" . $response->action_result[0]->bananas_received . "</td>\n";
		print "		</tr>\n";
		print "		<tr>\n";
		print "			<td>Is admin: </td>\n";
		if ($response->action_result[0]->is_admin == "1") {
			print "			<td>yes</td>\n";
		} else {
			print "			<td>no</td>\n";
		}
		print "		</tr>\n";

		print "		<tr>\n";
		print "			<td>Token: </td>\n";
		print "			<td>" . $response->action_result[0]->login_token . "</td>\n";
		print "		</tr>\n";
	
		print "		<tr>\n";
		print "			<td>Token duration: </td>\n";
		print "			<td>";
		print "               <input type=\"number\" style=\"width:4em\"  min=\"1\" max=\"9999\" name=\"new_duration\" value=\"" . $response->action_result[0]->token_duration . "\"/>  hours &nbsp;&nbsp;&nbsp;";
		print "				  <button type=\"submit\" name=\"submit-button-account\">Update</button>\n";
		print "         </td>\n";
		print "		</tr>\n";

		$periods = new DatePeriod(new DateTime(), new DateInterval('PT1H'), new DateTime($response->action_result[0]->token_expiration_timestamp));
		$hours = iterator_count($periods);
		
		if ($hours < 0.1 * $response->action_result[0]->token_duration) {
			$hours = "<b><font color=\"red\">" . $hours . "</font></b>";
		}
		
		print "		<tr>\n";
		print "			<td>Token expiration: </td>\n";
		print "			<td>" . $response->action_result[0]->token_expiration_timestamp . " (~" . $hours . " hours left)</td>\n";
		print "		</tr>\n";
		
		print "		<tr>\n";
		print "			<td>User id: </td>\n";
		print "			<td>" . $response->action_result[0]->id . "</td>\n";
		print "		</tr>\n";
		print "		<tr>\n";
		print "			<td>Visibility: </td>\n";
		print "			<td>\n";
		print "			  <select name=\"new_visibility\">\n";
		
		if ($response->action_result[0]->visibility == "0") {
			$select0 = "selected ";
			$select1 = "";
		} else if ($response->action_result[0]->visibility == "1") {
			$select0 = "";
			$select1 = "selected ";
		}
		
		print "			    <option " . $select0 . "value=\"0\">Hidden</option>\n";
		print "			    <option " . $select1 . "value=\"1\">Visible</option>\n";
		print "			  </select>\n";
		//print "			<td>\n";
		//print "               <input type=\"number\" style=\"width:3em\"  min=\"0\" max=\"1\" name=\"new_visibility\" value=\"" . $response->action_result[0]->visibility . "\"/> ";
		print "				  &nbsp;&nbsp;&nbsp;<button type=\"submit\" name=\"submit-button-account\">Update</button>\n";
		print "         </td>\n";
		print "		</tr>\n";
		print "  </table>\n";
		print "		</form>\n";
	} else {
		print "<br><font color=\"red\"> Error: " . $response->status . " </font><br>\n";
	}
} else if(isset($_POST["submit-button-transactionlist"])){
	print "<h3 id=\"action_header\">Transaction List</h3>\n";
	
	if (isset($_POST["update_transaction_comment"])) {
		$json_edit_transaction = createBasicRequest("edit_transaction");
		$json_edit_transaction->action_request->transaction_id = htmlspecialchars($_POST["update_transaction_id"]);
		$json_edit_transaction->action_request->comment = htmlspecialchars($_POST["update_transaction_comment"]);

		$response = json_decode(handleActionEditTransaction($json_edit_transaction));
		
		if($response->status == "edit_transaction ok"){
			print "<p id=\"update\"><font color=\"green\">update successful</font></p>\n";
		} else {
			print "<p id=\"update\"><font color=\"red\">update error</font></p>\n";
		}
	}

	$json_get_transactionlist = createBasicRequest("get_transaction_list");
	$json_get_transactionlist->action_request->check_visibility = "true";
	$response = json_decode(handleTransactionList($json_get_transactionlist));

	if($response->status == "get_transaction_list ok"){
		$self = guiGetSelf("display_name");
		
		print "<input type=\"text\" id=\"search_input\" onkeyup=\"filterListTransactions()\" placeholder=\"Search for names...\" title=\"Type in a username\">&nbsp;&nbsp;&nbsp;";
		print "<input type=\"text\" id=\"search_input_teams\" onkeyup=\"filterListTeams()\" placeholder=\"Search for team...\" title=\"Type in a teamname\"><br/><br/>\n";
		
		print "<table id=\"search_table\" rules=\"all\" frame=\"border\">\n";
		print "  <tr align=\"center\" valign=\"top\">\n";
		print "    <th>Timestamp</th><th id=\"sourceimage\">Source</th><th>From</th><th>To</th><th>Category</th><th width=\"100%\">Comment</th>\n";
		print "  </tr>\n";
		foreach ($response->action_result as &$result){
			if ($self == $result->from_user || $self == $result->to_user || "Everyone" == $result->to_user) {
				print "  <tr style=\"background-color:LightGoldenRodYellow\">\n";
			} else {
				print "  <tr>\n";
			}

			print "    <td style=\"white-space:nowrap;\" title=\"id " . $result->id  ."\">" . $result->timestamp . "</td>\n";
			
			if ($result->source == "webpage") {
				print "    <td id=\"sourceimage\" align=\"center\"><img src=\"images/icon_www.png\" title=\"Webpage\" alt=\"Webpage\" width=\"20\" height=\"20\"></td>\n";
			} else if ($result->source == "app") {
				print "    <td id=\"sourceimage\" align=\"center\"><img src=\"images/icon_app.png\" title=\"Android App\" alt=\"Android App\" width=\"20\" height=\"20\"></td>\n";
			} else if ($result->source == "rain") {
				print "    <td id=\"sourceimage\" align=\"center\"><img src=\"images/icon_rain.png\" title=\"Banana Rain\" alt=\"Banana Rain\" width=\"25\" height=\"20\"></td>\n";
			} else {
				print "    <td id=\"sourceimage\"></td>\n";
			} 
			
			print "    <td style=\"white-space:nowrap;\">" . $result->from_user . "</td>\n";
			print "    <td style=\"white-space:nowrap;\">" . $result->to_user . "</td>\n";
            print "    <td style=\"white-space:nowrap;\">" . $result->category . "</td>\n";
			print "    <td>"; 
			
			$diff = strtotime("now") - strtotime($result->timestamp);
			if ($result->from_user === $self && $diff < 3600) {	//oder Admin?
				print "<form name=\"send transaction update\" method=\"post\" action=\"\">";
				print "<button type=\"submit\" name=\"submit-button-transactionlist\">U</button>\n";
				print "<input type=\"hidden\" name=\"update_transaction_id\" value=\"" . $result->id . "\"/>\n";
				print "<input type=\"text\" style=\"width:97%\" name=\"update_transaction_comment\" value=\"" . $result->comment . "\"/>\n";
				print "</form>\n";
			} else {
				print $result->comment;
			}

			print "</td>\n";
			print "    <td style=\"display:none;\">" . $result->from_user_team . " " . $result->to_user_team . "</td>\n";
			print "  </tr>\n";
		}
		print "</table>\n";
		print "<p id=\"csvexport\">\n";
		print "  transaction count: " . count($response->action_result);
		print "  <br/>CSV <a href=\"csv_export.php?function=getTransactionsAsCSV\" target=\"_blank\">Export</a>\n";
		print "<br/><br/>Archive:<br/>\n";
		
		$files = array_diff(scandir("archive"), array('.', '..'));
		foreach($files as $file) 
		{
			if (stristr($file, '.csv') === FALSE || stristr($file, 'transactions_') === FALSE) {
				continue;
			}
			
			print " - <a href=\"archive/" . $file . "\">" . $file ."</a><br>\n";
		}

		print "</p>\n";
	} else {
		print "<br/><font color=\"red\"> Error: " . $response->status . " </font><br/>\n";
	}
} else if(isset($_POST["submit-button-makeitrain"])){
	print "<h3 id=\"action_header\">Banana Rain</h3>\n";

	$response = json_decode(handleActionBananaRain(createBasicRequest("")));

	if($response->status == "banana_rain ok"){
		print "<br><font color=\"green\"> banana-rain result: " . $response->status . "</font><br>\n";
	} else {
		print "<br><font color=\"red\"> admin login error, aborting ... </font><br>\n"; 
	}
} else if(isset($_POST["submit-button-admin-newuser"])){
	print "<h3 id=\"action_header\">New User</h3>";
	$ok = true;

	$jsonRQ = createBasicRequest("");
	if (!persistIsTokenValid($jsonRQ)) {
		print "<br><font color=\"red\"> token error, aborting ... </font><br>\n"; 
		$ok = false;
	}
			
	if ($ok == true && !persistIsUserAdmin($jsonRQ)) {
		print "<br><font color=\"red\"> admin rights error, aborting ... </font><br>\n";
		$ok = false;
	}

	if ($ok == true) {
		$createOk = persistCreateUser($_POST["displayname"], $_POST["aduser"], $_POST["team"], isset($_POST["isadmin"]), 10, 168, intval($_POST["visibility"]));
		if($createOk == true){
			print "<br><font color=\"green\"> Create-user result: ok</font><br>\n";
		} else {
			print "<br><font color=\"red\"> Create-user result: error </font><br>\n"; 
		}
	}
} else if(isset($_POST["submit-button-admin-deleteuser"])){
	print "<h3 id=\"action_header\">Delete User</h3>";
	$ok = true;
	$jsonRQ = createBasicRequest("");
	if (!persistIsTokenValid($jsonRQ)) {
		print "<br><font color=\"red\"> token error, aborting ... </font><br>\n"; 
		$ok = false;
	}
			
	if ($ok == true && !persistIsUserAdmin($jsonRQ)) {
		print "<br><font color=\"red\"> admin rights error, aborting ... </font><br>\n";
		$ok = false;
	}

	if ($ok == true) {
		$deleteOk = persistDeleteUser($_POST["displayname"], $_POST["team"]);
		if($deleteOk == true){
			print "<br><font color=\"green\"> Delete-user result: ok</font><br>\n";
		} else {
			print "<br><font color=\"red\"> Delete-user result: error </font><br>\n"; 
		}
	}
} else if(isset($_POST["submit-button-admin-anonymizetransactions"])){
	print "<h3 id=\"action_header\">Anonymize Transactions for user</h3>";
	$ok = true;
	$jsonRQ = createBasicRequest("");
	if (!persistIsTokenValid($jsonRQ)) {
		print "<br><font color=\"red\"> token error, aborting ... </font><br>\n"; 
		$ok = false;
	}
			
	if (!persistIsUserAdmin($jsonRQ)) {
		print "<br><font color=\"red\"> admin rights error, aborting ... </font><br>\n";
		$ok = false;
	}
	
	if ($ok == true) {
		$anonOk = persistAnonymizeTransactions($_POST["displayname"], $_POST["team"]);
		if($anonOk == true){
			print "<br><font color=\"green\"> Anonymize-user result: ok</font><br>\n";
		} else {
			print "<br><font color=\"red\"> Anonymize-user result: error </font><br>\n"; 
		}
	}
} else if(isset($_POST["submit-button-admin-deletetransaction"])){
	print "<h3 id=\"action_header\">Delete Transaction</h3>";
	$ok = true;
	$jsonRQ = createBasicRequest("");
	if (!persistIsTokenValid($jsonRQ)) {
		print "<br><font color=\"red\"> token error, aborting ... </font><br>\n"; 
		$ok = false;
	}
			
	if ($ok == true && !persistIsUserAdmin($jsonRQ)) {
		print "<br><font color=\"red\"> admin rights error, aborting ... </font><br>\n";
		$ok = false;
	}

	if ($ok == true) {
		$ok = persistDeleteTransaction($_POST["transactionid"]);
		if($ok == true){
			print "<br><font color=\"green\"> Delete-transaction result: ok</font><br>\n";
		} else {
			print "<br><font color=\"red\"> Delete-user transaction: error </font><br>\n"; 
		}
	}
} else if(isset($_POST["submit-button-admin-reverttransaction"])){
	print "<h3 id=\"action_header\">Revert Transaction</h3>";
	$ok = true;
	$jsonRQ = createBasicRequest("");
	if (!persistIsTokenValid($jsonRQ)) {
		print "<br><font color=\"red\"> token error, aborting ... </font><br>\n"; 
		$ok = false;
	}
			
	if ($ok == true && !persistIsUserAdmin($jsonRQ)) {
		print "<br><font color=\"red\"> admin rights error, aborting ... </font><br>\n";
		$ok = false;
	}

	if ($ok == true) {
		$isok = persistRevertTransaction($_POST["transactionid"]);
		if($isok == true){
			print "<br><font color=\"green\"> Revert-transaction result: ok</font><br>\n";
		} else {
			print "<br><font color=\"red\"> Revert-transaction result: error </font><br>\n"; 
		}
	}
} else if(isset($_POST["submit-button-login"])) {
	$jsonRQ = new stdClass();
	$jsonRQ->login = new stdClass();
	
	$user = htmlspecialchars($_POST["login"]);
	if (strpos($user, '@') !== false) {
		$user_arr = explode("@", $user);
		$user = $user_arr[0];
	}

	$user = $jsonRQ->login->user = $user;
	$pass = $jsonRQ->login->pass = $_POST["password"];
  
	$result_json = json_decode(handleActionLogin($jsonRQ));

	if (isset($result_json->action_result) && count($result_json->action_result)  > 0 && isset($result_json->action_result[0]->token)) {
		setcookie("banana-app-token", $result_json->action_result[0]->token, time()+86400*30);
		print "<p><br>Login ok</br></p>\n";
	} else {
		print "<p><br><font color=\"red\">Login Error!</font><br></p>\n"; 
	}
}  else if(isset($_POST["submit-button-logout"])){

	if (guiHasToken()) {
		handleActionLogout(createBasicRequest(""));
	}

	setcookie("banana-app-token", "", 1);
	unset($_POST["token"]);
	unset($_COOKIE["banana-app-token"]);
} 

print "</body>\n";
print "</html>\n";
?>