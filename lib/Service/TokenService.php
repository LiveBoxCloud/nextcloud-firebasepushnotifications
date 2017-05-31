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
 *
 */

namespace OCA\Firebasepushnotifications\Service;

use Exception;

use OCA\Firebasepushnotifications\DB\FirebaseTokenHandler;

/**
 * Description of TokenService
 *
 * @author nunzio
 */
class TokenService {
    
    private $mapper;
    
    public function __construct(FirebaseTokenHandler $mapper) {
        $this->mapper = $mapper;
    }

	public function testTest() {
		\OC::$server->getLogger()->info('Received Call');
	}

	/** This function registers a token on the backend
	 *
	 * @param $userId
	 * @param $token
	 * @param $resource
	 * @param $locale
	 * @param $type
	 * @return bool
	 */
    public function registerToken($userId, $token, $resource, $locale, $type){
		//  $regCheck = $this->isRegistered($userId,$token);
		$r = $this->mapper->registerOrUpdateToken($userId, $resource, $token, $locale, $type);
		\OC::$server->getLogger()->debug('TokenService: Registration Result: ' . $r);
		return $r;
//        if(!$regCheck){
//        }else if(is_a($regCheck,FirebaseToken::class)){
//            $firebaseToken->setId($regCheck->getId());
//            return $this->mapper->update($firebaseToken);
//        }
    }

    private function handleException ($e) {
        throw $e;
    }

    public function pruneToken($userId, $token) {
        try {
            return $this->mapper->pruneToken($userId, $token);
        } catch(Exception $e) {
            $this->handleException($e);
		}
		return false;
    }
    
    public function deleteUser($userId){
        try {
            return $this->mapper->deleteUser($userId);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }
    
    public function getTokensForUser($userId){
        return $this->mapper->getTokensForUser($userId);
    }
    
}