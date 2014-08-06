<?php

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
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Backend ACL - Replacement for "web->Access"
 *
 * @author  Sebastian Kurfuerst <sebastian@garbage-group.de>
 *
 * Bugfixes applied:
 * #25942, #25835, #13019, #13176, #13175 Jan Bartels
 */
class Tx_BeAcl_Xclass_PermissionModuleController extends SC_mod_web_perm_index {

	/*****************************
	 *
	 * Listing and Form rendering
	 *
	 *****************************/

	/**
	 * Showing the permissions in a tree ($this->edit = false)
	 * (Adding content to internal content variable)
	 *
	 * @return    void
	 */
	public function notEdit() {
		// Get ACL configuration
		$beAclConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_acl']);
		$disableOldPermissionSystem = 0;
		if ($beAclConfig['disableOldPermissionSystem']) {
			$disableOldPermissionSystem = 1;
		}
		$GLOBALS['LANG']->includeLLFile('EXT:be_acl/res/locallang_perm.xml');

		// Get usernames and groupnames: The arrays we get in return contains only 1) users which are members of the groups of the current user, 2) groups that the current user is member of
		$beGroupKeys = $GLOBALS['BE_USER']->userGroupsUID;
		$beUserArray = BackendUtility::getUserNames();
		if (!$GLOBALS['BE_USER']->isAdmin()) {
			$beUserArray = BackendUtility::blindUserNames($beUserArray, $beGroupKeys, 0);
		}
		$beGroupArray = BackendUtility::getGroupNames();
		if (!$GLOBALS['BE_USER']->isAdmin()) {
			$beGroupArray = BackendUtility::blindGroupNames($beGroupArray, $beGroupKeys, 0);
		}

		// Length of strings:
		$tLen = 20;

		// Selector for depth:
		$code = $GLOBALS['LANG']->getLL('Depth') . ': ';
		$code .= BackendUtility::getFuncMenu($this->id, 'SET[depth]', $this->MOD_SETTINGS['depth'], $this->MOD_MENU['depth']);
		$this->content .= $this->doc->section('', $code);

		/** @var \TYPO3\CMS\Backend\Tree\View\PageTreeView */
		$tree = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Tree\\View\\PageTreeView');
		$tree->init('AND ' . $this->perms_clause);
		$tree->addField('perms_user', 1);
		$tree->addField('perms_group', 1);
		$tree->addField('perms_everybody', 1);
		$tree->addField('perms_userid', 1);
		$tree->addField('perms_groupid', 1);
		$tree->addField('hidden');
		$tree->addField('fe_group');
		$tree->addField('starttime');
		$tree->addField('endtime');
		$tree->addField('editlock');

		// Creating top icon; the current page
		$HTML = IconUtility::getSpriteIconForRecord('pages', $this->pageinfo);
		$tree->tree[] = array(
			'row' => $this->pageinfo,
			'HTML' => $HTML
		);

		// Create the tree from $this->id:
		$tree->getTree($this->id, $this->MOD_SETTINGS['depth'], '');

		// Get list of ACL users and groups, and initialize ACLs
		$aclUsers = $this->acl_objectSelector(0, $displayUserSelector, $beAclConfig);
		$aclGroups = $this->acl_objectSelector(1, $displayGroupSelector, $beAclConfig);

		$this->buildACLtree($aclUsers, $aclGroups);

		$this->content .= $displayUserSelector;
		$this->content .= $displayGroupSelector;

		// Make header of table:
		$code = '
			<thead>
				<tr>
					<th colspan="2">&nbsp;</th>
					<th>' . $GLOBALS['LANG']->getLL('Owner', TRUE) . '</th>';
		$tableCells = array();
		if (!$disableOldPermissionSystem) {
			$tableCells[] = $GLOBALS['LANG']->getLL('Group', TRUE);
			$tableCells[] = $GLOBALS['LANG']->getLL('Everybody', TRUE);
			$tableCells[] = $GLOBALS['LANG']->getLL('EditLock', TRUE);
		}
		// ACL headers
		if (!empty($aclUsers)) {
			$tableCells[] = '<b>' . $GLOBALS['LANG']->getLL('aclUser') . '</b>';
			foreach ($aclUsers as $uid) {
				$tableCells[] = $beUserArray[$uid]['username'];
			}
		}
		if (!empty($aclGroups)) {
			$tableCells[] = '<b>' . $GLOBALS['LANG']->getLL('aclGroup') . '</b>';
			foreach ($aclGroups as $uid) {
				$tableCells[] = $beGroupArray[$uid]['title'];
			}
		}
		$code .= $this->printTableHeader($tableCells);
		$code .= '
				</tr>
			</thead>';

		// Traverse tree:
		foreach ($tree->tree as $data) {
			$cells = array();
			$pageId = $data['row']['uid'];

			// Background colors:
			$bgCol = $this->lastEdited == $pageId ? ' class="bgColor-20"' : '';
			$lE_bgCol = $bgCol;

			// User/Group names:
			$userName = $beUserArray[$data['row']['perms_userid']] ?
				$beUserArray[$data['row']['perms_userid']]['username'] :
				($data['row']['perms_userid'] ? $data['row']['perms_userid'] : '');

			if ($data['row']['perms_userid'] && !$beUserArray[$data['row']['perms_userid']]) {
				$userName = SC_mod_web_perm_ajax::renderOwnername($pageId, $data['row']['perms_userid'], htmlspecialchars(GeneralUtility::fixed_lgd_cs($userName, 20)), FALSE);
			} else {
				$userName = SC_mod_web_perm_ajax::renderOwnername($pageId, $data['row']['perms_userid'], htmlspecialchars(GeneralUtility::fixed_lgd_cs($userName, 20)));
			}

			$groupName = $beGroupArray[$data['row']['perms_groupid']] ?
				$beGroupArray[$data['row']['perms_groupid']]['title'] :
				($data['row']['perms_groupid'] ? $data['row']['perms_groupid'] : '');

			if ($data['row']['perms_groupid'] && !$beGroupArray[$data['row']['perms_groupid']]) {
				$groupName = SC_mod_web_perm_ajax::renderGroupname($pageId, $data['row']['perms_groupid'], htmlspecialchars(GeneralUtility::fixed_lgd_cs($groupName, 20)), FALSE);
			} else {
				$groupName = SC_mod_web_perm_ajax::renderGroupname($pageId, $data['row']['perms_groupid'], htmlspecialchars(GeneralUtility::fixed_lgd_cs($groupName, 20)));
			}

			// Seeing if editing of permissions are allowed for that page:
			$editPermsAllowed = $data['row']['perms_userid'] == $GLOBALS['BE_USER']->user['uid'] || $GLOBALS['BE_USER']->isAdmin();

			// First column:
			$cellAttrib = $data['row']['_CSSCLASS'] ? ' class="' . $data['row']['_CSSCLASS'] . '"' : '';
			$cells[] = '<td align="left" nowrap="nowrap"' . ($cellAttrib ? $cellAttrib : $bgCol) .
				$this->generateTitleAttribute($data['row']['uid'], $beUserArray, $beGroupArray) . '>' .
				$data['HTML'] . htmlspecialchars(GeneralUtility::fixed_lgd_cs($data['row']['title'], $tLen)) . '</td>';

			// "Edit permissions" -icon
			if ($editPermsAllowed && $pageId) {
				$aHref = BackendUtility::getModuleUrl('web_perm') . '&mode=' . $this->MOD_SETTINGS['mode'] . '&depth=' . $this->MOD_SETTINGS['depth'] . '&id=' . ($data['row']['_ORIG_uid'] ? $data['row']['_ORIG_uid'] : $pageId) . '&return_id=' . $this->id . '&edit=1';
				$cells[] = '<td' . $bgCol . '><a href="' . htmlspecialchars($aHref) . '" title="' . $GLOBALS['LANG']->getLL('ch_permissions', TRUE) . '">' .
					IconUtility::getSpriteIcon('actions-document-open') . '</a></td>';
			} else {
				$cells[] = '<td' . $bgCol . '></td>';
			}

			if (!$disableOldPermissionSystem) {
				$cells[] = '
				<td' . $bgCol . ' nowrap="nowrap">' . ($pageId ? SC_mod_web_perm_ajax::renderPermissions($data['row']['perms_user'], $pageId, 'user') . ' ' . $userName : '') . '</td>
				<td' . $bgCol . ' nowrap="nowrap">' . ($pageId ? SC_mod_web_perm_ajax::renderPermissions($data['row']['perms_group'], $pageId, 'group') . ' ' . $groupName : '') . '</td>
				<td' . $bgCol . ' nowrap="nowrap">' . ($pageId ? ' ' . SC_mod_web_perm_ajax::renderPermissions($data['row']['perms_everybody'], $pageId, 'everybody') : '') . '</td>
				<td' . $bgCol . ' nowrap="nowrap">' . ($data['row']['editlock'] ? '<span id="el_' . $pageId . '" class="editlock"><a class="editlock" onclick="WebPermissions.toggleEditLock(\'' . $pageId . '\', \'1\');" title="' . $GLOBALS['LANG']->getLL('EditLock_descr', TRUE) . '">' . IconUtility::getSpriteIcon('status-warning-lock') . '</a></span>' : ($pageId === 0 ? '' : '<span id="el_' . $pageId . '" class="editlock"><a class="editlock" onclick="WebPermissions.toggleEditLock(\'' . $pageId . '\', \'0\');" title="Enable the &raquo;Admin-only&laquo; edit lock for this page">[+]</a></span>')) . '</td>
			';
			}

			// ACL rows
			if (!empty($aclUsers)) {
				$cells[] = '<td' . $bgCol . '>' . $this->countAcls($this->aclList[$data['row']['uid']][0]) . '</td>';
				foreach ($aclUsers as $uid) {
					$tmpBg = $bgCol;
					if (isset($this->aclList[$data['row']['uid']][0][$uid]['newAcl'])) {
						if ($this->aclList[$data['row']['uid']][0][$uid]['recursive']) {
							$tmpBg = ' class="bgColor5"';
						} else {
							$tmpBg = ' class="bgColor6"';
						}
					}

					$cells[] = '<td' . $tmpBg . ' nowrap="nowrap">' . ($data['row']['uid'] ? ' ' . $this->printPerms($this->aclList[$data['row']['uid']][0][$uid]['permissions']) : '') . '</td>';
				}
			}
			if (!empty($aclGroups)) {
				$cells[] = '<td' . $bgCol . '>' . $this->countAcls($this->aclList[$data['row']['uid']][1]) . '</td>';
				foreach ($aclGroups as $uid) {
					$tmpBg = $bgCol;
					if (isset($this->aclList[$data['row']['uid']][1][$uid]['newAcl'])) {
						if ($this->aclList[$data['row']['uid']][1][$uid]['recursive']) {
							$tmpBg = ' class="bgColor5"';
						} else {
							$tmpBg = ' class="bgColor6"';
						}
					}
					$cells[] = '<td' . $tmpBg . ' nowrap="nowrap">' . ($data['row']['uid'] ? ' ' . $this->printPerms($this->aclList[$data['row']['uid']][1][$uid]['permissions']) : '') . '</td>';
				}
			}

			// Compile table row:
			$code .= '<tr>' . implode('', $cells) . '</tr>';
		}

		// Wrap rows in table tags:
		$code = '<table class="t3-table" id="typo3-permissionList">' . $code . '</table>';

		// Adding the content as a section:
		$this->content .= $this->doc->section('', $code);

		// CSH for permissions setting
		$this->content .= BackendUtility::cshItem('xMOD_csh_corebe', 'perm_module', $GLOBALS['BACK_PATH'], '<br />|');

		// Creating legend table:
		$legendText = '<strong>' . $GLOBALS['LANG']->getLL('1', TRUE) . '</strong>: ' . $GLOBALS['LANG']->getLL('1_t', TRUE);
		$legendText .= '<br /><strong>' . $GLOBALS['LANG']->getLL('16', TRUE) . '</strong>: ' . $GLOBALS['LANG']->getLL('16_t', TRUE);
		$legendText .= '<br /><strong>' . $GLOBALS['LANG']->getLL('2', TRUE) . '</strong>: ' . $GLOBALS['LANG']->getLL('2_t', TRUE);
		$legendText .= '<br /><strong>' . $GLOBALS['LANG']->getLL('4', TRUE) . '</strong>: ' . $GLOBALS['LANG']->getLL('4_t', TRUE);
		$legendText .= '<br /><strong>' . $GLOBALS['LANG']->getLL('8', TRUE) . '</strong>: ' . $GLOBALS['LANG']->getLL('8_t', TRUE);

		$code = '<div id="permission-information">
					<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/legend.gif', 'width="86" height="75"') . ' alt="" />
				<div class="text">' . $legendText . '</div></div>';

		$code .= '<div id="perm-legend">' . $GLOBALS['LANG']->getLL('def', TRUE);
		$code .= '<br /><br />' . IconUtility::getSpriteIcon('status-status-permission-granted') . ': ' . $GLOBALS['LANG']->getLL('A_Granted', TRUE);
		$code .= '<br />' . IconUtility::getSpriteIcon('status-status-permission-denied') . ': ' . $GLOBALS['LANG']->getLL('A_Denied', TRUE);
		$code .= '</div>';

		// Adding section with legend code:
		$this->content .= $this->doc->section($GLOBALS['LANG']->getLL('Legend') . ':', $code, TRUE, TRUE);
	}

