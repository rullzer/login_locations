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

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OCA\LoginLocations\Db\Location;
use OCA\LoginLocations\Db\LocationMapper;
use OCA\LoginLocations\Event\NewLocation;
use OCP\IRequest;
use OCP\IUser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LocationManager {

	/** @var LocationMapper */
	private $locationMapper;
	/** @var IRequest */
	private $request;
	/** @var EventDispatcherInterface */
	private $eventDispatcher;

	public function __construct(IRequest $request,
								LocationMapper $locationMapper,
								EventDispatcherInterface $eventDispatcher) {
		$this->locationMapper = $locationMapper;
		$this->request = $request;
		$this->eventDispatcher = $eventDispatcher;
	}

	public function handleLogin(IUser $user) {
		$location = new Location();
		$location->setUserId($user->getUID());
		$location->setIp($this->request->getRemoteAddress());

		try {
			$location = $this->locationMapper->insert($location);
		} catch (UniqueConstraintViolationException $e) {
			//Already exists nothing to do
			return;
		}

		// Emit event new location event
		$this->eventDispatcher->dispatch(NewLocation::class, new NewLocation($location, $user));
	}
}
