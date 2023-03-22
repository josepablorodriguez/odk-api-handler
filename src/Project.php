<?php
declare(strict_types = 1);
/**
 * The OdkApiHandler Projects object.
 */

namespace OdkApiHandler;

require_once("OdkCRUD.php");

class Project extends OdkCRUD
{
	/**
	 * Project Object.
	 *
	 * ```
	 * $project = new Project([
	 * 		'baseUrl' => 'https://your.domain.com',
	 * 		'token' => 'access_token_obtained_after_logging_in',
	 * ]);
	 * ```
	 *
	 * @param array $config configuration data to set the Project Handler Object.
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
		$this->placeholder["objectId"] = "%PROJECT_ID%";
	}

	//region METHODS
	//region PUBLIC

	/**
	 * Set a User to a specific Project at the defined ODK Central server.
	 *
	 * @return void
	 */
	public function userAssignment(array $params){
		if(count($params) > 0)
			$user_project_assignment_endpoint = str_replace(
				["%PROJECT_ID%", "%ROLE_ID%", "%ACTOR_ID%"],
				[$params["projectId"], $params["roleId"], $params["actorId"]],
				$this->endpoints["userProjectAssignment"]["url"]
			);
		else
			$user_project_assignment_endpoint = $this->endpoints["userProjectAssignment"]["url"];

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $user_project_assignment_endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_POST, TRUE);

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->token
		));

		$this->response = json_decode(curl_exec($curl), true);

		curl_close($curl);
	}

	/**
	 * Gets the list of Projects created or edited by this instance,
	 * meaning it does NOT return the actual list of Projects from
	 * the ODK Central server.
	 *
	 * @param string $name The name of the Project
	 * @return array
	 */
	public function getByName(string $name): array{
		$all_projects = $this->getAll();
		if(array_key_exists("code", $all_projects))
			$this->response = $all_projects;
		else if(count($all_projects) > 0 and array_key_exists("name", $all_projects[0]))
			foreach ($all_projects as $key => $project) {
				if($project["name"] == $name)
					return $project;
			}
		return [];
	}

	/**
	 * Gets the list of Projects created or edited by this instance,
	 * meaning it does NOT return the actual list of Projects from
	 * the ODK Central server.
	 *
	 * @param int $id The id of the Project
	 * @return array
	 */
	public function getById(int $id): array{
		$all_projects = $this->getAll();
		if(array_key_exists("code", $all_projects))
			$this->response = $all_projects;
		else if(count($all_projects) > 0 and array_key_exists("id", $all_projects[0]))
			foreach ($all_projects as $key => $project) {
				if($project["id"] == $id)
					return $project;
			}
		return [];
	}

	/**
	 * Requests a specific APP-USER previously assigned to a PROJECT
	 * at the defined ODK Central server.
	 * App-Users are NOT standard ODK USERS, these are assigned per
	 * PROJECT, have their own id per PROJECT, and a specific TOKEN
	 * that changes per PROJECT.
	 * An App-User is intended for access a PROJECT and its FORMS
	 * via ODK-COLLECT mobile app or via web.
	 *
	 * @param array $requestData The name of the USER and PROJECT id.
	 * @return array
	 */
	public function getAppUserByName(array $requestData): array{
		if(count($requestData) == 0) return [];

		$endpoint =
			str_replace(
				'%PROJECT_ID%',
				$requestData['project_id'],
				$this->endpoints["AppUsers"]["url"]
			);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->token
		));

		$this->response = json_decode(curl_exec($curl), true);

		curl_close($curl);

		foreach($this->response as $index => $user){
			if($user['displayName'] === $requestData['user_name'])
				$this->response =  $user;
		}

		return $this->response;
	}

	/**
	 * Requests all APP-USERS previously assigned to a PROJECT
	 * at the defined ODK Central server.
	 * App-Users are NOT standard ODK USERS, these are assigned per
	 * PROJECT, have their own id per PROJECT, and a specific TOKEN
	 * that changes per PROJECT.
	 * An App-User is intended for access a PROJECT and its FORMS
	 * via ODK-COLLECT mobile app or via web.
	 *
	 * @param string $project_id The PROJECT id.
	 * @return array
	 */
	public function getAppUsers(string $project_id): array{
		if(null == $project_id) return [];

		$endpoint =
			str_replace(
				'%PROJECT_ID%',
				$project_id,
				$this->endpoints["AppUsers"]["url"]
			);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $endpoint);
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
	 * Sets the Project's related endpoints.
	 *
	 * @param string $base_url The base URL of hosted ODK Central server
	 * @return void
	 */
	private function setEndpoints(string $base_url){
		$this->endpoints["create"] = [
			"url" => $base_url . "/v1/projects",
			"method" => "post",
		];
		$this->endpoints["delete"] = [
			"url" => $base_url . "/v1/projects/%PROJECT_ID%",
			"method" => "del",
		];
		$this->endpoints["all"] = [
			"url" => $base_url . "/v1/projects",
			"method" => "get",
		];
		$this->endpoints["userProjectAssignment"] = [
			"url" => $base_url . "/v1/projects/%PROJECT_ID%/assignments/%ROLE_ID%/%ACTOR_ID%",
			"method" => "post",
		];
		$this->endpoints["AppUsers"] = [
			"url" => $base_url . "/v1/projects/%PROJECT_ID%/app-users",
			"method" => "get",
		];
	}

	//endregion
	//endregion
}