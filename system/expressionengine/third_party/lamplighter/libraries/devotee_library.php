<?php
/**
 * Devotee Library
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Masuga Design
 * @link		http://www.masugadesign.com
 */

class Devotee_library
{
	protected $_addons = array();
	protected $EE = null;

	public function __construct()
	{
		$this->EE =& get_instance();

		// Set cache settings
		$this->_cache_path = $this->EE->config->item('devotee_monitor_cachepath') ? $this->EE->config->item('devotee_monitor_cachepath') : APPPATH . 'cache/devotee/';
		$this->_cache_time = 60 * 60; // 1 hour

		// Create cache folder if it doesn't exist
		if( ! is_dir($this->_cache_path))
		{
			mkdir($this->_cache_path, DIR_WRITE_MODE);
		}

		// Set theme URL
		$this->theme_url	= defined( 'URL_THIRD_THEMES' )
					? URL_THIRD_THEMES . '/devotee/'
					: $this->EE->config->item('theme_folder_url') . 'third_party/devotee/';

		// Include the ignored_addons array
		$this->_ignored_addons = is_array($this->EE->config->item('devotee_monitor_ignored_addons')) ? $this->EE->config->item('devotee_monitor_ignored_addons') : array();
	}

	/**
	 * Get installed add-on information
	 *
	 * @param Boolean $show_hidden_addons
	 * @param Boolean $return_view
	 *
	 * @return Mixed
	 */
	public function get_addons($show_hidden_addons = FALSE, $return_view=TRUE)
	{
		$this->EE->load->helper(array('file', 'language'));
		$this->EE->load->helper('directory');
		$this->EE->load->library('addons');
		$this->EE->load->model('addons_model');
		$this->EE->load->library('api');

		// Scan third_party folder
		$map = directory_map(PATH_THIRD, 2);

		// Bail out if nothing found
		if($map === FALSE)
		{
			return 'No third-party add-ons were found.';
		}

		// Get fieldtypes because the add-ons library doesn't give all the info
		$this->EE->api->instantiate('channel_fields');
		$fieldtypes = $this->EE->api_channel_fields->fetch_all_fieldtypes();

		// Set third-party add-ons
		$third_party = array_intersect_key($this->EE->addons->_packages, $map);

		// Get all installed add-ons
		$installed = array(
			'modules'     => $this->EE->addons->get_installed('modules'),
			'plugins'     => $this->_get_plugins(),
			'extensions'  => $this->EE->addons->get_installed('extensions'),
			'fieldtypes'  => $this->EE->addons->get_installed('fieldtypes'),
			'accessories' => $this->EE->addons->get_installed('accessories')
		);

		// Loop through each third-party package
		foreach($third_party as $package => $types)
		{
			// Skip this if we already have it
			if(array_key_exists($package, $this->_addons))
			{
				continue;
			}

			// Check if this is a module
			if(array_key_exists($package, $installed['modules']))
			{
				$addon = $installed['modules'][$package];

				// Fix weird EE name issue
				$this->EE->lang->loadfile(( ! isset($this->lang_overrides[$package])) ? $package : $this->lang_overrides[$package]);
				$name = (lang(strtolower($package) . '_module_name') != FALSE) ? lang(strtolower($package) . '_module_name') : $addon['name'];

				$this->set_addon_info($package, $name, $addon['module_version'], $types);
			}
			// Check if this is a plugin
			elseif(array_key_exists($package, $installed['plugins']))
			{
				$addon = $installed['plugins'][$package];
				$this->set_addon_info($package, $addon['pi_name'], $addon['pi_version'], $types);
			}
			// Check if this is an extension
			elseif(array_key_exists($package, $installed['extensions']))
			{
				$addon = $installed['extensions'][$package];
				$this->set_addon_info($package, $addon['name'], $addon['version'], $types);
			}
			// Check if this is a fieldtype
			elseif(array_key_exists($package, $installed['fieldtypes']))
			{
				$addon = $fieldtypes[$package];
				$this->set_addon_info($package, $addon['name'], $addon['version'], $types);
			}
			// Check if this is an accessory
			elseif(array_key_exists($package, $installed['accessories']))
			{
				$addon = $installed['accessories'][$package];

				// We need to load the class if it's not devot:ee to get more info
				// Otherwise, we already have the info
				if($package != 'devotee')
				{
					if( ! class_exists($addon['class']))
					{
						require_once PATH_THIRD . "{$package}/acc.{$package}.php";
					}

					$acc = new $addon['class']();
				}
				else
				{
					$acc = array(
						'name'    => $this->name,
						'version' => $this->version
					);
					$acc = (object) $acc;
				}

				if(isset($acc))
				{
					$this->set_addon_info($package, $acc->name, $acc->version, $types);

					unset($acc);
				}
			}
		}

		// Remove ignored add-ons from the _addons data member prior to fetching updates
		foreach ($this->_ignored_addons as $index => $package)
			unset($this->_addons[$package]);

		// Check updates
		$updates = $this->get_updates();

		$updates_decoded = json_decode($updates);
		if( ! $updates_decoded)
		{
			return $this->EE->load->view('error', array(
				'error' => 'Sorry, but something went wrong. Please try again later.',
				'cp' => $this->EE->cp
			), TRUE);
		}
		elseif( ! empty($updates_decoded->error))
		{
			return $this->EE->load->view('error', array(
				'error' => $updates_decoded->error,
				'cp' => $this->EE->cp
			), TRUE);
		}


		// Hidden add-ons
		$hidden_addons = array();

		if ( $return_view ) {

			// Return the view
			return $this->EE->load->view('accessory', array(
				'updates'       => json_decode($updates),
				'last_check'    => filemtime($cache_file),
				'hidden_addons' => $hidden_addons,
				'show_hidden'	=> $show_hidden_addons,
				'cp'			=> $this->EE->cp
			), TRUE);

		} else {
			return json_decode($updates);
		}
	}

