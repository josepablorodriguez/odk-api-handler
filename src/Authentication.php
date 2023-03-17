<?php
declare(strict_types = 1);
/**
 * The OdkApiHandler Authentication object.
 */

namespace OdkApiHandler;

require_once("OdkCRUD.php");

class Authentication extends OdkCRUD
{
	/**
	 * The authentication type.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Authentication Object.
	 *
	 * ```
	 * $authentication = new Authentication([
	 * 		'authentication_type' => 'session'|'https_basic'|'app_user',
	 * 		'baseUrl' => 'https://your.domain.com',
	 * 		'token => null|'access_token_obtained_after_logging_in'
	 * ]);
	 * ```
	 *
	 * @param array $config configuration data to set the Authentication Object.
	 * @return void
	 * @link https://odkapihandler.portafolio.dev
	 * @codeCoverageIgnore
	 */
	public function __construct(array $config)
	{
		$this->type = strtolower($config["authentication_type"]);
		switch($this->type){
			case "https_basic": { break;}
			case "app_user": { break;}
			case "session": {
				if(array_key_exists("baseUrl", $config))
					$this->setEndpoints($config["baseUrl"]);
			}
		}
		if(array_key_exists("token", $config) and null !== $config["token"])
			$this->token = $config["token"];
	}

	//region METHODS
	//region PUBLIC

	/**
	 * Requests a log-in to the defined ODK Central server.
	 *
	 * @param array $credentials The array containing "email" and "password".
	 * @return void
	 */
	public function logIn(array $credentials){
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->endpoints["logIn"]["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_POST, TRUE);

		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($credentials));

		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

		$this->response = json_decode(curl_exec($curl), true);

		curl_close($curl);

		if(null != $this->response and array_key_exists("token", $this->response)) {
			$this->token = $this->response["token"];
			$this->csrf = $this->response["csrf"];
			$this->endpoints["logOut"]["url"] =
				str_replace("%TOKEN%", $this->token, $this->endpoints["logOut"]["url"]);
		}
		else{
			$this->token = "";
		}
	}

	/**
	 * Requests a log-out to the defined ODK Central server.
	 *
	 * @return void
	 */
	public function logOut(){
		if(null != $this->token and strlen($this->token) > 0)
			$this->endpoints["logOut"]["url"] =
				str_replace("%TOKEN%", $this->token, $this->endpoints["logOut"]["url"]);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->endpoints["logOut"]["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->token
		));

		$this->response = json_decode(curl_exec($curl), true);

		curl_close($curl);
	}

	/**
	 * Requests to a none existent endpoint to the defined ODK Central server.
	 * if error code 404.1 is received, user is logged in.
	 *
	 * @return bool
	 */
	public function check(): bool{
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->endpoints["check"]["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->token
		));

		$response = json_decode(curl_exec($curl), true);

		curl_close($curl);

		if(array_key_exists("message", $response) and array_key_exists("code", $response)){
			if($response["code"] == "404.1") return true;
		}
		return false;
	}

	/**
	 * Gets the "token" of the log-in "Request".
	 *
	 * @return string
	 */
	public function getToken(): string{
		return $this->token;
	}

	/**
	 * Gets the "csrf" of the log-in "Request".
	 *
	 * @return string
	 */
	public function getCsrf(): string{
		return $this->csrf;
	}

	//endregion
	//region PRIVATE

	/**
	 * Sets the Authentication's related endpoints.
	 *
	 * @param string $base_url The base URL of hosted ODK Central server
	 * @return void
	 */
	private function setEndpoints(string $base_url){
		$this->endpoints["logIn"] = [
			"url" => $base_url . "/v1/sessions",
			"method" => "post",
		];
		$this->endpoints["logOut"] = [
			"url" => $base_url . "/v1/sessions/%TOKEN%",
			"method" => "del",
		];
		$this->endpoints["check"] = [
			"url" => $base_url . "/v1/check",
			"method" => "get",
		];
	}

	//endregion
	//endregion
}