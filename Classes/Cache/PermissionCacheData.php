<?php
namespace JBartels\BeAcl\Cache;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Alexander Stehlik (astehlik.deleteme@intera.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * The data that will be stored in the cache.
 */
class PermissionCacheData {

	/**
	 * Array containing the results of the different permission clauses.
	 *
	 * @var array
	 */
	protected $permissionCache;

	/**
	 * The timestamp when this record was created.
	 *
	 * @var int
	 */
	protected $timestamp;

	/**
	 * Initializes the current timestamp.
	 */
	public function __construct() {
		$this->timestamp = time();
		$this->permissionClauseCache = array();
	}

	/**
	 * Returns the matching permissions clause or NULL if none is stored.
	 *
	 * @param $requestedPermissions
	 * @return string|null
	 */
	public function getPermissionClause($requestedPermissions) {

		if (array_key_exists($requestedPermissions, $this->permissionClauseCache)) {
			$permissionsClause = $this->permissionClauseCache[$requestedPermissions];
			if (!empty($permissionsClause)) {
				return $permissionsClause;
			}
		}

		return NULL;
	}

	/**
	 * Returns the timestamp when this cache entry was created.
	 *
	 * @return int
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * Stores the given permissions clause in the cache.
	 *
	 * @param string $requestedPermissions
	 * @param string $permissionsClause
	 */
	public function setPermissionClause($requestedPermissions, $permissionsClause) {
		$this->permissionClauseCache[$requestedPermissions] = $permissionsClause;
	}
}