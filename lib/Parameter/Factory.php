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

namespace OCA\Firebasepushnotifications\Parameter;


use OCA\Firebasepushnotifications\CurrentUser;
use OCA\Firebasepushnotifications\Formatter\IFormatter;
use OCA\Firebasepushnotifications\Formatter\BaseFormatter;
use OCA\Firebasepushnotifications\Formatter\CloudIDFormatter;
use OCA\Firebasepushnotifications\Formatter\FileFormatter;
use OCA\Firebasepushnotifications\Formatter\UserFormatter;
use OCA\Firebasepushnotifications\ViewInfoCache;
use OCP\Activity\IEvent;
use OCP\Activity\IManager;
use OCP\Contacts\IManager as IContactsManager;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUserManager;

class Factory {
	/** @var IManager */
	protected $activityManager;

	/** @var IUserManager */
	protected $userManager;

	/** @var IContactsManager */
	protected $contactsManager;

	/** @var IL10N */
	protected $l;

	/** @var ViewInfoCache */
	protected $infoCache;

	/** @var string */
	protected $user;

	/** @var IURLGenerator */
	protected $urlGenerator;

	/**
	 * @param IManager $activityManager
	 * @param IUserManager $userManager
	 * @param IURLGenerator $urlGenerator
	 * @param IContactsManager $contactsManager
	 * @param ViewInfoCache $infoCache,
	 * @param IL10N $l
	 * @param CurrentUser $currentUser
	 */
	public function __construct(IManager $activityManager,
								IUserManager $userManager,
								IURLGenerator $urlGenerator,
								IContactsManager $contactsManager,
								ViewInfoCache $infoCache,
								IL10N $l,
								CurrentUser $currentUser) {
		$this->activityManager = $activityManager;
		$this->userManager = $userManager;
		$this->urlGenerator = $urlGenerator;
		$this->contactsManager = $contactsManager;
		$this->infoCache = $infoCache;
		$this->l = $l;
		$this->user = (string) $currentUser->getUID();
	}

	/**
	 * @param string $user
	 */
	public function setUser($user) {
		$this->user = (string) $user;
	}

	/**
	 * @param IL10N $l
	 */
	public function setL10n(IL10N $l) {
		$this->l = $l;
	}

	/**
	 * @param string $parameter
	 * @param IEvent $event
	 * @param string $formatter
	 * @return IParameter
	 */
	public function get($parameter, IEvent $event, $formatter) {
		return new Parameter(
			$parameter,
			$event,
			$this->getFormatter($formatter),
			$formatter
		);
	}

	/**
	 * @return Collection
	 */
	public function createCollection() {
		return new Collection($this->l, sha1(microtime() . mt_rand()));
	}

	/**
	 * @param string $formatter
	 * @return IFormatter
	 */
	protected function getFormatter($formatter) {
		switch ($formatter) {
			case 'file':
				/** @var \OCA\Firebasepushnotifications\Formatter\FileFormatter $fileFormatter */
				$fileFormatter = \OC::$server->query(FileFormatter::class);
				$fileFormatter->setUser($this->user);
				return $fileFormatter;
			case 'username':
				/** @var \OCA\Firebasepushnotifications\Formatter\UserFormatter */
				return \OC::$server->query(UserFormatter::class);
			case 'federated_cloud_id':
				/** @var \OCA\Firebasepushnotifications\Formatter\CloudIDFormatter */
				return \OC::$server->query(CloudIDFormatter::class);
			default:
				/** @var \OCA\Firebasepushnotifications\Formatter\BaseFormatter */
				return \OC::$server->query(BaseFormatter::class);
		}
	}
}
