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

/**
 * Created by PhpStorm.
 * User: paolo
 * Date: 18/04/2017
 * Time: 17:17
 */

namespace OCA\Firebasepushnotifications\DB;


use OCA\Firebasepushnotifications\Entities\DummyPushType;
use OCA\Firebasepushnotifications\Entities\PushTypesConfiguration;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Mapper;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\Firebasepushnotifications\Entities\FirebaseAppKey;
use OCP\IConfig;
use OCP\IDb;

class FirebaseConfHandler extends Mapper{

	/** Db reference
	 * @var IDb
	 */
	protected  $db;
	private $_log;
	protected $config;
	const APP_NAME = 'firebasepushnotifications';
	const PUSH_CONFIG_KEY = 'push_config';

	/**
	 * @return \OCP\ILogger
	 */
	private function log(){
		if(!$this->_log){
			$this->_log = \OC::$server->getLogger();
		}
		return $this->_log;
	}


	public function __construct(IConfig $config, IDb $db) {
		parent::__construct($db, 'firebase_configuration', FirebaseAppKey::class);
		$this->db = $db;
		$this->config = $config;
	}

	/**Performs an insert or update operation on the FirebaseAppKey object
	 * @param string $serverKey
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function saveServerKey($serverKey){
		$res =  $this->getServerConfiguration();
		$conf = new FirebaseAppKey();
		$conf->setAppName('drive');
		$conf->setServerKey($serverKey);
		if(is_a($res,FirebaseAppKey::class)){
			$conf->setId($res->getId());
			$conf->setAppName($res->getAppName());
			return $this->update($conf);
		}
		return $this->insert($conf);
	}

	/**This handy function retrieves the Firebase server key
	 * @return string representing the App Key or False
	 */
	public function getServerKey(){
		$res = $this->getServerConfiguration();
		if($res && is_a($res,FirebaseAppKey::class)){
			/** @var FirebaseAppKey $res */
			$this->log()->debug('Fetched Server KEy: ' . $res);
			return $res->getServerKey();
		}else{
			$this->log()->error('Failed to Fetch server configuration: ' . $res);
			return false;
		}

	}

	/** Returns the FirebaseAppKey entity or false if not found or exception
	 * @return bool|FirebaseAppKey
	 */
	public function getServerConfiguration(){
		$serverKey = false;
		$query = 'SELECT * FROM `*PREFIX*firebase_configuration`  where 1';
		try {
			/** @var FirebaseAppKey $res */
			$res = $this->findEntity($query);
			//	$this->log()->info('Get Server Configuration Debug: '.$query.' Result: '.$res);
			if(is_a($res,FirebaseAppKey::class)){
				return $res;
			}
		}catch(DoesNotExistException $dne){
			$this->log()->error('Server Configuration Not found on db');
		}catch (MultipleObjectsReturnedException $more){
			$this->log()->error('Multiple Server Configurations found');
		}
		return $serverKey;
	}

	/**Deletes saved FirebaseAppKey objects
	 * @return bool
	 */
	public function deleteKeys(){
		$res = $this->getServerConfigurations();
		$deleted = 0;
		if($res){
			/** @var FirebaseAppKey $key */
			foreach ($res as $key){
				$this->delete($key);
				$deleted++;
			}
		}
		return $deleted>0;
	}

	/**Returns an array of FirebaseAppKey entities
	 * @return array
	 */
	public function getServerConfigurations(){
		$query = 'SELECT * FROM `*PREFIX*firebase_configuration`  where 1';
		$res = $this->findEntities($query);
		return $res;
	}

	/**This is a test function that calls the Server Configuration fetching
	 * methods
	 *
	 */
	public function testHandler() {
		$this->log()->info('Deleting saved Entity: '.$this->deleteKeys());
		$this->log()->info('Testing Firebase Configuration Handler');
		$conf = $this->getServerConfiguration();
		$this->log()->info('Testing Firebase Current Configuration' . $conf);

//		$this->log()->info('Server Configuration: '.print_r($conf,true));
		$key = $this->getServerKey();
		$this->log()->info('Server key: ' . $key);
		$c = new FirebaseAppKey();
		$c->setAppName('TestAppName');
		$c->setServerKey('TheKey is Sette!'); //There are reasons 7 is the definitive number
		$this->log()->info('Object to Save: ' . $c->__toString());
		$this->log()->info('Test insert: ' . $this->insert($c));
		$conf = $this->getServerConfiguration();
//		$this->log()->info('Pulling saved Server Configuration: '.print_r($conf,true));
		$key = $this->getServerKey();
		if (is_a($key, FirebaseAppKey::class)) {

//		$this->log()->info('Pulling Server key: '.print_r($key,true));
			$this->log()->info('Object to Save: ' . $conf->__toString() . ' ' . $key);
			$conf= $this->getServerConfiguration();
			$this->log()->info('Server Configuration: should now be empty: '.print_r($conf,true));

		}


		$this->log()->info('Finished Testing Firebase Configuration Handler');
	}

