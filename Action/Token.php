<?php

namespace OAuth2Yii\Action;

use \OAuth2Yii\Component\ServerComponent;

use \OAuth2\GrantType;
use \OAuth2\Request;

use \Yii;
use \CAction;
use \CException;
use \CWebLogRoute;
use \CProfileLogRoute;

class Token extends CAction
{
    /**
     * @var string name of the OAuth2Yii application component. Default is 'oauth2'
     */
    public $oauth2Component = 'oauth2';

    /**
     * Runs the action.
     *
     *
     * @throws \CException if oauth is improperly configured.
     */
    public function run()
    {	
		if(!Yii::app()->getModule('oauth2')->hasComponent($this->oauth2Component)) {
            throw new CException("Could not find OAuth2Yii/Server component '{$this->oauth2Component}'");
        }

        $oauth2     =  Yii::app()->getModule('oauth2')->getComponent($this->oauth2Component); /* @var \OAuth2Yii\Component\ServerComponent $oauth2 */
        $server     = $oauth2->getServer();			//echo "server-->"; dump($server);

		  if(!$oauth2->getCanGrant()) {
            throw new CException("No grant types enabled see config/main");
        }
		
		//$storage = new \OAuth2\Storage\Pdo(array('dsn' =>'mysql:host=localhost;dbname=oauth2', 'username' =>'root', 'password' =>''));	//dump($storage);
		//dump($oauth2->getStorage('access_token'));   
		//dump($storage);
		
		# ADD GRANT-TYPE
		# 1. ClientCredentials
		if($oauth2->enableClientCredentials) {
            $clientStorage = $oauth2->getStorage(ServerComponent::STORAGE_CLIENT_CREDENTIALS);		//echo "storage-->"; print_r($clientStorage);
            $server->addGrantType(new GrantType\ClientCredentials($clientStorage));
        }
		
		# 2. UserCredentials
        if($oauth2->enableUserCredentials) {	
			#$server->addGrantType(new GrantType\RefreshToken($storage));
            $userStorage = $oauth2->getStorage(ServerComponent::STORAGE_USER_CREDENTIALS);
            $server->addGrantType(new GrantType\UserCredentials($userStorage));
			
            $refreshStorage = $oauth2->getStorage(ServerComponent::STORAGE_REFRESH_TOKEN);
            $server->addGrantType(new GrantType\RefreshToken($refreshStorage));
        }
		
		# 3. AuthorizationCode
		 if($oauth2->enableAuthorization) {	
            $authorizationStorage = $oauth2->getStorage(ServerComponent::STORAGE_AUTHORIZATION_CODE);	//dump( $authorizationStorage);
            $server->addGrantType(new GrantType\AuthorizationCode($authorizationStorage));	//echo "server-->"; dump($server);
        }

		# 1. Implicit ?
		 // if($oauth2->enableImplicit) {	ECHO "ALLOW IMPLICIT";
		 
		 // }
		 
		$request = Request::createFromGlobals();							//print_r($request);		//print_r($server->handleTokenRequest($request));		//print_r($request);
        $server->handleTokenRequest($request)->send();
    }
}
