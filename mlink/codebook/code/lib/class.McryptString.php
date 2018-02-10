<?php
class McryptString
{
	public $td,$iv,$ks;
	
	public $algorithm;
	public $algorithm_directory;
	public $mode = MCRYPT_MODE_ECB;
	public $mode_directory;

	public $algorithm_string_list = array("cast-128","gost","rijndael-128","twofish","arcfour","cast-256","loki97","rijndael-192","saferplus","wake","blowfish-compat","des","rijndael-256","xtea","enigma","rc2","blowfish","tripledes");
	//public $algorithm_list = array(MCRYPT_BLOWFISH,MCRYPT_DES,MCRYPT_TripleDES,MCRYPT_ThreeWAY,MCRYPT_GOST,MCRYPT_CRYPT,MCRYPT_DES_COMPAT,MCRYPT_SAFER64,MCRYPT_SAFER128,MCRYPT_CAST128,MCRYPT_TEAN,MCRYPT_RC2,MCRYPT_TWOFISH,MCRYPT_TWOFISH128,MCRYPT_TWOFISH192,MCRYPT_TWOFISH256,MCRYPT_RC6,MCRYPT_IDEA);
	public $mode_list = array(MCRYPT_MODE_CBC,MCRYPT_MODE_OFB,MCRYPT_MODE_CFB,MCRYPT_MODE_ECB);

	public function __construct($algorithm=MCRYPT_BLOWFISH,$mode=MCRYPT_MODE_ECB,$algorithm_directory="",$mode_directory="")
	{
		$this->algorithm = $algorithm;
		$this->algorithm_directory = $algorithm_directory;
		$this->mode = $mode;
		$this->mode_directory = $mode_directory;
		$this->td = mcrypt_module_open($this->algorithm,$this->algorithm_directory,$this->mode,$this->mode_directory);
		if($this->td === false) throw new Exception ("mcrypt_module_open " . $this->algorithm . "failed");
		$size = mcrypt_enc_get_iv_size($this->td);
		$this->iv = mcrypt_create_iv($size, MCRYPT_RAND);//MCRYPT_RAND,MCRYPT_DEV_RANDOM,MCRYPT_DEV_URANDOM
		$this->ks = mcrypt_enc_get_key_size($this->td);
	}

	public function __destruct()
	{
		try{
			if($this->td) mcrypt_module_close($this->td);
		}
		catch (Exception $e){
		}
	}

	public function encode($string,$salt)
	{
		$key = substr(md5($salt), 0, $this->ks);
		mcrypt_generic_init($this->td, $key, $this->iv);
		$encrypted = mcrypt_generic($this->td, $string);
		mcrypt_generic_deinit($this->td);
		return $encrypted;
	}

	public function decode($string,$salt)
	{
		$decrypted = "";
		try{
			$key = substr(md5($salt), 0, $this->ks);
			mcrypt_generic_init($this->td, $key, $this->iv);
			$decrypted = rtrim(mdecrypt_generic($this->td, $string));
			mcrypt_generic_deinit($this->td);
			return $decrypted;
		}
		catch (Exception $e){
		}
		return $decrypted;
        }

	function generate_rand_salt()
	{
		return md5(uniqid(rand(),true));
	}

	function encode_for_transfer($string,$public_salt,$rand_salt="")
	{
		if(!$rand_salt) $rand_salt = $this->generate_rand_salt();
		$key = $public_salt . $rand_salt;
		$encoded = $this->encode($string,$key);
		return array(
			"EncodedString" => base64_encode($encoded),
			"RandSalt" => $rand_salt,
		);
	}

	function decode_for_transfer($string,$public_salt,$rand_salt)
	{
		$string = @base64_decode($string);
		if(!$string) return false;
		$key = $public_salt . $rand_salt;
		$decoded = $this->decode($string,$key);
		if(!$decoded) return false;
		return $decoded;
	}
}
?>