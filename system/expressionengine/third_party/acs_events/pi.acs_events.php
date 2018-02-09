<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

// Plugin information
$plugin_info = [
    'pi_name' => 'ACS Events',
    'pi_version' => '1.0',
    'pi_author' => 'Zac Conant',
    'pi_author_url' => 'http://journeychurch.tv/',
    'pi_description' => 'Pull events from ACS calendar'
];

/*
  Plugin to fetch ACS events from a calendar
  https://wiki.acstechnologies.com/display/DevCom/Get+a+List+of+Events
*/
class Acs_events
{
  // username and password for login
  private $username;
  private $password;
  private $site_number;
  private $calendar_id;

  // Include facebook configuration variables
  public function __construct()
  {
    include(dirname(__FILE__). "/config.php");
    $this->username = $config["acs"]["username"];
    $this->password = $config["acs"]["password"];
    $this->site_number = $config["acs"]["site_number"];
    $this->calendar_id = $config["acs"]["calendar_id"];
  }

  // Make GET request
  private function request_GET($url, $query_parameters = NULL)
  {
    // Start cURL connection
    $curl = curl_init();

    // If parameters, Encode url
    if ($query_parameters)
    {
      $query = http_build_query($query_parameters);
      $url .= "?$query";
    }

    // Set to return string on, give curl a url, and use basic authentication
    curl_setopt_array($curl, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => "$this->username:$this->password",
      CURLOPT_VERBOSE => true
    ]);

    // Get response
    $response = curl_exec($curl);

    // Close cURL
    curl_close($curl);

    // decode from JSON
    return json_decode($response, true);
  }

  public function events()
  {
    $url = "https://secure.accessacs.com/api_accessacs_mobile/v2/$this->site_number/calendars/$this->calendar_id/events";

    $variables = $this->request_GET($url);

    return ee()->TMPL->parse_variables_row(ee()->TMPL->tagdata, ["variables" => $variables["Page"][0]["EventId"]]);
  }
}

echo (new Acs_events())->events();
