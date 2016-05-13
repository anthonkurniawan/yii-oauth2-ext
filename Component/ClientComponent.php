<?php
namespace OAuth2Yii\Component;

use \OAuth2Yii\Storage;

use \Yii;
use \CApplicationComponent;
use \CExcpetion;
use \CHtml;

/**
 * ClientComponent
 *
 * This is the OAuth2 client application component.
 */
class ClientComponent extends CApplicationComponent
{
	public $redirect_uri = null;
    /**
     * @var array provider configurations indexed by a custom provider name. Each
     * entry should contain a `class` property. If it's missing, the class name
     * is autogenerated from the index key.
     *
     *  array(
     *      'google' => array(
     *          // No 'class' specified, so OAuth2Yii\Provider\Google is assumed
     *          'clientId'      => 'Your Google client id',
     *          'clientSecret'  => 'Your Google client secret',
     *      ),
     *      'myapi' => array(
     *          // A generic OAuth2 provider e.g. to work with OAuth2Yii servers
     *          'class'             => 'OAuth2Yii\Providers\Generic'
     *          'clientId'          => 'Your client id',
     *          'clientSecret'      => 'Your client secret',
     *          'tokenUrl'          => 'http://myapi.com/token',
     *          'authorizationUrl'  => 'http://myapi.com/authorize',
     *      )
     *  );
     */
    public $providers = array();

    /**
     * @var array concreted providers
     */
    protected $_p = array();

    /**
     * @param string $name provider name as configured in $providers
     * @return \OAuth2Yii\Provider\Provider
     */
    public function getProvider($name)
    {	
        if(!isset($this->_p[$name])) {
            $this->_p[$name] = $this->createProvider($name);
        }

        return $this->_p[$name];
    }

    /**
     * Create and init an OAuth2 provider
     *
     * @param string $name of provider
     *
     * @throws \CException if the configuration is missing
     * @return \OAuth2Yii\Provider\Provider
     */
    protected function createProvider($name)
    {
        if(!isset($this->providers[$name])) {
            throw new \CException("Missing configuration for provider '$name'");
        }

        $config = $this->providers[$name];
        $config['name'] = $name;

        if(!isset($config['class'])) {
            $config['class'] = 'OAuth2Yii\\Provider\\'.ucfirst($name);
        }

        $provider = Yii::createComponent($config);
        $provider->init();
        return $provider;
    }
	
	public function getListProvider(){
		$providers = $this->providers;
		//foreach($providers as $client=>$value) :
			//echo CHtml::link( $client, 
				//// $value['authorizationUrl'] ."?provider={$client}
				////"http://localhost/yii-oauth2/oauthClient/auth?provider={$client}
				//"{$this->redirect_uri}?provider={$client}
				//&continues=true
				//&response_type=code
				//&client_id={$value['clientId']}
				//&redirect_uri={$this->redirect_uri}?provider={$client}
				//&scope={$value['defaultScope']}
				//&state=" . Yii::app()->session->getSessionID(),
				//array("class"=>"auth btn btn-sm btn-success") 
			//);
			////dump($value);
		//endforeach;
		
		foreach($providers as $client=>$value) :
			echo CHtml::link( $client, 
				array($value['authorizationUrl'],
					'provider'=>$client,
					'continues'=>true,
					'response_type'=>'code',
					'client_id'=>$value['clientId'],
					'redirect_uri'=>Yii::app()->getController()->createUrl('oauthClient/receive_code').'?provider='.$client, // FOR 'oauthClient/receive_code' SHOULD BE SET ON "OAUTH-CLIENT CONFIG"
					'scope'=>$value['defaultScope'],
					'state'=>Yii::app()->session->getSessionID()
				),
				array("class"=>"auth btn btn-sm btn-success") 
			);
			//dump($value);
		endforeach;
		
		$cs = Yii::app()->getClientScript();  
		$cs->registerScript('providerLink',    
			"$(function() {
				$('.auth').click(function() {
				var signinWin;
				var screenX     = window.screenX !== undefined ? window.screenX : window.screenLeft,
				screenY     = window.screenY !== undefined ? window.screenY : window.screenTop,
				outerWidth  = window.outerWidth !== undefined ? window.outerWidth : document.body.clientWidth,
				outerHeight = window.outerHeight !== undefined ? window.outerHeight : (document.body.clientHeight - 22),
				width       = 500,
				height      = 500,
				left        = parseInt(screenX + ((outerWidth - width) / 2), 10),
				top         = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
				options    = ('width=' + width +',height=' + height +',left=' + left +',top=' + top);
		 
				signinWin=window.open(this.href,'Login',options);
				if (window.focus) {signinWin.focus()}
					return false;
				});
			});", null );
	}
}
