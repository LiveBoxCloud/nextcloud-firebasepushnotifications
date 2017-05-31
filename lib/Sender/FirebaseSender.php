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


use \Exception;
use OC\BackgroundJob\TimedJob;
use OCA\Firebasepushnotifications\DB\FirebaseConfHandler;
use OCA\Firebasepushnotifications\DB\FirebaseStorageHandler;
use OCA\Firebasepushnotifications\DB\FirebaseTokenHandler;
use OCA\Firebasepushnotifications\Entities\FirebaseToken;
use OCA\Firebasepushnotifications\Entities\FirebaseMessage;
use OCA\Firebasepushnotifications\Entities\PushTypesConfiguration;
use OCA\Firebasepushnotifications\Localisation\Localiser;
use OCP\IDb;
use OCP\ILogger;


class FirebaseSender extends TimedJob {

	private $db;
	private $storageHandler;
	private $confHandler;
	private $tokenHandler;
	private $log ;


	private $responseHandler;

	/**Lazily fetches a FirebaseResponseHandler
	 * @return FirebaseResponseHandler
	 */
	private function getResponseParser(){
		if(!$this->responseHandler){
			$this->responseHandler = new FirebaseResponseHandler();
		}return $this->responseHandler;
	}

	private $localiser;

	/**Lazily Fetches a Message Localiser
	 * @return Localiser
	 */
	private function getLocaliser(){
		if(!$this->localiser){
			$this->localiser = new Localiser();
		}return $this->localiser;
	}

	private $userSettingsCache;

	/**This function fetches the UserSettings Cache
	 * @return array
	 */
	private function getUserSettingsCache(){
		if(!$this->userSettingsCache){
			$this->userSettingsCache = [];
		}return $this->userSettingsCache;
	}

	/**This function adds a User's PushTypesConfiguration to the Sender cache
	 * @param $userId
	 * @param $userSettings
	 */
	private function addToUserSettingsCache($userId, $userSettings){
		$this->userSettingsCache[$userId] = $userSettings;
	}

	/** This utility lazily fetches user settings DUH!
	 * @param $userId
	 * @return PushTypesConfiguration for the user (Possibly Default created if they lack settings)
	 */
	private function getUserSettings($userId){
		$userSettings = null;
		if(!isset($this->getUserSettingsCache()[$userId])){
			$userSettings = $this->confHandler->getUserPushSettings($userId);
			$this->addToUserSettingsCache($userId,$userSettings);
			return $userSettings;
		}
		return $this->getUserSettingsCache()[$userId];
	}


	private $_pushTypesConf;

	/**Lazy System configuration fetcher
	 * @return PushTypesConfiguration|string
	 */
	private function getPushConf(){
		if (!$this->_pushTypesConf) {
			$this->_pushTypesConf = $this->confHandler->getPushTypesConfiguration();
		}return $this->_pushTypesConf;
	}

	/**Whether a given push type is enabled at the System level
	 * @param $pushType
	 * @return bool
	 */
	private function getPushTypeEnabled($pushType) {
		return $this->getPushConf()->getPushTypeEnabled($pushType);
	}

	/**Whether PushSending is enabled at the System level
	 * @return bool
	 */
	private function getPushSendEnabled(){
		return $this->getPushConf()->getSendEnabled();
	}

	/**Whether system configuration allows SameUserSend
	 * @return bool
	 */
	private function getSendToSameUser(){
		return $this->getPushConf()->getSendToSameUser();
	}

	/**This utility function  checks whether a message should be sent based on
	 * user and system SameUserSend settings
	 * @param FirebaseMessage $firebaseMessage
	 * @param PushTypesConfiguration $userSettings
	 * @return bool
	 */
	private function skipBaseOnSameUserSendPolicies($firebaseMessage){
		/** @var FirebaseMessage $firebaseMessagesToSend */
		//$this->log->debug('Firebase Message Actor: ' . $firebaseMessage->getActorUserId() . 'Target: ' . $firebaseMessage->getTargetUserId() . ' Messagetype: ' . $firebaseMessage->getMessage());
		if($firebaseMessage->isSameActorAndTarget()){
			if(!$this->getUserSettings($firebaseMessage->getTargetUserId())->getSendToSameUser()){
				$this->log->debug('Identified same actor and target, skipping.');
				return true;
			}else if(!$this->getSendToSameUser()){
				$this->log->debug('Identified same actor and target by user, skipping.');
				return true;
			}
		}return false;
	}

