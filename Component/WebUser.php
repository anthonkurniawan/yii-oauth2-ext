<?php
namespace OAuth2Yii\Component;

use \Yii;
use \CWebUser;
use \CException;
use \CHttpCookie;
// use \User;
use \Rights;	# Used "Rights::getAuthorizer()->isSuperuser($id)" for set super user ( cover all scope/access)

/**
 * WebUser
 * This class adds support for OAuth2 access tokens to the user component.
 */
class WebUser extends CWebUser
{
    /**
     * @var string name of the oauth2 server component. Default is 'oauth2'.
     */
    public $oauth2 = 'oauth2';

    /**
     * @var CActiveRecord the user record of the currently logged in user
     */
    protected $_model = false;

    /**
     * @var bool whether the user authenticated successfully as OAuth2 user
     */
    protected $_isOAuth2User = false;

    /**
     * @var bool whether the user authenticated successfully as OAuth2 client
     */
    protected $_isOAuth2Client = false;

    /**
     * Treat the user as logged in user if a valid OAuth2 token is supplied
     */
    // public function init()
    // {
       // $oauth2 = Yii::app()->getModule('oauth2')->getComponent($this->oauth2);	//dump($oauth2);
	   
        // if($oauth2===null) {
            // throw new \CException("Invalid OAuth2Yii server component '{$this->oauth2}'");
        // }

        // if($this->getIsOAuth2Request()) {		echo "<br>getIsOAuth2Request()-->TRUE<br>";
            // if(($id = $oauth2->getUserId())!==null) {		echo "userOauth id-->".$id."<br>";
                // $this->_isOAuth2User = true;
                // $this->changeIdentity($id, 'oauth2user', array());	# changeIdentity(mixed $id, string $name, array $states)
				
				// # SET ROLE
				// $user = User::model()->findByPk($id);		//dump($user->role);
				// $this->setState('role',$user->role);
            // } elseif(($id = $oauth2->getClientId())!==null) {
                // $this->_isOAuth2Client = true;
                // $this->changeIdentity($id, 'oauth2client', array());
            // }
        // }
        // parent::init();
    // }

	public function init()
    {
       $oauth2 = Yii::app()->getComponent($this->oauth2);
	   
        if($oauth2===null) {
            throw new \CException("Invalid OAuth2Yii server component '{$this->oauth2}'");
        }

        if($this->getIsOAuth2Request()) {		echo "<br>getIsOAuth2Request()-->TRUE<br>";
            if(($id = $oauth2->getUserId())!==null) {		echo "userOauth id-->".$id."<br>";
                $this->_isOAuth2User = true;
               
				# Changes the current user with the specified identity information. This method is called by login and restoreFromCookie when the current user needs to be populated with the corresponding identity information. 
				# Derived classes may override this method by retrieving additional user-related information. Make sure the parent implementation is called first.
				# changeIdentity(mixed $id, string $name, array $states)
				$this->changeIdentity($id, 'oauth2user', array());	# changeIdentity(mixed $id, string $name, array $states)
				
				# BORROW FROM "module.rights.components.RWebUser - afterLogin"
				# COZ pada auth oauth use "changeIdentity()"  who privide access token, tidak gunakan "login() need $username & $pass"
				if( Rights::getAuthorizer()->isSuperuser($this->getId())===true )
					$this->isSuperuser = true;
				else
					$this->isSuperuser = false;
			
				//dump( Yii::app()->authManager->getAuthAssignments($id) );
				// $identity = new UserIdentity($username, $password);
				// $identity->authenticate();
				// if (!$identity->authenticate()) {
					// throw new \CHttpException(401, $identity->errorMessage);
				// }
				// \Yii::app()->user->login($identity);
				
            } 
			elseif(($id = $oauth2->getClientId())!==null) {		echo "client Oauth id-->".$id."<br>";
                $this->_isOAuth2Client = true;
                $this->changeIdentity($id, 'oauth2client', array());
            }
        }
        parent::init();
    }
	
    /**  CUMA AUTH BEARER .
     * @return bool whether the current request contains an OAuth2 access token. This is the case  if an "Authorization: Bearer ..." header is found.
     */
    public function getIsOAuth2Request()
    {				//dump($_SERVER);	//dump(Yii::app()->request->queryString);					//echo "<br>Masuk ------->getIsOAuth2Request()<br>"; // dump($_SERVER);
		if(isset($_GET['access_token'])){		//dump($_GET);	
			return false; //return true; XXXXXXXX
        }elseif(isset($_SERVER['HTTP_AUTHORIZATION'])&&$_SERVER['HTTP_AUTHORIZATION']!=null) {	//echo $_SERVER['HTTP_AUTHORIZATION']."<br>".substr($_SERVER['HTTP_AUTHORIZATION'],0,6);
            $authorization = $_SERVER['HTTP_AUTHORIZATION'];				//echo $_SERVER['HTTP_AUTHORIZATION']. substr($authorization,0,6)==='Bearer';
			 return substr($authorization,0,6)==='Bearer';
	   } 
		else if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])&&$_SERVER['REDIRECT_HTTP_AUTHORIZATION']!=null) {			
            $authorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];		//echo "<br>------->". substr($authorization,0,6)==='Bearer';
			 return substr($authorization,0,6)==='Bearer';
		} 
		elseif(function_exists('apache_request_headers')) {	
            $headers = apache_request_headers();
            $authorization = isset($headers['Authorization']) ? $headers['Authorization'] : '';
			 return substr($authorization,0,6)==='Bearer';
		}else 		
            return false;
    }

    /**
     * @var bool whether the user authenticated successfully as OAuth2 user
     */
    public function getIsOAuth2User()
    {
        return $this->_isOAuth2User;
    }

    /**
     * @var bool whether the user authenticated successfully as OAuth2 client
     */
    public function getIsOAuth2Client()
    {
        return $this->_isOAuth2Client;
    }
	
	// public function getRole(){
		// #Cari role utk di set pada authManager
		// $id = Yii::app()->user->id;
		// $user = User::model()->findByPk($id);		//dump($user->role);
		// $this->setState('role',$user->role);
	// }
	
}
