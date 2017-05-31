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
namespace OCA\Firebasepushnotifications\Controller;


use OCA\Firebasepushnotifications\CurrentUser;
use OCA\Firebasepushnotifications\Data;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\ILogger;
use OCP\IRequest;

class ExternalAPIv1 extends OCSController {

	/** @var Data */
	protected $data;


	/** @var CurrentUser */
	protected $currentUser;
	private $logger;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param Data $data
	 * @param CurrentUser $currentUser
	 */
	public function __construct($appName,
								IRequest $request,
								Data $data,
								CurrentUser $currentUser,
								ILogger $logger) {
		parent::__construct($appName, $request);

		$this->data = $data;
		$this->currentUser = $currentUser;
		$this->logger = $logger;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *     */
	public function registerToken($token, $resource, $deviceType, $locale = 'en_EN') {
		$this->logger->info('Registering Token: Received  Current User: ' . $this->currentUser->getUserIdentifier() . 'Parameters' . print_r($this->request->getParams(), true));

		$this->logger->info('Register Token Received' . print_r($this->request->getParams(), true));

		$entries = ['entries' => 'entrees'];

		return new DataResponse($entries);

	}
}