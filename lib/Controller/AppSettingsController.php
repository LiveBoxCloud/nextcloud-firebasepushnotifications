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
use OCA\Firebasepushnotifications\DB\FirebaseConfHandler;
use OCA\Firebasepushnotifications\DB\FirebaseTokenHandler;
use OCA\Firebasepushnotifications\Entities\DummyPushType;
use OCA\Firebasepushnotifications\UserSettings;
use OCP\Activity\IManager;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Security\ISecureRandom;

class AppSettingsController extends ApiController {

	/** @var \OCP\IConfig */
	protected $config;

	/** @var \OCP\Security\ISecureRandom */
	protected $random;

	/** @var \OCP\IURLGenerator */
	protected $urlGenerator;

	/** @var IManager */
	protected $manager;

	/** @var \OCA\Firebasepushnotifications\UserSettings */
	protected $userSettings;

	/** @var \OCP\IL10N */
	protected $l10n;

	/** @var string */
	protected $user;
	/** @var FirebaseConfHandler */
	protected $firebaseConfHandler;

	/** @var  FirebaseTokenHandler */
	protected $firebaseTokenHandler;
	private $_log;

	private function log() {
		if (!$this->_log) {
			$this->_log = \OC::$server->getLogger();
		}
		return $this->_log;
	}

