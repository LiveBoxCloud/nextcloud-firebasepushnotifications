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

use OCA\Firebasepushnotifications\Entities\FirebaseMessage;
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;
use PDO;

class FirebaseStorageHandler extends  Mapper {


	/**
	 * @var IDb
	 */
	protected $db;

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'firebase_saved_message', FirebaseMessage::class);
		$this->db = $db;
	}


	/**This function saves a FirebasePushMessage to be sent
	 * @param $userId
	 * @param $title
	 * @param $message
	 * @param array $replaceArray
	 * @param array $customData
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function saveFirebasePushMessage($userId, $title, $message, $replaceArray = [], $customData = []){
		$firebasePushMessage =  new FirebaseMessage();
		$firebasePushMessage->setUserId($userId);
		$firebasePushMessage->setTitle($title);
		$firebasePushMessage->setMessage($message);
		$firebasePushMessage->setEncodedCustomData($customData);
		$firebasePushMessage->setParams($replaceArray);
		$firebasePushMessage->setCurrentTimestamp();
		//\OC::$server->getLogger()->info('Saved Message: ' . $firebasePushMessage->toBaseEncodedMessage());
		return $this->insert($firebasePushMessage);
	}

	/**This function returns a set of messages to be sent
	 * @param int $howMany = 100 as default
	 * @return array
	 */
	public function getFirebaseMessagesToSend($howMany = 100){
		$query = 'SELECT * FROM `*PREFIX*firebase_saved_message`  where 1 ORDER BY id ASC LIMIT ? ';
		return $this->findEntities($query,[$howMany]);
	}

	/**This function Truncates the Firebase Saved Messages table
	 * @return \PDOStatement
	 */
	public function truncateSavedMessage(){
		$query = 'TRUNCATE TABLE `*PREFIX*firebase_saved_message` ';
		return $this->execute($query);
	}

	/**This function deletes saved messages by user id (Useful on user delete
	 * operations)
	 * @param $userId
	 * @return \PDOStatement
	 */
	public function deleteSavedMessagesByUserId($userId){
		$query = 'DELETE FROM `*PREFIX*firebase_saved_message`  where user_id = ? ';
		return $this->execute($query,[$userId]);
	}

	/**This function deletes saved messages by Id
	 * @param $id
	 * @return \PDOStatement
	 */
	public function deleteSavedMessageById($id){
		$query = 'DELETE FROM `*PREFIX*firebase_saved_message`  where id = ? ';
		$res = $this->execute($query,[$id]);
		return $res;
	}

	/**This function deletes saved messages given a list of IDs
	 * @param $Ids
	 * @return \PDOStatement
	 */
	public function deleteSavedMessagesWithIds($Ids){
		$query = 'DELETE FROM `*PREFIX*firebase_saved_message`  where id IN (' . implode(',', $Ids) . ')';
		return $this->execute($query);
	}

	/**Handy function to get the table name
	 * @return string
	 */
	public function getTableName() {
		return parent::getTableName();
	}

	/**This function deletes saved message using the Entity Mapper methods
	 * @param $messages
	 * @return int representing successful deletes
	 */
	public function deleteSavedMessages($messages){
		$deleted = 0;
		foreach ($messages as $message){
			if ($this->delete($message)) {
				$deleted++;
			}
		}
		return $deleted;
	}

	/**Handy function to count Saved Messages
	 * @return int|mixed -1 if the query fails
	 */
	public function countSavedMessages(){
		$query = 'SELECT COUNT(*) as n FROM `*PREFIX*firebase_saved_message` WHERE 1';
		$res = $this->execute($query);
		$count = -1;
		if($res && ($rs = $res->fetch(PDO::FETCH_ASSOC))){
			$count = $rs['n'];
		}
		return $count;

	}


}