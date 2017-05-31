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



class DummyPushType {
	public $pushType;
	public $pushDescription;
	public $isEnabled;

	/**DummyPushTypes are descriptors for types of PushMessages
	 * DummyPushType constructor.
	 *
	 * @param $pushType string MessageStringID this is the key that will be used
	 * by the localisation methods for Translation dictionaries lookups
	 * @param $pushDescription string  The Human readable description of the push
	 * message type (Typically the action it denotes)
	 * @param $isEnabled bool  If this message type should be sent or discarded
	 */
	public function __construct($pushType, $pushDescription = '', $isEnabled = true) {
		$this->pushType = $pushType;
		$this->pushDescription = $pushDescription;
		$this->isEnabled = $isEnabled;
	}

	/**Handy override (Helpful in debug printouts)
	 * @return string
	 */
	public function __toString() {
		return 'PushType: pushType: ' . $this->pushType . ' pushDescription: ' . $this->pushDescription . ' ' . ($this->isEnabled ? 'enabled' : 'disabled');
	}

	/**Handy method to help deserialisation of DummyPushTypes from the
	 * PushTypesConfiguration entities
	 * @param $a array to use to build the DummyPushType
	 * @return DummyPushType
	 */
	public static function fromArray($a) {
		$dpt = new DummyPushType($a['pushType'], $a['pushDescription'], $a['isEnabled']);
		return $dpt;
	}

}