	/**This function sets the defaults on the database and returns the default configuration
	 *
	 * @return PushTypesConfiguration default configuration
	 */
	public function setDefaults() {
		$conf = PushTypesConfiguration::getDefault();
		$this->config->setAppValue(self::APP_NAME, self::PUSH_CONFIG_KEY, json_encode($conf));
		return $conf;

	}

	/**This function retrieves the Server level PushTypes configuration
	 * @return PushTypesConfiguration|string
	 */
	public function getPushTypesConfiguration() {
		$t = microtime();
		$conf = $this->config->getAppValue(self::APP_NAME, self::PUSH_CONFIG_KEY, null);
		if (is_string($conf)) {
			return PushTypesConfiguration::fromJSON($conf);
		} else if (!$conf) {
			$conf = $this->setDefaults();
		}
		$this->log()->debug('Get Push Types Configuration Time: ' . (microtime() - $t));
		return $conf;
	}

	/**This function adds(or updates) a single PushTypes to the server level
	 * PushType configuration
	 * @param DummyPushType $pushType
	 */
	public function setPushTypeConfiguration($pushType) {
		$conf = $this->getPushTypesConfiguration();
		$conf->addPushType($pushType);
		$this->config->setAppValue(self::APP_NAME, self::PUSH_CONFIG_KEY, json_encode($conf));
	}

	/**This function makes a single update rather than frequent fetch/update as it happens with the single use function setPushTypeConfiguration
	 *
	 * @param PushTypesConfiguration $pushTypeConfiguration
	 */
	public function setPushTypeConfigurations($pushTypeConfiguration) {
		$this->config->setAppValue(self::APP_NAME, self::PUSH_CONFIG_KEY, json_encode($pushTypeConfiguration));
	}


	/**This function checks whether a given PushType is enabled at the server
	 * level
	 * @param $pushTypeName
	 */
	public function isPushTypeEnabled($pushTypeName) {
		$t = microtime();
		$conf = $this->getPushTypesConfiguration();
		$pTypeConf = $conf->getPushType($pushTypeName);
		if ($pTypeConf) {
			$this->log()->debug('Loaded PushTypeConf : ' . $pTypeConf . ' for ' . $pushTypeName);
		}
		$this->log()->debug('Get Push is enabled time: ' . (microtime() - $t));
	}

	/**This function fetches user level Notification settings (Creates a default
	 * notification configuration if none is set as a side-effect).
	 * @param $user
	 * @return array
	 */
	public function getUserSettings($user) {
		$settingsKeys = $this->config->getUserKeys($user, self::APP_NAME);
		$settings = [];
		if (count($settingsKeys) === 0) {
			$this->setDefaultUserSettings($user);
		}
		foreach ($settingsKeys as $key => $val) {
			$value = $this->config->getUserValue($user,self::APP_NAME,$val);
			$settings[$val] = $value;
		}
		return $settings;
	}

	/**This function resets user push settings to default values
	 * @param $user
	 */
	public function resetDefaultUserSettings($user) {
		return $this->setDefaultUserSettings($user);
	}

	/**This function sets the default values for PushType notifications at the
	 * user level
	 * @param $user
	 */
	private function setDefaultUserSettings($user) {
		$pushTypeConf = PushTypesConfiguration::getDefault();
		$this->config->setUserValue($user,self::APP_NAME,self::PUSH_CONFIG_KEY,json_encode($pushTypeConf));
	}

	/**This function fetches user level notification settings for the given
	 * userId
	 * @param $user
	 * @return PushTypesConfiguration pushTypesConfiguration for the given user
	 */
	public function getUserPushSettings($user){
		$p = $this->config->getUserValue($user,self::APP_NAME,self::PUSH_CONFIG_KEY,false);
		if(!$p){
			$def = PushTypesConfiguration::getDefault();
			$this->updateUserPushSettings($user,$def);
			return $def;
		}
		return is_a($p, PushTypesConfiguration::class) ? $p : PushTypesConfiguration::fromJSON($p);
	}

	/**This function updates the user level notification settings for the given
	 * user
	 * @param $user
	 * @param PushTypesConfiguration $pushSettings
	 */
	public function updateUserPushSettings($user, $pushSettings){
		if(is_a($pushSettings,PushTypesConfiguration::class)){
			$this->config->setUserValue($user,self::APP_NAME,self::PUSH_CONFIG_KEY,json_encode($pushSettings));
		}
	}

	public function deleteUserSettings($userId) {
		$this->config->deleteUserValue($userId, self::appName, self::PUSH_CONFIG_KEY);
	}

}