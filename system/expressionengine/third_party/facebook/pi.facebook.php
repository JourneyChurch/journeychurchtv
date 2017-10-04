<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// Plugin information
$plugin_info = [
    'pi_name'         => 'Facebook Events',
    'pi_version'      => '1.0',
    'pi_author'       => 'Zac Conant',
    'pi_author_url'   => 'http://journeychurch.tv/',
    'pi_description'  => 'Pull Facebook events from any facebook page'
];

/*
  Plugin to fetch Facebook events via a facebook page id
  https://developers.facebook.com/docs/graph-api/reference/page/events/
*/

class Facebook
{
  private $appID;
  private $secret;

  // Include facebook configuration variables
  public function __construct()
  {
    include("config.php");
    $this->appID = $config["events"]["facebook"]["appID"];
    $this->secret = $config["events"]["facebook"]["secret"];
  }

  // Make GET request
  private static function request_GET($url, $query_parameters = NULL)
  {

    // Start cURL connection
    $curl = curl_init();

    // If parameters, Encode url
    if ($query_parameters) {
      $query = http_build_query($query_parameters);
      $url .= "?$query";
    }

    // Set to Facebook access token url and return as string
    curl_setopt_array($curl, [
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => $url
    ]);

    // Get access token from JSON response
    $response = curl_exec($curl);
    $decoded_response = json_decode($response);

    // Close cURL
    curl_close($curl);

    return $decoded_response;
  }

  // Get access token from Facebook
  private function get_access_token()
  {
    // Request url for access token
    $url = "https://graph.facebook.com/oauth/access_token";

    // Query parameters for access token
    $query_parameters = [
      "client_id" => $this->appID,
      "client_secret" => $this->secret,
      "grant_type" => "client_credentials"
    ];

    // Make request for access token
    return Facebook::request_GET($url, $query_parameters)->access_token;
  }

  // Get events from facebook page
  public function events()
  {
    // Get page id from template
    $page_id = ee()->TMPL->fetch_param("page_id");

    // Get access token
    $access_token = $this->get_access_token();

    // Fields to return from api
    $fields = "id,cover,name,start_time";

    // Request url for page events
    $url = "https://graph.facebook.com/v2.10/$page_id/events?access_token=$access_token&fields=$fields";

    // Make request to facebook api
    $events = Facebook::request_GET($url)->data;

    // Organize variables
    $variables = [];
    $index = 0;

    foreach($events as $event) {
      $variables[$index]["id"] = $event->id;
      $variables[$index]["image"] = $event->cover->source;
      $variables[$index]["name"] = $event->name;
      $variables[$index]["start_time"] = $event->start_time;

      ++$index;
    }

    // Put events into template variables
    return ee()->TMPL->parse_variables_row(ee()->TMPL->tagdata, ["events" => $variables, "page_id" => $page_id]);
  }

  // Get single event from facebook page
  public function event()
  {
    // Get page id from template
    $page_id = ee()->TMPL->fetch_param("page_id");

    // Get event id from template
    $event_id = ee()->TMPL->fetch_param("event_id");

    // Get access token
    $access_token = $this->get_access_token();

    // Fields to return from api
    $fields = "id,attending_count,cover,description,end_time,name,place,start_time,ticket_uri";

    // Request url for page events
    $url = "https://graph.facebook.com/v2.10/$event_id?access_token=$access_token&fields=$fields";

    // Make request to facebook api
    $event = Facebook::request_GET($url);

    // Organize variables
    $variables = [];
    $variables["id"] = $event->id;
    $variables["attending_count"] = $event->attending_count;
    $variables["image"] = $event->cover->source;
    $variables["description"] = $event->description;
    $variables["end_time"] = $event->end_time;
    $variables["name"] = $event->name;
    $variables["place_name"] = $event->place->name;
    $variables["longitude"] = $event->place->location->longitude;
    $variables["latitude"] = $event->place->location->latitude;
    $variables["start_time"] = $event->start_time;
    $variables["ticket_uri"] = $event->ticket_uri;

    return ee()->TMPL->parse_variables_row(ee()->TMPL->tagdata, $variables);
  }
}
