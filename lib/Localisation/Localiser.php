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

namespace OCA\Firebasepushnotifications\Localisation;


use Exception;
use OCA\Firebasepushnotifications\Entities\FirebaseMessage;

class Localiser {


	private $_userManager;

	/**
	 * @return \OC\User\Manager
	 */
	private function getUserManager() {
		if (!$this->_userManager) {
			$this->_userManager = \OC::$server->getUserManager();
		}
		return $this->_userManager;
	}

	/**Handy system logger reference
	 * @return \OCP\ILogger
	 */
	private function log(){
		return \OC::$server->getLogger();
	}

	private $loadedLocales;
	protected $locNameRoot = 'push_loc_';
	protected $extension = '.json';

	/**This function fetches the requested locale dictionary or defaults to english if that is not available
	 * @param $locale
	 * @param bool $doRetry
	 * @return mixed
	 */
	private function getDictionary($locale, $doRetry = true) {
		if(!$this->loadedLocales){
			$this->loadedLocales = [];
		}
		if(!array_key_exists($locale, $this->loadedLocales)){
			$dictionary = $this->loadDictionaryFor($locale);
			if($dictionary){
				$this->loadedLocales[$locale] = $dictionary;
			} else {
				$baseLocale = $this->getBaseLocale($locale);
				$this->log()->debug('Locale: ' . $locale . ' BaseLocale:' . $this->getBaseLocale($locale));
				if ($baseLocale) {
					return $this->getDictionary($baseLocale, false);
				} else if ($locale != 'en_EN') {
					$this->log()->error('Could not load a dictionary for : ' . $locale . ' Trying for default');
					return $this->getDictionary('en_EN', false);
				}
			}
		}

		if(!$this->loadedLocales){
			return $this->getDictionary('en_EN', false);
		}
		return $this->loadedLocales[$locale];
	}

	/**This function attempts to generate the base locale for a given language
	 *
	 * @param $locale
	 * @return null|string
	 */
	private function getBaseLocale($locale) {
		if ($locale && strlen($locale) > 2) {
			$base = substr($locale, 0, 2);
			return $base . '_' . strtoupper($base);
		}
		return null;
	}

	/**This function loads the message dictionary for the given locale
	 * @param $locale
	 * @return mixed|null
	 */
	private function loadDictionaryFor($locale){
		$filename = $this->locNameRoot.$locale;
		$path = __DIR__ . '/../../l10n/'.$filename.'.json';
		if(file_exists($path)){
			$file = file_get_contents($path);
			if($file){
				$dictionary = json_decode($file,true);
				if(is_array($dictionary)){
					$this->log()->error('Successfully loaded a dictionary with'.count($dictionary).' for locale: '.$locale);
					return $dictionary;
				}else{
					$this->log()->error('Failed to parse dictionary for locale: '.$locale.' at path: '.$path);
				}
			}else{
				$this->log()->error('Failed to load the file: '.$file);
			}
		}else{
			$this->log()->error('Failed to load dictionary for locale: '.$locale.' at path: '.$path);
		}
		return null;
	}


	public function __construct() {
		//Hello Rob!
	}

	/**This function localizes a PushMessageType using the given substitution
	 * array and the given locale
	 *
	 * @param string $message (DummyPushType::pushType)
	 * @param array $replaceArray (FirebaseMessage::getParams())
	 * @param string $locale = 'en-EN'  (FirebaseToken::locale )
	 * @return mixed|null The message, localised in the primary or fallback
	 * locale null on error.
	 */
	public function localiseMessage($message, $replaceArray, $locale = 'en-EN') {
		$localisedMessage = $this->getMessageForLocale($message,$locale);
		if($localisedMessage){
			$replaceVars = $this->prepareReplaceArray($replaceArray);
			$this->log()->debug('Prepared substitutions: ' . print_r($replaceVars, true));
			$lM = str_replace(array_keys($replaceVars), array_values($replaceVars), $localisedMessage);
			$this->log()->debug('Localised and replaced as: ' . $lM . ' for locale: ' . $locale . ' with replaceArray: ' . print_r($replaceVars, true) . ' enc:' . base64_encode(print_r($replaceVars, true)));
			return $lM;
		}else{
			$this->log()->error('Failed to localise message: will return null');
			return null;
		}
	}

