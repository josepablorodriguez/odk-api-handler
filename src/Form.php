<?php
declare(strict_types = 1);
/**
 * The OdkApiHandler Form object.
 */

namespace OdkApiHandler;

use chillerlan\QRCode\QRCode;
//use chillerlan\QRCode\QROptions;
use JsonException;

require_once("OdkCRUD.php");

class Form extends OdkCRUD
{
	/**
	 * Form Object.
	 *
	 * ```
	 * $form = new Form([
	 * 		'baseUrl' => 'https://your.domain.com',
	 * 		'token' => 'access_token_obtained_after_logging_in',
	 * ]);
	 * ```
	 *
	 * @param array $config configuration data to set the Form Handler Object.
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
		$this->placeholder["objectId"] = "XML_FORM_ID%";
	}

	//region METHODS
	//region PUBLIC

	/**
	 * Requests a new Object creation to the defined ODK Central server.
	 *
	 * @param array $requestData The data array to POST and create a new
	 * object in the defined ODK Central server.
	 * @return array
	 */
	public function create(array $requestData): array{
		if(count($requestData) == 0) return [];

		$create_endpoint =
			str_replace(
				'%PROJECT_ID%',
				$requestData['project_id'],
				$this->endpoints["create"]["url"]
			);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $create_endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $requestData["form"]);

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/xml",
			"Authorization: Bearer " . $this->token
		));

		$this->response = json_decode(curl_exec($curl), true);

		curl_close($curl);

		return $this->response;
	}

	/**
	 * Requests to set an already created XML-Form as "Draft" to the defined ODK Central server.
	 *
	 * @param array $requestData The data array to POST and set the XML-Form
	 *  as draft in the defined ODK Central server.
	 * @return array
	 */
	public function draft(array $requestData): array{
		if(count($requestData) == 0) return [];

		$endpoint =
			str_replace(
				['%XML_FORM_ID%', '%PROJECT_ID%'],
				[ $requestData['xml_form_id'], $requestData['project_id'], ],
				$this->endpoints["draft"]["url"]
			);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $requestData["form"]);

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/xml",
			"Authorization: Bearer " . $this->token
		));

		$this->response = json_decode(curl_exec($curl), true);

		curl_close($curl);

		return $this->response;
	}

	/**
	 * Gets the Form with a specific ID, of the specified project ID,
	 * from the ODK Central server.
	 *
	 * @param array $requestData Containing the Project ID and the Form ID
	 * @return array
	 */
	public function getById(array $requestData): array{
		if(count($requestData) == 0) return [];

		$endpoint =
			str_replace(
				['%XML_FORM_ID%', '%PROJECT_ID%'],
				[ $requestData['xml_form_id'], $requestData['project_id'], ],
				$this->endpoints["details"]["url"]
			);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->token
		));

		$this->response = json_decode(curl_exec($curl), true);
		$this->response['enketoPreview'] = str_replace(
			"%ENKETO_ID%",
			$this->response["enketoId"],
			$this->endpoints["enketo"]
		);

		curl_close($curl);

		return $this->response;
	}

	/**
	 * Gets a string representing the Form QR code with a specific ID, to pass as the "src"
	 * attribute of an image HTML tag.
	 *
	 * @param array $requestData Containing the FORM id, PROJECT id, App-User token
	 * @return array
	 * @throws JsonException
	 */
	public function getDraftQRCode(array $requestData): array{
		if(count($requestData) == 0) return [];

		$endpoint =
			str_replace(
				['%XML_FORM_ID%', '%PROJECT_ID%', '%TOKEN%'],
				[$requestData['xml_form_id'], $requestData['project_id'], $requestData['token']],
				$this->endpoints["draftQrCode"]["url"]
			);
		$form_list_url =
			str_replace(
				'%PROJECT_ID%',
				$requestData['project_id'],
				$this->endpoints["all"]["url"]
			);

		$data = [
			"general" => [
				//server
				"protocol" => "odk_default",
				"server_url" => $endpoint,
				//user
				"navigation" => "swipe_buttons",
				//"formlist_url" => $form_list_url,
				//form management
				"form_update_mode" => "match_exactly",
				"constraint_behavior" => "on_finalize",
				"autosend" => "wifi_only",
				"instance_sync" => true,
			],
			"admin" => [
				"edit_saved" => false,
				"instance_form_sync" => true,
				"automatic_update" => true,
			],
			"project" => [
				"name" => "[DRAFT] " . $requestData["xml_form_title"],
				"icon" => "📝",
				"color" => "#FF0000",
			],
		];

		$data = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
		$client_settings = base64_encode(zlib_encode($data, ZLIB_ENCODING_DEFLATE));

		$qr_source = (new QRCode)->render($client_settings);

		$this->response = [
			"qrSource" => $qr_source,
		];

		return $this->response;
	}

	/**
	 * Gets the Form QR code with a specific ID,
	 * from the ODK Central server.
	 *
	 * @param array $requestData Containing the Form ID
	 * @return array
	 * @throws JsonException
	 */
	public function getQRCode(array $requestData): array{
		if(count($requestData) == 0) return [];

		$endpoint =
			str_replace(
				['%XML_FORM_ID%', '%PROJECT_ID%', '%TOKEN%'],
				[$requestData['xml_form_id'], $requestData['project_id'], $requestData['token']],
				$this->endpoints["qrCode"]["url"]
			);

		$data = [
			"general" => [
				//server
				"protocol" => "odk_default",
				"server_url" => $endpoint,
				//user
				"navigation" => "swipe_buttons",
				//"formlist_url" => $form_list_url,
				//form management
				"form_update_mode" => "match_exactly",
				"constraint_behavior" => "on_finalize",
				"autosend" => "wifi_only",
				"instance_sync" => true,
			],
			"admin" => [
				"edit_saved" => false,
				"instance_form_sync" => true,
				"automatic_update" => true,
			],
			"project" => [
				"name" => $requestData["project_name"],
				"icon" => "📝",
				"color" => "#006863",
			],
		];

		$data = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
		$client_settings = base64_encode(zlib_encode($data, ZLIB_ENCODING_DEFLATE));

		$qr_source = (new QRCode)->render($client_settings);

		$this->response = [
			"qrSource" => $qr_source,
		];

		return $this->response;
	}

	public function returnFromQR(string $data){
		$value = (new QRCode)->readFromBlob($data);

		return $value;

	}
	//endregion
	//region PRIVATE

	/**
	 * Sets the Form's related endpoints.
	 *
	 * @param string $base_url The base URL of hosted ODK Central server
	 * @return void
	 */
	private function setEndpoints(string $base_url){
		$this->endpoints["create"] = [
			"url" => $base_url . "/v1/projects/%PROJECT_ID%/forms?ignoreWarnings=false&publish=false",
			"method" => "post",
		];
		$this->endpoints["draft"] = [
			"url" => $base_url . "/v1/projects/%PROJECT_ID%/forms/%XML_FORM_ID%/draft?ignoreWarnings=false",
			"method" => "post",
		];
		$this->endpoints["draftQrCode"] = [
			"url" => $base_url . "/v1/test/%TOKEN%/projects/%PROJECT_ID%/forms/%XML_FORM_ID%/draft",
			"method" => "post",
		];
		$this->endpoints["qrCode"] = [
			"url" => $base_url . "/v1/key/%TOKEN%/projects/%PROJECT_ID%",
			"method" => "post",
		];
		$this->endpoints["enketo"] = [
			"url" => $base_url . "/-/preview/%ENKETO_ID%",
			"method" => "get",
		];
		$this->endpoints["details"] = [
			"url" => $base_url . "/v1/projects/%PROJECT_ID%/forms/%XML_FORM_ID%",
			"method" => "get",
		];
		$this->endpoints["all"] = [
			"url" => $base_url . "/v1/projects/%PROJECT_ID%/forms",
			"method" => "get",
		];
	}

	//endregion
	//endregion
}