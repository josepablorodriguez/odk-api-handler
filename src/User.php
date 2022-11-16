<?php
declare(strict_types = 1);
/**
 * The OdkApiHandler User object.
 */

namespace OdkApiHandler;

require_once("OdkCRUD.php");

class User extends OdkCRUD
{
	/**
	 * User Object.
	 *
	 * ```
	 * $user = new User([
	 * 		'baseUrl' => 'https://your.domain.com',
	 * 		'token' => 'access_token_obtained_after_logging_in',
	 * ]);
	 * ```
	 *
	 * @param array $config configuration data to set the User Handler Object.
	 * @return void
	 * @link https://odkapihandler.portafolio.dev
	 * @codeCoverageIgnore
	 */
	public function __construct(array $config)
	{
		if(null == $config) return;
		if(array_key_exists("token", $config))
			$this->token = $config["token"];

		if(array_key_exists("baseUrl", $config)){
			$this->setEndpoints($config["baseUrl"]);
		}
		$this->placeholder["objectId"] = "%USER_ID%";
	}

	//region METHODS
	//region PUBLIC

	/**
	 * Requests a specific user at the defined ODK Central server.
	 *
	 * @param string $name The name of the User.
	 * @return array
	 */
	public function getByName(string $name): array{
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, str_replace("%USERNAME%", $name, $this->endpoints["byName"]["url"]));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->token
		));

		$this->response = json_decode(curl_exec($curl), true);

		curl_close($curl);

		return $this->response;
	}

	//endregion
	//region PRIVATE

	/**
	 * Sets the User related endpoints.
	 *
	 * @param string $base_url The base URL of hosted ODK Central server
	 * @return void
	 */
	private function setEndpoints(string $base_url){
		$this->endpoints["create"] = [
			"url" => $base_url . "/v1/users",
			"method" => "post",
		];
		$this->endpoints["delete"] = [
			"url" => $base_url . "/v1/users/%USER_ID%",
			"method" => "delete",
		];
		$this->endpoints["all"] = [
			"url" => $base_url . "/v1/users",
			"method" => "get",
		];
		$this->endpoints["byName"] = [
			"url" => $base_url . "/v1/users?q=%USERNAME%",
			"method" => "get",
		];
	}

	//endregion
	//endregion
}