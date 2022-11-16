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

namespace OdkApiHandler;

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
	 * The OdkApiHandler User Handler object.
	 *
	 * @var User
	 */
	private $user;
	/**
	 * The OdkApiHandler Project Handler object.
	 *
	 * @var Project
	 */
	private $project;
	//endregion

	/**aut
	 * OdkApiHandler Object.
	 *
	 * ```
	 * $oaHandler = new OdkApiHandler([
	 * 		'baseUrl' => 'https://your.domain.com',
	 * 		'authentication_type' => 'session'|'https_basic'|'app_user',
	 * 		'token' => null|'access_token_obtained_after_logging_in'
	 * ]);
	 * ```
	 *
	 * @param array $config configuration data to set the OdkApiHandler Object.
	 * @return void
	 * @link https://odkapihandler.portafolio.dev
	 * @codeCoverageIgnore
	 */
	public function __construct(array $config)
	{
		if(null == $config) return;

		if(array_key_exists('baseUrl', $config))
			$this->base_url = $config['baseUrl'];
		if(array_key_exists('authentication_type', $config))
			$this->authentication = new Authentication($config);
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

	/**
	 * Gets the "User" handler Object.
	 *
	 * @return User
	 */
	public function user(): User{
		if(null == $this->user){
			$this->user = new User([
				"baseUrl" => $this->base_url,
				"token" => $this->authentication->getToken(),
			]);
		}

		return $this->user;
	}

	//endregion
	// region PRIVATE
	//endregion
	//endregion
}