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

namespace OCA\Firebasepushnotifications\Settings;


use OCA\Firebasepushnotifications\DB\FirebaseConfHandler;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\BackgroundJob\IJobList;
use OCP\IConfig;
use OCP\IDateTimeFormatter;
use OCP\IL10N;
use OCP\Settings\ISettings;
use OCP\Template;

class AdminSettings implements ISettings {


	/** @var IConfig */
	private $config;

	/** @var IL10N */
	private $l;

	/** @var IDateTimeFormatter */
	private $dateTimeFormatter;

	/** @var IJobList */
	private $jobList;

	private $firebaseConfHandler;


	/**
	 * Admin constructor.
	 *
	 * @param IConfig $config
	 * @param IL10N $l
	 * @param IDateTimeFormatter $dateTimeFormatter
	 * @param IJobList $jobList
	 * @param FirebaseConfHandler $firebaseConfHandler
	 */
	public function __construct(IConfig $config,
								IL10N $l,
								IDateTimeFormatter $dateTimeFormatter,
								IJobList $jobList, FirebaseConfHandler $firebaseConfHandler
	) {
		$this->config = $config;
		$this->l = $l;
		$this->dateTimeFormatter = $dateTimeFormatter;
		$this->jobList = $jobList;
		$this->firebaseConfHandler = $firebaseConfHandler;
	}

	/**
	 * The panel controller method that returns a template to the UI
	 *
	 * @since 10.0
	 * @return TemplateResponse | Template
	 */
	public function getPanel() {
		\OC::$server->getLogger()->info('Setting service called!!!');
		$parameters = [];
		$t = microtime();
		$conf = $this->firebaseConfHandler->getPushTypesConfiguration();
		$srv = $this->firebaseConfHandler->getServerConfiguration(); //TODO Autogenerate if missing
		if(!$srv){
			$srv = $this->firebaseConfHandler->saveServerKey('n/a');
		}
		\OC::$server->getLogger()->info('Get PushTypes Configuration time for admin page: ' . (microtime() - $t));
		$parameters['pushConf'] = $conf;
		$parameters['firebase'] = $srv;
		return new TemplateResponse('firebasepushnotifications', 'settings/adminSettings', $parameters);
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {
		return $this->getPanel();
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection() {
		return 'firebasepushnotifications';
	}

	/**
	 * A string to identify the section in the UI / HTML and URL
	 *
	 * @since 10.0
	 * @return string
	 */
	public function getSectionID() {
		\OC::$server->getLogger()->info('Setting service section id requested called!!!');
		return 'firebase';
	}

	/**
	 * The number used to order the section in the UI.
	 *
	 * @since 10.0
	 * @return int between 0 and 100, with 100 being the highest priority
	 */
	public function getPriority() {
		return 50;
	}
}