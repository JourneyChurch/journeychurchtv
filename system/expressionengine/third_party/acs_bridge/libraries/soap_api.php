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
 * Access ACS API Library.  This API only deals with the SOAP calls for getting and putting data.  For simpler and faster ascess to get only consider using the REST api methods.
 */
class Soap_api {
		
	function __construct() {
		
		$this->EE =& get_instance();
		ee()->access_client = new soapClient('https://secure.accessacs.com/acscfwsv2/wsca.asmx?wsdl', array('connection_timeout' => 120));
		ee()->event_client = new soapClient('https://secure.accessacs.com/acscfwsv2/wscea.asmx?wsdl', array('connection_timeout' => 120));
		
		// Get the set timezone for use throughout the API
		$query = ee()->db->query('SELECT dst FROM exp_acs_settings');
		if($query->row('dst') == 0) {
			$this->zone_offset = '-6:00';
		} else {
			$this->zone_offset = '-5:00';
		}			
	}

// ------------------------------------------------------------------------
// Access ACS General Calls
// ------------------------------------------------------------------------
 	
	function _getLoginToken() {
	
		ee()->load->library('file_cache');
		$cache = ee()->file_cache->getCache('acs_bridge', 'token', 3600);
				
		if ($cache === FALSE) {
			$query = ee()->db->query('SELECT secid, site_number FROM exp_acs_settings');
			
			if ($query->num_rows() > 0) {
				$auth = $query->row_array();
				
				if($auth['secid'] == NULL || $auth['site_number'] == NULL) {
					throw new SoapFault('Settings', lang('no_secid'));
					exit();
				}
				
				$tokenArray = array(
					'secid' 	=>	$auth['secid'],
					'siteid'	=>	$auth['site_number'],
				);
			   
				try {
					$getToken = ee()->access_client->getLoginToken($tokenArray);						
					ee()->file_cache->saveCache('acs_bridge', 'token', $getToken->getLoginTokenResult);
					return $getToken->getLoginTokenResult;
				} catch (SoapFault $f) {
					throw new SoapFault($f->faultcode, $f->faultstring);
				}
			} else {
				return FALSE;
			}
		} else {
			return $cache['token'];
		}
	}
	
	// ------------------------------------------------------------------------
	// General ACS People Calls
	// ------------------------------------------------------------------------
	
