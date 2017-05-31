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

use OCP\IL10N;

class Collection implements IParameter {
	/** @var IL10N */
	protected $l;

	/** @var Parameter[] */
	protected $parameters;

	/** @var string */
	protected $random;

	/**
	 * @param IL10N $l
	 * @param string $random
	 */
	public function __construct(IL10N $l, $random) {
		$this->l = $l;
		$this->random = $random;
		$this->parameters = [];
	}

	/**
	 * @param IParameter $parameter
	 */
	public function addParameter(IParameter $parameter) {
		foreach ($this->parameters as $existingParameter) {
			if ($existingParameter->getParameterInfo() === $parameter->getParameterInfo()) {
				return;
			}
		}
		$this->parameters[] = $parameter;
	}

	/**
	 * @return mixed
	 */
	public function getParameter() {
		return $this->random;
	}

	/**
	 * @return array With two entries: value and type
	 */
	public function getParameterInfo() {
		$parameters = [];
		foreach ($this->parameters as $parameter) {
			$parameters[] = $parameter->getParameterInfo();
		}

		return [
			'value' => $parameters,
			'type' => 'collection',
		];
	}

	/**
	 * @return string The formatted parameter
	 */
	public function format() {
		$parameterList = $plainParameterList = [];

		foreach ($this->parameters as $parameter) {
			$parameterList[] = $parameter->format();
		}

		return '<collection>' . implode('', $parameterList) . '</collection>';
	}
}
