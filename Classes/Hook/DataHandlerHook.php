<?php
namespace JBartels\BeAcl\Hook;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @param string $status TCEmain operation status, either 'new' or 'update'.
     * @param string $table The DB table the operation was carried out on.
     * @param mixed $recordId The record's uid for update records, a string to look the record's uid up after it has
     *     been created.
     * @param array $updatedFields Array of changed fiels and their new values.
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $tceMain TCEmain parent object.
     * @return void
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $recordId, $updatedFields, $tceMain)
    {

        // When a new page is created we update the permission timestamp
        // in the cache so that all Backend users recalculate their
        // permissions.
        if ($table == 'pages' && $status == 'new') {
            $this->flushPermissionCache();
            return;
        }

        // If a ACL is modified or created we also flush the cache
        if ($table == 'tx_beacl_acl') {
            $this->flushPermissionCache();
        }
    }

    /**
     * This hook is called when a record is moved or deleted.
     * It flushes the permission cache when a tx_beacl_acl has changed.
     *
     * @param string $command The TCE command.
     * @param string $table The record's table.
     * @param integer $recordId The record's uid.
     * @param array $commandValue The commands value, typically an array with more detailed command information.
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $tceMain The TCEmain parent object.
     * @return void
     */
    public function processCmdmap_postProcess(
        $command,
        $table,
        $recordId,
        $commandValue,
        \TYPO3\CMS\Core\DataHandling\DataHandler $tceMain
    ) {

        // This is required to take care of deleted ACLs.
        if ($table == 'tx_beacl_acl') {
            $this->flushPermissionCache();
        }
    }

    /**
     * Flushes the permission cache.
     */
    protected function flushPermissionCache()
    {
        /** @var \JBartels\BeAcl\Cache\PermissionCache $permissionCache */
        $permissionCache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JBartels\\BeAcl\\Cache\\PermissionCache');
        $permissionCache->flushCache();
    }

    /**
     * Handle page translations in the same table
     *
     * @param string $table
     * @param int $id
     * @param array $data
     * @param mixed $res
     * @param DataHandler $dataHandler
     * @return mixed
     */
    public function checkRecordUpdateAccess($table, $id, $data, &$res, DataHandler $dataHandler)
    {
        if ($table === 'pages') {

            /**
             * Case #1 - We are editing the page translation directly
             */
            if ($dataHandler->defaultValues['pages']['sys_language_uid'] ?? 0 > 0) {

                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
                $queryBuilder->getRestrictions()
                    ->removeAll()
                    ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
                $languageParent = $queryBuilder->select('l10n_parent')
                    ->from('pages')
                    ->where($queryBuilder->expr()->eq('uid', (int)$id))
                    ->execute()
                    ->fetchColumn();

                if ($languageParent) {
                    return $dataHandler->checkRecordUpdateAccess($table, $languageParent);
                }
            }

            /**
             * Case #2 - We are editing the page in default language, so translation gets updated too
             */
            $currentRecordUid = (integer)$dataHandler->checkValue_currentRecord['uid'];
            if ($currentRecordUid != 0 && $currentRecordUid != $id) {
                return true;
            }
        }
    }
}
