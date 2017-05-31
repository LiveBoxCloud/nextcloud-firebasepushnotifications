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

use OCA\Firebasepushnotifications\Entities\DummyShareData;

/**
 * The class to handle the filesystem hooks
 */
class FilesHooksStatic {

	/**
	 * @return FilesHooks
	 */
	static protected function getHooks() {
		return \OC::$server->query(FilesHooks::class);
	}

	/**
	 * @return MessageHooks
	 */
	static protected function getMessageHooks(){
		return \OC::$server->query(MessageHooks::class);
	}


	/**
	 * Store the create hook events
	 * @param array $params The hook params
	 */
	public static function fileCreate($params) {
		self::getHooks()->fileCreate($params['path']);
	}

	/**
	 * Store the update hook events
	 * @param array $params The hook params
	 */
	public static function fileUpdate($params) {
		self::getHooks()->fileUpdate($params['path']);
	}

	/**
	 * Store the delete hook events
	 * @param array $params The hook params
	 */
	public static function fileDelete($params) {
		self::getMessageHooks()->printParamsToLogs($params);
		self::getHooks()->fileDelete($params['path']);
	}

	/**
	 * Store the rename hook events
	 * @param array $params The hook params
	 */
	public static function fileMove($params) {
		self::getHooks()->fileMove($params['oldpath'], $params['newpath']);
		//	self::getMessageHooks()->genericStuff();
		self::getMessageHooks()->printParamsToLogs($params,'fileMove');
	}

	/**
	 * Store the rename hook events
	 * @param array $params The hook params
	 */
	public static function fileMovePost($params) {
		self::getHooks()->fileMovePost($params['oldpath'], $params['newpath']);
		//	self::getMessageHooks()->printParamsToLogs($params,'fileMovePost');
	}

	/**
	 * Store the restore hook events
	 * @param array $params The hook params
	 */
	public static function fileRestore($params) {
		self::getHooks()->fileRestore($params['filePath']);
	}

	/**
	 * Manage sharing events
	 * @param array $params The hook params
	 */
	public static function share($params) {
		self::getHooks()->share($params);
	}

	/**
	 * Unsharing event
	 * @param array $event
	 */
	public static function unShare($event) {
		$dummyShareData = new DummyShareData($event);
		//	self::getMessageHooks()->printParamsToLogs($event,'Unshare has been called!');
		self::getHooks()->unShare($dummyShareData);
	}
}
