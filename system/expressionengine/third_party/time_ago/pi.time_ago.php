<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
						'pi_name'        => 'Time Ago',
						'pi_version'     => '0.3',
						'pi_author'      => 'Milan Topalov',
						'pi_description' => 'Takes timestamp and returns time passed since as 1 minute ago, 2 hours ago, etc',
						'pi_usage'       => Time_ago::usage()
					);

class Time_ago
{
	var $return_data = "";

	function Time_ago()
	{
		$this->EE =& get_instance();
		
		// Load custom language file
		$this->EE->lang->loadfile('time_ago');
		
		// Get timestamps
		$timestamp = $this->EE->TMPL->tagdata;
		$timestamp_now = time();
		
		// Get revert to date format
		$revert_to_date_format = $this->EE->TMPL->fetch_param('revert_to_date_format');
				
		// If revert to date format set, revert to date if period more then 7 days
		if ($revert_to_date_format && (($timestamp_now - $timestamp) > (60 * 60 * 24 * 7)))
		{
			$this->return_data = $this->EE->localize->decode_date($revert_to_date_format, $timestamp);
		}
		// Otherwise return time ago format
		else
		{
			$this->return_data = $this->time_ago_in_words($timestamp, $timestamp_now);
		}
	}
	
	private function time_ago_in_words($timestamp, $timestamp_now)
	{
		// Time period definitions (length is in seconds)
		$periods[] = array('unit' => 'year', 	'length' => (60 * 60 * 24 * 365));
		$periods[] = array('unit' => 'month', 	'length' => (60 * 60 * 24 * 30));
		$periods[] = array('unit' => 'week', 	'length' => (60 * 60 * 24 * 7));
		$periods[] = array('unit' => 'day', 	'length' => (60 * 60 * 24));
		$periods[] = array('unit' => 'hour',	'length' => (60 * 60));
		$periods[] = array('unit' => 'minute',	'length' => (60));
		$periods[] = array('unit' => 'second',	'length' => (1));

		$time_difference = $timestamp_now - $timestamp;
		
		// Find the relevant time period unit (e.g. an hour) & calcualte the period count (e.g. number of hours)
		for($i = 0; $i < count($periods);  $i++)
		{
			$period_count = $time_difference / $periods[$i]['length'];
			if ($period_count >= 1)
			{
				$period_count = round($period_count);
				break;
			}
		}
		
		// Find the time period unit name
		$period_unit = ($period_count > 1)?($periods[$i]['unit']."s"):($periods[$i]['unit']);
		
		return($period_count." ".$this->EE->lang->line($period_unit)." ".$this->EE->lang->line('ago'));
	}

	function usage()
	{
		ob_start();
?>		
{exp:channel:entries}
{exp:time_ago revert_to_date_format="%d %F %Y"}{entry_date}{/exp:time_ago}
{/exp:channel:entries}
<?php
		$buffer = ob_get_contents();
		ob_end_clean(); 
		return $buffer;
	}
	
}

?>