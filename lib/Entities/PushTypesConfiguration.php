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


class PushTypesConfiguration {

	public $pushTypes;
	public $sendToSameUser;
	public $sendEnabled;

	public function __construct() {
		$this->pushTypes = [];
	}

	public static function getDefault() {
		$conf = new PushTypesConfiguration();
		$conf->setSendEnabled(true);
		$conf->setSendToSameUser(false);
		$conf->addPushType(new DummyPushType('create_file', 'This notification occurs when a file is created on the server'));
		$conf->addPushType(new DummyPushType('create_folder', 'This notification occurs when a folder is created on the server'));
		$conf->addPushType(new DummyPushType('update_file', 'This notification occurs when a file is updated on the server'));
		$conf->addPushType(new DummyPushType('move_file', 'This notification occurs when a file (or folder) is moved on the server'));
		$conf->addPushType(new DummyPushType('rename_file', 'This notification occurs when a file (or folder) is renamed on the server'));
		$conf->addPushType(new DummyPushType('restore_file', 'This notification occurs when a file (or folder) is restored from the recycle bin on the server'));
		$conf->addPushType(new DummyPushType('share_file', 'This notification occurs when a file is shared on the server'));
		$conf->addPushType(new DummyPushType('share_folder', 'This notification occurs when a folder is shared on the server'));
		$conf->addPushType(new DummyPushType('unshare_file', 'This notification occurs when a file is unshared on the server'));
		$conf->addPushType(new DummyPushType('unshare_folder', 'This notification occurs when a folder is unshared on the server'));
		return $conf;
	}

	/**This function adds or updates a DummyPushType entry in the configuration
	 * @param DummyPushType $newPushType
	 */
	public function addPushType(DummyPushType $newPushType) {
		if (is_a($newPushType, DummyPushType::class)) {
			$this->pushTypes[$newPushType->pushType] = $newPushType;
		} else {
			\OC::$server->getLogger()->error('Invalid object passed : ' . $newPushType);
		}
	}

	/**This function retrieves a specific DummyPushType configuration
	 * @param $type the string type of the DummyPushType to fetch
	 * @return DummyPushType |null if not found
	 */
	public function getPushType($type) {
		return isset($this->getPushTypes()[$type]) ? $this->getPushTypes()[$type] : null;
	}

	/**This function build a PushTypeConfiguration from the serialized JSON that
	 * is saved in the database
	 * @param $json
	 * @return PushTypesConfiguration
	 */
	public static function fromJSON($json) {
		$t = json_decode($json, true);
		$pushTypesConfiguration = new PushTypesConfiguration();
		if (isset($t['sendEnabled'])) {
			$pushTypesConfiguration->setSendEnabled($t['sendEnabled']);
		}
		if (isset($t['sendToSameUser'])) {
			$pushTypesConfiguration->setSendToSameUser($t['sendToSameUser']);

		}
		if (isset($t['pushTypes'])) {
			$pushTypes = $t['pushTypes'];
			$formatted = [];
			foreach ($pushTypes as $key => $val) {
				$formatted[$key] = DummyPushType::fromArray($val);
			}
			$pushTypesConfiguration->pushTypes = $formatted;
		} else {
			//Empty object returning empty conf
			\OC::$server->getLogger()->info('Could not load PushTypes Configuration from :' . $json . '.');
		}
		return $pushTypesConfiguration;
	}

	/**This function returns the configuration push types
	 * @return array DummyPushType
	 */
	public function getPushTypes() {

		return $this->pushTypes;
	}

	/** This function checks whether a given push type is enabled
	 * @param $pushType
	 * @return bool True if type is defined and enabled or false if it isn't or isn't defined
	 */
	public function getPushTypeEnabled($pushType) {
		$t = $this->getPushType($pushType);
		return $t && $t->isEnabled;
	}

	/**Handy for debug printouts
	 * @return string
	 */
	public function __toString() {
		$toString = "PushTypesConfiguration: ";
		foreach ($this->getPushTypes() as $key => $value) {
			$toString .= 'Type' . $key . ' as ' . $value . ' ';
		}
		$toString .= 'Send status: ' . ($this->getSendEnabled() ? 'Enabled' : 'Disabled') . ' ';
		$toString .= 'SendPolicy: ' . ($this->getSendToSameUser() ? ' Same User Included' : 'DifferentOnly ') . ' ';
		return $toString;
	}

	/**Whether Push Messages should be forwarded to the Acting user devices
	 * @return bool
	 */
	public function getSendToSameUser() {
		if (!isset($this->sendToSameUser)) {
			$this->sendToSameUser = false;
		}
		return $this->sendToSameUser;
	}

	/**Sets whether push messages should be send to the acting user or not
	 * @param bool $enabled
	 */
	public function setSendToSameUser($enabled = false) {
		$this->sendToSameUser = $enabled;
	}


	/**Whether PushNotification sending is enabled
	 * @return bool
	 */
	public function getSendEnabled() {
		if (!isset($this->sendEnabled)) {
			$this->sendEnabled = true;
		}
		return $this->sendEnabled;
	}

	/**Enables or disables push notification sending
	 * @param bool $enabled = true
	 */
	public function setSendEnabled($enabled = true) {
		$this->sendEnabled = $enabled;
	}
}