<?php
namespace OAuth2Yii\Component;

use \OAuth2Yii\Storage;

use \Yii;
use \CApplicationComponent;

/**
 * ServerComponent
 *
 * This is the OAuth2 server application component.
 */
class ServerComponent extends CApplicationComponent
{

    /** @var bool whether to enable the "Authorization Code" grant (see RFC 6749). Default is false. */
    public $enableAuthorization = false;

    /**
     * @var bool whether to enable the "Implicit" grant (see RFC 6749). Default is false.
     * Note, that this grant type is considered to be insecure. Use at your own risk.
     */
    public $enableImplicit = false;

    /** @var bool whether to enable the "Resource Owner Password Credentials" grant (see RFC 6749). Default is false.*/
    public $enableUserCredentials = false;

    /** @var bool whether to enable the "Client Credentials" grant (see RFC 6749). Default is false. */
    public $enableClientCredentials = false;

	public $enableRefreshToken = false;

	/** @var bool whether to enable the "JWT Beare" grant (see RFC 6749). Default is false. */
	public $enableJwtBearer = false;	# BARU -----------
    
	# using OpenID Connect 
	public $use_openid_connect = false;
	
	# need for openID
	# OpenID Connect supports multiple Issuers per Host and Port combination. The issuer returned by discovery MUST exactly match the value of iss in the ID Token.
	# OpenID Connect treats the path component of any Issuer URI as being part of the Issuer Identifier. 
	# For instance, the subject "1234" with an Issuer Identifier of "https://example.com" is not equivalent to the subject "1234" with an Issuer Identifier of "https://example.com/sales".
	# It is RECOMMENDED that only a single Issuer per host be used. However, if a host supports multiple tenants, multiple Issuers for that host may be needed.
	public $issuer = false;
	
	/** @var string name of CDbConnection app component. Default is 'db'. */
    public $db = 'db';

    /** @var int lifetime of the access token in seconds. Default is 3600. */
    public $accessTokenLifetime = 3600;

	/**
	* Crypto tokens provide a way to create and validate access tokens without requiring a central storage such as a database. 
	* This decreases the latency of the OAuth2 service when validating Access Tokens.
	*/
	public $use_crypto_tokens=false;
	
    /** @var bool whether to enforce the use of a 'state'. See RFC 6749. * Recommended to avoid CSRF attacks.*/
    public $enforceState = true;

    /**
     * @var bool whether supplied redirect_uri must exactly match the stored redirect URI for that client.
     * If false, only the beginning of the supplied URI must match the clients stored URI. Default is true.
     */
    public $exactRedirectUri = true;
	

    /** @var array|null list of available scopes or null/empty array if no scopes should be used. */
    public $scopes;

    /** @var string|null|bool a string with default scope(s). If set to `null` no scope is required.*/
    public $defaultScope;

    /** @var \OAuth2\Server*/
    protected $_server;

    /** @var \OAuth2\Request */
    protected $_request;

    /**@var array of storages for oauth2-php-server*/
    protected $_storages = array();

    /** @var array of access token data */
    protected $_tokenData;

	# REPACK
	public $dsn =null;
	public $tables = array();
	
	    /**
     * Init all required storages
     */
    protected function createGrantType($storage)
    {
        $grantType = array();

        if($this->enableAuthorization) 
			$grantType = array_merge( array('authorization_code' => new \OAuth2\GrantType\AuthorizationCode($storage)), $grantType);		
		if($this->enableRefreshToken) 
			$grantType = array_merge( array('refresh_token'   => new \OAuth2\GrantType\RefreshToken($storage, array('always_issue_new_refresh_token' => true) ) ), $grantType);		
        if($this->enableUserCredentials) 
			$grantType = array_merge( array('user_credentials'   => new \OAuth2\GrantType\UserCredentials($storage)), $grantType);		
        if($this->enableClientCredentials) 
			$grantType = array_merge( array('client_credentials' => new \OAuth2\GrantType\ClientCredentials($storage, array('allow_public_clients' => true) ) ), $grantType);		
		 if($this->enableJwtBearer) 
			$grantType = array_merge( array('jwt_bearer'   => new \OAuth2\ GrantType\JwtBearer($storage, 'http://localhost/yii-oauth2')), $grantType);		

		return $grantType;
    }
	
