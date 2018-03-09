<?=form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file='.$file);?>

<?php
// Main settings table
$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('preference'), 'style' => 'width:46%;'),
    lang('setting')
);

foreach ($settings as $key => $val)
{
    $this->table->add_row(lang($key, $key), $val);
}

echo $this->table->generate();

echo '<p>'. form_submit('submit', lang('submit'), 'class="submit"') .'</p>';

$this->table->clear();

echo form_close();
/* End of file settings.php */
/* Location: ./system/expressionengine/third_party/brandy/views/settings.php */