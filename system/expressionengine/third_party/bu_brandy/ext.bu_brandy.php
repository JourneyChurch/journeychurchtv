<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
=============================================================
 	Developed by Aidann Bowley, bridgingunit.com
	Copyright (c) 2012
=============================================================
	File:					ext.bu_brandy.php
-------------------------------------------------------------
	Compatibility (tested):	EE 2.5.2, 2.4.0, MSM 2.1.3 
	Requires:				EE > 2.1.2, PHP > 5.2.0
-------------------------------------------------------------
	Purpose:				Add logos to control panel for branding in sidebar and/or footer.
							Allow HTML to be added to sidebar and/or footer.
							MSM compatible.
						
-------------------------------------------------------------
	Thanks:					To Ben at Versa Studio (versastudio.com) for commissioning and sponsoring this addon.
	
	Inspiration:			Versa Studio's CP Logo Accessory, Pixel and Tonic's CP CSS JS extension, Leevi Graham's LG Add Site Name.
	
	To do: 					Get tabbing working properly in fields.
							Add logos to login page.
							
=============================================================
	Version History:
	
	0.9.0 - 13-19 April 2012 	Extension first built.
	0.9.1 - 09 July 2012		Added ability to turn off XSS filtering via custom config variable.
	0.9.2 - 24 August 2012		Fixed up MSM side so site-specific settings worked independently.
	0.9.3 - 05 September 2012	Added back BU prefixes to things.
	0.9.4 - 01 October 2012		Fix occasional array_combine bug in PHP < 5.4.0.
	0.9.5 - 02 July 2013		Added CSS to demonstrate basic colour coding of MSM sites. Cleared out some empty system files.
	
=============================================================
 */
class Bu_brandy_ext {

    var $name            = 'BU Brandy';
    var $version         = '0.9.5';
    var $description     = 'Add logos to control panel for branding etc.';
    var $settings_exist  = 'y';
    var $docs_url        = 'http://www.bridgingunit.com/labs/expressionengine/bu-brandy/';

	var $url_name		 = 'bu_brandy';// just a ref to this filename.
    var $settings        = array();


    // -------------------------------
    //   Constructor - Extensions use this for settings.
    // -------------------------------
	function __construct($settings='')
    {
		// Local ref to ExpressionEngine super object.
        $this->EE =& get_instance();

		// Where things might be found....
		$this->theme_folder_path = PATH_THIRD_THEMES.'bu_brandy/';		
		$this->theme_folder_url = $this->EE->config->slash_item('theme_folder_url').'third_party/bu_brandy/';
		
		// MSM: which Site are we dealing with?
		$this->site_id = $this->EE->config->item('site_id');
		
		// Get existing settings.
		$this->EE->db->select('settings')
			->from('extensions')
			->where('class', __CLASS__)
			->limit(1);// NOTE: actually should be 2 rows in db as applying to 2 hooks, but settings for both should both be the same. TODO: make that more efficient.
			
		$query = $this->EE->db->get();
		
		// If we already have something set, let's use that.
		if ($query->num_rows() > 0)
		{
		    // Turn them into something a little more useful, and overwrite default supplied by $settings param.
			$settings = unserialize($query->row('settings'));
		}

		$this->settings = $settings;
		
		// Make our language file available to all. By default some methods (save_settings) seem to not have access.
		// Why do we need to do this? Values from it are already otherwise used automatically in our extension elsewhere....
		$this->EE->lang->loadfile('bu_brandy');
    }
	// END
	

