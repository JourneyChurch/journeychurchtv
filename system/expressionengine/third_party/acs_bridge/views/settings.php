<?=form_open($form_action, '', $form_hidden);?>

<?php

	// SOAP settings
	ee()->table->set_template($cp_pad_table_template);
	
	ee()->table->set_heading(lang('key'), '');
	
	ee()->table->add_row(lang('secid'), form_input('secid', $secid));
	ee()->table->add_row(lang('site_number'), form_input('site_number', $site_number));
	
	if($dst == 0) {
		$dst_radio = form_radio('dst', 1, FALSE);
		$dst_radio .= form_label('Yes', 'dst', array('style' => 'padding: 0 24px 0 6px;'));
		$dst_radio .= form_radio('dst', 0, TRUE);	
		$dst_radio .= form_label('No', 'dst', array('style' => 'padding: 0 6px;'));	
	} else {
		$dst_radio = form_radio('dst', 1, TRUE);
		$dst_radio .= form_label('Yes', 'dst', array('style' => 'padding: 0 24px 0 6px;'));
		$dst_radio .= form_radio('dst', 0, FALSE);	
		$dst_radio .= form_label('No', 'dst', array('style' => 'padding: 0 6px;'));
	}

	ee()->table->add_row(lang('dst'), $dst_radio);
	
	echo ee()->table->generate();
	
	// REST authorization settings
	ee()->table->set_template($cp_pad_table_template);
	
	ee()->table->set_heading(lang('user_settings'), '');
	
	ee()->table->add_row(lang('user'), form_input('user', $user));
	ee()->table->add_row(lang('password'), form_password('pass', $pass));
	
	echo ee()->table->generate();
		
	ee()->table->set_template($cp_pad_table_template);
	ee()->table->set_heading(lang('cache'),'');
	
	ee()->table->add_row(lang('calendar_cache'),form_input('calendar_cache', $calendar_cache));
	ee()->table->add_row(lang('location_cache'),form_input('location_cache', $location_cache));
	ee()->table->add_row(lang('tag_cache'),form_input('tag_cache', $tag_cache));
	ee()->table->add_row(lang('serv_cache'),form_input('serv_cache', $serv_cache));
				
	echo ee()->table->generate();
?>

<?=form_submit('submit', lang('save'), 'class="submit"')?>
<?=form_close()?>