<?php

//set_time_limit(600);	// set the number of seconds a script is allowed to run. 
$ch = curl_init("http://70.32.99.23/_component/turn_service/off/n01w!llFindm3/");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$result = curl_exec($ch);
curl_close($ch);

echo $result . PHP_EOL;