    // -------------------------------
    //   Settings Form - sets up the form and pre-populates it with defaults, or submitted data.
    // -------------------------------	
	function settings_form($current)
	{
	    $this->EE->load->helper('form');
	    $this->EE->load->library('table');
		
		// MSM: get subset of settings for current site_id.
		$current_site = (isset($current[$this->site_id])) ? $current[$this->site_id] : $current;
		
		// Array to pass things through to our settings form.
		$vars = array();
		$vars['file'] = $this->url_name;
		
		// Need to use settings submitted, or provide some defaults.
		//---------------------

		// Logos 		
		$logos_available = $this->_get_files_by_directory($this->theme_folder_path.'logos/');
		
		// Fix for PHP bug version < 5.4.0, where array_combine can't handle empty arrays (hhttp://bugs.php.net/bug.php?id=34857)
		if($logos_available === array() )
		{
			$select_logos = array();
		}	
		else
		{
			$select_logos = array_combine($logos_available, $logos_available);
		}
		
		$select_opener = array('' => 'Please choose');
		// TODO: work out how to add ,'' => '--------------'. Problem being associative array can't have multiple blank keys.
		$select_logos = $select_opener + $select_logos;//array_merge($select_opener, $select_logos);
		//var_dump($select_logos);

		// Which logos have been selected already, if any.
		// If none, supply defaults.
		// for form dropdown selected state.
		$selected_sidebar_logo = isset($current_site['sidebar_logo']) ? $current_site['sidebar_logo'] : 'sidebar_logo.png';
		$selected_footer_logo = isset($current_site['footer_logo']) ? $current_site['footer_logo'] : 'footer_logo.png';

		// Textareas.

		// Default CSS to provide an example.
		// TODO: abstract this to view file or similar.
		$brandy_css = '/* Basic color coding. Hint: good to color code MSM sites. */
#activeUser
{
    background-color:#D91350;/* Native pink */
}
/* Add a bit of a gap */
#brandy_sidebar
{
    margin-top:10px;
}
/* Centre the logo in the sidebar */
#brandy_sidebar img.logo
{
    display:block;
    margin:0 auto;
}';
		
		// For form textareas.
		$val_sidebar_html = isset($current_site['sidebar_html']) ? $current_site['sidebar_html'] : '';
		$val_footer_html = isset($current_site['footer_html']) ? $current_site['footer_html'] : '';
		$val_brandy_css = isset($current_site['brandy_css']) ? $current_site['brandy_css'] : $brandy_css;
		
		// This is all very repetitive.... Must refactor to make it neater.
		$data_sidebar_html = array(
		              			'name'        => 'sidebar_html',
		              			'id'          => 'sidebar_html',
		              			'value'       => $val_sidebar_html,
		              			'rows'   	  => '10',
		            		);
		$data_footer_html = array(
		              			'name'        => 'footer_html',
		              			'id'          => 'footer_html',
		              			'value'       => $val_footer_html,
		              			'rows'   	  => '10',
		            		);	
		$data_brandy_css = array(
		              			'name'        => 'brandy_css',
		              			'id'          => 'brandy_css',
		              			'value'       => $val_brandy_css,
		              			'rows'   	  => '10',
		            		);
							
	 	$vars['settings'] = array(
			'sidebar_logo' 	=>  form_dropdown('sidebar_logo', $select_logos, $selected_sidebar_logo), 
			'sidebar_html' 	=> 	form_textarea($data_sidebar_html),
			'footer_logo' 	=>  form_dropdown('footer_logo', $select_logos, $selected_footer_logo), 
			'footer_html'  	=> 	form_textarea($data_footer_html),			
			'brandy_css' 	=> 	form_textarea($data_brandy_css),
		);
									
		// Additional Control Panel tweaking, following Brandon's lead.
		// CSS
		$css = 	'<style type="text/css">
		     		#sidebar_html, 
					#footer_html,
					#brandy_css 
					{ 
						font-family: monospace; 
					}
				</style>';

		$this->EE->cp->add_to_head($css);

		/* TODO: This doesn't work properly in practice, alas. Come back to it later.
		// JS - using 4 spaces instead of \t so not stripped by XSS filter.
		$js = 	'<script type="text/javascript">
					$("#sidebar_html,#footer_html,#brandy_css").keydown(function(event) 
					{
		    			if (event.keyCode == 9) {
		    				this.value += "    ";
		    				event.preventDefault();
		    			}
		    		});
		       	</script>';

		$this->EE->cp->add_to_foot($js);
		*/
					
		// Pull in our view file.
	    return $this->EE->load->view('settings', $vars, TRUE);
	}
	// END settings_form

