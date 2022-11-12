<?php
declare(strict_types = 1);
/**
 * ODK API Handler.
 *
 * The Lightweight PHP library to communicate with your ODK Central Server.
 *
 * @version 0.0.1
 * @author Jose Rodriguez
 * @package OdkApiHandler
 * @copyright Copyright 2022 OdkApiHandler Project, Jose Rodriguez.
 * @license https://opensource.org/licenses/MIT
 * @link https://odkapihandler.portafolio.dev
 */

namespace App\Ihub\odkApiHandler\src;

use Exception;
use InvalidArgumentException;

class OdkApiHandler
{
	// region PROPERTIES
	/**
	 * The base URL where your ODK Central server is hosted.
	 *
	 * @var string
	 */
	private $base_url = "";
	/**
	 * The odkApiHandler authentication object.
	 *
	 * @var Authentication
	 */
	private $authentication;
	//endregion

	public function __construct(array $config)
	{
		if(null == $config) return;

		if(array_key_exists('baseUrl', $config))
			$this->base_url = $config['baseUrl'];
		if(array_key_exists('authentication_type', $config))
			$this->authentication = new Authentication($config['authentication_type'], $this->base_url);
	}

	// region METHODS
	public function authentication(): Authentication{
		return $this->authentication;
	}
	// region PUBLIC
	//endregion
	// region PRIVATE
	private function getEndPoint(string $name): array
	{
		$endpoint = [
			"status" => "ok",
			"url" => null,
			"method" => null,
		];
		if(null == $this->base_url){
			$endpoint["status"] = "base url Not Set";
			return $endpoint;
		}

		switch($name){
			case "loggingIn": {
				$endpoint["url"] = str_replace("%BASEURL%", $this->base_url,
					$this->endpoints["authentication"]["sessionAuthentication"]["loggingIn"]);
				$endpoint["method"] = $this->endpoints["authentication"]["sessionAuthentication"]["loggingInMethod"];
				break;
			}
			case "loggingOut": {
				$endpoint["url"] = str_replace("%BASEURL%", $this->base_url,
					$this->endpoints["authentication"]["sessionAuthentication"]["loggingOut"]);
				$endpoint["method"] = $this->endpoints["authentication"]["sessionAuthentication"]["loggingOutMethod"];
			}
			default: $endpoint["status"] = "endpoint Not Found"; break;
		}

		return $endpoint;
	}
	//endregion
	//endregion

}
