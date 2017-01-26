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
 * This utility can be used to check the timestamps of the permission
 * cache of a Backend user.
 */
class TimestampUtility implements SingletonInterface
{

    const CACHE_IDENTIFIER_TIMESTAMP = 'last_permission_change_timestamp';

    /**
     * Second level cache (usually using a persistend backend) that is used
     * for storing the timestamp of the last permission update.
     *
     * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    protected $timestampCache;

    /**
     * First level cache for storing the timestamp of the last permission update.
     *
     * @var int
     */
    protected $timestampCacheFirstLevel = null;

    /**
     * Returns TRUE if the given timestamp is newer than the stored
     * timestamp.
     *
     * @param int $timestamp
     * @return bool
     */
    public function permissionTimestampIsValid($timestamp)
    {

        $this->initializeCache();

        $lastPermissionChangeTimestamp = $this->getLastPermissionChangeTimestampFromCache();
        $timestamp = intval($timestamp);

        if ($lastPermissionChangeTimestamp < $timestamp) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $timestampCache
     */
    public function setTimestampCache($timestampCache)
    {
        $this->timestampCache = $timestampCache;
    }

    /**
     * Updates the timestamp in the cache with the current timestamp
     *
     * @param int $offset
     * @return void
     */
    public function updateTimestamp($offset = 0)
    {
        $this->initializeCache();
        $timestamp = time() + $offset;
        $this->timestampCache->set(self::CACHE_IDENTIFIER_TIMESTAMP, (string)$timestamp);
        $this->timestampCacheFirstLevel = $timestamp;
    }

    /**
     * Returns the timestamp of the last permission update.
     *
     * @return int
     */
    protected function getLastPermissionChangeTimestampFromCache()
    {

        // Return directly if timestamp is found in the first level cache.
        if (isset($this->timestampCacheFirstLevel)) {
            return $this->timestampCacheFirstLevel;
        }

        // If timestamp is found in second level cache, fill first level cache and return value.
        if ($this->timestampCache->has(self::CACHE_IDENTIFIER_TIMESTAMP)) {
            $timestamp = (int)$this->timestampCache->get(self::CACHE_IDENTIFIER_TIMESTAMP);
            $this->timestampCacheFirstLevel = $timestamp;
            return $timestamp;
        }

        return 0;
    }

    /**
     * Initializes the timestamp cache
     *
     * @return void
     */
    protected function initializeCache()
    {

        if (isset($this->timestampCache)) {
            return;
        }

        /** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
        $cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\Core\\Cache\\CacheManager');
        $this->setTimestampCache($cacheManager->getCache('tx_be_acl_timestamp'));
    }
}
