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
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method void setCustomData(string $customData)
 * @method string getCustomData()
 * @method void setMessage(string $message)
 * @method string getMessage()
 * @method void setTitle(string $title)
 * @method string getTitle()
 * @method void setTimestamp(integer $timestamp)
 * @method integer getTimestamp()
 */
class FirebaseMessage extends Entity {
	/** @var int /
	public $id;
	/** @var  string */
	protected $userId;
	/** @var  string */
	protected $title;
	/** @var  string */
	protected $message;
	/** @var $customData */
	protected $customData;
	protected $timestamp;

	public function __toString(){
		return
			'FirebaseMessage: UserID: '.$this->getUserId().'. '
			.' Timestamp: '.$this->getTimestamp() . '. '
			.' Title: '.$this->getTitle() .'. '
			.' Message: '.$this->getMessage().'. '
			.' CustomData: '.$this->getCustomData() ;
	}

	public function toBaseEncodedMessage() {
		return
			'FirebaseMessage: BASEENCODED: ' . $this->getUserId() . '. '
			. ' Timestamp: ' . $this->getTimestamp() . '. '
			. ' Title: ' . $this->getTitle() . '. '
			. ' Message: ' . $this->getMessage() . '. '
			. ' CustomData: ' . base64_encode($this->getCustomData());
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/** This function requires an array
	 * @param array $data
	 */
	public function setEncodedCustomData($data){
		if(!is_array($data)){
			$data = [];
		}
		$this->setCustomData(json_encode($data));
	}

	/**Use this function to store parameters to be substituted for localisable messages WARNING: This method overrides set params
	 * @param $params
	 */
	public function setParams($params){
		if(!$this->customData){
			$this->setEncodedCustomData([]);
		}
		if(!is_array($this->customData)){
			$this->customData = json_decode($this->customData,true);
		}
		$this->customData['loc_params'] = $params;
		$this->setCustomData(json_encode($this->customData));
	}

	/**This function returns parameters for localisable messages
	 * @return array
	 */
	public function getParams(){
		$fetched = null;
		if(!$this->customData){
			return [];
		}
		if(!is_array($this->customData)){
			$fetched = json_decode($this->customData,true);
			$fetched = $fetched['loc_params'];
		}else {
			$fetched = $this->customData['loc_params'];
		}
		return $fetched ? $fetched : [];
	}

	/**handy function to set timestamp on the entitys
	 *
	 */
	public function setCurrentTimestamp(){
		$p = round(microtime(true) * 1000);
		$this->setTimestamp($p);
	}

	/**This function return the CustomData field as an associative array
	 * @return array|mixed|string
	 */
	public function getCustomDataAsArray(){
		$c = $this->getCustomData();
		if($c){
			if(is_array($c)){
				return $c;
			}else{
				$c = json_decode($c,true);
				if(is_array($c)){
					return $c;
				}else{
					return [];
				}
			}
		}else{
			return [];
		}
	}

	/**Whether the push target and actor are the same userId
	 * @return bool
	 */
	public function isSameActorAndTarget() {
		return $this->getTargetUserId() === $this->getActorUserId();
	}

	/**Whether the Notification subject is shared
	 * @return bool
	 */
	public function isAShare() {
		return !$this->isSameActorAndTarget();
	}

	const USER_TARGET_KEY = 'userTarget';
	const USER_ACTOR_KEY = 'userActor';

	/**This function returns the Target user id
	 * @return mixed
	 */
	public function getTargetUserId() {
		return $this->getParams()[self::USER_TARGET_KEY];
	}

	/**This function returns the target user id as an array (Useful for the
	 * Localisation functions)
	 * @return array
	 */
	public function getTargetUserIdAsArray() {
		$targetUser = $this->getTargetUserId();
		if ($targetUser) {
		//	\OC::$server->getLogger()->debug('User Target ParamFound: ' . self::USER_TARGET_KEY . ' v:' . $targetUser);
			return [self::USER_TARGET_KEY => $this->getParams()[self::USER_TARGET_KEY]];
		}
		//\OC::$server->getLogger()->info('Failed to return user target as array: ' . $targetUser);
		return [];
	}

	/**This function returns the Acting user id
	 * @return mixed
	 */
	public function getActorUserId() {
		return $this->getParams()[self::USER_ACTOR_KEY];
	}

	/**This function sets the Acting user id
	 * @param $userId
	 */
	public function setActorUserId($userId){
		$p = $this->getParams()[self::USER_ACTOR_KEY] = $userId;
		$this->setParams($p);
	}
}

