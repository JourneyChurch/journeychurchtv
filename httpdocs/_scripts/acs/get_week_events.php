<?php
	require_once("acs_class.php");
		
	$acs = new ACS();
	
	echo $acs->GetEvents($_GET['startdate'], $_GET['enddate']);
?>