<?php
/**
 * Created by PhpStorm.
 * User: TALOM BOUOPDA ARNAUD
 * Date: 13/08/2020
 * Time: 13:12
 */

namespace App\Entity;


class Sms
{
	private $url = [
		'credit' => "https://sms.ekotech.cm/api/v2/{api_key}/credit/2",
		'sms' => "https://sms.ekotech.cm/api/v2/{api_key}/sms/simple",
		'histories' => "https://sms.ekotech.cm/api/v2/{api_ky}/logs/history"
	];

	/*
    * Votre clés d'api généré dans le site cleansms
    */
	public $authorisation ='110|I5RBu9q1599218298wy2zTsI';

	/*
    * Votre Email dans le site cleansms
    */
	public $login = 'ktcdev';

	private $header = [
		"content-type: application/json",
		"accept: application/json",
	];
	/**
	 * @var bool
	 */
	private $local;

	/**
	 * CleansmsApi constructor.
	 * @param String $login
	 * @param String $authorisation
	 * @param bool $local
	 * @throws \Exception
	 */
	public function init($login, $authorisation)
	{
		$this->authorisation = $authorisation;
		$this->login = $login;
		$this->header[] = "authorization: Basic {$this->authorisation}";
	}

	// public function getCredit()
	// {
	// 	$url = str_replace("{api_key}", urlencode($this->authorisation), $this->url['credit']);
	// 	return $this->exec(array(), ($this->local ? $this->url_local['credit'] : $url));
	// }




	public function getCredit()
	{
		//dd(extension_loaded('curl'));

		$curl = curl_init();
		
		curl_setopt_array($curl, array(
		CURLOPT_URL => "https://api-public.ekotech.cm/balance?username=".$this->login."&password=".$this->authorisation,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		return $err;
		} else {
		return $response;
		}
	}




	/**
	 * Permet d'envoyer un sms vers un ou plusieurs numeros
	 * @param String $message
	 * @param string $to liste des numéros au format (+237*****,+245*****,...)
	 * @throws \Exception
	 */
	public function sendSms($message, $to,$sender)
	{
		$curl = curl_init();
		// $to=str_replace('+', '',$to);
		// $oldnumber ="+".$to;
		$oldnumber=str_replace(' ', '+',$to);
		$datainput = array("username"=>$this->login, "password"=>$this->authorisation, "msisdn"=>$oldnumber,"msg"=>$message,"sender"=>$sender);
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api-public.ekotech.cm/messages",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $datainput,
		  ));
		return curl_exec($curl);
	}

	/**
	 * Execute une requette Curl
	 * @param $data
	 * @param $url
	 * @return mixed
	 */
	private function exec($data, $url)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url.'?'.http_build_query($data),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING=>"",
			CURLOPT_MAXREDIRS=>10,
			CURLOPT_TIMEOUT=>30,
			CURLOPT_HTTP_VERSION=>CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			//CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array(
				'content-type: ' . "application/json",
				'accept: ' . "application/json"
			),
			CURLOPT_SSL_VERIFYHOST=>0,
			CURLOPT_SSL_VERIFYPEER=>0,
		));

		$result = curl_exec($curl);
		$error = curl_error($curl);

		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$isSuccess = 200 <= $code && $code < 300;
		curl_close($curl);


		if (!$isSuccess) {
			return false;
		}else{
			return $result;
		}
	}

	/**
	 * @param null|string $authorisation
	 */
	public function setAuthorisation($authorisation)
	{
		$this->authorisation = $authorisation;
	}
}
