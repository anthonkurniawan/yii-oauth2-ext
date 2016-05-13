<?php
namespace OAuth2Yii\Component;
use \Yii;
use OAuth2\Storage\Pdo;

class customPdo extends Pdo
{

	/* OAuth2\Storage\UserCredentialsInterface */
    // public function checkUserCredentials($username, $password)
    // {	echo "1<br>";
        // if ($user = $this->getUser($username)) {
            // return $this->checkPassword($user, $password);
        // }

        // return false;
    // }
	
 // plaintext passwords are bad!  Override this for your application
    protected function checkPassword($user, $password)
    {	
		 return Yii::app()->getModule('user')->encrypting($password)===$user['password'];
    }
	
		 /**
     * Return claims about the provided user id.
     *
     * Groups of claims are returned based on the requested scopes. No group
     * is required, and no claim is required.
     *
     * @param $user_id
     * The id of the user for which claims should be returned.
     * @param $scope
     * The requested scope.
     * Scopes with matching claims: profile, email, address, phone.
     *
     * @return
     * An array in the claim => value format.
     *
     * @see http://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
     */
    // zpublic function getUserClaims($user_id, $scope){	
		// ECHO "GET USER CLAIM---> user_id : {$user_id}, scope : {$scope}";	//dump($this->_server->getStorage('user_credentials')->getUserDetails($user_id));

		 // if (!$userDetails = $this->getUserDetails($user_id)) {
            // return false;
        // }									echo "userDetail -->"; dump($userDetails);

        // $claims = explode(' ', trim($scope));
        // $userClaims = array();
		
		// // for each requested claim, if the user has the claim, set it in the response
        // $validClaims = explode(' ', self::VALID_CLAIMS);		# const VALID_CLAIMS = 'profile email address phone';
        // foreach ($validClaims as $validClaim) {
            // if (in_array($validClaim, $claims)) {
                // if ($validClaim == 'address') {
                    // // address is an object with subfields
                    // $userClaims['address'] = $this->getUserClaim($validClaim, $userDetails['address'] ?: $userDetails);
                // } else {
                    // $userClaims = array_merge($userClaims, $this->getUserClaim($validClaim, $userDetails));
                // }
            // }
        // }
													// echo "<br>userClaims-->"; dump($userClaims);
        // return $userClaims;
		
	// }
    
	// protected function getUserClaim($claim, $userDetails)
    // {		//	XXXXXXXXXXXXXXX LOM ADA "address" xxxxxxxxxxxxxxxxxxxx
        // $userClaims = array();
        // $claimValuesString = constant(sprintf('self::%s_CLAIM_VALUES', strtoupper($claim)));
        // $claimValues = explode(' ', $claimValuesString);

        // foreach ($claimValues as $value) {
            // $userClaims[$value] = isset($userDetails[$value]) ? $userDetails[$value] : null;
        // }

        // return $userClaims;
    // }
	
	public function getUserDetails($username)
    {
		$sql = sprintf(
            'SELECT username as name, email, t2.first_name as given_name, t2.last_name as middle_name, t2.address, t2.phone
			FROM %s LEFT JOIN tbl_profiles as t2 
			ON t1.id = t2.user_id 
			WHERE username=:user_id ', 'tbl_users as t1'
        );
       // return $this->getDb()->createCommand($sql)->queryRow(true, array(':user_id'=>$username) );
		$stmt = $this->db->prepare($sql);			
		$stmt->execute(array('user_id' => $username));	//dump($stmt->fetchObject()); die();

        if (!$userInfo = (array) $stmt->fetchObject()) {
            return false;
        }

        // the default behavior is to use "username" as the user_id
        return array_merge(array(
            'user_id' => $username
        ), $userInfo);
    }
} 