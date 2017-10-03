<?php
/*
  Plugin to fetch Facebook events via a page id
*/

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Facebook_connection
{
  private $appID;
  private $secret;
  private $accessToken;
  public $return_data;

  public function __construct()
  {
    include("config.php");
    $this->appID = $config["events"]["facebook"]["appID"];
    $this->secret = $config["events"]["facebook"]["secret"];
  }

  private function get_access_token()
  {
    
  }

  public function get_events()
  {
    return "Get events";
  }

  public function get_event()
  {
    return ee()->TMPL->fetch_param("page_id") . " " . ee()->TMPL->fetch_param("event_id");
  }
}
