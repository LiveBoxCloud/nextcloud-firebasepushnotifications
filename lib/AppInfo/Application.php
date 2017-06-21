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

namespace OCA\Firebasepushnotifications\AppInfo;

use OC\Files\View;
use OCA\Firebasepushnotifications\Consumer;
use OCA\Firebasepushnotifications\Controller\TokenController;
use OCA\Firebasepushnotifications\FilesHooksStatic;
use OCA\Firebasepushnotifications\Hooks;
use OCA\Firebasepushnotifications\MessageHooksStatic;
use OCP\AppFramework\App;
use OCP\Util;

class Application extends App {
	public static function getAppName() {
		return 'firebasepushnotifications';
	}
	public function __construct () {
		parent::__construct($this->getAppName());
		$container = $this->getContainer();

		// Allow automatic DI for the View, until we migrated to Nodes API
		$container->registerService(View::class, function() {
			return new View('');
		}, false);
		$container->registerService('isCLI', function() {
			return \OC::$CLI;
		});

		$container->registerService('TokenController', function ($c) {
			\OC::$server->getLogger()->info('Registering Service TokenController');
			return new TokenController($this->getAppName(), $c->query('firebasepushnotifications'), $c->query('Request'));
		});

	}

	/**
	 * Register the different app parts
	 */
	public function register() {
		$this->registerActivityConsumer();
		$this->registerHooksAndEvents();
		$this->registerPersonalPage();
	}

	/**
	 * Registers the consumer to the Activity Manager
	 */
	public function registerActivityConsumer() {
		$c = $this->getContainer();
		/** @var \OCP\IServerContainer $server */
		$server = $c->getServer();

		$server->getActivityManager()->registerConsumer(function() use ($c) {
			return $c->query(Consumer::class);
		});
	}

	/**
	 * Register the hooks and events
	 */
	public function registerHooksAndEvents() {
//		$eventDispatcher = $this->getContainer()->getServer()->getEventDispatcher();
		//$eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', [Hooks::class, 'onLoadFilesAppScripts']);

		Util::connectHook('OC_User', 'post_deleteUser', Hooks::class, 'deleteUser');
		Util::connectHook('OC_User', 'post_login', Hooks::class, 'setDefaultsForUser');

		$this->registerFilesActivity();
	}

	/**
	 * Register the hooks for filesystem operations
	 */
	public function registerFilesActivity() {

		Util::connectHook('OC_Filesystem', 'post_create', MessageHooksStatic::class, 'fileCreate');
		Util::connectHook('OC_Filesystem', 'post_create', MessageHooksStatic::class, 'fileUpdate');

		// All other events from other apps have to be send via the Consumer
		Util::connectHook('OC_Filesystem', 'post_create', FilesHooksStatic::class, 'fileCreate');
		Util::connectHook('OC_Filesystem', 'post_update', FilesHooksStatic::class, 'fileUpdate');
		Util::connectHook('OC_Filesystem', 'delete', FilesHooksStatic::class, 'fileDelete');
		Util::connectHook('OC_Filesystem', 'rename', FilesHooksStatic::class, 'fileMove');
		Util::connectHook('OC_Filesystem', 'post_rename', FilesHooksStatic::class, 'fileMovePost');
		Util::connectHook('\OCA\Files_Trashbin\Trashbin', 'post_restore', FilesHooksStatic::class, 'fileRestore');
		Util::connectHook('OCP\Share', 'post_shared', FilesHooksStatic::class, 'share');
		Util::connectHook('OCP\Share', 'post_unshare', FilesHooksStatic::class, 'unShare');


		///\OC::$server->getLogger()->info('Successfully registered File Activity hooks');
	}

	/**
	 * Register personal settings for notifications and emails
	 */
	public function registerPersonalPage() {
		\OCP\App::registerPersonal($this->getContainer()->getAppName(), 'personal');
	}
}
