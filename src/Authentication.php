<?php
declare(strict_types = 1);
/**
 * The OdkApiHandler Authentication object.
 */

namespace App\Ihub\odkApiHandler\src;

class Authentication
{
	/**
	 * The endpoint URLs.
	 *
	 * @var array
	 */
	private $endpoints;
	/**
	 * The "Response" for the Authentication Handler's "Requests".
	 *
	 * @var array
	 */
	private $response;
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
	 * $authentication = new Authentication('session'|'https_basic'|'app_user');
	 * ```
	 *
	 * @param string $authentication_type Authentication Type
	 * @return void
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
					"url" => $base_url . "/v1/sessions",
					"method" => "post",
				];
				$this->endpoints["logOut"] = [
					"url" => $base_url . "/v1/sessions/%TOKEN%",
					"method" => "del",
				];
			}
		}
	}

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

		if(array_key_exists("token", $this->response))
			$this->endpoints["logOut"]["url"] =
				str_replace("%TOKEN%", $this->response["token"], $this->endpoints["logOut"]["url"]);
	}

	/**
	 * Requests a log-out to the defined ODK Central server.
	 *
	 * @return void
	 */
	public function logOut(){
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->endpoints["logOut"]["url"]);
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

	/**
	 * Gets the "Response" of the last "Request".
	 *
	 * @return array
	 */
	public function getResponse(): array
	{
		return $this->response;
	}

	/**
	 * Gets the "token" of the log-in "Request".
	 *
	 * @return string
	 */
	public function getToken(): string{
		return $this->response["token"];
	}
}
