<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed.');

//
// Module: ACS Bridge
// Version: 1.7
// Author: Ron Hickson
// URL: http://www.hicksondesign.com
//

class Acs_bridge {
	
	public $return_data = '';
	
	public function __construct() {
		$this->EE =& get_instance();
		
		$this->BASE_URL = ee()->functions->fetch_site_index();
		
		$this->_base = ee()->config->item('base_url');
		$this->_index = ee()->config->item('index_page');
		$this->theme_folder_url = ee()->config->item('theme_folder_url');
	}

	// ----------------------------------------------------------------	
	// People request - validate a login
	// ----------------------------------------------------------------	
	public function login() {
		
		ee()->load->library('soap_api');
		
		try {
			$validate = $this->EE->soap_api->validateLogin();
		} catch (SoapFault $f) {
			print_r($f);
			error_log($f->faultcode);
			$error = TRUE;
		}
			
		return $validate;
	}
	
	// ----------------------------------------------------------------	
	// Build the JS
	// ----------------------------------------------------------------	
	public function json() {
	
		$params = array(
			'fetch'			=>	ee()->TMPL->fetch_param('fetch'),
		);
		
		$aid = $this->BASE_URL.'?ACT='.ee()->functions->fetch_action_id('Acs_bridge', $params['fetch']);
		
		return $aid;
	}	
		
	// ----------------------------------------------------------------	
	// Get eventList in JSON format
	// ----------------------------------------------------------------	
	public function events_list() {
		
			ee()->load->model('acs_model');
			
			$auth = ee()->acs_model->getAuth();		
			ee()->load->library('rest_api', $auth);
			
			$params = array(
				'startdate'	=>	$_GET['start'],
				'id'		=>	$_GET['id'],
				'stop'		=>	$_GET['stop'],
				'limit'		=>	$_GET['limit'],
			);
						
			/*
			// Get a startdate to begin with
			if (empty($params['startdate'])) {
				$params['startdate'] = (int) time();
				$output['origStart'] = $params['startdate'];
				$output['displayLength'] = ($params['show_days']*86400);
			}
			
			// Set the startdate based on the 'view' parameter
			if ($params['view'] == 'week') {
				// Find beginning of current week
				$params['startdate'] = strtotime('Sunday last week', $params['startdate']);
				// Set the stopdate
				$params['stop'] = $params['startdate']+604800;
			} elseif ($params['view'] == 'month') {
				// Find beginning of current month
				$params['startdate'] = strtotime('midnight first day of this month', $params['startdate']);
				// Set the stopdate
				$params['stop'] = $params['startdate']+2592000;
			} else {
				// Default startdate is now
				$params['startdate'] = (int) time();
				// Default stopdate is last day of current month
				$params['stop'] = strtotime('midnight last day of this month');
			}
			*/
			
			// ----------------------------------------------------------------	
			// Decide which call to use depending on whether a calendar is set.
			// ----------------------------------------------------------------	
			
			$responses = '';
			
			// Try to retrieve calendars and get the calendar ID.
			try {
				if ($params['id'] != NULL) {
					
					$responses = ee()->rest_api->eventsList($params['id'], $params['startdate'], $params['stop'], 0, $params['limit']);

				} else {
					$responses = ee()->rest_api->eventsListWildcard($params['startdate'], $params['stop'], 0, $params['limit']);
				}
				
			} catch (Exception $e) {
				error_log($e->getMessage());
				$error = TRUE;
			}
			
			// Set some headers for the response.  Needed to specify the type and cache information for most browsers.
			$this->_set_headers();
			
			// Output our ajax response
			exit($responses);
	}
	