	/**This function fetches the Localised Base message for a given Message and
	 * Locale
	 * @param $message string (FirebaseMessage::PushType)
	 * @param $locale string (FirebaseToken::locale)
	 * @return mixed The localised message or the original on error
	 */
	public function getMessageForLocale($message, $locale){
		$dictionary = $this->getDictionary($locale);
		//$this->log()->debug('Fetched dictionary: '.print_r($dictionary,true));
		if ($dictionary && isset($dictionary[$message])) {
			$localised = $dictionary[$message];
			if($localised) {
				$this->log()->debug('Localised: '.$message.' in '.$locale. ' as : '.$localised);
				return $localised;
			}
		}
		$this->log()->error('Failed to localise '.$message.' in '.$locale);
		return $message;
	}


	/**Handy self test (Tests localisazione functions for a default set of
	 * parameters)
	 *
	 */
	public function selfTest(){
		try {
			$replaceArray =  ['%filename%'=>'Nome del file.xlsx', '%filedirectory%' => 'Percorsoneplusplus'] ;
			$test = 'test';
			$eng = $this->localiseMessage($test,$replaceArray,'en_EN');
			$esp = $this->localiseMessage($test,$replaceArray,'es_ES');
			$ita = $this->localiseMessage($test,$replaceArray,'it_IT');
			$unsupported = $this->localiseMessage($test,$replaceArray,'de_DE');
			$this->log()->info('Localisation self test result: English: ' . $eng . '\n Italian: ' . $ita . ' .\n Spanish: ' . $esp . '.\n Unsupported, german: ' . $unsupported);
		}catch (Exception $e){
			$this->log()->info('An error occurred in the Localiser self test'.$e->getMessage());
		}

	}

	/**This function parses substitution parameters from
	 * FirebaseMessage::getParams() to the localiser search patters
	 * (i.e. wrapping substitution params with %paramName%)
	 *
	 * @param $replaceArray array substitution array to edit
	 * @return array  the modified substitution array
	 */
	private function prepareReplaceArray($replaceArray) {
		$replaced = [];
		foreach ($replaceArray as $key => $value) {
			if (is_array($value)) {
				continue;
			}
			if (strpos($key, 'path') !== false && empty($value)) {
				//$this->log()->debug('Replacing with Home for ' . $key . ' in ' . $value);
				$value = 'Home';
			}
			if ($key === FirebaseMessage::USER_TARGET_KEY || $key === FirebaseMessage::USER_ACTOR_KEY) {
				$value = $this->getUserDisplayName($value);
			}

			$replaced['%' . $key . '%'] = $value;
		}
		return $replaced;
	}
//	/**This function adds userActorDisplayName and userActorDisplayTarget to the firebaseMessage
//	 * @param FirebaseMessage $firebaseMessage
//	 */
//	private function addDisplayNamesToParams($firebaseMessage){
//		$params = $firebaseMessage->getParams();
//		$params['userActorDisplayName'] = $this->getUserDisplayName($firebaseMessage->getActorUserId());
//		$params['userTargetDisplayName'] = $this->getUserDisplayName($firebaseMessage->getTargetUserId());
//		$firebaseMessage->setParams($params);
//	}

	/** This function fetches the User display name (if available)
	 *
	 * @param $userId string the user whose display name we are looking for
	 * @return string
	 */
	private function getUserDisplayName($userId) {
		if (!$this->getUserManager()) {
			$this->log()->debug('No UserManager Available to replace $userId');
			return $userId;
		}
		$user = $this->getUserManager()->get($userId);
		if (!$user) {
			$this->log()->debug('User: ' . $user . ' not found');
			return $userId;
		}
		$displayName = $user->getDisplayName();
		$this->log()->debug('Get User DisplayName Result:' . $displayName);
		return $displayName;
	}

}