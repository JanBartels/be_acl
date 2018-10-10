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

use JBartels\BeAcl\Cache\PermissionCache;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Backend ACL - Functions re-calculating permissions.
 *
 * @author  Sebastian Kurfuerst <sebastian@typo3.org>
 */
class UserAuthGroup
{
    /**
     * @var array
     */
    protected $aclDisallowed;

    /**
     * @var array
     */
    protected $aclPageList;

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $db;

    /**
     * Initialize the database connection.
     */
    public function __construct()
    {
        $this->db = $GLOBALS['TYPO3_DB'];
    }

    /**
     * Returns a combined binary representation of the current users permissions for the page-record, $row.
     * The perms for user, group and everybody is OR'ed together (provided that the page-owner is the user and for the
     * groups that the user is a member of the group If the user is admin, 31 is returned (full permissions for all
     * five flags).
     *
     * @param array                                                    $params input page row with all perms_* fields available
     * @param \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $that   BE User Object
     *
     * @return int Bitwise representation of the users permissions in relation to input page row, $row
     */
    public function calcPerms($params, $that)
    {
        $row = $params['row'];

        $beAclConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_acl']);
        if (!$beAclConfig['disableOldPermissionSystem']) {
            $out = $params['outputPermissions'];
        } else {
            $out = 0;
        }

        $rootLine = BackendUtility::BEgetRootLine($row['uid']);

        $i = 0;
        $takeUserIntoAccount = 1;
        $groupIdsAlreadyUsed = [];
        foreach ($rootLine as $values) {
            if (0 != $i) {
                $recursive = ' AND recursive=1';
            } else {
                $recursive = '';
            }
            $res = $this->db->exec_SELECTquery(
                '*',
                'tx_beacl_acl',
                'pid=' . intval($values['uid']) . $recursive,
                '',
                'recursive ASC'
            );

            while ($result = $this->db->sql_fetch_assoc($res)) {
                if (0 == $result['type']
                    && ($that->user['uid'] == $result['object_id'])
                    && $takeUserIntoAccount
                ) {
                    // user has to be taken into account
                    $out |= $result['permissions'];
                    $takeUserIntoAccount = 0;
                } elseif (1 == $result['type']
                    && $that->isMemberOfGroup($result['object_id'])
                    && !in_array($result['object_id'], $groupIdsAlreadyUsed)
                ) {
                    $out |= $result['permissions'];
                    $groupIdsAlreadyUsed[] = $result['object_id'];
                }
            }
            ++$i;
        }

        return $out;
    }

    /**
     * Returns a WHERE-clause for the pages-table where user permissions according to input argument, $perms, is
     * validated.
     * $perms is the 'mask' used to select. Fx. if $perms is 1 then you'll get all pages that a user can actually see!
     * 2^0 = show (1)
     * 2^1 = edit (2)
     * 2^2 = delete (4)
     * 2^3 = new (8)
     * If the user is 'admin' " 1=1" is returned (no effect)
     * If the user is not set at all (->user is not an array), then " 1=0" is returned (will cause no selection results
     * at all) The 95% use of this function is "->getPagePermsClause(1)" which will return WHERE clauses for
     * *selecting* pages in backend listings - in other words will this check read permissions.
     *
     * @param int                                                      $params Permission mask to use, see function description
     * @param \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $that   BE User Object
     *
     * @return string Part of where clause. Prefix " AND " to this.
     */
    public function getPagePermsClause($params, $that)
    {
        /** @var \JBartels\BeAcl\Cache\PermissionCache $permissionCache */
        $permissionCache = GeneralUtility::makeInstance(PermissionCache::class);
        $permissionCache->setBackendUser($that);

        $cachedPermissions = $permissionCache->getPermissionsClause($params['perms']);
        if (isset($cachedPermissions)) {
            return $cachedPermissions;
        }

        // get be_acl config in EM
        $beAclConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_acl']);
        if (!$beAclConfig['disableOldPermissionSystem']) {
            $str = $params['currentClause'];
        } else {
            $str = '1 = 2';
        }

        // get some basic variables
        $perms = $params['perms'];
        $this->aclPageList = [];

        // get allowed IDs for user
        $this->getPagePermsClause_single(0, $that->user['uid'], $perms);

        // get allowed IDs for every single group
        if ($that->groupList) {
            $groupList = explode(',', $that->groupList);
            foreach ($groupList as $singleGroup) {
                $this->getPagePermsClause_single(1, $singleGroup, $perms);
            }
        }
        if (!empty($this->aclPageList)) {
            // put all page IDs together to the final SQL string
            $str = '( ' . $str . ' ) OR ( pages.uid IN (' . implode(',', $this->aclPageList) . ') )';

            // if the user is in a workspace, that has to be taken into account
            // see t3lib_BEfunc::getWorkspaceVersionOfRecord() for the source of this query
            if ($that->workspace) {
                $str .= ' OR ( pages.t3ver_wsid=' . intval($that->workspace) . ' AND pages.t3ver_oid IN (' . implode(
                    ',',
                        $this->aclPageList
                ) . ') )';
            }
        }

        // for safety, put whole where query part into brackets so it won't interfere with other parts of the page
        $str = ' ( ' . $str . ' ) ';

        // Store data in cache
        $permissionCache->setPermissionsClause($params['perms'], $str);

        return $str;
    }

