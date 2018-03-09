<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include 'config.php';
/**
 * Lamplighter Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Masuga Design
 * @link
 */

class Lamplighter_upd {

	public $version = LAMPLIGHTER_VERSION;

	private $EE;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}

	// ----------------------------------------------------------------

	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{
		// Load dbforge
		$this->EE->load->dbforge();

		$licensing = array(
			'id' 		=> array('type' => 'INT', 'unsigned' => TRUE,	'auto_increment' => TRUE),
			'key' 		=> array('type' => 'VARCHAR', 'constraint' => 255),
			'site_id'	=> array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),

		);
		$this->EE->dbforge->add_field($licensing);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('lamplighter_license', TRUE);

		$mod_data = array(
			'module_name'			=> 'Lamplighter',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> 'y',
			'has_publish_fields'	=> 'n'
		);
		$this->EE->db->insert('modules', $mod_data);

		$data = array(
		    'class'     => 'Lamplighter',
		    'method'    => 'api_request'
		);
		$this->EE->db->insert('actions', $data);

		return TRUE;
	}

	// ----------------------------------------------------------------

	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */
	public function uninstall()
	{
		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('lamplighter_license');

		$mod_id = $this->EE->db->select('module_id')
								->get_where('modules', array(
									'module_name'	=> 'Lamplighter'
								))->row('module_id');

		$this->EE->db->where('module_id', $mod_id)
					 ->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Lamplighter')
					 ->delete('modules');

		$this->EE->db->where('class', 'Lamplighter')
					 ->delete('actions');

		return TRUE;
	}

	// ----------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */
	public function update($current = '')
	{
		return TRUE;
	}

}