    /**
     * Initialize OAuth2 PHP Server object
     */
    public function init()
    {															
		
		if($this->dsn==null)
			  throw new \CException("DSN is empty (please set dsn)");
		if(count($this->tables)==0)
			 throw new \CException("Table config is empty");
			 
		# create a storage object to hold new authorization codes
		// $storage = new \OAuth2\Storage\Pdo($this->dsn, $this->tables);	//print_r($storage);
		$storage = new customPdo($this->dsn, $this->tables);	//dump($storage);
																	
		# create the grant type
		// $grantTypes = array(
            // 'authorization_code' => new \OAuth2\GrantType\AuthorizationCode($storage),
			// 'user_credentials'   => new \OAuth2\GrantType\UserCredentials($storage),
			// 'client_credentials' => new \OAuth2\GrantType\ClientCredentials($storage, array('allow_public_clients' => true)),
			// 'refresh_token'   => new \OAuth2\GrantType\RefreshToken($storage, array('always_issue_new_refresh_token' => true)),
			// 'jwt_bearer'   => new \OAuth2\ GrantType\JwtBearer($storage, 'http://localhost/yii-oauth2')
		// );																
		$grantTypes = $this->createGrantType($storage);
		# __construct(
		# 		$storage = array(), array $config = array(), array $grantTypes = array(), array $responseTypes = array(), 
		# 		TokenTypeInterface $tokenType = null, ScopeInterface $scopeUtil = null, ClientAssertionTypeInterface $clientAssertionType = null
		# )
        $this->_server = new \OAuth2\Server($storage, 
			array(
			'use_crypto_tokens'=> $this->use_crypto_tokens,
			'allow_implicit' => $this->enableImplicit,
            'enforce_state' => $this->enforceState,
            'require_exact_redirect_uri' => $this->exactRedirectUri,
			'access_lifetime' => $this->accessTokenLifetime,
			'use_openid_connect'  => true,
			'issuer' => 'blogapp', # need for OpenID
			), 
			$grantTypes
		);

		# USING MEMORY STORAGE TO KEEP PUBLIC & PRIVATE KEY
		/*
		if($this->use_crypto_tokens){		
			// create storage
			$keyStorage = new \OAuth2\Storage\Memory( array(
				'keys' => array(
					// 'yii_oauth2' => array(
						// 'public_key'  => file_get_contents("http://localhost/blog.com/pubkey2.pem"),
						// 'private_key' => file_get_contents("http://localhost/blog.com/privkey2.pem"),
					// ),

					// declare global keys as well
					'public_key'  => file_get_contents("http://localhost/blog.com/pubkey.pem"),	//file_get_contents('/path/to/pubkey.pem');
					'private_key' => file_get_contents("http://localhost/blog.com/privkey.pem"),
				),
				// add a Client ID for testing
				// 'client_credentials' => array(
					// 'blogapp1' => array('client_secret' => 'blogapp_pass')
				// ),
			));																								
			// // Make the "access_token" storage use Crypto Tokens instead of a database
			$cryptoStorage = new \OAuth2\Storage\CryptoToken($keyStorage, $storage['access_token'] );
			//$cryptoStorage = new \OAuth2\Storage\CryptoToken($storage['public_key'], $storage['access_token']);
		
			# addStorage($storage, $key = null)
			$this->_server->addStorage($cryptoStorage, "access_token");				

			# make the "token" response type a CryptoToken
			#  __construct(PublicKeyInterface $publicKeyStorage = null, AccessTokenStorageInterface $tokenStorage = null, RefreshTokenInterface $refreshStorage = null, 
			#	array $config = array(), EncryptionInterface $encryptionUtil = null)
			$cryptoResponseType = new \OAuth2\ResponseType\CryptoToken($keyStorage, $storage['access_token'], $storage['refresh_token'] );	
			# addResponseType(ResponseTypeInterface $responseType, $key = null)
			$this->_server->addResponseType($cryptoResponseType);	
		
			#  $responseTypes['code'] = new AuthorizationCodeResponseType($this->storages['authorization_code'], $config);
			$authorize_codeType =  new \OAuth2\ResponseType\AuthorizationCode($storage['authorization_code']);
			$this->_server->addResponseType( $authorize_codeType );	
		}	//dump($this->_server->getStorages('access_token')['access_token'] );
		*/
		# TEST OPEN-ID ----------------
		// $userClaim = new Storage\UserClaims($this, $this->db);
		// # addStorage($storage, $key = null)
		// $this->_server->addStorage($userClaim, "user_claims");	
    }

