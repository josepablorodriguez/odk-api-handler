<?php

namespace OdkApiHandler;

class OdkCRUD
{
	/**
	 * The endpoint URLs.
	 *
	 * @var array
	 */
	protected $endpoints;
	/**
	 * The placeholder replace in endpoint's URLs entries.
	 *
	 * @var array
	 */
	protected $placeholder;
	/**
	 * The "Response" for the Object Handler's "Requests".
	 *
	 * @var array
	 */
	protected $response;
	/**
	 * The User authentication token.
	 *
	 * @var string
	 */
	protected $token;
	/**
	 * The User authentication csrf.
	 *
	 * @var string
	 */
	protected $csrf;

	/**
	 * Requests a new Object creation to the defined ODK Central server.
	 *
	 * @param array $requestData The data array to POST and create a new
	 * object in the defined ODK Central server.
	 * @return void
	 */
	public function create(array $requestData){
		if(count($requestData) == 0) return;

		$del_endpoint =
			str_replace($this->placeholder["objectId"], $object_id, $this->endpoints["delete"]["url"]);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->endpoints["create"]["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestData));

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Authorization: Bearer " . $this->token
		));

		$this->response = json_decode(curl_exec($curl), true);

		curl_close($curl);
	}
	/**
	 * Requests to delete a User to the defined ODK Central server.
	 *
	 * @param int|string $user_id The ID of the Project to be deleted.
	 * @return void
	 */
	public function delete($object_id){
		$curl = curl_init();
		$del_endpoint =
			str_replace($this->placeholder["objectId"], $object_id, $this->endpoints["delete"]["url"]);

		curl_setopt($curl, CURLOPT_URL, $del_endpoint);
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
	 * Requests a list of all Objects the authenticated user has access to,
	 * at the defined ODK Central server.
	 *
	 * @return array
	 */
	public function getAll(): array{
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->endpoints["all"]["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->token,
			"X-Extended-Metadata: true"
		));

		$this->response = json_decode(curl_exec($curl), true);

		curl_close($curl);

		return $this->response;
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
}