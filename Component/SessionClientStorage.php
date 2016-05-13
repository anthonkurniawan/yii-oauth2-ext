<?php
namespace OAuth2YII\Component;

use \Yii;

/**
 * SessionClientStorage
 *
 * This is a session based client storage for access tokens
 */
class SessionClientStorage //implements ClientStorage
{
	public function __toString()
    {
        return __class__;	//get_class($this);
    }

    /**
     * @param string $provider name of provider
     * @param string $type one of AccessToken::TYPE_(USER|CLIENT)
     * @return string the session key to use for the token
     */
    // protected function getKey($provider, $type)
	 protected function getKey($provider, $clientID)
    {
        // return '__accessToken_'.$type.'_'.$provider;
		 return "__accessToken_".$provider."_".$clientID."_".Yii::app()->user->name;
    }

    /**
     * @param string $username unique name of the user
     * @param \OAuth2Yii\Component\AccessToken $accessToken the token object to store
     */
    // public function saveToken($username, $accessToken)
    // {
        // $key = $this->getKey($accessToken->provider, $accessToken->type);
        // Yii::app()->session->add($key, $accessToken);
    // }

	 public function saveToken($provider, $clientID, $accessToken)
    {										//echo $provider . $clientID;  dump($accessToken); 
        $key = $this->getKey($provider, $clientID);			//echo $key; die();
        Yii::app()->session->add($key, $accessToken);
    }
	
    /**
     * @param string $id of the client/user. For a user this is usually Yii::app()->user->id.
     * @param string $type type of token. One of AccessToken::TYPE_(CLIENT|USER).
     * @param string $provider name of provider
     * @return null|\OAuth2Yii\Component\AccessToken the access token stored for this client/user or null if not found
     */
    // public function loadToken($id,$type,$provider)
    // {			dump($provider);
        // $key = $this->getKey($provider, $type);	//echo "key--->".$key;
        // return Yii::app()->session->itemAt($key);
    // }
	public function loadToken($provider, $clientID)
    {			
        $key = $this->getKey($provider, $clientID);			
        return Yii::app()->session->itemAt($key);
    }
	
    /**
     * @param string $id of the client/user. For a user this is usually Yii::app()->user->id.
     * @param string $type type of token. One of AccessToken::TYPE_(CLIENT|USER).
     * @param \OAuth2Yii\Component\AccessToken the new token object to store instead
     */
    public function updateToken($id, $type, $accessToken)
    {
        $key = $this->getKey($accessToken->provider, $type);
        Yii::app()->session->add($key, $accessToken);
    }

    /**
     * @param string $id of the client/user. For a user this is usually Yii::app()->user->id.
     * @param string $type type of token. One of AccessToken::TYPE_(CLIENT|USER).
     * @param string $provider name of provider
     */
    // public function deleteToken($id, $type, $provider)
    // {
        // $key = $this->getKey($provider, $type);
        // Yii::app()->session->remove($key);
    // }
	public function deleteToken($provider, $clientID)
    {
		$key = $this->getKey($provider, $clientID);			
        Yii::app()->session->remove($key);
    }
}
