<?php

namespace JBartels\BeAcl\Hook;

use JBartels\BeAcl\Cache\PermissionCache;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005 Sebastian Kurfuerst (sebastian@garbage-group.de)
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
 * This class contains hooks that are called by the TYPO3 DataHandler
 * when a record is modified (processDatamap / processCmdmap).
 */
class DataHandlerHook
{
    /**
     * This hook is called when a record is added or edited.
     * When a new page is created or a tx_beacl_acl record is changed
     * the permission cache is flushed.
     *
     * @param string                                   $status        TCEmain operation status, either 'new' or 'update'
     * @param string                                   $table         the DB table the operation was carried out on
     * @param mixed                                    $recordId      the record's uid for update records, a string to look the record's uid up after it has
     *                                                                been created
     * @param array                                    $updatedFields array of changed fiels and their new values
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $tceMain       TCEmain parent object
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $recordId, $updatedFields, $tceMain)
    {
        // When a new page is created we update the permission timestamp
        // in the cache so that all Backend users recalculate their
        // permissions.
        if ('pages' == $table && 'new' == $status) {
            $this->flushPermissionCache();

            return;
        }

        // If a ACL is modified or created we also flush the cache
        if ('tx_beacl_acl' == $table) {
            $this->flushPermissionCache();
        }
    }

    /**
     * This hook is called when a record is moved or deleted.
     * It flushes the permission cache when a tx_beacl_acl has changed.
     *
     * @param string                                   $command      the TCE command
     * @param string                                   $table        the record's table
     * @param int                                      $recordId     the record's uid
     * @param array                                    $commandValue the commands value, typically an array with more detailed command information
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $tceMain      the TCEmain parent object
     */
    public function processCmdmap_postProcess(
        $command,
        $table,
        $recordId,
        $commandValue,
        \TYPO3\CMS\Core\DataHandling\DataHandler $tceMain
    ) {
        // This is required to take care of deleted ACLs.
        if ('tx_beacl_acl' == $table) {
            $this->flushPermissionCache();
        }
    }

    /**
     * Flushes the permission cache.
     */
    protected function flushPermissionCache()
    {
        /** @var \JBartels\BeAcl\Cache\PermissionCache $permissionCache */
        $permissionCache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(PermissionCache::class);
        $permissionCache->flushCache();
    }
}
