<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Acs_bridge_upd {

	var $version = '1.7';
	
	function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */

	function install()
	{
		ee()->load->dbforge();
		$mod_data=array(
			'module_name' => 'Acs_bridge',
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);
		
		ee()->db->insert('modules', $mod_data);
		
		// Create table for settings
		$settings = array(
			'id'				=>	array('type'=>'INT','constrain'=>'2','unsigned'=>TRUE,'auto_increment'=>TRUE),
			'secid'				=>	array('type'=>'VARCHAR','constraint'=>'50'),
			'site_number'		=>	array('type'=>'INT','constrain'=>'6'),
			'dst'				=>	array('type'=>'TINYINT','constraint'=>'1'),
			'calendar_cache'	=>	array('type'=>'INT','constrain'=>'10'),
			'location_cache'	=>	array('type'=>'INT','constrain'=>'10'),
			'tag_cache'			=>	array('type'=>'INT','constrain'=>'10'),
			'serv_cache'		=>	array('type'=>'INT','constrain'=>'10'),
			// Added for REST implementation
			'username'			=>	array('type'=>'VARCHAR','constraint'=>'50'),
			'password'			=>	array('type'=>'VARCHAR','constraint'=>'250'),
			);
		
		ee()->dbforge->add_field($settings);
		ee()->dbforge->add_key('id', TRUE);
		ee()->dbforge->create_table('acs_settings');
		
		$event_list = array(
				'class'		=> 'Acs_bridge',
				'method'	=> 'events_list'
				);
		
		ee()->db->insert('actions', $event_list);
		
		$event = array(
				'class'		=> 'Acs_bridge',
				'method'	=> 'event'
				);
		
		ee()->db->insert('actions', $event);
		
		return TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	 
	function uninstall()
	{		
		ee()->load->dbforge();

		ee()->db->select('module_id');
		$query = ee()->db->get_where('modules', array('module_name' => 'Acs_bridge'));

		ee()->db->where('module_id', $query->row('module_id'));
		ee()->db->delete('module_member_groups');

		ee()->db->where('module_name', 'Acs_bridge');
		ee()->db->delete('modules');
		
		ee()->dbforge->drop_table('acs_settings');;

		ee()->db->where('class', 'Acs_bridge');
		ee()->db->delete('actions');
		
		return TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */	
	 
	function update($current='')
	{
		return FALSE;
	}
	
}

/* End of file upd.acs_events.php */
/* Location: ./system/expressionengine/third_party/acs_events/upd.acs_events.php */