    // -------------------------------
    //   Save Settings - validates submitted settings data and either flags errors or saves it.
    // -------------------------------	
	function save_settings()
	{
	    if (empty($_POST))
	    {
	        show_error($this->EE->lang->line('unauthorized_access'));
	    }
	
		// Why do we need to do this? Values from it are already used automatically in our extension elsewhere....
		//$this->EE->lang->loadfile('brandy');
		
		// Check optional config for XSS filtering.
		if($this->EE->config->item('bu_brandy_xss_filtering') AND $this->EE->config->item('bu_brandy_xss_filtering') === 'n')
		{
			// Turn filter off only if it's been requested by dev.
			$brandy_xss_filtering = FALSE;
		}
		else
		{
			// Default is always to filter.
			$brandy_xss_filtering = TRUE;
		}
		
		// Expected settings - limit to only those POST vars we expect.
		// Run through XSS filter too (if 2nd param of TRUE in $this->EE->input->post), 
		// this encodes script, object, iframe etc. so they can't run and are output instead.
		$expected_settings = array();
		$expected_settings['sidebar_logo'] = $this->EE->input->post('sidebar_logo', $brandy_xss_filtering); 
		$expected_settings['sidebar_html'] = $this->EE->input->post('sidebar_html', $brandy_xss_filtering);
		$expected_settings['footer_logo'] = $this->EE->input->post('footer_logo', $brandy_xss_filtering);
		$expected_settings['footer_html'] = $this->EE->input->post('footer_html', $brandy_xss_filtering);
		$expected_settings['brandy_css'] = $this->EE->input->post('brandy_css', $brandy_xss_filtering);
		
		/* NOTE: Commented out as no settings are 'required' at moment.
		// Perform validation on received settings here, if necessary.
		$errors = '';
		
		// Flag errors, if there any.
		if( !empty($errors) ){
			$this->EE->session->set_flashdata('message_failure', $errors);
			
			$this->EE->functions->redirect(
	            BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file='.$this->url_name
	        );
		}	
		*/
		
		// Let's use existing global settings.
		$settings = $this->settings;//array();
		
		// MSM: add our fresh settings to current site_id.
		$settings[$this->site_id] = $expected_settings;
		
		//var_dump($settings);
		//exit();
				
		// Otherwise update the settings accordingly.
	    $this->EE->db->where('class', __CLASS__);
	    $this->EE->db->update('extensions', array('settings' => serialize($settings)));

		// Show a success message when redirected to EE's settings page.
		// Shame this is so unobtrusive as to be easily overlooked.... oh well.
	    $this->EE->session->set_flashdata(
	        'message_success',
	        $this->EE->lang->line('success_preferences_updated')
	    );

		/* NOTE: commented out as we want to make clear that something has actually happened.
		// Stay on existing settings page instead, thank you.
		$this->EE->functions->redirect(
	        BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file='.$this->url_name
	    );
		*/
	}
	// END save_settings
	



/*	NOTE: commented out as need MSM, so have to use save_settings and settings_form instead. Would work for non-MSM EE.	
	// --------------------------------
	//  Settings
	// --------------------------------	
	function settings()
	{
		
		$logos_available = $this->_get_files_by_directory($this->theme_folder_path.'logos/');
		
		$select_logos = array_combine($logos_available, $logos_available);
		$select_opener = array('' => 'Please choose');
		$select_logos = $select_opener + $select_logos;
		
		// TODO: abstract this to view file.
		$brandy_css = '#brandy_sidebar
{
	margin-top:10px;
}
// Centre the logo in the sidebar.
#brandy_sidebar img.logo
{
	display:block;
	margin:0 auto;
}';
		
	 	$settings = array(
			'sidebar_logo' =>  array('s', $select_logos, 'sidebar_logo.png'),
			'sidebar_html' => array('t', array('rows' => '10'), ''),
			'footer_logo' =>  array('s', $select_logos, 'footer_logo.png'),
			'footer_html'  => array('t', array('rows' => '10'), ''),			
			'brandy_css' => array('t', array('rows' => '10'), $brandy_css)
		);

		// CSS
		$css = 	'<style type="text/css">
		     		#sidebar_html, 
					#footer_html,
					#brandy_css 
					{ 
						font-family: monospace; 
					}
				</style>';

		$this->EE->cp->add_to_head($css);

		// JS
		$js = 	'<script type="text/javascript">
					$("#sidebar_html,#footer_html,#brandy_css").keydown(function(event) 
					{
		    			if (event.keyCode == 9) {
		    				this.value += "\t";
		    				event.preventDefault();
		    			}
		    		});
		       	</script>';

		$this->EE->cp->add_to_foot($js);

		return $settings;
	}
	// END settings
*/
	
