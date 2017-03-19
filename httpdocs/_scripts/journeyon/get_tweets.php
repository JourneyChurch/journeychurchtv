<?php

require "twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

define("CONSUMER_KEY", "3KTKdgGx8a3hDVstQqg2NTg7u");
define("CONSUMER_SECRET", "i3yf0LMiY8WFCBBTwMjDGsGwAPZhaPtq1APTicsbPdZoN0hYdT");
$access_token = "440363064-aW8sWuGosYDcsmL4CegEoMVOYH7zwwIYImIdCXlc";
$access_token_secret = "bjgmnNjYTAOgvSO4MFhQDZApuQ090FF5XjApBlOjQ1aAW";

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token, $access_token_secret);
$content = $connection->get("account/verify_credentials");
//$data = $connection->get("search/tweets", ["q" => "from:journeychurchtv#journeyon"]);
$data = $connection->get("search/tweets", ["q" => "@journeychurchtv"]);

echo stripcslashes(json_encode($data));

?>
