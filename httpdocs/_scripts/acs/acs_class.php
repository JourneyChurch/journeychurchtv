<?php
	/**
	*	ACS Retrieval class
	*	\author Michael Walker <mike@moonduststudios.com>
	*/	
	
	class ACS {
		private $user = "geoffhensley";									// ACS API Username
		private $pass = "XjtS!6Qk7eB*";									// ACS API Password
		private $sitenumber = "106649";									// ACS Site Number
		private $calendarid = "74b25c3d-20ea-432f-90ba-a22000d547f8";	// ACS Calendar ID to retrieve events from
		//private $calendarid = "98398c77-6e6f-4897-b065-a0be0099e487";	// ACS Calendar ID to retrieve events from
		
		public function GetMonthEvents($month, $year) {
			return $this->single_curl_acs("https://secure.accessacs.com/api_accessacs_mobile/v2/$this->sitenumber/calendars/$this->calendarid/events?startdate=$month/1/$year&enddate=$month/31/$year&pageSize=500");
		}
		
		public function GetEvents($startdate, $enddate) {
			return $this->single_curl_acs("https://secure.accessacs.com/api_accessacs_mobile/v2/$this->sitenumber/calendars/$this->calendarid/events?startdate=$startdate&enddate=$enddate&pageSize=500");
		}
		
		public function GetEvent($eventid) {
			return $this->single_curl_acs("https://secure.accessacs.com/api_accessacs_mobile/v2/$this->sitenumber/events/$eventid");
		}
		
		private function single_curl_acs($url) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_USERPWD, "$this->user:$this->pass");
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			// Response error handler
			if ($httpCode == 200) {
				return $response;
			}
			else {
				$error_response = array(
					"error" => true,
					"httpCode" => $httpCode		
				);
				
				return json_encode($error_response);
			}			
		}
	}
?>