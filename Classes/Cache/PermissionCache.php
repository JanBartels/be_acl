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

use TYPO3\CMS\Core\SingletonInterface;

/**
 * A cache for storing permission data for a given Backend user
 */
class PermissionCache implements SingletonInterface
{

    const CACHE_IDENTIFIER_PERMISSIONS = 'tx_be_acl_permission_cache';

    /**
     * The Backend user for which this cache stores the permissions
     *
     * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected $backendUser;

    /**
     * @var bool
     */
    protected $enableFirstLevelCache = true;

    /**
     * This cache will be used during single requests.
     *
     * @var array
     */
    protected $permissionCacheFirstLevel = array();

    /**
     * This cache will be used during single requests.
     *
     * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    protected $permissionCacheSecondLevel;

    /**
     * @var \JBartels\BeAcl\Cache\TimestampUtility
     */
    protected $timestampUtility;

    /**
     * Initializes the timestamp utility.
     */
    public function __construct()
    {
        $this->initializeRequiredClasses();
    }

    /**
     * Disables the first level cache. Used for testing.
     */
    public function disableFirstLevelCache()
    {
        $this->enableFirstLevelCache = false;
    }

    /**
     * Updates the last permission update timestamp which makes all
     * previously stored caches invalid.
     *
     * @return void
     */
    public function flushCache()
    {
        $this->permissionCacheFirstLevel = array();
        $this->timestampUtility->updateTimestamp();
    }

    /**
     * Returns the stored permissions clause cache entry if one is found
     *
     * @param string $requestedPermissions
     * @return string|null If a cache entry is found the permissions clause will be returned, otherwise NULL
     */
    public function getPermissionsClause($requestedPermissions)
    {

        if (!isset($this->backendUser)) {
            return null;
        }

        if ($this->enableFirstLevelCache) {
            $firstLevelCacheIdentifier = $this->getCacheIdentifier($requestedPermissions);
            if (isset($this->permissionCacheFirstLevel[$firstLevelCacheIdentifier])) {
                return $this->permissionCacheFirstLevel[$firstLevelCacheIdentifier];
            }
        }

        $cacheData = $this->getCacheDataForCurrentUser();

        if ($this->isValidCacheData($cacheData)) {
            return $cacheData->getPermissionClause($requestedPermissions);
        } else {
            return null;
        }
    }

    /**
     * Sets the Backend user for which the cache entries will be managed.
     *
     * @param \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser The Backend user for which the
     *     cache is managed
     */
    public function setBackendUser($backendUser)
    {
        $this->backendUser = $backendUser;
    }

    /**
     * @param \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $permissionCache
     */
    public function setPermissionCache($permissionCache)
    {
        $this->permissionCacheSecondLevel = $permissionCache;
    }

    /**
     * Updates the permissions clause cache for the requested permission
     * data to the given value.
     *
     * @param string $requestedPermissions
     * @param string $permissionsClause
     * @return void
     */
    public function setPermissionsClause($requestedPermissions, $permissionsClause)
    {

        if (!isset($this->backendUser)) {
            return;
        }

        if ($this->enableFirstLevelCache) {
            $firstLevelCacheIdentifier = $this->getCacheIdentifier($requestedPermissions);
            $this->permissionCacheFirstLevel[$firstLevelCacheIdentifier] = $permissionsClause;
        }

        $cacheData = $this->getCacheDataForCurrentUser();

        if (!$this->isValidCacheData($cacheData)) {
            $cacheData = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JBartels\\BeAcl\\Cache\\PermissionCacheData');
        }

        $cacheData->setPermissionClause($requestedPermissions, $permissionsClause);

        $secondLevelCacheIdentifier = $this->getCacheIdentifier();
        $this->permissionCacheSecondLevel->set($secondLevelCacheIdentifier, $cacheData);
    }

    /**
     * @param TimestampUtility $timestampUtility
     */
    public function setTimestampUtility($timestampUtility)
    {
        $this->timestampUtility = $timestampUtility;
    }

    /**
     * Retrieves the cache data from the cache.
     *
     * @return PermissionCacheData|null
     */
    protected function getCacheDataForCurrentUser()
    {

        $cacheIdentifier = $this->getCacheIdentifier();

        if ($this->permissionCacheSecondLevel->has($cacheIdentifier)) {
            return $this->permissionCacheSecondLevel->get($cacheIdentifier);
        }

        return null;
    }

    /**
     * Returns the identifier that should be used for the permissions
     * cache in the users session data.
     *
     * @param string $requestedPermissions
     * @return string
     * @throws \RuntimeException
     */
    protected function getCacheIdentifier($requestedPermissions = '')
    {

        if (!isset($this->backendUser)) {
            throw new \RuntimeException('The Backend user needs to be initializes before the cache identifier can be generated.');
        }

        $identifier = self::CACHE_IDENTIFIER_PERMISSIONS . '_' . $this->backendUser->user['uid'];

        $requestedPermissions = trim($requestedPermissions);
        if ($requestedPermissions !== '') {
            $identifier .= '_' . $requestedPermissions;
        }

        return $identifier;
    }

    /**
     * Initializes the required cache classes.
     */
    protected function initializeRequiredClasses()
    {
        /** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
        $cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
        $this->setPermissionCache($cacheManager->getCache('tx_be_acl_permissions'));
        /** @var TimestampUtility $timestampUtility */
        $timestampUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JBartels\\BeAcl\\Cache\\TimestampUtility');
        $this->setTimestampUtility($timestampUtility);
    }

    /**
     * Returns TRUE if the given cache data is valid
     *
     * @param PermissionCacheData $cacheData
     * @return bool
     */
    protected function isValidCacheData($cacheData)
    {

        if (!isset($cacheData)) {
            return false;
        }

        return $this->timestampUtility->permissionTimestampIsValid($cacheData->getTimestamp());
    }
}
