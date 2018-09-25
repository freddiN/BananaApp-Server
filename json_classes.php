<?php
	class BananaActionTransaction {
		public $id = "";
		public $timestamp = "";
		public $from_user = "";
		public $to_user = "";
		public $bananacount = 0;
		public $comment = "";
		public $source = "";
        public $category = "";
		
		function __construct($id, $timestamp, $from_user, $to_user, $count, $comment, $source, $category) {
			$this->id = $id;
			$this->timestamp = $timestamp;
			$this->from_user = $from_user;
			$this->to_user = $to_user;
			$this->bananacount = intval($count);
			$this->comment = $comment;
			$this->source = $source;
            $this->category = $category;
		} 
	}
				
	class BananaActionUser {
		public $id = 0;
		public $display_name  = "";
		public $ad_user  = "";
		public $bananas_to_spend = 0;
		public $bananas_received = 0;
		public $is_admin = 0;
		public $login_token  = "";
		public $token_expiration_timestamp  = "";
		public $token_duration  = "";
		
		function __construct($id, $display_name, $ad_user, $is_admin, $spend, $received, $token, $token_expiration, $token_duration) {
			$this->id = intval($id);
			$this->display_name = $display_name;
			$this->ad_user = $ad_user;
			$this->is_admin = intval($is_admin);
			$this->bananas_to_spend = intval($spend);
			$this->bananas_received = intval($received);
			$this->login_token = $token;
			$this->token_expiration_timestamp = $token_expiration;
			$this->token_duration = $token_duration;
		} 
	}
	
	class BananaLogin {
		public $token = "";
		public $expiration = "";
		
		function __construct($token, $expiration) {
			$this->token = $token;
			$this->expiration = $expiration;
		} 
	}
	
	class BananaAction {
		public $actionname;
		public $status;
		public $action_result;
	}
	
	class MonthlyStats {
		public $month = "";
		public $year = "";
		public $count = 0;
		
		function __construct($month, $year, $count) {
			$this->month = $month;
			$this->year = $year;
			$this->count = intval($count);
		} 
	}
?>