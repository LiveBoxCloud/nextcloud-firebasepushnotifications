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

namespace OCA\Firebasepushnotifications\Entities;


class FirebaseResponse { //This class does not extend Entity, this is intentional

	private $multicast_ids;
	private $success;
	private $failure;
	private $results;
	const INVALID_REGISTRATION = 'InvalidRegistration';
	const MISSING_REGISTRATION = 'MissingRegistration';
	const NOT_REGISTERED = 'NotRegistered';

	public function __construct($serviceResponse) {
		$log = \OC::$server->getLogger();
		$log->debug('FB ResponseEncoded: '.base64_encode($serviceResponse));
		$serviceResponse = json_decode($serviceResponse);
		$this->multicast_ids = $serviceResponse->multicast_id;
		$this->success = $serviceResponse->success;
		$this->failure= $serviceResponse->failure;
		$this->results = $serviceResponse->results;

	}

	/**Handy override for debug printouts
	 * @return string
	 */
	public function __toString() {
		return 'FirebaseResponse: MulticastId: '.$this->multicast_ids.' Success:  '.$this->success.'. Failure: '.$this->failure.' Results: '.print_r($this->results,true);
	}

	/**Whether the Firebase Response reported errors
	 * @return bool
	 */
	public function hasFailures(){
		return $this->failure>0;
	}

	/**Whether the Firebase Response reported successful sends
	 * @return bool
	 */
	public function isSuccessful(){
		return $this->success>0;
	}

	/**This function fetches the error details in the Firebase Response
	 * @return array
	 */
	public function getErrors(){
		$err = [];
		if(is_array($this->results)){
			foreach ($this->results as $key => $item){
				if(is_array($item)){
					foreach ($item as $innerKey => $innerValue){
						if ($innerKey === 'error') {
							$err[] = $innerValue;
						}
					}
				}
			}
		}
		return $err;
	}
}