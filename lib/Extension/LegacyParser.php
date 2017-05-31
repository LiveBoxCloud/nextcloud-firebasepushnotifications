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

namespace OCA\Firebasepushnotifications\Extension;


use OCA\Firebasepushnotifications\DataHelper;
use OCA\Firebasepushnotifications\PlainTextParser;
use OCP\Activity\IEvent;
use OCP\Activity\IProvider;
use OCP\L10N\IFactory;

class LegacyParser implements IProvider {

	/** @var IFactory */
	protected $languageFactory;

	/** @var DataHelper */
	protected $dataHelper;

	/** @var PlainTextParser */
	protected $parser;

	/**
	 * @param IFactory $languageFactory
	 * @param DataHelper $dataHelper
	 * @param PlainTextParser $parser
	 */
	public function __construct(IFactory $languageFactory, DataHelper $dataHelper, PlainTextParser $parser) {
		$this->languageFactory = $languageFactory;
		$this->dataHelper = $dataHelper;
		$this->parser = $parser;
	}

	/**
	 * @param string $language
	 * @param IEvent $event
	 * @param IEvent|null $previousEvent
	 * @return IEvent
	 * @throws \InvalidArgumentException
	 * @since 9.2.0
	 */
	public function parse($language, IEvent $event, IEvent $previousEvent = null) {
		$l = $this->languageFactory->get('activity', $language);
		$this->dataHelper->setL10n($l);

		$event->setParsedSubject($this->parser->parseMessage($this->dataHelper->translation(
			$event->getApp(),
			$event->getSubject(),
			$this->dataHelper->getParameters($event, 'subject', json_encode($event->getSubjectParameters()))
		)));

		$event->setParsedMessage($this->parser->parseMessage($this->dataHelper->translation(
			$event->getApp(),
			$event->getMessage(),
			$this->dataHelper->getParameters($event, 'message', json_encode($event->getMessageParameters()))
		)));

		return $event;
	}
}
