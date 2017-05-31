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

namespace OCA\Firebasepushnotifications;

use OCA\Firebasepushnotifications\DB\FirebaseConfHandler;
use OCA\Firebasepushnotifications\DB\FirebaseStorageHandler;
use OCP\IDBConnection;
use OCP\Util;

/**
 * Handles the stream and mail queue of a user when he is being deleted
 */
class Hooks {
	/**
	 * Delete remaining activities and emails when a user is deleted
	 *
	 * @param array $params The hook params
	 */
	static public function deleteUser($params) {
		$confHandler = \OC::$server->query(FirebaseConfHandler::class);
		\OC::$server->getLogger()->info('Delete User params: ' . print_r($params, true));
		if (isset($confHandler)) {
			/** @var FirebaseConfHandler $confHandler */
			//	$confHandler->deleteUserSettings($userId);
		}
		$messageHandler = \OC::$server->query(FirebaseStorageHandler::class);
		if (isset($messageHandler)) {
			/** @var FirebaseStorageHandler $messageHandler */
			//	$messageHandler->deleteSavedMessagesByUserId($userId);
		}
	}

	/**
	 * Delete all items of the stream
	 *
	 * @param string $user
	 * @return bool
	 */
	static protected function deleteUserStream($user) {
		// Delete activity entries
		//TODO Delete Saved Tokens and Messages
		return true;
	}

	/**
	 * Delete all mail queue entries
	 *
	 * @param IDBConnection $connection
	 * @param string $user
	 */
	static protected function deleteUserMailQueue(IDBConnection $connection, $user) {
		// Delete entries from mail queue
		return;
	}

	static public function setDefaultsForUser($params) {
		return;
	}

	/**
	 * Load additional scripts when the files app is visible
	 */
	public static function onLoadFilesAppScripts() {
		//Util::addStyle('activity', 'style');
		//Util::addScript('activity', 'richObjectStringParser');
		//Util::addScript('activity', 'activitymodel');
		//Util::addScript('activity', 'activitycollection');
		//Util::addScript('activity', 'activitytabview');
		//Util::addScript('activity', 'filesplugin');
	}
}