    /**
     * @param string|null $scope to check or null if no scope is used
     * @return bool whether the client is authorized for this request
     */
    public function checkAccess($scope=null)
    {	
        $response = new \OAuth2\Response;		//dump($response);	dump($this->getRequest());

        YII_DEBUG && Yii::trace('Checking permission'.($scope ? " for scope '$scope'": ''),'oauth2.servercomponent');

		# verifyResourceRequest(RequestInterface $request, ResponseInterface $response = null, $scope = null)
        $server = $this->getServer();	//dump($server);	# res: vendor.bshaffer.."OAuth2\Server" obj
		$value = $server->verifyResourceRequest($this->getRequest(), $response, $scope);		//echo "value-->"; dump($value);

        if(YII_DEBUG) {
            $p = $response->getParameters();	
            $error = isset($p['error_description']) ? $p['error_description'] : 'Unknown error';
            Yii::trace($value ? 'Permission granted' : "Check failed: $error",'oauth2.servercomponent');
        }

								
		if(!$value){
			//return $server->getResponse(); //dump($res);
			return $res = $server->getResponse();	
			//return (array) $res;

			//return new Response(json_encode($api_response))
		}
		else
			return $value;
    }
	
    /**
     * @return \OAuth2\Server object
     */
    public function getServer()
    {
        return $this->_server;
    }

	/**
	*  This controller is called when the user claims for OpenID Connect's
	*  UserInfo endpoint should be returned.
	*
	*  ex:
	*  > $response = new OAuth2\Response();
	*  > $userInfoController->handleUserInfoRequest(
	*  >     OAuth2\Request::createFromGlobals(),
	*  >     $response;
	*  > $response->send();
	*
	*/
	public function UserInfo($scope=null)
    {	
        $response = new \OAuth2\Response;		//dump($response);	dump($this->getRequest());

		# verifyResourceRequest(RequestInterface $request, ResponseInterface $response = null, $scope = null)
        $server = $this->getServer();	//dump($server);	# res: vendor.bshaffer.."OAuth2\Server" obj
		//$value = $server->verifyResourceRequest($this->getRequest(), $response, $scope);		
		
		// $userInfoController->handleUserInfoRequest(
			// OAuth2\Request::createFromGlobals(),
		// $response;
		// $response->send();
		
		$request = $this->getRequest();	//dump($request);
		# handleUserInfoRequest(RequestInterface $request, ResponseInterface $response = null)
		return $server->handleUserInfoRequest( $request, $response);	

	}
	
    /**
     * @return mixed|null the user id if a valid access token was supplied in the request or null otherwhise
     */
    public function getUserId()
    {																		
        $tokenData = $this->getAccessTokenData();		//echo "<br>tokendata -->"; dump($tokenData);
        return isset($tokenData['user_id']) ? $tokenData['user_id'] : null;
    }

    /**
     * @return mixed|null the client id if a valid access token was supplied in the request or null otherwhise
     */
    public function getClientId()
    {
        $tokenData = $this->getAccessTokenData();
        return isset($tokenData['client_id']) ? $tokenData['client_id'] : null;
    }

    /**
     * @return array access token data
     */
    public function getAccessTokenData()
    {												//dump($this->getRequest());
        if($this->_tokenData===null) {
            $this->_tokenData = $this->_server->getAccessTokenData($this->getRequest());
        }
        return $this->_tokenData;
    }

	#ADD BARU
	public function getUserData(){
		// $server->handleAuthorizeRequest($request, $response, $is_authorized, $userid);
		// $response = new \OAuth2\Response;	
		// $authorize = (bool) 1;
		// $server = $this->_server->handleAuthorizeRequest($this->getRequest(), $response, $authorize, $userid);	dump($server);
		
		$server = $this->getServer();
		
		$res = $server->verifyResourceRequest($this->getRequest());	//dump( $server->getResponse()); //dump($res);
		if (!$res) {
			$server->getResponse()->send();
			die;
		}

		// $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
		$tokenData = $server->getAccessTokenData($this->getRequest());		//dump($token);
		return $tokenData;
	}
	
    /**
     * @return bool whether any grant type is enabled
     */
    public function getCanGrant()
    {
        return $this->enableAuthorization || $this->enableImplicit || $this->enableUserCredentials || $this->enableClientCredentials || $this->enableJwtBearer;
    }

    /**
     * @return \OAuth2\Request the request object as used by OAuth2-PHP
     */
    public function getRequest()
    {
        if($this->_request===null) {
            $this->_request = \OAuth2\Request::createFromGlobals();
        }
        return $this->_request;
    }

	
}
