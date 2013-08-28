<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @package		ACS Bridge
 * @author		Ron Hickson
 * @copyright	Copyright (c) 2013, Hickson Design
 * @link		http://hicksondesign.com
 * @since		Version 2.0
 */

// ------------------------------------------------------------------------

/**
 * ACS REST Library
 *
 * Access ACS API Library.  This API only deals with the REST calls for getting data.  If you need to put data or do more complex fetches then use the SOAP api methods.
 *
 * @category	Libraries
 * @author		Ron Hickson
 *
 */
class Rest_api {
		
	function __construct($config) {
		
		$this->auth = $config;
						
		/*
		// Set timezone offset for use throughout the API
		$query = $this->EE->db->query('SELECT dst FROM exp_acs_settings');
		if($query->row('dst') == 0) {
			$this->zone_offset = '-5:00';
		} else {
			$this->zone_offset = '-4:00';
		}
		*/			
	}

// ------------------------------------------------------------------------
// Access ACS Calendar
// ------------------------------------------------------------------------

	/**
	 * Calendar List
	 *
	 * Returns a list of calendars.
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/calendars
	 * @return json
	**/
	function calendarList() {
	
		ee()->load->library('file_cache');
		
		// Get the cache time setting from the DB
		$query = ee()->db->query('SELECT calendar_cache FROM exp_acs_settings');
		$cal_cache = (int) $query->row('calendar_cache');
		
		// Get the cache				
		$cache = ee()->file_cache->getCache('acs_bridge', 'calendars', $cal_cache);
		
		if ($cache === FALSE) {
		
			$url = 'https://secure.accessacs.com/api_accessacs_mobile/v2/'.$this->auth['site_number'].'/calendars';
		
			$response = $this->getRequest($url);
			
			ee()->file_cache->saveCache('acs_bridge', 'calendars', $response);

			return $response;
		} else {
			return $cache['calendars'];
		}
	}

// ------------------------------------------------------------------------
// Access ACS People
// ------------------------------------------------------------------------

	/**
	 * Available Comment Types
	 *
	 * Returns the comment types the user has Add rights to.
	 *
	 * @type	GET
	 * @url		https://secure.accessacs.com/api_accessacs_mobile/v2/comments/GetAvailableCommentTypes
	 * @return json
	**/
	function availableCommentTypes() {
		$url = 'https://secure.accessacs.com/api_accessacs_mobile/v2/comments/GetAvailableCommentTypes';
		
		$response = $this->getRequest($url);
		return $response;		
	}
	
	/**
	 * Get Available Comment Types
	 *
	 * Returns a list of comment types that the user has rights to view.
	 *
	 * @type	GET
	 * @url		https://secure.accessacs.com/api_accessacs_mobile/comments/GetAvailableCommentTypes
	 * @return json
	**/
	function getAvailableCommentTypes() {
		$url = 'https://secure.accessacs.com/api_accessacs_mobile/comments/GetAvailableCommentTypes';
		
		$response = $this->getRequest($url);
		return $response;	
	}
	
	/**
	 * Comment Types
	 *
	 * Returns the available Comment Types the user can create change requests for.
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/types/comments
	 * @return json
	**/
	function commentTypes() {
		$url = 'https://secure.accessacs.com/api_accessacs_mobile/v2/'.$this->auth['site_number'].'/types/comment';
		
		$response = $this->getRequest($url);
		return $response;
	}
	
	/**
	 * Get Comments
	 *
	 * Returns comment information for a specified individual and comment type.
	 *
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/comments/getcomments?id=<indvid>&commenttypeid=<commenttypeid>
	 * @return json
	**/
	function getComments($id, $commentTypeId) {
		$url = 'https://secure.accessacs.com/api_accessacs_mobile/comments/getcomments?id='.$id.'&commenttypeid='.$commentTypeId;
		
		$response = $this->getRequest($url);
		return $response;
	}
	
