<?php

require "twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

define("CONSUMER_KEY", "3KTKdgGx8a3hDVstQqg2NTg7u");
define("CONSUMER_SECRET", "i3yf0LMiY8WFCBBTwMjDGsGwAPZhaPtq1APTicsbPdZoN0hYdT");

class TwitterConnection {
  //private $consumer_key = "3KTKdgGx8a3hDVstQqg2NTg7u";
  //private $consumer_secret = "i3yf0LMiY8WFCBBTwMjDGsGwAPZhaPtq1APTicsbPdZoN0hYdT";
  private $access_token = "440363064-aW8sWuGosYDcsmL4CegEoMVOYH7zwwIYImIdCXlc";
  private $access_token_secret = "bjgmnNjYTAOgvSO4MFhQDZApuQ090FF5XjApBlOjQ1aAW";
  private $connection;
  private $content;

  public function __construct() {
    $this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->access_token, $this->access_token_secret);
    echo $this->connection;
    //$this->content = $this->connection->get("account/verify_credentials");
  }

  public function getData() {
    //return $this->connection->get("search/tweets", ["q" => "twitterapi"]);
  }
}

?>