    /**
     * adds allowed pages to $this->aclPageList for a certain user/group.
     *
     * most of the code found here was before in getPagePermsClause of be_acl
     *
     * @param $type int  Type of the ACL record (0 - User, 1 - Group)
     * @param $object_id int  ID of the group / user
     * @param $perms int  permission mask to use
     **/
    protected function getPagePermsClause_single($type, $object_id, $perms)
    {
        // reset aclDisallowed
        $this->aclDisallowed = [];

        // 1. fetch all ACLs relevant for the current user/group
        $aclAllowed = [];
        $where = ' ( (type = ' . intval($type) . ' AND object_id = ' . intval($object_id) . ')';

        $whereAllow = ') AND (permissions & ' . $perms . ' = ' . $perms . ')';
        $whereDeny = ') AND (permissions & ' . $perms . ' = 0)';

        $res = $this->db->exec_SELECTquery(
            'pid, recursive',
            'tx_beacl_acl',
            $where . $whereAllow
        );

        while ($result = $this->db->sql_fetch_assoc($res)) {
            $aclAllowed[] = $result;
        }

        if ($aclAllowed) {
            // get all "deny" acls if there are allow ACLs
            $res = $this->db->exec_SELECTquery(
                'pid, recursive',
                'tx_beacl_acl',
                $where . $whereDeny
            );
            while ($result = $this->db->sql_fetch_assoc($res)) {
                // only one ACL per group/user per page is allowed, that's why this line imposes no problem. It rather increases speed.
                $this->aclDisallowed[$result['pid']] = $result['recursive'];
            }

            // go through all allowed ACLs, if it is not recursive, add the page to the aclPageList, if recursive, call recursion function
            foreach ($aclAllowed as $singleAllow) {
                if (0 == $singleAllow['recursive']) {
                    $this->aclPageList[$singleAllow['pid']] = $singleAllow['pid'];
                } else {
                    $this->aclTraversePageTree($singleAllow['pid']);
                }
            }
        }
    }

    /**
     * traverses page tree and handles "disallow" ACLs.
     *
     * is a recursive function.
     *
     * @param int $pid Page ID where to start traversing the tree
     **/
    protected function aclTraversePageTree($pid)
    {
        // if there is a disallow ACL for the current page, don't add the page to the aclPageList
        if (array_key_exists($pid, $this->aclDisallowed)) {
            if (1 == $this->aclDisallowed[$pid]) {
                // if recursive, stop processing
                return;
            }
        } else {
            // in case there is no disallow ACL, add page ID to aclPageList
            $this->aclPageList[$pid] = $pid;
        }

        // find subpages and call function itself again
        $res = $this->db->exec_SELECTquery('uid', 'pages', 'pid=' . intval($pid) . ' AND deleted=0');
        while ($result = $this->db->sql_fetch_assoc($res)) {
            $this->aclTraversePageTree($result['uid']);
        }
    }
}
