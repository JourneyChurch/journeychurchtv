<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Acs_bridge_mcp {
	
	function __construct()
	{
		$this->EE =& get_instance();
		
		$this->_base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=acs_bridge';
		$this->_form_url = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=acs_bridge';
		$this->cache = APPPATH.'/cache'.'/acs_bridge';
		
		$this->theme_folder_url = ee()->config->item('theme_folder_url');
		
		$this->nav = array(
			'settings'		=>	BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=acs_bridge'.AMP.'method=index',
			'explore_data'	=>	BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=acs_bridge'.AMP.'method=data',
			'clear_cache'	=>	BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=acs_bridge'.AMP.'method=clear_cache'
		);
		
	}

	//---------------------------------------------------------------------
	// Script preparation and building for each CP call to the API
	//---------------------------------------------------------------------
	
	function index() {
	
		// Load necessary helpers and libraries
		ee()->load->library('table');
		ee()->load->library('encrypt');
		ee()->load->helper('form');
		
		// Set page title
		ee()->view->cp_page_title = lang('cp_title_setup');
		
		// Set nav
		ee()->cp->set_right_nav($this->nav);
				
		$vars['id'] = NULL;
		$vars['secid'] = NULL;
		$vars['site_number'] = NULL;
		$vars['dst'] = 0;
		$vars['user'] = NULL;
		$vars['pass'] = NULL;
		$vars['calendar_cache'] = 720;
		$vars['location_cache'] = 720;
		$vars['tag_cache'] = 720;
		$vars['serv_cache'] = 720;
		$vars['form_action'] = $this->_form_url.AMP.'method=add_secid';
		$vars['form_hidden'] = NULL;
						
		$query = ee()->db->get('acs_settings');
		
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $app_data) {
				$vars['id'] = $app_data['id'];
				$vars['form_hidden'] = array('id' => $app_data['id']);
				$vars['secid'] = $app_data['secid'];
				$vars['site_number'] = $app_data['site_number'];
				$vars['dst'] = $app_data['dst'];
				$vars['user'] = $app_data['username'];
				$vars['pass'] = $app_data['password'];
				$vars['calendar_cache'] = $app_data['calendar_cache']/3600;
				$vars['location_cache'] = $app_data['location_cache']/3600;
				$vars['tag_cache'] = $app_data['tag_cache']/3600;
				$vars['serv_cache'] = $app_data['serv_cache']/3600;
			}
			
			$vars['pass'] = ee()->encrypt->decode($vars['pass']);
		}

		return ee()->load->view('settings', $vars, TRUE);
	}
	
	function data() {
		ee()->load->library('javascript');
		
		ee()->javascript->output(
			array(
				'var loaderObj = \'<div id="loader"><img src="'.$this->theme_folder_url.'third_party/acs_bridge/ajax-loader.gif" /></div>\';',
				'function render(data) {
					$("#loader").remove();
					$("#acs_view").hide();
					$("#acs_view").append(data);
					$("#acs_view").fadeIn("slow");
				};',
				'$("#calendars").click(function() {
					$("#acs_view").wrapInner(loaderObj);
					$("#loader").fadeIn("slow");
					$.get("'.htmlspecialchars_decode($this->_base_url).'&method=ajax_calendars", function(data) {render(data);});
				});',
				'$("#locations").click(function() {
					$("#acs_view").wrapInner(loaderObj);
					$("#loader").fadeIn("slow");
					$.get("'.htmlspecialchars_decode($this->_base_url).'&method=ajax_locations", function(data) {render(data);});
				});',
				'$("#tags").click(function() {
					$("#acs_view").wrapInner(loaderObj);
					$("#loader").fadeIn("slow");
					$.get("'.htmlspecialchars_decode($this->_base_url).'&method=ajax_tags", function(data) {render(data);});
				});',
				'$("#groups").click(function() {
					$("#acs_view").wrapInner(loaderObj);
					$("#loader").fadeIn("slow");
					$.get("'.htmlspecialchars_decode($this->_base_url).'&method=ajax_groups", function(data) {render(data);});
				});',
				'$("#opps").click(function() {
					$("#acs_view").wrapInner(loaderObj);
					$("#loader").fadeIn("slow");
					$.get("'.htmlspecialchars_decode($this->_base_url).'&method=ajax_opps", function(data) {render(data);});
				});',
				'$("#pos").click(function() {
					$("#acs_view").wrapInner(loaderObj);
					$("#loader").fadeIn("slow");
					$.get("'.htmlspecialchars_decode($this->_base_url).'&method=ajax_positions", function(data) {render(data);});
				});'
			));
					
		ee()->javascript->compile();
		
		// Add stylesheet
		ee()->cp->add_to_head('<link rel="stylesheet" href="'.$this->theme_folder_url.'third_party/acs_bridge/css/styles.css" type="text/css" media="screen"');
		
		// Set page title
		ee()->view->cp_page_title = lang('cp_title_data');
			
		// Set nav
		ee()->cp->set_right_nav($this->nav);

		return ee()->load->view('data', '', TRUE);			
	}
		
	function add_secid() {
		// Load necessary helpers and libraries
		ee()->load->helper('form');
		ee()->load->library('form_validation');
		ee()->load->library('encrypt');
		
		$data = array(
			'id'				=>	ee()->input->get_post('id'),
			'secid'				=>	ee()->input->post('secid'),
			'site_number'		=>	ee()->input->post('site_number'),
			'dst'				=>	ee()->input->post('dst'),
			'username'			=>	ee()->input->post('user'),
			'password'			=>	ee()->input->post('pass'),
			'calendar_cache'	=>	ee()->input->post('calendar_cache'),
			'location_cache'	=>	ee()->input->post('location_cache'),
			'tag_cache'			=>	ee()->input->post('tag_cache'),
			'serv_cache'		=>	ee()->input->post('serv_cache'),
		);
		
		$data['password'] = ee()->encrypt->encode($data['password']);
				
		$data['calendar_cache'] = $data['calendar_cache']*3600;
		$data['location_cache'] = $data['location_cache']*3600;
		$data['tag_cache'] = $data['tag_cache']*3600;
		$data['serv_cache'] = $data['serv_cache']*3600;
			
		if ($data['id'] != NULL) {
			ee()->db->update('acs_settings', $data);
		} else {
			ee()->db->insert('acs_settings', $data);
		}
		
		ee()->session->set_flashdata('message_success', lang('updated'));
		ee()->functions->redirect($this->_base_url);
	}

	function clear_cache() {
		ee()->functions->delete_directory($this->cache);
		ee()->session->set_flashdata('message_success', lang('cache_cleared'));
		ee()->functions->redirect($this->_base_url);
	}


	//---------------------------------------------------------------------
	// AJAX calls to the API
	//---------------------------------------------------------------------
			
	function ajax_calendars() {
		ee()->load->library('table');
		ee()->load->model('acs_model');
		
		$auth = ee()->acs_model->getAuth();		
		ee()->load->library('rest_api', $auth);
		
		try {
			$calendars = json_decode(ee()->rest_api->calendarList());
															
			$tbl = array (
				'table_open'			=> '<table class="mainTable" border="0" cellpadding="0" cellspacing="0">',
				'heading_cell_start'	=> '<th width="50%">',
				'cell_start'			=> '<td width="50%">'
			);
		
			ee()->table->set_template($tbl);
			ee()->table->set_caption(lang('calendars'));		
			ee()->table->set_heading(
				lang('name'),
				lang('id')
			);
						
			foreach($calendars as $cal)
			{
				ee()->table->add_row(
					$cal->Name,
					$cal->CalendarId
				);
			}	
			exit(ee()->table->generate());
		} catch (Exception $e) {
			exit('Error: '.$e->getMessage());
		}		
	}
	
	function ajax_tags() {
		ee()->load->library('table');
		ee()->load->library('soap_api');
		
		try {
			$tags = ee()->soap_api->getTagsList();
					
			$tbl = array(
				'table_open'			=>	'<table class="mainTable" border="0" cellspadding="0" cellspacing="0">',
				'heading_cell_start'	=>	'<th width="50%">',
				'cell_start'			=>	'<td width="50%">',
			);
		
			ee()->table->set_template($tbl);
			ee()->table->set_caption(lang('tags'));
			ee()->table->set_heading(
				lang('id'),
				lang('name')
			);
		
			foreach($tags as $tag)
			{				
				ee()->table->add_row(
					$tag['tagid'],
					$tag['tagname']
				);
			}
			exit(ee()->table->generate());
		} catch (SoapFault $f) {
			exit('Error: '.$f->faultstring);
		}
	}
	
	function ajax_locations() {
		ee()->load->library('table');
		ee()->load->model('acs_model');
		
		$auth = ee()->acs_model->getAuth();		
		ee()->load->library('rest_api', $auth);

		
		try {
			$resources = json_decode(ee()->rest_api->locationsList());
			
			$tbl = array (
				'table_open'			=> '<table class="mainTable" border="0" cellpadding="0" cellspacing="0">',
				'heading_cell_start'	=> '<th width="25%">',
				'cell_start'			=> '<td width="25%">'
			);
		
			ee()->table->set_template($tbl);
			ee()->table->set_caption(lang('locations'));		
			ee()->table->set_heading(
				lang('name'),
				lang('id'),
				lang('catname'),
				lang('catid')
			);
		
			ee()->table->set_empty("&nbsp;");
				
			foreach($resources as $resource)
			{
				ee()->table->add_row(
					$resource->LocationName,
					$resource->LocationId,
					$resource->ResourceCategoryName,
					$resource->ResourceCategoryId
				);
			}		
			exit(ee()->table->generate());
		} catch (Exception $e) {
			exit('Error: '.$e->getMessage());
		}	
	}
	
	function ajax_groups() {
		ee()->load->library('table');
		ee()->load->library('soap_api');
		
		try {
			$groups = ee()->soap_api->sgGetGroupList(array('groupid','parentid','sgname','description','cat1'),array('-1'));
			
			$tbl = array (
				'table_open'			=> '<table class="mainTable" border="0" cellpadding="0" cellspacing="0">',
				'heading_cell_start'	=> '<th width="20%">',
				'cell_start'			=> '<td width="20%">'
			);
			
			ee()->table->set_template($tbl);
			ee()->table->set_caption(lang('small_groups'));
			ee()->table->set_heading(
				lang('group_id'),
				lang('parent_id'),
				lang('name'),
				lang('description'),
				lang('catname')
			);
			
			ee()->table->set_empty("&nbsp;");
			
			foreach($groups as $group)
			{
				ee()->table->add_row(
					$group['groupid'],
					$group['parentid'],
					$group['sgname'],
					$group['description'],
					$group['cat1']
				);
			}
			
			exit(ee()->table->generate());
		} catch (SoapFault $f) {
			exit('Error: '.$f->faultstring);
		}
	}
	
	function ajax_opps() {
		ee()->load->library('table');
		ee()->load->library('soap_api');
		
		try {
			$opps = ee()->soap_api->vmGetServeOpportunities(array('needid','needname','needdesc','groupid','closingdate'),array('-1'));
			
			$tbl = array (
				'table_open'			=> '<table class="mainTable" border="0" cellpadding="0" cellspacing="0">'
			);
			
			ee()->table->set_template($tbl);
			ee()->table->set_caption(lang('opportunities'));
			ee()->table->set_heading(
				lang('need_id'),
				lang('group_id'),
				lang('name'),
				lang('description')
			);
			
			ee()->table->set_empty("&nbsp;");
			
			foreach($opps as $k => $v)
			{
				if($v['closingdate'] < time()) {
					unset($opps[$k]);
					continue;
				}
				
				$needid = array('data'=>$v['needid'],'width'=>'5%');
				$groupid = array('data'=>$v['groupid'],'width'=>'5%');
				$name = array('data'=>$v['needname'],'width'=>'15%');
				$desc = array('data'=>$v['needdesc'],'width'=>'75%');
				
				ee()->table->add_row($needid,$groupid,$name,$desc);
			}
			
			exit(ee()->table->generate());
		} catch (SoapFault $f) {
			exit('Error: '.$f->faultstring);
		}	
	}
	
	function ajax_positions() {
		ee()->load->library('table');
		ee()->load->library('soap_api');
		
		try {
			$pos = ee()->soap_api->vmGetPositions();
			
			$tbl = array (
				'table_open'		=> '<table class="mainTable" border="0" cellpadding="0" cellspacing="0">'
			);
			
			ee()->table->set_template($tbl);
			ee()->table->set_caption(lang('positions'));
			ee()->table->set_heading(
				lang('pos_id'),
				lang('name'),
				lang('description'),
				lang('active')
			);
			
			ee()->table->set_empty("&nbsp;");
			
			foreach($pos as $p)
			{
				$posid = array('data' => $p['positionid'], 'width' => '5%');
				$name = array('data' => $p['positionname'], 'width' => '20%');
				$desc = array('data' => $p['description'], 'width' => '70%');
				$active = array('data' => $p['active'], 'width' => '5%');
				
				ee()->table->add_row($posid, $name, $desc, $active);
			}
			
			exit(ee()->table->generate());
		} catch (SoapFault $f) {
			exit('Error: '.$f->faultstring);
		}
	}
		
}

// END CLASS

/* End of file mcp.acs_bridge.php */
/* Location: ./system/expressionengine/third_party/acs_bridge/mcp.acs_bridge.php */