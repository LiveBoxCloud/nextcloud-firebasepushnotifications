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
 * @method string getAppName()
 * @method string getServerKey();
 * @method void setAppName(string $var)
 * @method void setServerKey(string $key)
*/
class FirebaseAppKey extends Entity {

	/** @var string  */
	protected $appName;
	/** @var string  */
	protected $serverKey;


	/**Handy debug printout
	 * @return string
	 */
	public function __toString(){
		return 'FirebaseAppKey: '.
			'id: '.$this->getId().'. '.
			'AppName: '.$this->appName.'.'.
			'ServerKey: '.$this->serverKey.'.';

	}

	/**This function trims the end off the Firebase Key so that it can be displayed safely in the admin panel
	 * @return string with the trimmed key or n/a if not set or ... if zero length key
	 */
	public function getSafeServerKey(){
		if($this->serverKey ){
			$kL = strlen($this->serverKey);
			$trimmedLength = $kL>10 ? $kL-5 : 0;
			$trimmed = substr($this->serverKey,0,$trimmedLength).'...';
			return $trimmed;
		}return 'n/a';
	}
}