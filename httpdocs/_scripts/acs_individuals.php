<?php
require_once("access_acs.php");

//date_default_timezone_set('America/New_York'); 

$individual = array();
$counter = 0;
//$individual['modified_since'] = '2010-06-08 00:00:00';
$individual['id'] = 186;

$fp = fopen('foo.out', 'w');

$options = array(
	CURLOPT_USERPWD => CCBAPI_AUTH, 
    CURLOPT_TIMEOUT => 400, 
);

echo 'Processing CCB api...' . PHP_EOL;

// Call CCB individual_profile api to retrieve all profiles
//$response1 = curl_get(CCBAPI_URL.'srv=individual_profiles&', $individual, $options, $fp);
$response1 = curl_get(CCBAPI_URL.'srv=execute_search&', $individual, $options, $fp);

//$xml1 = simplexml_load_string($response1);
//$countIndividual = $xml1->response->individuals['count'];
//echo $response1;
fclose($fp);
echo 'Done: ' . PHP_EOL;
/*
		$xml1 = simplexml_load_string($response1);
		$countIndividual = $xml1->response->individuals['count'];
		
		if($countIndividual > 0) {
			echo  '  ' . $countIndividual . ' account(s) exist with CCB ID: ' . $xml1->response->individuals[0]->individual['id'] . PHP_EOL;
			_log ('  ' . $countIndividual . ' account(s) exist with CCB ID: ' . $xml1->response->individuals[0]->individual['id'], 1);
		} else {
			// Create an account through CCB create_individual api with first/last name, email, address and phone
			echo  "  account does not exist: " . PHP_EOL;
			_log ("  account does not exist: ", 1);

            $individual['first_name']             = $row['firstname'];
            $individual['last_name']              = $row['lastname'];
            $individual['mailing_street_address'] = $row['addressline1'];
            $individual['mailing_city']           = $row['city'];
            $individual['mailing_state']          = $row['state'];
            $individual['mailing_zip']            = $row['postalcode'];
            $individual['contact_phone']          = $row['phoneareacode'].$row['phonelocalline'];
//			print_r($individual);

			$response2 = curl_post(CCBAPI_URL.'srv=create_individual', $individual, $options);

            $xml2 = simplexml_load_string($response2);
         	echo  '  A new CCB account has been created with ID: ' . $xml2->response->individuals[0]->individual['id'] . PHP_EOL;
         	_log ('  A new CCB account has been created with ID: ' . $xml2->response->individuals[0]->individual['id'], 1);
			$counter++;

//			break;
		}
*/
//echo $response1 . PHP_EOL;


function _log ($text, $level) {
    $_logLevel = 1;
	if ($level >= $_logLevel) {
		$log = sprintf('logs/%s.log', ($level !== 9)? 'debug' : date('YmdHis'));
		if (!is_string($text)) {
			$text = print_r($text, true);
		}
		// file_put_contents($log, $text.PHP_EOL, FILE_APPEND);
		file_put_contents($log, date('c', time()). ' : ' . $text . PHP_EOL, FILE_APPEND);
	}
	return true;
}

?>
