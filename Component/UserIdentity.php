<?php
namespace OAuth2Yii\Component;

use \Yii;
use \User;
/**
 * UserIdentity
 *
 * This represents the identity of a user on the OAuth2 server
 */
class UserIdentity extends Identity
{
    /**
     * @return bool whether the user could be authenticated against the OAuth2 server
     */
    public function authenticate()
    {	
        $provider   = $this->getProvider();					//echo "provider -->". dump($provider);	#generic
        $client     = new HttpClient;
        $url        = $provider->getTokenUrl();					//echo "url -->".$url;
        $data   = array(
             'grant_type'    => 'password',
			//'grant_type'    => 'client_credentials',
            'username'      => $this->username,
            'password'      => $this->password,
        );
				//dump($this);
        if($this->scope) {
            $data['scope'] = $this->scope;
        }
							//dump($data);
        YII_DEBUG && Yii::trace("Requesting access token for user from $url", 'oauth2.component.useridentity');
		$response   = $client->post($url, $data, array(), $provider->clientId, $provider->clientSecret);						//echo "<br>Response-->";dump($response);
		$token      = AccessToken::parseResponse($response, $provider, $this);			//echo "<br>token-->"; dump($token);

        if($token===null) {
            YII_DEBUG && Yii::trace('Access token request for user failed: '.$response, 'oauth2.component.useridentity');
            return false;
        } else {
            YII_DEBUG && Yii::trace(
                sprintf("Received user access token: %s, scope: '%s', expires: %s",
                    $token->token, $token->scope, date('Y-m-d H:i:s',$token->expires)
                ),
                'oauth2.component.useridentity'
            );
            $this->errorCode = self::ERROR_NONE;
            $token->type = AccessToken::TYPE_USER;
            $provider->getStorage()->saveToken($this->username,$token);		
		
			return true;
        }
    }
}