	// ----------------------------------------------------------------	
	// Details, details - get details in JSON format
	// ----------------------------------------------------------------		
	public function event() {

		ee()->load->model('acs_model');
		ee()->load->library('soap_api');
		
		$auth = ee()->acs_model->getAuth();		
		ee()->load->library('rest_api', $auth);
		
		$params = array(
			'id'	=>	$_GET['id'],
		);
						
		try {
			$result = ee()->rest_api->eventsDetail($params['id']);		
		} catch (Exception $e) {
			error_log($e->getMessage());
			$result = '';
		}
		
		if (!empty($_GET['reg']) && $_GET['reg'] == TRUE ) {
		
			// Check if the event has registration setup
			$array = json_decode($result);
			
			if ($array->AllowRegistration == 'true') {
			
				try {
					// Get the ID for the registration information		
					$regEvent = ee()->soap_api->getEventRegistrationsByID(array($params['id']));								
					$regId = $regEvent[0]['EventID'];
				
					// Get sections which contain contact information and capacity
					$sections = ee()->soap_api->getEventSections($regId);
				
					// Get the pricing information
					$registrationPeriods = ee()->soap_api->getEventRegistrationPricing($regId);
				
					foreach ($registrationPeriods as &$p) {
						$p['Cost'] = number_format((float)$p['Cost'], 2);
					}
								
					$array->Sections = $sections;
					$array->RegistrationPeriods = $registrationPeriods;
				
					$result = json_encode($array);
				
				} catch (SoapFault $f) {
					error_log($f->faultcode);
				}
			}
		}
		
		if (!empty($_GET['image']) && $_GET['image'] == TRUE) {
				
			try {
				// Get the image
				$image = ee()->soap_api->getEventImage($params['id']);
				
				if (!empty($image)) {								
					$array = json_decode($result);

					$array->Image = $image['siteurl'];
				
					$result = json_encode($array);
				}
				
			} catch (SoapFault $f) {
				error_log($f->faultcode);
			}
		}
				
		$this->_set_headers();		
		exit($result);

	}

	// ----------------------------------------------------------------	
	// Small Group Functions
	// ----------------------------------------------------------------
	
	// Get a collection of small groups
	public function small_groups() {
	
		$output = array();
		$data = '';

		ee()->load->library('soap_api');
		ee()->load->library('typography');
		ee()->typography->initialize();
			
		$defaultFields = array('GroupID','ParentID','SGName','Description','CloseDate','EndDate','GroupStatus');
		$params = array(
			'fields'			=>	ee()->TMPL->fetch_param('fields'),
			'group_id'			=>	ee()->TMPL->fetch_param('group_id'),
			'status'			=>	ee()->TMPL->fetch_param('status', 'active'),
			'parent_id'			=>	ee()->TMPL->fetch_param('parent_id'),
			'show_closed'		=>	ee()->TMPL->fetch_param('show_closed', 'no'),
		);
			
		// First check for a group_id and create an array of them
		if(!empty($params['group_id'])) {
			$params['group_id'] = explode('|', $params['group_id']);
		} else {
			$params['group_id'] = array('-1');
		}
			
		// Set to retrieve any extra fields
		if(!empty($params['fields'])) {
			$fieldsE = explode('|', $params['fields']);
			$params['fields'] = array_merge($defaultFields, $fieldsE);
		} else {
			$params['fields'] = $defaultFields;
		}
			
		// Create an array for parent ids
		if(!empty($params['parent_id'])) {
			$parent_ids = explode('|', $params['parent_id']);
		}
									
		// Fetch some data
		try {
			$results = ee()->soap_api->sgGetGroupList(array('*'), $params['group_id']);					
		} catch (SoapFault $f) {
			error_log($f->faultcode);
			$error = TRUE;
		}
		
		// Start the parsing
		$template = ee()->TMPL->tagdata;
		
		// If an error was thrown go ahead and output the error conditional
		if (isset($error)) {
			$output['error'] = 1;
			$output['output'] = $this->error_conditional($template);
		} elseif (empty($results)) {
		// If we didn't get any results parse accordingly
			$output['output'] = ee()->TMPL->no_results();
		} else {
		// Do the heavy parsing if there wasn't an error or no results
			
			// Set the current localized time
			$current = ee()->localize->now;
				
			foreach($results as $key => $result) {
				
				// Drop anything that is not active
				if($params['status'] == 'active' && $result['GroupStatus'] != 'Active') {
					unset($results[$key]);
					continue;
				}
				// Drop anything not in the parent group
				if(isset($parent_ids) && !in_array($result['ParentID'], $parent_ids)) {
					unset($results[$key]);
					continue;
				}	
				// Drop closed groups if set
				if($params['show_closed'] == 'no' && !empty($result['CloseDate']) && $result['CloseDate'] < $current) {
					unset($results[$key]);
					continue;
				}
				
				// Typography formatting
				$type_prefs = array('text_format' => 'xhtml', 'html_format' => 'safe', 'auto_links' => 'y', 'encode_email' => TRUE);
				if(!empty($result['Description'])) {
					$result['Description'] = $this->EE->typography->parse_type($result['Description'], $type_prefs);
				}
				
				// Now lets parse out the data
				$data .= ee()->TMPL->parse_variables_row($template, $result);
			}
			
			ee()->TMPL->parse($data);
			$output['output'] = ee()->TMPL->final_template;
		}
		
		// Only set headers if this is not a nested tag
		$this->_set_headers();
		
		// Output our ajax response
		return ee()->output->send_ajax_response($output);

	}
	