	/**
	 * Comment Summary
	 *
	 * Summary information for comment types including the number of comments per type and the date of the last comment per type for a specific individual.
	 *
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/comments?ID=<id>
	 * @return json
	**/
	function commentSummary($id) {
		
	}
	
	/**
	 * Individuals
	 *
	 * Returns a list of individuals the user has rights to view.
	 *
	 * @param	query
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/individuals?q=<*querystring>&pageIndex=<0-based int>&pageSize=<int>
	 * @return json
	**/
	function individuals($query, $pageIndex = 0, $pageSize = 20) {
		
	}
	
	/**
	 * Individuals Comments Detail
	 *
	 * Returns a list of the comments for the supplied Comment Type Id for an individual.
	 *
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/individuals/<Individual Id>/comments/<Comment Type Id>
	 * @return json
	**/
	function individualsCommentsDetail($individualId, $commentTypeId) {
		
	}

	/**
	 * Individuals Comment List
	 *
	 * Returns a list of comments the user is able to view based on their personal user security.
	 *
	 * @param	int
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/individuals/<individual id>/comments?pageIndex=<0-based int>&pageSize=<int>
	 * @return json
	**/
	function individualsCommentList($individualId, $pageIndex = 0, $pageSize = 20) {
		
	}
	
	/**
	 * Individual Comment Summary
	 *
	 * Returns comments summary for a specified individual based on comment types the user has rights to.
	 *
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/comments?ID=<indvid>
	 * @return json
	**/
	function individualCommentSummary($individualId) {
		
	}
	
	/**
	 * Individuals Detail
	 *
	 * View individual detail for the provided id.
	 *
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/individuals/<individual Id>
	 * @return json
	**/
	function individualsDetail($individualId) {
		
	}
	
	/**
	 * Individual Get Detail
	 *
	 * Returns the details for a specific individual.
	 *
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/individuals/GetDetail?id=<indvid>
	 * @return json
	**/
	function individualGetDetail($individualId) {
		
	}
	
	/**
	 * Individuals List
	 *
	 * Returns a list of individuals using the supplied parameters.
	 *
	 * @param	query
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/individuals?q=<query string>&pageIndex=<0-based int>&pageSize=<int>
	 * @return json
	**/
	function individualsList($query, $pageIndex = 0, $pageSize = 20) {
		
	}
	
	/**
	 * Individual Lookup
	 *
	 * Performs a search for a list of individuals to choose from for assigning, adding, or referencing.
	 *
	 * @param	query
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/individuals/find?q=<"FirstName"/LastName"/GoesByName">
	 * @return json
	**/
	function individualLookup($query) {
		
	}
	
	/**
	 * Find By Email
	 *
	 * Returns a list of logins that the user's email address and password are associated with.  The request body must contain the following:
	 *	{
	 *		email:"<email address>",
	 *		password:"<password>"
	 *	}
	 *
	 * @type	PUT or POST
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/accounts/findbyemail
	 * @return json
	**/
	function findByEmail() {
		
	}
	
	/**
	 * Post Comment Change Request
	 *
	 * Post a Comment Change Request to be permanently added to the database.
	 *
	 * @param	int
	 * @param	int
	 *
	 *	@type	POST
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/individuals/<Individual Id>/comments/<Comment Type Id>
	 * @return json
	**/
	function commentChangeRequest($individualId, $commentTypeId) {
		
	}
	
	/**
	 * User Account Security
	 *
	 * Returns the account and security information for the user supplied in the Basic Authentication.
	 *
	 * @param	int
	 *
	 *	@type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/account
	 * @return json
	**/
	function userAcctSecurity($site_number) {
		
	}
	
// ------------------------------------------------------------------------
// Access ACS Connections
// ------------------------------------------------------------------------

	/**
	 * Assignment List
	 *
	 * Returns a list of connection assignments that are grouped by connection type and sorted by Oldest Due Date.
	 *
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/connections/assignments?pageIndex=<0-based int>&pageSize=<int>
	 * @return json
	**/
	function assignmentList($pageIndex = 0, $pageSize = 20) {
		
	}
	