	/**
	 * Creating form for editing the permissions    ($this->edit = true)
	 * (Adding content to internal content variable)
	 *
	 * @return    void
	 */
	public function doEdit() {
		if ($GLOBALS['BE_USER']->workspace != 0) {
			// Adding section with the permission setting matrix:
			$flashMessage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $GLOBALS['LANG']->getLL('WorkspaceWarningText'), $GLOBALS['LANG']->getLL('WorkspaceWarning'), \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING);
			/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
			$flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
			/** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
			$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
			$defaultFlashMessageQueue->enqueue($flashMessage);
		}

		// Get ACL configuration
		$disableOldPermissionSystem = 0;
		$beAclConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_acl']);
		if ($beAclConfig['disableOldPermissionSystem']) {
			$disableOldPermissionSystem = 1;
		}
		$GLOBALS['LANG']->includeLLFile('EXT:be_acl/res/locallang_perm.xml');

		// Get usernames and groupnames
		$beGroupArray = BackendUtility::getListGroupNames('title,uid');
		$beGroupKeys = array_keys($beGroupArray);
		$beUserArray = BackendUtility::getUserNames();
		if (!$GLOBALS['BE_USER']->isAdmin()) {
			$beUserArray = BackendUtility::blindUserNames($beUserArray, $beGroupKeys, 1);
		}
		$beGroupArray_o = ($beGroupArray = BackendUtility::getGroupNames());
		if (!$GLOBALS['BE_USER']->isAdmin()) {
			$beGroupArray = BackendUtility::blindGroupNames($beGroupArray_o, $beGroupKeys, 1);
		}

		// Set JavaScript
		// Generate list if record is available on subpages, if yes, enter the id
		$this->content .= '<script src="../../../' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('be_acl') .
			'res/acl.js" type="text/javascript"></script>';

		// Owner selector:
		$options = '';

		// flag: is set if the page-userid equals one from the user-list
		$userset = 0;
		foreach ($beUserArray as $uid => $row) {
			if ($uid == $this->pageinfo['perms_userid']) {
				$userset = 1;
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$options .= '<option value="' . $uid . '"' . $selected . '>' . htmlspecialchars($row['username']) . '</option>';
		}
		$options = '<option value="0"></option>' . $options;

		// Hide selector if not needed
		$hidden = '';
		if ($disableOldPermissionSystem) {
			$hidden = ' style="display:none;" ';
		}

		$selector = '<select name="data[pages][' . $this->id . '][perms_userid]"' . $hidden . '>' . $options . '</select>';
		if ($disableOldPermissionSystem) {
			$this->content .= $selector;
		} else {
			$this->content .= $this->doc->section($GLOBALS['LANG']->getLL('Owner'), $selector, TRUE);
		}

		// Group selector:
		$options = '';
		$userset = 0;
		foreach ($beGroupArray as $uid => $row) {
			if ($uid == $this->pageinfo['perms_groupid']) {
				$userset = 1;
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$options .= '<option value="' . $uid . '"' . $selected . '>' . htmlspecialchars($row['title']) . '</option>';
		}

		// If the group was not set AND there is a group for the page
		if (!$userset && $this->pageinfo['perms_groupid']) {
			$options = '<option value="' . $this->pageinfo['perms_groupid'] . '" selected="selected">' . htmlspecialchars($beGroupArray_o[$this->pageinfo['perms_groupid']]['title']) . '</option>' . $options;
		}
		$options = '<option value="0"></option>' . $options;
		$selector = '<select name="data[pages][' . $this->id . '][perms_groupid]"' . $hidden . '>' . $options . '</select>';
		if ($disableOldPermissionSystem) {
			$this->content .= $selector;
		} else {
			$this->content .= $this->doc->section($GLOBALS['LANG']->getLL('Group'), $selector, TRUE);
		}

		// Permissions checkbox matrix:
		$code = '
			<input type="hidden" name="pageID" value="' . (int) $this->id . '" />
			<table class="t3-table" id="typo3-permissionMatrix">
				<thead>
					<tr>
						<th></th>
						<th>' . $GLOBALS['LANG']->getLL('1', TRUE) . '</th>
						<th>' . $GLOBALS['LANG']->getLL('16', TRUE) . '</th>
						<th>' . $GLOBALS['LANG']->getLL('2', TRUE) . '</th>
						<th>' . $GLOBALS['LANG']->getLL('4', TRUE) . '</th>
						<th>' . $GLOBALS['LANG']->getLL('8', TRUE) . '</th>
						<th>' . str_replace(' ', '<br />', $GLOBALS['LANG']->getLL('recursiveAcl', 1)) . '</th>
						<th></th>
					</tr>
				</thead>
				<tbody>';

		if (!$disableOldPermissionSystem) {
			$code .= '
					<tr>
						<td><strong>' . $GLOBALS['LANG']->getLL('Owner', TRUE) . '</strong></td>
						<td>' . $this->printCheckBox('perms_user', 1) . '</td>
						<td>' . $this->printCheckBox('perms_user', 5) . '</td>
						<td>' . $this->printCheckBox('perms_user', 2) . '</td>
						<td>' . $this->printCheckBox('perms_user', 3) . '</td>
						<td>' . $this->printCheckBox('perms_user', 4) . '</td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td><strong>' . $GLOBALS['LANG']->getLL('Group', TRUE) . '</strong></td>
						<td>' . $this->printCheckBox('perms_group', 1) . '</td>
						<td>' . $this->printCheckBox('perms_group', 5) . '</td>
						<td>' . $this->printCheckBox('perms_group', 2) . '</td>
						<td>' . $this->printCheckBox('perms_group', 3) . '</td>
						<td>' . $this->printCheckBox('perms_group', 4) . '</td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td><strong>' . $GLOBALS['LANG']->getLL('Everybody', TRUE) . '</strong></td>
						<td>' . $this->printCheckBox('perms_everybody', 1) . '</td>
						<td>' . $this->printCheckBox('perms_everybody', 5) . '</td>
						<td>' . $this->printCheckBox('perms_everybody', 2) . '</td>
						<td>' . $this->printCheckBox('perms_everybody', 3) . '</td>
						<td>' . $this->printCheckBox('perms_everybody', 4) . '</td>
						<td></td>
						<td></td>
					</tr>';
		}

		// ACL CODE
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_beacl_acl', 'pid=' . (int) $this->id);
		while ($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$acl_prefix = 'data[tx_beacl_acl][' . $result['uid'] . ']';
			$code .= '
				<tr>
					<td align="right"><select name="' . $acl_prefix . '[type]" onChange="updateUserGroup(' . $result['uid'] . ')"><option value="0" ' . ($result['type'] ? '' : 'selected="selected"') . '>User</option><option value="1" ' . ($result['type'] ? 'selected="selected"' : '') . '>Group</option></select><select name="' . $acl_prefix . '[object_id]"></select></td>
					<td>' . $this->printCheckBox('perms_acl_' . $result['uid'], 1, 'data[tx_beacl_acl][' . $result['uid'] . '][permissions]') . '</td>
					<td>' . $this->printCheckBox('perms_acl_' . $result['uid'], 5, 'data[tx_beacl_acl][' . $result['uid'] . '][permissions]') . '</td>
					<td>' . $this->printCheckBox('perms_acl_' . $result['uid'], 2, 'data[tx_beacl_acl][' . $result['uid'] . '][permissions]') . '</td>
					<td>' . $this->printCheckBox('perms_acl_' . $result['uid'], 3, 'data[tx_beacl_acl][' . $result['uid'] . '][permissions]') . '</td>
					<td>' . $this->printCheckBox('perms_acl_' . $result['uid'], 4, 'data[tx_beacl_acl][' . $result['uid'] . '][permissions]') . '
						<input type="hidden" name="' . $acl_prefix . '[permissions]" value="' . $result['permissions'] . '" />

						<script type="text/javascript">updateUserGroup(' . $result['uid'] . ', ' . $result['object_id'] . ');
						setCheck("check[perms_acl_' . $result['uid'] . ']","data[tx_beacl_acl][' . $result['uid'] . '][permissions]");
						global_currentACLs[global_currentACLs.length] = ' . $result['uid'] . ' ;
						</script>

					</td>
					<td>
						<input type="hidden" name="' . $acl_prefix . '[recursive]" value="0" />
						<input type="checkbox" name="' . $acl_prefix . '[recursive]" value="1" ' . ($result['recursive'] ? 'checked="checked"' : '') . ' />
					</td>
					<td><a href="#" onClick="deleteACL(' . $result['uid'] . ')"><img ' . IconUtility::skinImg('', 'gfx/garbage.gif') . ' alt="' . $GLOBALS['LANG']->getLL('delAcl', 1) . '" /></a></td>
				</tr>';
		}

        $code .= '
            </table>
            <br />';
        if ($disableOldPermissionSystem) {
        	$code .= '<span style="display:none;">';
        	for ( $i = 1; $i <= 5; ++$i ) {
				$code .= $this->printCheckBox('perms_user', $i)
					   . $this->printCheckBox('perms_group', $i)
					   . $this->printCheckBox('perms_everybody', $i)
					   ;
			}
        	$code .= '</span>';
        }

        $code .= '
			<span id="insertHiddenFields"></span>
			<img ' . IconUtility::skinImg('', 'gfx/garbage.gif') . ' alt="' . $GLOBALS['LANG']->getLL('delAcl', 1) . '" / id="templateDeleteImage" style="display:none">
			<a href="javascript:addACL()"><img  ' . IconUtility::skinImg('', 'gfx/new_el.gif') . ' alt="' . $GLOBALS['LANG']->getLL('addAcl', 1) . '" />' . $GLOBALS['LANG']->getLL('addAcl', 1) . '</a><br />

			<input type="hidden" name="data[pages][' . $this->id . '][perms_user]" value="' . $this->pageinfo['perms_user'] . '" />
			<input type="hidden" name="data[pages][' . $this->id . '][perms_group]" value="' . $this->pageinfo['perms_group'] . '" />
			<input type="hidden" name="data[pages][' . $this->id . '][perms_everybody]" value="' . $this->pageinfo['perms_everybody'] . '" />
			' . ($disableOldPermissionSystem ? '' : $this->getRecursiveSelect($this->id, $this->perms_clause)) . '
			<input type="submit" name="submit" value="' . $GLOBALS['LANG']->getLL('saveAndClose', TRUE) . '" />' . '<input type="submit" value="' . $GLOBALS['LANG']->getLL('Abort', TRUE) . '" onclick="' . htmlspecialchars(('jumpToUrl(' . GeneralUtility::quoteJSvalue((BackendUtility::getModuleUrl('web_perm') . '&id=' . $this->id), TRUE) . '); return false;')) . '" />
			<input type="hidden" name="redirect" value="' . htmlspecialchars((BackendUtility::getModuleUrl('web_perm') . '&mode=' . $this->MOD_SETTINGS['mode'] . '&depth=' . $this->MOD_SETTINGS['depth'] . '&id=' . (int) $this->return_id . '&lastEdited=' . $this->id)) . '" />
			' . \TYPO3\CMS\Backend\Form\FormEngine::getHiddenTokenField('tceAction');

		// Adding section with the permission setting matrix:
		$this->content .= $this->doc->section($GLOBALS['LANG']->getLL('permissions'), $code, TRUE);

		// CSH for permissions setting
		$this->content .= BackendUtility::cshItem('xMOD_csh_corebe', 'perm_module_setting', $GLOBALS['BACK_PATH'], '<br /><br />');

		// Adding help text:
		if ($GLOBALS['BE_USER']->uc['helpText']) {
			$legendText = '<p><strong>' . $GLOBALS['LANG']->getLL('1', TRUE) . '</strong>: ' . $GLOBALS['LANG']->getLL('1_t', TRUE) . '<br />';
			$legendText .= '<strong>' . $GLOBALS['LANG']->getLL('16', TRUE) . '</strong>: ' . $GLOBALS['LANG']->getLL('16_t', TRUE) . '<br />';
			$legendText .= '<strong>' . $GLOBALS['LANG']->getLL('2', TRUE) . '</strong>: ' . $GLOBALS['LANG']->getLL('2_t', TRUE) . '<br />';
			$legendText .= '<strong>' . $GLOBALS['LANG']->getLL('4', TRUE) . '</strong>: ' . $GLOBALS['LANG']->getLL('4_t', TRUE) . '<br />';
			$legendText .= '<strong>' . $GLOBALS['LANG']->getLL('8', TRUE) . '</strong>: ' . $GLOBALS['LANG']->getLL('8_t', TRUE) . '</p>';

			$code = $legendText . '<p>' . $GLOBALS['LANG']->getLL('def', TRUE) . '</p>';

			$this->content .= $this->doc->section($GLOBALS['LANG']->getLL('Legend', TRUE), $code, TRUE);
		}
	}

	/*****************************
	 *
	 * Helper functions
	 *
	 *****************************/
	/**
	 * generates title attribute for pages
	 *
	 * @param    integer        UID of page
	 * @param    array        BE user array
	 * @param    array        BE group array
	 * @return    string        HTML: title attribute
	 */
	function generateTitleAttribute($uid, $be_user_Array, $be_group_Array) {

		$composedStr = '';
		$this->aclList[$uid];
		if (!$this->aclList[$uid]) {
			return FALSE;
		}
		foreach ($this->aclList[$uid] as $type => $v1) {
			if (!$v1) {
				return FALSE;
			}
			foreach ($v1 as $object_id => $v2) {
				if ($v2['newAcl']) {
					if ($type == 1) { // group
						$composedStr .= ' G:' . $be_group_Array[$object_id]['title'];
					} else {
						$composedStr .= ' U:' . $be_user_Array[$object_id]['username'];
					}
				}
			}
		}

		return ' title="' . $composedStr . '"' . ($composedStr ? ' class="bgColor5"' : '');
	}

	/**
	 * outputs a selector for users / groups, returns current ACLs
	 *
	 * @param    integer        type of ACL. 0 -> user, 1 -> group
	 * @param    string        Pointer where the display code is stored
	 * @param    array        configuration of ACLs
	 * @return    array        list of groups/users where the ACLs will be shown
	 */
	function acl_objectSelector($type, &$displayPointer, $conf) {
		global $BE_USER;
		$aclObjects = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_beacl_acl.object_id AS object_id, tx_beacl_acl.type AS type',
			'tx_beacl_acl, be_groups, be_users',
			'tx_beacl_acl.type=' . intval($type) . ' AND ((tx_beacl_acl.object_id=be_groups.uid AND tx_beacl_acl.type=1) OR (tx_beacl_acl.object_id=be_users.uid AND tx_beacl_acl.type=0))',
			'',
			'be_groups.title ASC, be_users.realname ASC'
		);
		while ($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$aclObjects[] = $result['object_id'];
		}
		$aclObjects = array_unique($aclObjects);
		// advanced selector disabled
		if (!$conf['enableFilterSelector']) {
			return $aclObjects;
		}

		if (!empty($aclObjects)) {

			// Get usernames and groupnames: The arrays we get in return contains only 1) users which are members of the groups of the current user, 2) groups that the current user is member of
			$groupArray = $BE_USER->userGroupsUID;
			$be_user_Array = BackendUtility::getUserNames();
			if (!$GLOBALS['BE_USER']->isAdmin()) {
				$be_user_Array = BackendUtility::blindUserNames($be_user_Array, $groupArray, 0);
			}
			$be_group_Array = BackendUtility::getGroupNames();
			if (!$GLOBALS['BE_USER']->isAdmin()) {
				$be_group_Array = BackendUtility::blindGroupNames($be_group_Array, $groupArray, 0);
			}

			// get current selection from UC, merge data, write it back to UC
			$currentSelection = is_array($BE_USER->uc['moduleData']['txbeacl_aclSelector'][$type]) ? $BE_USER->uc['moduleData']['txbeacl_aclSelector'][$type] : array();

			$currentSelectionOverride_raw = GeneralUtility::_GP('tx_beacl_objsel');
			$currentSelectionOverride = array();
			if (is_array($currentSelectionOverride_raw[$type])) {
				foreach ($currentSelectionOverride_raw[$type] as $tmp) {
					$currentSelectionOverride[$tmp] = $tmp;
				}
			}
			if ($currentSelectionOverride) {
				$currentSelection = $currentSelectionOverride;
			}
			$BE_USER->uc['moduleData']['txbeacl_aclSelector'][$type] = $currentSelection;
			$BE_USER->writeUC($BE_USER->uc);

			// display selector
			$displayCode = '<select size="' . \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange(count($aclObjects), 5, 15) . '" name="tx_beacl_objsel[' . $type . '][]" multiple="multiple">';
			foreach ($aclObjects as $singleObjectId) {
				if ($type == 0) {
					$tmpnam = $be_user_Array[$singleObjectId]['username'];
				} else {
					$tmpnam = $be_group_Array[$singleObjectId]['title'];
				}

				$displayCode .= '<option value="' . $singleObjectId . '" ' . (@in_array($singleObjectId, $currentSelection) ? 'selected' : '') . '>' . $tmpnam . '</option>';
			}

			$displayCode .= '</select>';
			$displayCode .= '<br /><input type="button" value="' . $GLOBALS['LANG']->getLL('aclObjSelUpdate') . '" onClick="document.editform.action=document.location; document.editform.submit()" /><p />';

			// create section
			switch ($type) {
				case 0:
					$tmpnam = 'aclUsers';
					break;
				default:
					$tmpnam = 'aclGroups';
					break;
			}
			$displayPointer = $this->doc->section($GLOBALS['LANG']->getLL($tmpnam, 1), $displayCode);

			return $currentSelection;
		}

		return NULL;
	}


