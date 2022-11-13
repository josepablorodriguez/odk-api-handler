<?php
declare(strict_types = 1);
/**
 * The OdkApiHandler Projects object.
 */

namespace App\Ihub\odkApiHandler\src;

class Project
{
	/**
	 * The endpoint URLs.
	 *
	 * @var array
	 */
	private $endpoints;
	/**
	 * The stored projects to manage.
	 *
	 * @var array
	 */
	private $projects;
	/**
	 * The "Response" for the Project Handler's "Requests".
	 *
	 * @var array
	 */
	private $response;
	/**
	 * The "token" of the authenticated user, necessary for "Requests".
	 *
	 * @var string
	 */
	private $token;

	public function __construct(array $config)
	{
		if(null == $config) return;
		if(array_key_exists("token", $config))
			$this->token = $config["token"];

		if(array_key_exists("baseUrl", $config)){
			$this->endpoints["create"] = [
				"url" => $config["baseUrl"] . "/v1/projects",
				"method" => "post",
			];
			$this->endpoints["delete"] = [
				"url" => $config["baseUrl"] . "/v1/projects/%PROJECT_ID%",
				"method" => "del",
			];
		}
	}

	//region METHODS
	//region PUBLIC
	/**
	 * Requests a new Project creation to the defined ODK Central server.
	 *
	 * @param string $name The "name" for the new Project.
	 * @return void
	 */
	public function create(string $name){
		if(strlen($name) == 0) return;
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->endpoints["create"]["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_POST, TRUE);

		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(["name" => $name]));

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Authorization: Bearer " . $this->token
		));

		$this->response = json_decode(curl_exec($curl), true);

		if(array_key_exists("name", $this->response))
			$this->projects[$this->response["name"]] = $this->response;

		curl_close($curl);
	}

	/**
	 * Requests to delete a Project to the defined ODK Central server.
	 *
	 * @param int|string $project_id The ID of the Project to be deleted.
	 * @return void
	 */
	public function delete($project_id){
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, str_replace("%PROJECT_ID%", "".$project_id, $this->endpoints["delete"]["url"]));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->token
		));
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

		$this->response = json_decode(curl_exec($curl), true);

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

	public function getProjects($name){
		return $this->projects[$name];
	}
	//endregion
	//region PRIVATE
	//endregion
	//endregion
}
