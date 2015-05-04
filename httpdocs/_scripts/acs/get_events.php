<?php
	require_once("acs_class.php");
		
	$acs = new ACS();
	$events = json_decode($acs->GetMonthEvents($_GET['month'], $_GET['year']));
	
	$output_events = new stdClass();
	
	foreach ($events->Page as $event) {
		if ($event->IsPublished == 1) {
			$starttime = strtotime($event->StartDate);
			$endtime = strtotime($event->StopDate);
			$date = date("m-d-Y", $starttime);
			
			// Check to see if this is an all day event
			if (date("His", $starttime) == "000000" && date("His", $endtime) == "235900") {				
				$event_string = "<div class='fc-event'><a href='/calendar/event/$event->EventId/$starttime/$endtime'>$event->EventName</a></div>";
			}
			else {
				$event_string = "<div class='fc-event'><a href='/calendar/event/$event->EventId/$starttime/$endtime'><strong>" . date("g:i A", $starttime) . "</strong><br /> $event->EventName</a></div>";
			}
			
			if ($output_events->$date) {
				$output_events->$date .= $event_string;
			}
			else {
				$output_events->$date = $event_string;
			}
		}
	}

	echo json_encode($output_events);
?>