	/**
	 * Assignment List By TypeId
	 *
	 * Returns a list of connections assigned to the user by the Connection TypeId.
	 *
	 * @param	int
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/connections/assignments/<connectionTypeId>?pageIndex=<0-based int>&pageSize=<int>
	 * @return json
	**/
	function assignmentListTypeId($connectionTypeId, $pageIndex = 0, $pageSize = 20) {
		
	}
	
	/**
	 * Available Responses
	 *
	 * Returns the available responses the connection may use to complete the assignment.
	 *
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/connections/<connectionId>/responses
	 * @return json
	**/
	function availableResponses($connectionId) {
		
	}
	
	/**
	 * Connections Change Request
	 *
	 * Post a new change request to Close, Modify, Reassign, or Create an Outreach Connection
	 *
	 *
	 * @type	POST
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/connections
	 * @return json
	**/
	function connectionsChangeRequest() {
		
	}
	
	/**
	 * Connection Details
	 *
	 * Returns the details of a Connection. IncludeSelf parameter will include yourself in the TeamMember List when true. The default is IncludeSelf =false.
	 *
	 * @param	int
	 * @param	bool
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/connections/<ConnectionId>?IncludeSelf=<bool>
	  * @return json
	**/
	function connectionDetails($connectionId, $includeSelf = FALSE) {
		
	}
	
	/**
	 * Connections List
	 *
	 * Returns a list of connections. You must have view all connections security for this API to work. The IncludeSelf parameter will include yourself in the TeamMember List when set to true. The defaults for this API are IncludeSelf =false, and includeCompleted=false.
	 *
	 * @param	bool
	 * @param	bool
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/connections?includeCompleted=<bool>&includeSelf=<bool>pageIndex=<0-based int>&pageSize=<int>
	  * @return json
	**/
	function connectionsList($includeCompleted = FALSE, $includeSelf = FALSE, $pageIndex = 0, $pageSize = 20) {
		
	}
	
	/**
	 * Connections Responses
	 *
	 * Returns available responses for connections
	 *
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/types/responses/<ConnectionTypeId>
	 * @return json
	**/
	function connectionResponses() {
		$url = 'https://secure.accessacs.com/api_accessacs_mobile/v2/'.$this->auth['site_number'].'/types/responses';
		
		$response = $this->getRequest($url);
		return $response;
	}
	
	/**
	 * Connections Team List
	 *
	 * Returns a list of teams and the team member count.
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/connections/teams?q=<filter string>
	 * @return json
	**/
	function connectionsTeamList() {
		
	}
	
	/**
	 * Connections Team Member List
	 *
	 * Returns a list of team members of the supplied TeamId.
	 *
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/connections/teams/<TeamId>
	 * @return json
	**/
	function connectionsTeamMemberList($teamId) {
		
	}

	/**
	 * Connection Types
	 *
	 * Returns a list of connection types.
	 *
	 * @type	GET
	 * @url		https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/types/connections
	 * @return json
	**/
	function connectionTypes() {
		
	}
		
	/**
	 * Individuals Connection Detail
	 *
	 * Returns a single Connection's Detail. The default is includeSelf=true.
	 *
	 * @param	int
	 * @param	int
	 * @param	bool
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/individuals/<IndvId>/connections/<ConnectionId>?includeSelf=<bool>
	 * @return json
	**/
	function individualsConnectionDetail($individualId, $connectionId, $includeSelf = FALSE) {
		
	}
	
	/**
	 * Individuals Connection History
	 *
	 * Returns the connection history for an individual. includeSelf will show yourself in the Team Member list if there. The default is true. includeOpen will return all open connections as well. The default is false.
	 *
	 * @param	int
	 * @param	bool
	 * @param	bool
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/individuals/<IndvId>/connections?includeSelf=<false>&includeOpen=<false>pageIndex=<0-based int>&pageSize=<int>
	 * @return json
	**/
	function individualsConnectionHistory($individualId, $includeSelf = TRUE, $includeOpen = FALSE, $pageIndex = 0, $pageSize = 20) {
	}
	
// ------------------------------------------------------------------------
// Facility Scheduler Events
// ------------------------------------------------------------------------
	
