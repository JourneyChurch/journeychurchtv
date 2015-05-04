<?php
	require_once("acs_class.php");
		
	$acs = new ACS();
	$event = json_decode($acs->GetEvent($_GET['eventid']));
	$output = new stdClass();
	
	
	
	if (isset($_GET['starttime'])) {
		$output->StartTime = date("Y-m-d H:i:s", $_GET['starttime']);
	}
	else {
		$output->StartTime = $event->StartDate;
	}
	
	if (isset($_GET['endtime'])) {
		$output->EndTime = date("Y-m-d H:i:s", $_GET['endtime']);
	}
	else {
		$output->EndTime = $event->StopDate;
	}
	
	$output->EventName = $event->EventName;
	$output->IsPublished = $event->IsPublished;
	$output->Description = $event->Description;	
	$output->Location = $event->Location;
	$output->Note = $event->Note;
	
	echo json_encode($output);
?>