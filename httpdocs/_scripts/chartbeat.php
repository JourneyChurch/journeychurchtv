<?php
	if (isset($_GET['type'])) {
		// create curl resource 
	    $ch = curl_init(); 
	
		if ($_GET['type'] == 'current_pages') {
		    // set url 
		    curl_setopt($ch, CURLOPT_URL, "http://api.chartbeat.com/live/toppages/?host=journeychurch.tv&limit=10&apikey=a61ecd0a22efbd09b2d176c08050c81a"); 
	    }
	    else if ($_GET['type'] == 'quick_stats') {
	    	curl_setopt($ch, CURLOPT_URL, "http://api.chartbeat.com/live/quickstats/?host=journeychurch.tv&apikey=a61ecd0a22efbd09b2d176c08050c81a");
	    }
	    else if ($_GET['type'] == 'geo') {
	    	curl_setopt($ch, CURLOPT_URL, "http://api.chartbeat.com/live/geo/?host=journeychurch.tv&apikey=a61ecd0a22efbd09b2d176c08050c81a");
	    }
	    else {
	    	die();
	    }
	
	    //return the transfer as a string 
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	
	    // $output contains the output string 
	    echo curl_exec($ch); 
	
	    // close curl resource to free up system resources 
	    curl_close($ch);
	}
?>