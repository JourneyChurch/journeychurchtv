<?php

$plugin_info = array(
  'pi_name' => 'MultiPass',
  'pi_version' =>'1.0',
  'pi_author' =>'Geoff Hensley',
  'pi_description' => 'Creates a MultiPass anchor link',
  'pi_usage' => Multipass::usage()
);

/**
 * MultiPass is a plugin for ExpressionEngine that generates the anchor element, 
 * with necessary href to let users sign on to Tender from ExpressionEngine
 *
 * @package MultiPass
 */
class Multipass {
	
	/**
	 * The returned HTML to the template parser
	 *
	 * @var string
	 */
	public $return_data;

	/**
	 * The MultiPass object constructor which sets the return data
	 * so no extra function call is made.
	 */
	public function Multipass()
	{
		$this->EE =& get_instance();
		
		$sso_params = array();
		
		//Checks to see if a link was even entered
		if (!$link = str_replace("http://", "", $this->EE->TMPL->fetch_param('link')))
		{	
			$this->return_data = "You haven't entered your ChOP link yet!";
			return;
		}
		
		if (!$this->EE->TMPL->fetch_param('key'))
		{
			$this->return_data = "You haven't entered your SSO API Key";
			return;
		}
		
		//If the text parameter is set, use that, if not: use church online.
		$link_text = $this->EE->TMPL->fetch_param('text') ? $this->EE->TMPL->fetch_param('text') : "Church Online";
		
		// Checks to see if the user is logged in
		if ($this->EE->session->userdata['member_id'] == 0 || $this->EE->session->userdata['member_id'] == "")
		{
			$this->return_data =  '<a href="http://'.$link.'">'.$link_text.'</a>';
			return;
		}
		
		//Is the user's email setup?
		if (!$email = $this->EE->session->userdata['email'])
		{
			$this->return_data = "You must enter your email address";
			return;
		}
		
		$first_name= "Geoff";
		$last_name= "Hensley";
		
		//Now we setup the params with reasonable defaults
		$sso_params = array(
			'email' => $email,
			'expires' => date("Y-m-d H:i:s", strtotime("+30 minutes")),
			'first_name' => $first_name,
			'last_name' => $last_name,
			'unique_id' => $this->EE->session->userdata['member_id'],
			'nickname' => $this->EE->session->userdata['screen_name'],
			'trusted'   => true,
		);
		
		//Finally generate the Tender URL
		$this->return_data = '<a href="http://'. $link  . $this->getLink($sso_params).'">'.$link_text.'</a>';
	}
	
	/**
	 * Generates a ChOP API link, accepting parameters and doing all the
	 * hashing, security-ing, and modify-ing.
	 *
	 * @param array $params User information 
	 * @return string Tender MultiPass link
	 */
	private function getLink($params)
	{
		$SSO_KEY = $this->EE->TMPL->fetch_param('key'); //ChOP Key

		$hash= hash('sha1', $SSO_KEY, true);
		
		$saltedHash = substr($hash,0,16);
		
		$iv= "OpenSSL for Ruby";

		$data = json_encode($params);
		
		for ($i = 0; $i < 16; $i++)
		{
		    $data[$i] = $data[$i] ^ $iv[$i];
		}

		$pad = 16 - (strlen($data) % 16);
		$data = $data . str_repeat(chr($pad), $pad);
		$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'','cbc','');
		mcrypt_generic_init($cipher, $saltedHash, $iv);
		$encryptedData = mcrypt_generic($cipher,$data);
		mcrypt_generic_deinit($cipher);

		$encoded_signature = urlencode(base64_encode($encryptedData));
		
		return "?sso={$encoded_signature}";
	}
	
	/**
	 * These are the instructions for the Plugin
	 *
	 * @return string
	 */
	public function usage()
	{
		ob_start();
	
		$buffer = ob_get_contents();
	
		ob_end_clean();
	
		return $buffer;
		
	}
}