<?php
class APIClientCodeBook extends APIClient
{
	public function __construct($client_id="",$client_name="",$token_salt="")
	{
		$this->base_url = "http://codebook.bwe.io/api/";
		if($client_id) $this->client_id = $client_id;
		if($client_name) $this->client_name = $client_name;
		if($token_salt) $this->token_salt = $token_salt;
	}

	public function get_aff_password($user_id,$user_name)
	{
		$params = array(
			"user_id" => $user_id,
			"user_name" => $user_name,
		);
		
		$res = $this->call("GetAffPassword",$params);
		if(is_array($res) && isset($res["EncodedString"]))
		{
			$oMcryptString = new McryptString();
			return $oMcryptString->decode_for_transfer($res["EncodedString"],$user_name,$res["RandSalt"]);
		}
		return false;
	}
}
?>