	/**
	 * constructor of the controller
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param ISecureRandom $random
	 * @param IURLGenerator $urlGenerator
	 * @param IManager $manager
	 * @param UserSettings $userSettings
	 * @param IL10N $l10n
	 * @param CurrentUser $currentUser
	 * @param FirebaseConfHandler $firebaseConfHandler
	 * @param FirebaseTokenHandler $tokenHandler
	 */
	public function __construct($appName,
								IRequest $request,
								IConfig $config,
								ISecureRandom $random,
								IURLGenerator $urlGenerator,
								IManager $manager,
								UserSettings $userSettings,
								IL10N $l10n,
								CurrentUser $currentUser, FirebaseConfHandler $firebaseConfHandler, FirebaseTokenHandler $tokenHandler) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->random = $random;
		$this->urlGenerator = $urlGenerator;
		$this->manager = $manager;
		$this->userSettings = $userSettings;
		$this->l10n = $l10n;
		$this->user = (string)$currentUser->getUID();
		$this->firebaseConfHandler = $firebaseConfHandler;
		$this->firebaseTokenHandler = $tokenHandler;
	}

	/**This function handles Admin Settings update, spartan for now, but it should work.
	 *
	 * @return string
	 */
	public function updateAdminSettings() {
		$this->log()->info('Update Admin Settings has been called');
		$response = 'Successfully Updated settings';
		try {
			//	$this->log()->info('IRequest: ' . print_r($this->request->getParams(), true));
			$this->updateEnabledPushTypes();
		} catch (\Exception $e) {
			$response = 'Error during configuration update: ' . $e->getMessage();
			$this->log()->error('Error during configuration update' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
		}
		$this->log()->debug('Update Admin Settings Response: ' . $response);
		return new DataResponse(array(
			'data' => array(
				'message' => (string)$response,
			),
		));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function updateUserSettings() {
		$this->log()->info('Update User Settings has been called');
		$response = 'Successfully Updated settings';
		try {
			$enabledTypes = $this->getEnabledPushTypesFromPost();
			$pushTypesConf = $this->firebaseConfHandler->getUserPushSettings($this->user);
			foreach ($pushTypesConf->getPushTypes() as $pushType => $pushTypeObject) {
				$enabled = isset($enabledTypes[$pushType]);
				$this->log()->info('Type: ' . $pushType . ' is it set?: ' . $enabled);
				$pushTypeObject->isEnabled = $enabled;
				$pushTypesConf->addPushType($pushTypeObject);
			}
			$pushTypesConf->setSendEnabled($this->getSendEnabledFromPost());
			$pushTypesConf->setSendToSameUser($this->getSendToSameUserFromPost());
			$this->firebaseConfHandler->updateUserPushSettings($this->user, $pushTypesConf);
		} catch (\Exception $e) {
			$response = 'Error during configuration update: ' . $e->getMessage();
			$this->log()->error('Error during configuration update' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
		}
		$this->log()->debug('Update User Settings Response: ' . $response);
		return new DataResponse(array(
			'data' => array(
				'message' => (string)$response,
			),
		));
	}


	/**
	 *
	 *
	 * @return DataResponse
	 */
	public function updateFirebaseKey() {
		$this->log()->info('UpdateFirebaseKey called ' . print_r($this->request->getParams(), true));
		//Get Current Key and Compare
		$key = $this->request->getParam('serverKey', null);
		$response = 'Updated Server Key';
		if ($key) {
			$this->firebaseConfHandler->saveServerKey($key);
		} else {
			$response = 'Missing server Key';
		}

		return new DataResponse(array(
			'data' => array(
				'message' => (string)$response,
			),
		));
	}

	/**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function deleteUserToken() {
		$this->log()->info('Delete User tokens has been called'.$this->user.' PostP:'.print_r($this->request->getParams(),true));
		$tokenId = $this->request->getParam('tokenId');
		$response = 'Failed to delete Token';
		if($tokenId){
			$this->firebaseTokenHandler->deleteTokenByUserAndId($this->user,$tokenId);
			$response = 'Token deleted';
		}
		return new DataResponse(array(
			'data' => array(
				'message' => (string)$response,
				'removeRow' => $tokenId
			),
		));

	}
	/**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function deleteAllUserTokens() {
		$this->log()->info('Delete all user tokens has been called'.$this->user.' PostP:'.print_r($this->request->getParams(),true));
		//$tokenId = $this->request->getParam('tokenId');
		$response = 'Failed to delete User Tokens';
		if($this->user){
			$this->firebaseTokenHandler->deleteUser($this->user);
			$response = 'User tokens deleted';
		}
		return new DataResponse(array(
			'data' => array(
				'message' => (string)$response,
				'removeRows' => true
			),
		));

	}


	/**This function updates enabled push types using the values passed in the
	 * post request (No return is provided as the IConfig setAppValue doesn't
	 * return a result.
	 *
	 */
	private function updateEnabledPushTypes() {
		$enabledTypes = $this->getEnabledPushTypesFromPost();
		/** @var DummyPushType $pushTypeObject */
		$pushTypesConf = $this->firebaseConfHandler->getPushTypesConfiguration();
		//$this->log()->info('Got These Types: ' . print_r($enabledTypes, true) . ' SavedTypes: ' . print_r($pushTypesConf, true));
		foreach ($pushTypesConf->getPushTypes() as $pushType => $pushTypeObject) {
			$enabled = isset($enabledTypes[$pushType]);
			$this->log()->info('Type: ' . $pushType . ' is it set?: ' . $enabled);
			$pushTypeObject->isEnabled = $enabled;
			$pushTypesConf->addPushType($pushTypeObject);
		}

		$pushTypesConf->setSendEnabled($this->getSendEnabledFromPost());
		$pushTypesConf->setSendToSameUser($this->getSendToSameUserFromPost());
		$this->log()->debug('Resulting PushTypesConfiguration: ' . $pushTypesConf);
		$this->firebaseConfHandler->setPushTypeConfigurations($pushTypesConf);
	}


	/**This function checks if SendToSameUser option has been set in the request
	 * @return bool
	 */
	private function getSendToSameUserFromPost() {
		if ($this->request->getParam('SendToSameUser')) {
			return true;
		}
		return false;
	}

	/**This function checks if the SendEnabled option has been set in the request
	 * @return bool
	 */
	private function getSendEnabledFromPost() {
		if ($this->request->getParam('SendEnabled')) {
			return true;
		}
		return false;
	}


	/**This function extrapolates the PushTypes set to enabled in the request
	 * @return array of pushtype ids
	 */
	private function getEnabledPushTypesFromPost() {
		$enabledPushTypes = [];

		foreach ($this->request->getParams() as $param => $val) {
			if (strpos($param, 'PushType') !== false) {
				$enabledPushTypes[$val] = true;
			}
		}
		//$this->log()->info('getEnabledPushTypesFromPost: ' . print_r($this->request->getParams(), true) . ' Evaluated as: ' . print_r($enabledPushTypes, true));
		return $enabledPushTypes;
	}


	/**@NoCSRFRequired
	 * @NoAdminRequired
	 * @return TemplateResponse
	 */
	public function displayPanel() {
		$this->log()->info('User Settings Required');
		$userSettings = $this->firebaseConfHandler->getUserSettings($this->user);

		$tokens = $this->firebaseTokenHandler->getTokensForUser($this->user);
		$params = [];
		$params['tokens'] = $tokens;
		$params['userSettings'] = $userSettings;

		//	$this->log()->info('User Settings Called For User:' . $this->user . ' Passing these parameters to the template: ' . print_r($params, true));
		return new TemplateResponse('firebasepushnotifications', 'settings/personal', $params, 'blank');
		/*		return new TemplateResponse('activity', 'settings/personal', [
					'activities'		=> $activities,
					'activity_email'	=> $this->config->getUserValue($this->user, 'settings', 'email', ''),

					'setting_batchtime'	=> $settingBatchTime,

					'notify_self'		=> $this->userSettings->getUserSetting($this->user, 'setting', 'self'),
					'notify_selfemail'	=> $this->userSettings->getUserSetting($this->user, 'setting', 'selfemail'),

					'methods'			=> [
						IExtension::METHOD_MAIL => $this->l10n->t('Mail'),
						IExtension::METHOD_STREAM => $this->l10n->t('Stream'),
					],
				], '');*/
	}


}