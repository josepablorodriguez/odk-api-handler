# odk-api-handler
A php library to communicate with your ODK Central server
#### Note:
Alpha version v0.0, Not ready for production

## Installation
```bash
composer require josepablorodriguez/odk-api-handler:dev-main
```
## Usage

```php
<?php
use OdkApiHandler\OdkApiHandler;

public function test(){
    $config = [
        "baseUrl" => "https://your.domain.com",
        "authentication_type" => "session"
    ];
    $user_credentials = [
        "email" => "your@email.com",
        "password" => "yourPassword"
    ];
    $odk_api_handler = new OdkApiHandler($config);
    $odk_api_handler->authentication()->logIn($user_credentials);
    $odk_api_handler->project()->getByName("Default Project");
    //:OUTPUT
    /*
     * [
     *  "id": 1,
     *  "name": "Default Project",
     *  "description": "Description of this Project to show on Central.",
     *  "keyId": 3,
     *  "archived": false
     * ]    
     * */
}
```