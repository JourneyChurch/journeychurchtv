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

  // Convert array of fields to facebook's query string format
  // Example: place{name,location{latitude,longitude}}
  private static function convert_array_to_query_string($array)
  {
    $query_string = "";

    $last_index = count($array) - 1;
    $i = 0;

    // Loop over array keys
    foreach($array as $key => $value)
    {
      // If isn't a key for another array (key is a number)
      if (!is_string($key))
      {
        // If last key
        if ($i === $last_index)
        {
          // Append array value
          $query_string .= $array[$key];
        }
        else
        {
          // Append array value with comma for next value
          $query_string .= $array[$key] . ",";
        }
      }
      else
      {
        // Append key with opening bracket for children
        $query_string .= "$key{";

        // Recursively call for query string of child array
        $query_string .= Facebook::convert_array_to_query_string($array[$key]);

        // Append closing bracket
        if ($array[$i] === $array[$last_index])
        {
          $query_string .= "}";
        }
        // If not last value append a comma for next value
        else {
          $query_string .= "},";
        }
      }

      ++$i;
    }

    return $query_string;
  }

  // Get all values from a multi dimensional array
  private static function get_values_from_array($array, $parent_key = NULL)
  {
    $values_array = [];

    // Loop over array keys
    foreach($array as $key => $value)
    {
      // If key isn't pointing to array add value
      if (!is_array($value))
      {
        // If there is a parent key, change the name of the key to {parent key}_{key}
        if ($parent_key)
        {
          $values_array[$parent_key . "_" . $key] = $value;
        }
        else
        {
          $values_array[$key] = $value;
        }
      }
      else
      {
        // Recursively get children values and append to parent array
        // If there is a parent key append with current key for recursive call
        if ($parent_key)
        {
          $values_array = array_merge($values_array, Facebook::get_values_from_array($array[$key], $parent_key . "_" . $key));
        }
        else
        {
          $values_array = array_merge($values_array, Facebook::get_values_from_array($array[$key], $key));
        }
      }
    }

    return $values_array;
  }

  // Make GET request
  private static function request_GET($url, $query_parameters = NULL)
  {

    // Start cURL connection
    $curl = curl_init();

    // If parameters, Encode url
    if ($query_parameters)
    {
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
    $decoded_response = json_decode($response, true);

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
    return Facebook::request_GET($url, $query_parameters)["access_token"];
  }

  // Get events from facebook page
  public function events()
  {
    // Get page id from template
    $page_id = ee()->TMPL->fetch_param("page_id");

    // Get access token
    $access_token = $this->get_access_token();

    // Fields to return from api
    $fields = [
      "id",
      "cover" => [
        "source"
      ],
      "name",
      "start_time"
    ];

    // Create commas separated string for fields in url
    $fields_query_string = Facebook::convert_array_to_query_string($fields);

    // Request url for page events
    $url = "https://graph.facebook.com/v2.10/$page_id/events?access_token=$access_token&fields=$fields_query_string";

    // Make request to facebook api
    $events = Facebook::request_GET($url)["data"];

    // Organize variables
    $variables = [];
    foreach($events as $event) {
      array_push($variables, Facebook::get_values_from_array($event));
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
    $fields = [
      "id",
      "attending_count",
      "cover" => [
        "source"
      ],
      "description",
      "end_time",
      "name",
      "place" => [
        "location" => [
          "latitude",
          "longitude"
        ],
        "name"
      ],
      "start_time",
      "ticket_uri"
    ];

    // Get query string for fields
    $fields_query_string = Facebook::convert_array_to_query_string($fields);

    // Request url for page events
    $url = "https://graph.facebook.com/v2.10/$event_id?access_token=$access_token&fields=$fields_query_string";

    // Make request to facebook api
    $event = Facebook::request_GET($url);

    // Get variables from resulting event array
    $variables = Facebook::get_values_from_array($event);
    $variables["page_id"] = $page_id;

    return ee()->TMPL->parse_variables_row(ee()->TMPL->tagdata, $variables);
  }
}