	/**
	 * Set add-on info
	 *
	 * @param   string  The package name
	 * @param   string  The actual add-on name
	 * @param   string  The version number
	 * @param   array   Add-on types (module, plugin, etc.)
	 * @return  void
	 */
	public function set_addon_info($package, $name, $version, $types)
	{
		$this->_addons[$package] = array(
			'name'    => $name,
			'version' => $version,
			'types'   => $this->abbreviate_types(array_keys($types))
		);
	}

	/**
	 * Get update info from the API
	 *
	 * @return  string
	 */
	public function get_updates()
	{
		$data = array(
			'data'    => $this->_addons,
			'site'    => md5( $this->EE->config->item('site_label') ),
			'version' => $this->EE->config->item('app_version')
		);

		$ch = curl_init('http://monitor.devot-ee.com/');
		curl_setopt_array($ch, array(
			CURLOPT_POST           => TRUE,
			CURLOPT_CONNECTTIMEOUT => 2,
			CURLOPT_TIMEOUT        => 5,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POSTFIELDS     => json_encode($data),
			CURLOPT_HTTPHEADER     => array(
				'Content-type: application/json'
			)
		));
		$response = curl_exec($ch);
		curl_close($ch);

		if( ! $response)
		{
			$response = json_encode(array(
				'error' => 'The API could not be reached. Please try again later.'
			));
		}

		return $response;
	}

	/**
	 * Create an abbreviated list of add-on types, and designate whether the current add-on
	 * is of a particular type
	 *
	 * @param   array  The add-on types
	 * @return  array
	 */
	public function abbreviate_types($types = array())
	{
		$available_types = array(
			'module'    => 'MOD',
			'extension' => 'EXT',
			'plugin'    => 'PLG',
			'fieldtype' => 'FLD',
			'accessory' => 'ACC'
		);

		$abbrevs = array();

		foreach($available_types as $key => $abbrev)
		{
			$abbrevs[$abbrev] = (in_array($key, $types)) ? TRUE : FALSE;
		}

		return $abbrevs;
	}

	public function _get_plugins() {
		$this->EE->load->helper('directory');
		$plugins = $info = array();
		if (($map = directory_map(PATH_THIRD, 2)) !== FALSE)
		{
			foreach ($map as $pkg_name => $files)
			{
				if ( ! is_array($files))
				{
					$files = array($files);
				}

				foreach ($files as $file)
				{
					if (is_array($file))
					{
						// we're only interested in the top level files for the addon
						continue;
					}

					elseif (strncasecmp($file, 'pi.', 3) == 0 &&
							substr($file, -4) == '.php' &&
							strlen($file) > strlen('pi..php'))
					{
						if ( ! class_exists(ucfirst($pkg_name)))
						{
							if (!@include_once(PATH_THIRD.$pkg_name.'/'.$file)) {
								continue;
							}
						}

						$plugins[] = $pkg_name;

						$info[$pkg_name] = array_unique($plugin_info);
					}
				}
			}
		}
		return $info;
	}
}