	// Get a specific small group
	public function small_group_detail() {
			
		$output = array();
		$data = '';

		ee()->load->library('soap_api');
		ee()->load->library('typography');
		ee()->typography->initialize();
		
		$params = array(
			'nested'	=>	ee()->TMPL->fetch_param('nested', 'no'),
		);
			
		// Check for param from tag if one is not set dynamically
		if (!empty($_GET['id'])) {
			$params['group_id'] = $_GET['id'];
		} else {
			$params['group_id'] = ee()->TMPL->fetch_param('group_id');
		}
				
		try {
			$info = ee()->soap_api->sgGetMeetingInfo($params['group_id']);
			$time = ee()->soap_api->sgGetMeetingTimes($params['group_id']);
			$result[0] = array_merge($info[0], $time[0]);

		} catch (SoapFault $f) {
			error_log($f->faultcode);
			$error = TRUE;
		}
		
		// Start doing the parsing
		$template = ee()->TMPL->tagdata;
		
		// If an error was thrown go ahead and output the error conditional
		if (isset($error)) {
			$output['error'] = 1;
			$output['output'] = $this->error_conditional($template);
		} elseif (empty($result[0])) {
		// If no results output the appropriate response if one is set
			$output['output'] = ee()->TMPL->no_results();
		} else {
			
			// Typography parsing
			$type_prefs = array('text_format' => 'xhtml', 'html_format' => 'safe', 'auto_links' => 'y', 'encode_email' => TRUE);
			if(!empty($result['Description'])) {
				$result['Description'] = ee()->typography->parse_type($result['Description'], $type_prefs);
			}
			if(!empty($result['RemindMessage'])) {
				$result['RemindMessage'] = ee()->typography->parse_type($result['RemindMessage'], $type_prefs);
			}

			$data = ee()->TMPL->parse_variables($template, $result);
			ee()->TMPL->parse($data);
			
			$output['output'] = ee()->TMPL->final_template;
		}
		
		// If tag is set as "nested" then we need to send HTML so the outer tag will continue parsing.  Otherwise we parse and send an AJAX response.
		if ($params['nested'] == 'yes') {
			return $output['output'];
		} else {
			// Only set headers if this is not a nested tag
			$this->_set_headers();
			return ee()->output->send_ajax_response($output);
		}

	}
	
	// ----------------------------------------------------------------	
	// Volunteer/Service Calls
	// ----------------------------------------------------------------
	
