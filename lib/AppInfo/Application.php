<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2018, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

namespace OCA\LoginLocations\AppInfo;

use OCA\LoginLocations\Event\NewLocation;
use OCA\LoginLocations\Listener\Login;
use OCA\LoginLocations\Notification\Notifier;
use OCA\LoginLocations\Service\NotificationManager;
use OCP\AppFramework\App;
use OCP\IUserSession;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Application extends App {

	const APP_ID = 'login_locations';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register() {
		$container = $this->getContainer();

		$notificationManager = $container->getServer()->getNotificationManager();
		$notificationManager->registerNotifier(function() {
			return $this->getContainer()->query(Notifier::class);
		}, function() {
			return [
				'id' => self::APP_ID,
				'name' => 'Login Locations',
			];
		});

		/** @var IUserSession $userSession */
		$userSession = $container->query(IUserSession::class);

		$userSession->listen('\OC\User', 'postLogin', function ($user, $password) use ($container) {
			/** @var Login $listener */
			$listener = $container->query(Login::class);
			$listener->handleLogin($user);
		});

		/** @var EventDispatcherInterface $dispatcher */
		$dispatcher = $container->query(EventDispatcherInterface::class);
		$dispatcher->addListener(NewLocation::class, function(NewLocation $location) use ($container) {
			/** @var NotificationManager $notificationManager */
			$notificationManager = $container->query(NotificationManager::class);
			$notificationManager->newLocation($location);
		});
	}
}
