<?php
declare(strict_types = 1);
/**
 * The OdkApiHandler authentication object.
 */

namespace App\Ihub\odkApiHandler\src;

class Authentication{
	/**
	 * The Authentication credentials.
	 *
	 * @var array
	 */
	private $credentials = [
		"email" => null,
		"password" => null,
	];
	/**
	 * The endpoint URL.
	 *
	 * @var array
	 */
	private $endpoints = [
		"logIn" => null,
		"logOut" => null,
	];
	/**
	 * The response of the logIn request.
	 *
	 * @var array
	 */
	private $response = [
		"token" => null,
		"csrf" => null,
		"expiresAt" => null,
		"createdAt" => null,
	];
	/**
	 * The obtained token after logging In.
	 *
	 * @var string
	 */
	private $token = "";
	/**
	 * The authentication type.
	 *
	 * @var string
	 */
	private $type = "";

	/**
	 * Authentication Object.
	 *
	 * ```
	 * $authentication = new Authentication('session'|'https_basic'|'app_user');
	 * ```
	 *
	 * @param string $authentication_type Authentication Type
	 * @return Authentication
	 * @link https://odkapihandler.portafolio.dev
	 * @codeCoverageIgnore
	 */
	public function __construct(string $authentication_type, string $base_url)
	{
		$this->type = strtolower($authentication_type);
		switch($this->type){
			case "https_basic": { break;}
			case "app_user": { break;}
			case "session": {
				$this->endpoints["logIn"] = [
					"url" => str_replace("%BASEURL%", $base_url, "%BASEURL%/v1/sessions"),
					"method" => "post",
				];
				$this->endpoints["logOut"] = [
					"url" => str_replace("%BASEURL%", $base_url, "%BASEURL%/v1/sessions/%TOKEN%"),
					"method" => "del",
				];
			}
		}
	}

	public function logIn(array $credentials){
		$this->credentials = $credentials;
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->endpoints["logIn"]["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_POST, TRUE);

		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($credentials));

		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

		$response = json_decode(curl_exec($curl), true);

		$this->response = $response;

		curl_close($curl);

		$this->endpoints["logOut"]["url"] =
			str_replace("%TOKEN%", $this->token, $this->endpoints["logOut"]["url"]);
	}
	public function logOut(){
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->endpoints["logOut"]["url"] . $this->response["token"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->response["token"]
		));

		$response = json_decode(curl_exec($curl), true);

		$this->response = $response;

		curl_close($curl);
	}
	public function getResponse(): array
	{
		return $this->response;
	}
}