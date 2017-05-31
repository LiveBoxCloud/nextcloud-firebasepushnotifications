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

namespace OCA\Firebasepushnotifications;


use OC\Files\View;
use OCA\Firebasepushnotifications\DB\FirebaseConfHandler;
use OCA\Firebasepushnotifications\DB\FirebaseStorageHandler;
use OCA\Firebasepushnotifications\DB\FirebaseTokenHandler;
use OCA\Firebasepushnotifications\Entities\FirebaseMessage;
use OCA\Firebasepushnotifications\Sender\FirebaseSender;
use OCP\Activity\IManager;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\ILogger;
use OCP\IURLGenerator;


class MessageHooks {

	/** @var \OCP\Activity\IManager */
	protected $manager;

	/** @var \OCA\Firebasepushnotifications\Data */
	protected $activityData;

	/** @var \OCA\Firebasepushnotifications\UserSettings */
	protected $userSettings;

	/** @var \OCP\IGroupManager */
	protected $groupManager;

	/** @var \OCP\IDBConnection */
	protected $connection;

	/** @var \OC\Files\View */
	protected $view;

	/** @var IURLGenerator */
	protected $urlGenerator;

	/** @var ILogger */
	protected $logger;

	/** @var CurrentUser */
	protected $currentUser;

	/** @var string|bool */
	protected $moveCase = false;
	/** @var string[] */
	protected $oldParentUsers;
	/** @var string */
	protected $oldParentPath;
	/** @var string */
	protected $oldParentOwner;
	/** @var string */
	protected $oldParentId;

	private $_messageStore;

	/**
	 * Constructor
	 *
	 * @param IManager $manager
	 * @param Data $activityData
	 * @param UserSettings $userSettings
	 * @param IGroupManager $groupManager
	 * @param View $view
	 * @param IDBConnection $connection
	 * @param IURLGenerator $urlGenerator
	 * @param ILogger $logger
	 * @param CurrentUser $currentUser
	 */
	public function __construct(IManager $manager, Data $activityData, UserSettings $userSettings, IGroupManager $groupManager, View $view, IDBConnection $connection, IURLGenerator $urlGenerator, ILogger $logger, CurrentUser $currentUser) {
		$this->manager = $manager;
		$this->activityData = $activityData;
		$this->userSettings = $userSettings;
		$this->groupManager = $groupManager;
		$this->view = $view;
		$this->connection = $connection;
		$this->urlGenerator = $urlGenerator;
		$this->logger = $logger;
		$this->currentUser = $currentUser;
	}

	/**This function fetches the Message Store handler
	 * @return FirebaseStorageHandler | null
	 */
	protected function getMessageStore(){
		if(!$this->_messageStore){
			$this->_messageStore = \OC::$server->query(FirebaseStorageHandler::class);
		}
		//To Move to Util
		return $this->_messageStore;

	}

	/**This function saves a new FirebaseMessage on the db, based on the given
	 * data
	 * @param $userId string
	 * @param $message string (The PushType)
	 * @param $replaceArray array (The Localisation substitution parameters)
	 * @param $customData array (Extra custom data to be added to the message
	 * payload)
	 */
	public function storeMessage($userId, $message, $replaceArray, $customData){
		$defaultTestTile = 'base_title';
		$this->getMessageStore()->saveFirebasePushMessage($userId,$defaultTestTile,$message,$replaceArray,$customData);
	}

	/**Handy Test Function
	 * @param $userID
	 * @param string $testTitle
	 * @param string $message
	 */
	public function sendTestMessageToUser($userID, $testTitle = 'Test Message', $message = 'rename_file'){
		$this->getMessageStore()->saveFirebasePushMessage($userID,$testTitle,$message,[FirebaseMessage::USER_TARGET_KEY => $userID, FirebaseMessage::USER_ACTOR_KEY => 'someoneelse']);
	}


	/**Runs a test on the Push Save -> Send cycle
	 *
	 */
	public function genericStuff(){
		//$this->logger->info("I got called! Yay!");
		if($this->connection) {

			$this->logger->info("I got a DB. 1");
			$messageStore = $this->getMessageStore();
			$this->logger->info('Message Store Class: ' . (get_class($messageStore)));
			if ($messageStore) {
				$a = $messageStore->countSavedMessages();
				$this->logger->info('Saved Messages: '.$a);
		//		$this->testFirebaseConfHandler();
				//$this->testTokenHandler();
				$fs = \OC::$server->query(FirebaseSender::class);
				/** @var FirebaseSender $fs */
				if($fs){
					$fs->testSenderModule();
				}
			}
		}
	}

	/**Handy debugging function
	 * @param $params
	 * @param string $methodName
	 */
	public function printParamsToLogs($params, $methodName = 'n/a'){
		$this->logger->info('Call Parameters for '.$methodName.': '.print_r($params,true));
	}

	/**Directly invokes the background sender task
	 *
	 */
	public function manualPushSend() {
		/** @var FirebaseSender $firebaseSender */
		$firebaseSender = $fs = \OC::$server->query(FirebaseSender::class);
		$firebaseSender->manualRun();
		$this->logger->info('Manual Run Completed');

	}

	/**This function tests loading of the FirebaseTokenHandler
	 *
	 */
	private function testTokenHandler(){
		/** @var FirebaseTokenHandler $tokenHandler */
		$tokenHandler = \OC::$server->query(FirebaseTokenHandler::class);
		$tokenHandler->testTokenMapper();
	}

	/**This function tests
	 *
	 */
	private function testFirebaseConfHandler(){
		$ostia = \OC::$server->query(FirebaseConfHandler::class);
		$ostia->testHandler();
	}

	private function testStoreMessage(){
		/** @var FirebaseStorageHandler $messageStore */
		$messageStore = $this->getMessageStore();
		for($i=0; $i<10; $i++){
			$messageStore->saveFirebasePushMessage('Pino'.$i,'Test Massage'.$i,'Hamstard Rulez'.$i,  [ 'pino'=>$i] );
		}
		$this->logger->info( $messageStore->countSavedMessages(). ' Saved Messages');
		$messages = $messageStore->getFirebaseMessagesToSend();
		//$this->logger->info('Fetched: '.print_r($messages,true));
		if($messages){
			/** @var FirebaseMessage $message */
			foreach ($messages as $message){
				if(is_a($message, FirebaseMessage::class)){
					$this->logger->info('Saved Message: '.$message);
					if ($message->getId() % 2 === 0) {
						$this->logger->info('Deleting Message '. $messageStore->deleteSavedMessageById($message->getId()));
					}else{
						$this->logger->info('Deleting Message by userId'. $messageStore->deleteSavedMessagesByUserId($message->getUserId()));

					}
				}
			}
			$this->logger->info('Test Message Store truncate: '.$messageStore->truncateSavedMessage());
			$this->logger->info('New Message Count: '.$messageStore->countSavedMessages());

		}else{
			$this->logger->error('Could not fetch stored messages');
		}

	}
}