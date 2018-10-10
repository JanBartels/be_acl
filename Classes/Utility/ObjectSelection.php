<?php

namespace JBartels\BeAcl\Utility;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Class for building the item array for the Backend forms.
 */
class ObjectSelection
{
    /**
     * Populates the "object_id" field of a "tx_beacl_acl" record depending on
     * whether the field "type" is set to "User" or "Group".
     *
     * @param array  $PA   field configuration
     * @param object $fobj
     */
    public function select($PA, $fobj)
    {
        if (!array_key_exists('row', $PA)) {
            return;
        }

        if (!array_key_exists('type', $PA['row'])) {
            return;
        }

        // Resetting the SELECT field items
        $PA['items'] = [
            0 => [
                0 => '',
                1 => '',
            ],
        ];
        $type = isset($PA['row']['type'][0]) ? $PA['row']['type'][0] : null;
        // Get users or groups - The function copies functionality of the method acl_objectSelector()
        // of ux_SC_mod_web_perm_index class as for non-admins it returns only:
        // 1) Users which are members of the groups of the current user.
        // 2) Groups that the current user is a member of.
        switch ($type) {
            // In case users shall be returned
            case '0':
                $items = BackendUtility::getUserNames();
                if (!$GLOBALS['BE_USER']->isAdmin()) {
                    $items = BackendUtility::blindUserNames($items, $GLOBALS['BE_USER']->userGroupsUID, 1);
                }

                foreach ($items as $row) {
                    $PA['items'][] = [
                        0 => $row['username'],
                        1 => $row['uid'],
                    ];
                }
                break;

            // In case groups shall be returned
            case '1':
                $items = BackendUtility::getGroupNames();
                if (!$GLOBALS['BE_USER']->isAdmin()) {
                    $items = BackendUtility::blindGroupNames($items, $GLOBALS['BE_USER']->userGroupsUID, 1);
                }

                foreach ($items as $row) {
                    $PA['items'][] = [
                        0 => $row['title'],
                        1 => $row['uid'],
                    ];
                }
                break;

            default:
        }
    }
}
