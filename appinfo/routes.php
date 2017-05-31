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

return [
	'ocs' => [
		//['name' => 'ExternalAPIv1#registerToken', 'url' => '/registerToken', 'verb' => 'POST']
	],
	'routes' => [
		['name' => 'ApiTest#index', 'url' => '/firebasepushnotifications', 'verb' => 'GET'],
		['name' => 'AppSettings#updateAdminSettings', 'url' => '/appSettings', 'verb' => 'POST'],
		['name' => 'AppSettings#updateUserSettings', 'url' => '/userSettings', 'verb' => 'POST'],
		['name' => 'AppSettings#updateFirebaseKey', 'url' => '/firebaseSettings', 'verb' => 'POST'],
		['name' => 'AppSettings#deleteUserToken', 'url' => '/deleteToken', 'verb' => 'POST'],
		['name' => 'AppSettings#deleteAllUserTokens', 'url' => '/deleteAllTokens', 'verb' => 'POST'],
		['name' => 'Token#registerToken', 'url' => '/registerToken', 'verb' => 'GET'],
		['name' => 'Token#unregisterToken', 'url' => '/unregisterToken', 'verb' => 'GET']
	],
];
