<?php
/**
 *
 * @copyright Copyright (c) 2017, LiveBox (support@liveboxcloud.com)
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @author Paolo Manili
 */
namespace OCA\Firebasepushnotifications\DB;


use OCA\Firebasepushnotifications\Entities\FirebaseToken;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Mapper;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IDb;
use PDO;

class FirebaseTokenHandler extends Mapper
{
	private $_log;
	/**Handy logger references
	 * @return \OCP\ILogger
	 */
	private function log(){
		if(!$this->_log){
			$this->_log = \OC::$server->getLogger();
		}
		return $this->_log;
	}

	/**
	 * FirebaseTokenHandler constructor.
	 *
	 * @param IDb $db
	 */
    public function __construct(IDb $db){
        parent::__construct($db, 'firebase_token', FirebaseToken::class);
    }

	/**This function deletes a token from the database
	 * @param $token
	 * @return \PDOStatement
	 */
	public function deleteTokenByTokenString($token){
		$query = 'DELETE FROM `*PREFIX*firebase_token` where token = ? AND id > 0 LIMIT 1 ';
		$res = $this->execute($query,[$token]);
		return $res;
	}

	/**This function deletes a tokenId and userId couple
	 *
	 * @param $userId
	 * @param $tokenId
	 * @return \PDOStatement
	 */
	public function deleteTokenByUserAndId($userId,$tokenId){
		$query = 'DELETE FROM `*PREFIX*firebase_token` where user_id = ?  AND id = ?  ';
		$res =  $this->execute($query, [$userId,$tokenId]);
		return $res;
	}

	/**This function deletes a registered token for a given resource
	 *
	 * @param $userId
	 * @param $token
	 * @param $resource
	 * @return \PDOStatement
	 */
    public function deleteToken($userId, $token, $resource){
		$query = 'DELETE FROM `*PREFIX*firebase_token` where user_id = ?  AND token = ? AND resource = ? ';
		$res =  $this->execute($query, [$userId,$token,$resource]);
		return $res;
    }

	/**This function deletes registered tokens for a given userId
	 * @param $userId
	 * @return \PDOStatement
	 */
    public function deleteUser($userId){
		$query = 'DELETE FROM `*PREFIX*firebase_token`  where user_id = ? ';
		return $this->execute($query,[$userId]);
    }

    /** This function checks whether a given user/token pair is already registered.
     * @param $userId
     * @param $token
     * @return bool|\OCP\AppFramework\Db\Entity The Entity if found, or false, otherwise.
     */
    public function isRegistered($userId, $token) {
            $query = 'SELECT * FROM `*PREFIX*firebase_token`   where user_id = ? AND token = ? ';
            try {
                    $res = $this->findEntity($query, [$userId, $token]);
                    if(is_a($res,FirebaseToken::class)){
                            return $res; //Do Update
                    }
            }catch (MultipleObjectsReturnedException $e ){
				$this->pruneToken($userId,$token);
				return false;
            }catch (DoesNotExistException $dne){
				return false;
            }
    }


	/** This function deletes a token for a given userId
	 * (This is useful as sometimes implementing apps use the same device resource
	 * (device id) passing the same firebase token)
	 * @param $userId
	 * @param $token
	 * @return bool
	 */
    public function pruneToken($userId, $token){
            $query = 'DELETE FROM `*PREFIX*firebase_token` where user_id = ?  AND token = ? ';
		$r = $this->execute($query, [$userId, $token]);
		if ($r) {
			//		\OC::$server->getLogger()->info('Delete Token result: true');
			return true;
		} else {
			//		\OC::$server->getLogger()->info('Delete Token result: false');

			return false;
		}
    }

	/**This cleanup function clears the database from identical tokens on different
	 * user Ids. (This can happen if an implementing app doesn't request a new
	 * Firebase Token when registering a different user for the same token).
	 * @param $userId
	 * @param $token
	 * @return \PDOStatement
	 */
	public function removePossibleDuplicates($userId, $token) {
		$query = 'DELETE FROM `*PREFIX*firebase_token` WHERE user_id != ?  AND token = ? ';
		return $this->execute($query, [$userId, $token]);

	}