	// --------------------------------
	//  Update Extension
	// -------------------------------- 
	function update_extension($current='')
	{    
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }
	    
	    return FALSE;
	    // update if different version.
	}
	// END update_extension
	
	
			
	// --------------------------------
	//  Disable Extension
	// --------------------------------	
	function disable_extension()
	{		
		// Remove hooks.
		$this->EE->db->delete('extensions', array('class'=>get_class($this)));			
	}
	// END disable_extension	



    // --------------------------------
	//  Activate Extension
	// --------------------------------	
	function activate_extension()
	{	
		// -------------------------------------------------
		// Do some version checking first.
		// -------------------------------------------------
		// Check minimum criteria for this extension to work (EE 2.1.2, when hooks required introduced, PHP 5.2.0 when json_encode function added).
		$ee_version = explode('.', APP_VER);
		if($ee_version[0] < 2 OR ($ee_version[0] >= 2 AND $ee_version[1] < 1) OR ($ee_version[0] >= 2 AND $ee_version[1] == 1 AND $ee_version[2] < 2 ) )
		{
			$this->EE->output->show_user_error('general' , $this->EE->lang->line('error_ee_version'));
		}
		if(version_compare(PHP_VERSION, '5.2.0', '<') )
		{
			$this->EE->output->show_user_error('general' , $this->EE->lang->line('error_php_version'));
		}
			
		// -------------------------------------------------
		// Register hooks used by this extension. 
		// -------------------------------------------------		 
		$register_hooks = array(			
			'cp_js_end' => 'custom_js',
			'cp_css_end' => 'custom_css'				
		);
		
		$class_name = get_class($this);
		
		$this->settings = array();// We'd add defaults here if we wanted them applied on activation. Here we don't as user needs to supply or save first.
		
		foreach($register_hooks as $hook => $method)
		{
			$data = array(                                        
				'extension_id' => '',
				'class'        => $class_name,
				'method'       => $method,
				'hook'         => $hook,
				'settings'     => serialize($this->settings),
				'priority'     => 1,
				'version'      => $this->version,
				'enabled'      => "y"
			);
			$this->EE->db->insert('extensions', $data); 	
		}
		
	}
	// END activate_extension



	// --------------------------------
	//  Hooked Methods: get to the meat of things.
	// --------------------------------	
	// --------------------------------
	//  Apply our JS
	// --------------------------------
	public function custom_js($data)
	{		
		//----------------------------------------		
		// Do last call stuff.
		//----------------------------------------
		// Ensure we're not the only one using this hook.
        $data 	= ($this->EE->extensions->last_call !== FALSE) ? $this->EE->extensions->last_call : $data;

		// MSM: subset for this site_id, if available.
		if(!empty($this->settings[$this->site_id]))
		{
			$site_settings = $this->settings[$this->site_id];
		}
		
		// ------------------
		// Sidebar
		// ------------------

		// Sidebar logo.
		$logo_sidebar = '';
		if( !empty($site_settings['sidebar_logo']) )
		{
			$logo_sidebar = $this->_get_image_html($site_settings['sidebar_logo']);
		}
			
		// Sidebar HTML etc.
		$sidebar_html = '';
		if( !empty($site_settings['sidebar_html']) )
		{
			$sidebar_html = $site_settings['sidebar_html'];
		}			

		// Only bother to build our sidebar if we need to.
		if( !empty($logo_sidebar) OR !empty($sidebar_html) )
		{			
			// Sidebar in total
			$brandy_sidebar = '<div id="brandy_sidebar">' . $logo_sidebar . $sidebar_html . '</div>';// TODO: Swap order based on another setting.
			
			$js_sidebar  = '$("#sidebarContent #notePad").before('. json_encode($brandy_sidebar) .');';// Make sure we encode it so nothing breaks - would need to use addslashes instead and/or further function to translate some other characters like newline, tab, /, and non-english glyphs(?) if have to go for lower PHP version.
			
			$data .= NL . $js_sidebar;
		}		
		
		// ------------------
		// Footer	
		// ------------------
		
		// Footer logo
		$logo_footer = '';
		if( !empty($site_settings['footer_logo']) )
		{
			$logo_footer = $this->_get_image_html($site_settings['footer_logo']);
		}		
			
		// Footer HTML etc.
		$footer_html = '';
		if( !empty($site_settings['footer_html']) )
		{
			$footer_html = $site_settings['footer_html']. '<br />';
		}	
			
		// Only bother to build our footer if we need to.
		if( !empty($logo_footer) OR !empty($footer_html) )
		{
			$brandy_footer = '<div id="brandy_footer">' . $logo_footer . $footer_html . '</div>';// TODO: Swap order based on another setting.

			$js_footer  = '$("#footer").prepend('. json_encode($brandy_footer) .');';// Make sure we encode it so nothing breaks - would need to use addslashes instead and/or further function to translate some other characters like newline, tab, /, and non-english glyphs(?) if have to go for lower PHP version.	
			
			$data .= NL . $js_footer;		
		}		
		// ------------------
		
		// Doesn't stop others playing too. Change this if you do want to.
		$this->EE->extensions->end_script = FALSE;
		
		return $data;

	}// END custom_js


    // --------------------------------
	//  Apply our CSS
	// --------------------------------
	public function custom_css($data)
	{		
		//----------------------------------------		
		// Do last call stuff
		//----------------------------------------
		// Ensure we're not the only one using this hook.
        $data 	= ($this->EE->extensions->last_call !== FALSE) ? $this->EE->extensions->last_call : $data;
		
		// ------------------
		// Sidebar
		// ------------------
		// MSM: use setting for this site_id.
		if( !empty($this->settings[$this->site_id]['brandy_css']) )
		{
			$data 	.= NL . $this->settings[$this->site_id]['brandy_css'];
		}		
		
		// Doesn't stop others playing too. Change this if you do want to.
		$this->EE->extensions->end_script = FALSE;
		
		return $data;

	}// END custom_css
	
	
	// --------------------------------
	//  Outputs HTML for our image, if it's okay.
	// --------------------------------
	private function _get_image_html($img_filename){
		
		if(!empty($img_filename))
		{
			$img_file_path = $this->theme_folder_path . 'logos/' . $img_filename;
		}
		
		// Check if is file, is image, and is required image type.
		// getimagesize needs 12 bytes or more of data.
		if( is_file($img_file_path) AND filesize($img_file_path) > 11 )
		{
			$img_size = getimagesize($img_file_path);
			
			if( !empty($img_size) )
			{				
				list($width, $height, $type, $attr) = $img_size;

				$accepted_img_types = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);

				if( in_array($type, $accepted_img_types) )
				{
					// TODO: Update dims if it is bigger than minimum sidebar dimensions.
					$image 	= '<img src="'. $this->theme_folder_url . 'logos/' . $img_filename .'" class="logo" width="'. $width .'" height="'. $height .'" />';
					return $image;
				}				
			}			
		}
			
		return '';
	}// END _get_image_html
	
	
	// --------------------------------
	//  Outputs array of sanitized filenames from a given directory.
	// --------------------------------
	private function _get_files_by_directory($directory) 
	{
		// Array for directory list.
		$files = array();
		
		if( file_exists($directory) )
		{
			// Create directory handler.
			$handle = opendir($directory);
			
			// Loop through and add sanitized filenames to our array.
			while( false !== ($filename = readdir($handle)) ) 
			{
			    if( $filename != "." && $filename != ".." ) 
				{
			        $files[] = $this->EE->security->sanitize_filename($filename);
			    }
			}
			
			// Tidy up.
			closedir($handle);			
		}
		else
		{
			// Flag error.
			$this->EE->output->show_user_error('general' , $this->EE->lang->line('error_theme_folder'));
		}
		
		return $files;
	}// END _get_files_by_directory
 
	
}
// END class 

/* End of file ext.bu_brandy.php */
/* Location: ./system/expressionengine/third_party/bu_brandy/ext.bu_brandy.php */