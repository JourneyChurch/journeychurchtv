<?php
/*
Access Tokens
https://developers.facebook.com/docs/facebook-login/access-tokens/#apptokens\

Facebook Event Structure
https://developers.facebook.com/docs/graph-api/reference/event/
*/

namespace php\events\facebook;
use php;

class FacebookConnection {
  private $pageID;
  private $appID = $config["events"]["facebook"]["appID"];
  private $secret = $config["events"]["facebook"]["secret"];
  private $accessToken;

  public function __construct($pageID) {
    $this->pageID = $pageID;
  }

  public function getAccessToken() {

  }

  public function getEvents() {
    echo "$pageID";
  }

  public function getEvent($id) {

  }
}
?>