    /**This fetches an array of FirebaseToken entities for the given userId
     * @param $userId
     * @return array
     */
    public function getTokensForUser($userId){
            $query = 'SELECT * FROM `*PREFIX*firebase_token` where user_id = ? ';
            return $this->findEntities($query,[$userId]);
    }

	/**This fucntion retrieves the number of registered tokens
	 * @return int
	 */
    public function countTokens(){
    	$query = 'SELECT COUNT(*) as n FROM `*PREFIX*firebase_token` ';
		/** @var \PDOStatement $res */
		$res = $this->execute($query);
		$count = -1;
		if($res && ($rs = $res->fetch(PDO::FETCH_ASSOC))){
			$count = $rs['n'];
		}
		return $count;
	}

	/**This function clears the Token Table
	 * @return \PDOStatement
	 */
	public function clearTokens(){
		$query = 'TRUNCATE TABLE  `*PREFIX*firebase_token` ';
		return $this->execute($query);
	}

	/** This function (somewhat unsurprisingly) registers or updates a token
	 * registration
	 * @param $userId
	 * @param $resource
	 * @param $token
	 * @param $locale
	 * @param $type
	 * @return bool
	 */
	public function registerOrUpdateToken($userId, $resource, $token, $locale, $type){
		$registered = $this->isRegistered($userId,$token);
		$doUpdate = false;
		if($registered && is_a($registered,FirebaseToken::class)){
			$this->log()->debug('Token already registered, will update data');
			$doUpdate = true;
		}else{
			$registered = new FirebaseToken();
			$this->log()->debug('The pair: ' . $userId . '-' . $token . ' is new');
		}
		$registered->setUserId($userId);
		$registered->setResource($resource);
		$registered->setToken($token);
		$registered->setLocale($locale);
		$registered->setDeviceType($type);
		$registered->setLastUsed(microtime(true));

		$res = $doUpdate ? $this->update($registered) : $this->insert($registered);
		$this->removePossibleDuplicates($userId, $token);
		if (is_a($res, FirebaseToken::class)) {
			$this->log()->debug('Register Or Update: DoUpdate: ' . $doUpdate . 'Op Res: ' . $res);
			return true;
		}
		return false;
	}

	/**This function retrieves all registered tokens
	 * @return array
	 */
	public function getAllTokens(){
		$query = 'SELECT * FROM `*PREFIX*firebase_token` where 1';
		return $this->findEntities($query);
	}


	/**This function runs tests on token insertion update and deletion
	 * @return void
	 */
	public function testTokenMapper() {
		$logRes = 'FirebaseTokenHandler Test: ';
		$number = $this->countTokens();
		$logRes .= ' Saved Tokens: ' . $number . '. ';
		$this->clearTokens();
		$number = $this->countTokens();
		$logRes .= ' Tokens left after clear: ' . $number . '.';
		for ($i = 0; $i < 10; $i++) {
			$tokenType = microtime() % 2 === 0 ? FirebaseToken::ANDROID_DEVICE : FirebaseToken::IOS_DEVICE;
			$this->registerOrUpdateToken($this->getRandomString(10), 'Res: ' . $this->getRandomString(5), $this->getRandomString(64), 'EN-en', $tokenType);
		}
		$registered = $this->getAllTokens();
		$number = $this->countTokens();
		$logRes .= ' Registered 10 Tokens: Found: '.$number.' fetched: '.count($registered).' ';
		//Print Out:
		/** @var FirebaseToken $token */
		foreach ($registered as $token){
			$logRes .= $token->__toString(); //Using To String
		}
		$this->clearTokens();
		$number = $this->countTokens();
		$logRes .= ' Tokens left after clear: ' . $number . '.';
		$this->log()->info($logRes);
	}


	/**Handy test function to create randomized strings
	 * @param $length
	 * @return string
	 */
	public function getRandomString($length){
    	$cSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    	$cSetL = strlen($cSet);
    	$r = '';
    	for($i = 0; $i<$length ; $i++){
    		$r .= rand(0,$cSetL-1);
		}
		return $r;
	}



}