	/**
	 * returns a datastructure: pageid - userId / groupId - permissions
	 *
	 * @param    array        user ID list
	 * @param    array        group ID list
	 */
	function buildACLtree($users, $groups) {

		// get permissions in the starting point for users and groups
		$rootLine = BackendUtility::BEgetRootLine($this->id);

		$userStartPermissions = array();
		$groupStartPermissions = array();

		array_shift($rootLine); // needed as a starting point

		foreach ($rootLine as $level => $values) {
			$recursive = ' AND recursive=1';

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('type, object_id, permissions', 'tx_beacl_acl', 'pid=' . intval($values['uid']) . $recursive);

			while ($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($result['type'] == 0
					&& in_array($result['object_id'], $users)
					&& !array_key_exists($result['object_id'], $userStartPermissions)
				) {
					$userStartPermissions[$result['object_id']] = $result['permissions'];
				} elseif ($result['type'] == 1
					&& in_array($result['object_id'], $groups)
					&& !array_key_exists($result['object_id'], $groupStartPermissions)
				) {
					$groupStartPermissions[$result['object_id']] = $result['permissions'];
				}
			}
		}
		foreach ($userStartPermissions as $oid => $perm) {
			$startPerms[0][$oid]['permissions'] = $perm;
			$startPerms[0][$oid]['recursive'] = 1;
		}
		foreach ($groupStartPermissions as $oid => $perm) {
			$startPerms[1][$oid]['permissions'] = $perm;
			$startPerms[1][$oid]['recursive'] = 1;
		}


		$this->traversePageTree_acl($startPerms, $rootLine[0]['uid']);

		// check if there are any ACLs on these pages
		// build a recursive function traversing through the pagetree
	}