	// Get all opportunities for serving.
	public function opportunities() {
			
		$output = array();
		$data = '';

		ee()->load->library('soap_api');
		ee()->load->library('typography');
		ee()->typography->initialize();
									
		$params = array(
			'opp_id'			=>	ee()->TMPL->fetch_param('opportunity_id'),
			'show_closed'		=>	ee()->TMPL->fetch_param('show_closed', 'no'),
			'nested'			=>	ee()->TMPL->fetch_param('nested', 'no'),
		);
								
		// Set our opportunities to call.  If no parameter is set then check the URL for a GET variable.  If none set a default.
		if (!empty($params['opp_id'])) {
			$params['opp_id'] = explode('|', $params['opp_id']);
		} elseif (empty($params['opp_id']) && isset($_GET['opportunity_id'])) {
			$params['opp_id'] = explode('|', $_GET['opportunity_id']);
		} else {
			$params['opp_id'] = array('-1');
		}
					
		try {
			$opps = ee()->soap_api->vmGetServeOpportunities(array('*'),$params['opp_id']);
		} catch (SoapFault $f) {
			error_log($f->faultcode);
			$error = TRUE;
		}
		
		// Start the parsing
		$template = ee()->TMPL->tagdata;

		// If an error was thrown go ahead and output the error conditional
		if (isset($error)) {
			$output['error'] = 1;
			$output['output'] = $this->error_conditional($output);
		} elseif (empty($opps)) {
		// If we didn't get any results parse accordingly
			$output['output'] = ee()->TMPL->no_results();
		} else {

			foreach($opps as $key => $opp) {
			
				// Drop closed groups if set
				if($params['show_closed'] == 'no' && !empty($opp['ClosingDate']) && $opp['ClosingDate'] < time()) {
					unset($opps[$key]);
					continue;
				}
	
				// Typography formatting
				$type_prefs = array('text_format' => 'xhtml', 'html_format' => 'safe', 'auto_links' => 'y', 'encode_email' => TRUE);
				if(!empty($opp['NeedDesc'])) {
					$opp['NeedDesc'] = ee()->typography->parse_type($opp['NeedDesc'], $type_prefs);
				}
								
				// Now lets parse out the data
				$data .= ee()->TMPL->parse_variables_row($template, $opp);
			}
			
			ee()->TMPL->parse($data);
			$output['output'] = ee()->TMPL->final_template;
		}
						
		// If tag is set as "nested" then we need to send HTML so the outer tag will continue parsing.  Otherwise we parse and send an AJAX response.
		if ($params['nested'] == 'yes') {
			return $output['output'];
		} else {
			// Only set headers if this is not a nested tag
			$this->_set_headers();
			return ee()->output->send_ajax_response($output);
		}
	}

