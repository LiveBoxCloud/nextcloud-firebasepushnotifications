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

namespace OCA\Firebasepushnotifications;

use OCP\Activity\IConsumer;
use OCP\Activity\IEvent;
use OCP\Activity\IManager;
use OCP\L10N\IFactory;

class Consumer implements IConsumer {

	/** @var Data */
	protected $data;
	/** @var IManager */
	protected $manager;

	/** @var UserSettings */
	protected $userSettings;

	/** @var IFactory */
	protected $l10nFactory;

	/**
	 * Constructor
	 *
	 * @param Data $data
	 * @param IManager $manager
	 * @param UserSettings $userSettings
	 * @param IFactory $l10nFactory
	 */
	public function __construct(Data $data, IManager $manager, UserSettings $userSettings, IFactory $l10nFactory) {
		$this->data = $data;
		$this->manager = $manager;
		$this->userSettings = $userSettings;
		$this->l10nFactory = $l10nFactory;
	}

	/**
	 * Send an event to the notifications of a user
	 *
	 * @param IEvent $event
	 * @return null
	 */
	public function receive(IEvent $event) {

		$selfAction = $event->getAffectedUser() === $event->getAuthor();
				// Add activity to stream
		$this->data->send($event);

		// User is not the author or wants to see their own actions
		return;
	}
}
