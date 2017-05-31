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
 * Class FirebaseToken
 * @method string getUserId()
 * @method string getToken()
 * @method string getLocale()
 * @method string getResource()
 * @method string getLastUsed()
 * @method string getDeviceType()
 * @method void setUserId(string $userId)
 * @method void setToken(string $token)
 * @method void setLocale(string $locale)
 * @method void setResource(string $resource)
 * @method void setLastUsed(integer $lastUsed)
 * @method void setDeviceType(integer $deviceType)
 * @package OCA\Firebasepushnotifications\DB
 */
class FirebaseToken extends Entity
{
	public $id;
	protected $userId;
	protected $token;
	protected $locale;
	protected $resource;
	protected $lastUsed;
	protected $deviceType;

	const IOS_DEVICE =1;
	const ANDROID_DEVICE = 2;


	/**Whether the Device for this token is iOS based
	 * @return bool
	 */
	public function isIOSToken(){
		return $this->deviceType === self::IOS_DEVICE;
	}

	/**Whether the Device for this token is Android based
	 * @return bool
	 */
	public function isAndroidToken(){
		return $this->deviceType === self::ANDROID_DEVICE;
	}

	/**Returns the device type for this token as a string
	 * @return string
	 */
	public function getDeviceTypeAsString() {
		return $this->isIOSToken() ? 'IOS' : 'Android';
	}

	/**Trimmed token (Useful for display in UI ) Removes a few characters off of
	 * the original token
	 * @return string
	 */
	public function getClippedToken() {
		$tk = $this->getToken();
		$len = strlen($tk);
		if ($tk && $len > 4) {
			return substr($tk, 0, (strlen($tk) / 2) - 1) . '...';
		}
		return 'n/a';
	}

	/**Return a readable Last Used time value
	 * @return false|string
	 */
	public function getReadableLastUsed() {
		if ($this->getLastUsed()) {
			return date('d/m/Y H:i:s', $this->getLastUsed());
		}
		return 'n/a';
	}

	/**Handy override for debug printouts
	 * @return string
	 */
	public function __toString(){
		return 'FirebaseToken: Id: '.$this->getId().' UserId: '.$this->getUserId().
			'. Token: '.$this->getToken().'. Locale: '.$this->getLocale().
			'. Resource: '.$this->getResource().'. LastUsed: '.$this->getLastUsed().'. DeviceType: '.$this->getDeviceType().'.';

	}

}