	/**This utility function wraps the Sending Policy Checks
	 * @param FirebaseMessage $firebaseMessage
	 * @return bool
	 */
	private function skipBasedOnPolicies($firebaseMessage){
		if($this->skipBasedOnPushSendPolicies($firebaseMessage) ||
			$this->skipBasedOnPushTypeSendPolicy($firebaseMessage) ||
			$this->skipBaseOnSameUserSendPolicies($firebaseMessage)){
			return true;
		}return false;
	}

	/** This function checks whether general push sending should be skipped (Or if the user disabled push messages)
	 * @param FirebaseMessage $firebaseMessage
	 * @return bool
	 */
	private function skipBasedOnPushSendPolicies($firebaseMessage){
		if(!$this->getPushSendEnabled() || !$this->getUserSettings($firebaseMessage->getTargetUserId())->getSendEnabled()){
			$this->log->debug('Push Send has been disabled.');
			return true;
		}return false;
	}

	/**This utility function checks whether a message send should be skipped
	 * due to User or System PushType Send Settings
	 * @param FirebaseMessage $firebaseMessage
	 * @return bool
	 */
	private function skipBasedOnPushTypeSendPolicy($firebaseMessage){
		$messageType = $firebaseMessage->getMessage();
		if(!$this->getPushTypeEnabled($messageType) || !$this->getUserSettings($firebaseMessage->getTargetUserId())->getPushTypeEnabled($messageType)){
			return true;
		}return false;
	}

	/**
	 * FirebaseSender constructor.
	 *
	 * @param FirebaseConfHandler $confHandler
	 * @param FirebaseTokenHandler $tokenHandler
	 * @param FirebaseStorageHandler $firebaseStorageHandler
	 * @param IDb $db
	 * @param ILogger $logger
	 */
	public function __construct(FirebaseConfHandler $confHandler, FirebaseTokenHandler $tokenHandler, FirebaseStorageHandler $firebaseStorageHandler, IDb $db, ILogger $logger) {
		$this->setInterval(30);

		$this->db = $db;
		/** @var FirebaseStorageHandler $storageHandler */
		$this->storageHandler = $firebaseStorageHandler;
		$this->confHandler = $confHandler;
		$this->tokenHandler = $tokenHandler;
		$this->log = $logger;
		$this->log->debug('Push Sender Background Job Initialized');
//		$this->messageSender();
		//$localiser = new Localiser();
		//$localiser->selfTest();
	}


	/** This function fetches messages to be sent and takes care of checking
	 * sending policies, localisation and table cleanup while it's at it.
	 *
	 */
	private function messageSender(){
		$start = microtime();
		$firebaseMessagesToSend = $this->storageHandler->getFirebaseMessagesToSend();
//		$fMessages = 0;
		$skippedMessages = 0;
		$actualSends = 0;
		$this->log->info('Message Sender Run Started: Fetched: ' . count($firebaseMessagesToSend));
		if ($firebaseMessagesToSend) {
			/** @var FirebaseMessage $firebaseMessage */
			$serverKey = $this->confHandler->getServerKey();
			if($serverKey) {
				$messageIdsToDelete = [];
				foreach ($firebaseMessagesToSend as $firebaseMessage) {
					$messageIdsToDelete[] = $firebaseMessage->getId();
					if($this->skipBasedOnPolicies($firebaseMessage)){
						$skippedMessages++;
						continue;
					}
					$actualSends += $this->sendMessage($firebaseMessage,$serverKey);
					//$del = $this->storageHandler->deleteSavedMessageById($firebaseMessage->getId());
					//$this->log->debug('Sent Message Delete, Res. '.print_r($del,true));
				}
				$this->storageHandler->deleteSavedMessagesWithIds($messageIdsToDelete);
				$this->log->debug('Send Run Finished: ' . count($firebaseMessagesToSend) . ' TotalSends: ' . $actualSends . ' Time: ' . (microtime() - $start));

			}else{
				$this->log->error('Missing Server Key');
			}

		}else{
			$this->log->info('No Messages to send');
		}
	}