	// Get a specific serving opportunity.
	public function positions() {
			
		$output = array();
		$data = '';

		ee()->load->library('soap_api');
		ee()->load->library('typography');
		ee()->typography->initialize();

		$params = array(
			'opportunity_id'	=>	(int) ee()->TMPL->fetch_param('opportunity_id', -1),
			'position_id'		=>	(int) ee()->TMPL->fetch_param('position_id'),
			'sort'				=>	ee()->TMPL->fetch_param('sort'),
			'active'			=>	ee()->TMPL->fetch_param('active', 'yes'),
			'nested'			=>	ee()->TMPL->fetch_param('nested', 'no'),
		);
			
		// Fetch parameters from URL.  If none are set the set to default.
		if (empty($params['position_id']) && !empty($_GET['position_id'])) {
			$params['position_id'] = (int) $_GET['position_id'];
		} elseif (empty($params['position_id']) && empty($_GET['position_id'])) {
			$params['position_id'] = -1;
		}
				
		// Get data from ACS
		try {
			$pos = ee()->soap_api->vmGetPositionOpportunities($params['position_id'], $params['opportunity_id']);
		} catch (SoapFault $f) {
			error_log($f->faultcode);
			$error = TRUE;
		}
		
		// Start the parsing
		$template = ee()->TMPL->tagdata;
			
		// If an error was thrown go ahead and output the error conditional
		if (isset($error)) {
			$output['error'] = 1;
			$output['output'] = $this->error_conditional($output);
		} elseif (empty($pos)) {
			$output['output'] = ee()->TMPL->no_results();
		} else {
			
			// Do some sorting and filtering
			foreach($pos as $k => $p) {
			
				// Drop closed groups if set
				if($params['active'] == 'yes' && $p['Active'] != 'true') {
					unset($pos[$k]);
					continue;
				}
				
				// Set a few array pieces for sorting next
				$sort_urgent[] = $pos[$k]['Urgent'];
				$sort_feat[] = $pos[$k]['Featured'];
			}
				
			// Sort to place 'featured' opps at the top.
			if($params['sort'] == 'featured') {
				array_multisort($sort_feat, SORT_DESC, $pos);
			}
				
			// Sort to place 'urgent' opps at the top.				
			if($params['sort'] == 'urgent') {
				array_multisort($sort_urgent, SORT_DESC, $pos);				
			}				
			
			// Typography parsing
			$type_prefs = array('text_format' => 'xhtml', 'html_format' => 'safe', 'auto_links' => 'y', 'encode_email' => TRUE);

			foreach($pos as $k => $p) {
				
				// First do some typography work.
				if(!empty($p['Description'])) {
					$p['Description'] = ee()->typography->parse_type($p['Description'], $type_prefs);
				}
				
				// Now parse the data
				$data .= ee()->TMPL->parse_variables_row($template, $p);
			}
			
			ee()->TMPL->parse($data);
			$output['output'] = ee()->TMPL->final_template;
		}
			
		if($params['nested'] == 'yes') {
			return $output['output'];
		} else {
		// Only set headers if not nested
			$this->_set_headers();
			return ee()->output->send_ajax_response($output);
		}
	}
	// ----------------------------------------------------------------	
	// A few simple helper functions
	// ----------------------------------------------------------------
	
	// Simply set headers for caching				
	private function _set_headers() {
		header("Expires: ".(time() + 600)."");
		header("Pragma: cache");
		header("Cache-Control: max-age=600");
		header("Content-Type: application/json");
	}
	
	// A special function for {if error}.
	private function error_conditional($e_msg = '') {
		if (preg_match("/".LD."if error".RD."(.*?)".LD.'\/'."if".RD."/s", $e_msg, $match)) {
			if (stristr($match[1], LD.'if')) {
				$match[0] = ee()->functions->full_tag($match[0], $e_msg, LD.'if', LD.'\/'."if".RD);
			}
			
			$error = substr($match[0], strlen(LD."if error".RD), -strlen(LD.'/'."if".RD));
			return $error;
		}
	}
	
	// Add tags to each event
	private function add_tags($event_id) {
		$array = array();
		try {
			$tags = ee()->soap_api->getTagsByEventId($event_id);
			if (!empty($tags)) {
				foreach ($tags as $tag) {
					$tmp = array();
					$tmp['tag'] = $tag['tagname'];
					$tmp['tagid'] = $tag['tagid'];
					$array[] = $tmp;
				}
			}
			return $array;
		} catch (SoapFault $f) {
			error_log($f->faultcode);
			return $array;
		}
	}
	
	// Check if tags were one's requested
	private function tag_filter($group, $filter) {
		$matches = array();
		foreach ($group as $item) {
			$match = array_intersect($filter, $item);
			if (!empty($match)) {
				return TRUE;
				break;
			}
		}
	}
	
	// Add locations to event
	private function location_name($locationid) {
		$array = '';
		try {
			$locations = ee()->soap_api->getLocations();
			foreach ($locations as $loc) {
				if ($loc['resourceid'] == $locationid) {
					$array['locationname'] = $loc['resourcename'];
					$array['categoryname'] = $loc['resourcecategoryname'];
					break;
				}
			}
			return $array;
		} catch (SoapFault $f) {
			error_log($f->faultcode);
			return $array;
		}
	}
	
}

/* End of file mod.acs_events.php */
/* Location: ./system/expressionengine/third_party/acs_bridge/mod.acs_bridge.php */