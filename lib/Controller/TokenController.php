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
namespace OCA\Firebasepushnotifications\Controller;


use OCA\Firebasepushnotifications\CurrentUser;
use OCA\Firebasepushnotifications\Service\TokenService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class TokenController extends Controller {

	private function log() {
		return \OC::$server->getLogger();
	}

	private $currentUser;
	private $_tokenService;

	/**
	 * @return TokenService
	 */
	private function tokenService() {
		if (!$this->_tokenService) {
			$this->_tokenService = \OC::$server->query(TokenService::class);
			//$this->log()->info('Fetched token service: ' . get_class($this->_tokenService));
		}
		return $this->_tokenService;
	}

	public function __construct($appName, CurrentUser $currentUser, IRequest $request) {
		parent::__construct($appName, $request);
		//$this->log()->info('Initialized Token Controller');
		$this->currentUser = $currentUser;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *     */
	public function registerToken($token, $resource, $deviceType, $locale = 'en_EN') {
		$this->log()->info('Registering Token: Received  Current User: ' . $this->currentUser->getUserIdentifier() . 'Parameters' . print_r($this->request->getParams(), true));
		//Get User Info //Token //Type
		$this->log()->info('Received Token: ' . $token . ' Resource: ' . $resource . ' locale: ' . $locale);
		if ($this->tokenService()) {

			if (!$token) {
				return $this->returnError('token', 'Missing token parameter in registration request');
			}

			if (!$resource) {
				return $this->returnError('resource', 'Missing resource parameter in registration request');
			}

			if (!$deviceType) {
				return $this->returnError('deviceType', 'Missing deviceType parameter in registration request');
			}



			$this->log()->info('Have a token service');
			//			$this->tokenService()->registerToken($this->currentUser,$token,$resource,$locale,$type);
		}

		$arr['userId'] = $this->currentUser->getUserIdentifier();
		if (!$this->currentUser->getUserIdentifier()) {
			return $this->returnError('user auth', 'Could not identify calling user');
		}
		$result = $this->tokenService()->registerToken($this->currentUser->getUserIdentifier(), $token, $resource, $locale, $deviceType);
		if ($result) {
			return $this->returnSuccess([], 'Successfully Registered/Updated token');
		}
		return $this->returnError('Unknown', 'Unknown Error');
		//	return new DataResponse($arr, Http::STATUS_OK, []);

	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *     */
	public function unregisterToken($token) {
		if (!$token) {
			return $this->returnError('token', 'Missing token parameter in deregistration request');
		}
		$userId = $this->currentUser->getUserIdentifier();
		if (!$this->currentUser->getUserIdentifier()) {
			return $this->returnError('user auth', 'Could not identify calling user');
		}
		$this->log()->info('Removing Token: Received  Current User: ' . $this->currentUser->getUserIdentifier() . 'Parameters' . print_r($this->request->getParams(), true));
		//Get User Info //Token //Type

		$r = $this->tokenService()->pruneToken($userId, $token);
		if ($r) {
			return $this->returnSuccess([], 'Successfully removed token for userId ' . $userId);
		}
		return $this->returnError('Unknown', 'Unknown error');
//		return new DataResponse(['test' => 'toast'], Http::STATUS_OK, []);

	}


	/**This utility sends a response and a custom message with the 400 BAD_REQUEST
	 * @param $param
	 * @param string $customText
	 * @return DataResponse
	 */
	public function returnSuccess($params, $customText = '') {
		$arr = ['result' => 'success', 'extra ' => $customText];
		$arr = array_merge($params, $arr);
		return new DataResponse($arr, Http::STATUS_OK);
	}

	/**This utility sends a response and a custom message with the 400 BAD_REQUEST
	 *
	 * @param $param
	 * @param string $customText
	 * @return DataResponse
	 */
	public function returnError($param, $customText = '') {
		return new DataResponse(['result' => 'error', 'error' => 'missing: ' . $param, 'extra ' => $customText], Http::STATUS_BAD_REQUEST);
	}

}