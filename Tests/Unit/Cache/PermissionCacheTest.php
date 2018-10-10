<?php

namespace JBartels\BeAcl\Tests\Unit\Cache;

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

use JBartels\BeAcl\Cache\PermissionCache;
use JBartels\BeAcl\Cache\TimestampUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Tests for the permission cache.
 */
class PermissionCacheTest extends UnitTestCase
{
    /**
     * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected $backendUser;

    /**
     * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \JBartels\BeAcl\Cache\PermissionCache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionCache;

    /**
     * The key used for the test cache entry.
     *
     * @var string
     */
    protected $permissionsClauseCacheKey = 'testCachekey';

    /**
     * The value used for the test cache entry.
     *
     * @var string
     */
    protected $permissionsClauseCacheValue = 'testCacheValue';

    /**
     * Initializes the permission cache.
     */
    public function setUp()
    {
        /* @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser */
        $this->backendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(BackendUserAuthentication::class);
    }

    /**
     * @test
     */
    public function flushingCacheInvalidatesPreviouslySetFirstLevelCache()
    {
        $this->initializePermissionCacheMock(['initializeRequiredClasses']);

        /** @var \JBartels\BeAcl\Cache\TimestampUtility|\PHPUnit_Framework_MockObject_MockObject $timestampUtility */
        $timestampUtility = $this->getMock(TimestampUtility::class, ['updateTimestamp', 'permissionTimestampIsValid']);
        $timestampUtility->expects($this->once())->method('updateTimestamp');
        $timestampUtility->expects($this->once())->method('permissionTimestampIsValid')->will($this->returnValue(false));
        $this->permissionCache->setTimestampUtility($timestampUtility);

        $this->permissionCache->setPermissionsClause($this->permissionsClauseCacheKey, $this->permissionsClauseCacheValue);
        $this->permissionCache->flushCache();
        $cachedValue = $this->permissionCache->getPermissionsClause($this->permissionsClauseCacheKey);
        $this->assertNull($cachedValue);
    }

    /**
     * @test
     */
    public function flushingCacheInvalidatesPreviouslySetSecondLevelCache()
    {
        $this->initializePermissionCacheMock(['initializeRequiredClasses']);
        $this->permissionCache->disableFirstLevelCache();

        /** @var \JBartels\BeAcl\Cache\TimestampUtility|\PHPUnit_Framework_MockObject_MockObject $timestampUtility */
        $timestampUtility = $this->getMock(TimestampUtility::class, ['updateTimestamp', 'permissionTimestampIsValid']);
        $timestampUtility->expects($this->once())->method('updateTimestamp');
        $timestampUtility->expects($this->once())->method('permissionTimestampIsValid')->will($this->returnValue(false));
        $this->permissionCache->setTimestampUtility($timestampUtility);

        $this->permissionCache->setPermissionsClause($this->permissionsClauseCacheKey, $this->permissionsClauseCacheValue);
        $this->permissionCache->flushCache();
        $cachedValue = $this->permissionCache->getPermissionsClause($this->permissionsClauseCacheKey);
        $this->assertNull($cachedValue);
    }

    /**
     * @test
     */
    public function previouslySetCacheValueIsReturnedByFirstLevelCache()
    {
        $this->initializePermissionCacheMock(['initializeRequiredClasses']);
        $this->permissionCache->setPermissionsClause($this->permissionsClauseCacheKey, $this->permissionsClauseCacheValue);
        $cachedValue = $this->permissionCache->getPermissionsClause($this->permissionsClauseCacheKey);
        $this->assertEquals($this->permissionsClauseCacheValue, $cachedValue);
    }

    /**
     * @test
     */
    public function previouslySetCacheValueIsReturnedBySecondLevelCache()
    {
        $this->initializePermissionCacheMock(['initializeRequiredClasses']);

        /** @var \JBartels\BeAcl\Cache\TimestampUtility|\PHPUnit_Framework_MockObject_MockObject $timestampUtility */
        $timestampUtility = $this->getMock(TimestampUtility::class, ['permissionTimestampIsValid']);
        $timestampUtility->expects($this->once())->method('permissionTimestampIsValid')->will($this->returnValue(true));
        $this->permissionCache->setTimestampUtility($timestampUtility);

        $this->permissionCache->disableFirstLevelCache();
        $this->permissionCache->setPermissionsClause($this->permissionsClauseCacheKey, $this->permissionsClauseCacheValue);
        $cachedValue = $this->permissionCache->getPermissionsClause($this->permissionsClauseCacheKey);
        $this->assertEquals($this->permissionsClauseCacheValue, $cachedValue);
    }

    /**
     * @param array $mockedMethods
     */
    protected function initializePermissionCacheMock($mockedMethods)
    {
        /** @var \JBartels\BeAcl\Cache\PermissionCache $permissionCache */
        $permissionCache = $this->getMock(PermissionCache::class, $mockedMethods);
        $permissionCache->setBackendUser($this->backendUser);

        $cacheBackend = new \TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend('Testing');
        $cacheFrontend = new \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend('tx_be_acl_permissions', $cacheBackend);

        $permissionCache->setPermissionCache($cacheFrontend);

        $this->permissionCache = $permissionCache;
    }
}