	/**
	 * Events Detail
	 *
	 * Returns detailed information about the event identified by the EventId supplied in the URL.
	 *
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url		https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/events/<EventId>
	 * @return 	json
	**/
	function eventsDetail($eventId) {
		
		$url = 'https://secure.accessacs.com/api_accessacs_mobile/v2/'.$this->auth['site_number'].'/events/'.$eventId;
		
		$response = $this->getRequest($url);
		return $response;
	}

	/**
	 * Events List
	 *
	 * Returns a list of events for the current month, unless a specific date range is entered. Use the MM/DD/YYYY format when entering startDate and stopDate.
	 *
	 * @param	int
	 * @param	date
	 * @param	date
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/calendars/<CalendarId>/events?startDate=<startDate>&stopDate=<stopDate>&pageIndex=<0-based int>&pageSize<int>
	 * @return json
	**/
	function eventsList($calendarId, $startDate, $stopDate, $pageIndex = 0, $pageSize = NULL) {

		// Format the date for the API
		$startDate = date('m/d/Y', $startDate);
		$stopDate = date('m/d/Y', $stopDate);
				
		$url = 'https://secure.accessacs.com/api_accessacs_mobile/v2/'.$this->auth['site_number'].'/calendars/'.$calendarId.'/events?startDate='.$startDate.'&stopDate='.$stopDate.'&pageIndex='.$pageIndex.'&pageSize='.$pageSize;
				
		$response = $this->getRequest($url);
		return $response;
	}
	
	/**
	 * Events List Wildcard
	 *
	 * Returns a list of events regardless of the calendar they belong to. Use the MM/DD/YYYY format when entering startDate and stopDate.
	 *
	 * @param	date
	 * @param	date
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/events?startDate=<date>&stopDate=<date>&pageIndex=<0-based int>&pageSize<int>
	 * @return json
	**/
	function eventsListWildcard($startDate, $stopDate, $pageIndex = 0, $pageSize = NULL) {

		// Format the date for the API
		$startDate = date('m/d/Y', $startDate);
		$stopDate = date('m/d/Y', $stopDate);
				
		$url = 'https://secure.accessacs.com/api_accessacs_mobile/v2/'.$this->auth['site_number'].'/events?startDate='.$startDate.'&stopDate='.$stopDate.'&pageIndex='.$pageIndex.'&pageSize='.$pageSize;
		
		$response = $this->getRequest($url);
		return $response;
	}

	/**
	 * Events By Date Range
	 *
	 * Returns a list of events. Depending on the start and end date specified and the login used, the information displayed may change. Use the MM/DD/YYYY formatting when entering startDate and stopDate. The calendarId field is comma delimited.
	 *
	 * @param	date
	 * @param	date
	 * @param	int
	 *
	 * @type	GET
	 * @url		https://secure.accessacs.com/api_accessacs_mobile/Events?startdate=<startdate>&stopdate=<stopdate>&calendarid=<calendarid>
	 * @return	json
	 * @return	Description, EventDateId, EventId, EventName, EventType, EventTypeId, IsPublished, LocationId, Location, StartDate, StopDate, Status, CalendarName, IsRecurringEvent, CalendarId, AllowRegistration
	**/
	function getEventsByDateRange($startDate, $stopDate, $calendarId = NULL) {

		// Format the date for the API
		$startDate = date('m/d/Y', $startDate);
		$stopDate = date('m/d/Y', $stopDate);
		
		if ($calendarId != NULL) {
			$url = 'https://secure.accessacs.com/api_accessacs_mobile/Events?startdate='.$startDate.'&stopdate='.$stopDate.'&calendarid='.$calendarId;
		} else {
			$url = 'https://secure.accessacs.com/api_accessacs_mobile/Events?startdate='.$startDate.'&stopdate='.$stopDate;
		}

		$response = $this->getRequest($url);
		return $response;
	}
	