	function countAcls($pageData) {
		$i = 0;
		if (!$pageData) {
			return '';
		}
		foreach ($pageData as $aclId => $values) {
			if ($values['newAcl']) {
				$i += $values['newAcl'];
			}
		}

		return ($i ? $i : '');
	}

	/**
	 * build ACL tree
	 */
	function traversePageTree_acl($parentACLs, $uid) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('type, object_id, permissions, recursive', 'tx_beacl_acl', 'pid=' . intval($uid));

		$hasNoRecursive = array();
		$this->aclList[$uid] = $parentACLs;
		while ($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$permissions = array(
				'permissions' => $result['permissions'],
				'recursive' => $result['recursive'],
			);
			if ($result['recursive'] == 0) {
				if ($this->aclList[$uid][$result['type']][$result['object_id']]['newAcl']) {
					$permissions['newAcl'] = $this->aclList[$uid][$result['type']][$result['object_id']]['newAcl'];
				}
				$this->aclList[$uid][$result['type']][$result['object_id']] = $permissions;
				$permissions['newAcl'] = 1;
				$hasNoRecursive[$uid][$result['type']][$result['object_id']] = $permissions;
			} else {
				$parentACLs[$result['type']][$result['object_id']] = $permissions;
				if (is_array($hasNoRecursive[$uid][$result['type']][$result['object_id']])) {
					$this->aclList[$uid][$result['type']][$result['object_id']] = $hasNoRecursive[$uid][$result['type']][$result['object_id']];
				} else {
					$this->aclList[$uid][$result['type']][$result['object_id']] = $permissions;
				}
			}
			$this->aclList[$uid][$result['type']][$result['object_id']]['newAcl'] += 1;
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'pid=' . intval($uid) . ' AND deleted=0');
		while ($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->traversePageTree_acl($parentACLs, $result['uid']);
		}
	}

	/**
	 * prints table header
	 *
	 * @param    array        array of cells
	 * @return    string        HTML output for the cells
	 */
	function printTableHeader($cells) {
		$wrappedCells = '';
		foreach ($cells as $singleCell) {
			$wrappedCells .= '<th align="center">' . $singleCell . '</th>';
		}

		return $wrappedCells;

	}

	/**
	 * Print a checkbox for the edit-permission form
	 *
	 * @param    string        Checkbox name key
	 * @param    integer        Checkbox number index
	 * @param string        Result sting, not mandatory
	 * @return    string        HTML checkbox
	 */
	function printCheckBox($checkName, $num, $result = '') {
		if (empty($result)) {
			$result = 'data[pages][' . $GLOBALS['SOBE']->id . '][' . $checkName . ']';
		}

		$onClick = 'checkChange(\'check[' . $checkName . ']\', \'' . $result . '\')';

		return '<input type="checkbox" name="check[' . $checkName . '][' . $num . ']" onclick="' . htmlspecialchars($onClick) . '" /><br />';
	}

	/**
	 * Print a set of permissions
	 *
	 * @param    integer        Permission integer (bits)
	 * @return    string        HTML marked up x/* indications.
	 */
	function printPerms($int) {
		global $LANG;
		$permissions = array(
			1,
			16,
			2,
			4,
			8
		);
		$str = '';
		foreach ($permissions as $permission) {
			if ($int & $permission) {
				$str .= IconUtility::getSpriteIcon('status-status-permission-granted', array(
					'tag' => 'a',
					'title' => $LANG->getLL($permission, 1),
					'onclick' => 'WebPermissions.setPermissions(' . $GLOBALS['SOBE']->id . ', ' . $permission . ', \'delete\', \'' . $who . '\', ' . $int . ');'
				));
			} else {
				$str .= IconUtility::getSpriteIcon('status-status-permission-denied', array(
					'tag' => 'a',
					'title' => $LANG->getLL($permission, 1),
					'onclick' => 'WebPermissions.setPermissions(' . $GLOBALS['SOBE']->id . ', ' . $permission . ', \'add\', \'' . $who . '\', ' . $int . ');'
				));
			}
		}

		return $str;
	}

}

?>
