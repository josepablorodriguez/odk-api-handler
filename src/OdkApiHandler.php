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
	/**
	 * The OdkApiHandler Project Handler object.
	 *
	 * @var Project
	 */
	private $project;
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
	// region PUBLIC

	/**
	 * Gets the "Authentication" handler Object.
	 *
	 * @return Authentication
	 */
	public function authentication(): Authentication{
		return $this->authentication;
	}

	/**
	 * Gets the "Project" handler Object.
	 *
	 * @return Project
	 */
	public function project(): Project{
		if(null == $this->project){
			$this->project = new Project([
				"baseUrl" => $this->base_url,
				"token" => $this->authentication->getToken(),
			]);
		}

		return $this->project;
	}

	//endregion
	// region PRIVATE
	private function setToken(string $token){

	}
	//endregion
	//endregion
}
