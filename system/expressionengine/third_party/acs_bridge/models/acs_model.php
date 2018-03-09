<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ACS Bridge
 *
 * @package		ACS Bridge
 * @author		Ron Hickson
 * @copyright	Copyright (c) 2013
 * @link		http://hicksondesign.com
 * @since		Version 1.7
 */

// ------------------------------------------------------------------------

class Acs_model {

	// REST Authorization getter
	function getAuth() {
	
		ee()->load->library('encrypt');
		
		ee()->db->select('username, password, site_number');
		$query = ee()->db->get('acs_settings');
		
		if ($query->num_rows() > 0) {
			$auth = $query->row_array();
			
			$auth['password'] = ee()->encrypt->decode($auth['password']);
			return $auth;
		}
	}
}

/* End of file acs_model.php */
/* Location: ./system/expressionengine/third_party/acs_bridge/models/acs_model.php */