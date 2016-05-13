<?php
namespace OAuth2Yii\Provider;

use \Yii;
use \CComponent;

class Generic extends Provider
{
    public $authorizationUrl;

    public $tokenUrl;
	
	public $resourceUrl;
	
	public $jwt;
	 
	public $userProfileUrl;

    /**
     * Gets the authorization URL
     *
     * @throws \CException if the URL is missing
     * @return string the authorization URL of this provider
     */
    public function getAuthorizationUrl()
    {
        if(empty($this->authorizationUrl)) {
            throw new \CException('No authorization URL configured');
        }
        return $this->authorizationUrl;
    }

    /**
     * Gets the token URL
     *
     * @throws \CException if the URL is missing
     * @return string the token URL of this provider
     */
    public function getTokenUrl()
    {
        if(empty($this->tokenUrl)) {
            throw new \CException('No token URL configured');
        }
        return $this->tokenUrl;
    }
	
	// public function getTokenJwtUrl()
    // {
        // return $this->tokenJwtUrl;
    // }
	
	public function getUserProfileUrl(){
		return $this->UserProfileUrl;
	}
	
	 public function getAuth($provider, $continues=false)
	//public function actionAuth($continues=false)
	{															echo "CONTINUES-------->{$continues}";	
		// $clients = Yii::app()->oauth2client; 
		// $provider= $clients->getProvider($provider);	dump($provider);	echo  $provider->tokenUrl;
										
		$request = Yii::app()->request;	//echo $request->queryString;
		if( ! isset($_GET['code']) ){
			
			$direct = Yii::app()->createUrl('authorize/authorize') . "?" .  $request->queryString; 	echo "<br>DIRECT--->{$direct}";
			//$this->redirect( $direct );
			//header("location: $direct");
		}
		// else{
			// dump($request->queryString ); //die();
			// $res = $this->actionRequest_token($_GET['code'], $provider, true); 	dump($res);
			// //$this->redirect( $direct );
		// }
			
		// if( isset($res) && $res['access_token']){
		// // $user = Yii::app()->oauth2->userInfo();		//dump($res);
			// // echo  CJSON::encode($user->getParameters());
			// $http = new GuzzleClient();
			// $request = $http->get("http://localhost/yii-oauth2/client_requestResource/userInfo?access_token=$res[access_token]");
			// $user = $http->send($request);
			// //dump($res->getQuery());
			// echo $user->getBody();
			// echo "<script>window.opener.location.reload(); window.close(); </script>";
			// $json = CJSON::decode($res->getBody());		dump($json);
		// }
	}
}