	/**This function sends a FirebaseMessage off to Google Servers, takes care
	 * of target token fetching, localisation (and might make coffee some day
	 * if I'm in luck)
	 * @param FirebaseMessage $firebaseMessage
	 * @param string $serverKey
	 * @return int the number of sent messages
	 */
	private function sendMessage($firebaseMessage, $serverKey){
		$handledMessages = 0;
		$actualSends = 0;
		$tokens = $this->tokenHandler->getTokensForUser($firebaseMessage->getUserId());
		if ($tokens && count($tokens) > 0) {
			/** @var FirebaseToken $token */
			foreach ($tokens as $token) {
				$s =  microtime();
				/** @var string $message */
				$message = $this->prepareMessage($firebaseMessage, $token);
				/** @var string $serverKey */
				$resp = $this->sendToFirebase($message, $serverKey);
				$actualSends++;
				$this->log->debug('Firebase Sent, Token' . $token . '  EncodedMessage:' . base64_encode($message) . '  Response: ' . $resp . ' Time: ' . (microtime() - $s));
			}
			$handledMessages++;
		}
		$this->log->debug('SendMessage handled: ' . $handledMessages . ' messages');
		return $actualSends;
	}

	/**This function sends a single message on it's way to Google Firebase
	 * Servers
	 *
	 * @param $message string localised string message
	 * @param $serverKey string server key to be used
	 * @param string $token the destination token
	 */
	private function sendToFirebase($message, $serverKey, $token = ''){
		$ch = null;
		try{
			$ch = curl_init("https://fcm.googleapis.com/fcm/send");
			$header=array('Content-Type: application/json', 'charset=UTF-8', 'Authorization: key='.$serverKey);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			$ret = @curl_exec($ch);
			if($ret){
				$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$this->getResponseParser()->handleResponse($code,$ret,$token);
//				$this->log->info("Firebase CURL Result: ".$ret);
			}

		}catch (Exception $e){
			$this->log->error('An error occurred while sending to Firebase: '.$e->getMessage());
			$this->log->error($e->getTraceAsString());
		}finally{
			curl_close($ch);
		}
	}

	/**
	 * @param FirebaseMessage $firebaseMessage
	 * @param FirebaseToken $token
	 * @return string
	 */
	private function prepareMessage(FirebaseMessage $firebaseMessage, $token){
		$resJson = [];
		$resJson['to'] = $token->getToken();
		$resJson['time_to_live'] = 2300000;
		$resJson['priority'] = 'high';
		if($token->isIOSToken()){
			$resJson['badge'] = "1";
		}
		$payload = [];
		$payload['title'] = $this->getLocaliser()->localiseMessage($firebaseMessage->getTitle(), $firebaseMessage->getTargetUserIdAsArray(), $token->getLocale());
		$payload['body'] = $this->getLocaliser()->localiseMessage($firebaseMessage->getMessage(),$firebaseMessage->getParams(),$token->getLocale());
		$resJson['notification'] = $payload;
		$resJson['data'] = $firebaseMessage->getCustomDataAsArray();
//		$this->log->info('Firebase Message JSON data field: |'.json_encode($resJson.'|');
		return json_encode($resJson);
	}


	/** TimeJob Runner, so basically TimeRunner
	 * @param $argument
	 */
	protected function run($argument) {
		$messageQueue = $this->storageHandler->countSavedMessages();
		if($messageQueue>0){
			$this->messageSender();
		}else {
			//QUIT
		}
	}

	/**This function directly calls the sender method
	 *
	 */
	public function manualRun() {
		$messageQueue = $this->storageHandler->countSavedMessages();
		if ($messageQueue > 0) {
			$this->messageSender();
		} else {
			//QUIT
		}
	}

	/**This function runs a basic test of the sender module
	 *
	 */
	public function testSenderModule(){
		$serverKey = $this->confHandler->getServerKey();
		$message = new FirebaseMessage();
		$message->setCurrentTimestamp();
		$message->setMessage('test');
		$message->setTitle('Ciao Pino');
		$message->setEncodedCustomData([]);
		$message->setUserId('testUser');
		$replaceArray =  ['%filename%'=>'Nome del file.xlsx', '%filedirectory%' => 'Percorsoneplusplus'] ;
		$message->setParams($replaceArray);
		$token = new FirebaseToken();
		$token->setUserId('testUser');
		$token->setDeviceType(FirebaseToken::IOS_DEVICE);
		$token->setLocale('it_IT');
		$token->setLastUsed(0);
		$token->setToken('3anelliairedeignomisottoilcielocherisplende');
		/** @var string $preparedMessage */
		$preparedMessage = $this->prepareMessage($message,$token);
	//	$this->log->info('Fetched Server Key: '.$serverKey.' Prepared message:||'.base64_encode($preparedMessage).'||');
		if($serverKey){
			$this->sendToFirebase($preparedMessage, $serverKey,$token->getToken());
		}else {
			$insertTestKeyHere = "";
			$this->sendToFirebase($preparedMessage, $insertTestKeyHere, $token->getToken());
		}
	}



}