<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * BugHerd Sidebar installation
 *
 * @package			Bugherd
 * @version			1.0
 * @author			Tommy-Carlos Williams <http://bugherd.com> - Senior Software Engineer - BugHerd
 * @copyright 		Copyright (c) 2012 BugHerd <http://bugherd.com>
 * @license 		MIT
 * @link			http://bugherd.com
 */
class Bugherd_ext {

	var $name       	= 'BugHerd';
    var $version        = '1.0';
    var $description    = 'Installs BugHerd Sidebar';
    var $settings_exist = 'y';
    var $docs_url       = 'http://docs.bugherd.com/';

    var $settings = array();

    /**
     * Constructor
     *
     * @param   mixed   Settings array or empty string if none exist.
     */
    function __construct($settings='')
    {
        $this->EE =& get_instance();

        $this->EE->lang->loadfile('bugherd');

        $this->name = lang('name');
        $this->description = lang('description');

        $this->settings = $settings;
    }
    

    /**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	function activate_extension()
	{
	    $this->settings = array(
	        'api_key'   => ''
	    );


	    $data = array(
			'class'		=> __CLASS__,
			'method'	=> 'add_bugherd',
			'hook'		=> 'template_post_parse',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

	    $this->EE->db->insert('extensions', $data);
	}

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return  mixed   void on update / false if none
	 */
	function update_extension($current = '')
	{
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }

	    if ($current < '1.0')
	    {
	        // Update to version 1.0
	    }

	    $this->EE->db->where('class', __CLASS__);
	    $this->EE->db->update(
	                'extensions',
	                array('version' => $this->version)
	    );
	}

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
	    $this->EE->db->where('class', __CLASS__);
	    $this->EE->db->delete('extensions');
	}

	// --------------------------------
	//  Settings
	// --------------------------------

	function settings()
	{
	    $settings = array();

	    // Creates a text input with a default value
	    $settings['api_key'] = array('i', '', 'your api key here');

	    return $settings;
	}

	/**
	 * Settings Form
	 *
	 * @param   Array   Settings
	 * @return  void
	 */
	function settings_form($current)
	{
	    $this->EE->load->helper('form');
	    $this->EE->load->library('table');

	    $vars = array();

	    $api_key = isset($current['api_key']) ? $current['api_key'] : "";

	    $vars['settings'] = array(
	        'api_key'   => form_input('api_key', $api_key)
	    );

	    return $this->EE->load->view('index', $vars, TRUE);
	}

	/**
	 * Save Settings
	 *
	 * This function provides a little extra processing and validation
	 * than the generic settings form.
	 *
	 * @return void
	 */
	function save_settings()
	{
	    if (empty($_POST))
	    {
	        show_error($this->EE->lang->line('unauthorized_access'));
	    }

	    unset($_POST['submit']);

	    $this->EE->lang->loadfile('bugherd');

	    $len = $this->EE->input->post('api_key');

	    $this->EE->db->where('class', __CLASS__);
	    $this->EE->db->update('extensions', array('settings' => serialize($_POST)));

	    $this->EE->session->set_flashdata(
	        'message_success',
	        $this->EE->lang->line('preferences_updated')
	    );
	}

	/**
	 * clean_template
	 *
	 * @param 
	 * @return 
	 */
	public function add_bugherd($template, $sub,	$site_id)
	{
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$template = $this->EE->extensions->last_call;
		}



		// //only run on the final template
		if ( ! $sub )
		{
			//grab the cached settings if they're not already there for some reason
			if (empty($this->settings))
			{
				$this->settings = $this->EE->extensions->s_cache[__CLASS__];
			}

			$template = $this->EE->TMPL->parse_globals($template);

			$api_key = $this->settings['api_key'];

			if (!empty($api_key)) {
				$javascript = "
<!-- Add BugHerd Sidebar -->
<script type='text/javascript'>
  (function (d, t) {
    var bh = d.createElement(t), s = d.getElementsByTagName(t)[0];
    bh.type = 'text/javascript';
    bh.src = '//www.bugherd.com/sidebarv2.js?apikey=".$api_key."';
    s.parentNode.insertBefore(bh, s);
  })(document, 'script');
</script>

</head>";
				$template = str_ireplace("</head>", $javascript, $template);
			}
		}

	 	return $template;
	}
}
// END CLASS
/* End of file ext.bugherd.php */
/* Location: ./system/expressionengine/third_party/bugherd/ext.bugherd.php */