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

namespace OCA\Firebasepushnotifications\Sender;


use OCA\Firebasepushnotifications\DB\FirebaseTokenHandler;
use OCA\Firebasepushnotifications\Entities\FirebaseResponse;
use OCP\ILogger;

class FirebaseResponseHandler {

	private $tokenHandler;

	/**
	 * @return FirebaseTokenHandler
	 */
	private function getTokenHandler(){
		if(!$this->tokenHandler){
			$this->tokenHandler = \OC::$server->query(FirebaseTokenHandler::class);
		}return $this->tokenHandler;
	}

	private $log;

	/**Handy Drive logger reference
	 * @return ILogger
	 */
	private function log(){
		if(!$this->log){
			$this->log = \OC::$server->getLogger();
		}return $this->log;
	}

	public function __construct() {
	}

	/**This function parses the Firebase Server response looking for helpful
	 * feedback on the sending and token registration info
	 *
*@param $httpCode
	 * @param $response
	 * @param $token
	 * @return string A text response
	 */
	public function handleResponse($httpCode , $response, $token) {
		$logResp = "";
		switch ($httpCode){
			case (200):{ //Successful Response
				$resp = $this->parseResponse($response);
				if($resp->hasFailures()){
					$logResp = 'Token ' . $token . ' is Invalid and should be deleted ';
					$deleteResult = $this->getTokenHandler()->deleteTokenByTokenString($token);
					$logResp .= 'Token delete result' . $deleteResult->errorCode() . ' ';
					$logResp .= 'Errors: ' . print_r($resp->getErrors(), true);
				} else {
					if ($resp->isSuccessful()) {
						$logResp = 'Operation Successful';
					} else {
						$logResp = 'No Errors';
					}
				}
				break;
			}
			case 401 : {
				$logResp = 'Failed to authenticate sender account: ' . $httpCode;
				break;
			}
			default: {
				$logResp = 'Unhandled Response Code: ' . $httpCode . ' Data: ' . $response;
			}
		}
		$this->log()->debug($logResp);
		return $logResp;
	}

	/**This function builds the FirebaseResponse object
	 * @param $response
	 * @return FirebaseResponse
	 */
	private function parseResponse($response) {
		//Try Decode JSON
		$response = new FirebaseResponse($response);
		$this->log()->debug('Test Firebase Response Parsing: ' . $response);
		return $response;

	}


}