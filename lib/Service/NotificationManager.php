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

namespace OCA\LoginLocations\Service;

use OCA\LoginLocations\AppInfo\Application;
use OCA\LoginLocations\Event\NewLocation;
use OCP\Notification\IManager;

class NotificationManager {
	/**
	 * @var IManager
	 */
	private $manager;

	public function __construct(IManager $manager) {
		$this->manager = $manager;
	}

	public function newLocation(NewLocation $location) {
		//Send notificcation
		$notification = $this->manager->createNotification();
		$notification->setApp(Application::APP_ID)
			->setSubject('new_location', [
				'ip' => $location->getLocation()->getIp(),
			])
			->setObject('id', $location->getLocation()->getId())
			->setUser($location->getUser()->getUID())
			->setDateTime(new \DateTime());
		$this->manager->notify($notification);
	}
}
