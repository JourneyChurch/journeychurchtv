<?php
	$SiteNumber = 106649;

	// Create cURL Handle
	$url = 'https://secure.accessacs.com/api_accessacs_mobile/v2/106649/calendars';
	$ch = curl_init($url);
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Basic Z2VvZmZoZW5zbGV5OlNoZWliYmEyMDE2',
		'Sitenumber: 106649'
	  )
	);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
	
	$response = curl_exec($ch);
	curl_close($ch);
?>