	// Validate login
	function validateLogin($username, $password) {
		
		try {
			$loginArray = array(
				'token'		=> $this->_getLoginToken(),
				'username'	=>	$username,
				'password'	=> $password,
				'bHashCode'	=> 'false',
			);
			
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->access_client->validateAccessACSLogin($loginArray);
			$xmlResponse = $xml->validateAccessACSLoginResult->any;
			
			// Master array of keys to map
			$m = array('username','email','unifiedloginid','loginid','indvid','hashcode');
			
			// Convert the XML to an array for easier management and caching
			$validate = $this->_xml_parser($xmlResponse, $m);

			return $validate;

		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	// Get security profiles
	function getSeProfiles() {
		
		try {
			// Fetch the XML/SOAP response for use
			$xml = ee()->access_client->getseProfiles($this->_getLoginToken());
			$xmlResponse = $xml->getseProfilesResult->any;
			
			// Master array of keys to map
			$m = array('profileId','profileDesc','comment','profileType','sysProfile','customProfile','profileTypeId');
			
			// Convert the XML to an array for easier management and caching
			$profiles = $this->_xml_parser($xmlResponse, $m);
			
			return $profiles;
			
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	// Add individual
	function addIndividual($array) {
		
		try {
			$addIndivArray = $array;
			
			$addIndivArray['token'] = $this->_getLoginToken();
			
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->access_client->peAddIndividual($addIndivArray);
			$xmlResponse = $xml->peAddIndividualResult->any;
			
			// Need to handle the response somehow

		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
		
	}
	
	// Add/Update address
	function updateAddress($array) {
		
		try {
			$updateAddressArray = $array;
			
			$updateAddressArray['token'] = $this->_getLoginToken();
			
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->access_client->peAddUpdateAddress($updateAddressArray);
			$xmlResponse = $xml->peAddUpdateAddressResult->any;
			
			// Need to handle the response somehow

		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
		
	}

	// Add/Update email address
	function updateEmailAddress($array) {
		
		try {
			$updateEmailArray = $array;
			
			$updateEmailArray['token'] = $this->_getLoginToken();
			
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->access_client->peAddUpdateEmailAddress($updateEmailArray);
			$xmlResponse = $xml->peAddUpdateEmailAddressResult->any;
			
			// Need to handle the response somehow

		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}

	}
	
	// Add/Update phone
	function updatePhone($array) {
		
		try {
			$updatePhoneArray = $array;
			
			$updatePhoneArray['token'] = $this->_getLoginToken();
			
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->access_client->peAddUpdatePhone($updatePhoneArray);
			$xmlResponse = $xml->peAddUpdatePhoneResult->any;
			
			// Need to handle the response somehow

		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}

	}
	
	// ------------------------------------------------------------------------
	// General ACS Event Registration Calls
	// ------------------------------------------------------------------------
		
	// Returns the General API EventID by passing it the solutioneventids(eventid from Event API). This call must be made before any registration data can be pulled.
	function getEventRegistrationsByID($arrayIds) {
							
		try {
			$SolutionsArray = array(
				'token'				=>	$this->_getLoginToken(),
				'solutioneventids'	=>	$arrayIds,
			);
		
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->access_client->getEventRegistrationsByID($SolutionsArray);	
			$xmlResponse = $xml->getEventRegistrationsByIDResult->any;
						
			// Convert the XML to an array for easier management and caching
			$array = $this->_xml_parser($xmlResponse);
																								
			return $array;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}

	// Get some of the event section details.  This returns the name, description, stop time, location, contact email and phone, capacity, reserved, and filled count.
	function getEventSections($r_eventid) {

		try {
			$SectionsArray = array(
				'token'			=>	$this->_getLoginToken(),
				'eventID'		=>	$r_eventid,
			);
			
			$xml = ee()->access_client->getEventSections($SectionsArray);
			$xmlResponse = $xml->getEventSectionsResult->any;
	
			$array = $this->_xml_parser($xmlResponse);
			//$array = $this->_timestamp($array);

			return $array;	
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}

	// Get pricing for the event.  This will return the sectionid, name, times, and cost.
	function getEventRegistrationPricing($r_eventid) {
		try {
			$PricingArray = array(
				'token'			=>	$this->_getLoginToken(),
				'eventID'		=>	$r_eventid,
			);
			
			$xml = ee()->access_client->getEventRegistrationPricing($PricingArray);			
			$xmlResponse = $xml->getEventRegistrationPricingResult->any;
			
			// Parse the XML to an array			
			$array = $this->_xml_parser($xmlResponse);
			//$array = $this->_timestamp($array);

			return $array;	
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	// Get any registration rules that are set.  Requires a call to getEventSections be made.
	function getEventRegistrationRules($r_eventid, $r_sectionid) {
		try {
			$RulesArray = array(
				'token'			=>	$this->_getLoginToken(),
				'eventID'		=>	$r_eventid,
				'sectionID'		=>	$r_sectionid,
			);
			
			$xml = ee()->access_client->getEventRegistrationRules($RulesArray);
			$xmlResponse = $xml->getEventRegistrationRulesResult->any;

			// Master array of keys to map
			$m = array('eventid','sectionid','restriction','enforced');
			
			$array = $this->_xml_parser($xmlResponse, $m);
			$array = $this->_timestamp($array);

			return $array;	
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}	
	}
	
	// ------------------------------------------------------------------------
	// General ACS Small Group Calls
	// ------------------------------------------------------------------------
	
	// Fetch small groups data by group id (or pulls all group data). Fields returned include groupid, parentid, sgname, hasroster, lastupdated, treepos, groupid1, description, location, locdirections, closedate, groupstatus, childcare, topic, datecreated, dataupdated, address1, address2, city, state, zipcode, cat1, webpage, publicgroup, startdate, enddate, emailparent, remindmessage, markingnotification, longitude, latitude, maxroster.
	function sgGetGroupList($fieldlist, $groupids) {
		try {
			$groupArr = array(
				'token'			=>	$this->_getLoginToken(),
				'fieldlist'		=>	$fieldlist,
				'groupids'		=>	$groupids,
			);
			
			$xml = ee()->access_client->sgGetGroupList($groupArr);
			$xmlResponse = $xml->sgGetGroupListResult->any;

			// Master array of keys to map
			$m = array('GroupID','ParentID','SGName','HasRoster','Description','Location','LocDirections','CloseDate','GroupStatus','ChildCare','Topic','Address1','Address2','City','State','Zipcode','Cat1','Webpage','PublicGgroup','StartDate','EndDate');
						
			$array = $this->_xml_parser($xmlResponse, $m);
			$array = $this->_timestamp($array);
			
			uasort($array, array($this,'_sort'));
			$getCalEvents = array_values($array);

			return $array;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}	
	}
	
	// Returns an array of arrays containing category names (catname) and category id's (catid) for the Small Group structure.
	function sgGetCatNames() {

		ee()->load->library('file_cache');
		$cache = ee()->file_cache->getCache('acs_bridge', 'sg_categories', 2592000);
		
		if($cache === FALSE) {
			try {
				$token = array(
					'token'			=>	$this->_getLoginToken(),
				);
			
				$xml = ee()->access_client->sgGetCatNames($token);
				$xmlResponse = $xml->sgGetCatNamesResult->any;

				// Master array of keys to map
				$m = array('CatName','CatID');
				
				$array = $this->_xml_parser($xmlResponse, $m);
				ee()->file_cache->saveCache('acs_bridge', 'sg_categories', $array);
				return $array;	
			} catch (SoapFault $f) {
				throw new SoapFault($f->faultcode, $f->faultstring);
			}
		} else {
			return $cache['sg_categories'];
		}
	}
	
	// Returns keywords for a particular group
	function sgGetGroupKeywords($groupid) {
		try {
			$GroupKeys = array(
				'token'		=>	$this->_getLoginToken(),
				'groupid'	=>	$groupid,
			);
			
			$xml = ee()->access_client->sgGetGroupKeywords($GroupKeys);
			$xmlResponse = $xml->sgGetGroupKeywordsResult->any;

			// Master array of keys to map
			$m = array('GroupID','KeyID','Kkeyword');
						
			$array = $this->_xml_parser($xmlResponse, $m);
			return $array;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	// Returns a master list of all keywords.
	function sgGetMasterKeywordList() {
		try {
			$token = array(
				'token'			=>	$this->_getLoginToken(),
			);
			
			$xml = ee()->access_client->sgGetMasterKeywordList($token);
			$xmlResponse = $xml->sgGetMasterKeywordListResult->any;

			// Master array of keys to map
			$m = array('KeyID','Keyword');
			
			$array = $this->_xml_parser($xmlResponse, $m);
			return $array;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	// Get meeting information about a specific group,  Fields returned include: groupid, parentid, sgname, hasroster, lastupdated, treepos, groupid1, description, location, locdirections, closedate, groupstatus, childcare, topic, datecreated, dateupdated, address1, address2, city, state, zipcode, cat1, webpage, publicgroup, startdate, enddate, emailparent, remindfreq, remindmessage, markingnotification, meetingday, meetingfreq, starttime, endtime.  Note that starttime and endtime contain only the AM/PM hours not the correct date/month.  For dates reference startdate and enddate.
	function sgGetMeetingInfo($groupid) {
		try {
			$mtgInfo = array(
				'token'			=>	$this->_getLoginToken(),
				'groupid'		=>	$groupid,
			);
			
			$xml = ee()->access_client->sgGetMeetingInfo($mtgInfo);
			$xmlResponse = $xml->sgGetMeetingInfoResult->any;
						
			// Master array of keys to map
			$m = array('GroupID','ParentID','SGName','HasRoster','Description','Location','LocDirections','CloseDate','GroupStatus','ChildCare','Topic','Address1','Address2','City','State','Zipcode','Cat1','Webpage','PublicGroup','StartDate','EndDate','MeetingDay','MeetingFreq','StartTime','EndTime');
			$array = $this->_xml_parser($xmlResponse);
			$array = $this->_timestamp($array);

			return $array;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	// Return an array of meeting times for a small group. Fields returned include: groupid, meetingday, meetingfreq, starttime, endtime.  Note that starttime and endtime are the AM/PM time and the date/month is not correct.
	function sgGetMeetingTimes($groupid) {
		try {
			$mtgTime = array(
				'token'			=>	$this->_getLoginToken(),
				'groupid'		=>	$groupid,
			);
			
			$xml = ee()->access_client->sgGetMeetingTimes($mtgTime);
			$xmlResponse = $xml->sgGetMeetingTimesResult->any;

			// Master array of keys to map
			$m = array('GroupID','MeetingDay','MeetingFreq','StartTime','EndTime');
						
			$array = $this->_xml_parser($xmlResponse, $m);
			$array = $this->_timestamp($array);

			return $array;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}

	// ------------------------------------------------------------------------
	// General ACS Volunteer Calls
	// ------------------------------------------------------------------------

	// Returns category names and ids for volunteer categories.  Fields returned include: catid, catname.
	function vmGetCatNames() {
	
		ee()->load->library('file_cache');
		
		$vm_query = ee()->db->query('SELECT serv_cache FROM exp_acs_settings');
		$serv_cache = (int) $vm_query->row('serv_cache');
		
		$cache = ee()->file_cache->getCache('acs_bridge', 'vm_categories', $serv_cache);

		if($cache === FALSE) {
			try {
		 		$token = array(
		 			'token'			=>	$this->_getLoginToken(),
		 		);
		 	
		 		$xml = ee()->access_client->vmGetCategoryNames($token);
				$xmlResponse = $xml->vmGetCategoryNamesResult->any;

				// Master array of keys to map
				$m = array('catid','catname');
				
				$array = $this->_xml_parser($xmlResponse, $m);
				ee()->file_cache->saveCache('acs_bridge', 'vm_categories', $array);
				return $array;
			} catch (SoapFault $f) {
				throw new SoapFault($f->faultcode, $f->faultstring);
			}
		} else {
			return $cache['vm_categories'];
		}
	}
	
	// Returns a list of serving attributes. Fields returned include: attqid, attqcatid, attqname, attqdescription, attqactive.
	function vmGetCatNameAttr($catid) {
	
		ee()->load->library('file_cache');
		
		$vm_query = ee()->db->query('SELECT serv_cache FROM exp_acs_settings');
		$serv_cache = (int) $vm_query->row('serv_cache');
		
		$cache = ee()->file_cache->getCache('acs_bridge', 'vm_cat_attr', $serv_cache);

		if($cache === FALSE) {
			try {
				$catAttr = array(
					'token'				=>	$this->_getLoginToken(),
					'catid'				=>	$catid,
				);
			
				$xml = ee()->access_client->vmGetCategoryNameAttributes($catAttr);
				$xmlResponse = $xml->vmGetCategoryNameAttributesResult->any;

				// Master array of keys to map
				$m = array('attqid','attqcatid','attqname','attqdescription','attqactive');
						
				$array = $this->_xml_parser($xmlResponse, $m);
				ee()->file_cache->saveCache('acs_bridge', 'vm_cat_attr', $array);
				return $array;
			} catch (SoapFault $f) {
				throw new SoapFault($f->faultcode, $f->faultstring);
			}
		} else {
			return $cache['vm_cat_attr'];
		}
	}
	
	// Return an array of all attributes for a given position. Fields returned include: attqid, attqcatid, attqname, attqdescription, attqactive.
	function vmGetPositionAttr($positionid) {
	
		ee()->load->library('file_cache');
		
		$vm_query = ee()->db->query('SELECT serv_cache FROM exp_acs_settings');
		$serv_cache = (int) $vm_query->row('serv_cache');
		
		$cache = ee()->file_cache->getCache('acs_bridge', 'vm_pos_attr', $serv_cache);

		if($cache === FALSE) {
			try {
				$posAttr = array(
					'token'			=>	$this->_getLoginToken(),
					'positionid'	=>	$positionid,
				);
			
				$xml = ee()->access_client->vmGetAttributesforPosition($posAttr);
				$xmlResponse = $xml->vmGetAttributesforPositionResult->any;

				// Master array of keys to map
				$m = array('attqid','attqcatid','attqname','attqdescription','attqactive');
				
				$array = $this->_xml_parser($xmlResponse, $m);
				ee()->file_cache->saveCache('acs_bridge', 'vm_pos_attr', $array);
				return $array;
			} catch (SoapFault $f) {
				throw new SoapFault($f->faultcode, $f->faultstring);
			}
		} else {
			return $cache['vm_pos_attr'];
		}
	}

	// Returns all service opportunities.  Fieldlist and oppids passed in must be an array.  Returned fields include: needid, needname, needdesc, addr1, addr2, city, state, zipcode, contactindividual, closingdate, published, contactmethod, groupid, childcare, reminderfreq, remindermessage. Passing in * for fieldlist will get all fields and passing -1 for oppids will get all opportunities.
	function vmGetServeOpportunities($fieldlist, $oppids) {
		try {
			$serveOpps = array(
				'token'				=>	$this->_getLoginToken(),
				'fieldlist'			=>	$fieldlist,
				'opportunityids'	=>	$oppids,
			);
			
			$xml = ee()->access_client->vmGetServOpportunities($serveOpps);
			$xmlResponse = $xml->vmGetServOpportunitiesResult->any;

			// Master array of keys to map
			$m = array('needid','needname','needdesc','addr1','addr2','city','state','zipcode','contactindividual','closingdate','published','contactmethod','groupid','childcare');			
			$array = $this->_xml_parser($xmlResponse, $m);
			
			// Date formatting
			foreach($array as &$e) {
				if(isset($e['closingdate'])) {
					$e['closingdate'] = strtotime(substr($e['closingdate'], 0, -6).$this->zone_offset);
				}
			}
			
			uasort($array, array($this,'_sort_serve'));
			$array = array_values($array);

			return $array;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	// Get a list of all positions. Returned fields include: positionid, positionname, description, active(BOOL), leadership(BOOL), smallgroup(BOOL), volneeds(BOOL).
	function vmGetPositions() {
	
		ee()->load->library('file_cache');
		
		$vm_query = ee()->db->query('SELECT serv_cache FROM exp_acs_settings');
		$serv_cache = (int) $vm_query->row('serv_cache');
		
		$cache = ee()->file_cache->getCache('acs_bridge', 'vm_positions', $serv_cache);

		if($cache === FALSE) {
			try {
				$token = array(
					'token'		=>	$this->_getLoginToken(),
				);
			
				$xml = ee()->access_client->vmGetPositions($token);
				$xmlResponse = $xml->vmGetPositionsResult->any;

				// Master array of keys to map
				$m = array('positionid','positionname','description','active','leadership','smallgroup','volneeds');
				
				$array = $this->_xml_parser($xmlResponse, $m);
				ee()->file_cache->saveCache('acs_bridge', 'vm_positions', $array);
				return $array;
			} catch (SoapFault $f) {
				throw new SoapFault($f->faultcode, $f->faultstring);
			}
		} else {
			return $cache['vm_positions'];
		}
	}
	
	// Returns a position opportunity list.  Returned fields include: positionid, positionname, description, active, leadership, smallgroup, volneeds, positionneedid, needid, positionid1, numberneeded, urgent, sampling, recurring, startdate, enddate, timescheduled, starttime, endtime, frequency, sun, mon, tue, wed, thu, fri, sat, onedaypermonth, dayofmonth, weekofmonth, weekdayofmonth, featured, alternatename, continuous. Passing in -1 for both positionid and oppid will return all position/opportunity pairs.
	function vmGetPositionOpportunities($positionid, $oppid) {
		try {
			$posOpps = array(
				'token'			=>	$this->_getLoginToken(),
				'positionid'	=>	$positionid,
				'opportunityid'	=>	$oppid,
			);
			
			$xml = ee()->access_client->vmGetPositionOpportunities($posOpps);
			$xmlResponse = $xml->vmGetPositionOpportunitiesResult->any;

			// Master array of keys to map
			$m = array('positionid','positionname','description','active','leadership','smallgroup','volneeds','positionneedid','needid','positionid1','needid','positionid1','numberneeded','urgent','recurring','startdate','enddate','timescheduled','starttime','endtime','frequency','sun','mon','tue','wed','thu','fri','sat','onedaypermonth','dayofmonth','weekofmonth','weekdayofmonth','featured','alternatename','continuous');
						
			$array = $this->_xml_parser($xmlResponse, $m);
			$array = $this->_timestamp($array);

			return $array;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}

// ------------------------------------------------------------------------
// Access ACS Event Calls (requires Facility Scheduler)
// ------------------------------------------------------------------------
	
	// Returns event details.
	function getEventDetail($eventid) {
	
		try {
			$eventDetailArray = array(
				'token'		=>	$this->_getLoginToken(),
				'eventId'	=>	$eventid,
			);
		
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->event_client->getEventDetail($eventDetailArray);
			$xmlResponse = $xml->getEventDetailResult->any;

			// Master array of keys to map
			$m = array('eventid','parentid','eventypeid','calendarid','eventname','description','status','contactemail','recurrencetype','ispublished','allowregistration','startdate','stopdate');
						
			// Convert the XML to an array for easier management and caching
			$getDetail = $this->_xml_parser($xmlResponse, $m);
			$getDetail = $this->_timestamp($getDetail);

			uasort($getDetail, array($this,'_sort'));
			$getDetail = array_values($getDetail);		

			return $getDetail;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	// Returns a list of all events in a given date range.  Data includes eventId, Description, EventTypeId, EventName, note, locationid, startdate, stopdate, isPublished(BOOL), and allowregistration(BOOL).
	function getEventsByDate($start, $stop) {
		
		try {	
			$eventArray = array(
					'token'			=>	$this->_getLoginToken(),
					'startdate'		=>	date('c', $start),
					'stopdate'		=>	date('c', $stop),
				);
			
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->event_client->getEventsByDateRange($eventArray);
			$xmlResponse = $xml->getEventsByDateRangeResult->any;

			// Master array of keys to map
			$m = array('eventid','description','eventtypeid','eventname','note','locationid','startdate','stopdate','ispublished','allowregistration');
			
			// Convert the XML to an array for easier management and caching
			$eventsByDate = $this->_xml_parser($xmlResponse, $m);
			$eventsByDate = $this->_timestamp($eventsByDate);
			
			uasort($eventsByDate, array($this,'_sort'));
			$eventsByDate = array_values($eventsByDate);																			
		
			return $eventsByDate;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	/* Get Event Image
	 *
	 * Returns the image for the event
	 *
	 * @params int
	 *
	 * @type GET
	 * @return xml
	**/
	function getEventImage($eventId) {
		
		try {
			$eventImage = array(
				'token'		=> $this->_getLoginToken(),
				'eventId'	=> $eventId,
			);
			
			//Fetches and stores the XML/SOAP response
			$xml = ee()->event_client->getEventImage($eventImage);
			$xmlResponse = $xml->getEventImageResult->any;
			
			// Convert to an array
			$eventImage = $this->_xml_parser($xmlResponse);
			
			if (!empty($eventImage)) {
				return $eventImage[0];
			} else {
				return;
			}			
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	// Returns a list of tags for a specific event.  Data includes the TagId, TagName, IsActive, TagLinkId, TagId1, EventId, CalendarId, EventName, Description, Status, IsPublished, AllowRegistration, LocationId, and more.
	function getTagsbyEventId($eventid) {
		
		try {
			$TagbyEventArray = array(
				'token'		=>	$this->_getLoginToken(),
				'eventid'	=>	$eventid,
			);
		
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->event_client->getTagsbyEventId($TagbyEventArray);
			$xmlResponse = $xml->getTagsbyEventIDResult->any;

			// Master array of keys to map
			$m = array('tagid','tagname','isactive','taglinkid','tagid1','eventid','parentid','calendarid','eventname','description','status','recurrencetype','ispublished','allowregistration','locationid');
			
			// Convert the XML to an array for easier management and caching
			$eventTags = $this->_xml_parser($xmlResponse, $m);
									
			return $eventTags;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	// Returns a list of all tags.  Includes TagId, TagName, IsActive(BOOL).
	function getTagsList() {
		
		ee()->load->library('file_cache');
		
		$cache = ee()->file_cache->getCache('acs_bridge', 'tags_list', 2592000);
		
		if ($cache === FALSE) {
			try {
				$TagsListArray = array(
					'token'		=> $this->_getLoginToken(),
				);
			
				// Fetches and stores the XML/SOAP response
				$xml = ee()->event_client->getTagsList($TagsListArray);
				$xmlResponse = $xml->getTagsListResult->any;

				// Master array of keys to map
				$m = array('tagid','tagname','isactive');
				
				// Convert the XML to an array for easier management and caching
				$tagList = $this->_xml_parser($xmlResponse, $m);
			
				// Cache the array
				ee()->file_cache->saveCache('acs_bridge', 'tags_list', $tagList);
			
				return $tagList;
			} catch (SoapFault $f) {
				throw new SoapFault($f->faultcode, $f->faultstring);
			}
		} else {
			return $cache['tags_list'];
		}
	}
	
	// Get a list of locations.  The response includes resourcecategoryid, resourceid, resourcecategoryname, description, description1, resourcename, quantity, and occupancy
	function getLocations() {
		ee()->load->library('file_cache');

		$l_query = ee()->db->query('SELECT location_cache FROM exp_acs_settings');
		$loc_cache = (int) $l_query->row('location_cache');
		
		$cache = ee()->file_cache->getCache('acs_bridge', 'locations', $loc_cache);
		
		if ($cache === FALSE) {
			try {
				$LocationArray = array(
					'token'		=> $this->_getLoginToken(),
					'typeID'	=> 2,
				);
			
				// Fetches and stores the XML/SOAP response
				$xml = ee()->event_client->getResourcesByType($LocationArray);
				$xmlResponse = $xml->getResourcesByTypeResult->any;
				// Master array of keys to map
				$m = array('resourcecategoryid','resourceid','resourcecategoryname','description','description1','resourcename','quantity','occupancy');
				// Convert the XML to an array for easier management and caching
				$locations = $this->_xml_parser($xmlResponse, $m);
			
				// Cache the array
				ee()->file_cache->saveCache('acs_bridge', 'locations', $locations);
			
				return $locations;
			} catch (SoapFault $f) {
				throw new SoapFault($f->faultcode, $f->faultstring);
			}
		} else {
			return $cache['locations'];
		}
	}
	
	/**
	 * Get Event Custom Field
	 *
	 * Returns event custom fields.
	 *
	 * @params	int
	 *
	 * @type	GET
	 * @return	xml
	**/
	function getEventCustomField($eventId) {
		
		try {
			$eventCustomField = array(
				'token'		=>	$this->_getLoginToken(),
				'eventId'	=>	$eventId,
			);
		
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->event_client->getEventCustomField($eventCustomField);
			$xmlResponse = $xml->getEventCustomFieldResult->any;

			// Master array of keys to map
			$m = array('eventid','parentid','eventypeid','calendarid','eventname','description','status','contactemail','recurrencetype','ispublished','allowregistration','startdate','stopdate');
						
			// Convert the XML to an array for easier management and caching
			$eventCustomField = $this->_xml_parser($xmlResponse, $m);
			$eventCustomField = $this->_timestamp($eventCustomField);

			//uasort($eventCustomField, array($this,'_sort'));
			//$eventCustomField = array_values($eventCustomField);		

			return $eventCustomField;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	/**
	 * Get Event Media Names
	 *
	 * Returns event media names.
	 *
	 * @params	int
	 *
	 * @type	GET
	 * @return	xml
	**/
	function getEventMediaNames($eventId) {
			
		try {
			$eventMediaNames = array(
				'token'		=>	$this->_getLoginToken(),
				'eventId'	=>	$eventId,
			);
		
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->event_client->getEventMediaNames($eventMediaNames);
			$xmlResponse = $xml->getEventMediaNames->any;

			// Master array of keys to map
			$m = array('eventid','parentid','eventypeid','calendarid','eventname','description','status','contactemail','recurrencetype','ispublished','allowregistration','startdate','stopdate');
						
			// Convert the XML to an array for easier management and caching
			$eventMediaNames = $this->_xml_parser($xmlResponse, $m);
			$eventMediaNames = $this->_timestamp($eventMediaNames);

			//uasort($eventCustomField, array($this,'_sort'));
			//$eventCustomField = array_values($eventCustomField);		

			return $eventMediaNames;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
	/**
	 * Get Event Notes Description
	 *
	 * Returns event notes description.
	 *
	 * @params	int
	 *
	 * @type	GET
	 * @return	xml
	**/
	function getEventNotesDescription($eventId) {
				
		try {
			$eventNotesDesc = array(
				'token'		=>	$this->_getLoginToken(),
				'eventId'	=>	$eventId,
			);
		
			// Fetches and stores the XML/SOAP response for use
			$xml = ee()->event_client->getEventNotesDescription($eventNotesDesc);
			$xmlResponse = $xml->getEventNotesDescription->any;

			// Master array of keys to map
			$m = array('eventid','parentid','eventypeid','calendarid','eventname','description','status','contactemail','recurrencetype','ispublished','allowregistration','startdate','stopdate');
						
			// Convert the XML to an array for easier management and caching
			$eventNotesDesc = $this->_xml_parser($xmlResponse, $m);
			$eventNotesDesc = $this->_timestamp($eventNotesDesc);

			//uasort($eventCustomField, array($this,'_sort'));
			//$eventCustomField = array_values($eventCustomField);		

			return $eventNotesDesc;
		} catch (SoapFault $f) {
			throw new SoapFault($f->faultcode, $f->faultstring);
		}
	}
	
// ------------------------------------------------------------------------
// Access ACS XML Parse Help
// ------------------------------------------------------------------------	
	
	// Built to convert all time related string data to a timestamp
	function _timestamp($data) {
		foreach ($data as &$e) {
			if(isset($e['StartDate'])) {						
				$e['StartDate'] = strtotime(substr($e['StartDate'], 0, -6).$this->zone_offset);				
				if(isset($e['StopDate'])) {
					$e['StopDate'] = strtotime(substr($e['StopDate'], 0, -6).$this->zone_offset);
				}
				if(isset($e['EndDate'])) {
					$e['EndDate'] = strtotime(substr($e['EndDate'], 0, -6).$this->zone_offset);
				}
			}
			if(isset($e['StartTime'])) {
				$e['StartTime'] = str_replace('1900-01-01', '1971-01-01', $e['StartTime']);
				
				$e['StartTime'] = strtotime(substr($e['StartTime'], 0, -6).$this->zone_offset);
				if(isset($e['EndTime'])) {
					$e['EndTime'] = str_replace('1900-01-01', '1971-01-01', $e['EndTime']);
					$e['EndTime'] = strtotime(substr($e['EndTime'], 0, -6).$this->zone_offset);
				}
			}
			if(!empty($e['CloseDate'])) {
				$e['CloseDate'] = strtotime(substr($e['CloseDate'], 0, -6).$this->zone_offset);
			}
			if(isset($e['DateModified'])) {
				$e['DateModified'] = strtotime(substr($e['DateModified'], 0, -6).$this->zone_offset);
			}
			if(isset($e['DateCreated'])) {
				$e['DateCreated'] = strtotime(substr($e['DateCreated'], 0, -6).$this->zone_offset);
			}
			if(isset($e['DateUpdated'])) {
				$e['DateUpdated'] = strtotime(substr($e['DateUpdated'], 0, -6).$this->zone_offset);
			}
		}
		return $data;
	}
			
	// Sorts data by date ascending.
	function _sort($obj_1, $obj_2) {
		if ($obj_1['StartDate'] == $obj_2['StartDate']) {
			return 0;
		}
		return ($obj_1['StartDate'] < $obj_2['StartDate']) ? -1 : 1;
	}
	
	// Sorts data by date ascending.
	function _sort_serve($obj_1, $obj_2) {
		if ($obj_1['ClosingDate'] == $obj_2['ClosingDate']) {
			return 0;
		}
		return ($obj_1['ClosingDate'] < $obj_2['ClosingDate']) ? -1 : 1;
	}
	

	// Parses the XML response into an array for use in the Template Class
	function _xml_parser($xml, $map = NULL) {
		$xmlObject = simplexml_load_string($xml);		
		$xmlPiece = $xmlObject->xpath('NewDataSet/dbs');

		$data = array();
		
		// Build an array from the XML data
		foreach ($xmlPiece as $item) {
		
			if ($map != NULL) {
				$tmp = array_fill_keys($map, '');
			} else {
				$tmp = array();
			}
			
			foreach ($item as $val) {
				$tmp[$val->getName()] = trim($val);
			}
			
			$data[] = $tmp;
		}

		return $data;
	}

}
