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

use OCP\AppFramework\Db\Entity;


/**
 * Class FirebaseErrorLog
 * @method void setCustomData(string $customData);
 * @method string getCustomData();
 * @method void setTimestamp(integer $timestamp);
 * @package OCA\Firebasepushnotifications\Entities
 */
class FirebaseErrorLog extends Entity {

	protected $timestamp;
	protected $customData;

	public function __toString() {
		return 'FirebaseErrorLog: '.$this->id.' . Timestamp: '.$this->timestamp.
			' CustomData: '.$this->customData.'.';

	}

	/**This function sets the current timestamp on ErrorLog entry
	 *
	 */
	public function setCurrentTimestamp(){
		$p = round(microtime(true) * 1000);
		$this->setTimestamp($p);
	}

}