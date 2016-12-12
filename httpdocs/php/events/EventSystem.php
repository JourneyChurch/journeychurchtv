<?php
/*
Access Tokens
https://developers.facebook.com/docs/facebook-login/access-tokens/#apptokens\

Facebook Event Structure
https://developers.facebook.com/docs/graph-api/reference/event/
*/

class EventSystem {
  private $pageID = 174276778638;
  private $appID = 582098011969883;
  private $secret = "6a39f568bc7e1e55baf23bbb7e65bdbf";
  private $accessToken;

  private function getAccessToken() {
    $this->accessToken = file_get_contents("https://graph.facebook.com/oauth/access_token?client_id=" . $this->appID . "&client_secret=" . $this->secret . "&grant_type=client_credentials");
  }

  public function getEvents() {
    $sinceUnixTimestamp = strtotime("1/2/2015");

    $this->getAccessToken();
    $events = file_get_contents("https://graph.facebook.com/v2.8/" . $this->pageID . "/events?" . $this->accessToken . "&since=" . $sinceUnixTimestamp . "&fields=id,attending_count,cover,description,end_time,name,place,start_time");

    echo($events);
  }
}
?>
