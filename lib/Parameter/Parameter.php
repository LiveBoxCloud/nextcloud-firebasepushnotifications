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

use OCA\Firebasepushnotifications\Formatter\IFormatter;
use OCP\Activity\IEvent;

class Parameter implements IParameter {
	/** @var IFormatter */
	protected $formatter;

	/** @var mixed */
	protected $parameter;

	/** @var IEvent */
	protected $event;

	/** @var string */
	protected $type;

	/**
	 * @param mixed $parameter
	 * @param IEvent $event
	 * @param IFormatter $formatter
	 * @param string $type
	 */
	public function __construct($parameter,
								IEvent $event,
								IFormatter $formatter,
								$type) {
		$this->parameter = $parameter;
		$this->event = $event;
		$this->formatter = $formatter;
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function getParameter() {
		if ($this->event->getObjectType() && $this->event->getObjectId()) {
			return $this->event->getObjectType() . '#' . $this->event->getObjectId();
		}

		return $this->parameter;
	}

	/**
	 * @return array With two entries: value and type
	 */
	public function getParameterInfo() {
		return [
			'value' => $this->parameter,
			'type' => $this->type,
		];
	}

	/**
	 * @return string The formatted parameter
	 */
	public function format() {
		return $this->formatter->format($this->event, $this->parameter);
	}
}