	/**
	 * Get Single Event
	 *
	 * Pulls information for a specific event. The calendarName and calendarId returned only reflect the primary calendar.
	 *
	 * @param	int
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/Events/getDetail?id=<EventId>
	 * @return json
	**/
	function getSingleEvent($eventId) {
		$url = 'https://secure.accessacs.com/api_accessacs_mobile/Events/getDetail?id='.$eventId;
		
		$respone =$this->getRequest($url);
		return $respone;
	}
	
	/**
	 * Locations List
	 *
	 * Returns a list of all locations for the site.  These locations can be primary or non-primary locations.
	 *
	 * @param	int
	 *
	 * @type	GET
	 * @url		https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/locations
	 * @return json
	**/
	function locationsList() {
	
		ee()->load->library('file_cache');
		
		// Get the location cache time setting from DB
		$query = ee()->db->query('SELECT location_cache FROM exp_acs_settings');
		$loc_cache = (int) $query->row('location_cache');
		
		$cache = ee()->file_cache->getCache('acs_bridge', 'locations', $loc_cache);
		
		if ($cache === FALSE) {
			
			$url = 'https://secure.accessacs.com/api_accessacs_mobile/v2/'.$this->auth['site_number'].'/locations';
		
			$response =$this->getRequest($url);

			ee()->file_cache->saveCache('acs_bridge', 'locations', $response);
			
			return $response;
		} else {
			return $cache['locations'];
		}
	}

	/**
	 * Event List by Location
	 *
	 * Returns a list of events based on the locationId for the current month unless a specified date range is supplied.  LocationId can be a non-primary locationId.
	 *
	 * @param	int
	 * @param	date
	 * @param	date
	 * @param	int
	 * @param	int
	 *
	 * @type	GET
	 * @url		https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/locations/<LocationId Guid>/events?startDate=<date>&stopDate=<date>&pageIndex=<0-based int>&pageSize<int>
	 * @return json
	**/
	function eventsListByLocation($locationId, $startDate = NULL, $stopDate = NULL, $pageIndex = 0, $pageSize = 50) {
		$url = 'https://secure.accessacs.com/api_accessacs_mobile/v2/'.$this->auth['site_number'].'/locations/'.$locationId.'/events?startDate='.$startDate.'&stopDate='.$stopDate.'&pageIndex='.$pageIndex.'&pageSize'.$pageSize;
		
		$respone =$this->getRequest($url);
		return $respone;
	}

	
// ------------------------------------------------------------------------
// Access ACS Financials
// ------------------------------------------------------------------------

	/**
	 * Merchant Account
	 *
	 * Returns the merchant account settings need to process an ACS Pay Plus transaction.
	 *
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<site number>/account/merchant
	 * @return json
	**/
	function merchantAccount() {
		
	}
	
	/**
	 * Online Giving Funds Index
	 *
	 * Returns a list of funds available for Online Giving.
	 *
	 *
	 * @type	GET
	 * @url	https://secure.accessacs.com/api_accessacs_mobile/v2/<sitenumber>/onlinegiving
	 * @return json
	**/
	function onlineGivingFunds() {
		
	}


// ------------------------------------------------------------------------
// Access ACS Organizations
// ------------------------------------------------------------------------

	
// ------------------------------------------------------------------------
// cURL Helper
// ------------------------------------------------------------------------
	
	/* Simple function to make the cURL request*/
	protected function getRequest($url) {
		$ch = curl_init($url);
		
		// Gotta have this!  Without it the JSON is displayed immediately with no further parsing.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		// Required authentication.
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('SiteNumber: '.$this->auth['site_number']));
		curl_setopt($ch, CURLOPT_USERPWD, $this->auth['username'].':'.$this->auth['password']);

		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;
	}
}
